
<?php

/**
 * Contém controller da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Controller
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades sobre importação de notas de crédito.
 *
 * @category Orcamento
 * @package Orcamento_ImportacaoncController
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ImportarncController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'Listagem de Importação de Notas de Crédito' );

        // Define a Business
        $this->business = new Orcamento_Business_Negocio_Importarnc();
        
        // Define a Busines de nc
        $this->_negocionc = new Orcamento_Business_Negocio_Nc ();
        
        // Define a classe facade
        $this->defineFacade ( 'Importarnc' );

        // Conforme oriental na tag @tutorial
        parent::init ();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction ()
    {
        // Limpa cache
        $this->business->excluiCaches();

        // Nome da funcionalidade
        $funcionalidade = 'Listagem de Notas de Crédito';                

        // Formulario na view index
        $frmnc = new Orcamento_Form_Importarnc ();
        
        // Exibe o formulario na view index
        $this->view->frmnc = $frmnc;

        // Importa os arquivos
        if ($this->_request->isPost()) {
            // Arquivo enviado
            $f = $_FILES;

            // Nome do arquivo txt
            $txtName = $f["TEXTO"]["name"];

            // Verifica se é txt
            $this->verificaTxt($f);

            // Transforma o arquivo a ser importado
            $arquivo = file($f["TEXTO"]["tmp_name"], FILE_IGNORE_NEW_LINES);

            // Verfica se o arquivo é uma NC
            $this->verificaNC($arquivo);

            // Array com os dados da nota - array
            $nc = array();

            // Model de unidade gestora
            $modelUg = new Trf1_Orcamento_Negocio_Ug ();
            
            // Carrega classe de configuração de valores
            $valor = new Trf1_Orcamento_Valor();

            foreach ($arquivo as $key => $file) {
                // Compõe a nota de credito - string
                $ncc = substr($file, 18, 6);

                // Configura o campo data
                $data = App_Util::stringDateToDate( substr($file, 0, 8));
                $nocr_dt = new Zend_Db_Expr("TO_DATE('$data','dd/mm/yyyy')");
                            
                //$valorNc = $valor->formataMoedaBanco(trim( substr($file, 318, 19) ));
                $campoValor = substr($file, 318, 17);
                $valorNc = $valor->formataMoedaOrcamento( $campoValor );

                $vlNc = new Zend_Db_Expr("TO_NUMBER(" . $valorNc . ")");

                // Configura o UG
                $ug = substr($file, 49, 6);                
                $valorUg = $modelUg->retornaRegistro( $ug );

                if(!$valorUg['UNGE_CD_UG']){
                    $ug = "";
                }

                // Configura o tipo de nc
                $tiponc = $this->configuraTipoNc($file);

                // Configura a despesa e a despesa reserva
                $arrConfigDesp = $this->configuraDespesas($file);
                if( is_array($arrConfigDesp )){
                    $despesa = trim(str_replace(array("-"), "", $arrConfigDesp[0]));
                    $reserva = trim(str_replace(array("-"), "",$arrConfigDesp[1]));
                }else{
                    $despesa = "";
                    $reserva = "";
                }

                if($despesa){
                    $modelodespesa = new Trf1_Orcamento_Negocio_Despesa();
                    $existe = $modelodespesa->retornaDespesa($despesa);    
                }

                // Verifica se existe a despesa evitando erro de integridade                
                if(!$existe){
                    $despesa = "";
                }

                $nc[] = array(
                    'NOME_ARQUIVO' => $txtName,
                    'IMPD_TX_LINHA' => $file,
                    'NOCR_CD_NOTA_CREDITO' => substr($file, 29, 12) . $ncc,
                    'NOCR_CD_UG_OPERADOR' => $ncc,
                    'NOCR_CD_TIPO_NC' => $tiponc,
                    'NOCR_CD_FONTE' => substr($file, 303, 3),
                    'NOCR_CD_PT_RESUMIDO' => substr($file, 296, 6),
                    'NOCR_CD_VINCULACAO' => '',
                    'NOCR_NR_DESPESA' => $despesa,
                    'NOCR_CD_ELEMENTO_DESPESA_SUB' => substr($file, 312, 8),
                    'NOCR_DH_NC' => $nocr_dt,
                    'NOCR_DT_EMISSAO' => $nocr_dt,
                    'NOCR_CD_CATEGORIA' => 'C',
                    'NOCR_DS_OBSERVACAO' => utf8_encode(substr($file, 55, 234)),
                    'NOCR_VL_NC' => $vlNc,
                    'NOCR_VL_NC_ACERTADO' => $vlNc,
                    'NOCR_CD_UG_FAVORECIDO' => $ug,
                    'NOCR_CD_EVENTO' => substr($file, 289, 6),
                    'NOCR_NR_DESPESA_RESERVA' => $reserva,
                    'NOCR_IC_ACERTADO_MANUALMENTE' => 0
                );
                
                // Zend_Debug::dump( $nc ); die;

            }

            // Inclui o array de nc
            $resultado = $this->_negocionc->incluirDadosImportados($nc, $txtName);

            if( $resultado['INSERIDOS'] > 0 ) {
                $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO . $resultado['INSERIDOS'] . " registros.", 'status' => 'success'));
            }else{
                $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_IMPORTAR_ERRO. " Nennhum registro foi inserido.", 'status' => 'error'));
            }

            // Redirect
            $this->_redirect('orcamento/importarnc/index');

            // Limpa o cache
            $this->business->excluiCaches();

            // Msg sucesso e redirect
            
            $this->_redirect('orcamento/importarnc/index');

        }

        // Importarnc listagem de registros
        $this->listar ( $funcionalidade );
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Importação';

        // Exibição de um registro
        $this->detalhe ();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Importar Nota de Crédito';

        // Inclusão do registro
        $this->incluir ();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Nota(s) de Crédito(s) importada(s)';

        // Exclusão do registro
        $this->excluir ();
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function restaurarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar Nota(s) de Crédito(s) importada(s)';

        // Restauração de registro logicamente excluído
        $this->restaurar ();
    }
    
    // Verifica se NE já existe no banco, se sim deleta
    private function deletaExistentes($arquivo)
    {
        $nc = "";
        foreach ($arquivo as $key => $file) {
            $ncc = substr($file, 19, 5);
            $nc .= "'".substr($file, 29, 12) . $ncc."',";
        }

        $codigos = explode(",", $nc);

        return $this->_negocionc->deleteNC($codigos);
    }
    
    // Verfica se é um Nc
    private function verificaNc($f)
    {
        $nc = substr($f[0], 29, 12);
        $pos = strripos($nc, "NC");

        if($pos == false){
            $this->erroOperacao("O arquivo $nc não é uma Nota de Crédito!");
        }

    }

    // Verifica se é um txt
    private function verificaTxt($f)
    {
        $file = pathinfo($f["TEXTO"]["name"]);

        if( strtolower( $file["extension"] ) != 'txt' ) {
            $this->erroOperacao("O arquivo não é txt!");
        }        
    }
    
    // flashmessgender do erro
    private function erroOperacao ( $mensagemErro )
    {
        $erro = $mensagemErro;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest();
        // $log->gravaLog($requisicao, $erro, zend_log::ERR);
        
        $this->_helper->flashMessenger ( array ( message => $erro, 'status' => 'error' ) );
    }

    /**
     * Verfifica se a despesa é RDO e se tem reserva e retorna o split despesa[0] e resserva[1]
     *
     * @param string $file
     * @return array|bool
     */
    private function configuraDespesas($file){
        $despPos = strripos($file, "RDO.");
        if($despPos){
            $despesas = substr($file, $despPos + 6, 11 );
            return $arrDespesas = explode(".", $despesas);
        }else{
            return false;
        }
    }

    /**
     * Verfifica se a despesa é RDO e se tem reserva e retorna o split despesa[0] e resserva[1]
     *
     * @param string $file
     * @return string null
     */
    private function configuraTipoNc($file){
        $despPos = strripos($file, "RDO.");

        if($despPos){
            $tipo = substr($file, $despPos + 4, 1 );
            if(is_numeric($tipo)){
                $tipo = null;
            }
            return $tipo;
        }else{
            return NULL;
        }
    }


}











