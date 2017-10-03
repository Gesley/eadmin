<?php
/**
 * Classe para adequação das permissões do e-Orçamento, em relação aos dados
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Permissao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * ====================================================================================================
 * LICENSA (português)
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * @tutorial
 * a descrever...
 */
class Trf1_Orcamento_Permissao {
	/**
	 * Variável de sessão
	 */
	protected $_sessao = null;
	
	/**
	 * Classe construtora
	 * 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		// Busca dados da sessão
		$this->_sessao = new Zend_Session_Namespace ( 'userNs' );
	}
	
	/**
	 * Verifica as permissões de dado usuário, e define se é DIPOR, e senão, define os registros que podem ser exibidos
	 * 
	 * @deprecated	NÃO UTILIZAR MAIS ESSA FUNÇÃO!
	 * 
	 * @see		Orcamento_Bootstrap
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function definePermissoes() {
		$dados = $this->buscaPermissoesEmCache ();
		
		// Verifica se o usuário pe DIPOR (não gera Zend_Exception)...
		$this->definePermissao_GrupoDIPOR ( $dados );
		
		// ...Verifica se o usuário possui diferentes níveis de permissão para uma mesma lotação (gera Zend_Exception)...
		$this->definePermissao_ErroDuplicidade ( $dados );
		
		// ...Cria variável contendo as restrições por registro, se aplicável (não gera Zend_Exception)
		$this->definePermissao_MontaCondicaoRegistros ( $dados );
	}
	
	/**
	 * Verifica as permissões de dado usuário, e define se é DIPOR, e senão, define os registros que podem ser exibidos
	 *
	 * @deprecated	NÃO UTILIZAR MAIS ESSA FUNÇÃO!
	 * 
	 * @throws Exception
	 * @return	array	$dados (se houver!)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function buscaPermissoesEmCache() {
		// Busca permissões para o usuário logado
		$matricula = $this->_sessao->matricula;
		
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->retornaID_Permissao ( $matricula );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados == false) {
			throw new Zend_Exception ( 'Não foram encontradas as informações de permissões de acesso ao e-Orçamento para este usuário. Tente novo login no sistema.' );
		}
		
		return $dados;
	}
	
	/**
	 * Define se o usuário possui ou não permissão permissão da DIPOR. Cria a constante CEO_NIVEL_PERMISSAO_DIPOR com true ou false
	 * 
	 * @deprecated	NÃO UTILIZAR MAIS ESSA FUNÇÃO!
	 * 
	 * @param	array	$dados
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function definePermissao_GrupoDIPOR($dados) {
		$ehDipor = false;
		for($i = 0; $i < count ( $dados ); $i ++) {
			//foreach ($dados as $nivelPermissao) {
			if ($dados [$i] ['PERF_DS_PERFIL'] == 'CEO-DIPOR') {
				$ehDipor = true;
				unset ( $dados [$i] );
				
				// Sai do for...
				break;
			}
		}
		
		define ( 'CEO_NIVEL_PERMISSAO_DIPOR', $ehDipor );
	}
	
	/**
	 * Verifica se em uma dada lotação há diferentes tipos de permissão
	 * 
	 * @deprecated	NÃO UTILIZAR MAIS ESSA FUNÇÃO!
	 * 
	 * @param	array		$dados
	 * @throws	Exception	Não é permitido que um usuário tenha diferentes níveis de permissão para uma mesma lotação no sistema e-Orçamento
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function definePermissao_ErroDuplicidade($dados) {
		$ehDipor = CEO_NIVEL_PERMISSAO_DIPOR;
		if ($ehDipor) {
			return;
		}
		
		// Monta novo array para busca de um único nível de permissão para cada lotação
		$permissoes = array ();
		for($i = 0; $i < count ( $dados ); $i ++) {
			if (! in_array ( $dados [$i] ['UNPE_SG_SECAO'] . $dados [$i] ['UNPE_CD_LOTACAO'], $permissoes )) {
				$permissoes [$i] = $dados [$i] ['UNPE_SG_SECAO'] . $dados [$i] ['UNPE_CD_LOTACAO'];
			} else {
				throw new Zend_Exception ( 'Não é permitido que um usuário tenha diferentes níveis de permissão para uma mesma lotação no sistema e-Orçamento.' );
				break;
			}
		}
	}
	
	/**
	 * Caso não seja DIPOR, define os registros a serem exibidos (de acordo com a condição a ser inserida nas instruções Sqls relacionadas)
	 * 
	 * @TODO: Talvez cria uma nova função para verificar se LOTA_SIGLA_SECAO e LOTA_COD_LOTACAO estão contidos no resultado da função RH_SIGLAS_FAMILIA_CENTR_LOTA
	 * 
	 * @deprecated	NÃO UTILIZAR MAIS ESSA FUNÇÃO!
	 * 
	 * @param	array		$dados
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function definePermissao_MontaCondicaoRegistros($dados) {
		/* ************************************************************
		 * Caso não seja DIPOR, define os registros a serem exibidos (de acordo com a condição a ser inserida nas instruções Sqls relacionadas)
		************************************************************ */
		$ehDipor = CEO_NIVEL_PERMISSAO_DIPOR;
		
