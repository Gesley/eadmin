<?php

class Sosti_DashboardController extends Zend_Controller_Action {
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
        /* Initialize action controller here */
    }

    public function indexAction() {
        
    }

    public function dadosdashAction() {
        $dashboard = new App_Sosti_DashbordQuerys();
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();

        $params = array();
        $dados = $this->_getAllParams();

        $periodo = $DashboardQuerys->defineIntervaloDatas($dados['per']);

        $params["ID_CAIXA"] = $dados["caixa"];
        $params["SG_SECAO"] = $dados["secao"];
        $params["DATA_INICIO"] = $periodo["dataInicio"];
        $params["DATA_FIM"] = $periodo["dataTermino"];

        $resultado = $dashboard->getdadosDash($params);

        /*
         * TODO: A documentar após a conclusão do Dashboard pelo Wilton / Anderson
         * 
         * tipo:	string	pizza, barra, coluna, linha, grid, html e texto
         * titulo:	string	Texto de título do bloco individual do dashboard
         * dados:	array	Contém label e valor para os registros a serem mostrados. Limite de 10 registros??
         * legenda:	string	Texto no rodapé do bloco individual do dashboard
         */
        $data = array('tipo' => 'pizza',
            'titulo' => " Nível 1",
            'dados' => array(
                array('label' => 'Abertos', 'valor' => (int) $resultado[0]['ABERTA']),
                array('label' => 'Baixados', 'valor' => (int) $resultado[0]['BAIXADA']),
                array('label' => 'Em Atendimento', 'valor' => (int) $resultado[0]['ATENDIDA']),
                array('label' => 'Em Espera', 'valor' => (int) $resultado[0]['ESPERA'])
            ),
            'legenda' => 'Total de chamados até o momento da construção, mostrando os atendimentos concluídos(baixados), os não atendidos(não baixados e não encaminhados) e os encaminhados',
            'link' => 'url',
            'cor' => 'azul');

        return $this->_helper->json->sendJson($data);
    }

    public function documentostrfAction() {
        $DashboardQuerys = new Trf1_Sisad_Negocio_Dashboard();

        $dados = $this->_getAllParams();
        $Conformidade = $DashboardQuerys->retornaDadosDocumentos($dados);

        $ordem = array();
        /* Ordena do menor para o maior */
        foreach ($Conformidade as $label => $valor) {
            $ordem = array_merge(array(array('label' => $label, 'valor' => intval($valor))), $ordem);
        }

        $data = array('tipo' => 'pizza',
            'titulo' => 'documentos',
            'dados' => $ordem,
            'legenda' => 'Documentos do Tribunal',
            'link' => 'url',
            'cor' => 'azul'
        );
        return $this->_helper->json->sendJson($data);
    }

}
