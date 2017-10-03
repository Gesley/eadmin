<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Documento
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
class Trf1_Sisad_Negocio_Documento {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Adapter_Abstract $_db
     */
    protected $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */

    /**
     * Armazena dados de sessao do usuário logado
     *
     * @var Zend_Session_Namespace $_userNs
     */
    private $_userNs;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    /**
     * Busca todos os documentos da caixa que não seja autuado, arquivado e seja ativo
     * 
     * @param	int	$id	
     * @param	array	$configuracao	Obrigatório [SG_SECAO,CD_SECAO,ORDER]
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public static function alterar($id, array $arrayAlteracao, $dadosAntigos = null) {
        if ($dadosAntigos != null) {
            $userNs = new Zend_Session_Namespace('userNs');
            $dual = new Application_Model_DbTable_Dual();
            $dataHora = $dual->sysdatehoraDb();
            $dataFase = array(
                'MOFA_ID_MOVIMENTACAO' => $dadosAntigos['MOFA_ID_MOVIMENTACAO']
                , 'MOFA_DH_FASE' => new Zend_Db_Expr("TO_DATE('" . $dataHora . "','DD/MM/YYYY HH24:MI:SS')")
                , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_ALTERACAO_DE_METADADOS
                , 'MOFA_CD_MATRICULA' => $userNs->matricula
                , 'MOFA_DS_COMPLEMENTO' => ''
            );

            Trf1_Sisad_Negocio_Fase::lancaFase($dataFase);
        }
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        return $tabelaSadTbDocmDocumento->find($id)
                        ->current()
                        ->setFromArray($arrayAlteracao)
                        ->save();
    }

    /**
     * @tutorial Busca um ou varios documento especifico onde $configuracao pode ter valor 'um', 'array' ou 'query'.
     * 
     * @param	int		$nrDocumento	
     * @param	string	$configuracao	
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getDocumentoPorNumero($nrDocumento, $configuracao = 'um') {
        if ($configuracao == 'um') {
            $parametroDocumento = ' = ' . $nrDocumento;
        } elseif ($configuracao == 'array') {
            /* RECEBE UM ARRAY DE ID DE DOCUMENTOS */
            $parametroDocumento = ' in (' . implode(',', $nrDocumento) . ')';
        } elseif ($configuracao == 'query') {
            /* RECEBE UMA QUERY QUE RETORNA OS IDS DOS DOCUMENTOS */
            $parametroDocumento = ' in (' . $nrDocumento . ')';
        } else {
            return null;
        }
        $stmt = $this->_db->query("
SELECT 
        --TIPO DOCUMENTO
        DTPD.DTPD_NO_TIPO,
        DTPD.DTPD_ID_TIPO_DOC,
        --DOCUMENTOS
        DOCM.DOCM_ID_DOCUMENTO,
        DOCM.DOCM_NR_DOCUMENTO,
        DOCM.DOCM_NR_DCMTO_USUARIO,
        DOCM_CD_MATRICULA_CADASTRO,
        DOCM.DOCM_DS_ASSUNTO_DOC,
        DOCM.DOCM_NR_DOCUMENTO_RED,
        TO_CHAR(DOCM.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
        DECODE(
                LENGTH( DOCM_NR_DOCUMENTO),
                17, sad_pkg_nr_documento.mascara_processo(DOCM_NR_DOCUMENTO),
                DOCM_NR_DOCUMENTO
        ) MASC_NR_DOCUMENTO,

        --UNIDADE EMISSORA
        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)NOME,
        RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
        LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
        LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,

        --UNIDADE REDATORA
        RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) LOTA_DSC_LOTACAO_REDATORA,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) FAMILIA_REDATORA,
        LOTA_SIGLA_LOTACAO_P LOTA_SIGLA_LOTACAO_REDATORA,
        LOTA_COD_LOTACAO_P LOTA_COD_LOTACAO_REDATORA,

        --SITUACAO DOCUMENTO
        TPSD.TPSD_DS_TIPO_SITUACAO_DOC,

        --CONFIDENCIALIDADE
        CONF.CONF_ID_CONFIDENCIALIDADE,
        CONF.CONF_DS_CONFIDENCIALIDADE,

        --ASSUNTO 
        AQVP.AQVP_ID_PCTT,
        AQVP.AQVP_CD_PCTT,  
        AQAT.AQAT_DS_ATIVIDADE,

        --MOVIMENTACAO
        TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO,
        TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,

        --FASE
        MOFA.MOFA_ID_MOVIMENTACAO,
        DOCM.DOCM_ID_TP_EXTENSAO,

        MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
        MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
        LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO            
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
        ,( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_P,
            LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_P, 
            LOTA_COD_LOTACAO LOTA_COD_LOTACAO_P,
            LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_P
            FROM RH_CENTRAL_LOTACAO
        ) LOTA_P
WHERE  DOCM.DOCM_NR_DOCUMENTO $parametroDocumento
AND    LOTA_P.LOTA_SIGLA_SECAO_P = DOCM.DOCM_SG_SECAO_REDATORA
AND    LOTA_P.LOTA_COD_LOTACAO_P = DOCM.DOCM_CD_LOTACAO_REDATORA
AND    MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                            FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                            ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                            ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                            ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                            ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                            WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)");
        if ($configuracao == 'um') {
            return $stmt->fetch();
        } else {
            return $stmt->fetchAll();
        }
    }

    /**
     * @tutorial Busca um ou varios documento especifico onde $configuracao pode ter valor 'um', 'array' ou 'query'.
     * 
     * @param	int		$idDocumento	
     * @param	string	$configuracao	
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getDocumento($idDocumento, $configuracao = 'um') {
        if ($configuracao == 'um') {
            $parametroDocumento = ' = ' . $idDocumento;
        } elseif ($configuracao == 'array') {
            /* RECEBE UM ARRAY DE ID DE DOCUMENTOS */
            $parametroDocumento = ' in (' . implode(',', $idDocumento) . ')';
        } elseif ($configuracao == 'query') {
            /* RECEBE UMA QUERY QUE RETORNA OS IDS DOS DOCUMENTOS */
            $parametroDocumento = ' in (' . $idDocumento . ')';
        } else {
            return null;
        }
        $stmt = $this->_db->query("
SELECT 
        --TIPO DOCUMENTO
        DTPD.DTPD_NO_TIPO,
        DTPD.DTPD_ID_TIPO_DOC,
        --DOCUMENTOS
        DOCM.DOCM_ID_DOCUMENTO,
        DOCM.DOCM_IC_PROCESSO_AUTUADO,
        DOCM.DOCM_NR_DOCUMENTO,
        DOCM.DOCM_NR_DCMTO_USUARIO,
        DOCM_CD_MATRICULA_CADASTRO,
        DOCM.DOCM_DS_ASSUNTO_DOC,
        DOCM.DOCM_NR_DOCUMENTO_RED,
        DOCM.DOCM_IC_PROCESSO_AUTUADO,
        DOCM.DOCM_IC_MOVI_INDIVIDUAL,
        DOCM.DOCM_ID_CONFIDENCIALIDADE,
        DOCM.DOCM_DS_PALAVRA_CHAVE,
        DOCM.DOCM_ID_TIPO_SITUACAO_DOC,
        DOCM.DOCM_ID_PCTT,
        TO_CHAR(DOCM.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
        DECODE(
            LENGTH( DOCM.DOCM_NR_DOCUMENTO),
            14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
            sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
       ) MASC_NR_DOCUMENTO,
        --UNIDADE ENCAMINHADORA
        RH_SIGLAS_FAMILIA_CENTR_LOTA( MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO) FAMILIA_DESTINO,

        --UNIDADE EMISSORA
        LOTA.LOTA_SIGLA_SECAO,
        LOTA.LOTA_COD_LOTACAO,
        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)NOME,
        RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
        LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
        LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,

        --UNIDADE REDATORA
        RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) LOTA_DSC_LOTACAO_REDATORA,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) FAMILIA_REDATORA,
        LOTA_SIGLA_LOTACAO_P LOTA_SIGLA_LOTACAO_REDATORA,
        LOTA_SIGLA_LOTACAO_P LOTA_SIGLA_LOTACAO_ORIGEM,
        LOTA_COD_LOTACAO_P LOTA_COD_LOTACAO_REDATORA,

        --SITUACAO DOCUMENTO
        TPSD.TPSD_DS_TIPO_SITUACAO_DOC,

        --CONFIDENCIALIDADE
        CONF.CONF_ID_CONFIDENCIALIDADE,
        CONF.CONF_DS_CONFIDENCIALIDADE,

        --ASSUNTO 
        AQVP.AQVP_ID_PCTT,
        AQVP.AQVP_CD_PCTT,  
        AQAT.AQAT_DS_ATIVIDADE,

        --MOVIMENTACAO
        TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO,
        TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO_CHAR,
        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,

        --FASE
        MOFA.MOFA_ID_MOVIMENTACAO,
        DOCM.DOCM_ID_TP_EXTENSAO,

        MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
        MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,
        LOTA.LOTA_SIGLA_LOTACAO AS LOTA_SIGLA_LOTACAO_DESTINO,
        
        -- AUTOR DO DOCUMENTO
        PMAT_CD_MATRICULA PMAT_CD_MATRICULA_AUTOR,
        PNAT_NO_PESSOA PNAT_NO_PESSOA_AUTOR
FROM   SAD_TB_DOCM_DOCUMENTO DOCM
        INNER JOIN OCS_TB_PMAT_MATRICULA
            ON DOCM_CD_MATRICULA_CADASTRO = PMAT_CD_MATRICULA
        INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
            ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
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
        ,( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_P,
            LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_P, 
            LOTA_COD_LOTACAO LOTA_COD_LOTACAO_P,
            LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_P
            FROM RH_CENTRAL_LOTACAO
        ) LOTA_P
WHERE  DOCM.DOCM_ID_DOCUMENTO $parametroDocumento
AND    LOTA_P.LOTA_SIGLA_SECAO_P = DOCM.DOCM_SG_SECAO_REDATORA
AND    LOTA_P.LOTA_COD_LOTACAO_P = DOCM.DOCM_CD_LOTACAO_REDATORA
AND    MOFA.MOFA_DH_FASE = (SELECT MAX(MOFA_1.MOFA_DH_FASE)
                            FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                            ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                            ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODO_MOVI_1
                            ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODO_MOVI_1.MODE_ID_MOVIMENTACAO
                            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1
                            ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO
                            WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)");
        if ($configuracao == 'um') {
            return $stmt->fetch();
        } else {
            return $stmt->fetchAll();
        }
    }

    /**
     * Busca um documento especifico da caixa de rascunho
     * 
     * @param	int	$idDocumento	
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getDocumentoRascunho($idDocumento) {
        $stmt = $this->_db->query("
SELECT 
    --TIPO DOCUMENTO
    DTPD.DTPD_NO_TIPO,

    --DOCUMENTOS
    DOCM.DOCM_ID_DOCUMENTO,
    DOCM.DOCM_NR_DOCUMENTO,
    DOCM.DOCM_NR_DCMTO_USUARIO,
    DOCM_CD_MATRICULA_CADASTRO,
    DOCM.DOCM_DS_ASSUNTO_DOC,
    DOCM.DOCM_NR_DOCUMENTO_RED,
    TO_CHAR(DOCM.DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,

    --UNIDADE EMISSORA
    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM.DOCM_CD_MATRICULA_CADASTRO)NOME,
    RH_DESCRICAO_CENTRAL_LOTACAO(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA,
    RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA,
    LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA,
    LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA,

    --UNIDADE REDATORA
    RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) LOTA_DSC_LOTACAO_REDATORA,
    RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO_P,LOTA_COD_LOTACAO_P) FAMILIA_REDATORA,
    LOTA_SIGLA_LOTACAO_P LOTA_SIGLA_LOTACAO_REDATORA,
    LOTA_COD_LOTACAO_P LOTA_COD_LOTACAO_REDATORA,

    --SITUACAO DOCUMENTO
    TPSD.TPSD_DS_TIPO_SITUACAO_DOC,

    --CONFIDENCIALIDADE
    CONF.CONF_ID_CONFIDENCIALIDADE,
    CONF.CONF_DS_CONFIDENCIALIDADE,

    --ASSUNTO 
    AQVP.AQVP_ID_PCTT,
    AQVP.AQVP_CD_PCTT,  
    AQAT.AQAT_DS_ATIVIDADE,
    DOCM.DOCM_ID_TP_EXTENSAO



 FROM   SAD_TB_DOCM_DOCUMENTO DOCM
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
        ,( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_P,
                LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_P, 
                LOTA_COD_LOTACAO LOTA_COD_LOTACAO_P,
                LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_P
                FROM RH_CENTRAL_LOTACAO
            ) LOTA_P
    WHERE  DOCM.DOCM_ID_DOCUMENTO = ?
    AND    LOTA_P.LOTA_SIGLA_SECAO_P = DOCM.DOCM_SG_SECAO_REDATORA
    AND    LOTA_P.LOTA_COD_LOTACAO_P = DOCM.DOCM_CD_LOTACAO_REDATORA", array($idDocumento));
        return $stmt->fetch();
    }

    /**
     * Busca todos os documentos da caixa que não seja autuado, arquivado e seja ativo
     * 
     * @param	int	$codlotacao	
     * @param	array	$configuracao	Obrigatório [SG_SECAO,CD_SECAO,ORDER]
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getDocumentoCaixa(array $configuracao) {
        /*
         * Para inserir uma nova condição basta alimentar a variável $completa
         * com a clausula where desejada desde que a coluna desejada esteja em
         * alguma das tabelas envolvidas
         */
        $completa = '';

        /*
         * Exclui documentos desejados
         * Necessário ter um array de documentos (pelo menos a coluna DOCM_ID_DOCUMENTO)
         * ex: $configuracao['excluidos'] = array( 
         *                                        array('DOCM_ID_DOCUMENTO' => $valor1)
         *                                       ,array('DOCM_ID_DOCUMENTO' => $valor2) );
         */
        if (isset($configuracao['excluidos'])) {
            $completa .= " AND DOCM.DOCM_ID_DOCUMENTO NOT IN ('0'";
            foreach ($configuracao['excluidos'] as $excluido) {
                $completa .= ",'{$excluido['DOCM_ID_DOCUMENTO']}'";
            }
            $completa .= ')';
        }

        /* Define o tipo de documento a ser mostrado junto com o operador de associação (=,<>,!=,>,< ... etc)
         * ex: $configuracao['DTPD_ID_TIPO_DOC'] = '='.$valor
         */
        if (isset($configuracao['DTPD_ID_TIPO_DOC'])) {
            $completa .= " AND DTPD.DTPD_ID_TIPO_DOC {$configuracao['DTPD_ID_TIPO_DOC']}";
        }

        $sql = "
SELECT  DTPD.DTPD_ID_TIPO_DOC,
        DTPD.DTPD_NO_TIPO,
        DOCM.DOCM_ID_DOCUMENTO,
        DOCM.DOCM_NR_DOCUMENTO,
        DECODE(
            LENGTH( DOCM.DOCM_NR_DOCUMENTO),
            14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
            sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
            ) MASC_NR_DOCUMENTO,
        DOCM.DOCM_NR_DCMTO_USUARIO,
        DOCM.DOCM_ID_TIPO_SITUACAO_DOC,
        TO_CHAR(DOCM_DH_CADASTRO,'dd/mm/yyyy HH24:MI:SS') DOCM_DH_CADASTRO,
        DOCM_ID_CONFIDENCIALIDADE,
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
        AQAT.AQAT_DS_ATIVIDADE
FROM    SAD_TB_DOCM_DOCUMENTO DOCM
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
WHERE 
    MODP.MODP_ID_MOVIMENTACAO IS NULL
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
    AND MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = ?
    AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = ?
    $completa
