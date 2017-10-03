<?php
class Sisad_PesquisadcmtoController extends Zend_Controller_Action
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
		
        /**/
		$this->view->titleBrowser = 'e-Sisad';
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
        
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

       
    public function pesquisaDocEmailAction(){

        $formP = new Sisad_Form_Pesquisadcmto();
        $data = $this->_getParam('DOCM_ID_DOCUMENTO');
        $formP->DOCM_ID_DOCUMENTO->setValue($data);
       
        $this->view->form = $formP;
    }
    public function indexAction()
    {  
        $form = new Sisad_Form_Pesquisadcmto();
        if($this->getRequest()->isGet()){
            $data = $this->_getParam('DOCM_ID_DOCUMENTO');
             $form->DOCM_ID_DOCUMENTO->setValue($data);
        }
         
        $formParte = new Sisad_Form_Partes();
        $this->view->formParte = $formParte;
        $aDocumento = new Zend_Session_Namespace('documentoNs');
        $Ns_Pesquisardocumento_index = new Zend_Session_Namespace('Ns_Pesquisardocumento_index');
        $form_valores_padrao = $form->getValues();
        
        if($this->_getParam('nova')=== '1'){
                unset($Ns_Pesquisardocumento_index->data_pesq);
                $Request = $this->getRequest();
                $module = $Request->getModuleName();
                $controller = $Request->getControllerName();
                $action = $Request->getActionName();
                $this->_redirect($module.'/'.$controller.'/'.$action);
        }
     
        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            $form->populate ($data_pesq);
            $partes = count($data_pesq['partes_pessoa_trf'])+count($data_pesq['partes_pess_ext'])+count($data_pesq['partes_pess_jur'])+count($data_pesq['partes_unidade']);
            if ($partes == 0){
                if($form_valores_padrao == $form->getValues()){
                    $this->view->form = $form;
                    $this->view->title = "Pesquisar Documentos e Processos";
                    $msg_to_user = "O preenchimento de um dos campos de pesquisa é necessário.";
                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView .= $msg_to_user;
                    return;
                }
            }
            if($form->isValid($data_pesq)){
              $Ns_Pesquisardocumento_index->data_pesq = $this->getRequest()->getPost();
                
            }else{
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "Pesquisar Documentos e Processos";
                return;
            }
        }
        $data_pesq = $Ns_Pesquisardocumento_index->data_pesq;
        if(! is_null($data_pesq) ){
            $this->view->ultima_pesq = true;
            
            /*paginação*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');

            $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
            $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade ();
            
            $mapperDocumento = new Sisad_Model_DataMapper_Documento();
            
            try {
                $count = $mapperDocumento->getPesquisaDocumentoCount($Ns_Pesquisardocumento_index->data_pesq,$order);
            } catch (Exception $msg_to_user) {
                $msg_to_user = 'Não foi possível localizar o documento';
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            } 
                     
            if($count[0]["COUNT"] <= 200){
                $rows = $mapperDocumento->getPesquisaDocumento($Ns_Pesquisardocumento_index->data_pesq,$order);
            }else{
                $this->view->form = $form;
                $this->view->title = "Pesquisar Documentos";
                $msg_to_user = "A pesquisa retornou ".$count[0]["COUNT"].". Ultrapassou o maximo de 200 registros.  <br/> Informe mais parâmetros de pesquisa. <br/> Por Exemplo, limite um período de tempo.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
            
            if (($count[0]["COUNT"] == 0) && ($data_pesq["DOCM_ID_DOCUMENTO"] != "")) {
                $rascunho = $mapperDocumento->getDocumentoRacunho($data_pesq["DOCM_ID_DOCUMENTO"]);
                if ($rascunho[0]["ENCAMINHADOR"]) {
                    $this->view->form = $form;
                    $this->view->title = "Pesquisar Documentos";
                    $this->view->sucesso = true;
                    $msg_to_user = 'O documento pesquisado se encontra na caixa de rascunho do Sr (a) ' . $rascunho[0]["ENCAMINHADOR"] . '<br/> Lotação : ' . $rascunho[0]["LOTACAO"];
                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView .= $msg_to_user;
                    return;
                } else {
                    $minuta = $mapperDocumento->getMinutaDocmNr($data_pesq["DOCM_ID_DOCUMENTO"]);
                    if ($minuta[0]["LOTACAO"]) {
                        $this->view->form = $form;
                        $this->view->title = "Pesquisar Documentos";
                        $this->view->sucesso = true;
                        $msg_to_user = 'O documento pesquisado trata-se de uma minuta e se encontra na caixa de minutas do Sr (a) ' . $minuta[0]["ENCAMINHADOR"] . '<br/> Lotação : ' . $minuta[0]["LOTACAO"];
                        $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView .= $msg_to_user;
                        return;
                    }
                }
           }
            
            /*verifica condições e faz tratamento nos dados*/
            //$TimeInterval = new App_TimeInterval();
            $fim =  count($rows);
            $TimeInterval = new App_TimeInterval();
            $fim =  count($rows);
                     
            $dados_cx_entrada = new Application_Model_DbTable_SadTbMoviMovimentacao();
            $permissoes = $AcessoCaixaUnidade->getAcessoCaixaUnidade ( $AcessoCaixaUnidade->getMatricula () );
            
            $caixa_entrada = array();
            foreach($permissoes as $caixa){
                $codlotacao = $caixa["LOTA_COD_LOTACAO"];
                $siglasecao = $caixa["LOTA_SIGLA_SECAO"];
                $caixa_entrada[$i] = $dados_cx_entrada->getCaixaUnidadeRecebidos($codlotacao,$siglasecao,$order);
                $i++;
            }
            
            for ($i = 0; $i<$fim; $i++ ) {
                $enderecado = $SadTbPrdcProtDocProcesso->getEnderecamento($rows[$i]["DOCM_ID_DOCUMENTO"]);
                $protocolo = $SadTbPrdcProtDocProcesso->getProtocolado($rows[$i]["DOCM_ID_DOCUMENTO"]);
                
                /*Verifica permissoes do usuario para ir a determinada caixa*/
                $num_perm = count($permissoes); 
                $x=0;
                while ($x <= $num_perm){
                    if(($permissoes[$x]["LOTA_COD_LOTACAO"] == $rows[$i]["MODE_CD_SECAO_UNID_DESTINO"] && $permissoes[$x]["LOTA_SIGLA_SECAO"] == $rows[$i]["MODE_SG_SECAO_UNID_DESTINO"]) && $rows[$i]["DOCM_IC_ARQUIVAMENTO"]=="N" && $rows[$i]["DOCM_IC_ATIVO"]=="S"){
                       $rows[$i]["IR_CAIXA"] = "Sim";
                       $rows[$i]["PERMISSAO_LOTA_COD_LOTACAO"] = $permissoes[$x]["LOTA_COD_LOTACAO"];
                       $x = 10000;/*Força saida do loop pois ja achou se ja tem permissao*/
                    }
                    $x++;
                }
                
                /*Verifica se o documento existe na caixa de entrada [atual] da unidade*/
                foreach($caixa_entrada as $caixas){
                    foreach($caixas as $documentos){
                        if(($documentos["DOCM_ID_DOCUMENTO"] == $rows[$i]["DOCM_ID_DOCUMENTO"]) && $rows[$i]["DOCM_IC_ARQUIVAMENTO"]=="N" && $rows[$i]["DOCM_IC_ATIVO"]=="S"){
                           $rows[$i]["ESTA_NA_CAIXA"] = "Sim";
                           $rows[$i]["PERMISSAO_LOTA_COD_LOTACAO"] = $permissoes[$x]["LOTA_COD_LOTACAO"];
                        }
                    }
                }
                               
                if ($enderecado){
                    $rows[$i]['MSG_ENDERECADO'] = "Endereçado Para Postagem";
                    $rows[$i]['ENDERECADO'] = "enderecado";
                 }
                 if ($protocolo){
                    $nrs = null;
                    $qtdProt = count($protocolo);
                    $cont = 1;
                    foreach ($protocolo as $value) { 
                        if($cont == $qtdProt){
                            $nrs = $nrs.$value["PRDC_ID_PROTOCOLO"];
                        }else{
                            $nrs = $nrs.$value["PRDC_ID_PROTOCOLO"].' , ';
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

                if (is_null($rows[$i]["DOCM_NR_DOCUMENTO_RED"])){
                    $rows[$i]['MSG_ARQUIVO'] = "Adicionar o arquivo";
                    $rows[$i]['CLASS_ARQUIVO'] = "alertaButton";
                }else{
                    $rows[$i]['MSG_ARQUIVO'] = "Abrir Documento";
                    $rows[$i]['CLASS_ARQUIVO'] = "abrirAnexo";
                }
                $rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOVI_DH_ENCAMINHAMENTO_CHAR']); 

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
                      ->setItemCountPerPage(100);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            
            $form->populate($data_pesq);
        }
        $this->view->form = $form;
        $this->view->title = "Pesquisar Documentos e Processos";
    }

    public function setsolicitardocumentoAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sisad_Form_SolicitarDocumento();
        $this->view->title = 'Solicitar Documento/Processo/Vistas';

        $data = $this->getRequest()->getPost();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($data)) {
                $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                $historicoDOCM = $mapperDocumento->getHistoricoDCMTO($data["DOCM_ID_DOCUMENTO"]);
                if (is_null($historicoDOCM[0]["MODP_CD_MAT_PESSOA_DESTINO"])) {
                    $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                    $OcsTbUnpeUnidadePerfil1 = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
                    $responsaveis = $OcsTbUnpeUnidadePerfil1->getPessoasComPerfilX(9, $historicoDOCM[0]['MODE_SG_SECAO_UNID_DESTINO'], $historicoDOCM[0]['MODE_CD_SECAO_UNID_DESTINO']);
                    foreach ($responsaveis as $dadosresp) {
                        $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                        $matricula = $dadosresp['PMAT_CD_MATRICULA'];
                        $titulo = 'Solicitação de Documentos/Processos/Vistas';
                        $sistema = 'SISAD';
                        $mensagem = $data["DESCRICAO_USUARIO"];
                        $notificacao = $tabelaNotf->setnotfAction($matricula, $titulo, $sistema, $mensagem);
                        /**
                         * Envia e-mail de notificação para as pessoas responsáveis
                         */
                        $email = new Application_Model_DbTable_EnviaEmail();
                        $sistemaEmail = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
                        $remetente = 'noreply@trf1.jus.br';
                        $destinatario = $matricula.'@trf1.jus.br';
                        $mensagemEmail = "Documento nº " . $historicoDOCM[0]['DOCM_NR_DOCUMENTO'] . "<br />
                                          Solicitante: ".$userNs->matricula." - ".$userNs->nome." <br />
                                          Descrição da Solicitação: ".$mensagem;

                        $email->setEnviarEmail($sistemaEmail, $remetente, $destinatario, $titulo, $mensagemEmail);
                    }
                    $msg_to_user = "Documento nº " . $historicoDOCM[0]['DOCM_NR_DOCUMENTO'] . " solicitado com sucesso à " . $historicoDOCM[0]['LOTA_DSC_LOTACAO_DESTINO'] . " - " . $historicoDOCM[0]['FAMILIA_DESTINO'] . ".";
                } else {
                    $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                    $matricula = $historicoDOCM[0]['MOFA_CD_MATRICULA'];
                    $titulo = 'Solicitação de Documentos/Processos/Vistas';
                    $sistema = 'SISAD';
                    $mensagem = $data["DESCRICAO_USUARIO"];
                    $notificacao = $tabelaNotf->setnotfAction($matricula, $titulo, $sistema, $mensagem);
                     /**
                      * Envia e-mail de notificação para quem está com o documento
                      */
                    $email = new Application_Model_DbTable_EnviaEmail();
                    $sistemaEmail = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
                    $remetente = 'noreply@trf1.jus.br';
                    $destinatario = $matricula.'@trf1.jus.br';
                    $mensagemEmail = "Documento nº " . $historicoDOCM[0]['DOCM_NR_DOCUMENTO'] . "<br />
                                      Solicitante: ".$userNs->matricula." - ".$userNs->nome." <br />
                                      Descrição da Solicitação: ".$mensagem;

                    $email->setEnviarEmail($sistemaEmail, $remetente, $destinatario, $titulo, $mensagemEmail); 
                    $msg_to_user = "Documento nº " . $historicoDOCM[0]['DOCM_NR_DOCUMENTO'] . " solicitado com sucesso. ";
                }
                $faseSolicitar = $mapperDocumento->solicitarDocumento($historicoDOCM[0]['MOFA_ID_MOVIMENTACAO'],
                                                                         $userNs->matricula,
                                                                         $mensagem);
               
                if ($notificacao && $faseSolicitar) {
                    $this->_helper->flashMessenger ( array('message' => "$msg_to_user", 'status' => 'success'));
                    $this->_helper->_redirector('index', 'pesquisadcmto', 'sisad');
                } else {
                    $msg_to_user = "Não foi possível solicitar o documento/processo/vistas. Erro:" . $notificacao . $faseSolicitar;
                    $this->_helper->flashMessenger ( array('message' => "$msg_to_user", 'status' => 'error'));
                    $this->_helper->_redirector('index', 'pesquisadcmto', 'sisad');
                }
            } else {
                $this->view->form = $form;
            }
        } else {
            $id = $this->getRequest()->getParam('id');
            $mapperDocumento = new Sisad_Model_DataMapper_Documento();
            $dadosDocm = $mapperDocumento->getDadosDCMTO($id);
            $this->view->dadosDocm = $dadosDocm;
            $form->populate($dadosDocm);
            $this->view->form = $form;
        }
    }

    public function pesquisadocumentoemprocessoAction() {
        $this->view->title = 'Pesquisa de Documentos em Processos';
        $dataRequest = $this->getRequest()->getPost();
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $rn_Processo = new Trf1_Sisad_Negocio_Processo();
        $form = new Sisad_Form_Pesquisadcmto();
        $dataRequest['ID_PROCESSO'] ? ($retornoIdProc = $mapperDocumento->getIdProcesso($dataRequest['ID_PROCESSO'])) : ($retornoIdProc = $dataRequest["dcpr_id"]);
        is_array($retornoIdProc) ? ($IdProcesso = $retornoIdProc[0]['DCPR_ID_PROCESSO_DIGITAL']) : ($IdProcesso = $retornoIdProc);
        $hidden = $form->createElement('hidden', 'dcpr_id', array('value' => $IdProcesso));
        $form->addElement($hidden);
        $NsPesqDocumentoEmProcesso = new Zend_Session_Namespace('NsPesqDocumentoEmProcesso');
        if ($this->getRequest()->isPost() && $dataRequest["Pesquisar"] == "Pesquisar") {
            $this->view->ultima_pesq = true;
            $dcpr_id = $dataRequest["dcpr_id"];
            $retornoPesq = $mapperDocumento->getPesquisaDocumentoEmProcesso($dataRequest, $dcpr_id);
            $this->view->retornoPesq = $retornoPesq;
            $this->view->dados_documento = $NsPesqDocumentoEmProcesso->dados_documento;
            $this->view->form = $form->populate($dataRequest);
            return $this->render('pesquisadocumentoemprocesso');
        } else {
            $detalhes = $rn_Processo->getDocumento($dataRequest['ID_PROCESSO']);
            $NsPesqDocumentoEmProcesso->dados_documento = $detalhes;
            $this->view->dados_documento = $detalhes;
            $this->view->form = $form;
        }
    }
    
 }

