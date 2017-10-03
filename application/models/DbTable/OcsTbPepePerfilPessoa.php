<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_OcsTbPepePerfilPessoa extends Zend_Db_Table_Abstract {

    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PEPE_PERFIL_PESSOA';
    protected $_primary = 'PEPE_ID_PERFIL_PESSOA';
    protected $_sequence = 'OCS_SQ_PEPE_UN_PERFIL_PESSOA';

    public function getPessoa($lota_cod_lotacao, $sigla_secao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PMAT_ID_PESSOA,  B.PNAT_NO_PESSOA, A.PMAT_CD_MATRICULA 
					FROM OCS_TB_PMAT_MATRICULA A, OCS_TB_PNAT_PESSOA_NATURAL B
					WHERE A.PMAT_CD_UNIDADE_LOTACAO = $lota_cod_lotacao
                                        AND PMAT_SG_SECSUBSEC_LOTACAO = '$sigla_secao'
					AND  A.PMAT_ID_PESSOA = B.PNAT_ID_PESSOA
                                        AND A.PMAT_DT_FIM IS NULL
					ORDER BY B.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getPerfisAssociados($cod_lotacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  UNPE_ID_UNIDADE_PERFIL,UNPE_SG_SECAO,UNPE_CD_LOTACAO,UNPE_ID_PERFIL,PERF_DS_PERFIL
                                FROM  OCS_TB_UNPE_UNIDADE_PERFIL A, OCS_TB_PERF_PERFIL B
                                WHERE A.UNPE_ID_PERFIL = B.PERF_ID_PERFIL
                                AND A.UNPE_CD_LOTACAO = $cod_lotacao");
        return $stmt->fetchAll();
    }

    public function getSistemas() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NOME_SISTEMA, DS_NOME_SISTEMA
               			FROM  SISTEMAS_TRF
                		WHERE NOME_SISTEMA  = 'SOSTI' 
                                OR NOME_SISTEMA ='SISAD'");
        return $stmt->fetchAll();
    }

    public function getPerfilbyUnidadePessoa($sgsessao, $cdlotacao, $matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT PERF.PERF_ID_PERFIL, PERF.PERF_DS_PERFIL, PEPE.PEPE_ID_PERFIL_PESSOA
                                FROM  OCS_TB_PERF_PERFIL  PERF,
                                      OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                      OCS_TB_PEPE_PERFIL_PESSOA PEPE
                                WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                                AND   UNPE.UNPE_ID_PERFIL = PEPE.PEPE_ID_PERFIL
                                AND   PERF.PERF_ID_PERFIL = PEPE.PEPE_ID_PERFIL
                                AND   UNPE.UNPE_SG_SECAO = '$sgsessao'
                                AND   UNPE.UNPE_CD_LOTACAO = $cdlotacao
                                AND   PEPE.PEPE_CD_MATRICULA = '$matricula'
                                UNION
                                SELECT DISTINCT PERF.PERF_ID_PERFIL, PERF.PERF_DS_PERFIL, PEPE_ID_PERFIL_PESSOA
                                FROM  OCS_TB_PERF_PERFIL  PERF,
                                      OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                      (SELECT NULL AS PEPE_ID_PERFIL_PESSOA FROM DUAL)
                                WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                                AND   UNPE.UNPE_SG_SECAO = '$sgsessao'
                                AND   UNPE.UNPE_CD_LOTACAO = $cdlotacao
                                AND   PERF_ID_PERFIL NOT IN 
                                (
                                SELECT DISTINCT PERF.PERF_ID_PERFIL
                                FROM  OCS_TB_PERF_PERFIL  PERF,
                                      OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                      OCS_TB_PEPE_PERFIL_PESSOA PEPE
                                WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                                AND   UNPE.UNPE_ID_PERFIL = PEPE.PEPE_ID_PERFIL
                                AND   PERF.PERF_ID_PERFIL = PEPE.PEPE_ID_PERFIL
                                AND   UNPE.UNPE_SG_SECAO = '$sgsessao'
                                AND   UNPE.UNPE_CD_LOTACAO = $cdlotacao
                                AND   PEPE.PEPE_CD_MATRICULA = '$matricula'
                                )
                            ");
        return $stmt->fetchAll();
    }

    public function getPerfilUnidadePessoa($sgsessao, $cdlotacao, $matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PERF.PERF_ID_PERFIL, PERF.PERF_DS_PERFIL, UNPE.UNPE_ID_UNIDADE_PERFIL, UNPE.UNPE_SG_SECAO, UNPE.UNPE_CD_LOTACAO, PUPE.PUPE_ID_UNIDADE_PERFIL
                              FROM OCS_TB_PERF_PERFIL PERF
                              INNER JOIN OCS_TB_UNPE_UNIDADE_PERFIL UNPE
                              ON  PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                              INNER JOIN OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE
                              ON  UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                              WHERE UNPE.UNPE_SG_SECAO = '$sgsessao'
                              AND   UNPE.UNPE_CD_LOTACAO = $cdlotacao
                              AND  PUPE.PUPE_CD_MATRICULA = '$matricula'

                              UNION

                              SELECT PERF.PERF_ID_PERFIL, PERF.PERF_DS_PERFIL, UNPE.UNPE_ID_UNIDADE_PERFIL, UNPE.UNPE_SG_SECAO, UNPE.UNPE_CD_LOTACAO, NULL
                              FROM OCS_TB_PERF_PERFIL PERF
                              INNER JOIN OCS_TB_UNPE_UNIDADE_PERFIL UNPE
                              ON  PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                              AND   UNPE.UNPE_SG_SECAO = '$sgsessao'
                              AND   UNPE.UNPE_CD_LOTACAO = $cdlotacao
                              WHERE (UNPE.UNPE_ID_UNIDADE_PERFIL, UNPE.UNPE_SG_SECAO, UNPE.UNPE_CD_LOTACAO) NOT IN 

                              ( 
                              SELECT UNPE.UNPE_ID_UNIDADE_PERFIL, UNPE.UNPE_SG_SECAO, UNPE.UNPE_CD_LOTACAO
                              FROM OCS_TB_PERF_PERFIL PERF
                              INNER JOIN OCS_TB_UNPE_UNIDADE_PERFIL UNPE
                              ON  PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                              INNER JOIN OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE
                              ON  UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                              AND   UNPE.UNPE_SG_SECAO = '$sgsessao'
                              AND   UNPE.UNPE_CD_LOTACAO = $cdlotacao
                              WHERE PUPE.PUPE_CD_MATRICULA = '$matricula'
                              )   
                            ");
        return $stmt->fetchAll();
    }

    public function getDeletar($papl_id_papel) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("DELETE FROM OCS_TB_PERF_PERFIL
                                WHERE PERF_ID_PAPEL = $papl_id_papel");
    }

    public function getUpdate($papl_nm_papel, $papl_ds_finalidade, $papl_id_papel) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("UPDATE OCS_TB_PAPL_PAPEL
                                SET PAPL_NM_PAPEL = '$papl_nm_papel',
                                    PAPL_DS_FINALIDADE = '$papl_ds_finalidade'
                                    WHERE PAPL_ID_PAPEL = $papl_id_papel");
    }

    /**
     * Função que verifica se o usuário é desenvolvedor e-Admin
     * @param String $matricula
     * @return type
     */
    public function verificaPessoaDesen($matricula) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("
                            SELECT COUNT(*) VALOR 
                            FROM 
                            OCS_TB_PMAT_MATRICULA PMAT, OCS_TB_PNAT_PESSOA_NATURAL PNAT,
                            OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE, OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                            OCS_TB_PERF_PERFIL PERF
                            WHERE
                            PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA AND
                            PMAT.PMAT_CD_MATRICULA = PUPE.PUPE_CD_MATRICULA AND
                            PUPE.PUPE_ID_UNIDADE_PERFIL = UNPE.UNPE_ID_UNIDADE_PERFIL AND
                            UNPE.UNPE_ID_PERFIL = PERF.PERF_ID_PERFIL AND
                            PERF.PERF_ID_PERFIL = 8 AND
                            PUPE.PUPE_CD_MATRICULA = '$matricula'   
                            ");
        return $stmt->fetch();
    }

}