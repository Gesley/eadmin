<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as regras negociais sobre informativo matricula
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Informativomatricula
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_InformativoMatri extends Orcamento_Business_Negocio_Base
{

    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Infm ();

        // Define a negocio
        $this->_negocio = 'informativomatri';

    }

    /**
     * Configura as definições de responsaveis antes do insert
     *
     * @return string array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluir( $dados )
    {
        // Responsável do usuário logado
        $sessao = new Orcamento_Business_Sessao ();
        $perfilFull = $sessao->retornaPerfil ();

        $responsaveis = array(
                'INFM_CD_INFORMATIVO_RESP'  => $dados["INFM_CD_INFORMATIVO_RESP"],
                'INFM_CD_MATRICULA_LEITURA' => strtoupper( $perfilFull["usuario"] ),
                'INFM_DT_LEITURA'           => new Zend_Db_Expr ( 'SYSDATE' ),
                'INFM_CD_INFORMATIVO'       => $dados['INFM_CD_INFORMATIVO'],
        );

        return parent::incluir($responsaveis);
    }

    /**
     * Retorna dados de responsaveis
     *
     * @return string array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaResponsaveis ( $codigo )
    {

        $sql = "
                SELECT
                      RESP.RESP_CD_RESPONSAVEL,
                      LOTA.LOTA_SIGLA_LOTACAO,
                      LOTA.LOTA_DSC_LOTACAO
                FROM  CEO_TB_INFR_INFORMATIVO_RESP  INFR
                INNER JOIN
                      CEO_TB_RESP_RESPONSAVEL RESP ON
                      RESP.RESP_CD_RESPONSAVEL = INFR.INFR_CD_RESPONSAVEL
                Left JOIN
                      RH_CENTRAL_LOTACAO     LOTA ON
                      LOTA.LOTA_COD_LOTACAO    = RESP.RESP_CD_LOTACAO     AND
                      LOTA.LOTA_SIGLA_SECAO    = RESP.RESP_DS_SECAO
                WHERE INFR_CD_INFORMATIVO      = ". $codigo ."
                    ORDER BY
                      LOTA.LOTA_DSC_LOTACAO
                ";

        $banco = Zend_Db_Table::getDefaultAdapter ();

        $responsaveis = $banco->fetchAll ( $sql );

        return $responsaveis;
    }

    /**
     * Retorna informativo por matricula
     *
     * @return string array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaInformativosMatricula ( $codInfm )
    {
        $sql = "
                SELECT
                    INFM.INFM_CD_INFORMATIVO_MATRICULA,
                    INFO.INFO_NR_INFORMATIVO,
                    INFO.INFO_TX_TITULO_INFORMATIVO,
                    INFO.INFO_DS_INFORMATIVO,
                    INFR.INFR_CD_RESPONSAVEL,
                    INFM.INFM_CD_MATRICULA_LEITURA,
                    RESP.RESP_DS_SECAO,
                    RH_SIGLAS_FAMILIA_CENTR_LOTA (
                        RHCL.LOTA_SIGLA_SECAO,
                        RHCL.LOTA_COD_LOTACAO
                    )                               AS SG_FAMILIA_RESPONSAVEL,
                    TO_DATE(SYSDATE) AS HOJE,
                    INFO_DT_INICIO,
                    INFO_DT_TERMINO
                FROM
                    CEO_TB_INFO_INFORMATIVO INFO
                Left JOIN
                    CEO_TB_INFR_INFORMATIVO_RESP INFR ON
                        INFR.INFR_CD_INFORMATIVO = INFO.INFO_NR_INFORMATIVO
                Left JOIN
                    CEO_TB_INFM_INFORMATIVO_MATRI INFM ON
                        INFM.INFM_CD_INFORMATIVO_RESP = INFR.INFR_CD_RESPONSAVEL
                Left JOIN
                    CEO_TB_RESP_RESPONSAVEL RESP ON
                        RESP.RESP_CD_RESPONSAVEL = INFR.INFR_CD_RESPONSAVEL
                Left JOIN
                    RH_CENTRAL_LOTACAO RHCL ON
                        RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO AND
                        RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO
                WHERE
                    INFO.INFO_DH_EXCLUSAO_LOGICA IS NULL AND
                    TO_DATE(SYSDATE) BETWEEN INFO_DT_INICIO AND INFO_DT_TERMINO AND
                    INFM.INFM_CD_INFORMATIVO_MATRICULA = $codInfm
                ";

        $banco = Zend_Db_Table::getDefaultAdapter ();

        $informativos = $banco->fetchAll ( $sql );

        return $informativos;

    }

}
