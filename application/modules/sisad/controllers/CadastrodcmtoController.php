<?php

class Sisad_CadastrodcmtoController extends Zend_Controller_Action {
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

        $this->view->titleBrowser = 'e-Sisad';
        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
        // Informação
        $this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction() {
        $this->view->title = "Cadastramento de Documentos";
    }

    public function formAction() {
        $this->_helper->_redirector('cadastrar', 'documento', 'sisad');
    }

    public function ajaxunidadeAction() {
        $unidade = $this->_getParam('term', '');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $secao = $userNamespace->siglasecao;
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade, $secao);

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxassuntodocmAction() {
        $assunto_p = $this->_getParam('term', '');
        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        $assunto = $mapperPctt->getPCTTAjax($assunto_p);

        $fim = count($assunto);
        for ($i = 0; $i < $fim; $i++) {
            $assunto[$i] = array_change_key_case($assunto[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($assunto);
    }

    public function saveAction() {
        $this->_helper->_redirector('cadastrar', 'documento', 'sisad');
    }

    public function editAction() {

        $service_documento = new Services_Sisad_Documento();
        $ns_edit = new Zend_Session_Namespace('Ns_cadastrodcmto_edit');
        $data = array_merge($this->getRequest()->getPost()); /* Aplica Filtros - Mantem Post */

        $formParte = new Sisad_Form_Partes();
        $form = new Sisad_Form_Documento_Documento();

        if (!is_null($ns_edit->data)) {
            //escolhe o formulario de edicao de documento
            $documento = $service_documento->getDocumento($ns_edit->data['DOCM_ID_DOCUMENTO']);
            $form->edit($ns_edit->data);
            $form->isValid($ns_edit->data);
            unset($ns_edit->data);
        } else {
            $dataDocumento = Zend_Json::decode($data['documento'][0]);
            if (is_null($dataDocumento)) {
                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
            }
            $documento = $service_documento->getDocumento($dataDocumento['DOCM_ID_DOCUMENTO']);
            //escolhe o formulario de edicao de documento
            $form->edit($documento);
        }
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            $service_processo = new Services_Sisad_Processo();
            $processo = $service_processo->getProcessoPorIdDocumento($documento);
            $documento = array_merge($documento, $processo);
        }
        $service_parteVista = new Services_Sisad_ParteVista();
        $isVisivelAoUsuario = $service_parteVista->isVisivelAoUsuario($documento);
        if ($isVisivelAoUsuario) {
            //solução paleativa até ser refeito o esquema de partes para uma tela so
            //utilizando session sem ser destruida podem ocorrer problemas com integridade
            $Ns_Partes_documentos = new Zend_Session_Namespace('Ns_Partes_documentos');
            $Ns_Partes_documentos->data_post_caixa = array('documento' => array($documento));
            $this->view->title = 'Editar Documento - ' . $documento['MASC_NR_DOCUMENTO'];
            $this->view->documento = $documento;
            $this->view->form = $form;
            $this->view->formParte = $formParte;
        } else {
            $this->_helper->flashMessenger(array('message' => 'O usuário não possui vistas ao documento.', 'status' => 'notice'));
            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
        }
    }

    public function saveeditAction() {
        $form = new Sisad_Form_Documento_Documento();
        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
            //escolhe o formulario de edição
            $form->edit($data, 'editar');
            if ($form->isValid($data)) {
                $service_documento = new Services_Sisad_Documento();

                $mensagem = $service_documento->edit($data);
                $this->_helper->flashMessenger(array('message' => $mensagem['mensagem'], 'status' => ($mensagem['validado'] ? 'success' : 'error')));
            } else {
                $this->_helper->flashMessenger(array('message' => 'Preencha corretamente os campos!', 'status' => 'notice'));
            }
            $ns_edit = new Zend_Session_Namespace('Ns_cadastrodcmto_edit');
            $ns_edit->data = $data;
            $this->_helper->_redirector('edit', 'cadastrodcmto', 'sisad');
        } else {
            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
        }
        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
    }

    public function ativaAction() {

        $Ns_Cadastrodcmto_ativa = new Zend_Session_Namespace('Ns_Cadastrodcmto_ativa');
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $aNamespace = new Zend_Session_Namespace('userNs');

        $data_post = $Ns_Cadastrodcmto_ativa->data_post_documento;

        $rowDocmDocumento = $tabelaSadTbDocmDocumento->find($data_post['DOCM_ID_DOCUMENTO'])->current();

        $docm_cd_lotacao_geradora_array = explode(' - ', $data_post['DOCM_CD_LOTACAO_GERADORA']);
        $data['DOCM_SG_SECAO_GERADORA'] = $docm_cd_lotacao_geradora_array[3];
        $data['DOCM_CD_LOTACAO_GERADORA'] = $docm_cd_lotacao_geradora_array[2];

        $docm_cd_lotacao_redatora_array = explode(' - ', $data_post['DOCM_CD_LOTACAO_REDATORA']);
        $data['DOCM_SG_SECAO_REDATORA'] = $docm_cd_lotacao_redatora_array[3];
        $data['DOCM_CD_LOTACAO_REDATORA'] = $docm_cd_lotacao_redatora_array[2];
        $data["DOCM_DH_CADASTRO"] = new Zend_Db_Expr("SYSDATE");
        $data["DOCM_CD_MATRICULA_CADASTRO"] = $aNamespace->matricula;
        $data["DOCM_ID_TIPO_DOC"] = $data_post["DOCM_ID_TIPO_DOC"];
        $data["DOCM_NR_DCMTO_USUARIO"] = $data_post["DOCM_NR_DCMTO_USUARIO"];
        $data["DOCM_ID_PCTT"] = $data_post["DOCM_ID_PCTT"];
        $data["DOCM_ID_TIPO_SITUACAO_DOC"] = $data_post["DOCM_ID_TIPO_SITUACAO_DOC"];
        $data["DOCM_ID_CONFIDENCIALIDADE"] = $data_post["DOCM_ID_CONFIDENCIALIDADE"];

        $data["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data_post['DOCM_ID_TIPO_DOC']);
        $data["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_CD_LOTACAO_GERADORA'], $data_post['DOCM_ID_TIPO_DOC'], $data["DOCM_NR_SEQUENCIAL_DOC"]);
        $data["DOCM_DS_ASSUNTO_DOC"] = new Zend_Db_Expr(" CAST( '" . $data_post['DOCM_DS_ASSUNTO_DOC'] . "' AS VARCHAR(4000)) ");

        // prepara array cadastro de partes/vistas
        $dataPartePessoa = array();
        $dataParteLotacao = array();
        $dataPartePessExterna = array();
        $dataPartePessJur = array();

        if (count($data_post['partes_pessoa_trf']) > 0) {
            $dataPartePessoa = $data_post['partes_pessoa_trf'];
        }
        if (count($data_post['partes_unidade']) > 0) {
            $dataParteLotacao = $data_post['partes_unidade'];
        }
        if (count($data_post['partes_pess_ext']) > 0) {
            $dataPartePessExterna = $data_post['partes_pess_ext'];
        }
        if (count($data_post['partes_pess_jur']) > 0) {
            $dataPartePessJur = $data_post['partes_pess_jur'];
        }

        $data['DOCM_IC_ATIVO'] = 'S';
        $nroDocumento = $data['DOCM_NR_DOCUMENTO'];

        try {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();

            $rowDocmDocumento->setFromArray($data);
            $docmAtivado = $rowDocmDocumento->save();

            // Cadastra partes/vistas no documento 
            $rowDocmDocumento = $rowDocmDocumento->toArray();
            $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
            if (count($dataPartePessoa) > 0 || count($dataParteLotacao) > 0 || count($dataPartePessExterna) > 0 || count($dataPartePessJur) > 0) {
                if (!empty($rowDocmDocumento)) {
                    $cadastroPartes = $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $rowDocmDocumento, array(), false);
                }
            }

            if ($docmAtivado) {

                if (isset($data_post['POSSUI_MOVIMENTACAO']) && $data_post['POSSUI_MOVIMENTACAO'] == 'S') {
                    //envia para caixa de entrada pessoal o documento
                    $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();

                    $ultimaMovi = $SadTbMoviMovimentacao->getUltimaMovimentacaoDcmto($data_post['DOCM_ID_DOCUMENTO']);

                    $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $data['DOCM_CD_MATRICULA_CADASTRO'];
                    $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $ultimaMovi[0]['MODE_SG_SECAO_UNID_DESTINO'];
                    $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $ultimaMovi[0]['MODE_CD_SECAO_UNID_DESTINO'];

                    $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $aNamespace->siglasecao; //$dados_input['MODE_SG_SECAO_UNID_DESTINO'];
                    $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $aNamespace->codlotacao;
                    $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';

                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1027; /* REATIVAÇÃO DE DOCUMENTO */
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $aNamespace->matricula;
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = 'Documento reativado';

                    $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $aNamespace->matricula;

                    $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;
                    $encamCxPessoal_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($data_post['DOCM_ID_DOCUMENTO'], $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestPessoa, null, false);
                    if ($encamCxPessoal_retorno == false) {
                        $this->_helper->flashMessenger(array('message' => "Não foi possível encaminhar o documento nº $nroDocumento para a Caixa Pessoal", 'status' => 'error'));
                        //$msg_to_user = "Não foi possível encaminhar o documento nº $nroDocumento para a Caixa Pessoal";
                        //$status = 'error';
                    } else if ($encamCxPessoal_retorno == true) {
                        $caixa_encaminhada = 'entrada';
                    }
                } else {
                    $caixa_encaminhada = 'rascunhos';
                }


                $msg_to_user = " Documento reativado e atualizado com sucesso. Número do documento: " . $nroDocumento;
                $status = 'success';
                $db->commit();
            } else if (!$docmAtivado) {
                $msg_to_user = " Não foi possível reativar o documento: " . $nroDocumento;
                $status = 'error';
                $caixa_encaminhada = 'rascunhos';
            }

            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => $status));
            $this->_helper->_redirector($caixa_encaminhada, 'caixapessoal', 'sisad');
        } catch (Exception $e) {
            echo $e->getMessage();
            $db->rollback();
        }
    }

}
