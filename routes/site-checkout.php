<?php
	
	use \Hcode\Page;
	use \Hcode\Model\User;
	use \Hcode\Model\Address;
	use \Hcode\Model\Cart;
	use \Hcode\Model\Order;
	use \Hcode\Model\OrderStatus;

	$app->get('/checkout', function() {

		User::verifyLogin(false);

		$address = new Address();
		$cart = Cart::getFromSession();

		if (!isset($_GET['deszipcode'])) {

			$_GET['deszipcode'] = $cart->getdeszipcode();

		}

		if(isset($_GET['deszipcode'])) {

			$address->loadFromCEP($_GET['deszipcode']);

			$cart->save();

			$cart->getCalculateTotal();
		}

		if (!$address->getdesaddress()) $address->setdesaddress('');
		if (!$address->getdesnumber()) $address->setdesnumber('');
		if (!$address->getdescomplement()) $address->setdescomplement('');
		if (!$address->getdesdistrict()) $address->setdesdistrict('');
		if (!$address->getdescity()) $address->setdescity('');
		if (!$address->getdesstate()) $address->setdesstate('');
		if (!$address->getdescountry()) $address->setdescountry('');
		if (!$address->getdeszipcode()) $address->setdeszipcode('');

		$page = new Page();

		$page->setTpl("checkout", [
			'cart' => $cart->getData(),
			'address' => $address->getData(),
			'products'=> $cart->getProducts(),
			'error' => Address::getMsgError()
		]);

	});


	$app->post('/checkout', function() {

		User::verifyLogin(false);

		if(!(isset($_POST['deszipcode']) && $_POST['deszipcode'])) {

			Address::setMsgError("Enter the zipcode.");
			header("Location: /checkout");
			exit;

		}

		if(!(isset($_POST['desaddress']) && $_POST['desaddress'])) {

			Address::setMsgError("Enter the address.");
			header("Location: /checkout");
			exit;

		}

		if(!(isset($_POST['desdistrict']) && $_POST['desdistrict'])) {

			Address::setMsgError("Enter the district.");
			header("Location: /checkout");
			exit;

		}

		if(!(isset($_POST['descity']) && $_POST['descity'])) {

			Address::setMsgError("Enter the city.");
			header("Location: /checkout");
			exit;

		}

		if(!(isset($_POST['desstate']) && $_POST['desstate'])) {

			Address::setMsgError("Enter the state.");
			header("Location: /checkout");
			exit;

		}

		if(!(isset($_POST['descountry']) && $_POST['descountry'])) {

			Address::setMsgError("Enter the country.");
			header("Location: /checkout");
			exit;

		}

		$user = User::getFromSession();

		$address = new Address();

		$_POST['idperson'] = $user->getidperson();

		$address->setData($_POST);

		$address->save();

		$cart = Cart::getFromSession();

		$cart->getCalculateTotal();

		$order = new Order();

		$order->setData([
			'idcart' => $cart->getidcart(),
			'idaddress' => $address->getidaddress(),
			'iduser' => $user->getiduser(),
			'idstatus' => OrderStatus::EM_ABERTO,
			'vltotal' => $cart->getvltotal()
		]);

		$order->save();

		switch ((int)$_POST['payment-method']) {
			case 1:
				header("Location: /order/".$order->getidorder()."/pagseguro");
				break;
			
			default:
				header("Location: /order/".$order->getidorder()."/paypal");
				break;
		}

		exit();

	});

	$app->get("/order/:idorder/pagseguro", function($idorder) {

		User::verifyLogin(false);

		$order = new Order();

		$order->get((int)$idorder);

		$cart = $order->getCart();

		$page = new Page([
			'header' => false,
			'footer' => false
		]);

		$page->setTpl("payment-pagseguro", [
			'order' => $order->getData(),
			'cart' => $cart->getData(),
			'products' => $cart->getProducts(),
			'phone' => [
				'areaCode' => substr($order->getnrphone(), 0, 2),
				'number' => substr($order->getnrphone(), 2, strlen($order->getnrphone()))
			]
		]);

	});

	$app->get("/order/:idorder/paypal", function($idorder) {

		User::verifyLogin(false);

		$order = new Order();

		$order->get((int)$idorder);

		$cart = $order->getCart();

		$page = new Page([
			'header' => false,
			'footer' => false
		]);

		$page->setTpl("payment-paypal", [
			'order' => $order->getData(),
			'cart' => $cart->getData(),
			'products' => $cart->getProducts()
		]);

	});

?>