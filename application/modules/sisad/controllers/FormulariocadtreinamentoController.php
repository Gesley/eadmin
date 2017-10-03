<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sisad_FormulariocadtreinamentoController extends Zend_Controller_Action {
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
		
		$this->view->titleBrowser = 'e-Sisad';
    }

    public function indexAction()
    {

                $this->view->title = 'Lotações e subseções';
                $db = new Application_Model_DbTable_RhCadTreinamentoServ();
                $lista = $db->getSecao('AP');
                $this->view->secoes = $lista;
                
                
    }
    
   public function formAction()
    {
        $form   = new Sisad_Form_FormularioCadTreinamento ();
        $this->view->form = $form;
        $this->view->title = "Formulário de Requerimento de Cadastramento de Certificado para AQ";
    }
    
    public function ajaxdesccursoAction()
    {
        $desc     = $this->_getParam('term','');

        $RhCadTreinamentoServ = new Application_Model_DbTable_RhCadTreinamentoServ();
        $Descurso = $RhCadTreinamentoServ->getAjaxDescCurso($desc);
        
        $fim =  count($Descurso);
        for ($i = 0; $i<$fim; $i++ ) {
            $Descurso[$i] = array_change_key_case ($Descurso[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($Descurso);
        
    }
    
    public function ajaxcargahorariaAction()
    {
        $desc     = $this->_getParam('term','');
        $aux = explode(' - ', $desc);
        $codigodotipo = $aux[1];
        
        $RhCadTreinamentoServ = new Application_Model_DbTable_RhCadTreinamentoServ();
        $CargaHoraria = $RhCadTreinamentoServ->getAjaxCargaHoraria($codigodotipo);
        
        $this->_helper->json->sendJson($CargaHoraria);
    }
    
    
}
