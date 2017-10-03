<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Contém as regras negociais sobre regra
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Gerarrelatoriocnj
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Gerarrelatoriocnj extends Orcamento_Business_Negocio_Base {

    protected $_model;

    const ANEXOI_RP = '10';
    const ANEXOI_ORCAMENTARIO = '3';
    const ANEXOI_FINANCEIRO = '1';
    const ANEXOI_INCISOS_EXCEL = '6';
    const ANEXOI_IDENTIFICACAO = '5';
    const ANEXO_1 = '1';
    const ANEXO_2 = '2';
    const ANEXO_HTML = '1';
    const ANEXO_EXCEL = '2';
    const MENSAGEM_081 = 'Não é possível incluir anos futuros as regras CNJ, apenas o ano corrente ou os passados.';
    const TIPO_RELATORIO = '1';

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {

        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Gerarrelatoriocnj();

        // Define a negocio
        $this->_negocio = 'gerarrelatoriocnj';
    }

    /**
     * Validação da regras utilizada na inclusão e edição das regras cnj
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function validacao($dados) {

        // verifica a regra 081
        $regra081 = $this->consultarRN081($dados);
        if (true === $regra081) {
            return self::MENSAGEM_081;
        }

        $this->gerarRelatorio($dados);

        // retorna ok para a validação e seguirá gravação
        return true;
    }

    /**
     * Efetua verificação da RN081
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function consultarRN081($dados) {

        // Data passada via dados
        $data = $dados['REGC_AA_REGRA'];

        $dataAtual = date("Y");

        if ($data <= $dataAtual) {
            return false;
        }

        return true;
    }

    /**
     * Retorna array com todos os valores de cache
     * 
     * @param int $ano
     * @param int $mes
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaVariacoesCacheID($ano, $mes) {

        $array = array();

        for ($anexo = 1; $anexo <= 2; $anexo++) {
            for ($formato = 1; $formato <= 2; $formato++) {
                if ($anexo != 2) {
                    for ($relatorio = 1; $relatorio <= 6; $relatorio++) {
                        if ($anexo == 1 && $formato == 2 && $relatorio == 5) {
                            continue;
                        }

                        $array[] = "relatoriocnj_{$ano}_{$mes}_{$anexo}_{$formato}_{$relatorio}";
                    }
                } else {
                    $array[] = "relatoriocnj_{$ano}_{$mes}_{$anexo}_{$formato}";
                }
            }
        }

        return $array;
    }

    /**
     * Retorna id do cache
     * 
     * @param array $dados
     * @return string
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarCacheID($dados) {

        $ano = $dados['ano'];
        $mes = $dados['mes'];
        $anexo = $dados['anexo'];
        $formato = $dados['formato'];
        $html = $dados['html'];
        $excel = $dados['excel'];

        if (!empty($html) || !empty($excel)) {
            $relatorio = "_" . (empty($html) ? $excel : $html);
        }

        if ($anexo == "1" && $formato == "2" && $relatorio == "_5") {
            return null;
        }

        return "relatoriocnj_{$ano}_{$mes}_{$anexo}_{$formato}{$relatorio}";
    }

    /**
     * Gera relatório do anexo I ou II
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function gerarRelatorio($dados) {

        $array = array();

        $array['ano'] = $dados['REGC_AA_REGRA'];
        $array['mes'] = $dados['IMPA_IC_MES'];
        $array['anexo'] = $dados['TIPO_ANEXO'];
        $array['formato'] = $dados['TIPO_RELATORIO'];
        $array['html'] = $dados['TIPO_ANEXO_HTML'];
        $array['excel'] = $dados['TIPO_ANEXO_EXCEL'];

        if ($array['anexo'] == self::ANEXO_1 && $array['formato'] == self::ANEXO_EXCEL) {
            $array['ug'] = false;
        } else {
            $array['ug'] = $dados['UG_TODAS'] == 1 ? false : $dados['UNIDADE_GESTORA'];
        }

        $manipular = new Orcamento_Business_Negocio_RelatorioCNJ_ManipularDados();

        if ($array['anexo'] == self::ANEXO_1) {

            if ($array['formato'] == self::ANEXO_HTML) {
                switch ($array['html']) {
                    case self::ANEXOI_RP:
                        $array['tipo'] = Orcamento_Business_Importacao_Base::ARQUIVO_RESTOSAPAGAR;
                        break;
                    case self::ANEXOI_ORCAMENTARIO:
                        $array['tipo'] = Orcamento_Business_Importacao_Base::ARQUIVO_LIQUIDADO;
                        break;
                    case self::ANEXOI_FINANCEIRO:
                        $array['tipo'] = Orcamento_Business_Importacao_Base::ARQUIVO_FINANCEIRO;
                        break;
                }
            }


            $matriz = $manipular->manipularAnexoI($array);
        } elseif ($array['anexo'] == self::ANEXO_2) {
            // anexo 2
            // $matriz = $manipular->manipularAnexoII($array);

            $ano = $dados['REGC_AA_REGRA'];
            $mes = $dados['IMPA_IC_MES'];

            if ($dados['UG_TODAS'] == 0) {
                $filtroUg = "AND IMPO_CD_UG = " . $dados['UNIDADE_GESTORA'];
            }

            $sql = "
        SELECT DISTINCT

        CASE WHEN SUBSTR (PTRS_CD_PT_COMPLETO, 7,7) IS NULL
            THEN '---'
            ELSE SUBSTR (PTRS_CD_PT_COMPLETO, 6,13)
        END AS FUNCIONAL_PROGRAMATICA,
         
         CASE WHEN ptrs_ds_pt_resumido IS NULL 
             THEN IMPO_CD_PTRES ||' - [descrição não encontrada]'
             ELSE ptrs_ds_pt_resumido
         END AS PROGRAMA_ACAO,

         CASE WHEN PTRS_CD_PT_COMPLETO IS NOT NULL
             THEN SUBSTR(PTRS_CD_PT_COMPLETO, 1,5)
             ELSE '---'
         END AS FUNCAO_SUBFUNCAO,
                 
         IMPO_CD_ESFERA,
        SUBSTR(IMPO_CD_NATUREZA_DESPESA, 1,1) AS GND,                                
        IMPO_CD_FONTE,
        IMPO_CD_PTRES,
        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 4
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS DOTACAO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 2
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS SUPLEMENTACAO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 5
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS CANCELAMENTO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 6
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS CONTINGENCIAMENTO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 4
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS DOTACAO_AUTORIAZADA,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 7
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS PROVISAO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 8
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS DESTAQUE,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 9
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS EMPENHADO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = 2015        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 3
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS LIQUIDADO,        
    
        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = 2015        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 10
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS PAGO,
    
        UNGE_SG_SECAO,
        UNGE_SG_UG,
        UNGE_DS_UG,                
        IMPO_CD_UG,
        IMPO_CD_PTRES,
        PTRS_CD_PT_COMPLETO,
        PTRS_DS_PROGRAMA_ACAO
        --IMPO_CD_NATUREZA_DESPESA

        FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO PIMPA

        LEFT JOIN CEO_TB_IMPO_IMPORTACAO PIMPO
                ON IMPA_ID_IMPORT_ARQUIVO = IMPO_ID_IMPORT_ARQUIVO

        LEFT JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO
                ON PTRS_CD_PT_RESUMIDO = IMPO_CD_PTRES

        LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA
                ON UNGE_CD_UG = IMPO_CD_UG

        INNER JOIN CEO_TB_REGC_REGRA_CNJ R 
        ON impo_cd_natureza_despesa BETWEEN 
            R.regc_vl_natureza_desp_inicial and R.regc_vl_natureza_desp_final

        WHERE
            -- ptrs_cd_pt_completo like '%56909HB%' AND
            
            IMPA_AA_IMPORTACAO = $ano
            $filtroUg
            
            -- AND IMPO_CD_PTRES = 96962 filtroptresOff

            AND IMPO_IC_CATEGORIA IS NULL
            AND  UNGE_CD_UG IS NOT NULL 

            AND IMPO_CD_FONTE IS NOT NULL

        GROUP BY
            IMPO_CD_FONTE,
            IMPO_CD_PTRES,
            ptrs_ds_pt_resumido,
            IMPO_CD_NATUREZA_DESPESA,
            UNGE_DS_UG,
            IMPO_CD_ESFERA,
            UNGE_SG_SECAO,
            UNGE_SG_UG,                
            IMPO_CD_UG,
            IMPO_CD_PTRES,
            PTRS_CD_PT_COMPLETO,
            PTRS_DS_PROGRAMA_ACAO                                   
        ORDER BY
            UNGE_SG_SECAO,
            UNGE_SG_UG
            ";

            $db = Zend_Db_Table::getDefaultAdapter();

            $resultado = $db->fetchAll($sql);

            if (empty($resultado)) {
                $matriz = $mes . "/" . $ano;
            } else {

                $matriz = array();
                foreach ($resultado as $value) {
                    $matriz[$value['UNGE_SG_SECAO']][] = $value;
                }
            }
        }


        return $matriz;
    }

    /**
     * Consulta dados para o Anexo I em HTML
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarDadosAnexoIHtml($dados) {

        $ano = $dados['ano'];

        $campo = $dados['mes'];

        // $mes = $dados['mes'];
        switch ($dados['mes']) {
            case 'IMPO_VL_TOTAL_JAN':
                $mes = '01';
                break;
            case 'IMPO_VL_TOTAL_FEV':
                $mes = '02';
                break;
            case 'IMPO_VL_TOTAL_MAR':
                $mes = '03';
                break;
            case 'IMPO_VL_TOTAL_ABR':
                $mes = '04';
                break;
            case 'IMPO_VL_TOTAL_MAI':
                $mes = '05';
                break;
            case 'IMPO_VL_TOTAL_JUN':
                $mes = '06';
                break;
            case 'IMPO_VL_TOTAL_JUL':
                $mes = '07';
                break;
            case 'IMPO_VL_TOTAL_AGO':
                $mes = '08';
                break;
            case 'IMPO_VL_TOTAL_SET':
                $mes = '09';
                break;
            case 'IMPO_VL_TOTAL_OUT':
                $mes = '10';
                break;
            case 'IMPO_VL_TOTAL_NOV':
                $mes = '11';
                break;
            case 'IMPO_VL_TOTAL_DEZ':
                $mes = '12';
                break;
        }


        $tipo = $dados['tipo'];

        $ugMostrar = $dados['ug'] === false ? "" : " AND IMPO_CD_UG = {$dados['ug']}";

        if (!empty($tipo)) {
            $sqlTipo = " AND IMPA_IC_TP_ARQUIVO = {$tipo} ";
        }
        
        $ugMostrar = $dados['ug'] === false ? "" : " AND IMPO_CD_UG = {$dados['ug']}";
        
        $sql = $this->retornaSqlRelatorio ( $tipo, $dados );

        
        
        
/*        $sql = "


SELECT ALIN_ID_ALINEA, INCI_ID_INCISO,ALIN_VL_ALINEA, ALIN_DS_ALINEA, INCI_VL_INCISO, INCI_DS_INCISO, 
UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG,
SUM($campo) AS TOTAL
FROM
(SELECT C.ALIN_ID_ALINEA, D.INCI_ID_INCISO, C.ALIN_VL_ALINEA, C.ALIN_DS_ALINEA, D.INCI_VL_INCISO, D.INCI_DS_INCISO,
UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG,
     A.$campo
       
  FROM CEO.CEO_TB_IMPO_IMPORTACAO A
  INNER JOIN CEO.CEO_TB_REGC_REGRA_CNJ B ON A.IMPO_IC_CATEGORIA = B.REGC_IC_CATEGORIA
  INNER JOIN CEO.CEO_TB_ALIN_ALINEA C ON B.REGC_ID_ALINEA = C.ALIN_ID_ALINEA
  INNER JOIN CEO.CEO_TB_INCI_INCISO D ON C.ALIN_ID_INCISO = D.INCI_ID_INCISO
  INNER JOIN CEO.CEO_TB_IMPA_IMPORTAR_ARQUIVO E ON A.IMPO_ID_IMPORT_ARQUIVO = E.IMPA_ID_IMPORT_ARQUIVO
  INNER JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON A.IMPO_CD_UG = UNGE.UNGE_CD_UG     
 
  WHERE 
  1= 1
  $sqlTipo
  AND A.$campo <> 0
  AND A.IMPO_ID_ALINEA IS NULL 
  AND A.IMPO_ID_INCISO IS NULL
  $ugMostrar
  UNION
     SELECT C.ALIN_ID_ALINEA, D.INCI_ID_INCISO, C.ALIN_VL_ALINEA, C.ALIN_DS_ALINEA, D.INCI_VL_INCISO, D.INCI_DS_INCISO,
     UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG,
        A.$campo
       
  FROM CEO.CEO_TB_IMPO_IMPORTACAO A
  INNER JOIN CEO.CEO_TB_ALIN_ALINEA C ON C.ALIN_ID_ALINEA = A.IMPO_ID_ALINEA
  INNER JOIN CEO.CEO_TB_INCI_INCISO D ON D.INCI_ID_INCISO = A.IMPO_ID_INCISO
  INNER JOIN CEO.CEO_TB_IMPA_IMPORTAR_ARQUIVO E ON A.IMPO_ID_IMPORT_ARQUIVO = E.IMPA_ID_IMPORT_ARQUIVO
  INNER JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON A.IMPO_CD_UG = UNGE.UNGE_CD_UG     
  WHERE 
  1= 1
  $sqlTipo
  AND A.$campo <> 0
  $ugMostrar)
  GROUP BY ALIN_ID_ALINEA, INCI_ID_INCISO, ALIN_VL_ALINEA, ALIN_DS_ALINEA, INCI_VL_INCISO, INCI_DS_INCISO,UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG
  ORDER BY 1,3 
        ";
*/


        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchAll($sql);

        return $retorno;
    }

    /**
     * Consulta dados para o Anexo I em Excel
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarDadosAnexoIExcel($dados) {

        $ano = $dados['ano'];
        $campo = $dados['mes'];

        $ignorarUG = array(90032, 90009, 90000);
        $implodeUG = implode(', ', $ignorarUG);

        // $mes = $dados['mes'];
        switch ($dados['mes']) {
            case 'IMPO_VL_TOTAL_JAN':
                $mes = '01';
                break;
            case 'IMPO_VL_TOTAL_FEV':
                $mes = '02';
                break;
            case 'IMPO_VL_TOTAL_MAR':
                $mes = '03';
                break;
            case 'IMPO_VL_TOTAL_ABR':
                $mes = '04';
                break;
            case 'IMPO_VL_TOTAL_MAI':
                $mes = '05';
                break;
            case 'IMPO_VL_TOTAL_JUN':
                $mes = '06';
                break;
            case 'IMPO_VL_TOTAL_JUL':
                $mes = '07';
                break;
            case 'IMPO_VL_TOTAL_AGO':
                $mes = '08';
                break;
            case 'IMPO_VL_TOTAL_SET':
                $mes = '09';
                break;
            case 'IMPO_VL_TOTAL_OUT':
                $mes = '10';
                break;
            case 'IMPO_VL_TOTAL_NOV':
                $mes = '11';
                break;
            case 'IMPO_VL_TOTAL_DEZ':
                $mes = '12';
                break;
        }

        $sql = "

        SELECT
            INCI_ID_INCISO, INCI_VL_INCISO, INCI_DS_INCISO,
            ALIN_ID_ALINEA, ALIN_VL_ALINEA, ALIN_DS_ALINEA,
            SUM($campo) AS TOTAL

        FROM CEO_TB_INCI_INCISO

        INNER JOIN CEO_TB_ALIN_ALINEA ON INCI_ID_INCISO = ALIN_ID_INCISO

        LEFT JOIN CEO_TB_REGC_REGRA_CNJ ON REGC_ID_ALINEA = ALIN_ID_ALINEA

        LEFT JOIN CEO_TB_IMPO_IMPORTACAO
                ON REGC_ID_ALINEA = ALIN_ID_ALINEA
                AND IMPO_CD_UG NOT IN ({$implodeUG})

        LEFT JOIN CEO_TB_IMPA_IMPORTAR_ARQUIVO
            ON IMPA_ID_IMPORT_ARQUIVO = IMPO_ID_IMPORT_ARQUIVO
            --AND IMPA_IC_MES = {$mes} 
            AND IMPA_AA_IMPORTACAO = {$ano}
            AND REGC_AA_REGRA = IMPA_AA_IMPORTACAO
            
        GROUP BY
            INCI_ID_INCISO, INCI_VL_INCISO, INCI_DS_INCISO,
            ALIN_ID_ALINEA, ALIN_VL_ALINEA, ALIN_DS_ALINEA
        ";

        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchAll($sql);

        return $retorno;
    }

    /**
     * Consulta dados para o Anexo II em HTML
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarDadosAnexoIIExcel($dados) {

        $ano = $dados['REGC_AA_REGRA'];
        $mes = $dados['IMPA_IC_MES'];

        $sql = "

        SELECT DISTINCT
        
        CASE WHEN SUBSTR (PTRS_CD_PT_COMPLETO, 7,7) IS NULL
            THEN '---'
            ELSE SUBSTR (PTRS_CD_PT_COMPLETO, 6,13)
        END AS FUNCIONAL_PROGRAMATICA,
         
         CASE WHEN ptrs_ds_pt_resumido IS NULL 
             THEN IMPO_CD_PTRES ||' - [descrição não encontrada]'
             ELSE ptrs_ds_pt_resumido
         END AS PROGRAMA_ACAO,

         CASE WHEN PTRS_CD_PT_COMPLETO IS NOT NULL
             THEN SUBSTR(PTRS_CD_PT_COMPLETO, 1,5)
             ELSE '---'
         END AS FUNCAO_SUBFUNCAO,
                 
         IMPO_CD_ESFERA,
        SUBSTR(IMPO_CD_NATUREZA_DESPESA, 1,1) AS GND,                                
        IMPO_CD_FONTE,
        IMPO_CD_PTRES,
        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 4
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS DOTACAO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 2
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS SUPLEMENTACAO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 5
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS CANCELAMENTO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 6
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS CONTINGENCIAMENTO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 4
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS DOTACAO_AUTORIAZADA,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 7
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS PROVISAO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 8
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS DESTAQUE,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = $ano        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 9
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS EMPENHADO,

        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = 2015        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 3
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS LIQUIDADO,        
    
        (
            SELECT
                SUM($mes) as IMPO_VL_TOTAL
            FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA
                LEFT JOIN CEO_TB_IMPO_IMPORTACAO IMPO
                    ON IMPA.IMPA_ID_IMPORT_ARQUIVO = IMPO.IMPO_ID_IMPORT_ARQUIVO
            WHERE 
                IMPA.IMPA_AA_IMPORTACAO = 2015        
                AND IMPA.IMPA_IC_TP_ARQUIVO = 10
                AND IMPO.IMPO_CD_PTRES = PIMPO.IMPO_CD_PTRES
                AND IMPO.IMPO_CD_ESFERA = PIMPO.IMPO_CD_ESFERA
                AND IMPO.IMPO_CD_FONTE = PIMPO.IMPO_CD_FONTE                
        ) AS PAGO,
    
        UNGE_SG_SECAO,
        UNGE_SG_UG,                
        IMPO_CD_UG,
        IMPO_CD_PTRES,
        PTRS_CD_PT_COMPLETO,
        PTRS_DS_PROGRAMA_ACAO
        --IMPO_CD_NATUREZA_DESPESA

        FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO PIMPA

        LEFT JOIN CEO_TB_IMPO_IMPORTACAO PIMPO
                ON IMPA_ID_IMPORT_ARQUIVO = IMPO_ID_IMPORT_ARQUIVO

        LEFT JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO
                ON PTRS_CD_PT_RESUMIDO = IMPO_CD_PTRES

        LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA
                ON UNGE_CD_UG = IMPO_CD_UG

        INNER JOIN CEO_TB_REGC_REGRA_CNJ R 
        ON impo_cd_natureza_despesa BETWEEN 
            R.regc_vl_natureza_desp_inicial and R.regc_vl_natureza_desp_final

        WHERE
            -- ptrs_cd_pt_completo like '%56909HB%' AND
            
            IMPA_AA_IMPORTACAO = $ano
            
            $filtroUg
            
            -- AND IMPO_CD_PTRES = 96962 filtroptresOff

            AND IMPO_IC_CATEGORIA IS NULL
            AND  UNGE_CD_UG IS NOT NULL 
            AND IMPO_CD_FONTE IS NOT NULL

        GROUP BY
            IMPO_CD_FONTE,
            IMPO_CD_PTRES,
            ptrs_ds_pt_resumido,
            IMPO_CD_NATUREZA_DESPESA,
            IMPO_CD_ESFERA,
            UNGE_SG_SECAO,
            UNGE_SG_UG,                
            IMPO_CD_UG,
            IMPO_CD_PTRES,
            PTRS_CD_PT_COMPLETO,
            PTRS_DS_PROGRAMA_ACAO                                   
        ORDER BY
            UNGE_SG_SECAO,
            UNGE_SG_UG

        ";

        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchAll($sql);
        return $retorno;
    }

    /**
     * Consultar dados para o Anexo II em Excel
     * 
     * @param array $dados
     * @param string $ug
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarDadosAnexoIIHtml($dados, $ug = "") {

        $sqlUG = "";
        $groupBy = "";
        $ano = (int) $dados['ano'];
        $campo = $dados['mes'];
        $ugMostrar = $dados['ug'] === "" ? "" : " AND IMPO_CD_UG = {$dados['ug']}";

        if (empty($ug)) {

            $camposSelect = " 
                UNGE_CD_UG,
                UNGE_SG_SECAO,
                UNGE_SG_UG            
            ";

            $orderBy = "UNGE_SG_SECAO";

            $groupBy = " 
                GROUP BY
                {$camposSelect}
            ";
        } else {
            $sqlUG = " AND IMPO_CD_UG = {$ug}";

            $camposSelect = "
                UNGE_CD_UG,
                UNGE_SG_SECAO,
                UNGE_SG_UG,
                IMPO_CD_ESFERA,
                IMPO_CD_UG,
                IMPO_CD_PTRES,
                PTRS_CD_PT_COMPLETO,
                PTRS_DS_PROGRAMA_ACAO,
                IMPO_CD_NATUREZA_DESPESA,
                IMPO_CD_FONTE
            ";

            $groupBy = " 
                GROUP BY
                {$camposSelect}
            ";

            $orderBy = " 
                UNGE_SG_SECAO, 
                IMPO_CD_PTRES,
                IMPO_CD_ESFERA,
                IMPO_CD_FONTE,
                IMPO_CD_NATUREZA_DESPESA
            ";
        }

        $sql = " 

        SELECT
            {$camposSelect}

        FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO

        LEFT JOIN CEO_TB_IMPO_IMPORTACAO
                ON IMPA_ID_IMPORT_ARQUIVO = IMPO_ID_IMPORT_ARQUIVO

        LEFT JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO
                ON PTRS_CD_PT_RESUMIDO = IMPO_CD_PTRES

        LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA
                ON UNGE_CD_UG = IMPO_CD_UG

        WHERE
            1 = 1 
        
            {$groupBy}
            
        ORDER BY

            {$orderBy}

        ";


        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchAll($sql);

        return $retorno;
    }

    /**
     * Consultar dados para o Anexo II em Excel
     * 
     * @param array $dados
     * @param string $ug
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function driver($dados, $ug = "") {

        $sqlUG = "";
        $groupBy = "";
        $ano = (int) $dados['ano'];
        $campo = $dados['mes'];
        $ugMostrar = $dados['ug'] === false ? "" : " AND IMPO_CD_UG = {$dados['ug']}";

        if (empty($ug)) {

            $camposSelect = " 
                UNGE_CD_UG,
                UNGE_SG_SECAO,
                UNGE_SG_UG            
            ";

            $orderBy = "UNGE_SG_SECAO";

            $groupBy = " 
                GROUP BY
                {$camposSelect}
            ";
        } else {
            $sqlUG = " AND IMPO_CD_UG = {$ug}";

            $camposSelect = "
                UNGE_CD_UG,
                UNGE_SG_SECAO,
                UNGE_SG_UG,
                IMPO_CD_ESFERA,
                IMPO_CD_UG,
                IMPO_CD_PTRES,
                PTRS_CD_PT_COMPLETO,
                PTRS_DS_PROGRAMA_ACAO,
                IMPO_CD_NATUREZA_DESPESA,
                IMPO_CD_FONTE
            ";

            $groupBy = " 
                GROUP BY
                {$camposSelect}
            ";

            $orderBy = " 
                UNGE_SG_SECAO, 
                IMPO_CD_PTRES,
                IMPO_CD_ESFERA,
                IMPO_CD_FONTE,
                IMPO_CD_NATUREZA_DESPESA
            ";
        }

        $sql = " 

        SELECT
            {$camposSelect}
            
            -- ,SUM($campo) AS TOTAL

        FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO

        LEFT JOIN CEO_TB_IMPO_IMPORTACAO
                ON IMPA_ID_IMPORT_ARQUIVO = IMPO_ID_IMPORT_ARQUIVO

        LEFT JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO
                ON PTRS_CD_PT_RESUMIDO = IMPO_CD_PTRES

        LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA
                ON UNGE_CD_UG = IMPO_CD_UG

        WHERE
            --IMPA_IC_MES = {$mes} AND
            
            IMPA_AA_IMPORTACAO = {$ano}
            
            {$sqlUG}
            
            AND IMPO_IC_CATEGORIA IS NULL
            AND  UNGE_CD_UG IS NOT NULL 
            
            {$ugMostrar}
        
            {$groupBy}
            
        ORDER BY

            {$orderBy}

        ";

        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchAll($sql);

        return $retorno;
    }

    /**
     * Consultar dados por PTRES
     * 
     * @param array $dados
     * @param bool $ignorarUG
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function consultarPorPTRES($dados, $ignorarUG = array(90032, 90009, 90000)) {

        $sqlIgnorarUg = "";

        if (count((array) $ignorarUG) > 0) {
            $implodeUG = implode(', ', $ignorarUG);
            $sqlIgnorarUg = " AND IMPO_CD_UG NOT IN ({$implodeUG})";
        }

        $ano = "20" . $dados['ano'];
        $mes = $dados['mes'];
        $ptres = $dados['ptres'];
        $esfera = $dados['esfera'];
        $fonte = $dados['fonte'];
        $tipo = $dados['tipo'];
        $ug = $dados['ug'];



        if (!empty($ug)) {
            $sqlUG = " -- AND IMPO_CD_UG = {$ug}";
        }

        $sql = " 

    SELECT
	
    SUM({$mes}) IMPO_VL_TOTAL

    FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO

    LEFT JOIN CEO_TB_IMPO_IMPORTACAO
            ON IMPA_ID_IMPORT_ARQUIVO = IMPO_ID_IMPORT_ARQUIVO

    WHERE
        
        --IMPA_IC_MES = {$mes} AND
        IMPA_AA_IMPORTACAO = {$ano}        
        AND IMPO_CD_PTRES = {$ptres}
        AND IMPO_CD_ESFERA = {$esfera}
        AND IMPO_CD_FONTE = {$fonte}
        AND IMPA_IC_TP_ARQUIVO = {$tipo}
        AND IMPO_IC_CATEGORIA IS NULL
        {$sqlUG}

        ";


        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchOne($sql);

        return $retorno;
    }

    /**
     * Retorna combo
     *
     * @return	array
     * @author	Sandro Maceno <smaceno@stefanini.com>
     */
    public function retornaComboUG() {
        $sql = "
            SELECT
                DISTINCT UNGE_CD_UG,
                CONCAT(CONCAT(UNGE_SG_UG,' - '),UNGE_DS_UG) AS UNGE_DS_UG
            FROM CEO_TB_UNGE_UNIDADE_GESTORA
            ORDER BY UNGE_DS_UG";

        $banco = Zend_Db_Table::getDefaultAdapter();
        $dados = $banco->fetchPairs($sql);

        return $dados;
    }

    public function retornaSqlRelatorio( $tipo , $param) {
        
        switch ($tipo) {
            case $tipo == Orcamento_Business_Importacao_Base::ARQUIVO_RESTOSAPAGAR:
                $sql = $this->retornaSqlRestosaPagarLiquidado( $param );
                break;
            case $tipo == Orcamento_Business_Importacao_Base::ARQUIVO_LIQUIDADO:
                $sql = $this->retornaSqlRestosaPagarLiquidado( $param );
                break;
            case $tipo == Orcamento_Business_Importacao_Base::ARQUIVO_FINANCEIRO:
                $sql = $this->retornaSqlFinanceiro( $param );
                break;
        }
        
        return $sql;
    }

    public function retornaSqlRestosaPagarLiquidado($param) {
        
        $ugMostrar = $param['ug'] === false ? "" : " AND IMPO.IMPO_CD_UG = {$param['ug']}";
        $tipo = $param['tipo'];
        $mesvl = $param['mes'];
        $anovl = $param['ano'];
        
        $sql = "
                SELECT  

                    ALIN.ALIN_ID_ALINEA, INCI.INCI_ID_INCISO, ALIN.ALIN_VL_ALINEA, ALIN.ALIN_DS_ALINEA, INCI.INCI_VL_INCISO, INCI.INCI_DS_INCISO, 
                    UNGE.UNGE_CD_UG, UNGE.UNGE_SG_SECAO, UNGE.UNGE_SG_UG, UNGE.UNGE_DS_AUTORIDADE_MAXIMA, UNGE.UNGE_DS_UG
                    ,SUM( IMPO.$mesvl ) AS TOTAL


                from CEO.CEO_TB_REGC_REGRA_CNJ REGC

                  JOIN CEO_TB_IMPO_IMPORTACAO IMPO 
                    ON IMPO.IMPO_CD_NATUREZA_DESPESA IN REGC.REGC_VL_NATUREZA_DESP_INICIAL OR IMPO.IMPO_CD_NATUREZA_DESPESA IN REGC.REGC_VL_NATUREZA_DESP_FINAL

                  LEFT JOIN CEO.CEO_TB_IMPA_IMPORTAR_ARQUIVO IMPA 
                    ON IMPO.IMPO_ID_IMPORT_ARQUIVO = IMPA.IMPA_ID_IMPORT_ARQUIVO

                  LEFT JOIN CEO.CEO_TB_ALIN_ALINEA ALIN 
                    ON REGC.REGC_ID_ALINEA = ALIN.ALIN_ID_ALINEA

                  LEFT JOIN CEO.CEO_TB_INCI_INCISO INCI
                    ON ALIN.ALIN_ID_INCISO = INCI.INCI_ID_INCISO  

                  LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE 
                    ON IMPO.IMPO_CD_UG = UNGE.UNGE_CD_UG     

                WHERE  
                -- FILTRO MES
                      IMPA.IMPA_IC_TP_ARQUIVO = $tipo
                -- FILTRO ANO
                      AND IMPA.IMPA_AA_IMPORTACAO = $anovl                      
                -- FILTRO VALOR > 0
                -- AND IMPO.IMPO_VL_TOTAL_DEZ <> 0

                -- FILTRO ALINIA E INCISO 
                -- AND A.IMPO_ID_ALINEA IS NULL 
                -- AND A.IMPO_ID_INCISO IS NULL

                -- FILTRO UG
                   $ugMostrar

                GROUP BY 
                  ALIN_ID_ALINEA, INCI_ID_INCISO, ALIN_VL_ALINEA, ALIN_DS_ALINEA, INCI_VL_INCISO, INCI_DS_INCISO,UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG

                ORDER BY 1,3

            ";

        return $sql;
    }

    public function retornaSqlFinanceiro($param) {
        
        $ugMostrar = $param['ug'] === false ? "" : " AND A.IMPO_CD_UG = {$param['ug']}";
        $campo = $param["mes"];
        $tipo = $param['tipo'];
        $mesvl = $param['mes'];
        $anovl = $param['ano'];        
  
        $sql = " 
            SELECT ALIN_ID_ALINEA, INCI_ID_INCISO,ALIN_VL_ALINEA, ALIN_DS_ALINEA, INCI_VL_INCISO, INCI_DS_INCISO, 
                UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG,
                SUM($campo) AS TOTAL
                FROM
                (SELECT C.ALIN_ID_ALINEA, D.INCI_ID_INCISO, C.ALIN_VL_ALINEA, C.ALIN_DS_ALINEA, D.INCI_VL_INCISO, D.INCI_DS_INCISO,
                UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG,
                     A.$campo

                  FROM CEO.CEO_TB_IMPO_IMPORTACAO A
                  INNER JOIN CEO.CEO_TB_REGC_REGRA_CNJ B ON A.IMPO_IC_CATEGORIA = B.REGC_IC_CATEGORIA
                  INNER JOIN CEO.CEO_TB_ALIN_ALINEA C ON B.REGC_ID_ALINEA = C.ALIN_ID_ALINEA
                  INNER JOIN CEO.CEO_TB_INCI_INCISO D ON C.ALIN_ID_INCISO = D.INCI_ID_INCISO
                  INNER JOIN CEO.CEO_TB_IMPA_IMPORTAR_ARQUIVO E ON A.IMPO_ID_IMPORT_ARQUIVO = E.IMPA_ID_IMPORT_ARQUIVO
                  INNER JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON A.IMPO_CD_UG = UNGE.UNGE_CD_UG     

                  WHERE 
                  1= 1
                  $sqlTipo
                  AND A.$campo <> 0
                  AND A.IMPO_ID_ALINEA IS NULL 
                  AND A.IMPO_ID_INCISO IS NULL
                  $ugMostrar
                  UNION
                     SELECT C.ALIN_ID_ALINEA, D.INCI_ID_INCISO, C.ALIN_VL_ALINEA, C.ALIN_DS_ALINEA, D.INCI_VL_INCISO, D.INCI_DS_INCISO,
                     UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG,
                        A.$campo

                  FROM CEO.CEO_TB_IMPO_IMPORTACAO A
                  INNER JOIN CEO.CEO_TB_ALIN_ALINEA C ON C.ALIN_ID_ALINEA = A.IMPO_ID_ALINEA
                  INNER JOIN CEO.CEO_TB_INCI_INCISO D ON D.INCI_ID_INCISO = A.IMPO_ID_INCISO
                  INNER JOIN CEO.CEO_TB_IMPA_IMPORTAR_ARQUIVO E ON A.IMPO_ID_IMPORT_ARQUIVO = E.IMPA_ID_IMPORT_ARQUIVO
                  INNER JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON A.IMPO_CD_UG = UNGE.UNGE_CD_UG     
                  WHERE 
                  1= 1
                  $sqlTipo
                  AND A.$campo <> 0
                  $ugMostrar)
                  GROUP BY ALIN_ID_ALINEA, INCI_ID_INCISO, ALIN_VL_ALINEA, ALIN_DS_ALINEA, INCI_VL_INCISO, INCI_DS_INCISO,UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG
                  ORDER BY 1,3
                            ";
        
//        $sql = "
//                            
//            SELECT
//            ALIN_ID_ALINEA, INCI_ID_INCISO,ALIN_VL_ALINEA, ALIN_DS_ALINEA, INCI_VL_INCISO, INCI_DS_INCISO, 
//            UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG
//            ,SUM( A.$mesvl ) AS TOTAL
//
//            FROM CEO.CEO_TB_IMPO_IMPORTACAO A
//                LEFT JOIN CEO.CEO_TB_ALIN_ALINEA C ON C.ALIN_ID_ALINEA = A.IMPO_ID_ALINEA
//                LEFT JOIN CEO.CEO_TB_INCI_INCISO D ON D.INCI_ID_INCISO = A.IMPO_ID_INCISO
//                LEFT JOIN CEO.CEO_TB_IMPA_IMPORTAR_ARQUIVO E ON A.IMPO_ID_IMPORT_ARQUIVO = E.IMPA_ID_IMPORT_ARQUIVO
//                LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON A.IMPO_CD_UG = UNGE.UNGE_CD_UG     
//            WHERE 
//            1= 1
//            -- FILTRO TIPO
//            AND E.IMPA_IC_TP_ARQUIVO = $tipo
//            -- FILTRO ANO
//            AND E.IMPA_AA_IMPORTACAO = $anovl      
//            -- FILTRO UG
//            $ugMostrar
//
//            GROUP BY ALIN_ID_ALINEA, INCI_ID_INCISO, ALIN_VL_ALINEA, ALIN_DS_ALINEA, INCI_VL_INCISO, INCI_DS_INCISO,UNGE_CD_UG, UNGE_SG_SECAO, UNGE_SG_UG, UNGE_DS_AUTORIDADE_MAXIMA, UNGE_DS_UG
//            ORDER BY 1,3 
//            ";
        
        return $sql;
    }
}
