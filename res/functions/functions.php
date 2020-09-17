<?php

	function formatPrice($vlprice)
	{

		return number_format((float)$vlprice, 2, ",", ".");

	}

?>