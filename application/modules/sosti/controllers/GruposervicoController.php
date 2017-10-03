<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sosti_GruposervicoController extends Zend_Controller_Action {
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
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'SGRS_DS_GRUPO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

        $SosTbSgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico();
        $form = new Sosti_Form_Gruposervico();
        
        $form->removeElement('SGRS_DS_GRUPO');
        $form->removeElement('SGRS_IC_VISIVEL');
        $form->removeElement('UNPE_SG_SECAO');
       
        $form->removeElement('Salvar');
        $listar = new Zend_Form_Element_Submit('Listar');
        $form->addElement($listar);
       
        $trf1_secao = $form->getElement('TRF1_SECAO');
        $trf1_secao->setRequired(true);
        $trf1_secao = $form->getElement('SECAO_SUBSECAO');
        $trf1_secao->setRequired(true);
        
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //Zend_Debug::dump($data);
            
//            $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
//            $RhCentralLotacao->fetchAll('')
            
            $secao_subsecao = $form->getElement("SECAO_SUBSECAO");
            $secao_subsecao->addMultiOptions(array($data["SECAO_SUBSECAO"]=>''));
            
            
            if ( $form->isValid($data) ) {
                
                $secao_subsecao_arr = explode('|', $data["SECAO_SUBSECAO"]);
                //Zend_Debug::dump($secao_subsecao_arr); exit;
                
                $sgsecao = $secao_subsecao_arr[0];
                $codlotacao = $secao_subsecao_arr[1];
                $tipolotacao = $secao_subsecao_arr[2];
                
                $rows = $SosTbSgrsGrupoServico->getGrupoServicoPorTrfSecaoSubsecao($sgsecao, $codlotacao, $tipolotacao, $order);
                
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                          ->setItemCountPerPage(count($rows));
                
                $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
                $CentralLotacao = $RhCentralLotacao->fetchRow("LOTA_SIGLA_SECAO = '$sgsecao' AND LOTA_COD_LOTACAO = $codlotacao");
                
                $label_lotacao = $CentralLotacao["LOTA_SIGLA_LOTACAO"].' - '.$CentralLotacao["LOTA_DSC_LOTACAO"].' - '.$CentralLotacao["LOTA_COD_LOTACAO"].' - '.$CentralLotacao["LOTA_SIGLA_SECAO"];
                $secao_subsecao->addMultiOptions(array($data["SECAO_SUBSECAO"]=>$label_lotacao));
                
                $form->populate($data);
                
            }else{
                $form->populate($data);
                $this->view->form = $form;
                return;
            }
       }else{
           $rows = $SosTbSgrsGrupoServico->getGrupoServico($order); 
           $paginator = Zend_Paginator::factory($rows);
           $paginator->setCurrentPageNumber($page)
                     ->setItemCountPerPage(10);
       }
        
        $this->view->title = "Grupos de Serviços";
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        
        $this->view->form = $form;
        
        
    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar novo Grupos de Serviços de TI';
        $form = new Sosti_Form_Gruposervico();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SosTbSgrsGrupoServico();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            $secao_subsecao = $form->getElement("SECAO_SUBSECAO");
            $secao_subsecao->addMultiOptions(array($data["SECAO_SUBSECAO"]=>''));
            
            if ($form->isValid($data)) {
                unset($data['SGRS_ID_GRUPO']);
                $lotacao = explode('|', $data["UNPE_SG_SECAO"]);
                $data['SGRS_SG_SECAO_LOTACAO'] = $lotacao[0];
                $data['SGRS_CD_LOTACAO'] = $lotacao[1];
                $message = $data['SGRS_DS_GRUPO'];
                $row = $table->createRow($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O grupo de serviço: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','gruposervico','sosti');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Grupos de Serviços de TI';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sosti_Form_Gruposervico();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SosTbSgrsGrupoServico();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('SGRS_ID_GRUPO = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $data['LOTACAO'] = $data['SGRS_SG_SECAO_LOTACAO'].$data['SGRS_CD_LOTACAO'];
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui a variável $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            $secao_subsecao = $form->getElement("SECAO_SUBSECAO");
            $secao_subsecao->addMultiOptions(array($data["SECAO_SUBSECAO"]=>''));
            
            if ($form->isValid($data)) {
                $row = $table->find($data['SGRS_ID_GRUPO'])->current();
                $lotacao = explode('|', $data["UNPE_SG_SECAO"]);
                $data['SGRS_SG_SECAO_LOTACAO'] = $lotacao[0];
                $data['SGRS_CD_LOTACAO'] = $lotacao[1];
                $message = $data['SGRS_DS_GRUPO'];
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O grupo de serviço: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','gruposervico','sosti');
            }
        }
    }

}
