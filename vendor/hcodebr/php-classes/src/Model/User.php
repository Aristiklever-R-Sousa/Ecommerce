<?php

	namespace Hcode\Model;

	use \Hcode\DB\Sql;
	use \Hcode\Model;

	class User extends Model{

		const SESSION = "User";

		public static function login($login, $password)
		{

			$sql = new Sql();

			$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
				":LOGIN" => $login
			));

			if (count($results) === 0)
			{
				throw new \Exception("User unexistent or password invalid!");
			}

			$data = $results[0];

			if (password_verify($password, $data["despassword"]) === true)
			{
			
				$user = new User();
			
				$user->setData($data);
			
				$_SESSION[User::SESSION] = $user->getData();

			}else{
				throw new \Exception("User unexistent or password invalid.");
			}
				
		}

		public static function verifyLogin($inadmin = true)
		{
			if(
				!isset($_SESSION[User::SESSION])
				||
				!$_SESSION[User::SESSION]
				||
				!(int)$_SESSION[User::SESSION]["iduser"] > 1
				||
				(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
			) {
				header("Location: /admin/login");
				exit("Tente novamente");
			}
		}

		public static function logout()
		{
			$_SESSION[User::SESSION] = NULL;
		}

		public static function listAll() 
		{
			$sql = new Sql();

			return $sql->select("SELECT * FROM tb_users a
									INNER JOIN
									tb_persons b
									USING(idperson)
									ORDER BY b.desperson"
								);

		}

		public function save()
		{

			$sql = new Sql();

			$results = $sql->select(
				"CALL sp_users_save(
					:desperson, :deslogin,
					:despassword, :desemail,
					:nrphone, :inadmin
				)",
				array(
					":desperson" => $this->getdesperson(),
					":deslogin" => $this->getdeslogin(),
					":despassword" => $this->getdespassword(),
					":desemail" => $this->getdesemail(),
					":nrphone" => $this->getnrphone(),
					":inadmin" => $this->getinadmin()
				)
			);

			$this->setData($results[0]);
		}

		public function get($iduser)
		{
			
			$sql = new Sql();

			$results = $sql->select(
				"SELECT * FROM tb_users a
				INNER JOIN tb_persons b
				USING(idperson)
				WHERE a.iduser = :iduser", array(
					":iduser" => $iduser
				)
			);

			$this->setData($results[0]);

		}

		public function update()
		{

			$sql = new Sql();

			$results = $sql->select(
				"CALL sp_usersupdate_save(
					:iduser,
					:desperson, :deslogin,
					:despassword, :desemail,
					:nrphone, :inadmin
				)",
				array(
					":iduser" => $this->getiduser(),
					":desperson" => $this->getdesperson(),
					":deslogin" => $this->getdeslogin(),
					":despassword" => $this->getdespassword(),
					":desemail" => $this->getdesemail(),
					":nrphone" => $this->getnrphone(),
					":inadmin" => $this->getinadmin()
				)
			);

			$this->setData($results[0]);
		}

		public function delete()
		{

			$sql = new Sql();

			$sql->query("CALL sp_users_delete(:iduser)", array(
				":iduser" => $this->getiduser()
			));

		}

	}

?>