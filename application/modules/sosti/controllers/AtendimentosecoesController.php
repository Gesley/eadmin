<?php

class Sosti_AtendimentosecoesController extends Zend_Controller_Action {
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
        set_time_limit(420);
        /* 7 minutos */
    }

    public function atendimentousuarioAction() {
        /**
         * Paginação inicialização e configuração
         */
        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 50, 'page' => 1);
        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);
        /* Ordenação das paginas */
        $order_column = $varSessoes->getOrdem();
        $order_direction = $varSessoes->getDirecao();
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        
        // levando o perfil do usuario para a view
        $responsavelPelacaixa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $userNs = new Zend_Session_Namespace('userNs');
        $devincularCaixa = $responsavelPelacaixa->getPerfiRespnsavelCaixaUnidade($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula );
        $idPerfil = $devincularCaixa[0]['PERF_ID_PERFIL'];
        $this->view->idPerfil = $idPerfil;
        
        // matricula do usuario para view
        
        $this->view->userNs = $userNs->matricula;
        /**
         * variáveis de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Atendimentosecoes_atendimentousuario = new Zend_Session_Namespace('Ns_Atendimentosecoes_atendimentousuario');
        /**
         * models
         */
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
        /**
         *  forms 
         */
        $form = new Sosti_Form_AtendimentoSecoesFiltroCaixa();
        /**
         * Negócio & Classes da aplicação
         */
        $caixas = new Trf1_Sosti_Negocio_Caixas_Caixa();
        /**
         * Recuperando os dados da caixa conforme a lotação
         */
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($userNs->siglasecao, $userNs->codlotacao);
        /**
         * Verificação do grupo da caixa atual
         */
        $idGrupo = $SgrsGrupoServico[0]["SGRS_ID_GRUPO"];
        /*
         * Regra Negocial - Resposta Padrao
         * Armazena o Id do Grupo para fazer a validação no cadastro das Respostas Pdrões
         */
        $services_sosti_respostapadrao = new Services_Sosti_RespostaPadrao();
        $services_sosti_respostapadrao->setIdGrupo($idGrupo);
        /**
         * Tratamento filtro da caixa
         */
        $form->removeElement('SGRS_ID_GRUPO');
        /**
         *  Campo de serviços
         */
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->getServicoPorGrupo($idGrupo, 'SSER_DS_SERVICO ASC');
        $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
        $sser_id_servico->addMultiOptions(array('' => ''));
        foreach ($SserServico as $SserServico_p):
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        endforeach;
        /**
         * Campo de fases
         */
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
         * Campo de nível
         * E primeiro nível como padrao
         */
        $snat_cd_nivel = $form->getElement('SNAT_CD_NIVEL');
        $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisGrupoServicoAtendimentoUsuPorLotacao('MG', 10);
