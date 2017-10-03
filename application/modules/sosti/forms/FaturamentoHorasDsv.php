<?php

class Sosti_Form_FaturamentoHorasDsv extends Zend_Form {

    public function init() {

        $this->setAction('')
                ->setMethod('post');

       $horas = new Zend_Form_Element_Text('HORAS');
       $horas->setLabel('Horas')
               ->setAttribs(array('style' => 'width:30px;', 'required' => 'required'));
       
       $minutos = new Zend_Form_Element_Text('MINUTOS');
       $minutos->setLabel('Minutos')
               ->setAttribs(array('style' => 'width:30px;', 'required' => 'required', 'onChange' => 'Calcula()'));;
       
       $total = new Zend_Form_Element_Text('TOTAL');
       $total->setLabel('PF Correspondente:')
               ->setAttribs(array('readonly' => 'readonly'));
               

        $this->addElements(array(
            $horas,
            $minutos,
            $total));
       
       $this->addDisplayGroup(array('HORAS', 'MINUTOS', 'TOTAL' ), 'tempo_gasto', array("legend" => "Tempo de Desenvolvimento"));
    }

}