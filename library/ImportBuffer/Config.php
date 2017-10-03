<?php

/**
 * Classe base com os campos em comum reutilizados em toda a estrutura.
 * 
 * e-Admin
 * Core
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Classe base com os campos em comum reutilizados em toda a estrutura.
 *
 * @category ImportBuffer
 * @package ImportBuffer_Config
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
class ImportBuffer_Config {

    /**
     * Variáveis globais utilizadas pelo core.
     * OBS.: não alterar.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    protected $arrayRetorno;
    protected $buffer;
    protected $idBuffer;
    protected $conteudoArquivo;
    protected $json;
    protected $debug = FALSE;

    /**
     * Método para facilitar o debug.
     * 
     * @param type $texto
     * @param type $array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    protected function debug($texto, $array = FALSE) {

        if ($this->debug == TRUE) {
            if ($array) {
                Zend_Debug::dump($texto, "array");
            } else {
                Zend_Debug::dump($texto, "texto");
            }
        }
    }

    /**
     * Seta o JSON.
     * 
     * @param array $json
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    protected function setJson($json) {
        $this->json = $json;
    }

    /**
     * Seta o conteúdo do arquivo.
     * 
     * @param type $conteudoArquivo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    protected function setConteudoArquivo($conteudoArquivo) {
        $this->conteudoArquivo = $conteudoArquivo;
    }

}
