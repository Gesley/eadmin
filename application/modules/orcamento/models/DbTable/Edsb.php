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
 * Descrição desta tabela...
 *
 * @category Orcamento
 * @package Orcamento_Model_DbTable_Edsb
 * @todo Alterar o nome desta estrutura, bem como das classes e demais
 *       componentes referentes, para - por exemplo: CEO_TB_NATU_NATUREZA,
 *       conforme solicitado negocialmente pelo usuário gestor do sistema, uma
 *       vez que, segundo o mesmo, é a nomenclatura correta ao invés de elemento
 *       e subelemento da despesa.
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Model_DbTable_Edsb extends Orcamento_Model_DbTable_Base
{

    /**
     * Nome da tabela
     *
     * @var string
     */
    protected $_name = 'CEO_TB_EDSB_ELEMENTO_SUB_DESP';

    /**
     * Chave primária
     *
     * @var string
     */
    protected $_primary = 'EDSB_CD_ELEMENTO_DESPESA_SUB';

}