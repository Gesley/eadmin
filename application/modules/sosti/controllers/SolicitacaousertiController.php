<?php

class Sosti_SolicitacaousertiController extends Zend_Controller_Action {
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
	
    public function init() {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
        $this->view->titleBrowser = 'e-Sosti';
    }

    public function ajaxpessoaspartesAction() {
        $matriculanome = $this->_getParam('term', '');

        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $servidores = $OcsTbPmatMatricula->getPessoasPartes($matriculanome);

        $fim = count($servidores);
        for ($i = 0; $i < $fim; $i++) {
            $servidores[$i] = array_change_key_case($servidores[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($servidores);
    }

    public function indexAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order = $this->_getParam('ordem', 'SSOL_CD_DOCUMENTO');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order . ' ' . $direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /* Ordenação */

        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $select = $table->select(); /* ->order($order_aux); */

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Solicitações";
    }

    public function formAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $this->view->title = "Cadastro de Solicitação dos Usuários da TI (Grupos de Serviços do TRF1)";
        $form = new Sosti_Form_SolicitacaoUserTi();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        
        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $PerfilGestor = $ocsTbPupePerfilUnidPessoa->getPerfilGestao($userNs->matricula);
        
        $this->view->perfilGestor = $PerfilGestor;
        
        $rows = $dados->getSolicitacoesVencidasparaAvaliar($userNs->matricula);
        $Dual = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $Dual->sysdateDb();

        foreach ($ocsTbPupePerfilUnidPessoa->getTodosPerfilPessoa($userNs->matricula) as $f) {
            $perfGestao[] = $f["PERF_ID_PERFIL"];
        }
        $this->view->perfGestaoTi = in_array(35, $perfGestao);
        
        $this->view->exitesolicitacoesparaAvaliar = (int) count($rows);
        $this->view->form = $form;
    }

    public function ajaxservicosAction() {
        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $id[SGRS_ID_GRUPO] = Zend_Filter::FilterStatic($data[SGRS_ID_GRUPO], 'int');
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServico_array = $SosTbSserServico->fetchAll("SSER_ID_GRUPO = $id[SGRS_ID_GRUPO]")->toArray();
            $this->view->servicos = $SosTbSserServico_array;
        }
    }

    public function ajaxdesctomboAction() {
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $Tombo = new Application_Model_DbTable_TomboTiCentral();
        $Tombo_array = $Tombo->getDescTombo($id);
        $this->view->desctombo = $Tombo_array;
    }

