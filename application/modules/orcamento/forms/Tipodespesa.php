<?php

class Orcamento_Form_Tipodespesa extends Zend_Form
{

    public function init()
    {
        $this->setName('frmTipoDespesa')->setMethod('post')->setAttrib('id', 'frmTipoDespesa')->setElementFilters(array('StripTags', 'StringTrim'));

        $txtIDTipoDesp = new Zend_Form_Element_Text('TIDE_CD_TIPO_DESPESA');
        $txtIDTipoDesp->setLabel('Caráter da despesa:')->setRequired(true)->setAttrib('size', '10')->setAttrib('maxlength', 2)->addValidator('Digits');

        $txtDETipoDesp = new Zend_Form_Element_Textarea('TIDE_DS_TIPO_DESPESA');
        $txtDETipoDesp->setLabel('Descrição:')->setRequired(true)->setAttrib('size', 20)->setAttrib('maxlength', 80)->addFilter('StringTrim');

        $cmdSubmit = new Zend_Form_Element_Button('Salvar');
        $cmdSubmit->setLabel('Salvar')->setAttrib('type', 'submit')->setAttrib('class', 'ceo_salvar');

        $chkReservaRecursoSistema = new Zend_Form_Element_Checkbox('TIDE_IC_RESERVA_RECURSO');
        $chkReservaRecursoSistema->setLabel('Reserva de Recursos no Sistema?')->setRequired(true);

        $this->addElements(array($txtIDTipoDesp, $txtDETipoDesp, $chkReservaRecursoSistema, $cmdSubmit));
    }

}
