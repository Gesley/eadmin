<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_SosTbMtsaMaterialSaida extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_MTSA_MATERIAL_SAIDA';
    protected $_primary = array('MTSA_ID_HARDWARE', 'MTSA_DT_SAIDA_MATERIAL', 'MTSA_ID_DOCUMENTO');
    protected $_sequence = 'SOS_SQ_LFHW';

    /**
     * Retorna Quantidade de ewuipamento disponível prara uso peoi ID do hardware
     * @param int $id
     */
    public function getQuantidadeHardwareDisponivel($id) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT MTEN_QT_ENTRADA_MATERIAL FROM SOS_TB_MTEN_MATERIAL_ENTRADA  WHERE MTEN_ID_HARDWARE= " . $id;
        return $db->query($stmt)->fetchAll();
    }

    /**
     * Retorna informação sobre entrada do hardware e saldo
     * @param unknown_type $HardwareID
     */
    public function getMaterialSaidaInfo($IdHardware) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT A.MTSA_ID_DOCUMENTO,
            A.MTSA_ID_HARDWARE,
            TO_CHAR(A.MTSA_DT_SAIDA_MATERIAL,'DD/MM/YYYY HH24:MI:SS') MTSA_DT_SAIDA_MATERIAL,
            A.MTSA_CD_MATRICULA,
            A.MTSA_QT_SAIDA_MATERIAL,
            A.MTSA_SG_SECAO,
            A.MTSA_CD_LOTACAO,
            C.DOCM_NR_DOCUMENTO
            FROM 
            SOS_TB_MTSA_MATERIAL_SAIDA A, SOS_TB_SSOL_SOLICITACAO B, SAD_TB_DOCM_DOCUMENTO C
            WHERE A.MTSA_ID_DOCUMENTO = B.SSOL_ID_DOCUMENTO
            AND B.SSOL_ID_DOCUMENTO = C.DOCM_ID_DOCUMENTO
            AND A.MTSA_ID_HARDWARE = $IdHardware
        ";
        return $db->query($stmt)->fetchAll();
    }

    public function todosHardwaresDocumento($idDocumento) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT
            MTSA_ID_HARDWARE
            FROM
            SOS_TB_MTSA_MATERIAL_SAIDA
            WHERE
            MTSA_ID_DOCUMENTO = $idDocumento
        ";
        return $db->fetchAll($stmt);
    }

    public function verificaPendenciaHard($idDocumento) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT *FROM
            SOS_TB_MTSA_MATERIAL_SAIDA
            WHERE
            MTSA_ID_DOCUMENTO = $idDocumento AND
            MTSA_IC_APROVACAO = 'S'
        ";
        return $db->fetchAll($stmt);
    }

}