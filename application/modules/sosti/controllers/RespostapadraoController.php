<?php

class Sosti_RespostapadraoController extends Zend_Controller_Action {
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
	
    public function init() {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
    }

    public function indexAction() {

        $this->_helper->redirector('list', 'respostapadrao', 'sosti');
    }

    public function listAction() {

        //FORMULÁRIO DE PESQUISA DO FILTRO
        $RespostaPadrao = new Sosti_Form_RespostaPadrao();

        //REGRA NEGOCIAL
        $Services_Sosti_RespostaPadrao = new Services_Sosti_RespostaPadrao();

        //INSTANCIA DA VARIAVEL DE RESULTADOS
        $array_dados = array();

        //PESQUISA PELO FILTRO
        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getPost();

            //MONTA O FORMULÁRIO DO FILTRO
            $RespostaPadrao->set_idGrupo($data['REPD_ID_GRUPO']);
            $RespostaPadrao->filtroResposta();

            if ($RespostaPadrao->isValid($data)) {
                
                $data = array_merge($this->getRequest()->getPost(), $RespostaPadrao->populate($this->getRequest()->getPost())->getValues());

                //FAZ A CONSULTA NA BASE DE DADOS
                $array_dados = $Services_Sosti_RespostaPadrao->filtroRespostaPadrao($data);

                //MONTA O FORMULÁRIO DO FILTRO
                $RespostaPadrao->set_idGrupo($data['REPD_ID_GRUPO']);
                $RespostaPadrao->filtroResposta();
            $this->view->idGrupo = $data['REPD_ID_GRUPO'];
        } else {
                //MONTA O FORMULÁRIO DO FILTRO
                $RespostaPadrao->set_idGrupo($data['REPD_ID_GRUPO']);
                $RespostaPadrao->filtroResposta();
                $this->view->idGrupo = $data['REPD_ID_GRUPO'];
            }
        } else {

            //VERIFICA SE EXISTE O PARAMETRO IDGRUPO
            if ($this->getRequest()->getParam('idGrupo') && $this->getRequest()->getParam('idGrupo') != '') {
                $idGrupo = $this->getRequest()->getParam('idGrupo');
                //VERIFICA SE O PARAMETRO IDGRUPO PASSADO SE ENCONTRA NO ARRAY DA SESSION
                if ($Services_Sosti_RespostaPadrao->validaGrupo($idGrupo)) {
                    $this->view->idGrupo = $idGrupo;

                    //MONTA O FORMULÁRIO DO FILTRO
                    $RespostaPadrao->set_idGrupo($idGrupo);
                    $RespostaPadrao->filtroResposta();

                    //PESQUISA OS DADOS E JOGA O RESULTADO NA VIEW
                    $array_dados = $Services_Sosti_RespostaPadrao->listRespostaPadrao($idGrupo);
                } else {
                    //REDIRECIONA PARA A INICIAL DO SOSTI
                    $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                    $this->_helper->redirector('index', 'index', 'sosti');
                }
            } else {
                //REDIRECIONA PARA A INICIAL DO SOSTI
                $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                $this->_helper->redirector('index', 'index', 'sosti');
            }
        }

