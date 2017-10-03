<?php

class Guardiao_ControleController extends Zend_Controller_Action
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
        $this->view->title = "Controle";
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'CTRL_ID_CONTROLE_SISTEMA');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        $table = new Application_Model_DbTable_OcsTbCtrlControleSistema();
        $select = $table->getList();
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    public function delAction()
    {
        $this->view->title = "Desativar Controle";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Guardiao_Form_Controle();
        $table  = new Application_Model_DbTable_OcsTbPaplPapel();
        $data = $this->getRequest()->getPost();
        
        try{
//              $select = $table->getDeletar($id);
//              $msg_to_user = "Papel alterado com Sucesso";
//              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
               /*AINDA NÃO IMPLEMENTADO*/
              $msg_to_user = "Não é possível excluir o papel";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível excluir o papel";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $papelalterar = new Zend_Session_Namespace('papelalterarNs');
          $this->_helper->_redirector('index','controle','guardiao');
    }
    
   public function addAction()
   {
       $this->view->title = "Criar Controle";
       $table  = new Application_Model_DbTable_OcsTbCtrlControleSistema();
       $form   = new Guardiao_Form_Controle();
       
        $form->removeElement('Alterar');
        $this->view->form = $form;
       
       if ($this->getRequest()->isPost()) {
           $data = $this->getRequest()->getPost();
           try{
              if($table->getVerificacao($data['CTRL_NM_CONTROLE_SISTEMA'], $data['CTRL_ID_MODULO'])){
                  $msg_to_user = "Controle já associado!";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                  $this->_helper->_redirector('add','controle','guardiao');
              }else{
                  unset ($data["CTRL_ID_CONTROLE_SISTEMA"]);
                  $data['CTRL_NM_CONTROLE_SISTEMA'] = new Zend_Db_Expr("LOWER('$data[CTRL_NM_CONTROLE_SISTEMA]')");
                  $row = $table->createRow($data);
                  $row->save();
                  $msg_to_user = "Controle associado com Sucesso";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
              }
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível associar o controle ao modulo";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $papelalterar = new Zend_Session_Namespace('papelalterarNs');
          $this->_helper->_redirector('index','controle','guardiao');
       }
   }
   
   public function editAction()
   {
       $this->view->title = "Editar Controle"; 
       $id = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
       $table  = new Application_Model_DbTable_OcsTbCtrlControleSistema();
       $form   = new Guardiao_Form_Controle();
       
        $form->removeElement('Associar');
        
        $row = $table->fetchRow(array('CTRL_ID_CONTROLE_SISTEMA = ?' => $id));
        if ($row) {
            $data = $row->toArray();
            $form->populate($data);
        }
        
        $this->view->form = $form;
       
       if ($this->getRequest()->isPost()) {
           $data = $this->getRequest()->getPost();
           try{
              $table->getUpdate($data['CTRL_ID_MODULO'], $data['CTRL_NM_CONTROLE_SISTEMA'], $data['CTRL_ID_CONTROLE_SISTEMA']);
              $msg_to_user = "Papel alterado com Sucesso";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível excluir o papel";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $papelalterar = new Zend_Session_Namespace('papelalterarNs');
          $this->_helper->_redirector('index','controle','guardiao');
       }
   }
   
}
