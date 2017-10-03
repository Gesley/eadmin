<?php
class Application_Form_Procurar extends Zend_Form
{
    public function init()
    {
        $this->setAction('post/index')
             ->setMethod('get');

        /*-- Método Simples sem validar e verificar --------------*/
        $this->addElement('text', 'filtro',
                          array('label' =>'Sobrenome:'));

        $submit = new Zend_Form_Element_Submit('Procurar');
        $voltar = new Zend_Form_Element_Button('Voltar');
        $voltar->setOptions(array('onclick'=>'javascript:history.go(-1)'));

    }
}