//        $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisGrupoServicoAtendimentoUsuPorLotacao($userNs->siglasecao, $userNs->codlotacao);
//        Zend_Debug::dump($NivelAtendimento);die;
        $nivelDefault = array();
        $snat_cd_nivel->addMultiOptions(array('' => ''));
        foreach ($NivelAtendimento as $key => $NivelAtendimento_p):
            if ($key == 0) {
                $nivelDefault = $NivelAtendimento_p;
            }
            $snat_cd_nivel->addMultiOptions(array(Zend_Json::encode($NivelAtendimento_p) => $NivelAtendimento_p["SNAT_DS_NIVEL"]));
        endforeach;

        /**
         * Limpar o filtro
         */
        if ($this->_getParam('nova') === '1') {
            unset($Ns_Atendimentosecoes_atendimentousuario->data_pesq);
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
            $data = $this->getRequest()->getPost();

            /**
             * Tratamento campo de categorias
             */
            if (!empty($data["SNAT_CD_NIVEL"])) {
                $dadosNivel = Zend_Json::decode($data['SNAT_CD_NIVEL']);
                $idNivel = $dadosNivel["SNAT_ID_NIVEL"];
                $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
                $Categorias = $cateCategoria->fetchAll("CATE_ID_NIVEL = $idNivel")->toArray();
                $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
                $cont = 0;
                foreach ($Categorias as $Categorias_p):
                    $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
                    $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
                    $cont++;
                endforeach;
                $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
            }

            if ($form->isValid($data)) {
                $this->view->ultima_pesq = true;

                $dadosNivel = Zend_Json::decode($data['SNAT_CD_NIVEL']);
                $nivel = $dadosNivel["SNAT_CD_NIVEL"];
                $idNivel = $dadosNivel["SNAT_ID_NIVEL"];
                $idCaixa = $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"];

                /**
                 * Tratamento de entrada da array de pesquisa
                 */
                $data_pesq = $data;
                $mat_aux = explode(' - ', $data_pesq["DOCM_CD_MATRICULA_CADASTRO_VALUE"]);
                $data_pesq["DOCM_CD_MATRICULA_CADASTRO"] = $mat_aux[0];
                $mat_atend_aux = explode(' - ', $data_pesq["SSOL_CD_MATRICULA_ATENDENTE_VALUE"]);
                $data_pesq["SSOL_CD_MATRICULA_ATENDENTE"] = $mat_atend_aux[0];
                $unid_aux = explode(' - ', $data_pesq["DOCM_CD_LOTACAO_GERADORA_VALUE"]);
                ( array_key_exists(2, $unid_aux) ) ? ($data_pesq["DOCM_CD_LOTACAO_GERADORA"] = $unid_aux[2]) : '';
                ( array_key_exists(3, $unid_aux) ) ? ($data_pesq["DOCM_SG_SECAO_GERADORA"] = $unid_aux[3]) : '';

                $Ns_Atendimentosecoes_atendimentousuario->data_pesq = $data_pesq;
                $form->populate($data);
            } else {
                $form->populate($data);
                $this->view->form = $form;
                $title = "CAIXA DE ATENDIMENTO DA SEÇÃO";
                $this->view->title = $title;
                $redirectNs->title = $title;
                $this->view->ultima_pesq = true;
                return;
            }
        } else {
            $data = $Ns_Atendimentosecoes_atendimentousuario->data_pesq;
            if (!is_null($data)) {
                $dadosNivel = Zend_Json::decode($data['SNAT_CD_NIVEL']);
                $nivel = $dadosNivel["SNAT_CD_NIVEL"];
                $idNivel = $dadosNivel["SNAT_ID_NIVEL"];
                $idCaixa = $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"];
                $data_pesq = $data;
                $form->populate($data);
            } else {
                $nivel = $nivelDefault['SNAT_CD_NIVEL'];
                $idNivel = $nivelDefault['SNAT_ID_NIVEL'] ?: 1;
                $idCaixa = $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"];
                $data_pesq = array();
//                Zend_Debug::dump($nivelDefault);die;
                $form->populate(array(
                    'SNAT_CD_NIVEL' => Zend_Json::encode($nivelDefault)
                    , 'SOMENTE_PRINCIPAL' => 'N'
                    , 'SERVICO' => 'nomecompleto'
                ));
            }
        }

        /*         * ***************************************************************
         * Configuração das categorias
         * ************************************************************** */
        $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_ID_NIVEL = $idNivel")->toArray();
        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        foreach ($Categorias as $Categorias_p):
            $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
            $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
            $cont++;
        endforeach;
        $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));

        $CateNs = new Zend_Session_Namespace('CateNs');
        $CateNs->tipo = 3;
        $CateNs->identificador = $idNivel;
        $CateNs->controller = $this->getRequest()->getControllerName();
        $CateNs->action = $this->getRequest()->getActionName();
        /*         * ********************************************************************** */

        /**
         * Chamada da query da caixa
         */
        $consulta_caixa = $caixas->getCaixaComNivelPesq($idCaixa, $nivel, $data_pesq, $order);
        /**
         * Configuração do Paginador
         */
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        $paginator = new Zend_Paginator(new App_Paginator_Adapter_Sql_Oracle($consulta_caixa));
        $paginator->setCurrentPageNumber($varSessoes->getPage())
                ->setItemCountPerPage($varSessoes->getItemsperpage());

        /**
         * Variáveis para view
         */
        $title = "CAIXA DE ATENDIMENTO DA SEÇÃO";
        $this->view->vinc = $vinc;
        $this->view->idGrupo = $idGrupo;
        $this->view->title = $title;
        $redirectNs->title = $title;
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->form = $form;
        $this->view->modeIdCaixaEntrada = $idCaixa;
    }

    public function encaminhadasparatrf1Action() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itemCountPerPage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Atendimentosecoes_atendimentousuario = new Zend_Session_Namespace('Ns_Atendimentosecoes_atendimentousuario');

        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($userNs->siglasecao, $userNs->codlotacao);


        $idCaixa = $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"];
        $rows = $SosTbSsolSolicitacao->getCaixaEncamnhadosSecoesParaTrf($idCaixa, $order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemCountPerPage);

        $this->view->title = "CAIXA DE SOLICITAÇÕES ENCAMINHADAS DA SEÇÃO PARA O TRF1 -  EM ATENDIMENTO";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function baixarcaixaAction() {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        
        $form = new Sosti_Form_BaixarCaixa();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;	

        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

        if ($this->getRequest()->isPost()) {
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Baixar') {
                //$data = array_merge($this->getRequest()->getPost(),$form->populate($this->getRequest()->getPost())->getValues());/*Aplica Filtros - Mantem Post*/     
                $solicspace = new Zend_Session_Namespace('solicspace');
                $solicspace->dadosPost = $data;
                $userNs = new Zend_Session_Namespace('userNs');
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                $solicspace->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];

                $this->view->title = "ATENDIMENTO DA SEÇÃO - BAIXAR SOLICITAÇÃO(ÕES)";
                $this->view->form = $form;
            } else {
                $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
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
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];

                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                        $dataBaixa["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataBaixa["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataBaixa["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $dataBaixa["SNAS_ID_NIVEL"] = $dados_input["SNAS_ID_NIVEL"];
                        $dataBaixa["MODE_ID_CAIXA_ENTRADA"] = $dados_input["MODE_ID_CAIXA_ENTRADA"];
                        
                        $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $baixa->baixaSolicitacao($dataBaixa, $dados_input["SSOL_ID_DOCUMENTO"],$nrDocsRed);
                        
                        /**
                         * Envio de email de resposta
                         */
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                        $assunto = 'Baixa de Solicitação';
                        $corpo = "Uma solicitação foi baixada, será necessário acessar o sistema para avaliação.</p>
                                    Número da Solicitação: " . $dados_input['DOCM_NR_DOCUMENTO'] . " <br/>
                                    Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                    Atendente: " . $userNs->nome . " <br/>
                                    Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                    Descrição da Baixa: " . ($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        /**
                         * Fim do envio de email
                         */
                    }

                    $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " baixada(s)!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));

                    $this->_helper->_redirector($solicspace->dadosPost['action'], $solicspace->dadosPost['controller'], 'sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = "ATENDIMENTO DA SEÇÃO - BAIXAR SOLICITAÇÃO(ÕES)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('baixarcaixa');
                }
            }
        }
    }

    public function esperacaixaAction() {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        
        $form = new Sosti_Form_EsperaCaixa();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;

        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

        if ($this->getRequest()->isPost()) {
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Espera') {
                $userNs = new Zend_Session_Namespace('userNs');
                $solicspace = new Zend_Session_Namespace('solicspace');
                $solicspace->dadosPost = $data;
                $solicspace = new Zend_Session_Namespace('solicspace');
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                $solicspace->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $id = $solicitacao_array[0];
                $this->view->title = "ATENDIMENTO DA Seção - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
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
                    foreach ($solicspace->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                        $dataEspera["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataEspera["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataEspera["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $idDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
                        $limite = new Application_Model_DbTable_Dual();
                        $dataSespSolicEspera["SESP_DH_LIMITE_ESP"] = $limite->setEspera();
                        
                        $espera = new Application_Model_DbTable_SosTbSespSolicEspera();
                        $espera->esperaSolicitacao($idDocumento, $dataEspera, $dataSespSolicEspera,$nrDocsRed);
                        /**
                         * Envio de email de resposta
                         */
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dados_input["DOCM_CD_MATRICULA_CADASTRO"] . '@trf1.jus.br';
                        $assunto = 'Solicitação em Espera';
                        $corpo = "Sua solicitação foi colocada em espera.</p>
                                    Número da Solicitação: " . $dados_input['DOCM_NR_DOCUMENTO'] . " <br/>
                                    Data da Solicitação: " . $dados_input["DATA_ATUAL"] . " <br/>
                                    Atendente: " . $userNs->nome . " <br/>
                                    Tipo de Serviço Solicitado: " . $dados_input['SSER_DS_SERVICO'] . "<br/>
                                    Descrição da motivo: " . nl2br($dataEspera["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " colocada(s) em espera!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($solicspace->dadosPost['action'], $solicspace->dadosPost['controller'], 'sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = "ATENDIMENTO DA SEÇÃO - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('esperacaixa');
                }
            }
        }
    }

    public function encaminharAction() {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        
        $userNs = new Zend_Session_Namespace('userNs');
        $NsAction = new Zend_Session_Namespace('NsAction');

        $form = new Sosti_Form_AtendimentoSecoesEncaminhar();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $salvar = $formAnexo->getElement('Salvar');
        $salvar->setName('Encaminhar');
        $form->addElement($salvar);
        
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SadTbAtcxAtendenteCaixa = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

        /**
         * VARIAVEL DO GRUPO DE SERVIÇO PARA A RESPOSTA PADRÃO
         */
        $idGrupo_repd = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $solicNamespace = new Zend_Session_Namespace('solicitacaoNs');
        if ($this->getRequest()->isPost()) {
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Encaminhar') {
                $solicspace = new Zend_Session_Namespace('solicspace');
                $solicspace->dadosPost = $data;
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                $solicNamespace->dadosCaixa = $data;
                $solicNamespace->dadosSolicitacao = $data['solicitacao'];

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
                            $this->_helper->_redirector($solicspace->dadosPost['action'], $solicspace->dadosPost['controller'], 'sosti');
                            return;
                        }
                    }
                }

                /**
                 * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                 * SETANDO VALOR DO ID GRUPO NA SESSION
                 */
                $NsAction->idGrupo_repd = $idGrupo_repd;
                $form_resposta->set_idGrupo($idGrupo_repd);
                $form_resposta->escolheResposta();
                $this->view->formResposta = $form_resposta;

                /* Adicionar o grupo de gestão para a seção */
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao($dados_input['MODE_SG_SECAO_UNID_DESTINO'], $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
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

                foreach ($solicspace->dados as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                    $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                }
                $nrdocumento = substr($solicitacoesEncaminhadas, 1);
            } else {
                $dadosCaixa = $solicNamespace->dadosCaixa;

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
                            $solicspace = new Zend_Session_Namespace('solicspace');
                            $this->_helper->_redirector($solicspace->dadosPost['action'], $solicspace->dadosPost['controller'], 'sosti');
                            return;
                        }
                    }
                }

                /* Adicionar o grupo de gestão para a seção */
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao($dados_input['MODE_SG_SECAO_UNID_DESTINO'], $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
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
                    $acompanhar = $data["ACOMPANHAR"];
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
                    /* NIVEL */
                    if ($data["ENCAMINHAMENTO"] == 'nivel') {
                        $solicspace = new Zend_Session_Namespace('solicspace');
                        if ($data["SNAS_ID_NIVEL"] && isset($data["SNAS_ID_NIVEL"])) {
                            $dadosCaixa = $solicNamespace->dadosCaixa;
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
                            $solicspace = new Zend_Session_Namespace('solicspace');
                            $this->_helper->_redirector($solicspace->dadosPost['action'], $solicspace->dadosPost['controller'], 'sosti');
                        }

                        /* GRUPO */
                    } else if ($data["ENCAMINHAMENTO"] == 'trf') {
                        $solicspace = new Zend_Session_Namespace('solicspace');
                        $dadosCaixa = $solicNamespace->dadosCaixa;
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
                                /* INICIO ENVIAR EMAIL ######################################################### */
                                if ($destino['SGRS_SG_SECAO_LOTACAO'] != 'TR' && $destino["TPCX_ID_TIPO_CAIXA"] == 7) {
                                    $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                                    $arrayMatriculasPerfilUnidade = $ocsTbPupePerfilUnidPessoa->getMatriculasPossuiPerfilUnidade(
                                            'GESTÃO DE DEMAD. ATENDIMENTO AO USUÁRIO SEÇÕES'
                                            , $destino['SGRS_SG_SECAO_LOTACAO']
                                            , $destino['SGRS_CD_LOTACAO']);

                                    $app_Email = new App_Email();
                                    foreach ($arrayMatriculasPerfilUnidade as $matriculaPerfilUnidade):
                                        $arrayDados = array(
                                            'destinatario' => $matriculaPerfilUnidade['PMAT_CD_MATRICULA']
                                            , 'solicitacao' => $dados_input['DOCM_NR_DOCUMENTO']
                                            , 'dataSolicitacao' => $dados_input['DATA_ATUAL']
                                            , 'secao' => $destino['SGRS_SG_SECAO_LOTACAO']
                                            , 'descricao' => $data["MOFA_DS_COMPLEMENTO"]);

                                        $app_Email->encaminharSolicitacao($arrayDados, false);
                                    endforeach;
                                }
                                /* FIM ENVIAR EMAIL ############################################################ */
                                $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                                $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                            }
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $erro = $exc->getMessage();
                            $msg_to_user = "Ocorreu um erro ao encaminhar a solicitação! <br/> $erro ";
                            $solicspace = new Zend_Session_Namespace('solicspace');
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($solicspace->dadosPost['action'], $solicspace->dadosPost['controller'], 'sosti');
                        }
                        $db->commit();

                        /**
                         * Envio de Emails
                         */
                        if ($destino["SGRS_ID_GRUPO"] == 4 && $data["SSER_ID_SERVICO"] == '6071|N|S') {
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

                        $solicspace = new Zend_Session_Namespace('solicspace');
                        $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " encaminhada(s)!";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        $this->_helper->_redirector($solicspace->dadosPost['action'], $solicspace->dadosPost['controller'], 'sosti');

                        /* PESSOAL */
                    } else if ($data["ENCAMINHAMENTO"] == 'pessoal') {
                        $solicspace = new Zend_Session_Namespace('solicspace');
                        $dataSol = $solicNamespace->dadosCaixa;
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
                        $this->_helper->_redirector($solicspace->dadosPost['action'], $solicspace->dadosPost['controller'], 'sosti');
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
                    $this->view->data = $solicNamespace->dadosSolicitacao;
                    $pop['LOTA_COD_LOTACAO'] = strtoupper($userNs->siglalotacao) . ' - ' . strtoupper($userNs->descicaolotacao) . ' - ' . strtoupper($userNs->codlotacao);
                    $form->populate($pop);
                    $this->view->form = $form;
                }
            }
        }
        $this->view->title = "ATENDIMENTO DA SEÇÃO - ENCAMINHAR SOLICITAÇÃO(ES)";
        $this->view->data = $solicNamespace->dadosSolicitacao;
        $this->view->form = $form;
    }

    public function ajaxunidadeAction() {
        $userNamespace = new Zend_Session_Namespace('userNs');
        $unidade = $this->_getParam('term', '');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade, $userNamespace->siglasecao);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxpessoaAction() {
        $lota_cod_lotacao = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $OcsTbApspPapSistUnidPess = new Application_Model_DbTable_OcsTbApspPapSistUnidPess();
        $PapSistUnidPess_array = $OcsTbApspPapSistUnidPess->getPessoa($lota_cod_lotacao);
        $this->view->PapSistUnidPess_array = $PapSistUnidPess_array;
    }

    /**
     * 
     * Caixa de unidade central...
     */
    public function caixaunidadecentralAction() {
        /**
         * Definições
         */
        $idCaixa = explode(',', "5,6,18,7,8,9,10,11,12,13,14,15,16,17");
        $idNivel = null;
        $cdNivel = 2;
        $idGrupo = null;
        $title = "CAIXA DE ATENDIMENTO DA SEÇÃO CENTRALIZADO";
        $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 50, 'page' => 1);
        ;
        $populate_padrao = array(
            "SNAT_CD_NIVEL" => $cdNivel,
            "MODE_ID_CAIXA_ENTRADA" => $idCaixa
        );
        
       /*
        * Criando uma sessao com a matricula do usuario logado.
        */     
       $userNs = new Zend_Session_Namespace('userNs');
        $this->view->userNs = $userNs->matricula;
        /**
         * Paginação inicialização e configuração
         */
        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);
        /* Ordenação das paginas */
        $order_column = $varSessoes->getOrdem();
        $order_direction = $varSessoes->getDirecao();
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /**
         * Models
         */
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        /**
         * Form
         */
        $form = new Sosti_Form_CaixaunidadeCentralpesq();
        /**
         * Variáveis de Sessão
         */
        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);
        /**
         * Negócio & Classes da aplicação
         */
        $caixas = new Trf1_Sosti_Negocio_Caixas_Caixa();

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
        $snat_cd_nivel = $form->getElement('SNAT_CD_NIVEL');
        $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento ();
        $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisGrupoServicoAtendimentoUsuPorLotacao('MG', 10);
        $snat_cd_nivel->addMultiOptions(array('' => ''));
        foreach ($NivelAtendimento as $NivelAtendimento_p) :
            $snat_cd_nivel->addMultiOptions(array($NivelAtendimento_p ["SNAT_CD_NIVEL"] => $NivelAtendimento_p ["SNAT_DS_NIVEL"]));
        endforeach;
        $form->populate($populate_padrao);

        if ($this->_getParam('nova') === '1') {
            unset($NsAction->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();

            if (count($data_pesq["MODE_ID_CAIXA_ENTRADA"]) == 1) {
                $dadosGrupo = $SadTbCxgsGrupoServico->fetchRow("CXGS_ID_CAIXA_ENTRADA = " . $data_pesq['MODE_ID_CAIXA_ENTRADA'][0])->toArray();
                $dadosNivel = $SosTbSnatNivelAtendimento->fetchRow("SNAT_ID_GRUPO = " . $dadosGrupo["CXGS_ID_GRUPO"])->toArray();
                $idNivel = $dadosNivel['SNAT_ID_NIVEL'];
                $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
                $Categorias = $cateCategoria->fetchAll("CATE_ID_NIVEL = $idNivel", 2)->toArray();
                $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
                foreach ($Categorias as $Categorias_p):
                    $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
                endforeach;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $NsAction->data_pesq = $this->getRequest()->getPost();
            } else {
                /**
                 * Populando o formulário inválido
                 */
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = $title;
                return;
            }
        }

        $data_pesq = $NsAction->data_pesq;
        $post_data_pesq = $data_pesq;

        if (!is_null($data_pesq)) {
            $this->view->ultima_pesq = true;

            $mat_aux = explode(' - ', $data_pesq["DOCM_CD_MATRICULA_CADASTRO_VALUE"]);
            $data_pesq["DOCM_CD_MATRICULA_CADASTRO"] = $mat_aux[0];
            $mat_atend_aux = explode(' - ', $data_pesq["SSOL_CD_MATRICULA_ATENDENTE_VALUE"]);
            $data_pesq["SSOL_CD_MATRICULA_ATENDENTE"] = $mat_atend_aux[0];
            $unid_aux = explode(' - ', $data_pesq["DOCM_CD_LOTACAO_GERADORA_VALUE"]);
            ( array_key_exists(2, $unid_aux) ) ? ($data_pesq["DOCM_CD_LOTACAO_GERADORA"] = $unid_aux[2]) : '';
            ( array_key_exists(3, $unid_aux) ) ? ($data_pesq["DOCM_SG_SECAO_GERADORA"] = $unid_aux[3]) : '';

            if (!empty($data_pesq ['SNAT_CD_NIVEL'])) {
                $cdNivel = $data_pesq ['SNAT_CD_NIVEL'];
            }
            if (is_array($data_pesq['MODE_ID_CAIXA_ENTRADA'])) {
                $idCaixa = $data_pesq['MODE_ID_CAIXA_ENTRADA'];
            }

            /**
             * Chama o método padrão da caixa com filtro
             */
            $consulta_caixa = $caixas->getCaixaComNivelPesq($idCaixa, $cdNivel, $data_pesq, $order);


            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
            $paginator = new Zend_Paginator(new App_Paginator_Adapter_Sql_Oracle($consulta_caixa));
            $paginator->setCurrentPageNumber($varSessoes->getPage())
                    ->setItemCountPerPage($varSessoes->getItemsperpage());


            /**
             * Tratando o Form
             */
            if (count($data_pesq["MODE_ID_CAIXA_ENTRADA"]) == 1) {
                $dadosGrupo = $SadTbCxgsGrupoServico->fetchRow("CXGS_ID_CAIXA_ENTRADA = " . $data_pesq['MODE_ID_CAIXA_ENTRADA'][0])->toArray();
                $dadosNivel = $SosTbSnatNivelAtendimento->fetchRow("SNAT_ID_GRUPO = " . $dadosGrupo["CXGS_ID_GRUPO"])->toArray();
                $idNivel = $dadosNivel['SNAT_ID_NIVEL'];
                $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
                $Categorias = $cateCategoria->fetchAll("CATE_ID_NIVEL = $idNivel", 2)->toArray();
                $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
                $cont = 0;
                $cores = array();
                foreach ($Categorias as $Categorias_p):
                    $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
                    $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
                    $cont++;
                endforeach;
                $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));

                /*                 * ***************************************************************
                 * Configuração das categorias
                 * ************************************************************** */
                $this->view->categorias = $Categorias;
                $CateNs = new Zend_Session_Namespace('CateNs');
                $CateNs->tipo = 3;
                $CateNs->identificador = $idNivel;
                $CateNs->controller = $this->getRequest()->getControllerName();
                $CateNs->action = $this->getRequest()->getActionName();
                $this->view->categoria = 'categoria';
                $this->view->atendentes = 'atendentes';

                /*
                 * Regra Negocial - Resposta Padrao
                 * Armazena o Id do Grupo para fazer a validação no cadastro das Respostas Pdrões
                 */
                $services_sosti_respostapadrao = new Services_Sosti_RespostaPadrao();
                $services_sosti_respostapadrao->setIdGrupo($dadosGrupo["CXGS_ID_GRUPO"]);

                //VARIAVEIS DA VIEW
                $this->view->idGrupo = ($dadosGrupo["CXGS_ID_GRUPO"] != "") ? $dadosGrupo["CXGS_ID_GRUPO"] : 0;
            }

            $form->populate($post_data_pesq);
        } else {
            /**
             * Chama o método padrão da caixa com filtro
             */
            $consulta_caixa = $caixas->getCaixaComNivelPesq($idCaixa, $cdNivel, array(), $order);
            /**
             * Configuração do Paginador
             */
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
            $paginator = new Zend_Paginator(new App_Paginator_Adapter_Sql_Oracle($consulta_caixa));
           
            $paginator->setCurrentPageNumber($varSessoes->getPage())
                    ->setItemCountPerPage($varSessoes->getItemsperpage());


        }
        foreach ($paginator->getCurrentItems() as $sosti) {
            if ($sosti['VINCULADA'] > 0) {
                $vincs = $caixas->getVinculos($sosti["SSOL_ID_DOCUMENTO"]);
                foreach($vincs as $vincRow){
//                    $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq($idCaixa, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
                    $queryCaixa = $caixas->getCaixaSemNivelPesq($idCaixa, null, $order, $vincRow["VIDC_ID_DOC_VINCULADO"]);
                    if(is_string($queryCaixa))
                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getData($queryCaixa);
                    else
                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq($idCaixa, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
                }
            }
        }
        $this->view->vinc = $vinc;
        $this->view->title = $title;
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        $this->view->form = $form;
    }

}
