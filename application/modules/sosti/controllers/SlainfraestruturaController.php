<?php

class Sosti_SlainfraestruturaController extends Zend_Controller_Action {
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
      
         if ( ($horaAtual <= $horaInicio || $horaAtual >= $horaFinal) || strcmp($userNs->matricula, 'TR300785') == 0 || strcmp($userNs->matricula, 'TR179603') == 0 || strcmp($userNs->matricula, 'TR18077PS') == 0 ){
        */
        
        // Validação de acesso às funcionalidades do SLA, conforme servidor web
        $negocio = new Trf1_Sosti_Negocio_Sla ();
        $permiteSla = $negocio->permiteSla ();
        
        if ($permiteSla ['permissao']) {
            $form = new Sosti_Form_Sla();
            $form->removeElement('OPCAO');
            $form->removeElement('SGRS_ID_GRUPO');
            $form->setAction('triagem');
            $this->view->form = $form;
            $this->view->title = "SLA - INFRAESTRUTURA";
            $Sla_Helpdesk_ns = new Zend_Session_Namespace('Sla_Infraestrutura_ns');
            if ($Sla_Helpdesk_ns->data != '') {
                $form->populate($Sla_Helpdesk_ns->data);
            }
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                if ($form->isValid($data)) {
                    $Sla_Helpdesk_ns = new Zend_Session_Namespace('Sla_Infraestrutura_ns');
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
                     * Formato MkTime: Hora,Minuto,Segundo,Mes,Dia,Ano
                     * 
                     */
                    $timestempInicioContagem = mktime(23, 59, 59, 10, 31, 2012);
                    if ($timestempInicial < $timestempInicioContagem && $timestempFinal <= $timestempInicioContagem) {
                        $this->_helper->_redirector('indicadoressempedidoinformacao', 'slainfraestrutura', 'sosti');
                    } elseif ($timestempInicial > $timestempInicioContagem && $timestempFinal > $timestempInicioContagem) {
                        $this->_helper->_redirector('indicadorescompedidoinformacao', 'slainfraestrutura', 'sosti');
                    } else if ($timestempInicial < $timestempInicioContagem && $timestempFinal > $timestempInicioContagem) {
                        $msg_to_user = "Não é possivel retirar relatório do prazo entre Outubro e Novembro, pois o sistema de calculo foi modificado";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                        $this->_helper->_redirector('triagem', 'slainfraestrutura', 'sosti');
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
        set_time_limit(1800); //30 minutos para gerar o relatório

        $userNs = new Zend_Session_Namespace('userNs');
        $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $possui_permissao_fechamento = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('GESTOR DO CONTRATO DA INFRAESTRUTURA', $userNs->matricula);
        $this->view->possui_permissao_fechamento = $possui_permissao_fechamento;

        $tempoSla = new App_Sosti_TempoSla();
        $form = new Sosti_Form_Sla();
        $form->removeElement('OPCAO');
        $form->removeElement('SGRS_ID_GRUPO');
        $this->view->form = $form;
        $this->view->title = "SLA - INFRAESTRUTURA";
        $Sla_Infraestrutura_ns = new Zend_Session_Namespace('Sla_Infraestrutura_ns');
        if ($Sla_Infraestrutura_ns->data != '') {
            $form->populate($Sla_Infraestrutura_ns->data);
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $Sla_Infraestrutura_ns = new Zend_Session_Namespace('Sla_Infraestrutura_ns');
                $Sla_Infraestrutura_ns->data = $data;
            }
        }
        if ($Sla_Infraestrutura_ns->data != '') {
            $this->view->data = $Sla_Infraestrutura_ns->data;
            $form->populate($Sla_Infraestrutura_ns->data);
            /**
             * INDICADOR IIA 1
             */
            $IIA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'IIA');
            $ISC_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'ISC');
            $IAP_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'IAP');
            $ICR_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'ICR');
            $MTTR_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'MTTR');
            $INC_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'INC');
            $IRC_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'IRC');
            $DPIG_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'DPIG');

            $solicitacoesIia = $indicadorNivelServ->getDatasSLA_IIA(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL'], 0, $IIA_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesIsc = $indicadorNivelServ->getDatasSLA_ISC(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL'], 0, $ISC_DADOS['SINS_ID_INDICADOR'], $IAP_DADOS['SINS_ID_INDICADOR']);
