<?php
/**
 * Contém métodos para tratamento de erro na aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Core
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Classe genérica para exibição de dados referentes a um erro ao invés de gerar
 * uma excessão propriamente dita.
 *
 * @category Orcamento
 * @package Orcamento_Business_Memoria
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
class Orcamento_Business_Exception extends Zend_Exception
{

    /**
     * Método construtor implementa o padrão Singleton e não permite que essa
     * classe seja instanciada através do "new".
     *
     * @return void
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct ( $msg = '', $cod = 0, Exception $e = null )
    {
        // ************************************************************
        // Valida os parâmetros informados e sua ordem na assinatura do método
        // ************************************************************
        $this->validaParametros ( $msg, $cod, $e );
        
        // ************************************************************
        // Retorna a requisição atual
        // ************************************************************
        try {
            // Instancia o helper para requisição
            $helper = new Zend_Controller_Action_Helper_Redirector ();
            $requisicao = $helper->getRequest ();
        } catch ( Exception $e ) {
            $msgErro = 'Não foi possível obter dados da requisição.';
            
            // Gera o erro
            throw new Zend_Exception ( $msgErro );
        }
        
        // Busca dados da requisição
        $modulo = $requisicao->getModuleName ();
        $controle = $requisicao->getControllerName ();
        $acao = $requisicao->getActionName ();
        
        $oParametros = $requisicao->getParams ();
        
        // Elimana parâmetros fixos
        unset ( $oParametros [ 'module' ] );
        unset ( $oParametros [ 'controller' ] );
        unset ( $oParametros [ 'action' ] );
        
        $parametros = '';
        foreach ( $oParametros as $parametro => $valor ) {
            // ...continua a montagem da $url
            $parametros .= '/' . $parametro . '/' . $valor;
        }
        
        // ************************************************************
        // Retorna dados e perfil do usuário logado
        // ************************************************************
        $pUsuario = $this->retornaPerfilUsuarioLogado ();
        
        // Retorna dados sobre o perfil
        $usuario = $pUsuario [ 'usuario' ];
        $perfil = $pUsuario [ 'perfil' ];
        $ug = $pUsuario [ 'ug' ];
        $responsavel = $pUsuario [ 'responsavel' ];
        
        // ************************************************************
        // Define dados do erro
        // ************************************************************
        $bErro = false;
        if ( $e ) {
            $erroCod = $e->getCode ();
            $erroMsg = $e->getMessage ();
            
            $trace = $e->getTraceAsString ();
            $origem = $e->getFile ();
            $linha = $e->getLine ();
            
            $bErro = true;
        }
        
        // ************************************************************
        // Define array $erro para repasse via sessão
        // ************************************************************
        $erro [ 'msg' ] = $msg;
        $erro [ 'cod' ] = $cod;
        
        $erro [ 'bErro' ] = $bErro;
        $erro [ 'erroCod' ] = $erroCod;
        $erro [ 'erroMsg' ] = $erroMsg;
        $erro [ 'erroTrace' ] = $trace;
        $erro [ 'erroOrigem' ] = $origem;
        $erro [ 'erroLinha' ] = $linha;
        
        $erro [ 'modulo' ] = $modulo;
        $erro [ 'controle' ] = $controle;
        $erro [ 'acao' ] = $acao;
        $erro [ 'parametros' ] = $parametros;
        
        $erro [ 'usuario' ] = $usuario;
        $erro [ 'perfil' ] = $perfil;
        $erro [ 'ug' ] = $ug;
        $erro [ 'responsavel' ] = $responsavel;
        
        // Cria a sessão contendo dados sobre o erro, se for o caso
        $sessao = new Orcamento_Business_Sessao ();
        $sessao->defineErro ( $erro );
        
        // Redireciona para tela apropriada
        $helper->direct ( 'erro', 'index', 'orcamento' );
    }

    /**
     * Realiza a validação inicial dos parâmetros obrigatórios
     *
     * @param string $msg
     *        Texto utilizado como título
     * @param integer $cod
     *        Número com o código do erro
     * @param Exception $e
     *        Objeto do erro
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function validaParametros ( $msg, $cod, $e )
    {
        // Validações iniciais dos parâmetros
        $msgErro = '';
        $msgErro .= 'Parâmetro $msg da chamada throw new ';
        $msgErro .= 'Orcamento_Business_Exception ( $msg, $cod, $e ); ';
        $msgErro .= 'deve ser uma string.';
        
        if ( !is_string ( $msg ) && !is_null ( $msg ) ) {
            // Gera o erro
            throw new Zend_Exception ( $msgErro );
        }
        
        $msgErro = '';
        $msgErro .= 'Parâmetro $cod da chamada throw new ';
        $msgErro .= 'Orcamento_Business_Exception ( $msg, $cod, $e ); ';
        $msgErro .= 'deve ser um número inteiro.';
        
        if ( !is_int ( $cod ) && !is_null ( $cod ) ) {
            // Gera o erro
            throw new Zend_Exception ( $msgErro );
        }
    }

    /**
     * Retorna dados do usuário logado
     *
     * @throws Zend_Exception
     * @return array Dados do perfil do usuário logado
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaPerfilUsuarioLogado ()
    {
        try {
            // Retorna perfil do usuário logado
            $sessao = new Orcamento_Business_Sessao ();
            $p = $sessao->retornaPerfil ();
            
            return $p;
        } catch ( Exception $e ) {
            $msgErro = 'Não foi possível obter o perfil do usuário logado.';
            
            // Gera o erro
            throw new Zend_Exception ( $msgErro );
        }
    }

    /**
     * a descrever...
     * 
     * @param Exception $e
     *        Objeto que será manipulado para melhorar algumas mensagens de
     *        erro.
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function trataMsgErro ( Exception $e = null )
    {
        $msg = '';
        
        return $msg;
    }

}