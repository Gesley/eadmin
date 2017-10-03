
<?php

/**
 * Contém controller da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Controller
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades sobre importação de notas de empenho.
 *
 * @category Orcamento
 * @package Orcamento_ImportarefController
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ImportarefController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'Listagem de Execução' );

        // Define a Negocio de Importacao
        $this->business = new Orcamento_Business_Negocio_Importaref ();

        // Define a Busines de nc
        $this->_negocioef = new Orcamento_Business_Negocio_Ef ();

        // Define a classe facade
        $this->defineFacade ( 'Importaref' );

        // Conforme oriental na tag @tutorial
        parent::init ();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de Execução';

        // Formulario na view index
        $frmnc = new Orcamento_Form_Importaref ();

        // Exibe o formulario na view index
        $this->view->frmnc = $frmnc;

        $valor = new Trf1_Orcamento_Valor ();

        // Importa os arquivos
        if ($this->_request->isPost()) {
            // Arquivo enviado
            $f = $_FILES;

            // Nome do arquivo txt
            $txtName = $f["TEXTO"]["name"];

            // Abre o arquivo
            $arquivo = file($f["TEXTO"]["tmp_name"], FILE_IGNORE_NEW_LINES);

            // Verifica se é txt
            $this->verificaTxt($f);

            // Verifica se é um arquivo NE
            $this->verificaNE($arquivo);

            // Array com os dados da nota - array
            $ef = array();

            foreach ($arquivo as $file) {

                $ef[] = array(
                    'EXEC_CD_NOTA_EMPENHO' => substr($file, 16, 12).substr($file, 0, 6),
                    'EXEC_CD_UG' => substr($file, 0, 6),
                    'CONTA' => (int)substr($file, 28, 2),
                    //'NOEM_CD_NOTA_EMPENHO' => substr($file, 16, 12).substr($file, 0, 6),
                    // campo              credito                                   debito
                    'EXEC_VL_JANEIRO'   => $this->trataValor(substr($file, 309, 18) - substr($file,  93, 18)),
                    'EXEC_VL_FEVEREIRO' => $this->trataValor(substr($file, 327, 18) - substr($file, 111, 18)),
                    'EXEC_VL_MARCO'     => $this->trataValor(substr($file, 345, 18) - substr($file, 129, 18)),
                    'EXEC_VL_ABRIL'     => $this->trataValor(substr($file, 363, 18) - substr($file, 147, 18)),
                    'EXEC_VL_MAIO'      => $this->trataValor(substr($file, 381, 18) - substr($file, 165, 18)),
                    'EXEC_VL_JUNHO'     => $this->trataValor(substr($file, 399, 18) - substr($file, 183, 18)),
                    'EXEC_VL_JULHO'     => $this->trataValor(substr($file, 417, 18) - substr($file, 201, 18)),
                    'EXEC_VL_AGOSTO'    => $this->trataValor(substr($file, 435, 18) - substr($file, 219, 18)),
                    'EXEC_VL_SETEMBRO'  => $this->trataValor(substr($file, 453, 18) - substr($file, 237, 18)),
                    'EXEC_VL_OUTUBRO'   => $this->trataValor(substr($file, 471, 18) - substr($file, 255, 18)),
                    'EXEC_VL_NOVEMBRO'  => $this->trataValor(substr($file, 489, 18) - substr($file, 273, 18)),
                    'EXEC_VL_DEZEMBRO'  => $this->trataValor(substr($file, 507, 18) - substr($file, 291, 18)),

                    // CONFIGURACOES DE CREDITO
                    'CREDITO_EXEC_VL_JAN' => substr($file, 309, 18),
                    'CREDITO_EXEC_VL_FEV' => substr($file, 327, 18),
                    'CREDITO_EXEC_VL_MAR' => substr($file, 345, 18),
                    'CREDITO_EXEC_VL_ABR' => substr($file, 363, 18),
                    'CREDITO_EXEC_VL_MAI' => substr($file, 381, 18),
                    'CREDITO_EXEC_VL_JUN' => substr($file, 399, 18),
                    'CREDITO_EXEC_VL_JUL' => substr($file, 417, 18),
                    'CREDITO_EXEC_VL_AGO' => substr($file, 435, 18),
                    'CREDITO_EXEC_VL_SET' => substr($file, 453, 18),
                    'CREDITO_EXEC_VL_OUT' => substr($file, 471, 18),
                    'CREDITO_EXEC_VL_NOV' => substr($file, 489, 18),
                    'CREDITO_EXEC_VL_DEZ' => substr($file, 507, 18),

                    // CONFIGURACOES DE DEBITO
                    'DEBITO_EXEC_VL_JAN' => substr($file,  93, 18),
                    'DEBITO_EXEC_VL_FEV' => substr($file, 111, 18),
                    'DEBITO_EXEC_VL_MAR' => substr($file, 129, 18),
                    'DEBITO_EXEC_VL_ABR' => substr($file, 147, 18),
                    'DEBITO_EXEC_VL_MAI' => substr($file, 165, 18),
                    'DEBITO_EXEC_VL_JUN' => substr($file, 183, 18),
                    'DEBITO_EXEC_VL_JUL' => substr($file, 201, 18),
                    'DEBITO_EXEC_VL_AGO' => substr($file, 219, 18),
                    'DEBITO_EXEC_VL_SET' => substr($file, 237, 18),
                    'DEBITO_EXEC_VL_OUT' => substr($file, 255, 18),
                    'DEBITO_EXEC_VL_NOV' => substr($file, 273, 18),
                    'DEBITO_EXEC_VL_DEZ' => substr($file, 291, 18),

                );

            } // endforeach
              //
            $resultado = $this->_negocioef->incluirImportacao($ef, $txtName);

            if( count( $resultado['INSERIDOS'] ) > 0 ){
                $this->_helper->flashMessenger( array( message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO . $resultado['INSERIDOS'] . " nota(s) adicionadas", 'status' => 'success'));
            }else{
                $this->_helper->flashMessenger(array(message => "Não foi possivel adicionar a(s) execução(s) ou não existe nota(s) de empenho para essa(s) execução(s).", 'status' => 'error'));
            }

            if(count($resultado['SEM_EMPENHO']) > 0){
                $this->_helper->flashMessenger(array(message => "Não foram adicionadas ".count($resultado['SEM_EMPENHO'])." execuções por falta de notas de empenho", 'status' => 'info'));
            }

            // Limpa o cache
            $this->business->excluiCaches();

            // Msg Sucesso e redirect

            $this->_redirect('orcamento/importaref/index');

        }

        // Importarnc listagem de registros
        $this->listar ( $funcionalidade );
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Importação';

        // Exibição de um registro
        $this->detalhe ();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Informativo';

        // Exclusão do registro
        $this->excluir ();
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function restaurarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar Informativo';

        // Restauração de registro logicamente excluído
        $this->restaurar ();
    }

    // Verifica se é um txt
    public function verificaTxt($f)
    {

        $file = pathinfo($f["TEXTO"]["name"]);

        if( strtolower( $file["extension"] ) != 'txt') {
            $this->erroOperacao("O arquivo não é txt!");
        }
    }

    // Verfica se é um NE
    public function verificaNE($f)
    {
        $ef = substr($f[0], 16, 12);
        $pos = strripos($ef, "NE");

        if($pos == FALSE){
            $this->erroOperacao("O arquivo não é uma Execução valida.");
            $this->_redirect('orcamento/importaref/index');
        }

    }

    public function trataValor( $valor )
    {
        return $valor;
    }

    // flashmessgender do erro
    private function erroOperacao ( $mensagemErro )
    {
        $erro = $mensagemErro;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest();
        // $log->gravaLog($requisicao, $erro, zend_log::ERR);

        $this->_helper->flashMessenger ( array ( message => $erro, 'status' => 'error' ) );
    }

}