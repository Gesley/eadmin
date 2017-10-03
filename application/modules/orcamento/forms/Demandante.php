<?php
class Orcamento_Form_Demandante extends Zend_Form {
	public function init() {
		$this->setName ( 'frmDemandante' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmDemandante' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDDemandante = new Zend_Form_Element_Text ( 'DEMN_CD_DEMANDANTE' );
		$txtIDDemandante->setLabel ( 'Demandante:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 2 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDEDemandante = new Zend_Form_Element_Textarea ( 'DEMN_DS_DEMANDANTE' );
		$txtDEDemandante->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 80 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDDemandante, $txtDEDemandante, $cmdSubmit ) );
	}
}