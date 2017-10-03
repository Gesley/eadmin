<?php

class Sosti_FaturamentoController extends Zend_Controller_Action {

    public function init() {
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        /* Initialize action controller here */
    }

    /**
     * Recebe os dados de faturamento dos rias. 
     */
     public function getriasfaturadosAction() {
        $this->view->title = 'Importação de Documentos Finalizados';

        $anexos = new Zend_File_Transfer_Adapter_Http();
        $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
        include(realpath(APPLICATION_PATH . '/../library/PHPExcel/Classes/PHPExcel.php'));
        /**
         * Recebe documentos(RIAS e CONTAGEM), insere no RED:
         */
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
            
            $tabela = explode('_', $post["TIPO_IMPORT"]);
            
            if ($anexos->getFileName()) 
            {
                try 
                {
                    $data["DOCM_NR_DOCUMENTO"] = "FATURAMENTO DE SOLICITAÇÕES";
                    $data['DOCM_ID_CONFIDENCIALIDADE'] = 0;

                    $upload = new App_Multiupload_Faturamento($data);
                    $nrDocsRed = $upload->incluirarquivos($anexos, true);
                } 
                    catch (Exception $exc) 
                    {
                        $this->_helper->flashMessenger(array('message' => "GT_RIA 01 - Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                    }
                
                $qtd = 0;
                $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
                $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();

                foreach ($nrDocsRed["incluidos"] as $docs) 
                {
                    try 
                    {
                        /**********************
                         * $nrDocumento = array nome arquivo
                         * $idDocumento = idSosti
                         * $numeroSosti = Numero do Sosti (numeric)
                         **********************/
                        
                        $nrDocumento = explode("_", $docs['NOME']);
                        
                        $a = $docs['NOME'];
                        $e = explode(".", $docs['NOME']);
                        preg_match_all('!\d+!', $a, $m);
                        rsort($m[0],SORT_NUMERIC);
                        
                        $nSosti = $m[0][0];
                        $nExt   = $e[1];
                        
                        $idDocumento = $SadTbDocmDocumento->getDocumentoIdByNrDoc($nSosti);
                        $numeroSosti = $nSosti;
                        
                        $tipoTab = $tabela[0];
                        $tipoDoc = $tabela[3];
                        $tipoRia = $tabela[4];
                        
                        
                        if (!empty($idDocumento)) 
                        {
                            $idDoc      = $idDocumento[0]['DOCM_ID_DOCUMENTO'];
                            $dcmto      = $docs['ID_DOCUMENTO'];
                            $pfBruto    = "";
                            $pfLiquido  = "";
                            
                            $getDadosSolic["DOCM_NR_DOCUMENTO"] = $numeroSosti;
                            $dadosSolic = $negocioFaturamento->getAvalRias($getDadosSolic);
                            
                            $tipoSat = $dadosSolic[0]['STSA_ID_TIPO_SAT'];
                            
                            
                            if ($post["TIPO_IMPORT"] === 'PFDS_NR_DCMTO_CONTAGEM-1' || $post["TIPO_IMPORT"] === 'PFAF_NR_DCMTO_CONTAGEM-1') 
                            {
                                $arquivo = PHPExcel_IOFactory::load($docs['FULLFILEPATH']);
                                $arquivo->setActiveSheetIndexByName('Dados da contagem');
                                $leitura = $arquivo->getActiveSheet();

                                $nroSostiInterno = $leitura->getCell('B2')->getValue();
                                $validaNroSosti = strpos($nroSostiInterno, $numeroSosti);

                                $pSosti = '/^'.$numeroSosti.'/';
                                $scomp = preg_match($pSosti, $nroSostiInterno, $pmt);
                                
                                if (!$scomp)
                                {
                                    throw new Exception('GT_RIA 01 - O nome do arquivo não corresponde com o número do SOSTI.');
                                }

                                $pfBruto   = str_replace('.', ',', $leitura->getCell('B6')->getOldCalculatedValue());
                                $pfLiquido = str_replace('.', ',', $leitura->getCell('B7')->getOldCalculatedValue());
                                
                                /*
                                 * 
                                 * EDITADO PARA O BANCO DE PRODUÇÃO -- NÃO ACEITA ESSA FORMATAÇÃO
                                $pfBruto   = str_replace('.', ',', $leitura->getCell('B6')->getOldCalculatedValue());
                                $pfLiquido = str_replace('.', ',', $leitura->getCell('B7')->getOldCalculatedValue());
                                */
                            }
                            
                            if ($post["TIPO_IMPORT"] === 'PFDS_NR_DCMTO_CONTAGEM-2' || $post["TIPO_IMPORT"] === 'PFAF_NR_DCMTO_CONTAGEM-2') 
                            {
                                $arquivo = PHPExcel_IOFactory::load($docs['FULLFILEPATH']);
                                $arquivo->setActiveSheetIndexByName('Plano de Contagem');
                                $leitura = $arquivo->getActiveSheet();

                                $nroSostiInterno    = $leitura->getCell('I1')->getValue();
                                $validaNroSosti     = strpos($nroSostiInterno, $numeroSosti);

                                $pSosti = '/^'.$numeroSosti.'/';
                                $scomp = preg_match($pSosti, $nroSostiInterno, $pmt);
                                
                                if (!$scomp)
                                {
                                    throw new Exception('GT_RIA 02 - O nome do arquivo não corresponde com o número do SOSTI.');
                                }
                                
                                $pfBruto   = str_replace('.', ',', $leitura->getCell('X11')->getOldCalculatedValue());
                                $pfLiquido = str_replace('.', ',', $leitura->getCell('L14')->getOldCalculatedValue());
                                
                                /*
                                 * 
                                 * EDITADO PARA O BANCO DE PRODUÇÃO -- NÃO ACEITA ESSA FORMATAÇÃO
                                $pfBruto   = str_replace('.', ',', $leitura->getCell('X11')->getOldCalculatedValue());
                                $pfLiquido = str_replace('.', ',', $leitura->getCell('L14')->getOldCalculatedValue());
                                */
                            }
      
                            if ($post["TIPO_IMPORT"] === 'PFDS_NR_DCMTO_CONTAGEM-3' || $post["TIPO_IMPORT"] === 'PFAF_NR_DCMTO_CONTAGEM-3') 
                            {
                                
                                $arquivo = PHPExcel_IOFactory::load($docs['FULLFILEPATH']);
                                $arquivo->setActiveSheetIndexByName('Plano de Contagem');
                                $leitura = $arquivo->getActiveSheet();

                                $nroSostiInterno    = $leitura->getCell('I1')->getValue();
                                $validaNroSosti     = strpos($nroSostiInterno, $numeroSosti);

                                
                                
                                $pSosti = '/^'.$numeroSosti.'/';
                                $scomp = preg_match($pSosti, $nroSostiInterno, $pmt);
                                
                                if (!$scomp)
                                {
                                    throw new Exception('GT_RIA 03 - O nome do arquivo não corresponde com o número do SOSTI.');
                                }
                                $pfBruto   = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue());
                                $pfLiquido = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue());
                                
                                /*
                                 * 
                                 * EDITADO PARA O BANCO DE PRODUÇÃO -- NÃO ACEITA ESSA FORMATAÇÃO
                                $pfBruto   = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue());
                                $pfLiquido = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue());
                                */
                                
                                
                            }
                            
                            if ($tipoTab == "PFDS")
                            {
                                if ($tipoDoc == 'RIA')
                                {
                                   if (($nExt != "docx") && ($nExt!='doc'))
                                   {
                                       throw new Exception('Extensão inválida. Selecione um arquivo .doc ou .docx');
                                   }
                                    
                                    $dadosCadastro = array(
                                                            'PFDS_ID_SOLICITACAO'   => $idDoc,
                                                            'STSA_ID_TIPO_SAT'      => $tipoSat,
                                                            'PFDS_DH_STATUS'        => new Zend_Db_Expr('SYSDATE'),
                                                            $post["TIPO_IMPORT"]    => $dcmto
                                                            );
                                    
                                    
                                     try 
                                    {
                                        $negocioFaturamento->salvarDadosDesenvolvedora($dadosCadastro);                                    
                                    } 
                                        catch (Exception $exc) 
                                        {
                                            $this->_helper->flashMessenger(array('message' => "Erro ao salvar arquivo.", 'status' => 'notice'));
                                        }
                               }
                                    else
                                    {
                                       
                                        $dadosCadastro = array(
                                                                'PFDS_ID_SOLICITACAO'   => $idDoc,
                                                                'PFDS_DH_STATUS'        => new Zend_Db_Expr('SYSDATE'),
                                                                $post["TIPO_IMPORT"]    => $dcmto,
                                                                'PFDS_QT_PF_BRUTO'      => $pfBruto,
                                                                'PFDS_QT_PF_LIQUIDO'    => $pfLiquido,
                                                                'STSA_ID_TIPO_SAT'      => $tipoSat,
                                                                'PFDS_NR_DCMTO_CONTAGEM' =>$dcmto
                                                               );
                                    try 
                                    {
                                        $negocioFaturamento->salvarDadosDesenvolvedora($dadosCadastro);                                    
                                    } 
                                        catch (Exception $exc) 
                                        {
                                            $this->_helper->flashMessenger(array('message' => "Erro ao salvar arquivo.", 'status' => 'notice'));
                                        }
                                    }
                            }
                            
                            
                                else
                                {
                                     if ($tipoDoc == 'RIA')
                                    {
                                        $dadosCadastro = array(
                                                                'PFAF_ID_SOLICITACAO'   => $idDoc,
                                                                'PFAF_ID_STATUS'        => $pfdsStatus, //AFERIDO
                                                                'PFAF_DH_STATUS'        => new Zend_Db_Expr('SYSDATE'),
                                                                $post["TIPO_IMPORT"]    => $dcmto
                                                                );
                                        
                                        try 
                                        {
                                            $negocioFaturamento->salvarDadosAfericao($dadosCadastro);
                                        } 
                                            catch (Exception $exc) 
                                            {
                                                $this->_helper->flashMessenger(array('message' => "Erro ao salvar arquivo.", 'status' => 'notice'));
                                            }
                                        
                                        
                                    }
                                        else
                                        {
                                            $dadosCadastro = array(
                                                                'PFAF_ID_SOLICITACAO'   => $idDoc,
                                                                'PFDS_DH_STATUS'        => new Zend_Db_Expr('SYSDATE'),
                                                                $post["TIPO_IMPORT"]    => $dcmto,
                                                                'PFAF_QT_PF_BRUTO'      => $pfBruto,
                                                                'PFAF_QT_PF_LIQUIDO'    => $pfLiquido,
                                                                'PFAF_NR_DCMTO_CONTAGEM'=>$dcmto
                                                               );
                                                               
                                            try 
                                            {
                                                $negocioFaturamento->salvarDadosAfericao($dadosCadastro);
                                            } 
                                                catch (Exception $exc) 
                                                {
                                                    $this->_helper->flashMessenger(array('message' => "Erro ao salvar arquivo.", 'status' => 'notice'));
                                                }
                                        }
                                }
                           
                            $qtd++;
                            $this->_helper->flashMessenger(array('message' => "Documento $docs[NOME] Inserido com sucesso", 'status' => 'success'));
                        } 
                            else 
                            {
                                $this->_helper->flashMessenger(array('message' => "GT_RIA 03 - Não foi possível localizar a solicitação nr $docs[NOME]. Favor verificar o nome do arquivo.", 'status' => 'error'));
                            }
                            unlink(realpath($docs['FULLFILEPATH']));
                    } catch (Exception $exc) {
                        unlink(realpath($docs['FULLFILEPATH']));
                        $nrSolic = $docs['NOME'];
                        $this->_helper->flashMessenger(array('message' => "GT_RIA 04 - Problema ao tentar importar os dados do arquivo $nrSolic <br/>" . $exc->getMessage(), 'status' => 'notice'));
                    }
                }
                foreach ($nrDocsRed["existentes"] as $docs) {
                    try {
                        $nrDocumento = explode("_", $docs['NOME']);
                        foreach ($nrDocumento as $nrDoc) {
                            $extensões = array('.xlsx', '.xls');
                            $nrDoc = rtrim(str_replace($extensões, '', $nrDoc));
                            if (is_numeric($nrDoc)) {
                                $idDocumento = $SadTbDocmDocumento->getDocumentoIdByNrDoc($nrDoc);
                                $numeroSosti = $nrDoc;
                            }
                        }
                        if (!empty($idDocumento)) {
                            $idDoc = $idDocumento[0]['DOCM_ID_DOCUMENTO'];
                            $dcmto = $docs['ID_DOCUMENTO'];
                            $pfBruto = "";
                            $pfLiquido = "";
                            if ($post["TIPO_IMPORT"] === 'PFDS_NR_DCMTO_CONTAGEM' || $post["TIPO_IMPORT"] === 'PFAF_NR_DCMTO_CONTAGEM') {

                                $arquivo = PHPExcel_IOFactory::load($docs['FULLFILEPATH']);
                                $arquivo->setActiveSheetIndexByName('Plano de Contagem');
                                $leitura = $arquivo->getActiveSheet();

                                $nroSostiInterno    = $leitura->getCell('I1')->getValue();
                                $validaNroSosti     = strpos($nroSostiInterno, $numeroSosti);

                                if ($validaNroSosti === false) {
                                    throw new Exception('GT_RIA 05 - O nome do arquivo não corresponde com o número do SOSTI.');
                                }
                                $pfBruto    = $leitura->getCell('AA15')->getOldCalculatedValue();
                                $pfLiquido  = $leitura->getCell('AA16')->getOldCalculatedValue();
                            }
                            if ($tabela[0] == "PFDS") {
                                $dadosCadastro = array(
                                    'PFDS_ID_SOLICITACAO'   => $idDoc,
                                    'PFDS_ID_STATUS'        => 7, //PUBLICADO
                                    'PFDS_DH_STATUS'        => new Zend_Db_Expr('SYSDATE'),
                                    $post["TIPO_IMPORT"]    => $dcmto,
                                    'PFDS_QT_PF_BRUTO'      => $pfBruto,
                                    'PFDS_QT_PF_LIQUIDO'    => $pfLiquido
                                );
//                                Zend_debug::dump($dadosCadastro, 'PFDS');
                                $negocioFaturamento->salvarDadosDesenvolvedora($dadosCadastro);
                            }
                            if ($tabela[0] == "PFAF") {
                                $dadosCadastro = array(
                                    'PFAF_ID_SOLICITACAO'   => $idDoc,
                                    'PFAF_ID_STATUS'        => 15, //AFERIDO
                                    'PFAF_DH_STATUS'        => new Zend_Db_Expr('SYSDATE'),
                                    $post["TIPO_IMPORT"]    => $dcmto,
                                    'PFAF_QT_PF_BRUTO'      => $pfBruto,
                                    'PFAF_QT_PF_LIQUIDO'    => $pfLiquido
                                );
//                                   Zend_debug::dump($dadosCadastro, 'PFAF');
                                $negocioFaturamento->salvarDadosAfericao($dadosCadastro);
                            }
                            $qtd++;
                            $this->_helper->flashMessenger(array('message' => "Documento $docs[NOME] Inserido com sucesso", 'status' => 'success'));
                        } else {
                            $this->_helper->flashMessenger(array('message' => "GT_RIA 06 - Não foi possível localizar a solicitação nr $docs[NOME]. Favor verificar o nome do arquivo.", 'status' => 'error'));
                        }
                        unlink(realpath($docs['FULLFILEPATH']));
                    } catch (Exception $exc) {
                        unlink(realpath($docs['FULLFILEPATH']));
                        $nrSolic = $docs['NOME'];
                        $this->_helper->flashMessenger(array('message' => "GT_RIA 07 - Problema ao tentar importar os dados do arquivo $nrSolic <br/>" . $exc->getMessage(), 'status' => 'notice'));
                    }
                }

                if ($qtd >= 1) {
                    $this->_helper->flashMessenger(array('message' => "Foram atualizados os dados de $qtd solicitações.", 'status' => 'success'));
                }

                $this->_helper->_redirector('getriasfaturados', 'faturamento', 'sosti');
            }
        }
    }

