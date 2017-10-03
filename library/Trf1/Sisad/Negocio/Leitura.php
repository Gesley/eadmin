<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Leitura
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Leitura de documentos e processos
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
class Trf1_Sisad_Negocio_Leitura {

    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    private $_db;

    /**
     *
     * @var Zend_Session_Namespace 
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
     * Retorna os processos e documentos anexados ao processo
     * 
     * @param array $processo
     * @return array
     */
    public function getAnexadosAoProcesso($processo) {
        $sql = "
        SELECT 
        SUB_QUERY.*
        ,(
            SELECT COUNT(*)
            FROM SAD_TB_ANEX_ANEXO 
            INNER JOIN SAD_TB_MOFA_MOVI_FASE
                ON MOFA_ID_MOVIMENTACAO = ANEX_ID_MOVIMENTACAO
                AND MOFA_DH_FASE = ANEX_DH_FASE

            INNER JOIN OCS_TB_PMAT_MATRICULA
                ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA

            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
            WHERE ANEX_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
        ) AS QTD_ANEXOS_SEM_METADADOS 

        FROM (
        
        --DOCUMENTOS ANEXADOS
        
            SELECT
                DOCM.DOCM_ID_DOCUMENTO
                , DOCM.DOCM_NR_DOCUMENTO
                , DOCM.DOCM_NR_DOCUMENTO_RED
                , DECODE(
                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                ) MASC_NR_DOCUMENTO
                , DTPD.DTPD_NO_TIPO
                , DTPD.DTPD_ID_TIPO_DOC
                , AQVP_CD_PCTT
                , AQAT_DS_ATIVIDADE
                , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') DH_JUNTADA
                , PMAT_CD_MATRICULA
                , PNAT_NO_PESSOA
                , 'ANEXO' AS TIPO_JUNTADA
                , VIPD_IC_ORIGINAL AS IC_ORIGINAL

            FROM
              SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
              INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                  ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
              INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                  ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
              INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                  ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        

              INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                  ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
              INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                  ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE

              INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                  ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
              INNER JOIN OCS_TB_PMAT_MATRICULA
                  ON VIPD_CD_MATR_VINCULACAO = PMAT_CD_MATRICULA
              INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                  ON PMAT_ID_PESSOA = PNAT_ID_PESSOA

              WHERE
                  VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                  AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR . "
                  AND DOCM_IC_PROCESSO_AUTUADO = 'N'
                  AND VIPD_IC_ATIVO = 'S'
                  AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "


            UNION 



        --PROCESSOS ANEXADOS
            

            SELECT
                DOCM_DOCUMENTO.DOCM_ID_DOCUMENTO
                , DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO
                , DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO_RED
                , DECODE(
                      LENGTH( DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO),
                      14, sad_pkg_nr_documento.mascara_processo(DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO),
                      sad_pkg_nr_documento.mascara_documento(DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO)
                 ) MASC_NR_DOCUMENTO
                 ,DTPD_DOCUMENTO.DTPD_NO_TIPO
                 ,DTPD_DOCUMENTO.DTPD_ID_TIPO_DOC
                 ,AQVP_DOCUMENTO.AQVP_CD_PCTT
                 ,AQAT_DOCUMENTO.AQAT_DS_ATIVIDADE     
                 ,TO_CHAR (DCPR_DOCUMENTO.DCPR_DH_VINCULACAO_DOC,'DD/MM/YYYY HH24:MI:SS') DH_JUNTADA
                 ,PMAT_JUNTADA.PMAT_CD_MATRICULA
                 ,PNAT_JUNTADA.PNAT_NO_PESSOA
                 ,'ANEXO' AS TIPO_JUNTADA
                 , DCPR_PROCESSO.DCPR_IC_ORIGINAL AS IC_ORIGINAL
            FROM
                SAD_TB_PRDI_PROCESSO_DIGITAL PRDI_PROCESSO
                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR_PROCESSO
                    ON PRDI_PROCESSO.PRDI_ID_PROCESSO_DIGITAL = DCPR_PROCESSO.DCPR_ID_PROCESSO_DIGITAL
                    AND PRDI_PROCESSO.PRDI_ID_PROCESSO_DIGITAL = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "

                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM_PROCESSO
                    ON DCPR_PROCESSO.DCPR_ID_DOCUMENTO = DOCM_PROCESSO.DOCM_ID_DOCUMENTO
                    AND DOCM_PROCESSO.DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                    AND DOCM_PROCESSO.DOCM_IC_PROCESSO_AUTUADO = 'N'


                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR_DOCUMENTO
                    ON DCPR_PROCESSO.DCPR_ID_PROCESSO_DIGITAL = DCPR_DOCUMENTO.DCPR_ID_PROCESSO_DIGITAL
                    AND DCPR_DOCUMENTO.DCPR_IC_ATIVO = 'S'

                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM_DOCUMENTO
                    ON DCPR_DOCUMENTO.DCPR_ID_DOCUMENTO = DOCM_DOCUMENTO.DOCM_ID_DOCUMENTO
                    AND DOCM_DOCUMENTO.DOCM_ID_TIPO_DOC != " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                    AND DOCM_DOCUMENTO.DOCM_IC_PROCESSO_AUTUADO = 'S'

                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_PROCESSO
                    ON DOCM_PROCESSO.DOCM_ID_DOCUMENTO = MODO_PROCESSO.MODO_ID_DOCUMENTO

                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_PROCESSO
                    ON MODO_PROCESSO.MODO_ID_MOVIMENTACAO = MOFA_PROCESSO.MOFA_ID_MOVIMENTACAO
                    AND DCPR_DOCUMENTO.DCPR_DH_VINCULACAO_DOC = MOFA_PROCESSO.MOFA_DH_FASE

                INNER JOIN OCS_TB_PMAT_MATRICULA PMAT_JUNTADA
                    ON MOFA_PROCESSO.MOFA_CD_MATRICULA = PMAT_JUNTADA.PMAT_CD_MATRICULA

                INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT_JUNTADA
                    ON PMAT_JUNTADA.PMAT_ID_PESSOA = PNAT_JUNTADA.PNAT_ID_PESSOA

                INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD_DOCUMENTO
                    ON DOCM_DOCUMENTO.DOCM_ID_TIPO_DOC = DTPD_DOCUMENTO.DTPD_ID_TIPO_DOC

                INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP_DOCUMENTO
                    ON AQVP_DOCUMENTO.AQVP_ID_PCTT = DOCM_DOCUMENTO.DOCM_ID_PCTT
                INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT_DOCUMENTO
                    ON AQVP_DOCUMENTO.AQVP_ID_AQAT = AQAT_DOCUMENTO.AQAT_ID_ATIVIDADE
        ) SUB_QUERY
        ORDER BY TO_DATE(SUB_QUERY.DH_JUNTADA,'DD/MM/YYYY HH24:MI:SS') DESC
        ";
        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna os processos e documentos anexados ao processo. Porém com filtro
     * 
     * @param array $processo
     * @param array $filtro
     * @return array
     */
    public function getAnexadosAoProcessoFiltro($processo, $filtro) {

        $where = '';
        $and = '';
        if (isset($filtro['pesquisar_autor_juntada']) && $filtro['pesquisar_autor_juntada'] != '') {
            $aux = explode(' - ', $filtro['pesquisar_autor_juntada']);
            $where .= $and . 'PMAT_CD_MATRICULA = \'' . $aux[0] . '\'';
            $and = ' AND ';
        }
        //unset($filtro['pesquisar_autor_juntada']);

        if (isset($filtro['pesquisar_data_juntada']) && $filtro['pesquisar_data_juntada'] != '') {
            $where .= $and . ' SUBSTR(DH_JUNTADA,0,10) = \'' . $filtro['pesquisar_data_juntada'] . '\' ';
            $and = ' AND ';
        }
        //unset($filtro['pesquisar_data_juntada']);

        if (isset($filtro['pesquisar_ic_juntada']) && $filtro['pesquisar_ic_juntada'] == 'S') {
            $aux = self::getTiposComJuntada();
            $where .= $and . 'DTPD_ID_TIPO_DOC IN(' . implode(',', $aux) . ')';
            $and = ' AND ';
        }
        //unset($filtro['pesquisar_ic_juntada']);

        if (isset($filtro['DOCM_NR_DOCUMENTO']) && $filtro['DOCM_NR_DOCUMENTO'] != '') {
            $where .= $and . 'DOCM_NR_DOCUMENTO = ' . $filtro['DOCM_NR_DOCUMENTO'];
            $and = ' AND ';
        }
        if (isset($filtro['DOCM_DS_PALAVRA_CHAVE']) && $filtro['DOCM_DS_PALAVRA_CHAVE'] != '') {
            $where .= $and . 'DOCM_DS_PALAVRA_CHAVE = \'' . $filtro['DOCM_DS_PALAVRA_CHAVE'] . '\'';
            $and = ' AND ';
        }
        if (isset($filtro['DOCM_ID_PCTT']) && $filtro['DOCM_ID_PCTT'] != '') {
            $rn_pctt = new Trf1_Sisad_Negocio_Pctt();
            $aux = $rn_pctt->getPcttAjax($filtro['DOCM_ID_PCTT']);
            //podem retornar varios pctts
            //então só funfa se for um
            if (count($aux) == 1) {
                $pctt = $aux[0];
                $where .= $and . 'DOCM_ID_PCTT = ' . $pctt['AQVP_ID_PCTT'];
                $and = ' AND ';
            }
        }
        if ($where != '') {
            $where = ' WHERE ' . $where;
        }


        $sql = "
        SELECT 
        
        SUB_QUERY.*
        ,(
            SELECT COUNT(*)
            FROM SAD_TB_ANEX_ANEXO 
            INNER JOIN SAD_TB_MOFA_MOVI_FASE
                ON MOFA_ID_MOVIMENTACAO = ANEX_ID_MOVIMENTACAO
                AND MOFA_DH_FASE = ANEX_DH_FASE

            INNER JOIN OCS_TB_PMAT_MATRICULA
                ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA

            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
            WHERE ANEX_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
        ) AS QTD_ANEXOS_SEM_METADADOS
        
        FROM (
        
        --DOCUMENTOS ANEXADOS
        
            SELECT
                DOCM.DOCM_ID_DOCUMENTO
                , DOCM.DOCM_NR_DOCUMENTO
                , DOCM.DOCM_NR_DOCUMENTO_RED
                , DOCM_ID_PCTT
                , DOCM_DS_PALAVRA_CHAVE
                , DECODE(
                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                ) MASC_NR_DOCUMENTO
                , DTPD.DTPD_NO_TIPO
                , DTPD.DTPD_ID_TIPO_DOC
                , AQVP_CD_PCTT
                , AQAT_DS_ATIVIDADE
                , TO_CHAR(VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') DH_JUNTADA
                , PMAT_CD_MATRICULA
                , PNAT_NO_PESSOA
                , VIPD_IC_ORIGINAL AS IC_ORIGINAL

            FROM
              SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
              INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                  ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
              INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                  ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
              INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                  ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        

              INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                  ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
              INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                  ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE

              INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                  ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
              INNER JOIN OCS_TB_PMAT_MATRICULA
                  ON VIPD_CD_MATR_VINCULACAO = PMAT_CD_MATRICULA
              INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                  ON PMAT_ID_PESSOA = PNAT_ID_PESSOA

              WHERE
                  VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                  AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR . "
                  AND DOCM_IC_PROCESSO_AUTUADO = 'N'
                  AND VIPD_IC_ATIVO = 'S'
                  AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "


            UNION 



        --PROCESSOS ANEXADOS
            

            SELECT
                DOCM_DOCUMENTO.DOCM_ID_DOCUMENTO
                , DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO
                , DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO_RED
                , DOCM_DOCUMENTO.DOCM_ID_PCTT
                , DOCM_DOCUMENTO.DOCM_DS_PALAVRA_CHAVE
                , DECODE(
                      LENGTH( DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO),
                      14, sad_pkg_nr_documento.mascara_processo(DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO),
                      sad_pkg_nr_documento.mascara_documento(DOCM_DOCUMENTO.DOCM_NR_DOCUMENTO)
                 ) MASC_NR_DOCUMENTO
                 ,DTPD_DOCUMENTO.DTPD_NO_TIPO
                 ,DTPD_DOCUMENTO.DTPD_ID_TIPO_DOC
                 ,AQVP_DOCUMENTO.AQVP_CD_PCTT
                 ,AQAT_DOCUMENTO.AQAT_DS_ATIVIDADE     
                 ,TO_CHAR (DCPR_DOCUMENTO.DCPR_DH_VINCULACAO_DOC,'DD/MM/YYYY HH24:MI:SS') DH_JUNTADA
                 ,PMAT_JUNTADA.PMAT_CD_MATRICULA
                 ,PNAT_JUNTADA.PNAT_NO_PESSOA
                 ,DCPR_PROCESSO.DCPR_IC_ORIGINAL AS IC_ORIGINAL
            FROM
                SAD_TB_PRDI_PROCESSO_DIGITAL PRDI_PROCESSO
                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR_PROCESSO
                    ON PRDI_PROCESSO.PRDI_ID_PROCESSO_DIGITAL = DCPR_PROCESSO.DCPR_ID_PROCESSO_DIGITAL
                    AND PRDI_PROCESSO.PRDI_ID_PROCESSO_DIGITAL = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "

                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM_PROCESSO
                    ON DCPR_PROCESSO.DCPR_ID_DOCUMENTO = DOCM_PROCESSO.DOCM_ID_DOCUMENTO
                    AND DOCM_PROCESSO.DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                    AND DOCM_PROCESSO.DOCM_IC_PROCESSO_AUTUADO = 'N'


                INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR_DOCUMENTO
                    ON DCPR_PROCESSO.DCPR_ID_PROCESSO_DIGITAL = DCPR_DOCUMENTO.DCPR_ID_PROCESSO_DIGITAL
                    AND DCPR_DOCUMENTO.DCPR_IC_ATIVO = 'S'

                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM_DOCUMENTO
                    ON DCPR_DOCUMENTO.DCPR_ID_DOCUMENTO = DOCM_DOCUMENTO.DOCM_ID_DOCUMENTO
                    AND DOCM_DOCUMENTO.DOCM_ID_TIPO_DOC != " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                    AND DOCM_DOCUMENTO.DOCM_IC_PROCESSO_AUTUADO = 'S'

                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_PROCESSO
                    ON DOCM_PROCESSO.DOCM_ID_DOCUMENTO = MODO_PROCESSO.MODO_ID_DOCUMENTO

                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_PROCESSO
                    ON MODO_PROCESSO.MODO_ID_MOVIMENTACAO = MOFA_PROCESSO.MOFA_ID_MOVIMENTACAO
                    AND DCPR_DOCUMENTO.DCPR_DH_VINCULACAO_DOC = MOFA_PROCESSO.MOFA_DH_FASE

                INNER JOIN OCS_TB_PMAT_MATRICULA PMAT_JUNTADA
                    ON MOFA_PROCESSO.MOFA_CD_MATRICULA = PMAT_JUNTADA.PMAT_CD_MATRICULA

                INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT_JUNTADA
                    ON PMAT_JUNTADA.PMAT_ID_PESSOA = PNAT_JUNTADA.PNAT_ID_PESSOA

                INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD_DOCUMENTO
                    ON DOCM_DOCUMENTO.DOCM_ID_TIPO_DOC = DTPD_DOCUMENTO.DTPD_ID_TIPO_DOC

                INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP_DOCUMENTO
                    ON AQVP_DOCUMENTO.AQVP_ID_PCTT = DOCM_DOCUMENTO.DOCM_ID_PCTT
                INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT_DOCUMENTO
                    ON AQVP_DOCUMENTO.AQVP_ID_AQAT = AQAT_DOCUMENTO.AQAT_ID_ATIVIDADE
        ) SUB_QUERY
        $where
        ORDER BY TO_DATE(SUB_QUERY.DH_JUNTADA,'DD/MM/YYYY HH24:MI:SS') DESC
        ";
        
        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna array os documentos sem meta dados juntados ao documento
     * 
     * @param	int	$idDocumento
     * @return	array
     * @author	Desconhecido
     */
    public function getAnexadosSemMetadados($documento) {
        $sql = '
        SELECT ANEX_ID_DOCUMENTO,
            ANEX_NR_DOCUMENTO_INTERNO,
            ANEX_ID_MOVIMENTACAO,
            TO_CHAR(ANEX_DH_FASE, \'dd/mm/yyyy HH24:MI:SS\') ANEX_DH_FASE,
            ANEX_ID_TP_EXTENSAO,
            PMAT_CD_MATRICULA,
            PNAT_NO_PESSOA
        
        FROM SAD_TB_ANEX_ANEXO 
        INNER JOIN SAD_TB_MOFA_MOVI_FASE
            ON MOFA_ID_MOVIMENTACAO = ANEX_ID_MOVIMENTACAO
            AND MOFA_DH_FASE = ANEX_DH_FASE
            
        INNER JOIN OCS_TB_PMAT_MATRICULA
            ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA

        INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
            ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
        WHERE ANEX_ID_DOCUMENTO = ' . $documento['DOCM_ID_DOCUMENTO'] . '
        ORDER BY ANEX_DH_FASE DESC
        ';
        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna os processos e documentos anexados ao processo. Porém com filtro
     * 
     * @param array $processo
     * @param array $filtro
     * @return array
     */
    public function getApensadosAtivosFiltro($processo, $filtro) {

        $where = ' PRDI_ID_PROCESSO_DIGITAL != ' . $processo['PRDI_ID_PROCESSO_DIGITAL'];
        $and = ' AND ';
        if (isset($filtro['pesquisar_autor_juntada']) && $filtro['pesquisar_autor_juntada'] != '') {
            $aux = explode(' - ', $filtro['pesquisar_autor_juntada']);
            $where .= $and . 'PMAT_CD_MATRICULA = \'' . $aux[0] . '\'';
            $and = ' AND ';
        }
        //unset($filtro['pesquisar_autor_juntada']);

        if (isset($filtro['pesquisar_data_juntada_apenso']) && $filtro['pesquisar_data_juntada_apenso'] != '') {
            $where .= $and . ' SUBSTR(DH_JUNTADA,0,10) = \'' . $filtro['pesquisar_data_juntada_apenso'] . '\' ';
            $and = ' AND ';
        }
        //unset($filtro['pesquisar_data_juntada']);

        if (isset($filtro['DOCM_NR_DOCUMENTO']) && $filtro['DOCM_NR_DOCUMENTO'] != '') {
            $where .= $and . 'DOCM_NR_DOCUMENTO = ' . $filtro['DOCM_NR_DOCUMENTO'];
            $and = ' AND ';
        }
        if (isset($filtro['DOCM_DS_PALAVRA_CHAVE']) && $filtro['DOCM_DS_PALAVRA_CHAVE'] != '') {
            $where .= $and . 'DOCM_DS_PALAVRA_CHAVE = \'' . $filtro['DOCM_DS_PALAVRA_CHAVE'] . '\'';
            $and = ' AND ';
        }
        if (isset($filtro['DOCM_ID_PCTT']) && $filtro['DOCM_ID_PCTT'] != '') {
            $rn_pctt = new Trf1_Sisad_Negocio_Pctt();
            $aux = $rn_pctt->getPcttAjax($filtro['DOCM_ID_PCTT']);
            //podem retornar varios pctts
            //então só funfa se for um
            if (count($aux) == 1) {
                $pctt = $aux[0];
                $where .= $and . 'DOCM_ID_PCTT = ' . $pctt['AQVP_ID_PCTT'];
                $and = ' AND ';
            }
        }
        if ($where != '') {
            $where = ' WHERE ' . $where;
        }

        $sql = "
                    SELECT 
                        SUB_QUERY.*
                        ,(
                            SELECT COUNT(*)
                            FROM SAD_TB_ANEX_ANEXO 
                            INNER JOIN SAD_TB_MOFA_MOVI_FASE
                                ON MOFA_ID_MOVIMENTACAO = ANEX_ID_MOVIMENTACAO
                                AND MOFA_DH_FASE = ANEX_DH_FASE

                            INNER JOIN OCS_TB_PMAT_MATRICULA
                                ON MOFA_CD_MATRICULA = PMAT_CD_MATRICULA

                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL
                                ON PMAT_ID_PESSOA = PNAT_ID_PESSOA
                            WHERE ANEX_ID_DOCUMENTO = DOCM_ID_DOCUMENTO
                        ) AS QTD_ANEXOS_SEM_METADADOS 
                    FROM (
                        -- NESTE SELECT SE UM PROCESSO FOR FILHO ENTAO ELE VAI LISTAR APENAS OS PAIS A QUERY QUE MOSTRA OS FILHOS NÃO APARECER
                        -- SE A LÓGICA DE INSERIR DA JUNTADA TIVER CORRETA É CLARO!
                        -- MAS SE O PROCESSO FOR UM PAI ENTAO ELE SO VAI LISTAR OS FILHOS,
                        -- O SELECT DE PAIS NÃO deverá RETORNAR RESULTADOS SE A LÓGICA DE INSERIR NÃO ESTIVER ERRADA!


                        -- PEGA OS PROCESSOS FILHOS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            , DOCM.DOCM_ID_PCTT
                            , DOCM.DOCM_DS_PALAVRA_CHAVE
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            , VIPD_IC_ORIGINAL AS IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') DH_JUNTADA
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'NAO PRINCIPAL' AS STATUS_JUNTADA
                            , PMAT_CD_MATRICULA
                            , PNAT_NO_PESSOA
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO FILHO
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                                
                            INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                                ON VIPD.VIPD_CD_MATR_VINCULACAO = PMAT.PMAT_CD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                -- ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC IN(
                                
                                    -- CASO SEJA UM PROCESSO FILHO PEGUE O PAI, CASO SEJA UM PAI PEGUE O FILHO
                                    -- A FIM DE RETORNAR UM JUNTADA COMPLETA DE FILHOS
                                    -- ABAIXO NO WHERE SERÁ ELIMINADO O PROCESSO PASSADO POR PARAMETRO
                                    
                                    SELECT VIPD_ID_PROCESSO_DIGITAL_PRINC
                                    FROM SAD_TB_VIPD_VINC_PROC_DIGITAL
                                    WHERE    
                                        VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                        AND VIPD_IC_ATIVO = 'S'
                                        AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "

                                    UNION

                                    SELECT VIPD_ID_PROCESSO_DIGITAL_VINDO
                                    FROM SAD_TB_VIPD_VINC_PROC_DIGITAL
                                    WHERE    
                                        VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                        AND VIPD_IC_ATIVO = 'S'
                                        AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "
                                            
                                    UNION
                                    SELECT " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . " FROM DUAL
                                )
                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "


                        UNION


                        -- PEGA OS PROCESSOS PAIS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            , DOCM.DOCM_ID_PCTT
                            , DOCM.DOCM_DS_PALAVRA_CHAVE
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            , VIPD_IC_ORIGINAL AS IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') DH_JUNTADA
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'PRINCIPAL' AS STATUS_JUNTADA
                            , PMAT_CD_MATRICULA
                            , PNAT_NO_PESSOA
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO PAI
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                                
                            INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                                ON VIPD.VIPD_CD_MATR_VINCULACAO = PMAT.PMAT_CD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                --ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "

                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR . "  
                    ) SUB_QUERY
                    $where
                    ORDER BY TO_DATE (DH_JUNTADA,'DD/MM/YYYY HH24:MI:SS') DESC";

        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna os processos e documentos anexados ao processo. Porém com filtro
     * 
     * @param array $processo
     * @param array $filtro
     * @return array
     */
    public function getVinculosAtivosFiltro($processo, $filtro) {

        $where = ' PRDI_ID_PROCESSO_DIGITAL != ' . $processo['PRDI_ID_PROCESSO_DIGITAL'];
        $and = ' AND ';
        if (isset($filtro['pesquisar_autor_juntada']) && $filtro['pesquisar_autor_juntada'] != '') {
            $aux = explode(' - ', $filtro['pesquisar_autor_juntada']);
            $where .= $and . 'PMAT_CD_MATRICULA = \'' . $aux[0] . '\'';
            $and = ' AND ';
        }
        //unset($filtro['pesquisar_autor_juntada']);

        if (isset($filtro['pesquisar_data_juntada_vinculo']) && $filtro['pesquisar_data_juntada_vinculo'] != '') {
            $where .= $and . ' SUBSTR(VIPD_DH_VINCULACAO,0,10) = \'' . $filtro['pesquisar_data_juntada_vinculo'] . '\' ';
            $and = ' AND ';
        }
        //unset($filtro['pesquisar_data_juntada']);

        if (isset($filtro['DOCM_NR_DOCUMENTO']) && $filtro['DOCM_NR_DOCUMENTO'] != '') {
            $where .= $and . 'DOCM_NR_DOCUMENTO = ' . $filtro['DOCM_NR_DOCUMENTO'];
            $and = ' AND ';
        }
        if (isset($filtro['DOCM_DS_PALAVRA_CHAVE']) && $filtro['DOCM_DS_PALAVRA_CHAVE'] != '') {
            $where .= $and . 'DOCM_DS_PALAVRA_CHAVE = \'' . $filtro['DOCM_DS_PALAVRA_CHAVE'] . '\'';
            $and = ' AND ';
        }
        if (isset($filtro['DOCM_ID_PCTT']) && $filtro['DOCM_ID_PCTT'] != '') {
            $rn_pctt = new Trf1_Sisad_Negocio_Pctt();
            $aux = $rn_pctt->getPcttAjax($filtro['DOCM_ID_PCTT']);
            //podem retornar varios pctts
            //então só funfa se for um
            if (count($aux) == 1) {
                $pctt = $aux[0];
                $where .= $and . 'DOCM_ID_PCTT = ' . $pctt['AQVP_ID_PCTT'];
                $and = ' AND ';
            }
        }
        if ($where != '') {
            $where = ' WHERE ' . $where;
        }

        $sql = "
                    SELECT 
                *
            FROM (
                        -- NESTE SELECT SE UM PROCESSO FOR FILHO ENTAO ELE VAI LISTAR APENAS OS PAIS A QUERY QUE MOSTRA OS FILHOS NÃO APARECER
                        -- SE A LÓGICA DE INSERIR DA JUNTADA TIVER CORRETA É CLARO!
                        -- MAS SE O PROCESSO FOR UM PAI ENTAO ELE SO VAI LISTAR OS FILHOS,
                        -- O SELECT DE PAIS NÃO deverá RETORNAR RESULTADOS SE A LÓGICA DE INSERIR NÃO ESTIVER ERRADA!


                        -- PEGA OS PROCESSOS FILHOS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM_ID_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            , DOCM.DOCM_DS_PALAVRA_CHAVE
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'NAO PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                            , PMAT_CD_MATRICULA
                            , PNAT_NO_PESSOA
                            , 'VINCULO' AS TIPO_JUNTADA
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO FILHO
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                                
                            INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                                ON VIPD.VIPD_CD_MATR_VINCULACAO = PMAT.PMAT_CD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                

                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                -- ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "
                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR . "


                        UNION


                        -- PEGA OS PROCESSOS PAIS
                        SELECT
                            DTPD.DTPD_NO_TIPO
                            , DTPD.DTPD_ID_TIPO_DOC
                            , PRDI_ID_PROCESSO_DIGITAL
                            , PRDI_DS_TEXTO_AUTUACAO
                            , AQVP_CD_PCTT
                            , DOCM_ID_PCTT
                            , DOCM.DOCM_ID_DOCUMENTO
                            , DOCM.DOCM_NR_DOCUMENTO
                            , DOCM.DOCM_DS_PALAVRA_CHAVE
                            --TIPO DE JUNTADA ATUAL
                            , VIPD_ID_TP_VINCULACAO
                            , VIPD_IC_ATIVO
                            , VIPD_IC_ORIGINAL
                            ,DECODE(
                                    LENGTH( DOCM.DOCM_NR_DOCUMENTO),
                                    14, sad_pkg_nr_documento.mascara_processo(DOCM.DOCM_NR_DOCUMENTO),
                                    sad_pkg_nr_documento.mascara_documento(DOCM.DOCM_NR_DOCUMENTO)
                               ) MASC_NR_DOCUMENTO
                            , LOTA.LOTA_SIGLA_SECAO LOTA_SIGLA_SECAO_EMISSORA
                            , LOTA.LOTA_SIGLA_LOTACAO LOTA_SIGLA_LOTACAO_EMISSORA
                            , LOTA.LOTA_COD_LOTACAO LOTA_COD_LOTACAO_EMISSORA
                            , RH_DESCRICAO_CENTRAL_LOTACAO (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO_EMISSORA
                            , RH_SIGLAS_FAMILIA_CENTR_LOTA (LOTA.LOTA_SIGLA_SECAO,LOTA.LOTA_COD_LOTACAO) FAMILIA_EMISSORA
                            , TO_CHAR (VIPD.VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') VIPD_DH_VINCULACAO
                            , DOCM.DOCM_NR_DOCUMENTO_RED
                            , AQAT.AQAT_DS_ATIVIDADE
                            , CONF.CONF_ID_CONFIDENCIALIDADE
                            , 'PRINCIPAL' AS STATUS_JUNTADA
                            , '' AS PMAT_CD_MATRICULA_EXCLUIDOR
                            , '' AS PNAT_NO_PESSOA_EXCLUIDOR
                            , null AS VPPF_DH_FASE
                            , PMAT_CD_MATRICULA
                            , PNAT_NO_PESSOA
                            , 'VINCULO' AS TIPO_JUNTADA
                        FROM
                            SAD_TB_VIPD_VINC_PROC_DIGITAL VIPD
                            INNER JOIN SAD_TB_PRDI_PROCESSO_DIGITAL PRDI
                            -- ID PROCESSO PAI
                                ON VIPD.VIPD_ID_PROCESSO_DIGITAL_PRINC = PRDI.PRDI_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                ON PRDI.PRDI_ID_PROCESSO_DIGITAL = DCPR.DCPR_ID_PROCESSO_DIGITAL
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                                ON DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO        
                            INNER JOIN RH_CENTRAL_LOTACAO LOTA
                                ON DOCM.DOCM_CD_LOTACAO_GERADORA = LOTA.LOTA_COD_LOTACAO
                                AND DOCM.DOCM_SG_SECAO_GERADORA = LOTA.LOTA_SIGLA_SECAO
                            INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                                ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                            INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                                ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                            INNER JOIN SAD_TB_CONF_CONFIDENCIALIDADE CONF
                                ON DOCM.DOCM_ID_CONFIDENCIALIDADE = CONF.CONF_ID_CONFIDENCIALIDADE
                            INNER JOIN OCS_TB_DTPD_TIPO_DOC DTPD
                                ON DOCM_ID_TIPO_DOC = DTPD_ID_TIPO_DOC
                                
                            INNER JOIN OCS_TB_PMAT_MATRICULA PMAT
                                ON VIPD.VIPD_CD_MATR_VINCULACAO = PMAT.PMAT_CD_MATRICULA
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL PNAT
                                ON PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                                
                            WHERE
                                DOCM_IC_PROCESSO_AUTUADO = 'N'
                                --ID PROCESSO FILHO
                                AND VIPD.VIPD_ID_PROCESSO_DIGITAL_VINDO = " . $processo['PRDI_ID_PROCESSO_DIGITAL'] . "

                                AND DOCM_ID_TIPO_DOC = " . Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO . "
                                AND VIPD_IC_ATIVO = 'S'
                                AND VIPD_ID_TP_VINCULACAO = " . Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR . "


                        
            ) 
            $where
            ORDER BY TO_DATE (VIPD_DH_VINCULACAO,'DD/MM/YYYY HH24:MI:SS') DESC";

        return $this->_db->fetchAll($sql);
    }

    /**
     * Retorna todos os tipos de documento que obrigatoriamente possuem juntada
     * @return array
     */
    public static function getTiposComJuntada() {
        return array(
            Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO
        );
    }

    /**
     * Retorna todos os tipos de documento que obrigatoriamente possuem juntada
     * @return array
     */
    public static function isDocumentoComJuntada($documento) {
        if (in_array($documento['DTPD_ID_TIPO_DOC'], array(Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO))) {
            return true;
        } else {
            
        }
    }

}