<?php

class Guardiao_ModuloController extends Zend_Controller_Action
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
		$this->view->titleBrowser = "e-Guardião";
    }

   public function indexAction()
   {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'MODL_ID_MODULO');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        $table = new Application_Model_DbTable_OcsTbModlModulo();
        $select = $table->select()->order($order_aux);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Módulos";

        //$this->_helper->layout->disableLayout();
    }
    
   public function delAction()
   {
        $this->view->title = "Excluir Módulo";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        
        try{
              /*AINDA NÃO IMPLEMENTADO*/
              $msg_to_user = "Não é possível excluir o módulo";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível excluir o módulo";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $this->_helper->_redirector('index','modulo','guardiao');
   }
   
   public function addAction()
   {
       $this->view->title = "Associar Módulo";
       $form   = new Guardiao_Form_Modulo();
       $table  = new Application_Model_DbTable_OcsTbModlModulo();
       
       $form->removeElement('Alterar');
       $this->view->form = $form;
       
       if ($this->getRequest()->isPost()) {
           $data = $this->getRequest()->getPost();
           try{
              unset($data["MODL_ID_MODULO"]);
              if ($table->getVerificacao($data['MODL_NM_SISTEMA'],$data['MODL_NM_MODULO'])){
                  $msg_to_user = "Modulo já associado";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                  $this->_helper->_redirector('add','modulo','guardiao');
              }else{
                  $data['MODL_NM_MODULO'] = new Zend_Db_Expr("LOWER('$data[MODL_NM_MODULO]')");
                  $row = $table->createRow($data);
                  $row->save();
                  $msg_to_user = "Modulo associado com Sucesso";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
              }
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível associar o módulo";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $this->_helper->_redirector('index','modulo','guardiao');
       }
   }
   
   public function editAction()
   {
       $this->view->title = "Editar Módulo";
       $table  = new Application_Model_DbTable_OcsTbModlModulo();
       $data = $this->getRequest()->getPost();
       $form   = new Guardiao_Form_Modulo();
       $id = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
       
       if ($this->getRequest()->isPost()) {
           if($form->isValid($data)){
               if($table->getVerifAlteracao($data['MODL_NM_SISTEMA'], $data['MODL_NM_MODULO'])){
                  $msg_to_user = "Módulo já associado";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                  $this->_helper->_redirector('edit','modulo','guardiao');
               }else{
                   try{
                      $id = $data['MODL_ID_MODULO'];
                      $row = $table->find($id)->current();
                      $row = $row->setFromArray($data);
                      $row->save();
                      $msg_to_user = "Módulo alterado com Sucesso";
                      $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                  }  catch (Zend_Exception $error_string){
                      $msg_to_user = "Não é possível alterar o módulo";
                      $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                  }
              }
           }
          $this->_helper->_redirector('index','modulo','guardiao');
       }
       
       $form->removeElement('Associar');
       $this->view->form = $form;
       
       $row = $table->fetchRow(array('MODL_ID_MODULO = ?' => $id));
       if ($row) {
           $data = $row->toArray();
           $form->populate($data);
       }
   }
   
}
