<?php
class Sisad_Form_DataAData extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
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

        $this->addElements(array($dataInicial
                                , $dataFinal
                                , $submit));
    }
}