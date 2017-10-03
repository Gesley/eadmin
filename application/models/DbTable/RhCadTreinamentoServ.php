<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_RhCadTreinamentoServ extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SARH';
    protected $_name = 'RH_CAD_TREINAMENTO_SERV';
    protected $_primary = array('CTRE_FUNC_SIGLA_SECAO','CTRE_FUNC_COD_FUNCIONARIO','CTRE_COD_ID');
    //protected $_sequence = 'SEQ_RH_CAD_TREINAMENTO'; 

    
    public function getSecao($siglaSecao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SESB_SESU_CD_SECSUBSEC, SESB_RAZAO_SOCIAL_SECAO_SUB, SESB_SIGLA_SECAO_SUBSECAO
                              FROM RH_CENTRAL_SECAO_SUBSECAO
                             WHERE SESB_SIGLA_CENTRAL = 'TR'
                             AND SESB_SIGLA_SECAO_SUBSECAO = '" . $siglaSecao . "'");
        return $stmt->fetchAll();
    }
    
    public function getAjaxDescCurso($desc)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        /*$stmt = $db->query("SELECT A.ATCR_DSC_CURSO||' - '||A.ATCR_COD_CURSO AS LABEL 
                            FROM RH_AT_CURSOS A
                            WHERE UPPER(A.ATCR_DSC_CURSO||' - '||A.ATCR_COD_CURSO) LIKE UPPER('%$desc%')");*/
        
        $stmt = $db->query("SELECT A.ATCR_DSC_CURSO AS LABEL, A.ATCR_COD_CURSO AS VALUE,
                                   A.ATCR_CARGA_HORARIA AS CARGA
                            FROM RH_AT_CURSOS A
                            WHERE A.ATCR_DSC_CURSO LIKE UPPER('%$desc%')");
        return $stmt->fetchAll();
    }
    
    public function getAjaxCargaHoraria($codigodotipo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.ATCR_CARGA_HORARIA 
                            FROM RH_AT_CURSOS A
                            WHERE A.ATCR_COD_CURSO = $codigodotipo");
        return $stmt->fetchAll();
    }
    
    public function getNextCod()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $stmt = $db->query("SELECT NVL(MAX(CTRE_COD_ID), 0) + 1 MAX_COD 
                            FROM RH_CAD_TREINAMENTO_SERV");
        return $stmt->fetchAll();
        
    }
}