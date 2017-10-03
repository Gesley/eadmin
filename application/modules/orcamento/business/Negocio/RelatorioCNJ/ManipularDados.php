<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Contém as regras negociais sobre regra
 *
 * @category Orcamento
 * @package GerarExcel
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_RelatorioCNJ_ManipularDados {

    public $ano;
    public $mes;
    public $tipo;
    public $anexo;

    /**
     * Rotear para o tipo de montagem da matriz do anexo I
     * 
     * @param array $dados
     * @return aray
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function manipularAnexoI($dados) {

        $this->ano = $dados['ano'];
        $this->mes = $dados['mes'];
        $this->tipo = $dados['formato'];
        $this->ug = $dados['ug'];

        $negocio = new Orcamento_Business_Negocio_Gerarrelatoriocnj();

        if ($this->tipo ==
                Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_HTML) {
            $arrayDB = $negocio->consultarDadosAnexoIHtml($dados);
        } elseif ($this->tipo ==
                Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_EXCEL) {
            $arrayDB = $negocio->consultarDadosAnexoIExcel($dados);
        }



        $matriz = $this->montarMatrizAnexoI($arrayDB);

        return $matriz;
    }

    /**
     * Rotear para o tipo de montagem da matriz do anexo II
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function manipularAnexoII($dados) {

        $this->ano = $dados['REGC_AA_REGRA'];
        $this->mes = $dados['IMPA_IC_MES'];
        $this->tipo = $dados['TIPO_ANEXO'];
        $this->ug = $dados['UG_TODAS'];

        $negocio = new Orcamento_Business_Negocio_Gerarrelatoriocnj();

        if ($this->tipo ==
                Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_HTML) {
            $arrayDB = $negocio->consultarDadosAnexoIIHtml($dados);
        } elseif ($this->tipo ==
                Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_EXCEL) {
            $arrayDB = $negocio->consultarDadosAnexoIIExcel($dados);
        }

        $matriz = $this->montarMatrizAnexoII($arrayDB);

        return $matriz;
    }

    /**
     * Efetua montagem da matriz para utilização no Anexo I, dependendo por
     * excel ou em html.
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function montarMatrizAnexoI($dados) {

        $matriz = array();

        switch ($this->tipo) {

            case Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_HTML:

                foreach ($dados as $valor) {

                    $incID = $valor['INCI_ID_INCISO'];
                    $alinID = $valor['ALIN_ID_ALINEA'];
                    $ugID = $valor['UNGE_CD_UG'];

                    $matriz['ug'][$ugID]['sigla'] = $valor['UNGE_SG_UG'];
                    $matriz['ug'][$ugID]['secao'] = $valor['UNGE_SG_SECAO'];
                    $matriz['ug'][$ugID]['orgao'] = $valor['UNGE_DS_UG'];
                    $matriz['ug'][$ugID]['autoridade'] = $valor['UNGE_SG_AUTORIDADE_MAXIMA'];

                    $matriz['ug'][$ugID]['inciso'][$incID]['valor'] = $valor['INCI_VL_INCISO'];
                    $matriz['ug'][$ugID]['inciso'][$incID]['descricao'] = $valor['INCI_DS_INCISO'];
                    $matriz['ug'][$ugID]['inciso'][$incID]['total'] += $valor['TOTAL'];

                    $matriz['ug'][$ugID]['inciso'][$incID]['alinea'][$alinID]['valor'] = $valor['ALIN_VL_ALINEA'];
                    $matriz['ug'][$ugID]['inciso'][$incID]['alinea'][$alinID]['descricao'] = $valor['ALIN_DS_ALINEA'];
                    $matriz['ug'][$ugID]['inciso'][$incID]['alinea'][$alinID]['total'] = $valor['TOTAL'];
                }

                break;

            case Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_EXCEL:

                foreach ($dados as $valor) {

                    $incID = $valor['INCI_ID_INCISO'];
                    $alinID = $valor['ALIN_ID_ALINEA'];

                    $matriz['inciso'][$incID]['valor'] = $valor['INCI_VL_INCISO'];
                    $matriz['inciso'][$incID]['descricao'] = $valor['INCI_DS_INCISO'];
                    $matriz['inciso'][$incID]['total'] += $valor['TOTAL'];

                    $matriz['inciso'][$incID]['alinea'][$alinID]['valor'] = $valor['ALIN_VL_ALINEA'];
                    $matriz['inciso'][$incID]['alinea'][$alinID]['descricao'] = $valor['ALIN_DS_ALINEA'];
                    $matriz['inciso'][$incID]['alinea'][$alinID]['total'] = $valor['TOTAL'];
                }

                break;
        }

        return $matriz;
    }

    /**
     * Monta a matriz de preenchimento para o anexo II, dependendo se é para
     * excel ou para HTML
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function montarMatrizAnexoII($dados) {

        $matriz = array();
        $formato = new Trf1_Orcamento_Valor();

        foreach ($dados as $indice => $valor) {

            $dotacaoAutorizada = $valor['DOTACAO'] + $valor['SUPLEMENTACAO'] - $valor['CANCELAMENTO'] - $valor['CONTINGENCIAMENTO'];
            $dotacaoLiquida = $dotacaoAutorizada + $valor['PROVISAO'] + $valor['DESTAQUE'];
            $porcentagemEmpenhado = round(($valor['EMPENHADO'] / $dotacaoLiquida)*100);
            $porcentagemLiquidado = round(($valor['LIQUIDADO'] / $dotacaoLiquida)*100);            

            $matriz['anexo'][$indice]['FUNCIONAL_PROGRAMATICA'] = $valor['FUNCIONAL_PROGRAMATICA'];
            $matriz['anexo'][$indice]['PROGRAMA_ACAO'] = $valor['PROGRAMA_ACAO'];
            $matriz['anexo'][$indice]['FUNCAO_SUBFUNCAO'] = $valor['FUNCAO_SUBFUNCAO'];
            $matriz['anexo'][$indice]['IMPO_CD_ESFERA'] = $valor['IMPO_CD_ESFERA'];
            $matriz['anexo'][$indice]['GND'] = $valor['GND'];
            $matriz['anexo'][$indice]['IMPO_CD_FONTE'] = $valor['IMPO_CD_FONTE'];
            $matriz['anexo'][$indice]['IMPO_CD_PTRES'] = $valor['IMPO_CD_PTRES'];
            $matriz['anexo'][$indice]['VL_DOTACAO'] = $formato->retornaNumeroFormatado($valor['DOTACAO']);
            $matriz['anexo'][$indice]['VL_SUPLEMENTACAO'] = $formato->retornaNumeroFormatado($valor['SUPLEMENTACAO']);
            $matriz['anexo'][$indice]['VL_CANCELAMENTO'] = $formato->retornaNumeroFormatado($valor['CANCELAMENTO']);
            $matriz['anexo'][$indice]['VL_CONTINGENCIAMENTO'] = $formato->retornaNumeroFormatado($valor['CONTINGENCIAMENTO']);
            $matriz['anexo'][$indice]['VL_DOTACAO_AUTORIAZADA'] = $formato->retornaNumeroFormatado($dotacaoAutorizada);
            $matriz['anexo'][$indice]['VL_PROVISAO'] = $formato->retornaNumeroFormatado($valor['PROVISAO']);
            $matriz['anexo'][$indice]['VL_DESTAQUE'] = $formato->retornaNumeroFormatado($valor['DESTAQUE']);
            $matriz['anexo'][$indice]['VL_DOTACAO_LIQUIDA'] = $formato->retornaNumeroFormatado($dotacaoAutorizada);
            $matriz['anexo'][$indice]['VL_EMPENHADO'] = $formato->retornaNumeroFormatado($valor['EMPENHADO']);
            $matriz['anexo'][$indice]['VL_EMPENHADO_PORCENTAGEM'] = $porcentagemEmpenhado."%";
            $matriz['anexo'][$indice]['VL_LIQUIDADO'] = $formato->retornaNumeroFormatado($valor['LIQUIDADO']);
            $matriz['anexo'][$indice]['VL_LIQUIDADO_PORCENTAGEM'] = $porcentagemLiquidado."%";
            $matriz['anexo'][$indice]['VL_PAGO'] = $formato->retornaNumeroFormatado($valor['PAGO']);

        }   

        /*
        switch ($this->tipo) {
            case Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_EXCEL:

                $matriz['mes'] = $this->mes;
                $matriz['ano'] = $this->ano;

                foreach ($dados as $indice => $valor) {

                    $funcProgFull = $valor['PTRS_CD_PT_COMPLETO'];
                    if (!empty($funcProgFull)) {
                        $funcProg = substr($funcProgFull, 6, 7);
                        $funcSub = substr($funcProgFull, 0, 5);
                    } else {
                        $funcSub = $funcProg = "---";
                    }

                    $procAcFull = $valor['PTRS_DS_PROGRAMA_ACAO'];
                    $semDescPTRES = "{$valor['IMPO_CD_PTRES']} - [descrição não encontrada]";

                    $progAc = empty($procAcFull) ? $semDescPTRES : $procAcFull;

                    $natu = $valor['IMPO_CD_NATUREZA_DESPESA'];
                    $gnd = substr($natu, 1, 1);

                    $matriz['anexo'][$indice]['funcional_programatica'] = $funcProg;

                    $matriz['anexo'][$indice]['programa_acao'] = $progAc;

                    $matriz['anexo'][$indice]['funcao_subfuncao'] = $funcSub;
                    $matriz['anexo'][$indice]['esfera'] = $valor['IMPO_CD_ESFERA'];
                    $matriz['anexo'][$indice]['gnd'] = $gnd;
                    $matriz['anexo'][$indice]['fonte'] = substr($valor['IMPO_CD_FONTE'], 1, 3);

                    $esf = $valor['IMPO_CD_ESFERA'];
                    $ptr = $valor['IMPO_CD_PTRES'];
                    $fte = $valor['IMPO_CD_FONTE'];

                    $arrayEntra = array(2, 3, 4, 5, 6, 7, 8, 9, 10);

                    for ($i = 1; $i <= 11; $i++) {

                        if (!in_array($i, $arrayEntra)) {
                            continue;
                        }

                        $dados = $this->retornarDadosPTRES($esf, $fte, $ptr, $i);
                        $valTot = $this->consultarPorPTRES($dados, array(90032));



                        if (!empty($valTot)) {
                            $retVal = $valTot;
                        } else {
                            $retVal = "0";
                        }

                        $matriz['anexo'][$indice]['totais'][$i] = $retVal;
                    }
                }

                break;

            case Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_HTML:

                $dadoPTRES = array();
                $matriz = array();

                /*
                switch ($this->mes) {
                    case 'IMPO_VL_TOTAL_JAN':
                        $this->mes = 1;
                        break;
                    case 'IMPO_VL_TOTAL_FEV':
                        $this->mes = 2;
                        break;
                    case 'IMPO_VL_TOTAL_MAR':
                        $this->mes = 3;
                        break;
                    case 'IMPO_VL_TOTAL_ABR':
                        $this->mes = 4;
                        break;
                    case 'IMPO_VL_TOTAL_JUL':
                        $this->mes = 5;
                        break;
                    case 'IMPO_VL_TOTAL_JUN':
                        $this->mes = 6;
                        break;
                    case 'IMPO_VL_TOTAL_JUL':
                        $this->mes = 7;
                        break;
                    case 'IMPO_VL_TOTAL_AGO':
                        $this->mes = 8;
                        break;
                    case 'IMPO_VL_TOTAL_SET':
                        $this->mes = 9;
                        break;
                    case 'IMPO_VL_TOTAL_OUT':
                        $this->mes = 10;
                        break;
                    case 'IMPO_VL_TOTAL_NOV':
                        $this->mes = 11;
                        break;
                    case 'IMPO_VL_TOTAL_DEZ':
                        $this->mes = 12;
                        break;
                }
                */
                /*
                $dadoPTRES['mes'] = $this->mes;
                $dadoPTRES['ano'] = $this->ano;
                $dadoPTRES['ug'] = $this->ug;

                $matriz['mes'] = $this->mes;
                $matriz['ano'] = $this->ano;

                // percorre todas as UGs
                foreach ($dados as $indice => $valor) {

                    $ug = $valor['UNGE_CD_UG'];
                    $matriz['ug'][$indice]['orgao'] = $valor['UNGE_SG_UG'];

                    $negocio = new Orcamento_Business_Negocio_Gerarrelatoriocnj();

                    $arrayUG = $negocio->driver($dadoPTRES, $ug);

                    // percorre os dados de dentro das UGs
                    foreach ($arrayUG as $indUG => $valUG) {

                        try {
                            $funcProgFull = $valUG['PTRS_CD_PT_COMPLETO'];

                        if (!empty($funcProgFull)) {
                            $funcProg = substr($funcProgFull, 6, 7);
                            $funcSub = substr($funcProgFull, 0, 5);
                        } else {
                            $funcSub = $funcProg = "----";
                        }

                        $procAcFull = $valUG['PTRS_DS_PROGRAMA_ACAO'];
                        
                        $semDescPTRES = "{$valUG['IMPO_CD_PTRES']} - [descrição não encontrada]";

                        $progAc = empty($procAcFull) ? $semDescPTRES : $procAcFull;

                        $natu = $valUG['IMPO_CD_NATUREZA_DESPESA'];
                        $gnd = substr($natu, 1, 1);

                        // dados da UG
                        $matriz['ug'][$indice]['dados'][$indUG]['funcional_programatica'] = $funcProg;
                        $matriz['ug'][$indice]['dados'][$indUG]['programa_acao'] = $progAc;
                        $matriz['ug'][$indice]['dados'][$indUG]['funcao_subfuncao'] = $funcSub;
                        $matriz['ug'][$indice]['dados'][$indUG]['esfera'] = $valUG['IMPO_CD_ESFERA'];
                        $matriz['ug'][$indice]['dados'][$indUG]['gnd'] = $gnd;
                        $matriz['ug'][$indice]['dados'][$indUG]['fonte'] = substr($valUG['IMPO_CD_FONTE'], 1, 3);

                        $esf = $valUG['IMPO_CD_ESFERA'];
                        $ptr = $valUG['IMPO_CD_PTRES'];
                        $fte = $valUG['IMPO_CD_FONTE'];

                        

                        // tipos de arquivos que deverão ter a soma concretizada
                        $arrayEntra = array(2, 3, 4, 5, 6, 7, 8, 9, 10);

                        for ($i = 1; $i <= 11; $i++) {
                            if (!in_array($i, $arrayEntra)) {
                                continue;
                            }

                            $datPTRES = $this->retornarDadosPTRES($esf, $fte, $ptr, $i, $ug);

                            $valTot = $this->consultarPorPTRES($datPTRES, array());

                            if (!empty($valTot)) {
                                $retVal = $valTot;
                            } else {
                                $retVal = "0";
                            }

                            $cC[$i] = $matriz['ug'][$indice]['dados'][$indUG]
                                    ['totais'][$i] = $retVal;
                        }

                        // calculo da RN094
                        $calcK = $cC[4] + $cC[2] - $cC[5] - $cC[6];

                        // calculo da RN095
                        $calcN = $calcK + $cC[7] + $cC[8];

                        // calculo da RN096
                        try {
                            $calcP = ($cC[9] / $calcN) * 100;
                        } catch (Exception $e) {
                            $calcP = 0;
                        }

                        // calculo da RN097
                        try {
                            $calcR = ($cC[3] / $calcN) * 100;
                        } catch (Exception $e) {
                            $calcR = 0;
                        }

                        // calculo da RN098
                        try {
                            $calcT = ($cC[10] / $calcN) * 100;
                        } catch (Exception $e) {
                            $calcT = 0;
                        }

                        // seta na matriz campos de calculo
                        $matriz['ug'][$indice]['dados'][$indUG]['calc']['k'] = $calcK;
                        $matriz['ug'][$indice]['dados'][$indUG]['calc']['n'] = $calcN;
                        $matriz['ug'][$indice]['dados'][$indUG]['calc']['p'] = $calcP;
                        $matriz['ug'][$indice]['dados'][$indUG]['calc']['r'] = $calcR;
                        $matriz['ug'][$indice]['dados'][$indUG]['calc']['t'] = $calcT;

                        } catch (Exception $e) {
                            Zend_Debug::dump($e);
                            die;
                        }
                        

                    }

                }

                break;
        }
        */
        return $matriz;
    }

    /**
     * Consultar por PTRES
     * 
     * @param type $dados
     * @return type
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarPorPTRES($dados, $ugArray) {

        $negocio = new Orcamento_Business_Negocio_Gerarrelatoriocnj();
        $valorTotal = $negocio->consultarPorPTRES($dados, $ugArray);

        return $valorTotal;
    }

    /**
     * Retorna array para efetuar consulta por PTRES
     * 
     * @param int $esfera
     * @param int $fonte
     * @param int $ptres
     * @param int $tipo
     * @param int $ug
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornarDadosPTRES($esfera, $fonte, $ptres, $tipo, $ug = "") {
        $dados = array();

        $dados['ano'] = substr($this->ano, 2, 2);
        $dados['mes'] = $this->mes;
        $dados['esfera'] = $esfera;
        $dados['fonte'] = $fonte;
        $dados['ptres'] = $ptres;
        $dados['tipo'] = $tipo;
        $dados['ug'] = $ug;

        return $dados;
    }

}