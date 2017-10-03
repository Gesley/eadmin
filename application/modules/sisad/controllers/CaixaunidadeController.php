<?php

class Sisad_CaixaunidadeController extends Zend_Controller_Action {
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
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();

        // Ajuda
        $this->view->msgAjuda = AJUDA_AJUDA;
        // Informação
        $this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $rows = $dados->getCaixaUnidadeRascunhos($userNs->$codlotacao, $userNs->$siglasecao, $order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(10);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $siglalotacao = $userNs->siglalotacao;
        $this->view->title = "Caixa - $siglalotacao";
    }

    public function rascunhosAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $userNs = new Zend_Session_Namespace('userNs');
        $siglalotacao = $userNs->siglalotacao;

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $rows = $dados->getCaixaUnidadeRascunhos($codlotacao, $siglasecao, $order);

        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_TimeInterval();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {

            if (is_null($rows[$i]["DOCM_NR_DOCUMENTO_RED"])) {
                $rows[$i]['MSG_ARQUIVO'] = "Adicionar o arquivo";
                $rows[$i]['CLASS_ARQUIVO'] = "alertaButton";
            } else {
                $rows[$i]['MSG_ARQUIVO'] = "Abrir Documento";
                $rows[$i]['CLASS_ARQUIVO'] = "abrirAnexo";
            }
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['DOCM_DH_CADASTRO_CHAR']);

            $rows[$i]['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Caixa Rascunhos - $siglalotacao";
    }

