<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

class Application_Model_DbTable_SosTbAsscAssociacao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_ASSC_ASSOCIACAO';
    protected $_primary = 'ASSC_ID_ASSOCIACAO';
    protected $_sequence = 'SOS_SQ_ASSC';
    
    public function getValorSequenciaAssociacao()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT LAST_NUMBER FROM ALL_SEQUENCES 
              WHERE  SEQUENCE_NAME = 'SOS_SQ_ASSC'";
        return $db->fetchOne($q) + 1;
    }

    public function setAssociarSolicitacoes(
        $idDocmDocumento, 
        array $dataMofaMoviFase, array $dataSsesServicoSolic, array $dataSnasNivelAtendSolic, 
        $idAssociacao, $nrDocsRed = null, $acompanhar = null
    )

    {
        $solicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $datahora = $solicitacao->sysdate();
        /**
         * Realiza a vinculação entre as solicitações 
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SosTbAsscAssociacao();
        $SadTbVidcAuditoria = new Application_Model_DbTable_SosTbAsscAudit();
        $rowVinc = $SadTbVidcVinculacaoDoc->createRow(array(
            'ASSC_ID_ASSOCIACAO_SOSTI' => $idAssociacao,
            'ASSC_ID_SOSTI_ASSOCIADO'  => $idDocmDocumento,
            'ASSC_DH_ASSOCIACAO'       => $datahora,
            'ASSC_CD_MATR_ASSOCIACAO'  => $userNs->matricula
        ));
        $id_vinculacao = $rowVinc->save();
        $rowVidcAuditoria = $SadTbVidcAuditoria->createRow(
            array(
                'ASSC_TS_OPERACAO' => new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')"),
                'ASSC_IC_OPERACAO' => 'I',
                'ASSC_CD_MATRICULA_OPERACAO' => $userNs->matricula,
                'ASSC_CD_MAQUINA_OPERACAO' => substr($_SERVER['REMOTE_ADDR'], 0, 50),
                'ASSC_CD_USUARIO_SO' => substr($_SERVER['HTTP_USER_AGENT'], 0, 50),
                'OLD_ASSC_ID_ASSOCIACAO_SOSTI' => new Zend_Db_Expr("NULL"),
                'NEW_ASSC_ID_ASSOCIACAO_SOSTI' => $id_vinculacao,
                'OLD_ASSC_ID_SOSTI_ASSOCIADO' => new Zend_Db_Expr("NULL"),
                'NEW_ASSC_ID_SOSTI_ASSOCIADO' => new Zend_Db_Expr("NULL"),
                'OLD_ASSC_DH_ASSOCIACAO' => new Zend_Db_Expr("NULL"),
                'NEW_ASSC_DH_ASSOCIACAO' => $datahora,
                'OLD_ASSC_CD_MATR_ASSOCIACAO' => new Zend_Db_Expr("NULL"),
                'NEW_ASSC_CD_MATR_ASSOCIACAO' => $userNs->matricula
            )
        );
        $rowVidcAuditoria->save();
        
        /* ---------------------------------------------------------------------------------------- */
        // Insere o anexo
        /* ---------------------------------------------------------------------------------------- */
        $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
        $anexAnexo['ANEX_DH_FASE'] = $datahora;
        $anexAnexo['ANEX_ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        /**
         * Cadastra os documentos que ainda não existe no red.
         */
        if ($nrDocsRed['incluidos']) {
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            foreach ($nrDocsRed['incluidos'] as $anexosIncluir) {
                $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                $rowAnexAnexo->save();
            }
        }
        /**
         *  Verifica se o documento que já existe no red já pertence a esta solicitação
         * caso negativo, cadastra o nr do documento para a solicitação.
         */
        if ($nrDocsRed['existentes']) {
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            foreach ($nrDocsRed['existentes'] as $anexosIncluir) {
                $SadTbAnexAnexofetchRow = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO = $idDocmDocumento AND ANEX_NR_DOCUMENTO_INTERNO = " . $anexosIncluir["ID_DOCUMENTO"]);
                if (!$SadTbAnexAnexofetchRow) {
                    $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $anexosIncluir["ID_DOCUMENTO"];
                    $anexAnexo["ANEX_NM_ANEXO"] = $anexosIncluir["NOME"];
                    $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $anexosIncluir["ANEX_ID_TP_EXTENSAO"];
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
            }
        }
        /* ----------------------ACOMPANHAMENTO DE BAIXA DA SOLICITAÇÃO NO ENCAMINHAMENTO  --------- */
        if ($acompanhar == "S") {
            $tabelaPapd = new Application_Model_DbTable_SadTbPapdParteProcDoc();
            $tabelaPapd->addAcompanhanteSostiCaixaAtendimento($idDocmDocumento);
        }
        /* ---------------------------------------------------------------------------------------- */
        $retorno['ID_MOVIMENTACAO'] = $idMoviMovimentacao;
        $retorno['DATA_HORA'] = $datahora;
        
        return $retorno;
    }
    
    public function getAssociacaoSosti($idDocumento)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT DOCM_NR_DOCUMENTO, TO_CHAR(ASSC_DH_ASSOCIACAO, 'DD/MM/YYYY HH24:MI:SS') ASSC_DH_ASSOCIACAO, ASSC_CD_MATR_ASSOCIACAO,
                     PNAT_NO_PESSOA, DOCM_ID_DOCUMENTO, DOCM_ID_MOVIMENTACAO, ASSC_ID_ASSOCIACAO
            FROM SOS_TB_ASSC_ASSOCIACAO ASSC 
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                       ON DOCM.DOCM_ID_DOCUMENTO = ASSC.ASSC_ID_SOSTI_ASSOCIADO
                       INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                       ON ASSC.ASSC_CD_MATR_ASSOCIACAO = PMAT.PMAT_CD_MATRICULA
                       INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                       ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
              WHERE ASSC_ID_ASSOCIACAO_SOSTI IN (
                  SELECT ASSC_ID_ASSOCIACAO_SOSTI FROM SOS_TB_ASSC_ASSOCIACAO
                  WHERE ASSC_ID_SOSTI_ASSOCIADO = '".$idDocumento."') "
                . "AND ASSC_ID_SOSTI_ASSOCIADO <> '".$idDocumento."'";
        return $db->fetchAll($q);
    }
    
    public function getAssociacaoSostiId($idAssociacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT * FROM SOS_TB_ASSC_ASSOCIACAO ASSC 
                       INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                       ON DOCM.DOCM_ID_DOCUMENTO = ASSC.ASSC_ID_SOSTI_ASSOCIADO
                       INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                       ON ASSC.ASSC_CD_MATR_ASSOCIACAO = PMAT.PMAT_CD_MATRICULA
                       INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                       ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
              WHERE ASSC_ID_ASSOCIACAO_SOSTI = (
                select ASSC_ID_ASSOCIACAO_SOSTI from sos_tb_assc_associacao
                WHERE ASSC_ID_ASSOCIACAO = ".$idAssociacao.")";
        return $db->fetchAll($q);
    }
    
    public function getQtdeSostiAssociado($idSosti)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = ""
            . "SELECT COUNT(*) QTDE "
            . "FROM SOS_TB_ASSC_ASSOCIACAO ASSC "
            . "INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM "
            . "ON DOCM.DOCM_ID_DOCUMENTO = ASSC.ASSC_ID_SOSTI_ASSOCIADO "
            . "INNER JOIN OCS_TB_PMAT_MATRICULA PMAT "
            . "ON ASSC.ASSC_CD_MATR_ASSOCIACAO = PMAT.PMAT_CD_MATRICULA "
            . "INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT "
            . "ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA "
            . "WHERE ASSC_ID_ASSOCIACAO_SOSTI IN ( "
                . "SELECT ASSC_ID_ASSOCIACAO_SOSTI FROM SOS_TB_ASSC_ASSOCIACAO "
                . "WHERE ASSC_ID_SOSTI_ASSOCIADO = '".$idSosti. "'"
            .") "
            . "AND ASSC_ID_SOSTI_ASSOCIADO <> '".$idSosti."'";
        $result = $db->fetchAll($q);
        return $result[0]['QTDE'];
    }
    
    public function setExcluiAssociacao($idAssociacao)
    {
        /**
         * Pega a data e hora atual
         */
        $solicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $datahora = $solicitacao->sysdate();
        /**
         * Realiza a exclusão da associação entre as solicitações 
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SosTbAsscAssociacao();
        $SadTbVidcAuditoria = new Application_Model_DbTable_SosTbAsscAudit();
        /**
         * Exclui a associação entre os sostis
         */
        $rowAss = $SadTbVidcVinculacaoDoc->find($idAssociacao)->current();
        $result = $rowAss->delete();
        
        $rowVidcAuditoria = $SadTbVidcAuditoria->createRow(
            array(
                'ASSC_TS_OPERACAO' => new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')"),
                'ASSC_IC_OPERACAO' => 'E',
                'ASSC_CD_MATRICULA_OPERACAO' => $userNs->matricula,
                'ASSC_CD_MAQUINA_OPERACAO' => substr($_SERVER['REMOTE_ADDR'], 0, 50),
                'ASSC_CD_USUARIO_SO' => substr($_SERVER['HTTP_USER_AGENT'], 0, 50),
                'OLD_ASSC_ID_ASSOCIACAO_SOSTI' => new Zend_Db_Expr("NULL"),
                'NEW_ASSC_ID_ASSOCIACAO_SOSTI' => $id_vinculacao,
                'OLD_ASSC_ID_SOSTI_ASSOCIADO' => new Zend_Db_Expr("NULL"),
                'NEW_ASSC_ID_SOSTI_ASSOCIADO' => new Zend_Db_Expr("NULL"),
                'OLD_ASSC_DH_ASSOCIACAO' => new Zend_Db_Expr("NULL"),
                'NEW_ASSC_DH_ASSOCIACAO' => $datahora,
                'OLD_ASSC_CD_MATR_ASSOCIACAO' => new Zend_Db_Expr("NULL"),
                'NEW_ASSC_CD_MATR_ASSOCIACAO' => $userNs->matricula
            )
        );
        $rowVidcAuditoria->save();
        return $result;
    }
}