    public function ajaxnomesolicitanteAction() {
        $matriculanome = $this->_getParam('term', '');
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $nome_array = $OcsTbPmatMatricula->getNomeSolicitanteAjax($matriculanome);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxunidadeAction() {
        $unidade = $this->_getParam('term', '');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxpessoaunidadeAction() {
        $matricula = $this->_getParam('term', '');
        $aux = explode(' - ', $matricula);
        $matricula = $aux[0];

        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $dadosPessoais = $OcsTbPmatMatricula->getDadosPessoaisAjax($matricula);
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $fone = $mapperDocumento->getUltimoTelefoneCadastrado($matricula);
        $dadosPessoais[0]['SSOL_NR_TELEFONE_EXTERNO'] = $fone;
        $this->_helper->json->sendJson($dadosPessoais);
    }

    public function saveAction() {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $this->view->title = "Cadastro de solicitação";
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_SolicitacaoUserTi();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $Dual = new Application_Model_DbTable_Dual();
        $Ocs_tb_pmat_matricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $email = new Application_Model_DbTable_EnviaEmail();
        $this->view->sysdate = $Dual->sysdateDb();

        if ($this->getRequest()->isPost()) {
            /**
             * Seção de validações do formulário 
             */
            $data = $this->getRequest()->getPost();


            /* Adiciona o grupo escolhido no form */
            $destino = Zend_Json::decode($data['SGRS_ID_GRUPO']);
            $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
            $sgrs_id_grupo->addMultiOptions(array($data['SGRS_ID_GRUPO'] => $destino["SGRS_DS_GRUPO"]));


            /* Serviços do grupo de serviço escolhido - para validação */
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $servicos = $SosTbSserServico->getServicoPorGrupo($destino['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
            $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
            foreach ($servicos as $servicos_p):
                $sser_id_servico->addMultiOptions(array($servicos_p['SSER_ID_SERVICO'] . '|' . $servicos_p['SSER_IC_TOMBO'] . '|' . $servicos_p['SSER_IC_VIDEOCONFERENCIA'] => $servicos_p["SSER_DS_SERVICO"]));
            endforeach;


            /* Serviço de videoconferência - valida a presença da data de inicio da mesma */
            $dados_servico = explode('|', $data["SSER_ID_SERVICO"]);
            ($dados_servico[2] == 'S') ? ($form->getElement('SSES_DT_INICIO_VIDEO')->setRequired(true)) : ('');


            /* Serviço que exige o tombo - valida a presença do número do tombo */
            $dados_servico = explode('|', $data["SSER_ID_SERVICO"]);
            ($dados_servico[1] == 'S') ? ($form->getElement('SSOL_NR_TOMBO')->setRequired(true)) : ('');

            if ($form->isValid($data)) {
                $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                $form->ANEXOS->receive();
                $nrDocsRed = null;
                if (!is_null($data["ANEXOS"])/*$form->ANEXOS->isReceived()*/) {
                    try {
                        $upload = new App_Multiupload_NewMultiUpload();
                        $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
                    } catch (Exception $exc) {
                        $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                        $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                    }
                }

                /**
                 * Validação do cadastro de solicitação POR ORDEM DE
                 */
                if (isset($data['PORORDEMDE']) && $data['PORORDEMDE'] != "") {
                    $arr_exploded_porordemde = explode(" - ", $data['PORORDEMDE']);
                    $matricula = $arr_exploded_porordemde[0];
                    $nomeMatricula = $Ocs_tb_pmat_matricula->getPessoaMat($matricula);
                    if (count($nomeMatricula) == 0) {
                        $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foram encontrados registros da pessoa informada no campo 'POR ORDEM DE'. Neste campo, pesquise a pessoa pelo nome e depois selecione-a.";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                        $this->_helper->_redirector('form', 'solicitacaouserti', 'sosti');
                    } else {
                        $data['PORORDEMDE'] = $nomeMatricula[0]['VALUE'];
                    }
                }

                $unidade = explode(' - ', $data['UNIDADE']);

                $data['DOCM_SG_SECAO_REDATORA'] = $unidade[3];
                $data['DOCM_CD_LOTACAO_REDATORA'] = $unidade[0];

                $data['DOCM_SG_SECAO_GERADORA'] = $unidade[3];
                $data['DOCM_CD_LOTACAO_GERADORA'] = $unidade[0];

                $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = $userNs->matricula;
                $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = 160; //Solicitação de serviços a TI
                $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = $data['DOCM_SG_SECAO_GERADORA'];
                $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = $data['DOCM_CD_LOTACAO_GERADORA'];
                $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = $data['DOCM_SG_SECAO_REDATORA'];
                $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = $data['DOCM_CD_LOTACAO_REDATORA'];
                $dataDocmDocumento["DOCM_ID_PCTT"] = 2539; //PCTT Solicitação de TI
                $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = $data['DOCM_DS_ASSUNTO_DOC'];
                $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Sistuação Digital Gerado pelo sistema
                $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Situaação Pública
                $dataDocmDocumento["DOCM_DS_PALAVRA_CHAVE"] = substr($data['DOCM_DS_ASSUNTO_DOC'], 0, 100);

                $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = 1;
                $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = $data["SSOL_ED_LOCALIZACAO"];
                $dataSsolSolicitacao["SSOL_NR_TOMBO"] = $data["SSOL_NR_TOMBO"];
                $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"] = $data["SSOL_SG_TIPO_TOMBO"];
                $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = $data["SSOL_DS_OBSERVACAO"];
                unset($dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO']);
                unset($dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO']);
                $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = $data['SSOL_DS_EMAIL_EXTERNO'];
                $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = $data['SSOL_NR_TELEFONE_EXTERNO'];

                $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $unidade[3];
                $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $unidade[0];
                $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;

                $destino = Zend_Json::decode($data["SGRS_ID_GRUPO"]);

                $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $destino['SGRS_SG_SECAO_LOTACAO'];
                $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $destino['SGRS_CD_LOTACAO'];
                $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $destino['CXEN_ID_CAIXA_ENTRADA']; //Caixa de atendimento 

                $dataMofaMoviFase["MOFA_ID_FASE"] = 1006; //CADASTRO SOLICITACAO TI
                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solicitação.";

                $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
                $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($destino["SGRS_ID_GRUPO"]);

                if ($NivelAtendSolic) {
                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $NivelAtendSolic["SNAT_ID_NIVEL"];
                } else {
                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = '';
                }

                $id_servico = explode('|', $data["SSER_ID_SERVICO"]);
                $dataSsesServicoSolic["SSES_ID_SERVICO"] = $id_servico[0];
                $dataSsesServicoSolic["SSES_NR_TOMBO"] = $data["SSOL_NR_TOMBO"];
                $dataSsesServicoSolic["SSES_SG_TIPO_TOMBO"] = $data["SSOL_SG_TIPO_TOMBO"];
                if ($id_servico[2] == "S") {
                    $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = $data["SSES_DT_INICIO_VIDEO"];
                }

                if ($nrDocsRed["erro"]) {
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('form');
                    return;
                }
                if (!$nrDocsRed["existentes"]) {
                    if (!$nrDocsRed["incluidos"]) {
                        try {
                            $dataAcompanhantes = $data['acompanhante_sosti'];
                            $dataPorOrdemDe = $data['PORORDEMDE'];
                            $dataRetorno = $ssolSolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed, $dataAcompanhantes, $dataPorOrdemDe);
                            $DocmDocumento = $ssolSolicitacao->getDadosSolicitacao($dataRetorno["DOCM_ID_DOCUMENTO"]);

                            $msg_to_user = "Solicitação nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua solicitação.: " . $exc->getMessage();
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector('form', 'solicitacaouserti', 'sosti');
                        }
                    } else {
                        try {
                            $dataAcompanhantes = $data['acompanhante_sosti'];
                            $dataPorOrdemDe = $data['PORORDEMDE'];
                            $dataRetorno = $ssolSolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed, $dataAcompanhantes, $dataPorOrdemDe);
                            $DocmDocumento = $ssolSolicitacao->getDadosSolicitacao($dataRetorno["DOCM_ID_DOCUMENTO"]);

                            $msg_to_user = "Solicitação nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua solicitação.: " . $exc->getMessage();
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector('form', 'solicitacaouserti', 'sosti');
                        }
                    }
                } else {
                    foreach ($nrDocsRed["existentes"] as $existentes) {
                        $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->form = $form;
                    $this->render('form');
                    return;
                }

                /**
                 * Envio de Emails
                 */
                if ($destino["SGRS_ID_GRUPO"] == 4 && $data["SSER_ID_SERVICO"] != '6071|N|S') {
                    /**
                     * Email para Responsáveis pela caixa
                     * SOSTI: 2012010001135011350160010367
                     */
                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                    $remetente = 'noreply@trf1.jus.br';
                    $assunto = 'Cadastro de Solicitação para Caixa do NOC';
                    $corpo = "Solicitação Cadastrada para Caixa do NOC</p>
                              Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                              Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                              Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                              Descrição da Solicitação: " . nl2br($DocmDocumento["DOCM_DS_ASSUNTO_DOC"]) . "<br/>
                              Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                    try {
                        $email->setEnviarEmail($sistema, $remetente, 'wanderson.martins@trf1.jus.br', $assunto, $corpo);
                        $email->setEnviarEmail($sistema, $remetente, 'alex.peres@trf1.jus.br', $assunto, $corpo);
                        $email->setEnviarEmail($sistema, $remetente, 'plinio.meireles@trf1.jus.br', $assunto, $corpo);
                        $email->setEnviarEmail($sistema, $remetente, 'noc@srvmon1-trf1.trf1.gov.br', $assunto, $corpo);
                    } catch (Exception $exc) {
                        
                    }  
                }
                /**
                 * Email para video Conferencia
                 */ else if ($data["SSER_ID_SERVICO"] == '6071|N|S') {
                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                    $remetente = 'noreply@trf1.jus.br';
                    $assunto = 'Solicitação de Videoconferência';
                    $corpo = "Solicitação de Videoconferência</p>
                              Solicitante: " . $userNs->nome . " <br/>
                              Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                              Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                              Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                              Descrição da Solicitação: " . nl2br($DocmDocumento["DOCM_DS_ASSUNTO_DOC"]) . "<br/>
                              Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                    try {
                        $email->setEnviarEmail($sistema, $remetente, 'noc@trf1.jus.br', $assunto, $corpo);
                        $email->setEnviarEmail($sistema, $remetente, 'ditec@trf1.jus.br', $assunto, $corpo);
                        $email->setEnviarEmail($sistema, $remetente, 'coint@trf1.jus.br', $assunto, $corpo);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação/agendamento para NOC, DITEC e COINT, Solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                    }
                }
                /**
                 * Email de Confirmação ao cadastrante
                 */
                $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                $remetente = 'noreply@trf1.jus.br';
                $destinatario = $userNs->matricula . '@trf1.jus.br';
                $assunto = 'Cadastro de Solicitação';
                $corpo = "Cadastro de Solicitação efetuado com sucesso</p>
                          Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                          Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Solicitação: " . nl2br($DocmDocumento["DOCM_DS_ASSUNTO_DOC"]) . "<br/>
                          Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                try {
                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                }
                /**
                 * Caso exista acompanhanate
                 */
                if (($data['acompanhante_sosti']) && ($data['acompanhante_sosti'] != NULL)) {
                        try {
                    $emails = array();
                    foreach ($data['acompanhante_sosti'] as $acompanhante) {
                        $matricula = explode("-", $acompanhante);
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $matricula[0] . '@trf1.jus.br';
                        $assunto = 'Cadastro de Solicitação - Acompanhamento de Baixa';
                        $corpo = "Prezado Usuário(a),<br/>
                          Você foi cadastrado(a) como Acompanhante de Baixa na Solicitação descrita abaixo.<br/>
                          Cadastro de Solicitação efetuado com sucesso</p>
                          Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                          Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Solicitação: " . nl2br($DocmDocumento["DOCM_DS_ASSUNTO_DOC"]) . "<br/>
                          Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        } 
                        }catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                        }
                        $this->_helper->_redirector('form', 'solicitacaouserti', 'sosti');
                    
                }
                /**
                 * Caso seja Por ordem de aalguma pessoa
                 */
                if (($data['PORORDEMDE']) && ($data['PORORDEMDE'] != NULL)) {

                    $matricula = explode(" - ", $data['PORORDEMDE']);
                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                    $remetente = 'noreply@trf1.jus.br';
                    $destinatario = $matricula[0] . '@trf1.jus.br';
                    $assunto = 'Cadastro de Solicitação de TI';
                    $corpo = "Prezado(a) Usuário(a),<br/>" .
                            $userNs->nome . " cadastrou uma Solicitação de TI a seu pedido, conforme descrito abaixo:<br/>
                          Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                          Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Solicitação: " . nl2br($DocmDocumento["DOCM_DS_ASSUNTO_DOC"]) . "<br/>
                          Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                    try {
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                    }
                    $this->_helper->_redirector('form', 'solicitacaouserti', 'sosti');
                }
                $this->_helper->_redirector('form', 'solicitacaouserti', 'sosti');
            } else {
                /**
                 *  Seção de preenchimento do form 
                 */
                /* Faz a decodificação das entidades html dos campos de descrição */
                $form->getElement('DOCM_DS_ASSUNTO_DOC')->removeFilter('HtmlEntities');
                if ($form->getElement('DOCM_DS_ASSUNTO_DOC')->hasErrors()) {
                    $form->getElement('DOCM_DS_ASSUNTO_DOC')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                }
                $form->getElement('SSOL_DS_OBSERVACAO')->removeFilter('HtmlEntities');
                if ($form->getElement('SSOL_DS_OBSERVACAO')->hasErrors()) {
                    $form->getElement('SSOL_DS_OBSERVACAO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                }
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }

    public function encaminharAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_EncaminhaSolicitacao();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();

        $id = Zend_Filter::FilterStatic($this->_getParam('id', ''), 'alnum');
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao', ''), 'alnum');
        $unidade = Zend_Filter::FilterStatic($this->_getParam('unidade', ''), 'alnum');
        $caixa = Zend_Filter::FilterStatic($this->_getParam('caixa', ''), 'alnum');
        $servico = Zend_Filter::FilterStatic($this->_getParam('servico', ''), 'alnum');
        $nivel = Zend_Filter::FilterStatic($this->_getParam('nivel', ''), 'alnum');

        $form->DOCM_ID_DOCUMENTO->setValue($id);
        $form->MOVI_SG_SECAO_UNID_ORIGEM->setValue($secao);
        $form->MOVI_CD_SECAO_UNID_ORIGEM->setValue($unidade);
        $form->MOVI_ID_CAIXA_ENTRADA->setValue($caixa);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {

                $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $data['MOVI_SG_SECAO_UNID_ORIGEM'];
                $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $data['MOVI_CD_SECAO_UNID_ORIGEM'];
                $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;
                $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $data['MOVI_ID_CAIXA_ENTRADA'];

                $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $data["MODE_SG_SECAO_UNID_DESTINO"];
                $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $data["MODE_CD_SECAO_UNID_DESTINO"];
                $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $data["MODE_ID_CAIXA_ENTRADA"];

                $dataMofaMoviFase["MOFA_ID_FASE"] = 1001;
                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $data['SNAS_ID_NIVEL'];

                $table->encaminhaSolicitacao($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $dataMofaMoviFase);

                $msg_to_user = "Solicitação cadastrada!";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                $this->_helper->_redirector('encaminhar', 'solicitacao', 'sosti');
            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }

    public function solicitacaopdfAction() {
        $id = Zend_Filter::FilterStatic($this->_getParam('solic'), 'int');

        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();

        $DocmDocumento = $SosTbSsolSolicitacao->getDadosSolicitacao($id);
        $this->view->DocmDocumento = $DocmDocumento;

        $DocmDocumentoHistorico = $mapperDocumento->getHistoricoDCMTO($id);
        $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico;

        $AnexAnexo = $SadTbAnexAnexo->fetchAll("ANEX_ID_DOCUMENTO = $id")->toArray();
        $this->view->AnexAnexo = $AnexAnexo;

        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();

        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));
        $mpdf = new mPDF();

        $mpdf->AddPage('P', '', '0', '1');
        $imagem_path = realpath(APPLICATION_PATH . '/../public/img/BrasaoBrancoRelatorio.jpg');
        //Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true, $constrain=true, $watermark=false, $shownoimg=true, $allowvector=true) {
        $mpdf->Image($imagem_path, 94, 20, 23, 22, 'jpg', '', true, true, false, false, true);

        /* imagem da marca d'agua */
        $imagem_path = realpath(APPLICATION_PATH . '/../public/img/marcaDaguaTRFRelatorio.jpg');
        //Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true, $constrain=true, $watermark=false, $shownoimg=true, $allowvector=true) {
        $mpdf->Image($imagem_path, 90, 90, 20, 20, 'jpg', '', true, true, true, true, true);

        $mpdf->WriteHTML($body);

        $name = 'SOSTI_DOC_SOLICITACAO_NUMERO_' . $DocmDocumento[DOCM_NR_DOCUMENTO] . '.pdf';

        $mpdf->Output($name, 'D');
    }
       
}
