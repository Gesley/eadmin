<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Importacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Importação (NC, NE e EXEC)
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
class Trf1_Orcamento_Negocio_Importacao {
	/* ************************************************************
	 * Definição de constantes 
	 *************************************************************/
	// Tipos de importação
	const IMPORTACAO_TIPO_NOTA_CREDITO = 1;
	const IMPORTACAO_TIPO_NOTA_EMPENHO = 2;
	const IMPORTACAO_TIPO_EXECUCAO_EMPENHO = 3;
	
	// Erros gerais
	const IMPORTACAO_ERRO_GERAL_LIMPA_ERRO = 110; /* Mensagem??? */
	const IMPORTACAO_ERRO_GERAL_IMPORTACAO_REGISTROS = 120;
	
	// Erros na validação das NCs
	const IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_NAO_IDENTIFICADA = 301;
	const IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_EXISTE = 302;
	const IMPORTACAO_ERRO_NC_UG_OPERADOR = 303;
	const IMPORTACAO_ERRO_NC_UG_FAVORECIDA = 304;
	const IMPORTACAO_ERRO_NC_FONTE = 305;
	const IMPORTACAO_ERRO_NC_PTRES = 306;
	const IMPORTACAO_ERRO_NC_NATUREZA = 307;
	const IMPORTACAO_ERRO_NC_VINCULACAO = 308; /* ??? */
	const IMPORTACAO_ERRO_NC_CATEGORIA = 309; /* ??? */
	const IMPORTACAO_ERRO_NC_EVENTO = 310;
	
