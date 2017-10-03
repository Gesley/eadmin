<?php
class Orcamento_Form_Programa extends Zend_Form {
	public function init() {
		$this->setName ( 'frmPrograma' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmPrograma' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDPrograma = new Zend_Form_Element_Text ( 'PPRG_CD_PROGRAMA' );
		$txtIDPrograma->setLabel ( 'Programa:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 2 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtAAPrograma = new Zend_Form_Element_Text ( 'PPRG_AA_PROGRAMA' );
		$txtAAPrograma->setLabel ( 'Ano:' )->setRequired ( true )->setAttrib ( 'size', '4' )->setAttrib ( 'maxlength', 4 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDEPrograma = new Zend_Form_Element_Textarea ( 'PPRG_DS_PROGRAMA' );
		$txtDEPrograma->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 140 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDPrograma, $txtAAPrograma, $txtDEPrograma, $cmdSubmit ) );
	}
}