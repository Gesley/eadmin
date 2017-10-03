<?php

class Application_Model_DbTable_SadTbPageGuiaEncto extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name    = 'SAD_TB_PAGE_GUIA_ENCTO';
    protected $_primary = 'PAGE_ID_MOVIMENTACAO';
}