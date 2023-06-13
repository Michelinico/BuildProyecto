<?php
require_once("Coche.php");
require_once("Cliente.php");
require_once("Estado.php");

class ParkingDB {

    private $conexion;
    private $servidor;
    private $usuario;
    private $clave="";
    private $basedatos;

    public function __construct() {

        $credenciales=parse_ini_file("conexion.ini");
        
        $this->servidor=$credenciales["servidor"];
        $this->usuario=$credenciales["usuario"];
        $this->clave=$credenciales["clave"];
        $this->basedatos=$credenciales["basedatos"];

        $this->conexion = mysqli_connect($this->servidor, $this->usuario, $this->clave, $this->basedatos);
        if ($this->conexion->connect_error == true) {
            die("Error de conexion".$this->conexion->connect_error);
        }
    }

    // USUARIOS //

    public function comprueba($usuario) {

        $exito=false;
        $login=$usuario['DNI'];
        $clave=$usuario['clave'];
        $sql = "select Nombre,TipoUsu from usuarios where DNI = '$login' and Contrasena = '$clave'";
        $resultado=$this->conexion->query($sql);
        if ($resultado->num_rows > 0) {
            $exito = true;
            $fila = $resultado->fetch_assoc();
            $nombreUsuario = $fila["Nombre"];
            $tipoUsuario = $fila["TipoUsu"];
            $datosUsuario = array(
                "DNI" => $login,
                "nombre" => $nombreUsuario,
                "tipo" => $tipoUsuario
            );
        }
        return $exito ? $datosUsuario : null;
    }

    // VEHICULOS EN GENERAL //

    public function comprobarEstado() {
        $estados = array();
        $sql="SELECT es1.Matricula,
                CASE WHEN es1.Salida IS NULL THEN 'Dentro' ELSE 'Fuera' END AS Estado,
                MAX(DATE_FORMAT(es1.Entrada, '%Y-%m-%d %H:%i')) AS UltimaEntrada,
                MAX(DATE_FORMAT(es1.Salida, '%Y-%m-%d %H:%i')) AS UltimaSalida,
                (SELECT SUM(ROUND(TIMESTAMPDIFF(Minute, Entrada, IFNULL(Salida, NOW()))* 0.007,2)) 
                FROM `entrada-salidas` es2
                WHERE es2.Matricula = es1.Matricula) AS Pagar
            FROM `entrada-salidas` es1
            WHERE (es1.Matricula, es1.Entrada) IN (
                SELECT Matricula, MAX(Entrada)
                FROM `entrada-salidas` es2
                GROUP BY Matricula
            )
            GROUP BY es1.Matricula
            ORDER BY es1.Matricula;";
        $cursor = $this->conexion->query($sql);
        $tupla = $cursor->fetch_assoc();  
        while ($tupla != null) {
            $estado = new Estado($tupla);
            array_push($estados, $estado->toArray());
            $tupla = $cursor->fetch_assoc();
        }
        return $estados;
    }

    // VEHICULOS POR USUARIO //

    public function cochesUsu($usuario) {
        $coches = array();
        $sql="select * from coches where DNI='$usuario'";
        $cursor=$this->conexion->query($sql);
        $tupla = $cursor->fetch_assoc();
        while ($tupla != null) {
            $coche = new Coche($tupla);
            array_push($coches, $coche->toArray());
            $tupla = $cursor->fetch_assoc();
        }
        return $coches;
    }

    // VEHICULOS ADMIN //

    public function leerCochesJSON() {
        $json = array();
        $coches = $this->leerCoches();
        foreach ($coches as $coche) {
            array_push($json, $coche->toArray());
        }
        return json_encode($json);
    }

    public function leerCoches() {
        $coches = array();
        $sql="select * from coches";
        $cursor=$this->conexion->query($sql);
        $tupla = $cursor->fetch_assoc();
        while ($tupla != null) {
            $coche = new Coche($tupla);
            array_push($coches, $coche);
            $tupla = $cursor->fetch_assoc();
        }
        return $coches;
    }

    public function cocheUsu($matricula) {
        $coches = array();
        $sql="select * from coches where Matricula='$matricula'";
        $cursor=$this->conexion->query($sql);
        $coches = $cursor->fetch_assoc();
        while ($tupla != null) {
            $coche = new Coche($tupla);
            array_push($coches, $coche);
            $tupla = $cursor->fetch_assoc();
        }
        return $coches;     
    }

    public function plazasDisponibles() {
        $plazas = array();
        $sql="select Plaza from coches";
        $cursor = $this->conexion->query($sql);  
        while ($tupla = $cursor->fetch_assoc()) {
            array_push($plazas, $tupla['Plaza']);
        }   
        return $plazas;
    }

    public function guardarVehiculo($datosF){
        $Matricula=$datosF['Matricula'];
        $DNI=$datosF['DNI'];
        $Marca=$datosF['Marca'];
        $Modelo=$datosF['Modelo'];
        $Color=$datosF['Color'];
        $Plaza=$datosF['Plaza'];
        $Imagen=$datosF['Imagen'];

        $sql="insert into coches (Matricula,DNI,Marca,Modelo,Color,Plaza,Imagen) VALUES (
            '$Matricula','$DNI','$Marca','$Modelo','$Color','$Plaza','$Imagen')";
        
