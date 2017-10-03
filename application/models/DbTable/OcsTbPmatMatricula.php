<?php

class Application_Model_DbTable_OcsTbPmatMatricula extends Zend_Db_Table_Abstract {

    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_PMAT_MATRICULA';
    protected $_primary = 'PMAT_CD_MATRICULA';

    public function getPessoa($siglasecao, $codlotacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PMAT_ID_PESSOA,  B.PNAT_NO_PESSOA, A.PMAT_CD_MATRICULA 
					FROM OCS_TB_PMAT_MATRICULA A, OCS_TB_PNAT_PESSOA_NATURAL B
					WHERE A.PMAT_CD_UNIDADE_LOTACAO = $codlotacao
                                        AND PMAT_SG_SECSUBSEC_LOTACAO = '$siglasecao'
					AND  A.PMAT_ID_PESSOA = B.PNAT_ID_PESSOA
                                        AND A.PMAT_DT_FIM IS NULL
					ORDER BY B.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getDadosNomeSolicitante($nome) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT T.PFON_CD_DDD||'-'||T.PFON_NR_TELEFONE TELEFONE, E.PEEM_ED_EMAIL,
                                       C.LOTA_ANDAR||' - '||C.LOTA_SIGLA_LOTACAO LOCALIZACAO, P.PNAT_NO_PESSOA AS label, 
                                       P.PNAT_NR_CPF, C.LOTA_COD_LOTACAO, C.LOTA_DSC_LOTACAO, C.LOTA_SIGLA_LOTACAO,
                                       C.LOTA_SIGLA_SECAO
                            FROM   OCS_TB_PMAT_MATRICULA M
                            LEFT JOIN OCS_TB_PFON_TELEFONE_PESSOA T
                            ON T.PFON_ID_PESSOA = M.PMAT_ID_PESSOA
                            LEFT JOIN OCS_TB_PEEM_EMAIL_PESSOA E
                            ON E.PEEM_ID_PESSOA = M.PMAT_ID_PESSOA
                            INNER JOIN RH_CENTRAL_LOTACAO C
                            ON C.LOTA_COD_LOTACAO = M.PMAT_CD_UNIDADE_LOTACAO 
                            AND C.LOTA_SIGLA_SECAO = M.PMAT_SG_SECSUBSEC_LOTACAO 
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE P.PNAT_NO_PESSOA LIKE UPPER('%$nome%')
                            AND        M.PMAT_DT_FIM IS NULL
                            ORDER BY P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getDadosPessoaisAjax($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT T.PFON_CD_DDD||T.PFON_NR_TELEFONE TELEFONE, E.PEEM_ED_EMAIL EMAIL,
                                   C.LOTA_ANDAR||' - '||C.LOTA_SIGLA_LOTACAO LOCALIZACAO, P.PNAT_NO_PESSOA, 
                                   C.LOTA_SIGLA_LOTACAO||' - '||C.LOTA_DSC_LOTACAO||' - '||C.LOTA_COD_LOTACAO||' - '||C.LOTA_SIGLA_SECAO AS UNIDADE,
                                   C.LOTA_SIGLA_SECAO AS SECAO, M.PMAT_CD_MATRICULA AS MATRICULA
                            FROM   OCS_TB_PMAT_MATRICULA M
                            LEFT JOIN OCS_TB_PFON_TELEFONE_PESSOA T
                            ON T.PFON_ID_PESSOA = M.PMAT_ID_PESSOA
                            LEFT JOIN OCS_TB_PEEM_EMAIL_PESSOA E
                            ON E.PEEM_ID_PESSOA = M.PMAT_ID_PESSOA
                            INNER JOIN RH_CENTRAL_LOTACAO C
                            ON C.LOTA_COD_LOTACAO = M.PMAT_CD_UNIDADE_LOTACAO AND 
                               C.LOTA_SIGLA_SECAO = M.PMAT_SG_SECSUBSEC_LOTACAO
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE M.PMAT_CD_MATRICULA = '$matricula'
                            AND   M.PMAT_DT_FIM IS NULL
                            AND   C.LOTA_DAT_FIM IS NULL");
        return $stmt->fetchAll();
    }

    public function getNomeSolicitanteAjax($matriculanome, $sigla = null, $secao_subsecao_unidade = null, $ativo = true) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt  = "SELECT M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL\n";
        $stmt .= "FROM OCS_TB_PMAT_MATRICULA M\n";
        $stmt .= "INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P\n";
        $stmt .= "ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA\n";
        $stmt .= "WHERE M.PMAT_CD_MATRICULA||P.PNAT_NO_PESSOA LIKE UPPER('%$matriculanome%')\n";

        if($ativo == true)
            $stmt .= "AND M.PMAT_DT_FIM IS NULL\n";

        if(!empty($secao_subsecao_unidade) && !empty($sigla)){
            $stmt .= "AND pmat_cd_unidade_lotacao in (\n";
            $stmt .= "\tSELECT\n";
            $stmt .= "\tLOTA_COD_LOTACAO\n";
            $stmt .= "\tFROM\n";
            $stmt .= "\t(\n";
                $stmt .= "\t\tSELECT *\n";
                $stmt .= "\t\tFROM RH_CENTRAL_LOTACAO\n";
                $stmt .= "\t\tWHERE lota_sigla_secao = '$sigla' \n";
            $stmt .= "\t)\n";
            $stmt .= "\tWHERE LOTA_DAT_FIM IS NULL \n";
            $stmt .= "\tCONNECT BY PRIOR lota_cod_lotacao = lota_lota_cod_lotacao_pai\n";
            $stmt .= "\tAND lota_tipo_lotacao > 2 \n";
            $stmt .= "\tSTART WITH lota_cod_lotacao = $secao_subsecao_unidade\n";
            $stmt .= ")";
        }
        $stmt .= "ORDER BY P.PNAT_NO_PESSOA\n";
        $stmt = $db->query($stmt);
        return $stmt->fetchAll();
    }

