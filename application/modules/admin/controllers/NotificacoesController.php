<?php

class Admin_NotificacoesController extends Zend_Controller_Action
{
	/**
	 * Timer para mensuracao do tempo de carregamento da pagina
	 *
	 * @var int $_temporizador
	 */
	private $_temporizador;
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da pagina
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
	public function init()
    {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
		$this->view->titleBrowser = "e-Admin";
    }
	
    public function minhasnotificacoesAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
        $notfNaoLidas = $tabelaNotf->getNotf($userNs->matricula);
        if ($notfNaoLidas) {
            foreach ($notfNaoLidas as $notf) {
                $datahora = $notf['MILI'];
                $matricula = $userNs->matricula;
                $update = $tabelaNotf->setnotflida($matricula, $datahora);
            }
        }
        $dadosNotf = $tabelaNotf->getnotfCaixa($userNs->matricula);
        $this->view->data = $dadosNotf;
        $this->view->title = 'Caixa de Notificações do Sistema e-Admin';
    }
    
    public function delnotificacoesAction()
    {
        try {
            $notfLida = $this->getRequest()->getParam('setdel');
            $exploded = explode(' - ', $notfLida);
            $matricula = $exploded[0];
            $datahora = str_replace('.', '/', $exploded[1]);
            
            $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
            $delete = $tabelaNotf->setdeletenotf($matricula,$datahora);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }
    
    public function setnotificacaolidaAction() 
    {
        try {
            $notfLida = $this->getRequest()->getParam('setwritten');
            $exploded = explode(' - ', $notfLida);
            $matricula = $exploded[0];
            $datahora = str_replace('.', '/', $exploded[1]);
            
            $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
            $update = $tabelaNotf->setnotflida($matricula, $datahora);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }
    
    public function setnotifAction($matricula,$sistema,$mensagem) 
    {
        try {
            $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
            $update = $tabelaNotf->setnotfAction($matricula, $sistema, $mensagem);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }
    
    public function setsolicitardocumentoAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $data = $this->getRequest()->getParam('data');
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $historicoDOCM = $mapperDocumento->getHistoricoDCMTO($data);
        if (is_null($historicoDOCM[0]["MODP_CD_MAT_PESSOA_DESTINO"])) {
            $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
            $OcsTbUnpeUnidadePerfil1 = new Application_Model_DbTable_OcsTbUnpeUnidadePerfil();
            $responsaveis = $OcsTbUnpeUnidadePerfil1->getPessoasComPerfilX(9, $historicoDOCM[0]['MODE_SG_SECAO_UNID_DESTINO'], $historicoDOCM[0]['MODE_CD_SECAO_UNID_DESTINO']);
            foreach ($responsaveis as $dadosresp) {
                $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
                $matricula = $dadosresp['PMAT_CD_MATRICULA'];
                $titulo = 'Solicitação de Documentos/Processos';
                $sistema = 'SISAD';
                $mensagem = 'Prezado(a) ' .
                        $dadosresp['PNAT_NO_PESSOA'] .
                        ', <br/><br/> Solicito o documento/processo Número ' .
                        $historicoDOCM[0]['DOCM_NR_DOCUMENTO'] .
                        ' do tipo ' .
                        strtoupper($historicoDOCM[0]['DTPD_NO_TIPO']) .
                        ' presente na Caixa da Unidade: ' .
                        $dadosresp['LOTA_SIGLA_LOTACAO'] . ' - ' . $dadosresp['LOTA_DSC_LOTACAO'] .
                        '<br/><br/>SOLICITANTE: <br/>' .
                        $userNs->matricula . ' - ' . $userNs->nome . '.' .
                        $userNs->siglalotacao . ' - ' . $userNs->descicaolotacao . ' - ' . $userNs->codlotacao;
                $notificacao = $tabelaNotf->setnotfAction($matricula, $titulo, $sistema, $mensagem);
            }
            if ($notificacao) {
                $msg_to_user = "Documento nº " . $historicoDOCM[0]['DOCM_NR_DOCUMENTO'] . " solicitado com sucesso à " . $historicoDOCM[0]['LOTA_DSC_LOTACAO_DESTINO'] . " - " . $historicoDOCM[0]['FAMILIA_DESTINO'] . ".";
                $msg_to_user = "<div class='success'><strong>Sucesso:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView = $msg_to_user;
            } else {
                $msg_to_user = "Não foi possível solicitar o documento/processo. Erro:" . $notificacao;
                $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView = $msg_to_user;
            }
        } else {
            $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
            $matricula = $historicoDOCM[0]['MODP_CD_MAT_PESSOA_DESTINO'];
            $titulo = 'Solicitação de Documentos/Processos';
            $sistema = 'SISAD';
            $mensagem = 'Prezado(a) ' .
                    $historicoDOCM[0]['RECEBEDOR'] .
                    ', <br/><br/> Solicito o documento/processo número ' .
                    $historicoDOCM[0]['DOCM_NR_DOCUMENTO'] .
                    ' do tipo ' .
                    strtoupper($historicoDOCM[0]['DTPD_NO_TIPO']) .
                    ' presente na sua Caixa de Entrada Pessoal.' .
                    '<br/><br/>' .
                    'SOLICITANTE: <br/>' .
                    $userNs->matricula . ' - ' . $userNs->nome . '.' . '<br/>' .
                    $userNs->siglalotacao . ' - ' . $userNs->descicaolotacao . ' - ' . $userNs->codlotacao;
            $notificacao = $tabelaNotf->setnotfAction($matricula, $titulo, $sistema, $mensagem);
        }
    }
}
