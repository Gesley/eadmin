<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_OcsTbUnpeUnidadePerfil extends Zend_Db_Table_Abstract {

    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_UNPE_UNIDADE_PERFIL';
    protected $_primary = 'UNPE_ID_UNIDADE_PERFIL';
    protected $_sequence = 'OCS_SQ_UNPE_UN_PERFIL';

    public function getSistemas() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  NOME_SISTEMA, DS_NOME_SISTEMA
               			FROM  SISTEMAS_TRF
                		WHERE NOME_SISTEMA  = 'SOSTI' 
                                OR NOME_SISTEMA ='SISAD'");
        return $stmt->fetchAll();
    }

    public function getPessoa($lota_cod_lotacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PMAT_ID_PESSOA,  B.PNAT_NO_PESSOA, A.PMAT_CD_MATRICULA 
					FROM OCS_TB_PMAT_MATRICULA A, OCS_TB_PNAT_PESSOA_NATURAL B
					WHERE A.PMAT_CD_UNIDADE_LOTACAO = $lota_cod_lotacao
					AND  A.PMAT_ID_PESSOA = B.PNAT_ID_PESSOA
					ORDER BY B.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getUnidade($lota_cod_lotacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_SIGLA_LOTACAO, LOTA_DSC_LOTACAO, LOTA_COD_LOTACAO 
						FROM  RH_CENTRAL_LOTACAO
						ORDER BY 1");
        return $stmt->fetchAll();
    }

    public function getPerfisCriados() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PERF_ID_PERFIL, PERF_DS_PERFIL
                                   FROM OCS_TB_PERF_PERFIL");
        return $stmt->fetchAll();
    }

    public function getPerfisAssociados($cod_lotacao, $sg_secao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  UNPE_ID_UNIDADE_PERFIL,UNPE_SG_SECAO,UNPE_CD_LOTACAO,UNPE_ID_PERFIL,PERF_DS_PERFIL
                                FROM  OCS_TB_UNPE_UNIDADE_PERFIL A, OCS_TB_PERF_PERFIL B
                                WHERE A.UNPE_ID_PERFIL = B.PERF_ID_PERFIL
                                AND A.UNPE_CD_LOTACAO = $cod_lotacao
                                AND A.UNPE_SG_SECAO = '$sg_secao' ORDER BY PERF_DS_PERFIL");
        return $stmt->fetchAll();
    }

    public function getPerfisNaoAssociados($cod_lotacao, $sg_secao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT PERF_ID_PERFIL, PERF_DS_PERFIL
                                FROM OCS_TB_PERF_PERFIL
                                WHERE PERF_ID_PERFIL NOT IN (
                                SELECT  UNPE_ID_PERFIL
                                FROM  OCS_TB_UNPE_UNIDADE_PERFIL A, OCS_TB_PERF_PERFIL B
                                WHERE A.UNPE_ID_PERFIL = B.PERF_ID_PERFIL
                                AND A.UNPE_CD_LOTACAO = $cod_lotacao
                                AND A.UNPE_SG_SECAO = '$sg_secao') ORDER BY PERF_DS_PERFIL"
        );
        return $stmt->fetchAll();
    }

    public function getResponsávelCaixaUnidade($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT RHLOTA.LOTA_COD_LOTACAO, 
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
                              AND   PUPE.PUPE_CD_MATRICULA = '$matricula'
                              AND   PERF_ID_PERFIL = 9 /*RESPONSÁVEL PELA CAIXA DA UNIDADE*/
                              ORDER BY LOTA_DSC_LOTACAO
                           ");
        return $stmt->fetchAll();
    }

    public function getResponsavelCaixaUnidadePessoal($matricula) {
        if (is_string($matricula)) {
            $condicao1 = " PUPE.PUPE_CD_MATRICULA = '$matricula' ";
            $condicao2 = " PMAT.PMAT_CD_MATRICULA = '$matricula'";
        } else if (is_array($matricula)) {
            foreach ($matricula as $valor) {
                $matricula = explode("-", $valor['value']);
                $arrayMatricula[] = "'" . $matricula[0] . "'";
            }
            $arrayMatricula = implode(",", $arrayMatricula);
            $condicao1 = " PUPE.PUPE_CD_MATRICULA IN ($arrayMatricula)";
            $condicao2 = " PMAT.PMAT_CD_MATRICULA IN ($arrayMatricula)";
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = " SELECT RHLOTA.LOTA_COD_LOTACAO, 
                                    RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO,RHLOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO, 
                                    RHLOTA.LOTA_SIGLA_LOTACAO,
                                    RHLOTA.LOTA_SIGLA_SECAO 
                              FROM  OCS_TB_PERF_PERFIL  PERF,
                                    OCS_TB_UNPE_UNIDADE_PERFIL UNPE,
                                    OCS_TB_PUPE_PERFIL_UNID_PESSOA PUPE,
                                     RH_CENTRAL_LOTACAO RHLOTA
                              WHERE PERF.PERF_ID_PERFIL = UNPE.UNPE_ID_PERFIL
                              AND   UNPE.UNPE_ID_UNIDADE_PERFIL = PUPE.PUPE_ID_UNIDADE_PERFIL
                              AND   UNPE_SG_SECAO   =  RHLOTA.LOTA_SIGLA_SECAO
                              AND   UNPE_CD_LOTACAO =  RHLOTA.LOTA_COD_LOTACAO
                              AND   $condicao1
                              AND   PERF_ID_PERFIL = 9 
                              UNION
                              SELECT RHLOTA.LOTA_COD_LOTACAO, 
                                    RH_DESCRICAO_CENTRAL_LOTACAO(RHLOTA.LOTA_SIGLA_SECAO,RHLOTA.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO, 
                                    RHLOTA.LOTA_SIGLA_LOTACAO,
                                    RHLOTA.LOTA_SIGLA_SECAO 
                             FROM   OCS_TB_PMAT_MATRICULA PMAT
                         INNER JOIN RH_CENTRAL_LOTACAO RHLOTA
                                 ON RHLOTA.LOTA_COD_LOTACAO = PMAT.PMAT_CD_UNIDADE_LOTACAO 
                                AND RHLOTA.LOTA_SIGLA_SECAO = PMAT.PMAT_SG_SECSUBSEC_LOTACAO
                              WHERE $condicao2
                           ORDER BY LOTA_DSC_LOTACAO";

        //Zend_Debug::dump($query);        exit;  
        $stmt = $db->query($query);
        return $stmt->fetchAll();
    }

    public function getPessoasComPerfilX($idPerfil, $siglasecao, $codlotacao, $order = 'PNAT_NO_PESSOA') {
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
                              AND   UNPE_SG_SECAO = '$siglasecao'
                              AND   UNPE_CD_LOTACAO = $codlotacao
                              AND   PERF_ID_PERFIL = $idPerfil 
                              ORDER BY $order
                           ");
        return $stmt->fetchAll();
    }

    /**
     * @author Daniel Rodrigues
     * @param CD LOTAÇÃO
     * @return ARRAY ou NULL
     */
    public function getPerfisAssociadosaUnidade(array $unidade) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $array_perfis_assoc = null;
        $arrayParams[0] = $unidade['UNPE_CD_LOTACAO'];
        $arrayParams[1] = $unidade['UNPE_SG_SECAO'];

        $stmt = $db->query("SELECT UNPE_ID_PERFIL
                                FROM  OCS_TB_UNPE_UNIDADE_PERFIL
                                WHERE 
                                UNPE_CD_LOTACAO = $arrayParams[0] AND UNPE_SG_SECAO = '$arrayParams[1]'");
        $resultado_perfis = $stmt->fetchAll();

        if (!is_null($resultado_perfis)) {
            foreach ($resultado_perfis as $perfis_assoc) {
                $array_perfis_assoc[] = $perfis_assoc['UNPE_ID_PERFIL'];
            }
        }
        return $array_perfis_assoc;
    }

    /**
     * Função faz a associação de novos perfis à Unidade
     * @author Daniel Rodrigues
     * @param array $perfis - Novos perfis
     * @param array $dadosUnidade - Unidade a ser alterada
     * @param type $matricula - Matricula do usuário
     * @return boolean 
     */
    public function associarPerfisaUnidade(array $perfis, array $dadosUnidade, $matricula) {

        $table = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $table_audit = new Application_Model_DbTable_OcsTbUnpeAuditoria();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            foreach ($perfis as $UNPE_ID_PERFIL) {
                /**
                 * Obtem os dados necessários para inserir na tabela Unidade Perfil
                 */
                $dadosUnidadePerfil['UNPE_SG_SECAO'] = $dadosUnidade['UNPE_SG_SECAO'];
                $dadosUnidadePerfil['UNPE_CD_LOTACAO'] = $dadosUnidade['UNPE_CD_LOTACAO'];
                $dadosUnidadePerfil['UNPE_ID_PERFIL'] = $UNPE_ID_PERFIL;
                $row = $table->createRow($dadosUnidadePerfil);
                $row->save();
                /**
                 * Auditoria
                 */
                $data_audit['UNPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                $data_audit['UNPE_CD_OPERACAO'] = 'I';
                $data_audit['UNPE_CD_MATRICULA_OPERACAO'] = $matricula;
                $data_audit['UNPE_CD_MAQUINA_OPERACAO'] = 'NOME_MAQUINA';
                $data_audit['UNPE_CD_USUARIO_SO'] = 'NOME_USER_SO';
                $data_audit['OLD_UNPE_ID_UNIDADE_PERFIL'] = 0;
                $data_audit['NEW_UNPE_ID_UNIDADE_PERFIL'] = $row->save();
                $data_audit['OLD_UNPE_SG_SECAO'] = 0;
                $data_audit['NEW_UNPE_SG_SECAO'] = $dadosUnidadePerfil['UNPE_SG_SECAO'];
                $data_audit['OLD_UNPE_CD_LOTACAO'] = 0;
                $data_audit['NEW_UNPE_CD_LOTACAO'] = $dadosUnidadePerfil['UNPE_CD_LOTACAO'];
                $data_audit['OLD_UNPE_ID_PERFIL'] = 0;
                $data_audit['NEW_UNPE_ID_PERFIL'] = $dadosUnidadePerfil['UNPE_ID_PERFIL'];
                $row_audit = $table_audit->createRow($data_audit);
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
     * Função para desvincular TODOS os perfis de Determinada Unidade
     * @param array $dadosUnidade - Dados da unidade a ser alterada
     * @param type $matricula - Matricula do usuário
     * @return boolean
     */
    public function desassociarTodosPerfisDaUnidade(array $dadosUnidade, $matricula) {

        $table_audit = new Application_Model_DbTable_OcsTbUnpeAuditoria();
        $arrayParams[0] = $dadosUnidade['UNPE_CD_LOTACAO'];
        $arrayParams[1] = $dadosUnidade['UNPE_SG_SECAO'];

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        /**
         * Busca os dados antes da exclusao
         */
        $stmt_antigos = $db->query("SELECT * FROM OCS_TB_UNPE_UNIDADE_PERFIL
                                WHERE UNPE_CD_LOTACAO = $arrayParams[0] AND UNPE_SG_SECAO = '$arrayParams[1]'");
        $dados_antigos = $stmt_antigos->fetchAll();

        $stmt = $db->query("DELETE FROM OCS_TB_UNPE_UNIDADE_PERFIL 
            WHERE UNPE_CD_LOTACAO = $arrayParams[0] AND UNPE_SG_SECAO = '$arrayParams[1]'");

        try {
            /**
             * Executa a exclusão
             */
            $stmt->execute();
            foreach ($dados_antigos as $antigos) {
                /**
                 * Auditoria
                 */
                $data_audit['UNPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                $data_audit['UNPE_CD_OPERACAO'] = 'E';
                $data_audit['UNPE_CD_MATRICULA_OPERACAO'] = $matricula;
                $data_audit['UNPE_CD_MAQUINA_OPERACAO'] = 'NOME_MAQUINA';
                $data_audit['UNPE_CD_USUARIO_SO'] = 'NOME_USER_SO';
                $data_audit['OLD_UNPE_ID_UNIDADE_PERFIL'] = $antigos["UNPE_ID_UNIDADE_PERFIL"];
                $data_audit['NEW_UNPE_ID_UNIDADE_PERFIL'] = 0;
                $data_audit['OLD_UNPE_SG_SECAO'] = $antigos["UNPE_SG_SECAO"];
                $data_audit['NEW_UNPE_SG_SECAO'] = 0;
                $data_audit['OLD_UNPE_CD_LOTACAO'] = $antigos["UNPE_CD_LOTACAO"];
                $data_audit['NEW_UNPE_CD_LOTACAO'] = 0;
                $data_audit['OLD_UNPE_ID_PERFIL'] = $antigos["UNPE_ID_PERFIL"];
                $data_audit['NEW_UNPE_ID_PERFIL'] = 0;
                $row_audit = $table_audit->createRow($data_audit);
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
     * Função que desvincula DETERMINADOS perfis da Unidade
     * @param array $perfis - perfis a serem desvinculados
     * @param array $dadosUnidade - Dados da Unidade
     * @param type $matricula - Matricula do Usuário
     * @return boolean
     */
    public function desassociarPerfisDaUnidade(array $perfis, array $dadosUnidade, $matricula) {

        //$table = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $table_audit = new Application_Model_DbTable_OcsTbUnpeAuditoria();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {

            /**
             * Obtem os dados necessários para excluir na tabela Unidade Perfil
             */
            $arrayParams[0] = $dadosUnidade['UNPE_CD_LOTACAO'];
            $arrayParams[1] = $dadosUnidade['UNPE_SG_SECAO'];

            foreach ($perfis as $UNPE_ID_PERFIL) {
                /**
                 * Busca os dados antes da exclusao
                 */
                $stmt_antigos = $db->query("SELECT * FROM OCS_TB_UNPE_UNIDADE_PERFIL
                                WHERE UNPE_CD_LOTACAO = $arrayParams[0] AND UNPE_SG_SECAO = '$arrayParams[1]' AND UNPE_ID_PERFIL = $UNPE_ID_PERFIL");
                $dados_antigos = $stmt_antigos->fetch();

                /**
                 * Faz a exclusão
                 */
                $stmt = $db->query("DELETE FROM OCS_TB_UNPE_UNIDADE_PERFIL 
                WHERE UNPE_CD_LOTACAO = $arrayParams[0] AND UNPE_SG_SECAO = '$arrayParams[1]' AND UNPE_ID_PERFIL = $UNPE_ID_PERFIL");
                $stmt->execute();
                /**
                 * Auditoria
                 */
                $data_audit['UNPE_TS_OPERACAO'] = new Zend_Db_Expr("SYSDATE");
                $data_audit['UNPE_CD_OPERACAO'] = 'E';
                $data_audit['UNPE_CD_MATRICULA_OPERACAO'] = $matricula;
                $data_audit['UNPE_CD_MAQUINA_OPERACAO'] = 'NOME_MAQUINA';
                $data_audit['UNPE_CD_USUARIO_SO'] = 'NOME_USER_SO';
                $data_audit['OLD_UNPE_ID_UNIDADE_PERFIL'] = $dados_antigos["UNPE_ID_UNIDADE_PERFIL"];
                $data_audit['NEW_UNPE_ID_UNIDADE_PERFIL'] = 0;
                $data_audit['OLD_UNPE_SG_SECAO'] = $dados_antigos["UNPE_SG_SECAO"];
                $data_audit['NEW_UNPE_SG_SECAO'] = 0;
                $data_audit['OLD_UNPE_CD_LOTACAO'] = $dados_antigos["UNPE_CD_LOTACAO"];
                $data_audit['NEW_UNPE_CD_LOTACAO'] = 0;
                $data_audit['OLD_UNPE_ID_PERFIL'] = $dados_antigos["UNPE_ID_PERFIL"];
                $data_audit['NEW_UNPE_ID_PERFIL'] = 0;
                $row_audit = $table_audit->createRow($data_audit);
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
     * Busca o ID da associação entre perfil e unidade, para vincular a um usuário
     * @param type $unpe_sg_secao
     * @param type $unpe_cd_lotacao
     * @param type $unpe_id_perfil
     * @return array
     */
    public function getPerfisUnidade($unpe_sg_secao, $unpe_cd_lotacao, $unpe_id_perfil) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt_antigos = $db->query("           
                SELECT UNPE_ID_UNIDADE_PERFIL 
                FROM 
                OCS_TB_UNPE_UNIDADE_PERFIL 
                WHERE 
                UNPE_SG_SECAO = '$unpe_sg_secao' AND 
                UNPE_CD_LOTACAO = $unpe_cd_lotacao AND 
                UNPE_ID_PERFIL = $unpe_id_perfil
        ");

        return $dados_antigos = $stmt_antigos->fetch();
    }
    
    /**
     * Lista as caixas que foram extintas que pertencem a um atendente pela matrícula do
     * atendente
     * @param type $matricula
     * @return type array()
     */
    public function getResponsavelCaixaUnidadeExtinta($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query(" SELECT RHLOTA.LOTA_COD_LOTACAO, 
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
                              AND   PUPE.PUPE_CD_MATRICULA = '$matricula'
                              AND   RHLOTA.LOTA_DAT_FIM IS NOT NULL
                              AND   PERF_ID_PERFIL = 9 /*RESPONSÁVEL PELA CAIXA DA UNIDADE*/
                              ORDER BY LOTA_DSC_LOTACAO
                           ");
        return $stmt->fetchAll();
    }

}