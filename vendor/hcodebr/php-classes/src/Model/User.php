<?php

	namespace Hcode\Model;

	use \Hcode\DB\Sql;
	use \Hcode\Model;
	use \Hcode\Mailer;

	class User extends Model{

		const SESSION = "User";
		const SECRET = "HcodePhp7_Secret";
		const SECRET_IV = "HcodePhp7_Secret_IV";
		const ERROR = "UserError";
		const ERROR_REGISTER = "UserErrorResgister";
		const SUCCESS = "UserSuccess";

		public static function getFromSession()
		{

			$user = new User();

			if(
				isset($_SESSION[User::SESSION])
				&&
				(int)$_SESSION[User::SESSION]['iduser']
			)
				$user->setData($_SESSION[User::SESSION]);

			return $user;

		}

		public static function checkLogin($inadmin = false)
		{

			if(
				!isset($_SESSION[User::SESSION])
				||
				!$_SESSION[User::SESSION]
				||
				!(int)$_SESSION[User::SESSION]["iduser"]
			)

				return false;
			
			elseif(!$inadmin || (bool)$_SESSION[User::SESSION]['inadmin'])

				return true;
			
			else

				return false;

		}

		public static function verifyLogin($inadmin = true)
		{

			if(!User::checkLogin($inadmin)) {
				
				if($inadmin)
					header("Location: /admin/login");
				else
					header("Location: /login");

				exit("Try again!");
			
			}


		}

		public static function login($login, $password)
		{

			$sql = new Sql();

			$results = $sql->select("
				SELECT * 
				FROM tb_users a
				INNER JOIN tb_persons b
					ON a.idperson = b.idperson
				WHERE a.deslogin = :LOGIN", array(
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
				
				$data['desperson'] = utf8_encode($data['desperson']);

				$user->setData($data);
			
				$_SESSION[User::SESSION] = $user->getData();

			}else{
				throw new \Exception("User unexistent or password invalid.");
			}
				
		}

		public static function logout()
		{

			$_SESSION[User::SESSION] = NULL;
		
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

			$data = $results[0];

			$data['desperson'] = utf8_encode($data['desperson']);

			$this->setData($data);

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
					":deslogin" => utf8_decode($this->getdeslogin()),
					":despassword" => User::getPasswordHash($this->getdespassword()),
					":desemail" => $this->getdesemail(),
					":nrphone" => $this->getnrphone(),
					":inadmin" => $this->getinadmin()
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
					":desperson" => utf8_decode($this->getdesperson()),
					":deslogin" => $this->getdeslogin(),
					":despassword" => User::getPasswordHash($this->getdespassword()),
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

		public static function getForgot($email, $inadmin = true)
		{

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_persons a
				INNER JOIN tb_users b USING(idperson)
				WHERE a.desemail = :email;
			", array(
				":email" => $email
			));

			if (!count($results))
				throw new \Exception("Not was possible recover the password.");
			else
			{
				$data = $results[0];

				$resultsRecover = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
					":iduser" => $data["iduser"],
					":desip" => $_SERVER["REMOTE_ADDR"]
				));

				if(!count($resultsRecover))
					throw new \Exception("Not was possible recover the password.");
				else
				{
					$dataRecover = $resultsRecover[0];

					$encrypt = openssl_encrypt(
						$dataRecover['idrecovery'],
						'AES-128-CBC',
						pack("a16", User::SECRET),
						0,
						pack("a16", User::SECRET_IV)
					);

					$code = base64_encode($encrypt);

					$link = $inadmin
							? "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code"
							: "http://www.hcodecommerce.com.br/forgot/reset?code=$code";

					$mailer = new Mailer(
						$data["desemail"], $data["desperson"],
						"Redefinir Senha da Hcode Store", "forgot",
						array(
							"name" => $data["desperson"],
							"link" => $link
					));

					$mailer->send();

					return $data;

				}

			}

			return [];

		}

		public static function validForgotDecrypt($code)
		{

			$idrecovery = openssl_decrypt(
				base64_decode($code),
				'AES-128-CBC',
				pack("a16", User::SECRET),
				0,
				pack("a16", User::SECRET_IV)
			);

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_userspasswordsrecoveries a
				INNER JOIN tb_users b USING(iduser)
				INNER JOIN tb_persons c USING(idperson)
				WHERE
					a.idrecovery = :idrecovery
					AND
					a.dtrecovery IS NULL
					AND
					DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
			", array(
				":idrecovery"=>$idrecovery
			));

			if(!count($results))
				throw new \Exception("Not was possible recover the password.");
			else
				return $results[0];

		}

		public static function setForgotUsed($idrecovery)
		{

			$sql = new Sql();

			$sql->query("
				UPDATE tb_userspasswordsrecoveries
				SET dtrecovery = NOW()
				WHERE idrecovery = :idrecovery
			", array(
				":idrecovery" => $idrecovery
			));

		}

		public function setPassword($password)
		{
			$sql = new Sql();

			$sql->query("
				UPDATE tb_users
				SET despassword = :password
				WHERE iduser = :iduser
			", array(
				":password" => User::getPasswordHash($password),
				":iduser" => $this->getiduser()
			));
		}

		public static function setSuccess($msg)
		{

			$_SESSION[User::SUCCESS] = $msg;

		}

		public static function getSuccess()
		{

			$msg = isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]
					? $_SESSION[User::SUCCESS] : "";

			User::clearSuccess();

			return $msg;

		}

		public static function clearSuccess()
		{

			$_SESSION[User::SUCCESS] = NULL;

		}

		public static function setError($msg)
		{

			$_SESSION[User::ERROR] = $msg;

		}

		public static function getError()
		{

			$msg = isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]
					? $_SESSION[User::ERROR] : "";

			User::clearError();

			return $msg;

		}

		public static function clearError()
		{

			$_SESSION[User::ERROR] = NULL;

		}

		public static function setErrorRegister($msg)
		{

			$_SESSION[User::ERROR_REGISTER] = $msg;

		}

		public static function getErrorRegister()
		{

			$msg = isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]
					? $_SESSION[User::ERROR_REGISTER] : "";

			User::clearErrorRegister();

			return $msg;

		}

		public static function clearErrorRegister()
		{

			$_SESSION[User::ERROR_REGISTER] = NULL;

		}

		public static function checkLoginExist($login)
		{

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_users
				WHERE deslogin = :deslogin
			", [
				':deslogin' => $login
			]);

			return count($results) > 0;

		}

		public static function getPasswordHash($password)
		{

			return password_hash($password, PASSWORD_DEFAULT, [
				'cost' => 12
			]);


		}

		public function getOrders()
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
				WHERE o.iduser = :iduser
			", [
				':iduser' => $this->getiduser()
			]);

			return $results;

		}

	}

?>