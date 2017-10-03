<?php
 
class Sosti_SlasecoesController extends Zend_Controller_Action {
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
    }

    public function triagemAction() {
        set_time_limit(1200); //10 minutos
        
        /*
        Restricoes aplicadas conforme pedido nos sostis  2013010001155011550160000357 e 2013010001155011550160000349
        $userNs = new Zend_Session_Namespace('userNs');
        $horaInicio = mktime(10,00,00);
        $horaFinal =  mktime(19,00,00);
        $horaAtual = mktime(date("H"), date("i"), date("s"));
        $msgUsuario = "Atenção, devido ao crescente uso do sistema, o que está causando uma sobrecarga no banco
                       de dados, a funcionalidade de emissão de relatórios de SLA somente estará disponível antes das 10:00 e após às 19:00.";
      
        if ( ($horaAtual <= $horaInicio || $horaAtual >= $horaFinal) || strcmp($userNs->matricula, 'TR300785') == 0 || strcmp($userNs->matricula, 'TR179603') == 0 ){
        */
        
        // Validação de acesso às funcionalidades do SLA, conforme servidor web
        $negocio = new Trf1_Sosti_Negocio_Sla ();
        $permiteSla = $negocio->permiteSla ();
        
        if ($permiteSla ['permissao']) {
            $form = new Sosti_Form_Sla();
            $form->removeElement('OPCAO');
            $form->setAction('triagem');
            $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
            $sgrs_id_grupo->clearMultiOptions();
            $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasSecoes();
            $sgrs_id_grupo->addMultiOptions(array('' => ''));
            foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
                $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => substr($SgrsGrupoServico_p["CXEN_DS_CAIXA_ENTRADA"], 9)));
            endforeach;

            $this->view->form = $form;
            $this->view->title = "SLA - SEÇÕES";
            $Sla_Helpdesk_ns = new Zend_Session_Namespace('Sla_Secao_ns');
            if ($Sla_Helpdesk_ns->data != '') {
                $form->populate($Sla_Helpdesk_ns->data);
            }
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                if ($form->isValid($data)) {
                    $Sla_Helpdesk_ns = new Zend_Session_Namespace('Sla_Secao_ns');
                    $Sla_Helpdesk_ns->data = $data;
                    /**
                     * Veirifica o Timestemp das data de buscar Inicial e Final
                     */
                    $inicial = explode(' ', $data['DATA_INICIAL']);
                    $dataInicial = explode('/', $inicial[0]);
                    $horaInicial = explode(':', $inicial[1]);
                    $timestempInicial = mktime($horaInicial[0], $horaInicial[1], $horaInicial[2], $dataInicial[1], $dataInicial[0], $dataInicial[2]);

                    $final = explode(' ', $data['DATA_FINAL']);
                    $dataFinal = explode('/', $final[0]);
                    $horaFinal = explode(':', $final[1]);
                    $timestempFinal = mktime($horaFinal[0], $horaFinal[1], $horaFinal[2], $dataFinal[1], $dataFinal[0], $dataFinal[2]);
                    /**
                     * data limite para a contagem do PEDIDO DE INFORMAÇÃO;
                     * Data: 31/11/2012 23:59:59
                     */
                    $timestempInicioContagem = mktime(23, 59, 59, 10, 31, 2012);
                    if ($timestempInicial < $timestempInicioContagem && $timestempFinal <= $timestempInicioContagem) {
                        $this->_helper->_redirector('indicadoressempedidoinformacao', 'slasecoes', 'sosti');
                    } elseif ($timestempInicial > $timestempInicioContagem && $timestempFinal > $timestempInicioContagem) {
                        $this->_helper->_redirector('indicadorescompedidoinformacao', 'slasecoes', 'sosti');
                    } else if($timestempInicial < $timestempInicioContagem && $timestempFinal > $timestempInicioContagem) {
                        $msg_to_user = "Não é possivel retirar relatório do prazo entre Outubro e Novembro, pois o sistema de calculo foi modificado";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                        $this->_helper->_redirector('triagem', 'slasecoes', 'sosti');
                    }
                }
            }
        }else{
        	/*
            $this->_helper->flashMessenger(array('message' => $msgUsuario, 'status' => 'notice'));
            $this->_helper->_redirector('index', 'index', 'admin');
            */
			$this->_helper->flashMessenger ( array ('message' => $permiteSla ['mensagem'], 'status' => 'notice' ) );
			$this->_helper->_redirector ( 'index', 'index', 'admin' );
        }
    }

    public function indicadoressempedidoinformacaoAction() {
        set_time_limit(1200); //10 minutos
        $userNs = new Zend_Session_Namespace('userNs');
        $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();

        $tempoSla = new App_Sosti_TempoSla();
        $form = new Sosti_Form_Sla();
        $form->removeElement('OPCAO');
        $form->setAction('triagem');
        $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
        $sgrs_id_grupo->clearMultiOptions();

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasSecoes();
        $sgrs_id_grupo->addMultiOptions(array('' => ''));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => substr($SgrsGrupoServico_p["CXEN_DS_CAIXA_ENTRADA"], 9)));
        endforeach;

        $this->view->form = $form;
        $this->view->title = "SLA - SEÇÕES";

        $Sla_Secao_ns = new Zend_Session_Namespace('Sla_Secao_ns');
        if ($Sla_Secao_ns->data != '') {
            $form->populate($Sla_Secao_ns->data);
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $Sla_Secao_ns = new Zend_Session_Namespace('Sla_Secao_ns');
                $Sla_Secao_ns->data = $data;
            }
        }
        if ($Sla_Secao_ns->data != '') {
            $this->view->data = $Sla_Secao_ns->data;

            $form->populate($Sla_Secao_ns->data);

            $caixas_arr = Zend_Json::decode($Sla_Secao_ns->data['SGRS_ID_GRUPO']);

            $secao = $caixas_arr["CXEN_DS_CAIXA_ENTRADA"];
            $siglaSecao = explode(' - ', $secao);
            $tempoTimeInterval = new App_TimeInterval();
            $fusoHorario = $tempoTimeInterval->calculaFusoHorario($siglaSecao[2]);
            $this->view->fusoHorario = $fusoHorario;
            $Sla_Secao_ns->secao = $caixas_arr["CXEN_DS_CAIXA_ENTRADA"];
            /**
             * Permissao fechamento
             */
            $possui_permissao_fechamento = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNomedoPerfilMatriculaUnidade('GESTOR DO CONTRATO DO ATENDIMENTO USUÁRIO DA SEÇ', $userNs->matricula, $caixas_arr['SGRS_SG_SECAO_LOTACAO'], $caixas_arr['SGRS_CD_LOTACAO']);
            $this->view->possui_permissao_fechamento = $possui_permissao_fechamento;

            /**
             * INDICADOR IIA 1
             */
            $IIA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'IIA');
            $ISS_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'ISS');
            $IAP_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'IAP');
            $ISD_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'ISD');
            $ITP_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'ITP');
            $INC_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'INC');
            $ICR_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'ICR');
            /**
             * Passa as datas para calcular as metas alcançadas
             */
            $solicitacoesIia = $indicadorNivelServ->getAtendUsuDatasSLA_IIA($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 2, $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL'], $fusoHorario, $IIA_DADOS['SINS_ID_INDICADOR']);
//            Zend_Debug::dump($solicitacoesIia);
//            Zend_Debug::dump(count($solicitacoesIia),'$solicitacoesIia count');

            /**
             * INDICADOR ISS 2
             */
            $solicitacoesIss = $indicadorNivelServ->getAtendUsuDatasSLA_ISS($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL'], $fusoHorario, $ISS_DADOS['SINS_ID_INDICADOR'], $IAP_DADOS['SINS_ID_INDICADOR'], $ISD_DADOS['SINS_ID_INDICADOR']);
//            Zend_Debug::dump($solicitacoesIss);
//            Zend_Debug::dump(count($solicitacoesIss),'$solicitacoesIss count');

            /**
             * INDICADOR ISD 3
             */
//            $solicitacoesIsd = $solicitacoesIss; 
//            Zend_Debug::dump($solicitacoesIsd);
//            Zend_Debug::dump(count($solicitacoesIsd),'$solicitacoesIsd count');


            /**
             * INDICADOR ITP 4
             */
//            $solicitacoesItp = $indicadorNivelServ->getAtendUsuDatasSLA_ITP(1, $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL']);

            /**
             * INDICADOR IAP 5
             */
//            $solicitacoesIap = $solicitacoesIss;


            /**
             * INDICADOR INC 6
             */
            $solicitacoesInc = $indicadorNivelServ->getAtendUsuDatasSLA_INC($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL'], $fusoHorario, $INC_DADOS['SINS_ID_INDICADOR']);
//            Zend_Debug::dump(count($solicitacoesInc),'$solicitacoesIss count');

            /**
             * INDICADOR ICR 7 
             */
            $solicitacoesIcr = $indicadorNivelServ->getDatasSLA_ICR($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL'], $fusoHorario, $ICR_DADOS['SINS_ID_INDICADOR']);
//            Zend_Debug::dump(count($solicitacoesInc),'$solicitacoesIss count');


            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
            /**
             * Calcular o IIA – Índice de Início de Atendimento no Prazo
             */
            $i = 0;
            $solicitacoesIiaUltrapassado = array();
            foreach ($solicitacoesIia as $iia) {
                if (is_null($iia['DESCONSIDERADO_IAA'])) {
                    $tempoTotalSLA = $tempoSla->tempoTotalSLA($iia["DATA_CHAMADO"], $iia["DATA_PRIMEIRO_ATENDIMENTO"], '07:00:00', '20:00:00');
                    $prazoUltrapassado[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLA, 600);
                    if ($prazoUltrapassado[$i] == true) {
                        $solicitacoesIiaUltrapassado[$i] = $solicitacoesIia[$i];
                    }
                }
                $i++;
            }

            $totalIiaSolicitacoes = (count($solicitacoesIia));
            $foradoPrazoIiaSolicitacoes = count($solicitacoesIiaUltrapassado);
            $noPrazoIiaSolicitacoes = $totalIiaSolicitacoes - $foradoPrazoIiaSolicitacoes;

            if ($totalIiaSolicitacoes > 0) {
                $totalIia = ($noPrazoIiaSolicitacoes / $totalIiaSolicitacoes) * 100;
            } else {
                $totalIia = 100;
            }
            $totalIia = (float) sprintf('%3.1f', $totalIia);

            if ($totalIia >= 95) {
                $glosaIia = 0;
            }
            if (($totalIia <= 94.9) && ($totalIia >= 85.5)) {
                $glosaIia = 2;
            }
            if (($totalIia <= 85.4) && ($totalIia >= 76)) {
                $glosaIia = 4;
            }
            if (($totalIia <= 75.9) && ($totalIia >= 66.5)) {
                $glosaIia = 7;
            }
            if ($totalIia < 66.5) {
                $glosaIia = 7;
            }

            $this->view->totalIiaSolicitacoes = $totalIiaSolicitacoes;
            $this->view->noPrazoIiaSolicitacoes = $noPrazoIiaSolicitacoes;
            $this->view->foradoPrazoIiaSolicitacoes = $foradoPrazoIiaSolicitacoes;
            $this->view->idIndicadorIIA = $IIA_DADOS['SINS_ID_INDICADOR'];

            //solicitações
            $this->view->solicitacoesIia = $solicitacoesIiaUltrapassado;
            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */



            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
            /**
             * Calcular o ISS – Índice de Soluções das Solicitações no Prazo
             */
            $i = 0;
            $prazoUltrapassadoIss = array();
            $prazoUltrapassadoIsd = array();
            $solicitacoesIssUltrapassado = array();
            $solicitacoesIapUltrapassado = array();
            $fimIss = count($solicitacoesIss);

            for ($i = 0; $i < $fimIss; $i++) {

                if (is_null($solicitacoesIss[$i]['SSPA_DT_PRAZO'])) {

                    /*                     * TEMPO ÚTIL EM SEGUNDOS */
                    $tempoTotalSLAsegundos = $tempoSla->tempoTotalSLA($solicitacoesIss[$i]["DATA_CHAMADO"], $solicitacoesIss[$i]["DATA_FIM_CHAMADO"], '07:00:00', '20:00:00');


                    /*                     * ISS */
                    $prazoUltrapassadoIss[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLAsegundos, 57600);
                    if ($prazoUltrapassadoIss[$i] == true) {
                        if (is_null($solicitacoesIss[$i]['DESCONSIDERADO_ISS'])) {
                            $solicitacoesIssUltrapassado[$i] = $solicitacoesIss[$i];
                        }
                    }


                    /* ISD */
                    $prazoUltrapassadoIsd[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLAsegundos, 46800);
                    if ($prazoUltrapassadoIsd[$i] == true) {
                        if (is_null($solicitacoesIss[$i]['DESCONSIDERADO_ISD'])) {
                            $solicitacoesIsdUltrapassado[$i] = $solicitacoesIss[$i];
                        }
                    }
                } else {
                    if ($solicitacoesIss[$i]['PRAZO_ULTRAPASSADO'] == 1) {

                        if (is_null($solicitacoesIss[$i]['DESCONSIDERADO_ISS'])) {
                            $solicitacoesIssUltrapassado[$i] = $solicitacoesIss[$i];
                        }

                        if (is_null($solicitacoesIss[$i]['DESCONSIDERADO_ISD'])) {
                            $solicitacoesIsdUltrapassado[$i] = $solicitacoesIss[$i];
                        }
                    }
                }
            }

            $totalIssSolicitacoes = (count($solicitacoesIss));
            $foradoPrazoIssSolicitacoes = count($solicitacoesIssUltrapassado);
            $noPrazoIssSolicitacoes = $totalIssSolicitacoes - $foradoPrazoIssSolicitacoes;
//            Zend_Debug::dump($totalIssSolicitacoes,'$totalIssSolicitacoes');
//            Zend_Debug::dump($noPrazoIssSolicitacoes,'$noPrazoIssSolicitacoes');
//            Zend_Debug::dump(count($solicitacoesIssUltrapassado),'$solicitacoesIssUltrapassado');

            if ($totalIssSolicitacoes > 0) {
                $totalIss = ($noPrazoIssSolicitacoes / $totalIssSolicitacoes) * 100;
            } else {
                $totalIss = 100;
            }
            $totalIss = (float) sprintf('%3.1f', $totalIss);


            if ($totalIss >= 91) {
                $glosaIss = 0;
            }
            if (($totalIss <= 90.9) && ($totalIss >= 81.9)) {
                $glosaIss = 2;
            }
            if (($totalIss <= 81.8) && ($totalIss >= 72.8)) {
                $glosaIss = 4;
            }
            if (($totalIss <= 72.7) && ($totalIss >= 63.7)) {
                $glosaIss = 7;
            }
            if ($totalIss < 63.7) {
                $glosaIss = 7;
            }

            $this->view->totalIssSolicitacoes = $totalIssSolicitacoes;
            $this->view->foradoPrazoIssSolicitacoes = $foradoPrazoIssSolicitacoes;
            $this->view->noPrazoIssSolicitacoes = $noPrazoIssSolicitacoes;
            $this->view->idIndicadorISS = $ISS_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIssUltrapassado = $solicitacoesIssUltrapassado;
            /*             * **************************************************************************************************************** */



            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
//            /**
//             * Calcular o ISD – Índice de Chamados Solucionados no mesmo dia
//             */
//            Zend_Debug::dump($solicitacoesIsd);
//            $i=0;
//            $prazoUltrapassadoIsd = array();
//            foreach ($solicitacoesIsd as $isd) {
//                if(is_null($isd['DESCONSIDERADO_ISD'])){
//                    if(is_null ($isd['SSPA_DT_PRAZO'])){
//
//                        $prazoUltrapassadoIsd[$i] = $tempoSla->verificaPrazoUltrapassado($tempoSla->tempoTotalSLA($isd["DATA_CHAMADO"], $isd["DATA_FIM_CHAMADO"], '07:00:00', '20:00:00'), 46800);
//                        if ($prazoUltrapassadoIsd[$i] == true) {
//                            $solicitacoesIsdUltrapassado[$i] = $solicitacoesIsd[$i];
//                        }
//                    }else{
//                        if($isd['PRAZO_ULTRAPASSADO'] == 1){
//                            $solicitacoesIsdUltrapassado[$i] = $solicitacoesIsd[$i];
//                        }
//                    }
//                }
//                $i++;
//            }

            $totalIsdSolicitacoes = (count($solicitacoesIss));
            $foradoPrazoIsdSolicitacoes = count($solicitacoesIsdUltrapassado);
            $noPrazoIsdSolicitacoes = $totalIsdSolicitacoes - $foradoPrazoIsdSolicitacoes;
//            Zend_Debug::dump($totalIsdSolicitacoes,'$totalIsdSolicitacoes');
//            Zend_Debug::dump($noPrazoIsdSolicitacoes,'$noPrazoIsdSolicitacoes');
//            Zend_Debug::dump(count($solicitacoesIsdUltrapassado),'$solicitacoesIsdUltrapassado');

            if ($totalIsdSolicitacoes > 0) {
                $totalIsd = ($noPrazoIsdSolicitacoes / $totalIsdSolicitacoes) * 100;
            } else {
                $totalIsd = 100;
            }
            $totalIsd = (float) sprintf('%3.1f', $totalIsd);

            if ($totalIsd >= 95) {
                $glosaIsd = 0;
            }
            if (($totalIsd <= 94.9) && ($totalIsd >= 85.5)) {
                $glosaIsd = 2;
            }
            if (($totalIsd <= 85.4) && ($totalIsd >= 76.0)) {
                $glosaIsd = 4;
            }
            if (($totalIsd <= 75.9) && ($totalIsd >= 66.5)) {
                $glosaIsd = 7;
            }
            if ($totalIsd < 66.5) {
                $glosaIsd = 7;
            }

            $this->view->totalIsdSolicitacoes = $totalIsdSolicitacoes;
            $this->view->foradoPrazoIsdSolicitacoes = $foradoPrazoIsdSolicitacoes;
            $this->view->noPrazoIsdSolicitacoes = $noPrazoIsdSolicitacoes;
            $this->view->idIndicadorISD = $ISD_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIsdUltrapassado = $solicitacoesIsdUltrapassado;

            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
//            /**
//             * Calcular o ITP – Índice de Ligações Telefônicas Perdidas
//             */
//            $dadosIrc = new Application_Model_DbTable_SosTbSsolSolicitacao();
//            $rowsIrc = $dadosIrc->getSolicitacoesPeriodoSla(1, '', $Sla_Helpdesk_ns->data['DATA_INICIAL'], $Sla_Helpdesk_ns->data['DATA_FINAL'], '', 1000);
//            $totalIrc = (count($solicitacoesIcr)*100)/(count($rowsIrc));
//            if ($totalIrc <= 10) {
//                $glosaIrc = 0;
//            }
//            if (($totalIrc >= 10.1) && ($totalIrc <= 11)) {
//                $glosaIrc = 2;
//            }
//            if (($totalIrc >= 11.01) && ($totalIrc <= 12)) {
//                $glosaIrc = 4;
//            }
//            if (($totalIrc >= 12.01) && ($totalIrc <= 13)) {
//                $glosaIrc = 7;
//            }
//            if ($totalIrc > 13) {
//                $glosaIrc = 7;
//            }
//            
            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
//            /**
//             * Calcular o IAP – Índice de Ausência de prazo 
//             * 
//             */


            $solicitacoesIap = $solicitacoesIssUltrapassado;
            $i = 0;
            $prazoUltrapassadoIap = array();
            foreach ($solicitacoesIap as $iap) {
                if (is_null($iap['DESCONSIDERADO_IAP'])) {
                    if (is_null($iap['SSPA_DT_PRAZO'])) {
                        $solicitacoesIapUltrapassado[$i] = $iap;
                    } else {
                        if ($iap['PRAZO_ULTRAPASSADO'] == 1) {
                            $solicitacoesIapUltrapassado[$i] = $iap;
                        }
                    }
                }
                $i++;
            }


            $totalIapSolicitacoes = $totalIssSolicitacoes;
            $foradoPrazoIapSolicitacoes = count($solicitacoesIapUltrapassado);
            $noPrazoIapSolicitacoes = $totalIapSolicitacoes - $foradoPrazoIapSolicitacoes;
//            Zend_Debug::dump($totalIapSolicitacoes,'$totalIapSolicitacoes');
//            Zend_Debug::dump($noPrazoIapSolicitacoes,'$noPrazoIapSolicitacoes');
//            Zend_Debug::dump(count($solicitacoesIapUltrapassado),'$solicitacoesIapUltrapassado');


            if ($totalIapSolicitacoes > 0) {
                $totalIap = ($foradoPrazoIapSolicitacoes / $totalIapSolicitacoes) * 100;
            } else {
                $totalIap = 0;
            }
            $totalIap = (float) sprintf('%3.2f', $totalIap);

            if ($totalIap <= 10) {
                $glosaIap = 0;
            }
            if (($totalIap >= 10.01) && ($totalIap <= 11)) {
                $glosaIap = 2;
            }
            if (($totalIap >= 11.01) && ($totalIap <= 12)) {
                $glosaIap = 4;
            }
            if (($totalIap >= 12.01) && ($totalIap <= 13)) {
                $glosaIap = 7;
            }
            if ($totalIap > 13) {
                $glosaIap = 7;
            }
            $this->view->totalIapSolicitacoes = $totalIapSolicitacoes;
            $this->view->foradoPrazoIapSolicitacoes = $foradoPrazoIapSolicitacoes;
            $this->view->noPrazoIapSolicitacoes = $noPrazoIapSolicitacoes;
            $this->view->idIndicadorIAP = $IAP_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIapUltrapassado = $solicitacoesIapUltrapassado;
            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */

            /**
             * Calcular o INC – Índice de chamados com Não Conformidade
             * 
             */
            $i = 0;
            $prazoUltrapassadoInc = array();
            foreach ($solicitacoesInc as $inc) {
                if (is_null($inc['DESCONSIDERADO_INC'])) {
                    if ((int) $inc['NAO_CONFORME'] > 0) {
                        $solicitacoesIncUltrapassado[$i] = $inc;
                    }
                }
                $i++;
            }
            $totalIncSolicitacoes = count($solicitacoesInc);
            $foradoPrazoIncSolicitacoes = count($solicitacoesIncUltrapassado);
            $noPrazoIncSolicitacoes = $totalIncSolicitacoes - $foradoPrazoIncSolicitacoes;

            $totalInc = ($foradoPrazoIncSolicitacoes / $totalIncSolicitacoes) * 100;
            if ($totalInc <= 5) {
                $glosaInc = 0;
            }
            $totalInc = (float) sprintf('%3.2f', $totalInc);


            if (($totalInc >= 5.01) && ($totalInc <= 5.50)) {
                $glosaInc = 1;
            }
            if (($totalInc >= 5.51) && ($totalInc <= 6)) {
                $glosaInc = 3;
            }
            if (($totalInc >= 6.01) && ($totalInc <= 6.5)) {
                $glosaInc = 5;
            }
            if ($totalInc > 6.5) {
                $glosaInc = 5;
            }

            $this->view->totalIncSolicitacoes = $totalIncSolicitacoes;
            $this->view->foradoPrazoIncSolicitacoes = $foradoPrazoIncSolicitacoes;
            $this->view->noPrazoIncSolicitacoes = $noPrazoIncSolicitacoes;
            $this->view->idIndicadorINC = $INC_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIncUltrapassado = $solicitacoesIncUltrapassado;



            /*             * *********************************************************************************** */

            /*             * *********************************************************************************** */
            /**
             * Calcular o ICR – Índice de chamados reabertos
             * 
             */
            $i = 0;
            foreach ($solicitacoesIcr as $icr) {
                if (is_null($icr['DESCONSIDERADO_ICR'])) {
                    if (!is_null($icr['DATA_RECUSA'])) {
                        $tempoTotalSLAsegundos = $tempoSla->tempoTotalSLA($icr["DATA_BAIXA"], $icr["DATA_RECUSA"], '07:00:00', '20:00:00');
                        $prazoUltrapassadoIcr[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLAsegundos, 86400);
                        if ($prazoUltrapassadoIcr[$i] == false) {
                            $IcrRecusadasSolicitacoes[$i] = $icr;
                        }
                    }
                }
                $i++;
            }
            $totalIcrSolicitacoes = count($solicitacoesIcr);
            $RecusadasIcrSolicitacoes = count($IcrRecusadasSolicitacoes);
            $AvaliadasPositivamenteIcrSolicitacoes = $totalIcrSolicitacoes - $RecusadasIcrSolicitacoes;
//            Zend_Debug::dump($totalIcrSolicitacoes,'$totalIcrSolicitacoes');
//            Zend_Debug::dump($RecusadasIcrSolicitacoes,'$RecusadasIcrSolicitacoes');


            if ($totalIcrSolicitacoes > 0) {
                $totalIcr = ($RecusadasIcrSolicitacoes / $totalIcrSolicitacoes) * 100;
            } else {
                $totalIcr = 0;
            }
            $totalIcr = (float) sprintf('%3.2f', $totalIcr);

            if ($totalIcr <= 5) {
                $glosaIcr = 0;
            }
            if (($totalIcr >= 5.01) && ($totalIcr <= 5.50)) {
                $glosaIcr = 1;
            }
            if (($totalIcr >= 5.51) && ($totalIcr <= 6)) {
                $glosaIcr = 3;
            }
            if (($totalIcr >= 6.01) && ($totalIcr <= 6.5)) {
                $glosaIcr = 5;
            }
            if ($totalIcr > 6.5) {
                $glosaIcr = 5;
            }

            $this->view->totalIcrSolicitacoes = $totalIcrSolicitacoes;
            $this->view->RecusadasIcrSolicitacoes = $RecusadasIcrSolicitacoes;
            $this->view->AvaliadasPositivamenteIcrSolicitacoes = $AvaliadasPositivamenteIcrSolicitacoes;
            $this->view->idIndicadorICR = $ICR_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->IcrRecusadasSolicitacoes = $IcrRecusadasSolicitacoes;

            /**
             * Array contendo a meta alcançada para todos os índices
             */
            $meta[0] = $totalIia . '%'; // IIA – Índice de Início de Atendimento no Prazo
            $meta[1] = $totalIss . '%'; // ISS – Índice de Soluções das Solicitações no Prazo
            $meta[2] = $totalIsd . '%'; // ISD – Índice de Chamados Solucionados no mesmo dia
            $meta[3] = '--'; // ITP – Índice de Ligações Telefônicas Perdidas
            $meta[4] = $totalIap . '%'; // IAP – Índice de Ausência de Prazo
            $meta[5] = $totalInc . '%';  // INC – Índice de chamados com Não Conformidade
            $meta[6] = $totalIcr . '%'; // ICR – Índice de chamados reabertos
//            $meta[7] = $totalDpig.'%';//Deixar de Prestar as Informações Gerenciais
            /**
             * Array contendo o valor a ser glosado para todos os índices
             */
            $glosa[0] = $glosaIia . '%'; // IIA – Índice de Início de Atendimento no Prazo
            $glosa[1] = $glosaIss . '%'; // ISS – Índice de Soluções das Solicitações no Prazo
            $glosa[2] = $glosaIsd . '%'; // ISD – Índice de Chamados Solucionados no mesmo dia
            $glosa[3] = '--'; // ITP – Índice de Ligações Telefônicas Perdidas
            $glosa[4] = $glosaIap . '%';  // IAP – Índice de Ausência de Prazo
            $glosa[5] = $glosaInc . '%';  // INC – Índice de chamados com Não Conformidade
            $glosa[6] = $glosaIcr . '%'; // ICR – Índice de chamados reabertos
//            $glosa[7] = $glosaDpig.'%'; //Deixar de Prestar as Informações Gerenciais

            /**
             * Inclui a posição da meta alcançada no array dos indicadores mínimos
             */
            $indicadoresMinimos = $indicadorNivelServ->getIndicNivelServicoGrupo(1, '');
            $fim = count($indicadoresMinimos);
            for ($i = 0; $i < $fim; $i++) {
                $indicadoresMinimos[$i]['META_ALCANCADA'] = $meta[$i];
                $indicadoresMinimos[$i]['GLOSA'] = $glosa[$i];
            }
            $this->view->indicadoresMinimos = $indicadoresMinimos;
            $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
            $indMin->data = $indicadoresMinimos;
            $indMin->title = "SLA - SEÇÕES - TRF1";
            $indMin->periodo = 'PERÍODO: ' . $Sla_Secao_ns->data['DATA_INICIAL'] . ' À ' . $Sla_Secao_ns->data['DATA_FINAL'];
            $indMin->fuso = 'PERÍODO DE TEMPO DE ACORDO COM O FUSO HORÁRIO DA SEÇÃO: ' . '(' . $fusoHorario . ') HORA(S)';
            $indMin->secao = $Sla_Secao_ns->secao;
            $this->view->secao = $indMin->secao;
            $this->render('indicadoresnivelservico');
        }
    }

    public function indicadorescompedidoinformacaoAction() {
        set_time_limit(1200); //10 minutos
        $userNs = new Zend_Session_Namespace('userNs');
        $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();

        $tempoSla = new App_Sosti_TempoSla();
        $form = new Sosti_Form_Sla();
        $form->removeElement('OPCAO');
        $form->setAction('triagem');
        $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
        $sgrs_id_grupo->clearMultiOptions();

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasSecoes();
        $sgrs_id_grupo->addMultiOptions(array('' => ''));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => substr($SgrsGrupoServico_p["CXEN_DS_CAIXA_ENTRADA"], 9)));
        endforeach;

        $this->view->form = $form;
        $this->view->title = "SLA - SEÇÕES";

        $Sla_Secao_ns = new Zend_Session_Namespace('Sla_Secao_ns');
        if ($Sla_Secao_ns->data != '') {
            $form->populate($Sla_Secao_ns->data);
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $Sla_Secao_ns = new Zend_Session_Namespace('Sla_Secao_ns');
                $Sla_Secao_ns->data = $data;
            }
        }
        if ($Sla_Secao_ns->data != '') {
            $this->view->data = $Sla_Secao_ns->data;

            $form->populate($Sla_Secao_ns->data);

            $caixas_arr = Zend_Json::decode($Sla_Secao_ns->data['SGRS_ID_GRUPO']);

            $secao = $caixas_arr["CXEN_DS_CAIXA_ENTRADA"];
            $siglaSecao = explode(' - ', $secao);
            $tempoTimeInterval = new App_TimeInterval();
            $fusoHorario = $tempoTimeInterval->calculaFusoHorario($siglaSecao[2]);
            $this->view->fusoHorario = $fusoHorario;
            $Sla_Secao_ns->secao = $caixas_arr["CXEN_DS_CAIXA_ENTRADA"];
            /**
             * Permissao fechamento
             */
            $possui_permissao_fechamento = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNomedoPerfilMatriculaUnidade('GESTOR DO CONTRATO DO ATENDIMENTO USUÁRIO DA SEÇ', $userNs->matricula, $caixas_arr['SGRS_SG_SECAO_LOTACAO'], $caixas_arr['SGRS_CD_LOTACAO']);
            $this->view->possui_permissao_fechamento = $possui_permissao_fechamento;

            /**
             * INDICADOR IIA 1
             */
            $IIA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'IIA');
            $ISS_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'ISS');
            $IAP_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'IAP');
            $ISD_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'ISD');
            $ITP_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'ITP');
            $INC_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'INC');
            $ICR_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 'ICR');
            /**
             * Passa as datas para calcular as metas alcançadas
             */
            $solicitacoesIia = $indicadorNivelServ->getAtendUsuDatasSLA_IIA($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], 2, $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL'], $fusoHorario, $IIA_DADOS['SINS_ID_INDICADOR']);
