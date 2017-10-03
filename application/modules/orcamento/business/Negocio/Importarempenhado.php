<?php

/**
 * Contém regras negociais específicas desta funcionalidade.
 * 
 * e-Admin
 * e-Orçamento
 * Business - Negocio
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Contém as regras negociais de importação.
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_ImportarFinanceiro
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Importarempenhado extends Orcamento_Business_Importacao_Base {

    private $_padrao = ImportBuffer_Constants::PADRAO2;
    private $_tipo = Orcamento_Business_Importacao_Base::ARQUIVO_EMPENHADO;
    
    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {
        // Define qual é o padrão de importação
        parent::init($this->_padrao);
        
        $tipoImportacao = new Zend_Session_Namespace('tipoImportacao');
        $tipoImportacao->tipo = $this->_tipo;

        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Importacao();
        
        // Define a negocio
        $this->_negocio = Zend_Controller_Front::getInstance()->getRequest()
                ->getControllerName();
    }
 

    public function incluirDados( $dados, $codigo )
    {
        // codigo do arquivo importado na tabela impaimportacao
        $dados['IMPO_ID_IMPORT_ARQUIVO'] = $codigo;
        return parent::incluir($dados);

    }

    /**
     * Trata a edição de dados financeiros antes de salvar
     */
    public function editar($dados)
    {
        // remove o botao do array
        unset($dados["Enviar"]);

        $dados['IMPO_CD_PTRES'] =  trim(substr($dados['IMPO_CD_PTRES'], 0,6));

        $valor = new Trf1_Orcamento_Valor() ;

        // trata os campos com valor
        $dados['IMPO_VL_TOTAL_JAN'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_JAN']);
        $dados['IMPO_VL_TOTAL_FEV'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_FEV']);
        $dados['IMPO_VL_TOTAL_MAR'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_MAR']);
        $dados['IMPO_VL_TOTAL_ABR'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_ABR']);
        $dados['IMPO_VL_TOTAL_MAI'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_MAI']);
        $dados['IMPO_VL_TOTAL_JUN'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_JUN']);
        $dados['IMPO_VL_TOTAL_JUL'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_JUL']);
        $dados['IMPO_VL_TOTAL_AGO'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_AGO']);
        $dados['IMPO_VL_TOTAL_SET'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_SET']);
        $dados['IMPO_VL_TOTAL_OUT'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_OUT']);
        $dados['IMPO_VL_TOTAL_NOV'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_NOV']);
        $dados['IMPO_VL_TOTAL_DEZ'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_DEZ']);
        $dados['IMPO_VL_TOTAL'] = $valor->formataMoedaBanco($dados['IMPO_VL_TOTAL']);

        $negocioImpa = new Orcamento_Business_Negocio_ImpaImportar();
$existe = $negocioImpa-> verificaDuplicidade( $dados['IMPO_CD_CONTA_CONTABIL'],  $dados['IMPO_CD_PTRES'], $dados['IMPO_CD_NATUREZA_DESPESA'], $dados['IMPO_CD_FONTE'], $dados['IMPO_CD_ESFERA'], $dados['IMPO_CD_UG'], $dados['IMPO_CD_RESULTADO_PRIMARIO'], $dados['IMPO_IC_CATEGORIA'], null, 
        null, $dados['IMPA_AA_IMPORTACAO'], $this->_tipo, $dados['IMPO_ID_IMPORTACAO'] );

        $pos = strpos($dados['IMPO_CD_NATUREZA_DESPESA'], "-") - 1;
        $dados['IMPO_CD_NATUREZA_DESPESA'] =  trim(substr($dados['IMPO_CD_NATUREZA_DESPESA'], 0,$pos));

       if( count($existe) > 0 ){
            return false;
        }

        // base editar
        return parent::editar($dados);
    }

   /*
     * Retorna as condições restritivas, se houver INNER JOIN
     * sql.
     *
     * @param string $acao Nome da ação (action) em questão
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaJoins($acao = 'todos') {

        $join['index'] = "
        INNER JOIN CEO_TB_IMPA_IMPORTAR_ARQUIVO 
            ON IMPO_ID_IMPORT_ARQUIVO = IMPA_ID_IMPORT_ARQUIVO
        LEFT JOIN CEO_TB_ALIN_ALINEA 
            ON IMPO_ID_ALINEA = ALIN_ID_ALINEA 
        LEFT JOIN CEO_TB_INCI_INCISO 
            ON IMPO_ID_INCISO = INCI_ID_INCISO
        LEFT JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO 
                ON PTRS_CD_PT_RESUMIDO = IMPO_CD_PTRES            
        LEFT JOIN CEO_TB_FONT_FONTE ON 
            IMPO_CD_FONTE = FONT_CD_FONTE
        LEFT JOIN CEO_TB_CATE_CATEGORIA ON
            CATE_CD_CATEGORIA = IMPO_IC_CATEGORIA
        LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA ON 
            UNGE_CD_UG = IMPO_CD_UG 
        LEFT JOIN CEO_TB_VINC_VINCULACAO ON
            VINC_CD_VINCULACAO = IMPO_CD_VINCULACAO
        LEFT JOIN CEO_TB_ESFE_ESFERA ON
            ESFE_CD_ESFERA = IMPO_CD_ESFERA
        LEFT JOIN CEO_TB_EDSB_ELEMENTO_SUB_DESP ON
            EDSB_CD_ELEMENTO_DESPESA_SUB = IMPO_CD_NATUREZA_DESPESA                            
                ";

        $join['detalhe'] = $join['index'];
        $join['editar'] = $join['index'];
        $join['excluir'] = $join['index'];

        return $join[$acao];
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaOpcoesGrid ()
    {
        // Personaliza a exibição dos campos no grid
        $detalhes = array ( 
                'IMPO_ID_IMPORTACAO' => array ( 'title' => 'Código', 'abbr' => '' ),
                'IMPA_AA_IMPORTACAO' => array ( 'title' => 'Ano', 'abbr' => '' ),   
                'IMPO_ID_IMPORT_ARQUIVO' => array ( 'title' => 'Código arquivo', 'abbr' => '' ), 
                'IMPO_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'IMPO_CD_CONTA_CONTABIL' => array ( 'title' => 'Conta Contábil', 'abbr' => '' ), 
                'IMPO_CD_RESULTADO_PRIMARIO' => array ( 'title' => 'Resultado Primário', 'abbr' => '' ), 
                'IMPO_CD_FONTE' => array ( 'title' => 'Fonte', 'abbr' => '' ),
                'IMPO_CD_NATUREZA_DESPESA' => array ( 'title' => 'Natureza da despesa', 'abbr' => '' ),                
                'IMPO_CD_ESFERA' => array ( 'title' => 'Esfera', 'abbr' => '' ), 
                'IMPO_CD_PTRES' => array ( 'title' => 'PTRES', 'abbr' => '' ), 
                'IMPO_IC_CATEGORIA' => array ( 'title' => 'Categoria', 'abbr' => '' ), 
                'IMPO_CD_UG_RESPONSAVEL' => array ( 'title' => 'UG Responsavel', 'abbr' => '' ), 
                'IMPO_CD_VINCULACAO' => array ( 'title' => 'Vinculação', 'abbr' => '' ), 
                'IMPO_VL_TOTAL_JAN' => array ( 'title' => 'Janeiro', 'abbr' => '' ),
                'IMPO_VL_TOTAL_FEV' => array ( 'title' => 'Fevereiro', 'abbr' => '' ),
                'IMPO_VL_TOTAL_MAR' => array ( 'title' => 'Março', 'abbr' => '' ),
                'IMPO_VL_TOTAL_ABR' => array ( 'title' => 'Abril', 'abbr' => '' ),
                'IMPO_VL_TOTAL_MAI' => array ( 'title' => 'Maio', 'abbr' => '' ),
                'IMPO_VL_TOTAL_JUN' => array ( 'title' => 'Junho', 'abbr' => '' ),
                'IMPO_VL_TOTAL_JUL' => array ( 'title' => 'Julho', 'abbr' => '' ),
                'IMPO_VL_TOTAL_AGO' => array ( 'title' => 'Agosto', 'abbr' => '' ),
                'IMPO_VL_TOTAL_SET' => array ( 'title' => 'Setembro', 'abbr' => '' ),
                'IMPO_VL_TOTAL_OUT' => array ( 'title' => 'Outubro', 'abbr' => '' ),
                'IMPO_VL_TOTAL_NOV' => array ( 'title' => 'Novembro', 'abbr' => '' ),
                'IMPO_VL_TOTAL_DEZ' => array ( 'title' => 'Dezembro', 'abbr' => '' ),
                'IMPO_VL_TOTAL' => array ( 'title' => 'Total', 'abbr' => '' ), 
        );
        
        // Combina as opções num array
        $opcoes [ 'index' ] = $detalhes;
        $opcoes [ 'detalhes' ] = $detalhes;
        $opcoes [ 'controle' ] = $this->_negocio;
        $opcoes [ 'ocultos' ] = array ( 
            'IMPO_ID_IMPORT_ARQUIVO', 
            'IMPO_IC_CATEGORIA',
            'IMPO_CD_VINCULACAO',
            'IMPO_CD_UG_RESPONSAVEL',
            'IMPA_ID_IMPORT_ARQUIVO',
            'IMPA_DS_ARQUIVO',
            'IMPA_DH_IMPORTACAO',
            'IMPA_VL_RESP_IMPORTACAO',
            //'IMPA_AA_IMPORTACAO',
            'IMPA_IC_MES',
            'IMPA_IC_TP_ARQUIVO',

             );
        
        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCampos ( $acao = 'todos' )
    {
        // Campos para a serem apresentados na indexAction
        $campos [ 'todos' ] = " * ";
        
        // Campos para a serem apresentados na indexAction
        $campos [ 'index' ] = "
IMPO_ID_IMPORTACAO,
IMPA_AA_IMPORTACAO,
IMPO_CD_UG,
IMPO_CD_CONTA_CONTABIL,
IMPO_CD_RESULTADO_PRIMARIO,
IMPO_CD_ESFERA||' - '||ESFE_DS_ESFERA AS IMPO_CD_ESFERA,
IMPO_CD_PTRES||' - '||PTRS_DS_PT_RESUMIDO AS IMPO_CD_PTRES, 
IMPO_CD_NATUREZA_DESPESA||' - '||EDSB_DS_ELEMENTO_DESPESA_SUB AS IMPO_CD_NATUREZA_DESPESA,
IMPO_CD_FONTE||' - '||FONT_DS_FONTE AS IMPO_CD_FONTE,

TO_CHAR(IMPO_VL_TOTAL_JAN, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_JAN,
TO_CHAR(IMPO_VL_TOTAL_FEV, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_FEV,
TO_CHAR(IMPO_VL_TOTAL_MAR, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_MAR,
TO_CHAR(IMPO_VL_TOTAL_ABR, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_ABR,
TO_CHAR(IMPO_VL_TOTAL_MAI, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_MAI,
TO_CHAR(IMPO_VL_TOTAL_JUN, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_JUN,
TO_CHAR(IMPO_VL_TOTAL_JUL, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_JUL,
TO_CHAR(IMPO_VL_TOTAL_AGO, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_AGO,
TO_CHAR(IMPO_VL_TOTAL_SET, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_SET,
TO_CHAR(IMPO_VL_TOTAL_OUT, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_OUT,
TO_CHAR(IMPO_VL_TOTAL_NOV, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_NOV,
TO_CHAR(IMPO_VL_TOTAL_DEZ, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL_DEZ,
TO_CHAR(IMPO_VL_TOTAL, 'FML9G999G999G999G999G999G990D00') AS IMPO_VL_TOTAL
        ";
        
        // Campos para a serem apresentados na editarAction
        $campos [ 'editar' ] = "
IMPO_ID_IMPORTACAO,
IMPA_AA_IMPORTACAO,
IMPO_CD_UG,
IMPO_CD_CONTA_CONTABIL,
IMPO_CD_RESULTADO_PRIMARIO,
IMPO_CD_ESFERA,
IMPO_CD_PTRES||' - '||PTRS_DS_PT_RESUMIDO AS IMPO_CD_PTRES, 
IMPO_CD_NATUREZA_DESPESA||' - '||EDSB_DS_ELEMENTO_DESPESA_SUB AS IMPO_CD_NATUREZA_DESPESA,
IMPO_CD_FONTE,
IMPO_CD_NATUREZA_DESPESA,

IMPO_VL_TOTAL_JAN,
IMPO_VL_TOTAL_FEV,
IMPO_VL_TOTAL_MAR,
IMPO_VL_TOTAL_ABR,
IMPO_VL_TOTAL_MAI,
IMPO_VL_TOTAL_JUN,
IMPO_VL_TOTAL_JUL,
IMPO_VL_TOTAL_AGO,
IMPO_VL_TOTAL_SET,
IMPO_VL_TOTAL_OUT,
IMPO_VL_TOTAL_NOV,
IMPO_VL_TOTAL_DEZ,
IMPO_VL_TOTAL
        ";
        
        // Campos para a serem apresentados na detalheAction
        $campos [ 'detalhe' ] = "
                                    IMPO_ID_IMPORTACAO,
                                    IMPA_AA_IMPORTACAO,
                                    IMPO_CD_UG,
                                    IMPO_CD_UG||' - '||UNGE_DS_UG AS UNGE_DS_UG,
                                    IMPO_CD_CONTA_CONTABIL,
                                    IMPO_CD_RESULTADO_PRIMARIO,
IMPO_CD_ESFERA,
IMPO_CD_ESFERA||' - '||ESFE_DS_ESFERA AS ESFE_DS_ESFERA,
IMPO_CD_PTRES,
IMPO_CD_PTRES||' - '||PTRS_DS_PT_RESUMIDO AS PTRS_DS_PT_RESUMIDO, 
IMPO_CD_NATUREZA_DESPESA,
IMPO_CD_NATUREZA_DESPESA||' - '||EDSB_DS_ELEMENTO_DESPESA_SUB AS EDSB_DS_ELEMENTO_DESPESA_SUB,
IMPO_CD_FONTE,
IMPO_CD_FONTE||' - '||FONT_DS_FONTE AS FONT_DS_FONTE,
                                    IMPO_ID_ALINEA,
                                    ALIN_DS_ALINEA,
                                    IMPO_ID_INCISO,
                                    INCI_DS_INCISO,                                    
                                    IMPO_VL_TOTAL_JAN,
                                    IMPO_VL_TOTAL_FEV,
                                    IMPO_VL_TOTAL_MAR,
                                    IMPO_VL_TOTAL_ABR,
                                    IMPO_VL_TOTAL_MAI,
                                    IMPO_VL_TOTAL_JUN,
                                    IMPO_VL_TOTAL_JUL,
                                    IMPO_VL_TOTAL_AGO,
                                    IMPO_VL_TOTAL_SET,
                                    IMPO_VL_TOTAL_OUT,
                                    IMPO_VL_TOTAL_NOV,
                                    IMPO_VL_TOTAL_DEZ,
                                    IMPO_VL_TOTAL
                                ";
        
        // Campos para a serem apresentados na excluirAction
        $campos [ 'excluir' ] = "IMPO_ID_IMPORTACAO, ";
        $campos [ 'excluir' ] .= "
                                            IMPO_ID_IMPORTACAO AS \"Código\",
                                    IMPA_AA_IMPORTACAO AS \"Ano\",
                                    IMPO_CD_UG AS \"UG\",
                                    IMPO_CD_CONTA_CONTABIL AS \"Conta Contábil\",
                                    IMPO_CD_RESULTADO_PRIMARIO AS \"Resultado Primário\",
                                    IMPO_CD_FONTE AS \"Fonte\",
                                    IMPO_IC_CATEGORIA AS \"Categoria\",
                                    IMPO_CD_UG_RESPONSAVEL AS \"UG Responsavel\",
                                    IMPO_CD_VINCULACAO AS \"Vinculação\",
                                    IMPO_VL_TOTAL_JAN AS \"Janeiro\",
                                    IMPO_VL_TOTAL_FEV AS \"Fevereiro\",
                                    IMPO_VL_TOTAL_MAR AS \"Março\",
                                    IMPO_VL_TOTAL_ABR AS \"Abril\",
                                    IMPO_VL_TOTAL_MAI AS \"Maio\",
                                    IMPO_VL_TOTAL_JUN AS \"Junho\",
                                    IMPO_VL_TOTAL_JUL AS \"Julho\",
                                    IMPO_VL_TOTAL_AGO AS \"Agosto\",
                                    IMPO_VL_TOTAL_SET AS \"Setembro\",
                                    IMPO_VL_TOTAL_OUT AS \"Outubro\",
                                    IMPO_VL_TOTAL_NOV AS \"Novembro\",
                                    IMPO_VL_TOTAL_DEZ AS \"Dezembro\",
                                    IMPO_VL_TOTAL AS \"Total\"
                                    ";
        
        // Campos para a serem apresentados na restaurarAction
        $campos [ 'restaurar' ] = $campos [ 'todos' ] = " * ";
        
        // Campos para a serem apresentados num combo
        $campos [ 'combo' ] = $campos [ 'todos' ] = " * ";
        
        // Devolve os campos, conforme ação
        return $campos [ $acao ];
    }    

}