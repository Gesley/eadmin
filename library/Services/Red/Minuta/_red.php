<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1'); 
require_once('Interface.php');
require_once('Abstract.php');
require_once('Recuperar.php');
require_once('Parametros/Interface.php');
require_once('Parametros/Recuperar.php');

$parametros = new Services_Red_Minuta_Parametros_Recuperar();
$parametros->ip = '172.16.5.62';
$parametros->login = 'TR224PS';
$parametros->sistema = 'JURIS';
$parametros->nomeMaquina = 'DISAD010-TRF1';
$parametros->numeroDocumento = '32163400251';
//$parametros->numeroDocumento = '43180100223';
//$parametros->numeroDocumento	    = '153750100238';
$red = new Services_Red_Minuta_Recuperar();
$red->debug = true;
$red->recuperar($parametros);