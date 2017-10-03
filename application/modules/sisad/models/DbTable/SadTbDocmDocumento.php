<?php
class Sisad_Model_DbTable_SadTbDocmDocumento extends Zend_Db_Table_Abstract
{
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_DOCM_DOCUMENTO';
    protected $_primary = array('DOCM_ID_DOCUMENTO');
    protected $_sequence = 'SAD_SQ_DOCM';
    protected $_squema = 'SAD';
}
