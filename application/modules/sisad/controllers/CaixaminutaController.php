<?php
class Sisad_CaixaminutaController extends Zend_Controller_Action
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
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
        
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction()
    {
        $this->view->title = "Formulário de Minuta";     
    }
      
    public function encaminharpessoaAction()
    {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
        
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaunidade_encaunidadepessoa = new Zend_Session_Namespace('Ns_Caixaunidade_encaunidadepessoa');
        
        $form = new Sisad_Form_EncaPessoaMinuta();
        $data = array_merge($this->getRequest()->getPost(),$form->populate($this->getRequest()->getPost())->getValues());
        
       if(isset($data['acao']) && $data['acao'] == 'Encaminhar Pessoa'){
           $Ns_Caixaunidade_encaunidadepessoa->data_post_caixa = $data['documento'];
        }else if(!is_null($Ns_Caixaunidade_encaunidadepessoa->data_post_caixa )){
           $data['documento'] =  $Ns_Caixaunidade_encaunidadepessoa->data_post_caixa;
        }
        
        /**
         * Recuperando a unidade da caixa
         */
        $documentos = array();
        $i= 0;
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
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $pessoas = $SadTbPapdParteProcDoc->getPartesVistas($dados_input['DOCM_ID_DOCUMENTO'], null, 3);
       
        $mode_cd_matr_recebedor = $form->getElement('MODE_CD_MATR_RECEBEDOR');
        foreach ($pessoas as $pessoas_p):
            $mode_cd_matr_recebedor->addMultiOptions(array($pessoas_p["USUARIO"] => $pessoas_p["NOME"]));
        endforeach;

         /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        
        $cont = 0;
        $rows = array();

        foreach ($data['documento'] as $value) {
            $dados_input = Zend_Json::decode($value);
            $linha = $dados_input;
            $rows[$cont] = $linha;
            $cont++;
        }
        
        if($data['acao']=='EncaminharPessoaForm'){
            if($form->isValid($data)){
                if ($anexos->getFileName()) {
                    try {
                        $upload  = new App_Multiupload_Minuta($dados_input);
                        $nrDocsRed = $upload->incluirarquivos($anexos);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger (array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                        $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                    }
                }
                if($nrDocsRed["erro"]){
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->formInterno = $form;
                    $this->render('encaminharpessoa');
                    return;
                }
                if(!$nrDocsRed["existentes"]){
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];
                        /*dados da origem do documento*/

                        $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;
                        $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $dados_input['MODE_SG_SECAO_UNID_DESTINO'];
                        $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $dados_input['MODE_CD_SECAO_UNID_DESTINO'];

                        /*dados do destino do documento*/
                        $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $dados_input['MODE_SG_SECAO_UNID_DESTINO'];
                        $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $dados_input['MODE_CD_SECAO_UNID_DESTINO'];
                        $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';

                        $dataMofaMoviFase["MOFA_ID_FASE"] = 1010; /*ENCAMINHAMENTO SISAD*/
                        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                        $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;

                        $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = NULL;
                        if($data['MODE_CD_MATR_RECEBEDOR']){
                            $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"] = $data['MODE_CD_MATR_RECEBEDOR'];
                        }else{
                            $this->_helper->flashMessenger (array('message' => "Não foi possível encaminhar o(s) documento(s). Dados de destino não informados.", 'status' => 'notice'));
                            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                        }
                        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
                        if(is_null($OcsTbPmatMatricula->find($dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"])->current())){
                            $this->_helper->flashMessenger (array('message' => "Não foi possível encaminhar o(s) documento(s). Dados de destino não informados.", 'status' => 'notice'));
                            $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                        }
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistema = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $dataModpDestPessoa["MODP_CD_MAT_PESSOA_DESTINO"].'@trf1.jus.br';
                        $assunto = 'Encaminhamento de Documento';
                        $corpo = "Foi encaminhado um documento para sua caixa pessoal.</p>
                                  Número do Documento: ".$nrDocmDocumento." <br/>
                                  Encaminhado por: ".$userNs->nome." <br/>
                                  Tipo do Documento: ".$dados_input["DTPD_NO_TIPO"]." <br/>
                                  Descrição do Encaminhamento: ".nl2br($dataMofaMoviFase["MOFA_DS_COMPLEMENTO"])."<br/>";
                        if (!$nrDocsRed["incluidos"]) {
                            try {
                                $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, 
                                                                           $dataMoviMovimentacao, 
                                                                           $dataModeMoviDestinatario, 
                                                                           $dataMofaMoviFase,
                                                                           $dataModpDestPessoa );

                                $Ns_Caixaunidade_encaminhar->data_post_caixa_executado = $data_post_caixa;
                                $this->_helper->flashMessenger ( array('message' => "Documento nº $nrDocmDocumento Encaminhado", 'status' => 'success'));
                            } catch (Exception $exc) {
                                $this->_helper->flashMessenger (array('message' => "Não foi possível encaminhar o documento nº $nrDocmDocumento", 'status' => 'error'));
                                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                            }
                        } else {
                            try {
                                $encaminhaDocumento_retorno = $SadTbModeMoviDestinatario->encaminhaMinuta($idDocmDocumento, 
                                                                       $dataMoviMovimentacao, 
                                                                       $dataModeMoviDestinatario, 
                                                                       $dataMofaMoviFase,
                                                                       $dataModpDestPessoa,
                                                                       $nrDocsRed); //$nrDocsRed["incluidos"]);

                                $Ns_Caixaunidade_encaminhar->data_post_caixa_executado = $data_post_caixa;
                                $this->_helper->flashMessenger ( array('message' => "Documento nº $nrDocmDocumento Encaminhado", 'status' => 'success'));
                            } catch (Exception $exc) {
                                $this->_helper->flashMessenger (array('message' => "Não foi possível encaminhar o documento nº $nrDocmDocumento", 'status' => 'error'));
                                $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                            }
                        }
                    }
                    try {
                            $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger ( array('message' => 'Não foi possivel enviar email de confirmação para solicitação: '.$dados_input["DOCM_NR_DOCUMENTO"].'<br><b> Erro: </b> <p>'.strip_tags($exc->getMessage()).'<p>', 'status' => 'notice'));
                        }
            }else{
                foreach($nrDocsRed["existentes"] as $existentes){
                    $msg_to_user = "Anexo ".$existentes['NOME']." pertence ao documento nr: ".$existentes['NR_DOCUMENTO'];
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                }
                $this->view->formInterno = $form;
                $this->render('encaminharpessoa');
                return;
            }
            $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
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
        $this->view->title = "Documento para encaminhar";
    }
    
    public function minutasAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $nome = $aNamespace->nome;
        
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'MOVI.MOVI_DH_ENCAMINHAMENTO');
         
        $order_direction = $this->_getParam('direcao', 'DESC');
        
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
       $rows = $dados->getCaixaPessoalMinutas($aNamespace->matricula,$order);
       
        /*verifica condições e faz tratamento nos dados */
        $fim =  count($rows);
        $TimeInterval = new App_TimeInterval();
        for ($i = 0; $i<$fim; $i++ ) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOVI_DH_ENCAMINHAMENTO']);
            
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
        }
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');        
               
        $this->view->title = "Caixa de Minutas - $nome ";
    }
    
    public function finalizarAction()
    {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_finalizar = new Zend_Session_Namespace('Ns_Caixapessoal_finalizar');
        $Ns_Partes_documentos = new Zend_Session_Namespace('Ns_Partes_documentos');
        
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $formfinalizar = new Sisad_Form_Finalizar();
        $this->view->formfinalizar = $formfinalizar;
        
        $Dual = new Application_Model_DbTable_Dual();
        $datahora = $Dual->sysdate();
        
        if ($this->getRequest()->isPost()){
            $data = array_merge($this->getRequest()->getPost(),$formfinalizar->populate($this->getRequest()->getPost())->getValues());
            $data_post_caixa = $data;
           
              if ($anexos->getFileName()) {
                  try {
                      $upload  = new App_Multiupload_Upload();
                      $nrDocsRed = $upload->incluirarquivos($anexos);
                  } catch (Exception $exc) {
                      $this->_helper->flashMessenger (array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                      $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                  }
              }
              
              if(isset($data['acao']) && $data['acao'] == 'Finalizar' || ! is_null($Ns_Caixapessoal_finalizar->data_post_caixa )){
                  if(isset($data['acao']) && $data['acao'] == 'Finalizar'){
                      $Ns_Caixapessoal_finalizar->data_post_caixa = $data_post_caixa;
                  }else if(! is_null($Ns_Caixapessoal_finalizar->data_post_caixa )){
                      $data_post_caixa =  $Ns_Caixapessoal_finalizar->data_post_caixa;
                  }
                  /*paginação*/
                  $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

                  if(isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixaminuta' && 
                         isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'minutas'){
                          $cont = 0;
                          $rows = array();
                          foreach ($data_post_caixa['documento'] as $value) {
                              $rows['documento'][$cont] = Zend_Json::decode($value);
                              $cont++;
                          }
                      }

                  $DocmDocumentoHistorico = $mapperDocumento->getHistoricoDCMTO($rows['documento'][0][DOCM_ID_DOCUMENTO]);
                  $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico;      
                  $this->view->tipodoc = 'Minuta';   

                  $paginator = Zend_Paginator::factory($rows['documento']);

                  $this->view->data = $paginator;
                  Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                  //sessao que grava os documentos para verificar as partes e vistas ja cadastradas
                   $Ns_Partes_documentos->data_post_caixa['documento'] = $data_post_caixa['documento'];
                  
                  $this->view->title = "Finalização de Minuta - $userNs->siglalotacao";
              }
            
            if(isset($data['acao']) && $data['acao'] == 'submitFinalizar' ){
              if ($formfinalizar->isValid($data)) {
                 $data_post_caixa = $Ns_Caixapessoal_finalizar->data_post_caixa;

                 if($nrDocsRed["erro"]){
                      $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                      $this->view->flashMessagesView = $msg_to_user;
                      $this->view->form = $formfinalizar;
                      $this->render('minutas');
                      return;
                  }
                  if(!$nrDocsRed["existentes"]){
                      foreach ($data_post_caixa['documento'] as $value) {
                          $dados_input = Zend_Json::decode($value);

                          $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                          $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                          $dataMofaMoviFaseMin["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
                          $dataMofaMoviFaseMin["MOFA_DH_FASE"] = $datahora;
                          $dataMofaMoviFaseMin["MOFA_ID_FASE"] = 1044; /*FINALIZADA*/
                          $dataMofaMoviFaseMin["MOFA_CD_MATRICULA"] = $userNs->matricula;
                          $dataMofaMoviFaseMin["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                          $nrDocsRed["ID_DOCUMENTO"] = $idDocmDocumento;
                          $nrDocsRed["ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];

                          $nrRedarray = explode('-', $data['anexos'][0]);
                          $nrRed = $nrRedarray[0];
                          $tipoExtensao = $nrRedarray[1];

                          $anexAnexo['ANEX_ID_DOCUMENTO'] = $idDocmDocumento;
                          $anexAnexo["ANEX_NR_DOCUMENTO_INTERNO"] = $nrRed;
                          $anexAnexo["ANEX_ID_TP_EXTENSAO"] = $tipoExtensao;

                          $mapperDocumento = new Sisad_Model_DataMapper_Documento();

                          if ($tipoExtensao == 4){//html
                            $arquivo = $mapperDocumento->abrirArquivo($data, $tipoExtensao);                             
                          }

                          try {
                              $Ns_Caixapessoal_finalizar->data_post_caixa['dataMofaMoviFaseMin'] = $dataMofaMoviFaseMin;
                              $Ns_Caixapessoal_finalizar->data_post_caixa['anexAnexo'] = $anexAnexo;
                              $Ns_Caixapessoal_finalizar->data_post_caixa['DTPD_NO_TIPO'] = 'Minuta';
                              $Ns_Caixapessoal_finalizar->data_post_caixa['DOCM_ID_DOCUMENTO'] = $dados_input['DOCM_ID_DOCUMENTO'];
                              $Ns_Caixapessoal_finalizar->data_post_caixa['DOCM_NR_DOCUMENTO_RED'] = $nrRed; //$data['anexos'][0];
                              $Ns_Caixapessoal_finalizar->data_post_caixa['caminhoDocumento'] = $arquivo;

                              return $this->_helper->_redirector('formcadastro','caixaminuta','sisad');
                          } catch (Exception $exc) {
                              $this->_helper->flashMessenger (array('message' => "Não foi possível salvar a finalização do documento nº $nrDocmDocumento", 'status' => 'error'));
                              $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                          }
                      }
                  }else{
                      foreach($nrDocsRed["existentes"] as $existentes){
                          $msg_to_user = "Anexo ".$existentes['NOME']." pertence ao documento nr: ".$existentes['NR_DOCUMENTO'];
                          $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                          $this->view->flashMessagesView = $msg_to_user;
                      }
                      $this->view->form = $formfinalizar;
                      $this->render('minutas');
                      return;
                  }
            }else{

              $cont = 0;
              $rows = array();
              foreach ($data_post_caixa['documento'] as $value) {
                  $rows['documento'][$cont] = Zend_Json::decode($value);
                  $cont++;
              }
              $DocmDocumentoHistorico = $mapperDocumento->getHistoricoDCMTO($rows['documento'][0][DOCM_ID_DOCUMENTO]);
              $paginator = Zend_Paginator::factory($rows['documento']);
              $this->view->data = $paginator;
              Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

              $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico;    
              $this->view->formfinalizar = $formfinalizar;
              $this->render('finalizar');
              return;
            }
         }
       }
    }
    
    public function minutasfinalizadasAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $nome = $aNamespace->nome;
        
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'MOVI.MOVI_DH_ENCAMINHAMENTO');
         
        $order_direction = $this->_getParam('direcao', 'DESC');
        
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
       $rows = $dados->getCaixaPessoalMinutas($aNamespace->matricula,$order, 1044); //fase finalização
       
        /*verifica condições e faz tratamento nos dados */
        $fim =  count($rows);
        $TimeInterval = new App_TimeInterval();
        for ($i = 0; $i<$fim; $i++ ) {
            $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOVI_DH_ENCAMINHAMENTO']);
            
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
        }
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');        
               
        $this->view->title = "Caixa de Minutas Finalizadas - $nome ";
    }
    
    public function saveversaoAction() 
    {
     /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $this->view->title = "Inserir Versão na Minuta";
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaminuta_versao = new Zend_Session_Namespace('Ns_Caixaminuta_versao');
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        
        $form = new Sisad_Form_VersaoMinuta();
      //  $this->view->formVersaoMinuta = $form;

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues());
            $data_post_caixa = $Ns_Caixaminuta_versao->data_post_caixa;
            
          if ($form->isValid($data)) {
            
            foreach ($data_post_caixa['documento'] as $value) {
               $dados_input = Zend_Json::decode($value);
            }

            if ($anexos->getFileName()) {
                try {
                    $upload = new App_Multiupload_Minuta($dados_input);
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir arquivo, tente inserir novamente mais tarde.", 'status' => 'notice'));
                    $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                }
            }elseif ($data["RADIO_TIPO_ARQUIVO"] == 'E') {
                try {
                    $upload = new App_Multiupload_Minuta($dados_input);
                    $nrDocsRed = $upload->incluirarquivoHtml($data["TEXTO_HTML"]);
                    //$extensao = 4;
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir arquivo, tente inserir novamente mais tarde.", 'status' => 'notice'));
                    $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                }
            }
            
            if ($nrDocsRed["erro"]) {
                $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                $this->view->flashMessagesView = $msg_to_user;
                $this->view->formVersaoMinuta = $form;
                $this->render('parecer');
                return;
            }

            if (!$nrDocsRed["existentes"]) {
               $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
               $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

               $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
               $dataMofaMoviFase["MOFA_ID_FASE"] = 1048; /* INSERIR VERSÃO */
               $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
               $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

               $nrDocsRed["ID_DOCUMENTO"] = $idDocmDocumento;
               $nrDocsRed["ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];

               if (!$nrDocsRed["incluidos"]) {
                   try {
                       $parecerDocumento_retorno = $mapperDocumento->incluirArquivoMinuta($dataMofaMoviFase);

                       $this->_helper->flashMessenger(array('message' => "Versão do documento nº $nrDocmDocumento salva", 'status' => 'success'));
                   } catch (Exception $exc) {
                       $this->_helper->flashMessenger(array('message' => "Não foi possível salvar a versão do documento nº $nrDocmDocumento", 'status' => 'error'));
                       $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                   }
               } else {
                   try {
                       $parecerDocumento_retorno = $mapperDocumento->incluirArquivoMinuta($dataMofaMoviFase, $nrDocsRed);

                       $this->_helper->flashMessenger(array('message' => "Versão do documento nº $nrDocmDocumento salva", 'status' => 'success'));
                   } catch (Exception $exc) {
                       $this->_helper->flashMessenger(array('message' => "Não foi possível salvar a versão do documento nº $nrDocmDocumento", 'status' => 'error'));
                       $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                   }
               }
            } else {
                foreach ($nrDocsRed["existentes"] as $existentes) {
                    $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                }
                $this->view->formVersaoMinuta = $form;
                $this->render('formversao');
                return;
            }

            return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
        }else{
            $this->view->UltimoAnexoRed = $data_post_caixa['UltimoAnexoRed'];
            $this->view->UltimoAnexoId = $data_post_caixa['UltimoAnexoId'];
            $this->view->UltimoAnexoExtensao = $data_post_caixa['UltimoAnexoExtensao'];
            
            $cont = 0;
            $rows = array();
            foreach ($data_post_caixa['documento'] as $value) {
                $rows['documento'][$cont] = Zend_Json::decode($value);
                $cont++;
            }
            
           $paginator = Zend_Paginator::factory($rows['documento']);
           $paginator->setCurrentPageNumber($page)
                     ->setItemCountPerPage(15);
           $this->view->data = $paginator;
           Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
           
 //          unset($data['MODE_SG_SECAO_UNID_DESTINO']);
 //          unset($data['MODE_CD_SECAO_UNID_DESTINO']);
           $this->view->formVersaoMinuta = $form;
           $this->render('formversao');
           return;
        }
      }     
    }
    
    public function formversaoAction() 
    {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaminuta_versao = new Zend_Session_Namespace('Ns_Caixaminuta_versao');

        $form = new Sisad_Form_VersaoMinuta();
        $this->view->formVersaoMinuta = $form;

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues());
            
            $data_post_caixa = $data; 
            $Ns_Caixaminuta_versao->data_post_caixa = $data_post_caixa;
            /**
             * Recuperando a unidade da caixa
             */
            foreach ($data_post_caixa['documento'] as $value) {
                $dados_input = Zend_Json::decode($value);
            }
            
            $UltimoAnexo = $SadTbAnexAnexo->getUltimoAnexo($dados_input['DOCM_ID_DOCUMENTO'], 4); //html
            
            if(count($UltimoAnexo) == 0 || $UltimoAnexo == ""){
               $DocmDadosDocumento = $mapperDocumento->getDadosDCMTO($dados_input['DOCM_ID_DOCUMENTO']);
               $this->view->UltimoAnexoRed = $DocmDadosDocumento["DOCM_NR_DOCUMENTO_RED"];      
               $this->view->UltimoAnexoId = $DocmDadosDocumento["DOCM_ID_DOCUMENTO"];      
               $this->view->UltimoAnexoExtensao = $DocmDadosDocumento["DOCM_ID_TP_EXTENSAO"];      
            }else{
              $this->view->UltimoAnexoRed = $UltimoAnexo[0]["ANEX_NR_DOCUMENTO_INTERNO"];      
              $this->view->UltimoAnexoId = $UltimoAnexo[0]["ANEX_ID_DOCUMENTO"];
              $this->view->UltimoAnexoExtensao = $UltimoAnexo[0]["ANEX_ID_TP_EXTENSAO"];
            }             
        }
        
        $Ns_Caixaminuta_versao->data_post_caixa['UltimoAnexoRed'] = $this->view->UltimoAnexoRed;
        $Ns_Caixaminuta_versao->data_post_caixa['UltimoAnexoId'] = $this->view->UltimoAnexoId;
        $Ns_Caixaminuta_versao->data_post_caixa['UltimoAnexoExtensao'] = $this->view->UltimoAnexoExtensao;
        
        $cont = 0;
        $rows = array();
        foreach ($data_post_caixa['documento'] as $value) {
            $rows['documento'][$cont] = Zend_Json::decode($value);
            $cont++;
        }
                
        $paginator = Zend_Paginator::factory($rows['documento']);
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->title = "Inserir Versão na Minuta";
    }
    
    public function visualizarAction() {
        $userNamespace = new Zend_Session_Namespace('userNs');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
              $this->view->data = $data;
              $this->view->matricula = $userNamespace ->matricula;

              if($data["DOCM_CD_MATRICULA_CADASTRO"]){
                  $this->view->nome = $data["DOCM_CD_MATRICULA_CADASTRO"];
              }else{
                  $this->view->nome = $userNamespace ->nome . ' - ' . $userNamespace ->matricula;
              }

              $this->render();
              $response = $this->getResponse();
              $body = $response->getBody();
              $response->clearBody();

              $this->_helper->layout->disableLayout();
              define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
              define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
              include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
              $mpdf=new mPDF();

              $mpdf->AddPage('P', '', '0', '1');
              $imagem_path =  realpath(APPLICATION_PATH . '/../public/img/BrasaoBrancoRelatorio.jpg');
              $mpdf->Image($imagem_path, 94, 20, 23, 22, 'jpg', '', true, true, false, false, true);
              $mpdf->WriteHTML($body);
              $name =  'SISAD_TEMP_DOC_MINUTA_VISUALIZAR_DOCUMENTO' . date("dmYHisu") .'.pdf';
              $mpdf->Output($name,'D');
            }else{
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }
    
    public function reutilizarversaoAction()
    {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);
        
        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaminuta_reutilizar = new Zend_Session_Namespace('Ns_Caixaminuta_reutilizar');
        
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $formfinalizar = new Sisad_Form_Finalizar();
        $this->view->formfinalizar = $formfinalizar;
        
        $Dual = new Application_Model_DbTable_Dual();
        $datahora = $Dual->sysdate();
        
        if ($this->getRequest()->isPost()){
            $data = array_merge($this->getRequest()->getPost(),$formfinalizar->populate($this->getRequest()->getPost())->getValues());
            $formfinalizar->removeElement('MOFA_DS_COMPLEMENTO');
            
            if ($anexos->getFileName()) {
                try {
                    $upload  = new App_Multiupload_Upload();
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger (array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                    $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                }
            }
            
            if(isset($data['acao']) && $data['acao'] == 'Reutilizar Versão' || ! is_null($Ns_Caixaminuta_reutilizar->data_post_caixa )){
                $data_post_caixa = $data;
                                
                if(isset($data['acao']) && $data['acao'] == 'Reutilizar Versão'){
                    $Ns_Caixaminuta_reutilizar->data_post_caixa = $data_post_caixa;
                }else if(! is_null($Ns_Caixaminuta_reutilizar->data_post_caixa )){
                    $data_post_caixa =  $Ns_Caixaminuta_reutilizar->data_post_caixa;
                }
                /*paginação*/
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /*Ordenação das paginas*/
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column.' '.$order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                
                /*Ordenação*/
                if(isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixaminuta' && 
                   isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'minutasfinalizadas'){
                    $cont = 0;
                    $rows = array();
                    foreach ($data_post_caixa['documento'] as $value) {
                        $rows['documento'][$cont] = Zend_Json::decode($value);
                        $cont++;
                    }
                }

                $DocmDocumentoHistorico = $mapperDocumento->getHistoricoDCMTO($rows['documento'][0][DOCM_ID_DOCUMENTO]);
                $this->view->DocmDocumentoHistorico = $DocmDocumentoHistorico;      
                $this->view->tipodoc = 'Minuta';   
                                
                $paginator = Zend_Paginator::factory($rows['documento']);
                $paginator->setCurrentPageNumber($page)
                           ->setItemCountPerPage(count($rows['documento']));

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                $this->view->title = "Reutilização de Minuta - $userNs->siglalotacao";       
            }
            
            if(isset($data['acao']) && $data['acao'] == 'submitReutilizar' ){
               $data_post_caixa = $Ns_Caixaminuta_reutilizar->data_post_caixa;
               
               if($nrDocsRed["erro"]){
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $formAnaliseminuta;
                    $this->render('minutas');
                    return;
                }
                if(!$nrDocsRed["existentes"]){
                    foreach ($data_post_caixa['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        
                        $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                        $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                        $nrDocsRed["ID_DOCUMENTO"] = $idDocmDocumento;
                                                
                        $nrRedarray = explode('-', $data['anexos'][0]);
                        $nrRed = $nrRedarray[0];
                        $tipoExtensao = $nrRedarray[1];              
                        
                        $mapperDocumento = new Sisad_Model_DataMapper_Documento();

                        try {
                            $Ns_Caixaminuta_reutilizar->data_post_caixa['DOCM_ID_DOCUMENTO'] = $dados_input['DOCM_ID_DOCUMENTO'];
                            $Ns_Caixaminuta_reutilizar->data_post_caixa['tipoExtensao'] = $tipoExtensao;
                            $Ns_Caixaminuta_reutilizar->data_post_caixa['DOCM_NR_DOCUMENTO_RED'] = $nrRed; //$data['anexos'][0];
                                                        
                            return $this->_helper->_redirector('form','formulariominuta','sisad');
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger (array('message' => "Não foi possível reutilizar a versão do documento nº $nrDocmDocumento", 'status' => 'error'));
                            $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                        }
                    }
                }else{
                    foreach($nrDocsRed["existentes"] as $existentes){
                        $msg_to_user = "Anexo ".$existentes['NOME']." pertence ao documento nr: ".$existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->form = $formAnaliseminuta;
                    $this->render('minutasfinalizadas');
                    return;
                }
            }
        }
    }
    
    public function formcadastroAction()
     {
         $form   = new Sisad_Form_Cadastrodcmtominuta();

         $this->view->formParte = new Sisad_Form_Partes();
         $userNamespace = new Zend_Session_Namespace('userNs');

         $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
         $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidadePessoal($AcessoCaixaUnidade->getMatricula());

        if( !empty($CaixasUnidadeAcesso) ){
             $this->view->formCaixa = new Sisad_Form_PermissaoCaixa();
             $unidade = $this->view->formCaixa->UNIDADE->setRequired(false);
             foreach ($CaixasUnidadeAcesso as $CaixaUnidade):
                 $unidade->addMultiOptions(array(Zend_Json::encode($CaixaUnidade) => $CaixaUnidade["LOTA_SIGLA_LOTACAO"].' - '.$CaixaUnidade["LOTA_DSC_LOTACAO"].' - '.$CaixaUnidade["LOTA_COD_LOTACAO"].' - '.$CaixaUnidade["LOTA_SIGLA_SECAO"] ));
             endforeach;
}

         $Ns_Caixapessoal_finalizar = new Zend_Session_Namespace('Ns_Caixapessoal_finalizar');
         $dadosReutilizar = $Ns_Caixapessoal_finalizar->data_post_caixa;
         
         if((isset($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']) && !empty($dadosReutilizar['DOCM_NR_DOCUMENTO_RED']))){
           $this->view->ID_DOCUMENTO = $dadosReutilizar['DOCM_ID_DOCUMENTO'];
           $this->view->NR_DOCUMENTO_RED = $dadosReutilizar['DOCM_NR_DOCUMENTO_RED'];
           $this->view->ID_TP_EXTENSAO = $dadosReutilizar['anexAnexo']['ANEX_ID_TP_EXTENSAO'];
           $form->getElement('DESTINO_DOCUMENTO')->addMultiOptions(array("E" => "Caixa Unidade - Entrada"));
         }else{
           $this->view->ID_DOCUMENTO = "";
           $this->view->NR_DOCUMENTO_RED = "";
           $this->view->ID_TP_EXTENSAO = 0;
         }

         $cont = 0;
         $rows = array();
         foreach ($dadosReutilizar['documento'] as $value) {
             $rows['documento'][$cont] = Zend_Json::decode($value);
             $cont++;
         }
        
         $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
         $lotacaoGeradora = $rhCentralLotacao->getLotacaoAjax($rows['documento'][0]['DOCM_CD_LOTACAO_GERADORA'],$rows['documento'][0]['DOCM_SG_SECAO_GERADORA']);
         $lotacaoRedatora = $rhCentralLotacao->getLotacaoAjax($rows['documento'][0]['DOCM_CD_LOTACAO_REDATORA'],$rows['documento'][0]['DOCM_SG_SECAO_REDATORA']);
                
         $form->getElement('DOCM_CD_LOTACAO_GERADORA')
              ->setValue($lotacaoGeradora[0]['LABEL']);
         $form->getElement('DOCM_CD_LOTACAO_REDATORA')
              ->setValue($lotacaoRedatora[0]['LABEL']);
         $form->getElement('DOCM_NR_DCMTO_USUARIO')
              ->setValue($rows['documento']['DOCM_NR_DCMTO_USUARIO']);
         $form->getElement('DOCM_ID_PCTT')
              ->setValue($rows['documento'][0]['AQVP_ID_PCTT']);
         $form->getElement('DOCM_DS_ASSUNTO_DOC')
              ->setValue($rows['documento'][0]['DOCM_DS_ASSUNTO_DOC']);
         $form->getElement('DOCM_DS_PALAVRA_CHAVE')
              ->setValue($rows['documento'][0]['DOCM_DS_PALAVRA_CHAVE']);
         
         $this->view->form = $form;
         $this->view->title = "Cadastramento de Documentos";
     }

     public function savecadastroAction() {
        /*
         * Configurações
         * TEMPO máximo de upload 1hora minutos
         */
        set_time_limit(3600);

        /**
         * Forms
         */
        $form = new Sisad_Form_Cadastrodcmtominuta();
        $this->view->formParte = new Sisad_Form_Partes();

        /**
         * Models
         */
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $SadTbVidcVinculacaoDoc = new Application_Model_DbTable_SadTbVidcVinculacaoDoc();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();

        /**
         *  Classes
         */
        $Sisad_anexo = new App_Sisad_Anexo();
        /**
         * Namespaces
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixapessoal_finalizar = new Zend_Session_Namespace('Ns_Caixapessoal_finalizar');
        $dadosMinuta = $Ns_Caixapessoal_finalizar->data_post_caixa;
        $nrDocsRed = array();

        /**
         * Busca as caixas que o usuario tem permissao
         */
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidadePessoal($AcessoCaixaUnidade->getMatricula());
        if (!empty($CaixasUnidadeAcesso)) {
            $this->view->formCaixa = new Sisad_Form_PermissaoCaixa();
            $unidade = $this->view->formCaixa->UNIDADE->setRequired(false);
            foreach ($CaixasUnidadeAcesso as $CaixaUnidade):
                $unidade->addMultiOptions(array(Zend_Json::encode($CaixaUnidade) => $CaixaUnidade["LOTA_SIGLA_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_DSC_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_COD_LOTACAO"] . ' - ' . $CaixaUnidade["LOTA_SIGLA_SECAO"]));
            endforeach;
        }

        if ((isset($dadosMinuta['DOCM_NR_DOCUMENTO_RED']) && !empty($dadosMinuta['DOCM_NR_DOCUMENTO_RED']))) {
            $this->view->ID_DOCUMENTO = $dadosMinuta['DOCM_ID_DOCUMENTO'];
            $this->view->NR_DOCUMENTO_RED = $dadosMinuta['DOCM_NR_DOCUMENTO_RED'];
            $this->view->ID_TP_EXTENSAO = $dadosMinuta['anexAnexo']['ANEX_ID_TP_EXTENSAO'];
        } else {
            $this->view->ID_DOCUMENTO = "";
            $this->view->NR_DOCUMENTO_RED = "";
            $this->view->ID_TP_EXTENSAO = "";
        }

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */

            if ($form->isValidPartial($data)) {
                /**
                 * Validação
                 * Verifica presença de anexos sem um documento principal
                 */
                unset($data["DOCM_NR_DOCUMENTO_RED"]);

                $anexos = new Zend_File_Transfer_Adapter_Http();
                $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

                if ((!$form->DOCM_DS_HASH_RED->isUploaded() && $dadosMinuta["DOCM_NR_DOCUMENTO_RED"] == "") && $anexos->getFileName()) {
                    $msg_to_user = "Não é possivel anexar documentos sem um documento principal.";
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('formcadastro');
                    return;
                }

                if (!$form->DOCM_DS_HASH_RED->isUploaded()) {
                    if (isset($dadosMinuta['caminhoDocumento']) && !empty($dadosMinuta['caminhoDocumento'])) {
                        /*
                         * Caminho do documento html gerado em .pdf ja salvo na pasta temp
                         */
                        $fullFilePath = $dadosMinuta['caminhoDocumento'];
                    }
                } else if ($form->DOCM_DS_HASH_RED->isUploaded()) {
                    /**
                     * O documento foi carregado para o form 
                     */
                    $form->DOCM_DS_HASH_RED->receive();
                    if ($form->DOCM_DS_HASH_RED->isReceived()) {
                        /**
                         * O documento foi salvo na pasta temp
                         * 
                         * Renomeando o arquivo gravado no servidor
                         */
                        if ($form->DOCM_DS_HASH_RED->isUploaded()) {
                            $userfile = $form->DOCM_DS_HASH_RED->getFileName(); /* caminho completo do arquivo gravado no servidor */
                            $tempDirectory = "temp";
                            $userfilename = substr($userfile, strrpos($userfile, $tempDirectory) + strlen($tempDirectory) + 1);
                            //o documento foi renomeado na pasta temp
                            $fullFilePath = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR . 'SISADTEMPDOC' . date("dmYHisu") . $userfilename;
                            $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
                            $filterFileRename->filter($userfile); /* Renomeando a partir do caminho completo do arquivo no servidor */
                        } else {
                            $msg_to_user = "<div class='notice'><strong>Erro:</strong> Não é possível cadastrar uma Minuta sem inserir um documento. </div>";
                            $this->view->flashMessagesView = $msg_to_user;
                            $this->view->form = $form;
                            $this->render('formcadastro');
                            return;
                        }
                    }
                }

                /* Inclusão do arquivo no RED */
                $parametros = new Services_Red_Parametros_Incluir();
                $parametros->login = Services_Red::LOGIN_USER_EADMIN;
                $parametros->ip = Services_Red::IP_MAQUINA_EADMIN;
                $parametros->sistema = Services_Red::NOME_SISTEMA_EADMIN;
                $parametros->nomeMaquina = Services_Red::NOME_MAQUINA_EADMIN;

                $metadados = new Services_Red_Metadados_Incluir();
                $metadados->descricaoTituloDocumento = $data["DOCM_NR_DOCUMENTO"];
                $metadados->numeroTipoSigilo = /* "0"; */$data['DOCM_ID_CONFIDENCIALIDADE']/* Services_Red::NUMERO_SIGILO_PUBLICO; */;
                $metadados->numeroTipoDocumento = "01"/* $data['DOCM_ID_TIPO_DOC']; */;
                $metadados->nomeSistemaIntrodutor = Services_Red::NOME_SISTEMA_EADMIN;
                $metadados->ipMaquinaResponsavelIntervencao = Services_Red::IP_MAQUINA_EADMIN;
                $metadados->secaoOrigemDocumento = "0100";
                $metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
                $metadados->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
                $metadados->nomeMaquinaResponsavelIntervensao = Services_Red::NOME_MAQUINA_EADMIN;
                $metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
                $metadados->numeroDocumento = "";
                $metadados->pastaProcessoNumero = /* ""; */ Services_Red::PASTA_PROCESSO_NUMERO_EADMIN;
                $metadados->secaoDestinoIdSecao = "0100";


                if (defined('APPLICATION_ENV')) {
                    if (APPLICATION_ENV == 'development') {
                        $red = new Services_Red_Incluir(true); /* DESENVOLVIMENTO */
                    } else if (APPLICATION_ENV == 'production') {
                        $red = new Services_Red_Incluir(false); /* PRODUÇÃO */
                    }
                }

                $red->debug = false;
                $red->temp = APPLICATION_PATH . '/../temp';

                $file = $fullFilePath; /* caminho completo do arquivo renomeado no servidor */
                $retornoIncluir_red = $red->incluir($parametros, $metadados, $file);
                $input_files = $anexos->getFileInfo();

                if ($anexos->getFileName() && $input_files["ANEXOS_0_"]["name"] != NULL) {
                    try {
                        $upload = new App_Multiupload_Upload();
                        $nrDocsRed = $upload->incluirarquivos($anexos);
                    } catch (Exception $exc) {
                        $this->_helper->flashMessenger(array('message' => "Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                        $this->_helper->_redirector('formcadastro', 'caixaminuta', 'sisad');
                    }
                }
                if ($nrDocsRed["erro"]) {
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                    $this->view->form = $form;
                    $this->render('formcadastro');
                    return;
                } else if ($nrDocsRed["existentes"]) {
                    foreach ($nrDocsRed["existentes"] as $existentes) {
                        $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                        $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $this->view->form = $form;
                    $this->render('formcadastro');
                    return;
                }
                if (is_array($retornoIncluir_red)) {
                    /* Código repetido */
                    try {
                        $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red['numeroDocumento'];

                        $dataDocmDocumento = $mapperDocumento->preparaArrayDocumento($data, $dadosMinuta);
                        $documento = $mapperDocumento->cadastrarDocumento($dataDocmDocumento, $nrDocsRed, true);

                        $msg_to_user = "Documento cadastrado. Número do documento: " . $documento['DOCM_NR_DOCUMENTO'];
                        $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                        unset($Ns_Caixapessoal_finalizar->data_post_caixa);
                        $this->_helper->redirector('minutas');
                    } catch (Exception $exc) {
                        $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento. Erro: " . $exc->getMessage();
                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                        $this->view->form = $form;
                        $this->render('formcadastro');
                    }
                    /* Código repetido */
                } else {
                    /* Código repetido */
                    /* tratamento de erro */
                    $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                    $retornoIncluir_red_array["codigo"] = $retornoIncluir_red_array[0];
                    $retornoIncluir_red_array["descricao"] = $retornoIncluir_red_array[1];
                    $retornoIncluir_red_array["idDocumento"] = $retornoIncluir_red_array[2];

                    switch ($retornoIncluir_red_array["codigo"]) {
                        case 'Erro: 80':
                            $dcmto_cadastrado = $tabelaSadTbDocmDocumento->fetchAll(array('DOCM_NR_DOCUMENTO_RED = ?' => $retornoIncluir_red_array["idDocumento"]))->toArray();

                            if (isset($dcmto_cadastrado[0]["DOCM_ID_DOCUMENTO"])) {
                                $msg_to_user = "Esse documento já está cadastrado no sistema com o número: " . $dcmto_cadastrado[0]["DOCM_NR_DOCUMENTO"];

                                if ($dcmto_cadastrado[0]["DOCM_IC_ATIVO"] == 'N') {
                                    $msg_to_user .= " e está inativo. <br/><br/>Deseja reativar o documento e alterar os seus metadados?";
                                    $data['DOCM_ID_DOCUMENTO'] = $dcmto_cadastrado[0]["DOCM_ID_DOCUMENTO"];

                                    $movimentacoes = $SadTbModoMoviDocumento->verificaQtdeMovimentacaoDcmto($data['DOCM_ID_DOCUMENTO']);
                                    if ($movimentacoes > 0) {
                                        $msg_to_user .=' <br/><br/> Obs: Esse documento possui movimentações, ele será encaminhado para a sua caixa de entrada pessoal.';
                                        $data['POSSUI_MOVIMENTACAO'] = 'S';
                                    }

                                    $Ns_Cadastrodcmto_ativa = new Zend_Session_Namespace('Ns_Cadastrodcmto_ativa');
                                    $Ns_Cadastrodcmto_ativa->data_post_documento = $data;

                                    $this->view->alert = $msg_to_user;
                                } else {
                                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user </div>";
                                    $this->view->flashMessagesView = $msg_to_user;
                                }
                                $this->view->form = $form;
                                $this->render('formcadastro');
                            } else {
                                try {
                                    $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red_array["idDocumento"];

                                    $dataDocmDocumento = $mapperDocumento->preparaArrayDocumento($data, $dadosMinuta);
                                    $documento = $mapperDocumento->cadastrarDocumento($dataDocmDocumento, $nrDocsRed, true);

                                    $msg_to_user = "Documento cadastrado. Número do documento: " . $documento['DOCM_NR_DOCUMENTO'];
                                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                                    unset($Ns_Caixapessoal_finalizar->data_post_caixa);
                                    $this->_helper->redirector('minutas');
                                } catch (Exception $exc) {
                                    $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.  Erro: " . $exc->getMessage();
                                    $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                    $this->view->flashMessagesView = $msg_to_user;
                                    $this->view->form = $form;
                                    $this->render('formcadastro');
                                }
                                /* Código repetido
                                 */
                            }
                            break;
                        default:
                            $erro = $retornoIncluir_red_array;
                            $msg_to_user = "Documento não cadastrado. Não foi possível fazer o carregamento do arquivo.<br> " . implode(' , ', $erro);
                            $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView = $msg_to_user;
                            $this->view->form = $form;
                            $this->render('formcadastro');
                            break;
                    }
                }
            } else {
                $this->view->form = $form;
                $this->render('formcadastro');
            }
        }
    }
     
    public function parecerAction() {
        /*
         * TEMPO máximo de upload 30min minutos
         */
        set_time_limit(1800);

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Caixaminuta_parecer = new Zend_Session_Namespace('Ns_Caixaminuta_parecer');
        

        $formParecer = new Sisad_Form_Parecer();
        $this->view->formParecer = $formParecer;

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(), $formParecer->populate($this->getRequest()->getPost())->getValues());

            if (isset($data['acao']) && $data['acao'] == 'Parecer' || !is_null($Ns_Caixaminuta_parecer->data_post_caixa)) {
                $data_post_caixa = $data;
                if (isset($data['acao']) && $data['acao'] == 'Parecer') {
                    $Ns_Caixaminuta_parecer->data_post_caixa = $data_post_caixa;
                } else if (!is_null($Ns_Caixaminuta_parecer->data_post_caixa)) {
                    $data_post_caixa = $Ns_Caixaminuta_parecer->data_post_caixa;
                }
                /* paginação */
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /* Ordenação das paginas */
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column . ' ' . $order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

                /* Ordenação */
                foreach ($data_post_caixa['documento'] as $value) {
                    $dados_input = Zend_Json::decode($value);
                }

                if (isset($data_post_caixa['controller']) && $data_post_caixa['controller'] == 'caixaminuta' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'rascunhos') {
                    $cont = 0;
                    $rows = array();
                    foreach ($data_post_caixa['documento'] as $value) {
                        $rows['documento'][$cont] = Zend_Json::decode($value);
                        $cont++;
                    }
                } else {
                    if (isset($data_post_caixa['controller']) &&
                            (($data_post_caixa['controller'] == 'caixaminuta' && isset($data_post_caixa['action']) && $data_post_caixa['action'] == 'entrada') ||
                            ($dados_input['CAIXA_REQUISICAO'] == 'minutas'))) {
                        $cont = 0;
                        $rows = array();
                        foreach ($data_post_caixa['documento'] as $value) {
                            $rows['documento'][$cont] = Zend_Json::decode($value);
                            $cont++;
                        }
                    }
                }

                $paginator = Zend_Paginator::factory($rows['documento']);
                $paginator->setCurrentPageNumber($page)
                        ->setItemCountPerPage(count($rows['documento']));

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                $this->view->title = "Parecer em Documento(s) - $userNs->siglalotacao";
            }

            if ($anexos->getFileName()) {
                try {
                    $upload = new App_Multiupload_Minuta($dados_input);
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } catch (Exception $exc) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível inserir arquivo, tente inserir novamente mais tarde.", 'status' => 'notice'));
                    $this->_helper->_redirector('minutas', 'caixaminuta', 'sisad');
                }
            }
            if ($nrDocsRed["erro"]) {
                $msg_to_user = "<div class='notice'><strong>Erro:</strong> $nrDocsRed[erro]</div>";
                $this->view->flashMessagesView = $msg_to_user;
                $this->view->form = $form;
                $this->render('parecer');
                return;
            } else if ($nrDocsRed["existentes"]) {
                foreach ($nrDocsRed["existentes"] as $existentes) {
                    $msg_to_user = "Anexo " . $existentes['NOME'] . " pertence ao documento nr: " . $existentes['NR_DOCUMENTO'];
                    $msg_to_user = "<div class='notice'><strong>Erro:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView = $msg_to_user;
                }
                $this->view->form = $form;
                $this->render('parecer');
                return;
            }
            if (isset($data['acao']) && $data['acao'] == 'submitParecer') {

                $data_post_caixa = $Ns_Caixaminuta_parecer->data_post_caixa;
                foreach ($data_post_caixa['documento'] as $value) {

                    $dados_input = Zend_Json::decode($value);

                    $idDocmDocumento = $dados_input['DOCM_ID_DOCUMENTO'];
                    $nrDocmDocumento = $dados_input['DOCM_NR_DOCUMENTO'];

                    $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];
                    $dataMofaMoviFase["MOFA_ID_FASE"] = 1011; /* PARECER SISAD */
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data['MOFA_DS_COMPLEMENTO'];

                    $nrDocsRed["ID_DOCUMENTO"] = $idDocmDocumento;
                    $nrDocsRed["ID_MOVIMENTACAO"] = $dados_input['MOFA_ID_MOVIMENTACAO'];

                    $mapperDocumento = new Sisad_Model_DataMapper_Documento();

                    if (!$nrDocsRed["incluidos"]) {
                        try {
                            $parecerDocumento_retorno = $mapperDocumento->incluirArquivoMinuta($dataMofaMoviFase);

                            $this->_helper->flashMessenger(array('message' => "Parecer do documento nº $nrDocmDocumento salvo", 'status' => 'success'));
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível salvar o parecer do documento nº $nrDocmDocumento", 'status' => 'error'));
                            $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
                        }
                    } else {
                        try {
                            $parecerDocumento_retorno = $mapperDocumento->incluirArquivoMinuta($dataMofaMoviFase, $nrDocsRed);

                            $this->_helper->flashMessenger(array('message' => "Parecer do documento nº $nrDocmDocumento salvo", 'status' => 'success'));
                        } catch (Exception $exc) {
                            $this->_helper->flashMessenger(array('message' => "Não foi possível salvar o parecer do documento nº $nrDocmDocumento", 'status' => 'error'));
                            $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
                        }
                    }
                }
                return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
            }
        }
    }
}
