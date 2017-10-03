<?php
class Orcamento_Form_Tiposolicitacao extends Zend_Form {
	public function init() {
		$this->setName ( 'frmTipoSolicitacao' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmTipoSolicitacao' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDTiposolicitacao = new Zend_Form_Element_Text ( 'TSOL_CD_TIPO_SOLICITACAO' );
		$txtIDTiposolicitacao->setLabel ( 'Tipo de solicitação:' )->setRequired ( false )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 2 )->addValidator ( 'Digits' );
		
		$txtDETiposolicitacao = new Zend_Form_Element_Textarea ( 'TSOL_DS_TIPO_SOLICITACAO' );
		$txtDETiposolicitacao->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 40 )->setAttrib ( 'maxlength', 80 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDTiposolicitacao, $txtDETiposolicitacao, $cmdSubmit ) );
	}
}