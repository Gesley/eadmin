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
 * Contém as regras negociais sobre Despesas
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Despesa
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Despesa extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Desp ();

        // Define a negocio
        $this->_negocio = 'despesa';
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome da ação (action) em questão
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCampos ( $acao = 'todos' )
    {
        // Campos para a serem apresentados na indexAction
        $campos [ 'todos' ] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos [ 'index' ] = "";

        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = $campos [ 'index' ];

        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "";

        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "";
        $campos [ 'excluir' ] .= $campos [ 'detalhe' ];

        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = $campos [ 'excluir' ];

        // Campos para a serem apresentados num combo
        $campos [ 'combo' ] = "";

        // Devolve os campos, conforme ação
        return $campos [ $acao ];
    }

    /**
     * Retorna as condições restritivas, se houver para a montagem da instrução
     * sql.
     *
     * @param string $acao
     *        Nome da ação (action) em questão
     * @param string $chaves
     *        Informa a chave, já tratada, se for o caso
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaRestricoes ( $acao = 'todos', $chaves = null )
    {
        // Condição para ação editar
        $restricao [ 'editar' ] = "";

        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'editar' ];

        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'editar' ];

        // Condição para montagem do combo
        $restricao [ 'combo' ] = "";

        return $restricao [ $acao ];
    }

    /**
     * Retorna a instrução sql que realiza a exclusão lógica de uma ou mais
     * esferas
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlExclusaoLogica ( $chaves )
    {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula ();

        // Trata a chave primária (ou composta)
        $ptres = $this->separaChave ( $chaves );

        // Exclui um ou mais registros
        $sql = "";

        // Devolve a instrução sql para exclusão lógica
        return $sql;
    }

    /**
     * Retorna a instrução sql que restaura um ou mais registros logicamente
     * excluídos
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para restauração de um ou mais
     *        registros
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlRestauracaoLogica ( $chaves )
    {
        // Trata a chave primária (ou composta)
        $ptres = $this->separaChave ( $chaves );

        // Restaura um ou mais registros
        $sql = "";

        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCacheIds ( $acao = null )
    {
        // Instancia o cache
        $cache = new Trf1_Cache ();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $id [ 'index' ] = $cache->retornaID_Listagem ( 'orcamento', $negocio );

        // Id para combo
        $id [ 'combo' ] = $cache->retornaID_Combo ( 'orcamento', $negocio );

        // Determina qual valor será retornado
        $retorno = ( $acao != null ? $id [ $acao ] : $id );

        // Devolve o id, conforme $acao informada
        return $retorno;
    }

    /**
     * Copia as despesas do exercício desejado buscando os dados do ano anterior
     *
     * @param integer $ano
     * @return mixed Resultado da operação
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function copiaDespesas ( $ano, $faseAjuste = null )
    {
        // Monta array com as instruções Sql
        $sqlDespesas = $this->retornaSqlCopiaDespesas ( $ano );
        $sqlContratos = $this->retornaSqlCopiaContratos ( $ano );
        $sqlValores = $this->retornaSqlCopiaValores ( $ano, $faseAjuste );

         // Agrega as querys em único array
        $sqls = array ( $sqlDespesas, $sqlContratos, $sqlValores );
        $resultado = $this->executaQuery ( $sqls, true );

        return $resultado;
    }

    /**
     * Retorna a instrução sql que gera novas despesas para o exercício
     * informado baseado no ano imediatamente anterior
     *
     * @param int $ano
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlCopiaDespesas ( $ano )
    {
        $sql = "
INSERT INTO
    CEO_TB_DESP_DESPESA
(
    DESP_NR_DESPESA,
    DESP_AA_DESPESA,
    DESP_CD_UG,
    DESP_CD_ESFERA,
    DESP_CD_PT_RESUMIDO,
    DESP_CD_ELEMENTO_DESPESA_SUB,
    DESP_CD_TIPO_DESPESA,
    DESP_CD_FONTE,
    DESP_CD_VINCULACAO,
    DESP_CD_CATEGORIA,
    DESP_CD_TIPO_RECURSO,
    DESP_CD_TIPO_ORCAMENTO,
    DESP_CD_OBJETIVO,
    DESP_CD_PROGRAMA,
    DESP_CD_TIPO_OPERACIONAL,
    DESP_CD_RESPONSAVEL,
    DESP_DS_ADICIONAL,
    DESP_VL_MAX_MENSAL_AUTORIZADO,
    DESP_CD_MATRICULA_EXCLUSAO,
    DESP_DH_EXCLUSAO_LOGICA,
    DESP_NR_CEO,
    DESP_NR_COPIA_DESPESA,
    DESP_IC_REFLEXO_EXERCICIO
)
SELECT
    /* Sequence */ CEO_SQ_DESP.NEXTVAL,
    /* Variável do ano */ $ano,
    DESP_CD_UG,
    DESP_CD_ESFERA,
    DESP_CD_PT_RESUMIDO,
    DESP_CD_ELEMENTO_DESPESA_SUB,
    DESP_CD_TIPO_DESPESA,
    DESP_CD_FONTE,
    DESP_CD_VINCULACAO,
    DESP_CD_CATEGORIA,
    DESP_CD_TIPO_RECURSO,
    DESP_CD_TIPO_ORCAMENTO,
    DESP_CD_OBJETIVO,
    DESP_CD_PROGRAMA,
    DESP_CD_TIPO_OPERACIONAL,
    DESP_CD_RESPONSAVEL,
    DESP_DS_ADICIONAL,
    DESP_VL_MAX_MENSAL_AUTORIZADO,
    /* Valor fixo */ Null,
    /* Valor fixo */ Null,
    /* Valor fixo */ Null,
    DESP_NR_DESPESA,
    DESP_IC_REFLEXO_EXERCICIO
