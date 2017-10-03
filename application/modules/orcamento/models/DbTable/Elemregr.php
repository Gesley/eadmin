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
 * Contém as esferas; informações vinda do SIAFI como parte da identificação
 * orçamentária de cada despesa.
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Elemregr
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Elemregr extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_ELRG_ELEMENTOS_REGRA';

    /**
     * Chave primária
     *
     * @var string
     */
    protected $_primary = 'ELRG_ID_ELEMEN_REGRA';

    /**
     * Sequence da tabela
     *
     * @var string
     */
    protected $_sequence = 'CEO_SQ_ELRG';

}
