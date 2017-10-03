<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Sosti_LabhardwareController extends Zend_Controller_Action
{

    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * 
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch() {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    var $controller;
    var $module;
    var $refController; //CONTROLLER HTTP REFFFER
    var $refAction; //ACTION HTTP REFFFER

    public function init() {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        $this->controller = $this->getRequest()->getControllerName();
        $this->module = $this->getRequest()->getModuleName();
    }

    public function ajaxmarcaAction() {
        $marca = $this->_getParam('term', '');
        $OcsTbMarcMarca = new Application_Model_DbTable_OcsTbMarcMarca ();
        $marca = $OcsTbMarcMarca->getMarcaLab($marca);

        $fim = count($marca);
        for ($i = 0; $i < $fim; $i++) {
            $marca [$i] = array_change_key_case($marca [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($marca);
    }

    public function ajaxmodeloAction() {
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $OcsTbModeModelo = new Application_Model_DbTable_OcsTbModeModelo ();
        $OcsTbModeModelo_array = $OcsTbModeModelo->getmodelosporMarca($id);
        $this->view->modelos = $OcsTbModeModelo_array;
    }

    public function indexAction() {

        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');

        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'LHDW_DS_HARDWARE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SosTbLhdwMaterialAlmox ();
        $rows = $dados->getHardwares($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');

        $this->view->title = "Hardware";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
    }

    public function addAction() {
        $this->view->title = 'Cadastrar Hardware';
        $form = new Sosti_Form_LabCadastroHardware ();
        $codMaterial = new Zend_Form_Element_Hidden('LHDW_CD_MATERIAL_H');
        $codMaterial->setRequired(true);
        $codMaterial->removeDecorator('Errors');
        $form->addElement($codMaterial);

        $userNs = new Zend_Session_Namespace('userNs');
        $table = new Application_Model_DbTable_SosTbLhdwMaterialAlmox ();
        $Dual = new Application_Model_DbTable_Dual();
        $ObjmaterialEntrada = new Application_Model_DbTable_SosTbMtenMaterialEntrada ();
        $OcsTbModeModelo = new Application_Model_DbTable_OcsTbModeModelo ();
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $this->view->form = $form;

        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //$arrayMaterial = explode("-", $data['LHDW_CD_MATERIAL']);
            $data['LHDW_CD_MATERIAL'] = trim($data['LHDW_CD_MATERIAL_H']);
            $lhdw_cd_modelo = $form->getElement('LHDW_CD_MODELO');
            $secao_subsecao_form = $form->getElement('SECAO_SUBSECAO');

            //Populando combobox
            $ModeModelo = $OcsTbModeModelo->getmodelosporMarca($data ['LHDW_CD_MARCA']);
            $lhdw_cd_modelo->addMultiOptions(array('' => ''));
            foreach ($ModeModelo as $Modelo) {
                $lhdw_cd_modelo->addMultiOptions(array($Modelo ["MODE_ID_MODELO"] => $Modelo ["MODE_DS_MODELO"]));
            }

            //Populando combobox
            $getLotacao = $rh_central->getLotacao();
            $secao_subsecao_form->addMultiOptions(array('' => ''));
            foreach ($getLotacao as $lotacao) {
                $secao_subsecao_form->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] . '|' . $lotacao["LOTA_TIPO_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
            }

            //validando form
            if ($form->isValid($data)) {
                unset($data ['LHDW_ID_HARDWARE']);
                unset($data ['LHDW_CD_MARCA']);
                unset($data ['LHDW_DS_MARCA']);
                $message = $data ['LHDW_DS_HARDWARE'];
                $secao_subsecao = explode('|', $data['TRF1_SECAO']);
                $data['LHDW_SIGLA_SECAO'] = $secao_subsecao[0];
                $data['LHDW_COD_LOTACAO'] = $secao_subsecao[1];

                //verifica se ja existe o hardware para aquela unidade
                $where = "LHDW_CD_MATERIAL = " . $data['LHDW_CD_MATERIAL'] . " AND 
                    LHDW_CD_MODELO = " . $data['LHDW_CD_MODELO'] . " AND 
                    LHDW_SIGLA_SECAO = '" . $data['LHDW_SIGLA_SECAO'] . "' AND 
                    LHDW_COD_LOTACAO = " . $data['LHDW_COD_LOTACAO'] . "";
                $existe = $table->fetchAll($where);
                if (count($existe) != 0) {
                    $this->view->msg_error = "O hardware não foi cadastrado, pois o mesmo já existe para esta Seção/Subseção.";
                    $form->populate($data);
                } else {
                    try {
                        if (empty($data['LHDW_ID_HARDWARE'])) {
                            $row = $table->createRow($data)->save();
                        }
                        $this->_helper->flashMessenger(array('message' => "O hardware: <strong>$message</strong> foi cadastrado!", 'status' => 'success'));
                        return $this->_helper->_redirector('index', 'labhardware', 'sosti');
                    } catch (Exception $e) {
                        $this->view->msg_error = "O hardware não foi cadastrado. Verifique se o número do processo de compra está correto ou se o mesmo encontra-se cadastrado." . $e->getMessage();
                        $form->populate($data);
                    }
                }
            } else {
                $form->populate($data);
            }
        }
    }

    public function editAction() {
        $this->view->title = 'Editar Hardware';
        $id = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $form = new Sosti_Form_LabCadastroHardware ();
        $userNs = new Zend_Session_Namespace('userNs');
        $objRH_CentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $ObjmaterialEntrada = new Application_Model_DbTable_SosTbMtenMaterialEntrada ();

        $table = new Application_Model_DbTable_SosTbLhdwMaterialAlmox ();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->getHardwareMarcaModelo($id);
            if ($row) {
                $data = $row;

                //Busca a lotação
                $Lotacao_array = $objRH_CentralLotacao->getSubSecoes($data['LHDW_SIGLA_SECAO'], $data['LHDW_COD_LOTACAO']);
                $tipoLotacaoInfo = $objRH_CentralLotacao->fetchRow(array('LOTA_SIGLA_SECAO=?' => $data['LHDW_SIGLA_SECAO'], 'LOTA_COD_LOTACAO=?' => $data['LHDW_COD_LOTACAO']))->toArray();
                $data['TRF1_SECAO'] = $data['LHDW_SIGLA_SECAO'] . "|" . $data['LHDW_COD_LOTACAO'] . "|" . $tipoLotacaoInfo['LOTA_TIPO_LOTACAO'];
                $data['SECAO_SUBSECAO'] = $Lotacao_array[0]['LOTA_SIGLA_SECAO'] . "|" . $Lotacao_array[0]['LOTA_COD_LOTACAO'] . "|" . $Lotacao_array[0]['LOTA_TIPO_LOTACAO'];

                //Busca Marca
                $data['LHDW_DS_MARCA'] = $data['MARC_DS_MARCA'];

                //Desabilitando edição dos campos
                $form->getElement('TRF1_SECAO')->setAttrib("disabled", true);
                $form->getElement('SECAO_SUBSECAO')->setAttrib("disabled", true);
                $form->getElement('LHDW_CD_MATERIAL')->setAttrib("disabled", true);

                //Definindo uma descrição de os campos não poderão ser modificados
                $form->TRF1_SECAO->setDescription('');
                $form->SECAO_SUBSECAO->setDescription('');
                $form->LHDW_CD_MATERIAL->setDescription('');
                $form->LHDW_DS_HARDWARE->setDescription('');
                $form->TRF1_SECAO->setAttrib("class", 'campo-leitura');
                $form->SECAO_SUBSECAO->setAttrib("class", 'campo-leitura');
                $form->LHDW_CD_MATERIAL->setAttrib("class", 'campo-leitura');

                //Nao serao alterados
                $form->getElement('TRF1_SECAO')->setRequired(false);
                $form->getElement('SECAO_SUBSECAO')->setRequired(false);
                $form->getElement('LHDW_CD_MATERIAL')->setRequired(false);

                //Popula formulario
                $form->populate($data);

                //Busca o Modelo
                $lhdw_cd_modelo = $form->getElement('LHDW_CD_MODELO');
                $OcsTbModeModelo = new Application_Model_DbTable_OcsTbModeModelo ();
                $ModeModelo = $OcsTbModeModelo->getmodelosporMarca($data['MARC_ID_MARCA']);
                $lhdw_cd_modelo->addMultiOptions(array('' => '::SELECIONE::'));
                foreach ($ModeModelo as $Modelo) {
                    $lhdw_cd_modelo->addMultiOptions(array($Modelo["MODE_ID_MODELO"] => $Modelo["MODE_DS_MODELO"]));
                }
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            //Recuperando Secao e Subsecao
            $secao = $data['TRF1_SECAO'];
            $subSecao = $data['SECAO_SUBSECAO'];
            $data = $this->getRequest()->getPost();
            // $dataPost terá os valores a serem populados
            $dataPost = $data;
            $dataPost['TRF1_SECAO'] = $secao;
            $dataPost['SECAO_SUBSECAO'] = $subSecao;

            //limpando valores para nao serem alterados
            unset($data['TRF1_SECAO']);
            unset($data['SECAO_SUBSECAO']);

            $lhdw_cd_modelo = $form->getElement('LHDW_CD_MODELO');
            $OcsTbModeModelo = new Application_Model_DbTable_OcsTbModeModelo ();
            $ModeModelo = $OcsTbModeModelo->getmodelosporMarca($data ['LHDW_CD_MARCA']);
            $lhdw_cd_modelo->addMultiOptions(array('' => '::SELECIONE::'));
            foreach ($ModeModelo as $Modelo) {
                $lhdw_cd_modelo->addMultiOptions(array($Modelo ["MODE_ID_MODELO"] => $Modelo ["MODE_DS_MODELO"]));
            }

            if ($form->isValid($data)) {

                $row = $table->find($data ['LHDW_ID_HARDWARE'])->current();
                try {
                    $row->setFromArray($data)->save();
                    $this->_helper->flashMessenger(array('message' => "O hardware foi atualizado!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labhardware', 'sosti');
                } catch (Exception $e) {
                    $this->view->msg_error = "O hardware não foi atualizado. Verifique se o número do processo de compra está correto ou se o mesmo encontra-se cadastrado.";
                    $form->populate($dataPost);
                }
            } else {
                $row = $table->getHardwareMarcaModelo($data['LHDW_ID_HARDWARE']);
                if ($row) {
                    $data = $row;

                    //Busca a lotação
                    $Lotacao_array = $objRH_CentralLotacao->getSubSecoes($data['LHDW_SIGLA_SECAO'], $data['LHDW_COD_LOTACAO']);
                    $tipoLotacaoInfo = $objRH_CentralLotacao->fetchRow(array('LOTA_SIGLA_SECAO=?' => $data['LHDW_SIGLA_SECAO'], 'LOTA_COD_LOTACAO=?' => $data['LHDW_COD_LOTACAO']))->toArray();
                    $data['TRF1_SECAO'] = $data['LHDW_SIGLA_SECAO'] . "|" . $data['LHDW_COD_LOTACAO'] . "|" . $tipoLotacaoInfo['LOTA_TIPO_LOTACAO'];
                    $data['SECAO_SUBSECAO'] = $Lotacao_array[0]['LOTA_SIGLA_SECAO'] . "|" . $Lotacao_array[0]['LOTA_COD_LOTACAO'] . "|" . $Lotacao_array[0]['LOTA_TIPO_LOTACAO'];

                    //Busca Marca
                    $data['LHDW_DS_MARCA'] = $data['MARC_DS_MARCA'];

                    //Desabilitando edição dos campos
                    $form->getElement('TRF1_SECAO')->setAttrib("disabled", true);
                    $form->getElement('SECAO_SUBSECAO')->setAttrib("disabled", true);
                    $form->getElement('LHDW_CD_MATERIAL')->setAttrib("disabled", true);

                    //Nao serao alterados
                    $form->getElement('TRF1_SECAO')->setRequired(false);
                    $form->getElement('SECAO_SUBSECAO')->setRequired(false);
                    $form->getElement('LHDW_CD_MATERIAL')->setRequired(false);

                    //Busca o Modelo
                    $lhdw_cd_modelo = $form->getElement('LHDW_CD_MODELO');
                    $OcsTbModeModelo = new Application_Model_DbTable_OcsTbModeModelo ();
                    $ModeModelo = $OcsTbModeModelo->getmodelosporMarca($data['MARC_ID_MARCA']);
                    $lhdw_cd_modelo->addMultiOptions(array('' => '::SELECIONE::'));
                    foreach ($ModeModelo as $Modelo) {
                        $lhdw_cd_modelo->addMultiOptions(array($Modelo["MODE_ID_MODELO"] => $Modelo["MODE_DS_MODELO"]));
                    }
                }
                $form->populate($data);
            }
        }
        $this->view->form = $form;
    }

    function entradamaterialAction() {

        $this->view->title = "Entrada de Material";
        $OcsTbModeModelo = new Application_Model_DbTable_OcsTbModeModelo ();
        $OcsTbMtenMaterialEntrada = new Application_Model_DbTable_SosTbMtenMaterialEntrada ();
        $OcsTbMtenAuditoria = new Application_Model_DbTable_SosTbMtenAuditoria ();
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $userNs = new Zend_Session_Namespace('userNs');
        $dual = new Application_Model_DbTable_Dual();
        $form = new Sosti_Form_LabEntradaMaterial();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            /*
             * Buscar modelos e incluir na lista do form para validar
             */
            $mten_cd_modelo = $form->getElement('MTEN_CD_MODELO');
            $ModeModelo = $OcsTbModeModelo->getmodelosporMarca($data['MTEN_CD_MARCA']);
            $mten_cd_modelo->addMultiOptions(array('' => ''));
            foreach ($ModeModelo as $Modelo) {
                $mten_cd_modelo->addMultiOptions(array($Modelo["MODE_ID_MODELO"] => $Modelo["MODE_DS_MODELO"]));
            }

            //Populando combobox
            $dadosSecao = explode('|', $data['TRF1_SECAO']);
            $secao_subsecao_form = $form->getElement('SECAO_SUBSECAO');
            $getLotacao = $rh_central->getSubSecoes($dadosSecao[0], $dadosSecao[1]);
            $secao_subsecao_form->addMultiOptions(array('' => ''));
            foreach ($getLotacao as $lotacao) {
                $secao_subsecao_form->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] . '|' . $lotacao["LOTA_TIPO_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
            }

            if ($form->isValid($data)) {

                //Tratamento de alguns valores
                $secao = explode('|', $data['SECAO_SUBSECAO']);
                $data['MTEN_SG_SECAO'] = $secao[0];
                $data['MTEN_CD_LOTACAO'] = $secao[1];
                $data['MTEN_CD_MATRICULA'] = $userNs->matricula;
                $data['MTEN_DT_ENTRADA_MATERIAL'] = $dual->sysdate();

                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                try {
                    $db->beginTransaction();
                    //Salva a entrada
                    $row = $OcsTbMtenMaterialEntrada->createRow($data)->save();
                    $dataTimeStamp = $dual->localtimestampDb();

                    //Auditoria
                    $dataAudit['MTEN_TS_OPERACAO'] = $dataTimeStamp['DATA'];
                    $dataAudit['MTEN_IC_OPERACAO'] = 'I';
                    $dataAudit['MTEN_CD_MATRICULA_OPERACAO'] = $data['MTEN_CD_MATRICULA'];
                    $dataAudit['MTEN_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                    $dataAudit['MTEN_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                    $dataAudit['OLD_MTEN_ID_HARDWARE'] = 0;
                    $dataAudit['NEW_MTEN_ID_HARDWARE'] = $data['MTEN_ID_HARDWARE'];
                    $dataAudit['OLD_MTEN_DT_ENTRADA_MATERIAL'] = null;
                    $dataAudit['NEW_MTEN_DT_ENTRADA_MATERIAL'] = $data['MTEN_DT_ENTRADA_MATERIAL'];
                    $dataAudit['OLD_MTEN_NR_REQ_MATERIAL'] = 0;
                    $dataAudit['NEW_MTEN_NR_REQ_MATERIAL'] = $data['MTEN_NR_REQUISICAO_MATERIAL'];
                    $dataAudit['OLD_MTEN_QT_ENTRADA_MATERIAL'] = 0;
                    $dataAudit['NEW_MTEN_QT_ENTRADA_MATERIAL'] = $data['MTEN_QT_ENTRADA_MATERIAL'];
                    $dataAudit['OLD_MTEN_CD_MATRICULA'] = "";
                    $dataAudit['NEW_MTEN_CD_MATRICULA'] = $data['MTEN_CD_MATRICULA'];
                    $dataAudit['OLD_MTEN_SG_SECAO'] = "";
                    $dataAudit['NEW_MTEN_SG_SECAO'] = $data['MTEN_SG_SECAO'];
                    $dataAudit['OLD_MTEN_CD_LOTACAO'] = 0;
                    $dataAudit['NEW_MTEN_CD_LOTACAO'] = $data['MTEN_CD_LOTACAO'];

                    $rowAuditoria = $OcsTbMtenAuditoria->createRow($dataAudit);
                    $rowAuditoria->save();

                    $db->commit();

                    $this->_helper->flashMessenger(array('message' => "Entrada de material cadastrada!", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'labhardware', 'sosti');
                } catch (Exception $e) {

                    $db->rollBack();
                    $this->_helper->flashMessenger(array('message' => "A entrada de material não foi cadastrada. Erro:" . $e->getMessage(), 'status' => 'error'));
                    return $this->_helper->_redirector('index', 'labhardware', 'sosti');
                    $form->populate($data);
                }
            } else {

                //Populando combobox
                $dadosSecao = explode('|', $data['TRF1_SECAO']);
                $secao_subsecao_form = $form->getElement('SECAO_SUBSECAO');
                $getLotacao = $rh_central->getSubSecoes($dadosSecao[0], $dadosSecao[1]);
                $secao_subsecao_form->addMultiOptions(array('' => ''));
                foreach ($getLotacao as $lotacao) {
                    $secao_subsecao_form->addMultiOptions(array($lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao["LOTA_COD_LOTACAO"] . '|' . $lotacao["LOTA_TIPO_LOTACAO"] => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"]));
                }

                $dadosPopulate = array(
                    'flag' => 'true',
                    'modelo' => $data['MTEN_CD_MODELO'],
                    'descricao_hardware' => $data['LHDW_DS_HARDWARE_AUX']
                );

                $this->view->dadosPopulate = $dadosPopulate;
                $this->view->msg_error = "Entrada de material não foi cadastrada. Preencha todos campos obrigatórios corretamente.";
                $form->populate($data);
            }
        }
    }

    public function checklistAction() {

        $order_direction = $this->_getParam('direcao', 'DESC');
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        $order_column = $this->_getParam('ordem', 'DOCM_NR_DOCUMENTO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        $order = $order_column . ' ' . $order_direction;
        $obj = new Application_Model_DbTable_SosTbSsolSolicitacao ();
        $rows = $obj->getCheckList($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage(50);

        $this->view->title = "Checklist";

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function formchecklistAction() {
        $this->view->title = "Criar Checklist";
        $formSoftware = new Sosti_Form_softwares();
        $formServico = new Sosti_Form_Servicos ();
        $formHardware = new Sosti_Form_Hardwares(array('attribs' => array('secao' => $siglaSecao, 'lotacao' => $lotacao)));
        $this->view->hardware = $formHardware;
        $this->view->formServico = $formServico;
        $this->view->Software = $formSoftware;
        $form = new Sosti_Form_LabCheckList ();
        $form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl() . '/' . $this->module . '/' . $this->controller . '/formchecklistsave');
        $objtable = new Application_Model_DbTable_LfsefichaServico ();
        $objSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao ();
        $ocsModelo = new Application_Model_DbTable_OcsTbModeModelo ();
        $objSoft = new Application_Model_DbTable_SosTbLtpsTipoSoftware ();
        $objSoftware = new Application_Model_DbTable_SosTbLsfwSoftware ();
        $objHardware = new Application_Model_DbTable_SosTbLhdwMaterialAlmox();
        $objServico = new Application_Model_DbTable_SosTbTpseTipoServico();

        $nivel = $this->_getParam('nivel');
        if ($nivel == 2) {
            $form->getElement('acao')->setValue('segundonivel');
            $form->getElement('controller')->setValue('atendimentotecnico');
            $this->refAction = 'segundonivel';
            $this->refController = 'atendimentotecnico';
        } elseif ($nivel == 3) {
            $form->getElement('acao')->setValue('terceironivel');
            $form->getElement('controller')->setValue('suporteespecializado');
            $this->refAction = 'terceironivel';
            $this->refController = 'suporteespecializado';
        }

        if ($this->getRequest()->isPost()) {

            $dataPost_raw = $this->getRequest()->getPost();
            if (count($dataPost_raw ['solicitacao']) > 1) {
                $msg_to_user = "Não é possível criar checklist para várias solicitações. Selecione apenas uma por vez.";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                $this->_helper->_redirector($this->refAction, $this->refController, 'sosti');
                return;
            }
            $dataPost = Zend_Json::decode($dataPost_raw ['solicitacao'] [0]);
            $userNs = new Zend_Session_Namespace('userNs');
            $siglaSecao = $userNs->siglasecao;
            $lotacao = $userNs->codsecsubseclotacao;
            Zend_Session::namespaceUnset("formChecklistSaveNs");

            //checa se o documento já possui ficha de serviço
            if ($objtable->verificaexitenciaFicha($dataPost ['SSOL_ID_DOCUMENTO'])) {
                $msg_to_user = "Este documento já possui um checklist. Selecione a opção Atualizar Checklist.";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                $this->_helper->_redirector($this->refAction, $this->refController, 'sosti');
                return;
            }
            $labelDataEntrada = $form->getElement('MOFA_DH_FASE');
            if ($nivel == 2) {
                $labelDataEntrada->setLabel('Data de Entrada no Segundo Nível');
            } else {
                $labelDataEntrada->setLabel('Data de Entrada no Terceiro Nível');
            }

            $dataFichaServico = $objtable->getFichaServico($dataPost ['SSOL_ID_DOCUMENTO']);
            $rowmodelos = $ocsModelo->getmodelosporMarca($dataFichaServico ['LFSE_CD_MARCA']);
            $dataSolicitacao = $objSolicitacao->getSolicitacaoInfo($dataPost ['DOCM_NR_DOCUMENTO']);

            //HORA DE ENTRADA NO NIVEL
            $dataentrada = $objSolicitacao->getdataEntradaNivel($dataPost ['SSOL_ID_DOCUMENTO'], $nivel, 1005);
            $dataSolicitacao[0]['MOFA_DH_FASE'] = $dataentrada[0]['MOFA_DH_FASE'];
//            Zend_Debug::dump($dataPost_raw);exit;
            $solicitacaohasTombo = $objSolicitacao->solicitacaohasTomboCentral($dataPost['DOCM_NR_DOCUMENTO']);
            //POPULA OS DADOS DA SOLICITAÇÃO
            $this->view->solicitacaoNr = $dataSolicitacao [0]['DOCM_NR_DOCUMENTO'];
            $this->view->observacao = $dataSolicitacao [0]['SSOL_DS_OBSERVACAO'];
            $this->view->localizacao = $dataSolicitacao [0]['SSOL_ED_LOCALIZACAO'];
            $this->view->entradaNivel = $dataSolicitacao [0]['MOFA_DH_FASE'];
            $this->view->tipoCadastro = $dataSolicitacao [0]['STCA_DS_TIPO_CAD'];
            $this->view->lotacao = $dataSolicitacao [0]['DOCM_CD_LOTACAO_GERADORA'];
            $this->view->siglalotacao = $dataSolicitacao [0]['DOCM_SG_SECAO_GERADORA'];
            $this->view->telefoneExterno = $dataSolicitacao [0]['SSOL_NR_TELEFONE_EXTERNO'];
            $this->view->emailExterno = $dataSolicitacao [0]['SSOL_DS_EMAIL_EXTERNO'];
            $this->view->numeroTombo = $dataSolicitacao [0]['NU_TOMBO'];
            $this->view->DescricaoMaterial = $dataFichaServico ['DE_MAT'];
            if (!$solicitacaohasTombo) {
                $msg_to_user = "Não é possível criar checklist para a solicitação. Insira um número de tombo na troca de serviço e tente novamente.";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                $this->_helper->_redirector($this->refAction, $this->refController, 'sosti');
                return;
            }

            $dataFichaServico['LTPS_ID_TP_SOFTWARE'] = $dataSolicitacao[0]['LSFW_ID_TP_SOFTWARE'];
            $dataFichaServico['DOCM_NR_DOCUMENTO'] = $dataSolicitacao [0]['DOCM_NR_DOCUMENTO'];
            $dataFichaServico['SSOL_NR_TOMBO_PESQUISA'] = $dataSolicitacao [0]['NU_TOMBO'];
            //Valor fixo para tombo
            $dataFichaServico['TI_TOMBO'] = 'T';

            if (!empty($dataSolicitacao[0]['LSFW_ID_TP_SOFTWARE'])) {
                $softwareLista = $objSoft->getSoftwareComboList($dataSolicitacao[0]['LSFW_ID_TP_SOFTWARE']);
            }

            //POPULA A COMBO MODELO DO BACKUP[LFSE_CD_MODELO]
            $cmbmodelo = $form->getElement('LFSE_CD_MODELO');

            foreach ($rowmodelos as $Modelo) {
                $cmbmodelo->addMultiOptions(array($Modelo ["MODE_ID_MODELO"] => $Modelo ["MODE_DS_MODELO"]));
            }

            //POPULA A COMBO SOFTWARE[LFSW_ID_SOFTWARE]
            $cmbsoftware = $form->getElement('LFSW_ID_SOFTWARE');

            foreach ($softwareLista as $softwareNome) {
                $cmbsoftware->addMultiOptions(array($softwareNome ["LSFW_ID_SOFTWARE"] => $softwareNome ["LSFW_DS_SOFTWARE"]));
            }

            $form->getElement('DOC_ID')->setValue($dataPost ['SSOL_ID_DOCUMENTO']);
            $form->populate($dataSolicitacao [0]); // popula form com dados da solicitição
            $form->populate($dataFichaServico); // popula form com dados da ficha de serviço
            $form->getElement('LFSE_ID_DOCUMENTO')->setValue($dataPost['DOCM_NR_DOCUMENTO']);
            $this->view->form = $form;
        } else {

            $dadosServicos = '';
            $dadosSoftwares = '';
            $dadosHardwares = '';

            /*
             * Se o formulario for invalido na action save, retorna pra ca para
             * fazer o populate do formulario, via Sessão
             */
            $checklistNs = new Zend_Session_Namespace('formChecklistSaveNs');
            if (!empty($checklistNs)) {
                if (!$form->isValid($checklistNs->dados)) {

                    $dataSolicitacao = $objSolicitacao->getSolicitacaoInfo($checklistNs->dados['DOCM_NR_DOCUMENTO']);
                    //HORA DE ENTRADA NO NIVEL
                    $dataentrada = $objSolicitacao->getdataEntradaNivel($checklistNs->dados['DOC_ID'], $nivel, 1005);
                    $dataSolicitacao[0]['MOFA_DH_FASE'] = $dataentrada[0]['MOFA_DH_FASE'];
                    $solicitacaohasTombo = $objSolicitacao->solicitacaohasTombo($checklistNs->dados['DOCM_NR_DOCUMENTO']);
                    //POPULA OS DADOS DA SOLICITAÇÃO
                    $this->view->solicitacaoNr = $dataSolicitacao [0]['DOCM_NR_DOCUMENTO'];
                    $this->view->observacao = $dataSolicitacao [0]['SSOL_DS_OBSERVACAO'];
                    $this->view->localizacao = $dataSolicitacao [0]['SSOL_ED_LOCALIZACAO'];
                    $this->view->entradaNivel = $dataSolicitacao [0]['MOFA_DH_FASE'];
                    $this->view->tipoCadastro = $dataSolicitacao [0]['STCA_DS_TIPO_CAD'];
                    $this->view->lotacao = $dataSolicitacao [0]['DOCM_CD_LOTACAO_GERADORA'];
                    $this->view->siglalotacao = $dataSolicitacao [0]['DOCM_SG_SECAO_GERADORA'];
                    $this->view->telefoneExterno = $dataSolicitacao [0]['SSOL_NR_TELEFONE_EXTERNO'];
                    $this->view->emailExterno = $dataSolicitacao [0]['SSOL_DS_EMAIL_EXTERNO'];
                    $this->view->numeroTombo = $dataSolicitacao [0]['NU_TOMBO'];
                    $this->view->DescricaoMaterial = $dataFichaServico ['DE_MAT'];
                    $checklistNs->dados['TI_TOMBO'] = 'T';

                    //Busca dadosdos servicos
                    if (!empty($checklistNs->dados['servicos'])) {
                        foreach ($checklistNs->dados['servicos'] as $servico) {
                            $dadosServicos[] = $objServico->getInfoServico($servico);
                        }
                    }

                    //Busca dados dos softwares
                    if (!empty($checklistNs->dados['softwares'])) {
                        $flag_s = 0;
                        foreach ($checklistNs->dados['softwares'] as $software) {
                            $dadosSoftwares[$flag_s] = $objSoftware->getSoftwareInfo($software);
                            $qtd_total_s = $objSoftware->getQtdTotalSoftware($software);
                            $qtd_saida_s = $objSoftware->getQtdLicencasSaida($software);
                            $qtd_s = (int) $qtd_total_s['QTD_TOTAL'] - (int) $qtd_saida_s['QTD_SAIDA'];
                            $dadosSoftwares[$flag_s]['qtd_soft_disponivel'] = $qtd_s;
                            $flag_s++;
                        }
                    }

                    //Busca dados dos hardwares
                    if (!empty($checklistNs->dados['hardwares'])) {
                        $flag_h = 0;
                        foreach ($checklistNs->dados['hardwares'] as $hardware) {
                            $dadosHardwares[$flag_h] = $objHardware->getHardwareMarcaModelo($hardware);
                            $qtd_total_h = $objHardware->getQtdTotalMaterial($hardware);
                            $qtd_saida_h = $objHardware->getQtdMaterialSaida($hardware);
                            $qtd_h = (int) $qtd_total_h['QTD_TOTAL'] - (int) $qtd_saida_h['QTD_SAIDA'];
                            $dadosHardwares[$flag_h]['qtd_hard_disponivel'] = $qtd_h;
                            $flag_h++;
                        }
                    }

                    //Popula os dados selecionados
                    $this->view->recuperaServicos = $dadosServicos;
                    $this->view->recuperaSoftwares = $dadosSoftwares;
                    $this->view->recuperaHardwares = $dadosHardwares;

                    //Popula os formulários
                    $form->populate($checklistNs->dados);
                    $this->view->formServico = $formServico;
                    $this->view->Software = $formSoftware;
                    $this->view->hardware = $formHardware;
                    $this->view->form = $form;
                }
            } else {
                return $this->_helper->_redirector('index', 'index', 'sosti');
            }
        }
    }

    /**
     * Alterar as informações do check list
     */
    public function formchecklisteditAction() {

        $this->view->title = "Atualizar Checklist";
        $formSoftware = new Sosti_Form_softwares();
        $formServico = new Sosti_Form_Servicos ();
        $formHardware = new Sosti_Form_Hardwares(array('attribs' => array('secao' => $siglaSecao, 'lotacao' => $lotacao)));
        $this->view->hardware = $formHardware;
        $this->view->formServico = $formServico;
        $this->view->Software = $formSoftware;
        $form = new Sosti_Form_LabCheckList ();
        $objtable = new Application_Model_DbTable_LfsefichaServico ();
        $objSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao ();
        $ocsModelo = new Application_Model_DbTable_OcsTbModeModelo ();
        $objSoft = new Application_Model_DbTable_SosTbLtpsTipoSoftware ();
        $objSoftware = new Application_Model_DbTable_SosTbLsfwSoftware ();
        $objHardware = new Application_Model_DbTable_SosTbLhdwMaterialAlmox();
        $objServico = new Application_Model_DbTable_SosTbTpseTipoServico();
        $objServicoBackup = new Application_Model_DbTable_SosTbLsbkServicoBackup ();
        $checklistNs = new Zend_Session_Namespace('formChecklistSaveNs');
        $objFichaServico = new Application_Model_DbTable_LfsefichaServico ();
        $objHardwareSaida = new Application_Model_DbTable_SosTbMtsaMaterialSaida();
        $objSaidaSoftware = new Application_Model_DbTable_SosTbLssaLicencaSoftSaida();
        $userNs = new Zend_Session_Namespace('userNs');
        $sys = new Application_Model_DbTable_Dual ();
        $objFichaSoftware = new Application_Model_DbTable_SosTbLfswFichaSoftware ();
        $objFichaHardware = new Application_Model_DbTable_SosTbLfhwFichaHardware();
        $servicoFichaObj = new Application_Model_DbTable_SosTbTpsfTipoServico ();

        $nivel = $this->_getParam('nivel');
        if ($nivel == 2) {
            $form->getElement('acao')->setValue('segundonivel');
            $form->getElement('controller')->setValue('atendimentotecnico');
            $this->refAction = 'segundonivel';
            $this->refController = 'atendimentotecnico';
        } elseif ($nivel == 3) {
            $form->getElement('acao')->setValue('terceironivel');
            $form->getElement('controller')->setValue('suporteespecializado');
            $this->refAction = 'terceironivel';
            $this->refController = 'suporteespecializado';
        }

        $form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl() . "/sosti/labhardware/formchecklistedit/nivel/$nivel");

        if ($this->getRequest()->isPost()) {
            $dataPost = $this->getRequest()->getPost();

            if (count($dataPost['solicitacao']) > 1) {
                $msg_to_user = "Não é possível alterar checklist para várias solicitações. Selecione apenas uma por vez.";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                $this->_helper->_redirector($this->refAction, $this->refController, 'sosti');
                return;
            }

            if (isset($dataPost['Salvar'])) {
                $checklistNs->dados = $dataPost;

                //capturando a tela de destino
                $refAction = $dataPost['acao'];
                $refController = $dataPost['controller'];

                //Se toda informação do form for válida prossiga com  a atualização do checklist
                if ($form->isValid($dataPost)) {

                    //Tratamento da ficha de serviço e todas as suas inclusões
                    $msgErro = "Não foi possível atualizar Checklist! ";
                    $msgSucesso = "Checklist atualizada com sucesso.";

                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    try {
                        $db->beginTransaction();
                        //Dados da Ficha de Serviço
                        $datafichaServico ['LFSE_ID_DOCUMENTO'] = $dataPost ['DOC_ID'];
                        $datafichaServico ['LFSE_NR_TOMBO'] = $dataPost ['SSOL_NR_TOMBO'];
                        $datafichaServico ['LFSE_TI_TOMBO'] = 'T';
                        $datafichaServico ['LFSE_NO_COMPUTADOR'] = $dataPost ['LFSE_NO_COMPUTADOR'];
                        $datafichaServico ['LFSE_ID_TP_USUARIO'] = $dataPost ['LFSE_ID_TP_USUARIO'];
                        $datafichaServico ['LFSE_DS_SERVICO_EXECUTADO'] = $dataPost ['LFSE_DS_SERVICO_EXECUTADO'];
                        $datafichaServico ['LFSE_DS_MOTIVO_MANUTENCAO'] = $dataPost ['LFSE_DS_MOTIVO_MANUTENCAO'];
                        $datafichaServico ['LFSE_IC_EXCLUSAO_ARQTEMP'] = ($dataPost ['LFSE_IC_EXCLUSAO_ARQTEMP'] == 1) ? 'S' : 'N';
                        $datafichaServico ['LFSE_IC_EXCLUSAO_PROFILE'] = ($dataPost ['LFSE_IC_EXCLUSAO_PROFILE'] == 1) ? 'S' : 'N';
                        $datafichaServico ['LFSE_IC_WINUPDATE'] = ($dataPost ['LFSE_IC_WINUPDATE'] == 1) ? 'S' : 'N';
                        $datafichaServico ['LFSE_IC_DESFRAGMENTACAO'] = ($dataPost ['LFSE_IC_DESFRAGMENTACAO'] == 1) ? 'S' : 'N';
                        $datafichaServico ['LFSE_IC_MANUTENCAO_EXTERNA'] = ($dataPost ['LFSE_IC_MANUTENCAO_EXTERNA'] == 1) ? 'S' : 'N';
                        $datafichaServico ['LFSE_IC_FORMATACAO'] = ($dataPost ['LFSE_IC_FORMATACAO'] == 1) ? 'S' : 'N';
                        $datafichaServico ['LFSE_IC_SCANDISK'] = ($dataPost ['LFSE_IC_SCANDISK'] == 1) ? 'S' : 'N';
                        $datafichaServico ['LFSE_IC_GARANTIA'] = ($dataPost ['LFSE_IC_GARANTIA'] == 1) ? 'S' : 'N';
                        $datafichaServico ['LFSE_DS_MOTIVO_MANUTENCAO'] = $dataPost ['LFSE_DS_MOTIVO_MANUTENCAO'];
                        $datafichaServico ['LFSE_IC_BACKUP'] = ($dataPost ['LFSE_IC_BACKUP'] == 1) ? 'S' : 'N';

                        //Auditoria
                        $dadosAnt = $objFichaServico->fetchRow('LFSE_ID_DOCUMENTO = ' . $dataPost["DOC_ID"]);
                        $SosTbLfseAuditoria = new Application_Model_DbTable_SosTbLfseAuditoria();
                        $dataAuditoriaLfse['LFSE_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                        $dataAuditoriaLfse['LFSE_IC_OPERACAO'] = 'A';
                        $dataAuditoriaLfse['LFSE_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                        $dataAuditoriaLfse['LFSE_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                        $dataAuditoriaLfse['LFSE_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                        $dataAuditoriaLfse['OLD_LFSE_ID_DOCUMENTO'] = $dadosAnt ['LFSE_ID_DOCUMENTO'];
                        $dataAuditoriaLfse['NEW_LFSE_ID_DOCUMENTO'] = $datafichaServico ['LFSE_ID_DOCUMENTO'];
                        $dataAuditoriaLfse['OLD_LFSE_ID_TP_USUARIO'] = $dadosAnt ['LFSE_ID_TP_USUARIO'];
                        $dataAuditoriaLfse['NEW_LFSE_ID_TP_USUARIO'] = $datafichaServico ['LFSE_ID_TP_USUARIO'];
                        $dataAuditoriaLfse['OLD_LFSE_CD_MODELO'] = NULL;
                        $dataAuditoriaLfse['NEW_LFSE_CD_MODELO'] = NULL;
                        $dataAuditoriaLfse['OLD_LFSE_CD_MARCA'] = NULL;
                        $dataAuditoriaLfse['NEW_LFSE_CD_MARCA'] = NULL;
                        $dataAuditoriaLfse['OLD_LFSE_DS_SERVICO_EXECUTADO'] = $dadosAnt ['LFSE_DS_SERVICO_EXECUTADO'];
                        $dataAuditoriaLfse['NEW_LFSE_DS_SERVICO_EXECUTADO'] = $datafichaServico ['LFSE_DS_SERVICO_EXECUTADO'];
                        $dataAuditoriaLfse['OLD_LFSE_DS_MOTIVO_MANUTENCAO'] = $dadosAnt ['LFSE_DS_MOTIVO_MANUTENCAO'];
                        $dataAuditoriaLfse['NEW_LFSE_DS_MOTIVO_MANUTENCAO'] = $datafichaServico ['LFSE_DS_MOTIVO_MANUTENCAO'];
                        $dataAuditoriaLfse['OLD_LFSE_DT_ENTRADA'] = $dadosAnt ['LFSE_DT_ENTRADA'];
                        $dataAuditoriaLfse['NEW_LFSE_DT_ENTRADA'] = $datafichaServico ['LFSE_DT_ENTRADA'];
                        $dataAuditoriaLfse['OLD_LFSE_DT_SAIDA'] = NULL;
                        $dataAuditoriaLfse['NEW_LFSE_DT_SAIDA'] = NULL;
                        $dataAuditoriaLfse['OLD_LFSE_IC_BACKUP'] = $dadosAnt ['LFSE_IC_BACKUP'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_BACKUP'] = $datafichaServico ['LFSE_IC_BACKUP'];
                        $dataAuditoriaLfse['OLD_LFSE_IC_FORMATACAO'] = $dadosAnt ['LFSE_IC_FORMATACAO'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_FORMATACAO'] = $datafichaServico ['LFSE_IC_FORMATACAO'];
                        $dataAuditoriaLfse['OLD_LFSE_IC_EXCLUSAO_ARQTEMP'] = $dadosAnt ['LFSE_IC_EXCLUSAO_ARQTEMP'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_EXCLUSAO_ARQTEMP'] = $datafichaServico ['LFSE_IC_EXCLUSAO_ARQTEMP'];
                        $dataAuditoriaLfse['OLD_LFSE_IC_EXCLUSAO_PROFILE'] = $dadosAnt ['LFSE_IC_EXCLUSAO_PROFILE'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_EXCLUSAO_PROFILE'] = $datafichaServico ['LFSE_IC_EXCLUSAO_PROFILE'];
                        $dataAuditoriaLfse['OLD_LFSE_IC_WINUPDATE'] = $dadosAnt ['LFSE_IC_WINUPDATE'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_WINUPDATE'] = $datafichaServico ['LFSE_IC_WINUPDATE'];
                        $dataAuditoriaLfse['OLD_LFSE_IC_DESFRAGMENTACAO'] = $dadosAnt ['LFSE_IC_DESFRAGMENTACAO'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_DESFRAGMENTACAO'] = $datafichaServico ['LFSE_IC_DESFRAGMENTACAO'];
                        $dataAuditoriaLfse['OLD_LFSE_IC_SCANDISK'] = $dadosAnt ['LFSE_IC_SCANDISK'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_SCANDISK'] = $datafichaServico ['LFSE_IC_SCANDISK'];
                        $dataAuditoriaLfse['OLD_LFSE_IC_MANUTENCAO_EXTERNA'] = $dadosAnt ['LFSE_IC_MANUTENCAO_EXTERNA'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_MANUTENCAO_EXTERNA'] = $datafichaServico ['LFSE_IC_MANUTENCAO_EXTERNA'];
                        $dataAuditoriaLfse['OLD_LFSE_IC_GARANTIA'] = $dadosAnt ['LFSE_IC_GARANTIA'];
                        $dataAuditoriaLfse['NEW_LFSE_IC_GARANTIA'] = $datafichaServico ['LFSE_IC_GARANTIA'];
                        $rowAuditoriaLfse = $SosTbLfseAuditoria->createRow($dataAuditoriaLfse);
                        $rowAuditoriaLfse->save();

                        //Cadastro da ficha de Serviço
                        $objFichaServico->update($datafichaServico, 'LFSE_ID_DOCUMENTO = ' . $dataPost["DOC_ID"]);
                        //Tratamento dos hardwares 
                        if (!empty($dataPost['hardwares'])) {

                            $array_hardwares_doc = array();
                            $array_hardwares_post = array();
                            $array_hardwares_novos = array();
                            $array_hardwares_excluidos = array();
                            $array_hardwares_alterados = array();

                            //hardwares do doc
                            $pesquisa = $objHardwareSaida->todosHardwaresDocumento($dataPost['DOC_ID']);
                            foreach ($pesquisa as $hard) {
                                $array_hardwares_doc[] = $hard['MTSA_ID_HARDWARE'];
                            }
                            //hardwares do post
                            $array_hardwares_post = $dataPost['hardwares'];
                            //hardwares adicionados
                            $array_hardwares_novos = array_diff($array_hardwares_post, $array_hardwares_doc);
                            //hardwares excluidos
                            $array_hardwares_excluidos = array_diff($array_hardwares_doc, $array_hardwares_post);
                            //hardwares alterados
                            $array_hardwares_alterados = array_intersect($array_hardwares_post, $array_hardwares_doc);

                            if (count($array_hardwares_novos) > 0) {
                                foreach ($array_hardwares_novos as $hardware) {
                                    //Verificar se o hardware solicitado ainda possui a quantidade
                                    //receber aqui o valor do banco
                                    $qtd_total_h = $objHardware->getQtdTotalMaterial($hardware);
                                    $qtd_saida_h = $objHardware->getQtdMaterialSaida($hardware);
                                    $qtd_h = (int) $qtd_total_h['QTD_TOTAL'] - (int) $qtd_saida_h['QTD_SAIDA'];

                                    if ($dataPost['qtdHardware'][$hardware] > $qtd_h) {
                                        $hardwareErro = $objHardware->find('where LHDW_ID_HARDWARE = ' . $hardware);
                                        throw Exception('O hardware ' . $hardwareErro['LHDW_DS_HARDWARE'] . ' não possui mais a quantidade solicitada!');
                                    } else {
                                        $dataFichaHardware['LFHW_ID_HARDWARE'] = $hardware;
                                        $dataFichaHardware['LFHW_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                        $dataFichaHardware['LFHW_QT_MATERIAL_ALMOX'] = $dataPost['qtdHardware'][$hardware];

                                        //Auditoria
                                        $SosTbLfhwAuditoria = new Application_Model_DbTable_SosTbLfhwAuditoria();
                                        $dataAuditoriaNovoLfhw['LFHW_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                        $dataAuditoriaNovoLfhw['LFHW_IC_OPERACAO'] = 'I';
                                        $dataAuditoriaNovoLfhw['LFHW_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                        $dataAuditoriaNovoLfhw['LFHW_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                        $dataAuditoriaNovoLfhw['LFHW_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                        $dataAuditoriaNovoLfhw['OLD_LFHW_ID_DOCUMENTO'] = NULL;
                                        $dataAuditoriaNovoLfhw['NEW_LFHW_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                        $dataAuditoriaNovoLfhw['OLD_LFHW_ID_HARDWARE'] = NULL;
                                        $dataAuditoriaNovoLfhw['NEW_LFHW_ID_HARDWARE'] = $hardware;
                                        $dataAuditoriaNovoLfhw['OLD_LFHW_QT_MATERIAL_ALMOX'] = NULL;
                                        $dataAuditoriaNovoLfhw['NEW_LFHW_QT_MATERIAL_ALMOX'] = $dataPost['qtdHardware'][$hardware];
                                        $rowAuditoriaNovoLfhw = $SosTbLfhwAuditoria->createRow($dataAuditoriaNovoLfhw);
                                        $rowAuditoriaNovoLfhw->save();

                                        //Cria ficha de hardware
                                        $objFichaHardware->createRow($dataFichaHardware)->save();

                                        //Trata a saida de equipamento
                                        $saidaEquipamento['MTSA_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                        $saidaEquipamento['MTSA_ID_HARDWARE'] = $hardware;
                                        $saidaEquipamento['MTSA_QT_SAIDA_MATERIAL'] = 0;
                                        $saidaEquipamento['MTSA_DT_SAIDA_MATERIAL'] = App_Util::getTimeStamp_Audit();
                                        $saidaEquipamento['MTSA_CD_MATRICULA'] = $userNs->matricula;
                                        $saidaEquipamento['MTSA_SG_SECAO'] = $userNs->siglasecao;
                                        $saidaEquipamento['MTSA_CD_LOTACAO'] = $userNs->codsecsubseclotacao;
                                        $saidaEquipamento['MTSA_IC_APROVACAO'] = 'S';
                                        $saidaEquipamento['MTSA_QT_SOLIC_SAIDA_MATERIAL'] = $dataPost['qtdHardware'][$hardware];

                                        //Cria a saida do material
                                        $objHardwareSaida->createRow($saidaEquipamento)->save();

                                        //Auditoria
                                        $SosTbMtsaAuditoria = new Application_Model_DbTable_SosTbMtsaAuditoria();
                                        $dataAuditoriaNovoMtsa['MTSA_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                        $dataAuditoriaNovoMtsa['MTSA_IC_OPERACAO'] = 'I';
                                        $dataAuditoriaNovoMtsa['MTSA_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                        $dataAuditoriaNovoMtsa['MTSA_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                        $dataAuditoriaNovoMtsa['MTSA_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                        $dataAuditoriaNovoMtsa['OLD_MTSA_ID_DOCUMENTO'] = NULL;
                                        $dataAuditoriaNovoMtsa['NEW_MTSA_ID_DOCUMENTO'] = $saidaEquipamento['MTSA_ID_DOCUMENTO'];
                                        $dataAuditoriaNovoMtsa['OLD_MTSA_ID_HARDWARE'] = NULL;
                                        $dataAuditoriaNovoMtsa['NEW_MTSA_ID_HARDWARE'] = $saidaEquipamento['MTSA_ID_HARDWARE'];
                                        $dataAuditoriaNovoMtsa['OLD_MTSA_DT_SAIDA_MATERIAL'] = NULL;
                                        $dataAuditoriaNovoMtsa['NEW_MTSA_DT_SAIDA_MATERIAL'] = $saidaEquipamento['MTSA_DT_SAIDA_MATERIAL'];
                                        $dataAuditoriaNovoMtsa['OLD_MTSA_QT_SAIDA_MATERIAL'] = NULL;
                                        $dataAuditoriaNovoMtsa['NEW_MTSA_QT_SAIDA_MATERIAL'] = $saidaEquipamento['MTSA_QT_SOLIC_SAIDA_MATERIAL'];
                                        $dataAuditoriaNovoMtsa['OLD_MTSA_CD_MATRICULA'] = NULL;
                                        $dataAuditoriaNovoMtsa['NEW_MTSA_CD_MATRICULA'] = $saidaEquipamento['MTSA_CD_MATRICULA'];
                                        $dataAuditoriaNovoMtsa['OLD_MTSA_SG_SECAO'] = NULL;
                                        $dataAuditoriaNovoMtsa['NEW_MTSA_SG_SECAO'] = $saidaEquipamento['MTSA_SG_SECAO'];
                                        $dataAuditoriaNovoMtsa['OLD_MTSA_CD_LOTACAO'] = NULL;
                                        $dataAuditoriaNovoMtsa['NEW_MTSA_CD_LOTACAO'] = $saidaEquipamento['MTSA_CD_LOTACAO'];
                                        $rowAuditoriaNovoMtsa = $SosTbMtsaAuditoria->createRow($dataAuditoriaNovoMtsa);
                                        $rowAuditoriaNovoMtsa->save();
                                    }
                                }
                            }
                            if (count($array_hardwares_excluidos) > 0) {
                                foreach ($array_hardwares_excluidos as $h_exc) {
                                    $objFichaHardware->delete('lfhw_id_documento = ' . $dataPost["DOC_ID"] . ' and lfhw_id_hardware = ' . $h_exc);
                                    $objHardwareSaida->delete('mtsa_id_documento = ' . $dataPost["DOC_ID"] . ' and mtsa_id_hardware = ' . $h_exc);
                                }
                            }
                            if (count($array_hardwares_alterados) > 0) {
                                foreach ($array_hardwares_alterados as $h_alt) {
                                    $dadosHardwareAlt['MTSA_QT_SOLIC_SAIDA_MATERIAL'] = $dataPost['qtdHardware'][$h_alt];
                                    $dadosHardwareAltFicha['LFHW_QT_MATERIAL_ALMOX'] = $dataPost['qtdHardware'][$h_alt];
                                    $objHardwareSaida->update($dadosHardwareAlt, 'MTSA_ID_DOCUMENTO = ' . $dataPost["DOC_ID"] . ' and MTSA_ID_HARDWARE = ' . $h_alt);
                                    $objFichaHardware->update($dadosHardwareAltFicha, 'LFHW_ID_DOCUMENTO = ' . $dataPost["DOC_ID"] . ' and LFHW_ID_HARDWARE = ' . $h_alt);

                                    //Trata a saida de equipamento
                                    $saidaEquipamento['MTSA_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                    $saidaEquipamento['MTSA_ID_HARDWARE'] = $h_alt;
                                    $saidaEquipamento['MTSA_QT_SAIDA_MATERIAL'] = 0;
                                    $saidaEquipamento['MTSA_DT_SAIDA_MATERIAL'] = App_Util::getTimeStamp_Audit();
                                    $saidaEquipamento['MTSA_CD_MATRICULA'] = $userNs->matricula;
                                    $saidaEquipamento['MTSA_SG_SECAO'] = $userNs->siglasecao;
                                    $saidaEquipamento['MTSA_CD_LOTACAO'] = $userNs->codsecsubseclotacao;
                                    $saidaEquipamento['MTSA_IC_APROVACAO'] = 'S';
                                    $saidaEquipamento['MTSA_QT_SOLIC_SAIDA_MATERIAL'] = $dataPost['qtdHardware'][$h_alt];

                                    //Auditoria
                                    $SosTbMtsaAuditoria = new Application_Model_DbTable_SosTbMtsaAuditoria();
                                    $dataAuditoriaNovoMtsa['MTSA_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                    $dataAuditoriaNovoMtsa['MTSA_IC_OPERACAO'] = 'A';
                                    $dataAuditoriaNovoMtsa['MTSA_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                    $dataAuditoriaNovoMtsa['MTSA_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                    $dataAuditoriaNovoMtsa['MTSA_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                    $dataAuditoriaNovoMtsa['OLD_MTSA_ID_DOCUMENTO'] = $saidaEquipamento['MTSA_ID_DOCUMENTO'];
                                    $dataAuditoriaNovoMtsa['NEW_MTSA_ID_DOCUMENTO'] = $saidaEquipamento['MTSA_ID_DOCUMENTO'];
                                    $dataAuditoriaNovoMtsa['OLD_MTSA_ID_HARDWARE'] = $saidaEquipamento['MTSA_ID_HARDWARE'];
                                    $dataAuditoriaNovoMtsa['NEW_MTSA_ID_HARDWARE'] = $saidaEquipamento['MTSA_ID_HARDWARE'];
                                    $dataAuditoriaNovoMtsa['OLD_MTSA_DT_SAIDA_MATERIAL'] = $saidaEquipamento['MTSA_DT_SAIDA_MATERIAL'];
                                    $dataAuditoriaNovoMtsa['NEW_MTSA_DT_SAIDA_MATERIAL'] = $saidaEquipamento['MTSA_DT_SAIDA_MATERIAL'];
                                    $dataAuditoriaNovoMtsa['OLD_MTSA_QT_SAIDA_MATERIAL'] = $saidaEquipamento['MTSA_QT_SOLIC_SAIDA_MATERIAL'];
                                    $dataAuditoriaNovoMtsa['NEW_MTSA_QT_SAIDA_MATERIAL'] = $dadosHardwareAlt['MTSA_QT_SOLIC_SAIDA_MATERIAL'];
                                    $dataAuditoriaNovoMtsa['OLD_MTSA_CD_MATRICULA'] = $saidaEquipamento['MTSA_CD_MATRICULA'];
                                    $dataAuditoriaNovoMtsa['NEW_MTSA_CD_MATRICULA'] = $saidaEquipamento['MTSA_CD_MATRICULA'];
                                    $dataAuditoriaNovoMtsa['OLD_MTSA_SG_SECAO'] = $saidaEquipamento['MTSA_SG_SECAO'];
                                    $dataAuditoriaNovoMtsa['NEW_MTSA_SG_SECAO'] = $saidaEquipamento['MTSA_SG_SECAO'];
                                    $dataAuditoriaNovoMtsa['OLD_MTSA_CD_LOTACAO'] = $saidaEquipamento['MTSA_CD_LOTACAO'];
                                    $dataAuditoriaNovoMtsa['NEW_MTSA_CD_LOTACAO'] = $saidaEquipamento['MTSA_CD_LOTACAO'];
                                    $rowAuditoriaNovoMtsa = $SosTbMtsaAuditoria->createRow($dataAuditoriaNovoMtsa);
                                    $rowAuditoriaNovoMtsa->save();
                                }
                            }
                        } else {
                            $objFichaHardware->delete();
                            $objHardwareSaida->delete();
                        }

                        //Tratamento do software 
                        if (!empty($dataPost['softwares'])) {

                            $array_softwares_doc = array();
                            $array_softwares_post = array();
                            $array_softwares_novos = array();
                            $array_softwares_excluidos = array();
                            $array_softwares_alterados = array();
                            $array_softwares_doc = array();

                            //softwares do doc
                            $pesquisa_s = $objSaidaSoftware->todosSoftwaresDocumento($dataPost['DOC_ID']);
                            foreach ($pesquisa_s as $soft) {
                                $array_softwares_doc[] = $soft['LSSA_ID_SOFTWARE'];
                            }
                            //hardwares do post
                            $array_softwares_post = $dataPost['softwares'];
                            //hardwares adicionados
                            $array_softwares_novos = array_diff($array_softwares_post, $array_softwares_doc);
                            //hardwares excluidos
                            $array_softwares_excluidos = array_diff($array_softwares_doc, $array_softwares_post);
                            //hardwares alterados
                            $array_softwares_alterados = array_intersect($array_softwares_post, $array_softwares_doc);

                            if (count($array_softwares_novos) > 0) {
                                foreach ($array_softwares_novos as $software) {
                                    //Verificar se o software solicitado ainda possui a quantidade
                                    //receber aqui o valor do banco
                                    $qtd_total_s = $objSoftware->getQtdTotalSoftware($software);
                                    $qtd_saida_s = $objSoftware->getQtdLicencasSaida($software);
                                    $qtd_s = (int) $qtd_total_s['QTD_TOTAL'] - (int) $qtd_saida_s['QTD_SAIDA'];

                                    if ($qtd_s == 0) {
                                        $softwareErro = $objSoftware->find('WHERE LSFW_ID_SOFTWARE = ' . $software);
                                        throw Exception('O software ' . $softwareErro['LSFW_DS_SOFTWARE'] . ' não possui mais a quantidade solicitada!');
                                    } else {

                                        //Dados da ficha de software
                                        $dataFichaSoftware['LFSW_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                        $dataFichaSoftware['LFSW_ID_SOFTWARE'] = $software;

                                        //Cria a ficha de software
                                        $objFichaSoftware->createRow($dataFichaSoftware)->save();

                                        //Auditoria
                                        $SosTbLfswAuditoria = new Application_Model_DbTable_SosTbLfswAuditoria();
                                        $dataAuditoriaNovoLfsw['LFSW_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                        $dataAuditoriaNovoLfsw['LFSW_IC_OPERACAO'] = 'I';
                                        $dataAuditoriaNovoLfsw['LFSW_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                        $dataAuditoriaNovoLfsw['LFSW_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                        $dataAuditoriaNovoLfsw['LFSW_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                        $dataAuditoriaNovoLfsw['OLD_LFSW_ID_DOCUMENTO'] = NULL;
                                        $dataAuditoriaNovoLfsw['NEW_LFSW_ID_DOCUMENTO'] = $dataFichaSoftware['LFSW_ID_DOCUMENTO'];
                                        $dataAuditoriaNovoLfsw['OLD_LFSW_ID_SOFTWARE'] = NULL;
                                        $dataAuditoriaNovoLfsw['NEW_LFSW_ID_SOFTWARE'] = $dataFichaSoftware['LFSW_ID_SOFTWARE'];
                                        $rowAuditoriaNovoLfsw = $SosTbLfswAuditoria->createRow($dataAuditoriaNovoLfsw);
                                        $rowAuditoriaNovoLfsw->save();

                                        //Dados da saída de software
                                        $dataSaidaSoftware['LSSA_ID_SOFTWARE'] = $software;
                                        $dataSaidaSoftware['LSSA_ID_SOFTWARE'] = $software;
                                        $dataSaidaSoftware['LSSA_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                        $dataSaidaSoftware['LSSA_CD_MATRICULA'] = $userNs->matricula;
                                        $dataSaidaSoftware['LSSA_DT_SAIDA'] = $sys->sysdate();
                                        $dataSaidaSoftware['LSSA_IC_APROVACAO'] = 'S';

                                        //Cadastra a saida do software
                                        $objSaidaSoftware->createRow($dataSaidaSoftware)->save();
                                    }
                                }
                            }
                            if (count($array_softwares_excluidos) > 0) {
                                foreach ($array_softwares_excluidos as $s_exc) {
                                    $objSaidaSoftware->delete('lssa_id_documento = ' . $dataPost["DOC_ID"] . ' and lssa_id_software = ' . $s_exc);
                                    $objFichaSoftware->delete('lfsw_id_documento = ' . $dataPost["DOC_ID"] . ' and lfsw_id_software = ' . $s_exc);

                                    //Auditoria
                                    $SosTbLfswAuditoria = new Application_Model_DbTable_SosTbLfswAuditoria();
                                    $dataAuditoriaExcLfsw['LFSW_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                    $dataAuditoriaExcLfsw['LFSW_IC_OPERACAO'] = 'E';
                                    $dataAuditoriaExcLfsw['LFSW_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                    $dataAuditoriaExcLfsw['LFSW_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                    $dataAuditoriaExcLfsw['LFSW_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                    $dataAuditoriaExcLfsw['OLD_LFSW_ID_DOCUMENTO'] = $dataPost["DOC_ID"];
                                    $dataAuditoriaExcLfsw['NEW_LFSW_ID_DOCUMENTO'] = NULL;
                                    $dataAuditoriaExcLfsw['OLD_LFSW_ID_SOFTWARE'] = $s_exc;
                                    $dataAuditoriaExcLfsw['NEW_LFSW_ID_SOFTWARE'] = NULL;
                                    $rowAuditoriaExcLfsw = $SosTbLfswAuditoria->createRow($dataAuditoriaExcLfsw);
                                    $rowAuditoriaExcLfsw->save();
                                }
                            }
                        } else {
                            $objSaidaSoftware->delete();
                            $objFichaSoftware->delete();
                        }

                        //Tratamento dos serviços 
                        if (!empty($dataPost['servicos'])) {

                            $array_servicos_doc = array();
                            $array_servicos_novos = array();
                            $array_servicos_excluidos = array();
                            $array_servicos_alterados = array();

                            $pesquisa_serv = $objServico->getTpServicoPorDocumento($dataPost['DOC_ID']);
                            foreach ($pesquisa_serv as $serv) {
                                $array_servicos_doc[] = $serv['TPSE_ID_TP_SERVICO'];
                            }
                            //servicos do post
                            $array_servicos_post = $dataPost['servicos'];
                            //servicos adicionados
                            $array_servicos_novos = array_diff($array_servicos_post, $array_servicos_doc);
                            //servicos excluidos
                            $array_servicos_excluidos = array_diff($array_servicos_doc, $array_servicos_post);
                            //servicos alterados
                            $array_servicos_alterados = array_intersect($array_servicos_post, $array_servicos_doc);

                            if (count($array_servicos_novos) > 0) {
                                foreach ($array_servicos_novos as $servicos) {

                                    //Dados da ficha de servico
                                    $dataFichaServico['TPSF_ID_TP_SERVICO'] = $servicos;
                                    $dataFichaServico['TPSF_ID_DOCUMENTO'] = $dataPost['DOC_ID'];

                                    //Cadastra a ficha de servico
                                    $servicoFichaObj->createRow($dataFichaServico)->save();
                                }
                            }
                            if (count($array_servicos_excluidos) > 0) {
                                foreach ($array_servicos_excluidos as $ser_exc) {
                                    $servicoFichaObj->delete('TPSF_ID_DOCUMENTO = ' . $dataPost["DOC_ID"] . ' and TPSF_ID_TP_SERVICO = ' . $ser_exc);
                                }
                            }
                        } else {
                            $servicoFichaObj->delete();
                        }

                        //Tratamento do Tombo
                        if (!empty($dataPost['LBKP_NR_TOMBO'])) {

                            //verificar se ja existe tombo de backup
                            $verificaBackup = $objServicoBackup->gettomboBackupPeloIDDocumento($dataPost['DOC_ID']);
                            if (count($verificaBackup) > 0) {
                                //Se tiver backup, compara e verifica se precisa alterar o backup
                                if ($dataPost['LBKP_NR_TOMBO'] != $verificaBackup['LSBK_NR_TOMBO']) {
                                    //Auditoria
                                    $SosTbLsbkAuditoria = new Application_Model_DbTable_SosTbLsbkAuditoria();
                                    $dataAuditoria['LSBK_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                    $dataAuditoria['LSBK_IC_OPERACAO'] = 'I';
                                    $dataAuditoria['LSBK_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                    $dataAuditoria['LSBK_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                    $dataAuditoria['LSBK_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                    $dataAuditoria['OLD_LSBK_ID_DOCUMENTO'] = $verificaBackup['LSBK_ID_DOCUMENTO'];
                                    $dataAuditoria['NEW_LSBK_ID_DOCUMENTO'] = $dataPost['LSBK_ID_DOCUMENTO'];
                                    $dataAuditoria['OLD_LSBK_NR_TOMBO'] = $verificaBackup['LSBK_NR_TOMBO'];
                                    $dataAuditoria['NEW_LSBK_NR_TOMBO'] = $dataPost['LSBK_NR_TOMBO'];
                                    $dataAuditoria['OLD_LSBK_TP_TOMBO'] = $verificaBackup['LSBK_TP_TOMBO'];
                                    $dataAuditoria['NEW_LSBK_TP_TOMBO'] = $dataPost['LSBK_TP_TOMBO'];
                                    $dataAuditoria['OLD_LSBK_DT_EMPRESTIMO'] = $verificaBackup['LSBK_DT_EMPRESTIMO'];
                                    $dataAuditoria['NEW_LSBK_DT_EMPRESTIMO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY')");
                                    $dataAuditoria['OLD_LSBK_DT_REC_USUARIO'] = NULL;
                                    $dataAuditoria['NEW_LSBK_DT_REC_USUARIO'] = NULL;
                                    $dataAuditoria['OLD_SBK_DT_REC_DEVOL'] = NULL;
                                    $dataAuditoria['NEW_SBK_DT_REC_DEVOL'] = NULL;
                                    $dataAuditoria['OLD_LSBK_CD_MAT_EMPRESTIMO'] = $verificaBackup['LSBK_CD_MAT_EMPRESTIMO'];
                                    $dataAuditoria['NEW_LSBK_CD_MAT_EMPRESTIMO'] = $verificaBackup['LSBK_CD_MAT_EMPRESTIMO'];
                                    $dataAuditoria['OLD_LSBK_CD_MAT_RECEB_USUARIO'] = NULL;
                                    $dataAuditoria['NEW_LSBK_CD_MAT_RECEB_USUARIO'] = NULL;
                                    $dataAuditoria['OLD_LSBK_CD_MAT_RECEB_DEVOL'] = NULL;
                                    $dataAuditoria['NEW_LSBK_CD_MAT_RECEB_DEVOL'] = NULL;
                                    $rowAuditoria = $SosTbLsbkAuditoria->createRow($dataAuditoria);
                                    $rowAuditoria->save();

                                    //update do backup
                                    $servicoBackupdata ['LSBK_NR_TOMBO'] = $dataPost['LBKP_NR_TOMBO'];
                                    $servicoBackupdata ['LSBK_DT_EMPRESTIMO'] = $sys->sysdate();
                                    $servicoBackupdata ['LSBK_DT_RECEBIMENTO_DEVOLUCAO'] = NULL;
                                    $where = 'LSBK_ID_DOCUMENTO = ' . $dataPost['DOC_ID'] . ' and LSBK_NR_TOMBO = ' . $verificaBackup['LSBK_NR_TOMBO'] . '';
                                    $objServicoBackup->update($servicoBackupdata, $where);
                                }
                            } else {
                                //Se não tiver backup, então cadastra os dados
                                $servicoBackupdata ['LSBK_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                $servicoBackupdata ['LSBK_NR_TOMBO'] = $dataPost['LBKP_NR_TOMBO'];
                                $servicoBackupdata ['LSBK_TP_TOMBO'] = 'T';
                                $servicoBackupdata ['LSBK_CD_MAT_EMPRESTIMO'] = $userNs->matricula;
                                $servicoBackupdata ['LSBK_DT_EMPRESTIMO'] = $sys->sysdate();

                                //Verifica a disponibilidade do tombo
                                $disp = $objServicoBackup->getVerificaDisponibilidadeBackup($dataPost['LBKP_NR_TOMBO']);
                                //Se o backup ainda estiver disponivel, cadastrar
                                if (count($disp) == 0) {

                                    //Auditoria
                                    $SosTbLsbkAuditoria = new Application_Model_DbTable_SosTbLsbkAuditoria();
                                    $dataAuditoria['LSBK_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                    $dataAuditoria['LSBK_IC_OPERACAO'] = 'I';
                                    $dataAuditoria['LSBK_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                    $dataAuditoria['LSBK_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                    $dataAuditoria['LSBK_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                    $dataAuditoria['OLD_LSBK_ID_DOCUMENTO'] = NULL;
                                    $dataAuditoria['NEW_LSBK_ID_DOCUMENTO'] = $servicoBackupdata['LSBK_ID_DOCUMENTO'];
                                    $dataAuditoria['OLD_LSBK_NR_TOMBO'] = null;
                                    $dataAuditoria['NEW_LSBK_NR_TOMBO'] = $servicoBackupdata['LSBK_NR_TOMBO'];
                                    $dataAuditoria['OLD_LSBK_TP_TOMBO'] = null;
                                    $dataAuditoria['NEW_LSBK_TP_TOMBO'] = $servicoBackupdata['LSBK_TP_TOMBO'];
                                    $dataAuditoria['OLD_LSBK_DT_EMPRESTIMO'] = NULL;
                                    $dataAuditoria['NEW_LSBK_DT_EMPRESTIMO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY')");
                                    $dataAuditoria['OLD_LSBK_DT_REC_USUARIO'] = NULL;
                                    $dataAuditoria['NEW_LSBK_DT_REC_USUARIO'] = NULL;
                                    $dataAuditoria['OLD_SBK_DT_REC_DEVOL'] = NULL;
                                    $dataAuditoria['NEW_SBK_DT_REC_DEVOL'] = NULL;
                                    $dataAuditoria['OLD_LSBK_CD_MAT_EMPRESTIMO'] = NULL;
                                    $dataAuditoria['NEW_LSBK_CD_MAT_EMPRESTIMO'] = $servicoBackupdata['LSBK_CD_MAT_EMPRESTIMO'];
                                    $dataAuditoria['OLD_LSBK_CD_MAT_RECEB_USUARIO'] = NULL;
                                    $dataAuditoria['NEW_LSBK_CD_MAT_RECEB_USUARIO'] = NULL;
                                    $dataAuditoria['OLD_LSBK_CD_MAT_RECEB_DEVOL'] = NULL;
                                    $dataAuditoria['NEW_LSBK_CD_MAT_RECEB_DEVOL'] = NULL;
                                    $rowAuditoria = $SosTbLsbkAuditoria->createRow($dataAuditoria);
                                    $rowAuditoria->save();

                                    //Cadastra o servico de backup
                                    $objServicoBackup->createRow($servicoBackupdata)->save();
                                } else {
                                    throw Exception('O backup selecionado ' . $dataPost ['LBKP_NR_TOMBO'] . ' não está mais disponível para uso!');
                                }
                            }
                        }

                        $db->commit();
                        $this->_helper->flashMessenger(array('message' => "$msgSucesso", 'status' => 'success'));
                        return $this->_helper->_redirector($refAction, $refController, 'sosti');
                    } catch (Exception $e) {
                        //se der erro, mostra msg
                        $db->rollBack();
                        $msgErro .= " Erro: " . $e->getMessage();
                        $this->_helper->flashMessenger(array('message' => "$msgErro", 'status' => 'error'));
                        return $this->_helper->_redirector($refAction, $refController, 'sosti');
                    }
                } else {

                    $dadosServicos = '';
                    $dadosSoftwares = '';
                    $dadosHardwares = '';

                    /*
                     * Se o formulario for invalido na action save, retorna pra ca para
                     * fazer o populate do formulario, via Sessão
                     */
                    if (!empty($checklistNs)) {
                        if (!$form->isValid($checklistNs->dados)) {

                            $dataSolicitacao = $objSolicitacao->getSolicitacaoInfo($checklistNs->dados['DOCM_NR_DOCUMENTO']);
                            //HORA DE ENTRADA NO NIVEL
                            $dataentrada = $objSolicitacao->getdataEntradaNivel($checklistNs->dados['DOC_ID'], $nivel, 1005);
                            $dataSolicitacao[0]['MOFA_DH_FASE'] = $dataentrada[0]['MOFA_DH_FASE'];
                            $solicitacaohasTombo = $objSolicitacao->solicitacaohasTombo($checklistNs->dados['DOCM_NR_DOCUMENTO']);
                            //POPULA OS DADOS DA SOLICITAÇÃO
                            $this->view->solicitacaoNr = $dataSolicitacao [0]['DOCM_NR_DOCUMENTO'];
                            $this->view->observacao = $dataSolicitacao [0]['SSOL_DS_OBSERVACAO'];
                            $this->view->localizacao = $dataSolicitacao [0]['SSOL_ED_LOCALIZACAO'];
                            $this->view->entradaNivel = $dataSolicitacao [0]['MOFA_DH_FASE'];
                            $this->view->tipoCadastro = $dataSolicitacao [0]['STCA_DS_TIPO_CAD'];
                            $this->view->lotacao = $dataSolicitacao [0]['DOCM_CD_LOTACAO_GERADORA'];
                            $this->view->siglalotacao = $dataSolicitacao [0]['DOCM_SG_SECAO_GERADORA'];
                            $this->view->telefoneExterno = $dataSolicitacao [0]['SSOL_NR_TELEFONE_EXTERNO'];
                            $this->view->emailExterno = $dataSolicitacao [0]['SSOL_DS_EMAIL_EXTERNO'];
                            $this->view->numeroTombo = $dataSolicitacao [0]['NU_TOMBO'];
                            $this->view->DescricaoMaterial = $dataFichaServico ['DE_MAT'];
                            $checklistNs->dados['TI_TOMBO'] = 'T';

                            //Busca dadosdos servicos
                            if (!empty($checklistNs->dados['servicos'])) {
                                foreach ($checklistNs->dados['servicos'] as $servico) {
                                    $dadosServicos[] = $objServico->getInfoServico($servico);
                                }
                            }

                            //Busca dados dos softwares
                            $contSoft = 0;
                            if (!empty($checklistNs->dados['softwares'])) {
                                foreach ($checklistNs->dados['softwares'] as $software) {
                                    $dadosSoftwares[$contSoft] = $objSoftware->getSoftwareInfo($software);
                                    $qtd_total_s = $objSoftware->getQtdTotalSoftware($software);
                                    $qtd_saida_s = $objSoftware->getQtdLicencasSaida($software);
                                    $dadosSoftwares[$contSoft]['qtd_soft_disponivel'] = (int) $qtd_total_s['QTD_TOTAL'] - (int) $qtd_saida_s['QTD_SAIDA'];
                                    $contSoft++;
                                }
                            }

                            //Busca dados dos hardwares
                            $contHard = 0;
                            if (!empty($checklistNs->dados['hardwares'])) {
                                foreach ($checklistNs->dados['hardwares'] as $hardware) {
                                    $dadosHardwares[$contHard] = $objHardware->getHardwareMarcaModelo($hardware);
                                    $qtd_total_h = $objHardware->getQtdTotalMaterial($hardware);
                                    $qtd_saida_h = $objHardware->getQtdMaterialSaida($hardware);
                                    $dadosHardwares[$contHard]['qtd_hard_disponivel'] = (int) $qtd_total_h['QTD_TOTAL'] - (int) $qtd_saida_h['QTD_SAIDA'];
                                    $contHard++;
                                }
                            }

                            //Popula os dados selecionados
                            $this->view->recuperaServicos = $dadosServicos;
                            $this->view->recuperaSoftwares = $dadosSoftwares;
                            $this->view->recuperaHardwares = $dadosHardwares;

                            //Popula os formulários
                            $form->populate($checklistNs->dados);
                            $this->view->formServico = $formServico;
                            $this->view->Software = $formSoftware;
                            $this->view->hardware = $formHardware;
                            $this->view->form = $form;
                        }
                    } else {
                        return $this->_helper->_redirector('index', 'index', 'sosti');
                    }
                }
            } else {
                //Se não for para salvar, monta o formulário
                $dataPost = Zend_Json::decode($dataPost ['solicitacao'] [0]);
                $userNs = new Zend_Session_Namespace('userNs');
                $siglaSecao = $userNs->siglasecao;
                $lotacao = $userNs->codsecsubseclotacao;
                Zend_Session::namespaceUnset("formChecklistSaveNs");

                //checa se o documento já possui ficha de serviço
                if (!$objtable->verificaexitenciaFicha($dataPost ['SSOL_ID_DOCUMENTO'])) {
                    $msg_to_user = "Este documento não possui um checklist!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    $this->_helper->_redirector($this->refAction, $this->refController, 'sosti');
                    return;
                }

                $dataFichaServico = $objtable->getFichaServico($dataPost ['SSOL_ID_DOCUMENTO']);
                $rowmodelos = $ocsModelo->getmodelosporMarca($dataFichaServico ['LFSE_CD_MARCA']);
                $dataSolicitacao = $objSolicitacao->getSolicitacaoInfo($dataPost ['DOCM_NR_DOCUMENTO']);

                //HORA DE ENTRADA NO NIVEL
                $dataentrada = $objSolicitacao->getdataEntradaNivel($dataPost ['SSOL_ID_DOCUMENTO'], $nivel, 1005);
                $dataSolicitacao[0]['MOFA_DH_FASE'] = $dataentrada[0]['MOFA_DH_FASE'];
                $solicitacaohasTombo = $objSolicitacao->solicitacaohasTombo($dataPost ['DOCM_NR_DOCUMENTO']);

                if (!$solicitacaohasTombo) {
                    $msg_to_user = "Não é possível realizar criação ou alteração de 
                    chacklist para a solicitação. Insira um número de tombo na 
                    troca de serviço e tente novamente.";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    $this->_helper->_redirector($this->refAction, $this->refController, 'sosti');
                    return;
                }

                //POPULA OS DADOS DA SOLICITAÇÃO
                $this->view->solicitacaoNr = $dataSolicitacao [0]['DOCM_NR_DOCUMENTO'];
                $this->view->observacao = $dataSolicitacao [0]['SSOL_DS_OBSERVACAO'];
                $this->view->localizacao = $dataSolicitacao [0]['SSOL_ED_LOCALIZACAO'];
                $this->view->entradaNivel = $dataSolicitacao [0]['MOFA_DH_FASE'];
                $this->view->tipoCadastro = $dataSolicitacao [0]['STCA_DS_TIPO_CAD'];
                $this->view->lotacao = $dataSolicitacao [0]['DOCM_CD_LOTACAO_GERADORA'];
                $this->view->siglalotacao = $dataSolicitacao [0]['DOCM_SG_SECAO_GERADORA'];
                $this->view->telefoneExterno = $dataSolicitacao [0]['SSOL_NR_TELEFONE_EXTERNO'];
                $this->view->emailExterno = $dataSolicitacao [0]['SSOL_DS_EMAIL_EXTERNO'];
                $this->view->numeroTombo = $dataSolicitacao [0]['NU_TOMBO'];
                $this->view->DescricaoMaterial = $dataFichaServico ['DE_MAT'];

                $dataFichaServico['LTPS_ID_TP_SOFTWARE'] = $dataSolicitacao[0]['LSFW_ID_TP_SOFTWARE'];
                $dataFichaServico['DOCM_NR_DOCUMENTO'] = $dataSolicitacao [0]['DOCM_NR_DOCUMENTO'];
                $dataFichaServico['SSOL_NR_TOMBO_PESQUISA'] = $dataSolicitacao [0]['NU_TOMBO'];
                $dataFichaServico['LFSE_IC_EXCLUSAO_ARQTEMP'] = ($dataFichaServico['LFSE_IC_EXCLUSAO_ARQTEMP'] == 'S') ? 1 : 0;
                $dataFichaServico['LFSE_IC_EXCLUSAO_PROFILE'] = ($dataFichaServico['LFSE_IC_EXCLUSAO_PROFILE'] == 'S') ? 1 : 0;
                $dataFichaServico['LFSE_IC_WINUPDATE'] = ($dataFichaServico['LFSE_IC_WINUPDATE'] == 'S') ? 1 : 0;
                $dataFichaServico['LFSE_IC_DESFRAGMENTACAO'] = ($dataFichaServico['LFSE_IC_DESFRAGMENTACAO'] == 'S') ? 1 : 0;
                $dataFichaServico['LFSE_IC_MANUTENCAO_EXTERNA'] = ($dataFichaServico['LFSE_IC_MANUTENCAO_EXTERNA'] == 'S') ? 1 : 0;
                $dataFichaServico['LFSE_IC_FORMATACAO'] = ($dataFichaServico['LFSE_IC_FORMATACAO'] == 'S') ? 1 : 0;
                $dataFichaServico['LFSE_IC_SCANDISK'] = ($dataFichaServico['LFSE_IC_SCANDISK'] == 'S') ? 1 : 0;
                $dataFichaServico['LFSE_IC_GARANTIA'] = ($dataFichaServico['LFSE_IC_GARANTIA'] == 'S') ? 1 : 0;
                $dataFichaServico['LFSE_IC_BACKUP'] = ($dataFichaServico['LFSE_IC_BACKUP'] == 'S') ? 1 : 0;
                //Valor fixo para tombo
                $dataFichaServico['TI_TOMBO'] = 'T';

                if (!empty($dataSolicitacao[0]['LSFW_ID_TP_SOFTWARE'])) {
                    $softwareLista = $objSoft->getSoftwareComboList($dataSolicitacao[0]['LSFW_ID_TP_SOFTWARE']);
                }

                //POPULA A COMBO MODELO DO BACKUP[LFSE_CD_MODELO]
                $cmbmodelo = $form->getElement('LFSE_CD_MODELO');
                foreach ($rowmodelos as $Modelo) {
                    $cmbmodelo->addMultiOptions(array($Modelo ["MODE_ID_MODELO"] => $Modelo ["MODE_DS_MODELO"]));
                }

                //POPULA A COMBO SOFTWARE[LFSW_ID_SOFTWARE]
                $cmbsoftware = $form->getElement('LFSW_ID_SOFTWARE');
                foreach ($softwareLista as $softwareNome) {
                    $cmbsoftware->addMultiOptions(array($softwareNome ["LSFW_ID_SOFTWARE"] => $softwareNome ["LSFW_DS_SOFTWARE"]));
                }

                //Buscando backup, se houver
                $dadosBackup = $objServicoBackup->gettomboBackupPeloIDDocumento($dataPost['SSOL_ID_DOCUMENTO']);
                if (count($dadosBackup) > 0) {
                    $dataFichaServico['LBKP_NR_TOMBO_PESQUISA'] = $dadosBackup['LSBK_NR_TOMBO'];
                    $dataFichaServico['LBKP_NR_TOMBO'] = $dadosBackup['LSBK_NR_TOMBO'];
                }

                //Buscar dados da ficha de serviço
                $dadosServicos = $objServico->getTpServicoPorDocumento($dataPost['SSOL_ID_DOCUMENTO']);
                if (count($dadosServicos) == 0) {
                    $dadosServicos = '';
                }
                $dadosSoftwares = $objSoftware->getSoftwaresPorDocumento($dataPost['SSOL_ID_DOCUMENTO']);
                if (count($dadosSoftwares) == 0) {
                    $dadosSoftwares = '';
                } else {
                    $flag_s = 0;
                    foreach ($dadosSoftwares as $s) {
                        $qtd_total_s = $objSoftware->getQtdTotalSoftware($s['LSFW_ID_SOFTWARE']);
                        $qtd_saida_s = $objSoftware->getQtdLicencasSaida($s['LSFW_ID_SOFTWARE']);
                        $qtd_s = (int) $qtd_total_s['QTD_TOTAL'] - (int) $qtd_saida_s['QTD_SAIDA'];
                        $dadosSoftwares[$flag_s]['SOFTWARE_DISPONIVEL'] = $qtd_s;
                        $flag_s++;
                    }
                }
                $dadosHardwares = $objHardware->getMaterialAlmoxPorDocumento($dataPost['SSOL_ID_DOCUMENTO']);
                if (count($dadosHardwares) == 0) {
                    $dadosHardwares = '';
                } else {
                    $flag_h = 0;
                    foreach ($dadosHardwares as $h) {
                        $qtd_total_h = $objHardware->getQtdTotalMaterial($h['LHDW_ID_HARDWARE']);
                        $qtd_saida_h = $objHardware->getQtdMaterialSaida($h['LHDW_ID_HARDWARE']);
                        $qtd_h = (int) $qtd_total_h['QTD_TOTAL'] - (int) $qtd_saida_h['QTD_SAIDA'];
                        $dadosHardwares[$flag_h]['qtd_hard_disponivel'] = $qtd_h;
                        $flag_h++;
                    }
                }
                //Popula os dados selecionados
                $this->view->recuperaServicos = $dadosServicos;
                $this->view->recuperaSoftwares = $dadosSoftwares;
                $this->view->recuperaHardwares = $dadosHardwares;

                $form->getElement('DOC_ID')->setValue($dataPost ['SSOL_ID_DOCUMENTO']);
                $form->populate($dataSolicitacao[0]); // popula form com dados da solicitição
                $form->populate($dataFichaServico); // popula form com dados da ficha de serviço
                $form->getElement('LFSE_ID_DOCUMENTO')->setValue($dataPost['DOCM_NR_DOCUMENTO']);
                $this->view->form = $form;
            }
        } else {
            return $this->_helper->_redirector($refAction, $refController, 'sosti');
        }
    }

    public function formchecklistsaveAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $userNs = new Zend_Session_Namespace('userNs');
        $checklistNs = new Zend_Session_Namespace('formChecklistSaveNs');
        $form = new Sosti_Form_LabCheckList ();
        $objFichaServico = new Application_Model_DbTable_LfsefichaServico ();
        $objFichaSoftware = new Application_Model_DbTable_SosTbLfswFichaSoftware ();
        $objFichaHardware = new Application_Model_DbTable_SosTbLfhwFichaHardware();
        $sys = new Application_Model_DbTable_Dual ();
        $objServicoBackup = new Application_Model_DbTable_SosTbLsbkServicoBackup ();
        $servicoFichaObj = new Application_Model_DbTable_SosTbTpsfTipoServico ();
        $objHardwareSaida = new Application_Model_DbTable_SosTbMtsaMaterialSaida();
        $objHardware = new Application_Model_DbTable_SosTbLhdwMaterialAlmox();
        $objSoftware = new Application_Model_DbTable_SosTbLsfwSoftware();
        $objSaidaSoftware = new Application_Model_DbTable_SosTbLssaLicencaSoftSaida();

        if ($this->getRequest()->isPost()) {
            $dataPost = $this->getRequest()->getPost();
            $checklistNs->dados = $dataPost;

            //capturando a tela de destino
            $refAction = $dataPost['acao'];
            $refController = $dataPost['controller'];

            //Se toda informação do form for válida prossiga com  a atualização do checklist
            if ($form->isValid($dataPost)) {

                //Tratamento da ficha de serviço e todas as suas inclusões
                $msgErro = "Não foi possível cadastrar Checklist! ";
                $msgSucesso = "Checklist criada com sucesso.";

                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                try {
                    $db->beginTransaction();
                    //Dados da ficha de serviço                     
                    $datafichaServico ['LFSE_ID_DOCUMENTO'] = $dataPost ['DOC_ID'];
                    $datafichaServico ['LFSE_NR_TOMBO'] = $dataPost ['SSOL_NR_TOMBO'];
                    $datafichaServico ['LFSE_TI_TOMBO'] = 'T';
                    $datafichaServico ['LFSE_NO_COMPUTADOR'] = $dataPost ['LFSE_NO_COMPUTADOR'];
                    $datafichaServico ['LFSE_ID_TP_USUARIO'] = $dataPost ['LFSE_ID_TP_USUARIO'];
                    $datafichaServico ['LFSE_DS_SERVICO_EXECUTADO'] = $dataPost ['LFSE_DS_SERVICO_EXECUTADO'];
                    $datafichaServico ['LFSE_DS_MOTIVO_MANUTENCAO'] = $dataPost ['LFSE_DS_MOTIVO_MANUTENCAO'];
                    $datafichaServico ['LFSE_IC_EXCLUSAO_ARQTEMP'] = ($dataPost ['LFSE_IC_EXCLUSAO_ARQTEMP'] == 1) ? 'S' : 'N';
                    $datafichaServico ['LFSE_IC_EXCLUSAO_PROFILE'] = ($dataPost ['LFSE_IC_EXCLUSAO_PROFILE'] == 1) ? 'S' : 'N';
                    $datafichaServico ['LFSE_IC_WINUPDATE'] = ($dataPost ['LFSE_IC_WINUPDATE'] == 1) ? 'S' : 'N';
                    $datafichaServico ['LFSE_IC_DESFRAGMENTACAO'] = ($dataPost ['LFSE_IC_DESFRAGMENTACAO'] == 1) ? 'S' : 'N';
                    $datafichaServico ['LFSE_IC_MANUTENCAO_EXTERNA'] = ($dataPost ['LFSE_IC_MANUTENCAO_EXTERNA'] == 1) ? 'S' : 'N';
                    $datafichaServico ['LFSE_IC_FORMATACAO'] = ($dataPost ['LFSE_IC_FORMATACAO'] == 1) ? 'S' : 'N';
                    $datafichaServico ['LFSE_IC_SCANDISK'] = ($dataPost ['LFSE_IC_SCANDISK'] == 1) ? 'S' : 'N';
                    $datafichaServico ['LFSE_IC_GARANTIA'] = ($dataPost ['LFSE_IC_GARANTIA'] == 1) ? 'S' : 'N';
                    $datafichaServico ['LFSE_DS_MOTIVO_MANUTENCAO'] = $dataPost ['LFSE_DS_MOTIVO_MANUTENCAO'];
                    $datafichaServico ['LFSE_IC_BACKUP'] = ($dataPost ['LFSE_IC_BACKUP'] == 1) ? 'S' : 'N';
                    $datahora = $dataPost ['MOFA_DH_FASE'];
                    $datafichaServico ['LFSE_DT_ENTRADA'] = new Zend_Db_Expr("TO_DATE('$datahora','dd/mm/yyyy HH24:MI:SS')");

                    //Cadastro da ficha de Serviço
                    $objFichaServico->createRow($datafichaServico)->save();

                    //Auditoria
                    $SosTbLfseAuditoria = new Application_Model_DbTable_SosTbLfseAuditoria();
                    $dataAuditoriaLfse['LFSE_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                    $dataAuditoriaLfse['LFSE_IC_OPERACAO'] = 'I';
                    $dataAuditoriaLfse['LFSE_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                    $dataAuditoriaLfse['LFSE_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                    $dataAuditoriaLfse['LFSE_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                    $dataAuditoriaLfse['OLD_LFSE_ID_DOCUMENTO'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_ID_DOCUMENTO'] = $datafichaServico ['LFSE_ID_DOCUMENTO'];
                    $dataAuditoriaLfse['OLD_LFSE_ID_TP_USUARIO'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_ID_TP_USUARIO'] = $datafichaServico ['LFSE_ID_TP_USUARIO'];
                    $dataAuditoriaLfse['OLD_LFSE_CD_MODELO'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_CD_MODELO'] = NULL;
                    $dataAuditoriaLfse['OLD_LFSE_CD_MARCA'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_CD_MARCA'] = NULL;
                    $dataAuditoriaLfse['OLD_LFSE_DS_SERVICO_EXECUTADO'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_DS_SERVICO_EXECUTADO'] = $datafichaServico ['LFSE_DS_SERVICO_EXECUTADO'];
                    $dataAuditoriaLfse['OLD_LFSE_DS_MOTIVO_MANUTENCAO'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_DS_MOTIVO_MANUTENCAO'] = $datafichaServico ['LFSE_DS_MOTIVO_MANUTENCAO'];
                    $dataAuditoriaLfse['OLD_LFSE_DT_ENTRADA'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_DT_ENTRADA'] = $datafichaServico ['LFSE_DT_ENTRADA'];
                    $dataAuditoriaLfse['OLD_LFSE_DT_SAIDA'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_DT_SAIDA'] = NULL;
                    $dataAuditoriaLfse['OLD_LFSE_IC_BACKUP'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_BACKUP'] = $datafichaServico ['LFSE_IC_BACKUP'];
                    $dataAuditoriaLfse['OLD_LFSE_IC_FORMATACAO'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_FORMATACAO'] = $datafichaServico ['LFSE_IC_FORMATACAO'];
                    $dataAuditoriaLfse['OLD_LFSE_IC_EXCLUSAO_ARQTEMP'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_EXCLUSAO_ARQTEMP'] = $datafichaServico ['LFSE_IC_EXCLUSAO_ARQTEMP'];
                    $dataAuditoriaLfse['OLD_LFSE_IC_EXCLUSAO_PROFILE'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_EXCLUSAO_PROFILE'] = $datafichaServico ['LFSE_IC_EXCLUSAO_PROFILE'];
                    $dataAuditoriaLfse['OLD_LFSE_IC_WINUPDATE'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_WINUPDATE'] = $datafichaServico ['LFSE_IC_WINUPDATE'];
                    $dataAuditoriaLfse['OLD_LFSE_IC_DESFRAGMENTACAO'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_DESFRAGMENTACAO'] = $datafichaServico ['LFSE_IC_DESFRAGMENTACAO'];
                    $dataAuditoriaLfse['OLD_LFSE_IC_SCANDISK'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_SCANDISK'] = $datafichaServico ['LFSE_IC_SCANDISK'];
                    $dataAuditoriaLfse['OLD_LFSE_IC_MANUTENCAO_EXTERNA'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_MANUTENCAO_EXTERNA'] = $datafichaServico ['LFSE_IC_MANUTENCAO_EXTERNA'];
                    $dataAuditoriaLfse['OLD_LFSE_IC_GARANTIA'] = NULL;
                    $dataAuditoriaLfse['NEW_LFSE_IC_GARANTIA'] = $datafichaServico ['LFSE_IC_GARANTIA'];
                    $rowAuditoriaLfse = $SosTbLfseAuditoria->createRow($dataAuditoriaLfse);
                    $rowAuditoriaLfse->save();

                    //Tratamento dos hardwares 
                    if (!empty($dataPost['hardwares'])) {
                        foreach ($dataPost['hardwares'] as $hardware) {

                            //Verificar se o hardware solicitado ainda possui a quantidade
                            //receber aqui o valor do banco
                            $qtd_total_h = $objHardware->getQtdTotalMaterial($hardware);
                            $qtd_saida_h = $objHardware->getQtdMaterialSaida($hardware);
                            $qtd_h = (int) $qtd_total_h['QTD_TOTAL'] - (int) $qtd_saida_h['QTD_SAIDA'];

                            if ($dataPost['qtdHardware'][$hardware] > $qtd_h) {
                                $hardwareErro = $objHardware->find('where LHDW_ID_HARDWARE = ' . $hardware);
                                throw new Exception('O hardware ' . $hardwareErro['LHDW_DS_HARDWARE'] . ' não possui mais a quantidade solicitada!');
                            } else {
                                $dataFichaHardware['LFHW_ID_HARDWARE'] = $hardware;
                                $dataFichaHardware['LFHW_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                $dataFichaHardware['LFHW_QT_MATERIAL_ALMOX'] = $dataPost['qtdHardware'][$hardware];

                                //Cria ficha de hardware
                                $objFichaHardware->createRow($dataFichaHardware)->save();

                                //Trata a saida de equipamento
                                $saidaEquipamento['MTSA_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                $saidaEquipamento['MTSA_ID_HARDWARE'] = $hardware;
                                $saidaEquipamento['MTSA_QT_SAIDA_MATERIAL'] = 0;
                                $saidaEquipamento['MTSA_DT_SAIDA_MATERIAL'] = App_Util::getTimeStamp_Audit();
                                $saidaEquipamento['MTSA_CD_MATRICULA'] = $userNs->matricula;
                                $saidaEquipamento['MTSA_SG_SECAO'] = $userNs->siglasecao;
                                $saidaEquipamento['MTSA_CD_LOTACAO'] = $userNs->codsecsubseclotacao;
                                $saidaEquipamento['MTSA_IC_APROVACAO'] = 'S';
                                $saidaEquipamento['MTSA_QT_SOLIC_SAIDA_MATERIAL'] = $dataPost['qtdHardware'][$hardware];

                                //Cria a saida do material
                                $objHardwareSaida->createRow($saidaEquipamento)->save();

                                //Auditoria
                                $SosTbMtsaAuditoria = new Application_Model_DbTable_SosTbMtsaAuditoria();
                                $dataAuditoriaNovoMtsa['MTSA_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                $dataAuditoriaNovoMtsa['MTSA_IC_OPERACAO'] = 'I';
                                $dataAuditoriaNovoMtsa['MTSA_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                $dataAuditoriaNovoMtsa['MTSA_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                $dataAuditoriaNovoMtsa['MTSA_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                $dataAuditoriaNovoMtsa['OLD_MTSA_ID_DOCUMENTO'] = NULL;
                                $dataAuditoriaNovoMtsa['NEW_MTSA_ID_DOCUMENTO'] = $saidaEquipamento['MTSA_ID_DOCUMENTO'];
                                $dataAuditoriaNovoMtsa['OLD_MTSA_ID_HARDWARE'] = NULL;
                                $dataAuditoriaNovoMtsa['NEW_MTSA_ID_HARDWARE'] = $saidaEquipamento['MTSA_ID_HARDWARE'];
                                $dataAuditoriaNovoMtsa['OLD_MTSA_DT_SAIDA_MATERIAL'] = NULL;
                                $dataAuditoriaNovoMtsa['NEW_MTSA_DT_SAIDA_MATERIAL'] = $saidaEquipamento['MTSA_DT_SAIDA_MATERIAL'];
                                $dataAuditoriaNovoMtsa['OLD_MTSA_QT_SAIDA_MATERIAL'] = NULL;
                                $dataAuditoriaNovoMtsa['NEW_MTSA_QT_SAIDA_MATERIAL'] = $saidaEquipamento['MTSA_QT_SOLIC_SAIDA_MATERIAL'];
                                $dataAuditoriaNovoMtsa['OLD_MTSA_CD_MATRICULA'] = NULL;
                                $dataAuditoriaNovoMtsa['NEW_MTSA_CD_MATRICULA'] = $saidaEquipamento['MTSA_CD_MATRICULA'];
                                $dataAuditoriaNovoMtsa['OLD_MTSA_SG_SECAO'] = NULL;
                                $dataAuditoriaNovoMtsa['NEW_MTSA_SG_SECAO'] = $saidaEquipamento['MTSA_SG_SECAO'];
                                $dataAuditoriaNovoMtsa['OLD_MTSA_CD_LOTACAO'] = NULL;
                                $dataAuditoriaNovoMtsa['NEW_MTSA_CD_LOTACAO'] = $saidaEquipamento['MTSA_CD_LOTACAO'];
                                $rowAuditoriaNovoMtsa = $SosTbMtsaAuditoria->createRow($dataAuditoriaNovoMtsa);
                                $rowAuditoriaNovoMtsa->save();
                            }
                        }
                    }


                    //Tratamento do software 
                    if (!empty($dataPost['softwares'])) {

                        foreach ($dataPost['softwares'] as $software) {

                            //Verificar se o software solicitado ainda possui a quantidade
                            //receber aqui o valor do banco
                            $qtd_total_s = $objSoftware->getQtdTotalSoftware($software);
                            $qtd_saida_s = $objSoftware->getQtdLicencasSaida($software);
                            $qtd_s = (int) $qtd_total_s['QTD_TOTAL'] - (int) $qtd_saida_s['QTD_SAIDA'];

                            if ($qtd_s == 0) {
                                $softwareErro = $objSoftware->find('WHERE LSFW_ID_SOFTWARE = ' . $software);
                                throw Exception('O software ' . $softwareErro['LSFW_DS_SOFTWARE'] . ' não possui mais a quantidade solicitada!');
                            } else {

                                //Dados da ficha de software
                                $dataFichaSoftware['LFSW_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                $dataFichaSoftware['LFSW_ID_SOFTWARE'] = $software;

                                //Cria a ficha de software
                                $objFichaSoftware->createRow($dataFichaSoftware)->save();

                                //Auditoria
                                $SosTbLfswAuditoria = new Application_Model_DbTable_SosTbLfswAuditoria();
                                $dataAuditoriaExcLfsw['LFSW_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                                $dataAuditoriaExcLfsw['LFSW_IC_OPERACAO'] = 'I';
                                $dataAuditoriaExcLfsw['LFSW_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                                $dataAuditoriaExcLfsw['LFSW_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                                $dataAuditoriaExcLfsw['LFSW_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                                $dataAuditoriaExcLfsw['OLD_LFSW_ID_DOCUMENTO'] = NULL;
                                $dataAuditoriaExcLfsw['NEW_LFSW_ID_DOCUMENTO'] = $dataFichaSoftware['LFSW_ID_DOCUMENTO'];
                                $dataAuditoriaExcLfsw['OLD_LFSW_ID_SOFTWARE'] = NULL;
                                $dataAuditoriaExcLfsw['NEW_LFSW_ID_SOFTWARE'] = $dataFichaSoftware['LFSW_ID_SOFTWARE'];
                                $rowAuditoriaExcLfsw = $SosTbLfswAuditoria->createRow($dataAuditoriaExcLfsw);
                                $rowAuditoriaExcLfsw->save();

                                //Dados da saída de software
                                $dataSaidaSoftware['LSSA_ID_SOFTWARE'] = $software;
                                $dataSaidaSoftware['LSSA_ID_SOFTWARE'] = $software;
                                $dataSaidaSoftware['LSSA_ID_DOCUMENTO'] = $dataPost['DOC_ID'];
                                $dataSaidaSoftware['LSSA_CD_MATRICULA'] = $userNs->matricula;
                                $dataSaidaSoftware['LSSA_DT_SAIDA'] = $sys->sysdate();
                                $dataSaidaSoftware['LSSA_IC_APROVACAO'] = 'S';

                                //Cadastra a saida do software
                                $objSaidaSoftware->createRow($dataSaidaSoftware)->save();
                            }
                        }
                    }


                    //Tratamento dos serviços 
                    if (!empty($dataPost['servicos'])) {
                        foreach ($dataPost['servicos'] as $servicos) {

                            //Dados da ficha de servico
                            $dataFichaServico['TPSF_ID_TP_SERVICO'] = $servicos;
                            $dataFichaServico['TPSF_ID_DOCUMENTO'] = $dataPost['DOC_ID'];

                            //Cadastra a ficha de servico
                            $servicoFichaObj->createRow($dataFichaServico)->save();
                        }
                    }


                    //Tratamento do Tombo
                    $servicoBackupdata ['LSBK_ID_DOCUMENTO'] = $dataPost ['DOC_ID'];
                    $servicoBackupdata ['LSBK_NR_TOMBO'] = $dataPost ['LBKP_NR_TOMBO'];
                    $servicoBackupdata ['LSBK_TP_TOMBO'] = 'T';
                    $servicoBackupdata ['LSBK_CD_MAT_EMPRESTIMO'] = $userNs->matricula;
                    $servicoBackupdata ['LSBK_DT_EMPRESTIMO'] = $sys->sysdate();

                    if (!empty($dataPost ['LBKP_NR_TOMBO'])) {

                        //Verifica a disponibilidade do tombo
                        $disp = $objServicoBackup->getVerificaDisponibilidadeBackup($dataPost['LBKP_NR_TOMBO']);
                        //Se o backup ainda estiver disponivel, cadastrar
                        if (count($disp) == 0) {

                            //Auditoria
                            $SosTbLsbkAuditoria = new Application_Model_DbTable_SosTbLsbkAuditoria();
                            $dataAuditoria['LSBK_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                            $dataAuditoria['LSBK_IC_OPERACAO'] = 'I';
                            $dataAuditoria['LSBK_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                            $dataAuditoria['LSBK_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                            $dataAuditoria['LSBK_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                            $dataAuditoria['OLD_LSBK_ID_DOCUMENTO'] = NULL;
                            $dataAuditoria['NEW_LSBK_ID_DOCUMENTO'] = $servicoBackupdata['LSBK_ID_DOCUMENTO'];
                            $dataAuditoria['OLD_LSBK_NR_TOMBO'] = null;
                            $dataAuditoria['NEW_LSBK_NR_TOMBO'] = $servicoBackupdata['LSBK_NR_TOMBO'];
                            $dataAuditoria['OLD_LSBK_TP_TOMBO'] = null;
                            $dataAuditoria['NEW_LSBK_TP_TOMBO'] = $servicoBackupdata['LSBK_TP_TOMBO'];
                            $dataAuditoria['OLD_LSBK_DT_EMPRESTIMO'] = NULL;
                            $dataAuditoria['NEW_LSBK_DT_EMPRESTIMO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY')");
                            $dataAuditoria['OLD_LSBK_DT_REC_USUARIO'] = NULL;
                            $dataAuditoria['NEW_LSBK_DT_REC_USUARIO'] = NULL;
                            $dataAuditoria['OLD_SBK_DT_REC_DEVOL'] = NULL;
                            $dataAuditoria['NEW_SBK_DT_REC_DEVOL'] = NULL;
                            $dataAuditoria['OLD_LSBK_CD_MAT_EMPRESTIMO'] = NULL;
                            $dataAuditoria['NEW_LSBK_CD_MAT_EMPRESTIMO'] = $servicoBackupdata['LSBK_CD_MAT_EMPRESTIMO'];
                            $dataAuditoria['OLD_LSBK_CD_MAT_RECEB_USUARIO'] = NULL;
                            $dataAuditoria['NEW_LSBK_CD_MAT_RECEB_USUARIO'] = NULL;
                            $dataAuditoria['OLD_LSBK_CD_MAT_RECEB_DEVOL'] = NULL;
                            $dataAuditoria['NEW_LSBK_CD_MAT_RECEB_DEVOL'] = NULL;
                            $rowAuditoria = $SosTbLsbkAuditoria->createRow($dataAuditoria);
                            $rowAuditoria->save();

                            //Cadastra o servico de backup
                            $objServicoBackup->createRow($servicoBackupdata)->save();
                        } else {
                            throw Exception('O backup selecionado ' . $dataPost ['LBKP_NR_TOMBO'] . ' não está mais disponível para uso!');
                        }
                    }

                    $db->commit();
                    $this->_helper->flashMessenger(array('message' => "$msgSucesso", 'status' => 'success'));
                    return $this->_helper->_redirector($refAction, $refController, 'sosti');
                } catch (Exception $e) {
                    //se der erro, mostra msg
                    $db->rollBack();
                    $msgErro .= " Erro: " . $e->getMessage();
                    $this->_helper->flashMessenger(array('message' => "$msgErro", 'status' => 'error'));
                    return $this->_helper->_redirector($refAction, $refController, 'sosti');
                }
            } else {
                //mandar os dados de volta pra outra action
                if ($refAction == 'terceironivel') {
                    $nivel = 3;
                } else {
                    $nivel = 2;
                }
                return $this->_helper->_redirector('formchecklist', 'labhardware', 'sosti', array("nivel" => $nivel));
            }
        } else {
            //se nao houver post, envia para a index do modulo
            return $this->_helper->_redirector('index', 'index', 'sosti');
        }
    }

    public function salvaraprovacaochecklistAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $objHardwareSaida = new Application_Model_DbTable_SosTbMtsaMaterialSaida();
        $objSaidaSoftware = new Application_Model_DbTable_SosTbLssaLicencaSoftSaida();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $semAlteracao = true;
            try {
                if (isset($data['softwares'])) {
                    foreach ($data['softwares'] as $s) {
                        if (isset($data['s_ic_aprovacao-' . $s])) {
                            $semAlteracao = false;
                            $dataAlteracao_s['LSSA_IC_APROVACAO'] = $data['s_ic_aprovacao-' . $s];
                            $currentRow_s = $objSaidaSoftware->fetchRow('LSSA_ID_DOCUMENTO = ' . $data["DOC_ID"] . ' and LSSA_ID_SOFTWARE = ' . $s . '');
                            $currentRow_s->setFromArray($dataAlteracao_s)->save();
                        }
                    }
                }

                if (isset($data['hardwares'])) {
                    foreach ($data['hardwares'] as $h) {
                        if (isset($data['h_ic_aprovacao-' . $h])) {
                            $semAlteracao = false;
                            $dataAlteracao_h['MTSA_IC_APROVACAO'] = $data['h_ic_aprovacao-' . $h];
                            if ($data['h_ic_aprovacao-' . $h] == 'R') {
                                $dataAlteracao_h['MTSA_QT_SAIDA_MATERIAL'] = 0;
                            } else {
                                $dataAlteracao_h['MTSA_QT_SAIDA_MATERIAL'] = $data['qtd_aprovado-' . $h];
                            }
                            $where = 'MTSA_ID_DOCUMENTO = ' . $data["DOC_ID"] . ' and MTSA_ID_HARDWARE = ' . $h . '';
                            $objHardwareSaida->update($dataAlteracao_h, $where);
                        }
                    }
                }

                if ($semAlteracao) {
                    $msg = "Nenhum item foi avaliado.";
                    $status = 'notice';
                } else {
                    $msg = "Checklist avaliado com sucesso.";
                    $status = 'success';
                }
                $this->_helper->flashMessenger(array('message' => $msg, 'status' => $status));
                return $this->_helper->_redirector('index', 'gestaodedemandasdoatendimentoaosusuarios', 'sosti');
            } catch (Exception $e) {
                $this->_helper->flashMessenger(array('message' => "Erro ao avaliar checklist. Erro: " . $e->getMessage(), 'status' => 'error'));
                return $this->_helper->_redirector('index', 'gestaodedemandasdoatendimentoaosusuarios', 'sosti');
            }
        } else {
            return $this->_helper->_redirector('index', 'index', 'sosti');
        }
    }

    public function ajaxtiposoftwareAction() {

        $idtipo = Zend_Filter::FilterStatic($this->_getParam('id'), 'int');
        $objSoft = new Application_Model_DbTable_SosTbLtpsTipoSoftware ();
        $this->view->software = $objSoft->getTipoSoftware($idtipo);
    }

    /**
     * Retorna lista de números de tombos para inderir como backup...
     *  
     * @param integer tombo
     */
    public function ajaxtombobackupAction($tombo = null) {

        $tomboNr = Zend_Filter::FilterStatic($this->_getParam('term'), 'int');
        $documentoID = Zend_Filter::FilterStatic($this->_getParam('DocID'), 'int');
        $objTomboInfo = new Application_Model_DbTable_TomboTiCentral();
        $rows = $objTomboInfo->getTomboInfo($tomboNr);

        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows [$i] = array_change_key_case($rows [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($rows);
    }

    /**
     * Retorna lista de tombos para uso
     * 
     */
    public function ajaxgettombolistaAction() {

        $tomboNr = Zend_Filter::FilterStatic($this->_getParam('term'), 'int');
        $objTombo = new Application_Model_DbTable_TomboTiCentral();
        $rows = $objTombo->getTomboLista($tomboNr);

        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows [$i] = array_change_key_case($rows [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($rows);
    }

    /**
     * Retorna a Row do tombo com sua informação pra inserir na ficha de serviço.... 
     * ...
     */
    public function gettombobackupinfoAction() {
        $tomboNr = Zend_Filter::FilterStatic($this->_getParam('tombo'), 'int');
        $objTomboInfo = new Application_Model_DbTable_TomboTiCentral();
        $row = $objTomboInfo->getTomboBackup($tomboNr);
        $tomboDisponivel = $objTomboInfo->isTomboDisponivel($tomboNr);

        if ($row) {
            if (is_null($tomboDisponivel[0]['LSBK_DT_RECEBIMENTO_DEVOLUCAO'])) {
                $row[0]['TOMBO_DEVOLVIDO'] = 0; // tombo ainda esta em uso
            } else {
                $row[0]['TOMBO_DEVOLVIDO'] = 1; //tombo esta liberado para uso
            }
            $row[0]['DATA_EMPRESTIMO'] = $tomboDisponivel[0]['LSBK_DT_EMPRESTIMO'];
            $row[0]['EMPRESTADO_POR'] = $tomboDisponivel[0]['LSBK_CD_MAT_EMPRESTIMO'];
        }
        $this->_helper->json->sendJson($row);
    }

    /**
     * método para trocar o tombo pra ativo e nao ativo
     * 
     */
    public function edittombobackupAction() {


        $objModelBackup = new Application_Model_DbTable_SosTbLbkpBackup ();

        $form = new Sosti_Form_TomboBackupEdit();

        $tomboNr = Zend_Filter::filterStatic($this->_getParam('tomboNr'), 'int');
        $tomboTipo = $this->_getParam('tomboTp');

        $data = $objModelBackup->gettomboBackupDetail($tomboNr);
        $data[0]['LBKP_NR_TOMBO_AUX'] = $data[0]['ID_TOMBO_TI_CENTRAL'];

        $form->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->module . '/' . $this->controller . '/edittombobackupsave');
        $form->populate($data[0]);
        $this->view->title = "Editar Tombo Backup";
        $this->view->form = $form;
    }

    public function edittombobackupsaveAction() {

        //disabilita o layout e a view 
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $form = new Sosti_Form_TomboBackupEdit();
        $objRhCentral = new Application_Model_DbTable_RhCentralLotacao();
        $userNs = new Zend_Session_Namespace('userNs');

        $sys = new Application_Model_DbTable_Dual ();
        $objModelBackup = new Application_Model_DbTable_SosTbLbkpBackup ();
        $objtti = new Application_Model_DbTable_TomboTiCentral();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data['LBKP_IC_ATIVO'] = ($data['LBKP_IC_ATIVO'] == 1) ? 'S' : 'N';
        }

        //Setando campos não obrigatórios
        $form->NU_TOMBO->setRequired(false);
        $form->NU_TOMBO->setRequired(false);
        $form->TI_TOMBO->setRequired(false);
        $form->LBKP_CD_MATRICULA_CAD->setRequired(false);
        $form->LBKP_DH_CADASTRO->setRequired(false);
        $form->LOTA_SIGLA_SECAO->setRequired(false);
        $form->LOTA_COD_LOTACAO->setRequired(false);

        if ($form->isValid($data)) {
            try {
                //atualiza o  registro
                $currentRow = $objModelBackup->fetchRow(array('LBKP_ID_TOMBO_TI_CENTRAL=?' => $data['LBKP_NR_TOMBO_AUX']));
                $tti = $objtti->find($currentRow->LBKP_ID_TOMBO_TI_CENTRAL)->current();
                $dataBackup = $currentRow->toArray();
                $currentRow->setFromArray($data)->save();

                //Auditoria
                $SosTbLbkpAuditoria = new Application_Model_DbTable_SosTbLbkpAuditoria();
                $dataAuditoria['LBKP_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                $dataAuditoria['LBKP_IC_OPERACAO'] = 'A';
                $dataAuditoria['LBKP_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                $dataAuditoria['LBKP_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                $dataAuditoria['LBKP_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                $dataAuditoria['OLD_LBKP_NR_TOMBO'] = $dataBackup ['LBKP_NR_TOMBO'];
                $dataAuditoria['NEW_LBKP_NR_TOMBO'] = $dataBackup ['LBKP_NR_TOMBO'];
                $dataAuditoria['OLD_LBKP_SG_TOMBO'] = $dataBackup ['LBKP_SG_TOMBO'];
                $dataAuditoria['NEW_LBKP_SG_TOMBO'] = $dataBackup ['LBKP_SG_TOMBO'];
                $rowAuditoria = $SosTbLbkpAuditoria->createRow($dataAuditoria);
                $rowAuditoria->save();

                $message = $tti->NU_TOMBO;
                $this->_helper->flashMessenger(array('message' => "O Tombo:<strong> $message </strong>foi atualizado com sucesso.", 'status' => 'success'));
                return $this->_helper->_redirector('cadastrobackuplist', 'labhardware', 'sosti');
            } catch (Exception $e) {
                $this->_helper->flashMessenger(array('message' => "Ocorreu um erro.Tente novamente.Erro:" . $e->getMessage(), 'status' => 'notice'));
                return $this->_helper->_redirector('cadastrobackuplist', 'labhardware', 'sosti');
            }
        } else {
            $this->_helper->layout->enableLayout();
            $this->_helper->viewRenderer->setNoRender(false);
            $this->view->form = $form;
            $this->render('edittombobackup');
            $this->view->title = "Atualizar Tombo Atividade";
        }
    }

    public function devolucaobackuplistAction() {

        $objModelServicoBackup = new Application_Model_DbTable_SosTbLsbkServicoBackup();
        $this->view->title = "Devolução de Tombo Backup";
        $order_direction = $this->_getParam('direcao', 'ASC');
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        $order_column = $this->_getParam('ordem', 'LSBK_NR_TOMBO');
        $order = $order_column . ' ' . $order_direction;
        $rows = $objModelServicoBackup->getServicoBackupListDevolucao($order);
        $paginator = Zend_Paginator::factory($rows);
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage(50);
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function devolucaobackupeditAction() {

        $userNs = new Zend_Session_Namespace('userNs');
        $docID = Zend_Filter::FilterStatic($this->_getParam('DocID'), 'int'); // Número do documento
        $tomboNr = Zend_Filter::FilterStatic($this->_getParam('tomboNr'), 'int'); //Número do Tombo
        $objModeOcsMatricula = new Application_Model_DbTable_OcsTbPmatMatricula ();
        $objModelServicoBackupEdit = new Application_Model_DbTable_SosTbLsbkServicoBackup();
        $objModelServicoBackupSave = new Application_Model_DbTable_SosTbLsbkServicoBackup();
        $objModelSostbSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao ();
        $sosMaeqManutencao = new Application_Model_DbTable_SosTbMaeqManutencaoEqpto();


        $this->view->title = "Devolver Tombo Backup";
        $form = new Sosti_Form_DevolucaoTombo();
        $form->removeElement('DOCM_NR_DOCUMENTO');
        $form->removeElement('LSBK_DT_EMPRESTIMO');
        $form->removeElement('LSBK_DT_RECEBIMENTO_USUARIO');
        $form->removeElement('LSBK_CD_MAT_EMPRESTIMO');
        $SolicitacaoinfoData = $objModelSostbSolicitacao->getDadosSolicitacao($docID);

        $dataRow = $objModelServicoBackupEdit->fetchRow(array('LSBK_ID_DOCUMENTO=?' => $docID, 'LSBK_NR_TOMBO=?' => $tomboNr));
        $dodosMatriculaEmprestimo = $objModeOcsMatricula->getDadosMatriculaSolicitante($dataRow['LSBK_CD_MAT_EMPRESTIMO']);



        //INFORMAÇÃO SOBRE O TOMBO
        $this->view->DocmNrDocumento = $SolicitacaoinfoData['DOCM_NR_DOCUMENTO'];
        $this->view->TomboNr = $dataRow['LSBK_NR_TOMBO'];
        $this->view->DataEmprestimo = $dataRow['LSBK_DT_EMPRESTIMO'];
        $this->view->MatriculaEmprestimo = $dataRow['LSBK_CD_MAT_EMPRESTIMO'] . " - " . $dodosMatriculaEmprestimo[0]['PNAT_NO_PESSOA'];
        $this->view->TipoTombo = $dataRow['LSBK_TP_TOMBO'];
        $this->view->form = $form;

        $data = $dataRow->toArray();
        $data['DOCM_NR_DOCUMENTO'] = $SolicitacaoinfoData['DOCM_NR_DOCUMENTO'];
        $form->populate($data);
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //VALIDAÇÃO: SE O VALOR DA DATA DO RECEBIMENTO FOR == NULL VOILTE PRA TELA ANTERIOR    
            if ($form->isValid($data)) {

                $currentRow = $objModelServicoBackupSave->find(array('LSBK_ID_DOCUMENTO' => $data['LSBK_ID_DOCUMENTO']), array('LSBK_NR_TOMBO' => $data['LSBK_NR_TOMBO']), array('LSBK_TP_TOMBO' => $data['LSBK_TP_TOMBO'])
                        )->current();
                $verificaBackup = $currentRow->toArray();
                $date = new Zend_Date();
                $data['LSBK_CD_MAT_RECEB_DEVOLUCAO'] = $userNs->matricula;
                $message = $data['LSBK_NR_TOMBO'];
                try {

                    //Auditoria
                    $SosTbLsbkAuditoria = new Application_Model_DbTable_SosTbLsbkAuditoria();
                    $dataAuditoria['LSBK_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                    $dataAuditoria['LSBK_IC_OPERACAO'] = 'A';
                    $dataAuditoria['LSBK_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                    $dataAuditoria['LSBK_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                    $dataAuditoria['LSBK_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                    $dataAuditoria['OLD_LSBK_ID_DOCUMENTO'] = $verificaBackup['LSBK_ID_DOCUMENTO'];
                    $dataAuditoria['NEW_LSBK_ID_DOCUMENTO'] = $verificaBackup['LSBK_ID_DOCUMENTO'];
                    $dataAuditoria['OLD_LSBK_NR_TOMBO'] = $verificaBackup['LSBK_NR_TOMBO'];
                    $dataAuditoria['NEW_LSBK_NR_TOMBO'] = $verificaBackup['LSBK_NR_TOMBO'];
                    $dataAuditoria['OLD_LSBK_TP_TOMBO'] = $verificaBackup['LSBK_TP_TOMBO'];
                    $dataAuditoria['NEW_LSBK_TP_TOMBO'] = $verificaBackup['LSBK_TP_TOMBO'];
                    $dataAuditoria['OLD_LSBK_DT_EMPRESTIMO'] = $verificaBackup['LSBK_DT_EMPRESTIMO'];
                    $dataAuditoria['NEW_LSBK_DT_EMPRESTIMO'] = $verificaBackup['LSBK_DT_EMPRESTIMO'];
                    $dataAuditoria['OLD_LSBK_DT_REC_USUARIO'] = NULL;
                    $dataAuditoria['NEW_LSBK_DT_REC_USUARIO'] = NULL;
                    $dataAuditoria['OLD_SBK_DT_REC_DEVOL'] = NULL;
                    $dataAuditoria['NEW_SBK_DT_REC_DEVOL'] = NULL;
                    $dataAuditoria['OLD_LSBK_CD_MAT_EMPRESTIMO'] = $verificaBackup['LSBK_CD_MAT_EMPRESTIMO'];
                    $dataAuditoria['NEW_LSBK_CD_MAT_EMPRESTIMO'] = $verificaBackup['LSBK_CD_MAT_EMPRESTIMO'];
                    $dataAuditoria['OLD_LSBK_CD_MAT_RECEB_USUARIO'] = NULL;
                    $dataAuditoria['NEW_LSBK_CD_MAT_RECEB_USUARIO'] = $data['LSBK_CD_MAT_RECEB_DEVOLUCAO'];
                    $dataAuditoria['OLD_LSBK_CD_MAT_RECEB_DEVOL'] = NULL;
                    $dataAuditoria['NEW_LSBK_CD_MAT_RECEB_DEVOL'] = NULL;
                    $rowAuditoria = $SosTbLsbkAuditoria->createRow($dataAuditoria);
                    $rowAuditoria->save();

                    //Altera registro
                    $currentRow->setFromArray($data);
                    $currentRow->save();
                    $this->_helper->flashMessenger(array('message' => "O Tombo:<strong> $message </strong>foi atualizado e já está disponível para uso!", 'status' => 'success'));
                } catch (Zend_Exception $e) {
                    $this->_helper->flashMessenger(array('message' => "O Tombo:<strong> $message </strong>não foi devolvido! Informe uma data válida de devolução.", 'status' => 'notice'));
                }
                return $this->_helper->_redirector('devolucaobackuplist', 'labhardware', 'sosti');
            } else {
                $this->view->form = $form;
            }
        }
    }

    /**
     * 
     * Salva as informações do formulário da davolução do tombo Backup ...
     */
    public function devolucaobackupsaveAction() {
        $form = new Sosti_Form_DevolucaoTombo();
        $objModelServicoBackupSave = new Application_Model_DbTable_SosTbLsbkServicoBackup();
        //disabilita o layout e a view 
        //$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        //
    }

    function cadastrobackuplistAction() {

        $objBackup = new Application_Model_DbTable_SosTbLbkpBackup ();
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
        $this->view->title = "Cadastrar Tombo Backup";

        //paginator
//        Zend_Debug::dump($rows);die;
        $order_column = $this->_getParam('ordem', 'LBKP_ID_TOMBO_TI_CENTRAL');
        $order_direction = $this->_getParam('direcao', 'ASC');
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        $order = $order_column . ' ' . $order_direction;
        $rows = $objBackup->getbackupTomboList($order);
        $paginator = Zend_Paginator::factory($rows);
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        //
    }

    function cadastrobackupaddAction() {
        $form = new Sosti_Form_BackupCadastro();

        $this->view->title = "Cadastrar  Tombo Backup";
        $form->setAction('cadastrobackupsave');
        $this->view->form = $form;
    }

    function cadastrobackupsaveAction() {
        $form = new Sosti_Form_BackupCadastro ();
        $userNs = new Zend_Session_Namespace('userNs');
        $sys = new Application_Model_DbTable_Dual ();
        $rh_central_lotacao = new Application_Model_DbTable_RhCentralLotacao ();

        $objBackup = new Application_Model_DbTable_SosTbLbkpBackup ();
        $objTombo = new Application_Model_DbTable_TomboTiCentral();
        if ($this->getRequest()->isPost())
            $lotacao = $rh_central_lotacao->getSecSubsecPai($userNs->siglasecao, $userNs->codlotacao); {
            $data = $this->getRequest()->getPost();

            if ($form->isValid($data)) {
                //checa se o tombo existe na tabela de tombos e material
                $rowTomboExists = $objTombo->fetchRow(array('NU_TOMBO=?' => $data ['LBKP_NR_TOMBO'], 'TI_TOMBO=?' => 'T'));
                //checa se o tombo já foi inserido anteriormente
                if (is_null($rowTomboExists)) {
                    $this->_helper->flashMessenger(array('message' => "Cadastro não realizado, pois o Tombo não foi localizado na base de dados.", 'status' => 'notice'));
                    return $this->_helper->_redirector('cadastrobackupadd', 'labhardware', 'sosti');
                }
                $rowExists = $objBackup->fetchRow(array('LBKP_ID_TOMBO_TI_CENTRAL=?' => $rowTomboExists->ID_TOMBO_TI_CENTRAL, 'LBKP_IC_ATIVO=?' => 'S'));
                if (!is_null($rowExists)) {
                    $message = $data ['LBKP_NR_TOMBO'];
                    $this->_helper->flashMessenger(array('message' => "Cadastro não realizado, pois o Tombo já está cadastrado como backup.", 'status' => 'notice'));
                    return $this->_helper->_redirector('cadastrobackupadd', 'labhardware', 'sosti');
                }
                $transaction = Zend_Db_Table_Abstract::getDefaultAdapter()->beginTransaction();
                try{
                    $dataBackup ['LBKP_ID_TOMBO_TI_CENTRAL'] = $rowTomboExists->ID_TOMBO_TI_CENTRAL;
                    $dataBackup ['LBKP_IC_ATIVO'] = 'S';
                    $dataBackup ['LBKP_CD_MATRICULA_CAD'] = $userNs->matricula;
                    $dataBackup ['LBKP_DH_CADASTRO'] = $sys->sysdate();
//                    $dataBackup ['LBKP_SG_SECAO'] = $lotacao ['LOTA_SIGLA_SECAO'];
//                    $dataBackup ['LBKP_CD_LOTACAO'] = $lotacao ['LOTA_COD_LOTACAO'];
                    $objBackup->createRow($dataBackup)->save();

                    //Auditoria
                    $SosTbLbkpAuditoria = new Application_Model_DbTable_SosTbLbkpAuditoria();
                    $dataAuditoria['LBKP_TS_OPERACAO'] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                    $dataAuditoria['LBKP_IC_OPERACAO'] = 'I';
                    $dataAuditoria['LBKP_CD_MATRICULA_OPERACAO'] = $userNs->matricula;
                    $dataAuditoria['LBKP_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
                    $dataAuditoria['LBKP_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                    $dataAuditoria['OLD_LBKP_NR_TOMBO'] = NULL;
                    $dataAuditoria['NEW_LBKP_NR_TOMBO'] = $dataBackup ['LBKP_NR_TOMBO'];
                    $dataAuditoria['OLD_LBKP_SG_TOMBO'] = NULL;
                    $dataAuditoria['NEW_LBKP_SG_TOMBO'] = $dataBackup ['LBKP_SG_TOMBO'];
                    $rowAuditoria = $SosTbLbkpAuditoria->createRow($dataAuditoria);
                    $rowAuditoria->save();

                    $transaction->commit();

                    $message = $data ['LBKP_NR_TOMBO'];
                    $this->_helper->flashMessenger(array('message' => "O Tombo:<strong> $message </strong>foi cadastrado como backup!", 'status' => 'success'));
                    $this->_helper->_redirector('cadastrobackuplist', 'labhardware', 'sosti');
                }
                catch(Exception $e){
                    $transaction->rollBack();
//                    $tombo = $objTombo->fetchRow(array('LBKP_NR_TOMBO = ?', $message))->toArray();
//                    if(!empty($tombo)){
//                    }
//                    echo $e->getMessage();die;
                        $this->_helper->flashMessenger(array('message' => "O Tombo:<strong> $message </strong>já está cadastrado como backup!", 'status' => 'error'));
                    $this->_helper->_redirector('cadastrobackuplist', 'labhardware', 'sosti');
                }
            } else {
                $this->view->title = "Cadastrar Tombo Backup";
                $this->_helper->viewRenderer->setRender('cadastrobackupadd');
                $this->view->form = $form;
            }
        }
    }

    public function ajaxsubsecoesAction() {
        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $lotacao = Zend_Filter::FilterStatic($this->_getParam('lotacao'), 'int');
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao, $lotacao);
        $this->view->Lotacao_array = $Lotacao_array;
    }

    public function ajaxbuscaservicosAction() {
        $servicoModel = new Application_Model_DbTable_SosTbTpseTipoServico ();
        $k = $this->_getParam('term');
        $rows = $servicoModel->getServico($k);

        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows [$i] = array_change_key_case($rows [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($rows);
    }

    /**
     * Retorna lista de software para o campo AUTOCOMPLETE
     * 
     */
    public function ajaxbuscasoftwareAction() {
        $softwareModelObj = new Application_Model_DbTable_SosTbLsfwSoftware ();
        $k = $this->_getParam('term');
        $rows = $softwareModelObj->getsofwarelista($k);
        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows [$i] = array_change_key_case($rows [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($rows);
    }

    /**
     * Retorna informação do Software
     */
    public function ajaxbuscasoftwareinfoAction() {
        $id = $this->_getParam('id');
        $softwareModelObj = new Application_Model_DbTable_SosTbLsfwSoftware ();
        $objFichaSoftware = new Application_Model_DbTable_SosTbLfswFichaSoftware ();
        $licencaInfo = $objFichaSoftware->checaLicenca($id);
        $rows = $softwareModelObj->getSoftwareInfo($id);
        $rows[0]['licencaDisponivel'] = ((int) $licencaInfo[0]['LICENCAS_USADAS'] < (int) $rows[0]['LISW_QT_LICENCA']) ? "S" : "N";

        $fim = count($rows);
        for ($i = 0; $i < $fim; $i++) {
            $rows [$i] = array_change_key_case($rows [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($rows);
    }

    public function ajaxbuscahardwareinfoAction() {
        $id = $this->_getParam('id');
        $objMaterialentrada = new Application_Model_DbTable_SosTbMtenMaterialEntrada();
        $qtInfo = $objMaterialentrada->getQuantidadeHardwareDisponivel($id);

        $fim = count($qtInfo);
        for ($i = 0; $i < $fim; $i++) {
            $qtInfo [$i] = array_change_key_case($qtInfo [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($qtInfo);
    }

    public function ajaxchecahardwaredisponivelAction() {
        $id = $this->_getParam('id'); //ID DO HARDWARE

        $objMaterialentrada = new Application_Model_DbTable_SosTbMtenMaterialEntrada();
        $objhardwareFicha = new Application_Model_DbTable_SosTbLfhwFichaHardware();

        $qtInfo = $objMaterialentrada->getQuantidadeHardwareDisponivel($id);
        $info = $objhardwareFicha->getSomaHardwareSendoUsado($id);
        $SaldoInfo[0]['Disponivel'] = ((int) $info[0]['SALDO'] < (int) $qtInfo[0]['MTEN_QT_ENTRADA_MATERIAL']) ? "S" : "N";
        $SaldoInfo[0]['SaldoDisponivel'] = (int) $qtInfo[0]['MTEN_QT_ENTRADA_MATERIAL'] - (int) $info[0]['HARDWARE_SENDO_USADO'];
        $fim = count($SaldoInfo);
        for ($i = 0; $i < $fim; $i++) {
            $SaldoInfo [$i] = array_change_key_case($SaldoInfo [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($SaldoInfo);
    }

    public function ajaxquantidadehardwaredisponivelAction() {
        $id = $this->_getParam('id'); //ID DO HARDWARE
        $objFichaHardware = new Application_Model_DbTable_SosTbLfhwFichaHardware ();
        $objMaterialentrada = new Application_Model_DbTable_SosTbMtenMaterialEntrada();
        $data = $objMaterialentrada->getQuantidadeHardwareDisponivel($id);

        $HardwareSendoUsado = $objFichaHardware->getSomaHardwareSendoUsado($this->escape($data["LHDW_ID_HARDWARE"]));

        $disponivel = (int) $data[0]["MTEN_QT_ENTRADA_MATERIAL"] - (int) $HardwareSendoUsado[0]['HARDWARE_SENDO_USADO'];

        $fim = count($disponivel);
        for ($i = 0; $i < $fim; $i++) {
            $disponivel [$i] = array_change_key_case($disponivel [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($disponivel);
    }

    /*
     * Ajax retorna um valor, diferente da funcao acima que retorna um array
     * Talvez seja o caso de unificar as funções posteriormente
     */

    public function ajaxqtdhardwaredisponivelAction() {

        $objHardware = new Application_Model_DbTable_SosTbLhdwMaterialAlmox();
        $id = $this->_getParam('id');
        $qtd_saida = $objHardware->getQtdMaterialSaida($id);
        $qtd_total = $objHardware->getQtdTotalMaterial($id);
        $qtd = (int) $qtd_total['QTD_TOTAL'] - (int) $qtd_saida['QTD_SAIDA'];
        $this->_helper->json->sendJson(array('qtd' => $qtd));
    }

    public function ajaxqtdsoftwaredisponivelAction() {

        $objSoftware = new Application_Model_DbTable_SosTbLsfwSoftware();
        $id = $this->_getParam('id');
        $qtd_saida = $objSoftware->getQtdLicencasSaida($id);
        $qtd_total = $objSoftware->getQtdTotalSoftware($id);
        $qtd = (int) $qtd_total['QTD_TOTAL'] - (int) $qtd_saida['QTD_SAIDA'];
        $this->_helper->json->sendJson(array('qtd' => $qtd));
    }

    public function ajaxcdmaterialAction() {
        $cod = $this->_getParam('term', '');
        $tbMaterial = new Application_Model_DbTable_Material();
        $material = $tbMaterial->getCodMaterial($cod);

        $fim = count($material);
        for ($i = 0; $i < $fim; $i++) {
            $material [$i] = array_change_key_case($material [$i], CASE_LOWER);
        }

        $this->_helper->json->sendJson($material);
    }

    public function ajaxmaterialalmoxAction() {

        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $subsecao = Zend_Filter::FilterStatic($this->_getParam('subsecao'), 'alnum');
        $marca = Zend_Filter::FilterStatic($this->_getParam('marca'), 'alnum');
        $modelo = Zend_Filter::FilterStatic($this->_getParam('modelo'), 'alnum');

        $tbMaterial = new Application_Model_DbTable_SosTbLhdwMaterialAlmox();
        $material = $tbMaterial->getListaMaterialAmox($secao, $subsecao, $marca, $modelo);
        $this->view->dados = $material;
    }

    public function ajaxhardwareporsecaoAction() {

        $secao = Zend_Filter::FilterStatic($this->_getParam('secao'), 'alnum');
        $subsecao = Zend_Filter::FilterStatic($this->_getParam('subsecao'), 'alnum');

        $tbMaterial = new Application_Model_DbTable_SosTbLhdwMaterialAlmox();
        $material = $tbMaterial->getListaMaterialAmoxPorSecao($secao, $subsecao);
        $material_disponivel = array();
        //Retirando os hardwares que não possuem saldo disponivel
        $flag = 0;
        foreach ($material as $m) {
            $qtd_total = $tbMaterial->getQtdTotalMaterial($m['LHDW_ID_HARDWARE']);
            $qtd_saida = $tbMaterial->getQtdMaterialSaida($m['LHDW_ID_HARDWARE']);
            $qtd = $qtd_total['QTD_TOTAL'] - $qtd_saida['QTD_SAIDA'];
            if ($qtd > 0) {
                $material_disponivel[$flag]['LHDW_ID_HARDWARE'] = $m['LHDW_ID_HARDWARE'];
                $material_disponivel[$flag]['LHDW_DS_HARDWARE'] = $m['LHDW_DS_HARDWARE'];
                $material_disponivel[$flag]['LHDW_CD_MATERIAL'] = $m['LHDW_CD_MATERIAL'];
                $material_disponivel[$flag]['MARC_DS_MARCA'] = $m['MARC_DS_MARCA'];
            }
            $flag++;
        }
        $this->view->dados = $material_disponivel;
    }

    public function ajaxgetnumerotomboAction() {

        $nrTombo = $this->_getParam('term', '');
        $objTombo = new Application_Model_DbTable_TomboTiCentral();
        $tombo = $objTombo->getNumeroTombo($nrTombo);

        $fim = count($tombo);
        for ($i = 0; $i < $fim; $i++) {
            $tombo[$i] = array_change_key_case($tombo [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($tombo);
    }

    public function ajaxgetnumerotombobackupAction() {

        $nrTombo = $this->_getParam('term', '');
        $objTombo = new Application_Model_DbTable_SosTbLbkpBackup();
        $tombo = $objTombo->getNumeroTomboBackup($nrTombo);

        $fim = count($tombo);
        for ($i = 0; $i < $fim; $i++) {
            $tombo[$i] = array_change_key_case($tombo [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($tombo);
    }

}
