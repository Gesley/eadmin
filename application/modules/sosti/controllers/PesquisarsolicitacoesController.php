<?php

class Sosti_PesquisarsolicitacoesController extends Zend_Controller_Action {

    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch () {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function init () {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        $this->view->titleBrowser = 'e-Sosti';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function ajaxsubsecoesAction () {
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $lotacao = Zend_Filter::FilterStatic($this->_getParam('lotacao'), 'int');
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao, $lotacao);
        $this->view->Lotacao_array = $Lotacao_array;
    }

    public function formpesquisaAction () {
        $formP = new Sosti_Form_FormBuscaSosti();
        $permissao = new Application_Model_DbTable_OcsTbAspasPapelSistema();
        $nSosti = $this->getRequest()->getParam('nSosti');
        $idCaixa = $this->getRequest()->getParam('idcaixa');
        $idNivel = $this->getRequest()->getParam('idnivel');
        $formP->getElement('DOCM_NR_DOCUMENTO')->setValue($nSosti);
        $formP->getElement('STATUS_SOLICITACAO')->setValue('');
        $usurio = new Zend_Session_Namespace('userNs');
//        $idNivel = 'null';
//        Zend_Debug::dump($idNivel);exit;
        /**
         * Quando a pesquisa deve ser realizada em alguma caixa de atendimento
         * gestaodedemandasti
         */
        if ($idCaixa != '') {
            $formP->getElement('SERVICO')->setValue('nomecompleto');
            $formP->getElement('SOMENTE_PRINCIPAL')->setValue('S');
            $arrayCaixa = array(
                '1.1' => 'helpdesk/primeironivel',
                '1.2' => 'atendimentotecnico/segundonivel',
                '1.3' => 'suporteespecializado/terceironivel',
                '1.4' => 'servicoexterno/quartonivel',
                '11.33' => 'atendimentosecoes/atendimentousuario',
                2 => 'desenvolvimentosustentacao/index',
                3 => 'bancodadosrede/index',
                4 => 'noc/index',
                5 => 'atendimentosecoes/atendimentousuario',
                6 => 'atendimentosecoes/caixaunidadecentral',
                7 => 'atendimentosecoes/atendimentousuario',
                8 => 'atendimentosecoes/atendimentousuario',
                9 => 'atendimentosecoes/atendimentousuario',
                10 => 'atendimentosecoes/atendimentousuario',
                11 => 'atendimentosecoes/atendimentousuario',
                12 => 'atendimentosecoes/atendimentousuario',
                13 => 'atendimentosecoes/atendimentousuario',
                14 => 'atendimentosecoes/caixaunidadecentral',
                15 => 'atendimentosecoes/atendimentousuario',
                16 => 'atendimentosecoes/atendimentousuario',
                17 => 'atendimentosecoes/atendimentousuario',
                18 => 'atendimentosecoes/atendimentousuario',
                19 => 'gestaodedemandasti/index',
                20 => 'gestaodemandasinfraestrutura/index',
                21 => 'gestaodedemandasdoatendimentoaosusuarios',
                22 => 'atendimentosecoes/atendimentousuario',
                23 => 'atendimentosecoes/atendimentousuario',
                24 => 'atendimentosecoes/atendimentousuario',
                25 => 'atendimentosecoes/atendimentousuario',
                26 => 'atendimentosecoes/atendimentousuario',
                27 => 'atendimentosecoes/atendimentousuario',
                28 => 'atendimentosecoes/atendimentousuario',
                29 => 'atendimentosecoes/atendimentousuario',
                30 => 'atendimentosecoes/atendimentousuario',
                31 => 'atendimentosecoes/atendimentousuario',
                32 => 'atendimentosecoes/atendimentousuario',
                33 => 'atendimentosecoes/atendimentousuario',
                34 => 'atendimentosecoes/atendimentousuario',
                35 => 'atendimentosecoes/atendimentousuario',
                36 => 'gestaodedemandasdonoc/index'
            );
            $arrayNotAccess = array();
            foreach ($permissao->getNotAccess($usurio->matricula) as $k => $v) {
                if ($v["MODL_NM_MODULO"] == "sosti") {
                    $arrayNotAccess[$k] = $v["CTRL_NM_CONTROLE_SISTEMA"] . '/' . $v["ACAO_NM_ACAO_SISTEMA"];
                }
            }
            if ($idNivel == 'null') {
                if (in_array($arrayCaixa[$idCaixa], $arrayNotAccess)) {
                    $this->_helper->flashMessenger(array('message' => 'Você não possui acesso à caixa onde se encontra esta solicitação.', 'status' => 'notice'));
                    $this->_helper->_redirector('index', 'pesquisarsolicitacoes', 'sosti');
                }
                $formP->setAttrib('action', rtrim(preg_replace('/([^\/]*)$/', '', $_SERVER['PHP_SELF']), '/\\') . '/sosti/' . $arrayCaixa[$idCaixa]);
            } else {
                if (in_array($arrayCaixa[$idCaixa . '.' . $idNivel], $arrayNotAccess)) {
                    $this->_helper->flashMessenger(array('message' => 'Você não possui acesso à caixa onde se encontra esta solicitação.', 'status' => 'notice'));
                    $this->_helper->_redirector('index', 'pesquisarsolicitacoes', 'sosti');
                }
                $formP->setAttrib('action', rtrim(preg_replace('/([^\/]*)$/', '', $_SERVER['PHP_SELF']), '/\\') . '/sosti/' . $arrayCaixa[$idCaixa . '.' . $idNivel]);
            }
            $formP->getElement('SERVICO')->setValue('nomecompleto');
        }
        $this->view->form = $formP;
    }

    public function ajaxservicosAction () {
        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $id[SGRS_ID_GRUPO] = Zend_Filter::FilterStatic($data[SGRS_ID_GRUPO], 'int');
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServico_array = $SosTbSserServico->getServicoPorGrupo($id[SGRS_ID_GRUPO], 'SSER_DS_SERVICO ASC');
            $this->view->servicos = $SosTbSserServico_array;
        }
    }

