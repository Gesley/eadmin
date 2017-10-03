<?php

class Orcamento_NovadespesaController extends Zend_Controller_Action {

    /**
     * Timer para mensuração do tempo de carregamento da página
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    /**
     * Nome do modulo para usos diversos
     *
     * @var string $_modulo
     */
    private $_modulo = null;

    /**
     * Nome do controle para usos diversos
     *
     * @var string $_controle
     */
    private $_controle = null;

    /**
     * Classe negocial padrão
     *
     * @var string $_classeNegocio
     */
    private $_classeNegocio = null;

    /**
     * Classe negocial padrão
     *
     * @var string $_formulario
     */
    private $_formulario = null;

    public function init () {
        // Título apresentado no Browser
        $this->view->title = 'Solicitação de nova despesa';

        // Ajuda & Informações
        $this->view->msgAjuda = AJUDA_AJUDA;
        $this->view->msgInfo = AJUDA_INFOR;

        // Timer para mensuração do tempo de carregamento da página
        $this->_temporizador = new Trf1_Admin_Timer();
        $this->_temporizador->Inicio();

        // Informações sobre a requisição
        $requisicao = $this->getRequest();
        $this->_modulo = strtolower($requisicao->getModuleName());
        $this->_controle = strtolower($requisicao->getControllerName());

        //Perfil
        $sessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
        $this->perfil = $sessaoOrcamento->perfil;
        $this->ug = $sessaoOrcamento->ug;

        // Define o nome da classe negocial padrão
        $this->_classeNegocio = 'Trf1_Orcamento_Negocio_' . ucfirst($this->_controle);

        // Define o nome do formulário
        $this->_formulario = 'Orcamento_Form_' . ucfirst($this->_controle);

        // Define a business de Recurso
        $this->_negocioRecurso = new Orcamento_Business_Negocio_Recd();

        // Define a business de Nova despesa
        $this->_negocioSold = new Orcamento_Business_Negocio_Sold();

        // Grava nova tabela de log (NOVO)
        $this->_logdados = new Orcamento_Business_Negocio_Logdados(); 
        
        // Grava log de cada ação
        $log = new Trf1_Orcamento_Log();
        $log->gravaLog($requisicao);
    }

