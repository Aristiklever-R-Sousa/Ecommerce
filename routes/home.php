<?php 

	use \Hcode\Page;
	use \Hcode\Model\Product;
	use \Hcode\Model\Category;
	use \Hcode\Model\Cart;
	use \Hcode\Model\User;
	use \Hcode\Model\Address;
	use \Hcode\Model\Order;
	use \Hcode\Model\OrderStatus;

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

		header("Location: /order/".$order->getidorder());
		exit();

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

	$app->get('/order/:idorder', function($idorder) {

		User::verifyLogin(false);

		$order = new Order();

		$order->get((int)$idorder);

		$page = new Page();

		$page->setTpl("payment", [
			'order' => $order->getData()
		]);

	});

	$app->get('/boleto/:idorder', function($idorder) {

		User::verifyLogin(false);

		$order = new Order();

		$order->get((int)$idorder);

		// DADOS DO BOLETO PARA O SEU CLIENTE
		$dias_de_prazo_para_pagamento = 10;
		$taxa_boleto = 5.00;
		$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
		$valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
		$valor_cobrado = str_replace(",", ".",$valor_cobrado);
		$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

		$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
		$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
		$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
		$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
		$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
		$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

		// DADOS DO SEU CLIENTE
		$dadosboleto["sacado"] = $order->getdespersons();
		$dadosboleto["endereco1"] = $order->getdesaddress(). ", " . $order->desdistrict();
		$dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - " . $order->getdescountry() . " - CEP: " . $order->getdeszipcode();

		// INFORMACOES PARA O CLIENTE
		$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
		$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
		$dadosboleto["demonstrativo3"] = "";
		$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
		$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
		$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
		$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

		// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
		$dadosboleto["quantidade"] = "";
		$dadosboleto["valor_unitario"] = "";
		$dadosboleto["aceite"] = "";		
		$dadosboleto["especie"] = "R$";
		$dadosboleto["especie_doc"] = "";


		// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


		// DADOS DA SUA CONTA - ITAÚ
		$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
		$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
		$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

		// DADOS PERSONALIZADOS - ITAÚ
		$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

		// SEUS DADOS
		$dadosboleto["identificacao"] = "Hcode Treinamentos";
		$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
		$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
		$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
		$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

		// NÃO ALTERAR!
		$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'boletophp' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR;

		require_once($path . 'funcoes_itau.php');
		require_once($path . 'layout_itau.php');

	});

?>