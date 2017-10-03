<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sisad_LancamentofaseController extends Zend_Controller_Action {
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
		$this->view->titleBrowser = 'e-Sisad';
    }

    public function indexAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'FADM_DS_FASE');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/
        
        $table = new Application_Model_DbTable_SadTbFadmFaseAdm();
        $select = $table->select()->order($order_aux);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        
        $this->view->title = "Fases Administrativas";
        $MofaMoviFase =new Application_Model_DbTable_SadTbMofaMoviFase();
//        $MofaMoviFase->fetchNew()->toArray();
        Zend_Debug::dump($MofaMoviFase->fetchNew()->toArray());
        //$this->_helper->layout->disableLayout();

    }
    public function listajaxAction()
    {
       // $this->_helper->layout->disableLayout();
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'FADM_DS_FASE');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        $table = new Application_Model_DbTable_SadTbFadmFaseAdm();
        $select = $table->select()->order($order_aux);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Fases Administrativas";
    }

    public function formAction()
    {
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sisad_Form_Lancamentofase();
        $table  = new Application_Model_DbTable_SadTbFadmFaseAdm();

        if ($id) {
            $form->FADM_DS_FASE->setAttrib('disabled', 'disabled');
            $row = $table->fetchRow(array('FADM_ID_FASE = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        $this->view->form = $form;
    }

    public function saveAction()
    {
        $form = new Sisad_Form_Lancamentofase();
        $table = new Application_Model_DbTable_SadTbMofaMoviFase();

        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $data['MOFA_DH_FASE']=new Zend_Db_Expr("SYSDATE");
                $data['MOFA_CD_MATRICULA']= 'TR17539PS';
                Zend_Debug::dump($data);
                exit;

                if (isset($data['MOFA_ID_MOVIMENTACAO'])){
                    $row = $table->find($data['MOFA_ID_MOVIMENTACAO'])->current();
                    $row->setFromArray($data);
                } else {
                    unset($data['MOFA_ID_MOVIMENTACAO']);
                    $row = $table->createRow($data);
                }
                $id = $row->save();
                $this->_helper->flashMessenger->addMessage('Registrado com Sucesso');
                $this->_redirect('sisad/lancamentofase/index/');

            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }

}
