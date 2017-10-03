<?php

/**
 * Contém métodos e propriedades elementares para as regras negociais.
 * 
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Disponibiliza recursos elementares para as regras negociais referetne o 
 * importação.
 *
 * @category Orcamento
 * @package Orcamento_Business_Importacao_Base
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2015 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Importacao_Base extends Orcamento_Business_Negocio_Base {

    /**
     * Constantes com o valor dos modelos a serem utilizados no ImportBuffer.
     * OBs.: Apenas adicionar novos itens, não renomeá-los.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    const PADRAO1 = 'padrao1';
    const PADRAO2 = 'padrao2';
    const PADRAO3 = 'padrao3';

    /**
     * Constantes com as mensagens de retorno das validações.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    const MSG001 = 'Operação realizada com sucesso';
    const MSG025 = 'Arquivo corrompido, não foi possível finalizar a importação';
    const MSG028 = 'Não é possível informar datas futuras para geração de relatório, apenas data anterior ou atual';
    const MSG024 = 'Arquivo em formato não permitido, apenas formato texto (.txt) é permitido';
    const MSG026 = 'Ocorreu um erro e não foi possível completar a importação de arquivo';

    /**
     * Constantes do tipo de relatório.
     * OBS.: Apenas adicionar novos itens, não renomeá-los.
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    const ARQUIVO_FINANCEIRO = 1;
    const ARQUIVO_SUPLEMENTACAO = 2;
    const ARQUIVO_LIQUIDADO = 3;
    const ARQUIVO_DOTACAO = 4;
    const ARQUIVO_CANCELAMENTO = 5;
    const ARQUIVO_CONTINGENCIAMENTO = 6;
    const ARQUIVO_PROVISAO = 7;
    const ARQUIVO_DESTAQUE = 8;
    const ARQUIVO_EMPENHADO = 9;
    const ARQUIVO_PAGO = 10;
    const ARQUIVO_RESTOSAPAGAR = 11;

    private $modelo;

    public function init($modelo) {
        $this->modelo = $modelo;
    }

    /**
     * Retorna campos a serem mostrados na GRID conforme RN084, RN085 RN088.
     *
     * @param string $acao Nome da ação (action) em questão
     * @return string
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaCampos() {
        // define a ação
        $acao = Zend_Controller_Front::
                getInstance()->getRequest()->getActionName();

        // ---------------------------------------------------------------------
        // INDEX
        // Campos para a serem apresentados na indexAction
        // Padrão da regra RN084
        $campos[self::PADRAO1]['index'] = "
            IMPO_ID_IMPORTACAO,
            IMPO_CD_UG,
            IMPO_CD_CONTA_CONTABIL,
            IMPO_CD_RESULTADO_PRIMARIO,
            IMPO_CD_ESFERA,
            IMPO_CD_PTRES,
            IMPO_CD_FONTE,
            IMPO_CD_NATUREZA_DESPESA,
            CONCAT(CONCAT(TO_CHAR(IMPA_IC_MES, '00'), '/'),
            TO_CHAR(TO_DATE(IMPA_AA_IMPORTACAO, 'rr'), 'yyyy')) as \"DATA\",
            CASE WHEN IMPO_VL_TOTAL > 0 THEN
                TO_CHAR(IMPO_VL_TOTAL, '999G999G999G999D99') 
            ELSE
                '0,00'
            END AS IMPO_VL_TOTAL
        ";

        // Padrão da regra RN085
        $campos[self::PADRAO2]['index'] = "
            IMPO_ID_IMPORTACAO,
            IMPO_CD_UG,
            IMPO_CD_CONTA_CONTABIL,
            IMPO_CD_RESULTADO_PRIMARIO,
            IMPO_CD_ESFERA,
            IMPO_CD_PTRES,
            IMPO_CD_FONTE,
            IMPO_CD_NATUREZA_DESPESA,
            CONCAT(CONCAT(TO_CHAR(IMPA_IC_MES, '00'), '/'),
                TO_CHAR(TO_DATE(IMPA_AA_IMPORTACAO, 'rr'), 'yyyy')) as \"DATA\",

            CASE WHEN IMPO_VL_TOTAL > 0 THEN
                TO_CHAR(IMPO_VL_TOTAL, '999G999G999G999D99') 
            ELSE
                '0,00'
            END AS IMPO_VL_TOTAL
        ";

        // Padrão da regra RN088
        $campos[self::PADRAO3]['index'] = "
            IMPO_ID_IMPORTACAO,
            IMPO_CD_UG ,
            IMPO_CD_CONTA_CONTABIL,
            IMPO_CD_RESULTADO_PRIMARIO,
            IMPO_CD_UG_RESPONSAVEL,
            IMPO_CD_FONTE,
            IMPO_IC_CATEGORIA,
            IMPO_CD_VINCULACAO,
            CONCAT(CONCAT(TO_CHAR(IMPA_IC_MES, '00'), '/'),
                TO_CHAR(TO_DATE(IMPA_AA_IMPORTACAO, 'rr'), 'yyyy')) DATA,
            CASE WHEN IMPO_VL_TOTAL > 0 THEN
                TO_CHAR(IMPO_VL_TOTAL, '999G999G999G999D99') 
            ELSE
                '0,00'
            END AS IMPO_VL_TOTAL
        ";

        // ---------------------------------------------------------------------
        // DETALHE
        // Padrão da regra RN084
        $campos[self::PADRAO1]['detalhe'] = "
           IMPO_CD_UG as \"UG\",
           IMPO_CD_CONTA_CONTABIL as \"Conta Contábil\",
           IMPO_CD_RESULTADO_PRIMARIO as \"Resultado Primário\",
           IMPO_CD_ESFERA as \"Esfera\",
           IMPO_CD_PTRES as \"PTRES\",
           IMPO_CD_FONTE as \"Fonte\",
           IMPO_CD_NATUREZA_DESPESA as \"Natureza Despesa\",
           CONCAT(CONCAT(TO_CHAR(TO_DATE(IMPA_IC_MES, 'MM'), 'Month'), '/ '),
           TO_CHAR(TO_DATE(IMPA_AA_IMPORTACAO, 'rr'), 'yyyy')) as \"Mês / Ano\",
           CASE WHEN IMPO_VL_TOTAL > 0 THEN
                TO_CHAR(IMPO_VL_TOTAL, '999G999G999G999D99') 
            ELSE
                '0,00'
            END as \"Total\"
        ";

        // Padrão da regra RN085
        $campos[self::PADRAO2]['detalhe'] = "
           IMPO_CD_UG as \"UG\",
           IMPO_CD_CONTA_CONTABIL as \"Conta Contábil\",
           IMPO_CD_RESULTADO_PRIMARIO as \"Resultado Primário\",
           IMPO_CD_ESFERA as \"Esfera\",
           IMPO_CD_PTRES as \"PTRES\",
           IMPO_CD_FONTE as \"Fonte\",
           IMPO_CD_NATUREZA_DESPESA as \"Natureza Despesa\",
           CONCAT(CONCAT(TO_CHAR(TO_DATE(IMPA_IC_MES, 'MM'), 'Month'), '/ '),
           TO_CHAR(TO_DATE(IMPA_AA_IMPORTACAO, 'rr'), 'yyyy')) as \"Mês / Ano\",
           CASE WHEN IMPO_VL_TOTAL > 0 THEN
                TO_CHAR(IMPO_VL_TOTAL, '999G999G999G999D99') 
            ELSE
                '0,00'
            END as \"Total\"
        ";

        // Padrão da regra RN088
        $campos[self::PADRAO3]['detalhe'] = "
           IMPO_CD_UG as \"UG\",
           IMPO_CD_CONTA_CONTABIL as \"Conta Contábil\",
           IMPO_CD_RESULTADO_PRIMARIO as \"Resultádo Primário\",
           IMPO_CD_UG_RESPONSAVEL as \"UG Responsável\",
           IMPO_CD_FONTE as \"Fonte\",
           IMPO_IC_CATEGORIA as \"Categoria\",
           IMPO_IC_CATEGORIA as \"Vinculação\", /* arrumar para vinculacao */
           CONCAT(CONCAT(TO_CHAR(TO_DATE(IMPA_IC_MES, 'MM'), 'Month'), '/ '),
           TO_CHAR(TO_DATE(IMPA_AA_IMPORTACAO, 'rr'), 'yyyy')) as \"Mês / Ano\",
           CASE WHEN IMPO_VL_TOTAL > 0 THEN
                TO_CHAR(IMPO_VL_TOTAL, '999G999G999G999D99') 
            ELSE
                '0,00'
            END as \"Total\"
        ";

        // ---------------------------------------------------------------------
        // INCLUIR

        $campos[$this->modelo]['incluir'] = "
            IMPA_DS_ARQUIVO,
            TO_CHAR(IMPA_DH_IMPORTACAO, 'DD/MM/YYYY HH24:MI:SS') 
            IMPA_DH_IMPORTACAO,
            IMPA_VL_RESP_IMPORTACAO
        ";

        // ---------------------------------------------------------------------
        // EXCLUIR
        $campos ['excluir'] = "IMPO_ID_IMPORTACAO";
        $campos ['excluir'] .= $campos ['detalhe'];

        $retorno = $campos[$this->modelo][$acao];

        // Devolve os campos, conforme ação
        return $retorno;
    }

    /**
     * Retorna as condições restritivas, se houver para a montagem da instrução
     * sql.
     *
     * @param string $acao Nome da ação (action) em questão
     * @param string $chaves Informa a chave, já tratada, se for o caso
     * @return string
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaRestricoes($acao = 'todos', $chaves = null) {
        // Sem condição...
        $restricao ['todos'] = " ";

        $tipoImportacao = new Zend_Session_Namespace('tipoImportacao');
        $tipo = $tipoImportacao->tipo;

        // seleciona por tipo
        $restricao['index'] = " AND IMPA_IC_TP_ARQUIVO = {$tipo} ";

        // Condição para ação editar
        $restricao ['detalhe'] = " AND IMPO_ID_IMPORTACAO IN ( $chaves ) ";

        // Condição para ação editar
        $restricao ['editar'] = $restricao['detalhe'];

        // Condição para ação excluir
        $restricao ['excluir'] = $restricao['detalhe'];

        return $restricao [$acao];
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaOpcoesGrid() {
        // define a ação
        $acao = Zend_Controller_Front::
                getInstance()->getRequest()->getActionName();

        // Personaliza a exibição dos campos no grid
        // ---------------------------------------------------------------------
        // GRID INDEX
        $detalhes[self::PADRAO1]['index'] = array(
            'IMPO_CD_UG' => array('title' => 'UG', 'abbr' => 'UG'),
            'IMPO_CD_CONTA_CONTABIL' => array('title'
                => 'Conta Contábil', 'abbr'
                => 'Conta Contábil'),
            'IMPO_CD_RESULTADO_PRIMARIO' => array('title'
                => 'Resultado Primário', 'abbr'
                => 'Resultado Primário'),
            'IMPO_CD_ESFERA' => array('title'
                => 'Esfera', 'abbr'
                => 'Esfera'),
            'IMPO_CD_PTRES' => array('title'
                => 'PTRES', 'abbr'
                => 'PTRES'),
            'IMPO_CD_FONTE' => array('title'
                => 'Fonte', 'abbr'
                => 'Fonte'),
            'IMPO_CD_NATUREZA_DESPESA' => array('title'
                => 'Natureza da despesa', 'abbr'
                => 'Natureza da despesa'),
            'DATA' => array('title' => 'Data', 'abbr' => 'Data'),
            'IMPO_VL_TOTAL' => array('title' => 'Total', 'abbr' => 'Total')
        );

        $detalhes[self::PADRAO2]['index'] = array(
            'IMPO_CD_UG' => array('title' => 'UG', 'abbr' => 'UG'),
            'IMPO_CD_CONTA_CONTABIL' => array('title'
                => 'Conta Contábil', 'abbr' => 'Conta Contábil'),
            'IMPO_CD_RESULTADO_PRIMARIO' => array('title'
                => 'Resultado Primário', 'abbr' => 'Resultado Primário'),
            'IMPO_CD_ESFERA' => array('title' => 'Esfera', 'abbr' => 'Esfera'),
            'IMPO_CD_PTRES' => array('title' => 'PTRES', 'abbr' => 'PTRES'),
            'IMPO_CD_FONTE' => array('title' => 'Fonte', 'abbr' => 'Fonte'),
            'IMPO_CD_NATUREZA_DESPESA' => array('title'
                => 'Natureza da despesa', 'abbr'
                => 'Natureza da despesa'),
            'DATA' => array('title' => 'Data', 'abbr' => 'Data'),
            'IMPO_VL_TOTAL' => array('title' => 'Total', 'abbr' => 'Total')
        );

        $detalhes[self::PADRAO3]['index'] = array(
            'IMPO_CD_UG' => array('title' => 'UG', 'abbr' => 'UG'),
            'IMPO_CD_CONTA_CONTABIL' => array('title'
                => 'Conta Contábil', 'abbr' => 'Conta Contábil'),
            'IMPO_CD_RESULTADO_PRIMARIO' => array('title'
                => 'Resultado Primário', 'abbr' => 'Resultado Primário'),
            'IMPO_CD_UG_RESPONSAVEL' => array('title'
                => 'UG Responsável', 'abbr' => 'UG Responsável'),
            'IMPO_CD_FONTE' => array('title'
                => 'Fonte', 'abbr' => 'Fonte'),
            'IMPO_IC_CATEGORIA' => array('title'
                => 'Categoria', 'abbr' => 'Categoria'),
            'IMPO_CD_VINCULACAO' => array('title'
                => 'Vinculação', 'abbr' => 'Vinculação'),
            'DATA' => array('title' => 'Data', 'abbr' => 'Data'),
            'IMPO_VL_TOTAL' => array('title' => 'Total', 'abbr' => 'Total')
        );

        // ---------------------------------------------------------------------
        // GRID INCLUIR

        if (Orcamento_Business_Dados::ACTION_INCLUIR === $acao) {
            $this->_model = new Orcamento_Model_DbTable_ImportarArquivo();
        }

        $detalhes[$this->modelo]['incluir'] = array(
            'IMPA_DS_ARQUIVO' => array('title' => 'Nome do Arquivo', 'abbr'
                => 'Nome do Arquivo'),
            'IMPA_DH_IMPORTACAO' => array('title' => 'Data Importação', 'abbr'
                => 'Data Importação'),
            'IMPA_VL_RESP_IMPORTACAO' => array('title'
                => 'Responsável Importação', 'abbr'
                => 'Responsável Importação'),
        );

        // ---------------------------------------------------------------------
        // OCULTO

        $oculto['index'] = array('IMPO_ID_IMPORTACAO');

        // ---------------------------------------------------------------------
        // BOTOES DO GRID

        $acaoMassa['index'] = array('incluir', 'detalhe');
        $acaoMassa['incluir'] = array();

        // ---------------------------------------------------------------------
        // Combina as opções num array

        $opcoes ['detalhes'] = $detalhes[$this->modelo][$acao];
        $opcoes ['controle'] = $this->_negocio;
        $opcoes ['ocultos'] = $oculto[$acao];

        // botões de ação em massa
        $opcoes ['acoesEmMassa'] = $acaoMassa[$acao];

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Responsável em unir mais tabelas, 
     * dependendo do filtro necessário para a aplicação.
     * 
     * @return string
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaJoins() {
        // define a ação
        $acao = Zend_Controller_Front::
                getInstance()->getRequest()->getActionName();

        // ação index
        $opcoes['index'] = " INNER JOIN CEO_TB_IMPA_IMPORTAR_ARQUIVO "
                . "ON IMPA_ID_IMPORT_ARQUIVO = IMPO_ID_IMPORT_ARQUIVO ";

        // ação detalhe
        $opcoes['detalhe'] = $opcoes['index'];

        // sem inner para incluir
        $opcoes['incluir'] = " ";

        return $opcoes[$acao];
    }

    /**
     * Retorna array contendo as ids para uso no cache.
     *
     * @return string array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function retornaCacheIds($acao = null) {
        // Instancia o cache
        $cache = new Trf1_Cache ();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $id ['index'] = $cache->retornaID_Listagem('orcamento', $negocio);

        // Id para combo
        $id ['combo'] = $cache->retornaID_Combo('orcamento', $negocio);

        // Determina qual valor será retornado
        $retorno = ( $acao != null ? $id [$acao] : $id );

        // Devolve o id, conforme $acao informada
        return $retorno;
    }

    /**
     * Metodo responsável em direcionar para validação das regras RN90, RN91
     * Monta a sql de dados.
     * 
     * @param type $padrao
     * @param array $dados
     * @return type
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function montarSQLDados($padrao, array $dados) {

        switch ($padrao) {
            // caso seja necessário criar um case específico
            // todos os imports devem seguir essas RNs de soma
            default:
                $somaRetorno = $this->verificarRN90eRN91($dados);
                break;
        }

        // monta array com informações dos dados
        foreach ($dados as $indiceLinhas => $linhas) {
            $retorno[$indiceLinhas] = $this->montarArraySQL($linhas, $somaRetorno[$indiceLinhas]);
        }

        return $retorno;
    }

    /**
     * Método responsável em montar o array com todos os campos a serem 
     * preenchidos e realizar alguns tratamentos a campos que 
     * possam ser NOT NULL.
     *  
     * 
     * @param array $valores
     * @param array $soma
     * @return int
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function montarArraySQL(array $valores, array $soma) {

        // array com todos os campos a serem preenchidos
        $arrayNull = array(
            'IMPO_CD_UG',
            'IMPO_CD_CONTA_CONTABIL',
            'IMPO_CD_RESULTADO_PRIMARIO',
            'IMPO_CD_FONTE',
            'IMPO_CD_NATUREZA_DESPESA',
            'IMPO_VL_TOTAL',
            'IMPO_CD_ESFERA',
            'IMPO_CD_PTRES',
            'IMPO_IC_CATEGORIA',
            'IMPO_CD_UG_RESPONSAVEL'
        );

        // laço com os campos para efetuar atribuição de seus valores
        foreach ($valores as $indice => $valor) {
            // seta o valor na variável
            $value = $valor['valor'];

            // verifica se deve importar ou não o campo
            if (false === $valor['importar']) {
                continue;
            }

            // coloca soma no valor total
            if ('IMPO_VL_TOTAL' == $indice) {
                $retorno[$indice] = $soma['total'];
                continue;
            }

            $retorno[$indice] = $value;
        }

        return $retorno;
    }

    /**
     * RN090 – CALCULO CONTAS CONTÁBEIS INICIO ÍMPAR
     * Para as contas contábeis iniciadas com dígitos impares (1,3,5 ou 7) 
     * o sistema deverá somar os débitos e subtrair os créditos, este resultado 
     * deve ser salvo em base de dados como o valor total da conta.
     * 
     * RN091 – CALCULO CONTAS CONTÁBEIS INICIO PAR
     * Para as contas contábeis iniciadas com o dígitos pares (2,4,6 ou 8) 
     * o sistema deverá somar os créditos e subtrair os débitos, este resultado 
     * deve ser salvo em base de dados como o valor total da conta.
     * 
     * @param array $dados
     * @return string
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function verificarRN90eRN91($dados) {
        // cria arrays
        $retorno = array();
        $debito = array();
        $credito = array();
        $retornoTotal = array();

        // percorre todas as linhas
        foreach ($dados as $indiceLinha => $linha) {
            // inicializa todos os valores
            $conta = $linha['IMPO_CD_CONTA_CONTABIL']['valor'];
            $primeiroDigitoContaSplit = str_split($conta);
            $primeiroDigitoConta = $primeiroDigitoContaSplit[0];
            $debito[$indiceLinha] = 0;
            $credito[$indiceLinha] = 0;

            // soma todos os debitos
            foreach ($linha['IMPO_VL_TOTAL']['debito'] as $valores) {
                $debito[$indiceLinha] += $valores['valor_sem_sinal'];
            }

            // soma todos os creditos
            foreach ($linha['IMPO_VL_TOTAL']['credito'] as $valores) {
                $credito[$indiceLinha] += $valores['valor_sem_sinal'];
            }

            // se for conta par, aplica regra RN091
            // onde soma todos os creditos e subtrai os debitos
            if ($primeiroDigitoConta % 2 == 0) {
                $tipo = 'par';
                $retornoTotal[$indiceLinha] = $credito[$indiceLinha] - $debito[$indiceLinha];
            } else {
                // se for conta impar, aplica regra RN090
                // onde soma todos os debitos e subtrai os creditos
                $tipo = 'impar';
                $retornoTotal[$indiceLinha] = $debito[$indiceLinha] - $credito[$indiceLinha];
            }

            // array com os dados a serem gravador
            $retorno[$indiceLinha] = array(
                'tipo' => $tipo,
                'debito' => $debito[$indiceLinha],
                'credito' => $credito[$indiceLinha],
                'total' => $retornoTotal[$indiceLinha],
                'conta' => $conta
            );
        }

        return $retorno;
    }

    /**
     * Cria array para execução da SQL da tabela CEO_TB_IMPA_IMPORTAR_AQRUIVO.
     * 
     * @param array $post
     * @param string $nomeArquivo
     * @param int $tipo
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function montarSQLArquivo($post, $nomeArquivo, $tipo) {
        // nome do responsavel
        $userNs = new Zend_Session_Namespace('userNs');
        $responsavel = $userNs->nome;

        // data atual
        $data = "TIMESTAMP'" . date("d/m/Y H:i:s");

        // monta array para gerar sql
        $sql = array();
        $sql['IMPA_DS_ARQUIVO'] = $nomeArquivo;
        $sql['IMPA_DH_IMPORTACAO'] = new Zend_Db_Expr('SYSDATE');
        $sql['IMPA_AA_IMPORTACAO'] = substr($post['IMPA_AA_IMPORTACAO'], 2, 2);
        $sql['IMPA_IC_MES'] = $post['IMPA_IC_MES'];
        $sql['IMPA_IC_TP_ARQUIVO'] = $tipo;
        $sql['IMPA_VL_RESP_IMPORTACAO'] = $responsavel;

        return $sql;
    }

    /**
     * Validação de regras negociais.
     * RN083 - Verifica se a data está dentro do range permitido.
     * 
     * @param int $tipo
     * @param array $dadosPost
     * @return mixed
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function validarNegocial($tipo, $dadosPost) {

        // efetua validações negociais
        switch ($tipo) {
            // deixar no default caso sejam todos iguais
            default:
                // verifica se a data está dentro do range permitido
                $this->validarRN083($dadosPost);

                // valida se existe algum registro e apaga o mesmo
                $this->validarRN087($dadosPost);
                break;
        }

    }

    /**
     * RN083 – DATA PARA IMPORTAÇÃO
     * Não é possível informar datas futuras para importação de arquivos,
     * apenas data anterior ou atual. E por padrão a data sugerida pelo 
     * sistema é sempre do mês anterior ao atual.
     * 
     * @param type $dadosPost
     * @return boolean
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function validarRN083($dadosPost) {

        // formata ano e mês para verificação
        $ano = $dadosPost['IMPA_AA_IMPORTACAO'];
        $mes = str_pad($dadosPost['IMPA_IC_MES'], 2, "0", STR_PAD_LEFT);

        // data base da tela
        $dataBase = strtotime("{$ano}-{$mes}-01T00:00:00");

        // data maior que a data atual
        $dataMaior = strtotime("first day of next month, 00:00:00");

        if ($dataBase >= $dataMaior) {
            throw new Zend_Exception(self::MSG028);
        }
        
    }

    /**
     * RN087 – RE-IMPORTAÇÃO DE ARQUIVO
     * Caso usuário realize um importação de um arquivo com mês e ano já 
     * importados anteriormente o sistema deve apagar fisicamente do banco 
     * de dados estes dados importados anteriormente só após essa exclusão 
     * realizar a importação do arquivo selecionado.
     * 
     * @param array $dadosPost
     * @return void
     * @throws Zend_Exception
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function validarRN087($dadosPost) {

        $tipoImportacao = new Zend_Session_Namespace('tipoImportacao');
        $tipo = $tipoImportacao->tipo;

        // formata ano e mês para sql
        $ano = substr($dadosPost['IMPA_AA_IMPORTACAO'], 2, 2);
        $mes = $dadosPost['IMPA_IC_MES'];

        // db para apagar os dados caso exista repetido
        $tabelaIMPO = new Orcamento_Model_DbTable_Importacao();
        $tabelaIMPA = new Orcamento_Model_DbTable_ImportarArquivo();

        $whereIMPA = "IMPA_IC_MES = '{$mes}'"
                . " AND IMPA_AA_IMPORTACAO = '{$ano}'"
                . " AND IMPA_IC_TP_ARQUIVO = '{$tipo}'";

        // efetua select de verificação
        $sql = "
            SELECT
                IMPA_ID_IMPORT_ARQUIVO ID
            FROM
                CEO_TB_IMPA_IMPORTAR_ARQUIVO
            WHERE
                {$whereIMPA}
        ";

        // apaga todos os dados já existentes para sobrepor
        $db = Zend_Db_Table::getDefaultAdapter();

        try {
            // pega todos os valores
            $array = $db->fetchAll($sql);
            $totalLinhas = count($array);

            // caso não possua já inserido finaliza
            if ($totalLinhas == 0) {
                return;
            }

            // apaga todos que são da tabela importação de todos os IDs
            foreach ($array as $linha) {
                $whereIMPO = "IMPO_ID_IMPORT_ARQUIVO = {$linha['ID']}";
                $tabelaIMPO->delete($whereIMPO);
            }

            $tabelaIMPA->delete($whereIMPA);
        } catch (Zend_Exception $e) {
            throw new Zend_Exception(Orcamento_Business_Importacao_Base::MSG026);
        }
    }

    /**
     * Verifica se existe cadastro para ano, mês e tipo
     * 
     * @param array $dados
     * @return boolean
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function verificarExisteCadastro($dados) {
        $ano = substr($dados['ano'], 2, 2);
        $mes = $dados['mes'];
        $tipo = $dados['tipo'];

        $sql = "SELECT
                    IMPA_ID_IMPORT_ARQUIVO
                FROM
                    CEO_TB_IMPA_IMPORTAR_ARQUIVO
                WHERE
                    IMPA_AA_IMPORTACAO = '{$ano}'
                    AND IMPA_IC_MES = '{$mes}'
                    AND IMPA_IC_TP_ARQUIVO = {$tipo}
                ";

        $db = Zend_Db_Table::getDefaultAdapter();
        $retorno = $db->fetchAll($sql);

        return (count($retorno) > 0) ? true : false;
    }

    /**
     * Efetua quebra do buffer
     * 
     * @param int $padrao
     * @param string $arquivoTmpNome
     * @return array
     * @throws ImportBuffer_Exception_Modelo
     * @throws ImportBuffer_Exception_Arquivo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function efetuarQuebraBuffer($padrao, $arquivoTmpNome) {

        $import = new ImportBuffer_ImportBuffer();
        $import->selecionarArquivoModelo($padrao);
        $import->selecionarArquivoBuffer($arquivoTmpNome);

        // traz as informações do arquivo quebradas em array
        return $import->importar();
    }

    /**
     * Efetua validação da extensão
     * 
     * @param string $arquivoTmpNome
     * @throws ImportBuffer_Exception_Arquivo
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function efetuarValidacaoExtensao($arquivoTmpNome) {
        // verifica a extensão do arquivo
        $extArquivo = pathinfo($arquivoTmpNome, PATHINFO_EXTENSION);

        if ('txt' !== $extArquivo && 'TXT' !== $extArquivo) {
            throw new ImportBuffer_Exception_Arquivo(
                    Orcamento_Business_Importacao_Base::MSG024);
        }
    }
    
    /**
     * Efetua validação e quebra de buffer
     * 
     * @param int $padrao
     * @param string $arquivoTmpNome
     * @param string $arquivoNome
     * @param int $tipo
     * @param array $dadosPost
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function executar($padrao, $arquivoTmpNome, $arquivoNome, $tipo,
            $dadosPost) {
        
        // efetua validação de extensão
        $this->efetuarValidacaoExtensao($arquivoTmpNome);   

        // efetua validação negocial
        $this->validarNegocial($tipo, $dadosPost);

        // efetua quebra do buffer
        $dadosBuffer = $this->efetuarQuebraBuffer($padrao, $arquivoTmpNome);

        // efetua validação das regras SQL e monta array para gravação
        $impo = $this->montarSQLDados($padrao, $dadosBuffer);
        $impa = $this->montarSQLArquivo($dadosPost, $arquivoNome, $tipo);

        return array('impo' => $impo, 'impa' => $impa);

    }

}
