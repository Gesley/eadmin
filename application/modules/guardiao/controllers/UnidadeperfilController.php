<?php

class Guardiao_UnidadeperfilController extends Zend_Controller_Action {
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
        $this->view->titleBrowser = "e-Guardião";
    }

    public function indexAction() {
        $data = $this->getRequest()->getPost();
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order = $this->_getParam('ordem', 'UNPE_ID_UNIDADE_PERFIL');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order . ' ' . $direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /* Ordenação */


        $cod_lotacaoArray = explode(" - ", $data['UNPE_SG_SECAO']);
        $cod_lotacao = $cod_lotacaoArray[2];

        $table = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $select = $table->getPerfisAssociados($cod_lotacao);
        Zend_Debug::dump($select);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function formAction() {
        $this->view->title = "Perfil Unidade";
        $form = new Guardiao_Form_UnidadePerfil();
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $aNamespace = new Zend_Session_Namespace('userNs');
        $dadosUnidadePerfil = array();


        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getPost();

            /**
             * Formulário de associação submetido
             */
            if ($data['form_validator'] == 'form_validator') {

                /**
                 * Verifica se existe a Unidade
                 */
                if (isset($data['UNPE_SG_SECAO']) && $data['UNPE_SG_SECAO'] != "") {

                    /**
                     * Verifica se foi setado valor para os perfis
                     */
                    if (isset($data['perfis_unidade']) && $data['perfis_unidade'] != "") {
                        $array_perfis_post = $data['perfis_unidade'];
                    } else {
                        $array_perfis_post = null;
                    }
                    $unidade = explode("|", $data['UNPE_SG_SECAO']);
                    $dadosUnidadePerfil['UNPE_SG_SECAO'] = $unidade[0];
                    $dadosUnidadePerfil['UNPE_CD_LOTACAO'] = $unidade[1];

                    /**
                     * Verifica se a Unidade já possui perfis associados  
                     */
                    $resultado_perfis = $OcsTbUnpeUnidadePerfil->getPerfisAssociadosaUnidade($dadosUnidadePerfil);
                    if (!is_null($resultado_perfis)) {

                        /**
                         * TEM PERFIL ASSOCIADO NO BANCO
                         *
                         * Verifica se foi adicionado algum perril
                         */
                        if (!is_null($array_perfis_post)) {

                            /**
                             * Verificar quais perfis serão desvinculados e quais serão vinculados
                             */
                            $array_perfis_desvincular = array_diff($resultado_perfis, $array_perfis_post);
                            $array_perfis_vincular = array_diff($array_perfis_post, $resultado_perfis);

                            /**
                             * Verifica se houve alguma alteração
                             * Se não houve, ja retorna
                             */
                            if (count($array_perfis_desvincular) == 0 && count($array_perfis_vincular) == 0) {
                                $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Nenhuma alteração feita para a Unidade!</div>";
                            } else {
                                /**
                                 * Chamar funcao para fazer associação dos novos perfis
                                 * Chamar função para desvincular os perfis removidos
                                 */
                                if (count($array_perfis_vincular) != 0) {
                                    /**
                                     * Associar
                                     */
                                    $resultado_vinc = $OcsTbUnpeUnidadePerfil->associarPerfisaUnidade($array_perfis_vincular, $dadosUnidadePerfil, $aNamespace->matricula);
                                }
                                if (count($array_perfis_desvincular) != 0) {
                                    /**
                                     * Desassociar
                                     */
                                    $resultado_vinc = $OcsTbUnpeUnidadePerfil->desassociarPerfisDaUnidade($array_perfis_desvincular, $dadosUnidadePerfil, $aNamespace->matricula);
                                }

                                if ($resultado_vinc) {
                                    $flashMessagesView = "<div class='success'><strong>Sucesso:</strong>Perfis da Unidade atualizados com sucesso!</div>";
                                } else {
                                    $flashMessagesView = "<div class='error'><strong>Erro:</strong>Ocorreu um erro ao atualizar os perfis da Unidade! Verifique se existe alguma pessoa vinculada a este perfil na Unidade.</div>";
                                }
                            }
                        } else {
                            /*
                             * Se existem perfis associados e não foram passados perfis novos, então 
                             * será feita a desvinculação de todos
                             */
                            $resultado = $OcsTbUnpeUnidadePerfil->desassociarTodosPerfisDaUnidade($dadosUnidadePerfil, $aNamespace->matricula);
                            if ($resultado) {
                                $flashMessagesView = "<div class='success'><strong>Sucesso:</strong>Perfis desassociados com sucesso!</div>";
                            } else {
                                $flashMessagesView = "<div class='error'><strong>Erro:</strong>Ocorreu um erro ao desassociar os perfis da Unidade!</div>";
                            }
                        }
                    } else {
                        /**
                         * NÃO TEM PERFIL ASSOCIADO NO BANCO
                         *
                         * Verifica se foi adicionado algum perfil
                         */
                        if (!is_null($array_perfis_post)) {

                            /**
                             * chamar funcao para fazer associação dos novos perfis
                             */
                            $resultado = $OcsTbUnpeUnidadePerfil->associarPerfisaUnidade($array_perfis_post, $dadosUnidadePerfil, $aNamespace->matricula);
                            if ($resultado) {
                                $flashMessagesView = "<div class='success'><strong>Sucesso:</strong>Perfis associados com sucesso à Unidade!</div>";
                            } else {
                                $flashMessagesView = "<div class='error'><strong>Erro:</strong>Ocorreu um erro ao associar os perfis à unidade!</div>";
                            }
                        } else {
                            /**
                             * Mensagem caso a Unidade não tenha perfil e não foi feito nenhuma alteração
                             */
                            $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Nenhuma alteração foi realizada na Unidade!</div>";
                        }
                    }
                } else {
                    /**
                     * Mensagem caso a Unidade não tenha sido preenchida
                     */
                    $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Escolha uma Unidade para associar os perfis!</div>";
                }
            } else {
                $flashMessagesView = "<div class='notice'><strong>Alerta:</strong>Preencha os campos obrigatórios!</div>";
            }

            $form->populate($data);
            $this->view->secao = $data['TRF1_SECAO'];
            $this->view->subsecao = $data['SECAO_SUBSECAO'];
            $this->view->flashMessagesView = $flashMessagesView;
        }

        /**
         * Removendo a label do campo perfil
         */
        $form->UNPE_ID_PERFIL->removeDecorator('label');
        $this->view->form = $form;
    }

    public function delAction() {
        $this->view->title = "Perfil Unidade";
        $form = new Guardiao_Form_UnidadePerfil();
        $table = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $table_audit = new Application_Model_DbTable_OcsTbAuunUnidPerfilAudit();
        $aNamespace = new Zend_Session_Namespace('userNs');
        $papelalterar = new Zend_Session_Namespace('papelalterarNs');

        try {
//              $select = $table->getDeletar($id);
//              $msg_to_user = "Papel alterado com Sucesso";
//              $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
            /* AINDA NAO IMPLEMENTADO */
            $msg_to_user = "Não é possível excluir o perfil da unidade";
            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
        } catch (Zend_Exception $error_string) {
            $msg_to_user = "Não é possível excluir o perfil da unidade";
            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
        }
        $papelalterar = new Zend_Session_Namespace('papelalterarNs');
        $this->_helper->_redirector('form', 'unidadeperfil', 'guardiao');
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

    public function ajaxunidadebyiddestinatarioAction() {

        $idDestinatario = Zend_Filter::FilterStatic($this->_getParam('id'), 'alnum');
        $ocsPess = new Application_Model_DbTable_OcsTbPessPessoa();
        $dadosDestinatario = $ocsPess->getDadosDestinatario($idDestinatario);
        $this->view->Destinatario_array = $dadosDestinatario;
    }

    public function ajaxnomedestinatarioAction() {
        $nomeDestinatario = $this->_getParam('term', '');
        $OcsTbPessPessoa = new Application_Model_DbTable_OcsTbPessPessoa();
        $nome_array = $OcsTbPessPessoa->getNomeDestinatarioAjax($nomeDestinatario);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxperfilunidadeAction() {
        /**
         * Desabilita o Layout
         */
        if ($this->_helper->hasHelper('layout')) {
            $this->_helper->disableLayout();
        }

        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $unidade = $this->_getParam('unidade');

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
         * Faz a busca com os parametros
         */
        $perfis_associados = $OcsTbUnpeUnidadePerfil->getPerfisAssociados($cod_lotacao, $sg_secao);
        $perfis_nao_associados = $OcsTbUnpeUnidadePerfil->getPerfisNaoAssociados($cod_lotacao, $sg_secao);

        /**
         * Joga os resultados na view
         */
        $this->view->perfis_associados = $perfis_associados;
        $this->view->perfis_nao_associados = $perfis_nao_associados;
    }

}