    public function postDispatch () {
        // Apresenta o tempo de carregamento da página
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function indexAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem das solicitações de novas despesas';

        // Dados do grid
        $negocio = new $this->_classeNegocio();
        $dados = $negocio->retornaListagem();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array(
            'incluir',
            'detalhe',
            'editar',
            'excluir'
        );
        $camposDetalhes = array(
            'SOLD_NR_SOLICITACAO' => array(
                'title' => 'Código',
                'abbr' => 'Código da solicitação'
            ),
            'SOLD_NR_DESPESA' => array(
                'title' => 'Despesa',
                'abbr' => 'Código da despesa'
            ),
            'SOLD_DS_DESPESA' => array(
                'title' => 'Descrição da despesa',
                'abbr' => 'Descrição da despesa'
            ),
            'SOLD_CD_UG' => array(
                'title' => 'UG',
                'abbr' => 'Unidade gestora'
            ),
            'SOLD_DS_JUSTIFICATIVA_SOLICIT' => array(
                'title' => 'Justificativa',
                'abbr' => 'Justificativa do solicitante'
            ),
            'SOLD_DS_JUSTIFICATIVA_SECOR' => array(
                'title' => 'Motivação setorial',
                'abbr' => 'Motivação da divisão setorial'
            ),
            'SOLD_AA_SOLICITACAO' => array(
                'title' => 'Ano',
                'abbr' => 'Ano da solicitação'
            ),
            'SG_FAMILIA_RESPONSAVEL' => array(
                'title' => 'Responsável',
                'abbr' => 'Responsável solicitante'
            ),
            'SOLD_CD_PT_RESUMIDO' => array(
                'title' => 'PTRES',
                'abbr' => ''
            ),
            'UNOR_CD_UNID_ORCAMENTARIA' => array(
                'title' => 'UO',
                'abbr' => ''
            ),
            'PTRS_SG_PT_RESUMIDO' => array(
                'title' => 'Sigla',
                'abbr' => ''
            ),            
            'SOLD_CD_ELEMENTO_DESPESA_SUB' => array(
                'title' => 'Natureza da despesa',
                'abbr' => '',
                'format' => 'Naturezadespesa'
            ),
            'TIDE_DS_TIPO_DESPESA' => array(
                'title' => 'Caráter da despesa',
                'abbr' => ''
            ),
            'SOLD_NR_PRIORIDADE' => array(
                'title' => 'Prioridade',
                'abbr' => 'Prioridade da solicitação'
            ),
            'TSOL_DS_TIPO_SOLICITACAO' => array(
                'title' => 'Status',
                'abbr' => ''
            ),
            'SOLD_VL_SOLICITADO' => array(
                'title' => 'Vr Solicitado',
                'abbr' => 'Valor requisitado pelo solicitante',
                'format' => 'Numerocor',
                'class' => 'valorgrid'
            ),
            'SOLD_VL_ATENDIDO' => array(
                'title' => 'Vr Atendido',
                'abbr' => 'Valor disponibilizado pela divisão setorial',
                'format' => 'Numerocor',
                'class' => 'valorgrid'
            )
        );
        $camposOcultos = array('EXERCICIO');

        $classeGrid = new Trf1_Orcamento_Grid();
        $grid = $classeGrid->criaGrid($this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);

        // Personalização do grid
        foreach ($camposDetalhes as $campo => $opcoes) {
            $grid->updateColumn($campo, $opcoes);
        }

        // Oculta campos do grid
        $grid->setColumnsHidden($camposOcultos);

        // Exibição do grid
        $this->view->grid = $grid->deploy();

        // Grava em sessão as preferências do usuário para essa grid
        $requisicao = $this->getRequest();
        $sessao = new Orcamento_Business_Sessao();
        $sessao->defineOrdemFiltro($requisicao);
    }

