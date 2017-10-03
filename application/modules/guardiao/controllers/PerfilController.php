<?php

class Guardiao_PerfilController extends Zend_Controller_Action
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
        $order = $this->_getParam('ordem', 'PERF_ID_PERFIL');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        $table = new Application_Model_DbTable_OcsTbPerfPerfil();
        $select = $table->select()->order($order_aux);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Perfis";

        //$this->_helper->layout->disableLayout();
   }

   public function delAction()
   {
        $this->view->title = "Deletar Perfil";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Guardiao_Form_Perfil();
        $table  = new Application_Model_DbTable_OcsTbPerfPerfil();
        $data = $this->getRequest()->getPost();
        
        try{
            /* AINDA NÃO IMPLEMENTADO */
              $msg_to_user = "Não é possível excluir o perfil selecionado";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível excluir o perfil selecionado";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $this->_helper->_redirector('index','perfil','guardiao');
   }
   
   public function addAction()
   {
       $form   = new Guardiao_Form_Perfil();
       $table  = new Application_Model_DbTable_OcsTbPerfPerfil();
       $aNamespace = new Zend_Session_Namespace('userNs');
       
       $form->removeElement('Alterar');
       $this->view->form = $form;
       
        if ($this->getRequest()->isPost()) {
           $data = $this->getRequest()->getPost();  
           try{
              $data["PERF_CD_MATRICULA_INCLUSAO"] = $aNamespace->matricula;
              $data["PERF_DH_DATA_HORA_INCLUSAO"] = new Zend_Db_Expr("SYSDATE");
              $data['PERF_DS_PERFIL'] = new Zend_Db_Expr("UPPER('$data[PERF_DS_PERFIL]')");
              $row = $table->createRow($data);
              $row->save();
              $msg_to_user = "Perfil Criado com Sucesso";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível criar o perfil";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $this->_helper->_redirector('index','perfil','guardiao');
        }
   }
   
   public function editAction()
   {
       $this->view->title = "Editar Perfil";
       $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
       $form   = new Guardiao_Form_Perfil();
       $table  = new Application_Model_DbTable_OcsTbPerfPerfil();
       $aNamespace = new Zend_Session_Namespace('userNs');
       
       $form->removeElement('Criar');
       $row = $table->fetchRow(array('PERF_ID_PERFIL = ?' => $id));
       if ($row) {
           $data = $row->toArray();
           $form->populate($data);
       }
       $this->view->form = $form;  
       
        if ($this->getRequest()->isPost()) {
           $data = $this->getRequest()->getPost();  
           try{
              $data["PERF_CD_MATRICULA_INCLUSAO"] = $aNamespace->matricula;
              $data["PERF_DH_DATA_HORA_INCLUSAO"] = new Zend_Db_Expr("SYSDATE");
              $row = $table->find($id)->current();
              $row->setFromArray($data);
              $row->save();
              $msg_to_user = "Perfil alterado com Sucesso";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível excluir o alterar selecionado";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $this->_helper->_redirector('index','perfil','guardiao');
        }
   }
   
}
