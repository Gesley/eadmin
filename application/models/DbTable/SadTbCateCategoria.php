<?php
class Application_Model_DbTable_SadTbCateCategoria extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_CATE_CATEGORIA';
    protected $_primary = array('CATE_ID_CATEGORIA'); 
    protected $_sequence = 'SAD_SQ_CATE';
}