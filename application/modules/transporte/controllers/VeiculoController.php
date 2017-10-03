<?php

class Transporte_VeiculoController extends Zend_Controller_Action
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
        $order_column = $this->_getParam('ordem', 'MODE_DS_MODELO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

        $dados = new Application_Model_DbTable_TraTbVeicVeiculo();
        $rows = $dados->getVeiculo($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

        $this->view->title = 'Veículos';
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }    

    public function addAction()
    {        
        $this->view->title = 'Cadastrar novo Veículo';
        $form = new Transporte_Form_Veiculo();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbVeicVeiculo();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['VEIC_ID_VEICULO']);
                $message = $data['VEIC_CD_PLACA'];
                $row = $table->createRow($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O veículo: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','veiculo','transporte');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Veiculo';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Transporte_Form_Veiculo();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbVeicVeiculo();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('VEIC_ID_VEICULO = ?' => $id));
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
                $message = $data['VEIC_CD_PLACA'];
                $row = $table->find($data['VEIC_ID_VEICULO'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O veículo: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','veiculo','transporte');
            }
        }
    }
    
}