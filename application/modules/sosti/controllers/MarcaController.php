<?php

class Sosti_MarcaController extends Zend_Controller_Action
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
        $this->view->titleBrowser = 'e-Sosti';
    }

    public function indexAction()
    {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'MARC_DS_MARCA');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_OcsTbMarcMarca();
        $rows = $dados->getMarca($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);

        $this->view->title = 'Marca';
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function addAction()
    {
        $this->view->title = 'Cadastrar Marca';
        $form = new Sosti_Form_Marca();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_OcsTbMarcMarca();
        $usuario = new Zend_Session_Namespace('userNs');
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {

                /**
                 * Verificar se já existe o registro no banco
                 */
                $message = $data['MARC_DS_MARCA'];
                $desc = mb_strtoupper($data['MARC_DS_MARCA'], 'UTF-8');
                $where = "UPPER(MARC_DS_MARCA) = '$desc'";
                $existe = $table->fetchAll($where);

                if (count($existe) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe uma marca com o mesmo nome.";
                    $form->populate($data);
                } else {
                    unset($data['MARC_ID_MARCA']);
                    $data['MARC_CD_MAT_INCLUSAO'] = strtoupper($usuario->matricula);
                    $data['MARC_DT_INCLUSAO'] = new Zend_Db_Expr('SYSDATE');
                    $data['MARC_DS_MARCA'] = $desc;
                    $row = $table->createRow($data);
                    $row->save();
                    $this->_helper->flashMessenger(array('message' => "A marca: $message foi cadastrada!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'marca', 'sosti');
                }
            }
        }
    }

    public function editAction()
    {
        $this->view->title = 'Editar Marca';
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Sosti_Form_Marca();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_OcsTbMarcMarca();
        $usuario = new Zend_Session_Namespace('userNs');
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('MARC_ID_MARCA = ?' => $id));
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
                /**
                 * Verificar se já existe o registro no banco
                 */
                $message = strtoupper($data['MARC_DS_MARCA']);
                $desc = mb_strtoupper($data['MARC_DS_MARCA'], 'UTF-8');
                $where = "UPPER(MARC_DS_MARCA) = '$desc' AND MARC_ID_MARCA != " . $data['MARC_ID_MARCA'];
                $existe = $table->fetchAll($where);

                if (count($existe) > 0) {
                    $this->view->msg_error = "Alteração não realizada, pois já existe uma marca com o mesmo nome.";
                    $form->populate($data);
                } else {
                    $row = $table->find($data['MARC_ID_MARCA'])->current();
                    $data['MARC_CD_MAT_INCLUSAO'] = $usuario->matricula;
                    $data['MARC_DT_INCLUSAO'] = new Zend_Db_Expr('SYSDATE');
                    $data['MARC_DS_MARCA'] = mb_strtoupper($data['MARC_DS_MARCA'], 'UTF-8');
                    $row->setFromArray($data);
                    $row->save();
                    $this->_helper->flashMessenger(array('message' => "A marca: $message foi atualizada!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'marca', 'sosti');
                }
            }
        }
    }

}
