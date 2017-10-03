<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_OcsTbPupePerfilUnidPessoa extends Zend_Db_Table_Abstract {

    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PUPE_PERFIL_UNID_PESSOA';
    protected $_primary = array('PUPE_ID_UNIDADE_PERFIL', 'PUPE_CD_MATRICULA');

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
                              ORDER BY PERF_DS_PERFIL ASC
                            ");
        return $stmt->fetchAll();
    }

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

    public function getPossuiPerfil($idPerfil, $matricula, $order = 'PNAT_NO_PESSOA') {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT PMAT_CD_MATRICULA,PNAT_NO_PESSOA,PMAT_CD_MATRICULA, RHLOTA.LOTA_COD_LOTACAO, 
                                    RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO,RHLOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO, 
                                    RHLOTA.LOTA_SIGLA_LOTACAO,
                                    RHLOTA.LOTA_SIGLA_SECAO, 
                                    PERF_ID_PERFIL, 
                                    PERF_DS_PERFIL
                              FROM  OCS_TB_PERF_PERFIL  PERF,
                                    OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                    OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                                    RH_CENTRAL_LOTACAO RHLOTA,
                                    OCS_TB_PMAT_MATRICULA PMAT,
                                    OCS_TB_PNAT_PESSOA_NATURAL PNAT
                              WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                              AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                              AND   UNPE_SG_SECAO   =  RHLOTA.LOTA_SIGLA_SECAO
                              AND   UNPE_CD_LOTACAO =  RHLOTA.LOTA_COD_LOTACAO
                              AND   PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                              AND   PUPE.PUPE_CD_MATRICULA = PMAT.PMAT_CD_MATRICULA
                              AND   PERF_ID_PERFIL = $idPerfil 
                              AND   PMAT_CD_MATRICULA = '$matricula' 
                              ORDER BY $order
                           ");
        return $stmt->fetchAll();
    }

    /**
     * @abstract Retorna true ou false se uma matricula possui ou não um perfil
     * @since 27/07/2012
     * @param string $nomePerfil
     * @param string $matricula
     * @return bollean 
     */
    public function getPossuiPerfilPorNome($nomePerfil, $matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT COUNT(*) COUNT
                              FROM  OCS_TB_PERF_PERFIL  PERF,
                                    OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                    OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                                    RH_CENTRAL_LOTACAO RHLOTA,
                                    OCS_TB_PMAT_MATRICULA PMAT,
                                    OCS_TB_PNAT_PESSOA_NATURAL PNAT
                              WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                              AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                              AND   UNPE_SG_SECAO   =  RHLOTA.LOTA_SIGLA_SECAO
                              AND   UNPE_CD_LOTACAO =  RHLOTA.LOTA_COD_LOTACAO
                              AND   PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                              AND   PUPE.PUPE_CD_MATRICULA = PMAT.PMAT_CD_MATRICULA
                              AND   PERF_DS_PERFIL = '$nomePerfil' 
                              AND   PMAT_CD_MATRICULA = '$matricula' 
                           ");
        $retorno = $stmt->fetch();
        if ($retorno['COUNT'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @abstract Retorna true ou false se uma matricula possui ou não um perfil de uma lotação especifica
     * @since 27/07/2012
     * @param string $nomePerfil
     * @param string $matricula
     * @return bollean 
     */
    public function getPossuiPerfilPorNomedoPerfilMatriculaUnidade($nomePerfil, $matricula, $sgsecao, $cdlocacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT COUNT(*) COUNT
                              FROM  OCS_TB_PERF_PERFIL  PERF,
                                    OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                    OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                                    RH_CENTRAL_LOTACAO RHLOTA,
                                    OCS_TB_PMAT_MATRICULA PMAT,
                                    OCS_TB_PNAT_PESSOA_NATURAL PNAT
                              WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                              AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                              AND   UNPE_SG_SECAO   =  RHLOTA.LOTA_SIGLA_SECAO
                              AND   UNPE_CD_LOTACAO =  RHLOTA.LOTA_COD_LOTACAO
                              AND   PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                              AND   PUPE.PUPE_CD_MATRICULA = PMAT.PMAT_CD_MATRICULA
                              AND   PERF_DS_PERFIL = '$nomePerfil' 
                              AND   PMAT_CD_MATRICULA = '$matricula' 
                              AND   UNPE_SG_SECAO = '$sgsecao' 
                              AND   UNPE_CD_LOTACAO = $cdlocacao 
                           ");
        $retorno = $stmt->fetch();
        if ($retorno['COUNT'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Funcao que verifica se o usuario tem permissao da corregedoria
     * @param void
     * @return false - caso o usuario nao tenha a permissao
     *       true  - caso o usuario tenha a permissao
     */

    public function verificaPermissaoCorregedoria() {

        $aNamespace = new Zend_Session_Namespace('userNs');

        if (defined('APPLICATION_ENV')) {
            if (APPLICATION_ENV == 'development') {
                $usuarioCorregedoria = $this->getPossuiPerfil(36, $aNamespace->matricula); //DSV
            } else if (APPLICATION_ENV == 'production') {
                $usuarioCorregedoria = $this->getPossuiPerfil(38, $aNamespace->matricula); //PRD
            }
        }
        if (empty($usuarioCorregedoria)) {
            return false;
        } else {
            return true;
        }
    }

    public function getMatriculasPossuiPerfilUnidade($nomePerfil, $sgsecao, $cdlocacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT PMAT_CD_MATRICULA
                              FROM  OCS_TB_PERF_PERFIL  PERF,
                                    OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                    OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                                    RH_CENTRAL_LOTACAO RHLOTA,
                                    OCS_TB_PMAT_MATRICULA PMAT,
                                    OCS_TB_PNAT_PESSOA_NATURAL PNAT
                              WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                              AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                              AND   UNPE_SG_SECAO   =  RHLOTA.LOTA_SIGLA_SECAO
                              AND   UNPE_CD_LOTACAO =  RHLOTA.LOTA_COD_LOTACAO
                              AND   PMAT.PMAT_ID_PESSOA = PNAT.PNAT_ID_PESSOA
                              AND   PUPE.PUPE_CD_MATRICULA = PMAT.PMAT_CD_MATRICULA
                              AND   PERF_DS_PERFIL = '$nomePerfil' 
                              AND   UNPE_SG_SECAO = '$sgsecao' 
                              AND   UNPE_CD_LOTACAO = $cdlocacao 
                           ");
        return $stmt->fetchAll();
    }

    /**
     * Busca os perfis que um Usuário possui em determinada Unidade
     * @param type $sgsessao - Dado da Seção
     * @param type $cdlotacao - Codigo da Lotação
     * @param type $matricula - Matrícula o usuário
     * @return array
     */
    public function getPerfisPessoaNaUnidade($sgsessao, $cdlotacao, $matricula) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT PERF_ID_PERFIL, PERF_DS_PERFIL 
                            FROM
                            OCS_TB_PERF_PERFIL PERF,
                            OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                            OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                            OCS_TB_PESS_PESSOA PESS,
                            OCS_TB_PMAT_MATRICULA MAT
                            WHERE
                            PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL AND
                            UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL AND
                            PUPE.PUPE_CD_MATRICULA = MAT.PMAT_CD_MATRICULA AND
                            MAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
                            MAT.PMAT_CD_MATRICULA = '$matricula' AND
                            UNPE.UNPE_SG_SECAO = '$sgsessao' AND
                            UNPE.UNPE_CD_LOTACAO = '$cdlotacao' 
                            ORDER BY PERF_DS_PERFIL
                           ");
        return $stmt->fetchAll();
    }
    public function getPerfiRespnsavelCaixaUnidade($sgsessao, $cdlotacao, $matricula) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT PERF_ID_PERFIL 
                            FROM
                            OCS_TB_PERF_PERFIL PERF,
                            OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                            OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                            OCS_TB_PESS_PESSOA PESS,
                            OCS_TB_PMAT_MATRICULA MAT
                            WHERE
                            PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL AND
                            UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL AND
                            PUPE.PUPE_CD_MATRICULA = MAT.PMAT_CD_MATRICULA AND
                            MAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
                            MAT.PMAT_CD_MATRICULA = '$matricula' AND
                            UNPE.UNPE_SG_SECAO = '$sgsessao' AND
                            UNPE.UNPE_CD_LOTACAO = '$cdlotacao' AND
                            PERF_DS_PERFIL   = 'RESPONSÁVEL PELA CAIXA DA UNIDADE'
                            ORDER BY PERF_DS_PERFIL
                           ");
        return $stmt->fetchAll();
    }

    /**
     * Busca os perfis da uma determinada Unidade que não estão associados a um determinado Usuário
     * @param type $sgsessao - Dado da Seção
     * @param type $cdlotacao - Codigo da Lotação
     * @param type $matricula - Matricula do usuário
     * @return array
     */
    public function getPerfisPessoaNaoAssociados($sgsessao, $cdlotacao, $matricula) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT PERF_ID_PERFIL, PERF_DS_PERFIL
                            FROM 
                            OCS_TB_PERF_PERFIL PERF,
                            OCS_TB_UNPE_UNIDADE_PERFIL UNPE
                            WHERE
                            PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL AND
                            UNPE.UNPE_SG_SECAO = '$sgsessao' AND
                            UNPE.UNPE_CD_LOTACAO = $cdlotacao AND
                            PERF_ID_PERFIL NOT IN (

                            SELECT PERF_ID_PERFIL 
                            FROM
                            OCS_TB_PERF_PERFIL PERF,
                            OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                            OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                            OCS_TB_PESS_PESSOA PESS,
                            OCS_TB_PMAT_MATRICULA MAT
                            WHERE
                            PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL AND
                            UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL AND
                            PUPE.PUPE_CD_MATRICULA = MAT.PMAT_CD_MATRICULA AND
                            MAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
                            MAT.PMAT_CD_MATRICULA = '$matricula' AND
                            UNPE.UNPE_SG_SECAO = '$sgsessao' AND
                            UNPE.UNPE_CD_LOTACAO = $cdlotacao
                            )
                            ORDER BY PERF_DS_PERFIL
                           ");
        return $stmt->fetchAll();
    }

    /**
     * Associa os perfis da Unidade à um usuário
     * @param type $matricula
     * @param type $arrayUnpe
     * @param type $matricula_sessao
     * @return boolean
     */
    public function associarPerfilUnidadeAPessoa($matricula, $arrayUnpe, $matricula_sessao) {

        /**
         * Tabela Perfil Unidade Pessoa
         */
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $OcsTbPupeAudit = new Application_Model_DbTable_OcsTbPupeAuditoria();

        /**
         * Recebendo a matricula
         */
        $dadosPupe['PUPE_CD_MATRICULA'] = $matricula;

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            foreach ($arrayUnpe as $unpe) {
                $dadosPupe['PUPE_ID_UNIDADE_PERFIL'] = $unpe['UNPE_ID_UNIDADE_PERFIL'];
                $row = $OcsTbPupePerfilUnidPessoa->createRow($dadosPupe);
                $row->save();

                /**
                 * Auditoria
                 */
                $dados_audit['PUPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                $dados_audit['PUPE_CD_OPERACAO'] = 'I';
                $dados_audit['PUPE_CD_MATRICULA_OPERACAO'] = $matricula_sessao;
                $dados_audit['PUPE_CD_MAQUINA_OPERACAO'] = 'SERVIDOR_WEB';
                $dados_audit['PUPE_CD_USUARIO_SO'] = 'SERVIDOR_WEB';
                $dados_audit['OLD_PUPE_ID_UNIDADE_PERFIL'] = 0;
                $dados_audit['NEW_PUPE_ID_UNIDADE_PERFIL'] = $dadosPupe['PUPE_ID_UNIDADE_PERFIL'];
                $dados_audit['OLD_PUPE_CD_MATRICULA'] = 0;
                $dados_audit['NEW_PUPE_CD_MATRICULA'] = $dadosPupe['PUPE_CD_MATRICULA'];
                $row_audit = $OcsTbPupeAudit->createRow($dados_audit);
                $row_audit->save();
            }
            $db->commit();
            return true;
        } catch (Zend_Exception $error_string) {
            $db->rollback();
            return false;
        }
    }

    /**
     * Desvincula todas as permissoes de um usuário para uma Unidade
     * @param type $matricula
     * @param type $dadosUnidade
     * @param type $matricula_sessao
     * @return boolean
     */
    public function desassociarTodoPerfilPessoaUnidade($matricula, $dadosUnidade, $matricula_sessao) {

        /**
         * Tabela Perfil Unidade Pessoa Auditoria
         */
        $OcsTbPupeAudit = new Application_Model_DbTable_OcsTbPupeAuditoria();

        $arrayParams[0] = $dadosUnidade['LOTA_SIGLA_SECAO'];
        $arrayParams[1] = $dadosUnidade['LOTA_COD_LOTACAO'];

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        /**
         * Busca os dados antes da exclusao
         */
        $stmt_antigos = $db->query("   
            SELECT PUPE_ID_UNIDADE_PERFIL, PUPE_CD_MATRICULA
            FROM
            OCS_TB_PERF_PERFIL PERF,
            OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
            OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
            OCS_TB_PESS_PESSOA PESS,
            OCS_TB_PMAT_MATRICULA MAT
            WHERE
            MAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
            MAT.PMAT_CD_MATRICULA = PUPE.PUPE_CD_MATRICULA AND
            PUPE.PUPE_ID_UNIDADE_PERFIL = UNPE.UNPE_ID_UNIDADE_PERFIL AND
            UNPE.UNPE_SG_SECAO = '$arrayParams[0]' AND
            UNPE.UNPE_CD_LOTACAO = $arrayParams[1] AND
            MAT.PMAT_CD_MATRICULA = '$matricula' AND
            UNPE.UNPE_ID_PERFIL = PERF.PERF_ID_PERFIL
        ");
        $dados_antigos = $stmt_antigos->fetchAll();

        /**
         * Excluindo
         */
        $stmt = $db->query("
            DELETE FROM OCS_TB_PUPE_PERFIL_UNID_PESSOA 
            WHERE 
            PUPE_CD_MATRICULA = '$matricula' AND
            PUPE_ID_UNIDADE_PERFIL IN (
                SELECT PUPE_ID_UNIDADE_PERFIL
                FROM
                OCS_TB_PERF_PERFIL PERF,
                OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                OCS_TB_PESS_PESSOA PESS,
                OCS_TB_PMAT_MATRICULA MAT
                WHERE
                MAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
                MAT.PMAT_CD_MATRICULA = PUPE.PUPE_CD_MATRICULA AND
                PUPE.PUPE_ID_UNIDADE_PERFIL = UNPE.UNPE_ID_UNIDADE_PERFIL AND
                UNPE.UNPE_SG_SECAO = '$arrayParams[0]' AND
                UNPE.UNPE_CD_LOTACAO = $arrayParams[1] AND
                MAT.PMAT_CD_MATRICULA = '$matricula' AND
                UNPE.UNPE_ID_PERFIL = PERF.PERF_ID_PERFIL
            )
        ");

        try {
            /**
             * Executa a exclusão
             */
            $stmt->execute();
            foreach ($dados_antigos as $antigos) {
                /**
                 * Auditoria
                 */
                $dados_audit['PUPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                $dados_audit['PUPE_CD_OPERACAO'] = 'E';
                $dados_audit['PUPE_CD_MATRICULA_OPERACAO'] = $matricula_sessao;
                $dados_audit['PUPE_CD_MAQUINA_OPERACAO'] = 'SERVIDOR_WEB';
                $dados_audit['PUPE_CD_USUARIO_SO'] = 'SERVIDOR_WEB';
                $dados_audit['OLD_PUPE_ID_UNIDADE_PERFIL'] = $antigos['PUPE_ID_UNIDADE_PERFIL'];
                $dados_audit['NEW_PUPE_ID_UNIDADE_PERFIL'] = 0;
                $dados_audit['OLD_PUPE_CD_MATRICULA'] = $antigos['PUPE_CD_MATRICULA'];
                $dados_audit['NEW_PUPE_CD_MATRICULA'] = 0;
                $row_audit = $OcsTbPupeAudit->createRow($dados_audit);
                $row_audit->save();
            }
            $db->commit();
            return true;
        } catch (Zend_Exception $error_string) {
            $db->rollback();
            return false;
        }
    }

    /**
     * Busca as pessoas que tem perfil de uma Unidade
     * @param array $arrayUnidade
     * @return type
     */
    public function getPessoasDaUnidade(array $arrayUnidade) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT DISTINCT PNAT.PNAT_NO_PESSOA, PMAT.PMAT_CD_MATRICULA
                            FROM
                            OCS_TB_PMAT_MATRICULA PMAT,
                            OCS_TB_PESS_PESSOA PESS,
                            OCS_TB_PERF_PERFIL PERF,
                            OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                            OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                            OCS_TB_PNAT_PESSOA_NATURAL PNAT
                            WHERE
                            PMAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
                            PESS.PESS_ID_PESSOA = PNAT.PNAT_ID_PESSOA AND
                            PUPE.PUPE_CD_MATRICULA = PMAT.PMAT_CD_MATRICULA AND
                            PUPE.PUPE_ID_UNIDADE_PERFIL = UNPE.UNPE_ID_UNIDADE_PERFIL AND
                            UNPE.UNPE_ID_PERFIL = PERF.PERF_ID_PERFIL AND
                            UNPE.UNPE_SG_SECAO = '$arrayUnidade[0]' AND
                            UNPE.UNPE_CD_LOTACAO = $arrayUnidade[1]
                            ORDER BY PNAT_NO_PESSOA
                           ");
        return $stmt->fetchAll();
    }

    /**
     * Desvincula determinados perfis de uma pessoa em uma Unidade
     * @param type $arrayPerfis - perfis a serem desvinculados
     * @param type $dadosUnidade - dados da unidade
     * @param type $matricula - matricula do usuário
     * @param type $matricula_sessao - matricula de quem fizer a ação
     * @return boolean - retorna true ou false para a operação de desvinculação
     * @author Daniel Rodrigues
     */
    public function desassociarPerfilPessoaUnidade($arrayPerfis, $dadosUnidade, $matricula, $matricula_sessao) {

        /**
         * Tabela Perfil Unidade Pessoa Auditoria
         */
        $OcsTbPupeAudit = new Application_Model_DbTable_OcsTbPupeAuditoria();

        $arrayParams[0] = $dadosUnidade['LOTA_SIGLA_SECAO'];
        $arrayParams[1] = $dadosUnidade['LOTA_COD_LOTACAO'];

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();

        try {
            foreach ($arrayPerfis as $perfil) {

                /**
                 * Pegando dados antigos para auditoria
                 */
                $stmt_antigos = $db->query("
                    SELECT PUPE_ID_UNIDADE_PERFIL, PUPE_CD_MATRICULA
                    FROM
                    OCS_TB_PERF_PERFIL PERF,
                    OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                    OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                    OCS_TB_PESS_PESSOA PESS,
                    OCS_TB_PMAT_MATRICULA MAT
                    WHERE
                    MAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
                    MAT.PMAT_CD_MATRICULA = PUPE.PUPE_CD_MATRICULA AND
                    PUPE.PUPE_ID_UNIDADE_PERFIL = UNPE.UNPE_ID_UNIDADE_PERFIL AND
                    UNPE.UNPE_SG_SECAO = '$arrayParams[0]' AND
                    UNPE.UNPE_CD_LOTACAO = $arrayParams[1] AND
                    MAT.PMAT_CD_MATRICULA = '$matricula' AND
                    UNPE.UNPE_ID_PERFIL = PERF.PERF_ID_PERFIL AND
                    PERF.perf_id_perfil = $perfil
                ");

                $dados_antigos = $stmt_antigos->fetch();

                /**
                 * Excluindo
                 */
                $stmt_exc = $db->query("
                    DELETE FROM OCS_TB_PUPE_PERFIL_UNID_PESSOA 
                    WHERE 
                    PUPE_CD_MATRICULA = '$matricula' AND
                    PUPE_ID_UNIDADE_PERFIL IN (
                        SELECT PUPE_ID_UNIDADE_PERFIL
                        FROM
                        OCS_TB_PERF_PERFIL PERF,
                        OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                        OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                        OCS_TB_PESS_PESSOA PESS,
                        OCS_TB_PMAT_MATRICULA MAT
                        WHERE
                        MAT.PMAT_ID_PESSOA = PESS.PESS_ID_PESSOA AND
                        MAT.PMAT_CD_MATRICULA = PUPE.PUPE_CD_MATRICULA AND
                        PUPE.PUPE_ID_UNIDADE_PERFIL = UNPE.UNPE_ID_UNIDADE_PERFIL AND
                        UNPE.UNPE_SG_SECAO = '$arrayParams[0]' AND
                        UNPE.UNPE_CD_LOTACAO = $arrayParams[1] AND
                        MAT.PMAT_CD_MATRICULA = '$matricula' AND
                        UNPE.UNPE_ID_PERFIL = PERF.PERF_ID_PERFIL AND
                        PERF.perf_id_perfil = $perfil
                        )
                    ");
                $stmt_exc->execute();

                /**
                 * Auditoria
                 */
                $dados_audit['PUPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                $dados_audit['PUPE_CD_OPERACAO'] = 'E';
                $dados_audit['PUPE_CD_MATRICULA_OPERACAO'] = $matricula_sessao;
                $dados_audit['PUPE_CD_MAQUINA_OPERACAO'] = 'SERVIDOR_WEB';
                $dados_audit['PUPE_CD_USUARIO_SO'] = 'SERVIDOR_WEB';
                $dados_audit['OLD_PUPE_ID_UNIDADE_PERFIL'] = $dados_antigos['PUPE_ID_UNIDADE_PERFIL'];
                $dados_audit['NEW_PUPE_ID_UNIDADE_PERFIL'] = 0;
                $dados_audit['OLD_PUPE_CD_MATRICULA'] = $dados_antigos['PUPE_CD_MATRICULA'];
                $dados_audit['NEW_PUPE_CD_MATRICULA'] = 0;
                $row_audit = $OcsTbPupeAudit->createRow($dados_audit);
                $row_audit->save();
            }//foreach

            $db->commit();
            return true;
        } catch (Zend_Exception $error_string) {
            $db->rollback();
            return false;
        }
    }

    /*
     * Retorna todos os perfis associados a uma pessoa, independente da Unidade
     */
    public function getTodosPerfilPessoa($matricula){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PERF_ID_PERFIL, PERF_DS_PERFIL FROM 
                            OCS_TB_UNPE_UNIDADE_PERFIL, OCS_TB_PERF_PERFIL 
                            WHERE
                            UNPE_ID_PERFIL = PERF_ID_PERFIL AND
                            UNPE_ID_UNIDADE_PERFIL IN(
                                SELECT 
                                PUPE_ID_UNIDADE_PERFIL
                                FROM
                                OCS_TB_PUPE_PERFIL_UNID_PESSOA WHERE
                                PUPE_CD_MATRICULA = '$matricula')
                            ORDER BY PERF_ID_PERFIL ASC
                           ");
        return $stmt->fetchAll();
    }
    
    /*
     * Retorna todos os perfil DSV e-admin
     */
    public function getPerfilDSV($matricula){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PERF_ID_PERFIL, PERF_DS_PERFIL FROM 
                            OCS_TB_UNPE_UNIDADE_PERFIL, OCS_TB_PERF_PERFIL 
                            WHERE
                            UNPE_ID_PERFIL = PERF_ID_PERFIL AND
                            UNPE_ID_UNIDADE_PERFIL IN(
                                SELECT 
                                PUPE_ID_UNIDADE_PERFIL
                                FROM
                                OCS_TB_PUPE_PERFIL_UNID_PESSOA WHERE
                                PUPE_CD_MATRICULA = '$matricula')
                            AND PERF_ID_PERFIL = 8
                            ORDER BY PERF_ID_PERFIL ASC
                           ");
        return $stmt->fetchAll();
    }
    
    public function getPerfilGestao($matricula){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PERF_ID_PERFIL, PERF_DS_PERFIL FROM 
                            OCS_TB_UNPE_UNIDADE_PERFIL, OCS_TB_PERF_PERFIL 
                            WHERE
                            UNPE_ID_PERFIL = PERF_ID_PERFIL AND
                            UNPE_ID_UNIDADE_PERFIL IN(
                                SELECT 
                                PUPE_ID_UNIDADE_PERFIL
                                FROM
                                OCS_TB_PUPE_PERFIL_UNID_PESSOA WHERE
                                PUPE_CD_MATRICULA = '$matricula')
                            AND PERF_ID_PERFIL IN ('31','39')
                            ORDER BY PERF_ID_PERFIL ASC
                           ");
        return $stmt->fetchAll();
    }
    
}