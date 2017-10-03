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
 * Contém as regras negociais sobre Créditos recebidos do CJF / SOF
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Credito
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Credito extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Cred ();
        
        // Define a negocio
        $this->_negocio = 'credito';
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
        $campos [ 'index' ] = "
CRED_ID_CREDITO_RECEBIDO,
CRED_DS_DOCUMENTO,
CRED_DT_EMISSAO AS DATA_EMISSAO,
TO_CHAR(CRED_DT_EMISSAO, '" .
         Trf1_Orcamento_Definicoes::FORMATO_DATA . "') AS CRED_DT_EMISSAO,             
CRED_DS_OBSERVACAO,
CRED_CD_UNID_GEST_EMITENTE,
CRED_CD_FONTE,
CRED_CD_PT_RESUMIDO,
UNOR_CD_UNID_ORCAMENTARIA,
PTRS_SG_PT_RESUMIDO,
CRED_CD_ELEMENTO_DESPESA_SUB,
CRED_NR_DESPESA,
CRED_CD_TIPO_NC,
CRED_VL_CREDITO_RECEBIDO,
CASE CRED_IC_ACERTADO_MANUAL
    WHEN 1 THEN 'Sim '
    ELSE 'Não '
END                             CRED_IC_ACERTADO_MANUAL,
CASE
    WHEN '20'||SUBSTR( CRED_DT_EMISSAO , 7,8) = " . date('Y') . " THEN 1
    ELSE 2
END AS EXERCICIO,
CASE WHEN LENGTH(CRED_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS CRED_STATUS
                                ";
        
        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = "
CRED_ID_CREDITO_RECEBIDO,
CRED_DS_DOCUMENTO,
CRED_DT_EMISSAO,
CRED_DS_OBSERVACAO,
CRED_CD_UNID_GEST_EMITENTE,
CRED_CD_FONTE,
CRED_CD_PT_RESUMIDO,
CRED_CD_ELEMENTO_DESPESA_SUB,
CRED_NR_DESPESA,
CRED_CD_TIPO_NC,
CRED_VL_CREDITO_RECEBIDO,
CASE CRED_IC_ACERTADO_MANUAL
    WHEN 1 THEN 'Sim '
    ELSE 'Não '
END                             CRED_IC_ACERTADO_MANUAL,
CASE WHEN LENGTH(CRED_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS CRED_STATUS
                                ";
        
        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "
CRED_ID_CREDITO_RECEBIDO        AS \"Código do crédito\",
CRED_DS_DOCUMENTO               AS \"Documento gerador\",
CRED_DT_EMISSAO                 AS \"Data\",
CRED_DS_OBSERVACAO              AS \"Descrição\",
CRED_CD_UNID_GEST_EMITENTE      AS \"UG Emitente\",
CRED_CD_FONTE                   AS \"Fonte\",
CRED_CD_PT_RESUMIDO             AS \"PTRES\",
CRED_CD_ELEMENTO_DESPESA_SUB    AS \"Natureza\",
CRED_NR_DESPESA                 AS \"Despesa\",
CRED_CD_TIPO_NC                 AS \"Tipo de nota de crédito\",
CRED_VL_CREDITO_RECEBIDO        AS \"Valor\",
CASE CRED_IC_ACERTADO_MANUAL
    WHEN 1 THEN 'Sim '
    ELSE 'Não '
END                             AS \"Acertado manualmente\",
CASE WHEN LENGTH(CRED_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS \"Status do registro\"
                                ";
        
        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "CRED_ID_CREDITO_RECEBIDO, ";
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
        // Verifica os se esta na tela de excluidos
        $filtroIndex = Zend_Controller_Front::getInstance()->getRequest()->getActionName();

        if($filtroIndex == 'excluidos'){
            $filtro = 'AND CRED_CD_MATRICULA_EXCLUSAO IS Not Null';
        }else{
            $filtro = 'AND CRED_CD_MATRICULA_EXCLUSAO IS Null';
        }

        // Condição para index
        $restricao ['index'] = $filtro . " ORDER BY EXERCICIO ";

        // Condição para excluidos
        $restricao ['excluidos'] = $filtro . " ORDER BY DATA_EMISSAO DESC ";

        // Condição para ação editar
        $restricao [ 'detalhe' ] = " AND CRED_ID_CREDITO_RECEBIDO IN ( $chaves ) ";
        
        // Condição para ação editar
        $restricao [ 'editar' ] = $restricao [ 'detalhe' ];
        
        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'detalhe' ];
        
        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'detalhe' ];
        
        // Condição para montagem do combo
        $restricao [ 'combo' ] = " CRED_DH_EXCLUSAO_LOGICA IS Null ";
        
        return $restricao [ $acao ];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais esferas
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
        $creditos = $this->separaChave ( $chaves );
        
        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_CRED_CREDITO_RECEBIDO
SET
    CRED_CD_MATRICULA_EXCLUSAO          = '$matricula',
    CRED_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    CRED_ID_CREDITO_RECEBIDO            IN ( $creditos ) AND
    CRED_DH_EXCLUSAO_LOGICA             IS Null
                ";
        
        // Devolve a instrução sql para exclusão lógica
        return $sql;
    }

    /**
     * Restaura um ou mais registros logicamente excluídos
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para restauração de um ou mais
     *        registros
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlRestauracaoLogica ( $chaves )
    {
        // Trata a chave primária (ou composta)
        $creditos = $this->separaChave ( $chaves );
        
        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_CRED_CREDITO_RECEBIDO
SET
    CRED_CD_MATRICULA_EXCLUSAO          = Null,
    CRED_DH_EXCLUSAO_LOGICA             = Null
WHERE
    CRED_ID_CREDITO_RECEBIDO            IN ( $creditos ) AND
    CRED_DH_EXCLUSAO_LOGICA             IS NOT Null
                ";
        
        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaOpcoesGrid ()
    {
        // Personaliza a exibição dos campos no grid (index)
        $campo [ 'title' ] = 'Código';
        $detalhes [ 'CRED_ID_CREDITO_RECEBIDO' ] = $campo;
        
        $campo [ 'title' ] = 'Documento gerador';
        $detalhes [ 'CRED_DS_DOCUMENTO' ] = $campo;
        
        $campo [ 'title' ] = 'Data';
        $detalhes [ 'CRED_DT_EMISSAO' ] = $campo;
        
        $campo [ 'title' ] = 'Descrição';
        $detalhes [ 'CRED_DS_OBSERVACAO' ] = $campo;
        
        $campo [ 'title' ] = 'UG Emitente';
        $detalhes [ 'CRED_CD_UNID_GEST_EMITENTE' ] = $campo;
        
        $campo [ 'title' ] = 'Fonte';
        $detalhes [ 'CRED_CD_FONTE' ] = $campo;
        
        $campo [ 'title' ] = 'PTRES';
        $detalhes [ 'CRED_CD_PT_RESUMIDO' ] = $campo;
        
        $campo [ 'title' ] = 'UO';
        $detalhes [ 'UNOR_CD_UNID_ORCAMENTARIA' ] = $campo;

        $campo [ 'title' ] = 'Sigla';
        $detalhes [ 'PTRS_SG_PT_RESUMIDO' ] = $campo;
        
        $campo [ 'title' ] = 'Natureza';
        $campo [ 'format' ] = 'Naturezadespesa';
        $detalhes [ 'CRED_CD_ELEMENTO_DESPESA_SUB' ] = $campo;
        
        $campo [ 'title' ] = 'Despesa';
        $detalhes [ 'CRED_NR_DESPESA' ] = $campo;
        
        $campo [ 'title' ] = 'Tipo de nota de crédito';
        $detalhes [ 'CRED_CD_TIPO_NC' ] = $campo;
        
        $campo [ 'title' ] = 'Valor';
        $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'CRED_VL_CREDITO_RECEBIDO' ] = $campo;
        
        $campo = null;
        $campo [ 'title' ] = 'Acertado manualmente';
        $detalhes [ 'CRED_IC_ACERTADO_MANUAL' ] = $campo;
        
        $campo [ 'title' ] = 'Status';
        $detalhes [ 'CRED_STATUS' ] = $campo;
        
        // Personaliza a exibição dos campos adicionais no grid (inconsistencia)
        $campo [ 'title' ] = 'Inconsistência';
        $detalhes [ 'CRED_INCONSISTENCIA' ] = $campo;
        
        $campo [ 'title' ] = 'Ano (NC)';
        $detalhes [ 'CRED_ANO' ] = $campo;
        
        $campo [ 'title' ] = 'Ano (Desp)';
        $detalhes [ 'DESP_AA_DESPESA' ] = $campo;
        
        $campo [ 'title' ] = 'Fonte (Desp)';
        $detalhes [ 'DESP_CD_FONTE' ] = $campo;
        
        $campo [ 'title' ] = 'PTRES (Desp)';
        $detalhes [ 'DESP_CD_PT_RESUMIDO' ] = $campo;
        
        $campo [ 'title' ] = 'Natureza (Desp)';
        $campo [ 'format' ] = 'Naturezadespesa';
        $detalhes [ 'DESP_CD_ELEMENTO_DESPESA_SUB' ] = $campo;

        $campo [ 'title' ] = 'UO (NC)';
        $detalhes [ 'UNOR_CRED' ] = $campo;

        $campo [ 'title' ] = 'UO (Desp)';
        $detalhes [ 'UNOR_DESP' ] = $campo;

        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ( 'CRED_ID_CREDITO_RECEBIDO', 'DATA_EMISSAO', 'EXERCICIO' );
        
        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Efetua transformações no formulario, se aplicável
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function transformaFormulario ( $formulario, $acao )
    {
        // Define algumas variáveis...
        $incluir = Orcamento_Business_Dados::ACTION_INCLUIR;
        
        // Se for inclusão...
        if ( $acao == $incluir ) {
            // Remove campo que conterão valores padrão
            $formulario->removeElement ( 'CRED_ID_CREDITO_RECEBIDO' );
            // $formulario->removeElement ( 'CRED_DT_EMISSAO' );
        }
        
        return $formulario;
    }

    /**
     * Efetua transformações nos dados, se aplicável
     *
     * @param array $dados
     *        Dados do registro a ser transformado, se aplicável
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return array $dados
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function transformaDados ( $dados, $acao )
    {
        try {
            // Campo data deve ser formatado
            $data = $dados [ 'CRED_DT_EMISSAO' ];
            
            $formato = Trf1_Orcamento_Definicoes::FORMATO_DATA;
            $data = new Zend_Db_Expr ( "TO_DATE('$data', '$formato')" );
            
            $dados [ 'CRED_DT_EMISSAO' ] = $data;
            
            // Campo valor deve ser formatado
            $valorOld = $dados [ 'CRED_VL_CREDITO_RECEBIDO' ];
            
            $valor = new Trf1_Orcamento_Valor ();
            $valorNovo = $valor->retornaValorParaBancoRod ( $valorOld );
            $vl = new Zend_Db_Expr ( "TO_NUMBER(" . $valorNovo . ")" );
            
            $dados [ 'CRED_VL_CREDITO_RECEBIDO' ] = $vl;
            
            // Campo PTRES deve manter apenas o código
            $campoPTRES = $dados [ 'CRED_CD_PT_RESUMIDO' ];
            $infoPTRES = split ( '-', $campoPTRES );
            
            $ptres = null;
            if ( $infoPTRES ) {
                $ptres = trim ( $infoPTRES [ 0 ] );
            }
            
            $dados [ 'CRED_CD_PT_RESUMIDO' ] = $ptres;
            
            // Campo Natureza deve manter apenas o código
            $campoNatureza = $dados [ 'CRED_CD_ELEMENTO_DESPESA_SUB' ];
            $infoNatureza = split ( '-', $campoNatureza );
            
            $natureza = null;
            if ( $infoNatureza ) {
                $natureza = trim ( $infoNatureza [ 0 ] );
            }
            
            $dados [ 'CRED_CD_ELEMENTO_DESPESA_SUB' ] = $natureza;
            
            // Retorna os dados
            return $dados;
        } catch ( Exception $e ) {
            throw new Zend_Exception ( 'Erro na manipulação de dados' );
        }
    }

        /**
     * Retorna string contendo as relações (joins) com outras tabelas
     *
     * @return NULL
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaJoins ()
    {
        $join = "
Left JOIN 
	CEO_TB_DESP_DESPESA D ON
		D.DESP_NR_DESPESA = CRED_NR_DESPESA
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO			PTR2 ON
		PTR2.PTRS_CD_PT_RESUMIDO			= D.DESP_CD_PT_RESUMIDO
Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA       UNOR ON
        UNOR.UNOR_CD_UNID_ORCAMENTARIA = PTR2.PTRS_CD_UNID_ORCAMENTARIA
        ";
        return $join;
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
        
        // Id para combo - Sem combo para essa classe!
        // $id [ 'combo' ] = $cache->retornaID_Combo ( 'orcamento', $negocio );
        
        // Determina qual valor será retornado
        $retorno = ( $acao != null ? $id [ $acao ] : $id );
        
        // Devolve o id, conforme $acao informada
        return $retorno;
    }

    /**
     * Retorna a instrução sql que busca o valor agrupado dos créditos por
     * $despesa e $tipo de NC
     *
     * @param int $despesa
     *        Código da despesa
     * @param string $tipo
     *        Tipo de nota de crédito
     * @return string
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaSqlCreditos ( $despesa, $tipo )
    {
        if ( $despesa ) {
            $whereDespesa = " CRED_NR_DESPESA = $despesa AND ";
        } else {
            $whereDespesa = "";
        }
        
        $sql = "
SELECT
    CRED_NR_DESPESA,
    SUM(CRED_VL_CREDITO_RECEBIDO)   VALOR
FROM
    CEO_TB_CRED_CREDITO_RECEBIDO
WHERE
    $whereDespesa
    CRED_CD_TIPO_NC = '$tipo'
GROUP BY
    CRED_NR_DESPESA,
    CRED_CD_TIPO_NC
                ";
        
        // Devolve a instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de inconsistências entre os
     * dados um crédito e a respectiva despesa relacionada
     *
     * @param integer $ano
     *        Deve-se informar o ano para restringir os registros resultantes
     * @param boolean $filtra
     *        Filtra, ou não, registros manualmente acertados
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSql_CRED_INCONSISTENCIA ( $ano, $filtra = true )
    {
        if ( $ano != null ) {
            $condicaoAno = " TO_CHAR(CRED.CRED_DT_EMISSAO, 'YYYY') = $ano AND ";
        }
        
        if ( $filtra == true ) {
            $nao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
            $condicaoAcertos = " CRED.CRED_IC_ACERTADO_MANUAL = $nao AND ";
        }
        
        $sql = "
SELECT
    CRED.CRED_ID_CREDITO_RECEBIDO,
    CRED.CRED_NR_DESPESA,
    CASE WHEN TO_CHAR(CRED.CRED_DT_EMISSAO, 'YYYY') <> TO_CHAR(DESP.DESP_AA_DESPESA) THEN 'Ano; ' ELSE '' END ||
    CASE WHEN CRED.CRED_CD_FONTE <> DESP.DESP_CD_FONTE THEN 'Fonte; ' ELSE '' END ||
    CASE WHEN CRED.CRED_CD_PT_RESUMIDO <> DESP.DESP_CD_PT_RESUMIDO THEN 'PTRES; ' ELSE '' END ||
    CASE WHEN SUBSTR(CRED.CRED_CD_ELEMENTO_DESPESA_SUB, 0, 6) <> SUBSTR(DESP.DESP_CD_ELEMENTO_DESPESA_SUB, 0, 6) THEN 'Natureza; ' ELSE '' END AS CRED_INCONSISTENCIA,
    TO_CHAR(CRED.CRED_DT_EMISSAO, 'YYYY')   AS CRED_ANO,
    TO_CHAR(DESP.DESP_AA_DESPESA)           AS DESP_AA_DESPESA,
    CRED.CRED_CD_FONTE,
    DESP.DESP_CD_FONTE,
    CRED.CRED_CD_PT_RESUMIDO,
    DESP.DESP_CD_PT_RESUMIDO,
    UNOR.UNOR_CD_UNID_ORCAMENTARIA AS UNOR_DESP,
    UNOR2.UNOR_CD_UNID_ORCAMENTARIA AS UNOR_CRED,
    CRED.CRED_CD_ELEMENTO_DESPESA_SUB,
    DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
    CASE CRED_IC_ACERTADO_MANUAL
        WHEN 1 THEN 'Sim '
        ELSE 'Não '
    END                                     AS CRED_IC_ACERTADO_MANUAL,
        CASE WHEN LENGTH(CRED_CD_MATRICULA_EXCLUSAO) > 0
        THEN 'Excluído '
        ELSE 'Ativo'
    END                                     AS CRED_STATUS
FROM
    CEO_TB_CRED_CREDITO_RECEBIDO CRED
LEFT JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = CRED.CRED_NR_DESPESA
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO P ON
		P.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO

Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON
        UNOR.UNOR_CD_UNID_ORCAMENTARIA = P.PTRS_CD_UNID_ORCAMENTARIA

Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO P2 ON
		P2.PTRS_CD_PT_RESUMIDO = CRED.CRED_CD_PT_RESUMIDO

Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR2 ON
        UNOR2.UNOR_CD_UNID_ORCAMENTARIA = P2.PTRS_CD_UNID_ORCAMENTARIA

WHERE
$condicaoAno
$condicaoAcertos
    (
        TO_CHAR(CRED.CRED_DT_EMISSAO, 'YYYY') <> TO_CHAR(DESP.DESP_AA_DESPESA) OR
        CRED.CRED_CD_FONTE <> DESP.DESP_CD_FONTE OR
        CRED.CRED_CD_PT_RESUMIDO <> DESP.DESP_CD_PT_RESUMIDO OR
        SUBSTR(CRED.CRED_CD_ELEMENTO_DESPESA_SUB, 0, 6) <> SUBSTR(DESP.DESP_CD_ELEMENTO_DESPESA_SUB, 0, 6)
    )
                ";

        // Devolve a instrução sql
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de inconsistências entre os
     * dados um crédito e a respectiva despesa relacionada
     *
     * @param integer $ano
     *        Deve-se informar o ano para restringir os registros resultantes
     * @param boolean $filtra
     *        Filtra, ou não, registros manualmente acertados
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaInconsistencias ( $ano, $filtra = true )
    {
        // Retorna instrução sql
        $sql = $this->retornaSql_CRED_INCONSISTENCIA ( $ano, $filtra );
        
        $dados = null;
        
        try {
            $banco = Zend_Db_Table::getDefaultAdapter ();
            $dados = $banco->fetchAll ( $sql );
        } catch ( Exception $e ) {
            $msg = 'Erro ao gerar a listagem de inconsistências';
            throw new Zend_Exception ( $msg );
        }
        
        // Devolve os dados
        return $dados;
    }

}