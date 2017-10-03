<?php
/**
 * Contém os métodos de inicialização para o funcionamento do e-Orçamento
 * 
 * e-Admin
 * e-Orçamento
 * Bootstrap
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém os métodos de inicialização para o funcionamento do sistema
 * e-Orçamento
 *
 * @category Orcamento
 * @package Orcamento
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Bootstrap extends Zend_Application_Module_Bootstrap
{

    /**
     * Inicialização do carregamento automático dos arquivos do sistema
     *
     * @return Zend_Loader_Autoloader_Resource
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function _initAutoload ()
    {
        $this->bootstrap ( 'frontController' );
        
        // Instancia a classe autoloader, já com alguns parâmetros
        $autoloader = new Zend_Loader_Autoloader_Resource ( 
        array ( 'namespace' => 'Orcamento', 
                'basePath' => APPLICATION_PATH . '/modules/orcamento' ) );
        
        // Inclui novos tipos de recursos do sistema
        $autoloader->addResourceType ( 'Business', 'business/', 'Business' );
        $autoloader->addResourceType ( 'Facade', 'facades/', 'Facade' );
        $autoloader->addResourceType ( 'Form', 'forms/', 'Form' );
        
        // Devolve o autoloader
        return $autoloader;
    }

    /**
     * Inicializa diversos plugins do sistema
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function _initPlugins ()
    {
        $this->bootstrap ( 'frontController' );
        $frontController = $this->getResource ( 'frontController' );
        
        // Ajuda específica do e-Orçamento
        $classe = 'Trf1_Orcamento_Plugin_Ajuda';
        if ( class_exists ( $classe ) ) {
            $frontController->registerPlugin ( new $classe () );
        }
        
        // Análise de permissão específica do e-Orçamento
        $classe = 'Trf1_Orcamento_Plugin_Permissao';
        if ( class_exists ( $classe ) ) {
            $frontController->registerPlugin ( new $classe () );
        }

        // Informativos
        $classe = 'Orcamento_Business_Plugin_Informativo';
        if ( class_exists ( $classe ) ) {
            $frontController->registerPlugin ( new $classe () );
        }     
//
//        $classe = 'Orcamento_Business_Plugin_Logdados';
//        if ( class_exists ( $classe ) ) {
//            $frontController->registerPlugin ( new $classe () );
//        }   


    }

    /**
     * Inicializa constante APPLICATION_PUB que define a pasta public do projeto
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function _initAppPublic ()
    {
        // Define o caracter barra
        $barra = trim ( ' \ ' );
        
        try {
            // Retorna variável da url
            $url = getenv ( 'REQUEST_URI' );
            
            // Retorna a posicao inicial do nome do sistema
            $posEAdmin = strpos ( strtolower ( $url ), 'e-admin' );
            
            // Retorna a posicao da próxima barra
            $posBarra = strpos ( $url, '/', $posEAdmin );
            
            // Define nova variável para a pasta public
            $path = substr ( $url, 0, $posBarra );
            
            // Inclui a pasta public se a mesma não tiver sido suprimida
            $public = strpos ( strtolower ( $url ), '/public' );
            
            if ( $public > 0 ) {
                $path .= '/public';
            }
        } catch ( Exception $e ) {
            $path = '';
        }
        
        // Define a constante para a pasta public (a ser utilizada no grid.ini)
        define ( 'APPLICATION_PUB', $path );
    }

}