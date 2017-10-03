<?php

class Sisad_DashboardController extends Zend_Controller_Action
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
	
    public function init()
    {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
    }

    public function indexAction()
    {
       
    }
    
    
    public function todosdocumentosadministrativoshojeAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornatodosdocumentoAdministrativos('hoje');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => ''
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
        public function todosdocumentosadministrativos7diasAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornatodosdocumentoAdministrativos('7dias');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => 'verde'
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
        public function todosdocumentosadministrativosmesAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornatodosdocumentoAdministrativos('mes');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => 'azul'
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
    public function processossecaohojeAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornaprocessosSecao('hoje');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
        
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => ''
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
    public function processossecao7diasAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornaprocessosSecao('7dias');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => 'verde'
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
    public function processossecaomesAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornaprocessosSecao('mes');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => 'azul'
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
    public function processosunidadehojeAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornaprocessosUnidade('hoje');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
        
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => ''
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
     public function processosunidade7diasAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornaprocessosUnidade('7dias');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => 'verde'
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
    public function processosunidademesAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornaprocessosUnidade('mes');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => 'azul'
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
    public function documentosunidadehojeAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornadocumentosUnidade('hoje');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => ''
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
    public function documentosunidade7diasAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornadocumentosUnidade('7dias');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => 'verde'
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
    public function documentosunidademesAction()
    {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();
        $Conformidade = $DashboardQuerys->retornadocumentosUnidade('mes');

        $ordem = array();
        /*Ordena do menor para o maior*/
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }
 
        $dados = array( 'tipo'      => 'pizza',
                        'titulo'    => 'documentos',
                        'dados'     => $ordem,
                        'legenda'   => '',
                        'link'      => 'url',
                        'cor'       => 'azul'
        );
        /*
         * TODO: Gráfico lixo, você ja programou melhor Wilton.
         * 
         * 
         * 
         * 
         * 
         */
        return $this->_helper->json->sendJson($dados);
    }
    
}
