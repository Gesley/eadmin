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
 * Disponibiliza as funcionalidades ao usuário sobre Empenho.
 *
 * @category Orcamento
 * @package Orcamento_EmpenhoController
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_EmpenhoController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Saldo de empenho por Despesa');

        // Define a classe facade
        $this->defineFacade('Empenho');

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction() {
        $cache = new Trf1_Cache();
        $controller = Zend_Controller_Front::getInstance()->getRequest()
            ->getControllerName();

        $cache->excluirCache("orcamento_{$controller}_listagem");

        // Título da tela (action)
        $this->view->telaTitle = 'Saldo de Empenho por Despesa';

        $classeGrid = new Orcamento_Business_Tela_Grid();

        $negocio = new Orcamento_Business_Negocio_Empenho();

        $saldo = new Trf1_Orcamento_Negocio_Saldo();

        // dados da grid
        $opcoes['dados'] = $negocio->retornaEmpenhos();

        // Busca o parâmetro 'chavePrimaria'
        $opcoes['chavePrimaria'] = array('NOEM_NR_DESPESA');

        // Busca o parâmetro 'detalhes'
        $opcoes['detalhes'] = array('');

        // Busca o parâmetro 'ocultos'
        $opcoes['ocultos'] = array('');

        // Controler
        $opcoes['controle'] = 'empenho';

        // Configura os campos da tela
        $detalhes = array(
            'NOEM_CD_NOTA_EMPENHO' => array('title' => 'Nota de Empenho', 'abbr' => ''),
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'UNGE_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'NOEM_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DESP_DS_ADICIONAL' => array('title' => 'Descrição', 'abbr' => ''),
            'PTRS_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte de Recursos', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'EDSB_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza da despesa', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'DESP_CD_FONTE' => array('title' => 'Fonte de Recursos', 'abbr' => ''),
            // 'EXEC_CD_NOTA_EMPENHO' => array('title' => 'Código Nota de empenho', 'abbr' => '', 'format' => 'Notas'),
            // 'NOEM_CD_NOTA_EMPENHO' => array('title' => 'Nota de empenho', 'abbr' => '', 'format' => 'Notas'),
            'NOEM_DS_OBSERVACAO' => array('title' => 'Descrição da NE', 'abbr' => '', 'format' => 'Notas'),
            //'VR_PROPOSTA_APROVADA' => array ( 'title' => 'Proposta Aprovada', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
            // 'VR_CREDITO_ADICIONAL' => array ( 'title' => 'Credito Adicional', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
            // 'VR_A_EMPENHAR' => array ( 'title' => 'Valor a empenhar', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
            'EMPENHADO' => array('title' => 'Valor empenhado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'EXECUTADO' => array('title' => 'Valor executado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'A_EXECUTAR' => array('title' => 'Valor a executar', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
        );

        // configura os detalhes
        $opcoes['detalhes'] = $detalhes;

        $opcoes['ocultos'] = array('EXERCICIO');

        $opcoes['acoesEmMassa'] = array('detalhe');

        $grid = $classeGrid->criaGrid($opcoes);

        $this->view->grid = $grid->deploy();
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Empenho';

        // Identifica o parâmetro da chave primária a ser buscada
        $chavePrimaria = $this->_getParam('cod');

        if ($chavePrimaria) {
            // Busca registro específico
            $negocio = new Orcamento_Business_Negocio_Empenho();
            $negocioNe = new Trf1_Orcamento_Negocio_Ne();
            $registro = $negocio->retornaEmpenhos($chavePrimaria);

            $saldo = new Trf1_Orcamento_Negocio_Saldo();

            // Exibe os dados do registro
            $this->view->dados = $saldo->retornaSaldo($chavePrimaria);
            $this->view->execucao = $negocioNe->retornaExecucao($chavePrimaria);
        } else {
            $this->registroNaoEncontrado();
        }

    }

    protected function registroNaoEncontrado() {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_REGISTRO_NAO_ENCONTRADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'notice'));
        $this->voltarIndexAction();
    }

    protected function codigoNaoInformado() {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'notice'));
        $this->voltarIndexAction();
    }

}
