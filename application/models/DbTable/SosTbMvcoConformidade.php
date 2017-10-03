<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbMvcoConformidade extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_MVCO_MOVIM_N_CONFORM';
    protected $_primary = array('MVCO_ID_NAO_CONFORMIDADE','MVCO_ID_MOVIMENTACAO');
    protected $_sequence = 'SOS_SQ_MVCO';
    
    
    
    public function getNaoConformidadesDescricao($idMovimentacao){
    	
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$stmt= "SELECT SOTC_DS_CONFORMIDADE FROM 
						   SOS_TB_SOTC_TP_N_CONFORMIDADE SOTC,
						   SOS_TB_MVCO_MOVIM_N_CONFORM MOVIM
                            WHERE MOVIM.MVCO_ID_NAO_CONFORMIDADE = SOTC.SOTC_ID_NAO_CONFORMIDADE
                            AND MOVIM.MVCO_ID_MOVIMENTACAO = $idMovimentacao
                            AND MOVIM.MVCO_IC_ATIVO_INATIVO = 'S'
							";
		
    	return $db->query($stmt)->fetchAll();
    }
	
	/**
	 * método para busacar as não conformidades da solicitações selecionadas
	 * 
	 * @param string $idMovimentacao separado por virgula
	 */
    public function getNaoConformidadesParaRemover($idsMovimentacao){
    	
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$stmt= "SELECT DISTINCT SOTC_DS_CONFORMIDADE,SOTC_ID_NAO_CONFORMIDADE FROM 
						   SOS_TB_SOTC_TP_N_CONFORMIDADE SOTC,
						   SOS_TB_MVCO_MOVIM_N_CONFORM MOVIM
                            WHERE MOVIM.MVCO_ID_NAO_CONFORMIDADE = SOTC.SOTC_ID_NAO_CONFORMIDADE
                            AND MOVIM.MVCO_ID_MOVIMENTACAO IN($idsMovimentacao)
                            AND MOVIM.MVCO_IC_ATIVO_INATIVO = 'S'
							";
		
    	return $db->query($stmt)->fetchAll();
    }
    
    public function getconformidadeDescricaopelaID($idConformidade){
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$stmt="SELECT SOTC_DS_CONFORMIDADE FROM 
						   SOS_TB_SOTC_TP_N_CONFORMIDADE SOTC
						  WHERE SOTC.SOTC_ID_NAO_CONFORMIDADE = $idConformidade 
						   ";
    	return $db->query($stmt)->fetch();
    } 
    
    
}