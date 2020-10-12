<?php
	
	use \Hcode\Page;
	use \Hcode\Message;
	use \Hcode\Model\User;
	use \Hcode\Model\Cart;

	$app->get('/login', function() {

		$page = new Page();

		$page->setTpl('login',[
			'error' => Message::getError(),
			'errorRegister' => Message::getErrorRegister(),
			'registerValues' => isset($_SESSION['registerValues'])
								?
								$_SESSION['registerValues'] :
								['email'=> '', 'name'=> '', 'phone'=> '']
		]);

	});

	$app->post('/login', function() {

		try {

			User::login($_POST['login'], $_POST['password']);

			$cart = Cart::getFromSession();

			$cart->getData();

			$route = $cart->getvlsubtotal() ? "/checkout" : "/";
			
			header('Location: '.$route);
			exit;

		} catch(Exception $e) {

			Message::setError($e->getMessage());
			header('Location: /login');
			exit;
		}
		
	});

	$app->get('/logout', function() {

		User::logout();

		header('Location: /login');
		exit;

	});

	$app->post('/register', function() {

		$_SESSION['registerValues'] = $_POST;

		if(!isset($_POST['name']) || $_POST['name'] == '') {

			Message::setErrorRegister("Fill in your name.");
			header('Location: /login');
			exit;

		}

		if(!isset($_POST['email']) || $_POST['email'] == '') {

			Message::setErrorRegister("Fill in your email.");
			header('Location: /login');
			exit;

		}

		if (User::checkLoginExist($_POST['email'])) {
			
			Message::setErrorRegister("This email is already in use. Enter another.");
			header('Location: /login');
			exit;
			
		}

		if(!isset($_POST['password']) || $_POST['password'] == '') {

			Message::setErrorRegister("Fill in your password.");
			header('Location: /login');
			exit;

		}

		$user = new User();

		$user->setData([
			'inadmin' => 0,
			'deslogin' => $_POST['email'],
			'desperson' => $_POST['name'],
			'desemail' => $_POST['email'],
			'despassword' => $_POST['password'],
			'nrphone' => $_POST['phone']
		]);

		$user->save();

		User::login($_POST['email'], $_POST['password']);

		$cart = Cart::getFromSession();

		$cart->getData();

		$route = $cart->getvlsubtotal() ? "/checkout" : "/";
			
		header('Location: '.$route);

		$_SESSION['registerValues'] = [
			'email'=> '', 
			'name'=> '', 
			'phone'=> ''
		];
		exit;

	});

	$app->get('/forgot', function() {
	    
	    $page = new Page();

	    $page->setTpl("forgot");

	});

	$app->post('/forgot', function() {
	
		User::getForgot($_POST['email'], false);

		header("Location: /forgot/sent");
		exit;
	
	});

	$app->get('/forgot/sent', function() {
		
		$page = new Page();

	    $page->setTpl("forgot-sent");
	
	});

	$app->get('/forgot/reset', function() {

		$user = User::validForgotDecrypt($_GET["code"]);

		$page = new Page();

	    $page->setTpl("forgot-reset", array(
	    	"name" => $user["desperson"],
	    	"code" => $_GET["code"]
	    ));

	});

	$app->post("/forgot/reset", function() {

		$forgot = User::validForgotDecrypt($_POST["code"]);

		User::setForgotUsed($forgot["idrecovery"]);

		$user = new User();

		$user->get((int)$forgot["iduser"]);

		$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
			"cost" => 12
		]);

		$user->setPassword($password);

		$page = new Page();

	    $page->setTpl("forgot-reset-success");

	});

?>