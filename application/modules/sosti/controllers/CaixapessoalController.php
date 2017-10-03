<?php

class Sosti_CaixapessoalController extends Zend_Controller_Action {

    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch () {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->closeConnection();
    }

    public function init () {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        $this->view->titleBrowser = 'e-Sosti';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function indexAction () {
        
    }

    /**
     * Pode renderizar várias VIEWS
     *
     */
    public function entradaAction () {
        //controle de renderização da view com SLA
        //necessidade pois nem todos os contratos possui tal SLA
        //valido somente para caixa pessoal da equipe da STEFANINI
        $renderizarViewComSLA = false;
        //model da caixa da unidade
        $sadTbCxenCaixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $userNs = new Zend_Session_Namespace('userNs');
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        //formulário de filtro
        $form = new Sosti_Form_CaixaSolicitacao();
        $Ns_Caixapessoalsosti_index = new Zend_Session_Namespace('Ns_Caixapessoalsosti_index');
        $form->removeElement('SGRS_ID_GRUPO');
        $form->removeElement('SSOL_CD_MATRICULA_ATENDENTE');
        $form_valores_padrao = $form->getValues();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();

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
        $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_CD_MATRICULA_CATEGORIA = '$userNs->matricula'", $order = 2);
        $Categorias = $Categorias->toArray();
        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        $cores = array();
        foreach ($Categorias as $Categorias_p):
            $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
            $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
            $cont++;
        endforeach;
        $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
        $this->view->categorias = $Categorias;

        $uri = $_SERVER['REQUEST_URI'];
        $end = explode('/sosti/', $uri);
        $end = explode('/', $end[1]);

        $CateNs = new Zend_Session_Namespace('CateNs');
        $CateNs->tipo = 2;
        $CateNs->identificador = $userNs->matricula;
        $CateNs->controller = $end[0];
        if (isset($end[1])) {
            $CateNs->action = $end[1];
        } else {
            $CateNs->action = 'index';
        }

        if ($this->_getParam('nova') === '1') {
            unset($Ns_Caixapessoalsosti_index->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        //Verifica se possui mais de uma caixa Pessoal dentre as solicitações do atendente
        if ($dados->hasVariasCaixasPessoaisSolicitacoes($userNs->matricula)) {

            //aparece o campo select de escolha de caixa pessoal dentro do filtro
            //com o valor das caixas pessoais
            $arrayCaixasPessoais = $dados->getCaixasPessoaisSolicitacoes($userNs->matricula);
            //Elemento Select para escolher a caixa pessoal

            $elementoIdCaixa = new Zend_Form_Element_Select('MODE_ID_CAIXA_ENTRADA');
            $elementoIdCaixa->setLabel('Escolha a caixa pessoal:')
                ->setAttrib('style', 'width: 547px;')
                ->addMultiOption(0, 'TODAS AS CAIXAS PESSOAIS');
            $elementoIdCaixa->setOrder(0);
            $form->addElement($elementoIdCaixa);

            foreach ($arrayCaixasPessoais as $caixaPessoal) {
                //adiciona a caixa pessoal no option do select
                $form->MODE_ID_CAIXA_ENTRADA->addMultiOption($caixaPessoal['CXEN_ID_CAIXA_ENTRADA'], $caixaPessoal['CXEN_DS_CAIXA_ENTRADA']);
                //se tiver a caixa de desenvolvimento e sustentação da primeira instância do trf
                if ($caixaPessoal['CXEN_ID_CAIXA_ENTRADA'] == Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA) {
                    $form->MODE_ID_CAIXA_ENTRADA->setValue(Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA);
                    $renderizarViewComSLA = true;
                }
            }
            //se não tiver a caixa de desenvolvimento e sustentação da primeira instância do trf entre as caixas pessoais
            if ($renderizarViewComSLA == false) {
                //marca o valor default que é o 0
                $form->MODE_ID_CAIXA_ENTRADA->setValue(0);
            }
        } else {

            //não aparece o campo select de escolha de caixa pessoal dentro do filtro
            $form->removeElement('MODE_ID_CAIXA_ENTRADA');
        }

        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();

            /**
             * Validação de filtro Vazio
             */
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $this->view->title = "CAIXA PESSOAL - " . $userNs->nome;
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            $da_caixa = $Ns_Caixapessoalsosti_index->id_da_caixa;

            if (isset($da_caixa)) {
                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorIdCaixaAtendimento($da_caixa, 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                $sser_id_servico->addMultiOptions(array('' => ''));
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            if ($form->isValid($data_pesq)) {
                $Ns_Caixapessoalsosti_index->data_pesq = $this->getRequest()->getPost();
            } else {
                /**
                 * Populando o formulário inválido
                 */
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "CAIXA PESSOAL - " . $userNs->nome;
                return;
            }
        }

        $data_pesq = $Ns_Caixapessoalsosti_index->data_pesq;
        //$post_data_pesq = $data_pesq;
        //Se for utilizado o filtro de solicitações
        if (!is_null($data_pesq)) {

            $this->view->ultima_pesq = true;
            $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());

            $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'TEMPO_TOTAL', 'itemsperpage' => 50, 'page' => 1);

            $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);
            /* paginação */
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            //Zend_Debug::dump($_SESSION);exit;
            /* Ordenação das paginas */
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /* Ordenação */

            $mat_aux = explode(' - ', $data_pesq["DOCM_CD_MATRICULA_CADASTRO_VALUE"]);
            $data_pesq["DOCM_CD_MATRICULA_CADASTRO"] = $mat_aux[0];

            $mat_atend_aux = explode(' - ', $data_pesq["SSOL_CD_MATRICULA_ATENDENTE_VALUE"]);
            $data_pesq["SSOL_CD_MATRICULA_ATENDENTE"] = $mat_atend_aux[0];

            $unid_aux = explode(' - ', $data_pesq["DOCM_CD_LOTACAO_GERADORA_VALUE"]);
            ( array_key_exists(2, $unid_aux) ) ? ($data_pesq["DOCM_CD_LOTACAO_GERADORA"] = $unid_aux[2]) : '';
            ( array_key_exists(3, $unid_aux) ) ? ($data_pesq["DOCM_SG_SECAO_GERADORA"] = $unid_aux[3]) : '';

            //$userNs = new Zend_Session_Namespace('userNs');
            //$dados = new Application_Model_DbTable_SosTbSsolSolicitacao();

            if (isset($data_pesq['MODE_ID_CAIXA_ENTRADA']) && ($dados->hasCaixaPessoalSolicitacao($data_pesq['MODE_ID_CAIXA_ENTRADA'], strtoupper($userNs->matricula)) || $data_pesq['MODE_ID_CAIXA_ENTRADA'] == 0)) {
                $idCaixaPessoal = $data_pesq['MODE_ID_CAIXA_ENTRADA'];
                if ($idCaixaPessoal == Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA) {
                    $renderizarViewComSLA = true;
                } else {
                    $renderizarViewComSLA = false;
                }
                //por default se tiver a caixa desenvolvimento e sustentação da primeira instância do trf
            } else {
                if ($dados->hasCaixaPessoalSolicitacao(Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA, strtoupper($userNs->matricula))) {
                    $renderizarViewComSLA = true;
                    $idCaixaPessoal = Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA;
                } else {
                    $renderizarViewComSLA = false;
                    //default é 0 ou seja todas as solicitações
                    $idCaixaPessoal = 0;
                }
            }

            $rows = $dados->getCaixaPessoalPesq($idCaixaPessoal, strtoupper($userNs->matricula), $data_pesq, $order);

            if (isset($rows[0]['MODE_ID_CAIXA_ENTRADA'])) {
                $Ns_Caixapessoalsosti_index->id_da_caixa = $rows[0]['MODE_ID_CAIXA_ENTRADA'];
                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorIdCaixaAtendimento($Ns_Caixapessoalsosti_index->id_da_caixa, 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                $sser_id_servico->addMultiOptions(array('' => ''));
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }


            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemCountPerPage);

            foreach ($paginator->getCurrentItems() as $sosti) {
                if ($sosti['VINCULADA'] > 0) {
                    $vincs = $caixas->getVinculos($sosti["SSOL_ID_DOCUMENTO"]);
                    foreach ($vincs as $vincRow) {
                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $SosTbSsolSolicitacao->getDadosSolicitacao($vincRow["VIDC_ID_DOC_VINCULADO"]);

//                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq(2, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
//                        $queryCaixa = $caixas->getCaixaSemNivelPesq(2, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"]);
//                        if(is_string($queryCaixa))
//                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getData($queryCaixa);
//                        else
//                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq(2, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
                    }
                }
            }

            $this->view->vinc = $vinc;
            $solicspace = new Zend_Session_Namespace('solicspace');
            $solicspace->label = "CAIXA PESSOAL";
            $this->view->title = "CAIXA PESSOAL - " . $userNs->nome;
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        } else {
            $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());

            $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'TEMPO_TOTAL', 'itemsperpage' => 50, 'page' => 1);

            $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);
            /* paginação */
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            //Zend_Debug::dump($_SESSION);exit;
            /* Ordenação das paginas */
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /* Ordenação */

