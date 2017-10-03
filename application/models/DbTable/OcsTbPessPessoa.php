<?php
class Application_Model_DbTable_OcsTbPessPessoa extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_adapter = 'db_sisad';
    protected $_name = 'OCS_TB_PESS_PESSOA';
    protected $_primary = 'PESS_ID_PESSOA';
    protected $_sequence = 'OCS_SQ_PESS';

    public function getPessoaNatural($secsubsec){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PMAT_ID_PESSOA, B.PNAT_NO_PESSOA, A.PMAT_CD_MATRICULA
                                FROM OCS_TB_PMAT_MATRICULA A, OCS_TB_PNAT_PESSOA_NATURAL B
                                WHERE A.pmat_sg_secsubsec_lotacao =  '$secsubsec'
                                AND  A.PMAT_ID_PESSOA = B.PNAT_ID_PESSOA
                                ORDER BY B.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }
    
}