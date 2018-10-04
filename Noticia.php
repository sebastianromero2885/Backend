<?php namespace Modelos;

	class Noticia {

		public $titulo;
		public $contenido;
		public $imagen;
		private $con;

		public function __construct($titulo, $contenido, $imagen){

			$this->titulo = $titulo;
			$this->contenido = $contenido;
			$this->imagen = $imagen;
			$this->con = new Conexion();
		}

		public function guardar($usuario_id, $nombre_usuario){
			//Guardo la imagen con los datos que ya se encuentran en el objeto creado
			$sql = "INSERT INTO noticias (ID_noticia, titulo, contenido, imagen, fecha_creacion) values(null,'$this->titulo', '$this->contenido','$this->imagen', now())";
			$descripcion = "El usuario $nombre_usuario guardó la noticia con titulo $this->titulo";
			$bitacora = Bitacora::guardar($usuario_id, $descripcion);
			if(!$bitacora) {
				return false;
			}
			$resultado =  $this->con->consultaRetorno($sql);
			if ($resultado){
				return true;
			} else {
				return false;
			}
		}

		public static function actualizar($usuario_id, $nombre_usuario, $id, $titulo, $contenido, $imagen){
			//Si no se envia una imagen nueva solo actualizo titulo y contenido.
			$con = new Conexion();

			$sql = "UPDATE noticias SET titulo ='$titulo', contenido='$contenido' ";
			if($imagen){

				//Si se envia una imagen para actualizar, 1ero busco el nombre de la imagen, y despues la elimino del servidor
				$sqlBuscar = "SELECT imagen from noticias where ID_noticia= $id";
				$resultado = $con->consultaRetorno($sqlBuscar);
				$respuesta = mysqli_fetch_object($resultado);
				$imagen_a_borrar = $respuesta->imagen;

				$url_imagen = './imagenes/'.$imagen_a_borrar;
				// if(!unlink($url_imagen)){
				// 	return $url_imagen;
				// }
				//Creo la query para actualizar con la imagen
				$sql = $sql. ", imagen='$imagen' WHERE ID_noticia = $id";
			} else {
				//En caso de que no envien la imagen creo la Query de actualizar sin la imagen.
				$sql = $sql ."WHERE ID_noticia = $id";
			}
			$resultado = $con->consultaRetorno($sql);
			$descripcion = "El usuario $nombre_usuario actulizó la noticia con id: $id";
			$bitacora = Bitacora::guardar($usuario_id, $descripcion);
			if(!$bitacora) {
				return false;
			}
			if($resultado){
				return true;
			} else {
				return false;
			}
		}

		public static function todas(){

			//Devuelve todas las noticias que se encuentran guardadas en la base de datos

			$con = new Conexion();
			$sql = "SELECT titulo, contenido, imagen, fecha_creacion, ID_noticia FROM noticias";
			$resultado = $con->consultaRetorno($sql);
			$noticias = [];
			while($row = mysqli_fetch_object($resultado)){
				if($row->imagen){
					//Al nombre de la imagen le paso toda la direccion del servidor en la que se encuentra
					$enlace_actual = Noticia::armar_url();
					$row->imagen = $enlace_actual.'/imagenes/'.$row->imagen;
				}
				$noticias[] = $row;
			}

			return $noticias;
		}

		public static function eliminar($usuario_id, $nombre_usuario, $id){
			//Elimina la noticia con el id pasado por parametro.
			//1ero busco el nombre de la imagen de esa noticia asi la elimino del servidor

			$con = new Conexion();

			$sql = "SELECT imagen from noticias where ID_noticia= $id";
			$resultado = $con->consultaRetorno($sql);
			$respuesta = mysqli_fetch_object($resultado);
			$imagen = $respuesta->imagen;
			$url_imagen = './imagenes/'.$imagen;
			// if(!unlink($url_imagen)){
			// 	return $url_imagen;
			// }
			$sql = "DELETE FROM noticias WHERE ID_noticia=$id";
			$resultado = $con->consultaRetorno($sql);
			$descripcion = "El usuario $nombre_usuario eliminó la noticia con id: $id";
			$bitacora = Bitacora::guardar($usuario_id, $descripcion);
			if(!$bitacora) {
				return false;
			}
			if($resultado) {
				return true;
			} else {
				return false;
			}
		}

		public static function mostrar($id){
			//Devuelve los datos completos de la noticia que se obtiene con el id pasado por parametro

			$con = new Conexion();
			$sql = "SELECT * FROM noticias WHERE ID_noticia = $id";
			$resultado = $con->consultaRetorno($sql);
			$noticia = mysqli_fetch_object($resultado);
			if($noticia->imagen){
				$enlace_actual = Noticia::armar_url();

				//Devuelvo la url completa de la imagen
				$noticia->imagen = $enlace_actual.'/imagenes/'.$noticia->imagen;
			}
			return $noticia;
		}
		public static function armar_url(){
			//Devuelve la url completa de donde son llamados todos los scripts del servidor.

			$enlace_actual = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$array_enlace = explode('/',$enlace_actual);
			$array_enlace = array_slice($array_enlace,0,-1);
			$enlace_actual = implode('/',$array_enlace);
			return $enlace_actual;
		}

		public static function borrarImagen($id){
			$con = new Conexion();
			$sql = "UPDATE noticias SET imagen=null WHERE ID_noticia = $id";
			$resultado = $con->consultaRetorno($sql);
			if($resultado) {
				return true;
			} else {
				return false;
			}
		}
	}