	/* ************************************************************
	 * Variáveis e funções 'básicas'
	 *************************************************************/
	/**
	 * Model das Notas de Crédito
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbNocrNotaCredito ();
	}
	
	/* ************************************************************
	 * Funções genéricas
	 *************************************************************/
	/**
	 * Retorna a pasta onde os arquivos recem importados ficarão armazenados
	 * 
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaPastaImportacao() {
		return APPLICATION_PATH . '/data/ceo/import';
	}
	
	/**
	 * Retorna as opções de importações disponíveis e implementadas
	 * 
	 * @param	none
	 * @return	array	$opcoes
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaOpcoesImportacao() {
		$opcoes = array (self::IMPORTACAO_TIPO_NOTA_CREDITO => 'Nota de crédito (SIAFI)', self::IMPORTACAO_TIPO_NOTA_EMPENHO => 'Nota de empenho (SIAFI)', self::IMPORTACAO_TIPO_EXECUCAO_EMPENHO => 'Execução das notas de empenho (SIAFI)' );
		$opcoes = array (self::IMPORTACAO_TIPO_NOTA_CREDITO => 'Nota de crédito (SIAFI)', self::IMPORTACAO_TIPO_NOTA_EMPENHO => 'Nota de empenho (SIAFI)' );
		//$opcoes = array (self::IMPORTACAO_TIPO_NOTA_CREDITO => 'Nota de crédito (SIAFI)' );
		
		return $opcoes;
	}
	
	/**
	 * Retorna as opções de importações disponíveis e implementadas
	 * 
	 * @param	integer	$opcao
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaNomeImportacao($opcao = 0) {
		$tipoImportacao [0] = '';
		$tipoImportacao [self::IMPORTACAO_TIPO_NOTA_CREDITO] = 'nc';
		$tipoImportacao [self::IMPORTACAO_TIPO_NOTA_EMPENHO] = 'ne';
		$tipoImportacao [self::IMPORTACAO_TIPO_EXECUCAO_EMPENHO] = 'exec';
		
		return $tipoImportacao [$opcao];
	}
	
	/* ************************************************************
	 * Funções para exibição de erros de importação a serem tratados 
	 *************************************************************/
	/**
	 * Retorna array com registros contendo erro após última importação
	 *
	 * @return	array	$dados
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaListagemErros() {
		$sql = "
SELECT
	IMPD_ID_LINHA,
	CASE IMPD_NR_ERRO
		-- Erros de NC
		WHEN " . self::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_NAO_IDENTIFICADA . " THEN '" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_NAO_IDENTIFICADA ) . "'
		WHEN " . self::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_EXISTE . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_EXISTE ) . "', 'NNNCCC',  SUBSTR(IMPD_TX_LINHA, 30, 12) || SUBSTR(IMPD_TX_LINHA, 50, 6))
		WHEN " . self::IMPORTACAO_ERRO_NC_UG_OPERADOR . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_UG_OPERADOR ) . "', 'UGOPERADOR', SUBSTR(IMPD_TX_LINHA, 13, 6))
		WHEN " . self::IMPORTACAO_ERRO_NC_UG_FAVORECIDA . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_UG_FAVORECIDA ) . "', 'UGFAVORECIDA', SUBSTR(IMPD_TX_LINHA, 50, 6))
		WHEN " . self::IMPORTACAO_ERRO_NC_FONTE . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_FONTE ) . "', 'FONTE', SUBSTR(IMPD_TX_LINHA, 304, 3))
		WHEN " . self::IMPORTACAO_ERRO_NC_PTRES . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_PTRES ) . "', 'PPTTRRES', SUBSTR(IMPD_TX_LINHA, 297, 6))
		WHEN " . self::IMPORTACAO_ERRO_NC_NATUREZA . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_NATUREZA ) . "', 'NATUREZA', SUBSTR(IMPD_TX_LINHA, 313, 6))
		WHEN " . self::IMPORTACAO_ERRO_NC_VINCULACAO . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_VINCULACAO ) . "', 'VINCULACAO', 'não informada')
		WHEN " . self::IMPORTACAO_ERRO_NC_CATEGORIA . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_CATEGORIA ) . "', 'CATEGORIA', 'não informada')
		WHEN " . self::IMPORTACAO_ERRO_NC_EVENTO . " THEN REPLACE('" . $this->retornaMensagemErro ( self::IMPORTACAO_ERRO_NC_EVENTO ) . "', 'EVENTO', SUBSTR(IMPD_TX_LINHA, 290, 6))
		ELSE IMPD_NR_ERRO || ' - Erro não identificado ' 
	END IMPD_NR_ERRO,
	IMPD_DS_CLASSE_ARQUIVO,
	TO_CHAR(IMPD_DH_IMPORTACAO, 'YYYY-MM-DD-HH24:MI:SS') IMPD_DH_IMPORTACAO,
	IMPD_DS_ARQUIVO_ORIGEM
FROM
	CEO_TB_IMPD_IMPORTACAO_DADOS
WHERE
	IMPD_NR_ERRO IS NOT NULL
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$dados = $banco->fetchAll ( $sql );
		
		return $dados;
	}
	
	/**
	 * Retorna mensagem de erro conforme o código informado
	 *
	 * @param	integer	$codigoErro
	 * @return	string	$mensagem
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaMensagemErro($codigoErro) {
		$mensagem = 'Mensagem de erro não disponível.';
		
		$erroValidacao [self::IMPORTACAO_ERRO_GERAL_LIMPA_ERRO] = 'sem mensagem';
		$erroValidacao [self::IMPORTACAO_ERRO_GERAL_IMPORTACAO_REGISTROS] = 'sem mensagem';
		
		$erroValidacao [self::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_NAO_IDENTIFICADA] = 'Não foi identificada uma nota de crédito válida. Favor excluir este registro.';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_CHAVE_PRIMARIA_EXISTE] = 'Nota de crédito NNNCCC já importada. Favor excluir este registro.';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_UG_OPERADOR] = 'UG Operador UGOPERADOR não cadastrada no sistema. Favor [AHREF= orcamento/ug/incluir =AHREF]incluir a unidade gestora[/A].';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_UG_FAVORECIDA] = 'UG Favorecida UGFAVORECIDA não cadastrada no sistema. Favor [AHREF= orcamento/ug/incluir =AHREF]incluir a unidade gestora[/A].';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_FONTE] = 'Fonte FONTE não cadastrada no sistema. Favor [AHREF= orcamento/fonte/incluir =AHREF]incluir a fonte[/A].';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_PTRES] = 'PTRES PPTTRRES não cadastrado no sistema. Favor [AHREF= orcamento/ptres/incluir =AHREF]incluir o programa de trabalho resumido[/A].';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_NATUREZA] = 'Natureza NATUREZA não cadastrada no sistema. Favor [AHREF= orcamento/elemento/incluir =AHREF]incluir a natureza da despesa[/A].';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_VINCULACAO] = 'Vinculação VINCULACAO não cadastrada no sistema. Favor [AHREF= orcamento/vinculacao/incluir =AHREF]incluir a vinculação[/A].';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_CATEGORIA] = 'Categoria CATEGORIA não cadastrada no sistema. Favor [AHREF= orcamento/categoria/incluir =AHREF]incluir a categoria[/A].';
		$erroValidacao [self::IMPORTACAO_ERRO_NC_EVENTO] = 'Evento EVENTO não cadastrado no sistema. Favor [AHREF= orcamento/evento/incluir =AHREF]incluir o evento[/A].';
		
		if (array_key_exists ( $codigoErro, $erroValidacao )) {
			$mensagem = $codigoErro . ' - ' . $erroValidacao [$codigoErro];
		}
		
		return $mensagem;
	}
	
	/* ************************************************************
	 * Funções para validações de dados
	 *************************************************************/
	/**
	 * Valida o arquivo .ref para permitir ou não a importação de dados
	 * 
	 * @param	string				$tipoImportacao
	 * @param	array				$nomeArquivoREF
	 * @return	array				$dadosREF
	 * @throws	Zend_Exception		Erro tratado com mensagem amigável
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function validaRef($tipoImportacao, $nomeArquivoREF) {
		$bErro = false;
		
		// Busca dados do REF armazenado no banco
		$dadosREF = $this->retornaREFdoBanco ( $tipoImportacao );
		
		// Lê arquivo REF recem importado
		$arquivoREF = file ( $this->retornaPastaImportacao () . DIRECTORY_SEPARATOR . $nomeArquivoREF, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		
		// Valida arquivo REF. Todos os registros são lidos para, se for o caso, exibir campos desejados do arquivo
		$i = 0;
		$campos = '';
		
		foreach ( $dadosREF as $registro ) {
			$campos .= $registro ['CAMPO'] . '<br />';
			
			// Verifica se cada registro está presente e na ordem dentro do REF importado
			if (strpos ( $arquivoREF [$i ++], $registro ['CAMPO'] ) === false) {
				// Senão, volta erro!
				$bErro = true;
			}
		}
		
		// Retorna erro se validação dos dados falhar
		if ($bErro) {
			$msgErro = "Arquivo de referência (.ref) deve conter os seguintes campos: <br />";
			$msgErro .= $campos;
			throw new Zend_Exception ( $msgErro );
		}
		
		return $dadosREF;
	}
	
	/**
	 * Retorna array dados sobre o arquivo REF em vigor
	 * 
	 * @param	string	$tipoImportacao
	 * @throws	Zend_Exception		Erro tratado com mensagem amigável
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaREFdoBanco($tipoImportacao) {
		try {
			$classe = strtoupper ( $classe );
			
			// Retorna dados sobre arquivo REF
			$sql = "
SELECT
	IMPC_DS_CAMPO		CAMPO,
	IMPC_NU_INICIO		INICIO,
	IMPC_NU_TAMANHO		TAMANHO
FROM
	CEO_TB_IMPC_IMPORTA_CAMPO
WHERE
	IMPC_DS_CLASSE_ARQUIVO = '$tipoImportacao'
ORDER BY
	IMPC_ID_CAMPO
					";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$dados = $banco->fetchAll ( $sql );
			
			return $dados;
		} catch ( Zend_Exception $e ) {
			$msgErro = "Buscar dados do arquivo de referência (.REF) para conferência.<br />" . PHP_EOL . $e->getMessage ();
			throw new Zend_Exception ( $msgErro );
		}
	}
	
	/**
	 * Separa o array informado num array mais amigável para trabalho na função de importação
	 * 
	 * @param	array	$dadosREF
	 * @return	array	$ref
	 * @throws	Zend_Exception
	 * @see		$this->importaDados()
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaArrayReferencia($dadosREF) {
		try {
			foreach ( $dadosREF as $registro => $campo ) {
				$referencia [$campo ['CAMPO']] ['INICIO'] = $campo ['INICIO'];
				$referencia [$campo ['CAMPO']] ['TAMANHO'] = $campo ['TAMANHO'];
			}
		} catch ( Exception $e ) {
			$msgErro = "Separar os dados do arquivo de referência.<br />" . PHP_EOL . $e->getMessage ();
			throw new Zend_Exception ( $msgErro );
		}
		
		return $referencia;
	}
	
	/* ************************************************************
	 * Funções para importação de dados
	 *************************************************************/
	/**
	 * Função geral de importação
	 * 
	 * @param	int		$tipoImportacao
	 * @param	array	$arquivos
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function importacao($tipoImportacao, $arquivos = null) {
		$funcao = '<strong>Função: </strong>';
		$sArquivos = 'sem';
		
		$msgErro = '';
		
		try {
			$tipoImportacao = strtoupper ( $tipoImportacao );
			
			if ($arquivos != null) {
				$sArquivos = 'com';
				
				// @TODO: Ver em casos com mais de 2 arquivos upados por vez...
				// Verifica nomes e tipos dos arquivos recem importados
				$msgErro = 'Valida arquivo de dados (.txt) e de referência (.ref)';
				if (pathinfo ( $arquivos ['TEXTO_0_'], PATHINFO_EXTENSION ) == 'txt') {
					$nomeArquivoTXT = $arquivos ['TEXTO_0_'];
					$nomeArquivoREF = $arquivos ['TEXTO_1_'];
				} else {
					$nomeArquivoTXT = $arquivos ['TEXTO_1_'];
					$nomeArquivoREF = $arquivos ['TEXTO_0_'];
				}
				
				// Efetua a validação do arquivo REF. Se houver erro, a função gera uma exceção
				$msgErro = 'Validação do arquivo de referência (.ref)';
				$dadosREF = $this->validaREF ( $tipoImportacao, $nomeArquivoREF );
				
				// Importa novos registros
				$msgErro = 'Importação do arquivo de dados (.txt)';
				$this->importaArquivoTexto ( $tipoImportacao, $nomeArquivoTXT );
			}
			
			if (! is_array ( $dadosREF )) {
				// Busca dados do REF armazenado no banco
				$msgErro = '';
				$dadosREF = $this->retornaREFdoBanco ( $tipoImportacao );
			}
			
			// Busca um array mais amigável para uso
			$msgErro = '';
			$referencia = $this->retornaArrayReferencia ( $dadosREF );
			
			// Verifica a quantidade de linhas a importar
			$msgErro = 'Busca a quantidade de registros a importar';
			$qtdeRegistros = $this->retornaQtdeRegistros ( $tipoImportacao );
			
			// Realiza a validação dos registros a importar
			$msgErro = 'Acessa as regras negociais específicas';
			$negocioImportacao = $this->retornaClasseNegocioImportacao ( $tipoImportacao );
			
			$msgErro = 'Valida os registros a serem importados definitivamente';
			$negocioImportacao->validaRegistros ();
			
			// Importa dados sem restrições de validação
			$msgErro = 'Importa os dados pré-validados definitivamente';
			$negocioImportacao->importaDados ( $referencia );
		
		} catch ( Exception $e ) {
			$msgErro = $funcao . $msgErro . "<br />" . PHP_EOL . $e->getMessage ();
			throw new Zend_Exception ( $msgErro );
		}
	}
	
	/**
	 * Realiza a inserção de registros vindos do arquivo .txt para importação
	 * 
	 * @param	string	$tipoImportacao
	 * @param	array	$nomeArquivoTXT
	 * @throws	Zend_Exception		Erro tratado com mensagem amigável
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function importaArquivoTexto($tipoImportacao, $nomeArquivoTXT) {
		try {
			// Busca matrícula do usuário logado
			$sessao = new Zend_Session_Namespace ( 'userNs' );
			$matricula = strtoupper ( $sessao->matricula );
			
			// Lê arquivo TXT recem importado
			$arquivoTXT = file ( $this->retornaPastaImportacao () . DIRECTORY_SEPARATOR . $nomeArquivoTXT, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
			
			// Monta instrução SQL para inclusão de linhas no banco 
			$sql = "BEGIN ";
			
			/*
			 * No caso de importação de um arquivo com muitos registros essa 
			 * rotina dá erro na execução da query devido ao tamanho.
			 * Assim, foi implementado um contador e a cada X linhas do 
			 * arquivo a query é executada e a instrução SQL é zerada.
			 * 
			 * Limites identificados:
			 * 2000 linhas para arquivo de NC;
			 * 0 linhas para arquivo de NE;
			 * 0 linhas para arquivo de EXEC;
			 * 
			 */
			$iQtdeLimiteDeLinhas = 2000;
			$yLinha = 1;
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			foreach ( $arquivoTXT as $linha ) {
				$linha = str_replace ( "'", "´", $linha );
				
				$sql .= "INSERT INTO CEO_TB_IMPD_IMPORTACAO_DADOS ( IMPD_ID_LINHA, IMPD_DH_IMPORTACAO, IMPD_CD_MATRICULA, IMPD_DS_CLASSE_ARQUIVO, IMPD_DS_ARQUIVO_ORIGEM, IMPD_TX_LINHA ) VALUES ( CEO_SQ_IMPD.NEXTVAL, SYSDATE, '$matricula', '$tipoImportacao', '$nomeArquivoTXT', '$linha' ); ";
				
				// Verifica se chegou ao limite de linhas
				if ($iQtdeLimiteDeLinhas == $yLinha ++) {
					$sql .= "END; ";
					$banco->query ( $sql );
					
					$sql = "BEGIN ";
					$yLinha = 1;
				}
			}
			
