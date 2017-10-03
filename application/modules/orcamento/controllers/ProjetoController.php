<?php
class Orcamento_ProjetoController extends Zend_Controller_Action {
	/**
	 * Timer para mensuração do tempo de carregamento da página
	 *
	 * @var $_temporizador
	 */
	private $_temporizador;
	
	public function init() {
		// Título apresentado no Browser
		$this->view->title = 'Projeto e-Orçamento';
		
		// Ajuda & Informações
		$this->view->msgAjuda = AJUDA_AJUDA;
		$this->view->msgInfo = AJUDA_INFOR;
		
		// Timer para mensuração do tempo de carregamento da página
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
	}
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da página
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
	public function indexAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Projeto e-Orçamento';
		
		// Equipe de desenvolvimento
		$i = 0;
		$equipe [ $i ] [ 'nome' ] = '<strong> Anderson Sathler M. Ribeiro </strong>';
		$equipe [ $i ] [ 'funcao' ] = 'Analista de Sistemas - Líder do projeto';
		$equipe [ $i++ ] [ 'contato' ] = 'asathler@gmail.com';
		
		$equipe [ $i ] [ 'nome' ] = 'Adelson Vieira Torres';
		$equipe [ $i ] [ 'funcao' ] = 'Gestor do sistema - área negocial';
		$equipe [ $i++ ] [ 'contato' ] = 'adelson.torres@trf1.jus.br';
		
		$equipe [ $i ] [ 'nome' ] = 'Geraldo Afonso dos Santos Silva';
		$equipe [ $i ] [ 'funcao' ] = 'Gestor do sistema - área de TI';
		$equipe [ $i++ ] [ 'contato' ] = 'afonso.geraldo@trf1.jus.br';
		
		$equipe [ $i ] [ 'nome' ] = 'Alex Pitacci Simões';
		$equipe [ $i ] [ 'funcao' ] = 'Analista de Sistemas - Servidor';
		$equipe [ $i++ ] [ 'contato' ] = 'alex.simoes@trf1.jus.br';
		
		$equipe [ $i ] [ 'nome' ] = 'Gilmar Nonato dos Santos';
		$equipe [ $i ] [ 'funcao' ] = 'Analista de Sistemas - Servidor';
		$equipe [ $i++ ] [ 'contato' ] = 'gilmar.nonato@trf1.jus.br';
		
		$equipe [ $i ] [ 'nome' ] = 'Thiago Mota de Santana';
		$equipe [ $i ] [ 'funcao' ] = 'Analista de Sistemas - Servidor';
		$equipe [ $i++ ] [ 'contato' ] = 'thiago.santana@trf1.jus.br';
		
		$equipe [ $i ] [ 'nome' ] = 'Luiz Mendes de Moraes Junior';
		$equipe [ $i ] [ 'funcao' ] = 'Líder da equipe de desenvolvimento PHP/ZEND';
		$equipe [ $i++ ] [ 'contato' ] = 'jovencristao@gmail.com';
		
		$equipe [ $i ] [ 'nome' ] = 'Wilton Barbosa da Silva Júnior';
		$equipe [ $i ] [ 'funcao' ] = 'Arquiteto PHP/ZEND';
		$equipe [ $i++ ] [ 'contato' ] = 'wiltonbsjr@gmail.com';
		
		$equipe [ $i ] [ 'nome' ] = 'Simone Alves Magalhães';
		$equipe [ $i ] [ 'funcao' ] = 'DBA';
		$equipe [ $i++ ] [ 'contato' ] = 'simone.magalhaes@mais2x.com';
		
		$equipe [ $i ] [ 'nome' ] = 'Gesley Batista Rodrigues <gesley.rodrigues@trf1.jus.br>';
		$equipe [ $i ] [ 'funcao' ] = 'Programador';
		$equipe [ $i++ ] [ 'contato' ] = 'rodrigues.gesley@gmail.com';
		
		$equipe [ $i ] [ 'nome' ] = 'Rodrigo Mariano Rodrigues';
		$equipe [ $i ] [ 'funcao' ] = 'Programador';
		$equipe [ $i++ ] [ 'contato' ] = 'rodspt@gmail.com';
		
		$equipe [ $i ] [ 'nome' ] = 'Dayane Oliveira Freire';
		$equipe [ $i ] [ 'funcao' ] = 'Programadora';
		$equipe [ $i++ ] [ 'contato' ] = 'dayfreire@gmail.com';
		
		$equipe [ $i ] [ 'nome' ] = 'Robson Pereira';
		$equipe [ $i ] [ 'funcao' ] = 'Programador';
		$equipe [ $i++ ] [ 'contato' ] = 'robp2005@gmail.com';
		
		$equipe [ $i ] [ 'nome' ] = 'Valdilene Silva';
		$equipe [ $i ] [ 'funcao' ] = 'Programadora';
		$equipe [ $i++ ] [ 'contato' ] = 'valdilenesilva4@gmail.com';
		
		$this->view->equipe = $equipe;
		
