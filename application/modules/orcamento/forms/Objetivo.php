<?php
class Orcamento_Form_Objetivo extends Zend_Form {
	public function init() {
		$this->setName ( 'frmObjetivo' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmObjetivo' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDObjetivo = new Zend_Form_Element_Text ( 'POBJ_CD_OBJETIVO' );
		$txtIDObjetivo->setLabel ( 'Objetivo:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 2 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtAAObjetivo = new Zend_Form_Element_Text ( 'POBJ_AA_OBJETIVO' );
		$txtAAObjetivo->setLabel ( 'Ano:' )->setRequired ( true )->setAttrib ( 'size', '4' )->setAttrib ( 'maxlength', 4 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDEObjetivo = new Zend_Form_Element_Textarea ( 'POBJ_DS_OBJETIVO' );
		$txtDEObjetivo->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 300 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDObjetivo, $txtAAObjetivo, $txtDEObjetivo, $cmdSubmit ) );
	}
}