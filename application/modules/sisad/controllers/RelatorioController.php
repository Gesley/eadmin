<?php

class Sisad_RelatorioController extends Zend_Controller_Action {
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
	
    public function index() {
        
    }

    public function relatorioAction() {

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $dados = new Application_Model_DbTable_SadTbPostPostagemProcDoc();

            $tipo = $data["tipo"];
            $titulo = $data["titulo"];
            unset($data["tipo"]);
            
            
            $json = json_encode($data);


            $json = json_encode($data);

            if ($tipo == "valores") {
                $rows = $dados->relatorioValores($data);
            }
            
            if($tipo == "malote"){
                $rows = $dados->relatorioMaloteDia($data);
            }
            
            if($tipo == "sedex"){
                $rows = $dados->relatorioMaloteDia($data);
            }
            
            if($tipo == "correios"){
                $rows = $dados->relatorioCorreios($data);
            }
            
			if($tipo == "relatorios"){
                $rows = $dados->relatorioCorreios($data);
            }
            
             
             $this->view->dados = $rows;
             $this->view->titulo = $titulo;
             $this->view->data = $json;
             $this->render($tipo);
             $this->imprimir2();
            
            
        } else {
            $tipo = $this->_getParam('tipo', '');
            $this->view->tipo = $tipo;
        }
    }

    public function imprimir2() {
        $this->_helper->layout->disableLayout();

        $zend_date = new Zend_date();


        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

        $mpdf = new mPDF('utf-8', // mode - default ''
                        'A4', // format - A4, for example, default ''
                        '', // font size - default 0
                        '', // default font family
                        5, // margin_left
                        20, // margin right
                        25, // margin top
                        16, // margin bottom	 
                        0, // margin header
                        5, // margin footer
                        'L');  // L - landscape, P - portrait
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetFooter('|{PAGENO}/{nb}|TRIBUNAL REGIONAL FEDERAL DA 1ª REGIÃO');


        $data = new Zend_date();
        $hora = $data->get(Zend_Date::HOUR);
        $minuto = $data->get(Zend_Date::MINUTE);
        $segundo = $data->get(Zend_Date::SECOND);
        $dia = $data->get(Zend_Date::DAY);
        $mes = $data->get(Zend_Date::MONTH);
        $ano = $data->get(Zend_Date::YEAR);

        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();

        $cabecalho = "
            <table id='header'>
                <tr valign='top'>
                    <td class='brasao'>
                    </td>
                    <td class='date-time'>
                        $dia/$mes/$ano $hora:$minuto:$segundo
                    </td>
                </tr>
            </table>

            ";
        $mpdf->showWatermarkImage = true;
        $mpdf->watermarkImgBehind = true;
        $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage();
        $mpdf->WriteHTML($body);
        $mpdf->Output($name, 'D');
    }
	
	public function init() {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
	}
}