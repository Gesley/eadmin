<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Sisad_CaixapessoalController extends Zend_Controller_Action {
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
		
        $this->view->titleBrowser = 'e-Sisad';
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();

        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
        // Informação
        $this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $rows = $dados->getCaixaUnidadeNovos($order);

        Zend_Debug::dump($order);

        Zend_Debug::dump($rows);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(10);


        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        //$this->_helper->layout->disableLayout();
    }

    public function rascunhosAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $nome = $aNamespace->nome;

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $rows = $dados->getCaixaPessoalRascunhos($aNamespace->codlotacao, $aNamespace->siglasecao, $aNamespace->matricula, $order);

        $TimeInterval = new App_TimeInterval();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['DOCM_DH_CADASTRO_CHAR']);
            $rows[$i]['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }

        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');


        $this->view->title = "Caixa Rascunhos - $nome";

//        $this->view->title = "Caixa Rascunhos - DISAD";
    }

    public function entradaAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_entrada = new Zend_Session_Namespace('Ns_Caixapessoal_entrada');
        $nome = $userNs->nome;
        $matricula = $userNs->matricula;

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $form = new Sisad_Form_CaixaDocumentos();
        $form->DOCM_CD_LOTACAO_GERADORA->setLabel('Localização');

        //Categoria
        $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_CD_MATRICULA_CATEGORIA = '$matricula'");
        $Categorias = $Categorias->toArray();
        $qntCategorias = count($Categorias);

        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        foreach ($Categorias as $Categorias_p):
            $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
            $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NM_CATEGORIA"]));
            $cont++;
        endforeach;
        $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
        $this->view->categorias = $Categorias;

        //Pessoas da unidade
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $unidades = $OcsTbUnpeUnidadePerfil->getResponsavelCaixaUnidadePessoal($matricula);
        $pessoas = $OcsTbPmatMatricula->getPessoasdascaixas($unidades);

        $mode_cd_matr_recebedor = $form->getElement('PAPD_CD_MATRICULA_INTERESSADO');
        $mode_cd_matr_recebedor->addMultiOptions(array('' => 'Selecione uma pessoa da unidade'));
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
        endforeach;

        $form_valores_padrao = $form->getValues();


        if ($this->_getParam('nova') === '1') {
            unset($Ns_Caixapessoal_entrada->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $this->view->title = "Caixa de Entrada - $nome";
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $filtro = $this->tratafiltrodascaixas($data_pesq);
                $Ns_Caixapessoal_entrada->data_pesq = $this->getRequest()->getPost();
                $Ns_Caixapessoal_entrada->filtro = $filtro;
                $form->populate($Ns_Caixapessoal_entrada->data_pesq);
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "Caixa Entrada - $nome";
                return;
            }
        }//Post e validate

        $data_pesq = $Ns_Caixapessoal_entrada->data_pesq;
        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();

        if (!is_null($data_pesq)) {
            $rows = $dados->getCaixaPessoalRecebidos($userNs->matricula, $userNs->codlotacao, $userNs->siglasecao, $order, $Ns_Caixapessoal_entrada->filtro);
            $this->view->ultima_pesq = true;
        } else {
            $rows = $dados->getCaixaPessoalRecebidos($userNs->matricula, $userNs->codlotacao, $userNs->siglasecao, $order, null);
            $this->view->ultima_pesq = false;
        }


        $cateNs = new Zend_Session_Namespace('cateNs');
        $cateNs->tipo = 'pessoal';
        $cateNs->cdMatricula = $userNs->matricula;

        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_TimeInterval();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {

            if (is_null($rows[$i]["MODE_DH_RECEBIMENTO"])) {
                $rows[$i]['MSG_LIDO'] = "Documento não lido";
                $rows[$i]['CLASS_LIDO'] = "naolido";
                $rows[$i]['CLASS_LIDO_TR'] = "naolidoTr";
            } else {
                $rows[$i]['MSG_LIDO'] = "Documento lido";
                $rows[$i]['CLASS_LIDO'] = "lido";
                $rows[$i]['CLASS_LIDO_TR'] = "lidoTr";
            }

            if (is_null($rows[$i]["DOCM_NR_DOCUMENTO_RED"])) {
                $rows[$i]['MSG_ARQUIVO'] = "Adicionar o arquivo";
                $rows[$i]['CLASS_ARQUIVO'] = "alertaButton";
            } else {
                $rows[$i]['MSG_ARQUIVO'] = "Abrir Documento";
                $rows[$i]['CLASS_ARQUIVO'] = "abrirAnexo";
            }
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOVI_DH_ENCAMINHAMENTO_CHAR']);
            $rows[$i]['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows[$i]['controller'] = $this->getRequest()->getControllerName();
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }

        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->title = "Caixa Entrada - $nome";
        $this->view->form = $form;
    }

    public function encaminhadosAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $nome = $aNamespace->nome;

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI.MOVI_DH_ENCAMINHAMENTO');

        $order_direction = $this->_getParam('direcao', 'DESC');

        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $rows = $dados->getCaixaPessoalEncaminhados($aNamespace->siglasecao, $aNamespace->codlotacao, $aNamespace->matricula, $order);

        /* verifica condições e faz tratamento nos dados */
        $fim = count($rows);
        $TimeInterval = new App_TimeInterval();
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOVI_DH_ENCAMINHAMENTO']);

            if (is_null($rows[$i]["MODE_DH_RECEBIMENTO"])) {
                $rows[$i]['MSG_LIDO'] = "Documento não lido no DESTINO";
                $rows[$i]['CLASS_LIDO'] = "naolido";
                $rows[$i]['CLASS_LIDO_TR'] = "naolidoTr";
            } else {
                $rows[$i]['MSG_LIDO'] = "Documento lido no DESTINO";
                $rows[$i]['CLASS_LIDO'] = "lido";
                $rows[$i]['CLASS_LIDO_TR'] = "lidoTr";
            }

            $rows[$i]['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }

        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');


        $this->view->title = "Caixa Encaminhados - $nome ";
    }

    public function encaminharcaixaunidadeAction() {

        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_encaminharcaixaunidade = new Zend_Session_Namespace('Ns_Caixapessoal_encaminharcaixaunidade');
        $form = new Sisad_Form_EncaCaixaUnidade();
        $FormPermissaoCaixa = new Sisad_Form_PermissaoCaixa();
        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues());

        /**
         * Obtendo as caixas que os usuários tem acesso pela permissão PERF_ID_PERFIL = 9 RESPONSÁVEL PELA CAIXA DA UNIDADE
         */
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidade($AcessoCaixaUnidade->getMatricula());

        $unidade = $FormPermissaoCaixa->getElement('UNIDADE')->setRequired(false);
        $unidade->addMultiOptions(array('' => ''));
        foreach ($CaixasUnidadeAcesso as $CaixaUnidade):
            $unidade->addMultiOptions(array(Zend_Json::encode($CaixaUnidade) => $CaixaUnidade["LOTA_SIGLA_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_DSC_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_COD_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_SIGLA_SECAO"]));
        endforeach;
        /*
         * Adiciono o combobox das unidades no formulario de encaminhar, se houver caixa de unidade a ser escolhida
         */
        $form->addElement($unidade);


        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        if (isset($data['acao']) && $data['acao'] == 'Encaminhar') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento'], 'json', 'recebidos_caixa_pessoal');
            $Ns_Caixapessoal_encaminharcaixaunidade->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixapessoal_encaminharcaixaunidade->data_post_caixa)) {
            $data['documento'] = $Ns_Caixapessoal_encaminharcaixaunidade->data_post_caixa;
        }

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            if ($anexos->getFileName()) {
                try {
                    $upload = new App_Multiupload_Upload();
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
                $this->render('encaminharcaixaunidade');
                return;
            } else if ($nrDocsRed["existentes"]) {
                foreach ($nrDocsRed["existentes"] as $existentes) {
                    $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                }
                $this->view->form = $form;
                $this->render('parecer');
                return;
            }
            if (isset($data['salvar']) && $data['salvar'] == 'Salvar') {
                if ($form->isValid($data)) {

                    $caixa_unidade = Zend_Json::decode($data['UNIDADE']);
                    $data_post_caixa = $Ns_Caixapessoal_encaminharcaixaunidade->data_post_caixa;
                    try {
                        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $db->beginTransaction();
                        $DocsNaoEncaminhados = array();
                        $docsEncaminhados = array();
                        foreach ($data_post_caixa as $value) {

                            $dados_input = Zend_Json::decode($value);
                            $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                            $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                            /* dados da origem do documento */

                            $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                            $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];

                            $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;
                            /* Encaminhamento para unidade */
                            /* $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = ; */

                            /* dados do destino do documento */
                            /* Verificar se foi selecionada alguma caixa */
                            if (!empty($caixa_unidade['LOTA_COD_LOTACAO'])) {
                                $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $caixa_unidade['LOTA_SIGLA_SECAO'];
                                $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $caixa_unidade['LOTA_COD_LOTACAO'];
                                $caixa_enviada = $caixa_unidade['LOTA_SIGLA_LOTACAO'];
                            } else {
                                $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $userNs->siglasecao;
                                $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $userNs->codlotacao;
                                $caixa_enviada = $userNs->siglalotacao;
                                /* Encaminhamento para unidade */
                                /* $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = ; */
                            }
                            $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';

                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1010; /* ENCAMINHAMENTO SISAD */
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                            $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;
                            $dataModpDestPessoa = array();

                            if (!$nrDocsRed["incluidos"]) {
                                $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestPessoa,null,false);
                            } else {
                                $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestPessoa, $nrDocsRed,false);
                            }
                            if ($encaminhaDocumento_retorno === true) {
                                $docsEncaminhados[] = $data_post_caixa;
                            } else {
                                $DocsNaoEncaminhados[] = $data_post_caixa;
                            }
                        }
                        if (count($DocsNaoEncaminhados) > 0) {
                            $this->_helper->flashMessenger(array('message' => "A ação encaminhar foi cancelada para todos os documentos.", 'status' => 'notice'));
                            foreach ($DocsNaoEncaminhados as $docNaoEncaminhado) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível encaminhar o documento nº {$docNaoEncaminhado['DOCM_NR_DOCUMENTO']}.", 'status' => 'error'));
                            }
                            $db->rollBack();
                        } else {
                            foreach ($docsEncaminhados as $docEncaminhado) {
                                $this->_helper->flashMessenger(array('message' => "Documento nº {$docEncaminhado['DOCM_NR_DOCUMENTO']} encaminhado.", 'status' => 'success'));
                            }
                            $db->commit();
                        }
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => $exc->getMessage(), 'status' => 'error'));
                        $this->_helper->_redirector('entrada', 'caixapessoal', 'sisad');
                    }
                    return $this->_helper->_redirector('entrada', 'caixapessoal', 'sisad');
                }
            }
        }
    }

    public function assinardocmentradaAction() {
        $this->view->title = "Assinar Documento por Senha";
        $aNamespace = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_rascunho = new Zend_Session_Namespace('Ns_Caixapessoal_rascunho');
        $form = new Sisad_Form_Verify();
        $data = $this->_getAllParams();

        if (isset($data['acao']) && $data['acao'] == 'Assinar por senha') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento'], 'json', 'recebidos_caixa_pessoal');
            $Ns_Caixapessoal_rascunho->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixapessoal_rascunho->data_post_caixa)) {
            $data['documento'] = $Ns_Caixapessoal_rascunho->data_post_caixa;
        }


        $data['COU_COD_MATRICULA'] = $aNamespace->matricula;
        $form->populate($data);

        $this->view->form = $form;

        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $Ns_Caixapessoal_rascunho->data_post_caixa = $data['documento'];

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (isset($data['Assinar']) && $data['Assinar'] == 'Assinar') {
                try {
                    $data['documento'] = $Ns_Caixapessoal_rascunho->data_post_caixa;
                    $coUserid = new Application_Model_DbTable_CoUserId();
                    $resultado = $coUserid->getAssinatura($aNamespace->matricula, $data["COU_COD_PASSWORD"]);
                    if ($resultado > 0) {
                        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $db->beginTransaction();
                        $docsNaoAssinados = array();
                        $docsAssinados = array();
                        foreach ($data['documento'] as $value) {
                            $dados_input = Zend_Json::decode($value);
                            $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();

                            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
                            $dataMofaMoviFase["MOFA_DH_FASE"] = new Zend_Db_Expr("SYSDATE");
                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1018; /* ASSINATURA SISAD */
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $aNamespace->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = 'Assinatura por senha';

                            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
                            $rowMofaMoviFase->save();

                            if (!$rowMofaMoviFase) {
                                $docsNaoAssinados[] = $dados_input;
                            } else if ($rowMofaMoviFase) {
                                $docsAssinados[] = $dados_input;
                                $Ns_Caixaunidade_encaminhar->data_post_caixa_executado = $data_post_caixa;
                            }
                        }
                    } else {
                        $this->_helper->flashMessenger(array('message' => "Senha Inválida. Lembrando que o sistema usa a senha de login no e-Admin. ", 'status' => 'notice'));
                        $this->_helper->_redirector('assinardocmentrada', 'caixapessoal', 'sisad');
                    }

                    if (count($docsNaoAssinados) > 0) {
                        $this->_helper->flashMessenger(array('message' => "A ação de assinar foi cancelada para todos os documentos.", 'status' => 'notice'));
                        foreach ($docsNaoAssinados as $docNaoAssinado) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível assinar o documento nº {$docNaoAssinado['DOCM_NR_DOCUMENTO']}.", 'status' => 'error'));
                        }
                        $db->rollBack();
                    } else {
                        foreach ($docsAssinados as $docAssinado) {
                            $this->_helper->flashMessenger(array('message' => "Documento nº {$docAssinado['DOCM_NR_DOCUMENTO']} Assinado por senha com sucesso", 'status' => 'success'));
                        }
                        $db->commit();
                    }
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => $e->getMessage(), 'status' => 'error'));
                }

                if ($dados_input['CAIXA_REQUISICAO'] == 'minutas') {
                    $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                } else {
                    $this->_helper->_redirector('entrada', 'caixapessoal', 'sisad');
                }
            }
        }
    }

    public function assinarrascunhoAction() {
        $this->view->title = "Assinar Documento por Senha";
        $aNamespace = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_rascunho = new Zend_Session_Namespace('Ns_Caixapessoal_rascunho');
        $form = new Sisad_Form_Verify();
        $data = $this->_getAllParams();

        if (isset($data['acao']) && $data['acao'] == 'Assinar por senha') {
            $Ns_Caixapessoal_rascunho->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixapessoal_rascunho->data_post_caixa)) {
            $data['documento'] = $Ns_Caixapessoal_rascunho->data_post_caixa;
        }


        $data['COU_COD_MATRICULA'] = $aNamespace->matricula;
        $form->populate($data);

        $this->view->form = $form;

        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $Ns_Caixapessoal_rascunho->data_post_caixa = $data['documento'];

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (isset($data['Assinar']) && $data['Assinar'] == 'Assinar') {
                $data['documento'] = $Ns_Caixapessoal_rascunho->data_post_caixa;
                $coUserid = new Application_Model_DbTable_CoUserId();
                $resultado = $coUserid->getAssinatura($aNamespace->matricula, $data["COU_COD_PASSWORD"]);
                if ($resultado > 0) {
                    try {
                        foreach ($data['documento'] as $value) {
                            $dados_input = Zend_Json::decode($value);
                            $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                            $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];
                            /* dados da origem do documento */
                            $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $aNamespace->siglasecao;
                            $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $aNamespace->codlotacao;
                            $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $aNamespace->matricula;
                            /* Encaminhamento para unidade */
                            /* dados do destino do documento */
                            $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $aNamespace->siglasecao;
                            $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $aNamespace->codlotacao;
                            $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                            /* Encaminhamento para unidade */
                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1018; /* ASSINATURA SISAD */
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $aNamespace->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Assinatura por senha.";

                            if ($data['movimentacao'] == 'E') {
                                $dataModpDestinoPessoa = array();
                                $nome_da_caixa = $aNamespace->siglalotacao;
                            } else if ($data['movimentacao'] == 'I') {
                                $dataModpDestinoPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $aNamespace->matricula;
                                $nome_da_caixa = $aNamespace->nome;
                            }

                            $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;
                            $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestinoPessoa);
                            if (!$encaminhaDocumento_retorno) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível assinar o documento nº $nrDocmDocumento", 'status' => 'error'));
                            } else if ($encaminhaDocumento_retorno) {
                                $Ns_Caixaunidade_encaminhar->data_post_caixa = $data;
                                $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento Assinado por senha e enviado para Caixa de Entrada - $nome_da_caixa com sucesso", 'status' => 'success'));
                            }
                        }
                        $this->_helper->_redirector('rascunhos', $data["controller"], 'sisad');
                    } catch (Exception $e) {
                        echo $e;
                    }
                } else {
                    $this->_helper->flashMessenger(array('message' => "Senha Inválida. Lembrando que o sistema usa a senha de login no e-Admin. ", 'status' => 'notice'));
                    $this->_helper->_redirector('assinarrascunho', 'caixapessoal', 'sisad');
                }
            }
        }
    }

    public function cancelardocmentradaAction() {
        $this->view->title = "Cancelar Documento";
        $aNamespace = new Zend_Session_Namespace('userNs');
        $nome = $aNamespace->nome;
        $Ns_Caixapessoal_cancelar = new Zend_Session_Namespace('Ns_Caixapessoal_cancelar');
        $data = $this->_getAllParams();
        $form = new Sisad_Form_Cancelar();

        if (isset($data['acao']) && $data['acao'] == 'Excluir') {
            $Ns_Caixapessoal_cancelar->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixapessoal_cancelar->data_post_caixa)) {
            $data['documento'] = $Ns_Caixapessoal_cancelar->data_post_caixa;
        }


        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        if ($data['Excluir'] == 'Excluir') {
            if ($form->isValid($data)) {
                try {
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $dados_input['MATRICULA_CAIXA_PESSOAL'] = $aNamespace->matricula;
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                        $dados_input["DOCM_IC_ATIVO"] = "N";
                        $dados_input["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];
                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                        $cancelamento = $mapperDocumento->cancelarDocumento($dados_input);

                        if (!$cancelamento) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível cancelar o documento nº $nrDocmDocumento", 'status' => 'error'));
                        } else if ($cancelamento) {
                            $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento cancelado com sucesso!", 'status' => 'success'));
                        }
                    }
                } catch (exception $e) {
                    echo $e;
                }
                $this->_helper->_redirector('entrada', 'caixapessoal', 'sisad');
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Cancelar Documentos - $nome";
        $this->view->form = $form;
    }

    public function cancelarrascunhoAction() {
        $this->view->title = "Cancelar Documento";
        $aNamespace = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_cancelar = new Zend_Session_Namespace('Ns_Caixapessoal_cancelar');
        $data = $this->_getAllParams();


        if (isset($data['acao']) && $data['acao'] == 'Cancelar') {
            $Ns_Caixapessoal_cancelar->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixapessoal_cancelar->data_post_caixa)) {
            $data['documento'] = $Ns_Caixapessoal_cancelar->data_post_caixa;
        }

        try {
            foreach ($data['documento'] as $value) {
                $dados_input = Zend_Json::decode($value);
                $dados_input['MATRICULA_CAIXA_PESSOAL'] = $aNamespace->matricula;
                $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                $dataDocumentos_cancelar["DOCM_IC_ATIVO"] = "N";
                $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                $rowDocmDocumento_cancelar = $tabelaSadTbDocmDocumento->find($dados_input["DOCM_ID_DOCUMENTO"])->current();
                ;
                $rowDocmDocumento_cancelar->setFromArray($dataDocumentos_cancelar);
                $cancelamento = $rowDocmDocumento_cancelar->save();

                if (!$cancelamento) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível cancelar o documento nº $nrDocmDocumento", 'status' => 'error'));
                } else if ($cancelamento) {
                    $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento cancelado com sucesso!", 'status' => 'success'));
                }
            }
        } catch (exception $e) {
            echo $e;
        }
        $this->_helper->_redirector($dados_input["CAIXA_REQUISICAO"], 'caixapessoal', 'sisad');
    }

    public function arquivardocmentradaAction() {
        $form = new Sisad_Form_Arquivar();
        $aNamespace = new Zend_Session_Namespace('userNs');
        $siglalotacao = $aNamespace->siglalotacao;
        $Ns_Caixapessoal_arquivar = new Zend_Session_Namespace('Ns_Caixapessoal_arquivar');
        $data = $this->_getAllParams();

        if (isset($data['acao']) && $data['acao'] == 'Arquivar') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento'], 'json', 'recebidos_caixa_pessoal');
            $Ns_Caixapessoal_arquivar->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixapessoal_arquivar->data_post_caixa)) {
            $data['documento'] = $Ns_Caixapessoal_arquivar->data_post_caixa;
        }

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        if ($data['Arquivar'] == 'Arquivar') {
            if ($form->isValid($data)) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                try {
                    $db->beginTransaction();
                    $documentosNaoArquivados = array();
                    $documentosArquivados = array();
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $dados_input['MATRICULA_CAIXA_PESSOAL'] = $aNamespace->matricula;
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                        $dados_input["DOCM_IC_ARQUIVAMENTO"] = "S";
                        $dados_input['MOFA_DS_COMPLEMENTO'] = $data['MOFA_DS_COMPLEMENTO'];
                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                        $arquivamento = $mapperDocumento->arquivarDocumento($dados_input, false);

                        if (!$arquivamento) {
                            $documentosNaoArquivados[] = $dados_input;
                        } else if ($arquivamento) {
                            $documentosArquivados[] = $dados_input;
                        }
                    }
                    if (count($documentosNaoArquivados) > 0) {
                        $db->rollBack();
                        $this->_helper->flashMessenger(array('message' => "O arquivamento de todos os documentos foi cancelado.", 'status' => 'notice'));
                        foreach ($documentosNaoArquivados as $naoArquivado) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível arquivar o documento nº {$naoArquivado['DOCM_NR_DOCUMENTO']}", 'status' => 'error'));
                        }
                    } else {
                        $db->commit();
                        foreach ($documentosArquivados as $arquivadoComSucesso) {
                            $this->_helper->flashMessenger(array('message' => "Documento nº {$arquivadoComSucesso['DOCM_NR_DOCUMENTO']} arquivado com sucesso!", 'status' => 'success'));
                        }
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    $this->_helper->flashMessenger(array('message' => $e->getMessage(), 'status' => 'error'));
                }
                $this->_helper->_redirector('entrada', 'caixapessoal', 'sisad');
            }
        }


        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Arquivar Documentos - $siglalotacao";
        $this->view->form = $form;
    }

    public function arquivarrascunhoAction() {
        $this->view->title = "Arquivar Documento";
        $aNamespace = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_arquivar = new Zend_Session_Namespace('Ns_Caixapessoal_arquivar');
        $data = $this->_getAllParams();


        if (isset($data['acao']) && $data['acao'] == 'Cancelar') {
            $Ns_Caixapessoal_arquivar->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixapessoal_arquivar->data_post_caixa)) {
            $data['documento'] = $Ns_Caixapessoal_arquivar->data_post_caixa;
        }

        try {
            foreach ($data['documento'] as $value) {
                $dados_input = Zend_Json::decode($value);
                $dados_input['MATRICULA_CAIXA_PESSOAL'] = $aNamespace->matricula;
                $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                $dataDocumentos_arquivar["DOCM_IC_ARQUIVAMENTO"] = "S";
                $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                $rowDocmDocumento_arquivar = $tabelaSadTbDocmDocumento->find($dados_input["DOCM_ID_DOCUMENTO"])->current();
                ;
                $rowDocmDocumento_arquivar->setFromArray($dataDocumentos_arquivar);
                $arquivamento = $rowDocmDocumento_arquivar->save();

                if (!$arquivamento) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível arquivar o documento nº $nrDocmDocumento", 'status' => 'error'));
                } else if ($arquivamento) {
                    $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento arquivado com sucesso!", 'status' => 'success'));
                }
            }
        } catch (exception $e) {
            echo $e;
        }
        $this->_helper->_redirector($dados_input["CAIXA_REQUISICAO"], 'caixapessoal', 'sisad');
    }

    public function arquivadospessoalAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_arquivados = new Zend_Session_Namespace('Ns_Caixapessoal_arquivados');
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
        $siglalotacao = $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();
        $nome = $userNs->nome;
        $matricula = $userNs->matricula;

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $form = new Sisad_Form_CaixaDocumentos();
        $form->DOCM_CD_LOTACAO_GERADORA->setLabel('Localização');
        $form_valores_padrao = $form->getValues();

        //Categoria
        $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_CD_MATRICULA_CATEGORIA = '$matricula'");
        $Categorias = $Categorias->toArray();

        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        foreach ($Categorias as $Categorias_p):
            $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
            $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NM_CATEGORIA"]));
            $cont++;
        endforeach;
        $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
        $this->view->categorias = $Categorias;

        //Pessoas da unidade
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $unidades = $OcsTbUnpeUnidadePerfil->getResponsavelCaixaUnidadePessoal($matricula);
        $pessoas = $OcsTbPmatMatricula->getPessoasdascaixas($unidades);

        $mode_cd_matr_recebedor = $form->getElement('PAPD_CD_MATRICULA_INTERESSADO');
        $mode_cd_matr_recebedor->addMultiOptions(array('' => 'Selecione uma pessoa da unidade'));
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
        endforeach;

        if ($this->_getParam('nova') === '1') {
            unset($Ns_Caixapessoal_arquivados->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $this->view->title = "Caixa de Documentos Arquivados - $nome";
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $filtro = $this->tratafiltrodascaixas($data_pesq);
                $Ns_Caixapessoal_arquivados->data_pesq = $this->getRequest()->getPost();
                $Ns_Caixapessoal_arquivados->filtro = $filtro;
                $form->populate($Ns_Caixapessoal_arquivados->data_pesq);
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "Caixa de Documentos Arquivados - $nome";
                return;
            }
        }//Post e validate

        $data_pesq = $Ns_Caixapessoal_arquivados->data_pesq;
        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
        if (!is_null($data_pesq)) {
            $rows = $dados->getArquivadosPessoal($userNs->matricula, $userNs->codlotacao, $userNs->siglasecao, $order, $Ns_Caixapessoal_arquivados->filtro);
            $this->view->ultima_pesq = true;
        } else {
            $rows = $dados->getArquivadosPessoal($userNs->matricula, $userNs->codlotacao, $userNs->siglasecao, $order, null);
            $this->view->ultima_pesq = false;
        }

        $cateNs->tipo = 'pessoal';
        $cateNs->cdMatricula = $userNs->matricula;

        $TimeInterval = new App_TimeInterval();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {


            if (is_null($rows[$i]["MODE_DH_RECEBIMENTO"])) {
                $rows[$i]['MSG_LIDO'] = "Documento não lido";
                $rows[$i]['CLASS_LIDO'] = "naolido";
                $rows[$i]['CLASS_LIDO_TR'] = "naolidoTr";
            } else {
                $rows[$i]['MSG_LIDO'] = "Documento lido";
                $rows[$i]['CLASS_LIDO'] = "lido";
                $rows[$i]['CLASS_LIDO_TR'] = "lidoTr";
            }

            if (is_null($rows[$i]["DOCM_NR_DOCUMENTO_RED"])) {
                $rows[$i]['MSG_ARQUIVO'] = "Adicionar o arquivo";
                $rows[$i]['CLASS_ARQUIVO'] = "alertaButton";
            } else {
                $rows[$i]['MSG_ARQUIVO'] = "Abrir Documento";
                $rows[$i]['CLASS_ARQUIVO'] = "abrirAnexo";
            }
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOVI_DH_ENCAMINHAMENTO_CHAR']);
            $rows[$i]['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }

        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->title = "Caixa de Documentos Arquivados - $nome";
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->form = $form;
    }

    public function desarquivarpessoalAction() {
        $aNamespace = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_desarquivar = new Zend_Session_Namespace('Ns_Caixapessoal_desarquivar');
        $form = new Sisad_Form_Desarquivar();
        $nome = $aNamespace->nome;
        $data = $this->_getAllParams();

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        if (isset($data['acao']) && $data['acao'] == 'Desarquivar') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento'], 'json', 'arquivados_pessoal');
            $Ns_Caixapessoal_desarquivar->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixapessoal_desarquivar->data_post_caixa)) {
            $data['documento'] = $Ns_Caixapessoal_desarquivar->data_post_caixa;
        }

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        if ($data['Desarquivar'] == 'Desarquivar') {
            if ($form->isValid($data)) {
                try {
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    $docsNaoDesfeitos = array();
                    $docsDesfeitos = array();
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);

                        $dados_input['MATRICULA_CAIXA_PESSOAL'] = $aNamespace->matricula;
                        $dados_input['MOFA_DS_COMPLEMENTO'] = $data['MOFA_DS_COMPLEMENTO'];

                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                        $desarquivamento = $mapperDocumento->desarquivarDocumentoPessoal($dados_input, false);

                        if ($desarquivamento !== true) {
                            $docsNaoDesfeitos[] = $dados_input;
                        } else if ($desarquivamento === true) {
                            $docsDesfeitos[] = $dados_input;
                        }
                    }
                    if (count($docsNaoDesfeitos) > 0) {
                        $this->_helper->flashMessenger(array('message' => "A ação de desarquivar foi cancelada para todos os documentos.", 'status' => 'notice'));
                        foreach ($docsNaoDesfeitos as $docNaoDesfeito) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível desarquivar o documento nº {$docNaoDesfeito['DOCM_NR_DOCUMENTO']}", 'status' => 'error'));
                        }
                        $db->rollBack();
                    } else {
                        foreach ($docsDesfeitos as $docDesfeito) {
                            $this->_helper->flashMessenger(array('message' => "Documento nº {$docDesfeito['DOCM_NR_DOCUMENTO']} desarquivado com sucesso!", 'status' => 'success'));
                        }
                        $db->commit();
                    }
                } catch (exception $e) {
                    $this->_helper->flashMessenger(array('message' => $e->getMessage(), 'status' => 'error'));
                }
                $this->_helper->_redirector('entrada', 'caixapessoal', 'sisad');
            }
        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Desarquivar Documentos Arquivados - $nome";

        $this->view->form = $form;
    }

    public function desfazerAction() {
        $aNamespace = new Zend_Session_Namespace('userNs');
        $this->view->title = "Documentos para desfazer encaminhamento";
        $Ns_Caixaunidade_desfazer = new Zend_Session_Namespace('Ns_Caixaunidade_desfazer');
        $form = new Sisad_Form_DesfazerEncaminhamento();
        $data = $this->getRequest()->getPost();

        if (isset($data['acao']) && $data['acao'] == 'Desfazer') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento'], 'json', 'encaminhados_caixa_pessoal');
            $Ns_Caixaunidade_desfazer->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixaunidade_desfazer->data_post_caixa)) {
            $data['documento'] = $Ns_Caixaunidade_desfazer->data_post_caixa;
        }

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        if ($data['Desfazer'] == 'Desfazer') {
            if ($form->isValid($data)) {
                try {
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    $docsNaoDesfeitos = array();
                    $docsDesfeitos = array();

                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);

                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];

                        $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $data['MODE_CD_MATR_RECEBEDOR'];

                        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataMofaMoviFase["MOFA_DH_FASE"] = $dados_input['MOVI_DH_ENCAMINHAMENTO_CHAR'];

                        $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $dados_input['MODE_CD_SECAO_UNID_DESTINO'];
                        $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $dados_input['MODE_SG_SECAO_UNID_DESTINO'];

                        $dataModoMoviDocumento['MODO_ID_MOVIMENTACAO'] = $dados_input['MOFA_ID_MOVIMENTACAO'];

                        $dataMoviMovimentacao["MOVI_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        ;

                        $dataModpDestPessoa['MODP_ID_MOVIMENTACAO'] = $dados_input["MODP_ID_MOVIMENTACAO"];
                        $dataModpDestPessoa['MODP_SG_SECAO_UNID_DESTINO'] = $dados_input["MODP_SG_SECAO_UNID_DESTINO"];
                        $dataModpDestPessoa['MODP_CD_SECAO_UNID_DESTINO'] = $dados_input["MODP_CD_SECAO_UNID_DESTINO"];
                        $dataModpDestPessoa['MODP_CD_MAT_PESSOA_DESTINO'] = $dados_input["MODP_CD_MAT_PESSOA_DESTINO"];

                        if ($dados_input["MODE_DH_RECEBIMENTO"] == NULL) {
                            $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;
                            $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->desfazerencaminhamento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModoMoviDocumento, $dataModpDestPessoa, false);

                            if ($encaminhaDocumento_retorno !== true) {
                                $dados_input['mensagem_erro'] = $encaminhaDocumento_retorno;
                                $docsNaoDesfeitos[] = $dados_input;
                            } else if ($encaminhaDocumento_retorno === true) {
                                $docsDesfeitos[] = $dados_input;
                                $Ns_Caixaunidade_desfazer->data_post_caixa_executado = $data_post_caixa;
                            }
                        } else {
                            $dados_input['mensagem_erro'] = " já lido na unidade de destino. Solicite-o à unidade de destino que encaminhe de volta.";
                            ;
                            $docsNaoDesfeitos[] = $dados_input;
                        }
                    }
                    if (count($docsNaoDesfeitos) > 0) {
                        $this->_helper->flashMessenger(array('message' => "A ação de desfazer encaminhamento foi cancelada para todos os documentos.", 'status' => 'notice'));
                        foreach ($docsNaoDesfeitos as $docNaoDesfeito) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível desfazer encaminhamento do documento nº {$docNaoDesfeito['DOCM_NR_DOCUMENTO']}: {$docNaoDesfeito['mensagem_erro']}", 'status' => 'error'));
                        }
                        $db->rollBack();
                    } else {
                        foreach ($docsDesfeitos as $docDesfeito) {
                            $this->_helper->flashMessenger(array('message' => "Encaminhamento Desfeito Documento nº {$docDesfeito['DOCM_NR_DOCUMENTO']} ", 'status' => 'success'));
                        }
                        $db->commit();
                    }
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => $e->getMessage(), 'status' => 'error'));
                }
                $this->_helper->_redirector('encaminhados', 'caixapessoal', 'sisad');
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->formInterno = $form;
    }

    public function parecerAction() {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_parecer = new Zend_Session_Namespace('Ns_Caixapessoal_parecer');

        $formParecer = new Sisad_Form_Parecer();
        $this->view->formParecer = $formParecer;

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $formParecer->populate($this->getRequest()->getPost())->getValues());
            if ($anexos->getFileName()) {
                try {
                    $upload = new App_Multiupload_Upload();
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                }
            }
            if (isset($data['acao']) && $data['acao'] == 'Parecer' || !is_null($Ns_Caixapessoal_parecer->data_post_caixa)) {
                $data_post_caixa = $data;
                if (isset($data['acao']) && $data['acao'] == 'Parecer') {
                    $service_juntada = new Services_Sisad_Juntada();
                    $data_post_caixa['documento'] = $service_juntada->completaComApensados($data_post_caixa['documento'], 'json', 'encaminhados_caixa_pessoal');
                    $Ns_Caixapessoal_parecer->data_post_caixa = $data_post_caixa;
                } else if (!is_null($Ns_Caixapessoal_parecer->data_post_caixa)) {
                    $data_post_caixa = $Ns_Caixapessoal_parecer->data_post_caixa;
                }
                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /* Ordenação das paginas */
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column . ' ' . $order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                /* Ordenação */

                if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixapessoal' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'rascunhos') {
                    $cont = 0;
                    $rows = array();
                    foreach ($data_post_caixa['documento'] as $value) {
                        $rows['documento'][$cont] = Zend_Json::decode($value);
                        $cont++;
                    }
                } else {
                    if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixapessoal' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'entrada') {
                        $cont = 0;
                        $rows = array();
                        foreach ($data_post_caixa['documento'] as $value) {
                            $rows['documento'][$cont] = Zend_Json::decode($value);
                            $cont++;
                        }
                    }
                }

                $paginator = Zend_Paginator::factory($rows['documento']);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(count($rows['documento']));

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                $this->view->title = "Parecer em Documento(s) - $userNs->siglalotacao";
            }
            if (isset($data['acao']) && $data['acao'] == 'submitParecer') {

                $data_post_caixa = $Ns_Caixapessoal_parecer->data_post_caixa;
                if ($nrDocsRed["erro"]) {
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('parecer');
                    return;
                }
                if (!$nrDocsRed["existentes"]) {
                    try {
                        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $db->beginTransaction();
                        $DocNaoParecer = array();
                        $docsComParecer = array();
                        foreach ($data_post_caixa['documento'] as $value) {

                            $dados_input = Zend_Json::decode($value);

                            $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];

                            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1011; /* PARECER SISAD */
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                            $nrDocsRed["ID_DOCUMENTO"] = $idDocmDocumento;
                            $nrDocsRed["ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];

                            $mapperDocumento = new Sisad_Model_DataMapper_Documento();

                            if (!$nrDocsRed["incluidos"]) {
                                $parecerDocumento_retorno = $mapperDocumento->parecerDocumento($dataMofaMoviFase, null, null, false);
                                if ($parecerDocumento_retorno !== true) {
                                    $DocNaoParecer[] = $dados_input;
                                } else if ($parecerDocumento_retorno === true) {
                                    $docsComParecer[] = $dados_input;
                                }
                            } else {
                                $parecerDocumento_retorno = $mapperDocumento->parecerDocumento($dataMofaMoviFase, $nrDocsRed, null, false);
                                if ($parecerDocumento_retorno !== true) {
                                    $DocNaoParecer[] = $dados_input;
                                } else if ($parecerDocumento_retorno === true) {
                                    $docsComParecer[] = $dados_input;
                                }
                            }
                        }
                        if (count($DocNaoParecer) > 0) {
                            $this->_helper->flashMessenger(array('message' => "A ação parecer foi cancelada para todos os documentos.", 'status' => 'notice'));
                            foreach ($DocNaoParecer as $docNaoPacercer) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível realizar o parecer para o documento nº {$docNaoPacercer['DOCM_NR_DOCUMENTO']}.", 'status' => 'error'));
                            }
                            $db->rollBack();
                        } else {
                            foreach ($docsComParecer as $docParecer) {
                                $this->_helper->flashMessenger(array('message' => "Parecer realizado com sucesso para o documento nº {$docParecer['DOCM_NR_DOCUMENTO']}.", 'status' => 'success'));
                            }
                            $db->commit();
                        }
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => $exc->getMessage(), 'status' => 'error'));
                        $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
                    }
                } else {
                    foreach ($nrDocsRed["existentes"] as $existentes) {
                        $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->form = $form;
                    $this->render('parecer');
                    return;
                }
                return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
            }
        }
    }

    public function despachoAction() {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_despacho = new Zend_Session_Namespace('Ns_Caixapessoal_despacho');

        $formDespacho = new Sisad_Form_Despacho();
        $this->view->formDespacho = $formDespacho;

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $formDespacho->populate($this->getRequest()->getPost())->getValues());
            if ($anexos->getFileName()) {
                try {
                    $upload = new App_Multiupload_Upload();
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                }
            }
            if (isset($data['acao']) && $data['acao'] == 'Despacho' || !is_null($Ns_Caixapessoal_despacho->data_post_caixa)) {
                $data_post_caixa = $data;
                if (isset($data['acao']) && $data['acao'] == 'Despacho') {
                    $service_juntada = new Services_Sisad_Juntada();
                    $data_post_caixa['documento'] = $service_juntada->completaComApensados($data_post_caixa['documento'], 'json', 'encaminhados_caixa_pessoal');
                    $Ns_Caixapessoal_despacho->data_post_caixa = $data_post_caixa;
                } else if (!is_null($Ns_Caixapessoal_despacho->data_post_caixa)) {
                    $data_post_caixa = $Ns_Caixapessoal_despacho->data_post_caixa;
                }
                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /* Ordenação das paginas */
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column . ' ' . $order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                /* Ordenação */

                if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixapessoal' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'rascunhos') {
                    $cont = 0;
                    $rows = array();
                    foreach ($data_post_caixa['documento'] as $value) {
                        $rows['documento'][$cont] = Zend_Json::decode($value);
                        $cont++;
                    }
                } else {
                    if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixapessoal' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'entrada') {
                        $cont = 0;
                        $rows = array();
                        foreach ($data_post_caixa['documento'] as $value) {
                            $rows['documento'][$cont] = Zend_Json::decode($value);
                            $cont++;
                        }
                    }
                }

                $paginator = Zend_Paginator::factory($rows['documento']);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(count($rows['documento']));

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                $this->view->title = "Despacho em Documento(s) / Processo(s) - $userNs->siglalotacao";
            }
            if (isset($data['acao']) && $data['acao'] == 'submitDespacho') {

                $data_post_caixa = $Ns_Caixapessoal_despacho->data_post_caixa;
                if ($nrDocsRed["erro"]) {
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('despacho');
                    return;
                }
                if (!$nrDocsRed["existentes"]) {
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    $docsNaoDespachados = array();
                    $docsDespachados = array();
                    try {
                        foreach ($data_post_caixa['documento'] as $value) {

                            $dados_input = Zend_Json::decode($value);

                            $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                            $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1040; /* DESPACHO SISAD */
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                            $nrDocsRed["ID_DOCUMENTO"] = $idDocmDocumento;
                            $nrDocsRed["ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];

                            $mapperDocumento = new Sisad_Model_DataMapper_Documento();

                            if (!$nrDocsRed["incluidos"]) {

                                $despachoDocumento_retorno = $mapperDocumento->despachoDocumento($dataMofaMoviFase, null, false);
                                if ($despachoDocumento_retorno !== true) {
                                    $docsNaoDespachados[] = $dados_input;
                                } else if ($despachoDocumento_retorno === true) {
                                    $docsDespachados[] = $dados_input;
                                }
                            } else {
                                $despachoDocumento_retorno = $mapperDocumento->despachoDocumento($dataMofaMoviFase, $nrDocsRed,false);
                            }
                        }
                        if (count($docsNaoDespachados) > 0) {
                            $this->_helper->flashMessenger(array('message' => "A ação de despacho foi cancelada para todos os documentos.", 'status' => 'notice'));
                            foreach ($docsNaoDespachados as $docNaoDespachado) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível despachar o documento nº {$docNaoDespachado['DOCM_NR_DOCUMENTO']}.", 'status' => 'error'));
                            }
                            $db->rollBack();
                        } else {
                            foreach ($docsDespachados as $docDespachado) {
                                $this->_helper->flashMessenger(array('message' => "Despacho do documento nº {$docDespachado['DOCM_NR_DOCUMENTO']} salvo.", 'status' => 'success'));
                            }
                            $db->commit();
                        }
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => $exc->getMessage(), 'status' => 'error'));
                        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                    }
                } else {
                    foreach ($nrDocsRed["existentes"] as $existentes) {
                        $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->form = $form;
                    $this->render('despacho');
                    return;
                }
                return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
            }
        }
    }

    public function tratafiltrodascaixas($filtro) {

        $pesquisa = '';

        if (($filtro['DOCM_ID_PCTT'] != '')) {
            $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
            $pctt = $mapperPctt->getPCTTAjax($filtro['DOCM_ID_PCTT']);
            $filtro['DOCM_ID_PCTT'] = $pctt[0]['AQVP_ID_PCTT'];
        }
        if ($filtro['DOCM_NR_DOCUMENTO'] != '') {
            $docm_nr_documento = $filtro['DOCM_NR_DOCUMENTO'];
            $pesquisa .= (strlen(trim($docm_nr_documento)) == 28) ? ("AND DOCM_NR_DOCUMENTO = $docm_nr_documento") :
                    ("AND TO_NUMBER(SUBSTR(DOCM_NR_DOCUMENTO,-6,6)) = TO_NUMBER(SUBSTR($docm_nr_documento,5))
                           AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = SUBSTR($docm_nr_documento,0,4)");
        }
        if ($filtro['MOVI_CD_SECAO_UNID_ORIGEM'] != '') {
            $pesquisa .= " AND MOVI.MOVI_CD_SECAO_UNID_ORIGEM = " . $filtro['MOVI_CD_SECAO_UNID_ORIGEM'];
        }
        if ($filtro['DOCM_ID_TIPO_DOC'] != '') {
            $pesquisa .= " AND DOCM.DOCM_ID_TIPO_DOC = " . $filtro['DOCM_ID_TIPO_DOC'];
        }
        if ($filtro['DOCM_ID_PCTT'] != '') {
            $pesquisa .= " AND DOCM.DOCM_ID_PCTT = " . $filtro['DOCM_ID_PCTT'];
        }
        #ADICIONADO AO CODIGO EM 12/06 -- INICIO
        if ($filtro['DATA_INICIAL'] != '' && $filtro['DATA_FINAL'] != ''){
            $pesquisa .= "AND DOCM.DOCM_DH_CADASTRO between TO_DATE('".$filtro['DATA_INICIAL']."', 'DD/MM/YYYY') AND TO_DATE('".$filtro['DATA_FINAL']."', 'DD/MM/YYYY')+1-1/24/60/60" ;
        }
        if ($filtro['DATA_INICIAL'] == '' && $filtro['DATA_FINAL']!=''){
            $pesquisa .= " AND DOCM_DH_CADASTRO <= TO_DATE('".$filtro['DATA_FINAL']."', 'DD/MM/YYYY')";
        }
        if ($filtro['DATA_INICIAL'] != '' && $filtro['DATA_FINAL'] == ''){
            $pesquisa .= "AND DOCM_DH_CADASTRO >= TO_DATE('".$filtro['DATA_INICIAL']."', 'DD/MM/YYYY')";
        }
        # --FIM
        
        if ($filtro['DOCM_DS_PALAVRA_CHAVE'] != '') {
            $pesquisa .= " AND (DOCM.DOCM_DS_PALAVRA_CHAVE LIKE '%" . $filtro['DOCM_DS_PALAVRA_CHAVE'] . "%' 
                           OR  DOCM.DOCM_DS_ASSUNTO_DOC LIKE '%" . $filtro['DOCM_DS_PALAVRA_CHAVE'] . "%' )";
        }
        if (count($filtro['CATE_ID_CATEGORIA']) != 0) {
            $categoria = $filtro['CATE_ID_CATEGORIA'];
            $categoria = implode(',', $categoria);
            $pesquisa .= " AND CADO.CADO_ID_CATEGORIA IN ($categoria) AND CADO.CADO_DH_INATIVACAO_CATEGORIA IS NULL";
        }
        if ($filtro['PAPD_CD_MATRICULA_INTERESSADO'] != '') {
            $parte = $filtro['PAPD_CD_MATRICULA_INTERESSADO'];
            $pesquisa .= " AND   DOCM.DOCM_ID_DOCUMENTO IN (
                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_CD_MATRICULA_INTERESSADO = '$parte'
                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN  SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND PAPD_CD_MATRICULA_INTERESSADO =  '$parte'
                                                    
                                                    ) ";
        }

        return $pesquisa;
    }

}
