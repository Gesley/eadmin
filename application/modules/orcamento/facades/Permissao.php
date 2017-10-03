<?php
/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 *
 * e-Admin
 * e-Orçamento
 * Facade
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre esfera, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Esfera
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Permissao extends Orcamento_Facade_Base
{

    /**
     * Método construtor da classe
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_Permissao ();

        // Define a controle desta action
        $this->_controle = 'permissao';
    }

    /**
     * Retorna todos os usuarios cadastrados no orcamento
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaUsuarios() {
        $sql = "
                SELECT
                  *
                FROM CEO_TB_PERM_PERMISSAO_ACESSO
                WHERE
                  PERM_DH_EXCLUSAO_LOGICA IS NULL AND
                  PERM_CD_MATRICULA_EXCLUSAO IS NULL
        ";
        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchAll($sql);
    }

    /**
     * retorna dados da combo de perfil
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaComboPerfil () {

            $sql = "
                    SELECT
                    PERF_ID_PERFIL,
                    PERF_DS_PERFIL
                    FROM OCS_TB_PERF_PERFIL
                    ORDER BY
                        PERF_DS_PERFIL ASC ";

            $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchPairs($sql);

    }

    /**
     * retorna dados da combo de responsaveis
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaComboResponsavel () {
        $sql = "
                    SELECT
                    LOTA_SIGLA_LOTACAO,
                    LOTA_SIGLA_LOTACAO||' - '||LOTA_DSC_LOTACAO
                    FROM SARH.RH_CENTRAL_LOTACAO
                    ORDER BY
                        LOTA_DSC_LOTACAO ASC ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchPairs($sql);
    }

    /**
     * verfica se um usuário ja tem permissão
     * @param string $matricula
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function verificaDuplicidade($matricula) {
        $sql = "
            SELECT
              PERM_ID_PERMISSAO_ACESSO,
              PERM_CD_MATRICULA
            FROM CEO_TB_PERM_PERMISSAO_ACESSO
              WHERE
                    PERM_CD_MATRICULA = '$matricula'
              AND PERM_CD_MATRICULA_EXCLUSAO IS NULL
              AND PERM_DH_EXCLUSAO_LOGICA IS NULL
            ";
        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchRow($sql);
    }

    /**
     * retorna dados de um usuário pela matricula
     * @param $matricula
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaUsuarioPorMatricula($pessoa) {
        $sql ="
        SELECT
                PMAT_CD_MATRICULA,
                PNAT_NO_PESSOA
                FROM   OCS_TB_PMAT_MATRICULA M
                    INNER JOIN RH_CENTRAL_LOTACAO C ON
                      C.LOTA_COD_LOTACAO = M.PMAT_CD_UNIDADE_LOTACAO AND
                      C.LOTA_SIGLA_SECAO = M.PMAT_SG_SECSUBSEC_LOTACAO
                    INNER JOIN OCS_TB_PNAT_PESSOA_NATURAL P
                      ON M.PMAT_ID_PESSOA = P.PNAT_ID_PESSOA
                    WHERE
                      M.PMAT_CD_MATRICULA LIKE '%$pessoa%' OR
                      P.PNAT_NO_PESSOA LIKE '%$pessoa%'
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

}