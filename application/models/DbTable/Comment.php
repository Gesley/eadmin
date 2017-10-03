<?php

class Application_Model_DbTable_Comment extends Zend_Db_Table_Abstract
{
    protected $_name = 'comment';
    protected $_schema  = 'blog2';
    protected $_user = 'root';
    protected $_adapter = 'db_sessao';

    protected $_referenceMap = array (
        array ('refTableClass'  => 'Application_Model_DbTable_Post',
               'refColumns'     => 'id',
               'columns'        => 'post_id'));
}
?>
