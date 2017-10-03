<?php
/**
 * Contém informações sobre uso da memória nas requisições do servidor
 * 
 * e-Admin
 * e-Orçamento
 * Core
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Classe singleton genérica para atribuição de valores e outros método sobre
 * manipulação e consumo de memória nas requisições do servidor.
 *
 * @category Orcamento
 * @package Orcamento_Business_Memoria
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */
class Orcamento_Business_Memoria
{

    /**
     * Instância desta classe
     *
     * @var objeto
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected static $_instancia = null;
    
    /**
     * Nome da diretiva do servidor que armazena a quantidade de memória alocada
     * para cada requisição no servidor
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const MEM = 'memory_limit';
    
    /**
     * Nome da diretiva do servidor que armazena o tamanho máximo para cada
     * requisição tipo post no servidor
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    const PMS = 'post_max_size';

    /**
     * Método construtor implementa o padrão Singleton e não permite que essa
     * classe seja instanciada através do "new".
     *
     * @return void
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function __construct ()
    {}

    /**
     * Método que implementa o padrão Singleton e não permite que essa classe
     * seja clonada através do "clone".
     *
     * @return void
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function __clone ()
    {}

    /**
     * Retorna instância desta classe
     *
     * @return objeto
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public static function retornaInstancia ()
    {
        if ( null === self::$_instancia ) {
            self::$_instancia = new self ();
        }
        
        return self::$_instancia;
    }

    /**
     * Retorna a quantidade de memória alocado neste momento para cada
     * requisição, conforme definido no PHP.ini do servidor
     *
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaMemoria ()
    {
        // Define a variável com o valor da memória atualmente atribuído
        $memoria = ini_get ( self::MEM );
        
        // Devolve quantidade de memória alocada
        return $memoria;
    }

    /**
     * Retorna o tamanho máximo de dados passados por cada requisicao tipo post
     *
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaTamMaxPost ()
    {
        // Define a variável com o tamanho máximo por post
        $pms = ini_get ( self::PMS );
        
        // Devolve tamanho máximo por post
        return $pms;
    }

    /**
     * Define novo valor para quantidade de memória alocada para a próxima
     * requisição
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function expandeMemoria ()
    {
        // Define novo valor de memória alocada
        ini_set ( self::MEM, '1024M' );
        // Obsoleto! // ini_set ( self::MEM, '768M' );
        // Obsoleto! // ini_set ( self::MEM, '512M' );
        // Obsoleto! // ini_set ( self::MEM, '256M );
        // Obsoleto! // ini_set ( self::MEM, '128M' ); // Quantidade padrão
    }

    /**
     * Restaura a quantidade original de memória alocada para cada requisição
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function restauraMemoria ()
    {
        // Restaura o valor original de memória alocada
        ini_restore ( self::MEM );
        
        // Restaura o valor original de tamanho máximo por post
        ini_restore ( self::PMS );
    }

}