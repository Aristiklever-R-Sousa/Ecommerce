<?php

	namespace Hcode\Model;

	use \Hcode\Model;
	use \Hcode\Message;
	use \Hcode\DB\Sql;
	use \Hcode\Model\User;

	class Cart extends Model{

		public static function formatValueToDecimal($value):float
		{

			$value = str_replace('.', '', $value);

			return (float)str_replace(',', '.', $value);

		}

		public function setToSession()
		{

			$_SESSION[Cart::SESSION] = $this->getData();

		}

		public function get($idcart)
		{

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_carts
				WHERE idcart = :idcart
			", [
				":idcart" => $idcart
			]);

			if(count($results) > 0)
				$this->setData($results[0]);

		}

		public function getFromSessionID()
		{

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_carts
				WHERE dessessionid = :dessessionid
			", [
				":dessessionid" => session_id()
			]);

			if(count($results) > 0)
				$this->setData($results[0]);

		}

		public static function getFromSession()
		{

			$cart = new Cart();

			if (
				isset($_SESSION[Cart::SESSION])
				&&
				(int)$_SESSION[Cart::SESSION]['idcart'] > 0
			)
				// Se já tem um
				$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
			
			else {

				// Se não existir um carrinho, criará
				$cart->getFromSessionID();

				if(!(int)$cart->getidcart()) {
				
					$data = [
						'dessessionid' => session_id()
					];
					
					if(User::checkLogin()) {
						
						$user = User::getFromSession();
						
						$data['iduser'] = $user->getiduser();

					}

					$cart->setData($data);

					$cart->save();

					$cart->setToSession();

				}

			}

			return $cart;

		}

		public function save()
		{

			$sql = new Sql();

			$results = $sql->select(
				"CALL sp_carts_save(
					:idcart, :dessessionid,
					:iduser, :deszipcode,
					:vlfreight, :nrdays
				)",
				array(
					":idcart" => $this->getidcart(),
					":dessessionid" => $this->getdessessionid(),
					":iduser" => $this->getiduser(),
					":deszipcode" => $this->getdeszipcode(),
					":vlfreight" => $this->getvlfreight(),
					":nrdays" => $this->getnrdays()
				)
			);

			$this->setData($results[0]);

		}

		public function addProduct(Product $product)
		{

			$sql = new Sql();

			$sql->query("
				INSERT INTO tb_cartsproducts (idcart, idproduct)
				VALUES (:idcart, :idproduct)
			", [
				':idcart' => $this->getidcart(),
				':idproduct' => $product->getidproduct()
			]);

			$this->getCalculateTotal();

		}

		public function removeProduct(Product $product, $all = false)
		{

			$sql = new Sql();

			if($all)

				$sql->query("
					UPDATE tb_cartsproducts
					SET dtremoved = NOW()
					WHERE idcart = :idcart
					AND idproduct = :idproduct
					AND dtremoved IS NULL
				", [
					':idcart' => $this->getidcart(),
					':idproduct' => $product->getidproduct()
				]);

			 else

				$sql->query("
					UPDATE tb_cartsproducts
					SET dtremoved = NOW()
					WHERE idcart = :idcart
					AND idproduct = :idproduct
					AND dtremoved IS NULL
					LIMIT 1
				", [
					':idcart' => $this->getidcart(),
					':idproduct' => $product->getidproduct()
				]);

			$this->getCalculateTotal();

		}

		public function getProducts()
		{

			$sql = new Sql();

			return Product::checkList(
				$sql->select("
					SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth,
						   b.vlheight, b.vllength, b.vlweight, b.desurl,
						   COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
					FROM tb_cartsproducts a
					INNER JOIN tb_products b
						ON a.idproduct = b.idproduct
					WHERE a.idcart = :idcart
					AND a.dtremoved IS NULL
					GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth,
							 b.vlheight, b.vllength, b.vlweight, b.desurl
					ORDER BY b.desproduct
				", [
					':idcart' => $this->getidcart()
				])
			);

		}

		public function getProductsTotals()
		{

			$sql = new Sql();

			$results = $sql->select("
				SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth,
					   SUM(vlheight) AS vlheight, SUM(vllength) AS vllength,
					   SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
				FROM tb_products a
				INNER JOIN tb_cartsproducts b
					ON a.idproduct = b.idproduct
				WHERE b.idcart = :idcart
				AND b.dtremoved IS NULL
			", [
				':idcart' => $this->getidcart()
			]);

			// Se a quantidade de elementos dento do array results for maior que zero, retornamos ele na posição 0
			if (count($results))
				return $results[0];

			return [];

		}

		public function updateFreight()
		{

			if ($this->getdeszipcode() != '')
				$this->setFreight($this->getdeszipcode());

		}

		public function setFreight($nrzipcode)
		{

			$nrzipcode = str_replace('-', '', $nrzipcode);

			$totals = $this->getProductsTotals();

			if($totals['nrqtd']) {

				$totals['vlheight'] = $totals['vlheight'] < 2 ? 2 : $totals['vlheight'];
				$totals['vllength'] = $totals['vllength'] < 16 ? 16 : $totals['vllength'];

				$qs = http_build_query([
					'nCdEmpresa' => '',
					'sDsSenha' => '',
					'nCdServico' => '40010',
					'sCepOrigem' => '09853120',
					'sCepDestino' => $nrzipcode,
					'nVlPeso' => $totals['vlweight'],
					'nCdFormato' => '1',
					'nVlComprimento' => $totals['vllength'],
					'nVlAltura' => $totals['vlheight'],
					'nVlLargura' => $totals['vlwidth'],
					'nVlDiametro' => '0',
					'sCdMaoPropria' => 'S',
					'nVlValorDeclarado' => $totals['vlprice'],
					'sCdAvisoRecebimento' => 'S'
				]);

				$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?". $qs);

				$result = $xml->Servicos->cServico;

				if ($result->MsgErro != '')

					Message::setError($result->MsgErro, 'Cart');

				else
					
					Message::clearError('Cart');
					

				$this->setnrdays($result->PrazoEntrega);
				$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
				$this->setdeszipcode($nrzipcode);

				$this->save();

				return $result;

			} elseif(!$nrzipcode) {
				$this->setvlfreight(0);
				$this->setnrdays(0);
				$this->setdeszipcode($nrzipcode);
				$this->save();
			}

		}

		public function getCalculateTotal()
		{

			$this->updateFreight();

			$totals = $this->getProductsTotals();

			$this->setvlsubtotal($totals['vlprice']);
			$this->setvltotal($totals['vlprice'] + $this->getvlfreight());

		}

		public function getData()
		{

			$this->getCalculateTotal();

			return parent::getData();

		}

	}

?>