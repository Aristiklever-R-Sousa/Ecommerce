<?php
	
	namespace Hcode;

	class Message {

		const ERROR = [
			'User' => "UserError",
			'Cart' => "CartError",
			'Address' => "AddressError",
			'Order' => "Order-Error"
		];

		const ERROR_REGISTER = [
			'User' => "UserErrorResgister"
		];

		const SUCCESS = [
			'User' => "UserSuccess",
			'Order' => "Order-Success"
		];

		public static function setError($msg, $actor = 'User')
		{

			$_SESSION[Message::ERROR[$actor]] = $msg;

		}

		public static function getError($actor = 'User')
		{

			$msg = isset($_SESSION[Message::ERROR[$actor]]) && $_SESSION[Message::ERROR[$actor]]
					? $_SESSION[Message::ERROR[$actor]] : "";

			Message::clearError($actor);

			return $msg;

		}

		public static function clearError($actor = 'User')
		{

			$_SESSION[Message::ERROR[$actor]] = NULL;

		}

		public static function setSuccess($msg, $actor = 'User')
		{

			$_SESSION[Message::SUCCESS[$actor]] = $msg;

		}

		public static function getSuccess($actor = 'User')
		{

			$msg = isset($_SESSION[Message::SUCCESS[$actor]])
				   &&
				   $_SESSION[Message::SUCCESS[$actor]]
				   ?
				   $_SESSION[Message::SUCCESS[$actor]] : "";

			Message::clearSuccess($actor);

			return $msg;

		}

		public static function clearSuccess($actor = 'User')
		{

			$_SESSION[Message::SUCCESS[$actor]] = NULL;

		}

		public static function setErrorRegister($msg, $actor = 'User')
		{

			$_SESSION[Message::ERROR_REGISTER[$actor]] = $msg;

		}

		public static function getErrorRegister($actor = 'User')
		{

			$msg = isset($_SESSION[Message::ERROR_REGISTER[$actor]])
				   &&
				   $_SESSION[Message::ERROR_REGISTER[$actor]]
					? $_SESSION[Message::ERROR_REGISTER[$actor]] : "";

			Message::clearErrorRegister($actor);

			return $msg;

		}

		public static function clearErrorRegister($actor = 'User')
		{

			$_SESSION[Message::ERROR_REGISTER[$actor]] = NULL;

		}

	}

?>