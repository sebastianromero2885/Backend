<?php namespace Modelos;

	class Materia
	{
		public static $tabla = "materias";
		private $codigo_materia;
		private $nombre;
		private $codigo_carrera;
		private $fecha_finales = [];
		private $con;

		public function __construct(){
			$this->con = new Conexion();
		}

		public function get($atributo = "todo"){

			if($atributo=="todo"){
				$materia = [
						"codigo_materia" 	=>$this->codigo_materia,
						"codigo_carrera" 	=>$this->codigo_carrera,
						"nombre"			=>$this->nombre,
						"fecha_finales"		=>$this->fecha_finales

				];

				return $materia;
			}
		}

		public static function buscar($codigo_materia, $codigo_carrera){
			
			$con = new Conexion();
			$sql = "SELECT codigo_materia, nombre, codigo_carrera FROM " . self::$tabla 
					." WHERE codigo_materia=$codigo_materia and codigo_carrera=$codigo_carrera";
			$resultado = $con->consultaRetorno($sql);
			$row = mysqli_fetch_object($resultado);

			$materia = new Materia();
			$materia->codigo_materia = $row->codigo_materia;
			$materia->nombre = $row->nombre;
			$materia->codigo_carrera = $row->codigo_carrera;
			$materia->fecha_finales = $materia->fechasFinales();

			return $materia;
		}

		public static function todos(){
			$con = new Conexion();
			$sql = "SELECT codigo_materia, nombre, codigo_carrera FROM " . self::$tabla;

			$materias = $con->consultaRetorno($sql);
			$resultados = [];
			while($row = mysqli_fetch_object($materias)){
				array_push($resultados,$row);
			}

			return $resultado;
		}

		public function correlativas(){
			$sql = "SELECT m.codigo_materia, m.codigo_carrera, m.nombre FROM materias m
			INNER JOIN correlativas c ON m.codigo_materia = c.codigo_correlativa
			WHERE c.codigo_materia = $this->codigo_materia AND m.codigo_carrera = $this->codigo_carrera";

			$materias_correlativas = $this->con->consultaRetorno($sql);
			$resultados = [];
			while($row = mysqli_fetch_object($materias_correlativas)){
				array_push($resultados,$row);
			}

			return $resultados;

		}

		public function fechasFinales(){
			$sql = "SELECT fecha_1, fecha_2 FROM examenes WHERE codigo_materia = $this->codigo_materia AND codigo_carrera = $this->codigo_carrera AND fecha_1>CURDATE()";

			$fechas = [];
			$resultados = $this->con->consultaRetorno($sql);
			while($row = mysqli_fetch_object($resultados)){
				$fechas[] = $row->fecha_1;
				$fechas[] = $row->fecha_2;
			}

			return $fechas;
		}

	}
	