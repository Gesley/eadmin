<?php

class Guardiao_AjudaController extends Zend_Controller_Action
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
		$this->view->titleBrowser = 'e-Guardião - Sistema de Gerenciamento de Permissões';
    }

    public function indexAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'ACAO_ID_ACAO_SISTEMA');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        $table = new Application_Model_DbTable_OcsTbAcaoAcaoSistema();
        $select = $table->getList();

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Ajuda";
       }
    
   
    public function editAction() {
        $this->view->title = "Editar Ajuda";
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $table = new Application_Model_DbTable_OcsTbAcaoAcaoSistema();
        $form = new Guardiao_Form_Ajuda();
        $acao = $table->getAjudaAcao($id);
        $form->populate($acao);
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $registro = $table->find($id)->current();
            $data['ACAO_ID_ACAO_SISTEMA'] = $id;
            $registro->setFromArray ( $data );
            try {
                $codigo = $registro->save ();
                $this->_helper->flashMessenger(array('message' => 'Ajuda e Informação alterados com sucesso', 'status' => 'success'));
            } catch (Zend_Exception $e) {
                $msg_to_user = "Não é possível excluir o papel";
                $this->_helper->flashMessenger(array('message' => $e->getMessage (), 'status' => 'notice'));
            }
            $this->_helper->_redirector('index', 'ajuda', 'guardiao');
        }
    }
       
    
    public function detalheAction() {
        $id = Zend_Filter::FilterStatic($this->_getParam('acao'), 'int');
        $table = new Application_Model_DbTable_OcsTbAcaoAcaoSistema();
        $acao = $table->getAjudaAcao($id);
        $this->view->ajuda = $acao;
        }
       
  
  
}
