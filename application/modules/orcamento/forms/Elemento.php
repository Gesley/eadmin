<?php
class Orcamento_Form_Elemento extends Zend_Form {
	public function init() {
		$this->setName ( 'frmElemento' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmElemento' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDElemento = new Zend_Form_Element_Text ( 'EDSB_CD_ELEMENTO_DESPESA_SUB' );
		$txtIDElemento->setLabel ( 'Natureza da despesa:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 8 )->addValidator ( 'Digits' );
		
		$txtDEElemento = new Zend_Form_Element_Textarea ( 'EDSB_DS_ELEMENTO_DESPESA_SUB' );
		$txtDEElemento->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 80 )->addFilter ( 'StringTrim' );
		
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDElemento, $txtDEElemento, $cmdSubmit ) );
	}
}
