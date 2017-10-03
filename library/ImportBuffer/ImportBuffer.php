<?php

/**
 * Classe principal do Import Buffer, que realiza toda a operação de quebrar os
 * arquivos de acordo com o modelo selecionado.
 * 
 * e-Admin
 * Core
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Disponibiliza de modo fácil a quebra de arquivos em TXT baseado em modelos
 *
 * @category ImportBuffer
 * @package ImportBuffer_ImportBuffer
 * @tutorial Segue descrição de utilização:
 *  
 *          Para utilização no módulo de Transparência CNJ, esse componente
 *          controla todo o core do quebra buffer. A implementação segue em 
 *          4 passos:
 * 
 *          --------------------------------------------------------------------
 *          1º passo:
 * 
 *          $import = new ImportBuffer_ImportBuffer();
 *          
 *          --------------------------------------------------------------------
 *          2º passo:
 *          
 *          Seleciona o padrão do modelo de importação (arquivos JSON criados
 *          dentro da pasta ImportBuffer/modelo). O padrão deve estar criado
 *          como constante dentro do arquivo ImportBuffer_Constants, sendo o
 *          nome do padrão o mesmo nome do modelo (exemplo: padrao1.json).
 *          Caso o JSON não seja encontrado retornará uma exceção chamdada
 *          ImportBuffer_Exception_Arquivo.
 *          
 *          Chamada:
 *          $import->selecionarArquivoModelo($padrao);
 * 
 *          --------------------------------------------------------------------
 *          3º passo:
 * 
 *          Atribuir o caminho completo do arquivo após realizado o upload do
 *          mesmo. É o arquivo .txt que será quebrado com base no modelo. Caso
 *          ocorra algum erro nesse método retornará uma exceção chamada
 *          ImportBuffer_Exception_Arquivo.
 *          
 *          Chamada:
 *          $import->selecionarArquivoBuffer($arquivoTmpNome);
 * 
 *          --------------------------------------------------------------------
 *          4º passo:
 * 
 *          Método que realiza a execução de validações e quebra do buffer,
 *          caso o arquivo não esteja no modelo proposto retornará uma exceção
 *          chamada ImportBuffer_Exception_Modelo.
 * 
 *          Chamada:
 *          $dadosBuffer = $import->importar();
 * 
 *          --------------------------------------------------------------------
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
class ImportBuffer_ImportBuffer extends ImportBuffer_Config {

    /**
     * Variáveis base de utilização do core.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private $nomeModelo;
    private $nomeArquivoModelo;
    private $nomeArquivoBuffer;
    private $manipularArquivos;
    private $verificarModelo;
    
    /**
     * Classe construtora
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function __construct() {
        $this->manipularArquivos = new ImportBuffer_ManipularArquivos();
        $this->verificarModelo = new ImportBuffer_VerificarModelo();
    }

    /**
     * Realiza importação e quebra do buffer.
     * 
     * @return array
     * @throws ImportBuffer_Exception_Modelo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function importar() {

        $this->carregarModelo();
        $this->carregarBuffer();

        $this->manipularArquivos->setJson($this->getJson());
        $this->verificarModelo->setJson($this->getJson());

        if (!$this->verificarModelo->verificarArquivo()) {
            throw new ImportBuffer_Exception_Modelo(
            Orcamento_Business_Importacao_Base::MSG025, 1);
        }

        $this->manipularArquivos->efetuarQuebraArquivo();
        $retorno = $this->manipularArquivos->retonarArray();

        return $retorno;
    }

    /**
     * Atribui o modelo padrão que será utilizado na importação.
     * O modelo deve ser no formato JSON localizado na pasta modelo.
     * 
     * @param string $nomeModelo Utilizar constante do ImportBuffer_Constants
     * @throws ImportBuffer_Exception_Arquivo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function selecionarArquivoModelo($nomeModelo) {

        $nomeArquivo = APPLICATION_PATH . "/../library/ImportBuffer/modelo/{$nomeModelo}.json";

        if (FALSE === file_exists($nomeArquivo)) {
            throw new ImportBuffer_Exception_Arquivo("Arquivo modelo não existe", 1);
        }

        $this->nomeArquivoModelo = $nomeArquivo;
        $this->nomeModelo = $nomeModelo;
    }

    /**
     * Atribui em uma variável global o conteúdo do arquivo modelo.
     * 
     * @throws ImportBuffer_Exception_Arquivo
     * @throws ImportBuffer_Exception_Json
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function carregarModelo() {

        $tempFile = file_get_contents($this->nomeArquivoModelo);

        if (FALSE === $tempFile) {
            throw new ImportBuffer_Exception_Arquivo(
                    "Não foi possível carregar o modelo", 2);
        }

        $this->json = json_decode($tempFile, TRUE);

        if (FALSE === $this->json) {
            throw new ImportBuffer_Exception_Json(
                    "Não foi possível efetuar parse no JSON", 2);
        }
    }

    /**
     * Verifica se o arquivo buffer existe, atribui o nome numa variável global.
     * 
     * @param type $nomeArquivo
     * @throws ImportBuffer_Exception_Arquivo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function selecionarArquivoBuffer($nomeArquivo) {

        if (FALSE === file_exists($nomeArquivo)) {
            throw new ImportBuffer_Exception_Arquivo(
                    "Arquivo buffer não existe", 4);
        }

        $this->nomeArquivoBuffer = $nomeArquivo;

    }

    /**
     * Atribui em variáveis globais o conteúdo do arquivo para manipulação.
     * 
     * @throws ImportBuffer_Exception_Arquivo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function carregarBuffer() {

        $retornoArquivo = file_get_contents($this->nomeArquivoBuffer);

        if (FALSE === $retornoArquivo) {
            throw new ImportBuffer_Exception_Arquivo(
                    "Falha ao carregar arquivo buffer", 3);
        }

        $this->manipularArquivos->setConteudoArquivo($retornoArquivo);
        $this->verificarModelo->setConteudoArquivo($retornoArquivo);
    }

    /**
     * Retorna o JSON já quebrado com base no modelo selecionado.
     * 
     * @return type
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function getJson() {

        return $this->json[$this->nomeModelo];
    }

}
