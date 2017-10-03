<?php

class Sisad_AvisoController extends Zend_Controller_Action
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
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
		$this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function indexAction()
    {
        $this->view->title = "Listas de Divulgação de Avisos";
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/
        $dados = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
        $rows = $dados->getGrupos();
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

    }
    
    public function listunidadesdivulgadorasAction()
    {
        $this->view->title = "Unidades Divulgadoras de Documentos";
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/
        $dados = new Application_Model_DbTable_SadTbPediPermissaoDivulg();
        $rows = $dados->getUnidadesDivulgadoras();

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

    }
    
    public function addgrupoAction()
    {
        $this->view->title = "Criar Lista de Divulgação";
        $form = new Sisad_Form_AddGrupo();
        $this->view->form = $form;
    }
    
    public function addlistadivulgacaoAction()
    {
        $this->view->title = "Divulgar Aviso";
        $form = new Sisad_Form_Divulgar();
        $data = $this->getRequest()->getPost();
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        
//        Zend_Debug::dump($data);
//        exit;
        if ($data['Salvar']=='Salvar' && $this->getRequest()->isPost()) {
            try {
                $tabelaDocumentoLista = new Application_Model_DbTable_SadTbDoliDocumentoLista();
                $tabelaDocumentoLista->setDivulgarDocumento($data);
                $this->_helper->flashMessenger(array('message' => "Documento divulgado para lista com Sucesso.", 'status' => 'success'));
                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
            } catch (Exception $exc) {
                $this->_helper->flashMessenger(array('message' => "Não foi possível incluir Proprietário(os) na Grupo. " . $e->getMessage(), 'status' => 'error'));
                $this->_helper->_redirector('entrada', 'caixaunidade', 'sisad');
                echo $exc->getTraceAsString();
            }
        }
        /*---------GRID QUE LISTA OS ATUAIS COMPONENTES DE UM GRUPO--------------------*/
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

        /*Envia para a view o id do grupo*/
        $i = 0;
        foreach ($data['documento'] as $value) {
            $rows[$i] = Zend_Json_Decoder::decode($value);
            $rows[$i]['DADOS_INPUT'] = $value;
            $i++;
        }

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                     ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->form = $form;
    }
    
    public function addproprietarioAction()
    {
        $this->view->title = "Proprietário de Grupo de Divulgação";
        $form = new Sisad_Form_AddProprietario();
        $this->view->form = $form;
        $data = $this->getRequest()->getParams();

        if ($this->getRequest()->isPost()) {
            $id = $data['grupo'];
            $id = array('id' => $id);
            try {
                $tabelaProprietarioGrupo = new Application_Model_DbTable_SadTbPrgrProprietGrupoDiv();
                $update = $tabelaProprietarioGrupo->setNewProprietarioGrupo($data);
                $this->_helper->flashMessenger(array('message' => "Proprietário(s) incluído(s) no Grupo com Sucesso.", 'status' => 'success'));
                $this->_helper->_redirector('edit', 'aviso', 'sisad', $id);
            } catch (Zend_Exception $exc) {
                $this->_helper->flashMessenger(array('message' => "Não foi possível incluir Proprietário(os) na Grupo. " . $e->getMessage(), 'status' => 'error'));
                $this->_helper->_redirector('edit', 'aviso', 'sisad', $id);
            }
        } else {
            $id = $this->getRequest()->getParam("grupo");
            $dados = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
            $rows = $dados->getGrupobyId($id);
            $form->populate($rows);
            $this->view->form = $form;
        }
    }
    public function addunidadedivulgadoraAction()
    {
        $this->view->title = "Permissão para unidade divulgar documentos";
        $form = new Sisad_Form_AddProprietario();
        $formTipoDoc = new Sisad_Form_AddPermissaoDivulgacao();
        $this->view->form = $form;
        $data = $this->getRequest()->getParams();

        if ($this->getRequest()->isPost()) {
            $id = $data['grupo'];
            $id = 32;
            $id = array('id' => $id);
            try {
                $tabelaProprietarioGrupo = new Application_Model_DbTable_SadTbPrgrProprietGrupoDiv();
                $update = $tabelaProprietarioGrupo->setNewProprietarioGrupo($data);
                $this->_helper->flashMessenger(array('message' => "Proprietário(s) incluído(s) no Grupo com Sucesso.", 'status' => 'success'));
                $this->_helper->_redirector('edit', 'aviso', 'sisad', $id);
            } catch (Zend_Exception $exc) {
                $this->_helper->flashMessenger(array('message' => "Não foi possível incluir Proprietário(os) na Grupo. " . $e->getMessage(), 'status' => 'error'));
                $this->_helper->_redirector('edit', 'aviso', 'sisad', $id);
            }
        } else {
//            $id = $this->getRequest()->getParam("grupo");
//            $dados = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
//            $rows = $dados->getGrupobyId($id);
//            $form->populate($rows);
            $form->removeElement('GRDV_DS_GRUPO_DIVULGACAO');
            $this->view->form = $form;
            $this->view->formTipoDoc = $formTipoDoc;
        }
    }
    
    public function addpermdivulgacaoAction() 
    {
        $this->view->title = "Adicionar Documento para Divulgação";
        $form = new Sisad_Form_AddPermissaoDivulgacao();
        $this->view->form = $form;
        $data = $this->getRequest()->getParams();
        
        if ($this->getRequest()->isPost() && $form->isValid($data)) {
            $sg = $data['sgsecao'];
            $cd = $data['codlotacao'];
            $sg_cd = array('siglasecao' => $sg, 'codlotacao' => $cd);
            try {
                $tabelaPermissaoDivulgacao = new Application_Model_DbTable_SadTbPediPermissaoDivulg();
                $update = $tabelaPermissaoDivulgacao->setNewDocumentoDivulgacao($data);
                $this->_helper->flashMessenger(array('message' => "Documento(s) incluído(s) com Sucesso.", 'status' => 'success'));
                $this->_helper->_redirector('editpermissaotipodoc', 'aviso', 'sisad', $sg_cd);
            } catch (Zend_Exception $exc) {
                $this->_helper->flashMessenger(array('message' => "Não foi possível incluir Documento(s). " . $e->getMessage(), 'status' => 'error'));
                $this->_helper->_redirector('editpermissaotipodoc', 'aviso', 'sisad', $sg_cd);
            }
        } else {
            $this->view->form = $form;
        }
    }
    
    public function addcomponenteAction()
    {
        $this->view->title = "Adicionar Componente a Lista de Divulgação";
        $form = new Sisad_Form_AddComponente();
        $data = $this->getRequest()->getParams();

        if ($this->getRequest()->isPost()) {
            $id = $data['GRDV_ID_GRUPO_DIVULGACAO'];
            $id = array('id' => $id);
            try {
                $tabelaComponentes = new Application_Model_DbTable_SadTbCompComponenteGrupo();
                $update = $tabelaComponentes->setNewComponenteGrupo($data);
                $this->_helper->flashMessenger(array('message' => "Componente(s) incluído(s) no Grupo com Sucesso.", 'status' => 'success'));
                $this->_helper->_redirector('edit', 'aviso', 'sisad', $id);
            } catch (Zend_Exception $exc) {
                $this->_helper->flashMessenger(array('message' => "Não foi possível incluir Componente na Lista. " . $e->getMessage(), 'status' => 'error'));
                $this->_helper->_redirector('edit', 'aviso', 'sisad', $id);
            }
        } else {
            $id = $this->getRequest()->getParam("grupo");
            $id = array('id' => $id);
            $dados = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
            $id = $this->getRequest()->getParam("grupo");
            $rows = $dados->getGrupobyId($id);
            $form->populate($rows);
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            
            /*---------GRID QUE LISTA OS ATUAIS COMPONENTES DE UM GRUPO--------------------*/
            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /*Ordenação*/
            
            /*Envia para a view o id do grupo*/
            $this->view->idgrupo = $id;
            $dados = new Application_Model_DbTable_SadTbCompComponenteGrupo();
            $rows = $dados->getComponentesGrupo($id);

            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                         ->setItemCountPerPage(15);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            /*--------------------------FIM DA GRID-----------------------------------*/
            
            $this->view->form = $form;
        }
    }
    
    public function editAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/
        
        $id = $this->getRequest()->getParam("id");
        /*Envia para a view o id do grupo*/
        $this->view->idgrupo = $id;
        $dados = new Application_Model_DbTable_SadTbCompComponenteGrupo();
        $rowsComponentes = $dados->getComponentesGrupo($id);
        
        $paginator = Zend_Paginator::factory($rowsComponentes);
        $paginator->setCurrentPageNumber($page)
                     ->setItemCountPerPage(15);

        $this->view->rowsComponentes = $paginator;
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        
        $nomeGrupo = $this->getRequest()->getParam("descgrupo");
        $this->view->title = "Editar Lista de Divulgação de Avisos - ".$nomeGrupo;
    }
    
    public function editpermissaotipodocAction()
    {
        $data = $this->getRequest()->getParams();

        $tabelaPermissaoDivulgacao = new Application_Model_DbTable_SadTbPediPermissaoDivulg();
        $rows = $tabelaPermissaoDivulgacao->getListaDocumentosPermitidos($this->getRequest()->getParam("siglasecao"), $this->getRequest()->getParam("codlotacao"));
        
        /* ---------GRID QUE LISTA OS DOCUMENTOS PERMITIDOS DE UMA UNIDADE DIVULGADORA-------------------- */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'FADM_DS_FASE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $this->view->sgsecao = $data['siglasecao'];
        $this->view->codlotacao = $data['codlotacao'];
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        /* --------------------------FIM DA GRID----------------------------------- */
        
        $userNs = new Zend_Session_Namespace('userNs');
        if (is_null($userNs->descunidade)) {
            $userNs->descunidade = $rows[0]['LOTA_DSC_LOTACAO'];
            $nomeUnidade = $userNs->descunidade;
        }  else {
            $nomeUnidade = $userNs->descunidade;
        }
        $this->view->title = "Editar Permissão de Divulgação de Documentos - ".$nomeUnidade;
    }
    
    public function delAction()
    {
        $id = $this->getRequest()->getParam("id");
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try{
            $inativarComponenteEmLista["COMP_IC_ATIVO"] = "N";
            $tabelaComponentes = new Application_Model_DbTable_SadTbCompComponenteGrupo();
            $row = $tabelaComponentes->find($id)->current();
            $row->setFromArray($inativarComponenteEmLista);
            $row->save();
            $this->_helper->flashMessenger (array('message' => "Componente excluído do Grupo com Sucesso.", 'status' => 'success'));
        } catch(Zend_Exception $e){
             $this->_helper->flashMessenger (array('message' => "Não foi possível excluir Componente ".$id. " do Grupo. ".$e->getMessage(), 'status' => 'error'));
        }
        $id = $this->getRequest()->getParam("idgrupo");
        $id = array ('id'=>$id);
        $this->_helper->_redirector('edit', 'aviso', 'sisad',$id);
    }
    
    public function delgrupoAction()
    {
        $id = $this->getRequest()->getParam("id");
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try{
            $inativarGrupo["GRDV_IC_ATIVO"] = "N";
            $tabelaGrupos = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
            $row = $tabelaGrupos->find($id)->current();
            $row->setFromArray($inativarGrupo);
            $row->save();
            $this->_helper->flashMessenger (array('message' => "Grupo excluído com Sucesso.", 'status' => 'success'));
        } catch(Zend_Exception $e){
             $this->_helper->flashMessenger (array('message' => "Não foi possível excluir Grupo ".$id. " da Lista. ".$e->getMessage(), 'status' => 'error'));
        }
        $id = $this->getRequest()->getParam("idgrupo");
        $id = array ('id'=>$id);
        $this->_helper->_redirector('index', 'aviso', 'sisad');
    }
    
    public function delpermissaotipodocAction()
    {
        $id = $this->getRequest()->getParam("id");
        $sgsecao = $this->getRequest()->getParam("sgsecao");
        $codlotacao = $this->getRequest()->getParam("codlotacao");
        $sg_cd = array('siglasecao' => $sgsecao, 'codlotacao' => $codlotacao);
        $tabelaPermissaoDivulgacao = new Application_Model_DbTable_SadTbPediPermissaoDivulg();
        
        try {
            $linha = $tabelaPermissaoDivulgacao->fetchRow("PEDI_ID_TIPO_DOC = '$id' AND PEDI_SG_SECAO = '$sgsecao' AND PEDI_CD_LOTACAO = '$codlotacao'");
            $linha->delete();
            $this->_helper->flashMessenger (array('message' => "Documento excluído com Sucesso.", 'status' => 'success'));
        } catch (Zend_Exception $e) {
             $this->_helper->flashMessenger (array('message' => "Não foi possível excluir Documento ".$id. " da Lista. ".$e->getMessage(), 'status' => 'error'));
        }
        $this->_helper->_redirector('editpermissaotipodoc', 'aviso', 'sisad',$sg_cd);
    }
    
    public function delproprietarioAction()
    {
        $id = $this->getRequest()->getParam("id");
        $sg_secao_propriet = $this->getRequest()->getParam("sgpropri");
        $cd_lot_propriet= $this->getRequest()->getParam("cdpropri");
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try{
            $tabelaProprietarioGrupo = new Application_Model_DbTable_SadTbPrgrProprietGrupoDiv();
            $row = $tabelaProprietarioGrupo->fetchRow("PRGR_ID_GRUPO_DIVULGACAO = '$id' AND PRGR_SG_SECAO_PROPRIET_GR_DIV = '$sg_secao_propriet' AND PRGR_CD_LOT_PROPRIET_GR_DIV = '$cd_lot_propriet'");
            $row["PRGR_IC_ATIVO"] = "N";
            $row->setFromArray($row);
            $row->save();
            $this->_helper->flashMessenger (array('message' => "Grupo excluído com Sucesso.", 'status' => 'success'));
        } catch(Zend_Exception $e){
             $this->_helper->flashMessenger (array('message' => "Não foi possível excluir Proprietário do Grupo ".$id. " da Lista. ".$e->getMessage(), 'status' => 'error'));
        }
        $id = $this->getRequest()->getParam("id");
        $id = array ('id'=>$id);
        $this->_helper->_redirector('edit', 'aviso', 'sisad',$id);
    }
    
    public function savegrupoAction()
    {
        $form = new Sisad_Form_AddGrupo();
        $data = $this->getRequest()->getParams();
        
        if ($form->isValid($data)) {
            try {
                $tabelaGrupos = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
                $tabelaGrupos->setNewGrupo($data);
                $nome_do_grupo = $data['GRDV_DS_GRUPO_DIVULGACAO'];
                $this->_helper->flashMessenger(array('message' => "Grupo ".$nome_do_grupo." Criado com Sucesso.", 'status' => 'success'));
                $this->_helper->_redirector('index', 'aviso', 'sisad');
            } catch (Exception $e) {
                $nome_do_grupo = $data['GRDV_DS_GRUPO_DIVULGACAO'];
                $this->_helper->flashMessenger(array('message' => "Não foi possível criar Grupo " . $nome_do_grupo . ". Devido a: " . $e->getMessage(), 'status' => 'error'));
                $this->_helper->_redirector('index', 'aviso', 'sisad');
            }
        } else {
            $this->_helper->_redirector('addgrupo', 'aviso', 'sisad');
        }
    }
    
    public function divulgarAction()
    {
        $this->view->title = "Divulgar Documento";
        $form = new Sisad_Form_Divulgar();
        $this->view->form = $form;
        $data = $this->getRequest()->getParams();
        Zend_Debug::dump($data);
        exit;
        if ($this->getRequest()->isPost()) {
            $id = $data['grupo'];
            $id = array('id' => $id);
            try {
                $tabelaProprietarioGrupo = new Application_Model_DbTable_SadTbPrgrProprietGrupoDiv();
                $update = $tabelaProprietarioGrupo->setNewProprietarioGrupo($data);
                $this->_helper->flashMessenger(array('message' => "Proprietário(s) incluído(s) no Grupo com Sucesso.", 'status' => 'success'));
                $this->_helper->_redirector('edit', 'aviso', 'sisad', $id);
            } catch (Zend_Exception $exc) {
                $this->_helper->flashMessenger(array('message' => "Não foi possível incluir Proprietário(os) na Grupo. " . $e->getMessage(), 'status' => 'error'));
                $this->_helper->_redirector('edit', 'aviso', 'sisad', $id);
            }
        } else {
            $id = $this->getRequest()->getParam("grupo");
            $dados = new Application_Model_DbTable_SadTbGrdvGrupoDivulgacao();
            $rows = $dados->getGrupobyId($id);
            $form->populate($rows);
            $this->view->form = $form;
        }
    }
    
    public function ajaxnometipodocumentoAction()
    {
        $nomeTipoDocumento     = $this->_getParam('term','');
        $OcsTbDtpdTipoDoc = new Application_Model_DbTable_OcsTbDtpdTipoDoc();
        $nome_array = $OcsTbDtpdTipoDoc->getTipoDocumentosAjax($nomeTipoDocumento);
        $fim = count($nome_array);
        for ($i = 0; $i<$fim;$i++){
            $nome_array[$i] = array_change_key_case ($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }
    
    public function ajaxgruposdedivulgacaoAction()
    {
        $nomeGrupo = $this->_getParam('term','');
        $userNs = new Zend_Session_Namespace('userNs');
        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $divulgacao_avisos = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('LISTAS INTERNAS (AVISOS) ESPECIAL',$userNs->matricula);
        $tabelaDoliDocumentoLista = new Application_Model_DbTable_SadTbDoliDocumentoLista();
        if ($divulgacao_avisos) {
            $nome_array = $tabelaDoliDocumentoLista->getGruposAjax($nomeGrupo);
        }else{
            $nome_array = $tabelaDoliDocumentoLista->getGruposAjaxEspecial($nomeGrupo);
        }
        
        $fim = count($nome_array);
        for ($i = 0; $i<$fim;$i++){
            $nome_array[$i] = array_change_key_case ($nome_array[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($nome_array);
    }
    
     public function ajaxpessoasemjudsAction()
    {
        $matriculanome     = $this->_getParam('term','');
        
        $OcsTbPmatMatricula = new Application_Model_DbTable_OcsTbPmatMatricula();
        $sevidores = $OcsTbPmatMatricula->getNotJuizeseDesembargadores($matriculanome);
        
        $fim =  count($sevidores);
        for ($i = 0; $i<$fim; $i++ ) {
            $sevidores[$i] = array_change_key_case ($sevidores[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($sevidores);
    }
}
