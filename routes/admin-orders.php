<?php

	use \Hcode\PageAdmin;
	use \Hcode\Pagination;
	use \Hcode\Model\User;
	use \Hcode\Model\Order;
	use \Hcode\Model\OrderStatus;

	$app->get("/admin/orders/:idorder/status", function($idorder) {

		User::verifyLogin();

		$order = new Order();

		$order->get((int)$idorder);

		$page = new PageAdmin();

		$page->setTpl('order-status', [
			'order' => $order->getData(),
			'status' => OrderStatus::listAll(),
			'msgSuccess' => Order::getSuccess(),
			'msgError' => Order::getError()
		]);

	});

	$app->post("/admin/orders/:idorder/status", function($idorder) {

		User::verifyLogin();

		if(!(isset($_POST['idstatus']) && $_POST['idstatus'])) {

			Order::setError("Enter the current status.");
			header('Location: /admin/orders/'.$idorder.'/status');
			exit;

		}

		$order = new Order();

		$order->get((int)$idorder);

		$order->setidstatus((int)$_POST['idstatus']);

		$order->save();

		Order::setSuccess("Status updated.");
		header('Location: /admin/orders/'.$idorder.'/status');
		exit;

	});

	$app->get("/admin/orders/:idorder/delete", function($idorder) {

		User::verifyLogin();

		$order = new Order();

		$order->get((int)$idorder);

		$order->delete();

		header('Location: /admin/orders');
		exit;

	});

	$app->get("/admin/orders/:idorder", function($idorder) {

		User::verifyLogin();

		$order = new Order();

		$order->get((int)$idorder);

		$cart = $order->getCart();

		$page = new PageAdmin();

		$page->setTpl('order', [
			'order' => $order->getData(),
			'cart' => $cart->getData(),
			'products' => $cart->getProducts()
		]);

	});

	$app->get("/admin/orders", function(){

		User::verifyLogin();

		$search = isset($_GET['search']) ? $_GET['search'] : '';
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

		$pagination = $search ?
						Pagination::getPageSearch('orders', $search, $page)
						:
						Pagination::getPage('orders', $page);

		$pages = [];

		for ($x = 0; $x < $pagination['pages']; $x++)
		{
			
			array_push($pages, [
				'href' => '/admin/orders?'.http_build_query([
					'page' => $x+1,
					'search' => $search
				]),
				'text' => $x+1
			]);

		}

		$page = new PageAdmin();

		$page->setTpl('orders', [
			'orders' => $pagination['data'],
	    	"search" => $search,
	    	"pages" => $pages
		]);

	});

?>