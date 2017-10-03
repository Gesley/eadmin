<?php

class Application_Model_DbTable_SadTbPrvlProcAdmVol extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_PRVL_PROC_ADM_VOL';
    protected $_primary = array('PRVL_ID_PROC_FSPR','PRVL_NR_VOLUME');
}