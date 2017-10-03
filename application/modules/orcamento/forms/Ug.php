<?php
class Orcamento_Form_Ug extends Zend_Form {
	public function init() {
		$this->setName ( 'frmPtres' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmPtres' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtCDUG = new Zend_Form_Element_Text ( 'UNGE_CD_UG' );
		$txtCDUG->setLabel ( 'UG:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 6 )->addValidator ( 'Digits' );
		
		$txtDSUG = new Zend_Form_Element_Textarea ( 'UNGE_DS_UG' );
		$txtDSUG->setLabel ( 'Descrição:' )->setRequired ( true )->setAttrib ( 'size', 10 )->setAttrib ( 'maxlength', 80 )->addFilter ( 'StringTrim' );
		
		$txtSGUG = new Zend_Form_Element_Text ( 'UNGE_SG_UG' );
		$txtSGUG->setLabel ( 'Sigla:' )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 5 )->setRequired ( true );
		
		// Unidades orçamentárias
		$tbPadraoUG = new Trf1_Orcamento_Negocio_Padraoug ();
		$cboPadrao = new Zend_Form_Element_Select ( 'UNGE_CD_UG_PADRAO' );
		$cboPadrao->setLabel ( 'Padrão:' )->addFilter ( 'StripTags' )->addMultiOptions ( array ('' => 'Sem padrão' ) )->addMultiOptions ( $tbPadraoUG->retornaCombo () );
		
		$txtDsLotacao = new Zend_Form_Element_Text ( 'LOTACAO' );
		$txtDsLotacao->setLabel ( 'Lotação:' )->setAttrib ( 'size', '100' )->setAttrib ( 'maxlength', 120 )->setDescription ( 'A lista será carregada após digitar 3 caracteres.' )->setRequired ( true );
		
		$txtCdLotacao = new Zend_Form_Element_Hidden ( 'UNGE_CD_LOTACAO' );
		$txtCdLotacao->setRequired ( false )->setAttrib ( 'size', 10 )->setAttrib ( 'maxlength', 6 );
		
		$txtSgSecao = new Zend_Form_Element_Hidden ( 'UNGE_SG_SECAO' );
		$txtSgSecao->setRequired ( false )->setAttrib ( 'size', 10 )->setAttrib ( 'maxlength', 6 );
		
		$secSubc = new Trf1_Orcamento_Negocio_Ug ();
		$cboSecSubsec = new Zend_Form_Element_Select ( 'UNGE_CD_SECSUBSEC' );
		$cboSecSubsec->setLabel ( 'Seção/Subseção:' )->addFilter ( 'StripTags' )->addMultiOptions ( array ('' => 'Selecione' ) )->addMultiOptions ( $secSubc->comboSecSubsecao () )->setRequired ( true );
		
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtCDUG, $txtDSUG, $txtSGUG, $cboPadrao, $cboSecSubsec, $txtDsLotacao, $cmdSubmit, $txtCdLotacao, $txtSgSecao ) );
	}
}