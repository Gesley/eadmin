<?php

class Sosti_SolicitacaoController extends Zend_Controller_Action
{
    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch()
    {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function init()
    {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        /* Initialize action controller here */
        $this->view->titleBrowser = 'e-Sosti';
    }

    public function indexAction()
    {
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

        $this->view->title = "SOLICITAÇÕES";
    }

    public function formAction()
    {
        $aNamespace = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getSolicitacoesVencidasparaAvaliar($aNamespace->matricula);
        $Dual = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $Dual->sysdateDb();

        $this->view->exitesolicitacoesparaAvaliar = (int)count($rows);


        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $PerfilGestor = $ocsTbPupePerfilUnidPessoa->getPerfilGestao($aNamespace->matricula);
        $this->view->perfilGestor = $PerfilGestor;

        $this->view->title = "CADASTRO DE SOLICITAÇÃO";
        $form = new Sosti_Form_Solicitacao();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;
    }

    public function ajaxpessoasporordemdeAction()
    {
        $matriculanome = $this->_getParam('term', '');

        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $servidores = $OcsTbPmatMatricula->getPessoasPartes($matriculanome);

        $fim = count($servidores);
        for ($i = 0; $i < $fim; $i++) {
            $servidores[$i] = array_change_key_case($servidores[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($servidores);
    }

    public function ajaxservicosAction()
    {
        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $id[SGRS_ID_GRUPO] = Zend_Filter::FilterStatic($data[SGRS_ID_GRUPO], 'int');
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServico_array = $SosTbSserServico->getServicoPorGrupo($id[SGRS_ID_GRUPO], 'SSER_DS_SERVICO ASC');
            $this->view->servicos = $SosTbSserServico_array;
        } else {
            $id[SGRS_ID_GRUPO] = $this->_getParam('grupoID');
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServico_array = $SosTbSserServico->getServicoPorGrupo($id[SGRS_ID_GRUPO], 'SSER_DS_SERVICO ASC');
            $this->view->servicos = $SosTbSserServico_array;
        }
    }

    public function ajaxniveisAction()
    {
        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $id[SNAT_ID_GRUPO] = Zend_Filter::FilterStatic($data[SGRS_ID_GRUPO], 'int');
            $SosTbSserServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $SosTbSserServico_array = $SosTbSserServico->getTodosNiveis($id[SNAT_ID_GRUPO]);
            $this->view->niveis = $SosTbSserServico_array;
        }
    }

    public function ajaxdesctomboAction()
    {
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $Tombo = new Application_Model_DbTable_TomboTiCentral();
        $Tombo_array = $Tombo->getDescTombo($id);
        $this->view->desctombo = $Tombo_array;
    }

    public function ajaxnomesolicitanteAction()
    {
        $matriculanome = $this->_getParam('term', '');
        $sigla = $this->_getParam('sigla', '');
        $cod_lota = $this->_getParam('secao_subsecao_unidade', null);
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $nome_array = $OcsTbPmatMatricula->getNomeSolicitanteAjax($matriculanome, $sigla, $cod_lota, false);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxunidadeAction()
    {
        $unidade = $this->_getParam('term', '');
        $sigla = $this->_getParam('sigla', NULL);
        $secao = $this->_getParam('secao', NULL);

        if (empty($sigla) && !empty($secao)) {
            $sigla = $secao;
            $secao = NULL;
        }

        $subsecao = $this->_getParam('subsecao', NULL);
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade, $sigla, $secao, $subsecao);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxpessoaunidadeAction()
    {
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

    public function ajaxpessoasacompanhamentoAction()
    {
        $matriculanome = $this->_getParam('term', '');

        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $sevidores = $OcsTbPmatMatricula->getPessoasPartes($matriculanome);

        $fim = count($sevidores);
        for ($i = 0; $i < $fim; $i++) {
            $sevidores[$i] = array_change_key_case($sevidores[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($sevidores);
    }

    public function saveAction()
    {

        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(3600);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $this->view->title = "CADASTRO DE SOLICITAÇÃO";
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_Solicitacao();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $Dual = new Application_Model_DbTable_Dual();
        $Ocs_tb_pmat_matricula = new Application_Model_DbTable_OcsTbPmatMatricula();
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
                        $this->_helper->_redirector('form', 'solicitacao', 'sosti');
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
                $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = str_replace(array("(", "-", ")"), "", $data['SSOL_NR_TELEFONE_EXTERNO']);

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
                            $dataRetorno = $ssolSolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, null, $dataAcompanhantes, $dataPorOrdemDe);
                            $DocmDocumento = $ssolSolicitacao->getDadosSolicitacao($dataRetorno["DOCM_ID_DOCUMENTO"]);

                            $msg_to_user = "Solicitação nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua solicitação.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector('form', 'solicitacao', 'sosti');
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
                            $msg_to_user = "Não foi possível cadastrar sua solicitação.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector('form', 'solicitacao', 'sosti');
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

                if ($data["SSER_ID_SERVICO"] == '6072|N|S') {
                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                    $remetente = 'noreply@trf1.jus.br';
                    $assunto = 'Solicitação de Videoconferência';
                    $corpo = "Solicitação de Videoconferência</p>
                              Solicitante: " . $userNs->nome . " <br/>
                              Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                              Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                              Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                              Descrição da Solicitação: " . nl2br(substr($DocmDocumento["DOCM_DS_ASSUNTO_DOC"], 0, 4000)) . "<br/>
                              Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                    try {
                        $email->setEnviarEmail($sistema, $remetente, 'noc@trf1.jus.br', $assunto, $corpo);
                        $email->setEnviarEmail($sistema, $remetente, 'ditec@trf1.jus.br', $assunto, $corpo);
                        $email->setEnviarEmail($sistema, $remetente, 'coint@trf1.jus.br', $assunto, $corpo);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação/agendamento para NOC, DITEC e COINT, Solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                    }
                }

                $email = new Application_Model_DbTable_EnviaEmail();
                $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                $remetente = 'noreply@trf1.jus.br';
                $destinatario = $userNs->matricula . '@trf1.jus.br';
                $assunto = 'Cadastro de Solicitação';
                $corpo = "Cadastro de Solicitação efetuado com sucesso</p>
                          Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                          Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Solicitação: " . nl2br(substr($DocmDocumento["DOCM_DS_ASSUNTO_DOC"], 0, 4000)) . "<br/>
                          Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                try {
                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                }
                if (($data['acompanhante_sosti']) && ($data['acompanhante_sosti'] != NULL)) {
                    foreach ($data['acompanhante_sosti'] as $acompanhante) {
                        $matricula = explode("-", $acompanhante);
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $matricula[0] . '@trf1.jus.br';
                        $assunto = 'Cadastro de Solicitação - Acompanhamento de Baixa';
                        $corpo = "Prezado Usuário(a),<br/>
                          Vossa Senhoria foi cadastrado(a) como Acompanhante de Baixa na Solicitação descrita abaixo.<br/>
                          Cadastro de Solicitação efetuado com sucesso</p>
                          Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                          Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Solicitação: " . nl2br(substr($DocmDocumento["DOCM_DS_ASSUNTO_DOC"], 0, 4000)) . "<br/>
                          Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                        try {
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                        }
                    }
                }
                /**
                 * Caso seja Por ordem de alguma pessoa
                 */
                if (($data['PORORDEMDE']) && ($data['PORORDEMDE'] != NULL)) {

                    $matricula = explode(" - ", $data['PORORDEMDE']);
                    $email = new Application_Model_DbTable_EnviaEmail();
                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                    $remetente = 'noreply@trf1.jus.br';
                    $destinatario = $matricula[0] . '@trf1.jus.br';
                    $assunto = 'Cadastro de Solicitação de TI';
                    $corpo = "Prezado(a) Usuário(a),<br/>" .
                        $userNs->nome . " cadastrou uma Solicitação de TI a seu pedido, conforme descrito abaixo:<br/>
                          Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                          Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Solicitação: " . nl2br(substr($DocmDocumento["DOCM_DS_ASSUNTO_DOC"], 0, 4000)) . "<br/>
                          Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                    try {
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                    }
                    $this->_helper->_redirector('form', 'solicitacao', 'sosti');
                }
                $this->_helper->_redirector('form', 'solicitacao', 'sosti');
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

    public function encaminharAction()
    {
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

    public function solicitacaopdfAction()
    {
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

    public function solicitarinformacaoAction()
    {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);

        /**
         * INSTANCIA DA CLASSE PARA OBTER OS DADOS DAS SOLICITAÇÕES
         */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();

        /**
         * INSTANCIA DA CLASSE PARA OBTER ACOMPANHANTES DO SOSTI
         */
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();

        /**
         * ARRAY DE IDS DOS GRUPOS DAS SOLICITAÇÕES
         */
        $arrayIdGrupo = array();
        /**
         * VARIAVEL DE SESSION
         */
        $NsAction = new Zend_Session_Namespace('NsAction');
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $form = new Sosti_Form_SolicitarInformacao();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
//        $form->getElement('detalhe')->setValue('true');
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;
        $solicspace = new Zend_Session_Namespace('solicspace');
        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($data['acao'] && isset($data['acao']) && in_array($data['acao'], array('Solicitar Informação', 'Solicitar Informação ao Usuário Cadastrante'))) {
                $userNs = new Zend_Session_Namespace('userNs');
                $solicspace->dados = $data['solicitacao'];
                $solicspace->lastUrl = $_SERVER['HTTP_REFERER'];
                $solicspace->module = (isset($data["module"]) ? $data["module"] : 'sosti');
                $solicspace->controller = $data["controller"];
                $solicspace->action = $data["action"];
                $solicspace->label = $data["title"];
                /**
                 * OBTENDO OS DADOS DE VÁRIAS SOLICITAÇÕES
                 */
                $data["solicitacoes"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesJson($data["solicitacao"]);
                $arrayFinalizada = array();
                foreach ($data['solicitacoes'] as $value) {
                    /**
                     * PEGANDO OS GRUPOS DAS SOLICITACOES
                     */
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    $arrayIdGrupo[] = $solicitacao['SGRS_ID_GRUPO'];
                    
                    /** Monta um array contendo os números das solicitações que ja foram finalizadas */
                    if (Sosti_Model_DataMapper_HistoricoSolicitacao::getFinalizada($solicitacao['SSOL_ID_DOCUMENTO'])) {
                        $arrayFinalizada[$solicitacao['SSOL_ID_DOCUMENTO']] =  $solicitacao['DOCM_NR_DOCUMENTO'];  
                    }
                }
                $solicspace->finalizadas = $arrayFinalizada;
                if (count($solicspace->finalizadas) > 0 ) {
                    $this->view->flashMessagesView = "<div class='notice'><strong>Alerta:</strong> Não é possível solicitar informação para solicitações que foram canceladas, baixadas ou avaliadas.</div>";
                }
                /**
                 * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                 * SETANDO VALOR DO ID GRUPO NA SESSION
                 */
                $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
                $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;
                $this->view->data = $data['solicitacao'];
                $pendenteAvaliacao = in_array($data["action"], array('pendenteavaliacao', 'minhassolicitacoes'));
                $pedidoInfoDSV = in_array($data["action"], array('pedidoinformacaodsv', 'solicitacoesdaunidade'));
                $docValue = Zend_Json_Decoder::decode($data['solicitacao'][0]);
                $historico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($docValue["SSOL_ID_DOCUMENTO"]);
                $ultimaPosicao = array_pop($historico);
                if ($ultimaPosicao["FADM_ID_FASE"] == 1092) {
                    $tituloAcao = 'SOLICITAR INFORMAÇÃO PARA A(S) ORDEM(NS) DE SERVIÇO(S)';
                    $this->view->labelSolicit = 'N. da ordem de serviço';
                } else {
                    $this->view->labelSolicit = 'N. da solicitação';
                    $tituloAcao = 'SOLICITAR INFORMAÇÕES PARA A(S) SOLICITAÇÃO(ES)';
                }

                if ($pendenteAvaliacao || $pedidoInfoDSV) {
                    $this->view->title = "SOLICITAÇÃO DE INFORMAÇÃO AO USUÁRIO CADASTRANTE";
                } else {
                    $this->view->title = $solicspace->label . " - ".$tituloAcao;
                }
                if (($data['detalhe'] == "true") && ($data["acao"] !=  "Solicitar Informação")) {
                    $this->view->title = "DETALHE - ".$tituloAcao;
                }

                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');


                    $form->ANEXOS->receive();
                    $nrDocsRed = null;
                    if (!is_null($data["ANEXOS"])/*$form->ANEXOS->isReceived()*/) {
                        try {
                            $upload = new App_Multiupload_NewMultiUpload();
                            $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            if (($data['detalhe'] == "true") && ($data["acao"] !=  "Solicitar Informação")) {
                                echo "<script>javascript:history.back(-2)</script>";
                            } else {
                                $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                            }
                        }
                    }
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        if (key_exists($dados_input['SSOL_ID_DOCUMENTO'], $solicspace->finalizadas) === false) {
                            $id_doc = $dados_input['SSOL_ID_DOCUMENTO'];
                            $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                            $arrayEncaminhadasNr[] = $dados_input["DOCM_NR_DOCUMENTO"];
                            $dataInfo["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                            $dataInfo["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                            //Se for uma solicitação de informação ao usuário apartir
                            //de uma solicitação que um desenvolvedor já havia solicitado informação ao gestor
                            if ((in_array($solicspace->action, array('pedidoinformacaodsv', 'solicitacoesdaunidade'))) || (in_array($solicspace->action, array('pendenteavaliacao', 'minhassolicitacoes')))) {
                                $dataInfo['MOFA_ID_FASE'] = Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO;
                            } else {
                                $dataInfo['MOFA_ID_FASE'] = Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI;
                            }
                            if (($solicspace->action == 'solicitinformacaocaixa') || (($data['detalhe'] == "true") && ($data["acao"] !=  "Solicitar Informação"))) {
                                $dataInfo['MOFA_ID_FASE'] = Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO; 
                            }
    #                        $dataInfo['MOFA_ID_FASE'] = (in_array($solicspace->action, array('pedidoinformacaodsv', 'solicitacoesdaunidade')) ? Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO : Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI); // Fase de pedido de informação

                            $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            $baixa->setSolicitarInformacaoSolicitacao($dataInfo, $dados_input["SSOL_ID_DOCUMENTO"], $nrDocsRed);
                            /**
                             * Envio de email de resposta
                             */
                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $remetente = 'noreply@trf1.jus.br';
                            //Se a solicitação de informação é oriunda da 
                            //caixa de desenvolvimento e sustentação da primeira instância do trf
                            //e não for uma solicitação de informação para o usuário por parte do gestor
                            //que esta procurando saber a resposta da solicitação do desenvolvimento

                            if ($dados_input['MODE_ID_CAIXA_ENTRADA'] == Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA && !in_array($solicspace->action, array('pedidoinformacaodsv', 'solicitacoesdaunidade'))) {
                                //O sistema deve solicitar informação para o encaminhador da solicitação
                                $sadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
                                $dadosDestinatarioEmail = $sadTbMoviMovimentacao->fetchRow('MOVI_ID_MOVIMENTACAO = ' . $dados_input['MOFA_ID_MOVIMENTACAO'])->toArray();
                                $destinatario = $dadosDestinatarioEmail['MOVI_CD_MATR_ENCAMINHADOR'] . '@trf1.jus.br';
                                $descricaoTipoInformação = 'Ocorreu um pedido de informação técnica para uma solicitação em aberto.';
                            } else {
                                //O sistema deve solicitar informação para  o cadastrador da solicitação
                                $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                                $descricaoTipoInformação = 'Ocorreu um pedido de informação para uma solicitação em aberto.';
                            }
                            $solicit = $SosTbSsolSolicitacao->fetchRow('SSOL_ID_DOCUMENTO = '.$dados_input["SSOL_ID_DOCUMENTO"])->toArray();
                            $assunto = 'Solicitação de Informação';
                            if ($solicit["SSOL_NM_USUARIO_EXTERNO"] == null) {
                            $corpo = "$descricaoTipoInformação</p>
                                        Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                                        Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                        Atendente: " . $userNs->nome . " <br/>
                                        Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                        Solicitação: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                            /**
                             * Envia e-mail para os usuário externos
                             */
                            } else {
                                $corpo = "$descricaoTipoInformação</p>
                                            Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                                            Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                            Atendente: " . $userNs->nome . " <br/>
                                            Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                            Solicitação: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                                $email->setEnviarEmailExterno($sistema,  'CSTI@trf1.jus.br', $solicit["SSOL_DS_EMAIL_EXTERNO"], $assunto, $corpo);
                            }
                            #ACOMPANHANTES ATIVOS DO SOSTI
                            $PapdParteProcDoc = $SadTbPapdParteProcDoc->getAcompanhantesAtivos($id_doc);
                            $conta = count($PapdParteProcDoc);

                            $this->view->DocmPapdParteProcDoc = $PapdParteProcDoc;

                            if ($conta != 0) {
                                for ($n = 0; $n < $conta; $n++) {
                                    #DADOS DOS ACOMPANHANTES DO SOSTI
                                    $nome = $PapdParteProcDoc[$n]["NOME"];
                                    $matricula = $PapdParteProcDoc[$n]["PAPD_CD_MATRICULA_INTERESSADO"];

                                    #PREPARA DADOS E REALIZA O ENVIO PARA ACOMPANHANTES ATIVOS
                                    if (!empty($PapdParteProcDoc[$n]["PAPD_CD_MATRICULA_INTERESSADO"])) {
                                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                        $destinatario = $matricula . '@trf1.jus.br';
                                        $remetente = 'noreply@trf1.jus.br';
                                        $assunto = 'Solicitação de Informação - Acompanhante do SOSTI';
                                        $corpo = " $descricaoTipoInformação</p>
                                                            Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                                                            Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                                            Atendente: " . $userNs->nome . " <br/>
                                                            Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                                            Solicitação: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                    }
                                }
                            }
                        }
                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Solicitação(es) n(s)º " . implode(', ', $arrayEncaminhadasNr) . " com pedido de informação!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    if (count($solicspace->finalizadas) > 0) {
                        foreach ($solicspace->finalizadas as $af) {
                            $msg_to_user_2 = "Não foi possível solicitar informação para a solicitação " . $af . ", pois a mesma encontra-se cancelada, baixada ou avaliada.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user_2, 'status' => 'notice'));
                        }
                    }
                    if(!empty($solicspace->lastUrl))
                        $this->_redirect($solicspace->lastUrl);
                    else
                        $this->_helper->_redirector($solicspace->action, $solicspace->controller, $solicspace->module);
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * OBTENDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('solicitarinformacao');
                }
            }
        }
    }

    public function trocarservicoAction()
    {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        $solicspace = new Zend_Session_Namespace('solicspace');
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_TrocarServico();
        $formAnexo = new Sosti_Form_Anexo();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $Dual = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $Dual->sysdateDb();

        /**
         * ARRAY DE IDS GRUPO
         */
        $idGrupo_repd = array();
        /**
         * FORMULARIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();
        /**
         * VARIÁVEL DA SESSION
         */
        $NsAction = new Zend_Session_Namespace('NsAction');
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Trocar Serviço') {
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitação tenha família. Caso contrário, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                $solicspace->dados = $data['solicitacao'];
                $solicspace->controller = $data["controller"];
                $solicspace->action = $data["action"];
                $this->view->data = $data['solicitacao'];

                $docValue = Zend_Json_Decoder::decode($data['solicitacao'][0]);
                $historico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($docValue["SSOL_ID_DOCUMENTO"]);
                $ultimaPosicao = array_pop($historico);
                if ($ultimaPosicao["FADM_ID_FASE"] == 1092) {
                    $this->view->tituloSolicit = 'N. da ordem de serviço';
                    $tituloAcao = 'TROCA SERVIÇO DA(S) ORDEM(NS) DE SERVIÇO(S)';
                } else {
                    $this->view->tituloSolicit = 'N. da solicitação';
                    $tituloAcao = 'TROCAR SERVIÇO DA(S) SOLICITAÇÃO(ES)';
                }
                
                $this->view->title = $solicspace->label . " - ".$tituloAcao;

                foreach ($data["solicitacao"] as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $idServico = $dados_input["SSER_ID_SERVICO"];
                    /**
                     * Validação !!!!IMPORTANTE!!!!
                     * Para que uma solicitação não receba um serviço de um grupo diferente do grupo referente a caixa do grupo
                     * Exemplo: Para não permitir que uma solicitação da Seção Judiciária de Minas receba um serviço do grupo de serviço da Seção Judiciária de Goiás.
                     */
                    $idGrupo_aux = $idGrupo;
                    $row = $SosTbSserServico->find($idServico);
                    $servicos = $row->toArray();
                    $idGrupo = $servicos[0][SSER_ID_GRUPO];
                    $idGrupo_repd[] = $idGrupo;
                    if ($idGrupo_aux) {
                        if ($idGrupo != $idGrupo_aux) {
                            $msg_to_user = "Não é possível realizar TROCA DE SERVIÇO com solicitações com serviços de grupos de serviço diferentes";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                            $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                            return;
                        }
                    }
                }

                /**
                 * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                 * SETANDO VALOR DO ID GRUPO NA SESSION
                 */
                $NsAction->idGrupo_repd = $idGrupo_repd;
                $form_resposta->set_idGrupo($idGrupo_repd);
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;

//                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->fetchAll("SSER_ID_SERVICO = $idServico")->toArray();
                $idGrupo = $SserServico[0][SSER_ID_GRUPO];

                $ServicodoGrupo = $SosTbSserServico->fetchAll("SSER_ID_GRUPO = $idGrupo AND SSER_IC_ATIVO = 'S' AND SSER_IC_VISIVEL = 'S' ", 'SSER_DS_SERVICO')->toArray();
                $novoServico = $form->getElement('SSER_ID_SERVICO');
                $novoServico->addMultiOptions(array('' => '::SELECIONE::'));
                foreach ($ServicodoGrupo as $d) {
                    $novoServico->addMultiOptions(array($d["SSER_ID_SERVICO"] . '|' . $d["SSER_IC_TOMBO"] . '|' . $d["SSER_IC_VIDEOCONFERENCIA"] => $d["SSER_DS_SERVICO"]));
                }
            } else {
                $userNs = new Zend_Session_Namespace('userNs');
                $solicspace = new Zend_Session_Namespace('solicspace');
                $this->view->data = $solicspace->dados;
                /**
                 * Tratando o form
                 */
                foreach ($solicspace->dados as $value) {
                    $dados_input = Zend_Json::decode($value);
                    $idServico = $dados_input["SSER_ID_SERVICO"];
                }
//                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->fetchAll("SSER_ID_SERVICO = $idServico")->toArray();
                $idGrupo = $SserServico[0][SSER_ID_GRUPO];

                $ServicodoGrupo = $SosTbSserServico->fetchAll("SSER_ID_GRUPO = $idGrupo AND SSER_IC_ATIVO = 'S' AND SSER_IC_VISIVEL = 'S' ", 'SSER_DS_SERVICO')->toArray();
                $novoServico = $form->getElement('SSER_ID_SERVICO');
                $novoServico->addMultiOptions(array('' => '::SELECIONE::'));
                foreach ($ServicodoGrupo as $d) {
                    $novoServico->addMultiOptions(array($d["SSER_ID_SERVICO"] . '|' . $d["SSER_IC_TOMBO"] . '|' . $d["SSER_IC_VIDEOCONFERENCIA"] => $d["SSER_DS_SERVICO"]));
                }

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
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação
                        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"]; // Descrição da troca de serviço

                        /**
                         * Id do serviço vindo do form
                         */
                        $dados_servico = explode('|', $data["SSER_ID_SERVICO"]);
                        $dataSsesServicoSolic["SSES_ID_SERVICO"] = $dados_servico[0]; // Inclui novo serviço de TI

                        if ($dados_servico[2] == "S") {
                            $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = $data["SSES_DT_INICIO_VIDEO"];
                        }

                        /**
                         * Atualiza o número do tombo
                         */
                        $dataSsolSolicitacao["SSOL_NR_TOMBO"] = $data['SSOL_NR_TOMBO'];

//                        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();

                        $SosTbSserServico->setTrocaServicoSolicitacao($dados_input["SSOL_ID_DOCUMENTO"], $dataSsolSolicitacao, $dataMofaMoviFase, $dataSsesServicoSolic, $nrDocsRed);
                        /**
                         * Envio de email de resposta
                         */
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                        $assunto = 'Troca de Serviço';
                        $corpo = "Ocorreu a troca de serviço em uma solicitação.</p>
                                    Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                                    Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                    Atendente: " . $userNs->nome . " <br/>
                                    Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                    Motivo da troca de serviço: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " com o serviço trocado!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * OBTENDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $form->getElement('MOFA_DS_COMPLEMENTO')->removeFilter('HtmlEntities');
                    if ($form->getElement('MOFA_DS_COMPLEMENTO')->hasErrors()) {
                        $form->getElement('MOFA_DS_COMPLEMENTO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $form->populate($data);
                }
            }
        }
        $this->view->form = $form;
    }

    public function parecerAction()
    {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        /**
         * INSTANCIA DA CLASSE PARA OBTER OS DADOS DAS SOLICITAÇÕES
         */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        /**
         * ARRAY QUE ARMAZENA OS IDS DOS GRUPOS DAS SOLICITAÇÕES
         */
        $arrayIdGrupo = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();
        /**
         * VARIÁVEL DE SESSION
         */
        $NsAction = new Zend_Session_Namespace('NsAction');
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_ParecerSolicitacao();
        $formAnexo = new Sosti_Form_Anexo();
        $solicspace = new Zend_Session_Namespace('solicspace');
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;
        
        /** Mantém os dados das solicitações no populate do input hidden */
        $d = $this->getRequest()->getPost();
        foreach ($d['solicitacao'] as $k=>$hd) {
            $form->addElement('hidden', 'solicit_'.$k, array(
                'name' => 'solicitacao',
                'value' => $hd,
                'isArray' => true,
            ));
        }
        $form->addElement('hidden', 'actionData', array(
            'name' => 'actionData',
            'value' => $d["module"].'|'.$d['controller'].'|'.$d['action'].'|'.$d['title'],
            'isArray' => false,
        ));
        
        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                        $docValue = $data['solicitacao'][0] != null ? Zend_Json_Decoder::decode($data['solicitacao'][0]) : '';
            $historico = $SosTbSsolSolicitacao->getHistoricoSolicitacao((int) $docValue["SSOL_ID_DOCUMENTO"]);
            $ultimaPosicao = array_pop($historico);
            if ($ultimaPosicao["FADM_ID_FASE"] == 1092) {
                $acaoTitulo = "DAR PARECER NA(S) ORDEM(NS) DE SERVIÇO(S)";
            } else {
                $acaoTitulo = "DAR PARECER NA(S) SOLICITAÇÃO(ÕES)";
            }
            $this->view->title = $acaoTitulo;
            $data['actionData'] = $data['actionData'] ?: $data["module"].'|'.$data['controller'].'|'.$data['action'].'|'.$data['title'];
            $actionData = explode('|', $data['actionData']);
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Parecer') {
                if (count($data['solicitacao']) == 0) {
                    $this->_helper->flashMessenger(array('message' => 'É necessário selecionar uma Solicitação.', 'status' => 'notice'));
                    $this->_helper->_redirector($data["action"], $data["controller"], 'sosti');
                }
                /**
                 * OBTENDO OS DADOS DE VÁRIAS SOLICITAÇÕES
                 */
                // $controleSolicitacao = new Sosti_Business_ControleConcorrencia();
                $data["solicitacoes"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesJson($data["solicitacao"]);
                foreach ($data['solicitacoes'] as $value) {
                    /**
                     * PEGANDO OS GRUPOS DAS SOLICITACOES
                     */
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    $arrayIdGrupo[] = $solicitacao['SGRS_ID_GRUPO'];
                    /**
                     * Inclui na tabela de controle de concorrência
                     */
                    //$solicitacao["MOFA_ID_FASE"]
                    //$solicitacao["SSOL_ID_DOCUMENTO"]
                    //$userNs->matricula
                    //   $controleSolicitacao->addBusiness($solicitacao["SSOL_ID_DOCUMENTO"], $solicitacao["MOFA_ID_FASE"], $userNs->matricula);
                }
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                if ($data["controller"] != 'pesquisarsolicitacoes') {
                    $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                    /**
                     * Caso alguma solicitação tenha família. Caso contrário, caminho normal.
                     */
                    if ($SolicitacaoComFamilia) {
                        $data['solicitacao'] = $SolicitacaoComFamilia;
                    }
                } else {
                    foreach ($data['solicitacao'] as $key => $json):

                        $array = Zend_Json::decode($json);
                        if ($array['MOFA_ID_FASE'] == 1000 || $array['MOFA_ID_FASE'] == 1014 || $array['MOFA_ID_FASE'] == 1026) {
                            unset($data['solicitacao'][$key]);
                            $this->_helper->flashMessenger(array('message' => 'Solicitação ' . $array['DOCM_NR_DOCUMENTO'] . ' não permite parecer.', 'status' => 'notice'));
                        }
                    endforeach;

                    if (count($data['solicitacao']) == 0) {
                        $this->_helper->_redirector($data["action"], $data["controller"], 'sosti');
                    }
                }

                /**
                 * CRIANDO FORMULÁRIO RESPOSTA PADRÃO
                 * E SETANDO O ID GRUPO PARA A SESSION
                 */
                $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
                $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;
                $this->view->data = $data['solicitacao'];
                $solicspace->controller = $data["controller"];
                $solicspace->action = $data["action"];
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $form->ANEXOS->receive();
                    $nrDocsRed = null;

                    if (!is_null($data["ANEXOS"])/*$form->ANEXOS->isReceived()*/) {
                        try {
                            $upload = new App_Multiupload_NewMultiUpload();
                            $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($actionData[2], $actionData[1], 'sosti');
                        }
                    }

                    foreach ($data['solicitacao'] as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                        $dataParecer["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataParecer["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataParecer["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        /**
                         * Pega o elemento input file do form
                         */
                        $rn_documento = new Trf1_Sisad_Negocio_Documento();
                        $dadosDocumento = $rn_documento->getDocumentoPorNumero($dados_input["DOCM_NR_DOCUMENTO"]);

                        $SosTbSsolSolicitacao->parecerSolicitacao($dataParecer, $dados_input["SSOL_ID_DOCUMENTO"], $nrDocsRed);

                        $appEmail = new App_Email();
                        $dadosEmail = array(
                            'solicitacao' => $dados_input['DOCM_NR_DOCUMENTO']
                        , 'dataSolicitacao' => $dadosDocumento['DOCM_DH_CADASTRO']
                        , 'tipoServico' => $dados_input['SSER_DS_SERVICO']
                        , 'descricaoParecer' => $dataParecer['MOFA_DS_COMPLEMENTO']);
                        /* Envia e-mail para o Atendente da Solicitação */
                        if ($dados_input['ATENDENTE'] != ' - ' && $dados_input['ATENDENTE'] != '') {
                            $matriculaDestinatario = explode(' - ', $dados_input['ATENDENTE']);
                            if (count($matriculaDestinatario) > 0) {
                                $dadosEmail['destinatario'] = $matriculaDestinatario[0];
                            } else {
                                $dadosEmail['destinatario'] = $dados_input['ATENDENTE'];
                            }

                            try {
                                $appEmail->parecerSolicitacao($dadosEmail);
                            } catch (Exception $e) {
                                $this->_helper->flashMessenger(array('message' => 'Solicitação nº ' . $dados_input['DOCM_NR_DOCUMENTO'] . ': E-mail não enviado para o Atendente da solicitação.', 'status' => 'error'));
                            }
                        }
                        /* Envia e-mail para o Cadastrante da Solicitação */
                        $dadosEmail['destinatario'] = $dadosDocumento['DOCM_CD_MATRICULA_CADASTRO'];
                        try {
                            $appEmail->parecerSolicitacao($dadosEmail);
                        } catch (Exception $e) {
                            $this->_helper->flashMessenger(array('message' => 'Solicitação nº ' . $dados_input['DOCM_NR_DOCUMENTO'] . ': E-mail não enviado para o Cadastrante da solicitação.', 'status' => 'error'));
                        }
                        $this->_helper->flashMessenger(array('message' => "Solicitação nº " . $dados_input["DOCM_NR_DOCUMENTO"] . " com parecer!", 'status' => 'success'));
                    }
                    $this->_helper->_redirector($actionData[2], $actionData[1], 'sosti');
                } else {

                    /**
                     * CRIANDO FORMULÁRIO RESPOSTA PADRÃO
                     * E OBTENDO O ID GRUPO PARA A SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;
                    $this->view->data = $solicspace->dados;
                    $this->view->data = $data['solicitacao'];
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('parecer');
                }
            }
            $this->view->title = $actionData[3] . " - DAR PARECER NA(S) SOLICITAÇÃO(ÕES)";
        }
    }

    public function vincularAction()
    {
        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $form = new Sosti_Form_Vincular();
        $formVincularEntreCaixas = new Sosti_Form_VincularEntreCaixas();
        $this->view->formVincularEntreCaixas = $formVincularEntreCaixas;
        $solicspace = new Zend_Session_Namespace('solicspace');
        /**
         * Verifica a permissão para vincular entre caixas diferentes
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $permissaoPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        foreach ($permissaoPessoa->getTodosPerfilPessoa($userNs->matricula) as $ac) {
            $idPerfil[] = $ac["PERF_ID_PERFIL"];
        }
        /**
         * Perfis que podem acessar a funcionalidade que vincula entre caixas
         */
        $arrayPerfilGestao = array(44, 45, 39, 31, 42);
        foreach ($arrayPerfilGestao as $pg) {
            $verificaPerfil[] = in_array($pg, $idPerfil);
        }
        $permissao = in_array(true, $verificaPerfil);
        $this->view->permissaoEntreCaixas = $permissao;
        
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        /**
         * ARRAY DE IDS DOS GRUPOS DAS SOLICITAÇÕES
         */
        $arrayIdGrupo = array();
        /**
         * VARIÁVEL DE SESSION
         */
        $NsAction = new Zend_Session_Namespace('NsAction');
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $data = $this->getRequest()->getPost();
        $solicspace->dados_do_post = $data;
        foreach($data['solicitacao'] as $value) {
            $formVincularEntreCaixas->addElement('hidden', 'id_'.$value, array(
                'name' => 'solicitacao',
                'value' => $value,
                'isArray' => true,
            ));
        }
        $inputValue = $data["module"].'|'.$data["controller"].'|'.$data["action"];
        $formVincularEntreCaixas->addElement('hidden', 'id_'.$inputValue, array(
            'name' => 'urlcaixa',
            'value' => $inputValue,
            'isArray' => false,
        ));
        if (($data['acao'] && isset($data['acao']) && $data['acao'] == 'Vincular')) {
            $docValue = Zend_Json_Decoder::decode($data['solicitacao'][0]);
            $historico = $table->getHistoricoSolicitacao($docValue["SSOL_ID_DOCUMENTO"]);
            $ultimaPosicao = array_pop($historico);
            if ($ultimaPosicao["FADM_ID_FASE"] == 1092) {
                $titulo = 'VINCULAR ORDEM(NS) DE SERVIÇO(S)';
                $this->view->tituloSolicit = 'N. da ordem de serviço';
                $os = true;
            } else {
                $titulo = 'VINCULAR SOLICITAÇÕES';
                $this->view->tituloSolicit = 'N. da solicitação';
                $os = false;
            }
            $this->view->title = $titulo;
            $solMapper = new Sosti_Model_DataMapper_Solicitacao();
            /**
             * Verifica se é OS para retirar as solicitações que são vinculadas
             * na criação da OS.
             */
            foreach ($data['solicitacao'] as $k=>$solicitacao) {
                $soldata = json_decode($solicitacao, true);
                $arrayHistorico = $table->getHistoricoSolicitacao($soldata["SSOL_ID_DOCUMENTO"]);
                $ultimaFase = array_pop($arrayHistorico);
                $resultOs = array();
                if (($ultimaFase["FADM_ID_FASE"] == 1092)) {
                    $resultOs[] = json_encode($soldata);
                }
                $ndata[] = json_encode($soldata);
                $vincs = $solMapper->getVinculos($soldata["SSOL_ID_DOCUMENTO"]);
                if (!empty($vincs)) {
                    foreach ($vincs as $sostiVinc) {
                        $ndata[] = json_encode($solMapper->getSosti($sostiVinc["VIDC_ID_DOC_VINCULADO"]));
                        if (($ultimaFase["FADM_ID_FASE"] == 1092)) {
                            $resultOsVinc[] = json_encode($solMapper->getSosti($sostiVinc["VIDC_ID_DOC_VINCULADO"]));
                    }
                }
            }
            }
            /**
             * Se a existir alguma solicitação que for do tipo OS, apenas as solicitações
             * que são do tipo OS podem ser vinculadas
             */
            $arrayResultOs = array_merge($resultOs, $resultOsVinc);
            $temOs = count($arrayResultOs) > 0;
            $data['solicitacao'] = $temOs ? $arrayResultOs : $ndata;
            $solicspace->dados_do_post = $data;
            $solicspace->controller = $data["controller"];
            $solicspace->action = $data["action"];
            $solicspace->dados = $data['solicitacao'];
            $this->view->data = $data['solicitacao'];
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            foreach ($data["solicitacao"] as $d) {
                $dados_input = Zend_Json::decode($d);
                $idServico = $dados_input["SSER_ID_SERVICO"];
                /**
                 * Validação !!!!IMPORTANTE!!!!
                 * Para que uma solicitação não receba um serviço de um grupo diferente do grupo referente a caixa do grupo
                 * Exemplo: Para não permitir que uma solicitação da Seção Judiciária de Minas receba um serviço do grupo de serviço da Seção Judiciária de Goiás.
                 */
                $idGrupo_aux = $idGrupo;
                $row = $SosTbSserServico->find($idServico);
                $servicos = $row->toArray();
                $idGrupo = $servicos[0]['SSER_ID_GRUPO'];
                $arrayIdGrupo[] = $idGrupo;
                if ($idGrupo_aux && $temOs === false) {
                    if ($idGrupo != $idGrupo_aux) {
                        $msg_to_user = "Não é possível realizar VINCULAÇÃO com solicitações de grupos de serviço diferentes";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                        $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                        return;
                    }
                }
                if (strlen($dados_input["ASSOCIADO_OS"]) > 1) {
                    $arrayOs[] = $dados_input["ASSOCIADO_OS"];
                }
            }
            /** Quando o ator solicitar vincular 2 ou mais solicitações que possuam OS */
            if (Os_Model_DataMapper_Vinculacao::osAberta($arrayOs)) {
                $msg_to_user = "Não é possível vincular 2 ou mais solicitações que possuam OS aberta.";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                return;
            }

            foreach ($data['solicitacao'] as $solicitacao) {
                $rows[] = Zend_Json_Decoder::decode($solicitacao);
            }
            $fim = count($data["solicitacao"]);
            $TimeInterval = new App_Sosti_TempoSla();
            $verif_solic_vinc = $table->getSolicitacoesVinculadas($data["solicitacao"]);
            $cont = 0;
            for ($i = 0; $i < $fim; $i++) {
                /**
                 * Recolhendo os ids dos posts
                 */
                if ($cont == 0) {
                    $idsSolicitacoespost = $rows[$i]["SSOL_ID_DOCUMENTO"];
                } else {
                    $idsSolicitacoespost .= "," . $rows[$i]["SSOL_ID_DOCUMENTO"];
                }
                $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOFA_DH_FASE'], '', '07:00:00', '20:00:00');
                unset($rows[$i]['MOFA_DH_FASE']);
                unset($rows[$i]['DATA_ATUAL']);
                foreach ($verif_solic_vinc as $vinc) {
                    $vinculada = $vinc["ID_CONSULTADA"];
                    unset($vinc["ID_CONSULTADA"]);
                    if ($rows[$i]['SSOL_ID_DOCUMENTO'] == $vinculada) {
                        foreach ($vinc as $vinc_p) {
                            /**
                             * Recolhe somente os ids das vinculações dos filhos
                             */
//                             if ($rows[$i]['SSOL_ID_DOCUMENTO'] != $rows[$i]['VIDC_ID_DOC_PRINCIPAL']) {
                            $rows[$i]['VIDC_ID_VINCULACAO_DOCUMENTO'][] = $vinc_p["VIDC_ID_VINCULACAO_DOCUMENTO"];
//                             }
                            /**
                             * Recolhe os documentos filhos
                             */
                            $rows[$i]['VIDC_ID_DOC_VINCULADO'][] = $vinc_p["VIDC_ID_DOC_VINCULADO"];
                            /**
                             * * Recolhe o documento principal
                             */
                            $rows[$i]['VIDC_ID_DOC_PRINCIPAL'] = $vinc_p["VIDC_ID_DOC_PRINCIPAL"];

                            /**
                             * Recolhe todos os principais
                             */
                            $todas_principais[] = $rows[$i]["VIDC_ID_DOC_PRINCIPAL"];
                        }
                    }
                }
                $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
                $cont++;
            }
            /**
             * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
             * SETANDO VALOR DO ID GRUPO NA SESSION
             */
            $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
            $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
            $form_resposta->escolheResposta();
            $this->view->formResposta = $form_resposta;

            $solicspace->idsSolicitacoespost = $idsSolicitacoespost;
            $solicspace->todas_principais = $todas_principais;
            $solicspace->rows = $rows;

            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        } elseif (($data['Salvar'] && isset($data['Salvar']) && $data["Salvar"] == "Salvar")) {/* Salvar solicitações para vincular ou desvincular */
//            Zend_Debug::dump($data);
//            exit;
            if ($form->isValidPartial($data)) {

                if(empty($data['principal'])){
                    $this->_helper->flashMessenger(array('message' => 'Escolha a solicitação principal', 'status' => 'notice'));
                    $this->_helper->_redirector('vincular', 'solicitacao', 'sosti', $data);
                }

                $array_sol = Zend_Json::decode($data["solicitacao"][0]);
                $principal = $array_sol["SSOL_ID_DOCUMENTO"];
                $justificativa = $data["MOFA_DS_COMPLEMENTO"];
                /**
                 * Se for para VINCULAR solicitações
                 */
                if ($data["vincular"] == "V") {
                    /* Verifica se o usuário escolheu pelo menos duas solicitações
                     * ------------------------------------------------------------------------
                     */
                    $count_post = count($solicspace->rows);
                    if ($count_post < 2) {
                        $msg_to_user = "Não é possível realizar VINCULAÇÃO com menos de duas solicitações";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                        $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                        return;
                    }
                    /* ----------------------------------------------------------------------- */

                    /* ----------------Verificação de solicitações já vinculadas-------------- */
                    $principals = '';
                    foreach ($solicspace->rows as $solic_p) {
                        if (isset($solic_p['VIDC_ID_DOC_PRINCIPAL']))
                            $principals[] = $solic_p['VIDC_ID_DOC_PRINCIPAL'];
                    }
                    
                    if (!empty($principals)) {
                        $principals = array_unique($principals);
//                        sort($principals);
                    }

                    $transaction = Zend_Db_Table_Abstract::getDefaultAdapter()->beginTransaction();
                    try {
                        if (!empty($principals)) {
                            $table->desvincularSolicitacoes($principals, $principal);
                        }
                        $table->vincularSolicitacoes($solicspace->rows, $data["principal"], $data["MOFA_DS_COMPLEMENTO"]);
                        $transaction->commit();

                        $this->_helper->flashMessenger(array('message' => 'Solicitações Vinculadas com Sucesso', 'status' => 'success'));
                        $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                    } catch (Exception $e) {
                        $this->_helper->flashMessenger(array('message' => 'Não foi possível Vincular solicitações: ' . $e->getMessage(), 'status' => 'error'));
                        Zend_Debug::dump($e->getTraceAsString());die;
                        $transaction->rollBack();
                        $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                    }

                }
                $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
            } else {

                /**
                 * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                 * OBTENDO VALOR DO ID GRUPO NA SESSION
                 */
                $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;

                $paginator = Zend_Paginator::factory($solicspace->rows);
                $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(15);
                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }
        }
        $this->view->form = $form;
    }

    public function desvincularAction()
    {
        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $form = new Sosti_Form_Vincular();
        $form->MOFA_DS_COMPLEMENTO->setLabel('Descrição da Desvinculação');
        $solicspace = new Zend_Session_Namespace('solicspace');
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        /**
         * ARRAY DE IDS DOS GRUPOS DAS SOLICITAÇÕES
         */
        $arrayIdGrupo = array();
        /**
         * VARIÁVEL DE SESSION
         */
        $NsAction = new Zend_Session_Namespace('NsAction');
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $data = $this->getRequest()->getPost();
        $solicspace->dados_do_post = $data;
        if (($data['acao'] && isset($data['acao']) && $data['acao'] == 'Desvincular')) {
            $docValue = Zend_Json_Decoder::decode($data['solicitacao'][0]);
            $historico = $table->getHistoricoSolicitacao($docValue["SSOL_ID_DOCUMENTO"]);
            $ultimaPosicao = array_pop($historico);
            if ($ultimaPosicao["FADM_ID_FASE"] == 1092) {
                $this->view->title = 'DESVINCULAR ORDEM(NS) DE SERVIÇO(S)';
                $this->view->labelSolicitacao = 'N. da ordem de serviço';
                $os = true;
            } else {
                $this->view->title = "DESVINCULAR SOLICITAÇÕES";
                $this->view->labelSolicitacao = 'N. da solicitação';
                $os = false;
            }
            if(count($data['solicitacao']) > 1){
                $this->_helper->flashMessenger(array('message' => 'Para desvincular selecione apenas uma solicitação.', 'status' => 'notice'));
                $this->_helper->_redirector($solicspace->dados_do_post['action'], $solicspace->dados_do_post['controller'], 'sosti');
            }

            $solMapper = new Sosti_Model_DataMapper_Solicitacao();
            foreach ($data['solicitacao'] as $solicitacao) {
                $soldata = json_decode($solicitacao, true);
                $ndata[] = json_encode($soldata);
                $vincs = $solMapper->getVinculos($soldata["SSOL_ID_DOCUMENTO"]);

                if (!empty($vincs)) {
                    foreach ($vincs as $sostiVinc) {
                        if ($os == false) {
                        $ndata[] = json_encode($solMapper->getSosti($sostiVinc["VIDC_ID_DOC_VINCULADO"]));
                    }
                }
                }
                else{
                    $this->_helper->flashMessenger(array('message' => 'Solicitação sem vinculo. Favor selecione outra.', 'status' => 'notice'));
                    $this->_helper->_redirector($solicspace->dados_do_post['action'], $solicspace->dados_do_post['controller'], 'sosti');
                }
            }
            $data['solicitacao'] = $ndata;
            $solicspace->dados_do_post = $data;

            $solicspace->controller = $data["controller"];
            $solicspace->action = $data["action"];
            $solicspace->dados = $data['solicitacao'];
            $this->view->data = $data['solicitacao'];

            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            foreach ($data["solicitacao"] as $d) {
                $dados_input = Zend_Json::decode($d);
                $idServico = $dados_input["SSER_ID_SERVICO"];
                /**
                 * Validação !!!!IMPORTANTE!!!!
                 * Para que uma solicitação não receba um serviço de um grupo diferente do grupo referente a caixa do grupo
                 * Exemplo: Para não permitir que uma solicitação da Seção Judiciária de Minas receba um serviço do grupo de serviço da Seção Judiciária de Goiás.
                 */
                $idGrupo_aux = $idGrupo;
                $row = $SosTbSserServico->find($idServico);
                $servicos = $row->toArray();
                $idGrupo = $servicos[0][SSER_ID_GRUPO];
                $arrayIdGrupo[] = $idGrupo;
                if ($idGrupo_aux) {
                    if ($idGrupo != $idGrupo_aux) {
//                        $msg_to_user = "Não é possível realizar VINCULAÇÃO com solicitações de grupos de serviço diferentes";
//                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
//                        $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
//                        return;
                    }
                }
            }
            foreach ($data['solicitacao'] as $solicitacao) {
                $rows[] = Zend_Json_Decoder::decode($solicitacao);
            }
            $fim = count($data["solicitacao"]);
            $TimeInterval = new App_Sosti_TempoSla();
            $verif_solic_vinc = $table->getSolicitacoesVinculadas($data["solicitacao"]);
            $cont = 0;
            for ($i = 0; $i < $fim; $i++) {
                /**
                 * Recolhendo os ids dos posts
                 */
                if ($cont == 0) {
                    $idsSolicitacoespost = $rows[$i]["SSOL_ID_DOCUMENTO"];
                } else {
                    $idsSolicitacoespost .= "," . $rows[$i]["SSOL_ID_DOCUMENTO"];
                }
                $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOFA_DH_FASE'], '', '07:00:00', '20:00:00');
                unset($rows[$i]['MOFA_DH_FASE']);
                unset($rows[$i]['DATA_ATUAL']);
                foreach ($verif_solic_vinc as $vinc) {
                    $vinculada = $vinc["ID_CONSULTADA"];
                    unset($vinc["ID_CONSULTADA"]);
                    if ($rows[$i]['SSOL_ID_DOCUMENTO'] == $vinculada) {
                        foreach ($vinc as $vinc_p) {
                            /**
                             * Recolhe somente os ids das vinculações dos filhos
                             */
//                             if ($rows[$i]['SSOL_ID_DOCUMENTO'] != $rows[$i]['VIDC_ID_DOC_PRINCIPAL']) {
                            $rows[$i]['VIDC_ID_VINCULACAO_DOCUMENTO'][] = $vinc_p["VIDC_ID_VINCULACAO_DOCUMENTO"];
//                             }
                            /**
                             * Recolhe os documentos filhos
                             */
                            $rows[$i]['VIDC_ID_DOC_VINCULADO'][] = $vinc_p["VIDC_ID_DOC_VINCULADO"];
                            /**
                             * * Recolhe o documento principal
                             */
                            $rows[$i]['VIDC_ID_DOC_PRINCIPAL'] = $vinc_p["VIDC_ID_DOC_PRINCIPAL"];

                            /**
                             * Recolhe todos os principais
                             */
                            $todas_principais[] = $rows[$i]["VIDC_ID_DOC_PRINCIPAL"];
                        }
                    }
                }
                $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
                $cont++;
            }
            /**
             * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
             * SETANDO VALOR DO ID GRUPO NA SESSION
             */
            $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
            $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
            $form_resposta->escolheResposta();
            $this->view->formResposta = $form_resposta;

            $solicspace->idsSolicitacoespost = $idsSolicitacoespost;
            $solicspace->todas_principais = $todas_principais;
            $solicspace->rows = $rows;

            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        } elseif (($data['Salvar'] && isset($data['Salvar']) && $data["Salvar"] == "Salvar")) {/* Salvar solicitações para vincular ou desvincular */
            if ($form->isValidPartial($data)) {

                if(empty($data['solicitacao'])){
                    $msg_to_user = "Selecione ao menos uma solicitação para desvincular";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
//                $this->_helper->_redirector($data['action']);
                }
                $array_sol = Zend_Json::decode($data["solicitacao"][0]);
                $principal = $array_sol["SSOL_ID_DOCUMENTO"];
                $justificativa = $data["MOFA_DS_COMPLEMENTO"];
                /**
                 * Se for para VINCULAR solicitações
                 */
                /* Verifica se o usuário escolheu pelo menos duas solicitações
                 * ------------------------------------------------------------------------
                 */

//                $count_post = count($solicspace->rows);
//                if ($count_post < 2) {
//                    $msg_to_user = "Não é possível realizar VINCULAÇÃO com menos de duas solicitações";
//                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
//                    $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
//                    return;
//                }
                /* ----------------------------------------------------------------------- */

                /* ----------------Verificação de solicitações já vinculadas-------------- */
                $principals = '';
                $countSosti = count($data['solicitacao']);
                $countSostiTotal = count($solicspace->rows) - 1;
                if($countSosti == $countSostiTotal){
                    foreach ($solicspace->rows as $solic_p) {
                        if (isset($solic_p['VIDC_ID_DOC_PRINCIPAL']))
                            $principals[] = $solic_p['VIDC_ID_DOC_PRINCIPAL'];
                    }

                    if (!empty($principals)) {
                        $principals = array_unique($principals);
//                        sort($principals);
                    }
                    $principais = true;
                }
                else{
                    foreach ($data['solicitacao'] as $solic_p) {
                        $solic_p = json_decode($solic_p, true);
                        $principals[] = $solic_p['SSOL_ID_DOCUMENTO'];
                    }

                    if (!empty($principals)) {
                        $principals = array_unique($principals);
//                        sort($principals);
                    }
                    $principais = false;
                }

                $tpVincDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $tpVinculacao = $tpVincDoc->fetchRow('VIDC_ID_DOC_PRINCIPAL = '.$principals[0]);
                if ($tpVinculacao["VIDC_ID_TP_VINCULACAO"] == 8) {
                    $principais = true;
                }
                
                $transaction = Zend_Db_Table_Abstract::getDefaultAdapter()->beginTransaction();
                try {
                    if (!empty($principals)) {
                        $principal = json_decode($data['principal'],true);
                        $table->desvincularSolicitacoes($principals, $principal['SSOL_ID_DOCUMENTO'], $principais, $justificativa, $tpVinculacao["VIDC_ID_TP_VINCULACAO"]);
                    }
                    $transaction->commit();

                    $this->_helper->flashMessenger(array('message' => 'Solicitação(ões) Desvinculada(s) com Sucesso', 'status' => 'success'));
                    $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => 'Não foi possível Vincular solicitações: ' . $e->getMessage(), 'status' => 'error'));
//                        Zend_Debug::dump($e->getTraceAsString());die;
                    $transaction->rollBack();
                    $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                }

            }
//            $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
        } else {

            /**
             * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
             * OBTENDO VALOR DO ID GRUPO NA SESSION
             */
            $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
            $form_resposta->escolheResposta();
            $this->view->formResposta = $form_resposta;

            $paginator = Zend_Paginator::factory($solicspace->rows);
            $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        }

        $this->view->form = $form;
    }

    public function extenderprazoAction()
    {
        /**
         * Variáves de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);
        /**
         * Forms
         */
        $form = new Sosti_Form_SolicitacaoExtenderPrazo();
        /**
         * Tratamento do form
         */
        $form->removeElement('SSPA_IC_CONFIRMACAO');
        /**
         * Models
         */
        $Dual = new Application_Model_DbTable_Dual();
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        /**
         * Auxilio da data na view
         */
        $this->view->sysdate = $Dual->sysdateDb();

        /*
         * VARIAVEL DO GRUPO DE SERVIÇO PARA A RESPOSTA PADRÃO
         */
        $idGrupo_repd = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        /**
         *  Recebimento do post da caixa
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Extensão de Prazo') {

                $NsAction->dadosCaixa = $data;

                /**
                 * Tramento das vinculações
                 */
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                /**
                 * Passagem de paremetros para a view
                 */
                $NsAction->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $this->view->title = $NsAction->dadosCaixa['title'] . " - SOLICITAR EXTENSÃO DE PRAZO";
                /**
                 * Validação de solicitações do mesmo grupo e recuperação do prazo padrão de cada contrato
                 */
                foreach ($data["solicitacao"] as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $idServico = $dados_input["SSER_ID_SERVICO"];
                    $idGrupo_aux = $idGrupo;
                    $row = $SosTbSserServico->find($idServico);
                    $servicos = $row->toArray();
                    $idGrupo = $servicos[0]['SSER_ID_GRUPO'];
                    $idGrupo_repd[] = $idGrupo;
                    if ($idGrupo_aux) {
                        if ($idGrupo != $idGrupo_aux) {
                            $msg_to_user = "Não é possível realizar extensão de prazo nas solicitações com serviços de grupos de serviço diferentes";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                            /* redirecionamento */
                            $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                            /*                             * *redirecionamento */
                            return;
                        }
                    }
                }

                /**
                 * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                 * SETANDO VALOR DO ID GRUPO NA SESSION
                 */
                $NsAction->idGrupo_repd = array_unique($idGrupo_repd);
                $form_resposta->set_idGrupo(array_unique($idGrupo_repd));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;
            } else {
                if ($form->isValid($data)) {

                    /*                     * Aplica Filtros - Mantem Post */
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    /*                     * Aplica Filtros - Mantem Post */

                    foreach ($NsAction->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $idDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
                        /**
                         * Dados para inserir na tabela SAD_TB_MOFA_MOVI_FASE
                         */
                        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataMofaMoviFase["MOFA_ID_FASE"] = 1038; /* SOLICITAÇÃO DE EXTENSÃO DE PRAZO PARA SOLICITAÇÃO DE TI */
                        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["SOPR_DS_DESCRICAO_PRAZO"];
                        /**
                         * Dados para inserir na tabela SOS_TB_SSPA_SOLIC_PRAZO_ATEND
                         */
                        $dataSspaSolicPrazoAtend["SSPA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataSspaSolicPrazoAtend["SSPA_DT_PRAZO"] = new Zend_Db_Expr("TO_DATE('" . substr($data["SSPA_DT_PRAZO"], 0, -1) . ":00" . "','dd/mm/yyyy HH24:MI:SS')");
                        $dataSspaSolicPrazoAtend["SSPA_ID_DOCUMENTO"] = $idDocumento;
                        /**
                         * Método para incluir a extensão de prazo
                         */
                        $espera = new Application_Model_DbTable_SosTbSspaSolicPrazoAtend();
                        try {
                            /* INICIO ENVIAR EMAIL ######################################################### */
                            //para o encaminhador da solicitação
                            $app_Email = new App_Email();
                            if ($dados_input['MODE_SG_SECAO_UNID_DESTINO'] != 'TR') {
                                $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                                $arrayMatriculasPerfilUnidade = $ocsTbPupePerfilUnidPessoa->getMatriculasPossuiPerfilUnidade(
                                    'AUTORIZA EXTENSÃO DE PRAZO PARA SOLICITAÇÕES'
                                    , $dados_input['MODE_SG_SECAO_UNID_DESTINO']
                                    , $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
                                //Envia e-mail para todos com possibilidade de extender prazo na unidade
                                foreach ($arrayMatriculasPerfilUnidade as $matriculaPerfilUnidade) {
                                    $arrayDados = array(
                                        'destinatario' => $matriculaPerfilUnidade['PMAT_CD_MATRICULA']
                                    , 'solicitacao' => $dados_input['DOCM_NR_DOCUMENTO']
                                    , 'dataSolicitacao' => $dados_input['DATA_ATUAL']
                                    , 'dataPrazo' => $data['SSPA_DT_PRAZO']
                                    , 'descricao' => $data["SOPR_DS_DESCRICAO_PRAZO"]);
                                    $app_Email->extensaoPrazo($arrayDados);
                                }
                                //Envia e-mail para o encaminhador da solicitação
                            }
                            /* FIM ENVIAR EMAIL ############################################################ */
                            $sadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
                            $dadosMovimentacao = $sadTbMoviMovimentacao->find($dados_input["MOFA_ID_MOVIMENTACAO"])->toArray();
                            $arrayDados = array(
                                'destinatario' => $dadosMovimentacao[0]['MOVI_CD_MATR_ENCAMINHADOR']
                            , 'solicitacao' => $dados_input['DOCM_NR_DOCUMENTO']
                            , 'dataSolicitacao' => $dados_input['DATA_ATUAL']
                            , 'dataPrazo' => $data['SSPA_DT_PRAZO']
                            , 'descricao' => $data["SOPR_DS_DESCRICAO_PRAZO"]);

                            $app_Email->extensaoPrazo($arrayDados);
                            $espera->prazoSolicitacao($idDocumento, $dataMofaMoviFase, $dataSspaSolicPrazoAtend);
                            $msg_to_user = "Solicitação nº: $nrdocumento com extensão de prazo solicitado!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível extender o prazo da solicitação nº: $nrdocumento. </br>Código do erro: " . $exc->getMessage();
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                        }
                    }
                    /* redirecionamento */
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                    /*                     * *redirecionamento */
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * OBTENDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $this->view->data = $NsAction->dados;
                    $this->view->title = $NsAction->dadosCaixa['title'] . " - SOLICITAR EXTENSÃO DE PRAZO";

                    $form->getElement('SOPR_DS_DESCRICAO_PRAZO')->removeFilter('HtmlEntities');
                    if ($form->getElement('SOPR_DS_DESCRICAO_PRAZO')->hasErrors()) {
                        $form->getElement('SOPR_DS_DESCRICAO_PRAZO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $form->populate($data);
                }
            }
        }
        $this->view->form = $form;
    }

    public function baixarcaixaAction()
    {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        ini_set("memory_limit", "1024M");
        set_time_limit(1200);

        $userNs = new Zend_Session_Namespace('userNs');
        /**
         * Models
         */
        $tarefa = App_Factory_FactoryFacade::createInstance('Tarefa_Facade_Tarefa');
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $objFicha = new Application_Model_DbTable_LfsefichaServico ();
        $objHardwareSaida = new Application_Model_DbTable_SosTbMtsaMaterialSaida();
        $objSaidaSoftware = new Application_Model_DbTable_SosTbLssaLicencaSoftSaida();
        $app_Email = new App_Email();
//        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        /**
         * Classes da aplicação
         */
//        $Sosti_Anexo = new App_Sosti_Anexo();
        $NegociaGarantiaDesenvolvimento = new Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento();
        /**
         * Forms
         */
        $form = new Sosti_Form_BaixarCaixa();
        $formAnexo = new Sosti_Form_Anexo();
//        $formFaturamento = new Sosti_Form_FaturamentoDsv();
//        $formHoras= new Sosti_Form_FaturamentoHorasDsv();
//        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
//        $arrayPerfis = $ocsTbPupePerfilUnidPessoa->getPerfilUnidadePessoa($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula);
//        $perfilDSV = false;

        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));

//        foreach ($arrayPerfis as $perfil) 
//        {
//            $p = $perfil["PERF_ID_PERFIL"];
//            
//            if ($p == 25)
//            {
//                $perfilDSV = true;
//            }
//        }

        //Perfil de Desenvolvimento e Sustentação
//        if ($perfilDSV) 
//        {
//            $form->addElement($formFaturamento->getElement('PFDS_ID_CLASSIFICACAO'));
//            $form->addElement($formAnexo->getElement('PFDS_NR_DCMTO_RIA_ORIGINAL'));
//            $form->addElement($formHoras->getElement('HORAS'));
//            $form->addElement($formHoras->getElement('MINUTOS'));
//            $form->addElement($formHoras->getElement('TOTAL'));
//        
//            $form->addDisplayGroup(array('HORAS', 'MINUTOS', 'TOTAL' ), 'tempo_gasto', array("legend" => "Tempo de Desenvolvimento"));
//        }

        /**
         * Variáves de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);
        $this->view->NsAction = $NsAction;

        /**
         * ARRAY DE IDS GRUPO
         */
        $arrayIdGrupo = array();
        /**
         * FORMULARIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        if ($this->getRequest()->isPost()) {
            /**
             * Aplica os filtros do formulário
             */
            $data = $this->getRequest()->getPost();

            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Baixar') {
                /**
                 * Mantem os dados originais do post da caixa de atendimento
                 */
                $NsAction->dadosCaixa = $data;
                $docValue = Zend_Json_Decoder::decode($data['solicitacao'][0]);
                $historico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($docValue["SSOL_ID_DOCUMENTO"]);
                $ultimaPosicao = array_pop($historico);
                if ($ultimaPosicao["FADM_ID_FASE"] == 1092) {
                    $tituloAcao = 'BAIXAR ORDEM DE SERVIÇO (OS)';
                } else {
                    $tituloAcao = 'BAIXAR SOLICITAÇÃO(ES)';
                }
                $this->view->title = $NsAction->dadosCaixa["title"] . " - ".$tituloAcao;
                foreach ($data['solicitacao'] as $d) {
                    $arrayDecode = Zend_Json::decode($d);
                    $arrayIdSolic[] = $arrayDecode['SSOL_ID_DOCUMENTO'];
                }
                foreach ($arrayIdSolic as $as) {
                    $tarefaSolicit = $tarefa->listAll($as, 'TARE_ID_TAREFA ASC');
                    foreach ($tarefaSolicit as $st) {
                        $arrayStatus[] = $st->getStatus() != 1 ? true : false;
                    }
                }
                /** Validações para incluir checkbox quando for para replicar avaliação */
                if (Sosti_Model_DataMapper_ReplicaAvaliacao::addCheckbox($arrayDecode['SSOL_ID_DOCUMENTO'])) {
                    $form->addElement('Checkbox', 'REPLICA_AVALIACAO_OS', array('Label'=> 'Replicar avaliação da solicitação para a OS'));
                }
                $formAnexo->submit();
                $form->addElement($formAnexo->getElement('Salvar'));
                $this->view->form = $form;
                
                
                if (in_array(true, $arrayStatus)) {
                    $this->_helper->flashMessenger(array(
                        'message' => 'Esta OS possui defeitos que não foram disponibilizadas para homologação. Favor atendê-la(s).', 
                        'status' => 'notice'
                    ));
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                }
                if (count($data['solicitacao']) > 1 && $perfilDSV) {
                    $this->_helper->flashMessenger(array('message' => 'Selecione somente uma solicitação.', 'status' => 'notice'));
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                }
                /**
                 * Recupera todos os dados da solicitação sobescrevendo os valores vindos da caixa de entrada
                 */
                $data["solicitacao"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesJson($data["solicitacao"]);
                /**
                 * Mantem os dados originais do post da caixa de atendimento
                 */
                $NsAction->dadosCaixa = $data;
                /**
                 * Vinculação
                 */
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                /**
                 * Faz tramento da vinculação e mantem as solicitações vindas da caixa
                 */
                $NsAction->dadosCaixaSolicitacoes = $data["solicitacao"];

                /**
                 * Validação para solicitações de videoconferência
                 */
                $videoconferencia = false;
                foreach ($data['solicitacao'] as $value) {
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    if (!is_null($solicitacao['SSES_DT_INICIO_VIDEO'])) {
                        $videoconferencia = true;
                        foreach ($data['solicitacao'] as $value2) {
                            $solicitacao2 = Zend_Json_Decoder::decode($value2);
                            if (is_null($solicitacao2['SSES_DT_INICIO_VIDEO'])) {
                                $this->_helper->flashMessenger(array('message' => 'Não é possível realizar baixa com solicitações de videoconferência em conjunto com solicitações de outros serviços. Escolha somete a(s) de videoconferência(s)', 'status' => 'notice'));
                                $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                            }
                        }
                    }

                    /**
                     * Laboratorio
                     * Verifica se possui checklist e se tiver, nao podera baixar com item
                     * sem aprovacao ou recusa
                     */
                    if ($objFicha->verificaexitenciaFicha($solicitacao['SSOL_ID_DOCUMENTO'])) {
                        $verifica_s = $objHardwareSaida->verificaPendenciaHard($solicitacao['SSOL_ID_DOCUMENTO']);
                        $verifica_h = $objSaidaSoftware->verificaPendenciaSoft($solicitacao['SSOL_ID_DOCUMENTO']);
                        if (count($verifica_h) > 0 || count($verifica_s) > 0) {
                            $this->_helper->flashMessenger(array('message' => 'Não é possível realizar baixa da solicitação. A mesma possui ítens de checklist pendentes de avaliação.', 'status' => 'notice'));
                            $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                        }
                    }

                    /**
                     * PEGANDO OS GRUPOS DAS SOLICITACOES
                     */
                    $arrayIdGrupo[] = $solicitacao['SGRS_ID_GRUPO'];
                }
                $NsAction->videoconferencia = $videoconferencia;
                /** Passar o valor S para replicar a avaliação. */
                if ($NsAction->videoconferencia) {
                    $FormSosTbTsesServicoSolic = new Sosti_Form_SosTbTsesServicoSolic();
                    $form->addElement($FormSosTbTsesServicoSolic->getElement("SSES_IC_VIDEO_REALIZADA")->setValue('S')->setRequired(true));
                    $form->addDisplayGroup(array("MOFA_ID_MOVIMENTACAO", "DOCM_ID_DOCUMENTO", "DOCM_NR_DOCUMENTO", "MOFA_DS_COMPLEMENTO", "SSES_IC_VIDEO_REALIZADA", "DOCM_DS_HASH_RED", "Salvar"), 'DISP_VIDEOCONF');
                }

                /*                 * *******************************************
                 * Tratamento para Solicitações com garantia
                 * ******************************************* */
                $idsMovimentacao = array();
                foreach ($data['solicitacao'] as $value) {
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    if ($solicitacao["MODE_ID_CAIXA_ENTRADA"] == 2) {
                        $NsAction->desenvolvimento = true;
                    }
                    $idsMovimentacao[] = $solicitacao["MOFA_ID_MOVIMENTACAO"];
                }
                if ($NsAction->desenvolvimento) {
                    $NsAction->existeGarantiaPorMovimentacoes = $NegociaGarantiaDesenvolvimento->existeGarantiaPorMovimentacoes($idsMovimentacao);
                    if ($NsAction->existeGarantiaPorMovimentacoes) {
                        $NsAction->getMovimentacoesComGarantia = $NegociaGarantiaDesenvolvimento->getMovimentacoesComGarantia($idsMovimentacao);
                        $form = $NegociaGarantiaDesenvolvimento->addNoform('setAceitaGarantia', $form);
                        $form->getElement("NEGA_IC_ACEITE")->setRequired(true);
                        $form->addDisplayGroup(array("MOFA_ID_MOVIMENTACAO", "DOCM_ID_DOCUMENTO", "DOCM_NR_DOCUMENTO", "MOFA_DS_COMPLEMENTO", "DOCM_DS_HASH_RED", "NEGA_IC_ACEITE", "NEGA_DS_JUST_ACEITE_RECUSA", "Salvar"), "DISP_NORMAL");
                    }
                }

                /**
                 * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                 * SETANDO VALOR DO ID GRUPO NA SESSION
                 */
                $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
                $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;
                $this->view->data = $NsAction->dadosCaixaSolicitacoes;
            } else {

                if ($NsAction->videoconferencia) {
                    $FormSosTbTsesServicoSolic = new Sosti_Form_SosTbTsesServicoSolic();
                    $form->addElement($FormSosTbTsesServicoSolic->getElement("SSES_IC_VIDEO_REALIZADA")->setValue('S')->setRequired(true));
                    $form->addDisplayGroup(array("MOFA_ID_MOVIMENTACAO", "DOCM_ID_DOCUMENTO", "DOCM_NR_DOCUMENTO", "MOFA_DS_COMPLEMENTO", "SSES_IC_VIDEO_REALIZADA", "DOCM_DS_HASH_RED", "Salvar"), 'DISP_VIDEOCONF');
                }

                /*                 * *******************************************
                 * Tratamento para Solicitações com garantia
                 * ******************************************* */
                if ($NsAction->existeGarantiaPorMovimentacoes) {
                    $form = $NegociaGarantiaDesenvolvimento->addNoform('setAceitaGarantia', $form);
                    $form->getElement("NEGA_IC_ACEITE")->setRequired(true);
                    if ($this->getRequest()->getParam($form->getElement("NEGA_IC_ACEITE")->getName()) == "R") {
                        $form->getElement("NEGA_DS_JUST_ACEITE_RECUSA")->setRequired(true);
                    }
                    $form->addDisplayGroup(array("MOFA_ID_MOVIMENTACAO", "DOCM_ID_DOCUMENTO", "DOCM_NR_DOCUMENTO", "MOFA_DS_COMPLEMENTO", "DOCM_DS_HASH_RED", "NEGA_IC_ACEITE", "NEGA_DS_JUST_ACEITE_RECUSA", "Salvar"), "DISP_NORMAL");
                }

                if ($form->isValid($data)) {

                    $anexos = new Zend_File_Transfer_Adapter_Http();
                    $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

                    if ($anexos->getFileName()) {
//                        try 
//                        {
//                            $data["DOCM_NR_DOCUMENTO"] = "FATURAMENTO DE SOLICITAÇÕES";
//                            $data['DOCM_ID_CONFIDENCIALIDADE'] = 0;
//
//                            $upload = new App_Multiupload_Faturamento($data);
//                            $nrDocsRed = $upload->incluirarquivos($anexos);
//                            
//                        } 
//                            catch (Exception $exc) 
//                            {
//                                $this->_helper->flashMessenger(array('message' => "CAD_ANEX 01 - Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
//                                $this->_helper->_redirector($action, $controller, 'sosti');
//                            }

                        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
//                        $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();

                        foreach ($nrDocsRed["incluidos"] as $docs) {
                            $dadosCadastro[$docs["COLUNA"]] = $docs["ID_DOCUMENTO"];
                        }

                        foreach ($nrDocsRed["existentes"] as $docs) {
                            $dadosCadastro[$docs["COLUNA"]] = $docs["ID_DOCUMENTO"];
                        }
                    }


                    /*                     * Aplica Filtros - Mantem Post */
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    /*                     * Aplica Filtros - Mantem Post */
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


                    foreach ($NsAction->dadosCaixaSolicitacoes as $d) {
                        try {
                            $dados_input = Zend_Json::decode($d);

                            /* tratamento de dados */
                            $idSolicitacao = $dados_input["SSOL_ID_DOCUMENTO"];
                            $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];

                            $dataBaixa["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            $dataBaixa["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                            $dataBaixa["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                            $dataBaixa["REPLICA_AVALIACAO_OS"] = $data["REPLICA_AVALIACAO_OS"];

                            /*                             * *tratamento de dados */

                            /* Inclusão no banco */
                            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                            $db->beginTransaction();
                            try {
                                if ($NsAction->videoconferencia) {
                                    $SosTbSsolSolicitacao->baixaSolicitacao($dataBaixa, $idSolicitacao, $nrDocsRed, false);
                                    $SosTbSsolSolicitacao->setVideoConfRealizada($dataBaixa["MOFA_ID_MOVIMENTACAO"], $data["SSES_IC_VIDEO_REALIZADA"], false);
                                } else {
                                    $retorno = $SosTbSsolSolicitacao->baixaSolicitacao($dataBaixa, $idSolicitacao, $nrDocsRed, false);
//                                    if ($perfilDSV) 
//                                    {
//                                        $dadosCadastro["PFDS_ID_SOLICITACAO"] = $idSolicitacao;
//                                        $dadosCadastro["PFDS_DH_STATUS"]      = new Zend_Db_Expr("SYSDATE");
//                                        
//                                        if ($data["NEGA_IC_ACEITE"] == 'A') 
//                                        {
//                                            $dadosCadastro["PFDS_ID_STATUS"]        = 1;
//                                            $dadosCadastro["PFDS_ID_CLASSIFICACAO"] = 2;
//                                        }
//                                        
//                                        if (($data['PFDS_ID_CLASSIFICACAO'] == 1) || ($data['PFDS_ID_CLASSIFICACAO'] == 2))
//                                        {
//                                            $dadosCadastro["PFDS_ID_STATUS"]        = 1;
//                                            $dadosCadastro["PFDS_ID_CLASSIFICACAO"] = $data["PFDS_ID_CLASSIFICACAO"];
//                                        }
//                                            else if ($data['PFDS_ID_CLASSIFICACAO'] == 17)
//                                            {
//                                                $total = $data["TOTAL"];;
//                                                $total = str_replace('.', ',', $total);
//                                                
//                                                
//                                                $dadosCadastro["PFDS_ID_STATUS"]                = 7;
//                                                $dadosCadastro["PFDS_ID_CLASSIFICACAO"]         = 17;
//                                                $dadosCadastro["MOFA_ID_FASE"]                  = "1000";
//                                                $dadosCadastro["STSA_ID_TIPO_SAT"]              = "";
//                                                $dadosCadastro["PFDS_QT_PF_BRUTO"]              = $total;
//                                                $dadosCadastro["PFDS_QT_PF_LIQUIDO"]            = $total;
//                                                $dadosCadastro["PFDS_NR_HORAS_ITEM_14"]         = $data["HORAS"];
//                                                $dadosCadastro["PFDS_NR_MINUTOS_ITEM_14"]       = $data["MINUTOS"];
//                                             } 
//                                               else
//                                               {    
//                                                   $dadosCadastro["PFDS_ID_STATUS"]        = 2;
//                                                   $dadosCadastro["PFDS_ID_CLASSIFICACAO"] = $data["PFDS_ID_CLASSIFICACAO"];
//                                               }
//                                       
//                                        $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
//                                        $incluiDados = $negocioFaturamento->salvarDadosDesenvolvedora($dadosCadastro);
//                                        
//                                    }


                                    /*                                     * *******************************************
                                     * Tratamento para Solicitações com garantia
                                     * ******************************************* */
                                    if ($NsAction->existeGarantiaPorMovimentacoes) {
                                        if (in_array($dados_input["MOFA_ID_MOVIMENTACAO"], $NsAction->getMovimentacoesComGarantia)) {
                                            $dadosArrayAceitaGarantia["NEGA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                                            $dadosArrayAceitaGarantia["NEGA_DH_ACEITE_RECUSA"] = $retorno["DATA_HORA"];
                                            $dadosArrayAceitaGarantia["NEGA_IC_ACEITE"] = $data["NEGA_IC_ACEITE"];
                                            $dadosArrayAceitaGarantia["NEGA_DS_JUST_ACEITE_RECUSA"] = $data["NEGA_DS_JUST_ACEITE_RECUSA"];

                                            $NegociaGarantiaDesenvolvimento->setAceitaGarantia($dadosArrayAceitaGarantia);
                                        }
                                    }
                                }
                                $db->commit();
                            } catch (Exception $exc) {
                                $db->rollBack();
                                throw $exc;
                            }

                            /*                             * *Inclusão no banco */

                            /* mensagem para o usuário */
                            $msg_to_user = "Solicitação nº " . $nrdocumento . " baixada!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            /*                             * *mensagem para o usuário */

                            /*                             * Envio de E-mail
                              /*
                             * Caso de solicitacoes com avaliacao automatica nao envia e-mail de baixa
                             */
                            if ($dados_input["SSOL_ID_TIPO_CAD"] != 7) {
                                /**
                                 * Envio de email de resposta
                                 */
                                /* Envio de Email se houver acompanhantes */
                                $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                                $tem_acompanhamento = $tabelaPapd->getAcompanhantesSosti($idSolicitacao);
                                if (count($tem_acompanhamento) > 0) {
                                    foreach ($tem_acompanhamento as $matricula) {
                                        $arrayDados = array(
                                            'destinatario' => $matricula["PAPD_CD_MATRICULA_INTERESSADO"]
                                        , 'solicitacao' => $dados_input['DOCM_NR_DOCUMENTO']
                                        , 'dataSolicitacao' => $dados_input['DOCM_DH_CADASTRO']
                                        , 'tipoServico' => $dados_input['SSER_DS_SERVICO']
                                        , 'descricaoBaixa' => nl2br($data["MOFA_DS_COMPLEMENTO"])
                                        , 'descricaoSolicitacao' => $dados_input["DOCM_DS_ASSUNTO_DOC"]
                                        , 'acompanhante' => 'S');
                                        try {
                                            $app_Email->baixarSolicitacao($arrayDados);
                                        } catch (Exception $exc) {
                                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                                        }
                                    }
                                }

                               $solicit = $SosTbSsolSolicitacao->fetchRow('SSOL_ID_DOCUMENTO = '.$dados_input["SSOL_ID_DOCUMENTO"])->toArray();
                                $arrayDados = array(
                                    'destinatario' => $dados_input["DOCM_CD_MATRICULA_CADASTRO"]
                                , 'solicitacao' => $dados_input['DOCM_NR_DOCUMENTO']
                                , 'dataSolicitacao' => $dados_input['DOCM_DH_CADASTRO']
                                , 'tipoServico' => $dados_input['SSER_DS_SERVICO']
                                , 'descricaoBaixa' => nl2br($data["MOFA_DS_COMPLEMENTO"])
                                , 'descricaoSolicitacao' => $dados_input["DOCM_DS_ASSUNTO_DOC"]);
                               if ($solicit["SSOL_NM_USUARIO_EXTERNO"] == null) {
                                    /* Envio de email normal */
                                    try {
                                        $app_Email->baixarSolicitacao($arrayDados);
                                    } catch (Exception $exc) {
                                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                                    }
                               } else {
                                    /**
                                     * Envia e-mail para os usuário externos
                                     */
                                    $email = new Application_Model_DbTable_EnviaEmail();
                                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                    $remetente = 'CSTI@trf1.jus.br';
                                    $corpo = "Uma solicitação foi baixada, será necessário acessar o sistema para avaliação.</p>
                                             <br />Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/".$arrayDados["solicitacao"]."\"><b>".$arrayDados["solicitacao"]."</b> </a>
                                             <br />Data da Solicitação: ".$arrayDados["dataSolicitacao"]."
                                             <br />Atendente: $userNs->nome
                                             <br />Tipo de Serviço Solicitado: ".$arrayDados["tipoServico"]."
                                             <br />Descrição da Baixa: ".$arrayDados["descricaoBaixa"]."<br />
                                             <br />Descrição da Solicitação: ".$arrayDados["descricaoSolicitacao"]."<br/>";
                                    $email->setEnviarEmailExterno($sistema, $remetente, $solicit["SSOL_DS_EMAIL_EXTERNO"], 'Baixa de Solicitação', $corpo);
                               }
                            }
                            /**
                             * Fim do envio de email
                             */
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel baixar a solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                        }
                    }


                    /* redirecionamento */
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                    /*                     * *redirecionamento */
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * SETANDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo(array_unique($NsAction->idGrupo_repd));
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $this->view->data = $NsAction->dadosCaixaSolicitacoes;

                    $form->getElement('MOFA_DS_COMPLEMENTO')->removeFilter('HtmlEntities');
                    if ($form->getElement('MOFA_DS_COMPLEMENTO')->hasErrors()) {
                        $form->getElement('MOFA_DS_COMPLEMENTO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }

                    if ($NsAction->existeGarantiaPorMovimentacoes) {
                        $form = $NegociaGarantiaDesenvolvimento->addNoform('setAceitaGarantia', $form);
                        $form->getElement("NEGA_IC_ACEITE")->setRequired(true);
                        $form->addDisplayGroup(array("MOFA_ID_MOVIMENTACAO", "DOCM_ID_DOCUMENTO", "DOCM_NR_DOCUMENTO", "MOFA_DS_COMPLEMENTO", "DOCM_DS_HASH_RED", "NEGA_IC_ACEITE", "NEGA_DS_JUST_ACEITE_RECUSA", "Salvar"), "DISP_NORMAL");
                        $form->getElement('NEGA_DS_JUST_ACEITE_RECUSA')->removeFilter('HtmlEntities');
                        if ($form->getElement('NEGA_DS_JUST_ACEITE_RECUSA')->hasErrors()) {
                            $form->getElement('NEGA_DS_JUST_ACEITE_RECUSA')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                        }
                    }
                    $form->populate($data);

                    $this->render('baixarcaixa');
                }
            }
        }
    }

    public function homologarcaixaAction()
    {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);

        /**
         * INSTANCIA DA CLASSE PARA OBTER OS DADOS DAS SOLICITAÇÕES
         */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();

        /**
         * INSTANCIA DA CLASSE PARA OBTER ACOMPANHANTES DO SOSTI
         */
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();

        /**
         * ARRAY DE IDS DOS GRUPOS DAS SOLICITAÇÕES
         */
        $arrayIdGrupo = array();
        /**
         * VARIAVEL DE SESSION
         */
        $NsAction = new Zend_Session_Namespace('NsAction');
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $form = new Sosti_Form_HomologarCaixa();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Homologar') {
                $userNs = new Zend_Session_Namespace('userNs');
                $solicspace = new Zend_Session_Namespace('solicspace');
                $solicspace->dados = $data['solicitacao'];
                $solicspace->module = (isset($data["module"]) ? $data["module"] : 'sosti');
                $solicspace->controller = $data["controller"];
                $solicspace->action = $data["action"];

                /**
                 * OBTENDO OS DADOS DE VÁRIAS SOLICITAÇÕES
                 */
                $data["solicitacoes"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesJson($data["solicitacao"]);
                foreach ($data['solicitacoes'] as $value) {
                    /**
                     * PEGANDO OS GRUPOS DAS SOLICITACOES
                     */
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    $arrayIdGrupo[] = $solicitacao['SGRS_ID_GRUPO'];
                }

                /**
                 * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                 * SETANDO VALOR DO ID GRUPO NA SESSION
                 */
                $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
                $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;

                $this->view->data = $data['solicitacao'];
                $this->view->title = "HOMOLOGAR A(S) SEGUINTE(S) SOLICITAÇÕES";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {

                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
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
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);

                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $id_doc = $dados_input['SSOL_ID_DOCUMENTO'];

                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;

                        $dataInfo["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataInfo["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $dataInfo['MOFA_ID_FASE'] = Trf1_Sosti_Definicoes::FASE_HOMOLOGAR_SOLICITACAO_TI;


                        $HomologSol = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $HomologSol->setHomologarSos($dataInfo, $dados_input["SSOL_ID_DOCUMENTO"], $nrDocsRed);


                        /**
                         * Envio de email
                         */
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                        #$destinatario = 'tr18757ps@trf1.jus.br';
                        $descricaoTipoInformação = 'Solicitação colocada em Homologação.';

                        $assunto = 'Pedido de homologação de solicitação';
                        $corpo = "$descricaoTipoInformação</p>
                                    Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                                    Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                    Atendente: " . $userNs->nome . " <br/>
                                    Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                    Descrição: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);

                        #ACOMPANHANTES ATIVOS DO SOSTI
                        $PapdParteProcDoc = $SadTbPapdParteProcDoc->getAcompanhantesAtivos($id_doc);
                        $conta = count($PapdParteProcDoc);

                        $this->view->DocmPapdParteProcDoc = $PapdParteProcDoc;

                        if ($conta != 0) {
                            for ($n = 0; $n < $conta; $n++) {
                                #DADOS DOS ACOMPANHANTES DO SOSTI
                                $nome = $PapdParteProcDoc[$n]["NOME"];
                                $matricula = $PapdParteProcDoc[$n]["PAPD_CD_MATRICULA_INTERESSADO"];

                                #PREPARA DADOS E REALIZA O ENVIO PARA ACOMPANHANTES ATIVOS
                                if (!empty($PapdParteProcDoc[$n]["PAPD_CD_MATRICULA_INTERESSADO"])) {
                                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                    $destinatario = $matricula . '@trf1.jus.br';
                                    #$destinatario = 'tr18757ps@trf1.jus.br';
                                    $remetente = 'noreply@trf1.jus.br';
                                    $assunto = 'Pedido de homologação de solicitação - Acompanhante do SOSTI';
                                    $corpo = " $descricaoTipoInformação</p>
                                                        Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                                                        Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                                        Atendente: " . $userNs->nome . " <br/>
                                                        Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                                        Descrição: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                }
                            }
                        }

                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Solicitação nº " . substr($solicitacoesEncaminhadas, 1) . " colocada em homologação!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($solicspace->action, $solicspace->controller, $solicspace->module);
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * OBTENDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = "HOMOLOGAR A(S) SEGUINTE(S) SOLICITAÇÕES";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('homologarcaixa');
                }
            }
        }
    }

    public
    function esperacaixaAction()
    {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        /**
         * Variáves de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);

        /**
         * INSTANCIA DA CLASSE PARA OBTER OS DADOS DAS SOLICITAÇÕES
         */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        /**
         * ARRAY QUE ARMAZENA OS IDS DOS GRUPOS DAS SOLICITAÇÕES
         */
        $arrayIdGrupo = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $form = new Sosti_Form_EsperaCaixa();
        $form->getElement("MOFA_DS_COMPLEMENTO")->setLabel("Descrição da Espera:");
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Espera') {
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }

                /**
                 * OBTENDO OS DADOS DE VÁRIAS SOLICITAÇÕES
                 */
                $data["solicitacoes"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesJson($data["solicitacao"]);
                foreach ($data['solicitacoes'] as $value) {
                    /**
                     * PEGANDO OS GRUPOS DAS SOLICITACOES
                     */
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    $arrayIdGrupo[] = $solicitacao['SGRS_ID_GRUPO'];
                }

                /**
                 * CRIANDO FORMULÁRIO RESPOSTA PADRÃO
                 * E SETANDO O ID GRUPO PARA A SESSION
                 */
                $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
                $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;

                $NsAction->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $NsAction->dadosCaixaSolicitacoes = $data["solicitacao"];
                $NsAction->dadosCaixa = $data;
                $this->view->title = $NsAction->dadosCaixa["title"] . " - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $form->ANEXOS->receive();
                    $nrDocsRed = null;
                    if ($form->ANEXOS->isUploaded()) {
                        try {
                            $upload = new App_Multiupload_NewMultiUpload();
                            $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                        }
                    }
                    /*                     * Aplica Filtros - Mantem Post */
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    /*                     * Aplica Filtros - Mantem Post */

                    foreach ($NsAction->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                        $dataEspera["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataEspera["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataEspera["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $idDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
                        $limite = new Application_Model_DbTable_Dual();
                        $dataSespSolicEspera["SESP_DH_LIMITE_ESP"] = $limite->setEspera();
                        $SosTbSespSolicEspera = new Application_Model_DbTable_SosTbSespSolicEspera();
                        $SosTbSespSolicEspera->esperaSolicitacao($idDocumento, $dataEspera, $dataSespSolicEspera, $nrDocsRed);
                        /**
                         * Envio de email de resposta
                         */
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                        $assunto = 'Solicitação em Espera';
                        $corpo = "Sua solicitação foi colocada em espera.</p>
                                    Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                                    Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                    Atendente: " . $userNs->nome . " <br/>
                                    Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                    Descrição da motivo: " . nl2br($dataEspera["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " colocada(s) em espera!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                } else {

                    /**
                     * CRIANDO FORMULÁRIO RESPOSTA PADRÃO
                     * E OBTENDO O ID GRUPO PARA A SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $this->view->data = $NsAction->dados;
                    $this->view->title = $NsAction->dadosCaixa["title"] . " - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";


                    $form->getElement('MOFA_DS_COMPLEMENTO')->removeFilter('HtmlEntities');
                    if ($form->getElement('MOFA_DS_COMPLEMENTO')->hasErrors()) {
                        $form->getElement('MOFA_DS_COMPLEMENTO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('esperacaixa');
                }
            }
        }
    }

    public
    function acompanharsolicitacaocaixaAction()
    {
        $data = $this->getRequest()->getPost();
        $form = new Sosti_Form_Acompanhar();
        $aNamespaceCadastrados = new Zend_Session_Namespace('aNamespaceCadastrados');
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao ();
        $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        if (array_key_exists('solic', $data) === true) {
            $data['solicitacao'] = $data['solic'];
        }
        $docValue = Zend_Json_Decoder::decode($data['solicitacao'][0]);
        $historico = $ssolSolicitacao->getHistoricoSolicitacao($docValue["SSOL_ID_DOCUMENTO"]);
        $ultimaPosicao = array_pop($historico);
        $this->view->title = $ultimaPosicao["FADM_ID_FASE"] == 1092 ? 
            'Acompanhar Ordem de Serviço (OS)' : 'Acompanhar Solicitação';
        $this->view->labelText = $ultimaPosicao["FADM_ID_FASE"] == 1092 ? 
            'Acompanhar andamento da Ordem de Serviço' : 'Acompanhar andamento da solicitação';
        if (isset($data['acao']) && $data['acao'] == 'Acompanhar') {
            $this->view->form = $form;
            $this->view->data = $data;
            /**
             * Decodifica o array multidimensionar de json para array php
             */
            $i = 0;
            $arraySols = array();
            foreach ($data["solicitacao"] as $sols) {
                $arraySols[$i] = Zend_Json_Decoder::decode($sols);
                $i++;
            }
            /**
             * Pega todos os acompanhantes de todas as solicitações
             */
            $acompanhantesCadastrados = new Application_Model_DbTable_OcsTbPmatMatricula();
            $aux = 0;
            $acp = array();
            foreach ($arraySols as $s) {
                $acp[$aux] = $acompanhantesCadastrados->getAcompanhantesSolicitacao($s["SSOL_ID_DOCUMENTO"]);
                $aux++;
            }
            /**
             * Reduz para array unidimensional
             */
            $rep = 0;
            $arrayAcp = array();
            foreach ($acp as $a1) {
                foreach ($a1 as $a2) {
                    $arrayAcp[$rep] = $a2;
                    $rep++;
                }
            }
            /**
             * Retira os nomes repetidos dos acompanhantes
             */
            $ind = 0;
            $unique = array();
            foreach ($arrayAcp as $acp) {
                $unique[$ind] = $acp["LABEL"] . '|' . $acp["VALUE"] . '|' . $acp["MATRICULA"] . '|' . $acp["ID"];
                $ind++;
            }
            $exp = 0;
            $arrayFormat = array();
            foreach (array_unique($unique) as $u) {
                $auxFormat = explode('|', $u);
                $arrayFormat[$exp]["LABEL"] = $auxFormat[0];
                $arrayFormat[$exp]["VALUE"] = $auxFormat[1];
                $arrayFormat[$exp]["MATRICULA"] = $auxFormat[2];
                $arrayFormat[$exp]["ID"] = $auxFormat[3];
                $exp++;
            }
            $aNamespaceCadastrados->cadastrados = $arrayFormat;
            /**
             * Array que contem os nomes das pessoas que foram editadas na lista 'Acompanhantes Adicionados':
             * $data['acompanhante_sosti']
             */
        } elseif (($form->isValidPartial($data)) || (isset($data['acompanhante_sosti']))) {
            /**
             * Array que contem os nomes das pessoas que já estão acompanhando solicitações:
             * $arrayCadastrados
             */
            $i = 0;
            foreach ($aNamespaceCadastrados->cadastrados as $cad) {
                $arrayCadastrados[$i] = $cad["VALUE"];
                $i++;
            }
            /**
             * Verifica se foi mandado algum nome para realizar ação de inclusão. Se tiver enviado
             * inclui acompanhantes
             */
            if (count($data['acompanhante_sosti']) > 0) {
                $sol = 0;
                foreach ($data['solicitacao'] as $solicitacao) {
                    $solicitacaoData = Zend_Json_Decoder::decode($solicitacao);
                    $solicMsg[$sol] = $solicitacaoData["DOCM_NR_DOCUMENTO"];
                    foreach ($data['acompanhante_sosti'] as $dataAcomp) {
                        $str_exp = explode(' - ', $dataAcomp);
                        $matricula = $str_exp[0];
                        try {
                            $nome = $RhCentralLotacao->getDadosPessoais($matricula);
                            $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                            $tabelaPapd->addAcompanhanteSostiCadastroSolicitacao($solicitacaoData['SSOL_ID_DOCUMENTO'], $matricula);
                        } catch (Exception $exc) {
                            $msg_to_user = "Ocorreu um erro ao solicitar acompanhamento de solicitação: <br/>" . $exc->getMessage();
                            $aNamespaceCadastrados->cadastrados = "";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($data['action'], $data['controller'], 'sosti');
                        }
                    }
                    $sol++;
                }
                $msg_to_user = "Na(s) solicitação(ões): " . implode(', ', $solicMsg) . " foi(ram) cadastrado(s) o(s) acompanhante(s): " . implode(', ', $data['acompanhante_sosti']) . ".";
            }
            /**
             * Verifica se retirou da lista: "Acompanhantes Adicionados" para realizar exclusão
             */
            $excluirNomes = array_diff($arrayCadastrados, $data['acompanhante_sosti']);
            if (count($excluirNomes) > 0) {
                $sol = 0;
                foreach ($data['solicitacao'] as $solicitacao) {
                    $solicitacaoData = Zend_Json_Decoder::decode($solicitacao);
                    $solicMsg[$sol] = $solicitacaoData["DOCM_NR_DOCUMENTO"];
                    foreach ($excluirNomes as $dataAcomp) {
                        $str_exp = explode(' - ', $dataAcomp);
                        $matricula = $str_exp[0];
                        if (in_array($dataAcomp, $excluirNomes) == true) {
                            try {
                                $nome = $RhCentralLotacao->getDadosPessoais($matricula);
                                $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                                $tabelaPapd->delAcompanhanteSostiCadastroSolicitacao($solicitacaoData['SSOL_ID_DOCUMENTO'], $matricula);
                            } catch (Exception $exc) {
                                $msg_to_user = "Ocorreu um erro ao solicitar acompanhamento de solicitação: <br/>" . $exc->getMessage();
                                $aNamespaceCadastrados->cadastrados = "";
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                                $this->_helper->_redirector($data['action'], $data['controller'], 'sosti');
                            }
                        }
                    }
                    $sol++;
                }
                $msg_to_user = "Na(s) solicitação(ões): " . implode(', ', $solicMsg) . " foi(ram) excluídos(s) o(s) acompanhante(s): " . implode(', ', array_diff($arrayCadastrados, $data['acompanhante_sosti'])) . ".";
            }
            /**
             * Verifica se todos os nomes foram removidos da lista: "Acompanhantes Adicionados"
             */
            if (($data['acompanhante_sosti'] === null) && (count($arrayCadastrados) > 0)) {
                $sol = 0;
                foreach ($data['solicitacao'] as $solicitacao) {
                    $solicitacaoData = Zend_Json_Decoder::decode($solicitacao);
                    $solicMsg[$sol] = $solicitacaoData["DOCM_NR_DOCUMENTO"];
                    foreach ($arrayCadastrados as $dataAcomp) {
                        $str_exp = explode(' - ', $dataAcomp);
                        $matricula = $str_exp[0];
                        if (in_array($dataAcomp, $arrayCadastrados) == true) {
                            try {
                                $nome = $RhCentralLotacao->getDadosPessoais($matricula);
                                $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                                $tabelaPapd->delAcompanhanteSostiCadastroSolicitacao($solicitacaoData['SSOL_ID_DOCUMENTO'], $matricula);
                            } catch (Exception $exc) {
                                $msg_to_user = "Ocorreu um erro ao solicitar acompanhamento de solicitação: <br/>" . $exc->getMessage();
                                $aNamespaceCadastrados->cadastrados = "";
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                                $this->_helper->_redirector($data['action'], $data['controller'], 'sosti');
                            }
                        }
                    }
                    $sol++;
                }
                $msg_to_user = "Na(s) solicitação(ões): " . implode(', ', $solicMsg) . " foi(ram) excluídos(s) o(s) acompanhante(s): " . implode(', ', $arrayCadastrados) . ".";
            }
            $aNamespaceCadastrados->cadastrados = "";
            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
            $this->_helper->_redirector($data['action'], $data['controller'], 'sosti');
        } else {
            $this->view->data = $data;
            $form->populate($data);
            $this->view->form = $form;
        }
    }

    /**
     * Cancelamento de solicitaçao do usuário
     *
     */
    public
    function cancelarAction()
   {

        $userNs = new Zend_Session_Namespace('userNs');
        $data = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($data);
        $solicspace = new Zend_Session_Namespace('solicspace');
        $form = new Sosti_Form_Cancelar();
        $objFicha = new Application_Model_DbTable_LfsefichaServico ();
        $objHardwareSaida = new Application_Model_DbTable_SosTbMtsaMaterialSaida();
        $objSaidaSoftware = new Application_Model_DbTable_SosTbLssaLicencaSoftSaida();

        /**
         * INSTANCIA DA CLASSE PARA OBTER OS DADOS DAS SOLICITAÇÕES
         */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        /**
         * ARRAY DE IDS DOS GRUPOS DAS SOLICITAÇÕES
         */
        $arrayIdGrupo = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Cancelar') {
                $solicspace->dados = $data['solicitacao'];

                /**
                 * OBTENDO OS DADOS DE VÁRIAS SOLICITAÇÕES
                 */
                $data["solicitacoes"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesJson($data["solicitacao"]);
                $docValue = Zend_Json_Decoder::decode($data['solicitacao'][0]);
                $historico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($docValue["SSOL_ID_DOCUMENTO"]);
                $ultimaPosicao = array_pop($historico);
                if ($ultimaPosicao["FADM_ID_FASE"] == 1092) {
                    $acaoTitulo = "Ordem(ns) de Serviço(s)";
                } else {
                    $acaoTitulo = "Solicitação(es)";
                }

                $this->view->title = "Cancelar ".$acaoTitulo;

                foreach ($data['solicitacoes'] as $value) {
                    /**
                     * PEGANDO OS GRUPOS DAS SOLICITACOES
                     */
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    $arrayIdGrupo[] = $solicitacao['SGRS_ID_GRUPO'];
                    /**
                     * Laboratorio
                     * Verifica se possui checklist e se tiver, nao podera baixar com item
                     * sem aprovacao ou recusa
                     */
                    if ($objFicha->verificaexitenciaFicha($solicitacao['SSOL_ID_DOCUMENTO'])) {
                        $verifica_s = $objHardwareSaida->verificaPendenciaHard($solicitacao['SSOL_ID_DOCUMENTO']);
                        $verifica_h = $objSaidaSoftware->verificaPendenciaSoft($solicitacao['SSOL_ID_DOCUMENTO']);
                        if (count($verifica_h) > 0 || count($verifica_s) > 0) {
                            $this->_helper->flashMessenger(array('message' => 'Não é possível realizar baixa da solicitação. A mesma possui ítens de checklist pendentes de avaliação.', 'status' => 'notice'));
                            $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                        }
                    }
                }

                /**
                 * MONTANDO FORMULÁRIO DE RESPOSTA PADRÃO
                 * STANDO VALOR DO ID GRUPO NA SESSION
                 */
                $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
                $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;

                $this->view->data = $data['solicitacao'];
                $this->view->form = $form;
                $NsAction->dadosCaixa = $data;
            } else {
                if ($form->isValid($data)) {
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesCanceladas = $solicitacoesCanceladas . ', ' . $nrdocumento;
                        $dataCancelamento["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataCancelamento["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataCancelamento["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
//                        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $SosTbSsolSolicitacao->cancelaSolicitacao($dataCancelamento, $dados_input["SSOL_ID_DOCUMENTO"], 2);

                        /* Envia email */
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $assunto = 'Cancelamento de Solicitação';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dados_input['DOCM_CD_MATRICULA_CADASTRO'] . '@trf1.jus.br';
                        $corpo = "A seguinte solicitação foi cancelada.</p>
                                                                Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $nrdocumento . "\"><b>" . $nrdocumento . "</b> </a><br/>
                                                                Data da Solicitação: " . date('d/m/Y H:i:s') . " <br/>
                                                                Responsavél: " . $userNs->nome . " <br/>
                                                                Tipo de Serviço : Cancelamento de Solicitação <br/>
                                                                Descrição do Cancelamento: " . $dataCancelamento["MOFA_DS_COMPLEMENTO"] . "<br/>";

                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    }
                    $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesCanceladas, 1) . " cancelada(s)!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                } else {
                    /**
                     * MONTANDO FORMULÁRIO DE RESPOSTA PADRÃO
                     * STANDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo(array_unique($NsAction->idGrupo_repd));
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $this->view->data = $solicspace->dados;
                    $this->view->form = $form;
                }
            }
        }
    }

    public
    function devolverAction()
    {
        //SESSAO
        $NsActionNameDev = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionNameDev);
        $userNs = new Zend_Session_Namespace('userNs');

        //FORMULARIOS
        $form = new Sosti_Form_AtendimentoClienteDevolver();

        //TABELAS
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();

        //NEGOCIO
        //BUSCA OS DADOS NECESSARIOS PARA A DEVOLUCAO DA SOLICITACAO
        $rn_devolucao = new Trf1_Sosti_Negocio_Devolucao();

        //ARRAY DE IDS DOS GRUPOS DAS SOLICITAÇÕES
        $arrayIdGrupo = array();

        //TR 2 CÓDIGO DO TRIBUNAL PARA TRAZER OS GRUPOS DE SERVIÇO DO TRF1
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $gruposDeServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao('TR', 2);
        //VARIAVEL RECEBE O GRUPO PADRAO PARA DEVOLUCAO
        $grupoAtendimentoUsuario = $gruposDeServico[0];
        $this->view->nomeGrupoServico = $grupoAtendimentoUsuario['SGRS_DS_GRUPO'];

        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getPost();

            if (isset($data['acao']) && $data['acao'] == 'Devolver') {

                //ARRAY PARA MONTAR AS MENSAGENS DE ERRO
                $mensagem = array();

                //CARREGANDO A SESSAO
                $NsAction->dadosCaixa = $data;

                //OBTENDO OS DADOS DE VÁRIAS SOLICITAÇÕES ( RESPOSTA PADRÃO )
                $data["solicitacoes"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesJson($data["solicitacao"]);
                foreach ($data['solicitacoes'] as $value) {
                    //PEGANDO OS GRUPOS DAS SOLICITACOES
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    $arrayIdGrupo[] = $solicitacao['SGRS_ID_GRUPO'];
                }

                //JOGANDO O ARRAY DE GRUPOS PARA A VIEW
                $form_resposta = new Sosti_Form_RespostaPadrao();
                $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;
                $this->view->arrayIdGrupo = array_unique($arrayIdGrupo);

                //VALIDACOES DAS SOLICITACOES
                foreach ($data["solicitacao"] as $d) {

                    $dados_input = Zend_Json::decode($d);
                    $idDocmDocumento = $dados_input['SSOL_ID_DOCUMENTO'];
                    $nr_solicitacao = $dados_input['DOCM_NR_DOCUMENTO'];
                    $dados_devolucao = $rn_devolucao->getDadosDevolucao($idDocmDocumento);

                    //VERIFICA SE A SOLICITACAO JA PASSOU PELA CAIXA DE DESTINO
                    if ($dados_devolucao) {
                        //VERIFICA SE TEM VINCULACAO NA SOLICITACAO
                        if ($dados_devolucao['VINCULADA'] != '0') {
                            $array_destinos = array();
                            $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao(array($d));
                            foreach ($SolicitacaoComFamilia as $solFamilia) {

                                $dados_familia = Zend_Json::decode($solFamilia);
                                $id_solFamilia = $dados_familia['SSOL_ID_DOCUMENTO'];
                                $dados_devolucao_fam = $rn_devolucao->getDadosDevolucao($id_solFamilia);
                                $array_destinos[$dados_devolucao_fam['CXEN_ID_CAIXA_ENTRADA']] = null;
                            }
                            //VERIFICA SE TODAS AS SOLICITACOES VINCULADAS TEM A MESMA CAIXA DE DESTINO
                            if (count($array_destinos) > 1) {
                                $mensagem['familia_invalida'][] = $nr_solicitacao;
                            }
                        }
                    } else {
                        //SE A SOLICITACAO NAO TIVER PASSADO PELA CAIXA DE DESTINO, NAO PERMIRTIR DEVOLUCAO
                        $mensagem['caixa_invalida'][] = $nr_solicitacao;
                    }
                }

                //ADICIONA AS SOLICITACOES VINCULADAS, PARA QUE TAMBEM RECEBAM A FASE DE DEVOLUCAO
                if (!is_null($todasSolicitacoes = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']))) {
                    $data['solicitacao'] = $todasSolicitacoes;
                }

                //VERIFICA SE EXISTEM MENSAGENS DE ERRO
                if (count($mensagem) > 0) {
                    //ADICIONANDO AS MENSAGENS DE ERRO
                    if (count($mensagem['familia_invalida']) > 0) {
                        $msg_to_user = 'Não foi possivel realizar a DEVOLUÇÃO. A(s) solicitação(es) nº(s): ' . implode($mensagem['familia_invalida'], ', ') . ' possuem outras solicitações vinculadas com caixas de destino diferentes. Para fazer a devolução é necessário fazer a desvinculação das mesmas. ';
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    }
                    if (count($mensagem['caixa_invalida']) > 0) {
                        $msg_to_user = 'Não foi possivel realizar a DEVOLUÇÃO. A(s) solicitação(es) nº(s): ' . implode($mensagem['caixa_invalida'], ', ') . ' não passou(ram) por uma Caixa de Atendimento ao Usuário. ';
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    }
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                } else {
                    //SE TODAS AS SOLICITACOES PASSAREM PELA PELAS VALIDACOES, ARMAZENAR OS DADOS DAS SOLICITACOES E CAIXAS PARA REALIZAR A DEVOLUCAO
                    $NsAction->dadosCaixa = $data;
                    $NsAction->dadosSolicitacao = $data['solicitacao'];
                }
            } else {

                //SUBMISSAO DO FORMULARIO
                $dadosCaixa = $NsAction->dadosCaixa;

                //VALIDA FORMULARIO
                if ($form->isValid($data)) {

                    /*                     * Aplica Filtros - Mantem Post */
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    /*                     * Aplica Filtros - Mantem Post */

                    $acompanhar = $data["ACOMPANHAR"];
                    $matricula = $userNs->matricula;
                    $numeros_docm = '';

                    foreach ($dadosCaixa["solicitacao"] as $d) {
                        //DADOS DO FORMULARIO
                        $dados_input = Zend_Json::decode($d);
                        $idDocmDocumento = $dados_input['SSOL_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];
                        $dados_devolucao = $rn_devolucao->getDadosDevolucao($idDocmDocumento);

                        //VERIFICA SE A SOLICITACAO JA PASSOU PELA CAIXA DE DESTINO
                        if (!$dados_devolucao) {
                            $msg_to_user = "Ocorreu um erro na DEVOLUÇÃO! A solicitação nº: " . $nrDocmDocumento . "  não passou por uma caixa de atendimento ao usuário diferente da atual. ";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                            $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                        } else {
                            //CARREGA OS DADOS DA DEVOLUCAO DE SOLICITACAO
                            $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                            $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                            $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $matricula;
                            $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $dados_input["MODE_ID_CAIXA_ENTRADA"];
                            $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $dados_devolucao["SGRS_SG_SECAO_LOTACAO"];
                            $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $dados_devolucao["SGRS_CD_LOTACAO"];
                            $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                            $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $dados_devolucao["CXEN_ID_CAIXA_ENTRADA"];
                            //FASE DE DEVOLUCAO DE SOLICITACAO DE TI
                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1057;
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                            $dataSsesServicoSolic["SSES_ID_SERVICO"] = $dados_devolucao["SSES_ID_SERVICO"];

                            //NIVEL ATENDIMENTO
                            $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($dados_devolucao['SGRS_ID_GRUPO']);
                            $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $NivelAtendSolic["SNAT_ID_NIVEL"];

                            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                            $db->beginTransaction();
                            try {
                                $SosTbSsolSolicitacao->encaminhaSolicitacao($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, null, $acompanhar);
                                $db->commit();
                                $numeros_docm .= $dados_input['DOCM_NR_DOCUMENTO'] . ', ';
                            } catch (Exception $e) {
                                $db->rollBack();
                                $erro = $e->getMessage();
                                $msg_to_user = "Ocorreu um erro ao devolver a(s) solicitação(es)! <br/> $erro ";
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                                $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                            }
                        }
                    }
                    $this->_helper->flashMessenger(array('message' => 'Solicitação(es) nº(s) ' . $numeros_docm . ' devolvida(s) com sucesso.', 'status' => 'success'));
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                } else {
                    $this->view->title = $NsAction->dadosCaixa["title"] . " - DEVOLUÇÃO DE SOLICITAÇÃO (ES)";
                    $this->view->data = $NsAction->dadosSolicitacao;
                    $this->view->form = $form;
                    $form->getElement('MOFA_DS_COMPLEMENTO')->removeFilter('HtmlEntities');
                    if ($form->getElement('MOFA_DS_COMPLEMENTO')->hasErrors()) {
                        $form->getElement('MOFA_DS_COMPLEMENTO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $form->populate($data);
                }
            }
        } else {
            //SE NAO TIVER POST, REDIRECIONA PARA A PAGINA INICIAL DO SOSTI
            $this->_helper->_redirector('index', 'index', 'sosti');
        }

        //VARIAVEIS DA VIEW
        //$this->view->title = $NsAction->dadosCaixa['title'] . "Desvincular Atendente";
        $this->view->data = $NsAction->dadosSolicitacao;
        $this->view->form = $form;
    }


    /*Função para desvuncular uma solicitação*/

    public
    function desvincularatendenteAction()
    {
        $data = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($data);
        $solicspace = new Zend_Session_Namespace('solicspace');
        $formAnexo = new Sosti_Form_Anexo();
        $form = new Sosti_Form_DesvincularAtendente();
        $formAnexo->anexoUnico();
        $formAnexo->submit();
        $form->addElements(array($formAnexo->getElement('ANEXOS'), $form->Salvar));
        $form->addElement($formAnexo->getElement('Salvar'));
        $objFicha = new Application_Model_DbTable_LfsefichaServico ();
        $objHardwareSaida = new Application_Model_DbTable_SosTbMtsaMaterialSaida();
        $objSaidaSoftware = new Application_Model_DbTable_SosTbLssaLicencaSoftSaida();
        $userNs = new Zend_Session_Namespace('userNs');
        $nome = $this->getRequest()->getPost('nome');
        $this->view->title = $nome . " - " . "Desvincular Atendente(s)";
        /**
         * INSTANCIA DA CLASSE PARA OBTER OS DADOS DAS SOLICITAÇÕES
         */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        // Enviando para view a matricula do usuario
        /**
         * ARRAY DE IDS DOS GRUPOS DAS SOLICITAÇÕES
         */
        $arrayIdGrupo = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Desvincular Atendente') {
                $solicspace->dados = $data['solicitacao'];

                /**
                 * OBTENDO OS DADOS DE VÁRIAS SOLICITAÇÕES
                 */
                $data["solicitacoes"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesJson($data["solicitacao"]);
                foreach ($data['solicitacoes'] as $value) {
                    /**
                     * PEGANDO OS GRUPOS DAS SOLICITACOES
                     */
                    $solicitacao = Zend_Json_Decoder::decode($value);
                    $arrayIdGrupo[] = $solicitacao['SGRS_ID_GRUPO'];

                }

                /**
                 * MONTANDO FORMULÁRIO DE RESPOSTA PADRÃO
                 * STANDO VALOR DO ID GRUPO NA SESSION
                 */
                $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
                $form_resposta->set_idGrupo(array_unique($arrayIdGrupo));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;

                $this->view->data = $data['solicitacao'];
                $this->view->title = "Desvincular Atendente(s)";
                $this->view->form = $form;
                $NsAction->dadosCaixa = $data;
            } else {
                if ($form->isValid($data)) {
                    $form->ANEXOS->receive();
                    $nrDocsRed = null;
                    if (!is_null($data["ANEXOS"])/*$form->ANEXOS->isReceived()*/) {
                        try {
                            $upload = new App_Multiupload_NewMultiUpload();
                            $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível desvincular o atendente. Não foi possível fazer o carregamento do arquivo. Se for possível tente desvincular sem anexo.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                        }
                    }
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesCanceladas = $solicitacoesCanceladas . ', ' . $nrdocumento;
                        $dataDesvincular["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataDesvincular["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataDesvincular["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
//                        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $SosTbSsolSolicitacao->desvincularAtendente($idDocmDocumento, $dataDesvincular, $dados_input["SSOL_ID_DOCUMENTO"], $nrDocsRed, 2);
                        /* Envia email */
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $assunto = 'Desvincular Atendente';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dados_input['DOCM_CD_MATRICULA_CADASTRO'] . '@trf1.jus.br';
                        $corpo = "A seguinte solicitação foi desvinculada.</p>
                                                                Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $nrdocumento . "\"><b>" . $nrdocumento . "</b> </a><br/>
                                                                Data da Solicitação: " . date('d/m/Y H:i:s') . " <br/>
                                                                Responsavél: " . $userNs->nome . " <br/>
                                                                Tipo de Serviço : Desvincular Atendente <br/>
                                                                Descrição da Desvinculaçao: " . $dataDesvincular["MOFA_DS_COMPLEMENTO"] . "<br/>";

                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    }
                    $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesCanceladas, 1) . "  tiveram o atendente desvinculado!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                } else {
                    /**
                     * MONTANDO FORMULÁRIO DE RESPOSTA PADRÃO
                     * STANDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo(array_unique($NsAction->idGrupo_repd));
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;
                    $NsAction->idGrupo_repd = array_unique($arrayIdGrupo);
                    $this->view->data = $solicspace->dados;
                    $this->view->form = $form;
                }
            }
        }

    }
    
    public function associarcadastrarAction()
    {
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $associar = new Application_Model_DbTable_SosTbAsscAssociacao();
        $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $princ = $data['solicitacao'];
            foreach ($princ as $p) {
                $decode = Zend_Json::decode($p);
                $arrayCompSol[] = Zend_Json::encode($SosTbSsolSolicitacao->getDadosSolicitacao($decode['SSOL_ID_DOCUMENTO']));
            }
            $solicitacaoPrincipal = Zend_Json::decode($princ[0]);
            $solicitacaoPrincipalData = $SosTbSsolSolicitacao->getDadosSolicitacao($solicitacaoPrincipal['SSOL_ID_DOCUMENTO']);
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            $todasSolicitacoes = array_merge($data["SOLICITACOES_ESCOLHIDAS"], $arrayCompSol);
            $primeiraEscolhida = Zend_Json::decode($data["SOLICITACOES_ESCOLHIDAS"][0]);
            $urlRedir = explode('|', $primeiraEscolhida['URLCAIXA']);
            $destinoController = $urlRedir[0];
            $destinoAction = $urlRedir[1];
            /**
             * Verifica qual é a caixa que a solicitação principal está para direcionar
             * e mostrar que foi realizada a vinculação 
             */
            $principaData = Zend_Json::decode($princ[0]);
            $arrayCaixa = Sosti_Model_DataMapper_LinkPorCaixa::enderecoPorId();
            if ($principaData["SNAS_ID_NIVEL"]) {
                $keyValue = $principaData["MODE_ID_CAIXA_ENTRADA"].'.'.$principaData["SNAS_ID_NIVEL"];
            } else {
                $keyValue = $principaData["MODE_ID_CAIXA_ENTRADA"];
            }
            try {
                $idAssociacao = $associar->getValorSequenciaAssociacao();
                foreach ($todasSolicitacoes as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
                    $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                    $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                    $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $matricula;
                    $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $dados_input["MODE_ID_CAIXA_ENTRADA"];
                    $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                    $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                    $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                    $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $dados_input["MODE_ID_CAIXA_ENTRADA"];
                    /**
                     * Fase de associacao entre solicitações
                     */
                    $dataMofaMoviFase["MOFA_ID_FASE"] = 2004;
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $matricula;
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                    $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
                    $dataSsesServicoSolic["SSES_ID_SERVICO"] = $dados_input["SSER_ID_SERVICO"];
                    /**
                     * ENVIA PARA O INDICADOR DE MENOR NÍVEL
                     */
                    $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($solicitacaoPrincipalData["SGRS_ID_GRUPO"]);
                    if ($NivelAtendSolic) {
                        $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $NivelAtendSolic["SNAT_ID_NIVEL"];
                    } else {
                        /**
                         * PARA OS GRUPOS DE SERVIÇO QUE NÃO POSSUEM NÍVEIS COMO O DA DESENVOLVIMENTO E SUSTENTAÇÃO
                         */
                        $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = '';
                    }
                    /**
                     * Lança a fase de associação entre as solicitações
                     */
                    $SosTbSsolSolicitacao->setLancarFase($idDocmDocumento, $dataMofaMoviFase);
                    /**
                     * Realiza a associação entre as solicitações
                     */
                    $associar->setAssociarSolicitacoes(
                        $idDocmDocumento, $dataMofaMoviFase, 
                        $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $idAssociacao, $nrDocsRed, $acompanhar
                    );
                    $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                    $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                }
                $db->commit();
                $this->_helper->flashMessenger(array(
                    'message' => 'Solicitações Associadas com Sucesso', 
                    'status' => 'success'
                ));
                $this->_helper->_redirector($destinoAction, $destinoController, 'sosti');
            } catch (Exception $exc) {
                $db->rollBack();
                $erro = $exc->getMessage();
                $msg_to_user = "Ocorreu um erro ao associar a solicitação! <br/> $erro ";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                $this->_helper->_redirector($destinoAction, $destinoController, 'sosti');
            }
        }
    }
    
    public function vincularentrecaixasAction()
    {
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $solicitacaoPrincipal = Zend_Json::decode($data['solicitacao']);
            $solicitacaoPrincipalData = $SosTbSsolSolicitacao->getDadosSolicitacao($solicitacaoPrincipal['SSOL_ID_DOCUMENTO']);
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            $arrayDataSol[] = Zend_Json::encode($solicitacaoPrincipalData);
            $todasSolicitacoes = array_merge($data["SOLICITACOES_ESCOLHIDAS"], $arrayDataSol);
            /**
             * Verifica qual é a caixa que a solicitação principal está para direcionar
             * e mostrar que foi realizada a vinculação 
             */
            $principaData = Zend_Json::decode($data["solicitacao"]);
            $arrayCaixa = Sosti_Model_DataMapper_LinkPorCaixa::enderecoPorId();
            if ($principaData["SNAS_ID_NIVEL"]) {
                $keyValue = $principaData["MODE_ID_CAIXA_ENTRADA"].'.'.$principaData["SNAS_ID_NIVEL"];
            } else {
                $keyValue = $principaData["MODE_ID_CAIXA_ENTRADA"];
            }
            $destino = explode('/', $arrayCaixa[$keyValue]);
            $destinoController = $destino[0];
            $destinoAction = $destino[1];
            try {
                foreach ($todasSolicitacoes as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];

                    $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                    $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                    $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $matricula;
                    $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $dados_input["MODE_ID_CAIXA_ENTRADA"];
                    $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $solicitacaoPrincipalData["MODE_SG_SECAO_UNID_DESTINO"];
                    $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $solicitacaoPrincipalData["MODE_CD_SECAO_UNID_DESTINO"];
                    $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                    $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $solicitacaoPrincipalData["MODE_ID_CAIXA_ENTRADA"];
                    /**
                     * Fase de vinculação de solicitações de TI entre caixas 2003
                     */
                    $dataMofaMoviFase["MOFA_ID_FASE"] = 2003;
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $matricula;
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                    $dataSsesServicoSolic["SSES_ID_SERVICO"] = $solicitacaoPrincipalData["SSER_ID_SERVICO"];
                    /**
                     * ENVIA PARA O INDICADOR DE MENOR NÍVEL
                     */
                    $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($solicitacaoPrincipalData["SGRS_ID_GRUPO"]);
                    if ($NivelAtendSolic) {
                        $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $NivelAtendSolic["SNAT_ID_NIVEL"];
                    } else {
                        /**
                         * PARA OS GRUPOS DE SERVIÇO QUE NÃO POSSUEM NÍVEIS COMO O DA DESENVOLVIMENTO E SUSTENTAÇÃO
                         */
                        $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = '';
                    }
                    $SosTbSsolSolicitacao->setVinculaEntreCaixas(
                        $idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, 
                        $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $solicitacaoPrincipalData['SSOL_ID_DOCUMENTO'], $nrDocsRed, $acompanhar
                    );
                    $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                    $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                }
                $db->commit();
                $this->_helper->flashMessenger(array(
                    'message' => 'Solicitações Vinculadas com Sucesso', 
                    'status' => 'success'
                ));
                $this->_helper->_redirector($destinoAction, $destinoController, 'sosti');
            } catch (Exception $exc) {
                $db->rollBack();
                $erro = $exc->getMessage();
                $msg_to_user = "Ocorreu um erro ao encaminhar a solicitação! <br/> $erro ";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                $this->_helper->_redirector($destinoAction, $destinoController, 'sosti');
            }
        }
    }
    
    public function associadosexcluirAction()
    {
        $descricaoExclusao = $this->_getParam('descricaoExclusao');
        $idExclusao = $this->_getParam('idAssociacao');
        $dadosAssociacao = new Application_Model_DbTable_SosTbAsscAssociacao();
        $associar = new Application_Model_DbTable_SosTbAsscAssociacao();
        $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $userNs = new Zend_Session_Namespace('userNs');
        /**
         * Pega todos que estão associados ao id informado
         */
        $arrayAssociacao = $dadosAssociacao->getAssociacaoSostiId($idExclusao);
        $matricula = $userNs->matricula;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            foreach ($arrayAssociacao as $arr) {
                $arrayDataSol = $SosTbSsolSolicitacao->getDadosSolicitacao($arr['DOCM_ID_DOCUMENTO']);
                $idDocmDocumento = $arr["ASSC_ID_SOSTI_ASSOCIADO"];
                $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $arrayDataSol["MODE_SG_SECAO_UNID_DESTINO"];
                $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $arrayDataSol["MODE_CD_SECAO_UNID_DESTINO"];
                $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $matricula;
                $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $arrayDataSol["MODE_ID_CAIXA_ENTRADA"];
                $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $arrayDataSol["MODE_SG_SECAO_UNID_DESTINO"];
                $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $arrayDataSol["MODE_CD_SECAO_UNID_DESTINO"];
                $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $arrayDataSol["MODE_ID_CAIXA_ENTRADA"];
                /**
                 * Fase de remover a associação entre os sostis
                 */
                $dataMofaMoviFase["MOFA_ID_FASE"] = 2005;
                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $matricula;
                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $descricaoExclusao;
                $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $arrayDataSol['MOFA_ID_MOVIMENTACAO'];
                /**
                 * ENVIA PARA O INDICADOR DE MENOR NÍVEL
                 */
                $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($arrayDataSol["SGRS_ID_GRUPO"]);
                if ($NivelAtendSolic) {
                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $arrayDataSol["SNAT_ID_NIVEL"];
                } else {
                    /**
                     * PARA OS GRUPOS DE SERVIÇO QUE NÃO POSSUEM NÍVEIS COMO O DA DESENVOLVIMENTO E SUSTENTAÇÃO
                     */
                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = '';
                }
                $SosTbSsolSolicitacao->setLancarFase($idDocmDocumento, $dataMofaMoviFase);
                if ($idExclusao == $arr["ASSC_ID_ASSOCIACAO"]) {
                    $associar->setExcluiAssociacao($idExclusao);
                }
            }
            $db->commit();
            return $this->_helper->json->sendJson(array('message' => "Associação removida!", 'status' => 'success'));
        } catch (Exception $ex) {
            $db->rollBack();
            return $this->_helper->json->sendJson(array('message' => $ex->getMessage(), 'status' => 'error'));
        }
    }

}