<?php
/**
 * Contém controller da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Controller
 *
 * Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre permissão.
 *
 * @category Orcamento
 * @package Orcamento_PermissoesController
 * @author Gesley B Rodrgieus <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_PermissaoController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Permissao');

        // Define a classe facade
        $this->defineFacade('Permissao');

        // Define a businnes
        $this->_business = new Orcamento_Business_Negocio_Permissao();

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction() {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de Permissoes';

        // Exibir listagem de registros
        $this->listar($funcionalidade);
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar permissão';

        // Exibição de um registro
        $this->detalhe();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir permissão';

        // Sobrescreve o metodo incluir() para tratamento dos dados
        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            // Remove o nome da matricula
            $newmatricula = $this->_business->tratamatricula($dados['PERM_CD_MATRICULA']);

            $dados['PERM_CD_MATRICULA'] = $newmatricula;

            $formPerm = new Orcamento_Form_Permissao();

            if ($formPerm->isValid($dados)) {

                $this->verificaDuplicidade(strtoupper($dados['PERM_CD_MATRICULA']));
                
                $dados = $this->trataDados($dados);

                $res = $this->_business->incluir($dados);

                if ($res['sucesso']) {

                    // sucesso e redirecionamento
                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success'));

                    $this->_redirect('orcamento/permissao/index');
                } else {

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $res['msgErro'], 'status' => 'error'));

                    $this->_redirect('orcamento/permissao/incluir');
                }
            }
        }
        // Inclusão do registro
        $this->incluir();
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editarAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar permissão';

        // Edição do registro
        $this->editar();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir permissão';

        // Exclusão do registro
        $this->excluir(true);
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function restaurarAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar permissão';

        // Restauração de registro logicamente excluído
        $this->restaurar();
    }

    public function trataDados($dados) {

        // matriculas maiusculas
        $dados["PERM_CD_MATRICULA"] = strtoupper($dados["PERM_CD_MATRICULA"]);

        if ($dados['CH_UG'] == '1') {
            $dados["PERM_CD_UNIDADE_GESTORA"] = '99999';
        }

        // trata a unidade gestora ( todos )
        unset($dados['CH_UG']);

        // trata a responsabilidade ( todos )
        if ($dados['CH_RESP'] == '1') {
            $dados['PERM_DS_RESPONSABILIDADE'] = 'todos';
        }

        unset($dados['CH_RESP']);

        return $dados;
    }

    /**
     * Verifica se o usuário já existe na tabela de permissoes
     * @param $matricula
     * @return bool
     */
    private function verificaDuplicidade($matricula) {
        $businesPerm = new Orcamento_Business_Negocio_Permissao();
        $duplicidade = $businesPerm->verificaDuplicidade(strtoupper($matricula));
        // verifica se o usuário ja existe
        if (is_array($duplicidade)) {
            $this->_helper->flashMessenger(array(message => 'Usuário já existe. Você foi redirecionado pra edição', 'status' => 'info'));
            $this->_redirect('orcamento/permissao/editar/cod/' . $duplicidade['PERM_ID_PERMISSAO_ACESSO']);
            return false;
        }
        return true;
    }

    public function ajaxretornausuarioAction() {
        $matriculanome = $this->_getParam('term', '');

        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $nome_array = $OcsTbPmatMatricula->getNomeSolicitanteAjax($matriculanome);

        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    // retorna dados para o campo Responsabilidade.
    public function ajaxretornaresponsabilidadeAction() {

        $matriculanome = $this->_getParam('term', '');
        $nome_array = $this->_business->retornaComboResponsavel($matriculanome);
        $fim = count($nome_array);

        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }

        $this->_helper->json->sendJson($nome_array);
    }

}