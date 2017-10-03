<?php

class Application_Model_DbTable_Sisad_SadTbMofaMoviFase extends Zend_Db_Table_Abstract {

    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_MOFA_MOVI_FASE';
    protected $_primary = array('MOFA_ID_MOVIMENTACAO', 'MOFA_DH_FASE');

}