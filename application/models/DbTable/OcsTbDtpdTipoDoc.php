<?php
class Application_Model_DbTable_OcsTbDtpdTipoDoc extends Zend_Db_Table_Abstract
{
    protected $_adapter = 'db_sisad';
    protected $_name = 'OCS_TB_DTPD_TIPO_DOC';
    protected $_primary = 'DTPD_ID_TIPO_DOC';

    public function getTipoDocumento() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD_NO_TIPO, DTPD_ID_TIPO_DOC
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_IC_ATIVO = 'S'
                            -- AND DTPD_CD_TP_DOC_NIVEL_3 = 0
                            -- AND DTPD_CD_TP_DOC_NIVEL_2 = 0
                            -- AND DTPD_IC_ADM_JUD <> 'JU'
                               AND DTPD_ID_TIPO_DOC NOT IN (160,230,152) /*Solicitação de TI e Minuta*/
                          ORDER BY DTPD_NO_TIPO ");
        return $stmt->fetchAll();
    }
    
    public function getTipoDocumentoPesq() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD_NO_TIPO, DTPD_ID_TIPO_DOC
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_IC_ATIVO = 'S'
                            -- AND DTPD_CD_TP_DOC_NIVEL_2 = 0
                            -- AND DTPD_CD_TP_DOC_NIVEL_3 = 0
                            -- AND DTPD_IC_ADM_JUD <> 'JU'
                               AND DTPD_ID_TIPO_DOC NOT IN (160,230) /*Solicitação de TI, MINUTA*/
                          ORDER BY DTPD_NO_TIPO ");
        return $stmt->fetchAll();
    }
    
    public function getAjaxTipoDocumentoPesq($idDocumento) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT UPPER(DTPD_ID_TIPO_DOC||' - '||DTPD_NO_TIPO) AS LABEL , 
                                   UPPER(DTPD_ID_TIPO_DOC||' - '||DTPD_NO_TIPO) AS VALUE
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_IC_ATIVO = 'S'
                            -- AND DTPD_CD_TP_DOC_NIVEL_2 = 0
                            -- AND DTPD_CD_TP_DOC_NIVEL_3 = 0
                            -- AND DTPD_IC_ADM_JUD <> 'JU'
                               AND DTPD_ID_TIPO_DOC <> 160 /*Solicitação de TI*/
                               AND UPPER(DTPD_ID_TIPO_DOC||' - '||DTPD_NO_TIPO) LIKE UPPER('%$idDocumento%')
                          ORDER BY DTPD_ID_TIPO_DOC");
        return $stmt->fetchAll();
    }
    
        public function getTipoDocumentoAviso() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DTPD_NO_TIPO, DTPD_ID_TIPO_DOC
          FROM  OCS_TB_DTPD_TIPO_DOC
         WHERE  DTPD_ID_TIPO_DOC = 156
           AND  DTPD_IC_ATIVO = 'S'");
        return $stmt->fetchAll();
    }
    
        public function getTipoDocumentoMemorando() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DTPD_NO_TIPO, DTPD_ID_TIPO_DOC
          FROM  OCS_TB_DTPD_TIPO_DOC
         WHERE  DTPD_ID_TIPO_DOC = 85
           AND  DTPD_IC_ATIVO = 'S'");
        return $stmt->fetchAll();
    }

        public function getTipoDocumentoCircular() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DTPD_NO_TIPO, DTPD_ID_TIPO_DOC
          FROM  OCS_TB_DTPD_TIPO_DOC
         WHERE  DTPD_ID_TIPO_DOC = 26
           AND  DTPD_IC_ATIVO = 'S'");
        return $stmt->fetchAll();
    }

        public function getTipoDocumentoInformacao() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DTPD_NO_TIPO, DTPD_ID_TIPO_DOC
          FROM  OCS_TB_DTPD_TIPO_DOC
         WHERE  DTPD_ID_TIPO_DOC = 157
           AND  DTPD_IC_ATIVO = 'S'");
        return $stmt->fetchAll();
    }

        public function getTipoDocumentoOficio() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DTPD_NO_TIPO, DTPD_ID_TIPO_DOC
          FROM  OCS_TB_DTPD_TIPO_DOC
         WHERE  DTPD_ID_TIPO_DOC = 88
           AND  DTPD_IC_ATIVO = 'S'");
        return $stmt->fetchAll();
    }
      
    public function getTipoDocumentosTodosNivel1(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD_ID_TIPO_DOC,
                                   DTPD_CD_TP_DOC_NIVEL_1,
                                   DTPD_NO_TIPO,
                                   DTPD_IC_ADM_JUD 
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_IC_ATIVO = 'S'
                               AND DTPD_CD_TP_DOC_NIVEL_2 = 0
                               AND DTPD_CD_TP_DOC_NIVEL_3 = 0
                               AND DTPD_IC_ATIVO = 'S'
                               AND DTPD_ID_TIPO_DOC <> 160
                              ORDER BY DTPD_NO_TIPO");
        return $stmt->fetchAll();
    }
    
    public function getTipoDocumentosTodos(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD_ID_TIPO_DOC,
                                   DTPD_CD_TP_DOC_NIVEL_1||'-'||DTPD_CD_TP_DOC_NIVEL_2||'-'||DTPD_CD_TP_DOC_NIVEL_3 ID,
                                   UPPER(DTPD_NO_TIPO) DTPD_NO_TIPO,
                                   DTPD_IC_ADM_JUD
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_IC_ATIVO = 'S'
                               AND DTPD_IC_ATIVO = 'S'
                               AND DTPD_ID_TIPO_DOC <> 160
                              ORDER BY DTPD_NO_TIPO");
        return $stmt->fetchAll();
    }
    
    public function getTipoDocumentosNivel2($nivel){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD_CD_TP_DOC_NIVEL_1||'-'||DTPD_CD_TP_DOC_NIVEL_2 ID,
                                   DTPD_NO_TIPO
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_IC_ATIVO = 'S'
                               AND DTPD_CD_TP_DOC_NIVEL_1 = $nivel
                               AND DTPD_CD_TP_DOC_NIVEL_3 = 0
                               AND DTPD_IC_ATIVO = 'S'
                               AND DTPD_ID_TIPO_DOC <> 160
                              ORDER BY DTPD_NO_TIPO");
        return $stmt->fetchAll();
    }
    
    public function getDocsTipoEspecifico($codlotacao,$siglasecao,$nivel){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       DOCM_DH_CADASTRO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       MODE_DH_RECEBIMENTO,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                                       LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                               WHERE MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = '$siglasecao'
                               AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = $codlotacao
                               AND DTPD.DTPD_NO_TIPO IN (SELECT DTPD_NO_TIPO
                                                          FROM OCS_TB_DTPD_TIPO_DOC
                                                         WHERE DTPD_IC_ATIVO = 'S'
                                                           AND DTPD_CD_TP_DOC_NIVEL_1 = $nivel
                                                           AND DTPD_CD_TP_DOC_NIVEL_3 = 0
                                                           AND DTPD_IC_ATIVO = 'S'
                                                           AND DTPD_ID_TIPO_DOC <> 160)
                               AND MODP.MODP_ID_MOVIMENTACAO IS NULL
                               AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL
                               AND MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               ORDER BY MOVI.MOVI_DH_ENCAMINHAMENTO");
        return $stmt->fetchAll();
    }
    
    public function getTipoDocumentosAjax($nomeTipoDocumento)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT UPPER(DTPD_ID_TIPO_DOC||' - '||DTPD_NO_TIPO) AS LABEL                                   
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE UPPER(DTPD_ID_TIPO_DOC||' - '||DTPD_NO_TIPO) LIKE UPPER('%$nomeTipoDocumento%')
                               AND DTPD_IC_ATIVO = 'S'
                               AND DTPD_IC_ATIVO = 'S'
                               AND DTPD_ID_TIPO_DOC <> 160
                              ORDER BY DTPD_NO_TIPO");
        return $stmt->fetchAll();
    }
    
    public function setTipoDoc(array $TIPODOC)
    {
        $DtpdTipoDoc["DTPD_ID_TIPO_DOC"]           = $TIPODOC["DTPD_ID_TIPO_DOC"]; 
        $DtpdTipoDoc["DTPD_CD_TP_DOC_NIVEL_1"]     = $TIPODOC["DTPD_CD_TP_DOC_NIVEL_1"];
        $DtpdTipoDoc["DTPD_CD_TP_DOC_NIVEL_2"]     = $TIPODOC["DTPD_CD_TP_DOC_NIVEL_2"];
        $DtpdTipoDoc["DTPD_CD_TP_DOC_NIVEL_3"]     = $TIPODOC["DTPD_CD_TP_DOC_NIVEL_3"];
        $DtpdTipoDoc["DTPD_NO_TIPO"]               = $TIPODOC["DTPD_NO_TIPO"];
        $DtpdTipoDoc["DTPD_SG_DOC"]                = new Zend_Db_Expr('NULL');
        $DtpdTipoDoc["DTPD_IC_INSTANCIA"]          = $TIPODOC["DTPD_IC_INSTANCIA"];
        $DtpdTipoDoc["DTPD_IC_ADM_JUD"]            = $TIPODOC["DTPD_IC_ADM_JUD"]; 
        $DtpdTipoDoc["DTPD_IC_ATIVO"]              = 'S';
        $DtpdTipoDoc["DTPD_IC_ASSINATURA_DIGITAL"] = 'N';
        $DtpdTipoDoc["DTPD_IC_PRODUCAO_SISTEMA"]   = 'S';
        $DtpdTipoDoc["DTPD_NR_TIPO_SIGILO"]        = new Zend_Db_Expr('NULL');
        $DtpdTipoDoc["DTPD_ID_PCTT"]               = $TIPODOC["DTPD_ID_PCTT"];
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        
        try {
            $ocsTbDtpdTipoDoc = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
            $criaTipoDoc = $ocsTbDtpdTipoDoc->createRow($DtpdTipoDoc);
            $idTipoDoc = $criaTipoDoc->save();
            $db->commit();
        } catch (Exception $exc) {
            $db->rollBack();
        }
    }
    
    public function getQTDTipoDoc()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MAX(DTPD_ID_TIPO_DOC)+1 ID FROM OCS_TB_DTPD_TIPO_DOC");
        return $stmt->fetchAll();
    }
    public function getQTDTipoDocNivel1()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MAX(DTPD_CD_TP_DOC_NIVEL_1)+1 NIVEL_1
                            FROM OCS_TB_DTPD_TIPO_DOC");
        return $stmt->fetchAll();
    }
    public function getQTDTipoDocNivel2($nivel_1)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(DTPD_CD_TP_DOC_NIVEL_2) NIVEL_2 
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_CD_TP_DOC_NIVEL_1 = $nivel_1");
        return $stmt->fetchAll();
    }
    
    public function getQTDTipoDocNivel3($nivel_1,$nivel_2)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COUNT(DTPD_CD_TP_DOC_NIVEL_3) NIVEL_3 
                              FROM OCS_TB_DTPD_TIPO_DOC
                             WHERE DTPD_CD_TP_DOC_NIVEL_1 = $nivel_1
                               AND DTPD_CD_TP_DOC_NIVEL_2 = $nivel_2");
        return $stmt->fetchAll();
    }
    
}