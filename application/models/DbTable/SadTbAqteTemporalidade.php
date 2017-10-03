<?php
class Application_Model_DbTable_SadTbAqteTemporalidade extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_AQTE_TEMPORALIDADE';
    protected $_primary = 'AQTE_CD_TEMPORALIDADE';
}