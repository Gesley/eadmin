<?php

class Sosti_ConformidadeController extends Zend_Controller_Action
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
	
    var $controller;
    var $module;
		
    public function init() {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Sosti';
        $this->controller = $this->getRequest()->getControllerName();
        $this->module = $this->getRequest()->getModuleName();
    }

    public function indexAction()
    {
    	//TODO: Adicionar sessão Paginator
    	
        /*paginação*/
    	$conformidadeModel = new Application_Model_DbTable_SosTbSotctpnConformidade ();
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'SOTC_DS_CONFORMIDADE');
        $order_direction = $this->_getParam('direcao', 'DESC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC')?($order_direction = 'DESC'):($order_direction = 'ASC');
        /*Ordenação*/

       $dados = new Application_Model_DbTable_SosTbSaviAviso();
       $rows = $conformidadeModel->getConformidades($order);

       $paginator = Zend_Paginator::factory($rows);
       $paginator->setCurrentPageNumber($page)
                 ->setItemCountPerPage(10);

       $this->view->title = "Cadastro de tipo de não Conformidade";
       $this->view->ordem = $order_column;
       $this->view->direcao = $order_direction;
       $this->view->data = $paginator;
       Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function addtipoAction(){
    	
    	$form = new Sosti_Form_Conformidadeform ();
    	$this->view->title = "Cadastrar novo tipo de não Conformidade";
     	$form->setAction('saveconformidade');
    	$form->removeElement('SOTC_DH_FIM_CONFORMIDADE');
    	$this->view->form = $form;
    }
    /**
     * Salva uma nova não conformidade
     * 
     */

    public function saveconformidadeAction(){
    	//disabilita o layout e a view 
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
    	$form = new Sosti_Form_Conformidadeform ();
    	
    	$sys = new Application_Model_DbTable_Dual ();
    	$conformidadeModel = new Application_Model_DbTable_SosTbSotctpnConformidade ();
    	$userNs = new Zend_Session_Namespace ( 'userNs' );
    	
         
		if($this->getRequest()->isPost ()){
			$dataPost = $this->getRequest ()->getPost();
			$data = Zend_Json_Decoder::decode($dataPost['SOTC_ID_GRUPO']);
			
         //popula  a combo antes de submeter por causa do Ajax.
    	 $SosTbSserServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
         $SosTbSserServico_array = $SosTbSserServico->getIndicadorConformidade($data['SGRS_ID_GRUPO']);
         
		 $niveisCombo = $form->getElement('SOTC_ID_INDICADOR');
		foreach ($SosTbSserServico_array as $Niveis):
            $niveisCombo->addMultiOptions(array($Niveis["SINS_ID_INDICADOR"] => $Niveis["SINS_DS_INDICADOR"]));
        endforeach; 
		         
			if($form->isValid($dataPost))
			{
				zend_debug::dump($dataPost,'Data Post');
				zend_debug::dump($data,'Data Json');
				//monta array pra salvar no banco 
				$dataConformidade['SOTC_ID_INDICADOR'] = $dataPost['SOTC_ID_INDICADOR'];
				//$dataConformidade['SOTC_ID_GRUPO'] = $data['SGRS_ID_GRUPO'];
				$dataConformidade['SOTC_CD_MATRICULA_INCLUSAO'] = $userNs->matricula;
				$dataConformidade['SOTC_DS_CONFORMIDADE'] = $dataPost['SOTC_DS_CONFORMIDADE'];
				$dataConformidade['SOTC_DH_INICIO_CONFORMIDADE'] = $sys->sysdate();
				
				//zend_debug::dump($dataConformidade);exit;
				
				try {
				$conformidadeModel->createRow($dataConformidade)->save();
				$this->_helper->flashMessenger ( array ('message' => "Conformidade cadastrada", 'status' => 'success' ) );
					return $this->_helper->_redirector ( 'index', 'conformidade', 'sosti' );
					
				} catch (Exception $e) {
					$message = $e->getMessage();
					$this->_helper->flashMessenger ( array ('message' => "Ocorre um erro no cadastro.ERRO:$message", 'status' => 'notice' ) );
					return $this->_helper->_redirector ( 'index', 'conformidade', 'sosti' );
				}
				
			}else{
        		$form->removeElement('SOTC_DH_FIM_CONFORMIDADE');
				$this->_helper->layout->enableLayout();
				$this->_helper->viewRenderer->setNoRender(false);
				$this->view->form = $form;
				$this->_helper->viewRenderer->setRender ( 'addtipo' );
				
			}
			
		}
    }
    	

    	
    /**
     * altera as ionformações da não conformidade
     * 
     */
    public function editconformidadeAction() {

        $conformidadeModel = new Application_Model_DbTable_SosTbSotctpnConformidade ();
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $SosTbSserServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();

    	$form = new Sosti_Form_Conformidadeform ();
    	$idNivel = $this->_getParam('nivel');
    	$grupo = $this->_getParam('grupo');
        	
       	if(!empty($idNivel))
       	{
    		$grupoID = $SosTbSserServico->getGrupoAtendimentoNivel($idNivel);
    		
       	}else{
       		
       		$SosTbSserServico_array = $SosTbSserServico->getIndicadorConformidade($grupo);
       	}
       	
    	$SosTbSserServico_array = $SosTbSserServico->getIndicadorConformidade($grupo);

        $form = new Sosti_Form_Conformidadeform ();
        $idNivel = $this->_getParam('nivel');

        if (!empty($idNivel)) {
            $grupoID = $SosTbSserServico->getGrupoAtendimentoNivel($idNivel);

            $grupo = $grupoID[0]['SINS_ID_GRUPO'];
        } else {
            $grupo = $this->_getParam('grupo');
            $SosTbSserServico_array = $SosTbSserServico->getIndicadorConformidade($grupo);
        }

        $SgrsGrupoServicoRows = $SadTbCxgsGrupoServico->getTodasCaixas($grupo);

        $grupoJson = Zend_Json::encode($SgrsGrupoServicoRows[0]);
        $form->populate($grupoJson);
        $data['SOTC_ID_GRUPO'] = $grupoJson;

        $niveisCombo = $form->getElement('SOTC_ID_INDICADOR');
        foreach ($SosTbSserServico_array as $Niveis):
            $niveisCombo->addMultiOptions(array($Niveis["SINS_ID_INDICADOR"] => $Niveis["SINS_DS_INDICADOR"]));
        endforeach;

        //salva alterações no tipo da conformidade
        if ($this->getRequest()->isPost()) {
            $data = $this->_getAllParams();
            $dataJson = Zend_Json_Decoder::decode($data['SOTC_ID_GRUPO']); //decode JSon

            $SosTbSserServico_array = $SosTbSserServico->getIndicadorConformidade($dataJson['SGRS_ID_GRUPO']);

            $niveisCombo = $form->getElement('SOTC_ID_INDICADOR');
            foreach ($SosTbSserServico_array as $Niveis):
                $niveisCombo->addMultiOptions(array($Niveis["SINS_ID_INDICADOR"] => $Niveis["SINS_DS_INDICADOR"]));
            endforeach;

            if ($form->isValid($data)) {

                if (!empty($data['SOTC_ID_INDICADOR'])) {
                    $dataConformidade['SOTC_ID_GRUPO'] = "";
                } else {
                    $dataConformidade['SOTC_ID_GRUPO'] = $dataJson['SGRS_ID_GRUPO'];
                }
                //$dataConformidade['SOTC_ID_GRUPO'] = $dataJson['SGRS_ID_GRUPO'];
                $conformidadeID = $data['CONFORMIDADE_ID'];
                $dataConformidade['SOTC_CD_MATRICULA_INCLUSAO'] = $data['SOTC_CD_MATRICULA_INCLUSAO'];
                $dataConformidade['SOTC_DH_INICIO_CONFORMIDADE'] = $data['SOTC_DH_INICIO_CONFORMIDADE'];
                $dataConformidade['SOTC_DH_FIM_CONFORMIDADE'] = $data['SOTC_DH_FIM_CONFORMIDADE'];
                $dataConformidade['SOTC_DS_CONFORMIDADE'] = $data['SOTC_DS_CONFORMIDADE'];
                $dataConformidade['SOTC_ID_INDICADOR'] = $data['SOTC_ID_INDICADOR'];


                $row = $conformidadeModel->find(array('CONFORMIDADE_ID' => $conformidadeID))->current();
                try {
                    $row->setFromArray($dataConformidade)->save();
                    $this->_helper->flashMessenger(array('message' => "Conformidade alterada", 'status' => 'success'));
                    return $this->_helper->_redirector('index', 'conformidade', 'sosti');
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $this->_helper->flashMessenger(array('message' => "Ocorre um erro na alteração da conformidade.ERRO:$message", 'status' => 'notice'));
                    return $this->_helper->_redirector('index', 'conformidade', 'sosti');
                }
            } else {
                $this->_helper->layout->enableLayout();
                $this->_helper->viewRenderer->setNoRender(false);
                $this->view->form = $form;
                $this->_helper->viewRenderer->setRender('editconformidade');
            }
        } else {
            $conformidadeID = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
            $grupoID = Zend_Filter::filterStatic($this->_getParam('grupo'), 'int');

            $conformidadeModel = new Application_Model_DbTable_SosTbSotctpnConformidade ();



            $this->view->title = "Alterar tipo de não Conformidade";
            $data = $conformidadeModel->fetchRow(array('SOTC_ID_NAO_CONFORMIDADE=?' => $conformidadeID));
            $form->getElement('CONFORMIDADE_ID')->setValue($conformidadeID);

            $data['SOTC_ID_GRUPO'] = $grupoJson;
            $form->populate($grupoJson);
            $form->populate($data->toArray());
            $form->populate($data);
            $this->view->form = $form;
        }
    }
    
    public function ajaxniveisAction() {
        if ($this->getRequest()->isPost()) {
            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $id = $data['SGRS_ID_GRUPO'];
            $SosTbSserServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            //

            $SosTbSserServico_array = $SosTbSserServico->getIndicadorConformidade($id);
            $this->view->niveis = $SosTbSserServico_array;
        }
    }
   
}
