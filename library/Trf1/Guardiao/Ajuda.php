<?php
/**
 * Classe para exibição de ajuda para exibição na tela no e-Guardião
 * 
 * @category	TRF1
 * @package		Trf1_Guardiao_Ajuda
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
class Trf1_Guardiao_Ajuda
{
	/**
	 * Classe construtora
	 * 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		//
	}
	
	/**
	 * Busca dados de ajuda para exibição na tela
	 * 
	 * @param	string		$sController
	 * @param	string		$sAction
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getAjuda($sController, $sAction)
	{
		//
		//
		//
		// TODO: Mudar classe para busca dos dados de ajuda no banco!
		//
		//
		//
		
		$sController	= strtolower($sController);
		$sAction		= strtolower($sAction);
		
		/* ==========================================================================================
		 * CONTROLLERS SEM MENU
		========================================================================================== */
		
		/* ************************************************************
		 * INDEX
		 *********************************************************** */
		$sHelp['index']['index']									= '';
		
		/* ************************************************************
		 * ERROR
		 *********************************************************** */
		$sHelp['error']['error']									= '';
		
		/* ==========================================================================================
		 * MENU ...
		========================================================================================== */
		
		/* ************************************************************
		 * CONTROLLER ...
		 *********************************************************** */
		$sHelp['perfilpessoa']['form']								= ' <p>A página <b>Perfil Pessoa</b> é responsável por proporcionar a possibilidade de alteração dos perfis dos usuários.</p>
                                                                        <ul>
                                                                            <li>Apenas quem possui o perfil <b>RESPONSÁVEL PELA CAIXA DA UNIDADE</b> poderá alterá perfis.</li>
                                                                            <li>Ao escolher a pessoa à qual quer dar a permissão e clicar no botão <b>Pesquisar</b>, será listada todas as permissões possíveis para aquele setor.</li>
                                                                            <li>Para alterar as permissões basta escolhe-las e clicar no botão <b>Alterar</b>.</li>
                                                                            <li>Será emitida uma mensagem de sucesso.</li>
                                                                            <li>A partir deste momento o usuário terá níveis de acesso de acordo com os perfis associados.</li>  
                                                                        </ul>
                                                                        
                                                                        <p><b>1.</b> Se deseja alterar os perfis de uma pessoa localizando as unidades pela <b>matricula</b> do usuário:</p>
                                                                        <ul>
                                                                            <li>Selecione no campo <b>Tipo de Pesquisa</b> o item <b>Por Pessoa</b>.</li>
                                                                            <li>Digite a matricula do usuário ou parte do nome do mesmo e selecione-o nas opções que vão aparecer.</li>
                                                                            <li>Será carregada no campo <b>Unidade</b> as unidades ao qual o usuário possui o perfil <b>RESPONSÁVEL PELA CAIXA DA UNIDADE</b>.</li>
                                                                            <li>Selecione a unidade que deseja fazer as alterações.</li>
                                                                            <li>Clique no botão <b>Pesquisar</b>.</li>
                                                                            <li>Será exibida uma tabela com os perfis da unidade. Os perfis que o usuário possui na unidade estaram marcados.</li>
                                                                            <li>Desmarque ou marque os perfis desejados.</li>
                                                                            <li>Clique no botão <b>Alterar</b>.</li>
                                                                        </ul>
                                                                        
                                                                        <p><b>2.</b> Se deseja alterar os perfis de uma pessoa localizando-o através de uma unidade:</p>
                                                                        <ul>
                                                                            <li>Selecione no campo <b>Tipo de Pesquisa</b> o item <b>Por Unidade</b>.</li>
                                                                            <li>Escolha a unidade no campo <b>Unidade</b>.</li>
                                                                            <li>Existem duas formas de localizar um usuário. As formas estão concentradas no campo <b>Pessoa</b>, pelos itens <b>Pessoas da unidade</b> e <b>Todas as pessoas do TRF1/Seção/Subseção</b>.</li>
                                                                            <li>Se selecionar <b>Pessoas da unidade</b> será exbido o campo <b>Pessoas da unidade</b> que aceita parte do nome ou matricula do usuário da unidade ou clique no drop down e selecione a pessoa dentro da lista.</li>
                                                                            <li>Se selecionar <b>Pessoas da unidade</b> será exibido o campo <b>Informe o nome ou matricula</b> que aceita parte do nome ou matricula de algum usuário no TRF1/Seção/Subseção.</li>
                                                                            <li>Clique no botão <b>Pesquisar</b>.</li>
                                                                            <li>Será exibida uma tabela com os perfis da unidade. Os perfis que o usuário possui na unidade estaram marcados.</li>
                                                                            <li>Desmarque ou marque os perfis desejados.</li>
                                                                            <li>Clique no botão <b>Alterar</b>.</li>
                                                                        </ul>
                                                                        <br />
                                                                        <p><b>Importante</b>: Para as alterações serem de fato utilizadas, o usuário alvo da alteração deverá ser delogado e logado no sistema.</p>
                                                                        <span id="botao_ajuda_recolhe" ></span>';
		
                $sHelp['perfilpessoa']['pessoaacessounidade']                                           = '
                                                                                                            <p>A funçao dessa tela é modificar as permissões das pessoas com acesso a suas caixas. Suas caixas são exibidas no campo <b>Unidade</b>.</p>
                                                                                                            <ul>
                                                                                                                <li>Ao escolher a pessoa à qual quer dar a permissão, será listada todas as permissões possíveis para aquela unidade selecionada.</li>
                                                                                                                <li>Escolha as permissões e clique no botão <b>Alterar</b>.</li>
                                                                                                                <li>Será emitida uma mensagem de sucesso.</li>
                                                                                                            </ul>

                                                                                                            <br />
                                                                                                            <p><b>Importante</b>: Se você modificou alguma permissão sua <i>saia</i> do sistema e <i>entre</i> novamente.</p>
                                                                                                            <span id="botao_ajuda_recolhe" ></span>';
		
                $sHelp['perfilpessoaadm']['caixaspessoas']						= ' <p>Ao preencher o campo <b>Todas as pessoas do TRF1</b> será listada, no campo <b>Unidade</b>, todas as unidades que a pessoa escolhida possui.</p>
                                                                                                            <ul>
                                                                                                                <li>Ao escolher a pessoa e a caixa que ela tem acesso, será listada todas as permissões possíveis para aquele setor.</li>
                                                                                                                <li>Escolha a(s) permissão(ões) e clique no botão <b>Alterar</b>.</li>
                                                                                                                <li>Será emitida uma mensagem de sucesso.</li>
                                                                                                            </ul>
                                                                                                            <p><b>Importante</b>: Se você modificou alguma permissão sua <i>saia</i> do sistema e <i>entre</i> novamente.</p>
                                                                                                            <span id="botao_ajuda_recolhe" ></span>';
		/* ==========================================================================================
		 * Término do trecho de definição dos textos de ajuda
		 * 
		 * Retorno das strings para exibição na tela 
		========================================================================================== */
		// Seleção da ajuda conforme controller / action informados como parâmetros
        $txtAjuda = (isset($sHelp[$sController][$sAction])) ? $sHelp[$sController][$sAction] : '';
		
		// Ajuste para ajuda ainda não informada
		if ($txtAjuda == '') {
			$txtAjuda = 'Nenhuma ajuda disponível.';
		}
		
		$txtFixoInicio	= '' . '<br /><br />';
		$txtFixoTermino	= '<br /><br />' . '';
		
		// Acréscimos de textos fixos, se for o caso
		//$txtFinal = $txtFixoInicio . $txtAjuda . $txtFixoTermino;
		$txtFinal = $txtAjuda;
		
		// Retorno da função
		return $txtFinal;
	}
	
}