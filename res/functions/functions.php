<?php
	
	use \Hcode\Model\User;
	use \Hcode\Model\Cart;

	function formatPrice($vlprice)
	{

		if(!$vlprice) $vlprice = 0;

		return number_format((float)$vlprice, 2, ",", ".");

	}

	function formatDate($date)
	{

		return date('d/m/Y', strtotime($date));

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

	function getCartNrQtd()
	{

		$cart = Cart::getFromSession();

		$totals = $cart->getProductsTotals();

		return $totals['nrqtd'];

	}

	function getCartSubTotal()
	{

		$cart= Cart::getFromSession();

		$totals = $cart->getProductsTotals();

		return formatPrice($totals['vlprice']);

	}

?>