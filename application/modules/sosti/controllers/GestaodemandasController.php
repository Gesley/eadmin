<?php
/**
 * Realiza as gestões das demadas em várias caixas de atendimento
 */
class Sosti_GestaodemandasController extends Zend_Controller_Action
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
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }
    
    public function priorizardemandasAction()
    {
        $this->view->title = 'Priorizar Demandas';
        $form = new Sosti_Form_PesqGrupoServico();
        $this->view->form = $form;
    }
    
    public function priorizarlistAction()
    {
        $grupo = $this->_getParam('CXEN_ID_CAIXA_ENTRADA');
        $servico = $this->_getParam('SSER_ID_SERVICO');
        $idServico = explode('|', $servico);
        $arrayDataPesq = $idServico[0] != "null" ? 
            array("SSER_ID_SERVICO" => $idServico[0]) : array();
        $arrayDataFase = Sosti_Model_DataMapper_PriorizarDemanda::demadasPorServico(
            $grupo, $arrayDataPesq, 'PRDE_NR_PRIORIDADE ASC'
        );
        $this->view->assign(array(
            'qtdeData' => count($arrayDataFase),
            'data'     => $arrayDataFase,
            'grupo'    => $grupo,
            'servico'  => $servico
        ));
    }
    
    public function salvarpriorizacaoAction()
    {
        $formNs = new Zend_Session_Namespace('formNs');
        if ($this->getRequest()->isPost()) {
            $arrayPrioridade = $this->getRequest()->getPost();
            if ($formNs->envio != md5(implode('-', $arrayPrioridade['idDocumento']))) {
                $formNs->envio = md5(implode('-', $arrayPrioridade['idDocumento']));
                $result = Sosti_Model_DataMapper_PriorizarDemanda::salvarOrdem(
                    $arrayPrioridade['idDocumento']
                );
                if ($result === true) {
                    return $this->_helper->json->sendJson(array(
                        'message' => 'Priorização cadastrada com sucesso!', 'status' => 'success'
                    ));
                } else {
                    return $this->_helper->json->sendJson(array(
                        'message' => $result, 'status' => 'error'
                    ));
                }
            }
        }
    }
}