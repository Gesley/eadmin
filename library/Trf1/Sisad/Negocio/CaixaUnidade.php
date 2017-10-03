<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_CaixaUnidade
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
class Trf1_Sisad_Negocio_CaixaUnidade {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    protected $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    public function getCaixasPorResponsavel($matricula) {
        $sql = '
SELECT RHLOTA.LOTA_COD_LOTACAO, 
    RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO,RHLOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO, 
    RHLOTA.LOTA_SIGLA_LOTACAO,
    RHLOTA.LOTA_SIGLA_SECAO, 
    PERF_ID_PERFIL, 
    PERF_DS_PERFIL
FROM  OCS_TB_PERF_PERFIL  PERF,
    OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
    OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
    RH_CENTRAL_LOTACAO RHLOTA
WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
    AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
    AND   UNPE_SG_SECAO   =  RHLOTA.LOTA_SIGLA_SECAO
    AND   UNPE_CD_LOTACAO =  RHLOTA.LOTA_COD_LOTACAO
    AND   PUPE.PUPE_CD_MATRICULA = ?
    AND   PERF_ID_PERFIL = ?
    ORDER BY LOTA_DSC_LOTACAO
';
        return $this->_db->query($sql, array($matricula, Trf1_Sisad_Definicoes::PERFIL_RESPONSAVEL_UNIDADE))->fetchAll();
    }

    /**
     * Verifica se usuário tem permissão na Caixa da Corregedoria
     * 
     * @param	$matricula
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     * @deprecated since version 10853
     */
    public function verificaPermissaoCorregedoria($matricula) {
        $rn_Permissao = new Trf1_Guardiao_Negocio_Permissao();
        return $rn_Permissao->hasPermissaoCorregedoria($matricula);
    }

    /**
     * Altera um documento para lido naquela movimentação
     * 
     * @param	$id_movimentacao
     * @param	$matricula
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function lerDocumento($id_movimentacao, $matricula) {

        $sadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();

        $row = $sadTbModeMoviDestinatario->find($id_movimentacao)->current();
        $array = $row->toArray();
        
        if (is_null($array['MODE_CD_MATR_RECEBEDOR'])) {
            return $row->setFromArray(array(
                        'MODE_ID_MOVIMENTACAO' => $id_movimentacao
                        , 'MODE_DH_RECEBIMENTO' => new Zend_Db_Expr('SYSDATE')
                        , 'MODE_CD_MATR_RECEBEDOR' => $matricula
                    ))->save();
        } else {
            return false;
        }
    }
    
    /**
     * Verifica se Documento está na caixa da unidade
     * 
     * @param
     * @author Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function verificaDocumentoCaixaUnidade($docm_id_documento, $mode_sg_secao_unid_destino, $mode_cd_secao_unid_destino) {
        $bd_CaixaUnidade = new Trf1_Sisad_Bd_CaixaUnidade();
        return $bd_CaixaUnidade->verificaDocumentoCaixaUnidade($docm_id_documento, $mode_sg_secao_unid_destino, $mode_cd_secao_unid_destino);
    }
    
    /**
     * Busca todos os documentos da caixa que não seja autuado, arquivado e seja ativo
     * 
     * @param	array	$configuracao	Obrigatório [SG_SECAO,CD_SECAO,ORDER]
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getDocumentos(array $configuracao) {
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
        AQAT.AQAT_DS_ATIVIDADE,
        MOVI.MOVI_SG_SECAO_UNID_ORIGEM,
        MOVI.MOVI_CD_SECAO_UNID_ORIGEM
        RH_SIGLAS_FAMILIA_CENTR_LOTA(MOVI.MOVI_SG_SECAO_UNID_ORIGEM,MOVI.MOVI_CD_SECAO_UNID_ORIGEM) FAMILIA_EMISSORA,
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
     * Busca todos os processos da caixa
     * 
     * @param	array	$configuracao	Obrigatório [SG_SECAO,CD_SECAO,ORDER]
     * @return	array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getProcessos(array $configuracao) {
        $completa = '';
        if (isset($configuracao['excluidos'])) {
            $completa = 'AND DOCM.DOCM_ID_DOCUMENTO NOT IN(';
            foreach ($configuracao['excluidos'] as $excluido) {
                $completa .= $excluido['DOCM_ID_DOCUMENTO'] . ',';
            }
            $completa = substr($completa, 0, -1).')';
        }
        $sql = "
SELECT  DTPD.DTPD_ID_TIPO_DOC,
        DTPD.DTPD_NO_TIPO,
        PRDI_ID_PROCESSO_DIGITAL,
        PRDI_DS_TEXTO_AUTUACAO,
        DOCM.DOCM_ID_DOCUMENTO,
        DOCM.DOCM_NR_DOCUMENTO,
        DOCM.DOCM_ID_CONFIDENCIALIDADE,
        DOCM.DOCM_IC_MOVI_INDIVIDUAL,
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
        MOVI.MOVI_SG_SECAO_UNID_ORIGEM,
        MOVI.MOVI_CD_SECAO_UNID_ORIGEM,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(MOVI.MOVI_SG_SECAO_UNID_ORIGEM,MOVI.MOVI_CD_SECAO_UNID_ORIGEM) FAMILIA_EMISSORA,
        (SELECT LOTA_SIGLA_LOTACAO
            FROM RH_CENTRAL_LOTACAO
            WHERE LOTA_SIGLA_SECAO = MOVI.MOVI_SG_SECAO_UNID_ORIGEM
            AND   LOTA_COD_LOTACAO =   MOVI.MOVI_CD_SECAO_UNID_ORIGEM) LOTA_SIGLA_LOTACAO_ORIGEM,
        AQAT.AQAT_DS_ATIVIDADE
FROM    SAD_TB_DOCM_DOCUMENTO DOCM
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
            ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
        
        INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO
                ON DOCM_ID_DOCUMENTO        = DCPR_ID_DOCUMENTO
        INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL
                ON DCPR_ID_PROCESSO_DIGITAL = PRDI_ID_PROCESSO_DIGITAL
        
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
    AND DOCM_IC_MOVI_INDIVIDUAL = 'S'
    AND MODE_MOVI.MODE_SG_SECAO_UNID_DESTINO = ?
    AND MODE_MOVI.MODE_CD_SECAO_UNID_DESTINO = ?
    AND DTPD.DTPD_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
    $completa
ORDER BY ?";
        
        $stmt = $this->_db->query($sql, array($configuracao['SG_SECAO']
            , $configuracao['CD_SECAO']
            , $configuracao['ORDER']));

        return $stmt->fetchAll();
    }

}