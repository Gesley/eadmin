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
 * Contém os períodos de restrição ao uso da funcionalidade de Projeção por cada
 * UG (Unidade Gestora).
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Trvp
 * @todo Para o futuro módulo de captação da proposta existe a possibilidade de
 *       incluir uma opção para reuso desta estrutura para travamento do período
 *       de informação do valor desejado para cada despesa pelo seu respectivo
 *       responsável.
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Trvp extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_TRVP_TRAVA_PROJECAO';

    /**
     * Chave primária composta
     *
     * @var array
     */
    protected $_primary = array ( 'TRVP_CD_UG', 'TRVP_DT_INICIO' );

}