    /**
     * Buscar as Pessoas lotadas em uma Seção Judiciária pela SIGLA
     * @param String $matriculanome
     * @param String $sg
     * @return Array
     */
    public function getNomeSolicitanteSecaoAjax($matriculanome, $sg) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL 
                            FROM       OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE      M.PMAT_CD_MATRICULA||P.PNAT_NO_PESSOA LIKE UPPER('%$matriculanome%')
                            AND        M.PMAT_DT_FIM IS NULL
                            AND        PMAT_SG_SECSUBSEC_LOTACAO = '$sg'
                            ORDER BY   P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getNomeMatriculaSolicitanteAjax($nomematricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     P.PNAT_NO_PESSOA||' - '||M.PMAT_CD_MATRICULA AS LABEL 
                            FROM       OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE      M.PMAT_CD_MATRICULA||P.PNAT_NO_PESSOA LIKE UPPER('%$nomematricula%')
                            AND        M.PMAT_DT_FIM IS NULL
                            ORDER BY   M.PMAT_CD_MATRICULA");
        return $stmt->fetchAll();
    }

    public function getUnidadeSolicitanteAjax($matriculaSolicitante) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT LOTA_SIGLA_LOTACAO||' - '||LOTA_DSC_LOTACAO||' - '||LOTA_COD_LOTACAO
                            FROM   OCS_TB_PMAT_MATRICULA M
                            INNER JOIN RH_CENTRAL_LOTACAO C
                            ON C.LOTA_COD_LOTACAO = M.PMAT_CD_UNIDADE_LOTACAO 
                            AND C.LOTA_SIGLA_SECAO = M.PMAT_SG_SECSUBSEC_LOTACAO 
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE M.PMAT_CD_MATRICULA = UPPER('$matriculaSolicitante')
                            AND   M.PMAT_DT_FIM IS NULL
                            AND   C.LOTA_DAT_FIM IS NULL
                            ORDER BY C.LOTA_SIGLA_LOTACAO");
        return $stmt->fetchAll();
    }

    public function getDadosMatriculaSolicitante($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT T.PFON_CD_DDD||'-'||T.PFON_NR_TELEFONE TELEFONE, E.PEEM_ED_EMAIL,
                                       C.LOTA_ANDAR||' - '||C.LOTA_SIGLA_LOTACAO LOCALIZACAO, P.PNAT_NO_PESSOA, 
                                       P.PNAT_NR_CPF, C.LOTA_COD_LOTACAO, C.LOTA_DSC_LOTACAO, C.LOTA_SIGLA_LOTACAO,
                                       C.LOTA_SIGLA_SECAO
                            FROM   OCS_TB_PMAT_MATRICULA M
                            LEFT JOIN OCS_TB_PFON_TELEFONE_PESSOA T
                            ON T.PFON_ID_PESSOA = M.PMAT_ID_PESSOA
                            LEFT JOIN OCS_TB_PEEM_EMAIL_PESSOA E
                            ON E.PEEM_ID_PESSOA = M.PMAT_ID_PESSOA
                            INNER JOIN RH_CENTRAL_LOTACAO C
                            ON C.LOTA_COD_LOTACAO = M.PMAT_CD_UNIDADE_LOTACAO 
                            AND C.LOTA_SIGLA_SECAO = M.PMAT_SG_SECSUBSEC_LOTACAO 
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE M.PMAT_CD_MATRICULA = UPPER('$matricula')
                            AND        M.PMAT_DT_FIM IS NULL
                            ORDER BY P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getMatriculaSolicitanteAjax($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  P.PNAT_NO_PESSOA AS LABEL 
                            FROM   OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE M.PMAT_CD_MATRICULA = UPPER('$matricula')
                            AND        M.PMAT_DT_FIM IS NULL
                            ORDER BY P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getPessoaPorLotacao($lotacao) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT T.PFON_CD_DDD||T.PFON_NR_TELEFONE TELEFONE, E.PEEM_ED_EMAIL EMAIL,
                                   C.LOTA_ANDAR||' - '||C.LOTA_SIGLA_LOTACAO LOCALIZACAO, P.PNAT_NO_PESSOA, 
                                   C.LOTA_SIGLA_LOTACAO||' - '||C.LOTA_DSC_LOTACAO||' - '||C.LOTA_COD_LOTACAO||' - '||C.LOTA_SIGLA_SECAO AS UNIDADE,
                                   C.LOTA_SIGLA_SECAO AS SECAO, M.PMAT_CD_MATRICULA AS MATRICULA
                            FROM   OCS_TB_PMAT_MATRICULA M
                            LEFT JOIN OCS_TB_PFON_TELEFONE_PESSOA T
                            ON T.PFON_ID_PESSOA = M.PMAT_ID_PESSOA
                            LEFT JOIN OCS_TB_PEEM_EMAIL_PESSOA E
                            ON E.PEEM_ID_PESSOA = M.PMAT_ID_PESSOA
                            INNER JOIN RH_CENTRAL_LOTACAO C
                            ON C.LOTA_COD_LOTACAO = M.PMAT_CD_UNIDADE_LOTACAO AND 
                               C.LOTA_SIGLA_SECAO = M.PMAT_SG_SECSUBSEC_LOTACAO
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE C.LOTA_COD_LOTACAO = $lotacao
                            AND   M.PMAT_DT_FIM IS NULL
                            AND   C.LOTA_DAT_FIM IS NULL
                            ORDER BY P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getJuizeseDesembargadores($matriculanome) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL 
                            FROM       OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE      SUBSTR(M.PMAT_CD_MATRICULA,0,2) IN ('DS','JU')
                            AND        M.PMAT_DT_FIM IS NULL
                            AND        UPPER(M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA) LIKE UPPER('%$matriculanome%')
                            ORDER BY    M.PMAT_CD_MATRICULA, P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getServidores($matriculanome) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL 
                            FROM       OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE      SUBSTR(M.PMAT_CD_MATRICULA,0,2) NOT IN ('DS','JU')
                            /*
                            	- LENGTH(TRANSLATE(TRIM(SUBSTR(M.PMAT_CD_MATRICULA,-1,1)),' 0123456789',' ')) IS NULL
                            	- Verifica o último caractere da matricula não é uma letra.
                            */
                            AND        LENGTH(TRANSLATE(TRIM(SUBSTR(M.PMAT_CD_MATRICULA,-1,1)),' 0123456789',' ')) IS NULL
                            AND        M.PMAT_DT_FIM IS NULL
                            AND        UPPER(M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA) LIKE UPPER('%$matriculanome%')
                            ORDER BY    M.PMAT_CD_MATRICULA, P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getPessoasPartes($matriculanome) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL,  M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA AS VALUE,
                                       M.PMAT_CD_MATRICULA MATRICULA,
                                       M.PMAT_ID_PESSOA AS ID
                            FROM       OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE      M.PMAT_CD_MATRICULA||P.PNAT_NO_PESSOA LIKE UPPER('%$matriculanome%')
                            AND        M.PMAT_DT_FIM IS NULL
                            ORDER BY   P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getPessoaMat($matricula) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL,  M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA AS VALUE,
                                       M.PMAT_CD_MATRICULA MATRICULA,
                                       M.PMAT_ID_PESSOA AS ID
                            FROM       OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE      M.PMAT_CD_MATRICULA LIKE UPPER('%$matricula%')
                            AND        M.PMAT_DT_FIM IS NULL
                            ORDER BY   P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getNomeUnidade($matricula) {
        if (is_string($matricula)) {
            $condicao = " M.PMAT_CD_MATRICULA = '$matricula' ";
        } else if (is_array($matricula)) {
            foreach ($matricula as $valor) {
                $matricula = explode("-", $valor['value']);
                $arrayMatricula[] = "'" . $matricula[0] . "'";
            }
            $arrayMatricula = implode(",", $arrayMatricula);
            $condicao = " M.PMAT_CD_MATRICULA IN ($arrayMatricula)";
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "SELECT C.LOTA_SIGLA_LOTACAO, 
                                   P.PNAT_NO_PESSOA, 
                                   C.LOTA_COD_LOTACAO,
                                   C.LOTA_SIGLA_LOTACAO||' - '||C.LOTA_DSC_LOTACAO||' - '||C.LOTA_COD_LOTACAO||' - '||C.LOTA_SIGLA_SECAO AS UNIDADE,
                                   C.LOTA_SIGLA_SECAO, 
                                   M.PMAT_CD_MATRICULA
                            FROM   OCS_TB_PMAT_MATRICULA M
                            INNER JOIN RH_CENTRAL_LOTACAO C
                            ON C.LOTA_COD_LOTACAO = M.PMAT_CD_UNIDADE_LOTACAO AND 
                               C.LOTA_SIGLA_SECAO = M.PMAT_SG_SECSUBSEC_LOTACAO
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE M.PMAT_DT_FIM IS NULL
                            AND   C.LOTA_DAT_FIM IS NULL
                            AND   $condicao
                            ORDER BY 2";
        $stmt = $db->query($query);
        return $stmt->fetchAll();
    }

    public function getPessoasdascaixas($unidades) {
        $arrayunidade = array();
        $cont = 0;
        foreach ($unidades as $atual) {
            $arrayunidade[$cont] = $atual['LOTA_SIGLA_SECAO'] . '|' . $atual['LOTA_COD_LOTACAO'];
            $cont++;
        }
        //1134|TR
        $arrayunidade = "'" . implode("', '", $arrayunidade) . "'";
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT A.PMAT_ID_PESSOA,  B.PNAT_NO_PESSOA, A.PMAT_CD_MATRICULA 
					FROM OCS_TB_PMAT_MATRICULA A, OCS_TB_PNAT_PESSOA_NATURAL B
					WHERE   A.PMAT_ID_PESSOA = B.PNAT_ID_PESSOA
                      AND   A.PMAT_DT_FIM IS NULL
                      AND   A.PMAT_SG_SECSUBSEC_LOTACAO||'|'||A.PMAT_CD_UNIDADE_LOTACAO IN ($arrayunidade)
               	 ORDER BY   B.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    public function getNotJuizeseDesembargadores($matriculanome) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT     M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL,  M.PMAT_CD_MATRICULA AS VALUE,
                                       M.PMAT_ID_PESSOA AS ID 
                            FROM       OCS_TB_PMAT_MATRICULA M
                            INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                            ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                            WHERE      SUBSTR(M.PMAT_CD_MATRICULA,0,2) NOT IN ('DS','JU')
                            AND        M.PMAT_DT_FIM IS NULL
                            AND        UPPER(M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA) LIKE UPPER('%$matriculanome%')
                            ORDER BY    M.PMAT_CD_MATRICULA, P.PNAT_NO_PESSOA");
        return $stmt->fetchAll();
    }

    /**
     * Verifica se a matricula existe na base de dados
     * @param String $matricula
     * @return Object
     */
    public function verificaMatricula($matricula) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "
            SELECT COUNT(*) VALOR
            FROM OCS_TB_PMAT_MATRICULA
            WHERE
            PMAT_CD_MATRICULA = '$matricula'
        ";
        $stmt = $db->query($query);
        return $stmt->fetch();
    }

    /**
     * Busca pessoas responsáveis pela caixa de uma determinada Unidade
     * @param array $unidade
     * @return array
     */
    public function getPessoasResponsaveisCaixa(array $arrayUnidade) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "
            SELECT DISTINCT PNAT.PNAT_NO_PESSOA, PMAT.PMAT_CD_MATRICULA
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
            UNPE.UNPE_CD_LOTACAO = $arrayUnidade[1] AND
            UNPE.UNPE_ID_PERFIL = 9
            ORDER BY PNAT_NO_PESSOA
        ";
        $stmt = $db->query($query);
        return $stmt->fetchAll();
    }

    /**
     * Lista as pessoas que estão cadastrdas como acompanhantes das solicitações de TI
     * @param String $idDocumento
     * @return Object
     */
    public function getAcompanhantesSolicitacao($idDocumento) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $query = "SELECT M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA  AS LABEL,  M.PMAT_CD_MATRICULA||' - '||P.PNAT_NO_PESSOA AS VALUE,
                         M.PMAT_CD_MATRICULA MATRICULA,
                         M.PMAT_ID_PESSOA AS ID
                  FROM       OCS_TB_PMAT_MATRICULA M
                  INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                  ON         M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                  INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC D
                  ON         M.PMAT_CD_MATRICULA = D.PAPD_CD_MATRICULA_INTERESSADO
                  WHERE      M.PMAT_DT_FIM IS NULL
                  AND        D.PAPD_ID_DOCUMENTO = '".$idDocumento."'
                  AND        D.PAPD_DH_EXCLUSAO IS NULL
                  ORDER BY   P.PNAT_NO_PESSOA";
        $stmt = $db->query($query);
        return $stmt->fetchAll();
    }
    
}
