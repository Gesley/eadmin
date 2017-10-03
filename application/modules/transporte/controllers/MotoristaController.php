<?php

class Transporte_MotoristaController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Transporte';
    }

    public function indexAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'COU_COD_NOME');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

        $dados = new Application_Model_DbTable_TraTbMotoMotorista();
        $rows = $dados->getMotorista($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

        $this->view->title = 'Motoristas';
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
            public function addAction()
    {
        $this->view->title = 'Cadastrar novo Motorista';
        $form = new Transporte_Form_Motorista();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbMotoMotorista();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['MOTO_ID_MOTORISTA']);
                $message = $data['MOTO_ID_MOTORISTA'];
                $row = $table->createRow($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O motorista: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','motorista','transporte');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Motorista';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Transporte_Form_Motorista();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbMotoMotorista();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('MOTO_ID_MOTORISTA = ?' => $id));
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
                $message = $data['MOTO_ID_MOTORISTA'];
                $row = $table->find($data['MOTO_ID_MOTORISTA'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O motorista: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','motorista','transporte');
            }
        }
//    public function formAction()
//    {
//        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
//        $form   = new Transporte_Form_Motorista();
//        $table  = new Application_Model_DbTable_TraTbMotoMotorista();
//
//        if ($id) {
//            $row = $table->fetchRow(array('MOTO_ID_MOTORISTA = ?' => $id));
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
//        $form = new Transporte_Form_Motorista();
//        $table = new Application_Model_DbTable_TraTbMotoMotorista();
//
//        if ($this->getRequest()->isPost()){
//            $data = $this->getRequest()->getPost();
//            if ($form->isValid($data)) {
//                if (isset($data['MOTO_ID_MOTORISTA']) && $data['MOTO_ID_MOTORISTA']) {
//                    $row = $table->find($data['MOTO_ID_MOTORISTA'])->current();
//                    $row->setFromArray($data);
//                } else {
//                    unset($data['MOTO_ID_MOTORISTA']);
//                    $row = $table->createRow($data);
//                }
//                $id = $row->save();
//                $this->_helper->flashMessenger ( array('message' => 'Registrado!', 'status' => 'success'));
//                return $this->_helper->_redirector('index','motorista','transporte', array('id' => $id));
//
//            } else {
//                $form->populate($data);
//                $this->view->form = $form;
//                $this->render('form');
//            }
//        }
//    }
}
}

