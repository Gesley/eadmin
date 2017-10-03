<?php
/**
 * Pergunta o período (ano e mês) para buscar logs em questão
 * 
 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
 */
class Orcamento_Form_Log extends Zend_Form {
	public function init() {
		$this->setName ( 'frmLog' )->setMethod ( 'post' )->setAction ( 'log/listagem' )->setAttrib ( 'id', 'frmLog' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		$negocioPendencia = new Trf1_Orcamento_Negocio_Pendencia ();
		
		$cboAno = new Zend_Form_Element_Select ( 'ANO' );
		$cboAno->setLabel ( 'Ano:' )->addFilter ( 'StripTags' )->addMultiOptions ( $negocioPendencia->retornaAnosSistema () )->setRequired ( true );
		
		$cboMes = new Zend_Form_Element_Select ( 'MES' );
		$cboMes->setLabel ( 'Mês:' )->addFilter ( 'StripTags' )->addMultiOptions ( $negocioPendencia->retornaMeses () )->setRequired ( true );
		
		$cboParametros = new Zend_Form_Element_Select ( 'PARAMETRO' );
		$cboParametros->setLabel ( 'Detalhe dos parâmetros:' )->addFilter ( 'StripTags' )->addMultiOptions ( array (0 => 'Resumido', 1 => 'Completo' ) )->setRequired ( true );
		
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Consultar' );
		$cmdSubmit->setLabel ( 'Consultar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_consultar' );
		
		$this->addElements ( array ($cboAno, $cboMes, $cboParametros, $cmdSubmit ) );
	}
}