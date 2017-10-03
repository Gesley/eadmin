
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
 * Disponibiliza as funcionalidades ao usuário sobre regra.
 *
 * @category Orcamento
 * @package Orcamento_InformativoController
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_InformativoController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'Informativo' );

        // Define a classe facade
        $this->defineFacade ( 'Informativo' );

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
        $funcionalidade = 'Listagem de Informativos';

        // remove cache
        $cache = new Trf1_Cache();
        $controller = Zend_Controller_Front::getInstance()->getRequest()
            ->getControllerName();
        $cache->excluirCache("orcamento_{$controller}_listagem");

        // Exibir listagem de registros
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
        $this->view->telaTitle = 'Visualizar Informativo';

        $cod = $this->_getParam ( 'cod' );

       if ( $cod ) {
            $negocioMatri = new Orcamento_Business_Negocio_InformativoMatri();
            $res = $negocioMatri->retornaResponsaveis($cod);
            $this->view->responsaveis = $res;
        }

        // Exibição de um registro
        $this->detalhe ();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Cadastrar Informativo';

        // Inclusão do registro
        $this->incluir ();
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar Informativo';

        // Traz a listagem de responsaveis do informativo
        $cod = $this->_getParam ( 'cod' );

        if ( $cod ) {
            $negocioMatri = new Orcamento_Business_Negocio_InformativoMatri();
            $res = $negocioMatri->retornaResponsaveis($cod);
            $this->view->responsaveis = $res;
        }

        // Edição do registro
        $this->editar ();
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
     * Listagem de informativos excluidos
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluidosAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de Informativos';

        // remove cache
        $cache = new Trf1_Cache();
        $controller = Zend_Controller_Front::getInstance()->getRequest()
            ->getControllerName();
        $cache->excluirCache("orcamento_{$controller}_listagem");

        // Exibir listagem de registros
        $this->listar ( $funcionalidade );
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

    /**
     * Exibe a listagem de informativos casa exista mensagens para o usuario
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */

    public function listagemAction()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de Informativos';

        $classeGrid = new Orcamento_Business_Tela_Grid ();

        $negocio = new Orcamento_Business_Negocio_Informativo ();

        // dados da grid
        $opcoes [ 'dados' ] = $negocio->retornaInformativos();

        // Busca o parâmetro 'chavePrimaria'
        $opcoes [ 'chavePrimaria' ] = array('INFO_NR_INFORMATIVO');

        // Busca o parâmetro 'detalhes'
        $opcoes [ 'detalhes' ] = array('');

        // Busca o parâmetro 'ocultos'
        $opcoes [ 'ocultos' ] = array(
        		'INFO_NR_INFORMATIVO',
        		'INFO_DS_INFORMATIVO',
        		'INFR_CD_RESPONSAVEL',
        		'RESP_DS_SECAO',
        		'HOJE',        		
        		'INFM_CD_INFORMATIVO_MATRICULA',
        		'INFO_CD_MATRICULA_EXCLUSAO'
        		
        );

        // Controler
        $opcoes [ 'controle' ] = 'informativo';

        // Configura os campos da tela
        $detalhes = array (
                'INFO_TX_TITULO_INFORMATIVO' => array ( 'title' => 'Titulo',
                        'abbr' => 'Título do informativo' ),
                'SG_FAMILIA_RESPONSAVEL' => array ( 'title' => 'Destinatário',
                        'abbr' => 'Destinatários da mensagem' ),
                'INFO_DT_INICIO' => array ( 'title' => 'Data de publicação',
                        'abbr' => 'Data de publicação do informativo' ),
                'PTRS_CD_PT_COMPLETO' => array ( 'title' => 'PT',
                        'abbr' => 'Código do Programa de Trabalho',
                        'format' => 'Ptcompleto' ),
                'INFO_DT_TERMINO' => array ( 'title' => 'Data de termino',
                        'abbr' => 'Data de termino do informativo' ),
                'PTRS_STATUS' => array ( 'title' => 'Status',
                        'abbr' => 'Informa se o registro foi ou não excluído' ) );

        // configura os detalhes
        $opcoes [ 'detalhes' ] = $detalhes;

        $opcoes [ 'acoesEmMassa' ] = array ( 'detalhe', 'leitura' );

        $grid = $classeGrid->criaGrid( $opcoes );

        $this->view->grid = $grid->deploy( );
    }

    /**
     * Exibe o detalhe do informativo e grava o aceite no banco de dados
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function leituraAction()
    {
        $cod = $this->_getParam ( 'cod' );

        $negocio = new Orcamento_Business_Negocio_Informativo();

        $negocioMat = new Orcamento_Business_Negocio_InformativoMatri();

        $table = new Orcamento_Model_DbTable_Info();

        $dados = (array)$table->fetchRow( "INFO_NR_INFORMATIVO = $cod" )->toArray();

        $this->view->dados = (array)$dados;

        if ( $this->getRequest ()->getPost () ) {

        $resp = $negocio->retornaResponsaveis( $cod );
        // Responsável do usuário logado
        $sessao = new Orcamento_Business_Sessao ();
        $perfilFull = $sessao->retornaPerfil ();

        $resultado = "";
        $responsaveis = array(
                'INFM_CD_INFORMATIVO_RESP'  => (int)$resp['INFR_CD_RESPONSAVEL'],
                'INFM_CD_MATRICULA_LEITURA' => strtoupper( $perfilFull["usuario"] ),
                'INFM_DT_LEITURA'           => new Zend_Db_Expr ( 'SYSDATE' ),
                'INFM_CD_INFORMATIVO'       => $resp['INFR_CD_INFORMATIVO'],
        );



        
         $resultado = $negocioMat->incluir($responsaveis);

            /* foreach ($resp as $value)
            {
                $negocioMat = new Orcamento_Business_Negocio_InformativoMatri();
                $resultado = $negocioMat->incluir($value);
            }
            */
                if ( $resultado["sucesso"] ) {
                    // aceite ok
                    $this->_helper->flashMessenger ( array ( message => Trf1_Orcamento_Definicoes::MENSAGEM_LEITURA_SUCESSO, 'status' => 'success' ) );
                    $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
                    $redirector->gotoUrl('/orcamento/index/index');
                } else {
                    // aceite error - do nothing
                    $this->_helper->flashMessenger ( array ( message => Trf1_Orcamento_Definicoes::MENSAGEM_LEITURA_ERRO, 'status' => 'error' ) );
                    $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
                    $redirector->gotoUrl('/orcamento/index/index');

                }

        } // fim getpost
    }

    private function erroOperacao ( $mensagemErro )
    {
        $erro = $mensagemErro;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest ();
        $log->gravaLog ( $requisicao, $erro, zend_log::ERR );

        $this->_helper->flashMessenger ( array ( message => $erro, 'status' => 'error' ) );
    }

}











