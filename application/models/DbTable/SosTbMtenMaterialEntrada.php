<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_SosTbMtenMaterialEntrada extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_MTEN_MATERIAL_ENTRADA';
    protected $_primary = array('MTEN_ID_HARDWARE');
    protected $_sequence = 'SOS_SQ_LFHW';

    /**
     * Retorna Quantidade de equipamento disponível prara uso peoi ID do hardware
     * @param int $id
     */
    public function getQuantidadeHardwareDisponivel($id){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT MTEN_QT_ENTRADA_MATERIAL FROM SOS_TB_MTEN_MATERIAL_ENTRADA  WHERE MTEN_ID_HARDWARE= ".$id;
        return $db->query($stmt)->fetchAll();
    }
    /**
     * Retorna informação sobre entrada do hardware e saldo
     * @param unknown_type $HardwareID
     */
    public function getHardwareEntradaInfo($HardwareID){
    
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
       	$stmt = "SELECT A.MTEN_ID_HARDWARE, 
        A.MTEN_DT_ENTRADA_MATERIAL,
        A.MTEN_DS_OBSERVACAO,
        A.MTEN_NR_REQUISICAO_MATERIAL,
        TO_CHAR(A.MTEN_DT_ENTRADA_MATERIAL,'DD/MM/YYYY HH24:MI:SS') ENTRADA_MATERIAL,
        A.MTEN_CD_MATRICULA,
        A.MTEN_QT_ENTRADA_MATERIAL,
        A.MTEN_SG_SECAO,
        A.MTEN_CD_LOTACAO,
        B.LHDW_DS_OBSERVACAO
  		FROM SOS_TB_MTEN_MATERIAL_ENTRADA A,
        SOS_TB_LHDW_MATERIAL_ALMOX B
  		WHERE A.MTEN_ID_HARDWARE = B.LHDW_ID_HARDWARE
  		AND A.MTEN_ID_HARDWARE = $HardwareID";
       
       return $db->query($stmt)->fetchAll();
        
    } 
    public function getQuantidadeHardware($HardwareID){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt= "SELECT SUM(MTEN_QT_ENTRADA_MATERIAL) QUANTIDADE FROM SOS_TB_MTEN_MATERIAL_ENTRADA WHERE MTEN_ID_HARDWARE = $HardwareID";
        return $db->query($stmt)->fetchAll();
    }  
    
}