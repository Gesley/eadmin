<?php
/**
 * Associa solicitações de TI a OS já cadastradas.
 */
class Os_AssociarController extends Zend_Controller_Action 
{

    public function indexAction()
    {
        
    }

    public function pesquisarAction()
    {
        $allParams = $this->_getAllParams();
        $vinculos = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $osSolicitacao = new Os_Model_DataMapper_Solicitacao();
        $urlOrigem = explode('-', $allParams["urlOrigem"]);
        $this->view->title = "PESQUISAR ORDEM DE SERVIÇO";
        $form = new Sosti_Form_CaixaSolicitacao();
        $calculaTempo = new App_View_Helper_Calculahorasla();
        /** Verifica se existe alguma OS associada as solicitações escolhidas. */
        $verificaSeOs = Os_Model_DataMapper_Solicitacao::vericaOsCadastrada($allParams["solicitacao"] ?: array());
        if ($verificaSeOs === true) {
            $this->_helper->flashMessenger(array('message' => App_Sosti_Message::msg('msg013'), 'status' => 'notice'));
            $this->_helper->_redirector($urlOrigem[2], $urlOrigem[1], $urlOrigem[0]);
        }
        /**
         * Adiciona a máscara do formato de exibição do tempo.
         */
        foreach ($allParams["solicit_escolhidas"] as $i=>$sos) {
            $arraySolicit = Zend_Json::decode($sos);
            $arraySostis[] = $arraySolicit;
            $prazo = $calculaTempo->calculahorasla($arraySolicit);
            $tempoTotal = $prazo['prazo_total'][0] . "D " . $prazo['prazo_total'][1] . "h " . $prazo['prazo_total'][2] .
                    "m " . $prazo['prazo_total'][3] . "s";
            $arraySostis[$i]['PRAZO'] = $tempoTotal;
        }

        $this->view->filtro = $allParams["Filtrar2"];
        
        if ($allParams["solicit_escolhidas"] > 0) {
            $this->view->arraySostisJson = $allParams["solicit_escolhidas"];
        } else {
            $this->view->arraySostisJson = $allParams["solicitacao"];
        }
        $this->view->arraySostis = $arraySostis;
        /**
         * Definições
         */
        $idCaixa = Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA;
        $idGrupo = 2;
        $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 50, 'page' => 1);
        $tipoCategorizacao = 1;

        $userNs = new Zend_Session_Namespace('userNs');
        $this->view->userNs = $userNs->matricula;
        
        // levando o perfil do usuario para a view
        $responsavelPelacaixa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $devincularCaixa = $responsavelPelacaixa->getPerfiRespnsavelCaixaUnidade($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula );
        $idPerfil = $devincularCaixa[0]['PERF_ID_PERFIL'];
        $this->view->idPerfil = $idPerfil;
        
