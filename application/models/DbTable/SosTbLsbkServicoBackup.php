<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_SosTbLsbkServicoBackup extends Zend_Db_Table_Abstract
{

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LSBK_SERVICO_BACKUP';
    protected $_primary = array('LSBK_ID_DOCUMENTO', 'LSBK_NR_TOMBO', 'LSBK_TP_TOMBO');

    /**
     * 
     * Retorna a lista  de tombos que estao sendo usado ...
     * 
     */
    function getServicoBackupListDevolucao($order = null) {        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
                SELECT 
                    LSBK_ID_DOCUMENTO,
                    LSBK_NR_TOMBO,
                    LSBK_TP_TOMBO,
                    LSBK_DT_EMPRESTIMO,
                    LSBK_DT_RECEBIMENTO_USUARIO,
                    LSBK_DT_RECEBIMENTO_DEVOLUCAO,
                    LSBK_CD_MAT_EMPRESTIMO,
                    LSBK_CD_MAT_RECEB_USUARIO,
                    LSBK_CD_MAT_RECEB_DEVOLUCAO,
                    DOCM_NR_DOCUMENTO
                FROM 
                    SOS_TB_LSBK_SERVICO_BACKUP
                    INNER JOIN SAD_TB_DOCM_DOCUMENTO
                    ON LSBK_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                WHERE LSBK_DT_RECEBIMENTO_DEVOLUCAO IS NULL
		";
        if(!is_null($order)) {
            $stmt .=" ORDER BY $order";
        }

        $rows = $db->query($stmt)->fetchAll();
        return $rows;
    }

    public function gettomboBackupPeloIDDocumento($Id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "SELECT *
                FROM SOS_TB_LSBK_SERVICO_BACKUP S
                WHERE S.LSBK_ID_DOCUMENTO = $Id
                 AND S.LSBK_TP_TOMBO = 'T'";
        $row = $db->query($stmt)->fetch();
        if ($row) {
            return $row;
        }
    }

    function getServicoBackupEditDevolucao($docID, $tomboNr) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT *
                FROM SOS_TB_LSBK_SERVICO_BACKUP S
                WHERE S.LSBK_ID_DOCUMENTO = $docID
                 AND S.LSBK_NR_TOMBO = $tomboNr 
                 AND S.LSBK_TP_TOMBO = 'T'
                ";

        $row = $db->query($stmt)->fetchAll();
        return $row;
    }

    /**
     * Retorn a histórico do tombo de backup em relação seus empréstimos
     * @param unknown_type $tombonackupNr
     */
    public function getHitoricoEmprestimoEquipamento($tombonackupNr, $order = null) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $tombonackupNr = str_replace("';", "", $tombonackupNr);

