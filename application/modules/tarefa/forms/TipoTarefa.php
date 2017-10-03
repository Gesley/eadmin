<?php

class Tarefa_Form_TipoTarefa extends Zend_Form 
{

    public function init() 
    {
        $this->setAction('')
             ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('TPTA_ID_TIPO_TAREFA');
        $id->addFilter('Int')
           ->removeDecorator('Label')
           ->removeDecorator('HtmlTag');
        
        $nome = new Zend_Form_Element_Text('TPTA_NM_TAREFA');
        $nome->setLabel('Nome do Tipo de Tarefa:')
             ->addFilter('StripTags')
             ->setRequired(true)
             ->setOptions(array('style' => 'width:500px'))
             ->addFilter('StringTrim')
             ->addValidator('StringLength', false, array(5, 50))
             ->addValidator('NotEmpty');
        
        $descricao = new Zend_Form_Element_Textarea('TPTA_DS_TAREFA');
        $descricao->setLabel('Definição do Tipo de Tarefa:')
                  ->addFilter('StripTags')
                  ->setDescription('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.')
                  ->setAttrib('style', 'width: 800px; height: 30px;')
                  ->addFilter('StringTrim')
                  ->setRequired(true)
                  ->addValidator('StringLength', false, array(5, 500))
                  ->addValidator('NotEmpty');
        
        $salvar = new Zend_Form_Element_Submit('Salvar');
        $this->addElements(array(
            $id,
            $nome,
            $descricao,
            $salvar
        ));
    }
}