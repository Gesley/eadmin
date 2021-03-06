<?php
class Sosti_Form_AvaliacaoServico extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setAttrib('enctype', 'multipart/form-data')
             ->setMethod('post');

        $avaliar = new Zend_Form_Element_Hidden('Avaliar');
        $avaliar->removeDecorator('Label')
                ->removeDecorator('HtmlTag')
                ->setValue('Salvar');
        
        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $descricao = new Zend_Form_Element_Textarea('descricao');
        $descricao->setLabel('Descrição da Avaliação:')
                            ->setOptions(array('style' => 'width:500px'))
                            ->addValidator('StringLength', false, array(20, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter($Zend_Filter_HtmlEntities);

        $this->addElements(array($descricao));
    }

}