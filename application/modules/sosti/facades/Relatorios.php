<?php

/**
 * Facade responsável pelos relatorios do SOSTI
 * 
 * @author Daniel Rodrigues <daniel.fernandes@trf1.jus.br> 
 */
class Sosti_Facade_Relatorios
{

    protected $_businessSolicitacoesPorServico;

    public function __construct() {
        $this->_businessSolicitacoesPorServico = new Sosti_Business_SolicitacoesPorServico();
    }

    public function buscaSubsecaoValidacaoForm($parametros, $elementoForm) {
        return $this->_businessSolicitacoesPorServico->buscaSubsecaoValidacaoForm($parametros, $elementoForm);
    }
    
    public function buscaUnidadeValidacaoForm($parametros, $elementoForm) {
        return $this->_businessSolicitacoesPorServico->buscaUnidadeValidacaoForm($parametros, $elementoForm);
    }
    
    public function buscaGrupoServValidacaoForm($parametros, $elementoForm) {
        return $this->_businessSolicitacoesPorServico->buscaGrupoServValidacaoForm($parametros, $elementoForm);
    }
    
    public function buscaCatServValidacaoForm($parametros, $elementoForm) {
        return $this->_businessSolicitacoesPorServico->buscaCatServValidacaoForm($parametros, $elementoForm);
    }

    public function buscaCaixaValidacaoForm($parametros, $elementoForm) {
        return $this->_businessSolicitacoesPorServico->buscaCaixaValidacaoForm($parametros, $elementoForm);
    }

    public function listBusiness($parametros) {
        return $this->_businessSolicitacoesPorServico->listAllBusiness($parametros);
    }

}