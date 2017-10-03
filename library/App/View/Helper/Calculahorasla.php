<?php

class App_View_Helper_Calculahorasla extends Zend_View_Helper_Abstract
{

    protected
        /*
         * Define os prazos de cada categoria de solicitação
         * */
        $_prazo = array(
        'normal' => array(
            726 => array(3, 4, 5, 7, 8),
            242 => array(1, 6)
        ),
        'emergencial' => array(
            858 => array(3, 4, 5),
            286 => array(6)
        ),
        'corretiva' => array(
            6 => array(1),
            12 => array(2),
            24 => array(3)
        )
    ),
        /*
         * Define os horaros de inicio e fim assim como as horas uteis dos tipos de expediente
         * */
        $_expediente = array(
        'normal' => array(
            'inicio' => 8,
            'fim' => 19,
            'horas' => 11
        ),
        'emergencial' => array(
            'inicio' => 7,
            'fim' => 20,
            'horas' => 13
        )
    ),
        $_ssolIdDocumento,
        $_docmNrDocumento,
//        $_docmDhCadastro,
//        $_nomeUsarioCadastro,
//        $_mofaIdMovimentacao,
//        $_mofaDhFase,
//        $_mofaIdFase,
//        $_dataAtual,
        $_moviDhEncaminhamento,
//        $_tempoTotal,
//        $_modeIdCaixaEntrada,
//        $_modeSgSecaoUnidDestino,
//        $_modeCdSecaoUnidDestino,
        $_movimentacao,
//        $_sserIdServico,
//        $_sserDsServico,
//        $_sespDhLimiteEsp,
//        $_esperaFlag,
//        $_vinculada,
        $_sspaDtPrazo,
//        $_osisNmOcorrencia,
//        $_ctssNmCategoriaServico,
        $_ctssIdCategoriaServico,
        $_assoIcAtendimentoEmergencia,
//        $_assoIcSolucaoProblema,
//        $_assoIcSolucaoCausaProblema,
        $_asisIcNivelCriticidade,
//        $_asisPrzInicioAtendimento,
//        $_asisPrzSolProblema,
//        $_asisPrzSolCausaProblema,
//        $_asisPrzExecucaoServico,
        $_corretiva,
        $_dataFimChamado;

//1,1,"NOVOS SISTEMAS DE INFORMAÇÃO"
//2,2,"MANUTENÇÃO CORRETIVA"
//3,2,"MANUTENÇÃO ADAPTATIVA"
//4,2,"MANUTENÇÃO EVOLUTIVA (PROJETO DE MELHORIA)"
//5,3,"CONVERSÃO E INTEGRAÇÃO DE SISTEMAS"
//6,4,"DOCUMENTAÇÃO DE SISTEMAS"
//7,5,"ATENDIMENTO EXTERNO NAS SECCIONAIS"
//8,6,MEDIÇÃO


//    Construtor do helper
    public function calculahorasla($data = array(), $sla = false)
    {
        $sol = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $calendario = new App_Sosti_CalendarioSla();
        if (!array_key_exists('CTSS_ID_CATEGORIA_SERVICO', $data))
            $data['CTSS_ID_CATEGORIA_SERVICO'] = current($this->getCategoria($data['SSOL_ID_DOCUMENTO']));

//        if (!array_key_exists('SSPA_DT_PRAZO', $data))
//            $data['SSPA_DT_PRAZO'] = $data['PRAZO_DATA'];

        if (!array_key_exists('MOVI_DH_ENCAMINHAMENTO', $data))
            $data['MOVI_DH_ENCAMINHAMENTO'] = $data['DATA_CHAMADO'];

        if (!array_key_exists('ASSO_IC_ATENDIMENTO_EMERGENCIA', $data))
            $data['ASSO_IC_ATENDIMENTO_EMERGENCIA'] = $data['EMERGENCIA'];

        if (!array_key_exists('ASIS_IC_NIVEL_CRITICIDADE', $data)) {
            switch ($data['PRAZO_SEGUNDOS_UTEIS_STR']) {
                case '1D 11h 0m 0s': {
                    $data['ASIS_IC_NIVEL_CRITICIDADE'] = 3;
                }
                    break;
                case '0D 12h 0m 0s': {
                    $data['ASIS_IC_NIVEL_CRITICIDADE'] = 2;
                }
                    break;
                case '0D 6h 0m 0s': {
                    $data['ASIS_IC_NIVEL_CRITICIDADE'] = 1;
                }
                    break;
            }
        }
//        Zend_Debug::dump($data);
        $this->setFromArray($data);

        $expediente = $this->_expediente['normal'];
        if ($this->_assoIcAtendimentoEmergencia == "S")
            $expediente = $this->_expediente['emergencial'];

        $hist = $sol->getHistoricoSolInformacao((int)$this->_ssolIdDocumento, $this->toEn($data['MOVI_DH_ENCAMINHAMENTO']));
        $movimentacao = strtotime($this->toEn($this->_moviDhEncaminhamento));
        $hoje = strtotime(date('Y-m-d H:i:s'));

        $prazo = $this->calculaTempo($sla);
        $faseHorasParado = 0;
        if (!empty($this->_sspaDtPrazo)) {
            $diasUteisExt = $calendario->obterDiasUteisPeriodo($this->_moviDhEncaminhamento, $this->_sspaDtPrazo);
            $diasUteisExt = ($diasUteisExt >= 2) ? $diasUteisExt - 2 : 0;

            $extEnd = strtotime($this->toEn($this->_sspaDtPrazo));

            $extHorasRestantes = 0;

            if (date('dmY', $movimentacao) === date('dmY', $extEnd)) {
                $diasUteisExt = 0;
                if ($calendario->isDiaUtil(date('d/m/Y', $movimentacao))) {
                    if (date('H', $movimentacao) <= $expediente['inicio'] && date('H', $extEnd) >= $expediente['fim']) {
                        $diasUteisExt++;
//                            Zend_Debug::dump('+ um dia!');
                    } else {
                        $extHorasRestantes += (strtotime(date('H:i:s', $extEnd)) - strtotime(date('H:i:s', $movimentacao))) / 3600;
//                            Zend_Debug::dump("mesmo dia! horas restantes:" . $faseHorasRestantes);
                    }
                }
            } else {
                if (!$calendario->isDiaUtil(date('d/m/Y', $movimentacao))) {
                    $movimentacao = strtotime($this->toEn($calendario->obterDiaUtilApos(date('d/m/Y', $movimentacao)) . "0" . $expediente['inicio'] . ":00:00"));
//                    Zend_Debug::dump($movimentacao);
                }
                if (!$calendario->isDiaUtil(date('d/m/Y', $extEnd))) {
                    $extEnd = strtotime($this->toEn($calendario->obterDiaUtilApos(date('d/m/Y', $extEnd)) . "0" . $expediente['inicio'] . ":00:00"));
//                    Zend_Debug::dump($extEnd);
                }

                if (date('dmY', $movimentacao) != date('dmY', $extEnd)) {
                    $extHorasRestantes += ($extEnd - strtotime(date('Y-m-d', $extEnd) . " 0" . $expediente['inicio'] . ":00:00")) / 3600;
                    $extHorasRestantes += (strtotime(date('Y-m-d', $movimentacao) . " " . $expediente['fim'] . ":00:00") - $movimentacao) / 3600;
                }
            }

            $prazo = ($diasUteisExt * $expediente['horas']) + $extHorasRestantes;

        }

        $totalHoras = 0;
        if (!empty($hist)) {
            $init = true;
            for ($i = 0; count($hist) > $i; $i++) {
                if($hist[$i]["MOFA_ID_FASE"] == 1024 && $init)
                    $fases[1024][] = $hist[$i];

                $init = false;

                if($hist[$i]["MOFA_ID_FASE"] == 1025){
                    $init = true;
                    $fases[1025][] = $hist[$i];
                }

//                $fases[$hist[$i]["MOFA_ID_FASE"]][] = $hist[$i];
            }
//            Zend_Debug::dump($hist);
//            Zend_Debug::dump($fases);

            if (!isset($fases[1025])) {
                $faseFirst = $fases[1024][0];
                unset($fases);
                $fases[1024][0] = $faseFirst;
            }

            $faseHorasRestantes = 0;
            $totalHoras = 0;
            $dias = 0;
            for ($i = 0; count($fases[1024]) > $i; $i++) {

                $init = strtotime($fases[1024][$i]["MOFA_DH_FASE"]);

//                if ($calendario->isDiaUtil(date('d/m/Y', $init)) && $calendario->isDiaUtil(date('d/m/Y', strtotime(date('Y-m-d', $init) . ' +1 day'))))
                $initDay = strtotime(date('Y-m-d', $init)/* . ' +1 day'*/);

                if (isset($fases[1025][$i])) {
                    $end = strtotime($fases[1025][$i]["MOFA_DH_FASE"]);
//                    if ($calendario->isDiaUtil(date('d/m/Y', $end)) && $calendario->isDiaUtil(date('d/m/Y', strtotime(date('Y-m-d', $end) . ' +1 day'))))
                    $endDay = strtotime(date('Y-m-d', $end) /*. ' -1 day'*/);
                } else {
                    $end = strtotime(date('Y-m-d H:i:s'));
                    if ($sla)
                        $end = strtotime($this->toEn($this->_dataFimChamado));
//                    if ($calendario->isDiaUtil(date('d/m/Y', $end)) && $calendario->isDiaUtil(date('d/m/Y', strtotime(date('Y-m-d', $end) . ' +1 day'))))
                    $endDay = strtotime(date('Y-m-d', $end)/* . ' -1 day'*/);
                }

                $dias = $calendario->obterDiasUteisPeriodo(date('d/m/Y', $initDay), date('d/m/Y', $endDay));
                $dias = ($dias >= 2) ? $dias - 2 : 0;

                if (date('dmY', $init) === date('dmY', $end)) {
                    $dias = 0;
                    if ($calendario->isDiaUtil(date('d/m/Y', $init))) {
                        if (date('H', $init) <= $expediente['inicio'] && date('H', $end) >= $expediente['fim']) {
                            $dias++;
                            $faseHorasParado = 13;
                        } else {
                            $faseHorasRestantes += ($end - $init) / 3600;
                            if (!isset($fases[1025][$i]))
                                $faseHorasParado = ($end - $init) / 3600;
//                            Zend_Debug::dump("mesmo dia! horas restantes:" . ($end-$init) / 3600);
                        }
                    }
                } else {
                    if (!$calendario->isDiaUtil(date('d/m/Y', $init))) {
                        $init = strtotime($this->toEn($calendario->obterDiaUtilApos(date('d/m/Y', $init)) . "0" . $expediente['inicio'] . ":00:00"));
//                        Zend_Debug::dump($init);
                    }
                    if (!$calendario->isDiaUtil(date('d/m/Y', $end))) {
                        $end = strtotime($this->toEn($calendario->obterDiaUtilApos(date('d/m/Y', $end)) . "0" . $expediente['inicio'] . ":00:00"));
//                        Zend_Debug::dump($end);
                    }

                    if (date('dmY', $init) != date('dmY', $end)) {
                        $faseHorasRestantes += ($end - strtotime(date('Y-m-d', $end) . " 0" . $expediente['inicio'] . ":00:00")) / 3600;
                        $faseHorasRestantes += (strtotime(date('Y-m-d', $init) . " " . $expediente['fim'] . ":00:00") - $init) / 3600;
                        if (!isset($fases[1025][$i])) {
                            $faseHorasParado += ($end - strtotime(date('Y-m-d', $end) . " 0" . $expediente['inicio'] . ":00:00")) / 3600;
                            $faseHorasParado += (strtotime(date('Y-m-d', $init) . " " . $expediente['fim'] . ":00:00") - $init) / 3600;
                            $faseHorasParado = $dias * $expediente['horas'] + $faseHorasParado;
                        }
                    }
                }
                $totalHoras += ($dias * $expediente['horas']);
            }
            $totalHoras += $faseHorasRestantes;
        }

        if ($sla)
            $hoje = strtotime($this->toEn($this->_dataFimChamado));

        $diasUteis = $calendario->obterDiasUteisPeriodo(date('d/m/Y', $movimentacao), date('d/m/Y', $hoje));
        $horasRestantes = 0;
        $tttotalHoras = 0;
        $diasUteis = ($diasUteis >= 2) ? $diasUteis - 2 : 0;

        if (date('dmY', $movimentacao) === date('dmY', $hoje)) {
            if ($calendario->isDiaUtil(date('d/m/Y', $movimentacao))) {
                $diasUteis = 0;
                if (date('H', $movimentacao) <= $expediente['inicio'] && date('H', $hoje) >= $expediente['fim']) {
                    $diasUteis++;
//                            Zend_Debug::dump('+ um dia!');
                } else {
                    $horasRestantes += ($hoje - $movimentacao) / 3600;
//                            Zend_Debug::dump("mesmo dia! horas restantes:" . $horasRestantes);
                }
            }
        } else {
            if (!$calendario->isDiaUtil(date('d/m/Y', $movimentacao))) {
                $movimentacao = strtotime($this->toEn($calendario->obterDiaUtilApos(date('d/m/Y', $movimentacao)) . " 0" . $expediente['inicio'] . ":00:00"));
            }
            if (!$calendario->isDiaUtil(date('d/m/Y', $hoje))) {
                $hoje = strtotime($this->toEn($calendario->obterDiaUtilApos(date('d/m/Y', $hoje)) . " 0" . $expediente['inicio'] . ":00:00"));
            }
            if (date('dmY', $movimentacao) != date('dmY', $hoje)) {
                $horasRestantes += ($hoje - strtotime(date('Y-m-d', $hoje) . " 0" . $expediente['inicio'] . ":00:00")) / 3600;
                $horasRestantes += (strtotime(date('Y-m-d', $movimentacao) . " " . $expediente['fim'] . ":00:00") - $movimentacao) / 3600;
            }
        }

        $tttotalHoras += $diasUteis * $expediente['horas'] + $horasRestantes;

        $pr = ($tttotalHoras - $totalHoras) / $expediente['horas'];
        $dias = floor($pr);
        $pr = ($pr < 0) ? abs($pr) : $pr;
        $horas = ($pr * $expediente['horas']) % $expediente['horas'];
        $minutos = (($pr * $expediente['horas']) * 60) % 60;
        $segundos = (($pr * $expediente['horas']) * 3600) % 60;

        $ppr = ($prazo - ($tttotalHoras - $totalHoras)) / $expediente['horas'];
        $prazofinal = $ppr;

        $pdias = floor($ppr);
        $ppr = ($ppr < 0) ? abs($ppr) : $ppr;
        $phoras = ($ppr * $expediente['horas']) % $expediente['horas'];
        $pminutos = (($ppr * $expediente['horas']) * 60) % 60;
        $psegundos = (($ppr * $expediente['horas']) * 3600) % 60;

        $putil = $prazo / $expediente['horas'];
        $putildias = floor($putil);
        $putilhoras = ($putil * $expediente['horas']) % $expediente['horas'];
        $putilminutos = (($putil * $expediente['horas']) * 60) % 60;
        $putilsegundos = (($putil * $expediente['horas']) * 3600) % 60;
//
//        Zend_Debug::dump($dias);
//        Zend_Debug::dump($horas);
//        Zend_Debug::dump($minutos);
//        Zend_Debug::dump($segundos);
//
//        Zend_Debug::dump($pdias);
//        Zend_Debug::dump($phoras);
//        Zend_Debug::dump($pminutos);
//        Zend_Debug::dump($psegundos);
//        echo "Prazo em horas";
//        Zend_Debug::dump($prazo);
//        echo "Inicio e fim";
//        Zend_Debug::dump($diasUteis);
//        echo "Tempo parado";
//        Zend_Debug::dump($faseHorasParado);
//        echo "Total de Pausas";
//        Zend_Debug::dump($totalHoras);
//        echo "Tempo total do atendimento";
//        Zend_Debug::dump($tttotalHoras);
//        echo "Tempo considerado";
//        Zend_Debug::dump($tttotalHoras - $totalHoras);
//        echo "pct";
//        Zend_Debug::dump(($prazo - ($tttotalHoras - $totalHoras)) / $prazo * 100);
        $pct = ($prazo - ($tttotalHoras - $totalHoras)) / $prazo * 100;

//        Zend_Debug::dump($pct);
//        echo "-----------------------------------------------------<br>";
        switch (true) {
            case $pct >= 80: {
                $cor = "green";
            }
                break;
            case $pct >= 60: {
                $cor = "purple";
            }
                break;
            case $pct >= 40: {
                $cor = "blue";
            }
                break;
            case $pct >= 20: {
                $cor = "#CD950C";
            }
                break;
            case $pct > 0: {
                $cor = "orange";
            }
                break;
            case $pct < 0: {
                $cor = 'red';
            }
                break;
        }

        $percentual = array(
            'pct' => number_format($pct, 2),
            'cor' => $cor
        );
        return array(
            'prazo' => $prazofinal,
            'noprazo' => ($pct<0) ? "N" : "S",
            'expediente' => $expediente['horas'],
            'prazo_util' => array($putildias, $putilhoras, $putilminutos, $putilsegundos),
            'prazo_total' => array($dias, $horas, $minutos, $segundos),
            'prazo_restante' => array($pdias, $phoras, $pminutos, $psegundos),
            'percentual' => $percentual
        );

    }

