<?php

/**
 * Classe que efetua toda a manipulação dos arquivos buffer.
 * 
 * e-Admin
 * Core
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Classe que realiza a manipulação, quebra, verificação dos campos do buffer.
 *
 * @category ImportBuffer
 * @package ImportBuffer_ManipularArquivos
 * @tutorial Essa classe é utilizada pelo core do ImportBuffer.
 *          Toda alteração impactará a estruta por completo, ter atenção ao 
 *          adicionar uma validação nova, preferível não modificar a estrutura
 *          atual, mas sim criar a sua própria estrutura.
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
class ImportBuffer_ManipularArquivos extends ImportBuffer_Config {

    /**
     * Retorna o array quebrado.
     * 
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retonarArray() {
        return $this->arrayRetorno;
    }

    /**
     * Cria o buffer, explode as linhas e inicia a chamada da quebra do buffer.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function efetuarQuebraArquivo() {

        $linhas = explode("\r\n", $this->conteudoArquivo);

        foreach ($linhas as $id => $buffer) {
            
            // ignora linhas vazias
            if (strlen($buffer) == 0) {
                continue;
            }
            
            $this->buffer = $buffer;
            $this->idBuffer = $id;

            $this->efetuarQuebraBuffer();
        }
    }

    /**
     * Realiza todas as verificações necessárias para qeubra de buffer, baseado
     * na tipagem configurada no arquivo de modelo e suas posições.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function efetuarQuebraBuffer() {

        $arrayTipoNormal[] = ImportBuffer_Constants::DECIMAL;
        $arrayTipoNormal[] = ImportBuffer_Constants::INT;
        $arrayTipoNormal[] = ImportBuffer_Constants::STRING;

        foreach ($this->json as $_id => $detCampo) {

            switch ($detCampo['tipo']) {
                case ImportBuffer_Constants::LOOP:
                    $this->rotearTipoLoop($_id, $detCampo);
                    break;

                default:
                    $this->efetuarQuebraCampo($_id, $detCampo);
            }
        }
    }

    /**
     * Roteia para o tipo de loop, sequencial ou objeto.
     * 
     * @param type $_id
     * @param type $detCampo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function rotearTipoLoop($_id, $detCampo) {

        switch ($detCampo['repeticao']) {
            case ImportBuffer_Constants::SEQUENCIAL:
                $this->efetuarQuebraLoopSequencial($detCampo);
                break;

            case ImportBuffer_Constants::OBJETO:
                $this->efetuarQuebraLoopObjeto($_id, $detCampo);
                break;
        }
    }

    /**
     * Formata os campos já quebrados para retornar ao array de retorno.
     * 
     * @param string $campo
     * @param string $tipo
     * @param string $tipoSinal
     * @return string
     * @throws ImportBuffer_Exception_TipoModelo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function manipularTipoCampo($campo, $tipo, $tipoSinal = null) {

        $retornoCampo = null;
        $sinal = ($tipoSinal == ImportBuffer_Constants::POSITIVO
                || $tipoSinal == null) ? "+" : "-";
        
        

        switch ($tipo) {
            case ImportBuffer_Constants::DECIMAL:
                // caso o valor seja crédito porém provindo com sinal negativo
                // transforma em negativo
                if (strstr($campo, "-") !== FALSE) {
                    $sinal = "-";
                }

                $decimal = ltrim(substr($campo, 0, 16), "0");
                $posVirgula = substr($campo, 16, 2);
                $retornoCampoA = "{$sinal}{$decimal}{$posVirgula}";
                $retornoCampo = doubleval($retornoCampoA);

                if ($retornoCampo === FALSE) {
                    throw newImportBuffer_Exception_TipoModelo(
                            "Falha ao manipular tipo DECIMAL");
                }

                break;

            case ImportBuffer_Constants::INT:
                $retornoCampo = intval($campo);
                break;

            case ImportBuffer_Constants::STRING:
                $retornoCampo = $campo;
                break;
        }

        return $retornoCampo;
    }

    /**
     * Efetua a quebra onde existe um loop sequencial de um mesmo campo
     * 
     * @param array $detCampo
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function efetuarQuebraLoopSequencial(array $detCampo) {
        $loopUlPosTam = 0;

        // valores dentro do loop
        $valorLoop = $detCampo['loop'];
        $detIni = $detCampo['inicial'];

        // verifica o tamanho do loop dentro do buffer para efetuar quebra
        $tamanhoBuffer = strlen($this->buffer);
        $tamFinBuffer = $tamanhoBuffer - $detIni;
        $bufferCortado = substr($this->buffer, $detIni, $tamFinBuffer);

        // passa item a item dentro do loop
        foreach ($valorLoop as $detLoop) {
            // quantidade de repetições do loop
            $repeticoesLoop = $detLoop['quantidade'];

            // verifica quantas repetições e quebra o buffer
            for ($loopI = 0; $loopI < $repeticoesLoop; $loopI++) {
                // pega trecho do buffer para efetuar manipulação do campo
                $loopTam = ($detLoop['final'] + 1) - $detLoop['inicial'];
                $loopCortado = substr($bufferCortado, $loopUlPosTam, $loopTam);
                $campoManip = ImportBuffer_ManipularArquivos::manipularTipoCampo(
                             $loopCortado, $detLoop['tipo'], $detLoop['sinal']);

                // monta vetor de retorno para adicionar na matriz
                $arraySet = array(
                    'nome_campo' => $detLoop['nome'],
                    'valor' => $campoManip,
                    'valor_sem_sinal' => abs($campoManip),
                    'valor_sem_format' => $loopCortado,
                );

                // seta valores para montar vetor
                $nomeId = $detCampo['nome_base'];
                $tipo = $detLoop['nome_base'];
                $idBuff = $this->idBuffer;

                // adiciona o valor quebrado dentro do array
                $this->arrayRetorno[$idBuff][$nomeId][$tipo][$loopI] = $arraySet;

                // adiciona qual é a última posição para efetuar quebra do buffer
                $loopUlPosTam += $loopTam;
            }
        }
        
    }

    /**
     * Efetua quebra do campo e realiza sua formatação.
     * 
     * @param string $id
     * @param array $detCampo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function efetuarQuebraCampo($id, array $detCampo) {

        $inicial = $detCampo['inicial'] - 1;
        $tamanho = ($detCampo['final'] + 1) - $detCampo['inicial'];

        $campoCortado = substr($this->buffer, $inicial, $tamanho);

        $campoManip = ImportBuffer_ManipularArquivos::manipularTipoCampo(
                        $campoCortado, $detCampo['tipo']);

        $arraySet = array(
            'nome_base' => $detCampo['nome_base'],
            'nome_campo' => $detCampo['nome'],
            'valor' => $campoManip,
            'valor_sem_format' => $campoCortado,
            'importar' => $detCampo['importar']
        );

        $idBuff = $this->idBuffer;

        $this->arrayRetorno[$idBuff][$detCampo['nome_base']] = $arraySet;
    }

    /**
     * Efetua a quebra de loop baseado em objeto definido.
     * 
     * @todo Implementar esse tipo de quebra
     * @param string $id
     * @param array $detCampo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function efetuarQuebraLoopObjeto($id, array $detCampo) {
        // @todo implementar para repetição do tipo objeto.
    }

}
