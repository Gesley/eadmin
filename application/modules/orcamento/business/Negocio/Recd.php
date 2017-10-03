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
 * @package Orcamento_Business_Negocio_Recd
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Recd extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Recd ();

        // Classe de tratamento de valores
        $this->_trataValor = new Trf1_Orcamento_Valor ();

        // Define a negocio
        $this->_negocio = 'recd';

    }

    /**
     * Trata os dados antes de serem gravados
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirRecursoNovaDespesa($chavePrimaria, $dados) {
        // Trata o valor atendido
        $valorAtendido = $this->_trataValor->retornaValorParaBancoRod ( $dados ["SOLD_VL_ATENDIDO"] );
        $dados ["SOLD_VL_ATENDIDO"] = new Zend_Db_Expr ( "TO_NUMBER(" . $valorAtendido . ")" );

        // configura os dados a serem gravados
        $recurso = array(
            'RECD_NR_DESPESA'       => $dados['SOLD_NR_DESPESA'],
            'RECD_DS_JUSTIFICATIVA' => $dados['SOLD_DS_JUSTIFICATIVA_SOLICIT'],
            'RECD_VL_RECURSO'       => $dados['SOLD_VL_ATENDIDO'],
            'RECD_DT_RECURSO'       => new Zend_Db_Expr ( 'SYSDATE' ), // trata a data
            'RECD_IC_RECURSO'       => $dados['SOLD_IC_REC_DESCENTRALIZADO'],
            'RECD_NR_SOLICITACAO'   => $chavePrimaria
            );

        return $resultado = parent::incluir ( $recurso );
    }

    public function validaInclusao ( $dados )
    {
        $resultado [ 'sucesso' ] = true;

        if( $qtdeRegistros == 1 ) {
            $resultado [ 'sucesso' ] = false;
            $resultado [ 'msgErro' ] = Orcamento_Business_Dados::MSG_DUPLICIDADEREGRA_ERRO;
        }

        return $resultado;
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
            $formulario->removeElement ( 'REC_CD_RECURSO' );
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
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function transformaDados ( $dados, $acao = null )
    {

        return $dados;
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
        $campos [ 'index' ] = "
                                                    RECD_CD_RECURSO,
                                                    RECD_NR_DESPESA,
                                                    RECD_DS_JUSTIFICATVA,
                                                    RECD_VL_RECURSO,
                                                    RECD_DT_RECURSO
                                                ";

        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = $campos [ 'index' ];

        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ]  = $campos [ 'index' ];

        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "RECD_CD_RECURSO, ";
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
        $restricao [ 'detalhe' ] = " AND RECD_CD_RECURSO IN ( $chaves ) ";

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
     * Realiza a exclusão lógica de uma ou mais regras
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlExclusaoLogica ( $chaves )
    {
        // Retorna a matrícula do usuário logado
        $matricula = $this->retornaMatricula ();

        // Trata a chave primária (ou composta)
        $regras = $this->separaChave ( $chaves );

        // Exclui um ou mais registros
        $sql = "
                        UPDATE
                            CEO_TB_RECD_RECURSO_DESCENT
                        SET
                            RECD_CD_MATRICULA_EXCLUSAO            = '$matricula',
                            RECD_DH_EXCLUSAO_LOGICA                     = SYSDATE
                        WHERE
                            REC_CD_RECURSO                      IN ( $regras )
                            AND RECD_DH_EXCLUSAO_LOGICA             IS Null
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
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaSqlRestauracaoLogica ( $chaves )
    {
        // Trata a chave primária (ou composta)
        $regras = $this->separaChave ( $chaves );

        // Restaura um ou mais registros
        $sql = "
                       UPDATE
                           CEO_TB_RECD_RECURSO_DESCENT
                       SET
                           RECD_CD_MATRICULA_EXCLUSAO          = Null,
                       WHERE
                           RECD_CD_RECURSO                      IN ( $regras )
                     ";

        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaOpcoesGrid ()
    {
        // Personaliza a exibição dos campos no grid
        $detalhes = array (
            'RECD_CD_RECURSO' => array ( 'title' => 'Recurso', 'abbr' => '' ),
            'RECD_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ),
            'RECD_DS_JUSTIFICATVA' => array ( 'title' => 'Descrição', 'abbr' => '' ),
            'RECD_VL_RECURSO' => array ( 'title' => 'Valor', 'abbr' => '' ),
            'RECD_DT_RECURSO' => array ( 'title' => 'Data do recurso', 'abbr' => '' ),
            'RECD_CD_MATRICULA_EXCLUSAO' => array ( 'title' => 'Status', 'abbr' => 'Informa se o registro foi ou não excluído' ) );

        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ( 'RECD_CD_RECURSO' );

        // Devolve o array de opções
        return $opcoes;
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

    public function excluirRecurso ($cod) {
        $sql = "
            DELETE FROM CEO_TB_RECD_RECURSO_DESCENT
            WHERE RECD_CD_RECURSO = '$cod'
        ";
        return $resultado = $this->executaQuery ( $sql, true );
    }

    public function atualizaRecurso ($chavePrimaria, $dados) {

        $valorAtendido = $this->_trataValor->retornaValorParaBancoRod ( $dados ["SOLD_VL_ATENDIDO"] );
        $dados ["SOLD_VL_ATENDIDO"] = new Zend_Db_Expr ( "TO_NUMBER(" . $valorAtendido . ")" );

        $recurso = array(
            'RECD_CD_RECURSO'      => $dados['SOLD_NR_REC_DESCENTRALIZAR'],
            'RECD_NR_DESPESA'      => $dados['SOLD_NR_DESPESA'],
            'RECD_DS_JUSTIFICATVA' => $dados['SOLD_DS_JUSTIFICATIVA_SOLICIT'],
            'RECD_VL_RECURSO'      => $dados['SOLD_VL_ATENDIDO'],
            'RECD_IC_RECURSO'      => $dados['SOLD_IC_REC_DESCENTRALIZADO'],
            'RECD_NR_SOLICITACAO' => $chavePrimaria
            );

        return $resultado = parent::editar ( $recurso );
    }

 }
