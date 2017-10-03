
<?php

/**
 * Contém controller da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Controller
 *
 * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre pré empenho.
 *
 * @category Orcamento
 * @package Orcamento_PreempenhoController
 * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_PreempenhoController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Pré Empenho');

        // Define a classe facade
        $this->defineFacade('preempenho');

        /**
         * @goto Orcamento_Business_Negocio_Preempenho
         */
        $this->_negocio = new Orcamento_Business_Negocio_Preempenho();

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction() {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de pré empenhos';

        // Formulario na view index
        $frmnc = new Orcamento_Form_Preemp ();


        // Exibe o formulario na view index
        $this->view->frmnc = $frmnc;

        // Importa os arquivos
        if ($this->_request->isPost()) {
            // Arquivo enviado
            $f = $_FILES;

            // Verifica se é txt
            $this->verificaTxt($f);

            // Transforma o arquivo a ser importado
            $arquivo = $this->_negocio->transformaArquivo($f);

            // Eventos disponiveis no sistema
            $arrayEventos = $this->_negocio->retornaEventos();

            // Array com os dados da nota - array
            $nc = array();

            foreach ($arquivo as $file) {
                // Formata a data
                $data = App_Util::stringDateToDate(substr($file, 0, 12));
                $ano = App_Util::stringDateToDate(substr($file, 0, 12));

                // Retorna o evento se disponivel
                $evento = $this->_negocio->verificaEvento(substr($file, 275, 6), $arrayEventos);

                // trata a fonte
                $fonte = $this->_negocio->trataFonte(substr($file, 288, 10));

                // Trata o valor do preempenho
                $campovalor = $this->_negocio->trataValor(substr($file, 306, 15));

                $vlPremp = new Zend_Db_Expr("TO_NUMBER(" . $campovalor . ")");

                // Trata o campo processo baseado na descrição
                $processo = $this->_negocio->montaProcesso(substr($file, 41, 233));

                // Trata a despesa
                $despesa = $this->_negocio->trataDespesa(substr($file, 41, 233));

                $nc[] = array(
                    'PRMP_DT_ANO' => $ano,
                    'PRMP_DT_EMISSAO' => $data, // "TO_DATE('19/01/2015 14:27:00','DD/MM/YYYY HH24:MI:SS')",
                    'PRMP_CD_UG_OPERADOR' => substr($file, 12, 6), // ok
                    'PRMP_CD_UG_FAVORECIDO' => substr($file, 18, 6), // ok
                    'PRMP_CD_NOTA_EMPENHO' => substr($file, 18, 23), //  OK
                    'PRMP_CD_DESPESA' => $despesa,
                    'PRMP_DS_DESCRICAO' => trim(substr($file, 41, 233)), // ok
                    'PRMP_CD_EVENTO' => $evento, //ok
                    'PRMP_CD_ESFERA' => substr($file, 281, 1), // ok
                    'PRMP_CD_PT_RESUMIDO' => substr($file, 282, 6), //ok
                    'PRMP_CD_FONTE' => $fonte, //ok
                    'PRMP_CD_ELEMENTO_DESPESA_SUB' => substr($file, 298, 8),
                    'PRMP_VL_VALOR' => $vlPremp, // ok
                    'PRMP_CD_PROCESSO' => $processo, // ok
                    'PRMP_IC_ACERTADO_MANUALMENTE' => 0 // ok
                );
            }



            // verifica se ja existe o empenho
            foreach ($nc as $preemp) {
                $empenho = $this->_negocio->verificaExistencia($preemp);
                if ($empenho["QUATIDADE"] > 0) {
                    $deletados = $this->_negocio->deletaExistentes($preemp);
                }
            }

            foreach ($nc as $preemp) {
                try {
                    // inclui na tabela de nc
                    $resultado = $this->_negocio->incluir($preemp);
                    if ($resultado["sucesso"]) {
                        // Limpa o cache
                        $this->_negocio->excluiCaches();
                        // Msg Sucesso
                        $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success'));
                    }
                } catch (Exception $e) {
                    // Gera o erro
                    $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                }
            }

            // Volta pra listagem
            $this->_redirect('orcamento/preempenho/index');
        }

        // Exibir listagem de registros
        $this->listar($funcionalidade);
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar pré empenho';

        // Exibição de um registro
        $this->detalhe();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir pré empenho';

        // Inclusão do registro
        $this->incluir();
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editarAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar pré empenho';

        // Edição do registro
        $this->editar();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir pré empenho';

        // Exclusão do registro
        $this->excluir(true);
    }

    // Verifica se é um txt
    public function verificaTxt($f) {
        $file = pathinfo($f["TEXTO"]["name"]);
        if ($file["extension"] != 'TXT') {
            $this->erroOperacao("O arquivo não é txt!");
        }
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * @author Gesley B. Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function restaurarAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar pré empenho';

        // Restauração de registro logicamente excluído
        $this->restaurar();
    }

    // flashmessgender do erro
    private function erroOperacao($mensagemErro) {
        $erro = $mensagemErro;

        // Registra o erro
        /* $log = new Trf1_Orcamento_Log ();
          $requisicao = $this->getRequest();
          $log->gravaLog($requisicao, $erro, zend_log::ERR);
         */
        // $this->_helper->flashMessenger ( array ( message => $erro, 'status' => 'error' ) );
    }

}
