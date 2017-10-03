<?php

class Sisad_AnexardocController extends Zend_Controller_Action {
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
		
		$this->view->titleBrowser = 'e-Sisad';
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
        
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction() {
        
    }

    public function ajaxtipodocAction() {
        $form = new Sisad_Form_AddTipoDoc();
        $acao = $form->getElement('setAcao');
        $acao->setValue('anexardocspro');

        $this->view->form = $form;
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade ();
        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();

        $id_modulo = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $OcsTbAcaoAcaoSistema = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $rows = $OcsTbAcaoAcaoSistema->getDocsTipoEspecifico($codlotacao, $siglasecao, $id_modulo);

        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $TimeInterval = new App_TimeInterval ();
        $fim = count($rows);
        $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso ();
        for ($i = 0; $i < $fim; $i++) {
            $enderecado = $SadTbPrdcProtDocProcesso->getEnderecamento($rows [$i] ["DOCM_ID_DOCUMENTO"]);
            $protocolo = $SadTbPrdcProtDocProcesso->getProtocolado($rows [$i] ["DOCM_ID_DOCUMENTO"]);
            $rows [$i] ['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows [$i] ['MOVI_DH_ENCAMINHAMENTO_CHAR']);

            $rows [$i] ['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows [$i] ['DADOS_INPUT'] = Zend_Json::encode($rows [$i]);
        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->controle_array = $rows;
        $this->view->idNivel1 = $id_modulo;
    }

    public function anexardocsproAction() {
        /**
         * Variáves de seção
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $flashmessage = array('label' => '', 'status' => '', 'message' => '');

        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $TvdcTipoVincDoc = new Application_Model_DbTable_SadTbTvdcTipoVincDoc();
        $VipdVincProcDigital = new Application_Model_DbTable_SadTbVipdVincProcDigital();
        $VidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $PrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso ();
        $PrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();

        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
        $siglalotacao = $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $Dual = new Application_Model_DbTable_Dual();
            $datahora = $Dual->sysdate();
            $cont_anex = 0;
            $rows = array();
            /**
             * Tira a codificação dos documentos.
             */
            foreach ($data['documento'] as $value) {
                $rows['documento'][$cont_anex] = Zend_Json::decode($value);
                $cont_anex++;
            }
            if (isset($data['acao']) && $data['acao'] == 'Salvar') {

                switch ($data["TP_VINCULO"]) {
                    case 1:
                        $vinculo = 'anexar';
                        $descr = 'anexado';
                        $msg = 'anexado';
                        break;
                    case 2:
                        $vinculo = 'apensar';
                        $descr = 'apensado';
                        $msg = 'apensardo';
                        break;
                    case 3:
                        $vinculo = 'vincular';
                        $descr = 'vinculado';
                        $msg = 'vinculado';
                        break;
//                    case 4:
//                        $vinculo = 'juntar';
//                        $descr = 'juntado';
//                        $msg = 'juntado';
//                        break;
                    default:
                        break;
                }

//                $documentos_principais[$cont_princ] = Zend_Json::decode($data['documentoPrincipal'][0]);
                $cont_anex = 0;
                $cont_princ = 0;
                $documentos_principais = null;
                $documentos_anexos = null;
                $documentos_principais = array();
                $documentos_anexos = array();
                $documentosValidos = true;

                foreach ($data['documentoPrincipal'] as $documentos_p) {
                    $documentos_principais[$cont_princ] = Zend_Json::decode($documentos_p);
                    foreach ($data['documento'] as $value) {
                        $documentos_anexos[$cont_anex] = Zend_Json::decode($value);

                        /**
                         * Verifica se documento principal é o mesmo que está em documentos a serem vinculados.
                         */
                        if ($documentos_principais[$cont_princ]["DOCM_NR_DOCUMENTO"] === $documentos_anexos[$cont_anex]["DOCM_NR_DOCUMENTO"]) {
                            $flashmessage['label'] = 'Notice';
                            $flashmessage['status'] = 'notice';
                            $flashmessage['message'] = 'Documentos Principal Nr:'.$documentos_principais[$cont_princ][DOCM_NR_DOCUMENTO].' não pode ser igual a anexo.';
                            $documentosValidos = false;
                        }
                        /**
                         * Verificar se Documento principal é um PROCESSO ADMINISTRATIVOS e Documentos a serem anexados são diferentes de 
                         * Processo Administrativo e se TIPO DE VINCULO é diferente de ANEXAR;
                         * 
                         * Não é possível Vincular ou Apensar Documentos a um Processo.
                         */ elseif ($documentos_principais[$cont_princ]["DTPD_ID_TIPO_DOC"] == 152 &&
                                $documentos_anexos[$cont_anex]["DTPD_ID_TIPO_DOC"] != $documentos_principais[$cont_princ]["DTPD_ID_TIPO_DOC"] && $data["TP_VINCULO"] != 1) {
                            /**
                             * Não pode anexar documentos a processo
                             */
                            $flashmessage['label'] = 'Notice';
                            $flashmessage['status'] = 'notice';
                            $flashmessage['message'] = 'Não é possivel ' . $vinculo . ' documentos a processo administrativos.';
                            $documentosValidos = false;
                        }
                        /**
                         * Verificar se Documentos Principal NÃO é uma PROCESSO ADMINISTRATIVO e se Documentos para serem anexados é um PROCESSO ADMINSTRATIVO
                         * 
                         * Não é possivel Anexar, Apensar ou Vincular PROCESSOS ADMINISTRATIVOS a Documentos;
                         */ elseif ($documentos_principais[$cont_princ]["DTPD_ID_TIPO_DOC"] != 152 && $documentos_anexos[$cont_anex]["DTPD_ID_TIPO_DOC"] == 152) {
                            /**
                             * Não pode anexar processos a documento.
                             */
                            $flashmessage['label'] = 'Notice';
                            $flashmessage['status'] = 'notice';
                            $flashmessage['message'] = 'Não é possivel ' . $vinculo . ' processos administrativos a documentos.';
                            $documentosValidos = false;
                        }
                        /**
                         * Verificar se Documento Principal e anexo é um PROCESSO ADMINSITRATIVO, Caso posivito adciona o anexo ao array de 
                         * documentos que serão vinculados ao processo.
                         */ elseif ($documentos_principais[$cont_princ]["DTPD_ID_TIPO_DOC"] == 152 && $documentos_anexos[$cont_anex]["DTPD_ID_TIPO_DOC"] == 152) {
                            $idPrinc = $PrdiProcessoDigital->getProcesso($documentos_principais[$cont_princ]["DOCM_ID_DOCUMENTO"]);
                            $idVinc = $PrdiProcessoDigital->getProcesso($documentos_anexos[$cont_anex]["DOCM_ID_DOCUMENTO"]);

                            $vinc_proc_proc[$cont_anex]["VIPD_ID_PROCESSO_DIGITAL_PRINC"] = $idPrinc["PRDI_ID_PROCESSO_DIGITAL"];
                            $vinc_proc_proc[$cont_anex]["VIPD_ID_PROCESSO_DIGITAL_VINDO"] = $idVinc["PRDI_ID_PROCESSO_DIGITAL"];
                            $vinc_proc_proc[$cont_anex]["VIPD_ID_TP_VINCULACAO"] = $data["TP_VINCULO"];
                            $vinc_proc_proc[$cont_anex]["VIPD_DH_VINCULACAO"] = $datahora;
                            $vinc_proc_proc[$cont_anex]["VIPD_CD_MATR_VINCULACAO"] = $userNs->matricula;
                            $vinc_proc_proc[$cont_anex]["VIPD_NR_VOL_PRINCIPAL"] = null;
                            $vinc_proc_proc[$cont_anex]["VIPD_NR_FOLHA_PRINCIPAL"] = null;

                            $idMoviMovimentacao = $documentos_principais[$cont_princ]["MOFA_ID_MOVIMENTACAO"];
                            $vipdMoviMovimentacao["AUX"] = $descr;
                            $vipdMoviMovimentacao["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                            $vipdMoviMovimentacao["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $vipdMoviMovimentacao["MOFA_DS_COMPLEMENTO"] = "Processo " . $descr . " a Processo";
                        }

                        /**
                         * Veirifcar se Documentos Principal e anexo é diferente de PROCESSO ADMINISTRATIVO, 
                         * Caso positivo, adiciona o anex ao array de documentos que serão vinculados a outro documento.
                         */ elseif ($documentos_principais[$cont_princ]["DTPD_ID_TIPO_DOC"] != 152 && $documentos_anexos[$cont_anex]["DTPD_ID_TIPO_DOC"] != 152) {

                            $vincDocDoc[$cont_anex]['VIDC_ID_DOC_PRINCIPAL'] = $documentos_principais[$cont_princ]["DOCM_ID_DOCUMENTO"];
                            $vincDocDoc[$cont_anex]['VIDC_ID_DOC_VINCULADO'] = $documentos_anexos[$cont_anex]["DOCM_ID_DOCUMENTO"];
                            $vincDocDoc[$cont_anex]['VIDC_ID_TP_VINCULACAO'] = $data["TP_VINCULO"];
                            $vincDocDoc[$cont_anex]['VIDC_DH_VINCULACAO'] = $datahora;
                            $vincDocDoc[$cont_anex]['VIDC_CD_MATR_VINCULACAO'] = $userNs->matricula;

                            $idMoviMovimentacao = $documentos_principais[$cont_princ]["MOFA_ID_MOVIMENTACAO"];
                            $vincMofaFase["AUX"] = $descr;
                            $vincMofaFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                            //Fase 1031 - Anexar documento a documento
                            $vincMofaFase["MOFA_ID_FASE"] = 1031;
                            $vincMofaFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $vincMofaFase["MOFA_DS_COMPLEMENTO"] = "Documento " . $descr . " a documento";
                        }
                        /**
                         * Verificar se Documento Principal é um PROCESSO ADMINISTRATIVO e se Anexo não é uma PROCESSO ADMINISTRATIVO 
                         * e tipo de vinculação é igual a ANEXAR.
                         * 
                         * Caso positivo, insere o documento no array de documentos que serão anexados ao processo administrativo.
                         */ elseif ($documentos_principais[$cont_princ]["DTPD_ID_TIPO_DOC"] == 152 && $documentos_anexos[$cont_anex]["DTPD_ID_TIPO_DOC"] != 152 && $data["TP_VINCULO"] == 1) {

                            $idMoviMovimentacao = $documentos_principais[$cont_princ]["MOFA_ID_MOVIMENTACAO"];
                            $idDocmDocumentoPrincipal = $documentos_principais[$cont_princ]["DOCM_ID_DOCUMENTO"];

                            $dataPrdiProcessoDigital = $PrdiProcessoDigital->getProcesso($idDocmDocumentoPrincipal);
                            $idProcessoDigital = $dataPrdiProcessoDigital["PRDI_ID_PROCESSO_DIGITAL"];

                            $prdiMofaFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                            $prdiMofaFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $prdiMofaFase["MOFA_DS_COMPLEMENTO"] = "Documento " . $descr . " a Processo";

                            $prdiDocumentosVincular[$cont_anex] = $documentos_anexos[$cont_anex];
                        }
                        $cont_anex++;
                    }
                    $cont_princ++;
                }

                /**
                 * Verifica se os documentos são válidos, caso positivo, inicia o procedimento de vinculação.
                 */
                if ($documentosValidos) {
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    try {
                        /**
                         * Vincula Processos a processo
                         */
                        if ($vinc_proc_proc) {
                            $vinculacao_proc = $VipdVincProcDigital->setVincProcProc($vinc_proc_proc, $vipdMoviMovimentacao);
                        }
                        /**
                         * Vincula Documentos a documento
                         */ elseif ($vincDocDoc) {
                            $vinculacao_docs = $VidcVinculacaoDoc->setVioncDocDoc($vincDocDoc, $vincMofaFase);
                        }
                        /**
                         * Vincula Documentos a Processo
                         */ elseif ($idProcessoDigital) {
                            $PrdiProcessoDigital->addDocsProcesso($idProcessoDigital, $prdiMofaFase, $prdiDocumentosVincular);
                        }

                        $db->commit();
                        $this->_helper->flashMessenger(array('message' => "Documentos / Processos " . $msg . " com sucesso!", 'status' => 'success'));
                        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                    } catch (Exception $exc) {
                        echo $exc->getMessage();

                        $db->rollBack();
                        $this->_helper->flashMessenger(array('message' => "Problemas ao " . $vinculo . " documento", 'status' => 'error'));
                        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                    }
                }
            }

            /**
             * Form
             */
            $form = new Sisad_Form_AddTipoDoc();

            $DTPD_CD_TP_DOC_NIVEL_1 = $form->getElement('DTPD_CD_TP_DOC_NIVEL_1');
            $DTPD_CD_TP_DOC_NIVEL_1->setLabel('*Tipo de Documento');
            $DTPD_CD_TP_DOC_NIVEL_1->setDescription('Selecione o tipo de documeto para filtro.');

            $Adicionar = new Zend_Form_Element_Select('TP_VINCULO');
            $form->addElement($Adicionar);
            $vinc = $form->getElement('TP_VINCULO');
            $vinc->setLabel('*Tipo de Vinculo');

            $vincDocs = $TvdcTipoVincDoc->getTipoVincDoc();
            foreach ($vincDocs as $vincDocs_p) {
                $vinc->addMultiOptions(array($vincDocs_p['TVDC_ID_TP_VINCULACAO'] => $vincDocs_p['TVDC_DS_TP_VINCULACAO']));
            }
            $vinc->setDescription('Selecione o tipo de vinculação dos documentos.');
            $this->view->form = $form;
            /**
             * Tratamentos para reutilização do form de parecer
             */
            /* paginação */
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /* Ordenação das paginas */
            $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /* Ordenação */

            $TimeInterval = new App_TimeInterval ();
            $fim = count($rows['documento']);
            for ($cont_anex = 0; $cont_anex < $fim; $cont_anex++) {
                $enderecado = $PrdcProtDocProcesso->getEnderecamento($rows['documento'] [$cont_anex] ["DOCM_ID_DOCUMENTO"]);
                $protocolo = $PrdcProtDocProcesso->getProtocolado($rows['documento'] [$cont_anex] ["DOCM_ID_DOCUMENTO"]);
                $rows['documento'] [$cont_anex] ['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows['documento'] [$cont_anex] ['MOVI_DH_ENCAMINHAMENTO_CHAR']);

                $rows['documento'] [$cont_anex] ['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
                $rows['documento'] [$cont_anex] ['DADOS_INPUT'] = $data['documento']['DADOS_INPUT'];
            }
            $paginator = Zend_Paginator::factory($rows['documento']);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(count($rows['documento']));
            $this->view->dataDocumentos = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            $this->view->title = "Adicionar documentos a um documento - $siglalotacao";
            $this->view->siglalotacao = $siglalotacao;
            unset($data['DTPD_CD_TP_DOC_NIVEL_1']);
            $form->populate($data);
            $this->view->flashmessage = $flashmessage;
            $this->view->formParecer = $form;
        }
    }

//    public function anexaresostiAction() {
//        /**
//         * Variáves de seção
//         */
//        $userNs = new Zend_Session_Namespace('userNs');
//        $flashmessage = array('label' => '', 'status' => '', 'message' => '');
//
//        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
//        $TvdcTipoVincDoc = new Application_Model_DbTable_SadTbTvdcTipoVincDoc();
//        $VipdVincProcDigital = new Application_Model_DbTable_SadTbVipdVincProcDigital();
//        $VidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
//        $PrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso ();
//        $PrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
//
//        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
//        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
//        $siglalotacao = $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();
//
//        if ($this->getRequest()->isPost()) {
//            $data = $this->getRequest()->getPost();
//            $Dual = new Application_Model_DbTable_Dual();
//            $datahora = $Dual->sysdate();
//            /**
//             * Tira a codificação dos documentos.
//             */
//            if (isset($data['acao']) && $data['acao'] == 'Salvar') {
//                try {
//                    $docPrincipal = Zend_Json::decode($data['solicitacao'][0]);
//                    $i = 0;
//                    $documentos = array();
//                    $documentosValidos = true;
//
//                    foreach ($data['documento'] as $value) {
//                        $documentos[$i] = Zend_Json::decode($value);
//
//                        /**
//                         * Veirifcar se Documentos Principal e anexo é diferente de PROCESSO ADMINISTRATIVO, 
//                         * Caso positivo, adiciona o anex ao array de documentos que serão vinculados a outro documento.
//                         */
//                        $vincDocDoc[$i]['VIDC_ID_DOC_PRINCIPAL'] = $docPrincipal["DOCM_ID_DOCUMENTO"];
//                        $vincDocDoc[$i]['VIDC_ID_DOC_VINCULADO'] = $documentos[$i]["DOCM_ID_DOCUMENTO"];
//                        $vincDocDoc[$i]['VIDC_ID_TP_VINCULACAO'] = $data["TP_VINCULO"];
//                        $vincDocDoc[$i]['VIDC_DH_VINCULACAO'] = $datahora;
//                        $vincDocDoc[$i]['VIDC_CD_MATR_VINCULACAO'] = $userNs->matricula;
//
//                        $idMoviMovimentacao = $docPrincipal["MOFA_ID_MOVIMENTACAO"];
//                        $vincMofaFase["AUX"] = $descr;
//                        $vincMofaFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
//                        $vincMofaFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
//                        //Fase 1035 - Anexar Documentos a Solicitação (E-sosti)
//                        $vincMofaFase["MOFA_ID_FASE"] = 1035;
//                        $vincMofaFase["MOFA_DS_COMPLEMENTO"] = "Documento " . $descr . " a documento";
//                        $i++;
//                    }
//                    $db->commit();
//                    $this->_helper->flashMessenger(array('message' => "Documentos / Processos anexados a uma solicitação com sucesso!", 'status' => 'success'));
//                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
//                } catch (Exception $exc) {
//                    /**
//                     * Não pode anexar processos a documento.
//                     */
//                    $db->rollBack();
//                    $flashmessage['label'] = 'Notice';
//                    $flashmessage['status'] = 'notice';
//                    $flashmessage['message'] = 'Não é possivel anexar documentos a solicitação';
//                    $documentosValidos = false;
//                }
//            }
//
//            /**
//             * Verifica se os documentos são válidos, caso positivo, inicia o procedimento de vinculação.
//             */
//            if ($documentosValidos) {
//                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//                $db->beginTransaction();
//                try {
//                    /**
//                     * Vincula Documentos a documento
//                     */ if ($vincDocDoc) {
//                        $vinculacao_docs = $VidcVinculacaoDoc->setVincDocDoc($vincDocDoc, $vincMofaFase);
//                    }
//
//                    $db->commit();
//                    $this->_helper->flashMessenger(array('message' => "Documentos / Processos " . $msg . " ao Documento nr. " . $docPrincipal[DOCM_NR_DOCUMENTO] . " com sucesso!", 'status' => 'success'));
//                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
//                } catch (Exception $exc) {
//                    echo $exc->getMessage();
//
//                    $db->rollBack();
//                    exit;
//                    $this->_helper->flashMessenger(array('message' => "Problemas ao " . $vinculo . " documento", 'status' => 'error'));
//                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
//                }
//            }
//            /**
//             * Solicitaçõe em aberto
//             */
//            /* paginação */
//            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
//
//            /* Ordenação das paginas */
//            $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
//            $order_direction = $this->_getParam('direcao', 'ASC');
//            $order = $order_column . ' ' . $order_direction;
//            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
//            /* Ordenação */
//
//            $aNamespace = new Zend_Session_Namespace('userNs');
//            $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
//            $rows = $dados->getMinhasSolicitacoesAtendimento($aNamespace->matricula, $order, '', '');
//
//            /* verifica condições e faz tratamento nos dados */
//            $TimeInterval = new App_Sosti_TempoSla();
//            $fim = count($rows);
//            for ($i = 0; $i < $fim; $i++) {
//                $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['DOCM_DH_CADASTRO'], '', '07:00:00', '20:00:00');
//                unset($rows[$i]['DOCM_DH_CADASTRO']);
//                unset($rows[$i]['DATA_ATUAL']);
//                $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
//            }
//
//
//            $paginator = Zend_Paginator::factory($rows);
//            $paginator->setCurrentPageNumber($page)
//                    ->setItemCountPerPage(15);
//
//            $this->view->ordem2 = $order_column;
//            $this->view->direcao2 = $order_direction;
//            $this->view->data2 = $paginator;
//            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
//
//
//            /**
//             * Documentos recebidos da Caixa unidade
//             */
//            $i = 0;
//            $rows = array();
//            foreach ($data['documento'] as $value) {
//                $rows['documento'][$i] = Zend_Json::decode($value);
//                $i++;
//            }
//            $form = new Sisad_Form_AddTipoDoc();
//            $this->view->form = $form;
//            /**
//             * Tratamentos para reutilização do form de parecer
//             */
//            /* paginação */
//            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
//            /* Ordenação das paginas */
//            $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
//            $order_direction = $this->_getParam('direcao', 'DESC');
//            $order = $order_column . ' ' . $order_direction;
//            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
//            /* Ordenação */
//
//            $TimeInterval = new App_TimeInterval ();
//            $fim = count($rows['documento']);
//            for ($i = 0; $i < $fim; $i++) {
//                $enderecado = $PrdcProtDocProcesso->getEnderecamento($rows['documento'] [$i] ["DOCM_ID_DOCUMENTO"]);
//                $protocolo = $PrdcProtDocProcesso->getProtocolado($rows['documento'] [$i] ["DOCM_ID_DOCUMENTO"]);
//                $rows['documento'] [$i] ['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows['documento'] [$i] ['MOVI_DH_ENCAMINHAMENTO_CHAR']);
//
//                $rows['documento'] [$i] ['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
//                $rows['documento'] [$i] ['DADOS_INPUT'] = $data['documento']['DADOS_INPUT'];
//            }
//            $paginator = Zend_Paginator::factory($rows['documento']);
//            $paginator->setCurrentPageNumber($page)
//                    ->setItemCountPerPage(count($rows['documento']));
//            $this->view->dataDocumentos = $paginator;
//            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
//
//            $this->view->title = "Adicionar documentos a um documento - $siglalotacao";
//            $this->view->siglalotacao = $siglalotacao;
//            $form->populate($data);
//            $this->view->flashmessage = $flashmessage;
//            $this->view->formParecer = $form;
//        }
//    }
    
    public function inseriranexosAction(){
        /*
         * Configurações
         * TEMPO máximo de upload 1hora
         */
        set_time_limit( 3600 );
        
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();/*Aplica Filtros - Mantem Post*/
            $anexos = new Zend_File_Transfer_Adapter_Http();
            $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
            
            
            if(isset ($data["salvar"]) && $data["salvar"] === 'Salvar'){
                
                Zend_Debug::dump($data);
                Zend_Debug::dump($anexos->getFileName());
                exit;
            }
            
            
            $cont_anex = 0;
            foreach ($data['documento'] as $value) {
                $rows[$cont_anex] = Zend_Json::decode($value);
                $cont_anex++;
            }
            
            /*paginação*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

            /*Ordenação das paginas*/
//            $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
//            $order_direction = $this->_getParam('direcao', 'DESC');
//            $order = $order_column.' '.$order_direction;
//            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /*Ordenação*/

//           $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
//           $rows = $dados->getCaixaPessoalRascunhos($userNs->codlotacao,$userNs->siglasecao,$userNs->matricula,$order);

            $TimeInterval = new App_TimeInterval();
            $fim =  count($rows);
            for ($i = 0; $i<$fim; $i++ ) {
                $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['DOCM_DH_CADASTRO_CHAR']);
                $rows[$i]['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
                $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
            }

           $paginator = Zend_Paginator::factory($rows);
           $paginator->setCurrentPageNumber($page)
                     ->setItemCountPerPage(15);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');


            $this->view->title = "Caixa Rascunhos - Inserir Anexos";
        
        }
    }

}
