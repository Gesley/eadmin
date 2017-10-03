<?php
/**
 * Description of TempoSla
 *
 * @author Marcelo Caixeta Rocha & Leonan Alves dos Anjos
 */

class App_Sosti_TempoSla extends App_TimeInterval
{
    public static $dataFimSysdate = '';
    private $CalendarioSla = NULL;
    protected $horaInicioExpediente;
    protected $horaFimExpediente;
    protected $emSegundosInicioExpediente;
    protected $emSegundosFimExpediente;
    protected $emSegundosExpediente;
    protected $dataInicioSemExpediente;
    protected $dataFimSemExpediente;
    protected $horasAberturaSolicitacao;
    protected $horasBaixaSolicitacao;
    protected $hAbertura;
    protected $hBaixa;
    protected $segundosAberturaSolicitacao;
    protected $segundosBaixaSolicitacao;
    protected $diasUteis;
    protected $diaAberturaSolicitacao;
    protected $diaBaixaSolicitacao;
    protected $dataInicio;
    protected $dataFim;

    public function __construct()
    {
        self::$dataFimSysdate = parent::sysdateDb();
        $this->CalendarioSla = new App_Sosti_CalendarioSla();
    }
    
    /**
     * Recebe duas data e calcula o tempo total do helpdesk
     * @param type $dataInicial
     * @param type $dataFinal
     * @return type 
     */
    public function tempoTotalHelpdesk($dataInicio, $dataFim, $horaInicioExpediente, $horaFimExpediente)
    {
        if ($dataFim == '') {
            $dataFim = self::$dataFimSysdate;
        }
        return parent::FormataSaidaSegundos($this->tempoTotalSLA($dataInicio, $dataFim, $horaInicioExpediente, $horaFimExpediente));
    }
    
