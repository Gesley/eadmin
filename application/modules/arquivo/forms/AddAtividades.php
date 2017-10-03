<?php

class Arquivo_Form_AddAtividades extends Zend_Form {

    public function init() {

        $this->removeDecorator('DtDlWrapper');
        $this->setMethod('post')
                ->setAction('add-cadastro-atividades');
        $codigo = new Zend_Form_Element_Text('AQAT_CD_ATIVIDADE');
        $codigo->setLabel('*Código:')
                ->addValidator('Alnum')
                ->setRequired(true)
                ->setAttrib('required', 'required')
                ->addFilter('HtmlEntities')
                ->setAttrib('onkeypress', 'return Onlynumbers(event)')
                ->setAttrib('MaxLength', 3)
                ->addValidator('StringLength', false, array(1, 3))
                ->addFilter('StringTrim');
        $assunto = new Zend_Form_Element_Text('AQAT_DS_ATIVIDADE');
        $assunto->setLabel('* Descrição:')
                ->setRequired(true)
                ->setAttrib('required', 'required')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim');


        $data_inicio = new Zend_Form_Element_Text('AQAT_DH_CRIACAO', array(
            'id' => 'AQAT_DH_CRIACAO'
        ));

        $data_inicio->setLabel('* Data de criação:')
                ->setRequired(true)
                ->setAttrib('required', 'required')
                ->addValidator('NotEmpty');

        $data_fim = new Zend_Form_Element_Text('AQAT_DH_FIM', array(
            'id' => 'AQAT_DH_FIM'));
        $data_fim->setLabel('Data de finalização:')
                //->setRequired(true)
                ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Enviar', array('class' => 'novo'));
        $submit->setOptions(array('style' => 'margin-top:25px;'))
                ->removeDecorator("DtDdWrapper");

        $voltar = new Zend_Form_Element_Button('Cancelar', array(
            'class' => 'novo'));
        $voltar->setAttrib('id', 'voltar');
        $this->addElements(array($id, $codigo, $assunto, $data_inicio, $data_fim, $submit, $voltar));
    }

}
