<?php
class Orcamento_Form_Evento extends Zend_Form {
	public function init() {
		$this->setName ( 'frmEvento' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmEvento' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtIDEvento = new Zend_Form_Element_Text ( 'EVEN_CD_EVENTO' );
		$txtIDEvento->setLabel ( 'Evento:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 6 )->addValidator ( 'Digits' );
		
		$txtDSEvento = new Zend_Form_Element_Textarea ( 'EVEN_DS_EVENTO' );
		$txtDSEvento->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 40 )->setAttrib ( 'maxlength', 60 )->addFilter ( 'StringTrim' );
		
		// Tipo da variação
		$tbSinalEvento = new Trf1_Orcamento_Negocio_Evento ();
		$cboSinal = new Zend_Form_Element_Select ( 'EVEN_IC_SINAL_EVENTO' );
		$cboSinal->setLabel ( 'Sinal:' )->addFilter ( 'StripTags' )->addMultiOptions ( $tbSinalEvento->retornaVariacaoCombo () )->setRequired ( true );
		
		$txtDSDocumento = new Zend_Form_Element_Text ( 'EVEN_DS_DOCUMENTO' );
		$txtDSDocumento->setLabel ( 'Documento:' )->setRequired ( true )->setAttrib ( 'size', 20 )->setAttrib ( 'maxlength', 10 )->addFilter ( 'StringTrim' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtIDEvento, $txtDSEvento, $cboSinal, $txtDSDocumento, $cmdSubmit ) );
	}
}