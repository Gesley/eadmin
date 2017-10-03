<?php

class Sosti_HelpdeskController extends Zend_Controller_Action
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

        $this->view->titleBrowser = 'e-Sosti';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function indexAction()
    {


        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            switch ($data["acao"]) {
                case 'Encaminhar':
                    return $this->_helper->_redirector('encaminhar', 'helpdesk', 'sosti', array('id' => $id));
                    break;
                case 'Próxima':
                    return $this->_helper->_redirector('proxima', 'helpdesk', 'sosti', array('id' => $id));
                    break;
                case 'Baixar':
                    return $this->_helper->_redirector('baixar', 'helpdesk', 'sosti', array('id' => $id));
                    break;
                case 'Espera':
                    return $this->_helper->_redirector('espera', 'helpdesk', 'sosti', array('id' => $id));
                    break;
                default:
                    break;
            }
        }
    }

    public function primeironivelAction()
    {
        /**
         * Definições
         */
        $idCaixa = 1;
        $idNivel = 1;
        $cdNivel = 1;
        $idGrupo = 1;
        $title = "1º NÍVEL - SERVIÇO DE ATENDIMENTO TÉCNICO AO CLIENTE - SAT";
        $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 50, 'page' => 1);;

        // levando o perfil do usuario para a view
        $responsavelPelacaixa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $userNs = new Zend_Session_Namespace('userNs');
        $devincularCaixa = $responsavelPelacaixa->getPerfiRespnsavelCaixaUnidade($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula);
        $idPerfil = $devincularCaixa[0]['PERF_ID_PERFIL'];
        $this->view->idPerfil = $idPerfil;

        // matricula do usuario para view

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
         * Form
         */
        $form = new Sosti_Form_CaixaSolicitacao();
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
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->getServicoPorGrupo($idGrupo, 'SSER_DS_SERVICO ASC');
        $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
        $sser_id_servico->addMultiOptions(array('' => ''));
        foreach ($SserServico as $SserServico_p):
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        endforeach;

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

        /*         * ***************************************************************
         * Configuração das categorias
         * ************************************************************** */
        $this->view->categorias = $Categorias;
        $CateNs = new Zend_Session_Namespace('CateNs');
        $CateNs->tipo = 3;
        $CateNs->identificador = $idNivel;
        $CateNs->controller = $this->getRequest()->getControllerName();
        $CateNs->action = $this->getRequest()->getActionName();

        /*
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
                ($value == 1024) || ($value == 1008))
            ) {
                if ($value != "") {
                    $mofa_id_fase->removeMultiOption($value);
                }
            }
        }

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
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        if (!is_null($data_pesq)) {
            $this->view->ultima_pesq = true;

            $mat_aux = explode(' - ', $data_pesq["DOCM_CD_MATRICULA_CADASTRO_VALUE"]);
            $data_pesq["DOCM_CD_MATRICULA_CADASTRO"] = $mat_aux[0];
            $mat_atend_aux = explode(' - ', $data_pesq["SSOL_CD_MATRICULA_ATENDENTE_VALUE"]);
            $data_pesq["SSOL_CD_MATRICULA_ATENDENTE"] = $mat_atend_aux[0];
            $unid_aux = explode(' - ', $data_pesq["DOCM_CD_LOTACAO_GERADORA_VALUE"]);
            (array_key_exists(2, $unid_aux)) ? ($data_pesq["DOCM_CD_LOTACAO_GERADORA"] = $unid_aux[2]) : '';
            (array_key_exists(3, $unid_aux)) ? ($data_pesq["DOCM_SG_SECAO_GERADORA"] = $unid_aux[3]) : '';

            $consulta_caixa = $caixas->getCaixaComNivelPesq($idCaixa, $cdNivel, $data_pesq, $order);
            Zend_Debug::dump($consulta_caixa);
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
            $paginator = new Zend_Paginator(new App_Paginator_Adapter_Sql_Oracle($consulta_caixa));
            $paginator->setCurrentPageNumber($varSessoes->getPage())
                ->setItemCountPerPage($varSessoes->getItemsperpage());

            foreach ($paginator->getCurrentItems() as $sosti) {
                if ($sosti['VINCULADA'] > 0) {
                    $vincs = $caixas->getVinculos($sosti["SSOL_ID_DOCUMENTO"]);
                    foreach ($vincs as $vincRow) {
                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $SosTbSsolSolicitacao->getDadosSolicitacao($vincRow["VIDC_ID_DOC_VINCULADO"]);
//                        $queryCaixa = $caixas->getCaixaSemNivelPesq($idCaixa, null, $order, $vincRow["VIDC_ID_DOC_VINCULADO"]);
//                        if(is_string($queryCaixa))
//                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getData($queryCaixa);
//                        else
//                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq($idCaixa, null, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
                    }
                }
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

            foreach ($paginator->getCurrentItems() as $sosti) {
                if ($sosti['VINCULADA'] > 0) {
                    $vincs = $caixas->getVinculos($sosti["SSOL_ID_DOCUMENTO"]);
                    foreach ($vincs as $vincRow) {
                        $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $SosTbSsolSolicitacao->getDadosSolicitacao($vincRow["VIDC_ID_DOC_VINCULADO"]);
//                        $queryCaixa = $caixas->getCaixaSemNivelPesq($idCaixa, null, $order, $vincRow["VIDC_ID_DOC_VINCULADO"]);
//                        if(is_string($queryCaixa))
//                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getData($queryCaixa);
//                        else
//                            $vinc[$sosti["SSOL_ID_DOCUMENTO"]][] = $caixas->getCaixaSemNivelPesq($idCaixa, null, $order, $vincRow["VIDC_ID_DOC_VINCULADO"])->getData();
                    }
                }
            }
        }
//        Zend_Debug::dump($vinc);die;
        $this->view->vinc = $vinc;
        $this->view->title = $title;
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        $this->view->form = $form;
    }

    public function proximaAction()
    {
        $this->view->title = "EM ATENDIMENTO - SAT - TRF1";

        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;

        if ($this->_getParam('acao') == 'nova') {
            $nova = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $dadosNova = $nova->getSolicitacaoMaisNova($this->_getParam('id'));

            if ($dadosNova) {
                $idDocmDocumento = $this->_getParam('id');
                $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                /**
                 * Alterar a funcionalidade para o sistema Inclua o usuário que abriu a solicitação como 
                 * atendente somente para aqueles que estejam cadastrados como atendente da caixa (Helpdesk → 1º Nível - SAT).
                 */
                $atendentesCaixa = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
                $atCaixa = $atendentesCaixa->getAtendentesCaixa(1, $userNs->matricula);
                if ($atCaixa[0]["IC_ATIVIDADE"] == "SIM") {
                    $SosTbSsolSolicitacao->setAtendente($idDocmDocumento, $matricula);
                }

                $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                $fone = $mapperDocumento->getUltimoTelefoneCadastrado(strtoupper($dadosNova[0]["DOCM_CD_MATRICULA_CADASTRO"]));
                $solicitanteNs = new Zend_Session_Namespace('solicitanteNs');
                $solicitanteNs->iddocumento = $dadosNova[0]["SSOL_ID_DOCUMENTO"];
                $solicitanteNs->datafase = $dadosNova[0]["MOFA_DH_FASE"];
                /**
                 * Carrega os dados da unidade solicitante
                 */
                $dataSol = $SosTbSsolSolicitacao->getDadosSolicitacao($dadosNova[0]["SSOL_ID_DOCUMENTO"]);
                if ($dadosNova[0]["SSOL_NM_USUARIO_EXTERNO"] == '') {
                    $solicitanteNs->cadastrante = $dadosNova[0]["DOCM_CD_MATRICULA_CADASTRO"] . ' - ' . $dadosNova[0]["NOME"];
                    $solicitanteNs->unidade = $dataSol['LOTA_SIGLA_LOTACAO'] . ' - ' . $dataSol['LOTA_DSC_LOTACAO'] . ' - ' . $dataSol['LOTA_COD_LOTACAO'] . ' - ' . $dataSol['LOTA_SIGLA_SECAO'];
                } else {
                    $solicitanteNs->cadastrante = $dadosNova[0]["SSOL_NM_USUARIO_EXTERNO"];
                    $solicitanteNs->unidade = 'USUÁRIO EXTERNO';
                }
                $solicitanteNs->servico = html_entity_decode($dadosNova[0]["DOCM_DS_ASSUNTO_DOC"]);
                $solicitanteNs->tempototal = $dadosNova[0]["TEMPO_TOTAL"];
                $solicitanteNs->email = $dadosNova[0]["SSOL_DS_EMAIL_EXTERNO"];
                $solicitanteNs->matricula = $dadosNova[0]["DOCM_CD_MATRICULA_CADASTRO"];
                $solicitanteNs->localizacao = $dadosNova[0]["SSOL_ED_LOCALIZACAO"];
                $solicitanteNs->telefone = $fone[0]["SSOL_NR_TELEFONE_EXTERNO"];
                $solicitanteNs->unidadeh = $dadosNova[0]["LOTA_SIGLA_LOTACAO"];
                $solicitanteNs->idmovimentacao = $dadosNova[0]["MOVI_ID_MOVIMENTACAO"];
                $solicitanteNs->nrdocumento = $dadosNova[0]["DOCM_NR_DOCUMENTO"];
                $solicitanteNs->dsservico = $dadosNova[0]["SSER_DS_SERVICO"];

                $DocmDocumentoVinculacao = $nova->getPrincipalVinculacao($dadosNova[0]["SSOL_ID_DOCUMENTO"]);
                $this->view->DocmDocumentoVinculacao = $DocmDocumentoVinculacao;

                $DocmListaVinculados = $nova->getListaSolicitacoesVinculadas($dadosNova[0]["SSOL_ID_DOCUMENTO"]);
                $this->view->DocmListaVinculados = $DocmListaVinculados;


                $this->view->title = "EM ATENDIMENTO - SAT - SOLICITAÇÃO Nº " . $dadosNova[0]['DOCM_NR_DOCUMENTO'];
            } else {
                $msg_to_user = "Não exitem solicitções para atender neste momento!";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                $this->_helper->_redirector('atendimentointerno', 'helpdesk', 'sosti');
            }
        } else {

            $antiga = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $dadosAntiga = $antiga->getSolicitacaoMaisAntiga($matricula);

            if ($dadosAntiga) {

                $idDocmDocumento = $dadosAntiga[0]["SSOL_ID_DOCUMENTO"];
                $antiga->setAtendente($idDocmDocumento, $matricula);

                $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                $fone = $mapperDocumento->getUltimoTelefoneCadastrado(strtoupper($dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"]));
                $solicitanteNs = new Zend_Session_Namespace('solicitanteNs');
                $solicitanteNs->iddocumento = $dadosAntiga[0]["SSOL_ID_DOCUMENTO"];
                $solicitanteNs->datafase = $dadosAntiga[0]["MOFA_DH_FASE"];
                $solicitanteNs->cadastrante = $dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"] . ' - ' . $dadosAntiga[0]["NOME_USARIO_CADASTRO"];
                $solicitanteNs->servico = html_entity_decode($dadosAntiga[0]["DOCM_DS_ASSUNTO_DOC"]);
                $solicitanteNs->tempototal = $dadosAntiga[0]["TEMPO_TOTAL"];
                $solicitanteNs->email = strtolower($dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"]) . '@trf1.jus.br';
                $solicitanteNs->matricula = $dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"];
                $solicitanteNs->unidade = $dadosAntiga[0]["DOCM_CD_LOTACAO_GERADORA"] . ' - ' . $dadosAntiga[0]["LOTA_DSC_LOTACAO"] . ' - ' . $dadosAntiga[0]["LOTA_SIGLA_LOTACAO"];
                $solicitanteNs->localizacao = $dadosAntiga[0]["SSOL_ED_LOCALIZACAO"];
                $solicitanteNs->telefone = $fone[0]["SSOL_NR_TELEFONE_EXTERNO"];
                $solicitanteNs->unidadeh = $dadosAntiga[0]["LOTA_SIGLA_LOTACAO"];
                $solicitanteNs->idmovimentacao = $dadosAntiga[0]["MOVI_ID_MOVIMENTACAO"];
                $solicitanteNs->nrdocumento = $dadosAntiga[0]["DOCM_NR_DOCUMENTO"];
                $solicitanteNs->dsservico = $dadosAntiga[0]["SSER_DS_SERVICO"];
                $this->view->title = "EM ATENDIMENTO - SAT - SOLICITAÇÃO Nº " . $dadosAntiga[0]['DOCM_NR_DOCUMENTO'];

                $DocmDocumentoVinculacao = $antiga->getPrincipalVinculacao($dadosAntiga[0]["SSOL_ID_DOCUMENTO"]);
                $this->view->DocmDocumentoVinculacao = $DocmDocumentoVinculacao;

                $DocmListaVinculados = $antiga->getListaSolicitacoesVinculadas($dadosAntiga[0]["SSOL_ID_DOCUMENTO"]);
                $this->view->DocmListaVinculados = $DocmListaVinculados;
            } else {
                $msg_to_user = "Não exitem solicitções para atender neste momento!";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                $this->_helper->_redirector('atendimentointerno', 'helpdesk', 'sosti');
            }
        }
        $form = new Sosti_Form_Proxima();
        $this->view->form = $form;
    }

    public function encaminharcaixaAction()
    {
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

        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbAtcxAtendenteCaixa = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        $solicitacaoNs = new Zend_Session_Namespace('solicitacaoNs');

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

        /**
         * Tratamento para retirar o grupo de serviço Desenvolvimento e sustentação da lista
         */
        $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
        $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
        $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
        foreach ($arr_sgrs_id_grupo as $value) {
            $value_option = Zend_Json::decode($value);
            //desenvolvimento e sustentacao = 2
            //gestão da infra = 119
            //gestão do noc = 121
            // Infraestrutura = 3
            if (in_array($value_option["SGRS_ID_GRUPO"], array(2, 3, 121))) {
                $sgrs_id_grupo->removeMultiOption($value);
            }
        }

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
                            $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti');
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

                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                foreach ($arr_sgrs_id_grupo as $value) {
                    $value_option = Zend_Json::decode($value);
                    if ($value_option["SGRS_ID_GRUPO"] == $idGrupo) {
                        $sgrs_id_grupo->removeMultiOption($value);
                    }
                }

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
                            $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti');
                            return;
                        }
                    }
                }

                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $arr_sgrs_id_grupo = $sgrs_id_grupo->getMultiOptions();
                $arr_sgrs_id_grupo = array_keys($arr_sgrs_id_grupo);
                foreach ($arr_sgrs_id_grupo as $value) {
                    $value_option = Zend_Json::decode($value);
                    if ($value_option["SGRS_ID_GRUPO"] === $idGrupo) {
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
                    /**
                     * Encaminhar para outro nível de atendimento
                     */
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
                                /**
                                 * Pega o elemento input file do form
                                 */
                                $SosTbSnatNivelAtendimento->trocanivelSolicitacao($idDocmDocumento, $dataMofaMoviFase, $dataSnasNivelAtendSolic, $nrDocsRed, $acompanhar);
                                $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                                $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                                $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                            }
                            $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " encaminhada(s)!";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti');
                        }
                        /**
                         * Encaminhar solicitação para outro grupo de atendimento
                         */
                    } else if ($data["ENCAMINHAMENTO"] == 'trf' || $data["ENCAMINHAMENTO"] == 'secoes') {

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
                                 * ENCAMINHAMENTO DE SOLICITAÇÃO DE TI DE SEÇÃO PARA O TRIBUNAL CODIGO 1022
                                 */
                                if ($data["ENCAMINHAMENTO"] == 'secoes') {
                                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1029; /* Encaminhamento do Tribunal para Secao */
                                } else if ($data["ENCAMINHAMENTO"] == 'trf') {
                                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1001; /* Encaminhamento de Solicitacao de TI */
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
                                $email = new Application_Model_DbTable_EnviaEmail();
                                if ($data["SSER_ID_SERVICO"] == '620|N') {
                                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                                    $remetente = 'noreply@trf1.jus.br';
                                    $assunto = 'Encaminhamento de Solicitação de Videoconferência';
                                    $corpo = "Encaminhamento de Solicitação de Videoconferência</p>
                                            Solicitante: " . $userNs->nome . " <br/>
                                            Número da Solicitação: " . $dados_input["DOCM_NR_DOCUMENTO"] . " <br/>
                                            Descrição da Solicitação: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                                    $email->setEnviarEmail($sistema, $remetente, 'noc@trf1.jus.br', $assunto, $corpo);
                                    $email->setEnviarEmail($sistema, $remetente, 'ditec@trf1.jus.br', $assunto, $corpo);
                                    $email->setEnviarEmail($sistema, $remetente, 'coint@trf1.jus.br', $assunto, $corpo);
                                }
                                $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                                $solicitacoesEncaminhadas = $solicitacoesEncaminhadas . ', ' . $nrdocumento;
                            }
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $erro = $exc->getMessage();
                            $msg_to_user = "Ocorreu um erro ao encaminhar a solicitação! <br/> $erro ";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti');
                        }
                        $db->commit();

                        $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " encaminhada(s)!";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti');
                        /**
                         * Encaminhar a solicitação para caixa pessoal
                         */
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
                        $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti');
                    }
                } else {

                    /**
                     * CRIANDO FORMULÁRIO DA RESPOSTA PADRÃO COM ID GRUPO
                     * OBTENDO VALOR DO ID GRUPO NA SESSION
                     */
                    $form_resposta->set_idGrupo($NsAction->idGrupo_repd);
                    $form_resposta->escolheResposta();
                    $this->view->formResposta = $form_resposta;

                    $this->view->title = "1º NÍVEL - ENCAMINHAR SOLICITAÇÃO(ES)";
                    $this->view->data = $solicitacaoNs->dadosSolicitacao;
                    $this->view->form = $form;
                }
            }
        }
        $this->view->title = "1º NÍVEL - ENCAMINHAR SOLICITAÇÃO(ES)";
        $this->view->data = $solicitacaoNs->dadosSolicitacao;
        $pop['LOTA_COD_LOTACAO'] = strtoupper($userNs->siglalotacao) . ' - ' . strtoupper($userNs->descicaolotacao) . ' - ' . strtoupper($userNs->codlotacao);
        $form->populate($pop);
        $this->view->form = $form;
    }

    public function encaminharAction()
    {
        $solicitanteNs = new Zend_Session_Namespace('solicitanteNs');
        $this->view->title = "ENCAMINHAR SOLICITAÇÃO - SAT -  SOLICITAÇÃO Nº $solicitanteNs->nrdocumento";
        $form = new Sosti_Form_Encaminhar();
        $userNs = new Zend_Session_Namespace('userNs');
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SosTbSnatNivelAtendimento = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {

                $DadosSolicitacao = $SosTbSsolSolicitacao->getDadosSolicitacao($data["ID_DOCUMENTO"]);
                $dadosJson[0] = Zend_Json::encode($DadosSolicitacao);

                $data['solicitacao'] = $dadosJson;

                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($dadosJson);

                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }

                foreach ($data['solicitacao'] as $value) {
                    $dados_input = Zend_Json::decode($value);

                    $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["DESCRICAOENCAMINHAMENTO"];
                    $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = 2;

                    $SosTbSnatNivelAtendimento->trocanivelSolicitacao($dados_input["SSOL_ID_DOCUMENTO"], $dataMofaMoviFase, $dataSnasNivelAtendSolic);

                    $this->_helper->flashMessenger(array('message' => "Solicitação nº " . $dados_input['DOCM_NR_DOCUMENTO'] . " encaminhada!", 'status' => 'success'));
                }
                return $this->_helper->_redirector('atendimentointerno', 'helpdesk', 'sosti');
            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('encaminhar');
            }
        }
    }

    public function baixarAction()
    {
        $solicitanteNs = new Zend_Session_Namespace('solicitanteNs');
        $this->view->title = "BAIXAR SOLICITAÇÃO - SAT - SOLICITAÇÃO Nº $solicitanteNs->nrdocumento";
        $form = new Sosti_Form_Baixar();
        $this->view->form = $form;
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $userNs = new Zend_Session_Namespace('userNs');
        $app_Email = new App_Email();

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($form->isValid($data)) {

                $DadosSolicitacao = $SosTbSsolSolicitacao->getDadosSolicitacao($data["ID_DOCUMENTO"]);
                $dadosJson[0] = Zend_Json::encode($DadosSolicitacao);

                $data['solicitacao'] = $dadosJson;

                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($dadosJson);

                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }

                foreach ($data['solicitacao'] as $value) {
                    $dados_input = Zend_Json::decode($value);

                    $dataBaixa["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                    $dataBaixa["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                    $dataBaixa["MOFA_DS_COMPLEMENTO"] = $data["DESCRICAOSOLUCAO"];

                    $SosTbSsolSolicitacao->baixaSolicitacao($dataBaixa, $dados_input["SSOL_ID_DOCUMENTO"]);
                    
                    $solicit = $SosTbSsolSolicitacao->fetchRow('SSOL_ID_DOCUMENTO = '.$dados_input["SSOL_ID_DOCUMENTO"])->toArray();
                    $arrayDados = array(
                        'destinatario' => $dados_input["DOCM_CD_MATRICULA_CADASTRO"]
                    , 'solicitacao' => $dados_input['DOCM_NR_DOCUMENTO']
                    , 'dataSolicitacao' => $dados_input['DOCM_DH_CADASTRO']
                    , 'tipoServico' => $dados_input['SSER_DS_SERVICO']
                    , 'descricaoBaixa' => nl2br($data["MOFA_DS_COMPLEMENTO"])
                    , 'descricaoSolicitacao' => $dados_input["DOCM_DS_ASSUNTO_DOC"]);
                    if ($solicit["SSOL_NM_USUARIO_EXTERNO"] == null) {
                         /* Envio de email normal */
                         try {
                             $app_Email->baixarSolicitacao($arrayDados);
                         } catch (Exception $exc) {
                             $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                         }
                    } else {
                         /**
                          * Envia e-mail para os usuário externos
                          */
                         $email = new Application_Model_DbTable_EnviaEmail();
                         $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                         $remetente = 'CSTI@trf1.jus.br';
                         $corpo = "Uma solicitação foi baixada, será necessário acessar o sistema para avaliação.</p>
                                  <br />Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/".$arrayDados["solicitacao"]."\"><b>".$arrayDados["solicitacao"]."</b> </a>
                                  <br />Data da Solicitação: ".$arrayDados["dataSolicitacao"]."
                                  <br />Atendente: $userNs->nome
                                  <br />Tipo de Serviço Solicitado: ".$arrayDados["tipoServico"]."
                                  <br />Descrição da Baixa: ".$data["DESCRICAOSOLUCAO"]."<br />
                                  <br />Descrição da Solicitação: ".$arrayDados["descricaoSolicitacao"]."<br/>";
                         $email->setEnviarEmailExterno($sistema, $remetente, $solicit["SSOL_DS_EMAIL_EXTERNO"], 'Baixa de Solicitação', $corpo);
                    }

                    $this->_helper->flashMessenger(array('message' => "Solicitação nº " . $dados_input['DOCM_NR_DOCUMENTO'] . " baixada!", 'status' => 'success'));
                }

                return $this->_helper->_redirector('atendimentointerno', 'helpdesk', 'sosti');
            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('baixar');
            }
        }
    }

    public function baixarcaixaAction()
    {
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
                $this->view->title = "1º NÍVEL - BAIXAR SOLICITAÇÃO(ES)";
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
                        $dataBaixa["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataBaixa["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                        $dataBaixa["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];
                        /**
                         * Pega o elemento input file do form
                         */
                        $docm_ds_hash_red = $form->getElement('DOCM_DS_HASH_RED');
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
                    $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = "1º NÍVEL - BAIXAR SOLICITAÇÃO(ÕES)";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('baixarcaixa');
                }
            }
        }
    }

    public function esperaAction()
    {
        $solicitanteNs = new Zend_Session_Namespace('solicitanteNs');
        $this->view->title = "COLOCAR SOLICITAÇÃO EM ESPERA - SAT - SOLICITAÇÃO Nº $solicitanteNs->nrdocumento";
        $form = new Sosti_Form_Espera();
        $this->view->form = $form;
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $espera = new Application_Model_DbTable_SosTbSespSolicEspera();

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            if ($form->isValid($data)) {


                $DadosSolicitacao = $SosTbSsolSolicitacao->getDadosSolicitacao($data["ID_DOCUMENTO"]);
                $dadosJson[0] = Zend_Json::encode($DadosSolicitacao);

                $data['solicitacao'] = $dadosJson;

                $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
                $SolicitacaoComFamilia = $SadTbVidcVinculacaoDoc->getFamiliaVinculacao($dadosJson);

                /**
                 * Caso alguma solicitaÃ§Ã£o tenha famÃ­lia. Caso contrÃ¡rio, caminho normal.
                 */
                if ($SolicitacaoComFamilia) {
                    $data['solicitacao'] = $SolicitacaoComFamilia;
                }

                foreach ($data['solicitacao'] as $value) {
                    $dados_input = Zend_Json::decode($value);

                    $solicitacao = $data["ID_DOCUMENTO"];
                    $userNs = new Zend_Session_Namespace('userNs');
                    $dataEspera["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                    $dataEspera["MOFA_CD_MATRICULA"] = $userNs->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                    $dataEspera["MOFA_DS_COMPLEMENTO"] = $data["DESCRICAOSOLUCAO"];

                    $limite = new Application_Model_DbTable_Dual();
                    $dataSespSolicEspera["SESP_DH_LIMITE_ESP"] = $limite->setEspera();


                    $espera->esperaSolicitacao($dados_input["SSOL_ID_DOCUMENTO"], $dataEspera, $dataSespSolicEspera);
                    $this->_helper->flashMessenger(array('message' => "Solicitação nº " . $dados_input['DOCM_NR_DOCUMENTO'] . " colocada em espera!", 'status' => 'success'));
                }

                return $this->_helper->_redirector('atendimentointerno', 'helpdesk', 'sosti');
            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('espera');
            }
        }
    }

    public function atendimentointernoAction()
    {
        $nivelSn = new Zend_Session_Namespace('nivelNs');
        $this->view->nivel = $nivelSn->nivel;
        $this->view->title = "EM ATENDIMENTO - SAT - USUÁRIO INTERNO";
        $antiga = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $dadosAntiga = $antiga->getSolicitacaoMaisAntiga();
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $fone = $mapperDocumento->getUltimoTelefoneCadastrado(strtoupper($dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"]));
        $solicitanteNs = new Zend_Session_Namespace('solicitanteNs');
        $Dual = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $Dual->sysdateDb();
        $solicitanteNs->iddocumento = $dadosAntiga[0]["SSOL_ID_DOCUMENTO"];
        $solicitanteNs->datafase = $dadosAntiga[0]["MOFA_DH_FASE"];
        $solicitanteNs->cadastrante = $dadosAntiga[0]["NOME_USARIO_CADASTRO"];
        $solicitanteNs->servico = $dadosAntiga[0]["DOCM_DS_ASSUNTO_DOC"];
        $solicitanteNs->palavrachave = $dadosAntiga[0]["DOCM_DS_PALAVRA_CHAVE"];
        $solicitanteNs->tempototal = $dadosAntiga[0]["TEMPO_TOTAL"];
        $solicitanteNs->email = strtolower($dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"]) . '@trf1.jus.br';
        $solicitanteNs->matricula = $dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"];
        $solicitanteNs->unidade = $dadosAntiga[0]["DOCM_CD_LOTACAO_GERADORA"] . ' - ' . $dadosAntiga[0]["LOTA_DSC_LOTACAO"] . ' - ' . $dadosAntiga[0]["LOTA_SIGLA_LOTACAO"];
        $solicitanteNs->localizacao = $dadosAntiga[0]["SSOL_ED_LOCALIZACAO"];
        $solicitanteNs->telefone = $fone[0]["SSOL_NR_TELEFONE_EXTERNO"];
        $solicitanteNs->unidadeh = $dadosAntiga[0]["LOTA_SIGLA_LOTACAO"];
        $form = new Sosti_Form_Atendimentointerno();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;
    }

    public function atendimentoexternoAction()
    {
        $aNivelSpace = new Zend_Session_Namespace('nivelNs');
        $this->view->nivel = $aNivelSpace->nivel;
        $this->view->title = "EM ATENDIMENTO - SAT - USUÁRIO EXTERNO";
        $antiga = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $dadosAntiga = $antiga->getSolicitacaoMaisAntiga();
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $fone = $mapperDocumento->getUltimoTelefoneCadastrado(strtoupper($dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"]));
        $solicitanteNs = new Zend_Session_Namespace('solicitanteNs');
        $solicitanteNs->iddocumento = $dadosAntiga[0]["SSOL_ID_DOCUMENTO"];
        $solicitanteNs->datafase = $dadosAntiga[0]["MOFA_DH_FASE"];
        $solicitanteNs->cadastrante = $dadosAntiga[0]["NOME_USARIO_CADASTRO"];
        $solicitanteNs->servico = $dadosAntiga[0]["DOCM_DS_ASSUNTO_DOC"];
        $solicitanteNs->palavrachave = $dadosAntiga[0]["DOCM_DS_PALAVRA_CHAVE"];
        $solicitanteNs->tempototal = $dadosAntiga[0]["TEMPO_TOTAL"];
        $solicitanteNs->email = strtolower($dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"]) . '@trf1.jus.br';
        $solicitanteNs->matricula = $dadosAntiga[0]["DOCM_CD_MATRICULA_CADASTRO"];
        $solicitanteNs->unidade = $dadosAntiga[0]["DOCM_CD_LOTACAO_GERADORA"] . ' - ' . $dadosAntiga[0]["LOTA_DSC_LOTACAO"] . ' - ' . $dadosAntiga[0]["LOTA_SIGLA_LOTACAO"];
        $solicitanteNs->localizacao = $dadosAntiga[0]["SSOL_ED_LOCALIZACAO"];
        $solicitanteNs->telefone = $fone[0]["SSOL_NR_TELEFONE_EXTERNO"];
        $solicitanteNs->unidadeh = $dadosAntiga[0]["LOTA_SIGLA_LOTACAO"];
        $form = new Sosti_Form_Atendimentoexterno();
        $formAnexo = new Sosti_Form_Anexo();
        $formAnexo->anexoUnico();
        $form->addElement($formAnexo->getElement('ANEXOS'));
        $formAnexo->submit();
        $form->addElement($formAnexo->getElement('Salvar'));
        $this->view->form = $form;
    }

    public function acoesatendimentoAction()
    {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);

        $data = $this->getRequest()->getPost();

        /**
         * Cadastrar solicitação interna
         */
        if (($data["Salvar"] == "Salvar") && ($data["acaor"] == "I")) {

            $this->view->title = "CADASTRO DE SOLICITAÇÃO INTERNA";
            $form = new Sosti_Form_Atendimentointerno();
            $formAnexo = new Sosti_Form_Anexo();
            $formAnexo->anexoUnico();
            $form->addElement($formAnexo->getElement('ANEXOS'));
            $formAnexo->submit();
            $form->addElement($formAnexo->getElement('Salvar'));
            $this->view->form = $form;
            $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $userNs = new Zend_Session_Namespace('userNs');

            if ($this->getRequest()->isPost()) {
                $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                $destino = Zend_Json::decode($data['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data['SGRS_ID_GRUPO'] => $destino["SGRS_DS_GRUPO"]));

                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                $sser_id_servico->addMultiOptions(array($data['SSER_ID_SERVICO'] => ''));

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

                    $data["acaor"] = '';
                    $data['SSOL_ID_TIPO_CAD'] = 2; /* Telefone */
                    $matricula = explode(" - ", $data["DOCM_CD_MATRICULA_CADASTRO"]);

                    $unidade = explode(' - ', $data['DOCM_CD_LOTACAO_GERADORA']);

                    $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = $matricula[0];
                    $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = 160; //Solicitação de serviços a TI
                    $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = $unidade[3]; //TR solicitante que tá ligando
                    $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = $unidade[2]; //1155 solicitante que tá ligando
                    $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = $unidade[3]; // 
                    $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = $unidade[2]; // 
                    $dataDocmDocumento["DOCM_ID_PCTT"] = 2539; //PCTT Solicitação de TI
                    $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = $data['DOCM_DS_ASSUNTO_DOC'];
                    $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Sistuaçaõ Digital Gerado pelo sistema
                    $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Situaação Pública
                    $dataDocmDocumento["DOCM_DS_PALAVRA_CHAVE"] = substr($data['DOCM_DS_ASSUNTO_DOC'], 0, 100);

                    $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = 2; //telefone
                    $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = $data["SSOL_ED_LOCALIZACAO"];
                    $dataSsolSolicitacao["SSOL_NR_TOMBO"] = $data["SSOL_NR_TOMBO"];
                    $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"] = $data["SSOL_SG_TIPO_TOMBO"];
                    $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = $data["SSOL_DS_OBSERVACAO"];
                    unset($dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO']);
                    unset($dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO']);
                    $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = $data['SSOL_DS_EMAIL_EXTERNO'];
                    $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = $data['SSOL_NR_TELEFONE_EXTERNO'];

                    $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $unidade[3];
                    $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $unidade[2];
                    //$dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] ;//Caixa de atendimento DIATU
                    $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;

                    $destino = Zend_Json::decode($data[SGRS_ID_GRUPO]);

                    $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $destino['SGRS_SG_SECAO_LOTACAO'];
                    $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $destino['SGRS_CD_LOTACAO'];
                    $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                    $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $destino['CXEN_ID_CAIXA_ENTRADA']; //Caixa de atendimento 

                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1006; //CADASTRO SOLICITACAO TI
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solictação";

                    $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
                    $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($destino["SGRS_ID_GRUPO"]);

                    if ($NivelAtendSolic) {
                        $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $NivelAtendSolic["SNAT_ID_NIVEL"];
                    } else {
                        $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = '';
                    }

                    $id_servico = explode('|', $data["SSER_ID_SERVICO"]);
                    $dataSsesServicoSolic["SSES_ID_SERVICO"] = $id_servico[0];

                    if ($id_servico[2] == "S") {
                        $dataSsesServicoSolic["SSES_DT_INICIO_VIDEO"] = $data["SSES_DT_INICIO_VIDEO"];
                    }

                    $dataAnexAnexo['NR_DOCUMENTO_INTERNO'] = null;

                    if ($nrDocsRed["erro"]) {
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                        $this->view->form = $form;
                        $this->render('atendimentointerno');
                        return;
                    }

                    if (!$nrDocsRed["existentes"]) {
                        if (!$nrDocsRed["incluidos"]) {
                            try {
                                $dataRetorno = $ssolSolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic);
                                $msg_to_user = "Solicitação nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível salvar o anexo.";
                                $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView = $msg_to_user;
                            }
                        } else {
                            try {
                                $dataRetorno = $ssolSolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed);
                                $msg_to_user = "Solicitação nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível salvar o anexo.";
                                //$this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView = $msg_to_user;
                            }
                        }
                    } else {
                        foreach ($nrDocsRed["existentes"] as $existentes) {
                            $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                            $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView = $msg_to_user;
                        }
                        $this->view->form = $form;
                        $this->render('atendimentointerno');
                        return;
                    }
                    $email = new Application_Model_DbTable_EnviaEmail();
                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                    $remetente = 'noreply@trf1.jus.br';
                    $destinatario = $matricula[0] . '@trf1.jus.br';
                    $assunto = 'Cadastro de Solicitação';
                    $corpo = "Foi cadastrada uma solicitação em seu nome</p>
                              Número da Solicitação: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " <br/>
                              Descrição da Solicitação: " . nl2br(substr($data['DOCM_DS_ASSUNTO_DOC'], 0, 4000)) . "<br/>
                              Observação da Solicitação: " . nl2br($data['SSOL_DS_OBSERVACAO']) . "<br/>";
//                    Zend_Debug::dump($corpo); exit('chegou');

                    try {
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                    }
                    if ($userNs->siglasecao == 'TR') {
                        return $this->_helper->_redirector('proxima', 'helpdesk', 'sosti', array('id' => $dataRetorno['DOCM_ID_DOCUMENTO'],
                            'acao' => 'nova'));
                    } else {
                        return $this->_helper->_redirector('atendimentointerno', 'helpdesk', 'sosti');
                    }
                } else {
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('atendimentointerno');
                }
            }
        }
        /**
         * Cadastrar solicitação externa
         */
        if (($data["Salvar"] == "Salvar") && ($data["acaor"] == "E")) {
            $this->view->title = "CADASTRO DE SOLICITAÇÃO EXTERNA";
            $form = new Sosti_Form_Atendimentoexterno();
            $formAnexo = new Sosti_Form_Anexo();
            $formAnexo->anexoUnico();
            $form->addElement($formAnexo->getElement('ANEXOS'));
            $formAnexo->submit();
            $form->addElement($formAnexo->getElement('Salvar'));
            $this->view->form = $form;
            $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $userNs = new Zend_Session_Namespace('userNs');
            if ($this->getRequest()->isPost()) {
                if ($form->ANEXOS->receive()) {
                    try {
                        $upload = new App_Multiupload_NewMultiUpload();
                        $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                    }
                }
                $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                $destino = Zend_Json::decode($data['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data['SGRS_ID_GRUPO'] => $destino["SGRS_DS_GRUPO"]));

                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                $sser_id_servico->addMultiOptions(array($data['SSER_ID_SERVICO'] => ''));

                $data = $this->getRequest()->getPost();
                if ($form->isValid($data)) {
                    $data["acaor"] = '';
                    $data['SSOL_ID_TIPO_CAD'] = 2; /* Telefone */
                    $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = $userNs->matricula;
                    $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = 160; //Solicitação de serviços a TI
                    $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = $userNs->uf; //TR solicitante que tá ligando
                    $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = $userNs->codlotacao; //1155 solicitante que tá ligando
                    $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = $userNs->uf; // 
                    $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = $userNs->codlotacao; // 
                    $dataDocmDocumento["DOCM_ID_PCTT"] = 2539; //PCTT Solicitação de TI
                    $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = $data['DOCM_DS_ASSUNTO_DOC'];
                    $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Sistuaçaõ Digital Gerado pelo sistema
                    $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Situaação Pública
                    $dataDocmDocumento["DOCM_DS_PALAVRA_CHAVE"] = substr($data['DOCM_DS_ASSUNTO_DOC'], 0, 100);

                    $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = 2; //telefone
                    $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = $data["SSOL_ED_LOCALIZACAO"];
                    $dataSsolSolicitacao["SSOL_NR_TOMBO"] = $data["SSOL_NR_TOMBO"];
                    $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"] = $data["SSOL_SG_TIPO_TOMBO"];
                    $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = $data["SSOL_DS_OBSERVACAO"];
                    $dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO'] = new Zend_Db_Expr("UPPER('$data[SSOL_NM_USUARIO_EXTERNO]')");
                    unset($dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO']);
                    $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = new Zend_Db_Expr("LOWER('$data[SSOL_DS_EMAIL_EXTERNO]')");
                    $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = $data['SSOL_NR_TELEFONE_EXTERNO'];

                    $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $userNs->uf;
                    $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $userNs->codlotacao;
                    //$dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = ;
                    $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;


                    $destino = Zend_Json::decode($data[SGRS_ID_GRUPO]);

                    $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $destino['SGRS_SG_SECAO_LOTACAO'];
                    $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $destino['SGRS_CD_LOTACAO'];
                    $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                    $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $destino['CXEN_ID_CAIXA_ENTRADA']; //Caixa de atendimento 

                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1006; //CADASTRO SOLICITACAO TI
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solictação";

                    $SosTbSnasNivelAtendSolic = new Application_Model_DbTable_SosTbSnasNivelAtendSolic();
                    $NivelAtendSolic = $SosTbSnasNivelAtendSolic->getPrimeiroNivel($destino["SGRS_ID_GRUPO"]);

                    if ($NivelAtendSolic) {
                        $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $NivelAtendSolic["SNAT_ID_NIVEL"];
                    } else {
                        $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = '';
                    }

                    $id_serviço = explode('|', $data["SSER_ID_SERVICO"]);
                    $dataSsesServicoSolic["SSES_ID_SERVICO"] = $id_serviço[0];

                    $dataAnexAnexo['NR_DOCUMENTO_INTERNO'] = null;

                    if ($nrDocsRed["erro"]) {
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                        $this->view->form = $form;
                        $this->render('atendimentoexterno');
                        return;
                    }
                    if (!$nrDocsRed["existentes"]) {
                        if (!$nrDocsRed["incluidos"]) {
                            try {
                                $dataRetorno = $ssolSolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic);
                                $msg_to_user = "Solicitação nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível salvar o anexo.";
                                $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView = $msg_to_user;
                            }
                        } else {
                            try {
                                $dataRetorno = $ssolSolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed);
                                $msg_to_user = "Solicitação nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível salvar o anexo.";
                                //$this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView = $msg_to_user;
                            }
                        }
                    } else {
                        foreach ($nrDocsRed["existentes"] as $existentes) {
                            $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                            $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView = $msg_to_user;
                        }
                        $this->view->form = $form;
                        $this->render('atendimentoexterno');
                        return;
                    }
                    $email = new Application_Model_DbTable_EnviaEmail();
                    $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                    $remetente = 'noreply@trf1.jus.br';
                    $destinatario = $data['SSOL_DS_EMAIL_EXTERNO'];
                    $assunto = 'Cadastro de Solicitação';
                    $corpo = "Foi cadastrada uma solicitação em seu nome</p>
                              Número da Solicitação: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " <br/>
                              Descrição da Solicitação: " . nl2br($dataDocmDocumento['DOCM_DS_PALAVRA_CHAVE']) . "<br/>
                              Observação da Solicitação: " . nl2br($dataSsolSolicitacao['SSOL_DS_OBSERVACAO']) . "<br/>";
                    if ($data["SSOL_NM_USUARIO_EXTERNO"] == null) {
                        try {
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                        }
                        /**
                         * Envia e-mail para os usuário externos
                         */
                    } else {
                        $email->setEnviarEmailExterno($sistema,  'CSTI@trf1.jus.br', $data["SSOL_DS_EMAIL_EXTERNO"], $assunto, $corpo);
                    }
                    
                    return $this->_helper->_redirector('proxima', 'helpdesk', 'sosti', array('id' => $dataRetorno['DOCM_ID_DOCUMENTO'],
                        'acao' => 'nova'));
                } else {
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('atendimentoexterno');
                }
            }
        }
        /**
         * Carregar a solicitação mais antiga já cadastrada
         */
        if ($data["acao"] == "Próxima") {
            return $this->_helper->_redirector('proxima', 'helpdesk', 'sosti', array('id' => $id));
        }
        /**
         * Carregar o formulário de atendimento interno
         */
        if (($data["acaor"] == "I") && ($data["Salvar"] != "Salvar")) {
            return $this->_helper->_redirector('atendimentointerno', 'helpdesk', 'sosti', array('id' => $id));
        }
        /**
         * Carregar o formulário de atendimento externo
         */
        if (($data["acaor"] == "E") && ($data["Salvar"] != "Salvar")) {
            return $this->_helper->_redirector('atendimentoexterno', 'helpdesk', 'sosti', array('id' => $id));
        }
    }

    public function esperacaixaAction()
    {
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
        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
        if ($this->getRequest()->isPost()) {
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
                $this->view->title = "1º NÍVEL - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES)";
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
                                    Descrição da motivo: " . nl2br($data["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        /**
                         * Fim do envio de email
                         */
                    }
                    $msg_to_user = "Solicitação(es) n(s)º " . substr($solicitacoesEncaminhadas, 1) . " colocada(s) em espera!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti');
                } else {
                    $solicspace = new Zend_Session_Namespace('solicspace');
                    $this->view->data = $solicspace->dados;
                    $this->view->title = "1º NÍVEL - COLOCAR EM ESPERA A(S) SOLICITAÇÃO(ES):";
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('esperacaixa');
                }
            }
        }
    }

    public function servicocaixaAction()
    {
        $form = new Sosti_Form_ServicoCaixa();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Serviço') {
                $solicitacao_array = explode('|', $data['solicitacao']);
                $this->view->data = $data['solicitacao'];
                $id = $solicitacao_array[0];
                $caixa = $solicitacao_array[1];
                $secao = $solicitacao_array[2];
                $unidade = $solicitacao_array[3];
                $nivel = $solicitacao_array[4];
                $servico = $solicitacao_array[5];
                $movimentacao = $solicitacao_array[6];
                $nrdocumento = $solicitacao_array[7];

                $form->DOCM_ID_DOCUMENTO->setValue($id);
                $form->MOFA_ID_MOVIMENTACAO->setValue($movimentacao);
                $form->DOCM_NR_DOCUMENTO->setValue($nrdocumento);

                $this->view->title = "TROCAR O SERVIÇO DA SOLICITAÇÃO:";
                $this->view->form = $form;
            } else {
                if ($form->isValid($data)) {
                    $data = $this->getRequest()->getPost();
                    $namespace = new Zend_Session_Namespace('userNs');
                    $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $data["MOFA_ID_MOVIMENTACAO"];
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $namespace->matricula; // Matricula de quem fez o encaminhamento da solicitação (matricula do cara do helpdesk)
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["MOFA_DS_COMPLEMENTO"];

                    $dataSsesServicoSolic["SSES_ID_SERVICO"] = $data['SSER_ID_SERVICO'];
                    $SserServico = new Application_Model_DbTable_SosTbSserServico();
                    $SserServico->trocaservicoSolicitacao($dataMofaMoviFase, $dataSsesServicoSolic);
                    $this->_helper->flashMessenger(array('message' => "Solicitação nº $data[DOCM_NR_DOCUMENTO] sofreu troca de Serviço!", 'status' => 'success'));
                    return $this->_helper->_redirector('primeironivel', 'helpdesk', 'sosti', array('id' => $id));
                } else {
                    $this->view->title = "TROCAR O SERVIÇO  - SOLICITAÇÃO Nº $data[DOCM_NR_DOCUMENTO]";
                    $form->populate($data);
                    $this->view->form = $form;
                }
            }
        }
    }

}
