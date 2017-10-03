<?php 
set_time_limit(0); 
error_reporting(E_ALL);
ini_set('display_errors', '1'); 
require_once('Interface.php');
require_once('Abstract.php');
require_once('Recuperar.php');
require_once('Parametros/Interface.php');
require_once('Parametros/Recuperar.php');

$parametros = new Services_Red_Parametros_Recuperar();
$parametros->ip = '172.16.5.62';
$parametros->login = 'TR224PS';
$parametros->sistema = 'JURIS';
$parametros->nomeMaquina = 'DISAD010-TRF1';
$parametros->numeroDocumento = '32163400251';
//$parametros->numeroDocumento = '43180100223';
//$parametros->numeroDocumento	    = '153750100238';
$red = new Services_Red_Recuperar(true);
$red->debug = true;
$retorno = $red->recuperar($parametros);


$arquivo = $red->openHttpsUrl($retorno['url']);
		
		
		//$tmpfname = tempnam('/d01/processos/temp', "RED");
		$tmpfname = '/d01/processos/Teste/wilton/consultaProcessual/temp/RED.pdf';
		$tmpfname = substr($tmpfname,0,strpos($tmpfname,'.')) . '.pdf';
		
		$handle = fopen($tmpfname, "w");
		
		if (!$handle) {
			throw new Exception('Não foi possível gerar o arquivo em nossos servidores');			
		}
		
		fwrite($handle, $arquivo);
		fclose($handle);