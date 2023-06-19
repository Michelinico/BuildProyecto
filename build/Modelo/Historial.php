<?php

class Historial {

    private $Entrada;
    private $Salida;

    public function __construct($datos) {
        if ($datos != null) {
            $this->Entrada=$datos["Entrada"];
            $this->Salida=$datos["Salida"];
        }
    }

    public function getEntrada() {
        return $this->Entrada;
    }

    public function getSalida() {
        return $this->Salida;
    }

    public function setEntrada($valor) {
        $this->Entrada=$valor;
    }

    public function setSalida($valor) {
        $this->Salida=$valor;
    } 

    public function toArray() {
        return (get_object_vars($this));
    }
    
}

?>