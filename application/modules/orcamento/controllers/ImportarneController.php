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
 * Disponibiliza as funcionalidades sobre importação de notas de empenho.
 *
 * @category Orcamento
 * @package Orcamento_ImportarneController
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ImportarneController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'Listagem de Importação de Notas de Empenho' );

        // Define a Negocio de Importacao
        $this->business = new Orcamento_Business_Negocio_Importarne ();

        // Define a Busines de nc
        $this->_negocione = new Orcamento_Business_Negocio_Ne ();

        //
        $requisicao = $this->getRequest ();
        $this->_controle = strtolower ( $requisicao->getControllerName () );

        // Define a classe facade
        $this->defineFacade ( 'Importarne' );

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
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de Notas de Empenho';

        // Formulario na view index
        $frmnc = new Orcamento_Form_Importarne ();

        // Exibe o formulario na view index
        $this->view->frmnc = $frmnc;

         // Importa os arquivos
        if ($this->_request->isPost()) {

            $dados = $this->getRequest()->getPost();

            if( $frmnc->isValid($dados) ){
                // Arquivo enviado
                $f = $_FILES;

                // Nome do arquivo txt
                $txtName = $f["TEXTO"]["name"];

                // Abre o arquivo
                $arquivo = file($f["TEXTO"]["tmp_name"], FILE_IGNORE_NEW_LINES);

                // Verifica se é txt
                $this->verificaTxt($f);

                // Verifica se é um arquivo NE
                $this->verificaNE($arquivo);

                // Array com os dados da nota - array
                $ne = array();

                $valor = new Trf1_Orcamento_Valor();

                foreach ($arquivo as $file) {
                    // Compõe a nota de credito - string
                    $operador = trim(substr($file, 18, 6));
                    $notaemp = trim(substr($file, 29, 12));

                    // Configura o campo data
                    $data = App_Util::stringDateToDate( substr($file, 0, 8));
                    $noem_dt = new Zend_Db_Expr("TO_DATE('$data','DD/MM/YYYY')");

                    $esfera = substr($file, 301, 1);

                    $ptres = (substr($file, 302, 6) == ' ' or substr($file, 302, 6) == 000000 ) ? '' : substr($file, 302, 6);
                    $natureza = (substr($file, 318, 8) == ' ' or substr($file, 318, 8) == 0 ) ? '' : substr($file, 318, 8);
                    $fonte = (substr($file, 308, 10) == ' ' or substr($file, 308, 10) == 0 ) ? '' : substr($file, 308, 10);
                    $processo = substr($file, 343, 20);
                    $evento = (substr($file, 295, 6) == ' ' or substr($file, 295, 6) == 0 ) ? '' : substr($file, 295, 6);
                    $referencia = (substr($file, 41, 12) == ' ' or substr($file, 41, 12) == 0 ) ? '' : substr($file, 41, 12);

                    $campoValor = substr($file, 331, 10);

                    $valor = $this->_negocione->trataValor( $campoValor );

                    $vlNe = new Zend_Db_Expr("TO_NUMBER(" . $valor . ")");

                    // Configura a despesa e a despesa reserva
                    $arrConfigDesp = $this->configuraDespesas($file);

                    if( is_array($arrConfigDesp )){
                        $despesa = trim(str_replace(array("-"), "",$arrConfigDesp[1]));
                        $noemDesp = str_replace(array(".",",","-" ), "", $despesa);
                    }else{
                        $noemDesp = "";
                    }

                    $ne[] = array(
                                    'IMPD_TX_LINHA' => $file,
                                    'NOME_DO_ARQUIVO' => $txtName,
                                    'NOEM_CD_NOTA_EMPENHO' => $notaemp.$operador,
                                    'NOEM_CD_UG_OPERADOR' => $operador,
                                    'NOEM_CD_NE_REFERENCIA' => $referencia,
                                    'NOEM_CD_EVENTO' => $evento,
                                    'NOEM_CD_PT_RESUMIDO' => $ptres,
                                    'NOEM_CD_FONTE' => is_numeric($fonte) ? substr( $fonte, 1, 3) : "",
                                    'NOEM_CD_VINCULACAO' => '',
                                    'NOEM_VL_NE' => $vlNe,
                                    'NOEM_CD_ELEMENTO_DESPESA_SUB' => $natureza,
                                    'NOEM_CD_CATEGORIA' => '',
                                    'NOEM_NR_DESPESA' => $noemDesp,
                                    'NOEM_DH_NE' => $noem_dt,
                                    'NOEM_DT_EMISSAO' => $noem_dt,
                                    'NOEM_DS_OBSERVACAO' => trim(substr($file, 61, 234)),
                                    'NOEM_VL_NE_ACERTADO' => $vlNe,
                                    'NOEM_CD_UG_FAVORECIDO' => substr($file, 18, 6),
                                    'NOEM_CD_ESFERA' => (int) $esfera,
                                    'NOEM_IC_ACERTADO_MANUALMENTE' => 0,
                                    'NOEM_NR_PROCESSO' => $processo
                                );

                } // endforeach

                // Inclui o array de nes
                $resultado = $this->_negocione->incluirDadosImportados($ne, $txtName);

                if(!$resultado["inseridos"] > 0 ){
                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO . $resultado['inseridos'] . " Notas inseridas.", 'status' => 'success'));
                }else{
                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO, 'status' => 'success'));
                }

                // volta pra index
                $this->_redirect('orcamento/importarne/index');

            }else{
                $this->_helper->flashMessenger(array(message => "Arquivo não selecionado. Selecione um arquivo do tipo txt!", 'status' => 'error'));
                $this->_redirect('orcamento/importarne/index');
            }

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
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Informativo';

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
        $this->view->telaTitle = 'Restaurar Informativo';

        // Restauração de registro logicamente excluído
        $this->restaurar ();
    }

    // Verifica se é um txt
    public function verificaTxt($f)
    {

        $file = pathinfo($f["TEXTO"]["name"]);

        if( strtolower( $file["extension"] ) != 'txt') {
            $this->erroOperacao("O arquivo não é txt!");
        }
    }

    // Verifica se NE já existe no banco, se sim deleta
    public function verificaExistencia($ne)
    {
        return $this->existeNE($ne);
    }

    // Verfica se é um NE
    public function verificaNE($f)
    {
        $nc = substr($f[0], 29, 12);
        $pos = strripos($nc, "NE");

        if($pos == false){
            $this->erroOperacao("O arquivo $nc não é uma Nota de Crédito!");
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
        // Verifica os tipos de RDO a tratar
        // aumento do campo prevendo mais digitos de despesa nos proximos anos
        $rdo = substr($file, 60, 15 );

        $tipoRdo1 = strripos($rdo, "RDO ");
        $tipoRdo2 = strripos($rdo, "RDO-");
        $tipoRdo3 = strripos($rdo, "RD0 "); // zero
        $tipoRdo4 = strripos($rdo, "RD0-"); // zero

        // verifica se é rdo
        if($tipoRdo1 or $tipoRdo2 or $tipoRdo3 or $tipoRdo4){
            $despesas = substr($file, 61, 10 );

            // verifica a posicao do - ou espaço ( bugfix )
            $rdoPos = strripos($despesas, '-');
            if($rdoPos){
                return $arrDespesas = explode("-", trim($despesas));
            }else{
                return $arrDespesas = explode(" ", trim($despesas));
            }

        }else{
            // se não tem rdo retorna false, despesa é nula
            return false;
        }
    }

    private function recriarCaches($controle) {
        $cache = new Trf1_Orcamento_Cache ();
        $cache->excluirCachesSensiveis ( $controle );
    }

}