        /**
     * Recebe duas data e calcula o tempo total do helpdesk
     * @param type $dataInicial
     * @param type $dataFinal
     * @return type 
     */
    public function tempoTotalSLA($dataInicio, $dataFim, $horaInicioExpediente, $horaFimExpediente)
    {
        $this->horaInicioExpediente = $horaInicioExpediente;
        $this->horaFimExpediente = $horaFimExpediente;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->emSegundosInicioExpediente = null;
        $this->emSegundosFimExpediente = null;
        $this->emSegundosExpediente = null;
        $this->dataInicioSemExpediente = null;
        $this->dataFimSemExpediente = null;
        $this->horasAberturaSolicitacao = null;
        $this->horasBaixaSolicitacao = null;
        $this->hAbertura = null;
        $this->hBaixa = null;
        $this->segundosAberturaSolicitacao = null;
        $this->segundosBaixaSolicitacao = null;
        $this->diasUteis = null;
        $this->diaAberturaSolicitacao = null;
        $this->diaBaixaSolicitacao = null;
        
         /**
         * Validação de formato de data para classe
         */
        if( (!Zend_Date::isDate($this->dataInicio, 'dd/MM/yyyy HH:mm:ss')) || (!(strlen($this->dataInicio) === 19)) ){
            throw new Exception('Formato de data/hora invalido. O formato de ser dd/mm/yyyy hh:mm:ss');
        }
        if( (!Zend_Date::isDate($this->dataFim, 'dd/MM/yyyyHH:mm:ss')) || (!(strlen($this->dataFim) === 19)) ){
            throw new Exception('Formato de data/hora invalido. O formato de ser dd/mm/yyyy hh:mm:ss');
        }
        if( (!Zend_Date::isDate('01/01/2000 '.$this->horaInicioExpediente, 'dd/MM/yyyyHH:mm:ss')) || !(strlen($this->horaInicioExpediente) === 8)) {
            throw new Exception('Formato de hora invalido. O formato de ser hh:mm:ss');
        }
        if( (!Zend_Date::isDate('01/01/2000 '.$this->horaFimExpediente, 'dd/MM/yyyyHH:mm:ss')) || (!(strlen($this->horaFimExpediente) === 8)) ){
            throw new Exception('Formato de hora invalido. O formato de ser hh:mm:ss');
        }
        
        /**
         * Obtem em segudos a hora de inicio de expediente e de fim
         * contabilizando os segundos desde o início do dia
         */
        $this->emSegundosInicioExpediente = parent::converteHorasParaSegundos($this->horaInicioExpediente);
        $this->emSegundosFimExpediente = parent::converteHorasParaSegundos($this->horaFimExpediente);
        $this->emSegundosExpediente = ($this->emSegundosFimExpediente - $this->emSegundosInicioExpediente);
        
        /**
         * Verifica se a data inicial e data final não possui expediente
         */
        $this->dataInicioSemExpediente = $this->CalendarioSla->verificaDataSemExpediente($this->dataInicio);
        $this->dataFimSemExpediente = $this->CalendarioSla->verificaDataSemExpediente($this->dataFim);
        
         /**
         * Modifica as datas de abertura e fechamento se essas estiverem fora do horário de expediente
         */
        $this->hAbertura = explode(' ', $this->dataInicio);
        $this->hBaixa =  explode(' ', $this->dataFim);
        $this->horasAberturaSolicitacao = parent::horaExpediente($this->hAbertura[1],$this->horaInicioExpediente, $this->horaFimExpediente);
        $this->horasBaixaSolicitacao = parent::horaExpediente($this->hBaixa[1],$this->horaInicioExpediente, $this->horaFimExpediente);
       if($this->dataInicioSemExpediente == 1){
           $this->horasAberturaSolicitacao = $this->horaInicioExpediente;
        }
        if($this->dataFimSemExpediente == 1){
           $this->horasBaixaSolicitacao = $this->horaFimExpediente;
        }
        
        //MESMO DIA
        /**
         * Se a solicitação for aberta e fechada no mesmo dia e se o dia não for dia util
         */
        if ( 
                $this->dataInicioSemExpediente == 1 //se não ouver expediente
                &&
                (substr($this->dataInicio,0,10) ==  substr($this->dataFim,0,10)) //e a data inicial for igual a data final
           ) {
            return 0;
        }
        
        /**
         * Se a solicitação for aberta e fechada no mesmo dia e se o dia for util
         */  
        if (
                $this->dataInicioSemExpediente == 0 //se ouver expediente
                &&
                (substr($this->dataInicio,0,10) ==  substr($this->dataFim,0,10)) //e a data inicial for igual a data final
            ) {
                 return parent::tempoTotalSegundos(substr($this->dataInicio,0,10)." ".$this->horasAberturaSolicitacao, substr($this->dataFim,0,10)." ".$this->horasBaixaSolicitacao);
        }
        
        //DIAS DIFERENTES 
        /**
         * Se a solicitação for aberta e fechada no mesmo dia
         */
        $this->diasUteis = $this->CalendarioSla->obterDiasUteisPeriodo($this->dataInicio, $this->dataFim);
        $this->segundosAberturaSolicitacao = parent::converteHorasParaSegundos($this->horasAberturaSolicitacao);
        $this->segundosBaixaSolicitacao = parent::converteHorasParaSegundos($this->horasBaixaSolicitacao);
        
        if ($this->diasUteis == 1) {
            return $this->segundosBaixaSolicitacao - $this->segundosAberturaSolicitacao;
        } else {
            /**
             * Quando a solicitação tem dois ou mais dias uteis, soma-se as horas do dia da 
             * abertura com as horas do dia da baixa e os dias 
             */
            if ($this->diasUteis >= 2) {
                $this->diasUteisSegundos = ($this->diasUteis - 2) * $this->emSegundosExpediente; // dias uteis - 2 menos o dia inicial e o dia final
                $this->diaAberturaSolicitacao = $this->emSegundosFimExpediente - $this->segundosAberturaSolicitacao; // 20:00 - os segundos de abertura
                $this->diaBaixaSolicitacao = $this->segundosBaixaSolicitacao - $this->emSegundosInicioExpediente; // os segundos de baixa - 07:00
                return $this->diaBaixaSolicitacao + $this->diaAberturaSolicitacao + $this->diasUteisSegundos;
            }
        }
    }
    
