<?php
class Sisad_Form_Anexo extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $anexos = new Zend_Form_Element_File('ANEXOS');
        $anexos->setLabel('Anexos')
                ->setRequired(false)
                ->setIsArray(true)
                ->addValidator('Size', false, array('max' => '52428800'))
                ->setMaxFileSize(52428800)
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setAttribs(array('class' => 'Multi', 'maxlength' => 20, 'multiple' => true))
                ->setDescription('Até 20 Anexos. Soma dos arquivos até 50 Megas.');
        
        $this->addElements(array($anexos));
    }
}