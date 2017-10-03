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
 * Contém as regras negociais sobre movimentações de crédito e suas solicitações
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Movimentacaocred
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Movimentacaocred extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Movc ();
        
        // Define a negocio
        $this->_negocio = 'novamovimentacaocred';
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCampos ( $acao = 'todos' )
    {
        // Campos para a serem apresentados na indexAction
        $campos [ 'todos' ] = " * ";
        
        // Campos para a serem apresentados na indexAction
        $campos [ 'index2' ] = "
MOVC_CD_MOVIMENTACAO,
MOVC_ID_TIPO_MOVIMENTACAO,
MOVC_DH_MOVIMENTACAO,
MOVC_VL_MOVIMENTACAO,
MOVC_IC_MOVIMENT_REPASSADA,
MOVC_NR_DESPESA_ORIGEM,
MOVC_NR_DESPESA_DESTINO,
MOVC_CD_TIPO_SOLICITACAO,
MOVC_DS_JUSTIF_SOLICITACAO,
MOVC_DS_JUSTIF_SECOR,
CASE WHEN LENGTH(MOVC_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS ESFE_STATUS
-- MOVC_CD_MATRICULA_EXCLUSAO,
-- MOVC_DH_EXCLUSAO_LOGICA,
-- MOVC_NR_CEO
                                ";
        
        // Campos em teste
        $campos [ 'index' ] = "
MOVC.MOVC_CD_MOVIMENTACAO,
D1.DESP_AA_DESPESA,
D1.DESP_CD_UG,
MOVC.MOVC_NR_DESPESA_ORIGEM,
D1.DESP_CD_PT_RESUMIDO
        AS PTRES_ORIGEM,
D1.DESP_CD_ELEMENTO_DESPESA_SUB
        AS NATUREZA_ORIGEM,
RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO)
        AS RESPONSAVEL_ORIGEM,
MOVC.MOVC_NR_DESPESA_DESTINO,
D2.DESP_CD_PT_RESUMIDO
        AS PTRES_DESTINO,
D2.DESP_CD_ELEMENTO_DESPESA_SUB
        AS NATUREZA_DESTINO,
RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO)
        AS RESPONSAVEL_DESTINO,
