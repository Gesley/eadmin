<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sisad_TipocaixaController extends Zend_Controller_Action {
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

    }
    
    public function listAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'TPCX_ID_TIPO_CAIXA');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SadTbTpcxTipoCaixa();
       $rows = $dados->fetchAll(null, $order)->toArray();
       
       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(10);

       $this->view->title = "Tipos de Caixa";
       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar novo Tipo de Caixa';
        $form = new Sisad_Form_Tipocaixa();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbTpcxTipoCaixa();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['TPCX_ID_TIPO_CAIXA']);
                
                $message = $data['TPCX_DS_CAIXA_ENTRADA'];
                
                $data['TPCX_ID_TIPO_CAIXA'] = count($table->fetchAll())+1;
                $data['TPCX_DS_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('$data[TPCX_DS_CAIXA_ENTRADA]')");
                
                $row = $table->createRow($data);
                try {
                    $row->save();
                } catch (Exception $exc) {
                    //echo $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível adicionar o Tipo de Caixa: $message!", 'status' => 'error'));
                    return $this->_helper->_redirector('list','tipocaixa','sisad');
                }
                
                $this->_helper->flashMessenger ( array('message' => "O Tipo de Caixa: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('list','tipocaixa','sisad');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Tipo de Caixa';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sisad_Form_Tipocaixa();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbTpcxTipoCaixa();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('TPCX_ID_TIPO_CAIXA = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $row = $table->find($data['TPCX_ID_TIPO_CAIXA'])->current();
                $message = $data['TPCX_DS_CAIXA_ENTRADA'];
                
                $data['TPCX_DS_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('$data[TPCX_DS_CAIXA_ENTRADA]')");
                $row->setFromArray($data);
                
                try {
                    $row->save();
                } catch (Exception $exc) {
                    //echo $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível alterar o Tipo de Caixa: $message!", 'status' => 'error'));
                    return $this->_helper->_redirector('list','tipocaixa','sisad');
                }
                $this->_helper->flashMessenger ( array('message' => "O Tipo de Caixa: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('list','tipocaixa','sisad');
            }
        }
    }
    
    public function delAction()
    {
        $this->view->title = 'Excluir Tipo de Caixa';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sisad_Form_Tipocaixa();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbTpcxTipoCaixa();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('TPCX_ID_TIPO_CAIXA = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
                
                /*adiciona o elemento submit excluir*/
                $form->removeElement('Salvar');
                $excluir = new Zend_Form_Element_Submit('Excluir');
                $form->addElement($excluir);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $row = $table->find($data['TPCX_ID_TIPO_CAIXA'])->current();
                try {
                    $row->delete();
                } catch (Exception $exc) {
                    //echo $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível excluír o Tipo de Caixa:  $data[TPCX_DS_CAIXA_ENTRADA]!", 'status' => 'error'));
                    return $this->_helper->_redirector('list','tipocaixa','sisad');
                }
                $this->_helper->flashMessenger ( array('message' => "O Tipo de Caixa: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('list','tipocaixa','sisad');
            }
        }
    }

}
