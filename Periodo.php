<?php namespace Modelos;

	class Periodo
	{
		public $fecha_inicio;
		public $fecha_fin;
		private $con;

		public function __construct($fecha_inicio, $fecha_fin){
			$this->con = new Conexion();
			$this->fecha_inicio = $fecha_inicio;
			$this->fecha_fin = $fecha_fin;
		}

		public function guardarFecha(){
			$sql = "DELETE FROM periodos";
			$resultado = $this->con->consultaRetorno($sql);
			$sql = "INSERT INTO periodos(ID_periodo, fecha_inicio, fecha_fin) value (null, '$this->fecha_inicio', '$this->fecha_fin')";
			$resultado = $this->con->consultaRetorno($sql);
			if($resultado) {
				return true;
			} else {
				return false;
			}
		}

		public static function fechas(){
			$con = new Conexion();
			$sql = "SELECT fecha_inicio, fecha_fin FROM periodos";
			$resultado = $con->consultaRetorno($sql);
			$fechas = mysqli_fetch_object($resultado);

			return $fechas;
		}

	}