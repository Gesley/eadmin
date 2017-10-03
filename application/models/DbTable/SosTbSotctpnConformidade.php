<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbSotctpnConformidade extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SOTC_TP_N_CONFORMIDADE';
    protected $_primary = 'SOTC_ID_NAO_CONFORMIDADE';
    protected $_sequence = 'SOS_SQ_SOTC';

   
   public function getConformidades($order, $dataFimNull = false) {
        if (!isset($order)) {
            $order = " SOTC_DS_CONFORMIDADE ASC ";
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT
   						SOTC.SOTC_ID_NAO_CONFORMIDADE,
   						SOTC.SOTC_ID_INDICADOR,
   						SINS.SINS_ID_GRUPO,
   						SOTC.SOTC_CD_MATRICULA_INCLUSAO,
   						SOTC.SOTC_DS_CONFORMIDADE,
   						SOTC.SOTC_DH_INICIO_CONFORMIDADE,
   						SOTC.SOTC_DH_FIM_CONFORMIDADE
   						FROM SOS_TB_SOTC_TP_N_CONFORMIDADE SOTC, SOS_TB_SINS_INDIC_NIVEL_SERV SINS
   					 	WHERE SOTC.SOTC_ID_INDICADOR = SINS.SINS_ID_INDICADOR(+)
   						";
        if ($dataFimNull == true) {
            $stmt .= " AND SOTC.SOTC_DH_FIM_CONFORMIDADE IS NULL";
        }

        $stmt.= " ORDER BY $order";
        $rows = $db->query($stmt)->fetchAll();
        return $rows;
    }
   public function getConformidadesPorIndicador($order, $dataFimNull = false, $idIndicador) {
        if (!isset($order)) {
            $order = " SOTC_DS_CONFORMIDADE ASC ";
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT
   						SOTC.SOTC_ID_NAO_CONFORMIDADE,
   						SOTC.SOTC_ID_INDICADOR,
   						SOTC.SOTC_ID_GRUPO,
   						SOTC.SOTC_CD_MATRICULA_INCLUSAO,
   						SOTC.SOTC_DS_CONFORMIDADE,
   						SOTC.SOTC_DH_INICIO_CONFORMIDADE,
   						SOTC.SOTC_DH_FIM_CONFORMIDADE
   						FROM SOS_TB_SOTC_TP_N_CONFORMIDADE SOTC
                                                WHERE SOTC_ID_INDICADOR = $idIndicador
                                                ";
        if ($dataFimNull == true) {
            $stmt .= " AND SOTC.SOTC_DH_FIM_CONFORMIDADE IS NULL";
        }
        $stmt.= " ORDER BY $order";
        $rows = $db->query($stmt)->fetchAll();
        return $rows;
    }
   

}