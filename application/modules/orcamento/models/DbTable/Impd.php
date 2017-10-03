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
 * Serve de receptáculo dos dados a serem importados para o sistema, neste
 * momento sem nenhuma restrição (relacionamentos), para posterior crítica dos
 * dados e envio para suas respectivas tabelas negociais definitivas. Armazena
 * também o código de erro (definidos exclusivame via código-fonte), caso o
 * mesmo ocorra durante o processo de importação de dados da string.
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Impd
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Impd extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_IMPD_IMPORTACAO_DADOS';

    /**
     * Chave primária
     *
     * @var string
     */
    protected $_primary = 'IMPD_ID_LINHA';

    /**
     * Sequence (auto incremento)
     *
     * @var string
     */
    protected $_sequence = 'CEO_SQ_IMPD';

}