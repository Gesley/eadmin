<?php

class Arquivo_Form_AddVias extends Zend_Form {

    public function init() {
        $this->setMethod('post')
                ->setAction('add-cadastro-vias');
        $codigo = new Zend_Form_Element_Text('AQVI_CD_VIA');
        $codigo->setLabel('*Descrição:')
                ->addValidator('Alnum')
                ->setRequired(true)
                 ->setAttrib('required', 'required')
                ->addValidator('StringLength', false, array(0, 3))
                ->setAttrib('MaxLength', 3)
                ->setAttrib('required', 'required')
                ->setAttrib('onkeyup', 'keyUp()')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim');
        $quantidade = new Zend_Form_Element_Text('AQVI_QT_VIA');
        $quantidade->setLabel('*Quantidade:')
                ->setRequired(true)
                ->setAttrib('onkeypress', 'return Onlynumbers(event)')
                ->addValidator('Alnum')
                ->addValidator('StringLength', false, array(0, 8))
                ->setAttrib('MaxLength', 8)
                ->setAttrib('required', 'required')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim');
        $enviar = new Zend_Form_Element_Submit('Enviar', array('class' => 'novo'));
        $voltar = new Zend_Form_Element_Button('Cancelar', array(
            'class' => 'novo'));
        $voltar->setAttrib('id', 'voltar');
        $this->addElements(array($codigo, $quantidade, $enviar, $voltar));
    }

}
