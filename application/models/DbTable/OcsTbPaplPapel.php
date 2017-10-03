<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbPaplPapel extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PAPL_PAPEL';
    protected $_primary = 'PAPL_ID_PAPEL';
    protected $_sequence = 'OCS_SQ_PAPL_PAPEL';
 
    public function getAcao()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  ACAO_ID_ACAO_SISTEMA, ACAO_NM_ACAO_SISTEMA
               			FROM  OCS_TB_ACAO_ACAO_SISTEMA
                		");
        return $stmt->fetchAll();
    }
    
    public function getControle()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  CTRL_ID_CONTROLE_SISTEMA, CTRL_NM_CONTROLE_SISTEMA
               			FROM  OCS_TB_CTRL_CONTROLE_SISTEMA
                		");
        return $stmt->fetchAll();
    }
    
    
    public function getModulos()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  MODL_ID_MODULO, MODL_NM_MODULO
               			FROM  OCS_TB_MODL_MODULO
                		");
        return $stmt->fetchAll();
    }
    
    public function getPapeisCriados()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PAPL.PAPL_ID_PAPEL, 
                               PAPL.PAPL_CD_MATRICULA_EXCLUSAO,
                               PAPL.PAPL_DT_EXCLUSAO,
                               PAPL.PAPL_NM_PAPEL, 
                               PAPL.PAPL_DS_FINALIDADE,
                               PAPL.PAPL_SG_SISTEMA AS SISTEMA,
                               TEMP.MODULO,
                               TEMP.CONTROLE,
                               TEMP.ACAO
                         FROM  OCS_TB_PAPL_PAPEL PAPL,
                                     SISTEMAS_TRF SIS,
                                     (SELECT NULL AS MODULO, NULL AS CONTROLE, NULL AS ACAO
                         FROM DUAL) TEMP
                         WHERE PAPL.PAPL_SG_SISTEMA = SIS.NOME_SISTEMA
                         UNION
                         SELECT PAPL.PAPL_ID_PAPEL,
                                PAPL.PAPL_CD_MATRICULA_EXCLUSAO,
                                PAPL.PAPL_DT_EXCLUSAO, 
                                PAPL.PAPL_NM_PAPEL, 
                                PAPL.PAPL_DS_FINALIDADE,
                                MODL.MODL_NM_SISTEMA AS SISTEMA,
                                MODL.MODL_NM_MODULO AS MODULO,
                                CTRL.CTRL_NM_CONTROLE_SISTEMA AS CONTROLE,
                                ACAO.ACAO_NM_ACAO_SISTEMA AS ACAO

                          FROM  OCS_TB_PAPL_PAPEL PAPL,
                                SISTEMAS_TRF SIS,
                                OCS_TB_MODL_MODULO MODL,
                                OCS_TB_CTRL_CONTROLE_SISTEMA CTRL,
                                OCS_TB_ACAO_ACAO_SISTEMA ACAO
                          WHERE PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
                          AND   ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
                          AND   CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
                          AND   MODL.MODL_NM_SISTEMA = SIS.NOME_SISTEMA
                          ORDER BY MODULO, CONTROLE
                          ");
        return $stmt->fetchAll();
    }
    
    public function getPapelById($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT D.PAPL_ID_PAPEL,
                                   D.PAPL_NM_PAPEL,
                                   D.PAPL_DS_FINALIDADE,
                                   D.PAPL_SG_SISTEMA,
                                   D.PAPL_ID_ACAO_SISTEMA,
                                   MODL_ID_MODULO,
                                   MODL_NM_MODULO, 
                                   CTRL_ID_CONTROLE_SISTEMA,
                                   CTRL_NM_CONTROLE_SISTEMA,
                                   CTRL_ID_MODULO,
                                   ACAO_ID_ACAO_SISTEMA,
                                   ACAO_NM_ACAO_SISTEMA,
                                   ACAO_ID_CONTROLE_SISTEMA
                               FROM  OCS_TB_MODL_MODULO A, OCS_TB_CTRL_CONTROLE_SISTEMA B, OCS_TB_ACAO_ACAO_SISTEMA C, OCS_TB_PAPL_PAPEL D
                               WHERE B.CTRL_ID_MODULO = A.MODL_ID_MODULO
                               AND  C.ACAO_ID_CONTROLE_SISTEMA = B.CTRL_ID_CONTROLE_SISTEMA
                               AND D.PAPL_ID_ACAO_SISTEMA = C.ACAO_ID_ACAO_SISTEMA
                               AND PAPL_ID_PAPEL = $id
                		");
        return $stmt->fetch();
    }
    
    public function getVerificaId($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT (A.PAPL_ID_ACAO_SISTEMA) AS ACAO
                              FROM OCS_TB_PAPL_PAPEL A
                              WHERE A.PAPL_ID_ACAO_SISTEMA = $id
                		");
        return $stmt->fetch();
    }
    
    public function getSistemas()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NOME_SISTEMA, DS_NOME_SISTEMA
               			FROM  SISTEMAS_TRF
                		WHERE NOME_SISTEMA  = 'EADMIN' 
                                ");
        return $stmt->fetchAll();
    }
}