<?php

class Guardiao_ResponsavelController extends Zend_Controller_Action
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
        $order = $this->_getParam('ordem', 'PEPE_ID_PERFIL_PESSOA');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        $table = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        $select = $table->select()->order($order_aux);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Responsável Permissão";

        //$this->_helper->layout->disableLayout();
    }
    
        public function formAction()
    {
//        Zend_Debug::dump(ini_get('post_max_size'), 'post_max_size');
//        Zend_Debug::dump(ini_get('upload_max_filesize'), 'upload_max_filesize');
//        
//        Zend_Debug::dump(ini_set('post_max_size',52428800));
//        Zend_Debug::dump(ini_set('upload_max_filesize',52428800));
//        
//        Zend_Debug::dump(ini_get('post_max_size'), 'post_max_size');
//        Zend_Debug::dump(ini_get('upload_max_filesize'), 'upload_max_filesize');
//        
//        
//        Zend_Debug::dump(ini_get('session.gc_maxlifetime'));
//        Zend_Debug::dump(ini_set('session.gc_maxlifetime', 120));
//        Zend_Debug::dump(ini_get('session.gc_maxlifetime'));
//        
//        $dataFinal  = date('Y');
//        $obDataFinal = DateTime::createFromFormat('Y',$dataFinal);
//        
//        Zend_Debug::dump($obDataFinal,'$obDataFinal');
            
//        function get_real_mac_addr() {
//            exec("ipconfig /all", $arr, $retval);
//            Zend_Debug::dump($arr);
//            $arr[14];
//            $ph = explode(":", $arr[14]);
//            return trim($ph[1]);
//        }
//        echo get_real_mac_addr();
//        
//        echo 'teste2';
                
                
//        set_time_limit( 5 );
//        sleep(180);
//        echo 'teste time limit';
//        echo 'sleep 180';
        
//        ini_set("memory_limit","512M");
//        echo ini_get("memory_limit");
//        echo "<br> teste memory_limit";
            
            echo 'teste deploy';
                
        $this->view->title = "Responsável Permissão";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Guardiao_Form_Responsavel();
        $table  = new Application_Model_DbTable_OcsTbRepeRespPermissao();
        $table_audit = new Application_Model_DbTable_OcsTbRepeAuditoria();
        $unidadepessoaalterar = new Zend_Session_Namespace('unidadepessoaalterarNs');
        $aNamespace = new Zend_Session_Namespace('userNs');
        
        if ($this->getRequest()->isPost() || $unidadepessoaalterar->dataform['lota_cod_lotacao']){
            $data = $this->getRequest()->getPost();
            
             if($this->getRequest()->isPost()==FALSE && $unidadepessoaalterar->dataform){
                $data['LOTA_COD_LOTACAO'] = $unidadepessoaalterar->dataform['lota_cod_lotacao'];
                $data['APSP_ID_PESSOA'] = $unidadepessoaalterar->dataform['apsp_id_pessoa'];
                $data['APAS_SG_SISTEMA'] = $unidadepessoaalterar->dataform['apas_sg_sistema'];
                $form->populate($data);
             }
            
            /*Select Pessoas*/
            $modelPerfilPessoa = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
            $unidade_array = explode(' - ', $data['LOTA_COD_LOTACAO']);
            $unidade = $unidade_array[2];
            $sg_secao = $unidade_array[3];
            $pessoas = $modelPerfilPessoa->getPessoa($unidade);
            
                        
            $apsp_id_pessoa = $form->APSP_ID_PESSOA;
             foreach ($pessoas as $pessoas_p):
                $apsp_id_pessoa->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
             endforeach;;
             
             
             $pspa_id_perfil = $form->PSPA_ID_PERFIL;
             foreach ($perfis as $perfis_p):
                $apsp_id_pessoa->addMultiOptions(array($perfis_p["UNPE_ID_PERFIL"] => $perfis_p["PERF_DS_PERFIL"]));
             endforeach;;
             /*Fim Select Pessoas*/
            
            
            if ($form->isValid($data)) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try{
                     $data['REPE_SG_SECAO'] = $sg_secao;
                     $data['REPE_CD_LOTACAO'] = $unidade;
                     $data['REPE_CD_MATRICULA'] = $data['APSP_ID_PESSOA'];
                     unset ($data['LOTA_COD_LOTACAO']);
                     unset ($data['APSP_ID_PESSOA']);
                     $row = $table->createRow($data);
                     $data_audit["REPE_TS_OPERACAO"] = new Zend_Db_Expr("SYSDATE");
                     $data_audit["REPE_CD_OPERACAO"] = 'I';
                     $data_audit["REPE_CD_MATRICULA_OPERACAO"] = $aNamespace->matricula;
                     $data_audit["REPE_CD_MAQUINA_OPERACAO"] = 'NOME_MAQUINA';
                     $data_audit["REPE_CD_USUARIO_SO"] = 'NOME_USER_SO';
                     $data_audit['OLD_REPE_ID_RESP_PERMISSAO'] = 0;
                     $data_audit['NEW_REPE_ID_RESP_PERMISSAO'] = $row->save();
                     $data_audit['OLD_REPE_SG_SECAO'] = 0;
                     $data_audit['NEW_REPE_SG_SECAO'] = $data['REPE_SG_SECAO'];
                     $data_audit['OLD_REPE_CD_LOTACAO'] = 0;
                     $data_audit['NEW_REPE_CD_LOTACAO'] = $data['REPE_CD_LOTACAO'];
                     $data_audit['OLD_REPE_CD_MATRICULA'] = 0;
                     $data_audit['NEW_REPE_CD_MATRICULA'] = $data['REPE_CD_MATRICULA'];
                     $row = $table_audit->createRow($data_audit);
                     $row->save();
                     $msg_to_user = "Perfil associado com sucesso à pessoa";
                     $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                     $db->commit();
                  }  catch (Zend_Exception $error_string){
                      $db->rollback();
                      $msg_to_user = "Não é possível associar o perfil à pessoa";
                      $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                  }
                  $papelalterar = new Zend_Session_Namespace('unidadeperfilNs');
                  $this->_helper->_redirector('form','responsavel','guardiao');
//                 $this->view->pmat_id_pessoa = $data['APSP_ID_PESSOA'];
//                 $this->view->lota_cod_lotacao = $data['LOTA_COD_LOTACAO'];
//                 $this->view->apsp_id_pessoa = $data['APSP_ID_PESSOA'];
//                 $this->view->apas_sg_sistema = $data['APAS_SG_SISTEMA'];
            
//                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
//                
//                /*Ordenação*/
//                $order = $this->_getParam('ordem', 'SIPA_ID_SISTEMA_PAPEL');
//                $direction = $this->_getParam('direcao', 'ASC');
//                $order_aux = $order.' '.$direction;
//                ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
//                /*Ordenação*/
//               
//                
//                /* Filtragem pelo nome do sistema */
//                
//                
//               
//                $paginator = Zend_Paginator::factory($select);
//                $paginator->setCurrentPageNumber($page)
//                          ->setItemCountPerPage(15);
//                $this->view->ordem = $order;
//                $this->view->direcao = $direction;
//                $this->view->data = $paginator;
//                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
                //$this->render('form');
            } 
        }
        $this->view->form = $form;
    }
    
    public function ajaxunidadeAction()
    {
        $unidade     = $this->_getParam('term','');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade);
        
        $fim =  count($lotacao);
        for ($i = 0; $i<$fim; $i++ ) {
            $lotacao[$i] = array_change_key_case ($lotacao[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($lotacao);
        
    }
    
    public function ajaxpessoaAction()
    {
        $lota_cod_lotacao = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $OcsTbPepePerfilPessoa = new Application_Model_DbTable_OCsTbPepePerfilPessoa();        
        $PapSistUnidPess_array = $OcsTbPepePerfilPessoa->getPessoa($lota_cod_lotacao );
        $this->view->PapSistUnidPess_array = $PapSistUnidPess_array;
        
    }
    
    public function alterarAction()
    {
        $this->view->title = "Responsável Permissão";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $data = $this->getRequest()->getPost();
        $form   = new Guardiao_Form_Responsavel();
        $table  = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        $unidadepessoaalterar = new Zend_Session_Namespace('unidadepessoaalterarNs');
        $flag = FALSE; 
        
       foreach ($data['papeis'] as $papeis):

            $papelArray1 = explode(" - ", $papeis[1]);
            $apsp_id_papel_sist_unid = $papelArray1[0];
            $codigo1 = $papelArray1[1];

            $papelArray2 = explode(" - ", $papeis[2]);
            $apsp_id_papel_sist_unid = $papelArray2[0];
            $codigo2 = $papelArray2[1];
            
            //alterações de DELETE
            if ($codigo1 == ""  && $codigo2 == "associado") {
                $flag = TRUE; 
                try{
                    //$select = $table->getDeletar($apsp_id_papel_sist_unid,$data['pmat_id_pessoa']);
                    $msg_to_user = "Papel Alterado com Sucesso";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                }catch (Zend_Exception $error_string){
                    $msg_to_user = "Erro ao retirar papel";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                }

            //alterações de INSERT
            } else if ($codigo1 == "associar" && $codigo2 == "dissociado") {
                $flag = TRUE; 
                try{
                    //$select = $table->getInserir($apsp_id_papel_sist_unid,$data['pmat_id_pessoa']);
                    $msg_to_user = "Papel Alterado com Sucesso";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                }catch (Zend_Exception $error_string){
                    //$msg_to_user = "Erro ao retirar papel";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                }
           }
        endforeach;
        if ($flag != TRUE){
            $msg_to_user = "Nenhum papel foi modificado";
            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'info'));
        }        
        $unidadepessoaalterar->dataform = $data;
        $this->_helper->_redirector('form','perfilpessoa','guardiao');
    }
}
