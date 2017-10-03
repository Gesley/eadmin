<?php

/**
 * assinarDocsController
 * 
 * @author
 * @version 
 */

class Sisad_AssinardocsController extends Zend_Controller_Action {
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
	
	private $autorizado = true;
	/**
	 * The default action - show the home page
	 */
	
	public function init() 
	{
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
		$this->view->titleBrowser = 'e-Sisad';
	}
	
	public function indexAction() {
		// TODO Auto-generated assinarDocsController::indexAction() default action
	}
	
	public function assinarAction() {
            
		$userNs = new Zend_Session_Namespace ( 'userNs' );
		$DadosSolic = new Zend_Session_Namespace ( 'DadosSolic' );
		$form = new Sisad_Form_VerificaUsuario ();
		
		$data = $this->_getAllParams ();
		if (isset ( $data ['documento'] )) {
			$DadosSolic->solicitacaoDados = $data ['documento'];
		}
		
		//$processos = Zend_Json::decode ($data['documento']);
		$i = 0;
		foreach ( $data ['documento'] as $value ) {
                    $documento = Zend_Json::decode ( $value );
                    if (in_array ( $documento ['DOCM_ID_CONFIDENCIALIDADE'], array ('1', '3', '4' ) )) {
						$retorno = $this->verificaparteacesso ( $documento );
                    }else{
                        $retorno = true;
                    }
                    if (! $retorno) {
                            $documetosnaoAutorizados .= $documento ['DOCM_NR_DOCUMENTO'] . "<br>";
                            $this->autorizado = false;
                    } else {
                            $documentosAutorizados .= $documento ['DOCM_NR_DOCUMENTO'] . "<br>";
                    }
		}
		$url = explode ( 'page/', $_SERVER ['HTTP_REFERER'] );
		$page = $url [1];
		if ($this->autorizado === false) {
			sleep ( 1 );
			$this->_helper->flashMessenger ( array ('message' => "<strong>Existem processos sigilosos.</strong><br>===== Processos Sigilosos ======<br>$documetosnaoAutorizados ===== Processos Públicos ======<br>$documentosAutorizados<br>Selecione somente os processos que seu usuário tem permissão.", 'status' => 'notice' ) );
			$this->_helper->_redirector ( 'entrada', 'caixaunidade', 'sisad', array ('page' => $page ) );
		}
		
		//SE FOR POST
		if ($this->getRequest ()->isPost ()) {
			$data = $this->getRequest ()->getPost ();
			if ($data ['acao'] == 'AssinarDocs') {
				
				$docInfo = explode ( ",", substr ( $data ['documentosIDs'], 0, - 1 ) );
				$docInfoCount = count ( $docInfo );
				$documento = array ();
				
				foreach ( $docInfo as $value ) {
					$v = explode ( ":", $value );
					
					$documento ['DOCM_ID_DOCUMENTO'] = $v [0]; //VALOR DO ID DO DOCUMENTO
					$documento ['DTPD_ID_TIPO_DOC'] = $v [1]; //VALOR DO TIPO DO DOCUMENTO
					$documento ['DOCM_NR_DOCUMENTO'] = $v [2]; //NÚMERO DO DOCUMENTO
					$documento ['CONF_ID_CONFIDENCIALIDADE'] = $v [3]; // TIPO DE CONFIDENCIALIDADE
					

					if (in_array ( $documento ['CONF_ID_CONFIDENCIALIDADE'], array ('1', '3', '4' ) )) {
						$retorno = $this->verificaparteacesso ( $documento ); //VERIFICA SE A PARTE PODE ASSINAR O DOCUMENTO
						

						if (! $retorno) {
							$documetosnaoAutorizados .= $documento ['DOCM_NR_DOCUMENTO'] . "<br>";
							$this->autorizado = false;
						} else {
							
							$documentosAutorizados .= $documento ['DOCM_NR_DOCUMENTO'] . "<br>";
						}
					} else {
						if($v [3] == 0)
						{
							$documentosAutorizados .= $documento ['DOCM_NR_DOCUMENTO'] . "<br>";
						}
					}
				}
				
				if ($this->autorizado === false) {
					sleep ( 1 );
					$this->_helper->flashMessenger ( array ('message' => "<strong>Existem documentos sigilosos.</strong><br>===== Documentos Sigilosos ======<br>$documetosnaoAutorizados ===== Documentos Públicos ======<br>$documentosAutorizados<br>Selecione somente os processos que seu usuário tem permissão.", 'status' => 'notice' ) );
					$this->_helper->_redirector ( 'assinar', 'assinardocs', 'sisad', array ('page' => $page ) );
				
				}
				
				$SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase ();
				$idmovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao ();
				
				$dual = new Application_Model_DbTable_Dual ();
				
				$documentos = explode ( ',', $data ['documentosSelecionados'] );
				
				foreach ( $documentos as $value ) {
					
					$documentosAssinados .= $value . ', '; // CONCATENA OS NÚMEROS DO DOCUMENTOS SELECIONADOS PRA EXIBIR NA MENSAGEM DE SUCESSO.
				}
				
				$msgError = "";
				//INSERI A MOVIMENTAÇÃO NO PROCESSO SELECIONADO(ASSINATURA)::1
				$documentosAssinados = substr ( $documentosAssinados, 0, - 2 );
				$dataMofaMoviFaseProcesso ["MOFA_ID_MOVIMENTACAO"] = $data ['MOFA_ID_MOVIMENTACAO'];
				$dataMofaMoviFaseProcesso ["MOFA_DH_FASE"] = $dual->sysdate ();
				$dataMofaMoviFaseProcesso ["MOFA_ID_FASE"] = 1018; //ASSINATURA SISAD NÚMERO DA FASE
				$dataMofaMoviFaseProcesso ["MOFA_CD_MATRICULA"] = $userNs->matricula;
				$dataMofaMoviFaseProcesso ["MOFA_DS_COMPLEMENTO"] = 'Assinatura do(s) documento(s): ' . $documentosAssinados . ' por senha';
				//::1
				

				//INSERI MOVIMENTAÇÃO PARA O DOCUMENTO ASSINADO (ASSINATURA)::2
				foreach ( $documentos as $value ) {
					$mapperDocumento = new Sisad_Model_DataMapper_Documento ();
					if (! empty ( $value )) {
						
						$dados = $mapperDocumento->getDadosDocumentopeloNRDoc ( $value );
						
						$dataMofaMoviFaseDoc ["MOFA_ID_MOVIMENTACAO"] = $dados [0] ['MOFA_ID_MOVIMENTACAO'];
						$dataMofaMoviFaseDoc ["MOFA_DH_FASE"] = $dual->sysdate ();
						$dataMofaMoviFaseDoc ["MOFA_ID_FASE"] = 1018;
						$dataMofaMoviFaseDoc ["MOFA_CD_MATRICULA"] = $userNs->matricula;
						$dataMofaMoviFaseDoc ["MOFA_DS_COMPLEMENTO"] = 'Documento assinado por senha';
						//::2
                                                $rowMofaMoviFase = $SadTbMofaMoviFase->createRow( $dataMofaMoviFaseDoc);
                                              try {
						   $rowMofaMoviFase->save(); 
                                                  } catch ( Exception $e ) {
                                                    $msgError .= $e->getMessage () . "<br>";
						}
                                                        
					}
				}
				//::1
                                $db = $SadTbMofaMoviFase->getAdapter ();
				if($msgError == ""){
                                       $db->beginTransaction ();
                                       $rowMofaMoviFase = $SadTbMofaMoviFase->createRow ( $dataMofaMoviFaseProcesso )->save ();
                                       $db->commit ();
				}else{
                                    $db->rollBack ();
                                    $msgError .= $e->getMessage () . "<br>";
				}
				
				$documentosAssinados = substr ( $documentosAssinados, 0, - 2 );
				if (! $rowMofaMoviFase) {
					$this->_helper->flashMessenger ( array ('message' => "Documento(s) $documentosAssinados   não foram assinado(s).ERROR:$msgError", 'status' => 'error' ) );
					$this->_helper->_redirector ( 'entrada', 'caixaunidade', 'sisad' );
				} else {
					$this->_helper->flashMessenger ( array ('message' => "Documento(s) $documentosAssinados  foram assinado(s).", 'status' => 'success' ) );
					$this->_helper->_redirector ( 'entrada', 'caixaunidade', 'sisad' );
				}
			}
		}
		
		$documentosAssinar = array ();
		$this->view->title = "Assinar Documentos do processo";
		
		$data = $this->_getAllParams ();
		
		$dados_input = Zend_Json::decode ( $data ['documento'] [0] );
		
		$solicitacaoDados = Zend_Json::decode ( $DadosSolic->solicitacaoDados [0] );
		$this->view->processonr = ($dados_input ['DOCM_NR_DOCUMENTO']) ? ($dados_input ['MASC_NR_DOCUMENTO']) : ($solicitacaoDados ['MASC_NR_DOCUMENTO']); //SE ESTIVER VAZIO PEGUE O VALOR DA SESSÃO
		$docsNumero = new Zend_Session_Namespace ( 'docsAssinar' );
		$SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital ();
		$DocumentoID = ($dados_input ['DOCM_ID_DOCUMENTO']) ? ($dados_input ['DOCM_ID_DOCUMENTO']) : ($solicitacaoDados ['DOCM_ID_DOCUMENTO']); //SE ESTIVER VAZIO PEGUE O VALOR DA SESSÃO
		$mofaIDMovimentacao = ($dados_input ['MOFA_ID_MOVIMENTACAO']) ? ($dados_input ['MOFA_ID_MOVIMENTACAO']) : ($solicitacaoDados ['MOFA_ID_MOVIMENTACAO']); //SE ESTIVER VAZIO PEGUE O VALOR DA SESSÃO
		$rows = $SadTbPrdiProcessoDigital->getdocsProcesso ( $DocumentoID );
		$fim = count ( $rows );
		for($i = 0; $i < $fim; $i ++) {
			$rows [$i] ['DADOS_INPUT'] = Zend_Json::encode ( $rows [$i] );
		}
		
		$paginator = Zend_Paginator::factory ( $rows );
		
		$paginator->setCurrentPageNumber ( $page )->setItemCountPerPage ( );
		

		$this->view->ordem = $order_column;
		$this->view->direcao = $order_direction;
		$this->view->data = $paginator;
		$this->view->banco = $userNs->bancoUsuario;
		
		$this->view->DocumentosProcesso = $paginator;
		
		$form->getElement ( 'COU_COD_MATRICULA' )->setValue ( $userNs->matricula );
		$form->getElement ( 'MOFA_ID_MOVIMENTACAO' )->setValue ( $mofaIDMovimentacao );
		
		$this->view->formVerificar = $form;
	}
        
	function verificaassinanteAction() {
            $formVerificar = new Sisad_Form_VerificaUsuario ();            
            $userNs = new Zend_Session_Namespace('userNs');
            
            $authAdapter = new App_Auth_Adapter_Db ();
            $authAdapter->setIdentity($userNs->matricula);
            $authAdapter->setCredential($this->_getParam ( 'COU_COD_PASSWORD' ));
            $authAdapter->setDbName($userNs->bancoUsuario);
            $auth = Zend_Auth::getInstance();

            $result = $auth->authenticate ( $authAdapter );
            $messageLogin = $result->getMessages ();
 
            $this->view->verifica = ($result->isValid());
	}
	
	public function verificaparteacesso($documento) {
		
		$SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc ();
		$verifica = $SadTbPapdParteProcDoc->verificaPermissaoCadastroVistas ( $documento );
		return $verifica;
	}

}
