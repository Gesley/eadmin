<?php
/**
 * Description of TempoSlaDesenvolvimento
 *
 * @author Leonan Alves dos Anjos
 */

class App_Sosti_TempoSlaDesenvolvimento extends App_Sosti_TempoSla
{

    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Calcula o tempo total desconsiderando o tempo pedido-resposta informação
     * Calcula os prazos de acordo com
     * se o prazo prazo é útil ou não
     * se é emergencial ou não
     * se existe um prazo acordado ou não
     * @param array $solics array bidimencional com uma chave contendo o id da movimentação a data inicial e a data final da contagem
     * @param string $chaveMov  a chave que corresponde ao id da movimentação,inicío da contagem,fim da contagem
     * @param string $chaveDataInicio a chave que corresponde a data de inicío da contagem
     * @param string $chaveDataFim a chave que corresponde a data de fim da contagem
     * @param type $chaveVeriExisPedido a chave que corresponde a posição para contagem somente das solicitacoes com pedido de informacao 
     * @param type $chaveEmergencial a chave que corresponde a posição para verificação se a solicitação é emergencial similar ao $chaveVeriExisPedido
     * @param array $expediente array( 'NORMAL'=>array('INICIO'=>'',FIM=>''),'EMERGENCIAL'=>array('INICIO'=>'',FIM=>'') )
     * @return array 
     */
    public function PrazoSlaDesenvolvimento($solics, $chaveMov, $chaveDataInicio, $chavePrazo, $chaveCorretiva, $chaveEmergencial, $chaveProblema, $chaveCausa, $chavePrazoProblema, $chavePrazoCausa, $chavePrazoExecusaoServico, array $expediente) {
        
        $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] = $this->converteHorasParaSegundos($expediente["NORMAL"]["FIM"]) - $this->converteHorasParaSegundos($expediente["NORMAL"]["INICIO"]);
        $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] = $this->converteHorasParaSegundos($expediente["EMERGENCIAL"]["FIM"]) - $this->converteHorasParaSegundos($expediente["EMERGENCIAL"]["INICIO"]);
        $expediente["NORMAL"]["DIA_UTIL_HORAS"] = $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60; 
        $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"] = $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;
        
        $movimentacoesPrazo = array();
        
        foreach ($solics as $solic) {
            $movimentacoesPrazo[$solic[$chaveMov]] = $solic;
        }
        
        foreach ($movimentacoesPrazo as $chavEap => $epa) {
                $movimentacoesPrazo[$chavEap]["PRAZO_CORRIDO_PADRAO"] = false;
                $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = null;
                $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = null;
                if(is_null($movimentacoesPrazo[$chavEap][$chavePrazo])){
                    
                    if($movimentacoesPrazo[$chavEap][$chaveEmergencial] == "S"){
                        if($movimentacoesPrazo[$chavEap][$chaveCorretiva] == "S" ){
                            if($movimentacoesPrazo[$chavEap][$chaveProblema] == "S"){
                                $prazoArr = explode("|",$movimentacoesPrazo[$chavEap][$chavePrazoProblema]);
                                $quantidade = $prazoArr[0];
                                $medida = $prazoArr[1];
                                $tipoMedida = $prazoArr[2];
                                
                                if($tipoMedida == '1'){
                                    if($medida == "MÊS"){
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade month",$timestamp));
                                    }else if ($medida == "HORA") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade hour",$timestamp));
                                    }else if ($medida == "DIA") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade day",$timestamp));
                                    }else if ($medida == "MINUTO") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade minute",$timestamp));
                                    }else{
                                        throw new Exception("Medida: ".$medida." não implementada.");;
                                    }
                                    $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $this->tempoTotalSLA($movimentacoesPrazo[$chavEap][$chaveDataInicio], $movimentacoesPrazo[$chavEap]["PRAZO_DATA"],  $expediente['EMERGENCIAL']['INICIO'],$expediente['EMERGENCIAL']['FIM']);
                                }else if ($tipoMedida == '2') {
                                    if ($medida == "HORA") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * 60 * 60;
                                    }else if ($medida == "DIA") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"];
                                    }else if ($medida == "MINUTO") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * 60;
                                    }else{
                                        throw new Exception("Medida: ".$medida." não implementada.");;
                                    }
                                }else{
                                    throw new Exception("Tipo de Medida: ".$tipoMedida." não implementada.");
                                }
                            }else if($movimentacoesPrazo[$chavEap][$chaveCausa] == "S"){
                                $prazoArr = explode("|",$movimentacoesPrazo[$chavEap][$chavePrazoCausa]);
                                $quantidade = $prazoArr[0];
                                $medida = $prazoArr[1];
                                $tipoMedida = $prazoArr[2];
                                
                                if($tipoMedida == '1'){
                                    if($medida == "MÊS"){
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade month",$timestamp));
                                    }else if ($medida == "HORA") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade hour",$timestamp));
                                    }else if ($medida == "DIA") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade day",$timestamp));
                                    }else if ($medida == "MINUTO") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade minute",$timestamp));
                                    }else{
                                        throw new Exception("Medida: ".$medida." não implementada.");;
                                    }
                                    $movimentacoesPrazo[$chavEap]["PRAZO_CORRIDO_PADRAO"] = true;
                                }else if ($tipoMedida == '2') {
                                    if ($medida == "HORA") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * 60 * 60;
                                    }else if ($medida == "DIA") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"];
                                    }else if ($medida == "MINUTO") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * 60;
                                    }else{
                                        throw new Exception("Medida: ".$medida." não implementada.");;
                                    }
                                }else{
                                    throw new Exception("Tipo de Medida: ".$tipoMedida." não implementada.");
                                }
                            }else{
                                $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = null;
                            }
                        }else{
                            if(!is_null($movimentacoesPrazo[$chavEap][$chavePrazoExecusaoServico])){
                                $prazoArr = explode("|",$movimentacoesPrazo[$chavEap][$chavePrazoExecusaoServico]);
                                $quantidade = $prazoArr[0];
                                $medida = $prazoArr[1];
                                $tipoMedida = $prazoArr[2];

                                if($tipoMedida == '1'){
                                    if($medida == "MÊS"){
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade month",$timestamp));
                                    }else if ($medida == "HORA") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade hour",$timestamp));
                                    }else if ($medida == "DIA") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade day",$timestamp));
                                    }else if ($medida == "MINUTO") {
                                        $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                        $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                        $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade minute",$timestamp));
                                    }else{
                                        throw new Exception("Medida: ".$medida." não implementada.");;
                                    }
                                    $movimentacoesPrazo[$chavEap]["PRAZO_CORRIDO_PADRAO"] = true;
                                }else if ($tipoMedida == '2') {
                                    if ($medida == "HORA") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * 60 * 60;
                                    }else if ($medida == "DIA") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"];
                                    }else if ($medida == "MINUTO") {
                                        $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * 60;
                                    }else{
                                        throw new Exception("Medida: ".$medida." não implementada.");;
                                    }
                                }else{
                                    throw new Exception("Tipo de Medida: ".$tipoMedida." não implementada.");
                                }
                            }else{
                                $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = null;
                            }
                            
                        }
                    }else{
                        if(!is_null($movimentacoesPrazo[$chavEap][$chavePrazoExecusaoServico])){
                            $prazoArr = explode("|",$movimentacoesPrazo[$chavEap][$chavePrazoExecusaoServico]);
                            $quantidade = $prazoArr[0];
                            $medida = $prazoArr[1];
                            $tipoMedida = $prazoArr[2];

                            if($tipoMedida == '1'){
                                if($medida == "MÊS"){
                                    $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                    $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                    $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade month",$timestamp));
                                }else if ($medida == "HORA") {
                                    $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                    $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                    $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade hour",$timestamp));
                                }else if ($medida == "DIA") {
                                    $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                    $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                    $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade day",$timestamp));
                                }else if ($medida == "MINUTO") {
                                    $dataInicial = $movimentacoesPrazo[$chavEap][$chaveDataInicio];
                                    $timestamp = mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                    $movimentacoesPrazo[$chavEap]["PRAZO_DATA"] = date('d/m/Y H:i:s',  strtotime("+$quantidade minute",$timestamp));
                                }else{
                                    throw new Exception("Medida: ".$medida." não implementada.");;
                                }
                                $movimentacoesPrazo[$chavEap]["PRAZO_CORRIDO_PADRAO"] = true;
                            }else if ($tipoMedida == '2') {
                                if ($medida == "HORA") {
                                    $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * 60 * 60;
                                }else if ($medida == "DIA") {
                                    $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"];
                                }else if ($medida == "MINUTO") {
                                    $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = $quantidade * 60;
                                }else{
                                    throw new Exception("Medida: ".$medida." não implementada.");
                                }
                            }else{
                                throw new Exception("Tipo de Medida: ".$tipoMedida." não implementada.");
                            }
                        }else{
                            $movimentacoesPrazo[$chavEap]["PRAZO_SEGUNDOS_UTEIS"] = null;
                        }
                    }
                }
            }
        return $movimentacoesPrazo;
    }
}