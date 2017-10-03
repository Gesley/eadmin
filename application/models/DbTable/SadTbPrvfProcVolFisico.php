<?php


class Application_Model_DbTable_SadTbPrvfProcVolFisico extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_PRVF_PROC_VOL_FISICO';
    protected $_primary = array('PRVF_ID_PROC_FSPR','PRVF_NR_VOLUME');
}