<?php

class Sisad_LeituraController extends Zend_Controller_Action {

    /**
     * Timer para mensuracao do tempo de carregamento da página
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch() {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    private $userNs;

    public function init() {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        $this->userNs = new Zend_Session_Namespace('userNs');
        $this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
    }

    /**
     * Responsavel por exbir a visualização dos documentos
     * 
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     * @tutorial DESCREVER
     */
    public function indexAction() {
        $this->_helper->layout()->disableLayout();
        $service_documento = new Services_Sisad_Documento();
        $service_leitura = new Services_Sisad_Leitura();
        $service_processo = new Services_Sisad_Processo();
        $service_juntada = new Services_Sisad_Juntada();

        //busca os dados do documento via post
        $dataPost = $this->getRequest()->getPost(); /* Aplica Filtros - Mantem Post */
        $documentoPost = Zend_Json::decode($dataPost['documento'][0]);
        //se não existir documento no post
        if (is_null($documentoPost)) {
            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
        }

        $formAnexo = new Sisad_Form_Leitura_Juntada();
        //escolhe o formulario de pesquisa para ser usado nos filtros
        $formAnexo->pesquisaAnexo();
        $this->view->formPesquisaAnexos = $formAnexo;
        $formApenso = new Sisad_Form_Leitura_Juntada();
        $formApenso->pesquisaApenso();
        $this->view->formPesquisaApensos = $formApenso;
        $formVinculo = new Sisad_Form_Leitura_Juntada();
        $formVinculo->pesquisaVinculos();
        $this->view->formPesquisaVinculos = $formVinculo;

        //pega os dados do documento
        $documento = $service_documento->getDocumento($documentoPost['DOCM_ID_DOCUMENTO']);
        if ($documento != false) {
            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {

                $processo = $service_processo->getProcessoPorIdDocumento($documento);
                $documento = array_merge($documento, $processo);
                $anexos = $service_leitura->getAnexados($documento);
                $apensos = $service_juntada->getApensos($documento);
                $vinculos = $service_juntada->getVinculos($documento);
            } else {
                $anexos = array();
                $apensos = array();
                $vinculos = array();
            }

            $service_parteVista = new Services_Sisad_ParteVista();
            $isVisivelAoUsuario = $service_parteVista->isVisivelAoUsuario($documento);
            //não visivél ao usuário
            if (!$isVisivelAoUsuario) {
                $this->_helper->flashMessenger(array('message' => 'O usuário não possui vistas ao documento.', 'status' => 'notice'));
                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
            }

            $anexosSemMetadados = $service_leitura->getAnexadosSemMetadados($documento);

            $historico = $service_documento->getHistorico($documento);
            $minuta = array('documento_da_minuta', 'minuta_do_documento');
            //caso tenha minuta busca o documento da minuta
            $minuta['documento_da_minuta'] = $service_documento->getDocumentoDaMinuta($documento['DOCM_ID_DOCUMENTO']);

            //dados do documento principal na leitura
            $this->view->documento = $documento;
            //array de documentos/processos anexados ao documento principal na leitura
            $this->view->minuta = $minuta;
            $this->view->anexos = $anexos;
            $this->view->anexos_sem_metadados = $anexosSemMetadados;
            $this->view->apensos = $apensos;
            $this->view->vinculos = $vinculos;
            $this->view->historico = $historico;
        } else {
            $this->_helper->flashMessenger(array('message' => 'Documento não localizado', 'status' => 'notice'));
            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
        }
    }

