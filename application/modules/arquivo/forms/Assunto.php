<?php

class Arquivo_Form_Assunto extends Zend_Form {

    public function init() {
        
        $this->removeDecorator('DtDlWrapper');
        $this->setMethod('post')
                ->setAction('add-pctt');
        $id = new Zend_Form_Element_Hidden('AQAP_CD_ASSUNTO_PRINCIPAL');
        $codigo = new Zend_Form_Element_Text('AQAP_CD_ASSUNTO_PRINCIPAL');
        $codigo->setLabel('*Código:')
                ->setRequired(true)
                 ->setAttrib('required', 'required')
                ->addValidator('Alnum')
                ->addFilter('HtmlEntities')
                ->setAttrib('MaxLength', 3)
                ->setAttrib('size', 10)
                ->addValidator('StringLength', false, array(1,3) )
                ->setAttrib('onkeypress', 'return Onlynumbers(event)')
                ->addFilter('StringTrim');
        $assunto = new Zend_Form_Element_Text('AQAP_DS_ASSUNTO_PRINCIPAL');
        $assunto->setLabel('* Descrição:')
                ->setRequired(true)
                ->setAttrib('required', 'required')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim');


        $data_inicio = new Zend_Form_Element_Text('AQAP_DH_CRIACAO', array(
            'id' => 'AQAP_DH_CRIACAO'
        ));

        $data_inicio->setLabel('* Data de criação:')
                ->setRequired(true)
                 ->setAttrib('required', 'required')
                ->addValidator('NotEmpty');

        $data_fim = new Zend_Form_Element_Text('AQAP_DH_FIM', array(
            'id' => 'AQAP_DH_FIM'));
        $data_fim->setLabel('Data de finalização:')
                //->setRequired(true)
                ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Enviar', array('class' => 'novo'));
        $submit->setOptions(array('style' => 'margin-top:25px;'))
                ->removeDecorator("DtDdWrapper");
         $voltar = new Zend_Form_Element_Button('Cancelar', array(
            'class' => 'novo'));
        $voltar->setAttrib('id', 'voltar');
        $this->addElements(array($id, $codigo, $assunto, $data_inicio, $data_fim,  $submit, $voltar));
    }

}
