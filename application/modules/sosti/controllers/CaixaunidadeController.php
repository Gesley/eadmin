<?php

class Sosti_CaixauniodadeController extends Zend_Controller_Action
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
		
		$this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function indexAction()
    {
        
    }
    
    public function entradaAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itemCountPerPage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/
       
        $aNamespace = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getCaixaPessoal(strtoupper($aNamespace->matricula), $order);
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage($itemCountPerPage);
       
        $solicspace = new Zend_Session_Namespace('solicspace');
        $solicspace->label = "CAIXA PESSOAL";
        $this->view->title = "CAIXA PESSOAL - ".$aNamespace->nome;
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        
//        $TimeInterval = new App_Sosti_TempoSla();
//        Zend_Debug::dump($TimeInterval->tempoTotalHelpdesk('09/01/2012 08:00:00', '09/01/2012 20:00:00', '07:00:00', '20:00:00'));
    }
    
    public function baixarcaixaAction()
    {
        $form = new Sosti_Form_BaixarCaixa();
        $this->view->form = $form;
       
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if($data['acao'] && isset($data['acao']) && $data['acao'] == 'Baixar' ) {
                $userNs = new Zend_Session_Namespace('userNs');
                $solicspace = new Zend_Session_Namespace('solicspace');
                $solicspace->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $this->view->title = $solicspace->laber." - BAIXAR SOLICITAÇÃO(ES)";
                $this->view->form = $form;
            } else {    
                if ($form->isValid($data)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                        $dataBaixa["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataBaixa["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataBaixa["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        /**
                         * Pega o elemento input file do form
                         */
                        $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
                        if($docm_ds_hash_red->isUploaded()) {
                            /**
                             * Incluir no Red
                             */
                            $Sosti_Anexo = new App_Sosti_Anexo();
                            $nr_documento_red = $Sosti_Anexo->anexa($docm_ds_hash_red);
                            /**
                             * Incluir na tabela de anexo
                             */
                            $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            /**
                             * Quando a solicitação é baixada, retorna a data e hora da baixa para inserir na tabela de anexo
                             */
                            $dataHoraAnexo = $baixa->baixaSolicitacao($dataBaixa, $dados_input["SSOL_ID_DOCUMENTO"]);
                            /**
                             * Array com os dados a serem inseridos na tabela de anexo
                             */
                            $dataAnexo["ANEX_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                            $dataAnexo["ANEX_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $nr_documento_red;
                            $dataAnexo["ANEX_DH_FASE"] = $dataHoraAnexo;
                            if ($baixa->countAnexoIncluido($dados_input["SSOL_ID_DOCUMENTO"], $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"]) == 0) {
                                $baixa->setIncluirAnexo($dataAnexo);
                            } else {
                                /**
                                 * Envio de email de resposta
                                 */
                                    $email = new Application_Model_DbTable_EnviaEmail();
                                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                    $remetente = 'noreply@trf1.jus.br';
                                    $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"].'@trf1.jus.br';
                                    $assunto = 'Baixa de Solicitação';
                                    $corpo = "Uma solicitação foi baixada, será necessário acessar o sistema para avaliação.</p>
                                            Número da Solicitação: ".$dados_input['DOCM_NR_DOCUMENTO']." <br/>
                                            Data da Solicitação: ".$dados_input["DATA_ATUAL"]." <br/>
                                            Atendente: ".$userNs->nome." <br/>
                                            Tipo de Serviço Solicitado: ".$dados_input['SSER_DS_SERVICO']."<br/>
                                            Descrição da Baixa: ".$data["MOFA_DS_COMPLEMENTO"]."<br/>";
                                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                /**
                                 * Fim do envio de email
                                 */  
                                $this->_helper->flashMessenger ( array('message' => 'A solicitação foi baixada mas não foi possível incluir o anexo na solicitação'.substr($solicitacoesEncaminhadas, 1).' pois ele já está incluído!', 'status' => 'notice'));
                                $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                            }
                        } else {
                            $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            $baixa->baixaSolicitacao($dataBaixa, $dados_input["SSOL_ID_DOCUMENTO"]);    
                        }  
                        /**
                         * Envio de email de resposta
                         */
                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $remetente = 'noreply@trf1.jus.br';
                            $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"].'@trf1.jus.br';
                            $assunto = 'Baixa de Solicitação';
                            $corpo = "Uma solicitação foi baixada, será necessário acessar o sistema para avaliação.</p>
                                    Número da Solicitação: ".$dados_input['DOCM_NR_DOCUMENTO']." <br/>
                                    Data da Solicitação: ".$dados_input["DATA_ATUAL"]." <br/>
                                    Atendente: ".$userNs->nome." <br/>
                                    Tipo de Serviço Solicitado: ".$dados_input['SSER_DS_SERVICO']."<br/>
                                    Descrição da Baixa: ".$data["MOFA_DS_COMPLEMENTO"]."<br/>";
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." baixada(s)!";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector('entrada','caixapessoal','sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = $solicspace->label." - BAIXAR SOLICITAÇÃO(ÕES)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('baixarcaixa');
                }
            }
        }  
    }
    
    public function esperacaixaAction()
    {
        $form = new Sosti_Form_EsperaCaixa();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if($data['acao'] && isset($data['acao']) && $data['acao'] == 'Espera' ) {
                $userNs = new Zend_Session_Namespace('userNs');
                $solicspace = new Zend_Session_Namespace('solicspace');
                $solicspace->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $id = $solicitacao_array[0];
                $this->view->title = $solicspace->laber." - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";
                $this->view->form = $form;
            } else {    
                if ($form->isValid($data)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                        $dataEspera["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataEspera["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataEspera["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $idDocumento = $dados_input["SSOL_ID_DOCUMENTO"]; 
                        $limite = new Application_Model_DbTable_Dual();
                        $dataSespSolicEspera["SESP_DH_LIMITE_ESP"] = $limite->setEspera();
                         /**
                         * Pega o elemento input file do form
                         */
                        $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
                        if($docm_ds_hash_red->isUploaded()) {
                            /**
                             * Incluir no Red
                             */
                            $Sosti_Anexo = new App_Sosti_Anexo();
                            $nr_documento_red = $Sosti_Anexo->anexa($docm_ds_hash_red);
                            /**
                             * Incluir na tabela de espera
                             */
                            $espera = new Application_Model_DbTable_SosTbSespSolicEspera();                       
                            $dataHoraAnexo = $espera->esperaSolicitacao($idDocumento, $dataEspera, $dataSespSolicEspera);
                            /**
                             * Envio de email de resposta
                             */
                                $email = new Application_Model_DbTable_EnviaEmail();
                                $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                $remetente = 'noreply@trf1.jus.br';
                                $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"].'@trf1.jus.br';
                                $assunto = 'Solicitação em Espera';
                                $corpo = "Sua solicitação foi colocada em espera.</p>
                                        Número da Solicitação: ".$dados_input['DOCM_NR_DOCUMENTO']." <br/>
                                        Data da Solicitação: ".$dados_input["DATA_ATUAL"]." <br/>
                                        Atendente: ".$userNs->nome." <br/>
                                        Tipo de Serviço Solicitado: ".$dados_input['SSER_DS_SERVICO']."<br/>
                                        Descrição da motivo: ".$dataEspera["MOFA_DS_COMPLEMENTO"]."<br/>";
                                $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                            /**
                             * Fim do envio de email
                             */
                            /**
                             * Array com os dados a serem inseridos na tabela de anexo
                             */
                            $dataAnexo["ANEX_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                            $dataAnexo["ANEX_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $nr_documento_red;
                            $dataAnexo["ANEX_DH_FASE"] = $dataHoraAnexo;
                            $anexo = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            if ($anexo->countAnexoIncluido($dados_input["SSOL_ID_DOCUMENTO"], $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"]) == 0) {
                                $anexo->setIncluirAnexo($dataAnexo);
                                
                            } else {
                                $this->_helper->flashMessenger ( array('message' => 'Não foi possível incluir o anexo na solicitação'.substr($solicitacoesEncaminhadas, 1).' pois ele já está incluído!', 'status' => 'notice'));
                                $this->_helper->_redirector('segundonivel','atendimentotecnico','sosti');
                            }
                           } else {
                            $espera = new Application_Model_DbTable_SosTbSespSolicEspera();
                            $espera->esperaSolicitacao($idDocumento, $dataEspera, $dataSespSolicEspera);  
                            /**
                             * Envio de email de resposta
                             */
                                $email = new Application_Model_DbTable_EnviaEmail();
                                $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                $remetente = 'noreply@trf1.jus.br';
                                $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"].'@trf1.jus.br';
                                $assunto = 'Solicitação em Espera';
                                $corpo = "Sua solicitação foi colocada em espera.</p>
                                        Número da Solicitação: ".$dados_input['DOCM_NR_DOCUMENTO']." <br/>
                                        Data da Solicitação: ".$dados_input["DATA_ATUAL"]." <br/>
                                        Atendente: ".$userNs->nome." <br/>
                                        Tipo de Serviço Solicitado: ".$dados_input['SSER_DS_SERVICO']."<br/>
                                        Descrição da motivo: ".$dataEspera["MOFA_DS_COMPLEMENTO"]."<br/>";
                                $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                            /**
                             * Fim do envio de email
                             */
                        } 
                    }
                    $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." colocada(s) em espera!";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector('entrada','caixapessoal','sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = $solicspace->laber." - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('esperacaixa');
                }
            }
        }   
    }

    public function encaminharAction()
    {
        $userNs = new Zend_Session_Namespace('userNs'); 
        $form = new Sosti_Form_AtendimentoClienteEncaminhar();
        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $solicNamespace = new Zend_Session_Namespace('solicitacaoNs');
        $solicspace = new Zend_Session_Namespace('solicspace');
        $encaminhamento = $form->getElement('ENCAMINHAMENTO');
        $encaminhamento->setMultiOptions(array(
                                                'nivel'   => 'Outro nível de atendimento', 
                                               //'pessoal' => 'Caixa pessoal',
                                               'trf'   => 'Outro Grupo de Atendimento', 
                                                ));
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->getRequest()->getPost();
            
            if($data['acao'] && isset($data['acao']) && $data['acao'] == 'Encaminhar' ) {
                
                $solicNamespace->dadosCaixa = $data;
                $solicNamespace->dadosSolicitacao = $data['solicitacao'];
                
                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                 foreach ($data["solicitacao"] as $d) {
                      $dados_input = Zend_Json::decode($d);
                      $idServico = $dados_input["SSER_ID_SERVICO"];
                      $cdNivelCaixa = $dados_input["SNAT_CD_NIVEL"];
                      
                      /**
                       * Validação
                       */
                      $idGrupo_aux = $idGrupo;
                      $row = $SosTbSserServico->find($idServico);
                      $servicos = $row->toArray();
                      $idGrupo = $servicos[0][SSER_ID_GRUPO];
                      if($idGrupo_aux){
                          if($idGrupo != $idGrupo_aux){
                              $msg_to_user = "Não é possível realizar ações com solicitações com serviços de grupos de serviço diferentes";
                              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                              $this->_helper->_redirector('entrada','caixapessoal','sosti');
                              return;
                          }
                      }
                 }
                 
                 $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                 $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                 $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                 foreach ($arr_sgrs_id_grupo as $value) {
                      $value_option = Zend_Json::decode($value);
                      if($value_option["SGRS_ID_GRUPO"] === $idGrupo){
                          $sgrs_id_grupo->removeMultiOption($value);
                      }
                 }
                 
                 if(!$cdNivelCaixa){
                    $encaminhamento->setMultiOptions(array(
                                               // 'nivel'   => 'Outro nível de atendimento', 
                                               //'pessoal' => 'Caixa pessoal',
                                               'trf'   => 'Outro Grupo de Atendimento', 
                                                ));
                 }
                 
                 
                 
                $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisPorServico($idServico);
                //echo $idServico;
                //Zend_Debug::dump($NivelAtendimento); exit;
                $snas_id_nivel =  $form->SNAS_ID_NIVEL;
                foreach ($NivelAtendimento as $NivelAtendimento_p):
                    if($NivelAtendimento_p["SNAT_CD_NIVEL"] != $cdNivelCaixa){
                        $snas_id_nivel->addMultiOptions(array($NivelAtendimento_p["SNAT_ID_NIVEL"] => $NivelAtendimento_p["SNAT_DS_NIVEL"] ));
                    }
                endforeach;
                
                
                foreach ($solicspace->dados as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                    $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                }
                $nrdocumento = substr($solicitacoesEncaminhadas, 1);
               
            } else {  
                $dadosCaixa = $solicNamespace->dadosCaixa;
                
                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                 foreach ($dadosCaixa["solicitacao"] as $d) {
                      $dados_input = Zend_Json::decode($d);
                      $idServico = $dados_input["SSER_ID_SERVICO"];
                      $cdNivelCaixa = $dados_input["SNAT_CD_NIVEL"];
                      
                      /**
                       * Validação
                       */
                      $idGrupo_aux = $idGrupo;
                      $row = $SosTbSserServico->find($idServico);
                      $servicos = $row->toArray();
                      $idGrupo = $servicos[0][SSER_ID_GRUPO];
                      if($idGrupo_aux){
                          if($idGrupo != $idGrupo_aux){
                              $msg_to_user = "Não é possível realizar ações com solicitações com serviços de grupos de serviço diferentes";
                              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                              $this->_helper->_redirector('entrada','caixapessoal','sosti');
                              return;
                          }
                      }
                 }
                 
                 $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                 $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                 $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                 foreach ($arr_sgrs_id_grupo as $value) {
                      $value_option = Zend_Json::decode($value);
                      if($value_option["SGRS_ID_GRUPO"] === $idGrupo){
                          $sgrs_id_grupo->removeMultiOption($value);
                      }
                 }
                 
                 if(!$cdNivelCaixa){
                    $encaminhamento->setMultiOptions(array(
                                               // 'nivel'   => 'Outro nível de atendimento', 
                                               //'pessoal' => 'Caixa pessoal',
                                               'trf'   => 'Outro Grupo de Atendimento', 
                                                ));
                 }
                
                $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisPorServico($idServico);
                //echo $idServico;
                //Zend_Debug::dump($NivelAtendimento); exit;
                $snas_id_nivel =  $form->SNAS_ID_NIVEL;
                foreach ($NivelAtendimento as $NivelAtendimento_p):
                    if($NivelAtendimento_p["SNAT_CD_NIVEL"] != $cdNivelCaixa){
                        $snas_id_nivel->addMultiOptions(array($NivelAtendimento_p["SNAT_ID_NIVEL"] => $NivelAtendimento_p["SNAT_DS_NIVEL"] ));
                    }
                endforeach;
                
                $destino = Zend_Json::decode($data['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data['SGRS_ID_GRUPO'] => $destino["SGRS_DS_GRUPO"]));

                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                $sser_id_servico->addMultiOptions(array($data['SSER_ID_SERVICO'] => ''));
              
                if($data["APSP_ID_PESSOA"] != ''){
                    
                    $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbApspPapSistUnidPess();
                    $lota_cod_lotacao = explode(' - ', $data['LOTA_COD_LOTACAO']);

                    $pessoas = $OcsTbPmatMatricula->getPessoa($lota_cod_lotacao[2]);
                    $apsp_id_pessoa = $form->APSP_ID_PESSOA;
                    foreach ($pessoas as $pessoas_p):
                        $apsp_id_pessoa->addMultiOptions(array($data["APSP_ID_PESSOA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
                    endforeach;
                }
           
                if ($form->isValid($data)) {
                    /*NIVEL*/
                    if($data["ENCAMINHAMENTO"] == 'nivel') { 
                       
                        if( $data["SNAS_ID_NIVEL"] && isset($data["SNAS_ID_NIVEL"] )) {
                            $dadosCaixa = $solicNamespace->dadosCaixa;
                            $solicitacoesEncaminhadas = '';
                            
                            foreach ($dadosCaixa["solicitacao"] as $d) {
                                $dados_input = Zend_Json::decode($d);
                                $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
   
                                $movimentacao = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            
                                $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                                $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $movimentacao;
                                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];
                                $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $data['SNAS_ID_NIVEL'];
                                $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
                                    /**
                                     * Pega o elemento input file do form
                                     */
                                    $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
                                    if($docm_ds_hash_red->isUploaded()) {
                                        /**
                                         * Incluir no Red
                                         */
                                        $Sosti_Anexo = new App_Sosti_Anexo();
                                        $nr_documento_red = $Sosti_Anexo->anexa($docm_ds_hash_red);
                                        /**
                                         * Incluir na tabela de anexo
                                         */
                                        $anexo = new Application_Model_DbTable_SosTbSsolSolicitacao();
                                        /**
                                         * Quando a solicitação é baixada, retorna a data e hora da baixa para inserir na tabela de anexo
                                         */
                                        $dataHoraId = $SosTbSnatNivelAtendimento->trocanivelSolicitacao($idDocmDocumento , $dataMofaMoviFase, $dataSnasNivelAtendSolic);
                        
                                        /**
                                         * Array com os dados a serem inseridos na tabela de anexo
                                         */
                                        $dataAnexo["ANEX_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                                        $dataAnexo["ANEX_ID_MOVIMENTACAO"] = $dataHoraId['ID_MOVIMENTACAO'];
                                        $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $nr_documento_red;
                                        $dataAnexo["ANEX_DH_FASE"] = $dataHoraId['DATA_HORA'];
                                        if ($anexo->countAnexoIncluido($dados_input["SSOL_ID_DOCUMENTO"], $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"]) == 0) {
                                            $anexo->setIncluirAnexo($dataAnexo);
                                        } else {
                                            $this->_helper->flashMessenger ( array('message' => 'A solicitação foi encaminhada mas não foi possível incluir o anexo na solicitação'.substr($solicitacoesEncaminhadas, 1).' pois ele já está incluído!', 'status' => 'notice'));
                                            $this->_helper->_redirector('entrada','caixapessoal','sosti');
                                        }
                                } else {
                                     $SosTbSnatNivelAtendimento->trocanivelSolicitacao($idDocmDocumento , $dataMofaMoviFase, $dataSnasNivelAtendSolic);
                                }
                                    $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                                    $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                                    $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                            }
                            $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." encaminhada(s)!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('entrada','caixapessoal','sosti');
                        }
                        
                     /*GRUPO*/
                    } else if($data["ENCAMINHAMENTO"] == 'trf') {
                        
                        $dadosCaixa = $solicNamespace->dadosCaixa;
                        $matricula = $userNs->matricula;
                        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();

                        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $db->beginTransaction();
                        try {

                            foreach ($dadosCaixa["solicitacao"] as $d) {
                                $dados_input = Zend_Json::decode($d);

                                $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];

                                $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] =  $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                                $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                                $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $matricula;
                                $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $dados_input["MODE_ID_CAIXA_ENTRADA"];

                                $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $destino["SGRS_SG_SECAO_LOTACAO"];
                                $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $destino["SGRS_CD_LOTACAO"];
                                $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                                $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $destino["CXEN_ID_CAIXA_ENTRADA"];

                                /**
                                 * ENCAMINHAMENTO DE SOLICITAÇÃO DE TI DE SEÇÃO PARA O TRIBUNAL CODIGO 1022
                                 */
                                $dataMofaMoviFase["MOFA_ID_FASE"] = 1001;
                                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $matricula;
                                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];

                                $id_serviço = explode('|',$data["SSER_ID_SERVICO"]);
                                $dataSsesServicoSolic["SSES_ID_SERVICO"] = $id_serviço[0];

                                /**
                                 * ENVIA PARA O INDICADOR DE MENOR NÍVEL
                                 */
                                $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($destino["SGRS_ID_GRUPO"]);
                                if($NivelAtendSolic){
                                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $NivelAtendSolic["SNAT_ID_NIVEL"];
                                 }else{
                                     /**
                                      * PARA OS GRUPOS DE SERVIÇO QUE NÃO POSSUEM NÍVEIS COMO O DA DESENVOLVIMENTO E SUSTENTAÇÃO
                                      */
                                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = '';
                                 }

                                   /**
                                     * Pega o elemento input file do form
                                     */
                                    $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
                                    if($docm_ds_hash_red->isUploaded()) {
                                        /**
                                         * Incluir no Red
                                         */
                                        $Sosti_Anexo = new App_Sosti_Anexo();
                                        $nr_documento_red = $Sosti_Anexo->anexa($docm_ds_hash_red);
                                        /**
                                         * Incluir na tabela de anexo
                                         */
                                        $anexo = new Application_Model_DbTable_SosTbSsolSolicitacao();
                                        /**
                                         * Quando a solicitação é baixada, retorna a data e hora da baixa para inserir na tabela de anexo
                                         */
                                        $dataHoraId = $SosTbSsolSolicitacao->encaminhaSolicitacao($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase , $dataSsesServicoSolic, $dataSnasNivelAtendSolic );
                                        /**
                                         * Array com os dados a serem inseridos na tabela de anexo
                                         */
                                        $dataAnexo["ANEX_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                                        $dataAnexo["ANEX_ID_MOVIMENTACAO"] = $dataHoraId['ID_MOVIMENTACAO'];
                                        $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $nr_documento_red;
                                        $dataAnexo["ANEX_DH_FASE"] = $dataHoraId['DATA_HORA'];
                                        if ($anexo->countAnexoIncluido($dados_input["SSOL_ID_DOCUMENTO"], $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"]) == 0) {
                                            $anexo->setIncluirAnexo($dataAnexo);
                                        } else {
                                            $this->_helper->flashMessenger ( array('message' => 'A solicitação foi encaminhada mas não foi possível incluir o anexo na solicitação'.substr($solicitacoesEncaminhadas, 1).' pois ele já está incluído!', 'status' => 'notice'));
                                            $this->_helper->_redirector('entrada','caixapessoal','sosti');
                                        }
                                } else {
                                    $SosTbSsolSolicitacao->encaminhaSolicitacao($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase , $dataSsesServicoSolic, $dataSnasNivelAtendSolic );   
                                }
                                    $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                                    $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                                }
                                $db->commit();

                                /**
                                * Envio de Emails
                                */
                               if($destino["SGRS_ID_GRUPO"] == 4 && $data["SSER_ID_SERVICO"] != '6071|N|S'){
                                   /**
                                    * Email para Responsáveis pela caixa
                                    * SOSTI: 2012010001135011350160010367
                                    */
                                    $email = new Application_Model_DbTable_EnviaEmail();
                                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                    $remetente = 'noreply@trf1.jus.br';
                                    $assunto = 'Cadastro de Solicitação para Caixa do NOC';
                                    $corpo = "Solicitação Encaminhada para Caixa do NOC</p>
                                              Número da Solicitação: ".$dados_input['DOCM_NR_DOCUMENTO']." <br/>
                                              Data da Solicitação: ".$dados_input["DATA_ATUAL"]." <br/>
                                              Encaminhado por: ".$userNs->nome." <br/>
                                              Tipo de Serviço Solicitado: ".$dados_input['SSER_DS_SERVICO']."<br/>
                                              Descrição do Encaminhamento: ".nl2br($data["MOFA_DS_COMPLEMENTO"])."<br/>";
                                    try {
                                        $email->setEnviarEmail($sistema, $remetente, 'wanderson.martins@trf1.jus.br', $assunto, $corpo);
                                        $email->setEnviarEmail($sistema, $remetente, 'alex.peres@trf1.jus.br', $assunto, $corpo);
                                        $email->setEnviarEmail($sistema, $remetente, 'plinio.meireles@trf1.jus.br', $assunto, $corpo);
                                        $email->setEnviarEmail($sistema, $remetente, 'noc@srvmon1-trf1.trf1.gov.br', $assunto, $corpo);
                                    } catch (Exception $exc) {

                                    }
                               }
                                
                            $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." encaminhada(s)!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('entrada','caixapessoal','sosti');
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $erro =  $exc->getMessage();
                            $msg_to_user = "Ocorreu um erro ao encaminhar a solicitação! <br/> $erro ";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector('entrada','caixapessoal','sosti');

                        }

                    /*PESSOAL*/
                    } else if ($data["ENCAMINHAMENTO"] == 'pessoal') {
                            $dataSol = $solicNamespace->dadosCaixa;
                            foreach ($dataSol["solicitacao"] as $d) {
                                $dados_input = Zend_Json::decode($d);
                                $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                                $movimentacao = $dados_input["MOFA_ID_MOVIMENTACAO"];
                                $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
                                $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $movimentacao;
                                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];
                                
                                $dataSsolSolicitacao["SSOL_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                                $dataSsolSolicitacao["SSOL_CD_MATRICULA_ATENDENTE"] = $data["APSP_ID_PESSOA"];
     
                                $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];

                                    /**
                                     * Pega o elemento input file do form
                                     */
                                    $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
                                    if($docm_ds_hash_red->isUploaded()) {
                                        /**
                                         * Incluir no Red
                                         */
                                        $Sosti_Anexo = new App_Sosti_Anexo();
                                        $nr_documento_red = $Sosti_Anexo->anexa($docm_ds_hash_red);
                                        /**
                                         * Incluir na tabela de anexo
                                         */
                                        $anexo = new Application_Model_DbTable_SosTbSsolSolicitacao();
                                        /**
                                         * Quando a solicitação é baixada, retorna a data e hora da baixa para inserir na tabela de anexo
                                         */
                                        $dataHoraId = $SadTbMofaMoviFase->encaminhaCaixaPessoalSolicitacao($idDocmDocumento ,$dataMofaMoviFase, $dataSsolSolicitacao);
                                        /**
                                         * Array com os dados a serem inseridos na tabela de anexo
                                         */
                                        $dataAnexo["ANEX_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                                        $dataAnexo["ANEX_ID_MOVIMENTACAO"] = $dataHoraId['ID_MOVIMENTACAO'];
                                        $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $nr_documento_red;
                                        $dataAnexo["ANEX_DH_FASE"] = $dataHoraId['DATA_HORA'];
                                        if ($anexo->countAnexoIncluido($dados_input["SSOL_ID_DOCUMENTO"], $dataAnexo["ANEX_NR_DOCUMENTO_INTERNO"]) == 0) {
                                            $anexo->setIncluirAnexo($dataAnexo);
                                        } else {
                                            /**
                                             * Envio de email de resposta
                                             */
                                                $email = new Application_Model_DbTable_EnviaEmail();
                                                $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                                $remetente = 'noreply@trf1.jus.br';
                                                $destinatario = $data["APSP_ID_PESSOA"].'@trf1.jus.br';
                                                $assunto = 'Encaminhamento de Solicitação';
                                                $corpo = "Uma solicitação foi encaminhada para sua Caixa Pessoal.</p>
                                                        Número da Solicitação: ".$dados_input['DOCM_NR_DOCUMENTO']." <br/>
                                                        Data da Solicitação: ".$dados_input["DATA_ATUAL"]." <br/>
                                                        Encaminhado por: ".$userNs->nome." <br/>
                                                        Tipo de Serviço Solicitado: ".$dados_input['SSER_DS_SERVICO']."<br/>
                                                        Descrição do Encaminhamento: ".$data["MOFA_DS_COMPLEMENTO"]."<br/>";
                                                $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                            /**
                                             * Fim do envio de email
                                             */
                                            $this->_helper->flashMessenger ( array('message' => 'A solicitação foi encaminhada mas não foi possível incluir o anexo na solicitação'.substr($solicitacoesEncaminhadas, 1).' pois ele já está incluído!', 'status' => 'notice'));
                                            $this->_helper->_redirector('entrada','caixapessoal','sosti');
                                        }
                                } else {
                                    $SadTbMofaMoviFase->encaminhaCaixaPessoalSolicitacao($idDocmDocumento ,$dataMofaMoviFase, $dataSsolSolicitacao);
                                }
                                /**
                                 * Envio de email de resposta
                                 */
                                    $email = new Application_Model_DbTable_EnviaEmail();
                                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                    $remetente = 'noreply@trf1.jus.br';
                                    $destinatario = $data["APSP_ID_PESSOA"].'@trf1.jus.br';
                                    $assunto = 'Encaminhamento de Solicitação';
                                    $corpo = "Uma solicitação foi encaminhada para sua Caixa Pessoal.</p>
                                            Número da Solicitação: ".$dados_input['DOCM_NR_DOCUMENTO']." <br/>
                                            Data da Solicitação: ".$dados_input["DATA_ATUAL"]." <br/>
                                            Encaminhado por: ".$userNs->nome." <br/>
                                            Tipo de Serviço Solicitado: ".$dados_input['SSER_DS_SERVICO']."<br/>
                                            Descrição do Encaminhamento: ".$data["MOFA_DS_COMPLEMENTO"]."<br/>";
                                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                /**
                                 * Fim do envio de email
                                 */
                                $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                            }
                            $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." encaminhada(s)!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('entrada','caixapessoal','sosti');
                    }
                } else {
                    $this->view->title = $solicspace->label." - ENCAMINHAR SOLICITAÇÃO(ES)";
                    $this->view->data = $solicNamespace->dadosSolicitacao;
                    $this->view->form = $form;
                }
            }
        }
        $this->view->title = $solicspace->label." - ENCAMINHAR SOLICITAÇÃO(ES)";
        $this->view->data = $solicNamespace->dadosSolicitacao;
        $pop['LOTA_COD_LOTACAO'] =  strtoupper($userNs->siglalotacao) .' - '. strtoupper($userNs->descicaolotacao) .' - '. strtoupper($userNs->codlotacao);
        $form->populate ($pop); 
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
        $OcsTbApspPapSistUnidPess = new Application_Model_DbTable_OcsTbApspPapSistUnidPess();        
        $PapSistUnidPess_array = $OcsTbApspPapSistUnidPess->getPessoa($lota_cod_lotacao );
        $this->view->PapSistUnidPess_array = $PapSistUnidPess_array;
    }
    
}
