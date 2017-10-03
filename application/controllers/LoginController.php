<?php

class LoginController extends Zend_Controller_Action 
{

    public function init()
    {
        $this->view->titleBrowser = "e-Admin";
        $initNs = new Zend_Session_Namespace('initNs');
        $initNs->url = $_SERVER["REQUEST_URI"];
    }

    public function indexAction()
    {
        $this->view->title = "Página de Login do Sistema e-Admin";
        $this->_helper->layout->setLayout('login');
        $form = new Form_Login ();
        $data = $this->_getAllParams();
        $this->view->form = $form;

        if ($this->_getParam('sessao') == 'expirada') {
            $this->view->message_sessao = "Sua sessão expirou, favor logar novamente";
        }

        try {
            if ($this->_request->isPost()) {
                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $userNs->bancoUsuario = $form->getValue('COU_NM_BANCO');

                    $authAdapter = new App_Auth_Adapter_Db ();
                    $authAdapter->setIdentity($form->getValue('COU_COD_MATRICULA'));
                    $authAdapter->setCredential($form->getValue('COU_COD_PASSWORD'));
                    $authAdapter->setDbName($form->getValue('COU_NM_BANCO'));
                    $uf = strtoupper(substr($form->getValue('COU_COD_MATRICULA'), 0, 2));
                    $auth = Zend_Auth::getInstance();

                    /*                     * ***********************************************************
                     * NOTA:
                     * Código abaixo alterado pelas próximas linhas a seguir,
                     * evitando assim a dupla chamada da função authenticate()
                     * 
                     * Anderson Sathler
                     * ************************************************************
                      $result = $auth->authenticate($authAdapter);
                      $messageLogin = $authAdapter->authenticate()->getMessages();
                     */

                    $result = $auth->authenticate($authAdapter);
                    $messageLogin = $result->getMessages();

                    if ($result->isValid()) {
                        $data = $authAdapter->getResultRowObject(null, 'COU_COD_PASSWORD');

                        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao ();
                        $dp = $RhCentralLotacao->getDadosPessoais(strtoupper($form->getValue('COU_COD_MATRICULA')));

                        $mapperDocumento = new Sisad_Model_DataMapper_Documento ();
                        $fone = $mapperDocumento->getUltimoTelefoneCadastrado(strtoupper($form->getValue('COU_COD_MATRICULA')));

                        $userNs->matricula = strtoupper($form->getValue('COU_COD_MATRICULA'));
                        $userNs->banco = $messageLogin [1];
                        $userNs->codSec = $messageLogin [2];
                        $userNs->perfil = 'usuario';
                        $userNs->uf = $uf;

                        $userNs->nome = $dp [0] ['PNAT_NO_PESSOA'];
                        $userNs->siglasecao = $dp [0] ["LOTA_SIGLA_SECAO"];
                        $userNs->codlotacao = $dp [0] ["LOTA_COD_LOTACAO"];
                        $userNs->siglalotacao = $dp [0] ["LOTA_SIGLA_LOTACAO"];
                        $userNs->descicaolotacao = $dp [0] ["LOTA_DSC_LOTACAO"];
                        $userNs->localizacao = $dp [0] ["LOCALIZACAO"];
                        $userNs->lota_dat_fim = $dp [0] ["LOTA_DAT_FIM"];
                        $userNs->telefone = $fone [0] ["SSOL_NR_TELEFONE_EXTERNO"];

                        $dsecao = $RhCentralLotacao->getSecSubsecPai($dp [0] ["LOTA_SIGLA_SECAO"], $dp [0] ["LOTA_COD_LOTACAO"]);
                        $userNs->codsecsubsec = $dsecao ["SESB_SESU_CD_SECSUBSEC"];
                        $userNs->codsecsubseclotacao = $dsecao ["LOTA_COD_LOTACAO"];
                        $userNs->codtipolotacao = $dsecao ["LOTA_TIPO_LOTACAO"];
                        $userNs->codlotacaopai = $dsecao ["LOTA_LOTA_COD_LOTACAO_PAI"];
                        $userNs->siglasecsubseclotacao = $dsecao ["LOTA_SIGLA_LOTACAO"];
                        $userNs->descrisaosecsubsec = $dsecao ["LOTA_DSC_LOTACAO"];
                        $userNs->enderecosecsubsec = $dsecao ["SESB_ENDERECO_SECAO_SUBSECAO"];
                        $userNs->bairrosecsubsec = $dsecao ["SESB_BAIRRO_SECAO_SUBSECAO"];
                        $userNs->ufsecsubsec = $dsecao ["SESB_UF"];
                        $userNs->municipiosecsubsec = $dsecao ["SESB_MUNICIPIO_SECAO_SUBSECAO"];
                        $userNs->cepsecsubsec = $dsecao ["SESB_CEP_SECAO_SUBSECAO"];
                        $userNs->email = strtolower($form->getValue('COU_COD_MATRICULA')) . '@trf1.jus.br';

                        /**
                         * Gravando na Namespace Zend_Auth o timeout da sessão
                         */
                        App_Controller_Plugin_Timeout::initTimeOutNamespace();

                        /*                         * ***********************************************************
                         * NOTA:
                         * A gravação de registro de log não é mais necessária
                         * pela gravação dos dados em OCS_TB_LOGT...
                         * 
                         * Retirada deste trecho de código autorizada pelo:
                         * Thiago Mota de Santana
                         * 
                         * Anderson Sathler
                         * ************************************************************
                          // Grava registro de log para cada acesso ao e-Admin
                          $logAcesso = new Trf1_Guardiao_Log ();
                          $logAcesso->gravaLog();
                         */

                        return $this->_helper->_redirector('carregapermissao', 'index', 'guardiao');
                    } else {
                        $this->view->message = $messageLogin [0];
                    }
                }
            }
        } catch (Exception $e) {
            $e = 'Logon Negado';
            $this->view->message = $e;
        }
    }

    public function successAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
        } else {
            $this->_redirect('/');
        }
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
        $this->_redirect('/login');
    }

    public function ajaxbancoAction()
    {
        $matricula = $this->_getParam('matricula');
        $banco = new Application_Model_DbTable_CoUserId ();
        $bancosArray = $banco->getNomeBanco($matricula);
        $this->view->bancosArray = $bancosArray;
    }

}
