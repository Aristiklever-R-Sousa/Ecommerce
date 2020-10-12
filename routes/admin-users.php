<?php 

	use \Hcode\Model\User;
	use \Hcode\Message;
	use \Hcode\Pagination;
	use \Hcode\PageAdmin;

	$app->get('/admin/users/:iduser/password', function($iduser) {

		User::verifyLogin();
		
		$user = new User();

		$user->get((int)$iduser);

		$page = new PageAdmin();

		$page->setTpl('users-password', [
			'user' => $user->getData(),
			'msgError' => Message::getError(),
			'msgSuccess' => Message::getSuccess()
		]);

	});

	$app->post('/admin/users/:iduser/password', function($iduser) {

		User::verifyLogin();

		if(!(isset($_POST['despassword']) && $_POST['despassword'])) {

			Message::setError("Fill the new password.");
			header('Location: /admin/users/'.$iduser.'/password');
			exit;

		}

		if(!(isset($_POST['despassword-confirm']) && $_POST['despassword-confirm'])) {

			Message::setError("Fill the new password confirmation.");
			header('Location: /admin/users/'.$iduser.'/password');
			exit;

		}

		if($_POST['despassword'] !== $_POST['despassword-confirm']) {

			Message::setError("Passwords don't match.");
			header('Location: /admin/users/'.$iduser.'/password');
			exit;

		}

		$user = new User();

		$user->get((int)$iduser);

		$user->setPassword($_POST['despassword']);

		Message::setSuccess("Password changed successfully.");

		header('Location: /admin/users/'.$iduser.'/password');
		exit;

	});

	$app->get('/admin/users', function() {

		User::verifyLogin();

		$search = isset($_GET['search']) ? $_GET['search'] : '';
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

		$pagination = $search ?
						Pagination::getPageSearch('users', $search, $page)
						:
						Pagination::getPage('users', $page);

		$pages = [];

		for ($x = 0; $x < $pagination['pages']; $x++)
		{
			
			array_push($pages, [
				'href' => '/admin/users?'.http_build_query([
					'page' => $x+1,
					'search' => $search
				]),
				'text' => $x+1
			]);

		}

		$page = new PageAdmin();

	    $page->setTpl("users", array(
	    	"users" => $pagination['data'],
	    	"search" => $search,
	    	"pages" => $pages
	    ));
	
	});

	$app->get('/admin/users/create', function() {

		User::verifyLogin();

		$page = new PageAdmin();

	    $page->setTpl("users-create", [
	    	'errorRegister' => Message::getErrorRegister(),
	    	"registerDataAdm" => isset($_SESSION['registerDataAdm'])
	    						?
								$_SESSION['registerDataAdm'] :
								[
									'desperson'=> '',
									'deslogin'=> '',
									'desemail'=> '',
									'nrphone' => '',
									'inadmin' => 0
								]
	    ]);
	
	});

	$app->post('/admin/users/create', function() {

		User::verifyLogin();

		$_SESSION['registerDataAdm'] = $_POST;
		$_SESSION['registerDataAdm']['inadmin'] = isset($_POST['inadmin']) ? 1 : 0;

		if(!(isset($_POST['desperson']) && $_POST['desperson'])) {

			Message::setErrorRegister("Fill in your name.");
			header('Location: /admin/users/create');
			exit;

		}

		if(!(isset($_POST['deslogin']) && $_POST['deslogin'])) {

			Message::setErrorRegister("Fill in your login.");
			header('Location: /admin/users/create');
			exit;

		}

		if(!(isset($_POST['desemail']) && $_POST['desemail'])) {

			Message::setErrorRegister("Fill in your email.");
			header('Location: /admin/users/create');
			exit;

		}

		if (User::checkLoginExist($_POST['desemail'])) {
			
			Message::setErrorRegister("This email is already in use. Enter another.");
			header('Location: /admin/users/create');
			exit;
			
		}

		if(!(isset($_POST['despassword']) && $_POST['despassword'])) {

			Message::setErrorRegister("Fill in your password.");
			header('Location: /admin/users/create');
			exit;

		}

		$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
		
		$user = new User();

		$user->setData([
			'inadmin' => $_POST['inadmin'],
			'deslogin' => $_POST['deslogin'],
			'nrphone' => $_POST['nrphone'],
			'desperson' => $_POST['desperson'],
			'desemail' => $_POST['desemail'],
			'despassword' => $_POST['despassword']
		]);
		
		$user->save();

		header("Location: /admin/users");

		$_SESSION['registerDataAdm'] = [
			'inadmin' => 0,
			'deslogin' => '',
			'desperson' => '',
			'desemail' => '',
			'despassword' => '',
			'nrphone' => ''
		];

		exit;

	});

	$app->get('/admin/users/:iduser/delete', function($iduser) {

		User::verifyLogin();

		$user = new User();

		$user->get((int)$iduser);

		$user->delete();

		header("Location: /admin/users");
		exit;
	
	});

	$app->get('/admin/users/:iduser', function($iduser) {

		User::verifyLogin();

		$user = new User();

		$user->get((int)$iduser);

		$page = new PageAdmin();

	    $page->setTpl("users-update", array(
	    	"user" => $user->getData()
	    ));
	
	});

	$app->post('/admin/users/:iduser', function($iduser) {

		User::verifyLogin();

		if(!(isset($_POST['desperson']) && $_POST['desperson'])) {

			Message::setErrorRegister("Fill in your name.");
			header('Location: /admin/users/create');
			exit;

		}

		if(!(isset($_POST['deslogin']) && $_POST['deslogin'])) {

			Message::setErrorRegister("Fill in your login.");
			header('Location: /admin/users/create');
			exit;

		}

		if(!(isset($_POST['desemail']) && $_POST['desemail'])) {

			Message::setErrorRegister("Fill in your email.");
			header('Location: /admin/users/create');
			exit;

		}

		if (User::checkLoginExist($_POST['desemail'])) {
			
			Message::setErrorRegister("This email is already in use. Enter another.");
			header('Location: /admin/users/create');
			exit;
			
		}

		if(!(isset($_POST['despassword']) && $_POST['despassword'])) {

			Message::setErrorRegister("Fill in your password.");
			header('Location: /admin/users/create');
			exit;

		}

		$user = new User();

		$user->get((int)$iduser);

		$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
		$user->setData($_POST);

		$user->update();

		header("Location: /admin/users");
		exit;
	
	});

?>