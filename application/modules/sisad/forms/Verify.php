<?php

class Sisad_Form_Verify extends Zend_Form {

    public function init() {
        $this->setName('Login')
                ->setAttrib('id', 'login')
                ->setMethod('post')
                ->setElementFilters(array('StripTags', 'StringTrim'));

        $cpf = new Zend_Form_Element_Text('COU_COD_MATRICULA');
        $cpf->addValidator('NotEmpty')
                ->setLabel('Matrícula:')
                ->setAttrib('size', '24')
                ->setAttrib('disabled', 'disabled')
                ->setRequired(true)
                ->addValidator('Alnum')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', false, array(4, 11))
                ->setOptions(array('maxLength' => 11));

        $password = new Zend_Form_Element_Password('COU_COD_PASSWORD');
        $password->addValidator('NotEmpty')
                ->setLabel('Senha:')
                ->setAttrib('size', '24')
                ->setRequired(true)
                ->addFilter('StringTrim');

        $submit = new Zend_Form_Element_Submit('Assinar');

        $this->addElements(array($cpf, $password, $submit));
    }

}
