<?php

	use \Hcode\Model\User;
	use \Hcode\Model\Product;
	use \Hcode\Pagination;
	use \Hcode\PageAdmin;

	$app->get("/admin/products", function() {

		User::verifylogin();

		$search = isset($_GET['search']) ? $_GET['search'] : '';
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

		$pagination = $search ?
						Pagination::getPageSearch('products', $search, $page)
						:
						Pagination::getPage('products', $page);

		$pages = [];

		for ($x = 0; $x < $pagination['pages']; $x++)
		{
			
			array_push($pages, [
				'href' => '/admin/products?'.http_build_query([
					'page' => $x+1,
					'search' => $search
				]),
				'text' => $x+1
			]);

		}

		$page = new PageAdmin();

		$page->setTpl("products", [
			"products" => $pagination['data'],
	    	"search" => $search,
	    	"pages" => $pages
		]);

	});

	$app->get("/admin/products/create", function() {

		User::verifylogin();

		$page = new PageAdmin();

		$page->setTpl("products-create");

	});

	$app->post("/admin/products/create", function() {

		User::verifylogin();

		$product = new Product();

		$product->setData($_POST);

		$product->save();

		header("Location: /admin/products");
		exit;

	});

	$app->get("/admin/products/:idproduct", function($idproduct) {
		
		User::verifylogin();

		$product = new Product();

		$product->get((int)$idproduct);

		$page = new PageAdmin();

		$page->setTpl("products-update", [
			"product" => $product->getData()
		]);

	});

	$app->post("/admin/products/:idproduct", function($idproduct) {
		
		User::verifylogin();

		$product = new Product();

		$product->get((int)$idproduct);

		$product->setData($_POST);

		$product->setPhoto($_FILES["file"]);

		$product->save();

		header("Location: /admin/products");
		exit;

	});

	$app->get("/admin/products/:idproduct/delete", function($idproduct) {
		
		User::verifylogin();

		$product = new Product();

		$product->get((int)$idproduct);

		$product->delete();

		header("Location: /admin/products");
		exit;

	});

?>