<?php

class Guardiao_PapelController extends Zend_Controller_Action
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
        $form   = new Guardiao_Form_Papel();
        $this->view->title = "Papel";
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'PAPL_ID_PAPEL');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/
        
        $form->removeElement('Alterar');
        $form->removeElement('Criar');
        $form->removeElement('PAPL_ID_ACAO_SISTEMA');
        $form->removeElement('PAPL_NM_PAPEL');
        $form->removeElement('PAPL_DS_FINALIDADE');
        $form->removeElement('ACAO_ID_CONTROLE_SISTEMA');
        $this->view->form = $form;
        
        $table = new Application_Model_DbTable_OcsTbPaplPapel();
        $select = $table->getPapeisCriados();
       
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
        $this->view->title = "Deletar Papel";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Guardiao_Form_Papel();
        $table  = new Application_Model_DbTable_OcsTbPaplPapel();
        $data = $this->getRequest()->getPost();
        $aNamespace = new Zend_Session_Namespace('userNs');
        
        try{
              $data["PAPL_CD_MATRICULA_EXCLUSAO"] = $aNamespace->matricula;
              $data["PAPL_DT_EXCLUSAO"] = new Zend_Db_Expr("SYSDATE");
              $row = $table->find($id)->current();
              $row = $row->setFromArray($data);
              $row->save();
              $msg_to_user = "Papel excluído com Sucesso";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));

          }  catch (Zend_Exception $error_string){
              $msg_to_user = "Não é possível excluir o papel";
              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
          }
          $papelalterar = new Zend_Session_Namespace('papelalterarNs');
          $this->_helper->_redirector('index','papel','guardiao');
    }
    
   public function addAction()
   {
        $this->view->title = "Criar novo Papel";
        $form   = new Guardiao_Form_Papel();
        $table  = new Application_Model_DbTable_OcsTbPaplPapel();
        $aNamespace = new Zend_Session_Namespace('userNs');
        
        $form->removeElement('Alterar');
        $this->view->form = $form;
                
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $consulta = $table->getVerificaId($data['PAPL_ID_ACAO_SISTEMA']);
            
            if($consulta['ACAO']==1){
                
                $msg_to_user = "Ação já associada a um papel";
                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));;
                
            }else{
                try{
                  unset ($data['CTRL_ID_MODULO']);
                  unset ($data['ACAO_ID_CONTROLE_SISTEMA']);
                  $data['PAPL_NM_PAPEL'] = new Zend_Db_Expr("UPPER('$data[PAPL_NM_PAPEL]')");
                  $data['PAPL_DS_FINALIDADE'] = new Zend_Db_Expr("UPPER('$data[PAPL_DS_FINALIDADE]')");
                  $data["PAPL_CD_MATRICULA_INCLUSAO"] = $aNamespace->matricula;
                  $data["PAPL_DT_INCLUSAO"] = new Zend_Db_Expr("SYSDATE");
                  if ($data["PAPL_ID_ACAO_SISTEMA"]){
                      unset($data["PAPL_SG_SISTEMA"]);
                  }
                  $row = $table->createRow($data);
                  $row->save();
                  $msg_to_user = "Papel criado com Sucesso";
                  $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));;
              }  catch (Zend_Exception $error_string){
                  $msg_to_user = "Erro ao criar papel";
                  $this->_helper->flashMessenger ( array('error' => $msg_to_user, 'status' => 'success'));;
              }
            }
           $papelalterar = new Zend_Session_Namespace('papelalterarNs');
           $this->_helper->_redirector('add','papel','guardiao');
           }
    }
   
    public function editAction()
    {
        $this->view->title = "Editar Papel";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Guardiao_Form_Papel();
        $table  = new Application_Model_DbTable_OcsTbPaplPapel();
        $aNamespace = new Zend_Session_Namespace('userNs');
        
        $form->removeElement('Criar');
        $row = $table->getPapelById($id);
        if ($row) {
            $form->populate($row);
        }
        $this->view->form = $form;  
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();  
            try{
                unset ($data['CTRL_ID_MODULO']);
                unset ($data['ACAO_ID_CONTROLE_SISTEMA']);
                $data['PAPL_NM_PAPEL'] = new Zend_Db_Expr("UPPER('$data[PAPL_NM_PAPEL]')");
                $data['PAPL_DS_FINALIDADE'] = new Zend_Db_Expr("UPPER('$data[PAPL_DS_FINALIDADE]')");
                $data["PAPL_CD_MATRICULA_INCLUSAO"] = $aNamespace->matricula;
                $data["PAPL_DT_INCLUSAO"] = new Zend_Db_Expr("SYSDATE");
                if ($data["PAPL_ID_ACAO_SISTEMA"]){
                    unset($data["PAPL_SG_SISTEMA"]);
                }
                $row = $table->find($id)->current();
                $row = $row->setFromArray($data);
                $row->save();
                $msg_to_user = "Papel editado com Sucesso";
                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
            }  catch (Zend_Exception $error_string){
                $msg_to_user = "Não é possível editar o papel";
                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
            }
            $papelalterar = new Zend_Session_Namespace('papelalterarNs');
            $this->_helper->_redirector('index','papel','guardiao');
        }       
    }
   
    public function ajaxmoduloAction()
    {
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'alnum');
        $OcsTbModlModulo = new Application_Model_DbTable_OcsTbModlModulo();
        $OcsTbModlModulo_array = $OcsTbModlModulo->fetchAll("MODL_NM_SISTEMA = '$id' ","MODL_NM_SISTEMA")->toArray();
        $this->view->modulos = $OcsTbModlModulo_array;
    }
    public function ajaxcontroleAction()
    {
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $OcsTbCtrlControleSistema = new Application_Model_DbTable_OcsTbCtrlControleSistema();
        $OcsTbCtrlControleSistema_array = $OcsTbCtrlControleSistema->fetchAll("CTRL_ID_MODULO = $id ","CTRL_NM_CONTROLE_SISTEMA")->toArray();
        $this->view->controles = $OcsTbCtrlControleSistema_array;
    }
    
    public function ajaxacaoAction()
    {
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $OcsTbAcaoAcaoSistema= new Application_Model_DbTable_OcsTbAcaoAcaoSistema();
        $OcsTbAcaoAcaoSistema_array = $OcsTbAcaoAcaoSistema->getAcoesSemPapel($id);
        $this->view->acoes = $OcsTbAcaoAcaoSistema_array;
    }
}
