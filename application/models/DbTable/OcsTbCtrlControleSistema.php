<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbCtrlControleSistema extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_CTRL_CONTROLE_SISTEMA';
    protected $_primary = 'CTRL_ID_CONTROLE_SISTEMA';
    protected $_sequence = 'OCS_SQ_CTRL_ID_CONTROLE_SIST';
    
 
    public function getModulos()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  MODL_ID_MODULO, MODL_NM_MODULO
               			FROM  OCS_TB_MODL_MODULO
                		");
        return $stmt->fetchAll();
    }
    
    public function getList()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MODL_NM_MODULO, MODL_NM_SISTEMA,CTRL_NM_CONTROLE_SISTEMA, CTRL_ID_CONTROLE_SISTEMA
                                FROM  OCS_TB_MODL_MODULO A, OCS_TB_CTRL_CONTROLE_SISTEMA B
                                WHERE B.CTRL_ID_MODULO = A.MODL_ID_MODULO
                                ORDER BY MODL_NM_SISTEMA, MODL_NM_MODULO,CTRL_NM_CONTROLE_SISTEMA");
        return $stmt->fetchAll();
    }
    
    public function getDeletar($papl_id_papel)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("DELETE FROM OCS_TB_PAPL_PAPEL
                                WHERE PAPL_ID_PAPEL = $papl_id_papel");
    }
    
    public function getUpdate($ctrl_id_modulo, $ctrl_nm_controle_sistema, $ctrl_id_controle_sistema)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("UPDATE OCS_TB_CTRL_CONTROLE_SISTEMA
                                SET CTRL_ID_MODULO = '$ctrl_id_modulo',
                                    CTRL_NM_CONTROLE_SISTEMA = '$ctrl_nm_controle_sistema'
                                    WHERE CTRL_ID_CONTROLE_SISTEMA = $ctrl_id_controle_sistema");
    }
    
    public function getVerificacao($ctrl_nm_controle_sistema,$ctrl_id_modulo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.CTRL_ID_CONTROLE_SISTEMA, A.CTRL_NM_CONTROLE_SISTEMA,
                                   A.CTRL_ID_MODULO
                              FROM OCS_TB_CTRL_CONTROLE_SISTEMA A
                              WHERE CTRL_NM_CONTROLE_SISTEMA = LOWER('$ctrl_nm_controle_sistema')
                              AND CTRL_ID_MODULO = $ctrl_id_modulo");
        return $stmt->fetch();
    }

}