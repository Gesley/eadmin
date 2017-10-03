<?php

class Sisad_DetalhedcmtoController extends Zend_Controller_Action {

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

        $this->view->titleBrowser = 'e-Sisad';
    }

    public function indexAction() {
        
    }

    public function detalhedcmtoAction() {
          $host         = $_SERVER['SERVER_NAME']; 
          $http         = $host;
          $fullBaseUrl  = $this->getFrontController()->getBaseUrl();
          $this->view->http = $http;
          $this->view->fullBaseUrl = $fullBaseUrl;
        $form = new Sosti_Form_Anexo();
        $form->anexoUnico();
        $this->view->form = $form;
        $rnDocumento = new Trf1_Sisad_Negocio_Documento();
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $rn_fase = new Trf1_Sisad_Negocio_Fase();
        /* Juntada Anexo Documentos do Processo */
        $rn_juntadaProcesso = new Trf1_Sisad_Negocio_JuntadaProcessoProcesso();
        $arrayAssinaturas = array();

        if ($this->getRequest()->isPost()) {
            //array ainda não esta completamente em uso
            $arrayDetalhe = array(
                'dados_documento' => array()
                , 'dados_processo' => array()
                , 'vistas' => array()
                , 'interessados' => array()
                , 'juntada' => array(
                    'anexo' => array('documento_processo' => array())
                    , 'apenso' => array()
                    , 'vinculo' => array()
                )
                , 'historico_documento' => array()
                , 'despacho' => array('mostrar' => array())
                , 'protocolo' => array('enderecados' => array(), 'protocolados' => array())
            );

            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $this->view->controller = $data["controller"];
            $this->view->caixa = $data["CAIXA_REQUISICAO"];
            $userNs = new Zend_Session_Namespace('userNs');

            if ($data['CAIXA_REQUISICAO'] == 'rascunhos') {
                $DocmDocumento = $mapperDocumento->getDadosDCMTORascunho($data["DOCM_ID_DOCUMENTO"]);
                $DocmDocumento['CAIXA_REQUISICAO'] = $data['CAIXA_REQUISICAO'];
                $DocmDocumento['controller'] = $data['controller'];
                $this->view->DocmDocumento = $DocmDocumento;
            } else {
                $DocmDocumento = $mapperDocumento->getDadosDCMTO($data["DOCM_ID_DOCUMENTO"]);
                if (isset($data['CAIXA_REQUISICAO'])) {
                    $DocmDocumento['CAIXA_REQUISICAO'] = $data['CAIXA_REQUISICAO'];
                    $DocmDocumento['controller'] = $data['controller'];
                } else {
                    $DocmDocumento['CAIXA_REQUISICAO'] = null;
                    $DocmDocumento['controller'] = null;
                }
                $this->view->DocmDocumento = $DocmDocumento;
            }

            $Ns_Caixaunidade_despacho = new Zend_Session_Namespace('Ns_Caixaunidade_despacho');
            $Ns_Caixaunidade_despacho->data_post_caixa['documento'] = Zend_Json::encode(($DocmDocumento));
            $Ns_Caixaunidade_despacho->data_post_caixa['controller'] = (isset($data['controller'])) ? ($data['controller']) : (null);
            $Ns_Caixaunidade_despacho->data_post_caixa['action'] = 'entrada';
            $Ns_Caixaunidade_despacho->data_post_caixa['despachodetalhe'] = 'S';

            $AnexAnexo = $SadTbAnexAnexo->getDadosAnexo($data["DOCM_ID_DOCUMENTO"]);
            $this->view->AnexAnexo = $AnexAnexo;

            $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();

            //$this->_helper->layout->disableLayout();
            $this->view->DocumentosProcesso = array();
            $dadoProcesso = null;
            if ($data["DTPD_NO_TIPO"] == "Processo administrativo") {
                $DocumentosProcesso = $SadTbPrdiProcessoDigital->getdocsProcesso($data["DOCM_ID_DOCUMENTO"]);
                $dadoProcesso = $SadTbPrdiProcessoDigital->getProcesso($data["DOCM_ID_DOCUMENTO"]);
                $this->view->objeto = $dadoProcesso['PRDI_DS_TEXTO_AUTUACAO'];
                $this->view->idproc = $data["DOCM_ID_DOCUMENTO"];
                //Zend_debug::dump($DocumentosProcesso[0][ID_PROCESSO]);
                $interessadosProcesso = $SadTbPapdParteProcDoc->getPartesVistas(null, $DocumentosProcesso[0]["ID_PROCESSO"], 1); //1 = Parte/
                $vistaProcDoc = $SadTbPapdParteProcDoc->getPartesVistas(null, $DocumentosProcesso[0]["ID_PROCESSO"], 3); //3 = Parte/
                $temVista = $SadTbPapdParteProcDoc->verificaParteVista(null, $DocumentosProcesso[0]["ID_PROCESSO"], 3); //3 = Tem vista

                $cont = 0;
                foreach ($DocumentosProcesso as $documentos) {
                    if ($documentos["CONF_ID_CONFIDENCIALIDADE"] != "0") {

                        $temVistaDoc = $SadTbPapdParteProcDoc->verificaParteVista($documentos["DOCM_ID_DOCUMENTO"], null, 3); //3 = Tem vista

                        if (!$temVistaDoc) {
                            $DocumentosProcesso[$cont]['AQAT_DS_ATIVIDADE'] = 'RESERVADO';
                        }
                    }
                    $cont++;

                    /**
                     * Obtendo os ID dos documentos para buscar as assinaturas
                     */
                    $arrayIdDocumento[] = $documentos['DOCM_ID_DOCUMENTO'];
                }

                /**
                 * Pesquisar as assinaturas de cada documento do processo
                 */
                if (count($arrayIdDocumento) > 0) {
                    foreach ($arrayIdDocumento as $idDocumento) {
                        /**
                         * Array na posição ID do documento, recebe as assinaturas do mesmo
                         * 1018 - Fase de assinatura SISAD
                         */
                        $arrayAssinaturas[$idDocumento] = $rn_fase->getAssinaturasDocumento($idDocumento, 1018);
                    }
                }
                /* Juntada Apensação Processo a Processo */
                $arrayDetalhe['juntada']['anexo']['processo_processo'] = $rn_juntadaProcesso->getProcessosAnexados($dadoProcesso);
                /* Juntada Apensação Processo a Processo */
                $arrayDetalhe['juntada']['apenso']['processo_processo'] = $rn_juntadaProcesso->getProcessosApensados($dadoProcesso);
                /* Juntada Vinculação Processo a Processo */
                $arrayDetalhe['juntada']['vinculado']['processo_processo'] = $rn_juntadaProcesso->getProcessosVinculados($dadoProcesso);
                $this->view->assinaturasDocumento = $arrayAssinaturas;
                $this->view->DocumentosProcesso = $DocumentosProcesso;
                $this->view->interessados = $interessadosProcesso;
                $this->view->vistas = $vistaProcDoc;
            } else {
                if ($data["DTPD_NO_TIPO"] == "Minuta") {
                    $this->view->tipodoc = 'Minuta';
                }
                $interessadosProcesso = $SadTbPapdParteProcDoc->getPartesVistas($data["DOCM_ID_DOCUMENTO"], null, 1); //1 = Parte
                $vistaProcDoc = $SadTbPapdParteProcDoc->getPartesVistas($data["DOCM_ID_DOCUMENTO"], null, 3); //3 = Parte/
                $temVista = $SadTbPapdParteProcDoc->verificaParteVista($data["DOCM_ID_DOCUMENTO"], null, 3); //3 = Tem vista
                $this->view->interessados = $interessadosProcesso;
                $this->view->vistas = $vistaProcDoc;

                //verifica as assinaturas digitais
                $arrayDetalhe['assinatura']['digital'] = $rnDocumento->getAssinaturas($data["DOCM_ID_DOCUMENTO"]);
            }

            $this->view->ProcessosDocumento = $SadTbPrdiProcessoDigital->getProcessosDocumento($data["DOCM_ID_DOCUMENTO"]);
            $dadosJuntada = $SadTbPrdiProcessoDigital->getDadosJuntadaDocumentoProcesso($data["DOCM_ID_DOCUMENTO"]);
            $numProcessos = NULL;
            foreach ($dadosJuntada as $dadoJuntada) {
                $numProcessos = $numProcessos . $dadoJuntada["DOCM_NR_DOCUMENTO_PROC"] . ' em ' . $dadoJuntada["DCPR_DH_VINCULACAO_DOC"] . ' por ' . $dadoJuntada["PNAT_NO_PESSOA"];
                $numProcessos = $numProcessos . '; ' . '<br>';
            }

            $sigiloso = 'N';

            /* Confidencialidade:
             * 1 - Restrito as partes
             * 3 - As partes segredo de justiça
             * 4 - Ao subgrupo sigiloso
             * 5 - Corregedoria
             */
            if (in_array($DocmDocumento["CONF_ID_CONFIDENCIALIDADE"], array("1", "3", "4", "5"))) {
                $sigiloso = 'S';

                if ($DocmDocumento["CONF_ID_CONFIDENCIALIDADE"] == "5") {
                    $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                    $UsuarioCorregedoria = $OcsTbPupePerfilUnidPessoa->verificaPermissaoCorregedoria();
                }

                if (($temVista) || (!empty($UsuarioCorregedoria))) {
                    $sigiloso = 'N';
                } else {
                    $sigiloso = 'S';
                }
            }

            if ($sigiloso == 'S') {
                return $this->render('detalhedcmtoconf');
            } else {
                if (isset($data['MOFA_ID_MOVIMENTACAO']) && $data['MOFA_ID_MOVIMENTACAO'] &&
                        ($data['CAIXA_REQUISICAO'] == 'entrada') || ($data['CAIXA_REQUISICAO'] == 'minutas')) {
                    $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
                    $SadTbModeMoviDestinatario->setLeitura($data['MOFA_ID_MOVIMENTACAO'], $userNs->matricula);
                }
            }
            
            $DocmDocumentoHistorico = $mapperDocumento->getHistoricoDCMTO($data["DOCM_ID_DOCUMENTO"]);
            $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico;
            
            $arrayDetalhe['historico_documento'] = $DocmDocumentoHistorico;
            $arrayDetalhe['dados_documento'] = $DocmDocumento;
            $arrayDetalhe['dados_processo'] = $dadoProcesso;
            $this->view->arrayDetalhe = $arrayDetalhe;
            $this->view->ProcessosDocumento = $numProcessos;
            $this->view->NumDocPrincipal = $SadTbVidcVinculacaoDoc->getDocPrincipal($data["DOCM_ID_DOCUMENTO"]); //Caso seja minuta      
            $this->view->NumDocVinculado = $SadTbVidcVinculacaoDoc->getDocVinculado($data["DOCM_ID_DOCUMENTO"]); //Caso tenha minuta
        }
    }

    public function detalhedcmtoconfAction() {
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();

        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());

            if (isset($data[MOFA_ID_MOVIMENTACAO]) && $data[MOFA_ID_MOVIMENTACAO] && $data['CAIXA_REQUISICAO'] == 'entrada') {
                $aNamespace = new Zend_Session_Namespace('userNs');
                $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
                $SadTbModeMoviDestinatario->setLeitura($data[MOFA_ID_MOVIMENTACAO], $aNamespace->matricula);
            }

            $DocmDocumento = $mapperDocumento->getDadosDCMTO($data[DOCM_ID_DOCUMENTO]);
            $this->view->DocmDocumento = $DocmDocumento;

            if ($data[DTPD_NO_TIPO] == "Processo administrativo") {
                $DocumentosProcesso = $SadTbPrdiProcessoDigital->getdocsProcesso($data[DOCM_ID_DOCUMENTO]);
                //Zend_debug::dump($DocumentosProcesso[0][ID_PROCESSO]);
                $vistaProcDoc = $SadTbPapdParteProcDoc->getPartesVistas(null, $DocumentosProcesso[0][ID_PROCESSO], 3); //3 = Parte/
            } else {
                //Zend_debug::dump($data[DOCM_ID_DOCUMENTO]);
                $vistaProcDoc = $SadTbPapdParteProcDoc->getPartesVistas($data[DOCM_ID_DOCUMENTO], null, 3); //3 = Parte/
            }
            $this->view->vistas = $vistaProcDoc;
        }
    }

    public function detalhedcmtocorrespondenciaAction() {
        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());

            $PostPostagem = new Application_Model_DbTable_SadTbPostPostagemProcDoc();
            if (!isset($data["PRDC_ID_PROTOCOLO"])) {
                $documentos = $PostPostagem->getDadosDocumentosPostagem($data["PRDC_ID_POSTAGEM_PROC_DOC"], null);
            } else if (!isset($data["PRDC_ID_POSTAGEM_PROC_DOC"])) {
                $documentos = $PostPostagem->getDadosDocumentosPostagem(null, $data["PRDC_ID_PROTOCOLO"]);
            }
            $mapperDocumento = new Sisad_Model_DataMapper_Documento();
            $i = 0;
            foreach ($documentos as $value) {
                $dadosDocumento = $mapperDocumento->getDadosDCMTO($value["PRDC_ID_DOCUMENTO"]);
                $dados[$i] = $dadosDocumento;
                $dados[$i]['DADOS_INPUT'] = Zend_Json::encode($dadosDocumento);
                $i++;
            }
            $this->view->post = $data;
            $this->view->data = $dados;
        }
    }

    public function detalhedcmtavisoAction() {
        if ($this->getRequest()->isPost()) {
            $mapperDocumento = new Sisad_Model_DataMapper_Documento();
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
            $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();

            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());

            $DocmDocumento = $mapperDocumento->getDadosDCMTO($data);
            $this->view->DocmDocumento = $DocmDocumento;

            $DocmDocumentoHistorico = $mapperDocumento->getHistoricoDCMTO($data);
            $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico;

            $AnexAnexo = $SadTbAnexAnexo->getDadosAnexo($data);
            $this->view->AnexAnexo = $AnexAnexo;

            if ($data[DTPD_NO_TIPO] == "Processo administrativo") {
                $DocumentosProcesso = $SadTbPrdiProcessoDigital->getdocsProcesso($data[DOCM_ID_DOCUMENTO]);
                $dadoProcesso = $SadTbPrdiProcessoDigital->getProcesso($data[DOCM_ID_DOCUMENTO]);
                $this->view->objeto = $dadoProcesso['PRDI_DS_TEXTO_AUTUACAO'];
                //Zend_debug::dump($DocumentosProcesso[0][ID_PROCESSO]);
                $interessadosProcesso = $SadTbPapdParteProcDoc->getPartesVistas(null, $DocumentosProcesso[0][ID_PROCESSO], 1); //1 = Parte/
                $vistaProcDoc = $SadTbPapdParteProcDoc->getPartesVistas(null, $DocumentosProcesso[0][ID_PROCESSO], 3); //3 = Parte/
                $temVista = $SadTbPapdParteProcDoc->verificaParteVista(null, $DocumentosProcesso[0][ID_PROCESSO], 3); //3 = Tem vista

                $cont = 0;
                foreach ($DocumentosProcesso as $documentos) {
                    if ($documentos[CONF_ID_CONFIDENCIALIDADE] != "0") {

                        $temVistaDoc = $SadTbPapdParteProcDoc->verificaParteVista($documentos[DOCM_ID_DOCUMENTO], null, 3); //3 = Tem vista

                        if (!$temVistaDoc) {
                            $DocumentosProcesso[$cont]['AQAT_DS_ATIVIDADE'] = 'RESERVADO';
                        }
                    }
                    $cont++;
                }

                $this->view->DocumentosProcesso = $DocumentosProcesso;
                $this->view->interessados = $interessadosProcesso;
                $this->view->vistas = $vistaProcDoc;
            }

            $this->view->post = $data;
            $this->view->data = $dados;
        }
    }

}
