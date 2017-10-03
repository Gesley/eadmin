<?php
class Orcamento_Form_Padraoug extends Zend_Form {
	public function init() {
		$this->setName ( 'frmPadraoUG' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmPadraoUG' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtCDPadraoUG = new Zend_Form_Element_Text ( 'PADR_CD_UG' );
		$txtCDPadraoUG->setLabel ( 'Padrão da UG:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 1 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDSPadraoUG = new Zend_Form_Element_Textarea ( 'PADR_DS_UG' );
		$txtDSPadraoUG->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', '30' )->setAttrib ( 'maxlength', 30 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtCDPadraoUG, $txtDSPadraoUG, $cmdSubmit ) );
	}
}