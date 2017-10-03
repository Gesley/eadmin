<?php
class Application_Model_DbTable_SadTbDcprDocumentoProcesso extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_DCPR_DOCUMENTO_PROCESSO';
    protected $_primary = array('DCPR_ID_PROCESSO_DIGITAL','DCPR_ID_DOCUMENTO');
    
    
    public function getQtdDocsPro($idDocumento){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(DCPR_ID_PROCESSO_DIGITAL) QTD 
            FROM SAD_TB_DCPR_DOCUMENTO_PROCESSO
            WHERE DCPR_ID_DOCUMENTO = $idDocumento");
        return $stmt->fetch();
    }
}
