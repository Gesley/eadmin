<?php
class Orcamento_Form_Fonte extends Zend_Form {
	public function init() {
		$this->setName ( 'frmFonte' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmFonte' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDFonte = new Zend_Form_Element_Text ( 'FONT_CD_FONTE' );
		$txtIDFonte->setLabel ( 'Fonte de recurso:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 3 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDEFonte = new Zend_Form_Element_Textarea ( 'FONT_DS_FONTE' );
		$txtDEFonte->setLabel ( 'Descrição da fonte:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 40 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDFonte, $txtDEFonte, $cmdSubmit ) );
	}
}