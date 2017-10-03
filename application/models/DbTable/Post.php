<?php
class Application_Model_DbTable_Post extends Zend_Db_Table_Abstract
{
    protected $_name = 'post';
    protected $_schema  = 'blog2';
    protected $_adapter = 'db_sessao';
    //protected $_primary = ''; # caso a primary key esteja diferente

    protected $_dependentTables = array('Application_Model_DbTable_Comment');

    protected $_referenceMap = array (
        array ('refTableClass'  => 'Application_Model_DbTable_User',
               'refColumns'     => 'id',
               'columns'        => 'user_id'));

}
?>