            //valor default para utilizar a função de pesquisa
            $data_pesq["SOMENTE_PRINCIPAL"] = "N";

            //Verifica se tem a caixa desenvolvimento e sustentação da primeira instância do trf
            if ($dados->hasCaixaPessoalSolicitacao(Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA, strtoupper($userNs->matricula))) {
                $idCaixaPessoal = Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA;
                //por default se tiver a caixa desenvolvimento e sustentação da primeira instância do trf
                //ela deve vir já marcada e renderizar a view
                $renderizarViewComSLA = true;
            } else {
                //default é 0 ou seja todas as solicitações
                $idCaixaPessoal = 0;
                $renderizarViewComSLA = false;
            }

            $rows = $dados->getCaixaPessoalPesq($idCaixaPessoal, strtoupper($userNs->matricula), $data_pesq, $order);
//        Zend_Debug::dump($rows);exit;

            if (isset($rows[0]['MODE_ID_CAIXA_ENTRADA'])) {
                $Ns_Caixapessoalsosti_index->id_da_caixa = $rows[0]['MODE_ID_CAIXA_ENTRADA'];
                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorIdCaixaAtendimento($Ns_Caixapessoalsosti_index->id_da_caixa, 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                $sser_id_servico->addMultiOptions(array('' => ''));
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            /**
             * Configura o Zend paginator
             */
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemCountPerPage);
            foreach ($paginator->getCurrentItems() as $sosti) {
                if ($sosti['VINCULADA'] > 0) {
                    $vincs = $caixas->getVinculos($sosti["SSOL_ID_DOCUMENTO"]);
                    foreach ($vincs as $vincRow) {
                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $SosTbSsolSolicitacao->getDadosSolicitacao($vincRow["VIDC_ID_DOC_VINCULADO"]);

//                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq(2, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
//                        $queryCaixa = $caixas->getCaixaSemNivelPesq(2, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"]);
//                        if (is_string($queryCaixa))
//                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getData($queryCaixa);
//                        else
//                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq(2, $data_pesq, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
                    }
                }
            }
//            Zend_Debug::dump($vinc);die;
            $this->view->vinc = $vinc;
            $solicspace = new Zend_Session_Namespace('solicspace');
            $solicspace->label = "CAIXA PESSOAL";
            $this->view->title = "CAIXA PESSOAL - " . $userNs->nome;
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        }

