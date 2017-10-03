<?php

class Guardiao_PerfilpessoaController extends Zend_Controller_Action
{

    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch()
    {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function init()
    {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        /* Initialize action controller here */
        $this->view->titleBrowser = "e-Guardião";

        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
    }

    public function indexAction()
    {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order = $this->_getParam('ordem', 'PEPE_ID_PERFIL_PESSOA');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order . ' ' . $direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /* Ordenação */

        $table = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        $select = $table->select()->order($order_aux);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Perfil Pessoa";

        //$this->_helper->layout->disableLayout();
    }

    public function ajaxpessoastribunalAction()
    {
        $matriculanome = $this->_getParam('term', '');
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $nome_array = $OcsTbPmatMatricula->getNomeSolicitanteAjax($matriculanome);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxpessoassecaoAction()
    {
        $matriculanome = $this->_getParam('term', '');
        $sg = $this->_getParam('secao');
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $nome_array = $OcsTbPmatMatricula->getNomeSolicitanteSecaoAjax($matriculanome, $sg);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function formAction()
    {

        $this->view->title = "Permissões de Acesso";
        $aNamespace = new Zend_Session_Namespace('userNs');
        $formPorPessoa = new Guardiao_Form_PermissaoPorPessoa();
        $formPorUnidade = new Guardiao_Form_PerfilPessoaUnidade();

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        //Variavél que conterá os perfis que o usuário possui
        $array_perfis_post = null;
        $array_perfis_vincular = null;
        $array_perfis_desvincular = null;

        if ($this->getRequest()->isPost()) {
            /* Instanciando as DbTables necessárias */
            $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();

            /* Instanciando as Regras de Negócios necessárias */
            $rn_Permissao = new Trf1_Guardiao_Negocio_Permissao();
            /* Instanciando as Apps necessárias */

            //variável que armazena o post da requisição
            $dataPost = $this->getRequest()->getPost();


            //corrige o array de dados, no caso de pessoa ou unidade
            if ($dataPost['PMAT_CD_MATRICULA_PESSOA'] && $dataPost['LOTA_COD_LOTACAO_PESSOA']) {
                $dataPost['PMAT_CD_MATRICULA'] = $dataPost['PMAT_CD_MATRICULA_PESSOA'];
                $dataPost['LOTA_COD_LOTACAO'] = $dataPost['LOTA_COD_LOTACAO_PESSOA'];
            }

            if (isset($dataPost['form_validator']) && $dataPost['form_validator'] == 'form_validator') {
                if (isset($dataPost['LOTA_COD_LOTACAO']) && $dataPost['LOTA_COD_LOTACAO'] != "") {

                    /**
                     * Matricula
                     */
//                    if (isset($dataPost['PMAT_CD_MATRICULA']) && $dataPost['PMAT_CD_MATRICULA'] != "") {
//                        $arrayMatricula = explode(' - ', $dataPost['PMAT_CD_MATRICULA']);
//                    } else {
//                        $arrayMatricula = explode(' - ', $dataPost['PUPE_CD_MATRICULA']);
//                    }

                    if (isset($dataPost['SECAO_CD_MATRICULA']) && $dataPost['SECAO_CD_MATRICULA'] != "") {
                        $arrayMatricula = explode(' - ', $dataPost['SECAO_CD_MATRICULA']);
                    } elseif (isset($dataPost['PMAT_CD_MATRICULA']) && $dataPost['PMAT_CD_MATRICULA'] != "") {
                        $arrayMatricula = explode(' - ', $dataPost['PMAT_CD_MATRICULA']);
                    } elseif (isset($dataPost['PUPE_CD_MATRICULA']) && $dataPost['PUPE_CD_MATRICULA'] != "") {
                        $arrayMatricula = explode(' - ', $dataPost['PUPE_CD_MATRICULA']);
                    } elseif (isset($dataPost['RESPCAIXA_CD_MATRICULA']) && $dataPost['RESPCAIXA_CD_MATRICULA'] != "") {
                        $arrayMatricula = explode(' - ', $dataPost['RESPCAIXA_CD_MATRICULA']);
                    } else {
                        $msg_to_user = "Selecione as informações necessárias para conceder permissões!";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                        $this->_helper->redirector('form', 'perfilpessoaadm', 'guardiao');
                    }


                    /**
                     * Dados da Unidade
                     */
                    $unidade = explode('|', $dataPost['LOTA_COD_LOTACAO']);
                    $dadosUnidade['LOTA_SIGLA_SECAO'] = $unidade[0];
                    $dadosUnidade['LOTA_COD_LOTACAO'] = $unidade[1];

                    /**
                     * Se foi responsavel pela caixa e não veio no post
                     */
                    if (($dataPost['PMAT_CD_MATRICULA'] == '') && ($dataPost['PMAT_CD_MATRICULA'] == "")) {
                        if ($dataPost["GRUPOPESSOAS"] == 'pessoaacesso') {
                            $dataPost['PMAT_CD_MATRICULA'] = $dataPost["RESPCAIXA_CD_MATRICULA"];
                            $arrayMatricula = explode(' - ', $dataPost['PMAT_CD_MATRICULA']);
                        }
                    }


                    /**
                     * Verificar se o usuário já possui perfis para esta Unidade
                     */
                    $verifica_perfis = $ocsTbPupePerfilUnidPessoa->getPerfisPessoaNaUnidade(
                        $dadosUnidade['LOTA_SIGLA_SECAO'], $dadosUnidade['LOTA_COD_LOTACAO'], $arrayMatricula[0]);

                    if (count($verifica_perfis) != 0) {
                        /**
                         * Ja possui perfis desta Unidade
                         * Verifica se foram adicionados mais perfis
                         */
                        if (isset($dataPost["perfis_unidade"]) && $dataPost["perfis_unidade"] != "") {
                            /**
                             * Ver quais adicionar e quais retirar
                             * Buscar os Perfis que ja possui associados
                             */
                            foreach ($verifica_perfis as $perfil_assoc) {
                                $array_perfis_assoc[] = $perfil_assoc['PERF_ID_PERFIL'];
                            }

                            /**
                             * Array de Perfis vindos do form
                             */
                            $array_perfis_post = $dataPost['perfis_unidade'];

                            /**
                             * Verificar o que incluir e o que excluir
                             */
                            $array_perfis_desvincular = array_diff($array_perfis_assoc, $array_perfis_post);
                            $array_perfis_vincular = array_diff($array_perfis_post, $array_perfis_assoc);

                            if (count($array_perfis_desvincular) == 0 && count($array_perfis_vincular) == 0) {
                                $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Nenhuma alteração feita para o usuário!</div>";
                            } else {
                                /**
                                 * Chamar funcao para fazer associação dos novos perfis
                                 * Chamar função para desvincular os perfis removidos
                                 */
                                /**
                                 * Associar
                                 */
                                $resultado_assoc = $rn_Permissao->associarPerfilPessoaUnidade($array_perfis_vincular, $arrayMatricula[0], $dadosUnidade, $aNamespace->matricula);
                                /**
                                 * Desassociar
                                 */
                                $resultado_desassoc = $rn_Permissao->desassociarPerfilPessoaUnidade($array_perfis_desvincular, $dadosUnidade, $arrayMatricula[0], $aNamespace->matricula);

                                if ($resultado_assoc && $resultado_desassoc) {
                                    $flashMessagesView = "<div class='success'><strong>Sucesso:</strong>Perfis do usuário atualizados com sucesso!</div>";
                                } else {
                                    $flashMessagesView = "<div class='error'><strong>Erro:</strong>Ocorreu um erro ao atualizar os perfis do usuário!</div>";
                                }
                            }
                        } else {
                            /**
                             * Possui perfis, porem não foi adicionado nenhum, então DESVINCULAR TODOS desta pessoa
                             */
                            $desassociar = $rn_Permissao->desassociarTodoPerfilPessoaUnidade($arrayMatricula[0], $dadosUnidade, $aNamespace->matricula);
                            if ($desassociar) {
                                $flashMessagesView = "<div class='success'><strong>Sucesso:</strong>Perfis desvinculados com sucesso!</div>";
                            } else {
                                $flashMessagesView = "<div class='error'><strong>Erro:</strong>Ocorreu um erro ao desvincular os perfis!</div>";
                            }
                        }
                    } else {
                        /**
                         * Não possui perfis desta Unidade
                         * Agora verifica se foi adicionado algum
                         */
                        if (isset($dataPost["perfis_unidade"]) && $dataPost["perfis_unidade"] != "") {
                            /**
                             * Adicionar os Novos Perfis
                             */
                            $associar = $rn_Permissao->associarPerfilPessoaUnidade($dataPost['perfis_unidade'], $arrayMatricula[0], $dadosUnidade, $aNamespace->matricula);

                            if ($associar) {
                                $flashMessagesView = "<div class='success'><strong>Sucesso:</strong>Perfis do usuário atualizados com sucesso!</div>";
                            } else {
                                $flashMessagesView = "<div class='error'><strong>Erro:</strong>Ocorreu um erro ao associar os perfis!</div>";
                            }
                        } else {
                            /**
                             * Não tem perfil e não foi adicionado nenhum novo. Retornar
                             */
                            $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Nenhuma alteração efetuada para o Usuário!</div>";
                        }
                    }
                } else {
                    $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Selecione uma unidade para completar a ação!</div>";
                }
            } else {
                $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Preencha todos os campos obrigatórios!</div>";
            }

            /**
             * Manter ultima pesquisa
             */
            if (isset($dataPost['PMAT_CD_MATRICULA'])) {
                $this->view->matricula = $dataPost['PMAT_CD_MATRICULA'];
            }

            $label_unidade = "";

            if (isset($dataPost['LOTA_COD_LOTACAO'])) {

                $sg_cod = explode("|", $dataPost['LOTA_COD_LOTACAO']);
                $subsecao = $rh_central->getSecSubsecPai($sg_cod[0], $sg_cod[1]);
                $getLotacao = $rh_central->getLotacaobySecao($subsecao['LOTA_SIGLA_SECAO'], $subsecao['LOTA_COD_LOTACAO'], $subsecao['LOTA_TIPO_LOTACAO']);
                foreach ($getLotacao as $lotacao) {
                    $codigo_unidade = $lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"];
                    if ($dataPost['LOTA_COD_LOTACAO'] == $codigo_unidade) {
                        $label_unidade = $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"];
                    }
                }
                $this->view->unidade = $dataPost['LOTA_COD_LOTACAO'];
                $this->view->label_unidade = $label_unidade;
            }
            if (isset($dataPost['tipo_pesquisa'])) {
                $this->view->pesquisa = $dataPost['tipo_pesquisa'];
            }

            $this->view->flashMessagesView = $flashMessagesView;
        }//POST

        $this->view->secao = $aNamespace->siglasecao;
        $this->view->formPorPessoa = $formPorPessoa;
        $this->view->formPorUnidade = $formPorUnidade;
    }

    public function formunidadeAction()
    {
        $this->view->title = "Permissões de Acesso";
        $form = new Guardiao_Form_PerfilPessoaUnidade();
        $aNamespace = new Zend_Session_Namespace('userNs');
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();

        //Variavél que conterá os perfis que o usuário possui
        $array_perfis_post = null;
        $array_perfis_vincular = null;
        $array_perfis_desvincular = null;

        if ($this->getRequest()->isPost()) {
            /* Instanciando as DbTables necessárias */
            $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
            /* Instanciando as Regras de Negócios necessárias */
            $rn_Permissao = new Trf1_Guardiao_Negocio_Permissao();
            /* Instanciando as Apps necessárias */

            //variável que armazena o post da requisição
            $dataPost = $this->getRequest()->getPost();

            if (isset($dataPost['form_validator']) && $dataPost['form_validator'] == "form_validator") {
                if (isset($dataPost['LOTA_COD_LOTACAO']) && $dataPost['LOTA_COD_LOTACAO'] != "") {

                    /**
                     * Matricula
                     */
                    if (isset($dataPost['SECAO_CD_MATRICULA']) && $dataPost['SECAO_CD_MATRICULA'] != "") {
                        $arrayMatricula = explode(' - ', $dataPost['SECAO_CD_MATRICULA']);
                    } elseif (isset($dataPost['PMAT_CD_MATRICULA']) && $dataPost['PMAT_CD_MATRICULA'] != "") {
                        $arrayMatricula = explode(' - ', $dataPost['PMAT_CD_MATRICULA']);
                    } elseif (isset($dataPost['PUPE_CD_MATRICULA']) && $dataPost['PUPE_CD_MATRICULA'] != "") {
                        $arrayMatricula = explode(' - ', $dataPost['PUPE_CD_MATRICULA']);
                    } elseif (isset($dataPost['RESPCAIXA_CD_MATRICULA']) && $dataPost['RESPCAIXA_CD_MATRICULA'] != "") {
                        $arrayMatricula = explode(' - ', $dataPost['RESPCAIXA_CD_MATRICULA']);
                    } else {
                        $msg_to_user = "Selecione as informações necessárias para conceder permissões!";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                        $this->_helper->redirector('form', 'perfilpessoaadm', 'guardiao');
                    }

                    /**
                     * Dados da Unidade
                     */
                    $unidade = explode('|', $dataPost['LOTA_COD_LOTACAO']);
                    $dadosUnidade['LOTA_SIGLA_SECAO'] = $unidade[0];
                    $dadosUnidade['LOTA_COD_LOTACAO'] = $unidade[1];


                    /**
                     * Verificar se o usuário já possui perfis para esta Unidade
                     */
                    $verifica_perfis = $ocsTbPupePerfilUnidPessoa->getPerfisPessoaNaUnidade($dadosUnidade['LOTA_SIGLA_SECAO'], $dadosUnidade['LOTA_COD_LOTACAO'], $arrayMatricula[0]);
                    if (count($verifica_perfis) != 0) {
                        /**
                         * Ja possui perfis desta Unidade
                         * Verifica se foram adicionados mais perfis
                         */
                        if (isset($dataPost["perfis_unidade"]) && $dataPost["perfis_unidade"] != "") {
                            /**
                             * Ver quais adicionar e quais retirar
                             * Buscar os Perfis que ja possui associados
                             */
                            foreach ($verifica_perfis as $perfil_assoc) {
                                $array_perfis_assoc[] = $perfil_assoc['PERF_ID_PERFIL'];
                            }

                            /**
                             * Array de Perfis vindos do form
                             */
                            $array_perfis_post = $dataPost['perfis_unidade'];

                            /**
                             * Verificar o que incluir e o que excluir
                             */
                            $array_perfis_desvincular = array_diff($array_perfis_assoc, $array_perfis_post);
                            $array_perfis_vincular = array_diff($array_perfis_post, $array_perfis_assoc);

                            if (count($array_perfis_desvincular) == 0 && count($array_perfis_vincular) == 0) {
                                $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Nenhuma alteração feita para o Usuário!</div>";
                            } else {
                                /**
                                 * Chamar funcao para fazer associação dos novos perfis
                                 * Chamar função para desvincular os perfis removidos
                                 */
                                /**
                                 * Associar
                                 */
                                $resultado_assoc = $rn_Permissao->associarPerfilPessoaUnidade($array_perfis_vincular, $arrayMatricula[0], $dadosUnidade, $aNamespace->matricula);
                                /**
                                 * Desassociar
                                 */
                                $resultado_desassoc = $rn_Permissao->desassociarPerfilPessoaUnidade($array_perfis_desvincular, $dadosUnidade, $arrayMatricula[0], $aNamespace->matricula);

                                if ($resultado_assoc && $resultado_desassoc) {
                                    $flashMessagesView = "<div class='success'><strong>Alerta:</strong>Perfis do usuário atualizados com sucesso!</div>";
                                } else {
                                    $flashMessagesView = "<div class='error'><strong>Alerta:</strong>Ocorreu um erro ao atualizar os perfis do usuário!</div>";
                                }
                            }
                        } else {
                            /**
                             * Possui perfis, porem não foi adicionado nenhum, então DESVINCULAR TODOS desta pessoa
                             */
                            $desassociar = $rn_Permissao->desassociarTodoPerfilPessoaUnidade($arrayMatricula[0], $dadosUnidade, $aNamespace->matricula);
                            if ($desassociar) {
                                $flashMessagesView = "<div class='success'><strong>Alerta:</strong>Perfis do usuário desvinculados com sucesso!</div>";
                            } else {
                                $flashMessagesView = "<div class='error'><strong>Alerta:</strong>Ocorreu um erro ao desvincular os perfis!</div>";
                            }
                        }
                    } else {
                        /**
                         * Não possui perfis desta Unidade
                         * Agora verifica se foi adicionado algum
                         */
                        if (isset($dataPost["perfis_unidade"]) && $dataPost["perfis_unidade"] != "") {
                            /**
                             * Adicionar os Novos Perfis
                             */
                            $associar = $rn_Permissao->associarPerfilPessoaUnidade($dataPost['perfis_unidade'], $arrayMatricula[0], $dadosUnidade, $aNamespace->matricula);
                            if ($associar) {
                                $flashMessagesView = "<div class='success'><strong>Alerta:</strong>Perfis do usuário atualizados com sucesso!</div>";
                            } else {
                                $flashMessagesView = "<div class='error'><strong>Alerta:</strong>Ocorreu um erro ao associar os perfis!</div>";
                            }
                        } else {
                            /**
                             * Não tem perfil e não foi adicionado nenhum novo. Retornar
                             */
                            $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Nenhuma alteração efetuada para o Usuário!</div>";
                        }
                    }
                } else {
                    $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Selecione uma Unidade para completar a ação!</div>";
                }
            } else {
                $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Preencha todos os campos obrigatórios!</div>";
            }

            /*
             * Mensagens para a VIEW
             */
            $this->view->flashMessagesView = $flashMessagesView;
            $form->populate($dataPost);
            /*
             * Manter ultima pesquisa
             */
            if (isset($dataPost["TRF1_SECAO"])) {
                $this->view->secao = $dataPost["TRF1_SECAO"];
            }
            if (isset($dataPost["SECAO_SUBSECAO"])) {
                $this->view->subsecao = $dataPost["SECAO_SUBSECAO"];
            }

            $label_unidade = "";

            if (isset($dataPost["LOTA_COD_LOTACAO"])) {
                $sg_cod = explode("|", $dataPost['LOTA_COD_LOTACAO']);
                $subsecao = $rh_central->getSecSubsecPai($sg_cod[0], $sg_cod[1]);
                $getLotacao = $rh_central->getLotacaobySecao($subsecao['LOTA_SIGLA_SECAO'], $subsecao['LOTA_COD_LOTACAO'], $subsecao['LOTA_TIPO_LOTACAO']);
                foreach ($getLotacao as $lotacao) {
                    $codigo_unidade = $lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"];
                    if ($dataPost['LOTA_COD_LOTACAO'] == $codigo_unidade) {
                        $label_unidade = $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"];
                    }
                }
                $this->view->unidade = $dataPost['LOTA_COD_LOTACAO'];
                $this->view->label_unidade = $label_unidade;
            }
            if (isset($dataPost["GRUPOPESSOAS"])) {
                $this->view->pesquisa = $dataPost["GRUPOPESSOAS"];
            }
            if (isset($dataPost["PUPE_CD_MATRICULA"])) {
                $this->view->pupe_matricula = $dataPost["PUPE_CD_MATRICULA"];
            }
            if (isset($dataPost["PMAT_CD_MATRICULA"])) {
                $this->view->pmat_matricula = $dataPost["PMAT_CD_MATRICULA"];
            }
            if (isset($dataPost["RESPCAIXA_CD_MATRICULA"])) {
                $this->view->resp_matricula = $dataPost["RESPCAIXA_CD_MATRICULA"];
            }
        }//POST

        /*
         * View
         */
        $this->view->form = $form;
    }

    public function pessoaacessounidadeAction()
    {
        $Ns_Perfilpessoa_form = new Zend_Session_Namespace('Ns_Perfilpessoa_form');
        $aNamespace = new Zend_Session_Namespace('userNs');

        $this->view->title = "Pessoas com Acesso na Unidade";

        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Guardiao_Form_PerfilPessoa();
        $table = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $table_audit = new Application_Model_DbTable_OcsTbPupeAuditoria();
        $form->PUPE_CD_MATRICULA->setLabel('Pessoas com acesso:');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data_post_atual = $data;
            $dados_lotacao = Zend_Json::decode($data['LOTA_COD_LOTACAO']);

            if ($dados_lotacao != null) {
                $ocsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
                $pessoasAcesso = $ocsTbUnpeUnidadePerfil->getPessoasComPerfilX(9, $dados_lotacao['LOTA_SIGLA_SECAO'], $dados_lotacao['LOTA_COD_LOTACAO']);

                $verificaPessoa = false;

                foreach ($pessoasAcesso as $pessoas_p):
                    $form->PUPE_CD_MATRICULA->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
                    if ($pessoas_p["PMAT_CD_MATRICULA"] == $data['PUPE_CD_MATRICULA']) {
                        $verificaPessoa = true;
                    }
                endforeach;

                if ($data['PUPE_CD_MATRICULA'] && $verificaPessoa) {
                    $select = $table->getPerfilUnidadePessoa($dados_lotacao['LOTA_SIGLA_SECAO'], $dados_lotacao['LOTA_COD_LOTACAO'], $data['PUPE_CD_MATRICULA']);
                }
            }
            if ($data['acao'] == 'Alterar') {

                if ($data['PMAT_CD_MATRICULA']) {
                    exit;
                    $array_matricula = explode(' - ', $data['PMAT_CD_MATRICULA']);
                    $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
                    $rowPmatMatricula = $OcsTbPmatMatricula->fetchRow("PMAT_CD_MATRICULA = '$array_matricula[0]'");
                    if ($rowPmatMatricula) {
                        $data['PUPE_CD_MATRICULA'] = $array_matricula[0];
                    }
                }


                if ($Ns_Perfilpessoa_form->data_post_utimo_executado != $data) {

                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $flag = FALSE;
                    foreach ($data['papeis'] as $papeis):
                        $papelArray1 = explode(" - ", $papeis[1]);
                        $data['PUPE_ID_UNIDADE_PERFIL'] = $papelArray1[0];
                        //$data['PUPE_ID_UNIDADE_PERFIL'] = $papelArray1[1];
                        $codigo1 = $papelArray1[2];

                        $papelArray2 = explode(" - ", $papeis[2]);
                        $data['PUPE_ID_UNIDADE_PERFIL'] = $papelArray2[0];
                        //$data['PUPE_ID_UNIDADE_PERFIL'] = $papelArray2[1];
                        $codigo2 = $papelArray2[2];

                        //alterações de DELETE
                        if ($codigo1 == "" && $codigo2 == "associado") {
                            $flag = TRUE;
                            try {
                                $db->beginTransaction();
                                $data['PUPE_CD_MATRICULA'] = $data['PUPE_CD_MATRICULA'];
                                $rowPerfilUnidPessoa = $table->fetchRow("PUPE_ID_UNIDADE_PERFIL = $data[PUPE_ID_UNIDADE_PERFIL] AND PUPE_CD_MATRICULA = '$data[PUPE_CD_MATRICULA]'");
                                $rowPerfilUnidPessoa->delete();

                                $dual = new Application_Model_DbTable_Dual();
                                $dataTimeStamp = $dual->localtimestampDb();

                                $data_audit['PUPE_TS_OPERACAO'] = $dataTimeStamp['DATA'];
                                $data_audit['PUPE_CD_OPERACAO'] = 'E';
                                $data_audit['PUPE_CD_MATRICULA_OPERACAO'] = $aNamespace->matricula;
                                $data_audit['PUPE_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                $data_audit['PUPE_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                $data_audit['OLD_PUPE_ID_UNIDADE_PERFIL'] = $data['PUPE_ID_UNIDADE_PERFIL'];
                                $data_audit['NEW_PUPE_ID_UNIDADE_PERFIL'] = 0;
                                $data_audit['OLD_PUPE_CD_MATRICULA'] = $data['PUPE_CD_MATRICULA'];
                                $data_audit['NEW_PUPE_CD_MATRICULA'] = 0;

                                $rowPupeAuditoria = $table_audit->createRow($data_audit);
                                $rowPupeAuditoria->save();

                                $db->commit();
                            } catch (Zend_Exception $e) {
                                $db->rollBack();

                                $erro = $e->getMessage();

                                $msg_to_user = "Erro ao conceder o perfil </br> $erro";
                                $flashMessages = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";


                                $this->view->flashMessagesView = $this->view->flashMessagesView . $flashMessages;
                            }

                            //alterações de INSERT
                        } else if ($codigo1 == "associar" && $codigo2 == "dissociado") {
                            $flag = TRUE;
                            unset($data_audit);
                            try {
                                $db->beginTransaction();
                                $data['PUPE_CD_MATRICULA'] = $data['PUPE_CD_MATRICULA'];
                                $row = $table->createRow($data);
                                $idsalvo = $row->save();

                                $dual = new Application_Model_DbTable_Dual();
                                $dataTimeStamp = $dual->localtimestampDb();

                                $data_audit['PUPE_TS_OPERACAO'] = $dataTimeStamp['DATA'];
                                $data_audit['PUPE_CD_OPERACAO'] = 'I';
                                $data_audit['PUPE_CD_MATRICULA_OPERACAO'] = $aNamespace->matricula;
                                $data_audit['PUPE_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                $data_audit['PUPE_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                $data_audit['OLD_PUPE_ID_UNIDADE_PERFIL'] = 0;
                                $data_audit['NEW_PUPE_ID_UNIDADE_PERFIL'] = $idsalvo["PUPE_ID_UNIDADE_PERFIL"];
                                $data_audit['OLD_PUPE_CD_MATRICULA'] = 0;
                                $data_audit['NEW_PUPE_CD_MATRICULA'] = $idsalvo["PUPE_CD_MATRICULA"];

                                $rowPupeAuditoria = $table_audit->createRow($data_audit);
                                $rowPupeAuditoria->save();

                                $db->commit();
                            } catch (Zend_Exception $e) {
                                $db->rollBack();

                                $erro = $e->getMessage();

                                $msg_to_user = "Erro ao conceder o perfil </br> $erro";
                                $flashMessages = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";


                                $this->view->flashMessagesView = $this->view->flashMessagesView . $flashMessages;
                            }
                        }
                    endforeach;
                    if ($flag != TRUE) {
                        $msg_to_user = "Nenhum papel foi modificado";
                        $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    } else {
                        $Ns_Perfilpessoa_form->data_post_utimo_executado = $data_post_atual;

                        if (!$this->view->flashMessagesView) {

                            $msg_to_user = "Alterações Realizadas.";
                            $flashMessages = "<div class='success'><strong>Sucesso:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView = $flashMessages;
                        }
                    }
                }
            }//FIM IF ACAO==ALTERA
            $form->populate($data);
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /* Ordenação das paginas */
            $order = $this->_getParam('ordem', 'PAPL_ID_PAPEL');
            $direction = $this->_getParam('direcao', 'ASC');
            $order_aux = $order . ' ' . $direction;
            ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
            /* Ordenação */

            /* INICIO TEM UMA PESSOA DA LISTA SELECIONADA */
            if ($data['PUPE_CD_MATRICULA'] && $dados_lotacao != null && $verificaPessoa) {
                $select = $table->getPerfilUnidadePessoa($dados_lotacao['LOTA_SIGLA_SECAO'], $dados_lotacao['LOTA_COD_LOTACAO'], $data['PUPE_CD_MATRICULA']);
            }

            if ($select) {
                $form->populate($data);
                $paginator = Zend_Paginator::factory($select);
                $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(count($select));
                $this->view->ordem = $order;
                $this->view->direcao = $direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }
            /* FIM TEM UMA PESSOA DA LISTA SELECIONADA */
        }//FIM DA VERIFICACAO DE POST
        $this->view->form = $form;
    }

    public function ajaxunidadeAction()
    {
        $unidade = $this->_getParam('term', '');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxperfilAction()
    {
        $unidade = $this->_getParam('unidade', 'int');
        $secao = $this->_getParam('secao', 'alnum');
        $OCsTbPepePerfilPessoa = new Application_Model_DbTable_OCsTbPepePerfilPessoa();
        $perfil = $OCsTbPepePerfilPessoa->getPerfisAssociados($unidade);
        $this->view->perfil_array = $perfil;
    }

    public function ajaxpessoaAction()
    {
        $lota_cod_lotacao = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $OcsTbPepePerfilPessoa = new Application_Model_DbTable_OCsTbPepePerfilPessoa();
        $PapSistUnidPess_array = $OcsTbPepePerfilPessoa->getPessoa($lota_cod_lotacao);
        $this->view->PapSistUnidPess_array = $PapSistUnidPess_array;
    }

    public function alterarAction()
    {
        $this->view->title = "Perfil Pessoa";
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $data = $this->getRequest()->getPost();
        $form = new Guardiao_Form_UnidadePessoa();
        $table = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        $unidadepessoaalterar = new Zend_Session_Namespace('unidadepessoaalterarNs');
        $flag = FALSE;

        foreach ($data['papeis'] as $papeis):

            $papelArray1 = explode(" - ", $papeis[1]);
            $apsp_id_papel_sist_unid = $papelArray1[0];
            $codigo1 = $papelArray1[1];

            $papelArray2 = explode(" - ", $papeis[2]);
            $apsp_id_papel_sist_unid = $papelArray2[0];
            $codigo2 = $papelArray2[1];

            //alterações de DELETE
            if ($codigo1 == "" && $codigo2 == "associado") {
                $flag = TRUE;
                try {
                    //$select = $table->getDeletar($apsp_id_papel_sist_unid,$data['pmat_id_pessoa']);
                    $msg_to_user = "Papel Alterado com Sucesso";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                } catch (Zend_Exception $error_string) {
                    $msg_to_user = "Erro ao retirar papel";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                }

                //alterações de INSERT
            } else if ($codigo1 == "associar" && $codigo2 == "dissociado") {
                $flag = TRUE;
                try {
                    //$select = $table->getInserir($apsp_id_papel_sist_unid,$data['pmat_id_pessoa']);
                    $msg_to_user = "Papel Alterado com Sucesso";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                } catch (Zend_Exception $error_string) {
                    //$msg_to_user = "Erro ao retirar papel";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                }
            }
        endforeach;
        if ($flag != TRUE) {
            $msg_to_user = "Nenhum papel foi modificado";
            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'info'));
        }
        $unidadepessoaalterar->dataform = $data;
        $this->_helper->_redirector('form', 'perfilpessoa', 'guardiao');
    }

    public function ajaxperfilpessoaAction()
    {
        /**
         * Desabilita o Layout
         */
        if ($this->_helper->hasHelper('layout')) {
            $this->_helper->disableLayout();
        }

        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $ocsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $unidade = $this->_getParam('unidade');

        /**
         * Validar matricula
         */
        $array_matricula = explode(" - ", $this->_getParam('matricula'));
        $matricula = $array_matricula[0];
        $validacao = $ocsTbPmatMatricula->verificaMatricula($matricula);

        if ($validacao['VALOR'] == '0') {
            $this->view->matriculaValida = false;
            $flashMessages = "<div class='notice'><strong>Alerta:</strong> Não foram encontrados registros para o usuário informado. Favor fazer nova pesquisa.</div>";
            $this->view->flashMessagesView = $flashMessages;
        } else {
            /**
             * Tenta fazer o explode
             */
            try {
                $cod_lotacaoArray = explode("|", $unidade);
                $cod_lotacao = $cod_lotacaoArray[1];
                $sg_secao = $cod_lotacaoArray[0];
            } catch (Exception $e) {
                $cod_lotacao = "";
                $sg_secao = "";
            }

            /**
             * Perfis da unidade não associados ao usuário
             */
            $perfis_nao_associados = $ocsTbPupePerfilUnidPessoa->getPerfisPessoaNaoAssociados($sg_secao, $cod_lotacao, $matricula);
            /**
             * Perfis do Usuário na Unidade
             */
            $perfis_associados = $ocsTbPupePerfilUnidPessoa->getPerfisPessoaNaUnidade($sg_secao, $cod_lotacao, $matricula);

            /**
             * Joga os resultados na view
             */
            $this->view->matriculaValida = true;
            $this->view->perfis_associados = $perfis_associados;
            $this->view->perfis_nao_associados = $perfis_nao_associados;
        }
    }

    public function ajaxpessoasdaunidadeAction()
    {

        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        /**
         * Dados da Unidade
         */
        $array_unidade = explode("|", $this->_getParam('unidade'));
        $pessoas = $ocsTbPupePerfilUnidPessoa->getPessoasDaUnidade($array_unidade);
        $this->view->pessoasDaUnidade = $pessoas;
    }

    public function ajaxcaixaspessoaAction()
    {
        $aNamespace = new Zend_Session_Namespace('userNs');
        $matricula = $this->_getParam('PMAT_CD_MATRICULA');
        $caixa = $this->_getParam('caixa');
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidade($aNamespace->matricula);

        $this->view->caixa = $caixa;
        $this->view->unidades_array = $CaixasUnidadeAcesso;
    }

    public function ajaxperfilpessoaadmAction()
    {
        /**
         * Desabilita o Layout
         */
        if ($this->_helper->hasHelper('layout')) {
            $this->_helper->disableLayout();
        }

        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $ocsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $unidade = $this->_getParam('unidade');

        /**
         * Validar matricula
         */
        $array_matricula = explode(" - ", $this->_getParam('matricula'));
        $matricula = $array_matricula[0];
        $validacao = $ocsTbPmatMatricula->verificaMatricula($matricula);

        if ($validacao['VALOR'] == '0') {
            $this->view->matriculaValida = false;
            $flashMessages = "<div class='notice'><strong>Alerta:</strong> Não foram encontrados registros para o usuário informado. Favor fazer nova pesquisa.</div>";
            $this->view->flashMessagesView = $flashMessages;
        } else {
            /**
             * Tenta fazer o explode
             */
            try {
                $cod_lotacaoArray = explode("|", $unidade);
                $cod_lotacao = $cod_lotacaoArray[1];
                $sg_secao = $cod_lotacaoArray[0];
            } catch (Exception $e) {
                $cod_lotacao = "";
                $sg_secao = "";
            }

            /**
             * Perfis da unidade não associados ao usuário
             */
            $perfis_nao_associados = $ocsTbPupePerfilUnidPessoa->getPerfisPessoaNaoAssociados($sg_secao, $cod_lotacao, $matricula);
            /**
             * Perfis do Usuário na Unidade
             */
            $perfis_associados = $ocsTbPupePerfilUnidPessoa->getPerfisPessoaNaUnidade($sg_secao, $cod_lotacao, $matricula);

            /**
             * Joga os resultados na view
             */
            $this->view->matriculaValida = true;
            $this->view->perfis_associados = $perfis_associados;
            $this->view->perfis_nao_associados = $perfis_nao_associados;
        }
    }

}