		// Versão
		$this->view->versao = '2.5';
		$this->view->dataLiberacao = '28/mai/2013';
	}
	
	public function cacheAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Caches do sistema e-Orçamento!';
		
		// Pasta do cache
		$nomePasta = APPLICATION_PATH . '/data/cache';
		$pasta = dir ( $nomePasta );
		$folder = scandir ( $nomePasta );
		$quantidade = count ( $folder );
		
		// Arquivo (um para teste)
		$arquivo = 'zend_cache---orcamento_permissao_tr17496ps';
		$arquivoExiste = file_exists ( $pasta->path . '/' . $arquivo );
		$arquivoExiste = ($arquivoExiste = 1 ? 'Sim' : 'Não');
		
		// Conteúdo do arquivo
		$conteudo = file_get_contents ( $pasta->path . '/' . $arquivo );
		
		// Conteúdo do cache
		$log = new Trf1_Orcamento_Cache ();
		$cacheId = $log->retornaID_Permissao ( 'tr17496ps' );
		$dados = $log->lerCache ( $cacheId );
		
		// Zend_Debug::dump
		// var_dump the variable into a buffer and keep the output
		ob_start ();
		var_dump ( $dados );
		$output = ob_get_clean ();
		
		// neaten the newlines and indents
		$cache = preg_replace ( "/\]\=\>\n(\s+)/m", "] => ", $output );
		
		// Passagem dos conteúdos para a view
		$this->view->nomePasta = $nomePasta;
		$this->view->quantidade = $quantidade;
		
		$this->view->arquivo = $arquivo;
		$this->view->arquivoExiste = $arquivoExiste;
		$this->view->conteudo = $conteudo;
		$this->view->cache = $cache;
		
		$opcoes = array ('lista' => 0, 'usuario' => 1, 'listagem' => 2, 'combo' => 3, 'despesa' => 4 );
		$opcao = $this->_getParam ( 'opcao' );
		
		if ($opcao) {
			switch ($opcoes [$opcao]) {
				case 0 :
					// lista o conteúdo da pasta
					echo '<h3>Listagem dos arquivos</h3>' . PHP_EOL;
					
					for($i = 0; $i < $quantidade; $i ++) {
						if (! ($folder [$i] == '.' || $folder [$i] == '..' || $folder [$i] == '.svn')) {
							echo $folder [$i] . '<br />' . PHP_EOL;
						}
					}
					break;
				case 1 :
					// exclui os arquivos de cache de usuários
					echo '<h3>Exclusão do cache de permissão dos usuários</h3>' . PHP_EOL;
					$strUsuario = $log->retornaID_Permissao ( '' );
					
					for($i = 0; $i < $quantidade; $i ++) {
						if (! ($folder [$i] == '.' || $folder [$i] == '..' || $folder [$i] == '.svn') && strpos ( $folder [$i], $strUsuario ) > 0) {
							echo $folder [$i] . '<br />' . PHP_EOL;
							unlink ( $nomePasta . '/' . $folder [$i] );
						}
					}
					break;
				case 2 :
					// exclui os arquivos de cache de listagens
					echo '<h3>Exclusão do cache das listagens de registros</h3>' . PHP_EOL;
					$strListagem = $log->retornaID_Listagem ( '' );
					
					for($i = 0; $i < $quantidade; $i ++) {
						if (! ($folder [$i] == '.' || $folder [$i] == '..' || $folder [$i] == '.svn') && strpos ( $folder [$i], $strListagem ) > 0) {
							echo $folder [$i] . '<br />' . PHP_EOL;
							unlink ( $nomePasta . '/' . $folder [$i] );
						}
					}
					break;
				case 3 :
					// exclui os arquivos de cache de listagens
					echo '<h3>Exclusão do cache dos combos</h3>' . PHP_EOL;
					$strCombo = $log->retornaID_Combo ( '' );
					
					for($i = 0; $i < $quantidade; $i ++) {
						if (! ($folder [$i] == '.' || $folder [$i] == '..' || $folder [$i] == '.svn') && strpos ( $folder [$i], $strCombo ) > 0) {
							echo $folder [$i] . '<br />' . PHP_EOL;
							unlink ( $nomePasta . '/' . $folder [$i] );
						}
					}
					break;
				case 4 :
					// exclui os arquivos de cache de listagens
					echo '<h3>Exclusão do cache das despesas</h3>' . PHP_EOL;
					$strCombo = $log->retornaID_Despesa ( '' );
					
					for($i = 0; $i < $quantidade; $i ++) {
						if (! ($folder [$i] == '.' || $folder [$i] == '..' || $folder [$i] == '.svn') && strpos ( $folder [$i], $strCombo ) > 0) {
							echo $folder [$i] . '<br />' . PHP_EOL;
							unlink ( $nomePasta . '/' . $folder [$i] );
						}
					}
					break;
			}
		}
	
	}
	
	public function infoAction ()
	{
	    // Título da tela (action)
	    $this->view->telaTitle = 'Informações do ambiente';
	    
	    phpinfo ();
	}

}