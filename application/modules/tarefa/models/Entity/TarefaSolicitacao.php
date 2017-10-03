<?php
/**
 * O Model Entity é a Classe responsável pela definição dos atributos e métodos 
 * get e set de cada atributo.
 * 
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */

class Tarefa_Model_Entity_TarefaSolicitacao 
{

    private $_id;
    private $_documento;
    private $_tarefa;
    private $_anexo;
    private $_matriculaAtendente;
    private $_aceiteAtendente;  
    private $_justificativaAtendente;
    private $_matriculaAvalDefeito;
    private $_dataAvalDefeito;
    private $_situacaoNegociacao;
    private $_aceiteSolicitante;
    private $_justificativaSolicitante;
    private $_dataAvalSolicitante;
    
    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }
    
    public function getDocumento() {
        return $this->_documento;
    }

    public function setDocumento($documento) {
        $this->_documento = $documento;
        return $this;
    }

    public function getTarefa() {
        return $this->_tarefa;
    }

    public function setTarefa($tarefa) {
        $this->_tarefa = $tarefa;
        return $this;
    }

    public function getAnexo() {
        return $this->_anexo;
    }

    public function setAnexo($anexo) {
        $this->_anexo = $anexo;
        return $this;
    }
    
    public function getMatriculaAtendente() {
        return $this->_matriculaAtendente;
    }

    public function setMatriculaAtendente($matriculaAtendente) {
        $this->_matriculaAtendente = $matriculaAtendente;
        return $this;
    }
        
    public function getAceiteAtendente() {
        return $this->_aceiteAtendente;
    }

    public function setAceiteAtendente($aceiteAtendente) {
        $this->_aceiteAtendente = $aceiteAtendente;
        return $this;
    }
     
    public function getJustificativaAtendente() {
        return $this->_justificativaAtendente;
    }

    public function setJustificativaAtendente($justificativaAtendente) {
        $this->_justificativaAtendente = $justificativaAtendente;
        return $this;
    }
        
    public function getMatriculaAvalDefeito() {
        return $this->_matriculaAvalDefeito;
    }

    public function setMatriculaAvalDefeito($matriculaAvalDefeito) {
        $this->_matriculaAvalDefeito = $matriculaAvalDefeito;
        return $this;
    }
        
    public function getDataAvalDefeito() {
        return $this->_dataAvalDefeito;
    }

    public function setDataAvalDefeito($dataAvalDefeito) {
        $this->_dataAvalDefeito = $dataAvalDefeito;
        return $this;
    }
        
    public function getSituacaoNegociacao() {
        return $this->_situacaoNegociacao;
    }

    public function setSituacaoNegociacao($situacaoNegociacao) {
        $this->_situacaoNegociacao = $situacaoNegociacao;
        return $this;
    }
        
    public function getAceiteSolicitante() {
        return $this->_aceiteSolicitante;
    }

    public function setAceiteSolicitante($aceiteSolicitante) {
        $this->_aceiteSolicitante = $aceiteSolicitante;
        return $this;
    }

    public function getJustificativaSolicitante() {
        return $this->_justificativaSolicitante;
    }

    public function setJustificativaSolicitante($justificativaSolicitante) {
        $this->_justificativaSolicitante = $justificativaSolicitante;
        return $this;
    }
    
    public function getDataAvalSolicitante() {
        return $this->_dataAvalSolicitante;
    }

    public function setDataAvalSolicitante($dataAvalSolicitante) {
        $this->_dataAvalSolicitante = $dataAvalSolicitante;
        return $this;
    }

}
