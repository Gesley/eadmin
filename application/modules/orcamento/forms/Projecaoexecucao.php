<?php
class Orcamento_Form_Projecaoexecucao extends Zend_Form {
	public function init() {
		/*
		 * Definição das variáveis do tamanho e digitos do forms
		 */
		$tamanho = 18;
		$maxLenght = 14;
		
		/* ************************************************************
		 * Definições do form
		************************************************************ */
		$this->setName ( 'frmProjecaoExecucao' ) /* ->setMethod('post') */ ->setAttrib ( 'id', 'frmProjecaoExecucao' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		/* ************************************************************
		 * Campos - Valores da Execução
		************************************************************ */
		// Valor da execução - Janeiro
		$txtVRJaneiro = new Zend_Form_Element_Text ( 'EXEC_VL_JANEIRO' );
		$txtVRJaneiro->setLabel ( 'Janeiro:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Fevereiro
		$txtVRFevereiro = new Zend_Form_Element_Text ( 'EXEC_VL_FEVEREIRO' );
		$txtVRFevereiro->setLabel ( 'Fevereiro:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Março
		$txtVRMarco = new Zend_Form_Element_Text ( 'EXEC_VL_MARCO' );
		$txtVRMarco->setLabel ( 'Março:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Abril
		$txtVRAbril = new Zend_Form_Element_Text ( 'EXEC_VL_ABRIL' );
		$txtVRAbril->setLabel ( 'Abril:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Maio
		$txtVRMaio = new Zend_Form_Element_Text ( 'EXEC_VL_MAIO' );
		$txtVRMaio->setLabel ( 'Maio:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Junho
		$txtVRJunho = new Zend_Form_Element_Text ( 'EXEC_VL_JUNHO' );
		$txtVRJunho->setLabel ( 'Junho:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Julho
		$txtVRJulho = new Zend_Form_Element_Text ( 'EXEC_VL_JULHO' );
		$txtVRJulho->setLabel ( 'Julho:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Agosto
		$txtVRAgosto = new Zend_Form_Element_Text ( 'EXEC_VL_AGOSTO' );
		$txtVRAgosto->setLabel ( 'Agosto:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Setembro
		$txtVRSetembro = new Zend_Form_Element_Text ( 'EXEC_VL_SETEMBRO' );
		$txtVRSetembro->setLabel ( 'Setembro:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Outubro
		$txtVROutubro = new Zend_Form_Element_Text ( 'EXEC_VL_OUTUBRO' );
		$txtVROutubro->setLabel ( 'Outubro:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Novembro
		$txtVRNovembro = new Zend_Form_Element_Text ( 'EXEC_VL_NOVEMBRO' );
		$txtVRNovembro->setLabel ( 'Novembro:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		// Valor da execução - Dezembro
		$txtVRDezembro = new Zend_Form_Element_Text ( 'EXEC_VL_DEZEMBRO' );
		$txtVRDezembro->setLabel ( 'Dezembro:' )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght, 'readonly' => 'true', 'class' => 'execucao' ) );
		
		$txtVRTOTAL = new Zend_Form_Element_Text ( 'EXEC_VL_TOTAL' );
		$txtVRTOTAL->setLabel ( 'Total:' )->setRequired ( false )->setAttrib ( 'readonly', true );
		
		$this->addElements ( array ($txtVRJaneiro, $txtVRFevereiro, $txtVRMarco, $txtVRAbril, $txtVRMaio, $txtVRJunho, $txtVRJulho, $txtVRAgosto, $txtVRSetembro, $txtVROutubro, $txtVRNovembro, $txtVRDezembro, $txtVRTOTAL ) );
	}
}