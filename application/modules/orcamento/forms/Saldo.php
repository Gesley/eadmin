<?php
class Orcamento_Form_Saldo extends Zend_Form {
	public function init() {
		$this->setName ( 'frmSaldo' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmSaldo' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDDespesa = new Zend_Form_Element_Text ( 'DESP_NR_DESPESA' );
		$txtIDDespesa->setLabel ( 'Despesa:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'autofocus', 'autofucus' )->addValidator ( 'Digits' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Consultar' );
		$cmdSubmit->setLabel ( 'Consultar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_consultar' );
		
		$this->addElements ( array ($txtIDDespesa, $cmdSubmit ) );
	}
}