    /**
     * Carrega o html da leitura do documento
     * 
     * retorna string html da tela para ser usado em nova aboa
     */
    public function leituradocumentosajaxAction() {
        // $this->_helper->layout()->disableLayout();
        $service_documento = new Services_Sisad_Documento();
        $service_leitura = new Services_Sisad_Leitura();
        $service_processo = new Services_Sisad_Processo();
        $service_juntada = new Services_Sisad_Juntada();

        //busca os dados do documento dentre os parametros da requisição
        $data = $this->getRequest()->getParams(); /* Aplica Filtros - Mantem Post */
        if (is_null($data['DOCM_ID_DOCUMENTO'])) {
            $this->view->mensagem = $this->getMensagemDiv('Dados passados via ajax são invalidos.', 'error');
        }

        $formAnexo = new Sisad_Form_Leitura_Juntada();
        //escolhe o formulario de pesquisa para ser usado nos filtros
        $formAnexo->pesquisaAnexo();
        $this->view->formPesquisaAnexos = $formAnexo;
        $formApenso = new Sisad_Form_Leitura_Juntada();
        $formApenso->pesquisaApenso();
        $this->view->formPesquisaApensos = $formApenso;
        $formVinculo = new Sisad_Form_Leitura_Juntada();
        $formVinculo->pesquisaVinculos();
        $this->view->formPesquisaVinculos = $formVinculo;

        //pega os dados do documento
        $documento = $service_documento->getDocumento($data['DOCM_ID_DOCUMENTO']);
        if ($documento != false) {
            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {

                $processo = $service_processo->getProcessoPorIdDocumento($documento);
                $documento = array_merge($documento, $processo);
                $anexos = $service_leitura->getAnexados($documento);
                $apensos = $service_juntada->getApensos($documento);
                $vinculos = $service_juntada->getVinculos($documento);
            } else {
                $anexos = array();
                $apensos = array();
                $vinculos = array();
            }

            $service_parteVista = new Services_Sisad_ParteVista();
            $isVisivelAoUsuario = $service_parteVista->isVisivelAoUsuario($documento);
            //não visivél ao usuário
            if (!$isVisivelAoUsuario) {
                $this->view->mensagem = $this->getMensagemDiv('O usuário não possui vistas ao documento', 'notice');
            } else {
                $anexosSemMetadados = $service_leitura->getAnexadosSemMetadados($documento);
                $historico = $service_documento->getHistorico($documento);
                $minuta = array('documento_da_minuta', 'minuta_do_documento');
                //caso tenha minuta busca o documento da minuta
                $minuta['documento_da_minuta'] = $service_documento->getDocumentoDaMinuta($documento['DOCM_ID_DOCUMENTO']);

                //dados do documento principal na leitura
                $this->view->documento = $documento;
                //array de documentos/processos anexados ao documento principal na leitura
                $this->view->minuta = $minuta;
                $this->view->anexos = $anexos;
                $this->view->anexos_sem_metadados = $anexosSemMetadados;
                $this->view->apensos = $apensos;
                $this->view->vinculos = $vinculos;
                $this->view->historico = $historico;
                $this->view->documento_post = $data;
            }
        } else {
            //formulário não validado
            $this->view->mensagem = $this->getMensagemDiv('Documento não localizado', 'error');
        }
    }
    
    /**
     * Responsável por retornar a tabela de documentos anexados
     * recebe como parametro:
     * id do documento
     * dados formulario de pesquisa
     * 
     * retorna string tabela em html
     */
    public function documentosanexadosajaxAction() {
        //$this->_helper->layout()->disableLayout();
        $service_leitura = new Services_Sisad_Leitura();
        $service_documento = new Services_Sisad_Documento();
        $service_processo = new Services_Sisad_Processo();

        $data = $this->getRequest()->getParams(); /* Aplica Filtros - Mantem Post */
        $filtro = $data;
        $documentoPost = array('DOCM_ID_DOCUMENTO' => $data['DOCM_ID_DOCUMENTO_PRINCIPAL']);
        $documento = $service_documento->getDocumento($documentoPost['DOCM_ID_DOCUMENTO']);

        if ($documento != false) {
            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {

                $processo = $service_processo->getProcessoPorIdDocumento($documento);
                $documento = array_merge($documento, $processo);
            }
        }
        $form = new Sisad_Form_Leitura_Juntada();
        //escolhe o formulario de pesquisa para ser usado nos filtros
        $form->pesquisaAnexo();
        if ($form->isValid($filtro)) {
            $anexos = $service_leitura->filtroAnexados($documento, $filtro);
            if (count($anexos) == 0) {
                $this->view->mensagem = $this->getMensagemDiv('O filtro não localizou documentos anexados.');
            }
            $this->view->documento = $documento;
            $this->view->anexos = $anexos;
        } else {
            //formulário não validado
            $this->view->mensagem = $this->getMensagemDiv($filtro, 'form', $form);
        }
    }

