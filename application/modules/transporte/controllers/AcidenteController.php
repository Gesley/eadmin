<?php

class Transporte_AcidenteController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Transporte';
    }

/*    public function indexAction()
    {
        // action body
        $table = new Application_Model_DbTable_TraTbAcidAcidente();
        $select = $table->select();
        Zend_Debug::dump($select);
        exit;
    }*/

    public function indexAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'ACID_ID_ACIDENTE');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        $table = new Application_Model_DbTable_TraTbAcidAcidente();
        $select = $table->select()->order($order_aux);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->title = 'Acidente';
    }
    
        public function addAction()
    {
        $this->view->title = 'Cadastrar novo Acidente';
        $form = new Transporte_Form_Acidente();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbAcidAcidente();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['ACID_ID_ACIDENTE']);
                $message = $data['ACID_ID_ACIDENTE'];
                $row = $table->createRow($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O acidente: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','acidente','transporte');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Acidente';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Transporte_Form_Acidente();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbAcidAcidente();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('ACID_ID_ACIDENTE = ?' => $id));
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
                $message = $data['ACID_ID_ACIDENTE'];
                $row = $table->find($data['ACID_ID_ACIDENTE'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O acidente: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','acidente','transporte');
            }
        }
    }
    
//    public function formAction()
//    {
//        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
//        $form   = new Transporte_Form_Acidente();
//        $table  = new Application_Model_DbTable_TraTbAcidAcidente();
//
//        if ($id) {
//            //$form->FADM_DS_FASE->setAttrib('disabled', 'disabled');
//            $row = $table->fetchRow(array('ACID_ID_ACIDENTE = ?' => $id));
//            if ($row) {
//                $data = $row->toArray();
//                $form->populate($data);
//            }
//        }
//        $this->view->form = $form;
//    }
//
//    public function saveAction()
//    {
//        $form = new Transporte_Form_Acidente();
//        $table = new Application_Model_DbTable_TraTbAcidAcidente();
//
//        if ($this->getRequest()->isPost()){
//            $data = $this->getRequest()->getPost();
//            if ($form->isValid($data)) {
//                if (isset($data['ACID_ID_ACIDENTE']) && $data['ACID_ID_ACIDENTE']) {
//                    $row = $table->find($data['ACID_ID_ACIDENTE'])->current();
//                    $row->setFromArray($data);
//                } else {
//                    unset($data['ACID_ID_ACIDENTE']);
//                    $row = $table->createRow($data);
//                }
//                $id = $row->save();
//                $this->_helper->flashMessenger ( array('message' => 'Registrado!', 'status' => 'success'));
//                return $this->_helper->_redirector('index','acidente','transporte', array('id' => $id));
//
//            } else {
//                $form->populate($data);
//                $this->view->form = $form;
//                $this->render('form');
//            }
//        }
//    }
}

