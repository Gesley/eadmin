<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_PatTbTipoTombo extends Zend_Db_Table_Abstract
{
    protected $_name = 'PAT_TB_TPTB_TIPO_TOMBO';
    protected $_primary = array('TPTB_CD_TIPO_TOMBO');
    
    public function getTipoTombo(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT tptb_cd_tipo_tombo, tptb_ds_Tipo_tombo 
                              FROM pat_tb_tptb_tipo_tombo");
        return $stmt->fetchAll();
    }    
}