<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sisad_TipoprocessoController extends Zend_Controller_Action {
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
		$this->view->titleBrowser = 'e-Sisad';
    }

//    public function indexAction()
//    {
//
//    }
    
    public function listAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'TPPR_ID_TIPO_PROCESSO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SadTbTpprTipoProcesso();
       $rows = $dados->getTipoProcessoPesq($order);
       
       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(10);

       $this->view->title = "Tipos de Processo";
       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar novo Tipo de Processo';
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sisad_Form_Tipoprocesso();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbTpprTipoProcesso();
        $SadTbTpprAuditoria = new Application_Model_DbTable_SadTbTpprAuditoria();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['TPPR_ID_TIPO_PROCESSO']);
                
                $message = $data['TPPR_DS_DESCRICAO_PROCESSO'];
                
                $data['TPPR_ID_TIPO_PROCESSO'] = count($table->fetchAll())+1;
                $aux = $data['TPPR_DS_DESCRICAO_PROCESSO'];
                $data['TPPR_DS_DESCRICAO_PROCESSO'] = new Zend_Db_Expr("UPPER('".$data['TPPR_DS_DESCRICAO_PROCESSO']."')");
                $row = $table->createRow($data);
                
                $dataTpprAuditoria['TPPR_TS_OPERACAO']           = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");              
                $dataTpprAuditoria['TPPR_IC_OPERACAO']           = 'I';              
                $dataTpprAuditoria['TPPR_CD_MATRICULA_OPERACAO'] = $userNs->matricula;   
                $dataTpprAuditoria['TPPR_CD_MAQUINA_OPERACAO']   = substr($_SERVER['REMOTE_ADDR'],0,50);   
                $dataTpprAuditoria['TPPR_CD_USUARIO_SO']         = substr($_SERVER['HTTP_USER_AGENT'],0,50);       
                $dataTpprAuditoria['OLD_TPPR_ID_TIPO_PROCESSO']  = new Zend_Db_Expr("NULL");
                $dataTpprAuditoria['NEW_TPPR_ID_TIPO_PROCESSO']  = $data['TPPR_ID_TIPO_PROCESSO'];  
                $dataTpprAuditoria['OLD_TPPR_DS_DESCRICAO_PROC'] = new Zend_Db_Expr("NULL");  
                $dataTpprAuditoria['NEW_TPPR_DS_DESCRICAO_PROC'] = $aux;
                $dataTpprAuditoria['OLD_TPRR_IC_ATIVO'] = new Zend_Db_Expr("NULL");  
                $dataTpprAuditoria['NEW_TPRR_IC_ATIVO'] = $data['TPRR_IC_ATIVO'];
                
                $rowTpprAuditoria = $SadTbTpprAuditoria->createRow($dataTpprAuditoria);
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {
                    $row->save();
                    $rowTpprAuditoria->save();
                    $db->commit();
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível adicionar o Tipo de Processo: $message!". "<br><p>".strip_tags($exc->getMessage())."<p>", 'status' => 'error'));
                    return $this->_helper->_redirector('list','tipoprocesso','sisad');
                }
                $this->_helper->flashMessenger ( array('message' => "O Tipo de Processo: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('list','tipoprocesso','sisad');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Tipo de Processo';
        $userNs = new Zend_Session_Namespace('userNs');
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sisad_Form_Tipoprocesso();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbTpprTipoProcesso();
        $SadTbTpprAuditoria = new Application_Model_DbTable_SadTbTpprAuditoria();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('TPPR_ID_TIPO_PROCESSO = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $row = $table->find($data['TPPR_ID_TIPO_PROCESSO'])->current();
                $message = $data['TPPR_DS_DESCRICAO_PROCESSO'];
                $old_data = $row->toArray();
                
                $aux = $data['TPPR_DS_DESCRICAO_PROCESSO'];
                $data['TPPR_DS_DESCRICAO_PROCESSO'] = new Zend_Db_Expr("UPPER('".$data['TPPR_DS_DESCRICAO_PROCESSO']."')");
                
                $row->setFromArray($data);
                
                $dataTpprAuditoria['TPPR_TS_OPERACAO']           = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");              
                $dataTpprAuditoria['TPPR_IC_OPERACAO']           = 'A';              
                $dataTpprAuditoria['TPPR_CD_MATRICULA_OPERACAO'] = $userNs->matricula;   
                $dataTpprAuditoria['TPPR_CD_MAQUINA_OPERACAO']   = substr($_SERVER['REMOTE_ADDR'],0,50);   
                $dataTpprAuditoria['TPPR_CD_USUARIO_SO']         = substr($_SERVER['HTTP_USER_AGENT'],0,50);       
                $dataTpprAuditoria['OLD_TPPR_ID_TIPO_PROCESSO']  = $old_data['TPPR_ID_TIPO_PROCESSO'];
                $dataTpprAuditoria['NEW_TPPR_ID_TIPO_PROCESSO']  = $data['TPPR_ID_TIPO_PROCESSO'];  
                $dataTpprAuditoria['OLD_TPPR_DS_DESCRICAO_PROC'] = $old_data['TPPR_DS_DESCRICAO_PROCESSO'];
                $dataTpprAuditoria['NEW_TPPR_DS_DESCRICAO_PROC'] = $aux;
                $dataTpprAuditoria['OLD_TPRR_IC_ATIVO'] = $old_data['TPRR_IC_ATIVO'];
                $dataTpprAuditoria['NEW_TPRR_IC_ATIVO'] = $data['TPRR_IC_ATIVO'];
                
                $rowTpprAuditoria = $SadTbTpprAuditoria->createRow($dataTpprAuditoria);
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {
                    $row->save();
                    $rowTpprAuditoria->save();
                    $db->commit();
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível adicionar o Tipo de Processo: $message!". "<br><p>".strip_tags($exc->getMessage())."<p>", 'status' => 'error'));
                    return $this->_helper->_redirector('list','tipoprocesso','sisad');
                }
                $this->_helper->flashMessenger ( array('message' => "O Tipo de Processo: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('list','tipoprocesso','sisad');
            }
        }
    }
    
//    public function delAction()
//    {
//        $this->view->title = 'Excluir Tipo de Caixa';
//        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
//        $form   = new Sisad_Form_Tipoprocesso();
//        $this->view->form = $form;
//        $table  = new Application_Model_DbTable_SadTbTpcxtipoprocesso();
//        /**
//         * Busca pelo id da linha a ser alterada
//         */
//        if ($id) {
//            $row = $table->fetchRow(array('TPPR_ID_TIPO_PROCESSO = ?' => $id));
//            if ($row) {
//                $data = $row->toArray();
//                $form->populate($data);
//                
//                /*adiciona o elemento submit excluir*/
//                $form->removeElement('Salvar');
//                $excluir = new Zend_Form_Element_Submit('Excluir');
//                $form->addElement($excluir);
//            }
//        }
//        /**
//         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
//         */
//        if ($this->getRequest()->isPost()) {
//            $data = $this->getRequest()->getPost();
//            if ($form->isValid($data)) {
//                $row = $table->find($data['TPPR_ID_TIPO_PROCESSO'])->current();
//                try {
//                    $row->delete();
//                } catch (Exception $exc) {
//                    //echo $exc->getMessage();
//                    $this->_helper->flashMessenger ( array('message' => "Não foi possível excluír o Tipo de Caixa:  $data[TPPR_DS_DESCRICAO_PROCESSO]!", 'status' => 'error'));
//                    return $this->_helper->_redirector('list','tipoprocesso','sisad');
//                }
//                $this->_helper->flashMessenger ( array('message' => "O Tipo de Caixa: $message foi atualizado!", 'status' => 'success'));
//                return $this->_helper->_redirector('list','tipoprocesso','sisad');
//            }
//        }
//    }

}
