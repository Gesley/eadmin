<?php
header('Content-type: text/plain; charset=utf-8');
error_reporting( E_ALL ^ E_NOTICE );
set_include_path("/home/tr19223ps/projetos/eadmin/dsv/library/ImportBuffer/");

function __autoload($class_name) {
	$auto = explode("_" , $class_name);
	$text = implode("/", $auto);
	
    require_once "{$text}.php";
}

$import = new ImportBuffer_ImportBuffer();

try {
	$import->selecionarArquivoModelo("padrao3");
	$import->selecionarArquivoBuffer($_GET['nome']);
	$retorno = $import->retonarArray();

	print_r($retorno);
} catch (Exception $e) {
	echo "-----> FALHA: {$e->getMessage()}";
}