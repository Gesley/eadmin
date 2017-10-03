<?php
class Orcamento_Form_Categoria extends Zend_Form {
	public function init() {
		$this->setName ( 'frmCategoria' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmCategoria' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDCategoria = new Zend_Form_Element_Text ( 'CATE_CD_CATEGORIA' );
		$txtIDCategoria->setLabel ( 'Categoria de gasto:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 1 )->addFilters ( array ('Alpha', 'StringToUpper' ) )->addValidator ( 'Alpha' );
		
		$txtDECategoria = new Zend_Form_Element_Textarea ( 'CATE_DS_CATEGORIA' );
		$txtDECategoria->setLabel ( 'Descrição da categoria:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 40 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDCategoria, $txtDECategoria, $cmdSubmit ) );
	}
}