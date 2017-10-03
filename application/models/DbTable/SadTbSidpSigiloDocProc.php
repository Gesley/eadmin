<?php
class Application_Model_DbTable_SadTbSidpSigiloDocProc extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_SIDP_SIGILO_DOC_PROC';
    protected $_primary = array('SIDP_ID_DOCUMENTO', 'SIDP_CD_MATRICULA_VISUALIZACAO') ;
    protected $_sequence = 'SAD_SQ_SIDP';
     

}
