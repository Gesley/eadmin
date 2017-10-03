<?php
class Sisad_Form_PermissaoCaixa extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('permissaocaixaForm');
        
              
        $unidade = new Zend_Form_Element_Select('UNIDADE');
        $unidade->setRequired(true)
                 ->setLabel('Unidade:')
                 ->setAttrib('style', 'width: 628px;');

        $preferencia = new Zend_Form_Element_Checkbox('PREFERENCIA');
        $preferencia->setRequired(true)
                 ->setLabel('Salvar Preferencia:');
        
        $submit = new Zend_Form_Element_Submit('Escolher');
        $submit->setAttrib('class', 'botao')
               //->removeDecorator('DtDdWrapper')
                ->removeDecorator('label');
        
        $this->addElements(array($unidade,$preferencia,$submit));
    }
}