    /**
     * Responsável por retornar a tabela de documentos anexados
     * recebe como parametro:
     * id do documento
     * dados formulario de pesquisa
     * 
     * retorna string tabela em html
     */
    public function documentosapensadosajaxAction() {
        //$this->_helper->layout()->disableLayout();
        $service_leitura = new Services_Sisad_Leitura();
        $service_documento = new Services_Sisad_Documento();
        $service_processo = new Services_Sisad_Processo();

        $data = $this->getRequest()->getParams(); /* Aplica Filtros - Mantem Post */
        $filtro = $data;
        $documentoPost = array('DOCM_ID_DOCUMENTO' => $data['DOCM_ID_DOCUMENTO_PRINCIPAL']);
        $documento = $service_documento->getDocumento($documentoPost['DOCM_ID_DOCUMENTO']);

        if ($documento != false) {
            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {

                $processo = $service_processo->getProcessoPorIdDocumento($documento);
                $documento = array_merge($documento, $processo);
            }
        }
        $form = new Sisad_Form_Leitura_Juntada();
        //escolhe o formulario de pesquisa para ser usado nos filtros
        $form->pesquisaAnexo();
        if ($form->isValid($filtro)) {
            $apensos = $service_leitura->filtroApensados($documento, $filtro);
            if (count($apensos) == 0) {
                $this->view->mensagem = $this->getMensagemDiv('O filtro não localizou documentos apensado.');
            } else {
                $this->view->documento = $documento;
                $this->view->apensos = $apensos;
            }
        } else {
            //formulário não validado
            $this->view->mensagem = $this->getMensagemDiv($filtro, 'form', $form);
        }
    }
    
    /**
     * Responsável por retornar a tabela de documentos vinculados
     * recebe como parametro:
     * id do documento
     * dados formulario de pesquisa
     * 
     * retorna string tabela em html
     */
    public function documentosvinculadosajaxAction() {
        
        //$this->_helper->layout()->disableLayout();
        $service_leitura = new Services_Sisad_Leitura();
        $service_documento = new Services_Sisad_Documento();
        $service_processo = new Services_Sisad_Processo();

        $data = $this->getRequest()->getParams(); /* Aplica Filtros - Mantem Post */
        $filtro = $data;
        $documentoPost = array('DOCM_ID_DOCUMENTO' => $data['DOCM_ID_DOCUMENTO_PRINCIPAL']);
        $documento = $service_documento->getDocumento($documentoPost['DOCM_ID_DOCUMENTO']);

        if ($documento != false) {
            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {

                $processo = $service_processo->getProcessoPorIdDocumento($documento);
                $documento = array_merge($documento, $processo);
            }
        }
        $form = new Sisad_Form_Leitura_Juntada();
        //escolhe o formulario de pesquisa para ser usado nos filtros
        $form->pesquisaVinculos();
        if ($form->isValid($filtro)) {
            $vinculos = $service_leitura->filtroVinculos($documento, $filtro);
            if (count($vinculos) == 0) {
                $this->view->mensagem = $this->getMensagemDiv('O filtro não localizou documentos vinculado.');
            } else {
                $this->view->documento = $documento;
                $this->view->vinculos = $vinculos;
            }
        } else {
            //formulário não validado
            $this->view->mensagem = $this->getMensagemDiv($filtro, 'form', $form);
        }
    }

    function metadadosAction() {
        // $this->_helper->layout()->disableLayout();
        $service_documento = new Services_Sisad_Documento();
        $service_processo = new Services_Sisad_Processo();
        //busca os dados do documento dentre os parametros da requisição
        $data = $this->getRequest()->getParams(); /* Aplica Filtros - Mantem Post */
        if (is_null($data['DOCM_ID_DOCUMENTO'])) {
            $this->view->mensagem = $this->getMensagemDiv('Dados passados via ajax são invalidos.', 'error');
        }
        //pega os dados do documento
        $documento = $service_documento->getDocumento($data['DOCM_ID_DOCUMENTO']);
        if ($documento != false) {
            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {

                $processo = $service_processo->getProcessoPorIdDocumento($documento);
                $documento = array_merge($documento, $processo);
            }
            $service_parteVista = new Services_Sisad_ParteVista();
            $isVisivelAoUsuario = $service_parteVista->isVisivelAoUsuario($documento);
            //não visivél ao usuário
            if (!$isVisivelAoUsuario) {
                $this->view->mensagem = $this->getMensagemDiv('O usuário não possui vistas ao documento', 'notice');
            } else {
                //dados do documento principal na leitura
                $this->view->documento = $documento;
            }
        } else {
            //formulário não validado
            $this->view->mensagem = $this->getMensagemDiv('Documento não localizado', 'error');
        }
    }

