<?php
class Application_Model_DbTable_SadTbMoprMoviProcesso extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_MOPR_MOVI_PROCESSO';
    protected $_primary = 'MOPR_ID_MOVIMENTACAO';
}