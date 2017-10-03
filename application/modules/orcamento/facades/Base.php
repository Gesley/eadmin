<?php
/**
 * Contém classe de fachada base para extensão nas demais facades
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém as funcionalidades básicas disponíveis através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Base
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Base
{

    /**
     * Classe negocial
     *
     * @var object $_classeNegocio
     */
    protected $_negocio = null;

    /**
     * Controller desta facade
     *
     * @var string
     */
    protected $_controle = null;

    /**
     * Método construtor da classe
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct ()
    {
        // Chama o método init()
        $this->init ();
    }

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {}

    /**
     * Retorna todos os registros a serem apresentando na listagem de dados
     *
     * @param boolean $bUsaCache
     *        Determina se a listagem deverá ser cacheada ou não
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaListagem ( $bUsaCache = true )
    {
        try {
            // Retorna os dados a serem exibidos
            $dados = $this->_negocio->retornaListagem ( $bUsaCache );
            
            // Devolve os dados
            return $dados;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Retorna um registro, apresentando os campos conforme ação informada
     *
     * @param string $acao
     *        Ação para escolha dos campos
     * @param array $chavePrimaria
     *        Chave primária (ou composta) para identificação do registro
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaRegistro ( $acao, $chavePrimaria )
    {
        try {
            // Retorna o registro a ser exibido
            $registro = $this->_negocio->retornaRegistro ( $acao, 
            $chavePrimaria );
            
            // Devolve o registro
            return $registro;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Retorna os campos da chave primária (ou composta)
     *
     * @return unknown
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaChavePrimaria ()
    {
        try {
            // Retorna o campo chave primária (ou composta)
            $chavePrimaria = $this->_negocio->chavePrimaria ();
            
            // Devolve a chave primária (ou composta)
            return $chavePrimaria;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @param string $nomeGrid
     *        Informa qual grid a ser chamado, no caso de haver mais de uma
     *        listagem por controller
     * @return array
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaOpcoesGrid ( $nomeGrid = null )
    {
        try {
            // Retorna opções para criação do grid
            $opcoes = $this->_negocio->retornaOpcoesGrid ( $nomeGrid );
            
            // Devolve as opções
            return $opcoes;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Verifica se há algum impedimento negocial para, em caso verdadeiro,
     * bloquear todos os campos do formulário
     *
     * @tutorial Esse método deve ser sobrescrito na classe pai
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
    public function retornaDeveBloquearCampos ( $acao, $formulario, $dados )
    {
        try {
            // Define alias...
            $a = $acao;
            $f = $formulario;
            $d = $dados;
            
            // Retorna os dados para exibição no grid
            $bTrava = $this->_negocio->retornaDeveBloquearCampos ( $a, $f, $d );
            
            // Devolve o resultado
            return $bTrava;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Busca na classe negocial as transformações no formulario, se aplicável
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function transformaFormulario ( $formulario, $acao )
    {
        try {
            // Retorna o formulário após transformações, se aplicável
            $form = $this->_negocio->transformaFormulario ( $formulario, $acao );
            
            // Devolve o formulário
            return $form;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Chama a regra negocial para inclusão de novo registro
     *
     * @param array $dados
     *        Campos do registro a inserir
     * @return $resultado
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function incluir ( $dados )
    {
        try {
            // Realiza a operação de incluir
            $resultado = $this->_negocio->incluir ( $dados );
            
            // Devolve o resultado da operação
            return $resultado;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Chama a regra negocial para edição de registro
     *
     * @param array $dados
     *        Campos do registro a inserir
     * @return $resultado
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function editar ( $dados )
    {
        try {
            // Realiza a operação de editar
            $resultado = $this->_negocio->editar ( $dados );
            
            // Devolve o resultado da operação
            return $resultado;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Chama a regra negocial para exclusão física e definitiva de registros
     *
     * @param array $chavePrimaria
     *        Chave primária (ou composta) para identificação dos registros
     * @return $resultado
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function exclusaoFisica ( $chaves )
    {
        try {
            // Exclui registro definitivamente do banco
            $resultado = $this->_negocio->excluir ( $chaves );
            
            // Grava na tabela de log o resultado da exclusao
            $this->gravaLog();            
            
            // Devolve o resultado da operação
            return $resultado;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Chama a regra negocial para exclusão lógica de registros
     *
     * @param array $chavePrimaria
     *        Chave primária (ou composta) para identificação dos registros
     * @return $resultado
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function exclusaoLogica ( $chaves )
    {
        try {
            // Atualiza registro pré existente no banco
            $resultado = $this->_negocio->exclusaoLogica ( $chaves );
            
            // Grava na tabela de log o resultado da exclusao
            $this->gravaLog();
            
            // Devolve o resultado da operação
            return $resultado;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    /**
     * Chama a regra negocial para restauração de registro logicamente excluído
     *
     * @param array $chavePrimaria
     *        Chave primária (ou composta) para identificação dos registros
     * @return $resultado
     * @throws Zend_Exception
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function restaurar ( $chaves )
    {
        try {
            // Atualiza registro pré existente no banco
            $resultado = $this->_negocio->restauraExclusaoLogica ( $chaves );
            
            // Grava na tabela de log o resultado da restauracao
            $this->gravaLog($resultado);            
            
            // Devolve o resultado da operação
            return $resultado;
        } catch ( Exception $e ) {
            // Gera o erro
            throw new Zend_Exception ( $e->getMessage () );
        }
    }

    public function gravaLog( $parametros )
    {
        $logdados = New Orcamento_Business_Negocio_Logdados();                                      
        $logdados->incluirLog( $parametros );
    }
    
}