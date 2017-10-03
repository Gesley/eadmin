<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */

class Application_Model_DbTable_OcsTbAcaoAcaoSistema extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'OCS_TB_ACAO_ACAO_SISTEMA';
    protected $_primary = 'ACAO_ID_ACAO_SISTEMA';
    protected $_sequence = 'OCS_SQ_ACAO_ID_ACAO_SISTEMA';
    protected $_schema = 'OCS';
 
    public function getAcao()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  ACAO_ID_ACAO_SISTEMA, ACAO_NM_ACAO_SISTEMA
               			FROM  OCS_TB_ACAO_ACAO_SISTEMA
                             ORDER BY ACAO_NM_ACAO_SISTEMA
                		");
        return $stmt->fetchAll();
    }
    
    public function getControle($id_modulo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  CTRL_ID_CONTROLE_SISTEMA, CTRL_NM_CONTROLE_SISTEMA
               			FROM  OCS_TB_CTRL_CONTROLE_SISTEMA
                                WHERE CTRL_ID_MODULO = $id_modulo
                                ORDER BY CTRL_NM_CONTROLE_SISTEMA
                		");
        return $stmt->fetchAll();
    }
    
    public function getTodosControle()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  CTRL_ID_CONTROLE_SISTEMA, CTRL_NM_CONTROLE_SISTEMA
               			FROM  OCS_TB_CTRL_CONTROLE_SISTEMA
                                ORDER BY CTRL_NM_CONTROLE_SISTEMA
                		");
        return $stmt->fetchAll();
    }
    
    public function getList()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MODL_NM_MODULO, MODL_NM_SISTEMA, CTRL_NM_CONTROLE_SISTEMA, ACAO_NM_ACAO_SISTEMA, ACAO_ID_ACAO_SISTEMA
                                FROM  OCS_TB_MODL_MODULO A, OCS_TB_CTRL_CONTROLE_SISTEMA B, OCS_TB_ACAO_ACAO_SISTEMA C
                                WHERE B.CTRL_ID_MODULO = A.MODL_ID_MODULO
                                 AND  C.ACAO_ID_CONTROLE_SISTEMA = B.CTRL_ID_CONTROLE_SISTEMA
                             ORDER BY MODL_NM_SISTEMA,MODL_NM_MODULO,CTRL_NM_CONTROLE_SISTEMA,ACAO_NM_ACAO_SISTEMA
                		");
        return $stmt->fetchAll();
    }
    
    public function getModulos()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  MODL_ID_MODULO, MODL_NM_MODULO
               			FROM  OCS_TB_MODL_MODULO
                             ORDER BY MODL_NM_MODULO
                		");
        return $stmt->fetchAll();
    }
    
    public function getSistemas()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NOME_SISTEMA, DS_NOME_SISTEMA
               			FROM  SISTEMAS_TRF
                		WHERE NOME_SISTEMA  = 'EADMIN' 
                             ORDER BY NOME_SISTEMA
                                ");
        return $stmt->fetchAll();
    }
    
    public function getAcaoById($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MODL_ID_MODULO,
                                   MODL_NM_MODULO, 
                                   CTRL_ID_CONTROLE_SISTEMA,
                                   CTRL_NM_CONTROLE_SISTEMA,
                                   CTRL_ID_MODULO,
                                   ACAO_ID_ACAO_SISTEMA,
                                   ACAO_NM_ACAO_SISTEMA,
                                   ACAO_ID_CONTROLE_SISTEMA
                                FROM  OCS_TB_MODL_MODULO A, OCS_TB_CTRL_CONTROLE_SISTEMA B, OCS_TB_ACAO_ACAO_SISTEMA C
                                WHERE B.CTRL_ID_MODULO = A.MODL_ID_MODULO
                                AND  C.ACAO_ID_CONTROLE_SISTEMA = B.CTRL_ID_CONTROLE_SISTEMA
                                AND ACAO_ID_ACAO_SISTEMA = $id
                		");
        return $stmt->fetch();
    }
    
    public function getVerifica($id_modulo,$acao_nm_acao_sistema,$acao_id_controle_sistema)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  COUNT (A.ACAO_NM_ACAO_SISTEMA) AS ACAO,
                                    COUNT (A.ACAO_ID_CONTROLE_SISTEMA) AS CONTROLE,
                                    COUNT (C.MODL_ID_MODULO) AS MODULO
                                FROM OCS_TB_ACAO_ACAO_SISTEMA A,
                                     OCS_TB_CTRL_CONTROLE_SISTEMA B,
                                     OCS_TB_MODL_MODULO C
                                WHERE A.ACAO_ID_CONTROLE_SISTEMA = B.CTRL_ID_CONTROLE_SISTEMA
                                AND B.CTRL_ID_MODULO = C.MODL_ID_MODULO
                                AND C.MODL_ID_MODULO = $id_modulo
                                AND A.ACAO_NM_ACAO_SISTEMA = LOWER('$acao_nm_acao_sistema')
                                AND A.ACAO_ID_CONTROLE_SISTEMA = $acao_id_controle_sistema
                		");
        return $stmt->fetch();
    }
    public function getAcoesSemPapel($id_modulo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.ACAO_ID_ACAO_SISTEMA, A.ACAO_NM_ACAO_SISTEMA 
                            FROM OCS_TB_ACAO_ACAO_SISTEMA A
                            WHERE A.ACAO_ID_ACAO_SISTEMA IN (
                                                            SELECT A.ACAO_ID_ACAO_SISTEMA 
                                                            FROM OCS_TB_ACAO_ACAO_SISTEMA A
                                                            WHERE ACAO_ID_CONTROLE_SISTEMA = $id_modulo
                                                            MINUS
                                                            (SELECT A.ACAO_ID_ACAO_SISTEMA  
                                                            FROM OCS_TB_ACAO_ACAO_SISTEMA A
                                                            WHERE ACAO_ID_CONTROLE_SISTEMA = $id_modulo
                                                            INTERSECT
                                                            SELECT A.PAPL_ID_ACAO_SISTEMA 
                                                              FROM OCS_TB_PAPL_PAPEL A)
                                                            )
                           ORDER BY A.ACAO_NM_ACAO_SISTEMA
                              ");
        return $stmt->fetchAll();
    }
    

}