<?php

class Orcamento_Form_Licitacao extends Zend_Form {

    public function init() {

        $this->setName('frmLicitacao')
            ->setMethod('post')
            ->setAttrib('id', 'frmLicitacao')
            ->setElementFilters(array('StripTags', 'StringTrim'));

        $negocio = new Orcamento_Business_Negocio_Licitacao();

        $fase = new Zend_Form_Element_Select('FASL_CD_FASE');
        $fase->setLabel('Fase da licitação:');

        $fase->addMultiOptions(array('' => 'Selecione'));
        $fase->addMultiOptions($negocio->retornaCombo());
        $fase->setRequired(true);

        // Botão submit
        $cmdSubmit = new Zend_Form_Element_Button('Salvar');
        $cmdSubmit->setLabel('Salvar')->setAttrib('type', 'submit')->setAttrib('class', 'ceo_salvar');

        $this->addElements(array($fase, $cmdSubmit));
    }
}