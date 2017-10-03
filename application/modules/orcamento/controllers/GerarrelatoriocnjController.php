<?php

/**
 * Contém controller da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre regra.
 *
 * @category Orcamento
 * @package Orcamento_GerarrelatoriocnjController
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_GerarrelatoriocnjController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {

        // Título apresentado no Browser
        $this->defineTituloBrowser('Gerar Relatório CNJ');

        // Define a classe facade
        $this->defineFacade('gerarrelatoriocnj');

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function indexAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Gerar Relatório CNJ';

        $formulario = new Orcamento_Form_Gerarrelatoriocnj();

        // Exibe o formulário
        $this->view->formulario = $formulario;
    }

    /**
     * Gera o relatório do Anexo I e II em Excel ou HTML
     * 
     * @throws Zend_Exception
     */
    public function relatorioAction() {

        try {
            // Somente entra se for post
            if ($this->getRequest()->isPost()) {

                // Busca dados do formulário após o post e ação incluir
                $dados = $this->getRequest()->getPost();
                
                $negocio = new Orcamento_Business_Negocio_Gerarrelatoriocnj();
                
                // -------------------------------------------------------------
                // array base das informações

                $array['ano'] = $dados['REGC_AA_REGRA'];
                $array['mes'] = $dados['IMPA_IC_MES'];
                $array['anexo'] = $dados['TIPO_ANEXO'];
                $array['formato'] = $dados['TIPO_RELATORIO'];
                $array['html'] = $dados['TIPO_ANEXO_HTML'];
                $array['excel'] = $dados['TIPO_ANEXO_EXCEL'];
                $array['ug'] = $dados['UNIDADE_GESTORA'];
                $array['ug_todas'] = $dados['UG_TODAS'];

                // -------------------------------------------------------------
                // verificações em cache para não sobrecarregar as consultas
                
                $cache = new Trf1_Cache ();
                $cacheID = $negocio->consultarCacheID($array);
                
                /*if ($cacheID != NULL) {
                    // efetua leitura do cache
                    $dadosCache = $cache->lerCache($cacheID);

                    // caso não tenha cache cria o mesmo
                    if ($dadosCache === false) {
                        // gera matriz que alimenta relatório
                        $cacheTag = $negocio->retornaCacheTags();
                        
                        // salva cache
                        $cache->criarCache($matriz, $cacheID, 0, $cacheTag);
                    } else {
                        $matriz = $dadosCache;
                    }
                }*/
                
                $matriz = $negocio->gerarRelatorio($dados);

                /* Caso seja relatório excell executa essa extensão criada */
                if( $array['anexo'] != 1 && $array['formato'] == 2 ) {
                    $negocioManipular = new Orcamento_Business_Negocio_RelatorioCNJ_ManipularDados();
                    $matriz = $negocioManipular->manipularAnexoII($dados);
                }              

                // -------------------------------------------------------------
                // seta dados para visualização na view
                
                foreach ($matriz as $key => $value) {
                    $matriz[$key]['ano'] = $dados['REGC_AA_REGRA'];
                    $matriz[$key]['mes'] = $dados['IMPA_IC_MES'];
                }

                /* deprecated 
                $matriz['ano'] = $dados['REGC_AA_REGRA'];
                $matriz['mes'] = $dados['IMPA_IC_MES'];
                */

                switch ($dados['TIPO_ANEXO_HTML']) {
                    case 10: 
                    $tituloAnexoIHtml = "Restos a pagar"; 
                    $tipo = 10;
                    break;
                    case 3: 
                    $tituloAnexoIHtml = "Orçamentário"; 
                    $tipo = 3;
                    break;
                    case 1: 
                    $tituloAnexoIHtml = "Financeiro"; 
                    $tipo = 1;
                    break;
                }

                // envia dados para a view
                $this->view->tituloAnexo = $tituloAnexoIHtml;
                $this->view->dadosAnexo = $matriz;
                $this->view->ano = $dados['REGC_AA_REGRA'];
                $this->view->mes = $dados['IMPA_IC_MES'];
                $this->view->anexo = $array['anexo'];
                $this->view->formato = $array['formato'];
                $this->view->excel = $array['excel'];
                $this->view->arrayTodos = $array;

                // -------------------------------------------------------------
                
                // desabilita todo o layout para gerar o relatório
                $this->_helper->layout->disableLayout();

                // seta para não haver render por causa do Excel
                $tEx = Orcamento_Business_Negocio_Gerarrelatoriocnj::ANEXO_EXCEL;

                // seta cabeçalho para transformar em dowload
                if ($tEx == $array['formato']) {
                    $this->getResponse()
                            ->setHeader('Content-Disposition', 'attachment; filename=anexo_' . time() . '.xls')
                            ->setHeader('Content-type', 'application/vnd.ms-excel')
                            ->setHeader('Cache-Control', 'max-age=0');
                } else {
                    $this->getResponse()
                            ->setHeader('Content-Disposition', 'attachment; filename=anexo_' . time() . '.html')
                            ->setHeader('Content-type', 'text/html')
                            ->setHeader('Cache-Control', 'max-age=0');
                }
            }
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }
    
    public function validardataAction() {
        
        // Somente entra se for post
        if ($this->getRequest()->isPost()) {

            // Busca dados do formulário após o post e ação incluir
            $dados = $this->getRequest()->getPost();
            $negocial = new Orcamento_Business_Importacao_Base();

            $retorno = true;
            
            try {
                $dadosPost = array();
                $dadosPost['IMPA_AA_IMPORTACAO'] = $dados['REGC_AA_REGRA'];
                $dadosPost['IMPA_IC_MES'] = $dados['IMPA_IC_MES'];
                
                $negocial->validarRN083($dadosPost);
            } catch (Exception $e) {
                $retorno = false;
            }

            $this->_helper->json->sendJson($retorno);
            
        }
        
    }
        
}