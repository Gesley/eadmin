<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbmsiMovimDefSistemas extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_MDSI_MOVIM_DEF_SISTEMA';
    protected $_primary = 'MDSI_ID_MOVIM_DEF_SISTEMA';
    protected $_sequence = 'SOS_SQ_MDSI';
    
    public function getDefeitosPorMovimentacao($movimentacaoID){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT A.TIDE_NM_DEFEITO , A.TIDE_ID_TIPO_DEFEITO_SISTEMA
				FROM SOS.SOS_TB_TIDE_TIPO_DEFEITO_SIST A, SOS.SOS_TB_MDSI_MOVIM_DEF_SISTEMA B 
				WHERE A.TIDE_ID_TIPO_DEFEITO_SISTEMA = B.MDSI_ID_TIPO_DEFEITO_SISTEMA
				AND B. MDSI_ID_MOVIMENTACAO IN ($movimentacaoID) AND MDSI_IC_CANCELAMENTO ='N'  ORDER BY TIDE_NM_DEFEITO ASC";
        
        return $db->query($stmt)->fetchAll();
    }
    
    
}