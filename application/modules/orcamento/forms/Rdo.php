<?php
class Orcamento_Form_Rdo extends Zend_Form {
	public function init() {
		$this->setName ( 'frmRdo' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmRdo' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );

		$txtNRReq = new Zend_Form_Element_Text ( 'REQV_NR_DESPESA' );
		$txtNRReq->setLabel ( 'Despesa:' )->setRequired ( true )->setAttrib ( 'size', '10' )->addFilter ( 'Digits' )->addValidator ( new Trf1_Orcamento_Validacao_Despesa () )->setDescription ( 'Preencha o número da despesa' );

		$txtDSDet = new Zend_Form_Element_Textarea ( 'REQV_DS_DETALHAMENTO' );
		$txtDSDet->setLabel ( 'Descrição da variação:' )->setRequired ( true )->setAttrib ( 'size', '10' )->addFilter ( 'StringTrim' );

		// $rdPAdm = new Zend_Form_Element_Radio ( 'TIPO_PROCESSO' );
		// $rdPAdm->setLabel ( 'Tipo de processo administrativo: ' )->addMultiOptions ( array (0 => 'Digital', 1 => 'Fisico' ) )->setSeparator ( '   ' );

		// $validaPa = new Orcamento_Business_Validacao_Pa ();
		// $validaPa->defineTipo($rdPAdm);

		$txtPAdm = new Zend_Form_Element_Text ( 'REQV_NR_PROCESSO_ADM' );
		$txtPAdm->setLabel ( 'Processo: ' )->setRequired ( false )->setAttrib ( 'size', '90' );

		// Tipo da variação
		$tbVariacao = new Trf1_Orcamento_Negocio_Rdo ();
		$cboICTP = new Zend_Form_Element_Select ( 'REQV_IC_TP_VARIACAO' );
		$cboICTP->setLabel ( 'Variação:' )->addFilter ( 'StripTags' )->addMultiOptions ( array ('' => 'Selecione' ) )->addMultiOptions ( $tbVariacao->getVariacaoCombo () )->setRequired ( true );

		$txtVLVar = new Zend_Form_Element_Text ( 'REQV_VL_VARIACAO' );
		$txtVLVar->setLabel ( 'Valor:' )->setRequired ( true )->setAttribs ( array ('size' => 20, 'maxlength' => 18, 'class' => 'valordespesa' ) );

		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );

		$this->addElements ( array ($txtNRReq, $txtDSDet, $rdPAdm, $txtPAdm, $cboICTP, $txtVLVar, $cmdSubmit ) );

	}
}
