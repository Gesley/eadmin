<?php

class Sosti_Form_SolicManutencao extends Zend_Form
{

    public function init()
    {
        $this->setAction('')
                ->setMethod('post')
                ->setAttrib('name', 'solic');

        $NR_TOMBO = new Zend_Form_Element_Text('NR_TOMBO');
        $NR_TOMBO->setLabel('*Número do tombo')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
//                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true);
        $NR_TOMBO->setRequired(true);

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $SUBMIT = new Zend_Form_Element_Submit('Pesquisar');

        $this->addElements(array($NR_TOMBO, $SUBMIT, $obrigatorio));
    }

}