<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_SosTbPfmePontoFuncaoMes extends Zend_Db_Table_Abstract
{
    protected $_name = 'SOS_TB_PFME_PONTO_FUNCAO_MES';
    protected $_primary = 'PFME_ID_PONTO_FUNCAO_MES';
    protected $_sequence = 'SOS_SQ_PFME';
    

    public function getMediaUltimos6Meses()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT 
                            AVG(PFME_NR_PONTOS_FUNCAO) MEDIA_ULT_6MESES
                            FROM 
                            SOS_TB_PFME_PONTO_FUNCAO_MES
                            WHERE PFME_DT_REFERENCIA 
                            BETWEEN ADD_MONTHS(SYSDATE,-7) AND SYSDATE");
        return $stmt->fetch();
    }
}