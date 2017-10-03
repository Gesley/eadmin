<?php

class Sisad_CadastrodcmtoextController extends Zend_Controller_Action {
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
        $this->view->titleBrowser = 'e-Sisad';
        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
        // Informação
        $this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction() {
        $this->view->title = "Cadastramento de Documentos Externos";
    }

    public function formAction() {
        $this->_helper->_redirector('cadastrar', 'documento', 'sisad');
    }

    public function ajaxunidadeAction() {
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

    public function ajaxassuntodocmAction() {
        $assunto_p = $this->_getParam('term', '');
        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        $assunto = $mapperPctt->getPCTTAjax($assunto_p);

        $fim = count($assunto);
        for ($i = 0; $i < $fim; $i++) {
            $assunto[$i] = array_change_key_case($assunto[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($assunto);
    }

    public function ajaxnomedestinatarioAction() {
        $tipo = $this->_getParam('tipo', '');
        $nomeDestinatario = $this->_getParam('term', '');

        if ($tipo == 'juridica' || $tipo == null) {
            $OcsTbPessPessoa = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
            $nome_array = $OcsTbPessPessoa->getNomeDestinatarioAjax($nomeDestinatario);
        } else if ($tipo == 'fisica') {
            $OcsTbPnatPessoa = new Application_Model_DbTable_OcsTbPnatPessoaNatural();
            $nome_array = $OcsTbPnatPessoa->getPessoaComIDAjax($nomeDestinatario);
        }
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxdadospessoaAction() {
        $tipo = $this->_getParam('tipo', '');
        $dados = $this->_getParam('pessoa', '');

        $dados = explode(' - ', $dados);
        $id = $dados[0];
        $nome = $dados[1];

        $ocsTbPend = new Application_Model_DbTable_OcsTbPendEnderecoPessoa();
        $dadosUsuario = $ocsTbPend->getDadosEndereçoPessoa($tipo, $id);

        $this->_helper->_json->sendJson($dadosUsuario);
    }

    public function ajaxnometipodocumentoAction() {
        $nomeTipoDocumento = $this->_getParam('term', '');
        $OcsTbDtpdTipoDoc = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $nome_array = $OcsTbDtpdTipoDoc->getTipoDocumentosAjax($nomeTipoDocumento);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxcontrolenivelAction() {
        $id_modulo = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $OcsTbAcaoAcaoSistema = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $GetTipoDocNivel_2 = $OcsTbAcaoAcaoSistema->getTipoDocumentosNivel2($id_modulo);
        $this->view->controle_array = $GetTipoDocNivel_2;
        $this->view->idNivel1 = $id_modulo;
    }

    public function saveAction() {
        $this->_helper->_redirector('cadastrar', 'documento', 'sisad');
    }

    public function editAction() {
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


//                Zend_Debug::dump($data);exit;
//                Zend_Debug::dump($data);
//                exit;

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
                                $row = $tabelaSadTbDocmDocumento->createRow($data);
                                $idDocumento = $row->save();
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
                            $this->_helper->_redirector('form', 'cadastrodcmtoext', 'sisad');
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

    public function orgaoenderecoAction() {
        $ocsTbGrup = new Application_Model_DbTable_OcsTbPerfPerfil();
        $tbPess = new Application_Model_DbTable_OcsTbPessPessoa();
        $tbPnat = new Application_Model_DbTable_OcsTbPnatPessoaNatural();
        $tbPjur = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $tbPend = new Application_Model_DbTable_OcsTbPendEnderecoPessoa();

        $fetchall = $ocsTbGrup->find(11)->toArray();
        $current = $ocsTbGrup->find(11)->current();

        $data = $this->getRequest()->getPost();
        $this->view->title = 'Cadastro de Orgãos e Endereços';

        $Dual = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $Dual->sysdateDb();

        $form = new Sisad_Form_AddPessoa();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($form->isValid($data)) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                if ($data['cadastro'] == 'JURIDICA') {
                    $idNome = explode(' - ', $data["PJUR_NO_RAZAO_SOCIAL"]);
                    $idPessoa = $idNome[0];

                    if (count($idNome) == 2) {
                        try {
                            $cep = str_replace('-', '', $data["PEND_NR_CEP"]);

                            $pend["PEND_ID_PESSOA"] = $idPessoa;
                            $pend["PEND_ID_TP_ENDERECO"] = $data["PEND_ID_TP_ENDERECO"];
                            $pend["PEND_DS_ENDERECO"] = strtoupper($data["PEND_DS_ENDERECO"]);
                            $pend["PEND_NR_CEP"] = $cep;
                            $pend["PEND_IC_PRINCIPAL"] = 'S';
                            $pend["PEND_IC_EXCLUSAO_LOGICA"] = 'N';

                            $setEndereco = $tbPend->createRow($pend);
                            $enderecar = $setEndereco->save();

                            $db->commit();
                            $this->_helper->flashMessenger(array('message' => "Endereço Alterado Com sucesso!", 'status' => 'success'));
                            $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $this->_helper->flashMessenger(array('message' => "Problemas ao Tentar Alterar Endereço!", 'status' => 'error'));
                            $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                        }
                    } else {
                        try {
                            /*
                             * Formata os dados de CPNJ e CEP para somente números
                             */
                            $cnpj = str_replace('.', '', $data["PJUR_NR_CNPJ"]);
                            $cnpj = str_replace('/', '', $cnpj);
                            $cnpj = str_replace('-', '', $cnpj);
                            $cep = str_replace('-', '', $data["PEND_NR_CEP"]);

                            /*
                             * Insere na tabela OCS_TB_PESS_PESSOA
                             */
                            $pess['PESS_DH_CADASTRO'] = new Zend_Db_Expr('SYSDATE');
                            $pess['PESS_IC_EXCLUSAO_LOGICA'] = 'N';
                            $pess['PESS_IC_TIPO_PESSOA'] = 1;

                            $setPessoa = $tbPess->createRow($pess);
                            $idPessoa = $setPessoa->save();

                            /*
                             * Insere na tabela OCS_TB_PJUR_PESSOA_JURIDICA
                             */
                            $pjur["PJUR_ID_PESSOA"] = $idPessoa;
                            $pjur["PJUR_NO_RAZAO_SOCIAL"] = $data["PJUR_NO_RAZAO_SOCIAL"];
                            $pjur["PJUR_NR_CNPJ"] = $cnpj;
                            $pjur["PJUR_NO_FANTASIA"] = $data["PJUR_NO_FANTASIA"];
                            $pjur["PJUR_IC_PORTE"] = $data["PJUR_IC_PORTE"];

                            $setPjur = $tbPjur->createRow($pjur);
                            $idPjur = $setPjur->save();

                            /*
                             * Insere na tabela OCS_TB_PEND_ENDERECO_PESSOA.
                             */
                            $pend["PEND_ID_PESSOA"] = $idPessoa;
                            $pend["PEND_ID_TP_ENDERECO"] = $data["PEND_ID_TP_ENDERECO"];
                            $pend["PEND_DS_ENDERECO"] = $data["PEND_DS_ENDERECO"];
                            $pend["PEND_NR_CEP"] = $cep;
                            $pend["PEND_IC_PRINCIPAL"] = 'S';
                            $pend["PEND_IC_EXCLUSAO_LOGICA"] = 'N';

                            $setEndereco = $tbPend->createRow($pend);
                            $enderecar = $setEndereco->save();

                            /*
                             * Caso não apresente nenhum erro, Comita os dados, e 
                             * redireciona o usuário para página de orgãos.
                             */
                            $db->commit();
                            $this->_helper->flashMessenger(array('message' => "Orgão / Endereço Cadastrados Com sucesso!", 'status' => 'success'));
                            $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $this->_helper->flashMessenger(array('message' => "Problemas ao Tentar Cadastrar Orgão / Endereço!", 'status' => 'error'));
                            $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                        }
                        exit;
                    }
                } else if ($data['cadastro'] == 'FISICA') {
                    try {
                        $idNome = explode(' - ', $data["PNAT_NO_PESSOA"]);
                        if (count($idNome) == 2) {
                            $idPessoa = $idNome[0];
                            $pnat["PNAT_NO_PESSOA"] = $idNome[0];
                        } else {
                            /*
                             * Insere na tabela OCS_TB_PESS_PESSOA
                             */
                            $pnat["PNAT_NO_PESSOA"] = $idNome[0];
                            $pess['PESS_DH_CADASTRO'] = new Zend_Db_Expr('SYSDATE');
                            $pess['PESS_IC_EXCLUSAO_LOGICA'] = 'N';
                            $pess['PESS_IC_TIPO_PESSOA'] = 0;

                            $setPessoa = $tbPess->createRow($pess);
                            $idPessoa = $setPessoa->save();
                        }

                        $cep = str_replace('-', '', $data["PEND_NR_CEP"]);
                        $cpf = str_replace('.', '', $data["PNAT_NR_CPF"]);
                        $cpf = str_replace('-', '', $cpf);

                        /**
                         * insere os dados na tabela de pessoa
                         */
                        $pnat["PNAT_ID_PESSOA"] = $idPessoa;
                        $pnat["PNAT_NR_CPF"] = $cpf;
                        $pnat["PNAT_NR_IDENTIDADE"] = $data["PNAT_NR_IDENTIDADE"];
                        $pnat["PNAT_SG_ORGAO_EMISSOR_ID"] = $data["PNAT_SG_ORGAO_EMISSOR_ID"];
                        $pnat["PNAT_SG_UF_EMISSOR_ID"] = $data["PNAT_SG_UF_EMISSOR_ID"];
                        $pnat["PNAT_DH_EMISSAO_ID"] = new Zend_Db_Expr("TO_DATE('" . $data['PNAT_DH_EMISSAO_ID'] . " 00:00:00', 'dd/mm/yyyy HH24:MI:SS')");
                        $pnat["PNAT_DT_NASCIMENTO"] = new Zend_Db_Expr("TO_DATE('" . $data['PNAT_DT_NASCIMENTO'] . " 00:00:00', 'dd/mm/yyyy HH24:MI:SS')");
                        $setPessoaNatural = $tbPnat->createRow($pnat);

                        $idPessoaNatural = $setPessoaNatural->save();
                        /**
                         * Insere os dados na tabela de endereço
                         */
                        $pend["PEND_ID_PESSOA"] = $idPessoa;
                        $pend["PEND_ID_TP_ENDERECO"] = $data["PEND_ID_TP_ENDERECO"];
                        $pend["PEND_DS_ENDERECO"] = strtoupper($data["PEND_DS_ENDERECO"]);
                        $pend["PEND_NR_CEP"] = $cep;
                        $pend["PEND_IC_PRINCIPAL"] = 'S';
                        $pend["PEND_IC_EXCLUSAO_LOGICA"] = 'N';

                        $setEndereco = $tbPend->createRow($pend);
                        $enderecar = $setEndereco->save();

                        $db->commit();
                        $this->_helper->flashMessenger(array('message' => "Endereço Alterado Com sucesso!", 'status' => 'success'));
                        $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                    } catch (Exception $exc) {
                        $db->rollBack();
                        $this->_helper->flashMessenger(array('message' => "Problemas ao Tentar Alterar Endereço!", 'status' => 'error'));
                        $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                    }
                }
            } else {
                $form->populate($this->getRequest()->getPost())->getValues();
                $this->_helper->flashMessenger(array('message' => "Preenchimento dos campos de forma incorreta", 'status' => 'error'));
            }
        }
    }

    /*
     * 
     */

    public function addorgaoenderecoAction() {
        $data = $this->getRequest()->getPost();
        $tbPjur = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $tbPend = new Application_Model_DbTable_OcsTbPendEnderecoPessoa();
        $tbPess = new Application_Model_DbTable_OcsTbPessPessoa();

        if ($data["Salvar"] == 'Salvar') {
            $idNome = explode(' - ', $data["PJUR_NO_RAZAO_SOCIAL"]);
            $id = (int) $idNome[0];

            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            if (count($idNome) == 2) {
                try {
                    $cep = explode('-', $data["PEND_NR_CEP"]);
                    $cep = $cep[0] . '' . $cep[1];

                    $pend["PEND_ID_PESSOA"] = $id;
                    $pend["PEND_ID_TP_ENDERECO"] = $data["PEND_ID_TP_ENDERECO"];
                    $pend["PEND_DS_ENDERECO"] = strtoupper($data["PEND_DS_ENDERECO"]);
                    $pend["PEND_NR_CEP"] = $cep;
                    $pend["PEND_IC_PRINCIPAL"] = 'S';
                    $pend["PEND_IC_EXCLUSAO_LOGICA"] = 'N';

                    $setEndereco = $tbPend->createRow($pend);
                    $enderecar = $setEndereco->save();

                    $db->commit();
                    $this->_helper->flashMessenger(array('message' => "Endereço Alterado Com sucesso!", 'status' => 'success'));
                    $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger(array('message' => "Problemas ao Tentar Alterar Endereço!", 'status' => 'error'));
                    $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                }
            } else {
                try {
                    /*
                     * Formata os dados de CPNJ e CEP para somente números
                     */
                    $cnpj = str_replace('.', '', $data["PJUR_NR_CNPJ"]);
                    $cnpj = str_replace('/', '', $cnpj);
                    $cnpj = str_replace('-', '', $cnpj);
                    $cep = str_replace('-', '', $data["PEND_NR_CEP"]);

                    /*
                     * Insere na tabela OCS_TB_PESS_PESSOA
                     */
                    $pess['PESS_DH_CADASTRO'] = new Zend_Db_Expr('SYSDATE');
                    $pess['PESS_IC_EXCLUSAO_LOGICA'] = 'N';
                    $pess['PESS_IC_TIPO_PESSOA'] = 1;
                    $setPessoa = $tbPess->createRow($pess);
                    $idPessoa = $setPessoa->save();

                    /*
                     * Insere na tabela OCS_TB_PJUR_PESSOA_JURIDICA
                     */
                    $pjur["PJUR_ID_PESSOA"] = $idPessoa;
                    $pjur["PJUR_NO_RAZAO_SOCIAL"] = $data["PJUR_NO_RAZAO_SOCIAL"];
                    $pjur["PJUR_NR_CNPJ"] = $cnpj;
                    $pjur["PJUR_NO_FANTASIA"] = $data["PJUR_NO_FANTASIA"];
                    $pjur["PJUR_IC_PORTE"] = $data["PJUR_IC_PORTE"];

                    $setPjur = $tbPjur->createRow($pjur);
                    $idPjur = $setPjur->save();

                    /*
                     * Insere na tabela OCS_TB_PEND_ENDERECO_PESSOA.
                     */
                    $pend["PEND_ID_PESSOA"] = $idPessoa;
                    $pend["PEND_ID_TP_ENDERECO"] = $data["PEND_ID_TP_ENDERECO"];
                    $pend["PEND_DS_ENDERECO"] = $data["PEND_DS_ENDERECO"];
                    $pend["PEND_NR_CEP"] = $cep;
                    $pend["PEND_IC_PRINCIPAL"] = 'S';
                    $pend["PEND_IC_EXCLUSAO_LOGICA"] = 'N';

                    $setEndereco = $tbPend->createRow($pend);
                    $enderecar = $setEndereco->save();

                    /*
                     * Caso não apresente nenhum erro, Comita os dados, e 
                     * redireciona o usuário para página de orgãos.
                     */
                    $db->commit();
                    $this->_helper->flashMessenger(array('message' => "Orgão / Endereço Cadastrados Com sucesso!", 'status' => 'success'));
                    $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger(array('message' => "Problemas ao Tentar Cadastrar Orgão / Endereço!", 'status' => 'error'));
                    $this->_helper->_redirector('orgaoendereco', 'cadastrodcmtoext', 'sisad');
                }
            }
        } else {
            if ($data["TIPO"] == 'addOrgão') {
                $this->view->title = "Adicionar Orgão / Endereço Externo";
                $operacao = 'orgaos';
                $form = new Sisad_Form_ProtocoloAddOrgao(array('operacao' => $operacao));

                $idNome = explode(' - ', $data["PJUR_NO_RAZAO_SOCIAL"]);
                $id = $idNome[0];

                if (is_numeric($id)) {
                    $dadosPjur = $tbPjur->getEnderecosDestinatarios($id);

                    $cnpj = $dadosPjur[0]["PJUR_NR_CNPJ"];
                    $cnpj = str_pad($cnpj, 14, 0, STR_PAD_LEFT);

                    $data["PJUR_ID_PESSOA"] = $id;
                    $data["PJUR_NO_RAZAO_SOCIAL"] = $data["PJUR_NO_RAZAO_SOCIAL"];
                    $data["PJUR_NR_CNPJ"] = $cnpj;
                    $data["PJUR_NO_FANTASIA"] = $dadosPjur[0]["PJUR_NO_FANTASIA"];
                    $data["PJUR_IC_PORTE"] = $dadosPjur[0]["PJUR_IC_PORTE"];
                }
            }
            $form->populate($data);
            $this->view->formAdd = $form;
        }
    }

    public function cadastrotipodocAction() {
        $this->view->title = "Tipo de Documentos Cadastrados";
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order = $this->_getParam('ordem', 'DTPD_ID_TIPO_DOC');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order . ' ' . $direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /* Ordenação */

        $table = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $select = $table->getTipoDocumentosTodos();

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function addtipodocAction() {
        $this->view->title = "Criar Novo Tipo de Documento";
        $ocsTbDtpdTipoDoc = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();

        $form = new Sisad_Form_AddTipoDoc();
        $form->removeElement('DTPD_ID_TIPO_DOC');
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            try {
                $data['DTPD_ID_TIPO_DOC'] = $ocsTbDtpdTipoDoc->getQTDTipoDoc();
                $data['DTPD_ID_TIPO_DOC'] = $data['DTPD_ID_TIPO_DOC'][0]["ID"];

                $data["DTPD_CD_TP_DOC_NIVEL_2"] = explode('-', $data["DTPD_CD_TP_DOC_NIVEL_2"]);
                $data["DTPD_CD_TP_DOC_NIVEL_2"] = $data["DTPD_CD_TP_DOC_NIVEL_2"][1];

                if ($data["DTPD_CD_TP_DOC_NIVEL_1"] == 0) {
                    $data["DTPD_CD_TP_DOC_NIVEL_1"] = $ocsTbDtpdTipoDoc->getQTDTipoDocNivel1();
                    $data["DTPD_CD_TP_DOC_NIVEL_1"] = $data["DTPD_CD_TP_DOC_NIVEL_1"][0]["NIVEL_1"];
                    $data["DTPD_CD_TP_DOC_NIVEL_2"] = 0;
                    $data["DTPD_CD_TP_DOC_NIVEL_3"] = 0;
                } else if ($data["DTPD_CD_TP_DOC_NIVEL_2"] == 0) {
                    $data["DTPD_CD_TP_DOC_NIVEL_2"] = $ocsTbDtpdTipoDoc->getQTDTipoDocNivel2($data["DTPD_CD_TP_DOC_NIVEL_1"]);
                    $data["DTPD_CD_TP_DOC_NIVEL_2"] = $data["DTPD_CD_TP_DOC_NIVEL_2"][0]["NIVEL_2"];
                    $data["DTPD_CD_TP_DOC_NIVEL_3"] = 0;
                } else {
                    $data["DTPD_CD_TP_DOC_NIVEL_3"] = $ocsTbDtpdTipoDoc->getQTDTipoDocNivel3($data["DTPD_CD_TP_DOC_NIVEL_1"], $data["DTPD_CD_TP_DOC_NIVEL_2"]);
                    $data["DTPD_CD_TP_DOC_NIVEL_3"] = $data["DTPD_CD_TP_DOC_NIVEL_3"][0]["NIVEL_3"];
                }

                $pctt = explode(' - ', $data['DTPD_ID_PCTT']);

                if ($pctt[1] != NULL) {
                    $data['DTPD_ID_PCTT'] = $mapperPctt->getPCTTbyCodigo($pctt[1]);
                    $data['DTPD_ID_PCTT'] = $data['DTPD_ID_PCTT']["AQVP_ID_PCTT"];
                }

                $DtpdTipoDoc["DTPD_ID_TIPO_DOC"] = $data["DTPD_ID_TIPO_DOC"];
                $DtpdTipoDoc["DTPD_CD_TP_DOC_NIVEL_1"] = $data["DTPD_CD_TP_DOC_NIVEL_1"];
                $DtpdTipoDoc["DTPD_CD_TP_DOC_NIVEL_2"] = $data["DTPD_CD_TP_DOC_NIVEL_2"];
                $DtpdTipoDoc["DTPD_CD_TP_DOC_NIVEL_3"] = $data["DTPD_CD_TP_DOC_NIVEL_3"];
                $DtpdTipoDoc["DTPD_NO_TIPO"] = $data["DTPD_NO_TIPO"];
                $DtpdTipoDoc["DTPD_SG_DOC"] = new Zend_Db_Expr('NULL');

                if ($data["DTPD_IC_ADM_JUD"] == 'AD') {
                    $DtpdTipoDoc["DTPD_IC_INSTANCIA"] = NULL;
                } else {
                    $DtpdTipoDoc["DTPD_IC_INSTANCIA"] = $data["DTPD_IC_INSTANCIA"];
                }
                $DtpdTipoDoc["DTPD_IC_ADM_JUD"] = $data["DTPD_IC_ADM_JUD"];
                $DtpdTipoDoc["DTPD_IC_ATIVO"] = 'S';
                $DtpdTipoDoc["DTPD_IC_ASSINATURA_DIGITAL"] = 'N';
                $DtpdTipoDoc["DTPD_IC_PRODUCAO_SISTEMA"] = 'S';
                $DtpdTipoDoc["DTPD_NR_TIPO_SIGILO"] = new Zend_Db_Expr('NULL');
                $DtpdTipoDoc["DTPD_ID_PCTT"] = $data["DTPD_ID_PCTT"];

                $criaTipoDoc = $ocsTbDtpdTipoDoc->createRow($DtpdTipoDoc);
                $idTipoDoc = $criaTipoDoc->save();
                $db->commit();
                $msg_to_user = "Cadastro do tipo de Documento efetuado com sucesso!";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
            } catch (Exception $exc) {
                $db->rollBack();
                $msg_to_user = "Problemas ao cadastrar Tipo de Documento!";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
            }
            $this->_helper->_redirector('cadastrotipodoc', 'cadastrodcmtoext', 'sisad');
        }
    }

    public function edittipodocAction() {
        $this->view->title = "Editar Tipo de Documento";
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $table = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $form = new Sisad_Form_AddTipoDoc();

        $row = $table->fetchRow(array('DTPD_ID_TIPO_DOC = ?' => $id));
        if ($row) {
            $dados = $row->toArray();
            $dados["DTPD_CD_TP_DOC_NIVEL_2"] = $dados["DTPD_CD_TP_DOC_NIVEL_1"] . '-' . $dados["DTPD_CD_TP_DOC_NIVEL_2"] . '-0';
            $form->populate($dados);
            //Zend_Debug::dump($dados);
        }

        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            try {
                $rowProcesso = $table->find($id)->current();
                $data["DTPD_CD_TP_DOC_NIVEL_2"] = explode('-', $data["DTPD_CD_TP_DOC_NIVEL_2"]);
                $data["DTPD_CD_TP_DOC_NIVEL_2"] = $data["DTPD_CD_TP_DOC_NIVEL_2"][1];
                unset($data["setAcao"]);
                unset($data["Salvar"]);
                unset($data["DTPD_ID_TIPO_DOC"]);
                $rowProcesso->setFromArray($data);
                $rowProcesso->save();
                $db->commit();
                $this->_helper->flashMessenger(array('message' => "Tipo de Documento Alterado!", 'status' => 'success'));
            } catch (Exception $exc) {
                echo $exc->getMessage();
                $db->rollBack();
                $this->_helper->flashMessenger(array('message' => "Não foi possivel alterar o Tipo de Documento!", 'status' => 'notice'));
            }
            $this->_helper->_redirector('cadastrotipodoc', 'cadastrodcmtoext', 'sisad');
        }
    }

}
