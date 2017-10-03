<?php

class Sosti_LabmodeloController extends Zend_Controller_Action
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
        $order_column = $this->_getParam('ordem', 'MODE_DS_MODELO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_OcsTbModeModelo();
        $rows = $dados->getModelo($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);

        $this->view->title = 'Modelos';
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function addAction()
    {
        $this->view->title = 'Cadastrar Modelo';
        $form = new Sosti_Form_LabCadastroModelo();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_OcsTbModeModelo();
        $objgrupo = new Application_Model_DbTable_OcsTbGrupGrupo();

        $userNs = new Zend_Session_Namespace('userNs');
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            //ATUALIZA A COMBO DA MARCA ANTES DE SUBMTETERO FORMULARIO.::1
            $marcaCombo = $form->getElement('MODE_ID_MARCA');
            if ($data['MODE_ID_GRUPO_MAT_SERV'] != "") {
                $lstGrupos = $objgrupo->getMarcapeloGrupoID($data['MODE_ID_GRUPO_MAT_SERV']);
                foreach ($lstGrupos as $grupo) {
                    $marcaCombo->addMultiOptions(array($grupo["MARC_ID_MARCA"] => $grupo["MARC_DS_MARCA"]));
                }
            }

            if ($form->isValid($data)) {

                /**
                 * Verificar se ja existe registro no banco
                 */
                $marca = $data["MODE_ID_MARCA"];
                $grupo = $data['MODE_ID_GRUPO_MAT_SERV'];
                $desc = mb_strtoupper($data['MODE_DS_MODELO'], 'UTF-8');
                $where = "UPPER(MODE_DS_MODELO) = '$desc' AND MODE_ID_MARCA = $marca";
                $existe = $table->fetchAll($where);
                if (count($existe) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um modelo com o mesmo nome.";
                    $form->populate($data);
                } else {
                    unset($data['MODE_ID_MODELO']);
                    $data['MODE_CD_MAT_INCLUSAO'] = $userNs->matricula;
                    $data['MODE_DT_INCLUSAO'] = new Zend_Db_Expr('SYSDATE');
                    $message = strtoupper($data['MODE_DS_MODELO']);
                    $data['MODE_DS_MODELO'] = $desc;

                    try {
                        $row = $table->createRow($data);
                        $row->save();
                    } catch (Exception $exc) {
                        $erro = $exc->getMessage();
                        $this->_helper->flashMessenger(array('message' => "Não foi possível cadastrar o modelo. <br> $erro ", 'status' => 'error'));
                        return $this->_helper->_redirector('index', 'labmodelo', 'sosti');
                    }
                    $this->_helper->flashMessenger(array('message' => "O modelo<strong> $message </strong> foi cadastrado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labmodelo', 'sosti');
                }
            }
        }
    }

    public function editAction()
    {
        $this->view->title = 'Editar Modelo';
        $form = new Sosti_Form_LabCadastroModelo();
        $this->view->form = $form;
        $table = new Application_Model_DbTable_OcsTbModeModelo();
        $objgrupo = new Application_Model_DbTable_OcsTbGrupGrupo();
        $objmarca = new Application_Model_DbTable_OcsTbMarcMarca();
        $userNs = new Zend_Session_Namespace('userNs');

        /**
         * Busca pelo id da linha a ser alterada
         */
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        if (empty($id)) {
            //redirect informando erro
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            $row = $table->getModeloById($id);
            if ($row) {
                $dados['MODE_ID_GRUPO_MAT_SERV'] = $data['MODE_ID_GRUPO_MAT_SERV'];
                $dados['MODE_ID_MARCA'] = $data['MODE_ID_MARCA'];
                $dados['MODE_DS_MODELO'] = $data['MODE_DS_MODELO'];
                $dados['MODE_ID_MODELO'] = $data['MODE_ID_MODELO'];

                $marcaCombo = $form->getElement('MODE_ID_MARCA');
                if ($data['MODE_ID_GRUPO_MAT_SERV'] != "") {
                    $listaMarcas = $objgrupo->getMarcapeloGrupoID($data['MODE_ID_GRUPO_MAT_SERV']);
                    //$listaMarcas = $objmarca->getMarca();
                    foreach ($listaMarcas as $marca) {
                        $marcaCombo->addMultiOptions(array($marca["MARC_ID_MARCA"] => $marca["MARC_DS_MARCA"]));
                    }
                }
            }
            $form->populate($dados);

            if ($form->isValid($data)) {

                /**
                 * Verificar se ja existe registro no banco
                 */
                $marca = $data["MODE_ID_MARCA"];
                $grupo = $data['MODE_ID_GRUPO_MAT_SERV'];
                $desc = mb_strtoupper($data['MODE_DS_MODELO'], 'UTF-8');
                $where = "UPPER(MODE_DS_MODELO) = '$desc' AND MODE_ID_MARCA = $marca AND MODE_ID_GRUPO_MAT_SERV = $grupo";
                $existe = $table->fetchAll($where);
                if (count($existe) > 0) {
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um modelo com o mesmo nome.";
                    $form->populate($data);
                } else {
                    $row = $table->find($data['MODE_ID_MODELO'])->current();
                    $message = strtoupper($data['MODE_DS_MODELO']);
                    $datatoSave['MODE_CD_MAT_INCLUSAO'] = $userNs->matricula;
                    $datatoSave['MODE_DT_INCLUSAO'] = new Zend_Db_Expr('SYSDATE');
                    $datatoSave['MODE_ID_MARCA'] = $data['MODE_ID_MARCA'];
                    $datatoSave['MODE_ID_GRUPO_MAT_SERV'] = $data['MODE_ID_GRUPO_MAT_SERV'];
                    $datatoSave['MODE_DS_MODELO'] = $desc;

                    try {
                        $row->setFromArray($datatoSave);
                        $row->save();
                    } catch (Exception $exc) {
                        $erro = $exc->getMessage();
                        $this->_helper->flashMessenger(array('message' => "Não foi possível alterar o modelo. <br> $erro ", 'status' => 'error'));
                        return $this->_helper->_redirector('index', 'labmodelo', 'sosti');
                    }
                    $this->_helper->flashMessenger(array('message' => "O modelo <strong>" . $message . "</strong> foi atualizado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labmodelo', 'sosti');
                }
            }
        } else {
            $row = $table->getModeloById($id);
            if ($row) {
                $dados['MODE_ID_GRUPO_MAT_SERV'] = $row['MODE_ID_GRUPO_MAT_SERV'];
                $dados['MODE_ID_MARCA'] = $row['MODE_ID_MARCA'];
                $dados['MODE_DS_MODELO'] = $row['MODE_DS_MODELO'];
                $dados['MODE_ID_MODELO'] = $row['MODE_ID_MODELO'];

                $marcaCombo = $form->getElement('MODE_ID_MARCA');
                if(!empty($dados['MODE_ID_GRUPO_MAT_SERV'])){
                    $listaMarcas = $objgrupo->getMarcapeloGrupoID($dados['MODE_ID_GRUPO_MAT_SERV']);
                    //$listaMarcas = $objmarca->getMarca();
                    foreach ($listaMarcas as $marca) {
                        $marcaCombo->addMultiOptions(array($marca["MARC_ID_MARCA"] => $marca["MARC_DS_MARCA"]));
                    }
                }
                $form->populate($dados);
            }
        }
    }

    public function ajaxcadastromodeloAction()
    {
        $modelo = $this->_getParam('term');
        $objmodelo = new Application_Model_DbTable_OcsTbModeModelo();
        $rows = $objmodelo->getautocompleteModelo(strtoupper($modelo));

        for ($i = 0; $i < count($rows); $i++) {
            $rows [$i] = array_change_key_case($rows [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($rows);
    }

    public function ajaxgetmarcapelogrupoAction()
    {
        $grupoID = $this->_getParam('id');
        $objgrupo = new Application_Model_DbTable_OcsTbGrupGrupo();
        $rows = $objgrupo->getMarcapeloGrupoID($grupoID);
        $this->view->marcas = $rows;
    }

}
