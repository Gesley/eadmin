<?php

class Sosti_DetalhesolicitacaoController extends Zend_Controller_Action {

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
    }

    public function indexAction() {
        
    }

    public function detalhesolAction() {
        $form = new Sosti_Form_Anexo();
        $form->anexoUnico();
        $this->view->form = $form;
        $this->view->act = $this->_getParam('act');
        $userNs = new Zend_Session_Namespace('userNs');
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $SosTbNegaNegociaGarantia = new Application_Model_DbTable_SosTbNegaNegociaGarantia();
        $faturamento = new Trf1_Sosti_Negocio_Faturamento();
        $osVinculada = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $docmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $associacao = new Application_Model_DbTable_SosTbAsscAssociacao();
        
        $LfsefichaServico = new Application_Model_DbTable_LfsefichaServico ();
        
        $objSoftware = new Application_Model_DbTable_SosTbLsfwSoftware ();
        $objHardware = new Application_Model_DbTable_SosTbLhdwMaterialAlmox();
        $objServico  = new Application_Model_DbTable_SosTbTpseTipoServico();
        $objTombo    = new Application_Model_DbTable_TomboTiCentral();
        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $arrayPerfisPessoa = $ocsTbPupePerfilUnidPessoa->getTodosPerfilPessoa($userNs->matricula);
        
        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());

            $this->view->controller = $data["CONTROLLER"];
            $this->view->caixa = $data["ACTION"];
            /**
             * Verifica a categorização de Serviços e Prioridades dos 
             * Sostis da caixa de Desenvolvimento / Sustentação. 
             */
            $idCaixa = $this->_getParam('idcaixa');
            if ($idCaixa != null) {
                $DocmDocumento = $SosTbSsolSolicitacao->getDadosSolicitacao($data['SSOL_ID_DOCUMENTO'], 2, true);
                $this->view->idCaixa = $idCaixa;
            } else {
                $DocmDocumento = $SosTbSsolSolicitacao->getDadosSolicitacao($data['SSOL_ID_DOCUMENTO'], null, true);
                
            }
            // fim 
            $tpVincDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
            $tpVinculacao = $tpVincDoc->fetchRow('VIDC_ID_DOC_PRINCIPAL = '.$DocmDocumento["SSOL_ID_DOCUMENTO"]);
            if (($tpVinculacao["VIDC_ID_TP_VINCULACAO"] == null) || ($tpVinculacao["VIDC_ID_TP_VINCULACAO"] == 4)) {
                $tpVinculacaoSol = 4;
                $faseVincSolicit = 1035;
            } else {
                $tpVinculacaoSol = $tpVinculacao["VIDC_ID_TP_VINCULACAO"];
                $faseVincSolicit = 2003;
            }
            $DocmDocumentoHistorico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($data['SSOL_ID_DOCUMENTO']);
            $primeiraFase = end($DocmDocumentoHistorico);
            $descTombo = $objTombo->getDescTombo($DocmDocumento['SSOL_NR_TOMBO']); 
            $this->view->DocmDocumento = $DocmDocumento;
            $this->view->DescTombo     = $descTombo;
            $numeroOs = $osVinculada->getPrincipalOs($data['SSOL_ID_DOCUMENTO']);
            $doc  = new Application_Model_DbTable_SadTbDocmDocumento();
            $idDocPrincipal = $doc->fetchRow('DOCM_NR_DOCUMENTO = '.$numeroOs[0]['DOCM_NR_DOCUMENTO'])->toArray();
            foreach ($tpVincDoc->getDocVinculado($idDocPrincipal["DOCM_ID_DOCUMENTO"]) as $k=>$p) {
                $arrayAssoc[] = $p["DOCM_NR_DOCUMENTO"];
            }
            if ($numeroOs[0]['DOCM_NR_DOCUMENTO'] != false) {
                $idOs = $docmDocumento->getDocumentoIdByNrDoc($numeroOs[0]['DOCM_NR_DOCUMENTO']);
                $dadosOs = $docmDocumento->getDadosDCMTO($idOs[0]['DOCM_ID_DOCUMENTO']);
                $this->view->dadosOs = $dadosOs;
                /** Pega as solicitações associadas a OS */
                $arrayAssociadas = $osVinculada->getDadosSolicAssociadas(implode(',', $arrayAssoc), 7);
                $this->view->arrayAssociadas = $arrayAssociadas;
            } else {
                $this->view->dadosOs = array();
            }
            /** Verifica se é uma OS ou se tem associação a uma OS */
            if ($primeiraFase["FADM_ID_FASE"] == 1092) {
                $this->view->eOs = true; 
            } elseif ($arrayAssociadas != false) {
                $this->view->eOs = true; 
            } else {
                $this->view->eOs = false; 
            }
            $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico;
            foreach ($DocmDocumentoHistorico as $ds) {
                $fasesHistorico[] = $ds["FADM_ID_FASE"];
            }
            $faseEncaminhamento = array_search('1092', $fasesHistorico);
            $faseRecusa = array_search('1019', $fasesHistorico);
            $ultimaFase = array_pop($DocmDocumentoHistorico);
            /** 
             * Verifica o perfil Desenvolvimento e Sustentação e se a última fase
             * é de solicitação recusada.
             */
            foreach ($arrayPerfisPessoa as $pf) {
                $arrayPerfis[] = $pf["PERF_DS_PERFIL"];
            }
            /**
             * Verifica se a recusa foi a lançada após o último encaminhamento 
             * para caixa
             */
            if ($faseRecusa !== false) {
                if ($faseRecusa < $faseEncaminhamento) {
                    $this->view->verificaSeOs = in_array("DESENVOLVIMENTO E SUSTENTAÇÃO", $arrayPerfis);
                }
            }
            /**
             * Verifica se o perfil de Gestão de Demandas e se as solicitação está
             * baixada.
             */
            if ($DocmDocumentoHistorico[0]["FADM_ID_FASE"] == "1000") {
                if ($ultimaFase["FADM_ID_FASE"] == "1092") {
                    $this->view->verificaSeOs = in_array("GESTÃO DE DEMANDAS DE TI", $arrayPerfis);
                }
            }

            $DocmDocumentoVinculacao = $SosTbSsolSolicitacao->getPrincipalVinculacao($data['SSOL_ID_DOCUMENTO']);

            $DocmDocumentoVinculacao = $SosTbSsolSolicitacao->getPrincipalVinculacao($data['SSOL_ID_DOCUMENTO'], $tpVinculacaoSol);
            $this->view->DocmDocumentoVinculacao = $DocmDocumentoVinculacao;

            $DocmListaVinculados = $SosTbSsolSolicitacao->getListaSolicitacoesVinculadas($data['SSOL_ID_DOCUMENTO'], null, $tpVinculacaoSol, $faseVincSolicit);
            $this->view->DocmListaVinculados = $DocmListaVinculados;

            $DocmNaoConformidades = $SosTbSsolSolicitacao->getNaoConformidades($data['SSOL_ID_DOCUMENTO']);
            $this->view->DocmNaoConformidades = $DocmNaoConformidades;

            $DocmContoleQaulidade = $SosTbSsolSolicitacao->getDefeitosSolicitacao($DocmDocumento['MOFA_ID_MOVIMENTACAO']);
            $this->view->DocmDefeitos = $DocmContoleQaulidade;

            $DocmPapdParteProcDoc = $SadTbPapdParteProcDoc->getAcompanhantesAtivos($data['SSOL_ID_DOCUMENTO']);
            $this->view->DocmPapdParteProcDoc = $DocmPapdParteProcDoc;

            $DocmPapdParteProcDocPorOrdemDe = $SadTbPapdParteProcDoc->getPorOrdemDeSosti($data['SSOL_ID_DOCUMENTO']);
            $this->view->DocmPapdParteProcDocPorOrdemDe = $DocmPapdParteProcDocPorOrdemDe;

            $NegaNegociaGarantia = $SosTbNegaNegociaGarantia->getDetalheGarantia($data['SSOL_ID_DOCUMENTO']);
            
            $this->view->FaturamentoContratada = $faturamento->dadosFaturamentoContratada($data['SSOL_ID_DOCUMENTO']);
            $this->view->FaturamentoContratada[0]["PFDS_ID_SOLICITACAO"] = $data['SSOL_ID_DOCUMENTO'];
            $this->view->FaturamentoContratada[0]["DOCM_NR_DOCUMENTO"] = $DocmDocumento["DOCM_NR_DOCUMENTO"];
          
            $this->view->FaturamentoAfericao = $faturamento->dadosFaturamentoAfericao($data['SSOL_ID_DOCUMENTO']);
            $this->view->FaturamentoAfericao[0]["PFAF_ID_SOLICITACAO"] = $data['SSOL_ID_DOCUMENTO'];
            $this->view->FaturamentoAfericao[0]["DOCM_NR_DOCUMENTO"] = $DocmDocumento['DOCM_NR_DOCUMENTO'];
            if($this->view->FaturamentoContratada[0]["PFDS_NR_DCMTO_RIA_ESCLARECIDO"]){
                $this->view->FaturamentoAfericao[0]["RIA_DESENVOLVEDOR"] = $this->view->FaturamentoContratada[0]["PFDS_NR_DCMTO_RIA_ESCLARECIDO"];
            }else if($this->view->FaturamentoContratada[0]["PFDS_NR_DCMTO_RIA_ORIGINAL"]){
                $this->view->FaturamentoAfericao[0]["RIA_DESENVOLVEDOR"] = $this->view->FaturamentoContratada[0]["PFDS_NR_DCMTO_RIA_ORIGINAL"];
            }
                
            $this->view->FaturamentoContratante = $faturamento->dadosFaturamentoContratante($data['SSOL_ID_DOCUMENTO']);
            $this->view->FaturamentoContratante[0]["PFTR_ID_SOLICITACAO"] = $data['SSOL_ID_DOCUMENTO'];
            $this->view->FaturamentoContratante[0]["DOCM_NR_DOCUMENTO"] = $DocmDocumento['DOCM_NR_DOCUMENTO'];
            
            $this->view->qtdeSostisAssociados = $associacao->getQtdeSostiAssociado($data['SSOL_ID_DOCUMENTO']);
            $this->view->validaAba = $this->_getParam('idcaixa');
            /**
             * Carrega o fuso horário da seção onde o usuário está lotado
             */
            $tempoTimeInterval = new App_TimeInterval();
            $fusoHorario = $tempoTimeInterval->calculaFusoHorario($userNs->siglasecao);
            $this->view->fuso = ($fusoHorario === null) ? (0) : ($fusoHorario);
            if ($NegaNegociaGarantia != false) {
                $NegaNegociaGarantia['FUSO_HORARIO'] = ($fusoHorario === null) ? (0) : ($fusoHorario);
            }
            $this->view->NegaNegociaGarantia = $NegaNegociaGarantia;

            /*
             * Checklist
             */
            $verificaChecklist = false;
            $isCaixaHelpdesk = false;
            //Verifica se existe ficha de servico para a solicitacao
            if ($LfsefichaServico->verificaexitenciaFicha($data['SSOL_ID_DOCUMENTO'])) {
                $verificaChecklist = true;
                //Verifica se é caixa de gestao de demandas Helpdesk
                if ($idCaixa == Trf1_Sosti_Definicoes::CAIXA_GESTAO_DEMANDAS_DO_ATENDIMENTO_AO_USUARIOS) {
                    $isCaixaHelpdesk = true;
                }
                //Buscar os dados necessários das fichas de serviço
                $dadosFicha = $LfsefichaServico->getFichaServicoCompleta($data['SSOL_ID_DOCUMENTO']);
                $dadosServicos = $objServico->getTpServicoPorDocumento($data['SSOL_ID_DOCUMENTO']);
                $dadosSoftwares = $objSoftware->getSoftwaresPorDocumento($data['SSOL_ID_DOCUMENTO']);
                $dadosHardwares = $objHardware->getMaterialAlmoxPorDocumento($data['SSOL_ID_DOCUMENTO']);

                //tratamento dos campos IC
                $dadosFichaOp[0] = ($dadosFicha["LFSE_IC_GARANTIA"] == 'S') ? 'Garantia' : null;
                $dadosFichaOp[1] = ($dadosFicha["LFSE_IC_MANUTENCAO_EXTERNA"] == 'S') ? 'Manutenção Externa' : null;
                $dadosFichaOp[2] = ($dadosFicha["LFSE_IC_SCANDISK"] == 'S') ? 'Scandisk' : null;
                $dadosFichaOp[3] = ($dadosFicha["LFSE_IC_DESFRAGMENTACAO"] == 'S') ? 'Desfragmentação' : null;
                $dadosFichaOp[4] = ($dadosFicha["LFSE_IC_WINUPDATE"] == 'S') ? 'Winupdate' : null;
                $dadosFichaOp[5] = ($dadosFicha["LFSE_IC_EXCLUSAO_PROFILE"] == 'S') ? 'Exclusão Profile' : null;
                $dadosFichaOp[6] = ($dadosFicha["LFSE_IC_EXCLUSAO_ARQTEMP"] == 'S') ? 'Exclusão de Arquivos Temporários' : null;
                $dadosFichaOp[7] = ($dadosFicha["LFSE_IC_FORMATACAO"] == 'S') ? 'Formatação' : null;
                $dadosFichaOp[8] = ($dadosFicha["LFSE_IC_BACKUP"] == 'S') ? 'Backup' : null;

                //hardware
                if (count($dadosHardwares) > 0) {
                    $flag = 0;
                    foreach ($dadosHardwares as $h) {
                        $qtd_total_h = $objHardware->getQtdTotalMaterial($h['LHDW_ID_HARDWARE']);
                        $qtd_saida_h = $objHardware->getQtdMaterialSaida($h['LHDW_ID_HARDWARE']);
                        $qtd_h = (int) $qtd_total_h['QTD_TOTAL'] - (int) $qtd_saida_h['QTD_SAIDA'];
                        $dadosHardwares[$flag]['QTD_DISPONIVEL'] = $qtd_h;
                        $flag++;
                    }
                }

                //Softwares
                if (count($dadosSoftwares) > 0) {
                    $flag2 = 0;
                    foreach ($dadosSoftwares as $s) {
                        $qtd_total_s = $objSoftware->getQtdTotalSoftware($s['LSFW_ID_SOFTWARE']);
                        $qtd_saida_s = $objSoftware->getQtdLicencasSaida($s['LSFW_ID_SOFTWARE']);
                        $qtd_s = (int) $qtd_total_s['QTD_TOTAL'] - (int) $qtd_saida_s['QTD_SAIDA'];
                        $dadosSoftwares[$flag2]['QTD_DISPONIVEL'] = $qtd_s;
                        $flag2++;
                    }
                }

                //Dados do checklist completo
                $dadosChecklistGeral['checklist'] = $dadosFicha;
                $dadosChecklistGeral['opcoes'] = $dadosFichaOp;
                $dadosChecklistGeral['servicos'] = $dadosServicos;
                $dadosChecklistGeral['softwares'] = $dadosSoftwares;
                $dadosChecklistGeral['hardwares'] = $dadosHardwares;
            }

            //Mandando os dados pra view
            $this->view->verificaChecklist = $verificaChecklist;
            $this->view->isCaixaHelpdesk = $isCaixaHelpdesk;
            $this->view->dadosChecklistGestao = '$dadosChecklistGeral';
            $this->view->dadosChecklistGeral = $dadosChecklistGeral;
        }
    }

    public function detalhesolexportacaoAction() {
        set_time_limit(1200);
        $this->_helper->layout->disableLayout();
        $SosTbSsolSolicitacao  = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $TomboTiCentral        = new Application_Model_DbTable_TomboTiCentral();
//        $sosMaeqManutencao = new Application_Model_DbTable_SosTbMaeqManutencaoEqpto();

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost(); /* Aplica Filtros - Mantem Post */
            $i = 0;
            $this->render('titulo');
            /**
             * Adaptaçao para imprimir o relatorio detalhexlsii
             */
            ($this->_getParam('param') == 'detalhexlsii')?($this->render('cabecalho')):('');
            foreach ($post['solicitacao'] as $p) {
                $data[$i] = Zend_Json::decode($p);
                /**
                 * Verifica a categorização de Serviços e Prioridades dos 
                 * Sostis da caixa de Desenvolvimento / Sustentação. 
                 */
                $idCaixa = $this->_getParam('idcaixa');

                if ($idCaixa != null) {
                    $DocmDocumento = $SosTbSsolSolicitacao->getDadosSolicitacao($data[$i]['SSOL_ID_DOCUMENTO'], 2);
                    $this->view->idCaixa = $idCaixa;
                } else {
                    $DocmDocumento = $SosTbSsolSolicitacao->getDadosSolicitacao($data[$i]['SSOL_ID_DOCUMENTO']);
                }
                $TomboCentral =  $TomboTiCentral->getDescTombo($DocmDocumento['SSOL_NR_TOMBO']);
                $this->view->TobomTiCentral = $TomboCentral;
                $this->view->DocmDocumento = $DocmDocumento;
                $numDocumento = $DocmDocumento["DOCM_NR_DOCUMENTO"];

                $DocmDocumentoHistorico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($data[$i]['SSOL_ID_DOCUMENTO']);
                $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico;

                $DocmDocumentoVinculacao = $SosTbSsolSolicitacao->getPrincipalVinculacao($data[$i]['SSOL_ID_DOCUMENTO']);
                $this->view->DocmDocumentoVinculacao = $DocmDocumentoVinculacao;

                $DocmListaVinculados = $SosTbSsolSolicitacao->getListaSolicitacoesVinculadas($data[$i]['SSOL_ID_DOCUMENTO']);
                $this->view->DocmListaVinculados = $DocmListaVinculados;

                $DocmNaoConformidades = $SosTbSsolSolicitacao->getNaoConformidades($data[$i]['SSOL_ID_DOCUMENTO']);
                $this->view->DocmNaoConformidades = $DocmNaoConformidades;

                $DocmContoleQaulidade = $SosTbSsolSolicitacao->getDefeitosSolicitacao($data[$i]['MOFA_ID_MOVIMENTACAO']);
                $this->view->DocmDefeitos = $DocmContoleQaulidade;

                $DocmPapdParteProcDocPorOrdemDe = $SadTbPapdParteProcDoc->getPorOrdemDeSosti($data[$i]['SSOL_ID_DOCUMENTO']);
                $this->view->DocmPapdParteProcDocPorOrdemDe = $DocmPapdParteProcDocPorOrdemDe;

//                $DocmManutencaoEquip = $sosMaeqManutencao->getDadosManutencaoSolicitacao($data[$i]['SSOL_ID_DOCUMENTO']);
//                $this->view->DocmManutencaoEquip = $DocmManutencaoEquip;
                $this->render($this->_getParam('param'));
                ++$i;
            }
            /**
             * Adaptaçao para imprimir o relatorio detalhexlsii
             */
            ($this->_getParam('param') == 'detalhexlsii')?($this->render('rodape')):('');
            
            if ($this->_getParam('param') == 'detalhepdf') {
                $numDocumento = $DocmDocumento["DOCM_NR_DOCUMENTO"];
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
                $name = ($i > 1) ? ($i . '_solicitacoes.pdf') : ('solicitacao_' . $numDocumento . '.pdf');
                $mpdf->Output($name, 'D');
            }
            if ($this->_getParam('param') == 'detalhexls') {
                $name = ($i > 1) ? ($i . '_solicitacoes.xls') : ('solicitacao_' . $numDocumento . '.xls');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $name . '"');
                header('Cache-Control: max-age=0');
            }
            if ($this->_getParam('param') == 'detalhexlsii') {
                $name = ($i > 1) ? ($i . '_solicitacoes.xls') : ('solicitacao_' . $numDocumento . '.xls');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $name . '"');
                header('Cache-Control: max-age=0');
            }
        }
    }
    
    public function egdAction()
    {
        $this->_helper->layout->disableLayout();
        $solicitPost = $this->getRequest()->getPost();
        $SosTbSsolSolicitacao  = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $calculaTempo = new App_View_Helper_Calculahorasla();
        $arraySolic = array();
        foreach ($solicitPost["solicitacao"] as $v) {
            $dataDecode = Zend_Json::decode($v);
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]] = Zend_Json::decode($v);
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]]['DOC_VINC'] = $SosTbSsolSolicitacao->getPrincipalVinculacao($dataDecode["SSOL_ID_DOCUMENTO"]);
            $ultimaFase = $SosTbSsolSolicitacao->getHistoricoSolicitacao($dataDecode["SSOL_ID_DOCUMENTO"]);
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]]['DESCRICAO_ULTIMA_FASE'] = $ultimaFase[0]["FADM_DS_FASE"];
            $tempoArray = $calculaTempo->calculahorasla($dataDecode);
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]]['PRAZO_RESTANTE_D'] = $tempoArray['prazo_restante'][0];
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]]['PRAZO_RESTANTE_H'] = $tempoArray['prazo_restante'][1];
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]]['PRAZO_RESTANTE_M'] = $tempoArray['prazo_restante'][2];
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]]['PRAZO_RESTANTE_PERCENTUAL'] = $tempoArray['percentual']['pct']."%";
            $arrayDataUltFase = explode(' ', $dataDecode['MOFA_DH_FASE']);
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]]['ULT_F_DATA'] = $arrayDataUltFase[0];
            $arraySolic[$dataDecode["SSOL_ID_DOCUMENTO"]]['ULT_F_HORA'] = $arrayDataUltFase[1];
        }
        $name = 'relatorio_desenvolvimento_'.count($solicitPost["solicitacao"]).'.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '"');
        header('Cache-Control: max-age=0');
        $this->view->arraySolic = $arraySolic;
    }
    
    public function formassociadosAction()
    {
        /**
         * Carrega o array de associações das solicitações
         */
        $idDocumento = $this->_getParam('idDocumento');
        if (!empty($idDocumento)) {
            $associacao = new Application_Model_DbTable_SosTbAsscAssociacao();
            $this->view->associacaoSosti = $associacao->getAssociacaoSosti($idDocumento);
            $this->view->idDocumento = $idDocumento;
        }
    }
    
    public function vinculadosAction()
    {
        $idDocumento = $this->_getParam('id');
        $sosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $arrayDataVinculados = $sosTbSsolSolicitacao->getListaSolicitacoesVinculadas($idDocumento);
        foreach ($arrayDataVinculados as $v) {
            $arrayVinculados[] = $sosTbSsolSolicitacao->getDadosSolicitacao($v["VIDC_ID_DOC_VINCULADO"]);
        }
        $this->view->arrayVinculados = $arrayVinculados;
        $this->view->principal = $idDocumento;
    }

}