    public function incluirAction () {
        $this->view->telaTitle = 'Incluir solicitação de nova despesa';
        $formulario = new $this->_formulario();
        $this->view->formulario = $formulario;
        $this->view->perfil = $this->perfil;
        $valor = new Trf1_Orcamento_Valor();

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            $dados['SOLD_DT_SOLICITACAO'] = new Zend_Db_Expr('SYSDATE');

            unset($dados['SOLD_NR_SOLICITACAO']);

            if ($dados['RESP_CD_LOTACAO'] != '') {
                $responsavel = $dados['SOLD_CD_RESPONSAVEL'];
                $dados['SOLD_CD_RESPONSAVEL'] = $dados['RESP_CD_LOTACAO'];
            }

            // bug fix do campo removido do formlario
            if (!$dados['SOLD_IC_REC_DESCENTRALIZADO']) {
                $dados['SOLD_IC_REC_DESCENTRALIZADO'] = 1; //Nao
            }

            // aqui vai ser dipor
            if( $this->perfil != "dipor" && $this->perfil != "planejamento") {
                $dados['SOLD_CD_TIPO_SOLICITACAO'] = Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA;
                $dados['SOLD_VL_ATENDIDO'] = 0;
                $dados['SOLD_IC_REC_DESCENTRALIZADO'] = 1; //Nao
            }

            if ($formulario->isValid($dados)) {
                $negocio = new $this->_classeNegocio();
                $tabela = $negocio->tabela();
                $valor = new Trf1_Orcamento_Valor();
                $valordespesa = $valor->retornaValorParaBancoRod($dados["SOLD_VL_SOLICITADO"]);
                
                $dados["SOLD_VL_SOLICITADO"] = new Zend_Db_Expr("TO_NUMBER(" . $valordespesa . ")");

                if ($dados['SOLD_CD_TIPO_SOLICITACAO'] == Orcamento_Business_Dados::TIPO_SOLICITACAO_ATENDIDA) {
                    // grava na tabela de viculação
                    $codRecursoDescentralizar = $this->_negocioRecurso->incluirRecursoNovaDespesa($dados);
                    // Atualiza...
                    $dados['SOLD_NR_REC_DESCENTRALIZAR'] = $codRecursoDescentralizar['codigo'];
                }

                /*if (($this->perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR) || ($this->perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR) || ($this->perfil == Trf1_Orcamento_Definicoes::PERMISSAO_SECCIONAL)) { */
                    $valorAtendido = $valor->retornaValorParaBancoRod($dados["SOLD_VL_ATENDIDO"]);
                    $dados["SOLD_VL_ATENDIDO"] = new Zend_Db_Expr("TO_NUMBER(" . $valorAtendido . ")");
                /*}*/

                $registro = $tabela->createRow($dados);

                try {
                    $codigo = $registro->save();
                    
                    // inclui na tabela de log do orçamento
                    $this->_logdados->incluirLog( $codigo );
                    
                    $this->recriarCaches();

                    $this->_helper->flashMessenger(array(
                        message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO,
                        'status' => 'success'
                    ));
                } catch (Exception $e) {
                    $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                }

                $this->voltarIndexAction();
            } else {
                if ($responsavel) {
                    $dados['SOLD_CD_RESPONSAVEL'] = $responsavel;
                }

                $formulario->populate($dados);
            }
        }
    }

    public function detalheAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar solicitação de nova despesa';

        // Identifica o parâmetro da chave primária a ser buscada
        $chavePrimaria = $this->_getParam('cod');

        if ($chavePrimaria) {
            // Busca registro específico
            $negocio = new $this->_classeNegocio();
            $registro = $negocio->retornaRegistroNomeAmigavel($chavePrimaria);

            if ($registro) {
                // Exibe os dados do registro
                $this->view->dados = $registro;
            } else {
                $this->registroNaoEncontrado();
            }
        } else {
            $this->codigoNaoInformado();
        }
    }

    public function editarAction () {
        $this->view->telaTitle = 'Editar solicitação de nova despesa';
        // Instancia a regra de negócio
        $negocio = new $this->_classeNegocio();
        $camposChave = $negocio->chavePrimaria();
        $chavePrimaria = $this->_getParam('cod');
        $this->view->perfil = $this->perfil;
        $valor = new Trf1_Orcamento_Valor();

        if ($this->getRequest()->isGet()) {

            if ($chavePrimaria) {
                // Busca registro específico
                $registro = $negocio->retornaRegistro($chavePrimaria);
                
                if($this->perfil == 'seccional'){
                    
                }
                                
                if ($registro) {
                    $registro['RESP_CD_LOTACAO'] = $registro['SOLD_CD_RESPONSAVEL'];
                    $registro['SOLD_CD_RESPONSAVEL'] = $registro['RESPONSAVEL'];

                    //Verifica se a situação é diferente se solicitado e se o perfil é diferente de dipor e desenvolvedor
                    if (($registro["SOLD_CD_TIPO_SOLICITACAO"] != Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA) && (($this->perfil != Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR) && ($this->perfil != Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR))) {
                        $formulario = new $this->_formulario(Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR);
                        $plugin = new Trf1_Orcamento_Permissao();
                        $formulario = $plugin->camposSomenteLeitura($formulario, $registro);
                    } else {
                        $formulario = new $this->_formulario();
                    }
                    $formulario->populate($registro);
                } else {
                    $this->registroNaoEncontrado();
                }
            } else {
                $this->codigoNaoInformado();
            }
        } else {
            // Busca dados do formulário
            $formulario = new $this->_formulario();
            $dados = $this->getRequest()->getPost();

            if ($dados['RESP_CD_LOTACAO'] != '') {
                $responsavel = $dados['SOLD_CD_RESPONSAVEL'];
                $dados['SOLD_CD_RESPONSAVEL'] = $dados['RESP_CD_LOTACAO'];
            }

            if ($formulario->isValid($dados)) {
                $chavePrimaria = $this->_getParam('cod');
                // Instancia a model para edição do registro
                $tabela = $negocio->tabela();
                // Busca registro pela chave primária
                $registro = $tabela->find($chavePrimaria)->current();

                // caso tenha atualizado a despesa atualiza o recurso
                if (($dados['SOLD_CD_TIPO_SOLICITACAO'] == Orcamento_Business_Dados::TIPO_SOLICITACAO_ATENDIDA) && ($dados['SOLD_NR_REC_DESCENTRALIZAR'] != "")) {
                    $this->_negocioRecurso->atualizaRecurso($chavePrimaria, $dados);
                }

                // alterar status para atendido sem recurso
                if ($dados['SOLD_CD_TIPO_SOLICITACAO'] == Orcamento_Business_Dados::TIPO_SOLICITACAO_ATENDIDA && $dados['SOLD_NR_REC_DESCENTRALIZAR'] == "") {
                    // grava na tabela de viculação
                    $codRecursoDescentralizar = $this->_negocioRecurso->incluirRecursoNovaDespesa($chavePrimaria, $dados);
                    $dados['SOLD_NR_REC_DESCENTRALIZAR'] = $codRecursoDescentralizar['codigo'];
                }

                // alterar para outro status contendo recurso
                if (($dados['SOLD_CD_TIPO_SOLICITACAO'] != Orcamento_Business_Dados::TIPO_SOLICITACAO_ATENDIDA) && ($dados['SOLD_NR_REC_DESCENTRALIZAR'] != "")) {
                    // Exclui o recurso da tabela
                    // A exclusao so poderá ser feita após remoção da chave recd na tabela sold 
                    $remover = $dados['SOLD_NR_REC_DESCENTRALIZAR'];
                    $removdados['SOLD_NR_REC_DESCENTRALIZAR'] = NULL;
                    // remove o codigo do recurso 
                    $dados['SOLD_NR_REC_DESCENTRALIZAR'] = NULL;
                }

                // Não permite alteração na chave primária
                foreach ($camposChave as $chave) {
                    unset($dados[$chave]);
                }

                //Verifica se a situação é diferente se solicitado e se o perfil é diferente de dipor e desenvolvedor
                if (($registro["SOLD_CD_TIPO_SOLICITACAO"] != Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA) && (($this->perfil != Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR) && ($this->perfil != Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR))) {
                    $this->_helper->flashMessenger(array(
                        message => 'A solicitação não pode ser alterada, pois seus status é diferente de solicitada',
                        'status' => 'notice'
                    ));
                    $this->voltarIndexAction();
                }

                $valorSolicitado = $valor->retornaValorParaBancoRod($dados["SOLD_VL_SOLICITADO"]);
                $dados["SOLD_VL_SOLICITADO"] = new Zend_Db_Expr("TO_NUMBER(" . $valorSolicitado . ")");

                if (($this->perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR) || ($this->perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR)) {
                    $valorAtendido = $valor->retornaValorParaBancoRod($dados["SOLD_VL_ATENDIDO"]);
                    $dados["SOLD_VL_ATENDIDO"] = new Zend_Db_Expr("TO_NUMBER(" . $valorAtendido . ")");
                }

                $registro->setFromArray($dados);
                try {
                    // Grava as alterações no banco
                    $codigo = $registro->save();
                    
                    $this->_logdados->incluirLog( $codigo );
                    
                    if ($remover) {
                        $excluirRecurso = $this->_negocioRecurso->excluirRecurso($remover);
                    }
                    // exclui o recuso caso n seja descentralizado
                    if ($dados['SOLD_IC_REC_DESCENTRALIZADO'] == 0) {
                        $excluirRecurso = $this->_negocioRecurso->excluirRecurso($chavePrimaria);
                    }
                    // Recria os cache referentes a esta controlles
                    $this->recriarCaches();

                    $this->_helper->flashMessenger(array(
                        message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO,
                        'status' => 'success'
                    ));
                } catch (Exception $e) {
                    $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />' . $e->getMessage());
                }

                // Volta para a index
                $this->voltarIndexAction();
            } else {
                // Reapresenta o formulário para correção dos dados informados
                if ($responsavel) {
                    $dados['SOLD_CD_RESPONSAVEL'] = $responsavel;
                }
                $formulario->populate($dados);
            }
        }
        $this->view->formulario = $formulario;
    }

    public function excluirAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir solicitações de novas despesas';

        // Instancia a regra de negócio
        $negocio = new $this->_classeNegocio();

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {
            $chavePrimaria = $this->_getParam('cod');

            if ($chavePrimaria) {
                // Transforma o parâmetro informado para array de $chaves, se for o caso
                $chaves = explode(',', $chavePrimaria);

                // Busca os registros selecionados
                $registros = $negocio->retornaVariosRegistros($chaves);
                if ($registros) {
                    $semexclusao = Array();
                    $contsem = 0;
                    $comexclusao = Array();
                    $contcom = 0;
                    foreach ($registros as $registro) {
                        if ($registro["Tipo"] != Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA) {
                            $semexclusao[$contsem] = $registro["SOLD_NR_SOLICITACAO"];
                            $contsem++;
                        } else {
                            $comexclusao[$contcom] = $registro["SOLD_NR_SOLICITACAO"];
                            $contcom++;
                        }
                    }
                    $comexclusao = implode(',', $comexclusao);
                    $this->view->codigo = $negocio->chavePrimaria();
                    $this->view->dados = $registros;
                    $this->view->perfil = $this->perfil;

                    $this->view->naoexcluir = $semexclusao;
                    $this->view->excluir = $comexclusao;
                } else {
                    $this->registroNaoEncontrado();
                }
            } else {
                $this->codigoNaoInformado();
            }
        } else {
            // Busca a confirmação da exclusão
            $excluir = $this->getRequest()->getPost();
            if ($excluir['cmdExcluir'] == 'Sim') {
                $chavePrimaria = $this->_getParam('cod');

                if ($chavePrimaria) {
                    if ($excluir['cod']) {
                        $chaves = explode(',', $excluir['cod']);
                    } else {
                        $chaves = explode(',', $chavePrimaria);
                    }

                    try {
                        // Exclui o registro
                        $negocio->exclusaoLogica($chaves);
                        // Grava na tabela de log do orçamento
                        $this->_logdados->incluirLog();
                        
                        // Recria os cache referentes a esta controlles
                        $this->recriarCaches();

                        $this->_helper->flashMessenger(array(
                            message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_SUCESSO,
                            'status' => 'success'
                        ));
                    } catch (Exception $e) {
                        $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_ERRO . '<br />' . $e->getMessage());
                    }
                }
            } else {
                $this->_helper->flashMessenger(array(
                    message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_CANCELAR,
                    'status' => 'notice'
                ));
            }

            $this->voltarIndexAction();
        }
    }

    public function ajaxnovadespesaAction () {
        $despesa = $this->_getParam('despesa');
        $negocio = new Trf1_Orcamento_Negocio_Despesa();
        $dados = $negocio->retornaDespesa($despesa);
        $this->_helper->json->sendJson($dados);
    }

    private function recriarCaches () {
        $cache = new Trf1_Orcamento_Cache();
        $cache->excluirCachesSensiveis($this->_controle);
    }

    private function registroNaoEncontrado () {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_REGISTRO_NAO_ENCONTRADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(
            message => $erro,
            'status' => 'notice'
        ));
        $this->voltarIndexAction();
    }

    private function codigoNaoInformado () {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(
            message => $erro,
            'status' => 'notice'
        ));
        $this->voltarIndexAction();
    }

    private function erroOperacao ($mensagemErro) {
        $erro = $mensagemErro;

        // Registra o erro
        $log = new Trf1_Orcamento_Log();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::ERR);

        $this->_helper->flashMessenger(array(
            message => $erro,
            'status' => 'error'
        ));
    }

    /**
     * Redireciona para a indexAction do _modulo e _controle
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function voltarIndexAction () {
        // Grava em sessão as preferências do usuário para essa grid
        $sessao = new Orcamento_Business_Sessao();
        $url = $sessao->retornaOrdemFiltro($this->_controle);

        if ($url) {
            // Redireciona para a url salva em sessão
            $this->_redirect($url);
        } else {
            // Redireciona para a url combinada entre modulo/controle/index
            $this->_redirect($this->_modulo . '/' . $this->_controle);
        }
    }

}
