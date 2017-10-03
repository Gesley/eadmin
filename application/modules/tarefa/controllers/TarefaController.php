<?php

class Tarefa_TarefaController extends Zend_Controller_Action
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
        $this->view->titleBrowser = 'e-OS - Sistema de Gerenciamento de Ordem de Serviço';
        $this->facade = App_Factory_FactoryFacade::createInstance('Tarefa_Facade_Tarefa');
    }

    public function indexAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $arrayPerfisPessoa = $ocsTbPupePerfilUnidPessoa->getTodosPerfilPessoa($userNs->matricula);
        foreach ($arrayPerfisPessoa as $pf) {
            $arrayPerfis[] = $pf["PERF_DS_PERFIL"];
        }
        /** Campos acessados pelo perfil "GESTÃO DE DEMANDAS DE TI" */
        if (in_array("GESTÃO DE DEMANDAS DE TI", $arrayPerfis)) {
            $this->view->perfil = 'gestao';
        } else {
        /** Campos acessados pelo perfil "DESENVOLVIMENTO E SUSTENTAÇÃO" */
            $this->view->perfil = 'desenv';
        }
        $orderColumn = $this->_getParam('ordem', 'TARE_ID_TAREFA');
        $getUrlOrder = $this->_getParam('direcao', 'ASC');
        $getSolicitacao = $this->_getParam('idDocumento', 0);
        $orderDirection = ($getUrlOrder == 'DESC') ? ('ASC') : ('DESC');
        $order = $orderColumn . ' ' . $getUrlOrder;
        $rows = $this->facade->listAll($getSolicitacao, $order);
        $paginator = Zend_Paginator::factory($rows);
        $this->view->assign(array(
            'title'       => 'Tarefa',
            'idDocumento' => $getSolicitacao,
            'direcao'     => $orderDirection,
            'data'        => $paginator
        ));
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }
    
    public function saveAction()
    {
        $form = new Tarefa_Form_Tarefa();
        $formNs = new Zend_Session_Namespace('formNs');
        $userNs = new Zend_Session_Namespace('userNs');
        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $this->view->assign(array(
            'title' => 'Incluir Tarefa',
            'form'  => $form
        ));
        $id = $this->_getParam('id', 0);
        $idDocmento = $this->_getParam('idDocumento', 0);
        $idMovimentacao = $this->_getParam('idMovimentacao', 0);
        $form->setAttrib('id', 'tarefa_'.$idDocmento);
        $form->setAttrib('name', 'save');
        $form->getElement('MOFA_ID_MOVIMENTACAO')->setValue($idMovimentacao);
        $arrayPerfisPessoa = $ocsTbPupePerfilUnidPessoa->getTodosPerfilPessoa($userNs->matricula);
        foreach ($arrayPerfisPessoa as $pf) {
            $arrayPerfis[] = $pf["PERF_DS_PERFIL"];
        }
        $form->ID_SAVE_TAREFA->setValue($id);
        /** Campos acessados pelo perfil "GESTÃO DE DEMANDAS DE TI" */
        if (in_array("GESTÃO DE DEMANDAS DE TI", $arrayPerfis)) {
            $form->PERFIL_USER->setValue('gestao');
        } else {
        /** Campos acessados pelo perfil "DESENVOLVIMENTO E SUSTENTAÇÃO" */
            $form->PERFIL_USER->setValue('desenv');
        }
        /** Se a requisição for post */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $postEnvios = $form->populate($data)->getValues();
            if ($formNs->envio != md5(implode('-', $postEnvios))) {
                $formNs->envio = md5(implode('-', $postEnvios));
                if ($form->isValidPartial($data)) {
                    $data = array_merge($data, $postEnvios); /* Aplica Filtros - Mantem Post */
                    /**
                     * ADICIONA OS ANEXOS
                     */
                    $data['ID_DOCUMENTO'] = $idDocmento;

                    $arrayAnexos = array(
                        $data["ANEXOS_TAREFA"] != "" ? "ANEXOS_TAREFA" : "",
                        $data["ANEXOS_NEGOCIACAO_FABRICA"] != "" ? "ANEXOS_NEGOCIACAO_FABRICA" : "",
                        $data["ANEXOS_NEGOCIACAO_GESTAO"] != "" ? "ANEXOS_NEGOCIACAO_GESTAO" : ""
                    );
                    foreach ($arrayAnexos as $a) {
                        if ($a != "") {
                            $anexos = $a;
                        }
                    }
                    $nrDocsRed = null;
                    if (!is_null($data[$anexos])) {
                        try {
                            $upload = new App_Multiupload_NewMultiUpload();
                            $nrDocsRed = $upload->incluirarquivos($form->$anexos);
                        } catch (Exception $exc) {
                            return $this->_helper->json->sendJson(array('message' => "Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua tarefa sem anexo.", 'status' => 'error'));
                        }
                    }
                    $data["NR_DOCS_RED"] = $nrDocsRed;
                    $data['TASO_NR_DCMTO_ANEXO'] = $nrDocsRed['incluidos'][0]['ID_DOCUMENTO'];
                    /**
                     * FIM DO ADICIONAR ANEXOS
                     */
                    if ($data['TARE_ID_TAREFA'] != 0) {
                        $editResult = $this->facade->editar($data);
                        if (($editResult["edit"] == 1) || ($editResult["gravaRed"] != null)) {
                            return $this->_helper->json->sendJson(array('message' => "Tarefa editada com sucesso!", 'status' => 'success'));
                        } else {
                            return $this->_helper->json->sendJson(array('message' => "Não foi possível editar a tarefa!", 'status' => 'error'));
                        }
                    } else {
                        $data['ID_DOCUMENTO'] = $idDocmento;
                        $addResult = $this->facade->adicionar($data);
                        if (($addResult["add"] == true) || ($addResult["gravaRed"] != null)) {
                            return $this->_helper->json->sendJson(array('message' => "Tarefa cadastrada com sucesso!", 'status' => 'success'));
                        } else {
                            return $this->_helper->json->sendJson(array('message' => "Não foi possível cadastrar a tarefa!", 'status' => 'error'));
                        }
                    }
                } else {
                    return $this->_helper->json->sendJson($form->getMessages());
                }
            }
        } else {
            if ($id != 'Incluir') {
                $data = $this->facade->getById($id);
                $this->view->date = $data["TARE_DH_CADASTRO"];
                $form->populate($data);
            } else {
                $form->removeElement("TASO_CD_MATR_ATEND_TAREFA");
                $form->removeElement("TASO_IC_ACEITE_ATENDENTE");
                $form->removeElement("TASO_DS_JUSTIF_ATENDENTE");
                $form->removeElement("TASO_DS_JUSTIF_SOLICITANTE");
                $form->removeElement("TASO_IC_ACEITE_SOLICITANTE");
                $form->removeElement("TASO_IC_SITUACAO_NEGOCIACAO");
                $form->removeElement("ANEXOS_NEGOCIACAO_FABRICA");
                $form->removeElement("ANEXOS_NEGOCIACAO_GESTAO");
            }
        }
    }
    
    public function excluirAction()
    {
        if ($this->facade->excluir($this->_getParam('id'))) {
            return $this->_helper->json->sendJson(array('message' => 'Tarefa excluída com sucesso!', 'status' => 'success'));
        }
    }
    
    public function visualizarAction()
    {
        $form = new Tarefa_Form_Tarefa();
        foreach ($form->getElements() as $element) {
            $element->setAttrib('disabled', true);
            $element->setAttrib('readonly',true);
        }
        $this->view->assign(array(
            'title' => 'Visualizar Tarefa',
            'form'  => $form
        ));
        $id = $this->_getParam('id', 0);
        $data = $this->facade->getById($id);
        $this->view->date = $data["TARE_DH_CADASTRO"];
        $form->populate($data);
    }
    
    public function listanexosAction()
    {
        $idTarefa = $this->_getParam('idTarefa', 0);
        $list = $this->_getParam('list', 0);
        $anexoTarefa = new Tarefa_Model_DataMapper_TarefaSolicitacao();
        foreach ($anexoTarefa->getAnexoTarefaSolicitacao($idTarefa, $list) as $k=>$at) {
            $extensao = explode('.', $at->ANEX_NM_ANEXO);
            $objAnexo->$k->id =  $at->ANEX_ID_DOCUMENTO;
            $objAnexo->$k->documento = $at->ANEX_NR_DOCUMENTO_INTERNO;
            $objAnexo->$k->extensao =  $extensao[1];
        }
        $this->view->arrayAnexoTarefa = $objAnexo;
    }
    
}