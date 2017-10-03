<?php

class Sosti_GrupoatendimentoController extends Zend_Controller_Action
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
		
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Sosti';
    }

    public function indexAction()
    {
          $form = new Sosti_Form_Grupoatendimento();
          
          $this->view->form = $form;
    }

    public function formAction()
    {
        // action body
        $this->view->title = "Cadastro de solicitação";
        $form = new Sosti_Form_Solicitacao();
        $table = new Application_Model_DbTable_SosTbSserServico();
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        //Zend_Debug::dump($_SERVER['REMOTE_ADDR']);
        
        if ($id) {
            $row = $table->fetchRow(array('SSOL_CD_DOCUMENTO = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }else{
             $form->removeElement('SSOL_CD_DOCUMENTO');
        }
        $this->view->form = $form;
    }
    
    public function ajaxservicosAction()
    {
        //$id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $id     = $this->_getParam('id');
        $id = Zend_Json::decode($id);
        $id[SGRS_ID_GRUPO] = Zend_Filter::FilterStatic($id[SGRS_ID_GRUPO],'int');
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SosTbSserServico_array = $SosTbSserServico->fetchAll("SSER_ID_GRUPO = $id[SGRS_ID_GRUPO]")->toArray();
        $this->view->servicos = $SosTbSserServico_array;
        
    }
    public function ajaxdesctomboAction()
    {
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $Tombo = new Application_Model_DbTable_TomboTiCentral();
        $Tombo_array = $Tombo->getDescTombo($id);
        $this->view->desctombo = $Tombo_array;
    }
    
    public function ajaxnomesolicitanteAction()
    {
        $matriculanome     = $this->_getParam('term','');
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $nome_array = $OcsTbPmatMatricula->getNomeSolicitanteAjax($matriculanome);
        $fim =  count($nome_array);
        for ($i = 0; $i<$fim; $i++ ) {
            $nome_array[$i] = array_change_key_case ($nome_array[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($nome_array);
    }
    
    public function ajaxunidadeAction()
    {
        $unidade     = $this->_getParam('term','');
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade);
        
        $fim =  count($lotacao);
        for ($i = 0; $i<$fim; $i++ ) {
            $lotacao[$i] = array_change_key_case ($lotacao[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($lotacao);
        
    }
    
    public function ajaxpessoaunidadeAction()
    {
        $matricula     = $this->_getParam('term','');
        $aux = explode(' - ', $matricula);
        $matricula = $aux[0];
        
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $dadosPessoais = $OcsTbPmatMatricula->getDadosPessoaisAjax($matricula);
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $fone = $mapperDocumento->getUltimoTelefoneCadastrado($matricula);
        $dadosPessoais[0]['SSOL_NR_TELEFONE_EXTERNO'] = $fone;
        $this->_helper->json->sendJson($dadosPessoais);
        
    }
    
       
    public function saveAction()
    {
        set_time_limit(1800);
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
        
        $this->view->title = "Cadastro de solicitação";
        $aNamespace = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_Solicitacao();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();

        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                if ($anexos->getFileName()) {
                    try {
                        $upload  = new App_Multiupload_Upload();
                        $nrDocsRed = $upload->incluirarquivos($anexos);
                    } catch (Exception $exc) {
                        $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'error'));
                        $this->_helper->_redirector('form', 'solicitacaouserti', 'sosti');
                    }
                }
             $unidade = explode(' - ', $data['UNIDADE']);

             $data['DOCM_SG_SECAO_REDATORA'] = $unidade[3];
             $data['DOCM_CD_LOTACAO_REDATORA'] = $unidade[0];

             $data['DOCM_SG_SECAO_GERADORA'] = $unidade[3];
             $data['DOCM_CD_LOTACAO_GERADORA'] = $unidade[0];

             $dataDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = $aNamespace->matricula;
             $dataDocmDocumento["DOCM_ID_TIPO_DOC"] = 154; //Solicitação de serviços a TI
             $dataDocmDocumento["DOCM_SG_SECAO_GERADORA"] = $data['DOCM_SG_SECAO_GERADORA'];
             $dataDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = $data['DOCM_CD_LOTACAO_GERADORA'];
             $dataDocmDocumento["DOCM_SG_SECAO_REDATORA"] = $data['DOCM_SG_SECAO_REDATORA'];
             $dataDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] = $data['DOCM_CD_LOTACAO_REDATORA'];
             $dataDocmDocumento["DOCM_ID_PCTT"] = 414; //PCTT Solicitação de TI
             $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = $data['DOCM_DS_ASSUNTO_DOC'];
             $dataDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Sistuaçaõ Digital Gerado pelo sistema
             $dataDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Situaação Pública
             $dataDocmDocumento["DOCM_DS_PALAVRA_CHAVE"] =$data['DOCM_DS_PALAVRA_CHAVE'];

             $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = 1;
             $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = $data["SSOL_ED_LOCALIZACAO"];
             $dataSsolSolicitacao["SSOL_NR_TOMBO"] = $data["SSOL_NR_TOMBO"];
             $dataSsolSolicitacao["SSOL_SG_TIPO_TOMBO"] = $data["SSOL_SG_TIPO_TOMBO"]; 
             $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = $data["SSOL_DS_OBSERVACAO"];
             unset($dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO']);
             unset($dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO']);
             $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = $data['SSOL_DS_EMAIL_EXTERNO'];
             $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = $data['SSOL_NR_TELEFONE_EXTERNO'];

             $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $unidade[3];
             $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $unidade[0];
//                 unset($dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"]);
//                 unset($dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"]);
             $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = 1;//Caixa de atendimento DIATU
             $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $aNamespace->matricula;


             $destino = Zend_Json::decode($data[SGRS_ID_GRUPO]);
             //Zend_Debug::dump($destino);

             $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $destino['SGRS_SG_SECAO_LOTACAO'];
             $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $destino['SGRS_CD_LOTACAO']; 
//                 unset($dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"]);
//                 unset($dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"]);
             $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
             $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $destino['CXEN_ID_CAIXA_ENTRADA'];//Caixa de atendimento 


             $dataMofaMoviFase["MOFA_ID_FASE"] = 1006; //CADASTRO SOLICITACAO TI
             $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $aNamespace->matricula;
             $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solictação para primeiro atendiamento no 1º Nível - Serviço de Atendimento Técnico ao Cliente - SAT";


             $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = 1; //Primeiro Nivel HelpDesk ;

             $id_serviço = explode('|',$data["SSER_ID_SERVICO"]);
             $dataSsesServicoSolic["SSES_ID_SERVICO"] = $id_serviço[0];

             if($nrDocsRed["erro"]){
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('parecer');
                    return;
                }
                if(!$nrDocsRed["existentes"]){
                    if (!$nrDocsRed["incluidos"]) {
                        try {
                            $dataRetorno = $table->cadastraSolicitacao( $dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic);
                            $msg_to_user = "Solicitação nº: ".$dataRetorno['DOCM_NR_DOCUMENTO']." cadastrada!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                        }
                        }else{
                            try {
                                $dataRetorno = $table->cadastraSolicitacao( $dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $nrDocsRed);
                                $msg_to_user = "Solicitação nº: ".$dataRetorno['DOCM_NR_DOCUMENTO']." cadastrada!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                                $this->_helper->_redirector('form','solicitacao','sosti');
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possível cadastrar sua solicitação. Não foi possível fazer o carregamento do arquivo. Se for possível tente cadastrar sua solicitação sem anexo.";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                        }
                    }
              }else{
                    foreach($nrDocsRed["existentes"] as $existentes){
                        $msg_to_user = "Anexo ".$existentes['NOME']." pertence ao documento nr: ".$existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->form = $form;
                    $this->render('parecer');
                    return;
                }
              $this->_helper->_redirector('form','solicitacao','sosti');
            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }
    public function encaminharAction()
    {
        $aNamespace = new Zend_Session_Namespace('userNs'); 
        $form = new Sosti_Form_EncaminhaSolicitacao();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbSsolSolicitacao();

        $id     = Zend_Filter::FilterStatic($this->_getParam('id',''),'alnum');
        $secao     = Zend_Filter::FilterStatic($this->_getParam('secao',''),'alnum');
        $unidade     = Zend_Filter::FilterStatic($this->_getParam('unidade',''),'alnum');
        $caixa     = Zend_Filter::FilterStatic($this->_getParam('caixa',''),'alnum');
        $servico     = Zend_Filter::FilterStatic($this->_getParam('servico',''),'alnum');
        $nivel     = Zend_Filter::FilterStatic($this->_getParam('nivel',''),'alnum');
        
//        Zend_Debug::dump($id);
//        Zend_Debug::dump($secao);
//        Zend_Debug::dump($unidade);
//        Zend_Debug::dump($caixa);
//        Zend_Debug::dump($servico);
//        Zend_Debug::dump($nivel);
        
        $form->DOCM_ID_DOCUMENTO->setValue($id);
        $form->MOVI_SG_SECAO_UNID_ORIGEM->setValue($secao);
        $form->MOVI_CD_SECAO_UNID_ORIGEM->setValue($unidade);
        $form->MOVI_ID_CAIXA_ENTRADA->setValue($caixa);
        
        /*  sosti/solicitacao/encaminhar/id/1/secao/TR/unidade/1146/caixa/1  */
        
        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {                
             
                
                $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $data['MOVI_SG_SECAO_UNID_ORIGEM'];
                $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $data['MOVI_CD_SECAO_UNID_ORIGEM'];
                $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $aNamespace->matricula;
                $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $data['MOVI_ID_CAIXA_ENTRADA'];

                $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $data["MODE_SG_SECAO_UNID_DESTINO"];
                $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $data["MODE_CD_SECAO_UNID_DESTINO"];
                $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $data["MODE_ID_CAIXA_ENTRADA"];

                $dataMofaMoviFase["MOFA_ID_FASE"] = 1001;
                $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $aNamespace->matricula;
                $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                $dataSnasNivelAtendSolic["SNAS_ID_NIVEL"] = $data['SNAS_ID_NIVEL'];
                
                Zend_Debug::dump($dataMoviMovimentacao);
                Zend_Debug::dump($dataModeMoviDestinatario);
                Zend_Debug::dump($dataMofaMoviFase);
                Zend_Debug::dump($dataSnasNivelAtendSolic);
                exit;

                $table->encaminhaSolicitacao($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataSsesServicoSolic, $dataSnasNivelAtendSolic, $dataMofaMoviFase);
               
               $msg_to_user = "Solicitação cadastrada!";
               $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
               $this->_helper->_redirector('encaminhar','solicitacao','sosti');
                
            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }
    
}
