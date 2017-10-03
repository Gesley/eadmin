<?php
class Application_Model_DbTable_SadTbVidcAuditoria extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_VIDC_AUDITORIA';
    protected $_primary = 'VIDC_TS_OPERACAO';
   
} 