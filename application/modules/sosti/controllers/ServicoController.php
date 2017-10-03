<?php

class Sosti_ServicoController extends Zend_Controller_Action
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
		$this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }
    
    /*
     * busca os grupos de servicos especificos de acordo com a lotacao do usuario
     */
    public function combogruposervicoAction(){
        
        $userNs = new Zend_Session_Namespace('userNs');
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        $gruposServicosSecoes = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao( $userNs->siglasecao , $userNs->codsecsubseclotacao );

        $sser_id_grupo = new Zend_Form_Element_Select('SSER_ID_GRUPO');
        $sser_id_grupo->setLabel('Grupo de Serviço:')
                    ->setAttrib('style', 'width: 800px;')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');
        $sser_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($gruposServicosSecoes as $grupo_esp) {
            $sser_id_grupo->addMultiOptions(array($grupo_esp["SGRS_ID_GRUPO"] => $grupo_esp["SGRS_DS_GRUPO"]));
        }
        
        return $sser_id_grupo;
    }
    
    public function indexAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $NsActionName = $this->getRequest()->getModuleName().$this->getRequest()->getControllerName().$this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /*Ordenação das paginas*/
        $order_column = $this->_getParam('ordem', 'SSER_DS_SERVICO');
        $order_direction = $this->_getParam('direcao', 'ASC');
        $order = $order_column.' '.$order_direction;
        ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
        /*Ordenação*/

        $form = new Sosti_Form_Servico();
        
        $form->removeElement("SSER_DS_SERVICO");
        $form->removeElement("SSER_IC_ATIVO");
        $form->removeElement("SSER_IC_VISIVEL");
        $form->removeElement("SSER_IC_TOMBO");
        $form->removeElement("SSER_IC_ANEXO");
        $form->removeElement("SSER_ID_GRUPO");
        $form->removeElement("REPLICAR_TRF");
        $form->removeElement("SSER_IC_VIDEOCONFERENCIA");
        $form->addElement($this->combogruposervicoAction());
        
        $form->removeElement('Salvar');
        $listar = new Zend_Form_Element_Submit('Listar');
        $listar->setAttrib('class', 'listar');
        $listar->removeDecorator('DtDdWrapper')
                ->setAttrib('style', 'width: 200px;');
        $form->addElement($listar);
        
         if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //Zend_Debug::dump($data);

            
            if ( $form->isValid($data) ) {
                $NsAction->dataPost = $data;
                $dados = new Application_Model_DbTable_SosTbSserServico();
                $rows = $dados->getServicoPorGrupo($data["SSER_ID_GRUPO"], $order);
                $paginator = Zend_Paginator::factory($rows);
                $paginator->setCurrentPageNumber($page)
                              ->setItemCountPerPage(count($rows));
                $this->view->data = $paginator;
                $form->populate($data);
                $this->view->form = $form;
            }else{
                $form->populate($data);
                $this->view->form = $form;
                return;
            }
       }
       if(!is_null($NsAction->dataPost)){
           $data = $NsAction->dataPost;
           $dados = new Application_Model_DbTable_SosTbSserServico();
           $rows = $dados->getServicoPorGrupo($data["SSER_ID_GRUPO"], $order);
           $paginator = Zend_Paginator::factory($rows);
           $paginator->setCurrentPageNumber($page)
                              ->setItemCountPerPage(count($rows));
           $this->view->data = $paginator;
           $form->populate($data);
           $this->view->form = $form;
       }
        
        $this->view->title = 'Serviços de TI';
        $this->view->ordem = $order_column;
        $this->view->direcao = $order_direction;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        
        $this->view->form = $form;
    }
    
    public function addAction()
    {
        $this->view->title = 'Cadastrar novo serviço de TI';
        $form = new Sosti_Form_Servico();
        $form->removeelement("SSER_ID_GRUPO");
        $form->addElement($this->combogruposervicoAction());
        $this->view->form =  $form;
        $userNs = new Zend_Session_Namespace('userNs');

        $table  = new Application_Model_DbTable_SosTbSserServico();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                
                unset($data['SSER_ID_SERVICO']);
                $message = $data['SSER_DS_SERVICO'];
                $form->removeElement('REPLICAR_TRF');
              
                $row = $table->createRow($data);
                try {
                    $row->save();
                    if($row){
                         if( $data['REPLICAR_TRF'] == 'S' ){
                            
                            $SosTbSgrsGrupoServico = new Application_Model_DbTable_SosTbSgrsGrupoServico();
                            $descricaoGrupo = $SosTbSgrsGrupoServico->getEspecificoGrupoServico($data['SSER_ID_GRUPO']);
                            $descricaoSolicitacao = "Cadastrar novo serviço no TRF.
                                                      Grupo de Serviço: ".$descricaoGrupo[0]['SGRS_DS_GRUPO']."
                                                      Serviço: ".$data['SSER_DS_SERVICO']."
                                                      Flag ativo: ".$data['SSER_IC_ATIVO']." 
                                                      Flag visível: ".$data['SSER_IC_VISIVEL']."
                                                      Flag tombo: ".$data['SSER_IC_TOMBO']."
                                                      Flag anexo: ".$data['SSER_IC_ANEXO'];
         //                   Zend_debug::dump($descricaoSolicitacao); 

                            $dataDocmDocumento = array( "DOCM_CD_MATRICULA_CADASTRO" => $userNs->matricula,
                                                        "DOCM_ID_TIPO_DOC" => 160, //Solicitação de serviços a TI
                                                        "DOCM_SG_SECAO_GERADORA" => $userNs->siglasecao,
                                                        "DOCM_CD_LOTACAO_GERADORA" => $userNs->codlotacao,
                                                        "DOCM_SG_SECAO_REDATORA" => $userNs->siglasecao,
                                                        "DOCM_CD_LOTACAO_REDATORA" => $userNs->codlotacao,
                                                        "DOCM_ID_PCTT" => 2539, //PCTT Solicitação de TI
                                                        "DOCM_DS_ASSUNTO_DOC" => $descricaoSolicitacao,
                                                        "DOCM_ID_TIPO_SITUACAO_DOC" => 1,
                                                        "DOCM_ID_CONFIDENCIALIDADE" => 0,
                                                        "DOCM_NR_DOCUMENTO_RED" => "",
                                                        "DOCM_DS_PALAVRA_CHAVE" => "serviço, grupo de serviço, ".$data['SSER_DS_SERVICO']
                            );
                            unset($dataDocmDocumento["DOCM_NR_DOCUMENTO_RED"]);
                           // Zend_debug::dump($dataDocmDocumento); 

                            $dataSsolSolicitacao = array ('SSOL_ID_DOCUMENTO' => '',
                                                          'SSOL_ID_TIPO_CAD' => 1,
                                                          'SSOL_ED_LOCALIZACAO' => $userNs->localizacao,
                                                          'SSOL_NR_TOMBO'  => '',
                                                          'SSOL_SG_TIPO_TOMBO' => '',
                                                          'SSOL_DS_OBSERVACAO' => 'Solicitação cadastrada automaticamente pelo sistema E-SOSTI',
                                                          'SSOL_NM_USUARIO_EXTERNO' => '',
                                                          'SSOL_NR_CPF_EXTERNO' => '',
                                                          'SSOL_DS_EMAIL_EXTERNO' => $userNs->email,
                                                          'SSOL_NR_TELEFONE_EXTERNO' => $userNs->telefone
                            );

                            $dataMoviMovimentacao = array('MOVI_SG_SECAO_UNID_ORIGEM' => $userNs->siglasecao,
                                                          'MOVI_CD_SECAO_UNID_ORIGEM' => $userNs->codlotacao,
                                                          'MOVI_CD_MATR_ENCAMINHADOR' => $userNs->matricula
                            );

                            $dataModeMoviDestinatario = array('MODE_SG_SECAO_UNID_DESTINO' => 'TR',
                                                              'MODE_CD_SECAO_UNID_DESTINO' => 1134,  
                                                              'MODE_ID_CAIXA_ENTRADA' => '19', //Caixa Desenvolvimento/Sustentacao
                                                              'MODE_IC_RESPONSAVEL' => 'N',
                                                         );

                            $dataMofaMoviFase = array('MOFA_ID_FASE' => 1006, //Cadastro Solicitacao de TI
                                                      'MOFA_CD_MATRICULA' => $userNs->matricula, 
                                                      'MOFA_DS_COMPLEMENTO' => "Cadastro da Solicitação."
                            );

                            $dataSnasNivelAtendSolic = array('SNAS_ID_NIVEL' => "");

                            $dataSsesServicoSolic = array('SSES_ID_SERVICO' => 191); // Servico e-Sosti

                            $dataAnexAnexo['NR_DOCUMENTO_INTERNO'] = null; 
                            
                            $sosTbSsolsolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
                            $CadastraeSosti = $sosTbSsolsolicitacao->cadastraSolicitacao($dataDocmDocumento, $dataSsolSolicitacao, $dataMoviMovimentacao, 
                                                                                 $dataModeMoviDestinatario, $dataMofaMoviFase, $dataSsesServicoSolic, 
                                                                                 $dataSnasNivelAtendSolic);
                            
                            $dadosSolicitacao = $sosTbSsolsolicitacao->getDadosSolicitacao($CadastraeSosti["DOCM_ID_DOCUMENTO"]);
                        /**
                         * Envio de email de resposta
                         */
                            $email = new Application_Model_DbTable_EnviaEmail();
                            $sistema = 'e-Sosti - Sistema de Ordem de Serviço de TI';
                            $remetente = 'noreply@trf1.jus.br';
                            $destinatario = $userNs->email;
                            $assunto = 'Cadastro de Solicitação';
                            $corpo = "Cadastro de Solicitação efetuado com sucesso</p>
                                    Número da Solicitação: ".$CadastraeSosti['DOCM_NR_DOCUMENTO']." <br/>
                                    Data da Solicitação: ".$dadosSolicitacao["DOCM_DH_CADASTRO"]." <br/>
                                    Tipo de Serviço Solicitado: ".$dadosSolicitacao['SSER_DS_SERVICO']."<br/>
                                    Descrição da Solicitação: ".nl2br($dadosSolicitacao["DOCM_DS_ASSUNTO_DOC"])."<br/>
                                    Observação da Solicitação: ".nl2br($dadosSolicitacao["SSOL_DS_OBSERVACAO"])."<br/>";
                            try {
                                $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
                            } catch (Exception $exc) {
                                $this->_helper->flashMessenger ( array('message' => 'Não foi possivel enviar email de confirmação para solicitação: '.$dados_input["DOCM_NR_DOCUMENTO"].'<br><b> Erro: </b> <p>'.strip_tags($exc->getMessage()).'<p>', 'status' => 'notice'));
                            }
                            if($CadastraeSosti){
                                   $msg = " <br/>E-sosti nº ".$CadastraeSosti['DOCM_NR_DOCUMENTO']." enviado com sucesso.";
                            }else{
                                   $msg = " <br/>Não foi possível enviar o e-sosti para cadastro do serviço no TRF.";
                            }
                        } 
                    }
                    
                }catch (Exception $exc) {
                    $erro =  $exc->getMessage();
                    $this->_helper->flashMessenger ( array('message' => "Ocorreu um erro <br/> $erro", 'status' => 'error'));
                    return $this->_helper->_redirector('index','servico','sosti');
                }
                
                $msg_to_user = "O serviço: $message foi cadastrado!".$msg;
                $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                return $this->_helper->_redirector('index','servico','sosti');
            }
        }
    }  
    
    public function editAction()
    {
        $this->view->title = 'Alterar serviço de TI';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Sosti_Form_Servico();
        $form->removeElement('REPLICAR_TRF');                       
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_SosTbSserServico();
        
        $form->removeelement("SSER_ID_GRUPO");
        $form->addElement($this->combogruposervicoAction());
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('SSER_ID_SERVICO = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            if ($form->isValid($data)) {
                $message = $data['SSER_DS_SERVICO'];
                
                $row = $table->find($data['SSER_ID_SERVICO'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O serviço: $message foi atualizado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','servico','sosti');
            }
        }
    }
    
}
