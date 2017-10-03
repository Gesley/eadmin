<?php
/**
 * Classe para manipulação genérica de logs
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Log
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
class Trf1_Orcamento_Log {
	/**
	 * Nome do log utilizado no e-Orçamento
	 */
	private $_logNome = null;
	
	/**
	 * Classe construtora
	 * 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		// Define nome do arquivo de log
		$this->_logNome = APPLICATION_PATH . '/data/logs/' . 'orcamento-' . date ( 'Y-m' ) . '.log';
	}
	
	/**
	 * Retorna o nome do arquivo, contendo ano e mês como parâmentros
	 * 
	 * @param int		$ano
	 * @param int		$mes
	 * 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaNomeArquivoLog($ano, $mes) {
		return APPLICATION_PATH . '/data/logs/' . 'orcamento-' . $ano . '-' . $mes . '.log';
	}
	
	/**
	 * Grava texto no log
	 *
	 * @deprecated Gravação de log em arquivo foi descontinuada pelo uso do
	 * método Trf1_Admin_Timer::registraTempoResposta que registra os mesmos
	 * dados no banco!
	 * @see Trf1_Admin_Timer::registraTempoResposta
	 * 
	 * @param	array	$requisicao
	 * @param	string	$mensagem
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function gravaLog($requisicao, $mensagem = null, $prioridade = Zend_Log::INFO) {
        //
        //
        //
        //
        //
        //
        //
        //
        // OBSOLETO
        // Ver: Trf1_Admin_Timer::registraTempoResposta
        return false;
        //
        //
        //
        //
        //
        //
        //
        //
        //
        //
        
		// Define o separador de campos
		$separador = '|';
		
		// Cria instância do log
		$log = new Zend_Log ();
		
		// Define path e arquivo de log
		try {
			$arquivo = new Zend_Log_Writer_Stream ( $this->_logNome );
		} catch ( Exception $e ) {
			$msg = '';
			$msg .= '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html;charset=UTF-8"><title>e-Orçamento</title></head>';
			$msg .= '<h1>Erro!</h1>';
			$msg .= '<h2>Trf1_Orcamento_Log (55)</h2>';
			$msg .= '<p>Erro no registro de log do sistema, por ausência de <strong>permissão de gravação</strong> no arquivo: ' . $this->_logNome;
			
			echo $msg;
			exit ();
		
		//throw new Zend_Exception ('Erro ao acessar o arquivo de log! ' . $e->getMessage());
		}
		
		// Define o formato a utilizar - apenas a mensagem
		$formato = new Zend_Log_Formatter_Simple ( '%message%' . $separador . '%priorityName%' . $separador . '%priority% ' . PHP_EOL );
		$arquivo->setFormatter ( $formato );
		
		// Adiciona as formas de log
		$log->addWriter ( $arquivo );
		
		// Pega usuário logado
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Dados para exibição de log
		$data = date ( 'Y-m-d' ) . $separador;
		$hora = date ( 'H:i:s' ) . $separador;
		$usuario = strtoupper ( $sessao->matricula ) . $separador;
		$modulo = $requisicao->getModuleName () . $separador;
		$controle = $requisicao->getControllerName () . $separador;
		$acao = $requisicao->getActionName () . $separador;
		$parametros = $this->isolaParametros ( $requisicao->getParams () ) . $separador;
		
		// Padroniza o texto a ser inserido
		$txtLog = '';
		//$txtLog .= $cabecalho;
		$txtLog .= $data;
		$txtLog .= $hora;
		$txtLog .= $usuario;
		$txtLog .= $modulo;
		$txtLog .= $controle;
		$txtLog .= $acao;
		$txtLog .= $parametros;
		$txtLog .= $mensagem;
		
		$log->log ( $txtLog, $prioridade );
	}
	
	/**
	 * Retorna uma única linha do registro de log
	 *
	 * @param	int			$cod			Número da linha do arquivo de log
	 * @param	array						Campos do registro informado
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($cod) {
		$dados = $this->retornaLog ( true );
		$registro = $dados [$cod];
		
		$nomeCampos = array ('cod', 'data', 'hora', 'usuario', 'modulo', 'controle', 'acao', 'parametros', 'mensagem', 'prioridade', 'cod_prioridade' );
		$nomeArrumado = array ('Linha de registro do log', 'Data', 'Hora', 'Usuário', 'Módulo', 'Controle', 'Ação', 'Parâmetros', 'Mensagem' );
		for($i = 0; $i < count ( $nomeArrumado ); $i ++) {
			$retorno [$nomeArrumado [$i]] = $registro [$nomeCampos [$i]];
		}
		
		$retorno ['Prioridade'] = $registro ['cod_prioridade'] . ' - ' . $registro ['prioridade'];
		
		return $retorno;
	}
	
	/**
	 * Retorna todo o conteúdo do arquivo de log
	 *
	 * @param	int			$ano			Ano para busca do nome do arquivo de log
	 * @param	int			$mes			Mês para busca do nome do arquivo de log
	 * @param	boolean		$campoCompleto	Exibe o não o conteúdo completo de certos campos
	 * @return	array						Conteúdo do arquivo de log
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaLog($ano, $mes, $campoCompleto = false) {
		// Acerta os parâmetros
		$ano = sprintf('%04s', $ano);
		$mes = sprintf('%02s', $mes);
		
		// Busca nome do arquivo
		$arquivoLog = $this->retornaNomeArquivoLog($ano, $mes);
		
		// Busca o arquivo de log
		$dadosLog = file_get_contents ( $arquivoLog, 'r' );
		
		// Separa cada linha... ?
		$linhas = explode ( PHP_EOL, $dadosLog );
		
		// Gera array para retorno
		$retorno = null;
		$x = 1;
		$nomeCampos = array ('cod', 'data', 'hora', 'usuario', 'modulo', 'controle', 'acao', 'parametros', 'mensagem', 'prioridade', 'cod_prioridade' );
		
		// Define o tamanho máximo de caracteres, se aplicável
		$tamMaximo = 22;
		foreach ( $linhas as $linha ) {
			// Verifica se $linha tem conteúdo...
			if (trim ( $linha ) != '') {
				$campos = explode ( '|', $linha );
				array_unshift ( $campos, $x );
				
				for($i = 0; $i < count ( $nomeCampos ); $i ++) {
					$textoAjustado = $campos [$i];
					
					if (! $campoCompleto) {
						// Transforma campos específicos
						if ($nomeCampos [$i] == 'parametros' || $nomeCampos [$i] == 'mensagem') {
							if (substr ( $campos [$i], 1, 50 )) {
								$textoAjustado = substr ( $campos [$i], 1, $tamMaximo ) . '...';
							} else {
								$textoAjustado = '';
							}
						}
					}
					$registro [$nomeCampos [$i]] = $textoAjustado;
				}
				
				// ...se tiver, inclui no array
				if ($registro) {
					$retorno [$x ++] = $registro;
				}
			}
		}
		
		return $retorno;
	}
	
	/**
	 * Gera a string contendo apenas os parâmetros, se for o caso, do endereço visitado, baseado na requisicao getRequest()
	 *
	 * @param	array	$requisicao
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function isolaParametros($requisicao) {
		$txtParametros = '';
		
		// remove o módulo
		unset ( $requisicao ['module'] );
		
		// remove o controle
		unset ( $requisicao ['controller'] );
		
		// remove a ação
		unset ( $requisicao ['action'] );
		
		// retorna em string formatada os parâmetros informados, se for o caso
		foreach ( $requisicao as $id => $valor ) {
			$txtParametros .= '/' . $id . '/' . $valor;
		}
		
		return $txtParametros;
	}
	
	/**
	 * @deprecated Insere uma barra invertida [\] entre cada caracter da string inicial
	 *
	 * @param	string	$texto
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function mesclaBarra($texto) {
		// Transforma a string $texto inserindo uma barra invertida entre cada caracter
		$barra = "\\";
		
		// divide a string em array...
		$strTemp = str_split ( $texto, 1 );
		
		// ...para reagrupar a string incluindo uma barra invertida [\] entre os caracteres
		foreach ( $strTemp as $caracter ) {
			$retorno .= $barra . $caracter;
		}
		
		return $retorno;
	}

}