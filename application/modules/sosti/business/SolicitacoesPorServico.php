<?php

/**
 * Classe de negócio para o Relatório de Solicitações por Serviço
 * 
 * @author Daniel Rodrigues <daniel.fernandes@trf1.jus.br>
 */
class Sosti_Business_SolicitacoesPorServico
{

    public $_mapper;

    public function __construct() {
        $this->_mapper = new Sosti_Model_DataMapper_SolicitacoesPorServico();
    }

    public function buscaUnidadeValidacaoForm($parametros, $elementoForm) {

        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $trf1_array = explode('|', $parametros['TRF1_SECAO']);
        $lotacao_array = $rhCentralLotacao->getLotacaobySecao($trf1_array[0], $trf1_array[1], $trf1_array[2]);
        foreach ($lotacao_array as $lota) {
            $valor = $lota["LOTA_SIGLA_SECAO"] . '|' . $lota['LOTA_COD_LOTACAO'];
            $texto = $lota["LOTA_SIGLA_LOTACAO"] . ' - ' . $lota["LOTA_DSC_LOTACAO"] . ' - ' . $lota["LOTA_COD_LOTACAO"] . ' - ' . $lota["LOTA_SIGLA_SECAO"] . ' - ' . $lota["FAMILIA_LOTACAO"];
            $elementoForm->addMultiOptions(array($valor => $texto));
        }
        return $elementoForm;
    }

    public function buscaSubsecaoValidacaoForm($parametros, $elementoForm) {

        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $trf1_array = explode('|', $parametros['TRF1_SECAO']);
        $subsecao_array = $rhCentralLotacao->getSubSecoes($trf1_array[0], $trf1_array[1]);
        foreach ($subsecao_array as $sub) {
            $valor = $sub["LOTA_SIGLA_SECAO"] . '|' . $sub['LOTA_COD_LOTACAO'] . '|' . $sub["LOTA_TIPO_LOTACAO"];
            $texto = $sub["LOTA_SIGLA_LOTACAO"] . ' - ' . $sub["LOTA_DSC_LOTACAO"] . ' - ' . $sub["LOTA_COD_LOTACAO"] . ' - ' . $sub["LOTA_SIGLA_SECAO"] . ' - ' . $sub["LOTA_LOTA_COD_LOTACAO_PAI"];
            $elementoForm->addMultiOptions(array($valor => $texto));
        }
        return $elementoForm;
    }

    public function buscaGrupoServValidacaoForm($parametros, $elementoForm) {

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $lotacao_array = explode('|', $parametros['LOTA_COD_LOTACAO']);
        $sgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao($lotacao_array[0], $lotacao_array[1]);
        foreach ($sgrsGrupoServico as $sgrs) {
            $valor = '"' . $sgrs["SGRS_ID_GRUPO"] . '"';
            $texto = $sgrs["SGRS_DS_GRUPO"];
            $elementoForm->addMultiOptions(array($valor => $texto));
        }
        return $elementoForm;
    }

    public function buscaCatServValidacaoForm($parametros, $elementoForm) {
        $SosTbCtssCategServSistema = new Application_Model_DbTable_SosTbCtssCategServSistema();
        $servico_array = $SosTbCtssCategServSistema->fetchAll(null, 'CTSS_NM_CATEGORIA_SERVICO ASC');
        if (($parametros["LOTA_COD_LOTACAO"] == 'TR|1783|2') || ($parametros["LOTA_COD_LOTACAO"] == 'TR|1784|2') || ($parametros["LOTA_COD_LOTACAO"] == 'TR|1155|2')) {
            foreach ($servico_array as $serv) {
                $valor = $serv['CTSS_ID_CATEGORIA_SERVICO'];
//                $texto = $serv['CTSS_NM_CATEGORIA_SERVICO'];
//                $elementoForm->addMultiOptions(array($valor => $texto));
            }
        } else {
            $valor = '';
            $texto = 'Não existem categorias para esta caixa';
//            $elementoForm->addMultiOptions(array($valor => $texto));
        }
        return $elementoForm;
    }

    public function buscaCaixaValidacaoForm($parametros, $elementoForm) {

        $SosTbSserServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $grupo_array = explode('|', $parametros["TRF1_SECAO"]);
        $servico_array = $SosTbSserServico->getSecaoLotacaoSigla($grupo_array[0]);
        foreach ($servico_array as $serv) {
            $valor = $serv["SGRS_SG_SECAO_LOTACAO"].'|'.$serv["SGRS_CD_LOTACAO"].'|'.$serv["CXEN_ID_CAIXA_ENTRADA"];
            $texto = $serv['CXEN_DS_CAIXA_ENTRADA'];
            $elementoForm->addMultiOptions(array($valor => $texto));
        }
        return $elementoForm;
    }

    public function listAllBusiness($parametros) {
        $lotacao_array = explode('|', $parametros['LOTA_COD_LOTACAO']);
        $cdSecao = $lotacao_array[1];
        $sgSecao = $lotacao_array[0];
        $completaStatus = "";
        $dsvArray = explode('|', $parametros["TRF1_SECAO"]);
        $trfSecaoDsv = $dsvArray[1];
        if($parametros['STATUS'] == 1){
            $completaStatus = 'AND MOFA_ID_FASE IN (1000,1014)';
        }elseif($parametros['STATUS'] == 2){
            $completaStatus = 'AND MOFA_ID_FASE NOT IN (1000,1014)';
        }
        if ((($parametros['LOTA_COD_LOTACAO'] == 'TR|1783|2') || ($parametros['LOTA_COD_LOTACAO'] == 'TR|1784|2') || ($parametros['LOTA_COD_LOTACAO'] == 'TR|1155|2')) && $parametros['CTSS_NM_CATEGORIA_SERVICO'] != "") {
//        if ($parametros['SGRS_ID_GRUPO'] == '"2"' && $parametros['CTSS_NM_CATEGORIA_SERVICO'] != "") {
            $cat = $parametros['CTSS_NM_CATEGORIA_SERVICO'];
            $completaCategoria = ",SUM(DECODE(CTSS_ID_CATEGORIA_SERVICO,$cat,1,0)) CAT";
        }
        $agrupador = ($parametros['AGRUPAMENTO'] == 2) ? 'YYYY' : 'MM/YYYY';
        return $this->_mapper->listAll($cdSecao, $sgSecao, $completaStatus, $parametros['DATA_INICIAL'], $parametros['DATA_FINAL'], $agrupador, $completaCategoria);
    }

}