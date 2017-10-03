<?php
class Orcamento_Form_Tiponc extends Zend_Form {
	public function init() {
		$this->setName ( 'frmTipoNC' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmTipoNC' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDTiponc = new Zend_Form_Element_Text ( 'TPNC_CD_TIPO_NC' );
		$txtIDTiponc->setLabel ( 'Tipo de nota de crédito:' )->setRequired ( false )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 1 )->addFilters ( array ('Alpha', 'StringToUpper' ) )->addValidator ( 'Alpha' );
		
		$txtDETiponc = new Zend_Form_Element_Textarea ( 'TPNC_DS_TIPO_NC' );
		$txtDETiponc->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 40 )->setAttrib ( 'maxlength', 80 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDTiponc, $txtDETiponc, $cmdSubmit ) );
	}
}