        $this->view->form = $form;
        $this->view->title = "CAIXA PESSOAL - " . $userNs->nome;

        //se a caixa solicitação for desenvolvimento e sustentação da primeira instância do trf

        if ($idCaixaPessoal != 0) {

            //VERIFICAÇÃO DO ATUAL GRUPO DA CAIXA
            $grupoAtendimento = $SadTbCxgsGrupoServico->getGrupoAtendimentoByCaixa($idCaixaPessoal);
            $idGrupo = $grupoAtendimento[0]['SGRS_ID_GRUPO'];
            /*
             * Regra Negocial - Resposta Padrao
             * Armazena o Id do Grupo para fazer a validação no cadastro das Respostas Pdrões
             */
            $services_sosti_respostapadrao = new Services_Sosti_RespostaPadrao();
            $services_sosti_respostapadrao->setIdGrupo($idGrupo);
            $this->view->idGrupo = $idGrupo;

            $dadosCaixa = $sadTbCxenCaixaEntrada->fetchRow('CXEN_ID_CAIXA_ENTRADA = ' . $idCaixaPessoal);
            if (is_null($dadosCaixa)) {
                $dadosCaixa = array('CXEN_DS_CAIXA_ENTRADA' => 'CAIXA NÃO IDENTIFICADA');
            } else {

                $dadosCaixa = $dadosCaixa->toArray();
            }
            $this->view->nomeCaixa = $dadosCaixa['CXEN_DS_CAIXA_ENTRADA'];
        } else {
            $idGrupo = '0';
            $this->view->idGrupo = $idGrupo;
            $this->view->nomeCaixa = null;
        }
        if ($renderizarViewComSLA) {
            $this->render('entradacomprazo');
        }
    }

    public function baixarcaixaAction () {
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

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Baixar') {
                $userNs = new Zend_Session_Namespace('userNs');
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
                $this->view->title = $solicspace->label . " - BAIXAR SOLICITAÇÃO(ES)";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $form->ANEXOS->receive();
                    $nrDocsRed = null;
                    if (!is_null($data["ANEXOS"])/* $form->ANEXOS->isReceived() */) {
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
                        $dataBaixa["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataBaixa["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataBaixa["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        $dataBaixa["SNAS_ID_NIVEL"] = $dados_input["SNAS_ID_NIVEL"];
                        $dataBaixa["MODE_ID_CAIXA_ENTRADA"] = $dados_input["MODE_ID_CAIXA_ENTRADA"];

                        $baixa = new Application_Model_DbTable_SosTbSsolSolicitacao();
                        $baixa->baixaSolicitacao($dataBaixa, $dados_input["SSOL_ID_DOCUMENTO"], $nrDocsRed);
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
                                    Descrição da Baixa: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " baixada(s)!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector('entrada', 'caixapessoal', 'sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = $solicspace->label . " - BAIXAR SOLICITAÇÃO(ÕES)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('baixarcaixa');
                }
            }
        }
    }

    public function esperacaixaAction () {
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
        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Espera') {
                $userNs = new Zend_Session_Namespace('userNs');
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
                $this->view->title = $solicspace->label . " - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $form->ANEXOS->receive();
                    $nrDocsRed = null;
                    if (!is_null($data["ANEXOS"])/* $form->ANEXOS->isReceived() */) {
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
                        $espera->esperaSolicitacao($idDocumento, $dataEspera, $dataSespSolicEspera, $nrDocsRed);
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
                    $this->_helper->_redirector('entrada', 'caixapessoal', 'sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = $solicspace->label . " - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('esperacaixa');
                }
            }
        }
    }

    public function encaminharAction () {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_AtendimentoClienteEncaminhar();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $salvar = $formAnexo->getElement('Salvar');
        $salvar->setName('Encaminhar');
        $form->addElement($salvar);

        $SadTbAtcxAtendenteCaixa = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $solicitacaoNs = new Zend_Session_Namespace('solicitacaoNs');
        $solicspace = new Zend_Session_Namespace('solicspace');

        /**
         * VARIAVEL DA SESSION
         */
        $NsAction = new Zend_Session_Namespace('NsAction');
        /**
         * VARIAVEL DO GRUPO DE SERVIÇO PARA A RESPOSTA PADRÃO
         */
        $idGrupo_repd = array();
        /**
         * FORMULÁRIO RESPOSTA PADRÃO
         */
        $form_resposta = new Sosti_Form_RespostaPadrao();

        $encaminhamento = $form->getElement('ENCAMINHAMENTO');
        $encaminhamento->setMultiOptions(array(
            'nivel' => 'Outro nível de atendimento',
            'pessoal' => 'Caixa pessoal',
            'trf' => 'Outro Grupo de Atendimento',
        ));

        if ($this->getRequest()->isPost()) {

            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Encaminhar') {
                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($data['solicitacao']);
                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }
                $solicitacaoNs->dadosCaixa = $data;
                $solicitacaoNs->dadosSolicitacao = $data['solicitacao'];

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                foreach ($data["solicitacao"] as $d) {
                    $dados_input = Zend_Json::decode($d);
                    $idServico = $dados_input["SSER_ID_SERVICO"];
                    $cdNivelCaixa = $dados_input["SNAT_CD_NIVEL"];
                    $idCaixa = $dados_input["MODE_ID_CAIXA_ENTRADA"];

                    /**
                     * Validação
                     * Não permitir autoencaminhamento
                     * Validação de integridade dos SLAS
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
                            $this->_helper->_redirector('entrada', 'caixapessoal', 'sosti');
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

                /*                 * *tratamento de encaminhamentos */
                /**
                 * Validação
                 * Não permitir autoencaminhamento
                 * Validação de integridade dos SLAS
                 */
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                foreach ($arr_sgrs_id_grupo as $value) {
                    $value_option = Zend_Json::decode($value);
                    if ($value_option["SGRS_ID_GRUPO"] == $idGrupo) {
                        $sgrs_id_grupo->removeMultiOption($value);
                    }
                }

                /**
                 * Restrições de permissão por grupos de serviço
                 */
                $SosTbSgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico();
                $row = $SosTbSgrsGrupoServico->find($idGrupo);
                $GrupoServico = $row->toArray();

                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                foreach ($arr_sgrs_id_grupo as $value) {
                    $value_option = Zend_Json::decode($value);


                    /**
                     * Se o grupo for diferente do gurpo de gestão de demandas
                     * então remove o grupo de Desenvolvimento e sustentação, pois 
                     * somente o grupo de gestão pode encaminhar para caixa de Desenvolvimento e sustentação.
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] != 118) {
                        if ($value_option["SGRS_ID_GRUPO"] == 2) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }
                    /**
                     * Se o grupo não for um grupo de serviço do TRF1
                     * Então eliminte o grupo de gestão de demandas, pois as demandas para vindas das seções a gestão devem passar primeiro
                     * pelo atendimento aos usuários do tribunal.
                     */
                    if ($GrupoServico[0]["SGRS_SG_SECAO_LOTACAO"] != 'TR') {
                        if ($value_option["SGRS_ID_GRUPO"] == 118) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }

                        /**
                         *  Tratamento de da encaminhamento da seção para gestão da seção e vice versa
                         */
                        $GrupoAtendimento = $SadTbCxgsGrupoServico->getGrupoAtendimento($idGrupo);
                        if ($GrupoAtendimento[0]["TPCX_ID_TIPO_CAIXA"] == 7) {
                            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($dados_input['MODE_SG_SECAO_UNID_DESTINO'], $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
                            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico[0]) => str_replace('CAIXA DE', '', $SgrsGrupoServico[0]["CXEN_DS_CAIXA_ENTRADA"])));
                        } else {
                            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao($dados_input['MODE_SG_SECAO_UNID_DESTINO'], $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
                            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico[0]) => str_replace('CAIXA DE', '', $SgrsGrupoServico[0]["CXEN_DS_CAIXA_ENTRADA"])));
                        }
                    }
                    /**
                     * Se o grupo for diferente do grupo de infraestrutura
                     * então remove o grupo de gestão de infraestrutura, pois 
                     * somente o grupo infraestrutura pode encaminhar para caixa de Gestão de infraestrutura.
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] != 3) {
                        if ($value_option["SGRS_ID_GRUPO"] == 119) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }

                    /**
                     * Se o grupo for diferente do grupo do grupo de escritório de projetos/NOC
                     * então remove o grupo de gestão DO NOC, pois 
                     * somente o grupo do noc pode encaminhar para caixa de Gestão do noc.
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] != 4) {
                        if ($value_option["SGRS_ID_GRUPO"] == 121) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }
                    /**
                     * Se o grupo for diferente do grupo de atendimento aos usuários
                     * então remove o grupo de gestão de atendimento, pois 
                     * somente o grupo helpdesk pode encaminhar para caixa de Gestão de atendimento.
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] != 1) {
                        if ($value_option["SGRS_ID_GRUPO"] == 120) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }

                    /**
                     * Se o grupo for o grupo de gestão de demandas
                     * então remove o grupo de Desenvolvimento e sustentação, pois 
                     * as alterações de serviços de sistemas ainda não foi implementada para a caixa pessoal
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] == 118) {
                        if ($value_option["SGRS_ID_GRUPO"] == 2) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }
                }
                if (!$cdNivelCaixa) {
                    $encaminhamento->setMultiOptions(array(
                        // 'nivel'   => 'Outro nível de atendimento', 
                        'pessoal' => 'Caixa pessoal',
                        'trf' => 'Outro Grupo de Atendimento',
                    ));
                }
                /*                 * *tratamento de encaminhamentos */


                $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisPorServico($idServico);
                //echo $idServico;
                //Zend_Debug::dump($NivelAtendimento); exit;
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
                $dadosCaixa = $solicitacaoNs->dadosCaixa;

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
                            $this->_helper->_redirector('entrada', 'caixapessoal', 'sosti');
                            return;
                        }
                    }
                }

                /*                 * *tratamento de encaminhamentos */
                /**
                 * Validação
                 * Não permitir autoencaminhamento
                 * Validação de integridade dos SLAS
                 */
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                foreach ($arr_sgrs_id_grupo as $value) {
                    $value_option = Zend_Json::decode($value);
                    if ($value_option["SGRS_ID_GRUPO"] == $idGrupo) {
                        $sgrs_id_grupo->removeMultiOption($value);
                    }
                }

                /**
                 * Restrições de permissão por grupos de serviço
                 */
                $SosTbSgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico();
                $row = $SosTbSgrsGrupoServico->find($idGrupo);
                $GrupoServico = $row->toArray();

                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                foreach ($arr_sgrs_id_grupo as $value) {
                    $value_option = Zend_Json::decode($value);


                    /**
                     * Se o grupo for diferente do gurpo de gestão de demandas
                     * então remove o grupo de Desenvolvimento e sustentação, pois 
                     * somente o grupo de gestão pode encaminhar para caixa de Desenvolvimento e sustentação.
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] != 118) {
                        if ($value_option["SGRS_ID_GRUPO"] == 2) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }
                    /**
                     * Se o grupo não for um grupo de serviço do TRF1
                     * Então eliminte o grupo de gestão de demandas, pois as demandas para vindas das seções a gestão devem passar primeiro
                     * pelo atendimento aos usuários do tribunal.
                     */
                    if ($GrupoServico[0]["SGRS_SG_SECAO_LOTACAO"] != 'TR') {
                        if ($value_option["SGRS_ID_GRUPO"] == 118) {
                            $sgrs_id_grupo->removeMultiOption($value);


                            $GrupoAtendimento = $SadTbCxgsGrupoServico->getGrupoAtendimento($idGrupo);
                            if ($GrupoAtendimento[0]["TPCX_ID_TIPO_CAIXA"] == 7) {
                                $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($dados_input['MODE_SG_SECAO_UNID_DESTINO'], $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
                                $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico[0]) => str_replace('CAIXA DE', '', $SgrsGrupoServico[0]["CXEN_DS_CAIXA_ENTRADA"])));
                            } else {
                                $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoGestaoServicoPorLotacao($dados_input['MODE_SG_SECAO_UNID_DESTINO'], $dados_input['MODE_CD_SECAO_UNID_DESTINO']);
                                $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico[0]) => str_replace('CAIXA DE', '', $SgrsGrupoServico[0]["CXEN_DS_CAIXA_ENTRADA"])));
                            }
                        }
                    }
                    /**
                     * Se o grupo for diferente do grupo de infraestrutura
                     * então remove o grupo de gestão de infraestrutura, pois 
                     * somente o grupo infraestrutura pode encaminhar para caixa de Gestão de infraestrutura.
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] != 3) {
                        if ($value_option["SGRS_ID_GRUPO"] == 119) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }

                    /**
                     * Se o grupo for diferente do grupo do grupo de escritório de projetos/NOC
                     * então remove o grupo de gestão DO NOC, pois 
                     * somente o grupo do noc pode encaminhar para caixa de Gestão do noc.
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] != 4) {
                        if ($value_option["SGRS_ID_GRUPO"] == 121) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }
                    /**
                     * Se o grupo for diferente do grupo de atendimento aos usuários
                     * então remove o grupo de gestão de atendimento, pois 
                     * somente o grupo helpdesk pode encaminhar para caixa de Gestão de atendimento.
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] != 1) {
                        if ($value_option["SGRS_ID_GRUPO"] == 120) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }
                    /**
                     * Se o grupo for o grupo de gestão de demandas
                     * então remove o grupo de Desenvolvimento e sustentação, pois 
                     * as alterações de serviços de sistemas e garantia ainda não foi implementada para a caixa pessoal
                     */
                    if ($GrupoServico[0]['SGRS_ID_GRUPO'] == 118) {
                        if ($value_option["SGRS_ID_GRUPO"] == 2) {
                            $sgrs_id_grupo->removeMultiOption($value);
                        }
                    }
                }

                if (!$cdNivelCaixa) {
                    $encaminhamento->setMultiOptions(array(
                        // 'nivel'   => 'Outro nível de atendimento', 
                        'pessoal' => 'Caixa pessoal',
                        'trf' => 'Outro Grupo de Atendimento',
                    ));
                }
                /*                 * *tratamento de encaminhamentos */

                $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                $NivelAtendimento = $SosTbSnatNivelAtendimento->getNiveisPorServico($idServico);
                //echo $idServico;
                //Zend_Debug::dump($NivelAtendimento); exit;
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
                    if (!is_null($data["ANEXOS"])/* $form->ANEXOS->isReceived() */) {
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
                            $dadosCaixa = $solicitacaoNs->dadosCaixa;
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
                            $this->_helper->_redirector('entrada', 'caixapessoal', 'sosti');
                        }

                        /* GRUPO */
                    } else if ($data["ENCAMINHAMENTO"] == 'trf') {

                        $dadosCaixa = $solicitacaoNs->dadosCaixa;
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
                                 * ENCAMINHAMENTO DE SOLICITAÇÃO DE TI DE TRIBUNAL PARA TRIBUNAL CODIGO 1001
                                 * ENCAMINHAMENTO DE SOLICITAÇÃO DE TI DE SEÇÃO PARA O TRIBUNAL CODIGO 1022
                                 * ENCAMINHAMENTO DE SOLICITAÇÃO DE TI DE SEÇÃO ELA MESMA CODIGO 1050
                                 */
                                if (($dados_input["MODE_SG_SECAO_UNID_DESTINO"] == $destino["SGRS_SG_SECAO_LOTACAO"]) && ($destino["SGRS_SG_SECAO_LOTACAO"] != 'TR')) {
                                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1050;
                                } else if (($dados_input["MODE_SG_SECAO_UNID_DESTINO"] == $destino["SGRS_SG_SECAO_LOTACAO"])) {
                                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1001;
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
                            $db->commit();

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

                            $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " encaminhada(s)!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('entrada', 'caixapessoal', 'sosti');
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $erro = $exc->getMessage();
                            $msg_to_user = "Ocorreu um erro ao encaminhar a solicitação! <br/> $erro ";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector('entrada', 'caixapessoal', 'sosti');
                        }

                        /* PESSOAL */
                    } else if ($data["ENCAMINHAMENTO"] == 'pessoal') {
                        $dataSol = $solicitacaoNs->dadosCaixa;
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
                        $this->_helper->_redirector('entrada', 'caixapessoal', 'sosti');
                    }
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * OBTENDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $this->view->title = $solicspace->label . " - ENCAMINHAR SOLICITAÇÃO(ES)";
                    $this->view->data = $solicitacaoNs->dadosSolicitacao;
                    $this->view->form = $form;
                }
            }
        }
        $this->view->title = $solicspace->label . " - ENCAMINHAR SOLICITAÇÃO(ES)";
        $this->view->data = $solicitacaoNs->dadosSolicitacao;
        $pop['LOTA_COD_LOTACAO'] = strtoupper($userNs->siglalotacao) . ' - ' . strtoupper($userNs->descicaolotacao) . ' - ' . strtoupper($userNs->codlotacao);
        $form->populate($pop);
        $this->view->form = $form;
    }

    public function ajaxunidadeAction () {
        $unidade = $this->_getParam('term', '');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxpessoaAction () {
        $lota_cod_lotacao = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $OcsTbApspPapSistUnidPess = new Application_Model_DbTable_OcsTbApspPapSistUnidPess();
        $PapSistUnidPess_array = $OcsTbApspPapSistUnidPess->getPessoa($lota_cod_lotacao);
        $this->view->PapSistUnidPess_array = $PapSistUnidPess_array;
    }

}
