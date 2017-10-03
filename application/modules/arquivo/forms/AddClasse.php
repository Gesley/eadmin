<?php
class Arquivo_Form_AddClasse extends Zend_Form{
    
    public function init() {
        
      
        $this->setName('Classe')
                ->setAttrib('id', 'AQCL_ID_AQAS')
                ->setMethod('post')
                ->setAction('add-classe')
                ->setElementFilters(array('StripTags', 'StringTrim'));

        $codigo = new Zend_Form_Element_Text('AQCL_CD_CLASSE');
        $codigo->setLabel('*Código:')
                ->setAttrib('onkeypress', 'return Onlynumbers(event)')
                ->addValidator('Alnum')
                ->addValidator('StringLength', false, array(0,3))
                ->setAttrib('MaxLength', 3)
                ->setAttrib('required', 'required')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim');
        $assunto = new Zend_Form_Element_Text('AQCL_DS_CLASSE');
        $assunto->setLabel('*Descrição:')
                ->addFilter('HtmlEntities')
                ->setAttrib('required', 'required')
                ->setRequired(true)
                ->setAttrib('style', 'width:60%;')
                ->addFilter('StringTrim');


        $data_inicio = new Zend_Form_Element_Text('AQCL_DH_CRIACAO');
        $data_inicio->setAttrib('class', 'AQAS_DH_CRIACAO DATA_INICIAL');

        $data_inicio->setLabel('* Data de criação:')
                ->addFilter('StripTags')
                ->setAttrib('required', 'required')
                ->setRequired(true)
                ->addFilter('StringTrim')
                ->addFilter('HtmlEntities')
                ->addValidator('NotEmpty');

        $data_fim = new Zend_Form_Element_Text('AQCL_DH_FIM');
        $data_fim->setAttrib('class', 'AQAS_DH_FIM DATA_FINAL');
        $data_fim->setLabel('Data de finalização:')
                ->addFilter('StripTags')
               // ->setRequired(true)
               // ->setAttrib('required', 'required')
                ->addFilter('StringTrim')
                ->addFilter('HtmlEntities')
                ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Enviar', array(
            'class' => 'novo'));
        $voltar = new Zend_Form_Element_Button('Cancelar', array(
            'class' => 'novo'));
        $voltar->setAttrib('id', 'voltar');
        $this->addElements(array($codigo, $assunto, $data_inicio, $data_fim, $submit, $voltar));
    }

}
