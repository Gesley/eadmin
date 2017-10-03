<?php
class Sisad_Form_Etiqueta extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
       $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
       $data_inicial->setLabel('Data inicial:');
       
       $data_final = new Zend_Form_Element_Text('DATA_FINAL');
       $data_final->setLabel('Data final:');

       $submit = new Zend_Form_Element_Submit('Pesquisar');
       
       $this->addElements(array($data_inicial,
                                $data_final,
                                $submit));
    }
}