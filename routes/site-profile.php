<?php
	
	use \Hcode\Page;
	use \Hcode\Model\User;
	use \Hcode\Model\Order;
	use \Hcode\Model\Cart;

	$app->get('/profile', function() {

		User::verifyLogin(false);

		$user = User::getFromSession();

		$page = new Page();

		$page->setTpl("profile", [
			'user' => $user->getData(),
			'profileMsg' => User::getSuccess(),
			'profileError' => User::getError()
		]);

	});

	$app->post('/profile', function() {

		User::verifyLogin();

		if(!isset($_POST['desperson']) || $_POST['desperson'] == '') {

			User::setError("Fill in your name.");
			header('Location: /profile');
			exit;

		}

		if(!isset($_POST['desemail']) || $_POST['desemail'] == '') {

			User::setError("Fill in your email.");
			header('Location: /profile');
			exit;

		}

		$user = getFromSession();

		if ($_POST['desemail'] !== $user->getdesemail()) {
			
			if (User::checkLoginExist($_POST['desemail'])) {
			
				User::setError("This email address is already in use. Enter another.");
				header('Location: /login');
				exit;
			
			}
			
		} else {

			User::setError("You are already using this email address. Enter another.");
			header('Location: /login');
			exit;

		}

		$_POST['inadmin'] = $user->getinadmin();
		$_POST['password'] = $user->getdespassword();
		$_POST['deslogin'] = $_POST['desemail'];

		$user->setData($_POST);

		$user->update();

		User::setSuccess("Data updated with success.");

		header('Location: /profile');
		exit();

	});
$app->get("/profile/orders", function() {

		User::verifyLogin(false);

		$user = User::getFromSession();

		$page = new Page();

		$page->setTpl("profile-orders", [
			'orders' => $user->getOrders()
		]);

	});

	$app->get("/profile/orders/:idorder", function($idorder) {

		User::verifyLogin(false);

		$order = new Order();

		$order->get((int)$idorder);

		$cart = new Cart();

		$cart->get((int)$order->getidcart());

		$cart->getCalculateTotal()		;

		$page = new Page();

		$page->setTpl("profile-orders-detail", [
			'order' => $order->getData(),
			'cart' => $cart->getData(),
			'products' => $cart->getProducts()
		]);

	});

	$app->get("/profile/change-password", function() {

		User::verifyLogin(false);

		$page = new Page();

		$page->setTpl("profile-change-password", [
			'changePassError' => User::getError(),
			'changePassSuccess' => USer::getSuccess()
		]);

	});

	$app->post("/profile/change-password", function() {

		User::verifyLogin(false);

		if(!isset($_POST['current_pass']) || $_POST['current_pass'] == '') {

			User::setError("Fill in your current password.");
			header('Location: /profile/change-password');
			exit;

		}


		if(!isset($_POST['new_pass']) || $_POST['new_pass'] == '') {

			User::setError("Fill in your new password.");
			header('Location: /profile/change-password');
			exit;

		}

		if(!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] == '') {

			User::setError("Fill in the confirmation of the new password.");
			header('Location: /profile/change-password');
			exit;

		}

		if($_POST['new_pass'] != $_POST['new_pass_confirm']) {

			User::setError("Password confirmation and new password don't match.");
			header('Location: /profile/change-password');
			exit;

		}

		$user = User::getFromSession();

		if(!password_verify($_POST['current_pass'], $user->getdespassword())) {

			User::setError("Your current password is incorrect. Try again.");
			header('Location: /profile/change-password');
			exit;

		}

		$user->setdespassord($_POST['new_pass']);

		$user->update();

		User::setSuccess("Password changed successfully!");

		header('Location: /profile/change-password');
		exit;

	});

?>