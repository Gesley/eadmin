<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Sosti_LabtiposervicoController extends Zend_Controller_Action
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

    var $controller;
    var $module;
    var $refController; //CONTROLLER HTTP REFFFER
    var $refAction; //ACTION HTTP REFFFER

    public function init()
    {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        $this->controller = $this->getRequest()->getControllerName();
        $this->module = $this->getRequest()->getModuleName();
    }

    public function indexAction()
    {

        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'TPSE_ID_TP_SERVICO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SosTbTpseTipoServico ();
        $rows = $dados->getTipoServico($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);

        $this->view->title = "Tipo Serviço";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function addAction()
    {
        $this->view->title = 'Cadastrar Tipo Serviço';
        $form = new Sosti_Form_LabCadastroTipoServico ();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbTpseTipoServico ();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $desc = mb_strtoupper($data['TPSE_DS_TP_SERVICO'], 'UTF-8');
                $hasrow = $table->fetchRow(array('TPSE_DS_TP_SERVICO=?' => $desc));
                if (count($hasrow) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um Tipo de Serviço com o mesmo nome.";
                    $form->populate($data);
                } else {
                    unset($data ['TPSE_ID_TP_SERVICO']);
                    $message = $data ['TPSE_DS_TP_SERVICO'];
                    $data ['TPSE_DS_TP_SERVICO'] = mb_strtoupper($data ['TPSE_DS_TP_SERVICO'], 'UTF-8');
                    $row = $table->createRow($data);
                    $row->save();
                    $this->_helper->flashMessenger(array('message' => "O tipo de serviço: <strong>$message</strong> foi cadastrado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labtiposervico', 'sosti');
                }
            } else {
                $form->populate($data);
            }
        }
    }

    public function editAction()
    {
        $this->view->title = 'Alterar Tipo Serviço';
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Sosti_Form_LabCadastroTipoServico();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbTpseTipoServico();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('TPSE_ID_TP_SERVICO = ?' => $id));
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

                $desc = mb_strtoupper($data['TPSE_DS_TP_SERVICO'], 'UTF-8');
                $hasrow = $table->fetchRow(array('TPSE_DS_TP_SERVICO=?' => $desc, 'TPSE_ID_TP_SERVICO != ?' => $data['TPSE_ID_TP_SERVICO']));

                if (count($hasrow) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um Tipo de Serviço com o mesmo nome.";
                    $form->populate($data);
                } else {

                    $message = $data['TPSE_DS_TP_SERVICO'];
                    $data ['TPSE_DS_TP_SERVICO'] = mb_strtoupper($data ['TPSE_DS_TP_SERVICO'], 'UTF-8');
                    $row = $table->find($data['TPSE_ID_TP_SERVICO'])->current();
                    $row->setFromArray($data);
                    $row->save();
                    $this->_helper->flashMessenger(array('message' => "O tipo de serviço: <strong>$message</strong> foi atualizado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labtiposervico', 'sosti');
                }
            }
        }
    }

}
