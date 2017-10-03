<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class Application_Model_DbTable_SadTbAnexAnexo extends Zend_Db_Table_Abstract {

    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_ANEX_ANEXO';
    protected $_primary = array('ANEX_ID_DOCUMENTO','ANEX_NR_DOCUMENTO_INTERNO');

    public function getDadosAnexo($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT ANEX_ID_DOCUMENTO,
                                   ANEX_NR_DOCUMENTO_INTERNO,
                                   ANEX_ID_MOVIMENTACAO,
                                   TO_CHAR(ANEX_DH_FASE, 'yyyy/mm/dd HH24:MI:SS') ANEX_DH_FASE,
                                   ANEX_ID_TP_EXTENSAO,
                                   ANEX_NM_ANEXO
                              FROM SAD_TB_ANEX_ANEXO 
                             WHERE ANEX_ID_DOCUMENTO = $idDocumento
                             ORDER BY ANEX_DH_FASE DESC");
        return $stmt->fetchAll();
    }

    public function getUltimoAnexo($idDocumento, $tipoDoc) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = ("SELECT ANEX_ID_DOCUMENTO,
                      ANEX_NR_DOCUMENTO_INTERNO,
                      ANEX_ID_MOVIMENTACAO,
                      TO_CHAR(ANEX_DH_FASE, 'yyyy/mm/dd HH24:MI:SS') ANEX_DH_FASE,
                      ANEX_ID_TP_EXTENSAO
                 FROM SAD_TB_ANEX_ANEXO 
                WHERE ANEX_ID_DOCUMENTO = $idDocumento
                AND   ANEX_DH_FASE = (SELECT MAX(ANEX_DH_FASE)
                                      FROM SAD_TB_ANEX_ANEXO 
                                      WHERE ANEX_ID_DOCUMENTO = $idDocumento
                                      AND ANEX_ID_TP_EXTENSAO = $tipoDoc)");
        $stmt = $db->query($q);
        return $stmt->fetchAll();
    }

    public function getAnexosFase($idDocumento, $dhFase, $idMovimentacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $q = "SELECT ANEX_ID_DOCUMENTO,
                      ANEX_NR_DOCUMENTO_INTERNO,
                      ANEX_NM_ANEXO,
                      ANEX_ID_MOVIMENTACAO,
                      TO_CHAR(ANEX_DH_FASE, 'yyyy/mm/dd HH24:MI:SS') ANEX_DH_FASE,
                      ANEX_ID_TP_EXTENSAO
                 FROM SAD_TB_ANEX_ANEXO 
                WHERE ANEX_ID_DOCUMENTO = $idDocumento
                  AND ANEX_DH_FASE = TO_DATE('$dhFase', 'dd/mm/yyyy HH24:MI:SS') ";
        $q .= ($idMovimentacao != "")?(" AND ANEX_ID_MOVIMENTACAO = $idMovimentacao "):("");
        $q .= " ORDER BY ANEX_DH_FASE ASC ";
        $stmt = $db->query($q);
        return $stmt->fetchAll();
    }

    public function setAnexoErro($anexo) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        $idDocumento = $anexo["ANEX_ID_DOCUMENTO"];
        $idMovimentacao = $anexo["ANEX_ID_MOVIMENTACAO"];
        $dhFase = $anexo["ANEX_DH_FASE"];
        
        $nrDocumento = $anexo["ANEX_NR_DOCUMENTO_INTERNO"];
        $tpExtensao = $anexo["ANEX_ID_TP_EXTENSAO"];
        $nmAnexo = $anexo["ANEX_NM_ANEXO"];
        try {
            $q = "UPDATE SAD_TB_ANEX_ANEXO 
                SET ANEX_NR_DOCUMENTO_INTERNO = $nrDocumento, 
                    ANEX_ID_TP_EXTENSAO = $tpExtensao, 
                    ANEX_NM_ANEXO = '$nmAnexo'
             WHERE ANEX_ID_DOCUMENTO = $idDocumento
               AND ANEX_ID_MOVIMENTACAO = $idMovimentacao
               AND ANEX_DH_FASE = TO_CHAR('$dhFase','DD/MM/YYYY HH24:MI:SS')"
            ;
            $db->query($q);
            $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
            throw $exc;
        }
    }

    public function getAxenosMovimentacao($dataMofaMoviFase){
      
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT   ANEX_ID_DOCUMENTO
                                     ,ANEX_NR_DOCUMENTO_INTERNO
                                     ,ANEX_ID_MOVIMENTACAO
                                     ,TO_CHAR(ANEX_DH_FASE,'YYYY/MM/DD HH24:MI:SS') AS ANEX_DH_FASE
                                     ,ANEX_ID_TP_EXTENSAO
               FROM SAD_TB_ANEX_ANEXO
               WHERE ANEX_ID_MOVIMENTACAO = $dataMofaMoviFase[MOFA_ID_MOVIMENTACAO] 
                     AND ANEX_DH_FASE = TO_DATE('$dataMofaMoviFase[MOFA_DH_FASE]','dd/mm/yyyy HH24:MI:SS')");
        return $stmt->fetchAll();
    }
    
    public function getAxenosMovimentacaoTemporario($dataMofaMoviFase){
      
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT   ANEX_ID_DOCUMENTO
                                     ,ANEX_NR_DOCUMENTO_INTERNO
                                     ,ANEX_ID_MOVIMENTACAO
                                     ,TO_CHAR(ANEX_DH_FASE,'DD/MM/YYYY HH24:MI:SS') AS ANEX_DH_FASE
                                     ,ANEX_ID_TP_EXTENSAO
               FROM SAD_TB_ANEX_ANEXO
               WHERE ANEX_ID_MOVIMENTACAO = $dataMofaMoviFase[MOFA_ID_MOVIMENTACAO] 
                     AND ANEX_DH_FASE = TO_DATE('$dataMofaMoviFase[MOFA_DH_FASE]','dd/mm/yyyy HH24:MI:SS')");
        return $stmt->fetchAll();
    }
}