//            $solicitacoesIap = 'metodo';
            $solicitacoesIcr = $indicadorNivelServ->getDatasSLA_ICR(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL'], 0, $ICR_DADOS['SINS_ID_INDICADOR']);
//            $solicitacoesMttr = 'metodo';
            $solicitacoesInc = $indicadorNivelServ->getDatasSLA_INC(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL'], 0, $INC_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesIrc = $indicadorNivelServ->getDatasSLA_IRC(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL']);
//            $solicitacoesDepig = 'metodo';



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
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

            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o Índice de Soluções dos Chamados Encerrados no Prazo
             */
            $i = 0;
            $prazoUltrapassadoIsc = array();
            $solicitacoesIscUltrapassado = array();
            $fimIsc = count($solicitacoesIsc);
            for ($i = 0; $i < $fimIsc; $i++) {

                if (is_null($solicitacoesIsc[$i]['SSPA_DT_PRAZO'])) {
                    /*                     * TEMPO ÚTIL EM SEGUNDOS */
                    $tempoTotalSLAsegundos = $tempoSla->tempoTotalSLA($solicitacoesIsc[$i]["DATA_CHAMADO"], $solicitacoesIsc[$i]["DATA_FIM_CHAMADO"], '07:00:00', '20:00:00');

                    /*                     * ISC */
                    $prazoUltrapassadoIsc[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLAsegundos, 57600);
                    if ($prazoUltrapassadoIsc[$i] == true) {
                        if (is_null($solicitacoesIsc[$i]['DESCONSIDERADO_ISC'])) {
                            $solicitacoesIscUltrapassado[$i] = $solicitacoesIsc[$i];
                        }
                    }
                } else {
                    if ($solicitacoesIsc[$i]['PRAZO_ULTRAPASSADO'] == 1) {

                        if (is_null($solicitacoesIsc[$i]['DESCONSIDERADO_ISC'])) {
                            $solicitacoesIscUltrapassado[$i] = $solicitacoesIsc[$i];
                        }
                    }
                }
            }

            $totalIscSolicitacoes = (count($solicitacoesIsc));
            $foradoPrazoIscSolicitacoes = count($solicitacoesIscUltrapassado);
            $noPrazoIscSolicitacoes = $totalIscSolicitacoes - $foradoPrazoIscSolicitacoes;

            if ($totalIscSolicitacoes > 0) {
                $totalIsc = ($noPrazoIscSolicitacoes / $totalIscSolicitacoes) * 100;
            } else {
                $totalIsc = 100;
            }
            $totalIsc = (float) sprintf('%3.1f', $totalIsc);

            if ($totalIsc >= 90) {
                $glosaIsc = 0;
            }
            if (($totalIsc <= 89.9) && ($totalIsc >= 84.5)) {
                $glosaIsc = 2;
            }
            if (($totalIsc <= 84.4) && ($totalIsc >= 75)) {
                $glosaIsc = 4;
            }
            if (($totalIsc <= 74.9) && ($totalIsc >= 63)) {
                $glosaIsc = 7;
            }
            if ($totalIsc < 63) {
                $glosaIsc = 7;
            }

            $this->view->totalIscSolicitacoes = $totalIscSolicitacoes;
            $this->view->foradoPrazoIscSolicitacoes = $foradoPrazoIscSolicitacoes;
            $this->view->noPrazoIscSolicitacoes = $noPrazoIscSolicitacoes;
            $this->view->idIndicadorISC = $ISC_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIscUltrapassado = $solicitacoesIscUltrapassado;
            $this->view->solicitacoesIsc = $solicitacoesIscUltrapassado;
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o IAP – Índice de Ausência de prazo 
             * 
             */
            $solicitacoesIap = $solicitacoesIscUltrapassado;
            $solicitacoesIapUltrapassado = array();
            $i = 0;
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


            $totalIapSolicitacoes = $totalIscSolicitacoes;
            $foradoPrazoIapSolicitacoes = count($solicitacoesIapUltrapassado);
            $noPrazoIapSolicitacoes = $totalIapSolicitacoes - $foradoPrazoIapSolicitacoes;

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
                $glosaIap = 1;
            }
            if (($totalIap >= 11.01) && ($totalIap <= 12)) {
                $glosaIap = 3;
            }
            if (($totalIap >= 12.01) && ($totalIap <= 13)) {
                $glosaIap = 5;
            }
            if ($totalIap > 13) {
                $glosaIap = 5;
            }

            $this->view->totalIapSolicitacoes = $totalIapSolicitacoes;
            $this->view->foradoPrazoIapSolicitacoes = $foradoPrazoIapSolicitacoes;
            $this->view->noPrazoIapSolicitacoes = $noPrazoIapSolicitacoes;
            $this->view->idIndicadorIAP = $IAP_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIapUltrapassado = $solicitacoesIapUltrapassado;
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o ICR – Índice de chamados reabertos
             * 
             */
            $i = 0;
            $IcrRecusadasSolicitacoes = array();
            foreach ($solicitacoesIcr as $icr) {
                if (is_null($icr['DESCONSIDERADO_ICR'])) {
                    if (!is_null($icr['DATA_RECUSA'])) {
                        if (((float) $icr['DIAS_RECUSA']) <= 5) {
                            $IcrRecusadasSolicitacoes[$i] = $icr;
                        }
                    }
                }
                $i++;
            }
            $totalIcrSolicitacoes = count($solicitacoesIcr);
            $RecusadasIcrSolicitacoes = count($IcrRecusadasSolicitacoes);
            $AvaliadasPositivamenteIcrSolicitacoes = $totalIcrSolicitacoes - $RecusadasIcrSolicitacoes;

            if ($totalIcrSolicitacoes > 0) {
                $totalIcr = ($RecusadasIcrSolicitacoes / $totalIcrSolicitacoes) * 100;
            } else {
                $totalIcr = 0;
            }
            $totalIcr = (float) sprintf('%3.2f', $totalIcr);

            /**
             * Tabela de glosas 
             */
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
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Índice de Chamados Resolvidos pelo Contratante
             */
            $i = 0;
            $IrcResolvidosContratanteSolicitacoes = array();
            foreach ($solicitacoesIrc as $Irc) {
                if (substr($Irc['MATRICULA_BAIXA_ENCAM'], -2, 2) != 'PS') {
                    $IrcResolvidosContratanteSolicitacoes[$i] = $solicitacoesIrc[$i];
                }
                $i++;
            }

            $totalIrcSolicitacoes = count($solicitacoesIrc);
            $IrcResolvidosContratante = count($IrcResolvidosContratanteSolicitacoes);

            if ($IrcResolvidosContratante > 0) {
                $totalIrc = ($IrcResolvidosContratante / $totalIrcSolicitacoes) * 100;
            } else {
                $totalIrc = 0;
            }
            $totalIrc = (float) sprintf('%3.2f', $totalIrc);

            if ($totalIrc <= 5) {
                $glosaIrc = 0;
            }
            if (($totalIrc >= 5.01) && ($totalIrc <= 10)) {
                $glosaIrc = 2;
            }
            if (($totalIrc >= 10.01) && ($totalIrc <= 15)) {
                $glosaIrc = 4;
            }
            if (($totalIrc >= 15.01) && ($totalIrc <= 20)) {
                $glosaIrc = 7;
            }
            if ($totalIrc > 20) {
                $glosaIrc = 7;
            }

            $this->view->totalIrcSolicitacoes = $totalIrcSolicitacoes;
            $this->view->idIndicadorIRC = $IRC_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIrc = $IrcResolvidosContratanteSolicitacoes;

            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */

            /**
             * Calcular o INC – Índice de chamados com Não Conformidade
             * 
             */
            $i = 0;
            $prazoUltrapassadoInc = array();
            $solicitacoesIncUltrapassado = array();
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

            if ($totalIncSolicitacoes > 0) {
                $totalInc = ($foradoPrazoIncSolicitacoes / $totalIncSolicitacoes) * 100;
            } else {
                $totalInc = 0;
            }


            $totalInc = (float) sprintf('%3.2f', $totalInc);

            if ($totalInc <= 5) {
                $glosaInc = 0;
            }
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
             * Array contendo a meta alcançada para todos os índices
             */
            $meta[0] = $totalIia . '%'; //Índice de Início de Atendimento no Prazo
            $meta[1] = $totalIsc . '%'; //Índice de Índices de Soluções dos Chamados Encerradas no Prazo
            $meta[2] = $totalIap . '%'; //Índice de Ausência de Prazo
            $meta[3] = $totalIcr . '%'; //Índice de Chamados Reabertos
            $meta[4] = '--'; //$totalMttr.'%';//Tempo Médio para Reparo
            $meta[5] = $totalInc . '%'; //Índice de Chamados com Não Conformidade
            $meta[6] = $totalIrc . '%'; //Índice de Chamados Resolvidos pelo Contratante
