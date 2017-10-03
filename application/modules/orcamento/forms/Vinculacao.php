<?php
class Orcamento_Form_Vinculacao extends Zend_Form {
	public function init() {
		$this->setName ( 'frmVinculacao' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmVinculacao' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDVinculacao = new Zend_Form_Element_Text ( 'VINC_CD_VINCULACAO' );
		$txtIDVinculacao->setLabel ( 'Vinculação:' )->setRequired ( true )->setAttrib ( 'size', '4' )->setAttrib ( 'maxlength', 4 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDEVinculacao = new Zend_Form_Element_Textarea ( 'VINC_DS_VINCULACAO' );
		$txtDEVinculacao->setLabel ( 'Descrição da vinculação:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 40 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDVinculacao, $txtDEVinculacao, $cmdSubmit ) );
	}
}
