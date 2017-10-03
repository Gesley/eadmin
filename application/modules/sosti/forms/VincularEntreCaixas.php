<?php
class Sosti_Form_VincularEntreCaixas extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
             ->setName('vincularEntreCaixas');
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setName('acao')
             ->setValue('VINCULAR ENTRE CAIXAS');
        
        $submit = new Zend_Form_Element_Submit('VINCULAR_ENTRE_CAIXAS');
        $submit->setLabel('Vincular Entre Caixas');
        $this->addElements(array($submit, $acao));
    }

}