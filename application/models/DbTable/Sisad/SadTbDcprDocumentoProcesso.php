<?php

class Application_Model_DbTable_Sisad_SadTbDcprDocumentoProcesso extends Zend_Db_Table_Abstract {

    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_DCPR_DOCUMENTO_PROCESSO';
    protected $_primary = array('DCPR_ID_PROCESSO_DIGITAL', 'DCPR_ID_DOCUMENTO');

}
