<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_SistemasTrf extends Zend_Db_Table_Abstract
{
    //protected $_schema = 'trf1dsv';
    protected $_name = 'SISTEMAS_TRF';
    protected $_primary = 'NOME_SISTEMA';
    
    public function getSistemasdoEadmin()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NOME_SISTEMA, DS_NOME_SISTEMA
               			FROM  SISTEMAS_TRF
                		WHERE NOME_SISTEMA  IN ('SISAD','SOSTI') 
                                ");
        return $stmt->fetchAll();
    }
    
}