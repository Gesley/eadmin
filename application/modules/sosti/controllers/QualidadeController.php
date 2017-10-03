<?php

class Sosti_QualidadeController extends Zend_Controller_Action
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
	
    private $objModelDefeito;
    private $userNs;
    private $objModelMovimDefeito;
    
    public function init()
    {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        $this->objModelDefeito = new Application_Model_DbTable_SosTbTideTipoDefeito ();
        $this->objModelMovimDefeito = new Application_Model_DbTable_SosTbmsiMovimDefSistemas ();
        $this->userNs =  new Zend_Session_Namespace ( 'userNs' );
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI- Controle de qualidade';
    }
    
    /**
     * lista os tipos de erros.
     */
    public function indexAction(){
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        
        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'TIDE_DS_DEFEITO');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/
        
        
        $rows = $this->objModelDefeito->getDefeitos($order);
        
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setCurrentPageNumber($page)
        ->setItemCountPerPage(10);
        
        $this->view->title="Controle de qualidade- Tipos de erros";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        
    }
    
    /**
     * Adiciona um novo tipo de Erro para o controle de qualidade
     */
    public function addAction(){
        $this->view->title="Adiciona tipo de erro para controle de qualidade";
        $form = new Sosti_Form_Qualidade ();
        $this->view->form = $form;
        
        if ($this->getRequest()->isPost()) {
            $dataPost = $this->getRequest()->getPost();
            if ($form->isValid($dataPost)) {
                
                $dataDefeito = $dataPost;
                $dataDefeito['TIDE_NM_DEFEITO'] = strtoupper($dataDefeito['TIDE_NM_DEFEITO']);
                $dataDefeito['TIDE_DS_DEFEITO'] = strtoupper($dataDefeito['TIDE_DS_DEFEITO']);
                $dataDefeito['TIDE_CD_MATRICULA_INCLUSAO'] = $this->userNs->matricula;
                $dataDefeito['TIDE_DH_INCLUSAO'] = App_Util::getTimeStamp_Audit();
                $dataDefeito['TIDE_ID_INDICADOR'] = $dataDefeito['TIDE_ID_INDICADOR'];
//              $dataDefeito['TIDE_IC_ATIVO'] = 'S';
                try {
					$this->objModelDefeito->createRow($dataDefeito)->save();                	
					$this->_helper->flashMessenger ( array('message' =>'<strong>'. $dataDefeito['TIDE_DS_DEFEITO'].'</strong> cadastrado com sucesso', 'status' => 'success'));
                } catch (Exception $e) {
                    $this->_helper->flashMessenger ( array('message' => 'Erro ao inserir error:'.$e->getMessage(), 'status' => 'error'));
                }
                return $this->_helper->_redirector('index','qualidade','sosti');
        	}else{
        	    $this->view->form = $form;
        	    $this->view->title="Adiciona tipo de erro para controle de qualidade";
        	}
    	}
    }
	/**
	 * Atualiza o tipo de erro para o controle de qualidade
	 */    
    public function editAction(){
        $this->view->title="Altera tipo de erro para controle de qualidade";
		
		$form = new Sosti_Form_Qualidade ();
		$idTipoErro = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
		$dataPost = $this->getRequest()->getPost();
		
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($dataPost)) {
				
				$dataDefeito = $dataPost;
				$dataDefeito['TIDE_NM_DEFEITO'] = strtoupper($dataDefeito['TIDE_NM_DEFEITO']);
                $dataDefeito['TIDE_DS_DEFEITO'] = strtoupper($dataDefeito['TIDE_DS_DEFEITO']);
				
				if($dataDefeito["TIDE_IC_ATIVO"] == 'N'){
					$dataDefeito['TIDE_CD_MATRICULA_INATIVACAO'] = $this->userNs->matricula;
					$dataDefeito['TIDE_DH_INATIVACAO'] = App_Util::getTimeStamp_Audit();
				}
				
				$rowAlterar = $this->objModelDefeito->find($idTipoErro)->current();
				$rowAlterar->setFromArray($dataDefeito);

				try {
				$idRow = $rowAlterar->save();
					
                $this->_helper->flashMessenger ( array('message' => 'Registro<strong> '.$dataDefeito['TIDE_DS_DEFEITO'].'</strong> atualizado com sucesso!', 'status' => 'success'));
				} catch (Exception $e) {
				    $this->_helper->flashMessenger ( array('message' => 'Ocorreu um erro ao atualizar o registro!'.$e->getMessage(), 'status' => 'error'));
				}
				
            return $this->_helper->_redirector('index','qualidade','sosti');
				
            }
            
              
        }else{
			$dataPost = $this->objModelDefeito->fetchRow("TIDE_ID_TIPO_DEFEITO_SISTEMA = $idTipoErro")->toArray();

			$form->populate($dataPost);
            $this->view->form = $form;
            $this->render('edit');
		}
		$this->view->form = $form;
    }
    /**
     * Remove o tipo de erro para o controle de qualidade
     */
    public function deleteAction(){
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
            }
        }
    }
    /**
     * Associa um tipo de erro em uma solicitação
     */
    public function associaerroAction(){
        $form = new Sosti_Form_AssociarErro();
        $tabelaIndicador = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $this->dadosSolic =  new Zend_Session_Namespace ( 'dadosSolic' );
        $this->view->title="Controle de Qualidade - Insere Erro/defeito na solicitação";
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $dataPost = $this->getRequest()->getPost();
        if($dataPost['acao']=='associar'){
	        if($form->isValid($dataPost)){
                $id_indicador = $tabelaIndicador->getIndicadorDefeito();
	            foreach ( $this->dadosSolic->dadosSolic as $dataRaw) {
	                $data = Zend_Json::decode($dataRaw);
	                
	                $dataDefeitoMovim['MDSI_ID_MOVIMENTACAO'] = $data['MOFA_ID_MOVIMENTACAO'];
	                $dataDefeitoMovim['MDSI_ID_TIPO_DEFEITO_SISTEMA'] = 0;
	                $dataDefeitoMovim['MDSI_NR_DEFEITOS'] = $dataPost['MDSI_NR_DEFEITOS'];
	                $dataDefeitoMovim['MDSI_DS_JUSTIF_DEFEITO'] = $dataPost['COMENTARIO_DEFEITO'];
	                $dataDefeitoMovim['MDSI_CD_MATRICULA_INCLUSAO']= $this->userNs->matricula;
	                $dataDefeitoMovim['MDSI_DH_INCLUSAO'] = App_Util::getTimeStamp_Audit();
	                $dataDefeitoMovim['MDSI_ID_INDICADOR'] = $id_indicador[0]['SINS_ID_INDICADOR'];
                    $dataDefeitoMovim['MDSI_IC_CANCELAMENTO'] = "N";
                    $dataDefeitoMovim['MDSI_CD_MATRIC_CANCELAMENTO'] = NULL;
                    $dataDefeitoMovim['MDSI_DH_CANCELAMENTO'] = NULL;
                    $dataDefeitoMovim['MDSI_DS_CANCELAMENTO'] = NULL;
	                try {
	                    $hasRow = $this->objModelMovimDefeito->fetchRow(array('MDSI_ID_MOVIMENTACAO=?'=>$data['MOFA_ID_MOVIMENTACAO']));
	                    if(!$hasRow){//SENÃO EXISTIR DEFEITO PRA AQUELA MOVIMENTAÇÃO INSIRA O NOVO DEFEITO.ISTO EVITA REGISTROS DUPLICADOS.
	                        
	                        $this->objModelMovimDefeito->createRow($dataDefeitoMovim)->save();
	                    	$this->_helper->flashMessenger ( array('message' => 'Defeito registrado na solicitação<strong> '.$data['DOCM_NR_DOCUMENTO'].'</strong> com sucesso!', 'status' => 'success'));
	                    }else{
                            $hasRow->setFromArray($dataDefeitoMovim)->save();
                            $this->_helper->flashMessenger(array('message' => 'Defeito registrado na solicitação<strong> ' . $dadosSolic['DOCM_NR_DOCUMENTO'] . '</strong> com sucesso!', 'status' => 'success'));
                        }
	                } catch (Exception $e) {
	                    $this->_helper->flashMessenger ( array('message' => 'Erro ao registrar defeito na solicitação<strong> '.$data['DOCM_NR_DOCUMENTO'].'</strong>!'.$e->getMessage(), 'status' => 'error'));
	                }
	            
	            }
	             return $this->_helper->_redirector('qualidadecaixa','gestaodedemandasti','sosti',array('idcaixa'=>2));
	        
	        }else{
	            //$this->dadosSolic->dadosSolic = $dataPost['solicitacao'];
	            $paginator = Zend_Paginator::factory($this->dadosSolic->dadosSolic);
	            $this->view->data = $paginator;
	            $form->removeElement('TIDE_NM_DEFEITO');
	            $this->view->form = $form;
	        }         
        }
        else{
	        //INSERE DADOS DA SOLICITAÇÃO NA SESSÃO
        	$this->dadosSolic->dadosSolic = $dataPost['solicitacao'];
	        $paginator = Zend_Paginator::factory($this->dadosSolic->dadosSolic);
	        $this->view->data = $paginator;
	        $form->removeElement('TIDE_NM_DEFEITO');
	        $this->view->form = $form;
        	}
    	
    }
    
    /**
     * Desassocia erro  em uma solicitação
     */
    //TODO:Refactor ese método com uma estrutura melhor.
    public function desassociarerroAction() {
        $idMovimentacao = array();
        $this->view->title = "Controle de Qualidade - Remover Erro/Defeito na solicitação";
        $this->dadosSolic = new Zend_Session_Namespace('dadosSolic');
        $this->userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_AssociarErro();
        $dataPost = $this->getRequest()->getPost();
//        $form->getElement('acao')->setValue('desassociar');

//        if ($dataPost['acao'] == 'desassociar') {
//            if ($form->isValid($dataPost)) {
                $this->dadosSolic->dados = $dataPost['solicitacao'];
                foreach ($this->dadosSolic->dados as $data) { //LOOP NAS SOLICITAÇÕES
                    $dadosSolic = Zend_Json::decode($data);
                    $hasRow = $this->objModelMovimDefeito->fetchRow(array('MDSI_ID_MOVIMENTACAO=?' => $dadosSolic['MOFA_ID_MOVIMENTACAO'], 'MDSI_IC_CANCELAMENTO=?' => 'N'));
                    if ($hasRow) {
                        $dataCancelamento['MDSI_IC_CANCELAMENTO'] = "S";
                        $dataCancelamento['MDSI_CD_MATRIC_CANCELAMENTO'] = $this->userNs->matricula;
                        $dataCancelamento['MDSI_DH_CANCELAMENTO'] = App_Util::getTimeStamp_Audit();
                        $dataCancelamento['MDSI_DS_CANCELAMENTO'] = 'CANCELADO PELO USUÁRIO';
                        try {
                            $hasRow->setFromArray($dataCancelamento)->save();
                            $this->_helper->flashMessenger(array('message' => 'Defeito retirado na solicitação<strong> ' . $dadosSolic['DOCM_NR_DOCUMENTO'] . '</strong> com sucesso!', 'status' => 'success'));
                        } catch (Exception $e) {
                            $this->_helper->flashMessenger(array('message' => 'Erro ao retirar defeito da solicitação' . $e->getMessage(), 'status' => 'error'));
                        }
                    }
                }

                //REDIRECT PARA CAIXA
                return $this->_helper->_redirector('qualidadecaixa', 'gestaodedemandasti', 'sosti');
//            } else {
//                foreach ($this->dadosSolic->dados as $dataRaw) {
//                    $data = Zend_Json::decode($dataRaw);
//                    array_push($idMovimentacao, $data['MOFA_ID_MOVIMENTACAO']);
//                }
//                $form->populate($dataPost);
//                $this->view->form = $form;
//                $paginator = Zend_Paginator::factory($this->dadosSolic->dados);
//                $this->view->data = $paginator;
//            }
//        } else {
//            $this->dadosSolic = new Zend_Session_Namespace('dadosSolic');
//            $this->dadosSolic->dados = $dataPost['solicitacao'];
//            foreach ($this->dadosSolic->dados as $dataRaw) {
//                $data = Zend_Json::decode($dataRaw);
//                array_push($idMovimentacao, $data['MOFA_ID_MOVIMENTACAO']);
//            }
//            $this->view->form = $form;
//            $form->getElement('COMENTARIO_DEFEITO')->setValue($data['MDSI_DS_JUSTIF_DEFEITO']);
//            $form->populate($data);
//            $paginator = Zend_Paginator::factory($this->dadosSolic->dados);
//            $this->view->data = $paginator;
//        }
    }
    
    public function editarerroAction() {
        $idMovimentacao = array();
        $this->view->title = "Controle de Qualidade - Editar Erro/Defeito na solicitação";
        $this->dadosSolic = new Zend_Session_Namespace('dadosSolic');
        $form = new Sosti_Form_AssociarErro();
        $dataPost = $this->getRequest()->getPost();
        $form->getElement('acao')->setValue('desassociar');

        if ($dataPost['acao'] == 'desassociar') {
            if ($form->isValid($dataPost)) {
                $this->userNs = new Zend_Session_Namespace('userNs');
                foreach ($this->dadosSolic->dados as $data) { //LOOP NAS SOLICITAÇÕES
                    $dadosSolic = Zend_Json::decode($data);
                    $hasRow = $this->objModelMovimDefeito->fetchRow(array('MDSI_ID_MOVIMENTACAO=?' => $dadosSolic['MOFA_ID_MOVIMENTACAO']));
                    if ($hasRow) {
                        $dataEditar['MDSI_DH_INCLUSAO'] = App_Util::getTimeStamp_Audit();
                        $dataEditar['MDSI_ID_TIPO_DEFEITO_SISTEMA'] = 0;
                        $dataEditar['MDSI_CD_MATRICULA_INCLUSAO'] = $this->userNs->matricula;
                        $dataEditar['MDSI_NR_DEFEITOS'] = $dataPost['MDSI_NR_DEFEITOS'];
                        $dataEditar['MDSI_DS_JUSTIF_DEFEITO'] = $dataPost['COMENTARIO_DEFEITO'];
                        
                        $dataEditar['MDSI_IC_CANCELAMENTO'] = "N";
                        $dataEditar['MDSI_CD_MATRIC_CANCELAMENTO'] = NULL;
                        $dataEditar['MDSI_DH_CANCELAMENTO'] = NULL;
                        $dataEditar['MDSI_DS_CANCELAMENTO'] = NULL;
                        try {
                            $hasRow->setFromArray($dataEditar)->save();
                            $this->_helper->flashMessenger(array('message' => 'Defeito retirado na solicitação<strong> ' . $dadosSolic['DOCM_NR_DOCUMENTO'] . '</strong> com sucesso!', 'status' => 'success'));
                        } catch (Exception $e) {
                            $this->_helper->flashMessenger(array('message' => 'Erro ao retirar defeito da solicitação' . $e->getMessage(), 'status' => 'error'));
                        }
                    }
                }
                //REDIRECT PARA CAIXA
                return $this->_helper->_redirector('qualidadecaixa', 'gestaodedemandasti', 'sosti');
            } else {

                $form->populate($dataPost);
                $this->view->form = $form;
                $paginator = Zend_Paginator::factory($this->dadosSolic->dados);
                $this->view->data = $paginator;
            }
        } else {
            $count = count($dataPost['solicitacao']);
            if ($count!=1) {
                $this->_helper->flashMessenger(array('message' => 'Somente é possível editar uma solicitação por vez.', 'status' => 'notice'));
                return $this->_helper->_redirector('qualidadecaixa', 'gestaodedemandasti', 'sosti');
            }
            $this->dadosSolic = new Zend_Session_Namespace('dadosSolic');
            $this->dadosSolic->dados = $dataPost['solicitacao'];
            foreach ($this->dadosSolic->dados as $dataRaw) {
                $data = Zend_Json::decode($dataRaw);
                array_push($idMovimentacao, $data['MOFA_ID_MOVIMENTACAO']);
            }
            $form->getElement('COMENTARIO_DEFEITO')->setValue($data['MDSI_DS_JUSTIF_DEFEITO']);
            $form->populate($data);
            $paginator = Zend_Paginator::factory($this->dadosSolic->dados);
            $this->view->form = $form;
            $this->view->data = $paginator;
        }
    }
    
    public function semerroAction() {
        $idMovimentacao = array();
        $this->view->title = "Controle de Qualidade - Remover Erro/Defeito na solicitação";
        $this->dadosSolic = new Zend_Session_Namespace('dadosSolic');
        $this->userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_AssociarErro();
        $dataPost = $this->getRequest()->getPost();
        $this->dadosSolic->dados = $dataPost['solicitacao'];
        $tabelaIndicador = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $id_indicador = $tabelaIndicador->getIndicadorDefeito();
        foreach ($this->dadosSolic->dados as $data) { 
            $dadosSolic = Zend_Json::decode($data);
            $hasRow = $this->objModelMovimDefeito->fetchRow(array('MDSI_ID_MOVIMENTACAO=?' => $dadosSolic['MOFA_ID_MOVIMENTACAO'], 'MDSI_IC_CANCELAMENTO=?' => 'N'));
            if ($hasRow) {
                $dataSemErro['MDSI_ID_MOVIMENTACAO'] = $dadosSolic['MOFA_ID_MOVIMENTACAO'];
                $dataSemErro['MDSI_ID_TIPO_DEFEITO_SISTEMA'] = 0;
                $dataSemErro['MDSI_NR_DEFEITOS'] = 0;
                $dataSemErro['MDSI_DS_JUSTIF_DEFEITO'] = ' ';
                $dataSemErro['MDSI_CD_MATRICULA_INCLUSAO']= $this->userNs->matricula;
                $dataSemErro['MDSI_DH_INCLUSAO'] = App_Util::getTimeStamp_Audit();
                $dataSemErro['MDSI_ID_INDICADOR'] = $id_indicador[0]['SINS_ID_INDICADOR'];
                $dataSemErro['MDSI_IC_CANCELAMENTO'] = "N";
                $dataSemErro['MDSI_CD_MATRIC_CANCELAMENTO'] = NULL;
                $dataSemErro['MDSI_DH_CANCELAMENTO'] = NULL;
                $dataSemErro['MDSI_DS_CANCELAMENTO'] = NULL;
                try {
                    $hasRow->setFromArray($dataSemErro)->save();
                    $this->_helper->flashMessenger(array('message' => 'Defeito registrado na solicitação<strong> ' . $dadosSolic['DOCM_NR_DOCUMENTO'] . '</strong> com sucesso!', 'status' => 'success'));
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => 'Erro ao retirar defeito da solicitação' . $e->getMessage(), 'status' => 'error'));
                }
            }
        }

        //REDIRECT PARA CAIXA
        return $this->_helper->_redirector('qualidadecaixa', 'gestaodedemandasti', 'sosti');
    }
}
