<?php

class Soseg_SolicitacaoservicosgraficosController extends Zend_Controller_Action {
    
    private $_negocio = null;
    
    public function init() {
        
	$this->view->titleBrowser = 'e-Soseg - Sistema de Atendimento aos Serviços Editoriais e Gráficos';
        $this->_negocio = new Trf1_Soseg_Negocio_SolicitacaoServicosGraficos();
    }

    public function caixadiediAction() {
        
        $caixa = Zend_Filter::filterStatic($this->_getParam('caixa', 'diedi'), 'alpha');
        $idCaixa = Zend_Filter::filterStatic($this->_getParam('id', Trf1_Soseg_Definicoes::CAIXA_ATENDIMENTO_SERVICO_DIEDI), 'int');
        $idGrupoServico = Zend_Filter::filterStatic($this->_getParam('idgrupo', Trf1_Soseg_Definicoes::ID_GRUPO_SERV_DIEDI), 'int');
        $action = $this->getRequest()->getActionName();
        
        $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
        $nsIndex->caixa = $caixa;
        $nsIndex->idCaixa = $idCaixa;
        $nsIndex->idGrupo = $idGrupoServico;
        $nsIndex->actioncaixa = $action;
        
        
        $ns = 'ns_' . md5($this->getRequest ()->getControllerName().$this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array ('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 15, 'page' => 1 );
	$varSessoes = new App_SecaoPaginator ($ns, $variaveisSessaoPadrao);
                
        $form = new Soseg_Form_CaixaSolicitacoes();
       
        $form->removeElement('SGRS_ID_GRUPO');
        $form->removeElement('SOMENTE_PRINCIPAL');
        $form->removeElement('DATA_INICIAL');
        $form->removeElement('DATA_FINAL');
        $form->removeElement('SSER_DS_SERVICO');
        $form->removeElement('SERVICO-partenome');
        $form->removeElement('MOFA_ID_FASE');
        $form_valores_padrao = $form->getValues();
        
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->getServicoPorGrupo($idGrupoServico, 'SSER_DS_SERVICO ASC');
        $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
        $sser_id_servico->addMultiOptions(array('' => ''));
        foreach ($SserServico as $SserServico_p){
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        }
        
        $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_ID_GRUPO = $idGrupoServico", 2);
        $Categorias = $Categorias->toArray();
        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cores = array();
        if($Categorias){
            for ( $i = 0; $i < count($Categorias) ; $i++  ) {
                $cores[$i] = $Categorias[$i]["CATE_DS_DESCRICAO_COR"];
                $cate_id_categoria->addMultiOptions(array($Categorias[$i]["CATE_ID_CATEGORIA"] => $Categorias[$i]["CATE_NO_CATEGORIA"]));
            }
            $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
            $this->view->categorias = $Categorias;
        }
        
        $CateNs = new Zend_Session_Namespace('CateNs');
        $CateNs->tipo = 1;
        $CateNs->identificador = $idCaixa;
        $CateNs->idGrupo = $idGrupoServico;
        $CateNs->controller = 'solicitacaoservicosgraficos';
        $CateNs->action = $nsIndex->actioncaixa;
        
        
        if($this->_getParam('nova')=== '1'){
                unset($nsIndex->data_pesq);
                $this->_helper->_redirector( $nsIndex->actioncaixa, 'solicitacaoservicosgraficos', 'soseg');
        }

        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            
            $form->populate($data_pesq);
            
            if($form_valores_padrao == $form->getValues() ){
               
                $this->view->form = $form;
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                $this->_helper->_redirector( $nsIndex->actioncaixa , 'solicitacaoservicosgraficos', 'soseg');
            }
            
            if($form->isValid($data_pesq)){
               $chaves_nulas = array_flip(array_keys($form->getValues()));
                foreach ($chaves_nulas as $key => $value) {
                    $chaves_nulas[$key]= null;
                }
                $data_pesq = array_merge($chaves_nulas, $this->getRequest()->getPost());
                $nsIndex->data_pesq = $data_pesq;
            }else{
                /**
                 * Populando o formulário inválido
                 */
                $form->populate($data_pesq);
                $this->view->form = $form;
                return;
            }
        }
        
        $data_pesq = $nsIndex->data_pesq;
        $post_data_pesq = $data_pesq;
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        
       
        $params = array ( "idCaixa" =>  $idCaixa,
                         "tipoDoc" =>  Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO
        );
        
        if(! is_null($data_pesq) ){
           
            $this->view->ultima_pesq = true;
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
            
            /**
             * Tratamento dos dados para passar na array params
             */
            $mat_aux = explode(' - ', $data_pesq["DOCM_CD_MATRICULA_CADASTRO_VALUE"]);
            $data_pesq["DOCM_CD_MATRICULA_CADASTRO"] = $mat_aux[0];
            
            $mat_atend_aux = explode(' - ', $data_pesq["SSOL_CD_MATRICULA_ATENDENTE_VALUE"]);
            $data_pesq["SSOL_CD_MATRICULA_ATENDENTE"] = $mat_atend_aux[0];
            
            
            $unid_aux = explode(' - ', $data_pesq["DOCM_CD_LOTACAO_GERADORA_VALUE"]);
            ( array_key_exists(2,$unid_aux) ) ? ($data_pesq["DOCM_CD_LOTACAO_GERADORA"] = $unid_aux[2]) : '';
            ( array_key_exists(3,$unid_aux) ) ? ($data_pesq["DOCM_SG_SECAO_GERADORA"] = $unid_aux[3]) : '';
            
            $params['data_pesq'] = $data_pesq;
            //$rows = $dados->getCaixaSemNivelPesq($idCaixa, $data_pesq, $order, Trf1_Sosti_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO );
            $rows = $dados->getCaixaSolicitacoesServicosGraficos( $params );
            
            $form->populate($post_data_pesq);
        }else{
            /**
             * Caso não exista filtro executa a listagem normal sem filtro
             */
            
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
            $rows = $dados->getCaixaSolicitacoesServicosGraficos( $params, $order );
        }
       
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage($itemCountPerPage);
        
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        
        $this->view->caixa = array( 'caixa' => $caixa, 
                                    'idCaixa' => $idCaixa, 
                                    'idGrupoServico' => $idGrupoServico,
                                    'tipoSolicitacao' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO
                                );
        
        $this->view->form = $form;
        $this->view->title = "CAIXA DE ATENDIMENTO - DIEDI";
    }
    
    public function caixadigraAction() {
        
        $caixa = Zend_Filter::filterStatic($this->_getParam('caixa', 'digra'), 'alpha');
        $idCaixa = Zend_Filter::filterStatic($this->_getParam('id', Trf1_Soseg_Definicoes::CAIXA_ATENDIMENTO_SERVICO_DIGRA), 'int');
        $idGrupoServico = Zend_Filter::filterStatic($this->_getParam('idgrupo', Trf1_Soseg_Definicoes::ID_GRUPO_SERV_DIGRA), 'int');
        $action = $this->getRequest()->getActionName();
        
        $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
        $nsIndex->caixa = $caixa;
        $nsIndex->idCaixa = $idCaixa;
        $nsIndex->idGrupo = $idGrupoServico;
        $nsIndex->actioncaixa = $action;
        
        
        $ns = 'ns_' . md5($this->getRequest ()->getControllerName().$this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array ('direcao' => 'DESC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 15, 'page' => 1 );
	$varSessoes = new App_SecaoPaginator ($ns, $variaveisSessaoPadrao);
                
        $form = new Soseg_Form_CaixaSolicitacoes();
       
        $form->removeElement('SGRS_ID_GRUPO');
        $form->removeElement('SOMENTE_PRINCIPAL');
        $form->removeElement('DATA_INICIAL');
        $form->removeElement('DATA_FINAL');
        $form->removeElement('SSER_DS_SERVICO');
        $form->removeElement('SERVICO-partenome');
        $form->removeElement('MOFA_ID_FASE');
        $form_valores_padrao = $form->getValues();
        
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->getServicoPorGrupo($idGrupoServico, 'SSER_DS_SERVICO ASC');
        $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
        $sser_id_servico->addMultiOptions(array('' => ''));
        foreach ($SserServico as $SserServico_p){
            $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
        }
        
        $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        $Categorias = $cateCategoria->fetchAll("CATE_ID_GRUPO = $idGrupoServico", 2);
        $Categorias = $Categorias->toArray();
        $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
        $cores = array();
        if($Categorias){
            for ( $i = 0; $i < count($Categorias) ; $i++  ) {
                $cores[$i] = $Categorias[$i]["CATE_DS_DESCRICAO_COR"];
                $cate_id_categoria->addMultiOptions(array($Categorias[$i]["CATE_ID_CATEGORIA"] => $Categorias[$i]["CATE_NO_CATEGORIA"]));
            }
            $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
            $this->view->categorias = $Categorias;
        }
        
        $CateNs = new Zend_Session_Namespace('CateNs');
        $CateNs->tipo = 1;
        $CateNs->identificador = $idCaixa;
        $CateNs->idGrupo = $idGrupoServico;
        $CateNs->controller = 'solicitacaoservicosgraficos';
        $CateNs->action = $nsIndex->actioncaixa;
        
        
        if($this->_getParam('nova')=== '1'){
                unset($nsIndex->data_pesq);
                $this->_helper->_redirector( $nsIndex->actioncaixa, 'solicitacaoservicosgraficos', 'soseg');
        }

        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            
            $form->populate($data_pesq);
            
            if($form_valores_padrao == $form->getValues() ){
               
                $this->view->form = $form;
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                $this->_helper->_redirector( $nsIndex->actioncaixa , 'solicitacaoservicosgraficos', 'soseg');
            }
            
            if($form->isValid($data_pesq)){
               $chaves_nulas = array_flip(array_keys($form->getValues()));
                foreach ($chaves_nulas as $key => $value) {
                    $chaves_nulas[$key]= null;
                }
                $data_pesq = array_merge($chaves_nulas, $this->getRequest()->getPost());
                $nsIndex->data_pesq = $data_pesq;
            }else{
                /**
                 * Populando o formulário inválido
                 */
                $form->populate($data_pesq);
                $this->view->form = $form;
                return;
            }
        }
        
        $data_pesq = $nsIndex->data_pesq;
        $post_data_pesq = $data_pesq;
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        
       
        $params = array ( "idCaixa" =>  $idCaixa,
                         "tipoDoc" =>  Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO
        );
        
        if(! is_null($data_pesq) ){
           
            $this->view->ultima_pesq = true;
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
            
            /**
             * Tratamento dos dados para passar na array params
             */
            $mat_aux = explode(' - ', $data_pesq["DOCM_CD_MATRICULA_CADASTRO_VALUE"]);
            $data_pesq["DOCM_CD_MATRICULA_CADASTRO"] = $mat_aux[0];
            
            $mat_atend_aux = explode(' - ', $data_pesq["SSOL_CD_MATRICULA_ATENDENTE_VALUE"]);
            $data_pesq["SSOL_CD_MATRICULA_ATENDENTE"] = $mat_atend_aux[0];
            
            
            $unid_aux = explode(' - ', $data_pesq["DOCM_CD_LOTACAO_GERADORA_VALUE"]);
            ( array_key_exists(2,$unid_aux) ) ? ($data_pesq["DOCM_CD_LOTACAO_GERADORA"] = $unid_aux[2]) : '';
            ( array_key_exists(3,$unid_aux) ) ? ($data_pesq["DOCM_SG_SECAO_GERADORA"] = $unid_aux[3]) : '';
            
            $params['data_pesq'] = $data_pesq;
            $rows = $dados->getCaixaSolicitacoesServicosGraficos( $params );
            
            $form->populate($post_data_pesq);
        }else{
            /**
             * Caso não exista filtro executa a listagem normal sem filtro
             */
            
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
            $rows = $dados->getCaixaSolicitacoesServicosGraficos( $params, $order );
        }
       
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage($itemCountPerPage);
        
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        
        $this->view->caixa = array( 'caixa' => $caixa, 
                                    'idCaixa' => $idCaixa, 
                                    'idGrupoServico' => $idGrupoServico,
                                    'tipoSolicitacao' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO
                                );
        
        $this->view->form = $form;
        $this->view->title = "CAIXA DE ATENDIMENTO - DIGRA";
    }

    public function ajaxgruposervicoAction() {
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $lotacao = Zend_Filter::FilterStatic($this->_getParam('lotacao'), 'int');
        $tipolotacao = Zend_Filter::FilterStatic($this->_getParam('tipo'), 'int');
        $retiraCaixa1 = Zend_Filter::FilterStatic($this->_getParam('retiraCaixa1'), 'int');
        $retiraCaixa2 = Zend_Filter::FilterStatic($this->_getParam('retiraCaixa2'), 'int');

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $gruposServicosSecoes = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao($secao, $lotacao, $retiraCaixa1, $retiraCaixa2);
        $this->view->gruposServicosSecoes = $gruposServicosSecoes;
    }

    public function ajaxservicosAction() {
        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $id['SGRS_ID_GRUPO'] = Zend_Filter::FilterStatic($data['SGRS_ID_GRUPO'], 'int');
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServico_array = $SosTbSserServico->getServicoPorGrupo($id['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
            $this->view->servicos = $SosTbSserServico_array;
        }else{
            $id['SGRS_ID_GRUPO'] = $this->_getParam('grupoID');
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SosTbSserServico_array = $SosTbSserServico->getServicoPorGrupo($id['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
            $this->view->servicos = $SosTbSserServico_array;	
        }
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

    public function ajaxunidadeAction() {
        $unidade = $this->_getParam('term', '');
        $secao = $this->_getParam('secao', '');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        if ($secao == "") {
            $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade);
        } else {
            $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade, $secao);
        }

        $fim = count($lotacao);
        for ($i = 0; $i < $fim; $i++) {
            $lotacao[$i] = array_change_key_case($lotacao[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($lotacao);
    }

    public function ajaxpessoaunidadeAction() {
        
        $matricula = $this->_getParam('term', '');
        $aux = explode(' - ', $matricula);
        $matricula = $aux[0];

        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $dadosPessoais = $OcsTbPmatMatricula->getDadosPessoaisAjax($matricula);
        $ultimoTelefone = new Application_Model_DbTable_SadTbDocmDocumento();
        $fone = $ultimoTelefone->getUltimoTelefoneCadastrado($matricula);
        $dadosPessoais[0]['SSOL_NR_TELEFONE_EXTERNO'] = $fone;
        $this->_helper->json->sendJson($dadosPessoais);
    } 

    public function saveAction() {
        
        
        set_time_limit(1800);
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $this->view->title = "CADASTRO DE SOLICITAÇÃO DE SERVIÇOS EDITORIAIS/GRÁFICOS";
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Soseg_Form_SolicitacaoServicosGraficos();
        $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $sisad_anexo = new App_Sisad_Anexo(); 
        
       

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            //Zend_Debug::dump($data, 'data' );
            
            /*Adiciona o grupo escolhido no form*/
            $destino = Zend_Json::decode($data['SGRS_ID_GRUPO']);
            if ( !empty($destino)) {
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data['SGRS_ID_GRUPO'] => $destino["SGRS_DS_GRUPO"]));

                /*Serviços do grupo de serviço escolhido - para validação*/
                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $servicos = $SosTbSserServico->getServicoPorGrupo($destino['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($servicos as $servicos_p):
                    $sser_id_servico->addMultiOptions(array($servicos_p['SSER_ID_SERVICO'].'|'.$servicos_p['SSER_IC_TOMBO'].'|'.$servicos_p['SSER_IC_VIDEOCONFERENCIA']  => $servicos_p["SSER_DS_SERVICO"]));
                endforeach;
            }
                        

            if ($form->isValid($data)) {
                $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                
                unset($data["DOCM_NR_DOCUMENTO_RED"]);
                
                $anexos = new Zend_File_Transfer_Adapter_Http();
                $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
                
                
                //Zend_Debug::dump($anexos->getFileName(), 'anexos->getFileName() data isValid' ); 
                
                //se tiver carregado um anexo
                $unidade = explode(' - ', $data['UNIDADE']);

                $data['DOCM_SG_SECAO_REDATORA'] = $unidade[3];
                $data['DOCM_CD_LOTACAO_REDATORA'] = $unidade[0];

                $data['DOCM_SG_SECAO_GERADORA'] = $unidade[3];
                $data['DOCM_CD_LOTACAO_GERADORA'] = $unidade[0];
                
                $data["DOCM_NR_SEQUENCIAL_DOC"] = $SadTbDocmDocumento->getNumeroSequencialDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);
                $data["DOCM_NR_DOCUMENTO"] = $SadTbDocmDocumento->getNumeroDCMTO($data['DOCM_SG_SECAO_REDATORA'],$data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_CD_LOTACAO_GERADORA'], Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO, $data["DOCM_NR_SEQUENCIAL_DOC"]);
                $data["DOCM_ID_CONFIDENCIALIDADE"] = 0;
               
                
                
                if ($anexos->getFileName()) {
                    //Zend_Debug::dump($anexos->getFileName(), 'anexos->getFileName()');
                    try {
                       
                        $upload  = new App_Multiupload_Minuta($data);
                        //Zend_Debug::dump($upload, 'upload');
                        $nrDocsRed = $upload->incluirarquivos($anexos);
                        //Zend_Debug::dump($nrDocsRed['incluidos'], 'nrDocsRed incluidos');
                       //exit;
                        
                    }catch (Exception $exc) {
                        //Zend_Debug::dump($exc);
                        $msg_to_user = "Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                        $this->_helper->_redirector('form', 'solicitacaoservicosgraficos', 'soseg');
                    }
                    
                     
                    if( isset($nrDocsRed["erro"]) && $nrDocsRed["erro"]){
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                        $this->view->form = $form;
                        $this->render('form');
                        return;
                    }
                    if( isset($nrDocsRed["existentes"]) && !$nrDocsRed["existentes"]){
                        foreach($nrDocsRed["existentes"] as $existentes){
                            $msg_to_user = "Anexo ".$existentes['NOME']." pertence ao documento nr: ".$existentes['NR_DOCUMENTO'];
                            $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView = $msg_to_user;
                        }
                        $this->view->form = $form;
                        $this->render('form');
                        return;
                    }
                }
                
                    try {
                       // $dataRetorno = $ssolSolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed["incluidos"]);
                        $dataRetorno = $this->_negocio->cadastraSolicitacao($data, $nrDocsRed);
                        //Zend_Debug::dump($dataRetorno, 'data retorno');
                        $DocmDocumento = $ssolSolicitacao->getDadosSolicitacao($dataRetorno["DOCM_ID_DOCUMENTO"], null, Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);
                        //Zend_Debug::dump($DocmDocumento, 'DocmDocumento');
                        //exit;
                        $msg_to_user = "Solicitação de serviço nº: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " cadastrada!";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    } catch (Exception $exc) {
                        //Zend_Debug::dump($exc);
                        $msg_to_user = "Não foi possível cadastrar sua solicitação.";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                        $this->_helper->_redirector('form', 'solicitacaoservicosgraficos', 'soseg');
                    }
          //      }
                $sistema = 'e-Soseg - Sistema de Solicitações de Serviços Editoriais e Gráficos';
                $remetente = 'noreply@trf1.jus.br';
                $destinatario = $userNs->matricula.'@trf1.jus.br';
                $assunto = 'Cadastro de Solicitação';
                $corpo = "Cadastro de Solicitação efetuado com sucesso</p>
                          Número da Solicitação: " . $dataRetorno['DOCM_NR_DOCUMENTO'] . " <br/>
                          Data da Solicitação: " . $DocmDocumento["DOCM_DH_CADASTRO"] . " <br/>
                          Tipo de Serviço Solicitado: " . $DocmDocumento['SSER_DS_SERVICO'] . "<br/>
                          Descrição da Solicitação: " . nl2br($DocmDocumento["DOCM_DS_ASSUNTO_DOC"]) . "<br/>
                          Observação da Solicitação: " . nl2br($DocmDocumento["SSOL_DS_OBSERVACAO"]) . "<br/>";
                try {
                    $email = new Application_Model_DbTable_EnviaEmail();
                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger ( array('message' => 'Não foi possivel enviar email de confirmação para solicitação: '.$dataRetorno["DOCM_NR_DOCUMENTO"].'<br><b> Erro: </b> <p>'.strip_tags($exc->getMessage()).'<p>', 'status' => 'notice'));
                }
                
                $this->_helper->_redirector('form', 'solicitacaoservicosgraficos', 'soseg');
            } else {
             
                /* Faz a decodificação das entidades html dos campos de descrição*/
                $form->getElement('DOCM_DS_ASSUNTO_DOC')->removeFilter('HtmlEntities');
                if($form->getElement('DOCM_DS_ASSUNTO_DOC')->hasErrors()){
                    $form->getElement('DOCM_DS_ASSUNTO_DOC')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                }
                $form->getElement('SSOL_DS_OBSERVACAO')->removeFilter('HtmlEntities');
                if($form->getElement('SSOL_DS_OBSERVACAO')->hasErrors()){
                    $form->getElement('SSOL_DS_OBSERVACAO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                }
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }
   
    public function baixarAction()
    {  
        $form = new Soseg_Form_BaixarCaixa();
        $this->view->form = $form;
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_baixar');
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->getRequest()->getPost();
           // Zend_Debug::dump($data);
            $ids = implode(",", $data['solicitacao']);
            $params = array('ids' => $ids,
                            'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO
                          );
                          
            $solicitacoes = $this->_negocio->getDadosSolicitacao($params);

            if( count($solicitacoes) > 0 ){
                $ns->solicitacoes = $solicitacoes;
                $ns->ids = $ids;
                $ns->arrayIds = $data['solicitacao'];
                $this->view->data = $solicitacoes;
            }
                // Zend_Debug::dump($solicitacoes, 'solicitacoes');
            
            $this->view->title = $data['title']." - BAIXAR SOLICITAÇÃO(ÕES)";
            /*$ns->caixa = $data['caixa'];
            $ns->idCaixa = $data['idCaixa'];
            $ns->idGrupoServico = $data['idGrupoServico'];*/
        }
        
        
    }
    
    public function savebaixaAction()
    { 
        $form = new Soseg_Form_BaixarCaixa();
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_baixar');
        $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
        
        $form->removeElement('MOFA_ID_MOVIMENTACAO');
        $form->removeElement('DOCM_ID_DOCUMENTO');
        $form->removeElement('DOCM_NR_DOCUMENTO');
        $form->removeElement('DOCM_DS_HASH_RED');
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
        //$anexos->getErrors();
        //Zend_Debug::dump($anexos->getErrors(), 'anexos->getErrors()');
        //Zend_Debug::dump($anexos->getFileName(), 'anexos->getFileName() ' ); 
        //exit;
        
         if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            if($form->isValid($data)){
                $params = array( 'dados' => $data,
                                 'ids' => $ns->ids,
                                 'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO,
                                 'anexos' => $anexos);
                
                 $retorno = $this->_negocio->baixarSolicitacao($params);
                 
                if($retorno){
                     $msg_to_user = 'Solicitação(ões):'. $retorno .' baixada(s) com sucesso!';
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                }else{
                     $msg_to_user = 'Erro ao baixar a(s) solicitação(ões)!';
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                }
                 $this->_helper->_redirector( $nsIndex->actioncaixa, 'solicitacaoservicosgraficos', 'soseg');
            }else{
                //form nao valido, retorna para a pagina
                $params = array('ids' => $ns->ids,
                                'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO );
                $solicitacoes = $this->_negocio->getDadosSolicitacao($params);
                $this->view->data = $solicitacoes;
                $this->view->flashMessagesView = "<div class='error'> Por favor, corrija os erros do formulário. </div>";
                $form->populate($data);
                $this->view->form = $form;
                $this->render('parecer');
            }
               
        }
        
    }
    
    public function categoriaAction(){
        
    }
    
    public function cancelarAction() {
         
        $form = new Soseg_Form_Cancelar();
        $this->view->form = $form;
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_cancelar');
         
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->getRequest()->getPost();
            
            $ids = implode(",", $data['solicitacao']);
            $params = array('ids' => $ids,
                            'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO
                          );
                          
            $solicitacoes = $this->_negocio->getDadosSolicitacao($params);

            if( count($solicitacoes) > 0 ){
                $ns->solicitacoes = $solicitacoes;
                $ns->ids = $ids;
                $ns->arrayIds = $data['solicitacao'];
                $this->view->data = $solicitacoes;
            }
            
            $this->view->title = $data['title']." - CANCELAR SOLICITAÇÃO(ÕES)";
        
        }

    }

    public function savecancelarAction(){
        
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_cancelar');
        $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
         
         if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
           
                $params = array( 'dados' => $data,
                                 'ids' => $ns->ids,
                                 'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);
               
                $retorno = $this->_negocio->cancelarSolicitacao($params);
                 

                if($retorno){
                     $msg_to_user = "Solicitação(ões): ".$retorno." canceladas com sucesso!";
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                     
                }else{
                    $msg_to_user = "Não foi possível cancelar a(s) solicitação(ões): ".$retorno;
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                }
            
            
         }
         $this->_helper->_redirector( $nsIndex->actioncaixa, 'solicitacaoservicosgraficos', 'soseg');
         
        
        
    }    
    public function formAction() {
       
        $form = new Soseg_Form_SolicitacaoServicosGraficos();
        $this->view->form = $form;
        $this->view->title = "CADASTRO DE SOLICITAÇÕES DE SERVIÇOS EDITORIAIS/GRÁFICOS";
    }
    
    public function encaminharAction() {
        
        //$userNs = new Zend_Session_Namespace('userNs'); 
        $SadTbAtcxAtendenteCaixa = new Application_Model_DbTable_SadTbAtcxAtendenteCaixa();
        $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_encaminhar');
        $form = new Soseg_Form_EncaminhaSolicitacaoServico();
        
        //retira o grupo atual da lista de grupos de serviços
        $grupo_servico = $form->getElement('SGRS_ID_GRUPO');
        $arr_grupo_servico = $grupo_servico->getMultiOptions();
        $arr_grupo_servico = array_keys($arr_grupo_servico);
        foreach ($arr_grupo_servico as $value) {
        $value_option = Zend_Json::decode($value);
            if( in_array($value_option["SGRS_ID_GRUPO"], array($nsIndex->idGrupo))){
              $grupo_servico->removeMultiOption($value);
            }
        }
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //Zend_Debug::dump($data);
            $pessoas = $SadTbAtcxAtendenteCaixa->getPessoasCaixa($data['idCaixa']);
            $apsp_id_pessoa = $form->APSP_ID_PESSOA;
            foreach ($pessoas as $pessoas_p):
                $apsp_id_pessoa->addMultiOptions(array($pessoas_p["PMAT_CD_MATRICULA"] => $pessoas_p["ATENDENTE"] . ' - ' . $pessoas_p["PMAT_CD_MATRICULA"]));
            endforeach;
            
            $ids = implode(",", $data['solicitacao']);
            
            $params = array('ids' => $ids,
                            'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);
            
            $solicitacoes = $this->_negocio->getDadosSolicitacao($params);
            
            if( count($solicitacoes) > 0 ){
                $ns->solicitacoes = $solicitacoes;
                $ns->ids = $ids;
                $this->view->data = $solicitacoes;
            }
            
            $this->view->title = $data['title']." - ENCAMINHAR SOLICITAÇÃO(ÕES)";
        }
        $this->view->form = $form;
        
    }
    
    public function saveencaminhamentoAction() {
        
         $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_encaminhar');
         $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
         //$form = new Sosti_Form_EncaminhaSolicitacaoServico();
         
         $anexos = new Zend_File_Transfer_Adapter_Http();
         $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
         
         if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
       
                $params = array( 'dados' => $data,
                                 'ids' => $ns->ids,
                                 'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO,
                                 'anexos' => $anexos);
                
                
                if($data['ENCAMINHAMENTO'] == "grupo" ) {
                    $retorno = $this->_negocio->encaminhaSolicitacaoServGraficoGrupo($params);
                }else if($data['ENCAMINHAMENTO'] == "pessoa" ){
                    $retorno = $this->_negocio->encaminhaSolicitacaoServGraficoAtendente($params);
                }

                if($retorno){
                     $msg_to_user = 'Solicitação(ões) encaminhada(s) com sucesso: '.$retorno;
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                }else{
                     $msg_to_user = 'Erro ao encaminhar a(a) solicitação(ões).';
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                }
                
                $this->_helper->_redirector( $nsIndex->actioncaixa, 'solicitacaoservicosgraficos', 'soseg');
             
         }
        
    }
    
    public function parecerAction() {
        
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_parecer');
        $form = new Soseg_Form_ParecerSolicitacao();
        $this->view->form = $form;
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->getRequest()->getPost();

            $ids = implode(",", $data['solicitacao']);
            $params = array('ids' => $ids,
                            'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO
                          );

            $solicitacoes = $this->_negocio->getDadosSolicitacao($params);

            if( count($solicitacoes) > 0 ){
                $ns->solicitacoes = $solicitacoes;
                $ns->ids = $ids;
                $ns->arrayIds = $data['solicitacao'];
                $this->view->data = $solicitacoes;
            }
            
            $this->view->title = $data['title']." - DAR PARECER NA(S) SOLICITAÇÃO(ÕES)";
        }
         
    }
    
    public function saveparecerAction() {
        
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_parecer');
        $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
        $form = new Soseg_Form_ParecerSolicitacao();
        
        $form->removeElement('MOFA_ID_MOVIMENTACAO');
        $form->removeElement('DOCM_ID_DOCUMENTO');
        $form->removeElement('DOCM_NR_DOCUMENTO');
        $form->removeElement('DOCM_DS_HASH_RED');
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
        
         if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
           
            
            if($form->isValid($data)){
                $params = array( 'dados' => $data,
                                 'anexos' => $anexos);

                 $retorno = $this->_negocio->parecerSolicitacao($params);

                if($retorno){
                     $msg_to_user = 'Parecer cadastrado com sucesso!';
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                     $this->_helper->_redirector( $nsIndex->actioncaixa, 'solicitacaoservicosgraficos', 'soseg');
                }
            }else{
              
                $params = array('ids' => $ns->ids,
                                'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO );
                $solicitacoes = $this->_negocio->getDadosSolicitacao($params);
                $this->view->data = $solicitacoes;
                $this->view->flashMessagesView = "<div class='error'> Por favor, corrija os erros do formulário. </div>";
                $form->populate($data);
                $this->view->form = $form;
                $this->render('parecer');
            }
               
        }
            
    }

    public function solicitacaopdfAction() {
        
        $id = Zend_Filter::FilterStatic($this->_getParam('solic'), 'int');
        $params = array('ids' => $id,
                        'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);
        
        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();

        $DocmDocumento = $this->_negocio->getDadosSolicitacao($params);
        $this->view->DocmDocumento = $DocmDocumento[0];
        
        $DocmDocumentoHistorico = $SadTbDocmDocumento->getHistoricoDCMTO($id);
        $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico[0];
       
        $AnexAnexo = $SadTbAnexAnexo->fetchAll("ANEX_ID_DOCUMENTO = $id")->toArray();
        $this->view->AnexAnexo = $AnexAnexo;

        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();

        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));
        $mpdf = new mPDF();

        $mpdf->AddPage('P', '', '0', '1');
        $imagem_path = realpath(APPLICATION_PATH . '/../public/img/BrasaoBrancoRelatorio.jpg');
        $mpdf->Image($imagem_path, 94, 20, 23, 22, 'jpg', '', true, true, false, false, true);

        $mpdf->WriteHTML($body);

        $name = 'SISAD_DOC_SOLICITACAO_NUMERO_' . $DocmDocumento[0]['DOCM_NR_DOCUMENTO'] . '.pdf';

        $mpdf->Output($name, 'D');
    }

    public function solicitarinformacaoAction() {
        
      $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_solicitarinfo');
      $form = new Soseg_Form_Acoes();
      $form->getElement('MOFA_DS_COMPLEMENTO')->setLabel('Descrição da Informação:');
      
      if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
           // Zend_Debug::dump($data, 'data');
            
            $ids = implode(",", $data['solicitacao']);
            
            $params = array('ids' => $ids,
                            'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);
            
            $solicitacoes = $this->_negocio->getDadosSolicitacao($params);
            
            if( count($solicitacoes) > 0 ){
                $ns->solicitacoes = $solicitacoes;
                $ns->ids = $ids;
                $this->view->data = $solicitacoes;
            }
            
          $this->view->title = $data['title']." - SOLICITAR INFORMAÇÃO";
        }
        
        $this->view->form = $form;
    }
    
    public function savesolicitarinformacaoAction(){
        
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_solicitarinfo');
        $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
        $form = new Soseg_Form_Acoes();
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            if($form->isValid($data)){
                
                $params = array( 'dados' => $data,
                                 'anexos' => $anexos);
                 $retorno = $this->_negocio->solicitarInformacao($params);
               
                if($retorno){
                     $msg_to_user = 'Solicitação de Informação cadastrada com sucesso na(s) solicitação(ões): '.$retorno;
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                }else{
                    $msg_to_user = 'Erro ao solicitar informação na(s) solicitação(ões).';
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                }
                $this->_helper->_redirector( $nsIndex->actioncaixa, 'solicitacaoservicosgraficos', 'soseg', array('caixa' => $nsIndex->caixa ));
            }else{
                $params = array('ids' => $ns->ids,
                                'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO );
                $solicitacoes = $this->_negocio->getDadosSolicitacao($params);
                $this->view->data = $solicitacoes;
                $this->view->flashMessagesView = "<div class='error'> Por favor, corrija os erros do formulário. </div>";
                $form->populate($data);
                $this->view->form = $form;
                $this->render('solicitarinformacao');
            }
        }
    }

    public function trocarservicoAction() {
        
      
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_trocarservico');
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $form = new Soseg_Form_TrocarServico();
        
        //Zend_Debug::dump($ns->solicitacoes);
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            $ServicodoGrupo = $SosTbSserServico->fetchAll("SSER_ID_GRUPO = ".$data['idGrupoServico']." AND SSER_IC_ATIVO = 'S' AND SSER_IC_VISIVEL = 'S' ", 'SSER_DS_SERVICO')->toArray();
            $novoServico = $form->getElement('SSER_ID_SERVICO');
            //$novoServico->addMultiOptions(array('' => '::SELECIONE::'));
            foreach ($ServicodoGrupo as $d) {
                $novoServico->addMultiOptions(array($d["SSER_ID_SERVICO"] => $d["SSER_DS_SERVICO"]));
            }
           // Zend_Debug::dump($data, 'data');
            
            $ids = implode(",", $data['solicitacao']);
            
            $params = array('ids' => $ids,
                            'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);
            
            $solicitacoes = $this->_negocio->getDadosSolicitacao($params);
            
            if( count($solicitacoes) > 0 ){
                $ns->solicitacoes = $solicitacoes;
                $ns->ids = $ids;
                $this->view->data = $solicitacoes;
            }
            $this->view->title = $data['title']." - TROCAR SERVIÇO DA(S) SOLICITAÇÃO(ÕES)";
        }
        $this->view->form = $form;
    }
    
    public function savetrocaservicoAction(){
        
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_trocarservico');
        $nsIndex = new Zend_Session_Namespace('Ns_Solicitacaoservicosgraficos_index');
        //$SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $form = new Soseg_Form_TrocarServico();
        
        $form->removeElement('MOVI_ID_MOVIMENTACAO');
        $form->removeElement('SSOL_ID_DOCUMENTO');
        $form->removeElement('SSER_ID_SERVICO');
        $form->removeElement('SSOL_SG_TIPO_TOMBO');
        $form->removeElement('SSOL_NR_TOMBO');
        $form->removeElement('DE_MAT');
        $form->removeElement('SSES_DT_INICIO_VIDEO');
        $form->removeElement('DOCM_DS_HASH_RED');
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
        
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            if($form->isValid($data)){
                
                $params = array( 'dados' => $data,
                                 'anexos' => $anexos);
                
                 //Zend_Debug::dump($params, '$params');
                
                 $retorno = $this->_negocio->trocaServicoSolicitacao($params);
               
                if($retorno){
                     $msg_to_user = 'Serviço trocado com sucesso na(s) solicitação(ões): '.$retorno;
                     $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                     
                }else{
                    $msg_to_user = 'Erro ao trocar o serviço da(s) solicitação(ões).';
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                }
                $this->_helper->_redirector( $nsIndex->actioncaixa, 'solicitacaoservicosgraficos', 'soseg', array('caixa' => $nsIndex->caixa ));
            }else{
                
                $params = array('ids' => $ns->ids,
                                'tipo' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO );
                
                $solicitacoes = $this->_negocio->getDadosSolicitacao($params);
                $this->view->data = $solicitacoes;
                $this->view->flashMessagesView = "<div class='error'> Por favor, corrija os erros do formulário. </div>";
                $form->populate($data);
                $this->view->form = $form;
                $this->render('trocaservico');
            }
        }
    }
  
}