<?php
class Application_Model_DbTable_SadTbMoviMovimentacao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_adapter = 'db_sisad';
    protected $_name = 'SAD_TB_MOVI_MOVIMENTACAO';
    protected $_primary = 'MOVI_ID_MOVIMENTACAO';
    protected $_sequence = 'SAD_SQ_MOVI';
    
    public function getCaixaUnidadeRascunhos($codlotacao,$siglasecao,$order) 
    {
        if ( !isset($order) ) {
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD.DTPD_ID_TIPO_DOC,
                                   DTPD.DTPD_NO_TIPO,
                                   DOCM.DOCM_ID_DOCUMENTO,
                                   DOCM.DOCM_NR_DOCUMENTO,
                                   DOCM.DOCM_NR_DCMTO_USUARIO,
                                   TO_CHAR(DOCM.DOCM_DH_CADASTRO,'dd/mm/yyyy HH24:MI:SS') DOCM_DH_CADASTRO_CHAR,
                                   TO_CHAR(DOCM.DOCM_DH_CADASTRO,'dd/mm/yyyy HH24:MI:SS') DOCM_DH_CADASTRO,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)ENCAMINHADOR,
                                   DOCM.DOCM_NR_DOCUMENTO_RED,
                                   DOCM.DOCM_SG_SECAO_REDATORA,
                                   DOCM.DOCM_CD_LOTACAO_REDATORA,
                                   RH_LOTA.LOTA_SIGLA_LOTACAO
                              FROM SAD_TB_DOCM_DOCUMENTO DOCM,
                                   OCS_TB_DTPD_TIPO_DOC DTPD,
                                   RH_CENTRAL_LOTACAO RH_LOTA
                             WHERE DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                               AND RH_LOTA.LOTA_SIGLA_SECAO = DOCM.DOCM_SG_SECAO_REDATORA
                               AND RH_LOTA.LOTA_COD_LOTACAO = DOCM.DOCM_CD_LOTACAO_REDATORA
                               AND DOCM.DOCM_CD_LOTACAO_REDATORA  = $codlotacao
                               AND DOCM.DOCM_SG_SECAO_REDATORA    = '$siglasecao'
                               AND DOCM.DOCM_ID_DOCUMENTO NOT IN (SELECT MODO.MODO_ID_DOCUMENTO
                                                                    FROM SAD_TB_MODO_MOVI_DOCUMENTO MODO
                                                                   WHERE MODO.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               ORDER BY $order");
        return $stmt->fetchAll();
    }
    
    public function getCaixaUnidadeRecebidos($codlotacao,$siglasecao,$order, $parametro = null) //Caixa de Entrada.
    {
        if ( !isset($order) ){
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
		 if ($parametro == null ){
            $parametro = 'AND DOCM.DOCM_ID_DOCUMENTO IS NOT NULL';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$stmt = $db->query("SELECT DISTINCT DTPD.DTPD_ID_TIPO_DOC,
                                                    DTPD.DTPD_NO_TIPO,
                                                    DOCM.DOCM_ID_DOCUMENTO,
                                                    DOCM.DOCM_NR_DOCUMENTO,
                                                    DOCM.DOCM_NR_DCMTO_USUARIO,
                                                    DOCM.DOCM_ID_TIPO_SITUACAO_DOC,
                                                    TO_CHAR(DOCM_DH_CADASTRO,'dd/mm/yyyy HH24:MI:SS') DOCM_DH_CADASTRO,
                                                    DOCM_ID_CONFIDENCIALIDADE,
                                                    TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                                    MOVI_DH_ENCAMINHAMENTO,
                                                    LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO,
                                                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                                    DOCM.DOCM_NR_DOCUMENTO_RED,
                                                    
                                                    --MOFA.MOFA_ID_MOVIMENTACAO,
                                                    -- Subquery evita duplicidade de documento
                                                    (
                                                        select max(MODO_ID_MOVIMENTACAO)
                                                        from SAD_TB_MODO_MOVI_DOCUMENTO M
                                                        where DOCM.DOCM_ID_DOCUMENTO = M.MODO_ID_DOCUMENTO
                                                    ) as MOFA_ID_MOVIMENTACAO,
                                                    
                                                    TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                                    MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                                    MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                                    (SELECT LOTA_SIGLA_LOTACAO
                                                    FROM RH_CENTRAL_LOTACAO
                                                    WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                                    AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                                    DECODE(
                                                        LENGTH( DOCM_NR_DOCUMENTO),
                                                        17, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                        sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                    ) MASC_NR_DOCUMENTO,
                                                    AQAT.AQAT_DS_ATIVIDADE,

                                                    PRDI_DS_TEXTO_AUTUACAO,
                                                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC
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
											INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
											ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
											INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
											ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
											LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
											ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
											AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
											LEFT JOIN SAD_TB_PAPD_PARTE_PROC_DOC PARTE
											ON  PARTE.PAPD_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
											LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
											ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                                                                                        LEFT JOIN (
                                                                                            select max(DCPR_ID_PROCESSO_DIGITAL) as DCPR_ID_PROCESSO_DIGITAL, DCPR_ID_DOCUMENTO
                                                                                            from SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                                                                            group by DCPR_ID_DOCUMENTO
                                                                                        ) SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                                                                        ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                                                                        
                                                                                        LEFT JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                                                                        ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL

									 WHERE MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = '$siglasecao'
									 AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = $codlotacao
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
									  AND DOCM_IC_MOVI_INDIVIDUAL = 'S' --NAO LISTA DOCS DIVULGADOS 
									  AND DTPD_ID_TIPO_DOC <> 230 --MINUTAS
					    			  $parametro
									  ORDER BY $order");
       
        return $stmt->fetchAll();

    }
    
    public function getCaixaUnidadeEncaminhados($codlotacao,$siglasecao,$order) 
    {
        if ( !isset($order) ){
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                        MODP.MODP_ID_MOVIMENTACAO,
                                       MODP.MODP_SG_SECAO_UNID_DESTINO,
                                       MODP.MODP_CD_SECAO_UNID_DESTINO,
                                       MODP.MODP_CD_MAT_PESSOA_DESTINO,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,

                                       PRDI_DS_TEXTO_AUTUACAO,
                                       DOCM_DS_ASSUNTO_DOC

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
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON MODP.MODP_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO

                                       LEFT JOIN (
                                           select max(DCPR_ID_PROCESSO_DIGITAL) as DCPR_ID_PROCESSO_DIGITAL, DCPR_ID_DOCUMENTO
                                           from SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                           group by DCPR_ID_DOCUMENTO
                                       ) SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                       ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO

                                       LEFT JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                       ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL

                               WHERE MOVI.MOVI_SG_SECAO_UNID_ORIGEM = '$siglasecao'
                               AND MOVI.MOVI_CD_SECAO_UNID_ORIGEM = $codlotacao
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
                               AND DTPD_ID_TIPO_DOC <> 230 --MINUTAS
                               ORDER BY $order");
        return $stmt->fetchAll();

    }
    
    public function getDocumentoCaixaUnidadeEncaminhados($documento,$codlotacao,$siglasecao) 
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT     DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                        MODP.MODP_ID_MOVIMENTACAO,
                                       MODP.MODP_SG_SECAO_UNID_DESTINO,
                                       MODP.MODP_CD_SECAO_UNID_DESTINO,
                                       MODP.MODP_CD_MAT_PESSOA_DESTINO,
                                    DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,
                                        AQAT.AQAT_DS_ATIVIDADE
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
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON MODP.MODP_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO
                               WHERE MOVI.MOVI_SG_SECAO_UNID_ORIGEM = '$siglasecao'
                               AND MOVI.MOVI_CD_SECAO_UNID_ORIGEM = $codlotacao
                               AND DOCM.DOCM_ID_DOCUMENTO = {$documento['DOCM_ID_DOCUMENTO']}
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
                               AND DTPD_ID_TIPO_DOC <> 230 --MINUTAS";
        return $db->fetchRow($sql);

    }
    
    public function getCaixaPessoalRascunhos($codlotacao,$siglasecao,$matricula,$order) 
    {
        if ( !isset($order) ) {
            $order = 'DOCM_DH_CADASTRO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD.DTPD_ID_TIPO_DOC,
                                   DTPD.DTPD_ID_TIPO_DOC,
                                   DTPD.DTPD_NO_TIPO,
                                   DOCM.DOCM_ID_DOCUMENTO,
                                   DOCM.DOCM_NR_DOCUMENTO,
                                   DOCM.DOCM_NR_DCMTO_USUARIO,
                                   TO_CHAR(DOCM.DOCM_DH_CADASTRO,'dd/mm/yyyy HH24:MI:SS') DOCM_DH_CADASTRO_CHAR,
                                   DOCM_DH_CADASTRO,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)ENCAMINHADOR,
                                   DOCM.DOCM_NR_DOCUMENTO_RED,
                                   DOCM.DOCM_SG_SECAO_REDATORA,
                                   DOCM.DOCM_CD_LOTACAO_REDATORA,
                                   RH_LOTA.LOTA_SIGLA_LOTACAO,
                                   AQAT.AQAT_DS_ATIVIDADE,
                                   DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,

                                   PRDI_DS_TEXTO_AUTUACAO,
                                   DOCM_DS_ASSUNTO_DOC

                              FROM SAD_TB_DOCM_DOCUMENTO DOCM
                                   INNER JOIN RH_CENTRAL_LOTACAO RH_LOTA
                                   ON RH_LOTA.LOTA_SIGLA_SECAO = DOCM.DOCM_SG_SECAO_REDATORA
                                   AND RH_LOTA.LOTA_COD_LOTACAO = DOCM.DOCM_CD_LOTACAO_REDATORA
                                   INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                   ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                   INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                   ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                   INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                   ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE

                                       LEFT JOIN (
                                           select max(DCPR_ID_PROCESSO_DIGITAL) as DCPR_ID_PROCESSO_DIGITAL, DCPR_ID_DOCUMENTO
                                           from SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                           group by DCPR_ID_DOCUMENTO
                                       ) SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                       ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO

                                       LEFT JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                       ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL

                             WHERE 
                               --AND DOCM.DOCM_CD_LOTACAO_REDATORA  = $codlotacao
                               --AND DOCM.DOCM_SG_SECAO_REDATORA    = '$siglasecao'
                               DOCM.docm_cd_matricula_cadastro= '$matricula'
                               AND DOCM.DOCM_ID_DOCUMENTO NOT IN (SELECT MODO.MODO_ID_DOCUMENTO
                                                                    FROM SAD_TB_MODO_MOVI_DOCUMENTO MODO
                                                                   WHERE MODO.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                               AND  DOCM_IC_ARQUIVAMENTO = 'N'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               AND DTPD_ID_TIPO_DOC <> 230 --MINUTAS
                               ORDER BY $order");
        return $stmt->fetchAll();

    }
 
      public function getCaixaPessoalRecebidos($matricula,$codlotacao,$siglasecao,$order, $parametro = null) //Caixa de Entrada.
    {
        if ( !isset($order) ){
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DISTINCT
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,
                                        AQAT.AQAT_DS_ATIVIDADE,
                                       TO_CHAR(DOCM.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                                       PRDI_DS_TEXTO_AUTUACAO,
                                       DOCM_DS_ASSUNTO_DOC
                                       
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
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
                                       INNER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                                       AND MODP.MODP_CD_SECAO_UNID_DESTINO = MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO
                                       LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                       ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                                       LEFT JOIN (
                                           select max(DCPR_ID_PROCESSO_DIGITAL) as DCPR_ID_PROCESSO_DIGITAL, DCPR_ID_DOCUMENTO
                                           from SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                           group by DCPR_ID_DOCUMENTO
                                       ) SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                       ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                       LEFT JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                       ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL

                               WHERE  MODP.MODP_CD_MAT_PESSOA_DESTINO = '$matricula'
                              -- AND    MODP.MODP_SG_SECAO_UNID_DESTINO = '$siglasecao'
                              -- AND    MODP.MODP_CD_SECAO_UNID_DESTINO = $codlotacao
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
                               AND  DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N'
                               AND DOCM_IC_MOVI_INDIVIDUAL = 'S' --NAO LISTA DOCS DIVULGADOS 
                               AND  DTPD.DTPD_ID_TIPO_DOC <> 230 /*Minuta*/
                                    $parametro
                               ORDER BY $order");
        return $stmt->fetchAll();

    }
    
    public function getArquivadosPessoal($matricula,$codlotacao,$siglasecao,$order, $parametro = null) //Caixa de Arquivados Pessoal.
    {
        if ( !isset($order) ){
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  DISTINCT
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,
                                       PRDI_DS_TEXTO_AUTUACAO,
                                       DOCM_DS_ASSUNTO_DOC

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
                                       INNER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                                       AND MODP.MODP_CD_SECAO_UNID_DESTINO = MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO
                                       LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                       ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                                       LEFT JOIN (
                                           select max(DCPR_ID_PROCESSO_DIGITAL) as DCPR_ID_PROCESSO_DIGITAL, DCPR_ID_DOCUMENTO
                                           from SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                           group by DCPR_ID_DOCUMENTO
                                       ) SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                       ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO

                                       LEFT JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                       ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL

                               WHERE  MODP.MODP_CD_MAT_PESSOA_DESTINO = '$matricula'
                               --AND    MODP.MODP_SG_SECAO_UNID_DESTINO = '$siglasecao'
                               --AND    MODP.MODP_CD_SECAO_UNID_DESTINO = $codlotacao
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
                               AND  DOCM_IC_ARQUIVAMENTO = 'S'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               AND  DTPD.DTPD_ID_TIPO_DOC <> 230 /*Minuta*/
                                   $parametro
                               ORDER BY $order");
        return $stmt->fetchAll();

    }
    
    public function getArquivadosUnidade($codlotacao,$siglasecao,$order, $parametro = null) //Caixa de Arquivados Pessoal.
    {
        if ( !isset($order) ){
            $order = 'MOVI_DH_ENCAMINHAMENTO DESC';
        }
		if ($parametro == null ){
            $parametro = 'AND DOCM.DOCM_ID_DOCUMENTO IS NOT NULL';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT 
                                       DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(DOCM.DOCM_DH_CADASTRO,'dd/mm/yyyy HH24:MI:SS') DOCM_DH_CADASTRO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI.MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       MOVI.MOVI_SG_SECAO_UNID_ORIGEM,
                                       MOVI.MOVI_CD_SECAO_UNID_ORIGEM,
                                       (SELECT LOTA_SIGLA_LOTACAO
                                        FROM RH_CENTRAL_LOTACAO
                                        WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
                                        AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
                                        DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,

                                       PRDI_DS_TEXTO_AUTUACAO,
                                       DOCM_DS_ASSUNTO_DOC

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
                                LEFT JOIN SAD_TB_PAPD_PARTE_PROC_DOC PARTE
                                ON  PARTE.PAPD_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                LEFT JOIN SAD_TB_CADO_CATEGORIA_DOC CADO
                                ON CADO.CADO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                                LEFT JOIN (
                                    select max(DCPR_ID_PROCESSO_DIGITAL) as DCPR_ID_PROCESSO_DIGITAL, DCPR_ID_DOCUMENTO
                                    from SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                    group by DCPR_ID_DOCUMENTO
                                ) SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO

                                LEFT JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL

		   WHERE MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = '$siglasecao'
                               AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = $codlotacao
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
                               AND  DOCM_IC_ARQUIVAMENTO = 'S'
                               AND  DOCM_IC_ATIVO = 'S'
                               AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
							   $parametro
                               ORDER BY $order");
        return $stmt->fetchAll();

    }
    
    public function getCaixaPessoalEncaminhados($siglasecao,$codlotacao,$matricula,$order) 
    {
        if ( !isset($order) ) {
            $order = 'MOVI.MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOVI.MOVI_SG_SECAO_UNID_ORIGEM,
                                       MOVI.MOVI_CD_SECAO_UNID_ORIGEM,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       MODP.MODP_ID_MOVIMENTACAO,
                                       MODP.MODP_SG_SECAO_UNID_DESTINO,
                                       MODP.MODP_CD_SECAO_UNID_DESTINO,
                                       MODP.MODP_CD_MAT_PESSOA_DESTINO,
                                       AQAT.AQAT_DS_ATIVIDADE,   
                                       DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,

                                       PRDI_DS_TEXTO_AUTUACAO,
                                       DOCM_DS_ASSUNTO_DOC

                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       LEFT JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON MODP.MODP_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO   
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO

                                       LEFT JOIN (
                                           select max(DCPR_ID_PROCESSO_DIGITAL) as DCPR_ID_PROCESSO_DIGITAL, DCPR_ID_DOCUMENTO
                                           from SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                           group by DCPR_ID_DOCUMENTO
                                       ) SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                       ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO

                                       LEFT JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                       ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL

                               WHERE MOVI.MOVI_CD_MATR_ENCAMINHADOR = '$matricula'
                               --AND MOVI.MOVI_SG_SECAO_UNID_ORIGEM = '$siglasecao' 
                               --AND MOVI.MOVI_CD_SECAO_UNID_ORIGEM = $codlotacao
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
                               AND DOCM_IC_MOVI_INDIVIDUAL = 'S' --NAO LISTA DOCS DIVULGADOS 
                               AND  DTPD.DTPD_ID_TIPO_DOC <> 230 /*Minuta*/
                               ORDER BY $order");
        return $stmt->fetchAll();

    }
    
    public function getDocumentoCaixaPessoalEncaminhados($documento,$siglasecao,$codlotacao) 
    {
        if ( !isset($order) ) {
            $order = 'MOVI.MOVI_DH_ENCAMINHAMENTO DESC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql ="SELECT     DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOVI.MOVI_SG_SECAO_UNID_ORIGEM,
                                       MOVI.MOVI_CD_SECAO_UNID_ORIGEM,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       MODP.MODP_ID_MOVIMENTACAO,
                                       MODP.MODP_SG_SECAO_UNID_DESTINO,
                                       MODP.MODP_CD_SECAO_UNID_DESTINO,
                                       MODP.MODP_CD_MAT_PESSOA_DESTINO,
                                       DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,
                                        AQAT.AQAT_DS_ATIVIDADE  
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                                       INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                       ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       LEFT JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON MODP.MODP_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO   
                                       INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                       ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                                       INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                       ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                       INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                       ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
                               WHERE DOCM.DOCM_ID_DOCUMENTO = '{$documento['DOCM_ID_DOCUMENTO']}'
                               --AND MOVI.MOVI_SG_SECAO_UNID_ORIGEM = '$siglasecao' 
                               --AND MOVI.MOVI_CD_SECAO_UNID_ORIGEM = $codlotacao
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
                               AND  DTPD.DTPD_ID_TIPO_DOC <> 230 /*Minuta*/";
        return $db->fetchRow($sql);

    }
    
    public function getDocumento($nrDocumento) 
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DTPD.DTPD_ID_TIPO_DOC,
                                   DTPD.DTPD_NO_TIPO,
                                   DOCM.DOCM_ID_DOCUMENTO,
                                   DOCM.DOCM_NR_DOCUMENTO,
                                   DOCM.DOCM_NR_DCMTO_USUARIO,
                                   TO_CHAR(DOCM.DOCM_DH_CADASTRO,'dd/mm/yyyy HH24:MI:SS') DOCM_DH_CADASTRO,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)ENCAMINHADOR,
                                   DOCM.DOCM_NR_DOCUMENTO_RED 
                                FROM SAD_TB_DOCM_DOCUMENTO DOCM,
                                   OCS_TB_DTPD_TIPO_DOC DTPD
                                WHERE DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
                                AND DOCM.docm_nr_documento = $nrDocumento
                                AND DOCM.DOCM_ID_DOCUMENTO NOT IN (SELECT MODO.MODO_ID_DOCUMENTO
                                                                    FROM SAD_TB_MODO_MOVI_DOCUMENTO MODO
                                                                   WHERE MODO.MODO_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)");

        return $stmt->fetchAll();
    }
    
    public function getDocumentoMovimentado($nrDocumento) 
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $stmt = $db->query("SELECT DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(DOCM.DOCM_DH_CADASTRO,'dd/mm/yyyy HH24:MI:SS') DOCM_DH_CADASTRO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED
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
                                WHERE  MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
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
                                AND      DOCM.DOCM_NR_DOCUMENTO = $nrDocumento
                                AND ROWNUM = 1");

        return $stmt->fetchAll();
    }
    
    public function getUltimaMovimentacaoDcmto($idDocumento){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DOCM.DOCM_ID_DOCUMENTO,
                                   DOCM.DOCM_NR_DOCUMENTO,
                                   DOCM.DOCM_NR_DCMTO_USUARIO,
                                   TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                   TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO,
                                   SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                   DOCM.DOCM_NR_DOCUMENTO_RED,
                                   MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                   MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                   MOFA.MOFA_ID_MOVIMENTACAO,
                                   TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                   DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                17, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO
                            FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                   INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                   ON  DOCM.DOCM_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO
                                   INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                                   ON  MODO_MOVI.MODO_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                   ON  MOVI.MOVI_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                   INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                                   ON  MOVI.MOVI_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                           WHERE  DOCM.DOCM_ID_DOCUMENTO = $idDocumento
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
                           AND DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' ");
         
         return $stmt->fetchAll();
    }
    
    public function getCaixaPessoalMinutas($matricula,$order, $finalizada = null) 
    {
        if ( !isset($order) ) {
            $order = 'MOVI.MOVI_DH_ENCAMINHAMENTO DESC';
        }
    
        if ($finalizada){
          $and = "AND MOFA.MOFA_ID_FASE = 1044
                  AND DOCM.DOCM_ID_DOCUMENTO IN (SELECT PAPD_ID_DOCUMENTO
                                                 FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                 WHERE PAPD_ID_TP_PARTE IN (1,3)--PARTE/VISTA
                                                 AND PAPD_CD_MATRICULA_INTERESSADO = '$matricula')";
}
        else{
          $and = " AND MOFA.MOFA_ID_FASE <> 1044".
                 " AND MODP.MODP_CD_MAT_PESSOA_DESTINO = '$matricula'";
        }
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "SELECT     DTPD.DTPD_ID_TIPO_DOC,
                                       DTPD.DTPD_NO_TIPO,
                                       DOCM.DOCM_ID_DOCUMENTO,
                                       DOCM.DOCM_NR_DOCUMENTO,
                                       DOCM.DOCM_NR_DCMTO_USUARIO,
                                       TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
                                       MOVI_DH_ENCAMINHAMENTO,
                                       LOTA.LOTA_SIGLA_LOTACAO,
                                       SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
                                       DOCM.DOCM_NR_DOCUMENTO_RED,
                                       MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
                                       MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
                                       MOFA.MOFA_ID_MOVIMENTACAO,
                                       TO_CHAR(MODE_DH_RECEBIMENTO,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
                                       DECODE(
                                                LENGTH( DOCM_NR_DOCUMENTO),
                                                14, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                                                sad_pkg_nr_documento.mascara_documento(DOCM_NR_DOCUMENTO)
                                                ) MASC_NR_DOCUMENTO,
                                       AQAT.AQAT_DS_ATIVIDADE,
                                       DOCM.DOCM_SG_SECAO_GERADORA, 
                                       DOCM.DOCM_CD_LOTACAO_GERADORA, 
                                       DOCM.DOCM_SG_SECAO_REDATORA, 
                                       DOCM.DOCM_CD_LOTACAO_REDATORA,
                                       AQVP.AQVP_ID_PCTT,
                                       DOCM.DOCM_ID_TIPO_SITUACAO_DOC,
                                       DOCM.DOCM_ID_CONFIDENCIALIDADE,
                                       DOCM.DOCM_DS_ASSUNTO_DOC,
                                       DOCM.DOCM_DS_PALAVRA_CHAVE
                                FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                       INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                       ON  DOCM.DOCM_ID_DOCUMENTO   = MODO_MOVI.MODO_ID_DOCUMENTO
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
                                       INNER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
                                       ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                                       INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                       ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                                       INNER JOIN SAD_TB_AQAT_ATIVIDADE AQAT
                                       ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                                       AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
                                       AND MODP.MODP_CD_SECAO_UNID_DESTINO = MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO
                               WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA IS NULL
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
                               AND  DOCM.DOCM_IC_PROCESSO_AUTUADO = 'N' 
                               AND  DTPD.DTPD_ID_TIPO_DOC = 230 /*Minuta*/
                               $and
                               ORDER BY $order";
        $stmt = $db->query($query);
        return $stmt->fetchAll();
    
    }
}
