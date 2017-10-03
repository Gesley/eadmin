<?php
/**
 * Contém regras negociais específicas desta funcionalidade
 * 
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém as regras negociais sobre as Pendências do sistemas
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Pendencia
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Pendencia extends Orcamento_Business_Negocio_Base
{

    /**
     * Método construtor
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct ()
    {
        // não faz nada
    }

    /**
     * Verifica se há alguma pendência nos dados do sistema que necessite
     * intervenção
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return boolean há ou não pendências para o ano informado
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function haPendencias ( $ano )
    {
        // Retorna as instruções sql para verificação de pendências
        $sql1 = $this->retornaSqlQtde_NE_SEM_RDO ( $ano );
        $sql2 = $this->retornaSqlQtde_NE_INCONSISTENTE ( $ano );
        $sql3 = $this->retornaSqlQtde_RDO_SEM_NE ( $ano );
        $sql4 = $this->retornaSqlQtde_NC_SEM_DESPESA ( $ano );
        $sql5 = $this->retornaSqlQtde_NC_SEM_DESPESA_RESERVA ( $ano );
        $sql6 = $this->retornaSqlQtde_NC_SEM_TIPO_NC ( $ano );
        $sql7 = $this->retornaSqlQtde_NC_INCONSISTENTE ( $ano );
        $sql71 = $this->retornaSqlQtde_NC_INCONSISTENTE_RESERVA ( $ano );
        $sql8 = $this->retornaSqlQtde_CRED_INCONSISTENTE ( $ano );
        $sql9 = $this->retornaSqlQtde_SOLICITACAO_ABERTA_DESPESA ( $ano );
        $sql10 = $this->retornaSqlQtde_SOLICITACAO_ABERTA_MOVIMENTACAO ( $ano );
        
        // Monta a instrução sql para a verificação da existência de pendências
        $sql = "
SELECT
    CASE SUM(Qtde)
        WHEN 0 THEN 0
        ELSE 1
    END AS QTDE_REGISTROS
FROM
    (
    $sql1 UNION $sql2 UNION $sql3 UNION
    $sql4 UNION $sql5 UNION $sql6 UNION
    $sql7 UNION $sql71 UNION $sql8 UNION $sql9 UNION
    $sql10
    )
				";
        
        // Instancia o adaptador
        $banco = Zend_Db_Table::getDefaultAdapter ();
        
        // Devolve a existência ou não de pendências
        return $banco->fetchOne ( $sql );
    }

    /**
     * Verifica se há alguma pendência nos dados do sistema que necessite
     * intervenção
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaPendencias ( $ano )
    {
        // Verifica se ano foi informado
        if ( !isset ( $ano ) ) {
            // Define ano atual
            $ano = date ( 'Y' );
        }
        
        // Retorna a instrução sql de pendências
        $sql = $this->retornaSqlPendencias ( $ano );

        // Instancia o adaptador
        $banco = Zend_Db_Table::getDefaultAdapter ();
        
        // Devolve o registro com dados para exibição quantitativa
        return $banco->fetchRow ( $sql );
    }

    /**
     * Monta as diversas querys para a verificação de existência de pendências,
     * ou não, do sistema
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlPendencias ( $ano )
    {
        // Retorna as instruções sql para verificação de pendências
        $sql1 = $this->retornaSqlQtde_NE_SEM_RDO ( $ano );
        $sql2 = $this->retornaSqlQtde_NE_INCONSISTENTE ( $ano );
        $sql3 = $this->retornaSqlQtde_RDO_SEM_NE ( $ano );
        $sql4 = $this->retornaSqlQtde_NC_SEM_DESPESA ( $ano );
        $sql5 = $this->retornaSqlQtde_NC_SEM_DESPESA_RESERVA ( $ano );
        $sql6 = $this->retornaSqlQtde_NC_SEM_TIPO_NC ( $ano );
        $sql7 = $this->retornaSqlQtde_NC_INCONSISTENTE ( $ano );
        $sql71 = $this->retornaSqlQtde_NC_INCONSISTENTE_RESERVA ( $ano );
        $sql8 = $this->retornaSqlQtde_CRED_INCONSISTENTE ( $ano );
        $sql9 = $this->retornaSqlQtde_SOLICITACAO_ABERTA_DESPESA ( $ano );
        $sql10 = $this->retornaSqlQtde_SOLICITACAO_ABERTA_MOVIMENTACAO ( $ano );
        $sql11 = $this->retornaSql_ULTIMA_DATA_IMPORT_NE ( $ano );
        $sql12 = $this->retornaSql_ULTIMA_DATA_IMPORT_EXEC ( $ano );
        $sql13 = $this->retornaSql_ULTIMA_DATA_IMPORT_NC ( $ano );
        $sql14 = $this->retornaSql_ULTIMA_DATA_IMPORT_ND ( $ano );
        
        // Monta a instrução sql com o quantitivo de ocorrências por tipo
        $sql = "
SELECT
    ( $sql1 ) AS QTDE_NE_SEM_RDO,
    ( $sql2 ) AS QTDE_NE_INCONSISTENTE,
    ( $sql3 ) AS QTDE_RDO_SEM_NE,
    ( $sql4 ) AS QTDE_NC_SEM_DESPESA,
    ( $sql5 ) AS QTDE_NC_SEM_DESPESA_RESERVA,
    ( $sql6 ) AS QTDE_NC_SEM_TIPO_NC,
    ( $sql7 ) AS QTDE_NC_INCONSISTENTE,
    ( $sql71 ) AS QTDE_NC_INCONSISTENTE_RESERVA,
    ( $sql8 ) AS QTDE_CRED_INCONSISTENCIA,
    ( $sql9 ) AS QTDE_SOLICITACAO_DESPESA,
    ( $sql10 ) AS QTDE_SOLICITACAO_MOVIMENTACAO,
    ( $sql11 ) AS DATA_ULTIMA_IMPORTACAO_NE,
    ( $sql12 ) AS DATA_ULTIMA_IMPORTACAO_EXEC,
    ( $sql13 ) AS DATA_ULTIMA_IMPORTACAO_NC,
    ( $sql14 ) AS DATA_ULTIMA_IMPORTACAO_ND
FROM
    Dual
		";
        


        // Devolve a instrução sql
        return $sql;
    }

    /**
     * Monta instrução sql base para contagem de registros de outra query
     *
     * @param string $sqlBase        
     * @return string
     */
    private function retornaSqlQtdeBase ( $sqlBase )
    {
        // Devolve instrução sql para contagem de registros
        return "SELECT NVL(COUNT(*), 0) Qtde FROM ( $sqlBase )";
    }

    /**
     * Instrução sql que apresenta a quantidade de notas de empenho sem
     * identificação de despesa
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_NE_SEM_RDO ( $ano )
    {
        // Define instrução sql base
        $sqlBase = $this->retornaSql_NE_SEM_RDO ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de notas de empenho sem
     * identificação de despesa
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_NE_SEM_RDO ( $ano )
    {
        // Define o código do tipo de solicitação
        $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
        
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    NOEM_CD_NOTA_EMPENHO
FROM
    CEO_TB_NOEM_NOTA_EMPENHO
Left JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = NOEM_NR_DESPESA
Left JOIN
    CEO_TB_RESP_RESPONSAVEL RSP ON
        RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO RHCL ON
        RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
        RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO
WHERE
    NOEM_NR_DESPESA IS NULL AND
    NOEM_CD_NE_REFERENCIA IS NULL AND
    NOEM_IC_ACERTADO_MANUALMENTE = $acertadaNao AND
    SUBSTR(NOEM_CD_NOTA_EMPENHO, 1, 4) = $ano
                ".CEO_PERMISSAO_RESPONSAVEIS;
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de inconsitências entre os dados
     * da nota de empenho e a respectiva despesa relacionada
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_NE_INCONSISTENTE ( $ano )
    {
        // Instancia regra negocial correspondente
        $negocio = new Trf1_Orcamento_Negocio_Ne ();
        
        // Define instrução sql base
        $sqlBase = $negocio->retornaSqlListagemInconsistencia ( $ano, true );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de RDOs não utilizadas até então
     * em nenhuma nota de empenho original
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_RDO_SEM_NE ( $ano )
    {
        // Define instrução sql base
        $sqlBase = $this->retornaSql_RDO_SEM_NE ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de RDOs não utilizadas até
     * então em nenhuma nota de empenho original
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_RDO_SEM_NE ( $ano )
    {
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    REQV_NR_DESPESA
FROM
    CEO_TB_REQV_REQU_VARIACAO
Left JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = REQV_NR_DESPESA
Left JOIN
    CEO_TB_RESP_RESPONSAVEL RSP ON
        RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO RHCL ON
        RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
        RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO    
WHERE
    REQV_NR_DESPESA NOT IN (
        SELECT
            DISTINCT(NOEM_NR_DESPESA)
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO
        WHERE
            NOEM_NR_DESPESA IS NOT NULL AND
            NOEM_CD_NE_REFERENCIA IS NULL AND
            SUBSTR(NOEM_CD_NOTA_EMPENHO, 1, 4) = $ano 
            ".CEO_PERMISSAO_RESPONSAVEIS."
    )";
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de notas de crédito sem
     * identificação de despesa ou tipo de nota de crédito
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_NC_SEM_DESPESA ( $ano )
    {
        // Define instrução sql base
        $sqlBase = $this->retornaSql_NC_SEM_DESPESA ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de notas de crédito sem
     * identificação de despesa ou tipo de nota de crédito
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_NC_SEM_DESPESA ( $ano )
    {
        // Define o código do tipo de solicitação
        $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
        
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    NOCR_CD_NOTA_CREDITO
FROM
    CEO_TB_NOCR_NOTA_CREDITO
Left JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = NOCR_NR_DESPESA
Left JOIN
    CEO_TB_RESP_RESPONSAVEL RSP ON
        RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO RHCL ON
        RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
        RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO      
WHERE
    NOCR_CD_NOTA_CREDITO LIKE '$ano%' AND
    NOCR_IC_ACERTADO_MANUALMENTE = $acertadaNao AND
    ( NOCR_NR_DESPESA IS NULL OR NOCR_NR_DESPESA <= 0 )        
                ".CEO_PERMISSAO_RESPONSAVEIS;
        
        // Devolve a instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de notas de crédito sem
     * identificação de despesa ou tipo de nota de crédito
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_NC_SEM_DESPESA_RESERVA ( $ano )
    {
        // Define instrução sql base
        $sqlBase = $this->retornaSql_NC_SEM_DESPESA_RESERVA ( $ano );
        
        $negocio = new Trf1_Orcamento_Negocio_Nc();

        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Retorna instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de notas de crédito sem
     * identificação de despesa ou tipo de nota de crédito
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_NC_SEM_DESPESA_RESERVA ( $ano )
    {
        // Define o código do tipo de solicitação
        $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
        
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    NOCR_CD_NOTA_CREDITO
FROM
    CEO_TB_NOCR_NOTA_CREDITO
Left JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = NOCR_NR_DESPESA
Left JOIN
    CEO_TB_RESP_RESPONSAVEL RSP ON
        RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO RHCL ON
        RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
        RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO      
WHERE
    NOCR_CD_NOTA_CREDITO LIKE '$ano%' AND
    NOCR_IC_ACERTADO_MANUALMENTE = $acertadaNao AND
    ( NOCR_NR_DESPESA_RESERVA IS NULL OR NOCR_NR_DESPESA_RESERVA <= 0 )        
                ".CEO_PERMISSAO_RESPONSAVEIS;
        
        // Devolve a instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de notas de crédito sem
     * identificação de despesa ou tipo de nota de crédito
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_NC_SEM_TIPO_NC ( $ano )
    {
        // Define instrução sql base
        $sqlBase = $this->retornaSql_NC_SEM_TIPO_NC ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de notas de crédito sem
     * identificação de despesa ou tipo de nota de crédito
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_NC_SEM_TIPO_NC ( $ano )
    {
        // Define o código do tipo de solicitação
        $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
        
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    NOCR_CD_NOTA_CREDITO
FROM
    CEO_TB_NOCR_NOTA_CREDITO
Left JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = NOCR_NR_DESPESA
Left JOIN
    CEO_TB_RESP_RESPONSAVEL RSP ON
        RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO RHCL ON
        RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
        RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO     
WHERE
    NOCR_CD_NOTA_CREDITO LIKE '$ano%' AND
    NOCR_IC_ACERTADO_MANUALMENTE = $acertadaNao AND
    NOCR_CD_TIPO_NC IS NULL        
                ".CEO_PERMISSAO_RESPONSAVEIS;
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de de inconsistências entre os
     * dados da nota de crédito e a respectiva despesa relacionada
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_NC_INCONSISTENTE ( $ano )
    {
        // Instancia regra negocial correspondente
        $negocio = new Trf1_Orcamento_Negocio_Nc ();
        
        // Define instrução sql base
        $sqlBase = $negocio->retornaSqlListagemInconsistencia ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de de inconsistências entre os
     * dados da nota de crédito e a respectiva despesa relacionada
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_NC_INCONSISTENTE_RESERVA ( $ano )
    {
        // Instancia regra negocial correspondente
        $negocio = new Trf1_Orcamento_Negocio_Nc ();
        
        // Define instrução sql base
        $sqlBase = $negocio->retornaSqlListagemInconsistenciaReserva ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de de inconsistências entre os
     * dados da nota de crédito e a respectiva despesa relacionada
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_CRED_INCONSISTENTE ( $ano )
    {
        // Instancia regra negocial correspondente
        $negocio = new Orcamento_Business_Negocio_Credito ();
        
        // Define instrução sql base
        $sqlBase = $negocio->retornaSql_CRED_INCONSISTENCIA ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de de solicitações de novas
     * despesas com status de solicitada
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_SOLICITACAO_ABERTA_DESPESA ( $ano )
    {
        // Define instrução sql base
        $sqlBase = $this->retornaSql_SOLICITACAO_ABERTA_DESPESA ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de solicitações de novas
     * despesas com status de solicitada
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_SOLICITACAO_ABERTA_DESPESA ( $ano )
    {
        // Define o código do tipo de solicitação
        $solicitada = Orcamento_Business_Dados::TIPO_SOLICITACAO_SOLICITADA;
        
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    SOLD_NR_SOLICITACAO
FROM
    CEO_TB_SOLD_SOLIC_DESPESA
Left JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = SOLD_NR_DESPESA
Left JOIN
    CEO_TB_RESP_RESPONSAVEL RSP ON
        RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO RHCL ON
        RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
        RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO     
WHERE
    SOLD_DH_EXCLUSAO_LOGICA IS NULL AND
    SOLD_CD_TIPO_SOLICITACAO = $solicitada
                ".CEO_PERMISSAO_RESPONSAVEIS;
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta a quantidade de solicitações de novas
     * movimentações de crédito com status de solicitada
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSqlQtde_SOLICITACAO_ABERTA_MOVIMENTACAO ( $ano )
    {
        // Define instrução sql base
        $sqlBase = $this->retornaSql_SOLICITACAO_ABERTA_MOVIMENTACAO ( $ano );
        
        // Retorna instrução com contagem de registros
        $sql = $this->retornaSqlQtdeBase ( $sqlBase );
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de solicitações de novas
     * movimentações de crédito com status de solicitada
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_SOLICITACAO_ABERTA_MOVIMENTACAO ( $ano )
    {
        // Define o código do tipo de solicitação
        $solicitada = Orcamento_Business_Dados::TIPO_SOLICITACAO_SOLICITADA;
        
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    MOVC_CD_MOVIMENTACAO
FROM
    CEO_TB_MOVC_MOVIMENTACAO_CRED
Left JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = MOVC_NR_DESPESA_ORIGEM OR
        DESP.DESP_NR_DESPESA = MOVC_NR_DESPESA_DESTINO
Left JOIN
    CEO_TB_RESP_RESPONSAVEL RSP ON
        RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO RHCL ON
        RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
        RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO    
WHERE
    MOVC_DH_EXCLUSAO_LOGICA IS NULL AND
    MOVC_CD_TIPO_SOLICITACAO = $solicitada
                ";
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que devolve a maior data dentre as notas de empenho já
     * importadas conforme o ano informado
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_ULTIMA_DATA_IMPORT_NE ( $ano )
    {
        // Define formato para exibição da data
        $data = Trf1_Orcamento_Definicoes::FORMATO_DATA;
        
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    TO_CHAR(MAX(NOEM_DH_NE), '$data') DATA
FROM
    CEO_TB_NOEM_NOTA_EMPENHO        
                ";
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que devolve a maior data dentre a execução das notas de
     * empenho já importadas conforme o ano informado
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_ULTIMA_DATA_IMPORT_EXEC ()
    {
        // TODO: Ver necessária alteração da tabela negocial
        return "'-'";
    }

    /**
     * Instrução sql que devolve a maior data dentre as notas de crédito já
     * importadas conforme o ano informado
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_ULTIMA_DATA_IMPORT_NC ( $ano )
    {
        // Define formato para exibição da data
        $data = Trf1_Orcamento_Definicoes::FORMATO_DATA;
        
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    TO_CHAR(MAX(NOCR_DH_NC), '$data') DATA
FROM
    CEO_TB_NOCR_NOTA_CREDITO        
                ";
        
        // Devolve instrução sql
        return $sql;
    }

    /**
     * Instrução sql que devolve a maior data dentre as notas de dotação já
     * importadas conforme o ano informado
     *
     * @param integer $ano
     *        Exercício a ser pesquisado
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaSql_ULTIMA_DATA_IMPORT_ND ()
    {
        // TODO: Ver necessária alteração da tabela negocial
        return "'-'";
    }

    /**
     * Monta o conteúdo a ser exibido na view, conforme os parâmetros informados
     *
     * @param string $texto        
     * @param numeric $qtde        
     * @param string $linkBase        
     * @param string $linkComplemento        
     * @param boolean $critico        
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaLinhaConteudo ( $texto, $qtde, $linkBase, 
    $linkComplemento, $critico = true )
    {
        // 'Zera' variáveis
        $erro = '';
        $link = '';
        
        if ( $qtde > 0 ) {
            // Tipifica o erro
            $erro = ( $critico ? " class='alert'" : " class='notice'" );
            
            // OBSOLETO abaixo
            // $erroDescricao = ($critico ? "Corrigir ocorrências" : "Verificar
            // ocorrências");
            $erroDescricao = 'Listar ocorrências';
            
            // Monta link a ser utilizado na funcionalidade
            $link = '';
            if ( $linkComplemento ) {
                // Define o campo contendo o link para a pendência em questão
                $link = "";
                $link .= "<a href='";
                $link .= $linkBase;
                $link .= $linkComplemento;
                $link .= "' target='_blank'> ";
                $link .= $erroDescricao;
                $link .= " </a>";
            }
        }
        
        // TODO: Passar para a view tal formatação
        // Define os dados formatados
        $retorno = "";
        $retorno .= "<tr $erro>" . PHP_EOL;
        $retorno .= "	<td width='80%'> $texto </td>" . PHP_EOL;
        $retorno .= "	<td width='05%'> $qtde </td>" . PHP_EOL;
        $retorno .= "	<td width='15%'> $link </td>" . PHP_EOL;
        $retorno .= "</tr>" . PHP_EOL . PHP_EOL;
        
        // Devolve a informação formatada
        return $retorno;
    }

    /**
     * Lista os anos controlados pelo sistema
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaAnosSistema ()
    {
        // TODO: Ver se é o caso de mover essa sql para a regra negocial...
        // Define a instrução sql negocial
        $sql = "
SELECT
    DESP_AA_DESPESA,
    DESP_AA_DESPESA ANO
FROM
    CEO_TB_DESP_DESPESA
GROUP BY
    DESP_AA_DESPESA
ORDER BY
    DESP_AA_DESPESA DESC
				";
        
        // Instancia o adaptador
        $banco = Zend_Db_Table::getDefaultAdapter ();
        
        // Devolve os anos presente na tabela de despesa
        return $banco->fetchPairs ( $sql );
    }

    /**
     * Retorna array contendo nome dos meses
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaMeses ()
    {
        // Define os nomes dos meses em português
        $meses [ 1 ] = 'Janeiro';
        $meses [ 2 ] = 'Fevereiro';
        $meses [ 3 ] = 'Março';
        $meses [ 4 ] = 'Abril';
        $meses [ 5 ] = 'Maio';
        $meses [ 6 ] = 'Junho';
        $meses [ 7 ] = 'Julho';
        $meses [ 8 ] = 'Agosto';
        $meses [ 9 ] = 'Setembro';
        $meses [ 10 ] = 'Outubro';
        $meses [ 11 ] = 'Novembro';
        $meses [ 12 ] = 'Dezembro';
        
        // Devolve meses
        return $meses;
        
        /*
         * return array ( 1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 =>
         * 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 =>
         * 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro' );
         */
    }

}