        if($this->_getParam('itemsperpage')) {
            $this->view->itemsperpage = $this->_getParam('itemsperpage');
        }
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
         * Variáveis de Sessão
         */
        $NsActionName = $this->getRequest()->getModuleName() . $this->getRequest()->getControllerName() . $this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);
        /**
         * Negócio & Classes da aplicação
         */
        $caixas = new Trf1_Sosti_Negocio_Caixas_Caixa();
        /**
         * Tratando o Form
         */
        $form->removeElement('SGRS_ID_GRUPO');
        $form->removeElement('SSOL_NM_USUARIO_EXTERNO');
        $form->removeElement('SOMENTE_PRINCIPAL');
        
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->getServicoPorGrupo($idGrupo, 'SSER_DS_SERVICO ASC');
        $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
        $sser_id_servico->addMultiOptions(array('' => ''));
        foreach ($SserServico as $SserServico_p):
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        endforeach;
        /**
         * Regra Negocial - Resposta Padrao
         * Armazena o Id do Grupo para fazer a validação no cadastro das Respostas Pdrões
         */
        $rn_resposta_padrao = new Services_Sosti_RespostaPadrao();
        $rn_resposta_padrao->setIdGrupo($idGrupo);
        $this->view->idGrupo = $idGrupo;

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

        $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_ID_GRUPO = $idGrupo", '2')->toArray();
        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        $cores = array();
        if ($Categorias) {
            foreach ($Categorias as $Categorias_p):
                $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
                $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
                $cont++;
            endforeach;
            $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
            $this->view->categorias = $Categorias;
        }

        /* ****************************************************************
         * Configuração das categorias
         * ************************************************************** */
        $CateNs = new Zend_Session_Namespace('CateNs');
        $CateNs->tipo = $tipoCategorizacao;
        $CateNs->identificador = $idCaixa;
        $CateNs->idGrupo = $idGrupo;
        $CateNs->controller = $this->getRequest()->getControllerName();
        $CateNs->action = $this->getRequest()->getActionName();
        /**
         * Para zerar o filtro
         */
        if ($this->_getParam('nova') === '1') {
            unset($NsAction->data_pesq);
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

            /**
             * Validação de filtro Vazio
             */
            $form->populate($data_pesq);
            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $NsAction->data_pesq = $data_pesq;
            } else {
                /**
                 * Populando o formulário inválido
                 */
                $form->populate($data_pesq);
                $this->view->form = $form;
                return;
            }
        }

        /*
         * Aplicação do filtro caso ele seja válido
         */
        $data_pesq = $NsAction->data_pesq;
        $post_data_pesq = $data_pesq;

        if (!is_null($data_pesq)) {
            /**
             * Auxilia a view a tratar a ausencia de registros e esconder botões
             */
            $this->view->ultima_pesq = true;
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

            $consulta_caixa = $caixas->getCaixaSemNivelPesq($idCaixa, $data_pesq, $order);
            /**
             * Configura o Zend paginator
             */
            $paginator = new Zend_Paginator(new App_Paginator_Adapter_Sql_Oracle($consulta_caixa));
            $paginator->setCurrentPageNumber($varSessoes->getPage())
                    ->setItemCountPerPage($varSessoes->getItemsperpage());
            foreach ($paginator->getCurrentItems() as $sosti) {
                if ($sosti['VINCULADA'] > 0) {
                    $vincs = $caixas->getVinculos($sosti["SSOL_ID_DOCUMENTO"]);
                    foreach($vincs as $vincRow){
                        $row = $caixas->getCaixaSemNivelPesq($idCaixa, null, $order, $vincRow["VIDC_ID_DOC_VINCULADO"]);
                        if(is_object($row))
                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $row->getData();
                        else
                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getData($row);
                    }
                }
            }
            $this->view->vinc = $vinc;

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
    }
    
    public function saveAction()
    {
        $data = $this->_getAllParams();
        $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $solicitacaoOs = new Os_Model_DataMapper_Solicitacao();
        $os = Zend_Json::decode($data['solicitacao'][0]);
        $arrayJasonSosti = $data['solicit_escolhidas'];
        /**
         * Envia o tipo de vinculação = 7 para a model lançar a vinculação
         */
        $arrayRows = array();
        foreach ($arrayJasonSosti as $k=>$rowsJson) {
            $rows = Zend_Json::decode($rowsJson);
            $ultimaFase = $ssolSolicitacao->getHistoricoSolicitacao($rows['SSOL_ID_DOCUMENTO']);
            $arrayRows[$k] = $ultimaFase[0];
        }
        $solicitacaoOs->setVincularSolicitacao(
            $arrayRows, 
            $os['SSOL_ID_DOCUMENTO'], 
            'Vinculação de solicitação a OS.',
            7
        );
        $this->_helper->flashMessenger(array('message' => App_Sosti_Message::msg('msg033'), 'status' => 'notice'));
        $this->_helper->_redirector('index', 'gestaodedemandasti', 'sosti');
    }

}
