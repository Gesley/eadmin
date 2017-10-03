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
 * Contém as despesas controladas pelo sistema. Esta é a tabela mais importante
 * do sistema e a que contém mais relacionamentos com outras tabelas. Recebe os
 * dados elementares da despesa, que é a menor unidade de informação
 * orçamentária para o sistema; abrange toda a 1ª região
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Desp
 * @todo Criar estruturas e funcionalidades para relacionamento entre despesas
 *       para o acompanhamento plurianual das mesmas para ser possível observar
 *       sua evolução ao longo dos exercícios.
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Desp extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_DESP_DESPESA';

    /**
     * Chave primária
     *
     * @var string
     */
    protected $_primary = 'DESP_NR_DESPESA';

    /**
     * Sequence (auto incremento)
     *
     * @var string
     */
    protected $_sequence = 'CEO_SQ_DESP';

}