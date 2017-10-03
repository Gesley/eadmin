<?php
class Application_Model_DbTable_SadTbTvpdTipoVincProcesso extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_TVPD_TIPO_VINC_PROCESSO';
    protected $_primary = array('TVPD_ID_TP_VINCULACAO');
    
    public function getTipoVincProcesso(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TVPD_ID_TP_VINCULACAO, 
                                   TVPD_DS_TP_VINCULACAO 
                              FROM SAD_TB_TIPO_VINC_PROCESSO
                          ORDER BY TVPD_DS_TP_VINCULACAO");
        return $stmt->fetchAll();
    }
    
}