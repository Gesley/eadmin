<?php
/**
 * Description of Anexo
 *
 * @author Pedro Henrique dos Santos Correia
 */

class Sosti_CategoriasController extends Zend_Controller_Action
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
        $userNs = new Zend_Session_Namespace('userNs');
        $CateNs = new Zend_Session_Namespace('CateNs');
        $this->view->title = "Categorizar";
        $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        
        $CateNs = new Zend_Session_Namespace('CateNs');
        $cxgsGrupoServiço = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        if($CateNs->tipo == 1){
            $data = $cateCategoria->fetchAll("CATE_ID_GRUPO = $CateNs->idGrupo");
            $rows = $data->toArray();
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($CateNs->identificador);
            $this->view->title = "Categorias Cadastradas - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
        }else if($CateNs->tipo == 2){
            $data = $cateCategoria->fetchAll("CATE_CD_MATRICULA_CATEGORIA = '$userNs->matricula'");
            $rows = $data->toArray();
            $this->view->title = "Categorias Cadastradas - Pessoal";
        }else if($CateNs->tipo == 3){
            $data = $cateCategoria->fetchAll("CATE_ID_NIVEL = $CateNs->identificador");
            $rows = $data->toArray();
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoNivel($CateNs->identificador);
            $this->view->title = "Categorias Cadastradas - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
        }

       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

