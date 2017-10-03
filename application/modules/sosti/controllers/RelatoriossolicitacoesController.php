<?php

class Sosti_RelatoriossolicitacoesController extends Zend_Controller_Action
{

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

        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        $this->_form = new Sosti_Form_RelatoriosSolicitacoes();
    }

    public function solicitacoesperiodoAction() {

        $this->view->title = "Relatório de Solicitações por Período";
        $this->view->form = $this->_form;
        $Ns_sisad_solicitacoesperiodo = new Zend_Session_Namespace('Ns_sisad_solicitacoesperiodo');
        $userNs = new Zend_Session_Namespace('userNs');
        $timeInterval = new App_TimeInterval();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data['SIGLA_SECAO'] = $userNs->siglasecao;
            $data['CODIGO_LOTACAO'] = $userNs->codlotacao;
            $data['DESCRICAO_LOTACAO'] = $userNs->descicaolotacao;
            $data['SIGLA_LOTACAO'] = $userNs->siglalotacao;
            $Ns_sisad_solicitacoesperiodo->data = $data;
        }
        if (!is_null($Ns_sisad_solicitacoesperiodo->data)) {

            $this->view->cabecalho = $Ns_sisad_solicitacoesperiodo->data;
            $this->view->exibicao = $Ns_sisad_solicitacoesperiodo->data['EXIBICAO'];

            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            $itemsperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');

            //Ordenação das paginas
            $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
            $order_direction = $this->_getParam('direcao', 'ASC');

            $sosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $rows = $sosTbSsolSolicitacao->getTodasSolicitacoesUnidade($Ns_sisad_solicitacoesperiodo->data);


            if (!empty($rows)) {
                // $qtde = count($rows);
                // $this->view->qtde = $qtde;
                // if ($qtde < 5000) {
                for ($i = 0; $i < count($rows); $i++) {
                    if (!empty($rows[$i]["DH_BAIXA"])) {
                        $data = $rows[$i]["DH_BAIXA"];
                    } else {
                        $data = date("d/m/Y H:i:s");
                    }

                    $tempoTotal = $timeInterval->tempoTotal($rows[$i]["DH_CADASTRO"], $data);
                    $rows[$i]['TEMPO_TOTAL_FORMATADO'] = $tempoTotal;
                    $mes = substr($rows[$i]["DH_CADASTRO"], 3, 2);
                    $ano = substr($rows[$i]["DH_CADASTRO"], 6, 4);
                    $descricaoMes = App_Sosti_CalendarioSla::getDescricaoMes($mes);
                    $qtdeMes[] = $descricaoMes . "/" . $ano;
                    $qtdeSituacao[] = $rows[$i]["DESCRICAO_FASE"];
                }
                $somatorioSituacao = array_count_values($qtdeSituacao);
                $somatorioMensal = array_count_values($qtdeMes);

                $this->view->somatorioMensal = $somatorioMensal;
                $this->view->somatorioSituacao = $somatorioSituacao;

                $Ns_sisad_solicitacoesperiodo->rows = $rows;
                $Ns_sisad_solicitacoesperiodo->somatorioMensal = $somatorioMensal;
                $Ns_sisad_solicitacoesperiodo->somatorioSituacao = $somatorioSituacao;

                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage($itemsperpage);

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
                //  }
            } else {
                $msg = "Não foram encontrados registros para os parâmetros pesquisados. <br/> Informe mais parâmetros de pesquisa.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }

    public function solicitacoesunidadeperiodoAction() {
        $this->view->title = "Relatório de Solicitações por Unidade e Período";
        $Ns_solicitacoesunidadeperiodo = new Zend_Session_Namespace('Ns_solicitacoesunidadeperiodo');
        $rhCentalLot = new Application_Model_DbTable_RhCentralLotacao();
        $timeInterval = new App_TimeInterval();
        $form = $this->_form;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $Ns_solicitacoesunidadeperiodo->data = $data;
        }

        if (!is_null($Ns_solicitacoesunidadeperiodo->data)) {

            $this->view->cabecalho = $Ns_solicitacoesunidadeperiodo->data;
            $this->view->exibicao = $Ns_solicitacoesunidadeperiodo->data['EXIBICAO'];

            if (!empty($Ns_solicitacoesunidadeperiodo->data['DOCM_CD_LOTACAO_GERADORA'])) {
                $this->view->cabecalho['unidadeSolicitante'] = $Ns_solicitacoesunidadeperiodo->data['DOCM_CD_LOTACAO_GERADORA'];
            } else {
                $lot = explode("|", $Ns_solicitacoesunidadeperiodo->data['SECAO_SUBSECAO']);
                $descricaoUnidade = $rhCentalLot->getDescricaoLotacao($lot[0], $lot[1]);
                $this->view->cabecalho['unidadeSolicitante'] = $descricaoUnidade[0]['DESCRICAO'];
            }

            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            $itemsperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');

            //Ordenação das paginas
            $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
            $order_direction = $this->_getParam('direcao', 'ASC');

            $sosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $rows = $sosTbSsolSolicitacao->getTodasSolicitacoesUnidade($Ns_solicitacoesunidadeperiodo->data);


            if (!empty($rows)) {

                $qtde = count($rows);
                $this->view->qtde = $qtde;

                for ($i = 0; $i < count($rows); $i++) {
                    if (!empty($rows[$i]["DH_BAIXA"])) {
                        $data = $rows[$i]["DH_BAIXA"];
                    } else {
                        $data = date("d/m/Y H:i:s");
                    }

                    $tempoTotal = $timeInterval->tempoTotal($rows[$i]["DH_CADASTRO"], $data);
                    $rows[$i]['TEMPO_TOTAL_FORMATADO'] = $tempoTotal;
                    $mes = substr($rows[$i]["DH_CADASTRO"], 3, 2);
                    $ano = substr($rows[$i]["DH_CADASTRO"], 6, 4);
                    $descricaoMes = App_Sosti_CalendarioSla::getDescricaoMes($mes);
                    $qtdeMes[] = $descricaoMes . "/" . $ano;
                    $qtdeSituacao[] = $rows[$i]["DESCRICAO_FASE"];
                }

                $somatorioMensal = array_count_values($qtdeMes);
                $somatorioSituacao = array_count_values($qtdeSituacao);
                $this->view->somatorioMensal = $somatorioMensal;
                $this->view->somatorioSituacao = $somatorioSituacao;

                $Ns_solicitacoesunidadeperiodo->rows = $rows;
                $Ns_solicitacoesunidadeperiodo->somatorioMensal = $somatorioMensal;
                $Ns_solicitacoesunidadeperiodo->somatorioSituacao = $somatorioSituacao;


                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage($itemsperpage);

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            } else {
                $msg = "Não foram encontrados registros para os parâmetros pesquisados. <br/> Informe mais parâmetros de pesquisa.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }

    public function solicitacoesperiodopdfAction() {

        set_time_limit(1200);
        $Ns_sisad_solicitacoesperiodo = new Zend_Session_Namespace('Ns_sisad_solicitacoesperiodo');

        $this->view->cabecalho = $Ns_sisad_solicitacoesperiodo->data;
        $this->view->rows = $Ns_sisad_solicitacoesperiodo->rows;
        $this->view->somatorioMensal = $Ns_sisad_solicitacoesperiodo->somatorioMensal;
        $this->view->somatorioSituacao = $Ns_sisad_solicitacoesperiodo->somatorioSituacao;
        $this->view->exibicao = $Ns_sisad_solicitacoesperiodo->data['EXIBICAO'];

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

        if ($Ns_sisad_solicitacoesperiodo->data['EXIBICAO'] == "C") {
            $orientacao = "P";
        }
        if ($Ns_sisad_solicitacoesperiodo->data['EXIBICAO'] == "S") {
            $orientacao = "L";
        }
        $mpdf->AddPage($orientacao, '', '0', '1');
        $mpdf->WriteHTML($body);
        $mpdf->Footer();
        $name = 'Relatório_Sostis_periodo_' . str_replace('/', '_', $Ns_sisad_solicitacoesperiodo->data['DATA_INICIAL'] . '_' . $Ns_sisad_solicitacoesperiodo->data['DATA_FINAL']) . '.pdf';
        $mpdf->Output($name, 'D');
    }

    public function solicitacoesunidadeperiodopdfAction() {

        set_time_limit(1200);
        $Ns_solicitacoesunidadeperiodo = new Zend_Session_Namespace('Ns_solicitacoesunidadeperiodo');
        $rhCentalLot = new Application_Model_DbTable_RhCentralLotacao();

        $this->view->cabecalho = $Ns_solicitacoesunidadeperiodo->data;
        if (!empty($Ns_solicitacoesunidadeperiodo->data['DOCM_CD_LOTACAO_GERADORA'])) {
            $this->view->cabecalho['unidadeSolicitante'] = $Ns_solicitacoesunidadeperiodo->data['DOCM_CD_LOTACAO_GERADORA'];
        } else {
            $lot = explode("|", $Ns_solicitacoesunidadeperiodo->data['SECAO_SUBSECAO']);
            $descricaoUnidade = $rhCentalLot->getDescricaoLotacao($lot[0], $lot[1]);
            $this->view->cabecalho['unidadeSolicitante'] = $descricaoUnidade[0]['DESCRICAO'];
        }

        $this->view->rows = $Ns_solicitacoesunidadeperiodo->rows;
        $this->view->somatorioMensal = $Ns_solicitacoesunidadeperiodo->somatorioMensal;
        $this->view->somatorioSituacao = $Ns_solicitacoesunidadeperiodo->somatorioSituacao;
        $this->view->exibicao = $Ns_solicitacoesunidadeperiodo->data['EXIBICAO'];


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


        if ($Ns_solicitacoesunidadeperiodo->data['EXIBICAO'] == "C") {
            $orientacao = "P";
        }
        if ($Ns_solicitacoesunidadeperiodo->data['EXIBICAO'] == "S") {
            $orientacao = "L";
        }

        $mpdf->AddPage($orientacao, '', '0', '1');
        $mpdf->WriteHTML($body);

        $name = 'Relatório_Sostis_Unidade_Período_' . str_replace('/', '_', $Ns_solicitacoesunidadeperiodo->data['DATA_INICIAL'] . '_' . $Ns_solicitacoesunidadeperiodo->data['DATA_FINAL']) . '.pdf';
        $mpdf->Output($name, 'D');
    }

    public function solicitacoesperiodoexcelAction() {

        set_time_limit(1200);
        $this->_helper->layout->disableLayout();
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=solicitacoesPeriodo.xls");

        $Ns_sisad_solicitacoesperiodo = new Zend_Session_Namespace('Ns_sisad_solicitacoesperiodo');
        $this->view->cabecalho = $Ns_sisad_solicitacoesperiodo->data;
        $this->view->rows = $Ns_sisad_solicitacoesperiodo->rows;
        $this->view->somatorioMensal = $Ns_sisad_solicitacoesperiodo->somatorioMensal;
        $this->view->somatorioSituacao = $Ns_sisad_solicitacoesperiodo->somatorioSituacao;
        $this->view->exibicao = $Ns_sisad_solicitacoesperiodo->data['EXIBICAO'];
    }

    public function solicitacoesunidadeperiodoexcelAction() {

        set_time_limit(1200);
        $this->_helper->layout->disableLayout();
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=solicitacoesUnidadePeriodo.xls");

        $rhCentalLot = new Application_Model_DbTable_RhCentralLotacao();
        $Ns_solicitacoesunidadeperiodo = new Zend_Session_Namespace('Ns_solicitacoesunidadeperiodo');
        $this->view->cabecalho = $Ns_solicitacoesunidadeperiodo->data;
        if (!empty($Ns_solicitacoesunidadeperiodo->data['DOCM_CD_LOTACAO_GERADORA'])) {
            $this->view->cabecalho['unidadeSolicitante'] = $Ns_solicitacoesunidadeperiodo->data['DOCM_CD_LOTACAO_GERADORA'];
        } else {
            $lot = explode("|", $Ns_solicitacoesunidadeperiodo->data['SECAO_SUBSECAO']);
            $descricaoUnidade = $rhCentalLot->getDescricaoLotacao($lot[0], $lot[1]);
            $this->view->cabecalho['unidadeSolicitante'] = $descricaoUnidade[0]['DESCRICAO'];
        }
        $this->view->rows = $Ns_solicitacoesunidadeperiodo->rows;
        $this->view->somatorioMensal = $Ns_solicitacoesunidadeperiodo->somatorioMensal;
        $this->view->somatorioSituacao = $Ns_solicitacoesunidadeperiodo->somatorioSituacao;
        $this->view->exibicao = $Ns_solicitacoesunidadeperiodo->data['EXIBICAO'];
    }

    public function solicitacoesporservicoAction() {

        $facadeRelatorios = new Sosti_Facade_Relatorios();
        $Ns_solicitacoesporservico = new Zend_Session_Namespace('Ns_solicitacoesporservico');
        $form = new Sosti_Form_RelatoriosSolicitacoesPorServico();

        if ($this->getRequest()->isPost()) {
            $parametros = $this->getRequest()->getPost();
            //Buscar dados para preencher os campos do form antes da validacao
            if ($parametros['TRF1_SECAO'] != "") {
                $facadeRelatorios->buscaCaixaValidacaoForm($parametros, $form->getElement('LOTA_COD_LOTACAO'));
//                Zend_Debug::dump($facadeRelatorios->buscaCaixaValidacaoForm($parametros, $form->getElement('LOTA_COD_LOTACAO')));exit;
            }
            if ($parametros['LOTA_COD_LOTACAO'] != "") {
                $facadeRelatorios->buscaCatServValidacaoForm($parametros, $form->getElement('CTSS_NM_CATEGORIA_SERVICO'));
            }

            //validacao
            if ($form->isValid($parametros)) {
                $dados = $facadeRelatorios->listBusiness($parametros);
                if (count($dados) <= 0) {
                    $this->view->semDados = true;
                }

                $Ns_solicitacoesporservico->dados = $dados;
                $Ns_solicitacoesporservico->parametros = $parametros;
                $Ns_solicitacoesporservico->nomeCategoria = $parametros['NOM_CATEGORIA'];
                $Ns_solicitacoesporservico->caixa = $parametros["LOTA_COD_LOTACAO"];
                $this->view->caixa = $parametros["LOTA_COD_LOTACAO"];
                $this->view->dados = $dados;
                $this->view->nomeCategoria = $parametros['NOM_CATEGORIA'];
            } else {
                $form->populate($parametros);
                $this->view->dados = array();
                $this->view->nomeCategoria = '';
            }
        }
        $this->view->form = $form;

        $this->view->title = "Relatório de Solicitações por Serviço";
    }

    public function solicitacoesporservicoexcelAction() {

        set_time_limit(1200);
        $this->_helper->layout->disableLayout();
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=solicitacoesPorServico.xls");
        $Ns_solicitacoesporservico = new Zend_Session_Namespace('Ns_solicitacoesporservico');
        $this->view->dados = $Ns_solicitacoesporservico->dados;
        $this->view->parametros = $Ns_solicitacoesporservico->parametros;
        $this->view->caixa = $Ns_solicitacoesporservico->caixa;
        
        $this->view->nomeCategoria = $Ns_solicitacoesporservico->nomeCategoria;
    }

    public function solicitacoesporservicopdfAction() {

        set_time_limit(1200);
        $this->_helper->layout->disableLayout();
        $Ns_solicitacoesporservico = new Zend_Session_Namespace('Ns_solicitacoesporservico');
        $this->view->dados = $Ns_solicitacoesporservico->dados;
        $this->view->parametros = $Ns_solicitacoesporservico->parametros;
        $this->view->nomeCategoria = $Ns_solicitacoesporservico->nomeCategoria;
        $this->view->caixa = $Ns_solicitacoesporservico->caixa;

        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();

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
        $orientacao = "P";
        $mpdf->AddPage($orientacao, '', '0', '1');
        $mpdf->WriteHTML($body);
        $mpdf->Footer();
        $name = 'Relatorio_solicitacoes_por_servico' . str_replace('/', '_', $Ns_solicitacoesporservico->data['DATA_INICIAL'] . '_' . $Ns_solicitacoesporservico->data['DATA_FINAL']) . '.pdf';
        $mpdf->Output($name, 'D');
    }

    public function ajaxgetgruposervicosAction() {

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $sgSecao = $this->_getParam('sgSecao', '');
        $cdLotacao = $this->_getParam('cdLotacao', '');
        $sgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao($sgSecao, $cdLotacao);
        $this->view->grupos = $sgrsGrupoServico;
    }
    
    public function ajaxsecaolotacaosiglaAction()
    {
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $RhCentralLotacao = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $Lotacao_array = $RhCentralLotacao->getSecaoLotacaoSigla($secao);
        $this->view->Lotacao_array = $Lotacao_array;
    }
    
    public function ajaxcategoriaservicoAction()
    {
        $SosTbCtssCategServSistema = new Application_Model_DbTable_SosTbCtssCategServSistema();
        $categoriaServico = $SosTbCtssCategServSistema->fetchAll(null, 'CTSS_NM_CATEGORIA_SERVICO ASC');
        if ($this->_getParam('dsv') == 1) {
            $this->view->categoriaServico = $categoriaServico;
        } else {
            $this->view->categoriaServico = 0;
        }
    }
    
    public function solicitacoesporsetorAction()
    {
        $form = new Sosti_Form_SolicitacoesSetor();
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $aNamespace = new Zend_Session_Namespace('userNs');
        $this->view->assign(array(
            'title' => 'SOLICITAÇÕES ABERTAS POR SETOR',
            'form'  => $form)
        );
    }
    
    public function ajaxcaixaentradaAction()
    {
        $sigla = $this->_getParam('sigla');
        $this->view->arrayCaixa = Sosti_Model_DataMapper_CaixaServico::caixaEntrada($sigla);
    }
    
    public function ajaxservicocaixaAction()
    {
        $cx = $this->_getParam('cx');
        $this->view->arrayServico = Sosti_Model_DataMapper_CaixaServico::servico($cx);
    }
    
    public function gridsolicitacoessetorAction()
    {
        $arrayData = array();
        $data = $this->getRequest()->getPost();
        $dataHora = App_Controller_Plugin_DataHoraServidor::now();
        $lotacao = new Application_Model_DbTable_RhCentralLotacao();
        $userNs = new Zend_Session_Namespace('userNs');
        /** Carrega o grupo de serviço pesquisado */
        if ($data["SGRS_ID_GRUPO"] != "") {
            $grupo = Sosti_Model_DataMapper_CaixaServico::servico($data["SGRS_ID_GRUPO"]);
        } else {
            $grupo[0]["SGRS_DS_GRUPO"] = "";
        }
        /** Carrega a unidade */
        if ($data["UNPE_SG_SECAO"] != "") {
            $lt = explode("|", $data["UNPE_SG_SECAO"]);
            $descLotacao = $lotacao->getDescricaoLotacao($lt[0], $lt[1]);
        } else {
            $descLotacao[0]["DESCRICAO"] = "";
        }
        $arrayData = Sosti_Model_DataMapper_SolicitacaoAbertaPorSetor::getQuery($data);
        $cache = new App_Controller_Plugin_Cache();
        $assignArray = array(
            'arrayData'         => $arrayData,
            'arrayPainelBotoes' => array('excel' => true, 'pdf' => true, 'el' => false),
            'dataHora'          => $dataHora,
            'dataInicial'       => $data["DATA_INICIAL"],
            'dataFinal'         => $data["DATA_FINAL"],
            'unidade'           => $descLotacao[0]["DESCRICAO"],
            'grupo'             => $grupo[0]["SGRS_DS_GRUPO"],
            'title'             => 'SOLICITAÇÕES ABERTAS POR SETOR'
        );
        $this->view->assign($assignArray);
        $cache->save($assignArray, 'ABERTAS_POR_SETOR_'.$userNs->matricula);
    }
    
    public function excelAction()
    {
        $idCache = $this->_getParam('id');
        $cache = new App_Controller_Plugin_Cache();
        $this->view->cache = $cache->load($idCache);
        $this->_helper->layout->disableLayout();
        App_Controller_Plugin_ArquivoExportacao::excel($idCache);
    }

    public function pdfAction()
    {
        $idCache = $this->_getParam('id');
        $cache = new App_Controller_Plugin_Cache();
        $this->view->cache = $cache->load($idCache);
        $this->_helper->layout->disableLayout();
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        App_Controller_Plugin_ArquivoExportacao::pdf($idCache, $body);
    }
}
