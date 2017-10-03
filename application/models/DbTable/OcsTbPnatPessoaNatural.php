<?php
class Application_Model_DbTable_OcsTbPnatPessoaNatural extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PNAT_PESSOA_NATURAL';
    protected $_primary = 'PNAT_ID_PESSOA';
    
    /*
     * Retorna matricula||nome da pessoa
     */
    public function getMatriculaNomeAjax($nomePessoa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PMAT_CD_MATRICULA||' - '||B.PNAT_NO_PESSOA AS LABEL
                            FROM   OCS_TB_PMAT_MATRICULA A, 
                                   OCS_TB_PNAT_PESSOA_NATURAL B
                            WHERE  PNAT_ID_PESSOA = PMAT_ID_PESSOA
                               AND B.PNAT_NO_PESSOA LIKE UPPER('%$nomePessoa%') ");
        return $stmt->fetchAll();
    }
    
    /*
     * Retorna nome e o id da pessoa
     */
    public function getPessoaAjax($nomePessoa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  PNAT_NO_PESSOA AS LABEL,
                                    PNAT_ID_PESSOA AS VALUE
                            FROM   OCS_TB_PNAT_PESSOA_NATURAL 
                            WHERE  PNAT_NO_PESSOA LIKE UPPER('%$nomePessoa%') ");
        return $stmt->fetchAll();
    }
    
    public function getDesembargadoresFederais(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  PMAT_CD_MATRICULA
                                    ,PNAT_NO_PESSOA
                                FROM OCS_TB_PMAT_MATRICULA PMAT
                                    ,OCS_TB_PNAT_PESSOA_NATURAL
                                WHERE  PMAT_ID_PESSOA = PNAT_ID_PESSOA 
                                    AND PMAT_DT_FIM IS NULL
                                    AND PMAT_CD_MATRICULA LIKE 'DS%'
                                ORDER BY PNAT_NO_PESSOA ASC
                            ");
        return $stmt->fetchAll();
    }

    /*
     * Retorna nome e o id da pessoa
     */
    public function getPessoaComIDAjax($nomePessoa)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  PNAT_ID_PESSOA||' - '||PNAT_NO_PESSOA AS LABEL,
                                    PNAT_ID_PESSOA||' - '||PNAT_NO_PESSOA AS VALUE
                            FROM   OCS_TB_PNAT_PESSOA_NATURAL 
                            WHERE  PNAT_NO_PESSOA LIKE UPPER('%$nomePessoa%') ");
        return $stmt->fetchAll();
    }
 }