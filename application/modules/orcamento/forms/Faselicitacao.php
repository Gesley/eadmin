<?php
class Orcamento_Form_Faselicitacao extends Zend_Form {
	public function init() {
		$this->setName ( 'frmFaseLicitacao' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmFaseLicitacao' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDFaseLicitacao = new Zend_Form_Element_Text ( 'FASL_CD_FASE' );
		$txtIDFaseLicitacao->setLabel ( 'Fase da licitação:' )->setRequired ( false )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 2 )->addValidator ( 'Digits' );
		
		$txtDEFaseLicitacao = new Zend_Form_Element_Textarea ( 'FASL_DS_FASE' );
		$txtDEFaseLicitacao->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 40 )->setAttrib ( 'maxlength', 80 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDFaseLicitacao, $txtDEFaseLicitacao, $cmdSubmit ) );
	}
}