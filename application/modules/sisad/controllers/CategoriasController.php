<?php
/**
 * Description of Anexo
 *
 * @author Pedro Henrique dos Santos Correia
 * 
 * @version 1.0
 */

class Sisad_CategoriasController extends Zend_Controller_Action
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
		
		$this->view->titleBrowser = 'e-Sisad';
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }
    
    public function categorizarAction()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $end = explode('/sosti/', $uri);
        $end = explode('/', $end[1]);
        $data = $this->getRequest()->getPost();
        
        $userNs = new Zend_Session_Namespace('userNs');
        $cateNs = new Zend_Session_Namespace('cateNs');
        $form = new Sisad_Form_Categorias();
        if ($this->getRequest()->isPost()) {
            if($data["acao"] === 'Categorias'){
                $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
                $service_juntada = new Services_Sisad_Juntada();
                $data['documento'] = $service_juntada->completaComApensados($data['documento']);
                $this->view->data = $data;
                $this->view->categorias = $categorias;
                $this->view->form = $form;
                $cateNs->controller = $data["controller"];
                $cateNs->action = $data["action"];
                $this->render('categorizar');

            }else if ($data["Salvar"] === 'Salvar') {
                $dual = new Application_Model_DbTable_Dual();
                $datahora = $dual->sysdate();
                $cateNs = new Zend_Session_Namespace('cateNs');
                $cado_categoria_Doc = new Application_Model_DbTable_SadTbCadoCategoriaDoc();
                $sadCateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                if($data["categorizar"] === 'N'){
                    try {
                        if($cateNs->tipo == 'unidade'){
                            try {
                                $cateCategoria["CATE_NM_CATEGORIA"] = $data["CATE_NM_CATEGORIA"];
                                $cateCategoria["CATE_SG_SECAO_CATEGORIA"] = $data["CATE_SG_SECAO_CATEGORIA"];
                                $cateCategoria["CATE_CD_LOTACAO_CATEGORIA"] = $data["CATE_CD_LOTACAO_CATEGORIA"];
                                $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];
                                $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                                $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                                
                                $cadastroCate = $sadCateCategoria->createRow($cateCategoria);
                                $idCategoria = $cadastroCate->save();
                                
                                $msg_to_user = "Nova categoria cadastrada com sucesso!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $db->rollBack();
                                $msg_to_user = "Não foi possivel cadastrar categoria";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }else if($cateNs->tipo == 'pessoal'){
                            try {
                                $cateCategoria["CATE_NM_CATEGORIA"] = $data["CATE_NM_CATEGORIA"];
                                $cateCategoria["CATE_CD_MATRICULA_CATEGORIA"] = $userNs->matricula;
                                $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];
                                $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                                $cateCategoria["CATE_ID_ATIVO"] = $data["CATE_ID_ATIVO"];

                                $cadastroCate = $sadCateCategoria->createRow($cateCategoria);
                                $idCategoria = $cadastroCate->save();
                                
                                $msg_to_user = "Nova categoria cadastrada com sucesso!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $db->rollBack();
                                $msg_to_user = "Não foi possivel cadastrar categoria";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }
                        foreach ($data["documento"] as $value) {
                            try {
                                $dados_input = Zend_Json::decode($value);
                                $cadoCategoriaDoc["CADO_ID_CATEGORIA"] = $idCategoria;
                                $cadoCategoriaDoc["CADO_ID_DOCUMENTO"] = $dados_input["DOCM_ID_DOCUMENTO"];
                                $cadoCategoriaDoc["CADO_CD_MATRICULA_OPERACAO"] = $userNs->matricula;
                                $cadoCategoriaDoc["CADO_DH_CATEGORIA_DOCUMENTO"] = $datahora;

                                $CateDoc = $cado_categoria_Doc->createRow($cadoCategoriaDoc);
                                $idCado = $CateDoc->save();
                                
                                $msg_to_user = "Categoria adicionada a solicitação!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            } catch (Exception $exc) {
                                $db->rollBack();
                                $msg_to_user = "Não foi possivel adicionar categoria a solicitação";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                            }
                        }
                    } catch (Exception $exc) {
                        $db->rollBack();
                        $msg_to_user = "Não foi possivel cadastrar categoria";
                        $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                    }
                }else if($data["categorizar"] === 'C'){
                    foreach ($data["documento"] as $value) {
                        try {
                            $dados_input = Zend_Json::decode($value);
                            $cadoCategoriaDoc["CADO_ID_DOCUMENTO"] = $dados_input["DOCM_ID_DOCUMENTO"];
                            $cadoCategoriaDoc["CADO_ID_CATEGORIA"] = $data["cat"];
                            $cadoCategoriaDoc["CADO_CD_MATRICULA_OPERACAO"] = $userNs->matricula;
                            $cadoCategoriaDoc["CADO_DH_CATEGORIA_DOCUMENTO"] = $datahora;
                            
                            $categoria = $cado_categoria_Doc->fetchRow("CADO_ID_DOCUMENTO = ".$cadoCategoriaDoc['CADO_ID_DOCUMENTO']
                                                                      ."AND CADO_ID_CATEGORIA = ".$cadoCategoriaDoc['CADO_ID_CATEGORIA']);
                            if(!$categoria){
                                $CateDoc = $cado_categoria_Doc->createRow($cadoCategoriaDoc);
                                $idCado = $CateDoc->save();
                            }else{
                                $cadoCategoriaDoc["CADO_CD_MATRICULA_INATIVACAO"] = NULL;
                                $cadoCategoriaDoc["CADO_DH_INATIVACAO_CATEGORIA"] = NULL;
                                $rowCado = $cado_categoria_Doc->find($cadoCategoriaDoc["CADO_ID_CATEGORIA"],$cadoCategoriaDoc["CADO_ID_DOCUMENTO"])->current();
                                $rowCado->setFromArray($cadoCategoriaDoc);
                                $idRow = $rowCado->save();
                            }
                            $msg_to_user = "Categoria adicionada a solicitação!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $msg_to_user = "Não foi possivel adicionar categoria a solicitação";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }
                }else if($data["categorizar"] === 'D'){
                    foreach ($data["documento"] as $value) {
                        try {
                            $dados_input = Zend_Json::decode($value);
                            
                            $idDoc = $dados_input["DOCM_ID_DOCUMENTO"];
                            $idCate = $data["des"];
                            $cadoCategoriaDoc["CADO_CD_MATRICULA_INATIVACAO"] = $userNs->matricula;
                            $cadoCategoriaDoc["CADO_DH_INATIVACAO_CATEGORIA"] = $datahora;
                            
                            $categoria = $cado_categoria_Doc->fetchRow("CADO_ID_DOCUMENTO = ".$idDoc."AND CADO_ID_CATEGORIA = ".$idCate);
                            if($categoria){
                                $rowCado = $cado_categoria_Doc->find($idCate,$idDoc)->current();
                                $rowCado->setFromArray($cadoCategoriaDoc);
                                $idRow = $rowCado->save();
                                $msg_to_user = "Categoria removida da solicitação!";
                                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                            }
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $msg_to_user = "Não foi possivel remover categoria da solicitação";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }
                }
                $db->commit();
                $this->_helper->_redirector("$cateNs->action","$cateNs->controller","sisad");
            }
        } 
    }
    
    public function editarAction()
    {
        $form = new Sisad_Form_Categorias();
        $userNs = new Zend_Session_Namespace('userNs');
        $cateNs = new Zend_Session_Namespace('cateNs');
        $idCor = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        
        $cate_id_categoria = new Zend_Form_Element_Hidden('CATE_ID_CATEGORIA');
        $form->addElement($cate_id_categoria);
        
        if($cateNs->tipo == 'unidade'){
            $this->view->title = "Editar Categoria - ".$userNs->siglalotacao;
        }else if($cateNs->tipo == 'pessoal'){
            $this->view->title = "Editar Categoria - Pessoal";
        }
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if($data["Salvar"] === 'Salvar'){
                if ($form->isValid($data)) {
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    $SadCateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
                    if(($data["CATE_SG_SECAO_CATEGORIA"] != NULL) && ($data["CATE_CD_LOTACAO_CATEGORIA"] != NULL) ){
                        try {
                            $cateCategoria["CATE_ID_CATEGORIA"] = $data["CATE_ID_CATEGORIA"];
                            $cateCategoria["CATE_NM_CATEGORIA"]  = $data["CATE_NM_CATEGORIA"] ;
                            $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                            $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                            $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                            $rowCate = $SadCateCategoria->find($cateCategoria["CATE_ID_CATEGORIA"])->current();
                            $rowCate->setFromArray($cateCategoria);
                            $idRow = $rowCate->save();
                            
                            $msg_to_user = "Categoria Alterada!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $msg_to_user = "Não foi possivel alterar categoria";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }elseif(($data["CATE_SG_SECAO_CATEGORIA"] != NULL)){
                        try {
                            $cateCategoria["CATE_ID_CATEGORIA"] = $data["CATE_ID_CATEGORIA"];
                            $cateCategoria["CATE_NM_CATEGORIA"]  = $data["CATE_NM_CATEGORIA"] ;
                            $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                            $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];
                            $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];

                            $rowCate = $SadCateCategoria->find($cateCategoria["CATE_ID_CATEGORIA"])->current();
                            $rowCate->setFromArray($cateCategoria);
                            $idRow = $rowCado->save();
                            
                            $msg_to_user = "Categoria Alterada!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $msg_to_user = "Não foi possivel alterar categoria";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }
                    $db->commit();
                    $this->_helper->_redirector('index','categorias','sisad');
                } else {
                    $form->populate($cateCategoria);
                    $this->view->form = $form;
                    $this->view->cor = $data["CATE_DS_DESCRICAO_COR"];
                    $this->render('editar');
                }
            }
        }else {
            $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();;
            $data = $cateCategoria->fetchRow("CATE_ID_CATEGORIA = $idCor")->toArray();
            $form->populate($data);
            $this->view->form = $form;
            $this->view->cor = $data["CATE_DS_DESCRICAO_COR"];
            $this->render('editar');
        }
        $this->view->form = $form;
    }
    
    public function indexAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $cateNs = new Zend_Session_Namespace('cateNs');
        
        if($cateNs->tipo == 'unidade'){
            $this->view->title = "Categorias da Unidade - ".$userNs->siglalotacao;
        }else if($cateNs->tipo == 'pessoal'){
            $this->view->title = "Categorias Pessoal";
        }
        $cateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $cado_categoria = new Application_Model_DbTable_SadTbCadoCategoriaDoc();
        if($cateNs->tipo == 'unidade'){
            $rows = $cado_categoria->getCategoriasUnidadePessoa(null,$cateNs->sgSecao, $cateNs->cdLotacao);
        }else if($cateNs->tipo == 'pessoal'){
            $rows = $cado_categoria->getCategoriasUnidadePessoa($cateNs->matricula,null,null);
        }
       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function novaAction()
    {
        
        $form = new Sisad_Form_Categorias();
        $userNs = new Zend_Session_Namespace('userNs');
        $cateNs = new Zend_Session_Namespace('cateNs');
        $sadCateCategoria = new Application_Model_DbTable_SadTbCateCategoria();
        /**
         * Verifica qual a caixa correspondente
         */
        if($cateNs->tipo == 'unidade'){
            $this->view->title = "Nova Categoria - ".$userNs->siglalotacao;
            
        }else if($cateNs->tipo == 'pessoal'){
            $this->view->title = "Nova Categoria - Pessoal";
        }
        /**
         * Salva os dados
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if($data["Salvar"] === 'Salvar'){
                if ($form->isValid($data)) {
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    if($cateNs->tipo == 'unidade'){
                    try {
                            $cateCategoria["CATE_NM_CATEGORIA"] = $data["CATE_NM_CATEGORIA"];
                            $cateCategoria["CATE_SG_SECAO_CATEGORIA"] = $data["CATE_SG_SECAO_CATEGORIA"];
                            $cateCategoria["CATE_CD_LOTACAO_CATEGORIA"] = $data["CATE_CD_LOTACAO_CATEGORIA"];
                            $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];
                            $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                            $cateCategoria["CATE_IC_ATIVO"] = $data["CATE_IC_ATIVO"];

                            $cadastroCate = $sadCateCategoria->createRow($cateCategoria);
                            $idCategoria = $cadastroCate->save();

                            $msg_to_user = "Nova categoria cadastrada com sucesso!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $msg_to_user = "Não foi possivel cadastrar categoria";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }else if($cateNs->tipo == 'pessoal'){
                        try {
                            $cateCategoria["CATE_NM_CATEGORIA"] = $data["CATE_NM_CATEGORIA"];
                            $cateCategoria["CATE_CD_MATRICULA_CATEGORIA"] = $userNs->matricula;
                            $cateCategoria["CATE_DS_DESCRICAO_COR"] = $data["CATE_DS_DESCRICAO_COR"];
                            $cateCategoria["CATE_DS_OBSERVACAO"] = $data["CATE_DS_OBSERVACAO"];
                            $cateCategoria["CATE_ID_ATIVO"] = $data["CATE_ID_ATIVO"];

                            $cadastroCate = $sadCateCategoria->createRow($cateCategoria);
                            $idCategoria = $cadastroCate->save();

                            $msg_to_user = "Nova categoria cadastrada com sucesso!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $db->rollBack();
                            $msg_to_user = "Não foi possivel cadastrar categoria";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'notice'));
                        }
                    }
                    $db->commit();
                    $this->_helper->_redirector('index','categorias','sisad');
                } else {
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('form');
                }
            }
        }
        $this->view->form = $form;
    }
    
    
    
    
}
