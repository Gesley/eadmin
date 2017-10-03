<?php

class Arquivo_Form_AddSubClasse extends Zend_Form {

    public function init() {
        
        $this->removeDecorator('DtDlWrapper');
        $this->setMethod('post')
              ->setAction('add-sub-class');
        $codigo = new Zend_Form_Element_Text('AQSC_CD_SUBCLASSE');
        $codigo->setLabel('*Código:')
                ->addValidator('Alnum')
                 ->setAttrib('required', 'required')
                ->setRequired(true)
                ->setAttrib('onkeypress', 'return Onlynumbers(event)')
                ->addFilter('HtmlEntities')
                ->addValidator('StringLength', false, array(1,3))
                ->setAttrib('MaxLength', 3)
                ->addFilter('StringTrim');
        $assunto = new Zend_Form_Element_Text('AQSC_DS_SUBCLASSE');
        $assunto->setLabel('* Descrição:')
                ->setRequired(true)
                ->setAttrib('required', 'required')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim');


        $data_inicio = new Zend_Form_Element_Text('AQSC_DH_CRIACAO', array(
            'class' => 'AQSC_DH_CRIACAO',
        ));

        $data_inicio->setLabel('* Data de criação:')
                ->setRequired(true)
                ->setAttrib('required', 'required')
                ->addValidator('NotEmpty');

        $data_fim = new Zend_Form_Element_Text('AQSC_DH_FIM', array(
            'classe' => 'AQSC_DH_FIM'));
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

