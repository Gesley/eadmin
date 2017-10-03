<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_SosTbLsfwSoftware extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LSFW_SOFTWARE';
    protected $_primary = 'LSFW_ID_SOFTWARE';
    protected $_sequence = 'SOS_SQ_LSFW';

    public function getSoftwares($order) {
        if (!isset($order)) {
            $order = 'LSFW_DS_SOFTWARE ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT 
                LSFW_ID_SOFTWARE,
                LSFW_DS_SOFTWARE,
                LSFW_ID_TP_SOFTWARE,
                LTPS_DS_TP_SOFTWARE,
                MODE_ID_MARCA,
                MARC_DS_MARCA,
                MODE_DS_MODELO,
                SUM(LISW_QT_LICENCA) AS LISW_QT_LICENCA
            FROM
                SOS_TB_LSFW_SOFTWARE
                INNER JOIN SOS_TB_LTPS_TIPO_SOFTWARE
                ON LTPS_ID_TP_SOFTWARE = LSFW_ID_TP_SOFTWARE

                INNER JOIN OCS_TB_MODE_MODELO MO
                ON LSFW_ID_MODELO = MO.MODE_ID_MODELO

                INNER JOIN OCS_TB_MARC_MARCA M 
                ON MODE_ID_MARCA = M.MARC_ID_MARCA

                LEFT JOIN SOS_TB_LISW_LICENCA_SOFTWARE
                ON LSFW_ID_SOFTWARE = LISW_ID_SOFTWARE
            GROUP BY
                LSFW_ID_SOFTWARE,
                LSFW_DS_SOFTWARE,
                LSFW_ID_TP_SOFTWARE,
                LTPS_DS_TP_SOFTWARE,
                MODE_ID_MARCA,
                MARC_DS_MARCA,
                MODE_DS_MODELO
            ORDER BY $order
        ");

        $LstSoftware = $stmt->fetchAll();
        return $LstSoftware;
    }

    /**
     * Retorna o número do tombo que estão instalados o software
     * @param int $id - software
     * @return array
     */
    public function getSoftwareTombo($id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT A.LFSW_ID_DOCUMENTO,
                           C.SSOL_NR_TOMBO, 
                           A.LFSW_ID_SOFTWARE, 
                           B.LSFW_DS_SOFTWARE, 
                           B.LSFW_IC_APROVACAO_INSTALACAO
                      FROM SOS_TB_LFSW_FICHA_SOFTWARE A, 
                           SOS_TB_LSFW_SOFTWARE B, SOS_TB_SSOL_SOLICITACAO C
                     WHERE A.LFSW_ID_SOFTWARE = B.LSFW_ID_SOFTWARE
                       AND B.LSFW_ID_SOFTWARE = $id
                       AND C.SSOL_ID_DOCUMENTO = A.LFSW_ID_DOCUMENTO";

        $LstSoftware = $db->query($stmt)->fetchAll();
        return $LstSoftware;
    }

    /**
     * Retorna detalhes do software
     * @param int $id
     * @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
     */
    public function getSoftwareInfo($id) {
        if (!isset($order)) {
            $order = 'LSFW_DS_SOFTWARE ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT  LSFW_ID_SOFTWARE,
 	    LSFW_DS_SOFTWARE,
 	    LSFW_IC_APROVACAO_INSTALACAO,
 	    LSFW_ID_TP_SOFTWARE,
 	    LSFW_ID_MODELO,
 	    LSFW_DT_AQUISICAO,
 	    LSFW_DT_VALIDADE_LICENCA,
 	    LSFW_IC_SOFTWARE_LIVRE,
 	    LSFW_NR_PROCESSO_COMPRA,
 	    LSFW_CD_TIPO_DOC_CONTRATO,
 	    LSFW_NR_ADITAMENTO_CONTRATO,
 	    LSFW_NR_CONTRATO,
 	    LSFW_NR_PROCESSO_CONTRATO,
 	    LSFW_NR_TERMO,
 	    LSFW_AA_TERMO,
 	    LSFW_CD_TIPO_TERMO,
 	    LSFW_NR_TOMBO,
 	    LSFW_SG_TOMBO,
 	    LSFW_QT_ADQUIRIDA,
 	    LSFW_NR_DOC_ORIGEM,
 	    MA.MARC_DS_MARCA,
 	    LI.LISW_QT_LICENCA,
 	    MO.MODE_DS_MODELO
 	    FROM SOS_TB_LSFW_SOFTWARE, OCS_TB_MODE_MODELO MO, OCS_TB_MARC_MARCA MA, SOS_TB_LISW_LICENCA_SOFTWARE LI
 	    WHERE LSFW_ID_MODELO = MO.MODE_ID_MODELO
 	    AND MO.MODE_ID_MARCA = MA.MARC_ID_MARCA
 	    AND LI.LISW_ID_SOFTWARE = LSFW_ID_SOFTWARE
 	    AND LSFW_ID_SOFTWARE= " . $id;


        $LstSoftware = $db->query($stmt)->fetch();
        return $LstSoftware;
    }

    /**
     * Busca as informações básicas softwares por nome, filtrados por um modelo
     * @param type termo
     * @return array 
     * @author Daniel Rodrigues
     */
    public function getSoftware($modelo) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT
                LSFW_ID_SOFTWARE,
                LSFW_DS_SOFTWARE
            FROM
                SOS_TB_LSFW_SOFTWARE,
                OCS_TB_MODE_MODELO
            WHERE
                LSFW_ID_MODELO = MODE_ID_MODELO AND
                MODE_ID_MODELO = $modelo
            ORDER BY 
                LSFW_DS_SOFTWARE
        ";

        $LstSoftware = $db->query($stmt)->fetchAll();
        return $LstSoftware;
    }
    
    public function getSoftwaresPorDocumento($idDocumento){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                LSFW_ID_SOFTWARE,
                LSFW_DS_SOFTWARE,
                LSSA_IC_APROVACAO
            FROM
                SOS_TB_LSSA_LICENCA_SOFT_SAIDA,
                SOS_TB_LSFW_SOFTWARE
            WHERE
                LSSA_ID_SOFTWARE = LSFW_ID_SOFTWARE AND
                LSSA_ID_DOCUMENTO = $idDocumento
        "; 
        return $db->query($sql)->fetchAll();
    }
    
    
    public function getQtdLicencasSaida($id){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT
            COUNT(
                LSSA_ID_LICEN_SOFT_SAIDA
            ) AS QTD_SAIDA
            FROM 
                SOS_TB_LSSA_LICENCA_SOFT_SAIDA
            WHERE
                LSSA_IC_APROVACAO != 'R' AND
                LSSA_ID_SOFTWARE = $id            
        ";
        return $db->fetchRow($sql);
    }
    
    public function getQtdTotalSoftware($id){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                SUM(LISW_QT_LICENCA) AS QTD_TOTAL
            FROM
                SOS_TB_LISW_LICENCA_SOFTWARE
            WHERE
                LISW_ID_SOFTWARE = $id           
        ";
        return $db->fetchRow($sql);
    } 
    
}