<?php

class Guardiao_PerfilpessoaadmController extends Zend_Controller_Action {
	/**
	 * Timer para mensuracao do tempo de carregamento da pagina
	 *
	 * @var int $_temporizador
	 */
	private $_temporizador;
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da pagina
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
    public function init() {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
        $this->view->titleBrowser = 'e-Guardião - Sistema de Gerenciamento de Permissões';
        $this->view->msgAjuda = AJUDA_AJUDA;
    }

    public function indexAction() {
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

    public function ajaxpessoastribunalAction() {
        $matriculanome = $this->_getParam('term', '');
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $nome_array = $OcsTbPmatMatricula->getNomeSolicitanteAjax($matriculanome);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxpessoassecaoAction() {
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

    public function ajaxpessoassecaoautosgAction() {
    	$matriculanome = $this->_getParam('term', '');
    	$sg = $_SESSION['userNs']['siglasecao'];
    	$OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
    	$nome_array = $OcsTbPmatMatricula->getNomeSolicitanteSecaoAjax($matriculanome, $sg);
    	$fim = count($nome_array);
    	for ($i = 0; $i < $fim; $i++) {
    		$nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
    	}
    	$this->_helper->json->sendJson($nome_array);
    }
    
    public function ajaxpessoasdaunidadeAction() {

        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        /**
         * Dados da Unidade
         */
        $array_unidade = explode("|", $this->_getParam('unidade'));
        $pessoas = $ocsTbPupePerfilUnidPessoa->getPessoasDaUnidade($array_unidade);
        $this->view->pessoasDaUnidade = $pessoas;
    }

    public function ajaxpessoasresponsaveiscaixaAction() {
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        /**
         * Dados da Unidade
         */
        $array_unidade = explode("|", $this->_getParam('unidade'));
        $pessoas = $OcsTbPmatMatricula->getPessoasResponsaveisCaixa($array_unidade);
        $this->view->pessoasDaUnidade = $pessoas;
    }

    public function formAction() {
        $this->view->title = "Perfil Pessoa ADM";
        $form = new Guardiao_Form_PerfilPessoaAdm();
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
                    if(isset($dataPost['SECAO_CD_MATRICULA']) && $dataPost['SECAO_CD_MATRICULA'] != ""){
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

    public function ajaxunidadebysecaoAction() {
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $lotacao = Zend_Filter::FilterStatic($this->_getParam('lotacao'), 'int');
        $tipolotacao = Zend_Filter::FilterStatic($this->_getParam('tipo'), 'int');
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $Lotacao_array = $RhCentralLotacao->getLotacaobySecao($secao, $lotacao, $tipolotacao);
        $this->view->Lotacao_array = $Lotacao_array;
    }

    public function ajaxsubsecoesAction() {
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $lotacao = Zend_Filter::FilterStatic($this->_getParam('lotacao'), 'int');
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao, $lotacao);
        $this->view->Lotacao_array = $Lotacao_array;
    }

    public function ajaxunidadeAction() {
        $unidade = $this->_getParam('term', '');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxpessoaAction() {
        $lota_cod_lotacao = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $sg_secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $OcsTbPepePerfilPessoa = new Application_Model_DbTable_OcsTbPepePerfilPessoa();
        $PapSistUnidPess_array = $OcsTbPepePerfilPessoa->getPessoa($lota_cod_lotacao, $sg_secao);
        $this->view->PapSistUnidPess_array = $PapSistUnidPess_array;
    }

    public function ajaxcaixaspessoaAction() {
        $matricula = Zend_Filter::FilterStatic($this->_getParam('PMAT_CD_MATRICULA'), 'alnum');
        $caixa = $this->_getParam('caixa');
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidadePessoal($matricula);

        $this->view->caixa = $caixa;
        $this->view->unidades_array = $CaixasUnidadeAcesso;
    }

    public function ajaxpessoaacessoAction() {
        $lota_cod_lotacao = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $sg_secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $pupe_cd_matricula = Zend_Filter::FilterStatic($this->_getParam('PUPE_CD_MATRICULA'), 'alnum');

        $ocsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $pessoasAcesso = $ocsTbUnpeUnidadePerfil->getPessoasComPerfilX(9, $sg_secao, $lota_cod_lotacao);
        $this->view->PapSistUnidPess_array = $pessoasAcesso;
        $this->view->pupe_cd_matricula = $pupe_cd_matricula;
    }

    public function caixaspessoasAction() {
        $this->view->title = "Permissões de acesso as Caixas por Matricula";
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Guardiao_Form_PerfilPessoaAdm();

        $table = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $ocsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $table_audit = new Application_Model_DbTable_OcsTbPupeAuditoria();

        $Ns_Perfilpessoaadm_form = new Zend_Session_Namespace('Ns_Perfilpessoaadm_form');
        $aNamespace = new Zend_Session_Namespace('userNs');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data_post_atual = $data;

            if ($data['acao'] == 'Alterar') {
                if ($data['PMAT_CD_MATRICULA']) {
                    $array_matricula = explode(' - ', $data['PMAT_CD_MATRICULA']);
                    $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
                    $rowPmatMatricula = $OcsTbPmatMatricula->fetchRow("PMAT_CD_MATRICULA = '$array_matricula[0]'");
                    if ($rowPmatMatricula) {
                        $data['PUPE_CD_MATRICULA'] = $array_matricula[0];
                    }
                }
                if ($Ns_Perfilpessoaadm_form->data_post_utimo_executado != $data) {

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
                        $Ns_Perfilpessoaadm_form->data_post_utimo_executado = $data_post_atual;

                        if (!$this->view->flashMessagesView) {

                            $msg_to_user = "Alterações Realizadas.";
                            $flashMessages = "<div class='success'><strong>Sucesso:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView = $flashMessages;
                        }
                    }
                }
            }

            $form->populate($data);
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /* Ordenação das paginas */
            $order = $this->_getParam('ordem', 'PERF_ID_PERFIL');
            $direction = $this->_getParam('direcao', 'ASC');
            $order_aux = $order . ' ' . $direction;
            ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
            /* Ordenação */

            //$data["LOTA_COD_LOTACAO"]="SG|COD"
            $array_cod_sec = explode("|", $data["LOTA_COD_LOTACAO"]);
            //$matricula = explode(" - ", $data['PUPE_CD_MATRICULA']);
            /** Comentado para funcionamento da Funcionalidade
             * 
            $PUPE_CD_MATRICULA = $form->PUPE_CD_MATRICULA;
            if ($data['GRUPOPESSOAS'] == 'pessoaacesso') {
                $pessoas = $ocsTbUnpeUnidadePerfil->getPessoasComPerfilX(9, $array_cod_sec[0], $array_cod_sec[1]);
                foreach ($pessoas as $pessoas_p):
                    $PUPE_CD_MATRICULA->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
                endforeach;
                ;
            }else {

                $pessoas = $table->getPessoa($array_cod_sec[1], $array_cod_sec[0]);
                foreach ($pessoas as $pessoas_p):
                    $PUPE_CD_MATRICULA->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
                endforeach;
                ;
            }
             * */

            if ($data['PUPE_CD_MATRICULA']) {
                $select = $table->getPerfilUnidadePessoa($array_cod_sec[0], $array_cod_sec[1], $data['PUPE_CD_MATRICULA']);
                if (!$select) {
                    $form->removeElement('Alterar');
                    $this->view->mensagem = "A unidade selecionada não possui perfis associados";
                }
            } else if ($data['PMAT_CD_MATRICULA']) {
                $array_matricula = explode(' - ', $data['PMAT_CD_MATRICULA']);
                $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
                $rowPmatMatricula = $OcsTbPmatMatricula->fetchRow("PMAT_CD_MATRICULA = '$array_matricula[0]'");
                if ($rowPmatMatricula) {
                    $select = $table->getPerfilUnidadePessoa($array_cod_sec[0], $array_cod_sec[1], $array_matricula[0]);
                }
            }

            if ($select) {
                $form->populate($data);
                $paginator = Zend_Paginator::factory($select);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(count($select));
                $this->view->caixa = $data['LOTA_COD_LOTACAO'];
                $this->view->ordem = $order;
                $this->view->direcao = $direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }
        }
        $this->view->form = $form;
    }

    public function ajaxperfilpessoaadmAction() {
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
