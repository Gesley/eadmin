<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_SosTbLfswFichaSoftware extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LFSW_FICHA_SOFTWARE';
    protected $_primary = 'LFSW_ID_DOCUMENTO';
    protected $_sequence = 'SOS_SQ_LFSW';

    /**
     * Retorna a lista de software associados ao documento na ficha de serviço.
     * 
     */
    public function getsoftwareFichaLista($idDocumento){
    	 $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	 $stmt ="SELECT A.LFSW_ID_DOCUMENTO, A.LFSW_ID_SOFTWARE, B.LSFW_DS_SOFTWARE, B.LSFW_IC_APROVACAO_INSTALACAO
            FROM SOS_TB_LFSW_FICHA_SOFTWARE A, SOS_TB_LSFW_SOFTWARE B
            WHERE  A.lfsw_id_documento = $idDocumento AND
           A.LFSW_ID_SOFTWARE = B.LSFW_ID_SOFTWARE
    	 " ;
    	 return $db->query($stmt)->fetchAll();

}
    
    public function checaLicenca($id){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT COUNT(*) AS LICENCAS_USADAS FROM SOS_TB_LFSW_FICHA_SOFTWARE F WHERE F.LFSW_ID_SOFTWARE=$id";
        
        return $db->query($stmt)->fetchAll();
    }
    
   /**
    * 
    * @param type $idDocumento 
    * @param type $software
    * Desvincula um equipamento a uma licença de software
    */
   public function devincularLicenca($idDocumento, $software){
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
       $stmt = $db->query("DELETE FROM SOS_TB_LFSW_FICHA_SOFTWARE WHERE LFSW_ID_DOCUMENTO = $idDocumento AND LFSW_ID_SOFTWARE = $software");
     }   
    
}