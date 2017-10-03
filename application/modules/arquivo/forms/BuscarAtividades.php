<?php

class Arquivo_Form_BuscarAtividades extends Zend_Form {

    public function init() {

        $id = new Zend_Form_Element_Hidden('AQVP_CD_PCTT');
        $codigo = new Zend_Form_Element_Text('AQVP_CD_PCTT');
        $codigo->setLabel('*Código:')
                ->addValidator('Alnum')
                ->addFilter('HtmlEntities')
                ->addValidator('StringLength', false, array(1, 3))
                ->addFilter('StringTrim');
        $vias = new Zend_Form_Element_Text('AQVI_CD_VIA');
        $vias->setLabel('* Vias:')
                ->setRequired(true)
                ->setAttrib('required', 'required')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim');


        $destino = new Zend_Form_Element_Text('AQAP_DH_CRIACAO', array(
            'id' => 'DATA_INICIAL'
        ));

        $data_inicio->setLabel('* Data de criação:')
                ->setRequired(true)
                ->addValidator('NotEmpty');

        $data_fim = new Zend_Form_Element_Text('AQAP_DH_FIM', array(
            'id' => 'DATA_FINAL'));
        $data_fim->setLabel('Data de finalização:')
                //->setRequired(true)
                ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Enviar', array('class' => 'novo'));
        $submit->setOptions(array('style' => 'margin-top:25px;'))
                ->removeDecorator("DtDdWrapper");
        $this->addElements(array($id, $codigo, $assunto, $data_inicio, $data_fim, $submit));
    }

}
