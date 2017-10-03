<?php

class Sosti_Model_DataMapper_CaixaServico extends Zend_Db_Table_Abstract
{
    
    public static function caixaEntrada($uf)
    {
        $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $arrayCaixa = $caixaEntrada->getCaixas('CXEN_DS_CAIXA_ENTRADA');
        foreach ($arrayCaixa as $c) {
            $ufCaixa = end(explode(' - ', $c["CXEN_DS_CAIXA_ENTRADA"]));
            if ($ufCaixa == $uf) {
                $arrayCaixaUf[] = $c;
            }
        }
        return $arrayCaixaUf;
    }
    
    public static function servico($idCaixa)
    {
        $servico = new Application_Model_DbTable_SosTbSserServico();
        $cxGrupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $idGrupo = $cxGrupo->fetchRow('CXGS_ID_CAIXA_ENTRADA = '.$idCaixa);
        return $servico->getServicoPorGrupo($idGrupo['CXGS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
    }
}
