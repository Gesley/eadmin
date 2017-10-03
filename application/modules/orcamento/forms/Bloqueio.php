<?php
class Orcamento_Form_Bloqueio extends Zend_Form {
	public function init() {
		$this->setName ( 'frmBloqueio' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmBloqueio' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDPtres = new Zend_Form_Element_Text ( 'REMB_CD_PT_RESUMIDO' );
		$txtIDPtres->setLabel ( 'PTRES:' )->setRequired ( true )->setAttribs ( array ('size' => '90', 'maxlength' => 20 ) )->setDescription ( 'A lista será carregada após digitar 3 caracteres.' );
		
		$txtIDElemento = new Zend_Form_Element_Text ( 'REMB_CD_ELEMENTO_DESPESA_SUB' );
		$txtIDElemento->setLabel ( 'Natureza da despesa:' )->setRequired ( true )->setAttribs ( array ('size' => '70', 'maxlength' => 20 ) )->setDescription ( 'A lista será carregada após digitar 3 caracteres.' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDPtres, $txtIDElemento, $cmdSubmit ) );
	}
}