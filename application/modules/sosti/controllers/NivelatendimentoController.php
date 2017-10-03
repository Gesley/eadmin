<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php

class Sosti_NivelatendimentoController extends Zend_Controller_Action
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
		
		$this->view->titleBrowser = 'e-Sosti';
    }

    public function indexAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'SNAT_ID_NIVEL');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        
        $form = new Sosti_Form_NivelAtendimento();
        
        
        $form->removeElement("SNAT_CD_NIVEL");
        $form->removeElement("SNAT_DS_NIVEL");
        $form->removeElement("SNAT_SG_NIVEL");
        $form->removeElement("SNAT_PZ_ATENDIMENTO");
        
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
                
                $dados = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
                $rows = $dados->getNiveisPorGrupo($data["SNAT_ID_GRUPO"], $order);
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                              ->setItemCountPerPage(count($rows));
                
            }else{
                $form->populate($data);
                $this->view->form = $form;
                return;
            }
       }else{
            $dados = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
            $rows = $dados->getNiveis($order);
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                     ->setItemCountPerPage(15);
       }
        
        
        
        
        $this->view->title = "Níveis de Atendimento";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        
        $this->view->form = $form;
        
        
    }
    
    public function addAction()
    {
        $this->view->title = "Adicionar Novo Nível de Atendimento";
        $form = new Sosti_Form_NivelAtendimento();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['SNAT_ID_NIVEL']);
                //$data["SNAT_CD_NIVEL"] = $table->getMaiorIndicador($data["SNAT_ID_GRUPO"]) + 1;
                //Zend_Debug::dump($data);exit;
                $message = $data['SNAT_DS_NIVEL'];
                
                $row = $table->createRow($data);
                //Zend_Debug::dump($row->toArray()); exit;;
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O nível de atendimento: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','nivelatendimento','sosti');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = "Alterar Nível de Atendimento";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sosti_Form_NivelAtendimento();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SosTbSnatNivelAtendimento();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('SNAT_ID_NIVEL = ?' => $id));
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
                $message = $data['SNAT_DS_NIVEL'];
                $row = $table->find($data['SNAT_ID_NIVEL'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O nível de atendimento: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','nivelatendimento','sosti');
            }
        }
    }

}
