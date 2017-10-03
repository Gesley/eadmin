<?php

class Os_SolicitacaoController extends Zend_Controller_Action 
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
            $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
    }
	
    public function init() 
    {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
        $this->view->titleBrowser = 'e-Os - Sistema de Gerenciamento de Ordem de Serviço';
    }

    public function formAction() 
    {
        $jsonSolicitacoes = $this->getRequest()->getPost();
        $userNs = new Zend_Session_Namespace('userNs');
        $vinculos = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $this->view->title = "Cadastro de Ordem de Serviço (OS)";
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $sadDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $PerfilGestor = $ocsTbPupePerfilUnidPessoa->getPerfilGestao($userNs->matricula);
        $this->view->perfilGestor = $PerfilGestor;
        $arraySolicitacoes = array();
        foreach ($jsonSolicitacoes["solicitacao"] as $k=>$j) {
            $jd = json_decode($j);
            $arraySolicitacoes[$k] = $jd->SSOL_ID_DOCUMENTO;
        }
        /**
         * Seção de validações do formulário 
         */
        $data = $this->getRequest()->getPost();

        if ($data['acao'] === 'Criar OS') {
            $todasVinculadas = array();
            $autorizaCad = array();
            foreach ($arraySolicitacoes as $i=>$sos) {
                $vinculoOs = $vinculos->getDocPrincipal($sos);
                if ($vinculoOs[0]["DOCM_NR_DOCUMENTO"] != null) {
                    $idDocumento = $sadDocmDocumento->getDocumentoIdByNrDoc($vinculoOs[0]["DOCM_NR_DOCUMENTO"]);
                    $dadosOsVinculada = $dados->getDadosSolicitacao($idDocumento[0]["DOCM_ID_DOCUMENTO"]);
                    /**
                     * Verificar se a última fase é de solicitação avaliada
                     * positivamente.
                     */
                    if ($dadosOsVinculada["MOFA_ID_FASE"] !== 1014) {
                        $this->_helper->flashMessenger(array(
                                'message' => 'Essa solicitação já possui uma Ordem de Serviço Aberta!', 
                                'status' => 'error'
                            )
                        );
                        $this->_helper->_redirector('index', 'gestaodedemandasti', 'sosti');
                    }
                }
            }
        }
        $this->view->arraySostisEscolhidos = $data["solicitacao"];
        $arrayMescladoSol = array_merge($todasVinculadas, $arraySolicitacoes);
        $form = new Os_Form_SolicitacaoOs();
        $formOsis = new Sosti_Form_Osis();
        $formAsso = new Sosti_Form_Asso();
        $formCtss = new Sosti_Form_Ctss();
        $formAsis = new Sosti_Form_Asis();
        $formNegociaGarantia = new Sosti_Form_SosTbNegaNegociaGarantia();
        $NegociaGarantiaDesenvolvimento = new Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento();
        $formAnexo = new Sosti_Form_Anexo();
        $form->addElement($formAsis->getElement('ASIS_IC_NIVEL_CRITICIDADE'));
        $form->addElement($formCtss->getElement('CTSS_NM_CATEGORIA_SERVICO'));
        $form->addElement($formAsso->getElement('EMERGENCIAL'));
        $form->addElement($formAsso->getElement('CAUSA_PROBLEMA'));
        $form->addElement($formAsso->getElement('SOLIC_PROBLEMAS'));
        $form->addElement($formNegociaGarantia->getElement('NEGA_IC_SOLICITA'));
        $form->addElement($formNegociaGarantia->getElement('NEGA_DS_JUSTIFICATIVA_PEDIDO'));
        $form->SOLICITACOES_OS->setValue(json_encode($arrayMescladoSol));
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
            
        /**
         * VARIAVEL DA SESSION
         */
        $NsAction = new Zend_Session_Namespace('NsAction');
        /**
         * VARIAVEL DO GRUPO DE SERVIÇO PARA A RESPOSTA PADRÃO
         */
        $idGrupo_repd = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $permissao_emergencial = true;
        if ($permissao_emergencial) {
            /* MOSTRA TODOS AS OPCOES DO SELECT, INCLUINDO CHECKBOX DEMANDA EMERGENCIAL */
        } else {
            $select_oco = $formOsis->OSIS_NM_OCORRENCIA;
            $select_oco->removeMultiOption('2'); /* DEMANDA EMERGENCIAL */
            $form->removeElement('EMERGENCIAL');
            $form->removeElement('CAUSA_PROBLEMA');
        }
        /**
         * Tratamento para retirar o grupo de serviço Gestão de Infra da lista
         */
        $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
        $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
        $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
        foreach ($arr_sgrs_id_grupo as $value) {
            $value_option = Zend_Json::decode($value);
            if (in_array($value_option["SGRS_ID_GRUPO"], array(119, 120, 121))) {
                $sgrs_id_grupo->removeMultiOption($value);
            }
        }
        $Dual = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $Dual->sysdateDb();
        $this->view->jsonSolicitacoes = $arraySolicitacoes;
        $this->view->form = $form;
    }

    public function saveAction() 
    {
        $data = $this->getRequest()->getPost();
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $this->view->title = "Cadastro de Ordem de Serviço (OS)";
        $userNs = new Zend_Session_Namespace('userNs');
        
        $form = new Os_Form_SolicitacaoOs();
        $formOsis = new Sosti_Form_Osis();
        $formAsso = new Sosti_Form_Asso();
        $formCtss = new Sosti_Form_Ctss();
        $formAsis = new Sosti_Form_Asis();
        $formNegociaGarantia = new Sosti_Form_SosTbNegaNegociaGarantia();
        $NegociaGarantiaDesenvolvimento = new Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento();
        $formAnexo = new Sosti_Form_Anexo();
        $form->addElement($formAsis->getElement('ASIS_IC_NIVEL_CRITICIDADE'));
        $form->addElement($formCtss->getElement('CTSS_NM_CATEGORIA_SERVICO'));
        $form->addElement($formAsso->getElement('EMERGENCIAL'));
        $form->addElement($formAsso->getElement('CAUSA_PROBLEMA'));
        $form->addElement($formAsso->getElement('SOLIC_PROBLEMAS'));
        $form->addElement($formNegociaGarantia->getElement('NEGA_IC_SOLICITA'));
        $form->addElement($formNegociaGarantia->getElement('NEGA_DS_JUSTIFICATIVA_PEDIDO'));
        $form->SOLICITACOES_OS->setValue(json_encode($arrayMescladoSol));
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        
        $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $solicitacaoOs = new Os_Model_DataMapper_Solicitacao();
        $Dual = new Application_Model_DbTable_Dual();
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
                        $msg_to_user = "Não foi possível cadastrar sua Ordem de Serviço. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua Ordem de Serviço sem anexo.";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                        $this->_helper->_redirector('index', 'gestaodedemandasti', 'sosti');
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

                $dataMofaMoviFase["MOFA_ID_FASE"] = 1092; //ABERTURA DE ORDEM DE SERVIÇO
                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Ordem de Serviço.";

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
                /** Pega todos os anexos que foram importados do Sostis */
                $contIAnx = count($nrDocsRed['incluidos']);
                if (count($data["importar_anexos"]) > 0) {
                    /**
                     * Verificar o retorno do array com os documentos incluídos 
                     * no RED para acrescentar as demais posições dos documentos 
                     * que foram importados.
                     */
                    foreach ($data["importar_anexos"] as $ia) {
                        $arrayAnx = explode("|;", $ia);
                        $tpExt = explode('.', $arrayAnx[1]);
                        $idTpExt = new Application_Model_DbTable_SadTbTpexTipoExtensao();
                        $tp = $idTpExt->fetchRow("TPEX_DS_TP_EXTENSAO = '".end($tpExt)."'");
                        $nrDocsRed["incluidos"][$contIAnx]["ID_DOCUMENTO"] = $arrayAnx[0];
                        $nrDocsRed["incluidos"][$contIAnx]["NOME"] = $arrayAnx[1];
                        $nrDocsRed["incluidos"][$contIAnx]["ANEX_ID_TP_EXTENSAO"] = $tp['TPEX_ID_TP_EXTENSAO'];
                        $contIAnx++;
                    }
                }
                /**
                 * Realiza as operações de inserção no banco de dados para cadastrar OS.
                 */
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {
                if (!$nrDocsRed["existentes"]) {
                    if (!$nrDocsRed["incluidos"]) {
                        try {
                            $dataAcompanhantes = $data['acompanhante_sosti'];
                            $dataPorOrdemDe = $data['PORORDEMDE'];
                            $dataRetorno = $solicitacaoOs->setCadastrarSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed, $dataAcompanhantes, $dataPorOrdemDe);
                            $DocmDocumento = $ssolSolicitacao->getDadosSolicitacao($dataRetorno["DOCM_ID_DOCUMENTO"]);

                            $msg_to_user = "Ordem de Serviço nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua Ordem de Serviço: " . $exc->getMessage();
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector('index', 'gestaodedemandasti', 'sosti');
                        }
                        } else {
                            try {
                                $dataAcompanhantes = $data['acompanhante_sosti'];
                                $dataPorOrdemDe = $data['PORORDEMDE'];
                                $dataRetorno = $solicitacaoOs->setCadastrarSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed, $dataAcompanhantes, $dataPorOrdemDe);
                                $DocmDocumento = $ssolSolicitacao->getDadosSolicitacao($dataRetorno["DOCM_ID_DOCUMENTO"]);

                                $msg_to_user = "Ordem de Serviço nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possível cadastrar sua Ordem de Serviço.: " . $exc->getMessage();
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                                $this->_helper->_redirector('index', 'gestaodedemandasti', 'sosti');
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
                     * Envia o tipo de vinculação = 7 para a model lançar a vinculação
                     */
                    $arrayRows = array();
                    foreach (json_decode($data['SOLICITACOES_OS']) as $k=>$rows) {
                        $ultimaFase = $ssolSolicitacao->getHistoricoSolicitacao($rows);
                        $arrayRows[$k] = $ultimaFase[0];
                    }
                    $solicitacaoOs->setVincularSolicitacao(
                            $arrayRows, 
                            $dataRetorno["DOCM_ID_DOCUMENTO"], 
                            'Vinculação para criação de OS.',
                            7
                        );
                    /**
                     * Realiza o cadastro nas tabelas de garantia
                     */
                    $tabelaAsis = new Application_Model_DbTable_SosTbAsisAtendSistema();

                    $id_categoria_servico = $data['CTSS_NM_CATEGORIA_SERVICO'];
                    $ic_nivel_criticidade = $data['ASIS_IC_NIVEL_CRITICIDADE'];

                    if ($id_categoria_servico == '2') {
                        $data['EMERGENCIAL'] = 'S';
                    }
                    $idAsis = $tabelaAsis->getIdAtendimentoSistema($data['EMERGENCIAL'], $id_categoria_servico, $ic_nivel_criticidade);

                    /* Inclusão na tabela Atendimento Sistema */
                    $dataAsso['ASSO_ID_ATENDIMENTO_SISTEMAS'] = $idAsis['ASIS_ID_ATENDIMENTO_SISTEMA'];
                    $dataAsso['ASSO_ID_MOVIMENTACAO'] = $DocmDocumento["MOVI_ID_MOVIMENTACAO"];

                    if ($data['EMERGENCIAL'] == 'S') {
                        $dataAsso['ASSO_IC_ATENDIMENTO_EMERGENCIA'] = 'S';
                    } else if ($data['EMERGENCIAL']) {
                        $dataAsso['ASSO_IC_ATENDIMENTO_EMERGENCIA'] = 'N';
                    }

                    if ($data['CAUSA_PROBLEMA'] == 1) {
                        /* SITUAÇÃO CAUSA */
                        $dataAsso['ASSO_IC_SOLUCAO_CAUSA_PROBLEMA'] = 'S';
                        $dataAsso['ASSO_IC_SOLUCAO_PROBLEMA'] = 'N';
                    } else {
                        /* SITUAÇÃO PROBLEMA */
                        $dataAsso['ASSO_IC_SOLUCAO_CAUSA_PROBLEMA'] = 'N';
                        $dataAsso['ASSO_IC_SOLUCAO_PROBLEMA'] = 'S';
                    }
                    $dataAsso['ASSO_ID_MOVIMENTACAO'] = $dataRetorno["MOFA_ID_MOVIMENTACAO"];
                    $tabelaAsso = new Application_Model_DbTable_SosTbAssoAtendSistemSolic();
                    $rowTabelaAsso = $tabelaAsso->createRow($dataAsso);
                    $rowTabelaAsso->save();

                    /* Vinculacao do tipo Causa Problema */
                    $arr_solic_prob = explode(',', $data['SOLIC_PROBLEMAS']);
                    $dual = new Application_Model_DbTable_Dual();
                    foreach ($arr_solic_prob as $nr_solic) {
                        if ($nr_solic != "") {
                            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                            $tabelaVidc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                            $dadosSolic = $tabelaSadTbDocmDocumento->fetchRow(array('DOCM_NR_DOCUMENTO = ?' => $nr_solic));
                            $dataVidc['VIDC_ID_DOC_PRINCIPAL'] = $dados_input['SSOL_ID_DOCUMENTO'];
                            $dataVidc['VIDC_ID_DOC_VINCULADO'] = $dadosSolic['DOCM_ID_DOCUMENTO'];
                            $dataVidc['VIDC_ID_TP_VINCULACAO'] = 6; /* VINCULACAO CAUSA PROBLEMA */
                            $dataVidc['VIDC_DH_VINCULACAO'] = $dual->sysdate();
                            $dataVidc['VIDC_CD_MATR_VINCULACAO'] = $userNs->matricula;
                            $rowVidc = $tabelaVidc->createRow($dataVidc);
                            $rowVidc->save();
                        }
                    }
                    $data["NEGA_IC_SOLICITA"]  = $data['SSOL_FLAG_GARANTIA'] == 1 ? "S" : "N";
                    if ($data["NEGA_IC_SOLICITA"] == "S") {
                        $dadosArraySolicitaGarantia["NEGA_ID_MOVIMENTACAO"] = $dataRetorno["MOFA_ID_MOVIMENTACAO"];
                        $dadosArraySolicitaGarantia["NEGA_DH_SOLIC_GARANTIA"] = $dual->sysdate();
                        $dadosArraySolicitaGarantia["NEGA_DS_JUSTIFICATIVA_PEDIDO"] = $data["SSOL_GARANTIA_OBSERVACAO"];
                        $NegociaGarantiaDesenvolvimento->setSolicitaGarantia($dadosArraySolicitaGarantia);
                    }
                    $db->commit();
                    /** Atualiza quando é necessário gravar CLOB */
                    App_Clob::saveClob(
                        'DOCM_DS_ASSUNTO_DOC',
                        'SAD_TB_DOCM_DOCUMENTO',
                        "DOCM_ID_DOCUMENTO = ".$dataRetorno["DOCM_ID_DOCUMENTO"],
                        $dataDocmDocumento['DOCM_DS_ASSUNTO_DOC']
                    );
                } catch (Exception $exc) {
                    $db->rollBack();
                    throw $exc;
                }

                /**
                 * Email de Confirmação ao cadastrante
                 */
                $email = new Application_Model_DbTable_EnviaEmail();
                $sistema = 'e-Os - Sistema de Gerenciamento de Ordem de Serviço';
                $remetente = 'noreply@trf1.jus.br';
                $destinatario = $userNs->matricula . '@trf1.jus.br';
                $assunto = 'Cadastro de Ordem de Serviço';
                $corpo = "Cadastro de Ordem de Serviço efetuado com sucesso</p>
                          Número da Ordem de Serviço: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                          Data da Ordem de Serviço: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Ordem de Serviço: " . nl2br($DocmDocumento["DOCM_DS_ASSUNTO_DOC"]) . "<br/>
                          Observação da Ordem de Serviço: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                try {
                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para Ordem de Serviço: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                }
                /**
                 * Caso exista acompanhanate
                 */
                if (($data['acompanhante_sosti']) && ($data['acompanhante_sosti'] != NULL)) {
                        try {
                    $emails = array();
                    foreach ($data['acompanhante_sosti'] as $acompanhante) {
                        $matricula = explode("-", $acompanhante);
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Os - Sistema de Gerenciamento de Ordem de Serviço';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $matricula[0] . '@trf1.jus.br';
                        $assunto = 'Cadastro de Ordem de Serviço - Acompanhamento de Baixa';
                        $corpo = "Prezado Usuário(a),<br/>
                          Você foi cadastrado(a) como Acompanhante de Baixa na Ordem de Serviço descrita abaixo.<br/>
                          Cadastro de Ordem de Serviço efetuado com sucesso</p>
                          Número da Ordem de Serviço: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dataRetorno['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>
                          Data da Ordem de Serviço: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Ordem de Serviço: " . nl2br($DocmDocumento["DOCM_DS_ASSUNTO_DOC"]) . "<br/>
                          Observação da Ordem de Serviço: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        } 
                        }catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                        }
                        $this->_helper->_redirector('index', 'gestaodedemandasti', 'sosti');
                }
                $this->_helper->_redirector('index', 'gestaodedemandasti', 'sosti');
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
    
    public function trocarservicoAction()
    {
        /**
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        $solicspace = new Zend_Session_Namespace('solicspace');
        $userNs = new Zend_Session_Namespace('userNs');
        $servicoOs = new Os_Model_DataMapper_ServicoSolicitacao();
//        $form = new Sosti_Form_TrocarServico();
//        $formAnexo = new Sosti_Form_Anexo();
        
                $form = new Os_Form_SolicitacaoOs();
        $formAsso = new Sosti_Form_Asso();
        $formCtss = new Sosti_Form_Ctss();
        $formAsis = new Sosti_Form_Asis();
        $formNegociaGarantia = new Sosti_Form_SosTbNegaNegociaGarantia();
        $NegociaGarantiaDesenvolvimento = new Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento();
        $formAnexo = new Sosti_Form_Anexo();
        $form->addElement($formAsis->getElement('ASIS_IC_NIVEL_CRITICIDADE'));
        $form->addElement($formCtss->getElement('CTSS_NM_CATEGORIA_SERVICO'));
        $form->addElement($formAsso->getElement('EMERGENCIAL'));
        $form->addElement($formAsso->getElement('CAUSA_PROBLEMA'));
        $form->addElement($formAsso->getElement('SOLIC_PROBLEMAS'));
        $form->addElement($formNegociaGarantia->getElement('NEGA_IC_SOLICITA'));
        $form->addElement($formNegociaGarantia->getElement('NEGA_DS_JUSTIFICATIVA_PEDIDO'));
        $form->SOLICITACOES_OS->setValue(json_encode($arrayMescladoSol));
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        
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
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Trocar Categoria de Serviço') {
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
                    $idGrupo = $servicos[0]['SSER_ID_GRUPO'];
                    $idGrupo_repd[] = $idGrupo;
                    if ($idGrupo_aux) {
                        if ($idGrupo != $idGrupo_aux) {
                            $msg_to_user = "Não é possível realizar TROCA DE CATEGIRA DE SERVIÇO com OS de serviços de grupos de serviço diferentes";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                            $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti', array('filtro' => '1'));
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

                if ($form->isValidPartial($data)) {
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    $form->ANEXOS->receive();
                    $nrDocsRed = null;
                    if (!is_null($data["ANEXOS"])/*$form->ANEXOS->isReceived()*/) {
                        try {
                            $upload = new App_Multiupload_NewMultiUpload();
                            $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível fazer o carregamento do arquivo. Se for possível tente trocar a categoria de serviço sem anexo.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti', array('filtro' => '1'));
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
                        $allParams = $this->_getAllParams();
//                        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();

                        $servicoOs->setTrocaServicoOs($dados_input["SSOL_ID_DOCUMENTO"], $dataSsolSolicitacao, $dataMofaMoviFase, $dataSsesServicoSolic, $nrDocsRed, $allParams);
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
                                    Motivo da troca de serviço: " . nl2br($allParams["DOCM_DS_ASSUNTO_DOC"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Ordem(es) de Serviço(s) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " com a categoria de serviço trocada!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti', array('filtro' => '1'));
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * OBTENDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

//                    $form->getElement('MOFA_DS_COMPLEMENTO')->removeFilter('HtmlEntities');
//                    if ($form->getElement('MOFA_DS_COMPLEMENTO')->hasErrors()) {
//                        $form->getElement('MOFA_DS_COMPLEMENTO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
//                    }
                    $form->populate($data);
                }
            }
        }
        $this->view->form = $form;
    }
    
    public function importaranexoAction()
    {
        $arraySolicit = $this->getRequest()->getPost();
        $anex =  Os_Model_DataMapper_AnexoSolicitacao::listAll(
            Zend_Json::decode($arraySolicit['SOLICITACOES_OS'])
        );
        $this->view->arrayAnexo = $anex;
    }

}