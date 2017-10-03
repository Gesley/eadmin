<?php
/**
 * O Model Entity é a Classe responsável pela definição dos atributos e métodos 
 * get e set de cada atributo.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Model_Entity_Tarefa 
{

    private $_id;
    private $_tipo;
    private $_descricao;
    private $_matricula;
    private $_status;
    private $_nomeAtendente;

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }
    
    public function getTipo() {
        return $this->_tipo;
    }

    public function setTipo($tipo) {
        $this->_tipo = $tipo;
        return $this;
    }

    public function getDescricao() {
        return $this->_descricao;
    }

    public function setDescricao($decricao) {
        $this->_descricao = $decricao;
        return $this;
    }
    
    public function getMatricula() {
        return $this->_matricula;
    }

    public function setMatricula($matricula) {
        $this->_matricula = $matricula;
        return $this;
    }
    
    public function getNomeAtendente() {
        return $this->_nomeAtendente;
    }

    public function setNomeAtendente($nomeAtendente) {
        $this->_nomeAtendente = $nomeAtendente;
        return $this;
    }
    
    public function getStatus() {
        return $this->_status;
    }

    public function setStatus($status) {
        $this->_status = $status;
        return $this;
    }

}