//        $stmt = "SELECT A.LSBK_ID_DOCUMENTO, A.LSBK_NR_TOMBO, A.LSBK_TP_TOMBO,
//       A.LSBK_DT_RECEBIMENTO_USUARIO,
//       A.LSBK_DT_RECEBIMENTO_DEVOLUCAO, TO_CHAR(A.LSBK_DT_EMPRESTIMO, 'DD/MM/YYYY HH24:MI:SS') LSBK_DT_EMPRESTIMO,
//       A.LSBK_CD_MAT_RECEB_USUARIO, A.LSBK_CD_MAT_RECEB_DEVOLUCAO,A.LSBK_CD_MAT_EMPRESTIMO,
//
//       B.LBKP_NR_TOMBO, B.LBKP_SG_TOMBO, B.LBKP_CD_MATRICULA_CAD,
//       B.LBKP_DH_CADASTRO, B.LBKP_IC_ATIVO, B.LBKP_CD_MATRICULA_EXC,
//       B.LBKP_SG_SECAO, B.LBKP_CD_LOTACAO, B.LBKP_DH_EXCLUSAO,
//
//       D.SSOL_ID_DOCUMENTO, D.SSOL_ID_TIPO_CAD, D.SSOL_ED_LOCALIZACAO,
//       D.SSOL_NR_TOMBO, D.SSOL_SG_TIPO_TOMBO, D.SSOL_DS_OBSERVACAO,
//       D.SSOL_HH_INICIO_ATEND, D.SSOL_HH_FINAL_ATEND,
//       D.SSOL_NM_USUARIO_EXTERNO, D.SSOL_NR_CPF_EXTERNO,
//       D.SSOL_DS_EMAIL_EXTERNO, D.SSOL_NR_TELEFONE_EXTERNO,
//       D.SSOL_CD_MATRICULA_ATENDENTE,
//
//       C.DOCM_ID_DOCUMENTO, C.DOCM_NR_DOCUMENTO,
//       C.DOCM_NR_SEQUENCIAL_DOC, C.DOCM_NR_DCMTO_USUARIO,
//       C.DOCM_DH_CADASTRO, C.DOCM_CD_MATRICULA_CADASTRO,
//       C.DOCM_ID_TIPO_DOC, C.DOCM_SG_SECAO_GERADORA,
//       C.DOCM_CD_LOTACAO_GERADORA, C.DOCM_SG_SECAO_REDATORA,
//       C.DOCM_CD_LOTACAO_REDATORA, C.DOCM_ID_PCTT,
//       C.DOCM_DS_ASSUNTO_DOC, C.DOCM_ID_TIPO_SITUACAO_DOC,
//       C.DOCM_ID_CONFIDENCIALIDADE, C.DOCM_NR_DOCUMENTO_RED,
//       C.DOCM_DH_EXPIRACAO_DOCUMENTO, C.DOCM_DS_PALAVRA_CHAVE,
//       C.DOCM_IC_ARQUIVAMENTO, C.DOCM_ID_PESSOA,
//       C.DOCM_IC_DOCUMENTO_EXTERNO, C.DOCM_IC_ATIVO,
//       C.DOCM_IC_PROCESSO_AUTUADO, C.DOCM_ID_MOVIMENTACAO,
//       C.DOCM_DH_FASE, C.DOCM_ID_DOCUMENTO_PAI,
//       C.DOCM_ID_PESSOA_TEMPORARIA, C.DOCM_ID_TP_EXTENSAO,
//	   RH_DESCRICAO_CENTRAL_LOTACAO(B.LBKP_SG_SECAO,B.LBKP_CD_LOTACAO) AS LOCALIZACAO,
//                            C.DOCM_CD_MATRICULA_CADASTRO||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(C.DOCM_CD_MATRICULA_CADASTRO) TOMBO_EMPRESTIMO
//                            FROM SOS_TB_LSBK_SERVICO_BACKUP A,
//                                 SOS_TB_LBKP_BACKUP B,
//                                 SAD_TB_DOCM_DOCUMENTO C,
//                                 sos_tb_ssol_solicitacao D
//                            WHERE B.LBKP_NR_TOMBO = A.LSBK_NR_TOMBO
//                            AND A.LSBK_ID_DOCUMENTO = C.DOCM_ID_DOCUMENTO
//                            AND A.LSBK_ID_DOCUMENTO = D.SSOL_ID_DOCUMENTO
//                             AND LSBK_NR_TOMBO = $tombonackupNr
//                           ";
        $stmt =    "SELECT
                      *
                    FROM
                      SICAM.TOMBO_TI_CENTRAL TTC
                      INNER JOIN SOS.SOS_TB_LBKP_BACKUP BKP
                        ON TTC.ID_TOMBO_TI_CENTRAL = BKP.LBKP_ID_TOMBO_TI_CENTRAL
                      INNER JOIN SOS.SOS_TB_LSBK_SERVICO_BACKUP SBKP
                        ON TTC.NU_TOMBO = SBKP.LSBK_NR_TOMBO
                      INNER JOIN SAD.SAD_TB_DOCM_DOCUMENTO DOC
                        ON SBKP.LSBK_ID_DOCUMENTO = DOC.DOCM_ID_DOCUMENTO
                      INNER JOIN SOS.SOS_TB_SSOL_SOLICITACAO SOL
                        ON DOC.DOCM_ID_DOCUMENTO = SOL.SSOL_ID_DOCUMENTO
                    WHERE
                      TTC.NU_TOMBO = $tombonackupNr";
        if (!is_null($order)) {
            $stmt .= "ORDER BY $order";
        }

        return $db->query($stmt)->fetchAll();
    }

    /**
     * Verifica a disponibilidade de um backup
     * @param type $tombo
     * @return array 
     */
    public function getVerificaDisponibilidadeBackup($tombo) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = "
            SELECT
                LSBK_NR_TOMBO,
                LSBK_DT_RECEBIMENTO_DEVOLUCAO,
                MAX(LSBK_DT_EMPRESTIMO) AS EMPRESTIMO
            FROM 
                SOS_TB_LSBK_SERVICO_BACKUP 
            WHERE
                LSBK_NR_TOMBO = $tombo AND
                LSBK_DT_RECEBIMENTO_DEVOLUCAO IS NULL
            GROUP BY 
                LSBK_NR_TOMBO,
                LSBK_DT_RECEBIMENTO_DEVOLUCAO            
        ";

        return $db->query($sql)->fetchAll();
    }

}