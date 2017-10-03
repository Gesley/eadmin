<?php

class Arquivo_Form_ListSecundario extends Zend_Form{
    
       public function init() {
        $classe= new Zend_Form_Element_Select(
                'AQAS_CD_ASSUNTO_SECUNDARIO'
                );
        $classe->setRequired(true)
                ->setLabel('Cód - Classe:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addMultiOptions(array('' => 'SELECIONE UM ASSUNTO'));

        $this->addElements(array($classe));
    }
}