<?php
class Sisad_Form_DataADataOrgao extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $orgao = new Zend_Dojo_Form_Element_FilteringSelect('orgao');
        $orgao->setRequired(true)
                                   ->setLabel('Orgão Julgador:')
                                   ->addMultiOption('null','Todos da Lista');
        
        $dataInicial = new Zend_Form_Element_Text('data_inicial');
        $dataInicial->setLabel('Data Inicial:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setOptions(array('style' => 'width: 80px'))
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyy')));
        
        $dataFinal = new Zend_Form_Element_Text('data_final');
        $dataFinal->setLabel('Data Final:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setOptions(array('style' => 'width: 80px'))
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyy')));
        
        $submit = new Zend_Form_Element_Submit('Buscar');

        $this->addElements(array($orgao
                                , $dataInicial
                                , $dataFinal
                                , $submit));
    }
}