<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbSoslSolicitacaoSla extends Zend_Db_Table_Abstract
{
    protected $_name = 'SOS_TB_SOSL_SOLICITACAO_SLA';
    protected $_primary = 'SOSL_ID_SEQUENCIAL_SLA';
    protected $_sequence = 'SOS_SQ_SOSL';

    public function getSolicitacaoSla($sosl_id_documento, $sosl_id_grupo, $sosl_id_indicador)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT SOSL_ID_SEQUENCIAL_SLA,
                       SOSL_ID_DOCUMENTO,
                       SOSL_ID_GRUPO,
                       SOSL_ID_INDICADOR,
                       TO_CHAR(SOSL_DH_INICIO_SLA,'DD/MM/YYYY HH24:MI:SS') SOSL_DH_INICIO_SLA,
                       TO_CHAR(SOSL_DH_FIM_SLA,'DD/MM/YYYY HH24:MI:SS') SOSL_DH_FIM_SLA,
                       SOSL_QT_TEMPO_UTIL,
                       SOSL_IC_REICIDENCIA
                FROM   SOS_TB_SOSL_SOLICITACAO_SLA ";
        $q .= " WHERE SOSL_ID_DOCUMENTO = $sosl_id_documento
                AND SOSL_ID_GRUPO = $sosl_id_grupo   ";
        $q .= ($sosl_id_indicador)?(" AND SOSL_ID_INDICADOR = $sosl_id_indicador "):("");
        $stmt = $db->query($q);
        return $stmt->fetchAll();
    }
    
    public function setAtualizaSolicitacaoSla($sosl_dh_fim_sla, $sosl_qt_tempo_util, 
                                              $sosl_id_documento, $sosl_id_grupo, 
                                              $sosl_id_indicador)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "UPDATE SOS_TB_SOSL_SOLICITACAO_SLA
              SET SOSL_DH_FIM_SLA = $sosl_dh_fim_sla,
              SOSL_QT_TEMPO_UTIL = $sosl_qt_tempo_util
              WHERE SOSL_ID_DOCUMENTO = $sosl_id_documento
              AND SOSL_ID_GRUPO = $sosl_id_grupo ";
        $q .= ($sosl_id_indicador)?("AND SOSL_ID_INDICADOR = $sosl_id_indicador "):("");
        //Zend_Debug::dump($q); exit;
        $stmt = $db->query($q);
    }

}