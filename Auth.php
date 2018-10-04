<?php namespace Modelos;
	require_once 'vendor/autoload.php';
	use \Firebase\JWT\JWT;
	use \Exception;
	use \Firebase\JWT\ExpiredException;
	use \Firebase\JWT\SignatureInvalidException;


	class Auth
	{
		private static $clave_secreta = "isft_179_clave";
		private static $encriptacion = ['HS256'];
		private static $aud = null;

		public function __construct(){

		}

		public static function autenticar($datos){
			$time = time();

			$token = [
				'iat' => $time,
				'exp' => $time + (60 * 60),
				'aud' => self::Aud(),
				'data' => $datos
			];

			return JWT::encode($token, self::$clave_secreta);
		}

		public static function verificar($token){
			if(empty($token)){
				throw new Exception('Token enviado invalido.');
			}
			try {
				$tokenArray = JWT::decode($token, self::$clave_secreta, self::$encriptacion);
			}
			catch (\Firebase\JWT\ExpiredException $e) {
				return ["error" => "El token ha expirado"];
			}
			catch (\Firebase\JWT\SignatureInvalidException $e){
				return ["error" => "Token no verificado"];
			}
			if($tokenArray->aud !== self::Aud()) {
      	return ["error" => "Usuario Invalido"];
      }

		}

		public static function obtenerDatos($token){
			try {
				return JWT::decode(
					$token,
					self::$clave_secreta,
					self::$encriptacion
				)->data;
			} catch(\UnexpectedValueException $e) {
				return $e->getMessage();
			} catch(ExpiredException $e) {
				return $e->getMessage();
			}

		}

		private static function Aud() {
      $aud = '';

      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $aud = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
        $aud = $_SERVER['REMOTE_ADDR'];
      }

      $aud .= @$_SERVER['HTTP_USER_AGENT'];
      $aud .= gethostname();

      return sha1($aud);
    }
	}
