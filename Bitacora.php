<?php namespace Modelos;

	class Bitacora
	{
		private $con;

		public function __construct(){
			$this->con = new Conexion();

		}

		public static function guardar($usuario, $descripcion){
			$con = new Conexion();
			$sql = "INSERT INTO bitacora (ID_usuario, fecha, Descripcion) VALUES ($usuario, now(), '$descripcion')";
			$resultado = $con->consultaRetorno($sql);
			if($resultado){
				return true;
			} else {
				return false;
			}
		}

		public static function mostrarPorUsuario($usuario_id){
			$con = new Conexion();
			$sql = "SELECT ID_usuario, fecha, Descripcion FROM bitacora WHERE ID_usuario = $usuario_id";
			$resultado = $con->consultaRetorno($sql);
			$historial = [];
			while($row = mysqli_fetch_object($resultado)){
				$historial[] = $row;
			}
			return $historial;
		}

		public static function mostrarAdmin() {
			$con = new Conexion();
			$sql = "SELECT ID_usuario, fecha, Descripcion FROM bitacora b INNER JOIN usuarios u ON b.ID_usuario = u.ID WHERE ID_rol != 1";
			$resultado = $con->consultaRetorno($sql);
			$historial = [];
			while($row = mysqli_fetch_object($resultado)){
				$historial[] = $row;
			}
			return $historial;
		}
	}