<?php
class Application_Model_DbTable_SadTbVipdVincProcDigital extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_VIPD_VINC_PROC_DIGITAL';
    protected $_primary = array('VIPD_ID_VINCULACAO_PROCESSO');
    protected $_sequence = 'SAD_SQ_VIPD';
    
    public function setVincProcProc(array $documentos, array $vipdMoviMovimentacao){
        $vipdVipdProcDigital = new Application_Model_DbTable_SadTbVipdVincProcDigital();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        
        $Dual = new Application_Model_DbTable_Dual();
        
        foreach ($documentos as $value) {
            /*----------------------------------------------------------------------------------------*/
            $datahora = $Dual->sysdate();
            /*Cria fase*/

            $vipdMoviMovimentacao["MOFA_ID_MOVIMENTACAO"] = $vipdMoviMovimentacao['MOFA_ID_MOVIMENTACAO'];
            $vipdMoviMovimentacao["MOFA_DH_FASE"] = $datahora;
            /**
             * 1030 ADIÇÃO DE DOCUMENTOS A Documento
             */
            $vipdMoviMovimentacao["MOFA_ID_FASE"] = 1030; 
            //$dataMofaMoviFase["MOFA_CD_MATRICULA"] = INFORMA;
            //$dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = INFORMA;

            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($vipdMoviMovimentacao);
            $rowMofaMoviFase->save();
            /*----------------------------------------------------------------------------------------*/
            
            /*
             * Vincula o processo a outro processo
             */
            
            $value["VIPD_DH_VINCULACAO"] = $datahora;
            
            $row = $vipdVipdProcDigital->createRow($value);
            $row->save();
        }
    }
    
    public function getProcessosVinculados($idProcessoPrincipal, $idProcessoVindo){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM.DOCM_NR_DOCUMENTO,
                                   VIPD.VIPD_DH_VINCULACAO,
                                   AQAT.AQAT_DS_ATIVIDADE,
                                   LOCA.LOTA_DSC_LOTACAO
                                       FROM SAD_TB_DOCM_DOCUMENTO
                                       INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                       ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                       INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                       ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                                       INNER JOIN PRDI_ID_PROCESSO_DIGITAL = VIPD_ID_PROCESSO_DIGITAL_PRINC
                                            WHERE VIPD_ID_PROCESSO_DIGITAL_PRINC = $idProcessoPrincipal
                                              AND VIPD_ID_PROCESSO_DIGITAL_VINDO = $idProcessoVindo");
        return $stmt->fetchAll();
    }
}