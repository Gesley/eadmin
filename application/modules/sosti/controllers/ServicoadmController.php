<?php

class Sosti_ServicoadmController extends Zend_Controller_Action
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
		$this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }

    public function indexAction()
    {
        $NsActionName = $this->getRequest()->getModuleName().$this->getRequest()->getControllerName().$this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'SSER_DS_SERVICO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

        $form = new Sosti_Form_Servico();
        
        $form->removeElement("SSER_DS_SERVICO");
        $form->removeElement("SSER_IC_ATIVO");
        $form->removeElement("SSER_IC_VISIVEL");
        $form->removeElement("SSER_IC_TOMBO");
        $form->removeElement("SSER_IC_ANEXO");
        $form->removeElement("REPLICAR_TRF");
        $form->removeElement("SSER_IC_VIDEOCONFERENCIA");
        
        $form->removeElement('Salvar');
        $listar = new Zend_Form_Element_Submit('Listar');
        $listar->setAttrib('class', 'listar');
        $listar->removeDecorator('DtDdWrapper')
                ->setAttrib('style', 'width: 200px;');
        $form->addElement($listar);
        
         if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //Zend_Debug::dump($data);

            
            if ( $form->isValid($data) ) {
                $NsAction->dataPost = $data;
                $dados = new Application_Model_DbTable_SosTbSserServico();
                $rows = $dados->getServicoPorGrupo($data["SSER_ID_GRUPO"], $order);
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                              ->setItemCountPerPage(count($rows));
                
                $this->view->data = $paginator;
                
            }else{
                $form->populate($data);
                $this->view->form = $form;
                return;
            }
       }else
       if(!is_null($NsAction->dataPost)){
            $data = $NsAction->dataPost;
            $dados = new Application_Model_DbTable_SosTbSserServico();
            $rows = $dados->getServicoPorGrupo($data["SSER_ID_GRUPO"], $order);
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage(count($rows));
            $this->view->data = $paginator;
            $form->populate($data);
            $this->view->form = $form;
       }else{
            $dados = new Application_Model_DbTable_SosTbSserServico();
            $rows = $dados->getServico($order);
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                     ->setItemCountPerPage(15);
            $this->view->data = $paginator;
       }
        
        $this->view->title = 'Serviços de TI';
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        
        $this->view->form = $form;
    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar novo serviço de TI';
        $form = new Sosti_Form_Servico();
        $this->view->form = $form;
        $form->removeElement("REPLICAR_TRF");
        $table  = new Application_Model_DbTable_SosTbSserServico();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['SSER_ID_SERVICO']);
                $message = $data['SSER_DS_SERVICO'];
                $row = $table->createRow($data);
                try {
                    $row->save();
                }catch (Exception $exc) {
                    $erro =  $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Ocorreu um erro <br/> $erro", 'status' => 'error'));
                    return $this->_helper->_redirector('index','servicoadm','sosti');
                }
                $this->_helper->flashMessenger ( array('message' => "O serviço: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','servicoadm','sosti');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar serviço de TI';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sosti_Form_Servico();
        $this->view->form = $form;
        $form->removeElement("REPLICAR_TRF");
        $table  = new Application_Model_DbTable_SosTbSserServico();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('SSER_ID_SERVICO = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $message = $data['SSER_DS_SERVICO'];
                $row = $table->find($data['SSER_ID_SERVICO'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O serviço: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','servicoadm','sosti');
            }
        }
    }
    
}
