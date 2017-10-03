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
 * @package Orcamento_Business_Negocio_ImportararrayPadrao
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_ImpaImportar extends Orcamento_Business_Importacao_Base {

    /**
     * Carrega variaveis para serem usadas na business
     */
    public function init() {
        
        // Define a negocio
        $this->_negocio = Zend_Controller_Front::getInstance()->getRequest()
                ->getControllerName();

        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_ImportarArquivo ();
        
        // Define a negocio
        $this->_negocio = 'impaimportar';

    }

    /**
     * Trata os dados para inclusão na tabela impa
     * 
     * @param string $nome
     * @param int $ano
     * @param int $tipo
     * @return array
     */
    public function incluirDados( $nome, $ano, $tipo )
    {
        
        $userNs = new Zend_Session_Namespace('userNs');
        $responsavel = $userNs->nome;

        $arrayImportacao = array(
            'IMPA_DS_ARQUIVO' => $nome,            
            'IMPA_DH_IMPORTACAO' => new Zend_Db_Expr('SYSDATE'),
            'IMPA_VL_RESP_IMPORTACAO' => $responsavel,
            'IMPA_AA_IMPORTACAO' => $ano,
            'IMPA_IC_MES' => 1, // mes não é mais utilziado
            'IMPA_IC_TP_ARQUIVO' => $tipo // arrayPadrao
        );
        
        return parent::incluir($arrayImportacao);
    }

    /**
     * Inclusão manual de registros
     * 
     * @param array $dados
     * @param int $tipo
     * @param int $ano
     * @return array
     */
    public function incluirManual($dados, $tipo, $ano)
    {
        
        // trata os textos de ptres e natureza da despesa s
        $dados['IMPO_CD_PTRES'] =  trim(substr($dados['IMPO_CD_PTRES'], 0,6));
        $pos = strpos($dados['IMPO_CD_NATUREZA_DESPESA'], "-") - 1;
        $dados['IMPO_CD_NATUREZA_DESPESA'] =  trim(substr($dados['IMPO_CD_NATUREZA_DESPESA'], 0,$pos));
        
        unset($dados['["Enviar"]']);
        
        //verificaDuplicidade
        $existe = $this->verificaDuplicidade(
                                                $dados['IMPO_CD_CONTA_CONTABIL'],  
                                                $dados['IMPO_CD_PTRES'], 
                                                $dados['IMPO_CD_NATUREZA_DESPESA'], 
                                                $dados['IMPO_CD_FONTE'], 
                                                $dados['IMPO_CD_ESFERA'], 
                                                $dados['IMPO_CD_UG'], 
                                                $dados['IMPO_CD_RESULTADO_PRIMARIO'], 
                                                null,
                                                null,
                                                null,
                                                $ano, 
                                                $tipo,
                                                null
                                            );
        if( count($existe) > 0 ){
           return false; 
        }
        
        $valor = new Trf1_Orcamento_Valor() ;
        // Busca perfil
        $userNs = new Zend_Session_Namespace('userNs');
        $responsavel = $userNs->nome;

        $arrayImportacao = array(
            'IMPA_DS_ARQUIVO' => 'INCLUSÃO MANUAL', 
            'IMPA_DH_IMPORTACAO' => new Zend_Db_Expr('SYSDATE'),
            'IMPA_VL_RESP_IMPORTACAO' => $responsavel,
            'IMPA_AA_IMPORTACAO' => $ano,
            'IMPA_IC_MES' => 1, // mes não é mais utilziado
            'IMPA_IC_TP_ARQUIVO' => $tipo // arrayPadrao
        );        

        $arrayValidacao = array( 'validacao' => array(
            'IMPO_CD_UG' => $dados['IMPO_CD_UG'],
            'IMPO_CD_CONTA_CONTABIL' => $dados['IMPO_CD_CONTA_CONTABIL'],
            'IMPO_CD_RESULTADO_PRIMARIO' => $dados['IMPO_CD_RESULTADO_PRIMARIO'],
            'IMPO_CD_FONTE' => $dados['IMPO_CD_FONTE'],            
        ));
                
        $valida = $this->validaArray($arrayValidacao);
        
        if($valida){
            $codigo = parent::incluir($arrayImportacao);
        }else{
            return false;
        }

        if($dados['IMPO_IC_CATEGORIA'] != ""){
            $dados['IMPO_IC_CATEGORIA'] = "'".$dados['IMPO_IC_CATEGORIA']."'";
        }

        if($dados['IMPO_CD_VINCULACAO'] != ""){
            $dados['IMPO_CD_VINCULACAO'] = "'".$dados['IMPO_CD_VINCULACAO']."'";
        }              

        $sqlimpo = "
            INSERT INTO CEO_TB_IMPO_IMPORTACAO (
                IMPO_ID_IMPORTACAO,
                IMPO_ID_IMPORT_ARQUIVO,
                IMPO_CD_UG,
                IMPO_CD_CONTA_CONTABIL,
                IMPO_CD_RESULTADO_PRIMARIO,
                IMPO_CD_FONTE,
                IMPO_VL_TOTAL,
                IMPO_CD_NATUREZA_DESPESA,
                IMPO_CD_ESFERA,
                IMPO_CD_PTRES,
                IMPO_IC_CATEGORIA,
                IMPO_CD_UG_RESPONSAVEL,
                IMPO_CD_VINCULACAO,
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
                IMPO_VL_TOTAL_DEZ
            ) VALUES (
                 CEO_SEQ_IMPO.NEXTVAL,
                 ".$codigo['codigo'].",
                 ".$dados['IMPO_CD_UG'].",
                 ".$dados['IMPO_CD_CONTA_CONTABIL'].",
                 '".$dados['IMPO_CD_RESULTADO_PRIMARIO']."',
                 ".$dados['IMPO_CD_FONTE'].",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL']).",
                 ".(($dados['IMPO_CD_NATUREZA_DESPESA'] == '') ? 'null' : $dados['IMPO_CD_NATUREZA_DESPESA']).",
                 ".(($dados['IMPO_CD_ESFERA'] == '') ? 'null' : $dados['IMPO_CD_ESFERA']).",
                 ".(($dados['IMPO_CD_PTRES'] == '') ? 'null' : $dados['IMPO_CD_PTRES']).",
                 ".(($dados['IMPO_IC_CATEGORIA'] == '') ? 'null' : $dados['IMPO_IC_CATEGORIA']).",
                 null,
                 ".(($dados['IMPO_CD_VINCULACAO'] == '') ? 'null' : $dados['IMPO_CD_VINCULACAO']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_JAN']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_FEV']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_MAR']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_ABR']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_MAI']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_JUN']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_JUL']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_AGO']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_SET']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_OUT']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_NOV']).",
                 ".$valor->formataMoedaBanco($dados['IMPO_VL_TOTAL_DEZ'])." 
                )
        ";

        return $this->executaQuery ( $sqlimpo, true );
    }

    /**
     * Verifica se o arquivo é um txt
     * 
     * @param array $f
     * @return boolean
     */
    public function verificaTxt($f)
    {
        
        $file = pathinfo($f["IMPA_DS_ARQUIVO"]["name"]);

        if(strtolower($file["extension"]) != 'txt') {
            return false;
        }  
        
        return true;      
    }

    /**
     * retorna todos os registros da tabela impa
     * 
     * @return array
     */
    public function retornaListagem()
    {
        $sql = "
        SELECT * FROM CEO_TB_IMPA_IMPORTAR_ARQUIVO
        ORDER BY IMPA_AA_IMPORTACAO DESC
        ";

        $banco = Zend_Db_Table::getDefaultAdapter ();

        return $banco->fetchAll ( $sql );
    }

    /*
     * retorna a array com padrao de arquivos 1
     */
    public function retornaPadrao1( $dados )
    {

        $arrayPadrao= array();

        foreach ($dados as $str) {
            $arrayPadrao[] = array(
                'IMPO_CD_UG' => substr($str, 1, 5),
                'IMPO_CD_CONTA_CONTABIL' => substr($str, 6, 9),
                'IMPO_CD_RESULTADO_PRIMARIO' => substr($str, 15, 1),
                'IMPO_CD_ESFERA' => substr($str, 16, 1),                
                'IMPO_CD_PTRES' => substr($str, 18, 5),
                'IMPO_CD_FONTE' => substr($str, 24, 3),
                'IMPO_CD_NATUREZA_DESPESA' => substr($str, 33, 6),                
                 // campo              debito                                   crédito
                'IMPO_VL_TOTAL_JAN' => App_Util::moeda( + (substr($str,  93, 18)) - (substr($str, 309, 18))),
                'IMPO_VL_TOTAL_FEV' => App_Util::moeda( + (substr($str, 111, 18)) - (substr($str, 327, 18))),
                'IMPO_VL_TOTAL_MAR' => App_Util::moeda( + (substr($str, 129, 18)) - (substr($str, 345, 18))),
                'IMPO_VL_TOTAL_ABR' => App_Util::moeda( + (substr($str, 147, 18)) - (substr($str, 363, 18))),
                'IMPO_VL_TOTAL_MAI' => App_Util::moeda( + (substr($str, 165, 18)) - (substr($str, 381, 18))),
                'IMPO_VL_TOTAL_JUN' => App_Util::moeda( + (substr($str, 183, 18)) - (substr($str, 399, 18))),
                'IMPO_VL_TOTAL_JUL' => App_Util::moeda( + (substr($str, 201, 18)) - (substr($str, 417, 18))),
                'IMPO_VL_TOTAL_AGO' => App_Util::moeda( + (substr($str, 219, 18)) - (substr($str, 435, 18))),
                'IMPO_VL_TOTAL_SET' => App_Util::moeda( + (substr($str, 237, 18)) - (substr($str, 453, 18))),
                'IMPO_VL_TOTAL_OUT' => App_Util::moeda( + (substr($str, 255, 18)) - (substr($str, 471, 18))),
                'IMPO_VL_TOTAL_NOV' => App_Util::moeda( + (substr($str, 273, 18)) - (substr($str, 489, 18))),
                'IMPO_VL_TOTAL_DEZ' => App_Util::moeda( + (substr($str, 291, 18)) - (substr($str, 507, 18))),
  
  /*
                'CREDITO_IMPO_VL_TOTAL_JAN' => substr($str, 309, 18),
                'CREDITO_IMPO_VL_TOTAL_FEV' => substr($str, 327, 18),
                'CREDITO_IMPO_VL_TOTAL_MAR' => substr($str, 345, 18),
                'CREDITO_IMPO_VL_TOTAL_ABR' => substr($str, 363, 18),
                'CREDITO_IMPO_VL_TOTAL_MAI' => substr($str, 381, 18),
                'CREDITO_IMPO_VL_TOTAL_JUN' => substr($str, 399, 18),
                'CREDITO_IMPO_VL_TOTAL_JUL' => substr($str, 417, 18),
                'CREDITO_IMPO_VL_TOTAL_AGO' => substr($str, 435, 18),
                'CREDITO_IMPO_VL_TOTAL_SET' => substr($str, 453, 18),
                'CREDITO_IMPO_VL_TOTAL_OUT' => substr($str, 471, 18),
                'CREDITO_IMPO_VL_TOTAL_NOV' => substr($str, 489, 18),
                'CREDITO_IMPO_VL_TOTAL_DEZ' => substr($str, 507, 18),

                'DEBITO_IMPO_VL_TOTAL_JAN' => substr($str,  93, 18),
                'DEBITO_IMPO_VL_TOTAL_FEV' => substr($str, 111, 18),
                'DEBITO_IMPO_VL_TOTAL_MAR' => substr($str, 129, 18),
                'DEBITO_IMPO_VL_TOTAL_ABR' => substr($str, 147, 18),
                'DEBITO_IMPO_VL_TOTAL_MAI' => substr($str, 165, 18),
                'DEBITO_IMPO_VL_TOTAL_JUN' => substr($str, 183, 18),
                'DEBITO_IMPO_VL_TOTAL_JUL' => substr($str, 201, 18),
                'DEBITO_IMPO_VL_TOTAL_AGO' => substr($str, 219, 18),
                'DEBITO_IMPO_VL_TOTAL_SET' => substr($str, 237, 18),
                'DEBITO_IMPO_VL_TOTAL_OUT' => substr($str, 255, 18),
                'DEBITO_IMPO_VL_TOTAL_NOV' => substr($str, 273, 18),
                'DEBITO_IMPO_VL_TOTAL_DEZ' => substr($str, 291, 18),
*/
                
                'IMPO_VL_TOTAL' => App_Util::moeda(
                            
                            (
                                // SOMA OS DEBITO
                            substr($str,  93, 18)+
                            substr($str, 111, 18)+
                            substr($str, 129, 18)+
                            substr($str, 147, 18)+
                            substr($str, 165, 18)+
                            substr($str, 183, 18)+
                            substr($str, 201, 18)+
                            substr($str, 219, 18)+
                            substr($str, 237, 18)+
                            substr($str, 255, 18)+
                            substr($str, 273, 18)+
                            substr($str, 291, 18)
                            ) 
                            // SUBTRAI
                            -
                            
                            (
                                // SOMA OS CREDITO
                            substr($str, 309, 18)+
                            substr($str, 327, 18)+
                            substr($str, 345, 18)+
                            substr($str, 363, 18)+
                            substr($str, 381, 18)+
                            substr($str, 399, 18)+
                            substr($str, 416, 18)+
                            substr($str, 434, 18)+
                            substr($str, 452, 18)+
                            substr($str, 470, 18)+
                            substr($str, 488, 18)+
                            substr($str, 506, 18))
                            ),
                );

                

        }

        return $arrayPadrao;
    }

    /*
     * retorna a array com padrao de arquivos 2
     */    
    public function retornaPadrao2( $dados )
    {

        $arrayPadrao= array();

        foreach ($dados as $str) {
            $arrayPadrao[] = array(
                'IMPO_CD_UG' => substr($str, 1, 5),
                'IMPO_CD_CONTA_CONTABIL' => substr($str, 6, 9),
                'IMPO_CD_RESULTADO_PRIMARIO' => substr($str, 15, 1),
                'IMPO_CD_ESFERA' => substr($str, 16, 1),                
                'IMPO_CD_PTRES' => substr($str, 18, 5),
                'IMPO_CD_FONTE' => substr($str, 24, 3),
                'IMPO_CD_NATUREZA_DESPESA' => substr($str, 33, 8),                
                 // campo              credito                                   debito
                'IMPO_VL_TOTAL_JAN' => App_Util::moeda(substr($str, 309, 18) - substr($str,  93, 18)),
                'IMPO_VL_TOTAL_FEV' => App_Util::moeda(substr($str, 327, 18) - substr($str, 111, 18)),
                'IMPO_VL_TOTAL_MAR' => App_Util::moeda(substr($str, 345, 18) - substr($str, 129, 18)),
                'IMPO_VL_TOTAL_ABR' => App_Util::moeda(substr($str, 363, 18) - substr($str, 147, 18)),
                'IMPO_VL_TOTAL_MAI' => App_Util::moeda(substr($str, 381, 18) - substr($str, 165, 18)),
                'IMPO_VL_TOTAL_JUN' => App_Util::moeda(substr($str, 399, 18) - substr($str, 183, 18)),
                'IMPO_VL_TOTAL_JUL' => App_Util::moeda(substr($str, 417, 18) - substr($str, 201, 18)),
                'IMPO_VL_TOTAL_AGO' => App_Util::moeda(substr($str, 435, 18) - substr($str, 219, 18)),
                'IMPO_VL_TOTAL_SET' => App_Util::moeda(substr($str, 453, 18) - substr($str, 237, 18)),
                'IMPO_VL_TOTAL_OUT' => App_Util::moeda(substr($str, 471, 18) - substr($str, 255, 18)),
                'IMPO_VL_TOTAL_NOV' => App_Util::moeda(substr($str, 489, 18) - substr($str, 273, 18)),
                'IMPO_VL_TOTAL_DEZ' => App_Util::moeda(substr($str, 507, 18) - substr($str, 291, 18)),
 
/*  
                'CREDITO_IMPO_VL_TOTAL_JAN' => substr($str, 309, 18),
                'CREDITO_IMPO_VL_TOTAL_FEV' => substr($str, 327, 18),
                'CREDITO_IMPO_VL_TOTAL_MAR' => substr($str, 345, 18),
                'CREDITO_IMPO_VL_TOTAL_ABR' => substr($str, 363, 18),
                'CREDITO_IMPO_VL_TOTAL_MAI' => substr($str, 381, 18),
                'CREDITO_IMPO_VL_TOTAL_JUN' => substr($str, 399, 18),
                'CREDITO_IMPO_VL_TOTAL_JUL' => substr($str, 417, 18),
                'CREDITO_IMPO_VL_TOTAL_AGO' => substr($str, 435, 18),
                'CREDITO_IMPO_VL_TOTAL_SET' => substr($str, 453, 18),
                'CREDITO_IMPO_VL_TOTAL_OUT' => substr($str, 471, 18),
                'CREDITO_IMPO_VL_TOTAL_NOV' => substr($str, 489, 18),
                'CREDITO_IMPO_VL_TOTAL_DEZ' => substr($str, 507, 18),

                'DEBITO_IMPO_VL_TOTAL_JAN' => substr($str,  93, 18),
                'DEBITO_IMPO_VL_TOTAL_FEV' => substr($str, 111, 18),
                'DEBITO_IMPO_VL_TOTAL_MAR' => substr($str, 129, 18),
                'DEBITO_IMPO_VL_TOTAL_ABR' => substr($str, 147, 18),
                'DEBITO_IMPO_VL_TOTAL_MAI' => substr($str, 165, 18),
                'DEBITO_IMPO_VL_TOTAL_JUN' => substr($str, 183, 18),
                'DEBITO_IMPO_VL_TOTAL_JUL' => substr($str, 201, 18),
                'DEBITO_IMPO_VL_TOTAL_AGO' => substr($str, 219, 18),
                'DEBITO_IMPO_VL_TOTAL_SET' => substr($str, 237, 18),
                'DEBITO_IMPO_VL_TOTAL_OUT' => substr($str, 255, 18),
                'DEBITO_IMPO_VL_TOTAL_NOV' => substr($str, 273, 18),
                'DEBITO_IMPO_VL_TOTAL_DEZ' => substr($str, 291, 18),


                'TOTAL_CREDITO' => App_Util::moeda(
                            // CREDITO
                            
                            substr($str, 309, 18)+
                            substr($str, 327, 18)+
                            substr($str, 345, 18)+
                            substr($str, 363, 18)+
                            substr($str, 381, 18)+
                            substr($str, 399, 18)+
                            substr($str, 417, 18)+
                            substr($str, 435, 18)+
                            substr($str, 453, 18)+
                            substr($str, 471, 18)+
                            substr($str, 489, 18)+
                            substr($str, 507, 18)),

                'TOTAL_DEBITO' => App_Util::moeda(
                            substr($str,  93, 18)+
                            substr($str, 111, 18)+
                            substr($str, 129, 18)+
                            substr($str, 147, 18)+
                            substr($str, 165, 18)+
                            substr($str, 183, 18)+
                            substr($str, 201, 18)+
                            substr($str, 219, 18)+
                            substr($str, 237, 18)+
                            substr($str, 255, 18)+
                            substr($str, 273, 18)+
                            substr($str, 291, 18)
                            ),

*/
                'IMPO_VL_TOTAL' => App_Util::moeda(
                            // CREDITO
                            (
                            substr($str, 309, 18)+
                            substr($str, 327, 18)+
                            substr($str, 345, 18)+
                            substr($str, 363, 18)+
                            substr($str, 381, 18)+
                            substr($str, 399, 18)+
                            substr($str, 417, 18)+
                            substr($str, 435, 18)+
                            substr($str, 453, 18)+
                            substr($str, 471, 18)+
                            substr($str, 489, 18)+
                            substr($str, 507, 18)
                            ) 
                            -
                            // DEBITO
                            (
                            substr($str,  93, 18)+
                            substr($str, 111, 18)+
                            substr($str, 129, 18)+
                            substr($str, 147, 18)+
                            substr($str, 165, 18)+
                            substr($str, 183, 18)+
                            substr($str, 201, 18)+
                            substr($str, 219, 18)+
                            substr($str, 237, 18)+
                            substr($str, 255, 18)+
                            substr($str, 273, 18)+
                            substr($str, 291, 18)
                            )
                )
                );

                

        }

        return $arrayPadrao;
    }

    /*
     * retorna a array com padrao de arquivos 3
     */
    public function retornaPadrao3( $dados )
    {

        $arrayPadrao= array();

        foreach ($dados as $str) {
            $arrayPadrao[] = array(
                'IMPO_CD_UG' => substr($str, 1, 5),
                'IMPO_CD_CONTA_CONTABIL' => substr($str, 6, 9),
                'IMPO_CD_RESULTADO_PRIMARIO' => substr($str, 15, 1),
                'IMPO_CD_UG_RESPONSAVEL' => substr($str, 16, 6),                
                'IMPO_CD_FONTE' => substr($str, 23, 3),
                'IMPO_IC_CATEGORIA' => substr($str, 32, 1),
                'IMPO_CD_VINCULACAO' => substr($str, 34, 6),                
                 // campo              credito                                   debito
                'IMPO_VL_TOTAL_JAN' => App_Util::moeda(substr($str, 309, 18) - substr($str,  93, 18)),
                'IMPO_VL_TOTAL_FEV' => App_Util::moeda(substr($str, 327, 18) - substr($str, 111, 18)),
                'IMPO_VL_TOTAL_MAR' => App_Util::moeda(substr($str, 345, 18) - substr($str, 129, 18)),
                'IMPO_VL_TOTAL_ABR' => App_Util::moeda(substr($str, 363, 18) - substr($str, 147, 18)),
                'IMPO_VL_TOTAL_MAI' => App_Util::moeda(substr($str, 381, 18) - substr($str, 165, 18)),
                'IMPO_VL_TOTAL_JUN' => App_Util::moeda(substr($str, 399, 18) - substr($str, 183, 18)),
                'IMPO_VL_TOTAL_JUL' => App_Util::moeda(substr($str, 417, 18) - substr($str, 201, 18)),
                'IMPO_VL_TOTAL_AGO' => App_Util::moeda(substr($str, 435, 18) - substr($str, 219, 18)),
                'IMPO_VL_TOTAL_SET' => App_Util::moeda(substr($str, 453, 18) - substr($str, 237, 18)),
                'IMPO_VL_TOTAL_OUT' => App_Util::moeda(substr($str, 471, 18) - substr($str, 255, 18)),
                'IMPO_VL_TOTAL_NOV' => App_Util::moeda(substr($str, 489, 18) - substr($str, 273, 18)),
                'IMPO_VL_TOTAL_DEZ' => App_Util::moeda(substr($str, 507, 18) - substr($str, 291, 18)),
                
                /*
                'CREDITO_IMPO_VL_TOTAL_JAN' => substr($str, 309, 18),
                'CREDITO_IMPO_VL_TOTAL_FEV' => substr($str, 327, 18),
                'CREDITO_IMPO_VL_TOTAL_MAR' => substr($str, 345, 18),
                'CREDITO_IMPO_VL_TOTAL_ABR' => substr($str, 363, 18),
                'CREDITO_IMPO_VL_TOTAL_MAI' => substr($str, 381, 18),
                'CREDITO_IMPO_VL_TOTAL_JUN' => substr($str, 399, 18),
                'CREDITO_IMPO_VL_TOTAL_JUL' => substr($str, 417, 18),
                'CREDITO_IMPO_VL_TOTAL_AGO' => substr($str, 435, 18),
                'CREDITO_IMPO_VL_TOTAL_SET' => substr($str, 453, 18),
                'CREDITO_IMPO_VL_TOTAL_OUT' => substr($str, 471, 18),
                'CREDITO_IMPO_VL_TOTAL_NOV' => substr($str, 489, 18),
                'CREDITO_IMPO_VL_TOTAL_DEZ' => substr($str, 507, 18),

                'DEBITO_IMPO_VL_TOTAL_JAN' => substr($str,  93, 18),
                'DEBITO_IMPO_VL_TOTAL_FEV' => substr($str, 111, 18),
                'DEBITO_IMPO_VL_TOTAL_MAR' => substr($str, 129, 18),
                'DEBITO_IMPO_VL_TOTAL_ABR' => substr($str, 147, 18),
                'DEBITO_IMPO_VL_TOTAL_MAI' => substr($str, 165, 18),
                'DEBITO_IMPO_VL_TOTAL_JUN' => substr($str, 183, 18),
                'DEBITO_IMPO_VL_TOTAL_JUL' => substr($str, 201, 18),
                'DEBITO_IMPO_VL_TOTAL_AGO' => substr($str, 219, 18),
                'DEBITO_IMPO_VL_TOTAL_SET' => substr($str, 237, 18),
                'DEBITO_IMPO_VL_TOTAL_OUT' => substr($str, 255, 18),
                'DEBITO_IMPO_VL_TOTAL_NOV' => substr($str, 273, 18),
                'DEBITO_IMPO_VL_TOTAL_DEZ' => substr($str, 291, 18),
                */

                'IMPO_VL_TOTAL' => App_Util::moeda(
                            // CREDITO
                            (
                            substr($str, 309, 18)+
                            substr($str, 327, 18)+
                            substr($str, 345, 18)+
                            substr($str, 363, 18)+
                            substr($str, 381, 18)+
                            substr($str, 399, 18)+
                            substr($str, 417, 18)+
                            substr($str, 435, 18)+
                            substr($str, 453, 18)+
                            substr($str, 471, 18)+
                            substr($str, 489, 18)+
                            substr($str, 507, 18)) 
                            -
                            // DEBITO
                            (
                            substr($str,  93, 18)+
                            substr($str, 111, 18)+
                            substr($str, 129, 18)+
                            substr($str, 147, 18)+
                            substr($str, 165, 18)+
                            substr($str, 183, 18)+
                            substr($str, 201, 18)+
                            substr($str, 219, 18)+
                            substr($str, 237, 18)+
                            substr($str, 255, 18)+
                            substr($str, 273, 18)+
                            substr($str, 291, 18)
                            )),
                );

                

        }

        return $arrayPadrao;
    }

    /**
     * transforma os dados e retorna caso exista o registro no banco de dados
     * 
     * @param array $dados
     * @param int $ano
     */
    public function verificaExistentes( $dados, $ano = null, $tipo = null )
    {

        if($ano != ""){
            $dados['IMPA_AA_IMPORTACAO'] = $ano;
        }

        if($tipo != ""){
            $dados['IMPA_IC_TP_ARQUIVO'] = $tipo;
        }
        
        // verifica se já existe
        $res = $this->verificaDuplicidade( 
            $dados['IMPO_CD_CONTA_CONTABIL'],
            $dados['IMPO_CD_PTRES'],
            $dados['IMPO_CD_NATUREZA_DESPESA'],
            $dados['IMPO_CD_FONTE'],
            $dados['IMPO_CD_ESFERA'],
            $dados['IMPO_CD_UG'],
            $dados['IMPO_CD_RESULTADO_PRIMARIO'],
            $dados['IMPO_IC_CATEGORIA'],
            $dados['IMPO_CD_UG_RESPONSAVEL'],
            $dados['IMPO_CD_VINCULACAO'], 
            $dados['IMPA_AA_IMPORTACAO'],
            $dados['IMPA_IC_TP_ARQUIVO']
        );
        
        // exclui um ou mais registros repetidos
        if( count($res) > 0 ){
            
            $chaves = array();
            
            foreach ($res as $chave) {
                $chaves[] = $chave['IMPO_ID_IMPORTACAO'];                
            }            
            
            $this->exclusaoFisica ( $chaves );
        }
    }

    /**
     * Verifica se já existe o registro no banco de dados
     * 
     * @param int $conta
     * @param int $ptres
     * @param int $natureza
     * @param int $fonte
     * @param int $esfera
     * @param int $ug
     * @param int $resultado
     * @param int $categoria
     * @param int $ugresponsavel
     * @param int $vinculacao
     * @param int $ano
     * @return array
     */
    public function verificaDuplicidade( $conta,  $ptres, $natureza, $fonte, $esfera, $ug, $resultado, $categoria, $ugresponsavel, 
        $vinculacao, $ano, $tipo, $codigo = null )
    {

        $strAno = "";
        if($ano != ""){
            $strAno = "AND IMPA_AA_IMPORTACAO = $ano";
        }

        $strPtres = "";
        if($ptres != ""){
            $strPtres = "AND IMPO_CD_PTRES = '$ptres' ";
        }

        $strNatureza = "";
        if($natureza !=""){
            $strNatureza = "AND IMPO_CD_NATUREZA_DESPESA = '$natureza' ";
        }

        $strFonte = "";
        if($fonte != ""){
            $strFonte = "AND IMPO_CD_FONTE = $fonte ";
        }

        $strEsfera = "";
        if($esfera != ""){
            $strEsfera = "AND IMPO_CD_ESFERA = '$esfera' ";
        }

        $strUg = "";
        if($ug != ""){
            $strUg = "AND IMPO_CD_UG = $ug ";
        }

        $strResultado = "";
        if($resultado != ""){
            $strResultado = "AND IMPO_CD_RESULTADO_PRIMARIO = '$resultado' ";
        }

        $strCategoria = "";
        if($categoria != ""){
            $strCategoria = "AND IMPO_IC_CATEGORIA = '$categoria' ";
        }

        $strResponsavel = "";
        if($ugresponsavel != ""){
            $strResponsavel = "AND IMPO_CD_UG_RESPONSAVEL = $ugresponsavel ";
        }

        $strVinculacao = "";
        if($vinculacao != ""){
            $strVinculacao = "AND IMPO_CD_VINCULACAO = $vinculacao";
        }

        $strTipo = "";
        if($tipo != ""){
            $strTipo = "AND IMPA_IC_TP_ARQUIVO = $tipo";
        }
        
        $strCodigo = "";
        if($codigo != ""){
            $strCodigo = "AND IMPO_ID_IMPORTACAO <> $codigo";
        }

        $sql = "
            SELECT 
                * 
            FROM CEO_TB_IMPO_IMPORTACAO
            
            LEFT JOIN CEO_TB_IMPA_IMPORTAR_ARQUIVO
                ON IMPO_ID_IMPORT_ARQUIVO = IMPA_ID_IMPORT_ARQUIVO

            WHERE 
                IMPO_CD_CONTA_CONTABIL   = $conta              

            $strPtres
            $strNatureza
            $strFonte
            $strEsfera
            $strUg
            $strResultado
            $strCategoria
            $strResponsavel
            $strVinculacao
            $strAno
            $strTipo
            $strCodigo
        ";
        
        $banco = Zend_Db_Table::getDefaultAdapter ();

        return $banco->fetchAll ( $sql );
    }

    /**
     * Excui fisicamente um ou mais registros
     * 
     * @param array $chaves
     * @return array
     */
    public function exclusaoFisica ( $chaves )
    {        
        $codigos = $this->separaChave ( $chaves );
               
        $sql = " 
            DELETE 
                FROM CEO_TB_IMPO_IMPORTACAO 
            WHERE 
                IMPO_ID_IMPORTACAO IN ( $codigos )
        ";

        return $this->executaQuery ( $sql, true );
    }    

    /**
     * Valida se os dados do txt estão de acordo com os campos não nulos do 
     * banco de dados
     * 
     * @param array $array
     * @return boolean
     */
    public function validaArray($array)
    {
                
        foreach ($array as $dado) {
                   
            if(!is_numeric($dado['IMPO_CD_UG'])){
                return false;
            }            
            
            if(!is_numeric($dado['IMPO_CD_CONTA_CONTABIL'])){
                return false;
            }                                    
                        
            $tamanho = strlen($dado['IMPO_CD_RESULTADO_PRIMARIO']);    
                       
            if( $tamanho > 1 ){
                return false;
            }                                
            
            if(!is_numeric($dado['IMPO_CD_FONTE'])){
                return false;
            }                                 
            
        }
                
        return true;
        
    }
    
}