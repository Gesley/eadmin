<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sosti_IndicadornivelservicoController extends Zend_Controller_Action {
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
        $order_column = $this->_getParam('ordem', 'SINS_CD_INDICADOR');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
       $rows = $dados->getIndicNivelServico($order);

       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(15);

       $this->view->title = "Indicador de Níveis de Serviço";
       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function addAction()
    {
        $this->view->title = "Cadastrar Indicador de Nível de Serviço";
        $form = new Sosti_Form_Indicadornivelservico();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['SINS_ID_INDICADOR']);
                $data["SINS_CD_INDICADOR"] = $table->getMaiorIndicador($data["SINS_ID_GRUPO"]) + 1;
                $message = $data['SINS_DS_INDICADOR'];
                $row = $table->createRow($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O indicador de nível de seviço: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','indicadornivelservico','sosti');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Indicador de Nível de Serviço';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sosti_Form_Indicadornivelservico();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('SINS_ID_INDICADOR = ?' => $id));
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
                $message = $data['SINS_DS_INDICADOR'];
                $row = $table->find($data['SINS_ID_INDICADOR'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O serviço: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','indicadornivelservico','sosti');
            }
        }
    }
    
}
