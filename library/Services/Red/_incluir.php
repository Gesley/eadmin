<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1'); 
require_once('Interface.php');
require_once('Abstract.php');
require_once('Incluir.php');
require_once('Parametros/Interface.php');
require_once('Parametros/Incluir.php');
require_once('Metadados/Interface.php');
require_once('Metadados/Incluir.php');


$parametros = new Services_Red_Parametros_Incluir();
$parametros->login = 'TR224PS';
$parametros->ip = '172.16.5.62';
$parametros->sistema = 'JURIS';
$parametros->nomeMaquina = 'DISAD010-TRF1';

$metadados = new Services_Red_Metadados_Incluir();
$metadados->dataHoraProducaoConteudo = "30/01/2009 11:15:00";
$metadados->descricaoTituloDocumento = "Primeiro teste inclusao1";
$metadados->numeroTipoSigilo = Services_Red::NUMERO_SIGILO_RESTRITO_AS_PARTES;
$metadados->numeroTipoDocumento = "01";
$metadados->nomeSistemaIntrodutor = "JURIS";
$metadados->ipMaquinaResponsavelIntervencao = "172.16.5.62";
$metadados->secaoOrigemDocumento = "0100";
$metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
$metadados->espacoDocumento = "I";
$metadados->nomeMaquinaResponsavelIntervensao = "DIESP07";
$metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
$metadados->numeroDocumento = "";
$metadados->dataHoraProtocolo = "30/01/2008 11:15:00";
$metadados->pastaProcessoNumero = "12345";
$metadados->secaoDestinoIdSecao = "0100";


$red = new Services_Red_Incluir(true);
$red->debug = true;
$red->temp = realpath(dirname(__FILE__)) . '/../../../temp';

echo '<pre>Retorno RED<br>';
$file = realpath(dirname(__FILE__)) . '/pdf/h.pdf';
//var_dump($red->getAutorizacao());
var_dump($red->incluir($parametros,$metadados,$file));

/*
echo 'erro';

var_dump($red);
*/
