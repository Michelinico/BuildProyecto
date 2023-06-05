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

        /*$this->conexion = new mysqli($this->servidor, $this->usuario, $this->clave, $this->basedatos);
        if ($this->conexion->connect_error == true) {
            die("Error de conexion".$this->conexion->connect_error);
        }*/
        
        try {
         $this->conexion = new PDO("mysql:host=$this->servidor;dbname=$this->basedatos", $this->usuario, $this->clave);
            // Establecer el modo de error de PDO a excepciones
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // USUARIOS //

    public function comprueba($usuario) {
        $exito=false;
        $login=$usuario['DNI'];
        $clave=$usuario['clave'];
	$sql = "SELECT Nombre, TipoUsu FROM usuarios WHERE DNI = :login AND Contrasena = :clave";
    $consulta = $this->conexion->prepare($sql);
    $consulta->bindParam(':login', $login);
    $consulta->bindParam(':clave', $clave);
    $consulta->execute();

    $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($resultado !== false) {
        $exito = true;
        $nombreUsuario = $resultado["Nombre"];
        $tipoUsuario = $resultado["TipoUsu"];
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
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resultados as $fila) {
            $estado = new Estado($fila);
            array_push($estados, $estado->toArray());
        }

        return $estados;
    }

    // VEHICULOS POR USUARIO //

    public function cochesUsu($usuario) {
        $coches = array();

        $sql = "SELECT * FROM coches WHERE DNI = :usuario";
        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':usuario', $usuario);
        $consulta->execute();

        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resultados as $fila) {
            $coche = new Coche($fila);
            array_push($coches, $coche->toArray());
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

        $sql = "SELECT * FROM coches";
        $consulta = $this->conexion->query($sql);

        while ($tupla = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $coche = new Coche($tupla);
            array_push($coches, $coche);
        }

        return $coches;
    }

    public function cocheUsu($matricula) {
        $coches = array();

        $sql = "SELECT * FROM coches WHERE Matricula = :matricula";
        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':matricula', $matricula);
        $consulta->execute();

        while ($tupla = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $coche = new Coche($tupla);
            array_push($coches, $coche);
        }

        return $coches;
    }

    public function plazasDisponibles() {
        $plazas = array();

        $sql = "SELECT Plaza FROM coches";
        $consulta = $this->conexion->query($sql);

        while ($tupla = $consulta->fetch(PDO::FETCH_ASSOC)) {
            array_push($plazas, $tupla['Plaza']);
        }

        return $plazas;
    }

    public function guardarVehiculo($datosF) {
        $Matricula = $datosF['Matricula'];
        $DNI = $datosF['DNI'];
        $Marca = $datosF['Marca'];
        $Modelo = $datosF['Modelo'];
        $Color = $datosF['Color'];
        $Plaza = $datosF['Plaza'];
        $Imagen = $datosF['Imagen'];

        $sql = "INSERT INTO coches (Matricula, DNI, Marca, Modelo, Color, Plaza, Imagen) 
                VALUES (:matricula, :dni, :marca, :modelo, :color, :plaza, :imagen)";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':matricula', $Matricula);
        $consulta->bindParam(':dni', $DNI);
        $consulta->bindParam(':marca', $Marca);
        $consulta->bindParam(':modelo', $Modelo);
        $consulta->bindParam(':color', $Color);
        $consulta->bindParam(':plaza', $Plaza);
        $consulta->bindParam(':imagen', $Imagen);
        $exito = $consulta->execute();

        return $exito;
    }

    public function modVehiculo($datosF) {
        $Matricula = $datosF['Matricula'];
        $Color = $datosF['Color'];
        $Plaza = $datosF['Plaza'];
        $Imagen = $datosF['Imagen'];

        if ($Imagen === '') {
            $sql = "UPDATE coches SET Color = :color, Plaza = :plaza WHERE Matricula = :matricula";
        } else {
            $sql = "UPDATE coches SET Color = :color, Plaza = :plaza, Imagen = :imagen WHERE Matricula = :matricula";
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':color', $Color);
        $consulta->bindParam(':plaza', $Plaza);
        $consulta->bindParam(':matricula', $Matricula);

        if ($Imagen !== '') {
            $consulta->bindParam(':imagen', $Imagen);
        }

        $exito = $consulta->execute();

        return $exito;
    }

    public function borrarVehiculo($matricula) {
        $sql = "DELETE FROM coches WHERE Matricula = :matricula";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':matricula', $matricula);
        $exito = $consulta->execute();

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

        $sql = "SELECT u.*, IFNULL(GROUP_CONCAT(c.Plaza), 'NA') AS Plazas 
                FROM usuarios u 
                LEFT JOIN coches c USING (DNI) 
                GROUP BY u.DNI";

        $consulta = $this->conexion->query($sql);

        while ($tupla = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $cliente = new Cliente($tupla);
            array_push($clientes, $cliente);
        }

        return $clientes;
    }

    public function guardarCliente($datosF) {
        $DNI = $datosF['DNI'];
        $Nombre = $datosF['Nombre'];
        $Apellido = $datosF['Apellido'];
        $Contrasena = $datosF['Contrasena'];
        $TipoUsu = $datosF['TipoUsu'];
        $Avatar = $datosF['Avatar'];

        $sql = "INSERT INTO usuarios (DNI, Nombre, Apellido, Contrasena, TipoUsu, Avatar) 
                VALUES (:dni, :nombre, :apellido, :contrasena, :tipousu, :avatar)";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':dni', $DNI);
        $consulta->bindParam(':nombre', $Nombre);
        $consulta->bindParam(':apellido', $Apellido);
        $consulta->bindParam(':contrasena', $Contrasena);
        $consulta->bindParam(':tipousu', $TipoUsu);
        $consulta->bindParam(':avatar', $Avatar);
        $exito = $consulta->execute();

        return $exito;
    }

   public function modCliente($datosF) {
        $DNI = $datosF['DNI'];
        $Contrasena = $datosF['Contrasena'];
        $Avatar = $datosF['Avatar'];

        if ($Avatar === '') {
            $sql = "UPDATE usuarios SET Contrasena = :contrasena WHERE DNI = :dni";
        } else if ($Contrasena === '') {
            $sql = "UPDATE usuarios SET Avatar = :avatar WHERE DNI = :dni";
        } else {
            $sql = "UPDATE usuarios SET Contrasena = :contrasena, Avatar = :avatar WHERE DNI = :dni";
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':dni', $DNI);
        $consulta->bindParam(':contrasena', $Contrasena);
        $consulta->bindParam(':avatar', $Avatar);
        $exito = $consulta->execute();

        return $exito;
    }

    public function borrarCliente($DNI) {
        $sql = "DELETE FROM usuarios WHERE DNI = :dni";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':dni', $DNI);
        $exito = $consulta->execute();

        return $exito;
    }
    
    /* Cierra la conexión con la base de datos */
    public function cerrar() {
        $this->conexion = null;
    }
    
}
?>
