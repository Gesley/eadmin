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
 * Contém as regras negociais sobre perspectiva
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Esfera
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>  
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Perspectiva extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>  
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Pers ();
        
        // Define a negocio
        $this->_negocio = 'perspectiva';
    }

    public function incluir($dados) {
        // bugfix do id
        unset($dados ["PERS_ID_PERSPECTIVA"]);
        unset($dados ["Enviar"]);
        return parent::incluir($dados);
    }

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @return  array
     */
    public function retornaCombo ()
    {

            // Retorna instrução sql para listagem dos dados
            $sql = "
            SELECT
                PERS_ID_PERSPECTIVA,
                PERS_TX_PERSPECTIVA
            FROM CEO_TB_PERS_PERSPECTIVA
            WHERE PERS_DH_EXCLUSAO_LOGICA IS NULL
                    ";

            // Retorna default adapter de banco
            $banco = Zend_Db_Table::getDefaultAdapter ();

            // Retorna todos os registros e campos da instrução sql
            $dados = $banco->fetchPairs ( $sql );

            // Devolve os dados
            return $dados;
    }
    
    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>  
     */
    public function retornaCampos ( $acao = 'todos' )
    {
        // Campos para a serem apresentados na indexAction
        $campos [ 'todos' ] = " * ";
        
        // Campos para a serem apresentados na indexAction
        $campos [ 'index' ] = "
PERS_ID_PERSPECTIVA,
PERS_TX_PERSPECTIVA,
PERS_AA_EXERCICIO,
CASE WHEN LENGTH(PERS_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END AS PERS_STATUS
                                ";
        
        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = $campos [ 'index' ];
        
        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "
PERS_ID_PERSPECTIVA             AS \"Codigo\",
PERS_TX_PERSPECTIVA             AS \"Descrição\",
PERS_AA_EXERCICIO               AS \"Exercicio\",
CASE WHEN LENGTH(PERS_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END AS \"Status\"
";
        
        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "PERS_ID_PERSPECTIVA, ";
        $campos [ 'excluir' ] .= $campos [ 'detalhe' ];
        
        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = $campos [ 'excluir' ];
        
        // Campos para a serem apresentados num combo
        $campos [ 'combo' ] = "
PERS_ID_PERSPECTIVA,
PERS_TX_PERSPECTIVA
                               ";
        
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
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>  
     */
    public function retornaRestricoes ( $acao = 'todos', $chaves = null )
    {

        // Verifica os se esta na tela de excluidos
        $filtroIndex = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        if($filtroIndex == 'excluidos'){
            $filtro = 'AND PERS_CD_MATRICULA_EXCLUSAO IS Not Null';
        }else{
            $filtro = 'AND PERS_CD_MATRICULA_EXCLUSAO IS Null';
        }

        // Condição para index
        $restricao ['index'] = $filtro;

        // Condição para excluidos
        $restricao ['excluidos'] = $filtro;

        // Condição para ação editar
        $restricao [ 'detalhe' ] = " AND PERS_ID_PERSPECTIVA IN ( $chaves ) ";
        
        // Condição para ação editar
        $restricao [ 'editar' ] = $restricao [ 'detalhe' ];
        
        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'detalhe' ];
        
        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'detalhe' ];
        
        // Condição para montagem do combo
        $restricao [ 'combo' ] = " PERS_DH_EXCLUSAO_LOGICA IS Null ";
        
        return $restricao [ $acao ];
    }

    /**
     * Realiza a exclusão lógica de uma ou mais perspectiva
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
        $perspectiva = $this->separaChave ( $chaves );
        
        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_PERS_PERSPECTIVA
SET
    PERS_CD_MATRICULA_EXCLUSAO          = '$matricula',
    PERS_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    PERS_ID_PERSPECTIVA                      IN ( $perspectiva ) AND
    PERS_DH_EXCLUSAO_LOGICA             IS Null
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
        $perspectiva = $this->separaChave ( $chaves );
        
        // Restaura um ou mais registros
        $sql = "
UPDATE
    CEO_TB_PERS_PERSPECTIVA
SET
    PERS_CD_MATRICULA_EXCLUSAO          = Null,
    PERS_DH_EXCLUSAO_LOGICA             = Null
WHERE
    PERS_ID_PERSPECTIVA                      IN ( $perspectiva ) AND
    PERS_DH_EXCLUSAO_LOGICA             IS NOT Null
                ";
        
        // Devolve a sql para restauração da registros logicamente excluídos
        return $sql;
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>  
     */
    public function retornaOpcoesGrid ()
    {
        // Personaliza a exibição dos campos no grid
        $detalhes = array ( 
        'PERS_ID_PERSPECTIVA'        => array ( 'title' => 'Codigo', 'abbr' => '' ), 
        'PERS_TX_PERSPECTIVA'        => array ( 'title' => 'Descrição', 'abbr' => '' ), 
        'PERS_AA_EXERCICIO'          => array ( 'title' => 'Exercicio', 'abbr' => '' ),
        'PERS_STATUS' => array ( 'title' => 'Status', 
                        'abbr' => 'Informa se o registro foi ou não excluído' )
        );
        
        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ( '' );
        
        // Devolve o array de opções
        return $opcoes;
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

}