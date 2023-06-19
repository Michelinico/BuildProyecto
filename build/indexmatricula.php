<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: content-type");
header("Access-Control-Allow-Methods: OPTIONS,GET,PUT,POST,DELETE");
require_once("./Modelo/ParkingDB.php");

$matricula = $_POST['matricula'];
$direccion = $_POST['direccion'];
$parkingDB = new ParkingDB();

switch ($direccion) {

    case "entrada":
        $exito = $parkingDB->entrada($matricula);
        echo ($exito);
    break;

    case "salida":
        $exito = $parkingDB->salida($matricula);
        echo ($exito);
    break;
}

$parkingDB->cerrar();

?>