<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sisad_CaixaentradaController extends Zend_Controller_Action {
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
        $order_column = $this->_getParam('ordem', 'CXEN_ID_CAIXA_ENTRADA');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
       $rows = $dados->getCaixas($order);
       
       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(10);

       $this->view->title = "Caixas de Entrada";
       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar novo Tipo de Caixa';
        $form = new Sisad_Form_CaixaEntrada();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $userNamespace = new Zend_Session_Namespace('userNs');
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['CXEN_ID_CAIXA_ENTRADA']);
                
                $message = $data['CXEN_DS_CAIXA_ENTRADA'];
                
                $data['CXEN_ID_CAIXA_ENTRADA'] = count($table->fetchAll())+1;
                $data['CXEN_DS_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('$data[CXEN_DS_CAIXA_ENTRADA]')");
                                                    
                $data['CXEN_DT_INCLUSAO']= new Zend_Db_Expr("SYSDATE"); 
                $data['CXEN_CD_MATRICULA_INCLUSAO'] = $userNamespace->matricula;
                
                unset($data['CXEN_DT_EXCLUSAO']);
                unset($data['CXEN_CD_MATRICULA_EXCLUSAO']);
                
                $row = $table->createRow($data);
                
//                Zend_Debug::dump($row->toArray());
//                exit;
                
                try {
                    $row->save();
                } catch (Exception $exc) {
                    echo $exc->getMessage();
                    EXIT;
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível adicionar a Caixa de Entrada: $message!", 'status' => 'error'));
                    return $this->_helper->_redirector('list','caixaentrada','sisad');
                }
                
                $this->_helper->flashMessenger ( array('message' => "A Caixa de Entrada: $message foi cadastrada!", 'status' => 'success'));
                return $this->_helper->_redirector('list','caixaentrada','sisad');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Caixa de Entrada';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sisad_Form_caixaentrada();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $userNamespace = new Zend_Session_Namespace('userNs');
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('CXEN_ID_CAIXA_ENTRADA = ?' => $id));
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
                $row = $table->find($data['CXEN_ID_CAIXA_ENTRADA'])->current();
                $message = $data['CXEN_DS_CAIXA_ENTRADA'];
                
                $data['CXEN_DT_INCLUSAO']= new Zend_Db_Expr("SYSDATE"); 
                $data['CXEN_CD_MATRICULA_INCLUSAO'] = $userNamespace->matricula;
                
                $data['CXEN_DS_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('$data[CXEN_DS_CAIXA_ENTRADA]')");
                $row->setFromArray($data);
                
                try {
                    $row->save();
                } catch (Exception $exc) {
                    echo $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível alterar a Caixa de Entrada: $message!", 'status' => 'error'));
                    return $this->_helper->_redirector('list','caixaentrada','sisad');
                }
                $this->_helper->flashMessenger ( array('message' => "A Caixa de Entrada: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('list','caixaentrada','sisad');
            }
        }
    }
    
    public function delAction()
    {
        $this->view->title = 'Excluir Caixa de Entrada';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sisad_Form_caixaentrada();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $userNamespace = new Zend_Session_Namespace('userNs');
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('CXEN_ID_CAIXA_ENTRADA = ?' => $id));
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
                $row = $table->find($data['CXEN_ID_CAIXA_ENTRADA'])->current();
                $message = $data['CXEN_DS_CAIXA_ENTRADA'];
                
                $data['CXEN_DT_EXCLUSAO']= new Zend_Db_Expr("SYSDATE"); 
                $data['CXEN_CD_MATRICULA_EXCLUSAO'] = $userNamespace->matricula;
                
                unset($data['CXEN_DT_INCLUSAO']);
                unset($data['CXEN_CD_MATRICULA_INCLUSAO']);
                
                $data['CXEN_DS_CAIXA_ENTRADA'] = new Zend_Db_Expr("UPPER('$data[CXEN_DS_CAIXA_ENTRADA]')");
                $row->setFromArray($data);
                try {
                    $row->save();
                } catch (Exception $exc) {
                    //echo $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Não foi possível excluír a Caixa de Entrada:  $data[CXEN_DS_CAIXA_ENTRADA]!", 'status' => 'error'));
                    return $this->_helper->_redirector('list','caixaentrada','sisad');
                }
                $this->_helper->flashMessenger ( array('message' => "A Caixa de Entrada: $message foi excluída!", 'status' => 'success'));
                return $this->_helper->_redirector('list','caixaentrada','sisad');
            }
        }
    }

}
