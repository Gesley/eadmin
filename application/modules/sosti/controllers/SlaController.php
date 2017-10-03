<?php

class Sosti_SlaController extends Zend_Controller_Action
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
	
   public $baseUrl;
    
    public function init()
    {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
		$this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        $this->baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }
    
    public function controleslaAction() {
        /**
         * Forms
         */
        $form = new Sosti_Form_CaixaSolicitacao();
        $form2 = new Sosti_Form_RelatoriosHelpdesk();
//        $form->removeElement('SSOL_ID_DOCUMENTO');
//        $form->removeElement('DOCM_NR_DOCUMENTO');
//        $form->removeElement('DOCM_CD_MATRICULA_CADASTRO');
//        $form->removeElement('DOCM_CD_MATRICULA_CADASTRO_VALUE');
//        $form->removeElement('SSOL_CD_MATRICULA_ATENDENTE');
//        $form->removeElement('SSOL_CD_MATRICULA_ATENDENTE_VALUE');
//        $form->removeElement('DOCM_CD_LOTACAO_GERADORA');
//        $form->removeElement('DOCM_CD_LOTACAO_GERADORA_VALUE');
//        $form->removeElement('SSOL_ID_TIPO_CAD');
        $form->removeElement('MOFA_ID_FASE');
//        $form->removeElement('SERVICO');
//        $form->removeElement('SSER_ID_SERVICO');
//        $form->removeElement('CATE_ID_CATEGORIA');
//        $form->removeElement('SSER_DS_SERVICO');
//        $form->removeElement('DOCM_NR_DOCUMENTO');
//        $form->removeElement('DOCM_NR_DOCUMENTO');
        $form->removeElement('SOMENTE_PRINCIPAL');
//        $form->removeElement('SGRS_ID_GRUPO');
//        $form->removeElement('DATA_INICIAL_CADASTRO');
//        $form->removeElement('DATA_FINAL_CADASTRO');
        
        $filtrar = $form->getElement('Filtrar');
        $form->removeElement('Filtrar');
        
        $form->getElement('DATA_INICIAL')->setlabel("Data inicial - Avaliação:");
        $form->getElement('DATA_FINAL')->setlabel("Data final - Avaliação:");
        $form->getElement('SSOL_CD_MATRICULA_ATENDENTE')->setlabel("Atendente Baixa:");
        
        $data_inicial_baixa = new Zend_Form_Element_Text('DATA_INICIAL_BAIXA');
        $data_inicial_baixa->setValue('')
                    ->setLabel('Data inicial - Baixa:');
       
        $data_final_baixa = new Zend_Form_Element_Text('DATA_FINAL_BAIXA');
        $data_final_baixa->setValue('')
                    ->setLabel('Data final - Baixa:');
        
        $form->addElements(array($data_inicial_baixa,$data_final_baixa));
        
        $form->addElement($filtrar);
        
        $formConformidade = new Sosti_Form_Conformidade ();
        $formConformidade->setAction('salvaconformidade');
        /**
         * Variáves de Sessão
         */
        $Ns_Controlesla_index = new Zend_Session_Namespace('Ns_Controlesla_index');
        /**
         * Pega o id da caixa e a descrição para gravar nas variáveis de sessão
         */
        if ($this->_getParam('idcaixa') != '' ) {
            $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
            $dataCaixa = $caixaEntrada->fetchRow("CXEN_ID_CAIXA_ENTRADA = " . $this->_getParam('idcaixa'));
            $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getGrupoAtendimentoByCaixa($dataCaixa["CXEN_ID_CAIXA_ENTRADA"]);
            
            $Ns_Controlesla_index->descricaocaixa = $dataCaixa["CXEN_DS_CAIXA_ENTRADA"];
            $Ns_Controlesla_index->idcaixa = $this->_getParam('idcaixa');
            
            $form->removeElement('SGRS_ID_GRUPO');
            $form_valores_padrao = $form->getValues();
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SserServico = $SosTbSserServico->getServicoPorGrupo($SgrsGrupoServico[0]["SGRS_ID_GRUPO"], 'SSER_DS_SERVICO ASC');
            $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
            $sser_id_servico->addMultiOptions(array('' => ''));
            foreach ($SserServico as $SserServico_p):
                $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
            endforeach;
            
            $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
            $Categorias = $cateCategoria->fetchAll("CATE_ID_GRUPO = ".$SgrsGrupoServico[0]["SGRS_ID_GRUPO"],2);
            $Categorias = $Categorias->toArray();
            $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
            $cont = 0;
            foreach ($Categorias as $Categorias_p):
                $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
                $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
                $cont++;
            endforeach;
            $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
            $this->view->categorias = $Categorias;
            
        }
        if ($this->_getParam('caixa') == 'secao') {
            $userNs = new Zend_Session_Namespace('userNs');
            $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($userNs->siglasecao, $userNs->codlotacao);
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('AC', '3');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('AM', '4');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('AP', '5');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('BA', '6');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('DF', '7');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('GO', '8');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('MA', '9');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('MG', '10');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('MT', '11');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('PA', '12');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('PI', '13');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('RO', '14');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('TO', '16');
            
            $form->removeElement('SGRS_ID_GRUPO');
            $form_valores_padrao = $form->getValues();
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SserServico = $SosTbSserServico->getServicoPorGrupo($SgrsGrupoServico[0]["SGRS_ID_GRUPO"], 'SSER_DS_SERVICO ASC');
            $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
            $sser_id_servico->addMultiOptions(array('' => ''));
            foreach ($SserServico as $SserServico_p):
                $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
            endforeach;
            
            $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
            $Categorias = $cateCategoria->fetchAll("CATE_ID_GRUPO = ".$SgrsGrupoServico[0]["SGRS_ID_GRUPO"],2);
            $Categorias = $Categorias->toArray();
            $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
            $cont = 0;
            foreach ($Categorias as $Categorias_p):
                $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
                $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
                $cont++;
            endforeach;
            $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
            $this->view->categorias = $Categorias;

            $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
            $dataCaixa = $caixaEntrada->fetchRow("CXEN_ID_CAIXA_ENTRADA = " . $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"]);
            $Ns_Controlesla_index->descricaocaixa = $dataCaixa["CXEN_DS_CAIXA_ENTRADA"];
            $Ns_Controlesla_index->idcaixa = $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"];
        }
      
        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array('direcao' => 'ASC', 'ordem' => "TO_DATE(DATA_AVALIACAO, 'DD/MM/YYYY HH24:MI:SS') ASC,TO_DATE(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS')ASC, DOCM_NR_DOCUMENTO", 'itemsperpage' => 15, 'page' => 1);
        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);


        $form_valores_padrao = $form->getValues();
        /**
         * Para zerar o filtro
         */
        if ($this->_getParam('nova') === '1') {
            unset($Ns_Controlesla_index->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        /**
         * Submissão do filtro
         */
        $post = $this->getRequest()->getPost();
        if (($post["Filtrar"] == "Filtrar") || ($post["Filtrar2"] == "Filtrar")) {
            $data_pesq = $this->getRequest()->getPost();

            /**
             * Validação de filtro Vazio
             */
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $this->view->title = "CONTROLE DE CONFORMIDADE - " . $Ns_Controlesla_index->descricaocaixa;
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $Ns_Controlesla_index->data_pesq = $this->getRequest()->getPost();
            } else {
                /**
                 * Populando o formulário inválido
                 */
                $form->populate($data_pesq);
                $this->view->form = $form;
                $this->view->title = "CONTROLE DE CONFORMIDADE - " . $Ns_Controlesla_index->descricaocaixa;
                return;
            }
        }
        


        /*
         * Aplicação do filtro caso ele seja válido
         */
        $data_pesq = $Ns_Controlesla_index->data_pesq;
        $post_data_pesq = $data_pesq;
        
        if($post_data_pesq['DATA_INICIAL'] == null && $post_data_pesq['DATA_FINAL'] <> null){
            $form->getElement('DATA_INICIAL')->addError('ATENÇÃO: É necessário preencher data inicial e final de cada par de datas, para que a pesquisa seja efetuada corretamente.');
        }else if($post_data_pesq['DATA_INICIAL'] <> null && $post_data_pesq['DATA_FINAL'] == null){
           $form->getElement('DATA_FINAL')->addError('ATENÇÃO: É necessário preencher data inicial e final de cada par de datas, para que a pesquisa seja efetuada corretamente.');
        }
        
        if($post_data_pesq['DATA_INICIAL_CADASTRO'] == null && $post_data_pesq['DATA_FINAL_CADASTRO'] <> null){
            $form->getElement('DATA_INICIAL_CADASTRO')->addError('ATENÇÃO: É necessário preencher data inicial e final de cada par de datas, para que a pesquisa seja efetuada corretamente.');
        }else if($post_data_pesq['DATA_INICIAL_CADASTRO'] <> null && $post_data_pesq['DATA_FINAL_CADASTRO'] == null){
           $form->getElement('DATA_FINAL_CADASTRO')->addError('ATENÇÃO: É necessário preencher data inicial e final de cada par de datas, para que a pesquisa seja efetuada corretamente.');
        }
        
        if($post_data_pesq['DATA_INICIAL_BAIXA'] == null && $post_data_pesq['DATA_FINAL_BAIXA'] <> null){
            $form->getElement('DATA_INICIAL_BAIXA')->addError('ATENÇÃO: É necessário preencher data inicial e final de cada par de datas, para que a pesquisa seja efetuada corretamente.');
        }else if($post_data_pesq['DATA_INICIAL_BAIXA'] <> null && $post_data_pesq['DATA_FINAL_BAIXA'] == null){
           $form->getElement('DATA_FINAL_BAIXA')->addError('ATENÇÃO: É necessário preencher data inicial e final de cada par de datas, para que a pesquisa seja efetuada corretamente.');
        }
        if (!is_null($data_pesq)) {
            /**
             * Auxilia a view a tratar a ausencia de registros e esconder botões
             */
            $this->view->ultima_pesq = true;

            /* paginação */
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            /* Ordenação das paginas */
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

            /**
             * Tratamento dos dados para passar na array params
             */
            $mat_aux = explode(' - ', $data_pesq["DOCM_CD_MATRICULA_CADASTRO_VALUE"]);
            $data_pesq["DOCM_CD_MATRICULA_CADASTRO"] = $mat_aux[0];
            
            $mat_atend_aux = explode(' - ', $data_pesq["SSOL_CD_MATRICULA_ATENDENTE_VALUE"]);
            $data_pesq["SSOL_CD_MATRICULA_ATENDENTE"] = $mat_atend_aux[0];
            
            
            $unid_aux = explode(' - ', $data_pesq["DOCM_CD_LOTACAO_GERADORA_VALUE"]);
            ( array_key_exists(2,$unid_aux) ) ? ($data_pesq["DOCM_CD_LOTACAO_GERADORA"] = $unid_aux[2]) : '';
            ( array_key_exists(3,$unid_aux) ) ? ($data_pesq["DOCM_SG_SECAO_GERADORA"] = $unid_aux[3]) : '';
            
            
            $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
            $dataCaixa = $caixaEntrada->fetchRow("CXEN_ID_CAIXA_ENTRADA = " . $Ns_Controlesla_index->idcaixa);
            $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getGrupoAtendimentoByCaixa($dataCaixa["CXEN_ID_CAIXA_ENTRADA"]);
            
            $form->removeElement('SGRS_ID_GRUPO');
            $form_valores_padrao = $form->getValues();
            $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
            $SserServico = $SosTbSserServico->getServicoPorGrupo($SgrsGrupoServico[0]["SGRS_ID_GRUPO"], 'SSER_DS_SERVICO ASC');
            $sser_id_servico = $form->getElement('SSER_ID_SERVICO');
            $sser_id_servico->addMultiOptions(array('' => ''));
            foreach ($SserServico as $SserServico_p):
                $sser_id_servico->addMultiOptions(array($SserServico_p["SSER_ID_SERVICO"] => $SserServico_p["SSER_DS_SERVICO"]));
            endforeach;
            
            $cateCategoria = new Application_Model_DbTable_SosTbCateCategoria();
            $Categorias = $cateCategoria->fetchAll("CATE_ID_GRUPO = ".$SgrsGrupoServico[0]["SGRS_ID_GRUPO"],2);
            $Categorias = $Categorias->toArray();
            $cate_id_categoria = $form->getElement('CATE_ID_CATEGORIA');
            $cont = 0;
            foreach ($Categorias as $Categorias_p):
                $cores[$cont] = $Categorias_p["CATE_DS_DESCRICAO_COR"];
                $cate_id_categoria->addMultiOptions(array($Categorias_p["CATE_ID_CATEGORIA"] => $Categorias_p["CATE_NO_CATEGORIA"]));
                $cont++;
            endforeach;
            $cate_id_categoria->setAttrib('cores', Zend_Json::encode($cores));
            $this->view->categorias = $Categorias;
            
            
            /**
             * Chama o método de pesquisa
             */
            $dados = new Application_Model_DbTable_SosTbSinsIndicNivelServ();          
            $rows = $dados->getCaixaSemNivelControleSLA($Ns_Controlesla_index->idcaixa, $data_pesq, $order, true);
            /**
             * Configura o Zend paginator
             */
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage($itemCountPerPage);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');

            /**
             * Popula o filtro com a última pesquisa
             */
            $form->populate($post_data_pesq);
        }
        $this->view->form = $form;
        $this->view->formConformidade = $formConformidade;
        $this->view->title = "CONTROLE DE CONFORMIDADE - " . $Ns_Controlesla_index->descricaocaixa;
    }
    
    /**
     * Inseri conformidade nas solicitações de TI
     * @return type void
     */
    public function inserirconformidadeAction() {
        
        /**
         * Namespaces 
         */
        $Ns_Controlesla_index = new Zend_Session_Namespace('Ns_Controlesla_index');
        $Ns_gestaodemandasinfraestrutura_inserirconformidade = new Zend_Session_Namespace('Ns_gestaodemandasinfraestrutura_inserirconformidade');
        /**
         * Models 
         */
        $conformidadeModel = new Application_Model_DbTable_SosTbSotctpnConformidade();
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
         /**
         * Forms
         */
        $formConformidade = new Sosti_Form_Conformidade();
        /**
         * Manipulação do form 
         */
        $Indicador = $SadTbCxgsGrupoServico->getIndicadorByCaixa($Ns_Controlesla_index->idcaixa);
        $rows = $conformidadeModel->getConformidadesPorIndicador(null, true, $Indicador['SINS_ID_INDICADOR']);
        
        $CONFORMIDADE = $formConformidade->getElement('SCONF_ID_TIPO');
        $CONFORMIDADE->setLabel('Selecione tipo de não conformidade:');
        foreach ($rows as $data):
            $CONFORMIDADE->addMultiOptions((array($data["SOTC_ID_NAO_CONFORMIDADE"] => $data["SOTC_DS_CONFORMIDADE"])));
        endforeach;
        $formConformidade->setAction('salvaconformidade');
        
        if ($this->getRequest()->isPost()) {
            $dataSolicitacoes = $this->_getParam('solicitacao');
            $rows = $dataSolicitacoes;
            $Ns_gestaodemandasinfraestrutura_inserirconformidade->dadospost = $dataSolicitacoes;
            $this->view->data = $rows;
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            $itemCountPerPage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($itemCountPerPage);
            $this->view->data = $paginator;
        }
        $this->view->title = "INSERIR NÃO CONFORMIDADE - " . $Ns_Controlesla_index->descricaocaixa;
        $this->view->form = $formConformidade;
    }
    
    
     /**
     * Salva as não conformidade para os documentos selecionados
     */
    public function salvaconformidadeAction() {

         /**
         * Models 
         */
        $conformidadeModel = new Application_Model_DbTable_SosTbSotctpnConformidade();
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
        

        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Controlesla_index = new Zend_Session_Namespace('Ns_Controlesla_index');
        $Ns_gestaodemandasinfraestrutura_inserirconformidade = new Zend_Session_Namespace('Ns_gestaodemandasinfraestrutura_inserirconformidade');
        //$this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);
        $ModelMovimConformidade = new Application_Model_DbTable_SosTbMvcoConformidade();
        $sys = new Application_Model_DbTable_Dual ();
        $formConformidade = new Sosti_Form_Conformidade ();
        
        $Indicador = $SadTbCxgsGrupoServico->getIndicadorByCaixa($Ns_Controlesla_index->idcaixa);
        $rows = $conformidadeModel->getConformidadesPorIndicador(null, true, $Indicador['SINS_ID_INDICADOR']);
        
        $CONFORMIDADE = $formConformidade->getElement('SCONF_ID_TIPO');
        $CONFORMIDADE->setLabel('Selecione tipo de não conformidade:');
        foreach ($rows as $data):
            $CONFORMIDADE->addMultiOptions((array($data["SOTC_ID_NAO_CONFORMIDADE"] => $data["SOTC_DS_CONFORMIDADE"])));
        endforeach;
        
        
        $userNs = new Zend_Session_Namespace('userNs');
        if ($this->getRequest()->isPost()) {
            $dataPost = $this->_getAllParams();
            $dataCaixa = $Ns_gestaodemandasinfraestrutura_inserirconformidade->dadospost;
        }
        if ($formConformidade->isValid($dataPost)) {

            foreach ($dataCaixa as $dataRaw) {
                $data = Zend_Json::decode($dataRaw);

                //$documentos = explode(":",$values);// numero do documento[0] : id do documento[1] : id da movimentação[2].
                //$docs = $documentos[0].", "; //string com documentos pra inserir na flash message

                $dadosInsert['MVCO_ID_MOVIMENTACAO'] = $data['MOFA_ID_MOVIMENTACAO']; // id da movimentação
                $dadosInsert['MVCO_ID_NAO_CONFORMIDADE'] = $dataPost ['SCONF_ID_TIPO'];
                $dadosInsert['MVCO_DS_JUSTIF_N_CONFORMIDADE'] = $dataPost['COMENTARIO'];
                $dadosInsert['MVCO_DH_INCLUSAO'] = $sys->sysdate();
                $dadosInsert['MVCO_IC_ATIVO_INATIVO'] = "S";
                $dadosInsert['MVCO_CD_MATRICULA_INCLUSAO'] = $userNs->matricula;
                
                try {
                    $rowExists = $ModelMovimConformidade->find($dadosInsert['MVCO_ID_NAO_CONFORMIDADE'], $dadosInsert['MVCO_ID_MOVIMENTACAO'])->current();
                    if ($rowExists) {
                        $rowExists->setFromArray($dadosInsert)->save();
                    } else {
                        $ModelMovimConformidade->createRow($dadosInsert)->save();
                    }
                    $this->_helper->flashMessenger(array('message' => 'Não conformidade foi inserida na solicitação: <b>' . $data['DOCM_NR_DOCUMENTO'] . '</b>', 'status' => 'success'));
                    
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => 'Não foi possivel inserir não conformidade na solicitaçõe: <b>' . $data['DOCM_NR_DOCUMENTO'] . ' </b> <p>' . strip_tags($e->getMessage()) . '<p>', 'status' => 'error'));
                }
            }
            return $this->_helper->_redirector('controlesla', 'sla', 'sosti', array('idcaixa' => $Ns_Controlesla_index->idcaixa));
        } else {

            $rows = $Ns_gestaodemandasinfraestrutura_inserirconformidade->dadospost;
            $this->view->data = $rows;
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            $itemCountPerPage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($itemCountPerPage);
            $this->view->data = $paginator;
            $this->view->title = "Inserir não conformidade";
            $this->view->form = $formConformidade;
            return $this->render('inserirconformidade');
        }

        return $this->_helper->_redirector('controlesla', 'sla', 'sosti', array('idcaixa' => $Ns_Controlesla_index->idCaixa));
    }
    
    public function removerconformidadeAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $Ns_Controlesla_index = new Zend_Session_Namespace('Ns_Controlesla_index');
        $Ns_Controlesla_index = new Zend_Session_Namespace('Ns_Controlesla_index');
        $formConformidade = new Sosti_Form_Conformidade ();
        $modelConformidade = new Application_Model_DbTable_SosTbMvcoConformidade ();
        $Ns_gestaodemandasinfraestrutura_removerconformidade = new Zend_Session_Namespace('Ns_gestaodemandasinfraestrutura_removerconformidade');
        $formConformidade->setAction('desvicularconformidadedasolicitacao');
        $this->view->form = $formConformidade;
        if ($this->getRequest()->isPost()) {
            $dadosPost = $this->_getAllParams();
            $Ns_gestaodemandasinfraestrutura_removerconformidade->dadospost = $dadosPost['solicitacao'];
            foreach ($dadosPost['solicitacao'] as $value) {
                $dados = Zend_Json::decode($value);
                $dadosBuscar .= $dados['MOFA_ID_MOVIMENTACAO'] . ",";
            }
            $idsMovimentacoes = substr($dadosBuscar, 0, -1);

            $rows = $modelConformidade->getNaoConformidadesParaRemover($idsMovimentacoes);
            if (!$rows) {
                $this->_helper->flashMessenger(array('message' => 'Solicitação não possui nenhuma não conformidade. Selecione outra.', 'status' => 'notice'));
                return $this->_helper->_redirector('controlesla', 'sla', 'sosti');
            }
            $conformidadesSelect = new Zend_Form_Element_Multiselect('SCONF_ID_TIPO');
            foreach ($rows as $data):
                $conformidadesSelect->addMultiOptions(array($data['SOTC_ID_NAO_CONFORMIDADE'] => $data['SOTC_DS_CONFORMIDADE']));
            endforeach;
            $formConformidade->setAttrib('style', 'height:100px');
            $conformidadecombo['SCONF_ID_TIPO'] = $rows['SOTC_ID_NAO_CONFORMIDADE'];

            $formConformidade->populate($conformidadecombo);
            $conformidadesSelect->setLabel('Selecione as conformidades pra remover');
            $formConformidade->addElement($conformidadesSelect);
            $this->view->title = "REMOVER NÃO CONFORMIDADE - " . $Ns_Controlesla_index->descricaocaixa;
            $dataSolicitacoes = $this->_getParam('solicitacao');
            $rows = $dataSolicitacoes;
            $Ns_gestaodemandasinfraestrutura_removerconformidade->dadospost = $dataSolicitacoes;
            $this->view->data = $rows;
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
            $itemCountPerPage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($itemCountPerPage);
            $this->view->data = $paginator;
        }
    }

    /**
     * Remove uma conformidade de uma solicitação
     * 
     */
    public function desvicularconformidadedasolicitacaoAction() {
        $userNs = new Zend_Session_Namespace('userNs');
        $formConformidade = new Sosti_Form_Conformidade ();

        $modelConformidade = new Application_Model_DbTable_SosTbMvcoConformidade ();

        $Ns_gestaodemandasinfraestrutura_removerconformidade = new Zend_Session_Namespace('Ns_gestaodemandasinfraestrutura_removerconformidade');
        if ($this->getRequest()->isPost()) {
            $solicitacao = $Ns_gestaodemandasinfraestrutura_removerconformidade->dadospost;
            $dadosPost = $this->_getParam('SCONF_ID_TIPO');
            $comentario = $this->_getParam('COMENTARIO');
            $dadosform['COMENTARIO'] = $this->_getParam('COMENTARIO');
            //loop em todas as solicitações selecionadas
            if ($formConformidade->isValid($dadosform)) {
                foreach ($solicitacao as $value) {
                    $movimentacaoID_Raw = Zend_Json::decode($value);
                    $movimentacaoID = $movimentacaoID_Raw ['MOFA_ID_MOVIMENTACAO'];
                    $documentoID = $movimentacaoID_Raw ['MOFA_ID_MOVIMENTACAO'];
                    $pks['MVCO_ID_MOVIMENTACAO'] = $movimentacaoID; // primary key na tabela SOS_TB_MVCO_MOVIM_N_CONFORM
                    //loop nas conformidades seleciondas pra desvincular das solicitações
                    foreach ($dadosPost as $tipoConformidade) {
                        $pks['MVCO_ID_NAO_CONFORMIDADE'] = $tipoConformidade; // primary key na tabela SOS_TB_MVCO_MOVIM_N_CONFORM

                        $existVinculo = $modelConformidade->find($pks['MVCO_ID_NAO_CONFORMIDADE'], $pks['MVCO_ID_MOVIMENTACAO'])->current();

                        //se existir vinculo update na tabela
                        if ($existVinculo) {
                            $atualizaVinculo['MVCO_IC_ATIVO_INATIVO'] = "N";
                            $atualizaVinculo['MVCO_CD_MATRIC_ATIVO_INATIVO'] = $userNs->matricula;
                            $atualizaVinculo['MVCO_DS_JUSTIF_N_CONFORM_INAT'] = $comentario; //TODO:mudar campo para descrição quando desativar.
                            $conformidadeDescricao = $modelConformidade->getconformidadeDescricaopelaID($tipoConformidade);
                        }
                        try {
                            if ($existVinculo) {
                                $existVinculo->setFromArray($atualizaVinculo)->save();
                                $this->_helper->flashMessenger(array('message' => 'Conformidade(' . $conformidadeDescricao['SOTC_DS_CONFORMIDADE'] . ') foi removida da solicitação: <b>' . $movimentacaoID_Raw ['DOCM_NR_DOCUMENTO'] . '</b>', 'status' => 'success'));
                            }
                        } catch (Exception $e) {
                            $this->_helper->flashMessenger(array('message' => 'Não foi possivel remover a conformidade(<strong>' . $conformidadeDescricao['SOTC_DS_CONFORMIDADE'] . '</strong>) da solicitaçõe: <b>' . $movimentacaoID_Raw ['DOCM_NR_DOCUMENTO'] . ' </b> <p>' . strip_tags($e->getMessage()) . '<p>', 'status' => 'error'));
                        }
                    }
                }
                return $this->_helper->_redirector('controlesla', 'sla', 'sosti', array('idcaixa' => $Ns_Controlesla_index->idcaixa));
            } else {

                $conformidadesSelect = new Zend_Form_Element_Multiselect('SCONF_ID_TIPO');
                $conformidadesSelect->setDescription('Conformidades Disponíveis');
                $salvar = $formConformidade->getElement('Salvar');
                $SUBMIT = new Zend_Form_Element_Submit('Salvar');
                $formConformidade->addElement($conformidadesSelect);
                foreach ($Ns_gestaodemandasinfraestrutura_removerconformidade->dadospost as $value) {
                    $dados = Zend_Json::decode($value);
                    $dadosBuscar .= $dados['MOFA_ID_MOVIMENTACAO'] . ",";
                }
                $idsMovimentacoes = substr($dadosBuscar, 0, -1);

                $rows = $modelConformidade->getNaoConformidadesParaRemover($idsMovimentacoes);
                foreach ($rows as $data):
                    $conformidadesSelect->addMultiOptions(array($data['SOTC_ID_NAO_CONFORMIDADE'] => $data['SOTC_DS_CONFORMIDADE']));
                endforeach;
                $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
                $itemCountPerPage = Zend_Filter::filterStatic($this->_getParam('itemsperpage', 15), 'int');
                $paginator = Zend_Paginator::factory($Ns_gestaodemandasinfraestrutura_removerconformidade->dadospost);
                $paginator->setCurrentPageNumber($page);
                $paginator->setItemCountPerPage($itemCountPerPage);
                $this->view->data = $paginator;
                $this->view->title = "Remover Conformidade";
                $this->view->form = $formConformidade;
                $this->render('removerconformidade');
            }
        }
    }

    public function autorizaextensaoprazoAction() {
        /**
         * Forms
         */
        $form = new Sosti_Form_CaixaSolicitacao();
        $form->removeElement('SSOL_ID_DOCUMENTO');
        $form->removeElement('DOCM_NR_DOCUMENTO');
        $form->removeElement('DOCM_CD_MATRICULA_CADASTRO');
        $form->removeElement('DOCM_CD_MATRICULA_CADASTRO_VALUE');
        $form->removeElement('SSOL_CD_MATRICULA_ATENDENTE');
        $form->removeElement('SSOL_CD_MATRICULA_ATENDENTE_VALUE');
        $form->removeElement('DOCM_CD_LOTACAO_GERADORA');
        $form->removeElement('DOCM_CD_LOTACAO_GERADORA_VALUE');
        $form->removeElement('SSOL_ID_TIPO_CAD');
        $form->removeElement('MOFA_ID_FASE');
        $form->removeElement('SERVICO');
        $form->removeElement('SSER_ID_SERVICO');
        $form->removeElement('CATE_ID_CATEGORIA');
        $form->removeElement('SSER_DS_SERVICO');
        $form->removeElement('DOCM_NR_DOCUMENTO');
        $form->removeElement('DOCM_NR_DOCUMENTO');

        $formConformidade = new Sosti_Form_Conformidade ();
        $util = new App_Util();

        $formConformidade->setAction('salvaconformidade');
        /**
         * Variáves de Sessão
         */
        //$Ns_gestaodemandasinfraestrutura_inserirconformidade = new Zend_Session_Namespace ( 'Ns_gestaodemandasinfraestrutura_inserirconformidade' );
        $Ns_sla_autorizaextensaoprazo = new Zend_Session_Namespace('Ns_sla_autorizaextensaoprazo');
        /**
         * Pega o id da caixa e a descrição para gravar nas variáveis de sessão
         */
        if ($this->_getParam('idcaixa') != '') {
            $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
            $dataCaixa = $caixaEntrada->fetchRow("CXEN_ID_CAIXA_ENTRADA = " . $this->_getParam('idcaixa'));
            $Ns_sla_autorizaextensaoprazo->descricaocaixa = $dataCaixa["CXEN_DS_CAIXA_ENTRADA"];
            $Ns_sla_autorizaextensaoprazo->idcaixa = $this->_getParam('idcaixa');
        }
        if ($this->_getParam('caixa') == 'secao') {
            $userNs = new Zend_Session_Namespace('userNs');
            $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();
            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao($userNs->siglasecao, $userNs->codlotacao);
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('AC', '3');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('AM', '4');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('AP', '5');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('BA', '6');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('DF', '7');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('GO', '8');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('MA', '9');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('MG', '10');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('MT', '11');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('PA', '12');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('PI', '13');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('RO', '14');
//            $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao('TO', '16');
            
            
            
            
            $caixaEntrada = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
            $dataCaixa = $caixaEntrada->fetchRow("CXEN_ID_CAIXA_ENTRADA = " . $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"]);
            $Ns_sla_autorizaextensaoprazo->descricaocaixa = $dataCaixa["CXEN_DS_CAIXA_ENTRADA"];
            $Ns_sla_autorizaextensaoprazo->idcaixa = $SgrsGrupoServico[0]["CXEN_ID_CAIXA_ENTRADA"];
        }
        $ns = 'ns_' . md5($this->getRequest()->getControllerName() . $this->getRequest()->getActionName());
        $variaveisSessaoPadrao = array('direcao' => 'ASC', 'ordem' => 'MOVI_DH_ENCAMINHAMENTO', 'itemsperpage' => 15, 'page' => 1);
        $varSessoes = new App_SecaoPaginator($ns, $variaveisSessaoPadrao);

        $form->removeElement('SGRS_ID_GRUPO');
        $form_valores_padrao = $form->getValues();
        $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
        $SserServico = $SosTbSserServico->getServicoPorGrupo(3, 'SSER_DS_SERVICO ASC');



        /**
         * Para zerar o filtro
         */
        if ($this->_getParam('nova') === '1') {
            unset($Ns_sla_autorizaextensaoprazo->data_pesq);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }

        /**
         * Submissão do filtro
         */
        $post = $this->getRequest()->getPost();
        if (isset($post["Filtrar"]) && $post["Filtrar"] == "Filtrar") {
            $data_pesq = $this->getRequest()->getPost();

            /**
             * Validação de filtro Vazio
             */
            $form->populate($data_pesq);
            if ($form_valores_padrao == $form->getValues()) {
                $this->view->form = $form;
                $this->view->title = "GERENCIAR EXTENSÃO DE PRAZO - " . $Ns_sla_autorizaextensaoprazo->descricaocaixa;
                $msg_to_user = "O preenchimento de um dos campos do filtro é necessário.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }

            /**
             * Verificação das validações do form gravação na sessão
             */
            if ($form->isValid($data_pesq)) {
                $Ns_sla_autorizaextensaoprazo->data_pesq = $this->getRequest()->getPost();
            } else {
                /**
                 * Populando o formulário inválido
                 */
                $form->populate($data_pesq);
                $this->view->form = $form;

                $this->view->title = "GERENCIAR EXTENSÃO DE PRAZO - " . $Ns_sla_autorizaextensaoprazo->descricaocaixa;
                return;
            }
        }

        /*
         * Aplicação do filtro caso ele seja válido
         */
        $data_pesq = $Ns_sla_autorizaextensaoprazo->data_pesq;
        $post_data_pesq = $data_pesq;
        $post_data_pesq = date('Y-m-d');
        if (!is_null($data_pesq)) {
            /**
             * Auxilia a view a tratar a ausencia de registros e esconder botões
             */
            $this->view->ultima_pesq = true;

            /* paginação */
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage();
            /* Ordenação das paginas */
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');

            /**
             * Chama o método de pesquisa
             */
            $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $rows = $dados->getCaixaSemNivelPesq($Ns_sla_autorizaextensaoprazo->idcaixa, $data_pesq, $order);

            /**
             * Configura o Zend paginator
             */
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage($itemCountPerPage);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');

            /**
             * Popula o filtro com a última pesquisa
             */
            $form->populate($post_data_pesq);
        } else {
            /**
             * Caso não exista filtro execulta a listagem normal sem filtro
             */
            /* paginação */
            $page = $varSessoes->getPage();
            $itemCountPerPage = $varSessoes->getItemsperpage() == 15 ? 50 : $varSessoes->getItemsperpage();
            /* Ordenação das paginas */
            $order_column = $varSessoes->getOrdem();
            $order_direction = $varSessoes->getDirecao();
            $order = $order_column . ' ' . $order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /* Ordenação */

            /**
             * Chama o método padrão da caixa sem filtro
             */
            $dados = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
            $rows = $dados->getCaixaSemNivelExtensaoPrazo($Ns_sla_autorizaextensaoprazo->idcaixa, $order);
            /**
             * Configura o Zend paginator
             */
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage($itemCountPerPage);

            $this->view->title = "GERENCIAR EXTENSÃO DE PRAZO - " . $Ns_sla_autorizaextensaoprazo->descricaocaixa;
            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
        }

        $this->view->form = $form;
        $this->view->formConformidade = $formConformidade;
        $this->view->title = "GERENCIAR EXTENSÃO DE PRAZO - " . $Ns_sla_autorizaextensaoprazo->descricaocaixa;
        //Se a autorização de extenção de prazo for referente a solicitações
        //presentes na caixa de desenvolvimento e sustentação do tribunal primeira instancia
        //então utilizar um script de view diferenciado
        if($Ns_sla_autorizaextensaoprazo->idcaixa == Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA){
            $this->render('autorizaextensaoprazocomatual');
        }
    }
    
    public function saveautorizaextensaoprazoAction() {
        /**
         *Variáves de sessão 
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $NsActionName = $this->getRequest()->getModuleName().$this->getRequest()->getControllerName().$this->getRequest()->getActionName();
        $NsAction = new Zend_Session_Namespace($NsActionName);
        /**
         *Forms 
         */
        $form = new Sosti_Form_SolicitacaoExtenderPrazo();
        $form->removeElement('SSPA_DT_PRAZO');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            if ($data['acao'] && isset($data['acao']) && $data['acao'] == 'Gerenciar Extensão de Prazo') {
                $NsAction->dadosCaixa = $data;
                $NsAction->dados = $data['solicitacao'];
                $this->view->data = $data['solicitacao'];
                $this->view->title = "AUTORIZAR A EXTENSÃO - " . $NsAction->dadosCaixa['title'];
                $dataAux = $data['solicitacao'];
                $decodeData = Zend_Json::decode($dataAux[0]);
                $NsAction->idCaixa = $decodeData["MODE_ID_CAIXA_ENTRADA"];
            } else {
                if ($form->isValid($data)) {
                    
                    /**Aplica Filtros - Mantem Post*/
                    $data = array_merge($this->getRequest()->getPost(),$form->populate($this->getRequest()->getPost())->getValues());/*Aplica Filtros - Mantem Post*/
                    /**Aplica Filtros - Mantem Post*/
                    
                    foreach ($NsAction->dados as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $idDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
                        /**
                         * Dados para inserir na tabela SAD_TB_MOFA_MOVI_FASE
                         */
                        $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        $dataMofaMoviFase["MOFA_ID_FASE"] = 1039; /* AVALIAÇÃO DA EXTENSÃO DE PRAZO PARA SOLICITAÇÃO DE TI */
                        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = $data["SOPR_DS_DESCRICAO_PRAZO"];
                        /**
                         * Dados para inserir na tabela SOS_TB_SSPA_SOLIC_PRAZO_ATEND
                         */
                        $dataSspaSolicPrazoAtend["SSPA_ID_MOVIMENTACAO"] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                        //$dataSspaSolicPrazoAtend["SSPA_DT_PRAZO"] = new Zend_Db_Expr("TO_DATE('" . substr($data["SSPA_DT_PRAZO"], 0, -1) . ":00" . "','dd/mm/yyyy HH24:MI:SS')");
                        $dataSspaSolicPrazoAtend["SSPA_ID_DOCUMENTO"] = $idDocumento;
                        $dataSspaSolicPrazoAtend["SSPA_IC_CONFIRMACAO"] = $data["SSPA_IC_CONFIRMACAO"];
                        /**
                         * Método para incluir a autorização da extensão de prazo
                         */
                        $espera = new Application_Model_DbTable_SosTbSspaSolicPrazoAtend();
                        try {
                            $espera->autorizaPrazoSolicitacao($idDocumento, $dataMofaMoviFase, $dataSspaSolicPrazoAtend);
                            $msg_to_user = "Solicitação nº: $nrdocumento com extensão de prazo avaliada!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        } catch (Exception $exc) {
                            $msg_to_user = "Não foi possível avaliar a extensão de prazo da solicitação nº: $nrdocumento. </br>Código do erro: ".$exc->getMessage();
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                        }
                    }
                    /*redirecionamento*/
                    $this->_helper->_redirector($NsAction->dadosCaixa['action'],$NsAction->dadosCaixa['controller'],$NsAction->dadosCaixa['module'], array('idcaixa' => $NsAction->idCaixa));
                    /***redirecionamento*/
                } else {
                    $this->view->data = $NsAction->dados;
                    $this->view->title = "AUTORIZAR A EXTENSÃO - " . $NsAction->dadosCaixa['title'];
                    
                    $form->getElement('SOPR_DS_DESCRICAO_PRAZO')->removeFilter('HtmlEntities');
                    if($form->getElement('SOPR_DS_DESCRICAO_PRAZO')->hasErrors()){
                        $form->getElement('SOPR_DS_DESCRICAO_PRAZO')->addError('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.');
                    }
                    $form->populate($data);
                }
            }
        }
        $this->view->form = $form;
    }
    
    public function desconsiderarslaAction()
    {
        $form = new Sosti_Form_RetirarSla();
        $this->view->form = $form;
        $limite = new Application_Model_DbTable_Dual();
        $this->view->sysdate = $limite->sysdateDb();
               
        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->getRequest()->getPost(),$form->populate($this->getRequest()->getPost())->getValues());/*Aplica Filtros - Mantem Post*/
            
            if($data['acao'] && isset($data['acao']) && $data['acao'] == 'Desconsiderar SLA' ) {
                $userNs = new Zend_Session_Namespace('userNs');
                $Ns_sla_desconsidera = new Zend_Session_Namespace('Ns_sla_desconsidera');
                $Ns_sla_desconsidera->title = "DESCONSIDERAR SLA - ".$data['title'];
                $Ns_sla_desconsidera->data = $data['solicitacao'];
                $Ns_sla_desconsidera->module = $data['module'];
                $Ns_sla_desconsidera->controller = $data['controller'];
                $Ns_sla_desconsidera->action = $data['action'];
                $Ns_sla_desconsidera->indicador = $data['indicador'];
                
                $this->view->data = $Ns_sla_desconsidera->data;
                $id = $solicitacao_array[0];
                $this->view->title = $Ns_sla_desconsidera->title;
                $this->view->form = $form;
                
                
            } else {
                if ($form->isValid($data)) {
                    $userNs = new Zend_Session_Namespace('userNs');
                    $Ns_sla_desconsidera = new Zend_Session_Namespace('Ns_sla_desconsidera');
                    foreach ($Ns_sla_desconsidera->data as $d) {
                        $dados_input = Zend_Json::decode($d);
                        $nrdocumento = $dados_input["DOCM_NR_DOCUMENTO"];
                        $solicitacoesEncaminhadas = $solicitacoesEncaminhadas.', '.$nrdocumento;
                        $idDocumento = $dados_input["SSOL_ID_DOCUMENTO"];
                       
                        if ($data["DSIN_ID_DOC_JUSTIFICATIVA"] != '') {
                            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                            $documentoJust = $tabelaSadTbDocmDocumento->fetchRow('DOCM_NR_DOCUMENTO = '.$data["DSIN_ID_DOC_JUSTIFICATIVA"]);
                        }
                         
                        $slaDesconsiderado = new Application_Model_DbTable_SosTbDsinDesconsideraIndic();
                        $validaDesconsiderado = $slaDesconsiderado->fetchRow('DSIN_ID_INDICADOR = '.$Ns_sla_desconsidera->indicador. ' AND DSIN_ID_MOVIMENTACAO = '.$dados_input["MOFA_ID_MOVIMENTACAO"]);
                        
                        if (count($validaDesconsiderado["DSIN_ID_MOVIMENTACAO"]) > 0) {
                            $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." já está(ão) com o SLA desconsiderado!";
                            $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'error'));
                            $this->_helper->_redirector($Ns_sla_desconsidera->action,$Ns_sla_desconsidera->controller,$Ns_sla_desconsidera->module);
                        } else {
                            /**
                             * Array para inserir na tabela que desconsidera para o SLA
                             */
                            $dataDesinDesconsideraIndic["DSIN_ID_MOVIMENTACAO"]       = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            $dataDesinDesconsideraIndic["DSIN_ID_INDICADOR"]          = $Ns_sla_desconsidera->indicador;
                            $dataDesinDesconsideraIndic["DSIN_CD_MATRICULA_OPERACAO"] = $userNs->matricula;
                            $dataDesinDesconsideraIndic["DSIN_ID_DOC_JUSTIFICATIVA"]  = $documentoJust['DOCM_ID_DOCUMENTO'];
                            $dataDesinDesconsideraIndic["DSIN_DS_JUSTIFICATIVA"]      = $data["DSIN_DS_JUSTIFICATIVA"]; // Descrição da justificativa
                            /**
                             * Array para inserir na tabela de auditoria da desconsidera o Sla
                             */
                            $dataDesinDesconsideraAudit['DSIN_TS_OPERACAO']               = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");              
                            $dataDesinDesconsideraAudit['DSIN_CD_OPERACAO']               = 'I';              
                            $dataDesinDesconsideraAudit['DSIN_CD_MATRICULA_OPERACAO']     = $userNs->matricula;   
                            $dataDesinDesconsideraAudit['DSIN_CD_MAQUINA_OPERACAO']       = substr($_SERVER['REMOTE_ADDR'],0,50);   
                            $dataDesinDesconsideraAudit['DSIN_CD_USUARIO_SO']             = substr($_SERVER['HTTP_USER_AGENT'],0,50);
                            $dataDesinDesconsideraAudit['OLD_DSIN_ID_MOVIMENTACAO']       = new Zend_Db_Expr("NULL");
                            $dataDesinDesconsideraAudit['NEW_DSIN_ID_MOVIMENTACAO']       = $dados_input["MOFA_ID_MOVIMENTACAO"]; 
                            $dataDesinDesconsideraAudit['OLD_DSIN_ID_INDICADOR']          = new Zend_Db_Expr("NULL");
                            $dataDesinDesconsideraAudit['NEW_DSIN_ID_INDICADOR']          = $Ns_sla_desconsidera->indicador;
                            $dataDesinDesconsideraAudit['OLD_DSIN_CD_MATRICULA_OPERACAO'] = new Zend_Db_Expr("NULL");            
                            $dataDesinDesconsideraAudit['NEW_DSIN_CD_MATRICULA_OPERACAO'] = $userNs->matricula;   
                            $dataDesinDesconsideraAudit['OLD_DSIN_ID_DOC_JUSTIFICATIVA']  = new Zend_Db_Expr("NULL");   
                            $dataDesinDesconsideraAudit['NEW_DSIN_ID_DOC_JUSTIFICATIVA']  = $documentoJust['DOCM_ID_DOCUMENTO'];
                            $dataDesinDesconsideraAudit['OLD_DSIN_DS_JUSTIFICATIVA']      = new Zend_Db_Expr("NULL");
                            $dataDesinDesconsideraAudit['NEW_DSIN_DS_JUSTIFICATIVA']      = $data["DSIN_DS_JUSTIFICATIVA"]; // Descrição da justificativa   
                            /**
                             * Método para desconsiderar o SLA e realizar auditoria
                             */
                            $desconsidera = new Application_Model_DbTable_SosTbDsinDesconsideraIndic();
                            $desconsidera->setRetiraSla($dataDesinDesconsideraIndic, $dataDesinDesconsideraAudit);
                            
                             /**
                             * Método para inserir a Justificativa e lançar fase de Desconsideração de sla para a solicitação
                             */
                            $zend_date = new Zend_Date(null, 'dd/MM/YY HH:mm:ss');
                            $dataMofa = $zend_date->get(Zend_Date::DATETIME);
                            /**
                             * Montando Array com os dados necessários para o lançamento da fase
                             */
                            $arrayMovimentacao['MOFA_ID_MOVIMENTACAO'] = $dados_input["MOFA_ID_MOVIMENTACAO"];
                            $arrayMovimentacao['MOFA_CD_MATRICULA'] = $userNs->matricula;
                            $arrayMovimentacao['MOFA_ID_FASE'] = 1081; //fase de Desconsideração de SLA
                            $arrayMovimentacao['MOFA_DH_FASE'] = new Zend_Db_Expr("TO_DATE('$dataMofa','DD/MM/YYYY HH24:MI:SS')");
                            $arrayMovimentacao['MOFA_DS_COMPLEMENTO'] = $data["DSIN_DS_JUSTIFICATIVA"]; // Descrição da justificativa
                            /**
                             * Lança a fase de Desconsideração
                             */
                            $fase = new Trf1_Sosti_Negocio_Fase();
                            $fase->lancaFase($arrayMovimentacao);
                            
                        }
                    }
                    $msg_to_user = "Solicitação(es) n(s)º ".substr($solicitacoesEncaminhadas, 1)." com o SLA desconsiderado!";
                    $this->_helper->flashMessenger ( array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector($Ns_sla_desconsidera->action,$Ns_sla_desconsidera->controller,$Ns_sla_desconsidera->module);
                } else {
                    $Ns_sla_desconsidera = new Zend_Session_Namespace('Ns_sla_desconsidera');
                    $this->view->data = $Ns_sla_desconsidera->data;
                    $this->view->title = $Ns_sla_desconsidera->title;
                    $form->populate($data);
                    $this->view->form = $form;
                    $this->render('desconsiderarsla');
                }
            }
        }
    }
 
    public function indicadoresnivelservicopdfAction() 
    {
        set_time_limit( 1200 );
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $this->view->slaIndices = $indMin->data;
        $this->view->titulo = $indMin->title;
        $this->view->periodo = $indMin->periodo;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->view->emissor = strtoupper($userNamespace->matricula).' - '.$userNamespace->nome;
        if ($indMin->fuso != "") {
            $this->view->fuso = $indMin->fuso;
            $this->view->secao = $indMin->secao;
        }
        
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();

        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       10,    // margin top
                       10,    // margin bottom
                        9,    // margin header
                        9,    // margin footer
                       'L');

        $mpdf->AddPage('P', '', '0', '1');
        $mpdf->WriteHTML($body);
        
        $name =  'Resultado_SLA.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function indicadoresnivelservicoexcelAction()
    {
        set_time_limit( 1200 );
        $this->_helper->layout->disableLayout(); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=extraction.xls"); 
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $this->view->slaIndices = $indMin->data;
        $this->view->titulo = $indMin->title;
        $this->view->periodo = $indMin->periodo;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->view->emissor = strtoupper($userNamespace->matricula).' - '.$userNamespace->nome;
        if ($indMin->fuso != "") {
            $this->view->fuso = $indMin->fuso;
            $this->view->secao = $indMin->secao;
        }
    }
    
}
