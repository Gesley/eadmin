<?php
class Orcamento_Form_Uo extends Zend_Form {
	public function init() {
		$this->setName ( 'frmUO' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmUO' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtCDUG = new Zend_Form_Element_Text ( 'UNOR_CD_UNID_ORCAMENTARIA' );
		$txtCDUG->setLabel ( 'UO:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 5 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDSUG = new Zend_Form_Element_Textarea ( 'UNOR_DS_UNID_ORCAMENTARIA' );
		$txtDSUG->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 80 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtCDUG, $txtDSUG, $cmdSubmit ) );
	}
}