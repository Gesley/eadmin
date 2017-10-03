<?php
class Orcamento_Form_RecursoDesc extends Zend_Form {
	public function init() {
		$this->setName ( 'frmRecursoDesc' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmRecursoDesc' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$txtRecurso = new Zend_Form_Element_Text ( 'RECD_CD_RECURSO' );
		$txtRecurso->setLabel ( 'Recurso:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 8 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtDespesa = new Zend_Form_Element_Text ( 'RECD_NR_DESPESA' );
		$txtDespesa->setLabel ( 'Despesa:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'maxlength', 8 )->addFilter ( 'Digits' )->addValidator ( 'Digits' );
		
		$txtJustif = new Zend_Form_Element_Textarea ( 'RECD_DS_JUSTIFICATIVA' );
		$txtJustif->setLabel ( 'Observação:' )->setRequired ( true )->setAttrib ( 'size', '40' )->setAttrib ( 'maxlength', 400 )->addFilter ( 'StringTrim' );
		
		$txtVlRec = new Zend_Form_Element_Text ( 'RECD_VL_RECURSO' );
		$txtVlRec->setLabel ( 'Valor:' )->setRequired ( true )->setAttribs ( array ('size' => '10', 'maxlength' => 20, 'class' => 'valordespesa' ) );
		
		$txtData = new Zend_Form_Element_Text ( 'RECD_DT_RECURSO' );
		$txtData->setLabel ( 'Data:' )->setRequired ( true )->setAttrib ( 'size', '10' )->setAttrib ( 'class', 'datepicker' );
		
		$tbFaseLicitacao = new Trf1_Orcamento_Negocio_Faselicitacao ();
		$cboFaseLicitacao = new Zend_Form_Element_Select ( 'RECD_CD_FASE' );
		$cboFaseLicitacao->setLabel ( 'Fase da licitação:' )->setRequired ( true )->setMultiOptions ( $tbFaseLicitacao->retornaCombo () );
		
		$chkDescentralizado = new Zend_Form_Element_Checkbox ( 'RECD_IC_RECURSO' );
		$chkDescentralizado->setLabel ( 'Recurso descentralizado?' )->setRequired ( true );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array ($txtDespesa, $txtJustif, $txtVlRec, $txtData, $cboFaseLicitacao, $chkDescentralizado, $cmdSubmit ) );
	}
}  