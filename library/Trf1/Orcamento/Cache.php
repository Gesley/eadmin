<?php
/**
 * Classe para manipulação genérica de cache
 *
 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
 * @category	TRF1
 * @package		Trf1_Orcamento_Cache
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
class Trf1_Orcamento_Cache {
	/**
	 * Nome do cache utilizado no e-Orçamento
	 */
	private $_cacheNome = 'orcamento';
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		//
	}
	
	/**
	 * Ler cache por id
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	string	$cacheId
	 * @return	array	$dados
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function lerCache($cacheId) {
		$bootstrap = Zend_Controller_Front::getInstance ()->getParam ( 'bootstrap' );
		$cacheManager = $bootstrap->getResource ( 'cachemanager' );
		$cacheOrcamento = $cacheManager->getCache ( $this->_cacheNome );
		
		try {
			$dados = $cacheOrcamento->load ( $cacheId );
		} catch ( Exception $e ) {
			throw new Zend_Exception ( "Não foi possível ler o cache $cacheId. <br />" . $e->getMessage () );
		}
		
		return false;
		// return $dados;
		
		/* ************************************************************
		 * @TODO: O trecho de código abaixo deve ser removido. O correto é:
		 * 
		 * return $dados; 
		 ************************************************************ */
		if (substr ( $cacheId, 0, 20 ) == 'orcamento_permissao_') {
			// return $dados;
		} else {
			// return false;
		}
	}
	
	/**
	 * Cria novo cache
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	array	$dados
	 * @param	string	$cacheId
	 * @param	int		$cacheLifetime
	 * @return	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function criarCache($dados, $cacheId, $cacheLifetime = 0) {
		$bootstrap = Zend_Controller_Front::getInstance ()->getParam ( 'bootstrap' );
		$cacheManager = $bootstrap->getResource ( 'cachemanager' );
		$cacheOrcamento = $cacheManager->getCache ( $this->_cacheNome );
		
		// Define o tempo de vida deste cache, caso o tempo não seja infinito (0)
		if ($cacheLifetime != 0) {
			$cacheOrcamento->setLifetime ( $cacheLifetime );
		}
		
		try {
		    // Retorna instância de classe para manipulação de memória
		    $mem = Orcamento_Business_Memoria::retornaInstancia();
		    
		    // Expande a quantidade de memória disponível para essa requisição
            $mem->expandeMemoria ();
		    
		    // Salva os dados em cache
			// $cacheOrcamento->save ( $dados, $cacheId );
			
			// Restaura a quantidade original de memória
			$mem->restauraMemoria ();
		} catch ( Exception $e ) {
			throw new Zend_Exception ( "Não foi possível gravar o cache $cacheId. <br />" . $e->getMessage () );
		}
	}
	
	/**
	 * Exclui cache por id
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	string	$cacheId
	 * @return	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function excluirCache($cacheId) {
		$bootstrap = Zend_Controller_Front::getInstance ()->getParam ( 'bootstrap' );
		$cacheManager = $bootstrap->getResource ( 'cachemanager' );
		$cacheOrcamento = $cacheManager->getCache ( $this->_cacheNome );
		
		$cacheOrcamento->remove ( $cacheId );
	}
	
	/**
	 * Exclui diversos caches, conforme nível de sensibilidade dos dados
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	string	$negocio
	 * @param	int		$despesa
	 * @return	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function excluirCachesSensiveis($negocio, $despesa = 0) {
		// Informa os nomes dos caches a serem excluídos
		$cacheCombo = $this->retornaID_Combo ( $negocio );
		$cacheDespesa = $this->retornaID_Despesa ( $despesa );
		$cacheDipor = $this->gerarID_Listagem ( $negocio, array ('bDadosSensiveis' => true, 'perfil' => Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR ) );
		$cacheSeccional = $this->gerarID_Listagem ( $negocio, array ('bDadosSensiveis' => true, 'perfil' => Trf1_Orcamento_Definicoes::PERMISSAO_SECCIONAL ) );
		$cacheSecretaria = $this->gerarID_Listagem ( $negocio, array ('bDadosSensiveis' => true, 'perfil' => Trf1_Orcamento_Definicoes::PERMISSAO_SECRETARIA ) );
		
		// Exclui diversos caches, se existirem
		$this->excluirCache ( $cacheCombo );
		$this->excluirCache ( $cacheDespesa );
		$this->excluirCache ( $cacheDipor );
		$this->excluirCache ( $cacheSeccional );
		$this->excluirCache ( $cacheSecretaria );
	}
	
	/**
	 * Retorna o id de caches para Despesas únicas
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	int		$despesa
	 * @return	string	$cacheId
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaID_Despesa($despesa) {
		return 'orcamento_despesa_' . $despesa;
	}
	
	/**
	 * Retorna o id de caches para Combos
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	string	$nomeCombo
	 * @return	string	$cacheId
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaID_Combo($nomeCombo) {
		return 'orcamento_itens_combo_' . strtolower ( $nomeCombo );
	}
	
	/**
	 * Gera a $cacheId para dada listagem, conforme perfil e tipo de sensibilidade dos dados
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	string	$negocio
	 * @param	array	$opcoes		São esperados as opções: bDadosSensiveis e perfil 
	 * @return	string	$cacheId, já formatado pela função $this->retornaID_Listagem()
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function gerarID_Listagem($negocio, array $opcoes = null) {
		// Valores padrão para as $opcoes
		$bDadosSensiveis = false;
		$perfil = null;
		
		if (is_array ( $opcoes )) {
			if ($opcoes ['bDadosSensiveis']) {
				$bDadosSensiveis = $opcoes ['bDadosSensiveis'];
			}
			
			if ($opcoes ['perfil']) {
				$perfil = $opcoes ['perfil'];
			}
		}
		
		// Se os dados não forem sensíveis, retorna o próprio negócio 
		if (! $bDadosSensiveis) {
			return $this->retornaID_Listagem ( $negocio );
		}
		
		// Verifica possível restrição de registros
		$sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );
		
		// Se $perfil não for informado, busca o da sessão
		if (! $perfil) {
			$perfil = $sessaoOrcamento->perfil;
		}
		$ug = $sessaoOrcamento->ug;
		$responsavel = str_replace ( '/', '_', $sessaoOrcamento->responsavel );
		
		// Informa os nomes dos caches a serem gerados, conforme perfil
		$cacheId = '';
		$cacheDipor = $negocio;
		$cacheSeccional = $cacheDipor . '_' . $ug;
		$cacheSecretaria = str_replace ( '__', '_', $cacheSeccional . '_' . $responsavel );
		
		switch ($perfil) {
			case Trf1_Orcamento_Definicoes::PERMISSAO_CONSULTA :
			case Trf1_Orcamento_Definicoes::PERMISSAO_DIEFI :
			case Trf1_Orcamento_Definicoes::PERMISSAO_SECRETARIA :
				$cacheId = $cacheSecretaria;
				break;
			case Trf1_Orcamento_Definicoes::PERMISSAO_SECCIONAL :
				$cacheId = $cacheSeccional;
				break;
			case Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR :
			case Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR :
			default :
				$cacheId = $cacheDipor;
				break;
		}
		
		return $this->retornaID_Listagem ( $cacheId );
	}
	
	/**
	 * Retorna o id de caches para Listagens
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	string	$nomeListagem
	 * @return	string	$cacheId
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaID_Listagem($nomeListagem) {
		return 'orcamento_listagem_' . strtolower ( $nomeListagem );
	}
	
	/**
	 * Retorna o id de caches para Permissão
	 * 
	 * @deprecated Essa classe deve ser descontinuada em favor da Trf1_Cache!
	 * @param	string	$matricula
	 * @return	string	$cacheId
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaID_Permissao($matricula) {
		return 'orcamento_permissao_' . strtolower ( $matricula );
	}

}