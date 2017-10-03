<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sosti_LabconsultaController extends Zend_Controller_Action {
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
		$this->view->titleBrowser = 'e-Sosti';
    }
    
    public function indexAction()
    {
        $form = new Sosti_Form_LabConsulta();
        
        $Ns_Pesquisarsolicitacoes_index = new Zend_Session_Namespace('Ns_Pesquisarsolicitacoes_index');
        
        $form_valores_padrao = $form->getValues();
        
        
        if($this->_getParam('nova')=== '1'){
                unset($Ns_Pesquisarsolicitacoes_index->data_pesq);
                $Request = $this->getRequest();
                $module = $Request->getModuleName();
                $controller = $Request->getControllerName();
                $action = $Request->getActionName();
                $this->_redirect($module.'/'.$controller.'/'.$action);
        }
        
        if ($this->getRequest()->isPost()) {
            $data_pesq = $this->getRequest()->getPost();
            
            $form->populate ($data_pesq);
            if($form_valores_padrao == $form->getValues() ){
                 $this->view->form = $form;
                $this->view->title = "Laboratório, Pesquisar Solicitações";
                $msg_to_user = "O preenchimento de um dos campos de pesquisa é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
                
            if($data_pesq['SGRS_ID_GRUPO']){
                $input_data_sgrs_id_grupo = Zend_Json::decode($data_pesq['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data_pesq['SGRS_ID_GRUPO'] => $input_data_sgrs_id_grupo["SGRS_DS_GRUPO"]));

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorGrupo($input_data_sgrs_id_grupo['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"].'|'.$SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            
            
            if($form->isValid($data_pesq)){
                $Ns_Pesquisarsolicitacoes_index->data_pesq = $this->getRequest()->getPost();
            }else{
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "Laboratório, Pesquisar Solicitações";
                return;
            }
        }
     
        
        $data_pesq = $Ns_Pesquisarsolicitacoes_index->data_pesq;
        if(! is_null($data_pesq) ){
            $this->view->ultima_pesq = true;
            
            
            /*paginação*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'TEMPO_TOTAL');
            $order_direction = $this->_getParam('direcao', 'DESC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');


            $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();

            
            $count = $dados->getTodasSolicitacoesCount($data_pesq);

            if($count["COUNT"] <= 500){
                $rows = $dados->getTodasSolicitacoes($data_pesq, $order);
            }else{
                $this->view->form = $form;
                $this->view->title = "Laboratório, Pesquisar Solicitações";
                $msg_to_user = "A pesquisa retornou $count[COUNT] registros ultrapassou o maximo de 500 registros.  <br/> Informe mais parâmetros de pesquisa. <br/> Por Exemplo, limite um período de tempo.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
            
            /*verifica condições e faz tratamento nos dados*/
            //$TimeInterval = new App_TimeInterval();
            $fim =  count($rows);
            for ($i = 0; $i<$fim; $i++ ) {
                //$rows[$i]['TEMPO_TRANSCORRIDO'] = $TimeInterval->interval($rows[$i]['MOFA_DH_FASE']);
                unset ($rows[$i]['MOFA_DH_FASE']);
                unset ($rows[$i]['DATA_ATUAL']);
                $rows[$i]['DADOS_INPUT'] = Zend_Json::encode($rows[$i]);
            }
            
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                      ->setItemCountPerPage(100);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            
            if($data_pesq['SGRS_ID_GRUPO']){
                $input_data_sgrs_id_grupo = Zend_Json::decode($data_pesq['SGRS_ID_GRUPO']);
                $sgrs_id_grupo = $form->getElement('SGRS_ID_GRUPO');
                $sgrs_id_grupo->addMultiOptions(array($data_pesq['SGRS_ID_GRUPO'] => $input_data_sgrs_id_grupo["SGRS_DS_GRUPO"]));

                $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
                $SserServico = $SosTbSserServico->getServicoPorGrupo($input_data_sgrs_id_grupo['SGRS_ID_GRUPO'], 'SSER_DS_SERVICO ASC');
                $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
                foreach ($SserServico as $SserServico_p):
                    $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"].'|'.$SserServico_p["SSER_IC_TOMBO"] => $SserServico_p["SSER_DS_SERVICO"]));
                endforeach;
            }
            $form->populate($data_pesq);
        }
        
        
        $this->view->form = $form;
        $this->view->title = "Laboratório, Pesquisar Solicitações";
    }
}