			$sql .= "END; ";
			
			/*
			Zend_Debug::dump($sql);
			exit;
			*/
			
			$banco->query ( $sql );
		} catch ( Exception $e ) {
			$msgErro = "Inclusão de registros brutos para validação que antecede a importação.<br />" . PHP_EOL . $e->getMessage ();
			throw new Zend_Exception ( $msgErro );
		}
	}
	
	/**
	 * Retorna a quantidade de registros a importar por tipo de arquivo.
	 * É possível que essa quantidade seja maior que o total de linhas do
	 * arquivo pela pré-existência de registros com erros em importações
	 * anteriores.
	 * 
	 * @param	string	$tipoImportacao
	 * @return	int		$qtde
	 * @throws	Zend_Exception		Erro tratado com mensagem amigável
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaQtdeRegistros($tipoImportacao) {
		try {
			$sql = "
SELECT
	NVL(COUNT(IMPD_ID_LINHA), 0)	QTDE
FROM
	CEO_TB_IMPD_IMPORTACAO_DADOS
WHERE
	IMPD_DS_CLASSE_ARQUIVO			= '$tipoImportacao'
					";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$qtde = $banco->fetchOne ( $sql );
			
			return $qtde;
		} catch ( Zend_Exception $e ) {
			$msgErro = "Realizar a contagem de registros a importar.<br />" . PHP_EOL . $e->getMessage ();
			throw new Zend_Exception ( $msgErro );
		}
	}
	
	private function retornaClasseNegocioImportacao($tipoImportacao) {
		$eu = 'Trf1_Orcamento_Negocio_Importacao';
		$classe = $eu . '_' . ucfirst ( strtolower ( $tipoImportacao ) );
		
		try {
			$negocioImportacao = new $classe ();
		} catch ( Exception $e ) {
			throw new Zend_Exception ( "Erro ao carregar a classe $classe." );
		}
		
		return $negocioImportacao;
	}

}