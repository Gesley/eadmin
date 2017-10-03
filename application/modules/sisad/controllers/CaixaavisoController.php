<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sisad_CaixaavisoController extends Zend_Controller_Action {
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
		
        /**/
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
		$this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function entradaAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/
        
        $dados = new Application_Model_DbTable_SadTbDoliDocumentoLista();
        $rows = $dados->getCaixaAvisosPessoais($userNs->matricula);
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['CAIXA_REQUISICAO'] = 'aviso_pessoal';
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(10);


        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->title = 'Caixa de Avisos Pessoal';
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

       //$this->_helper->layout->disableLayout();
    }
    
    public function entradaunidadeAction()
    {
        /*Variáveis de sessão*/
        $userNs = new Zend_Session_Namespace('userNs');
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/
        
        $dados = new Application_Model_DbTable_SadTbDoliDocumentoLista();
        $rows = $dados->getCaixaAvisosUnidade($userNs->siglasecao, $userNs->codlotacao);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(10);


        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->title = 'Caixa de Avisos da Unidade';
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

       //$this->_helper->layout->disableLayout();
    }
    
}
