<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class Application_Model_DbTable_SosTbLtpTipoUsuario extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LTPU_TIPO_USUARIO';
    protected $_primary = 'LPTUID_TP_USUARIO';

    public function gettipoUsuariosLaboratorio() {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = $db->query("
            SELECT 
                LTPU_ID_TP_USUARIO, 
                LTPU_DS_TP_USUARIO
            FROM
		SOS_TB_LTPU_TIPO_USUARIO
		ORDER BY LTPU_DS_TP_USUARIO ASC 
	");

        return $stmt->fetchAll();
    }

}