		if ($ehDipor == true) {
			define ( 'CEO_PERMISSAO_RESPONSAVEIS', '' );
			return false;
		}
		
		// Inicia a montagem da condição para verificação de quais registros devem ser exibidos, conforme lotação / nível de permissão
		$condicao = " ( ";
		
		foreach ( $dados as $acesso ) {
			// Para cada lotação em que o usuário tem permissão para ver registros, uma nova linha de condição
			$condicao .= " RH_CODIGO_FAMILIA_CENTR_LOTA(RESP.RESP_DS_SECAO, RESP.RESP_CD_LOTACAO) Like '%/" . $acesso ['UNPE_SG_SECAO'] . $acesso ['UNPE_CD_LOTACAO'] . "%' OR ";
		}
		
		// Remove último OR
		$condicao = substr ( $condicao, 0, - 3 );
		
		// Finaliza a condição
		$condicao .= " ) ";
		
		if ($ehDipor != true) {
			$condicao = 'AND' . $condicao;
		}
		$condicao = '';
		define ( 'CEO_PERMISSAO_RESPONSAVEIS', $condicao );
	}
	
	/**
	 * Verifica as permissões de dado usuário, e define se é DIPOR, e senão, define os registros que podem ser exibidos
	 * 
	 * @deprecated	NÃO UTILIZAR MAIS ESSA FUNÇÃO!
	 * 
	 * @param	string	$secao
	 * @param	int		$lotacao
	 * @return	int		$nivelPermisao
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaNivelPermissao($secao, $lotacao) {
		// Se for DIPOR, já retorna...
		if (CEO_NIVEL_PERMISSAO_DIPOR) {
			return Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_DIPOR;
		}
		
		// ...senão, busca os dados desta informação
		$dados = $this->buscaPermissoesEmCache ();
		
		foreach ( $dados as $acesso ) {
			if ($acesso ['UNPE_SG_SECAO'] == $secao && $acesso ['UNPE_CD_LOTACAO'] == $lotacao) {
				return $this->retornaPermissaoPorDescricao ( $acesso ['PERF_DS_PERFIL'] );
			}
		}
		
		// Se não tem nenhum nível de permissão, apresentar bloqueio!
		return Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_SEM_ACESSO;
	}
	
	private function retornaPermissaoPorDescricao($nivel) {
		$nivelDescricao ['CEO-DIPOR'] = Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_DIPOR;
		$nivelDescricao ['CEO-CONSULTA'] = Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_CONSULTA;
		$nivelDescricao ['CEO-SECCIONAL'] = Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_SECCIONAL;
		$nivelDescricao ['CEO-TRF-SECRETARIA'] = Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_TRF_SECRETARIA;
		$nivelDescricao ['CEO-TRF-DIEFI'] = Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_TRF_DIEFI;
		
		return $nivelDescricao [$nivel];
	}
	
	/*
	 * 
	 * 
	 * 
	 * 
	 * 
	 * Revisar uso e necessidade das funções abaixo mantidas provisoriamente! 
	 * 
	 * 
	 * 
	 * 
	 * 
	 */
	
	public function getResponsaveisWHERE($campoResponsavel) {
		$responsaveis = self::getResponsaveis ();
		
		if ($campoResponsavel === null) {
			$campoResponsavel = ' D.DESP_CD_RESPONSAVEL ';
		}
		
		if ($responsaveis) {
			$dados = implode ( ', ', $responsaveis );
		}
		
		if ($dados) {
			$sqlResponsaveisPermitidos = " AND $campoResponsavel In ($dados) ";
		}
		
		//exit($sqlResponsaveisPermitidos);
		return $sqlResponsaveisPermitidos;
	}
	
	/**
	 * Retorna lista de responsáveis permitidos, conforme permissao do usuário
	 * 
	 * @param	array		$permissoes			Permissões por usuário
	 * @return	string		$responsaveis		Responsáveis permitidos para uso em cláusula WHERE
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function detalhaPermissao($permissoes) {
		$negocioLotacao = new Trf1_Guardiao_Negocio_Lotacao ();
		
		$sql .= "SELECT RESP_CD_RESPONSAVEL FROM CEO_TB_RESP_RESPONSAVEL WHERE " . PHP_EOL;
		
		foreach ( $permissoes as $perfil ) {
			if (strtoupper ( $perfil ['PERF_DS_PERFIL'] ) == 'CEO-DIPOR') {
				// Se houver permissão CEO-DIPOR então o usuário pode ver todas as despesas
				// Cria o cache
				//$orcCache->criarCache("", $cacheId);
				return false;
			}
			$ceoLotacaoFilhas = $negocioLotacao->getLotacoesFilhas ( $perfil ['UNPE_SG_SECAO'], $perfil ['UNPE_CD_LOTACAO'] );
			
			foreach ( $ceoLotacaoFilhas as $filha ) {
				$filhaSecao = $filha ['LOTA_SIGLA_SECAO'];
				$filhaLotacao = $filha ['LOTA_COD_LOTACAO'];
				
				//echo $filha['LOTA_SIGLA_SECAO'] . '-' . $filha['LOTA_COD_LOTACAO'] . '<br />';
				$sql .= "(RESP_DS_SECAO = '$filhaSecao' AND RESP_CD_LOTACAO = $filhaLotacao) OR " . PHP_EOL;
			}
		}
		
		// Remove último OR
		$sql = substr ( $sql, 0, - 6 );
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		//exit($sql);
		$responsaveis = $banco->fetchCol ( $sql );
		
		return $responsaveis;
	}
	
	/**
	 * Retirar Campos correspondente ao perfil DIPOR
	 * Necessario que tenha a class DIPOR no form
	 * Pode ser informado uma exceção informando algum criterio como id , class ou type
	 * Rodrigo Mariano
	 */
	public function retiracamposDipor($formulario, $tipo = null, $valor = null) {
		if (! is_null ( $tipo && valor )) {
			foreach ( $formulario->getElements () as $controle ) {
				if (($controle->getAttrib ( 'class', 'DIPOR' )) && (! $controle->getAttrib ( 'type', 'submit' )) && (! $controle->getAttrib ( $tipo, $valor ))) {
					$formulario->removeElement ( $controle->getName () );
				}
			}
		} else {
			foreach ( $formulario->getElements () as $controle ) {
				if (($controle->getAttrib ( 'class', 'DIPOR' )) && (! $controle->getAttrib ( 'type', 'submit' ))) {
					$formulario->removeElement ( $controle->getName () );
				}
			}
		}
		return $formulario;
	}
	
	/**
	 * Define todos os campos como readonly
	 * Solução de somente leitura para Tags Select
	 * Recebe como parametro o formulario e o populate do formulario
	 * Rodrigo Mariano
	 */
	public function camposSomenteLeitura($formulario, $registro) {
		
		foreach ( $formulario->getElements () as $controle ) {
			// Evita que o botão seja Readonly
			if ($controle->getType () != 'Zend_Form_Element_Button') {
				//Força o Select ser Readonly - Ja que Html não permite  
				if ($controle->getType () == 'Zend_Form_Element_Select') {
					$nomecampo = $controle->getName ();
					$options = $controle->getMultiOptions ();
					$options = array_keys ( $options );
					foreach ( $options as $options ) {
						$valorformulario = $registro [$nomecampo];
						if ($valorformulario != $options) {
							$controle->removeMultiOption ( $options );
							$controle->setAttrib ( 'readonly', true );
							$controle->setAttrib ( 'class' , 'bloqueado');
						}
					}
				} else {
					//Define os demais campos como readonly
					$controle->setAttrib ( 'readonly', true );
					$controle->setAttrib ( 'class' , 'bloqueado');
				}
			}
		}
		return $formulario;
	}

}
