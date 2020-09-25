<?php 

	use \Hcode\Page;
	use \Hcode\Model\Product;
	use \Hcode\Model\Category;
	use \Hcode\Model\Cart;
	use \Hcode\Model\User;
	use \Hcode\Model\Address;

	$app->get('/', function() {
	    
		$products = Product::listAll();

	    $page = new Page();

	    $page->setTpl("index", [
	    	"products" => Product::checkList($products)
	    ]);

	});

	$app->get("/categories/:idcategory", function($idcategory) {

		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

		$category = new Category();

		$category->get((int)$idcategory);

		$pagination = $category->getProductsPage($page);

		$pages = [];

		for ($i = 1; $i <= $pagination['pages']; $i++) { 
			array_push($pages, [
				'link' => '/categories/' .$category->getidcategory(). '?page='. $i,
				'page' => $i
			]);
		}

		$page = new Page();

	    $page->setTpl("category", [
	    	'category' => $category->getData(),
	    	'products' => $pagination['data'],
	    	'pages' => $pages
	    ]);

	});

	$app->get('/products/:desurl', function($desurl) {

		$product = new Product();

		$product->getFromURL($desurl);

		$page = new Page();

		$page->setTpl("product-detail", [
			'product' => $product->getData(),
			'categories' => $product->getCategories()
		]);

	});

	$app->get('/cart', function() {

		$cart = Cart::getFromSession();
		
		$page = new Page();

		$page->setTpl("cart", [
			'cart' => $cart->getData(),
			'products' => $cart->getProducts(),
			'error' => Cart::getMsgError()
		]);

	});

	$app->get('/cart/:idproduct/add', function($idproduct) {

		$product = new Product();

		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();

		$qtd = isset($_GET['qtd']) ? (int)$_GET['qtd'] : 1;

		for($i = 0; $i < $qtd; $i++)
			$cart->addProduct($product);

		header("Location: /cart");
		exit;

	});

	$app->get('/cart/:idproduct/minus', function($idproduct) {

		$product = new Product();

		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();

		$cart->removeProduct($product);

		header("Location: /cart");
		exit;

	});

	$app->get('/cart/:idproduct/remove', function($idproduct) {

		$product = new Product();

		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();

		$cart->removeProduct($product, true);

		header("Location: /cart");
		exit;

	});

	$app->post('/cart/freight', function() {

		$cart = Cart::getFromSession();

		$cart->setFreight($_POST['zipcode']);

		header('Location: /cart');
		exit;

	});

	$app->get('/checkout', function() {

		User::verifyLogin(false);

		$cart = Cart::getFromSession();

		$address = new Address();

		$page = new Page();

		$page->setTpl("checkout", [
			'cart' => $cart->getData(),
			'address' => $address->getData()
		]);

	});

	$app->get('/login', function() {

		$page = new Page();

		$page->setTpl('login',[
			'error' => User::getError(),
			'errorRegister' => User::getErrorRegister(),
			'registerValues' => isset($_SESSION['registerValues']) ?
								$_SESSION['registerValues'] :
								['email'=> '', 'name'=> '', 'phone'=> '']
		]);

	});

	$app->post('/login', function() {

		try {

			User::login($_POST['login'], $_POST['password']);

		} catch(Exception $e) {
			User::setError($e->getMessage());
		}

		header('Location: /checkout');
		exit();
		
	});

	$app->get('/logout', function() {

		User::logout();

		header('Location: /login');
		exit;

	});

	$app->post('/register', function() {

		$_SESSION['registerValues'] = $_POST;

		if(!isset($_POST['name']) || $_POST['name'] == '') {

			User::setErrorRegister("Fill in your name.");
			header('Location: /login');
			exit;

		}

		if(!isset($_POST['email']) || $_POST['email'] == '') {

			User::setErrorRegister("Fill in your email.");
			header('Location: /login');
			exit;

		}

		if (User::checkLoginExist($_POST['email'])) {
			
			User::setErrorRegister("This email is already in use. Enter another.");
			header('Location: /login');
			exit;
			
		}

		if(!isset($_POST['password']) || $_POST['password'] == '') {

			User::setErrorRegister("Fill in your password.");
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

		header('Location: /checkout');

		$_SESSION['registerValues'] = ['email'=> '', 'name'=> '', 'phone'=> ''];
		exit;

	});

?>