<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbSaviAviso extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SAVI_AVISO';
    protected $_primary = 'SAVI_ID_AVISO';
    protected $_sequence = 'SOS_SQ_SAVI';


    public function getAvisosAtivos($order)
    {
        if ( !isset($order) ) {
            $order = 'SAVI_DH_CADASTRO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.SAVI_ID_AVISO, A.SAVI_DH_CADASTRO, A.SAVI_CD_MATR_CAD,
                                   A.SAVI_DS_AVISO, A.SAVI_SG_SECAO_LOTACAO, A.SAVI_CD_LOTACAO,
                                   A.SAVI_IC_VISIBILIDADE VISIBILIDADE,
                                   A.SAVI_DH_EXCLUSAO, A.SAVI_CD_MATR_EXC
                            FROM   SOS_TB_SAVI_AVISO A
                            WHERE  A.SAVI_DH_EXCLUSAO IS NULL
                            ORDER  BY $order");
        return $stmt->fetchAll();
    }

}