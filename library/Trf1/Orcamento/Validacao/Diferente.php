<?php
require_once 'Zend/Validate/Abstract.php';

class Trf1_Orcamento_Validacao_Diferente extends Zend_Validate_Abstract
{
    /**
     * Códigos de erro @const string
     */
    const IGUAL = 'igual';
    const FALTA_VALOR = 'faltavalor';

    /**
     * Mensagens de erro
     * 
     * @var array
     */
    protected $_messageTemplates = array ( 
            self::IGUAL => "As despesas de origem e de destino devem ser diferentes", 
            self::FALTA_VALOR => 'Despesa de origem ainda não foi informada para comparação' );

    protected $_despesaOrigem;

    public function defineDespesaOrigem ( $despesa )
    {
        $this->_despesaOrigem = $despesa;
        return $this;
    }

    public function retornaDespesaOrigem ()
    {
        return $this->_despesaOrigem;
    }

    public function isValid ( $despesa )
    {
        $despesaOrigem = $this->_despesaOrigem->getValue ();
        
        if ( $despesaOrigem === null ) {
            $this->_error ( self::FALTA_VALOR );
            return false;
        }
        
        if ( $despesa == $despesaOrigem ) {
            $this->_error ( self::IGUAL );
            return false;
        }
        
        return true;
    }

}
