<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class Application_Model_DbTable_lfsefichaServico extends Zend_Db_Table_Abstract {

    protected $_name = 'SOS_TB_LFSE_FICHA_SERVICO';
    protected $_primary = 'LFSE_ID_DOCUMENTO';

    public function getFichaServico($id) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT 
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_ID_DOCUMENTO, 
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_ID_TP_USUARIO,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_DS_SERVICO_EXECUTADO,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_DT_ENTRADA,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_DS_MOTIVO_MANUTENCAO,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_DT_SAIDA,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_NO_COMPUTADOR,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_GARANTIA,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_MANUTENCAO_EXTERNA,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_SCANDISK,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_DESFRAGMENTACAO,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_WINUPDATE,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_EXCLUSAO_PROFILE,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_EXCLUSAO_ARQTEMP,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_FORMATACAO,
                        SOS_TB_LFSE_FICHA_SERVICO.LFSE_IC_BACKUP 
			FROM SOS.SOS_TB_LFSE_FICHA_SERVICO
			WHERE LFSE_ID_DOCUMENTO = '$id'
			";

        return $solicitacao = $db->fetchRow($stmt);
    }
    
    public function getFichaServicoCompleta($id) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "
            SELECT *FROM 
                SOS_TB_LFSE_FICHA_SERVICO
                INNER JOIN SOS_TB_LTPU_TIPO_USUARIO
                ON LFSE_ID_TP_USUARIO = LTPU_ID_TP_USUARIO
                
                LEFT JOIN SOS_TB_LSBK_SERVICO_BACKUP 
                ON LSBK_ID_DOCUMENTO = $id
            WHERE 
                LFSE_ID_DOCUMENTO = $id
         ";

        return $solicitacao = $db->fetchRow($stmt);
    }
    
    public function verificaexitenciaFicha($idsolicitacao) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT COUNT(*) COUNT
                  FROM
                  SOS_TB_SSOL_SOLICITACAO  SSOL 
                  INNER JOIN  SOS_TB_LFSE_FICHA_SERVICO A
                  ON 
                  SSOL.SSOL_ID_DOCUMENTO = A.LFSE_ID_DOCUMENTO
                  WHERE ssol.SSOL_ID_DOCUMENTO = $idsolicitacao
			     ";
        $count = $db->fetchRow($stmt);
        if ($count['COUNT'] == 0) {
            return false;
        } else {
            return true;
        }
    }

}