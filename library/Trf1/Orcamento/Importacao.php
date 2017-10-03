<?php
/**
 * Classe de importação de dados 
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Importacao
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

/**
 * @see Trf1_Orcamento_Importacao_Interface
 */
require_once 'Trf1/Orcamento/Importacao/Interface.php';

class Trf1_Orcamento_Importacao
{
	/**
	 * Adapter para a Importação
	 */
	protected $_adapter = null;
	
	/**
	 * Opções do adapter para a Importação
	 */
	protected $_adapterOpcoes = array();
	
	/**
	 * Classe construtora
	 * 
	 * @param	optional $opcoes
	 * @return	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct($opcoes = null)
	{
		if ($opcoes instanceof Zend_Config) {
			$opcoes = $opcoes->toArray();
		}
		
		if (is_string($opcoes)) {
			$this->setAdapter($opcoes);
			
		} elseif ($opcoes instanceof Trf1_Orcamento_Importacao_Interface) {
			$this->setAdapter($opcoes);
			
		} elseif (is_array($opcoes)) {
			$this->setOpcoes($opcoes);
		}
	}
	
	/**
	 * Determina as opções informadas
	 * 
	 * @param	string $opcoes
	 * @return	Trf1_Orcamento_Importacao
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function setOpcoes(array $opcoes)
	{
		foreach ($opcoes as $key => $opcao) {
			if ($key == 'opcoes') {
				$key = 'adapterOpcoes';
			}
			
			$metodo = 'set' . ucfirst($key);
			if (method_exists($this, $metodo)) {
				$this->$metodo($opcao);
			}
		}
		
		return $this;
	}
	
	/**
	 * Retorna o adapter, instanciando-o se necessário
	 *
	 * @param	none
	 * @return	Trf1_Orcamento_Importacao_Interface
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getAdapter()
	{
		if ($this->_adapter instanceof Trf1_Orcamento_Importacao_Interface) {
			return $this;
		}
		
		$adapter	= $this->_adapter;
		$opcoes		= $this->_adapterOpcoes;
		
		if (!$adapter) {
			throw new Trf1_Orcamento_Importacao_Exception('Nenhuma adaptador informado.');
		}
		
		if (!class_exists($adapter)) {
			require_once 'Zend/Loader.php';
			if (Zend_Loader::isReadable('Trf1/Orcamento/Importacao/' . ucfirst($adapter) . '.php')) {
				$adapter = 'Trf1_Orcamento_Importacao_' . ucfirst($adapter);
			}
			Zend_Loader::loadClass($adapter);
		}
		
		$this->_adapter = new $adapter($opcoes);
		if (!$this->_adapter instanceof Trf1_Orcamento_Importacao_Interface) {
			require_once 'Trf1/Orcamento/Importacao/Exception.php';
			throw new Trf1_Orcamento_Importacao_Exception('O adaptador da Importação ' . $adapter . ' não implementa Trf1_Orcamento_Importacao_Interface.');
		}
		
		return $this->_adapter;
	}
	
	/**
	 * Retorna o nome do adapter
	 * 
	 * @param	none
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getAdapterName()
	{
		//return $this->getAdapter()->toString();
	}
	
	/**
	 * Determina o adapter da Importação
	 * 
	 * @param	string|Trf1_Orcamento_Importacao $adapter
	 * @return	Trf1_Orcamento_Importacao
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function setAdapter($adapter)
	{
		if ($adapter instanceof Trf1_Orcamento_Importacao_Interface) {
			$this->_adapter = $adapter;
			return $this;
		}
		
		if (!is_string($adapter)) {
			require_once 'Trf1/Orcamento/Importacao/Exception.php';
			throw new Trf1_Orcamento_Importacao_Exception('Adaptador inválido. Adapter necessário deve ser uma string ou uma instância de Trf1_Orcamento_Importacao_Interface');
		}
		
		$this->_adapter = $adapter;
		
		return $this;
	}
	
	/**
	 * Retorna as opções para o adapter
	 * 
	 * @param	none
	 * @return	array $opcoes
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getAdapterOpcoes()
	{
		return $this->_adapterOpcoes;
	}
	
	/**
	 * Determina as opções do adapter
	 * 
	 * @param	array $opcoes
	 * @return	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function setAdapterOpcoes(array $opcoes)
	{
		$this->_adapterOpcoes = $opcoes;
		return $this;
	}
	
	public function importa($dados)
	{
		return $this->getAdapter()->importa($dados);
	}
	
	/**
	 * Chama métodos do adapter
	 * 
	 * @param	string		$metodo - Método a chamar
	 * @param	string|array	$opcoes - Opções para esse método
	 * @return	none
	 * @throws	Trf1_Orcamento_Importacao_Exception
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __call($metodo, $opcoes)
	{
		$adapter = $this->getAdapter();
		
		if (!method_exists($adapter, $metodo)) {
			require_once 'Trf1/Orcamento/Importacao/Exception.php';
			throw new Trf1_Orcamento_Importacao_Exception('Método [' . $metodo . '] desconhecido.');
		}
		return call_user_func_array(array($adapter, $metodo), $opcoes);
	}
	
}