    public function verificaPrazoUltrapassado($segAtend, $tempoMaximo)
    {
        if($segAtend <= $tempoMaximo){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * Converte as horas formatadas das caixas para segundos
     */
    public static function converteHorasFormatadasParaSegundos($hora, $horasUteis = 13)
    {
        $aux = explode(" ", $hora);
        $diaStr = str_replace("D", "", $aux[0]);
        $horaStr = str_replace("h", "", $aux[1]);
        $minutoStr = str_replace("m", "", $aux[2]);
        $segundoStr = str_replace("s", "", $aux[3]);
        return (($diaStr*$horasUteis)*3600)+($horaStr*3600)+($minutoStr*60)+$segundoStr;
    }
    
    /**
     * Classe de degug do método tempoTotalSLA();
     * @param bollean $returnArray
     * @param bollean $returnDebug 
     */
    public function debugTempoTotalSLA($returnArray = false,$returnDebug = true)
    {
        if($returnDebug){
            Zend_Debug::dump($this->horaInicioExpediente,'horaInicioExpediente');
            Zend_Debug::dump($this->horaFimExpediente,'horaFimExpediente');
            Zend_Debug::dump($this->emSegundosInicioExpediente,'emSegundosInicioExpediente');
            Zend_Debug::dump($this->emSegundosFimExpediente,'emSegundosFimExpediente');
            Zend_Debug::dump($this->emSegundosExpediente,'emSegundosExpediente');
            Zend_Debug::dump($this->dataInicioSemExpediente,'dataInicioSemExpediente');
            Zend_Debug::dump($this->dataFimSemExpediente,'dataFimSemExpediente');
            Zend_Debug::dump($this->horasAberturaSolicitacao,'horasAberturaSolicitacao');
            Zend_Debug::dump($this->horasBaixaSolicitacao,'horasBaixaSolicitacao');
            Zend_Debug::dump($this->hAbertura,'hAbertura');
            Zend_Debug::dump($this->hBaixa,'hBaixa');
            Zend_Debug::dump($this->segundosAberturaSolicitacao,'segundosAberturaSolicitacao');
            Zend_Debug::dump($this->segundosBaixaSolicitacao,'segundosBaixaSolicitacao');
            Zend_Debug::dump($this->diasUteis,'diasUteis');
            Zend_Debug::dump($this->diaAberturaSolicitacao,'diaAberturaSolicitacao');
            Zend_Debug::dump($this->diaBaixaSolicitacao,'diaBaixaSolicitacao');
            Zend_Debug::dump($this->dataInicio,'dataInicio');
            Zend_Debug::dump($this->dataFim,'dataFim');
        }else if($returnArray){
            return array(            
                'horaInicioExpediente' =>  $this->horaInicioExpediente,
                'horaFimExpediente' =>  $this->horaFimExpediente,
                'emSegundosInicioExpediente' =>  $this->emSegundosInicioExpediente,
                'emSegundosFimExpediente' =>  $this->emSegundosFimExpediente,
                'emSegundosExpediente' =>  $this->emSegundosExpediente,
                'dataInicioSemExpediente' =>  $this->dataInicioSemExpediente,
                'dataFimSemExpediente' =>  $this->dataFimSemExpediente,
                'horasAberturaSolicitacao' =>  $this->horasAberturaSolicitacao,
                'horasBaixaSolicitacao' =>  $this->horasBaixaSolicitacao,
                'hAbertura' =>  $this->hAbertura,
                'hBaixa' =>  $this->hBaixa,
                'segundosAberturaSolicitacao' =>  $this->segundosAberturaSolicitacao,
                'segundosBaixaSolicitacao' =>  $this->segundosBaixaSolicitacao,
                'diasUteis' =>  $this->diasUteis,
                'diaAberturaSolicitacao' =>  $this->diaAberturaSolicitacao,
                'diaBaixaSolicitacao' =>  $this->diaBaixaSolicitacao,
                'dataInicio' =>  $this->dataInicio,
                'dataFim' =>  $this->dataFim
            );
        }
    }
    
    /**
     * Calcula o tempo total desconsiderando o tempo pedido-resposta informação
     * @param array $solics array bidimencional com uma chave contendo o id da movimentação a data inicial e a data final da contagem
     * @param string $chaveMov  a chave que corresponde ao id da movimentação,inicío da contagem,fim da contagem
     * @param string $chaveDataInicio a chave que corresponde a data de inicío da contagem
     * @param string $chaveDataFim a chave que corresponde a data de fim da contagem
     * @param type $chaveVeriExisPedido a chave que corresponde a posição para contagem somente das solicitacoes com pedido de informacao 
     * @param type $chaveEmergencial a chave que corresponde a posição para verificação se a solicitação é emergencial similar ao $chaveVeriExisPedido
     * @param array $expediente array( 'NORMAL'=>array('INICIO'=>'',FIM=>''),'EMERGENCIAL'=>array('INICIO'=>'',FIM=>'') )
     * @return array 
     */
    public function TempoTotalPedidoInfor($solics, $chaveMov, $chaveDataInicio, $chaveDataFim, $chaveVeriExisPedido,$chaveEmergencial, array $expediente) {
        $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $chaveHistorico = "HISTORICO";
        $chaveInicioExpediente = "INICIO_EXPEDIENTE";
        $chaveFimExpediente = "FIM_EXPEDIENTE";
        $faseParaContagem = 1024;
        $faseRecomecaContagem = 1025;

        if ($chaveVeriExisPedido != "") {
            /*             * *
             * Recupera os ids das movimentações com pedido de informação no histórico.
             */
            $movimentacoesComPedido = array();
            foreach ($solics as $solic) {
                if (!is_null($solic[$chaveVeriExisPedido])) {
                    $movimentacoesComPedido[$solic[$chaveMov]][$chaveMov] = $solic[$chaveMov];
                    $movimentacoesComPedido[$solic[$chaveMov]][$chaveDataInicio] = $solic[$chaveDataInicio];
                    $movimentacoesComPedido[$solic[$chaveMov]][$chaveDataFim] = $solic[$chaveDataFim];
                    $movimentacoesComPedido[$solic[$chaveMov]][$chaveHistorico] = array();
                    if (!empty($chaveEmergencial)) {
                        if (is_null($solic[$chaveEmergencial]) || $solic[$chaveEmergencial] == "N") {
                            $movimentacoesComPedido[$solic[$chaveMov]][$chaveInicioExpediente] = $expediente['NORMAL']['INICIO'];
                            $movimentacoesComPedido[$solic[$chaveMov]][$chaveFimExpediente] = $expediente['NORMAL']['FIM'];
                            $movimentacoesComPedido[$solic[$chaveMov]]["DIA_UTIL_HORAS"]= $expediente["NORMAL"]["DIA_UTIL_HORAS"];
                        } else {
                            $movimentacoesComPedido[$solic[$chaveMov]][$chaveInicioExpediente] = $expediente['EMERGENCIAL']['INICIO'];
                            $movimentacoesComPedido[$solic[$chaveMov]][$chaveFimExpediente] = $expediente['EMERGENCIAL']['FIM'];
                            $movimentacoesComPedido[$solic[$chaveMov]]["DIA_UTIL_HORAS"]= $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"];
                        }
                    } else {
                        $movimentacoesComPedido[$solic[$chaveMov]][$chaveInicioExpediente] = $expediente['NORMAL']['INICIO'];
                        $movimentacoesComPedido[$solic[$chaveMov]][$chaveFimExpediente] = $expediente['NORMAL']['FIM'];
                        $movimentacoesComPedido[$solic[$chaveMov]]["DIA_UTIL_HORAS"]= $expediente["NORMAL"]["DIA_UTIL_HORAS"];
                    }
                }
            }
        } else {
            $movimentacoesComPedido = array();
            foreach ($solics as $solic) {
                $movimentacoesComPedido[$solic[$chaveMov]][$chaveMov] = $solic[$chaveMov];
                $movimentacoesComPedido[$solic[$chaveMov]][$chaveDataInicio] = $solic[$chaveDataInicio];
                $movimentacoesComPedido[$solic[$chaveMov]][$chaveDataFim] = $solic[$chaveDataFim];
                $movimentacoesComPedido[$solic[$chaveMov]][$chaveHistorico] = array();
                if (!empty($chaveEmergencial)) {
                     if (is_null($solic[$chaveEmergencial]) || $solic[$chaveEmergencial] == "N") {
                        $movimentacoesComPedido[$solic[$chaveMov]][$chaveInicioExpediente] = $expediente['NORMAL']['INICIO'];
                        $movimentacoesComPedido[$solic[$chaveMov]][$chaveFimExpediente] = $expediente['NORMAL']['FIM'];
                        $movimentacoesComPedido[$solic[$chaveMov]]["DIA_UTIL_HORAS"]= $expediente["NORMAL"]["DIA_UTIL_HORAS"];
                    } else {
                        $movimentacoesComPedido[$solic[$chaveMov]][$chaveInicioExpediente] = $expediente['EMERGENCIAL']['INICIO'];
                        $movimentacoesComPedido[$solic[$chaveMov]][$chaveFimExpediente] = $expediente['EMERGENCIAL']['FIM'];
                        $movimentacoesComPedido[$solic[$chaveMov]]["DIA_UTIL_HORAS"]= $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"];
                    }
                } else {
                    $movimentacoesComPedido[$solic[$chaveMov]][$chaveInicioExpediente] = $expediente['NORMAL']['INICIO'];
                    $movimentacoesComPedido[$solic[$chaveMov]][$chaveFimExpediente] = $expediente['NORMAL']['FIM'];
                    $movimentacoesComPedido[$solic[$chaveMov]]["DIA_UTIL_HORAS"]= $expediente["NORMAL"]["DIA_UTIL_HORAS"];
                }
            }
        }

        /**
         * Recupera as fases de pedido-resposta do histórico com base nos ids das movimentações
         */
        $histoComPedidoArr = array();
        if(count($movimentacoesComPedido) > 0){
            $idsMovComPedido = implode(',', array_keys($movimentacoesComPedido));
            $histoComPedidoArr = $indicadorNivelServ->getDatasMovPedidoRespostaInfo($idsMovComPedido);
        }

        /**
         * Junta as arrays de ids de movimentações com as fases de pedido-resposta 
         */
        if (!empty($histoComPedidoArr)) {
            foreach ($movimentacoesComPedido as $chaveMoviCom => $moviValue) {
                foreach ($histoComPedidoArr as $histo) {
                    if ($histo[$chaveMov] == $chaveMoviCom) {
                        $movimentacoesComPedido[$chaveMoviCom][$chaveHistorico][] = $histo;
                    }
                }
            }
        }

        /**
         * Extrai a partir da array juntada os pares inicio-fim para o cálculo de tempo útil  
         */
        foreach ($movimentacoesComPedido as $chaveMoviCom => $moviValue) {
            $historico = $movimentacoesComPedido[$chaveMoviCom][$chaveHistorico];
            $array_intervalos = array();
            $conta_intervalos = 0;
            if (!empty($historico)) {
                $count_historico = count($historico);
                for ($n = 0; $n < $count_historico; $n++) {
                    if ($historico[$n]["MOFA_ID_FASE"] == $faseParaContagem) {
                        if ($n == 0) {
                            $array_intervalos[$conta_intervalos]["INICIO"] = $movimentacoesComPedido[$chaveMoviCom][$chaveDataInicio];
                            $array_intervalos[$conta_intervalos]["FIM"] = $historico[$n]["MOFA_DH_FASE"];
                            $conta_intervalos++;
                        }
                        while ($historico[$n]["MOFA_ID_FASE"] == $faseParaContagem) {
                            $n++;
                            if ($n == $count_historico) {
                                break;
                            }
                        }
                        $n--;
                    } else {
                        if ($n == $count_historico - 1) {
                            $array_intervalos[$conta_intervalos]["INICIO"] = $historico[$n]["MOFA_DH_FASE"];
                            $array_intervalos[$conta_intervalos]["FIM"] = $movimentacoesComPedido[$chaveMoviCom][$chaveDataFim];
                            $conta_intervalos++;
                        } else {
                            $array_intervalos[$conta_intervalos]["INICIO"] = $historico[$n]["MOFA_DH_FASE"];

                            while ($historico[$n]["MOFA_ID_FASE"] == $faseRecomecaContagem) {
                                $n++;
                                if ($n == $count_historico - 1) {
                                    break;
                                }
                            }
                            $array_intervalos[$conta_intervalos]["FIM"] = $historico[$n]["MOFA_DH_FASE"];
                            $conta_intervalos++;
                        }
                    }
                    $movimentacoesComPedido[$chaveMoviCom]["INTERVALOS"] = $array_intervalos;
                }
            } else {
                $array_intervalos[0]["INICIO"] = $movimentacoesComPedido[$chaveMoviCom][$chaveDataInicio];
                $array_intervalos[0]["FIM"] = $movimentacoesComPedido[$chaveMoviCom][$chaveDataFim];
                $movimentacoesComPedido[$chaveMoviCom]["INTERVALOS"] = $array_intervalos;
            }
        }

        foreach ($movimentacoesComPedido as $chaveMoviCom => $moviValue) {
            $soma_tempo = 0;
            foreach ($moviValue["INTERVALOS"] as $intervalos) {
                $soma_tempo += $this->tempoTotalSLA($intervalos["INICIO"], $intervalos["FIM"], $movimentacoesComPedido[$chaveMoviCom][$chaveInicioExpediente], $movimentacoesComPedido[$chaveMoviCom][$chaveFimExpediente]);
            }
            $movimentacoesComPedido[$chaveMoviCom]["TEMPO_UTIL_TOTAL"] = $soma_tempo;
        }
        return $movimentacoesComPedido;
    }
}