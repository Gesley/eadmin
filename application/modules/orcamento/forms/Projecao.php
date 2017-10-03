<?php
class Orcamento_Form_Projecao extends Zend_Form {
	public function init() {
		/*
		 * Definição das variáveis do tamanho e digitos do forms
		 */
		$tamanho = 18;
		$maxLenght = 14;
		
		/* ************************************************************
		 * Definições do form
		************************************************************ */
		$this->setName ( 'frmProjecao' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmProjecao' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		/* ************************************************************
		 * Decorators ???
		************************************************************ */
		//TODO: ver modificação / inclusão de decorators
		

		/* ************************************************************
		 * Campos - Identificação
		************************************************************ */
		/*
		// Despesa
		$txtIDDespesa = new Zend_Form_Element_Text('DESP_NR_DESPESA');
		$txtIDDespesa				->setLabel('Código da Despesa:')
									->setRequired(true)
									->setAttrib('size', '10')
									->setAttrib('style', 'font-weight: bold; text-align: center')
									->addFilter('Int')
									->addValidator('Int');
		*/
		
		/* ************************************************************
		 * Campos - Valores
		************************************************************ */
		// Valor da projeção - Janeiro
		$txtVRJaneiro = new Zend_Form_Element_Text ( 'PROJ_VR_JANEIRO' );
		$txtVRJaneiro->setLabel ( 'Janeiro:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Fevereiro
		$txtVRFevereiro = new Zend_Form_Element_Text ( 'PROJ_VR_FEVEREIRO' );
		$txtVRFevereiro->setLabel ( 'Fevereiro:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Março
		$txtVRMarco = new Zend_Form_Element_Text ( 'PROJ_VR_MARCO' );
		$txtVRMarco->setLabel ( 'Março:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Abril
		$txtVRAbril = new Zend_Form_Element_Text ( 'PROJ_VR_ABRIL' );
		$txtVRAbril->setLabel ( 'Abril:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Maio
		$txtVRMaio = new Zend_Form_Element_Text ( 'PROJ_VR_MAIO' );
		$txtVRMaio->setLabel ( 'Maio:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Junho
		$txtVRJunho = new Zend_Form_Element_Text ( 'PROJ_VR_JUNHO' );
		$txtVRJunho->setLabel ( 'Junho:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		// Valor da projeção - Julho
		$txtVRJulho = new Zend_Form_Element_Text ( 'PROJ_VR_JULHO' );
		$txtVRJulho->setLabel ( 'Julho:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Agosto
		$txtVRAgosto = new Zend_Form_Element_Text ( 'PROJ_VR_AGOSTO' );
		$txtVRAgosto->setLabel ( 'Agosto:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Setembro
		$txtVRSetembro = new Zend_Form_Element_Text ( 'PROJ_VR_SETEMBRO' );
		$txtVRSetembro->setLabel ( 'Setembro:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Outubro
		$txtVROutubro = new Zend_Form_Element_Text ( 'PROJ_VR_OUTUBRO' );
		$txtVROutubro->setLabel ( 'Outubro:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Novembro
		$txtVRNovembro = new Zend_Form_Element_Text ( 'PROJ_VR_NOVEMBRO' );
		$txtVRNovembro->setLabel ( 'Novembro:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		// Valor da projeção - Dezembro
		$txtVRDezembro = new Zend_Form_Element_Text ( 'PROJ_VR_DEZEMBRO' );
		$txtVRDezembro->setLabel ( 'Dezembro:' )->setRequired ( true )->setAttribs ( array ('size' => $tamanho, 'maxlength' => $maxLenght ) )->addValidator ( 'Between', false, array ('min' => 0, 'max' => 99999999999999, 'messages' => 'Valor da projeção deve ser maior ou igual a 0 (zero)' ) )->setAttrib ( 'class', 'projecao' );
		
		$txtPROJ_VR_TOTAL = new Zend_Form_Element_Text ( 'PROJ_VR_TOTAL' );
		$txtPROJ_VR_TOTAL->setLabel ( 'Total:' )->setRequired ( false )->setAttrib ( 'readonly', true );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Salvar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_salvar' );
		
		$this->addElements ( array (/* $txtIDDespesa, */ $txtVRJaneiro, $txtVRFevereiro, $txtVRMarco, $txtVRAbril, $txtVRMaio, $txtVRJunho, $txtVRJulho, $txtVRAgosto, $txtVRSetembro, $txtVROutubro, $txtVRNovembro, $txtVRDezembro, $txtPROJ_VR_TOTAL, $cmdSubmit ) );
	}
}