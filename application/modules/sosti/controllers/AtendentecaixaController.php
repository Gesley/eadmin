<?php

class Sosti_AtendentecaixaController extends Zend_Controller_Action {
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

        $this->view->titleBrowser = 'e-Sosti';
    }
    
    public function listAction()
    {
       $CaixaNs = new Zend_Session_Namespace('CateNs');
       $cxgsGrupoServiço = new Application_Model_DbTable_SadTbCxgsGrupoServico();
       
       if($CaixaNs->tipo == 1){
           $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($CaixaNs->identificador);
           $this->view->title = "Atendentes - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
           $idCaixa = $CaixaNs->identificador;
       }else if($CaixaNs->tipo == 3){
           $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoNivel($CaixaNs->identificador);
           $this->view->title = "Atendentes - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'].' / '.$nomeCaixa[0]['SGRS_SG_SECAO_LOTACAO'];
           $idCaixa = $nomeCaixa[0]['CXEN_ID_CAIXA_ENTRADA'];
       }
       
       /**
        * Tratamento quando é caixa de atendimento da seção
        */
       $cx = $this->_getParam('cx', 0);
       if ($cx != 0) {
           $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($cx);
           $this->view->title = "Atendentes - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'].' / '.$nomeCaixa[0]['SGRS_SG_SECAO_LOTACAO'];
           $idCaixa = $nomeCaixa[0]['CXEN_ID_CAIXA_ENTRADA'];
       }
       $this->view->cx = $cx;
       /*paginação*/
       $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
       /*Ordenação das paginas*/
       $order_column = $this->_getParam('ordem','NOME_ATENDENTE');
       $order_direction = $this->_getParam('direcao', 'ASC');
       $order = $order_column.' '.$order_direction;
       ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

       $dados = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
       $rows = $dados->getAtendentesCaixa($idCaixa, null,$order);
              
       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(50);

       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    public function addAction()
    {
        //$this->view->title = 'Cadastrar novo Atendente';
        $userNs = new Zend_Session_Namespace('userNs');
        $CaixaNs = new Zend_Session_Namespace('CateNs');
        $cxgsGrupoServiço = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $form = new Sosti_Form_AtendenteCaixa();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        $SadTbAtcxAuditoria = new Application_Model_DbTable_SadTbAtcxAuditoria();
        
        if($CaixaNs->tipo == 1){
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($CaixaNs->identificador);
            $this->view->title = "Novo Atendente - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
            $idCaixa = $CaixaNs->identificador;
        }else if($CaixaNs->tipo == 3){
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoNivel($CaixaNs->identificador);
            $this->view->title = "Novo Atendente - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'].' / '.$nomeCaixa[0]['SGRS_SG_SECAO_LOTACAO'];
            $idCaixa = $nomeCaixa[0]['CXEN_ID_CAIXA_ENTRADA'];
        }
        /**
         * Tratamento quando é caixa de atendimento da seção
         */
        $cx = $this->_getParam('cx', 0);
        if ($cx != 0) {
           $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($cx);
           $this->view->title = "Atendentes - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'].' / '.$nomeCaixa[0]['SGRS_SG_SECAO_LOTACAO'];
           $idCaixa = $nomeCaixa[0]['CXEN_ID_CAIXA_ENTRADA'];
        }
        
        $this->view->idCaixa = $idCaixa;
        
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {                
                $data['ATCX_ID_CAIXA_ENTRADA'] = $idCaixa;
                $dados = explode(" - ",$data['ATENDENTE']);
                $data['ATCX_CD_MATRICULA'] = $dados[0];
                $data['ATCX_NM_SISTEMA'] =   $nomeCaixa[0]['TPCX_DS_PROPRIETARIO_CAIXA'];

                $message = $nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
                
                $form->removeElement('ATENDENTE');
                $row = $table->createRow($data);
                
                $dataAtcxAuditoria['ATCX_TS_OPERACAO']           = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");              
                $dataAtcxAuditoria['ATCX_IC_OPERACAO']           = 'I';              
                $dataAtcxAuditoria['ATCX_CD_MATRICULA_OPERACAO'] = $userNs->matricula;   
                $dataAtcxAuditoria['ATCX_CD_MAQUINA_OPERACAO']   = substr($_SERVER['REMOTE_ADDR'],0,50);   
                $dataAtcxAuditoria['ATCX_CD_USUARIO_SO']         = substr($_SERVER['HTTP_USER_AGENT'],0,50);       
                $dataAtcxAuditoria['OLD_ATCX_ID_CAIXA_ENTRADA']  = new Zend_Db_Expr("NULL");
                $dataAtcxAuditoria['NEW_ATCX_ID_CAIXA_ENTRADA']  = $CaixaNs->identificador;
                $dataAtcxAuditoria['OLD_ATCX_CD_MATRICULA'] = new Zend_Db_Expr("NULL");  
                $dataAtcxAuditoria['NEW_ATCX_CD_MATRICULA'] = $data['ATCX_CD_MATRICULA']; 
                $dataAtcxAuditoria['OLD_ATCX_NM_SISTEMA'] = new Zend_Db_Expr("NULL");  
                $dataAtcxAuditoria['NEW_ATCX_NM_SISTEMA'] = $data['ATCX_NM_SISTEMA'];
                $dataAtcxAuditoria['OLD_ATCX_IC_ATIVIDADE'] = new Zend_Db_Expr("NULL");  
                $dataAtcxAuditoria['NEW_ATCX_IC_ATIVIDADE'] = $data['ATCX_IC_ATIVIDADE']; ;
                
                $rowAtcxAuditoria = $SadTbAtcxAuditoria->createRow($dataAtcxAuditoria);
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                
                try {
                    $row->save();
                    $rowAtcxAuditoria->save();
                    $db->commit();
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível adicionar pessoa a essa caixa: $message!". "<br><p>".strip_tags($exc->getMessage())."<p>", 'status' => 'error'));
                    return $this->_helper->_redirector('list','atendentecaixa','sosti');
                }
                $this->_helper->flashMessenger ( array('message' => "Novo atendente cadastrado para a caixa: $message!", 'status' => 'success'));
                return $this->_helper->_redirector('list','atendentecaixa','sosti');
            }
        }
    }
    
    public function editAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $CaixaNs = new Zend_Session_Namespace('CateNs');
        $cxgsGrupoServiço = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $cod_mat = Zend_Filter::FilterStatic($this->_getParam('cod_mat'),'Alnum');
        $form   = new Sosti_Form_AtendenteCaixa();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        $SadTbAtcxAuditoria = new Application_Model_DbTable_SadTbAtcxAuditoria();

        if($CaixaNs->tipo == 1){
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($CaixaNs->identificador);
            $this->view->title = "Alterar Situação Atendente - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
            $idCaixa = $nomeCaixa[0]['CXEN_ID_CAIXA_ENTRADA'];
        }else if($CaixaNs->tipo == 3){
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoNivel($CaixaNs->identificador);
            $this->view->title = "Alterar Situação Atendentes - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'].' / '.$nomeCaixa[0]['SGRS_SG_SECAO_LOTACAO'];
            $idCaixa = $nomeCaixa[0]['CXEN_ID_CAIXA_ENTRADA'];
        }
        
        /**
         * Busca pelo id da linha a ser alterada
         */
        if (($id)&&($cod_mat)) {
            //$row = $table->fetchRow(array('ATCX_ID_CAIXA_ENTRADA = ?' => $id,'ATCX_CD_MATRICULA = ?' => $cod_mat));
            $row = $table->getAtendentesCaixa($id,$cod_mat);
            //Zend_Debug::dump($row);
            if ($row) {
               /* $this->view->atendente = $row[0]['ATENDENTE'];
                $form->getElement('ATCX_CD_MATRICULA')->setOptions(array('display' => 'none'));*/
                $form->populate($row[0]);
            }
        }
        
        
                //Zend_Debug::dump($row[0]);
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */                
        if ($this->getRequest()->isPost()){
            //echo "inicio";
            $data = $this->getRequest()->getPost();
            
            if ($form->isValid($data)) {
                $row = $table->find($id,$cod_mat)->current();
                $data['ATCX_NM_SISTEMA'] =   $nomeCaixa[0]['TPCX_DS_PROPRIETARIO_CAIXA'];
                //$row = $table->find($data[array('ATCX_ID_CAIXA_ENTRADA', 'ATCX_CD_MATRICULA')])->current();
                $message = $SadTbAtcxAtendenteCaixa['DSC_CAIXA_ENTRADA'];
                $old_data = $row->toArray();
                
                $aux = $SadTbAtcxAtendenteCaixa['DSC_CAIXA_ENTRADA'];
                $SadTbAtcxAtendenteCaixa['DSC_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('".$SadTbAtcxAtendenteCaixa['DSC_CAIXA_ENTRADA']."')");
                
                $form->removeElement('ATENDENTE');                
                $row->setFromArray($data);
                
                $dataAtcxAuditoria['ATCX_TS_OPERACAO']           = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");              
                $dataAtcxAuditoria['ATCX_IC_OPERACAO']           = 'A';              
                $dataAtcxAuditoria['ATCX_CD_MATRICULA_OPERACAO'] = $userNs->matricula;   
                $dataAtcxAuditoria['ATCX_CD_MAQUINA_OPERACAO']   = substr($_SERVER['REMOTE_ADDR'],0,50);   
                $dataAtcxAuditoria['ATCX_CD_USUARIO_SO']         = substr($_SERVER['HTTP_USER_AGENT'],0,50);   
                $dataAtcxAuditoria['OLD_ATCX_ID_CAIXA_ENTRADA']  = $old_data['ATCX_ID_CAIXA_ENTRADA'];
                $dataAtcxAuditoria['NEW_ATCX_ID_CAIXA_ENTRADA']  = $data['ATCX_ID_CAIXA_ENTRADA'];         
                $dataAtcxAuditoria['OLD_ATCX_CD_MATRICULA']  = $old_data['ATCX_CD_MATRICULA'];
                $dataAtcxAuditoria['NEW_ATCX_CD_MATRICULA']  = $data['ATCX_CD_MATRICULA'];         
                $dataAtcxAuditoria['OLD_ATCX_NM_SISTEMA']  = $old_data['ATCX_NM_SISTEMA'];
                $dataAtcxAuditoria['NEW_ATCX_NM_SISTEMA']  = $data['ATCX_NM_SISTEMA'];                  
                $dataAtcxAuditoria['OLD_ATCX_IC_ATIVIDADE']  = $old_data['ATCX_IC_ATIVIDADE'];
                $dataAtcxAuditoria['NEW_ATCX_IC_ATIVIDADE']  = $data['ATCX_IC_ATIVIDADE'];  
                
                $rowAtcxAuditoria = $SadTbAtcxAuditoria->createRow($dataAtcxAuditoria);
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {
                    $row->save();
                    $rowAtcxAuditoria->save();
                    $db->commit();
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível adicionar o Status da permissão: $message!". "<br><p>".strip_tags($exc->getMessage())."<p>", 'status' => 'error'));
                    return $this->_helper->_redirector('list','atendentecaixa','sosti');
                }
                $this->_helper->flashMessenger ( array('message' => "O Status da Permissão foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('list','atendentecaixa','sosti');
            }
        }
    }
    
    public function ajaxnomeatendenteAction() {
        $matriculanome = $this->_getParam('term', '');
        $idCaixa = $this->_getParam('idcaixa', '');
        //var_dump($matriculanome." id ".$idCaixa); exit;
        
        $SadTbAtcxAtendenteCaixa = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        $nome_array = $SadTbAtcxAtendenteCaixa->getNomeAtendenteAjax($matriculanome, $idCaixa);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }
}
