<?php

class Sosti_GarantiaController extends Zend_Controller_Action {
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
		
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
        $this->Negocio_NegociaGarantiaDesenvolvimento = new Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento();
    }

    public function indexAction() {
        /*         * ******************
         * Variáves de sessão 
         * ****************** */
        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);

        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array('direcao' => 'ASC', 'ordem' => 'NEGA_DH_ACEITE_RECUSA_DATE', 'itemsperpage' => 15, 'page' => 1);
        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);

        /**
         * Inicialização de variáveis 
         */
        $title = "GARANTIA DESENVOLVIMENTO - CONTROLE DIVERGENCIA";
        $this->view->title = $title;
        /**
         * Tratando o Form
         */
        $form_valores_padrao = $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->getValues();
        $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->confFormFiltro();

        /**
         * Para zerar o filtro
         */
        if ($this->_getParam('nova') === '1') {
            unset($NsAction->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }


        /**
         * Submissão do filtro
         */
        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();

            /**
             * Validação de filtro Vazio
             */
            $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->populate($data_pesq);
            if ($form_valores_padrao == $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->getValues()) {
                $this->view->form = $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia;
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->isValid($data_pesq)) {
                $chaves_nulas = array_flip(array_keys($this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->getValues()));
                foreach ($chaves_nulas as $key => $value) {
                    $chaves_nulas[$key] = null;
                }
                $data_pesq = array_merge($chaves_nulas, $this->getRequest()->getPost());
                $NsAction->data_pesq = $data_pesq;
            } else {
                /**
                 * Populando o formulário inválido
                 */
                $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->populate($data_pesq);
                $this->view->form = $form;
                return;
            }
        }

        /*
         * Aplicação do filtro caso ele seja válido
         */
        $data_pesq = $NsAction->data_pesq;
        $post_data_pesq = $data_pesq;
        if (!is_null($data_pesq)) {
            /**
             * Auxilia a view a tratar a ausencia de registros e esconder botões
             */
            $this->view->ultima_pesq = true;

            /* paginação */
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            /* Ordenação das paginas */
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /**
             * Chama o método de pesquisa
             */
            $rows = $this->Negocio_NegociaGarantiaDesenvolvimento->getCaixaGarantiaDivergPesq($data_pesq, $order);

            /**
             * Configura o Zend paginator
             */
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage($itemCountPerPage);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');

            /**
             * Popula o filtro com a última pesquisa
             */
            $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->populate($post_data_pesq);
        } else {
            /**
             * Caso não exista filtro execulta a listagem normal sem filtro
             */
            /* paginação */
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();

            /* Ordenação das paginas */
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /* Ordenação */

            /**
             * Chama o método padrão da caixa sem filtro
             */
            $data_pesq["SOMENTE_PRINCIPAL"] = "N";
            $data_pesq["NEGA_IC_CONCORDANCIA"] = "NAV";
            $chaves_nulas = array_flip(array_keys($this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->getValues()));
            foreach ($chaves_nulas as $key => $value) {
                $chaves_nulas[$key] = null;
            }

            $data_pesq = array_merge($chaves_nulas, $data_pesq);
            $rows = $this->Negocio_NegociaGarantiaDesenvolvimento->getCaixaGarantiaDivergPesq($data_pesq, $order);
            /*             * ****************************
             * Configura o Zend paginator
             * *************************** */
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage($itemCountPerPage);
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        }
        $this->view->form = $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia;
    }

    public function concordarAction() {
        /*         * ********
         * Models
         * ******** */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        /*         * ******************
         * Variáves de sessão 
         * ****************** */
        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);
        $this->view->NsAction = $NsAction;
        /*         * ********
         * Form
         * ******** */
        $form = $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia;
        $this->view->form = $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia;
        $Form_Anexo = new Sosti_Form_Anexo();
        $Form_Anexo->anexoUnico();
        $this->Negocio_NegociaGarantiaDesenvolvimento->confForm(Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ACAO_CONCORDANCIA);
        $form->addElement($Form_Anexo->getElement("DOCM_DS_HASH_RED"));
        $this->Negocio_NegociaGarantiaDesenvolvimento->_Bd_form_garantia->addSubmitInput();
        /*         * **********************
         * Classes da aplicação 
         * ********************* */
        //$app_Email = new App_Email();
        $Sosti_Anexo = new App_Sosti_Anexo();
        $msg_to_user = "";

        if ($this->getRequest()->isPost()) {
            /**
             * Aplica os filtros do formulário
             */
            $data = $this->getRequest()->getPost();
            if ($data['acao'] == 'Avaliar') {
                /**
                 * Recupera todos os dados da solicitação sobescrevendo os valores vindos da caixa de entrada 
                 */
                $data["solicitacao"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesGarantiaJson($data["solicitacao"]);
                $data["garantiasolicitacao"] = $data["solicitacao"];
                /**
                 * Mantem os dados originais do post da caixa de atendimento 
                 */
                $NsAction->dadosCaixa = $data;
                /**
                 * Vinculação 
                 */
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao'], true);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                foreach ($data['solicitacao'] as $keyFamilia => $valueFamilia) {
                    $valueFamilia = Zend_Json::decode($valueFamilia);
                    foreach ($data["garantiasolicitacao"] as $valueGarantia) {
                        $valueGarantia = Zend_Json::decode($valueGarantia);
                        if ($valueGarantia["SSOL_ID_DOCUMENTO"] == $valueFamilia["SSOL_ID_DOCUMENTO"]) {
                            $valueFamilia["MOVI_ID_MOVIMENTACAO"] = $valueGarantia["MOVI_ID_MOVIMENTACAO"];
                        }
                    }

                    $data['solicitacao'][$keyFamilia] = Zend_Json::encode($valueFamilia);
                }
                /**
                 * Faz tramento da vinculação e mantem as solicitações vindas da caixa 
                 */
                $data["solicitacao"] = $SosTbSsolSolicitacao->getDadosVariasSolicitacoesGarantiaJson($data["solicitacao"]);
                $NsAction->dadosCaixaSolicitacoes = $data["solicitacao"];
                /**
                 * Variáves da view 
                 */
                $this->view->title = $NsAction->dadosCaixa["title"] . " - CONCORDÂNCIA";
                $this->view->data = $NsAction->dadosCaixaSolicitacoes;
            } else {

                if ($form->isValid($data)) {


                    /*                     * Aplica Filtros - Mantem Post */
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    /*                     * Aplica Filtros - Mantem Post */


                    /* Inclusão no red */
                    $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
                    /**
                     *  Verifica se foi feito o upload do arquivo se sim então insere o arquivo no red
                     */
                    if ($docm_ds_hash_red->isUploaded()) {
                        try {
                            $nr_documento_red = null;
                            $nr_documento_red = $Sosti_Anexo->anexa($docm_ds_hash_red);
                            if (is_array($nr_documento_red)) {
                                $exc = new Exception(implode(' - ', $nr_documento_red));
                                throw $exc;
                            }
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel inserir anexo na solicitação ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Mensagem do repositório:<br></b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                            $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                        }
                    }
                    /*                     * *Inclusão no red */

                    foreach ($NsAction->dadosCaixaSolicitacoes as $d) {
                        try {
                            $dados_input = Zend_Json::decode($d);

                            /* tratamento de dados */
                            $idSolicitacao = $dados_input["SSOL_ID_DOCUMENTO"];
                            $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];

                            $dadosArrayConcordaGarantia = $this->Negocio_NegociaGarantiaDesenvolvimento->prepareEntradaMetodoFromArray($data, Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ACAO_CONCORDANCIA, Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::PADRAO_METODO_IGNORE, $dadosArrayConcordaGarantia);
                            $dadosArrayConcordaGarantia["NEGA_ID_MOVIMENTACAO"] = $dados_input["NEGA_ID_MOVIMENTACAO"];

                            if (!is_null($nr_documento_red)) {
                                unset($nrDocsRed);
                                /*
                                 * Consulta a solicitação e vefirica se a mesma ja possui o documento incluido, 
                                 * caso possua, apresenta uma msg de notificação.
                                 */
                                $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO =  $idSolicitacao AND ANEX_NR_DOCUMENTO_INTERNO = " . $nr_documento_red);
                                if (!is_null($SadTbAnexAnexofetchRow)) {
                                    $this->_helper->flashMessenger(array('message' => 'O arquivo ' . substr($docm_ds_hash_red->getFileName(), strrpos($docm_ds_hash_red->getFileName(), 'temp') + strlen('temp') + 1) . ' já é anexo da solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '!', 'status' => 'notice'));
                                } else {
                                    $nrDocsRed[] = $nr_documento_red;
                                    $anexAnexo['ANEX_ID_DOCUMENTO'] = $idSolicitacao;
                                }
                            }
                            /*                             * *tratamento de dados */

                            /* Inclusão no banco */
                            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                            $db->beginTransaction();
                            try {
                                $this->Negocio_NegociaGarantiaDesenvolvimento->setConcordaGarantia($dadosArrayConcordaGarantia);
                                $SosTbSsolSolicitacao->setIncluirVariosAnexo($anexAnexo, $nrDocsRed);
                                $db->commit();
                            } catch (Exception $exc) {
                                $db->rollBack();
                                throw $exc;
                            }

                            /*                             * *Inclusão no banco */

                            /* mensagem para o usuário */
                            $msg_to_user =  $nrdocumento ;
                            
                         
                            /*                             * *mensagem para o usuário */

                            /* Trecho comentado em 09/09/2013 pois o metodo concordanciaSolicitacao() não existe na App_Email e estava
                             * gerando erro quando o usuário salva uma garantia. */
                            /* Envio de email normal */
                            /*$arrayDados = array(
                                'destinatario' => $dados_input["NEGA_CD_MATR_CONCORDANCIA"]
                                , 'solicitacao' => $dados_input['DOCM_NR_DOCUMENTO']
                                , 'dataSolicitacao' => $dados_input['DOCM_DH_CADASTRO']
                                , 'tipoServico' => $dados_input['SSER_DS_SERVICO']
                                , 'descricaoConcordancia' => $data["NEGA_DS_JUSTIFICATIVA_CONCOR"]);
                            try {
                               // $app_Email->concordanciaSolicitacao($arrayDados);
                            } catch (Exception $exc) {
                                $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                            }*/
                            /**
                             * Fim do envio de email
                             */
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel avaliar a solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'error'));
                        }
                    }

                    $this->_helper->flashMessenger(array('message' => 'Solicitação ' . $msg_to_user . ' avaliada!', 'status' => 'success'));
                    /* redirecionamento */
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'], $NsAction->dadosCaixa['controller'], $NsAction->dadosCaixa['module']);
                    /*                     * *redirecionamento */
                } else {
                    $this->view->title = $NsAction->dadosCaixa["title"] . " - CONCORDÂNCIA";
                    $this->view->data = $NsAction->dadosCaixaSolicitacoes;

                    $form->getElement('NEGA_DS_JUSTIFICATIVA_CONCOR')->removeFilter('HtmlEntities');
                    if ($form->getElement('NEGA_DS_JUSTIFICATIVA_CONCOR')->hasErrors()) {
                        $form->getElement('NEGA_DS_JUSTIFICATIVA_CONCOR')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $form->populate($data);
                }
            }
        }
    }

}
