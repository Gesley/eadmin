<?php

/**
 * Sentando o tempo maximo de sessão
 */
ini_set('session.gc_maxlifetime', 14400);
//echo ini_get('session.gc_maxlifetime');
/**
 * Sentando o time limit da aplicação em geral para 2 minutos
 */
set_time_limit(120);

//only for development
if (false !== strpos($_SERVER['REQUEST_URI'], "/hml/")) {
    define('APPLICATION_ENV', 'staging');
} elseif (false !== strpos($_SERVER['REQUEST_URI'], "/dsv/")) {
    define('APPLICATION_ENV', 'development');
} elseif (false !== strpos($_SERVER['REQUEST_URI'], "/prd/")) {
    define('APPLICATION_ENV', 'production');
} elseif (false !== strpos($_SERVER['REQUEST_URI'], "/tst/")) {
    define('APPLICATION_ENV', 'testing');
}

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    //get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini'
);

/**
 * registra a função shutdown para tratamento de erro fatal no sistema.
 *
 * @name register_shutdown_function
 * @author Victor Eduardo Barreto
 * @param string Função para tratar erro fatal
 * @date Mar 03, 2015
 * @version 1.0
 */
register_shutdown_function('shutdownFunction');

/**
 * Método responsável por tratar erro fatal no sistema.
 *
 * @name shutDownFunction
 * @author Victor Eduardo Barreto
 * @throws Exception
 * @date Mar 03, 2015
 * @version 1.0
 */
function shutDownFunction() {

    $error = error_get_last();

    # verifica se o erro é para o módulo orçamento.
    $erroOrcamento = strchr($_SERVER['REQUEST_URI'], "orcamento");

    if ($error['type'] == 1 && $erroOrcamento) {
        throw new Orcamento_Business_Exception(Trf1_Orcamento_Definicoes::MENSAGEM_ERRO_INESPERADO);
    }
}

$application->bootstrap()
    ->run();
