<?php

class Sisad_Form_VerificaUsuario extends Zend_Form {

    public function init() {
        $this->setName('Login')
                ->setAttrib('id', 'login')
                ->setMethod('post')
                ->setElementFilters(array('StripTags', 'StringTrim'));

        $cpf = new Zend_Form_Element_Text('COU_COD_MATRICULA');
        $cpf->addValidator('NotEmpty')
                ->setLabel('Matrícula:')
                ->setAttrib('size', '25')
                ->setAttrib('disabled', 'disabled')
                ->setRequired(true)
                ->addValidator('Alnum')
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', false, array(4, 11))
                ->setOptions(array('maxLength' => 11));

        $password = new Zend_Form_Element_Password('COU_COD_PASSWORD');
        $password->addValidator('NotEmpty')
                ->setLabel('Senha:')
                ->setAttrib('size', '26')
                ->setRequired(true)
                ->addFilter('HtmlEntities')
                ->addFilter('StringTrim');
         $docs = new Zend_Form_Element_Hidden('documentosSelecionados');
         
		$acao = new Zend_Form_Element_Hidden('acao');
        	 $acao->setValue('AssinarDocs');
        	 
        $mofa_id_movi = new Zend_Form_Element_Hidden('MOFA_ID_MOVIMENTACAO');
         $assinar = new Zend_Form_Element_Button('Assinar');
         $assinar->setAttrib('onclick', 'submitVerificar();');
			
        $this->addElements(array($cpf, $password, $assinar, $docs,$acao,$mofa_id_movi));
    }

}