    /**
     * Possibilita a visualização dos rias já faturados de um determinado perdíodo, 
     * sendo possível filtrar por data, status e/ou classificação, sendo possível 
     * exportar o pdf dos registros retornados. 
     * 
     */
    public function relatoriosAction() {
        $this->view->title = 'Relatório de Faturamento de PF';

        $relatorioNs = new Zend_Session_Namespace('relatorioNs');
        $userNs = new Zend_Session_Namespace('userNs');
        $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
        $arrayPerfis = $ocsTbPupePerfilUnidPessoa->getTodosPerfilPessoa($userNs->matricula);
        
         /**
         * Para zerar o filtro
         */
        if ($this->_getParam('nova') === '1') {
            unset($relatorioNs->dados);
            $Request = $this->getRequest();
            $module = $Request->getModuleName();
            $controller = $Request->getControllerName();
            $action = $Request->getActionName();
            $this->_redirect($module . '/' . $controller . '/' . $action);
        }
        
        
        
        
        $formDsv = new Sosti_Form_FaturamentoDsv();
        $formTrf = new Sosti_Form_FaturamentoTrf();
        $formAfe = new Sosti_Form_FaturamentoAfe();
        $formGeral = new Sosti_Form_FaturamentoGeral();
        
        foreach ($arrayPerfis as $perfil) 
        {
            $p = $perfil["PERF_ID_PERFIL"];
            
            if ($p == 25 || $p == 31 || $p == 53 || $p == 63)
            {
                $idPerfil = $p;
            }
        }
        
        switch ($idPerfil)
        {
            case 25: // DESENVOLVIMENTO E SUSTENTAÇÃO
                $matricula = $formDsv->getElement('MOFA_CD_MATRICULA');
                $matricula->setAttrib('disabled', 'disabled');
                $matricula->setValue($userNs->matricula.' - '.$userNs->nome);
                break;

            case 31: // GESTÃO DE DEMANDAS DE TI
                $status = $formDsv->getElement('PFDS_ID_STATUS');
                $status->setAttrib('disabled', 'disabled');
                $status->setValue(7);
                $statusSolic = $formGeral->getElement('STATUS_SOLICITACAO');
                $statusSolic->setAttrib('disabled', 'disabled');
                $statusSolic->setValue(1014);
                break;
            case 53: // GESTOR DO CONTRATO DO DESEN. E SUSTENTAÇÃO
                break;
            case 63: // RESPONSÁVEL AFERIÇÃO
                $status = $formDsv->getElement('PFDS_ID_STATUS');
                $status->setAttrib('disabled', 'disabled');
                $status->setValue(7);
                $formDsv->removeElement('PFDS_ID_CLASSIFICACAO');
                $formDsv->removeElement('MOFA_CD_MATRICULA');
                $formGeral->removeElement('STATUS_SOLICITACAO');
                $formGeral->removeElement('DATA_INICIAL');
                $formGeral->removeElement('DATA_FINAL');
                $formGeral->removeElement('DATA_ENTRADA_CAIXA_INICIAL');
                $formGeral->removeElement('DATA_ENTRADA_CAIXA_FINAL');

                break;
            default:
                break;
        }
        
        $formGeral->addElements($formDsv->getElements(array()));
        $formGeral->addElements($formAfe->getElements(array()));
        $formGeral->addElements($formTrf->getElements(array()));
        $formGeral->addDisplayGroup(array('PFDS_ID_STATUS','PFDS_ID_CLASSIFICACAO','MOFA_CD_MATRICULA',
            'PFAF_ID_STATUS','PFAF_ID_CLASSIFICACAO','PFAF_NR_LOTE','PFAF_DH_PREVISAO_RETORNO_LOTE','PFAF_DH_RETORNO_LOTE',
            'PFTR_ID_STATUS','PFTR_ID_CLASSIFICACAO','PFTR_NR_ID_RELAT_FATURAMENTO','PFTR_DH_FATURAMENTO','STATUS_SOLICITACAO',
            'DATA_INICIAL','DATA_FINAL','DATA_ENTRADA_CAIXA_INICIAL','DATA_ENTRADA_CAIXA_FINAL','acao'), 'Informações da Solicitação');
        $formGeral->getDisplayGroup('Informações da Solicitação');
        
       
        
        if ($this->getRequest()->isPost()) {
            ini_set("memory_limit","1024M");
                set_time_limit(1200);
            $post = $this->getRequest()->getPost();
            $relatorioNs->dados = $post;
            if ($post["acao"] === 'Excel') {
                $relatorioNs->dados = null;
                
                

                $this->_helper->layout->disableLayout();
                $this->render('titulo');
                if ($post["solicitacao"]) {
                    foreach ($post["solicitacao"] as $id => $data) {
                        $dados[$id] = Zend_Json::decode($data);
                    }
                    $faturamento = new Trf1_Sosti_Negocio_Faturamento();
                    
                    $this->view->total = $faturamento->calculoStatus($dados);
                    
                    $this->view->dados = $dados;

                    $this->render('relatoriosxls');

                    if ($this->_getParam('param') == 'xls') {
                        $name = 'Relatorio de Faturamento de PF.xls';
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $name . '"');
                        header('Cache-Control: max-age=0');
                    }
                } else {
                    $this->_helper->flashMessenger(array('message' => "Selecione as solicitações que serão exibidas no relatório", 'status' => 'notice'));
                }
            }
        }
        if ($relatorioNs->dados) {
            try {
                
                 foreach ($arrayPerfis as $perfil) 
        {
            $p = $perfil["PERF_ID_PERFIL"];
            
            if ($p == 25 || $p == 31 || $p == 53 || $p == 63)
            {
                $idPerfil = $p;
            }
        }
        
        switch ($idPerfil)
        {
            case 25: // DESENVOLVIMENTO E SUSTENTAÇÃO
                $relatorioNs->dados['MOFA_CD_MATRICULA'] = $userNs->matricula.' - '.$userNs->nome;
                break;

            case 31: // GESTÃO DE DEMANDAS DE TI
                $relatorioNs->dados['PFDS_ID_STATUS'] = 7;
                $relatorioNs->dados['STATUS_SOLICITACAO'] = $userNs->matricula.' - '.$userNs->nome;     
                break;
            
            case 53: // GESTOR DO CONTRATO DO DESEN. E SUSTENTAÇÃO
                break;
            
            case 63: // RESPONSÁVEL AFERIÇÃO
                $relatorioNs->dados['PFDS_ID_STATUS'] = 7;
                break;
            
            default:
                break;
        }
                
                $faturamento = new Trf1_Sosti_Negocio_Faturamento();
                $dadosFatura = $faturamento->getRelatorioRias($relatorioNs->dados);
                if (!empty($dadosFatura)) {
                    $this->view->ultima_pesq = true;
                    $this->view->total = $faturamento->calculoStatus($dadosFatura);
                    $this->view->dados = $dadosFatura;
                } else {
                    $this->view->ultima_pesq = false;
                    $this->_helper->flashMessenger(array('message' => "Pesquisa retorna mais de 1000 registros, favor mudar filtro de pesquisa", 'status' => 'notice'));
                }
                $formDsv->populate($relatorioNs->dados);
                $formAfe->populate($relatorioNs->dados);
                $formTrf->populate($relatorioNs->dados);
                $formGeral->populate($relatorioNs->dados);
            } catch (Exception $exc) {
                $this->_helper->flashMessenger(array('message' => "A quantidade de registro é maior que a permitida, preencha mais campos no filtro.", 'status' => 'notice'));
            }
        }
        /**
         * Monta Form
         */
        
