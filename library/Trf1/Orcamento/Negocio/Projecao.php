<?php

/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Projecao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Bloqueio de Movimentação
 * 
 * ====================================================================================================
 * LICENSA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * ====================================================================================================
 * TUTORIAL
 * ====================================================================================================
 * Descrever...
 * 
 */
class Trf1_Orcamento_Negocio_Projecao {

    /**
     * Model dos Tipos de Solicitações
     */
    protected $_dados = null;

    /**
     * Classe construtora
     * 
     * @param	none
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function __construct () {
        $this->_dados = new Application_Model_DbTable_Orcamento_CeoTbProjProjecao();
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     * 
     * @return	array		Chave primária ou composta
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function tabela () {
        return $this->_dados;
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     * 
     * @return	array		Chave primária ou composta
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function chavePrimaria () {
        return $this->_dados->chavePrimaria();
    }

    /**
     * Retorna array com campos e registros desejados
     *
     * @param	none
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaListagem ($despesa) {
        // Valida parâmetros
        $condicaoDespesa = "";
        if ($despesa) {
            $condicaoDespesa = " AND DESP.DESP_NR_DESPESA IN ( $despesa ) ";
        }

        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        $sql = "
SELECT
	DESP.DESP_NR_DESPESA													AS PROJ_NR_DESPESA,
	EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' || DESP.DESP_DS_ADICIONAL	AS DS_DESPESA,
	REPLACE(NVL(Sum(DETALHE.VR_JANEIRO), 0), ',', '.')						AS PROJ_VR_JANEIRO,
	REPLACE(NVL(Sum(DETALHE.VR_FEVEREIRO), 0), ',', '.')					AS PROJ_VR_FEVEREIRO,
	REPLACE(NVL(Sum(DETALHE.VR_MARCO), 0), ',', '.')						AS PROJ_VR_MARCO,
	REPLACE(NVL(Sum(DETALHE.VR_ABRIL), 0), ',', '.')						AS PROJ_VR_ABRIL,
	REPLACE(NVL(Sum(DETALHE.VR_MAIO), 0), ',', '.')							AS PROJ_VR_MAIO,
	REPLACE(NVL(Sum(DETALHE.VR_JUNHO), 0), ',', '.')						AS PROJ_VR_JUNHO,
	REPLACE(NVL(Sum(DETALHE.VR_JULHO), 0), ',', '.')						AS PROJ_VR_JULHO,
	REPLACE(NVL(Sum(DETALHE.VR_AGOSTO), 0), ',', '.')						AS PROJ_VR_AGOSTO,
	REPLACE(NVL(Sum(DETALHE.VR_SETEMBRO), 0), ',', '.')						AS PROJ_VR_SETEMBRO,
	REPLACE(NVL(Sum(DETALHE.VR_OUTUBRO), 0), ',', '.')						AS PROJ_VR_OUTUBRO,
	REPLACE(NVL(Sum(DETALHE.VR_NOVEMBRO), 0), ',', '.')						AS PROJ_VR_NOVEMBRO,
	REPLACE(NVL(Sum(DETALHE.VR_DEZEMBRO), 0), ',', '.')						AS PROJ_VR_DEZEMBRO
FROM
	CEO_TB_DESP_DESPESA														DESP
Left JOIN
	(
	SELECT
		PROJ_NR_DESPESA,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_JANEIRO,
		0				AS VR_FEVEREIRO,
		0				AS VR_MARCO,
		0				AS VR_ABRIL,
		0				AS VR_MAIO,
		0				AS VR_JUNHO,
		0				AS VR_JULHO,
		0				AS VR_AGOSTO,
		0				AS VR_SETEMBRO,
		0				AS VR_OUTUBRO,
		0				AS VR_NOVEMBRO,
		0				AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 01
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 02
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 03
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 04
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 05
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 06
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 07
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 08
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 09
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 10
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_NOVEMBRO,
		0																	AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 11
	
	UNION ALL
	
	SELECT
		PROJ_NR_DESPESA,
		0																	AS VR_JANEIRO,
		0																	AS VR_FEVEREIRO,
		0																	AS VR_MARCO,
		0																	AS VR_ABRIL,
		0																	AS VR_MAIO,
		0																	AS VR_JUNHO,
		0																	AS VR_JULHO,
		0																	AS VR_AGOSTO,
		0																	AS VR_SETEMBRO,
		0																	AS VR_OUTUBRO,
		0																	AS VR_NOVEMBRO,
		NVL(PROJ_VL_PROJECAO, 0)											AS VR_DEZEMBRO
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO													= 12
	)																		DETALHE ON
		DETALHE.PROJ_NR_DESPESA												= DESP.DESP_NR_DESPESA  
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP											EDSB ON
		EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB									= DESP.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
		RESP.RESP_CD_RESPONSAVEL			= DESP.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
		RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO					AND
		RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
WHERE
	DESP.DESP_DH_EXCLUSAO_LOGICA											IS Null
	$condicaoDespesa
	$condicaoResponsaveis					
GROUP BY
	DESP.DESP_NR_DESPESA,
	EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB,
	DESP.DESP_DS_ADICIONAL
				";

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchAll($sql);

        return $dados;
    }

    /**
     * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
     *
     * @param	int		$tiposolicitacao			Chave primária para busca do registro
     * @return	array
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaProjecao ($despesa) {
        $sql = "
SELECT
	$despesa																					AS PROJ_NR_DESPESA,
	TO_CHAR(NVL(VR_JANEIRO, 0),		'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_JANEIRO,
	TO_CHAR(NVL(VR_FEVEREIRO, 0),	'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_FEVEREIRO,
	TO_CHAR(NVL(VR_MARCO, 0),		'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_MARCO,
	TO_CHAR(NVL(VR_ABRIL, 0),		'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_ABRIL,
	TO_CHAR(NVL(VR_MAIO, 0),		'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_MAIO,
	TO_CHAR(NVL(VR_JUNHO, 0),		'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_JUNHO,
	TO_CHAR(NVL(VR_JULHO, 0),		'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_JULHO,
	TO_CHAR(NVL(VR_AGOSTO, 0),		'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_AGOSTO,
	TO_CHAR(NVL(VR_SETEMBRO, 0),	'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_SETEMBRO,
	TO_CHAR(NVL(VR_OUTUBRO, 0),		'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_OUTUBRO,
	TO_CHAR(NVL(VR_NOVEMBRO, 0),	'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_NOVEMBRO,
	TO_CHAR(NVL(VR_DEZEMBRO, 0),	'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')		AS PROJ_VR_DEZEMBRO,
	TO_CHAR(
	NVL(VR_JANEIRO, 0)	+ NVL(VR_FEVEREIRO, 0)	+ NVL(VR_MARCO, 0)		+ NVL(VR_ABRIL, 0) +
	NVL(VR_MAIO, 0)		+ NVL(VR_JUNHO, 0)		+ NVL(VR_JULHO, 0)		+ NVL(VR_AGOSTO, 0) +
	NVL(VR_SETEMBRO, 0)	+ NVL(VR_OUTUBRO, 0)	+ NVL(VR_NOVEMBRO, 0)	+ NVL(VR_DEZEMBRO, 0),
	'" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')										AS PROJ_VR_TOTAL
FROM
	(
	SELECT
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 01)	AS VR_JANEIRO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 02)	AS VR_FEVEREIRO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 03)	AS VR_MARCO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 04)	AS VR_ABRIL,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 05)	AS VR_MAIO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 06)	AS VR_JUNHO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 07)	AS VR_JULHO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 08)	AS VR_AGOSTO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 09)	AS VR_SETEMBRO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 10)	AS VR_OUTUBRO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 11)	AS VR_NOVEMBRO,
		(SELECT PROJ_VL_PROJECAO FROM CEO_TB_PROJ_PROJECAO WHERE PROJ_NR_DESPESA = $despesa AND PROJ_MM_PROJECAO = 12)	AS VR_DEZEMBRO
	FROM DUAL
	)
				";
        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Retorna as justificativas da projeção de uma dada despesa
     * 
     * @param	array	$despesa		Código da Despesa
     * @return	array	$justificativas	Justificativas das projeções de uma dada despesa
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaJustificativas ($despesa) {
        $sql = "
SELECT
	PRJJ_NR_DESPESA,
	TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA . "')	AS DATA_JUSTIFICATIVA,
	PRJJ_DS_JUSTIFICATIVA,
	CASE PRJJ_IC_ORIGEM
		WHEN 0 THEN 'Justificativa original '
		WHEN 1 THEN 'Motivação setorial '
	END																						AS ORIGEM
FROM
	CEO_TB_PRJJ_JUSTIF_PROJECAO
WHERE
	PRJJ_NR_DESPESA				= $despesa 													AND
	PRJJ_DH_EXCLUSAO_LOGICA		IS NULL
ORDER BY
	PRJJ_DH_JUSTIFICATIVA		DESC
				";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchAll($sql);
    }

    /**
     * Insere os 12 registros da projeção, sendo 1 para cada mês, com valor zerado
     *
     * @param	int		$despesa			Código da despesa desejada
     * @return	none
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function insereProjecao ($despesa) {
        $formatoBanco = new Trf1_Orcamento_Valor();

        $sql = "BEGIN ";

        for ($i = 1; $i <= 12; $i++) {
            $sql .= "INSERT INTO CEO_TB_PROJ_PROJECAO (PROJ_NR_DESPESA, PROJ_MM_PROJECAO, PROJ_VL_PROJECAO) VALUES ($despesa, $i, 0) ; ";
        }

        $sql .= "END; ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        //exit($sql);
        $banco->query($sql);
    }

    /**
     * Atualiza os dados da projeção de uma dada despesa
     *
     * @param array $dados Código da Despesa e os valores da projeção da mesma
     * @return none
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function atualizaProjecao ($dados) {

        try {

            $formatoBanco = new Trf1_Orcamento_Valor();

            # ajusta formato dos dados para salvar no banco.
            foreach ($dados as $chave => $valor) {

                $valores[] = $formatoBanco->retornaValorParaBancoRod($valor);
            }

            $banco = Zend_Db_Table::getDefaultAdapter();
            $banco->beginTransaction();

            $despesa = $dados['PROJ_NR_DESPESA'];

            # verifica se a projeção já está cadastrada.
            $verifica = $banco->fetchRow("select PROJ_NR_DESPESA from CEO_TB_PROJ_PROJECAO where PROJ_NR_DESPESA = $despesa");

            # define o mês de inicio.
            $mes = 1;

            # se a projeção já estiver cadastrada apenas atualiza.
            if ($verifica) {

                # percorre os valores e faz a inserção no banco para cada mês do ano.
                foreach ($valores as $val) {

                    # faz a inserção até o mês dezembro.
                    if ($mes <= 12) {

                        $sql = "update CEO_TB_PROJ_PROJECAO set PROJ_VL_PROJECAO = TO_NUMBER($val) where PROJ_NR_DESPESA = $despesa and PROJ_MM_PROJECAO = $mes";
                        $banco->query($sql);
                        $mes ++;
                    }
                }

                # se não existir insere uma projeção para a despesa.
            } else {

                # percorre os valores e faz a inserção no banco para cada mês do ano.
                foreach ($valores as $val) {

                    # faz a inserção até o mês dezembro.
                    if ($mes <= 12) {

                        $sql = "insert into CEO_TB_PROJ_PROJECAO (PROJ_NR_DESPESA, PROJ_MM_PROJECAO, PROJ_VL_PROJECAO) values($despesa, $mes, $val)";
                        $banco->query($sql);
                        $mes ++;
                    }
                }
            }

            $banco->commit();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
