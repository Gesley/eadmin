<?php
/**
 * TRF1, Classe auxiliar para contagem de tempo
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Timer
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
class Trf1_Orcamento_Timer
{
	/**
	 * Valor definido como máximo para o tempo de resposta de uma página.
	 * 
	 * @var		TEMPO_MAXIMO_RESPOSTA int (constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	//protected $TEMPO_MAXIMO_RESPOSTA						= 2;
	protected $TEMPO_MAXIMO_RESPOSTA						= 3;
	
	/**
	 * Retorna o microtime do momento da solicitação
	 *
	 * @return	float Tempo, em micro segundos
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function getMicroTime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * Inicia o contador
	 * @return	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function Inicio() {
		$this->st = $this->getMicroTime();
	}
	
	/**
	 * Retorna tempo decorrido desde o início do contador até o momento da solicitação
	 *
	 * @return	float Tempo, em micro segundos
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getTempo() {
		$this->et = $this->getMicroTime();
		return round(($this->et - $this->st), 3);
	}
	
	/**
	 * Mostra mensagem contendo o tempo decorrido desde o início do contador até o momento da solicitação
	 *
	 * @return	string Mensagem contendo o tempo decorrido entre $this->Inicio até o momento
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function MostraMensagemTempo() {
		$tempo = self::getTempo();
		
		if ($tempo < $this->TEMPO_MAXIMO_RESPOSTA) {
			$texto = 'Consulta respondida em ' . $tempo . ' segundos.';
		} else {
			$texto = 'Consulta respondida em <font color="red">' . $tempo . '</font> segundos.';
		}
		
		return $texto;
	}
	
}
