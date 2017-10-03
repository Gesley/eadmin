<?php
class Application_Model_DbTable_SadTbAqdeDestino extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_AQDE_DESTINO';
    protected $_primary = 'AQDE_CD_DESTINO';
}