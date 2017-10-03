<?php
/**
 * Contém funcionalidade básicas sobre cacheamento de dados
 * 
 * e-Admin
 * Core
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza classe genérica para manipulação de caches no sistema
 *
 * @category Trf1
 * @package Trf1_Cache
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 * @tutorial Para uso desta classe deve-se utilizar os seguintes códigos-fonte:
 *           ------------------------------------------------------------
 *           1) Para leitura de caches existentes, e sua respectiva criação,
 *           caso o mesmo ainda não tenha sido criado:
 *           
 *           // Criação do objeto cache
 *           $cache = new Trf1_Cache ();
 *           
 *           // Define o id conforme padrões
 *           $cacheId = $cache->retornaID_Listagem ( 'sosti', 'solicitacoes' );
 *           
 *           // Tenta ler os dados em cache
 *           $dados = $cache->lerCache ( $cacheId );
 *           
 *           // Verifica existência dos dados em cache
 *           if ( $dados === false ) {
 *               //Não existindo o cache, busca do banco
 *               // ...
 *               
 *               // Cria o cache com tempo determinado
 *               $cache->criarCache ( $dados, $cacheId, $tags, self::TEMPO_DIA_01 );
 *           }
 *           
 *           // Devolve os dados
 *           return $dados;
 *           ------------------------------------------------------------
 *           2) Para criação de cache de duração de 24 horas (ou 1 dia):
 *           
 *           // Criação do objeto cache
 *           $cache = new Trf1_Cache ();
 *           
 *           // Define o id conforme padrões
 *           $cacheId = $cache->retornaID_Listagem ( 'sosti', 'solicitacoes' );
 *           
 *           // Cria o cache
 *           $cache->criarCache ( $dados, $cacheId, $tags, Trf1_Cache::TEMPO_DIA_01 );
 *           ------------------------------------------------------------
 *           3) Para exclusão de cache, por exemplo, após atualização de dados:
 *           
 *           // Criação do objeto cache
 *           $cache = new Trf1_Cache ();
 *           
 *           // Define o id conforme padrões
 *           $cacheId = $cache->retornaID_Listagem ( 'sosti', 'solicitacoes' );
 *           
 *           // Apaga o cache
 *           $dados = $cache->excluirCache ( $cacheId );
 *           ------------------------------------------------------------
 *           4) Para exclusão de um ou mais caches por tag:
 *           
 *           // Criação do objeto cache
 *           $cache = new Trf1_Cache ();
 *           
 *           // Define tags
 *           $tags = array ( 'tag1', 'tag2', 'outro nome' );
 *           
 *           // Apaga os caches
 *           $dados = $cache->excluirCachesPorTag ( $tags );
 *           ------------------------------------------------------------
 */
class Trf1_Cache
{
    
    // Constantes de tempo de cache (em segundos)
    /**
     * Cache de 1 minuto (em segundos)
     *
     * @var number
     */
    const TEMPO_MINUTO_01 = 60;
    
    /**
     * Cache de 2 minutos (em segundos)
     *
     * @var number
     */
    const TEMPO_MINUTO_02 = 120;
    
    /**
     * Cache de 5 minutos (em segundos)
     *
     * @var number
     */
    const TEMPO_MINUTO_05 = 300;
    
    /**
     * Cache de 10 minutos (em segundos)
     *
     * @var number
     */
    const TEMPO_MINUTO_10 = 600;
    
    /**
     * Cache de 15 minutos (em segundos)
     *
     * @var number
     */
    const TEMPO_MINUTO_15 = 900;
    
    /**
     * Cache de 20 minutos (em segundos)
     *
     * @var number
     */
    const TEMPO_MINUTO_20 = 1200;
    
    /**
     * Cache de 30 minutos (em segundos)
     *
     * @var number
     */
    const TEMPO_MINUTO_30 = 1800;
    
    /**
     * Cache de 45 minutos (em segundos)
     *
     * @var number
     */
    const TEMPO_MINUTO_45 = 2700;
    
