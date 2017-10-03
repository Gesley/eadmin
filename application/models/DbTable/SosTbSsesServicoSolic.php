<?php

class Application_Model_DbTable_SosTbSsesServicoSolic extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SSES_SERVICO_SOLIC';
    protected $_primary = array('SSES_ID_MOVIMENTACAO', 'SSES_DH_FASE');

    public function getsolicitacoesporTombo($tomboNr, $order = null, $codlotacao = null) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "
				SELECT
				  TTI.*,
                  SOLIC.*,
                  SOL.*,
                  SERV.SSER_DS_SERVICO,
                  DOC.*,
                  DECODE(DOC.DOCM_IC_ARQUIVAMENTO, 'S','Sim','N','NÃ£o ')as DOCM_ARQUIVADO,
                  TO_CHAR(DOC.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MM:SS') AS DATA_CADASTRO,
                  LOT.LOTA_DSC_LOTACAO
                FROM
                  SOS.SOS_TB_SSOL_SOLICITACAO SOL
                INNER JOIN SICAM.TOMBO_TI_CENTRAL TTI
                  ON SOL.SSOL_NR_TOMBO = TTI.NU_TOMBO
                INNER JOIN SAD.SAD_TB_DOCM_DOCUMENTO DOC
                    ON SOl.SSOL_ID_DOCUMENTO = DOC.DOCM_ID_DOCUMENTO
                INNER JOIN SOS.SOS_TB_SSES_SERVICO_SOLIC SOLIC
                    ON SOLIC.SSES_ID_DOCUMENTO = DOC.DOCM_ID_DOCUMENTO
                INNER JOIN SOS.SOS_TB_SSER_SERVICO SERV
                    ON SOLIC.SSES_ID_SERVICO = SERV.SSER_ID_SERVICO
                INNER JOIN SARH.RH_CENTRAL_LOTACAO LOT
                    ON TTI.LOTA_COD_LOTACAO = LOT.LOTA_COD_LOTACAO
                WHERE
                    SOL.SSOL_NR_TOMBO = $tomboNr
		";
//        if (!empty($codlotacao)) {
//            $stmt .=" AND LOT.LOTA_SIGLA_SECAO = '$codlotacao' ";
//        }
        if (!is_null($order)) {
            $stmt .= " ORDER BY $order";
        }
        return $db->query($stmt)->fetchAll();
    }

    public function getdatavideoconferencia($idDocumento) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
				SELECT DISTINCT TO_CHAR(SSES_DT_INICIO_VIDEO,'DD/MM/YYYY HH24:MI:SS') VIDEO
				FROM SOS_TB_SSES_SERVICO_SOLIC
                WHERE  SSES_ID_DOCUMENTO = $idDocumento
				AND  TO_CHAR(SSES_DT_INICIO_VIDEO,'DD/MM/YYYY HH24:MI:SS') < TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS')
				");

        return $stmt->fetch();
    }

}