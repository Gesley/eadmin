<?php

/**
 * @category    TRF1
 * @package     Sisad_DocumentoController
 * @copyright   Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author      Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license     FREE, keep original copyrights
 * @version     controlada pelo SVN
 * @tutorial    Tutorial abaixo
 * 
 * TRF1, Classe controladora para operações com documentos
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Sisad_DocumentoController extends Zend_Controller_Action {

    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch() {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function init() {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        $this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
        // Informação
        $this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    /**
     * Action que apenas redireciona para a action cadastrarAction
     */
    public function indexAction() {
        //redireciona para a tela de cadastro de documentos
        $this->_helper->_redirector('cadastrar', 'documento', 'sisad');
    }

    /**
     * Action responsável por exibir a tela de cadastro de documentos.
     */
    public function cadastrarAction() {
        $session = new Zend_Session_Namespace('sisad_documento_cadastrar');
        $form = new Sisad_Form_Documento();
        $service_pessoa = new Services_Rh_Pessoa();
        $form->setAction('cadastrar');
        if ($this->getRequest()->isPost()) {
            $fileTransfer = new Zend_File_Transfer_Adapter_Http();
            $fileTransfer->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

            //Validando a soma de todos os anexos
            $arquivosAnexos = $fileTransfer->getFileInfo();
            unset($arquivosAnexos['arquivo_principal']);
            $somaAnexos = 0;
            foreach ($arquivosAnexos as $arq) {
                $somaAnexos += (int) $arq['size'];
            }
            //Compara os valores em Byte
            if ($somaAnexos > 52428800) {
                $this->_helper->flashMessenger(array('message' => 'A soma dos arquivos do campo Anexos ultrapassou o limite de 50 Megas.', 'status' => 'notice'));
                $form->populate($data);
                $this->_helper->_redirector('cadastrar', 'documento', 'sisad');
            }

            //isValid foi sobrescrito            
            if ($form->isValid($data)) {
                $session->data = $data;
                $upload = (count($fileTransfer->getFileName()) > 0 ? $fileTransfer->getFileInfo() : array());
                $session->upload = $upload;
                $this->_helper->_redirector('cadastrarsalvar', 'documento', 'sisad');
            } else {
                //populate foi sobrescrito
                $form->populate($data);
            }
        }
        //caso tenha dados na sessão fazer populate no form
        if (isset($session->data)) {
            $form->populate($session->data);
        }
        $this->view->title = 'Cadastrar documento';
        $this->view->formCadastro = $form;

        //json contendo a lista de pessoas lotadas nas unidades de sua 
        //responsabilidade
        $this->view->jsonPessoasFisicasTrf1AgrupadasPorLotacao = Zend_Json::encode($service_pessoa->retornaComboPessoasFisicasTrf1AgrupadasPorMinhasUnidades());
        //json com base no perfil de responsabilidade
        $this->view->jsonResponsaveisAgrupadosPorUnidade = Zend_Json::encode($service_pessoa->retornaComboResponsaveisAgrupadosPorMinhasUnidade());
    }

    public function cadastrarsalvarAction() {
        $service_documento = new Services_Sisad_Documento();
        $session = new Zend_Session_Namespace('sisad_documento_cadastrar');
        if (isset($session->data)) {
            $data = $session->data;
            $upload = $session->upload;
            $dadosComplementares = array(
                'CADASTRO' => array(
                    'TIPO' => $data['radio_tipo_cadastro']
                )
                , 'PARTES' => array(
                    'DADOS' => $data
                )
                , 'ENCAMINHAMENTO' => array(
                    'TIPO' => $data['radio_tipo_encaminhamento']
                    , 'PARA_MINHA_CAIXA_PESSOAL' => $data['checkbox_minha_caixa_pessoal'] == 1
                    , 'MATRICULA_DESTINO' => ($data['checkbox_apenas_responsaveis'] == 1 ? $data['responsaveis_pela_unidade'] : $data['pessoas_da_unidade'])
                    , 'UNIDADE' => $data['caixa_minha_responsabilidade']
                )
                , 'AUTUACAO' => array(
                    'AUTUAR' => $data['check_box_autuacao'] == 1
                    , 'TEXTO' => $data['PRDI_DS_TEXTO_AUTUACAO']
                )
            );
            $retorno = $service_documento->cadastrar($data, $upload, $dadosComplementares);

            if ($retorno['sucesso']) {
                //remove os dados da sessão
                unset($session->data);
                unset($session->upload);
                $this->_helper->flashMessenger(array('message' => $retorno['mensagem'], 'status' => 'success'));
            } else {
                $this->_helper->flashMessenger(array('message' => $retorno['mensagem'], 'status' => $retorno['status']));
            }
            $this->_helper->_redirector('cadastrar', 'documento', 'sisad');
        } else {
            $this->_helper->flashMessenger(array('message' => 'Preencha o formulário primeiro.', 'status' => 'success'));
            $this->_helper->_redirector('cadastrar', 'documento', 'sisad');
        }
    }

    public function encaminharAction() {
        //Instancias
        $service_documento = new Services_Sisad_Documento();
        $userNs = new Zend_Session_Namespace('userNs');
        $sessaoDocumento = new Zend_Session_Namespace('sessaoDocumento');
        $form = new Sisad_Form_Encaminhar();
        $formListas = new Sisad_Form_Divulgar();
        $service_juntada = new Services_Sisad_Juntada();
        $service_pessoa = new Services_Rh_Pessoa();

        //Se tiver POST
        if ($this->getRequest()->isPost()) {

            //Recebe os dados
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

            //Se tiver caido na tela
            if (isset($data['acao']) && $data['acao'] == 'Encaminhar') {
                $data_post_caixa = $data;
                $data_post_caixa['documento'] = $service_juntada->completaComApensados($data_post_caixa['documento']);
                $sessaoDocumento->dadosDocumento = $data_post_caixa;

                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                $cont = 0;
                $rows = array();
                foreach ($data_post_caixa['documento'] as $value) {
                    $rows['documento'][$cont] = Zend_Json::decode($value);
                    $cont++;
                }
                $paginator = Zend_Paginator::factory($rows['documento']);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(count($rows['documento']));
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');

                /**
                 * Define a tela de origem
                 */
                $form->getElement('acao_sistema')->setValue($data['action']);
                $form->getElement('controle_sistema')->setValue($data['controller']);
                $form->getElement('modulo_sistema')->setValue('sisad');

                //Se for o encaminhamento
            } elseif (isset($data['salvar']) && $data['salvar'] == 'Encaminhar') {

                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                $cont = 0;
                $rows = array();
                foreach ($sessaoDocumento->dadosDocumento['documento'] as $value) {
                    $rows['documento'][$cont] = Zend_Json::decode($value);
                    $cont++;
                }
                $paginator = Zend_Paginator::factory($rows['documento']);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(count($rows['documento']));
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');

                //Upload dos arquivos
                $fileTransfer = new Zend_File_Transfer_Adapter_Http();
                $fileTransfer->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

                //Validando a soma de todos os anexos
                $arquivosAnexos = $fileTransfer->getFileInfo();
                $somaAnexos = 0;
                foreach ($arquivosAnexos as $arq) {
                    $somaAnexos += (int) $arq['size'];
                }
                //Compara os valores em Byte
                if ($somaAnexos > 52428800) {
                    $this->_helper->flashMessenger(array('message' => 'A soma dos arquivos do campo Anexos ultrapassou o limite de 50 Megas.', 'status' => 'notice'));
                    $form->populate($data);
                    $this->_helper->_redirector('encaminhar', 'documento', 'sisad');
                }

                //Captura os anexos informados
                $upload = (count($fileTransfer->getFileName()) > 0 ? $fileTransfer->getFileInfo() : array());

                //Valida formulário de listas e de encaminhamento
                if ($data['radio_tipo_encaminhamento'] == 'listas_internas') {
                    $dtInicio = $formListas->getElement('LIST_DT_INICIO_DIVULGACAO');
                    $dtFim = $formListas->getElement('LIST_DT_FIM_DIVULGACAO');
                    $dtInicio->setRequired(true);
                    $dtFim->setRequired(true);
                }

                if ($form->isValid($data) && $formListas->isValid($data)) {
                    //Chama função para necaminhamento
                    $retorno = $service_documento->encaminhar($sessaoDocumento->dadosDocumento, $upload, $data);
                    //Verifica retorno do encaminhamento
                    if ($retorno['sucesso']) {
                        $this->_helper->flashMessenger(array('message' => $retorno['mensagem'], 'status' => 'success'));
                    } else {
                        $this->_helper->flashMessenger(array('message' => $retorno['mensagem'], 'status' => $retorno['status']));
                    }
                    $this->_helper->_redirector($data['acao_sistema'], $data['controle_sistema'], $data['modulo_sistema']);
                } else {
                    $formListas->populate($data);
                    $form->populateValidacao($data);
                }
            }

            //Verificando permissão de divulgação em lista
            if ($service_documento->verificaPermissaoLista($userNs->matricula)) {
                $this->view->PermissaoLista = TRUE;
            } else {
                /* Retira o form para divulgação em listas internas */
                $radio = $form->radio_tipo_encaminhamento;
                $radio->removeMultiOption('listas_internas');
                $this->view->PermissaoLista = FALSE;
            }

            //Joga valores na view
            $this->view->jsonPessoasFisicasTrf1AgrupadasPorLotacao = Zend_Json::encode($service_pessoa->retornaComboPessoasFisicasTrf1AgrupadasPorMinhasUnidades());
            $this->view->jsonResponsaveisAgrupadosPorUnidade = Zend_Json::encode($service_pessoa->retornaComboResponsaveisAgrupadosPorMinhasUnidade());
            $this->view->data = $paginator;
            $this->view->title = "Encaminhar Documentos";
            $this->view->form_encaminhar = $form;
            $this->view->form_listas = $formListas;
        } else {
            $this->_helper->_redirector('index', 'index', 'admin');
        }
    }

    /**
     * Possibilita a assinatura de um documento,
     * Quando processo administrativo exibe toda a arvore de documentos e 
     * processos incluídos no processo.
     * Quando não processo administrativo exibe apenas o documento selecionado
     */
    public function assinarAction() {
        $this->view->title = 'Assinar';
        if ($this->getRequest()->isPost()) {
            $rnDocumento = new Trf1_Sisad_Negocio_Documento();
            $userNs = new Zend_Session_Namespace('userNs');
            $data = $this->getRequest()->getPost();
            $documentos = array();
            $processos = array();

            foreach ($data['documento'] as $i=>$json) {
                $documento = Zend_Json::decode($json);
                if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
                    $processos = $rnDocumento->getArvoreDeJuntadaProcesso($documento, false, false);
                    foreach ($processos as $k=>$proc) {
                       array_unshift(
                            $proc["DOCUMENTOS_ANEXADOS"], $rnDocumento->getDadosDocumentoAssinatura($documento['DOCM_ID_DOCUMENTO'])
                        );
                       $arrayProcessos[$k] = $proc["DOCUMENTOS_ANEXADOS"];
                    }
                } else {
                    $documento = $rnDocumento->getDadosDocumentoAssinatura($documento['DOCM_ID_DOCUMENTO']);
                    /**
                     * Verifica se os documentos são públicos e/ou se o assinante 
                     * tem vistas ao documento.
                     */
                    $valDoc = $rnDocumento->validaParteVista($userNs->matricula, null, $documento['DOCM_ID_DOCUMENTO'], $documento["DTPD_ID_TIPO_DOC"], 3);
                    if ($documento["DOCM_ID_CONFIDENCIALIDADE"] == '0' || $valDoc == true) {
                        $documentos[$i] = $documento;
                    }
                }
            }
                $this->view->form = new Sisad_Form_Assinatura();
                $this->view->documentos = $documentos;
                $this->view->arrayProcessos = $arrayProcessos;
            }
        }

    /*
     * 
     * ACTIONS AJAX
     * 
     * 
     */

    public function assinarporsenhasalvarajaxAction() {
        $mensagem = array();
        if ($this->getRequest()->isPost()) {
            $rnDocumento = new Trf1_Sisad_Negocio_Documento();
            $rnFase = new Trf1_Sisad_Negocio_Fase();
            $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc ();
            $dual = new Application_Model_DbTable_Dual();

            $data = $this->getRequest()->getPost();

            $dhAssinatura = $dual->sysdate();
            $documento = $data["DOCUMENTO"];
            $userNs = new Zend_Session_Namespace('userNs');
            $authAdapter = new App_Auth_Adapter_Db ();
            $authAdapter->setIdentity($userNs->matricula);
            $authAdapter->setCredential($data['SENHA']);
            $authAdapter->setDbName($userNs->bancoUsuario);
            $auth = Zend_Auth::getInstance();

            $result = $auth->authenticate($authAdapter);
            $messageLogin = $result->getMessages();
            if ($result->isValid()) {

                $verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($documento);
                if ($verifica) {
                    try {
                        $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $adapter->beginTransaction();

                        if (isset($documento['PROCESSO_PAI'])) {
                            $verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($documento['PROCESSO_PAI']);
                            if (!$verifica) {
                                $this->_helper->json->sendJson(array("MENSAGEM" => "O processo " . $$documento['PROCESSO_PAI']["MASC_NR_DOCUMENTO"] . " é confidencial ao usuário logado.", "SUCESSO" => false));
                            }

                            $processo = $rnDocumento->getDocumento($documento['PROCESSO_PAI']['DOCM_ID_DOCUMENTO']);
                            $rnFase->lancaFase(array(
                                "MOFA_ID_MOVIMENTACAO" => $processo['MOFA_ID_MOVIMENTACAO']
                                , "MOFA_DH_FASE" => $dhAssinatura
                                , "MOFA_ID_FASE" => Trf1_Sisad_Definicoes::FASE_ASSINATURA_POR_SENHA
                                , "MOFA_CD_MATRICULA" => $userNs->matricula
                                , "MOFA_DS_COMPLEMENTO" => 'Assinatura por senha realizada no documento ' . $documento["MASC_NR_DOCUMENTO"]
                            ));
                        }
                        $documento = $rnDocumento->getDocumento($documento['DOCM_ID_DOCUMENTO']);
                        $rnFase->lancaFase(array(
                            "MOFA_ID_MOVIMENTACAO" => $documento['MOFA_ID_MOVIMENTACAO']
                            , "MOFA_DH_FASE" => $dhAssinatura
                            , "MOFA_ID_FASE" => Trf1_Sisad_Definicoes::FASE_ASSINATURA_POR_SENHA
                            , "MOFA_CD_MATRICULA" => $userNs->matricula
                            , "MOFA_DS_COMPLEMENTO" => 'Assinatura por senha realizada'
                        ));

                        $adapter->commit();
                        $this->_helper->json->sendJson(array("MENSAGEM" => "Assinatura do documento " . $documento["MASC_NR_DOCUMENTO"] . " foi realizada com sucesso", "SUCESSO" => true));
                    } catch (Exception $e) {
                        $adapter->rollBack();
                        $this->_helper->json->sendJson(array("MENSAGEM" => "Não foi possível assinar o documento " . $documento["MASC_NR_DOCUMENTO"] . ": " . $e->getMessage(), "SUCESSO" => false));
                    }
                } else {
                    $this->_helper->json->sendJson(array("MENSAGEM" => "O documento " . $documento["MASC_NR_DOCUMENTO"] . " é confidencial ao usuário logado.", "SUCESSO" => false));
                }
            } else {
                $this->_helper->json->sendJson(array("MENSAGEM" => "Senha incorreta", "SUCESSO" => false));
            }
        } else {
            $this->_helper->json->sendJson(array("MENSAGEM" => "Nenhum dado foi passado para a assinatura.", "SUCESSO" => false));
        }
    }

    public function assinarporcertificadosalvarajaxAction() {
        $mensagem = array();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $rnDocumento = new Trf1_Sisad_Negocio_Documento();
            $assinatura['ARQUIVO'] = $data['ARQUIVO'];
            $assinatura['ASSINATURA'] = $data['ASSINATURA'];
            $assinatura['CERTIFICADO']['CPF'] = $data['CPF'];
            $mensagem = $rnDocumento->assinarPorCertificado($data['DOCUMENTO'], $assinatura);
            $this->_helper->json->sendJson($mensagem);
        } else {
            $this->_helper->json->sendJson(array("MENSAGEM" => "Nenhum dado foi passado para a assinatura.", "SUCESSO" => false));
        }
    }

    /**
     * Trata a mensagem ou array de mensagens a fim de retorna uma div contendo
     * as mensagens passadas por parametro.
     * 
     * @param array $array
     * @param string $tipo
     * @param Zend_Form $objForm
     */
    function getMensagemDiv($data, $tipo = 'notice', Zend_Form $objForm = null) {
        $conteudo = '';
        if ($tipo == 'form') {
            $conteudo = '<ul>';
            $objForm->isValid($data);
            foreach ($objForm->getMessages() as $key => $value) {
                foreach ($value as $erro) {
                    $conteudo .= '<li><strong>' . $objForm->getElement($key)->getLabel() . ' </strong>' . $erro . '</li>';
                }
            }
            $conteudo.='</ul>';
            $class = 'notice';
            $label = 'Aviso';
        } else {

            if (is_array($data)) {
                $conteudo = '<ul>';
                foreach ($data as $mensagem) {
                    $conteudo .= '<li>' . $mensagem['mensagem'] . '</li>';
                }
                $conteudo .= '</ul>';
            } else {
                $conteudo = $data;
            }
            if ($tipo == 'notice') {
                $class = $tipo;
                $label = 'Aviso';
            } elseif ($tipo == 'error') {
                $class = $tipo;
                $label = 'Erro';
            } elseif ($tipo == 'success') {
                $class = $tipo;
                $label = 'Sucesso';
            } elseif ($tipo == 'info') {
                $class = $tipo;
                $label = 'Informação';
            }
        }
        return '<div class=\'' . $class . '\'><strong>' . $label . ': </strong>' . $conteudo . '</div>';
    }

    public function novomenuAction() {
        // usa um script de layout diferente com este action:
        $this->_helper->layout->setLayout('novomenu');
        $form = new Guardiao_Form_PerfilPessoaAdm();
        $this->view->form = $form;
        $this->view->teste = "teste";
    }

    public function assinarsalvarAction() {
        $data = $this->getRequest()->getPost();
        /**
         * Assina os documentos por senha
         */
        if ($data["TIPO_ASSINATURA"] == "senha") {
            /**
             * Realiza uma verificação na tabela de login e retorna false caso a
             * senha esteja incorreta.
             */
            $login = new App_Auth_Adapter_Db();
            $usr = explode('-', $data["USUARIO"]);
            $login->setIdentity(trim($usr[0]));
            $login->setCredential($data["SENHA"]);
            if ($data["SENHA"] == '') {
                $this->_helper->json->sendJson(array("MENSAGEM" => "Favor digitar a senha.", 'SUCESSO' => false));
}
            $ass =  $login->verify();
            if ($ass === false) {
                $this->_helper->json->sendJson(array("MENSAGEM" => "A senha está incorreta.", 'SUCESSO' => false));
            } else {
                $userNs = new Zend_Session_Namespace('userNs');
                $nDoc = new Trf1_Sisad_Negocio_Documento();
                $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc ();
                $documentoArray = array();
                foreach ($data ['documentos'] as $k=>$value) {
                    $documentoArray[$k] = Zend_Json::decode ( $value );
                    $dadosDocumento[$k] = $nDoc->getDocumento($documentoArray[$k]["DOCM_ID_DOCUMENTO"]);

                    if (in_array($dadosDocumento[$k] ['DOCM_ID_CONFIDENCIALIDADE'], array('1', '3', '4'))) {
                        $retorno = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($dadosDocumento[$k]); //VERIFICA SE A PARTE PODE ASSINAR O DOCUMENTO

                        if (!$retorno) {
                            $documentosAutorizados .= $dadosDocumento[$k] ['DOCM_NR_DOCUMENTO'] . "<br>";
                            $this->autorizado = false;
                        } else {

                            $documentosAutorizados .= $dadosDocumento[$k] ['DOCM_NR_DOCUMENTO'] . "<br>";
                        }
                    } else {
                        if ($dadosDocumento[$k] ['DOCM_ID_CONFIDENCIALIDADE'] == 0) {
                            $documentosAutorizados .= $dadosDocumento[$k] ['DOCM_NR_DOCUMENTO'] . "<br>";
                        }
                    }
                }
                $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase ();
                $dual = new Application_Model_DbTable_Dual ();
                if (count($dadosDocumento) == 0) {
                    $this->_helper->json->sendJson(array("MENSAGEM" => "Favor escolher um documento.", 'SUCESSO' => false));
                }
                foreach ($dadosDocumento as $value) {
                    $documentosAss .= $value['DOCM_NR_DOCUMENTO'] . ', '; // CONCATENA OS NÚMEROS DO DOCUMENTOS SELECIONADOS PRA EXIBIR NA MENSAGEM DE SUCESSO.
                }
                /** 
                 * INSERI A MOVIMENTAÇÃO NO PROCESSO SELECIONADO(ASSINATURA)::1
                 */
                $documentosAssinados = substr($documentosAss, 0, - 2);
                foreach ($dadosDocumento as $value) {
                    $mapperDocumento = new Sisad_Model_DataMapper_Documento ();
                    if (!empty($value)) {
                        $dados = $mapperDocumento->getDadosDocumentopeloNRDoc($value['DOCM_NR_DOCUMENTO']);
                        $dataMofaMoviFaseDoc ["MOFA_ID_MOVIMENTACAO"] = $dados[0]['MOFA_ID_MOVIMENTACAO'];
                        $dataMofaMoviFaseDoc ["MOFA_DH_FASE"] = $dual->sysdate();
                        $dataMofaMoviFaseDoc ["MOFA_ID_FASE"] = 1018;
                        $dataMofaMoviFaseDoc ["MOFA_CD_MATRICULA"] = $userNs->matricula;
                        $dataMofaMoviFaseDoc ["MOFA_DS_COMPLEMENTO"] = 'Documento assinado por senha';
                        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFaseDoc);
                        try {
                            $rowMofaMoviFase->save();
                        } catch (Exception $e) {
                            $msgError .= $e->getMessage() . "<br>";
                        }
                    }
                }
                if (!$rowMofaMoviFase) {
                    $this->_helper->json->sendJson(array("MENSAGEM" => "Documento(s) $documentosAssinados não foram assinado(s).", 'SUCESSO' => false));
                } else {
                    $this->_helper->json->sendJson(array("MENSAGEM" => "Documento(s): $documentosAssinados <br />assinado(s).", 'SUCESSO' => true));
                }
            }
        } else {
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                Zend_Debug::dump($data);exit;
                $rnDocumento = new Trf1_Sisad_Negocio_Documento();
                $assinatura['ARQUIVO'] = $data['ARQUIVO'];
                $assinatura['ASSINATURA'] = $data['ASSINATURA'];
                $assinatura['CERTIFICADO']['CPF'] = $data['CPF'];
                $mensagem = $rnDocumento->assinarPorCertificado($data['DOCUMENTO'], $assinatura);
                $this->_helper->json->sendJson($mensagem);
            } else {
                $this->_helper->json->sendJson(array("MENSAGEM" => "Nenhum dado foi passado para a assinatura.", "SUCESSO" => false));
            }
        }
    }

}
