<?php
class Sisad_Form_VersaoMinuta extends Zend_Form
{
    public function init()
    {
        $this->setAction('saveversao')
             ->setMethod('post')
             ->setName('versaominuta');
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('submitVersao');
               
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                 ->setLabel('Descrição:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty')
                 ->addValidator('StringLength', false, array(5, 4000))
                ->setAttrib('style', 'width: 628px;');

        $tipo_arquivo = new Zend_Form_Element_Radio('RADIO_TIPO_ARQUIVO');
        $tipo_arquivo->setLabel("Inserir / Criar Documento:")
                     ->addMultiOptions(array("D" => "Inserir documento previamente elaborado",
                                             "E" => "Criar documento"));
        
        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $visualizar = new Zend_Form_Element_Submit('PréVisualizar');
        $visualizar->removeDecorator('DTDDWRAPPER');
        
        $this->addElements(array($acao,$mofa_ds_complemento,$tipo_arquivo,$submit,$visualizar));
    }
}