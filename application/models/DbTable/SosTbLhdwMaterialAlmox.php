<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_SosTbLhdwMaterialAlmox extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LHDW_MATERIAL_ALMOX';
    protected $_primary = 'LHDW_ID_HARDWARE';
    protected $_sequence = 'SOS_SQ_LHDW';

    public function getHardwares($order) {
        if (!isset($order)) {
            $order = 'LSFW_ID_SOFTWARE DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT 
                LHDW_ID_HARDWARE,
                LHDW_DS_HARDWARE,
                LHDW_CD_MATERIAL,
                MODE_ID_MARCA,
                MARC_DS_MARCA,
                LHDW_CD_MODELO,
                MODE_DS_MODELO,
                LHDW_NR_PROCESSO,
                LHDW_SIGLA_SECAO || '-' || LHDW_COD_LOTACAO AS SECAO_SUBSECAO , 
                SUM(MTEN_QT_ENTRADA_MATERIAL) AS MTEN_QT_ENTRADA_MATERIAL
              FROM
                SOS_TB_LHDW_MATERIAL_ALMOX
                INNER JOIN OCS_TB_MODE_MODELO
                ON MODE_ID_MODELO = LHDW_CD_MODELO

                INNER JOIN OCS_TB_MARC_MARCA
                ON MARC_ID_MARCA = MODE_ID_MARCA

                LEFT JOIN SOS_TB_MTEN_MATERIAL_ENTRADA
                ON MTEN_ID_HARDWARE = LHDW_ID_HARDWARE
              GROUP BY 
                LHDW_ID_HARDWARE, 
                LHDW_DS_HARDWARE, 
                LHDW_CD_MATERIAL,
                MODE_ID_MARCA,
                MARC_DS_MARCA,
                LHDW_CD_MODELO,
                MODE_DS_MODELO,
                LHDW_NR_PROCESSO,
                LHDW_SIGLA_SECAO || '-' || LHDW_COD_LOTACAO
             ORDER BY $order
        ");
        $Hardware = $stmt->fetchAll();
        return $Hardware;
    }

    public function getHardwareMarcaModelo($id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT 
                LHDW_ID_HARDWARE,
                LHDW_DS_HARDWARE,
                LHDW_CD_MATERIAL,
                MODE_ID_MARCA,
                MARC_DS_MARCA,
                MARC_ID_MARCA,
                LHDW_CD_MODELO,
                MODE_DS_MODELO,
                LHDW_SIGLA_SECAO,
                LHDW_COD_LOTACAO,
                LHDW_ID_TP_USUARIO,
                LHDW_DS_OBSERVACAO,
                LHDW_NR_SERIE,
                LHDW_NR_PROCESSO
            FROM 
                SOS_TB_LHDW_MATERIAL_ALMOX,
                OCS_TB_MODE_MODELO,
                OCS_TB_MARC_MARCA
            WHERE 
                LHDW_CD_MODELO = MODE_ID_MODELO
                AND MODE_ID_MARCA = MARC_ID_MARCA   
                AND LHDW_ID_HARDWARE = '$id'");
        $HardwareMarcaModelo = $stmt->fetch();
        return $HardwareMarcaModelo;
    }

    public function getMaterialAmox($termo) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                LHDW_ID_HARDWARE AS ID,
                LHDW_CD_MATERIAL || ' - ' || LHDW_DS_HARDWARE AS LABEL
            FROM
                SOS_TB_LHDW_MATERIAL_ALMOX
            WHERE
                (LHDW_DS_HARDWARE LIKE '%$termo%')
                OR LHDW_CD_MATERIAL LIKE '%$termo%'
                ORDER BY LHDW_CD_MATERIAL, LHDW_DS_HARDWARE
            ";

        $rows = $db->fetchAll($sql);
        return $rows;
    }

    public function getListaMaterialAmox($secao, $subsecao, $marca, $modelo) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                LHDW_ID_HARDWARE,
                LHDW_DS_HARDWARE,
                LHDW_CD_MATERIAL
            FROM 
                SOS_TB_LHDW_MATERIAL_ALMOX,
                OCS_TB_MARC_MARCA,
                OCS_TB_MODE_MODELO
            WHERE
                LHDW_CD_MODELO = MODE_ID_MODELO AND 
                MODE_ID_MARCA = MARC_ID_MARCA AND 
                MARC_ID_MARCA = $marca AND
                MODE_ID_MODELO = $modelo AND
                LHDW_SIGLA_SECAO = '$secao' AND
                LHDW_COD_LOTACAO = $subsecao
            ORDER BY LHDW_CD_MATERIAL, LHDW_DS_HARDWARE
            ";

        $rows = $db->fetchAll($sql);
        return $rows;
    }
    
    public function getListaMaterialAmoxPorSecao($secao, $subsecao) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                LHDW_ID_HARDWARE,
                LHDW_DS_HARDWARE,
                LHDW_CD_MATERIAL,
                MARC_DS_MARCA
            FROM 
                SOS_TB_LHDW_MATERIAL_ALMOX,
                OCS_TB_MODE_MODELO,
                OCS_TB_MARC_MARCA
            WHERE
                LHDW_SIGLA_SECAO = '$secao' AND
                LHDW_COD_LOTACAO = $subsecao AND
                LHDW_CD_MODELO = MODE_ID_MODELO AND
                MODE_ID_MARCA = MARC_ID_MARCA
            ORDER BY LHDW_CD_MATERIAL, LHDW_DS_HARDWARE
            ";

        $rows = $db->fetchAll($sql);
        return $rows;
    }
    
    public function getMaterialAlmoxPorDocumento($idDocumento) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT
                LHDW_ID_HARDWARE,
                LHDW_DS_HARDWARE,
                MTSA_IC_APROVACAO,
                MTSA_QT_SAIDA_MATERIAL,
                MTSA_QT_SOLIC_SAIDA_MATERIAL,
                MARC_DS_MARCA,
                LHDW_CD_MATERIAL
            FROM
                SOS_TB_LHDW_MATERIAL_ALMOX,
                SOS_TB_MTSA_MATERIAL_SAIDA,
                OCS_TB_MODE_MODELO,
                OCS_TB_MARC_MARCA
            WHERE
                MTSA_ID_HARDWARE = LHDW_ID_HARDWARE AND
                MTSA_ID_DOCUMENTO = $idDocumento AND
                LHDW_CD_MODELO = MODE_ID_MODELO AND
                MODE_ID_MARCA = MARC_ID_MARCA
            ";

        return $db->fetchAll($sql);
    }
    
    public function getQtdMaterialSaida($id){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                SUM(
                    DECODE(MTSA_IC_APROVACAO,'S',MTSA_QT_SOLIC_SAIDA_MATERIAL,MTSA_QT_SAIDA_MATERIAL )
                ) AS QTD_SAIDA
            FROM
                SOS_TB_MTSA_MATERIAL_SAIDA
            WHERE
                MTSA_ID_HARDWARE = $id AND
                MTSA_IC_APROVACAO != 'R'
            ";
        
        return $db->fetchRow($sql);  
    }
    
    public function getQtdTotalMaterial($id){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                SUM(MTEN_QT_ENTRADA_MATERIAL) AS QTD_TOTAL
            FROM
                SOS_TB_MTEN_MATERIAL_ENTRADA
            WHERE
                MTEN_ID_HARDWARE = $id            
        ";
        return $db->fetchRow($sql);
    } 

}