<?php

class Sosti_AvisoController extends Zend_Controller_Action
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
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'SAVI_DS_AVISO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SosTbSaviAviso();
       $rows = $dados->getAvisosAtivos($order);

       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(10);

       $this->view->title = "Avisos";
       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function formAction()
    {
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sosti_Form_Aviso();
        $table  = new Application_Model_DbTable_SosTbSaviAviso();

        if (isset($id) && $id) {
            $this->view->title = "Excluir o aviso";
            $form->setAction($this->view->baseUrl().'/sosti/aviso/save');
        } else {
            $this->view->title = "Cadastrar novo aviso";
        }
        if ($id) {
            $row = $table->fetchRow(array('SAVI_ID_AVISO = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        $this->view->form = $form;
    }

    public function saveAction()
    {
        $form = new Sosti_Form_Aviso();
        $table = new Application_Model_DbTable_SosTbSaviAviso();
        $userNs = new Zend_Session_Namespace('userNs');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                if (isset($data['SAVI_ID_AVISO']) && $data['SAVI_ID_AVISO']) {
                    $data['SAVI_CD_MATR_EXC'] = strtoupper($userNs->matricula);
                    $data['SAVI_DH_EXCLUSAO'] = new Zend_Db_Expr("SYSDATE");
                    $row = $table->find($data['SAVI_ID_AVISO'])->current();
                    $row->setFromArray($data);
                    $id = $row->save();
                    $this->_helper->flashMessenger ( array('message' => 'Aviso excluído!', 'status' => 'success'));
                    return $this->_helper->_redirector('index','aviso','sosti', array('id' => $id));
                } else {
                    unset($data['SAVI_ID_AVISO']);
                    $data['SAVI_CD_MATR_CAD'] = strtoupper($userNs->matricula);
                    $data['SAVI_DH_CADASTRO'] = new Zend_Db_Expr("SYSDATE");
                    $data['SAVI_SG_SECAO_LOTACAO'] = strtoupper($userNs->siglasecao);
                    $data['SAVI_CD_LOTACAO'] = $userNs->codlotacao;
                    $row = $table->createRow($data);
                    $id = $row->save();
                    $this->_helper->flashMessenger ( array('message' => 'Aviso incluido!', 'status' => 'success'));
                    return $this->_helper->_redirector('index','aviso','sosti', array('id' => $id));
                }

            } else {
                $form->populate($data);
                $this->view->form = $form;
                $this->render('form');
            }
        }
    }
    
}
