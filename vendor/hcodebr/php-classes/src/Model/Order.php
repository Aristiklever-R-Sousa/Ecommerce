<?php

	namespace Hcode\Model;

	use \Hcode\DB\Sql;
	use \Hcode\Model;

	class Order extends Model
	{

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



	}

?>