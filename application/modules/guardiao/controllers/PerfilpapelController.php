<?php

class Guardiao_PerfilpapelController extends Zend_Controller_Action
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
		$this->view->titleBrowser = "e-Guardião";
    }

    public function indexAction()
    {
        $this->view->title = "Perfil Papel";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Guardiao_Form_PerfilPapel();
        $table  = new Application_Model_DbTable_OcsTbPspaPerfilPapel();
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'PAPL_ID_PAPEL');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/
        $select = $table->getPapelbyPerfil($data['PSPA_ID_PERFIL'],$data['MODL_NM_MODULO']);
               
        $form->populate($data); 

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
                  //->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    public function alterarAction()
    {
        $this->view->title = "Perfil Papel ";
        $form   = new Guardiao_Form_PerfilPapel();
        $table  = new Application_Model_DbTable_OcsTbPspaPerfilPapel();
        
        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /*Ordenação das paginas*/
            $order = $this->_getParam('ordem', 'PAPL_ID_PAPEL');
            $direction = $this->_getParam('direcao', 'ASC');
            $order_aux = $order.' '.$direction;
            ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
            /*Ordenação*/
               
            $select = $table->getPapelbyPerfil($data['PSPA_ID_PERFIL'],$data['MODL_NM_MODULO']);
            
            $paginator = Zend_Paginator::factory($select);
            $paginator->setCurrentPageNumber($page)
                      ->setItemCountPerPage(count($select));
            $this->view->ordem = $order;
            $this->view->direcao = $direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        }
        $form->populate($data); 
        $this->view->form = $form;
    }
    
    public function formAction()
    {
        $this->view->title = "Perfil Papel";
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $data = $this->getRequest()->getPost();
        $form   = new Guardiao_Form_PerfilPapel();
        $table  = new Application_Model_DbTable_OcsTbPspaPerfilPapel();
        $flag = FALSE; 
        
        $this->view->form = $form;
       if ($this->getRequest()->isPost()){
            if ($data['acao']=='Alterar'){
                
            foreach ($data['papeis'] as $papeis):
                $papelArray1 = explode(" - ", $papeis[1]);
                $data['PAPL_ID_PAPEL'] = $papelArray1[0];
                $data['PSPA_ID_PERFIL_PAPEL'] = $papelArray1[1];
                $codigo1 = $papelArray1[2];

                $papelArray2 = explode(" - ", $papeis[2]);
                $data['PAPL_ID_PAPEL'] = $papelArray2[0];
                $data['PSPA_ID_PERFIL_PAPEL'] = $papelArray2[1];
                $codigo2 = $papelArray2[2];

                //alterações de DELETE
                if ($codigo1 == ""  && $codigo2 == "associado") {
                   $flag =TRUE;
                   try{
                       $row = $table->fetchRow("PSPA_ID_PERFIL_PAPEL = $data[PSPA_ID_PERFIL_PAPEL]");
                       $row->delete();
                       $msg_to_user = "Papel(is) excluído(s) com sucesso do perfil";
                       $msg_to_user = "<div class='success'><strong>Sucesso:</strong> $msg_to_user</div>";
                       $this->view->flashMessagesView = $msg_to_user;
                   }catch(Zend_Exception $error_string){
                        $msg_to_user = "Erro ao excluir papel(is) do perfil";
                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                   }

                //alterações de INSERT
                } else if ($codigo1 == "associar" && $codigo2 == "dissociado") {
                    $flag =TRUE;
                    try{
                        unset($data['PSPA_ID_PERFIL_PAPEL']);
                        $data["PSPA_ID_PAPEL"] = $data['PAPL_ID_PAPEL'];
                        $row = $table->createRow($data);
                        $row->save();
                        $msg_to_user = "Papel(is) associado(s) com sucesso ao perfil";
                        $msg_to_user = "<div class='success'><strong>Sucesso:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                   }catch(Zend_Exception $error_string){
                        $msg_to_user = "Erro ao associar papel(s) ao perfil";
                        $msg_to_user = "<div class='error'><strong>Erro:</strong> $msg_to_user</div>";
                        $this->view->flashMessagesView = $msg_to_user;
                   }
                }
            endforeach;
            
                if ($flag != TRUE){
                   $msg_to_user = "Nenhum papel foi associado ao perfil";
                   $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                   $this->view->flashMessagesView = $msg_to_user;
                }
            }


            $form->populate($data);
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            /*Ordenação das paginas*/
            $order = $this->_getParam('ordem', 'PAPL_ID_PAPEL');
            $direction = $this->_getParam('direcao', 'ASC');
            $order_aux = $order.' '.$direction;
            ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
            /*Ordenação*/

            $select = $table->getPapelbyPerfil($data['PSPA_ID_PERFIL'],$data['MODL_NM_MODULO']);

            if($select){
                $form->populate($data); 
                $paginator = Zend_Paginator::factory($select);
                $paginator->setCurrentPageNumber($page)
                          ->setItemCountPerPage(count($select));
                $this->view->ordem = $order;
                $this->view->direcao = $direction;
                $this->view->data = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }
       }        
        $this->view->form = $form;
    }
}
