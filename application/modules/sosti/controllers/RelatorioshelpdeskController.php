<?php

class Sosti_RelatorioshelpdeskController extends Zend_Controller_Action
{
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
	
    public function init()
    {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
		$this->view->titleBrowser = 'e-Sosti';
    }

    public function solicitacoesperiodoAction()
    {
    	/*
        // Restricoes aplicadas conforme pedido nos sostis  2013010001155011550160000357 e 2013010001155011550160000349
    	$userNs = new Zend_Session_Namespace('userNs');
        $horaInicio = mktime(10,00,00);
        $horaFinal =  mktime(19,00,00);
        $horaAtual = mktime(date("H"), date("i"), date("s"));
        $msgUsuario = "Atenção, devido ao crescente uso do sistema, o que está causando uma sobrecarga no banco
                       de dados, a funcionalidade de emissão de relatórios de SLA somente estará disponível antes das 10:00 e após às 19:00.";
      
        if ( ($horaAtual <= $horaInicio || $horaAtual >= $horaFinal) || strcmp($userNs->matricula, 'TR300785') == 0 || strcmp($userNs->matricula, 'TR179603') || strcmp($userNs->matricula, 'TR18077PS') == 0 ){
        */
    	
    	// Validação de acesso às funcionalidades do SLA, conforme servidor web
    	$negocio = new Trf1_Sosti_Negocio_Sla ();
    	$permiteSla = $negocio->permiteSla ();
    	
    	if ($permiteSla ['permissao']) {
            $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoNs');
            $form = new Sosti_Form_RelatoriosHelpdesk();
            $form->removeElement('DATA_INICIAL_CADASTRO');
            $form->removeElement('DATA_FINAL_CADASTRO');
            $form->removeElement('DATA_INICIAL_ENCAMINHAMENTO');
            $form->removeElement('DATA_FINAL_ENCAMINHAMENTO');
            $this->view->form = $form;
            /*paginação*/
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $grupoJsonDecode = Zend_Json::decode($data["SGRS_ID_GRUPO"]);
                $data["DATA_INICIAL"] = $data["DATA_INICIAL"];
                $data["DATA_FINAL"] = $data["DATA_FINAL"];
                $aSlaPeriodoSpace->data = $data;
                $aSlaPeriodoSpace->grupo = $grupoJsonDecode["SGRS_ID_GRUPO"];
                $aSlaPeriodoSpace->nivel = $data["SNAT_CD_NIVEL"];
                $aSlaPeriodoSpace->data_inicial = $data["DATA_INICIAL"];
                $aSlaPeriodoSpace->data_final = $data["DATA_FINAL"];
                $aSlaPeriodoSpace->avaliacao = $data["AVALIACAO"];
                $aSlaPeriodoSpace->pesquisar = $data["Pesquisar"];
            }
            $grupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
            if ($aSlaPeriodoSpace->nivel) {
                $descricaoNivel = $grupo->getNivel($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel);
                $aSlaPeriodoSpace->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"].' - '.$descricaoNivel[0]["SNAT_SG_NIVEL"];
                $this->view->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"].' - '.$descricaoNivel[0]["SNAT_SG_NIVEL"];
            }
            if ($aSlaPeriodoSpace->grupo) {
                $aSlaPeriodoSpace->descricaoGrupo = $grupo->getGrupoAtendimento($aSlaPeriodoSpace->grupo);
                $this->view->descricaoGrupo = $grupo->getGrupoAtendimento($aSlaPeriodoSpace->grupo);
            }
            $this->view->title = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                                  ("SLA - Solicitações Finalizadas por Período: ".$aSlaPeriodoSpace->data_inicial." a ".$aSlaPeriodoSpace->data_final):
                                  ("SLA - Solicitações Finalizadas por Período: ".$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);

            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
            $order_direction = $this->_getParam('direcao', 'ASC');
            $aSlaPeriodoSpace->order = $order_column.' '.$order_direction;

            if ($aSlaPeriodoSpace->pesquisar != "") {
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                $itemsperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');

                ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');

                $dadosQtde = new Application_Model_DbTable_SosTbSsolSolicitacao();
                $qtde = $dadosQtde->getQtdeSolicitacoesPeriodoSla($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel, $aSlaPeriodoSpace->data_inicial,
                                                              $aSlaPeriodoSpace->data_final, $aSlaPeriodoSpace->order,$aSlaPeriodoSpace->avaliacao);
                $this->view->qtde = $qtde[0]['QTDE'];
                if ($qtde[0]['QTDE'] < 5000) {
                    /**
                     * Gera o cache
                     */      
                    $frontendOptions = array(
                        'lifetime' => 1800, // cache lifetime of 30 minutes
                        'automatic_serialization' => true
                    );
//                    $cache_dir = APPLICATION_PATH . '/../temp';
                    $cache_dir = sys_get_temp_dir();
                    $backendOptions = array(
                        'cache_dir' => $cache_dir 
                    );
                    // getting a Zend_Cache_Core object
                    $cache = Zend_Cache::factory('Core',
                                                 'File',
                                                 $frontendOptions,
                                                 $backendOptions);
                    $idCache = str_replace(':','_',str_replace(' ','',(str_replace('/','_','solicitacoesPeriodo'.$aSlaPeriodoSpace->grupo.$aSlaPeriodoSpace->nivel.$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final.$aSlaPeriodoSpace->avaliacao.$aSlaPeriodoSpace->grupo.$aSlaPeriodoSpace->nivel))));

                    if (($rows = $cache->load($idCache)) === false ) {
                        $rows = $dados->getSolicitacoesPeriodoSla($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel, $aSlaPeriodoSpace->data_inicial,
                                                                  $aSlaPeriodoSpace->data_final, $aSlaPeriodoSpace->order, $aSlaPeriodoSpace->avaliacao);
                        $cache->save($rows, $idCache);
                    }

                    $paginator = Zend_Paginator::factory($rows);
                    $paginator->setCurrentPageNumber($page)
                              ->setItemCountPerPage($itemsperpage);

                    $this->view->ordem = $order_column;
                    $this->view->direcao = $order_direction;
                    $this->view->data = $paginator;
                    $this->view->direcao_pdf = $aSlaPeriodoSpace->order;
                    Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
                }
            }
        }else{
            /*
        	$this->_helper->flashMessenger(array('message' => $msgUsuario, 'status' => 'notice'));
            $this->_helper->_redirector('index', 'index', 'admin');
    		*/
    		$this->_helper->flashMessenger ( array ('message' => $permiteSla ['mensagem'], 'status' => 'notice' ) );
    		$this->_helper->_redirector ( 'index', 'index', 'admin' );
        }
    }
    
    public function solicitacoesperiodopdfAction() 
    {
        ini_set("memory_limit","1024M");
        set_time_limit(1200);
        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoNs');
        $this->view->titulo = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                               ($aSlaPeriodoSpace->data_inicial." a ".$aSlaPeriodoSpace->data_final):
                               ($aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        /**
         * Gera o cache das solicitações atendidas por período
         */      
        $frontendOptions = array(
            'lifetime' => 1800, // cache lifetime of 30 minutes
            'automatic_serialization' => true
        );
        $cache_dir = APPLICATION_PATH . '/../temp';
        $backendOptions = array(
            'cache_dir' => $cache_dir 
        );
        // getting a Zend_Cache_Core object
        $cache = Zend_Cache::factory('Core',
                                     'File',
                                     $frontendOptions,
                                     $backendOptions);
        $idCache = str_replace(':','_',str_replace(' ','',(str_replace('/','_','solicitacoesPeriodo'.$aSlaPeriodoSpace->grupo.$aSlaPeriodoSpace->nivel.$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final.$aSlaPeriodoSpace->avaliacao.$aSlaPeriodoSpace->grupo.$aSlaPeriodoSpace->nivel))));

        if (($rows = $cache->load($idCache)) === false ) {
            $rows = $dados->getSolicitacoesPeriodoSla($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel, $aSlaPeriodoSpace->data_inicial,
                                                      $aSlaPeriodoSpace->data_final, $aSlaPeriodoSpace->order, $aSlaPeriodoSpace->avaliacao);
            $cache->save($rows, $idCache);
        }
       /**
        * Fim da geração do cache das solicitações atendidas por período
        */
        if ($aSlaPeriodoSpace->nivel) {
            $this->view->descricaoNivel = $aSlaPeriodoSpace->descricaoNivel;
        }
        $this->view->grupo = $aSlaPeriodoSpace->descricaoGrupo;
        $this->view->data = $rows;
        $this->view->horaAtual = $dados->dataHoraAtual();
        
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();

        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       10,    // margin top
                       10,    // margin bottom
                        9,    // margin header
                        9,    // margin footer
                       'L');

        $mpdf->AddPage('P', '', '0', '1');
        $mpdf->WriteHTML($body);
        
        $name =  'Finalizadas_periodo_'.str_replace('/', '_', $aSlaPeriodoSpace->data_inicial.'_'.$aSlaPeriodoSpace->data_final).'.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function solicitacoesperiodoexcelAction()
    {
        $this->_helper->layout->disableLayout(); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=extraction.xls"); 
        
        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoNs');
        $this->view->titulo = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                               ($aSlaPeriodoSpace->data_inicial." a ".$aSlaPeriodoSpace->data_final):
                               ($aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        /**
         * Gera o cache das solicitações atendidas por período
         */      
        $frontendOptions = array(
            'lifetime' => 1800, // cache lifetime of 30 minutes
            'automatic_serialization' => true
        );
        $cache_dir = APPLICATION_PATH . '/../temp';
        $backendOptions = array(
            'cache_dir' => $cache_dir 
        );
        // getting a Zend_Cache_Core object
        $cache = Zend_Cache::factory('Core',
                                     'File',
                                     $frontendOptions,
                                     $backendOptions);
        $idCache = str_replace(':','_',str_replace(' ','',(str_replace('/','_','solicitacoesPeriodo'.$aSlaPeriodoSpace->grupo.$aSlaPeriodoSpace->nivel.$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final.$aSlaPeriodoSpace->avaliacao.$aSlaPeriodoSpace->grupo.$aSlaPeriodoSpace->nivel))));

        if (($rows = $cache->load($idCache)) === false ) {
            $rows = $dados->getSolicitacoesPeriodoSla($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel, $aSlaPeriodoSpace->data_inicial,
                                                      $aSlaPeriodoSpace->data_final, $aSlaPeriodoSpace->order, $aSlaPeriodoSpace->avaliacao);
            $cache->save($rows, $idCache);
        }
       /**
        * Fim da geração do cache das solicitações atendidas por período
        */
        if ($aSlaPeriodoSpace->nivel) {
            $this->view->descricaoNivel = $aSlaPeriodoSpace->descricaoNivel;
        }
        $this->view->grupo = $aSlaPeriodoSpace->descricaoGrupo;
        $this->view->data = $rows;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->view->total = count($rows);
        $this->render();
    }
    
    public function minhassolicitacoesperiodoAction()
    {
        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoNs');
        $form = new Sosti_Form_RelatoriosHelpdesk();
        $form->removeElement('DATA_INICIAL_CADASTRO');
        $form->removeElement('DATA_FINAL_CADASTRO');
        $form->removeElement('DATA_INICIAL_ENCAMINHAMENTO');
        $form->removeElement('DATA_FINAL_ENCAMINHAMENTO');
        $this->view->form = $form;
        $form->removeElement(SGRS_ID_GRUPO);
        $form->removeElement(SNAT_CD_NIVEL);
        /*paginação*/
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $grupoJsonDecode = Zend_Json::decode($data["SGRS_ID_GRUPO"]);
            $aSlaPeriodoSpace->data = $data;
            $aSlaPeriodoSpace->grupo = $grupoJsonDecode["SGRS_ID_GRUPO"];
            $aSlaPeriodoSpace->nivel = $data["SNAT_CD_NIVEL"];
            $aSlaPeriodoSpace->data_inicial = $data["DATA_INICIAL"];
            $aSlaPeriodoSpace->data_final = $data["DATA_FINAL"];
            $aSlaPeriodoSpace->pesquisar = $data["Pesquisar"];
        }
        $grupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $aNamespace = new Zend_Session_Namespace('userNs');
        if ($aSlaPeriodoSpace->nivel) {
            $descricaoNivel = $grupo->getNivel($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel);
            $this->view->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"].' - '.$descricaoNivel[0]["SNAT_SG_NIVEL"];
        }
        if ($aSlaPeriodoSpace->grupo) {
            $this->view->descricaoGrupo = $grupo->getGrupoAtendimento($aSlaPeriodoSpace->grupo);
        }
        $this->view->title = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                              ("Minhas Solicitações Baixadas por Período: ".$aSlaPeriodoSpace->data_inicial." a ".$aSlaPeriodoSpace->data_final):
                              ("Minhas Solicitações Baixadas por Período: ".$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);
        $dadosQtde = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $qtde = $dadosQtde->getQtdeMinhasSolicitacoesPeriodoSla($aNamespace->matricula, $aSlaPeriodoSpace->data_inicial, $aSlaPeriodoSpace->data_final, $order);
        if ($aSlaPeriodoSpace->pesquisar != "") {
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
            $order_direction = $this->_getParam('direcao', 'ASC');
            $order = $order_column.' '.$order_direction;

            ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
            /*Ordenação*/
            $this->view->qtde = $qtde[0]['QTDE'];
            if ($qtde[0]['QTDE'] < 5000) {
                $rows = $dados->getMinhasSolicitacoesPeriodoSla($aNamespace->matricula, $aSlaPeriodoSpace->data_inicial, $aSlaPeriodoSpace->data_final, $order);
                /*verifica condições e faz tratamento nos dados*/
                $TimeInterval = new App_TimeInterval();
                $fim =  count($rows);
                for ($i = 0; $i<$fim; $i++ ) {
                    $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotal($rows[$i]['DOCM_DH_CADASTRO'], $rows[$i]['MOFA_DH_FASE']);
                    $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
                }

                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                          ->setItemCountPerPage(15);

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                $this->view->direcao_pdf = $order;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }
        }
    }
    
    public function minhassolicitacoesperiodopdfAction() 
    {
        $aNamespace = new Zend_Session_Namespace('userNs');
        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPeriodoNs');
        $this->view->titulo = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                               ($aSlaPeriodoSpace->data_inicial." a ".$aSlaPeriodoSpace->data_final):
                               ($aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);
                     
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
         
        $rows = $dados->getMinhasSolicitacoesPeriodoSla($aNamespace->matricula, $aSlaPeriodoSpace->data_inicial, $aSlaPeriodoSpace->data_final, $this->_getParam('ordem'));
       
        $TimeInterval = new App_TimeInterval();
        $fim =  count($rows);
        for ($i = 0; $i<$fim; $i++ ) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->tempoTotal($rows[$i]['DOCM_DH_CADASTRO'], $rows[$i]['MOFA_DH_FASE']);
            $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
        }
        $grupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        if ($aSlaPeriodoSpace->nivel) {
            $descricaoNivel = $grupo->getNivel($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel);
            $this->view->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"].' - '.$descricaoNivel[0]["SNAT_SG_NIVEL"];
        }  
        $this->view->nome = $aNamespace->matricula." - ".$aNamespace->nome;
        $this->view->data = $rows;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->render();
       
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       10,    // margin top
                       10,    // margin bottom
                        9,    // margin header
                        9,    // margin footer
                       'L');
        $mpdf->AddPage('P', '', '0', '1');
        $mpdf->WriteHTML($body);
        $name =  'Finalizadas_periodo_'.str_replace('/', '_', $aSlaPeriodoSpace->data_inicial.'_'.$aSlaPeriodoSpace->data_final).'.pdf';
        $mpdf->Output($name,'D');
    }

    public function primeiroatendimentosolicitacoesAction() 
    {
        /* 
        // Restricoes aplicadas conforme pedido nos sostis  2013010001155011550160000357 e 2013010001155011550160000349
        $userNs = new Zend_Session_Namespace('userNs');
        $horaInicio = mktime(10,00,00);
        $horaFinal =  mktime(19,00,00);
        $horaAtual = mktime(date("H"), date("i"), date("s"));
        $msgUsuario = "Atenção, devido ao crescente uso do sistema, o que está causando uma sobrecarga no banco
                       de dados, a funcionalidade de emissão de relatórios de SLA somente estará disponível antes das 10:00 e após às 19:00.";
      
        if ( ($horaAtual <= $horaInicio || $horaAtual >= $horaFinal) || strcmp($userNs->matricula, 'TR300785') == 0 || strcmp($userNs->matricula, 'TR179603') == 0 ){
        */
    	
    	// Validação de acesso às funcionalidades do SLA, conforme servidor web
    	$negocio = new Trf1_Sosti_Negocio_Sla ();
    	$permiteSla = $negocio->permiteSla ();
    	
    	if ($permiteSla ['permissao']) {
                $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPrimeiroAtendimentoNs');
                $form = new Sosti_Form_RelatoriosHelpdesk();
                $form->removeElement('DATA_INICIAL_CADASTRO');
                $form->removeElement('DATA_FINAL_CADASTRO');
                $form->removeElement('DATA_INICIAL_ENCAMINHAMENTO');
                $form->removeElement('DATA_FINAL_ENCAMINHAMENTO');
                $form->removeElement('AVALIACAO');
                $this->view->form = $form;
                /*paginação*/
                if ($this->getRequest()->isPost()) {
                    $data = $this->getRequest()->getPost();
                    $grupoJsonDecode = Zend_Json::decode($data["SGRS_ID_GRUPO"]);
                    $data["DATA_INICIAL"] = $data["DATA_INICIAL"];
                    $data["DATA_FINAL"] = $data["DATA_FINAL"];
                    $aSlaPeriodoSpace->data = $data;
                    $aSlaPeriodoSpace->grupo = $grupoJsonDecode["SGRS_ID_GRUPO"];
                    $aSlaPeriodoSpace->caixa = $grupoJsonDecode["CXEN_ID_CAIXA_ENTRADA"];
                    $aSlaPeriodoSpace->nivel = $data["SNAT_CD_NIVEL"];
                    $aSlaPeriodoSpace->data_inicial = $data["DATA_INICIAL"];
                    $aSlaPeriodoSpace->data_final = $data["DATA_FINAL"];
                    $aSlaPeriodoSpace->pesquisar = $data["Pesquisar"];
                }
                $grupo = new Application_Model_DbTable_SadTbCxgsGrupoServico();
                $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
                $aSlaPeriodoSpace->dataHoraAtual = $dados->dataHoraAtual();
                if ($aSlaPeriodoSpace->nivel) {
                    $descricaoNivel = $grupo->getNivel($aSlaPeriodoSpace->grupo, $aSlaPeriodoSpace->nivel);
                    $aSlaPeriodoSpace->descricaoNivel = $descricaoNivel[0]["SNAT_DS_NIVEL"].' - '.$descricaoNivel[0]["SNAT_SG_NIVEL"];
                    $this->view->descricaoNivel = $aSlaPeriodoSpace->descricaoNivel;
                }
                if ($aSlaPeriodoSpace->grupo) {
                    $aSlaPeriodoSpace->descricaoGrupo = $grupo->getGrupoAtendimento($aSlaPeriodoSpace->grupo);
                    $this->view->descricaoGrupo = $aSlaPeriodoSpace->descricaoGrupo;
                }
                $this->view->title = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                                      ("SLA - Primeiro Atendimento das Solicitações: ".$aSlaPeriodoSpace->data_inicial." a ".$aSlaPeriodoSpace->data_final):
                                      ("SLA - Primeiro Atendimento das Solicitações: ".$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);

                /*Ordenação das paginas*/
                $order_column = $this->_getParam('ordem', 'DOCM_DH_CADASTRO');
                $order_direction = $this->_getParam('direcao', 'ASC');
                $order = $order_column.' '.$order_direction;

                if ($aSlaPeriodoSpace->pesquisar != "") {
                    $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                    $itemsperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');


                    ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');

                    $rows = $dados->getSolicitacoesPrimeiroAtendimentoSla($aSlaPeriodoSpace->caixa, $aSlaPeriodoSpace->data_inicial, $aSlaPeriodoSpace->data_final, $order);
                    $qtde = count($rows);
                    $this->view->qtde = $qtde;

                    if ($qtde < 5000) {       

                        $tempoInicial = new App_Sosti_TempoSla();
                        $i = 0;
                        for ($i = 0; $i < $qtde; $i++) {
                            $rows[$i]['TEMPO_TRANSCORRIDO'] = $tempoInicial->tempoTotalHelpdesk($rows[$i]['MOVI_DH_ENCAMINHAMENTO'], $rows[$i]["DATA_PRIMEIRO_ATENDIMENTO"], '07:00:00', '20:00:00');
                            $rows[$i]['TEMPO_TRANSCORRIDO_MINUTOS'] = (float) sprintf('%.2f',($tempoInicial->converteHorasFormatadasParaSegundos($rows[$i]['TEMPO_TRANSCORRIDO']))/60);
                        }

                        $aSlaPeriodoSpace->rows = $rows;
                        //$fim =  count($rows);

                        $paginator = Zend_Paginator::factory($rows);
                        $paginator->setCurrentPageNumber($page)
                                  ->setItemCountPerPage($itemsperpage);

                        $this->view->ordem = $order_column;
                        $this->view->direcao = $order_direction;
                        $this->view->data = $paginator;
                        $this->view->direcao_pdf = $order;
                        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
                    }
                }
        }else{
        	/*
            $this->_helper->flashMessenger(array('message' => $msgUsuario, 'status' => 'notice'));
            $this->_helper->_redirector('index', 'index', 'admin');
            */
        	$this->_helper->flashMessenger ( array ('message' => $permiteSla ['mensagem'], 'status' => 'notice' ) );
        	$this->_helper->_redirector ( 'index', 'index', 'admin' );
        }
    }
    
    public function primeiroatendimentosolicitacoespdfAction() 
    {
        set_time_limit( 1200 );

        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPrimeiroAtendimentoNs');
        
        $this->view->descricaoNivel = $aSlaPeriodoSpace->descricaoNivel;
        $this->view->descricaoGrupo = $aSlaPeriodoSpace->descricaoGrupo;
        $this->view->dataHoraAtual = $aSlaPeriodoSpace->dataHoraAtual;

        $this->view->title = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                              ("SLA - Primeiro Atendimento das Solicitações: ".$aSlaPeriodoSpace->data_inicial." a ".$aSlaPeriodoSpace->data_final):
                              ("SLA - Primeiro Atendimento das Solicitações: ".$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);

        $this->view->data = $aSlaPeriodoSpace->rows;
        
        $this->render();
       
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       10,    // margin top
                       10,    // margin bottom
                        9,    // margin header
                        9,    // margin footer
                       'L');
        $mpdf->AddPage('P', '', '0', '1');
        $mpdf->WriteHTML($body);
        $name =  'Primeiro_atendimento_'.str_replace('/', '_', $aSlaPeriodoSpace->data_inicial.'_'.$aSlaPeriodoSpace->data_final).'.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function primeiroatendimentosolicitacoesexcelAction()
    {
        $this->_helper->layout->disableLayout(); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=extraction.xls"); 
         
        $aSlaPeriodoSpace = new Zend_Session_Namespace('slaPrimeiroAtendimentoNs');
        
        $this->view->descricaoNivel = $aSlaPeriodoSpace->descricaoNivel;
        $this->view->descricaoGrupo = $aSlaPeriodoSpace->descricaoGrupo;

        $this->view->title = (($aSlaPeriodoSpace->data_inicial) && ($aSlaPeriodoSpace->data_final))?
                              ("SLA - Primeiro Atendimento das Solicitações: ".$aSlaPeriodoSpace->data_inicial." a ".$aSlaPeriodoSpace->data_final):
                              ("SLA - Primeiro Atendimento das Solicitações: ".$aSlaPeriodoSpace->data_inicial.$aSlaPeriodoSpace->data_final);

        $this->view->data = $aSlaPeriodoSpace->rows;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }
}
