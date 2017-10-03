<?php
/**
 * Contém funcionalidade básicas para telas de CRUD
 * 
 * e-Admin
 * e-Orçamento
 * Business - Tela
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades para uso em telas CRUD
 *
 * @category Orcamento
 * @package Orcamento_Business_Tela_Crud
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */
class Orcamento_Business_Tela_Crud extends Orcamento_Business_Tela_Base
{

    /**
     * Nome padrão do formulário
     *
     * @var string
     */
    protected $_nomeFormulario = null;

    /**
     * Formulário negocial para o CRUD
     *
     * @var string
     */
    protected $_formulario = null;

    /**
     * Noma da classe fachada
     *
     * @var string
     */
    protected $_classeFacade = null;

    /**
     * Classe facade
     *
     * @var object
     */
    protected $_facade = null;

    /**
     * Método init para ser executado na inicialização desta classe.
     *
     * @see Orcamento_Business_Tela_Base::init()
     * @tutorial Toda classe extendida deve utilizar, obrigatoriamente, no
     *           início do método init() a intrução: parent::init();
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        try {
            // Conforme orientado acima na tag @tutorial
            parent::init ();
            
            // Verifica se a classe facade foi informada
            $msg = 'Obrigatório informar a classe facade para uso desta classe';
            
            if ( !$this->_classeFacade ) {
                // Gera o erro
                throw new Zend_Exception ( $msg );
            }
            
            // Nome da classe facade em minúsculas
            $facade = strtolower ( $this->_classeFacade );
            
            // Instancia a classe fábrica das fachadas
            $fabrica = new Trf1_Facade_Factory ();
            
            // Instancia a classe de fachada
            $this->_facade = $fabrica->retornaInstancia ( 'orcamento', $facade );
            
            // Define o nome do formulário
            $nControle = ucfirst ( $this->_controle );
            $this->_nomeFormulario = "Orcamento_Form_$nControle";
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Apresenta a listagem dos campos da tabela
     *
     * @param string $funcionalidade
     *        Informa o título desta funcionalidade
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function listar ( $funcionalidade )
    {
        try {
            // Título da tela (action)
            $this->view->telaTitle = $funcionalidade;
            
            // Retorna o grid gerado através da facade
            $grid = $this->montaGrid ( $funcionalidade );
            
            // Exibição do grid
            $this->view->grid = $grid->deploy ();
            
            // Grava em sessão as preferências do usuário para essa grid
            $requisicao = $this->_requisicao;
            $sessao = new Orcamento_Business_Sessao ();
            $sessao->defineOrdemFiltro ( $requisicao );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Visualiza um registro em detalhes
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function detalhe ()
    {
        try {
            // Define a ação de detalhe
            $acao = Orcamento_Business_Dados::ACTION_DETALHE;
            
            // Retorna parâmetros informados via get, após validações
            $parametros = $this->trataParametroGet ( 'cod' );
            
            // Busca os dados a exibir, após validações
            $registro = $this->trataRegistro ( $acao, $parametros );
            
            // Exibe o registro retornado
            $this->view->dados = $registro;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Inclui um novo registro
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function incluir ()
    {
        try {
            // Define a ação de incluir
            $acao = Orcamento_Business_Dados::ACTION_INCLUIR;
            
            // Cria o formulário vazio
            $formulario = $this->retornaFormulario ( $acao );
            
            // Faz transformações no formulário, se necessário
            $formulario = $this->transformaFormulario ( $formulario, $acao );
            
            // Exibe o formulário
            $this->view->formulario = $formulario;
            
            // Grava o novo registro
            $this->gravaDados ( $acao, $formulario );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Edita o registro selecionado
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function editar ()
    {
        try {
            // Define a ação de editar
            $acao = Orcamento_Business_Dados::ACTION_EDITAR;
            
            if ( $this->_requisicao->isGet () ) {
                // Retorna parâmetros informados via get, após validações
                $parametros = $this->trataParametroGet ( 'cod' );
                
                // Busca os dados a exibir, após validações
                $registro = $this->trataRegistro ( $acao, $parametros );
                
                // Cria o formulário populado com os dados
                $formulario = $this->popularFormulario ( $acao, $registro );
                
                // Faz transformações no formulário, se necessário
                $formulario = $this->transformaFormulario ( $formulario, $acao );
                
                // Bloqueia a edição de campos de chave primária (ou composta)
                $this->bloqueiaCamposChave ( $formulario );
                
                // Bloqueia todos os campos
                $this->bloqueiaCamposTodos ( $acao, $formulario, $registro );
                
                // Exibe o formulário
                $this->view->formulario = $formulario;
            } else {
                // Cria o formulário vazio
                $formulario = $this->retornaFormulario ( $acao );
                
                // Grava o novo registro
                $this->gravaDados ( $acao, $formulario );
            }
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Exclui um ou mais registros selecionados
     *
     * @param boolean $bLogica
     *        Informa se será uma exclusão lógica ou física do registro
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function excluir ( $bLogica = true )
    {
        try {
            // Define a ação de excluir
            $acao = Orcamento_Business_Dados::ACTION_EXCLUIR;
            
            // Retorna parâmetros informados via get, após validações
            $parametros = $this->trataParametroGet ( 'cod' );
            
            if ( $this->_requisicao->isGet () ) {
                // Busca chave primária (ou composta)
                $chavePrimaria = $this->retornaChave ();
                
                // Busca os dados a exibir, após validações
                $registros = $this->trataRegistro ( $acao, $parametros );
                
                // Exibe os dados
                $this->view->codigo = $chavePrimaria;
                $this->view->dados = $registros;
            } else {               
                // Exclui o registro, após confirmação
                $this->excluiDados ( $parametros, $bLogica );
                $this->gravaLog($parametros);
            }
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Restaura um ou mais registros excluídos logicamente no banco
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function restaurar ()
    {
        try {
            // Define a ação de restaurar
            $acao = Orcamento_Business_Dados::ACTION_RESTAURAR;
            
            // Retorna parâmetros informados via get, após validações
            $parametros = $this->trataParametroGet ( 'cod' );
            
            if ( $this->_requisicao->isGet () ) {
                // Busca chave primária (ou composta)
                $chavePrimaria = $this->retornaChave ();
                
                // Busca os dados a exibir, após validações
                $registros = $this->trataRegistro ( $acao, $parametros );
                
                // Exibe os dados
                $this->view->codigo = $chavePrimaria;
                $this->view->dados = $registros;
            } else {
                // Restaura o registro, após confirmação
                $this->restauraDados ( $parametros );
            }
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Salva o novo registro após validações e outras tarefas
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param Zend_Form $formulario
     *        Contém o formulário e seus dados para uso nesta operação
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function gravaDados ( $acao, $formulario )
    {
        try {
            if ( $this->_requisicao->isPost () ) {
                // Busca dados do formulário após o post
                $dados = $this->_requisicao->getPost ();
                
                if ( !$formulario->isValid ( $dados ) ) {
                    // Reapresenta os dados no formulário para correção do
                    // usuário
                    $formulario->populate ( $dados );
                    
                    $this->view->formulario = $formulario;
                } else {
                    // Passa os dados para a facade realizar a ação
                    $resultado = $this->gravaRegistro ( $acao, $dados );
                    
                    // Define valor da variável
                    $bSucesso = $resultado [ 'sucesso' ];
                    
                    // Define mensagem após a ação
                    $msg = $this->retornaMensagem ( $acao, $bSucesso );
                    
                    if ( $bSucesso != true ) {
                        // Redefine mensagem de erro e status
                        $msg .= '<br />' . $resultado [ 'msgErro' ];
                        
                        // Exibe mensagem de erro na próxima tela
                        $this->operacaoErro ( $msg );
                    }
                    
                    // Apresenta mensagem de sucesso na próxima tela
                    $this->operacaoSucesso ( $msg );
                }
            }
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Exclui o registro após validações e outras tarefas
     *
     * @param array $chaves
     *        Informa uma ou mais chaves primárias (ou composta) de cada
     *        registro a excluir
     * @param boolean $bLogica        
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function excluiDados ( $chaves, $bLogica )
    {
        try {
            // Busca a confirmação da exclusão
            $excluir = $this->trataParametroPost ( 'cmdExcluir' );
            
            if ( $excluir != 'Sim' ) {
                // Cancelamento da exclusão pelo usuário
                $this->exclusaoCancelada ();
            }
            
            // Define a ação de excluir
            $acao = Orcamento_Business_Dados::ACTION_EXCLUIR;
            
            // Efetua a exclusão do registro
            $resultado = $this->excluiRegistro ( $chaves, $bLogica );
            
            if ( !$resultado [ 'sucesso' ] ) {
                // Define mensagem de erro e status
                $msg = $this->retornaMensagem ( $acao, false );
                $msg .= '<br />' . $resultado [ 'msgErro' ];
                
                // Exibe mensagem de erro na próxima tela
                $this->operacaoErro ( $msg );
            }
            
            // Define mensagem de sucesso
            $msg = $this->retornaMensagem ( $acao, true );
            
            // Apresenta mensagem de sucesso na próxima tela
            $this->operacaoSucesso ( $msg );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Restaura registros logicamente excluídos no banco
     *
     * @param array $chaves
     *        Informa uma ou mais chaves primárias (ou composta) de cada
     *        registro a excluir
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function restauraDados ( $chaves )
    {
        try {
            // Busca a confirmação da restauração
            $restaurar = $this->trataParametroPost ( 'cmdRestaurar' );
            
            if ( $restaurar != 'Sim' ) {
                // Cancelamento da exclusão pelo usuário
                $this->restauracaoCancelada ();
            }
            
            // Define a ação de restaurar
            $acao = Orcamento_Business_Dados::ACTION_RESTAURAR;
            
            // Efetua a restauração do registro
            $resultado = $this->restauraRegistro ( $chaves );
            
            if ( !$resultado [ 'sucesso' ] ) {
                // Define mensagem de erro e status
                $msg = $this->retornaMensagem ( $acao, false );
                $msg .= '<br />' . $resultado [ 'msgErro' ];
                
                // Exibe mensagem de erro na próxima tela
                $this->operacaoErro ( $msg );
            }
            
            // Define mensagem de sucesso
            $msg = $this->retornaMensagem ( $acao, true );
            
            // Apresenta mensagem de sucesso na próxima tela
            $this->operacaoSucesso ( $msg );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade os dados para exibição
     *
     * @param boolean $bUsaCache
     *        Determina se a listagem deverá ser cacheada ou não
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaListagem ( $bUsaCache = true )
    {
        try {
            // Retorna os dados para exibição no grid
            $dados = $this->_facade->retornaListagem ( $bUsaCache );
            
            // Devolve os dados
            return $dados;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade o registro para exibição em detalhes
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param string $chave
     *        Traz o valor da chave primária (ou composta) do registro a
     *        procurar
     * @return array $registro
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaRegistro ( $acao, $chave )
    {
        try {
            // Retorna o registro conforme chave informada
            $registro = $this->_facade->retornaRegistro ( $acao, $chave );
            
            // Devolve o registro recebido
            return $registro;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade a operação (incluir ou editar) informada como parâmetro
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param array $dados
     *        Campos a serem gravados no banco
     * @return mixed resultado da operação
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function gravaRegistro ( $acao, $dados )
    {
        try {
            // Passa os dados para a facade realizar a inclusão ou edição
            $resultado = $this->_facade->$acao ( $dados );
            
            // Devolve erro gerado ou nulo no caso de sucesso
            return $resultado;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade a operação de excluir
     *
     * @param array $chave
     *        Valores de uma ou mais chaves primárias (ou compostas) para a
     *        realização da operação
     * @param boolean $bLogica        
     * @return mixed resultado da operação
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function excluiRegistro ( $chave, $bLogica )
    {
        try {
            // Passa os dados para a facade realizar a exclusão
            if ( $bLogica ) {
                // Efetua a exclusão lógia do registro
                $resultado = $this->_facade->exclusaoLogica ( $chave );
            } else {
                // Efetua a exclusão física, e definitiva, do registro
                $resultado = $this->_facade->excluir ( $chave );
            }
            
            // Devolve erro gerado ou nulo no caso de sucesso
            return $resultado;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade a operação de restaurar
     *
     * @param array $chave
     *        Valores de uma ou mais chaves primárias (ou compostas) para a
     *        realização da operação
     * @return mixed resultado da operação
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function restauraRegistro ( $chave )
    {
        try {
            // Passa os dados para a facade realizar a restauração de registro
            $resultado = $this->_facade->restaurar ( $chave );
            
            // Devolve erro gerado ou nulo no caso de sucesso
            return $resultado;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade o(s) campo(s) da chave primária (ou composta)
     *
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaChave ()
    {
        try {
            // Busca chave primária (ou composta)
            $chavePrimaria = $this->_facade->retornaChavePrimaria ();
            
            // Devolve a chave encontrada
            return $chavePrimaria;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade os dados para exibição
     *
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaOpcoesGrid ()
    {
        try {
            // Retorna os dados para exibição no grid
            $opcoes = $this->_facade->retornaOpcoesGrid ();
            
            // Devolve os dados
            return $opcoes;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade se deve, ou não, bloquear os campos do formulário
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param Zend_Form $formulário
     *        Formulário em uso para bloqueio dos campos
     * @param array $dados
     *        Dados para popular o formulário
     * @return boolean
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaDeveBloquearCampos ( $acao, $formulario, $dados )
    {
        try {
            // Define alias...
            $a = $acao;
            $f = $formulario;
            $d = $dados;
            
            // Retorna os dados para exibição no grid
            $bTravaCampos = $this->_facade->retornaDeveBloquearCampos ( $a, $f, 
            $d );
            
            // Devolve o resultado
            return $bTravaCampos;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Reune os parâmetros para a confecção de novo grid para exibição em tela
     *
     * @see Orcamento_Business_Tela_Grid::criaGrid
     * @param string $funcionalidade
     *        Informa o título exibido na tela para composição do nome do
     *        arquivo a exportar
     * @return BvB_Grid
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function montaGrid ( $funcionalidade )
    {
        try {
            // Retorna as opções para confecção do grid
            $opcoesGrid = $this->retornaOpcoesGrid ();
            
            // Define a opção 'funcionalidade' com a opção passada por parâmetro
            $opcoesGrid [ 'funcionalidade' ] = $funcionalidade;
            
            // Define opção 'dados' com o retorno dos dados a serem exibidos
            $opcoesGrid [ 'dados' ] = $this->retornaListagem ();
            
            // Define a opção 'chavePrimaria' com o retorno do(s) campo(s)
            // chave(s)
            $opcoesGrid [ 'chavePrimaria' ] = $this->retornaChave ();
            
            // Instancia o grid
            $classeGrid = new Orcamento_Business_Tela_Grid ();
            
            // Define o grid
            $grid = $classeGrid->criaGrid ( $opcoesGrid );
            
            // Devolve o objeto grid
            return $grid;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca registro conforme chave primária, verifica se o mesmo existe e
     * apresenta mensagem de erro específica caso contrário
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param array $chave
     *        Informa uma ou mais chaves primárias (ou composta) de cada
     *        registro a excluir
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function trataRegistro ( $acao, $chave )
    {
        try {
            // Busca registro específico conforme chave primária (ou composta)
            $registro = $this->retornaRegistro ( $acao, $chave );
            
            if ( !$registro ) {
                // Gera erro se não encontrou o registro baseado na chave
                $this->registroNaoEncontrado ();
            }
            
            // Devolve o registro encontrado
            return $registro;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca valor do parâmetro por Post
     *
     * @param string $parametro
     *        Nome do parâmetro a buscar no Post
     * @return mixed (array|String)
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function trataParametroPost ( $parametro )
    {
        try {
            // Identifica o parâmetro a ser procurado
            $valor = $this->getRequest ()->getPost ( $parametro );
            
            // Valida o(s) parâmetro(s) informado
            $parametros = $this->retornaParametro ( $valor );
            
            // Devolve o valor do parâmetro recebido
            return $parametros;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca valor do parâmetro por Get
     *
     * @param string $parametro
     *        Nome do parâmetro a buscar no Get
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function trataParametroGet ( $parametro )
    {
        try {
            // Identifica o parâmetro a ser procurado
            $valor = $this->_getParam ( $parametro );
            
            // Valida o(s) parâmetro(s) informado
            $parametros = $this->retornaParametro ( $valor );
            
            // Devolve o valor do parâmetro recebido
            return $parametros;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Verifica se o parâmetro foi informado, se há separador de parâmetros,
     * converte os parâmetros em array se aplicável e apresenta mensagem de erro
     * específica conforme o caso
     *
     * @param string $parametro
     *        Valores separados por vírgula ou o $separador abaixo
     * @param string $separador
     *        Caracter, tipicamente a vírgula, que separa os valores na string
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaParametro ( $parametro, $separador = ',' )
    {
        try {
            if ( !$parametro ) {
                // Gera erro se não encontrou a chave primária
                $this->codigoNaoInformado ();
            }
            
            // Verifica se o parâmetro contém vírgulas [,]
            $posicao = strpos ( $parametro, ',' );
            
            // Unifica a variável
            $parametros = $parametro;
            
            if ( $posicao > 0 ) {
                // Transforma o parâmetro informado para array
                $parametros = explode ( $separador, $parametro );
            }
            
            // Devolve o array contendo os valores já separados
            return $parametros;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Instancia novo formulário ou devolve o já instanciado
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaFormulario ( $acao )
    {
        try {
            // Retorna atual objeto ou nulo se o mesmo ainda não foi instanciado
            $formulario = $this->_formulario;
            
            if ( !$formulario ) {
                // Instancia o objeto formulario
                $formulario = new $this->_nomeFormulario ();
            }
            
            // Define o texto do botão de submit
            $nomes [ Orcamento_Business_Dados::ACTION_INCLUIR ] = 'Incluir';
            $nomes [ Orcamento_Business_Dados::ACTION_EDITAR ] = 'Salvar';
            
            $nomeEnviar = $nomes [ $acao ];
            
            // Se existir o botão...
            if ( $formulario->Enviar != null ) {
                // Redefine o nome do botão
                $formulario->Enviar->SetLabel ( $nomeEnviar );
            }
            
            // Devolve o objeto $formulario
            return $formulario;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Instancia o formulário e o popula com o $registro informado
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param array $registro
     *        Dados para popular o formulário
     * @return Zend_Form $formulario
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function popularFormulario ( $acao, $registro )
    {
        try {
            // Chama o método para criar o formulário vazio
            $formulario = $this->retornaFormulario ( $acao );
            
            // Preenche os campos do formulário com os campos do $registro
            $formulario->populate ( $registro );
            
            // Devolve o formulário preenchido com o $registro
            return $formulario;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na facade as transformações no formulario, se aplicável
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function transformaFormulario ( $formulario, $acao )
    {
        try {
            // Retorna o formulário após transformações, se aplicável
            $form = $this->_facade->transformaFormulario ( $formulario, $acao );
            
            // Devolve o formulário
            return $form;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Bloqueia o(s) campo(s) chave primária (ou composta) do $formulario para
     * evitar a edição de tal(is) campo(s)
     *
     * @param Zend_Form $formulário
     *        Formulário em uso para bloqueio dos campos
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function bloqueiaCamposChave ( $formulario )
    {
        try {
            // Busca chave primária (ou composta)
            $chavePrimaria = $this->retornaChave ();
            
            // Varre um ou mais campos da chave primária (ou composta)
            foreach ( $chavePrimaria as $campo ) {
                // Bloqueia o campo
                $this->bloqueiaCampo ( $formulario, $campo );
            }
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Bloqueia todos os campos do $formulario
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param Zend_Form $formulário
     *        Formulário em uso para bloqueio dos campos
     * @param array $dados
     *        Dados para popular o formulário
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function bloqueiaCamposTodos ( $acao, $formulario, $dados )
    {
        try {
            // Define alias...
            $a = $acao;
            $f = $formulario;
            $d = $dados;
            
            // Verifica se deve haver bloqueio de campos
            $trava = $this->retornaDeveBloquearCampos ( $a, $f, $d );
            
            if ( $trava ) {
                // Busca os campos do $formulario
                $campos = $formulario->getElements ();
                
                foreach ( $campos as $campo ) {
                    // Bloqueia o campo
                    $this->bloqueiaCampo ( $formulario, $campo->getName () );
                }
            }
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Bloqueia a edição do $campo informado do $formulario
     *
     * @param Zend_Form $formulário
     *        Formulário em uso para bloqueio dos campos
     * @param string $campo
     *        Nome do campo a ser bloqueado
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function bloqueiaCampo ( $formulario, $campo )
    {
        try {
            // Alterar o atributo readonly para bloquear o campo
            $formulario->$campo->setAttrib ( 'readonly', true );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Retorna a mensagem a ser apresentada, conforme $acao e $sucesso
     *
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @param boolean $sucesso
     *        Informa se a operação foi realizada com sucesso ou não
     * @return string
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function retornaMensagem ( $acao, $sucesso )
    {
        try {
            // asdsad
            $m [ 'incluir' ] [ 1 ] = Orcamento_Business_Dados::MSG_INCLUIR_SUCESSO;
            $m [ 'incluir' ] [ 0 ] = Orcamento_Business_Dados::MSG_INCLUIR_ERRO;
            
            $m [ 'editar' ] [ 1 ] = Orcamento_Business_Dados::MSG_ALTERAR_SUCESSO;
            $m [ 'editar' ] [ 0 ] = Orcamento_Business_Dados::MSG_ALTERAR_ERRO;
            
            $m [ 'excluir' ] [ 1 ] = Orcamento_Business_Dados::MSG_EXCLUIR_SUCESSO;
            $m [ 'excluir' ] [ 0 ] = Orcamento_Business_Dados::MSG_EXCLUIR_ERRO;
            
            $m [ 'restaurar' ] [ 1 ] = Orcamento_Business_Dados::MSG_RESTAURAR_SUCESSO;
            $m [ 'restaurar' ] [ 0 ] = Orcamento_Business_Dados::MSG_RESTAURAR_ERRO;
            
            $m [ 'aplicar' ] [ 1 ] = Orcamento_Business_Dados::MSG_APLICARREGRA_SUCESSO;
            $m [ 'aplicar' ] [ 0 ] = Orcamento_Business_Dados::MSG_APLICARREGRA_ERRO;
            
            // Busca a mensagem
            $mensagem = $m [ $acao ] [ $sucesso ];
            
            // Devolve a mensagem
            return $mensagem;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Tratamento padronizado para apresentação de mensagem na tela da
     * funcionalidade quando o parâmetro, tipicamente 'cod', não for informado.
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function codigoNaoInformado ()
    {
        try {
            // Define a mensagem no caso de cósigo não informado
            $msg = Orcamento_Business_Dados::MSG_CODIGO_NAO_INFORMADO;
            
            // Exibe a mensagem com o status de alerta
            $this->operacaoAlerta ( $msg );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Tratamento padronizado para apresentação de mensagem na tela da
     * funcionalidade quando o registro não for encontrado.
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function registroNaoEncontrado ()
    {
        try {
            // Define a mensagem no caso de registro não encontrado
            $msg = Orcamento_Business_Dados::MSG_REGISTRO_NAO_ENCONTRADO;
            
            // Exibe a mensagem com o status de alerta
            $this->operacaoAlerta ( $msg );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Tratamento padronizado para apresentação de mensagem na tela da
     * funcionalidade quando uma exclusão for cancelada
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function exclusaoCancelada ()
    {
        try {
            // Define a mensagem no caso de cancelamento da exclusão
            $msg = Orcamento_Business_Dados::MSG_EXCLUIR_CANCELAR;
            
            // Exibe a mensagem com o status de alerta
            $this->operacaoAlerta ( $msg );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Tratamento padronizado para apresentação de mensagem na tela da
     * funcionalidade quando uma restauracao for cancelada
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function restauracaoCancelada ()
    {
        try {
            // Define a mensagem no caso de cancelamento da exclusão
            $msg = Orcamento_Business_Dados::MSG_RESTAURAR_CANCELAR;
            
            // Exibe a mensagem com o status de alerta
            $this->operacaoAlerta ( $msg );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Tratamento padronizado para apresentação de mensagem de sucesso
     *
     * @param string $mensagem
     *        Informa a mensagem de sucesso a ser apresentada
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function operacaoSucesso ( $mensagem )
    {
        try {
            // Define status para a mensagem
            $statusSucesso = Orcamento_Business_Dados::MSG_STATUS_SUCESSO;
            
            // Exibe a mensagem na tela com status de sucesso
            $this->mostraMensagem ( $mensagem, $statusSucesso );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Tratamento padronizado para apresentação de mensagem de alerta
     *
     * @param string $mensagem
     *        Informa a mensagem de alerta a ser apresentada
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function operacaoAlerta ( $mensagem )
    {
        try {
            // Define status para a mensagem
            $statusAlerta = Orcamento_Business_Dados::MSG_STATUS_ALERTA;
            
            // Exibe a mensagem na tela com status de alerta
            $this->mostraMensagem ( $mensagem, $statusAlerta );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Tratamento padronizado para apresentação de mensagem de erro
     *
     * @param string $mensagem
     *        Informa a mensagem de erro a ser apresentada
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function operacaoErro ( $mensagem )
    {
        try {
            // Define status para a mensagem
            $statusErro = Orcamento_Business_Dados::MSG_STATUS_ERRO;
            
            // Exibe a mensagem na tela com status de erro
            $this->mostraMensagem ( $mensagem, $statusErro );
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Chama função para exibição de mensagem, via flashMessenger, na tela
     * posterior a action atual
     *
     * @param string $mensagem
     *        Mensagem a exibir
     * @param string $status
     *        Status do flashMessenger, sendo success, notice ou error
     * @param boolean $bVoltaIndex
     *        Se true, volta para a indexAction
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function mostraMensagem ( $mensagem, $status, $bVoltaIndex = true )
    {
        try {
            // Instancia a sessão..
            $sessao = new Orcamento_Business_Sessao ();
            $sessao->defineMensagemExtra ( $mensagem, $status );
            
            // Retorna todas as mensagens a exibir
            $mensagens = $sessao->retornaMensagemExtra ( true );
            
            foreach ( $mensagens as $unid ) {
                $msg = $unid [ 'mensagem' ];
                $sts = $unid [ 'status' ];
                
                // Chama a função do _helper para cada exibição de mensagem
                $this->_helper->flashMessenger ( 
                array ( message => $msg, 'status' => $sts ) );
            }
            
            if ( $bVoltaIndex ) {
                // Volta a indexAction
                $this->voltarIndexAction ();
            }
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Redireciona para a indexAction do _modulo e _controle
     *
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function voltarIndexAction ()
    {
        try {
            // Grava em sessão as preferências do usuário para essa grid
            $sessao = new Orcamento_Business_Sessao ();
            $url = $sessao->retornaOrdemFiltro ( $this->_controle );
            
            if ( $url ) {
                // Redireciona para a url salva em sessão
                $this->_redirect ( $url );
            } else {
                // Redireciona para a url combinada entre modulo/controle/index
                $this->_redirect ( $this->_modulo . '/' . $this->_controle );
            }
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Define o nome da classe facade desta funcionalidade
     *
     * @param string $classeFacade
     *        Título a ser apresentado na barra de título do navegador
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function defineFacade ( $classeFacade )
    {
        try {
            // Define o nome da classe facade
            $this->_classeFacade = $classeFacade;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Retorna o nome da classe facade desta funcionalidade
     *
     * @return $_classeFacade
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaFacade ()
    {
        try {
            // Retorna o nome da classe facade
            return $this->_classeFacade;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }
    
}