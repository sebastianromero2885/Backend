<?php namespace Modelos;

	class Carrera
	{

		public static $tabla = "carreras";

		private $codigo_carrera;
		private $nombre;
		private $con;

		public function __construct(){
			$this->con = new Conexion();

		}

		public function get($atributo ="todos"){
			if($atributo =="todos"){
				$carrera = [
							"codigo_carrera" 	=> $this->codigo_carrera,
							"nombre"		=> $this->nombre
				];
				return $carrera;
			} else {
				return $this->$atributo;
			}
		}

		public static function buscar($cod_carrera){

			$con = new Conexion();
			$sql = "SELECT 
					codigo_carrera, nombre FROM " . self::$tabla 
					." where codigo_carrera=$cod_carrera";
			$carrera = $con->consultaRetorno($sql);
			$row = mysqli_fetch_object($carrera);

			$carrera = new Carrera();
			$carrera->codigo_carrera = $row->codigo_carrera; 
			$carrera->nombre = $row->nombre;

			return $carrera;
		}

		public static function todos(){
			$con = new Conexion();
			$sql = "SELECT codigo_carrera, nombre FROM " . self::$tabla;

			$carreras = $con->consultaRetorno($sql);
			$resultados = [];
			while($row = mysqli_fetch_object($carreras)){
				array_push($resultados,$row);
			}

			return $resultados;
		}

		public function materias(){
			$sql = "SELECT codigo_materia, nombre FROM materias where codigo_carrera = $this->codigo_carrera";
			$materias = $this->con->consultaRetorno($sql);
			$resultados = [];
			while($row = mysqli_fetch_object($materias)){
				array_push($resultados,$row);
			}

			return $resultados;

		}
	}