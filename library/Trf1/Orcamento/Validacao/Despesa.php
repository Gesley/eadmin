<?php
require_once 'Zend/Validate/Abstract.php';

class Trf1_Orcamento_Validacao_Despesa extends Zend_Validate_Abstract
{
    const DESPESA_INVALIDA = 'despesaInvalida';
    // const ANO_INVALIDO = 'anoInvalido';
    
    protected static $_filter = null;

    protected $_messageTemplates = array ( 
            self::DESPESA_INVALIDA => "A despesa não pertence a sua unidade ou foi excluida"
											/*, self::ANO_INVALIDO => "A despesa não pertence ao ano corrente"*/ );

    public function isValid ( $despesa )
    {
        $this->_negocioDespesa = new Trf1_Orcamento_Negocio_Despesa ();
        $consulta = $this->_negocioDespesa->retornaDespesa ( $despesa );
        if ( $consulta == false ) {
            $this->_error ( self::DESPESA_INVALIDA );
            return false;
        } else {
            return true;
        }
    }

}
