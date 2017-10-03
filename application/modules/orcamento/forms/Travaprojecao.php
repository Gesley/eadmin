<?php
class Orcamento_Form_TravaProjecao extends Zend_Form {
	public function init() {
		$this->setName ( 'frmTravaProjecao' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmTravaProjecao' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$tbUg = new Trf1_Orcamento_Negocio_Ug ();
		$txtCDUGProjecao = new Zend_Form_Element_Select ( 'TRVP_CD_UG' );
		$txtCDUGProjecao->setLabel ( 'Sigla:' )->setRequired ( true )->addMultiOptions ( array ('' => 'Selecione' ) )->addMultiOptions ( $tbUg->retornaCombo () )->addValidator ( 'Digits' );
		
		$txtDTInicio = new Zend_Form_Element_Text ( 'TRVP_DT_INICIO' );
		$txtDTInicio->setLabel ( 'Início do bloqueio:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'class', 'datepicker' );
		
		$txtDTFim = new Zend_Form_Element_Text ( 'TRVP_DT_FIM' );
		$txtDTFim->setLabel ( 'Término do bloqueio :' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'class', 'datepicker' );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtCDUGProjecao, $txtDTInicio, $txtDTFim, $cmdSubmit ) );
	}
}