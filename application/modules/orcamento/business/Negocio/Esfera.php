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
 * Contém as regras negociais sobre esfera
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Esfera
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Esfera extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Esfe ();
        
        // Define a negocio
        $this->_negocio = 'esfera';
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
ESFE_CD_ESFERA,
ESFE_DS_ESFERA,
CASE WHEN LENGTH(ESFE_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS ESFE_STATUS
                                ";
        
        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = $campos [ 'index' ];
        
        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "
ESFE_CD_ESFERA                  AS \"Esfera\",
ESFE_DS_ESFERA                  AS \"Descrição\",
CASE WHEN LENGTH(ESFE_CD_MATRICULA_EXCLUSAO) > 0
    THEN 'Excluído '
    ELSE 'Ativo'
END                             AS \"Status do registro\",
ESFE_CD_MATRICULA_EXCLUSAO      AS \"Excluído por\",
ESFE_DH_EXCLUSAO_LOGICA         AS \"Data da exclusão\"
                                ";
        
        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "ESFE_CD_ESFERA, ";
        $campos [ 'excluir' ] .= $campos [ 'detalhe' ];
        
        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = $campos [ 'excluir' ];
        
        // Campos para a serem apresentados num combo
        $campos [ 'combo' ] = "
ESFE_CD_ESFERA,
ESFE_DS_ESFERA
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
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaRestricoes ( $acao = 'todos', $chaves = null )
    {
        // Condição para ação editar
        $restricao [ 'detalhe' ] = " AND ESFE_CD_ESFERA IN ( $chaves ) ";
        
        // Condição para ação editar
        $restricao [ 'editar' ] = $restricao [ 'detalhe' ];
        
        // Condição para ação excluir
        $restricao [ 'excluir' ] = $restricao [ 'detalhe' ];
        
        // Condição para ação restaurar
        $restricao [ 'restaurar' ] = $restricao [ 'detalhe' ];
        
        // Condição para montagem do combo
        $restricao [ 'combo' ] = " ESFE_DH_EXCLUSAO_LOGICA IS Null ";
        
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
        $esferas = $this->separaChave ( $chaves );
        
        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_ESFE_ESFERA
SET
    ESFE_CD_MATRICULA_EXCLUSAO          = '$matricula',
    ESFE_DH_EXCLUSAO_LOGICA             = SYSDATE
WHERE
    ESFE_CD_ESFERA                      IN ( $esferas ) AND
    ESFE_DH_EXCLUSAO_LOGICA             IS Null
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
    CEO_TB_ESFE_ESFERA
SET
    ESFE_CD_MATRICULA_EXCLUSAO          = Null,
    ESFE_DH_EXCLUSAO_LOGICA             = Null
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
                'ESFE_CD_ESFERA' => array ( 'title' => 'Esfera', 'abbr' => '' ), 
                'ESFE_DS_ESFERA' => array ( 'title' => 'Descrição', 
                        'abbr' => '' ), 
                'ESFE_STATUS' => array ( 'title' => 'Status', 
                        'abbr' => 'Informa se o registro foi ou não excluído' ) );
        
        // Combina as opções num array
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ( 'CAMPO_NAO_EXISTENTE' );
        
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