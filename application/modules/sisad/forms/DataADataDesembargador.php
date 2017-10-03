<?php
class Sisad_Form_DataADataDesembargador extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $ocsTbPnatPessoaNatural = new Application_Model_DbTable_OcsTbPnatPessoaNatural();
        $rows = $ocsTbPnatPessoaNatural->getDesembargadoresFederais();
        
        $desem_federal = new Zend_Dojo_Form_Element_FilteringSelect('desem_federal');
        $desem_federal->setRequired(true)
                                   ->setLabel('Desembargador Federal:')
                                   ->addMultiOption('todos','TODOS OS DESEMBARGADORES');
        foreach ($rows as $row):
            $desem_federal->addMultiOption($row['PMAT_CD_MATRICULA'], $row['PNAT_NO_PESSOA']);
        endforeach;
        
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

        $this->addElements(array($desem_federal
                                , $dataInicial
                                , $dataFinal
                                , $submit));
    }
}