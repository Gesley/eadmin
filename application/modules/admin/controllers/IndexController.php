<?php

class Admin_IndexController extends Zend_Controller_Action
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

    public function indexAction()
    {
        $this->view->title = "Seja Bem-Vindo ao Sistema e-Admin!";
        $userNs = new Zend_Session_Namespace('userNs');
        $userPerf = new Zend_Session_Namespace('userPrf');
        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        
        /* CAIXA EXTINTA COM DOCUMENTOS*/
//        $dadosMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        /**
         * Verifica se possui o Perfil Autoriza Extensão de Prazo e na caixa com
         * solicitação de informação pelo desenvolvedor
         */
        $arrayPerfisPessoa = $ocsTbPupePerfilUnidPessoa->getTodosPerfilPessoa($userNs->matricula);  
        $temPerfilAutoriza = false;
        foreach ($arrayPerfisPessoa as $perfil) {
            if ($perfil['PERF_ID_PERFIL'] == 41) {
                $temPerfilAutoriza = true;
            }
            if($perfil['PERF_DS_PERFIL'] == "GESTÃO DE DEMANDAS DE TI") {
                $gestaoDemandas = true;
            }
        }
        $this->view->gestaoDemandas = $gestaoDemandas;
        $userPerf->temPerfilAutoriza = $temPerfilAutoriza;
        $userPerf->gestaoDemandas = $gestaoDemandas;
        
        $arrayPerfis = $ocsTbPupePerfilUnidPessoa->getPerfilUnidadePessoa($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula);
        
        foreach ($arrayPerfis as $perfil) {
            if ($perfil["PERF_ID_PERFIL"] == 9 && $perfil["PUPE_ID_UNIDADE_PERFIL"] != NULL) {
                $this->view->responsavelCaixa = 'ATIVO';
                $userPerf->responsavelCaixa = 'ATIVO';
            }
            if ($perfil["PERF_ID_PERFIL"] == 54) {
                $this->view->dashboardSosti = 'ATIVO';
                $userPerf->dashboardSosti = 'ATIVO';
            }
            if ($perfil["PERF_ID_PERFIL"] == 55) {
                $this->view->dashboardSisad = 'ATIVO';
                $userPerf->dashboardSisad = 'ATIVO';
            }
        }
        $dadosDesatualizados = '';
        $this->view->msgsAlertaDadosPessoais = array();
        if( ! $userNs->siglasecao ){
            if( ! $dadosDesatualizados ){
                $dadosDesatualizados = 'Seus dados pessoais encontram-se desatualizados.';
            }
            $this->view->msgsAlertaDadosPessoais['siglasecao'] = 'Sua Seção nao foi encontrada.';
        }
        if( ! $userNs->codlotacao ){
            if( ! $dadosDesatualizados ){
                $dadosDesatualizados = 'Seus dados pessoais encontram-se desatualizados.';
            }
            $this->view->msgsAlertaDadosPessoais['codlotacao'] = 'Sua unidade nao foi encontrada.';
        }
        if( $userNs->lota_dat_fim ){
            if( ! $dadosDesatualizados ){
                $dadosDesatualizados = 'Seus dados pessoais encontram-se desatualizados.';
            }
            $this->view->msgsAlertaDadosPessoais['codlotacao'] = 'Sua lotação se encontra desativada.';
        }
        $this->view->dadospessoaisdesatualizados = $dadosDesatualizados;
    }
    
    public function ajaxmensagensAction()
    {
        $cache = new App_Controller_Plugin_Cache(120);
        $userNs = new Zend_Session_Namespace('userNs');
        $ocsNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $userPerf = new Zend_Session_Namespace('userPrf');
        $parteAcomp = new Application_Model_DbTable_SadTbPapdParteProcDoc();
        $this->view->responsavelCaixa = $userPerf->responsavelCaixa;
        $this->view->dashboardSosti = $userPerf->dashboardSosti;
        $this->view->dashboardSisad = $userPerf->dashboardSisad ;

        /* NOTIFICAÇÕES NÃO LIDAS*/
        $notificacoesnaolidas = $ocsNotf->getnotfCount($userNs->matricula);
        $this->view->notificacoesnaolidas = $notificacoesnaolidas[0]["COUNT"];
        $arrayCacheNotif['notificacoesnaolidas'] = $this->view->notificacoesnaolidas;

        /* SOLICITAÇÕES PARA AVALIAR */
        $QtdeMinhasSolicitacoesAvaliacao = $dados->getQtdeMinhasSolicitacoesAvaliacao($userNs->matricula,'DOCM_NR_DOCUMENTO');
        $this->view->solicitacoesparaavaliar = $QtdeMinhasSolicitacoesAvaliacao[0]['QTDE'];
        $arrayCacheNotif['solicitacoesparaavaliar'] = $this->view->solicitacoesparaavaliar;
        
        /* SOLICITAÇÕES COM SOLICITAÇAO DE INFORMAÇÃO */
        $QtdeMinhasSolicitacoesAtendimentoPedido = $dados->getQtdeMinhasSolicitacoesAtendimento($userNs->matricula, 'MOFA_DH_FASE ASC', 'solicitacao de informacao', 'aousuario');
        $this->view->solicitacoescompedidodeinformacao = $QtdeMinhasSolicitacoesAtendimentoPedido[0]['QTDE'];
        $arrayCacheNotif['solicitacoescompedidodeinformacao'] = $this->view->solicitacoescompedidodeinformacao;
        
        $QtdeMinhasSolicitacoesAtendimentoInfo = $dados->getQtdeMinhasSolicitacoesAtendimento($userNs->matricula, 'MOFA_DH_FASE ASC', 'solicitacao de informacao', 'aoencaminhador');
        $this->view->solicitacoescompedidodeinformacao_aoencaminhador = $QtdeMinhasSolicitacoesAtendimentoInfo[0]['QTDE'];
        $arrayCacheNotif['solicitacoescompedidodeinformacao_aoencaminhador'] = $this->view->solicitacoescompedidodeinformacao_aoencaminhador;
        
        /* SOLICITAÇÕES EM ATENDIMENTO */
        $QtdeMinhasSolicitacoesAtendimento = $dados->getQtdeMinhasSolicitacoesAtendimento($userNs->matricula, 'MOFA_DH_FASE ASC', '', '');
        $this->view->solicitacoesematendimento = $QtdeMinhasSolicitacoesAtendimento[0]['QTDE'];
        $arrayCacheNotif['solicitacoesematendimento'] = $this->view->solicitacoesematendimento;
        
        /*ACOMPANHAMENTO DE SOLICITAÇÕES */
        $solicitacoesacompanhamento = $parteAcomp->getSolAcompanhamento($userNs->matricula);
        $this->view->solicitacoesacompanhamento = count($solicitacoesacompanhamento);
        $arrayCacheNotif['solicitacoesacompanhamento'] = $this->view->solicitacoesacompanhamento;
        
        /* PENDENTE DE AVALIAÇÃO */
//        $pendenteAvaliacao = $dados->getSolicitacoesPendenteAvaliacao('DOCM_NR_DOCUMENTO');
//        $this->view->pendenteavaliacao = count($pendenteAvaliacao);
        
        /* PARA HOMOLOGACAO */
//        $paraHomologacao = $dados->getMinhasSolicitacoesHomologacao($userNs->matricula,' TEMPO_TOTAL ASC');
//        $this->view->parahomologacao = count($paraHomologacao);
        
        /* SOLICITAÇÕES DE INFORMAÇÃO RESPONDIDAS*/
        $solicitacoescompedidodeinformacaorespondido = $dados->getQtdeMinhasSolicitacoesPedidoInfRespondido($userNs->matricula, 'MOFA_DH_FASE ASC', 1024);
        $this->view->solicitacoescompedidodeinformacaorespondido = $solicitacoescompedidodeinformacaorespondido[0]['QTDE'];
        $arrayCacheNotif['solicitacoescompedidodeinformacaorespondido'] = $this->view->solicitacoescompedidodeinformacaorespondido;
                
        $cache->save($arrayCacheNotif, 'user_'.$userNs->matricula);

        $aNivelSpace = new Zend_Session_Namespace('nivelNs');
        $aNivelSpace->nivel = 1;

        $dadosDesatualizados = '';
        $this->view->msgsAlertaDadosPessoais = array();
        if( ! $userNs->siglasecao ){
            if( ! $dadosDesatualizados ){
                $dadosDesatualizados = 'Seus dados pessoais encontram-se desatualizados.';
            }
            $this->view->msgsAlertaDadosPessoais['siglasecao'] = 'Sua Seção nao foi encontrada.';
        }
        if( ! $userNs->codlotacao ){
            if( ! $dadosDesatualizados ){
                $dadosDesatualizados = 'Seus dados pessoais encontram-se desatualizados.';
            }
            $this->view->msgsAlertaDadosPessoais['codlotacao'] = 'Sua unidade nao foi encontrada.';
        }
        if( $userNs->lota_dat_fim ){
            if( ! $dadosDesatualizados ){
                $dadosDesatualizados = 'Seus dados pessoais encontram-se desatualizados.';
            }
            $this->view->msgsAlertaDadosPessoais['codlotacao'] = 'Sua lotação se encontra desativada.';
        }
        $this->view->dadospessoaisdesatualizados = $dadosDesatualizados;
    }
    
    public function ajaxextensaoprazoAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $SosTbSinsIndicNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
        $userPerf = new Zend_Session_Namespace('userPrf');
        $gestaoDemandas = $userPerf->gestaoDemandas;
        $this->view->gestaoDemandas = $gestaoDemandas;
        $temPerfilAutoriza = $userPerf->temPerfilAutoriza;
        if($temPerfilAutoriza){
            $caixasQuantidade = $SosTbSinsIndicNivelServ->getQtdSosExtensaoPrazo($userNs);    
            $this->view->caixasQuantidade = count($caixasQuantidade);
            $this->view->caixasQuantidadeDados = $caixasQuantidade;
        }
    }
    
    public function ajaxminhasnotificacoesAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $ocsNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
        $notificacoesnaolidas = $ocsNotf->getnotfCount($userNs->matricula);
        $this->view->notificacoesnaolidas = $notificacoesnaolidas[0]["COUNT"];
    }
    
    public function ajaxminhassolicitacoesAction()
    {
        $cache = new App_Controller_Plugin_Cache();
        $userNs = new Zend_Session_Namespace('userNs');
        $userPerf = new Zend_Session_Namespace('userPrf');
        $gestaoDemandas = $userPerf->gestaoDemandas;
        $this->view->gestaoDemandas = $gestaoDemandas;
        $resource = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getPluginResource('multidb');
        $dbSosti = $resource->getDb('sosti');
        Zend_Db_Table::setDefaultAdapter($dbSosti);

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $cxSemNivel = new Sosti_Model_DataMapper_CaixaSemNivel();
        $qtdeNacaixa = $db->fetchAll($cxSemNivel->getQuery(2, array(), 'MOVI_DH_ENCAMINHAMENTO DESC', true, true));
        $this->view->nacaixasolicdesenv = $qtdeNacaixa[0]['QTDE'];
        
        $dbGuardiao = $resource->getDb('guardiao');
        Zend_Db_Table::setDefaultAdapter($dbGuardiao);

        $arrayCacheNotificacao = $cache->load('user_'.$userNs->matricula);
        /* SOLICITAÇÕES PARA AVALIAR */
        $this->view->solicitacoesparaavaliar = $arrayCacheNotificacao['solicitacoesparaavaliar'];
        
        /* SOLICITAÇÕES COM SOLICITAÇAO DE INFORMAÇÃO */
        $this->view->solicitacoescompedidodeinformacao = $arrayCacheNotificacao['solicitacoescompedidodeinformacao'];
        
        $this->view->solicitacoescompedidodeinformacao_aoencaminhador = $arrayCacheNotificacao['solicitacoescompedidodeinformacao_aoencaminhador'];
        
        /* SOLICITAÇÕES EM ATENDIMENTO */
        $this->view->solicitacoesematendimento = $arrayCacheNotificacao['solicitacoesematendimento'];
        
        /*ACOMPANHAMENTO DE SOLICITAÇÕES */
        $this->view->solicitacoesacompanhamento = $arrayCacheNotificacao['solicitacoesacompanhamento'];
        
        /* SOLICITAÇÕES DE INFORMAÇÃO RESPONDIDAS*/
        $this->view->solicitacoescompedidodeinformacaorespondido = $arrayCacheNotificacao['solicitacoescompedidodeinformacaorespondido'];
        
        /* PENDENTE DE AVALIAÇÃO */
//        $pendenteAvaliacao = $dados->getSolicitacoesPendenteAvaliacao('DOCM_NR_DOCUMENTO');
//        $this->view->pendenteavaliacao = count($pendenteAvaliacao);
        
        /* PARA HOMOLOGACAO */
//        $paraHomologacao = $dados->getMinhasSolicitacoesHomologacao($userNs->matricula,' TEMPO_TOTAL ASC');
//        $this->view->parahomologacao = count($paraHomologacao);
        $cache->remove('user_'.$userNs->matricula);
    }
    
    public function ajaxmeusavisosAction()
    {
        $userNs = new Zend_Session_Namespace('userNs');
        $doliDocumento = new Application_Model_DbTable_SadTbDoliDocumentoLista();
        $rows = $doliDocumento->getCaixaAvisosPessoaisCount($userNs->matricula);
        $this->view->avisosnaolidos = $rows["COUNT"];
    }
   
}