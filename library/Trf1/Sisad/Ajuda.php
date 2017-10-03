<?php
/**
 * @category	TRF1
 * @package		Trf1_Sisad_Ajuda
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe para exibição de ajuda para exibição na tela
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
 */
class Trf1_Sisad_Ajuda
{
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
	 * Busca dados de ajuda para exibição na tela
	 * 
	 * @param	string		$sController
	 * @param	string		$sAction
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getAjuda($sController, $sAction)
	{
                $helper = new Zend_View_Helper_BaseUrl();
                $baseUrl = $helper->baseUrl();
                
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
		$sHelp['anexardoc']['form']								= '';
		$sHelp['assinardocs']['form']								= '';
		$sHelp['juntada']['documentoaprocesso']									= 
                      '<ul>
                          <li>
                            A primeira tabela mostra a lista de documentos selecionados e validados. É possível excluir um documento do esquema de juntada clicando no "x" ao lado do documento, caso tenha mais de um documento na primeira tabela.
                          </li>
                          <li> 
                            A segunda tabela mostra os processos validados que estão na caixa atual da unidade do usuário logado.
                          <li>
                            É importante saber que os documentos presentes na primeira tabela serão anexados aos processos selecionados na segunda tabela.
                          </li>
                          <li>
                            Ao anexar um documento em dois processos, o primeiro processo recebe o documento original. Já o segundo processo recebe uma cópia anexada.
                          </li>
                       <ul>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
		$sHelp['juntada']['processoaprocesso']									= 
                      '
                        <b>Funcionalidade Juntada entre Processos Administrativos</b>
                        <br/>Para visualizar o manual completo clique no botão <a class="abrirAnexo" target="_blank" title="Abrir Documento" href="' . $baseUrl . '/sisad/gerenciared/recuperar/'.(APPLICATION_ENV == 'development' ? 'verificar_no_arquivo_Ajuda.php_pois_documento_so_cadastrado_em_produção' : 'id/369235/dcmto/74856060100281/tipo/').'"></a> ou procure pelo documento nº 2013010001134011340200000001.
                        <br/><br/>Estamos na tela <i>Juntada entre Processos Administrativos</i>. Podemos notar que a tela mostra a nossa caixa atual, os processos administrativos escolhidos na tela anterior, já filtrados, e os processos administrativos filtrados da caixa atual.
                        <br/><b>Itens da Página:</b>
                        <ul>
                            <li>
                                <b>Documentos para juntada:</b> São os documentos escolhidos na tela anterior. Os documentos passaram por um filtro de validação. É possível que alguns documentos tenham reprovado no teste de validação.
                                <br/>Caso haja mais de um documento na tela e deseje eliminar algum do esquema de juntada, basta clicar no botão x. Se desejar os documentos de volta clique em Recarregar Documentos.
                            </li>
                            <li>
                                <b>Escolha os Processos:</b> São os processos administrativos presentes na caixa da unidade que é descrita abaixo do titulo da página (<i>Caixa atual</i>). Os processos também passaram por um filtro de validação. É possível que alguns processos reprovem no teste.                             
                            </li>
                            <li>
                                <b>Selecione o tipo de juntada:</b> São os tipos de juntada disponível para juntada entre processos administrativos.
                            </li>
                            <li>
                                <b>Justificativa:</b> É o campo responsável por armazenar os dados da justificativa da juntada. Também será o texto que aparecerá no histórico.
                            </li>
                        </ul>
                        <b>Obs:</b> É possível visualizar o detalhamento do documento clicando duas vezes na linha da tabela.
                        <br/>O documento anexado será incorporado ao processo. Podendo ser desanexado enquanto não tramitado.
                ';
                $sHelp['autuar']['autuar'] =
                      '<p></p>
                       <ul>
                           <li><b>Assunto do Processo</b> - é o PCTT do processo administrativo. Deve ser selecionado um assunto relativo ao processo.</li>
                           <li><b>Objeto do Processo</b> - o objeto do processo deve ser claro e objetivo, para que seja de fácil compreens&atilde;o para as pessoas que irão manuseá-lo.</li>
                           <li><b>Relator</b> - tem uso para os processos que v&atilde;o a julgamento administrativo. Neste caso, ao clicarmos no bot&atilde;o <b>Sim</b>, outra tela ser aberta a qual o sistema mostra a lista de servidores e a relação de magistrados da Primeira Região.</li>
                           <li><b>Palavras Chave</b> - s&atilde;o importantes para a pesquisa e localiza&ccedil;&atilde;o do documento posteriormente. As palavras (quando mais de uma) devem ser separadas por vírgula.</li>
                           <li><b>Confidencialidade </b>e<b> Estado do Processo</b> - segue as orienta&ccedil;&otilde;es realizadas para cada documento.</li>
                       </ul>
                       <p><strong>Cadastro de Vistas</strong></p>
                       <ul>
                           <li>Em Processos P&uacute;blicos não &eacute; poss&iacute;vel cadastrar vistas.</li>
                           <li>Para os todos os Processos <strong>n&atilde;o</strong> públicos &eacute; obrigat&oacute;rio o cadastro de, pelo menos, uma pessoa com vistas.</li>
                           <li>Processos Sigilosos n&atilde;o podem ter Unidades Administrativas cadastradas como vistas.</li>
                       </ul>

                       <p><strong>Cadastro de Partes</strong></p>
                       <ul>
                           <li>Podem ser cadastradas em processos com qualquer tipo de confidencialidade.</li>
                           <li>Processos P&uacute;blicos e Restrito as Partes obrigatoriamente devem ter, no m&iacute;nimo, uma parte cadastrada.</li>
                           <li>Processos Sigilosos n&atilde;o podem ter Unidades Administrativas cadastradas como partes.</li>
                       </ul>
                       
                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>

                   <!--<a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       '; 
                $sHelp['cadastrodcmto']['form']							= 
                      '<p><strong>Unidade Emissora</strong> &eacute; a unidade respons&aacute;vel pela assinatura e divulga&ccedil;&atilde;o do documento.</p>
                       <ul>
                           <li>Ao iniciar a digita&ccedil;&atilde;o de parte do nome, a tela mostra todas as op&ccedil;&otilde;es existentes na tabela de Lota&ccedil;&otilde;es do Ã³rg&atilde;o. Se n&atilde;o for escolhido o item da lista, o sistema gera erro.</li>
                       </ul>

                       <p><strong>Unidade Redatora</strong> &eacute; a unidade respons&aacute;vel pela reda&ccedil;&atilde;o do documento, podendo ser igual à unidade emissora do documento.</p>
                       <ul>
                           <li>Para localizar a unidade de lota&ccedil;&atilde;o, basta seguir os procedimentos listados para <i>unidade emissora</i>.</li>
                       </ul>

                       <p><strong>Tipo Documento</strong> &eacute; uma lista com todos os tipos de documentos que constam do <i>Manual de Documentos Administrativos</i>.</p>

                       <p><strong>N&uacute;mero do Documento Usu&aacute;rio</strong> &eacute; o n&uacute;mero do documento existente hoje, registrado de forma manual. Este n&uacute;mero servir&aacute; como forma de pesquisa para localiza&ccedil;&atilde;o do documento.</p>

                       <p><strong>Assunto do Documento</strong> s&atilde;o os assuntos conforme constam da tabela do PCTT, que servir&aacute; para registrar a codifica&ccedil;&atilde;o do documento.</p>
                       <ul>
                           <li>Digitam-se as palavras relativas ao assunto e o sistema mostra a lista de assuntos cadastrada. Seleciona-se o assunto desejado.</li>
                       </ul>

                       <p><strong>Campo Descri&ccedil;&atilde;o</strong> &eacute; o campo onde devem ser digitadas as informa&ccedil;&otilde;es que facilitar&atilde;o a pesquisa e localiza&ccedil;&atilde;o do documento (utilizar palavras significativas).</p>
                       <p><strong>Palavras Chave</strong> campo o qual devem ser digitadas as palavras que lhe permitir&atilde;o localizar o documento no módulo de pesquisas (mais de uma palavra deve ser separada por v&iacute;rgula).</p>
                       <p><strong>Estado do Documento</strong></p>
                       <ul>
                           <li>Digital &eacute; o documento que foi criado de forma DIGITAL (padr&atilde;o do sistema).</li>
                           <li>Digitalizado &eacute; o documento que foi recebido pela unidade e foi digitalizado para ser tramitado atrav&eacute;s do sistema.</li>
                           <li>F&iacute;sico &eacute; o documento que foi recebido pela unidade, foi digitalizado e necessita ser encaminhado de forma f&iacute;sica para a unidade destinat&aacute;ria.</li>
                       </ul>

                       <p><strong>Confidencialidade</strong> indica qual o grau de sigilo do documento, sendo padr&atilde;o do sistema o tipo <i>p&uacute;blico</i>.</p>

                       <p><strong>Cadastro de Vistas</strong></p>
                       <ul>
                           <li>Em Documentos P&uacute;blicos não &eacute; poss&iacute;vel cadastrar vistas.</li>
                           <li>Para os todos os documentos n&atilde;o públicos &eacute; obrigat&oacute;rio o cadastro de, pelo menos, uma pessoa com vistas.</li>
                           <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como vistas.</li>
                       </ul>

                       <p><strong>Cadastro de Partes</strong></p>
                       <ul>
                           <li>Podem ser cadastradas em documentos com qualquer tipo de confidencialidade.</li>
                           <li>Documentos P&uacute;blicos e Restrito as Partes obrigatoriamente devem ter, no m&iacute;nimo, uma parte cadastrada.</li>
                           <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como partes.</li>
                       </ul>

                       <p><strong>Inserir Documento</strong></p>
                       <ol>
                           <li>Clicar no bot&atilde;o Selecionar arquivo;</li>
                           <li>Localizar em qual pasta / diretório encontra-se o arquivo;</li>
                           <li>Selecionar e abrir arquivo.</li>
                       </ol>

                       <p><strong>Encaminhar documento</strong>: Escolha se deseja encaminhar o documento para a sua Caixa Pessoal Rascunhos, ou para a Caixa de Entrada da Unidade. É possível escolher para qual Unidade deseja encaminhar, desde que possua permissão de acesso à(s) Caixa(s).</p>
                       <p><strong>Aten&ccedil;&atilde;o</strong>: Encerrada a ultima opera&ccedil;&atilde;o, deve-se clicar no bot&atilde;o SALVAR.</p>
                       <p><strong>Importante</strong>: Algumas op&ccedil;&otilde;es ainda n&atilde;o est&atilde;o dispon&iacute;veis nesta vers&atilde;o.</p>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                
                $sHelp['cadastrodcmtoext']['form']	= 
                      '<p><b>Usuário/Unidade Cadastrante</b> o sistema mostra os dados do servidor que se encontra logado para fazer o cadastramento.</p>
                       <p><b>Órgão/Empresa Emissora</b> responsável pela emissão do documento.</p>
                       <ul>
                           <li>Ao iniciar a digitação de parte do nome, a tela mostra todas as opções existentes na tabela de Lotações do órgão.</li>
                       </ul>
                       <p><b>Emissor/Assinante</b> pode ser uma pessoa física ou um empresa e é responsável pela assinatura ou emissão do documento.</p>

                       <p><b>Tipo Documento</b> é uma lista com todos os tipos de documentos que constam do <i>Manual de Documentos Administrativos</i>.</p>

                       <p><b>Número do Documento Externo</b> é o número do documento (campo não obrigatório). Este número servirá como forma de pesquisa para localização do documento.</p>

                       <p><b>Assunto do Documento</b> são os assuntos conforme constam da tabela do PCTT, que servirá para registrar a codificação do documento.</p>
                       <ul>
                           <li>Digitam-se as palavras relativas ao assunto e o sistema mostra a lista de assuntos cadastrada. Seleciona-se o assunto desejado.</li>
                       </ul>

                       <p><b>Descrição do Documento</b> é o campo onde deve-se Registrar as informações que identificam o teor do documento.</p>
                       <p><b>Palavras Chave</b> campo o qual devem ser digitadas as palavras que lhe permitirão localizar o documento no módulo de pesquisas (mais de uma palavra deve ser separada por vírgula).</p>
                       <p><b>Estado do Documento</b></p>
                       <ul>
                           <li>Digital é o documento que foi criado de forma DIGITAL (padrão do sistema).</li>
                           <li>Digitalizado é o documento que foi recebido pela unidade e foi digitalizado para ser tramitado através do sistema.</li>
                           <li>Físico é o documento que foi recebido pela unidade, foi digitalizado e necessita ser encaminhado de forma física para a unidade destinatária.</li>
                       </ul>

                       <p><b>Confidencialidade</b> indica qual o grau de sigilo do documento, sendo padrão do sistema o tipo <i>público</i>.</p>

                       <p><b>Inserir Documento</b></p>
                       <ol>
                           <li>Clicar no botão Selecionar arquivo;</li>
                           <li>Localizar em qual pasta / diretório encontra-se o arquivo;</li>
                           <li>Selecionar e abrir arquivo.</li>
                       </ol>
                       <br />
                       <p><b>Atenção</b>: Encerrada a ultima operação, deve-se clicar no botão SALVAR (irá para a Caixa Pessoal/Rascunho).</p>
                       <p><b>Importante</b>: Algumas opções ainda não estão disponíveis nesta versão.</p>
                       <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->';
		
                $sHelp['cadastrodcmtoext']['orgaoendereco']	= '';
                      /*'<p>Endereçamento de Documentos para Postagem</p>
                       <ul>
                           <li><b>Órgão Destino</b> - constam todos os órgãos cadastrados  pelas Secam’s 1ªreg.</li>
                           <li><b>Destinatário</b> - neste campo pode-se digitar o nome da pessoa que irá receber o documento.</li>
                           <li><b>Tratamento</b> - escolher a forma de tratamento para o destinatário.</li>
                           <li><b>Endereço</b> - o endereço será mostrado caso ele exista, ou permitida a sua digitação.</li>
                           <li><b>CEP</b> - o sistema irá trazer, não existindo poderá ser digitado</li>
                           <li><b>Preferência de Postagem</b> - conforme definido pelos Correios poderá ser solicitado pelo usuário, no entanto, caberá ao setor de protocolo definir o melhor tipo de postagem.</li>
                       </ul>

                      <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>';*/
		$sHelp['caixaentrada']['form']								= '';
                $sHelp['caixapessoal']['arquivadospessoal']= 
                      '<p>Todos os documentos arquivados encontram-se nesta caixa e podem ser <i>desarquivados</i> a qualquer momento, voltando para a <b>Caixa Pessoal/Entrada</b> e assim, receberem novas tramitações.</p>
                       <p><b>Desarquivar</b> a justificativa de desarquivamento é obrigatória e deve conter informações significativas para o responsável pelo desarquivamento, bem como para as pessoas que tomarão conhecimento do documento.</p>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php //echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       <br />
                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>    

                       ';
		$sHelp['caixapessoal']['entrada']								= 
                      '<p>Esta tela contém <u>todos</u> os documentos criados pelo usuário.</p>
                       <ul>
                           <li>Ao posicionar o cursor sobre o documento, ele mostra que se deve dar um duplo click para abri-lo (abre a tela <i>Detalhe</i>).</li>
                           <li>Na tela <b>Detalhe</b>, estão todas as informações sobre o documento, bem como o <i>Histórico</i> de tramitação e pareceres realizados.</li>
                   <!--    <li>A opção <b>Responder</b> permite responder a um documento recebido da Caixa da Unidade onde trabalha para a caixa da própria unidade.</li>-->
                           <li><b>Encaminhar</b> realiza o encaminhamento de um documento; este deve estar assinado ou certificado digitalmente. Pode-se encaminhar um documento ou um lote de documentos para uma mesma unidade. Primeiro deve-se escolher os documentos que serão encaminhados e clicar no botão.</li>
                           <li>Após a ação <i>Encaminhar</i>, surge a tela acima, onde podemos observar os documentos selecionados e caso deseje não encaminhar algum documento, basta voltar a tela onde estão os documentos e selecionar novamente.</li>
                           <li>Em <b>Parecer</b> pareceres realizados nos documentos recebidos ou criados. Escolhe-se o documento e clica-se no botão parecer.</li> <!--a qualquer momento o usuário pode gerar um parecer em um documento que está sob a sua responsabilidade. Todas as informações registram data e hora que são mostradas no <i>Histórico</i>.</li>-->
                           <li><b>Arquivar</b> permitirá o arquivamento do documento no arquivo corrente pessoal (a justificativa de arquivamento é obrigatória). Estará na Caixa Pessoal/Arquivados.</li>
                           <li><b>Assinar por Senha</b> - o sistema solicita a senha do usuário para fazer o login. Após digitado, clicar no botão <i>Assinar</i> (o documento após assinado fica registrado no <i>Histórico</i>).</li>
                   <!--        <li><b>Pesquisar</b> o documento permite a pesquisa por metadados e por palavras chave.</li>-->
                           <li><b>Excluir</b> selecionando o documento, este poderá ser cancelado. É necessário justificar a exclusão para efeitos de auditoria.</li>
                           <li><b>Composição do número do documento :</b> 
                            <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                            <p>Onde:</p>
                            <p>AAAA   – ano de expedição do documento;</p>
                            <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                            <p>EEEEE  – unidade emissora;</p>
                            <p>RRRRR  – unidade redatora;</p>
                            <p>TTTT   – tipo do documento;</p>
                            <p>NNNNNN – número sequencial do documento. </p> </li>
                       </ul>
                        <p><b>Categorias</b> - é uma funcionalidade que serve para diversificar um documento/processo de outro.</p> 
                        <ul>
                           <li>Para visualizar o tutorial de categorização <a href="' . $baseUrl . '/sisad/gerenciared/recuperar/id/144451/dcmto/47334000100226" target="_blank">Clique Aqui.</a></li>
                        </ul>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                
                $sHelp['caixapessoal']['encaminhados'] = 
                      '<p><b>Encaminhados</b> mostra os documentos que saíram da unidade para outros destinos.</p>
                       <ul>
                           <li>A caixa mostra os documentos pelo nome do encaminhador e o tempo que ele foi enviado.</li>
                       </ul>

                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>    
                   <!--<p>Pesquisar</p>-->
                   <!--<p><b>Desfazer</b> somente é permitido desfazer um encaminhamento de um documento que ainda não foi recebido pela unidade. O documento não recebido na unidade aparece em negrito e com o ícone da carta fechada.</p>-->
                   <!--<p><b>Solicitar</b> se o documento já foi recebido pela outra unidade, utilizar esta funcionalidade para solicitar o documento que foi recentemente encaminhado.</p>        -->

                   <!--<a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                
                $sHelp['caixapessoal']['parecer']= 
                      '<ul>
                           <li>Os pareceres realizados em um documento mantêm o documento na Caixa Unidade / Entrada / Caixa Minutas. Eles somente saem da Caixa Unidade / Caixa Minutas através de <i>Encaminhamento</i>.</li>
                       </ul>
                      ';
                
		$sHelp['caixapessoal']['rascunhos'] = 
                      '<p>Todos os documentos criados ou digitalizados pelo servidor, são direcionados inicialmente para a <b>Caixa de Rascunhos</b>, onde o responsável pela criação do documento irá ver o documento e tomar as seguintes ações:</p>
                       <ul>
                           <li>Ao posicionar o cursor sobre o documento, ele mostra que se deve dar um duplo click para abri-lo (abre a tela <i>Detalhe</i>).</li>
                           <li>Na tela <b>Detalhe</b>, estão todas as informações sobre o documento, bem como o <i>Histórico</i> de tramitação e pareceres realizados.</li>
                           <li><b>Assinar por Senha</b> - o sistema solicita a senha do usuário para fazer o login. Após digitado. Clicar no botão <i>Assinar</i> para que o sistema possa gravar a fase de assinatura. O documento após assinado vai para a <b>Caixa Pessoal</b>.</li>
                           <li><b>Pesquisar</b> o documento permite a pesquisa por metadados e por palavras chave.</li>
                           <li><b>Excluir</b> o documento selecionando o documento, este poderá ser cancelado. A exclusão é lógica e não física.</li>
                       </ul>

                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
		                
                $sHelp['caixaminuta']['encaminharpessoa']= 
                      '<ul>
                          <li>Nesta área de trabalho, o documento poderá ser encaminhado para um servidor que tem vistas ao documento, permitindo que este trabalhe no documento conforme a descrição do encaminhamento.</li>
                          <li>Escolhido o <i>servidor</i> para o qual será enviado o documento, e feita a <i>descrição</i> do encaminhamento, clica-se no botão <b>Salvar</b> para que o documento seja enviado a Caixa de Minutas do servidor.</li>
                      </ul>
                      ';
                
                $sHelp['caixaminuta']['formversao']= 
                      '<ul>
                          Nesta área de trabalho, o usuário poderá inserir uma versão escolhendo um dos itens em <b>Texto do documento / Documento</b>:
                          <li>Escolhido <b>Inserir Documento</b> poderá inserir arquivos dos tipos DOC, DOCX e RTF.</li>
                          <li>Escolhido <b>Utilizar editor on-line</b> será carregada na tela a última versão de editor inserida na minuta. Caso não tenha nenhuma versão de editor, então abrirá o editor on-line em branco para digitação.</li>
                          <br />
                          Para visualizar o tutorial de inserção de versão <a href="' . $baseUrl . '/sisad/gerenciared/recuperar/id/188871/dcmto/52446520100260/tipo/1" target="_blank">Clique Aqui.</a>
                      </ul>
                      ';
                
                $sHelp['caixaminuta']['formcadastro']							= 
                      '<p><strong>Unidade Emissora</strong> &eacute; a unidade respons&aacute;vel pela assinatura e divulga&ccedil;&atilde;o do documento.</p>
                       <ul>
                           <li>Ao iniciar a digita&ccedil;&atilde;o de parte do nome, a tela mostra todas as op&ccedil;&otilde;es existentes na tabela de Lota&ccedil;&otilde;es do Ã³rg&atilde;o. Se n&atilde;o for escolhido o item da lista, o sistema gera erro.</li>
                       </ul>

                       <p><strong>Unidade Redatora</strong> &eacute; a unidade respons&aacute;vel pela reda&ccedil;&atilde;o do documento, podendo ser igual à unidade emissora do documento.</p>
                       <ul>
                           <li>Para localizar a unidade de lota&ccedil;&atilde;o, basta seguir os procedimentos listados para <i>unidade emissora</i>.</li>
                       </ul>

                       <p><strong>Tipo Documento</strong> &eacute; uma lista com todos os tipos de documentos que constam do <i>Manual de Documentos Administrativos</i>.</p>

                       <p><strong>N&uacute;mero do Documento Usu&aacute;rio</strong> &eacute; o n&uacute;mero do documento existente hoje, registrado de forma manual. Este n&uacute;mero servir&aacute; como forma de pesquisa para localiza&ccedil;&atilde;o do documento.</p>

                       <p><strong>Assunto do Documento</strong> s&atilde;o os assuntos conforme constam da tabela do PCTT, que servir&aacute; para registrar a codifica&ccedil;&atilde;o do documento.</p>
                       <ul>
                           <li>Digitam-se as palavras relativas ao assunto e o sistema mostra a lista de assuntos cadastrada. Seleciona-se o assunto desejado.</li>
                       </ul>

                       <p><strong>Campo Descri&ccedil;&atilde;o</strong> &eacute; o campo onde devem ser digitadas as informa&ccedil;&otilde;es que facilitar&atilde;o a pesquisa e localiza&ccedil;&atilde;o do documento (utilizar palavras significativas).</p>
                       <p><strong>Palavras Chave</strong> campo o qual devem ser digitadas as palavras que lhe permitir&atilde;o localizar o documento no módulo de pesquisas (mais de uma palavra deve ser separada por v&iacute;rgula).</p>
                       <p><strong>Estado do Documento</strong></p>
                       <ul>
                           <li>Digital &eacute; o documento que foi criado de forma DIGITAL (padr&atilde;o do sistema).</li>
                           <li>Digitalizado &eacute; o documento que foi recebido pela unidade e foi digitalizado para ser tramitado atrav&eacute;s do sistema.</li>
                           <li>F&iacute;sico &eacute; o documento que foi recebido pela unidade, foi digitalizado e necessita ser encaminhado de forma f&iacute;sica para a unidade destinat&aacute;ria.</li>
                       </ul>

                       <p><strong>Confidencialidade</strong> indica qual o grau de sigilo do documento, sendo padr&atilde;o do sistema o tipo <i>p&uacute;blico</i>.</p>

                       <p><strong>Cadastro de Vistas</strong></p>
                       <ul>
                           <li>Em Documentos P&uacute;blicos não &eacute; poss&iacute;vel cadastrar vistas.</li>
                           <li>Para os todos os documentos n&atilde;o públicos &eacute; obrigat&oacute;rio o cadastro de, pelo menos, uma pessoa com vistas.</li>
                           <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como vistas.</li>
                       </ul>

                       <p><strong>Cadastro de Partes</strong></p>
                       <ul>
                           <li>Podem ser cadastradas em documentos com qualquer tipo de confidencialidade.</li>
                           <li>Documentos P&uacute;blicos e Restrito as Partes obrigatoriamente devem ter, no m&iacute;nimo, uma parte cadastrada.</li>
                           <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como partes.</li>
                       </ul>
                       
                       <p>Se a versão escolhida for de <strong>editor on-line</strong>, o link da versão estará disponível com o nome <strong>Versão Escolhida</strong>.</p>
                       <p>Se a versão escolhida for de <strong>arquivo</strong> estará disponível a opção <strong>Inserir Documento</strong></p>
                       <p><strong>Inserir Documento</strong></p>
                       <ol>
                           <li>Clicar no bot&atilde;o Selecionar arquivo;</li>
                           <li>Localizar em qual pasta / diretório encontra-se o arquivo;</li>
                           <li>Selecionar e abrir arquivo.</li>
                       </ol>

                       <p><strong>Encaminhar documento</strong>: Escolha se deseja encaminhar o documento para a sua Caixa Pessoal Rascunhos, ou para a Caixa de Entrada da Unidade. É possível escolher para qual Unidade deseja encaminhar, desde que possua permissão de acesso à(s) Caixa(s).</p>
                       <p><strong>Aten&ccedil;&atilde;o</strong>: Encerrada a ultima opera&ccedil;&atilde;o, deve-se clicar no bot&atilde;o SALVAR.</p>
                       <p><strong>Importante</strong>: Algumas op&ccedil;&otilde;es ainda n&atilde;o est&atilde;o dispon&iacute;veis nesta vers&atilde;o.</p>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                
                $sHelp['caixaminuta']['finalizar']= 
                      '<ul>
                          <li>Nessa fase deve ser feita a <i>descrição</i> da finalização do documento, escolher uma das versões e clicar no botão <b>Salvar Versão Final</b> para que abra a página de cadastro de documentos definitiva.</li>
                          <li>Se for escolhida uma versão em <i>Arquivo</i>, deverá ser gerado o PDF desse arquivo para inserção no documento final.</li>
                          <br /> 
                          Para visualizar o tutorial de finalização da minuta <a href="' . $baseUrl . '/sisad/gerenciared/recuperar/id/189044/dcmto/52459220100216/tipo/1" target="_blank">Clique Aqui.</a>                      
                       </ul>
                      ';
                
                $sHelp['caixaminuta']['minutas']= 
                      '<p>Mostra as <b>minutas</b> que estão na caixa do usuário.</p>
                       <ul>
                           <p> O usuário poderá realizar uma das seguintes ações:</p>
                           <ul>
                              <li><b>Assinar por Senha:</b> O sistema grava a fase de assinatura.</li>
                              <li><b>Cadastrar Vistas: </b> Se precisar inserir novas pessoas que participarão da minuta.</li>
                              <li><b>Encaminhar Pessoa: </b> Poderá encaminhar a minuta para uma das pessoas que tem vistas.</li>
                              <li><b>Finalizar: </b> Poderá inserir a faze de finalização da minuta e gerar o documento final.</li>
                              <li><b>Inserir Versão: </b> Insere uma nova versão na minuta.</li>
                              <li><b>Parecer: </b> Cria uma fase na minuta com o parecer informado.</li>
                           </ul>        
                           A caixa mostra as minutas pelo tempo que ele foi enviada.
                       </ul>
                       ';
                
                $sHelp['caixaminuta']['minutasfinalizadas']= 
                      '<p><b>Minutas</b> mostra as minutas finalizadas que o usuário participou.</p>
                       <ul>
                           <li>A caixa mostra os documentos pelo tempo que ele foi enviado.</li>
                       </ul>
                       ';
                
                
                $sHelp['caixaminuta']['reutilizarversao']= 
                      '<ul>
                          Nesta área de trabalho, o usuário poderá escolher uma das versãos em Arquivos
                          <li>Escolhida a versão, clique no botão <b>Inserir Minuta</b> será carregada na tela de cadastro de minuta a versão escolhida.</li>
                      </ul>';
                
                
                $sHelp['caixaunidade']['entrada']= 
                      '<p>A <b>Ordenação</b> é uma funcionalidade que está presente em quase todas as telas dos sistemas do projeto e-Admin. O sistema permite ordenar os dados pelos campos do menu conforme a descrição:</p>
                      <ul>
                          <li><b>Tipo</b> - Mostra o tipo do documento conforme consta na tabela.</li>
                          <li><b>Número</b> - Numeração única de documentos na Primeira Região.</li>
                          <li><b>Data</b> - Data de criação ou tramitação do documento.</li>
                          <li><b>Origem</b> - É a unidade de criação ou de origem do documento encaminhado.</li>
                          <li><b>Encaminhador</b> - É o nome do responsável pelo encaminhamento do documento.</li>
                          <li><b>Tempo</b> - Mostra o tempo em que o documento foi criado ou tempo de tramitação.</li>
                      </ul>
                      <p><b>Detalhes</b> - Exibe todos os metadados do documento, bem como a possibilidade de abrir o documento para leitura se este existir. Ao clicar duas vezes no documento surge uma tela com as seguintes opções:</p>
                      <ul>
                          <li><b>Documento</b> - para ler o documento clica-se uma vez no botão em formato <i>ícone</i> "abre pasta" na parte inferior.</li>
                          <li><b>Histórico</b> - mostra todos os pareceres e tramitações ocorridas com o documento, constando o nome do responsável, data e hora da ocorrência.</li>
                      </ul>
                      <p><b>Adicionar</b> - Para fazer uma movimentação de documentos, deve-se primeiro selecionar os documentos que se deseja tramitar e em seguida clicar no botão <i>Adicionar</i>:</p>
                      <ul>
                          <li>No campo Carrinho de Documentos e Processos possui o botão <b>Encaminhar</b> que realiza a movimentação <i>interna</i>.</li>
                          <li><b>Encaminhar Pessoa</b> - Nesta área de trabalho, o documento poderá ser encaminhado para um servidor da Divisão ou da Seção, permitindo que este trabalhe no documento ou processo conforme a descrição do encaminhamento.</li>
                          <li><b>Parecer</b> - o usuário pode gerar um parecer em um documento que está sob a sua responsabilidade (registra as atividades que vem executando). Todas as informações são registradas no </i>histórico</i>.</li>
                          <li>Para movimentação <i>externa</i> clicar no botão <b>Endereçar</b> para preenchimento dos dados de endereçamento do local onde a correspondência deverá ser entregue (etapa necessária para protocolar).</li>
                  <!--        <li>Enviar ao <b>Protocolo</b> após o endereçamento para dar continuidade a movimentação <i>externa</i>.</li>--> 
                          <li><b>Assinar por Senha</b> - marcar o documento e clicar no botão.</li>
                          <li><b>Autuar</b> - clique no botão para abrir a função.</li>
                          <li><b>Adicionar ao processo</b> - utilizado para incluir os documentos ao processo.</li>
                          <li><b>Arquivar</b> - marca-se o documento ou os documentos que se deseja arquivar e clicar no botão Arquivar.</li>
                       </ul>
                  <!--    <p><b>Visualizar</b></p>-->
                      <p><b>Permissões</b> - Trata-se de Concessão de Permissões na Caixa de Unidade do e-Sisad para Servidores da Unidade ou fora dela.</p> 
                      <ul>
                           <li>Ao escolher <b>Pessoa da Unidade</b>, o sistema mostra os servidores que estão lotados na Unidade a qual o servidor possui permissão de responsável pela caixa da unidade. Apenas quem possui essa permissão poderá conceder <i>novas</i> permissões.</li>
                      </ul>

                      <p><b>Categorias</b> - é uma funcionalidade que serve para diversificar um documento/processo de outro.</p> 
                      <ul>
                          <li>Para visualizar o tutorial de categorização <a href="' . $baseUrl . '/sisad/gerenciared/recuperar/id/144451/dcmto/47334000100226" target="_blank">Clique Aqui.</a></li>
                      </ul>
                      <p><b>Composição do número do documento</b> 
                          <ul>
                           <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                           <p>Onde:</p>
                           <p>AAAA   – ano de expedição do documento;</p>
                           <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                           <p>EEEEE  – unidade emissora;</p>
                           <p>RRRRR  – unidade redatora;</p>
                           <p>TTTT   – tipo do documento;</p>
                           <p>NNNNNN – número sequencial do documento. </p> 
                          </ul>
                      </p>
                      <p><strong>Cadastro de Partes</strong> 
                          <ul>
                              <li>Podem ser cadastradas em documentos com qualquer tipo de confidencialidade.</li>
                              <li>Em documentos n&atilde;o p&uacute;blicos &eacute; necess&aacute;rio ter vistas ao documento para realizar o cadastro de novas partes.</li>
                              <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como partes.</li>
                              <li>Documentos da Corregedoria s&oacute; podem ter partes cadastradas por quem possuir a permiss&atilde;o da Corregedoria.</li>
                         </ul>  
                      </p>
                      <p><strong>Cadastro de Vistas</strong> 
                           <ul>
                              <li>Em Documentos P&uacute;blicos não &eacute; poss&iacute;vel cadastrar vistas.</li>
                              <li>Para realizar o cadastro de novas vistas &agrave; um documento &eacute; necess&aacute;rio ter vistas no mesmo.</li>
                              <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como vistas.</li>
                              <li>Documentos da Corregedoria s&oacute; podem ter vistas cadastradas por quem possuir a permiss&atilde;o da Corregedoria.</li>
                         </ul>  

                      <p><strong> Observa&ccedil;&otilde;es: </strong> 
                      <ul>
                          <li>Para adicionar documentos com confidencialidade n&atilde;o p&uacute;blica no carrinho &eacute; necess&aacute;rio ter permiss&atilde;o de vistas no documento.</li>
                          <li>Somente as pessoas com permiss&atilde;o da Corregedoria podem efetuar a&ccedil;&otilde;es nos documentos da Corregedoria.</li>
                      </ul>
                      <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->';
		
                $sHelp['caixaunidade']['arquivar']	= 
                      '<ul>
                           <li>Marca-se o documento ou os documentos que se deseja Arquivar e clicar no botão <b>Arquivar</b>.</li>
                           <li>A justificativa de arquivamento é <b>Obrigatória</b> e deve conter um texto significativo para o usuário e para a sua unidade.</li>
                           <li>O documento <b>arquivado</b> estará na Caixa Unidade / Arquivados, e será mantido como arquivo corrente.</li>
                       </ul>
                   <!--    <p>Todos os documentos criados ou digitalizados pelo servidor, são direcionados inicialmente para a <b>Caixa de Rascunhos</b>, onde o responsável pela criação do documento irá ver o documento e tomar duas ações:</p>
                       <ul>
                           <li><b>Encaminhar</b> - o documento será encaminhado automaticamente para a Caixa de Entrada de documentos do servidor.</li>
                           <li><b>Excluir</b> - poderá excluir o documento e os metadados ou somente o documento, mantendo os metadados para nova inclusão do documento excluído.</li>
                       </ul>-->
                       
                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>

                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                $sHelp['caixaunidade']['arquivadosunidade']				= 
                      '<p><b>Composição do número do documento -</b> 
                         <ul>
                            <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                            <p>Onde:</p>
                            <p>AAAA   – ano de expedição do documento;</p>
                            <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                            <p>EEEEE  – unidade emissora;</p>
                            <p>RRRRR  – unidade redatora;</p>
                            <p>TTTT   – tipo do documento;</p>
                            <p>NNNNNN – número sequencial do documento. </p> 
                          </ul>  
                       </p>';
                     /* '<p>Todos os documentos criados ou digitalizados pelo servidor, são direcionados inicialmente para a <b>Caixa de Rascunhos</b>, onde o responsável pela criação do documento irá ver o documento e tomar as seguintes ações:</p>
                       <ul>
                           <li><b>Encaminhar</b> - o documento será encaminhado automaticamente para a Caixa de Entrada de documentos do servidor.</li>
                           <li><b>Assinar por Senha</b> - o sistema solicita a senha do usuário para fazer o login. Após digitado. Clicar no botão <i>Assinar</i> para que o sistema possa gravar a fase de assinatura. O documento após assinado vai para a <b>Caixa Pessoal</b>.</li>
                           <li><b>Arquivar</b> o documento permitirá o arquivamento do documento no arquivo corrente pessoal.</li>
                           <li><b>Pesquisar</b> o documento permite a pesquisa por metadados e por palavras chave.</li>
                           <li><b>Cancelar</b> o documento selecionando o documento, este poderá ser cancelado. A exclusão é lógica e não física.</li>
                       </ul>

                       <a target="_blank" title="Documento de ajuda completo" href="<?php //echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>
                       ';*/
                $sHelp['caixaunidade']['assinar']	= 
                      '<p>Clicar no botão <b>Assinar</b> para que o sistema possa gravar a fase de assinatura.</p>
                       <p>Após digitar a senha e clicar no botão Assinar, o sistema grava os dados do assinante e o documento retorna para a Caixa Unidade / Entrada, sendo emitida a mensagem de sucesso ou não do procedimento.</p>
                       
                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                $sHelp['caixaunidade']['avaliacaodiretores']	= 
                      '<p>A <b>avaliação</b> será obrigatória e deve ser realizada assim que receberem o aviso de baixa da solicitação na sua Caixa Pessoal. 
                          Por meio dessa avaliação, serão gerados os relatórios para pagamento a empresa.
                       </p>
                       <ul>
                           <li>Todas as solicitações devem ser avaliadas dentro do mês de execução ou no primeiro dia útil após o fechamento do mês.</li>
                           <li>Basta marcar a solicitação que deseja avaliar e clique no botão <b>Avaliar</b>.</li>
                       </ul>

                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                $sHelp['caixaunidade']['correspondencias']	= 
                      '<p>Nesta tela, devemos marcar os documentos que serão encaminhados ao setor de Protocolo/SECAM e clicarmos no botão <b>Enviar</b>.</p>
                       <p>O sistema mostrará a tela com o número do protocolo gerado e surgirá um terceiro ícone, demonstrando que este documento teve os seus metadados enviados aos setor de Protocolo/SECAM, onde os servidores aguardarão o recebimento dos documentos físicos.</p>
                       <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->';
                
                $sHelp['caixaunidade']['despacho']								= 
                      '<ul>
                           <li>Os despachos realizados em um documento mantêm o documento na Caixa Unidade / Entrada. Eles somente saem da Caixa Unidade através de <i>Encaminhamento</i>.</li>
                       </ul>
                       
                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>';
                
                $sHelp['caixaunidade']['documentosdaunidade']	= 
                       '<p>A <b>Ordenação</b> é uma funcionalidade que está presente em quase todas as telas dos sistemas do projeto e-Admin. O sistema permite ordenar os dados pelos campos do menu conforme a descrição:</p>
                        <ul>
                            <li><b>Tipo</b> - Mostra o tipo do documento conforme consta na tabela.</li>
                            <li><b>Número</b> - Numeração única de documentos na Primeira Região.</li>
                            <li><b>Data</b> - Data de criação ou tramitação do documento.</li>
                            <li><b>Origem</b> - É a unidade de criação ou de origem do documento encaminhado.</li>
                            <li><b>Encaminhador</b> - É o nome do responsável pelo encaminhamento do documento.</li>
                            <li><b>Tempo</b> - Mostra o tempo em que o documento foi criado ou tempo de tramitação.</li>
                        </ul>
                        <p><b>Detalhes</b> - Exibe todos os metadados do documento, bem como a possibilidade de abrir o documento para leitura se este existir. Ao clicar duas vezes no documento surge uma tela com as seguintes opções:</p>
                        <ul>
                            <li><b>Documento</b> - para ler o documento clica-se uma vez no botão em formato <i>ícone</i> "abre pasta" na parte inferior.</li>
                            <li><b>Histórico</b> - mostra todos os pareceres e tramitações ocorridas com o documento, constando o nome do responsável, data e hora da ocorrência.</li>
                        </ul>
                        <p><strong>Cadastro de Partes</strong> 
                            <ul>
                                <li>Podem ser cadastradas em documentos com qualquer tipo de confidencialidade.</li>
                                <li>Em documentos n&atilde;o p&uacute;blicos &eacute; necess&aacute;rio ter vistas ao documento para realizar o cadastro de novas partes.</li>
                                <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como partes.</li>
                                <li>Documentos da Corregedoria s&oacute; podem ter partes cadastradas por quem possuir a permiss&atilde;o da Corregedoria.</li>
                           </ul>  
                        </p>
                        <p><strong>Cadastro de Vistas</strong> 
                             <ul>
                                <li>Em Documentos P&uacute;blicos não &eacute; poss&iacute;vel cadastrar vistas.</li>
                                <li>Para realizar o cadastro de novas vistas &agrave; um documento &eacute; necess&aacute;rio ter vistas no mesmo.</li>
                                <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como vistas.</li>
                                <li>Documentos da Corregedoria s&oacute; podem ter vistas cadastradas por quem possuir a permiss&atilde;o da Corregedoria.</li>
                           </ul> 
                        </p>
                        <p><strong>Parecer</strong> - o usuário pode gerar um parecer em um documento que está sob a sua responsabilidade (registra as atividades que vem executando). Todas as informações são registradas no histórico.</p> 
                        <br />   
                        <p><b>Composição do número do documento</b> 
                          <ul>
                           <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                           <p>Onde:</p>
                           <p>AAAA   – ano de expedição do documento;</p>
                           <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                           <p>EEEEE  – unidade emissora;</p>
                           <p>RRRRR  – unidade redatora;</p>
                           <p>TTTT   – tipo do documento;</p>
                           <p>NNNNNN – número sequencial do documento. </p> 
                          </ul>
                      </p>

                        <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                        ';
                
                $sHelp['caixaunidade']['encaminhados']	= 
                      '<p><b>Encaminhados</b> - esta caixa mostra todos os documentos que foram encaminhados pela unidade e permite saber se estes já foram lidos.</p><br />
                       <p>A <b>Ordenação</b> é uma funcionalidade que está presente em quase todas as telas dos sistemas do projeto e-Admin. O sistema permite ordenar os dados pelos campos do menu conforme a descrição:</p>
                       <ul>
                           <li><b>Tipo</b> - Mostra o tipo do documento conforme consta na tabela.</li>
                           <li><b>Número</b> - Numeração única de documentos na Primeira Região.</li>
                           <li><b>Data</b> - Data de criação ou tramitação do documento.</li>
                           <li><b>Origem</b> - É a unidade de criação ou de origem do documento encaminhado.</li>
                           <li><b>Encaminhador</b> - É o nome do responsável pelo encaminhamento do documento.</li>
                           <li><b>Tempo</b> - Mostra o tempo em que o documento foi criado ou tempo de tramitação.</li>
                       </ul>

                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                                
                $sHelp['caixaunidade']['encaminhar'] = 
                      '<p>Nesta caixa, <b>Documentos para encaminhar</b>, marque o Tipo de Movimentação - <b>Interna</b>. Preencha os campos necessários e salve.</p>
                       <p>O documento é enviado para a lotação de destino e fica na <b>Caixa Encaminhados</b>.</p>
                       <br />
                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>
                       
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                
                $sHelp['caixaunidade']['encaminharpessoa'] = 
                      '<ul>
                           <li>Nesta área de trabalho, o documento poderá ser encaminhado para um servidor da Divisão ou da Seção, permitindo que este trabalhe no documento ou processo conforme a descrição do encaminhamento.</li>
                           <li>Escolhido o <i>servidor</i> para o qual será enviado o documento, e feita a <i>descrição</i> do encaminhamento, clica-se no botão <b>Encaminhar</b> para que o documento seja enviado a Caixa Pessoal do servidor.</li>
                       </ul>

                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>
                       <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php //echo $this->baseUrl();  ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                     ';
                $sHelp['caixaunidade']['enderecar']								= 
                      '<p>Endereçamento de Documentos para Postagem</p>
                       <ul>
                           <li><b>Órgão Destino</b> - constam todos os órgãos cadastrados  pelas Secam’s 1ªreg.</li>
                           <li><b>Destinatário</b> - neste campo pode-se digitar o nome da pessoa que irá receber o documento.</li>
                           <li><b>Tratamento</b> - escolher a forma de tratamento para o destinatário.</li>
                           <li><b>Endereço</b> - o endereço será mostrado caso ele exista, ou permitida a sua digitação.</li>
                           <li><b>CEP</b> - o sistema irá trazer, não existindo poderá ser digitado</li>
                           <li><b>Preferência de Postagem</b> - conforme definido pelos Correios poderá ser solicitado pelo usuário, no entanto, caberá ao setor de protocolo definir o melhor tipo de postagem.</li>
                       </ul>

                  <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->

                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>';
                
                
                
                $sHelp['caixaunidade']['parecer']	= 
                      '<ul>
                           <li>Os pareceres realizados em um documento mantêm o documento na Caixa Unidade / Entrada. Eles somente saem da Caixa Unidade através de <i>Encaminhamento</i>.</li>
                       </ul>

                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php //echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                $sHelp['caixaunidade']['processosdaunidade']	= 
                       '<p>A <b>Ordenação</b> é uma funcionalidade que está presente em quase todas as telas dos sistemas do projeto e-Admin. O sistema permite ordenar os dados pelos campos do menu conforme a descrição:</p>
                        <ul>
                            <li><b>Tipo</b> - Mostra o tipo do documento conforme consta na tabela.</li>
                            <li><b>Número</b> - Numeração única de documentos na Primeira Região.</li>
                            <li><b>Data</b> - Data de criação ou tramitação do documento.</li>
                            <li><b>Origem</b> - É a unidade de criação ou de origem do documento encaminhado.</li>
                            <li><b>Encaminhador</b> - É o nome do responsável pelo encaminhamento do documento.</li>
                            <li><b>Tempo</b> - Mostra o tempo em que o documento foi criado ou tempo de tramitação.</li>
                        </ul>
                        <p><b>Detalhes</b> - Exibe todos os metadados do documento, bem como a possibilidade de abrir o documento para leitura se este existir. Ao clicar duas vezes no documento surge uma tela com as seguintes opções:</p>
                        <ul>
                            <li><b>Documento</b> - para ler o documento clica-se uma vez no botão em formato <i>ícone</i> "abre pasta" na parte inferior.</li>
                            <li><b>Histórico</b> - mostra todos os pareceres e tramitações ocorridas com o documento, constando o nome do responsável, data e hora da ocorrência.</li>
                        </ul>
                        <p><strong>Cadastro de Partes</strong> 
                        <ul>
                                <li>Podem ser cadastradas em documentos com qualquer tipo de confidencialidade.</li>
                                <li>Em documentos n&atilde;o p&uacute;blicos &eacute; necess&aacute;rio ter vistas ao documento para realizar o cadastro de novas partes.</li>
                                <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como partes.</li>
                                <li>Documentos da Corregedoria s&oacute; podem ter partes cadastradas por quem possuir a permiss&atilde;o da Corregedoria.</li>
                         </ul>
                        </p>
                        <p><strong>Cadastro de Vistas</strong> 
                        <ul>
                                <li>Em Documentos P&uacute;blicos não &eacute; poss&iacute;vel cadastrar vistas.</li>
                                <li>Para realizar o cadastro de novas vistas &agrave; um documento &eacute; necess&aacute;rio ter vistas no mesmo.</li>
                                <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como vistas.</li>
                                <li>Documentos da Corregedoria s&oacute; podem ter vistas cadastradas por quem possuir a permiss&atilde;o da Corregedoria.</li>
                        </ul>
                        </p>
                        <p><strong>Parecer</strong> - o usuário pode gerar um parecer em um documento que está sob a sua responsabilidade (registra as atividades que vem executando). Todas as informações são registradas no histórico.</p> 
                        <br />                        
                        <p>
                          <b>Composição do número do documento</b> 
                          <ul>
                           <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                           <p>Onde:</p>
                           <p>AAAA   – ano de expedição do documento;</p>
                           <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                           <p>EEEEE  – unidade emissora;</p>
                           <p>RRRRR  – unidade redatora;</p>
                           <p>TTTT   – tipo do documento;</p>
                           <p>NNNNNN – número sequencial do documento. </p> 
                          </ul>
                        </p>

                    <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                        ';
                $sHelp['caixaunidade']['solicitacoesdaunidade']	= 
                      '<p>A <b>avaliação</b> será obrigatória e deve ser realizada assim que receberem o aviso de baixa da solicitação na sua Caixa Pessoal. 
                          Por meio dessa avaliação, serão gerados os relatórios para pagamento a empresa.
                       </p>
                       <ul>
                           <li>Todas as solicitações devem ser avaliadas dentro do mês de execução ou no primeiro dia útil após o fechamento do mês.</li>
                           <li>Basta marcar a solicitação que deseja avaliar e clique no botão <b>Avaliar</b>.</li>
                       </ul>

                       <b>Composição do número do documento :</b> 
                       <ul>
                          <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                          <p>Onde:</p>
                          <p>AAAA   – ano de expedição do documento;</p>
                          <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                          <p>EEEEE  – unidade emissora;</p>
                          <p>RRRRR  – unidade redatora;</p>
                          <p>TTTT   – tipo do documento;</p>
                          <p>NNNNNN – número sequencial do documento. </p> 
                       </ul>
                       <b>Pedido de Informação do Desenvolvedor: </b> 
                       <ul>
                          <li>Ao clicar no botão <b>Pedido de Informação do Desenvolvedor</b> o sistema vai mostrar todas as solicitações de informação que o desenvolvedor realizou para o usuário que encaminhou a solicitação ao desenvolvimento.</li>
                          <li>Vale salientar que para a solicitação de informação aparecer, a unidade atual do usuário logado precisa ser a mesma unidade que o usuário encaminhador está lotado.</li>
                          <li>Na caixa das solicitações de informação do desenvolvedor é possível responder a solicitação no lugar do usuário encaminhador clicando no botão <b>Incluir Informação</b> ou se necessário solicitar informação ao usuário cadastrante clicando em <b>Solicitar Informação ao Usuário Cadastrante</b>.</li>
                          <li>É possível responder a solicitação de informação ao desenvolvedor mesmo que o usuário cadastrante não tenha respondido.</li>
                       </ul>
                       <b>Pedido de Informação ao Usuário Cadastrante: </b> 
                       <ul>
                          <li>Ao clicar no botão <b>Pedido de Informação</b> o sistema vai exibir todas as solicitação de informação ao usuário cadastrante da solicitação.</li>
                          <li>Vale salientar que para a solicitação de informação aparecer, o usuário cadastrante da solicitação precisa possuir lotação na unidade atual do usuário logado.</li>
                          <li>É possivel responder a solicitação de informação ao usuário cadastrante clicando em <b>Incluir Informação</b>.</li>
                       </ul>

                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';                       
                
                $sHelp['caixaunidade']['trocarcaixa']	= 
                      '<p>Esta funcionalidade permite que um servidor possa ter acesso a mais de uma caixa de unidade para analisar documentos/processos e enviá-los.</p>
                       <ul>
                           <li>Será disponibilizada uma consulta para que o Diretor da Unidade possa verificar quais pessoas estão com acesso a caixa da sua unidade e assim poder retirar os que não podem mais acessar os documentos/processos de sua unidade.</li>
                           <li>O sistema informará sempre a lotação do servidor no RH e não a que ele tem permissão de uso.</li>
                           <li>Selecione a unidade que se quer trabalhar clicando sobre a mesma e depois no botão <b>Escolher</b>.</li>
                           <li>Definada a unidade de trabalho, escolha qual a caixa de trabalho ou atividade que pretende executar no campo <b>Ir para</b>.</li>
                       </ul>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                
                $sHelp['categorias']['categorizar']	=
                      '<p><b>Categorias</b> - é uma funcionalidade que serve para diversificar um documento/processo de outro.</p> 
                       <ul>
                           <li>Para visualizar o tutorial de categorização <a href="' . $baseUrl . '/sisad/gerenciared/recuperar/id/144451/dcmto/47334000100226/tipo/1" target="_blank">Clique Aqui.</a></li>
                       </ul>

                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
                
                $sHelp['detalhedcmto']['detalhedcmto']	= '';
		$sHelp['distribuicao']['form']								= '';
		$sHelp['etiqueta']['criar'] = 
                      '<p>Feito o <i>Endereçamento</i> e gerado o <i>Protocolo</i>, deve-se imprimir os documentos para tramitação física, bem como criar as etiquetas de endereçamento.</p>
                      <ul>
                          <li>Digitar o intervalo de data em que os protocolos foram gerados e clicar no botão <b>Pesquisar</b> (o sistema mostrará os documentos protocolados).</li>
                          <li>Marcar as correspondências e clicar no botão <b>Imprimir</b>. O sistema mostrará as etiquetas geradas para impressão.</li>
                      </ul>
                      <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->';
		$sHelp['faseadm']['form']									= '';
		$sHelp['formularioaviso']['form']   = 
                      '<li>Unidade emissora, unidade superior ou responsável por emitir o aviso(Ex: ASCOM).</li>
                       <li>Unidade redatora, unidade de onde foi criada o aviso(Ex: DISAD).</li>
                       <li>Assuntos devem seguir o padrão do PCTT, exemplo de um aviso falecimento deve-se procurar por "falecimento".</li>
                       <li>Visualizar como o documenento ficará ao ser gerado.</li>
                      ';
		$sHelp['formulariocadtreinamento']['form']					= '';
		$sHelp['formulariocadtreinamento']['form']					= 'Texto de ajuda deve ser inserido aqui...';
		
                $sHelp['formulariocircular']['form']						= 
                      '<li>Unidade emissora, unidade superior ou responsável por emitir a circular(Ex: ASCOM).</li>
                       <li>Unidade redatora, unidade de onde foi criada a circular(Ex: DISAD).</li>
                       <li>Assuntos devem seguir o padrão do PCTT, exemplo de um circular falecimento deve-se procurar por "falecimento".</li>
                       <li>Visualizar como o documenento ficará ao ser gerado.</li>';
		$sHelp['formularioinformacao']['form']						= 
                      '<li>Unidade emissora, unidade superior ou responsável por emitir o informativo(Ex: ASCOM).</li>
                       <li>Unidade redatora, unidade de onde foi criado o informativo(Ex: DISAD).</li>
                       <li>Assuntos devem seguir o padrão do PCTT, exemplo de um memorando de compra de materiais, deve-se procurar por "Compra".</li>
                       <li>O botão visualizar exibi como o documenento ficará ao ser gerado.</li>';
		$sHelp['formulariomemorando']['form']						= 
                      '<li>Unidade emissora, unidade superior ou responsável por emitir o memorando(Ex: ASCOM).</li>
                       <li>Unidade redatora, unidade de onde foi criada o memorando(Ex: DISAD).</li>
                       <li>Assuntos devem seguir o padrão do PCTT, exemplo de um memorando de compra de materiais, deve-se procurar por "Compra".</li>
                       <li>O botão visualizar exibi como o documenento ficará ao ser gerado.</li>';
		$sHelp['formulariominuta']['form']							= 
                      '<p><b>Unidade Emissora</b> &eacute; a unidade respons&aacute;vel pela assinatura e divulga&ccedil;&atilde;o do documento.</p>
                       <ul>
                           <li>Ao iniciar a digita&ccedil;&atilde;o de parte do nome, a tela mostra todas as op&ccedil;&otilde;es existentes na tabela de Lota&ccedil;&otilde;es do Ã³rg&atilde;o. Se n&atilde;o for escolhido o item da lista, o sistema gera erro.</li>
                       </ul>

                       <p><b>Unidade Redatora</b> &eacute; a unidade respons&aacute;vel pela reda&ccedil;&atilde;o do documento, podendo ser igual à unidade emissora do documento.</p>
                       <ul>
                           <li>Para localizar a unidade de lota&ccedil;&atilde;o, basta seguir os procedimentos listados para <i>unidade emissora</i>.</li>
                       </ul>

                       <p><b>N&uacute;mero do Documento Usu&aacute;rio</b> &eacute; o n&uacute;mero do documento existente hoje, registrado de forma manual. Este n&uacute;mero servir&aacute; como forma de pesquisa para localiza&ccedil;&atilde;o do documento.</p>

                       <p><b>Assunto do Documento</b> s&atilde;o os assuntos conforme constam da tabela do PCTT, que servir&aacute; para registrar a codifica&ccedil;&atilde;o do documento.</p>
                       <ul>
                           <li>Digitam-se as palavras relativas ao assunto e o sistema mostra a lista de assuntos cadastrada. Seleciona-se o assunto desejado.</li>
                       </ul>

                       <p><b>Campo Descri&ccedil;&atilde;o</b> &eacute; o campo onde devem ser digitadas as informa&ccedil;&otilde;es que facilitar&atilde;o a pesquisa e localiza&ccedil;&atilde;o do documento (utilizar palavras significativas).</p>
                       <p><b>Palavras Chave</b> campo o qual devem ser digitadas as palavras que lhe permitir&atilde;o localizar o documento no módulo de pesquisas (mais de uma palavra deve ser separada por v&iacute;rgula).</p>
                       <p><b>Estado do Documento</b></p>
                       <ul>
                           <li>Digital &eacute; o documento que foi criado de forma DIGITAL (padr&atilde;o do sistema).</li>
                           <li>Digitalizado &eacute; o documento que foi recebido pela unidade e foi digitalizado para ser tramitado atrav&eacute;s do sistema.</li>
                           <li>F&iacute;sico &eacute; o documento que foi recebido pela unidade, foi digitalizado e necessita ser encaminhado de forma f&iacute;sica para a unidade destinat&aacute;ria.</li>
                       </ul>

                       <p><b>Confidencialidade</b> indica qual o grau de sigilo do documento, sendo padr&atilde;o da minuta o tipo <i>restrito as partes</i>.</p>
                       <p><b>Cadastro Vistas</b> </p>
                       <ul>
                           <li>É necessário inserir no mínimo 1 pessoa com vistas a minuta. Só será possível encaminhar minuta para quem tem vistas.</li>
                       </ul>

                       <p><b>Tipo de armazenamento:</b></p>
                       <ul>
                          <li>
                            <p><b>Inserir Documento</b></p>
                            <ol>
                                <li>Clicar no bot&atilde;o Selecionar arquivo;</li>
                                <li>Localizar em qual pasta / diretório encontra-se o arquivo;</li>
                                <li>Selecionar e abrir arquivo.</li>
                            </ol>
                          </li>
                          <li>
                            <p><b>Utilizar editor on-line</b></p>
                            <ol>
                                <li>Digite as informações desejadas utilizando os recursos do editor para formatação.</li>
                            </ol>
                          </li>
                       </ul>   
                       
                       <p><strong>Cadastro de Vistas</strong></p>
                       <ul>
                           <li>Para os todos os documentos n&atilde;o públicos &eacute; obrigat&oacute;rio o cadastro de, pelo menos, uma pessoa com vistas.</li>
                       </ul>
                       
                       <p><strong>Enviar para:</strong></p>
                       <ul>
                           <li>Selecione a pessoa que receberá a minuta. Essa lista traz as pessoas cadastradas como vistas.</li>
                       </ul>
                       
                       <p><b>Aten&ccedil;&atilde;o</b>: Encerrada a última opera&ccedil;&atilde;o, deve-se clicar no bot&atilde;o SALVAR (ir&aacute; para a Caixa de Minutas de quem foi escolhido no item <i>Enviar para</i>).</p>
                       <br />
                       Para visualizar o tutorial de inserção de formulário da minuta <a href="' . $baseUrl . '/sisad/gerenciared/recuperar/id/188882/dcmto/52447810100229/tipo/1" target="_blank">Clique Aqui.</a>                   
                       ';
		$sHelp['formulariooficio']['form']							= 
                      '<li>Unidade emissora, unidade superior ou responsável por emitir o ofício(Ex: ASCOM).</li>
                       <li>Unidade redatora, unidade de onde foi criada o ofício(Ex: DISAD).</li>
                       <li>Assuntos devem seguir o padrão do PCTT, exemplo de um ofício sobre alterações em um determinado porcesso.</li>
                       <li>O botão visualizar exibi como o documenento ficará ao ser gerado.</li>';
		$sHelp['gerenciared']['form']								= '';
		$sHelp['lancamentofase']['form']							= '';
		$sHelp['partes']['cadastrapartes']	=
                      '<p><strong>Cadastro de Partes</strong> 
                       <ul>
                           <li>Podem ser cadastradas em documentos com qualquer tipo de confidencialidade.</li>
                           <li>Em documentos n&atilde;o p&uacute;blicos &eacute; necess&aacute;rio ter vistas ao documento para realizar o cadastro de novas partes.</li>
                           <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como partes.</li>
                           <li>Documentos da Corregedoria s&oacute; podem ter partes cadastradas por quem possuir a permiss&atilde;o da Corregedoria.</li>
                      </ul>  
                   </p>
                   <p><strong>Cadastro de Vistas</strong> 
                        <ul>
                           <li>Em Documentos P&uacute;blicos não &eacute; poss&iacute;vel cadastrar vistas.</li>
                           <li>Para realizar o cadastro de novas vistas &agrave; um documento &eacute; necess&aacute;rio ter vistas no mesmo.</li>
                           <li>Documentos Sigilosos e de Segredo de Justi&ccedil;a n&atilde;o podem ter Unidades Administrativas cadastradas como vistas.</li>
                           <li>Documentos da Corregedoria s&oacute; podem ter vistas cadastradas por quem possuir a permiss&atilde;o da Corregedoria.</li>
                      </ul> 
                   </p>
                   
                   <b>Composição do número do documento :</b> 
                   <ul>
                      <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                      <p>Onde:</p>
                      <p>AAAA   – ano de expedição do documento;</p>
                      <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                      <p>EEEEE  – unidade emissora;</p>
                      <p>RRRRR  – unidade redatora;</p>
                      <p>TTTT   – tipo do documento;</p>
                      <p>NNNNNN – número sequencial do documento. </p> 
                   </ul>';
		$sHelp['pesquisadcmto']['index']							= 
                      '<p><b>Unidade Emissora</b> é a unidade responsável pela assinatura e divulgação do documento.</p>
                       <ul>
                           <i>Nota</i>: Ao iniciar a digitação de parte do nome da unidade que emitiu o documento, a tela mostra todas as opções existentes na tabela de Lotações do órgão.
                       </ul>
                       <p><b>Unidade Redatora</b> é a unidade responsável pela redação do documento, podendo ser igual a unidade emissora do documento(idem ao recurso preenchimento).</p>
                       <br />
                       <p><b>Tipo Documento</b> é uma lista com todos os tipos de documentos que constam do <i>Manual de Documentos Administrativos</i>.</p>
                       <br />
                       <p><b>Número do Documento do Usuário</b> é o número do documento existente hoje, registrado de forma manual.</p>
                       <ul>
                           <i>Nota</i>: este número servirá como forma de pesquisa para localização do documento.
                       </ul>
                       <p><b>Assunto do Documento</b> são os assuntos conforme constam da tabela do PCTT.</p>
                       <ul>
                           <i>Nota</i>: servirá para registrar a codificação do documento e permitir que o sistema controle o tempo de guarda e gere relatórios para descarte.</p>
                       </ul>
                       <p><b>Palavras Chave</b> neste campo devem ser digitadas as palavras que lhe permitirão localizar o documento no módulo de pesquisas.</p>
                       <br />
                       <p><b>Estado do Documento</b>:</p>
                       <ul>
                           <li><i>Digital</i> - é o documento que foi criado de forma DIGITAL, tendo sido assinado ou certificado de forma digital.</li>
                           <li><i>Digitalizado</i> - é o documento que foi recebido pela unidade e foi digitalizado para ser tramitado através do sistema.</li>
                           <li><i>Físico</i> - é o documento que foi recebido pela unidade, foi digitalizado e necessita ser encaminhado de forma física para a unidade destinatária.</li>
                       </ul>
                       <p><b>Confidencialidade</b> indica qual o grau de sigilo do documento, sendo padrão do sistema o tipo <i>Público</i>.</p>
                       <br />
                       <p><b>Data inicial</b> registrar a data de provável criação do documento. O sistema mostra um calendário para que se selecione a data da pesquisa desejada.</p>
                       <br />
                       <p><b>Data final</b> registrar a data de provável criação do documento. O sistema mostra um calendário para que se selecione a data da pesquisa desejada.</p>
                       <br />
                       <p><b>Importante</b>:</p>
                       <ol>
                           <li>As pesquisas não trazem documentos localizados na <i>Caixa Pessoal/Rascunhos</i>, pois estes são documentos que ainda <b>não</b> estão prontos para tramitação.</li>
                           <li>O formulário permite a pesquisa por qualquer campo disponível.</li>
                           <li>Serão mostrados no máximo 200 documentos por pesquisa.</li>
                           <li>Informe o máximo de dados possíveis para que a pesquisa contenha o menor número  de documentos, facilitanto a localização.</li>
                           <li>Poderá ser realizada pesquisa somente por data.</li>
                       </ol>
                       <p><b>Composição do número do documento -</b> 
                         <ul>
                            <p>AAAA.OOOO.EEEEE.RRRRR.TTTT.NNNNNN</p>
                            <p>Onde:</p>
                            <p>AAAA   – ano de expedição do documento;</p>
                            <p>OOOO   – órgão da 1ª Região (ou órgão de origem do documento. Ex.: TRF1, SJAC, SJAM, SJAP, etc);</p>
                            <p>EEEEE  – unidade emissora;</p>
                            <p>RRRRR  – unidade redatora;</p>
                            <p>TTTT   – tipo do documento;</p>
                            <p>NNNNNN – número sequencial do documento. </p> 
                          </ul>  
                       </p>
                       <p>
                           <b>Botões na Tela</b>
                           <ul>
                               <li>O botão <b>Juntar Documento à Processo</b> seve para adicionar um documento à um processo.</li>
                           </ul>
                       </p>
                   <!--    <a target="_blank" title="Documento de ajuda completo" href="<?php echo $this->baseUrl(); ?>/sisad/gerenciared/recuperar/dcmto/472880100233">Ajuda completa.</a>-->
                       ';
		$sHelp['protocolo']['form']								= '';
		$sHelp['tipocaixa']['form']								= '';
		$sHelp['tipoprocesso']['form']								= '';
		
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