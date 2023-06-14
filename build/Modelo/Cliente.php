<?php

class Cliente {

    private $DNI;
    private $Nombre;
    private $Apellido;
    private $Contrasena;
    private $telefono;
    private $TipoUsu;
    private $Avatar;
    private $Plazas;

    public function __construct($datos) {
        if ($datos != null) {
            $this->DNI=$datos["DNI"];
            $this->Nombre=$datos["Nombre"];
            $this->Apellido=$datos["Apellido"];
            $this->Contrasena=$datos["Contrasena"];
            $this->Telefono=$datos["Telefono"];
            $this->TipoUsu=$datos["TipoUsu"];
            $this->Avatar=$datos["Avatar"];
            $this->Plazas=$datos["Plazas"];
        }
    }

    public function getMatricula() {
        return $this->Matricula;
    }

    public function getDNI() {
        return $this->DNI;
    }

    public function getNombre() {
        return $this->Nombre;
    }

    public function getApellido() {
        return $this->Apellido;
    }

    public function getContrasena() {
        return $this->Contrasena;
    }

    public function getTelefono() {
        return $this->Telefono;
    }

    public function getTipoUsu() {
        return $this->TipoUsu;
    }

    public function getAvatar() {
        return $this->Avatar;
    }

    public function getPlazas() {
        return $this->Plazas;
    }

    public function setMatricula($valor) {
        $this->Matricula=$valor;
    }

    public function setDNI($valor) {
        $this->DNI=$valor;
    }

    public function setNombre($valor) {
        $this->Nombre=$valor;
    }

    public function setApellido($valor) {
        $this->Apellido=$valor;
    }

    public function setContrasena($valor) {
        $this->Contrasena=$valor;
    }

    public function setTelefono($valor) {
        $this->Telefono=$valor;
    }

    public function setTipoUsu($valor) {
        $this->TipoUsu=$valor;
    }

    public function setAvatar($valor) {
        $this->Avatar=$valor;
    }

    public function setPlazas($valor) {
        $this->Plazas=$valor;
    }

    public function toArray() {
        return (get_object_vars($this));
    }
    
}

?>