    public function setFromArray($data = array())
    {
        $properties = get_object_vars($this);
        unset($properties['view']);
        foreach ($data as $key => $value) {
            $key = "_" . $this->underlineToCamelCase($key);
            if (in_array($key, array_keys($properties))) {
                $this->$key = $value;
            }
        }
    }

    public function underlineToCamelCase($string)
    {
        $string = trim(ucwords(str_replace('_', ' ', strtolower($string))));
        $string = explode(' ', $string);
        $string[0] = strtolower($string[0]);
        return implode('', $string);
    }

    private function calculaTempo($sla = false)
    {
        $prazo = 0;
        if ($this->_corretiva == "N") {
            if ($this->_assoIcAtendimentoEmergencia == "S") {
                foreach ($this->_prazo['emergencial'] as $key => $value) {
                    if (in_array($this->_ctssIdCategoriaServico, $value))
                        $prazo = $key;
                }
            } else {
                foreach ($this->_prazo['normal'] as $key => $value) {
                    if($sla && $this->_ctssIdCategoriaServico == 2)
                        $this->_ctssIdCategoriaServico = 3;
                    if (in_array($this->_ctssIdCategoriaServico, $value))
                        $prazo = $key;
                }
            }
        } else {
            foreach ($this->_prazo['corretiva'] as $key => $value) {
                if (in_array($this->_asisIcNivelCriticidade, $value))
                    $prazo = $key;
            }
        }
        return $prazo;
    }

    private function toEn($date)
    {
        $date = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date)));
        return $date;
    }

    private function getCategoria($idDoc)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = $db->query("
            SELECT CTSS.CTSS_ID_CATEGORIA_SERVICO
            FROM
              SAD_TB_MODO_MOVI_DOCUMENTO MODO
              INNER JOIN
              SAD_TB_MOVI_MOVIMENTACAO MOVI
                ON MOVI.MOVI_ID_MOVIMENTACAO = MODO.MODO_ID_MOVIMENTACAO
              INNER JOIN SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                ON MOVI.MOVI_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
              INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
              INNER JOIN SOS_TB_CTSS_CATEG_SERV_SISTEMA CTSS
                ON ASIS.ASIS_ID_CATEGORIA_SERVICO = CTSS.CTSS_ID_CATEGORIA_SERVICO
            WHERE
              MODO.MODO_ID_DOCUMENTO = $idDoc
        ");

        return current($query->fetchAll());
    }
}