<?php
/**
 * Classe para validação dos anexos.
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class App_Form_Validate_Anexos extends Zend_Validate_Abstract
{
    const ANEXO_EXCEED = '';
    
    protected $_messageTemplates = array(
        self::ANEXO_EXCEED => 'Os arquivos anexados podem ter no Maximo 50 Megas.'
//        self::ANEXO_EXCEED => 'O processo "%value%" não existe'
    );
    
    public function __construct()
    {
        $this->_model = '';
    }
    
    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
//        if ($value == "") {
//            return false;
//        } else {
            $this->_error(self::ANEXO_EXCEED);
            return false;
//        }
    }
}