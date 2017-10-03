<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_SosTbLtpsTipoSoftware extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LTPS_TIPO_SOFTWARE';
    protected $_primary = 'LTPS_ID_TP_SOFTWARE';
    protected $_sequence = 'SOS_SQ_LTPS';

    public function getTipoSoftware($order) {
        if (!isset($order)) {
            $order = 'LTPS_ID_TP_SOFTWARE ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LTPS_ID_TP_SOFTWARE, LTPS_DS_TP_SOFTWARE
                              FROM SOS_TB_LTPS_TIPO_SOFTWARE
                                ORDER BY $order");
        $tiposSoftware = $stmt->fetchAll();
        return $tiposSoftware;
    }

    /**
     * 
     * Retorna o lista  de  Software pelo tipo ...
     * @param int $tipo
     */
    public function getSoftwareList($tipo = null, $order = null) {
        if (is_null($order)) {
            $order = 'LTPS_DS_TP_SOFTWARE ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT LTPS_ID_TP_SOFTWARE, LTPS_DS_TP_SOFTWARE";
        $q .= " FROM SOS_TB_LTPS_TIPO_SOFTWARE ";
        if (!is_null($tipo)) {
            $q .= "WHERE LTPS_ID_TP_SOFTWARE = " . $tipo;
        }
        $q .= " ORDER BY $order";

        //zend_debug::dump($q);exit;
        $rows = $db->query($q)->fetchAll();
        return $rows;
    }

    public function getEditarTpSoftware($ltps_id_tp_software) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LTPS_ID_TP_SOFTWARE, LTPS_DS_TP_SOFTWARE
                              FROM SOS_TB_LTPS_TIPO_SOFTWARE
                             WHERE LTPS_ID_TP_SOFTWARE = $ltps_id_tp_software");
        $grupoServico = $stmt->fetch();
        return $grupoServico;
    }

    public function autoCompleteTipoSoftware($descricao) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT A.LTPS_DS_TP_SOFTWARE AS LABEL
 			FROM SOS_TB_LTPS_TIPO_SOFTWARE A
 			WHERE
 			A.LTPS_DS_TP_SOFTWARE LIKE('$descricao%')";

        return $db->query($sql)->fetchAll();
    }

    public function populaComboTipoSoftware($idDocumento, $idTipoSoft = null) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
     SELECT A.* , C.*, B.*
	 FROM sos_tb_lfsw_ficha_software A, 
     sos_tb_lsfw_software B, 
     sos_tb_lsfw_software C
 	 where A.lfsw_id_documento = $idDocumento
 	 AND B.lsfw_id_software = c.lsfw_id_software
 	 AND C.lsfw_id_software = $idTipoSoft
 	 ");
        $rows = $stmt->fetchAll();
        return $rows;
    }

    /**
     * Retorna uma lista de Software baseado no tipo de software ja salvo ja ficha de servico 
     * @param int $idDocumento
     */
    public function getSoftwareComboList($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "select B.lsfw_ds_software, B.lsfw_id_software
			from  sos_tb_lfsw_ficha_software A, sos_tb_lsfw_software B
			Where  A.lfsw_id_software = B.lsfw_id_software 
			AND	A.lfsw_id_documento = $idDocumento
    	 ";

        return $db->query($stmt)->fetchAll();
    }

    /**
     * Método usado para popular o combo(AJAX) após o form é submetido.
     * 
     */
    public function populaComboSoftwareAposSubmit($tipo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LSFW_DS_SOFTWARE, LSFW_ID_SOFTWARE 
    	 FROM SOS_TB_LSFW_SOFTWARE
    	 WHERE
    	 LSFW_ID_TP_SOFTWARE(+) = $tipo
    	 ");

        $rows = $stmt->fetchAll();
        return $rows;
    }

    /**
     * Retorna os tipos de Sooftware cadastrados no sistema.
     * @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
     */
    public function gettiposdeSoftware() {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT DISTINCT S.LTPS_DS_TP_SOFTWARE, S.LTPS_ID_TP_SOFTWARE 
        FROM SOS_TB_LTPS_TIPO_SOFTWARE S ORDER BY LTPS_DS_TP_SOFTWARE
    ";

        return $db->query($stmt)->fetchAll();
    }

    /**
     * Retorna o histórico  das licenças usadas do tipo de software. 
     * @param integer $idTipoSoftware
     */
    public function getSoftwareTipoLicencaHistorico($idTipoSoftware) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT A.LSFW_ID_TP_SOFTWARE,
        A.LSFW_DS_SOFTWARE,
        B.LFSW_ID_DOCUMENTO,
        D.DOCM_NR_DOCUMENTO,
        C.SSOL_NR_TOMBO,
        E.LOTA_SIGLA_LOTACAO,
        D.DOCM_CD_MATRICULA_CADASTRO ,
        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_CADASTRO
        FROM SOS_TB_LSFW_SOFTWARE A, 
        SOS_TB_LFSW_FICHA_SOFTWARE B, 
        SOS_TB_SSOL_SOLICITACAO C,
        SARH.RH_CENTRAL_LOTACAO E,
        SAD_TB_DOCM_DOCUMENTO D
		WHERE  A.LSFW_ID_TP_SOFTWARE =$idTipoSoftware
		AND A.LSFW_ID_SOFTWARE = B.LFSW_ID_SOFTWARE
		AND  B.LFSW_ID_DOCUMENTO = C.SSOL_ID_DOCUMENTO
		AND B.LFSW_ID_DOCUMENTO = D.DOCM_ID_DOCUMENTO
		AND D.DOCM_CD_LOTACAO_GERADORA = E.LOTA_COD_LOTACAO
		AND D.DOCM_SG_SECAO_REDATORA = E.LOTA_SIGLA_SECAO
		
        ";

        return $db->query($stmt)->fetchAll();
    }

}

