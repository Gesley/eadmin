<?php

class Sisad_RelatorioprocessoController extends Zend_Controller_Action {
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
		
        $this->view->titleBrowser = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
    }
    
    public function index() {
    }

    public function ajaxrelatoresAction(){
        $nome = $this->_getParam('term','');
        $service = new Services_Sisad_RelatorioProcesso();
        $relatores = $service->retornaRelatores($nome);
        $fim =  count($relatores);
        for ($i = 0; $i<$fim; $i++ ) {
            $relatores[$i] = array_change_key_case ($relatores[$i],CASE_LOWER );
        }
        $this->_helper->json->sendJson($relatores);
    }
    
    public function cabecalhopdfAction(){
        $nsParams = new Zend_Session_Namespace('NsAtuadosUnidade');
        $this->view->cabecalho = $nsParams->cabecalho;
    }

    public function apensadosanexadosAction(){
        $params = array(
            'start' => $this->_getParam('start', 0),
            'limit' => $this->_getParam('limit', 20),
            'sort'  => $this->_getParam('sort', 'id'),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsApensadosAnexados');
        //unset($nsParams);
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormApensadosAnexados();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos Apensados / Anexados / Vinculados";
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaApensadosAnexados($nsParams->dataPost, $params, true);
            if ( isset($resultado) && $resultado) {
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function apensadosanexadospdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsApensadosAnexados');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS APENSADOS / ANEXADOS / VINCULADOS";
        $this->view->resultado = $nsParams->resultado;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');
        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Apensados_Anexados_Vinculados_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function arquivadosunidadeAction(){
        $params = array(
            'start' => $this->_getParam('start', 0),
            'limit' => $this->_getParam('limit', 20),
            'sort'  => $this->_getParam('sort', 'id'),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsArquivadosUnidade');
        //unset($nsParams);
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormArquivadosUnidade();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos Arquivados na Unidade";
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaArquivadosUnidade($nsParams->dataPost, $params, true);
            if ( isset($resultado) && $resultado) {
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function arquivadosunidadepdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsArquivadosUnidade');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS ARQUIVADOS NA UNIDADE";
        $this->view->resultado = $nsParams->resultado;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');
        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Arquivados_Unidade_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }

    public function autuadosassuntoAction(){
        $params = array(
            'start' => $this->_getParam('start', 0),
            'limit' => $this->_getParam('limit', 20),
            'sort'  => $this->_getParam('sort', 'id'),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsAtuadosAssunto');
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormAutuadosAssunto();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos Autuados por Assunto";
    
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
       /// unset($nsParams->dataPost);
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaAutuadosAssunto($nsParams->dataPost, $params, true);
            if ( isset($resultado) && $resultado) {
                
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $somatorioUnidade = $service->retornaSomatorioUnidade( $resultado );
                $nsParams->somatorioUnidades = $somatorioUnidade["somaUnidades"];
                $this->view->somatorioUnidades = $somatorioUnidade["somaUnidades"];
                unset($resultado["somaUnidades"]);
                
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function autuadosassuntopdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsAtuadosAssunto');

        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS AUTUADOS POR ASSUNTO";
        $this->view->resultado = $nsParams->resultado;
        $this->view->somatorioUnidades =  $nsParams->somatorioUnidades;
        
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();

        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       55,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');

        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
 
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
      
        $mpdf->WriteHTML($body);
     
        $name =  'Relatório_Autuados_Assunto_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function autuadosunidadeAction(){
        
        $params = array(
            'start' => $this->_getParam('start', 0),
            'limit' => $this->_getParam('limit', 20),
            'sort'  => $this->_getParam('sort', 'id'),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        
        $nsParams = new Zend_Session_Namespace('NsAtuadosUnidade');
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormAutuadosUnidade();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos Autuados por Unidade e Período";
        
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
        
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaAutuadosUnidade($nsParams->dataPost, $params, true);
                
            if ( isset($resultado) && $resultado) {
                
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $somatorioUnidade = $service->retornaSomatorioUnidade( $resultado );
                $nsParams->somatorioUnidades = $somatorioUnidade["somaUnidades"];
                $this->view->somatorioUnidades = $somatorioUnidade["somaUnidades"];
                unset($resultado["somaUnidades"]);
                
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
        
    }
    
    public function autuadosunidadepdfAction(){
        
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsAtuadosUnidade');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS AUTUADOS POR UNIDADE E PERÍODO";
        $this->view->resultado = $nsParams->resultado;
        $this->view->somatorioUnidades =  $nsParams->somatorioUnidades;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                        9,    // margin header
                       10,    // margin footer
                       'L');
        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Autuados_Unidade_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }

    public function distribuidosredistribuidosAction(){
        $params = array(
            'start' => $this->_getParam('start', 0),
            'limit' => $this->_getParam('limit', 20),
            'sort'  => $this->_getParam('sort', 'id'),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsDistribuidosRedistribuidos');
        //unset($nsParams);
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormDistribuidosRedistribuidos();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos Distribuídos / Redistribuídos";
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
        
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaProcessosDistribuidosRedistribuidos($nsParams->dataPost);
            if ( isset($resultado) && $resultado) {
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $tamanho = count($resultado);
                $this->view->cabecalho = $cabecalho;
                $this->view->qtdeDistribuidos = $resultado[$tamanho-1]['QTDE_DISTRIBUIDOS']; 
                $this->view->qtdeRedistribuidos = $resultado[$tamanho-1]['QTDE_REDISTRIBUIDOS']; 
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $nsParams->qtdeDistribuidos = $resultado[$tamanho-1]['QTDE_DISTRIBUIDOS'];
                $nsParams->qtdeRedistribuidos = $resultado[$tamanho-1]['QTDE_REDISTRIBUIDOS']; 
                
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function distribuidosredistribuidospdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsDistribuidosRedistribuidos');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS DISTRIBUÍDOS / REDISTRIBUÍDOS";
        $this->view->resultado = $nsParams->resultado;
        $this->view->qtdeDistribuidos = $nsParams->qtdeDistribuidos;
        $this->view->qtdeRedistribuidos = $nsParams->qtdeRedistribuidos;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');
        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Distribuidos_Redistribuidos_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function encaminhadosunidadeAction(){
        $params = array(
            'start' => $this->_getParam('start', 0),
            'limit' => $this->_getParam('limit', 20),
            'sort'  => $this->_getParam('sort', 'id'),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsEnaminhadosUnidade');
        //unset($nsParams);
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormEncaminhadosUnidade();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos Encaminhados pela Unidade";
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
    
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaEncaminhadosUnidade($nsParams->dataPost, $params, true);
    
            if ( isset($resultado) && $resultado) {
                
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function encaminhadosunidadepdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsEnaminhadosUnidade');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS ENCAMINHADOS PELA UNIDADE";
        $this->view->resultado = $nsParams->resultado;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');
        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Encaminhados_Unidade_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function orgaojulgadorAction(){
        $params = array(
            'start' => $this->_getParam('start', 0),
            'limit' => $this->_getParam('limit', 20),
            'sort'  => $this->_getParam('sort', 'id'),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsOrgaoJulgador');
        //unset($nsParams);
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormOrgaoJulgador();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos por Órgão Julgador";
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
        
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaProcessosOrgaoJulgador($nsParams->dataPost);
            if ( isset($resultado) && $resultado) {
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
    }
    }
    
    public function orgaojulgadorpdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsOrgaoJulgador');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS POR ÓRGÃO JULGADOR";
        $this->view->resultado = $nsParams->resultado;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');
        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_porOrgao_Julgador_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function naunidadeAction(){
        
        $params = array(
            'limit' => $this->_getParam('limit', 20),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsnaUnidade');
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormNaUnidade();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos na Unidade";
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
       /// unset($nsParams->dataPost);
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaProcessosNaUnidade($nsParams->dataPost);
            if ( isset($resultado) && $resultado) {
                
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function naunidadepdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsnaUnidade');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS NA UNIDADE";
        $this->view->resultado = $nsParams->resultado;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');
        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Processos_naUnidade_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }
       
    public function paradosnaunidadeAction(){
        
        $params = array(
            'limit' => $this->_getParam('limit', 20),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsParadosnaUnidade');
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormParadoNaUnidade();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos Parados na Unidade";
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
}
       /// unset($nsParams->dataPost);
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaProcessosParadosNaUnidade($nsParams->dataPost);
            if ( isset($resultado) && $resultado) {
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function paradosnaunidadepdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsParadosnaUnidade');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS PARADOS NA UNIDADE";
        $this->view->resultado = $nsParams->resultado;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');

        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Processos_Parados_naUnidade_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }

    public function processosrelatorAction(){
        
        $params = array(
            'limit' => $this->_getParam('limit', 20),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsRelator');
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormRelator();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos por Relator";
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
        }
       /// unset($nsParams->dataPost);
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaProcessosPorRelator($nsParams->dataPost);
                //Zend_Debug::dump($resultado);
               // exit;
            if ( isset($resultado) && $resultado) {
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function processosrelatorpdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsRelator');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS POR RELATOR";
        $this->view->resultado = $nsParams->resultado;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');

        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Processos_Relator_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }
    
    public function sigilososAction(){
        
        $params = array(
            'limit' => $this->_getParam('limit', 20),
            'dir'   => $this->_getParam('dir', 'ASC'),
            'page'  => $this->_getParam('page', 1),
        );
        $nsParams = new Zend_Session_Namespace('NsSigilosos');
        $service = new Services_Sisad_RelatorioProcesso();
        $form = $service->getFormSigilosos();
        $this->view->form = $form;
        $this->view->title = "Relatório de Processos Sigilosos";
        
        if ( $this->_request->isPost() ) {
            $nsParams->dataPost = $this->_request->getPost();
}
        //unset($nsParams->dataPost);
        if (isset ($nsParams->dataPost )) {
                $resultado = $service->retornaProcessosSigilosos($nsParams->dataPost);
            if ( isset($resultado) && $resultado) {
                
                $cabecalho = $service->retornaCabecalho( $nsParams->dataPost );
                $this->view->cabecalho = $cabecalho;
                $nsParams->cabecalho = $cabecalho;
                $nsParams->resultado = $resultado;
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array ($resultado));
                $paginator->setItemCountPerPage($params['limit']);
                $paginator->setCurrentPageNumber($params['page']);
                $this->view->resultado = $paginator;
                Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

            }else{
                $msg = "Não foram encontrados registros para os parâmetros pesquisados.";
                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg </div>";
                $this->view->flashMessagesView .= $msg_to_user;
                return;
            }
        }
    }
    
    public function sigilosospdfAction(){
        set_time_limit( 1200 );
        $nsParams = new Zend_Session_Namespace('NsSigilosos');
        $this->view->cabecalho = $nsParams->cabecalho;
        $this->view->titulo = "RELATÓRIO DE PROCESSOS SIGILOSOS";
        $this->view->resultado = $nsParams->resultado;
        $this->render();
        $response = $this->getResponse();
        $body = $response->getBody();
        $response->clearBody();
        $this->_helper->layout->disableLayout();
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
        $mpdf=new mPDF('',    // mode - default ''
                       '',    // format - A4, for example, default ''
                        8,    // font size - default 0
                       '',    // default font family
                       10,    // margin_left
                       10,    // margin right
                       50,    // margin top
                       15,    // margin bottom
                       9,    // margin header
                       10,    // margin footer
                       'L');

        $this->render('cabecalhopdf');
        $responseCabecalho = $this->getResponse();
        $cabecalho = $responseCabecalho->getBody();
        $responseCabecalho->clearBody();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage("L", '', 1, 1,0);
        $mpdf->setFooter('Gerado em {DATE d/m/Y H:i:s} - Pág. {PAGENO}');
        $mpdf->WriteHTML($body);
        $name =  'Relatório_Processos_Sigilosos_'.date("d/m/Y H:i:s").'.pdf';
        $mpdf->Output($name,'D');
    }

}
