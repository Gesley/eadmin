<?php
/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as regras negociais sobre solicitação de ajuste
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Solicitacaoajuste
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Solicitacaoajuste extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Sola ();

        // Define a negocio
        $this->_negocio = 'solicitacaoajuste';
    }

    public function incluir($dados) {

        // Converte os valores do fomulário para o formato do banco oracle
        $dados['SOLA_VL_PROPOSTA_ORIGINAL'] = App_Util::formatamodedaorcamento( $dados['SOLA_VL_PROPOSTA_ORIGINAL'] );
        $dados['SOLA_VL_SOLICITADO']        = App_Util::formatamodedaorcamento( $dados['SOLA_VL_SOLICITADO'] );
        $dados['SOLA_VL_ATENDIDO']          = App_Util::formatamodedaorcamento( $dados['SOLA_VL_ATENDIDO'] );
        
        // Formata a data para o banco oracle
        $dados['SOLA_DT_SOLICITACAO'] = new Zend_Db_Expr('SYSDATE');

        // Remove o campo id
        unset( $dados["SOLA_ID_SOLICITACAO_AJUSTE"] );
        
        // Fix bug enviar
        unset( $dados["Enviar"] );

        // inclusao      
        return parent::incluir( $dados );
    }
    
    public function editar($dados) {


        // Converte os valores do fomulário para o formato do banco oracle        
        $dados['SOLA_VL_PROPOSTA_ORIGINAL'] = App_Util::formatamodedaorcamento( $dados['SOLA_VL_PROPOSTA_ORIGINAL'] );
        $dados['SOLA_VL_SOLICITADO']        = App_Util::formatamodedaorcamento( $dados['SOLA_VL_SOLICITADO'] );
        $dados['SOLA_VL_ATENDIDO']          = App_Util::formatamodedaorcamento( $dados['SOLA_VL_ATENDIDO'] );
        
        // Remove a data 
        unset( $dados["SOLA_DT_SOLICITACAO"] );

        // Fix bug enviar
        unset( $dados["Enviar"] );

        // edição      
        return parent::editar( $dados );        
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
        $campos [ 'index' ] = "
        SOLA_ID_SOLICITACAO_AJUSTE,
        DESP_AA_DESPESA,
        SOLA_NR_DESPESA,
        DESP_CD_UG,
        JUST_DS_TITULO,
        SOLA_DS_JUSTIFICATIVA_RETORNO,
        RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS SG_FAMILIA_RESPONSAVEL,
        SOLA_DT_SOLICITACAO,
        
        CASE
        WHEN NVL(VLD2.VLDE_VL_DESPESA, 0) > 0 THEN VLD2.VLDE_VL_DESPESA /* Se o valor for editado manualmente, prevalece ele */
        WHEN NVL(VLD6.VLDE_VL_DESPESA, 0) + NVL(VLD9.VLDE_VL_DESPESA, 0) > 0 THEN NVL(VLD6.VLDE_VL_DESPESA, 0) + NVL(VLD6.VLDE_VL_DESPESA, 0) * (NVL(VLD9.VLDE_VL_DESPESA, 0) / NVL(VLD6.VLDE_VL_DESPESA, 0) * 100) / 100 + NVL(SOLA.SOLA_VL_ATENDIDO, 0)
        ELSE NVL(SOLA.SOLA_VL_ATENDIDO, 0)
        END AS VL_DESPESA_DIPLA,
        SOLA_VL_SOLICITADO,
        SOLA_VL_ATENDIDO,        
        
        CASE SOLA_IC_SITUACAO
        WHEN '0' THEN 'Em definição  '
            WHEN '1' THEN 'Atendida  '
            WHEN '2' THEN 'Recusada  '   
        END AS SOLA_IC_SITUACAO,
        CASE SOLA_TP_SOLICITACAO
            WHEN 1 THEN 'Solicitação de Acréscimo       '
            WHEN 0 THEN 'Solicitação de Ajuste      '
        END AS SOLA_TP_SOLICITACAO,
CASE WHEN LENGTH(SOLA_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS SOLA_STATUS        
                                ";

        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = "*";

        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "
        SOLA_ID_SOLICITACAO_AJUSTE         AS \"Código\",
        DESP_AA_DESPESA                    AS \"Exercicio\",
        SOLA_NR_DESPESA                    AS \"Despesa\",
        DESP_CD_UG                         AS \"UG\",
        JUST_DS_TITULO                     AS \"Justificativa Padronizada\",
        SOLA_DS_NOVA_JUSTIFICATIVA         AS \"Descrição Justificativa\",
        SOLA_DS_JUSTIFICATIVA_RETORNO      AS \"Justificativa Setorial\",
        SOLA_DT_SOLICITACAO                AS \"Data da Solicitação\",        
        SOLA_VL_SOLICITADO                 AS \"Valor Solicitado\",
        SOLA_VL_SOLICITADO                 AS \"Valor solicitado\",

        SOLA_VL_ATENDIDO                   AS \"Valor Atendido\",  

        CASE SOLA_IC_SITUACAO
            WHEN '0' THEN 'Em definição  '
            WHEN '1' THEN 'Atendida   '
            WHEN '2' THEN 'Recusada   '
        END AS \"Situação da solicitação\",
        CASE SOLA_TP_SOLICITACAO
            WHEN 0 THEN 'Solicitação de Ajuste      '
            WHEN 1 THEN 'Solicitação de Acréscimo       '
        END AS \"Tipo da solicitação\"
        ";

        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "SOLA_ID_SOLICITACAO_AJUSTE, ";
        $campos [ 'excluir' ] .= "
        SOLA_ID_SOLICITACAO_AJUSTE         AS \"Código\",
        DESP_AA_DESPESA                    AS \"Exercicio\",
        SOLA_NR_DESPESA                    AS \"Despesa\",
        DESP_CD_UG                         AS \"UG\",
        JUST_DS_TITULO                     AS \"Justificativa Padronizada\",
        SOLA_DS_NOVA_JUSTIFICATIVA         AS \"Descrição Justificativa\",
        SOLA_DS_JUSTIFICATIVA_RETORNO      AS \"Justificativa Setorial\",
        SOLA_DT_SOLICITACAO                AS \"Data da Solicitação\",
        VL_DESPESA_DIPLA                   AS \"Ajuste setorial\",
        SOLA_VL_SOLICITADO       AS \"Solicitado pelo responsavel\",
        SOLA_VL_SOLICITADO                 AS \"Valor Solicitado\",
        SOLA_VL_ATENDIDO                   AS \"Valor Atendido\",
        CASE SOLA_IC_SITUACAO
            WHEN '0' THEN 'Em definição  '
            WHEN '1' THEN 'Atendida   '
            WHEN '2' THEN 'Recusada   '
        END AS \"Situação da solicitação\",
        CASE SOLA_TP_SOLICITACAO
            WHEN 0 THEN 'Solicitação de Ajuste      '
            WHEN 1 THEN 'Solicitação de Acréscimo       '
        END AS \"Tipo da solicitação\"
        ";

        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = "SOLA_ID_SOLICITACAO_AJUSTE, ";
        $campos [ 'restaurar' ] .= " 
        SOLA_ID_SOLICITACAO_AJUSTE         AS \"Código\",
        -- DESP_AA_DESPESA                    AS \"Exercicio\",
        SOLA_NR_DESPESA                    AS \"Despesa\",
        DESP_CD_UG                         AS \"UG\",
        --JUST_DS_DESCRICAO                  AS \"Justificativa Padronizada\",
        SOLA_DS_NOVA_JUSTIFICATIVA         AS \"Descrição Justificativa\",
        SOLA_DS_JUSTIFICATIVA_RETORNO      AS \"Justificativa Setorial\",
        SOLA_DT_SOLICITACAO                AS \"Data da Solicitação\",        
        VL_DESPESA_DIPLA                   AS \"Ajuste setorial\",
        SOLA_VL_SOLICITADO       AS \"Solicitado pelo responsavel\",
        SOLA_VL_SOLICITADO                 AS \"Valor Solicitado\",
        SOLA_VL_ATENDIDO                   AS \"Valor Atendido\",
        CASE SOLA_IC_SITUACAO
            WHEN '0' THEN 'Em definição  '
            WHEN '1' THEN 'Atendida   '
            WHEN '2' THEN 'Recusada   '
        END AS \"Situação da solicitação\",
        CASE SOLA_TP_SOLICITACAO
            WHEN 0 THEN 'Solicitação de Ajuste      '
            WHEN 1 THEN 'Solicitação de Acréscimo       '
        END AS \"Tipo da solicitação\"
         ";
        
        // Devolve os campos, conforme ação
        return $campos [ $acao ];
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
                'SOLA_ID_SOLICITACAO_AJUSTE' => array ( 'title' => 'Código', 'abbr' => '' ),
                'DESP_AA_DESPESA' => array ( 'title' => 'Exercicio', 'abbr' => '' ),
                'SOLA_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ),
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ),
                'JUST_DS_TITULO' => array ( 'title' => 'Justificativa', 'abbr' => '' ),
                'SOLA_DS_JUSTIFICATIVA_RETORNO' => array ( 'title' => 'Justificativa retorno', 'abbr' => '' ),
                'SG_FAMILIA_RESPONSAVEL'  => array ( 'title' => 'Responsavel', 'abbr' => '' ),
                'SOLA_DT_SOLICITACAO' => array ( 'title' => 'Data solicitaão', 'abbr' => '' ),
                
                'VL_DESPESA_DIPLA' => array ( 'title' => 'Ajuste setorial', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
                'SOLA_VL_SOLICITADO' => array ( 'title' => 'Valor Solicitado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
                'SOLA_VL_ATENDIDO' => array ( 'title' => 'Valor atendido', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
                
                'SOLA_TP_SOLICITACAO' => array ( 'title' => 'Tipo da solicitacao', 'abbr' => '' ),
                'SOLA_IC_SITUACAO' => array ( 'title' => 'Situação', 'abbr' => 'Informa se o registro está ou não ativo') ,
                'SOLA_STATUS' => array ( 'title' => 'Status', 'abbr' => 'Status da solicitação' ) );

        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ();
        $opcoes ['ocultos'] = array('JUST_DT_EXCLUSAO_LOGICA', 'JUST_CD_MATRICULA_EXCLUSAO');

        // Devolve o array de opções
        return $opcoes;
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
            CEO_TB_JUST_JUSTIFICATIVA JUST ON
                SOLA.SOLA_ID_JUSTIFICATIVA = JUST.JUST_ID_JUSTIFICATIVA
        Left JOIN
            CEO_TB_DESP_DESPESA DESP ON
                DESP.DESP_NR_DESPESA = SOLA.SOLA_NR_DESPESA   
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD2 ON 
            VLD2.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD2.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_DIPLA_AJUSTE_POS_RESPONSAVEL . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD6 ON VLD6.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD6.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_BASE_ANO_ATUAL . "            
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD8 ON 
            VLD8.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD8.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_SOLIC_RESPONSAVEL . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD9 ON 
            VLD9.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD9.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_PROPOSTA_ATUAL . "            
        Left JOIN
            CEO_TB_RESP_RESPONSAVEL RSP ON
                RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
        Left JOIN
            RH_CENTRAL_LOTACAO RHCL ON
                RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
                RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO            
        ";
        
        $joins [ 'detalhe' ] = $joins [ 'index' ];

        
        $joins [ 'editar' ] = "
        Left JOIN
            CEO_TB_JUST_JUSTIFICATIVA JUST ON
                SOLA.SOLA_ID_JUSTIFICATIVA = JUST.JUST_ID_JUSTIFICATIVA
        Left JOIN
            CEO_TB_DESP_DESPESA DESP ON
            DESP.DESP_NR_DESPESA = SOLA.SOLA_NR_DESPESA
        ";        

        $joins [ 'excluir' ] = $joins[ 'index' ];

        $joins [ 'restaurar ' ] = "
        Left JOIN
            CEO_TB_JUST_JUSTIFICATIVA JUST ON
                SOLA.SOLA_ID_JUSTIFICATIVA = JUST.JUST_ID_JUSTIFICATIVA
        Left JOIN
            CEO_TB_DESP_DESPESA DESP ON
            DESP.DESP_NR_DESPESA = SOLA.SOLA_NR_DESPESA
        Left JOIN
            CEO_TB_RESP_RESPONSAVEL RSP ON
                RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
        Left JOIN
            RH_CENTRAL_LOTACAO RHCL ON
                RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
                RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO  
         ";

        // Devolve os joins encontrados
        return $joins [ $acao ];
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
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaRestricoes ( $acao = 'todos', $chaves = null )
    {

        // Filtra solicitações por ug
        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        // Verifica os se esta na tela de excluidos
        $filtroIndex = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        
        if($filtroIndex == 'excluidos'){
            $filtro = ' AND SOLA_CD_MATRICULA_EXCLUSAO IS Not Null ';
        }else{
            $filtro = ' AND SOLA_CD_MATRICULA_EXCLUSAO IS Null ';
        }
       
        // Condição para excluidos
        $restricao ['excluidos'] = $filtro;

        if( $condicaoResponsaveis != ""){
            $restricao [ 'index' ] = $filtro . $condicaoResponsaveis;
        }else{
            $restricao [ 'index' ] = $filtro;
        }
        
        // Condição para ação editar
        $restricao [ 'detalhe' ] = " AND SOLA_ID_SOLICITACAO_AJUSTE IN ( $chaves ) ";

        // Condição para ação editar
        $restricao [ 'editar' ] = $restricao [ 'detalhe' ];

        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'detalhe' ];

        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'detalhe' ];


        // Condição para montagem do combo
        $restricao [ 'combo' ] = "";

        return $restricao [ $acao ];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais solicitacaoajustes
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlExclusaoLogica ( $chaves )
    {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula ();

        // Trata a chave primária (ou composta)
        $solicitacaoajustes = $this->separaChave ( $chaves );

        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_SOLA_SOLICITACAO_AJUSTE
SET
    SOLA_CD_MATRICULA_EXCLUSAO          = '$matricula',
    SOLA_DT_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    SOLA_ID_SOLICITACAO_AJUSTE                      IN ( $solicitacaoajustes ) AND
    SOLA_DT_EXCLUSAO_LOGICA             IS Null
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
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlRestauracaoLogica ( $chaves )
    {
        // Trata a chave primária (ou composta)
        $solicitacaoajustes = $this->separaChave ( $chaves );

        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_SOLA_SOLICITACAO_AJUSTE
SET
    SOLA_CD_MATRICULA_EXCLUSAO          = Null,
    SOLA_DT_EXCLUSAO_LOGICA             = Null
WHERE
    SOLA_ID_SOLICITACAO_AJUSTE                      IN ( $solicitacaoajustes ) AND
    SOLA_DT_EXCLUSAO_LOGICA              IS NOT Null
        ";

        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
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
     * retorna o tipo da solicitacao
     * @param int id $solicitacao
     * @return array
     */
    public function retornaTipo( $solicitacao )
    {
        $sql = "SELECT
            CASE SOLA_TP_SOLICITACAO
                        WHEN 0 THEN 'Solicitação de Ajuste    '
                        WHEN 1 THEN 'Solicitação de Acréscimo   '
                    END AS SOLA_TP_SOLICITACAO
            FROM CEO_TB_SOLA_SOLICITACAO_AJUSTE WHERE SOLA_ID_SOLICITACAO_AJUSTE = $solicitacao";
        $banco = Zend_Db_Table::getDefaultAdapter ();
        return $banco->fetchRow ( $sql );
    }

}
