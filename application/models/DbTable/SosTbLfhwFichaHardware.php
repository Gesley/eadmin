<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_SosTbLfhwFichaHardware extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LFHW_FICHA_HARDWARE';
    protected $_primary = 'LFHW_ID_DOCUMENTO';
    protected $_sequence = 'SOS_SQ_LFHW';

    /**
     * Retorna a aquantidade de saoftware que esta sendo usado no momento. 
     * @param int $IdHardware
     */
    public function getSaldoHardware($IdHardware){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT COUNT(*) AS SALDO FROM SOS_TB_LFHW_FICHA_HARDWARE WHERE LFHW_ID_HARDWARE =". $IdHardware;
        return  $db->query($stmt)->fetchAll(); 
}
    
    /**
     * Retorna os hardwares relacionados a ficha de serviço.
     * @param unknown_type $IdDocumento
     */
    public function getHardwaresfichaServico($IdDocumento){
    
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    
    $stmt = "SELECT B.LHDW_ID_HARDWARE,B.LHDW_DS_HARDWARE,A.LFHW_QT_MATERIAL_ALMOX  FROM SOS_TB_LFHW_FICHA_HARDWARE A, SOS_TB_LHDW_MATERIAL_ALMOX B
				WHERE
				 A.LFHW_ID_HARDWARE = B.LHDW_ID_HARDWARE AND 
				 A.LFHW_ID_DOCUMENTO =". $IdDocumento;
    			return $db->query($stmt)->fetchAll();
    }
    
    /**
     * Retorna a quantidade de Hardware sendo usado pelo sistema
     * @param int $IdHardware
     */
    public function getSomaHardwareSendoUsado($IdHardware){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT SUM(A.LFHW_QT_MATERIAL_ALMOX) AS HARDWARE_SENDO_USADO FROM SOS_TB_LFHW_FICHA_HARDWARE A WHERE A.LFHW_ID_HARDWARE = ".$IdHardware;
        
        return $db->query($stmt)->fetchAll();
    }
    
}