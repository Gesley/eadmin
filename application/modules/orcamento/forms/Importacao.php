<?php
class Orcamento_Form_Importacao extends Zend_Form {
	public function init() {
		$this->setName ( 'frmImportacao' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmImportacao' )->setAttrib ( 'enctype', 'multipart/form-data' )->setElementFilters ( array ('StripTags', 'StringTrim' ) );
		
		// Chamada para regras aplicáveis ao formulário
		$regra = new Trf1_Orcamento_Negocio_Importacao ();
		$tiposImportacao = $regra->retornaOpcoesImportacao ();
		
		// Definições sobre o(s) arquivo(s) a importar
		$pastaImportacao = $regra->retornaPastaImportacao ();
		
		$txtArquivoTXT = new Zend_Form_Element_File ( 'TEXTO' );
		$txtArquivoTXT->setRequired ( true )->setLabel ( 'Arquivos a importar:' )->setDescription ( 'Favor selecionar todos os arquivos a importar, tanto o(s) arquivo(s) de dados (.txt) quanto o(s) de referência (.ref)' )->setDestination ( $pastaImportacao )->setAttribs ( array ('size' => '60', 'multiple' => 'multiple' ) )->addValidator ( new Zend_Validate_File_Extension ( array ('txt', 'ref' ) ) )->setIsArray ( true );
		
		$optTipoImportacao = new Zend_Form_Element_Radio ( 'TIPO_IMPORTACAO' );
		$optTipoImportacao->setLabel ( 'Tipos de importação:' )->setRequired ( true )->setMultiOptions ( $tiposImportacao );
		
		/*
		// Botão submit
		$cmdSubmit = new Zend_Form_Element_Button ( 'Salvar' );
		$cmdSubmit->setLabel ( 'Enviar' )->setAttrib ( 'type', 'submit' )->setAttrib ( 'class', 'ceo_importar' );
		
		$this->addElements ( array ($txtArquivoTXT, $optTipoImportacao, $cmdSubmit ) );
		*/
		
		/*
		$cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );
		$cmdEnviar->setAttribs ( array ('class' => 'ceo_importar', 'value' => 'importar' ) );
		
		$cmdReImportar = new Zend_Form_Element_Submit ( 'Reimportar' );
		$cmdReImportar->setAttribs ( array ('class' => 'ceo_importar', 'value' => 'reimportar' ) );
		*/
		
		$cmdEnviar = new Zend_Form_Element_Button ( 'Enviar' );
		$cmdEnviar->setAttribs ( array ('class' => 'ceo_importar', 'value' => 'importar', 'type'=> 'submit' ) );
		
		/* 'Realiza nova importação a partir dos dados que apresentaram qualquer inconsistência nas tentativas anteriores.' */
		$cmdReImportar = new Zend_Form_Element_Button ( 'Reimportar' );
		$cmdReImportar->setAttribs ( array ('class' => 'ceo_importar', 'value' => 'reimportar', 'type'=> 'submit' ) );
		
		
		$this->addElements ( array ($txtArquivoTXT, $optTipoImportacao, $cmdEnviar, $cmdReImportar ) );
	}
}