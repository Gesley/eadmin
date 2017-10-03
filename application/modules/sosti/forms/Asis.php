<?php

class Sosti_Form_Asis extends Zend_Form {

    public function init() {
        $this->setAction('save')
                ->setMethod('post');


        $asis_ic_nivel_criticidade = new Zend_Form_Element_Select('ASIS_IC_NIVEL_CRITICIDADE');
        $asis_ic_nivel_criticidade->setLabel('*Nível de Criticidade')
                ->setRequired(false)
                ->addMultiOptions(array(3 => 'Nível 3 - Baixa criticidade'))
                ->addMultiOptions(array(2 => 'Nível 2 - Média criticidade'))
                ->addMultiOptions(array(1 => 'Nível 1 - Alta criticidade'));

        $this->addElements(array($asis_ic_nivel_criticidade));
    }

}