//            $meta[7] = $totalDpig.'%';//Deixar de Prestar as Informações Gerenciais
            /**
             * Array contendo o valor a ser glosado para todos os índices
             */
            $glosa[0] = $glosaIia . '%';
            $glosa[1] = $glosaIsc . '%';
            $glosa[2] = $glosaIap . '%';
            $glosa[3] = $glosaIcr . '%';
            $glosa[4] = '--'; //$glosaMttr.'%';
            $glosa[5] = $glosaInc . '%';
            $glosa[6] = $glosaIrc . '%';
//            $glosa[7] = $glosaDpig.'%';
            /**
             * Inclui a posição da meta alcançada no array dos indicadores mínimos
             */
            $indicadoresMinimos = $indicadorNivelServ->getIndicNivelServicoGrupo(3, '');
            $fim = count($indicadoresMinimos);
            for ($i = 0; $i < $fim; $i++) {
                $indicadoresMinimos[$i]['META_ALCANCADA'] = $meta[$i];
                $indicadoresMinimos[$i]['GLOSA'] = $glosa[$i];
            }
            $this->view->indicadoresMinimos = $indicadoresMinimos;
            $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
            $indMin->data = $indicadoresMinimos;
            $indMin->title = "SLA - INFRAESTRUTURA - TRF1";
            $indMin->periodo = 'PERÍODO: ' . $Sla_Infraestrutura_ns->data['DATA_INICIAL'] . ' À ' . $Sla_Infraestrutura_ns->data['DATA_FINAL'];
            $this->render('indicadoresnivelservico');
        }
    }

    public function indicadorescompedidoinformacaoAction() {
        set_time_limit(1200); //10 minutos para gerar o relatório

        $userNs = new Zend_Session_Namespace('userNs');
        $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $possui_permissao_fechamento = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('GESTOR DO CONTRATO DA INFRAESTRUTURA', $userNs->matricula);
        $this->view->possui_permissao_fechamento = $possui_permissao_fechamento;

        $tempoSla = new App_Sosti_TempoSla();
        $form = new Sosti_Form_Sla();
        $form->removeElement('OPCAO');
        $form->removeElement('SGRS_ID_GRUPO');
        $form->setAction('triagem');
        $this->view->form = $form;
        $this->view->title = "SLA - INFRAESTRUTURA";
        $Sla_Infraestrutura_ns = new Zend_Session_Namespace('Sla_Infraestrutura_ns');
        if ($Sla_Infraestrutura_ns->data != '') {
            $form->populate($Sla_Infraestrutura_ns->data);
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $Sla_Infraestrutura_ns = new Zend_Session_Namespace('Sla_Infraestrutura_ns');
                $Sla_Infraestrutura_ns->data = $data;
            }
        }
        if ($Sla_Infraestrutura_ns->data != '') {
            $this->view->data = $Sla_Infraestrutura_ns->data;
            $form->populate($Sla_Infraestrutura_ns->data);
            /**
             * INDICADOR IIA 1
             */
            $IIA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'IIA');
            $ISC_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'ISC');
            $IAP_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'IAP');
            $ICR_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'ICR');
            $MTTR_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'MTTR');
            $INC_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'INC');
            $IRC_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'IRC');
            $DPIG_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(3, 'DPIG');

            $solicitacoesIia = $indicadorNivelServ->getDatasSLA_IIA(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL'], 0, $IIA_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesIsc = $indicadorNivelServ->getDatasSLA_ISC(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL'], 0, $ISC_DADOS['SINS_ID_INDICADOR'], $IAP_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesIcr = $indicadorNivelServ->getDatasSLA_ICR(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL'], 0, $ICR_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesInc = $indicadorNivelServ->getDatasSLA_INC(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL'], 0, $INC_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesIrc = $indicadorNivelServ->getDatasSLA_IRC(3, $Sla_Infraestrutura_ns->data['DATA_INICIAL'], $Sla_Infraestrutura_ns->data['DATA_FINAL']);


            /**
             * Configurações do horário de expediente
             */
            $SosTbGrexGrupoServExped = new Application_Model_DbTable_SosTbGrexGrupoServExped();
            $expedienteNormal = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(3, "SLA");
            $expedienteEmergencia = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(2, "EMERGENCIAL");

            $expediente = array('NORMAL' => array('INICIO' => $expedienteNormal["INICIO"], 'FIM' => $expedienteNormal["FIM"]), 'EMERGENCIAL' => array('INICIO' => $expedienteEmergencia['INICIO'], 'FIM' => $expedienteEmergencia['FIM']));
            $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos($expediente["NORMAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["NORMAL"]["INICIO"]);
            $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos($expediente["EMERGENCIAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["EMERGENCIAL"]["INICIO"]);
            $expediente["NORMAL"]["DIA_UTIL_HORAS"] = $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;
            $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"] = $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;

            /**
             * Calcula o tempo total das solicitações não contabilizando o tempo em que a solicitação ficou aguardando a resposta do pedido de informação.
             */
            $TempoTotalPedidoInforArrISC = $tempoSla->TempoTotalPedidoInfor($solicitacoesIsc, 'MOFA_ID_MOVIMENTACAO', "DATA_CHAMADO", "DATA_FIM_CHAMADO", "", "", $expediente);
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
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

            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */

            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o Índice de Soluções dos Chamados Encerrados no Prazo
             */
            $i = 0;
            $prazoUltrapassadoIsc = array();
            $solicitacoesIscUltrapassado = array();
            $fimIsc = count($solicitacoesIsc);

            for ($i = 0; $i < $fimIsc; $i++) {

                if (is_null($solicitacoesIsc[$i]['SSPA_DT_PRAZO'])) {
                    /*                     * TEMPO ÚTIL EM SEGUNDOS */
//                    $tempoTotalSLAsegundos =  $tempoSla->tempoTotalSLA($solicitacoesIsc[$i]["DATA_CHAMADO"], $solicitacoesIsc[$i]["DATA_FIM_CHAMADO"], '07:00:00', '20:00:00');
                    $idMovimentacao = $solicitacoesIsc[$i]["MOFA_ID_MOVIMENTACAO"];
                    $tempoTotalSLAsegundos = $TempoTotalPedidoInforArrISC[$idMovimentacao]["TEMPO_UTIL_TOTAL"];

                    /*                     * ISC */
                    $prazoUltrapassadoIsc[$i] = $tempoSla->verificaPrazoUltrapassado($tempoTotalSLAsegundos, 57600);
                    if ($prazoUltrapassadoIsc[$i] == true) {
                        if (is_null($solicitacoesIsc[$i]['DESCONSIDERADO_ISC'])) {
                            $solicitacoesIscUltrapassado[$i] = $solicitacoesIsc[$i];
                        }
                    }
                } else {
                    if ($solicitacoesIsc[$i]['PRAZO_ULTRAPASSADO'] == 1) {
                        if (is_null($solicitacoesIsc[$i]['DESCONSIDERADO_ISC'])) {
                            $solicitacoesIscUltrapassado[$i] = $solicitacoesIsc[$i];
                        }
                    }
                }
            }

            $totalIscSolicitacoes = (count($solicitacoesIsc));
            $foradoPrazoIscSolicitacoes = count($solicitacoesIscUltrapassado);
            $noPrazoIscSolicitacoes = $totalIscSolicitacoes - $foradoPrazoIscSolicitacoes;

            if ($totalIscSolicitacoes > 0) {
                $totalIsc = ($noPrazoIscSolicitacoes / $totalIscSolicitacoes) * 100;
            } else {
                $totalIsc = 100;
            }
            $totalIsc = (float) sprintf('%3.1f', $totalIsc);

            if ($totalIsc >= 90) {
                $glosaIsc = 0;
            }
            if (($totalIsc <= 89.9) && ($totalIsc >= 84.5)) {
                $glosaIsc = 2;
            }
            if (($totalIsc <= 84.4) && ($totalIsc >= 75)) {
                $glosaIsc = 4;
            }
            if (($totalIsc <= 74.9) && ($totalIsc >= 63)) {
                $glosaIsc = 7;
            }
            if ($totalIsc < 63) {
                $glosaIsc = 7;
            }

            $this->view->totalIscSolicitacoes = $totalIscSolicitacoes;
            $this->view->foradoPrazoIscSolicitacoes = $foradoPrazoIscSolicitacoes;
            $this->view->noPrazoIscSolicitacoes = $noPrazoIscSolicitacoes;
            $this->view->idIndicadorISC = $ISC_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIscUltrapassado = $solicitacoesIscUltrapassado;
            $this->view->solicitacoesIsc = $solicitacoesIscUltrapassado;
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o IAP – Índice de Ausência de prazo 
             * 
             */
            $solicitacoesIap = $solicitacoesIscUltrapassado;
            $solicitacoesIapUltrapassado = array();
            $i = 0;
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


            $totalIapSolicitacoes = $totalIscSolicitacoes;
            $foradoPrazoIapSolicitacoes = count($solicitacoesIapUltrapassado);
            $noPrazoIapSolicitacoes = $totalIapSolicitacoes - $foradoPrazoIapSolicitacoes;

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
                $glosaIap = 1;
            }
            if (($totalIap >= 11.01) && ($totalIap <= 12)) {
                $glosaIap = 3;
            }
            if (($totalIap >= 12.01) && ($totalIap <= 13)) {
                $glosaIap = 5;
            }
            if ($totalIap > 13) {
                $glosaIap = 5;
            }

            $this->view->totalIapSolicitacoes = $totalIapSolicitacoes;
            $this->view->foradoPrazoIapSolicitacoes = $foradoPrazoIapSolicitacoes;
            $this->view->noPrazoIapSolicitacoes = $noPrazoIapSolicitacoes;
            $this->view->idIndicadorIAP = $IAP_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIapUltrapassado = $solicitacoesIapUltrapassado;
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o ICR – Índice de chamados reabertos
             * 
             */
            $i = 0;
            $IcrRecusadasSolicitacoes = array();
            foreach ($solicitacoesIcr as $icr) {
                if (is_null($icr['DESCONSIDERADO_ICR'])) {
                    if (!is_null($icr['DATA_RECUSA'])) {
                        if (((float) $icr['DIAS_RECUSA']) <= 5) {
                            $IcrRecusadasSolicitacoes[$i] = $icr;
                        }
                    }
                }
                $i++;
            }
            $totalIcrSolicitacoes = count($solicitacoesIcr);
            $RecusadasIcrSolicitacoes = count($IcrRecusadasSolicitacoes);
            $AvaliadasPositivamenteIcrSolicitacoes = $totalIcrSolicitacoes - $RecusadasIcrSolicitacoes;

            if ($totalIcrSolicitacoes > 0) {
                $totalIcr = ($RecusadasIcrSolicitacoes / $totalIcrSolicitacoes) * 100;
            } else {
                $totalIcr = 0;
            }
            $totalIcr = (float) sprintf('%3.2f', $totalIcr);

            /**
             * Tabela de glosas 
             */
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
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Índice de Chamados Resolvidos pelo Contratante
             */
            $i = 0;
            $IrcResolvidosContratanteSolicitacoes = array();
            foreach ($solicitacoesIrc as $Irc) {
                if (substr($Irc['MATRICULA_BAIXA_ENCAM'], -2, 2) != 'PS') {
                    $IrcResolvidosContratanteSolicitacoes[$i] = $solicitacoesIrc[$i];
                }
                $i++;
            }

            $totalIrcSolicitacoes = count($solicitacoesIrc);
            $IrcResolvidosContratante = count($IrcResolvidosContratanteSolicitacoes);

            if ($IrcResolvidosContratante > 0) {
                $totalIrc = ($IrcResolvidosContratante / $totalIrcSolicitacoes) * 100;
            } else {
                $totalIrc = 0;
            }
            $totalIrc = (float) sprintf('%3.2f', $totalIrc);

            if ($totalIrc <= 5) {
                $glosaIrc = 0;
            }
            if (($totalIrc >= 5.01) && ($totalIrc <= 10)) {
                $glosaIrc = 2;
            }
            if (($totalIrc >= 10.01) && ($totalIrc <= 15)) {
                $glosaIrc = 4;
            }
            if (($totalIrc >= 15.01) && ($totalIrc <= 20)) {
                $glosaIrc = 7;
            }
            if ($totalIrc > 20) {
                $glosaIrc = 7;
            }

            $this->view->totalIrcSolicitacoes = $totalIrcSolicitacoes;
            $this->view->idIndicadorIRC = $IRC_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesIrc = $IrcResolvidosContratanteSolicitacoes;

            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */

            /**
             * Calcular o INC – Índice de chamados com Não Conformidade
             * 
             */
            $i = 0;
            $prazoUltrapassadoInc = array();
            $solicitacoesIncUltrapassado = array();
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

            if ($totalIncSolicitacoes > 0) {
                $totalInc = ($foradoPrazoIncSolicitacoes / $totalIncSolicitacoes) * 100;
            } else {
                $totalInc = 0;
            }


            $totalInc = (float) sprintf('%3.2f', $totalInc);

            if ($totalInc <= 5) {
                $glosaInc = 0;
            }
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
             * Array contendo a meta alcançada para todos os índices
             */
            $meta[0] = $totalIia . '%'; //Índice de Início de Atendimento no Prazo
            $meta[1] = $totalIsc . '%'; //Índice de Índices de Soluções dos Chamados Encerradas no Prazo
            $meta[2] = $totalIap . '%'; //Índice de Ausência de Prazo
            $meta[3] = $totalIcr . '%'; //Índice de Chamados Reabertos
            $meta[4] = '--'; //$totalMttr.'%';//Tempo Médio para Reparo
            $meta[5] = $totalInc . '%'; //Índice de Chamados com Não Conformidade
            $meta[6] = $totalIrc . '%'; //Índice de Chamados Resolvidos pelo Contratante
//            $meta[7] = $totalDpig.'%';//Deixar de Prestar as Informações Gerenciais
            /**
             * Array contendo o valor a ser glosado para todos os índices
             */
            $glosa[0] = $glosaIia . '%';
            $glosa[1] = $glosaIsc . '%';
            $glosa[2] = $glosaIap . '%';
            $glosa[3] = $glosaIcr . '%';
            $glosa[4] = '--'; //$glosaMttr.'%';
            $glosa[5] = $glosaInc . '%';
            $glosa[6] = $glosaIrc . '%';
//            $glosa[7] = $glosaDpig.'%';
            /**
             * Inclui a posição da meta alcançada no array dos indicadores mínimos
             */
            $indicadoresMinimos = $indicadorNivelServ->getIndicNivelServicoGrupo(3, '');
            $fim = count($indicadoresMinimos);
            for ($i = 0; $i < $fim; $i++) {
                $indicadoresMinimos[$i]['META_ALCANCADA'] = $meta[$i];
                $indicadoresMinimos[$i]['GLOSA'] = $glosa[$i];
            }
            $this->view->indicadoresMinimos = $indicadoresMinimos;
            $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
            $indMin->data = $indicadoresMinimos;
            $indMin->title = "SLA - INFRAESTRUTURA - TRF1";
            $indMin->periodo = 'PERÍODO: ' . $Sla_Infraestrutura_ns->data['DATA_INICIAL'] . ' À ' . $Sla_Infraestrutura_ns->data['DATA_FINAL'];

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


        }
    }

}