    function historicoAction() {
        // $this->_helper->layout()->disableLayout();
        $service_documento = new Services_Sisad_Documento();
        $service_leitura = new Services_Sisad_Leitura();
        $service_processo = new Services_Sisad_Processo();

        //busca os dados do documento dentre os parametros da requisição
        $data = $this->getRequest()->getParams(); /* Aplica Filtros - Mantem Post */
        if (is_null($data['DOCM_ID_DOCUMENTO'])) {
            $this->view->mensagem = $this->getMensagemDiv('Dados passados via ajax são invalidos.', 'error');
        }
        //pega os dados do documento
        $documento = $service_documento->getDocumento($data['DOCM_ID_DOCUMENTO']);
        if ($documento != false) {
            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {

                $processo = $service_processo->getProcessoPorIdDocumento($documento);
                $documento = array_merge($documento, $processo);
            }

            $service_parteVista = new Services_Sisad_ParteVista();
            $isVisivelAoUsuario = $service_parteVista->isVisivelAoUsuario($documento);
            //não visivél ao usuário
            if (!$isVisivelAoUsuario) {
                $this->view->mensagem = $this->getMensagemDiv('O usuário não possui vistas ao documento', 'notice');
            } else {
                $anexosSemMetadados = $service_leitura->getAnexadosSemMetadados($documento);
                $historico = $service_documento->getHistorico($documento);
                $minuta = array('documento_da_minuta', 'minuta_do_documento');
                //caso tenha minuta busca o documento da minuta
                $minuta['documento_da_minuta'] = $service_documento->getDocumentoDaMinuta($documento['DOCM_ID_DOCUMENTO']);

                //dados do documento principal na leitura
                $this->view->documento = $documento;
                //array de documentos/processos anexados ao documento principal na leitura
                $this->view->minuta = $minuta;
                $this->view->anexosSemMetadados = $anexosSemMetadados;
                $this->view->historico = $historico;
            }
        } else {
            //formulário não validado
            $this->view->mensagem = $this->getMensagemDiv('Documento não localizado', 'error');
        }
    }

    /**
     * Quando a página é carrega o formulário de parecer e ajax começa a carregar
     * retorna um html em duas divs (div#div-parecer_ajax, div#div-despacho_ajax)
     */
    public function formparecerdespachoajaxAction() {
        // $this->_helper->layout()->disableLayout();
        $service_documento = new Services_Sisad_Documento();
        $service_processo = new Services_Sisad_Processo();
        //busca os dados do documento dentre os parametros da requisição
        $data = $this->getRequest()->getParams(); /* Aplica Filtros - Mantem Post */
        if (!isset($data['DOCM_ID_DOCUMENTO']) || is_null($data['DOCM_ID_DOCUMENTO'])) {
            $this->view->mensagem = $this->getMensagemDiv('Dados passados via ajax são invalidos.', 'error');
        } else {
            //pega os dados do documento
            $documento = $service_documento->getDocumento($data['DOCM_ID_DOCUMENTO']);
            if ($documento != false) {
                if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {

                    $processo = $service_processo->getProcessoPorIdDocumento($documento);
                    $documento = array_merge($documento, $processo);
                }
                $service_parteVista = new Services_Sisad_ParteVista();
                $isVisivelAoUsuario = $service_parteVista->isVisivelAoUsuario($documento);
                //não visivél ao usuário
                if (!$isVisivelAoUsuario) {
                    $this->view->mensagem = $this->getMensagemDiv('O usuário não possui vistas ao documento', 'notice');
                } else {
                    //valida a exibição do formulário de parecer ou despacho
                    $documentosParaAcao = $service_documento->validaParecer($documento, array('DOCM_ID_DOCUMENTO' => $data['APARTIR_DE_DOCM_ID_DOCUMENTO']));

                    if (isset($documentosParaAcao['validado']) && $documentosParaAcao['validado'] == false) {
                        $this->view->mensagem = $this->getMensagemDiv($documentosParaAcao['motivo'], 'error');
                    } else {
                        //dados do documento principal na leitura
                        $this->view->formDespacho = new Sisad_Form_Despacho();
                        $this->view->formParecer = new Sisad_Form_Parecer();
                        $this->view->documento = $documento;
                        $this->view->documentos_alvo = $documentosParaAcao;
                    }
                }
            } else {
                //formulário não validado
                $this->view->mensagem = $this->getMensagemDiv('Documento não localizado', 'error');
            }
        }
    }

