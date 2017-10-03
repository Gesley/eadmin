<?php 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
//set_time_limit(0); 
//ini_set('memory_limit','64M');
ini_set('default_charset','UTF-8');

setlocale(LC_CTYPE, 'pt_BR.UTF-8');
date_default_timezone_set('America/Sao_Paulo');
error_reporting(E_ALL);
ini_set('display_errors', '1'); 

/**
 * Migração para a Blade
 * Acertar o inlcude path abaixo.
 */
 
require '../../../config.php';

require_once('Interface.php');
require_once('Abstract.php');
require_once('Incluir.php');
require_once('Parametros/Interface.php');
require_once('Parametros/Incluir.php');
require_once('Metadados/Interface.php');
require_once('Metadados/Incluir.php');
require_once('../Red.php');


$parametros = new Services_Red_Minuta_Parametros_Incluir();
$parametros->login = 'TR224PS';
$parametros->ip = '172.16.5.62';

$metadados = new Services_Red_Minuta_Metadados_Incluir();
$metadados->dataHoraProducaoConteudo = "30/01/2009 11:15:00";
$metadados->descricaoTituloDocumento = "Primeiro teste inclusao1";
$metadados->numeroTipoSigilo = Services_Red::NUMERO_SIGILO_PUBLICO;
$metadados->nomeSistemaIntrodutor = "JURIS";
$metadados->ipMaquinaResponsavelIntervencao = "172.16.5.62";
$metadados->secaoOrigemDocumento = "0100";
$metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
$metadados->espacoDocumento = "I";
$metadados->nomeMaquinaResponsavelIntervensao = "DIESP07";
$metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_MINUTA;
$metadados->numeroDocumento = "";
$metadados->secaoDestinoIdSecao = "0100";
$metadados->numeroPasta = "";

try {
    $red = new Services_Red_Minuta_Incluir(true);
    $red->debug = true;
    $red->temp = realpath(dirname(__FILE__)) . '/../../../temp';

    echo '<pre>Retorno RED<br>';
    $file = realpath(dirname(__FILE__)) . '\pdf\g.pdf';
    //var_dump($red->getAutorizacao());
    var_dump($red->incluir($parametros,$metadados,$file));
    
} catch (Exception $exc) {
    echo '<pre>';
    var_dump($exc);
}


/*
echo 'erro';

var_dump($red);
*/
