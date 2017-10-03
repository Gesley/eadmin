<?php
class Sisad_AutuarController extends Zend_Controller_Action {
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
		
		$this->view->titleBrowser = 'e-Sisad';
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction()
    {


    }
    
    public function ajaxjuizesdesembargadoresAction()
    {
        $matriculanome     = $this->_getParam('term','');
        
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $juizesdesembargadores = $OcsTbPmatMatricula->getJuizeseDesembargadores($matriculanome);
        
        $fim =  count($juizesdesembargadores);
        for ($i = 0; $i<$fim; $i++ ) {
            $juizesdesembargadores[$i] = array_change_key_case ($juizesdesembargadores[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($juizesdesembargadores);
    }
    
    public function ajaxservidoresAction()
    {
        $matriculanome     = $this->_getParam('term','');
        
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $sevidores = $OcsTbPmatMatricula->getServidores($matriculanome);
        
        $fim =  count($sevidores);
        for ($i = 0; $i<$fim; $i++ ) {
            $sevidores[$i] = array_change_key_case ($sevidores[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($sevidores);
    }
    
    public function ajaxpessoaspartesAction()
    {
        $matriculanome     = $this->_getParam('term','');
        
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $sevidores = $OcsTbPmatMatricula->getPessoasPartes($matriculanome);
        
        $fim =  count($sevidores);
        for ($i = 0; $i<$fim; $i++ ) {
            $sevidores[$i] = array_change_key_case ($sevidores[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($sevidores);
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
    
    public function autuarAction(){
        
     
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Atuar_autuar = new Zend_Session_Namespace('Ns_Atuar_autuar');
        
        $formAutuardcmto = new Sisad_Form_Autuardcmto();
        $this->view->formParte = new Sisad_Form_Partes();
        $this->view->formAutuardcmto = $formAutuardcmto;
        
        $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
//        Zend_Debug::dump($SadTbPrdiProcessoDigital->fetchNew()->toArray());
        $SadTbDcprDocumentoProcesso = new Application_Model_DbTable_SadTbDcprDocumentoProcesso();
//        Zend_Debug::dump($SadTbDcprDocumentoProcesso->fetchNew()->toArray());


        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        
 //       Zend_Debug::dump($Ns_Atuar_autuar->data_post_caixa); exit;
        
        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            
 //         Zend_Debug::dump($data); exit;
            
            if(isset($data['acao']) && $data['acao'] == 'Autuar' || ! is_null($Ns_Atuar_autuar->data_post_caixa )){
                $data_post_caixa = $data;
                
                if(isset($data['acao']) && $data['acao'] == 'Autuar'){
                    $Ns_Atuar_autuar->data_post_caixa = $data_post_caixa;
                }else if(! is_null($Ns_Atuar_autuar->data_post_caixa )){
                    $data_post_caixa =  $Ns_Atuar_autuar->data_post_caixa;
                }
                
  //            Zend_Debug::dump($data_post_caixa['documento'] ); 
                
                /*paginacao*/
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                /*Ordenacao das paginas*/
                $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
                $order_direction = $this->_getParam('direcao', 'DESC');
                $order = $order_column.' '.$order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                /*Ordenacao*/
                
                $cont = 0;
                $rows = array();
                foreach ($data_post_caixa['documento'] as $value) {
                    $rows['documento'][$cont] = Zend_Json::decode($value);
                    $doc = $rows['documento'][$cont];
                    
                    //verifico se o usuario tem permissao no documento nao publico para autua-lo
                    if( in_array( $doc['DOCM_ID_CONFIDENCIALIDADE'], array("1","3","4")) ){
                             $verifica = $SadTbPapdParteProcDoc->verificaParteVista($doc['DOCM_ID_DOCUMENTO'], null, 3);
                             if(!$verifica){
                                //retiro o documento da listagem caso o usuario nao tenha vista, logo ele nao pode autuar processo sigiloso onde nao possua vistas.
                                unset($rows['documento'][$cont]);
                             }
                    }
                   //Verifico permissao da Corregedoria
                    if( $doc['DOCM_ID_CONFIDENCIALIDADE'] == "5"){
                         $usuarioCorregedoria = $SadTbPapdParteProcDoc->verificaPermissaoCorregedoria();
                        if( empty($usuarioCorregedoria) ){
                            unset($rows['documento'][$cont]);
                        }
                    } 
                    
                    $cont++;
                }
                
                /**
                 * Recuperando a unidade da caixa
                 */
                $documentos = array();
                $i= 0;
                foreach ($data_post_caixa['documento'] as $value) {
                    $dados_input = Zend_Json::decode($value);
                    //Zend_Debug::dump($dados_input);
                    $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                    $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                    $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
                    $i++;
                }
              //  Zend_Debug::dump($rows['documento']); exit;
                if($rows['documento']){
                    $paginator = Zend_Paginator::factory($rows['documento']);
                    $paginator->setCurrentPageNumber($page)
                               ->setItemCountPerPage(count($rows['documento']));

                   //Zend_Debug::dump(count($rows['documento']));
                   //Zend_Debug::dump($rows['documento']);
                    $this->view->ordem = $order_column;
                    $this->view->direcao = $order_direction;
                    $this->view->data = $paginator;
                    Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                    $this->view->title = "Autuar Processo - $siglalotacao";
                }else{
                    $this->_helper->flashMessenger ( array('message' => 'É necessário escolher um documento em que possua permissão para Autuar o Processo.', 'status' => 'notice'));
                    return $this->_helper->_redirector($data_post_caixa['action'], $data_post_caixa['controller'], 'sisad');
                }
            }
            
            
            if(isset($data['acao']) && $data['acao'] == 'FormAutuar' ){
//             Zend_Debug::dump($data); exit;
              //  Zend_debug::dump($formAutuardcmto->getErrors());
                if ( !$formAutuardcmto->isValid($data) ) {
                    $formAutuardcmto->populate($data);
                    $this->view->formAutuardcmto = $formAutuardcmto;
                    return;
                }
                
                
                $data_post_caixa = $Ns_Atuar_autuar->data_post_caixa;
                
                Zend_Debug::dump($data_post_caixa['documento']);
                    
                /*
                 * trada os dados do post para inserir no banco
                 */
                $documentos = array();
                $i= 0;
                foreach ($data_post_caixa['documento'] as $value) {
                    $dados_input = Zend_Json::decode($value);
                    Zend_Debug::dump($dados_input);
                    
                    $sg_secao_unid = $dados_input["MODE_SG_SECAO_UNID_DESTINO"]; 
                    $cd_secao_unid = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                    
                    $dataDocumentos_vicular[$i]["DOCM_ID_DOCUMENTO"] = $dados_input["DOCM_ID_DOCUMENTO"];
                    $i++;
                }
                
/*                Zend_Debug::dump($data['partes_pessoa']); 
                Zend_Debug::dump($data['partes_lotacao']);  exit;
*/                   
                 
                  $dataPartePessoa = array();
                  $dataParteLotacao = array();
                  $dataPartePessExterna = array();
                  $dataPartePessJur = array();
                  $dataDocumento = array();
         
                    if( count($data['partes_pessoa_trf']) ){
                            $dataPartePessoa = $data['partes_pessoa_trf'];
                    }
                    if( count($data['partes_unidade']) && $data['DOCM_ID_CONFIDENCIALIDADE'] != "4"){ //se for doc sigiloso nao pode ter unidade como interessada
                            $dataParteLotacao = $data['partes_unidade'];
                    }
                    if( count($data['partes_pess_ext']) ){
                            $dataPartePessExterna = $data['partes_pess_ext'];
                    }
                    if( count($data['partes_pess_jur']) ){
                            $dataPartePessJur = $data['partes_pess_jur'];
                    }
                
                
 //               Zend_Debug::dump($dataDocumentos_vicular,"DOCUMENTOS PARA VICULAR"); exit;
                
                $matricula = $userNs->matricula;
//                Zend_Debug::dump($formAutuardcmto->acao);
                //$dataPrdiProcessoDigital["PRDI_ID_PROCESSO_DIGITAL"] = '';
                //$dataPrdiProcessoDigital["PRDI_DH_AUTUACAO"] = '';
                $dataPrdiProcessoDigital["PRDI_CD_MATR_AUTUADOR"] = $matricula;
                $dataPrdiProcessoDigital["PRDI_SG_SECAO_AUTUADORA"] = $sg_secao_unid;
                $dataPrdiProcessoDigital["PRDI_CD_UNID_AUTUADORA"] = $cd_secao_unid;

                $arr_matr_serv_relator = explode(' - ',$data['PRDI_CD_MATR_SERV_RELATOR']);
                $data['PRDI_CD_MATR_SERV_RELATOR'] = $arr_matr_serv_relator[0];  
                $dataPrdiProcessoDigital["PRDI_CD_MATR_SERV_RELATOR"] = $data['PRDI_CD_MATR_SERV_RELATOR'];
                
                $dataPrdiProcessoDigital["PRDI_ID_AQVP"] = $data['PRDI_ID_AQVP'];
                $dataPrdiProcessoDigital["PRDI_DS_TEXTO_AUTUACAO"] = new Zend_Db_Expr( " CAST( '". $data['PRDI_DS_TEXTO_AUTUACAO'] ."' AS VARCHAR(4000)) " );
                
                
                $arr_juiz_relator_processo = explode(' - ',$data['PRDI_CD_JUIZ_RELATOR_PROCESSO']);
                $data['PRDI_CD_JUIZ_RELATOR_PROCESSO'] = $arr_juiz_relator_processo[0];
                $dataPrdiProcessoDigital["PRDI_CD_JUIZ_RELATOR_PROCESSO"] = $data['PRDI_CD_JUIZ_RELATOR_PROCESSO'];
                
                //$dataPrdiProcessoDigital["PRDI_DH_DISTRIBUICAO"] = '';
                //$dataPrdiProcessoDigital["PRDI_CD_MATR_DISTRIBUICAO"] = '';
                $dataPrdiProcessoDigital["PRDI_IC_TP_DISTRIBUICAO"] = 'DA';
                
                if($data["DOCM_ID_CONFIDENCIALIDADE"] == '4' ){
                    $dataPrdiProcessoDigital["PRDI_IC_SIGILOSO"] = 'S';
                }else{
                    $dataPrdiProcessoDigital["PRDI_IC_SIGILOSO"] = 'N';
                }
                $dataPrdiProcessoDigital["PRDI_IC_CANCELADO"] = 'N';
                
                //$dataSadTbDocmDocumento["DOCM_ID_DOCUMENTO"] = '';
                //$dataSadTbDocmDocumento["DOCM_NR_DOCUMENTO"] = '';
                //$dataSadTbDocmDocumento["DOCM_NR_SEQUENCIAL_DOC"] = '';
                //$dataSadTbDocmDocumento["DOCM_NR_DCMTO_USUARIO"] = '';
                //$dataSadTbDocmDocumento["DOCM_DH_CADASTRO"] = $datahora;
                $dataSadTbDocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] = $matricula;
                /**
                 * Tipo de Documento 
                 * DSV - 152 TIPO PROCESSO ADMINISTRATIVO
                 */
                $dataSadTbDocmDocumento["DOCM_ID_TIPO_DOC"] = 152;
                $dataSadTbDocmDocumento["DOCM_SG_SECAO_GERADORA"] = $dataPrdiProcessoDigital["PRDI_SG_SECAO_AUTUADORA"];
                $dataSadTbDocmDocumento["DOCM_CD_LOTACAO_GERADORA"] = $dataPrdiProcessoDigital["PRDI_CD_UNID_AUTUADORA"];
                $dataSadTbDocmDocumento["DOCM_SG_SECAO_REDATORA"] = $dataPrdiProcessoDigital["PRDI_SG_SECAO_AUTUADORA"];
                $dataSadTbDocmDocumento["DOCM_CD_LOTACAO_REDATORA"] =  $dataPrdiProcessoDigital["PRDI_CD_UNID_AUTUADORA"];
                $dataSadTbDocmDocumento["DOCM_ID_PCTT"] = $data['PRDI_ID_AQVP'];
                $dataSadTbDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = $data['PRDI_DS_TEXTO_AUTUACAO'];
                $dataSadTbDocmDocumento["DOCM_ID_TIPO_SITUACAO_DOC"] = $data['DOCM_ID_TIPO_SITUACAO_DOC'];
                $dataSadTbDocmDocumento["DOCM_ID_CONFIDENCIALIDADE"] = $data['DOCM_ID_CONFIDENCIALIDADE'];
                unset($dataSadTbDocmDocumento["DOCM_NR_DOCUMENTO_RED"]);
                unset($dataSadTbDocmDocumento["DOCM_DH_EXPIRACAO_DOCUMENTO"]);
                $dataSadTbDocmDocumento["DOCM_DS_PALAVRA_CHAVE"] = $data['DOCM_DS_PALAVRA_CHAVE'];
                $dataSadTbDocmDocumento["DOCM_IC_PROCESSO_AUTUADO"] = 'N';
                
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {
                    $dataProcesso = array();
                    $dataProcesso = $SadTbPrdiProcessoDigital->autuarProcesso($dataSadTbDocmDocumento, $dataPrdiProcessoDigital,$dataDocumentos_vicular);
                    $dataProcesso['replicaVistas'] = $data['REPLICA_VISTAS'];
                    
                    if( count($dataPartePessoa) ||  count($dataParteLotacao) || count($dataPartePessExterna) || count($dataPartePessJur) ){
                        $dataProcesso = $SadTbPapdParteProcDoc->adicionaPartesDocmProc($dataPartePessoa, $dataParteLotacao, $dataPartePessExterna, $dataPartePessJur, $dataProcesso, $dataProcesso, false);
                    }
                    
                    $db->commit();
                    
                    $msg_to_user = "Processo Autuado.";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                    return $this->_helper->_redirector($data_post_caixa['action'],$data_post_caixa['controller'],'sisad'); 
                    
                } catch (Exception $exc) {
                    
                    $db->rollBack();
                    $msg_to_user = 'Não foi possivel autuar o processo: <br> <p>'.strip_tags($exc->getMessage()).'</p>';
                    //Zend_Debug::dump($msg_to_user);exit;
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                    return $this->_helper->_redirector($data_post_caixa['action'],$data_post_caixa['controller'],'sisad'); 
                }
                   
            }
        } // fim isPost()
    }
    
    public function adicionardocsproAction()
    {
        /**
         * Variáves de seção
         */
        $userNs = new Zend_Session_Namespace('userNs');
        /**
         * Form
         */
        $formParecer = new Sisad_Form_Parecer();
        /**
         * Tratamentos para reutilização do form de parecer
         */
        $mofa_ds_complemento = $formParecer->getElement('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setLabel('Justificativa de inclusão/Parecer:');
        $acao = $formParecer->getElement('acao');
        $acao->setValue('submitAdicionarDocPro');
        $formParecer->removeElement('Salvar');
        $Adicionar = new Zend_Form_Element_Submit('Adicionar');
        $formParecer->addElement($Adicionar);
        
        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
                /**
                * Verifica se Ã© a ação de submeter do form
                */
            if(isset($data['acao']) && $data['acao'] == 'submitAdicionarDocPro' ){
                if($formParecer->isValid($data)){
                    $dadosProcesso['processo'] = $data['processo'];
                    $dadosProcesso['obs'] = $data['MOFA_DS_COMPLEMENTO'];
                    
                    $documentos = array();
                    $i= 0;
                    foreach ($data['documento'] as $value) {
                        $dados_input = Zend_Json::decode($value);
                        $dataDocumentos_vicular[$i]["DOCM_ID_DOCUMENTO"] = $dados_input["DOCM_ID_DOCUMENTO"];
                        $i++;
                    }
                    /**
                     * Abrindo a trasaçãoo persistir os dados nas tabelas
                     */ 
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    $SadTbPrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital(); 
                    try {
                        $SadTbPrdiProcessoDigital->addDocsProcesso($dadosProcesso, $dataDocumentos_vicular);
                        $db->commit();
                        //$db->rollBack();
                    } catch (Exception $exc) {
                        $db->rollBack();
                        $erro =  $exc->getMessage();

                        $msg_to_user = "Ocorreu um erro ao adicionar os documentos <br/> $erro";
                        $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                        return $this->_helper->_redirector('entrada','caixaunidade','sisad');

                    }
                    $msg_to_user = "Documentos adicionados";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                    return $this->_helper->_redirector('entrada','caixaunidade','sisad');
                }else{
                    /**
                     * Tratamentos para dados inválidos do formulário
                     */
                    if(!$idDocmDocumentoProc){
                        $msg_to_user = "Escolha um Processo.";
                        $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                    }
                    $formParecer->populate($data);
                }
            }
            
            /**
             * Lista os documentos escolhidos no carrinho
             */

            /*paginaçãoo*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /*Ordenaçãoo das paginas*/
            $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /*Ordenação*/

            $i = 0;
            $rows = array();
            foreach ($data['documento'] as $value) {
                $rows['documento'][$i] = Zend_Json::decode($value);

                /**
                 * Tratamento para assegurar que nÃ£o se adicione um processo ao outro
                 */
                if (
                    $rows['documento'][$i]['DTPD_ID_TIPO_DOC'] === '152'
                    &&
                    $rows['documento'][$i]['DTPD_NO_TIPO'] === 'Processo administrativo'
                ) {
                    unset($rows['documento'][$i]);
                } 
                $i++;
            }

            $paginator = Zend_Paginator::factory($rows['documento']);
            $paginator->setCurrentPageNumber($page)
                       ->setItemCountPerPage(count($rows['documento']));
            $this->view->dataDocumentos = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');



            /**
             * Lista os processos da caixa de entrada da unidade
             */
            /*paginação*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /*Ordenação*/

            /**
             * Recuperando a unidade da caixa
             */
            $documentos = array();
            $i= 0;
            foreach ($data['documento'] as $value) {
                $dados_input = Zend_Json::decode($value);
                //Zend_Debug::dump($dados_input);
                $codlotacao = $dados_input["MODE_CD_SECAO_UNID_DESTINO"];
                $siglasecao = $dados_input["MODE_SG_SECAO_UNID_DESTINO"];
                $siglalotacao = $dados_input["LOTA_SIGLA_LOTACAO_DESTINO"];
                $i++;
            }

            $SadTbPrdcProtDocProcesso = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
            $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
            $rows = $dados->getCaixaUnidadeRecebidos($codlotacao,$siglasecao,$order);
            /*verifica condiÃ§Ãµes e faz tratamento nos dados */

            $fim =  count($rows);
            for ($i = 0; $i<$fim; $i++ ) {

              /**
               * Tratamento para listar somente os processos da caixa
               */  
              if( 
                    $rows[$i]['DTPD_ID_TIPO_DOC'] === '152' 
                    && 
                    $rows[$i]['DTPD_NO_TIPO'] === 'Processo administrativo' 
               ){
                  $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
               }else{
                    unset($rows[$i]);
               }
            }
            $paginator = Zend_Paginator::factory($rows);
            $paginator ->setCurrentPageNumber($page)
                       ->setItemCountPerPage(count($rows));

            $this->view->dataProcessos = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            $this->view->title = "Adicionar documentos a um processo - $siglalotacao";
            $this->view->siglalotacao = $siglalotacao;

            $this->view->formParecer = $formParecer;
        }
    }
    
    public function removerdocsproAction(){
        $server =  new Zend_Json_Server_Request_Http();
        $data = Zend_Json::decode($server->getRawJson());
        if(isset ($data)){
            $dadosDocsProc["DCPR_ID_PROCESSO_DIGITAL"] = $data["IDPROCESSO"];
            $dadosDocsProc["DCPR_ID_DOCUMENTO"] = $data["IDDOCUMENTO"];
            $dadosDocsProc["DOCM_ID_DOCUMENTO_PRINCIPAL"] = $data["IDDOCUMENTOPRINCIPAL"];
            if($data["EXCLUIR_FASE"]){
                $dadosDocsProc["MOFA_ID_MOVIMENTACAO"] = $data["DADOS_FASE"]['MOFA_ID_MOVIMENTACAO'];
                $dadosDocsProc["MOFA_DH_FASE"] = $data["DADOS_FASE"]['MOFA_DH_FASE'];
            }
            $PrdiProcessoDigital = new Application_Model_DbTable_SadTbPrdiProcessoDigital();
            $excluiDocsPro = $PrdiProcessoDigital->setExcluirDocsProc($dadosDocsProc);
            
            if($excluiDocsPro == 1){
                $DocumentosProcesso = $PrdiProcessoDigital->getdocsProcesso($dadosDocsProc["DOCM_ID_DOCUMENTO_PRINCIPAL"]);
                $documentosProcesso["DocsProcesso"] = count($DocumentosProcesso);
                $documentosProcesso["Retorno"] = $excluiDocsPro;
                $this->_helper->json->sendJson($documentosProcesso); 
            }
        }
    }
}
