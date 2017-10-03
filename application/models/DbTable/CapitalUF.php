<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_CapitalUF extends Zend_Db_Table_Abstract
{
    protected $_name = 'CAP_UF';
    
   public function getCapitalUF(){
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
       $stmt = $db->query("SELECT CAP_UF||' - '||CAP_NOME NOME, CAP_UF FROM CAPITAL_UF ORDER BY CAP_UF");
        return $stmt->fetchAll();
   }

}