<?php
/**
 * Contém classe para mapeamento de estruturas do banco de dados
 * 
 * e-Admin
 * e-Orçamento
 * Model - DbTable
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Classe intermediária, entre cada model e a Zend_Db_Table_Abstract, servindo
 * para disponibilizar funções genéricas para todas as models.
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Base
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Base extends Zend_Db_Table_Abstract
{

    /**
     * Nome do owner (ou schema) da tabela
     *
     * @var string
     */
    protected $_schema = Orcamento_Business_Dados::BANCO_OWNER_SCHEMA;

    /**
     * Retorna a chave primária - ou composta - da tabela da model
     *
     * @return array Chave primária (ou composta)
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function chavePrimaria ()
    {
        // Devolve a chave primária (ou composta)
        return array ( $this->_primary );
    }

    /**
     * Retorna o nome da tabela
     *
     * @return string Nome da tabela
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function tabela ()
    {
        // Devolve o nome da tabela
        return $this->_name;
    }

    /**
     * Retorna o alias da tabela, no caso o nome curto da mesma
     *
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaAlias ()
    {
        // Identificador da tabela
        $identificador = 'CEO_TB_';
        
        // Define o alias inicial
        $alias = '';
        
        // Retorna o nome da tabela
        $tabela = $this->tabela ();
        
        // Verifica a posição de string fixa 'CEO_TB_'
        $posicao = strpos ( $tabela, $identificador );
        
        // Evita retorno incorreto no caso desse método ser chamado diretamente
        if ( $posicao === 0 ) {
            $alias = substr ( $tabela, strlen ( $identificador ), 4 );
        }
        
        // Devolve o alias
        return $alias;
    }

}