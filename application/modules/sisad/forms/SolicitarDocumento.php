<?php

class Sisad_Form_SolicitarDocumento extends Zend_Form {

    public function init() {
        $this->setAction('')
                ->setMethod('post')
                ->setName('Solicitar');

        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $descricao_usuario = new Zend_Form_Element_Textarea('DESCRICAO_USUARIO');
        $descricao_usuario->setRequired(true)
                ->setLabel('Descrição da Solicitação de Documento/Processo/Vistas:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('StringLength', false, array(5, 4000))
                ->setAttrib('style', 'width: 628px;')
                ->addFilter($Zend_Filter_HtmlEntities);

        $id_documento = new Zend_Form_Element_Hidden('DOCM_ID_DOCUMENTO');
        $id_documento->setRequired(false)
                ->addFilter('Int')
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $submit = new Zend_Form_Element_Submit('Solicitar');

        $this->addElements(array($descricao_usuario, $id_documento,$submit));
    }

}