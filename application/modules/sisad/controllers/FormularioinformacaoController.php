<?php
class Sisad_FormularioinformacaoController extends Zend_Controller_Action
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
		$this->view->titleBrowser = 'e-Sisad';
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction()
    {
        $this->view->title = "Formulário de Informação";
    }

   public function formAction()
    {
        $form   = new Sisad_Form_Formularioinformacao();
//        $table  = new Sisad_Model_DataMapper_Documento();
        $this->view->form = $form;
        $this->view->title = "Formulário de Informação";
    }

    public function ajaxunidadeAction()
    {
        $unidade     = $this->_getParam('term','');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $secao = $userNamespace->siglasecao;
        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $lotacao = $rhCentralLotacao->getLotacaoAjax($unidade,$secao);

        $fim =  count($lotacao);
        for ($i = 0; $i<$fim; $i++ ) {
            $lotacao[$i] = array_change_key_case ($lotacao[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($lotacao);

    }

    public function ajaxassuntodocmAction()
    {
        $assunto_p     = $this->_getParam('term','');
        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
        $assunto = $mapperPctt->getPCTTAjax($assunto_p);

        $fim =  count($assunto);
        for ($i = 0; $i<$fim; $i++ ) {
            $assunto[$i] = array_change_key_case ($assunto[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($assunto);

    }
    
    public function visualizarAction() {
        $this->view->title = "Pré-visualização de Informação";
        $form = new Sisad_Form_Formularioinformacao();

        $userNamespace = new Zend_Session_Namespace('userNs');
        $PLocalidade = new Application_Model_DbTable_PLocalidade();
        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $this->view->data = $data;
                $codpctt = $mapperPctt->getPCTTbyId($data["DOCM_ID_PCTT"]);
                $this->view->matricula = $userNamespace ->matricula;
                if($data["DOCM_CD_MATRICULA_CADASTRO"]){
                    $this->view->nome = $data["DOCM_CD_MATRICULA_CADASTRO"];
                }else{
                    $this->view->nome = $userNamespace ->nome . ' - ' . $userNamespace ->matricula;
                }
                $this->view->codpctt = $codpctt;
                $this->view->descrisaosecsubsec = $userNamespace->descrisaosecsubsec;
                $this->view->municipiosecsubsec = $userNamespace->municipiosecsubsec;


                $this->render();
                $response = $this->getResponse();
                $body = $response->getBody();
                $response->clearBody();

                //echo $body;
                $this->_helper->layout->disableLayout();
                define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
                define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
                include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
                $mpdf=new mPDF();

                $mpdf->AddPage('P', '', '0', '1');
                /*$imagem_path =  realpath(APPLICATION_PATH . '/../public/img/BrasaoBrancoRelatorio.jpg');
                //Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true, $constrain=true, $watermark=false, $shownoimg=true, $allowvector=true) {
                $mpdf->Image($imagem_path, 94, 20, 23, 22, 'jpg', '', true, true, false, false, true);
                */
                /*imagem da marda d'gua*/
                $imagem_path =  realpath(APPLICATION_PATH . '/../public/img/marcaDaguaTRFRelatorio.jpg');
                //Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true, $constrain=true, $watermark=false, $shownoimg=true, $allowvector=true) {
                $mpdf->Image($imagem_path, 90, 90, 20, 20, 'jpg', '', true, true, true, false, true);


                $mpdf->WriteHTML($body,2);

                $name =  'SISAD_TEMP_DOC_INFORMACAO_VISUALIZAR_DOCUMENTO' . date("dmYHisu") .'.pdf';

                $mpdf->Output($name,'D');

            }else{
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }

    public function saveAction() {
        $this->view->title = "Formulário de Informação";
        $form = new Sisad_Form_Formularioinformacao();
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $userNamespace = new Zend_Session_Namespace('userNs');
        $PLocalidade = new Application_Model_DbTable_PLocalidade();
        $mapperPctt = new Arquivo_Model_DataMapper_Pctt();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {

                /*gerar pdf com o mpdf*/
                $this->view->data = $data;
                $codpctt = $mapperPctt->getPCTTbyId($data["DOCM_ID_PCTT"]);
                $this->view->matricula = $userNamespace ->matricula;
                if($data["DOCM_CD_MATRICULA_CADASTRO"]){
                    $this->view->nome = $data["DOCM_CD_MATRICULA_CADASTRO"];
                }else{
                    $this->view->nome = $userNamespace ->nome . ' - ' . $userNamespace ->matricula;
                }
                $this->view->codpctt = $codpctt;
                $this->view->descrisaosecsubsec = $userNamespace->descrisaosecsubsec;
                $this->view->municipiosecsubsec = $userNamespace->municipiosecsubsec;

                $this->render();
                $response = $this->getResponse();
                $body = $response->getBody();
                $response->clearBody();
                //echo $body;

                $this->_helper->layout->disableLayout();
                define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
                define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
                include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
                $mpdf=new mPDF();
                $mpdf->AddPage('P', '', '0', '1');
                /*
                $imagem_path =  realpath(APPLICATION_PATH . '/../public/img/BrasaoBrancoRelatorio.jpg');
                //Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true, $constrain=true, $watermark=false, $shownoimg=true, $allowvector=true) {
                $mpdf->Image($imagem_path, 94, 20, 23, 22, 'jpg', '', true, true, false, false, true);
                */
                /*imagem da marda d'gua*/
                $imagem_path =  realpath(APPLICATION_PATH . '/../public/img/marcaDaguaTRFRelatorio.jpg');
                //Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true, $constrain=true, $watermark=false, $shownoimg=true, $allowvector=true) {
                $mpdf->Image($imagem_path, 90, 90, 20, 20, 'jpg', '', true, true, true, false, true);
                $mpdf->WriteHTML($body);

                /*destino de onde sera gravado o documento*/
                $dest = realpath(APPLICATION_PATH . '/../temp/');
                $name =  'SISAD_TEMP_DOC_INFORMACAO_VISUALIZAR_DOCUMENTO' . date("dmYHisu") .'.pdf';

                $name = $dest.DIRECTORY_SEPARATOR.$name;
//                Zend_Debug::dump($name); exit;

                $mpdf->Output($name,'F');

                /*setando os campos da tabela que não são obtidos no formulario*/
                unset($data["DOCM_ID_DOCUMENTO"]);
//               DIGITAL - 1 - TRF1
                 $data["DOCM_ID_TIPO_SITUACAO_DOC"] = 1;
//               PÚBLICO - 0 - TRF1
                 $data["DOCM_ID_CONFIDENCIALIDADE"] = 0;
//               INFORMACAO - 159 - TRF1
//               INFORMACAO - 157 - HML
                 $data["DOCM_ID_TIPO_DOC"] = 159;

                 $data["DOCM_DH_CADASTRO"] = new Zend_Db_Expr("SYSDATE");
                 $data["DOCM_CD_MATRICULA_CADASTRO"] = $userNamespace->matricula;

                 $aux_DOCM_CD_LOTACAO_GERADORA = $data['DOCM_CD_LOTACAO_GERADORA'];
                 $docm_cd_lotacao_geradora_array = explode(' - ', $data['DOCM_CD_LOTACAO_GERADORA']);
                 $data['DOCM_SG_SECAO_GERADORA'] = $docm_cd_lotacao_geradora_array[3];
                 $data['DOCM_CD_LOTACAO_GERADORA'] = $docm_cd_lotacao_geradora_array[2];

                 $aux_DOCM_CD_LOTACAO_REDATORA = $data['DOCM_CD_LOTACAO_REDATORA'];
                 $docm_cd_lotacao_redatora_array = explode(' - ', $data['DOCM_CD_LOTACAO_REDATORA']);
                 $data['DOCM_SG_SECAO_REDATORA'] = $docm_cd_lotacao_redatora_array[3];
                 $data['DOCM_CD_LOTACAO_REDATORA'] = $docm_cd_lotacao_redatora_array[2];

                unset($data["DOCM_NR_DOCUMENTO_RED"]);

                $data["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_ID_TIPO_DOC']);

                $data["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO($data['DOCM_SG_SECAO_REDATORA'],$data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_CD_LOTACAO_GERADORA'], $data['DOCM_ID_TIPO_DOC'], $data["DOCM_NR_SEQUENCIAL_DOC"]);
                
                $data["DOCM_DS_ASSUNTO_DOC"] = SUBSTR($data["DOCM_DS_ASSUNTO_DOC"],0,499);
                /**************************************************************************************/

                if ( file_exists($name) ) {
                        /*Inclusão do arquivo no RED*/
                        $parametros = new Services_Red_Parametros_Incluir();
                        $parametros->login = Services_Red::LOGIN_USER_EADMIN;
                        $parametros->ip = Services_Red::IP_MAQUINA_EADMIN;
                        $parametros->sistema = Services_Red::NOME_SISTEMA_EADMIN;
                        $parametros->nomeMaquina = Services_Red::NOME_MAQUINA_EADMIN;

                        $metadados = new Services_Red_Metadados_Incluir();
                        $metadados->descricaoTituloDocumento = $data["DOCM_NR_DOCUMENTO"];
                        $metadados->numeroTipoSigilo = $data['DOCM_ID_CONFIDENCIALIDADE']/*Services_Red::NUMERO_SIGILO_PUBLICO;*/;
                        $metadados->numeroTipoDocumento = $data['DOCM_ID_TIPO_DOC'];
                        $metadados->nomeSistemaIntrodutor = Services_Red::NOME_SISTEMA_EADMIN;
                        $metadados->ipMaquinaResponsavelIntervencao = Services_Red::IP_MAQUINA_EADMIN;
                        $metadados->secaoOrigemDocumento = "0100";
                        $metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
                        $metadados->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
                        $metadados->nomeMaquinaResponsavelIntervensao = Services_Red::NOME_MAQUINA_EADMIN;
                        $metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
                        $metadados->numeroDocumento = "";
                        $metadados->pastaProcessoNumero = /*"";*/ Services_Red::PASTA_PROCESSO_NUMERO_EADMIN;
                        $metadados->secaoDestinoIdSecao = "0100";

                        //$red = new Services_Red_Incluir(true); /*DESENVOLVIMENTO*/
                        $red = new Services_Red_Incluir(false); /*PRODUÇÃO*/
                        $red->debug = false;
                        $red->temp = APPLICATION_PATH . '/../temp';

                        $file = $name;/*caminho completo do arquivo gravado no servidor*/

                        $retornoIncluir_red = $red->incluir($parametros, $metadados, $file);
                        Zend_Debug::dump($parametros); Zend_Debug::dump($metadados); Zend_Debug::dump($file); Zend_Debug::dump($retornoIncluir_red);

                        if(is_array($retornoIncluir_red)){
                            try {
                                $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red['numeroDocumento'];
                                $row                           = $tabelaSadTbDocmDocumento->createRow($data);
                                $idDocumento                   = $row->save();
                                  /*Enviando o numero do documento para o email logado*/
                                $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                                 $userNs          = new Zend_Session_Namespace('userNs');
                                    $email        = new Application_Model_DbTable_EnviaEmail();
                                    $sistema      = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Adminintrativos Digitais';
                                    $remetente    = 'noreply@trf1.jus.br';
                                    $destinatario = $userNs->matricula . '@trf1.jus.br';
                                    $assunto      = 'Cadastro de Documento';
                                    $http         = $host = $_SERVER['SERVER_NAME'];
                                    $url          = $this->getFrontController()->getBaseUrl();
                                    $corpo        = "Cadastro efetuado com sucesso</p>
                                              Número do Documento: <a href=\"http://$http/$url/sisad/pesquisadcmto/pesquisa-doc-email/DOCM_ID_DOCUMENTO/" . $rowDocmDocumento['DOCM_NR_DOCUMENTO'] . "\"><b>" . $rowDocmDocumento['DOCM_NR_DOCUMENTO'] . "</b> </a><br/>";
                                    $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                            } catch (Exception $exc) {
                                $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.";
//                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView = $msg_to_user;
                                $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                                $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                                $form->populate($data);
                                $this->view->form = $form;
                                $this->render('form');
                            }
                            //echo "Inseriu no red e na tabela com sucesso";
                            $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                            $msg_to_user = "Documento cadastrado. Número do documento: ".$rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('form','Formularioinformacao','sisad');
                        } else {
                            /*tratamento de erro*/
                            $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                            $retornoIncluir_red_array["codigo"] = $retornoIncluir_red_array[0];
                            $retornoIncluir_red_array["descricao"] = $retornoIncluir_red_array[1];
                            $retornoIncluir_red_array["idDocumento"] = $retornoIncluir_red_array[2];

                            switch ($retornoIncluir_red_array["codigo"]) {

                                case 'Erro: 80':
                                    //Zend_Debug::dump($retornoIncluir_red_array);
                                    $dcmto_cadastrado = $tabelaSadTbDocmDocumento->fetchAll(array('DOCM_NR_DOCUMENTO_RED = ?' =>$retornoIncluir_red_array["idDocumento"]))->toArray();
                                    if(isset($dcmto_cadastrado[0]["DOCM_ID_DOCUMENTO"])){
                                         $msg_to_user = "Este documento já encontra-se cadastrado com o número de documento: ". $dcmto_cadastrado[0]["DOCM_NR_DOCUMENTO"].'.';
//                                        $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                         $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                         $this->view->flashMessagesView = $msg_to_user;
                                        $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                                        $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                                        $form->populate($data);
                                        $this->view->form = $form;
                                        $this->render('form');
                                    }else{
                                        try {
                                            $data["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red_array["idDocumento"];
                                            $row = $tabelaSadTbDocmDocumento->createRow($data);
                                            $idDocumento = $row->save();
                                            $rowDocmDocumento = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento");
                                            $msg_to_user = "Documento cadastrado. Número do documento: ".$rowDocmDocumento['DOCM_NR_DOCUMENTO'];
                                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                                        } catch (Exception $exc) {
                                            //echo $exc->getMessage();
                                            $msg_to_user = "Ocorreu um erro ao cadastrar os metadados do documento.";
//                                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                            $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                            $this->view->flashMessagesView = $msg_to_user;
                                            $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                                            $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                                            $form->populate($data);
                                            $this->view->form = $form;
                                            $this->render('form');
                                        }
                                    }
                                    break;
                                default:
                                    $msg_to_user = "Documento não cadastrado. Não foi possível fazer o carregamento do arquivo. ";
//                                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                                    $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                                    $this->view->flashMessagesView = $msg_to_user;
                                    $data['DOCM_CD_LOTACAO_REDATORA'] = $aux_DOCM_CD_LOTACAO_REDATORA;
                                    $data['DOCM_CD_LOTACAO_GERADORA'] = $aux_DOCM_CD_LOTACAO_GERADORA;
                                    $form->populate($data);
                                    $this->view->form = $form;
                                    $this->render('form');
                                    break;
                            }
                       }
                    }
            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }

}
