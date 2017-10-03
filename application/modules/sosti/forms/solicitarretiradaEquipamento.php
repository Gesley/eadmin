<?php
class Sosti_Form_solicitarretiradaEquipamento extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

       
        $descricaoSolicitar = new Zend_Form_Element_Textarea('DESCRICAO');
        $descricaoSolicitar->setRequired(true)
                         ->setLabel('Descrição da solicitação:')
                         ->setAttrib('style', 'width: 400px; height: 60px;')
                         ->addValidator('StringLength', false, array(5, 4000))
                         ->addValidator('NotEmpty')
                         ->addFilter('StripTags')
                         ->addFilter('StringTrim')
                         ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
        

        $submit = new Zend_Form_Element_Submit('Solicitar');
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('solicitarRetirada');

        $this->addElements(array( $descricaoSolicitar,$acao,$submit));
    }

}