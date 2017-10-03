<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Sosti_LabtiposoftwareController extends Zend_Controller_Action
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

    public function ajaxmarcaAction()
    {
        $marca = $this->_getParam('term', '');
        $OcsTbMarcMarca = new Application_Model_DbTable_OcsTbMarcMarca();
        $marca = $OcsTbMarcMarca->getMarcaLab($marca);

        $fim = count($marca);
        for ($i = 0; $i < $fim; $i++) {
            $marca[$i] = array_change_key_case($marca[$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($marca);
    }

    public function ajaxmodeloAction()
    {
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $OcsTbModeModelo = new Application_Model_DbTable_OcsTbModeModelo();
        $OcsTbModeModelo_array = $OcsTbModeModelo->getModeloLab($id);
        $this->view->modelos = $OcsTbModeModelo_array;
    }

    public function indexAction()
    {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'LTPS_ID_TP_SOFTWARE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SosTbLtpsTipoSoftware();
        $rows = $dados->getSoftwareList(null, $order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);

        $this->view->title = "Tipo de Software";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function addAction()
    {
        $this->view->title = 'Cadastrar Tipo de Software';
        $form = new Sosti_Form_LabCadastroTipoSoftware();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbLtpsTipoSoftware();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $desc = mb_strtoupper($data['LTPS_DS_TP_SOFTWARE'], 'UTF-8');
                $rowexists = $table->fetchRow(array('LTPS_DS_TP_SOFTWARE=?' => $desc)); //CHECA SE JÁ EXISTE UM A DESCRIÇÃO COM O MESMO NOME
                if (count($rowexists) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um Tipo de Software com o mesmo nome.";
                    $form->populate($data);
                } else {

                    unset($data['LTPS_ID_TP_SOFTWARE']);
                    $message = strtoupper($data['LTPS_DS_TP_SOFTWARE']);
                    $data['LTPS_DS_TP_SOFTWARE'] = mb_strtoupper($data['LTPS_DS_TP_SOFTWARE'], 'UTF-8');
                    $row = $table->createRow($data);
                    try {
                        $row->save();
                    } catch (Exception $exc) {
                        $erro = $exc->getMessage();
                        $this->_helper->flashMessenger(array('message' => "Não foi possível cadastrar o tipo de software. <br> $erro ", 'status' => 'error'));
                        return $this->_helper->_redirector('index', 'labtiposoftware', 'sosti');
                    }
                    $this->_helper->flashMessenger(array('message' => "O tipo de software: $message foi cadastrado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labtiposoftware', 'sosti');
                }
            }
        }
    }

    public function editAction()
    {

        $this->view->title = 'Editar Tipo de Software';
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Sosti_Form_LabCadastroTipoSoftware();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbLtpsTipoSoftware();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->getEditarTpSoftware($id);
            if ($row) {
                $data = $row;
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $message = strtoupper($data['LTPS_DS_TP_SOFTWARE']);
                $id = $data['LTPS_ID_TP_SOFTWARE'];
                $desc = mb_strtoupper($data['LTPS_DS_TP_SOFTWARE'], 'UTF-8');
                $rowexists = $table->fetchRow(array('LTPS_DS_TP_SOFTWARE=?' => $desc, 'LTPS_ID_TP_SOFTWARE != ?' => $id)); //CHECA SE JÁ EXISTE UM A DESCRIÇÃO COM O MESMO NOME
                if (count($rowexists) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um Tipo de Software com o mesmo nome.";
                    $form->populate($data);
                } else {
                    $data['LTPS_DS_TP_SOFTWARE'] = mb_strtoupper($data['LTPS_DS_TP_SOFTWARE'], 'UTF-8');
                    $row = $table->find($data['LTPS_ID_TP_SOFTWARE'])->current();
                    $row->setFromArray($data);
                    try {
                        $row->save();
                    } catch (Exception $exc) {
                        $erro = $exc->getMessage();
                        $this->_helper->flashMessenger(array('message' => "Não foi possível alterar o tipo de software. <br> $erro ", 'status' => 'error'));
                        return $this->_helper->_redirector('index', 'labtiposoftware', 'sosti');
                    }
                    $this->_helper->flashMessenger(array('message' => "O tipo de software: <strong>$message</strong> foi atualizado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labtiposoftware', 'sosti');
                }
            }
        }
    }

    public function delAction()
    {

        $this->view->title = 'Ecluir Tipo de Software';
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Sosti_Form_LabCadastroTipoSoftware();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_SosTbLtpsTipoSoftware();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->getEditarTpSoftware($id);
            if ($row) {
                $data = $row;
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                zend_debug::dump($data);
                $message = $data['LTPS_DS_TP_SOFTWARE'];
                $row = $table->find($data['LTPS_ID_TP_SOFTWARE'])->current();
                $row->setFromArray($data);
                try {
                    $row->delete();
                } catch (Exception $exc) {
                    $erro = $exc->getMessage();
                    $this->_helper->flashMessenger(array('message' => "Não foi possível exckuir o tipo de software. <br> $erro ", 'status' => 'error'));
                    return $this->_helper->_redirector('index', 'labtiposoftware', 'sosti');
                }
                $this->_helper->flashMessenger(array('message' => "O tipo de software: $message foi excluído!", 'status' => 'success'));
                return $this->_helper->_redirector('index', 'labtiposoftware', 'sosti');
            }
        }
    }

    public function ajaxcadastrotiposoftwareAction()
    {
        $descricao = $this->_getParam('term');
        $objTipoSoft = new Application_Model_DbTable_SosTbLtpsTipoSoftware ();
        $rows = $objTipoSoft->autoCompleteTipoSoftware($descricao);

        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows [$i] = array_change_key_case($rows [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($rows);
    }

}
