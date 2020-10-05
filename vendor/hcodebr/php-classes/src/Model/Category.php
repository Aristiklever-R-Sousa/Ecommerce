<?php

	namespace Hcode\Model;

	use \Hcode\DB\Sql;
	use \Hcode\Model;

	class Category extends Model{

		public function save()
		{

			$sql = new Sql();

			$results = $sql->select(
				"CALL sp_categories_save(
					:idcategory, :descategory
				)",
				array(
					":idcategory" => $this->getidcategory(),
					":descategory" => $this->getdescategory()
				)
			);

			$this->setData($results[0]);

			Category::updateFile();
		}

		public function get($idcategory)
		{

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_categories
				WHERE idcategory = :idcategory
			", [
				":idcategory" => $idcategory
			]);

			$this->setData($results[0]);

		}

		public function delete()
		{

			$sql = new Sql();

			$sql->query("
				DELETE FROM tb_categories
				WHERE idcategory = :idcategory
			", [
				":idcategory" => $this->getidcategory()
			]);

			Category::updateFile();
		}

		public static function updateFile()
		{

			$categories = Category::listAll();

			$html = [];

			foreach($categories as $row)
			{
				array_push($html, 
					'<li>'. PHP_EOL.
					'	<a href="/categories/'.$row['idcategory'].'">'. PHP_EOL.
					'		'.$row['descategory']. PHP_EOL.
					'	</a>'. PHP_EOL.
					'</li>'. PHP_EOL
				);

			}

			$filename = $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. "views" .DIRECTORY_SEPARATOR. "categories-menu.html";
			file_put_contents($filename, implode('', $html));

		}

		public function getProducts($releated = true)
		{

			$sql = new Sql();

			if($releated)
			{

				// Produtos que estão relacionados com o idcategory informado
				return (
					$sql->select("
						SELECT * 
						FROM tb_products 
						WHERE idproduct IN(
							SELECT a.idproduct
							FROM tb_products a
							INNER JOIN tb_productscategories b
							ON a.idproduct = b.idproduct
							WHERE b.idcategory = :idcategory
						);
					", [
						":idcategory" => $this->getidcategory()
					])
				);

			} else {

				// Produtos que não estão relacionados com o idcategory informado
				return (
					$sql->select("
						SELECT * 
						FROM tb_products 
						WHERE idproduct NOT IN(
							SELECT a.idproduct
							FROM tb_products a
							INNER JOIN tb_productscategories b
							ON a.idproduct = b.idproduct
							WHERE b.idcategory = :idcategory
						);
					", [
						':idcategory' => $this->getidcategory()
					])
				);

			}

		}

		public function getProductsPage($page = 1, $itemsPerPage = 8)
		{

			$start = ($page - 1) * $itemsPerPage ;

			$sql = new Sql();

			$results = $sql->select("
				SELECT SQL_CALC_FOUND_ROWS *
				FROM tb_products a
				INNER JOIN tb_productscategories b
					ON a.idproduct = b.idproduct
				INNER JOIN tb_categories c
					ON c.idcategory = b.idcategory
				WHERE c.idcategory = :idcategory
				LIMIT $start, $itemsPerPage;
			", [
				':idcategory' => $this->getidcategory()
			]);

			$resultTotal = $sql->select("
				SELECT FOUND_ROWS() AS nrtotal;
			");

			return [
				'data' => Product::checkList($results),
				'totalRows' => $resultTotal[0]['nrtotal'],
				'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
			];

		}

		public function addProduct(Product $product)
		{

			$sql = new Sql();

			$sql->query("
				INSERT INTO tb_productscategories (idcategory, idproduct)
				VALUES (:idcategory, :idproduct)
			", [
				':idcategory' => $this->getidcategory(),
				':idproduct' => $product->getidproduct()
			]);

		}

		public function removeProduct(Product $product)
		{

			$sql = new Sql();

			$sql->query("
				DELETE FROM tb_productscategories
				WHERE idcategory = :idcategory
				AND idproduct = :idproduct
			", [
				':idcategory' => $this->getidcategory(),
				':idproduct' => $product->getidproduct()
			]);

		}

	}

?>