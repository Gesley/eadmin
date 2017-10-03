<?php
class Sisad_Form_Despacho extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('despachoForm');
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('submitDespacho');
               
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                 ->setLabel('Descrição do Despacho:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty')
                 ->addValidator('StringLength', false, array(5, 4000))
                ->setAttrib('style', 'width: 628px;');

        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($acao,$mofa_ds_complemento,$submit));
    }
}