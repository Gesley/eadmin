<?php

class Arquivo_Form_SelectSubClasse extends Zend_Form {

    public function init() {
        $subClasse = new Zend_Form_Element_Select(
                'AQSC_CD_SUBCLASSE'
        );
        $subClasse->setRequired(true)
                ->setLabel('Cód - SubClasse:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addMultiOptions(array('' => 'SELECIONE UM ASSUNTO'));

        $this->addElements(array($subClasse));
    }

}
