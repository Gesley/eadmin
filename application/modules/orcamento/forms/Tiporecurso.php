<?php
class Orcamento_Form_TipoRecurso extends Zend_Form {
	public function init() {
		$this->setName ( 'frmTipoRecurso' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmTipoRecurso' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDTipoRec = new Zend_Form_Element_Text ( 'TREC_CD_TIPO_RECURSO' );
		$txtIDTipoRec->setLabel ( 'Tipo de recurso:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 1 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDETipoRec = new Zend_Form_Element_Textarea ( 'TREC_DS_TIPO_RECURSO' );
		$txtDETipoRec->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 60 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDTipoRec, $txtDETipoRec, $cmdSubmit ) );
	}
}