<?php

class Soseg_MinhassolicitacoesController extends Zend_Controller_Action
{
    private $_negocio = null;
    
    public function init()
    {
		$this->view->titleBrowser = 'e-Soseg - Sistema de Atendimento a Solicitações de TI';
		$this->view->module = $this->getRequest()->getModuleName();
                $this->view->controller = $this->getRequest()->getControllerName();
                $this->view->action = $this->getRequest()->getActionName();
                $this->_negocio = new Trf1_Soseg_Negocio_SolicitacaoServicosGraficos();
    }
    
   public function atendimentoAction()
    {
    	$userNs = new Zend_Session_Namespace ( 'userNs' );
    	
    	$data = $this->getRequest()->getPost();
    	
    	
         
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        //FORM VERIFICA USUÁRIO
         $form = new Sisad_Form_Verify();
         $form->getElement('COU_COD_MATRICULA')->setValue($userNs->matricula);
         $this->view->formVerificar = $form;
		
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'DH_CADASTRO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        
       
       /*verifica condições e faz tratamento nos dados*/
        $TimeInterval = new App_Sosti_TempoSla();
        
        $rows = $dados->getMinhasSolicitacoesAtendimento($userNs->matricula, $order, '', '','',$this->_getParam('tipo'));
        $this->view->tipo = $this->_getParam('tipo');
        
        $fim =  count($rows);
        for ($i = 0; $i<$fim; $i++ ) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['DOCM_DH_CADASTRO'], '', '07:00:00', '20:00:00');
            unset ($rows[$i]['DOCM_DH_CADASTRO']);
            unset ($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
            if($rows[$i]['MOFA_ID_FASE'] == 1000 || $rows[$i]['MOFA_ID_FASE'] == 1014 || $rows[$i]['DOCM_CD_MATRICULA_CADASTRO'] != $userNs->matricula ){
                unset($rows[$i]);
            }
            
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

        $this->view->title = "Minhas Solicitações em Atendimento";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    public function baixadasAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesBaixadas($userNs->matricula, $order);
       
       /*verifica condições e faz tratamento nos dados*/
        $TimeInterval = new App_Sosti_TempoSla();
        $fim =  count($rows);
        for ($i = 0; $i<$fim; $i++ ) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['DOCM_DH_CADASTRO'], '', '07:00:00', '20:00:00');
            unset ($rows[$i]['DOCM_DH_CADASTRO']);
            unset ($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

        $this->view->title = "Minhas Solicitações de TI Baixadas";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    
    public function avaliadasAction()
    {
                /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesAvaliadas($userNs->matricula, $order);
       
        /*verifica condições e faz tratamento nos dados*/
        $TimeInterval = new App_Sosti_TempoSla();
        $fim =  count($rows);
        for ($i = 0; $i<$fim; $i++ ) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['DOCM_DH_CADASTRO'], '', '07:00:00', '20:00:00');
            unset ($rows[$i]['DOCM_DH_CADASTRO']);
            unset ($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

        $this->view->title = "Minhas Solicitações de TI Avaliadas";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    
    public function pedidoinformacaoAction()
    {
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

     
        $order_column = $this->_getParam('ordem', 'MOFA_DH_FASE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $params = array(
                'matricula' => $userNs->matricula,
                'order' => $order
                 );
        
        $rows = $this->_negocio->getSolicitacoesPedidoInformacao($params);
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);
       
        $this->view->title = "Minhas Solicitações com Pedido de Informação";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    
    public function pedidoinformacaoaddAction()
    {
        /*$form = new Sosti_Form_SolicitarInformacao();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(),$form->populate($this->getRequest()->getPost())->getValues());
            
            if($data['acao'] && isset($data['acao']) && $data['acao'] == 'Incluir Informação' ) {
                $userNs = new Zend_Session_Namespace('userNs');
                $solicspace = new Zend_Session_Namespace('solicspace');
                $solicspace->request = $data;
                $solicspace->dados = $data['solicitacao'];
                $solicspace->module = (isset($data["module"]) ? $data["module"] : 'sosti');
                $solicspace->controller = $data["controller"];
                $solicspace->action = $data["action"];
                $this->view->data = $data['solicitacao'];
                $this->view->title = "Incluir Informações para a(s) Solicitação(es):";
                $this->view->form = $form;
            } else {
                
                if ($form->isValid($data)) {
                    
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $nr_documento_red = null;
                    
                    foreach ($solicspace->dados as $d) {
                        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $dados_input = Zend_Json::decode($d);
                        $DocmDocumentoHistorico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($dados_input["SSOL_ID_DOCUMENTO"]);
                        //Se for a ultima fase for solicitação de informação
                        if(in_array($DocmDocumentoHistorico[0]["FADM_ID_FASE"], array(Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO, Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI))) {
                            $envio = true;
                        }else{
                            $envio = false;
                        }
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                        $dataInfo["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataInfo['MOFA_ID_FASE'] = ($solicspace->action == 'pedidoinformacaodsv' || ($solicspace->action == 'solicitacoesdaunidade' && $solicspace->request['tipo'] == 'informacaodsv')) ? Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI : Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO; //Inclui a fase de cadastro de informação
                        $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataInfo["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        
                       
                        
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $DocmDocumentoHistorico[0]["MOFA_CD_MATRICULA"] . '@trf1.jus.br';
                        $assunto = 'Resposta da Solicitação de Informação';
                        $corpo = "Solicitação de Informação respondida.</p>
                                  Número da Solicitação: " . $dados_input['DOCM_NR_DOCUMENTO'] . " <br/>
                                  Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                  Resposta: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                       
                       
                        $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
                        if($docm_ds_hash_red->isUploaded()) {
                            
                             
                        if(is_null($nr_documento_red)) {
                            $Sosti_Anexo = new App_Sosti_Anexo();
                            $nr_documento_red = $Sosti_Anexo->anexa($docm_ds_hash_red);
                           }    
                            
                            // Incluir na tabela de anexo
                            
                            $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            // Quando a solicitação é baixada, retorna a data e hora da baixa para inserir na tabela de anexo
                            
                            $dataHoraAnexo = $baixa->setSolicitarInformacaoSolicitacao($dataInfo, $dados_input["SSOL_ID_DOCUMENTO"]);  
                            // Array com os dados a serem inseridos na tabela de anexo
                             
                            $dataAnexo["ANEX_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                            $dataAnexo["ANEX_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $nr_documento_red;
                            $dataAnexo["ANEX_DH_FASE"] = $dataHoraAnexo;
                            if ($baixa->countAnexoIncluido($dados_input["SSOL_ID_DOCUMENTO"], $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"]) == 0) {
                                $baixa->setIncluirAnexo($dataAnexo);
                            } else {
                                if($envio){
                                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                }
                                $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." com pedido de informação!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                                $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                            }
                        } else {
                            $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            $baixa->setSolicitarInformacaoSolicitacao($dataInfo, $dados_input["SSOL_ID_DOCUMENTO"]);   
                        }  
                        if($envio){
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        }
                    }
                    $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." com pedido de informação!";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($solicspace->action, $solicspace->controller, $solicspace->module);
                    }   
                }
            } else {
                $solicspace = new Zend_Session_Namespace('solicspace');
                $this->view->data = $solicspace->dados;
                $this->view->title = "Incluir informação para a(s) solicitação(es):";
                $form->populate($data);
                $this->view->form = $form;
                $this->render('pedidoinformacaoadd');
        }*/
        
        $ns = new Zend_Session_Namespace('Ns_minhassolicitacoes_incluirInfo');
        $form = new Soseg_Form_Acoes();
        $form->getElement('MOFA_DS_COMPLEMENTO')->setLabel('Descrição da Informação:');

        if ($this->getRequest()->isPost()) {
              $data = $this->getRequest()->getPost();

             // Zend_Debug::dump($data, 'data');

              $ids = implode(",", $data['solicitacao']);

              $params = array('ids' => $ids,
                              'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);

              $solicitacoes = $this->_negocio->getDadosSolicitacao($params);

              if( count($solicitacoes) > 0 ){
                  $ns->solicitacoes = $solicitacoes;
                  $ns->ids = $ids;
                  $this->view->data = $solicitacoes;
              }

            $this->view->title = "Incluir Informações para a(s) Solicitação(ões)";
          }
        
        $this->view->form = $form;
    
    }
    
    public function savepedidoinformacaoAction(){
        
        $ns = new Zend_Session_Namespace('Ns_minhassolicitacoes_incluirInfo');
        //$nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
        $form = new Soseg_Form_Acoes();
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            if($form->isValid($data)){
                
                $params = array( 'dados' => $data,
                                 'anexos' => $anexos);
                 $retorno = $this->_negocio->incluirInformacao($params);
               
                if($retorno){
                     $msg_to_user = 'Informação incluída com sucesso na(s) solicitação(ões): '.$retorno;
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                }else{
                    $msg_to_user = 'Erro ao incluir informação na(s) solicitação(ões).';
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                }
                $this->_helper->_redirector( 'pedidoinformacao', 'minhassolicitacoes', 'soseg' );
            }else{
                $params = array('ids' => $ns->ids,
                                'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO );
                $solicitacoes = $this->_negocio->getDadosSolicitacao($params);
                $this->view->data = $solicitacoes;
                $this->view->flashMessagesView = "<div class='error'> Por favor, corrija os erros do formulário. </div>";
                $form->populate($data);
                $this->view->form = $form;
                $this->render('solicitarinformacaoadd');
            }
        }
        
        
        
    }
    
    public function pedidoinformacaorespondidoAction()
    {
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'MOFA_DH_FASE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesPedidoInfRespondido($userNs->matricula, 'MOFA_DH_FASE ASC', 1024, 1025);
        /*verifica condições e faz tratamento nos dados*/
        $TimeInterval = new App_Sosti_TempoSla();
        $fim =  count($rows);
        for ($i = 0; $i<$fim; $i++ ) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOVIMENTACAO'], '', '07:00:00', '20:00:00');
            unset ($rows[$i]['MOFA_DH_FASE']);
            unset ($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);
       
        $this->view->title = "Solicitação(es) com Pedido de Informação Respondido";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
   public function minhassolicitacoesperiodoAction()
    {
    
    	$ns = 'ns_' . md5($this->getRequest ()->getControllerName().$this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array ('direcao' => 'ASC', 'ordem' => 'DOCM_DH_CADASTRO', 'itemsperpage' => 15, 'page' => 1 );
        $varSessoes = new App_SecaoPaginator ($ns,$variaveisSessaoPadrao);
        
       $page = $varSessoes->getPage();
       $itemCountPerPage = $varSessoes->getItemsperpage();

        /*Ordenação das paginas*/
        $order_column = $varSessoes->getOrdem();
        $order_direction = $varSessoes->getDirecao();
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
         $this->view->direcao = $order_direction;
        
        $order = $order_column.' '.$order_direction;
        
        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoMinhaNs');
        $form = new Sosti_Form_RelatoriosHelpdesk();
        $this->view->form = $form;
        $form->removeElement('SGRS_ID_GRUPO');
        $form->removeElement('SNAT_CD_NIVEL');
        $form->removeElement('AVALIACAO');
        $form->getElement('DATA_INICIAL')->setLabel('Data inicial baixa:');
        $form->getElement('DATA_FINAL')->setLabel('Data final baixa:');
        $Dual = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $Dual->sysdateDb();
        $this->view->sysdateFirstDay = substr($Dual->sysdateDbFirstDay(),0,10)." 00:00:00";
        $this->view->primeira = $aSlaPeriodoSpace->primeira;
        if(is_null($aSlaPeriodoSpace->primeira)){
            $aSlaPeriodoSpace->data['DATA_INICIAL'] = $Dual->sysdateDbFirstDay();
            $aSlaPeriodoSpace->data['DATA_FINAL'] = $Dual->sysdateDb();
        }
        $aSlaPeriodoSpace->pesquisar = 'Pesquisar';
        //$aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoMinhaNs');
        /*paginação*/
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $aSlaPeriodoSpace->data = $data;
            $aSlaPeriodoSpace->data_inicial = $data["DATA_INICIAL"];
            $aSlaPeriodoSpace->data_final = $data["DATA_FINAL"];
            $aSlaPeriodoSpace->data_inicial_cadastro = $data["DATA_INICIAL_CADASTRO"];
            $aSlaPeriodoSpace->data_final_cadastro = $data["DATA_FINAL_CADASTRO"];
            $aSlaPeriodoSpace->data_inicial_encaminhamento = $data["DATA_INICIAL_ENCAMINHAMENTO"];
            $aSlaPeriodoSpace->data_final_encaminhamento = $data["DATA_FINAL_ENCAMINHAMENTO"];
            $aSlaPeriodoSpace->pesquisar = $data["Pesquisar"];
            $aSlaPeriodoSpace->primeira = true;
            $this->view->primeira = $aSlaPeriodoSpace->primeira;
        }
        if ((($aSlaPeriodoSpace->data_inicial != "") && ($aSlaPeriodoSpace->data_final != "")) ||(($aSlaPeriodoSpace->data_inicial_encaminhamento != "") && ($aSlaPeriodoSpace->data_final_encaminhamento != "")) || (($aSlaPeriodoSpace->data_inicial_cadastro != "") && ($aSlaPeriodoSpace->data_final_cadastro != "")) ) {
                $form->populate($aSlaPeriodoSpace->data);
        }
        $grupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $userNs = new Zend_Session_Namespace('userNs');
        if ($aSlaPeriodoSpace->nivel) {
            $descricaoNivel = $grupo->getNivel($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel);
            $this->view->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"].' - '.$descricaoNivel[0]["SNAT_SG_NIVEL"];
        }
        if ($aSlaPeriodoSpace->grupo) {
            $this->view->descricaoGrupo = $grupo->getGrupoAtendimento($aSlaPeriodoSpace->grupo);
        }
        $this->view->title = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                              ("Minhas Solicitações Baixadas por Período: ".$aSlaPeriodoSpace->data_inicial." à ".$aSlaPeriodoSpace->data_final):
                              ("Minhas Solicitações Baixadas por Período: ".$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);
        $dadosQtde = new Application_Model_DbTable_SosTbSsolSolicitacao();
            //$order_direction = $this->_getParam ( 'direcao', 'DESC' );
            
            
        $qtde = $dadosQtde->getQtdeMinhasSolicitacoesPeriodoSla($userNs->matricula, $aSlaPeriodoSpace->data, $order);
        
        if ($aSlaPeriodoSpace->pesquisar != "") {
        	//zend_debug::dump($varSessoes->getDirecao(),'direcao sessao');
            $page = $varSessoes->getPage();
    
            /*Ordenação das paginas*/
            
           

            /*Ordenação*/
            $this->view->qtde = $qtde[0]['QTDE'];
            
            if ($qtde[0]['QTDE'] < 5000) {
              
            	
                $rows = $dados->getMinhasSolicitacoesPeriodoSla($userNs->matricula, $aSlaPeriodoSpace->data, $order);
                /*verifica condições e faz tratamento nos dados*/
                $TimeInterval = new App_TimeInterval();
                $fim =  count($rows);
                for ($i = 0; $i<$fim; $i++ ) {
                    $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotal($rows[$i]['DH_CADASTRO'], $rows[$i]['DH_FASE']);
                    $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
                }

                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($varSessoes->getPage())
                          ->setItemCountPerPage($varSessoes->getItemsperpage());
                 
                $this->view->ordem = $varSessoes->getOrdem();
                $this->view->data = $paginator;
                $this->view->ordem = $order_column;
		$this->view->direcao = $order_direction;
                $this->view->direcao_pdf = $order;
            	 
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
            }
        }
}
    
    public function minhassolicitacoesperiodopdfAction() 
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoMinhaNs');
        $this->view->titulo = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                               ($aSlaPeriodoSpace->data_inicial." à ".$aSlaPeriodoSpace->data_final):
                               ($aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);
                     
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
         
        $rows = $dados->getMinhasSolicitacoesPeriodoSla($userNs->matricula, $aSlaPeriodoSpace->data, $this->_getParam('ordem'));
       
        $TimeInterval = new App_TimeInterval();
        $fim =  count($rows);
        for ($i = 0; $i<$fim; $i++ ) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotal($rows[$i]['DH_CADASTRO'], $rows[$i]['DH_FASE']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        $grupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        if ($aSlaPeriodoSpace->nivel) {
            $descricaoNivel = $grupo->getNivel($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel);
            $this->view->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"].' - '.$descricaoNivel[0]["SNAT_SG_NIVEL"];
        }  
       // $this->view->grupo = $grupo->getGrupoAtendimento($aSlaPeriodoSpace->grupo);
        // Zend_Debug::dump($rows);exit ;
        $this->view->nome = $userNs->matricula." - ".$userNs->nome;
        $this->view->data = $rows;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->render();
       
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       10,    // margin top
                       10,    // margin bottom
                        9,    // margin header
                        9,    // margin footer
                       'L');
        $mpdf->AddPage('P', '', '0', '1');
        $mpdf->WriteHTML($body);
        $name =  'Finalizadas_periodo_'.str_replace('/', '_', $aSlaPeriodoSpace->data_inicial.'_'.$aSlaPeriodoSpace->data_final).'.pdf';
        $mpdf->Output($name,'D');
    }
    
    /**
    * Cancelar propria solicitacao
    * Não excluir essa action
    */
    public function cancelarAction()
    {
        $form = new Sosti_Form_Cancelar();
		$form->MOFA_DS_COMPLEMENTO->setRequired(false);
		$this->view->form = $form;
        $userNs = new Zend_Session_Namespace('userNs');
		$solicspace = new Zend_Session_Namespace('solicspace');
               
		if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(),$form->populate($this->getRequest()->getPost())->getValues());/*Aplica Filtros - Mantem Post*/
            if($data['acao'] && isset($data['acao']) && $data['acao'] == 'Cancelar' ) {
                $solicspace->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $this->view->title = "Cancelar - Solicitação(es)";
                $this->view->form = $form;
            } else {    
                if ($form->isValid($data)) {
                   $cancelada = '';
					$solicitada = '';
					
					foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
						$nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $dataCancelamento["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataCancelamento["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataCancelamento["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
						$dados = $baixa->cancelaSolicitacao($dataCancelamento, $dados_input["SSOL_ID_DOCUMENTO"],1);    
					
						if($dados['atendente'] != ''){
						$email = new Application_Model_DbTable_EnviaEmail();
						$sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
						$assunto = $dados['assunto'];
						$remetente = 'noreply@trf1.jus.br';
						$destinatario = $dados['atendente'].'@trf1.jus.br';
						$corpo = "A seguinte solicitação foi cancelada.</p>
								Número da Solicitação: ".$nrdocumento." <br/>
								Data da Solicitação: ".date('d/m/Y H:i:s')." <br/>
								Responsavél: ".$userNs->nome." <br/>
								Tipo de Serviço : ".$dados['assunto']." <br/>
								Descrição do Cancelamento: ".$dataCancelamento["MOFA_DS_COMPLEMENTO"]."<br/>";
						
						$email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
						}
						
					//Prepara mensagem
					if($dados['tipo'] == 1){
							$cancelada = $cancelada.", ".$nrdocumento; 
						}else{
							$solicitada = $solicitada.", ".$nrdocumento;
						}
					}
					
					if ($solicitada != '') {
						$solicitada = $solicitada . ' com cancelamento solicitado!';
						$separador = ' ';
					}

					if ($cancelada != '') {
						$cancelada = $cancelada . ' cancelada(s)!';
						$separador = "<br/>";
					}

					if (($solicitada != '') && ($cancelada != '')) {
						$separador = "<br/>Solicitação(es) n(s)º ";
					}
					//Fim prepara mensagem 
					
                    $msg_to_user = "<br/>Solicitação(es) n(s)º ".substr($cancelada, 1).$separador.substr($solicitada, 1);
					
					$this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector('atendimento','minhassolicitacoes','sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = "Cancelar - Solicitação(ões)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('cancelar');
                }
            }
        }  
    }
    
    public function emacompanhamentoAction(){
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $rows = $dados->getMinhasSolicitacoesAcompanhamento($userNs->matricula,$order);
       
        /*verifica condições e faz tratamento nos dados*/
        $TimeInterval = new App_Sosti_TempoSla();
        $fim =  count($rows);
        for ($i = 0; $i<$fim; $i++ ) {
            unset ($rows[$i]['DOCM_DH_CADASTRO']);
            unset ($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

        $this->view->title = "Meus Acompanhamentos de Solicitações de TI";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    public function desacompanharAction(){
       $data = $this->getRequest()->getPost();
       $userNs = new Zend_Session_Namespace('userNs');
       $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
       $delAcompanhamento = $tabelaPapd->delAcompanhamento($data['solicitacao'],$userNs->matricula);
       if ($delAcompanhamento) {
           $this->_helper->flashMessenger ( array('message' => 'Solicitação Desacompanhada com Sucesso', 'status' => 'success'));
           $this->_helper->_redirector('emacompanhamento', 'minhassolicitacoes', 'sosti');
       }else{
           $this->_helper->flashMessenger ( array('message' => 'Ocorreu em erro ao desacompanhar solicitação: '.$exc->getMessage(), 'status' => 'error'));
           $this->_helper->_redirector('emacompanhamento', 'minhassolicitacoes', 'sosti');
       }
    }
}