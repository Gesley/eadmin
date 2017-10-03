<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1'); 
require_once('Interface.php');
require_once('Abstract.php');
require_once('Cancelar.php');
require_once('Parametros/Interface.php');
require_once('Parametros/Cancelar.php');

$parametros = new Services_Red_Parametros_Cancelar();
$parametros->ip = '172.16.5.62';
$parametros->login = 'TR224PS';
$parametros->sistema = 'JURIS';
$parametros->nomeMaquina = 'DISAD010-TRF1';
$parametros->numeroDocumento = '43180100223';

$red = new Services_Red_Cancelar(true);

echo '<pre>';
var_dump($red->cancelar($parametros));


echo 'erro';

var_dump($red);
