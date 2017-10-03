<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Sosti_LabrelatoriosController extends Zend_Controller_Action
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

    public function init() {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();
    }

    public function indexAction() {
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }

    /**
     * Relatórios dos backups que estão empresatados.
     * 
     */
    public function backupemprestadosAction() {
        //MODELS OBJETOS ::1
        $objModelServicoBackup = new Application_Model_DbTable_SosTbLsbkServicoBackup();
        //::1
        $this->view->title = "Backups Emprestados";
        //PAGINAÇÃO ::2
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');

        $order_column = $this->_getParam('ordem', 'LSBK_NR_TOMBO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        $order = $order_column . ' ' . $order_direction;
        //::2
        $rows = $objModelServicoBackup->getServicoBackupListDevolucao($order);

        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);

        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function solicitacoesportomboAction() {

        $Sos_servico_soliObj = new Application_Model_DbTable_SosTbSsesServicoSolic ();
        $SolicitacaoObj = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SolicitacoestomboNs = new Zend_Session_Namespace('SolicitacoestomboNs');
        $this->view->title = "Solicitações pelo Tombo";
        $form = new Sosti_Form_SolicManutencao ();
        $this->view->form = $form;
        $nr_tombo = $this->_getParam('NR_TOMBO');
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $SolicitacoestomboNs->tomboNr = $data['NR_TOMBO'];
            } else {//FORM NÃO É VALIDO
                $this->view->form = $form;
            }
            $form->populate($data);
        }
        if (!is_null($SolicitacoestomboNs->tomboNr)) {

            $userNs = new Zend_Session_Namespace('userNs');
//            Zend_Debug::dump($_SESSION['userNs']);die;

            if ($this->_getParam('NR_TOMBO') == "") {
                unset($SolicitacoestomboNs->tomboNr);
            } else {
                $data = $this->getRequest()->getPost();
                $this->view->nrtombo = $this->_getParam('NR_TOMBO');
                $nr_tombo = ($data['NR_TOMBO']) ? ($data['NR_TOMBO']) : ($this->_getParam('NR_TOMBO'));

                $order_column = $this->_getParam('ordem', 'SOLIC.SSES_NR_TOMBO');
                $order_direction = $this->_getParam('direcao', 'ASC');
                ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
                $order = $order_column . ' ' . $order_direction;
                $this->view->ordem = $order_column;
                $this->view->direcao = $order_direction;
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
                $rows = $Sos_servico_soliObj->getsolicitacoesporTombo($SolicitacoestomboNs->tomboNr, $order, $userNs->codsecsubsec);
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
            }
        }
    }

    public function historicoequipamentosAction() {

        $form = new Sosti_Form_SolicManutencao();
        $HistoricotomboNs = new Zend_Session_Namespace('HistoricotomboNs');
        $objModelServicoBackup = new Application_Model_DbTable_SosTbLsbkServicoBackup();
        $this->view->title = "Histórico dos Equipamentos de Empréstimo";
        $this->view->form = $form;
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
        $order_column = $this->_getParam('ordem', 'NU_TOMBO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        $order = $order_column . ' ' . $order_direction;
        $this->view->direcao = $order_direction;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $HistoricotomboNs->tomboNr = $data['NR_TOMBO'];
            } else {//FORM NÃO É VALIDO
                $this->view->form = $form;
            }
        }

        if (!is_null($HistoricotomboNs->tomboNr)) {
            if ($this->_getParam('NR_TOMBO') == "") {
                unset($HistoricotomboNs->tomboNr);
            } else {
                $tomboNr = $this->_getParam($data['NR_TOMBO'], $HistoricotomboNs->tomboNr);
                $this->view->tombo = $HistoricotomboNs->tomboNr;
                $rows = $objModelServicoBackup->getHitoricoEmprestimoEquipamento($HistoricotomboNs->tomboNr, $order);
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
            }
        }
    }

    public function relatoriosoftwareAction() {
        $this->view->title = "Relatório por Tipo de Software [Licenças]";
        $objTipoSoftware = new Application_Model_DbTable_SosTbLtpsTipoSoftware ();
        $rows = $objTipoSoftware->gettiposdeSoftware();
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itensperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itensperpage);
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function relatoriohardwareAction() {
        $this->view->title = "Relatório Detalhado de Movimentação de Peças";
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itemperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'LHDW_DS_HARDWARE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SosTbLhdwMaterialAlmox ();
        $rows = $dados->getHardwares($order);
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itemperpage);
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function relatorioresumidohardwareAction() {
        $this->view->title = "Relatório Resumido de Movimentação de Peças";
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $itemperpage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 50), 'int');
        /* Ordenação das paginas */
        $order_column = $this->_getParam('ordem', 'LHDW_DS_HARDWARE');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column . ' ' . $order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /* Ordenação */

        $dados = new Application_Model_DbTable_SosTbLhdwMaterialAlmox ();
        $rows = $dados->getHardwares($order);
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($itemperpage);
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

}
