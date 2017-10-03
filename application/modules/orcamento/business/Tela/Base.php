<?php
/**
 * Contém funcionalidade básicas dos controllers da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Business - Tela
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades elementares para os controllers
 *
 * @category Orcamento
 * @package Orcamento_Business_Tela_Base
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
class Orcamento_Business_Tela_Base extends Zend_Controller_Action
{

    /**
     * Timer para mensuração do tempo de carregamento da página
     *
     * @var int
     */
    protected $_tempo;

    /**
     * String a ser exibida no título do navegador
     *
     * @var string
     */
    protected $_tituloBrowser = null;

    /**
     * Atual requisicao
     *
     * @var object
     */
    protected $_requisicao = null;

    /**
     * Nome do modulo para usos diversos
     *
     * @var string
     */
    protected $_modulo = null;

    /**
     * Nome do controle para usos diversos
     *
     * @var string
     */
    protected $_controle = null;

    /**
     * Método init para ser executado na inicialização desta classe.
     *
     * @see Zend_Controller_Action::init()
     * @tutorial Toda classe extendida deve utilizar, obrigatoriamente, no
     *           início do método init() a intrução: parent::init();
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Realiza as tarefas iniciais para esta controller
        $this->definicoesIniciais ();
        
        // Verifica se o título para exibição no navegador foi informado
        $msgErro = 'Obrigatório informar o título da funcionalidade a ser
        exibido no navegador';
        
        if ( !$this->_tituloBrowser ) {
            // Gera o erro
            throw new Zend_Exception ( $msgErro );
        }
        
        // Título apresentado no Browser
        $this->view->title = $this->_tituloBrowser;
    }

    /**
     * Atribui valores às variáveis protegidas e efetua operações iniciais para
     * uso da controller.
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function definicoesIniciais ()
    {
        // Busca essa requisicao
        $this->_requisicao = $this->getRequest ();
        $requisicao = $this->_requisicao;
        
        // Busca nome do module
        $this->_modulo = strtolower ( $requisicao->getModuleName () );
        
        // Busca nome do controller
        $this->_controle = strtolower ( $requisicao->getControllerName () );
        
        // Apresenta, se houver, as mensagens de ajuda e informação
        $this->view->msgAjuda = AJUDA_AJUDA;
        $this->view->msgInfo = AJUDA_INFOR;
        
        // Timer para mensuração do tempo de carregamento da página
        $this->_tempo = new Trf1_Admin_Timer ();
        $this->_tempo->Inicio ();
    }

    /**
     * Método postDispatch para ser executado no encerramento desta classe
     *
     * @see Zend_Controller_Action::postDispatch()
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function postDispatch ()
    {
        // Apresenta o tempo de carregamento da página
        $this->view->tempoResposta = $this->_tempo->MostraMensagemTempo ();
    }

    /**
     * Define o título desta funcionalidade no navegador
     *
     * @param string $_tituloBrowser
     *        Título a ser apresentado na barra de título do navegador
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function defineTituloBrowser ( $tituloBrowser )
    {
        // Define o título
        $this->_tituloBrowser = $tituloBrowser;
    }

    /**
     * Retorna o atual título a ser apresentado na barra de título do navegador
     *
     * @return $_tituloBrowser
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaTituloBrowser ()
    {
        // Retorna o valor atual
        return $this->_tituloBrowser;
    }

}