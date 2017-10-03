<?php
class Orcamento_Form_Tipoorcamento extends Zend_Form {
	public function init() {
		$this->setName ( 'frmTipoOrcamento' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmTipoOrcamento' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDTipoOrcamento = new Zend_Form_Element_Text ( 'PORC_CD_TIPO_ORCAMENTO' );
		$txtIDTipoOrcamento->setLabel ( 'Tipo de orçamento:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 1 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDETipoOrcamento = new Zend_Form_Element_Textarea ( 'PORC_DS_TIPO_ORCAMENTO' );
		$txtDETipoOrcamento->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 30 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDTipoOrcamento, $txtDETipoOrcamento, $cmdSubmit ) );
	}
}
