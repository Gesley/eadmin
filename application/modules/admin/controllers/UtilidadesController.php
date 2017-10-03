<?php

class Admin_UtilidadesController extends Zend_Controller_Action
{
	/**
	 * Timer para mensuracao do tempo de carregamento da pagina
	 *
	 * @var int $_temporizador
	 */
	private $_temporizador;
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da pagina
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
    public function init()
    {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    }
    
     /**
     * Ajax que recebe o id do tipo do do documento e retorna
     * a descrição do tipo do documento.
     */
    public function ajaxidnometipodocAction() {
        $idDocumento = $this->_getParam('term', '');
        $ocsDtpdTipoDoc = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $nome_array = $ocsDtpdTipoDoc->getAjaxTipoDocumentoPesq($idDocumento);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }
    
    /**
     * Ajax que retorna os Id e nomes de Orgãos externos Pessoa Juridica
     */
    public function ajaxnomepessoajuridicaAction()
    {
        $nomeDestinatario     = $this->_getParam('term','');
        $OcsTbPessPessoa = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $nome_array = $OcsTbPessPessoa->getNomeDestinatarioAjax($nomeDestinatario);
        $fim = count($nome_array);
        for ($i = 0; $i<$fim;$i++){
            $nome_array[$i] = array_change_key_case ($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }
    
    /**
     *  Ajax que retorna o id e nome de pessoas fisicas e juridias
     */
    public function ajaxnomefisicajuridicaAction()
    {
        $nomeDestinatario     = $this->_getParam('term','');
        $OcsTbPessPessoa = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $nome_array = $OcsTbPessPessoa->getNomeRemetenteAjax($nomeDestinatario);
        $fim = count($nome_array);
        for ($i = 0; $i<$fim;$i++){
            $nome_array[$i] = array_change_key_case ($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }
}
