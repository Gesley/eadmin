<?php
class Orcamento_Form_Importarnc extends Zend_Form 
{
	public function init() 
	{
		$this->setName ( 'frmImportarnc' )
		//->setMethod ( 'post' )
		//->setAttrib ( 'id', 'frmImportarnc' )
		->setAttrib ( 'enctype', 'multipart/form-data' )
		//->setElementFilters ( array ('StripTags', 'StringTrim' ) )
		;

		$txtArquivoTXT = new Zend_Form_Element_File ( 'TEXTO' );
		$txtArquivoTXT->setRequired ( true )->setLabel ( 'Importar arquivo:' )
		->setAttribs ( array ('size' => '60', 'multiple' => 'false' ) )
		->addValidator ( new Zend_Validate_File_Extension ( array ('txt' ) ) );

		$cmdEnviar = new Zend_Form_Element_Button ( 'Importar' );
		$cmdEnviar->setAttribs ( array ('class' => 'ceo_importar', 'value' => 'importar', 'type'=> 'submit' ) );

		$this->addElements ( array ($txtArquivoTXT, $cmdEnviar ) );
	}
}
