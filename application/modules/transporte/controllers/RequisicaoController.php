<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Transporte_RequisicaoController extends Zend_Controller_Action {

    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Transporte';
    }

    public function indexAction()
    {
        //exit('gkjhgkjhkjhkjhkjh');
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $order = $this->_getParam('ORDER', 'REQU_ID_REQUISICAO');
        $direction = $this->_getParam('direction', 'ASC');

        $table = new Application_Model_DbTable_TraTbRequRequisicao();
        $select = $table->select();

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage(10);

        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->dir = array('REQU_ID_REQUISICAO' => 'ASC',
                                 'description'  => 'ASC');
        $this->view->data = $paginator;
    }

//    public function formAction()
//    {
//        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
//        $form   = new Sisad_Form_Faseadm();
//        $table  = new Application_Model_DbTable_SadTbFadmFaseAdm();
//
//        if ($id) {
//            $form->FADM_DS_FASE->setAttrib('disabled', 'disabled');
//            $row = $table->fetchRow(array('FADM_ID_FASE = ?' => $id));
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
//        $form = new Sisad_Form_Faseadm();
//        $table = new Application_Model_DbTable_SadTbFadmFaseAdm();
//
//        if ($this->getRequest()->isPost()){
//            $data = $this->getRequest()->getPost();
//            if ($form->isValid($data)) {
//                if (isset($data['FADM_ID_FASE']) && $data['FADM_ID_FASE']) {
//                    $row = $table->find($data['FADM_ID_FASE'])->current();
//                    $row->setFromArray($data);
//                } else {
//                    unset($data['FADM_ID_FASE']);
//                    $row = $table->createRow($data);
//                }
//                $id = $row->save();
//                $this->_helper->flashMessenger ( array('message' => 'Registrado!', 'status' => 'success'));
//                return $this->_helper->_redirector('index','requisicao','transporte', array('id' => $id));
//
//            } else {
//                $form->populate($data);
//                $this->view->form = $form;
//                $this->render('form');
//            }
//        }
//    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar nova Requisição de Transporte';
        $form = new Transporte_Form_Requisicao();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbRequRequisicao();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['REQU_ID_REQUISICAO']);
                $message = $data['REQU_ID_REQUISICAO'];
                $row = $table->createRow($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "A requisição: $message foi cadastrada!", 'status' => 'success'));
                return $this->_helper->_redirector('index','requisicao','transporte');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Requisição';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Transporte_Form_Requisicao();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbRequRequisicao();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('REQU_ID_REQUISICAO = ?' => $id));
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
                $message = $data['REQU_ID_REQUISICAO'];
                $row = $table->find($data['REQU_ID_REQUISICAO'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "A requisição: $message foi atualizada!", 'status' => 'success'));
                return $this->_helper->_redirector('index','requisicao','transporte');
            }
        }
    }

}