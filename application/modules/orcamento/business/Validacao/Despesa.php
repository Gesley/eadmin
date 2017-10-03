<?php
/**
 * Contém classe para validação de elementos de formulário
 * 
 * e-Admin
 * e-Orçamento
 * Business - Validação
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém as validações referentes às despesas
 *
 * @category Orcamento
 * @package Orcamento_Business_Validacao_Despesa
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
require_once 'Zend/Validate/Abstract.php';

class Orcamento_Business_Validacao_Despesa extends Zend_Validate_Abstract
{
    const DESPESA_INVALIDA = 'despesaInvalida';
    // const ANO_INVALIDO = 'anoInvalido';
    
    protected static $_filter = null;

    protected $_messageTemplates = array ( 
            self::DESPESA_INVALIDA => "A despesa não pertence a sua unidade, foi
            excluida ou você não possui permissão para visualizar a mesma."
            /*
             * , self::ANO_INVALIDO =>
             * "A despesa não pertence ao ano corrente"
             */
        );

    public function isValid ( $despesa )
    {
        $negocio = new Trf1_Orcamento_Negocio_Despesa ();
        
        $consulta = $negocio->retornaDespesa ( $despesa );
        
        if ( $consulta == false )
        {
            $this->_error ( self::DESPESA_INVALIDA );
            return false;
        }
        
        return true;
    }

}
