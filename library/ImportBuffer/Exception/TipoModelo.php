<?php

/**
 * Classe exceção para erros do tipo tipo_modelo.
 * 
 * e-Admin
 * Core
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Classe exceção para erros do tipo tipo_modelo.
 *
 * @category ImportBuffer_Exception
 * @package ImportBuffer_Exception_TipoModelo
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class ImportBuffer_Exception_TipoModelo extends Zend_Exception {

    /**
     * Classe construtora
     * 
     * @param string $message
     * @param int $code
     * @param Exception $previous
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
