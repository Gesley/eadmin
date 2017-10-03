<?php

/**
 * LabGrupoController
 * 
 * @author
 * @version 
 */
require_once 'Zend/Controller/Action.php';

class Sosti_LabgrupoController extends Zend_Controller_Action
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

    /**
     * Listagem os registros da tabela.
     */
    public function init()
    {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }

    public function indexAction()
    {

        $this->view->title = "Grupo";
        $objModelGrupo = new Application_Model_DbTable_OcsTbGrupGrupo ();
        $order_column = $this->_getParam('ordem', 'GRUP_DS_GRUPO_MAT_SERV');
        $order_direction = $this->_getParam('direcao', 'DESC');
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        $order = $order_column . ' ' . $order_direction;
        $rows = $objModelGrupo->getGrupos($order);
        $paginator = Zend_Paginator::factory($rows);
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');

        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    /**
     * Adiciona um grupo novo na lista
     * 
     */
    public function addAction()
    {

        $objModelGrupo = new Application_Model_DbTable_OcsTbGrupGrupo ();
        $objGramGrupo = new Application_Model_DbTable_OcsTbGramGrupoMarca();
        $objAuditModel = new Application_Model_DbTable_OcsTbGrmaAuditoria ();
        $sys = new Application_Model_DbTable_Dual ();
        $userNs = new Zend_Session_Namespace('userNs');
        $this->view->title = "Cadastrar Grupo";
        $form = new Sosti_Form_Labgrupo ();
        $form->removeElement('GRMA_ID_MARCA');
        $form->removeElement('GRUP_ID_GRUPO_MAT_SERV');
        $this->view->form = $form;
        //SALVA O NOVO GRUPO NO BANCO
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {

                $desc = trim(mb_strtoupper($data['GRUP_DS_GRUPO_MAT_SERV'], 'UTF-8'));
                $exist = $objModelGrupo->fetchRow(array('GRUP_DS_GRUPO_MAT_SERV=?' => $desc)); // checa se o registro já existe


                if ($exist) {
                    $gruponome = $desc;
                    $this->view->msg_error = "Cadastro não realizado, pois já existe um grupo com o mesmo nome.";
                    $form->populate($data);
                } else {//SALVA NOVO GRUPO
                    $datanovoGrupo['GRUP_CD_MAT_INCLUSAO'] = $userNs->matricula;
                    $datanovoGrupo['GRUP_DT_INCLUSAO'] = $sys->sysdate();
                    $datanovoGrupo['GRUP_DS_GRUPO_MAT_SERV'] = $desc;


                    $newData['GRUP_DS_GRUPO_MAT_SERV'] = $desc;

                    try {
                        $idInserted = $objModelGrupo->createRow($datanovoGrupo)->save();
                        $newData['idInserted'] = $idInserted;
                        // SALVA NA TABELA DE AUDITORIA O NOVO GRUPO ::1
                        self::salvaAuditoriagrupoDesc('I', $idInserted, $newData);
                        //::1
                    } catch (Exception $e) {
                        $this->_helper->flashMessenger(array('message' => "Grupo não foi cadastrado. Error:" . $e->getMessage(), 'status' => 'error'));
                        return $this->_helper->_redirector('index', 'labgrupo', 'sosti');
                    }


                    $gruponome = $data['GRUP_DS_GRUPO_MAT_SERV'];
                    $this->_helper->flashMessenger(array('message' => "Novo grupo <strong>" . strtoupper($desc) . "</strong> cadastrado com sucesso.", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labgrupo', 'sosti');
                }
            } else {
                $this->view->title = "Cadastrar Grupo";
                $form->removeElement('GRUP_ID_GRUPO_MAT_SERV');
                $this->view->form = $form;
            }
        }
    }

    /**
     * Altera o nome do grupo
     * 
     */
    public function editdescAction()
    {
        //MODELS
        $objModelGrupo = new Application_Model_DbTable_OcsTbGrupGrupo ();
        $form = new Sosti_Form_Labgrupo ();
        $form->removeElement('GRMA_ID_MARCA');
        $this->view->title = "Editar Descrição do Grupo";
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            if ($form->isValid($data)) {
                $data = $this->getRequest()->getPost();
                //VALIDAÇÃO DO GRUPO JÁ EXISTENTE ::1
                $id = $data['GRUP_ID_GRUPO_MAT_SERV'];
                $desc = mb_strtoupper($data['GRUP_DS_GRUPO_MAT_SERV'], 'UTF-8');
                $hasGrupo = $objModelGrupo->fetchRow(array('GRUP_DS_GRUPO_MAT_SERV=?' => $desc, 'GRUP_ID_GRUPO_MAT_SERV != ?' => $id));

                if ($hasGrupo) {
                    $gruponome = $data['GRUP_DS_GRUPO_MAT_SERV'];
                    $this->view->msg_error = "Alteração não realizada, pois já existe um grupo com o mesmo nome.";
                    $form->populate($data);
                } else {
                    $currentRow = $objModelGrupo->find($data ['GRUP_ID_GRUPO_MAT_SERV'])->current();
                    try {
                        $dataTosave['GRUP_DS_GRUPO_MAT_SERV'] = $desc;
                        $newData['GRUP_DS_GRUPO_MAT_SERV'] = $data['GRUP_DS_GRUPO_MAT_SERV'];
                        $oldData = $objModelGrupo->getgrupoInfo($data ['GRUP_ID_GRUPO_MAT_SERV']); //DADOS DA TABELA OCS_TB_GRUP_GRUPO_MAT_SERV
                        //SALVA NA TABELA DE AUDITORIA::1
                        self::salvaAuditoriagrupoDesc('A', $data ['GRUP_ID_GRUPO_MAT_SERV'], $newData, $oldData[0]);
                        $retorno = $currentRow->setFromArray($dataTosave)->save();
                        $gruponome = $data['GRUP_DS_GRUPO_MAT_SERV'];
                        $this->_helper->flashMessenger(array('message' => "O grupo <strong>$gruponome</strong> foi atualizado.", 'status' => 'success'));
                        return $this->_helper->_redirector('index', 'labgrupo', 'sosti');
                    } catch (Exception $e) {
                        $gruponome = $data['GRUP_DS_GRUPO_MAT_SERV'];
                        $this->_helper->flashMessenger(array('message' => "O grupo <strong>$gruponome</strong> não foi atualizado!.Erro:" . $e->getMessage(), 'status' => 'error'));
                        return $this->_helper->_redirector('index', 'labgrupo', 'sosti');
                    }
                }
            }
        } else {
            $grupoID = $this->_getParam('id');
            $data = $objModelGrupo->fetchRow(array("GRUP_ID_GRUPO_MAT_SERV=?" => $grupoID))->toArray();
            $form->populate($data);
        }
    }

    public function editassocAction()
    {
        $this->view->title = "Associar Grupo";
        $objAuditModel = new Application_Model_DbTable_OcsTbGrmaAuditoria ();
        $objModelGrupo = new Application_Model_DbTable_OcsTbGrupGrupo ();
        $objGramGrupo = new Application_Model_DbTable_OcsTbGramGrupoMarca();
        $sys = new Application_Model_DbTable_Dual ();
        $form = new Sosti_Form_Labgrupo ();

        //REMOVE O CAMPO DE DESCRIÇÃO  DO GRUPO.
        $form->removeElement('GRUP_DS_GRUPO_MAT_SERV');
        $form->removeElement('OBRIGATORIO');

        $grupoID = $this->_getParam('id');
        $data = $objModelGrupo->fetchRow(array("GRUP_ID_GRUPO_MAT_SERV=?" => $grupoID))->toArray();

        $this->view->grupoDescricao = $data['GRUP_DS_GRUPO_MAT_SERV'];
        $associacoes = $objModelGrupo->getgrupoAssociacoes($grupoID);
        $idGrmIdMarca = array();
        foreach ($associacoes as $r) {
            $idGrmIdMarca[] = $r["GRMA_ID_MARCA"];
        }
        $data['GRMA_ID_MARCA'] = $idGrmIdMarca;
        $form->getElement('GRUP_ID_GRUPO_MAT_SERV')->setValue($grupoID);
        $form->populate($data);

        $this->view->form = $form;
        //SALVA AS ALTERAÇÕES NO BANCO
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($data)) {
                $data = $this->getRequest()->getPost();

                //array com as marcas escolhidas
                $arrayMarcasCadastro = $data["GRMA_ID_MARCA"];

                //verificar as associacoes existentes
                $marcasAssociadas = $objGramGrupo->getMarcasAssociadas($data['GRUP_ID_GRUPO_MAT_SERV']);
                foreach ($marcasAssociadas as $m) {
                    $arrayMarcasAssociadas[] = $m['GRMA_ID_MARCA'];
                }

                //valores excluidos
                $arrayMarcasDesmarcadas = array_diff((array) $arrayMarcasAssociadas, (array) $arrayMarcasCadastro);

                //valores novos
                $arrayMarcasNovas = array_diff((array) $arrayMarcasCadastro, (array) $arrayMarcasAssociadas);

                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                try {
                    //verirficar se algum marcado ja está no banco como inativo
                    foreach ($arrayMarcasCadastro as $mc) {
                        $verifica = $objGramGrupo->verificaMarcaAssociada($data['GRUP_ID_GRUPO_MAT_SERV'], $mc);
                        //Se ja estivar no banco a associação, então seta S para ativar
                        if ($verifica) {
                            $rowExists = $objGramGrupo->find($data['GRUP_ID_GRUPO_MAT_SERV'], $mc)->current();
                            if ($rowExists) {
                                $dataAtualizar['GRMA_IC_ATIVO'] = 'S';
                                $rowExists->setFromArray($dataAtualizar)->save();
                            }
                        }
                    }

                    //setar N para os valores desmarcados
                    if (count($arrayMarcasDesmarcadas) > 0) {
                        foreach ($arrayMarcasDesmarcadas as $md) {
                            $verifica = $objGramGrupo->verificaMarcaAssociada($data['GRUP_ID_GRUPO_MAT_SERV'], $md);
                            //Se ja estivar no banco a associação, então seta S para ativar
                            if ($verifica) {
                                $rowExists = $objGramGrupo->find($data['GRUP_ID_GRUPO_MAT_SERV'], $md)->current();
                                if ($rowExists) {
                                    $dataAtualizar['GRMA_IC_ATIVO'] = 'N';
                                    $rowExists->setFromArray($dataAtualizar)->save();
                                }
                            }
                        }
                    }

                    //inserir um novo registro como relacionamento
                    if (count($arrayMarcasNovas) > 0) {
                        foreach ($arrayMarcasNovas as $mn) {
                            $dataSave["GRMA_ID_GRUPO_MAT_SERV"] = $data['GRUP_ID_GRUPO_MAT_SERV'];
                            $dataSave["GRMA_ID_MARCA"] = $mn;
                            $dataSave["GRMA_IC_ATIVO"] = 'S';
                            $objGramGrupo->createRow($dataSave)->save();
                        }
                    }

                    $db->commit();
                    $this->_helper->flashMessenger(array('message' => "O grupo foi atualizado.", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labgrupo', 'sosti');
                } catch (Exception $exc) {
                    $db->rollBack();
                    $this->_helper->flashMessenger(array('message' => "O grupo não foi atualizado. Erro:" . $exc->getMessage(), 'status' => 'error'));
                    return $this->_helper->_redirector('index', 'labgrupo', 'sosti');
                }
            }
        } else {
            $this->view->title = "Associar Grupo";
            $this->view->form = $form;
        }
    }

    /**
     * Método para associar um grupo as marcas.
     * 
     */
    public function associargrupoAction()
    {

        $form = new Sosti_Form_associargrupomarca ();
        $objGramMarca = new Application_Model_DbTable_OcsTbGramGrupoMarca ();
        $objMarca = new Application_Model_DbTable_OcsTbMarcMarca();
        $this->view->form = $form;
        $this->view->title = "Associar Grupo a Marca";

        $dataMarca = $objMarca->getMarca();
        $Marcas = count($dataMarca);

        //foreach ($dataMarca as $i => $value) 
        //{
        //$checkBoxes = new Zend_Form_Element_MultiCheckbox('checkBox_',$dataMarca );
        //$checkBoxes[$i]->setDescription($value['MARC_DS_MARCA'])
        //->setDecorators(array( 'ViewHelper', 'Errors', 'Label'))
        //->removeDecorator('HtmlTag', array('tag'=>'dt'))
        //->addDecorator('HtmlTag', array('tag'=>'div','style'=> 'clear:both'))
        //->setAttrib('style', 'float:left');
        //}

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {

                foreach ($data['GRMA_ID_MARCA'] as $value) {
                    $associacaoExiste = $objGramMarca->fetchRow(array('GRMA_ID_GRUPO_MAT_SERV=?' => $data['GRMA_ID_GRUPO_MAT_SERV'], 'GRMA_ID_MARCA=?' => $value));
                    $datatoSave['GRMA_ID_GRUPO_MAT_SERV'] = $data['GRMA_ID_GRUPO_MAT_SERV'];
                    $datatoSave['GRMA_ID_MARCA'] = $value;
                    if (!$associacaoExiste) {
                        $objGramMarca->createRow($datatoSave)->save();
                    }
                }
                $this->_helper->flashMessenger(array('message' => "O grupo foi associado!", 'status' => 'success'));
                return $this->_helper->_redirector('index', 'labgrupo', 'sosti');
            } else {
                $this->view->form = $form;
                $this->view->title = "Associar Grupo a Marca";
            }
        }
    }

    public function ajaxcadastrogrupoAction()
    {
        $grupo = $this->_getParam('term');
        $objGrupo = new Application_Model_DbTable_OcsTbGrupGrupo ();
        $rows = $objGrupo->autoCompleteGrupo(strtoupper($grupo));

        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows [$i] = array_change_key_case($rows [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($rows);
    }

    /**
     * Método de auditoria para a tabela OCS_TB_GRMA_AUDITORIA
     * 
     * @param array $data
     * @param string $icOperacao
     * @param int $idInserted
     * @param int $marcaID
     */
    public static function salvaAuditoria($data, $icOperacao, $marcaID = null, $dataOld, $icAtivo)
    {

        $objAuditModel = new Application_Model_DbTable_OcsTbGrmaAuditoria ();
        $objGramGrupo = new Application_Model_DbTable_OcsTbGramGrupoMarca();
        $objgrupModel = new Application_Model_DbTable_OcsTbGrupGrupo ();
        if ($icOperacao === 'I') {

            $datatoSave['GRMA_TS_OPERACAO'] = App_Util::getTimeStamp_Audit();
            $datatoSave['GRMA_IC_OPERACAO'] = $icOperacao;
            $datatoSave['GRMA_CD_MATRICULA_OPERACAO'] = App_Util::getMatricula_Audit();
            $datatoSave['GRMA_CD_MAQUINA_OPERACAO'] = App_Util::getIpMaquina_Audit();
            $datatoSave['GRMA_CD_USUARIO_SO'] = App_Util::getUsuarioSistemaOperacional_Audit();
            $datatoSave['OLD_GRMA_ID_GRUPO_MAT_SERV'] = new Zend_Db_Expr("NULL");
            $datatoSave['NEW_GRMA_ID_GRUPO_MAT_SERV'] = $data['idInserted'];
            $datatoSave['OLD_GRMA_ID_MARCA'] = new Zend_Db_Expr("NULL");
            $datatoSave['NEW_GRMA_ID_MARCA'] = $marcaID;
            $datatoSave['OLD_GRMA_IC_ATIVO'] = new Zend_Db_Expr("NULL");
            $datatoSave['NEW_GRMA_IC_ATIVO'] = 'S';
            $datatoSave['OLD_DS_GRUPO_MAT_SERV'] = new Zend_Db_Expr("NULL");
            $datatoSave['NEW_DS_GRUPO_MAT_SERV'] = $data['GRUP_DS_GRUPO_MAT_SERV'];

            $objAuditModel->createRow($datatoSave)->save();
        } else if ($icOperacao === 'A') {

            $oldData_raw = $objGramGrupo->find($data['GRUP_ID_GRUPO_MAT_SERV'], $marcaID)->current(); // OLD DATA TABELA DE ASSOCIAÇÃO
            $oldData2_raw = $objgrupModel->find($data['GRUP_ID_GRUPO_MAT_SERV'])->current();
            $oldData_raw = array_merge($oldData_raw->toArray(), $oldData2_raw->toArray());
            $datatoSave['GRMA_TS_OPERACAO'] = App_Util::getTimeStamp_Audit();
            $datatoSave['GRMA_IC_OPERACAO'] = $icOperacao;
            $datatoSave['GRMA_CD_MATRICULA_OPERACAO'] = App_Util::getMatricula_Audit();
            $datatoSave['GRMA_CD_MAQUINA_OPERACAO'] = App_Util::getIpMaquina_Audit();
            $datatoSave['GRMA_CD_USUARIO_SO'] = App_Util::getUsuarioSistemaOperacional_Audit();
            $datatoSave['OLD_GRMA_ID_GRUPO_MAT_SERV'] = $data['GRUP_ID_GRUPO_MAT_SERV'];
            $datatoSave['NEW_GRMA_ID_GRUPO_MAT_SERV'] = $data['GRUP_ID_GRUPO_MAT_SERV'];
            $datatoSave['OLD_GRMA_ID_MARCA'] = $marcaID;
            $datatoSave['NEW_GRMA_ID_MARCA'] = $marcaID;
            $datatoSave['OLD_GRMA_IC_ATIVO'] = $oldData_raw['GRMA_IC_ATIVO'];
            $datatoSave['NEW_GRMA_IC_ATIVO'] = "$icAtivo";
            $datatoSave['OLD_DS_GRUPO_MAT_SERV'] = $oldData2_raw['GRUP_DS_GRUPO_MAT_SERV'];
            $datatoSave['NEW_DS_GRUPO_MAT_SERV'] = $data['GRUP_DS_GRUPO_MAT_SERV'];

            $usuario = App_Util::getMatricula_Audit();

            $objAuditModel->createRow($datatoSave)->save();
        }
    }

    public function atualizaauditoriatabelaAction()
    {

        //MODELS OBJETOS
        $objAuditModel = new Application_Model_DbTable_OcsTbGrmaAuditoria ();
        $objGramGrupo = new Application_Model_DbTable_OcsTbGramGrupoMarca();
        //POST VALUES
        $grma_id_marca = $this->_getParam('checkboxValor'); // VALOR DO CHECKBOX
        $descricaoGrupo = $this->_getParam('Dsgrupo');
        $acao = $this->_getParam('acao');
        $grupoId = $this->_getParam('grupoId');
        $ativo = $this->_getParam('Ativo');


        //REORNA VALORES ANTIGOS::0
        $dadosAntigos = $objAuditModel->getLastAuditRow($grupoId, $grma_id_marca);
        //::0
        //VALORES ESTÁTICOS::1

        $datatoSave['GRMA_TS_OPERACAO'] = App_Util::getTimeStamp_Audit();
        $datatoSave['GRMA_IC_OPERACAO'] = $acao; //AÇÃO DO USUÁRIO. A=ATUALIZAR,I= INCLUIR.
        $datatoSave['GRMA_CD_MATRICULA_OPERACAO'] = App_Util::getMatricula_Audit();
        $datatoSave['GRMA_CD_MAQUINA_OPERACAO'] = App_Util::getIpMaquina_Audit();
        $datatoSave['GRMA_CD_USUARIO_SO'] = App_Util::getUsuarioSistemaOperacional_Audit();
        //::1
        //OLD VALUES::2
        $datatoSave['OLD_GRMA_ID_MARCA'] = $dadosAntigos[0]['NEW_GRMA_ID_MARCA'];
        $datatoSave['OLD_GRMA_IC_ATIVO'] = $dadosAntigos[0]['NEW_GRMA_IC_ATIVO'];
        $datatoSave['OLD_GRMA_ID_GRUPO_MAT_SERV'] = $dadosAntigos[0]['NEW_GRMA_ID_GRUPO_MAT_SERV'];
        //::2
        //NOVOS VALORES::3
        $datatoSave['NEW_GRMA_ID_MARCA'] = $grma_id_marca; // ID/VALOR DO CHECKBOX QUE FOI ATUALIZADO
        $datatoSave['NEW_GRMA_IC_ATIVO'] = $ativo; // ATIVO OU INATIVO. S,N
        $datatoSave['NEW_GRMA_ID_GRUPO_MAT_SERV'] = $grupoId; // ID DO GRUPO DE MATERIAL DE SERVIÇO
        //::3
        //DESATIVA/ATIVA  A ASSOCIACAO DA MARCA COM O GRUPO::4
        $rowExists = $objGramGrupo->find($grupoId, $grma_id_marca)->current();
        if ($rowExists) {
            $dataAtualizar['GRMA_IC_ATIVO'] = $ativo;
            $rowExists->setFromArray($dataAtualizar)->save();
        } else {
            $dataAtualizar['GRMA_ID_GRUPO_MAT_SERV'] = $grupoId;
            $dataAtualizar['GRMA_ID_MARCA'] = $grma_id_marca;
            $dataAtualizar['GRMS_IC_ATIVO'] = 'S';
            $objGramGrupo->createRow($dataAtualizar)->save();
        }


        //::4

        $result = $objAuditModel->createRow($datatoSave)->save();
        if ($result)
            $this->view->result = '1';
        else {
            $this->view->result = '0';
        }
    }

    /**
     * Insere ações na tabela de auditoria ao editar ou adicionar a descrição um grupo
     * 
     * @param string $action A,I
     * @param array $newdata
     * @param int $rowId
     */
    public static function salvaAuditoriagrupoDesc($icOperacao, $rowdId = null, $newData, $oldData = null)
    {

        $objGrupMatSerAuditoria = new Application_Model_DbTable_OcsTbGrupMatSerAuditoria();
        $objGrupGrupoMatServ = new Application_Model_DbTable_OcsTbGrupGrupo ();

        if ($icOperacao === 'I') {//SE O USUÁRIO ESTIVER INSERINDO UM NOVO GRUPO ::1
            $datatoSave['GRUP_TS_OPERACAO'] = App_Util::getTimeStamp_Audit();
            $datatoSave['GRUP_IC_OPERACAO'] = $icOperacao;
            $datatoSave['GRUP_CD_MATRICULA_OPERACAO'] = App_Util::getMatricula_Audit();
            $datatoSave['GRUP_CD_MAQUINA_OPERACAO'] = App_Util::getIpMaquina_Audit();
            $datatoSave['GRUP_CD_USUARIO_SO'] = App_Util::getUsuarioSistemaOperacional_Audit();
            $datatoSave['OLD_GRUP_ID_GRUPO_MAT_SERV'] = new Zend_Db_Expr("NULL");
            $datatoSave['NEW_GRUP_ID_GRUPO_MAT_SERV'] = $rowdId;
            $datatoSave['OLD_GRUP_DS_GRUPO_MAT_SERV'] = new Zend_Db_Expr("NULL");
            $datatoSave['NEW_GRUP_DS_GRUPO_MAT_SERV'] = $newData['GRUP_DS_GRUPO_MAT_SERV'];
            $datatoSave['OLD_GRUP_DT_INCLUSAO'] = new Zend_Db_Expr("NULL");
            $datatoSave['OLD_GRUP_CD_MAT_INCLUSAO'] = new Zend_Db_Expr("NULL");
            $datatoSave['NEW_GRUP_CD_MAT_INCLUSAO'] = App_Util::getMatricula_Audit();
            $datatoSave['NEW_GRUP_DT_INCLUSAO'] = App_Util::getTimeStamp_Audit();
        } else if ($icOperacao === 'A') {

            $currentrow = $objGrupMatSerAuditoria->getLastAuditRow($rowdId); //DADOS DA TABELA DE AUDITORIA
            $datatoSave['GRUP_TS_OPERACAO'] = App_Util::getTimeStamp_Audit();
            $datatoSave['GRUP_IC_OPERACAO'] = $icOperacao;
            $datatoSave['GRUP_CD_MATRICULA_OPERACAO'] = App_Util::getMatricula_Audit();
            $datatoSave['GRUP_CD_MAQUINA_OPERACAO'] = App_Util::getIpMaquina_Audit();
            $datatoSave['GRUP_CD_USUARIO_SO'] = App_Util::getUsuarioSistemaOperacional_Audit();
            $datatoSave['OLD_GRUP_ID_GRUPO_MAT_SERV'] = $oldData['GRUP_ID_GRUPO_MAT_SERV'];
            $datatoSave['NEW_GRUP_ID_GRUPO_MAT_SERV'] = $oldData['GRUP_ID_GRUPO_MAT_SERV'];
            $datatoSave['OLD_GRUP_DS_GRUPO_MAT_SERV'] = $oldData['GRUP_DS_GRUPO_MAT_SERV'];
            $datatoSave['NEW_GRUP_DS_GRUPO_MAT_SERV'] = $newData['GRUP_DS_GRUPO_MAT_SERV'];
            $datatoSave['OLD_GRUP_DT_INCLUSAO'] = new Zend_Db_Expr("TO_DATE('" . $oldData['GRUP_DT_INCLUSAO'] . "','dd/mm/yyyy HH24:MI:SS')");
            $datatoSave['OLD_GRUP_CD_MAT_INCLUSAO'] = $oldData['GRUP_CD_MAT_INCLUSAO'];
            $datatoSave['NEW_GRUP_CD_MAT_INCLUSAO'] = App_Util::getMatricula_Audit();
            $datatoSave['NEW_GRUP_DT_INCLUSAO'] = App_Util::getTimeStamp_Audit();
        }
        //zend_debug::dump($datatoSave,'$datatoSave');exit;
        $objGrupMatSerAuditoria->createRow($datatoSave)->save();
    }

    public function redirectAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->_helper->flashMessenger(array('message' => "Marcas associadas com sucesso!", 'status' => 'success'));
        return $this->_helper->_redirector('index', 'labgrupo', 'sosti');
    }

}
