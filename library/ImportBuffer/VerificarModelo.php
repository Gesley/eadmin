<?php

/**
 * Classe que efetua validação do buffer vs. o modelo selecionado.
 * 
 * e-Admin
 * Core
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Classe para validação do tamanho do buffer vs. o tamanho dos campos.
 *
 * @category ImportBuffer
 * @package ImportBuffer_VerificarModelo
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
class ImportBuffer_VerificarModelo extends ImportBuffer_Config {

    /**
     * Guarda o tamanho do buffer baseado no modelo.
     * 
     * @var int
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private $tamanhoStringModelo;

    /**
     * Verifica o tamanho do arquivo e retorna o tamanho do buffer.
     * 
     * @return int
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function verificarArquivo() {

        $this->tamanhoStringModelo = $this->calcularTamanhoStringModelo();
        $linhas = explode("\r\n", $this->conteudoArquivo);
        $retornoTamanho = false;
        
        foreach ($linhas as $id => $buffer) {
            $this->buffer = $buffer;
            $retornoTamanho = $this->verificarTamanhoBufferModelo();

            if (!$retornoTamanho) {
                break;
            }
        }

        return $retornoTamanho;
    }

    /**
     * Verifica o tamanho do buffer e do modelo e retorna se estão iguais
     * 
     * @return boolean
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function verificarTamanhoBufferModelo() {

        $tamStrBuffer = strlen($this->buffer);
        $tamModel = $this->tamanhoStringModelo;

        // ignora linhas vazias e retorna true
        return ($tamStrBuffer == $tamModel || $tamStrBuffer == 0) ? true : false;
    }

    /**
     * Calcula o tamanho do buffer à partir do modelo JSON.
     * 
     * @return int
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function calcularTamanhoStringModelo() {

        $total = 0;

        foreach ($this->json as $objetoJson) {
            if ($objetoJson['tipo'] == ImportBuffer_Constants::LOOP) {
                foreach ($objetoJson['loop'] as $objLoop) {
                    $tamLoop = $objLoop['final'] - ($objLoop['inicial'] - 1);
                    $totalLoop += $tamLoop * $objLoop['quantidade'];
                }
            } else {
                $tamStr = $objetoJson['final'] - ($objetoJson['inicial'] - 1);
                $total += $tamStr;
            }
        }
        
        $totalString = $total + $totalLoop;

        return $totalString;
    }

}