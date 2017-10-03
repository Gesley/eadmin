<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Processo
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Sisad
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Trf1_Sisad_Negocio_Processo extends Trf1_Sisad_Negocio_Documento {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    public $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    /**
     * Regras de alteração de um processo
     *
     * @param	int $idDocumento	
     * @param	array $dadosAlteracao
     * @return	bool
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function alteraDadosProcesso($idProcesso, $dadosAlteracao) {
        $sadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
        return $sadTbPrdiProcessoDigital->find($idProcesso)
                        ->current()
                        ->setFromArray($dadosAlteracao)
                        ->save();
    }

    /**
     * Busca um processo através do id do documento
     *
     * @param	int	$idDocumento	
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getProcessoPorIdDocumento($idDocumento) {
        return $this->_db->fetchRow('
            SELECT  PRDI_ID_PROCESSO_DIGITAL,
                    PRDI_DS_TEXTO_AUTUACAO
            FROM SAD_TB_DOCM_DOCUMENTO
            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                ON DOCM_ID_DOCUMENTO        = DCPR_ID_DOCUMENTO
            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
            WHERE DOCM_ID_DOCUMENTO     = ? '
                        , array($idDocumento));
    }

    /**
     * Busca os processos ao qual o documento faz parte
     *
     * @param	int	$idDocumento	
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getProcessosPai($idDocumento) {
        $sql = '
SELECT  DTPD.DTPD_NO_TIPO,
        DOCM.DOCM_ID_DOCUMENTO,
        DOCM.DOCM_NR_DOCUMENTO,
        LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA,
        LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
        LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,
        RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
        TO_CHAR(DCPR.DCPR_DH_VINCULACAO_DOC,\'DD/MM/YYYY HH24:MI:SS\') DCPR_DH_VINCULACAO_DOC,
        DOCM.DOCM_NR_DOCUMENTO_RED,
        AQAT.AQAT_DS_ATIVIDADE,
        PRDI_ID_PROCESSO_DIGITAL ID_PROCESSO,
        CONF.CONF_ID_CONFIDENCIALIDADE
        
FROM    SAD_TB_DOCM_DOCUMENTO DOCM
        INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
            ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
        INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
            ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
        INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
            ON DOCM.DOCM_ID_TIPO_DOC = DTPD.DTPD_ID_TIPO_DOC
        INNER JOIN SAD_TB_TPSD_TIPO_SITUACAO_DOC TPSD
            ON DOCM.DOCM_ID_TIPO_SITUACAO_DOC = TPSD.TPSD_ID_TIPO_SITUACAO_DOC
        INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
            ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
        INNER JOIN RH_CENTRAL_LOTACAO LOTA
            ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
            AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
        INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
            ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
        INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
            ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
        
WHERE DOCM_ID_TIPO_DOC = ' . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . '
    AND DOCM_ID_DOCUMENTO <> ' . $idDocumento . '
    AND DCPR_ID_PROCESSO_DIGITAL IN (SELECT DCPR_ID_PROCESSO_DIGITAL
                                    FROM SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                    WHERE DCPR_ID_DOCUMENTO = ' . $idDocumento . ')';

        $stmt = $this->_db->query($sql);

        return $stmt->fetchAll();
}
    
    public function getDocumentoPorIdProcesso($processo){
        $sql = "
            SELECT
                DOCM.DOCM_ID_DOCUMENTO
                , DOCM.DOCM_ID_MOVIMENTACAO
                , DECODE(
                     LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                     14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                     sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                ) MASC_NR_DOCUMENTO
                , DOCM.DOCM_IC_PROCESSO_AUTUADO
                , DOCM.DOCM_NR_DOCUMENTO
                , DOCM.DOCM_NR_DCMTO_USUARIO
                , DOCM_CD_MATRICULA_CADASTRO
                , DOCM.DOCM_DS_ASSUNTO_DOC
                , DOCM.DOCM_NR_DOCUMENTO_RED
                , DOCM.DOCM_IC_PROCESSO_AUTUADO
                , DOCM_IC_MOVI_INDIVIDUAL
                --MOVIMENTACAO
                , TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO
                , TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR
                , SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR
                --FASE
                , MOFA.MOFA_ID_MOVIMENTACAO
                , DOCM.DOCM_ID_TP_EXTENSAO
                ,DOCM.DOCM_ID_CONFIDENCIALIDADE
                ,DOCM_ID_TIPO_DOC
                ,DOCM_ID_TIPO_DOC DTPD_ID_TIPO_DOC
            FROM
                SAD_TB_PRDI_PROCESSO_DIGITAL
                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                    ON PRDI_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                    AND PRDI_ID_PROCESSO_DIGITAL = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                    ON DCPR_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                    AND DOCM_IC_PROCESSO_AUTUADO = 'N'
                    AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                    ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                    ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO
                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                    ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                    ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
            WHERE
                MOFA.MOFA_DH_FASE = (   SELECT MAX(MOFA_1.MOFA_DH_FASE)
                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                                                ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                                            WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)";
        return $this->_db->fetchRow($sql);
    }

}