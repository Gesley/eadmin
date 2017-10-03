<?php

/**
 * Contém controller da aplicação.
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Disponibiliza controller de importação.
 *
 * @category Orcamento
 * @package Orcamento_ImportarliquidadoController
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ImportarliquidadoController extends Orcamento_Business_Tela_Crud {

    private $_titulo = "Liquidado";
    private $_padrao = ImportBuffer_Constants::PADRAO2;
    private $_tipo = Orcamento_Business_Importacao_Base::ARQUIVO_LIQUIDADO;

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser("Importar {$this->_titulo}");

        // Define a classe facade
        $this->defineFacade('Importar' . strtolower($this->_titulo));

        // Negocio
        $this->_negocioLiquidado = new Orcamento_Business_Negocio_Importarliquidado ();

        // Inicia a negocio de importacao
        $this->_negocioImpa = new Orcamento_Business_Negocio_ImpaImportar ();

        // Pega o nome da modulo
        $this->_module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();

        // Pega o nome da controller
        $this->_controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        
        // Pega o nome da action
        $this->_action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();                

        // Conforme orienta na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function indexAction() {
        // Nome da funcionalidade
        $funcionalidade = "Importar {$this->_titulo}";

        // Exibir listagem de registros
        $this->listar($funcionalidade);
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function detalheAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Dados';

        // Exibição de um registro
        $this->detalhe();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function incluirAction ()
    {

        // Título da tela (action)
        $this->view->telaTitle = 'Incluir Liquidado';
        
        if ($this->getRequest()->isPost()) {
            try {
                $dados = $this->getRequest()->getPost();            
                $res = $this->_negocioImpa->incluirManual($dados, $this->_tipo, $dados['IMPA_AA_IMPORTACAO'] );

                if($res == FALSE){
                    $this->erroOperacao("Não foi possível completar a ação, pois se trata de um registro duplicado.");
                    $this->_redirect( 'orcamento/'.$this->_controller.'/incluir');
                }
                
                if($res["sucesso"]){
                    // remove cache
                    $cache = new Trf1_Cache();
                    $controller = Zend_Controller_Front::getInstance()->getRequest()
                            ->getControllerName();
                    $cache->excluirCache("orcamento_{$controller}_listagem");

                    // sucesso e redirecionamento
                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success'));
                    $this->_redirect( 'orcamento/'.$controller.'/index');                
                }                
            } catch (Exception $e) {
                $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_ERRO . '<br />' . $e->getMessage());
            }

        }

        // Inclusão do registro
        $this->incluir ();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function importarAction() {
        
        // Título da tela (action)
        $this->view->titulo = 'Importar Liquidado';

        try {
            // remove cache
            $cache = new Trf1_Cache();
            $controller = Zend_Controller_Front::getInstance()->getRequest()
                    ->getControllerName();

            $cache->excluirCache("orcamento_{$controller}_listagem");
            
            // Título da tela (action)
            $this->view->telaTitle = '';

            // Dados do grid
            $negocio = new Orcamento_Business_Negocio_ImpaImportar ();
            
            $chavePrimaria = array(
                    0 => "IMPA_ID_IMPORT_ARQUIVO"
                );
            $dados = $negocio->retornaListagem();

            // Geração do grid
            $acoes = array('incluir', 'detalhe', 'editar', 'editarcontrato', 'excluir');
            $camposDetalhes = array(
                'IMPA_ID_IMPORT_ARQUIVO' => array('title' => 'Código', 'abbr' => ''),
                'IMPA_DS_ARQUIVO' => array('title' => 'Nome do Arquivo', 'abbr' => ''),
                'IMPA_VL_RESP_IMPORTACAO' => array('title' => 'Responsavel', 'abbr' => '')                
            );

            $camposOcultos = array(
                'IMPA_DH_IMPORTACAO',
                'IMPA_AA_IMPORTACAO',
                'IMPA_IC_MES',
                'IMPA_IC_TP_ARQUIVO'
            );

            $classeGrid = new Trf1_Orcamento_Grid ();
            $grid = $classeGrid->criaGrid($controller, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);

            // Personalização do grid
            foreach ($camposDetalhes as $campo => $opcoes) {
                $grid->updateColumn($campo, $opcoes);
            }

            // Oculta campos do grid
            $grid->setColumnsHidden($camposOcultos);

            // Exibição do grid
            $this->view->grid = $grid->deploy();            

            // Importa os arquivos
            if ($this->_request->isPost()) {

                // model de liquidado
                $negocioLiquidado = new Orcamento_Business_Negocio_Importarliquidado ();
                // model de importacao
                $negocioImportacao = new Orcamento_Business_Negocio_ImpaImportar ();

                // arquivo a importar e dados do form
                $f = $_FILES;
                $p = $_POST;

                 // Verifica se o ano é maior do que o atual
                if ( $p["IMPA_AA_IMPORTACAO"] > date('Y') ){
                    $this->erroOperacao("Não é possível informar datas futuras para geração de relatório, apenas data anterior ou atual.");
                    $this->_redirect( 'orcamento/'.$this->_controller.'/importar');
                }

                // Verifica a extensão do arquivo
                $extensao = $negocioImportacao->verificaTxt($f);
                
                if( $extensao == FALSE ){
                    $this->erroOperacao("Arquivo em formato não permitido, apenas formato texto (.txt) são permitidos.");
                    $this->_redirect( 'orcamento/'.$this->_controller.'/importar');
                }

                // tabela de importacao -> nome do arquivo e ano
                $resImportacao = $negocioImportacao->incluirDados( $f['IMPA_DS_ARQUIVO']['name'], $p['IMPA_AA_IMPORTACAO'], $this->_tipo );

                if($resImportacao["sucesso"] == FALSE){
                    $this->erroOperacao("Ocorreu um erro e não foi possível completar importação de arquivo.");
                    $this->_redirect( 'orcamento/'.$this->_controller.'/importar');                    
                }
                
                // Tratamento do arquivo
                $arquivo = file($f["IMPA_DS_ARQUIVO"]["tmp_name"], FILE_IGNORE_NEW_LINES);
                
                // Monta o array de valores
                $arrayliquidado = $negocioImportacao->retornaPadrao2($arquivo);

                // Valida o arquivo txt enviado
                $validacao = $negocioImportacao->validaArray($arrayliquidado);              
                if($validacao == FALSE ){
                    $this->erroOperacao("Arquivo corrompido, não foi possível finalizar importação !");
                    $this->_redirect( 'orcamento/'.$this->_controller.'/importar');
                }                                                 

                // Inclui
                foreach ($arrayliquidado as  $liquidado) {                    

                    if( $resImportacao['sucesso'] ){
                        // verifica se já existe se sim deleta
                        $existe = $negocioImportacao->verificaExistentes( $liquidado, $p["IMPA_AA_IMPORTACAO"], $this->_tipo );
                        // tabela de valores
                        $res = $negocioLiquidado->incluirDados( $liquidado, $resImportacao['codigo'] );

                    }                
                }

                $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success'));
                // redirect 
                $this->_redirect( 'orcamento/'.$this->_controller.'/index');

            }

        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function editarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar Liquidado';

        $formulario = new Orcamento_Form_Importarliquidado ();
        $this->view->formulario = $formulario;
        
        $cod = $this->_getParam('cod');
        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();
            $res = $this->_negocioLiquidado->editar($dados);
            if($res == FALSE){
                $this->erroOperacao("Não foi possível completar a ação, pois se trata de um registro duplicado.");
                $this->_redirect( 'orcamento/'.$this->_controller.'/editar/cod/'.$cod);                
            }            
            $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success'));
            $this->_redirect( $this->_module.'/'.$this->_controller.'/'.$this->_action.'/cod/'.$cod);
        }

        // Edição do registro
        $this->editar ();
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function excluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Liquidado';
        
        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {      

            $chavePrimaria = $this->_getParam('cod');            
            // busca os dados dos registros selecionados
            if ($chavePrimaria) {
                    $chaves = explode(',', $chavePrimaria);
                    $registros = $this->retornaRegistro( Orcamento_Business_Dados::ACTION_EXCLUIR, $chavePrimaria );
                    // manda esses dados pra view
                    $this->view->codigo = $this->_negocioLiquidado->chavePrimaria();
                    $this->view->dados = $registros;                    
            }

        // post
        }else{

            $excluir = $this->getRequest()->getPost('cmdExcluir');
            
            if ($excluir == 'Sim') {
                $chavePrimaria = $this->_getParam('cod');

                if ($chavePrimaria) {
                    // Transforma o parâmetro informado para array de $chaves, se for o caso
                    $chaves = explode(',', $chavePrimaria);

                    try {
                        // Exclui o registro
                        $negocioImportacao = new Orcamento_Business_Negocio_ImpaImportar ();
                        $exclusao = $negocioImportacao->exclusaoFisica($chaves);                        

                        // remove cache
                        $cache = new Trf1_Cache();
                        $controller = Zend_Controller_Front::getInstance()->getRequest()
                                ->getControllerName();
                        $cache->excluirCache("orcamento_{$controller}_listagem");

                        // sucesso e redirecionamento
                        $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_SUCESSO, 'status' => 'success'));
                        $this->_redirect( 'orcamento/'.$controller.'/index');

                    } catch (Exception $e) {
                        $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_ERRO . '<br />' . $e->getMessage());
                    }
                }
            } else {
                $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_CANCELAR, 'status' => 'notice'));
                $this->_redirect( 'orcamento/'.$this->_controller.'/index');
            }

        }
    }
    
    // Msg de erro
    private function erroOperacao ($mensagemErro) {
        $erro = $mensagemErro;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::ERR);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'error'));
        return false;
    }

}