        //CONFIGURA O ZEND PAGINATOR
        $page = $this->getRequest()->getParam('page', 1);
        $itemCountPerPage = $this->getRequest()->getParam('itemsperpage', 15);
        $paginator = Zend_Paginator::factory($array_dados);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemCountPerPage);


        //VARIAVEIS DA VIEW
        $this->view->title = 'RESPOSTAS PADRÕES';
        $this->view->data = $paginator;
        $this->view->form_filtro = $RespostaPadrao;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_v2.phtml');
    }

    public function addAction() {

        //FORMULARIO
        $RespostaPadrao = new Sosti_Form_RespostaPadrao();
        //REGRA DE NEGOCIO
        $Services_Sosti_RespostaPadrao = new Services_Sosti_RespostaPadrao();

        //VERIFICA SE EXISTE REQUISIÇÃO POST
        if ($this->getRequest()->getPost()) {

            //RECEBE OS DADOS POST
            $data = $this->getRequest()->getPost();
            //VALIDAÇÃO DO IDGRUPO  
            if ($Services_Sosti_RespostaPadrao->validaGrupo($data['REPD_ID_GRUPO'])) {
                //SETA O IDGRUPO PARA CARREGAR O CAMPO DE TIPO DE SERVICO
                $RespostaPadrao->set_idGrupo($data['REPD_ID_GRUPO']);
                //CHAMA O MÉTODO PARA IMPLEMENTAR O CAMPO TIPO DE SERVICO
                $RespostaPadrao->add();
            } else {
                //REDIRECIONA PARA A INICIAL DO SOSTI
                $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                $this->_helper->redirector('index', 'index', 'sosti');
            }

            //VALIDAR FORMULARIO
            if ($RespostaPadrao->isValid($data)) {
                
                //APLICA OS FILTROS NOS VALORES DO POST
                $data_valid = array_merge($this->getRequest()->getPost(), $RespostaPadrao->populate($this->getRequest()->getPost())->getValues());
                
                //COM OS DADOS VÁLIDOS, SOLICITA À REGRA NEGOCIAL O CADASTRO DA RESPOSTA
                try {
                    $Services_Sosti_RespostaPadrao->addRespostaPadrao($data_valid);
                    $this->_helper->flashMessenger(array('message' => 'Resposta Padrão cadastrada com sucesso.', 'status' => 'success'));
                    $this->_redirect('sosti/respostapadrao/list/idGrupo/' . $data_valid['REPD_ID_GRUPO']);
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => 'Ocorreu um erro no cadastro da Resposta Padrão. ' . $e->getMessage(), 'status' => 'error'));
                    $this->_redirect('sosti/respostapadrao/list/idGrupo/' . $data_valid['REPD_ID_GRUPO']);
                }
            } else {
                //POPULA O FORMULÁRIO
                $RespostaPadrao->populate($data_valid);
            }
        } else {
            //SE NÃO TIVER REQUISIÇÃO POST, VERIFICA SE EXISTE O PARAMETRO DO IDGRUPO
            if ($this->getRequest()->getParam('idGrupo') && $this->getRequest()->getParam('idGrupo') != '') {
                $idGrupo = $this->getRequest()->getParam('idGrupo');
                //VERIFICA SE O PARAMETRO IDGRUPO PASSADO SE ENCONTRA NO ARRAY DA SESSION
                if ($Services_Sosti_RespostaPadrao->validaGrupo($idGrupo)) {
                    //SE FOR VÁLIDO, ADICIONAR O VALOR NO CAMPO IDGRUPO HIDDEN
                    $RespostaPadrao->REPD_ID_GRUPO->setValue($idGrupo);
                    //SETA O IDGRUPO PARA CARREGAR O CAMPO DE TIPO DE SERVICO
                    $RespostaPadrao->set_idGrupo($idGrupo);
                    //CHAMA O MÉTODO PARA IMPLEMENTAR O CAMPO TIPO DE SERVICO
                    $RespostaPadrao->add();
                } else {
                    //REDIRECIONA PARA A INICIAL DO SOSTI
                    $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                    $this->_helper->redirector('index', 'index', 'sosti');
                }
            } else {
                //REDIRECIONA PARA A INICIAL DO SOSTI
                $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                $this->_helper->redirector('index', 'index', 'sosti');
            }
        }

        //VARIAVEIS DA VIEW
        $this->view->title = 'CADASTRAR NOVA RESPOSTA PADRÃO';
        $this->view->form = $RespostaPadrao;
    }

    public function editAction() {

        //INSTANCIA DA SESSAO DO USUARIO
        $userNs = new Zend_Session_Namespace('userNs');
        //FORMULARIO
        $RespostaPadrao = new Sosti_Form_RespostaPadrao();
        //REGRA DE NEGOCIO
        $Services_Sosti_RespostaPadrao = new Services_Sosti_RespostaPadrao();

        //VERIFICA SE EXISTE REQUISIÇÃO POST
        if ($this->getRequest()->getPost()) {

            //RECEBE OS DADOS POST
            $data = $this->getRequest()->getPost();

            //VERIFICA SE A RESPOSTA PADRAO FOI CADASTRADA PELA MATRICULA LOGADA
            if ($data['REPD_CD_MATRICULA_CADASTRO'] != "" && $data['REPD_CD_MATRICULA_CADASTRO'] != $userNs->matricula) {
                //VALIDAÇÃO DO IDGRUPO  
                if (!$Services_Sosti_RespostaPadrao->validaGrupo($data['REPD_ID_GRUPO'])) {
                    //REDIRECIONA PARA A INICIAL DO SOSTI
                    $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                    $this->_helper->redirector('index', 'index', 'sosti');
                }
            }

            //SETA O IDGRUPO PARA CARREGAR O CAMPO DE TIPO DE SERVICO
            $RespostaPadrao->set_idGrupo($data['REPD_ID_GRUPO']);
            //CHAMA O MÉTODO PARA IMPLEMENTAR O FORMULÁRIO DE VALIDAÇÃO
            $RespostaPadrao->add();
            $RespostaPadrao->edit();
            //VALIDAR FORMULARIO
            if ($RespostaPadrao->isValid($data)) {
                
                 //APLICA OS FILTROS NOS VALORES DO POST
                $data_valid = array_merge($this->getRequest()->getPost(), $RespostaPadrao->populate($this->getRequest()->getPost())->getValues());
                
                //COM OS DADOS VÁLIDOS, SOLICITA À REGRA NEGOCIAL A ALTERAÇÃO DA RESPOSTA
                try {
                    $Services_Sosti_RespostaPadrao->editRespostaPadrao($data_valid);
                    $this->_helper->flashMessenger(array('message' => 'Resposta Padrão alterada com sucesso.', 'status' => 'success'));
                    $this->_redirect('sosti/respostapadrao/list/idGrupo/' . $data_valid['REPD_ID_GRUPO_VALIDACAO']);
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => 'Ocorreu um erro na alteração da Resposta Padrão. ' . $e->getMessage(), 'status' => 'error'));
                    $this->_redirect('sosti/respostapadrao/list/idGrupo/' . $data_valid['REPD_ID_GRUPO_VALIDACAO']);
                }
            } else {
                //POPULA O FORMULÁRIO
                $RespostaPadrao->populate($data_valid);
            }
        } else {
            //SE NÃO TIVER REQUISIÇÃO POST, VERIFICA SE EXISTE O PARAMETRO DO IDGRUPO E A RESPOSTA PADRAO
            if ($this->getRequest()->getParam('idGrupo') && $this->getRequest()->getParam('idGrupo') != '' && $this->getRequest()->getParam('resposta') && $this->getRequest()->getParam('resposta') != '') {
                $idGrupo = $this->getRequest()->getParam('idGrupo');
                $idResposta = $this->getRequest()->getParam('resposta');
                //VERIFICA SE O PARAMETRO IDGRUPO PASSADO SE ENCONTRA NO ARRAY DA SESSION
                if ($Services_Sosti_RespostaPadrao->validaGrupo($idGrupo)) {
                    //FAZER CONSULTA DA RESPOSTA NA BASE DE DADOS
                    $resposta = $Services_Sosti_RespostaPadrao->buscaRespostaPadrao($idResposta);
                    //SE FOR VÁLIDO, ADICIONAR O VALOR NO CAMPO IDGRUPO HIDDEN E SETA O IDGRUPO PARA CARREGAR O CAMPO DE TIPO DE SERVICO
                    $RespostaPadrao->set_idGrupo($resposta["REPD_ID_GRUPO"]);
                    $RespostaPadrao->set_idGrupoValidacao($idGrupo);
                    //CHAMA OS MÉTODOS PARA IMPLEMENTAR O CAMPO TIPO DE SERVICO E IDRESPOSTA
                    $RespostaPadrao->add();
                    $RespostaPadrao->edit();
                    //REMOVENDO FILTRO DO FORMULÁRIO PARA FAZER O POPULATE DOS DADOS
                    $RespostaPadrao->getElement('REPD_DS_RESPOSTA_PADRAO')->removeFilter('HtmlEntities');
                    $RespostaPadrao->populate($resposta);
                } else {
                    //REDIRECIONA PARA A INICIAL DO SOSTI
                    $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                    $this->_helper->redirector('index', 'index', 'sosti');
                }
            } else {
                //REDIRECIONA PARA A INICIAL DO SOSTI
                $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                $this->_helper->redirector('index', 'index', 'sosti');
            }
        }

        //VARIAVEIS DA VIEW
        $this->view->title = 'EDITAR RESPOSTA PADRÃO';
        $this->view->form = $RespostaPadrao;
    }

    public function deleteAction() {

        //INSTANCIA DA SESSAO DO USUARIO
        $userNs = new Zend_Session_Namespace('userNs');
        //FORMULARIO
        $RespostaPadrao = new Sosti_Form_RespostaPadrao();
        //REGRA DE NEGOCIO
        $Services_Sosti_RespostaPadrao = new Services_Sosti_RespostaPadrao();


        //VERIFICA SE EXISTE REQUISIÇÃO POST
        if ($this->getRequest()->getPost()) {

            //RECEBE OS DADOS POST
            $data = $this->getRequest()->getPost();

            //VERIFICA SE A RESPOSTA PADRAO FOI CADASTRADA PELA MATRICULA LOGADA
            if ($data['REPD_CD_MATRICULA_CADASTRO'] != "" && $data['REPD_CD_MATRICULA_CADASTRO'] != $userNs->matricula) {
                //VALIDAÇÃO DO IDGRUPO 
                if ($Services_Sosti_RespostaPadrao->validaGrupo($data['REPD_ID_GRUPO'])) {
                    //SETA O IDGRUPO PARA CARREGAR O CAMPO DE TIPO DE SERVICO
                    $RespostaPadrao->set_idGrupo($data['REPD_ID_GRUPO']);
                } else {
                    //REDIRECIONA PARA A INICIAL DO SOSTI
                    $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação. Violação de parâmetro. ', 'status' => 'notice'));
                    $this->_helper->redirector('index', 'index', 'sosti');
                }
            }

            //CHAMA O MÉTODO PARA IMPLEMENTAR O FORMULÁRIO DE VALIDAÇÃO
            $RespostaPadrao->delete();
            //VALIDAR FORMULARIO
            if ($RespostaPadrao->isValid($data)) {
                //COM OS DADOS VÁLIDOS, SOLICITA À REGRA NEGOCIAL A ALTERAÇÃO DA RESPOSTA
                try {
                    $Services_Sosti_RespostaPadrao->deleteRespostaPadrao($data);
                    $this->_helper->flashMessenger(array('message' => 'Resposta Padrão excluída com sucesso.', 'status' => 'success'));
                    $this->_redirect('sosti/respostapadrao/list/idGrupo/' . $data['REPD_ID_GRUPO_VALIDACAO']);
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array('message' => 'Ocorreu um erro na exclusão da Resposta Padrão. ' . $e->getMessage(), 'status' => 'error'));
                    $this->_redirect('sosti/respostapadrao/list/idGrupo/' . $data['REPD_ID_GRUPO_VALIDACAO']);
                }
            } else {
                //POPULA O FORMULÁRIO
                $RespostaPadrao->populate($data);
            }
        } else {

            if ($this->getRequest()->getParam('idGrupo') && $this->getRequest()->getParam('idGrupo') != '' && $this->getRequest()->getParam('resposta') && $this->getRequest()->getParam('resposta') != '') {
                $idGrupo = $this->getRequest()->getParam('idGrupo');
                $idResposta = $this->getRequest()->getParam('resposta');
                //VERIFICA SE O PARAMETRO IDGRUPO PASSADO SE ENCONTRA NO ARRAY DA SESSION
                if ($Services_Sosti_RespostaPadrao->validaGrupo($idGrupo)) {

                    //SE FOR VÁLIDO, ADICIONAR O VALOR NO CAMPO IDGRUPO HIDDEN
                    $RespostaPadrao->set_idGrupo($idGrupo);
                    //CHAMA A FUNÇÃO PARA TRATAR O FORMULÁRIO DE EXCLUSÃO
                    $RespostaPadrao->delete();
                    //FAZER CONSULTA DA RESPOSTA NA BASE DE DADOS
                    $resposta = $Services_Sosti_RespostaPadrao->buscaRespostaPadrao($idResposta);
                    //POPULAR O FORMULÁRIO COM OS DADOS NECESSÁRIOS PARA O CADASTRO
                    $RespostaPadrao->populate($resposta);
                    //MOSTRAR OS DETALHES DA RESPOSTA NA VIEW
                    $this->view->resposta = $resposta;
                } else {
                    //REDIRECIONA PARA A INICIAL DO SOSTI
                    $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                    $this->_helper->redirector('index', 'index', 'sosti');
                }
            } else {
                //REDIRECIONA PARA A INICIAL DO SOSTI
                $this->_helper->flashMessenger(array('message' => 'Não foi possível completar a ação.', 'status' => 'notice'));
                $this->_helper->redirector('index', 'index', 'sosti');
            }
        }

        //Variaveis da view
        $this->view->title = 'EXCLUIR RESPOSTA PADRÃO';
        $this->view->form = $RespostaPadrao;
    }

    public function escolherespostapadraoAction() {

        //INSTANCIA DA REGRA NEGOCIAL
        $Services_Sosti_RespostaPadrao = new Services_Sosti_RespostaPadrao();

        //VARIAVEL QUE RECEBERA OS DADOS DA PESQUISA
        $array_dados = array();

        //CAPTURA OS DADOS DA REQUISIÇÃO
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //FAZ A CONSULTA NA BASE DE DADOS
            $array_dados = $Services_Sosti_RespostaPadrao->pesquisaRespostaPadrao($data);
            $this->view->arrayDados = $array_dados;
        }
    }

}
