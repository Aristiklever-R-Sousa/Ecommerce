<?php
	
	use \Hcode\Page;
	use \Hcode\Message;
	use \Hcode\Model\User;
	use \Hcode\Model\Order;
	use \Hcode\Model\Cart;

	$app->get('/profile', function() {

		User::verifyLogin(false);

		$user = User::getFromSession();

		$page = new Page();

		$page->setTpl("profile", [
			'user' => $user->getData(),
			'profileMsg' => Message::getSuccess(),
			'profileError' => Message::getError()
		]);

	});

	$app->post('/profile', function() {

		User::verifyLogin(false);

		if(!isset($_POST['desperson']) || $_POST['desperson'] == '') {

			Message::setError("Fill in your name.");
			header('Location: /profile');
			exit;

		}

		if(!isset($_POST['desemail']) || $_POST['desemail'] == '') {

			Message::setError("Fill in your email.");
			header('Location: /profile');
			exit;

		}

		$user = User::getFromSession();

		if ($_POST['desemail'] !== $user->getdesemail()) {
			
			if (User::checkLoginExist($_POST['desemail'])) {
			
				Message::setError("This email address is already in use. Enter another.");
				header('Location: /profile');
				exit;
			
			}
			
		} 

		$_POST['inadmin'] = $user->getinadmin();
		$_POST['password'] = $user->getdespassword();
		$_POST['deslogin'] = $_POST['desemail'];

		$user->setData($_POST);

		$user->update();

		Message::setSuccess("Data updated with success.");

		header('Location: /profile');
		exit;

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
			'changePassError' => Message::getError(),
			'changePassSuccess' => Message::getSuccess()
		]);

	});

	$app->post("/profile/change-password", function() {

		User::verifyLogin(false);

		if(!isset($_POST['current_pass']) || $_POST['current_pass'] == '') {

			Message::setError("Fill in your current password.");
			header('Location: /profile/change-password');
			exit;

		}


		if(!isset($_POST['new_pass']) || $_POST['new_pass'] == '') {

			Message::setError("Fill in your new password.");
			header('Location: /profile/change-password');
			exit;

		}

		if(!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] == '') {

			Message::setError("Fill in the confirmation of the new password.");
			header('Location: /profile/change-password');
			exit;

		}

		if($_POST['new_pass'] != $_POST['new_pass_confirm']) {

			Message::setError("Password confirmation and new password don't match.");
			header('Location: /profile/change-password');
			exit;

		}

		$user = User::getFromSession();

		if(!password_verify($_POST['current_pass'], $user->getdespassword())) {

			Message::setError("Your current password is incorrect. Try again.");
			header('Location: /profile/change-password');
			exit;

		}

		$user->setPassword($_POST['new_pass']);

		Message::setSuccess("Password changed successfully!");

		header('Location: /profile/change-password');
		exit;

	});

?>