<?php
class Sisad_Form_Parecer extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('parecerForm');
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('submitParecer');
               
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                 ->setLabel('Descrição do Parecer:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty')
                 ->addValidator('StringLength', false, array(5, 4000))
                 ->setOptions(array('maxLength' => 4000))
                 ->setAttrib('style', 'width: 628px;');

        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($acao,$mofa_ds_complemento,$submit));
    }
}