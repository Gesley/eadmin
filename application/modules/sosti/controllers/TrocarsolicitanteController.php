<?php

class Sosti_TrocarsolicitanteController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $ajaxContext = $this->_helper->getHelper('ajaxContext');

        $ajaxContext
            ->addActionContext('busca-ajax-secao-subsecao', 'json')
            ->initContext();
    }

    public function init()
    {
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }

    public function indexAction()
    {

        $flash = $this->_helper->getHelper('flashMessenger');
//        $flash->clearMessages();
        $form = new Sosti_Form_TrocarSolicitante();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if ($form->isValid($this->getRequest()->getPost())) {
                $sol = new Application_Model_DbTable_SosTbSsolSolicitacao();
                $sostis = $sol->getTodasSolicitacoes($post, null);
                if(empty($sostis)){
                    $flash->addMessage(array('message' => "Não foram encontrados registros para o solicitante informado. ", "status" => 'notice'));
                    $this->_helper->redirector('index');
                }
                $this->view->sostis = $sostis;
                $mat = explode(' - ', $post['DOCM_CD_MATRICULA_CADASTRO']);
                $this->view->matricula = $mat[0];
            } else {
                $form->populate($this->getRequest()->getPost());
            }
        }
    }

    public function novoSolicitanteAction()
    {
        $flash = $this->_helper->getHelper('flashMessenger');
        $form = new Sosti_Form_TrocarSolicitante();
        $sol = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $doc = new Application_Model_DbTable_SadTbDocmDocumento();
        $pmat = new Application_Model_DbTable_RhCentralLotacao();
        $fase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $params = $this->_getAllParams();
//        Zend_Debug::dump($sol->getTodasSolicitacoes($params, null));die;
        $form->reset();
        $lista = $this->_getParam('lista', null);

        $form->getElement('Listar')->setLabel('Trocar Solicitante')->removeDecorator('DtDdWrapper');
        $form->addElement('button', 'Voltar', array('attribs' => array('class' => 'ui-button')));
        $form->getElement('Voltar')->removeDecorator('DtDdWrapper');
        $form->addDisplayGroup(array('Listar', 'Voltar'), 'submitButtons', array(
            'decorators' => array(
                'FormElements',
                array('HtmlTag', array('tag' => 'div', 'class' => 'button-group')),
            ),
        ));
//        $params['DOCM_CD_MATRICULA_CADASTRO'] = $params['matricula'];
        $sostis = $this->_getParam('checkSostisSelecionados');
        if(empty($sostis)){
            $flash->addMessage(array('message' => 'É necessario escolher uma Solicitação', 'status' => 'notice'));
            $this->_helper->redirector('index');
        }
        $this->view->sostis = json_decode($sostis);
        $this->view->form = $form;
        $this->view->messages = $flash->getMessages();
        $flash->clearMessages();
        $this->view->jsonSostis = $sostis;

        if ($this->getRequest()->isPost() && empty($lista)) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $solicitanteTo = explode(' - ', $params['DOCM_CD_MATRICULA_CADASTRO']);
                $solicitanteFrom = $params['matricula'];
                $sostis = json_decode($this->_getParam('checkSostisSelecionados'));
//                Zend_Db_Table_Abstract::getDefaultAdapter()->closeConnection();
//                Zend_Debug::dump($solicitanteTo);die;
                try {
                    $transaction = Zend_Db_Table_Abstract::getDefaultAdapter()->beginTransaction();
                    foreach ($sostis as $sosti) {
//                        Zend_Debug::dump($solicitanteTo);
//                        Zend_Debug::dump($solicitanteFrom);die;

                        $doc->update(array('DOCM_CD_MATRICULA_CADASTRO' => $solicitanteTo[0]), array('DOCM_ID_DOCUMENTO = ?' => $sosti->SSOL_ID_DOCUMENTO));
                        $docData = $doc->find($sosti->SSOL_ID_DOCUMENTO)->current();
                        $dadosSolicitanteFrom = $pmat->getDadosPessoais($solicitanteFrom);
                        $sol->update(array(
                            'SSOL_DS_EMAIL_EXTERNO' => $dadosSolicitanteFrom['PEEM_ED_EMAIL'],
                            'SSOL_NR_TELEFONE_EXTERNO' => $dadosSolicitanteFrom['TELEFONE']
                        ), array('SSOL_ID_DOCUMENTO = ?' => $sosti->SSOL_ID_DOCUMENTO));
                        $dataMofaMoviFase["MOFA_DH_FASE"] = new Zend_Db_Expr("SYSDATE");
                        $dataMofaMoviFase["MOFA_ID_FASE"] = 1088;
                        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $docData->DOCM_ID_MOVIMENTACAO;
//                        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'teste'");
                        $namespace = new Zend_Session_Namespace('userNs');
                        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $namespace->matricula;
                        $idMoviMovimentacao = $fase->createRow($dataMofaMoviFase);
                        $idMoviMovimentacao = $idMoviMovimentacao->save();

                        $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                        $dataUltima_fase["DOCM_DH_FASE"] = new Zend_Db_Expr("SYSDATE");
                        $rowUltima_fase = $doc->find($sosti->SSOL_ID_DOCUMENTO)->current();

                        $rowUltima_fase->setFromArray($dataUltima_fase);
                        $rowUltima_fase->save();

                    }
                    $transaction->commit();
                    $flash->addMessage(array('message' => "O solicitante foi alterado para as solicitações selecionadas com sucesso", "status" => 'success'));
                    $this->_helper->redirector('index');
                } catch (Zend_Db_Table_Exception $e) {
                    $transaction->rollBack();
                    echo $e->getMessage();
                    die;
                }
            } else {
                $form->populate($this->getRequest()->getPost());
            }
        }
    }

    public function buscaAjaxSecaoSubsecaoAction()
    {
        $params = $this->_getAllParams();
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $Lotacao_array = $RhCentralLotacao->getSubSecoes($params['SESB_SIGLA_SECAO_SUBSECAO'], $params['LOTA_COD_LOTACAO']);
        $res = array();
        foreach ($Lotacao_array as $lotacao) {
            $res[] = array(
                'key' => $lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao['LOTA_COD_LOTACAO'] . '|' . $lotacao["LOTA_TIPO_LOTACAO"],
                'value' => $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"] . ' - ' . $lotacao["LOTA_LOTA_COD_LOTACAO_PAI"]
            );
        }

        $this->view->assign($res);
    }
}
