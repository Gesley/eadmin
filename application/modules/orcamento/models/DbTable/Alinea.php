<?php
/**
 * Contém classe para mapeamento de estruturas do banco de dados
 * 
 * e-Admin
 * e-Orçamento
 * Model - DbTable
 * 
 * @author Sandro Maceno <smaceno@stefanini.com>
 */

/**
 * Tabela do Alinea
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Alinea
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Alinea extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_ALIN_ALINEA';

    /**
     * Chave primária
     *
     * @var string
     */
    protected $_primary = 'ALIN_ID_ALINEA';
    
    /**
     * Nome do sequence
     * 
     * @var string 
     */
    protected $_sequence = 'CEO.CEO_SQ_ALIN';
    
}