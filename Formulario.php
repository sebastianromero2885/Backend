<?php namespace Modelos;

require_once "Config/Autoload.php";


class Formulario
{
  public $legajo;
  public $numero;
  public $carrera;
  public $mes;

  public function __construct(){
    $this->con = new Conexion();
  }

  public static function pdfalumno_regular($legajo){
    $sql = "SELECT a.numero_documento, a.nombre, a.apellido FROM carreras c INNER JOIN alumnos a ON c.codigo_carrera = a.codigo_carrera WHERE a.legajo = $legajo";
    $con = new Conexion();
    $resultado = $con->consultaRetorno($sql);
    if(mysqli_num_rows($resultado)==0){
      return mysqli_fetch_object($resultado);
    } else {
      return mysqli_fetch_object($resultado);
      // en caso de ser muchas filas los datos que obtenemos de la consulta
      // while ($row = mysqli_fetch_object($resultado)) {
      //   $array_resultado[] = $row;
      // }
      // return $array_resultado;

      }

  }

  public static function pdfporcentaje_materias($legajo, $carrera){
    $sql = "SELECT DISTINCT a.numero_documento as 'Documento', a.nombre as 'Alumno', a.apellido as 'Apellido', m.nombre AS 'Materia', m.codigo_materia AS 'Codigo de Materia' , l.nota_final AS 'Nota del Final', l.fecha_final AS 'Fecha del Final', l.libro_examen AS 'Libro', l.Folio_Actexamen AS 'Folio', c.resolucion as 'Resolucion de carrera', c.cantidad_materias as 'Cantidad de Materias' FROM carreras c INNER JOIN alumnos a ON c.codigo_carrera = a.codigo_carrera INNER JOIN materias m ON a.codigo_carrera = m.codigo_carrera INNER JOIN libro_matriz l ON a.legajo = l.legajo AND m.codigo_materia = l.codigo_materia WHERE a.legajo = '$legajo' AND c.nombre = '$carrera' ORDER by m.codigo_materia";
    $con = new Conexion();
    $resultado = $con->consultaRetorno($sql);
    if(mysqli_num_rows($resultado)==0){
      while ($row = mysqli_fetch_object($resultado)) {
        $array_resultado[] = $row;
      }
      return $array_resultado;
    } else {
      while ($row = mysqli_fetch_object($resultado)) {
        $array_resultado[] = $row;
      }
      return $array_resultado;
    }
  }

  public static function pdftitulo_tramite($legajo, $carrera){
    $sql = "SELECT DISTINCT a.numero_documento as 'Documento', a.nombre as 'Alumno', a.apellido as 'Apellido', m.nombre AS 'Materia', m.codigo_materia AS 'Codigo de Materia', l.nota_final AS 'Nota del Final', l.fecha_final AS 'Fecha del Final', c.resolucion as 'Resolucion de carrera', c.cantidad_materias as 'Cantidad de Materias' FROM carreras c INNER JOIN alumnos a ON c.codigo_carrera = a.codigo_carrera INNER JOIN materias m ON a.codigo_carrera = m.codigo_carrera INNER JOIN  libro_matriz l ON a.legajo = l.legajo AND m.codigo_materia = l.codigo_materia WHERE a.legajo = '$legajo' AND c.nombre = '$carrera' ORDER by m.codigo_materia";
    $con = new Conexion();
    $resultado = $con->consultaRetorno($sql);
    if(mysqli_num_rows($resultado)==0){
      while ($row = mysqli_fetch_object($resultado)) {
        $array_resultado[] = $row;
      }
      return $array_resultado;
    } else {
      while ($row = mysqli_fetch_object($resultado)) {
        $array_resultado[] = $row;
      }
      return $array_resultado;
    }
  }

  public static function pdfasistencia_examen($legajo){
    $sql = "SELECT a.nombre, a.apellido, a.numero_documento FROM alumnos a WHERE legajo = '$legajo'";
    $con = new Conexion();
    $resultado = $con->consultaRetorno($sql);
    if(mysqli_num_rows($resultado)==0){
      return mysqli_fetch_object($resultado);
    } else {
      return mysqli_fetch_object($resultado);
      // en caso de ser muchas filas los datos que obtenemos de la consulta
      // while ($row = mysqli_fetch_object($resultado)) {
      //   $array_resultado[] = $row;
      // }
      // return $array_resultado;

      }

  }


 public static function mes_escrito($mes)
 {
   switch ($mes) {
       case "01":
           $mes_escrito = 'Enero';
           return $mes_escrito;
           break;
       case "02":
           $mes_escrito = 'Febrero';
           return $mes_escrito;
           break;
       case "03":
           $mes_escrito = 'Marzo';
           return $mes_escrito;
           break;
   		case '04':
   				$mes_escrito = 'Abril';
          return $mes_escrito;
   				break;
   		case '05':
   				$mes_escrito = 'Mayo';
          return $mes_escrito;
   				break;
   		case '06':
   				$mes_escrito = 'Junio';
          return $mes_escrito;
   				break;
   		case '07':
   				$mes_escrito = 'Julio';
          return $mes_escrito;
   				break;
   		case '08':
   				$mes_escrito = 'Agosto';
          return $mes_escrito;
   				break;
   		case '09':
   				$mes_escrito = 'Septiembre';
          return $mes_escrito;
          break;
   		case '10':
   				$mes_escrito = 'Octubre';
          return $mes_escrito;
          break;
   		case '11':
   				$mes_escrito = 'Noviembre';
          return $mes_escrito;
          break;
   		case '12':
   				$mes_escrito = 'Diciembre';
          return $mes_escrito;
          break;
   }
 }

  // Funcion que toma de parametro una array de numeros (del 1 al 10)
  // y los devuelve en un array con los numeros escritos
  public static function numero_escrito($numero){
    foreach ($numero as $valor) {
        switch ($valor) {
          case '10':
            $numero_escrito[] ='Diez';
            break;
          case '9':
            $numero_escrito[] ='Nueve';
            break;
          case '8':
            $numero_escrito[] ='Ocho';
            break;
          case '7':
            $numero_escrito[] ='Siete';
            break;
          case '6':
            $numero_escrito[] ='Seis';
            break;
          case '5':
            $numero_escrito[] ='Cinco';
            break;
          case '4':
            $numero_escrito[] ='Cuatro';
            break;
          case '3':
            $numero_escrito[] ='Tres';
            break;
          case '2':
            $numero_escrito[] ='Dos';
            break;
          case '1':
            $numero_escrito[] ='Uno';
            break;
          case '0':
            $numero_escrito[] ='Cero';
            break;
         default:
             $numero_escrito[] =' ';
             break;
        }

    }
    return $numero_escrito;
  }

}
 ?>
