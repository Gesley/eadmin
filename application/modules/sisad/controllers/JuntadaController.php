<?php

class Sisad_JuntadaController extends Zend_Controller_Action {

    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch() {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function init() {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        $this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
        // Informação
        $this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction() {
        $this->_helper->_redirector('index', 'index', 'admin');
    }

    /**
     * Monta view de Juntada de vários documentos à vários processos
     */
    public function documentoaprocessoAction() {
        if ($this->getRequest()->getPost()) {
            $service_caixaUnidade = new Services_Sisad_CaixaUnidade();
            $service_juntada = new Services_Sisad_Juntada();
            $service_lotacao = new Services_Rh_Lotacao();
            //plugin para buscar a unidade atual na sessao
            $plugin_acessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();

            $this->view->title = 'Juntada de Documentos à Processos';
            $this->view->familiaLotacao = $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade() . ' - ' . $service_lotacao->getFamiliaLotacao($plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade(), $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade());
            $this->view->formJuntada = new Sisad_Form_Juntada_DocumentoProcesso();
            //é escolhido o formulario de cadastro de juntada
            $this->view->formJuntada->add();
            //foi removido a tag form pois já esta incluida na view
            $this->view->formJuntada->removeDecorator('form');

            $dataRequest = $this->getRequest()->getPost();
            if (!isset($dataRequest['documentoPrincipal'])) {

                //define a action que veio os documentos
                $this->view->actionDocumentos = $dataRequest['action'];
                $this->view->controllerDocumentos = $dataRequest['controller'];

                /*
                 * Converte os dados json para array normal e filtra os documentos com possibilidade de juntada
                 * 
                 */
                //Filtra os documentos e completa com os campos necessários para o esquema de juntada
                $filtroDocumentos = $service_juntada->filtraFilhos($dataRequest['documento'], $this->view->action);
                if (count($filtroDocumentos['documentos']) == 0) {
                    foreach ($filtroDocumentos['mensagens'] as $mensagens) {
                        $this->_helper->flashMessenger(array('message' => $mensagens, 'status' => 'notice'));
                    }
                    //direcionar o usuário para a tela anterior
                    $this->_helper->_redirector($dataRequest['action'], $dataRequest['controller'], 'sisad');
                } else {
                    if (count($filtroDocumentos['mensagens']) > 0) {
                        $this->view->flashMessagesView = '
                        <div class = "notice ">
                            <strong>Alerta: </strong>
                            <ul>
                        ';
                        foreach ($filtroDocumentos['mensagens'] as $mensagens) {
                            $this->view->flashMessagesView .= '<li>' . $mensagens . '</li>';
                        }
                        $this->view->flashMessagesView .= '
                            </ul>
                        </div >';
                    }
                }

                $processosCaixa = $service_caixaUnidade->getProcessos(
                        array('SG_SECAO' => $plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade()
                            , 'CD_SECAO' => $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade()
                            , 'ORDER' => 'MODE_DH_RECEBIMENTO'
                        )
                );
                //Filtra os processos e completa com os campos necessários para o esquema de juntada
                $filtroProcessos = $service_juntada->filtraPrincipais($processosCaixa, $this->view->action);
                //se não existirem processos principais retorna para caixa unidade
                if (count($filtroProcessos['documentos']) == 0) {
                    $this->_helper->flashMessenger(array('message' => 'Não foi encontrado nenhum Processo Administrativo na Caixa da Unidade.', 'status' => 'notice'));
                    //direcionar o usuário para a tela anterior
                    $this->_helper->_redirector($dataRequest['action'], $dataRequest['controller'], 'sisad');
                }

                $this->view->arrayProcessosPrincipais = $filtroProcessos['documentos'];
                $this->view->jsonDocumentosParaJuntada = $filtroDocumentos['documentos'];
            } else {
                $session = new Zend_Session_Namespace('juntada');
                $session->data = $dataRequest;
                $session->action = $this->view->action;
                $this->_helper->_redirector('salvar', 'juntada', 'sisad');
            }
        } else {
            //colocar flashmessenger
            $this->_helper->flashMessenger(array('message' => 'Não foi selecionado nenhum Documento.', 'status' => 'notice'));
            return $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
        }
    }

    /**
     * Monta view de Juntada de varios processos a varios processos
     */
    public function processoaprocessoAction() {

        /*
          $sec = explode(" ",microtime());
          $fim = $sec[1] + $sec[0];
          echo number_format(($fim-$inicio),6);
         */
        //somente se tiver post e for pela caixa de entrada da unidade
        if ($this->getRequest()->getPost()) {
            $dataRequest = $this->getRequest()->getPost();

            if ($dataRequest['action'] . $dataRequest['controller'] != 'entrada' . 'caixaunidade') {
                $this->_helper->flashMessenger(array('message' => 'Acesso invalido.', 'status' => 'notice'));
                return $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
            }

            $service_caixaUnidade = new Services_Sisad_CaixaUnidade();
            $service_juntada = new Services_Sisad_Juntada();
            $service_lotacao = new Services_Rh_Lotacao();
            //plugin para buscar a unidade atual na sessao
            $plugin_acessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();

            $this->view->title = 'Juntada entre Processos Administrativos';
            $this->view->familiaLotacao = $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade() . ' - ' . $service_lotacao->getFamiliaLotacao($plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade(), $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade());
            $this->view->formJuntada = new Sisad_Form_Juntada_ProcessoProcesso();
            //é escolhido o formulario de cadastro de juntada
            $this->view->formJuntada->add();
            //foi removido a tag form pois já esta incluida na view
            $this->view->formJuntada->removeDecorator('form');

            if (!isset($dataRequest['documentoPrincipal'])) {

                //define a action que veio os documentos
                $this->view->actionDocumentos = $dataRequest['action'];
                $this->view->controllerDocumentos = $dataRequest['controller'];
                /*
                 * Converte os dados json para array normal e filtra os documentos com possibilidade de juntada
                 * 
                 */
                //Filtra os documentos e completa com os campos necessários para o esquema de juntada
                $filtroProcessosFilhos = $service_juntada->filtraFilhos($dataRequest['documento'], $this->view->action);

                if (count($filtroProcessosFilhos['documentos']) == 0) {
                    foreach ($filtroProcessosFilhos['mensagens'] as $mensagens) {
                        $this->_helper->flashMessenger(array('message' => $mensagens, 'status' => 'notice'));
                    }
                    //direcionar o usuário para a tela anterior
                    $this->_helper->_redirector($dataRequest['action'], $dataRequest['controller'], 'sisad');
                } else {
                    if (count($filtroProcessosFilhos['mensagens']) > 0) {
                        $this->view->flashMessagesView = '
                        <div class = "notice ">
                            <strong>Alerta: </strong>
                            <ul>
                        ';
                        foreach ($filtroProcessosFilhos['mensagens'] as $mensagens) {
                            $this->view->flashMessagesView .= '<li>' . $mensagens . '</li>';
                        }
                        $this->view->flashMessagesView .= '
                            </ul>
                        </div >';
                    }
                }

                $processosCaixa = $service_caixaUnidade->getProcessos(
                        array('excluidos' => $filtroProcessosFilhos['documentos']
                            , 'SG_SECAO' => $plugin_acessoCaixaUnidade->getSgsecaoCaixaUnidade()
                            , 'CD_SECAO' => $plugin_acessoCaixaUnidade->getCdlotacaoCaixaUnidade()
                            , 'ORDER' => 'MODE_DH_RECEBIMENTO'
                        )
                );
                // Iniciamos o "contador"
                //Filtra os processos e completa com os campos necessários para o esquema de juntada
                $filtroProcessosPais = $service_juntada->filtraPrincipais($processosCaixa, $this->view->action, $filtroProcessosFilhos['documentos']);

                //se não existirem processos principais retorna para caixa unidade
                if (count($filtroProcessosPais['documentos']) == 0) {
                    $this->_helper->flashMessenger(array('message' => 'Não foi encontrado nenhum Processo Administrativo na Caixa da Unidade. Talvez você tenha selecionado todos os processos administrativos da caixa.', 'status' => 'notice'));
                    //direcionar o usuário para a tela anterior
                    $this->_helper->_redirector($dataRequest['action'], $dataRequest['controller'], 'sisad');
                }

                $this->view->arrayProcessosPrincipais = $filtroProcessosPais['documentos'];
                $this->view->processosJuntados = $filtroProcessosPais['juntada'];
                $this->view->jsonDocumentosParaJuntada = $filtroProcessosFilhos['documentos'];
            } else {
                $session = new Zend_Session_Namespace('juntada');
                $session->data = $dataRequest;
                $session->action = $this->view->action;
                $this->_helper->_redirector('salvar', 'juntada', 'sisad');
            }
        } else {
            //colocar flashmessenger
            $this->_helper->flashMessenger(array('message' => 'Não foi selecionado nenhum Documento.', 'status' => 'notice'));
            return $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
        }
    }

    /**
     * Salva vinculação
     * Não possui view
     * 
     * Para utilizar a action é necessário criar as seguintes sessões:
     * 
     * $session = new Zend_Session_Namespace('juntada');
     * $session->data = $dataRequest;
     * $session->module = $this->getRequest()->getModuleName();
     * $session->controller = $this->getRequest()->getControllerName();
     * $session->action = $this->getRequest()->getActionName();
     * $this->_helper->_redirector('salvar', 'vinculacao', 'sisad');
     */
    public function salvarAction() {

        $session = new Zend_Session_Namespace('juntada');

        if ($session->data) {

            $service_juntada = new Services_Sisad_Juntada();
            $mensagens = $service_juntada->juntarVarios($session->data, $session->action);

            $destinoUrl = array($session->data['action'], $session->data['controller'], 'sisad');

            unset($session->data);

            foreach ($mensagens as $mensagem) {
                $this->_helper->flashMessenger(($mensagem['validado']) ? array('message' => $mensagem['mensagem'], 'status' => 'success') : array('message' => $mensagem['mensagem'], 'status' => 'error') );
            }
            return $this->_helper->_redirector($destinoUrl[0], $destinoUrl[1], $destinoUrl[2]);
        }
        return $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
    }

    public function removerapensoAction() {
        $server = new Zend_Json_Server_Request_Http();
        $data = Zend_Json::decode($server->getRawJson());
        if (isset($data)) {
            $service_juntada = new Services_Sisad_Juntada();
            $resposta = $service_juntada->desativar($data['ID_PROCESSO_PAI'], $data['ID_PROCESSO_FILHO'], Trf1_Sisad_Definicoes::ID_VINCULACAO_APENSAR, 'processoaprocesso');
            $this->_helper->json->sendJson($resposta);
        } else {
            Zend_Debug::dump('erro');
            exit;
        }
    }

    public function removeranexoprocessoAction() {
        $server = new Zend_Json_Server_Request_Http();
        $data = Zend_Json::decode($server->getRawJson());
        if (isset($data)) {
            $service_juntada = new Services_Sisad_Juntada();
            $resposta = $service_juntada->desativar($data['ID_PROCESSO_PAI'], $data['ID_PROCESSO_FILHO'], Trf1_Sisad_Definicoes::ID_VINCULACAO_ANEXAR, 'processoaprocesso');
            $this->_helper->json->sendJson($resposta);
        } else {
            Zend_Debug::dump('erro');
            exit;
        }
    }

    public function removervinculoAction() {
        $server = new Zend_Json_Server_Request_Http();
        $data = Zend_Json::decode($server->getRawJson());
        if (isset($data)) {
            $service_juntada = new Services_Sisad_Juntada();
            $resposta = $service_juntada->desativar($data['ID_PROCESSO_PAI'], $data['ID_PROCESSO_FILHO'], Trf1_Sisad_Definicoes::ID_VINCULACAO_VINCULAR, 'processoaprocesso');
            $this->_helper->json->sendJson($resposta);
        } else {
            Zend_Debug::dump('erro');
            exit;
        }
    }

}
