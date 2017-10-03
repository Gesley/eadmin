<?php

class Sosti_MetaalcancadaController extends Zend_Controller_Action
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
        $order_column = $this->_getParam('ordem', 'SMAN_ID_INDICADOR');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SosTbSmanMetaAlcancada();
       $rows = $dados->getMeta($order);

       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(10);

       $this->view->title = "Aferição de Nível de Serviços";
       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function formAction()
    {
        $this->view->title = "Aferição de Nível de Serviços";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sosti_Form_Metaalcancada();
        $table  = new Application_Model_DbTable_SosTbSmanMetaAlcancada();

        if ($id) {
            $row = $table->fetchRow(array('SMAN_ID_INDICADOR = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        $this->view->form = $form;
    }

    public function saveAction()
    {
        $form = new Sosti_Form_Metaalcancada();
        $table = new Application_Model_DbTable_SosTbSmanMetaAlcancada();

        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {

                if (isset($data['SMAN_ID_INDICADOR']) && $data['SMAN_ID_INDICADOR']) {
                    $row = $table->find($data['SMAN_ID_INDICADOR'])->current();
                    $row->setFromArray($data);
                } else {
                    unset($data['SMAN_ID_INDICADOR']);
                    $row = $table->createRow($data);
                }
                $id = $row->save();
                $this->_helper->flashMessenger ( array('message' => 'Registrado!', 'status' => 'success'));
                return $this->_helper->_redirector('index','metaalcancada','sosti', array('id' => $id));

            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }
    
}
