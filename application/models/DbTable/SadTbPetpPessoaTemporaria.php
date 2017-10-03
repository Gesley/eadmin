<?php
class Application_Model_DbTable_SadTbPetpPessoaTemporaria extends Zend_Db_Table_Abstract
{
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_PETP_PESSOA_TEMPORARIA';
    protected $_primary = 'PETP_ID_PESSOA_TEMPORARIA';
    protected $_sequence = 'SAD_SQ_PETP';

    public function getPessoaTemporaria($nomeTemp){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PETP_ID_PESSOA_TEMPORARIA, 
                                   PETP_NM_NOME_TEMPORARIA 
                               FROM SAD_TB_PETP_PESSOA_TEMPORARIA 
                              WHERE PETP_NM_NOME_TEMPORARIA LIKE UPPER('$nomeTemp')");
        return $stmt->fetchAll();
    }
    
}