    /**
     * Realiza o parecer em um documento
     * Não possui view
     */
    public function parecerajaxAction() {
        $data = $this->getRequest()->getParams();
        if (!is_null($data)) {
            $form = new Sisad_Form_Parecer();
            if ($form->isValid($data)) {
                //documento alvo ou array de documentos
                $documento_alvo = Zend_Json::decode($data['documentos_alvo']);

                $service_documento = new Services_Sisad_Documento();
                $retorno = $service_documento->parecer($documento_alvo, $data);
                if (isset($retorno[0])) {
                    //retorna um array de mensagens de sucesso
                    $mensagem = $this->getMensagemDiv($retorno, 'success');
                } else {
                    if ($retorno['validado']) {
                        $statusMensagem = 'success';
                    } else {
                        $statusMensagem = 'error';
                    }
                    $mensagem = $this->getMensagemDiv($retorno['mensagem'], $statusMensagem);
                }
                $this->_helper->json->sendJson($mensagem);
            } else {
                //formulário não validado
                $mensagem = $this->getMensagemDiv($data, 'form', $form);
                $this->_helper->json->sendJson($mensagem);
            }
        } else {
            $mensagem = $this->getMensagemDiv('Não existe post.', 'error');
        }
    }

    /**
     * Realiza o despacho em um documento
     * Não possui view
     */
    public function despachoajaxAction() {
        $data = $this->getRequest()->getParams();
        if (!is_null($data)) {
            $form = new Sisad_Form_Despacho();
            if ($form->isValid($data)) {
                //documento alvo ou array de documentos
                $documento_alvo = Zend_Json::decode($data['documentos_alvo']);

                $service_documento = new Services_Sisad_Documento();
                $retorno = $service_documento->despacho($documento_alvo, $data);
                if (isset($retorno[0])) {
                    //retorna um array de mensagens de sucesso
                    $mensagem = $this->getMensagemDiv($retorno, 'success');
                } else {
                    if ($retorno['validado']) {
                        $statusMensagem = 'success';
                    } else {
                        $statusMensagem = 'error';
                    }
                    $mensagem = $this->getMensagemDiv($retorno['mensagem'], $statusMensagem);
                }
                $this->_helper->json->sendJson($mensagem);
            } else {
                //formulário não validado
                $mensagem = $this->getMensagemDiv($data, 'form', $form);
                $this->_helper->json->sendJson($mensagem);
            }
        } else {
            $mensagem = $this->getMensagemDiv('Não existe post.', 'error');
        }
    }

    /**
     * Retorna a string de uma div com as mensagem a serem mostradas na tela
     * 
     * @param array $array
     * @param string $tipo
     * @param Zend_Form $objForm
     */
    function getMensagemDiv($data, $tipo = 'notice', $objForm = null) {
        $conteudo = '';
        if ($tipo == 'form') {
            $conteudo = '<ul>';
            $objForm->isValid($data);
            foreach ($objForm->getMessages() as $key => $value) {
                foreach ($value as $erro) {
                    $conteudo .= '<li><strong>' . $objForm->getElement($key)->getLabel() . ' </strong>' . $erro . '</li>';
                }
            }
            $conteudo.='</ul>';
            $class = 'notice';
            $label = 'Aviso';
        } else {

            if (is_array($data)) {
                $conteudo = '<ul>';
                foreach ($data as $mensagem) {
                    $conteudo .= '<li>' . $mensagem['mensagem'] . '</li>';
                }
                $conteudo .= '</ul>';
            } else {
                $conteudo = $data;
            }
            if ($tipo == 'notice') {
                $class = $tipo;
                $label = 'Aviso';
            } elseif ($tipo == 'error') {
                $class = $tipo;
                $label = 'Erro';
            } elseif ($tipo == 'success') {
                $class = $tipo;
                $label = 'Sucesso';
            }
        }
        return '<div class=\'' . $class . '\'><strong>' . $label . ': </strong>' . $conteudo . '</div>';
    }

}
