<?php
	
	use \Hcode\Model\User;

	function formatPrice($vlprice)
	{

		return number_format((float)$vlprice, 2, ",", ".");

	}

	function checkLogin($inadmin = true)
	{

		return User::checkLogin($inadmin);

	}

	function getUserName()
	{

		$user = User::getFromSession();

		return $user->getdesperson();

	}

?>