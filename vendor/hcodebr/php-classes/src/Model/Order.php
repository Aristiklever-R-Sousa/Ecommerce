<?php

	namespace Hcode\Model;

	use \Hcode\DB\Sql;
	use \Hcode\Model;
	use \Hcode\Model\Cart;

	class Order extends Model
	{

		const SUCCESS = "Order-Success";
		const ERROR = "Order-Error";

		public function save()
		{

			$sql = new Sql();

			$results = $sql->select("
				CALL sp_orders_save(
					:idorder,:idcart,
					:iduser,:idstatus,
					:idaddress,:vltotal
				)
			", [
				':idorder' => $this->getidorder(),
				':idcart' => $this->getidcart(),
				':iduser' => $this->getiduser(),
				':idstatus' => $this->getidstatus(),
				':idaddress' => $this->getidaddress(),
				':vltotal' => $this->getvltotal()
			]);

			if(count($results))
				$this->setData($results[0]);
		}

		public function get($idorder)
		{

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_orders o
				INNER JOIN tb_ordersstatus os
					ON o.idstatus = os.idstatus
				INNER JOIN tb_carts c
					ON o.idcart = c.idcart
				INNER JOIN tb_users u
					ON o.iduser = u.iduser
				INNER JOIN tb_addresses a
					ON o.idaddress = a.idaddress
				INNER JOIN tb_persons p
					ON u.idperson = p.idperson
				WHERE o.idorder = :idorder
			", [
				':idorder' => $idorder
			]);

			if(count($results))
				$this->setData($results[0]);
			
		}

		public static function listAll()
		{

			$sql = new Sql();

			return $sql->select("
				SELECT *
				FROM tb_orders o
				INNER JOIN tb_ordersstatus os
					ON o.idstatus = os.idstatus
				INNER JOIN tb_carts c
					ON o.idcart = c.idcart
				INNER JOIN tb_users u
					ON o.iduser = u.iduser
				INNER JOIN tb_addresses a
					ON o.idaddress = a.idaddress
				INNER JOIN tb_persons p
					ON u.idperson = p.idperson
				ORDER BY o.dtregister DESC
			");

		}

		public function delete()
		{

			$sql = new Sql();

			$sql->query("
				DELETE FROM tb_orders
				WHERE idorder = :idorder
			", [
				':idorder' => $this->getidorder()
			]);

		}

		public function getCart():Cart
		{

			$cart = new Cart();

			$cart->get((int)$this->getidcart());

			return $cart;

		}

		public static function setSuccess($msg)
		{

			$_SESSION[Order::SUCCESS] = $msg;

		}

		public static function getSuccess()
		{

			$msg = isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]
					? $_SESSION[Order::SUCCESS] : "";

			Order::clearSuccess();

			return $msg;

		}

		public static function clearSuccess()
		{

			$_SESSION[Order::SUCCESS] = NULL;

		}

		public static function setError($msg)
		{

			$_SESSION[Order::ERROR] = $msg;

		}

		public static function getError()
		{

			$msg = isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]
					? $_SESSION[Order::ERROR] : "";

			Order::clearError();

			return $msg;

		}

		public static function clearError()
		{

			$_SESSION[Order::ERROR] = NULL;

		}

	}

?>