    /**
     * Cache de 1 hora ou 60 minutos (em segundos)
     *
     * @var number
     */
    const TEMPO_HORA_01 = 3600;
    
    /**
     * Cache de 2 horas (em segundos)
     *
     * @var number
     */
    const TEMPO_HORA_02 = 7200;
    
    /**
     * Cache de 4 horas (em segundos)
     *
     * @var number
     */
    const TEMPO_HORA_04 = 14400;
    
    /**
     * Cache de 8 horas (em segundos)
     *
     * @var number
     */
    const TEMPO_HORA_08 = 28800;
    
    /**
     * Cache de 12 horas (em segundos)
     *
     * @var number
     */
    const TEMPO_HORA_12 = 43200;
    
    /**
     * Cache de 1 dia ou 24 horas (em segundos)
     *
     * @var number
     */
    const TEMPO_DIA_01 = 86400;
    
    /**
     * Cache de 2 dias (em segundos)
     *
     * @var number
     */
    const TEMPO_DIA_02 = 172800;
    
    /**
     * Cache de 5 dias (em segundos)
     *
     * @var number
     */
    const TEMPO_DIA_05 = 432000;
    
    /**
     * Cache de 7 dias (em segundos)
     *
     * @var number
     */
    const TEMPO_DIA_07 = 604800;
    
    /**
     * Cache de 10 dias (em segundos)
     *
     * @var number
     */
    const TEMPO_DIA_10 = 864000;
    
    /**
     * Cache de 15 dias (em segundos)
     *
     * @var number
     */
    const TEMPO_DIA_15 = 1296000;
    
    /**
     * Cache de 30 dias (em segundos)
     *
     * @var number
     */
    const TEMPO_DIA_30 = 2592000;
    
    /**
     * Cache de 60 dias (em segundos)
     *
     * @var number
     */
    const TEMPO_DIA_60 = 5184000;
    
    /**
     *
     * @var string (constante)
     */
    const CACHE_NOME = 'eadmin';

    /**
     *
     * @var cache $_cache
     */
    protected $_cache = null;

