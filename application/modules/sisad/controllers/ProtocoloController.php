<?php
class Sisad_ProtocoloController extends Zend_Controller_Action
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
    }

    public function indexAction()
    {
        $this->view->title = "Protocolo de Documentos";     
    }
    
    
    /**
     * Ajax para carregar automaticamente os Orgãos externos.
     */
    public function ajaxnomedestinatarioAction()
    {
        $nomeDestinatario     = $this->_getParam('term','');
        $OcsTbPessPessoa = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $nome_array = $OcsTbPessPessoa->getNomeDestinatarioAjax($nomeDestinatario);
        $fim = count($nome_array);
        for ($i = 0; $i<$fim;$i++){
            $nome_array[$i] = array_change_key_case ($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }
    
    /**
     * Ajax para carregar automaticamente os Nomes da Tabela Pnat_Pessoa_Natural.
     */
    public function ajaxmatriculanomeAction()
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
    
    /**
     * Ajax para carregar automaticamente os Nomes da Tabela Pnat_Pessoa_Natural.
     */
    public function ajaxnomesecsubsecajaxAction()
    {
        $nomeSecao     = $this->_getParam('term','');
        $OcsTbPmatMatricula = new Application_Model_DbTable_RhCentralSecaoSubsecao();
        $nome_array = $OcsTbPmatMatricula->getSecaoSubsecaoAjax($nomeSecao);
        $fim =  count($nome_array);
        for ($i = 0; $i<$fim; $i++ ) {
            $nome_array[$i] = array_change_key_case ($nome_array[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($nome_array);
    }
    
    
    
    /**
     * Caixa de recebimento de pacotes do protocolo.
     * Cada pacode deve ser encaminhado pela unidade, e ao receber será 
     * utilizado o numero do protocolo para verificar os dados do pacote.
     * 
     */
    public function entradaAction()
    {
        $this->view->title = "Receber Documentos/Processos - Internos";
 
        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            if($data["protocolo"] ==  null){
                $this->_helper->flashMessenger (array('message' => "Insira um número de protocolo!", 'status' => 'notice'));
                $this->_helper->_redirector('entrada','protocolo','sisad');
            }
            /*paginação*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'PRDC_ID_PROTOCOLO');
            $order_direction = $this->_getParam('direcao', 'ASC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /*Ordenação*/

            $dados = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
            $rows = $dados->getDadosProtocoladosPacote($data["protocolo"]);
            $cont = 0;
            foreach ($rows as $value) {
                $rows[$cont]["DADOS_INPUT"] = Zend_Json::encode($value);
                $cont++;
            }

            if(!$rows){
                $this->_helper->flashMessenger (array('message' => "Protocolo inexistente!", 'status' => 'notice'));
                $this->_helper->_redirector('entrada','protocolo','sisad');
            }
            
            $this->view->protocolo = $data["protocolo"];
            $this->view->remetente = $rows[0]["REMETENTE"];
            
            /*verifica condições e faz tratamento nos dados */
            $fim =  count($rows);
            $TimeInterval = new App_TimeInterval();
            
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                      ->setItemCountPerPage(20);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            $this->view->title = "Recebimento de Documentos/Processos Físico - Interno";
        }
    }
    
    
    
    /**
     * Recebe os pacotes encaminhados para o protocolo de acordo com dados
     * contidos no pacote.
     */
    public function receberAction() 
    {
        if($this->getRequest()->getPost()){
            $data = $this->getRequest()->getPost();
            
            if($data["dados_input"] ==  null){
                $this->_helper->flashMessenger (array('message' => "Nenhum documento selecionado no Protocolo: ".$data["nrprotocolo"], 'status' => 'notice'));
                $this->_helper->_redirector('entrada','protocolo','sisad');
            }
            
            $prdc = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            foreach ($data["dados_input"] as $value) {
                $dados = Zend_Json::decode($value);
                try {
                    $idProcesso = $prdc->getIdProtocolo($dados["PRDC_ID_POSTAGEM_PROC_DOC"], $dados["PRDC_ID_PROTOCOLO"]);
                    $processo["PRDC_ID_PROTOCOLO"] = $dados["PRDC_ID_PROTOCOLO"];
                    $processo["PRDC_IC_RECEBIMENTO"] = 'S';
                    $processo["PRDC_DH_RECEBIMENTO_PROTOCOLO"] = new Zend_Db_Expr('SYSDATE');
                    
                    foreach ($idProcesso as $value) {
                        $row = $prdc->find($value["PRDC_ID_PROT_DOC_PROCESSO"])->current();
                        $row->setFromArray($processo);
                        $row->save();
                    }
                    
                    $db->commit();
                    $this->_helper->flashMessenger (array('message' => "Documentos Recebidos com sucesso!", 'status' => 'success'));
                    $this->_helper->_redirector('entrada','protocolo','sisad');
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger (array('message' => "Erro ao receber documentos!", 'status' => 'error'));
                    $this->_helper->_redirector('entrada','protocolo','sisad');
                }
            }
        }
    }
    
    
    
    /**
     * Carrega os documentos recebidos fisicamente pelo protocolo de acordo 
     * com o numero do protocolo. Apresenta somente os documentos recebidos 
     * fisicamente.
     */
    public function postagemAction() 
    {
        $this->view->title = "Postagem de Documentos / Processos";
        $userNamespace = new Zend_Session_Namespace('userNs');

        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            $tbPrdc = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
            $tbPost = new Application_Model_DbTable_SadTbPostPostagemProcDoc();
            
            if($data["acao"] == 'Pesquisar'){
                if($data["protocolo"] ==  null){
                    $this->_helper->flashMessenger (array('message' => "Insira um número de protocolo!", 'status' => 'notice'));
                    $this->_helper->_redirector('postagem','protocolo','sisad');
                }
                /*paginação*/
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

                /*Ordenação das paginas*/
                $order_column = $this->_getParam('ordem', 'PRDC_ID_PROTOCOLO');
                $order_direction = $this->_getParam('direcao', 'ASC');
                $order = $order_column.' '.$order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                /*Ordenação*/

                $rows = $tbPrdc->getPacotesPostagem($data["protocolo"]);

                $cont = 0;
                foreach ($rows as $value) {
                        $rows[$cont]['DADOS_INPUT'] = Zend_Json::encode($rows[$cont]);
                        $cont++;
                }
                /*verifica condições e faz tratamento nos dados */
                $fim =  count($rows);
                $TimeInterval = new App_TimeInterval();
                $this->view->protocolo = $data["protocolo"];
                $this->view->remetente = $rows[0]["REMETENTE"];

                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                          ->setItemCountPerPage(20);

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

                $this->view->title = "Postagem de Pacotes de Documentos / Processo";
            }else if($data["acao"] == 'Gravar'){
                
                $post["POST_ID_POSTAGEM_PROC_DOC"]      = (int)$data["PRDC_ID_POSTAGEM_PROC_DOC"];
                $post["POST_VR_POSTAGEM"]               = (float)$data["POST_VR_POSTAGEM"];
                $post["POST_CD_CORREIO_ENVIO"]          = $data["POST_CD_CORREIO_ENVIO"];
                $post["POST_NR_PESO_POSTAGEM_PROC_DOC"] = (int)$data["POST_NR_PESO_POSTAGEM_PROC_DOC"];
                $post["POST_ID_TIPO_POSTAGEM"]          = (int)$data["POST_ID_TIPO_POSTAGEM"];
                
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {
                    $rowProcesso = $tbPost->find($post["POST_ID_POSTAGEM_PROC_DOC"])->current();
                    unset($post["POST_ID_POSTAGEM_PROC_DOC"]);
                    $rowProcesso->setFromArray($post);
                    $rowProcesso->save();
                    $db->commit();
                    $this->_helper->flashMessenger (array('message' => "Dados de Postagem Salvos!", 'status' => 'success'));
                } catch (Exception $exc) {
                    echo $exc->getMessage();
                    $db->rollBack();
                    $this->_helper->flashMessenger (array('message' => "Erro ao salvar dados de Postagem!", 'status' => 'notice'));
                    $this->_helper->_redirector('postagem','protocolo','sisad');
                }
                /*paginação*/
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

                /*Ordenação das paginas*/
                $order_column = $this->_getParam('ordem', 'PRDC_ID_PROTOCOLO');
                $order_direction = $this->_getParam('direcao', 'ASC');
                $order = $order_column.' '.$order_direction;
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                /*Ordenação*/

                $rows = $tbPrdc->getPacotesPostagem($data["protocolo"]);

                $cont = 0;
                foreach ($rows as $value) {
                        $rows[$cont]['DADOS_INPUT'] = Zend_Json::encode($value);
                        $cont++;
                }
                
                /*verifica condições e faz tratamento nos dados */
                $TimeInterval = new App_TimeInterval();
                $this->view->protocolo = $data["protocolo"];
                $this->view->remetente = $rows[0]["REMETENTE"];

                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                          ->setItemCountPerPage(20);

                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            }else if($data["acao"] == 'Devolver'){
                Zend_Debug::dump($data);
                exit('CRIAR CONFIRMAÇÂO DE DEVOLUÇÂO COM OBS');
            }
        }
    }
    
    
    
    /*
     * Mostra os dados de postagem dos Documentos recebidos pelo protocolo.
     */
    public function postarAction()
    {

        if ($this->getRequest()->isPost()){
            
            $dados = $this->getRequest()->getPost();
            $server =  new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $this->view->data = $data;
        }
    }
    
    
    /*
     * Cadatro de Documentos físico recebidos de meios externos para encaminhar para
     * as unidades internas.
     */
    public function cadastrointernoAction()
    {
        $this->view->title = 'Cadastro de Documentos Para Envio a Lotações Internas';
        $this->_helper->flashMessenger (array('message' => "Pressione F4 para Inserir um Documento!", 'status' => 'notice'));
        $data = $this->getRequest()->getPost();
        
        if($this->getRequest()->getPost())
        {
            if($data["acao"] == 'Inserir')
            {
                $this->_helper->flashMessenger (array('message' => "Documentos Cadastrados, Documentos devem ser encaminhados ao destino!", 'status' => 'success'));
                $this->_helper->_redirector('cadastrointerno','protocolo','sisad');
            }
        }
    }
    
    
    /*
     * Modulo de cadastramento e consulta dos números de malotes cadastrados.
     * Os números cadastrados serão utilizados para postagem dos documentos.
     * 
     */
    public function maloteAction()
    {
        $tbFpdp = new Application_Model_DbTable_SadTbFpdpFaixaPostagemDoc();
        $tbMapo = new Application_Model_DbTable_SadTbMapoMalotePostagem();
        $tbSesu = new Application_Model_DbTable_RhCentralSecaoSubsecao();
        
        $userNamespace = new Zend_Session_Namespace('userNs');
        
        $data = $this->getRequest()->getPost();
        $this->view->title = 'Cadastro de Faixas de Postagem';

        
        $form = new Sisad_Form_ProtocoloAddMalote();
        $this->view->formAdd = $form;
        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            
            $destino = explode(' - ', $data["MAPO_SG_SECSUBSEC_DESTINO"]);
            $dadoSecSubsec = $tbSesu->getDadosSecSubsec($destino[0]);
            $rows = $tbMapo->getMalotes($dadoSecSubsec[0]["SESB_SIGLA_CENTRAL"], 
                                        $dadoSecSubsec[0]["SESB_SIGLA_SECAO_SUBSECAO"]);
            
            if(!$rows){
                $rows[0]['MAPO_NR_MALOTE'] = 'Nenhum Número Cadastrado';
            }
            
            /*paginação*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'MAPO_NR_MALOTE');
            $order_direction = $this->_getParam('direcao', 'ASC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /*Ordenação*/

            /*verifica condições e faz tratamento nos dados */
            $TimeInterval = new App_TimeInterval();

            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                      ->setItemCountPerPage(20);
            
            $form->populate($data);
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        }
    }

    public function addmaloteAction()
    {
        $userNamespace = new Zend_Session_Namespace('userNs');
        $tbSesu = new Application_Model_DbTable_RhCentralSecaoSubsecao();
        $tbMapo = new Application_Model_DbTable_SadTbMapoMalotePostagem();
        $data = $this->getRequest()->getPost();
        
        if($data["Salvar"] == 'Salvar')
        {
            $maloteCadastrado = $tbMapo->getMaloteCadastrado($data["MAPO_NR_MALOTE"]);
            if(!$maloteCadastrado){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {

                    $dadoSecSubsecOrigem = $tbSesu->getDadosSecSubsec($userNamespace->codsecsubsec);

                    $destino = explode(' - ', $data["MAPO_SG_SECSUBSEC_DESTINO"]);
                    $dadoSecSubsecDestino = $tbSesu->getDadosSecSubsec($destino[0]);

                    $mapo["MAPO_NR_MALOTE"] = $data["MAPO_NR_MALOTE"];
                    $mapo["MAPO_SG_CENTRAL_ORIGEM"] = $dadoSecSubsecOrigem[0]["SESB_SIGLA_CENTRAL"];
                    $mapo["MAPO_SG_SECSUBSEC_ORIGEM"] = $dadoSecSubsecOrigem[0]["SESB_SIGLA_SECAO_SUBSECAO"];
                    $mapo["MAPO_SG_CENTRAL_DESTINO"] = $dadoSecSubsecDestino[0]["SESB_SIGLA_CENTRAL"];
                    $mapo["MAPO_SG_SECSUBSEC_DESTINO"] = $dadoSecSubsecDestino[0]["SESB_SIGLA_SECAO_SUBSECAO"];
    //                $mapo["MAPO_ID_ORGAO_EXTERNO"] = $data["MAPO_ID_ORGAO_EXTERNO"];
    //                $mapo["MAPO_DS_ORGAO_EXTERNO"] = $data["MAPO_DS_ORGAO_EXTERNO"];
                    $mapo["MAPO_CD_MATRICULA"] = $userNamespace->matricula;
                    $mapo["MAPO_DH_ALTERACAO"] = new Zend_Db_Expr("TO_DATE(SYSDATE,'dd/mm/yyyy HH24:MI:SS')");
                    $mapo["MAPO_IC_TIPO_MALOTE"] = 'I';
                    if($data["MAPO_IC_ATIVO"] == 1){
                        $mapo["MAPO_IC_ATIVO"] = 'S';
                    }else{
                        $mapo["MAPO_IC_ATIVO"] = 'N';
                    }

                    $idMalote = $tbMapo->getProximoNumeroMalote();
                    $mapo["MAPO_ID_MALOTE"] = $idMalote[0]["MAX(MAPO_ID_MALOTE)"]+1;
                    
                    $cadastroMalote = $tbMapo->createRow($mapo);
                    $idcadastroMalote = $cadastroMalote->save();
                    
                    $db->commit();
                    $this->_helper->flashMessenger (array('message' => "Número de malote cadastrado!", 'status' => 'success'));
                    $this->_helper->_redirector('malote','protocolo','sisad');
                } catch (Exception $exc) {
                    $db->rollBack();
                    echo $exc->getMessage();
                    exit;
                    $this->_helper->flashMessenger (array('message' => "Problemas ao Cadastrar Número de Malote!", 'status' => 'error'));
                    $this->_helper->_redirector('malote','protocolo','sisad');
                }
            }else
            {
                $origem = $tbSesu->getNomeSecSubsec($maloteCadastrado[0]["MAPO_SG_CENTRAL_ORIGEM"], $maloteCadastrado[0]["MAPO_SG_SECSUBSEC_ORIGEM"]);
                $origem = $origem[0]["SESB_RAZAO_SOCIAL_SECAO_SUB"];
                $destino = $tbSesu->getNomeSecSubsec($maloteCadastrado[0]["MAPO_SG_CENTRAL_DESTINO"], $maloteCadastrado[0]["MAPO_SG_SECSUBSEC_DESTINO"]);
                $destino = $destino[0]["SESB_RAZAO_SOCIAL_SECAO_SUB"];
                $numero = $data["MAPO_NR_MALOTE"];
                
                $this->_helper->flashMessenger (array('message' => "Número de malote $numero já cadastrado da(o) $origem para $destino !", 'status' => 'notice'));
                $this->_helper->_redirector('malote','protocolo','sisad');
            }
        }else if($data["acao"] == 'Cadastrar Novo'){
            if($data["TIPO"] == 'addMalote'){
                $this->view->title = "Adicionar Faixa de Postagem";
                $form = new Sisad_Form_ProtocoloAddMalote();
            }
            $form->populate($data);
            $this->view->formAdd = $form;
        }
    }
    
    /*
     * Modulo de cadastramento e consulta das faixas de postagem cadastradas.
     * Os números cadastrados serão utilizados para postagem dos documentos.
     * 
     * Tabelas Utilizadas:
     * 
     * SAD_TB_FTDP_FAIXA_POSTAGEM_DOC
     * 
     */
    public function faixapostagemAction()
    {
        $tbFpdp = new Application_Model_DbTable_SadTbFpdpFaixaPostagemDoc();
        $userNamespace = new Zend_Session_Namespace('userNs');
        
        $data = $this->getRequest()->getPost();
        $this->view->title = 'Cadastro de Faixas de Postagem';

        $form = new Sisad_Form_ProtocoloAddFaixaPostagem();
        $this->view->formAdd = $form;
        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            $siglaSecao = $userNamespace->siglasecao;
            
            $rows = $tbFpdp->getFaixasSecao($data["SELECT_ID_TIPO_POSTAGEM"], $siglaSecao);
            if($rows == NULL){
                $rows[0]["FPDP_NR_NUMERO_INICIAL"] = '';
                $rows[0]["FPDP_NR_NUMERO_FINAL"] = '';
                $rows[0]["FPDP_DS_LETRA_INICIAL"] = '';
                $rows[0]["FPDP_DS_LETRA_FINAL"] = '';
                $rows[0]["FPDP_DH_INCLUSAO_POSTAGEM"] = '';
                $rows[0]["FPDP_CD_MATRICULA_INCLUSAO"] = '';
                $rows[0]["FPDP_NR_ULTIMO_NUMERO"] = '';
                $rows[0]["FPDP_NR_SEGURANCA_POSTAGEM"] = '';
                $rows[0]["FPDP_IC_POSTAGEM_ATIVO"] = '';
            }
            
            /*paginação*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'FPDP_ID_TIPO_POSTAGEM');
            $order_direction = $this->_getParam('direcao', 'ASC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /*Ordenação*/

            /*verifica condições e faz tratamento nos dados */
            $TimeInterval = new App_TimeInterval();

            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                      ->setItemCountPerPage(20);
            
            $form->populate($data);
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        }
    }
    
    public function addfaixapostagemAction()
    {
        $userNamespace = new Zend_Session_Namespace('userNs');
        $tbFpdp = new Application_Model_DbTable_SadTbFpdpFaixaPostagemDoc();
        $data = $this->getRequest()->getPost();
        
        
         if($data["FPDP_NR_NUMERO_FINAL"] < $data["FPDP_NR_NUMERO_INICIAL"]){
            $this->_helper->flashMessenger (array('message' => "Número Final Menor que Número Inicial", 'status' => 'notice'));
            unset($data["Salvar"]);
        }
        
        if($data["Salvar"] == 'Salvar')
        {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            try {
                if($data["FPDP_IC_POSTAGEM_ATIVO"] == 1){
                    $fpdp["FPDP_IC_POSTAGEM_ATIVO"] = 'S';
                }else {
                    $fpdp["FPDP_IC_POSTAGEM_ATIVO"] = 'N';
                }
                
                $gestor = explode(' - ', $data["FPDP_CD_MATRICULA_GESTOR"]);
                
                $fpdp["FPDP_ID_TIPO_POSTAGEM"]        = $data["FPDP_ID_TIPO_POSTAGEM"];
                $fpdp["FPDP_NR_NUMERO_INICIAL"]       = $data["FPDP_NR_NUMERO_INICIAL"];
                $fpdp["FPDP_NR_NUMERO_FINAL"]         = $data["FPDP_NR_NUMERO_FINAL"];
                $fpdp["FPDP_DS_LETRA_INICIAL"]        = new Zend_Db_Expr("UPPER('$data[FPDP_DS_LETRA_INICIAL]')");
                $fpdp["FPDP_DS_LETRA_FINAL"]          = new Zend_Db_Expr("UPPER('$data[FPDP_DS_LETRA_FINAL]')");
                $fpdp["FPDP_CD_MATRICULA_INCLUSAO"]   = $userNamespace->matricula;
                $fpdp["FPDP_DH_INCLUSAO_POSTAGEM"]    = new Zend_Db_Expr("TO_DATE(SYSDATE,'dd/mm/yyyy HH24:MI:SS')");
                $fpdp["FPDP_NR_ULTIMO_NUMERO"]        = $data["FPDP_NR_NUMERO_INICIAL"];
                $fpdp["FPDP_NR_SEGURANCA_POSTAGEM"]   = $data["FPDP_NR_SEGURANCA_POSTAGEM"];
                $fpdp["FPDP_CD_MATRICULA_GESTOR"]     = $gestor[0];
                $fpdp["FPDP_SG_SIGLA_SECAO_INCLUSAO"] = $userNamespace->siglasecao;
                $fpdp["FPDP_CD_LOTACAO_INCLUSAO"]     = $userNamespace->codlotacao;
                
                $setFaixaPostagem = $tbFpdp->createRow($fpdp);
                $faixaPostagem = $setFaixaPostagem->save();

                $db->commit();
                $this->_helper->flashMessenger (array('message' => "Faixa Postagem Cadastrada com sucesso!", 'status' => 'success'));
                $this->_helper->_redirector('faixapostagem','protocolo','sisad');
            } catch (Exception $exc) {
                $db->rollBack();
                echo $exc->getMessage();
                $this->_helper->flashMessenger (array('message' => "Problemas ao Cadastrar Faixa de Postagem!", 'status' => 'error'));
                $this->_helper->_redirector('faixapostagem','protocolo','sisad');
            }
        }else{
            if($data["TIPO"] == 'addFaixaPostagem'){
                $tppoTipoPostagem = new Application_Model_DbTable_SadTbTppoTipoPostagem();
                $getNomePostagem = $tppoTipoPostagem->getTipoPostagemByID($data["SELECT_ID_TIPO_POSTAGEM"]);
                $this->view->title = "Adicionar Faixa de Postagem";
                $form = new Sisad_Form_ProtocoloAddFaixaPostagem();
                
                $data["FPDP_CD_MATRICULA_INCLUSAO"] = $userNamespace->matricula;
                $data["FPDP_NO_TIPO_POSTAGEM"] = $getNomePostagem[0]["TPPO_DS_TIPO_POSTAGEM"];
                $data["FPDP_ID_TIPO_POSTAGEM"] = $data["SELECT_ID_TIPO_POSTAGEM"];
            }
            $form->populate($data);
            $this->view->formAdd = $form;
        }
    }
}
