<?php

class Sosti_GestaodedemandasdoatendimentoaosusuariossecoesController extends Zend_Controller_Action {
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
		
        $this->view->titleBrowser = 'e-Sosti';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function indexAction() {
        /**
         * Negócio & Classes da aplicação
         */
        $caixas = new Trf1_Sosti_Negocio_Caixas_Caixa();
        /**
         * Forms
         */
        $form = new Sosti_Form_CaixaSolicitacao();
        
        // levando o perfil do usuario para a view
        $responsavelPelacaixa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $userNs = new Zend_Session_Namespace('userNs');
        $devincularCaixa = $responsavelPelacaixa->getPerfiRespnsavelCaixaUnidade($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula );
        $idPerfil = $devincularCaixa[0]['PERF_ID_PERFIL'];
        $this->view->idPerfil = $idPerfil;
        
        // matricula do usuario para view
        $this->view->userNs = $userNs->matricula;

        /**
         * Variáves de Sessão
         */
        $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index = new Zend_Session_Namespace('Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index');

        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());

        $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 50, 'page' => 1);

        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);
        /**
         * Tratando o Form
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao($userNs->siglasecao, $userNs->codlotacao);
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('AC', '3');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('AM', '4');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('AP', '5');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('BA', '6');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('DF', '7');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('GO', '8');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('MA', '9');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('MG', '10');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('MT', '11');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('PA', '12');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('PI', '13');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('RO', '14');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao('TO', '16');

        $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $dataCaixa = $caixaEntrada->fetchRow("CXEN_ID_CAIXA_ENTRADA = " . $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"]);
        $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->descricaocaixa = $dataCaixa["CXEN_DS_CAIXA_ENTRADA"];
        $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idcaixa = $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"];
        $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idGrupo = $SgrsGrupoServico[0]["SGRS_ID_GRUPO"];

        $form->removeElement('SGRS_ID_GRUPO');
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->getServicoPorGrupo($Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idGrupo, 'SSER_DS_SERVICO ASC');
        $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
        $sser_id_servico->addMultiOptions(array('' => ''));
        foreach ($SserServico as $SserServico_p):
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        endforeach;

        $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_ID_GRUPO = $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idGrupo");
        $Categorias = $Categorias->toArray();
        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        if ($Categorias) {
            foreach ($Categorias as $Categorias_p):
                $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
                $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
                $cont++;
            endforeach;
            $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
            $this->view->categorias = $Categorias;
        }

        $uri = $_SERVER['REQUEST_URI'];
        $end = explode('/sosti/', $uri);
        $end = explode('/', $end[1]);

        $CateNs = new Zend_Session_Namespace('CateNs');
        $CateNs->tipo = 1;
        $CateNs->identificador = $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idcaixa;
        $CateNs->idGrupo = $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idGrupo;
        $CateNs->controller = $end[0];
        if (isset($end[1])) {
            $CateNs->action = $end[1];
        } else {
            $CateNs->action = 'index';
        }

        /*
         * Regra Negocial - Resposta Padrao
         * Armazena o Id do Grupo para fazer a validação no cadastro das Respostas Pdrões
         */
        $rn_resposta_padrao = new Services_Sosti_RespostaPadrao();
        $rn_resposta_padrao->setIdGrupo($Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idGrupo);
        $this->view->idGrupo = $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idGrupo;

        $mofa_id_fase = $form->getElement('MOFA_ID_FASE');
        $arr_fadm_id_fase = $mofa_id_fase->getMultiOptions();
        $arr_fadm_id_fase = array_keys($arr_fadm_id_fase);
        foreach ($arr_fadm_id_fase as $value) {
            if (!(($value == 1019) || ($value == 1006) || ($value == 1022) || ($value == 1013) || ($value == 1001) || ($value == 1007) || ($value == 1025) ||
                    ($value == 1024) || ($value == 1008) )
            ) {
                if ($value != "") {
                    $mofa_id_fase->removeMultiOption($value);
                }
            }
        }

        /**
         * Para zerar o filtro
         */
        if ($this->_getParam('nova') === '1') {
            unset($Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }


        /**
         * Submissão do filtro
         */
        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            $form->populate($data_pesq);

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->data_pesq = $this->getRequest()->getPost();
            } else {
                /**
                 * Populando o formulário inválido
                 */
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "CAIXA DE ENTRADA DA GESTÃO DE DEMANDAS DO ATENDIMENTO AOS USUÁRIOS";
                return;
            }
        }

        /*
         * Aplicação do filtro caso ele seja válido
         */
        $data_pesq = $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->data_pesq;
        $post_data_pesq = $data_pesq;
        if (!is_null($data_pesq)) {
            /**
             * Auxilia a view a tratar a ausencia de registros e esconder botões
             */
            $this->view->ultima_pesq = true;

            /* paginação */
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            /* Ordenação das paginas */
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

            /**
             * Tratamento dos dados para passar na array params
             */
            $mat_aux = explode(' - ', $data_pesq["DOCM_CD_MATRICULA_CADASTRO_VALUE"]);
            $data_pesq["DOCM_CD_MATRICULA_CADASTRO"] = $mat_aux[0];

            $mat_atend_aux = explode(' - ', $data_pesq["SSOL_CD_MATRICULA_ATENDENTE_VALUE"]);
            $data_pesq["SSOL_CD_MATRICULA_ATENDENTE"] = $mat_atend_aux[0];


            $unid_aux = explode(' - ', $data_pesq["DOCM_CD_LOTACAO_GERADORA_VALUE"]);
            ( array_key_exists(2, $unid_aux) ) ? ($data_pesq["DOCM_CD_LOTACAO_GERADORA"] = $unid_aux[2]) : '';
            ( array_key_exists(3, $unid_aux) ) ? ($data_pesq["DOCM_SG_SECAO_GERADORA"] = $unid_aux[3]) : '';

            /**
             * Chama o método de pesquisa
             */
            $consulta_caixa = $caixas->getCaixaSemNivelPesq($Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idcaixa, $data_pesq, $order);

            /**
             * Configura o Zend paginator
             */
            $paginator = new Zend_Paginator(new App_Paginator_Adapter_Sql_Oracle($consulta_caixa));
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage($itemCountPerPage);

            foreach ($paginator->getCurrentItems() as $sosti) {
                if ($sosti['VINCULADA'] > 0) {
                    $vincs = $caixas->getVinculos($sosti["SSOL_ID_DOCUMENTO"]);
                    foreach($vincs as $vincRow){
//                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq($Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idcaixa, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
                        $queryCaixa = $caixas->getCaixaSemNivelPesq($Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idcaixa, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"]);
                        if(is_string($queryCaixa))
                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getData($queryCaixa);
                        else
                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq($Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->idcaixa, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
                    }
                }
            }
            $this->view->vinc = $vinc;

            $this->view->title = $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->descricaocaixa;
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');

            /**
             * Popula o filtro com a última pesquisa
             */
            $form->populate($post_data_pesq);
        }

        $this->view->form = $form;
        $this->view->title = $Ns_Gestaodedemandasdoatendimentoaosusuariossecoes_index->descricaocaixa;
    }

    public function encaminharAction() {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_AtendimentoSecoesEncaminhar();
        $formAnexo = new Sosti_Form_Anexo();
		$formAnexo->anexoUnico();
		$form->addElement($formAnexo->getElement('ANEXOS'));
		$formAnexo->submit();
        $salvar = $formAnexo->getElement('Salvar');
        $salvar->setName('Encaminhar');
		$form->addElement($salvar);
        
        $SadTbAtcxAtendenteCaixa = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

        /**
         * VARIAVEL DO GRUPO DE SERVIÇO PARA A RESPOSTA PADRÃO
         */
        $idGrupo_repd = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);

        $encaminhamento = $form->getElement('ENCAMINHAMENTO');
        $encaminhamento->setMultiOptions(array(
            /* 'nivel'   => 'Outro nível de atendimento', */
            'pessoal' => 'Caixa pessoal',
            'trf' => 'Trf ou Atendimento da Seção'/* , 
                  'secoes' => 'Seções', */
        ));
        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();
        if ($this->getRequest()->isPost()) {
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Encaminhar') {

                $NsAction->dadosPost = $data;
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                $NsAction->dadosCaixa = $data;
                $NsAction->dadosSolicitacao = $data['solicitacao'];

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                foreach ($data["solicitacao"] as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $idServico = $dados_input["SSER_ID_SERVICO"];
                    $cdNivelCaixa = $dados_input["SNAT_CD_NIVEL"];
                    $idCaixa = $dados_input["MODE_ID_CAIXA_ENTRADA"];

                    /**
                     * Validação
                     */
                    $idGrupo_aux = $idGrupo;
                    $row = $SosTbSserServico->find($idServico);
                    $servicos = $row->toArray();
                    $idGrupo = $servicos[0][SSER_ID_GRUPO];
                    $idGrupo_repd[] = $idGrupo;
                    if ($idGrupo_aux) {
                        if ($idGrupo != $idGrupo_aux) {
                            $msg_to_user = "Não é possível realizar ENCAMINHAMENTO com solicitações com serviços de grupos de serviço diferentes";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                            $this->_helper->_redirector($NsAction->dadosPost['action'], $NsAction->dadosPost['controller'], 'sosti');
                            return;
                        }
                    }
                }

                /**
                 * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                 * SETANDO VALOR DO ID GRUPO NA SESSION
                 */
                $NsAction->idGrupo_repd = array_unique($idGrupo_repd);
                $form_resposta->set_idGrupo(array_unique($idGrupo_repd));
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;

                /* Adicionar o grupo de gestão para a seção */
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($dados_input['MODE_SG_SECAO_UNID_DESTINO'], $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
                $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico[0]) => $SgrsGrupoServico[0]["CXEN_DS_CAIXA_ENTRADA"]));

                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                foreach ($arr_sgrs_id_grupo as $value) {
                    $value_option = Zend_Json::decode($value);
                    if (in_array($value_option["SGRS_ID_GRUPO"], array($idGrupo))) {
                        $sgrs_id_grupo->removeMultiOption($value);
                    }
                }

                $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisPorServico($idServico);

                $snas_id_nivel = $form->SNAS_ID_NIVEL;
                foreach ($NivelAtendimento as $NivelAtendimento_p):
                    if ($NivelAtendimento_p["SNAT_CD_NIVEL"] != $cdNivelCaixa) {
                        $snas_id_nivel->addMultiOptions(array($NivelAtendimento_p["SNAT_ID_NIVEL"] => $NivelAtendimento_p["SNAT_DS_NIVEL"]));
                    }
                endforeach;

                /* Alteração para retirar a escolha da unidade quando for encaminhar */
                $form->removeElement('LOTA_COD_LOTACAO');
                /**
                 * Método que retorna os atendentes da caixa pelo id da caixa ($idCaixa)
                 */
                $pessoas = $SadTbAtcxAtendenteCaixa->getPessoasCaixa($idCaixa);
                $apsp_id_pessoa = $form->APSP_ID_PESSOA;
                foreach ($pessoas as $pessoas_p):
                    $apsp_id_pessoa->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["ATENDENTE"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
                endforeach;

                foreach ($NsAction->dados as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                    $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                }
                $nrdocumento = substr($solicitacoesEncaminhadas, 1);
            } else {
                $dadosCaixa = $NsAction->dadosCaixa;

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                foreach ($dadosCaixa["solicitacao"] as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $idServico = $dados_input["SSER_ID_SERVICO"];
                    $cdNivelCaixa = $dados_input["SNAT_CD_NIVEL"];
                    $idCaixa = $dados_input["MODE_ID_CAIXA_ENTRADA"];

                    /**
                     * Validação
                     */
                    $idGrupo_aux = $idGrupo;
                    $row = $SosTbSserServico->find($idServico);
                    $servicos = $row->toArray();
                    $idGrupo = $servicos[0][SSER_ID_GRUPO];
                    if ($idGrupo_aux) {
                        if ($idGrupo != $idGrupo_aux) {
                            $msg_to_user = "Não é possível realizar ENCAMINHAMENTO com solicitações com serviços de grupos de serviço diferentes";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));

                            $this->_helper->_redirector($NsAction->dadosPost['action'], $NsAction->dadosPost['controller'], 'sosti');
                            return;
                        }
                    }
                }

                /* Adicionar o grupo de gestão para a seção */
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($dados_input['MODE_SG_SECAO_UNID_DESTINO'], $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
                $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico[0]) => $SgrsGrupoServico[0]["CXEN_DS_CAIXA_ENTRADA"]));

                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                foreach ($arr_sgrs_id_grupo as $value) {
                    $value_option = Zend_Json::decode($value);
                    if (in_array($value_option["SGRS_ID_GRUPO"], array($idGrupo))) {
                        $sgrs_id_grupo->removeMultiOption($value);
                    }
                }

                $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisPorServico($idServico);

                $snas_id_nivel = $form->SNAS_ID_NIVEL;
                foreach ($NivelAtendimento as $NivelAtendimento_p):
                    if ($NivelAtendimento_p["SNAT_CD_NIVEL"] != $cdNivelCaixa) {
                        $snas_id_nivel->addMultiOptions(array($NivelAtendimento_p["SNAT_ID_NIVEL"] => $NivelAtendimento_p["SNAT_DS_NIVEL"]));
                    }
                endforeach;

                $destino = Zend_Json::decode($data['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data['SGRS_ID_GRUPO'] => $destino["SGRS_DS_GRUPO"]));

                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                $sser_id_servico->addMultiOptions(array($data['SSER_ID_SERVICO'] => ''));

                /* Alteração para retirar a escolha da unidade quando for encaminhar */
                $form->removeElement('LOTA_COD_LOTACAO');
                /**
                 * Método que retorna os atendentes da caixa pelo id da caixa ($idCaixa)
                 */
                $pessoas = $SadTbAtcxAtendenteCaixa->getPessoasCaixa($idCaixa);
                $apsp_id_pessoa = $form->APSP_ID_PESSOA;
                foreach ($pessoas as $pessoas_p):
                    $apsp_id_pessoa->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["ATENDENTE"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
                endforeach;

                if ($form->isValid($data)) {
                    $form->ANEXOS->receive();
					$nrDocsRed = null;
					if (!is_null($data["ANEXOS"])/*$form->ANEXOS->isReceived()*/) {
						try {
							$upload = new App_Multiupload_NewMultiUpload();
							$nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
						} catch (Exception $exc) {
							$msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
							$this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
							$this->_helper->_redirector($solicspace->action, $solicspace->controller, 'sosti');
						}
					}
                    $acompanhar = $data["ACOMPANHAR"];
                    /* NIVEL */
                    if ($data["ENCAMINHAMENTO"] == 'nivel') {


                        if ($data["SNAS_ID_NIVEL"] && isset($data["SNAS_ID_NIVEL"])) {
                            $dadosCaixa = $NsAction->dadosCaixa;
                            $solicitacoesEncaminhadas = '';
                            foreach ($dadosCaixa["solicitacao"] as $d) {
                                $dados_input = Zend_Json::decode($d);
                                $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];

                                $movimentacao = $dados_input["MOFA_ID_MOVIMENTACAO"];

                                $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                                $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $movimentacao;
                                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];
                                $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $data['SNAS_ID_NIVEL'];
                                $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
                                
                                $SosTbSnatNivelAtendimento->trocanivelSolicitacao($idDocmDocumento, $dataMofaMoviFase, $dataSnasNivelAtendSolic, $nrDocsRed, $acompanhar);
                                $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                                $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                                $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                            }
                            $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " encaminhada(s)!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));

                            $this->_helper->_redirector($NsAction->dadosPost['action'], $NsAction->dadosPost['controller'], 'sosti');
                        }

                        /* GRUPO */
                    } else if ($data["ENCAMINHAMENTO"] == 'trf') {

                        $dadosCaixa = $NsAction->dadosCaixa;
                        $matricula = $userNs->matricula;
                        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();

                        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $db->beginTransaction();
                        try {
                            foreach ($dadosCaixa["solicitacao"] as $d) {
                                $dados_input = Zend_Json::decode($d);

                                $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];

                                $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                                $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                                $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $matricula;
                                $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $dados_input["MODE_ID_CAIXA_ENTRADA"];

                                $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $destino["SGRS_SG_SECAO_LOTACAO"];
                                $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $destino["SGRS_CD_LOTACAO"];
                                $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                                $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $destino["CXEN_ID_CAIXA_ENTRADA"];

                                /**
                                 * ENCAMINHAMENTO DE SOLICITAÇÃO DE TI DE SEÇÃO PARA O TRIBUNAL CODIGO 1022
                                 */
                                if ($destino['TPCX_ID_TIPO_CAIXA'] == 7) {
                                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1050;
                                } else {
                                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1022;
                                }
                                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $matricula;
                                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];

                                $id_serviço = explode('|', $data["SSER_ID_SERVICO"]);
                                $dataSsesServicoSolic["SSES_ID_SERVICO"] = $id_serviço[0];

                                /**
                                 * ENVIA PARA O INDICADOR DE MENOR NÍVEL
                                 */
                                $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($destino["SGRS_ID_GRUPO"]);
                                if ($NivelAtendSolic) {
                                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $NivelAtendSolic["SNAT_ID_NIVEL"];
                                } else {
                                    /**
                                     * PARA OS GRUPOS DE SERVIÇO QUE NÃO POSSUEM NÍVEIS COMO O DA DESENVOLVIMENTO E SUSTENTAÇÃO
                                     */
                                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = '';
                                }

                                $SosTbSsolSolicitacao->encaminhaSolicitacao($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed, $acompanhar);
                                $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                                $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                            }
                        } catch (Exception $exc) {
                            $db->rollBack();

                            /**
                             * Envio de Emails
                             */
                            if ($destino["SGRS_ID_GRUPO"] == 4 && $data["SSER_ID_SERVICO"] != '6071|N|S') {
                                /**
                                 * Email para Responsáveis pela caixa
                                 * SOSTI: 2012010001135011350160010367
                                 */
                                $email = new Application_Model_DbTable_EnviaEmail();
                                $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                $remetente = 'noreply@trf1.jus.br';
                                $assunto = 'Cadastro de Solicitação para Caixa do NOC';
                                $corpo = "Solicitação Encaminhada para Caixa do NOC</p>
                                           Número da Solicitação: " . $dados_input['DOCM_NR_DOCUMENTO'] . " <br/>
                                           Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                           Encaminhado por: " . $userNs->nome . " <br/>
                                           Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                           Descrição do Encaminhamento: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                                try {
                                    $email->setEnviarEmail($sistema, $remetente, 'wanderson.martins@trf1.jus.br', $assunto, $corpo);
                                    $email->setEnviarEmail($sistema, $remetente, 'alex.peres@trf1.jus.br', $assunto, $corpo);
                                    $email->setEnviarEmail($sistema, $remetente, 'plinio.meireles@trf1.jus.br', $assunto, $corpo);
                                    $email->setEnviarEmail($sistema, $remetente, 'noc@srvmon1-trf1.trf1.gov.br', $assunto, $corpo);
                                } catch (Exception $exc) {
                                    
                                }
                            }

                            $erro = $exc->getMessage();
                            $msg_to_user = "Ocorreu um erro ao encaminhar a solicitação! <br/> $erro ";

                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($NsAction->dadosPost['action'], $NsAction->dadosPost['controller'], 'sosti');
                        }
                        $db->commit();

                        $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " encaminhada(s)!";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        $this->_helper->_redirector($NsAction->dadosPost['action'], $NsAction->dadosPost['controller'], 'sosti');

                        /* PESSOAL */
                    } else if ($data["ENCAMINHAMENTO"] == 'pessoal') {

                        $dataSol = $NsAction->dadosCaixa;
                        foreach ($dataSol["solicitacao"] as $d) {
                            $dados_input = Zend_Json::decode($d);
                            $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                            $movimentacao = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
                            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $movimentacao;
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                            $dataSsolSolicitacao["SSOL_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                            $dataSsolSolicitacao["SSOL_CD_MATRICULA_ATENDENTE"] = $data["APSP_ID_PESSOA"];

                            $idDocmDocumento = $dados_input["SSOL_ID_DOCUMENTO"];

                            $SadTbMofaMoviFase->encaminhaCaixaPessoalSolicitacao($idDocmDocumento, $dataMofaMoviFase, $dataSsolSolicitacao, $nrDocsRed, $acompanhar);
                            /**
                             * Envio de email de resposta
                             */
                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $remetente = 'noreply@trf1.jus.br';
                            $destinatario = $data["APSP_ID_PESSOA"] . '@trf1.jus.br';
                            $assunto = 'Encaminhamento de Solicitação';
                            $corpo = "Uma solicitação foi encaminhada para sua Caixa Pessoal.</p>
                                            Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/" . $dados_input['DOCM_NR_DOCUMENTO'] . "\"><b>" . $dados_input['DOCM_NR_DOCUMENTO'] . "</b> </a><br />
                                            Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                            Encaminhado por: " . $userNs->nome . " <br/>
                                            Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                            Descrição do Encaminhamento: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                            /**
                             * Fim do envio de email
                             */
                            $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                        }
                        $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " encaminhada(s)!";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        $this->_helper->_redirector($NsAction->dadosPost['action'], $NsAction->dadosPost['controller'], 'sosti');
                    }
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * OBTENDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $this->view->title = "ATENDIMENTO DA SEÇÃO - ENCAMINHAR SOLICITAÇÃO(ES)";
                    $this->view->data = $NsAction->dadosSolicitacao;
                    $pop['LOTA_COD_LOTACAO'] = strtoupper($userNs->siglalotacao) . ' - ' . strtoupper($userNs->descicaolotacao) . ' - ' . strtoupper($userNs->codlotacao);
                    $form->populate($pop);
                    $this->view->form = $form;
                }
            }
        }
        $this->view->title = "ATENDIMENTO DA SEÇÃO - ENCAMINHAR SOLICITAÇÃO(ES)";
        $this->view->data = $NsAction->dadosSolicitacao;
        $this->view->form = $form;
    }

}
