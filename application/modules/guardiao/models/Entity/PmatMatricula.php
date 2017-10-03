<?php
/**
 * O Model Entity é a Classe responsável pela definição dos atributos e métodos 
 * get e set de cada atributo.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Guardiao_Model_Entity_PmatMatricula
{
    private $_codigo;
    private $_idPessoa;
    private $_secaoSubsecaoLotacao;
    private $_unidadeLotacao;
    private $_dataInicio;
    private $_dataFim;
    private $_emailInterno;
    private $_nomeGuerra;
    private $_idTipoMatricula;
    
    public function getCodigo() {
        return $this->_codigo;
    }

    public function setCodigo($codigo) {
        $this->_codigo = $codigo;
        return $this;
    }

    public function getIdPessoa() {
        return $this->_idPessoa;
    }

    public function setIdPessoa($idPessoa) {
        $this->_idPessoa = $idPessoa;
        return $this;
    }

    public function getSecaoSubsecaoLotacao() {
        return $this->_secaoSubsecaoLotacao;
    }

    public function setSecaoSubsecaoLotacao($secaoSubsecaoLotacao) {
        $this->_secaoSubsecaoLotacao = $secaoSubsecaoLotacao;
        return $this;
    }

    public function getUnidadeLotacao() {
        return $this->_unidadeLotacao;
    }

    public function setUnidadeLotacao($unidadeLotacao) {
        $this->_unidadeLotacao = $unidadeLotacao;
        return $this;
    }

    public function getDataInicio() {
        return $this->_dataInicio;
    }

    public function setDataInicio($dataInicio) {
        $this->_dataInicio = $dataInicio;
        return $this;
    }

    public function getDataFim() {
        return $this->_dataFim;
    }

    public function setDataFim($dataFim) {
        $this->_dataFim = $dataFim;
        return $this;
    }

    public function getEmailInterno() {
        return $this->_emailInterno;
    }

    public function setEmailInterno($emailInterno) {
        $this->_emailInterno = $emailInterno;
        return $this;
    }

    public function getNomeGuerra() {
        return $this->_nomeGuerra;
    }

    public function setNomeGuerra($nomeGuerra) {
        $this->_nomeGuerra = $nomeGuerra;
        return $this;
    }

    public function getIdTipoMatricula() {
        return $this->_idTipoMatricula;
    }

    public function setIdTipoMatricula($idTipoMatricula) {
        $this->_idTipoMatricula = $idTipoMatricula;
        return $this;
    }
}