FROM
    CEO_TB_DESP_DESPESA
WHERE
    DESP_IC_REFLEXO_EXERCICIO = 1 AND
    DESP_AA_DESPESA           = $ano - 1 AND
    DESP_DH_EXCLUSAO_LOGICA IS Null AND
    DESP_NR_DESPESA NOT IN
        (
        SELECT DESP_NR_COPIA_DESPESA
        FROM CEO_TB_DESP_DESPESA
        WHERE DESP_AA_DESPESA = $ano AND
        DESP_NR_COPIA_DESPESA IS NOT NULL
        )
                ";

        return $sql;
    }

    /**
     * Retorna a instrução sql que gera novos registros dos contratos das
     * despesas recém duplicadas para o exercício informado baseado no ano
     * imediatamente anterior
     *
     * @param int $ano
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlCopiaContratos ( $ano )
    {
        $sql = "
INSERT INTO
    CEO_TB_CTRD_CONTRATO_DESPESA
(
    CTRD_ID_CONTRATO_DESPESA,
    CTRD_NR_DESPESA,
    CTRD_NR_CONTRATO,
    CTRD_NM_EMPRESA_CONTRATADA,
    CTRD_DT_INICIO_VIGENCIA,
    CTRD_DT_TERMINO_VIGENCIA,
    CTRD_CPFCNPJ_DESPESA,
    CTRD_VL_DESPESA
)
SELECT
    CEO_SQ_CTRD.NEXTVAL,
    /* Nova código da despesa */ DESP.DESP_NR_DESPESA,
    CTRD.CTRD_NR_CONTRATO,
    CTRD.CTRD_NM_EMPRESA_CONTRATADA,
    CTRD.CTRD_DT_INICIO_VIGENCIA,
    CTRD.CTRD_DT_TERMINO_VIGENCIA,
    CTRD.CTRD_CPFCNPJ_DESPESA,
    CTRD.CTRD_VL_DESPESA
FROM
    CEO_TB_CTRD_CONTRATO_DESPESA CTRD
INNER JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_COPIA_DESPESA = CTRD.CTRD_NR_DESPESA AND
        DESP.DESP_AA_DESPESA = $ano AND
        DESP.DESP_NR_DESPESA NOT IN
            (
            SELECT CTRD_NR_DESPESA
            FROM CEO_TB_CTRD_CONTRATO_DESPESA
            )
                ";

        return $sql;
    }

    /**
     * Retorna a instrução sql que gera novos registros dos valores das despesas
     * recém duplicadas para o exercício informado baseado no ano imediatamente
     * anterior
     *
     * @param int $ano
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     * @modified Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlCopiaValores ( $ano, $faseAjuste = null )
    {
        // Faz o ajuste do campo saldo se necessario
        $negSaldo = new Trf1_Orcamento_Negocio_Saldo ();
        if($faseAjuste != ''){
            $sqlSaldos = $negSaldo->_retornaQueryCompleta ( null, $faseAjuste);

        }else{
            $sqlSaldos = $negSaldo->_retornaQueryCompleta ();

        }


        $sql = "
INSERT INTO
    CEO_TB_VLDE_VALOR_DESPESA
(
    VLDE_NR_DESPESA,
    VLDE_CD_DEMANDANTE,
    VLDE_VL_DESPESA,
    VLDE_DH_DESPESA,
    VLDE_CD_MATRICULA_EXCLUSAO,
    VLDE_DH_EXCLUSAO_LOGICA
)
SELECT
    -- Código da despesa
    DANT.DESP_NR_DESPESA            DESP_ATUAL,

    -- Campo não utilizado!
    -- DESP.DESP_NR_DESPESA            DESP_ANTERIOR,

    -- Primeiro demandante
    (
    SELECT DEMN_CD_DEMANDANTE
    FROM CEO_TB_DEMN_DEMANDANTE_VALOR
    WHERE DEMN_NR_ORDEM_DEMANDANTE =
        (
        SELECT MIN ( DEMN_NR_ORDEM_DEMANDANTE ) DEMN_PRIMEIRO
        FROM CEO_TB_DEMN_DEMANDANTE_VALOR
        )
    )                               DEMANDANTE,

    /* Valor base de cálculo antigo
    (
    SALDO.VR_PROPOSTA_APROVADA +
    SALDO.VR_CREDITO_ADICIONAL +
    SALDO.VR_MOVIMENTACAO
    )                               VR_BASE,

    */

    -- novo caculo para o campo valor - solicitado 29-03 pelo adelson trocando o calculo acima pelo valor estático
    VR_PROPOSTA_APROVADA  AS  VR_BASE,


    -- Momento atual para inclusão do registro
    SYSDATE                         DATA,

    -- Valor fixo
    Null                            EXCLUSAO_MATRICULA,
    -- Valor fixo
    NULL                            EXCLUSAO_DATA
