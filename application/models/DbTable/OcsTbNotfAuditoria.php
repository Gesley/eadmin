<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */

class Application_Model_DbTable_OcsTbNotfAuditoria extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_NOTF_AUDITORIA';
    protected $_primary = 'NOTF_TS_OPERACAO';
}