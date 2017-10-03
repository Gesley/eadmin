<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1'); 
require_once('../Abstract.php');
require_once('Listar.php');

$red = Services_Red_Minuta_Listar::getInstance(true);

echo '<pre>Retorno RED<br>';
//var_dump($red->getAutorizacao());
//var_dump($red->consultarDadosNumIdProc('3151233007'));
var_dump($red->consultarDadosNumIdProc('3151233007'));                            
var_dump($red->consultarDadosNumIdProc('3480602007'));                            
var_dump($red->consultarDadosNumIdProc('3555868007'));                            
var_dump($red->consultarDadosNumIdProc('3558108007'));                            
var_dump($red->consultarDadosNumIdProc('3559903007'));                            
var_dump($red->consultarDadosNumIdProc('3622604007'));                            
var_dump($red->consultarDadosNumIdProc('3624492007'));                            

/*
echo 'erro';

var_dump($red);
*/