    public function indexAction () {
        $form = new Sosti_Form_PesquisarTodasSolicitacoes();

        $Ns_Pesquisarsolicitacoes_index = new Zend_Session_Namespace('Ns_Pesquisarsolicitacoes_index');

        $form_valores_padrao = $form->getValues();

        if ($this->_getParam('nova') === '1') {
            unset($Ns_Pesquisarsolicitacoes_index->data_pesq);
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
                $this->view->title = "Pesquisar Solicitações";
                $msg_to_user = "O preenchimento de um dos campos de pesquisa é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            if ($data_pesq['SGRS_ID_GRUPO']) {
                $input_data_sgrs_id_grupo = Zend_Json::decode($data_pesq['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data_pesq['SGRS_ID_GRUPO'] => $input_data_sgrs_id_grupo["SGRS_DS_GRUPO"]));

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorGrupo($input_data_sgrs_id_grupo['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] . '|' . $SserServico_p["SSER_IC_TOMBO"] . '|' . $SserServico_p["SSER_IC_VIDEOCONFERENCIA"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            if ($data_pesq['TRF1_SECAO']) {
                $secao = explode('|', $data_pesq['TRF1_SECAO']);

                $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
                $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao[0], $secao[1]);
                $trf1_secao = $form->getElement('SECAO_SUBSECAO');
                foreach ($Lotacao_array as $value) {
                    $trf1_secao->addMultiOptions(array($value["LOTA_SIGLA_SECAO"] . '|' . $value['LOTA_COD_LOTACAO'] . '|' . $value["LOTA_TIPO_LOTACAO"] => $value["LOTA_SIGLA_LOTACAO"] . ' - ' . $value["LOTA_DSC_LOTACAO"] . ' - ' . $value["LOTA_COD_LOTACAO"] . ' - ' . $value["LOTA_SIGLA_SECAO"] . ' - ' . $value["LOTA_LOTA_COD_LOTACAO_PAI"]));
                }
            }

            if ($form->isValid($data_pesq)) {
                $Ns_Pesquisarsolicitacoes_index->data_pesq = $this->getRequest()->getPost();
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "Pesquisar Solicitações";
                return;
            }
        }


        $data_pesq = $Ns_Pesquisarsolicitacoes_index->data_pesq;
        if (!is_null($data_pesq)) {
            $this->view->ultima_pesq = true;


            /* paginação */
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /* Ordenação das paginas */
            $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');


            $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();

            $count = $dados->getTodasSolicitacoesCount($data_pesq);

            // se não retornar registros, apresenta mensagem.
            if ($count['COUNT'] == 0) {

                $this->view->form = $form;
                $this->view->title = "Pesquisar Solicitações";
                $msg_to_user = "Não existem registros para os parametros de pesquisa informados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            if ($count["COUNT"] <= 500) {
                $rows = $dados->getTodasSolicitacoes($data_pesq, $order);
            } else {
                $this->view->form = $form;
                $this->view->title = "Pesquisar Solicitações";
                $msg_to_user = "A pesquisa retornou $count[COUNT] registros ultrapassou o maximo de 500 registros.  <br/> Informe mais parâmetros de pesquisa. <br/> Por Exemplo, limite um período de tempo.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /* verifica condições e faz tratamento nos dados */
            //$TimeInterval = new App_TimeInterval();
            $fim = count($rows);
            for ($i = 0; $i < $fim; $i++) {
                //$rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOFA_DH_FASE']);
                unset($rows[$i]['MOFA_DH_FASE']);
                unset($rows[$i]['DATA_ATUAL']);
//                $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
            }

            if (!is_null($rows)) {
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(100);
            }

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');


            if ($data_pesq['SGRS_ID_GRUPO']) {
                $input_data_sgrs_id_grupo = Zend_Json::decode($data_pesq['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data_pesq['SGRS_ID_GRUPO'] => $input_data_sgrs_id_grupo["SGRS_DS_GRUPO"]));

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorGrupo($input_data_sgrs_id_grupo['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] . '|' . $SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            $form->populate($data_pesq);
        }


        $this->view->form = $form;
        $this->view->title = "Pesquisar Solicitações";
    }

    public function associarpesquisarAction () {
        $form = new Sosti_Form_PesquisarTodasSolicitacoes();
        $acaoSolicit = new Zend_Form_Element_Hidden('ACAO_SOLICIT');
        $formVincular = new Sosti_Form_Vincular();
        $calculaTempo = new App_View_Helper_Calculahorasla();
        $form->setAttrib('id', 'pesq');
        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            if ((count($data_pesq["solicitacao"]) > 0) && ($data_pesq["acao"] == "Associar Sostis")) {
                foreach ($data_pesq["solicitacao"] as $value) {
                    $jsonDecodeSol = Zend_Json::decode($value);
                    $prazo = $calculaTempo->calculahorasla($jsonDecodeSol['TEMPO_TOTAL']);
                    $tempoTotal = $prazo['prazo_total'][0] . "D " . $prazo['prazo_total'][1] . "h " . $prazo['prazo_total'][2] .
                        "m " . $prazo['prazo_total'][3] . "s";
                    $jsonDecodeSol['PRAZO'] = $tempoTotal;
                    $jsonDecodeSol['URLCAIXA'] = $data_pesq["controller"] . '|' . $data_pesq["action"];
                    $result = Zend_Json::encode($jsonDecodeSol);
                    $form->addElement('hidden', 'id_' . $result, array(
                        'name' => 'SOLICITACOES_ESCOLHIDAS',
                        'value' => $result,
                        'isArray' => true,
                    ));
                }
            }
        }

        if ($data_pesq['acao'] != null) {
            $form->addElement($acaoSolicit->setValue($data_pesq['acao']));
        }

        $form_valores_padrao = $form->getValues();

        if ($this->_getParam('nova') === '1') {
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        if ($data_pesq["acao"] != "Associar Sostis") {
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $this->view->title = "Associar Sostis";
                $msg_to_user = "O preenchimento de um dos campos de pesquisa é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            if ($data_pesq['SGRS_ID_GRUPO']) {
                $input_data_sgrs_id_grupo = Zend_Json::decode($data_pesq['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data_pesq['SGRS_ID_GRUPO'] => $input_data_sgrs_id_grupo["SGRS_DS_GRUPO"]));
                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorGrupo($input_data_sgrs_id_grupo['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] . '|' . $SserServico_p["SSER_IC_TOMBO"] . '|' . $SserServico_p["SSER_IC_VIDEOCONFERENCIA"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            if ($data_pesq['TRF1_SECAO']) {
                $secao = explode('|', $data_pesq['TRF1_SECAO']);

                $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
                $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao[0], $secao[1]);
                $trf1_secao = $form->getElement('SECAO_SUBSECAO');
                foreach ($Lotacao_array as $value) {
                    $trf1_secao->addMultiOptions(array($value["LOTA_SIGLA_SECAO"] . '|' . $value['LOTA_COD_LOTACAO'] . '|' . $value["LOTA_TIPO_LOTACAO"] => $value["LOTA_SIGLA_LOTACAO"] . ' - ' . $value["LOTA_DSC_LOTACAO"] . ' - ' . $value["LOTA_COD_LOTACAO"] . ' - ' . $value["LOTA_SIGLA_SECAO"] . ' - ' . $value["LOTA_LOTA_COD_LOTACAO_PAI"]));
                }
            }

            if ($data_pesq["SOLICITACOES_ESCOLHIDAS"]) {
                foreach ($data_pesq["SOLICITACOES_ESCOLHIDAS"] as $value) {
                    $form->addElement('hidden', 'id_' . $value, array(
                        'name' => 'SOLICITACOES_ESCOLHIDAS',
                        'value' => $value,
                        'isArray' => true,
                    ));
                }
                $this->view->solicitacoesEscolhidas = $data_pesq["SOLICITACOES_ESCOLHIDAS"];
            }

            if ($form->isValid($data_pesq)) {
                $data_pesq = $this->getRequest()->getPost();
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "Associar Sostis";
                return;
            }
        }

        if ((!is_null($data_pesq)) && ($data_pesq["acao"] != "Associar Sostis")) {
            $this->view->ultima_pesq = true;
            $this->view->dadosPesquisa = $data_pesq;
            /* paginação */
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /* Ordenação das paginas */
            $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

            $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();

            $count = $dados->getTodasSolicitacoesCount($data_pesq);

            if ($count["COUNT"] <= 500) {
                $rows = $dados->getTodasSolicitacoes($data_pesq, $order);
            } else {
                $this->view->form = $form;
                $this->view->title = "Associar Sostis";
                $msg_to_user = "A pesquisa retornou $count[COUNT] registros ultrapassou o maximo de 500 registros.  <br/> Informe mais parâmetros de pesquisa. <br/> Por Exemplo, limite um período de tempo.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            $fim = count($rows);
            for ($i = 0; $i < $fim; $i++) {
                unset($rows[$i]['MOFA_DH_FASE']);
                unset($rows[$i]['DATA_ATUAL']);
            }

            if (!is_null($rows)) {
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(100);
            }

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            if ($data_pesq['SGRS_ID_GRUPO']) {
                $input_data_sgrs_id_grupo = Zend_Json::decode($data_pesq['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data_pesq['SGRS_ID_GRUPO'] => $input_data_sgrs_id_grupo["SGRS_DS_GRUPO"]));

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorGrupo($input_data_sgrs_id_grupo['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] . '|' . $SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            $form->populate($data_pesq);
            foreach ($data_pesq["SOLICITACOES_ESCOLHIDAS"] as $value) {
                $form->addElement('hidden', 'id_' . $value, array(
                    'name' => 'SOLICITACOES_ESCOLHIDAS',
                    'value' => $value,
                    'isArray' => true,
                ));
            }
        }
        $this->view->formVincular = $formVincular;
        $this->view->form = $form;
        $this->view->title = "Associar Sostis";
    }

    public function vincularpesquisarAction () {
        $form = new Sosti_Form_PesquisarTodasSolicitacoes();
        $acaoSolicit = new Zend_Form_Element_Hidden('ACAO_SOLICIT');
//        $formVincular = new Sosti_Form_Vincular();
        $calculaTempo = new App_View_Helper_Calculahorasla();
        $form->setAttrib('id', 'pesq');
        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            /**
             * Verifica se existe alguma solicitação da caixa de entrada do desenvolvimento
             * e sustentação
             */
            foreach ($data_pesq["solicitacao"] as $k => $dp) {
                $dpDecode[] = Zend_Json::decode($dp);
                $arrayCaixaEntrada[] = $dpDecode[$k]['MODE_ID_CAIXA_ENTRADA'];
            }
            if (in_array("1", $arrayCaixaEntrada) || in_array("2", $arrayCaixaEntrada)) {
                $urlRedir = explode('|', $data_pesq["urlcaixa"]);
                $this->_helper->flashMessenger(array(
                    'message' => 'A solicitação filtrada não está disponível para esse tipo de vinculação.',
                    'status' => 'notice'
                ));
                $this->_helper->_redirector($urlRedir[2], $urlRedir[1], ($urlRedir[0] == '') ? ('sosti') : ($urlRedir[0]));
            }
            if ((count($data_pesq["solicitacao"]) > 0) && ($data_pesq["acao"] == "VINCULAR ENTRE CAIXAS")) {
                foreach ($data_pesq["solicitacao"] as $value) {
                    $jsonDecodeSol = Zend_Json::decode($value);
                    $prazo = $calculaTempo->calculahorasla($jsonDecodeSol['TEMPO_TOTAL']);
                    $tempoTotal = $prazo['prazo_total'][0] . "D " . $prazo['prazo_total'][1] . "h " . $prazo['prazo_total'][2] .
                        "m " . $prazo['prazo_total'][3] . "s";
                    $jsonDecodeSol['PRAZO'] = $tempoTotal;
                    $result = Zend_Json::encode($jsonDecodeSol);
                    $form->addElement('hidden', 'id_' . $result, array(
                        'name' => 'SOLICITACOES_ESCOLHIDAS',
                        'value' => $result,
                        'isArray' => true,
                    ));
                }
            }
        }
        $form->addElement('hidden', 'id_' . $data_pesq["urlcaixa"], array(
            'name' => 'urlcaixa',
            'value' => $data_pesq["urlcaixa"],
            'isArray' => false,
        ));
        if ($data_pesq['acao'] != null) {
            $form->addElement($acaoSolicit->setValue($data_pesq['acao']));
        }

        $form_valores_padrao = $form->getValues();

        if ($this->_getParam('nova') === '1') {
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }
        if ($data_pesq["acao"] != "VINCULAR ENTRE CAIXAS") {
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $this->view->title = "VINCULAR ENTRE CAIXAS";
                $msg_to_user = "O preenchimento de um dos campos de pesquisa é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            if ($data_pesq['SGRS_ID_GRUPO']) {
                $input_data_sgrs_id_grupo = Zend_Json::decode($data_pesq['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data_pesq['SGRS_ID_GRUPO'] => $input_data_sgrs_id_grupo["SGRS_DS_GRUPO"]));
                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorGrupo($input_data_sgrs_id_grupo['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] . '|' . $SserServico_p["SSER_IC_TOMBO"] . '|' . $SserServico_p["SSER_IC_VIDEOCONFERENCIA"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            if ($data_pesq['TRF1_SECAO']) {
                $secao = explode('|', $data_pesq['TRF1_SECAO']);

                $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
                $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao[0], $secao[1]);
                $trf1_secao = $form->getElement('SECAO_SUBSECAO');
                foreach ($Lotacao_array as $value) {
                    $trf1_secao->addMultiOptions(array($value["LOTA_SIGLA_SECAO"] . '|' . $value['LOTA_COD_LOTACAO'] . '|' . $value["LOTA_TIPO_LOTACAO"] => $value["LOTA_SIGLA_LOTACAO"] . ' - ' . $value["LOTA_DSC_LOTACAO"] . ' - ' . $value["LOTA_COD_LOTACAO"] . ' - ' . $value["LOTA_SIGLA_SECAO"] . ' - ' . $value["LOTA_LOTA_COD_LOTACAO_PAI"]));
                }
            }

            if ($data_pesq["SOLICITACOES_ESCOLHIDAS"]) {
                foreach ($data_pesq["SOLICITACOES_ESCOLHIDAS"] as $value) {
                    $form->addElement('hidden', 'id_' . $value, array(
                        'name' => 'SOLICITACOES_ESCOLHIDAS',
                        'value' => $value,
                        'isArray' => true,
                    ));
                }
                $this->view->solicitacoesEscolhidas = $data_pesq["SOLICITACOES_ESCOLHIDAS"];
            }

            if ($form->isValid($data_pesq)) {
                $data_pesq = $this->getRequest()->getPost();
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "VINCULAR ENTRE CAIXAS";
                return;
            }
        }

        if ((!is_null($data_pesq)) && ($data_pesq["acao"] != "VINCULAR ENTRE CAIXAS")) {
            $this->view->ultima_pesq = true;
            $this->view->dadosPesquisa = $data_pesq;
            /* paginação */
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /* Ordenação das paginas */
            $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

            $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();

            $count = $dados->getTodasSolicitacoesCount($data_pesq);

            if ($count["COUNT"] <= 500) {
                $rows = $dados->getTodasSolicitacoes($data_pesq, $order);
            } else {
                $this->view->form = $form;
                $this->view->title = "VINCULAR ENTRE CAIXAS";
                if ($data_pesq["SOLICITACOES_ESCOLHIDAS"]) {
                    foreach ($data_pesq["SOLICITACOES_ESCOLHIDAS"] as $value) {
                        $form->addElement('hidden', 'id_' . $value, array(
                            'name' => 'SOLICITACOES_ESCOLHIDAS',
                            'value' => $value,
                            'isArray' => true,
                        ));
                    }
                    $this->view->solicitacoesEscolhidas = $data_pesq["SOLICITACOES_ESCOLHIDAS"];
                }
                $msg_to_user = "A pesquisa retornou $count[COUNT] registros ultrapassou o maximo de 500 registros.  <br/> Informe mais parâmetros de pesquisa. <br/> Por Exemplo, limite um período de tempo.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            $fim = count($rows);
            for ($i = 0; $i < $fim; $i++) {
                unset($rows[$i]['MOFA_DH_FASE']);
                unset($rows[$i]['DATA_ATUAL']);
            }
//            $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
//            foreach ($rows as $k=>$r) {
//                $arrayRows[] = $r;
////            Zend_Debug::dump($arrayRows[$k]["MODE_ID_CAIXA_ENTRADA"]);exit;
//                $caixaEntrada = $caixaEntrada->getCaixaEntrada("1");
//                Zend_Debug::dump($caixaEntrada["CXEN_ID_TIPO_CAIXA"]);exit;
//                $arrayRows[$k]['CXEN_ID_TIPO_CAIXA'] = $caixaEntrada["CXEN_ID_TIPO_CAIXA"];
//            }
//            Zend_Debug::dump($arrayRows);exit;
            if (!is_null($rows)) {
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(100);
            }

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            if ($data_pesq['SGRS_ID_GRUPO']) {
                $input_data_sgrs_id_grupo = Zend_Json::decode($data_pesq['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data_pesq['SGRS_ID_GRUPO'] => $input_data_sgrs_id_grupo["SGRS_DS_GRUPO"]));

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorGrupo($input_data_sgrs_id_grupo['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] . '|' . $SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            $form->populate($data_pesq);
            foreach ($data_pesq["SOLICITACOES_ESCOLHIDAS"] as $value) {
                $form->addElement('hidden', 'id_' . $value, array(
                    'name' => 'SOLICITACOES_ESCOLHIDAS',
                    'value' => $value,
                    'isArray' => true,
                ));
            }
        }
        if ($rows[0] != null) {
            $principalData = $rows[0];
            $arrayCaixa = Sosti_Model_DataMapper_LinkPorCaixa::enderecoPorId();
            if ($principalData["SNAS_ID_NIVEL"]) {
                $keyValue = $principalData["MODE_ID_CAIXA_ENTRADA"] . '.' . $principalData["SNAS_ID_NIVEL"];
            } else {
                $keyValue = $principalData["MODE_ID_CAIXA_ENTRADA"];
            }
            $destino = explode('/', $arrayCaixa[$keyValue]);
            $destinoController = $destino[0];
            $destinoAction = $destino[1];
            /**
             * Verifica se o usuário tem acesso a caixa onde ele deseja realizar 
             * a vinculação
             */
            $userNs = new Zend_Session_Namespace('userNs');
            $permissao = new Application_Model_DbTable_OcsTbAspasPapelSistema();
            $arrayNotAccess = array();
            foreach ($permissao->getNotAccess($userNs->matricula) as $k => $v) {
                if ($v["MODL_NM_MODULO"] == "sosti") {
                    $arrayNotAccess[$k] = $v["CTRL_NM_CONTROLE_SISTEMA"] . '/' . $v["ACAO_NM_ACAO_SISTEMA"];
                }
            }
            if ($idNivel == 'null') {
                if (in_array($arrayCaixa[$idCaixa], $arrayNotAccess)) {
                    $this->_helper->flashMessenger(array('message' => 'Você não possui acesso à caixa onde se encontra esta solicitação.', 'status' => 'notice'));
                    $this->_helper->_redirector($destinoAction, $destinoController, 'sosti');
                }
            } else {
                if (in_array($arrayCaixa[$idCaixa . '.' . $idNivel], $arrayNotAccess)) {
                    $this->_helper->flashMessenger(array('message' => 'Você não possui acesso à caixa onde se encontra esta solicitação.', 'status' => 'notice'));
                    $this->_helper->_redirector($destinoAction, $destinoController, 'sosti');
                }
            }
        }
//            Zend_Debug::dump($rows);
//        $this->view->formVincular = $formVincular;
        $this->view->form = $form;
        $this->view->title = "VINCULAR ENTRE CAIXAS";
    }

}
