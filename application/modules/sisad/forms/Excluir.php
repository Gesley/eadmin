<?php
class Sisad_Form_Excluir extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
             ->setName('Excluir');

        
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                 ->setLabel('Justificativa do Cancelamento:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty')
                 ->addValidator('StringLength', false, array(5, 4000))
                ->setAttrib('style', 'width: 628px;');

        $submit = new Zend_Form_Element_Submit('Excluir');
        
        $this->addElements(array($mofa_ds_complemento,$submit));
    }
}