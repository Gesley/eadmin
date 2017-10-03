<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbModlModulo extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_MODL_MODULO';
    protected $_primary = 'MODL_ID_MODULO';
    protected $_sequence = 'OCS_SQ_MODL_ID_MODULO';
    
 
    public function getSistemaEadmin()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NOME_SISTEMA, DS_NOME_SISTEMA
               			FROM  SISTEMAS_TRF
                		WHERE NOME_SISTEMA  = 'EADMIN' 
                                ");
        return $stmt->fetchAll();
    }
    
    public function getVerificacao($modl_nm_sistema,$modl_nm_modulo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.MODL_ID_MODULO, A.MODL_NM_MODULO, A.MODL_NM_SISTEMA
                              FROM OCS_TB_MODL_MODULO A
                              WHERE A.MODL_NM_SISTEMA = '$modl_nm_sistema'
                              AND  A.MODL_NM_MODULO = LOWER('$modl_nm_modulo')");
        return $stmt->fetch();
    }
    
    public function getVerifAlteracao($modl_nm_sistema,$modl_nm_modulo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT (a.modl_nm_modulo),
                                   COUNT (a.modl_nm_sistema)
                            FROM ocs_tb_modl_modulo a
                            WHERE  a.modl_nm_modulo = LOWER('$modl_nm_modulo')
                            AND a.modl_nm_sistema = '$modl_nm_sistema'
                            ");
        return $stmt->fetch();
    }
    
    public function getDeletar($papl_id_papel)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("DELETE FROM OCS_TB_PAPL_PAPEL
                                WHERE PAPL_ID_PAPEL = $papl_id_papel");
    }
    
}