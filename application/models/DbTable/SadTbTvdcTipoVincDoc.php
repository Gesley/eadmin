<?php
class Application_Model_DbTable_SadTbTvdcTipoVincDoc extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_TVDC_TIPO_VINC_DOC';
    protected $_primary = array('TVDC_ID_TP_VINCULACAO');
    
    public function getTipoVincDoc(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TVDC_ID_TP_VINCULACAO, 
                                   TVDC_DS_TP_VINCULACAO
                              FROM SAD_TB_TVDC_TIPO_VINC_DOC
                          ORDER BY TVDC_DS_TP_VINCULACAO");
        return $stmt->fetchAll();
    }
    
}