FROM
    CEO_TB_DESP_DESPESA DESP
Left JOIN
    CEO_TB_DESP_DESPESA DANT ON
        DANT.DESP_NR_COPIA_DESPESA = DESP.DESP_NR_DESPESA
Left JOIN
    (
    $sqlSaldos
    ) SALDO ON
     SALDO.NR_DESPESA = DESP.DESP_NR_DESPESA
WHERE
    DESP.DESP_DH_EXCLUSAO_LOGICA IS NULL AND
    DESP.DESP_AA_DESPESA = $ano - 1 AND
    DANT.DESP_NR_DESPESA NOT IN
        (
        SELECT VLDE_NR_DESPESA
        FROM CEO_TB_VLDE_VALOR_DESPESA
        )
                ";

        return $sql;
    }

    /**
     * Retorna a instrução sql que gera novos registros dos valores das despesas
     * recém duplicadas para o exercício informado baseado no ano imediatamente
     * anterior
     *
     * @param int $ano
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlCopiaValores_OLD ( $ano )
    {
        $sql = "
INSERT INTO
    CEO_TB_VLDE_VALOR_DESPESA
(
    VLDE_NR_DESPESA,
    VLDE_CD_DEMANDANTE,
    VLDE_VL_DESPESA,
    VLDE_DH_DESPESA,
    VLDE_CD_MATRICULA_EXCLUSAO,
    VLDE_DH_EXCLUSAO_LOGICA
)
SELECT
    DESP.DESP_NR_DESPESA,
    (
    SELECT DEMN_CD_DEMANDANTE
    FROM CEO_TB_DEMN_DEMANDANTE_VALOR
    WHERE DEMN_NR_ORDEM_DEMANDANTE =
        (
        SELECT MIN ( DEMN_NR_ORDEM_DEMANDANTE ) DEMN_PRIMEIRO
        FROM CEO_TB_DEMN_DEMANDANTE_VALOR
        )
    ) DEMANDANTE,
    VLDE.VLDE_VL_DESPESA,
    /* Momento da inclusão */ SYSDATE,
    /* Valor fixo */ Null,
    /* Valor fixo */ NULL
FROM
    CEO_TB_VLDE_VALOR_DESPESA VLDE
INNER JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_DH_EXCLUSAO_LOGICA IS Null AND
        DESP.DESP_NR_COPIA_DESPESA = VLDE.VLDE_NR_DESPESA AND
        DESP.DESP_AA_DESPESA = $ano AND
        DESP.DESP_NR_DESPESA NOT IN
            (
            SELECT VLDE_NR_DESPESA
            FROM CEO_TB_VLDE_VALOR_DESPESA
            )
