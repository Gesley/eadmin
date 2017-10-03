<?php
/**
 * Contém classe para mapeamento de estruturas do banco de dados
 *
 * e-Admin
 * e-Orçamento
 * Model - DbTable
 *
 * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contem as informações de banco de dados da tabela Log Dados do orçamento
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Logd
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Logd extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_LOG_DADOS';

    /**
     * Chave primária
     *
     * @var string
     */
    protected $_primary = 'LOG_ID_DADOS';

}