    public function processosdaunidadeAction() {
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade ();
        $this->view->title = "Processos da " . $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();
        $TimeInterval = new App_TimeInterval ();

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidadesisad_processos = new Zend_Session_Namespace('Ns_Caixaunidadesisad_processos');
        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $matricula = $userNs->matricula;

        $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 15, 'page' => 15);

        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);

        $form = new Sisad_Form_CaixaDocumentos();
        $form->removeElement('DOCM_ID_TIPO_DOC');
        $form->DOCM_CD_LOTACAO_GERADORA->setLabel('Localização');
        $form_valores_padrao = $form->getValues();


        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();

        //Categoria
        $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_CD_LOTACAO_CATEGORIA = $codlotacao AND CATE_SG_SECAO_CATEGORIA = '$siglasecao'");
        $Categorias = $Categorias->toArray();

        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        foreach ($Categorias as $Categorias_p):
            $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
            $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NM_CATEGORIA"]));
            $cont++;
        endforeach;
        $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
        $this->view->categorias = $Categorias;

        //Pessoas da unidade
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $pessoas = $OcsTbPmatMatricula->getPessoa($siglasecao, $codlotacao);
        $mode_cd_matr_recebedor = $form->getElement('PAPD_CD_MATRICULA_INTERESSADO');
        $mode_cd_matr_recebedor->addMultiOptions(array('' => 'Selecione uma pessoa da unidade'));
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
        endforeach;


        $this->view->title = "Processos da " . $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();

        if ($this->_getParam('nova') === '1') {
            unset($Ns_Caixaunidadesisad_processos->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }


        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $filtro = $this->tratafiltrodascaixas($data_pesq);
                $Ns_Caixaunidadesisad_processos->data_pesq = $this->getRequest()->getPost();
                $Ns_Caixaunidadesisad_processos->filtro = $filtro;
                $form->populate($Ns_Caixaunidadesisad_processos->data_pesq);
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                return;
            }
        }//Post e validate

        $data_pesq = $Ns_Caixaunidadesisad_processos->data_pesq;
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $page = $varSessoes->getPage();
        $itemCountPerPage = $varSessoes->getItemsperpage();
        $order_column = $varSessoes->getOrdem();
        $order_direction = $varSessoes->getDirecao();
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');


        if (!is_null($data_pesq)) {
            $rows = $mapperDocumento->getProcessosUnidade($siglasecao, $codlotacao, $matricula, $order, $Ns_Caixaunidadesisad_processos->filtro);
            $this->view->ultima_pesq = true;
        } else {
            $rows = $mapperDocumento->getProcessosUnidade($siglasecao, $codlotacao, $matricula, $order, null);
            $this->view->ultima_pesq = false;
        }

        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {

            if (is_null($rows [$i] ["DOCM_NR_DOCUMENTO_RED"])) {
                $rows [$i] ['MSG_ARQUIVO'] = "Adicionar o arquivo";
                $rows [$i] ['CLASS_ARQUIVO'] = "alertaButton";
            } else {
                $rows [$i] ['MSG_ARQUIVO'] = "Abrir Documento";
                $rows [$i] ['CLASS_ARQUIVO'] = "abrirAnexo";
            }
            $rows [$i] ['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows [$i] ['MOFA_DH_FASE']);

            $rows [$i] ['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows [$i] ['DADOS_INPUT'] = Zend_Json::encode($rows [$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }

        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itemCountPerPage);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        $this->view->form = $form;
    }

    public function documentosdaunidadeAction() {
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade ();
        $this->view->title = "Documentos da " . $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();
        $TimeInterval = new App_TimeInterval ();

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidadesisad_documentos = new Zend_Session_Namespace('Ns_Caixaunidadesisad_documentos');
        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $matricula = $userNs->matricula;

        $variaveisSessaoPadrao = array('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 15, 'page' => 1);

        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);

        $form = new Sisad_Form_CaixaDocumentos();
        // $form->removeElement('PAPD_CD_MATRICULA_INTERESSADO');
        $form->DOCM_CD_LOTACAO_GERADORA->setLabel('Localização');
        $form_valores_padrao = $form->getValues();

        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();

        //Categoria
        $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_CD_LOTACAO_CATEGORIA = $codlotacao AND CATE_SG_SECAO_CATEGORIA = '$siglasecao'");
        $Categorias = $Categorias->toArray();

        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        foreach ($Categorias as $Categorias_p):
            $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
            $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NM_CATEGORIA"]));
            $cont++;
        endforeach;
        $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
        $this->view->categorias = $Categorias;

        //Pessoas da unidade
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $pessoas = $OcsTbPmatMatricula->getPessoa($siglasecao, $codlotacao);
        $mode_cd_matr_recebedor = $form->getElement('PAPD_CD_MATRICULA_INTERESSADO');
        $mode_cd_matr_recebedor->addMultiOptions(array('' => 'Selecione uma pessoa da unidade'));
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
        endforeach;

        $this->view->title = "Documentos da " . $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();

        if ($this->_getParam('nova') === '1') {
            unset($Ns_Caixaunidadesisad_documentos->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }


        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $filtro = $this->tratafiltrodascaixas($data_pesq);
                $Ns_Caixaunidadesisad_documentos->data_pesq = $this->getRequest()->getPost();
                $Ns_Caixaunidadesisad_documentos->filtro = $filtro;
                $form->populate($Ns_Caixaunidadesisad_documentos->data_pesq);
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                return;
            }
        }//Post e validate


        $data_pesq = $Ns_Caixaunidadesisad_documentos->data_pesq;
        $mapperDocumento = new Sisad_Model_DataMapper_Documento ();
        $page = $varSessoes->getPage();
        $itemCountPerPage = $varSessoes->getItemsperpage();
        $order_column = $varSessoes->getOrdem();
        $order_direction = $varSessoes->getDirecao();
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');


        if (!is_null($data_pesq)) {
            $rows = $mapperDocumento->getDocumentosUnidade($siglasecao, $codlotacao, $matricula, $order, $Ns_Caixaunidadesisad_documentos->filtro);
            $this->view->ultima_pesq = true;
        } else {
            $rows = $mapperDocumento->getDocumentosUnidade($siglasecao, $codlotacao, $matricula, $order, null);
            $this->view->ultima_pesq = false;
        }

        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {

            if (is_null($rows [$i] ["DOCM_NR_DOCUMENTO_RED"])) {
                $rows [$i] ['MSG_ARQUIVO'] = "Adicionar o arquivo";
                $rows [$i] ['CLASS_ARQUIVO'] = "alertaButton";
            } else {
                $rows [$i] ['MSG_ARQUIVO'] = "Abrir Documento";
                $rows [$i] ['CLASS_ARQUIVO'] = "abrirAnexo";
            }
            $rows [$i] ['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows [$i] ['MOFA_DH_FASE']);

            $rows [$i] ['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows [$i] ['DADOS_INPUT'] = Zend_Json::encode($rows [$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }

        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itemCountPerPage);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->form = $form;
    }

    public function solicitacoesdaunidadeAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $tipoSolicitacao = $this->_getParam('tipo');

        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $secaopermissao = new App_Controller_Plugin_AcessoCaixaUnidade ();
        $sgsecao = $secaopermissao->getSgsecaoCaixaUnidade();
        $Siglalotacao = $secaopermissao->getSiglaLotacaoCaixaUnidade();
        $codlotacao = $secaopermissao->getCdlotacaoCaixaUnidade();

        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array('direcao' => 'ASC', 'ordem' => 'TEMPO_TOTAL', 'itemsperpage' => 15, 'page' => 1);
        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);
        $page = $varSessoes->getPage();

        /* Ordenação das paginas */
        $order_column = $varSessoes->getOrdem();
        $order_direction = $varSessoes->getDirecao();
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        if ($tipoSolicitacao === 'avaliar' || $tipoSolicitacao == null) {
            $this->view->title = "Solicitações para Avaliação - " . $Siglalotacao;
            $rows = $dados->getSolicitacoesDaUnidade($sgsecao, $codlotacao, 'avaliar', $order);
            $this->view->tipo = 'avaliar';
        } else if ($tipoSolicitacao === 'atendimento') {
            $this->view->title = "Solicitações em Atendimento - " . $Siglalotacao;
            $rows = $dados->getSolicitacoesDaUnidade($sgsecao, $codlotacao, 'atendimento', $order);
            $this->view->tipo = 'atendimento';
        } else if ($tipoSolicitacao === 'informacao') {
            $this->view->title = "Solicitações com Pedido de Informação - " . $Siglalotacao;
            $rows = $dados->getSolicitacoesDaUnidade($sgsecao, $codlotacao, 'informacao', $order);
            $this->view->tipo = 'informacao';
        } else if ($tipoSolicitacao === 'informacaodsv') {
            $this->view->title = "Solicitações com Pedido de Informação do Desenvolvedor - " . $Siglalotacao;
            $rows = $dados->getSolicitacoesDaUnidade($sgsecao, $codlotacao, 'informacaodsv', $order);
            $this->view->tipo = 'informacaodsv';
        } else if ($tipoSolicitacao === 'avaliadas') {
            $this->view->title = "Solicitações Avaliadas - " . $Siglalotacao;
            $rows = $dados->getSolicitacoesDaUnidade($sgsecao, $codlotacao, 'avaliadas', $order);
            $this->view->tipo = 'avaliadas';
        }

        $TimeInterval = new App_TimeInterval();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['MOFA_DH_FASE']);
            unset($rows[$i]['DATA_ATUAL']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($varSessoes->getItemsperpage());
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function entradaAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidadesisad_index = new Zend_Session_Namespace('Ns_Caixaunidadesisad_index');
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();

        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
        $siglalotacao = $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();
        
        $sessionCaixaUnidade = new Zend_Session_Namespace('Ns_dados_caixa_unidade');
        
        $page = $this->_getParam('page');
        if($page == null){
            if(isset($sessionCaixaUnidade->page)){
                $page = $sessionCaixaUnidade->page;
            }else{
                $page = 1;
            }
        }else{
            $page = Zend_Filter::filterStatic($page, 'int');
        }
        $sessionCaixaUnidade->page = $page;
        
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem');
        if($order_column == null){
            if(isset($sessionCaixaUnidade->order_column)){
                $order_column = $sessionCaixaUnidade->order_column;
            }else{
                $order_column = 'MOVI_DH_ENCAMINHAMENTO';
            }
        }
        $sessionCaixaUnidade->order_column = $order_column;
        
        $order_direction = $this->_getParam('direcao');
        if($order_direction == null){
            if(isset($sessionCaixaUnidade->order_direction)){
                $order_direction = $sessionCaixaUnidade->order_direction;
            }else{
                $order_direction = 'DESC';
            }
        }
        $sessionCaixaUnidade->order_direction = $order_direction;
        
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

        $form = new Sisad_Form_CaixaDocumentos();
        $form_valores_padrao = $form->getValues();


        //Categoria
        $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_CD_LOTACAO_CATEGORIA = $codlotacao");
        $Categorias = $Categorias->toArray();
        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        foreach ($Categorias as $Categorias_p):
            $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
            $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NM_CATEGORIA"]));
            $cont++;
        endforeach;
        $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
        $this->view->categorias = $Categorias;

        //Pessoas da unidade
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $pessoas = $OcsTbPmatMatricula->getPessoa($siglasecao, $codlotacao);
        $mode_cd_matr_recebedor = $form->getElement('PAPD_CD_MATRICULA_INTERESSADO');
        $mode_cd_matr_recebedor->addMultiOptions(array('' => 'Selecione uma pessoa da unidade'));
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
        endforeach;

        $uri = $_SERVER['REQUEST_URI'];
        $end = explode('/sisad/', $uri);
        $end = explode('/', $end[1]);


        if ($this->_getParam('nova') === '1') {
            unset($Ns_Caixaunidadesisad_index->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $this->view->title = "Caixa Entrada - $siglalotacao";
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $filtro = $this->tratafiltrodascaixas($data_pesq);
                $Ns_Caixaunidadesisad_index->data_pesq = $this->getRequest()->getPost();
                $Ns_Caixaunidadesisad_index->filtro = $filtro;
                $form->populate($Ns_Caixaunidadesisad_index->data_pesq);
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "Caixa Entrada - $siglalotacao";
                return;
            }
        }//Post e validate

        $data_pesq = $Ns_Caixaunidadesisad_index->data_pesq;

        $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();

        if (!is_null($data_pesq)) {
            $rows = $dados->getCaixaUnidadeRecebidos($codlotacao, $siglasecao, $order, $Ns_Caixaunidadesisad_index->filtro);
            $this->view->ultima_pesq = true;
        } else {
            $rows = $dados->getCaixaUnidadeRecebidos($codlotacao, $siglasecao, $order);
            $this->view->ultima_pesq = false;
        }

        $cateNs = new Zend_Session_Namespace('cateNs');
        $cateNs->tipo = 'unidade';
        $cateNs->cdLotacao = $codlotacao;
        $cateNs->sgSecao = $siglasecao;


        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_TimeInterval();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {

            $enderecado = $SadTbPrdcProtDocProcesso->getEnderecamento($rows[$i]["DOCM_ID_DOCUMENTO"]);
            $protocolo = $SadTbPrdcProtDocProcesso->getProtocolado($rows[$i]["DOCM_ID_DOCUMENTO"]);
            if ($enderecado) {
                $rows[$i]['MSG_ENDERECADO'] = "Endereçado Para Postagem";
                $rows[$i]['ENDERECADO'] = "enderecado";
            }
            if ($protocolo) {
                $nrs = null;
                $qtdProt = count($protocolo);
                $cont = 1;
                foreach ($protocolo as $value) {
                    if ($cont == $qtdProt) {
                        $nrs = $nrs . $value["PRDC_ID_PROTOCOLO"];
                    } else {
                        $nrs = $nrs . $value["PRDC_ID_PROTOCOLO"] . ' , ';
                    }
                    $cont++;
                }
                $rows[$i]['MSG_POSTAGEM'] = "Protocolo Aguardando Documento Físico, Nr. Protocolo: $nrs";
                $rows[$i]['PARA_POSTAGEM'] = "protocolo";
            }

            if (is_null($rows[$i]["MODE_DH_RECEBIMENTO"])) {
                $rows[$i]['MSG_LIDO'] = "Documento não lido";
                $rows[$i]['CLASS_LIDO'] = "naolido";
                $rows[$i]['CLASS_LIDO_TR'] = "naolidoTr";
            } else {
                $rows[$i]['MSG_LIDO'] = "Documento lido";
                $rows[$i]['CLASS_LIDO'] = "lido";
                $rows[$i]['CLASS_LIDO_TR'] = "lidoTr";
            }

            if (is_null($rows[$i]["DOCM_NR_DOCUMENTO_RED"])) {
                $rows[$i]['MSG_ARQUIVO'] = "Adicionar o arquivo";
                $rows[$i]['CLASS_ARQUIVO'] = "alertaButton";
            } else {
                $rows[$i]['MSG_ARQUIVO'] = "Abrir Documento";
                $rows[$i]['CLASS_ARQUIVO'] = "abrirAnexo";
            }
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOVI_DH_ENCAMINHAMENTO_CHAR']);

            $rows[$i]['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows[$i]['controller'] = $this->getRequest()->getControllerName();
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->title = "Caixa Entrada - $siglalotacao";
        $this->view->form = $form;
    }

    public function meucarrinhoAction() {        
        $userNs = new Zend_Session_Namespace('userNs');
        $userNs->meucarrinho;

        $documentos_do_carrinho = array();



        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //Zend_Debug::dump($data);
            
            
            $flashmessage = array('label' => '', 'status' => '', 'message' => '');

            /*
             * Adicionando ao carrinho o controller e a action que faz a requisição
             */
            $documentos_do_carrinho = $userNs->meucarrinho;
            if ($data["controller"] &&
                    $data["action"] &&
                    !$documentos_do_carrinho["controller"] &&
                    !$documentos_do_carrinho["action"]) {

                $documentos_do_carrinho["controller"] = $data["controller"];
                $documentos_do_carrinho["action"] = $data["action"];
                $userNs->meucarrinho = $documentos_do_carrinho;
            }
            
            /*
             * Adicionar no carrinho de documentos
             */
            if ($data[adicionar] == 'adicionar') {
                $documentos_do_carrinho = $userNs->meucarrinho;
                $countc = count($documentos_do_carrinho['documento']);
                if (!$countc === 0) {
                    $countc++;
                }

                foreach ($data['documento'] as $documento) {
                    $documento = Zend_Json::decode($documento);
                    $item_no_carrinho = false;

                    foreach ($documentos_do_carrinho['documento'] as $itemCarrinho) {
                        if ($itemCarrinho["DOCM_ID_DOCUMENTO"] == $documento["DOCM_ID_DOCUMENTO"]) {
                            $item_no_carrinho = true;
                        }
                    }

                    //Verificacao da permissao de vistas para documentos nao publicos
                    if (in_array($documento['DOCM_ID_CONFIDENCIALIDADE'], array("1", "3", "4"))) {
                        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                        $verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas($documento);

                        if (!$verifica) {
                            $item_no_carrinho = true;
                            $flashmessage['labelErro'] = 'Atenção: os seguintes documentos não foram adicionados ao carrinho, pois são confidenciais e é necessário ter permissão de vistas para executar ações sobre os mesmos. <br/>';
                            $flashmessage['statusErro'] = 'notice';
                            $flashmessage['messageErro'] .= $documento[DTPD_NO_TIPO] . " nº " . $documento[DOCM_NR_DOCUMENTO] . " <br/>";
                        }
                    }
                    //Verificacao de permissao da Corregedoria para os documentos da corregedoria
                    if ($documento['DOCM_ID_CONFIDENCIALIDADE'] == "5") {
                        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                        $usuarioCorregedoria = $OcsTbPupePerfilUnidPessoa->verificaPermissaoCorregedoria();

                        if (empty($usuarioCorregedoria)) {
                            $item_no_carrinho = true;
                            $flashmessage['labelErro'] = 'Atenção: os seguintes documentos não foram adicionados ao carrinho, pois são confidenciais e é necessário ter permissão de vistas para executar ações sobre os mesmos. <br/>';
                            $flashmessage['statusErro'] = 'notice';
                            $flashmessage['messageErro'] .= $documento[DTPD_NO_TIPO] . " nº " . $documento[DOCM_NR_DOCUMENTO] . " - Documento da Corregedoria <br/>";
                        }
                    }


                    if (!$item_no_carrinho) {
                        $documentos_do_carrinho['documento'][$countc] = $documento;
                        $countc++;
                    }
                }
                $userNs->meucarrinho = $documentos_do_carrinho;
                $flashmessage['label'] = 'Sucesso';
                $flashmessage['status'] = 'success';
                $flashmessage['message'] = 'Documento(s) Adicionados!';
            }

            /*
             * Remover do carrinho de documentos
             */
            if ($data[remover] == 'remover') {
                $documentos_do_carrinho = $userNs->meucarrinho;
                if ($documentos_do_carrinho) {
                    foreach ($data['documento'] as $documento) {
                        $documento = Zend_Json::decode($documento);
                        $i = 0;
                        foreach ($documentos_do_carrinho['documento'] as $itemCarrinho) {
                            if ($itemCarrinho["DOCM_ID_DOCUMENTO"] == $documento["DOCM_ID_DOCUMENTO"]) {
                                array_splice($documentos_do_carrinho['documento'], $i, 1);
                            }
                            $i++;
                        }
                    }
                    $userNs->meucarrinho = $documentos_do_carrinho;
                    $flashmessage['label'] = 'Sucesso';
                    $flashmessage['status'] = 'success';
                    $flashmessage['message'] = 'Documento(s) Removidos!';
                }
            }

            /*
             * Remover um único item do carrinho
             */
            if ($data[remover_item] == 'remover_item') {
                $documentos_do_carrinho = $userNs->meucarrinho;
                if ($documentos_do_carrinho) {
                    $i = 0;
                    foreach ($documentos_do_carrinho['documento'] as $itemCarrinho) {
                        if ($itemCarrinho["DOCM_ID_DOCUMENTO"] == $data['item']) {
                            array_splice($documentos_do_carrinho['documento'], $i, 1);
                        }
                        $i++;
                    }
                }
                $userNs->meucarrinho = $documentos_do_carrinho;
                $flashmessage['label'] = 'Sucesso';
                $flashmessage['status'] = 'success';
                $flashmessage['message'] = 'Documento Removido!';
            }
        }

        /*
         * Limpar o carrinho de documentos e processos que vão sofrer uma ação.
         */
        if ($data[limpar_para_acao] == 'limpar_para_acao') {
            $userNs->meucarrinho = array();
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }

        /*
         * Limpar o carrinho de documentos e processos 
         */
        if ($data[limpar] == 'limpar') {
            $userNs->meucarrinho = array();
            $flashmessage['label'] = 'Sucesso';
            $flashmessage['status'] = 'success';
            $flashmessage['message'] = 'Carrinho Limpo!';
        }
        
        

        /*
         * Visualizar o carrinho de documentos
         */
        //if($data[visualizar] == 'visualizar'){
        $documentos_do_carrinho = $userNs->meucarrinho;
        if ($documentos_do_carrinho['documento']) {
            $paginator = Zend_Paginator::factory($documentos_do_carrinho['documento']);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(count($documentos_do_carrinho['documento']));

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            $this->view->controlerRequisicao = $documentos_do_carrinho['controller'];
            $this->view->actionRequisicao = $documentos_do_carrinho['action'];
            $this->view->sgSecaoRequisicao = $data['sgSecao'];
            $this->view->cdLotacaoRequisicao = $data['cdLotacao'];

            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        } else {
            $flashmessage['label'] = 'Informação';
            $flashmessage['status'] = 'info';
            $flashmessage['message'] = 'Carrinho Vazio!';
        }
        //}


        if ($data[visualizar] == 'visualizar') {
            $flashmessage['label'] = 'Informação';
            $flashmessage['status'] = 'info';
            $flashmessage['message'] = 'Carrinho Aberto!';
        }

        //Zend_Debug::dump($userNs->meucarrinho);

        $this->view->flashmessage = $flashmessage;
    }

    public function assinarAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidade_entrada = new Zend_Session_Namespace('Ns_Caixaunidade_entrada');
        $form = new Sisad_Form_Verify();
        $data = $this->_getAllParams();

        if (isset($data['acao']) && $data['acao'] == 'Assinar por Senha') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento']);
            $Ns_Caixaunidade_entrada->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixaunidade_entrada->data_post_caixa)) {
            $data['documento'] = $Ns_Caixaunidade_entrada->data_post_caixa;
        }

        $data['COU_COD_MATRICULA'] = $userNs->matricula;
        $form->populate($data);

        $this->view->form = $form;

        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }
        /**
         * Recuperando a unidade da caixa
         */
        $documentos = array();
        $i = 0;
        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            //Zend_Debug::dump($dados_input);
            $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
            $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
            $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
            $i++;
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $Ns_Caixaunidade_entrada->data_post_caixa = $data['documento'];

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (isset($data['Assinar']) && $data['Assinar'] == 'Assinar') {
                try {
                    $data['documento'] = $Ns_Caixaunidade_entrada->data_post_caixa;
                    $coUserid = new Application_Model_DbTable_CoUserId();
                    $resultado = $coUserid->getAssinatura($userNs->matricula, $data["COU_COD_PASSWORD"]);
                    if ($resultado > 0) {
                        foreach ($data['documento'] as $value) {
                            $dados_input = Zend_Json::decode($value);
                            $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                            $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
                            //$dataMofaMoviFase=  $SadTbMofaMoviFase->fetchNew()->toArray();
                            //$dataMofaMoviFase = array();
                            // Zend_Debug::dump($dados_input);
                            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
                            $dataMofaMoviFase["MOFA_DH_FASE"] = new Zend_Db_Expr("SYSDATE");
                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1018; /* ASSINATURA SISAD */
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = 'Assinatura por senha';
//                            Zend_Debug::dump($dataMofaMoviFase);
//                            exit;

                            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
                            $rowMofaMoviFase->save();

                            if (!$rowMofaMoviFase) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível assinar o documento nº $nrDocmDocumento", 'status' => 'error'));
                            } else if ($rowMofaMoviFase) {
                                $Ns_Caixaunidade_encaminhar->data_post_caixa_executado = $data_post_caixa;
                                $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento Assinado por senha com sucesso", 'status' => 'success'));
                            }
                        }
                        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                    } else {
                        $this->_helper->flashMessenger(array('message' => "Senha Inválida. Lembrando que o sistema usa a senha de login no e-Admin. ", 'status' => 'notice'));
                        $this->_helper->_redirector('assinar', 'caixaunidade', 'sisad');
                    }
                } catch (Exception $e) {
                    echo $e;
                }
            }
            $this->view->title = "Assinar Documento por Senha - $siglalotacao";
        }
    }

    public function cancelarAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $siglalotacao = $userNs->siglalotacao;
        $form = new Sisad_Form_Cancelar;
        $Ns_Caixaunidade_cancelar = new Zend_Session_Namespace('Ns_Caixaunidade_cancelar');
        $data = $this->_getAllParams();


        if (isset($data['acao']) && $data['acao'] == 'Excluir') {
            $Ns_Caixaunidade_cancelar->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixaunidade_cancelar->data_post_caixa)) {
            $data['documento'] = $Ns_Caixaunidade_cancelar->data_post_caixa;
        }

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        if ($data['Cancelar'] == 'Cancelar') {
            if ($form->isValid($data)) {
                try {
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $dados_input['MATRICULA_CAIXA_PESSOAL'] = $userNs->matricula;
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                        $dados_input["DOCM_IC_ATIVO"] = "N";
                        $dados_input['MOFA_DS_COMPLEMENTO'] = $data['MOFA_DS_COMPLEMENTO'];
                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                        $cancelamento = $mapperDocumento->cancelarDocumento($dados_input);

                        if (!$cancelamento) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível cancelar o documento nº $nrDocmDocumento", 'status' => 'error'));
                        } else if ($cancelamento) {
                            $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento cancelado com sucesso!", 'status' => 'success'));
                        }
                    }
                } catch (exception $e) {
                    echo $e;
                }
                $this->_helper->_redirector($dados_input["CAIXA_REQUISICAO"], 'caixaunidade', 'sisad');
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Cancelar Documentos - $siglalotacao";
        $this->view->form = $form;
    }

    public function excluirAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sisad_Form_Excluir;
        $Ns_Caixaunidade_excluir = new Zend_Session_Namespace('Ns_Caixaunidade_excluir');
        $data = $this->_getAllParams();


        if (isset($data['acao']) && $data['acao'] == 'Excluir') {
            $Ns_Caixaunidade_excluir->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixaunidade_excluir->data_post_caixa)) {
            $data['documento'] = $Ns_Caixaunidade_excluir->data_post_caixa;
        }

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        /**
         * Recuperando a unidade da caixa
         */
        $documentos = array();
        $i = 0;
        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            //Zend_Debug::dump($dados_input);
            $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
            $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
            $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
            $i++;
        }

        if ($data['Excluir'] == 'Excluir') {
            if ($form->isValid($data)) {
                try {
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $dados_input['MATRICULA_CAIXA_PESSOAL'] = $userNs->matricula;
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                        $dados_input["DOCM_IC_ATIVO"] = "N";
                        $dados_input['MOFA_DS_COMPLEMENTO'] = $data['MOFA_DS_COMPLEMENTO'];
                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                        $cancelamento = $mapperDocumento->cancelarDocumento($dados_input);

                        if (!$cancelamento) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível excluir o documento nº $nrDocmDocumento", 'status' => 'error'));
                        } else if ($cancelamento) {
                            $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento excluido com sucesso!", 'status' => 'success'));
                        }
                    }
                } catch (exception $e) {
                    echo $e;
                }
                $this->_helper->_redirector($dados_input["CAIXA_REQUISICAO"], 'caixaunidade', 'sisad');
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Excluir Documentos - $siglalotacao";
        $this->view->form = $form;
    }

    public function arquivarAction() {
        $form = new Sisad_Form_Arquivar();
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidade_arquivar = new Zend_Session_Namespace('Ns_Caixaunidade_arquivar');
        $data = $this->_getAllParams();


        if (isset($data['acao']) && $data['acao'] == 'Arquivar') {
            // BUSCA JUNTO TODOS OS PROCESSOS APENSADOS
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento']);

            $Ns_Caixaunidade_arquivar->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixaunidade_arquivar->data_post_caixa)) {
            $data['documento'] = $Ns_Caixaunidade_arquivar->data_post_caixa;
        }

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        /**
         * Recuperando a unidade da caixa
         */
        $documentos = array();
        $i = 0;
        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            //Zend_Debug::dump($dados_input);
            $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
            $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
            $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
            $i++;
        }

        if ($data['Arquivar'] == 'Arquivar') {
            if ($form->isValid($data)) {
                try {
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $dados_input['MATRICULA_CAIXA_PESSOAL'] = $userNs->matricula;
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                        $dados_input["DOCM_IC_ARQUIVAMENTO"] = "S";
                        $dados_input['MOFA_DS_COMPLEMENTO'] = $data['MOFA_DS_COMPLEMENTO'];
                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                        $arquivamento = $mapperDocumento->arquivarDocumento($dados_input);

                        if (!$arquivamento) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível arquivar o documento nº $nrDocmDocumento", 'status' => 'error'));
                        } else if ($arquivamento) {
                            $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento arquivado com sucesso!", 'status' => 'success'));
                        }
                    }
                } catch (exception $e) {
                    echo $e;
                }
                $this->_helper->_redirector($dados_input["CAIXA_REQUISICAO"], 'caixaunidade', 'sisad');
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Arquivar Documentos - $siglalotacao";
        $this->view->form = $form;
    }

    public function arquivadosunidadeAction() {

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidadearquividadosisad_index = new Zend_Session_Namespace('Ns_Caixaunidadearquivadosisad_index');
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();

        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
        $siglalotacao = $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

        $form = new Sisad_Form_CaixaDocumentos();
        $form_valores_padrao = $form->getValues();


        //Categoria
        $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_CD_LOTACAO_CATEGORIA = $codlotacao");
        $Categorias = $Categorias->toArray();
        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cont = 0;
        foreach ($Categorias as $Categorias_p):
            $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
            $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NM_CATEGORIA"]));
            $cont++;
        endforeach;
        $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
        $this->view->categorias = $Categorias;

        //Pessoas da unidade
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $pessoas = $OcsTbPmatMatricula->getPessoa($siglasecao, $codlotacao);
        $mode_cd_matr_recebedor = $form->getElement('PAPD_CD_MATRICULA_INTERESSADO');
        $mode_cd_matr_recebedor->addMultiOptions(array('' => 'Selecione uma pessoa da unidade'));
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
        endforeach;

        $uri = $_SERVER['REQUEST_URI'];
        $end = explode('/sisad/', $uri);
        $end = explode('/', $end[1]);


        if ($this->_getParam('nova') === '1') {
            unset($Ns_Caixaunidadearquividadosisad_index->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                unset($Ns_Caixaunidadearquividadosisad_index->data_pesq);
                $this->view->title = "Caixa de Documentos Arquivados - $siglalotacao";
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $filtro = $this->tratafiltrodascaixas($data_pesq);
                $Ns_Caixaunidadearquividadosisad_index->data_pesq = $this->getRequest()->getPost();
                $Ns_Caixaunidadearquividadosisad_index->filtro = $filtro;
            } else {
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "Caixa de Documentos Arquivados - $siglalotacao";
                return;
            }
        }//Post e validate

        $data_pesq = $Ns_Caixaunidadearquividadosisad_index->data_pesq;

        $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();

        if (!is_null($data_pesq)) {
            $rows = $dados->getArquivadosUnidade($codlotacao, $siglasecao, $order, $Ns_Caixaunidadearquividadosisad_index->filtro);
            $this->view->ultima_pesq = true;
        } else {
            $rows = $dados->getArquivadosUnidade($codlotacao, $siglasecao, $order);
            $this->view->ultima_pesq = false;
        }

        $cateNs = new Zend_Session_Namespace('cateNs');
        $cateNs->tipo = 'unidade';
        $cateNs->cdLotacao = $codlotacao;
        $cateNs->sgSecao = $siglasecao;


        /* verifica condições e faz tratamento nos dados */
        $TimeInterval = new App_TimeInterval ();
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {

            if (is_null($rows [$i] ["MODE_DH_RECEBIMENTO"])) {
                $rows [$i] ['MSG_LIDO'] = "Documento não lido";
                $rows [$i] ['CLASS_LIDO'] = "naolido";
                $rows [$i] ['CLASS_LIDO_TR'] = "naolidoTr";
            } else {
                $rows [$i] ['MSG_LIDO'] = "Documento lido";
                $rows [$i] ['CLASS_LIDO'] = "lido";
                $rows [$i] ['CLASS_LIDO_TR'] = "lidoTr";
            }

            if (is_null($rows [$i] ["DOCM_NR_DOCUMENTO_RED"])) {
                $rows [$i] ['MSG_ARQUIVO'] = "Adicionar o arquivo";
                $rows [$i] ['CLASS_ARQUIVO'] = "alertaButton";
            } else {
                $rows [$i] ['MSG_ARQUIVO'] = "Abrir Documento";
                $rows [$i] ['CLASS_ARQUIVO'] = "abrirAnexo";
            }
            $rows [$i] ['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows [$i] ['MOVI_DH_ENCAMINHAMENTO_CHAR']);
            $rows [$i] ['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows [$i] ['DADOS_INPUT'] = Zend_Json::encode($rows [$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }

        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->title = "Caixa de Documentos Arquivados - $siglalotacao";
        $this->view->form = $form;
    }

    public function desarquivarunidadeAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidade_desarquivar = new Zend_Session_Namespace('Ns_Caixaunidade_desarquivar');
        $form = new Sisad_Form_Desarquivar();
        $data = $this->_getAllParams();

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        if (isset($data['acao']) && $data['acao'] == 'Desarquivar') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento']);

            $Ns_Caixaunidade_desarquivar->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixaunidade_desarquivar->data_post_caixa)) {
            $data['documento'] = $Ns_Caixaunidade_desarquivar->data_post_caixa;
        }

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        /**
         * Recuperando a unidade da caixa
         */
        $documentos = array();
        $i = 0;
        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            //Zend_Debug::dump($dados_input);
            $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
            $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
            $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
            $i++;
        }

        if ($data['Desarquivar'] == 'Desarquivar') {
            if ($form->isValid($data)) {
                try {
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);

                        $dados_input['MATRICULA_CAIXA_PESSOAL'] = $userNs->matricula;
                        $dados_input['MOFA_DS_COMPLEMENTO'] = $data['MOFA_DS_COMPLEMENTO'];
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                        $desarquivamento = $mapperDocumento->desarquivarDocumentoUnidade($dados_input);

                        if (!$desarquivamento) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível desarquivar o documento nº $nrDocmDocumento", 'status' => 'error'));
                        } else if ($desarquivamento) {
                            $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento desarquivado com sucesso!", 'status' => 'success'));
                        }
                    }
                } catch (exception $e) {
                    echo $e;
                }
                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Desarquivar Documentos Arquivados - $siglalotacao";

        $this->view->form = $form;
    }

    public function ajaxunidadebysecaoAction() {
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $lotacao = Zend_Filter::FilterStatic($this->_getParam('lotacao'), 'int');
        $tipolotacao = Zend_Filter::FilterStatic($this->_getParam('tipo'), 'int');
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $Lotacao_array = $RhCentralLotacao->getLotacaobySecao($secao, $lotacao, $tipolotacao);
        $this->view->Lotacao_array = $Lotacao_array;
    }

    public function ajaxsubsecoesAction() {
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $lotacao = Zend_Filter::FilterStatic($this->_getParam('lotacao'), 'int');
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao, $lotacao);
        $this->view->Lotacao_array = $Lotacao_array;
    }

    public function ajaxnomedestinatarioAction() {
        $nomeDestinatario = $this->_getParam('term', '');
        $ocsPjur = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $nome_array = $ocsPjur->getNomeDestinatarioAjax($nomeDestinatario);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxnomesolicitanteAction() {
        $matriculanome = $this->_getParam('term', '');
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $nome_array = $OcsTbPmatMatricula->getNomeSolicitanteAjax($matriculanome);
        $fim = count($nome_array);
        for ($i = 0; $i < $fim; $i++) {
            $nome_array[$i] = array_change_key_case($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }

    public function ajaxpessoacaixaAction() {
        $matriculanome = $this->_getParam('matricula', '');
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $nome_array = $OcsTbUnpeUnidadePerfil->getResponsavelCaixaUnidadePessoal($matriculanome);
        $this->view->Caixas = $nome_array;
    }

    public function encaminhadosAction() {
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
        $siglalotacao = $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();

        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $userNs = new Zend_Session_Namespace('userNs');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $rows = $dados->getCaixaUnidadeEncaminhados($codlotacao, $siglasecao, $order);

        /* verifica condições e faz tratamento nos dados */
        $fim = count($rows);
        $TimeInterval = new App_TimeInterval();
        for ($i = 0; $i < $fim; $i++) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOVI_DH_ENCAMINHAMENTO_CHAR']);

            if (is_null($rows[$i]["MODE_DH_RECEBIMENTO"])) {
                $rows[$i]['MSG_LIDO'] = "Documento não lido no DESTINO";
                $rows[$i]['CLASS_LIDO'] = "naolido";
                $rows[$i]['CLASS_LIDO_TR'] = "naolidoTr";
            } else {
                $rows[$i]['MSG_LIDO'] = "Documento lido no DESTINO";
                $rows[$i]['CLASS_LIDO'] = "lido";
                $rows[$i]['CLASS_LIDO_TR'] = "lidoTr";
            }

            $rows[$i]['CAIXA_REQUISICAO'] = $this->getRequest()->getActionName();
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);

            // Caso seja um Processo, a hint será o Objeto
            // Caso seja um Documento, a hint será a Descrição
            switch ($rows[$i]['DTPD_ID_TIPO_DOC']) {
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_ADMINISTRATIVO:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_JUDICIAL:
                case Trf1_Sisad_Definicoes::TIPO_PROCESSO_AVULSO:
                    $rows[$i]['hint'] = $rows[$i]['PRDI_DS_TEXTO_AUTUACAO'];
                    break;
                default:
                    $rows[$i]['hint'] = $rows[$i]['DOCM_DS_ASSUNTO_DOC'];
                    break;
            }

        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Caixa Encaminhados - $siglalotacao";
    }

    public function desfazerAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidade_desfazer = new Zend_Session_Namespace('Ns_Caixaunidade_desfazer');
        $form = new Sisad_Form_DesfazerEncaminhamento();
        $data = $this->getRequest()->getPost();

        if (isset($data['acao']) && $data['acao'] == 'Desfazer') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento'], 'json', 'encaminhados_caixa_unidade');
            $Ns_Caixaunidade_desfazer->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixaunidade_desfazer->data_post_caixa)) {
            $data['documento'] = $Ns_Caixaunidade_desfazer->data_post_caixa;
        }

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        /**
         * Recuperando a unidade da caixa
         */
        $documentos = array();
        $i = 0;
        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            //Zend_Debug::dump($dados_input);
            $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
            $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
            $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
            $i++;
        }


        if ($data['Desfazer'] == 'Desfazer') {
            if ($form->isValid($data)) {
                try {
                    $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $adapter->beginTransaction();
                    $documentosNaoDefeitos = array();
                    $documentosDefeitos[] = array();
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                        $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $data['MODE_CD_MATR_RECEBEDOR'];

                        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataMofaMoviFase["MOFA_DH_FASE"] = $dados_input['MOVI_DH_ENCAMINHAMENTO_CHAR'];

                        $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $dados_input['MODE_CD_SECAO_UNID_DESTINO'];
                        $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $dados_input['MODE_SG_SECAO_UNID_DESTINO'];

                        $dataModoMoviDocumento['MODO_ID_MOVIMENTACAO'] = $dados_input['MOFA_ID_MOVIMENTACAO'];

                        $dataMoviMovimentacao['MOVI_ID_MOVIMENTACAO'] = $dados_input['MOFA_ID_MOVIMENTACAO'];

                        $dataModpDestPessoa['MODP_ID_MOVIMENTACAO'] = $dados_input["MODP_ID_MOVIMENTACAO"];
                        $dataModpDestPessoa['MODP_SG_SECAO_UNID_DESTINO'] = $dados_input["MODP_SG_SECAO_UNID_DESTINO"];
                        $dataModpDestPessoa['MODP_CD_SECAO_UNID_DESTINO'] = $dados_input["MODP_CD_SECAO_UNID_DESTINO"];
                        $dataModpDestPessoa['MODP_CD_MAT_PESSOA_DESTINO'] = $dados_input["MODP_CD_MAT_PESSOA_DESTINO"];

                        if ($dados_input["MODE_DH_RECEBIMENTO"] == NULL) {

                            $SadTbHdpaHistDistribuicao = new Application_Model_DbTable_SadTbHdpaHistDistribuicao();
                            $desfazDistribuicao_retorno = $SadTbHdpaHistDistribuicao->desfazDistribuicaoPorNumeroDoc(
                                    $idDocmDocumento, $dataMofaMoviFase, false); // autocommit

                            if ($desfazDistribuicao_retorno === true) {
                                $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;

                                $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->desfazerencaminhamento(
                                        $idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModoMoviDocumento, $dataModpDestPessoa, false); // autocommit
                                if ($encaminhaDocumento_retorno === true) {
                                    $Ns_Caixaunidade_desfazer->data_post_caixa_executado = $data_post_caixa;
                                    $dados_input['mensagem'] = "Encaminhamento Desfeito Documento nº $nrDocmDocumento.";
                                    $documentosDefeitos[] = $dados_input;
                                } else {
                                    $dados_input['mensagem'] = "Não foi possível desfazer encaminhamento do documento nº $nrDocmDocumento. <br> $encaminhaDocumento_retorno ";
                                    $documentosNaoDefeitos[] = $dados_input;
                                }
                            } else {
                                $dados_input['mensagem'] = "Não foi possível desfazer a distribuição do documento nº $nrDocmDocumento. <br> $desfazDistribuicao_retorno ";
                                $documentosNaoDefeitos[] = $dados_input;
                            }
                        } else {
                            $dados_input['mensagem'] = "Não foi possível desfazer encaminhamento do documento nº $nrDocmDocumento, já lido na unidade de destino. Solicite-o à unidade de destino que encaminhe de volta.";
                            $documentosNaoDefeitos[] = $dados_input;
                        }
                    }
                    if (count($documentosNaoDefeitos) > 0) {
                            $adapter->rollBack();
                        $this->_helper->flashMessenger(array('message' => 'A ação defazer não foi realizada para todos os documentos pelo motivo abaixo.', 'status' => 'notice'));
                        foreach ($documentosNaoDefeitos as $nãoDesfeito) {
                            $this->_helper->flashMessenger(array('message' => $nãoDesfeito['mensagem'], 'status' => 'error'));
                        }
                    } elseif (count($documentosDefeitos) > 0) {
                        $adapter->commit();
                        foreach ($documentosDefeitos as $desfeito) {
                            $this->_helper->flashMessenger(array('message' => $desfeito['mensagem'], 'status' => 'success'));
                    }
                    } else {
                        $this->_helper->flashMessenger(array('message' => 'Não foi solicitada nenhuma ação.', 'status' => 'error'));
                    }
                } catch (Exception $e) {
                    $adapter->rollBack();
                    $this->_helper->flashMessenger(array('message' => $e->getMessage(), 'status' => 'error'));
                }
                $this->_helper->_redirector('encaminhados', 'caixaunidade', 'sisad');
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->formInterno = $form;
        $this->view->title = "Documentos para desfazer encaminhamento - $siglalotacao";
    }

    /*
     * Lista os documentos que serão endereçados e mostra os campos de endereçamento
     *
     */

    public function enderecarAction() {
        $enderecarSession = new Zend_Session_Namespace('enderecarDocumentos');
        $userNs = new Zend_Session_Namespace('userNs');
        $movi_movimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $Ns_Caixaunidade_encaminhar = new Zend_Session_Namespace('Ns_Caixaunidade_Encaminhar');

        $externo = new Sisad_Form_EncaExterno();
        $this->view->formExterno = $externo;

        $data = $this->getRequest()->getPost();

        if ($this->getRequest()->isPost()) {
            $cont = 0;
            $rows = array();
            if (isset($data['setAcao']) && $data['setAcao'] == 'SetEndereco') {

                if ($externo->isValid($data)) {

                    if ($this->getRequest()->isPost()) {

                        try {
                            /**
                             * Recuperando a unidade da caixa
                             */
                            $documentos = array();
                            $i = 0;
                            foreach ($enderecarSession->DadosDocsSet as $value) {
                                $dados_input = Zend_Json::decode($value);
                                //Zend_Debug::dump($dados_input);
                                $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                                $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                                $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
                                $i++;
                            }

                            $postPostagemProcDoc = $this->getRequest()->getPost();

                            //Parametros da postagem
                            $postPostagemProcDoc["POST_SG_SECAO_ORIGEM"] = $siglasecao;
                            $postPostagemProcDoc["POST_CD_LOTACAO_ORIGEM"] = $codlotacao;
                            unset($postPostagemProcDoc["setAcao"]);
                            unset($postPostagemProcDoc["Salvar"]);
                            unset($postPostagemProcDoc["POST_CD_LOCACAO_DESTINO"]);


                            //Documentos para postagem.
                            foreach ($enderecarSession->DadosDocsSet as $value) {
                                $documentos[$cont] = Zend_Json::decode($value);
                                $cont++;
                            }

                            $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
                            $protocolar = $SadTbPrdcProtDocProcesso->setEnderecar($postPostagemProcDoc, $documentos);

                            $this->_helper->flashMessenger(array('message' => "Documentos Endereçados!", 'status' => 'success'));
                            $this->_helper->_redirector('entrada', 'Caixaunidade', 'sisad');
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível endereçar documentos!", 'status' => 'error'));
                            $externo->populate($postPostagemProcDoc);
                            $externo->populate($data);
                            return;
                        }
                    }
                } else {
                    $data["documento"] = $enderecarSession->DadosDocsSet;
                    $data['acao'] = 'Endereçar';
                }
            }
            if (isset($data['acao']) && $data['acao'] == 'Endereçar' || !is_null($Ns_Caixaunidade_encaminhar->data_post_caixa)) {
                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /* Ordenação das paginas */
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column . ' ' . $order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

                /* Ordenação */
                $cont = 0;
                $rows = array();
                foreach ($data['documento'] as $value) {
                    $dados_input = Zend_Json::decode($value);
                    $rows[$cont] = $dados_input;
                    $rows[$cont]["DADOS_INPUT"] = $value;
                    $enderecarSession->DadosDocsSet[$cont] = $value;
                    $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
                    $cont++;
                }

                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(15);

                $externo->populate($data);
                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                $this->view->title = "Endereçamento de Documentos Para Postagem - $siglalotacao";
                $data = 0;
            }
        }
    }

    public function protocolarAction() {
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
        $siglalotacao = $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();


        $Ns_Caixaunidade_encaminhar = new Zend_Session_Namespace('Ns_Caixaunidade_Encaminhar');
        $data = $this->getRequest()->getPost();
        $userNs = new Zend_Session_Namespace('userNs');
        $PrdcProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso;
        $PostPostagem = new Application_Model_DbTable_SadTbPostPostagemProcDoc;

        if (isset($data['acao']) && $data['acao'] == 'Protocolar' || !is_null($Ns_Caixaunidade_encaminhar->data_post_caixa)) {

            $prdc = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
            $nrProtocolo = new Zend_Session_Namespace('protocolo');

            /*
             * Dados do Protocolo
             */
            $protocolo = array();
            $protocolo["PROT_SG_SECAO"] = $siglasecao;
            $protocolo["PROT_CD_LOTACAO"] = $codlotacao;
            $protocolo["PROT_CD_MATRICULA"] = $userNs->matricula;

            /*
             * Monta o Nr de Protocolo
             */
            $nextProtocolo = $PrdcProcesso->getProtocolosSecao($codlotacao, $siglasecao);
            $nextProtocolo = $nextProtocolo[0]["QTD"] + 1;
            $nextProtocolo = str_pad($nextProtocolo, 6, "0", STR_PAD_LEFT);
            $ano = date("Y");


            $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
            $dsecao = $RhCentralLotacao->getSecSubsecPai($siglasecao, $codlotacao);
            $cdSecao = $dsecao["SESB_SESU_CD_SECSUBSEC"];

            $cdLotacao = $protocolo["PROT_CD_LOTACAO"];

            $protocolo["PROT_ID_PROTOCOLO"] = $ano . "0" . $cdSecao . "0" . $cdLotacao . $nextProtocolo;

            $protocolo = $PrdcProcesso->setProtocolo($protocolo, $data["idpostagem"]);

            $this->_helper->flashMessenger(array('message' => "Nr: Protocolo:$nrProtocolo->protocolo - Metadados encaminhados ao Protocolo, Favor encaminhar Documento Físico!", 'status' => 'success'));
            return $this->_helper->_redirector('protocolar', 'caixaunidade', 'sisad');
        } else {
            $rows = $PostPostagem->getPostagens($siglasecao, $codlotacao);

            /* paginação */
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /* Ordenação das paginas */
            $order_column = $this->_getParam('ordem', 'DTPD_NO_TIPO');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /* Ordenação */

            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(10);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            $this->view->title = "Protocolar Documentos - $siglalotacao";
        }
    }

    public function encaminharAction() {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $Ns_Caixaunidade_encaminhar = new Zend_Session_Namespace('Ns_Caixaunidade_encaminhar');
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sisad_Form_EncaInterno();
        $formDivulgar = new Sisad_Form_Divulgar();

        /* Verificacao para encaminhar documento da caixa de entrada da unidade para listas internas para diferentes grupos */
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $divulgacao_avisos = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('DIVULGAÇÃO PARA LISTAS INTERNAS (AVISOS)', $userNs->matricula);
        $divulgacao_avisos_ESPECIAL = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('LISTAS INTERNAS (AVISOS) ESPECIAL', $userNs->matricula);
        if (($divulgacao_avisos) || ($divulgacao_avisos_ESPECIAL)) {
            $this->view->PermissaoLista = TRUE;
        } else {
            /* Retira o form para divulgação em listas internas */
            $radio = $form->TIPO_MOVIMENTACAO;
            $radio->removeMultiOption('internalista'); /* Listas Internas */
            $this->view->PermissaoLista = FALSE;
        }

        $data = $this->getRequest()->getPost();
        /**
         * Forms
         */
        /**
         * Variáves de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');

        /**
         * Funcionando para lotações de subsecoes.
         */
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        if ($data["acao"] == 'Encaminhar') {
            if ($userNs->codtipolotacao != 1) {
                if ($userNs->siglasecao == 'TR') {
                    $dadosPopulate['MODE_SG_SECAO_UNID_DESTINO'] = $userNs->siglasecao . '|' . $userNs->codsecsubseclotacao . '|9';
                    $secao = $rh_central->getSubSecoes($userNs->siglasecao, $userNs->codsecsubseclotacao);
                } else {
                    $dadosPopulate['MODE_SG_SECAO_UNID_DESTINO'] = $userNs->siglasecao . '|' . $userNs->codlotacaopai . '|1';
                    $secao = $rh_central->getSubSecoes($userNs->siglasecao, $userNs->codlotacaopai);
                }
                $dadosPopulate['SECAO_SUBSECAO'] = $userNs->siglasecao . '|' . $userNs->codsecsubseclotacao . '|' . $userNs->codtipolotacao;
                $unidade = $rh_central->getLotacaobySecao($userNs->siglasecao, $userNs->codsecsubseclotacao, $userNs->codtipolotacao);
            } else {
                if ($userNs->siglasecao == 'TR') {
                    $dadosPopulate['MODE_SG_SECAO_UNID_DESTINO'] = $userNs->siglasecao . '|' . $userNs->codsecsubseclotacao . '|9';
                } else {
                    $dadosPopulate['MODE_SG_SECAO_UNID_DESTINO'] = $userNs->siglasecao . '|' . $userNs->codsecsubseclotacao . '|1';
                }
                $dadosPopulate['SECAO_SUBSECAO'] = $userNs->siglasecao . '|' . $userNs->codsecsubseclotacao . '|' . $userNs->codtipolotacao;
                $secao = $rh_central->getSubSecoes($userNs->siglasecao, $userNs->codsecsubseclotacao);
                $unidade = $rh_central->getLotacaobySecao($userNs->siglasecao, $userNs->codsecsubseclotacao, $userNs->codtipolotacao);
            }

            $secao_subsecao = $form->getElement('SECAO_SUBSECAO');
            $secao_subsecao->addMultiOptions(array('' => ''));
            foreach ($secao as $lotacao):
                $secao_subsecao->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] . '|' . $lotacao["LOTA_TIPO_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
            endforeach;

            $secao_subsecao = $form->getElement('MODE_CD_SECAO_UNID_DESTINO');
            $secao_subsecao->addMultiOptions(array('' => ''));
            foreach ($unidade as $lotacao):
                $secao_subsecao->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"] . ' - ' . $lotacao["FAMILIA_LOTACAO"]));
            endforeach;
            $form->populate($dadosPopulate);
            $this->view->formInterno = $form;
            $this->view->formDivulgar = $formDivulgar;
        }else if ($data["acao"] == 'EncaminhamentoInterno') {
            $dadosPopulate['MODE_SG_SECAO_UNID_DESTINO'] = $data['MODE_SG_SECAO_UNID_DESTINO'];
            $dadosPopulate['SECAO_SUBSECAO'] = $data['SECAO_SUBSECAO'];

            $dadoSecao = explode('|', $data["MODE_SG_SECAO_UNID_DESTINO"]);
            $dadoUnidade = explode('|', $data["SECAO_SUBSECAO"]);

            $secao = $rh_central->getSubSecoes($dadoSecao[0], $dadoSecao[1]);
            $unidade = $rh_central->getLotacaobySecao($dadoUnidade[0], $dadoUnidade[1], $dadoUnidade[2]);
            $secao_subsecao = $form->getElement('SECAO_SUBSECAO');
            $secao_subsecao->addMultiOptions(array('' => ''));
            foreach ($secao as $lotacao):
                $secao_subsecao->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] . '|' . $lotacao["LOTA_TIPO_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
            endforeach;

            $secao_subsecao = $form->getElement('MODE_CD_SECAO_UNID_DESTINO');
            $secao_subsecao->addMultiOptions(array('' => ''));
            foreach ($unidade as $lotacao):
                $secao_subsecao->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
            endforeach;
            $form->populate($dadosPopulate);
            $this->view->formInterno = $form;
        }

        /**
         * FIM
         */
        if ($this->getRequest()->isPost()) {
            if (isset($data['acao']) && $data['acao'] == 'Encaminhar' || !is_null($Ns_Caixaunidade_encaminhar->data_post_caixa)) {
                $data_post_caixa = $data;
                if (isset($data['acao']) && $data['acao'] == 'Encaminhar') {

                    $service_juntada = new Services_Sisad_Juntada();
                    $data_post_caixa['documento'] = $service_juntada->completaComApensados($data_post_caixa['documento']);

                    $Ns_Caixaunidade_encaminhar->data_post_caixa = $data_post_caixa;
                } else if (!is_null($Ns_Caixaunidade_encaminhar->data_post_caixa)) {
                    $data_post_caixa = $Ns_Caixaunidade_encaminhar->data_post_caixa;
                }
                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /* Ordenação das paginas */
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column . ' ' . $order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                /* Ordenação */

                $cont = 0;
                $rows = array();
                /**
                 * Dayane incorporar ao código essas modificações
                 * $testeVistaUnidadeApenso = array();
                  $naoTemVistaUnidade = array();
                 */
                foreach ($data_post_caixa['documento'] as $value) {
                    $rows['documento'][$cont] = Zend_Json::decode($value);
                    /*
                      //
                     * Dayane incorporar ao código essas modificações quando for fazer restrição de vistas por unidade
                     * visto que não é possivel movimentar apenas um dos processos apensos caso um deles seja barrado.
                     * Tudo que fizer em um processo tem que fazer em outro no caso do apenso
                      $doc = $rows['documento'][$cont];

                      //PEGA A LINHA DO DOCUMENTO
                      $testeVistaUnidadeApenso[$doc['DOCM_ID_DOCUMENTO']] = $cont;
                      if (!isset($naoTemVistaUnidade[$doc['DOCM_ID_DOCUMENTO']])) {
                      //verifica se o documento eh confidencial
                      if (in_array($doc['DOCM_ID_CONFIDENCIALIDADE'], array("1", "3", "4", "5"))) {
                      $rows['documento'][$cont]['restricao'] = 1;

                      //verifico as unidades cadastradas com vistas
                      $vistaUnidade = $sadTbPapdParteProcDoc->verificaTipoDeVistasOuPartes($doc, 4, 3);
                      if (count($vistaUnidade) > 0) {
                      $vistaUnidade = '"' . implode('","', $vistaUnidade['value']) . '"';
                      }
                      $rows['documento'][$cont]['vistas'] = $vistaUnidade;

                      // se nao tem unidade cadastrada como vistas
                      if (!$vistaUnidade) {
                      // remove o doc da listagem e apresenta msg pro usuario
                      $flashmessage['label'] = 'Atenção: os seguintes documentos confidenciais foram retirados da listagem pois não possuem uma Unidade cadastrada com vistas. <br/>';
                      $flashmessage['status'] = 'notice';
                      $flashmessage['message'] .= $doc['DTPD_NO_TIPO'] . " nº " . $doc['DOCM_NR_DOCUMENTO'] . " <br/>";

                      if (isset($doc['apensados'])) {
                      foreach ($doc['apensados'] as $apenso_apenso) {
                      $flashmessage['message'] .= $apenso_apenso['DTPD_NO_TIPO'] . " nº " . $apenso_apenso['DOCM_NR_DOCUMENTO'] . ", pois seu apenso confidencial nº " . $doc['DOCM_NR_DOCUMENTO'] . " não possui uma Unidade cadastrada como vistas.<br/>";
                      $naoTemVistaUnidade[$apenso_apenso['DOCM_ID_DOCUMENTO']] = $apenso_apenso;
                      }
                      }
                      $this->view->flashMessages = $flashmessage;
                      unset($rows['documento'][$cont]);
                      }
                      } else {
                      $rows['documento'][$cont]['restricao'] = 0;
                      }
                      } else {
                      unset($rows['documento'][$cont]);
                      }
                     */
                    $cont++;
                }

                $paginator = Zend_Paginator::factory($rows['documento']);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(count($rows['documento']));

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                /**
                 * Recuperando a unidade da caixa
                 */
                $documentos = array();
                $i = 0;
                foreach ($data_post_caixa['documento'] as $value) {
                    $dados_input = Zend_Json::decode($value);
                    //Zend_Debug::dump($dados_input);
                    $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                    $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                    $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
                    $i++;
                }
                $this->view->title = "Documentos para encaminhar da - $siglalotacao";
            }

            if (isset($data['acao']) && $data['acao'] == 'EncaminhamentoInterno') {
                if ($anexos->getFileName()) {
                    try {
                        $upload = new App_Multiupload_NewMultiUpload();
                        $nrDocsRed = $upload->incluirarquivos($anexos);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                    }
                }

                if (!$form->isValid($data)) {
                    $form->getElement('MOFA_DS_COMPLEMENTO')->removeFilter('HtmlEntities');
                    if ($form->getElement('MOFA_DS_COMPLEMENTO')->hasErrors()) {
                        $form->getElement('MOFA_DS_COMPLEMENTO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $form->populate($data);
                    return;
                }
                $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues());


                $aux_destino_array = explode('|', $data['MODE_SG_SECAO_UNID_DESTINO']);
                $data['MODE_SG_SECAO_UNID_DESTINO'] = $aux_destino_array[0];
                
                $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
                $pessoas = $OcsTbPmatMatricula->getPessoasResponsaveisCaixa($aux_destino_array);
                
                unset($aux_destino_array);
                $aux_destino_array = explode('|', $data['MODE_CD_SECAO_UNID_DESTINO']);
                $data['MODE_CD_SECAO_UNID_DESTINO'] = $aux_destino_array[1];


                $data_post_caixa = $Ns_Caixaunidade_encaminhar->data_post_caixa;

                if ($data_post_caixa != $Ns_Caixaunidade_encaminhar->data_post_caixa_executado) {
                    set_time_limit(120);
                    if ($nrDocsRed["erro"]) {
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                        $this->view->form = $form;
                        $this->render('encaminhar');
                        return;
                    }
                    if (!$nrDocsRed["existentes"]) {
                        foreach ($data_post_caixa['documento'] as $value) {

                            $dados_input = Zend_Json::decode($value);
                            $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                            $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                            /* dados da origem do documento */
                            if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixaunidade' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'rascunhos') {

                                $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input['DOCM_SG_SECAO_REDATORA'];
                                $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input['DOCM_CD_LOTACAO_REDATORA'];
                            } else if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixaunidade' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'entrada') {

                                $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input['MODE_SG_SECAO_UNID_DESTINO'];
                                $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input['MODE_CD_SECAO_UNID_DESTINO'];
                            }

                            $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;
                            /* Encaminhamento para unidade */

                            /* dados do destino do documento */
                            $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $data['MODE_SG_SECAO_UNID_DESTINO'];
                            $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $data['MODE_CD_SECAO_UNID_DESTINO'];
                            $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                            /* Encaminhamento para unidade */
                            /* $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = ; */

                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1010; /* ENCAMINHAMENTO SISAD */
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];
                             /**
                             * Verifica se existe algum responsável pela caixa antes de encaminhar o documento
                             */
                            $atendentesCaixa = array();
                            $atendentesCaixa[0] = $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"];
                            $atendentesCaixa[1] = $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"];
                            if ((count($OcsTbPmatMatricula->getPessoasResponsaveisCaixa($atendentesCaixa)) == 0)) {
                                 $msg_to_user = "<div class='notice'><strong>Alerta: </strong>A caixa de destino não possui responsável. Favor escolher outra caixa.</div>";
                                 $this->view->flashMessagesView = $msg_to_user;
                                 return;
                            }
                            $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento();

                            $dataModpDestPessoa = array();
                            if (!$nrDocsRed["incluidos"]) {
                                try {
                                    $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestPessoa);
                                    $OcsTbUnpeUnidadePerfil1 = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
                                    $responsaveis = $OcsTbUnpeUnidadePerfil1->getPessoasComPerfilX(9, $data['MODE_SG_SECAO_UNID_DESTINO'], $data['MODE_CD_SECAO_UNID_DESTINO']);
                                    foreach ($responsaveis as $dadosresp) {
                                        $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                                        $matricula = $dadosresp['PMAT_CD_MATRICULA'];
                                        $titulo = 'Encaminhamento de Documentos/Processos';
                                        $sistema = 'SISAD';
                                        $mensagem = 'Prezado(a) ' . $dadosresp['PNAT_NO_PESSOA'] . ', <br/><br/> Há um novo documento/processo na Caixa da Unidade: ' . $dadosresp['LOTA_SIGLA_LOTACAO'] . ' - ' . $dadosresp['LOTA_DSC_LOTACAO'];
                                        $retorno = $tabelaNotf->setnotfAction($matricula, $titulo, $sistema, $mensagem);
                                    }

                                    $Ns_Caixaunidade_encaminhar->data_post_caixa_executado = $data_post_caixa;
                                    $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento Encaminhado", 'status' => 'success'));
                                } catch (Exception $exc) {
                                    $this->_helper->flashMessenger(array('message' => "Não foi possível encaminhar o documento nº $nrDocmDocumento", 'status' => 'error'));
                                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                                }
                            } else {
                                try {
                                    $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestPessoa, $nrDocsRed);
                                    $OcsTbUnpeUnidadePerfil1 = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
                                    $responsaveis = $OcsTbUnpeUnidadePerfil1->getPessoasComPerfilX(9, $data['MODE_SG_SECAO_UNID_DESTINO'], $data['MODE_CD_SECAO_UNID_DESTINO']);
                                    foreach ($responsaveis as $dadosresp) {
                                        $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                                        $matricula = $dadosresp['PMAT_CD_MATRICULA'];
                                        $titulo = 'Encaminhamento de Documentos/Processos';
                                        $sistema = 'e-Sisad';
                                        $mensagem = 'Prezado(a) ' . $dadosresp['PNAT_NO_PESSOA'] . ', <br/><br/> Há um novo documento/processo na Caixa da Unidade: ' . $dadosresp['LOTA_SIGLA_LOTACAO'] . ' - ' . $dadosresp['LOTA_DSC_LOTACAO'];
                                        $tabelaNotf->setnotfAction($matricula, $titulo, $sistema, $mensagem);
                                    }

                                    $Ns_Caixaunidade_encaminhar->data_post_caixa_executado = $data_post_caixa;
                                    $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento Encaminhado", 'status' => 'success'));
                                } catch (Exception $exc) {
                                    $this->_helper->flashMessenger(array('message' => "Não foi possível encaminhar o documento nº $nrDocmDocumento", 'status' => 'error'));
                                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                                }
                            }
                        }
                    } else {
                        foreach ($nrDocsRed["existentes"] as $existentes) {
                            $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                            $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView = $msg_to_user;
                        }
                        $this->view->form = $form;
                        $this->render('encaminhar');
                        return;
                    }
                }
                return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
            } elseif (($data["TIPO_MOVIMENTACAO"] == "internalista") && ($data["acao"] == "EncaminhamentoInternoLista")) {
                $documento = $data_post_caixa;
                $tabelaDocumentoLista = new Application_Model_DbTable_SadTbDoliDocumentoLista();
                $divulgacao = $tabelaDocumentoLista->setDivulgarDocumento($data, $documento);
                if ($divulgacao) {
                    $this->_helper->flashMessenger(array('message' => "Documento enviado com sucesso para Lista de Divulgaçao.", 'status' => 'success'));
                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                } elseif ($divulgacao == 'nao_publico') {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível enviar, devido à confidencialidade de um ou mais documentos.", 'status' => 'error'));
                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                } else {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível enviar documento para Lista de Divulgação.", 'status' => 'error'));
                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                }
            }
        }
    }

    public function encaminharpessoaAction() {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidade_encaunidadepessoa = new Zend_Session_Namespace('Ns_Caixaunidade_encaunidadepessoa');

        $form = new Sisad_Form_EncaUnidadePessoa();
        $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues());

        if (isset($data['acao']) && $data['acao'] == 'Encaminhar Pessoa') {
            $service_juntada = new Services_Sisad_Juntada();
            $data['documento'] = $service_juntada->completaComApensados($data['documento']);
            $Ns_Caixaunidade_encaunidadepessoa->data_post_caixa = $data['documento'];
        } else if (!is_null($Ns_Caixaunidade_encaunidadepessoa->data_post_caixa)) {
            $data['documento'] = $Ns_Caixaunidade_encaunidadepessoa->data_post_caixa;
        }

        /**
         * Recuperando a unidade da caixa
         */
        $documentos = array();
        $i = 0;
        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
            $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
            $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
            $i++;
        }

        /**
         * Configurando o Form
         */
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $pessoas = $OcsTbPmatMatricula->getPessoa($siglasecao, $codlotacao);
        $mode_cd_matr_recebedor = $form->getElement('MODE_CD_MATR_RECEBEDOR');
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
        endforeach;

        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        /**
         * Perfil 9 RESPONSÁVEL PELA CAIXA DA UNIDADE
         */
        $pessoas = $OcsTbUnpeUnidadePerfil->getPessoasComPerfilX(9, $siglasecao, $codlotacao);
        $mode_cd_matr_recebedor_responsavel = $form->getElement('MODE_CD_MATR_RECEBEDOR_RESPONSAVEL');
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor_responsavel->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["PNAT_NO_PESSOA"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
        endforeach;

        /**
         * Caixas Disponiveis
         */
        $dados = explode(' - ', $data['MODE_CD_MATR_RECEBEDOR_UNIDADES']);
        $OcsTbUnpeUnidadePerfil = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
        $caixas = $OcsTbUnpeUnidadePerfil->getResponsavelCaixaUnidadePessoal($dados[0]);
        $caixas_disponiveis = $form->getElement('CAIXAS_DISPONIVEIS');
        foreach ($caixas as $value):
            $caixas_disponiveis->addMultiOptions(array($value["LOTA_SIGLA_SECAO"] . '|' . $value["LOTA_COD_LOTACAO"] => $value["LOTA_SIGLA_LOTACAO"] . ' - ' . $value["LOTA_DSC_LOTACAO"] . ' - ' . $value["LOTA_COD_LOTACAO"] . ' - ' . $value["LOTA_SIGLA_SECAO"]));
        endforeach;

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }

        if ($data['acao'] == 'EncaminharPessoaForm') {
            if ($form->isValid($data)) {
                if ($anexos->getFileName()) {
                    try {
                        $upload = new App_Multiupload_NewMultiUpload();
                        $nrDocsRed = $upload->incluirarquivos($anexos);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                    }
                }
                if ($nrDocsRed["erro"]) {
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->formInterno = $form;
                    $this->render('encaminharpessoa');
                    return;
                }
                /*
                 * Dayane quando for implementar a funcionalidade incluir as seguintes validações
                 * visto que tudo que fizer em um processo apensado deve faer no outro
                 * se um nao for encaminhado por algum motivo entao nao devera ser encaminhado o outro
                 * $cont = 0;
                  $rows = array();

                  //verifica se os documentos possuem pessoas cadastradas com vistas para poderem ser encaminhados
                  foreach ($data['documento'] as $value) {

                  $rows['documento'][$cont] = Zend_Json::decode($value);
                  $doc = $rows['documento'][$cont];
                  //verifica se o documento eh confidencial
                  if (in_array($doc['DOCM_ID_CONFIDENCIALIDADE'], array("1", "3", "4", "5"))) {
                  $rows['documento'][$cont]['restricao'] = 1;
                  //verifico e retorno as unidades cadastradas com vistas
                  if ($doc['DOCM_ID_CONFIDENCIALIDADE'] != "4") {
                  $vistaUnidade = $sadTbPapdParteProcDoc->verificaTipoDeVistasOuPartes($doc, 4, 3);
                  if (count($vistaUnidade) > 0) {
                  $vistaUnidade = '"' . implode('","', $vistaUnidade['value']) . '"';
                  $rows['documento'][$cont]['unidades'] = $vistaUnidade;
                  }
                  }

                  //verifico e retorno as pessoas cadastradas com vistas
                  $vistaPessoa = $sadTbPapdParteProcDoc->verificaTipoDeVistasOuPartes($doc, 1, 3);
                  //Zend_Debug::dump($vistaPessoa, 'vistaPessoa');
                  if (count($vistaPessoa) > 0) {
                  //$v = explode("-", $vistaPessoa);
                  $vistaPessoa = '"' . implode('","', $vistaPessoa['pessoa']) . '"';
                  $rows['documento'][$cont]['pessoas'] = $vistaPessoa;
                  }
                  //Zend_Debug::dump($vistaPessoa, 'vistaPessoa depois ');
                  } else {
                  $rows['documento'][$cont]['restricao'] = 0;
                  }
                  $cont++;
                  } */
                if (!$nrDocsRed["existentes"]) {
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];
                        /* dados da origem do documento */

                        $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;
                        $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input['MODE_SG_SECAO_UNID_DESTINO'];
                        $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input['MODE_CD_SECAO_UNID_DESTINO'];

                        /* dados do destino do documento */
                        $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $dados_input['MODE_SG_SECAO_UNID_DESTINO'];
                        $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $dados_input['MODE_CD_SECAO_UNID_DESTINO'];
                        $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';

                        $dataMofaMoviFase["MOFA_ID_FASE"] = 1010; /* ENCAMINHAMENTO SISAD */
                        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                        $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;

                        $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = NULL;
                        if ($data["ENCAMINHAMENTO"] == 'daunidade') {
                            $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $data['MODE_CD_MATR_RECEBEDOR'];
                        } else if ($data["ENCAMINHAMENTO"] == 'responsaveis') {
                            $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $data['MODE_CD_MATR_RECEBEDOR_RESPONSAVEL'];
                        } else if ($data["ENCAMINHAMENTO"] == 'outraunidade') {
                            $destino = explode('|', $data["CAIXAS_DISPONIVEIS"]);
                            $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $dados[0];
                            $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $destino[0];
                            $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $destino[1];
                        } else {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível encaminhar o(s) documento(s). Dados de destino não informados.", 'status' => 'notice'));
                            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                        }
                        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
                        if (is_null($OcsTbPmatMatricula->find($dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"])->current())) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível encaminhar o(s) documento(s). Dados de destino não informados.", 'status' => 'notice'));
                            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                        }
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] . '@trf1.jus.br';
                        $assunto = 'Encaminhamento de Documento';
                        $corpo = "Foi encaminhado um documento para sua caixa pessoal.</p>
                                  Número do Documento: " . $nrDocmDocumento . " <br/>
                                  Encaminhado por: " . $userNs->nome . " <br/>
                                  Tipo do Documento: " . $dados_input["DTPD_NO_TIPO"] . " <br/>
                                  Descrição do Encaminhamento: " . nl2br($dataMofaMoviFase["MOFA_DS_COMPLEMENTO"]) . "<br/>";
                        if (!$nrDocsRed["incluidos"]) {
                            try {
                                $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestPessoa);

                                $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                                $matricula = $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"];
                                $titulo = 'Encaminhamento de Documentos/Processos';
                                $sistema = 'SISAD';
                                $mensagem = 'Prezado(a), <br/><br/> Há um novo documento/processo na sua Caixa Pessoal.';
                                $retorno = $tabelaNotf->setnotfAction($matricula, $titulo, $sistema, $mensagem);

                                $Ns_Caixaunidade_encaminhar->data_post_caixa_executado = $data_post_caixa;
                                $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento Encaminhado", 'status' => 'success'));
                            } catch (Exception $exc) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível encaminhar o documento nº $nrDocmDocumento", 'status' => 'error'));
                                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                            }
                        } else {
                            try {
                                $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataModpDestPessoa, $nrDocsRed);
                                $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                                $matricula = $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"];
                                $titulo = 'Encaminhamento de Documentos/Processos';
                                $sistema = 'SISAD';
                                $mensagem = 'Prezado(a), <br/><br/> Há um novo documento/processo na sua Caixa Pessoal.';
                                $retorno = $tabelaNotf->setnotfAction($matricula, $titulo, $sistema, $mensagem);

                                $Ns_Caixaunidade_encaminhar->data_post_caixa_executado = $data_post_caixa;
                                $this->_helper->flashMessenger(array('message' => "Documento nº $nrDocmDocumento Encaminhado", 'status' => 'success'));
                            } catch (Exception $exc) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível encaminhar o documento nº $nrDocmDocumento", 'status' => 'error'));
                                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                            }
                        }
                    }
                    try {
                        $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => 'Não foi possivel enviar email de confirmação para solicitação: ' . $dados_input["DOCM_NR_DOCUMENTO"] . '<br><b> Erro: </b> <p>' . strip_tags($exc->getMessage()) . '<p>', 'status' => 'notice'));
                    }
                } else {
                    foreach ($nrDocsRed["existentes"] as $existentes) {
                        $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->formInterno = $form;
                    $this->render('encaminharpessoa');
                    return;
                }
                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
            }
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->formInterno = $form;
        $this->view->title = "Documentos para encaminhar da $siglalotacao";
    }

    public function parecerAction() {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidade_parecer = new Zend_Session_Namespace('Ns_Caixaunidade_parecer');

        $form = new Sisad_Form_Parecer();
        $this->view->formParecer = $form;

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues());
            if ($anexos->getFileName()) {
                try {
                    $upload = new App_Multiupload_NewMultiUpload();
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                }
            }
            if (isset($data['acao']) && $data['acao'] == 'Parecer' || !is_null($Ns_Caixaunidade_parecer->data_post_caixa)) {
                $data_post_caixa = $data;
                if (isset($data['acao']) && $data['acao'] == 'Parecer') {
                    $service_juntada = new Services_Sisad_Juntada();
                    $data_post_caixa['documento'] = $service_juntada->completaComApensados($data_post_caixa['documento']);
                    $Ns_Caixaunidade_parecer->data_post_caixa = $data_post_caixa;
                } else if (!is_null($Ns_Caixaunidade_parecer->data_post_caixa)) {
                    $data_post_caixa = $Ns_Caixaunidade_parecer->data_post_caixa;
                }
                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /* Ordenação das paginas */
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column . ' ' . $order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                /* Ordenação */

                if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixaunidade' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'processosdaunidade' || $data_post_caixa['action'] == 'entrada' || $data_post_caixa['action'] == 'documentosdaunidade') {

                    $cont = 0;
                    $rows = array();
                    foreach ($data_post_caixa['documento'] as $value) {
                        $rows['documento'][$cont] = Zend_Json::decode($value);
                        $cont++;
                    }

                    /**
                     * Recuperando a unidade da caixa
                     */
                    $documentos = array();
                    $i = 0;
                    foreach ($data_post_caixa['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                        $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                        $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
                        $i++;
                    }
                }

                $paginator = Zend_Paginator::factory($rows['documento']);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(count($rows['documento']));

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
                $this->view->title = "Parecer em Documento(s) - $siglalotacao";
            }
            if (isset($data['acao']) && $data['acao'] == 'submitParecer') {
                $data_post_caixa = $Ns_Caixaunidade_parecer->data_post_caixa;

                if (!$form->isValid($data)) {
                    unset($data['MODE_SG_SECAO_UNID_DESTINO']);
                    unset($data['MODE_CD_SECAO_UNID_DESTINO']);
                    $this->view->formParecer = $form;
                    return;
                }
                if ($nrDocsRed["erro"]) {
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('parecer');
                    return;
                }
                if (!$nrDocsRed["existentes"]) {
                    try {
                        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                        $db->beginTransaction();
                        $DocNaoParecer = array();
                        $docsComParecer = array();
                        foreach ($data_post_caixa['documento'] as $value) {

                            $dados_input = Zend_Json::decode($value);

                            $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];

                            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
                            $dataMofaMoviFase["MOFA_ID_FASE"] = 1011; /* PARECER SISAD */
                            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                            $nrDocsRed["ID_DOCUMENTO"] = $idDocmDocumento;
                            $nrDocsRed["ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];

                            $mapperDocumento = new Sisad_Model_DataMapper_Documento();

                            if (!$nrDocsRed["incluidos"]) {
                                $parecerDocumento_retorno = $mapperDocumento->parecerDocumento($dataMofaMoviFase, null, null, false);
                                if ($parecerDocumento_retorno !== true) {
                                    $DocNaoParecer[] = $dados_input;
                                } else if ($parecerDocumento_retorno === true) {
                                    $docsComParecer[] = $dados_input;
                                }
                            } else {
                                $parecerDocumento_retorno = $mapperDocumento->parecerDocumento($dataMofaMoviFase, $nrDocsRed, null, false);
                                if ($parecerDocumento_retorno !== true) {
                                    $DocNaoParecer[] = $dados_input;
                                } else if ($parecerDocumento_retorno === true) {
                                    $docsComParecer[] = $dados_input;
                                }
                            }
                        }
                        if (count($DocNaoParecer) > 0) {
                            $this->_helper->flashMessenger(array('message' => "A ação parecer foi cancelada para todos os documentos.", 'status' => 'notice'));
                            foreach ($DocNaoParecer as $docNaoPacercer) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível realizar o parecer para o documento nº {$docNaoPacercer['DOCM_NR_DOCUMENTO']}.", 'status' => 'error'));
                            }
                            $db->rollBack();
                        } else {
                            foreach ($docsComParecer as $docParecer) {
                                $this->_helper->flashMessenger(array('message' => "Parecer realizado com sucesso para o documento nº {$docParecer['DOCM_NR_DOCUMENTO']}.", 'status' => 'success'));
                            }
                            $db->commit();
                        }
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => $exc->getMessage(), 'status' => 'error'));
                        $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
                    }
                } else {
                    foreach ($nrDocsRed["existentes"] as $existentes) {
                        $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->form = $form;
                    $this->render('parecer');
                    return;
                }
                return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
            }
        }
    }

    public function irparacaixaAction() {
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade ();

        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $cdunidade = Zend_Filter::FilterStatic($this->_getParam('cdunidade'), 'int');
        $sigla = Zend_Filter::FilterStatic($this->_getParam('sigla'), 'alnum');


        if ($secao != $AcessoCaixaUnidade->getSgsecaoCaixaUnidade() && $cdunidade != $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade() && $sigla != $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade()
        ) {
            $AcessoCaixaUnidade->setSgsecaoCaixaUnidade($secao);
            $AcessoCaixaUnidade->setCdlotacaoCaixaUnidade($cdunidade);
            $AcessoCaixaUnidade->setSiglaLotacaoCaixaUnidade($sigla);
            /**
             * Para limpar o carrinho quando troca-se de caixa
             */
            $userNs = new Zend_Session_Namespace('userNs');
            $userNs->meucarrinho = array();
        }/* Dentro da mesma seção */ elseif ($secao == $AcessoCaixaUnidade->getSgsecaoCaixaUnidade() && $cdunidade != $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade() && $sigla != $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade()
        ) {
            $AcessoCaixaUnidade->setSgsecaoCaixaUnidade($secao);
            $AcessoCaixaUnidade->setCdlotacaoCaixaUnidade($cdunidade);
            $AcessoCaixaUnidade->setSiglaLotacaoCaixaUnidade($sigla);
            /**
             * Para limpar o carrinho quando troca-se de caixa
             */
            $userNs = new Zend_Session_Namespace('userNs');
            $userNs->meucarrinho = array();
        }
        $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
    }

    public function trocarcaixaAction() {
        /**
         * Forms
         */
        $FormPermissaoCaixa = new Sisad_Form_PermissaoCaixa();

        /**
         * Variáves de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');

        /**
         * Obtendo as caixas que os usuários tem acesso pela permissão PERF_ID_PERFIL = 9 RESPONSÁVEL PELA CAIXA DA UNIDADE
         */
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $dadosMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $CaixasUnidadeAcessoExtintas = $AcessoCaixaUnidade->getAcessoCaixaUnidadeExtinta($AcessoCaixaUnidade->getMatricula());
        $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidade($AcessoCaixaUnidade->getMatricula());
        /**
         * Retirar as caixas extintas que não tem documentos
         */
        $dadosUnidade = array();
        $dadosMovimentacaoExtinta = array();
        if (count($CaixasUnidadeAcessoExtintas) > 0) {
            $i=0;
            foreach ($CaixasUnidadeAcessoExtintas as $c) {
                if (!count($dadosMovimentacao->getCaixaUnidadeRecebidos($c["LOTA_COD_LOTACAO"], $c["LOTA_SIGLA_SECAO"], NULL))) {
                    $dadosMovimentacaoExtinta[$i] = $c["LOTA_COD_LOTACAO"].'|'.$c["LOTA_DSC_LOTACAO"].'|'.$c["LOTA_SIGLA_LOTACAO"].'|'.$c["LOTA_SIGLA_SECAO"].'|'.$c["PERF_ID_PERFIL"].'|'.$c["PERF_DS_PERFIL"];
                }
                $i++;
            }
            $y=0;
            foreach ($CaixasUnidadeAcesso as $u) {
                $dadosUnidade[$y] = $u["LOTA_COD_LOTACAO"].'|'.$u["LOTA_DSC_LOTACAO"].'|'.$u["LOTA_SIGLA_LOTACAO"].'|'.$u["LOTA_SIGLA_SECAO"].'|'.$u["PERF_ID_PERFIL"].'|'.$u["PERF_DS_PERFIL"];
                $y++;
            }
        }
        /**
         * Configurando as option do form
         */
        $unidade = $FormPermissaoCaixa->getElement('UNIDADE');
        //$unidade->addMultiOptions(array('--' => '--'));
        foreach ($CaixasUnidadeAcesso as $CaixaUnidade) {
            if (!in_array($CaixaUnidade["LOTA_COD_LOTACAO"].'|'.$CaixaUnidade["LOTA_DSC_LOTACAO"].'|'.$CaixaUnidade["LOTA_SIGLA_LOTACAO"].'|'.$CaixaUnidade["LOTA_SIGLA_SECAO"].'|'.$CaixaUnidade["PERF_ID_PERFIL"].'|'.$CaixaUnidade["PERF_DS_PERFIL"],array_intersect($dadosUnidade, $dadosMovimentacaoExtinta))) {
            $unidade->addMultiOptions(array(Zend_Json::encode($CaixaUnidade) => $CaixaUnidade["LOTA_SIGLA_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_DSC_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_COD_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_SIGLA_SECAO"]));
            }
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            $arr_unidade = Zend_Json::decode($data[UNIDADE]);
            $siglasecao = $arr_unidade[LOTA_SIGLA_SECAO];
            $codlotacao = $arr_unidade[LOTA_COD_LOTACAO];
            $siglalotacao = $arr_unidade[LOTA_SIGLA_LOTACAO];
            $desclotacao = $arr_unidade[LOTA_DSC_LOTACAO];

            if ($FormPermissaoCaixa->isValid($data)) {
                /**
                 * Setando os valores na sessão a caixa escolhida 
                 */
                $AcessoCaixaUnidade->setSgsecaoCaixaUnidade($siglasecao);
                $AcessoCaixaUnidade->setCdlotacaoCaixaUnidade($codlotacao);
                $AcessoCaixaUnidade->setSiglaLotacaoCaixaUnidade($siglalotacao);
                $AcessoCaixaUnidade->setDescLotacaoCaixaUnidade($desclotacao);

                $FormPermissaoCaixa->populate($data);
                /**
                 * Para limpar o carrinho quando troca-se de caixa
                 */
                $userNs = new Zend_Session_Namespace('userNs');
                $userNs->meucarrinho = array();
            } else {
                $FormPermissaoCaixa->populate($data);
            }
        } else {
            $SgsecaoCaixaUnidade = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
            $CdlotacaoCaixaUnidade = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();

            foreach ($CaixasUnidadeAcesso as $CaixaUnidade) {
                if ($CaixaUnidade["LOTA_SIGLA_SECAO"] == $SgsecaoCaixaUnidade &&
                        $CaixaUnidade["LOTA_COD_LOTACAO"] == $CdlotacaoCaixaUnidade) {

                    $dataFormPermissaoCaixaPopulate['UNIDADE'] = Zend_Json::encode($CaixaUnidade);
                    $FormPermissaoCaixa->populate($dataFormPermissaoCaixaPopulate);
                }
            }
        }

        $this->view->form = $FormPermissaoCaixa;
        $this->view->title = "Trocar Caixa" . ' - ' . $userNs->nome ;
    }

    public function despachoAction() {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);
        $despachodetalhe = $this->_getParam('despachodetalhe', '');

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidade_despacho = new Zend_Session_Namespace('Ns_Caixaunidade_despacho');

        $form = new Sisad_Form_Despacho();
        $this->view->formDespacho = $form;

        if ($this->getRequest()->isPost() || $despachodetalhe == "S") {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues());

            if ($anexos->getFileName()) {
                try {
                    $upload = new App_Multiupload_NewMultiUpload();
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                    $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                }
            }

            if (isset($data['acao']) && $data['acao'] == 'Despacho' || !is_null($Ns_Caixaunidade_despacho->data_post_caixa)) {
                if ($despachodetalhe == "S") {
                    $data_post_caixa['documento'] = array($Ns_Caixaunidade_despacho->data_post_caixa['documento']);
                    $data['acao'] = 'Despacho';
                } else {
                    $service_juntada = new Services_Sisad_Juntada();
                    $data['documento'] = $service_juntada->completaComApensados($data['documento']);

                    $data_post_caixa = $data;
                }

                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /* Ordenação das paginas */
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column . ' ' . $order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                /* Ordenação */

                if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixaunidade' && isset($data_post_caixa['action']) &&
                        $data_post_caixa['action'] == 'processosdaunidade' || $data_post_caixa['action'] == 'entrada' ||
                        $data_post_caixa['action'] == 'documentosdaunidade' || $despachodetalhe == "S") {
                    $cont = 0;
                    $rows = array();
                    $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();

                    foreach ($data_post_caixa["documento"] as $value) {
                        $doc = Zend_Json::decode($value);
                        $cont++;

                        if ($doc['DTPD_NO_TIPO'] == "Processo administrativo") {
                            $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
                            $DocumentosProcesso = $SadTbPrdiProcessoDigital->getdocsProcesso($doc['DOCM_ID_DOCUMENTO']);
                            $temVista = $SadTbPapdParteProcDoc->verificaParteVista(null, $DocumentosProcesso[0]['ID_PROCESSO'], 3); //3 = Tem vista
                        } else {
                            $temVista = $SadTbPapdParteProcDoc->verificaParteVista($doc['DOCM_ID_DOCUMENTO'], null, 3); //3 = Tem vista
                        }

                        $sigiloso = 'N';

                        /* Confidencialidade:
                         * 1 - Restrito as partes
                         * 3 - As partes segredo de justiça
                         * 4 - Ao subgrupo sigiloso
                         * 5 - Corregedoria
                         */
                        if (in_array($doc['DOCM_ID_CONFIDENCIALIDADE'], array("1", "3", "4", "5"))) {
                            $sigiloso = 'S';

                            if ($doc['DOCM_ID_CONFIDENCIALIDADE'] == "5") {
                                $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                                if (defined('APPLICATION_ENV')) {
                                    if (APPLICATION_ENV == 'development') {
                                        $UsuarioCorregedoria = $OcsTbPupePerfilUnidPessoa->getPossuiPerfil(36, $aNamespace->matricula); //DSV
                                    } else if (APPLICATION_ENV == 'production') {
                                        $UsuarioCorregedoria = $OcsTbPupePerfilUnidPessoa->getPossuiPerfil(38, $aNamespace->matricula); //PRD
                                    }
                                }
                            }

                            if (($temVista) || (!empty($UsuarioCorregedoria))) {
                                $sigiloso = 'N';
                            } else {
                                $sigiloso = 'S';
                            }
                        }

                        if ($sigiloso == 'N') {
                            $rows['documento'][$cont] = $doc;
                        }
                    }

                    if ($rows['documento']) {
                        /**
                         * Recuperando a unidade da caixa
                         */
                        $documentos = array();
                        $i = 0;
                        foreach ($data_post_caixa['documento'] as $value) {
                            $dados_input = Zend_Json::decode($value);
                            $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                            $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                            $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
                            $i++;
                        }

                        $paginator = Zend_Paginator::factory($rows['documento']);
                        $paginator->setCurrentPageNumber($page)
                                ->setItemCountPerPage(count($rows['documento']));

                        $this->view->ordem = $order_column;
                        $this->view->direcao = $order_direction;
                        $this->view->data = $paginator;
                        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
                        $this->view->title = "Despacho em Documento(s) / Processo(s) - $siglalotacao";
                        $data_post_caixa['documento'] = $rows['documento'];
                        $Ns_Caixaunidade_despacho->data_post_caixa['documento'] = $data_post_caixa['documento'];
                    } else {
                        $this->_helper->flashMessenger(array('message' => 'Usuário não tem vistas em nenhum documento. Favor escolher um documento no qual tem vistas.', 'status' => 'notice'));
                        return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
                    }
                }
            }
            if (isset($data['acao']) && $data['acao'] == 'submitDespacho') {
                $data_post_caixa = $Ns_Caixaunidade_despacho->data_post_caixa;

                if ($despachodetalhe == "S") {
                    $data_post_caixa['controller'] = $Ns_Caixaunidade_despacho->data_post_caixa['controller'];
                    $data_post_caixa['action'] = $Ns_Caixaunidade_despacho->data_post_caixa['action'];
                }

                if (!$form->isValid($data)) {
                    unset($data['MODE_SG_SECAO_UNID_DESTINO']);
                    unset($data['MODE_CD_SECAO_UNID_DESTINO']);
                    $this->view->formDespacho = $form;
                    return;
                }
                if ($nrDocsRed["erro"]) {
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('despacho');
                    return;
                }
                if (!$nrDocsRed["existentes"]) {
                    foreach ($data_post_caixa['documento'] as $value) {


                        $idDocmDocumento = $value['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $value['DOCM_NR_DOCUMENTO'];

                        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $value['MOFA_ID_MOVIMENTACAO'];
                        $dataMofaMoviFase["MOFA_ID_FASE"] = 1040; /* DESPACHO SISAD */
                        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                        $nrDocsRed["ID_DOCUMENTO"] = $idDocmDocumento;
                        $nrDocsRed["ID_MOVIMENTACAO"] = $value['MOFA_ID_MOVIMENTACAO'];

                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();

                        if (!$nrDocsRed["incluidos"]) {
                            try {
                                $despachoDocumento_retorno = $mapperDocumento->despachoDocumento($dataMofaMoviFase);

                                $this->_helper->flashMessenger(array('message' => "Despacho do documento nº $nrDocmDocumento salvo", 'status' => 'success'));
                            } catch (Exception $exc) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível salvar o despacho do documento nº $nrDocmDocumento", 'status' => 'error'));
                                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                            }
                        } else {
                            try {
                                $despachoDocumento_retorno = $mapperDocumento->despachoDocumento($dataMofaMoviFase, $nrDocsRed);

                                $this->_helper->flashMessenger(array('message' => "Despacho do documento nº $nrDocmDocumento salvo", 'status' => 'success'));
                            } catch (Exception $exc) {
                                $this->_helper->flashMessenger(array('message' => "Não foi possível salvar o despacho do documento nº $nrDocmDocumento", 'status' => 'error'));
                                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                            }
                        }
                    }
                } else {
                    foreach ($nrDocsRed["existentes"] as $existentes) {
                        $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->form = $form;
                    $this->render('despacho');
                    return;
                }
                return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
            }
        }
    }

    public function tratafiltrodascaixas($filtro) {
        
        $pesquisa = '';

        if (($filtro['DOCM_ID_PCTT'] != '')) {
            $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
            $pctt = $mapperPctt->getPCTTAjax($filtro['DOCM_ID_PCTT']);
            $filtro['DOCM_ID_PCTT'] = $pctt[0]['AQVP_ID_PCTT'];
        }
        if ($filtro['DOCM_NR_DOCUMENTO'] != '') {
            $docm_nr_documento = $filtro['DOCM_NR_DOCUMENTO'];
            $pesquisa .= (strlen(trim($docm_nr_documento)) == 28) ? ("AND DOCM_NR_DOCUMENTO = $docm_nr_documento") :
                    ("AND TO_NUMBER(SUBSTR(DOCM_NR_DOCUMENTO,-6,6)) = TO_NUMBER(SUBSTR($docm_nr_documento,5))
                           AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = SUBSTR($docm_nr_documento,0,4)");
        }
        if ($filtro['MOVI_CD_SECAO_UNID_ORIGEM'] != '') {
            $pesquisa .= " AND MOVI.MOVI_CD_SECAO_UNID_ORIGEM = " . $filtro['MOVI_CD_SECAO_UNID_ORIGEM'];
        }
        if ($filtro['DOCM_ID_TIPO_DOC'] != '') {
            $pesquisa .= " AND DOCM.DOCM_ID_TIPO_DOC = " . $filtro['DOCM_ID_TIPO_DOC'];
        }
        if ($filtro['DOCM_ID_PCTT'] != '') {
            $pesquisa .= " AND DOCM.DOCM_ID_PCTT = " . $filtro['DOCM_ID_PCTT'];
        }
        
        #ADICIONADO AO CODIGO EM 20/06 -- INICIO
        if ($filtro['DATA_INICIAL'] != '' && $filtro['DATA_FINAL'] != ''){
            $pesquisa .= "AND DOCM.DOCM_DH_CADASTRO between TO_DATE('".$filtro['DATA_INICIAL']."', 'DD/MM/YYYY') AND TO_DATE('".$filtro['DATA_FINAL']."', 'DD/MM/YYYY')+1-1/24/60/60" ;
        }
        if ($filtro['DATA_INICIAL'] == '' && $filtro['DATA_FINAL']!=''){
            $pesquisa .= " AND DOCM_DH_CADASTRO <= TO_DATE('".$filtro['DATA_FINAL']."', 'DD/MM/YYYY')";
        }
        if ($filtro['DATA_INICIAL'] != '' && $filtro['DATA_FINAL'] == ''){
            $pesquisa .= "AND DOCM_DH_CADASTRO >= TO_DATE('".$filtro['DATA_INICIAL']."', 'DD/MM/YYYY')";
        }
        #FIM -- 
        
        if ($filtro['DOCM_DS_PALAVRA_CHAVE'] != '') {
            $pesquisa .= " AND (DOCM.DOCM_DS_PALAVRA_CHAVE LIKE '%" . $filtro['DOCM_DS_PALAVRA_CHAVE'] . "%' 
                           OR  DOCM.DOCM_DS_ASSUNTO_DOC LIKE '%" . $filtro['DOCM_DS_PALAVRA_CHAVE'] . "%' )";
        }
        if (count($filtro['CATE_ID_CATEGORIA']) != 0) {
            $categoria = $filtro['CATE_ID_CATEGORIA'];
            $categoria = implode(',', $categoria);
            $pesquisa .= " AND CADO.CADO_ID_CATEGORIA IN ($categoria) AND CADO.CADO_DH_INATIVACAO_CATEGORIA IS NULL";
        }
        if ($filtro['PAPD_CD_MATRICULA_INTERESSADO'] != '') {
            $parte = $filtro['PAPD_CD_MATRICULA_INTERESSADO'];
            $pesquisa .= " AND   DOCM.DOCM_ID_DOCUMENTO IN (
                                                    SELECT DISTINCT PAPD_ID_DOCUMENTO
                                                    FROM SAD_TB_PAPD_PARTE_PROC_DOC
                                                    WHERE PAPD_DH_EXCLUSAO    IS NULL 
                                                    AND PAPD_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_CD_MATRICULA_INTERESSADO = '$parte'
                                                    
                                                    UNION 
                                                    
                                                    SELECT DISTINCT DOCM_ID_DOCUMENTO
                                                    FROM   SAD_TB_DOCM_DOCUMENTO DOCM
                                                    INNER JOIN SAD_TB_DCPR_DOCUMENTO_PROCESSO DCPR
                                                    ON     DCPR.DCPR_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                    INNER JOIN  SAD_TB_PAPD_PARTE_PROC_DOC PAPD
                                                    ON PAPD_ID_PROCESSO_DIGITAL = DCPR_ID_PROCESSO_DIGITAL
                                                    WHERE  DOCM.DOCM_ID_TIPO_DOC = 152
                                                    AND DOCM_ID_DOCUMENTO IS NOT NULL
                                                    AND PAPD_DH_EXCLUSAO     IS NULL 
                                                    AND PAPD_CD_MATRICULA_INTERESSADO =  '$parte'
                                                    
) ";
        }
        return $pesquisa;
    }

    public function anexoerroAction() {
        $form = new Sosti_Form_Anexo();
        $form->anexoUnico();
        $this->view->form = $form;

        $data = $this->getRequest()->getPost();
        $dados = explode('-', $data["identificacao"]);

        $anex["ANEX_ID_DOCUMENTO"] = $dados[0];
        $anex["ANEX_DH_FASE"] = $dados[1];
        $anex["ANEX_ID_MOVIMENTACAO"] = $dados[2];

        if ($this->getRequest()->isPost()) {
            $form->ANEXOS->receive();
            $nrDocsRed = null;
            if ($form->ANEXOS->isReceived()) {
                try {
                    $upload = new App_Multiupload_NewMultiUpload();
                    $nrDocsRed = $upload->incluirarquivos($form->ANEXOS);

                    $msg_to_user = "Anexos inserido no documento";

                    $anex["ANEX_NR_DOCUMENTO_INTERNO"] = $nrDocsRed["existentes"][0]["ID_DOCUMENTO"];
                    $anex["ANEX_ID_TP_EXTENSAO"] = $nrDocsRed["existentes"][0]["ANEX_ID_TP_EXTENSAO"];
                    $anex["ANEX_NM_ANEXO"] = $nrDocsRed["existentes"][0]["NOME"];

                    $anexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                    $anexAnexo->setAnexoErro($anex);

                    $msg_to_user = 'Anexo substituido com sucesso!';
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                } catch (Exception $exc) {
                    $msg_to_user = "Não foi possível subistituir anexo";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                }
            }
        }
        $this->_helper->_redirector($data["caixa"], $data["controller"], 'sosti');
    }

}
