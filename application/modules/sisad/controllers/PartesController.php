<?php

class Sisad_PartesController extends Zend_Controller_Action {
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
		
        $this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
        $this->_model = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $this->controller = $this->getRequest()->getControllerName();
        $this->action = $this->getRequest()->getActionName();
        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
        // Informação
        $this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function index() {
        
    }

    public function ajaxpessoaexternaAction() {
        $pessExt = $this->_getParam('term', '');

        $OcsTbPnatPessoaNatural = new Application_Model_DbTable_OcsTbPnatPessoaNatural();
        $pessoas = $OcsTbPnatPessoaNatural->getPessoaAjax($pessExt);

        $fim = count($pessoas);
        for ($i = 0; $i < $fim; $i++) {
            $pessoas[$i] = array_change_key_case($pessoas[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($pessoas);
    }

    public function ajaxpessoajuridicaAction() {
        $pessJur = $this->_getParam('term', '');

        $OcsTbPjurPessoaJuridica = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $pessoasJuri = $OcsTbPjurPessoaJuridica->getNomeDestinatarioAjax($pessJur);

        $fim = count($pessoasJuri);
        for ($i = 0; $i < $fim; $i++) {
            $pessoasJuri[$i] = array_change_key_case($pessoasJuri[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($pessoasJuri);
    }

    public function ajaxunidadeAction() {
        $unidade = $this->_getParam('term', '');
        $sigla = $this->_getParam('sigla', '');
        $cod = $this->_getParam('cod', '');

        //$userNamespace = new Zend_Session_Namespace('userNs');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacoesDaSecaoAjax($unidade, $sigla, $cod);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function cadastrapartesAction() {

        $tipo = $this->_getParam('tipo', '');
        if ($tipo == '1') {
            $this->view->title = "Cadastro de Partes";
        } else if ($tipo == '3') {
            $this->view->title = "Cadastro de Vistas";
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            $this->view->formParte = new Sisad_Form_Partes;
            $this->view->tipoCadastro = $tipo;
            $Ns_partes_cadastrapartes = new Zend_Session_Namespace('Ns_partes_cadastrapartes');
            $Ns_Partes_documentos = new Zend_Session_Namespace('Ns_Partes_documentos');
            $aNamespace = new Zend_Session_Namespace('userNs');
            $docsRetirados = "";
            $flashmessage = array('label' => '', 'status' => 'notice', 'message' => '');


            if (isset($data['acao']) && $data['acao'] == 'Cadastrar Partes' || $data['acao'] == 'Cadastrar Vistas') {

                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column . ' ' . $order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

                $cont = 0;
                $rows = array();
                $service_juntada = new Services_Sisad_Juntada();
                $data['documento'] = $service_juntada->completaComApensados($data['documento']);
                $data_post_caixa = $data;

                foreach ($data_post_caixa['documento'] as $value) {

                    $rows['documento'][$cont] = Zend_Json::decode($value);
                    $doc = $rows['documento'][$cont];

                    //Se for cadastro de vista, verifico se ha documento publico
                    if ($data['acao'] == 'Cadastrar Vistas') {

                        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                        //Zend_Debug::dump($doc['DOCM_ID_CONFIDENCIALIDADE'],'confidencialidade');
                        // verifico se o documento é publico
                        if ($doc['DOCM_ID_CONFIDENCIALIDADE'] == "0") {
                            //retiro o documento da listagem pois nao eh possivel cadastrar vista em documento publico
                            $docsRetirados .= $doc["DTPD_NO_TIPO"] . " nº " . $doc["DOCM_NR_DOCUMENTO"] . " - Confidencialidade Público <br />";
                            //$this->view->flashmessage = $flashmessage;
                            unset($rows['documento'][$cont]);

                            //se o documento for confidencial       
                        } else {

                            //se for documento da corregedoria
                            if ($doc['DOCM_ID_CONFIDENCIALIDADE'] == "5") {
                                $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                                $usuarioCorregedoria = $OcsTbPupePerfilUnidPessoa->verificaPermissaoCorregedoria();
                                // Zend_Debug::dump($usuarioCorregedoria, 'usuarioCorregedoria ');
                                if (empty($usuarioCorregedoria)) {
                                    $docsRetirados .= $doc[DTPD_NO_TIPO] . " nº " . $doc[DOCM_NR_DOCUMENTO] . " - Documento da Corregedoria <br/>";
                                    unset($rows['documento'][$cont]);
                                }

                                //demais documentos confidenciais, excluindo os da corregedoria
                            } else {

                                $verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($doc);
                                // Zend_Debug::dump($verifica, 'verifica ');
                                if (!$verifica) {
                                    $docsRetirados .= $doc[DTPD_NO_TIPO] . " nº " . $doc[DOCM_NR_DOCUMENTO] . " - É necessário permissão de vistas para cadastrar novas vistas. <br/>";
                                    unset($rows['documento'][$cont]);
                                }
                            }
                        }
                    }

                    $cont++;
                }

                if (!empty($docsRetirados)) {
                    $flashmessage['label'] = 'Atenção: ';
                    $flashmessage['message'] = " não é possível cadastrar vistas nos seguintes Documentos/Processos:  <br/>" . $docsRetirados;
                    $this->view->flashmessage = $flashmessage;
                }

                $data_post_caixa['documento'] = $rows['documento'];
                $Ns_partes_cadastrapartes->data_post_caixa = $data_post_caixa;

                //sessao utilizada para verificar as vistas/partes ja cadastradas dos documentos selecionados
                $Ns_Partes_documentos->data_post_caixa['documento'] = $data_post_caixa['documento'];


                if ($rows['documento']) {
                    $paginator = Zend_Paginator::factory($rows['documento']);
                    $paginator->setCurrentPageNumber($page)
                            ->setItemCountPerPage(count($rows['documento']));

                    $this->view->ordem = $order_column;
                    $this->view->direcao = $order_direction;
                    $this->view->data = $paginator;
                    Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
                } else {
                    $this->_helper->flashMessenger(array('message' => 'É necessário escolher um documento em que possua permissão para cadastrar partes ou vistas. <br/>' . $msg, 'status' => 'notice'));
                    return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
                }
            }
        } else {
            $this->_helper->flashMessenger(array('message' => 'É necessário escolher um documento para cadastrar partes ou vistas. <br/>' . $msg, 'status' => 'notice'));
            return $this->_helper->_redirector('index', 'index', 'sisad');
        }
    }

    public function saveAction() {

        $Ns_partes_cadastrapartes = new Zend_Session_Namespace('Ns_partes_cadastrapartes');
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            $data_post_caixa = $Ns_partes_cadastrapartes->data_post_caixa;
            //  Zend_debug::dump($data_post_caixa); exit;
            $dataPartePessoa = array();
            $dataParteLotacao = array();
            $dataPartePessExterna = array();
            $dataPartePessJur = array();
            $dataProcesso = array();

            if (count($data['partes_pessoa_trf'])) {
                $dataPartePessoa = array_unique($data['partes_pessoa_trf']);
            }
            if (count($data['partes_unidade'])) {
                $dataParteLotacao = array_unique($data['partes_unidade']);
            }
            if (count($data['partes_pess_ext'])) {
                $dataPartePessExterna = array_unique($data['partes_pess_ext']);
            }
            if (count($data['partes_pess_jur'])) {
                $dataPartePessJur = array_unique($data['partes_pess_jur']);
            }
            //Zend_debug::dump($data_post_caixa['documento'], 'documentos ');
            try {
                foreach ($data_post_caixa['documento'] as $documento) {
                    // $dados_input = Zend_Json::decode($documento);
                    // Zend_debug::dump($dados_input);

                    $documento['replicaVistas'] = $data['REPLICA_VISTAS'];
                    //verificar se tem processo administrativo para buscar o codigo do processo 
                    if ($documento['DTPD_ID_TIPO_DOC'] == '152') {
                        $dataProcesso = $SadTbPrdiProcessoDigital->getProcesso($documento['DOCM_ID_DOCUMENTO']);
                    } else {
                        $dataProcesso = array();
                    }

                    if (count($dataPartePessoa) || count($dataParteLotacao) || count($dataPartePessExterna) || count($dataPartePessJur)) {
                        if (!empty($documento['DOCM_ID_DOCUMENTO'])) {
                            $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $documento, $dataProcesso);
                        }
                    }
                }//exit;
                $msg_to_user = "Partes/Vistas cadastradas com sucesso.";
                unset($Ns_partes_cadastrapartes->data_post_caixa);
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
            } catch (Exception $e) {
                $msg_to_user = "Erro ao cadastrar partes/vistas.";
                $this->_helper->flashMessenger(array('message' => $msg_to_user . ' - ' . $e, 'status' => 'error'));
                return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
            }
        }
    }

    /**
     * Mostra as partes e vistas de um documento caso ele possua
     * Recebe como paratro:
     * tipo valores: 1 ou 3 - serve para mostrar o titulo
     * remover valores: S ou N - serve para exibir a possibilidade de remover parte ou vista.
     *      Sendo o valor S o responsavel por prover a possibilidade de remoção.
     */
    public function partesvistasdocumentosAction() {

        $remover = $this->_getParam('remover', 'N');
        $tipo = $this->_getParam('tipo', '');
        if ($tipo == '1') {
            $this->view->tituloTabela = "partes";
        } else if ($tipo == '3') {
            $this->view->tituloTabela = "vistas";
        }
        $Ns_Atuar_autuar = new Zend_Session_Namespace('Ns_Atuar_autuar');
        $Ns_Partes_documentos = new Zend_Session_Namespace('Ns_Partes_documentos');


        $this->view->form = new Sisad_Form_Partes;
        $data_post_caixa = $Ns_Atuar_autuar->data_post_caixa;

        if (isset($Ns_Partes_documentos->data_post_caixa) && !empty($Ns_Partes_documentos->data_post_caixa)) {
            $data_post_caixa = $Ns_Partes_documentos->data_post_caixa;
        }

        if (!empty($data_post_caixa)) {
            foreach ($data_post_caixa['documento'] as $value) {
                if (is_array($value)) {
                    $dados_input = $value;
                } else {
                    $dados_input = Zend_Json::decode($value);
                }
                $id_documento = $dados_input['DOCM_ID_DOCUMENTO'];
                $interessados_docs = $this->_model->getPartesVistas($id_documento, null, $tipo);
                if (!empty($interessados_docs)) {
                    foreach ($interessados_docs as $int) {
                        $inter[] = array(
                            "nome" => $int['NOME'],
                            "id" => $int['ID'],
                            "input_name" => $int['TIPO'],
                            "value" => $int['VALUE'],
                            "tipo_parte" => $int['TIPO_PARTE']
                        );
                    }
                }
            }
        }
        $this->view->interessados_docs = $inter;
        $this->view->remover = $remover;
    }

    public function verificapermissaovistasAction() {
        //echo 'oiii';
        if ($this->getRequest()->isPost()) {

            $server = new Zend_Json_Server_Request_Http();
            $dados = Zend_Json::decode($server->getRawJson());

            //$dados = Zend_Json::decode($data);  
            //Zend_Debug::dump($dados[DOCM_ID_DOCUMENTO]); 
            $eRedator = $this->_model->verificaParteVista($dados["DOCM_ID_DOCUMENTO"], null, 4); //4 = Redator
            $this->view->eRedator = $eRedator;
            //Zend_Debug::dump($eRedator,'Redator'); exit;
        }
    }

}
