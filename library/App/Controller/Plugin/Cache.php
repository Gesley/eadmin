<?php
/**
 * Classe para criar os caches 
 */
class App_Controller_Plugin_Cache extends Zend_Controller_Plugin_Abstract
{
    private $_cache = '';
    
    public function __construct($lifeTime = 999999999999)
    {
        /**
         * Gera o cache
         */      
        $frontendOptions = array(
            'lifetime' => $lifeTime, // cache lifetime
            'automatic_serialization' => true
        );
        $cache_dir = APPLICATION_PATH . '/../temp';
        $backendOptions = array(
            'cache_dir' => $cache_dir 
        );
        // getting a Zend_Cache_Core object
        $this->_cache = Zend_Cache::factory('Core',
                                     'File',
                                     $frontendOptions,
                                     $backendOptions);
    }
    
    public function save($arrayCache, $idCache)
    {
        return $this->_cache->save($arrayCache, $idCache);
    }
    
    public function load($idCache)
    {
        return $this->_cache->load($idCache);
    }
    
    public function remove($idCache)
    {
        return $this->_cache->remove($idCache);
    }
}