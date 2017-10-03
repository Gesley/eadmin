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
 * Estrutura provisória para vinculação de Contrato com Despesa. A mesma se
 * tornará obsoleta (@deprecated) quando, e se, for criada a tabela (ou conjunto
 * delas) para o armazenamento das informações sobre os contratos vigentes e
 * passados desta corte.
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Ctrd
 * @todo Eliminar essa tabela tão logo se crie estrutura definitiva, em sistema
 *       e/ou banco de dados próprios sobre os contratos firmados com esta
 *       corte.
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Ctrd extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_CTRD_CONTRATO_DESPESA';

    /**
     * Chave primária
     *
     * @var string
     */
    protected $_primary = 'CTRD_ID_CONTRATO_DESPESA';

    /**
     * Sequence (auto incremento)
     *
     * @var string
     */
    protected $_sequence = 'CEO_SQ_CTRD';

}