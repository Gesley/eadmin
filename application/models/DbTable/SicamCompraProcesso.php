<?php

class Application_Model_DbTable_SicamCompraProcesso  extends Zend_Db_Table_Abstract {

	  protected $_name = 'PROCESSO_COMPRA';
    protected $_primary = 'PROCESSO_COMPRA_PK';
    

    
    public function getCompraNumeroProcessos($order){
    	
    	 if ( !isset($order) ) {
            $order = 'NU_PROCESSO';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT NU_PROCESSO
                              FROM SICAM.PROCESSO_COMPRA
                                ORDER BY $order");
        $numeroProcessos = $stmt->fetchAll();
        return $numeroProcessos;
    	
    }
	
}

?>