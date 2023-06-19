<?php

class Estado {

    private $Matricula;
    private $Estado;
    private $UltimaEntrada;
    private $UltimaSalida;
    private $Pagar;

    public function __construct($datos) {
        if ($datos != null) {
            $this->Matricula=$datos["Matricula"];
            $this->Estado=$datos["Estado"];
            $this->UltimaEntrada=$datos["UltimaEntrada"];
            $this->UltimaSalida=$datos["UltimaSalida"];
            $this->Pagar=$datos["Pagar"];
        }
    }

    public function getMatricula() {
        return $this->Matricula;
    }

    public function getEstado() {
        return $this->Estado;
    }

    public function getUltimaEntrada() {
        return $this->UltimaEntrada;
    }

    public function getUltimaSalida() {
        return $this->UltimaSalida;
    }

    public function getPagar() {
        return $this->Pagar;
    }

    public function setMatricula($valor) {
        $this->Matricula=$valor;
    }

    public function setEstado($valor) {
        $this->Estado=$valor;
    }

    public function setUltimaEntrada($valor) {
        $this->UltimaEntrada=$valor;
    }

    public function setUltimaSalida($valor) {
        $this->UltimaSalida=$valor;
    }

    public function setPagar($valor) {
        $this->Pagar=$valor;
    }    

    public function toArray() {
        return (get_object_vars($this));
    }
    
}

?>