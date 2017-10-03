<?php
class Application_Model_DbTable_SadTbTpprTipoProcesso extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_TPPR_TIPO_PROCESSO';
    protected $_primary = 'TPPR_ID_TIPO_PROCESSO';
    
    
    public function getTipoProcessoPesq($order = null) {
        if ($order) {
          $ordem = $order;
        }else{
         $ordem = 'TPPR_ID_TIPO_PROCESSO';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT TPPR_ID_TIPO_PROCESSO, 
                                   TPPR_DS_DESCRICAO_PROCESSO,
                                   TPRR_IC_ATIVO,
                                   DECODE(TPRR_IC_ATIVO,
                                          'S','SIM',
                                          'N','NÃO ',TPRR_IC_ATIVO) IC_ATIVO
                           FROM SAD.SAD_TB_TPPR_TIPO_PROCESSO
                           ORDER BY ".$ordem);
        return $stmt->fetchAll();
    }
 }