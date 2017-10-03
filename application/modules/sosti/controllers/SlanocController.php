<?php

class Sosti_SlanocController extends Zend_Controller_Action {
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
		
        $this->view->titleBrowser = 'e-Sosti';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function triagemAction() {
        set_time_limit(1200); //10 minutos
        
        /*
        // Restricoes aplicadas conforme pedido nos sostis  2013010001155011550160000357 e 2013010001155011550160000349
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
            $form->removeElement('SGRS_ID_GRUPO');
            $form->setAction('triagem');
            $this->view->form = $form;
            $this->view->title = "SLA - NOC";
            $Sla_Helpdesk_ns = new Zend_Session_Namespace('Sla_Noc_ns');
            if ($Sla_Helpdesk_ns->data != '') {
                $form->populate($Sla_Helpdesk_ns->data);
            }
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                if ($form->isValid($data)) {
                    $Sla_Helpdesk_ns = new Zend_Session_Namespace('Sla_Noc_ns');
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
                        $this->_helper->_redirector('indicadoressempedidoinformacao', 'slanoc', 'sosti');
                    } elseif ($timestempInicial > $timestempInicioContagem && $timestempFinal > $timestempInicioContagem) {
                        $this->_helper->_redirector('indicadorescompedidoinformacao', 'slanoc', 'sosti');
                    } else if($timestempInicial < $timestempInicioContagem && $timestempFinal > $timestempInicioContagem) {
                        $msg_to_user = "Não é possivel retirar relatório do prazo entre Outubro e Novembro, pois o sistema de calculo foi modificado";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                        $this->_helper->_redirector('triagem', 'slanoc', 'sosti');
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
        set_time_limit(1200); //10 minutos para gerar o relatório

        $userNs = new Zend_Session_Namespace('userNs');
        $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $tempoSla = new App_Sosti_TempoSla();
        $form = new Sosti_Form_Sla();
        $form->removeElement('OPCAO');
        $form->removeElement('SGRS_ID_GRUPO');
        $form->setAction('triagem');
        $this->view->form = $form;
        $this->view->title = "SLA - NOC";



        $Sla_Noc_ns = new Zend_Session_Namespace('Sla_Noc_ns');

        if ($Sla_Noc_ns->data != '') {
            $form->populate($Sla_Noc_ns->data);
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $Sla_Noc_ns = new Zend_Session_Namespace('Sla_Noc_ns');
                $Sla_Noc_ns->data = $data;
            }
        }
        if ($Sla_Noc_ns->data != '') {
            $this->view->data = $Sla_Noc_ns->data;
            $form->populate($Sla_Noc_ns->data);

            /**
             * Permissão de desconsiderar SLA 
             */
            $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
            $possui_permissao_fechamento = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('GESTOR DO CONTRATO DO NOC', $userNs->matricula);
            $this->view->possui_permissao_fechamento = $possui_permissao_fechamento;

//            $TMC_DADOS  = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'TMC');
            $TMA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'TMA');
            $TMCSA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'TMCSA');
            $MAICPA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'MAICPA');
            $NVNR_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'NVNR');


            $solicitacoesTma = $indicadorNivelServ->getDatasSLA_TMA(4, $Sla_Noc_ns->data['DATA_INICIAL'], $Sla_Noc_ns->data['DATA_FINAL'], $TMA_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesTmcsa = $indicadorNivelServ->getDatasSLA_TMCSA(4, $Sla_Noc_ns->data['DATA_INICIAL'], $Sla_Noc_ns->data['DATA_FINAL'], $TMCSA_DADOS['SINS_ID_INDICADOR'], $MAICPA_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesNvnr = $indicadorNivelServ->getDatasSLA_NVNR(4, $Sla_Noc_ns->data['DATA_INICIAL'], $Sla_Noc_ns->data['DATA_FINAL'], $NVNR_DADOS['SINS_ID_INDICADOR']);
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o TMA – Tempo médio de atendimento pela monitoria <= 10 minutos
             */
            $i = 0;
            $totalTmaSolicitacoes = (count($solicitacoesTma));
            $somatoriaTma = 0;
            $total_parcial_Tma = 0;
            $tempo_ated_Tma = 0;
            foreach ($solicitacoesTma as $tma) {

                $tempo_ated_Tma = $tempoSla->tempoTotalSLA($tma["DATA_CHAMADO"], $tma["DATA_PRIMEIRO_ATENDIMENTO"], '07:00:00', '20:00:00');
                $solicitacoesTma[$i]['TEMPO_ATENDIMENTO_MINUTOS'] = sprintf('%.2f', $tempo_ated_Tma / 60);

                if (is_null($tma['DESCONSIDERADO_TMA'])) {
                    $somatoriaTma += $tempo_ated_Tma;
                    $solicitacoesTma[$i]['CONSIDERADO_TMA'] = 'S';
                    $total_parcial_Tma++;
                }

                $i++;
            }
            $somatoriaTma = $somatoriaTma / 60;

            if ($totalTmaSolicitacoes > 0) {
                $totalTma = ($somatoriaTma / $total_parcial_Tma);
            } else {
                $totalTma = 0;
            }

            $totalTma = (int) $totalTma;

            if ($totalTma <= 10) {
                $glosaTma = 0;
            }
            if (($totalTma <= 20) && ($totalTma >= 11)) {
                $glosaTma = 2;
            }
            if (($totalTma <= 40) && ($totalTma >= 21)) {
                $glosaTma = 4;
            }
            if (($totalTma <= 60 ) && ($totalTma >= 41)) {
                $glosaTma = 7;
            }
            if ($totalTma > 60) {
                $glosaTma = 7;
            }

            $this->view->totalTmaSolicitacoes = $totalTmaSolicitacoes;
            $this->view->totalParcial = $total_parcial_Tma;
            $this->view->totalDesconsideradas = $totalTmaSolicitacoes - $total_parcial_Tma;
            $this->view->tempoMedioTMA = $totalTma;
            $this->view->idIndicadorTMA = $TMA_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesTma = $solicitacoesTma;
//            Zend_Debug::dump($solicitacoesTma);
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */


            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Tempo médio para cadastramento de novos serviços ou ativos
             */
            $i = 0;
            $fimTmcsa = count($solicitacoesTmcsa);
            $totalTmcsaSolicitacoes = (count($solicitacoesTmcsa));
            $somatoriaTmcsa = 0;
            $total_considerados_Tmcsa = 0;
            $tempo_resolucao_Tmcsa = 0;

            for ($i = 0; $i < $fimTmcsa; $i++) {
//                    Zend_Debug::dump($solicitacoesTmcsa[$i],'$solicitacoesTmcsa[$i]');
                /**
                 * Calcula o tempo util em segundos de acordo com o e-calendario 
                 */
                $tempo_resolucao_Tmcsa = $tempoSla->tempoTotalSLA($solicitacoesTmcsa[$i]["DATA_CHAMADO"], $solicitacoesTmcsa[$i]["DATA_FIM_CHAMADO"], '07:00:00', '20:00:00');
                /**
                 * Contabiliza se a solicitação não foi desconsiderada
                 */
                if (is_null($solicitacoesTmcsa[$i]['DESCONSIDERADO_TMCSA'])) {
                    $somatoriaTmcsa += $tempo_resolucao_Tmcsa;
                    $total_considerados_Tmcsa++;
                    $solicitacoesTmcsa[$i]['CONSIDERADO_TMCSA'] = 'S';
                }
                /**
                 * Mostra no gerador de SLA o tempo em dias do atendimento
                 */
                $solicitacoesTmcsa[$i]['TEMPO_ATENDIMENTO_DIAS'] = $tempoSla->FormataSaidaSegundos($tempo_resolucao_Tmcsa) . ' = ' . sprintf('%.2f', ($tempo_resolucao_Tmcsa / 60 / 60 / 13)) . ' dias úteis';
                /**
                 * Armazena o tempo em segundos para o cálculo do indicador  MAICPA
                 */
                $solicitacoesTmcsa[$i]['TEMPO_ATENDIMENTO_SEGUNDOS'] = $tempo_resolucao_Tmcsa;
            }

//            Zend_Debug::dump($quantitativo_de_segundos_uteis_em_segundos,'$quantitativo_de_segundos_uteis_em_segundos');
            /**
             * Convertendo para dias
             */
            $somatoriaTmcsa = $somatoriaTmcsa / (60 * 60 * 13);


            if ($totalTmcsaSolicitacoes > 0) {
                $totalTmcsa = ($somatoriaTmcsa / $total_considerados_Tmcsa);
            } else {
                $totalTmcsa = 0;
            }

            $totalTmcsa = (int) $totalTmcsa;
            /**
             * Tabela de glosa
             */
            if ($totalTmcsa <= 2) {
                $glosaTmcsa = 0;
            }
            if (($totalTmcsa <= 5) && ($totalTmcsa >= 3)) {
                $glosaTmcsa = 2;
            }
            if (($totalTmcsa <= 8) && ($totalTmcsa >= 6)) {
                $glosaTmcsa = 4;
            }
            if (($totalTmcsa <= 12) && ($totalTmcsa >= 9)) {
                $glosaTmcsa = 7;
            }
            if ($totalTmcsa > 12) {
                $glosaTmcsa = 7;
            }

            //total de solicitações
            $this->view->totalTmcsaSolicitacoes = $totalTmcsaSolicitacoes;
            //total de solicitações consideradas
            $this->view->total_considerados_Tmcsa = $total_considerados_Tmcsa;
            //total de solicitações não consideradas
            $this->view->total_desconsiderados_Tmcsa = $totalTmcsaSolicitacoes - $total_considerados_Tmcsa;
            //Tempo médio de atendimento em dias
            $this->view->tempoMedioTMCSA = $totalTmcsa;
            //id do indicador
            $this->view->idIndicadorTMCSA = $TMCSA_DADOS['SINS_ID_INDICADOR'];
            //lista de solicitações
            $this->view->solicitacoesTmcsa = $solicitacoesTmcsa;
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */

            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o MAICPA – Índice de Ausência de prazo 
             * 
             */
            /**
             *  Meta em segundos
             */
            $quantitativo_de_segundos_uteis_meta = 2 /* dias */ * 13 /* horas úteis */ * 60 /* minutos */ * 60 /* segundos */;


            $solicitacoesMaicpa = $solicitacoesTmcsa;
            $i = 0;
            $fimMaicpa = count($solicitacoesMaicpa);
            $totalMaicpaSolicitacoes = (count($solicitacoesMaicpa));
            $somatoriaMaicpa = 0;
            $total_considerados_Maicpa = 0;
            $tempo_resolucao_Maicpa = 0;

            for ($i = 0; $i < $fimMaicpa; $i++) {
                if (is_null($solicitacoesMaicpa[$i]['SSPA_DT_PRAZO'])) {

                    $atraso_em_segundos = $solicitacoesMaicpa[$i]['TEMPO_ATENDIMENTO_SEGUNDOS'] - $quantitativo_de_segundos_uteis_meta;

                    if (is_null($solicitacoesMaicpa[$i]['DESCONSIDERADO_MAICPA'])) {
                        $somatoriaMaicpa += $atraso_em_segundos;
                        $total_considerados_Maicpa++;
                        $solicitacoesMaicpa[$i]['CONSIDERADO_MAICPA'] = 'S';
                    }
                    /**
                     * Mostra no gerador de SLA o tempo em dias do atendimento
                     */
                    if ($atraso_em_segundos > 0) {
                        $solicitacoesMaicpa[$i]['TEMPO_ATRASO'] = sprintf('%.2f', ($atraso_em_segundos / 60 / 60 / 13));
                    } else {
                        $solicitacoesMaicpa[$i]['TEMPO_ATRASO'] = sprintf('%.2f', ($atraso_em_segundos / 60 / 60 / 13));
                    }
                    $solicitacoesMaicpa[$i]['ACORDADO'] = $tempoSla->FormataSaidaSegundos($quantitativo_de_segundos_uteis_meta);
                } else {
                    $prazo_acordado_em_segundos_uteis = $tempoSla->tempoTotalSLA($solicitacoesMaicpa[$i]["DATA_CHAMADO"], $solicitacoesMaicpa[$i]["SSPA_DT_PRAZO"], '07:00:00', '20:00:00');
                    /**
                     * Calcula o tempo util em segundos de acordo com o e-calendario 
                     */
                    $atraso_em_segundos = $solicitacoesMaicpa[$i]['TEMPO_ATENDIMENTO_SEGUNDOS'] - $prazo_acordado_em_segundos_uteis;
                    /**
                     * Contabiliza se a solicitação não foi desconsiderada
                     */
                    if (is_null($solicitacoesMaicpa[$i]['DESCONSIDERADO_MAICPA'])) {
                        $somatoriaMaicpa += $atraso_em_segundos;
                        $total_considerados_Maicpa++;
                        $solicitacoesMaicpa[$i]['CONSIDERADO_MAICPA'] = 'S';
                    }
                    /**
                     * Mostra no gerador de SLA o tempo em dias do atendimento
                     */
                    if ($atraso_em_segundos > 0) {
                        $solicitacoesMaicpa[$i]['TEMPO_ATRASO'] = sprintf('%.2f', ($atraso_em_segundos / 60 / 60 / 13));
                    } else {
                        $solicitacoesMaicpa[$i]['TEMPO_ATRASO'] = sprintf('%.2f', ($atraso_em_segundos / 60 / 60 / 13));
                    }
                    $solicitacoesMaicpa[$i]['ACORDADO'] = $tempoSla->FormataSaidaSegundos($prazo_acordado_em_segundos_uteis);
                }
            }

            /**
             * Convertendo para dias
             */
            $somatoriaMaicpa = $somatoriaMaicpa / (60 * 60 * 13);


            if ($totalMaicpaSolicitacoes > 0) {
                $totalMaicpa = ($somatoriaMaicpa / $total_considerados_Maicpa);
            } else {
                $totalMaicpa = 0;
            }

            $totalMaicpa = (int) $totalMaicpa;
            /**
             * Tabela de glosa
             */
            if ($totalMaicpa <= 2) {
                $glosaMaicpa = 0;
            }
            if (($totalMaicpa <= 5) && ($totalMaicpa >= 3)) {
                $glosaMaicpa = 2;
            }
            if (($totalMaicpa <= 8) && ($totalMaicpa >= 6)) {
                $glosaMaicpa = 4;
            }
            if (($totalMaicpa <= 12) && ($totalMaicpa >= 9)) {
                $glosaMaicpa = 7;
            }
            if ($totalMaicpa > 12) {
                $glosaMaicpa = 7;
            }



            //total de solicitações
            $this->view->totalMaicpaSolicitacoes = $totalMaicpaSolicitacoes;
            //total de solicitações consideradas
            $this->view->total_considerados_Maicpa = $total_considerados_Maicpa;
            //total de solicitações não consideradas
            $this->view->total_desconsiderados_Maicpa = $totalMaicpaSolicitacoes - $total_considerados_Maicpa;
            //Tempo médio de atendimento em dias
            $this->view->tempoMedioMAICPA = $totalMaicpa;
            //id do indicador
            $this->view->idIndicadorMAICPA = $MAICPA_DADOS['SINS_ID_INDICADOR'];
            //lista de solicitações
            $this->view->solicitacoesMaicpa = $solicitacoesMaicpa;
//            Zend_Debug::dump($solicitacoesMaicpa);
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */



            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o NVNR – Índice de Ausência de prazo 
             * 
             */
            /**
             *  Meta em segundos
             */
            $quantitativo_de_segundos_uteis_exigido_agendamento_nvnr = 1 /* dias */ * 13 /* horas úteis */ * 60 /* minutos */ * 60 /* segundos */;

            $i = 0;
            $fimNvnr = count($solicitacoesNvnr);
            $totalNvnrSolicitacoes = (count($solicitacoesNvnr));
            $videoconf_nao_realizadas = 0;

            for ($i = 0; $i < $fimNvnr; $i++) {
                $tempo_cadastro_inicio_video = $tempoSla->tempoTotalSLA($solicitacoesNvnr[$i]["DATA_CHAMADO"], $solicitacoesNvnr[$i]["SSES_DT_INICIO_VIDEO"], '07:00:00', '20:00:00');
                /**
                 * Contabiliza se a solicitação não foi desconsiderada
                 */
                if (is_null($solicitacoesNvnr[$i]['DESCONSIDERADO_NVNR'])) {
                    if (($tempo_cadastro_inicio_video >= $quantitativo_de_segundos_uteis_exigido_agendamento_nvnr) && ($solicitacoesNvnr[$i]["SSES_IC_VIDEO_REALIZADA"] == "N")) {
                        $videoconf_nao_realizadas++;
                    }
                    $solicitacoesNvnr[$i]['CONSIDERADO_NVNR'] = 'S';
                } else {
                    $solicitacoesNvnr[$i]['CONSIDERADO_NVNR'] = 'N';
                }
                $solicitacoesNvnr[$i]['TEMPO_AGENDAMENTO'] = $tempoSla->FormataSaidaSegundos($tempo_cadastro_inicio_video);
            }

            if ($totalNvnrSolicitacoes > 0) {
                $totalNvnr = $videoconf_nao_realizadas;
            } else {
                $totalNvnr = 0;
            }

            /**
             * Tabela de glosa
             */
            if ($totalNvnr == 0) {
                $glosaNvnr = 0;
            }
            if ($totalNvnr == 1) {
                $glosaNvnr = 1;
            }
            if ($totalNvnr == 2) {
                $glosaNvnr = 3;
            }
            if ($totalNvnr == 3) {
                $glosaNvnr = 5;
            }
            if ($totalNvnr > 3) {
                $glosaNvnr = 5;
            }

            //total de solicitações
            $this->view->totalNvnrSolicitacoes = $totalNvnrSolicitacoes;
            //total de solicitações consideradas
            $this->view->total_videos_realizadas_Nvnr = $totalNvnrSolicitacoes - $totalNvnr;
            //total de solicitações não consideradas
            $this->view->total_videos_nao_realizadas_Nvnr = $totalNvnr;
            //id do indicador
            $this->view->idIndicadorNVNR = $NVNR_DADOS['SINS_ID_INDICADOR'];
            //lista de solicitações
            $this->view->solicitacoesNvnr = $solicitacoesNvnr;
//            Zend_Debug::dump($solicitacoesNvnr);
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */


            /**
             * Array contendo a meta alcançada para todos os índices
             */
            $meta[0] = '--';
            ; //Tempo médio entre o aparecimento de um problema e sua comunicação 
            $meta[1] = $totalTma . '(min)'; //Tempo médio para atendimento às solicitações por parte da equipe de monitoria 1º nível
            $meta[2] = $totalTmcsa . '(dias)'; //Tempo médio para cadastramento de novos serviços ou ativos
            $meta[3] = $totalMaicpa . '(dias)'; //Média de dias de atraso injustificado no cumprimento dos prazos acordadoss
            $meta[4] = $totalNvnr; //Número de Videoconferências não realizadas com agendamento prévio de 1 dia

            /**
             * Array contendo o valor a ser glosado para todos os índices
             */
            $glosa[0] = '--';
            $glosa[1] = $glosaTma . '%';
            $glosa[2] = $glosaTmcsa . '%';
            $glosa[3] = $glosaMaicpa . '%';
            $glosa[4] = $glosaNvnr . '%';

            /**
             * Inclui a posição da meta alcançada no array dos indicadores mínimos
             */
            $indicadoresMinimos = $indicadorNivelServ->getIndicNivelServicoGrupo(4, '');
            $fim = count($indicadoresMinimos);
            for ($i = 0; $i < $fim; $i++) {
                $indicadoresMinimos[$i]['META_ALCANCADA'] = $meta[$i];
                $indicadoresMinimos[$i]['GLOSA'] = $glosa[$i];
            }
            $this->view->indicadoresMinimos = $indicadoresMinimos;
            $this->render('indicadoresnivelservico');
            $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
            $indMin->data = $indicadoresMinimos;
            $indMin->title = "SLA - NOC E ESCRITÓRIO DE PROJETOS - TRF1";
            $indMin->periodo = 'PERÍODO: ' . $Sla_Noc_ns->data['DATA_INICIAL'] . ' À ' . $Sla_Noc_ns->data['DATA_FINAL'];
        }
    }

    public function indicadorescompedidoinformacaoAction() {
        set_time_limit(1200); //10 minutos para gerar o relatório

        $userNs = new Zend_Session_Namespace('userNs');
        $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $tempoSla = new App_Sosti_TempoSla();
        $form = new Sosti_Form_Sla();
        $form->removeElement('OPCAO');
        $form->removeElement('SGRS_ID_GRUPO');
        $form->setAction('triagem');
        $this->view->form = $form;
        $this->view->title = "SLA - NOC";



        $Sla_Noc_ns = new Zend_Session_Namespace('Sla_Noc_ns');

        if ($Sla_Noc_ns->data != '') {
            $form->populate($Sla_Noc_ns->data);
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $Sla_Noc_ns = new Zend_Session_Namespace('Sla_Noc_ns');
                $Sla_Noc_ns->data = $data;
            }
        }
        if ($Sla_Noc_ns->data != '') {
            $this->view->data = $Sla_Noc_ns->data;
            $form->populate($Sla_Noc_ns->data);

            /**
             * Permissão de desconsiderar SLA 
             */
            $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
            $possui_permissao_fechamento = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('GESTOR DO CONTRATO DO NOC', $userNs->matricula);
            $this->view->possui_permissao_fechamento = $possui_permissao_fechamento;

//            $TMC_DADOS  = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'TMC');
            $TMA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'TMA');
            $TMCSA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'TMCSA');
            $MAICPA_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'MAICPA');
            $NVNR_DADOS = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(4, 'NVNR');


            $solicitacoesTma = $indicadorNivelServ->getDatasSLA_TMA(4, $Sla_Noc_ns->data['DATA_INICIAL'], $Sla_Noc_ns->data['DATA_FINAL'], $TMA_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesTmcsa = $indicadorNivelServ->getDatasSLA_TMCSA(4, $Sla_Noc_ns->data['DATA_INICIAL'], $Sla_Noc_ns->data['DATA_FINAL'], $TMCSA_DADOS['SINS_ID_INDICADOR'], $MAICPA_DADOS['SINS_ID_INDICADOR']);
            $solicitacoesNvnr = $indicadorNivelServ->getDatasSLA_NVNR(4, $Sla_Noc_ns->data['DATA_INICIAL'], $Sla_Noc_ns->data['DATA_FINAL'], $NVNR_DADOS['SINS_ID_INDICADOR']);


            /**
             * Configurações do horário de expediente
             */
            $SosTbGrexGrupoServExped = new Application_Model_DbTable_SosTbGrexGrupoServExped();
            $expedienteNormal = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(4, "SLA");
            $expedienteEmergencia = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(2, "EMERGENCIAL");

            $expediente = array('NORMAL' => array('INICIO' => $expedienteNormal["INICIO"], 'FIM' => $expedienteNormal["FIM"]), 'EMERGENCIAL' => array('INICIO' => $expedienteEmergencia['INICIO'], 'FIM' => $expedienteEmergencia['FIM']));
            $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos($expediente["NORMAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["NORMAL"]["INICIO"]);
            $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos($expediente["EMERGENCIAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["EMERGENCIAL"]["INICIO"]);
            $expediente["NORMAL"]["DIA_UTIL_HORAS"] = $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;
            $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"] = $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;

            /**
             * Calcula o tempo total das solicitações não contabilizando o tempo em que a solicitação ficou aguardando a resposta do pedido de informação.
             */
            $TempoTotalPedidoInforArrTMCSA = $tempoSla->TempoTotalPedidoInfor($solicitacoesTmcsa, 'MOFA_ID_MOVIMENTACAO', "DATA_CHAMADO", "DATA_FIM_CHAMADO", "", "", $expediente);
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Calcular o TMA – Tempo médio de atendimento pela monitoria <= 10 minutos
             */
            $i = 0;
            $totalTmaSolicitacoes = (count($solicitacoesTma));
            $somatoriaTma = 0;
            $total_parcial_Tma = 0;
            $tempo_ated_Tma = 0;
            foreach ($solicitacoesTma as $tma) {

                $tempo_ated_Tma = $tempoSla->tempoTotalSLA($tma["DATA_CHAMADO"], $tma["DATA_PRIMEIRO_ATENDIMENTO"], '07:00:00', '20:00:00');
                $solicitacoesTma[$i]['TEMPO_ATENDIMENTO_MINUTOS'] = sprintf('%.2f', $tempo_ated_Tma / 60);

                if (is_null($tma['DESCONSIDERADO_TMA'])) {
                    $somatoriaTma += $tempo_ated_Tma;
                    $solicitacoesTma[$i]['CONSIDERADO_TMA'] = 'S';
                    $total_parcial_Tma++;
                }

                $i++;
            }
            $somatoriaTma = $somatoriaTma / 60;

            if ($totalTmaSolicitacoes > 0) {
                $totalTma = ($somatoriaTma / $total_parcial_Tma);
            } else {
                $totalTma = 0;
            }

            $totalTma = (int) $totalTma;

            if ($totalTma <= 10) {
                $glosaTma = 0;
            }
            if (($totalTma <= 20) && ($totalTma >= 11)) {
                $glosaTma = 2;
            }
            if (($totalTma <= 40) && ($totalTma >= 21)) {
                $glosaTma = 4;
            }
            if (($totalTma <= 60) && ($totalTma >= 41)) {
                $glosaTma = 7;
            }
            if ($totalTma > 60) {
                $glosaTma = 7;
            }
            /**
             * Carrega as variáveis de sessão do indicador:
             * Tempo médio para atendimento às solicitações por parte da equipe de monitoria
             */
            $Sla_Noc_Tma_ns = new Zend_Session_Namespace('Sla_Noc_Tma_ns');
            $Sla_Noc_Tma_ns->totalTmaSolicitacoes = $totalTmaSolicitacoes;
            $Sla_Noc_Tma_ns->totalParcial = $total_parcial_Tma;
            $Sla_Noc_Tma_ns->totalDesconsideradas = $totalTmaSolicitacoes - $total_parcial_Tma;
            $Sla_Noc_Tma_ns->tempoMedioTMA = $totalTma;
            $Sla_Noc_Tma_ns->idIndicadorTMA = $TMA_DADOS['SINS_ID_INDICADOR'];
            $Sla_Noc_Tma_ns->solicitacoesTma = $solicitacoesTma;
            
            $this->view->totalTmaSolicitacoes = $totalTmaSolicitacoes;
            $this->view->totalParcial = $total_parcial_Tma;
            $this->view->totalDesconsideradas = $totalTmaSolicitacoes - $total_parcial_Tma;
            $this->view->tempoMedioTMA = $totalTma;
            $this->view->idIndicadorTMA = $TMA_DADOS['SINS_ID_INDICADOR'];
            //solicitações
            $this->view->solicitacoesTma = $solicitacoesTma;
//            Zend_Debug::dump($solicitacoesTma);
            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */


            /*             * *********************************************************************************** */
            /*             * *********************************************************************************** */
            /**
             * Tempo médio para cadastramento de novos serviços ou ativos
             */
            $i = 0;
            $fimTmcsa = count($solicitacoesTmcsa);
            $totalTmcsaSolicitacoes = (count($solicitacoesTmcsa));
            $somatoriaTmcsa = 0;
            $total_considerados_Tmcsa = 0;
            $tempo_resolucao_Tmcsa = 0;

            for ($i = 0; $i < $fimTmcsa; $i++) {
//                    Zend_Debug::dump($solicitacoesTmcsa[$i],'$solicitacoesTmcsa[$i]');
                /**
                 * Calcula o tempo util em segundos de acordo com o e-calendario 
                 */
//                    $tempo_resolucao_Tmcsa = $tempoSla->tempoTotalSLA($solicitacoesTmcsa[$i]["DATA_CHAMADO"], $solicitacoesTmcsa[$i]["DATA_FIM_CHAMADO"], '07:00:00', '20:00:00');
                $idMovimentacao = $solicitacoesTmcsa[$i]["MOFA_ID_MOVIMENTACAO"];
                $tempo_resolucao_Tmcsa = $TempoTotalPedidoInforArrTMCSA[$idMovimentacao]["TEMPO_UTIL_TOTAL"];
                /**
                 * Contabiliza se a solicitação não foi desconsiderada
                 */
                if (is_null($solicitacoesTmcsa[$i]['DESCONSIDERADO_TMCSA'])) {
                    $somatoriaTmcsa += $tempo_resolucao_Tmcsa;
                    $total_considerados_Tmcsa++;
                    $solicitacoesTmcsa[$i]['CONSIDERADO_TMCSA'] = 'S';
                }
                /**
                 * Mostra no gerador de SLA o tempo em dias do atendimento
                 */
                $solicitacoesTmcsa[$i]['TEMPO_ATENDIMENTO_DIAS'] = $tempoSla->FormataSaidaSegundos($tempo_resolucao_Tmcsa) . ' = ' . sprintf('%.2f', ($tempo_resolucao_Tmcsa / 60 / 60 / 13)) . ' dias úteis';
                /**
                 * Armazena o tempo em segundos para o cálculo do indicador  MAICPA
                 */
                $solicitacoesTmcsa[$i]['TEMPO_ATENDIMENTO_SEGUNDOS'] = $tempo_resolucao_Tmcsa;
            }

//            Zend_Debug::dump($quantitativo_de_segundos_uteis_em_segundos,'$quantitativo_de_segundos_uteis_em_segundos');
            /**
             * Convertendo para dias
             */
            $somatoriaTmcsa = $somatoriaTmcsa / (60 * 60 * 13);


            if ($totalTmcsaSolicitacoes > 0) {
                $totalTmcsa = ($somatoriaTmcsa / $total_considerados_Tmcsa);
            } else {
                $totalTmcsa = 0;
            }

            $totalTmcsa = (int) $totalTmcsa;
            /**
             * Tabela de glosa
             */
            if ($totalTmcsa <= 2) {
                $glosaTmcsa = 0;
            }
            if (($totalTmcsa <= 5) && ($totalTmcsa >= 3)) {
                $glosaTmcsa = 2;
            }
            if (($totalTmcsa <= 8) && ($totalTmcsa >= 6)) {
                $glosaTmcsa = 4;
            }
            if (($totalTmcsa <= 12) && ($totalTmcsa >= 9)) {
                $glosaTmcsa = 7;
            }
            if ($totalTmcsa > 12) {
                $glosaTmcsa = 7;
            }

            //total de solicitações
            $this->view->totalTmcsaSolicitacoes = $totalTmcsaSolicitacoes;
            //total de solicitações consideradas
            $this->view->total_considerados_Tmcsa = $total_considerados_Tmcsa;
            //total de solicitações não consideradas
            $this->view->total_desconsiderados_Tmcsa = $totalTmcsaSolicitacoes - $total_considerados_Tmcsa;
            //Tempo médio de atendimento em dias
            $this->view->tempoMedioTMCSA = $totalTmcsa;
            //id do indicador
            $this->view->idIndicadorTMCSA = $TMCSA_DADOS['SINS_ID_INDICADOR'];
            //lista de solicitações
            $this->view->solicitacoesTmcsa = $solicitacoesTmcsa;
            /**
             * Carrega as variáveis de sessão do indicador:
             * Tempo médio para cadastramento de novos serviços ou ativos 
             */
            $Sla_Noc_Tmcsa_ns = new Zend_Session_Namespace('Sla_Noc_Tmcsa_ns');
            $Sla_Noc_Tmcsa_ns->totalTmcsaSolicitacoes = $totalTmcsaSolicitacoes;
            $Sla_Noc_Tmcsa_ns->total_considerados_Tmcsa = $total_considerados_Tmcsa;
            $Sla_Noc_Tmcsa_ns->total_desconsiderados_Tmcsa = $totalTmcsaSolicitacoes - $total_considerados_Tmcsa;
            $Sla_Noc_Tmcsa_ns->tempoMedioTMCSA = $totalTmcsa;
            $Sla_Noc_Tmcsa_ns->idIndicadorTMCSA = $TMCSA_DADOS['SINS_ID_INDICADOR'];
            $Sla_Noc_Tmcsa_ns->solicitacoesTmcsa = $solicitacoesTmcsa;
            /**
             * Calcular o MAICPA – Índice de Ausência de prazo 
             * 
             */
            /**
             *  Meta em segundos
             */
            $quantitativo_de_segundos_uteis_meta = 2 /* dias */ * 13 /* horas úteis */ * 60 /* minutos */ * 60 /* segundos */;


            $solicitacoesMaicpa = $solicitacoesTmcsa;
            $i = 0;
            $fimMaicpa = count($solicitacoesMaicpa);
            $totalMaicpaSolicitacoes = (count($solicitacoesMaicpa));
            $somatoriaMaicpa = 0;
            $total_considerados_Maicpa = 0;
            $tempo_resolucao_Maicpa = 0;

            for ($i = 0; $i < $fimMaicpa; $i++) {
                if (is_null($solicitacoesMaicpa[$i]['SSPA_DT_PRAZO'])) {

                    $atraso_em_segundos = $solicitacoesMaicpa[$i]['TEMPO_ATENDIMENTO_SEGUNDOS'] - $quantitativo_de_segundos_uteis_meta;

                    if (is_null($solicitacoesMaicpa[$i]['DESCONSIDERADO_MAICPA'])) {
                        $somatoriaMaicpa += $atraso_em_segundos;
                        $total_considerados_Maicpa++;
                        $solicitacoesMaicpa[$i]['CONSIDERADO_MAICPA'] = 'S';
                    }
                    /**
                     * Mostra no gerador de SLA o tempo em dias do atendimento
                     */
                    if ($atraso_em_segundos > 0) {
                        $solicitacoesMaicpa[$i]['TEMPO_ATRASO'] = sprintf('%.2f', ($atraso_em_segundos / 60 / 60 / 13));
                    } else {
                        $solicitacoesMaicpa[$i]['TEMPO_ATRASO'] = sprintf('%.2f', ($atraso_em_segundos / 60 / 60 / 13));
                    }
                    $solicitacoesMaicpa[$i]['ACORDADO'] = $tempoSla->FormataSaidaSegundos($quantitativo_de_segundos_uteis_meta);
                } else {
                    $prazo_acordado_em_segundos_uteis = $tempoSla->tempoTotalSLA($solicitacoesMaicpa[$i]["DATA_CHAMADO"], $solicitacoesMaicpa[$i]["SSPA_DT_PRAZO"], '07:00:00', '20:00:00');
                    /**
                     * Calcula o tempo util em segundos de acordo com o e-calendario 
                     */
                    $atraso_em_segundos = $solicitacoesMaicpa[$i]['TEMPO_ATENDIMENTO_SEGUNDOS'] - $prazo_acordado_em_segundos_uteis;
                    /**
                     * Contabiliza se a solicitação não foi desconsiderada
                     */
                    if (is_null($solicitacoesMaicpa[$i]['DESCONSIDERADO_MAICPA'])) {
                        $somatoriaMaicpa += $atraso_em_segundos;
                        $total_considerados_Maicpa++;
                        $solicitacoesMaicpa[$i]['CONSIDERADO_MAICPA'] = 'S';
                    }
                    /**
                     * Mostra no gerador de SLA o tempo em dias do atendimento
                     */
                    if ($atraso_em_segundos > 0) {
                        $solicitacoesMaicpa[$i]['TEMPO_ATRASO'] = sprintf('%.2f', ($atraso_em_segundos / 60 / 60 / 13));
                    } else {
                        $solicitacoesMaicpa[$i]['TEMPO_ATRASO'] = sprintf('%.2f', ($atraso_em_segundos / 60 / 60 / 13));
                    }
                    $solicitacoesMaicpa[$i]['ACORDADO'] = $tempoSla->FormataSaidaSegundos($prazo_acordado_em_segundos_uteis);
                }
            }

            /**
             * Convertendo para dias
             */
            $somatoriaMaicpa = $somatoriaMaicpa / (60 * 60 * 13);


            if ($totalMaicpaSolicitacoes > 0) {
                $totalMaicpa = ($somatoriaMaicpa / $total_considerados_Maicpa);
            } else {
                $totalMaicpa = 0;
            }

            $totalMaicpa = (int) $totalMaicpa;
            /**
             * Tabela de glosa
             */
            if ($totalMaicpa <= 2) {
                $glosaMaicpa = 0;
            }
            if (($totalMaicpa <= 5) && ($totalMaicpa >= 3)) {
                $glosaMaicpa = 2;
            }
            if (($totalMaicpa <= 8) && ($totalMaicpa >= 6)) {
                $glosaMaicpa = 4;
            }
            if (($totalMaicpa <= 12) && ($totalMaicpa >= 9)) {
                $glosaMaicpa = 7;
            }
            if ($totalMaicpa > 12) {
                $glosaMaicpa = 7;
            }

            //total de solicitações
            $this->view->totalMaicpaSolicitacoes = $totalMaicpaSolicitacoes;
            //total de solicitações consideradas
            $this->view->total_considerados_Maicpa = $total_considerados_Maicpa;
            //total de solicitações não consideradas
            $this->view->total_desconsiderados_Maicpa = $totalMaicpaSolicitacoes - $total_considerados_Maicpa;
            //Tempo médio de atendimento em dias
            $this->view->tempoMedioMAICPA = $totalMaicpa;
            //id do indicador
            $this->view->idIndicadorMAICPA = $MAICPA_DADOS['SINS_ID_INDICADOR'];
            //lista de solicitações
            $this->view->solicitacoesMaicpa = $solicitacoesMaicpa;
            /**
             * Carrega as variáveis de sessão do indicador:
             * Média de dias de atraso injustificado no cumprimento dos prazos acordados
             */
            $Sla_Noc_Maicpa_ns = new Zend_Session_Namespace('Sla_Noc_Maicpa_ns');
            $Sla_Noc_Maicpa_ns->totalMaicpaSolicitacoes = $totalMaicpaSolicitacoes;
            $Sla_Noc_Maicpa_ns->total_considerados_Maicpa = $total_considerados_Maicpa;
            $Sla_Noc_Maicpa_ns->total_desconsiderados_Maicpa = $totalMaicpaSolicitacoes - $total_considerados_Maicpa;
            $Sla_Noc_Maicpa_ns->tempoMedioMAICPA = $totalMaicpa;
            $Sla_Noc_Maicpa_ns->idIndicadorMAICPA = $MAICPA_DADOS['SINS_ID_INDICADOR'];
            $Sla_Noc_Maicpa_ns->solicitacoesMaicpa = $solicitacoesMaicpa;

            /**
             * Calcular o NVNR – Índice de Ausência de prazo 
             * 
             */
            /**
             *  Meta em segundos
             */
            $quantitativo_de_segundos_uteis_exigido_agendamento_nvnr = 1 /* dias */ * 13 /* horas úteis */ * 60 /* minutos */ * 60 /* segundos */;

            $i = 0;
            $fimNvnr = count($solicitacoesNvnr);
            $totalNvnrSolicitacoes = (count($solicitacoesNvnr));
            $videoconf_nao_realizadas = 0;

            for ($i = 0; $i < $fimNvnr; $i++) {
                $tempo_cadastro_inicio_video = $tempoSla->tempoTotalSLA($solicitacoesNvnr[$i]["DATA_CHAMADO"], $solicitacoesNvnr[$i]["SSES_DT_INICIO_VIDEO"], '07:00:00', '20:00:00');
                /**
                 * Contabiliza se a solicitação não foi desconsiderada
                 */
                if (is_null($solicitacoesNvnr[$i]['DESCONSIDERADO_NVNR'])) {
                    if (($tempo_cadastro_inicio_video >= $quantitativo_de_segundos_uteis_exigido_agendamento_nvnr) && ($solicitacoesNvnr[$i]["SSES_IC_VIDEO_REALIZADA"] == "N")) {
                        $videoconf_nao_realizadas++;
                    }
                    $solicitacoesNvnr[$i]['CONSIDERADO_NVNR'] = 'S';
                } else {
                    $solicitacoesNvnr[$i]['CONSIDERADO_NVNR'] = 'N';
                }
                $solicitacoesNvnr[$i]['TEMPO_AGENDAMENTO'] = $tempoSla->FormataSaidaSegundos($tempo_cadastro_inicio_video);
            }

            if ($totalNvnrSolicitacoes > 0) {
                $totalNvnr = $videoconf_nao_realizadas;
            } else {
                $totalNvnr = 0;
            }

            /**
             * Tabela de glosa
             */
            if ($totalNvnr == 0) {
                $glosaNvnr = 0;
            }
            if ($totalNvnr == 1) {
                $glosaNvnr = 1;
            }
            if ($totalNvnr == 2) {
                $glosaNvnr = 3;
            }
            if ($totalNvnr == 3) {
                $glosaNvnr = 5;
            }
            if ($totalNvnr > 3) {
                $glosaNvnr = 5;
            }

            //total de solicitações
            $this->view->totalNvnrSolicitacoes = $totalNvnrSolicitacoes;
            //total de solicitações consideradas
            $this->view->total_videos_realizadas_Nvnr = $totalNvnrSolicitacoes - $totalNvnr;
            //total de solicitações não consideradas
            $this->view->total_videos_nao_realizadas_Nvnr = $totalNvnr;
            //id do indicador
            $this->view->idIndicadorNVNR = $NVNR_DADOS['SINS_ID_INDICADOR'];
            //lista de solicitações
            $this->view->solicitacoesNvnr = $solicitacoesNvnr;
            /**
             * Carrega as variáveis de sessão do indicador:
             * Número de Videoconferências não realizadas com agendamento prévio de 1 dia
             */
            $Sla_Noc_Nvnr_ns = new Zend_Session_Namespace('Sla_Noc_Nvnr_ns');
            $Sla_Noc_Nvnr_ns->totalNvnrSolicitacoes = $totalNvnrSolicitacoes;
            $Sla_Noc_Nvnr_ns->total_videos_realizadas_Nvnr = $totalNvnrSolicitacoes - $totalNvnr;
            $Sla_Noc_Nvnr_ns->total_videos_nao_realizadas_Nvnr = $totalNvnr;
            $Sla_Noc_Nvnr_ns->idIndicadorNVNR = $NVNR_DADOS['SINS_ID_INDICADOR'];
            $Sla_Noc_Nvnr_ns->solicitacoesNvnr = $solicitacoesNvnr;

            /**
             * Array contendo a meta alcançada para todos os índices
             */
            $meta[0] = '--';
            ; //Tempo médio entre o aparecimento de um problema e sua comunicação 
            $meta[1] = $totalTma . '(min)'; //Tempo médio para atendimento às solicitações por parte da equipe de monitoria 1º nível
            $meta[2] = $totalTmcsa . '(dias)'; //Tempo médio para cadastramento de novos serviços ou ativos
            $meta[3] = $totalMaicpa . '(dias)'; //Média de dias de atraso injustificado no cumprimento dos prazos acordadoss
            $meta[4] = $totalNvnr; //Número de Videoconferências não realizadas com agendamento prévio de 1 dia

            /**
             * Array contendo o valor a ser glosado para todos os índices
             */
            $glosa[0] = '--';
            $glosa[1] = $glosaTma . '%';
            $glosa[2] = $glosaTmcsa . '%';
            $glosa[3] = $glosaMaicpa . '%';
            $glosa[4] = $glosaNvnr . '%';

            /**
             * Inclui a posição da meta alcançada no array dos indicadores mínimos
             */
            $indicadoresMinimos = $indicadorNivelServ->getIndicNivelServicoGrupo(4, '');
            $fim = count($indicadoresMinimos);
            for ($i = 0; $i < $fim; $i++) {
                $indicadoresMinimos[$i]['META_ALCANCADA'] = $meta[$i];
                $indicadoresMinimos[$i]['GLOSA'] = $glosa[$i];
            }
            $this->view->indicadoresMinimos = $indicadoresMinimos;
            $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
            $indMin->data = $indicadoresMinimos;
            $indMin->title = "SLA - NOC E ESCRITÓRIO DE PROJETOS - TRF1";
            $indMin->periodo = 'PERÍODO: ' . $Sla_Noc_ns->data['DATA_INICIAL'] . ' À ' . $Sla_Noc_ns->data['DATA_FINAL'];
            $this->render('indicadoresnivelservico');
        }
    }
    
    public function indicadoresnivelservicopdfAction() 
    {
        set_time_limit( 1200 );
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
         * Tempo médio para atendimento às solicitações por parte da equipe de monitoria
         */
        $Sla_Noc_Tma_ns = new Zend_Session_Namespace('Sla_Noc_Tma_ns');
        $this->view->solicitacoesTma = $Sla_Noc_Tma_ns->solicitacoesTma;
        $this->view->totalTmaSolicitacoes = $Sla_Noc_Tma_ns->totalTmaSolicitacoes;
        $this->view->totalParcial = $Sla_Noc_Tma_ns->totalParcial;
        $this->view->totalDesconsideradas = $Sla_Noc_Tma_ns->totalDesconsideradas;
        $this->view->tempoMedioTMA = $Sla_Noc_Tma_ns->tempoMedioTMA;
        $this->view->idIndicadorTMA = $Sla_Noc_Tma_ns->idIndicadorTMA;
        /**
         * Carrega as variáveis de sessão do indicador:
         * Tempo médio para cadastramento de novos serviços ou ativos 
         */
        $Sla_Noc_Tmcsa_ns = new Zend_Session_Namespace('Sla_Noc_Tmcsa_ns');
        $this->view->totalTmcsaSolicitacoes = $Sla_Noc_Tmcsa_ns->totalTmcsaSolicitacoes;
        $this->view->total_considerados_Tmcsa = $Sla_Noc_Tmcsa_ns->total_considerados_Tmcsa;
        $this->view->total_desconsiderados_Tmcsa = $Sla_Noc_Tmcsa_ns->total_desconsiderados_Tmcsa;
        $this->view->tempoMedioTMCSA = $Sla_Noc_Tmcsa_ns->tempoMedioTMCSA;
        $this->view->idIndicadorTMCSA = $Sla_Noc_Tmcsa_ns->idIndicadorTMCSA;
        $this->view->solicitacoesTmcsa = $Sla_Noc_Tmcsa_ns->solicitacoesTmcsa;
        /**
         * Carrega as variáveis de sessão do indicador:
         * Média de dias de atraso injustificado no cumprimento dos prazos acordados
         */
        $Sla_Noc_Maicpa_ns = new Zend_Session_Namespace('Sla_Noc_Maicpa_ns');
        $this->view->totalMaicpaSolicitacoes = $Sla_Noc_Maicpa_ns->totalMaicpaSolicitacoes;
        $this->view->total_considerados_Maicpa = $Sla_Noc_Maicpa_ns->total_considerados_Maicpa;
        $this->view->total_desconsiderados_Maicpa = $Sla_Noc_Maicpa_ns->total_desconsiderados_Maicpa;
        $this->view->tempoMedioMAICPA = $Sla_Noc_Maicpa_ns->tempoMedioMAICPA;
        $this->view->idIndicadorMAICPA = $Sla_Noc_Maicpa_ns->idIndicadorMAICPA;
        $this->view->solicitacoesMaicpa = $Sla_Noc_Maicpa_ns->solicitacoesMaicpa;
        /**
         * Carrega as variáveis de sessão do indicador:
         * Número de Videoconferências não realizadas com agendamento prévio de 1 dia
         */
        $Sla_Noc_Nvnr_ns = new Zend_Session_Namespace('Sla_Noc_Nvnr_ns');
        $this->view->totalNvnrSolicitacoes = $Sla_Noc_Nvnr_ns->totalNvnrSolicitacoes;
        $this->view->total_videos_realizadas_Nvnr = $Sla_Noc_Nvnr_ns->total_videos_realizadas_Nvnr;
        $this->view->total_videos_nao_realizadas_Nvnr = $Sla_Noc_Nvnr_ns->total_videos_nao_realizadas_Nvnr;
        $this->view->idIndicadorNVNR = $Sla_Noc_Nvnr_ns->idIndicadorNVNR;
        $this->view->solicitacoesNvnr = $Sla_Noc_Nvnr_ns->solicitacoesNvnr;
        
        if ($indMin->fuso != "") {
            $this->view->fuso = $indMin->fuso;
            $this->view->secao = $indMin->secao;
        }
        
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();

        $this->_helper->layout->disableLayout();
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
        
        $name =  'Resultado_SLA.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function indicadoresnivelservicoexcelAction()
    {
        set_time_limit( 1200 );
        $this->_helper->layout->disableLayout(); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=extraction.xls"); 
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $this->view->slaIndices = $indMin->data;
        $this->view->titulo = $indMin->title;
        $this->view->periodo = $indMin->periodo;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->view->emissor = strtoupper($userNamespace->matricula).' - '.$userNamespace->nome;
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
         * Tempo médio para atendimento às solicitações por parte da equipe de monitoria
         */
        $Sla_Noc_Tma_ns = new Zend_Session_Namespace('Sla_Noc_Tma_ns');
        $this->view->solicitacoesTma = $Sla_Noc_Tma_ns->solicitacoesTma;
        $this->view->totalTmaSolicitacoes = $Sla_Noc_Tma_ns->totalTmaSolicitacoes;
        $this->view->totalParcial = $Sla_Noc_Tma_ns->totalParcial;
        $this->view->totalDesconsideradas = $Sla_Noc_Tma_ns->totalDesconsideradas;
        $this->view->tempoMedioTMA = $Sla_Noc_Tma_ns->tempoMedioTMA;
        $this->view->idIndicadorTMA = $Sla_Noc_Tma_ns->idIndicadorTMA;
        /**
         * Carrega as variáveis de sessão do indicador:
         * Tempo médio para cadastramento de novos serviços ou ativos 
         */
        $Sla_Noc_Tmcsa_ns = new Zend_Session_Namespace('Sla_Noc_Tmcsa_ns');
        $this->view->totalTmcsaSolicitacoes = $Sla_Noc_Tmcsa_ns->totalTmcsaSolicitacoes;
        $this->view->total_considerados_Tmcsa = $Sla_Noc_Tmcsa_ns->total_considerados_Tmcsa;
        $this->view->total_desconsiderados_Tmcsa = $Sla_Noc_Tmcsa_ns->total_desconsiderados_Tmcsa;
        $this->view->tempoMedioTMCSA = $Sla_Noc_Tmcsa_ns->tempoMedioTMCSA;
        $this->view->idIndicadorTMCSA = $Sla_Noc_Tmcsa_ns->idIndicadorTMCSA;
        $this->view->solicitacoesTmcsa = $Sla_Noc_Tmcsa_ns->solicitacoesTmcsa;
        /**
         * Carrega as variáveis de sessão do indicador:
         * Média de dias de atraso injustificado no cumprimento dos prazos acordados
         */
        $Sla_Noc_Maicpa_ns = new Zend_Session_Namespace('Sla_Noc_Maicpa_ns');
        $this->view->totalMaicpaSolicitacoes = $Sla_Noc_Maicpa_ns->totalMaicpaSolicitacoes;
        $this->view->total_considerados_Maicpa = $Sla_Noc_Maicpa_ns->total_considerados_Maicpa;
        $this->view->total_desconsiderados_Maicpa = $Sla_Noc_Maicpa_ns->total_desconsiderados_Maicpa;
        $this->view->tempoMedioMAICPA = $Sla_Noc_Maicpa_ns->tempoMedioMAICPA;
        $this->view->idIndicadorMAICPA = $Sla_Noc_Maicpa_ns->idIndicadorMAICPA;
        $this->view->solicitacoesMaicpa = $Sla_Noc_Maicpa_ns->solicitacoesMaicpa;
        /**
         * Carrega as variáveis de sessão do indicador:
         * Número de Videoconferências não realizadas com agendamento prévio de 1 dia
         */
        $Sla_Noc_Nvnr_ns = new Zend_Session_Namespace('Sla_Noc_Nvnr_ns');
        $this->view->totalNvnrSolicitacoes = $Sla_Noc_Nvnr_ns->totalNvnrSolicitacoes;
        $this->view->total_videos_realizadas_Nvnr = $Sla_Noc_Nvnr_ns->total_videos_realizadas_Nvnr;
        $this->view->total_videos_nao_realizadas_Nvnr = $Sla_Noc_Nvnr_ns->total_videos_nao_realizadas_Nvnr;
        $this->view->idIndicadorNVNR = $Sla_Noc_Nvnr_ns->idIndicadorNVNR;
        
        $this->view->solicitacoesNvnr = $Sla_Noc_Nvnr_ns->solicitacoesNvnr;
        if ($indMin->fuso != "") {
            $this->view->fuso = $indMin->fuso;
            $this->view->secao = $indMin->secao;
        }
    }

}
