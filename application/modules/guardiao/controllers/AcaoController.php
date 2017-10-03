<?php

class Guardiao_AcaoController extends Zend_Controller_Action
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

        $this->view->title = "Ações";

        //$this->_helper->layout->disableLayout();
    }
    
    public function delAction()
    {
        $this->view->title = "Desativar Ação";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Guardiao_Form_Acao();
        $table  = new Application_Model_DbTable_OcsTbPaplPapel();
        $data = $this->getRequest()->getPost();
        
        try{
//              $select = $table->getDeletar($id);
//              $msg_to_user = "Papel alterado com Sucesso";
//              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
              /*AINDA NAO IMPLEMENTADO*/
              $msg_to_user = "Não é possível excluir a ação";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível excluir a ação";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $papelalterar = new Zend_Session_Namespace('papelalterarNs');
          $this->_helper->_redirector('index','acao','guardiao');
    }
   
    public function addAction()
    {
        $this->view->title = "Criar Ação";
        $form   = new Guardiao_Form_Acao();
        $table  = new Application_Model_DbTable_OcsTbAcaoAcaoSistema();
        
        $form->removeElement('Alterar');
        $this->view->form = $form;
        
        if ($this->getRequest()->isPost()) {
          $data = $this->getRequest()->getPost();
          $consulta = $table->getVerifica($data['CTRL_ID_MODULO'], $data['ACAO_NM_ACAO_SISTEMA'], $data['ACAO_ID_CONTROLE_SISTEMA']);
          try{
              if($consulta['ACAO']==1){
                  $msg_to_user = "Ação já cadastrada!";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
              }else{
                  unset ($data['ACAO_ID_ACAO_SISTEMA']);
                  $data['ACAO_NM_ACAO_SISTEMA'] = new Zend_Db_Expr("LOWER('$data[ACAO_NM_ACAO_SISTEMA]')");
                  $row = $table->createRow($data);
                  $row->save();
                  $msg_to_user = "Ação associada com Sucesso";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
              }
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível associar ação ao controle";
              $this->_helper->flashMessenger ( array('message' => $error_string, 'status' => 'notice'));
          }
          $this->_helper->_redirector('index','acao','guardiao');
        }
        
    }
    
    public function editAction()
    {
      $this->view->title = "Editar Ação";
      $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
      $form   = new Guardiao_Form_Acao();
      $table  = new Application_Model_DbTable_OcsTbAcaoAcaoSistema();
        

      $form->removeElement('Associar');
      $row = $table->getAcaoById($id);
      if ($row) {
          $data = $row;
          $form->populate($data);
      }
      $this->view->form = $form; 
        
      if ($this->getRequest()->isPost()) {
          $data = $this->getRequest()->getPost(); 
          $consulta = $table->getVerifica($data['CTRL_ID_MODULO'], $data['ACAO_NM_ACAO_SISTEMA'], $data['ACAO_ID_CONTROLE_SISTEMA']);
          try{
              if ($consulta['ACAO']==1){
                  $msg_to_user = "Ação já cadastrada!";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
              }else{
                  $row = $table->find($id)->current();
                  $row = $row->setFromArray($data);
                  $row->save();
                  $msg_to_user = "Papel alterado com Sucesso";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
              }
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível excluir o papel";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $papelalterar = new Zend_Session_Namespace('papelalterarNs');
          $this->_helper->_redirector('index','acao','guardiao');
      }
    }
   
   public function ajaxcontroleAction()
    {
        $id_modulo = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $OcsTbAcaoAcaoSistema = new Application_Model_DbTable_OcsTbAcaoAcaoSistema();        
        $controle_array = $OcsTbAcaoAcaoSistema->getControle($id_modulo);
        $this->view->controle_array = $controle_array;
    }
}
