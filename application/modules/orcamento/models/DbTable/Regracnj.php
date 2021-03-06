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
 * Tabela do Regracnj
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Regracnj
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Regracnj extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_REGC_REGRA_CNJ';

    /**
     * Chave primária
     *
     * @var string
     */
    protected $_primary = 'REGC_ID_REGRA';
    
    /**
     * Nome do sequence
     * 
     * @var string 
     */
    protected $_sequence = 'CEO.CEO_SEQ_REGC';
    
}