//            Zend_Debug::dump($solicitacoesIia);
//            Zend_Debug::dump(count($solicitacoesIia),'$solicitacoesIia count');

            /**
             * INDICADOR ISS 2
             */
            $solicitacoesIss = $indicadorNivelServ->getAtendUsuDatasSLA_ISS($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL'], $fusoHorario, $ISS_DADOS['SINS_ID_INDICADOR'], $IAP_DADOS['SINS_ID_INDICADOR'], $ISD_DADOS['SINS_ID_INDICADOR']);
//            Zend_Debug::dump($solicitacoesIss);
//            Zend_Debug::dump(count($solicitacoesIss),'$solicitacoesIss count');

            /**
             * INDICADOR ISD 3
             */
//            $solicitacoesIsd = $solicitacoesIss; 
//            Zend_Debug::dump($solicitacoesIsd);
//            Zend_Debug::dump(count($solicitacoesIsd),'$solicitacoesIsd count');


            /**
             * INDICADOR ITP 4
             */
//            $solicitacoesItp = $indicadorNivelServ->getAtendUsuDatasSLA_ITP(1, $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL']);

            /**
             * INDICADOR IAP 5
             */
//            $solicitacoesIap = $solicitacoesIss;


            /**
             * INDICADOR INC 6
             */
            $solicitacoesInc = $indicadorNivelServ->getAtendUsuDatasSLA_INC($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL'], $fusoHorario, $INC_DADOS['SINS_ID_INDICADOR']);
//            Zend_Debug::dump(count($solicitacoesInc),'$solicitacoesIss count');

            /**
             * INDICADOR ICR 7 
             */
            $solicitacoesIcr = $indicadorNivelServ->getDatasSLA_ICR($caixas_arr['CXEN_ID_CAIXA_ENTRADA'], $Sla_Secao_ns->data['DATA_INICIAL'], $Sla_Secao_ns->data['DATA_FINAL'], $fusoHorario, $ICR_DADOS['SINS_ID_INDICADOR']);
//            Zend_Debug::dump(count($solicitacoesInc),'$solicitacoesIss count');

            /**
             * Configurações do horário de expediente
             */
            $SosTbGrexGrupoServExped = new Application_Model_DbTable_SosTbGrexGrupoServExped();
            $expedienteNormal = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente($caixas_arr['SGRS_ID_GRUPO'], "SLA");
            $expedienteEmergencia = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(2, "EMERGENCIAL");

            $expediente = array('NORMAL' => array('INICIO' => $expedienteNormal["INICIO"], 'FIM' => $expedienteNormal["FIM"]), 'EMERGENCIAL' => array('INICIO' => $expedienteEmergencia['INICIO'], 'FIM' => $expedienteEmergencia['FIM']));
            $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos($expediente["NORMAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["NORMAL"]["INICIO"]);
            $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos($expediente["EMERGENCIAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["EMERGENCIAL"]["INICIO"]);
            $expediente["NORMAL"]["DIA_UTIL_HORAS"] = $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;
            $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"] = $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;

            /**
             * Calcula o tempo total das solicitações não contabilizando o tempo em que a solicitação ficou aguardando a resposta do pedido de informação.
             */
            $TempoTotalPedidoInforArrISS = $tempoSla->TempoTotalPedidoInfor($solicitacoesIss, 'MOFA_ID_MOVIMENTACAO', "DATA_CHAMADO", "DATA_FIM_CHAMADO", "", "", $expediente);

            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
            /**
             * Calcular o IIA – Índice de Início de Atendimento no Prazo
             */
            $i = 0;
            $solicitacoesIiaUltrapassado = array();
            foreach ($solicitacoesIia as $iia) {
                if (is_null($iia['DESCONSIDERADO_IAA'])) {
                    $tempoTotalSLA = $tempoSla->tempoTotalSLA($iia["DATA_CHAMADO"], $iia["DATA_PRIMEIRO_ATENDIMENTO"], '07:00:00', '20:00:00');
                    $prazoUltrapassado[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLA, 600);
                    if ($prazoUltrapassado[$i] == true) {
                        $solicitacoesIiaUltrapassado[$i] = $solicitacoesIia[$i];
                    }
                }
                $i++;
            }

            $totalIiaSolicitacoes = (count($solicitacoesIia));
            $foradoPrazoIiaSolicitacoes = count($solicitacoesIiaUltrapassado);
            $noPrazoIiaSolicitacoes = $totalIiaSolicitacoes - $foradoPrazoIiaSolicitacoes;

            if ($totalIiaSolicitacoes > 0) {
                $totalIia = ($noPrazoIiaSolicitacoes / $totalIiaSolicitacoes) * 100;
            } else {
                $totalIia = 100;
            }
            $totalIia = (float) sprintf('%3.1f', $totalIia);

            if ($totalIia >= 95) {
                $glosaIia = 0;
            }
            if (($totalIia <= 94.9) && ($totalIia >= 85.5)) {
                $glosaIia = 2;
            }
            if (($totalIia <= 85.4) && ($totalIia >= 76)) {
                $glosaIia = 4;
            }
            if (($totalIia <= 75.9) && ($totalIia >= 66.5)) {
                $glosaIia = 7;
            }
            if ($totalIia < 66.5) {
                $glosaIia = 7;
            }
            /**
             * Carrega as variáveis para gerar o indicador:
             * Índice de Inicio de Atendimento no Prazo
             */
            $slaHelpIiaNs = new Zend_Session_Namespace('slaHelpIiaNs');
            $slaHelpIiaNs->totalIiaSolicitacoes = $totalIiaSolicitacoes;
            $slaHelpIiaNs->noPrazoIiaSolicitacoes = $noPrazoIiaSolicitacoes;
            $slaHelpIiaNs->foradoPrazoIiaSolicitacoes = $foradoPrazoIiaSolicitacoes;
            $slaHelpIiaNs->idIndicadorIIA = $IIA_DADOS['SINS_ID_INDICADOR'];
            $slaHelpIiaNs->solicitacoesIia = $solicitacoesIiaUltrapassado;

            $this->view->totalIiaSolicitacoes = $totalIiaSolicitacoes;
            $this->view->noPrazoIiaSolicitacoes = $noPrazoIiaSolicitacoes;
            $this->view->foradoPrazoIiaSolicitacoes = $foradoPrazoIiaSolicitacoes;
            $this->view->idIndicadorIIA = $IIA_DADOS['SINS_ID_INDICADOR'];

            //solicitações
            $this->view->solicitacoesIia = $solicitacoesIiaUltrapassado;
            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */



            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
            /**
             * Calcular o ISS – Índice de Soluções das Solicitações no Prazo
             */
            $i = 0;
            $prazoUltrapassadoIss = array();
            $prazoUltrapassadoIsd = array();
            $solicitacoesIssUltrapassado = array();
            $solicitacoesIapUltrapassado = array();
            $fimIss = count($solicitacoesIss);

            for ($i = 0; $i < $fimIss; $i++) {

                if (is_null($solicitacoesIss[$i]['SSPA_DT_PRAZO'])) {

                    /*                     * TEMPO ÚTIL EM SEGUNDOS */
//                    $tempoTotalSLAsegundos =  $tempoSla->tempoTotalSLA($solicitacoesIss[$i]["DATA_CHAMADO"], $solicitacoesIss[$i]["DATA_FIM_CHAMADO"], '07:00:00', '20:00:00');
                    $idMovimentacao = $solicitacoesIss[$i]["MOFA_ID_MOVIMENTACAO"];
                    $tempoTotalSLAsegundos = $TempoTotalPedidoInforArrISS[$idMovimentacao]["TEMPO_UTIL_TOTAL"];

                    /*                     * ISS */
                    $prazoUltrapassadoIss[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLAsegundos, 57600);
                    if ($prazoUltrapassadoIss[$i] == true) {
                        if (is_null($solicitacoesIss[$i]['DESCONSIDERADO_ISS'])) {
                            $solicitacoesIssUltrapassado[$i] = $solicitacoesIss[$i];
                        }
                    }


                    /* ISD */
                    $prazoUltrapassadoIsd[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLAsegundos, 46800);
                    if ($prazoUltrapassadoIsd[$i] == true) {
                        if (is_null($solicitacoesIss[$i]['DESCONSIDERADO_ISD'])) {
                            $solicitacoesIsdUltrapassado[$i] = $solicitacoesIss[$i];
                        }
                    }
                } else {
                    if ($solicitacoesIss[$i]['PRAZO_ULTRAPASSADO'] == 1) {

                        if (is_null($solicitacoesIss[$i]['DESCONSIDERADO_ISS'])) {
                            $solicitacoesIssUltrapassado[$i] = $solicitacoesIss[$i];
                        }

                        if (is_null($solicitacoesIss[$i]['DESCONSIDERADO_ISD'])) {
                            $solicitacoesIsdUltrapassado[$i] = $solicitacoesIss[$i];
                        }
                    }
                }
            }

            $totalIssSolicitacoes = (count($solicitacoesIss));
            $foradoPrazoIssSolicitacoes = count($solicitacoesIssUltrapassado);
            $noPrazoIssSolicitacoes = $totalIssSolicitacoes - $foradoPrazoIssSolicitacoes;
//            Zend_Debug::dump($totalIssSolicitacoes,'$totalIssSolicitacoes');
//            Zend_Debug::dump($noPrazoIssSolicitacoes,'$noPrazoIssSolicitacoes');
//            Zend_Debug::dump(count($solicitacoesIssUltrapassado),'$solicitacoesIssUltrapassado');

            if ($totalIssSolicitacoes > 0) {
                $totalIss = ($noPrazoIssSolicitacoes / $totalIssSolicitacoes) * 100;
            } else {
                $totalIss = 100;
            }
            $totalIss = (float) sprintf('%3.1f', $totalIss);


            if ($totalIss >= 91) {
                $glosaIss = 0;
            }
            if (($totalIss <= 90.9) && ($totalIss >= 81.9)) {
                $glosaIss = 2;
            }
            if (($totalIss <= 81.8) && ($totalIss >= 72.8)) {
                $glosaIss = 4;
            }
            if (($totalIss <= 72.7) && ($totalIss >= 63.7)) {
                $glosaIss = 7;
            }
            if ($totalIss < 63.7) {
                $glosaIss = 7;
            }
            /**
             * Carrega as variáveis para gerar o indicador:
             * ISS – Índice de Soluções das Solicitações no Prazo
             */
            $slaHelpIssNs = new Zend_Session_Namespace('slaHelpIssNs');
            $slaHelpIssNs->totalIssSolicitacoes = $totalIssSolicitacoes;
            $slaHelpIssNs->foradoPrazoIssSolicitacoes = $foradoPrazoIssSolicitacoes;
            $slaHelpIssNs->noPrazoIssSolicitacoes = $noPrazoIssSolicitacoes;
            $slaHelpIssNs->idIndicadorISS = $ISS_DADOS['SINS_ID_INDICADOR'];
            $slaHelpIssNs->solicitacoesIssUltrapassado = $solicitacoesIssUltrapassado;

            $this->view->totalIssSolicitacoes = $totalIssSolicitacoes;
            $this->view->foradoPrazoIssSolicitacoes = $foradoPrazoIssSolicitacoes;
            $this->view->noPrazoIssSolicitacoes = $noPrazoIssSolicitacoes;
            $this->view->idIndicadorISS = $ISS_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIssUltrapassado = $solicitacoesIssUltrapassado;
            /*             * **************************************************************************************************************** */



            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
//            /**
//             * Calcular o ISD – Índice de Chamados Solucionados no mesmo dia
//             */
//            Zend_Debug::dump($solicitacoesIsd);
//            $i=0;
//            $prazoUltrapassadoIsd = array();
//            foreach ($solicitacoesIsd as $isd) {
//                if(is_null($isd['DESCONSIDERADO_ISD'])){
//                    if(is_null ($isd['SSPA_DT_PRAZO'])){
//
//                        $prazoUltrapassadoIsd[$i] = $tempoSla->verificaPrazoUltrapassado($tempoSla->tempoTotalSLA($isd["DATA_CHAMADO"], $isd["DATA_FIM_CHAMADO"], '07:00:00', '20:00:00'), 46800);
//                        if ($prazoUltrapassadoIsd[$i] == true) {
//                            $solicitacoesIsdUltrapassado[$i] = $solicitacoesIsd[$i];
//                        }
//                    }else{
//                        if($isd['PRAZO_ULTRAPASSADO'] == 1){
//                            $solicitacoesIsdUltrapassado[$i] = $solicitacoesIsd[$i];
//                        }
//                    }
//                }
//                $i++;
//            }

            $totalIsdSolicitacoes = (count($solicitacoesIss));
            $foradoPrazoIsdSolicitacoes = count($solicitacoesIsdUltrapassado);
            $noPrazoIsdSolicitacoes = $totalIsdSolicitacoes - $foradoPrazoIsdSolicitacoes;
//            Zend_Debug::dump($totalIsdSolicitacoes,'$totalIsdSolicitacoes');
//            Zend_Debug::dump($noPrazoIsdSolicitacoes,'$noPrazoIsdSolicitacoes');
//            Zend_Debug::dump(count($solicitacoesIsdUltrapassado),'$solicitacoesIsdUltrapassado');

            if ($totalIsdSolicitacoes > 0) {
                $totalIsd = ($noPrazoIsdSolicitacoes / $totalIsdSolicitacoes) * 100;
            } else {
                $totalIsd = 100;
            }
            $totalIsd = (float) sprintf('%3.1f', $totalIsd);

            if ($totalIsd >= 95) {
                $glosaIsd = 0;
            }
            if (($totalIsd <= 94.9) && ($totalIsd >= 85.5)) {
                $glosaIsd = 2;
            }
            if (($totalIsd <= 85.4) && ($totalIsd >= 76.0)) {
                $glosaIsd = 4;
            }
            if (($totalIsd <= 75.9) && ($totalIsd >= 66.5)) {
                $glosaIsd = 7;
            }
            if ($totalIsd < 66.5) {
                $glosaIsd = 7;
            }
            /**
             * Carrega as variáveis para gerar o indicador:
             * ISD – Índice de Chamados Solucionados no mesmo dia
             */
            $slaHelpIsdNs = new Zend_Session_Namespace('slaHelpIsdNs');
            $slaHelpIsdNs->totalIsdSolicitacoes = $totalIsdSolicitacoes;
            $slaHelpIsdNs->foradoPrazoIsdSolicitacoes = $foradoPrazoIsdSolicitacoes;
            $slaHelpIsdNs->noPrazoIsdSolicitacoes = $noPrazoIsdSolicitacoes;
            $slaHelpIsdNs->idIndicadorISD = $ISD_DADOS['SINS_ID_INDICADOR'];
            $slaHelpIsdNs->solicitacoesIsdUltrapassado = $solicitacoesIsdUltrapassado;

            $this->view->totalIsdSolicitacoes = $totalIsdSolicitacoes;
            $this->view->foradoPrazoIsdSolicitacoes = $foradoPrazoIsdSolicitacoes;
            $this->view->noPrazoIsdSolicitacoes = $noPrazoIsdSolicitacoes;
            $this->view->idIndicadorISD = $ISD_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIsdUltrapassado = $solicitacoesIsdUltrapassado;

            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
//            /**
//             * Calcular o ITP – Índice de Ligações Telefônicas Perdidas
//             */
//            $dadosIrc = new Application_Model_DbTable_SosTbSsolSolicitacao();
//            $rowsIrc = $dadosIrc->getSolicitacoesPeriodoSla(1, '', $Sla_Helpdesk_ns->data['DATA_INICIAL'], $Sla_Helpdesk_ns->data['DATA_FINAL'], '', 1000);
//            $totalIrc = (count($solicitacoesIcr)*100)/(count($rowsIrc));
//            if ($totalIrc <= 10) {
//                $glosaIrc = 0;
//            }
//            if (($totalIrc >= 10.1) && ($totalIrc <= 11)) {
//                $glosaIrc = 2;
//            }
//            if (($totalIrc >= 11.01) && ($totalIrc <= 12)) {
//                $glosaIrc = 4;
//            }
//            if (($totalIrc >= 12.01) && ($totalIrc <= 13)) {
//                $glosaIrc = 7;
//            }
//            if ($totalIrc > 13) {
//                $glosaIrc = 7;
//            }
//            
            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */
//            /**
//             * Calcular o IAP – Índice de Ausência de prazo 
//             * 
//             */


            $solicitacoesIap = $solicitacoesIssUltrapassado;
            $i = 0;
            $prazoUltrapassadoIap = array();
            foreach ($solicitacoesIap as $iap) {
                if (is_null($iap['DESCONSIDERADO_IAP'])) {
                    if (is_null($iap['SSPA_DT_PRAZO'])) {
                        $solicitacoesIapUltrapassado[$i] = $iap;
                    } else {
                        if ($iap['PRAZO_ULTRAPASSADO'] == 1) {
                            $solicitacoesIapUltrapassado[$i] = $iap;
                        }
                    }
                }
                $i++;
            }


            $totalIapSolicitacoes = $totalIssSolicitacoes;
            $foradoPrazoIapSolicitacoes = count($solicitacoesIapUltrapassado);
            $noPrazoIapSolicitacoes = $totalIapSolicitacoes - $foradoPrazoIapSolicitacoes;
//            Zend_Debug::dump($totalIapSolicitacoes,'$totalIapSolicitacoes');
//            Zend_Debug::dump($noPrazoIapSolicitacoes,'$noPrazoIapSolicitacoes');
//            Zend_Debug::dump(count($solicitacoesIapUltrapassado),'$solicitacoesIapUltrapassado');


            if ($totalIapSolicitacoes > 0) {
                $totalIap = ($foradoPrazoIapSolicitacoes / $totalIapSolicitacoes) * 100;
            } else {
                $totalIap = 0;
            }
            $totalIap = (float) sprintf('%3.2f', $totalIap);

            if ($totalIap <= 10) {
                $glosaIap = 0;
            }
            if (($totalIap >= 10.01) && ($totalIap <= 11)) {
                $glosaIap = 2;
            }
            if (($totalIap >= 11.01) && ($totalIap <= 12)) {
                $glosaIap = 4;
            }
            if (($totalIap >= 12.01) && ($totalIap <= 13)) {
                $glosaIap = 7;
            }
            if ($totalIap > 13) {
                $glosaIap = 7;
            }
            /**
             * Carrega as variáveis para gerar o indicador:
             * IAP – Índice de Ausência de prazo
             */
            $slaHelpIapNs = new Zend_Session_Namespace('slaHelpIapNs');
            $slaHelpIapNs->totalIapSolicitacoes = $totalIapSolicitacoes;
            $slaHelpIapNs->foradoPrazoIapSolicitacoes = $foradoPrazoIapSolicitacoes;
            $slaHelpIapNs->noPrazoIapSolicitacoes = $noPrazoIapSolicitacoes;
            $slaHelpIapNs->idIndicadorIAP = $IAP_DADOS['SINS_ID_INDICADOR'];
            $slaHelpIapNs->solicitacoesIapUltrapassado = $solicitacoesIapUltrapassado;
            
            $this->view->totalIapSolicitacoes = $totalIapSolicitacoes;
            $this->view->foradoPrazoIapSolicitacoes = $foradoPrazoIapSolicitacoes;
            $this->view->noPrazoIapSolicitacoes = $noPrazoIapSolicitacoes;
            $this->view->idIndicadorIAP = $IAP_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIapUltrapassado = $solicitacoesIapUltrapassado;
            /*             * **************************************************************************************************************** */
            /*             * **************************************************************************************************************** */

            /**
             * Calcular o INC – Índice de chamados com Não Conformidade
             * 
             */
            $i = 0;
            $prazoUltrapassadoInc = array();
            foreach ($solicitacoesInc as $inc) {
                if (is_null($inc['DESCONSIDERADO_INC'])) {
                    if ((int) $inc['NAO_CONFORME'] > 0) {
                        $solicitacoesIncUltrapassado[$i] = $inc;
                    }
                }
                $i++;
            }
            $totalIncSolicitacoes = count($solicitacoesInc);
            $foradoPrazoIncSolicitacoes = count($solicitacoesIncUltrapassado);
            $noPrazoIncSolicitacoes = $totalIncSolicitacoes - $foradoPrazoIncSolicitacoes;

            $totalInc = ($foradoPrazoIncSolicitacoes / $totalIncSolicitacoes) * 100;
            if ($totalInc <= 5) {
                $glosaInc = 0;
            }
            $totalInc = (float) sprintf('%3.2f', $totalInc);


            if (($totalInc >= 5.01) && ($totalInc <= 5.50)) {
                $glosaInc = 1;
            }
            if (($totalInc >= 5.51) && ($totalInc <= 6)) {
                $glosaInc = 3;
            }
            if (($totalInc >= 6.01) && ($totalInc <= 6.5)) {
                $glosaInc = 5;
            }
            if ($totalInc > 6.5) {
                $glosaInc = 5;
            }
            /**
             * Carrega as variáveis para gerar o indicador:
             * INC – Índice de chamados com Não Conformidade
             */
            $slaHelpIncNs = new Zend_Session_Namespace('slaHelpIncNs');
            $slaHelpIncNs->totalIncSolicitacoes = $totalIncSolicitacoes;
            $slaHelpIncNs->foradoPrazoIncSolicitacoes = $foradoPrazoIncSolicitacoes;
            $slaHelpIncNs->noPrazoIncSolicitacoes = $noPrazoIncSolicitacoes;
            $slaHelpIncNs->idIndicadorINC = $INC_DADOS['SINS_ID_INDICADOR'];
            $slaHelpIncNs->solicitacoesIncUltrapassado = $solicitacoesIncUltrapassado;

            $this->view->totalIncSolicitacoes = $totalIncSolicitacoes;
            $this->view->foradoPrazoIncSolicitacoes = $foradoPrazoIncSolicitacoes;
            $this->view->noPrazoIncSolicitacoes = $noPrazoIncSolicitacoes;
            $this->view->idIndicadorINC = $INC_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIncUltrapassado = $solicitacoesIncUltrapassado;



            /*             * *********************************************************************************** */

            /*             * *********************************************************************************** */
            /**
             * Calcular o ICR – Índice de chamados reabertos
             * 
             */
            $i = 0;
            foreach ($solicitacoesIcr as $icr) {
                if (is_null($icr['DESCONSIDERADO_ICR'])) {
                    if (!is_null($icr['DATA_RECUSA'])) {
                        $tempoTotalSLAsegundos = $tempoSla->tempoTotalSLA($icr["DATA_BAIXA"], $icr["DATA_RECUSA"], '07:00:00', '20:00:00');
                        $prazoUltrapassadoIcr[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLAsegundos, 86400);
                        if ($prazoUltrapassadoIcr[$i] == false) {
                            $IcrRecusadasSolicitacoes[$i] = $icr;
                        }
                    }
                }
                $i++;
            }
            $totalIcrSolicitacoes = count($solicitacoesIcr);
            $RecusadasIcrSolicitacoes = count($IcrRecusadasSolicitacoes);
            $AvaliadasPositivamenteIcrSolicitacoes = $totalIcrSolicitacoes - $RecusadasIcrSolicitacoes;
//            Zend_Debug::dump($totalIcrSolicitacoes,'$totalIcrSolicitacoes');
//            Zend_Debug::dump($RecusadasIcrSolicitacoes,'$RecusadasIcrSolicitacoes');


            if ($totalIcrSolicitacoes > 0) {
                $totalIcr = ($RecusadasIcrSolicitacoes / $totalIcrSolicitacoes) * 100;
            } else {
                $totalIcr = 0;
            }
            $totalIcr = (float) sprintf('%3.2f', $totalIcr);

            if ($totalIcr <= 5) {
                $glosaIcr = 0;
            }
            if (($totalIcr >= 5.01) && ($totalIcr <= 5.50)) {
                $glosaIcr = 1;
            }
            if (($totalIcr >= 5.51) && ($totalIcr <= 6)) {
                $glosaIcr = 3;
            }
            if (($totalIcr >= 6.01) && ($totalIcr <= 6.5)) {
                $glosaIcr = 5;
            }
            if ($totalIcr > 6.5) {
                $glosaIcr = 5;
            }
            /**
             * Carrega as variáveis para gerar o indicador:
             * ICR – Índice de chamados reabertos
             */
            $slaHelpIcrNs = new Zend_Session_Namespace('slaHelpIcrNs');
            $slaHelpIcrNs->totalIcrSolicitacoes = $totalIcrSolicitacoes;
            $slaHelpIcrNs->RecusadasIcrSolicitacoes = $RecusadasIcrSolicitacoes;
            $slaHelpIcrNs->AvaliadasPositivamenteIcrSolicitacoes = $AvaliadasPositivamenteIcrSolicitacoes;
            $slaHelpIcrNs->idIndicadorICR = $ICR_DADOS['SINS_ID_INDICADOR'];
            $slaHelpIcrNs->IcrRecusadasSolicitacoes = $IcrRecusadasSolicitacoes;

            $this->view->totalIcrSolicitacoes = $totalIcrSolicitacoes;
            $this->view->RecusadasIcrSolicitacoes = $RecusadasIcrSolicitacoes;
            $this->view->AvaliadasPositivamenteIcrSolicitacoes = $AvaliadasPositivamenteIcrSolicitacoes;
            $this->view->idIndicadorICR = $ICR_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->IcrRecusadasSolicitacoes = $IcrRecusadasSolicitacoes;

            /**
             * Array contendo a meta alcançada para todos os índices
             */
            $meta[0] = $totalIia . '%'; // IIA – Índice de Início de Atendimento no Prazo
            $meta[1] = $totalIss . '%'; // ISS – Índice de Soluções das Solicitações no Prazo
            $meta[2] = $totalIsd . '%'; // ISD – Índice de Chamados Solucionados no mesmo dia
            $meta[3] = '--'; // ITP – Índice de Ligações Telefônicas Perdidas
            $meta[4] = $totalIap . '%'; // IAP – Índice de Ausência de Prazo
            $meta[5] = $totalInc . '%';  // INC – Índice de chamados com Não Conformidade
            $meta[6] = $totalIcr . '%'; // ICR – Índice de chamados reabertos
//            $meta[7] = $totalDpig.'%';//Deixar de Prestar as Informações Gerenciais
            /**
             * Array contendo o valor a ser glosado para todos os índices
             */
            $glosa[0] = $glosaIia . '%'; // IIA – Índice de Início de Atendimento no Prazo
            $glosa[1] = $glosaIss . '%'; // ISS – Índice de Soluções das Solicitações no Prazo
            $glosa[2] = $glosaIsd . '%'; // ISD – Índice de Chamados Solucionados no mesmo dia
            $glosa[3] = '--'; // ITP – Índice de Ligações Telefônicas Perdidas
            $glosa[4] = $glosaIap . '%';  // IAP – Índice de Ausência de Prazo
            $glosa[5] = $glosaInc . '%';  // INC – Índice de chamados com Não Conformidade
            $glosa[6] = $glosaIcr . '%'; // ICR – Índice de chamados reabertos
//            $glosa[7] = $glosaDpig.'%'; //Deixar de Prestar as Informações Gerenciais

            /**
             * Inclui a posição da meta alcançada no array dos indicadores mínimos
             */
            $indicadoresMinimos = $indicadorNivelServ->getIndicNivelServicoGrupo(1, '');
            $fim = count($indicadoresMinimos);
            for ($i = 0; $i < $fim; $i++) {
                $indicadoresMinimos[$i]['META_ALCANCADA'] = $meta[$i];
                $indicadoresMinimos[$i]['GLOSA'] = $glosa[$i];
            }
            $this->view->indicadoresMinimos = $indicadoresMinimos;
            $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
            $indMin->data = $indicadoresMinimos;
            $indMin->title = "SLA - SEÇÕES - TRF1";
            $indMin->periodo = 'PERÍODO: ' . $Sla_Secao_ns->data['DATA_INICIAL'] . ' À ' . $Sla_Secao_ns->data['DATA_FINAL'];
            $indMin->fuso = 'PERÍODO DE TEMPO DE ACORDO COM O FUSO HORÁRIO DA SEÇÃO: ' . '(' . $fusoHorario . ') HORA(S)';
            $indMin->secao = $Sla_Secao_ns->secao;
            $this->view->secao = $indMin->secao;

            $param = $this->_getParam('param', null);
            $this->view->pdf = false;
            if($param == 'pdf'){
                $this->view->sysdate = Trf1_Sosti_Negocio_Sla::getSysDate();
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                $userNamespace = new Zend_Session_Namespace('userNs');
                $this->view->emissor = strtoupper($userNamespace->matricula).' - '.$userNamespace->nome;
                $this->render('indicadoresnivelservicopdf2');
                $response = $this->getResponse();
                $body = $response->getBody();
                $response->clearBody();
//                Zend_Debug::dump($body);die;
                define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
                define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
                include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
                $mpdf=new mPDF('',    // mode - default ''
                    '',    // format - A4, for example, default ''
                    8,    // font size - default 0
                    '',    // default font family
                    10,    // margin_left
                    10,    // margin right
                    10,    // margin top
                    10,    // margin bottom
                    9,    // margin header
                    9,    // margin footer
                    'L');

                $mpdf->AddPage('P', '', '0', '1');
                $mpdf->WriteHTML($body);

                $name =  'sla_helpdesk_teste.pdf';
                $mpdf->Output();
            }
            else{
                $this->render('indicadoresnivelservico');
            }

//            $this->render('indicadoresnivelservico');
        }
    }

    public function indicadoresnivelservicocompletoAction() 
    {
        ini_set("memory_limit","1024M");
        set_time_limit(1200);
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $this->view->slaIndices = $indMin->data;
        $this->view->titulo = $indMin->title;
        $this->view->periodo = $indMin->periodo;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->view->emissor = strtoupper($userNamespace->matricula).' - '.$userNamespace->nome;
        /**
         * Carrega as variáveis para gerar o indicador:
         * IIA - Índice de Inicio de Atendimento no Prazo
         */
        $slaHelpIiaNs = new Zend_Session_Namespace('slaHelpIiaNs');
        $this->view->totalIiaSolicitacoes = $slaHelpIiaNs->totalIiaSolicitacoes;
        $this->view->noPrazoIiaSolicitacoes = $slaHelpIiaNs->noPrazoIiaSolicitacoes;
        $this->view->foradoPrazoIiaSolicitacoes = $slaHelpIiaNs->foradoPrazoIiaSolicitacoes;
        $this->view->idIndicadorIIA = $slaHelpIiaNs->idIndicadorIIA;
        $this->view->solicitacoesIia = $slaHelpIiaNs->solicitacoesIia;
        if (count($slaHelpIiaNs->solicitacoesIia) > 0) {
            $iIia = 0;
            foreach ($slaHelpIiaNs->solicitacoesIia as $iia) {
                $iIia++;
                $documentoIia[$iIia] = $iia['SSOL_ID_DOCUMENTO'];
}
            $this->view->documentoIia = array_unique($documentoIia);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * ISS – Índice de Soluções das Solicitações no Prazo
         */
        $slaHelpIssNs = new Zend_Session_Namespace('slaHelpIssNs');
        $this->view->totalIssSolicitacoes = $slaHelpIssNs->totalIssSolicitacoes;
        $this->view->foradoPrazoIssSolicitacoes = $slaHelpIssNs->foradoPrazoIssSolicitacoes;
        $this->view->noPrazoIssSolicitacoes = $slaHelpIssNs->noPrazoIssSolicitacoes;
        $this->view->idIndicadorISS = $slaHelpIssNs->idIndicadorISS;
        $this->view->solicitacoesIssUltrapassado = $slaHelpIssNs->solicitacoesIssUltrapassado;
        if (count($slaHelpIssNs->solicitacoesIssUltrapassado) > 0) {
            $iIss = 0;
            foreach ($slaHelpIssNs->solicitacoesIssUltrapassado as $iss) {
                $iIss++;
                $documentoIss[$iIss] = $iss['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoIss = array_unique($documentoIss);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * ISD – Índice de Chamados Solucionados no mesmo dia
         */
        $slaHelpIsdNs = new Zend_Session_Namespace('slaHelpIsdNs');
        $this->view->totalIsdSolicitacoes = $slaHelpIsdNs->totalIsdSolicitacoes;
        $this->view->foradoPrazoIsdSolicitacoes = $slaHelpIsdNs->foradoPrazoIsdSolicitacoes;
        $this->view->noPrazoIsdSolicitacoes = $slaHelpIsdNs->noPrazoIsdSolicitacoes;
        $this->view->idIndicadorISD = $slaHelpIsdNs->idIndicadorISD;
        $this->view->solicitacoesIsdUltrapassado = $slaHelpIsdNs->solicitacoesIsdUltrapassado;
        if (count($slaHelpIsdNs->solicitacoesIsdUltrapassado) > 0) {
            $iIsd = 0;
            foreach ($slaHelpIsdNs->solicitacoesIsdUltrapassado as $isd) {
                $iIsd++;
                $documentoIsd[$iIsd] = $isd['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoIsd = array_unique($documentoIsd);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * IAP – Índice de Ausência de prazo
         */
        $slaHelpIapNs = new Zend_Session_Namespace('slaHelpIapNs');
        $this->view->totalIapSolicitacoes = $slaHelpIapNs->totalIapSolicitacoes;
        $this->view->foradoPrazoIapSolicitacoes = $slaHelpIapNs->foradoPrazoIapSolicitacoes;
        $this->view->noPrazoIapSolicitacoes = $slaHelpIapNs->noPrazoIapSolicitacoes;
        $this->view->idIndicadorIAP = $slaHelpIapNs->idIndicadorIAP;
        $this->view->solicitacoesIapUltrapassado = $slaHelpIapNs->solicitacoesIapUltrapassado;
        if (count($slaHelpIapNs->solicitacoesIapUltrapassado) > 0) {
            $iIap = 0;
            foreach ($slaHelpIapNs->solicitacoesIapUltrapassado as $iap) {
                $iIsd++;
                $documentoIap[$iIap] = $iap['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoIap = array_unique($documentoIap);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * INC – Índice de chamados com Não Conformidade
         */
        $slaHelpIncNs = new Zend_Session_Namespace('slaHelpIncNs');
        $this->view->totalIncSolicitacoes = $slaHelpIncNs->totalIncSolicitacoes;
        $this->view->foradoPrazoIncSolicitacoes = $slaHelpIncNs->foradoPrazoIncSolicitacoes;
        $this->view->noPrazoIncSolicitacoes = $slaHelpIncNs->noPrazoIncSolicitacoes;
        $this->view->idIndicadorINC = $slaHelpIncNs->idIndicadorINC;
        $this->view->solicitacoesIncUltrapassado = $slaHelpIncNs->solicitacoesIncUltrapassado;
        if ((count($slaHelpIncNs->solicitacoesIncUltrapassado)) > 0) {
            $iInc = 0;
            foreach ($slaHelpIncNs->solicitacoesIncUltrapassado as $inc) {
                $iInc++;
                $documentoInc[$iInc] = $inc['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoInc = array_unique($documentoInc);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * ICR – Índice de chamados reabertos
         */
        $slaHelpIcrNs = new Zend_Session_Namespace('slaHelpIcrNs');
        $this->view->totalIcrSolicitacoes = $slaHelpIcrNs->totalIcrSolicitacoes;
        $this->view->RecusadasIcrSolicitacoes = $slaHelpIcrNs->RecusadasIcrSolicitacoes;
        $this->view->AvaliadasPositivamenteIcrSolicitacoes = $slaHelpIcrNs->AvaliadasPositivamenteIcrSolicitacoes;
        $this->view->idIndicadorICR = $slaHelpIcrNs->idIndicadorICR;
        $this->view->IcrRecusadasSolicitacoes = $slaHelpIcrNs->IcrRecusadasSolicitacoes;
        if (count($slaHelpIcrNs->IcrRecusadasSolicitacoes) > 0) {
            $iIcr = 0;
            foreach ($slaHelpIcrNs->IcrRecusadasSolicitacoes as $icr) {
                $iIcr++;
                $documentoIcr[$iIcr] = $icr['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoIcr = array_unique($documentoIcr);
        }
        $this->_helper->layout->disableLayout();
        /**
         * Gera o Excel
         */
        $param = $this->_getParam ('param'); 
        $this->view->param = $param;
        $periodo = $indMin->periodo;
        $p = explode(' ', $periodo);
        $nomeArq = str_replace('/','-',$p[1].'_'.$p[4]);
        if ($param == 'xls') {
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=sla_helpdesk_".$nomeArq.".xls");
        } else {
            /**
             * Gera o PDF
             */
            $this->render();
            $response = $this->getResponse();
            $body = $response->getBody();
            $response->clearBody();

            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
            $mpdf=new mPDF('',    // mode - default ''
                           '',    // format - A4, for example, default ''
                            8,    // font size - default 0
                           '',    // default font family
                           10,    // margin_left
                           10,    // margin right
                           10,    // margin top
                           10,    // margin bottom
                            9,    // margin header
                            9,    // margin footer
                           'L');

            $mpdf->AddPage('P', '', '0', '1');
            $mpdf->WriteHTML($body);

            $name =  'sla_helpdesk_'.$nomeArq.'.pdf';
            $mpdf->Output($name,'D');
        }
    }
}