TO_CHAR(MOVC.MOVC_DH_MOVIMENTACAO, '" .
         Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA . "')
         AS MOVC_DH_MOVIMENTACAO,
T.TSOL_DS_TIPO_SOLICITACAO,
/* DECODE(MOVC.MOVC_IC_MOVIMENT_REPASSADA, 1, 'Sim', 0, 'Não') AS MOVC_IC_MOVIMENT_REPASSADA, */
MOVC.MOVC_VL_MOVIMENTACAO
         AS MOVC_VL_MOVIMENTACAO,
CASE WHEN LENGTH(MOVC_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS MOVC_STATUS
         
                               ";
        
        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = $campos [ 'index' ];
        
        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "
                                 ";
        
        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "MOVC_CD_MOVIMENTACAO, ";
        $campos [ 'excluir' ] .= $campos [ 'detalhe' ];
        
        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = $campos [ 'excluir' ];
        
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
        $restricao [ 'editar' ] = " AND ESFE_CD_ESFERA IN ( $chaves ) ";
        
        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'editar' ];
        
        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'editar' ];
        
        // Devolve as restrições encontradas
        return $restricao [ $acao ];
    }

    /**
     * Retorna string contendo as relações (joins) com outras tabelas
     *
     * @param string $acao
     *        Nome da ação (action) em questão
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaJoins ( $acao )
    {
        $joins [ 'index' ] = "
Left JOIN
    CEO_TB_TSOL_TIPO_SOLICITACAO    T ON
        T.TSOL_CD_TIPO_SOLICITACAO  = MOVC.MOVC_CD_TIPO_SOLICITACAO
Left JOIN
    CEO_TB_DESP_DESPESA             D1 ON
        D1.DESP_NR_DESPESA          = MOVC.MOVC_NR_DESPESA_ORIGEM
Left JOIN
    CEO_TB_RESP_RESPONSAVEL         RSP1 ON
        RSP1.RESP_CD_RESPONSAVEL    = D1.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO              RHC1 ON
        RHC1.LOTA_COD_LOTACAO       = RSP1.RESP_CD_LOTACAO AND
        RHC1.LOTA_SIGLA_SECAO       = RSP1.RESP_DS_SECAO
Left JOIN
    CEO_TB_DESP_DESPESA             D2 ON
        D2.DESP_NR_DESPESA          = MOVC.MOVC_NR_DESPESA_DESTINO
Left JOIN
    CEO_TB_RESP_RESPONSAVEL         RSP2 ON
        RSP2.RESP_CD_RESPONSAVEL    = D2.DESP_CD_RESPONSAVEL
Left JOIN
    RH_CENTRAL_LOTACAO              RHC2 ON
        RHC2.LOTA_COD_LOTACAO       = RSP2.RESP_CD_LOTACAO AND
        RHC2.LOTA_SIGLA_SECAO       = RSP2.RESP_DS_SECAO
                              ";
        
        // Devolve os joins encontrados
        return $joins [ $acao ];
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
        $movimentacoes = $this->separaChave ( $chaves );
        
        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_MOVC_MOVIMENTACAO_CRED
SET
    MOVC_CD_MATRICULA_EXCLUSAO          = '$matricula',
    MOVC_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    MOVC_CD_MOVIMENTACAO                IN ( $movimentacoes ) AND
    MOVC_DH_EXCLUSAO_LOGICA             IS Null
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
        $esferas = $this->separaChave ( $chaves );
        
        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_MOVC_MOVIMENTACAO_CRED
SET
    MOVC_CD_MATRICULA_EXCLUSAO          = Null,
    MOVC_DH_EXCLUSAO_LOGICA             = Null
WHERE
    ESFE_CD_ESFERA                      IN ( $esferas ) AND
    ESFE_DH_EXCLUSAO_LOGICA             IS NOT Null
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
        // Personaliza a exibição dos campos no grid
        $detalhes = array ( 
                'MOVC_CD_MOVIMENTACAO' => array ( 'title' => 'Código', 
                        'abbr' => '' ), 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'MOVC_NR_DESPESA_ORIGEM' => array ( 
                        'title' => 'Despesa de origem', 'abbr' => '' ), 
                'PTRES_ORIGEM' => array ( 'title' => 'PTRES (Origem)', 
                        'abbr' => '' ), 
                'NATUREZA_ORIGEM' => array ( 'title' => 'Natureza (Origem)', 
                        'abbr' => '' ), 
                'RESPONSAVEL_ORIGEM' => array ( 
                        'title' => 'Responsável (Origem)', 'abbr' => '' ), 
                'MOVC_NR_DESPESA_DESTINO' => array ( 
                        'title' => 'Despesa de destino', 'abbr' => '' ), 
                'PTRES_DESTINO' => array ( 'title' => 'PTRES (Destino)', 
                        'abbr' => '' ), 
                'NATUREZA_DESTINO' => array ( 'title' => 'Natureza (Destino)', 
                        'abbr' => '' ), 
                'RESPONSAVEL_DESTINO' => array ( 
                        'title' => 'Responsável (Destino)', 'abbr' => '' ), 
                'MOVC_DH_MOVIMENTACAO' => array ( 'title' => 'Data', 
                        'abbr' => '' ), 
                'MOVC_VL_MOVIMENTACAO' => array ( 'title' => 'Valor', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ), 
                'TSOL_DS_TIPO_SOLICITACAO' => array ( 
                        'title' => 'Status da solicitação', 'abbr' => '' ),
                'MOVC_STATUS' => array ( 
                        'title' => 'Status', 'abbr' => '' ) );
        
        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        
        // Devolve o array de opções
        return $opcoes;
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

}