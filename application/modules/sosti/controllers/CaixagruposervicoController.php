<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sosti_CaixagruposervicoController extends Zend_Controller_Action {
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
        /* Initialize action controller here */
    }

    public function indexAction()
    {

    }
    
    public function listAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'CXEN_ID_CAIXA_ENTRADA');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/


       $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
       $form = new Sosti_Form_Caixagruposervico();
       
       $form->removeElement('CXEN_DS_CAIXA_ENTRADA');
       //$form->removeElement('CXEN_ID_TIPO_CAIXA');
       $form->removeElement('CXGS_ID_GRUPO');
       
       $form->removeElement('Salvar');
       $listar = new Zend_Form_Element_Submit('Listar');
       $form->addElement($listar);
       
       $trf1_secao = $form->getElement('TRF1_SECAO');
       $trf1_secao->setRequired(true);
       $trf1_secao = $form->getElement('SECAO_SUBSECAO');
       $trf1_secao->setRequired(true);
       
       if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //Zend_Debug::dump($data);
            /**
             * Preservando os dados decodificados em json do post
             */
            $data_json = $data;
            
            /**
             * Decodificando o post da subcessão
             */
            $arr_secao_subsecao = Zend_Json::decode($data["SECAO_SUBSECAO"]);
            //Zend_Debug::dump($arr_secao_subsecao);
            /**
             * Adicionando no form para passar pelo isValid() do form
             */
            $secao_subsecao = $form->getElement('SECAO_SUBSECAO');
            $secao_subsecao->addMultiOptions(array($data_json["SECAO_SUBSECAO"] => $arr_secao_subsecao["LOTA_SIGLA_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_DSC_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_COD_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_SIGLA_SECAO"]));
            
            
            if ( $form->isValid($data) ) {
                $rows = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorTrfSecaoSubsecao($arr_secao_subsecao["LOTA_SIGLA_SECAO"], $arr_secao_subsecao["LOTA_COD_LOTACAO"], $arr_secao_subsecao["LOTA_TIPO_LOTACAO"], $data["CXEN_ID_TIPO_CAIXA"], $order);
                
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                          ->setItemCountPerPage(count($rows));
                
            }else{
                $form->populate($data);
                $this->view->form = $form;
                return;
            }
       }else{
           $rows = $SadTbCxgsGrupoServico->getCaixasGrupoServico($order);
           $paginator = Zend_Paginator::factory($rows);
           $paginator->setCurrentPageNumber($page)
                     ->setItemCountPerPage(count($rows));
       }
       


       $this->view->title = "Caixas dos Grupos de Serviço";
       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
       
       $this->view->form = $form;
       
    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar nova Caixa de Atendimento do Grupo de Serviço';
        $form = new Sosti_Form_Caixagruposervico();
        $this->view->form = $form;
        
        $SadTbCxenCaixaEntrada  = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $SadTbCxgsGrupoServico  = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        
        $userNamespace = new Zend_Session_Namespace('userNs');
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //Zend_Debug::dump($data); 
             
            /**
             * Preservando os dados decodificados em json do post
             */
            $data_json = $data;
            
            /**
             * Decodificando o post da subcessão
             */
            $arr_secao_subsecao = Zend_Json::decode($data["SECAO_SUBSECAO"]);
            //Zend_Debug::dump($arr_secao_subsecao);
            /**
             * Adicionando no form para passar pelo isValid() do form
             */
            $secao_subsecao = $form->getElement('SECAO_SUBSECAO');
            $secao_subsecao->addMultiOptions(array($data_json["SECAO_SUBSECAO"] => $arr_secao_subsecao["LOTA_SIGLA_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_DSC_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_COD_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_SIGLA_SECAO"]));
            
            /**
             * Decodificando o post do grupo
             */
            $arr_grupo = Zend_Json::decode($data["CXGS_ID_GRUPO"]);
            //Zend_Debug::dump($arr_grupo);
            /**
             * Adicionando no form para passar pelo isValid() do form
             */
            $cxgs_id_grupo = $form->getElement('CXGS_ID_GRUPO');
            $cxgs_id_grupo->addMultiOptions(array($data_json["CXGS_ID_GRUPO"] => $arr_grupo["SGRS_DS_GRUPO"]));
            
            
            if ($form->isValid($data)) {
                    /**
                     * Tratando os dados do post para passar os dados para a rotina de insersão de caixa
                     */
                    $data["CXGS_ID_GRUPO"] = $arr_grupo["SGRS_ID_GRUPO"];
                    
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    try {
                        /**
                         * Validações
                         */
//                        $ValidaReplicacaoCaixa_arr = $SadTbCxgsGrupoServico->getValidaReplicacaoCaixa($data['CXGS_ID_GRUPO'], $data['CXEN_ID_TIPO_CAIXA']);
//                        Zend_Debug::dump($ValidaReplicacaoCaixa_arr);
//                        if( $ValidaReplicacaoCaixa_arr['COUNT'] > 0  ){
//                            Zend_Debug::dump($CxgsGrupoServico_arr);
//                            $this->_helper->flashMessenger ( array('message' => "Este grupo de serviço já está associada a uma caixa deste tipo!", 'status' => 'error'));
//                            return $this->_helper->_redirector('add','caixagruposervico','sosti');
//                            exit('já exite');
//                        }
                        
                        /**
                         * Validação contra replicação de uma mesma caixa em uma SEÇÃO, SUBÇÃO OU TRF1
                         */
                        $ValidaReplicacao_arr = $SadTbCxgsGrupoServico->getValidaReplicacaoCaixaPorTrfSecaoSubsecao($arr_secao_subsecao['LOTA_SIGLA_SECAO'], $arr_secao_subsecao['LOTA_COD_LOTACAO'], $arr_secao_subsecao['LOTA_TIPO_LOTACAO'], $data['CXEN_ID_TIPO_CAIXA'] );
                        if( $ValidaReplicacao_arr  ){
                            //Zend_Debug::dump($ValidaReplicacao_arr);
                            $label_da_secao = $arr_secao_subsecao["LOTA_SIGLA_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_DSC_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_COD_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_SIGLA_SECAO"];
                            $mensagem = "Já exite uma caixa do tipo $ValidaReplicacao_arr[TPCX_DS_CAIXA_ENTRADA] na $label_da_secao seu código é $ValidaReplicacao_arr[CXEN_ID_CAIXA_ENTRADA]!";
                            $this->_helper->flashMessenger ( array('message' => $mensagem, 'status' => 'error'));
                            return $this->_helper->_redirector('add','caixagruposervico','sosti');
                        }
                        
                        /**
                         * DADOS DA TABELA SAD_TB_CXEN_CAIXA_ENTRADA
                         */
                        unset($data['CXEN_ID_CAIXA_ENTRADA']);
                        $data['CXEN_ID_CAIXA_ENTRADA'] = count($SadTbCxenCaixaEntrada->fetchAll())+1;
                        $data['CXEN_DS_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('$data[CXEN_DS_CAIXA_ENTRADA]')");
                        $data['CXEN_DT_INCLUSAO']= new Zend_Db_Expr("SYSDATE"); 
                        $data['CXEN_CD_MATRICULA_INCLUSAO'] = $userNamespace->matricula;
                        unset($data['CXEN_DT_EXCLUSAO']);
                        unset($data['CXEN_CD_MATRICULA_EXCLUSAO']);
                        
                        /**
                         * SALVANDO NA TABELA SAD_TB_CXEN_CAIXA_ENTRADA
                         */
                        $rowCxenCaixaEntrada = $SadTbCxenCaixaEntrada->createRow($data);
                        //Zend_Debug::dump($rowCxenCaixaEntrada->toArray());
                        $idCxenCaixaEntrada = $rowCxenCaixaEntrada->save();

                         /**
                         * DADOS DA TABELA SAD_TB_CXGS_GRUPO_SERVICO
                         */
                        $data['CXGS_ID_CAIXA_ENTRADA'] = $idCxenCaixaEntrada;
                        $data['CXGS_ID_GRUPO'] = $data['CXGS_ID_GRUPO'];
                        
                         /**
                         * SALVANDO NA TABELA SAD_TB_CXGS_GRUPO_SERVICO
                         */
                        $rowCxgsGrupoServico = $SadTbCxgsGrupoServico->createRow($data);
                        //Zend_Debug::dump($rowCxgsGrupoServico->toArray());
                        $rowCxgsGrupoServico->save();            
                        
                        //$db->rollBack();
                        $db->commit();
                        
                    } catch (Exception $exc) {
                        $db->rollBack();
                        $error_message = $exc->getMessage(); 
                        $this->_helper->flashMessenger ( array('message' => "Não foi possível adicionar a Caixa de Entrada: $data[CXEN_DS_CAIXA_ENTRADA]! <br/> $error_message", 'status' => 'error'));
                        return $this->_helper->_redirector('add','caixagruposervico','sosti');
                    }
                    
                $this->_helper->flashMessenger ( array('message' => "A Caixa de Entrada: $data[CXEN_DS_CAIXA_ENTRADA] foi cadastrada!", 'status' => 'success'));
                return $this->_helper->_redirector('add','caixagruposervico','sosti');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Caixa de Entrada';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sisad_Form_caixaentrada();
        $this->view->form = $form;
        $SadTbCxenCaixaEntrada  = new Application_Model_DbSadTbCxenCaixaEntrada_SadTbCxenCaixaEntrada();
        $userNamespace = new Zend_Session_Namespace('userNs');
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $SadTbCxenCaixaEntrada->fetchRow(array('CXEN_ID_CAIXA_ENTRADA = ?' => $id));
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
                $row = $SadTbCxenCaixaEntrada->find($data['CXEN_ID_CAIXA_ENTRADA'])->current();
                $message = $data['CXEN_DS_CAIXA_ENTRADA'];
                
                $data['CXEN_DT_INCLUSAO']= new Zend_Db_Expr("SYSDATE"); 
                $data['CXEN_CD_MATRICULA_INCLUSAO'] = $userNamespace->matricula;
                
                $data['CXEN_DS_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('$data[CXEN_DS_CAIXA_ENTRADA]')");
                $row->setFromArray($data);
                
                try {
                    $row->save();
                } catch (Exception $exc) {
                    echo $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível alterar a Caixa de Entrada: $message!", 'status' => 'error'));
                    return $this->_helper->_redirector('list','caixaentrada','sisad');
                }
                $this->_helper->flashMessenger ( array('message' => "A Caixa de Entrada: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('list','caixaentrada','sisad');
            }
        }
    }
    
    public function delAction()
    {
        $this->view->title = 'Excluir Caixa de Entrada';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sisad_Form_caixaentrada();
        $this->view->form = $form;
        $SadTbCxenCaixaEntrada  = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $userNamespace = new Zend_Session_Namespace('userNs');
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $SadTbCxenCaixaEntrada->fetchRow(array('CXEN_ID_CAIXA_ENTRADA = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
                
                /*adiciona o elemento submit excluir*/
                $form->removeElement('Salvar');
                $excluir = new Zend_Form_Element_Submit('Excluir');
                $form->addElement($excluir);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $row = $SadTbCxenCaixaEntrada->find($data['CXEN_ID_CAIXA_ENTRADA'])->current();
                $message = $data['CXEN_DS_CAIXA_ENTRADA'];
                
                $data['CXEN_DT_EXCLUSAO']= new Zend_Db_Expr("SYSDATE"); 
                $data['CXEN_CD_MATRICULA_EXCLUSAO'] = $userNamespace->matricula;
                
                unset($data['CXEN_DT_INCLUSAO']);
                unset($data['CXEN_CD_MATRICULA_INCLUSAO']);
                
                $data['CXEN_DS_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('$data[CXEN_DS_CAIXA_ENTRADA]')");
                $row->setFromArray($data);
                try {
                    $row->save();
                } catch (Exception $exc) {
                    //echo $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível excluír a Caixa de Entrada:  $data[CXEN_DS_CAIXA_ENTRADA]!", 'status' => 'error'));
                    return $this->_helper->_redirector('list','caixaentrada','sisad');
                }
                $this->_helper->flashMessenger ( array('message' => "A Caixa de Entrada: $message foi excluída!", 'status' => 'success'));
                return $this->_helper->_redirector('list','caixaentrada','sisad');
            }
        }
    }
    
     public function addnovogrupoAction()
    {
        $this->view->title = 'Cadastrar nova Caixa de Atendimento do Grupo de Serviço';
        $form = new Sosti_Form_Caixagruposervico();
        
        $SadTbCxenCaixaEntrada  = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $SadTbCxgsGrupoServico  = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        
        $userNamespace = new Zend_Session_Namespace('userNs');
        /**
         * Busca pelo id da linha a ser alterada
         */
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        if ($id) {
            $rowCxenCaixaEntradaSelect = $SadTbCxenCaixaEntrada->fetchRow(array('CXEN_ID_CAIXA_ENTRADA = ?' => $id));
            if ($rowCxenCaixaEntradaSelect) {
                $dataCxenCaixaEntradaSelect = $rowCxenCaixaEntradaSelect->toArray();
                $dataCxenCaixaEntradaSelect["CXEN_ID_TIPO_CAIXA_HIDDEN"] = $dataCxenCaixaEntradaSelect["CXEN_ID_TIPO_CAIXA"];
                
//                $cxen_id_tipo_caixa_hidden = new Zend_Form_Element_Hidden('CXEN_ID_TIPO_CAIXA_HIDDEN');
//                $form->addElement($cxen_id_tipo_caixa_hidden);
                $form->populate($dataCxenCaixaEntradaSelect);
            }
        }
        $cxen_ds_caixa_entrada = $form->getElement('CXEN_DS_CAIXA_ENTRADA');
        $cxen_ds_caixa_entrada->setAttrib('readonly', 'readonly')->setRequired(false);
        
        $tpcx_ds_proprietario_caixa = $form->getElement('CXEN_ID_TIPO_CAIXA');
        $tpcx_ds_proprietario_caixa->setAttrib('disabled', 'disabled')->setRequired(false);
        

        
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //Zend_Debug::dump($data); 
             
            /**
             * Preservando os dados decodificados em json do post
             */
            $data_json = $data;
            
            /**
             * Decodificando o post da subcessão
             */
            $arr_secao_subsecao = Zend_Json::decode($data["SECAO_SUBSECAO"]);
            //Zend_Debug::dump($arr_secao_subsecao);
            /**
             * Adicionando no form para passar pelo isValid() do form
             */
            $secao_subsecao = $form->getElement('SECAO_SUBSECAO');
            $secao_subsecao->addMultiOptions(array($data_json["SECAO_SUBSECAO"] => $arr_secao_subsecao["LOTA_SIGLA_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_DSC_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_COD_LOTACAO"].' - '.$arr_secao_subsecao["LOTA_SIGLA_SECAO"]));
            
            /**
             * Decodificando o post do grupo
             */
            $arr_grupo = Zend_Json::decode($data["CXGS_ID_GRUPO"]);
            //Zend_Debug::dump($arr_grupo);
            /**
             * Adicionando no form para passar pelo isValid() do form
             */
            $cxgs_id_grupo = $form->getElement('CXGS_ID_GRUPO');
            $cxgs_id_grupo->addMultiOptions(array($data_json["CXGS_ID_GRUPO"] => $arr_grupo["SGRS_DS_GRUPO"]));
            
            
            if ($form->isValid($data)) {
                    /**
                     * Tratando os dados do post para passar os dados para a rotina de insersão de caixa
                     */
                    $data["CXGS_ID_GRUPO"] = $arr_grupo["SGRS_ID_GRUPO"];
                    
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    try {
                        

                         /**
                         * DADOS DA TABELA SAD_TB_CXGS_GRUPO_SERVICO
                         */
                        //$data['CXGS_ID_CAIXA_ENTRADA'] = $data['CXEN_ID_CAIXA_ENTRADA'];
                        $data['CXGS_ID_GRUPO'] = $data['CXGS_ID_GRUPO'];
                        
                         /**
                         * SALVANDO NA TABELA SAD_TB_CXGS_GRUPO_SERVICO
                         */
                        $rowCxgsGrupoServico = $SadTbCxgsGrupoServico->createRow($data);
                        //Zend_Debug::dump($rowCxgsGrupoServico->toArray());
                        $rowCxgsGrupoServico->save();            
                        
                        $db->rollBack();
                        //$db->commit();
                        
                    } catch (Exception $exc) {
                        $db->rollBack();
                        $error_message = $exc->getMessage(); 
                        $this->_helper->flashMessenger ( array('message' => "Não foi possível adicionar o Grupo de Serviço: $arr_grupo[SGRS_DS_GRUPO] a caixa! <br/> $error_message", 'status' => 'error'));
                        $form->populate($data);
                        $this->view->form = $form;
                        return;
                        //return $this->_helper->_redirector('addnovogrupo','caixagruposervico','sosti',array('id'=>$id));
                    }
                    
                $this->_helper->flashMessenger ( array('message' => "O Grupo de Serviço: $arr_grupo[SGRS_DS_GRUPO] foi adionado a caixa!", 'status' => 'success'));
                return $this->_helper->_redirector('addnovogrupo','caixagruposervico','sosti');
            }
        }
        $this->view->form = $form;

    }
    
    public function ajaxsubsecoesAction()
    {
        echo $secao    = Zend_Filter::FilterStatic($this->_getParam('secao'),'alnum');
        echo $lotacao     = Zend_Filter::FilterStatic($this->_getParam('lotacao'),'int');
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao, $lotacao);
        $this->view->Lotacao_array = $Lotacao_array;
    }
    
    public function ajaxgruposervicoporpaiAction()
    {
        if ($this->getRequest()->isPost()){
            $server =  new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            
            $id[LOTA_SIGLA_SECAO] = Zend_Filter::FilterStatic($data[LOTA_SIGLA_SECAO],'alnum');
            $id[LOTA_COD_LOTACAO] = Zend_Filter::FilterStatic($data[LOTA_COD_LOTACAO],'int');
            $id[LOTA_TIPO_LOTACAO] = Zend_Filter::FilterStatic($data[LOTA_TIPO_LOTACAO],'int');
            
            $SosTbSgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico();
            $SgrsGrupoServico = $SosTbSgrsGrupoServico->getGrupoServicoPorUnidadePai($id[LOTA_SIGLA_SECAO], $id[LOTA_COD_LOTACAO],$id[LOTA_TIPO_LOTACAO]);
            
            $this->_helper->json->sendJson($SgrsGrupoServico);
        }
    }

}