WHERE
    VLDE_CD_DEMANDANTE =
        (
        SELECT DEMN_CD_DEMANDANTE
        FROM CEO_TB_DEMN_DEMANDANTE_VALOR
        WHERE DEMN_NR_ORDEM_DEMANDANTE =
            (
            SELECT MAX ( DEMN_NR_ORDEM_DEMANDANTE ) DEMN_ULTIMO
            FROM CEO_TB_DEMN_DEMANDANTE_VALOR
            )
        )
                ";

        return $sql;
    }

    /**
     * Retorna a lista de despesas conforme parâmetros de $ano e $ptres
     *
     * @param int $ano
     *        Ano
     * @param int $ptres
     *        Código do PTRES atual
     * @param string $d
     *        Texto digitado na combo a ser pesquisado
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaComboDespesaPorAnoPtres ( $ano = 0, $pt = 0, $d = '' )
    {
        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        // Busca perfil
        $sessao = new Orcamento_Business_Sessao ();
        $perfilFull = $sessao->retornaPerfil ();
        $perfil = $perfilFull [ 'perfil' ];

        $negocio = new Trf1_Orcamento_Negocio_Despesa ();
        $fasesExercicios = $negocio->retornaSqlFaseExercicio ();

        $condicaoFaseExercicio = "";
        if ( $perfil != Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR &&
         $perfil != Orcamento_Business_Dados::PERMISSAO_PLANEJAMENTO ) {
            $condicaoFaseExercicio = " AND FANE.FANE_ID_FASE_EXERCICIO <> ";
            $condicaoFaseExercicio .= Orcamento_Business_Dados::FASE_EXERCICIO_DEFINICAO;
        }

        $sql = "
SELECT
    DESP.DESP_NR_DESPESA,
    /* Descrição para o combo */
    DESP.DESP_NR_DESPESA || ' - ' ||
    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
    DESP.DESP_DS_ADICIONAL || ' [' ||
    DESP.DESP_AA_DESPESA || '] - ' ||
    DESP.DESP_CD_UG AS DS_COMBO_DESPESA
FROM
    CEO_TB_DESP_DESPESA DESP
Left JOIN
    CEO_TB_EDSB_ELEMENTO_SUB_DESP EDSB ON
        EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB = DESP.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
		RESP.RESP_CD_RESPONSAVEL			= DESP.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
		RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO					AND
		RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
Left JOIN
    (
    $fasesExercicios
    )                                       FANE ON
        FANE.FANE_NR_ANO                    = DESP.DESP_AA_DESPESA
WHERE
    DESP_DH_EXCLUSAO_LOGICA IS Null AND
    DESP_AA_DESPESA = $ano AND
    DESP_CD_PT_RESUMIDO = $pt AND

    /* Descrição para o combo */
    DESP.DESP_NR_DESPESA || ' - ' ||
    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
    DESP.DESP_DS_ADICIONAL || ' [' ||
    DESP.DESP_AA_DESPESA || '] - ' ||
    DESP.DESP_CD_UG

    Like '%$d%'

$condicaoResponsaveis
$condicaoFaseExercicio

ORDER BY
    DESP.DESP_NR_DESPESA
                ";

    $banco = Zend_Db_Table::getDefaultAdapter ();

    $dados = $banco->fetchAll ( $sql );

    return $dados;

    }

    /**
     * Retorna a instrução Sql que realiza a alteração de PTRES em lote das
     * despesas selecionadas
     *
     * @param integer $ano
     *        Ano das despesas a serem alteradas
     * @param integer $pOld
     *        PTRES anterior a atualização
     * @param integer $pNovo
     *        PTRES definitivo (após a alteração)
     * @param array $d
     *        Array dos códigos da despesa a serem alteradas
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlAlteraDespesaPTRES ( $ano, $pOld, $pNovo, array $d )
    {
         if ( !is_array ( $d ) ) {
            $msg = 'Parâmetro $d deve ser um array de despesas.';
            throw new Zend_Exception ( $msg );
        }

        $registros = count($d);
        $sqls = array();

        if( $registros > 1000 ) {
            $arrd = array_chunk($d, 1000);

            foreach ($arrd as $value) {
                try {
                    $despesas = implode ( ', ', $value );
                } catch ( Exception $e ) {
                    $msg = 'Erro ao separar as despesas.';
                    throw new Zend_Exception ( $msg );
                }


        $sqls[] = "
                UPDATE
                    CEO_TB_DESP_DESPESA
                SET
                    DESP_CD_PT_RESUMIDO = $pNovo
                WHERE
                    DESP_DH_EXCLUSAO_LOGICA IS Null AND
                    DESP_AA_DESPESA = $ano AND
                    DESP_CD_PT_RESUMIDO = $pOld AND
                    DESP_NR_DESPESA IN ( $despesas )
                    ";
            }
            return $this->executaQuery ( $sqls, true );

        } else {

            try {
                $despesas = implode ( ', ', $d );
            } catch ( Exception $e ) {
                $msg = 'Erro ao separar as despesas.';
                throw new Zend_Exception ( $msg );
            }



            //
            $sql = "
    UPDATE
        CEO_TB_DESP_DESPESA
    SET
        DESP_CD_PT_RESUMIDO = $pNovo
    WHERE
        DESP_DH_EXCLUSAO_LOGICA IS Null AND
        DESP_AA_DESPESA = $ano AND
        DESP_CD_PT_RESUMIDO = $pOld AND
        DESP_NR_DESPESA IN ( $despesas )
                    ";

            return $this->executaQuery ( $sql, true );
        }
    }
}
