<?php
require_once("./Modelo/Cabeceras.php");
require_once("./Modelo/ParkingDB.php");
session_start();

$_post = json_decode(file_get_contents('php://input'),true);
if ($_post==NULL){
    $accion="comprobarUsu";
}else{
    $accion=$_post["accion"];
}
$parkingDB = new ParkingDB();

switch ($accion) {

    case "acceder":
        $usuario = $_post['usuario'];
        $datosUsuario = $parkingDB->comprueba($usuario);
        if ($datosUsuario != null) {
            $_SESSION["usuario"] = $datosUsuario["DNI"];
            $_SESSION["tipo"] = $datosUsuario["tipo"];
            $_SESSION["nombre"] = $datosUsuario["nombre"];
            $exito = true;
        } else {
            $exito = false;
        }
        $respuesta = array("respuesta" => $exito,   
                            "usuario" => $usuario['DNI'], 
                            "tipo" => $datosUsuario['tipo'],
                            "nombre" => $datosUsuario['nombre']
        );
        echo json_encode($respuesta);
    break;

    case "comprobarUsu":
        if (isset($_SESSION["usuario"])) {
            $usuario = $_SESSION['usuario'];
            $tipo = $_SESSION["tipo"];
            $nombre= $_SESSION["nombre"];
            echo json_encode(['usuario' => $usuario,'tipo' => $tipo, 'nombre' => $nombre] );
          } else {
            echo json_encode(['usuario' => null]);
          }
    break;

    case "leercochesusu":
        $usuario = $_post['DNI'];
        $cochesUsuario = $parkingDB->cochesUsu($usuario);
        echo json_encode($cochesUsuario);
    break;

    case "leercoche":
        $matricula = $_post['matricula'];
        $cocheUsuario = $parkingDB->cocheUsu($matricula);
        echo json_encode($cocheUsuario);
    break;

    case "leerhistorial":
        $matricula = $_post['matricula'];
        $historial = $parkingDB->historial($matricula);
        echo json_encode($historial);
    break;

    case "plazasdisponibles":
        $plazasDisponibles = $parkingDB->plazasDisponibles();
        echo json_encode($plazasDisponibles);
    break;

    case "leercochesjson": 
        $respuesta=$parkingDB->leerCochesJSON();
        echo $respuesta;
    break;

    case "leerclientesjson": 
        $respuesta=$parkingDB->leerClientesJSON();
        echo $respuesta;
    break;

    case "añadircliente":
        $datosF = $_post['datosform'];
        $respuesta=$parkingDB->guardarCliente($datosF);
        echo $respuesta;
    break;

    case "modificarcliente":
        $datosF = $_post['datosform'];
        $respuesta=$parkingDB->modCliente($datosF);
        echo $respuesta;
    break;

    case "borrarcliente":
        $DNI = $_post['idcliente'];
        $exito=$parkingDB->borrarCliente($DNI);
        echo $exito;
    break;

    case "añadirvehiculo":
        $datosF = $_post['datosform'];
        $respuesta=$parkingDB->guardarVehiculo($datosF);
        echo $respuesta;
    break;

    case "modificarvehiculo":
        $datosF = $_post['datosform'];
        $respuesta=$parkingDB->modVehiculo($datosF);
        echo $respuesta;
    break;

    case "borrarvehiculo":
        $matricula = $_post['idcoche'];
        $exito=$parkingDB->borrarVehiculo($matricula);
        echo $exito;
    break;

    case "estadovehiculo":
        $estados = $parkingDB->comprobarEstado();
        echo json_encode($estados);
    break;

    case "cerrarsesion":
        session_destroy();
        $respuesta = array("respuesta" => true);
        echo json_encode($respuesta);
    break;

}

$parkingDB->cerrar();

?>