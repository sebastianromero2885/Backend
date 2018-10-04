<?php namespace Modelos;

	class Usuario {

		public $nombre;
		public $apellido;
		public $nombre_usuario;
		public $password;
		public $tipo;
		private $con;

		public function __construct(){
			$this->con = new Conexion();
		}

		public static function obtenerPassword($usuario) {
			$con = New Conexion();
			$sql = "SELECT password, nombre_cuenta_usuario FROM usuarios WHERE nombre_cuenta_usuario = '$usuario'";
			$resultado = $con->consultaRetorno($sql);
			if(mysqli_num_rows($resultado)){
				$row = mysqli_fetch_object($resultado);
				return ["password" => $row->password, "nombre_usuario" => $row->nombre_cuenta_usuario];
			} else {
				return false;
			}

		}

		public static function registrarAlumno ($legajo, $nombre_usuario, $password, $ID_rol, $email, $numero_documento) {
			$con = new Conexion();
			if($legajo) {
				$sqlAlumno = "SELECT 1 FROM alumnos WHERE numero_documento = '$numero_documento' and legajo = $legajo";
				$resultadoAlumno = $con->consultaRetorno($sqlAlumno);
				if($resultadoAlumno->num_rows==0){
					return false;
				}
				$sql = "INSERT usuarios (nombre_cuenta_usuario, password, correoElectronico, legajo, ID_rol ) VALUES ('$nombre_usuario', '$password', '$email', $legajo, $ID_rol)";
			} else {
				$sql = "INSERT usuarios (nombre_cuenta_usuario, password, correoElectronico, ID_rol ) VALUES ('$nombre_usuario', '$password', '$email', $ID_rol)";
			}
			$resultado = $con->consultaRetorno($sql);
			if($resultado) {
				return true;
			} else {
				return false;
			}
		}

		public static function obtenerDatos($nombre_usuario) {
			$con = new Conexion();
			//InTec se agrego a la consulta a.numero_documento.
			$sql = "SELECT u.ID, u.legajo, u.ID_rol, u.nombre_cuenta_usuario, u.correoElectronico, a.nombre, a.apellido, a.numero_documento FROM usuarios u LEFT JOIN alumnos a ON a.legajo = u.legajo WHERE u.nombre_cuenta_usuario = '$nombre_usuario'";
			$resultado = $con->consultaRetorno($sql);
			if($row = mysqli_fetch_object($resultado)) {
				if($row->ID_rol == 1){
					return [
						"id" => $row->ID,
						"legajo" => $row->legajo,
						"apellido" => $row->apellido,
						"nombre" => $row->nombre,
						"tipo_usuario" => $row->ID_rol,
						"nombre_usuario" => $row->nombre_cuenta_usuario,
						"email" => $row->correoElectronico,
						//InTec
						"documento" => $row->numero_documento,
					];
				} else {
					return [
						"id" => $row->ID,
						"nombre_usuario" => $row->nombre_cuenta_usuario,
						"tipo_usuario" => $row->ID_rol,
						"email" => $row->correoElectronico
					];
				}
			}
		}

		public static function login($usuario, $password) {
			$con = new Conexion();
			$sql = "SELECT password FROM usuarios WHERE nombre_cuenta_usuario = '$usuario'";
			$resultado = $con->consultaRetorno($sql);
			if($resultado->num_rows != 0) {
				$row = mysqli_fetch_object($resultado);
				if (password_verify($password, $row->password)){
					return ["estado" => "iniciar"];
				} else {
					return ["estado" => "error", "mensaje" => "Datos incorrectos"];
				}
			} else {
				$sql = "SELECT legajo FROM alumnos WHERE numero_documento = '$usuario' and numero_documento = '$password' and numero_documento != '0'";
				$resultado = $con->consultaRetorno($sql);
				if($resultado->num_rows != 0){
					$row = mysqli_fetch_object($resultado);
					$sql = "SELECT 1 FROM usuarios WHERE legajo = $row->legajo";
					$resultado = $con->consultaRetorno($sql);
					if($resultado->num_rows != 0) {
						return ["estado" => "error", "mensaje" => "Alumno ya registrado"];
					} else {
						return ["estado" => "registrar"];
					}	
				} else {
					return ["estado" => "error", "mensaje" => "Datos incorrectos"];
				}
			}
		}

		public static function actualizar($email, $nombre_usuario, $password_actual = null, $password_nuevo = null) {
			$con = new Conexion();
			if($password_actual) {
				$usuario = self::obtenerPassword($nombre_usuario);
				if(password_verify($password_actual, $usuario["password"])){
					$password_nuevo = password_hash($password_nuevo, PASSWORD_DEFAULT);
					$sql = "UPDATE usuarios SET correoElectronico = '$email', password = '$password_nuevo' WHERE nombre_cuenta_usuario = '$nombre_usuario'";
					$resultado = $con->consultaRetorno($sql);
					if($resultado) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				$sql = "UPDATE usuarios SET correoElectronico = '$email' WHERE nombre_cuenta_usuario = '$nombre_usuario'";
				$resultado = $con->consultaRetorno($sql);
				if($resultado) {
					return true;
				} else {
					return false;
				}
			}				
		}

		public static function reiniciar($nombre_usuario) {
			$con = new Conexion();
			$sql = "DELETE FROM usuarios WHERE nombre_cuenta_usuario = '$nombre_usuario'";
			$resultado = $con->consultaRetorno($sql);
			if($resultado){
				return true;
			} else {
				return false;
			}
		}

		public static function preceptores() {
			$con = new Conexion();
			$sql = "SELECT ID, correoElectronico, nombre_cuenta_usuario FROM usuarios WHERE ID_rol = 2";
			$resultado = $con->consultaRetorno($sql);
			$noticias = [];
			while($row = mysqli_fetch_object($resultado)){
				$noticias[] = $row;
			}

			return $noticias;
		}

		public static function editarPassword($password, $nombre_usuario) {
			$con = new Conexion();
			$password = password_hash($password, PASSWORD_DEFAULT);
			$sql = "UPDATE usuarios SET password = '$password' WHERE nombre_cuenta_usuario = '$nombre_usuario'";
			$resultado = $con->consultaRetorno($sql);
			if($resultado){
				return true;
			} else {
				return false;
			}
		}
	}
