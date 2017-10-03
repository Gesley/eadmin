<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Sosti_LabtipousuarioController extends Zend_Controller_Action
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

        /* Initialize action controller here */
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }

    public function indexAction()
    {

        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'LTPU_ID_TP_USUARIO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SosTbLtpuTipoUsuario();
        $rows = $dados->getTipoUsuario($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);

        $this->view->title = "Tipo Usuário";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function addAction()
    {
        $this->view->title = 'Cadastrar Tipo Usuário';
        $form = new Sosti_Form_LabCadastroTipoUsuario();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbLtpuTipoUsuario();

        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();


            if ($form->isValid($data)) {

                $desc = mb_strtoupper($data['LTPU_DS_TP_USUARIO'], 'UTF-8');
                $rowexists = $table->fetchRow(array('LTPU_DS_TP_USUARIO=?' => $desc)); //CHECA SE JÁ EXISTE UM A DESCRIÇÃO COM O MESMO NOME
                if (count($rowexists) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um Tipo de Usuário com o mesmo nome.";
                    $form->populate($data);
                } else {
                    unset($data['LTPU_ID_TP_USUARIO']);
                    unset($data['Salvar']);
                    $message = $data['LTPU_DS_TP_USUARIO'];
                    $data['LTPU_DS_TP_USUARIO'] = mb_strtoupper($data['LTPU_DS_TP_USUARIO'], 'UTF-8');
                    $row = $table->createRow($data);
                    $row->save();
                    $this->_helper->flashMessenger(array('message' => "O tipo de usuário: $message foi cadastrado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labtipousuario', 'sosti');
                }
            } else {
                $form->populate($data);
            }
        }
    }

    public function editAction()
    {
        $this->view->title = 'Editar Tipo Usuário';
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Sosti_Form_LabCadastroTipoUsuario();

        $table = new Application_Model_DbTable_SosTbLtpuTipoUsuario();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->getEditarTpUsuario($id);
            if ($row) {
                $data = $row;
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            if ($form->isValid($data)) {
                $message = $data['LTPU_DS_TP_USUARIO'];

                $id = $data['LTPU_ID_TP_USUARIO'];
                $desc = mb_strtoupper($data['LTPU_DS_TP_USUARIO'], 'UTF-8');
                $rowexists = $table->fetchRow(array('LTPU_DS_TP_USUARIO = ?' => $desc, 'LTPU_ID_TP_USUARIO != ?' => $id)); //CHECA SE JÁ EXISTE UM A DESCRIÇÃO COM O MESMO NOME
                if (count($rowexists) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um Tipo de Usuário com o mesmo nome.";
                    $form->populate($data);
                } else {

                    $data['LTPU_DS_TP_USUARIO'] = mb_strtoupper($data['LTPU_DS_TP_USUARIO'], 'UTF-8');
                    $row = $table->find($data['LTPU_ID_TP_USUARIO'])->current();
                    unset($data['LTPU_ID_TP_USUARIO']);
                    $row->setFromArray($data);
                    $row->save();
                    $this->_helper->flashMessenger(array('message' => "O tipo de usuário: <strong>$message</strong> foi atualizado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labtipousuario', 'sosti');
                }
            } else {
                $form->populate($data);
            }
        }
        $this->view->form = $form;
    }

}
