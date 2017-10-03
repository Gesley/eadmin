<?php
class Application_Model_DbTable_User extends Zend_Db_Table_Abstract
{
    protected $_name = 'user';
    protected $_schema  = 'blog';
    protected $_adapter = 'db_sessao';
    protected $_dependentTables = array('Application_Model_DbTable_Comment');

}
?>
