<?php

class Sosti_MinhassolicitacoesController extends Zend_Controller_Action {

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

        $this->view->titleBrowser = 'e-Sosti';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
        $this->view->module = $this->getRequest()->getModuleName();
    }

    public function pendenteavaliacaoAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getSolicitacoesPendenteAvaliacao($order);

        $fim = count($rows);


//        for ($i = 0; $i < $fim; $i++) 
//        {
//            $id = $rows[$i]['SSOL_ID_DOCUMENTO'];
//            $d2 = $dados->getAnalistaResponsavel($id);
//            $rows[$i]['ENCAMINHADOR'] = $d2[$i]['ENCAMINHADOR'];
//            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
//        }


        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOFA_DH_FASE'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Solicitações Pendentes de Avaliação";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function atendimentoAction() {
        $userNs = new Zend_Session_Namespace('userNs');

        $data = $this->getRequest()->getPost();
        if ($data['acao'] == 'assinaEquip') {
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase ();
            $dual = new Application_Model_DbTable_Dual();
            //zend_debug::dump($data);
            $solicitacaoInfo = explode(':', $data['documentosSelecionados']);

            $dataMofaMoviFase ["MOFA_ID_MOVIMENTACAO"] = $solicitacaoInfo[1];
            $dataMofaMoviFase ["MOFA_DH_FASE"] = $dual->sysdate();
            $dataMofaMoviFase ["MOFA_ID_FASE"] = 1049; /* ASSINATURA POR SENHA EQUIPAMENTO SOSTI */
            $dataMofaMoviFase ["MOFA_CD_MATRICULA"] = $userNs->matricula;
            $dataMofaMoviFase ["MOFA_DS_COMPLEMENTO"] = 'Equipamento assinado por senha por <strong>' . $userNs->nome . '</strong>';


            try {
                $SadTbMofaMoviFase->createRow($dataMofaMoviFase)->save();
                $this->_helper->flashMessenger(array('message' => "Equipamento foi assinado com sucesso.", 'status' => 'success'));
                $this->_helper->_redirector('atendimento', 'minhassolicitacoes', 'sosti');
            } catch (Exception $e) {
                $this->_helper->flashMessenger(array('message' => "Erro ao asinnar Equipamento. ERRO:" . $e->getMessage(), 'status' => 'error'));
                $this->_helper->_redirector('atendimento', 'minhassolicitacoes', 'sosti');
            }
        }


       
        //FORM VERIFICA USUÁRIO
        $form = new Sisad_Form_Verify();
        $form->getElement('COU_COD_MATRICULA')->setValue($userNs->matricula);
        $this->view->formVerificar = $form;

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'DH_CADASTRO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesAtendimento($userNs->matricula, $order, '', '');

        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['DOCM_DH_CADASTRO'], '', '07:00:00', '20:00:00');
            $rows[$i]['CONTROLLER'] = $this->getRequest()->getControllerName();
            $rows[$i]['ACTION'] = $this->getRequest()->getActionName();

            unset($rows[$i]['DOCM_DH_CADASTRO']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

            /* paginação */
            //$page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            $this->view->ultima_pesq = true;
            $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
            $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'TEMPO_TOTAL', 'itemsperpage' => 50, 'page' => 1);
            $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemCountPerPage);
            /* // Fim do paginator// */

        $this->view->title = "Minhas Solicitações de TI em Atendimento";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function baixadasAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesBaixadas($userNs->matricula, $order);

        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['DOCM_DH_CADASTRO'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['DOCM_DH_CADASTRO']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Minhas Solicitações de TI Baixadas";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function homologacaoAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesHomologacao($userNs->matricula, $order);
        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOFA_DH_FASE'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Minhas Solicitações para Homologação";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function homologacaoaddAction() {
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
        $this->view->NsAction = $NsAction;

        /**
         * Forms
         */
        $form = new Sosti_Form_HomologacaoServico();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;


        /*         * **************** 
         * MODELS
         * *************** */
        /**
         * INSTANCIA DA CLASSE PARA OBTER OS DADOS DAS SOLICITAÇÕES
         */
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();

        /**
         * INSTANCIA DA CLASSE PARA OBTER ACOMPANHANTES DO SOSTI
         */
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();


        if ($this->getRequest()->isPost()) {
            /**
             * Chaves padrões de entrada
             */
            $chaves_dados = array('acao' => NULL, 'salvar' => NULL);
            $data = array_merge($chaves_dados, $this->getRequest()->getPost());

            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Homologar') {
                $NsAction->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $this->view->title = "Homologar Solicitação";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    $form->ANEXOS->receive();
                    $nrDocsRed = null;

                    if (!is_null($data["ANEXOS"])/* $form->ANEXOS->isReceived() */) {
                        try {
                            $upload = new App_Multiupload_NewMultiUpload();
                            $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
                        }
                    }

                    if (($data["Salvar"] == 'Salvar') && ($data["homologacao"] == 1)) { //HOMOLOGAR
                        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

                        foreach ($NsAction->dados as $d) {
                            $dados = Zend_Json_Decoder::decode($d);

                            $dataHomologa["MOFA_ID_MOVIMENTACAO"] = $dados["MOFA_ID_MOVIMENTACAO"];
                            $dataHomologa["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataHomologa["MOFA_DS_COMPLEMENTO"] = $data["descricao"];
                            $dataHomologa["MOFA_ID_FASE"] = Trf1_Sosti_Definicoes::FASE_HOMOLOGADO_SOLICITACAO_TI; // HOMOLOGADO

                            $msg = $dados["DOCM_NR_DOCUMENTO"];
                            $idSolicitacao = $dados["SSOL_ID_DOCUMENTO"];
                            $nrdocumento = $dados["DOCM_NR_DOCUMENTO"];

                            $SosTbSsolSolicitacao->setHomologarSos($dataHomologa, $idSolicitacao, $nrDocsRed);


                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $remetente = 'noreply@trf1.jus.br';
                            $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                            #$destinatario = 'tr18757ps@trf1.jus.br';
                            $descricaoTipoInformação = 'Solicitação Homologada.';

                            $assunto = 'Homologação de Solicitação';
                            $corpo = "$descricaoTipoInformação</p>
                                            Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados['DOCM_DH_CADASTRO'] . "\"><b>" . $dados['DOCM_DH_CADASTRO'] . "</b> </a><br/>
                                            Data da Solicitação: " . $dados['DOCM_DH_CADASTRO'] . " <br/>
                                            Tipo de Serviço Solicitado: " . $dados['SSER_DS_SERVICO'] . "<br/>
                                            Descrição: " . nl2br($data["descricao"]) . "<br/>";
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);

                            #ACOMPANHANTES ATIVOS DO SOSTI
                            $PapdParteProcDoc = $SadTbPapdParteProcDoc->getAcompanhantesAtivos($idSolicitacao);
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
                                        $assunto = 'Homologação de Solicitação - Acompanhante do SOSTI';
                                        $corpo = " $descricaoTipoInformação</p>
                                                                Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados['DOCM_DH_CADASTRO'] . "\"><b>" . $dados['DOCM_DH_CADASTRO'] . "</b> </a><br/>
                                                                Data da Solicitação: " . $dados['DOCM_DH_CADASTRO'] . " <br/>
                                                                Tipo de Serviço Solicitado: " . $dados['SSER_DS_SERVICO'] . "<br/>
                                                                Descrição: " . nl2br($data["descricao"]) . "<br/>";
                                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                    }
                                }
                            }

                            $msg_to_user = "Solicitação nº " . $msg . " homologada!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('homologacao', 'minhassolicitacoes', 'sosti');
                        }
                    } elseif (($data["Salvar"] == 'Salvar') && ($data["homologacao"] == 2)) {
                        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

                        foreach ($NsAction->dados as $d) {
                            $dados = Zend_Json_Decoder::decode($d);

                            $dataHomologa["MOFA_ID_MOVIMENTACAO"] = $dados["MOFA_ID_MOVIMENTACAO"];
                            $dataHomologa["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataHomologa["MOFA_DS_COMPLEMENTO"] = $data["descricao"];
                            $dataHomologa["MOFA_ID_FASE"] = Trf1_Sosti_Definicoes::FASE_NAOHOMOLOGADO_SOLICITACAO_TI; // NAOHOMOLOGADO

                            $msg = $dados["DOCM_NR_DOCUMENTO"];
                            $idSolicitacao = $dados["SSOL_ID_DOCUMENTO"];
                            $nrdocumento = $dados["DOCM_NR_DOCUMENTO"];

                            $SosTbSsolSolicitacao->setHomologarSos($dataHomologa, $idSolicitacao, $nrDocsRed);


                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $remetente = 'noreply@trf1.jus.br';
                            $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                            #$destinatario = 'tr18757ps@trf1.jus.br';
                            $descricaoTipoInformação = 'Solicitação NÃO Homologada.';

                            $assunto = 'Homologação de Solicitação';
                            $corpo = "$descricaoTipoInformação</p>
                                            Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados['DOCM_DH_CADASTRO'] . "\"><b>" . $dados['DOCM_DH_CADASTRO'] . "</b> </a><br/>
                                            Data da Solicitação: " . $dados['DOCM_DH_CADASTRO'] . " <br/>
                                            Tipo de Serviço Solicitado: " . $dados['SSER_DS_SERVICO'] . "<br/>
                                            Descrição: " . nl2br($data["descricao"]) . "<br/>";
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);

                            #ACOMPANHANTES ATIVOS DO SOSTI
                            $PapdParteProcDoc = $SadTbPapdParteProcDoc->getAcompanhantesAtivos($idSolicitacao);
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
                                        #$destinatario = $matricula . '@trf1.jus.br';
                                        $destinatario = 'tr18757ps@trf1.jus.br';
                                        $remetente = 'noreply@trf1.jus.br';
                                        $assunto = 'Homologação de Solicitação - Acompanhante do SOSTI';
                                        $corpo = " $descricaoTipoInformação</p>
                                                                Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados['DOCM_DH_CADASTRO'] . "\"><b>" . $dados['DOCM_DH_CADASTRO'] . "</b> </a><br/>
                                                                Data da Solicitação: " . $dados['DOCM_DH_CADASTRO'] . " <br/>
                                                                Tipo de Serviço Solicitado: " . $dados['SSER_DS_SERVICO'] . "<br/>
                                                                Descrição: " . nl2br($data["descricao"]) . "<br/>";
                                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                                    }
                                }
                            }

                            $msg_to_user = "Solicitação nº " . $msg . " homologada!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('homologacao', 'minhassolicitacoes', 'sosti');
                        }
                    } else {
                        $form->getElement('descricao')->removeFilter('HtmlEntities');
                        if ($form->getElement('descricao')->hasErrors()) {
                            $form->getElement('descricao')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                        }
                        $this->view->data = $NsAction->dados;
                        $this->view->title = "Homologar Solicitação";
                        $this->view->post = $data;
                        $form->populate($data);
                        $this->view->form = $form;
                        return;
                    }
                } else {
                    $form->getElement('descricao')->removeFilter('HtmlEntities');
                    if ($form->getElement('descricao')->hasErrors()) {
                        $form->getElement('descricao')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $this->view->data = $NsAction->dados;
                    $this->view->title = "Homologar Solicitação";
                    $this->view->post = $data;
                    $form->populate($data);
                    $this->view->form = $form;
                    return;
                }
            }
        } else {
            $this->_helper->_redirector('index', 'index', 'sosti');
        }
    }

    public function avaliacaoAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesAvaliacao($userNs->matricula, $order);
        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOFA_DH_FASE'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Minhas Solicitações de TI para Avaliação";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function avaliacaoaddAction() {
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
        $this->view->NsAction = $NsAction;
        $solicitacaoOs = new Os_Model_DataMapper_Solicitacao();
        /**
         * Forms
         */
        $form = new Sosti_Form_AvaliacaoServico();
        /** Verifica se a solicitação é uma OS para incluir o botão de “Baixar solicitação original com a descrição da avaliação da OS” */
        $params = $this->_getAllParams();
        $d = Zend_Json::decode($params['solicitacao'][0]);
        if ($solicitacaoOs->getVerificaSeOs($d["SSOL_ID_DOCUMENTO"])) {
            $form->addElement('Checkbox', 'BAIXAR_DESCRICAO_AVALIACAO', array('Label'=> 'Baixar solicitação original com a descrição da avaliação da OS'));
        }
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;

        /**
         * Models
         */
        $baixa = new Application_Model_DbTable_SosTbStsaTipoSatisfacao();

        /**
         * Tratamento Form
         */
        $tipoSatisfacao = $baixa->getTipoSatisfacao();
        $ids_tipo_satisfacao = array();
        foreach ($tipoSatisfacao as $value) {
            $ids_tipo_satisfacao[] = $value["STSA_ID_TIPO_SAT"];
        }
        $this->view->tipoSatisfacao = $tipoSatisfacao;


        if ($this->getRequest()->isPost()) {
            /**
             * Chaves padrões de entrada
             */
            $chaves_dados = array('acao' => NULL, 'salvar' => NULL, 'satisfacao' => NULL);
            $data = array_merge($chaves_dados, $this->getRequest()->getPost());

            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Avaliar') {
                $NsAction->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $this->view->title = "Avaliar Solicitação";
                $this->view->form = $form;
                
            } else {
                /** Se for selecionada a opção para baixar as solicitações com a descrição da avaliação */
                Sosti_Model_DataMapper_ReplicaAvaliacao::baixaComMesmaDescricaoAvaliacaoOs($data, $NsAction->dados, $nrDocsRed);

                if (($data["salvar"] == 'Salvar') && ($data["satisfacao"] == 6)) {
                    $form->getElement('descricao')->setRequired(true);
                    $form->getElement('descricao')->setLabel("*Descrição da Avaliação:");
                }
                if ($form->isValid($data)) {
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    
                    
                    $form->ANEXOS->receive();
                    $nrDocsRed = null;
                    if (!is_null($data["ANEXOS"])/* $form->ANEXOS->isReceived() */) {
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
                     * Solicitação Avaliada
                     */
                    
                    if (($data["Salvar"] == 'Salvar') && (in_array($data["satisfacao"], $ids_tipo_satisfacao) && ($data["satisfacao"] != 6) )) {
                        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                        /** Validações para avaliação de OS **/
                        $avaliarPositiv = Os_Model_DataMapper_Avaliacao::avaliarPositivamenteStatusMsg($NsAction->dados);
                        if ($avaliarPositiv['status'] == false) {
                            $this->_helper->flashMessenger(array('message' => $avaliarPositiv['message'], 'status' => 'notice'));
                            $this->_helper->_redirector('avaliacao', 'minhassolicitacoes', 'sosti');
                        }
                        /** Se passar pela valiação: baixa solicitações as que deram origem a OS **/
//                        Os_Model_DataMapper_Avaliacao::baixaSolicitacoesOrigemOs($NsAction->dados);
                        foreach ($NsAction->dados as $d) {
                            $dados = Zend_Json_Decoder::decode($d);
                            $sadTbMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados["MOFA_ID_MOVIMENTACAO"];
                            $sadTbMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $sadTbMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["descricao"];
                            $sadTbMofaMoviFase["MOFA_ID_FASE"] = 1014; // Solicitação avaliada
                            $savisAvaliacaoServico["SAVS_ID_MOVIMENTACAO"] = $dados["MOFA_ID_MOVIMENTACAO"];
                            $savisAvaliacaoServico["SAVS_ID_TIPO_SAT"] = $data["satisfacao"];
                            $msg = $dados["DOCM_NR_DOCUMENTO"];
                            $avaliaSatisfacao = new Application_Model_DbTable_SosTbSavsAvaliacaoServico();
                            $idDocmDocumento = $dados["SSOL_ID_DOCUMENTO"];
                            /** Insere a avaliação na OS da solicitação quando foi marcada a opção na avaliação */
                            Sosti_Model_DataMapper_ReplicaAvaliacao::addMesmaAvaliacaoSolicitacaoOs($idDocmDocumento, $sadTbMofaMoviFase, $savisAvaliacaoServico, $nrDocsRed);
                            $dataHoraId = $avaliaSatisfacao->setAvaliaSolicitacao($idDocmDocumento, $sadTbMofaMoviFase, $savisAvaliacaoServico, $nrDocsRed);
                            $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                            $arrayPerfis = $ocsTbPupePerfilUnidPessoa->getPerfilUnidadePessoa($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula);

                            foreach ($arrayPerfis as $perfil) {
                                //Perfil de Desenvolvimento e Sustentação
                                if ($perfil["PERF_ID_PERFIL"] == 25) {
                                    $SosTbPfdsApfDesenvolvedora = new Application_Model_DbTable_Sosti_SosTbPfdsApfDesenvolvedora();
                                    $verificaRegistroDsv = $SosTbPfdsApfDesenvolvedora->fetchRow('PFDS_ID_SOLICITACAO = ' . $idDocmDocumento);

                                    if ($verificaRegistroDsv["PFDS_ID_STATUS"] == 2) {
                                        $dadosCadastro["PFDS_ID_STATUS"] = 3;
                                        $dadosCadastro["PFDS_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                                        $dadosCadastro["PFDS_ID_SOLICITACAO"] = $idDocmDocumento;
                                        $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
                                        $negocioFaturamento->salvarDadosDesenvolvedora($dadosCadastro);
                                    }
                                }
                            }

                            switch ($data["satisfacao"]) {
                                case 1:
                                    $avaliacao = 'Ótimo';
                                    break;
                                case 2:
                                    $avaliacao = 'Bom';
                                    break;
                                case 3:
                                    $avaliacao = 'Regular';
                                    break;
                                case 4:
                                    $avaliacao = 'Ruim';
                                    break;
                                case 5:
                                    $avaliacao = 'Péssimo';
                                    break;
                                default:
                                    break;
                            }
                            $msg_to_user = "Solicitação nº " . $msg . " avaliada!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        }
                    }
                    /**
                     * Solicitação Recusada
                     */ elseif (($data["Salvar"] == 'Salvar') && ($data["satisfacao"] == 6)) {
                        /** Validações para recusa de OS **/
                        $recusar = Os_Model_DataMapper_Avaliacao::recusarStatusMsg($NsAction->dados);
                        if ($recusar['status'] == false) {
                            $this->_helper->flashMessenger(array('message' => $recusar['message'], 'status' => 'notice'));
                            $this->_helper->_redirector('avaliacao', 'minhassolicitacoes', 'sosti');
                        }
                        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                        foreach ($NsAction->dados as $d) {
                            $avaliaSatisfacao = new Application_Model_DbTable_SosTbSavsAvaliacaoServico();
                            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            $dados = Zend_Json_Decoder::decode($d);
                            $sadTbMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados["MOFA_ID_MOVIMENTACAO"];
                            $sadTbMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $sadTbMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["descricao"];
                            $sadTbMofaMoviFase["MOFA_ID_FASE"] = 1019; // Solicitação recusada
                            $savisAvaliacaoServico["SAVS_ID_MOVIMENTACAO"] = $dados["MOFA_ID_MOVIMENTACAO"];
                            $savisAvaliacaoServico["SAVS_ID_TIPO_SAT"] = $data["satisfacao"];
                            $msg = $dados["DOCM_NR_DOCUMENTO"];
                            $idDocmDocumento = $dados["SSOL_ID_DOCUMENTO"];
                            $avaliacao = 'Recusada';
                            if ($nrDocsRed["erro"]) {
                                $msg_to_user = "<div class='notice'><strong>Erro:</strong> " . $nrDocsRed["erro"] . "</div>";
                                $this->view->flashMessagesView = $msg_to_user;
                                $form->getElement('descricao')->removeFilter('HtmlEntities');
                                if ($form->getElement('descricao')->hasErrors()) {
                                    $form->getElement('descricao')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                                }
                                $this->view->data = $NsAction->dados;
                                $this->view->title = "Avaliar Solicitação";
                                $this->view->post = $data;
                                $form->populate($data);
                                $this->view->form = $form;
                                return;
                            }
                            if (!$nrDocsRed["existentes"]) {
                                /** Verifica se é vinculação de OS */
                                $vinculacao_os = $solicitacaoOs->getVerificaSeOs($dados["SSOL_ID_DOCUMENTO"]);
                                if (!$nrDocsRed["incluidos"]) {
                                    try {
                                        $id_vinculacao_documento = $SosTbSsolSolicitacao->getIdVinculacaoRecusada($dados["SSOL_ID_DOCUMENTO"]);
                                        if ((count($id_vinculacao_documento) > 0) && ($vinculacao_os == false)) {
                                            $desvincular = $SosTbSsolSolicitacao->setDesvincularSolicitacoesRecusada($id_vinculacao_documento, $dados["SSOL_ID_DOCUMENTO"]);
                                        }
                                        $dataHoraId = $avaliaSatisfacao->setAvaliaSolicitacao($idDocmDocumento, $sadTbMofaMoviFase, $savisAvaliacaoServico);
                                        $msg_to_user = "Solicitação nº " . $msg . " avaliada!";
                                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                                    } catch (Exception $exc) {
                                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel avaliar solicitação: ' . $msg . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'error'));
                                    }
                                } else {
                                    try {
                                        $id_vinculacao_documento = $SosTbSsolSolicitacao->getIdVinculacaoRecusada($dados["SSOL_ID_DOCUMENTO"]);
                                        if ((count($id_vinculacao_documento) > 0) && ($vinculacao_os == false)) {
                                            $SosTbSsolSolicitacao->setDesvincularSolicitacoesRecusada($id_vinculacao_documento, $dados["SSOL_ID_DOCUMENTO"]);
                                        }
                                        $dataHoraId = $avaliaSatisfacao->setAvaliaSolicitacao($idDocmDocumento, $sadTbMofaMoviFase, $savisAvaliacaoServico, $nrDocsRed);
                                        $msg_to_user = "Solicitação nº " . $msg . " avaliada!";
                                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                                    } catch (Exception $exc) {
                                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel avaliar solicitação: ' . $msg . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'error'));
                                    }
                                }
                            } else {
                                foreach ($nrDocsRed["existentes"] as $existentes) {
                                    $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                    $this->view->flashMessagesView = $msg_to_user;
                                }
                                $form->getElement('descricao')->removeFilter('HtmlEntities');
                                if ($form->getElement('descricao')->hasErrors()) {
                                    $form->getElement('descricao')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                                }
                                $this->view->data = $NsAction->dados;
                                $this->view->title = "Avaliar Solicitação";
                                $this->view->post = $data;
                                $form->populate($data);
                                $this->view->form = $form;
                                return;
                            }
                        }
                    } else {
                        $form->getElement('descricao')->removeFilter('HtmlEntities');
                        if ($form->getElement('descricao')->hasErrors()) {
                            $form->getElement('descricao')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                        }
                        $this->view->data = $NsAction->dados;
                        $this->view->title = "Avaliar Solicitação";
                        $this->view->post = $data;
                        $form->populate($data);
                        $this->view->form = $form;
                        return;
                    }
                    /**
                     * Envio de email de resposta
                     */
                    if ($dataHoraId) {
                        try {
                            /**
                             * Envio de email de resposta
                             */
                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $remetente = 'noreply@trf1.jus.br';
                            $destinatario = $dados["MOFA_CD_MATRICULA"] . '@trf1.jus.br';
                            $assunto = 'Avaliação de Solicitação';
                            $corpo = "Solicitação Avaliada.</p>
                                Serviço: " . $dados["SSER_DS_SERVICO"] . "<br/>
                                Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $msg . "\"><b>" . $msg . "</b> </a><br />
                                Avaliada por: " . $userNs->nome . " <br/>
                                Avaliação: " . $avaliacao . " <br/>
                                Descrição da Avaliação: " . nl2br($sadTbMofaMoviFase["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                            /**
                             * Fim do envio de email
                             */
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação da avaliação da solicitação: ' . $msg . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                        }
                        $this->_helper->_redirector('avaliacao', 'minhassolicitacoes', 'sosti');
                    }
                    /**
                     * Fim do envio de email
                     */
                } else {
                    $form->getElement('descricao')->removeFilter('HtmlEntities');
                    if ($form->getElement('descricao')->hasErrors()) {
                        $form->getElement('descricao')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $this->view->data = $NsAction->dados;
                    $this->view->title = "Avaliar Solicitação";
                    $this->view->post = $data;
                    $form->populate($data);
                    $this->view->form = $form;
                    return;
                }
            }
        } else {
            $this->_helper->_redirector('index', 'index', 'sosti');
        }
    }

    public function avaliadasAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesAvaliadas($userNs->matricula, $order);

        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['DOCM_DH_CADASTRO'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['DOCM_DH_CADASTRO']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Minhas Solicitações de TI Avaliadas";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function avaliacaoautomaticaAction() {
        /**
         * Variáveis de Sessão
         */
        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);

        /**
         * Models
         */
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();


        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        /**
         * Form de Filtro
         */
        $IntervaloData = new Sosti_Form_IntervaloData();


        $IntervaloData->getElement("DATA_INICIAL")->setLabel("Data inicial Baixa");
        $IntervaloData->getElement("DATA_FINAL")->setLabel("Data final Baixa");
        echo "<hr>";
        $IntervaloData->getElement("SGRS_ID_GRUPO")->setLabel("Grupo de Serviços");
        $IntervaloData->getElement("SSER_ID_SERVICO")->setLabel("Serviços");

        $submit = new Zend_Form_Element_Submit('Listar');
        $submit->removeDecorator('Label')->removeDecorator('HtmlTag')->removeDecorator('DtDdWrapper');
        $submit->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all');
        $IntervaloData->addElement($submit);

        /**
         * Post
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            $destino = Zend_Json::decode($data['SGRS_ID_GRUPO']);
            $sgrs_id_grupo = $IntervaloData->getElement('SGRS_ID_GRUPO');
            $sgrs_id_grupo->addMultiOptions(array($data['SGRS_ID_GRUPO'] => $destino["SGRS_DS_GRUPO"]));


            /* Serviços do grupo de serviço escolhido - para validação */
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $servicos = $SosTbSserServico->getServicoPorGrupo($destino['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
            $sser_id_servico = $IntervaloData->getElement('SSER_ID_SERVICO');
            foreach ($servicos as $servicos_p):
                $sser_id_servico->addMultiOptions(array($servicos_p['SSER_ID_SERVICO'] . '|' . $servicos_p['SSER_IC_TOMBO'] . '|' . $servicos_p['SSER_IC_VIDEOCONFERENCIA'] => $servicos_p["SSER_DS_SERVICO"]));
            endforeach;


            if ($IntervaloData->isValid($data)) {

                $idSvc = explode("|", $IntervaloData->getElement("SSER_ID_SERVICO")->getValue());
                $idService = $idSvc[0];



                $NsAction->unsetAll();
                $NsAction->dataPost = $data;
                $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();

                if (!isset($idService)) {
                    $rows = $dados->getMinhasSolicitacoesAvaliacaoAutomatica(2, $IntervaloData->getElement("DATA_INICIAL")->getValue(), $IntervaloData->getElement("DATA_FINAL")->getValue(), $order);
                } else {
                    $rows = $dados->getMinhasSolicitacoesAvaliacaoAutomatica(2, $IntervaloData->getElement("DATA_INICIAL")->getValue(), $IntervaloData->getElement("DATA_FINAL")->getValue(), $idService, $order);
                }

                $this->view->pesquisa = true;
                if (count($rows) > 0) {
                    $paginator = Zend_Paginator::factory($rows);
                    $paginator->setCurrentPageNumber($page)
                            ->setItemCountPerPage(count($rows));
                    $this->view->data = $paginator;
                }
            } else {
                foreach ($IntervaloData->getMessages() as $messageId => $message) {
                    echo "Validation failure '$messageId': $message\n";
                }
                $IntervaloData->populate($data);
            }
            /**
             *  Sessão
             */
        } else {
//            if (!is_null($NsAction->dataPost)) {
//                $data = $NsAction->dataPost;
//                $IntervaloData->populate($data);
//                $rows = $dados->getMinhasSolicitacoesAvaliacaoAutomatica(2, $IntervaloData->getElement("DATA_INICIAL")->getValue(), $IntervaloData->getElement("DATA_FINAL")->getValue(), $order);
//                $this->view->pesquisa = true;
//                if (count($rows) > 0) {
//                    $paginator = Zend_Paginator::factory($rows);
//                    $paginator->setCurrentPageNumber($page)
//                            ->setItemCountPerPage(count($rows));
//                    $this->view->data = $paginator;
//                }
//            }
        }

        $this->view->title = "Avaliação automática de solicitações pendentes do desenvolvimento";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->form = $IntervaloData;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function avaliacaoautomaticaaddAction() {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(3600);
        /**
         * Models 
         */
        $avaliaSatisfacao = new Application_Model_DbTable_SosTbSavsAvaliacaoServico();
        $SosTbPfdsApfDesenvolvedora = new Application_Model_DbTable_Sosti_SosTbPfdsApfDesenvolvedora();
        $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();

        // Variáveis do usuário
        $userNs = new Zend_Session_Namespace('userNs');


        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            try {
                foreach ($data['solicitacao'] as $d) {
                    $dados = Zend_Json_Decoder::decode($d);
                    /**
                     * Valicação de segurança quanto aos dados vindos da caixa.
                     */
                    $arrayChavesDados = array('SSOL_ID_DOCUMENTO', 'DOCM_NR_DOCUMENTO', 'MOFA_ID_MOVIMENTACAO', 'DOCM_CD_MATRICULA_CADASTRO', 'MOFA_CD_MATRICULA');
                    foreach ($arrayChavesDados as $key) {
                        if (!array_key_exists($key, $dados)) {
                            throw new Exception("A chave obrigatória: $key não foi encontrada nos dados de entrada.");
                        }
                    }
                    $data["descricao"] = "Avaliação automática. A decurso de prazo.";

                    /*                     * *
                     * Dados Solicitacao/Documento
                     */
                    $idDocmDocumento = $dados["SSOL_ID_DOCUMENTO"];
                    $nrDocmSolicitacao = $dados["DOCM_NR_DOCUMENTO"];

                    /*
                     * Altera status para o faturamento
                     */
                    $verificaRegistroDsv = $SosTbPfdsApfDesenvolvedora->fetchRow('PFDS_ID_SOLICITACAO = ' . $idDocmDocumento);
                    if ($verificaRegistroDsv["PFDS_ID_STATUS"] == 2) {
                        $dadosCadastro["PFDS_ID_STATUS"] = 3;
                        $dadosCadastro["PFDS_DH_STATUS"] = new Zend_Db_Expr("SYSDATE");
                        $dadosCadastro["PFDS_ID_SOLICITACAO"] = $idDocmDocumento;

                        $negocioFaturamento->salvarDadosDesenvolvedora($dadosCadastro);
                    }

                    /**
                     * Dados fase 
                     */
                    $sadTbMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados["MOFA_ID_MOVIMENTACAO"];
                    $sadTbMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula; //MATRÍCULA DO USUÁRIO
                    $sadTbMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["descricao"];
                    $sadTbMofaMoviFase["MOFA_ID_FASE"] = 1014; // Solicitação avaliada
                    /**
                     * Dados Avaliação 
                     */
                    $savisAvaliacaoServico["SAVS_ID_MOVIMENTACAO"] = $dados["MOFA_ID_MOVIMENTACAO"];
                    $savisAvaliacaoServico["SAVS_ID_TIPO_SAT"] = 7;  //AUTOMÁTICA             

                    $avaliaSatisfacao->setAvaliaSolicitacao($idDocmDocumento, $sadTbMofaMoviFase, $savisAvaliacaoServico);

                    /**
                     * Envia E-mail Cadastrante
                     */
                    $email = new Application_Model_DbTable_EnviaEmail();
                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                    $remetente = 'noreply@trf1.jus.br';
                    $destinatario = $dados["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                    $assunto = 'Avaliação de Solicitação';
                    $corpo = "Solicitação Avaliada.</p>
                            Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin-Treinamento/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $nrDocmSolicitacao . "\"><b>" . $nrDocmSolicitacao . "</b> </a><br />
                            Avaliada por: " . $userNs->nome . ". <br/>
                            Avaliação: AUTOMÁTICA. <br/>
                            Descrição da Avaliação: " . nl2br($sadTbMofaMoviFase["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    /**
                     * Envia E-mail Atendente Baixa
                     */
                    $destinatario = $dados["MOFA_CD_MATRICULA"] . '@trf1.jus.br';
                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    /**
                     * Fim do envio de email
                     */
                }
            } catch (Exception $exc) {
                $msg_to_user = "Ocorreu um erro durante a avaliação: " . $exc->getMessage();
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                $this->_helper->_redirector('avaliacaoautomatica', 'minhassolicitacoes', 'sosti');
            }
            $msg_to_user = "Solicitação(ões) Avaliada(s) automaticamente!";
            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
            $this->_helper->_redirector('avaliacaoautomatica', 'minhassolicitacoes', 'sosti');
        }
    }

    public function pedidoinformacaoAction() {
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itemsperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOFA_DH_FASE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesAtendimento($userNs->matricula, $order, 'solicitacao de informacao', 'aousuario');
        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOVIMENTACAO'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsperpage);

        $this->view->title = "Minhas Solicitações de TI com Pedido de Informação";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
//        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }


    public function pedidoinformacaounidadeAction(){
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOFA_DH_FASE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesAtendimento($userNs->matricula, $order, 'solicitacao de informacao', 'aunidade');
        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOVIMENTACAO'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Solicitações de TI encaminhadas ao Desenvolvimento e Sustentação pela Gestão de Demandas de TI com Pedido de Informação";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function pedidoinformacaodsvAction() {
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOFA_DH_FASE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getMinhasSolicitacoesAtendimento($userNs->matricula, $order, 'solicitacao de informacao', 'aoencaminhador');
        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOVIMENTACAO'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Minhas Solicitações de TI encaminhadas ao Desenvolvimento e Sustentação com Pedido de Informação";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->action = $this->getRequest()->getActionName();
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function pedidoinformacaoaddAction() {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        $form = new Sosti_Form_SolicitarInformacao();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Incluir Informação') {
                $userNs = new Zend_Session_Namespace('userNs');
                $solicspace = new Zend_Session_Namespace('solicspace');
                $solicspace->request = $data;
                $solicspace->dados = $data['solicitacao'];
                $solicspace->module = (isset($data["module"]) ? $data["module"] : 'sosti');
                $solicspace->controller = $data["controller"];
                $solicspace->action = $data["action"];
                $this->view->data = $data['solicitacao'];
                $this->view->title = "Incluir Informações para a(s) Solicitação(es):";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                    $form->ANEXOS->receive();

                    $nrDocsRed = null;
                    if (!is_null($data["ANEXOS"])/* $form->ANEXOS->isReceived() */) {
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
                        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $dados_input = Zend_Json::decode($d);

                        $rn_solicitacaoInformacao = new Trf1_Sosti_Negocio_SolicitacaoInformacao();
                        $destinoFasePedidoInformacao = $rn_solicitacaoInformacao->getDestinoFaseResposta($dados_input);
                        if (isset($destinoFasePedidoInformacao['erro'])) {
                            $this->_helper->flashMessenger(array('message' => 'SOSTI: ' . $dados_input['DOCM_NR_DOCUMENTO'] . ' - ' . $destinoFasePedidoInformacao['erro'], 'status' => 'notice'));
                        } else {
                            $DocmDocumentoHistorico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($dados_input["SSOL_ID_DOCUMENTO"]);
                            //Se for a ultima fase for solicitação de informação
                            if (in_array($DocmDocumentoHistorico[0]["FADM_ID_FASE"], array(Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO, Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI))) {
                                $envio = true;
                            } else {
                                $envio = false;
                            }
                            $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                            $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                            $dataInfo["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];


                            $dataInfo['MOFA_ID_FASE'] = $destinoFasePedidoInformacao['fase'];
                            $dataInfo["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                            $dataInfo["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];

                            /**
                             * Monta email
                             */
                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $remetente = 'noreply@trf1.jus.br';
                            $destinatario = $destinoFasePedidoInformacao['para'] . '@trf1.jus.br';
                            $assunto = 'Resposta da Solicitação de Informação';
                            $corpo = "Solicitação de Informação respondida.</p>
                                  Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br />
                                  Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                  Resposta: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
//                            $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            $SosTbSsolSolicitacao->setSolicitarInformacaoSolicitacao($dataInfo, $dados_input["SSOL_ID_DOCUMENTO"], $nrDocsRed);
                            if ($envio) {
                                $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                            }
                        }
                    }
                    if (!isset($destinoFasePedidoInformacao['erro'])) {
                        $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " com pedido de informação!";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    }
                    $this->_helper->_redirector($solicspace->action, $solicspace->controller, $solicspace->module);
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = "Incluir informação para a(s) solicitação(es):";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('pedidoinformacaoadd');
                }
            }
        } else {
            $solicspace = new Zend_Session_Namespace('solicspace');
            $this->view->data = $solicspace->dados;
            $this->view->title = "Incluir informação para a(s) solicitação(es):";
            $form->populate($data);
            $this->view->form = $form;
            $this->render('pedidoinformacaoadd');
        }
    }

    public function pedidoinformacaorespondidoAction() {
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOFA_DH_FASE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $SosTbSsolSolicitacao->getMinhasSolicitacoesPedidoInfRespondido($userNs->matricula, 'MOFA_DH_FASE ASC', 1024, 1025);
        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_Sosti_TempoSla();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotalHelpdesk($rows[$i]['MOVIMENTACAO'], '', '07:00:00', '20:00:00');
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Solicitação(es) com Pedido de Informação Respondido";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function minhassolicitacoesperiodoAction() {

        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array('direcao' => 'ASC', 'ordem' => 'DOCM_DH_CADASTRO', 'itemsperpage' => 15, 'page' => 1);
        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);

        $page = $varSessoes->getPage();
        $itemCountPerPage = $varSessoes->getItemsperpage();

        /* Ordenação das paginas */
        $order_column = $varSessoes->getOrdem();
        $order_direction = $varSessoes->getDirecao();
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        $this->view->direcao = $order_direction;

        $order = $order_column . ' ' . $order_direction;

        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoMinhaNs');
        $form = new Sosti_Form_RelatoriosHelpdesk();
        $this->view->form = $form;
        $form->removeElement('SGRS_ID_GRUPO');
        $form->removeElement('SNAT_CD_NIVEL');
        $form->removeElement('AVALIACAO');
        $form->getElement('DATA_INICIAL')->setLabel('Data inicial baixa:');
        $form->getElement('DATA_FINAL')->setLabel('Data final baixa:');
        $Dual = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $Dual->sysdateDb();
        $this->view->sysdateFirstDay = substr($Dual->sysdateDbFirstDay(), 0, 10) . " 00:00:00";
        $this->view->primeira = $aSlaPeriodoSpace->primeira;
        if (is_null($aSlaPeriodoSpace->primeira)) {
            $aSlaPeriodoSpace->data['DATA_INICIAL'] = $Dual->sysdateDbFirstDay();
            $aSlaPeriodoSpace->data['DATA_FINAL'] = $Dual->sysdateDb() . " 23:59:59";
        }
        $aSlaPeriodoSpace->pesquisar = 'Pesquisar';
        //$aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoMinhaNs');
        /* paginação */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $aSlaPeriodoSpace->data = $data;
            $aSlaPeriodoSpace->data_inicial = $data["DATA_INICIAL"];
            $aSlaPeriodoSpace->data_final = $data["DATA_FINAL"];
            $aSlaPeriodoSpace->data_inicial_cadastro = $data["DATA_INICIAL_CADASTRO"];
            $aSlaPeriodoSpace->data_final_cadastro = $data["DATA_FINAL_CADASTRO"];
            $aSlaPeriodoSpace->data_inicial_encaminhamento = $data["DATA_INICIAL_ENCAMINHAMENTO"];
            $aSlaPeriodoSpace->data_final_encaminhamento = $data["DATA_FINAL_ENCAMINHAMENTO"];
            $aSlaPeriodoSpace->pesquisar = $data["Pesquisar"];
            $aSlaPeriodoSpace->primeira = true;
            $this->view->primeira = $aSlaPeriodoSpace->primeira;
        }
        if ((($aSlaPeriodoSpace->data_inicial != "") && ($aSlaPeriodoSpace->data_final != "")) || (($aSlaPeriodoSpace->data_inicial_encaminhamento != "") && ($aSlaPeriodoSpace->data_final_encaminhamento != "")) || (($aSlaPeriodoSpace->data_inicial_cadastro != "") && ($aSlaPeriodoSpace->data_final_cadastro != ""))) {
            $form->populate($aSlaPeriodoSpace->data);
        }
        $grupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $userNs = new Zend_Session_Namespace('userNs');
        if ($aSlaPeriodoSpace->nivel) {
            $descricaoNivel = $grupo->getNivel($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel);
            $this->view->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"] . ' - ' . $descricaoNivel[0]["SNAT_SG_NIVEL"];
        }
        if ($aSlaPeriodoSpace->grupo) {
            $this->view->descricaoGrupo = $grupo->getGrupoAtendimento($aSlaPeriodoSpace->grupo);
        }
        $this->view->title = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final)) ?
                ("Minhas Solicitações Baixadas por Período: " . $aSlaPeriodoSpace->data_inicial . " à " . $aSlaPeriodoSpace->data_final) :
                ("Minhas Solicitações Baixadas por Período: " . $aSlaPeriodoSpace->data_inicial . $aSlaPeriodoSpace->data_final);
        $dadosQtde = new Application_Model_DbTable_SosTbSsolSolicitacao();
        //$order_direction = $this->_getParam ( 'direcao', 'DESC' );


        $qtde = $dadosQtde->getQtdeMinhasSolicitacoesPeriodoSla($userNs->matricula, $aSlaPeriodoSpace->data, $order);

        if ($aSlaPeriodoSpace->pesquisar != "") {
            //zend_debug::dump($varSessoes->getDirecao(),'direcao sessao');
            $page = $varSessoes->getPage();

            /* Ordenação das paginas */



            /* Ordenação */
            $this->view->qtde = $qtde[0]['QTDE'];

            if ($qtde[0]['QTDE'] < 5000) {


                $rows = $SosTbSsolSolicitacao->getMinhasSolicitacoesPeriodoSla($userNs->matricula, $aSlaPeriodoSpace->data, $order);
                /* verifica condições e faz tratamento nos dados */
                $TimeInterval = new App_TimeInterval();
                $fim = count($rows);
                for ($i = 0; $i < $fim; $i++) {
                    $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotal($rows[$i]['DH_CADASTRO'], $rows[$i]['DH_FASE']);
                    $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
                }

                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($varSessoes->getPage())
                        ->setItemCountPerPage($varSessoes->getItemsperpage());

                $this->view->ordem = $varSessoes->getOrdem();
                $this->view->data = $paginator;
                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->direcao_pdf = $order;

                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
            }
        }
    }

    public function minhassolicitacoesperiodopdfAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoMinhaNs');
        $this->view->titulo = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final)) ?
                ($aSlaPeriodoSpace->data_inicial . " à " . $aSlaPeriodoSpace->data_final) :
                ($aSlaPeriodoSpace->data_inicial . $aSlaPeriodoSpace->data_final);

        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();

        $rows = $dados->getMinhasSolicitacoesPeriodoSla($userNs->matricula, $aSlaPeriodoSpace->data, $this->_getParam('ordem'));

        $TimeInterval = new App_TimeInterval();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotal($rows[$i]['DH_CADASTRO'], $rows[$i]['DH_FASE']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        $grupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        if ($aSlaPeriodoSpace->nivel) {
            $descricaoNivel = $grupo->getNivel($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel);
            $this->view->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"] . ' - ' . $descricaoNivel[0]["SNAT_SG_NIVEL"];
        }
        // $this->view->grupo = $grupo->getGrupoAtendimento($aSlaPeriodoSpace->grupo);
        // Zend_Debug::dump($rows);exit ;
        $this->view->nome = $userNs->matricula . " - " . $userNs->nome;
        $this->view->data = $rows;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->render();

        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));
        $mpdf = new mPDF('', // mode - default ''
                '', // format - A4, for example, default ''
                8, // font size - default 0
                '', // default font family
                10, // margin_left
                10, // margin right
                10, // margin top
                10, // margin bottom
                9, // margin header
                9, // margin footer
                'L');
        $mpdf->AddPage('P', '', '0', '1');
        $mpdf->WriteHTML($body);
        $name = 'Finalizadas_periodo_' . str_replace('/', '_', $aSlaPeriodoSpace->data_inicial . '_' . $aSlaPeriodoSpace->data_final) . '.pdf';
        $mpdf->Output($name, 'D');
    }

    /**
     * Cancelar propria solicitacao
     * Não excluir essa action
     */
    public function cancelarAction() {
        $form = new Sosti_Form_Cancelar();
        $form->MOFA_DS_COMPLEMENTO->setRequired(false);
        $this->view->form = $form;
        $userNs = new Zend_Session_Namespace('userNs');
        $solicspace = new Zend_Session_Namespace('solicspace');

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Cancelar') {
                $solicspace->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $this->view->title = "Cancelar - Solicitação(es)";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $cancelada = '';
                    $solicitada = '';

                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $dataCancelamento["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataCancelamento["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataCancelamento["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $dados = $baixa->cancelaSolicitacao($dataCancelamento, $dados_input["SSOL_ID_DOCUMENTO"], 1);
                        if ($dados['atendente'] != '') {
                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $assunto = $dados['assunto'];
                            $remetente = 'noreply@trf1.jus.br';
                            $destinatario = $dados['atendente'] . '@trf1.jus.br';
                            $corpo = "A seguinte solicitação foi cancelada.</p>
								Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $nrdocumento . "\"><b>" . $nrdocumento . "</b> </a><br />
								Data da Solicitação: " . date('d/m/Y H:i:s') . " <br/>
								Responsavél: " . $userNs->nome . " <br/>
								Tipo de Serviço : " . $dados['assunto'] . " <br/>
								Descrição do Cancelamento: " . $dataCancelamento["MOFA_DS_COMPLEMENTO"] . "<br/>";

                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        }

                        //Prepara mensagem
                        if ($dados['tipo'] == 1) {
                            $cancelada = $cancelada . ", " . $nrdocumento;
                        } else {
                            $solicitada = $solicitada . ", " . $nrdocumento;
                        }
                    }

                    if ($solicitada != '') {
                        $solicitada = $solicitada . ' com cancelamento solicitado!';
                        $separador = ' ';
                    }

                    if ($cancelada != '') {
                        $cancelada = $cancelada . ' cancelada(s)!';
                        $separador = "<br/>";
                    }

                    if (($solicitada != '') && ($cancelada != '')) {
                        $separador = "<br/>Solicitação(es) n(s)º ";
                    }
                    //Fim prepara mensagem 

                    $msg_to_user = "<br/>Solicitação(es) n(s)º " . substr($cancelada, 1) . $separador . substr($solicitada, 1);

                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector('atendimento', 'minhassolicitacoes', 'sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = "Cancelar - Solicitação(ões)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('cancelar');
                }
            }
        }
    }

    public function avaliacaodiretoresAction() {

        $userNs = new Zend_Session_Namespace('userNs');

        $secaopermissao = new App_Controller_Plugin_AcessoCaixaUnidade ();
        $sgsecao = $secaopermissao->getSgsecaoCaixaUnidade();
        $Siglalotacao = $secaopermissao->getSiglaLotacaoCaixaUnidade();
        $codlotacao = $secaopermissao->getCdlotacaoCaixaUnidade();
        /*
          if(!$userNs->isDiretor){

          $this->_helper->flashMessenger ( array ('message' => "Módulo de avaliação da unidade é somente disponível para Diretores ", 'status' => 'notice' ) );
          $this->_helper->_redirector ( 'index', 'index', 'sisad' );
          //throw new Exception('módulo somente pra diretores');

          }
         */
        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());

        $variaveisSessaoPadrao = array('direcao' => 'ASC', 'ordem' => 'TEMPO_TOTAL', 'itemsperpage' => 15, 'page' => 1);

        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);
        $this->view->title = "Solicitações para avaliar pelos diretores - " . $Siglalotacao;
        $page = $varSessoes->getPage();

        /* Ordenação das paginas */
        $order_column = $varSessoes->getOrdem();
        $order_direction = $varSessoes->getDirecao();
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $rows = $dados->getSolicitacoesBaixadasdaUnidade($sgsecao, $codlotacao, $order);
        $TimeInterval = new App_TimeInterval();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($varSessoes->getItemsperpage());
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function emacompanhamentoAction() {
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'DOCM_NR_DOCUMENTO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $rows = $dados->getMinhasSolicitacoesAcompanhamento($userNs->matricula, $order);

        /* verifica condições e faz tratamento nos dados */
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            unset($rows[$i]['DOCM_DH_CADASTRO']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->title = "Solicitações de TI em Acompanhamento";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function desacompanharAction() {
        $data = $this->getRequest()->getPost();
        $userNs = new Zend_Session_Namespace('userNs');
        $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        try {
            $tabelaPapd->delAcompanhamento($data['solicitacao'], $userNs->matricula);
            $this->_helper->flashMessenger(array('message' => 'Solicitação Desacompanhada com Sucesso', 'status' => 'success'));
            $this->_helper->_redirector('emacompanhamento', 'minhassolicitacoes', 'sosti');
        } catch (Exception $exc) {
            $this->_helper->flashMessenger(array('message' => 'Ocorreu em erro ao desacompanhar solicitação: ' . $exc->getMessage(), 'status' => 'error'));
            $this->_helper->_redirector('emacompanhamento', 'minhassolicitacoes', 'sosti');
        }
    }

    public function anexoerroAction() {
        $form = new Sosti_Form_Anexo();
        $form->anexoUnico();
        $this->view->form = $form;

        $data = $this->getRequest()->getPost();
        $dados = explode('-', $data["identificacao"]);

        $anex["ANEX_ID_DOCUMENTO"] = $dados[0];
        $anex["ANEX_DH_FASE"] = $dados[1];
        $anex["ANEX_ID_MOVIMENTACAO"] = $dados[2];

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            $form->ANEXOS->receive();
            $nrDocsRed = null;
            if (!is_null($data["ANEXOS"])/* $form->ANEXOS->isReceived() */) {
                try {
                    $upload = new App_Multiupload_NewMultiUpload();
                    $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);

                    $msg_to_user = "Anexos inserido no documento";

                    $anex["ANEX_NR_DOCUMENTO_INTERNO"] = $nrDocsRed["existentes"][0]["ID_DOCUMENTO"];
                    $anex["ANEX_ID_TP_EXTENSAO"] = $nrDocsRed["existentes"][0]["ANEX_ID_TP_EXTENSAO"];
                    $anex["ANEX_NM_ANEXO"] = $nrDocsRed["existentes"][0]["NOME"];

                    $anexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                    $anexAnexo->setAnexoErro($anex);

                    $msg_to_user = 'Anexo substituido com sucesso!';
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                } catch (Exception $exc) {
                    $msg_to_user = "Não foi possível subistituir anexo";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                }
            }
        }
        $this->_helper->_redirector($data["caixa"], $data["controller"], 'sosti');
    }

}
