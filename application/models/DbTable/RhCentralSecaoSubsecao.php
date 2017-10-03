<?php
class Application_Model_DbTable_RhCentralSecaoSubsecao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SARH';
    protected $_name = 'RH_CENTRAL_SECAO_SUBSECAO';
    protected $_primary = array('SESB_SIGLA_CENTRAL','SESB_SIGLA_SECAO_SUBSECAO') ;


    public function getSecao($siglaSecao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SESB_SESU_CD_SECSUBSEC, SESB_RAZAO_SOCIAL_SECAO_SUB
                              FROM RH_CENTRAL_SECAO_SUBSECAO
                             WHERE SESB_SIGLA_CENTRAL = 'TR'
                               AND SESB_SIGLA_SECAO_SUBSECAO = '$siglaSecao'");
        return $stmt->fetchAll();
    }
    
    public function getSecaoSubsecaoAjax($nomeSecao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SESB_SESU_CD_SECSUBSEC||' - '||SESB_RAZAO_SOCIAL_SECAO_SUB AS LABEL 
                                    FROM RH_CENTRAL_SECAO_SUBSECAO 
                                    WHERE SESB_SESU_CD_SECSUBSEC IS NOT NULL
                                    AND SESB_SIGLA_CENTRAL = SESB_UF
                                    AND SESB_SESU_CD_SECSUBSEC||SESB_RAZAO_SOCIAL_SECAO_SUB||SESB_NOME_BANCO LIKE UPPER('%$nomeSecao%')
                                    ORDER BY SESB_SESU_CD_SECSUBSEC");
        return $stmt->fetchAll();
    }
    
    public function getDadosSecSubsec($cdSecSubsec)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SESB_SIGLA_CENTRAL,SESB_SIGLA_SECAO_SUBSECAO 
                              FROM RH_CENTRAL_SECAO_SUBSECAO
                            WHERE  SESB_SESU_CD_SECSUBSEC IS NOT NULL
                               AND SESB_SIGLA_CENTRAL = SESB_UF
                               AND SESB_SESU_CD_SECSUBSEC = $cdSecSubsec");
        return $stmt->fetchAll();
    }
    
    public function getNomeSecSubsec($siglaCentral,$siglaSecSubsec)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SESB_RAZAO_SOCIAL_SECAO_SUB 
                              FROM RH_CENTRAL_SECAO_SUBSECAO 
                             WHERE SESB_SIGLA_CENTRAL = '$siglaCentral'
                               AND SESB_SIGLA_SECAO_SUBSECAO = '$siglaSecSubsec'");
        return $stmt->fetchAll();
    }
}
