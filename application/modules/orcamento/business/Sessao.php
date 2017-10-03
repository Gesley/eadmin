<?php
/**
 * Contém constantes e valores padrão dos dados do banco e-Admin e-Orçamento
 * Core
 *
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Classe genérica para atribuição de constantes e outros valores, conforme os
 * dados dispostos no banco
 *
 * @category Orcamento
 * @package Orcamento_Business_Sessao
 * @todo Todas as chamadas para uso de sessão no e-Orçamento devem ser
 *       utilizadas através desta classe, assim, será necessário refactory de
 *       algumas classes/métodos. Até a conclusão de todas as alterações nesse
 *       sentido esse TODO deve ser mantido aqui.
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
class Orcamento_Business_Sessao
{

    /**
     * Nome do namespace específica do sistema
     *
     * @var string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected $_sessaoNome = 'sessaoOrcamento';

    /**
     * Grava na sessão do e-Orçamento nomeada com a concatenação da $matricula
     * do usuário e a funcionalidade em questão, conforme
     * $requisicao->getControllerName() a url 'chamadora' desta funcionalidade
     *
     * @param Zend_Controller_Action $requisicao
     *        Objeto que tem informações da requisição para serem salvas na
     *        sessão
     * @return string Apenas se $requisicao não for informada
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function defineOrdemFiltro ( $requisicao )
    {
        if ( !$requisicao ) {
            // Se não tiver parâmetro sai do método
            return '';
        }
        
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Retorna matrícula
        $matricula = $this->retornaMatricula ();
        
        // Retorna o nome do controle
        $controle = $requisicao->getControllerName ();
        
        // Define a url para redirecionamento...
        $url = '';
        $url .= '/' . $requisicao->getModuleName ();
        $url .= '/' . $requisicao->getControllerName ();
        $url .= '/' . $requisicao->getActionName ();
        
        // Retorna os parâmetros da requisição para montagem da $url
        $parametros = $requisicao->getParams ();
        
        // Elimana parâmetros fixos
        unset ( $parametros [ 'module' ] );
        unset ( $parametros [ 'controller' ] );
        unset ( $parametros [ 'action' ] );
        
        foreach ( $parametros as $parametro => $valor ) {
            // ...continua a montagem da $url
            $url .= '/' . $parametro . '/' . $valor;
        }
        
        // Define o nome da sessão
        $sessaoNome = $matricula . '_' . $controle;
        
        // Define o valor desta sessão
        $sessao->$sessaoNome = $url;
    }

    /**
     * Lê na sessão do e-Orçamento, conforme usuário logado e $controle
     * informado, e retorna a url gravada para ser redirecionada
     *
     * @param string $controle
     *        Funcionalidade
     * @return string Apenas se $controle não for informado
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaOrdemFiltro ( $controle )
    {
        if ( !$controle ) {
            // Se não tiver controle sai do método
            return '';
        }
        
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Retorna matrícula
        $matricula = $this->retornaMatricula ();
        
        // Define o nome da sessão
        $sessaoNome = $matricula . '_' . $controle;
        
        // Retorna o valor desta sessão
        $sessaoValor = $sessao->$sessaoNome;
        
        // Devolve a url desta sessão
        return $sessaoValor;
    }

    /**
     * Limpa a url pré-gravada de uma determinada sessão do e-Orçamento
     *
     * @param string $controle        
     * @return string Apenas se $controle não for informado
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function limpaOrdemFiltro ( $controle )
    {
        if ( !$controle ) {
            // Se não tiver controle sai do método
            return '';
        }
        
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Retorna matrícula
        $matricula = $this->retornaMatricula ();
        
        // Define o nome da sessão
        $sessaoNome = $matricula . '_' . $controle;
        
        // Limpa o conteúdo da sessão
        $sessao->$sessaoNome = '';
    }

    /**
     * Grava na sessão do e-Orçamento as informações sobre o perfil do usuário
     * logado
     *
     * @param array $perfil
     *        Contém as variáveis sobre o perfil do usuário
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function definePerfil ( $perfil )
    {
        if ( !$perfil ) {
            // Se não tiver perfil sai do método
            return '';
        }
        
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Define dados de perfil do usuário logado
        $sessao->usuario = $this->retornaMatricula ();
        $sessao->perfil = $perfil [ 'perfil' ];
        $sessao->ug = $perfil [ 'ug' ];
        $sessao->responsavel = $perfil [ 'responsavel' ];
    }

    /**
     * Retorna informações sobre o perfil do usuário logado
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaPerfil ()
    {
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Monta array com as informações salvas na sessão
        $perfil [ 'usuario' ] = $sessao->usuario;
        $perfil [ 'perfil' ] = $sessao->perfil;
        $perfil [ 'ug' ] = $sessao->ug;
        $perfil [ 'responsavel' ] = $sessao->responsavel;
        
        // Devolve o array ddo perfil
        return $perfil;
    }

    /**
     * Grava na sessão do e-Orçamento as informações de erro necessárias para
     * sua exibição em tela apropriada
     *
     * @param array $erro
     *        Contém as variáveis necessárias para informação em tela de erro
     *        apropriada
     * @return string Apenas se $erro não for informado
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function defineErro ( $erro )
    {
        if ( !$erro ) {
            // Se não tiver controle sai do método
            return '';
        }
        
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Define a matrícula do usuário logado
        $sessao->usuario = $this->retornaMatricula ();
        $sessao->perfil = $erro [ 'perfil' ];
        $sessao->ug = $erro [ 'ug' ];
        $sessao->responsavel = $erro [ 'responsavel' ];
        
        // Define os dados da requisição
        $sessao->modulo = $erro [ 'modulo' ];
        $sessao->controle = $erro [ 'controle' ];
        $sessao->acao = $erro [ 'acao' ];
        $sessao->parametros = $erro [ 'parametros' ];
        
        // Define as demais informações do erro
        $sessao->msg = $erro [ 'msg' ];
        $sessao->cod = $erro [ 'cod' ];
        $sessao->bErro = $erro [ 'bErro' ];
        $sessao->erroCod = $erro [ 'erroCod' ];
        $sessao->erroMsg = $erro [ 'erroMsg' ];
        $sessao->erroOrigem = $erro [ 'erroOrigem' ];
        $sessao->erroLinha = $erro [ 'erroLinha' ];
        $sessao->erroTrace = $erro [ 'erroTrace' ];
    }

    /**
     * Retorna as informações salvas na sessão sobre o erro em questão
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaErro ()
    {
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Monta array com as informações salvas na sessão
        $erro [ 'usuario' ] = $sessao->usuario;
        $erro [ 'perfil' ] = $sessao->perfil;
        $erro [ 'ug' ] = $sessao->ug;
        $erro [ 'responsavel' ] = $sessao->responsavel;
        
        $erro [ 'modulo' ] = $sessao->modulo;
        $erro [ 'controle' ] = $sessao->controle;
        $erro [ 'acao' ] = $sessao->acao;
        $erro [ 'parametros' ] = $sessao->parametros;
        
        $erro [ 'msg' ] = $sessao->msg;
        $erro [ 'cod' ] = $sessao->cod;
        $erro [ 'bErro' ] = $sessao->bErro;
        $erro [ 'erroCod' ] = $sessao->erroCod;
        $erro [ 'erroMsg' ] = $sessao->erroMsg;
        $erro [ 'erroOrigem' ] = $sessao->erroOrigem;
        $erro [ 'erroLinha' ] = $sessao->erroLinha;
        $erro [ 'erroTrace' ] = $sessao->erroTrace;
        
        // Devolve o array de erro
        return $erro;
    }

    /**
     * Limpa as informações salvas na sessão sobre o erro em questão
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function limpaErro ()
    {
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Limpa o valor das variáveis em sessão
        $sessao->msg = '';
        $sessao->cod = '';
        
        $sessao->modulo = '';
        $sessao->controle = '';
        $sessao->acao = '';
        $sessao->parametros = '';
        
        $sessao->bErro = false;
        $sessao->erroCod = '';
        $sessao->erroMsg = '';
        $sessao->erroOrigem = '';
        $sessao->erroLinha = '';
        $sessao->erroTrace = '';
    }

    /**
     * Retorna a matrícula, sempre em minúscula, do usuário logado salva na
     * sessão do e-Admin
     *
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaMatricula ()
    {
        $sessao = new Zend_Session_Namespace ( 'userNs' );
        
        $matricula = trim ( strtolower ( $sessao->matricula ) );
        
        return $matricula;
    }

    /**
     * Grava na sessão do e-Orçamento as mensagens extras para exibição via
     * flashMessenger
     *
     * @param string $msg
     *        Adiciona esta mensagem extra na sessão
     * @param string $status
     *        Adiciona o tipo desta mensagem
     * @return string Apenas se $erro não for informado
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function defineMensagemExtra ( $msg, $status )
    {
        if ( trim ( $msg ) == '' ) {
            // Devolve falso se não houver $msg
            return false;
        }
        
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Retorna mensagens existentes
        $msgs = $sessao->msgsExtras;
        
        // Verifica a quantidade atual de itens no array
        $qtde = count ( $msgs );
        
        // Grava nova mensagem e seu status
        $msgs [ $qtde ] [ 'mensagem' ] = $msg;
        $msgs [ $qtde ++ ] [ 'status' ] = $status;
        
        // Grava nova mensagem extra
        $sessao->msgsExtras = $msgs;
    }

    /**
     * Retorna mensagens extras para exibição via flashMessenger
     *
     * @param boolean $limpa
     *        Define se irá limpar as mensagens antigas após seu retorno
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaMensagemExtra ( $limpa = false )
    {
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Retorna mensagens existentes
        $msgs = $sessao->msgsExtras;
        
        if ( $limpa == true ) {
            $this->limpaMensagemExtra ();
        }
        
        // Devolve as mensagens, se houver
        return $msgs;
    }

    /**
     * Limpa as mensagens extras
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function limpaMensagemExtra ()
    {
        // Retorna sessão do sistema
        $sessao = new Zend_Session_Namespace ( $this->_sessaoNome );
        
        // Limpa mensagens existentes
        $sessao->msgsExtras = null;
    }

}