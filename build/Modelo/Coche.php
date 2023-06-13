<?php

class Coche {

    private $Matricula;
    private $DNI;
    private $Marca;
    private $Modelo;
    private $Color;
    private $Plaza;
    private $Imagen;

    public function __construct($datos) {
        if ($datos != null) {
            $this->Matricula=$datos["Matricula"];
            $this->DNI=$datos["DNI"];
            $this->Marca=$datos["Marca"];
            $this->Modelo=$datos["Modelo"];
            $this->Color=$datos["Color"];
            $this->Plaza=$datos["Plaza"];
            $this->Imagen=$datos["Imagen"];
        }
    }

    public function getMatricula() {
        return $this->Matricula;
    }

    public function getDNI() {
        return $this->DNI;
    }

    public function getMarca() {
        return $this->Marca;
    }

    public function getModelo() {
        return $this->Modelo;
    }

    public function getColor() {
        return $this->Color;
    }

    public function getPlaza() {
        return $this->Plaza;
    }

    public function getImagen() {
        return $this->Imagen;
    }

    public function setMatricula($valor) {
        $this->Matricula=$valor;
    }

    public function setDNI($valor) {
        $this->DNI=$valor;
    }

    public function setMarca($valor) {
        $this->Marca=$valor;
    }

    public function setModelo($valor) {
        $this->Modelo=$valor;
    }

    public function setColor($valor) {
        $this->Color=$valor;
    }

    public function setPlaza($valor) {
        $this->Plaza=$valor;
    }

    public function setImange($valor) {
        $this->Imagen=$valor;
    }

    public function toArray() {
        return (get_object_vars($this));
    }
    
}

?>