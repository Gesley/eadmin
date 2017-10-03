<?php
class Sisad_Model_DbTable_SadTbAsdcAssinaturaDoc extends Zend_Db_Table_Abstract
{
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_ASDC_ASSINATURA_DOC';
    protected $_primary = array('ASDC_ID_ASSINATURA_DOCUMENTO');
    protected $_sequence = 'SAD_SQ_ASDC';
    protected $_squema = 'SAD';
}
