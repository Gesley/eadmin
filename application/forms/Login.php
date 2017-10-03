<?php

class Form_Login extends Zend_Form
{
    public function init() 
    {
        $this->setName('Login')
             ->setAttrib('id', 'login')
             ->setMethod('post')
             ->setElementFilters(array('StripTags', 'StringTrim'));

        $cpf = new Zend_Form_Element_Text('COU_COD_MATRICULA');
        $cpf->setLabel('Matrícula:')
            ->setAttrib('size', '24')
            ->setRequired(true)
            ->addValidator('Alnum')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(4, 11))
            ->setOptions(array('maxLength' => 11));

        $password = new Zend_Form_Element_Password('COU_COD_PASSWORD');
        $password->setLabel('Senha:')
                 ->setAttrib('size', '24')
                 ->setRequired(true)
                 ->addFilter('StringTrim');

        $banco = new Zend_Form_Element_Select('COU_NM_BANCO');
        $banco->setLabel('Banco:')
              ->addFilter('StripTags')
              ->setRequired(true)
              ->addFilter('StringTrim')
              ->addFilter('HtmlEntities')
              ->addValidator('NotEmpty')
              ->addMultiOptions(array(':: Selecione ::'));
        
        $submit = new Zend_Form_Element_Submit('Conectar');
        $this->addElements(array($cpf, $password, $banco, $submit));
    }
}