    /**
     * Método construtor
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct ()
    {
        try {
            // Instancia objetos e variáveis...
            $zcfInstancia = Zend_Controller_Front::getInstance ();
            $bootstrap = $zcfInstancia->getParam ( 'bootstrap' );
            $gerenciador = $bootstrap->getResource ( 'cachemanager' );
            
            // Define o cache a ser utilizado nesta classe
            $this->_cache = $gerenciador->getCache ( self::CACHE_NOME );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Cria novo cache, conforme parâmetros
     *
     * @param string|array $dados
     *        Contém os dados a serem gravados
     * @param string $cacheId
     *        Informa o nome único do cache
     * @param string|array $tags
     *        Informa uma ou mais tags para este cache
     * @param int $cacheLifetime
     *        Tempo em segundos para expiração do cache
     * @return none
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function criarCache ( $dados, $cacheId, $cacheLifetime = 0, 
    $tags = array() )
    {
        try {
            // Caso não seja tempo infinito (=0)
            if ( $cacheLifetime != 0 ) {
                // Define o tempo de vida deste cache
                $this->_cache->setLifetime ( $cacheLifetime );
            }
            
            // Retorna instância de classe para manipulação de memória
            $mem = Orcamento_Business_Memoria::retornaInstancia ();
            
            // Expande a quantidade de memória disponível para essa requisição
            $mem->expandeMemoria ();
            
            // Grava o cache com os dados e id informados
            $this->_cache->save ( $dados, $cacheId, $tags );
            
            // Restaura a quantidade original de memória
            $mem->restauraMemoria ();
        } catch ( Exception $e ) {
            // Melhora a mensagem de erro, se aplicável
            $msgErro = $this->retornaMsgErro ( 'gravar', $cacheId );
            
            // Retorna o erro
            throw new Zend_Exception ( $msgErro . $e->getMessage () );
        }
    }

    /**
     * Ler cache por id
     *
     * @param string $cacheId        
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function lerCache ( $cacheId )
    {
        try {
            // Tenta ler o cache
            $dados = $this->_cache->load ( $cacheId );
        } catch ( Exception $e ) {
            // Melhora a mensagem de erro, se aplicável
            $msgErro = $this->retornaMsgErro ( 'ler', $cacheId );
            
            // Retorna o erro
            throw new Zend_Exception ( $msgErro . $e->getMessage () );
        }
        
        // Retorna os dados lidos do cache
        return $dados;
    }

    /**
     * Exclui cache por id
     *
     * @param string $cacheId        
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function excluirCache ( $cacheId )
    {
        try {
            // Exclui o cache a partir do id informado
            $this->_cache->remove ( $cacheId );
        } catch ( Exception $e ) {
            // Melhora a mensagem de erro, se aplicável
            $msgErro = $this->retornaMsgErro ( 'excluir', $cacheId );
            
            // Retorna o erro
            throw new Zend_Exception ( $msgErro . $e->getMessage () );
        }
    }

    /**
     * Exclui uma ou mais caches por tag
     *
     * @param array $tags        
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function excluirCachesPorTag ( $tags )
    {
        try {
            // Modo de exclusão
            $modoExclusao = Zend_Cache::CLEANING_MODE_MATCHING_TAG;
            
            // Exclui uma ou mais caches
            $this->_cache->clean ( $modoExclusao, $tags );
        } catch ( Exception $e ) {
            // Melhora a mensagem de erro, se aplicável
            $msgErro = $this->retornaMsgErro ( 'excluir', 'por tags' );
            
            // Retorna o erro
            throw new Zend_Exception ( $msgErro . $e->getMessage () );
        }
    }

    /**
     * Limpa todos os caches expirados (comparados pelo lifetime)
     *
     * @return none
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function limparCachesExpirados ()
    {
        try {
            // Limpa caches expirados
            $this->_cache->clean ( 'old' );
        } catch ( Exception $e ) {
            // Melhora a mensagem de erro, se aplicável
            $msgErro = $this->retornaMsgErro ( 'limpar', 'expirado' );
            
            // Retorna o erro
            throw new Zend_Exception ( $msgErro . $e->getMessage () );
        }
    }

    /**
     * Retorna o id de caches para listagens (ou grids) Tipicamente, array de
     * dados com N campos Função apenas utilizada para padronização de nomes dos
     * caches
     *
     * @param string $modulo        
     * @param string $nomeListagem        
     * @return string
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaID_Listagem ( $modulo, $nomeListagem )
    {
        try {
            // Padroniza o nome do id da listagem
            $id = strtolower ( $modulo ) . '_';
            $id .= strtolower ( $nomeListagem ) . '_listagem';
            
            // Devolve o id
            return $id;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Retorna o id de caches para combos Tipicamente, array de 2 campos, sendo
     * código e descrição (fetchPairs) Função apenas utilizada para padronização
     * de nomes dos caches
     *
     * @param string $modulo        
     * @param string $nomeCombo        
     * @return string
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaID_Combo ( $modulo, $nomeCombo )
    {
        try {
            // Padroniza o nome do id do combo
            $id = strtolower ( $modulo ) . '_';
            $id .= strtolower ( $nomeCombo ) . '_itens_combo';
            
            // Devolve o id
            return $id;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Retorna o id de caches para permissão Tipicamente, uma string contendo a
     * matrícula do usuário Função apenas utilizada para padronização de nomes
     * dos caches
     *
     * @param string $matricula        
     * @return string
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaID_Permissao ( $matricula )
    {
        try {
            // Devolve o id
            return 'eadmin_permissao_' . strtolower ( $matricula );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Retorna mensagem de erro, conforme ação e id
     *
     * @param string $acao        
     * @param string $cacheId        
     * @return string
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaMsgErro ( $acao, $cacheId )
    {
        try {
            // Devolve a mensagem de erro
            return "Não foi possível $acao cache $cacheId. <br />";
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

}