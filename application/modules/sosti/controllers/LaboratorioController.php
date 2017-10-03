<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sosti_LaboratorioController extends Zend_Controller_Action {
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
		$this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }

    public function acoesAction()
    {
        $data = $this->getRequest()->getPost();
        Zend_Debug::dump($data);exit;
    }
    
    public function formsAction()
    {
        $getTipo    = $this->_getParam('tipo');
        $getID      = $this->_getParam('id');
        $post       = $this->getRequest()->getPost();
        
        if($post['acao'] == 'tipoUsuario'){
            $this->view->title = 'Cadastrar Novo Tipo de Usuário';
            $form = new Sosti_Form_LabCadastroTipoUsuario();
        }
        
        elseif($post['acao'] == 'tipoSoftware'){
            $this->view->title = 'Cadastrar Novo Tipo de Software';
            $form = new Sosti_Form_LabCadastroTipoSoftware();
        }
        
        elseif($post['acao'] == 'cadastroSoftware'){
            $this->view->title = 'Cadastrar Novo Software';
            $form = new Sosti_Form_LabCadastroSoftware();
        }
        
        elseif($post['acao'] == 'cadastroHardware'){
            $this->view->title = 'Cadastrar Novo Hardware';
            $form = new Sosti_Form_LabCadastroHardware();
        }
        
        elseif($getTipo == 'cadastroMarca'){
            $this->view->title = 'Cadastrar Nova Marca';
            $data = new Application_Model_DbTable_OcsTbMarcMarca();
            //$dados = $data->getEditarTpSoftware($getID);
            $form = new Sosti_Form_LabCadastroMarca();
        }  
        
        elseif($getTipo == 'cadastroModelo'){
            $this->view->title = 'Cadastrar Novo Modelo';
            $data = new Application_Model_DbTable_OcsTbModeModelo();
            //$dados = $data->getEditarTpSoftware($getID);
            $form = new Sosti_Form_LabCadastroModelo();   
        } 
        
        elseif($getTipo == 'editarUsuario'){
            $this->view->title = 'Editar Tipo de Usuário';
            $data = new Application_Model_DbTable_SosTbLtpuTipoUsuario();
            $dados = $data->getEditarTpUsuario($getID);
            $form = new Sosti_Form_LabCadastroTipoUsuario();
        }
        
        elseif($getTipo == 'editarSoftware'){
            $this->view->title = 'Editar Tipo de Software';
            $data = new Application_Model_DbTable_SosTbLtpsTipoSoftware();
            $dados = $data->getEditarTpSoftware($getID);
            $form = new Sosti_Form_LabCadastroTipoSoftware();         
        }
        $form->populate($dados[0]);
        $this->view->form = $form;
    }
    
    public function ajaxdesctomboAction()
    {
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $Tombo = new Application_Model_DbTable_TomboTiCentral();
        $Tombo_array = $Tombo->getDescTombo($id);
        $this->view->desctombo = $Tombo_array;
    }
    
    public function hardwareAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $siglalotacao = $aNamespace->siglalotacao;
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'LHDW_ID_HARDWARE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SosTbLhdwMaterialAlmox();
       $rows = $dados->getHardwares($order);

       $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Hardwares Cadastrados";
    }

    public function softwareAction()
    {
       /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $siglalotacao = $aNamespace->siglalotacao;
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'LSFW_ID_SOFTWARE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SosTbLsfwSoftware();
       $rows = $dados->getSoftwares($order);
       $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Softwares Cadastrados";
    }

    public function tiposoftwareAction()
    {
       /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $siglalotacao = $aNamespace->siglalotacao;
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'LTPS_ID_TP_SOFTWARE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SosTbLtpsTipoSoftware();
       $rows = $dados->getTipoSoftware($order);

       $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Cadastro Tipo de Software";
    }

    public function tipousuarioAction()
    {
       /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $siglalotacao = $aNamespace->siglalotacao;
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'LTPU_ID_TP_USUARIO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SosTbLtpuTipoUsuario();
       $rows = $dados->getTipoUsuario($order);

       $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Cadastro Tipo de Usuário";
    }

    public function checklistAction()
    {
        $this->view->title = 'Cadastro da Ficha de Serviço do Laboratório';
        $form = new Sosti_Form_LabCheckList();
        $this->view->form = $form;
    }

    public function consultasAction()
    {
       /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $siglalotacao = $aNamespace->siglalotacao;
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SadTbMoviMovimentacao();
       $rows = $dados->getCaixaUnidadeRecebidos($aNamespace->codlotacao,$aNamespace->siglasecao,$order);

       $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Caixa Entrada - $siglalotacao";
    }

    public function marcaAction()
    {
       /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $siglalotacao = $aNamespace->siglalotacao;
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $marca = new Application_Model_DbTable_OcsTbMarcMarca();
       $rows = $marca->getMarca();
       
       Zend_Debug::dump($rows);
       exit;

       $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Marcas";
    }    
    
    public function modeloAction()
    {
       /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $aNamespace = new Zend_Session_Namespace('userNs');
        $siglalotacao = $aNamespace->siglalotacao;
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'MOVI_DH_ENCAMINHAMENTO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $marca = new Application_Model_DbTable_OcsTbModeModelo();
       $rows = $marca->getModelo();       

       $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->title = "Modelos";
    }     
    
    
}
