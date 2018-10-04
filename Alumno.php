<?php namespace Modelos;

use Modelos\Carrera;
use Modelos\Materia;

	class Alumno
	{

		//Propiedades
		//public static $tabla = "alumnos";

		public $legajo;
		private $con;
		public $nombre;
		public $apellido;
		public $tipo_documento;
		public $numero_documento;
		public $nombre_usuario;

		//Metodos
		public function __construct($legajo){

			$this->con = new Conexion();
			$this->legajo = $legajo;
		}

		// Metodo estatico que devuelve true si el alumno existe y false si no existe.
		public static function alumnoExiste($legajo){
			$sql = "SELECT * FROM alumnos WHERE legajo = $legajo";
			$con = new Conexion();
			$resultado = $con->consultaRetorno($sql);
			if(mysqli_num_rows($resultado)==0){
				return false;
			} else {
				return true;
			}
		}

		// Metodo que devuelve todas las materias que el alumno tiene disponible para rendir, según lo que devuelve el Stored Procedure sp_materias_a_rendir
		public function materiasDisponiblesParaRendir(){
			$disponibles = [];
			$this->con->consultaRetorno("SET NAMES 'utf8'");
			$sql = "CALL sp_materias_a_rendir($this->legajo)";
			if($resultado = $this->con->consultaRetorno($sql)){
				while($row = mysqli_fetch_object($resultado)){
					$disponibles[$row->codigo_carrera]["nombre_carrera"] = $row->nombre_carrera;
					$disponibles[$row->codigo_carrera]["codigo_carrera"] = $row->codigo_carrera;
					$disponibles[$row->codigo_carrera]["materias"][] =$row;
				}
			}

			return $disponibles;
		}

		//Metodo que devuelve la fecha de regularización, nota del final, fecha del final de todas las materias regularizadas del alumno.
		public function situacionAcademica(){
			$this->con->consultaRetorno("SET NAMES 'utf8'");
			$sql = "SELECT m.codigo_carrera, c.nombre AS nombre_carrera, m.codigo_materia, m.codigo_materia, m.nombre AS nombre_materia, l.fecha_regular, l.nota_final, l.fecha_final FROM materias m
				INNER JOIN carreras c on c.codigo_carrera = m.codigo_carrera
    		LEFT JOIN libro_matriz l on l.codigo_carrera = m.codigo_carrera and l.codigo_materia = m.codigo_materia
    		WHERE l.legajo = $this->legajo";

			$resultado = $this->con->consultaRetorno($sql);
			$materias = [];
			while($row = mysqli_fetch_object($resultado)){

				$materias[$row->codigo_carrera]["nombre_carrera"] = $row->nombre_carrera;
				$materias[$row->codigo_carrera]["codigo_carrera"] = $row->codigo_carrera;
				$materias[$row->codigo_carrera]["materias"][$row->codigo_materia] =$row;
				$sqlCorrelativa = "SELECT DISTINCT codigo_correlativa FROM correlativas c INNER JOIN materias WHERE c.codigo_materia = $row->codigo_materia and c.codigo_carrera = $row->codigo_carrera";
				$resultadoCorrelativas = $this->con->consultaRetorno($sqlCorrelativa);
				while ($correlativa = mysqli_fetch_object($resultadoCorrelativas)) {
					$materias[$row->codigo_carrera]["materias"][$row->codigo_materia]->correlativas[] = $correlativa->codigo_correlativa;
				}
			}

			return $materias;
		}

		// Guardar las inscripciones a finales de las materias pasadas por parametro.
		// Se guardan las nuevas inscripciones, se actualizan inscripciones ya hechas y se borran las que se habian hecho y ya no estan
		public function inscripcionAFinales($usuario_id, $nombre_usuario, $materias = []){
			$this->con->consultaRetorno("SET NAMES 'utf8'");
			$legajo = $this->legajo;
			if(count($materias)>0){
				//Voy generando un string para borrar todas las inscripciones que no se hayan pasado por parametro. NOT IN (materias anotadas)
				$sqlBorrarNoActualizadas = "DELETE FROM inscripciones_finales WHERE legajo = $legajo AND nro_operacion NOT IN ( ";
				foreach ($materias as $key => $materia) {
						$codigo_carrera = $materia["codigo_carrera"];
						$codigo_materia = $materia["codigo_materia"];
						$fecha_final = $materia["fecha_final"];
						$modalidad = $materia["modalidad"];
						$otrasMaterias[] = $materia;
						//Si el numero de operacion es igual a -1, quiere decir que es una inscripcion de una materia nueva. Si no es una inscripcion ya existente entonces se actualiza
					if($materia["nro_operacion"]!=-1){
						$sql = "UPDATE inscripciones_finales set codigo_carrera =" . $materia['codigo_carrera'] .", codigo_materia = " . $materia["codigo_materia"] .", fecha_final = '". $materia["fecha_final"]. "', modalidad = '".$materia["modalidad"]."' WHERE nro_operacion =" .$materia["nro_operacion"];
						// Se agrega el nro_operacion de la materia que no quiero borrar.
						// Guardo en la bitcora
						$descripcion = "El usuario $nombre_usuario elimino la inscripción a la materia " . $materia['codigo_materia'] . " de la carrera " . $materia["codigo_carrera"];
						$bitacora = Bitacora::guardar($usuario_id, $descripcion);
						if(!$bitacora) {
							return false;
						}
						$sqlBorrarNoActualizadas = $sqlBorrarNoActualizadas."'". $materia["nro_operacion"] ."',";
						if(!$this->con->consultaRetorno($sql)){
							return false;
						}
					} else {
						$sql = "INSERT INTO inscripciones_finales (codigo_carrera, codigo_materia, legajo, fecha_final, modalidad) values ($codigo_carrera, $codigo_materia, $legajo, '$fecha_final', '$modalidad')";
						if(!$this->con->consultaRetorno($sql)){
							return false;
						}
						// Guardo en la bitcora
						$descripcion = "El usuario $nombre_usuario se inscribio a la materia " . $materia['codigo_materia'] . " de la carrera " . $materia["codigo_carrera"];
						$bitacora = Bitacora::guardar($usuario_id, $descripcion);
						if(!$bitacora) {
							return false;
						}
						// Si se inserta esa inscripciones necesito saber el nro_operacion para no borrarla. Entonces busco la ultima inscripcion agregada
						$sqlBuscar = "SELECT nro_operacion FROM inscripciones_finales ORDER BY nro_operacion DESC LIMIT 1";
						$resultado = $this->con->consultaRetorno($sqlBuscar);
						$ultimoNroOperacion = mysqli_fetch_object($resultado)->nro_operacion;
						// Entonces de la ultima inscripcion agregada guardo el nro_operacion y lo concanteno al string de las inscripciones a eliminar
						$sqlBorrarNoActualizadas = $sqlBorrarNoActualizadas."'". $ultimoNroOperacion ."',";
					}
				}
				//Elimino el ultimo caracter del string de la sentencia para borrar materias ya que es una , (coma). Y cierro el parentesis.
				$sqlBorrarNoActualizadas = substr($sqlBorrarNoActualizadas, 0, strlen($sqlBorrarNoActualizadas) - 1);
				$sqlBorrarNoActualizadas = $sqlBorrarNoActualizadas . ')';
				if(!$this->con->consultaRetorno($sqlBorrarNoActualizadas)) { //Ejecuto la sentencia para eliminar las materias no anotadas
					return false;
				} else {
					return true;
				}

			} else {
				// En caso de que no se anote en ninguna materia, elimino todas las inscripciones de ese alumno.
				$sqlBorrar = "DELETE FROM inscripciones_finales WHERE legajo = $this->legajo";
				$this->con->consultaSimple($sqlBorrar);
				// Guardo en la bitcora
				$descripcion = "El usuario $nombre_usuario elimino todas las inscripciones previamente realizadas";
				$bitacora = Bitacora::guardar($usuario_id, $descripcion);
				if(!$bitacora) {
					return false;
				}
			}
		}

		//Metodo que devuelve los datos de las inscripcion del alumno que se encuentran en la base de datos.
		public function estadoInscripcion(){

			$inscripciones = [];
			$sql = "SELECT i.codigo_carrera, c.nombre as nombre_carrera, i.codigo_materia, m.nombre as nombre_materia, i.fecha_final, i.legajo, i.modalidad,
				CONCAT(i.legajo,i.codigo_carrera,i.codigo_materia,date_format(i.fecha_final,'%Y%m%d'),i.nro_operacion) as codigo_operacion
			FROM inscripciones_finales i
			INNER JOIN carreras c ON c.codigo_carrera = i.codigo_carrera
			INNER JOIN materias m ON m.codigo_materia = i.codigo_materia AND m.codigo_carrera = i.codigo_carrera
			WHERE legajo=$this->legajo";
			$this->con->consultaRetorno("SET NAMES 'utf8'");
			$resultado = $this->con->consultaRetorno($sql);
			while($row = mysqli_fetch_object($resultado)){
				$row->url_codigo_operacion = $this->getUrlCodigoOperacion($row->codigo_operacion);
				$inscripciones[$row->codigo_carrera]["nombre_carrera"] = $row->nombre_carrera;
				$inscripciones[$row->codigo_carrera]["codigo_carrera"] = $row->codigo_carrera;
				$inscripciones[$row->codigo_carrera]["materias"][] =$row;
			}

			return $inscripciones;
		}

		public function getUrlCodigoOperacion($codigo_operacion){

			$size = 50;

			$urlHost = $this->armar_url();
			$url_codigo_operacion = "$urlHost/barcode/barcode.php?text=$codigo_operacion&size=$size&print=true";
			return $url_codigo_operacion;
		}

		public function armar_url(){
			//Devuelve la url completa de donde son llamados todos los scripts del servidor.

			$enlace_actual = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$array_enlace = explode('/',$enlace_actual);
			$array_enlace = array_slice($array_enlace,0,-1);
			$enlace_actual = implode('/',$array_enlace);
			return $enlace_actual;
		}


		public static function buscarPorLegajo($legajo){
			$con = new Conexion();
			$con->consultaRetorno("SET NAMES 'utf8'");
			$sql = "SELECT distinct a.legajo, a.nombre, a.apellido, a.tipo_documento, a.numero_documento, u.ID as usuario_id, u.nombre_cuenta_usuario FROM alumnos a LEFT JOIN usuarios u ON u.legajo = a.legajo WHERE a.legajo=$legajo";
			$resultado = $con->consultaRetorno($sql);
			if($resultado->num_rows!= 0) {
				$row = mysqli_fetch_object($resultado);
				$alumno = new Alumno($row->legajo);
				$alumno->nombre = $row->nombre;
				$alumno->apellido = $row->apellido;
				$alumno->numero_documento = $row->numero_documento;
				$alumno->tipo_documento = $row->tipo_documento;
				$alumno->usuario_id = $row->usuario_id;
				$alumno->nombre_usuario = $row->nombre_cuenta_usuario;

				return $alumno;
			} else {
				return false;
			}

		}

		public static function buscarPorDNI($dni){
			$con = new Conexion();
			$con->consultaRetorno("SET NAMES 'utf8'");
			$sql = "SELECT a.legajo, a.nombre, a.apellido, a.tipo_documento, a.numero_documento, u.ID as usuario_id, u.nombre_cuenta_usuario FROM alumnos a LEFT JOIN usuarios u ON u.legajo = a.legajo WHERE a.numero_documento='$dni'";
			$resultado = $con->consultaRetorno($sql);
			if($resultado->num_rows!=0){
				$row = mysqli_fetch_object($resultado);
				$alumno = new Alumno($row->legajo);
				$alumno->nombre = $row->nombre;
				$alumno->apellido = $row->apellido;
				$alumno->numero_documento = $row->numero_documento;
				$alumno->tipo_documento = $row->tipo_documento;
				$alumno->usuario_id = $row->usuario_id;
				$alumno->nombre_usuario = $row->nombre_cuenta_usuario;

				return $alumno;
			} else {
				return false;
			}
		}

		public static function buscarPorNombre($nombre, $pag){
			$con = new Conexion();
			$con->consultaRetorno("SET NAMES 'utf8'");
			$sqlCantidad = "SELECT COUNT(DISTINCT a.legajo, a.nombre, a.apellido, a.tipo_documento, a.numero_documento) as cantidad FROM alumnos a WHERE a.apellido LIKE '$nombre%'";
			$resultadoCantidad = $con->consultaRetorno($sqlCantidad);
			$cantidad = mysqli_fetch_object($resultadoCantidad)->cantidad;
			if($cantidad % 20 == 0){
				$paginas = intdiv($cantidad, 20);
			} else {
				$paginas = intdiv($cantidad, 20) + 1;
			}
			$limite_inferior = 20*($pag-1);
			$cantidad_resultados = 20;
			$sql = "SELECT DISTINCT a.legajo, a.nombre, a.apellido, a.tipo_documento, a.numero_documento, u.ID as usuario_id, u.nombre_cuenta_usuario FROM alumnos a LEFT JOIN usuarios u ON a.legajo = u.legajo WHERE a.apellido LIKE '$nombre%' ORDER BY a.apellido limit $limite_inferior,$cantidad_resultados";
			$resultado = $con->consultaRetorno($sql);
			$alumnos = [];
			while($row = mysqli_fetch_object($resultado)){
				$alumno = new Alumno($row->legajo);
				$alumno->nombre = $row->nombre;
				$alumno->apellido = $row->apellido;
				$alumno->numero_documento = $row->numero_documento;
				$alumno->tipo_documento = $row->tipo_documento;
				$alumno->usuario_id = $row->usuario_id;
				$alumno->nombre_usuario = $row->nombre_cuenta_usuario;
				$alumnos[] =$alumno;
			}
			return ["alumnos" => $alumnos, "paginas" => $paginas];
		}

		public static function buscarPorNombreUsuario($usuario){
			$con = new Conexion();
			$con->consultaRetorno("SET NAMES 'utf8'");
			$sql = "SELECT  u.ID as usuario_id, u.nombre_cuenta_usuario, a.legajo, a.nombre, a.apellido, a.numero_documento FROM usuarios u LEFT JOIN alumnos a on u.legajo = a.legajo WHERE nombre_cuenta_usuario='$usuario' AND ID_rol=1";
			$resultado = $con->consultaRetorno($sql);
			if($resultado->num_rows!=0){
				$row = mysqli_fetch_object($resultado);
				$alumno = new Alumno($row->legajo);
				$alumno->nombre = $row->nombre;
				$alumno->apellido = $row->apellido;
				$alumno->numero_documento = $row->numero_documento;
				$alumno->usuario_id = $row->usuario_id;
				$alumno->nombre_usuario = $row->nombre_cuenta_usuario;
				
				return $alumno;
			} else {
				return false;
			}

		}

	}
