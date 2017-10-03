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
 * Contém uma ou mais justificativas de dada projeção orçamentária, onde o
 * responsável da mesma descreve a necessidade de se utilizar mais ou menos
 * recursos quando houver qualquer diferença com o valor disponível por
 * exercício, ou ainda da disponibilidade de recursos que porventura não venham
 * a ser utilizados.
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Prjj
 * @see Orcamento_Model_DbTable_Proj Classe model da projeção.
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Prjj extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_PRJJ_JUSTIF_PROJECAO';

    /**
     * Chave primária composta
     *
     * @var array
     */
    protected $_primary = array ( 'PRJJ_NR_DESPESA', 'PRJJ_DH_JUSTIFICATIVA' );

}