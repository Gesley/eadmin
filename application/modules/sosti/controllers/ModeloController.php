<?php

class Sosti_ModeloController extends Zend_Controller_Action
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
		$this->view->titleBrowser = 'e-Sosti';
    }

    public function indexAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'MODE_DS_MODELO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

        $dados = new Application_Model_DbTable_OcsTbModeModelo();
        $rows = $dados->getModelo($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage(15);

        $this->view->title = 'Modelos';
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar novo Modelo';
        $form = new Sosti_Form_Modelo();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_OcsTbModeModelo();
        $usuario = new Zend_Session_Namespace('userNs');
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['MODE_ID_MODELO']);
                $data['MODE_CD_MAT_INCLUSAO'] = $usuario->matricula;
                $data['MODE_DT_INCLUSAO'] = new Zend_Db_Expr('SYSDATE');
                $message = $data['MODE_DS_MODELO'];
                $row = $table->createRow($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O modelo: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','servico','sosti');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Modelo';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sosti_Form_Modelo();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_OcsTbModeModelo();
        $usuario = new Zend_Session_Namespace('userNs');
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('MODE_ID_MODELO = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $message = $data['MODE_DS_MODELO'];
                $row = $table->find($data['MODE_ID_MODELO'])->current();
                $data['MODE_CD_MAT_INCLUSAO'] = $usuario->matricula;
                $data['MODE_DT_INCLUSAO'] = new Zend_Db_Expr('SYSDATE');
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O modelo: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','servico','sosti');
            }
        }
    }
    
}
