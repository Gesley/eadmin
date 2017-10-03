<?php

class Arquivo_Form_Buscar extends Zend_Form{
    
    public function init() {
        
        $this->setAction('')
                ->setMethod('get');
        
        $listar = new Zend_Form_Element_Text('codigo');
        $listar->setLabel('Sistema de busca')
                                 ->addFilter('StripTags')
                                 ->addFilter('StringTrim')
                                 ->setAttrib('size', '110')
                                 ->addValidator('NotEmpty');
        $submit = new Zend_Form_Element_Button('Localizar');
        $submit->setAttrib('id', 'buscar');
        $submit->setAttrib('class', 'enviar');
        
          $this->addElements(array($listar, $submit));
    }
}
