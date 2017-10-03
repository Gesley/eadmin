<?php

class Tarefa_TipotarefaController extends Zend_Controller_Action
{
    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch()
    {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function init() 
    {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();
        $this->view->titleBrowser = 'e-OS - Sistema de Gerenciamento de Ordem de Serviço';
        $this->facade = App_Factory_FactoryFacade::createInstance('Tarefa_Facade_TipoTarefa');
    }

    public function indexAction()
    {
        $orderColumn = $this->_getParam('ordem', 'TPTA_NM_TAREFA');
        $getUrlOrder = $this->_getParam('direcao', 'ASC');
        $orderDirection = ($getUrlOrder == 'DESC') ? ('ASC') : ('DESC');
        $order = $orderColumn . ' ' . $getUrlOrder;
        $rows = $this->facade->listAll($order);
        $paginator = Zend_Paginator::factory($rows);
        $this->view->assign(array(
            'title'   => 'Tipo de Tarefa',
            'direcao' => $orderDirection,
            'data'    => $paginator
        ));
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }
    
    public function adicionarAction()
    {
        $form = new Tarefa_Form_TipoTarefa();
        $this->view->assign(array(
            'title' => 'Incluir Tipo de Tarefa',
            'form'  => $form
        ));
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                if ($this->facade->adicionar($data)) {
                    $this->_helper->flashMessenger(array('message' => 'Tipo de tarefa cadastrado com sucesso!', 'status' => 'success'));
                    $this->_helper->_redirector('index', 'tipotarefa', 'tarefa');
                } else {
                    $form->populate($data);
                }
            } else {
                $form->populate($data);
            }
            
        }
    }
    
    public function editarAction()
    {
        $form = new Tarefa_Form_TipoTarefa();
        $this->view->assign(array(
            'title' => 'Editar Tipo de Tarefa',
            'form'  => $form
        ));
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                if ($this->facade->editar($data)) {
                    $this->_helper->flashMessenger(array('message' => "Tipo de tarefa editado com sucesso!", 'status' => 'success'));
                    $this->_helper->_redirector('index', 'tipotarefa', 'tarefa');
                }
            }
        } else {
            $id = $this->_getParam('id', 0);
            $data = $this->facade->getById($id);
            $form->populate($data);
        }
    }

    public function excluirAction()
    {
        if ($this->facade->excluir($this->_getParam('id'))) {
            $this->_helper->flashMessenger(array(
                'message' => "Tipo de tarefa excluído com sucesso!", 'status'  => 'success'
            ));
            $this->_helper->_redirector('index', 'tipotarefa', 'tarefa');
        }
    }
    
    public function jsonverificatipotarefaAction()
    {
        $this->facadeTarefa = App_Factory_FactoryFacade::createInstance('Tarefa_Facade_Tarefa');
        $id = $this->_getParam('id', 0);
        $data = $this->facadeTarefa->listPorTarefa($id);
        if (count($data) > 0) {
            return $this->_helper->json->sendJson(array('message' => 'Esse Tipo de Tarefa já foi usado. Deseja realmente excluí-lo?', 'status' => 'success'));
        } else {
            return $this->_helper->json->sendJson(array('message' => 'Deseja realmente excluir o Tipo de Tarefa?', 'status' => 'success'));
        }
    }
    
}