        $exito=$this->conexion->query($sql);
        return $exito;
    }

    public function modVehiculo($datosF){
        $Matricula=$datosF['Matricula'];
        $Color=$datosF['Color'];
        $Plaza=$datosF['Plaza'];
        $Imagen=$datosF["Imagen"];
        if ($Imagen==='') {
            $sql="UPDATE coches SET Color = '$Color', Plaza = '$Plaza' WHERE Matricula = '$Matricula'";
        }else {
            $sql="UPDATE coches SET Color = '$Color', Plaza = '$Plaza', Imagen = '$Imagen' WHERE Matricula = '$Matricula'";
        }
        $exito=$this->conexion->query($sql);
        return $exito;
    }

    public function borrarVehiculo($matricula){
        $sql="delete from coches where Matricula='$matricula'";
        $exito=$this->conexion->query($sql);
        return $exito;
    }


    // CLIENTES ADMIN //

    public function leerClientesJSON() {
        $json = array();
        $clientes = $this->leerClientes();
        foreach ($clientes as $cliente) {
            array_push($json, $cliente->toArray());
        }
        return json_encode($json);
    }

    public function leerClientes() {
        $clientes = array();
        $sql="SELECT u.*, IFNULL(GROUP_CONCAT(c.Plaza), 'NA') AS Plazas 
                FROM usuarios u 
                LEFT JOIN coches c USING (DNI) 
                GROUP BY u.DNI";
        $cursor=$this->conexion->query($sql);
        $tupla = $cursor->fetch_assoc();
        while ($tupla != null) {
            $cliente = new Cliente($tupla);
            array_push($clientes, $cliente);
            $tupla = $cursor->fetch_assoc();
        }
        return $clientes;
    }

    public function guardarCliente($datosF){
        $DNI=$datosF['DNI'];
        $Nombre=$datosF['Nombre'];
        $Apellido=$datosF['Apellido'];
        $Contrasena=$datosF['Contrasena'];
        $Telefono=$datosF['Telefono'];
        $TipoUsu=$datosF['TipoUsu'];
        $Avatar=$datosF["Avatar"];
        $sql="insert into usuarios (DNI, Nombre, Apellido, Contrasena, Telefono, TipoUsu, Avatar) VALUES (
            '$DNI','$Nombre','$Apellido','$Contrasena','$Telefono','$TipoUsu','$Avatar')";
        $exito=$this->conexion->query($sql);
        return $exito;
    }

    public function modCliente($datosF){
        $DNI=$datosF['DNI'];
        $Contrasena=$datosF['Contrasena'];
        $Telefono=$datosF['Telefono'];
        $Avatar=$datosF["Avatar"];
        if ($Avatar==='') {
            $sql="UPDATE usuarios SET Contrasena = '$Contrasena', Telefono = '$Telefono' WHERE DNI = '$DNI'";
        }else if ($Contrasena===''){
            $sql="UPDATE usuarios SET Avatar = '$Avatar', Telefono = '$Telefono' WHERE DNI = '$DNI'";
        }else{
            $sql="UPDATE usuarios SET Contrasena = '$Contrasena', Avatar = '$Avatar', Telefono = '$Telefono' WHERE DNI = '$DNI'";
        }
        $exito=$this->conexion->query($sql);
        return $exito;
    }

    public function borrarCliente($DNI){
        $sql="delete from usuarios where DNI='$DNI'";
        $exito=$this->conexion->query($sql);
        return $exito;
    }

    // ENTRADA SALIDA PARKING //

    public function entrada($matricula) {
        $sql="SELECT * FROM coches WHERE Matricula='$matricula'";
        $respuesta=$this->conexion->query($sql);
        if ($respuesta->num_rows > 0) {
            $sql="SELECT Salida FROM `entrada-salidas` WHERE Matricula='$matricula' ORDER BY `IDE-S` DESC LIMIT 1";
            $respuesta=$this->conexion->query($sql);
            $fila = $respuesta->fetch_assoc();
            $valor = $fila['Salida'];
            if ($valor!=NULL || $valor==""){
                $sql="INSERT INTO `entrada-salidas`(`IDE-S`,Matricula,Entrada,Salida) VALUES (DEFAULT,'$matricula',CURRENT_TIMESTAMP,DEFAULT)";
                $respuesta=$this->conexion->query($sql);
                $exito = "ADELANTE";
            }else {
                $exito = "DENTRO";
            }
        }else{
            $exito = "NODB";
        }
        return $exito;     
    }

    public function salida($matricula) {
        $sql="SELECT * FROM coches WHERE Matricula='$matricula'";
        $respuesta=$this->conexion->query($sql);
        if ($respuesta->num_rows > 0) {
            $sql="SELECT Salida FROM `entrada-salidas` WHERE Matricula='$matricula' ORDER BY `IDE-S` DESC LIMIT 1";
            $respuesta=$this->conexion->query($sql);
            $fila = $respuesta->fetch_assoc();
            $valor = $fila['Salida'];
            if ($valor==NULL){
                $sql="UPDATE `entrada-salidas` SET `Salida`=CURRENT_TIMESTAMP WHERE Matricula='$matricula'";
                $respuesta=$this->conexion->query($sql);
                $exito = "ADELANTE";
            }else {
                $exito = "FUERA";
            }
        }else{
            $exito = "ERROR EN LA LECTURA";
        }
        return $exito; 
         
    }
    
    /* Cierra la conexión con la base de datos */
    public function cerrar() {
        $this->conexion->close();
    }
    
}
?>