<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_OcsTbModeModelo extends Zend_Db_Table_Abstract {

    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_MODE_MODELO';
    protected $_primary = 'MODE_ID_MODELO';
    protected $_sequence = 'OCS_SQ_MODE';

    /**
     * Retorna a listagem de modelos
     * @param type $order
     * @return array
     */
    public function getModelo($order) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT 
                MODE_ID_MODELO,
                MODE_DS_MODELO,  
                MODE_ID_MARCA,
                MARC_DS_MARCA,
                MODE_ID_GRUPO_MAT_SERV,
                GRUP_DS_GRUPO_MAT_SERV
              FROM
                OCS_TB_MODE_MODELO
                INNER JOIN  OCS_TB_MARC_MARCA
                ON  MODE_ID_MARCA = MARC_ID_MARCA

                LEFT JOIN OCS_TB_GRUP_GRUPO_MAT_SERV
                ON MODE_ID_GRUPO_MAT_SERV = GRUP_ID_GRUPO_MAT_SERV
                
                ORDER BY $order
            ");
        return $stmt->fetchAll();
    }
    
    /**
     * Retorna um modelo buscado por ID
     * @param type $id
     */
    public function getModeloById($id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
            SELECT 
                MODE_ID_MODELO,
                MODE_DS_MODELO,  
                MODE_ID_MARCA,
                --MARC_DS_MARCA,
                MODE_ID_GRUPO_MAT_SERV
                --GRUP_DS_GRUPO_MAT_SERV
              FROM
                OCS_TB_MODE_MODELO
                INNER JOIN  OCS_TB_MARC_MARCA
                ON  MODE_ID_MARCA = MARC_ID_MARCA

                LEFT JOIN OCS_TB_GRUP_GRUPO_MAT_SERV
                ON MODE_ID_GRUPO_MAT_SERV = GRUP_ID_GRUPO_MAT_SERV
                
              WHERE 
                MODE_ID_MODELO = $id
            ");
        return $stmt->fetch();
    }

    public function getmodelosporMarca($id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MODE_ID_MODELO, MODE_DS_MODELO
                            FROM OCS_TB_MODE_MODELO
                            WHERE MODE_ID_MARCA = '$id'
                            ORDER BY 2");
        return $stmt->fetchAll();
    }
    
}