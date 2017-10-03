<?php
class Orcamento_Form_Projecaojustificativa extends Zend_Form {
	public function init() {
		$this->setName ( 'frmProjecaoJustificativa' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmProjecaoJustificativa' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtDSJustif = new Zend_Form_Element_Textarea ( 'PRJJ_DS_JUSTIFICATIVA' );
		$txtDSJustif->setLabel ( 'Justificativa:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 1000 )->addFilter ( 'StringTrim' );
		
		$txtNRDesp = new Zend_Form_Element_Hidden ( 'PRJJ_NR_DESPESA' );
		$txtNRDesp->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 8 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDHJustif = new Zend_Form_Element_Hidden ( 'PRJJ_DH_JUSTIFICATIVA' );
		
		$txtIC = new Zend_Form_Element_Hidden ( 'PRJJ_IC_ORIGEM' );
		$txtIC->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 1 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtDSJustif, $cmdSubmit ) );
	}
}