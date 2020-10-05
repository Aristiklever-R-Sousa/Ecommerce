<?php

	namespace Hcode;

	use \Hcode\DB\Sql;

	class Pagination
	{

		public static function getPage($route, $page = 1, $itemsPerPage = 10)
		{

			$start = ($page - 1) * $itemsPerPage ;

			$sql = new Sql();

			switch ($route) {
				case 'users':
					$results = $sql->select("
						SELECT SQL_CALC_FOUND_ROWS *
						FROM tb_users u
						INNER JOIN tb_persons p
							ON u.idperson = p.idperson
						ORDER BY p.desperson
						LIMIT $start, $itemsPerPage;
					");
				break;
				
				case 'categories':
					$results = $sql->select("
						SELECT SQL_CALC_FOUND_ROWS *
						FROM tb_categories
						ORDER BY descategory
						LIMIT $start, $itemsPerPage;
					");
				break;
			}

			$resultTotal = $sql->select("
				SELECT FOUND_ROWS() AS nrtotal;
			");

			return [
				'data' => $results,
				'totalRows' => $resultTotal[0]['nrtotal'],
				'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
			];

		}

		public static function getPageSearch($route, $search, $page = 1, $itemsPerPage = 10)
		{

			$start = ($page - 1) * $itemsPerPage ;

			$sql = new Sql();

			switch ($route) {
				case 'users':
					$results = $sql->select("
						SELECT SQL_CALC_FOUND_ROWS *
						FROM tb_users u
						INNER JOIN tb_persons p
							ON u.idperson = p.idperson
						WHERE p.desperson LIKE :search
							OR p.desemail = :search
							OR u.deslogin LIKE :search
						ORDER BY p.desperson
						LIMIT $start, $itemsPerPage;
					", [
						':search' => '%'.$search.'%'
					]);
				break;
				
				case 'categories':
					$results = $sql->select("
						SELECT SQL_CALC_FOUND_ROWS *
						FROM tb_categories
						WHERE descategory LIKE :search
						ORDER BY descategory
						LIMIT $start, $itemsPerPage;
					", [
						':search' => '%'.$search.'%'
					]);
				break;
			}

			$resultTotal = $sql->select("
				SELECT FOUND_ROWS() AS nrtotal;
			");

			return [
				'data' => $results,
				'totalRows' => $resultTotal[0]['nrtotal'],
				'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
			];

		}

	}

?>