<?php

class Sisad_FormulariominutaController extends Zend_Controller_Action
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
        $this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
        // Informação
        $this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction()
    {
        $this->view->title = "Formulário de Minuta";
    }

    public function formAction()
    {
        $form = new Sisad_Form_Formulariominuta();
//      $table  = new Sisad_Model_DataMapper_Documento();
        $this->view->form = $form;
        $this->view->formParte = new Sisad_Form_Partes();
        $this->view->formSisadAnexo = new Sisad_Form_Anexo();
        $userNamespace = new Zend_Session_Namespace('userNs');
        $this->view->title = "Formulário de Minuta";

        $Ns_Caixaminuta_reutilizar = new Zend_Session_Namespace('Ns_Caixaminuta_reutilizar');
        $dadosReutilizar = $Ns_Caixaminuta_reutilizar->data_post_caixa;

        if ((isset($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']) && !empty($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']))) {
            $this->view->DOCM_ID_DOCUMENTO = $dadosReutilizar['DOCM_ID_DOCUMENTO'];
            $this->view->DOCM_NR_DOCUMENTO_RED = $dadosReutilizar['DOCM_NR_DOCUMENTO_RED'];
            $this->view->DOCM_ID_TP_EXTENSAO = $dadosReutilizar['tipoExtensao'];
        } else {
            $this->view->DOCM_ID_DOCUMENTO = "";
            $this->view->DOCM_NR_DOCUMENTO_RED = "";
            $this->view->DOCM_ID_TP_EXTENSAO = "";
        }
    }

    public function ajaxunidadeAction()
    {
        $unidade = $this->_getParam('term', '');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $secao = $userNamespace->siglasecao;
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade, $secao);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxassuntodocmAction()
    {
        $assunto_p = $this->_getParam('term', '');
        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        $assunto = $mapperPctt->getPCTTAjax($assunto_p);

        $fim = count($assunto);
        for ($i = 0; $i < $fim; $i++) {
            $assunto[$i] = array_change_key_case($assunto[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($assunto);
    }

    public function saveAction()
    {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);

        /**
         * Forms
         */
        $form = new Sisad_Form_Formulariominuta();
        $this->view->formParte = new Sisad_Form_Partes();

        /*
         * Titulo do formulario
         */
         $this->view->title = "Formulário de Minuta";
        /**
         * Models
         */
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        /**
         *  Classes
         */
        $Sisad_anexo = new App_Sisad_Anexo();
        /**
         * Namespaces
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaminuta_reutilizar = new Zend_Session_Namespace('Ns_Caixaminuta_reutilizar');
        $dadosReutilizar = $Ns_Caixaminuta_reutilizar->data_post_caixa;

        /**
         * Inicialia Variáveis
         */
        $dataPartePessoa = array();
        $dataParteLotacao = array();
        $dataPartePessExterna = array();
        $dataPartePessJur = array();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                
                //Alterara a maneira de encaminhar para a pessoa - conforme sosti 2013010001152011520160000224
                $unidade = $OcsTbPmatMatricula->getNomeUnidade($data['encaminhar']);
                $siglaSecaoDestino = $unidade[0]["LOTA_SIGLA_SECAO"];
                $codCaixaDestino = $unidade[0]["LOTA_COD_LOTACAO"];
                $siglaCaixa = $unidade[0]['LOTA_SIGLA_LOTACAO'];


                $dataMoviMovimentacao = array('MOVI_SG_SECAO_UNID_ORIGEM' => $siglaSecaoDestino,
                    'MOVI_CD_SECAO_UNID_ORIGEM' => $codCaixaDestino,
                    'MOVI_CD_MATR_ENCAMINHADOR' => $userNs->matricula);
                $dataModeMoviDestinatario = array('MODE_SG_SECAO_UNID_DESTINO' => $siglaSecaoDestino,
                    'MODE_CD_SECAO_UNID_DESTINO' => $codCaixaDestino,
                    'MODE_IC_RESPONSAVEL' => 'N');
                $dataMofaMoviFase = array('MOFA_ID_FASE' => 1010, /* ENCAMINHAMENTO SISAD */
                    'MOFA_CD_MATRICULA' => $userNs->matricula,
                    'MOFA_DS_COMPLEMENTO' => 'Documento cadastrado e enviado para a Caixa de Minutas');
                $dataModpDestinoPessoa = array('MODP_CD_MAT_PESSOA_DESTINO' => $data['encaminhar']);

                $dataPartePessoa = array();
                if (!empty($data['partes_pessoa_trf'])) {
                    $dataPartePessoa = array_unique($data['partes_pessoa_trf']);
                }
                $envia_documento = 'E'; //Documento será enviado para a unidade emissora
                /**
                 * Adapatação
                 * Setando os campos da tabela que não são obtidos no formulario
                 */
                unset($data["DOCM_ID_DOCUMENTO"]);
//               PÚBLICO -  - TRF1
                $data["DOCM_ID_CONFIDENCIALIDADE"] = 1;
                $data['DOCM_ID_TIPO_DOC'] = 230; //DSV
                $data["DOCM_DH_CADASTRO"] = new Zend_Db_Expr("SYSDATE");
                $data["DOCM_CD_MATRICULA_CADASTRO"] = $userNs->matricula;

                $aux_DOCM_CD_LOTACAO_GERADORA = $data['DOCM_CD_LOTACAO_GERADORA'];
                $docm_cd_lotacao_geradora_array = explode(' - ', $data['DOCM_CD_LOTACAO_GERADORA']);
                $data['DOCM_SG_SECAO_GERADORA'] = $docm_cd_lotacao_geradora_array[3];
                $data['DOCM_CD_LOTACAO_GERADORA'] = $docm_cd_lotacao_geradora_array[2];

                $aux_DOCM_CD_LOTACAO_REDATORA = $data['DOCM_CD_LOTACAO_REDATORA'];
                $docm_cd_lotacao_redatora_array = explode(' - ', $data['DOCM_CD_LOTACAO_REDATORA']);
                $data['DOCM_SG_SECAO_REDATORA'] = $docm_cd_lotacao_redatora_array[3];
                $data['DOCM_CD_LOTACAO_REDATORA'] = $docm_cd_lotacao_redatora_array[2];

                if (isset($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']) && !empty($dadosReutilizar['DOCM_NR_DOCUMENTO_RED'])) {
                    $data["DOCM_NR_DOCUMENTO_RED"] = $dadosReutilizar['DOCM_NR_DOCUMENTO_RED'];
                } else {
                    unset($data["DOCM_NR_DOCUMENTO_RED"]);
                }

                $data["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_ID_TIPO_DOC']);
                $data["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_CD_LOTACAO_GERADORA'], $data['DOCM_ID_TIPO_DOC'], $data["DOCM_NR_SEQUENCIAL_DOC"]);
                $data["DOCM_DS_ASSUNTO_DOC"] = $data['DOCM_DS_ASSUNTO_DOC'];

                /**
                 * @todo verificar
                 */
                //$data["DOCM_ID_PESSOA_TEMPORARIA"] = 1; //???????????

                $email = new Application_Model_DbTable_EnviaEmail();
                $sistema = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
                $remetente = 'noreply@trf1.jus.br';
                $destinatario = $dataModpDestinoPessoa["MODP_CD_MAT_PESSOA_DESTINO"] . '@trf1.jus.br';
                $assunto = 'Encaminhamento de Documento';
                $corpo = "Foi encaminhado um documento para sua caixa de minutas.</p>
                          Número do Documento: " . $data["DOCM_NR_DOCUMENTO"] . " <br/>
                          Encaminhado por: " . $userNs->nome . " <br/>
                          Tipo do Documento: MINUTA <br/> 
                          Descrição do Encaminhamento: " . nl2br($data["DOCM_DS_ASSUNTO_DOC"]) . "<br/>";

                /**
                 * Validação
                 * Verifica presença de anexos sem um documento principal
                 */
                $anexos = new Zend_File_Transfer_Adapter_Http();
                $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
                if ((!$form->DOCM_DS_HASH_RED->isUploaded() && $data["TEXTO_HTML"] == "" && $dadosReutilizar['DOCM_NR_DOCUMENTO_RED'] == "") &&
                        $anexos->getFileName()) {
                    $msg_to_user = "Não é possivel anexar documentos sem um documento principal";
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('form');
                    return;
                }

                if (!$form->DOCM_DS_HASH_RED->isUploaded() &&
                        (isset($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']) && !empty($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']))) {
                    /**
                     * se o usuário não fizer o upload do arquivo insere somente nas tabelas
                     */
                    if ((isset($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']) && !empty($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']))) {
                        $data["DOCM_ID_TP_EXTENSAO"] = $dadosReutilizar['tipoExtensao'];
                        $this->view->DOCM_NR_DOCUMENTO_RED;
                        $this->view->DOCM_ID_TP_EXTENSAO;
                        unset($Ns_Caixaminuta_reutilizar->data_post_caixa);
                    }

                    try {
                        $row = $tabelaSadTbDocmDocumento->createRow($data);
                        $idDocumento = $row->save();
                        $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento")->toArray();
                        $dataDocumento['DOCM_ID_DOCUMENTO'] = $idDocumento;

                        //     Zend_Debug::dump($rowDocmDocumento); exit;
                        /*
                         * Opcao de enviar o documento para Caixa Entrada da Unidade
                         */

                        if ($envia_documento == 'E') {
                            $encaCaixaUnidade = $SadTbModoMoviDocumento->encaminhaMinuta($idDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestinoPessoa);
                        }

                        /* Cadastra partes do documento */
                        if (!empty($dataPartePessoa) || !empty($dataParteLotacao) || !empty($dataPartePessExterna) || !empty($dataPartePessJur)) {
                            if (!empty($rowDocmDocumento))
                                $cadastroPartes = $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $dataDocumento, array());
                        }
                        $msg_to_user = "Documento cadastrado. Número do documento: " . $rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        $this->view->msg_to_user = $msg_to_user;
                        $this->_helper->redirector("form");
                    } catch (Exception $e) {
                        $erro = $e->getMessage();
                        $msg_to_user = "Ocorreu erro no cadastro dos metadados. <br> Erro: $erro.";
                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                        $this->view->form = $form;
                        $this->render('form');
                    }
                    try {
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $data["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                    }
                } elseif ($form->DOCM_DS_HASH_RED->isUploaded() || $data["TEXTO_HTML"]) {
                    if ($data['RADIO_TIPO_ARQUIVO'] == 'D') {
                        $form->DOCM_DS_HASH_RED->receive();
                    }

                    if ($form->DOCM_DS_HASH_RED->isReceived() || $data['RADIO_TIPO_ARQUIVO'] == 'E') {
                        /**
                         * o documento foi salvo na pasta temp
                         */
                        /*
                         * Renomeando o arquivo gravado no servidor
                         */
                        if ($data['RADIO_TIPO_ARQUIVO'] == 'D') {
                            $userfile = $form->DOCM_DS_HASH_RED->getFileName(); /* caminho completo do arquivo gravado no servidor */
                            $extensao = strtolower(end(explode('.', $userfile)));
                            $codTipoExtensao = $mapperDocumento->retornaCodExtensao($extensao);
                            $data["DOCM_ID_TP_EXTENSAO"] = $codTipoExtensao[0]['TPEX_ID_TP_EXTENSAO'];

                            $tempDirectory = "temp";
                            $userfilename = substr($userfile, strrpos($userfile, $tempDirectory) + strlen($tempDirectory) + 1);
                            $fullFilePath = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR . 'SISADTEMPDOC' . date("dmYHisu") . $userfilename;
                            $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
                            $filterFileRename->filter($userfile); /* Renomeando a partir do caminho completo do arquivo no servidor */
                        } else {
                            $arquivo = $mapperDocumento->criarArquivo($data, 'html');
                            $data["DOCM_ID_TP_EXTENSAO"] = 4;
                        }

                        /**
                         * o documento foi renomeado na pasta temp
                         */
                        /* Inclusão do arquivo no RED */
                        $parametros = new Services_Red_Minuta_Parametros_Incluir();
                        $parametros->login = $userNs->matricula;
                        $parametros->ip = Services_Red::IP_MAQUINA_EADMIN;

                        $metadados = new Services_Red_Minuta_Metadados_Incluir();
                        $metadados->dataHoraProducaoConteudo = date('d/m/Y H:i:s');
                        $metadados->descricaoTituloDocumento = $data["DOCM_NR_DOCUMENTO"];
                        $metadados->numeroTipoSigilo = $data['DOCM_ID_CONFIDENCIALIDADE'];
                        $metadados->numeroPasta = '';
                        $metadados->nomeSistemaIntrodutor = Services_Red::NOME_SISTEMA_EADMIN;
                        $metadados->ipMaquinaResponsavelIntervencao = Services_Red::IP_MAQUINA_EADMIN;
                        $metadados->secaoOrigemDocumento = "0100";
                        $metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
                        $metadados->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
                        $metadados->nomeMaquinaResponsavelIntervensao = Services_Red::NOME_MAQUINA_EADMIN;
                        $metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
                        $metadados->numeroDocumento = "";
                        $metadados->secaoDestinoIdSecao = "0100";

                        $red = new Services_Red_Minuta_Incluir();

                        $red->debug = false;
                        $red->temp = APPLICATION_PATH . '/../temp';

                        if ($data['RADIO_TIPO_ARQUIVO'] == 'D') {
                            $file = $fullFilePath; /* caminho completo do arquivo renomeado no servidor */
                        } else {
                            $file = $arquivo;
                        }

                        $retornoIncluir_red = $red->incluir($parametros, $metadados, $file);
                        $input_files = $anexos->getFileInfo();

                        if (is_array($retornoIncluir_red)) {
                            if ($anexos->getFileName() /*&& $input_files["ANEXOS_0_"]["name"]*/ != NULL) {
                                try {
                                    $upload = new App_Multiupload_NewMultiUpload();
                                    $nrDocsRed = $upload->incluirarquivos($anexos);
                                } catch (Exception $exc) {
                                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                                }
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
                                        $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red['numeroDocumento'];
                                        $row = $tabelaSadTbDocmDocumento->createRow($data);
                                        $idDocumento = $row->save();
                                        if ($envia_documento == 'E') {
                                            $encaCaixaUnidade = $SadTbModoMoviDocumento->encaminhaMinuta($idDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestinoPessoa);
                                        }
                                        $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                                        $dataDocumento['DOCM_ID_DOCUMENTO'] = $idDocumento;
                                        $dataDocumento['replicaVistas'] = $data['REPLICA_VISTAS'];

                                        /* Cadastra partes do documento */
                                        if (!empty($dataPartePessoa) || !empty($dataParteLotacao) || !empty($dataPartePessExterna) || !empty($dataPartePessJur)) {
                                            if (!empty($rowDocmDocumento))
                                                $cadastroPartes = $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $dataDocumento, array());
                                        }

                                        $msg_to_user = "Documento cadastrado. Número do documento: " . $rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));

                                        try {
                                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                        } catch (Exception $exc) {
                                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $data["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                                        }
                                        $this->_helper->redirector("form");
                                    } catch (Exception $exc) {
                                        $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.";
                                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user </div>";
                                        $this->view->flashMessagesView = $msg_to_user;
                                        $this->view->form = $form;
                                        $this->render('form');
                                    }
                                } else {
                                    try {
                                        $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red['numeroDocumento'];
                                        $row = $tabelaSadTbDocmDocumento->createRow($data);
                                        $idDocumento = $row->save();
                                        if ($envia_documento == 'E') {
                                            $encaCaixaUnidade = $SadTbModoMoviDocumento->encaminhaMinuta($idDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestinoPessoa, $nrDocsRed);
                                        }
                                        $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                                        $dataDocumento['DOCM_ID_DOCUMENTO'] = $idDocumento;
                                        $dataDocumento['replicaVistas'] = $data['REPLICA_VISTAS'];

                                        /* Cadastra partes do documento */
                                        if (!empty($dataPartePessoa) || !empty($dataParteLotacao) || !empty($dataPartePessExterna) || !empty($dataPartePessJur)) {
                                            if (!empty($rowDocmDocumento))
                                                $cadastroPartes = $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $dataDocumento, array());
                                        }
                                        $msg_to_user = "Documento cadastrado. Número do documento: " . $rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                                        $this->_helper->redirector("form");
                                    } catch (Exception $exc) {
                                        $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.";
                                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                        $this->view->flashMessagesView = $msg_to_user;
                                        $this->view->form = $form;
                                        $this->render('form');
                                    }
                                    try {
                                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                    } catch (Exception $exc) {
                                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $data["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
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
                        } else {
                            if ($anexos->getFileName() && $input_files["ANEXOS_0_"]["name"] != NULL) {
                                try {
                                    $upload = new App_Multiupload_Minuta($data);
                                    $nrDocsRed = $upload->incluirarquivos($anexos);
                                } catch (Exception $exc) {
                                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                                }
                            }
                            if (!$nrDocsRed["existentes"]) {
                                /* tratamento de erro */
                                $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                                $retornoIncluir_red_array["codigo"] = $retornoIncluir_red_array[0];
                                $retornoIncluir_red_array["descricao"] = $retornoIncluir_red_array[1];
                                $retornoIncluir_red_array["idDocumento"] = $retornoIncluir_red_array[2];

                                switch ($retornoIncluir_red_array["codigo"]) {
                                    case 'Erro: 80':
                                        $dcmto_cadastrado = $tabelaSadTbDocmDocumento->fetchAll(array('DOCM_NR_DOCUMENTO_RED = ?' => $retornoIncluir_red_array["idDocumento"]))->toArray();

                                        if (isset($dcmto_cadastrado[0]["DOCM_ID_DOCUMENTO"])) {
                                            $msg_to_user = "Esse documento já está cadastrado no sistema com o número: " . $dcmto_cadastrado[0]["DOCM_NR_DOCUMENTO"];

                                            if ($dcmto_cadastrado[0]["DOCM_IC_ATIVO"] == 'N') {
                                                $msg_to_user .= " e está inativo. <br/><br/>Deseja reativar o documento e alterar os seus metadados?";
                                                $data['DOCM_ID_DOCUMENTO'] = $dcmto_cadastrado[0]["DOCM_ID_DOCUMENTO"];

                                                $movimentacoes = $SadTbModoMoviDocumento->verificaQtdeMovimentacaoDcmto($data['DOCM_ID_DOCUMENTO']);
                                                if ($movimentacoes > 0) {
                                                    $msg_to_user .=' <br/><br/> Obs: Esse documento possui movimentações, ele será encaminhado para a sua caixa de entrada pessoal.';
                                                    $data['POSSUI_MOVIMENTACAO'] = 'S';
                                                }

                                                $Ns_Cadastrodcmto_ativa = new Zend_Session_Namespace('Ns_Cadastrodcmto_ativa');
                                                $Ns_Cadastrodcmto_ativa->data_post_documento = $data;

                                                $this->view->alert = $msg_to_user;
                                            } else {
                                                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user </div>";
                                                $this->view->flashMessagesView = $msg_to_user;
                                            }
                                            $this->view->form = $form;
                                            $this->render('form');
                                        } else {
                                            if (!$nrDocsRed["incluidos"]) {
                                                try {
                                                    $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red_array["idDocumento"];
                                                    $row = $tabelaSadTbDocmDocumento->createRow($data);
                                                    $idDocumento = $row->save();
                                                    if ($envia_documento == 'E') {
                                                        $encaCaixaUnidade = $SadTbModoMoviDocumento->encaminhaMinuta($idDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestinoPessoa);
                                                    }
                                                    $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                                                    $dataDocumento['DOCM_ID_DOCUMENTO'] = $idDocumento;
                                                    $dataDocumento['replicaVistas'] = $data['REPLICA_VISTAS'];
                                                    /* Cadastra partes do documento */
                                                    if (!empty($dataPartePessoa) || !empty($dataParteLotacao) || !empty($dataPartePessExterna) || !empty($dataPartePessJur)) {
                                                        if (!empty($rowDocmDocumento))
                                                            $cadastroPartes = $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $dataDocumento, array());
                                                    }

                                                    $msg_to_user = "Documento cadastrado. Número do documento: " . $rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                                                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                                                    $this->_helper->redirector("form");
                                                } catch (Exception $exc) {
                                                    $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.";
                                                    $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                                    $this->view->flashMessagesView = $msg_to_user;
                                                    $this->view->form = $form;
                                                    $this->render('form');
                                                }
                                                try {
                                                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                                } catch (Exception $exc) {
                                                    $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $data["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                                                }
                                            } else {
                                                try {
                                                    $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red_array["idDocumento"];
                                                    $row = $tabelaSadTbDocmDocumento->createRow($data);
                                                    $idDocumento = $row->save();
                                                    if ($envia_documento == 'E') {
                                                        $encaCaixaUnidade = $SadTbModoMoviDocumento->encaminhaMinuta($idDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestinoPessoa, $nrDocsRed);
                                                    }
                                                    $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                                                    $dataDocumento['DOCM_ID_DOCUMENTO'] = $idDocumento;
                                                    $dataDocumento['replicaVistas'] = $data['REPLICA_VISTAS'];
                                                    /* Cadastra partes do documento */
                                                    if (!empty($dataPartePessoa) || !empty($dataParteLotacao) || !empty($dataPartePessExterna) || !empty($dataPartePessJur)) {
                                                        if (!empty($rowDocmDocumento))
                                                            $cadastroPartes = $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $dataDocumento, array());
                                                    }
                                                    $msg_to_user = "Documento cadastrado. Número do documento: " . $rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                                                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                                                    $this->_helper->redirector("form");
                                                } catch (Exception $exc) {
                                                    $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.";
                                                    $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                                    $this->view->flashMessagesView = $msg_to_user;
                                                    $this->view->form = $form;
                                                    $this->render('form');
                                                }
                                                try {
                                                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                                } catch (Exception $exc) {
                                                    $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $data["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                                                }
                                            }
                                        }
                                        break;
                                    default:
                                        $erro = $retornoIncluir_red_array;
                                        $msg_to_user = "Documento não cadastrado. Não foi possível fazer o carregamento do arquivo.<br> " . implode(' , ', $erro);
                                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                        $this->view->flashMessagesView = $msg_to_user;
                                        $this->view->form = $form;
                                        $this->render('form');
                                        break;
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
                        }
                    }
                }
            } else {
                $pessoas = new Zend_Form_Element_Select('MODP_CD_MAT_PESSOA_DESTINO');
                $pessoas->setRequired(false)->setLabel('Enviar para:');
                $pessoas->addMultiOptions(array("" => "Selecione"));
                $form->addElement($pessoas);
                $this->view->textoHTML = $data['TEXTO_HTML'];
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }

    public function editAction()
    {
        $this->view->title = "Editar Documento";
        $form = new Sisad_Form_Cadastrodcmto();
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $userNamespace = new Zend_Session_Namespace('userNs');


        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            $dataCheck = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $data[DOCM_ID_DOCUMENTO]")->toArray();

            if ($form->isValid($data)) {

                /* setando os campos da tabela que não são obtidos no formulario */
                unset($data["DOCM_DH_CADASTRO"]);
                unset($data["DOCM_CD_MATRICULA_CADASTRO"]);

                $aux_DOCM_CD_LOTACAO_GERADORA = $data['DOCM_CD_LOTACAO_GERADORA'];
                $docm_cd_lotacao_geradora_array = explode(' - ', $data['DOCM_CD_LOTACAO_GERADORA']);
                $data['DOCM_SG_SECAO_GERADORA'] = $docm_cd_lotacao_geradora_array[3];
                $data['DOCM_CD_LOTACAO_GERADORA'] = $docm_cd_lotacao_geradora_array[2];

                $aux_DOCM_CD_LOTACAO_REDATORA = $data['DOCM_CD_LOTACAO_REDATORA'];
                $docm_cd_lotacao_redatora_array = explode(' - ', $data['DOCM_CD_LOTACAO_REDATORA']);
                $data['DOCM_SG_SECAO_REDATORA'] = $docm_cd_lotacao_redatora_array[3];
                $data['DOCM_CD_LOTACAO_REDATORA'] = $docm_cd_lotacao_redatora_array[2];

                unset($data["DOCM_NR_DOCUMENTO_RED"]);


                if ($data['DOCM_SG_SECAO_GERADORA'] != $dataCheck['DOCM_SG_SECAO_GERADORA'] ||
                        $data['DOCM_CD_LOTACAO_GERADORA'] != $dataCheck['DOCM_CD_LOTACAO_GERADORA'] ||
                        $data['DOCM_SG_SECAO_REDATORA'] != $dataCheck['DOCM_SG_SECAO_REDATORA'] ||
                        $data['DOCM_CD_LOTACAO_REDATORA'] != $dataCheck['DOCM_CD_LOTACAO_REDATORA'] ||
                        $data['DOCM_ID_TIPO_DOC'] != $dataCheck['DOCM_ID_TIPO_DOC']) {
                    $data["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_ID_TIPO_DOC']);
                    $data["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_CD_LOTACAO_GERADORA'], $data['DOCM_ID_TIPO_DOC'], $data["DOCM_NR_SEQUENCIAL_DOC"]);
                } else {
                    unset($data["DOCM_NR_SEQUENCIAL_DOC"]);
                    unset($data["DOCM_NR_DOCUMENTO"]);
                }


                /*                 * *********************************************************************************** */

                if (!$form->DOCM_DS_HASH_RED->isUploaded()) {
                    /* se o usuário não fizer o upload do arquivo insere somente nas tabelas */
                    try {
                        $row = $tabelaSadTbDocmDocumento->find($data['DOCM_ID_DOCUMENTO'])->current();
                        $row->setFromArray($data);
                        $idDocumento = $row->save();
                        $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                        $msg_to_user = "Documento alterado. Número do documento: " . $rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));

                        $this->_helper->_redirector('edit', 'cadastrodcmto', 'sisad', array('dcmto' => "$data[DOCM_ID_DOCUMENTO]"));
                    } catch (Exception $e) {
//                            echo $e->getMessage();
                        $msg_to_user = "Ocorreu erro ao alterar os metadados.";
//                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;

                        $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                        $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                        $form->populate($data);
                        $this->view->form = $form;
                    }
                } else {
                    if (isset($dataCheck['DOCM_NR_DOCUMENTO_RED']) && !is_null($dataCheck['DOCM_NR_DOCUMENTO_RED'])) {
                        $msg_to_user = "Não é possível reenviar o documento.";
                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                        return;
                    }
                    //echo "O documento foi carregado para o form<br/>";
                    $form->DOCM_DS_HASH_RED->receive();
                    if ($form->DOCM_DS_HASH_RED->isReceived()) {
//                        echo "o documento foi salvo na pasta temp<br/>";
                        /* Renomeando o arquivo gravado no servidor */
                        $userfile = $form->DOCM_DS_HASH_RED->getFileName(); /* caminho completo do arquivo gravado no servidor */
                        $tempDirectory = "temp";
                        $userfilename = substr($userfile, strrpos($userfile, $tempDirectory) + strlen($tempDirectory) + 1);
                        $fullFilePath = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR . 'SISADTEMPDOC' . date("dmYHisu") . $userfilename;
                        $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
                        $filterFileRename->filter($userfile); /* Renomeando a partir do caminho completo do arquivo no servidor */

                        //echo "o documento foi renomeado na pasta temp<br/>";
                        /* Inclusão do arquivo no RED */
                        $parametros = new Services_Red_Parametros_Incluir();
                        $parametros->login = Services_Red::LOGIN_USER_EADMIN;
                        $parametros->ip = Services_Red::IP_MAQUINA_EADMIN;
                        $parametros->sistema = Services_Red::NOME_SISTEMA_EADMIN;
                        $parametros->nomeMaquina = Services_Red::NOME_MAQUINA_EADMIN;

                        $metadados = new Services_Red_Metadados_Incluir();
                        $metadados->descricaoTituloDocumento = $data["DOCM_NR_DOCUMENTO"];
                        $metadados->numeroTipoSigilo = /* "0"; */$data['DOCM_ID_CONFIDENCIALIDADE']/* Services_Red::NUMERO_SIGILO_PUBLICO; */;
                        $metadados->numeroTipoDocumento = "01"/* $data['DOCM_ID_TIPO_DOC']; */;
                        $metadados->nomeSistemaIntrodutor = Services_Red::NOME_SISTEMA_EADMIN;
                        $metadados->ipMaquinaResponsavelIntervencao = Services_Red::IP_MAQUINA_EADMIN;
                        $metadados->secaoOrigemDocumento = "0100";
                        $metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
                        $metadados->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
                        $metadados->nomeMaquinaResponsavelIntervensao = Services_Red::NOME_MAQUINA_EADMIN;
                        $metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
                        $metadados->numeroDocumento = "";
                        $metadados->pastaProcessoNumero = /* ""; */ Services_Red::PASTA_PROCESSO_NUMERO_EADMIN;
                        $metadados->secaoDestinoIdSecao = "0100";

                        //$red = new Services_Red_Incluir(true); /*DESENVOLVIMENTO*/
                        $red = new Services_Red_Incluir(false); /* PRODUÇÃO */
                        $red->debug = false;
                        $red->temp = APPLICATION_PATH . '/../temp';

                        $file = $fullFilePath; /* caminho completo do arquivo renomeado no servidor */

//                        echo "Retorno da classe de inclusão<br/>";

                        $retornoIncluir_red = $red->incluir($parametros, $metadados, $file);
                        //Zend_Debug::dump($retornoIncluir_red);

                        if (is_array($retornoIncluir_red)) {
                            try {
                                $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red['numeroDocumento'];
                                $row                           = $tabelaSadTbDocmDocumento->createRow($data);
                                $idDocumento                   = $row->save();
                                
                                 /*Enviando o numero do documento para o email logado*/
                                $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                                 $userNs          = new Zend_Session_Namespace('userNs');
                                    $email        = new Application_Model_DbTable_EnviaEmail();
                                    $sistema      = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Adminintrativos Digitais';
                                    $remetente    = 'noreply@trf1.jus.br';
                                    $destinatario = $userNs->matricula . '@trf1.jus.br';
                                    $assunto      = 'Cadastro de Documento';
                                    $http         = $host = $_SERVER['SERVER_NAME'];
                                    $url          = $this->getFrontController()->getBaseUrl();
                                    $corpo        = "Cadastro efetuado com sucesso</p>
                                              Número do Documento: <a href=\"http://$http/$url/sisad/pesquisadcmto/pesquisa-doc-email/DOCM_ID_DOCUMENTO/" . $rowDocmDocumento['DOCM_NR_DOCUMENTO'] . "\"><b>" . $rowDocmDocumento['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>";
                                    
                            } catch (Exception $exc) {
                                //echo $exc->getMessage();
                                unlink(realpath($fullFilePath)); /* excluí o do pdf da pasta temp */
                                $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.";
//                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView = $msg_to_user;
                                $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                                $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                                $form->populate($data);
                                $this->view->form = $form;
                                $this->render('form');
                            }
                            unlink(realpath($fullFilePath)); /* excluí o do pdf da pasta temp */
                            //echo "Inseriu no red e na tabela com sucesso";

                            $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                            $msg_to_user = "Documento cadastrado. Número do documento: " . $rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('form', 'cadastrodcmto', 'sisad');
                        } else {
                            unlink(realpath($fullFilePath)); /* excluí o do pdf da pasta temp */
                            /* tratamento de erro */
                            $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                            $retornoIncluir_red_array["codigo"] = $retornoIncluir_red_array[0];
                            $retornoIncluir_red_array["descricao"] = $retornoIncluir_red_array[1];
                            $retornoIncluir_red_array["idDocumento"] = $retornoIncluir_red_array[2];

                            switch ($retornoIncluir_red_array["codigo"]) {

                                case 'Erro: 80':
                                    //Zend_Debug::dump($retornoIncluir_red_array);
                                    $dcmto_cadastrado = $tabelaSadTbDocmDocumento->fetchAll(array('DOCM_NR_DOCUMENTO_RED = ?' => $retornoIncluir_red_array["idDocumento"]))->toArray();
                                    if (isset($dcmto_cadastrado[0]["DOCM_ID_DOCUMENTO"])) {
                                        $msg_to_user = "Este documento já encontra-se cadastrado com o número de documento: " . $dcmto_cadastrado[0]["DOCM_NR_DOCUMENTO"] . '.';
//                                        $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                        $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                        $this->view->flashMessagesView = $msg_to_user;
                                        $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                                        $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                                        $form->populate($data);
                                        $this->view->form = $form;
                                        $this->render('form');
                                    } else {
                                        try {
                                            $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red_array["idDocumento"];
                                            $row = $tabelaSadTbDocmDocumento->find($data['DOCM_ID_DOCUMENTO'])->current();
                                            $row->setFromArray($data);
                                            $idDocumento = $row->save();
                                            $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                                            $msg_to_user = "Documento cadastrado. Número do documento: " . $rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                                        } catch (Exception $exc) {
                                            //echo $exc->getMessage();
                                            $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.";
//                                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                            $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                            $this->view->flashMessagesView = $msg_to_user;
                                            $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                                            $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                                            $form->populate($data);
                                            $this->view->form = $form;
                                            $this->render('form');
                                        }
                                    }
                                    break;
                                default:
                                    $msg_to_user = "Não foi possível fazer o carregamento do arquivo.";
//                                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                    $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                    $this->view->flashMessagesView = $msg_to_user;
                                    $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                                    $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                                    $form->populate($data);
                                    $this->view->form = $form;
                                    $this->render('form');
                                    break;
                            }
                        }
                    }
                }
            } else {
                $form->populate($data);
                $this->view->form = $form;
            }
        } else {
            $idDocumento = Zend_Filter::filterStatic($this->_getParam('dcmto', ''), 'int');
            if ($idDocumento) {
                $data = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento")->toArray();
//                    Zend_Debug::dump($data);
                $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
                $sec_lot_geradora = $RhCentralLotacao->getLotacaoBySecaoLotacao($data["DOCM_SG_SECAO_GERADORA"], $data["DOCM_CD_LOTACAO_GERADORA"]);
                $sec_lot_redatora = $RhCentralLotacao->getLotacaoBySecaoLotacao($data["DOCM_SG_SECAO_REDATORA"], $data["DOCM_CD_LOTACAO_REDATORA"]);


                $data["DOCM_SG_SECAO_GERADORA"] = $sec_lot_geradora['LOTA_SIGLA_SECAO'];
                $data["DOCM_CD_LOTACAO_GERADORA"] = $sec_lot_geradora['LOTA_SIGLA_LOTACAO'] . ' - ' . $sec_lot_geradora['LOTA_DSC_LOTACAO'] . ' - ' . $sec_lot_geradora['LOTA_COD_LOTACAO'] . ' - ' . $sec_lot_geradora['LOTA_SIGLA_SECAO'];
                $data["DOCM_SG_SECAO_REDATORA"] = $sec_lot_redatora['LOTA_SIGLA_SECAO'];
                $data["DOCM_CD_LOTACAO_REDATORA"] = $sec_lot_redatora['LOTA_SIGLA_LOTACAO'] . ' - ' . $sec_lot_redatora['LOTA_DSC_LOTACAO'] . ' - ' . $sec_lot_redatora['LOTA_COD_LOTACAO'] . ' - ' . $sec_lot_redatora['LOTA_SIGLA_SECAO'];

                if (isset($data['DOCM_NR_DOCUMENTO_RED']) && !is_null($data['DOCM_NR_DOCUMENTO_RED'])) {
                    $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
                    $docm_ds_hash_red->removeDecorator('Label')->removeDecorator('HtmlTag');
                    $docm_ds_hash_red->setAttribs(array('style' => 'display: none;'));
                }

                $form->populate($data);
                $this->view->form = $form;
            }
        }
    }

    /* public function ativaAction(){

      $Ns_Cadastrodcmto_ativa = new Zend_Session_Namespace('Ns_Cadastrodcmto_ativa');
      $tabelaSadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
      $aNamespace = new Zend_Session_Namespace('userNs');

      $data_post = $Ns_Cadastrodcmto_ativa->data_post_documento;

      try {
      $rowDocmDocumento = $tabelaSadTbDocmDocumento->find($data_post['DOCM_ID_DOCUMENTO'])->current();

      $data_post['DOCM_IC_ATIVO'] = 'S';
      $nroDocumento = $data_post['DOCM_NR_DOCUMENTO'];

      $rowDocmDocumento->setFromArray($data_post);
      $docmAtivado = $rowDocmDocumento->save();
      if($docmAtivado){

      if( isset($data_post['POSSUI_MOVIMENTACAO']) && $data_post['POSSUI_MOVIMENTACAO'] == 'S' ){
      //envia para caixa de entrada pessoal o documento
      $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();

      $ultimaMovi = $SadTbMoviMovimentacao->getUltimaMovimentacaoDcmto($data_post['DOCM_ID_DOCUMENTO']);

      $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $data_post['DOCM_CD_MATRICULA_CADASTRO'];
      $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $ultimaMovi['MODE_SG_SECAO_UNID_DESTINO'];
      $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $ultimaMovi['MODE_CD_SECAO_UNID_DESTINO'];

      $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $aNamespace->siglasecao; //$dados_input['MODE_SG_SECAO_UNID_DESTINO'];
      $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $aNamespace->codlotacao;
      $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';

      $dataMofaMoviFase["MOFA_ID_FASE"] = 1027; //REATIVAÇÃO DE DOCUMENTO
      $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $aNamespace->matricula;
      $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = 'Documento reativado';

      $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $aNamespace->matricula;

      $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;
      $encamCxPessoal_retorno = $SadTbModeMoviDestinatario->encaminhaMinuta($data_post['DOCM_ID_DOCUMENTO'],
      $dataMoviMovimentacao,
      $dataModeMoviDestinatario,
      $dataMofaMoviFase,
      $dataModpDestPessoa );
      if($encamCxPessoal_retorno == false){
      $this->_helper->flashMessenger (array('message' => "Não foi possível encaminhar o documento nº $nroDocumento para a Caixa Pessoal", 'status' => 'error'));
      //$msg_to_user = "Não foi possível encaminhar o documento nº $nroDocumento para a Caixa Pessoal";
      //$status = 'error';
      }else if($encamCxPessoal_retorno == true){
      $caixa_encaminhada = 'entrada';
      }
      }else{
      $caixa_encaminhada = 'rascunhos';
      }


      $msg_to_user = " Documento reativado e atualizado com sucesso. Número do documento: ".$nroDocumento;
      $status = 'success';

      }else if(!$docmAtivado){
      $msg_to_user = " Não foi possível reativar o documento: ".$nroDocumento;
      $status = 'error';
      $caixa_encaminhada = 'rascunhos';
      }

      $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => $status));
      $this->_helper->_redirector($caixa_encaminhada, 'caixapessoal', 'sisad');


      } catch (Exception $e) {
      echo $e->getMessage();

      }


      }
     */

    public function ajaxpessoasvistasAction()
    {
        $form = new Sisad_Form_Formulariominuta();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
            $Pessoas = $OcsTbPmatMatricula->getNomeUnidade($data);

            if (!empty($Pessoas)) {
                $this->view->pessoas = $Pessoas;
            }
        }
    }

    public function visualizarAction()
    {
        $form = new Sisad_Form_Formulariominuta();
        $this->view->formParte = new Sisad_Form_Partes();

        $userNamespace = new Zend_Session_Namespace('userNs');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $this->view->data = $data;
            $this->view->matricula = $userNamespace->matricula;

            if ($data["DOCM_CD_MATRICULA_CADASTRO"]) {
                $this->view->nome = $data["DOCM_CD_MATRICULA_CADASTRO"];
            } else {
                $this->view->nome = $userNamespace->nome . ' - ' . $userNamespace->matricula;
            }

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
            $mpdf->Image($imagem_path, 94, 20, 23, 22, 'jpg', '', true, true, false, false, true);
            $mpdf->WriteHTML($body);
            $name = 'SISAD_TEMP_DOC_MINUTA_VISUALIZAR_DOCUMENTO' . date("dmYHisu") . '.pdf';
            $mpdf->Output($name, 'D');
        }
    }

}
