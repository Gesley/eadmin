<?php
class Orcamento_Form_Tipooperacional extends Zend_Form {
	public function init() {
		$this->setName ( 'frmTipoOperacional' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmTipoOperacional' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDTipoOperacional = new Zend_Form_Element_Text ( 'POPE_CD_TIPO_OPERACIONAL' );
		$txtIDTipoOperacional->setLabel ( 'Tipo operacional:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 1 )->addFilter ( 'Alnum' )->addValidator ( 'Alnum' );
		
		$txtDETipoOperacional = new Zend_Form_Element_Textarea ( 'POPE_DS_TIPO_OPERACIONAL' );
		$txtDETipoOperacional->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 30 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDTipoOperacional, $txtDETipoOperacional, $cmdSubmit ) );
	}
}