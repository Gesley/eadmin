<?php

class Sisad_DistribuicaoController extends Zend_Controller_Action {
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
        $this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
    }

    public function indexAction() {
        
    }

    public function orgaosAction() {
        
    }

    public function iniciadistribuicaoAction() {
        $form = new Sisad_Form_Distribuicao();
        $this->view->formDistribuir = $form;

        $sessionDadosDist = new Zend_Session_Namespace('postDistribuicao');
        if ($this->getRequest()->isPost() != $sessionDadosDist->dataPost) {
            //caso tenha dados na sessão quer dizer que foi efetuado uma validação na action de distribuir
            //e não foi validado com sucesso
            if ($sessionDadosDist->dataPost != null) {
                //como não tem post é necessário alimentar a variável para continuar o fluxo normal
                $dataPost = $sessionDadosDist->dataPost;
                //limpa os dados da sessão
                $sessionDadosDist->dataPost = null;
            } else {
                $dataPost = $this->getRequest()->getPost();
            }

            $cont = 0;
            $arrayProcesso = array();
            $rn_caixaUnidade = new Trf1_Sisad_Negocio_CaixaUnidade();
            $rn_processo = new Trf1_Sisad_Negocio_Processo();
            $verificaCaixa = true;
            foreach ($dataPost['documento'] as $value) {
                $arrayProcesso[$cont] = Zend_Json::decode($value);

                $arrayProcessoDados = $rn_processo->getProcessoPorIdDocumento($arrayProcesso[$cont]['DOCM_ID_DOCUMENTO']);
                $arrayProcesso[$cont]['PRDI_ID_PROCESSO_DIGITAL'] = $arrayProcessoDados['PRDI_ID_PROCESSO_DIGITAL'];
                $verificaCaixa = $rn_caixaUnidade->verificaDocumentoCaixaUnidade($arrayProcesso[$cont]['DOCM_ID_DOCUMENTO'], $arrayProcesso[$cont]['MODE_SG_SECAO_UNID_DESTINO'], $arrayProcesso[$cont]['MODE_CD_SECAO_UNID_DESTINO']);
                if ($verificaCaixa == false) {
                    $this->_helper->flashMessenger(array('status' => 'error', 'message' => 'Existem processos que não estão mais na Caixa da Unidade.'));
                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                }
                $cont++;
            }

            $this->view->data = $arrayProcesso;
            $this->view->title = "Processo para Distribuição Eletrônica/Comissão";
        } else {
            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
        }
    }

    public function impedimentoAction() {

        $post = $this->_request->getPost();
        $orgao = $post['IMDI_CD_COMISSAO'];
        $prdi_id_processo_digital = $post['PRDI_ID_PROCESSO_DIGITAL'];

        /* VERIFICANDO SE REALMENTE E UM ORGAO VALIDO PARA DESEMBARGADORES */
        if ($orgao == 1000 || $orgao == 2000 || $orgao == 3000) {
            
        } else {
            /* paginação */
            $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
            $sadTbImdiImpedeDistribui = new Application_Model_DbTable_SadTbImdiImpedeDistribui();
            
            $rowsImpedidos = $sadTbImdiImpedeDistribui->fetchAll("IMDI_CD_COMISSAO ='$orgao' AND IMDI_ID_PROCESSO_DIGITAL ='$prdi_id_processo_digital'");

            $rowsMembros = $sadTbCcpaContDistComissao->getMembros('PNAT_NO_PESSOA desc', $orgao);
            $dado = array();

            foreach ($rowsImpedidos as $array) {
                $dado[$array['IMDI_CD_MATRICULA_SERVIDOR']] = true;
            }

            $this->view->title = "CADASTRO DE DESEMBARGADORES FEDERAIS QUE PARTICIPAM DA DISTRIBUIÇÃO DE PROCESSOS ADMINISTRATIVOS";
            $this->view->impedidos = $dado;
            $this->view->data = $rowsMembros;
            $this->view->IMDI_CD_COMISSAO = $orgao;
            $this->view->PRDI_ID_PROCESSO_DIGITAL = $prdi_id_processo_digital;
        }
    }

    public function distribuirAction() {

        if ($this->getRequest()->isPost()) {
            $form = new Sisad_Form_Distribuicao();


            $dataPost = $this->getRequest()->getPost();
            $formValidado = false;
            if ($form->ORGJ_CD_ORGAO_JULGADOR->isValid($dataPost['ORGJ_CD_ORGAO_JULGADOR']) && $form->nome_orgao->isValid($dataPost['nome_orgao'])) {
                if (isset($dataPost['matricula_membro'])) {
                    $formValidado = $dataPost['matricula_membro'] != '';
                }else{
                    $formValidado = true;
                }
            }
            
            if ($formValidado) {
                $rn_distribuicao = new Trf1_Sisad_Negocio_Distribuicao();
                try {
                    $rn_distribuicao->processoDistribuicao($dataPost);
                    foreach ($rn_distribuicao->getSucesso() as $mensagem) {
                        $this->_helper->flashMessenger(array('status' => 'success', 'message' => $mensagem));
                    }
                    foreach ($rn_distribuicao->getErros() as $mensagem) {
                        $this->_helper->flashMessenger(array('status' => 'error', 'message' => $mensagem));
                    }
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('status' => 'notice', 'message' => 'Erro inexperado: ' . $e->getMessage()));
                }
            } else {
                $sessionDadosDist = new Zend_Session_Namespace('postDistribuicao');
                $sessionDadosDist->dataPost = $dataPost;
                $this->_helper->flashMessenger(array('status' => 'notice', 'message' => 'Preencha todos os campos.'));
                $this->_helper->_redirector('iniciadistribuicao', 'distribuicao', 'sisad');
            }
        }
        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
    }

    public function addmembroAction() {
        //Zend_Debug::dump($this->getRequest()->getPost());exit;
        $this->view->title = "Novo membro para o orgão";
        $form = new Sisad_Form_MembroDistProc();

        //incluir na distribuicao ja vem marcado para facilitar
        $form->setAttrib('id', 'formulario');
        $form->getElement('CCPA_IC_DISTRIBUICAO')->setValue('N');

        $form->getElement('Adicionar')->setAttrib('style', 'display: none;');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $this->view->orgao = $data['CCPA_CD_ORGAO_JULGADOR'];
            $form->getElement('CCPA_IC_ATIVO')->setValue($data['CCPA_IC_ATIVO']);
            //SE NAO FOR SUBMETIDO PELO BOTAO ENTAO BUSCAR OS DADOS PARA PREENCHER OS CAMPOS HIDDEN
            if ($data['PNAT_NO_PESSOA'] && $data['Adicionar'] != 'Adicionar') {

                $array_matricula = explode(' - ', $data['PNAT_NO_PESSOA']);
                $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
                $rowPmatMatricula = $OcsTbPmatMatricula->getDadosMatriculaSolicitante($array_matricula[0]);

                if ($rowPmatMatricula) {

                    $form->getElement('CCPA_CD_SERVIDOR')->setValue($array_matricula[0]);
                    $form->getElement('PNAT_NO_PESSOA')->setValue($data['PNAT_NO_PESSOA']);
                    $form->getElement('Adicionar')->setAttrib('style', 'display: block;');
                }
            }
            //SE CLICOU EM ADICIONAR ENTAO ADICIONE
            if ($data['Adicionar'] == 'Adicionar' && $form->isValid($data)) {

                if (!(strstr($data['CCPA_CD_SERVIDOR'], 'PS') || strstr($data['CCPA_CD_SERVIDOR'], 'ES'))) {
                    //efetuar cadastro no banco
                    try {
                        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $db->beginTransaction();

                        $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
                        $nomeMembro = $data['PNAT_NO_PESSOA'];
                        unset($data['PNAT_NO_PESSOA']);
                        unset($data['Adicionar']);
                        $data['CCPA_IC_ATIVO'] == '1' ? $data['CCPA_IC_ATIVO'] = 'S' : $data['CCPA_IC_ATIVO'] = 'N';
                        //$data['CCPA_IC_DISTRIBUICAO'] == '1' ? $data['CCPA_IC_DISTRIBUICAO'] = 'S' : $data['CCPA_IC_DISTRIBUICAO'] = 'N';

                        $rowAddMembros = $sadTbCcpaContDistComissao->createRow($data);
                        $rowAddMembros->save();

                        $db->commit();

                        $msg_to_user = "Membro $nomeMembro adcionado.";
                        $flashMessages = "<div class='success'><strong>Sucesso:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $flashMessages;
                    } catch (Zend_Exception $e) {
                        $db->rollBack();
                        $erro = $e->getMessage();
                        if (strpos($erro, 'ORA-00001') > 0) {
                            $msg_to_user = "Membro já cadastrado";
                        } else {
                            $msg_to_user = $erro;
                        }
                        $flashMessages = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $this->view->flashMessagesView . $flashMessages;
                    }
                } else {

                    $msg_to_user = "Não é permitido o cadastro de estagiário ou terceirizado.";
                    $flashMessages = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView = $this->view->flashMessagesView . $flashMessages;
                }
            }
            //pega o orgao que veio do post desde a tela dos membros

            $form->getElement('CCPA_CD_ORGAO_JULGADOR')->setValue($data['CCPA_CD_ORGAO_JULGADOR']);
            $this->view->form = $form;
        } else {
            //sempre que nao tiver post voltar para list
            $this->_helper->redirector('list');
        }
    }

    public function membrosservidoresAction() {
        $dataPost = $this->getRequest()->getPost();
        /* VERIFICA SE O ACESSO É VALIDO */

        $orgao = $this->_getParam('orgao');
        if ($orgao == 1000 || $orgao == 2000 || $orgao == 3000) {
            $this->_helper->redirector('list');
        }
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'PNAT_NO_PESSOA');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $direcaoSalvar = $order_direction;
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */
        $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
        $rows = $sadTbCcpaContDistComissao->getMembros($order, $this->_getParam('orgao'));

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        if ($dataPost['acao'] == 'Salvar') {

            try {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();

                foreach ($paginator as $arrayPaginator):
                    //lembrando que o post de checkbox sempre retorna os valores S
                    if ($dataPost[$arrayPaginator['PMAT_CD_MATRICULA']]) {
                        //$arrayAlteracao[$arrayPaginator['PMAT_CD_MATRICULA']] = 'S';
                        $arrayAlteracao['CCPA_IC_ATIVO'] = 'S';
                    } else {
                        //$arrayAlteracao[$arrayPaginator['PMAT_CD_MATRICULA']] = 'N';
                        $arrayAlteracao['CCPA_IC_ATIVO'] = 'N';
                    }
                    $rowDist = $sadTbCcpaContDistComissao->find($orgao, $arrayPaginator['PMAT_CD_MATRICULA'])->current();
                    //Zend_Debug::dump($rowDist);exit;
                    $rowDist->setFromArray($arrayAlteracao);
                    $rowDist->save();
                endforeach;
                $db->commit();

                $msg_to_user = "Alteração efetuada com sucesso.";
                $flashMessages = "<div class='success'><strong>Sucesso:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView = $flashMessages;
            } catch (Zend_Exception $e) {
                $db->rollBack();

                $erro = $e->getMessage();
                $flashMessages = "<div class='error'><strong>Erro:</strong> $erro</div>";
                $this->view->flashMessagesView = $this->view->flashMessagesView . $flashMessages;
            }
            //refaz a busca para atualizar os dados da lista
            $rows = $sadTbCcpaContDistComissao->getMembros($order, $this->_getParam('orgao'));
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(15);
        } // fim if ($dataPost['acao'] == 'Salvar')

        $this->view->title = "CADASTRO DE SERVIDORES QUE PARTICIPAM DA DISTRIBUIÇÃO DE PROCESSOS ADMINISTRATIVOS";
        $this->view->ordem = $order_column;
        $this->view->direcaoSalvar = $direcaoSalvar;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->orgao = $orgao;
        $this->view->page = $page;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function membrosdesembargadoresAction() {
        $dataPost = $this->getRequest()->getPost();
        /* VERIFICANDO SE REALMENTE E UM ORGAO VALIDO PARA DESEMBARGADORES */
        $orgao = $this->_getParam('orgao');
        if ($orgao == 1000 || $orgao == 2000 || $orgao == 3000) {
            /* paginação */
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

            /* Ordenação das paginas */
            $order_column = $this->_getParam('ordem', 'PMAT_CD_MATRICULA');
            $order_direction = $this->_getParam('direcao', 'ASC');
            $direcaoSalvar = $order_direction;
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /* Ordenação */

            $sadTbCdpaContDistProcAdm = new Application_Model_DbTable_SadTbCdpaContDistProcAdm();
            $rows = $sadTbCdpaContDistProcAdm->getMembros($order, $this->_getParam('orgao'));

            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(15);

            if ($dataPost['acao'] == 'Salvar') {
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(15);

                try {
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();

                    foreach ($paginator as $arrayPaginator):
                        //lembrando que o post de checkbox sempre retorna os valores S
                        if ($dataPost[$arrayPaginator['PMAT_CD_MATRICULA']]) {
                            //$arrayAlteracao[$arrayPaginator['PMAT_CD_MATRICULA']] = 'S';
                            $arrayAlteracao['CDPA_IC_ATIVO'] = 'S';
                        } else {
                            //$arrayAlteracao[$arrayPaginator['PMAT_CD_MATRICULA']] = 'N';
                            $arrayAlteracao['CDPA_IC_ATIVO'] = 'N';
                        }
                        if ($dataPost['promo-' . $arrayPaginator['PMAT_CD_MATRICULA']]) {
                            //$arrayAlteracao[$arrayPaginator['PMAT_CD_MATRICULA']] = 'S';
                            $arrayAlteracao['CDPA_IC_PLENARIO'] = 'S';
                        } else {
                            //$arrayAlteracao[$arrayPaginator['PMAT_CD_MATRICULA']] = 'N';
                            $arrayAlteracao['CDPA_IC_PLENARIO'] = 'N';
                        }

                        $rowDist = $sadTbCdpaContDistProcAdm->find($orgao, $arrayPaginator['PMAT_CD_MATRICULA'])->current();
                        $rowDist->setFromArray($arrayAlteracao);
                        $rowDist->save();
                    endforeach;
                    $db->commit();

                    $msg_to_user = "Alteração efetuada com sucesso.";
                    $flashMessages = "<div class='success'><strong>Sucesso:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView = $flashMessages;
                } catch (Zend_Exception $e) {
                    $db->rollBack();

                    $erro = $e->getMessage();
                    $flashMessages = "<div class='error'><strong>Erro:</strong> $erro</div>";
                    $this->view->flashMessagesView = $this->view->flashMessagesView . $flashMessages;
                }

                //refaz a busca para atualizar os dados da lista
                $rows = $sadTbCdpaContDistProcAdm->getMembros($order, $this->_getParam('orgao'));
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(15);
            }// fim if ($dataPost['acao'] == 'Salvar')
            else if ($dataPost['acao'] == 'Excluir') {
                //CDPA_IC_ATIVO
            }

            $this->view->title = "CADASTRO DE DESEMBARGADORES FEDERAIS QUE PARTICIPAM DA DISTRIBUIÇÃO DE PROCESSOS ADMINISTRATIVOS";
            $this->view->ordem = $order_column;
            $this->view->direcaoSalvar = $direcaoSalvar;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            $this->view->orgao = $orgao;
            $this->view->page = $page;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        } else {
            $this->_helper->redirector('list');
        }
    }

    public function listAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'ORGJ_NM_ORGAO_JULGADOR');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $direcaoSalvar = $order_direction;
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SadTbOrgjOrgaoJulgador();
        $rows = $dados->fetchAll(null, $order)->toArray();

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(10);

        $this->view->title = "CADASTRO DE ORGÃOS JULGADORES PARA PROCESSOS ADMINISTRATIVOS";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function editAction() {
        $this->view->title = 'ALTERAR ORGÃOS JULGADORES PARA PROCESSOS ADMINISTRATIVOS';
        $userNs = new Zend_Session_Namespace('userNs');
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Sisad_Form_OrgaoJulgador();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SadTbOrgjOrgaoJulgador();


//        $SadTbTpprAuditoria = new Application_Model_DbTable_SadTbTpprAuditoria();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('ORGJ_CD_ORGAO_JULGADOR = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $row = $table->find($data['ORGJ_CD_ORGAO_JULGADOR'])->current();
                $message = $data['ORGJ_NM_ORGAO_JULGADOR'];
                $old_data = $row->toArray();

//                $aux = $data['ORGJ_NM_ORGAO_JULGADOR'];
                $data['ORGJ_NM_ORGAO_JULGADOR'] = new Zend_Db_Expr("UPPER('" . $data['ORGJ_NM_ORGAO_JULGADOR'] . "')");
                $data['ORGJ_DS_ORGAO_JULGADOR'] = new Zend_Db_Expr("UPPER('" . $data['ORGJ_DS_ORGAO_JULGADOR'] . "')");
                $data['ORGJ_SG_ORGAO_JULGADOR'] = new Zend_Db_Expr("UPPER('" . $data['ORGJ_SG_ORGAO_JULGADOR'] . "')");

                $row->setFromArray($data);

//                $dataTpprAuditoria['TPPR_TS_OPERACAO']           = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");              
//                $dataTpprAuditoria['TPPR_IC_OPERACAO']           = 'A';              
//                $dataTpprAuditoria['TPPR_CD_MATRICULA_OPERACAO'] = $userNs->matricula;   
//                $dataTpprAuditoria['TPPR_CD_MAQUINA_OPERACAO']   = substr($_SERVER['REMOTE_ADDR'],0,50);   
//                $dataTpprAuditoria['TPPR_CD_USUARIO_SO']         = substr($_SERVER['HTTP_USER_AGENT'],0,50);       
//                $dataTpprAuditoria['OLD_TPPR_ID_TIPO_PROCESSO']  = $old_data['TPPR_ID_TIPO_PROCESSO'];
//                $dataTpprAuditoria['NEW_TPPR_ID_TIPO_PROCESSO']  = $data['TPPR_ID_TIPO_PROCESSO'];  
//                $dataTpprAuditoria['OLD_TPPR_DS_DESCRICAO_PROC'] = $old_data['TPPR_DS_DESCRICAO_PROCESSO'];
//                $dataTpprAuditoria['NEW_TPPR_DS_DESCRICAO_PROC'] = $aux;
//                $rowTpprAuditoria = $SadTbTpprAuditoria->createRow($dataTpprAuditoria);
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {
                    $row->save();
//                    $rowTpprAuditoria->save();
                    $db->commit();
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger(array('message' => "Não foi possível adicionar o orgão: $message!" . "<br><p>" . strip_tags($exc->getMessage()) . "<p>", 'status' => 'error'));
                    return $this->_helper->_redirector('list', 'distribuicao', 'sisad');
                }
                $this->_helper->flashMessenger(array('message' => "O orgão: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('list', 'distribuicao', 'sisad');
            }
        }
    }

    public function impedirAction() {

        $post = $this->_request->getPost();
        if ($post != null) {
            $imdi_cd_comissao = $post['IMDI_CD_COMISSAO'];
            $imdi_id_processo_digital = $post['IMDI_ID_PROCESSO_DIGITAL'];
            $sadTbImdiImpedeDistribui = new Application_Model_DbTable_SadTbImdiImpedeDistribui();
            try {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();

                $sadTbImdiImpedeDistribui->delete("IMDI_CD_COMISSAO='$imdi_cd_comissao' AND IMDI_ID_PROCESSO_DIGITAL='$imdi_id_processo_digital'");
                $data['IMDI_CD_COMISSAO'] = $imdi_cd_comissao;
                $data['IMDI_ID_PROCESSO_DIGITAL'] = $imdi_id_processo_digital;
                if ($post != null) {
                    $mensagem = 'Impedimentos RETIRADOS com sucesso';
                    $success = true;
                }

                unset($post['IMDI_CD_COMISSAO']);
                unset($post['IMDI_ID_PROCESSO_DIGITAL']);

                foreach ($post as $key => $value) {
                    $data['IMDI_CD_MATRICULA_SERVIDOR'] = $key;
                    $sadTbImdiImpedeDistribui->insert($data);
                }

                $db->commit();

                if (count($post) > 0) {
                    $mensagem = 'Impedimentos REALIZADOS com sucesso';
                    $success = true;
                } else {
                    $mensagem = 'Impedimentos RETIRADOS com sucesso';
                    $success = true;
                }
                $array = array(
                    'success' => $success,
                    'mensagem' => $mensagem
                );
                $this->_helper->json->sendJson($array);
            } catch (Zend_Exception $e) {
                $db->rollBack();

                $erro = $e->getMessage();
                $array = array(
                    'success' => false,
                    'mensagem' => $erro
                );
                $this->_helper->json->sendJson($array);
            }
        } else {

            $array = array(
                'success' => false,
                'mensagem' => 'É necessário que tenham pessoas cadastradas'
            );
            $this->_helper->json->sendJson($array);
        }
    }

    //lista de dados da ultima distribuiçao de um processo sem julgamento
    public function listdadosultimauistribuicaonaojulgadosAction() {
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TO_DATE(HDPA_TS_DISTRIBUICAO,\'DD/MM/YYYY hh24:mi:ss\')');
        if($order_column == 'HDPA_TS_DISTRIBUICAO'){
            $order_column = 'TO_DATE(HDPA_TS_DISTRIBUICAO,\'DD/MM/YYYY hh24:mi:ss\')';
        }
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction; //PEGA DIRECAO PARA COLOCAR NA QUERY
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */
        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $arraySadTbHdpaHistDistribuicao = $sadTbHdpaHistDistribuicao->dadosUltimaDistribuicaoProcesso('nao_julgados', $order);


        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $paginator = Zend_Paginator::factory($arraySadTbHdpaHistDistribuicao);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->title = "CADASTRAMENTO DE DADOS DO JULGAMENTO DO PROCESSO";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->orgao = $orgao;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function listdadosultimauistribuicaojulgadosAction() {
        /* Ordenação das paginas */

        $resposta = $this->_getParam('resposta', '');
        if ($resposta == 'sucesso') {
            $this->view->flashMessagesView = '<div class="success"><strong>Sucesso:</strong> Dados Alterados com Sucesso.</div>';
        }
        $order_column = $this->_getParam('ordem', 'TO_DATE(HDPA_DT_JULGAMENTO,\'DD/MM/YYYY hh24:mi:ss\')');
        if($order_column == 'HDPA_DT_JULGAMENTO'){
            $order_column = 'TO_DATE(HDPA_DT_JULGAMENTO,\'DD/MM/YYYY hh24:mi:ss\')';
        }elseif($order_column == 'HDPA_TS_DISTRIBUICAO'){
            $order_column = 'TO_DATE(HDPA_TS_DISTRIBUICAO,\'DD/MM/YYYY hh24:mi:ss\')';
        }elseif($order_column == 'HDPA_DT_PUBLIC_JULGAMENTO_DJ'){
            $order_column = 'TO_DATE(HDPA_DT_PUBLIC_JULGAMENTO_DJ,\'DD/MM/YYYY hh24:mi:ss\')';
        }elseif($order_column == 'HDPA_DT_PUBLIC_JULGAMENTO_BS'){
            $order_column = 'TO_DATE(HDPA_DT_PUBLIC_JULGAMENTO_BS,\'DD/MM/YYYY hh24:mi:ss\')';
        }
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction; //PEGA DIRECAO PARA COLOCAR NA QUERY
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */
        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $arraySadTbHdpaHistDistribuicao = $sadTbHdpaHistDistribuicao->dadosUltimaDistribuicaoProcesso('julgados', $order);

        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $paginator = Zend_Paginator::factory($arraySadTbHdpaHistDistribuicao);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->title = "DADOS DE JULGAMENTO PROCESSUAL";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->orgao = $orgao;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function formdadosjulgamentoAction() {
        $data = $this->_getAllParams();
        $tipoPagina = $data['tipoPagina'];
        unset($data['tipoPagina']);
        unset($data['module']);
        unset($data['controller']);
        unset($data['action']);

        $form = new Sisad_Form_DadosJulgamento();
        $form->setName('dadosJulgamento')
                ->setAttrib('id', 'dadosJulgamento');
        $form->removeElement('Salvar');

        if ($data) {
            $this->view->comentario = 'Confira os processos abaixo. Os dados que você preencher será incluidos nos processos selecionados.';
            if (isset($data['salvar'])) {

                if ($form->isValid($data)) {

                    $dataHistorico = $data;
                    unset($dataHistorico['processo']);
                    unset($dataHistorico['salvar']);
                    try {
                        $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $adapter->beginTransaction();

                        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
                        foreach ($data['processo'] as $distribuicaoJSon):
                            $dadosDistribuicao = Zend_Json::decode($distribuicaoJSon);
                            $arrayIdProcesso[] = $dadosDistribuicao['HDPA_CD_PROC_ADMINISTRATIVO'];
                            $rowHdpaHistDistribuicao = $sadTbHdpaHistDistribuicao->find($dadosDistribuicao['HDPA_ID_DISTRIBUICAO'])->current();
                            $arrayDados = $rowHdpaHistDistribuicao->toArray();

                            if ($arrayDados['HDPA_DT_JULGAMENTO'] == null) {

                                if ($arrayDados["HDPA_CD_ORGAO_JULGADOR"] == 1000 || $arrayDados["HDPA_CD_ORGAO_JULGADOR"] == 2000 || $arrayDados["HDPA_CD_ORGAO_JULGADOR"] == 3000) {
                                    $sadTbCdpaContDistProcAdm = new Application_Model_DbTable_SadTbCdpaContDistProcAdm();

                                    $rowSadTbCdpa = $sadTbCdpaContDistProcAdm
                                            ->find($arrayDados["HDPA_CD_ORGAO_JULGADOR"], $arrayDados["HDPA_CD_JUIZ"])
                                            ->current();

                                    $auxRowSadTbCdpa = $rowSadTbCdpa->toArray();
                                    if ($auxRowSadTbCdpa['CDPA_QT_DEVOLVIDO_JUIZ'] != 0 && $auxRowSadTbCdpa['CDPA_QT_DEVOLVIDO_JUIZ'] != null) {
                                        $auxRowSadTbCdpa['CDPA_QT_DEVOLVIDO_JUIZ'] = new Zend_Db_Expr("CDPA_QT_DEVOLVIDO_JUIZ - 1");
                                    }
                                    $auxRowSadTbCdpa['CDPA_QT_JULGADO_JUIZ'] = new Zend_Db_Expr("NVL(CDPA_QT_JULGADO_JUIZ,0) + 1");

                                    $rowSadTbCdpa->setFromArray($auxRowSadTbCdpa)->save();
                                } else {

                                    $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
                                    $rowSadTbCcpa = $sadTbCcpaContDistComissao
                                            ->find($arrayDados["HDPA_CD_ORGAO_JULGADOR"], $arrayDados["HDPA_CD_SERVIDOR"])
                                            ->current();
                                    $auxRowSadTbCcpa = $rowSadTbCcpa->toArray();

                                    if ($auxRowSadTbCcpa['CCPA_QT_DEVOLVIDO_SERVIDOR'] != 0 && $auxRowSadTbCcpa['CCPA_QT_DEVOLVIDO_SERVIDOR'] != null) {
                                        $auxRowSadTbCcpa['CCPA_QT_JULGADO_SERVIDOR'] = new Zend_Db_Expr("CCPA_QT_DEVOLVIDO_SERVIDOR - 1");
                                    }

                                    //$auxRowSadTbCdpa['CCPA_QT_JULGADO_SERVIDOR'] = new Zend_Db_Expr("NVL(CCPA_QT_JULGADO_SERVIDOR,0) + 1");

                                    $rowSadTbCcpa->setFromArray($auxRowSadTbCcpa)->save();
                                }
                            }
                            $rowHdpaHistDistribuicao->setFromArray($dataHistorico);
                            $rowHdpaHistDistribuicao->save();

                        endforeach;
                        $adapter->commit();
                        //$adapter->rollBack();
                        $array = array(
                            'arrayIdProcesso' => $arrayIdProcesso,
                            'success' => true
                        );
                        $this->_helper->json->sendJson($array);
                        //$adapter->rollBack();
                    } catch (Exception $exc) {
                        $adapter->rollBack();
                        $this->view->flashMessagesView = '<div class="error"><strong>Erro:</strong> ' . $exc->getMessage() . '</div>';
                    }
                    //$sadTbHdpaHistDistribuicao
                } else {
                    $form->populate($data);
                }
            }
            if ($tipoPagina && isset($data['processo'])) {
                $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();

                $dadosDistribuicao = Zend_Json::decode($data['processo'][0]);
                $rowHdpaHistDistribuicao = $sadTbHdpaHistDistribuicao->find($dadosDistribuicao['HDPA_ID_DISTRIBUICAO'])->current();
                $form->populate($rowHdpaHistDistribuicao->toArray());
                $this->view->comentario = '<div class="notice"><strong>Atenção:</strong> Os dados correspondem a Primeira Distribuição da Lista.
                    <br/>A alteração que você fizer valerá para todas as Distribuições Marcadas.</div>';
            }
            $this->view->form = $form;
            $this->view->arrayProcessos = $data['processo'];
        } else {
            $this->view->flashMessagesView = '<div class="notice"><strong>Erro:</strong> Você precisa marcar alguma distribuição.</div>';
        }
    }

    //############################### INICIO RELATORIOS
    public function paraosdesembargadoresfederaisAction() {
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('.', '/', $dataInicial);
        $dataFinal = str_replace('.', '/', $dataFinal);

        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $dadosRelatorio = $sadTbHdpaHistDistribuicao->paraOsDesembargadoresFederais($dataInicial, $dataFinal);

        if (count($dadosRelatorio) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Não existem dados para sua pesquisa', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formparaosdesembargadoresfederais/');
        } else {
            $zend_date = new Zend_date();

            $arrayRelatorio = array();
            foreach ($dadosRelatorio as $tupla):
                $zend_date->setDate($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $zend_date->setTime($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $tupla['HDPA_TS_DISTRIBUICAO'] = $zend_date->get(Zend_Date::DATES);
                $tupla['HDPA_TS_DISTRIBUICAO'] .= ' ' . $zend_date->get(Zend_Date::TIMES);

                $dia = $zend_date->get(Zend_Date::DAY);
                $mes = $zend_date->get(Zend_Date::MONTH_NAME);
                $ano = $zend_date->get(Zend_Date::YEAR);
                $dataExtenso = "$dia de $mes do ano de $ano";

                switch ($tupla['HDPA_IC_FORMA_DISTRIBUICAO']):
                    case 'DA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'RA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª REDISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'DM':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO MANUAL';
                        break;
                    default :
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = 'NÃO IDENTIFICADA';
                        break;
                endswitch;
                if ($arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] == null) {
                    $arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array();
                }
                $arrayTupla[0] = $tupla;
                $arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array_merge($arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']], $arrayTupla);

            endforeach;

            $this->_helper->layout->disableLayout();
            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            60, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            //############### INICIO DA LOGICA
            //banco retorna array[dataDoDia][orgao] as data
            //array[dataDoDia][orgao] tem arrayDistribuicoes la dentro
            //INICIO foreach array NO CASO RODA NOS DIAS
            //
            $data = new Zend_date();
            $hora = $data->get(Zend_Date::HOUR);
            $minuto = $data->get(Zend_Date::MINUTE);
            $segundo = $data->get(Zend_Date::SECOND);
            $dia = $data->get(Zend_Date::DAY);
            $mes = $data->get(Zend_Date::MONTH);
            $ano = $data->get(Zend_Date::YEAR);
            foreach ($arrayRelatorio as $dataDistribuicao => $arrayOrgao):
                $this->view->arrayOrgao = $arrayOrgao;
                $this->render();
                $response = $this->getResponse();
                $body = $response->getBody();
                $response->clearBody();

                $cabecalho = "
                <table id='header'>
                        <tr valign='top'>
                            <td class='brasao'>
                            </td>
                            <td class='date-time'>
                                $dia/$mes/$ano $hora:$minuto:$segundo
                            </td>
                        </tr>
                    </table>
                    <br/>
                    <div class='titulo-rel'><b>ATA DE DISTRIBUIÇÃO DE PROCESSOS ADMINISTRATIVOS</b></div>
                    <br/>
                    <div id='texto'>

                        Aos $dataDistribuicao foi(ram) distribuído(s) eletronicamente, na forma estabelecida pelo 
                        &#167; 2º do art. 405 do Regimento Interno do Tribunal Regional Federal da 1ª Região, o(s) Processo(s)
                        Administrativo(s) abaixo relacionado(s):

                    </div>
                    ";

                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;

                $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
                $mpdf->SetHTMLHeader($cabecalho);
                $mpdf->AddPage();
                $mpdf->WriteHTML($body);
                //$mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');
                $mpdf->SetHTMLHeader($cabecalho);

            //FIM foreach array

            endforeach;

            $mpdf->Output();
        }
    }

    public function formparaosdesembargadoresfederaisAction() {
        $data = $this->_getAllParams();
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $form = new Sisad_Form_DataAData();
        $this->view->form = $form;

        if ($data['Buscar']) {
            if ($form->isValid($data)) {
                $dataInicial = str_replace('/', '.', $dataInicial);
                $dataFinal = str_replace('/', '.', $dataFinal);
                return $this->_redirect('/sisad/distribuicao/paraosdesembargadoresfederais/data_inicial/' . $dataInicial . '/data_final/' . $dataFinal);
            } else {
                $form->populate($data);
            }
        }
        $this->view->title = "Para os Desembargadores Federais";
    }

    public function geralprocessosdistribuidosporperiodoAction() {
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('.', '/', $dataInicial);
        $dataFinal = str_replace('.', '/', $dataFinal);


        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $dadosRelatorio = $sadTbHdpaHistDistribuicao->relatorioProcDistribuidosPorPeriodo($dataInicial, $dataFinal);
        if (count($dadosRelatorio) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Não existem dados para sua pesquisa', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formgeralprocessosdistribuidosporperiodo/');
        } else {
            $zend_date = new Zend_date();

            $arrayRelatorio = array();
            foreach ($dadosRelatorio as $tupla):
                $zend_date->setDate($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $zend_date->setTime($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $tupla['HDPA_TS_DISTRIBUICAO'] = $zend_date->get(Zend_Date::DATES);
                $tupla['HDPA_TS_DISTRIBUICAO'] .= ' ' . $zend_date->get(Zend_Date::TIMES);

                //            $dia = $zend_date->get(Zend_Date::DAY);
                //            $mes = $zend_date->get(Zend_Date::MONTH_NAME);
                //            $ano = $zend_date->get(Zend_Date::YEAR);


                switch ($tupla['HDPA_IC_FORMA_DISTRIBUICAO']):
                    case 'DA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'RA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª REDISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'DM':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO MANUAL';
                        break;
                    default :
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = 'NÃO IDENTIFICADA';
                        break;
                endswitch;

                if ($arrayRelatorio[$tupla['ORGJ_NM_ORGAO_JULGADOR']] == null) {
                    $arrayRelatorio[$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array();
                }
                $arrayTupla[0] = $tupla;
                $arrayRelatorio[$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array_merge($arrayRelatorio[$tupla['ORGJ_NM_ORGAO_JULGADOR']], $arrayTupla);

            endforeach;

            $this->_helper->layout->disableLayout();
            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            52, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            //############### INICIO DA LOGICA
            //banco retorna array[dataDoDia][orgao] as data
            //array[dataDoDia][orgao] tem arrayDistribuicoes la dentro
            //INICIO foreach array NO CASO RODA NOS DIAS
            //$this->view->dados;

            $data = new Zend_date();
            $hora = $data->get(Zend_Date::HOUR);
            $minuto = $data->get(Zend_Date::MINUTE);
            $segundo = $data->get(Zend_Date::SECOND);
            $dia = $data->get(Zend_Date::DAY);
            $mes = $data->get(Zend_Date::MONTH);
            $ano = $data->get(Zend_Date::YEAR);

            if ($dataInicial <> 'null' && $dataFinal <> 'null') {
                $complemento = "$dataInicial à $dataFinal";
            } else {
                if ($dataInicial <> 'null') {
                    $complemento = "Distribuições após $dataInicial";
                } else {
                    if ($dataFinal <> 'null') {
                        $complemento = "Distribuições até $dataFinal";
                    }
                }
            }
            $soma = 0;
            end($arrayRelatorio);
            $ultimaKeyArrayRelatorio = key($arrayRelatorio);

            foreach ($arrayRelatorio as $orgaoJulgador => $arrayDistribuicoes):
                $soma+= count($arrayDistribuicoes);
                $this->view->arrayDistribuicoes = $arrayDistribuicoes;
                $this->render();
                $response = $this->getResponse();
                $body[$orgaoJulgador] = $response->getBody();
                $response->clearBody();

                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;
                $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list



                $cabecalho = "
                        <table id='header'>
                            <tr valign='top'>
                                <td class='brasao'>
                                </td>
                                <td class='date-time'>
                                    $dia/$mes/$ano $hora:$minuto:$segundo
                                </td>
                            </tr>
                        </table>
                        <div class='alinha-centro'>PROCESSO QUE FORAM DISTRIBUÍDOS / REDISTRIBUIDOS NO PERÍODO
                                <br/>$complemento
                        </div>
                        <br/>
                        <div class='titulo-tabela'>
                            <b>ÓRGÃO JULGADOR: $orgaoJulgador</b>
                        </div>
                    ";
                $mpdf->SetHTMLHeader($cabecalho);


                $mpdf->AddPage();
                $mpdf->WriteHTML($body[$orgaoJulgador]);

            //$mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');


            endforeach;

            $mpdf->WriteHTML("<div class='rodape-total'><b >TOTAL DE PROCESSOS DISTRIBUÍDOS ELETRONICAMENTE NESTE PERÍODO: $soma</b></div>");
            $mpdf->Output();
        }
    }

    public function formgeralprocessosdistribuidosporperiodoAction() {
        $data = $this->_getAllParams();
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');

        $dataInicial = str_replace('/', '.', $dataInicial);
        $dataFinal = str_replace('/', '.', $dataFinal);

        $form = new Sisad_Form_DataAData();
        $this->view->form = $form;

        if ($data['Buscar']) {
            if ($form->isValid($data)) {
                return $this->_redirect('/sisad/distribuicao/geralprocessosdistribuidosporperiodo/data_inicial/' . $dataInicial . '/data_final/' . $dataFinal);
            } else {
                $form->populate($data);
            }
        }
        $this->view->title = "Geral - Processos Distribuidos por período";
    }

    public function paraconselhoscomissoesAction() {
        $this->_helper->layout->disableLayout();

        $orgao = $this->_getParam('orgao', 'null');
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('.', '/', $dataInicial);
        $dataFinal = str_replace('.', '/', $dataFinal);

        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $dadosRelatorio = $sadTbHdpaHistDistribuicao->relatorioConselhosComissoes($orgao, $dataInicial, $dataFinal);
        if (count($dadosRelatorio) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Não existem dados para sua pesquisa', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formparaconselhoscomissoes/');
        } else {
            $zend_date = new Zend_date();
            $arrayRelatorio = array();
            foreach ($dadosRelatorio as $tupla):
                $zend_date->setDate($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $zend_date->setTime($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $tupla['HDPA_TS_DISTRIBUICAO'] = $zend_date->get(Zend_Date::DATES);
                $tupla['HDPA_TS_DISTRIBUICAO'] .= ' ' . $zend_date->get(Zend_Date::TIMES);

                $dia = $zend_date->get(Zend_Date::DAY);
                $mes = $zend_date->get(Zend_Date::MONTH_NAME);
                $ano = $zend_date->get(Zend_Date::YEAR);
                $dataExtenso = "$dia de $mes do ano de $ano";

                switch ($tupla['HDPA_IC_FORMA_DISTRIBUICAO']):
                    case 'DA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'RA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª REDISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'DM':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO MANUAL';
                        break;
                    default :
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = 'NÃO IDENTIFICADA';
                        break;
                endswitch;
                if ($arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] == null) {
                    $arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array();
                }
                $arrayTupla[0] = $tupla;
                $arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array_merge($arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']], $arrayTupla);

            endforeach;
            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            60, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            $data = new Zend_date();
            $hora = $data->get(Zend_Date::HOUR);
            $minuto = $data->get(Zend_Date::MINUTE);
            $segundo = $data->get(Zend_Date::SECOND);
            $dia = $data->get(Zend_Date::DAY);
            $mes = $data->get(Zend_Date::MONTH);
            $ano = $data->get(Zend_Date::YEAR);
            foreach ($arrayRelatorio as $dataDistribuicao => $arrayOrgaos):
                $this->view->arrayOrgaos = $arrayOrgaos;
                $data = new Zend_date($data, 'dd/MM/yyyy');

                $mes = $data->get(Zend_Date::MONTH_NAME);
                $this->render();
                $response = $this->getResponse();
                $body[$dataDistribuicao] = $response->getBody();
                $response->clearBody();

                $cabecalho = "
                <table id='header'>
                    <tr valign='top'>

                        <td class='brasao'>
                        </td>
                        <td class='date-time'>
                            $dia/$mes/$ano $hora:$minuto:$segundo
                        </td>
                    </tr>
                </table>

                <div class='alinha-centro'><b>ATA DE DISTRIBUIÇÃO DE PROCESSOS ADMINISTRATIVOS</b></div>
                <br/>
                <div id='texto'>

                    Aos $dataDistribuicao foi(ram) distribuído(s) eletronicamente, na forma estabelecida pelo 
                    &#167; <!-- simbolo do paragrafo--> 2º do art. 400 do Regimento Interno do Tribunal Regional Federal da 1ª Região, o(s) Processo(s)
                    Administrativo(s) abaixo relacionado(s):

                </div>
                ";
                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;
                $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
                $mpdf->SetHTMLHeader($cabecalho);
                $mpdf->AddPage();
                //caso o relatorio passe de uma pagina por dia o cabeçalho sera outro
                $cabecalho = "
                <table id='header'>
                    <tr valign='top'>

                        <td class='brasao'>
                        </td>
                        <td class='date-time'>
                            $dia/$mes/$ano $hora:$minuto:$segundo
                        </td>
                    </tr>
                </table>
                ";
                $mpdf->SetHTMLHeader($cabecalho);
                $mpdf->WriteHTML($body[$dataDistribuicao]);
            //$mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');
            endforeach;
            //FIM foreach array
            $mpdf->Output();
        }
    }

    public function formparaconselhoscomissoesAction() {

        $data = $this->_getAllParams();

        $orgao = $this->_getParam('orgao', 'null');
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');

        $dataInicial = str_replace('/', '.', $dataInicial);
        $dataFinal = str_replace('/', '.', $dataFinal);

        $form = new Sisad_Form_DataADataOrgao();
        $sadTbOrgjOrgaoJulgador = new Application_Model_DbTable_SadTbOrgjOrgaoJulgador();
        $fetchOrgaosJulgadores = $sadTbOrgjOrgaoJulgador->getOrgaosJulgadores(1);

        foreach ($fetchOrgaosJulgadores as $rowOrgaoJulgador):
            $form->orgao->addMultiOption($rowOrgaoJulgador['ORGJ_CD_ORGAO_JULGADOR'], $rowOrgaoJulgador['ORGJ_NM_ORGAO_JULGADOR']);
        endforeach;
        $this->view->form = $form;

        if ($data['Buscar']) {
            if ($form->isValid($data)) {
                return $this->_redirect('/sisad/distribuicao/paraconselhoscomissoes/data_inicial/' . $dataInicial . '/data_final/' . $dataFinal . '/orgao/' . $orgao);
            } else {
                $form->populate($data);
            }
        }
        $this->view->title = "Para Conselhos/ Comissões";
    }

    public function redistribuicaoeletronicaAction() {

        $orgao = $this->_getParam('orgao', 'null');
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('.', '/', $dataInicial);
        $dataFinal = str_replace('.', '/', $dataFinal);
        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $dadosRelatorio = $sadTbHdpaHistDistribuicao->relatorioRedistribuicao($orgao, $dataInicial, $dataFinal);
        if (count($dadosRelatorio) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Não existem dados para sua pesquisa', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formredistribuicaoeletronica/');
        } else {
            $zend_date = new Zend_date();

            $arrayRelatorio = array();
            foreach ($dadosRelatorio as $tupla):
                $zend_date->setDate($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $zend_date->setTime($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $tupla['HDPA_TS_DISTRIBUICAO'] = $zend_date->get(Zend_Date::DATES);
                $tupla['HDPA_TS_DISTRIBUICAO'] .= ' ' . $zend_date->get(Zend_Date::TIMES);

                $dia = $zend_date->get(Zend_Date::DAY);
                $mes = $zend_date->get(Zend_Date::MONTH_NAME);
                $ano = $zend_date->get(Zend_Date::YEAR);
                $dataExtenso = "$dia de $mes do ano de $ano";

                switch ($tupla['HDPA_IC_FORMA_DISTRIBUICAO']):
                    case 'DA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'RA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª REDISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'DM':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO MANUAL';
                        break;
                    default :
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = 'NÃO IDENTIFICADA';
                        break;
                endswitch;
                if ($arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] == null) {
                    $arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array();
                }
                $arrayTupla[0] = $tupla;
                $arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array_merge($arrayRelatorio[$dataExtenso][$tupla['ORGJ_NM_ORGAO_JULGADOR']], $arrayTupla);

            endforeach;
            $this->_helper->layout->disableLayout();
            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            60, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            //############### INICIO DA LOGICA
            //banco retorna array[dataDoDia][orgao] as data
            //array[dataDoDia][orgao] tem arrayDistribuicoes la dentro
            //INICIO foreach array NO CASO RODA NOS DIAS
            //
            $data = new Zend_date();
            $hora = $data->get(Zend_Date::HOUR);
            $minuto = $data->get(Zend_Date::MINUTE);
            $segundo = $data->get(Zend_Date::SECOND);
            $dia = $data->get(Zend_Date::DAY);
            $mes = $data->get(Zend_Date::MONTH);
            $ano = $data->get(Zend_Date::YEAR);
            foreach ($arrayRelatorio as $dataDistribuicao => $arrayOrgao):
                $this->view->arrayOrgao = $arrayOrgao;
                $this->render();
                $response = $this->getResponse();
                $body[$dataDistribuicao] = $response->getBody();
                $response->clearBody();

                $cabecalho = "
                <table id='header'>
                    <tr valign='top'>

                        <td class='brasao'>
                        </td>
                        <td class='date-time'>
                            $dia/$mes/$ano $hora:$minuto:$segundo
                        </td>
                    </tr>
                </table>

                <div class='alinha-centro'><b>ATA DE REDISTRIBUIÇÃO DE PROCESSOS ADMINISTRATIVOS</b></div>
                <br/>
                <div id='texto'>

                    Aos $dataDistribuicao foi(ram) redistribuído(s) eletronicamente, na forma estabelecida pelo 
                    &#167; <!-- simbolo do paragrafo--> 2º do art. 400 do Regimento Interno do Tribunal Regional Federal da 1ª Região, o(s) Processo(s)
                    Administrativo(s) abaixo relacionado(s):

                </div><!-- Fim da div texto-->
                ";

                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;

                $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
                $mpdf->SetHTMLHeader($cabecalho);
                $mpdf->AddPage();
                $mpdf->WriteHTML($body[$dataDistribuicao]);
                //$mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');
                $mpdf->SetHTMLHeader($cabecalho);

            //FIM foreach array

            endforeach;

            $mpdf->Output();
        }
    }

    public function formredistribuicaoeletronicaAction() {
        $data = $this->_getAllParams();
        $orgao = $this->_getParam('orgao', 'null');
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('/', '.', $dataInicial);
        $dataFinal = str_replace('/', '.', $dataFinal);

        $form = new Sisad_Form_DataADataOrgao();
        $sadTbOrgjOrgaoJulgador = new Application_Model_DbTable_SadTbOrgjOrgaoJulgador();
        $fetchOrgaosJulgadores = $sadTbOrgjOrgaoJulgador->getOrgaosJulgadores();

        foreach ($fetchOrgaosJulgadores as $rowOrgaoJulgador):
            $form->orgao->addMultiOption($rowOrgaoJulgador['ORGJ_CD_ORGAO_JULGADOR'], $rowOrgaoJulgador['ORGJ_NM_ORGAO_JULGADOR']);
        endforeach;
        $this->view->form = $form;

        if ($data['Buscar']) {
            if ($form->isValid($data)) {
                return $this->_redirect('/sisad/distribuicao/redistribuicaoeletronica/data_inicial/' . $dataInicial . '/data_final/' . $dataFinal . '/orgao/' . $orgao);
            } else {
                $form->populate($data);
            }
        }
        $this->view->title = "Redistribuição Eletronica";
    }

    public function pordesembargadorAction() {
        $this->_helper->layout->disableLayout();

        $desem_federal = $this->_getParam('desem_federal', 'null');
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('.', '/', $dataInicial);
        $dataFinal = str_replace('.', '/', $dataFinal);


        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $dadosRelatorio = $sadTbHdpaHistDistribuicao->relatorioPorDesembargador($desem_federal, $dataInicial, $dataFinal);
        if (count($dadosRelatorio) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Não existem dados para sua pesquisa', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formpordesembargador/');
        } else {

            $zend_date = new Zend_date();
            $arrayRelatorio = array();
            foreach ($dadosRelatorio as $tupla):
                $zend_date->setDate($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $zend_date->setTime($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $tupla['HDPA_TS_DISTRIBUICAO'] = $zend_date->get(Zend_Date::DATES);
                $tupla['HDPA_TS_DISTRIBUICAO'] .= ' ' . $zend_date->get(Zend_Date::TIMES);

                switch ($tupla['HDPA_IC_FORMA_DISTRIBUICAO']):
                    case 'DA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'RA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª REDISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'DM':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO MANUAL';
                        break;
                    default :
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = 'NÃO IDENTIFICADA';
                        break;
                endswitch;
                if ($arrayRelatorio[$tupla['PMAT_CD_MATRICULA']][$tupla['ORGJ_NM_ORGAO_JULGADOR']] == null) {
                    $arrayRelatorio[$tupla['PMAT_CD_MATRICULA']][$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array();
                }
                $arrayTupla[0] = $tupla;
                $arrayRelatorio[$tupla['PMAT_CD_MATRICULA']][$tupla['ORGJ_NM_ORGAO_JULGADOR']] = array_merge($arrayRelatorio[$tupla['PMAT_CD_MATRICULA']][$tupla['ORGJ_NM_ORGAO_JULGADOR']], $arrayTupla);

            endforeach;
            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            25, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            //############### INICIO DA LOGICA
            //banco retorna array[dataDoDia][orgao] as data
            //array[dataDoDia][orgao] tem arrayDistribuicoes la dentro
            //INICIO foreach array NO CASO RODA NOS DIAS
            //$this->view->dados;
            $data = new Zend_date();
            $hora = $data->get(Zend_Date::HOUR);
            $minuto = $data->get(Zend_Date::MINUTE);
            $segundo = $data->get(Zend_Date::SECOND);
            $dia = $data->get(Zend_Date::DAY);
            $mes = $data->get(Zend_Date::MONTH);
            $ano = $data->get(Zend_Date::YEAR);
            foreach ($arrayRelatorio as $matDesembargador => $arrayOrgao):
                $this->view->arrayOrgao = $arrayOrgao;
                $auxRelator = $arrayOrgao[key($arrayOrgao)];
                $relator = $auxRelator[0]['PNAT_NO_PESSOA'];
                $this->view->relator = $relator;

                $this->render();
                $response = $this->getResponse();
                $body[$matDesembargador] = $response->getBody();
                $response->clearBody();

                $cabecalho = "
                <table id='header'>
                    <tr valign='top'>
                        <td class='brasao'>
                        </td>
                        <td class='date-time'>
                            $dia/$mes/$ano $hora:$minuto:$segundo
                        </td>
                    </tr>
                </table>
                ";
                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;
                $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
                $mpdf->SetHTMLHeader($cabecalho);
                $mpdf->AddPage();
                $mpdf->WriteHTML($body[$matDesembargador]);
            //$mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');
            endforeach;

            //FIM foreach array
            $mpdf->Output();
        }
    }

    public function formpordesembargadorAction() {
        $data = $this->_getAllParams();
        $relator = $this->_getParam('desem_federal', 'null');
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $form = new Sisad_Form_DataADataDesembargador();
        $this->view->form = $form;

        if ($data['Buscar']) {
            if ($form->isValid($data)) {
                return $this->_redirect('/sisad/distribuicao/pordesembargador/datainicial/' . $dataInicial . '/datafinal/' . $dataFinal . '/desem_federal/' . $relator);
            } else {
                $form->populate($data);
            }
        }
        $this->view->title = "Por Desembargador";
    }

    public function pordesembargadoreassuntoAction() {
        $this->_helper->layout->disableLayout();
        $desem_federal = $this->_getParam('desem_federal', 'null');
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('.', '/', $dataInicial);
        $dataFinal = str_replace('.', '/', $dataFinal);

        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $dadosRelatorio = $sadTbHdpaHistDistribuicao->relatorioPorDesembargadorAssunto($desem_federal, $dataInicial, $dataFinal);
        if (count($dadosRelatorio) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Não existem dados para sua pesquisa', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formpordesembargadoreassunto/');
        } else {

            $zend_date = new Zend_date();
            $arrayRelatorio = array();
            foreach ($dadosRelatorio as $tupla):
                $zend_date->setDate($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $zend_date->setTime($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $tupla['HDPA_TS_DISTRIBUICAO'] = $zend_date->get(Zend_Date::DATES);
                $tupla['HDPA_TS_DISTRIBUICAO'] .= ' ' . $zend_date->get(Zend_Date::TIMES);

                switch ($tupla['HDPA_IC_FORMA_DISTRIBUICAO']):
                    case 'DA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'RA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª REDISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'DM':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO MANUAL';
                        break;
                    default :
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = 'NÃO IDENTIFICADA';
                        break;
                endswitch;
                if ($arrayRelatorio[$tupla['PMAT_CD_MATRICULA']][$tupla['ASSUNTO']] == null) {
                    $arrayRelatorio[$tupla['PMAT_CD_MATRICULA']][$tupla['ASSUNTO']] = array();
                }
                $arrayTupla[0] = $tupla;
                $arrayRelatorio[$tupla['PMAT_CD_MATRICULA']][$tupla['ASSUNTO']] = array_merge($arrayRelatorio[$tupla['PMAT_CD_MATRICULA']][$tupla['ASSUNTO']], $arrayTupla);

            endforeach;

            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            25, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            //############### INICIO DA LOGICA
            //banco retorna array[dataDoDia][orgao] as data
            //array[dataDoDia][orgao] tem arrayDistribuicoes la dentro
            //INICIO foreach array NO CASO RODA NOS DIAS
            //$this->view->dados;
            $data = new Zend_date();
            $hora = $data->get(Zend_Date::HOUR);
            $minuto = $data->get(Zend_Date::MINUTE);
            $segundo = $data->get(Zend_Date::SECOND);
            $dia = $data->get(Zend_Date::DAY);
            $mes = $data->get(Zend_Date::MONTH);
            $ano = $data->get(Zend_Date::YEAR);
            foreach ($arrayRelatorio as $matDesembargador => $arrayAssunto):
                $this->view->arrayAssunto = $arrayAssunto;
                $auxRelator = $arrayAssunto[key($arrayAssunto)];
                $relator = $auxRelator[0]['PNAT_NO_PESSOA'];
                $this->view->relator = $relator;

                $this->render();
                $response = $this->getResponse();
                $body[$matDesembargador] = $response->getBody();
                $response->clearBody();

                $cabecalho = "
                <table id='header'>
                    <tr valign='top'>
                        <td class='brasao'>
                        </td>
                        <td class='date-time'>
                            $dia/$mes/$ano $hora:$minuto:$segundo
                        </td>
                    </tr>
                </table>

                ";
                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;
                $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
                $mpdf->SetHTMLHeader($cabecalho);
                $mpdf->AddPage();
                $mpdf->WriteHTML($body[$matDesembargador]);
            //$mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');
            endforeach;
            //FIM foreach array
            $mpdf->Output();
        }
    }

    public function formpordesembargadoreassuntoAction() {
        $data = $this->_getAllParams();
        $relator = $this->_getParam('desem_federal', 'null');
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('/', '.', $dataInicial);
        $dataFinal = str_replace('/', '.', $dataFinal);
        $form = new Sisad_Form_DataADataDesembargador();
        $this->view->form = $form;

        if ($data['Buscar']) {
            if ($form->isValid($data)) {
                return $this->_redirect('/sisad/distribuicao/pordesembargadoreassunto/data_inicial/' . $dataInicial . '/data_final/' . $dataFinal . '/desem_federal/' . $relator);
            } else {
                $form->populate($data);
            }
        }
        $this->view->title = "Por Desembargador e assunto";
    }

    public function porprocessoAction() {
        $this->_helper->layout->disableLayout();
        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $dados = $sadTbHdpaHistDistribuicao->relatorioDistribuicaoProcAdm();
        if (count($dados) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Nenhum processo administrativo foi distribuído.', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formporprocesso/');
        } else {

            $this->view->arrayDados = $dados;
            $dual = new Application_Model_DbTable_Dual();
            $data = $dual->sysDataHoraDb();

            $this->view->dateTime = $data;

            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            40, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            $this->render();
            $response = $this->getResponse();
            $body = $response->getBody();
            $response->clearBody();

            $cabecalho = "
                    <table id='header'>
                    <tr valign='top'>
                        <td class='brasao'>
                        </td>
                        <td class='date-time'>
                            20/07/2012 17:57:29
                        </td>
                    </tr>
                </table>
                <div class='alinha-titulo'><b>RELATÓRIO DE DISTRIBUIÇÕES ELETRÔNICAS DE PROCESSOS ADMINISTRATIVOS</b></div>
                ";
            $mpdf->showWatermarkImage = true;
            $mpdf->watermarkImgBehind = true;
            $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
            $mpdf->SetHTMLHeader($cabecalho);
            $mpdf->AddPage();
            $mpdf->WriteHTML($body);
            //$mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            $mpdf->Output();
        }
    }
    public function formporprocessoAction() {
        //não faz nada
        //somente mostra mensagem de erro de falta de distribuições na tela
    }
    public function atadedistribuicaoporprocessoAction() {
        $this->_helper->layout->disableLayout();


        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('.', '/', $dataInicial);
        $dataFinal = str_replace('.', '/', $dataFinal);

        $hdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $arrayDistribuicoes = $hdpaHistDistribuicao->relatorioUltimaDistribuicao($dataInicial, $dataFinal);

        if (count($arrayDistribuicoes) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Não existem dados para sua pesquisa', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formatadedistribuicaoporprocesso/');
        } else {
            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));
            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            40, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            //$mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            $data = new Zend_date();
            $hora = $data->get(Zend_Date::HOUR);
            $minuto = $data->get(Zend_Date::MINUTE);
            $segundo = $data->get(Zend_Date::SECOND);
            $dia = $data->get(Zend_Date::DAY);
            $mes = $data->get(Zend_Date::MONTH);
            $ano = $data->get(Zend_Date::YEAR);


            $cabecalho = "
            <table id='header'>
                <tr valign='top'>
                    <td class='brasao'>
                    </td>
                    <td class='date-time'>
                        $dia/$mes/$ano $hora:$minuto:$segundo
                    </td>
                </tr>
            </table> 
            ";


            $mpdf->showWatermarkImage = true;
            $mpdf->watermarkImgBehind = true;
            $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
            $mpdf->SetHTMLHeader($cabecalho);

            $zend_date = new Zend_Date();
            foreach ($arrayDistribuicoes as $rowDistribuicao):
                if (strstr($rowDistribuicao['PMAT_CD_MATRICULA'], 'DS')) {
                    $rowDistribuicao['PNAT_NO_PESSOA'] = 'DESEMBARGADOR FEDERAL ' . $rowDistribuicao['PNAT_NO_PESSOA'];
                }
                switch ($rowDistribuicao['HDPA_IC_FORMA_DISTRIBUICAO']):
                    case 'DA':
                        $rowDistribuicao['HDPA_IC_FORMA_DISTRIBUICAO'] = $rowDistribuicao['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'RA':
                        $rowDistribuicao['HDPA_IC_FORMA_DISTRIBUICAO'] = $rowDistribuicao['QTD_DISTRIBUICAO'] . 'ª REDISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'DM':
                        $rowDistribuicao['HDPA_IC_FORMA_DISTRIBUICAO'] = $rowDistribuicao['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO MANUAL';
                        break;
                    default :
                        $rowDistribuicao['HDPA_IC_FORMA_DISTRIBUICAO'] = 'NÃO IDENTIFICADA';
                        break;
                endswitch;


                $zend_date->setDate($rowDistribuicao['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $zend_date->setTime($rowDistribuicao['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $rowDistribuicao['HDPA_TS_DISTRIBUICAO'] = $zend_date->get(Zend_Date::DATES);
                $rowDistribuicao['HDPA_TS_DISTRIBUICAO'] .= ' ' . $zend_date->get(Zend_Date::TIMES);

                $dia = $zend_date->get(Zend_Date::DAY);
                $mes = $zend_date->get(Zend_Date::MONTH_NAME);
                $ano = $zend_date->get(Zend_Date::YEAR);

                $this->view->dataExtenso = "$dia de $mes do ano de $ano";
                $this->view->processo = $rowDistribuicao['DOCM_NR_DOCUMENTO'];
                $this->view->modalidadeDistribuicao = $rowDistribuicao['HDPA_IC_FORMA_DISTRIBUICAO'];
                $this->view->dataDistribuicao = $rowDistribuicao['HDPA_TS_DISTRIBUICAO'];
                $this->view->orgaoJulgador = $rowDistribuicao['ORGJ_NM_ORGAO_JULGADOR'];
                $this->view->relator = $rowDistribuicao['PNAT_NO_PESSOA'];

                $this->render();
                $response = $this->getResponse();
                $body = $response->getBody();
                $response->clearBody();

                $mpdf->AddPage();
                $mpdf->WriteHTML($body);

            endforeach;

            $mpdf->Output();
        }
    }

    public function formatadedistribuicaoporprocessoAction() {
        $data = $this->_getAllParams();
        $form = new Sisad_Form_DataAData();
        $this->view->form = $form;

        if ($data['Buscar']) {
            if ($form->isValid($data)) {
                $dataInicial = $this->_getParam('data_inicial', 'null');
                $dataFinal = $this->_getParam('data_final', 'null');
                $dataInicial = str_replace('/', '.', $dataInicial);
                $dataFinal = str_replace('/', '.', $dataFinal);
                return $this->_redirect('/sisad/distribuicao/atadedistribuicaoporprocesso/data_inicial/' . $dataInicial . '/data_final/' . $dataFinal);
            } else {
                $form->populate($data);
            }
        }
        $this->view->title = "Ata de distribuição por Processo";
    }

    public function processosjulgadosporperiodoAction() {
        $this->_helper->layout->disableLayout();
        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('/', '.', $dataInicial);
        $dataFinal = str_replace('/', '.', $dataFinal);

        $sadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
        $dadosRelatorio = $sadTbHdpaHistDistribuicao->relatorioProcessoJulgado($dataInicial, $dataFinal);
        if (count($dadosRelatorio) == 0) {
            $this->_helper->flashMessenger->addMessage(array('message' => 'Não existem dados para sua pesquisa', 'status' => 'notice'));
            $this->_redirect('/sisad/distribuicao/formprocessosjulgadosporperiodo/');
        } else {
            $zend_date = new Zend_date();
            $arrayRelatorio = array();
            foreach ($dadosRelatorio as $tupla):
                $zend_date->setDate($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $zend_date->setTime($tupla['HDPA_TS_DISTRIBUICAO'], 'dd/MM/YY HH:mm:ss');
                $tupla['HDPA_TS_DISTRIBUICAO'] = $zend_date->get(Zend_Date::DATES);
                $tupla['HDPA_TS_DISTRIBUICAO'] .= ' ' . $zend_date->get(Zend_Date::TIMES);

                switch ($tupla['HDPA_IC_FORMA_DISTRIBUICAO']):
                    case 'DA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'RA':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª REDISTRIBUIÇÃO AUTOMÁTICA';
                        break;
                    case 'DM':
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = $tupla['QTD_DISTRIBUICAO'] . 'ª DISTRIBUIÇÃO MANUAL';
                        break;
                    default :
                        $tupla['HDPA_IC_FORMA_DISTRIBUICAO'] = 'NÃO IDENTIFICADA';
                        break;
                endswitch;
                if ($arrayRelatorio[$tupla['ORGJ_NM_ORGAO_JULGADOR']][$tupla['PMAT_CD_MATRICULA']] == null) {
                    $arrayRelatorio[$tupla['ORGJ_NM_ORGAO_JULGADOR']][$tupla['PMAT_CD_MATRICULA']] = array();
                }
                $arrayTupla[0] = $tupla;
                $arrayRelatorio[$tupla['ORGJ_NM_ORGAO_JULGADOR']][$tupla['PMAT_CD_MATRICULA']] = array_merge($arrayRelatorio[$tupla['ORGJ_NM_ORGAO_JULGADOR']][$tupla['PMAT_CD_MATRICULA']], $arrayTupla);

            endforeach;


            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

            //$mpdf=new mPDF('utf-8','A4','','' , 0 , 0 , 0 , 0 , 0 , 0); 
            $mpdf = new mPDF('utf-8', // mode - default ''
                            'A4', // format - A4, for example, default ''
                            '', // font size - default 0
                            '', // default font family
                            5, // margin_left
                            20, // margin right
                            55, // margin top
                            16, // margin bottom	 
                            0, // margin header
                            5, // margin footer
                            'L');  // L - landscape, P - portrait
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');

            //############### INICIO DA LOGICA
            //banco retorna array[dataDoDia][orgao] as data
            //array[dataDoDia][orgao] tem arrayDistribuicoes la dentro
            //INICIO foreach array NO CASO RODA NOS DIAS
            //$this->view->dados;
            $data = new Zend_date();
            $hora = $data->get(Zend_Date::HOUR);
            $minuto = $data->get(Zend_Date::MINUTE);
            $segundo = $data->get(Zend_Date::SECOND);
            $dia = $data->get(Zend_Date::DAY);
            $mes = $data->get(Zend_Date::MONTH);
            $ano = $data->get(Zend_Date::YEAR);
            $complemento = '<br/>';
            if ($dataInicial <> 'null' && $dataFinal <> 'null') {
                $complemento = "NO PERÍODO <br/>$dataInicial à $dataFinal";
            } else {
                if ($dataInicial <> 'null') {
                    $complemento = "NO PERÍODO <br/>Após $dataInicial";
                } else {
                    if ($dataFinal <> 'null') {
                        $complemento = "NO PERÍODO <br/>Até $dataFinal";
                    }
                }
            }

            foreach ($arrayRelatorio as $nomeOrgao => $arrayRelatores):

                $this->view->arrayRelatores = $arrayRelatores;

                $this->render();
                $response = $this->getResponse();
                $body[$nomeOrgao] = $response->getBody();
                $response->clearBody();

                $cabecalho = "
                <table id='header'>
                    <tr valign='top'>
                        <td class='brasao'>
                        </td>
                        <td class='date-time'>
                            $dia/$mes/$ano $hora:$minuto:$segundo
                        </td>
                    </tr>
                </table>
                <div class='alinha-titulo'><b>PROCESSO QUE FORAM DISTRIBUÍDOS / REDISTRIBUIDOS
                        $complemento E FORAM JULGADOS</b></div>
                <br/>    
                <div class='orgao-julgador'>
                    <b>ÓRGÃO JULGADOR: $nomeOrgao</b>
                </div>            
                ";
                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;
                $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
                $mpdf->SetHTMLHeader($cabecalho);
                $mpdf->AddPage();
                $mpdf->WriteHTML($body[$nomeOrgao]);
            endforeach;

            //FIM foreach array
            $mpdf->Output();
        }
    }

    public function formprocessosjulgadosporperiodoAction() {
        $data = $this->_getAllParams();

        $dataInicial = $this->_getParam('data_inicial', 'null');
        $dataFinal = $this->_getParam('data_final', 'null');
        $dataInicial = str_replace('/', '.', $dataInicial);
        $dataFinal = str_replace('/', '.', $dataFinal);
        $form = new Sisad_Form_DataAData();
        $this->view->form = $form;

        if ($data['Buscar']) {
            if ($form->isValid($data)) {
                return $this->_redirect('/sisad/distribuicao/processosjulgadosporperiodo/data_inicial/' . $dataInicial . '/data_final/' . $dataFinal);
            } else {
                $form->populate($data);
            }
        }
        $this->view->title = "Processos Julgados (por período)";
    }

    //############################### FIM RELATORIOS
    //############################ REQUISICAO AJAX #############################
    public function ajaxorgaojulgadorAction() {
        $matriculanome = $this->_getParam('term', '');
        $sadTbOrgjOrgaoJulgador = new Application_Model_DbTable_SadTbOrgjOrgaoJulgador();
        $nome_array = $sadTbOrgjOrgaoJulgador->getCodNomeOrgaoAjax($matriculanome);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxpessoasorgaoAction() {
        $orgao = $this->_getParam('ORGJ_CD_ORGAO_JULGADOR', '');

        if ($orgao == 1000 || $orgao == 2000 || $orgao == 3000) {
            $sadTbCdpaContDistProcAdm = new Application_Model_DbTable_SadTbCdpaContDistProcAdm();
            $rows = $sadTbCdpaContDistProcAdm->getMembros('PNAT_NO_PESSOA ASC', $orgao);
        } else {
            $sadTbCcpaContDistComissao = new Application_Model_DbTable_SadTbCcpaContDistComissao();
            $rows = $sadTbCcpaContDistComissao->getMembros('PNAT_NO_PESSOA ASC', $orgao);
        }
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($rows[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

}
