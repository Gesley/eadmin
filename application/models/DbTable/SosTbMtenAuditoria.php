<?php
class Application_Model_DbTable_SosTbMtenAuditoria extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_MTEN_AUDITORIA';
    protected $_primary = 'MTEN_TS_OPERACAO';
}