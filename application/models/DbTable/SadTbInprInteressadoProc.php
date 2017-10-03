<?php
class Application_Model_DbTable_SadTbProcProcessoAdm extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_PROC_PROCESSO_ADM';
    protected $_primary = 'PROC_ID_PROC_FSPR';
}