        $this->view->formGeral = $formGeral;
    }

    /**
     * Recebe os RIAS(documento word) ou a guia de contagem de PF(Excel) e 
     * verifica se já possui os dados gravados em suas devidas solicitações. 
     * 
     * Caso positivo, a função retorna a informação que já existe documento cadastradado para aquela solicitação. 
     * Caso negativo, grava os dados referentes a aquela solicitação.
     */
    public function setdadoscontratadaAction() {
        if ($this->getRequest()->isPost()) {
            $dadosDsv = $this->getRequest()->getPost();
            
            /**
             * Recebe a controller e a action de onde veio a requisição para retornar a mesma tela após a inclusão dos dados. 
             */
            
            $controller = $dadosDsv["CONTROLLER"];
            $action = $dadosDsv["ACTION"];
            unset($dadosDsv["CONTROLLER"]);
            unset($dadosDsv["ACTION"]);

            $anexos = new Zend_File_Transfer_Adapter_Http();
            $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
            
            include(realpath(APPLICATION_PATH . '/../library/PHPExcel/Classes/PHPExcel.php'));
            
            if ($anexos->getFileName()) 
            {
                try 
                {
                    $data["DOCM_NR_DOCUMENTO"] = "FATURAMENTO DE SOLICITAÇÕES";
                    $data['DOCM_ID_CONFIDENCIALIDADE'] = 0;

                    $upload = new App_Multiupload_Faturamento($data);
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } 
                    catch (Exception $exc) 
                    {
                        $this->_helper->flashMessenger(array('message' => "CAD_ANEX 01 - Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                        $this->_helper->_redirector($action, $controller, 'sosti');
                    }
                
                $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
                $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
                
                foreach ($nrDocsRed["incluidos"] as $docs) 
                {
                    $dadosDsv[$docs["COLUNA"]] = $docs["ID_DOCUMENTO"];
                    
                    $a = $docs['NOME'];
                    $e = explode(".", $docs['NOME']);
                    preg_match_all('!\d+!', $a, $m);
                    rsort($m[0],SORT_NUMERIC);

                    $nSosti = $m[0][0];
                    $nExt   = $e[1];

                    
                    $e = explode(".",$docs['NOME']);
                   
                    
                    if (($nExt == "xls") || ($nExt == "xlsx"))
                    {
                        try 
                        {
                            if (is_numeric($nSosti)) 
                            {
                                $idDocumento = $SadTbDocmDocumento->getDocumentoIdByNrDoc($nSosti);
                                $numeroSosti = $nSosti;
                            }
                           
                            if (!empty($idDocumento)) 
                            {
                                $idDoc = $idDocumento[0]['DOCM_ID_DOCUMENTO'];
                                $dcmto = $docs['ID_DOCUMENTO'];
                                
                                $arquivo = PHPExcel_IOFactory::load($docs['FULLFILEPATH']);
                                $arquivo->setActiveSheetIndexByName('Plano de Contagem');
                                $leitura = $arquivo->getActiveSheet();
                                
                                $nroSostiInterno = $leitura->getCell('I1')->getValue();
                                
                                $validaNroSosti = strpos($nroSostiInterno, $numeroSosti);
                                
                                if (strcmp($nroSostiInterno,$numeroSosti) != 0)
                                {
                                    throw new Exception('GT_PF 01 - O arquivo não corresponde com o número do SOSTI.');
                                }
                                
                               /*
                                 * 
                                 * EDITADO PARA O BANCO DE PRODUÇÃO -- NÃO ACEITA ESSA FORMATAÇÃO
                                $dadosDsv["PFDS_QT_PF_BRUTO"]   = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue());
                                $dadosDsv["PFDS_QT_PF_LIQUIDO"] = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue());
                                */
                                
//                                $dadosDsv["PFDS_QT_PF_BRUTO"]   = $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue();
//                                $dadosDsv["PFDS_QT_PF_LIQUIDO"] = $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue();
                                
                                $dadosDsv["PFDS_QT_PF_BRUTO"]   = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue());
                                $dadosDsv["PFDS_QT_PF_LIQUIDO"] = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue());
                            } 
                            unlink(realpath($docs['FULLFILEPATH']));
                        } 
                        catch (Exception $exc) 
                        {
                            unlink(realpath($docs['FULLFILEPATH']));
                            $nrSolic = $docs['NOME'];
                            $this->_helper->flashMessenger(array('message' => "GT_PF 02 - Problema ao tentar importar os dados do arquivo $nrSolic <br/>" . $exc->getMessage(), 'status' => 'notice'));
                        }
                    }
               }
               
               
                foreach ($nrDocsRed["existentes"] as $docs) {
                    $dadosDsv[$docs["COLUNA"]] = $docs["ID_DOCUMENTO"];
                    
                    $e = explode(".",$docs['NOME']);
                   
                    if ($e[1] == "xls" || $e[1] == "xlsx")
                    {
                        try 
                        {
                            $nrDocumento = explode("_", $docs['NOME']);
                            foreach ($nrDocumento as $nrDoc) 
                            {
                                $extensões = array('.xlsx', '.xls');
                                $nrDoc = rtrim(str_replace($extensões, '', $nrDoc));
                                if (is_numeric($nrDoc)) 
                                {
                                    $idDocumento = $SadTbDocmDocumento->getDocumentoIdByNrDoc($nrDoc);
                                    $numeroSosti = $nrDoc;
                                }
                            }
        
                            if (!empty($idDocumento)) 
                            {
                                $idDoc = $idDocumento[0]['DOCM_ID_DOCUMENTO'];
                                $dcmto = $docs['ID_DOCUMENTO'];
                                
                                $arquivo = PHPExcel_IOFactory::load($docs['FULLFILEPATH']);
                                $arquivo->setActiveSheetIndexByName('Plano de Contagem');
                                $leitura = $arquivo->getActiveSheet();
                                
                                $nroSostiInterno = $leitura->getCell('I1')->getValue();
                                
                                
                                $validaNroSosti = strpos($nroSostiInterno, $numeroSosti);
                                
                                
                                
                                $dadosDsv["PFDS_QT_PF_BRUTO"]   = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue());
                                $dadosDsv["PFDS_QT_PF_LIQUIDO"] = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue());
                                
                                #exit;
                                
                            } 
                            unlink(realpath($docs['FULLFILEPATH']));
                        } 
                        catch (Exception $exc) 
                        {
                            unlink(realpath($docs['FULLFILEPATH']));
                            $nrSolic = $docs['NOME'];
                            $this->_helper->flashMessenger(array('message' => "GT_PF 02 - Problema ao tentar importar os dados do arquivo $nrSolic <br/>" . $exc->getMessage(), 'status' => 'notice'));
                        }
                    }
                    
                }
            }
            /**
             * Cadastra/altera os dados inseridos pela empresa responsável pelo desenvolvimento
             */
            $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
            unset($dadosDsv["Salvar"]);
            
            if ($dadosDsv["CADASTRO"] === 'CONTRADADA') 
            {
                unset($dadosDsv["CADASTRO"]);
                $db = Zend_Db_Table::getDefaultAdapter();
                $db->beginTransaction();
                $incluiDados = $negocioFaturamento->salvarDadosDesenvolvedora($dadosDsv);
                
                $db->commit();
            }
            
            if (!empty($incluiDados["success"])) 
            {
                foreach ($incluiDados["success"] as $success) 
                {
                    $this->_helper->flashMessenger(array('message' => $success, 'status' => 'success'));
                }
            }
            if (!empty($incluiDados["error"])) 
            {
                foreach ($incluiDados["error"] as $error) 
                {
                    $this->_helper->flashMessenger(array('message' => $error, 'status' => 'success'));
                }
            }
            $this->_helper->_redirector($action, $controller, 'sosti');
        }
    }

    /**
     * Recebe os RIAS(documento word) ou a guia de contagem de PF(Excel) e 
     * verifica se já possui os dados gravados em suas devidas solicitações. 
     * 
     * Caso positivo, a função retorna a informação que já existe documento cadastradado para aquela solicitação. 
     * Caso negativo, grava os dados referentes a aquela solicitação.
     */
    public function setdadosafericaoAction() {
        if ($this->getRequest()->isPost()) {
            $dadosAfe = $this->getRequest()->getPost();
            
            
            
            /**
             * Recebe a controller e a action de onde veio a requisição para retornar a mesma tela após a inclusão dos dados. 
             */
            $controller = $dadosAfe["CONTROLLER"];
            $action = $dadosAfe["ACTION"];
            unset($dadosAfe["CONTROLLER"]);
            unset($dadosAfe["ACTION"]);

            $anexos = new Zend_File_Transfer_Adapter_Http();
            $anexos->setDestination(APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR);
            
            
            if ($anexos->getFileName()) 
            {
                try 
                {
                    $data["DOCM_NR_DOCUMENTO"] = "FATURAMENTO DE SOLICITAÇÕES";
                    $data['DOCM_ID_CONFIDENCIALIDADE'] = 0;

                    $upload = new App_Multiupload_Faturamento($data);
                    $nrDocsRed = $upload->incluirarquivos($anexos);
                } 
                    catch (Exception $exc) 
                    {
                        $this->_helper->flashMessenger(array('message' => "CAD_ANEX 01 - Não foi possível inserir anexos, se possível encaminhar documentos sem anexo.", 'status' => 'notice'));
                        $this->_helper->_redirector($action, $controller, 'sosti');
                    }
                
                $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
                $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
                
                foreach ($nrDocsRed["incluidos"] as $docs) 
                {
                    $dadosAfe[$docs["COLUNA"]] = $docs["ID_DOCUMENTO"];
                    
                    $a = $docs['NOME'];
                    $e = explode(".", $docs['NOME']);
                    preg_match_all('!\d+!', $a, $m);
                    rsort($m[0],SORT_NUMERIC);

                    $nSosti = $m[0][0];
                    $nExt   = $e[1];

                    
                    $e = explode(".",$docs['NOME']);
                   
                    
                    if (($nExt == "xls") || ($nExt == "xlsx"))
                    {
                        try 
                        {
                            if (is_numeric($nSosti)) 
                            {
                                $idDocumento = $SadTbDocmDocumento->getDocumentoIdByNrDoc($nSosti);
                                $numeroSosti = $nSosti;
                            }
                           
                            if (!empty($idDocumento)) 
                            {
                                $idDoc = $idDocumento[0]['DOCM_ID_DOCUMENTO'];
                                $dcmto = $docs['ID_DOCUMENTO'];
                                
                                $arquivo = PHPExcel_IOFactory::load($docs['FULLFILEPATH']);
                                $arquivo->setActiveSheetIndexByName('Plano de Contagem');
                                $leitura = $arquivo->getActiveSheet();
                                
                                $nroSostiInterno = $leitura->getCell('I1')->getValue();
                                
                                $validaNroSosti = strpos($nroSostiInterno, $numeroSosti);
                                
                                if (strcmp($nroSostiInterno,$numeroSosti) != 0)
                                {
                                    throw new Exception('GT_PF 01 - O arquivo não corresponde com o número do SOSTI.');
                                }
                                
                               /*
                                 * 
                                 * EDITADO PARA O BANCO DE PRODUÇÃO -- NÃO ACEITA ESSA FORMATAÇÃO
                                $dadosAfe["PFAF_QT_PF_BRUTO"]   = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue());
                                $dadosAfe["PFAF_QT_PF_LIQUIDO"] = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue());
                                */
                                
                                $teste = $leitura->getCell('I1')->getValue();
                                echo "teste ".$teste;
                                
                                
//                                $dadosAfe["PFAF_QT_PF_BRUTO"]   = $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue();
//                                $dadosAfe["PFAF_QT_PF_LIQUIDO"] = $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue();
                                $dadosAfe["PFAF_QT_PF_BRUTO"]   = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue());
                                $dadosAfe["PFAF_QT_PF_LIQUIDO"] = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue());
                                
                                
                            } 
                            unlink(realpath($docs['FULLFILEPATH']));
                        } 
                        catch (Exception $exc) 
                        {
                            unlink(realpath($docs['FULLFILEPATH']));
                            $nrSolic = $docs['NOME'];
                            $this->_helper->flashMessenger(array('message' => "GT_PF 02 - Problema ao tentar importar os dados do arquivo $nrSolic <br/>" . $exc->getMessage(), 'status' => 'notice'));
                        }
                    }
               }
               
               
                foreach ($nrDocsRed["existentes"] as $docs) {
                    $dadosAfe[$docs["COLUNA"]] = $docs["ID_DOCUMENTO"];
                    echo "existe";
                    exit;
                    $e = explode(".",$docs['NOME']);
                   
                    if ($e[1] == "xls" || $e[1] == "xlsx")
                    {
                        try 
                        {
                            $nrDocumento = explode("_", $docs['NOME']);
                            foreach ($nrDocumento as $nrDoc) 
                            {
                                $extensões = array('.xlsx', '.xls');
                                $nrDoc = rtrim(str_replace($extensões, '', $nrDoc));
                                if (is_numeric($nrDoc)) 
                                {
                                    $idDocumento = $SadTbDocmDocumento->getDocumentoIdByNrDoc($nrDoc);
                                    $numeroSosti = $nrDoc;
                                }
                            }
        
                            if (!empty($idDocumento)) 
                            {
                                $idDoc = $idDocumento[0]['DOCM_ID_DOCUMENTO'];
                                $dcmto = $docs['ID_DOCUMENTO'];
                                
                                $arquivo = PHPExcel_IOFactory::load($docs['FULLFILEPATH']);
                                $arquivo->setActiveSheetIndexByName('Plano de Contagem');
                                $leitura = $arquivo->getActiveSheet();
                                
                                $nroSostiInterno = $leitura->getCell('I1')->getValue();
                                
                                
                                $validaNroSosti = strpos($nroSostiInterno, $numeroSosti);
                                
                                
                                
                                $dadosAfe["PFDS_QT_PF_BRUTO"]   = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_SR')->getOldCalculatedValue());
                                $dadosAfe["PFDS_QT_PF_LIQUIDO"] = str_replace('.', ',', $leitura->getCell('_Tot_Aferidos_CR')->getOldCalculatedValue());
                                
                                #exit;
                                
                            } 
                            unlink(realpath($docs['FULLFILEPATH']));
                        } 
                        catch (Exception $exc) 
                        {
                            unlink(realpath($docs['FULLFILEPATH']));
                            $nrSolic = $docs['NOME'];
                            $this->_helper->flashMessenger(array('message' => "GT_PF 02 - Problema ao tentar importar os dados do arquivo $nrSolic <br/>" . $exc->getMessage(), 'status' => 'notice'));
                        }
                    }
                    
                }
            }
            
            
            
            /**
             * Cadastra/altera os dados inseridos pela empresa responsável pelo desenvolvimento
             */
            $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
            unset($dadosAfe["Salvar"]);
            if ($dadosAfe["CADASTRO"] === 'AFERICAO') {
                unset($dadosAfe["CADASTRO"]);
                $db = Zend_Db_Table::getDefaultAdapter();
                $db->beginTransaction();
                $incluiDados = $negocioFaturamento->salvarDadosAfericao($dadosAfe);
                $db->commit();
            }
            if (!empty($incluiDados["success"])) {
                foreach ($incluiDados["success"] as $success) {
                    $this->_helper->flashMessenger(array('message' => $success, 'status' => 'success'));
                }
            }
            if (!empty($incluiDados["error"])) {
                foreach ($incluiDados["error"] as $error) {
                    $this->_helper->flashMessenger(array('message' => $error, 'status' => 'success'));
                }
            }
            $this->_helper->_redirector($action, $controller, 'sosti');
        }
    }

    /**
     * Recebe os RIAS(documento word) ou a guia de contagem de PF(Excel) e 
     * verifica se já possui os dados gravados em suas devidas solicitações. 
     * 
     * Caso positivo, a função retorna a informação que já existe documento cadastradado para aquela solicitação. 
     * Caso negativo, grava os dados referentes a aquela solicitação.
     */
    public function setdadoscontratanteAction() {
        if ($this->getRequest()->isPost()) {
            $dadosTrf = $this->getRequest()->getPost();
            
            /**
             * Recebe a controller e a action de onde veio a requisição para retornar a mesma tela após a inclusão dos dados. 
             */
            $controller = $dadosTrf["CONTROLLER"];
            $action = $dadosTrf["ACTION"];
            unset($dadosTrf["CONTROLLER"]);
            unset($dadosTrf["ACTION"]);

            $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
            
            unset($dadosTrf["Salvar"]);
            /**
             * Cadastra/altera os dados inseridos pelo Tribunal
             */
            if ($dadosTrf["CADASTRO"] === 'CONTRATANTE') {
                unset($dadosTrf["CADASTRO"]);
                $db = Zend_Db_Table::getDefaultAdapter();
                $db->beginTransaction();
                $negocioFaturamento->salvarDadosTRF($dadosTrf);
                $db->commit();
            }
            $this->_helper->_redirector($action, $controller, 'sosti');
        }
    }

    public function exportardocumentosAction() {
        $this->view->title = 'Download de Documentos das Solicitações';
        if ($this->getRequest()->isPost()) {
            
            $dadosPost = $this->getRequest()->getPost();
            
            
            
            if ($dadosPost["acao"] == 'Exportar') {
                foreach ($dadosPost["solicitacao"] as $id => $solics) {
                    $solicitacoes[$id] = Zend_Json::decode($solics);
                }
                $this->view->dados = $solicitacoes;
            } else if ($dadosPost["acao"] == 'Gerar Download') {
                try {
                    foreach ($dadosPost["solicitacao"] as $id => $solics) {
                        $solicsDecode[$id] = Zend_Json::decode($solics);
                        $faturamento = new Trf1_Sosti_Negocio_Faturamento();

                        $FaturamentoContratada = $faturamento->dadosFaturamentoContratada($solicsDecode[$id]['SSOL_ID_DOCUMENTO']);
                        $FaturamentoAfericao = $faturamento->dadosFaturamentoAfericao($solicsDecode[$id]['SSOL_ID_DOCUMENTO']);

                        $tipo = 3;
                        switch ($dadosPost["TIPO_EXPORT"]) {
                            case 'PFDS_NR_DCMTO_RIA_ORIGINAL':

                                $nrDoc = $FaturamentoContratada[0]["PFDS_NR_DCMTO_RIA_ORIGINAL"];
                                break;
                            case 'PFDS_NR_DCMTO_RIA_ESCLARECER':

                                $nrDoc = $FaturamentoContratada[0]["PFDS_NR_DCMTO_RIA_ESCLARECER"];
                                break;
                            case 'PFDS_NR_DCMTO_RIA_ESCLARECIDO':

                                $nrDoc = $FaturamentoContratada[0]["PFDS_NR_DCMTO_RIA_ESCLARECIDO"];
                                break;
                            case 'PFDS_NR_DCMTO_CONTAGEM':

                                $nrDoc = $FaturamentoContratada[0]["PFDS_NR_DCMTO_CONTAGEM"];
                                $tipo = 8;
                                break;
                            case 'PFAF_NR_DCMTO_RIA_PARECER':

                                $nrDoc = $FaturamentoAfericao[0]["PFAF_NR_DCMTO_RIA_PARECER"];
                                break;
                            case 'PFAF_NR_DCMTO_RIA_ESCLARECIDO':

                                $nrDoc = $FaturamentoAfericao[0]["PFAF_NR_DCMTO_RIA_ESCLARECIDO"];
                                break;
                            case 'PFAF_NR_DCMTO_CONTAGEM':

                                $nrDoc = $FaturamentoAfericao[0]["PFAF_NR_DCMTO_CONTAGEM"];
                                $tipo = 8;
                                break;

                            default:
                                break;
                        }
                        
                        if (!is_null($nrDoc)) {
                            $arrayEnderecoAbsoluto[$id] = $faturamento->recuperarDoc($solicsDecode[$id]['SSOL_ID_DOCUMENTO'], $solicsDecode[$id]['DOCM_NR_DOCUMENTO'], $nrDoc, $tipo, true);
                            
                        }
                    }
                    
                } catch (Exception $exc) {
                    echo 'DW-FAT 01 - Erro ao recuperar Número dos anexos. Erro: ' . $exc->getMessage();
                }
                
                $qtd = 0;
                $this->view->dados = $solicsDecode;
                
                $destino = APPLICATION_PATH . '/../temp/download.zip';
                $pasta = APPLICATION_PATH . '/../temp/download/';
                
                foreach ($arrayEnderecoAbsoluto as $arquivos) 
                {
                    $qtd++;
                }
                
                if ($qtd > 1)
                {
                    $filter = new Zend_Filter_Compress(array('adapter' => 'Zip',
                        'options' => array(
                            'archive' => $destino),));

                     $filter->filter($pasta);
                     $this->view->download = $qtd;
                     
                    foreach ($arrayEnderecoAbsoluto as $arquivos) 
                    {
                        unlink($arquivos["ENDERECO"]);
                    }
                }
                    else
                    {
                        $this->view->download1 = $arrayEnderecoAbsoluto[0]['ENDERECO'];
                    }
               
                
                return true;
            }
        }
    }

    public function liberarafericaoAction() {
        $this->view->title = 'Liberar Lote para Aferição';
        if ($this->getRequest()->isPost()) {
            $dadosPost = $this->getRequest()->getPost();
            if ($dadosPost["acao"] == 'Liberar para Aferição') {
                $retiradas = array();
                $contaRetiradas = -1;
                foreach ($dadosPost["solicitacao"] as $id => $data) {
                    
                    
                    
                    $dados[$id] = Zend_Json::decode($data);
                    if ($dados[$id]["SCTA_ID_STATUS_AFE"] != 8) {
                        if ($dados[$id]["SCTA_ID_STATUS_AFE"] != 11) {
                            if ($dados[$id]["SCTA_ID_STATUS_AFE"] != 12) {
                                $msgValidacao =  $dados[$id]["DOCM_NR_DOCUMENTO"]." - removida pois não possui o status para liberação<br />";
                                $retiradas[$contaRetiradas++] = $msgValidacao;
                                unset($dados[$id]);
                                
                                
                            }
                        }
                    }
                }
                
                if(count($retiradas)>0)
                {
                    $retiradas = array_unique($retiradas);
                    $retiradasStr = implode('<br />',$retiradas);
                    $msg_to_user = "Atenção para a(s) solicitação(ões) nº(s): <br> ".$retiradasStr."";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    $this->_helper->_redirector('relatorios', 'faturamento', 'sosti');
                }
                                
                if (empty($dados)) {
                    $this->_helper->_redirector('relatorios', 'faturamento', 'sosti');
                }
                $faturamento = new Trf1_Sosti_Negocio_Faturamento();
                $this->view->total = $faturamento->calculoStatus($dados);
                $this->view->dados = $dados;
            } else if ($dadosPost["acao"] == 'Liberar Lote') {
                try {
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $db->beginTransaction();
                    foreach ($dadosPost["solicitacao"] as $data) {
                        $dados = Zend_Json::decode($data);
                        $dadosAfe["PFAF_ID_SOLICITACAO"] = $dados["SSOL_ID_DOCUMENTO"];
                        $dadosAfe["PFAF_NR_LOTE"] = $dadosPost["PFAF_NR_LOTE"];
                        $data_retorno = $dadosPost['PFAF_DH_PREVISAO_RETORNO_LOTE'];
                        $dadosAfe["PFAF_DH_PREVISAO_RETORNO_LOTE"] = new Zend_Db_Expr("TO_DATE('$data_retorno','dd/mm/yyyy')");
                        $dadosAfe["PFAF_ID_STATUS"] = 22;

                        $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
                        $msg = $negocioFaturamento->salvarDadosAfericao($dadosAfe);
                        $this->_helper->flashMessenger(array('message' => $msg, 'status' => 'success'));
                    }
                    $db->commit();
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                }
                $this->_helper->_redirector('relatorios', 'faturamento', 'sosti');
            }
        }
    }

    public function liberarfaturamentoAction() {
        $this->view->title = 'Gerar Faturamento';
        if ($this->getRequest()->isPost()) {
            $dadosPost = $this->getRequest()->getPost();
            if ($dadosPost["acao"] == 'Gerar Faturamento') {
                $retiradas = array();
                $contaRetiradas = -1;
                foreach ($dadosPost["solicitacao"] as $id => $data) 
                {
                    $dados[$id] = Zend_Json::decode($data);
                    if ($dados[$id]["SCTA_ID_STATUS_TRF"] != 18) {
                        $msgValidacao =  $dados[$id]["DOCM_NR_DOCUMENTO"]." - removida pois não possui o status para liberação<br />";
                        $retiradas[$contaRetiradas++] = $msgValidacao;
                        unset($dados[$id]);
                    }
                }
                if(count($retiradas)>0)
                {
                    $retiradas = array_unique($retiradas);
                    $retiradasStr = implode('<br />',$retiradas);
                    $msg_to_user = "Atenção para a(s) solicitação(ões) nº(s): <br> ".$retiradasStr."";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    $this->_helper->_redirector('relatorios', 'faturamento', 'sosti');
                }
                
                
                if (empty($dados)) 
                {
                    $msg_to_user = "Dados vazios!";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    $this->_helper->_redirector('relatorios', 'faturamento', 'sosti');
                }
            } else if ($dadosPost["acao"] == 'Liberar Lote') {
                try {
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $db->beginTransaction();
                    foreach ($dadosPost["solicitacao"] as $data) {
                        $dados = Zend_Json::decode($data);
                        $dadosTRF["PFTR_ID_SOLICITACAO"] = $dados["SSOL_ID_DOCUMENTO"];
                        $dadosTRF["PFTR_NR_ID_RELAT_FATURAMENTO"] = $dadosPost["PFTR_NR_ID_RELAT_FATURAMENTO"];
                        $dadosTRF["PFTR_ID_STATUS"] = 20;

                        $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
                        $msg = $negocioFaturamento->salvarDadosTRF($dadosTRF);
                        $this->_helper->flashMessenger(array('message' => $msg, 'status' => 'success'));
                    }
                    $db->commit();
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                }
                $this->_helper->_redirector('relatorios', 'faturamento', 'sosti');
            }
        }
        $faturamento = new Trf1_Sosti_Negocio_Faturamento();
        $this->view->total = $faturamento->calculoStatus($dados);
        $this->view->dados = $dados;
    }

}

