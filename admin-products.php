<?php

	use \Hcode\Model\User;
	use \Hcode\Model\Product;
	use \Hcode\PageAdmin;

	$app->get("/admin/products", function() {

		User::verifylogin();

		$products = Product::listAll();

		$page = new PageAdmin();

		$page->setTpl("products", [
			"products" => $products
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