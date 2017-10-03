<?php
class Sisad_EtiquetaController extends Zend_Controller_Action
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
		
		$this->view->titleBrowser = 'e-Sisad';
        // Ajuda
    	$this->view->msgAjuda	= AJUDA_AJUDA;
        // Informação
    	$this->view->msgInformacao = INFORMACAO_INFORMACAO;
    }

    public function indexAction()
    {
        
    }
    
    public function criarAction()
    {
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $siglasecao = $AcessoCaixaUnidade->getSgsecaoCaixaUnidade();
        $codlotacao = $AcessoCaixaUnidade->getCdlotacaoCaixaUnidade();
        $siglalotacao = $AcessoCaixaUnidade->getSiglaLotacaoCaixaUnidade();
        
        $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $dsecao = $RhCentralLotacao->getSecSubsecPai($siglasecao, $codlotacao);
        $cdSecao = $dsecao["SESB_SESU_CD_SECSUBSEC"];
        
        $aNamespace = new Zend_Session_Namespace('userNs');
        $this->view->title = "Etiquetas a serem geradas";
        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            
            if($data["dataInicio"] ==  null){
                $this->_helper->flashMessenger (array('message' => "Insira uma data inicio!", 'status' => 'notice'));
                $this->_helper->_redirector('criar','etiqueta','sisad');
            }
            if($data["dataFim"] ==  null){
                $this->_helper->flashMessenger (array('message' => "Insira uma data fim!", 'status' => 'notice'));
                $this->_helper->_redirector('criar','etiqueta','sisad');
            }
            /*paginação*/
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

            /*Ordenação das paginas*/
            $order_column = $this->_getParam('ordem', 'PRDC_ID_PROTOCOLO');
            $order_direction = $this->_getParam('direcao', 'ASC');
            $order = $order_column.' '.$order_direction;
            ($order_direction == 'ASC') ? ($order_direction = 'DESC') : ($order_direction = 'ASC');
            /*Ordenação*/

            $dados = new Application_Model_DbTable_SadTbPrdcProtDocProcesso();
            $etiquetas = $dados->getEtiquetas($data["dataInicio"],$data["dataFim"]);
            
            $cont = 0;
            foreach ($etiquetas as $value) {
            $nrSecao = substr($value["PRDC_ID_PROTOCOLO"], 4,4);
            $nrLotacao = substr($value["PRDC_ID_PROTOCOLO"], 8,5);
                if($nrSecao == $cdSecao && $nrLotacao == $codlotacao){
                    $rows[$cont] = $value;
                    $rows[$cont]["DADOS_INPUT"] = Zend_Json::encode($value);
                    $cont++;
                }
            }
            $this->view->protocolo = $data["protocolo"];
            $this->view->remetente = $rows[0][0]["REMETENTE"];
            
            /*verifica condições e faz tratamento nos dados */
            $fim =  count($rows);
            $TimeInterval = new App_TimeInterval();
            
            $paginator = Zend_Paginator::factory($rows);
            $paginator->setCurrentPageNumber($page)
                      ->setItemCountPerPage(20);

            $this->view->ordem = $order_column;
            $this->view->direcao = $order_direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            $this->view->title = "Etiquetas a serem geradas";
        }
    }

    public function imprimirAction()
    {
        $this->view->title = "Visualização de Etiquetas";
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $cont = 0;
            foreach ($data["documento"] as $value) {
                $dados_input[$cont] = Zend_Json::decode($value);
                $protocolo = $dados_input[$cont]["PRDC_ID_PROTOCOLO"];
                
                $this->_helper->viewRenderer->setNoRender();
                $barcodeOptions = array('text' => $protocolo, 'barHeight' => 30);
                $bc = Zend_Barcode::factory(
                    'code39',
                    'image',
                    $barcodeOptions,
                    array()
                );

                /* @var $bc Zend_Barcode */
                $res = $bc->draw();
                $filename = tempnam('../temp', 'image').'.jpg';
                imagepng($res, $filename);

                /*
                 * Recebe o array com o endereço da imagem do Codigo de Barras
                 */
                $dados = explode('\\', $filename);
                $temporario = explode('.jpg', $filename);
                $qtd = count($dados) - 1;
                $nomeCodigo = $dados[$qtd];

                $dados_input[$cont]["NOMECODBARRAS"] = $nomeCodigo;
                $dados_input[$cont]["ENDERECOCDBARRAS"] = $filename;
                $dados_input[$cont]["TEMPORARIOCDBARRAS"] = $temporario[0];
                
                $cont++;
            }
            $this->view->dados = $dados_input;

            $this->render();
            $response = $this->getResponse();
            $body = $response->getBody();
            $response->clearBody();

            //echo $body;
            $this->_helper->layout->disableLayout();
            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
            $mpdf=new mPDF();

            $mpdf->AddPage('P', '', '0', '1');

            $mpdf->WriteHTML($body);

            /*
             * Remove os arquivos criados na pasta de temporários
             */
            foreach ($dados_input as $value) {
                unlink($value["ENDERECOCDBARRAS"]);
                unlink($value["TEMPORARIOCDBARRAS"]);
            }

            $name =  'Impressão de Etiquetas.pdf';
            $mpdf->Output($name,'D');
        }
    }
}
