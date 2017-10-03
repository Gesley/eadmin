<?php 
$doc = new DOMDocument('1.0');
// we want a nice output
$doc->formatOutput = true;


$doc = new DOMDocument();
		$root = $doc->createElement('root');
		$doc->appendChild($root);

		$solicitanteArquivo = $doc->createElement('solicitanteArquivo');
		$root->appendChild($solicitanteArquivo); 
		
		$numeroDocumento = $doc->createAttribute('numeroDocumento');
		$solicitanteArquivo->appendChild($numeroDocumento);
		$numeroDocumentoTexto = $doc->createTextNode('numeroDocumento texto');
    	$numeroDocumento->appendChild($numeroDocumentoTexto); 
    	
		$login = $doc->createAttribute('login');
		$solicitanteArquivo->appendChild($login);
		$loginTexto = $doc->createTextNode('login texto');
    	$login->appendChild($loginTexto);

		$ip = $doc->createAttribute('ip');
		$solicitanteArquivo->appendChild($ip);
		$ipTexto = $doc->createTextNode('ip texto');
    	$ip->appendChild($ipTexto);

		$sistema = $doc->createAttribute('sistema');
		$solicitanteArquivo->appendChild($sistema);
		$sistemaTexto = $doc->createTextNode('sistema texto');
    	$sistema->appendChild($sistemaTexto);

		$maquina = $doc->createAttribute('maquina');
		$solicitanteArquivo->appendChild($maquina);
		$maquinaTexto = $doc->createTextNode('sistema texto');
    	$maquina->appendChild($maquinaTexto);      





$root = $doc->createElement('book');
$root = $doc->appendChild($root);

$title = $doc->createElement('title');
$title = $root->appendChild($title);

$text = $doc->createTextNode('This is the title');
$text = $title->appendChild($text);

echo "Saving all the document:\n";
echo $doc->saveXML() . "\n";

echo "Saving only the title part:\n";
echo $doc->saveXML($title);


