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
 * Contém as regras negociais sobre recurso a descentralizar
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Novadespesa
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Soldajuste extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Soldaj ();

        // Define a negocio
        $this->_negocio = 'soldaj';

    }


    /**
     * Efetua transformações no formulario, se aplicável
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function transformaFormulario ( $formulario, $acao )
    {
        if( $acao == Orcamento_Business_Dados::ACTION_INCLUIR ) {
            $formulario->removeElement ( 'SOLD_NR_SOLICITACAO' );
        }

        return $formulario;
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCampos ( $acao = 'todos' )
    {
        // Campos para a serem apresentados na indexAction
        $campos [ 'todos' ] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos [ 'index' ] = " * ";

        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = $campos [ 'index' ];

        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ]  = $campos [ 'index' ];

        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "SOLD_NR_SOLICITACAO, ";
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
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaRestricoes ( $acao = 'todos', $chaves = null )
    {
        // Condição para ação editar
        $restricao [ 'detalhe' ] = " AND SOLD_NR_SOLICITACAO IN ( $chaves ) ";

        // Condição para ação editar
        $restricao [ 'editar' ] = $restricao [ 'detalhe' ];

        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'detalhe' ];

        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'detalhe' ];

        // Condição para montagem do combo
        $restricao [ 'combo' ] = " RGEX_CD_MATRICULA_EXCLUSAO IS Null ";

        return $restricao [ $acao ];
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
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

    public function atualizaSolicitacao( $idRecd, $idSold )
    {
        $sql = "
            UPDATE
                CEO_TB_SOLD_SOLIC_DESPESA
            SET
                SOLD_NR_REC_DESCENTRALIZAR = $idRecd
            WHERE
                SOLD_NR_SOLICITACAO = $idSold
            AND
                SOLD_NR_REC_DESCENTRALIZAR IS NULL

        ";

        $resultado = $this->executaQuery ( $sql, true );
        return $resultado;
    }

    public function editaSolicitacao($idSold) {
        $sql = "
            UPDATE
                CEO_TB_SOLD_SOLIC_DESPESA
            SET
                SOLD_NR_REC_DESCENTRALIZAR = NULL
            WHERE
                SOLD_NR_SOLICITACAO = $idSold
        ";

            $resultado = $this->executaQuery ( $sql, true );
            return $resultado;
    }


 }
