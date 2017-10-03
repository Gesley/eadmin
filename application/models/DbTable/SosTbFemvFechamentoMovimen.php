<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbFemvFechamentoMovimen extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_FEMV_FECHAMENTO_MOVIMEN';
    protected $_primary = array('FEMV_ID_DOCUMENTO','FEMV_ID_MOVIMENTACAO','FEMV_ID_INDICADOR');
    
}