//       $this->view->ordem = $order_column;
//       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function novaAction(){
        
        $form = new Sosti_Form_Categorias();
        $userNs = new Zend_Session_Namespace('userNs');
        $CateNs = new Zend_Session_Namespace('CateNs');
        /**
         * Verifica qual a caixa correspondente
         */
        if($CateNs->tipo == 1){
            $CATE_ID_GRUPO = $form->getElement('CATE_ID_GRUPO');
            $CATE_ID_GRUPO->setValue($CateNs->idGrupo);
            $cxgsGrupoServiço = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($CateNs->identificador);
            $this->view->title = "Nova Categoria - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
        }else if($CateNs->tipo == 2){
            $CateNs->identificador = $userNs->matricula;
            $CATE_CD_MATRICULA_CATEGORIA = $form->getElement('CATE_CD_MATRICULA_CATEGORIA');
            $CATE_CD_MATRICULA_CATEGORIA->setValue($CateNs->identificador);
            $this->view->title = "Nova Categoria - Pessoal";
        }else if($CateNs->tipo == 3){
            $CATE_ID_NIVEL = $form->getElement('CATE_ID_NIVEL');
            $CATE_ID_NIVEL->setValue($CateNs->identificador);
            $cxgsGrupoServiço = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoNivel($CateNs->identificador);
            $this->view->title = "Nova Categoria - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
        }
        /**
         * Salva os dados
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if($data["Salvar"] === 'Salvar'){
                if ($form->isValid($data)) {
                    $sosCateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
                    if($data["CATE_ID_GRUPO"] != NULL){
                        try {
                            $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                            $cateCategoria["CATE_ID_GRUPO"] = $data["CATE_ID_GRUPO"];
                            $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                            $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                            $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                            $cadastroCate = $sosCateCategoria->setCategoria($cateCategoria);
                            
                            $CateNs->idGrupo = $data["CATE_ID_GRUPO"];
                            $msg_to_user = "Nova categoria cadastrada com sucesso!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possivel cadastrar categoria";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }elseif($data["CATE_ID_NIVEL"] != NULL){
                        try {
                            $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                            $cateCategoria["CATE_CD_MATRICULA_CATEGORIA"] = $data["CATE_CD_MATRICULA_CATEGORIA"];
                            $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                            $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                            $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                            $cadastroCate = $sosCateCategoria->setCategoria($cateCategoria);
                            
                            $CateNs->identificador = $data["CATE_CD_MATRICULA_CATEGORIA"];
                            $msg_to_user = "Nova categoria cadastrada com sucesso!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possivel cadastrar categoria";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }elseif($data["CATE_CD_MATRICULA_CATEGORIA"] != NULL){
                        try {
                            $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                            $cateCategoria["CATE_ID_NIVEL"] = $data["CATE_ID_NIVEL"];
                            $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                            $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                            $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];
                            
                            $cadastroCate = $sosCateCategoria->setCategoria($cateCategoria);
                            
                            $CateNs->identificador = $data["CATE_ID_NIVEL"];
                            $msg_to_user = "Nova categoria cadastrada com sucesso!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possivel cadastrar categoria";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }
                    $this->_helper->_redirector('index','categorias','sosti',array('tipo'=>$CateNs->tipo,'id'=>$CateNs->identificador));
                } else {
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('nova');
                }
            }
        }
        $this->view->form = $form;
    }
    
    public function editarAction(){
        $form = new Sosti_Form_Categorias();
        $userNs = new Zend_Session_Namespace('userNs');
        $CateNs = new Zend_Session_Namespace('CateNs');
        $idCor = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        
        $cate_id_categoria = new Zend_Form_Element_Hidden('CATE_ID_CATEGORIA');
        $form->addElement($cate_id_categoria);
        
        $cxgsGrupoServiço = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        if($CateNs->tipo == 1){
            $CATE_ID_GRUPO = $form->getElement('CATE_ID_GRUPO');
            $CATE_ID_GRUPO->setValue($CateNs->idGrupo);
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($CateNs->identificador);
            $this->view->title = "Editar Categoria - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
        }else if($CateNs->tipo == 2){
            $CateNs->identificador = $userNs->matricula;
            $CATE_CD_MATRICULA_CATEGORIA = $form->getElement('CATE_CD_MATRICULA_CATEGORIA');
            $CATE_CD_MATRICULA_CATEGORIA->setValue($CateNs->identificador);
            $this->view->title = "Editar Categoria - Pessoal";
        }else if($CateNs->tipo == 3){
            $CATE_ID_NIVEL = $form->getElement('CATE_ID_NIVEL');
            $CATE_ID_NIVEL->setValue($CateNs->identificador);
            $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoNivel($CateNs->identificador);
            $this->view->title = "Editar Categoria - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
        }
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if($data["Salvar"] === 'Salvar'){
                if ($form->isValid($data)) {
                    $sosCateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
                    $sosCasoSolicitacao = new Application_Model_DbTable_SosTbCasoCategoriaSolic();
                    
                        if($data["CATE_ID_GRUPO"] != NULL){
                            try {
                                $cateCategoria["CATE_ID_CATEGORIA"] = $data["CATE_ID_CATEGORIA"];
                                $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                                $cateCategoria["CATE_ID_GRUPO"] = $data["CATE_ID_GRUPO"];
                                $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                                $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                                $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                                $cadastroCate = $sosCateCategoria->setAlterarCategoria($cateCategoria);

                                $CateNs->idGrupo = $data["CATE_ID_GRUPO"];
                                $msg_to_user = "Categoria Alterada!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possivel alterar categoria";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }elseif($data["CATE_CD_MATRICULA_CATEGORIA"] != NULL){
                            try {
                                $cateCategoria["CATE_ID_CATEGORIA"] = $data["CATE_ID_CATEGORIA"];
                                $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                                $cateCategoria["CATE_CD_MATRICULA_CATEGORIA"] = $data["CATE_CD_MATRICULA_CATEGORIA"];
                                $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                                $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                                $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                                $cadastroCate = $sosCateCategoria->setAlterarCategoria($cateCategoria);
                                
                                $CateNs->identificador = $data["CATE_CD_MATRICULA_CATEGORIA"];
                                $msg_to_user = "Categoria Alterada!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possivel alterar categoria";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }elseif($data["CATE_ID_NIVEL"] != NULL){
                            try {
                                $cateCategoria["CATE_ID_CATEGORIA"] = $data["CATE_ID_CATEGORIA"];
                                $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                                $cateCategoria["CATE_ID_NIVEL"] = $data["CATE_ID_NIVEL"];
                                $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                                $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                                $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                                $cadastroCate = $sosCateCategoria->setAlterarCategoria($cateCategoria);

                                $CateNs->identificador = $data["CATE_ID_NIVEL"];
                                $msg_to_user = "Categoria Alterada!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possivel alterar categoria";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }
                    $this->_helper->_redirector('index','categorias','sosti');
                } else {
                    $form->populate($cateCategoria);
                    $this->view->form = $form;
                    $this->view->cor = $data["CATE_DS_DESCRICAO_COR"];
                    $this->render('editar');
                }
            }
        }else {
            $sosCateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
            $data = $sosCateCategoria->fetchRow("CATE_ID_CATEGORIA = $idCor")->toArray();
            $form->populate($data);
            $this->view->form = $form;
            $this->view->cor = $data["CATE_DS_DESCRICAO_COR"];
            $this->render('editar');
        }
        $this->view->form = $form;
    }
    
    public function categorizarAction(){
        $uri = $_SERVER['REQUEST_URI'];
        $end = explode('/sosti/', $uri);
        $end = explode('/', $end[1]);
        $data = $this->getRequest()->getPost();
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_Categorias();
        $CateNs = new Zend_Session_Namespace('CateNs');
        if ($this->getRequest()->isPost()) {
            if($data["acao"] === 'Categorias'){
                $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
                /**
                 * Verifica qual a caixa correspondente
                 */
                $cxgsGrupoServiço = new Application_Model_DbTable_SadTbCxgsGrupoServico();
                if($CateNs->tipo == 1){
                    $Categorias = $cateCategoria->fetchAll("CATE_ID_GRUPO = $CateNs->idGrupo AND CATE_IC_ATIVO = 'S'");
                    $rows = $Categorias->toArray();
                    $this->view->categorias = $rows;
                    $CATE_ID_GRUPO = $form->getElement('CATE_ID_GRUPO');
                    $CATE_ID_GRUPO->setValue($CateNs->idGrupo);
                    $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoByCaixa($CateNs->identificador);
                    $this->view->title = "Categorização - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
                }else if($CateNs->tipo == 2){
                    $Categorias = $cateCategoria->fetchAll("CATE_CD_MATRICULA_CATEGORIA = '$userNs->matricula' AND CATE_IC_ATIVO = 'S'");
                    $rows = $Categorias->toArray();
                    $this->view->categorias = $rows;
                    $CATE_CD_MATRICULA_CATEGORIA = $form->getElement('CATE_CD_MATRICULA_CATEGORIA');
                    $CATE_CD_MATRICULA_CATEGORIA->setValue($CateNs->identificador);
                    $this->view->title = "Categorização - Pessoal";
                }else if($CateNs->tipo == 3){
                    $Categorias = $cateCategoria->fetchAll("CATE_ID_NIVEL = $CateNs->identificador AND CATE_IC_ATIVO = 'S'");
                    $rows = $Categorias->toArray();
                    $this->view->categorias = $rows;
                    $CATE_ID_NIVEL = $form->getElement('CATE_ID_NIVEL');
                    $CATE_ID_NIVEL->setValue($CateNs->identificador);
                    $nomeCaixa = $cxgsGrupoServiço->getGrupoAtendimentoNivel($CateNs->identificador);
                    $this->view->title = "Categorização - ".$nomeCaixa[0]['TPCX_DS_CAIXA_ENTRADA'];
                }
                $solicspace = new Zend_Session_Namespace('solicspace');
                $this->view->data = $data["solicitacao"];
                $this->view->form = $form;
                $this->render('categorizar');

            }else if ($data["Salvar"] === 'Salvar') {
                $dual = new Application_Model_DbTable_Dual();
                $datahora = $dual->sysdate();
                $caso_categoria_Solic = new Application_Model_DbTable_SosTbCasoCategoriaSolic();
                $sosCateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
                if($data["categorizar"] === 'N'){
                    try {
                        if($data["CATE_ID_GRUPO"] != NULL){
                            try {
                                $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                                $cateCategoria["CATE_ID_GRUPO"] = $data["CATE_ID_GRUPO"];
                                $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                                $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                                $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                                $cadastroCate = $sosCateCategoria->setCategoria($cateCategoria);
                                
                                $CateNs->idGrupo = $data["CATE_ID_GRUPO"];
                                $msg_to_user = "Nova categoria cadastrada com sucesso!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possivel cadastrar categoria";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }else if($data["CATE_ID_NIVEL"] != NULL){
                            try {
                                $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                                $cateCategoria["CATE_ID_NIVEL"] = $data["CATE_ID_NIVEL"];
                                $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                                $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                                $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                                $cadastroCate = $sosCateCategoria->setCategoria($cateCategoria);

                                $CateNs->identificador = $data["CATE_ID_NIVEL"];
                                $msg_to_user = "Nova categoria cadastrada com sucesso!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possivel cadastrar categoria";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }else if($data["CATE_CD_MATRICULA_CATEGORIA"] != NULL){
                            try {
                                $cateCategoria["CATE_NO_CATEGORIA"] = $data["CATE_NO_CATEGORIA"];
                                $cateCategoria["CATE_CD_MATRICULA_CATEGORIA"] = $data["CATE_CD_MATRICULA_CATEGORIA"];
                                $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                                $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                                $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                                $cadastroCate = $sosCateCategoria->setCategoria($cateCategoria);

                                $CateNs->identificador = $data["CATE_CD_MATRICULA_CATEGORIA"];
                                $msg_to_user = "Nova categoria cadastrada com sucesso!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possivel cadastrar categoria";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }
                        foreach ($data["solicitacao"] as $value) {
                            try {
                                $dados_input = Zend_Json::decode($value);
                                $caso["CASO_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                                $caso["CASO_CD_MATRICULA_OPERACAO"] = $userNs->matricula;
                                $caso["CASO_DH_CATEGORIA_SOLICITACAO"] = $datahora;
                                $caso["CASO_ID_CATEGORIA"] = $cadastroCate;

                                $caso_categoria_Solic->setIncluirCategoria($caso);
                                $msg_to_user = "Categoria adicionada a solicitação!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $msg_to_user = "Não foi possivel adicionar categoria a solicitação";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }
                    } catch (Exception $exc) {
                        $msg_to_user = "Não foi possivel cadastrar categoria";
                        $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                    }
                }else if($data["categorizar"] === 'C'){
                    foreach ($data["solicitacao"] as $value) {
                        try {
                            $dados_input = Zend_Json::decode($value);
                            $caso["CASO_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                            $caso["CASO_CD_MATRICULA_OPERACAO"] = $userNs->matricula;
                            $caso["CASO_DH_CATEGORIA_SOLICITACAO"] = $datahora;
                            $caso["CASO_ID_CATEGORIA"] = $data["cat"];

                            $caso_categoria_Solic->setIncluirCategoria($caso);
                            $msg_to_user = "Categoria adicionada a solicitação!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possivel adicionar categoria a solicitação";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }
                }else if($data["categorizar"] === 'D'){
                    foreach ($data["solicitacao"] as $value) {
                        try {
                            $dados_input = Zend_Json::decode($value);
                            $caso["CASO_ID_DOCUMENTO"] = $dados_input["SSOL_ID_DOCUMENTO"];
                            $caso["CASO_ID_CATEGORIA"] = $data["des"];
                            $caso["CASO_DH_INATIVACAO_CATEGORIA"] = $datahora;
                            $caso["CASO_CD_MATRICULA_INATIVACAO"] = $userNs->matricula;
                            
                            
                            $caso_categoria_Solic->setExcluirCategoria($caso);
                            $msg_to_user = "Categoria adicionada a solicitação!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possivel adicionar categoria a solicitação";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }
                }
                $this->_helper->_redirector($CateNs->action,$CateNs->controller,'sosti');
            }
        } 
    }
}
