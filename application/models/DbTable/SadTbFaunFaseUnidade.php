<?php
class Application_Model_DbTable_SadTbFaunFaseUnidade extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_FAUN_FASE_UNIDADE';
    protected $_primary = 'FAUN_ID_FASE_UNID';
}