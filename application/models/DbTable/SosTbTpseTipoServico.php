<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_SosTbTpseTipoServico extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_TPSE_TIPO_SERVICO';
    protected $_primary = 'TPSE_ID_TP_SERVICO';
    protected $_sequence = 'SOS_SQ_TPSE';

    public function getTipoServico($order) {
        if (!isset($order)) {
            $order = 'TPSE_ID_TP_SERVICO ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  TPSE_ID_TP_SERVICO,
                                    TPSE_DS_TP_SERVICO,
                                    TPSE_IC_ATIVO
                               FROM SOS_TB_TPSE_TIPO_SERVICO
                               ORDER BY $order");
        $Tiposervico = $stmt->fetchAll();
        return $Tiposervico;
    }

    public function getTipoServicoAtivo($order) {
        if (!isset($order)) {
            $order = 'TPSE_ID_TP_SERVICO ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  TPSE_ID_TP_SERVICO,
                                    TPSE_DS_TP_SERVICO,
                                    TPSE_IC_ATIVO
                               FROM SOS_TB_TPSE_TIPO_SERVICO
                              WHERE TPSE_IC_ATIVO = 'S'
                               --ORDER BY $order");
        $Tiposervico = $stmt->fetchAll();
        return $Tiposervico;
    }

    public function getServico($k) {
        if (!isset($order)) {
            $order = 'TPSE_DS_TP_SERVICO ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  TPSE_ID_TP_SERVICO ID,
                                    TPSE_DS_TP_SERVICO AS LABEL,
                                    TPSE_IC_ATIVO
                               FROM SOS_TB_TPSE_TIPO_SERVICO
                              WHERE TPSE_IC_ATIVO = 'S'
                              AND UPPER(TPSE_DS_TP_SERVICO) LIKE UPPER('$k%') 
                               ORDER BY $order");
        $Tiposervico = $stmt->fetchAll();
        return $Tiposervico;
    }
    
    public function getInfoServico($id){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT 
                TPSE_ID_TP_SERVICO,
                TPSE_DS_TP_SERVICO
            FROM
                SOS_TB_TPSE_TIPO_SERVICO
            WHERE
                TPSE_ID_TP_SERVICO = $id            
        ";
        return $db->fetchRow($stmt);
    }
    
    public function getTpServicoPorDocumento($idDocumento){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT
                TPSE_ID_TP_SERVICO,
                TPSE_DS_TP_SERVICO
            FROM 
                SOS_TB_TPSF_TIPO_SERVICO_FICHA,
                SOS_TB_TPSE_TIPO_SERVICO
            WHERE
                TPSE_ID_TP_SERVICO = TPSF_ID_TP_SERVICO AND
                TPSF_ID_DOCUMENTO = $idDocumento           
        ";
        return $db->fetchAll($stmt);
    }

}