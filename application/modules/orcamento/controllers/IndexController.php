<?php
class Orcamento_IndexController extends Zend_Controller_Action
{

    /**
     * Timer para mensuração do tempo de carregamento da página
     *
     * @var $_temporizador
     */
    private $_temporizador;

    public function init ()
    {
        // Título apresentado no Browser
        $this->view->title = 'Início e acesso rápido';
        
        // Ajuda & Informações
        $this->view->msgAjuda = AJUDA_AJUDA;
        $this->view->msgInfo = AJUDA_INFOR;
        
        // Timer para mensuração do tempo de carregamento da página
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio ();
        
        // Grava log de cada ação
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest ();
        
        // $log->gravaLog ( $requisicao );
    }

    public function postDispatch ()
    {
        // Apresenta o tempo de carregamento da página
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
    }

    public function indexAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Seja bem vindo ao sistema e-Orçamento!';
        
        // Pendências do e-Orçamento
        $ano = date ( 'Y' );
        $pendencia = new Trf1_Orcamento_Negocio_Pendencia ();
        $this->view->ano = $ano;
        $this->view->haPendencias = $pendencia->haPendencias ( $ano );
    }

    public function erroAction ()
    {
        // Busca sessão sobre o erro de permissão
        $sessao = new Orcamento_Business_Sessao ();
        $erro = $sessao->retornaErro ();
        
        // Repassa os dados do erro para exibição na view
        $this->view->erro = $erro;
        
        // Limpa dados do último erro apresentado
        // $sessao->limpaErro ();
    }

}