ORDER BY ?";
        //Mostra os dados da variavel $sql
        /* Zend_Debug::dump($sql);
          Zend_Debug::dump(array($configuracao['SG_SECAO']
          , $configuracao['CD_SECAO']
          , $configuracao['ORDER']));
          exit; */
        $stmt = $this->_db->query($sql, array($configuracao['CD_SECAO']
            , $configuracao['SG_SECAO']
            , $configuracao['ORDER']));

        return $stmt->fetchAll();
    }

    /**
     * Retorna um array de numero de documentos
     * 
     * @param	int	$idDocOrigem
     * @param	int	$idMovimentacao
     * @param	int	$dhFase
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getNumeroDocumento($idDocOrigem, $idMovimentacao, $dhFase) {

        $sql = '
SELECT DOCM_NR_DOCUMENTO
FROM SAD_TB_DOCM_DOCUMENTO
    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
        ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
    INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
        ON  MOFA.MOFA_DH_FASE = DCPR_DH_VINCULACAO_DOC
WHERE MOFA_ID_MOVIMENTACAO = ?
      AND DOCM_ID_TIPO_DOC <> ?
      AND DCPR_ID_PROCESSO_DIGITAL IN ( SELECT DCPR_ID_PROCESSO_DIGITAL
                                        FROM SAD_TB_DOCM_DOCUMENTO
                                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                                            ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                                            ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
                                        WHERE DOCM_ID_DOCUMENTO = ? )
      AND TO_DATE(TO_CHAR(MOFA.MOFA_DH_FASE,\'DD/MM/YYYY HH24:MI:SS\'),\'DD/MM/YYYY HH24:MI:SS\') = TO_DATE( ? ,\'DD/MM/YYYY HH24:MI:SS\')';

        $resultado = $this->_db->query($sql, array($idMovimentacao, Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO, $idDocOrigem, $dhFase))->fetchAll();

        $arrayNumeros = array();
        foreach ($resultado as $tupla) {
            $arrayNumeros[] = $tupla['DOCM_NR_DOCUMENTO'];
        }
        return $arrayNumeros;
    }

    /**
     * Busca todos os documentos da caixa que não seja autuado, arquivado e seja ativo
     * 
     * @param	int	$codlotacao	
     * @param	array	$configuracao	Obrigatório [SG_SECAO,CD_SECAO,ORDER]
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getHistoricoDocumento($idDocumento) {
        $query = "
SELECT  DTPD.DTPD_NO_TIPO,
        DOCM.DOCM_ID_DOCUMENTO,
        DOCM.DOCM_NR_DOCUMENTO,
        DOCM.DOCM_NR_DCMTO_USUARIO,
        TO_CHAR(MOVI.MOVI_DH_ENCAMINHAMENTO ,'dd/mm/yyyy HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO,
        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOVI.MOVI_CD_MATR_ENCAMINHADOR)ENCAMINHADOR,
        DOCM.DOCM_NR_DOCUMENTO_RED,
        MOFA.MOFA_DS_COMPLEMENTO,
        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA.MOFA_CD_MATRICULA) MOFA_CD_MATRICULA_NOME,
        MOFA.MOFA_CD_MATRICULA,
        TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
        FADM.FADM_ID_FASE,
        FADM.FADM_DS_FASE,
        TO_CHAR(MOFA.MOFA_DH_FASE ,'dd/mm/yyyy HH24:MI:SS') MOFA_DH_FASE,
        LOTA_1.LOTA_SIGLA_LOTACAO_1 LOTA_SIGLA_LOTACAO_ORIGEM,
        LOTA_1.LOTA_DSC_LOTACAO_1 LOTA_DSC_LOTACAO_ORIGEM,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(MOVI.MOVI_SG_SECAO_UNID_ORIGEM,MOVI.MOVI_CD_SECAO_UNID_ORIGEM) FAMILIA_ORIGEM,
        MOVI.MOVI_SG_SECAO_UNID_ORIGEM,
        MOVI.MOVI_CD_SECAO_UNID_ORIGEM,

        LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_DESTINO,
        LOTA.LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_DESTINO,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO) FAMILIA_DESTINO,
        MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO,
        MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO,

        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MODE_MOVI.MODE_CD_MATR_RECEBEDOR) RECEBEDOR,
        TO_CHAR(MODE_MOVI.MODE_DH_RECEBIMENTO ,'dd/mm/yyyy HH24:MI:SS') MODE_DH_RECEBIMENTO,
        ANEX.ANEX_NR_DOCUMENTO_INTERNO NR_RED,
        SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MODP.MODP_CD_MAT_PESSOA_DESTINO) MODP_CD_MAT_PESSOA_DESTINO,
        MOFA_ID_MOVIMENTACAO,
        DOCM.DOCM_ID_TP_EXTENSAO,
        ANEX.ANEX_ID_TP_EXTENSAO

FROM    SAD_TB_DOCM_DOCUMENTO DOCM
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
        LEFT JOIN SAD_TB_ANEX_ANEXO ANEX
        ON ANEX.ANEX_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO 
        AND    ANEX.ANEX_DH_FASE = MOFA.MOFA_DH_FASE
        AND    ANEX.ANEX_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
        INNER JOIN RH_CENTRAL_LOTACAO LOTA
        ON MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = LOTA.LOTA_SIGLA_SECAO
        AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = LOTA.LOTA_COD_LOTACAO
        INNER JOIN SAD_TB_FADM_FASE_ADM FADM
        ON FADM.FADM_ID_FASE = MOFA.MOFA_ID_FASE
        LEFT OUTER JOIN SAD_TB_MODP_DESTINO_PESSOA MODP
        ON  MODP.MODP_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
        AND MODP.MODP_SG_SECAO_UNID_DESTINO =  MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO
        ,
        ( SELECT LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_1,
            LOTA_DSC_LOTACAO LOTA_DSC_LOTACAO_1, 
            LOTA_COD_LOTACAO LOTA_COD_LOTACAO_1,
            LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_1
            FROM RH_CENTRAL_LOTACAO
        ) LOTA_1                                       
        WHERE  DOCM.DOCM_ID_DOCUMENTO = ?
        AND    MOVI.MOVI_SG_SECAO_UNID_ORIGEM = LOTA_1.LOTA_SIGLA_SECAO_1
        AND    MOVI.MOVI_CD_SECAO_UNID_ORIGEM = LOTA_1.LOTA_COD_LOTACAO_1
        ORDER BY MOFA.MOFA_DH_FASE DESC";
        return $this->_db->query($query, array($idDocumento))->fetchAll();
    }

    /**
     * Retorna todas as partes e vistas que o documento possui
     * @param type $idDocumento
     * @param type $tipoDocumento
     * @param type $tipoParte
     * @return type
     * @deprecated since version 12171
     */
    public function getPartesVistas($idDocumento, $tipoDocumento, $tipoParte = Trf1_Sisad_Definicoes::PARTE_VISTA) {
        $rn_ParteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        return $rn_ParteVistas->getPartesVistas($idDocumento, $tipoDocumento, $tipoParte);
    }

    /**
     * Verifica se a matricula passada tem parte ou vista para o documento solicitado
     * 
     * @param	string     $matricula	
     * @param	array      $arrayParteVista	
     * @param	int        $idDocumento	
     * @param	int        $tipo
     * @return	boolean
     * @author	Desconhecido
     * @tutorial se não for passado um array de partes a função busca elas 
     * @deprecated since version 12171
     * de acordo com o documento ou processo e o tipo de parte passado como parametro
     */
    public function validaParteVista($matricula, array $arrayParteVista = null, $idDocumento = null, $tipoDocumento = null, $tipoParte = Trf1_Sisad_Definicoes::PARTE_VISTA) {
        $rn_ParteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        return $rn_ParteVistas->validaParteVista($matricula, $arrayParteVista, $idDocumento, $tipoDocumento, $tipoParte);
    }

    /**
     * Valida se o usuário solicitado pode visualizar o documento especificado.
     * 
     * @param	array      $documento	
     * @param	String     $mat_usuario	
     * @param	array      $array_vista	
     * @return	array('sigilo' => 'S' ou 'N', 'tem_vista' => 'S' ou 'N')
     * @author	Leidison Siqueira Barbosa
     * @tutorial se não for passado um array de vistas a função busca elas 
     * @deprecated since version 11300
     * de acordo com o documento ou processo
     */
    public function statusSigiloVista($documento, $matricula, $array_vista = null) {
        $rn_ParteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        return $rn_ParteVistas->statusSigiloVista($documento, $matricula, $array_vista);
    }

    /**
     * Valida se o usuário solicitado pode visualizar o documento especificado.
     * 
     * @param	array      $documento	
     * @param	String     $mat_usuario	
     * @param	array      $array_vista	
     * @return	array('sigilo' => 'S' ou 'N', 'tem_vista' => bool)
     * @author	Leidison Siqueira Barbosa
     * @tutorial se não for passado um array de vistas a função busca elas 
     * @deprecated since version 12171
     * de acordo com o documento ou processo
     */
    public function isVisivel($documento, $matricula, $array_vista = null) {
        $rn_ParteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        return $rn_ParteVistas->statusSigiloVista($documento, $matricula, $array_vista);
    }

    /**
     * Verifica se o documento é divulgado
     * @param int $idDocumento
     * @return boolean
     */
    public function isDivulgado($idDocumento) {
        //Colocar regras de negócio que estipulem que o documento é dibulgado aqui
        return false;
    }

    /**
     * Audita a tabela de documentos.
     * 
     * ATENÇÃO: NO CASO DE AUTERAÇÃO. SE UM INDICE NO ARRAY DE AUTERAÇÃO NÃO FOR
     * ENCONTRADO ENTÃO SEU VALOR SERÁ O DO SELECT DOS DADOS ANTIGOS DO DOCUMENTO.
     * 
     * @param string $shortName
     * @param string $acao
     * @param array $documentoManipulado
     * @param arary $dadosNovos
     * @return none Não retorna nenhum dado
     */
    public function auditar($shortName, $acao, $documentoManipulado, $dadosNovos = null) {
        try {
            $tb_auditoria = new Application_Model_DbTable_Sisad_SadTbDocmAuditoria();
            $tb_principal = new Application_Model_DbTable_Sisad_SadTbDocmDocumento();

            $dual = new Application_Model_DbTable_Dual();

            $shortName = strtoupper($shortName);
            $dataTimeStamp = $dual->localtimestampDb();

            $data_audit[$shortName . '_TS_OPERACAO'] = $dataTimeStamp['DATA'];
            $data_audit[$shortName . '_CD_MATRICULA_OPERACAO'] = $this->_userNs->matricula;
            $data_audit[$shortName . '_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
            $data_audit[$shortName . '_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

            if ($acao == Trf1_Sisad_Definicoes::AUDITORIA_INSERIR) {

                $data_audit[$shortName . '_IC_OPERACAO'] = $acao;
                foreach ($dadosNovos as $key => $value) {
                    $data_audit['NEW_' . $key] = $value;
                }
            } elseif ($acao == Trf1_Sisad_Definicoes::AUDITORIA_EXCLUIR) {

                $data_audit[$shortName . '_IC_OPERACAO'] = $acao;
                $dadosRow = $tb_principal->find($documentoManipulado['DOCM_ID_DOCUMENTO'])->current()->toArray();
                foreach ($dadosRow as $key => $value) {
                    $data_audit['OLD_' . $key] = $value;
                }
            } elseif ($acao == Trf1_Sisad_Definicoes::AUDITORIA_ALTERAR) {

                $data_audit[$shortName . '_IC_OPERACAO'] = $acao;

                $dadosRow = $tb_principal->find($documentoManipulado['DOCM_ID_DOCUMENTO'])->current()->toArray();

                foreach ($dadosRow as $key => $value) {
                    $data_audit['OLD_' . $key] = $value;
                    //caso não seja passado o indice no array $dadosNovos então o valor será o antigo.
                    $data_audit['NEW_' . $key] = (isset($dadosNovos[$key]) ? $dadosNovos[$key] : $value);
                }
            }
            $tb_auditoria->createRow($data_audit)
                    ->save();
            return array('mensagem' => 'Dados de auditoria salvos com sucesso.', 'validado' => true);
        } catch (Exception $e) {
            return array('mensagem' => $e->getMessage(), 'validado' => false);
        }
    }

    /**
     * Realiza o parecer em um documento
     * @param type $documento
     * @param type $parecer
     * @param type $autoCommit
     */
    public function parecer($documento, $dadosParecer, $anexos = null, $autoCommit = true) {
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        try {
            if ($autoCommit) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
            }
            $userNs = new Zend_Session_Namespace('userNs');
            $Dual = new Application_Model_DbTable_Dual();

            $datahora = $Dual->sysdate();

            $arrayFase = array(
                'MOFA_ID_MOVIMENTACAO' => $documento['MOFA_ID_MOVIMENTACAO']
                , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_PARECER_SISAD
                , 'MOFA_CD_MATRICULA' => $userNs->matricula
                , 'MOFA_DS_COMPLEMENTO' => new Zend_Db_Expr("'" . $dadosParecer['MOFA_DS_COMPLEMENTO'] . "'")
                , 'MOFA_DH_FASE' => $datahora
            );
            //armazena os dados de fase
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($arrayFase);
            $rowMofaMoviFase->save();

            //caso tenha anexos armazenar os anexos
            $mensagem = $this->inserirAnexo($documento, 'parecer', $anexos, $datahora);
            if ($mensagem['validado'] == false) {
                return $mensagem;
            }

            if ($autoCommit) {
                $db->commit();
            }
            return array('validado' => true, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': Parecer efetuado com sucesso.');
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            return array('validado' => false, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': Erro ao executar o parecer. Mensagem: ' . $exc->getMessage());
        }
    }

    /**
     * Realiza o despacho em um documento
     * @param type $documento
     * @param type $parecer
     * @param type $autoCommit
     */
    public function despacho($documento, $dadosParecer, $anexos = null, $autoCommit = true) {
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        try {
            if ($autoCommit) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
            }
            $userNs = new Zend_Session_Namespace('userNs');
            $Dual = new Application_Model_DbTable_Dual();

            $datahora = $Dual->sysdate();

            $arrayFase = array(
                'MOFA_ID_MOVIMENTACAO' => $documento['MOFA_ID_MOVIMENTACAO']
                , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_DESPACHO_SISAD
                , 'MOFA_CD_MATRICULA' => $userNs->matricula
                , 'MOFA_DS_COMPLEMENTO' => new Zend_Db_Expr("'" . $dadosParecer['MOFA_DS_COMPLEMENTO'] . "'")
                , 'MOFA_DH_FASE' => $datahora
            );
            //armazena os dados de fase
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($arrayFase);
            $rowMofaMoviFase->save();

            //caso tenha anexos armazenar os anexos

            $mensagem = $this->inserirAnexo($documento, 'despacho', $anexos, $datahora);
            if ($mensagem['validado'] == false) {
                return $mensagem;
            }
            if ($autoCommit) {
                $db->commit();
            }
            return array('validado' => true, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': Despacho efetuado com sucesso.');
        } catch (Exception $exc) {
            if ($autoCommit) {
                $db->rollBack();
            }
            return array('validado' => false, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': Erro ao executar o despacho. Mensagem: ' . $exc->getMessage());
        }
    }

    /**
     * Função para incluir arquivos no red e adicionar como anexo sem metadado a um documento.
     * @param array $documento
     * @param array $uploads
     * @param array $configuracoes
     * @param bolean $autoCommit
     * @return array
     */
    public function anexarDocumentosSemMetadados($documento, $uploads, $configuracoes) {
        $app_multiupload_upload = new App_Multiupload_Upload();
        $dbTableAnexo = new Application_Model_DbTable_Sisad_SadTbAnexAnexo();
        $dbTableDual = new Application_Model_DbTable_Dual();

        try {
            //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
            if (!isset($configuracoes['AUTOCOMMIT']) || $configuracoes['AUTOCOMMIT'] === true) {
                $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                $adapter->beginTransaction();
            }
            if (count($uploads) > 0) {

                /*
                 * Criando uma session para o anexo incluído para o caso de
                 * um encaminhamento de vários documentos
                 */
                $userNs = new Zend_Session_Namespace('userNs');
                $sessionAnexos = new Zend_Session_Namespace('Sisad_Anexos_' . $userNs->matricula);
                $anexosSessao = NULL;
                $datahora = $dbTableDual->sysdate();

                $cont = 0;
                foreach ($uploads as $upload) {
                    //Caso venha um campo sem arquivo deverá passar para o próximo arquivo.
                    if ($upload['name'] != '') {
                        if ($sessionAnexos->anexos) {
                            $retornoAnexo = $sessionAnexos->anexos[$cont];
                        } else {
                            $retornoAnexo = $app_multiupload_upload->incluirArquivoNoRed($upload, 'SISAD');
                        }

                        if (!$retornoAnexo['sucesso']) {
                            //caso ocorra um erro com algum anexo cancelar a operação toda
                            return array(
                                'sucesso' => false
                                , 'mensagem' => 'Erro ao incluir anexo sem metadado: ' . $retornoAnexo['mensagem']
                                , 'status' => 'error'
                                , 'dados' => $retornoAnexo['dados']
                            );
                        }

                        $dadosAnexo = array(
                            'ANEX_ID_DOCUMENTO' => $documento['DOCM_ID_DOCUMENTO']
                            , 'ANEX_ID_MOVIMENTACAO' => (isset($configuracoes['ID_MOVIMENTACAO']) ? $configuracoes['ID_MOVIMENTACAO'] : null )
                            , 'ANEX_DH_FASE' => $datahora
                            , 'ANEX_NR_DOCUMENTO_INTERNO' => $retornoAnexo['dados']['ID_DOCUMENTO']
                            , 'ANEX_NM_ANEXO' => $retornoAnexo['dados']['NOME']
                            , 'ANEX_ID_TP_EXTENSAO' => $retornoAnexo['dados']['ANEX_ID_TP_EXTENSAO']
                        );

                        /*
                         * verificar se o arquivo já pertence ao documento
                         * Se não pertence cadastra. Se pertence não acadastra.
                         */
                        $idDocumento = $documento['DOCM_ID_DOCUMENTO'];
                        $idDocInterno = $retornoAnexo['dados']['ID_DOCUMENTO'];
                        $verificaAnexo = $dbTableAnexo->fetchAll("ANEX_ID_DOCUMENTO = $idDocumento AND ANEX_NR_DOCUMENTO_INTERNO = $idDocInterno");
                        if (count($verificaAnexo->toArray()) == 0) {
                            $rowAnexAnexo = $dbTableAnexo->createRow($dadosAnexo);
                            $rowAnexAnexo->save();
                        }

                        $anexosSemMetadados[] = $dadosAnexo;
                        $anexosSessao[] = $retornoAnexo;
                        $cont++;
                    }
                }
                //Se for mais de um documento, armazenar os anexos em sessão
                if ($configuracoes['QTD_DOCUMENTOS'] > 1) {
                    $sessionAnexos->anexos = $anexosSessao;
                }

                //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
                if (!isset($configuracoes['AUTOCOMMIT']) || $configuracoes['AUTOCOMMIT'] === true) {
                    $adapter->commit();
                }
                $retorno = array(
                    'sucesso' => true
                    , 'mensagem' => 'Arquivo(s) anexado(s) com sucesso.'
                    , 'status' => 'success'
                    , 'dados' => $anexosSemMetadados
                );
            } else {
                $retorno = array(
                    'sucesso' => false
                    , 'mensagem' => 'Nenhum upload foi adicionado a variavel.'
                    , 'status' => 'error'
                    , 'dados' => array()
                );
            }
        } catch (Exception $e) {
            //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
            if (!isset($configuracoes['AUTOCOMMIT']) || $configuracoes['AUTOCOMMIT'] === true) {
                $adapter->rollBack();
            }
            $retorno = array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao incluir anexo sem metadado: ' . $e->getMessage()
                , 'status' => 'error'
                , 'dados' => array()
            );
        }
        return $retorno;
    }

    /**
     * Função de inserir anexos. Porém ela esta depreciada favor utilizar anexarDocumentosSemMetadados
     * @param type $documento
     * @param type $nomeAnexo
     * @param type $anexos
     * @param type $datahora
     * @return type
     * @deprecated since version xxx
     */
    public function inserirAnexo($documento, $nomeAnexo, $anexos, $datahora) {
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        if ($anexos->getFileName()) {
            $upload = new App_Multiupload_Upload();
            $nrDocsRed = $upload->incluirarquivos($anexos);
            if (isset($nrDocsRed['incluidos'])) {
                $anexAnexo = array(
                    'ANEX_ID_DOCUMENTO' => $documento['DOCM_ID_DOCUMENTO']
                    , 'ANEX_DH_FASE' => $datahora
                    , 'ANEX_ID_MOVIMENTACAO' => $documento['MOFA_ID_MOVIMENTACAO']
                    , 'ANEX_NM_ANEXO' => $nomeAnexo
                );
                foreach ($nrDocsRed['incluidos'] as $value) {
                    $anexAnexo['ANEX_NR_DOCUMENTO_INTERNO'] = $value;
                    $anexAnexo['ANEX_ID_TP_EXTENSAO'] = 1; //tipo padrão pdf colocar depois na classe de constrantes
                    $rowAnexAnexo = $SadTbAnexAnexo->createRow($anexAnexo);
                    $rowAnexAnexo->save();
                }
                return array('validado' => true, 'mensagem' => 'Anexo inserido com sucesso');
            } elseif (isset($nrDocsRed['existentes'])) {
                $arrayMensagem = array();
                foreach ($nrDocsRed['existentes'] as $existentes) {
                    $arrayMensagem[] = 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': Anexo ' . $existentes['NOME'] . ' pertence ao documento nº ' . $existentes['NR_DOCUMENTO'];
                }
                return array('validado' => false, 'mensagem' => $arrayMensagem);
            } elseif (isset($nrDocsRed['erro'])) {
                return array('validado' => false, 'mensagem' => 'Documento ' . $documento['MASC_NR_DOCUMENTO'] . ': Erro ao armazernar os anexos. Mensagem: ' . $nrDocsRed['erro']);
            }
        } else {
            return array('validado' => true, 'mensagem' => 'Não existem anexos para inserir.');
        }
    }

    /**
     * Realiza o encaminhamento de um documento para a caixa de entrada de
     * alguma unidade.
     * 
     * @param type $documento
     * @param type $destino
     * @param type $uploads
     * @param type $dadosComplementares utiliza os índeces ('TIPO'=>(PESSOA OU UNIDADE),'TEXTO_ENCAMINHAMENTO'=>STRING,'ENCAMINHAMENTO_POR_CADASTRO'=>BOLEAN,'AUTOCOMMIT'=>BOLEAN,'SYSDATE'=>STRING(DATA_HORA))
     * @return array {'status':bolean, 'mensagem':string}
     * @throws Exception
     */
    public function encaminhar($documento, $destino, $uploads, $dadosComplementares) {

        $rn_pessoa = new Trf1_Rh_Negocio_Pessoa();
        $textoPrimeiroCadastro = '';
        try {
            //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
            if (!isset($dadosComplementares['AUTOCOMMIT']) || $dadosComplementares['AUTOCOMMIT'] === true) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
            }
            $dual = new Application_Model_DbTable_Dual();
            //pega a hora do sistema caso não passada por parametro
            $sysdate = (isset($dadosComplementares['SYSDATE']) ? $dadosComplementares['SYSDATE'] : $dual->sysdatehoraDb());
            //pega o destino
            //caso seja a primeira movimentação
            if (isset($dadosComplementares['ENCAMINHAMENTO_POR_CADASTRO']) && $dadosComplementares['ENCAMINHAMENTO_POR_CADASTRO'] === true) {
                $textoPrimeiroCadastro = 'Documento cadastrado e encaminhado para a caixa da unidade.';
                $origem = $destino;
            } else {
                //pega ultima movimentação do documento
                $ultimaMovimentacao = $this->retornaUltimaMovimentacao($documento);
                $origem = array('SIGLA' => $ultimaMovimentacao['MODE_SG_SECAO_UNID_DESTINO'], 'CODIGO' => $ultimaMovimentacao['MODE_CD_SECAO_UNID_DESTINO']);
            }

            //caso a origem esteja vazia
            if (empty($origem['SIGLA']) || empty($origem['CODIGO'])) {

                throw new Exception('O valor da sigla ou código da seção de origem estão vazios', 1, null);
            }

            //caso o destino esteja vazia
            if (empty($destino['SIGLA']) || empty($destino['CODIGO'])) {
                throw new Exception('O valor da sigla ou código da seção de destino estão vazios', 1, null);
            }

            //pega todos os responsáveis por todas as unidades pela cache
            $responsaveisAgrupados = $rn_pessoa->retornaCacheResponsaveisAgrupadosPorUnidade();
            //verifica se unidade não possui responsáveis
            if (!isset($responsaveisAgrupados[$destino['SIGLA'] . '|' . $destino['CODIGO']]) || count($responsaveisAgrupados[$destino['SIGLA'] . '|' . $destino['CODIGO']]) == 0) {
                return array(
                    'sucesso' => false
                    , 'mensagem' => 'Não existem pessoas responsáveis pela unidade selecionada. Favor escolher uma unidade com responsáveis.'
                    , 'status' => 'notice'
                    , 'dados' => array('documento' => $documento, 'uploads' => $uploads, 'configuracoes' => $dadosComplementares)
                );
            }
            //caso possua um texto que descreve o conteudo da fase de encaminhamento
            if (isset($dadosComplementares['TEXTO_ENCAMINHAMENTO']) && $dadosComplementares['TEXTO_ENCAMINHAMENTO'] != '') {
                $texto = $dadosComplementares['TEXTO_ENCAMINHAMENTO'];
            } else {
                //caso tenha um texto de primeiro encaminhamento
                $texto = ($textoPrimeiroCadastro != '' ? $textoPrimeiroCadastro : 'Documento encaminhado.');
            }
            //inclui uma tupla na tabela moviMovimentacao
            //basicamente os dados do encaminhamento
            $dbTable_Movi = new Application_Model_DbTable_Sisad_SadTbMoviMovimentacao();
            $dataMovi = array(
                'MOVI_SG_SECAO_UNID_ORIGEM' => $origem['SIGLA']
                , 'MOVI_CD_SECAO_UNID_ORIGEM' => $origem['CODIGO']
                , 'MOVI_CD_MATR_ENCAMINHADOR' => $this->_userNs->matricula
                , 'MOVI_DH_ENCAMINHAMENTO' => (is_a($sysdate, 'Zend_Db_Expr') ? $sysdate : new Zend_Db_Expr("TO_DATE('" . $sysdate . "','DD/MM/YYYY HH24:MI:SS')"))
            );
            $idMovi = $dbTable_Movi->createRow($dataMovi)->save();
            //inclui uma tupla na tabela modoMoviDocumento
            //associa a movimentação ao documento
            $dbTable_Modo = new Application_Model_DbTable_Sisad_SadTbModoMoviDocumento();
            $dataModo = array(
                'MODO_ID_MOVIMENTACAO' => $idMovi
                , 'MODO_ID_DOCUMENTO' => $documento['DOCM_ID_DOCUMENTO']
            );
            $idModo = $dbTable_Modo->createRow($dataModo)->save();
            //inclui uma tupla na tabela modoMoviDocumento
            //designa a unidade que receberá o documento
            $dbTable_Mode = new Application_Model_DbTable_Sisad_SadTbModeMoviDestinatario();
            $dataMode = array(
                'MODE_ID_MOVIMENTACAO' => $idMovi
                , 'MODE_SG_SECAO_UNID_DESTINO' => $destino['SIGLA']
                , 'MODE_CD_SECAO_UNID_DESTINO' => $destino['CODIGO']
                , 'MODE_IC_RESPONSAVEL' => 'N'
            );
            $idMode = $dbTable_Mode->createRow($dataMode)->save();
            //inclui uma tupla na tabela mofaMoviFase
            //inclui no histórico do documento a movimentação

            if (!isset($dadosComplementares['SEM_FASE']) || (isset($dadosComplementares['SEM_FASE']) && $dadosComplementares['SEM_FASE'] == false)) {
                $dbTable_Mofa = new Application_Model_DbTable_Sisad_SadTbMofaMoviFase();
                $dataMofa = array(
                    'MOFA_ID_MOVIMENTACAO' => $idMovi
                    , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_ENCAMINHAR_DOC_PROC
                    , 'MOFA_DH_FASE' => (is_a($sysdate, 'Zend_Db_Expr') ? $sysdate : new Zend_Db_Expr("TO_DATE('" . $sysdate . "','DD/MM/YYYY HH24:MI:SS')"))
                    , 'MOFA_CD_MATRICULA' => $this->_userNs->matricula
                    , 'MOFA_DS_COMPLEMENTO' => $texto
                );
                $idMofa = $dbTable_Mofa->createRow($dataMofa)->save();
            }
            $dataModp = array();
            if (isset($dadosComplementares['TIPO']) && $dadosComplementares['TIPO'] == 'PESSOA') {
                $dbTable_Modp = new Application_Model_DbTable_Sisad_SadTbModpDestinoPessoa();
                $dataModp = array(
                    'MODP_ID_MOVIMENTACAO' => $idMovi
                    , 'MODP_SG_SECAO_UNID_DESTINO' => $destino['SIGLA']
                    , 'MODP_CD_SECAO_UNID_DESTINO' => $destino['CODIGO']
                    , 'MODP_CD_MAT_PESSOA_DESTINO' => $destino['MATRICULA']
                );
                $idModp = $dbTable_Modp->createRow($dataModp)->save();
            }

            //inclui os anexos na fase de movimentação
            //inclui os anexos sem metadados caso tenha ao documento criado
            if (!empty($uploads)) {
                $complemento = array('ID_MOVIMENTACAO' => $idMovi, 'AUTOCOMMIT' => false, 'QTD_DOCUMENTOS' => $dadosComplementares['QTD_DOCUMENTOS']);
                $retornoAnexo = $this->anexarDocumentosSemMetadados($documento, $uploads, $complemento);

                if ($retornoAnexo['sucesso'] == false) {
                    return array(
                        'sucesso' => false
                        , 'mensagem' => $retornoAnexo['mensagem']
                        , 'status' => 'error'
                        , 'dados' => array('documento' => $documento, 'uploads' => $uploads, 'configuracoes' => $dadosComplementares)
                    );
                }
            }
            //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
            if (!isset($dadosComplementares['AUTOCOMMIT']) || $dadosComplementares['AUTOCOMMIT'] === true) {
                $db->commit();
            }
            return array(
                'sucesso' => true
                , 'mensagem' => 'Documento(s) encaminhado(s) com sucesso.'
                , 'status' => 'success'
                , 'dados' => array('movi' => $dataMovi, 'modo' => $dataModo, 'mode' => $dataMode, 'mofa' => $dataMofa, 'modp' => $dataModp)
            );
        } catch (Exception $e) {
            //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
            if (!isset($dadosComplementares['AUTOCOMMIT']) || $dadosComplementares['AUTOCOMMIT'] === true) {
                $db->rollBack();
            }
            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao encaminhar documento: ' . $e->getMessage() . '.'
                , 'status' => 'error'
                , 'dados' => array('movi' => $dataMovi, 'modo' => $dataModo, 'mode' => $dataMode, 'mofa' => $dataMofa)
            );
        }
    }

    /**
     * Função faz a divulgação de documentos em listas internas     
     * 
     * @param array $data
     * @param array $documento
     * @return array
     */
    public function divulgarDocumento($data, $documento) {
        $userNs = new Zend_Session_Namespace('userNs');
        $tabelaListaDivulgacao = new Application_Model_DbTable_SadTbListListaDivulgacao();
        $tabelaDocumentoLista = new Application_Model_DbTable_SadTbDoliDocumentoLista();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();

        /* Verifica se todos os documentos tem confidencialidade pública */
        if ($documento['DOCM_ID_CONFIDENCIALIDADE'] != "0") {
            return array(
                'sucesso' => false
                , 'mensagem' => 'Selecione apenas documentos públicos.'
                , 'status' => 'error'
                , 'dados' => '');
        }

        //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
        if (!isset($data['AUTOCOMMIT']) || $data['AUTOCOMMIT'] === true) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
        }

        try {
            if (isset($data['list_id_componente']) && $data['list_id_componente'] != NULL) {
                foreach ($data['list_id_componente'] as $dados) {
                    $id_grupo_divulgacao = explode(" - ", $dados);
                    $dadosListaDivul['LIST_ID_DOCUMENTO_DIVULGADO'] = $documento['DOCM_ID_DOCUMENTO'];
                    $dadosListaDivul['LIST_ID_GRUPO_DIVULGACAO'] = $id_grupo_divulgacao[0];
                    $dadosListaDivul['LIST_QT_DIAS_DIVULGACAO'] = 0;
                    $dadosListaDivul['LIST_SG_SECAO_DIVULGADORA'] = $userNs->siglasecao;
                    $dadosListaDivul['LIST_CD_LOTACAO_DIVULGADORA'] = $userNs->codlotacao;
                    $dadosListaDivul['LIST_CD_MATRICULA_DIVULGADORA'] = $userNs->matricula;
                    $dadosListaDivul['LIST_DT_INICIO_DIVULGACAO'] = new Zend_Db_Expr("TO_DATE('" . $data['LIST_DT_INICIO_DIVULGACAO'] . "','dd/mm/yyyy HH24:MI:SS')");
                    $dadosListaDivul['LIST_DT_FIM_DIVULGACAO'] = new Zend_Db_Expr("TO_DATE('" . $data['LIST_DT_FIM_DIVULGACAO'] . "','dd/mm/yyyy HH24:MI:SS')");

                    $rowListaDivulgacao = $tabelaListaDivulgacao->createRow($dadosListaDivul);
                    $id_lista_divulgacao = $rowListaDivulgacao->save();

                    $id_documento = $documento['DOCM_ID_DOCUMENTO'];
                    $dadosDocumentoLista['DOLI_ID_DOCUMENTO'] = $id_documento;
                    $dadosDocumentoLista['DOLI_ID_LISTA_DIVULGACAO'] = $id_lista_divulgacao;
                    $rowDocumentoLista = $tabelaDocumentoLista->createRow($dadosDocumentoLista);
                    $rowDocumentoLista->save();
                }
                /* FINALIZA A MOVIMENTAÇÃO INDIVIDUAL DO DOCUMENTO. */
                $where = "DOCM_ID_DOCUMENTO = $id_documento";
                $dataUpdt = array(
                    "DOCM_IC_MOVI_INDIVIDUAL" => "N"
                );
                $tabelaSadTbDocmDocumento->update($dataUpdt, $where);
            }
            //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
            if (!isset($data['AUTOCOMMIT']) || $data['AUTOCOMMIT'] === true) {
                $db->commit();
            }
            return array(
                'sucesso' => true
                , 'mensagem' => 'Documento(s) divulgado(s) com sucesso.'
                , 'status' => 'success'
                , 'dados' => ''
            );
        } catch (Zend_Exception $e) {
            //se não tiver nenhuma definição de autocommit ou se autocommit for igual a true
            if (!isset($data['AUTOCOMMIT']) || $data['AUTOCOMMIT'] === true) {
                $db->rollBack();
            }
            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao divulgar documento: ' . $e->getMessage() . '.'
                , 'status' => 'error'
                , 'dados' => ''
            );
        }
    }

    /**
     * 
     */
    public function cadastrarMinuta() {
        //Colocar o código do cadastro de minutas
    }

    /**
     * Cadastra um documento no sisad, se necessário poderá estabelecer para que lugar
     * o documento vai ao ser cadastrado.
     * @param array $documento
     * @param array $partes
     * @param array $uploads
     * @param array $dadosComplementares (
     *  'CADASTRO'=>ARRAY('TIPO'=>STRING)
     *  ,'PARTES' => ARRAY('DADOS'=>ARRAY())
     *  ,'ENCAMINHAMENTO'=>ARRAY('TIPO'=>STRING,'PARA_MINHA_CAIXA_PESSOAL'=>BOOLEAN,'MATRICULA_DESTINO'=>STRING,'UNIDADE'=>STRING)
     *  ,'AUTUACAO'=>ARRAY('AUTUAR'=>BOOLEAN,'TEXTO'=>STRING)
     * @return array {'status':bolean, 'mensagem':string}
     */
    public function cadastrarInterno($documento, $partes, $uploads, $dadosComplementares) {
        /**
         * Limpando a session dos anexos
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $sessionAnexos = new Zend_Session_Namespace('Sisad_Anexos_' . $userNs->matricula);
        $sessionAnexos->anexos = NULL;
        $dadosComplementares['AUTOCOMMIT'] = (isset($dadosComplementares['AUTOCOMMIT']) ? $dadosComplementares['AUTOCOMMIT'] : true);
        try {
            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
            }

            //seta a tolerancia de tempo para 30 minutos
            set_time_limit(1800);

            $app_multiupload_upload = new App_Multiupload_Upload();
            $dual = new Application_Model_DbTable_Dual();

            $nrRedAnexoPrincipal = null;

            //se tiver um arquivo para ser o anexo principal
            if (isset($uploads['arquivo_principal']) && $uploads['arquivo_principal']['name'] != '') {
                $retornoAnexo = $app_multiupload_upload->incluirArquivoNoRed($uploads['arquivo_principal'], 'SISAD');
                //se o documento não foi cadastrado no RED com sucesso
                if (!$retornoAnexo['sucesso']) {
                    return array(
                        'sucesso' => false
                        , 'mensagem' => $retornoAnexo['mensagem']
                        , 'status' => 'error'
                        , 'dados' => $retornoAnexo['dados']
                    );
                } else {
                    $nrRedAnexoPrincipal = $retornoAnexo['dados']['ID_DOCUMENTO'];
                }
            }
            //verifica se os dados da unidade geradora precisam de algum tratamento
            if (strpos($documento['DOCM_CD_LOTACAO_GERADORA'], '|') !== false) {
                $arrayLotacaoGeradora = explode('|', $documento['DOCM_CD_LOTACAO_GERADORA']);
                $documento['DOCM_SG_SECAO_GERADORA'] = $arrayLotacaoGeradora[0];
                $documento['DOCM_CD_LOTACAO_GERADORA'] = $arrayLotacaoGeradora[1];
            }
            //verifica se os dados da unidade redatora precisam de algum tratamento
            if (strpos($documento['DOCM_CD_LOTACAO_REDATORA'], '|') !== false) {
                $arrayLotacaoRedatora = explode('|', $documento['DOCM_CD_LOTACAO_REDATORA']);
                $documento['DOCM_SG_SECAO_REDATORA'] = $arrayLotacaoRedatora[0];
                $documento['DOCM_CD_LOTACAO_REDATORA'] = $arrayLotacaoRedatora[1];
            }

            $numeroSequencialDocumento = $this->retornaNumeroSequencial($documento['DOCM_SG_SECAO_REDATORA'], $documento['DOCM_CD_LOTACAO_REDATORA'], $documento['DOCM_ID_TIPO_DOC']);
            $dataHora = (is_a($documento['DOCM_DH_CADASTRO'], 'Zend_Db_Expr') ? $documento['DOCM_DH_CADASTRO'] : new Zend_Db_Expr("TO_DATE('" . $dual->sysdatehoraDb() . "','DD/MM/YYYY HH24:MI:SS')"));
            $novaTupla = array(
                'DOCM_NR_DOCUMENTO' => $this->retornaNumeroDocumento($documento['DOCM_SG_SECAO_REDATORA'], $documento['DOCM_CD_LOTACAO_REDATORA'], $documento['DOCM_CD_LOTACAO_GERADORA'], $documento['DOCM_ID_TIPO_DOC'], $numeroSequencialDocumento)
                , 'DOCM_NR_SEQUENCIAL_DOC' => $numeroSequencialDocumento
                , 'DOCM_DH_CADASTRO' => $dataHora
                , 'DOCM_CD_MATRICULA_CADASTRO' => $this->_userNs->matricula
                , 'DOCM_SG_SECAO_GERADORA' => $documento['DOCM_SG_SECAO_GERADORA']
                , 'DOCM_CD_LOTACAO_GERADORA' => $documento['DOCM_CD_LOTACAO_GERADORA']
                , 'DOCM_SG_SECAO_REDATORA' => $documento['DOCM_SG_SECAO_REDATORA']
                , 'DOCM_CD_LOTACAO_REDATORA' => $documento['DOCM_CD_LOTACAO_REDATORA']
                , 'DOCM_IC_PROCESSO_AUTUADO' => (is_array($dadosComplementares['AUTUACAO']) && $dadosComplementares['AUTUACAO']['AUTUAR'] === true ? 'S' : 'N')
                , 'DOCM_ID_TIPO_DOC' => $documento['DOCM_ID_TIPO_DOC']
                , 'DOCM_NR_DCMTO_USUARIO' => $documento['DOCM_NR_DCMTO_USUARIO']
                , 'DOCM_ID_PCTT' => $documento['DOCM_ID_PCTT']
                , 'DOCM_DS_ASSUNTO_DOC' => (is_a($documento['DOCM_DS_ASSUNTO_DOC'], 'Zend_Db_Expr') ? $documento['DOCM_DS_ASSUNTO_DOC'] : new Zend_Db_Expr(" CAST( '" . $documento['DOCM_DS_ASSUNTO_DOC'] . "' AS VARCHAR(4000)) "))
                , 'DOCM_DS_PALAVRA_CHAVE' => $documento['DOCM_DS_PALAVRA_CHAVE']
                , 'DOCM_ID_TIPO_SITUACAO_DOC' => $documento['DOCM_ID_TIPO_SITUACAO_DOC']
                , 'DOCM_ID_CONFIDENCIALIDADE' => $documento['DOCM_ID_CONFIDENCIALIDADE']
                , 'DOCM_IC_DOCUMENTO_EXTERNO' => 'N'
                , 'DOCM_NR_DOCUMENTO_RED' => $nrRedAnexoPrincipal
                , 'DOCM_ID_TP_EXTENSAO' => $retornoAnexo['dados']['ANEX_ID_TP_EXTENSAO']
            );

            //inseri os dados do documento externo na tabela DOCM
            $dbTableDocumento = new Application_Model_DbTable_Sisad_SadTbDocmDocumento();
            $idDocumento = $dbTableDocumento->createRow($novaTupla)->save();
            $novaTupla['DOCM_ID_DOCUMENTO'] = $idDocumento;
            //elimina o arquivo principal do array de uploads

            unset($uploads['arquivo_principal']);
            //se tiver algum arquivo para servi de anexo sem metadados
            //qualquer arquivo que tiver (fora o arquivo principal) será considerado 
            //anexo sem metadado
            if (is_null($nrRedAnexoPrincipal) && !empty($uploads)) {
                return array(
                    'sucesso' => false
                    , 'mensagem' => 'Não é possível incluir anexos ao documento se não for selecionado um arquivo principal.'
                    , 'status' => 'notice'
                    , 'dados' => array()
                );
            }


            //inclui os anexos sem metadados caso tenha ao documento criado
            if (!empty($uploads)) {
                $complemento = array('AUTOCOMMIT' => false);
                $retornoAnexo = $this->anexarDocumentosSemMetadados($novaTupla, $uploads, $complemento);
                if ($retornoAnexo['sucesso'] == false) {
                    return array(
                        'sucesso' => false
                        , 'status' => 'error'
                        , 'mensagem' => $retornoAnexo['mensagem']
                        , 'dados' => array('documento' => $novaTupla, 'uploads' => $uploads, 'configuracoes' => $dadosComplementares)
                    );
                }
            }
            //verifica se o tipo de cadastro não é para processo administrativo
            if (!isset($dadosComplementares['TIPO']) || $dadosComplementares['TIPO'] != 'PROCESSO_ADMINISTRATIVO') {
                //adiciona as partes ou vistas ao documento ou processo
                $rn_parteVistas = new Trf1_Sisad_Negocio_ParteVistas();

                if (!is_array($partes['partes_pessoa_trf'])) {
                    $partes['partes_pessoa_trf'] = Zend_Json::decode($partes['partes_pessoa_trf']);
                    $partes['partes_unidade'] = Zend_Json::decode($partes['partes_unidade']);
                    $partes['partes_pess_ext'] = Zend_Json::decode($partes['partes_pess_ext']);
                    $partes['partes_pess_jur'] = Zend_Json::decode($partes['partes_pess_jur']);
                }

                $retorno = $rn_parteVistas->addParteVistas($novaTupla, $partes, false);
                if ($retorno['validado'] == false) {
                    return array('sucesso' => false, 'mensagem' => $retorno['mensagem'], 'status' => 'error', 'dados' => array());
                }
            }
            //se for preciso autuar
            if (is_array($dadosComplementares['AUTUACAO']) && $dadosComplementares['AUTUACAO']['AUTUAR'] === true) {
                $configuracoes = $dadosComplementares;
                $configuracoes['DURANTE_CADASTRO'] = true;
                $configuracoes['AUTOCOMMIT'] = false;
                //substitui os dados do documento pelo processo administrativo

                $retornoAutuar = $this->autuar($novaTupla, $partes, $dadosComplementares['AUTUACAO'], $configuracoes);

                if ($retornoAutuar['sucesso'] == false) {
                    return array(
                        'sucesso' => false
                        , 'status' => 'error'
                        , 'mensagem' => $retornoAutuar['mensagem']
                        , 'dados' => $retornoAutuar['dados']
                    );
                }
                //os procedimentos abaixo serão realizados no documento e não no processo administrativo
            }
            //se precisar encaminhar
            if (isset($dadosComplementares['ENCAMINHAMENTO'])) {
                //realiza o encaminhamento do documento
                if ($dadosComplementares['ENCAMINHAMENTO']['TIPO'] == 'caixa_pessoal') {
                    if ($dadosComplementares['ENCAMINHAMENTO']['PARA_MINHA_CAIXA_PESSOAL']) {
                        //pega os dados de destino
                        $destino = array('MATRICULA' => $this->_userNs->matricula, 'SIGLA' => $this->_userNs->siglasecao, 'CODIGO' => $this->_userNs->codlotacao);
                    } else {
                        //pega os dados de destino
                        $arrayDestino = explode('|', $partes['caixa_minha_responsabilidade']);
                        $destino = array('MATRICULA' => $dadosComplementares['ENCAMINHAMENTO']['MATRICULA_DESTINO'], 'SIGLA' => $arrayDestino[0], 'CODIGO' => $arrayDestino[1]);
                    }
                    //realiza o encaminhamento do documento para a caixa do usuario desejado
                    $retornoEncaminhamento = $this->encaminhar(
                            $novaTupla
                            , $destino
                            , array()
                            , array(
                        'TIPO' => 'PESSOA'
                        , 'SYSDATE' => $dataHora
                        , 'ENCAMINHAMENTO_POR_CADASTRO' => true
                        , 'AUTOCOMMIT' => false
                        , 'SEM_FASE' => (isset($dadosComplementares['ENCAMINHAMENTO']['SEM_FASE']) ? $dadosComplementares['ENCAMINHAMENTO']['SEM_FASE'] === true : false)
                            )
                    );
                } elseif ($dadosComplementares['ENCAMINHAMENTO']['TIPO'] == 'caixa_rascunho') {
                    $retornoEncaminhamento = array(
                        'sucesso' => true
                        , 'status' => 'success'
                        , 'mensagem' => 'Documento interno cadastrado e encaminhado para caixa de rascunho com sucesso.'
                        , 'dados' => array()
                    );
                } elseif ($dadosComplementares['ENCAMINHAMENTO']['TIPO'] == 'caixa_unidade') {
                    //pega os dados de destino
                    $arrayDestino = explode('|', $dadosComplementares['ENCAMINHAMENTO']['UNIDADE']);
                    $destino = array('SIGLA' => $arrayDestino[0], 'CODIGO' => $arrayDestino[1]);
                    //encaminha informando que é o primeiro encaminhamento e passa os arquivos para upload
                    $retornoEncaminhamento = $this->encaminhar(
                            $novaTupla
                            , $destino
                            , array()
                            , array(
                        'SYSDATE' => $dataHora
                        , 'ENCAMINHAMENTO_POR_CADASTRO' => true
                        , 'AUTOCOMMIT' => false
                        , 'SEM_FASE' => (isset($dadosComplementares['ENCAMINHAMENTO']['SEM_FASE']) ? $dadosComplementares['ENCAMINHAMENTO']['SEM_FASE'] === true : false)
                            )
                    );
                } else {
                    return array('sucesso' => false, 'mensagem' => 'Tipo de encaminhamento não encontrado.');
                }

                //se não tiver sucesso ao encaminhar
                if (!$retornoEncaminhamento['sucesso']) {
                    if ($dadosComplementares['AUTOCOMMIT'] === true) {
                        $db->rollBack();
                    }
                    return array(
                        'sucesso' => false
                        , 'mensagem' => $retornoEncaminhamento['mensagem']
                        , 'status' => 'error'
                        , 'dados' => $retornoEncaminhamento['dados']
                    );
                }
            }
            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db->commit();
            }
            if (isset($retornoAutuar['sucesso']) && $retornoAutuar['sucesso'] === true) {
                return array('sucesso' => true, 'status' => 'success', 'mensagem' => 'Documento interno de número ' . $novaTupla['DOCM_NR_DOCUMENTO'] . ' foi cadastrado e incluído no processo administrativo de número ' . $retornoAutuar['dados']['retorno_cadastro_processo']['dados']['DOCM_NR_DOCUMENTO'] . ' com sucesso. ', 'dados' => $novaTupla, 'dados_encaminhamento' => $retornoEncaminhamento . '.');
            } else {
                return array('sucesso' => true, 'status' => 'success', 'mensagem' => 'Documento interno de número ' . $novaTupla['DOCM_NR_DOCUMENTO'] . ' foi cadastrado com sucesso.', 'dados' => $novaTupla, 'dados_encaminhamento' => $retornoEncaminhamento);
            }
        } catch (Exception $e) {
            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db->rollBack();
            }
            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao cadastrar um documento interno: ' . $e->getMessage()
                , 'status' => 'error'
                , 'dados' => $novaTupla
            );
        }
    }

    /**
     * Cadastra um documento no sisad, é necessário estabelecer para que lugar
     * o documento vai ao ser cadastrado.
     * @param array $documento
     * @param array $partes
     * @param array $uploads testando usando o fileInfo da classe Zend_File_Transfer_Adapter_Http
     * @return array {'status':bolean, 'mensagem':string}
     */
    public function cadastrarExterno($documento, $partes, $uploads, $dadosComplementares) {
        /**
         * Limpando a session dos anexos
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $sessionAnexos = new Zend_Session_Namespace('Sisad_Anexos_' . $userNs->matricula);
        $sessionAnexos->anexos = NULL;
        $dadosComplementares['AUTOCOMMIT'] = (isset($dadosComplementares['AUTOCOMMIT']) ? $dadosComplementares['AUTOCOMMIT'] : true);
        try {
            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
            }
            /*
             * LEIA - ME
             * 
             * - A função leva em consideração que o upload ja tenha sido feito
             * 
             * - A função também leva em consideração que o valor da variavel $uploads
             * foi oriundo do metodo fileInfo da classe Zend_File_Transfer_Adapter_Http
             * mas nada impede que seja utilizado outro metodo de upload desde a 
             * variavel $uploads tenha no minimo o index name e como arquivo principal
             * o indice arquivo_principal
             */

            //seta a tolerancia de tempo para 30 minutos
            set_time_limit(1800);

            $app_multiupload_upload = new App_Multiupload_Upload();
            $dual = new Application_Model_DbTable_Dual();

            $nrRedAnexoPrincipal = null;
            //se tiver um arquivo para ser o anexo principal
            if (isset($uploads['arquivo_principal']) && $uploads['arquivo_principal']['name'] != '') {
                $retornoAnexo = $app_multiupload_upload->incluirArquivoNoRed($uploads['arquivo_principal'], 'SISAD');
                //se o documento não foi cadastrado no RED com sucesso
                if (!$retornoAnexo['sucesso']) {
                    return $retornoAnexo;
                } else {
                    $nrRedAnexoPrincipal = $retornoAnexo['dados']['ID_DOCUMENTO'];
                }
            }

            $numeroSequencialDocumento = $this->retornaNumeroSequencial($this->_userNs->siglasecao, $this->_userNs->codlotacao, $documento['DOCM_ID_TIPO_DOC']);
            $dataHora = $dual->sysdatehoraDb();
            $novaTupla = array(
                'DOCM_NR_DOCUMENTO' => $this->retornaNumeroDocumento($this->_userNs->siglasecao, $this->_userNs->codlotacao, $this->_userNs->codlotacao, $documento['DOCM_ID_TIPO_DOC'], $numeroSequencialDocumento)
                , 'DOCM_NR_SEQUENCIAL_DOC' => $numeroSequencialDocumento
                , 'DOCM_DH_CADASTRO' => new Zend_Db_Expr("TO_DATE('$dataHora','dd/mm/yyyy HH24:MI:SS')")
                , 'DOCM_CD_MATRICULA_CADASTRO' => $this->_userNs->matricula
                , 'DOCM_SG_SECAO_GERADORA' => $this->_userNs->siglasecao
                , 'DOCM_CD_LOTACAO_GERADORA' => $this->_userNs->codlotacao
                , 'DOCM_SG_SECAO_REDATORA' => $this->_userNs->siglasecao
                , 'DOCM_CD_LOTACAO_REDATORA' => $this->_userNs->codlotacao
                , 'DOCM_ID_PESSOA_EXTERNO' => $documento['DOCM_ID_PESSOA_EXTERNO']
                , 'DOCM_DS_NOME_EMISSOR_EXTERNO' => $documento['DOCM_DS_NOME_EMISSOR_EXTERNO']
                , 'DOCM_ID_TIPO_DOC' => $documento['DOCM_ID_TIPO_DOC']
                , 'DOCM_NR_DCMTO_USUARIO' => $documento['DOCM_NR_DCMTO_USUARIO']
                , 'DOCM_ID_PCTT' => $documento['DOCM_ID_PCTT']
                , 'DOCM_DS_ASSUNTO_DOC' => new Zend_Db_Expr(" CAST( '" . $documento['DOCM_DS_ASSUNTO_DOC'] . "' AS VARCHAR(4000)) ")
                , 'DOCM_DS_PALAVRA_CHAVE' => $documento['DOCM_DS_PALAVRA_CHAVE']
                , 'DOCM_ID_TIPO_SITUACAO_DOC' => $documento['DOCM_ID_TIPO_SITUACAO_DOC']
                , 'DOCM_ID_CONFIDENCIALIDADE' => $documento['DOCM_ID_CONFIDENCIALIDADE']
                , 'DOCM_IC_DOCUMENTO_EXTERNO' => 'S'
                , 'DOCM_NR_DOCUMENTO_RED' => $nrRedAnexoPrincipal
            );
            //inseri os dados do documento externo na tabela DOCM
            $dbTableDocumento = new Application_Model_DbTable_Sisad_SadTbDocmDocumento();
            $idDocumento = $dbTableDocumento->createRow($novaTupla)->save();
            $novaTupla['DOCM_ID_DOCUMENTO'] = $idDocumento;



            //elimina o arquivo principal do array de uploads
            unset($uploads['arquivo_principal']);
            //se tiver algum arquivo para servi de anexo sem metadados
            //qualquer arquivo que tiver (fora o arquivo principal) será considerado 
            //anexo sem metadado
            if (is_null($nrRedAnexoPrincipal) && !empty($uploads)) {
                return array(
                    'sucesso' => false
                    , 'mensagem' => 'Não é possível inclui anexos ao documento se não for selecionado um arquivo principal.'
                    , 'status' => 'notice'
                    , 'dados' => array()
                );
            }
            //inclui os anexos sem metadados caso tenha ao documento criado
            if (!empty($uploads)) {
                $complemento = array('AUTOCOMMIT' => false);
                $retornoAnexo = $this->anexarDocumentosSemMetadados($novaTupla, $uploads, $complemento);
                if ($retornoAnexo['sucesso'] == false) {
                    return array(
                        'sucesso' => false
                        , 'status' => 'error'
                        , 'mensagem' => $retornoAnexo['mensagem']
                        , 'dados' => array('documento' => $novaTupla, 'uploads' => $uploads, 'configuracoes' => $dadosComplementares)
                    );
                }
            }
            //adiciona as partes ou vistas ao documento ou processo
            $rn_parteVistas = new Trf1_Sisad_Negocio_ParteVistas();

            if (!is_array($partes['partes_pessoa_trf'])) {
                $partes['partes_pessoa_trf'] = Zend_Json::decode($partes['partes_pessoa_trf']);
                $partes['partes_unidade'] = Zend_Json::decode($partes['partes_unidade']);
                $partes['partes_pess_ext'] = Zend_Json::decode($partes['partes_pess_ext']);
                $partes['partes_pess_jur'] = Zend_Json::decode($partes['partes_pess_jur']);
            }

            $retorno = $rn_parteVistas->addParteVistas($novaTupla, $partes, false);
            if ($retorno['validado'] == false) {
                return array('sucesso' => false, 'mensagem' => $retorno['mensagem'], 'status' => 'error', 'dados' => array());
            }
            //se for preciso autuar
            if (is_array($dadosComplementares['AUTUACAO']) && $dadosComplementares['AUTUACAO']['AUTUAR'] === true) {
                $configuracoes = $dadosComplementares;
                $configuracoes['DURANTE_CADASTRO'] = true;
                $configuracoes['AUTOCOMMIT'] = false;
                //substitui os dados do documento pelo processo administrativo

                $retornoAutuar = $this->autuar($novaTupla, $partes, $dadosComplementares['AUTUACAO'], $configuracoes);
                if ($retornoAutuar['sucesso'] == false) {
                    return array(
                        'sucesso' => false
                        , 'status' => 'error'
                        , 'mensagem' => $retornoAutuar['mensagem']
                        , 'dados' => $retornoAutuar['dados']
                    );
                }
                //os procedimentos abaixo serão realizados no documento e não no processo administrativo
            }
            //se precisar encaminhar
            if (isset($dadosComplementares['ENCAMINHAMENTO'])) {
                //realiza o encaminhamento do documento
                if ($dadosComplementares['ENCAMINHAMENTO']['TIPO'] == 'caixa_pessoal') {
                    if ($dadosComplementares['ENCAMINHAMENTO']['PARA_MINHA_CAIXA_PESSOAL']) {
                        //pega os dados de destino
                        $destino = array('MATRICULA' => $this->_userNs->matricula, 'SIGLA' => $this->_userNs->siglasecao, 'CODIGO' => $this->_userNs->codlotacao);
                    } else {
                        //pega os dados de destino
                        $arrayDestino = explode('|', $partes['caixa_minha_responsabilidade']);
                        $destino = array('MATRICULA' => $dadosComplementares['ENCAMINHAMENTO']['MATRICULA_DESTINO'], 'SIGLA' => $arrayDestino[0], 'CODIGO' => $arrayDestino[1]);
                    }
                    //realiza o encaminhamento do documento para a caixa do usuario desejado
                    $retornoEncaminhamento = $this->encaminhar(
                            $novaTupla
                            , $destino
                            , array()
                            , array(
                        'TIPO' => 'PESSOA'
                        , 'SYSDATE' => $dataHora
                        , 'ENCAMINHAMENTO_POR_CADASTRO' => true
                        , 'AUTOCOMMIT' => false
                        , 'SEM_FASE' => (isset($dadosComplementares['ENCAMINHAMENTO']['SEM_FASE']) ? $dadosComplementares['ENCAMINHAMENTO']['SEM_FASE'] === true : false)
                            )
                    );
                } elseif ($dadosComplementares['ENCAMINHAMENTO']['TIPO'] == 'caixa_rascunho') {
                    $retornoEncaminhamento = array(
                        'sucesso' => true
                        , 'status' => 'success'
                        , 'mensagem' => 'Documento interno cadastrado e encaminhado para caixa de rascunho com sucesso.'
                        , 'dados' => array()
                    );
                } elseif ($dadosComplementares['ENCAMINHAMENTO']['TIPO'] == 'caixa_unidade') {
                    //pega os dados de destino
                    $arrayDestino = explode('|', $dadosComplementares['ENCAMINHAMENTO']['UNIDADE']);
                    $destino = array('SIGLA' => $arrayDestino[0], 'CODIGO' => $arrayDestino[1]);
                    //encaminha informando que é o primeiro encaminhamento e passa os arquivos para upload
                    $retornoEncaminhamento = $this->encaminhar(
                            $novaTupla
                            , $destino
                            , array()
                            , array(
                        'SYSDATE' => $dataHora
                        , 'ENCAMINHAMENTO_POR_CADASTRO' => true
                        , 'AUTOCOMMIT' => false
                        , 'SEM_FASE' => (isset($dadosComplementares['ENCAMINHAMENTO']['SEM_FASE']) ? $dadosComplementares['ENCAMINHAMENTO']['SEM_FASE'] === true : false)
                            )
                    );
                } else {
                    return array('sucesso' => false, 'mensagem' => 'Tipo de encaminhamento não encontrado.');
                }

                //se não tiver sucesso ao encaminhar
                if (!$retornoEncaminhamento['sucesso']) {
                    if ($dadosComplementares['AUTOCOMMIT'] === true) {
                        $db->rollBack();
                    }
                    return array(
                        'sucesso' => false
                        , 'mensagem' => $retornoEncaminhamento['mensagem']
                        , 'status' => 'error'
                        , 'dados' => $retornoEncaminhamento['dados']
                    );
                }
            }

            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db->commit();
            }
            $db->closeConnection();
            if (isset($retornoAutuar['sucesso']) && $retornoAutuar['sucesso'] === true) {
                return array('sucesso' => true, 'status' => 'success', 'mensagem' => 'Documento externo de número ' . $novaTupla['DOCM_NR_DOCUMENTO'] . ' foi cadastrado e incluido no processo administrativo de número ' . $retornoAutuar['dados']['retorno_cadastro_processo']['dados']['DOCM_NR_DOCUMENTO'] . ' com sucesso', 'dados' => $novaTupla, 'dados_encaminhamento' => $retornoEncaminhamento);
            } else {
                return array('sucesso' => true, 'status' => 'success', 'mensagem' => 'Documento externo de número ' . $novaTupla['DOCM_NR_DOCUMENTO'] . ' foi cadastrado com sucesso.', 'dados' => $novaTupla, 'dados_encaminhamento' => $retornoEncaminhamento);
            }
        } catch (Exception $e) {
            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db->rollBack();
            }
            $db->closeConnection();
            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao cadastrar um documento interno: ' . $e->getMessage()
                , 'status' => 'error'
                , 'dados' => $novaTupla
            );
        }
    }

    /**
     * Cadastra um documento pessoal no sisad. O documento será cadastrado na
     * caixa pessoal do usuáio
     * 
     * @param array $documento
     * @param array $partes
     * @param array $uploads testando usando o fileInfo da classe Zend_File_Transfer_Adapter_Http
     * @return array {'status':bolean, 'mensagem':string}
     */
    public function cadastrarPessoal($documento, $partes, $uploads, $dadosComplementares) {
        /**
         * Limpando a session dos anexos
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $sessionAnexos = new Zend_Session_Namespace('Sisad_Anexos_' . $userNs->matricula);
        $sessionAnexos->anexos = NULL;
        $dadosComplementares['AUTOCOMMIT'] = (isset($dadosComplementares['AUTOCOMMIT']) ? $dadosComplementares['AUTOCOMMIT'] : true);
        try {
            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
            }
            /*
             * LEIA - ME
             * 
             * - A função leva em consideração que o upload ja tenha sido feito
             * 
             * - A função também leva em consideração que o valor da variavel $uploads
             * foi oriundo do metodo fileInfo da classe Zend_File_Transfer_Adapter_Http
             * mas nada impede que seja utilizado outro metodo de upload desde a 
             * variavel $uploads tenha no minimo o index name e como arquivo principal
             * o indice arquivo_principal
             */

            //seta a tolerancia de tempo para 30 minutos
            set_time_limit(1800);

            $app_multiupload_upload = new App_Multiupload_Upload();
            $dual = new Application_Model_DbTable_Dual();

            $nrRedAnexoPrincipal = null;
            //se tiver um arquivo para ser o anexo principal
            if (isset($uploads['arquivo_principal']) && $uploads['arquivo_principal']['name'] != '') {
                $retornoAnexo = $app_multiupload_upload->incluirArquivoNoRed($uploads['arquivo_principal'], 'SISAD');
                //se o documento não foi cadastrado no RED com sucesso
                if (!$retornoAnexo['sucesso']) {
                    return $retornoAnexo;
                } else {
                    $nrRedAnexoPrincipal = $retornoAnexo['dados']['ID_DOCUMENTO'];
                }
            }

            $numeroSequencialDocumento = $this->retornaNumeroSequencial($this->_userNs->siglasecao, $this->_userNs->codlotacao, $documento['DOCM_ID_TIPO_DOC']);
            $dataHora = $dual->sysdatehoraDb();
            $novaTupla = array(
                'DOCM_NR_DOCUMENTO' => $this->retornaNumeroDocumento($this->_userNs->siglasecao, $this->_userNs->codlotacao, $this->_userNs->codlotacao, $documento['DOCM_ID_TIPO_DOC'], $numeroSequencialDocumento)
                , 'DOCM_NR_SEQUENCIAL_DOC' => $numeroSequencialDocumento
                , 'DOCM_DH_CADASTRO' => new Zend_Db_Expr("TO_DATE('$dataHora','dd/mm/yyyy HH24:MI:SS')")
                , 'DOCM_CD_MATRICULA_CADASTRO' => $this->_userNs->matricula
                , 'DOCM_SG_SECAO_GERADORA' => $this->_userNs->siglasecao
                , 'DOCM_CD_LOTACAO_GERADORA' => $this->_userNs->codlotacao
                , 'DOCM_SG_SECAO_REDATORA' => $this->_userNs->siglasecao
                , 'DOCM_CD_LOTACAO_REDATORA' => $this->_userNs->codlotacao
                , 'DOCM_ID_PESSOA_EXTERNO' => $documento['DOCM_ID_PESSOA_EXTERNO']
                , 'DOCM_DS_NOME_EMISSOR_EXTERNO' => $documento['DOCM_DS_NOME_EMISSOR_EXTERNO']
                , 'DOCM_ID_TIPO_DOC' => $documento['DOCM_ID_TIPO_DOC']
                , 'DOCM_NR_DCMTO_USUARIO' => $documento['DOCM_NR_DCMTO_USUARIO']
                , 'DOCM_ID_PCTT' => $documento['DOCM_ID_PCTT']
                , 'DOCM_DS_ASSUNTO_DOC' => new Zend_Db_Expr(" CAST( '" . $documento['DOCM_DS_ASSUNTO_DOC'] . "' AS VARCHAR(4000)) ")
                , 'DOCM_DS_PALAVRA_CHAVE' => $documento['DOCM_DS_PALAVRA_CHAVE']
                , 'DOCM_ID_TIPO_SITUACAO_DOC' => $documento['DOCM_ID_TIPO_SITUACAO_DOC']
                , 'DOCM_ID_CONFIDENCIALIDADE' => $documento['DOCM_ID_CONFIDENCIALIDADE']
                , 'DOCM_IC_DOCUMENTO_EXTERNO' => 'S'
                , 'DOCM_NR_DOCUMENTO_RED' => $nrRedAnexoPrincipal
            );
            //inseri os dados do documento externo na tabela DOCM
            $dbTableDocumento = new Application_Model_DbTable_Sisad_SadTbDocmDocumento();
            $idDocumento = $dbTableDocumento->createRow($novaTupla)->save();
            $novaTupla['DOCM_ID_DOCUMENTO'] = $idDocumento;



            //elimina o arquivo principal do array de uploads
            unset($uploads['arquivo_principal']);
            //se tiver algum arquivo para servir de anexo sem metadados
            //qualquer arquivo que tiver (fora o arquivo principal) será considerado 
            //anexo sem metadado
            if (is_null($nrRedAnexoPrincipal) && !empty($uploads)) {
                return array(
                    'sucesso' => false
                    , 'mensagem' => 'Não é possível inclui anexos ao documento se não for selecionado um arquivo principal.'
                    , 'status' => 'notice'
                    , 'dados' => array()
                );
            }
            //inclui os anexos sem metadados caso tenha ao documento criado
            if (!empty($uploads)) {
                $complemento = array('AUTOCOMMIT' => false);
                $retornoAnexo = $this->anexarDocumentosSemMetadados($novaTupla, $uploads, $complemento);
                if ($retornoAnexo['sucesso'] == false) {
                    return array(
                        'sucesso' => false
                        , 'status' => 'error'
                        , 'mensagem' => $retornoAnexo['mensagem']
                        , 'dados' => array('documento' => $novaTupla, 'uploads' => $uploads, 'configuracoes' => $dadosComplementares)
                    );
                }
            }
            //adiciona as partes ou vistas ao documento ou processo
            $rn_parteVistas = new Trf1_Sisad_Negocio_ParteVistas();

            if (!is_array($partes['partes_pessoa_trf'])) {
                $partes['partes_pessoa_trf'] = Zend_Json::decode($partes['partes_pessoa_trf']);
                $partes['partes_unidade'] = Zend_Json::decode($partes['partes_unidade']);
                $partes['partes_pess_ext'] = Zend_Json::decode($partes['partes_pess_ext']);
                $partes['partes_pess_jur'] = Zend_Json::decode($partes['partes_pess_jur']);
            }

            $retorno = $rn_parteVistas->addParteVistas($novaTupla, $partes, false);
            if ($retorno['validado'] == false) {
                return array('sucesso' => false, 'mensagem' => $retorno['mensagem'], 'status' => 'error', 'dados' => array());
            }

            //Encaminhar para a caixa pessoal
            $destino = array('MATRICULA' => $this->_userNs->matricula, 'SIGLA' => $this->_userNs->siglasecao, 'CODIGO' => $this->_userNs->codlotacao);
            //realiza o encaminhamento do documento para a caixa do usuario desejado
            $retornoEncaminhamento = $this->encaminhar(
                    $novaTupla
                    , $destino
                    , array()
                    , array(
                'TIPO' => 'PESSOA'
                , 'SYSDATE' => $dataHora
                , 'ENCAMINHAMENTO_POR_CADASTRO' => true
                , 'AUTOCOMMIT' => false
                , 'SEM_FASE' => (isset($dadosComplementares['ENCAMINHAMENTO']['SEM_FASE']) ? $dadosComplementares['ENCAMINHAMENTO']['SEM_FASE'] === true : false)
                    )
            );

            //se não tiver sucesso ao encaminhar
            if (!$retornoEncaminhamento['sucesso']) {
                if ($dadosComplementares['AUTOCOMMIT'] === true) {
                    $db->rollBack();
                }
                return array(
                    'sucesso' => false
                    , 'mensagem' => $retornoEncaminhamento['mensagem']
                    , 'status' => 'error'
                    , 'dados' => $retornoEncaminhamento['dados']
                );
            }

            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db->commit();
            }
            $db->closeConnection();
            return array('sucesso' => true, 'status' => 'success', 'mensagem' => 'Documento Pessoal de número ' . $novaTupla['DOCM_NR_DOCUMENTO'] . ' foi cadastrado com sucesso.', 'dados' => $novaTupla, 'dados_encaminhamento' => $retornoEncaminhamento);
        } catch (Exception $e) {
            if ($dadosComplementares['AUTOCOMMIT'] === true) {
                $db->rollBack();
            }
            $db->closeConnection();
            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao cadastrar um documento interno: ' . $e->getMessage()
                , 'status' => 'error'
                , 'dados' => $novaTupla
            );
        }
    }

    /**
     * Retorna o número sequencial baseado no ano, unidade redatora e tipo do documento.
     * @param string $siglaSecaoRedatora
     * @param int $codigoLotacaoRedatora
     * @param int $tipoDocumento
     * @return int sequencial do documento
     */
    private function retornaNumeroSequencial($siglaSecaoRedatora, $codigoLotacaoRedatora, $tipoDocumento) {
        $sql = "SELECT  NVL(MAX(DOCM_NR_SEQUENCIAL_DOC)+1,1) AS DOCM_NR_SEQUENCIAL_DOC
                FROM SAD_TB_DOCM_DOCUMENTO A
                WHERE A.DOCM_ID_TIPO_DOC = $tipoDocumento
                    AND A.DOCM_SG_SECAO_REDATORA = '$siglaSecaoRedatora'
                    AND A.DOCM_CD_LOTACAO_REDATORA = $codigoLotacaoRedatora
                    AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = TO_CHAR(SYSDATE, 'YYYY')";
        $tupla = $this->_db->fetchRow($sql);
        return (int) $tupla['DOCM_NR_SEQUENCIAL_DOC'];
    }

    /**
     * Retorna o número de identificação do documento.
     * @param type $siglaSecaoRedatora
     * @param type $codigoLotacaoRedatora
     * @param type $codigoLotacaoGeradora
     * @param type $tipoDocumento
     * @param type $numeroSequencial
     * @return string|\Zend_Db_Expr
     */
    private function retornaNumeroDocumento($siglaSecaoRedatora, $codigoLotacaoRedatora, $codigoLotacaoGeradora, $tipoDocumento, $numeroSequencial) {
        /*
         * Fórmula do Número do documento
         * ano(4)unidadeRedatora(4)unidadeGeradora(4)TipodoDocumento(3)Númerosequenciadocumento(5)
         * 
         */
        $rn_lotacao = new Trf1_Rh_Negocio_Lotacao();
        $codigoSecaoPai = $rn_lotacao->retornaCodigoSecaoDaUnidade($siglaSecaoRedatora, $codigoLotacaoRedatora);

        $ano = date('Y');
        $codigoSecaoPai = substr(sprintf('%04d', $codigoSecaoPai), 0, 4);
        $codigoLotacaoRedatora = substr(sprintf('%05d', $codigoLotacaoRedatora), 0, 5);
        $codigoLotacaoGeradora = substr(sprintf('%05d', $codigoLotacaoGeradora), 0, 5);
        $tipoDocumento = substr(sprintf('%04d', $tipoDocumento), 0, 4);
        $numeroSequencial = substr(sprintf('%06d', $numeroSequencial), 0, 6);

        $NumeroDCMTO = $ano . $codigoSecaoPai . $codigoLotacaoRedatora . $codigoLotacaoGeradora . $tipoDocumento . $numeroSequencial;

        if (strlen($NumeroDCMTO) == 28) {
            return $NumeroDCMTO;
        } else {
            return new Zend_Db_Expr("NULL");
        }
    }

    /**
     * Retorna as caixas que o usuário possui perfil de Resposável pela Caixa da Unidade
     * @return array
     */
    public function retornaComboCaixasPorResponsavel($matricula) {

        $sql = "
                SELECT  RHLOTA.LOTA_COD_LOTACAO||' - '||RHLOTA.LOTA_SIGLA_SECAO,
                        RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO,RHLOTA.LOTA_COD_LOTACAO)||' - '||RHLOTA.LOTA_SIGLA_LOTACAO||' - '||RHLOTA.LOTA_SIGLA_SECAO AS LOTA_DSC_LOTACAO
                  FROM  OCS_TB_PERF_PERFIL  PERF,
                        OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                        OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                        RH_CENTRAL_LOTACAO RHLOTA
                  WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                  AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                  AND   UNPE_SG_SECAO   =  RHLOTA.LOTA_SIGLA_SECAO
                  AND   UNPE_CD_LOTACAO =  RHLOTA.LOTA_COD_LOTACAO
                  AND   PUPE.PUPE_CD_MATRICULA = '$matricula'
                  AND   PERF_ID_PERFIL = 9 /*RESPONSÁVEL PELA CAIXA DA UNIDADE*/
                  ORDER BY LOTA_DSC_LOTACAO
			";

        return $this->_db->fetchPairs($sql);
    }

    public function retornaUltimaMovimentacao($documento) {
        $sql = 'SELECT 
                    MODO_ID_MOVIMENTACAO
                    ,MODO_ID_DOCUMENTO
                    ,MOVI_ID_MOVIMENTACAO
                    ,TO_CHAR(MOVI_DH_ENCAMINHAMENTO,\'DD/MM/YYYY HH24:MI:SS\') AS MOVI_DH_ENCAMINHAMENTO
                    ,MOVI_SG_SECAO_UNID_ORIGEM
                    ,MOVI_CD_SECAO_UNID_ORIGEM
                    ,MOVI_CD_MATR_ENCAMINHADOR
                    ,MOVI_ID_CAIXA_ENTRADA
                    ,MODE_ID_MOVIMENTACAO
                    ,MODE_SG_SECAO_UNID_DESTINO
                    ,MODE_CD_SECAO_UNID_DESTINO
                    ,MODE_IC_RESPONSAVEL
                    ,TO_CHAR(MODE_DH_RECEBIMENTO,\'DD/MM/YYYY HH24:MI:SS\') AS MODE_DH_RECEBIMENTO
                    ,MODE_CD_MATR_RECEBEDOR
                    ,MODE_ID_CAIXA_ENTRADA
                FROM 
                    SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                    INNER JOIN SAD_TB_MOVI_MOVIMENTACAO
                        ON MODO_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO
                        AND MODO_ID_DOCUMENTO = ' . $documento['DOCM_ID_DOCUMENTO'] . '
                        AND MODO_ID_MOVIMENTACAO = (
                            SELECT MAX(MODO_ID_MOVIMENTACAO) FROM SAD_TB_MODO_MOVI_DOCUMENTO  MODO_MOVI_2 WHERE MODO_MOVI_2.MODO_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO
                        )
                    INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO
                        ON MOVI_ID_MOVIMENTACAO = MODE_ID_MOVIMENTACAO';
        return $this->_db->fetchRow($sql);
    }

    /**
     * Autua um documento. Cria um processo administrativo e inclui um documento ao processo
     * @param array $documento
     * @param array $dadosAutuacao
     * @param array $configuracoes ARRAY('DURANTE_CADASTRO' => BOOLEAN
     */
    public function autuar($documento, $partes, $dadosAutuacao, $configuracoes) {

        $dbDocm = new Application_Model_DbTable_Sisad_SadTbDocmDocumento();
        $dbPrdi = new Application_Model_DbTable_Sisad_SadTbPrdiProcessoDigital();
        $dbTable_Mofa = new Application_Model_DbTable_Sisad_SadTbMofaMoviFase();
        $dbTable_Dcpr = new Application_Model_DbTable_Sisad_SadTbDcprDocumentoProcesso();
        $rn_parteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        try {

            if ($configuracoes['AUTOCOMMIT'] === true) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
            }
            $documento['DOCM_ID_TIPO_DOC'] = Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO;
            unset($documento['DOCM_NR_DCMTO_USUARIO']);

            if (!isset($configuracoes['DURANTE_CADASTRO']) || $configuracoes['DURANTE_CADASTRO'] === false) {

                //ainda não implementado
                return array(
                    'sucesso' => false
                    , 'mensagem' => 'Estilo de autuação não implementado. Contate a fábrica de software via sosti.'
                    , 'status' => 'error'
                    , 'dados' => array(
                        'configuracoes' => $configuracoes
                    )
                );
            } else {


                $configuracoes['ENCAMINHAMENTO']['SEM_FASE'] = true;
                //se for uma autuação durante o cadastro
                $dadosComplementares = array(
                    'PARTES' => array(
                        'DADOS' => $partes
                    )
                    , 'TIPO' => 'PROCESSO_ADMINISTRATIVO'
                    , 'ENCAMINHAMENTO' => $configuracoes['ENCAMINHAMENTO']
                    , 'AUTOCOMMIT' => false
                );

                //cria um documento que será o processo administrativo
                $retornoCadastroProcesso = $this->cadastrarInterno($documento, $partes, array(), $dadosComplementares);
                $dataPrdi = array(
                    'PRDI_CD_MATR_AUTUADOR' => $this->_userNs->matricula
                    , 'PRDI_DH_AUTUACAO' => $retornoCadastroProcesso['dados']['DOCM_DH_CADASTRO']
                    , 'PRDI_DS_TEXTO_AUTUACAO' => $dadosAutuacao['TEXTO']
                    , 'PRDI_SG_SECAO_AUTUADORA' => $this->_userNs->siglasecao
                    , 'PRDI_CD_UNID_AUTUADORA' => $this->_userNs->codlotacao
                    , 'PRDI_ID_AQVP' => $retornoCadastroProcesso['dados']['DOCM_ID_PCTT']
                    , 'PRDI_IC_SIGILOSO' => ($retornoCadastroProcesso['dados']['DOCM_ID_CONFIDENCIALIDADE'] == '4' ? 'S' : 'N' )
                    , 'PRDI_IC_TP_DISTRIBUICAO' => 'DA'
                        // notei que o valor não é setado durante a autuação comum, 'PRDI_ID_TIPO_PROCESSO'
                        // sistema de distribuição coloca o valor ,'PRDI_CD_MATR_DISTRIBUICAO'
                        // sistema de distribuição coloca o valor ,'PRDI_IC_TP_DISTRIBUICAO'
                        // sistema de distribuição coloca o valor ,'PRDI_DH_DISTRIBUICAO'
                        // sistema de distribuição coloca o valor ,'PRDI_CD_MATR_SERV_RELATOR'
                        // sistema de distribuição coloca o valor ,'PRDI_CD_JUIZ_RELATOR_PROCESSO'
                        // sistema de distribuição coloca o valor ,'PRDI_CD_ORGAO_JULGADOR'
                        // banco seta automaticamente o valor N,'PRDI_IC_CANCELADO'
                );
                $idPrdi = $dbPrdi->createRow($dataPrdi)->save();

                $dataMofa = array(
                    'MOFA_ID_MOVIMENTACAO' => $retornoCadastroProcesso['dados_encaminhamento']['dados']['modo']['MODO_ID_MOVIMENTACAO']
                    , 'MOFA_ID_FASE' => Trf1_Sisad_Definicoes::FASE_AUTUACAO_PROCESSO
                    , 'MOFA_DH_FASE' => (is_a($dataPrdi['PRDI_DH_AUTUACAO'], 'Zend_Db_Expr') ? $dataPrdi['PRDI_DH_AUTUACAO'] : new Zend_Db_Expr("TO_DATE('" . $dataPrdi['PRDI_DH_AUTUACAO'] . "','DD/MM/YYYY HH24:MI:SS')"))
                    , 'MOFA_CD_MATRICULA' => $this->_userNs->matricula
                    , 'MOFA_DS_COMPLEMENTO' => new Zend_Db_Expr("'" . $dataPrdi['PRDI_DS_TEXTO_AUTUACAO'] . "'")
                );
                $idMofa = $dbTable_Mofa->createRow($dataMofa)->save();

                //adicionando o documento do processo ao processo administrativo
                $dataDcprProcesso = array(
                    'DCPR_ID_PROCESSO_DIGITAL' => $idPrdi
                    , 'DCPR_ID_DOCUMENTO' => $retornoCadastroProcesso['dados']['DOCM_ID_DOCUMENTO']
                    , 'DCPR_ID_TP_VINCULACAO' => Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR
                    , 'DCPR_DH_VINCULACAO_DOC' => (is_a($dataPrdi['PRDI_DH_AUTUACAO'], 'Zend_Db_Expr') ? $dataPrdi['PRDI_DH_AUTUACAO'] : new Zend_Db_Expr("TO_DATE('" . $dataPrdi['PRDI_DH_AUTUACAO'] . "','DD/MM/YYYY HH24:MI:SS')"))
                    , 'DCPR_IC_ATIVO' => 'S'
                    , 'DCPR_IC_ORIGINAL' => 'S'
                );
                $idDcprProcesso = $dbTable_Dcpr->createRow($dataDcprProcesso)->save();

                //adicionando o documento que foi autuado ao processo administrativo
                $dataDcprDocumento = array(
                    'DCPR_ID_PROCESSO_DIGITAL' => $idPrdi
                    , 'DCPR_ID_DOCUMENTO' => $documento['DOCM_ID_DOCUMENTO']
                    , 'DCPR_ID_TP_VINCULACAO' => Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR
                    , 'DCPR_DH_VINCULACAO_DOC' => (is_a($dataPrdi['PRDI_DH_AUTUACAO'], 'Zend_Db_Expr') ? $dataPrdi['PRDI_DH_AUTUACAO'] : new Zend_Db_Expr("TO_DATE('" . $dataPrdi['PRDI_DH_AUTUACAO'] . "','DD/MM/YYYY HH24:MI:SS')"))
                    , 'DCPR_IC_ATIVO' => 'S'
                    , 'DCPR_IC_ORIGINAL' => 'S'
                );
                $idDcprDocumento = $dbTable_Dcpr->createRow($dataDcprDocumento)->save();
                //altera os dados do documento que foi autuado
                $dataDocumentos_vicular["DOCM_IC_PROCESSO_AUTUADO"] = "S";
                $dataDocumentos_vicular["DOCM_IC_MOVI_INDIVIDUAL"] = "N";
                $rowDocumento = $dbDocm->find($documento['DOCM_ID_DOCUMENTO'])->current();
                $idqq = $rowDocumento->setFromArray(array('DOCM_IC_PROCESSO_AUTUADO' => 'S', 'DOCM_IC_MOVI_INDIVIDUAL' => 'N'))->save();

                $retornoCadastroProcesso['dados']['PRDI_ID_PROCESSO_DIGITAL'] = $idPrdi;
                //adiciona as partes ou vistas ao processo
                $retorno = $rn_parteVistas->addParteVistas($retornoCadastroProcesso['dados'], $partes, false);
                if ($dadosComplementares['AUTOCOMMIT'] === true) {
                    $db->commit();
                }
                return array(
                    'sucesso' => true
                    , 'mensagem' => 'Sucesso ao autuar'
                    , 'status' => 'success'
                    , 'dados' => array(
                        'configuracoes' => $configuracoes
                        , 'retorno_cadastro_processo' => $retornoCadastroProcesso
                        , 'data_prdi' => $dataPrdi
                        , 'data_mofa' => $dataMofa
                    )
                );
            }
        } catch (Exception $e) {
            if ($configuracoes['AUTOCOMMIT'] === true) {
                $db->rollBack();
            }
            return array(
                'sucesso' => false
                , 'mensagem' => 'Erro ao autuar: ' . $e->getMessage()
                , 'status' => 'error'
                , 'dados' => array(
                    'configuracoes' => $configuracoes
                    , 'retorno_cadastro_processo' => $retornoCadastroProcesso
                    , 'data_prdi' => $dataPrdi
                    , 'data_mofa' => $dataMofa
                )
            );
        }
    }

    /**
     * Retorna os documentos anexados em um processo administrativo. Poderá ser 
     * passado mais de um id de processo administrativo, desde que seja em forma
     * de string e separados por virgula.
     * O indice da tupla será igual ao id do documento.
     * @param array $idProcessos
     */
    public function getDocumentosAnexados($idProcessos) {
        $sql = "
            SELECT 
                DOCM_ID_DOCUMENTO
                ,DOCM_NR_DOCUMENTO_RED
                ,DECODE(
                    LENGTH(DOCM_NR_DOCUMENTO)
                    ,17
                    ,SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(DOCM_NR_DOCUMENTO)
                    ,SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(DOCM_NR_DOCUMENTO)
                ) MASC_NR_DOCUMENTO
                ,DTPD_NO_TIPO
                ,RH_SIGLAS_FAMILIA_CENTR_LOTA(
                    DOCM_SG_SECAO_GERADORA
                    ,DOCM_CD_LOTACAO_GERADORA
                ) FAMILIA_LOTACAO
                ,PMAT_CD_MATRICULA PMAT_CD_MATRICULA_AUTOR
                ,PNAT_NO_PESSOA PNAT_NO_PESSOA_AUTOR
                ,DCPR_ID_PROCESSO_DIGITAL
                ,TO_CHAR(
                    DCPR_DH_VINCULACAO_DOC
                    ,'DD/MM/YYYY HH24:MI:SS'
                ) DCPR_DH_VINCULACAO_DOC,
                DOCM_ID_CONFIDENCIALIDADE
            FROM
                SAD_TB_DCPR_DOCUMENTO_PROCESSO
                INNER JOIN SAD_TB_DOCM_DOCUMENTO
                    ON DOCM_ID_DOCUMENTO = DCPR_ID_DOCUMENTO
                    AND DOCM_ID_TIPO_DOC != "
                . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO
                . "
                    AND DCPR_ID_TP_VINCULACAO = "
                . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR
                . "
                    AND DCPR_ID_PROCESSO_DIGITAL IN (
                        " . $idProcessos . "
                    )
                    AND DCPR_IC_ATIVO = 'S'
                INNER JOIN OCS_TB_DTPD_TIPO_DOC
                    ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                INNER JOIN OCS_TB_PMAT_MATRICULA
                    ON DOCM_CD_MATRICULA_CADASTRO = PMAT_CD_MATRICULA
                INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                    ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
        ";
        //o indice da tupla no array será o id do documento
        return $this->_db->fetchAssoc($sql);
    }

    /**
     * Retorna a hierarquia de anexação e apensação de um processo. 
     * Toma o processo do parametro como raiz da hierarquia.
     * O indice da tupla no array será o id (PRDI_ID...) do processo 
     * administrativo.
     * @param array $processo
     */
    public function getArvoreDeAnexosEApensosDoProcesso($processo) {
        $sql = "
            SELECT 
                PRDI_ID_PROCESSO_DIGITAL
                ,DECODE(
                    LENGTH(DOCM_NR_DOCUMENTO)
                    ,17
                    ,SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(DOCM_NR_DOCUMENTO)
                    ,SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(DOCM_NR_DOCUMENTO)
                ) MASC_NR_DOCUMENTO
                ,DOCM_ID_CONFIDENCIALIDADE
                ,DECODE(
                  LENGTH(DOCM_NR_DOCUMENTO_PAI)
                  ,17
                  ,SAD_PKG_NR_DOCUMENTO.MASCARA_PROCESSO(DOCM_NR_DOCUMENTO_PAI)
                  ,SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(DOCM_NR_DOCUMENTO_PAI)
                ) MASC_NR_DOCUMENTO_PAI
                ,PRDI_ID_PROCESSO_DIGITAL_PAI
                ,TVPD_DS_TP_VINCULACAO
                ,VIPD_ID_TP_VINCULACAO
                ,VIPD_DH_VINCULACAO
                ,DOCM_ID_DOCUMENTO
                , DOCM_ID_TIPO_DOC
                , DOCM_ID_TIPO_DOC DTPD_ID_TIPO_DOC
            FROM (
                SELECT
                    DOCM_FILHO.DOCM_ID_DOCUMENTO
                    ,DOCM_FILHO.DOCM_ID_TIPO_DOC 
                    ,DOCM_FILHO.DOCM_NR_DOCUMENTO
                    ,DOCM_FILHO.DOCM_ID_CONFIDENCIALIDADE
                    ,DOCM_PAI.DOCM_NR_DOCUMENTO DOCM_NR_DOCUMENTO_PAI
                    ,TVPD_FILHO.TVPD_DS_TP_VINCULACAO
                    ,VIPD_FILHO.VIPD_DH_VINCULACAO
                    ,VIPD_FILHO.VIPD_ID_TP_VINCULACAO
                    ,VIPD_FILHO.VIPD_ID_PROCESSO_DIGITAL_VINDO
                    ,VIPD_FILHO.VIPD_ID_PROCESSO_DIGITAL_PRINC
                    ,PRDI_FILHO.PRDI_ID_PROCESSO_DIGITAL
                    ,PRDI_PAI.PRDI_ID_PROCESSO_DIGITAL
                        PRDI_ID_PROCESSO_DIGITAL_PAI
                FROM
                    SAD_TB_DOCM_DOCUMENTO DOCM_FILHO
                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR_FILHO
                        ON DOCM_FILHO.DOCM_ID_DOCUMENTO 
                            = DCPR_FILHO.DCPR_ID_DOCUMENTO
                        AND DOCM_FILHO.DOCM_ID_TIPO_DOC 
                            = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                        /* COLOCAR RESTRITORES CASO SEJA NECESSÁRIO */
                    INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI_FILHO
                        ON DCPR_FILHO.DCPR_ID_PROCESSO_DIGITAL 
                            = PRDI_FILHO.PRDI_ID_PROCESSO_DIGITAL
                    INNER JOIN SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD_FILHO
                        ON PRDI_FILHO.PRDI_ID_PROCESSO_DIGITAL 
                            = VIPD_FILHO.VIPD_ID_PROCESSO_DIGITAL_VINDO
                        AND VIPD_FILHO.VIPD_IC_ATIVO = 'S'
                        AND VIPD_FILHO.VIPD_ID_TP_VINCULACAO IN (
                           " . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR . "
                           ," . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "
                        ) 
                    /* PEGANDO OS DADOS COMPLEMENTARES DA JUNTADA */
                    INNER JOIN SAD_TB_TVPD_TIPO_VINC_PROCESSO TVPD_FILHO
                        ON VIPD_FILHO.VIPD_ID_TP_VINCULACAO 
                            = TVPD_FILHO.TVPD_ID_TP_VINCULACAO
                    /* PEGANDO OS DADOS DO PROCESSO PAI */
                    INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI_PAI
                        ON VIPD_FILHO.VIPD_ID_PROCESSO_DIGITAL_PRINC
                            = PRDI_PAI.PRDI_ID_PROCESSO_DIGITAL
                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR_PAI
                        ON PRDI_PAI.PRDI_ID_PROCESSO_DIGITAL
                            = DCPR_PAI.DCPR_ID_PROCESSO_DIGITAL
                    INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM_PAI
                        ON DCPR_PAI.DCPR_ID_DOCUMENTO
                            = DOCM_PAI.DOCM_ID_DOCUMENTO
                        AND DOCM_PAI.DOCM_ID_TIPO_DOC 
                            = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
            )
            CONNECT BY PRIOR VIPD_ID_PROCESSO_DIGITAL_VINDO 
                = VIPD_ID_PROCESSO_DIGITAL_PRINC
            START WITH VIPD_ID_PROCESSO_DIGITAL_PRINC 
                = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
            ";
        //Indice da tupla no array será o id(PRDI...) do processo administrativo
        return $this->_db->fetchAssoc($sql);
    }

    /**
     * Retorna a hierarquia de juntada de um processo. Toma o processo
     * do parametro como raiz da hierarquia.
     * O indice da tupla no array será o id (PRDI_ID...) do processo 
     * administrativo.
     * O primeiro processo da listagem é o processo passado por parametro
     * @param array $processo
     * @param boolean $paraAssinatura
     * @param boolean $verificaVista
     * @return array lista de processos que 
     * encadeiam-se pelo index PRDI_ID_PROCESSO_DIGITAL_PAI.
     * Através do index VIPD_ID_TP_VINCULACAO é possível saber qual a modalidade
     * de juntada que o processo está envolvido.
     * Através do index DOCUMENTOS_ANEXADOS é possível saber quais os documentos
     * estão incluídos no processo como anexos.
     */
    public function getArvoreDeJuntadaProcesso($processo, $paraAssinatura = false, $verificaVista = false) {
        $rn_processo = new Trf1_Sisad_Negocio_Processo();
        $rn_JuntadaProcessoProcesso = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc ();

        if (!isset($processo['PRDI_ID_PROCESSO_DIGITAL'])) {
            $aux = $rn_processo
                    ->getProcessoPorIdDocumento($processo['DOCM_ID_DOCUMENTO']);
            $processo['PRDI_ID_PROCESSO_DIGITAL'] = $aux['PRDI_ID_PROCESSO_DIGITAL'];
        }
        //flag informa se o processo está apensado a outro como filho
        $flagApenso = false;
        $processoFilho = array();
        //dados do processo apenso pai. Caso não tenha o valor é null
        $processoApensoPai = $rn_JuntadaProcessoProcesso->isApensado($processo);

        //se o processo estiver apensado a algum processo
        if (!is_null($processoApensoPai)) {
            $flagApenso = true;
            $processoFilho = $processo;
            //id do processo para realizar a busca dos metadados
            $idProcesso = $processoApensoPai['VIPD_ID_PROCESSO_DIGITAL_PRINC'];
        } else {
            //id do processo para realizar a busca dos metadados
            $idProcesso = $processo['PRDI_ID_PROCESSO_DIGITAL'];
        }
        //busca dos metadados do processo principal
        $processo = $rn_processo->getDocumentoPorIdProcesso(array(
            'PRDI_ID_PROCESSO_DIGITAL' => $idProcesso
        ));
        //inclui o id do processo principal
        $processo['PRDI_ID_PROCESSO_DIGITAL'] = $idProcesso;

        $listaProcessos = $this->getArvoreDeAnexosEApensosDoProcesso($processo);
        $arrayIdProcessos = array_keys($listaProcessos);
        //inclui o id do processo administrativo principal na lista de ids
        $arrayIdProcessos[] = $processo['PRDI_ID_PROCESSO_DIGITAL'];
        $idProcessos = implode(',', $arrayIdProcessos);
        $documentosAnexados = $this->getDocumentosAnexados($idProcessos);
        //adiciona o processo principal a lista de processos formando a arvore
        $listaProcessos = array(
            $processo['PRDI_ID_PROCESSO_DIGITAL'] => $processo
                ) + $listaProcessos;
        //inclui os documentos dentro do array dos processos
        foreach ($documentosAnexados as $documento) {
            if ($verificaVista) {
                $verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($documento);
                if (!$verifica) {
                    continue;
                }
                $verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($listaProcessos[$documento['DCPR_ID_PROCESSO_DIGITAL']]);
                if (!$verifica) {
                    unset($listaProcessos[$documento['DCPR_ID_PROCESSO_DIGITAL']]);
                }
            }
            //se for para incluir dados de assinatura digital
            if ($paraAssinatura) {
                $documento['aceita_assinatura_digital'] = $this->aceitaAssinaturaDigital($documento['DOCM_ID_DOCUMENTO']);
            }
            if (isset($listaProcessos[$documento['DCPR_ID_PROCESSO_DIGITAL']])) {
                $processo_pai = $listaProcessos[$documento['DCPR_ID_PROCESSO_DIGITAL']];
                unset($processo_pai["DOCUMENTOS_ANEXADOS"]);
                $documento["PROCESSO_PAI"] = $processo_pai;
                $listaProcessos[$documento['DCPR_ID_PROCESSO_DIGITAL']]
                        ['DOCUMENTOS_ANEXADOS'][$documento['DOCM_ID_DOCUMENTO']] = $documento;
            }
        }

        //se o processo passado por parametro nessa função for um apenso filho
        if ($flagApenso) {
            //adiciona o processo passado por parametro nessa função no inicio
            //da listagem
            $aux = $listaProcessos[$processoFilho['PRDI_ID_PROCESSO_DIGITAL']];
            unset($listaProcessos[$processoFilho['PRDI_ID_PROCESSO_DIGITAL']]);
            $listaProcessos = array(
                $aux['PRDI_ID_PROCESSO_DIGITAL'] => $aux
                    ) + $listaProcessos;
        }
        return $listaProcessos;
    }

    /**
     * Retorna o tipo de assinatura que o documento deverá receber
     * @return boolean
     */
    public function aceitaAssinaturaDigital($idDocumento) {
        $resultado = $this->_db->fetchRow('SELECT COUNT(*) QTD FROM SAD_TB_ASDC_ASSINATURA_DOC WHERE ASDC_ID_DOCUMENTO = ?', array($idDocumento));
        return $resultado['QTD'] == 0;
    }

    /**
     * Retorna os dados necessários para assinar um documento
     * @return string (tipo de assinatura)
     */
    public function getDadosDocumentoAssinatura($idDocumento) {
        $documento = $this->getDocumento($idDocumento);
        $documento['aceita_assinatura_digital'] = $this->aceitaAssinaturaDigital($idDocumento);
        return $documento;
    }

    public function getAssinaturas($idDocumento) {
        $resultados = $this->_db->fetchAll('SELECT
                                                ASDC_ID_ASSINATURA_DOCUMENTO,
                                                ASDC_ID_DOCUMENTO,
                                                ASDC_CD_MATRICULA,
                                                ASDC_NR_DOCUMENTO_ANT_RED,
                                                ASDC_DH_ASSINATURA,
                                                TO_CHAR(ASDC_DH_ASSINATURA,\'dd/mm/yyyy HH24:MI:SS\') ASDC_DH_ASSINATURA,
                                                PMAT_CD_MATRICULA,
                                                PNAT_NO_PESSOA
                                            FROM SAD_TB_ASDC_ASSINATURA_DOC
                                            INNER JOIN OCS_TB_PMAT_MATRICULA
                                                ON ASDC_CD_MATRICULA = PMAT_CD_MATRICULA
                                                AND ASDC_ID_DOCUMENTO = ?
                                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                                                ON PMAT_ID_PESSOA = PNAT_ID_PESSOA', array($idDocumento));
        if (count($resultados) > 0) {
            return $resultados[count($resultados) - 1];
        } else {
            return array();
        }
    }

    public function validaAssinaturaPorCertificado($documento, $pessoa, $assinatura, $hexadecimalArquivo) {
        if ($assinatura['CERTIFICADO']['CPF'] == $pessoa['PNAT_NR_CPF']) {
            //verifica se o hexadecimal assinado corresponde ao arquivo armazenado na temp do servidor
            if ($assinatura['ARQUIVO']['HEXADECIMAL_ARQUIVO'] === $hexadecimalArquivo) {
                $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                $verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($documento);
                if ($verifica) {
                    //caso tenha um processo pai
                    if (isset($documento['PROCESSO_PAI'])) {
                        //valida a vista ao processo administrativo
                        $verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($documento['PROCESSO_PAI']);
                        return $verifica;
                    } else {
                        return true;
                    }
                } else {
                    return array('SUCESSO' => false, 'MENSAGEM' => 'O documento não é visível ao usuário logado');
                }
            } else {
                return array('SUCESSO' => false, 'MENSAGEM' => 'O arquivo assinado não pertence ao documento escolhido');
            }
        } else {
            return array('SUCESSO' => false, 'MENSAGEM' => 'O cpf do usuário logado não bate com o cpf do certificado digital');
        }
    }

    public function assinaPorSenha() {
        
    }

    /**
     * Grava a assinatura por certificado digital na base de dados
     * 
     * @param type $documento array()
     * @param type $assinatura array('ARQUIVO' => array('HEXADECIMAL_ARQUIVO', 'NOME_ARQUIVO'),'ASSINATURA')
     */
    public function assinarPorCertificado($documento, $assinatura) {

        $dual = new Application_Model_DbTable_Dual();
        $rnPessoa = new Trf1_Rh_Negocio_Pessoa();
        $rnFase = new Trf1_Sisad_Negocio_Fase();
        $upload = new App_Multiupload_Upload();
        $tableDocm = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $tableAsdc = new Sisad_Model_DbTable_SadTbAsdcAssinaturaDoc();

        $pessoa = $rnPessoa->retornaPessoaFisica(array('PMAT_CD_MATRICULA' => $this->_userNs->matricula));

        //busca o arquivo armazenado na temp
        $caminho = substr(APPLICATION_PATH, 0, strpos(APPLICATION_PATH, 'application')) . 'temp' . DIRECTORY_SEPARATOR;
        $caminhoArquivo = $caminho . $assinatura['ARQUIVO']['NOME_ARQUIVO'];
        $hexadecimalArquivo = App_Utilidades_Arquivo::getHexadecimal($caminhoArquivo);
        $validacao = $this->validaAssinaturaPorCertificado($documento, $pessoa, $assinatura, $hexadecimalArquivo);
        if ($validacao === true) {
            try {
                $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                $adapter->beginTransaction();
                $dhAssinatura = $dual->sysdate();

                //se tiver um processo administrativo pai
                if (isset($documento['PROCESSO_PAI'])) {
                    //lança uma fase ao processo administrativo
                    $processo = $this->getDocumento($documento['PROCESSO_PAI']['DOCM_ID_DOCUMENTO']);
                    $rnFase->lancaFase(array(
                        "MOFA_ID_MOVIMENTACAO" => $processo['MOFA_ID_MOVIMENTACAO']
                        , "MOFA_DH_FASE" => $dhAssinatura
                        , "MOFA_ID_FASE" => Trf1_Sisad_Definicoes::FASE_ASSINATURA_POR_CERTIFICADO_DIGIRAL
                        , "MOFA_CD_MATRICULA" => $this->_userNs->matricula
                        , "MOFA_DS_COMPLEMENTO" => 'Assinatura digital realizada no documento ' . $documento["MASC_NR_DOCUMENTO"]
                    ));
                }
                //lança a fase ao documento
                $documento = $this->getDocumento($documento['DOCM_ID_DOCUMENTO']);
                $rnFase->lancaFase(array(
                    "MOFA_ID_MOVIMENTACAO" => $documento['MOFA_ID_MOVIMENTACAO']
                    , "MOFA_DH_FASE" => $dhAssinatura
                    , "MOFA_ID_FASE" => Trf1_Sisad_Definicoes::FASE_ASSINATURA_POR_CERTIFICADO_DIGIRAL
                    , "MOFA_CD_MATRICULA" => $this->_userNs->matricula
                    , "MOFA_DS_COMPLEMENTO" => 'Assinatura digital realizada'
                ));



                /// APAGAR DEPOIS
                //  $assinatura['ARQUIVO']['NOME_ARQUIVO'] = 'aaa1.pdf';
                //grava o P7s da assinatura na temp do servidor
                $nomeP7s = $assinatura['ARQUIVO']['NOME_ARQUIVO'] . '.P7s';
                App_Utilidades_Arquivo::gravaHexadecimalNaTemp($assinatura['ASSINATURA'], $nomeP7s);

                //grava o documento assinado mais a assinatura no RED
                $mensagem = $upload->incluirArquivoNoRed(array('name' => $assinatura['ARQUIVO']['NOME_ARQUIVO']), '', $caminho, false, array('name' => $nomeP7s));
                unlink(realpath($caminhoArquivo));
                unlink(realpath($caminhoArquivo . '.P7s'));

                if ($mensagem['sucesso'] && $mensagem['incluido_na_base']) {
                    $numeroAssinaturaRed = $mensagem['dados']['ID_DOCUMENTO'];
                    //grava no banco de dados o histórico da assinatura
                    if ($this->aceitaAssinaturaDigital($documento['DOCM_ID_DOCUMENTO'])) {
                        $dados = array(
                            'ASDC_ID_DOCUMENTO' => $documento['DOCM_ID_DOCUMENTO']
                            , 'ASDC_CD_MATRICULA' => $this->_userNs->matricula
                            , 'ASDC_NR_DOCUMENTO_ANT_RED' => $documento['DOCM_NR_DOCUMENTO_RED']
                            , 'ASDC_DH_ASSINATURA' => $dhAssinatura
                        );
                        $rowAsdc = $tableAsdc->createRow($dados);
                        $rowAsdc->save();
                        //altera o arquivo do documento
                        $tableDocm->find($documento['DOCM_ID_DOCUMENTO'])
                                ->current()->setFromArray(array('DOCM_NR_DOCUMENTO_RED' => $numeroAssinaturaRed))->save();
                    } else {
                        return array('SUCESSO' => false, 'MENSAGEM' => 'O documento ' . $documento['MASC_NR_DOCUMENTO'] . ' já foi assinado por outro usuário');
                    }
                } else {
                    if ($mensagem['sucesso']) {
                        return array('SUCESSO' => false, 'MENSAGEM' => 'Não foi possível incluir a assinatura do documento ' . $documento['MASC_NR_DOCUMENTO'] . '. A identificação da assinatura não foi incluída no arquivo.');
                    } else {
                        return array('SUCESSO' => false, 'MENSAGEM' => 'Não foi possível incluir a assinatura do documento ' . $documento['MASC_NR_DOCUMENTO'] . ' no RED: ' . $mensagem['mensagem']);
                    }
                }
                $adapter->commit();
                return array("MENSAGEM" => "Assinatura do documento " . $documento["MASC_NR_DOCUMENTO"] . " foi realizada com sucesso", "SUCESSO" => true);
            } catch (Exception $e) {
                unlink(realpath($caminhoArquivo));
                unlink(realpath($caminhoArquivo . '.P7s'));
                $adapter->rollBack();
                return array('SUCESSO' => false, 'MENSAGEM' => 'Não foi possível assinar o documento ' . $documento['MASC_NR_DOCUMENTO'] . ': ' . $e->getMessage());
            }
        }
        unlink(realpath($caminhoArquivo));
        unlink(realpath($caminhoArquivo . '.P7s'));
        return $validacao;
    }

}
