<?php
class Orcamento_Form_Pendencia extends Zend_Form {
	public function init() {
		$this->setName ( 'frmPendencia' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmPendencia' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$negocioPendencia = new Trf1_Orcamento_Negocio_Pendencia ();
		$cboAno = new Zend_Form_Element_Select ( 'ANO' );
		$cboAno->setLabel ( 'Ano / Exercício:' )->addFilter ( 'StripTags' )->addMultiOptions ( $negocioPendencia->retornaAnosSistema () )->setRequired ( true );
		
		// Cria o campo PTRS_CD_UNID_ORCAMENTARIA
		$cboAno = new Zend_Form_Element_Select ( 'ANO' );
		
		// Dados sobre exercícios
		$tbAno = new Orcamento_Business_Negocio_Exercicio ();
		$exercicios = $tbAno->retornaCombo ();
		
		// Define opções o controle $txtAno
		$cboAno->setLabel ( 'Ano:' );
		$cboAno->addFilter ( 'StringTrim' );
		$cboAno->addFilter ( 'StripTags' );
		$cboAno->addMultiOptions ( array ( '' => 'Selecione' ) );
		$cboAno->addMultiOptions ( $exercicios );
		$cboAno->setRequired ( true );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Atualizar' );
		$cmdSubmit->setLabel ( 'Atualizar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_consultar' )->setAttrib ( 'title', 'Realizar nova consulta às pendências' );
		
		$this->addElements ( array ($cboAno, $cmdSubmit ) );
	}
}