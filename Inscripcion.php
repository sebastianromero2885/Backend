<?php namespace Modelos;

	class Inscripcion
	{
		private $con;

		public function __construct(){
			$this->con = new Conexion();

		}
		public static function cantidadPorCarreras(){
			$con = new Conexion();
			$sql = "SELECT count(*) as cantidad, c.nombre  FROM inscripciones_finales i INNER JOIN carreras c WHERE c.codigo_carrera = i.codigo_carrera  GROUP BY i.codigo_carrera";
			$resultado = $con->consultaRetorno($sql);
			$cantidades = [];
			while($row = mysqli_fetch_object($resultado)){
				$cantidades[] = $row;
			}
			return $cantidades;
		}
		public static function cantidadPorMesa(){
			$con = new Conexion();
			$sql = "SELECT count(*) as cantidad, i.codigo_materia, i.codigo_carrera, m.nombre, i.fecha_final FROM inscripciones_finales i INNER JOIN materias m ON m.codigo_carrera = i.codigo_carrera and m.codigo_materia = i.codigo_materia GROUP BY i.codigo_materia, i.codigo_carrera, i.fecha_final";
			$resultado = $con->consultaRetorno($sql);
			$cantidades = [];
			while($row = mysqli_fetch_object($resultado)){
				$cantidades[] = $row;
			}
			return $cantidades;
		}
		public static function cantidadPorMaterias(){
			$con = new Conexion();
			$sql = "SELECT count(*) as cantidad, i.codigo_materia, i.codigo_carrera, m.nombre FROM inscripciones_finales i INNER JOIN materias m ON m.codigo_carrera = i.codigo_carrera and m.codigo_materia = i.codigo_materia GROUP BY i.codigo_materia, i.codigo_carrera";
			$resultado = $con->consultaRetorno($sql);
			$cantidades = [];
			while($row = mysqli_fetch_object($resultado)){
				$cantidades[] = $row;
			}
			return $cantidades;
		}
	}