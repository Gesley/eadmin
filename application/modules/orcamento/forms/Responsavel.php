<?php
class Orcamento_Form_Responsavel extends Zend_Form {
	public function init() {
		$this->setName ( 'frmResponsavel' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmResponsavel' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtCdResponsavel = new Zend_Form_Element_Text ( 'RESP_CD_RESPONSAVEL' );
		$txtCdResponsavel->setLabel ( 'Código:' )->setRequired ( true )->setAttrib ( 'size', '10' )->addValidator ( 'Digits' )->setAttrib ( 'maxlength', 8 );
		
		// UG - Unidade Gestora
		$tbUG = new Trf1_Orcamento_Negocio_Ug ();
		$cboUG = new Zend_Form_Element_Select ( 'UG' );
		$cboUG->setLabel ( 'Unidade gestora:' )->addFilter ( 'StripTags' )->addMultiOptions ( array ('' => 'Selecione' ) )->addMultiOptions ( $tbUG->retornaComboLotacoes () )->setRequired ( true );
		
		$txtDsLotacao = new Zend_Form_Element_Text ( 'RESPONSAVEL' );
		$txtDsLotacao->setLabel ( 'Responsável' )->setRequired ( false )->setAttribs ( array ('size' => '100', 'maxlength' => 120 ) )->setDescription ( 'A lista será carregada após digitar 3 caracteres.' );
		
		$txtCdLotacao = new Zend_Form_Element_Hidden ( 'RESP_CD_LOTACAO' );
		$txtCdLotacao->setRequired ( true )->setAttrib ( 'size', '10' )->addFilter ( 'Int' )->addValidator ( 'Int' )->setAttrib ( 'maxlength', 5 );
		
		$txtDsSecao = new Zend_Form_Element_Hidden ( 'RESP_DS_SECAO' );
		$txtDsSecao->setRequired ( true )->setAttrib ( 'size', '10' )->addFilter ( 'Alnum' )->addFilter ( 'StringToUpper' )->addValidator ( 'Alnum' )->setAttrib ( 'maxlength', 2 );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtCdResponsavel, $cboUG, $txtDsLotacao, $cmdSubmit, $txtCdLotacao, $txtDsSecao ) );
	}

}
   