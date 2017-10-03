<?php
/**
 * O Model Entity é a Classe responsável pela definição dos atributos e métodos 
 * get e set de cada atributo.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_Entity_UserId
{
    private $_nomeBanco;
    private $_codigoSecao;
    private $_matricula;
    private $_password;
    private $_nome;
    private $_status;
    
    public function getNomeBanco() {
        return $this->_nomeBanco;
    }

    public function setNomeBanco($nomeBanco) {
        $this->_nomeBanco = $nomeBanco;
        return $this;
    }

    public function getCodigoSecao() {
        return $this->_codigoSecao;
    }

    public function setCodigoSecao($codigoSecao) {
        $this->_codigoSecao = $codigoSecao;
        return $this;
    }

    public function getMatricula() {
        return $this->_matricula;
    }

    public function setMatricula($matricula) {
        $this->_matricula = $matricula;
        return $this;
    }

    public function getPassword() {
        return $this->_password;
    }

    public function setPassword($password) {
        $this->_password = $password;
        return $this;
    }

    public function getNome() {
        return $this->_nome;
    }

    public function setNome($nome) {
        $this->_nome = $nome;
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
