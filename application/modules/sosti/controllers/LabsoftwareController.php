<?php

/**
 * labsoftware
 * 
 * @author
 * @version 
 */
class Sosti_LabSoftwareController extends Zend_Controller_Action
{

    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch() {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    /**
     * The default action - show the home page
     */
    public function init() {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        //nothing here
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }

    /**
     * 
     * Executa Ação index
     * view:Software
     */
    public function indexAction() {

        $form = new Sosti_Form_LicencaSoftw ();

        //Busca os dados a serem exibidos
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itemCountPerPage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
        $order_column = $this->_getParam('ordem', 'LSFW_ID_TP_SOFTWARE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        $dados = new Application_Model_DbTable_SosTbLsfwSoftware ();
        $rows = $dados->getSoftwares($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemCountPerPage);

        $this->view->title = "Software";
        $this->view->form = $form;
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    /**
     * 
     * cria combo baseada no tipo da marca
     */
    public function ajaxnometipomodeloAction($idMarca = null) {
        $idMarca = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $tipomodelo = new Application_Model_DbTable_OcsTbModeModelo();
        $server = new Zend_Json_Server_Request_Http ();
        $data = Zend_Json::decode($server->getRawJson());
        $rows_array_raw = $tipomodelo->getmodelosporMarca($idMarca);
        $this->view->modelos = $rows_array_raw;
    }

    /**
     * 
     * Desvincula um equipamento a uma licennça
     */
    public function ajaxdevincularAction() {
        $id = $this->_getParam('id', '');
        $software = $this->_getParam('software', '');
        $SosTbLfswFichaSoftware = new Application_Model_DbTable_SosTbLfswFichaSoftware();
        $SosTbLfswFichaSoftware->devincularLicenca($id, $software);
    }

    /**
     * 
     * Executa ação Add para cadastrar novo software
     */
    public function addAction() {

        $this->view->title = 'Cadastrar Software';
        $table = new Application_Model_DbTable_SosTbLsfwSoftware ();
        $ocsModelo = new Application_Model_DbTable_OcsTbModeModelo();
        $licencaSoftwareObj = new Application_Model_DbTable_SosTbLiswLicencaSoftware ();
        $sys = new Application_Model_DbTable_Dual ();
        $form = new Sosti_Form_LabCadastroSoftware ();
        $lsfw_id_modelo = $form->getElement('LSFW_ID_MODELO');
        $rowmodelos = $ocsModelo->getmodelosporMarca($data['LSFW_ID_MARCA']);
        $form->removeElement('LSFW_ID_SOFTWARE');
        $this->view->form = $form;
        $lsfw_id_modelo->addMultiOptions(array('' => '::Selecione::'));
        $userNs = new Zend_Session_Namespace('userNs');

        foreach ($rowmodelos as $Modelo) {
            $lsfw_id_modelo->addMultiOptions(array($Modelo["CO_MODELO"] => $Modelo["DE_MODELO"]));
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $lsfw_id_modelo = $form->getElement('LSFW_ID_MODELO');
            $ocsModelo = new Application_Model_DbTable_OcsTbModeModelo();
            $rowmodelos = $ocsModelo->getmodelosporMarca($data['LSFW_ID_MARCA']);
            $lsfw_id_modelo->addMultiOptions(array('' => 'Selecione o Modelo::'));

            foreach ($rowmodelos as $Modelo) {
                $lsfw_id_modelo->addMultiOptions(array($Modelo["MODE_ID_MODELO"] => $Modelo["MODE_DS_MODELO"]));
            }

            if ($form->isValid($data)) {

                $data ['LSFW_IC_APROVACAO_INSTALACAO'] = ($data ['LSFW_IC_APROVACAO_INSTALACAO'] == 1) ? 'S' : 'N';
                $data ['LSFW_IC_PERPETUIDADE_LICENCA'] = ($data ['LSFW_IC_PERPETUIDADE_LICENCA'] == 1) ? 'S' : 'N';
                $rowmodelos = $ocsModelo->getmodelosporMarca($data['LSFW_ID_MARCA']);
                $lsfw_id_modelo->addMultiOptions(array(':: Selecione :: ' => ''));
                $lsfw_id_modelo = $form->getElement('LSFW_ID_MODELO');
                foreach ($rowmodelos as $Modelo) {
                    $lsfw_id_modelo->addMultiOptions(array($Modelo["MODE_ID_MODELO"] => $Modelo["MODE_DS_MODELO"]));
                }
                unset($data ['LSFW_ID_SOFTWARE']);
                unset($data ['Salvar']);
                unset($data ['acao']);

                //verifica se ja existe o software
                $where = "LSFW_DS_SOFTWARE = '" . $data['LSFW_DS_SOFTWARE'] . "' ";
                $existe = $table->fetchAll($where);
                if (count($existe) != 0) {
                    $this->view->msg_error = "O software não foi cadastrado, pois já existe outro com o mesmo nome na base de dados.";
                    $form->populate($data);
                } else {
                    $data['LSFW_DT_AQUISICAO'] = new Zend_Db_Expr("TO_DATE('" . $data['LSFW_DT_AQUISICAO'] . "','dd/mm/yyyy')");
                    $data['LSFW_DT_VALIDADE_LICENCA'] = new Zend_Db_Expr("TO_DATE('" . $data['LSFW_DT_VALIDADE_LICENCA'] . "','dd/mm/yyyy')");
                    $message = $data ['LSFW_DS_SOFTWARE'];
                    $data['LSFW_DS_SOFTWARE'] = mb_strtoupper($data['LSFW_DS_SOFTWARE'], 'UTF-8');

                    try {
                        $idInserted = $table->createRow($data)->save();
                        //Auditoria
                        $SosTbLsfwAuditoria = new Application_Model_DbTable_SosTbLsfwAuditoria();
                        $dataAuditoria['LSFW_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                        $dataAuditoria['LSFW_IC_OPERACAO'] = 'I';
                        $dataAuditoria['LSFW_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                        $dataAuditoria['LSFW_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                        $dataAuditoria['LSFW_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                        $dataAuditoria['OLD_LSFW_ID_SOFTWARE'] = NULL;
                        $dataAuditoria['NEW_LSFW_ID_SOFTWARE'] = $idInserted;
                        $dataAuditoria['OLD_LSFW_DS_SOFTWARE'] = NULL;
                        $dataAuditoria['NEW_LSFW_DS_SOFTWARE'] = $data['LSFW_DS_SOFTWARE'];
                        $dataAuditoria['OLD_LSFW_ID_TP_SOFTWARE'] = NULL;
                        $dataAuditoria['NEW_LSFW_ID_TP_SOFTWARE'] = $data['LSFW_ID_TP_SOFTWARE'];
                        $dataAuditoria['OLD_LSFW_ID_MARCA'] = NULL;
                        $dataAuditoria['NEW_LSFW_ID_MARCA'] = $data['LSFW_ID_MARCA'];
                        $dataAuditoria['OLD_LSFW_ID_MODELO'] = NULL;
                        $dataAuditoria['NEW_LSFW_ID_MODELO'] = $data['LSFW_ID_MODELO'];
                        $dataAuditoria['OLD_LSFW_DT_AQUISICAO'] = NULL;
                        $dataAuditoria['NEW_LSFW_DT_AQUISICAO'] = $data['LSFW_DT_AQUISICAO'];
                        $dataAuditoria['OLD_LSFW_DT_VALIDADE_LICENCA'] = NULL;
                        $dataAuditoria['NEW_LSFW_DT_VALIDADE_LICENCA'] = $data['LSFW_DT_VALIDADE_LICENCA'];
                        $dataAuditoria['OLD_LSFW_IC_SOFTWARE_LIVRE'] = NULL;
                        $dataAuditoria['NEW_LSFW_IC_SOFTWARE_LIVRE'] = $data['LSFW_IC_SOFTWARE_LIVRE'];
                        $dataAuditoria['OLD_LSFW_NR_PROCESSO_COMPRA'] = NULL;
                        $dataAuditoria['NEW_LSFW_NR_PROCESSO_COMPRA'] = $data['LSFW_NR_PROCESSO_COMPRA'];
                        $dataAuditoria['OLD_LSFW_CD_TIPO_DOC_COMPRA'] = NULL;
                        $dataAuditoria['NEW_LSFW_CD_TIPO_DOC_COMPRA'] = $data['LSFW_CD_TIPO_DOC_COMPRA'];
                        $dataAuditoria['OLD_LSFW_NR_ADIT_CONTRATO'] = NULL;
                        $dataAuditoria['NEW_LSFW_NR_ADIT_CONTRATO'] = $data['LSFW_NR_ADITAMENTO_CONTRATO'];
                        $dataAuditoria['OLD_LSFW_NR_CONTRATO'] = NULL;
                        $dataAuditoria['NEW_LSFW_NR_CONTRATO'] = $data['LSFW_NR_ADITAMENTO_CONTRATO'];
                        $dataAuditoria['OLD_LSFW_NR_PROCESSO_CONTRATO'] = NULL;
                        $dataAuditoria['NEW_LSFW_NR_PROCESSO_CONTRATO'] = $data['LSFW_NR_PROCESSO_CONTRATO'];
                        $dataAuditoria['OLD_LSFW_NR_TERMO'] = NULL;
                        $dataAuditoria['NEW_LSFW_NR_TERMO'] = $data['LSFW_NR_TERMO'];
                        $dataAuditoria['OLD_LSFW_AA_TERMO'] = NULL;
                        $dataAuditoria['NEW_LSFW_AA_TERMO'] = $data['LSFW_AA_TERMO'];
                        $dataAuditoria['OLD_LSFW_CD_TIPO_TERMO'] = NULL;
                        $dataAuditoria['NEW_LSFW_CD_TIPO_TERMO'] = $data['LSFW_CD_TIPO_TERMO'];
                        $dataAuditoria['OLD_LSFW_NR_TOMBO'] = NULL;
                        $dataAuditoria['NEW_LSFW_NR_TOMBO'] = $data['LSFW_NR_TOMBO'];
                        $dataAuditoria['OLD_LSFW_SG_TOMBO'] = NULL;
                        $dataAuditoria['NEW_LSFW_SG_TOMBO'] = $data['LSFW_SG_TOMBO'];
                        $dataAuditoria['OLD_LSFW_QT_ADQUIRIDA'] = NULL;
                        $dataAuditoria['NEW_LSFW_QT_ADQUIRIDA'] = $data['LSFW_QT_ADQUIRIDA'];
                        $dataAuditoria['OLD_LSFW_NR_DOC_ORIGEM'] = NULL;
                        $dataAuditoria['NEW_LSFW_NR_DOC_ORIGEM'] = $data['LSFW_NR_DOC_ORIGEM'];
                        $dataAuditoria['OLD_LSFW_NR_NOTA_FISCAL'] = NULL;
                        $dataAuditoria['NEW_LSFW_NR_NOTA_FISCAL'] = $data['LSFW_NR_NOTA_FISCAL'];
                        $rowAuditoria = $SosTbLsfwAuditoria->createRow($dataAuditoria);
                        $rowAuditoria->save();

                        $this->_helper->flashMessenger(array('message' => "O Software: $message foi cadastrado!", 'status' => 'success'));
                    } catch (Exception $e) {
                        $this->_helper->flashMessenger(array('message' => "Não foi possível cadastrar o Software. Verifique se o número dos documentos, processos ou contratos informados estão corretos.", 'status' => 'error'));
                    }
                    return $this->_helper->_redirector('index', 'labsoftware', 'sosti');
                }
            } else {

                $this->view->form = $form;
            }
        }
    }

    /**
     * Cadastra as licenças de softwares
     */
    public function entradalicencaAction() {

        $this->view->title = 'Cadastrar Licença de Software';
        $table = new Application_Model_DbTable_SosTbLiswLicencaSoftware();
        $ocsModelo = new Application_Model_DbTable_OcsTbModeModelo();
        $tbSoftware = new Application_Model_DbTable_SosTbLsfwSoftware();
        $sys = new Application_Model_DbTable_Dual ();
        $form = new Sosti_Form_LabEntradaLicencaSoftware ();
        $this->view->form = $form;
        $userNs = new Zend_Session_Namespace('userNs');

        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getPost();

            if ($data['LSFW_ID_MARCA'] != "") {
                //Preenchendo os campos
                $lsfw_id_modelo = $form->getElement('LSFW_ID_MODELO');
                $rowmodelos = $ocsModelo->getmodelosporMarca($data['LSFW_ID_MARCA']);
                $lsfw_id_modelo->addMultiOptions(array('' => '::Selecione o Modelo::'));
                foreach ($rowmodelos as $Modelo) {
                    $lsfw_id_modelo->addMultiOptions(array($Modelo["MODE_ID_MODELO"] => $Modelo["MODE_DS_MODELO"]));
                }
            }
            if ($data['LSFW_ID_MODELO'] != "") {
                $lsfw_ds_software = $form->getElement('LISW_ID_SOFTWARE');
                $rowSoftwares = $tbSoftware->getSoftware($data['LSFW_ID_MODELO']);
                foreach ($rowSoftwares as $s) {
                    $lsfw_ds_software->addMultiOptions(array($s["LSFW_ID_SOFTWARE"] => $s["LSFW_DS_SOFTWARE"]));
                }
            }

            if ($form->isValid($data)) {

                unset($data['LSFW_ID_MARCA']);
                unset($data['LSFW_ID_MODELO']);
                unset($data['Salvar']);
                unset($data['OBRIGATORIO']);
                $data['LISW_DT_AQUISICAO'] = $sys->sysdate();

                try {

                    $table->createRow($data)->save();

                    //Auditoria
                    $SosTbLiswAuditoria = new Application_Model_DbTable_SosTbLiswAuditoria();
                    $dataAuditoria['LISW_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                    $dataAuditoria['LISW_IC_OPERACAO'] = 'I';
                    $dataAuditoria['LISW_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                    $dataAuditoria['LISW_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                    $dataAuditoria['LISW_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                    $dataAuditoria['OLD_LISW_ID_SOFTWARE'] = NULL;
                    $dataAuditoria['NEW_LISW_ID_SOFTWARE'] = $data['LISW_ID_SOFTWARE'];
                    $dataAuditoria['OLD_LISW_DT_AQUISICAO'] = NULL;
                    $dataAuditoria['NEW_LISW_DT_AQUISICAO'] = $data['LISW_DT_AQUISICAO'];
                    $dataAuditoria['OLD_LISW_QT_LICENCA'] = NULL;
                    $dataAuditoria['NEW_LISW_QT_LICENCA'] = $data['LISW_QT_LICENCA'];
                    $rowAuditoria = $SosTbLiswAuditoria->createRow($dataAuditoria);
                    $rowAuditoria->save();

                    $this->_helper->flashMessenger(array('message' => "A licença foi cadastrada!", 'status' => 'success'));
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => "Não foi possível cadastrar a licença do Software." . $e->getMessage(), 'status' => 'error'));
                }
                return $this->_helper->_redirector('index', 'labsoftware', 'sosti');
            } else {
                $this->view->form = $form;
            }
        }
    }

    /**
     * 
     * Método/Ação  para atualizar software 
     */
    public function editarAction() {

        $this->view->title = 'Editar Software';
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $table = new Application_Model_DbTable_SosTbLsfwSoftware ();
        $licencaSoftwareObj = new Application_Model_DbTable_SosTbLiswLicencaSoftware ();
        $Modelo = new Application_Model_DbTable_OcsTbModeModelo();
        $userNs = new Zend_Session_Namespace('userNs');

        $form = new Sosti_Form_LabCadastroSoftware ();
        $form->removeElement('LISW_QT_LICENCA');
        $form->LSFW_ID_SOFTWARE->setAttrib('readonly', 'readonly');
        $form->LSFW_ID_SOFTWARE->setAttrib('style', 'display:none;');
        $form->LSFW_ID_SOFTWARE->removeDecorator('Label');
        $form->LSFW_ID_SOFTWARE->setAttrib('class', 'campo-leitura');
        $this->view->form = $form;
        //popula o form baseado na id da ROW
        if ($id) {
            $row = $table->fetchRow(array('LSFW_ID_SOFTWARE = ?' => $id));
            $data['LISW_QT_LICENCA'] = $licencadata['LISW_QT_LICENCA'];
            if ($row) {
                $sfw_id_modelo = $form->getElement('LSFW_ID_MODELO');
                $data = $row->toArray();
                $data['LISW_QT_LICENCA'] = $licencadata['LISW_QT_LICENCA'];
                $marcaID = $Modelo->fetchRow(array('MODE_ID_MODELO=?' => $row['LSFW_ID_MODELO']));
                $rowmodelos = $Modelo->getmodelosporMarca($marcaID['MODE_ID_MARCA']);
                $sfw_id_modelo->addMultiOptions(array(' ' => '::SELECIONE::'));
                foreach ($rowmodelos as $Modelo) {
                    $sfw_id_modelo->addMultiOptions(array($Modelo["MODE_ID_MODELO"] => $Modelo["MODE_DS_MODELO"]));
                }
                $data['LSFW_ID_MARCA'] = $marcaID['MODE_ID_MARCA'];
                $form->populate($data);
            }
            $tombos = $table->getSoftwareTombo($id);
            $this->view->tombos = $tombos;
        }
        if ($this->getRequest()->isPost()) {
            $Modelo = new Application_Model_DbTable_OcsTbModeModelo();
            $data = $this->getRequest()->getPost();

            $rowmodelos = $Modelo->getmodelosporMarca($data['LSFW_ID_MARCA']);
            $sfw_id_modelo->addMultiOptions(array('' => '::SELECIONE::'));
            foreach ($rowmodelos as $Modelo) {
                $sfw_id_modelo->addMultiOptions(array($Modelo["MODE_ID_MODELO"] => $Modelo["MODE_DS_MODELO"]));
            }
            if($data['LSFW_IC_PERPETUIDADE_LICENCA'] == 'S')
                $form->getElement('LSFW_DT_VALIDADE_LICENCA')->setRequired(false);
            else
                $form->getElement('LSFW_DT_VALIDADE_LICENCA')->setRequired(true);

            if ($form->isValid($data)) {
                $data['LSFW_DS_SOFTWARE'] = mb_strtoupper($data['LSFW_DS_SOFTWARE'], 'UTF-8');
                //verifica se ja existe o software
                $where = "LSFW_DS_SOFTWARE = '" . $data['LSFW_DS_SOFTWARE'] . "' AND LSFW_ID_SOFTWARE != " . $data['LSFW_ID_SOFTWARE'];
                $existe = $table->fetchAll($where);
                if (count($existe) != 0) {
                    $this->view->msg_error = "O software não foi atualizado, pois já existe outro com o mesmo nome na base de dados.";
                    $form->populate($data);
                } else {

                    $message = $data['LSFW_DS_SOFTWARE'];
                    $row = $table->find($data['LSFW_ID_SOFTWARE'])->current();
                    $dataAnt = $row->toArray();
//                    $data["LSFW_DT_VALIDADE_LICENCA"] = new Zend_Db_Expr("to_date('".$data["LSFW_DT_VALIDADE_LICENCA"]."', YYYY-MM-dd)");
                    if($data['LSFW_IC_PERPETUIDADE_LICENCA'] == "S")
                        $data['LSFW_DT_VALIDADE_LICENCA'] = NULL;
                    try {
                        $row->setFromArray($data)->save();

                        //Auditoria
                        $SosTbLsfwAuditoria = new Application_Model_DbTable_SosTbLsfwAuditoria();
                        $dataAuditoria['LSFW_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                        $dataAuditoria['LSFW_IC_OPERACAO'] = 'A';
                        $dataAuditoria['LSFW_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                        $dataAuditoria['LSFW_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                        $dataAuditoria['LSFW_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                        $dataAuditoria['OLD_LSFW_ID_SOFTWARE'] = $dataAnt['LSFW_ID_SOFTWARE'];
                        $dataAuditoria['NEW_LSFW_ID_SOFTWARE'] = $dataAnt['LSFW_ID_SOFTWARE'];
                        $dataAuditoria['OLD_LSFW_DS_SOFTWARE'] = $dataAnt['LSFW_DS_SOFTWARE'];
                        $dataAuditoria['NEW_LSFW_DS_SOFTWARE'] = $data['LSFW_DS_SOFTWARE'];
                        $dataAuditoria['OLD_LSFW_ID_TP_SOFTWARE'] = $dataAnt['LSFW_ID_TP_SOFTWARE'];
                        $dataAuditoria['NEW_LSFW_ID_TP_SOFTWARE'] = $data['LSFW_ID_TP_SOFTWARE'];
                        $dataAuditoria['OLD_LSFW_ID_MARCA'] = $dataAnt['LSFW_ID_MARCA'];
                        $dataAuditoria['NEW_LSFW_ID_MARCA'] = $data['LSFW_ID_MARCA'];
                        $dataAuditoria['OLD_LSFW_ID_MODELO'] = $dataAnt['LSFW_ID_MODELO'];
                        $dataAuditoria['NEW_LSFW_ID_MODELO'] = $data['LSFW_ID_MODELO'];
                        $dataAuditoria['OLD_LSFW_DT_AQUISICAO'] = $dataAnt['LSFW_DT_AQUISICAO'];
                        $dataAuditoria['NEW_LSFW_DT_AQUISICAO'] = $data['LSFW_DT_AQUISICAO'];
                        $dataAuditoria['OLD_LSFW_DT_VALIDADE_LICENCA'] = $dataAnt['LSFW_DT_VALIDADE_LICENCA'];
                        $dataAuditoria['NEW_LSFW_DT_VALIDADE_LICENCA'] = $data['LSFW_DT_VALIDADE_LICENCA'];
                        $dataAuditoria['OLD_LSFW_IC_SOFTWARE_LIVRE'] = $dataAnt['LSFW_IC_SOFTWARE_LIVRE'];
                        $dataAuditoria['NEW_LSFW_IC_SOFTWARE_LIVRE'] = $data['LSFW_IC_SOFTWARE_LIVRE'];
                        $dataAuditoria['OLD_LSFW_NR_PROCESSO_COMPRA'] = $dataAnt['LSFW_NR_PROCESSO_COMPRA'];
                        $dataAuditoria['NEW_LSFW_NR_PROCESSO_COMPRA'] = $data['LSFW_NR_PROCESSO_COMPRA'];
                        $dataAuditoria['OLD_LSFW_CD_TIPO_DOC_COMPRA'] = $dataAnt['LSFW_CD_TIPO_DOC_COMPRA'];
                        $dataAuditoria['NEW_LSFW_CD_TIPO_DOC_COMPRA'] = $data['LSFW_CD_TIPO_DOC_COMPRA'];
                        $dataAuditoria['OLD_LSFW_NR_ADIT_CONTRATO'] = $dataAnt['LSFW_NR_ADITAMENTO_CONTRATO'];
                        $dataAuditoria['NEW_LSFW_NR_ADIT_CONTRATO'] = $data['LSFW_NR_ADITAMENTO_CONTRATO'];
                        $dataAuditoria['OLD_LSFW_NR_CONTRATO'] = $dataAnt['LSFW_NR_ADITAMENTO_CONTRATO'];
                        $dataAuditoria['NEW_LSFW_NR_CONTRATO'] = $data['LSFW_NR_ADITAMENTO_CONTRATO'];
                        $dataAuditoria['OLD_LSFW_NR_PROCESSO_CONTRATO'] = $dataAnt['LSFW_NR_PROCESSO_CONTRATO'];
                        $dataAuditoria['NEW_LSFW_NR_PROCESSO_CONTRATO'] = $data['LSFW_NR_PROCESSO_CONTRATO'];
                        $dataAuditoria['OLD_LSFW_NR_TERMO'] = $dataAnt['LSFW_NR_TERMO'];
                        $dataAuditoria['NEW_LSFW_NR_TERMO'] = $data['LSFW_NR_TERMO'];
                        $dataAuditoria['OLD_LSFW_AA_TERMO'] = $dataAnt['LSFW_AA_TERMO'];
                        $dataAuditoria['NEW_LSFW_AA_TERMO'] = $data['LSFW_AA_TERMO'];
                        $dataAuditoria['OLD_LSFW_CD_TIPO_TERMO'] = $dataAnt['LSFW_CD_TIPO_TERMO'];
                        $dataAuditoria['NEW_LSFW_CD_TIPO_TERMO'] = $data['LSFW_CD_TIPO_TERMO'];
                        $dataAuditoria['OLD_LSFW_NR_TOMBO'] = $dataAnt['LSFW_NR_TOMBO'];
                        $dataAuditoria['NEW_LSFW_NR_TOMBO'] = $data['LSFW_NR_TOMBO'];
                        $dataAuditoria['OLD_LSFW_SG_TOMBO'] = $dataAnt['LSFW_SG_TOMBO'];
                        $dataAuditoria['NEW_LSFW_SG_TOMBO'] = $data['LSFW_SG_TOMBO'];
                        $dataAuditoria['OLD_LSFW_QT_ADQUIRIDA'] = $dataAnt['LSFW_QT_ADQUIRIDA'];
                        $dataAuditoria['NEW_LSFW_QT_ADQUIRIDA'] = $data['LSFW_QT_ADQUIRIDA'];
                        $dataAuditoria['OLD_LSFW_NR_DOC_ORIGEM'] = $dataAnt['LSFW_NR_DOC_ORIGEM'];
                        $dataAuditoria['NEW_LSFW_NR_DOC_ORIGEM'] = $data['LSFW_NR_DOC_ORIGEM'];
                        $dataAuditoria['OLD_LSFW_NR_NOTA_FISCAL'] = $dataAnt['LSFW_NR_NOTA_FISCAL'];
                        $dataAuditoria['NEW_LSFW_NR_NOTA_FISCAL'] = $data['LSFW_NR_NOTA_FISCAL'];
                        $rowAuditoria = $SosTbLsfwAuditoria->createRow($dataAuditoria);
                        $rowAuditoria->save();

                        $this->_helper->flashMessenger(array('message' => "Software <strong>" . $message . "</strong> atualizado com sucesso.", 'status' => 'success'));
                    } catch (Exception $e) {
//                        Zend_Debug::dump($e->getTraceAsString());die;
                        $this->_helper->flashMessenger(array('message' => "Erro ao atualizar o software:" . $e->getMessage(), 'status' => 'error'));
                    }

                    return $this->_helper->_redirector('index', 'labsoftware', 'sosti');
                }
            }
        }
    }

    /**
     * Busca os softwares relacionados a um modelo
     */
    public function ajaxsoftwareAction() {

        $modelo = Zend_Filter::FilterStatic($this->_getParam('modelo'), 'alnum');
        if ($modelo != "") {
            $tbSoftware = new Application_Model_DbTable_SosTbLsfwSoftware();
            $sofware = $tbSoftware->getSoftware($modelo);
        } else {
            $sofware = null;
        }
        $this->view->dados = $sofware;
    }

}
