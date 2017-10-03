<?php
/**
 * @category	TRF1
 * @package		Trf1_Sisad_Informacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe para exibição de informacao para exibição na tela
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
class Trf1_Sisad_Informacao
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
	 * Busca dados de informacao para exibição na tela
	 * 
	 * @param	string		$sController
	 * @param	string		$sAction
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getInformacao($sController, $sAction)
	{
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
		$sHelp['autuar']['form']									= '';
		$sHelp['cadastrodcmto']['form']							= '';
		$sHelp['cadastrodcmtoext']['form']							= '';
		$sHelp['caixaentrada']['form']								= '';
		$sHelp['caixapessoal']['form']								= '';
		$sHelp['caixaunidade']['form']								= '';
		$sHelp['categorias']['form']								= '';
		$sHelp['detalhedcmto']['form']								= '';
		$sHelp['distribuicao']['form']								= '';
		$sHelp['etiqueta']['form']									= '';
		$sHelp['faseadm']['form']									= '';
		$sHelp['formularioaviso']['form']   = 
                      '<ul>
                          <li>AVISO é o tipo de documento utilizado para comunicar assuntos administrativos de interesse dos servidores.</li>
                          <li>O AVISO deve ter conteúdo simples, objetivo e com informações precisas.</li>
                          <li>O AVISO não deve ser numerado. O título é colocado por extenso, centrado na parte superior do impresso, mencionando-se a natureza do mesmo.</li>
                          <li>O AVISO deve ser afixado nos quadros próprios para tal finalidade. Dependendo da natureza do assunto, pode, ainda, ser emitido em número de vias e cópias estritamente necessário ao quantitativo de órgãos ou pessoas aos quais se destinar.</li>
                          <li>Do fecho devem constar local, dia, mês e ano, bem como a assinatura aposta ao nome completo do emissor e à identificação do cargo/órgão</li>
                      </ul>';
		$sHelp['formulariocadtreinamento']['form']					= '';
		$sHelp['formulariocadtreinamento']['form']					= 'Texto de informacao deve ser inserido aqui...';
		$sHelp['formulariocircular']['form']						= 
                      '<li>CIRCULAR é toda comunicação reproduzida em vias, cópias ou exemplares de igual teor, através da qual a autoridade dirige-se, ao mesmo tempo, a diferentes órgãos ou pessoas.</li>
                       <li>A emissão de CIRCULARES é privativa do Juiz-Presidente, do Juiz Vice-Presidente e Corregedor, dos Juízes Federais Diretores de Foro, do Diretor-Geral da Secretaria do Tribunal e dos Diretores em nível de Secretaria.</li>
                       <li>A CIRCULAR destina-se a transmitir instruções de serviço, ordens, avisos, decisões ou esclarecimentos sobre objetivos, políticas, diretrizes, programas de trabalho e normas administrativas e operacionais do Tribunal.</li>
                       <li>A CIRCULAR deve ser emitida no impresso próprio para correspondência:
                           <ul>
                               <li>do Presidente e do Vice-Presidente e Corregedor: IMP.15-02-02 (primeira folha) e IMP.15-02-03 (folha de continuação);</li>
                               <li>de uso geral: IMP.15-02-04 (primeira folha) e IMP.15-02-05 (folha de continuação).</li>
                           </ul>
                       </li>
                       <li>A CIRCULAR possui a seguinte estrutura:
                           <ul>
                               <li>título: o nome CIRCULAR por extenso, sigla do órgão emitente, número e data, centrados na parte superior do impresso. Exemplo: CIRCULAR/DIGES/N. 001, DE 30.03.90;</li>
                               <li>destinatário: deve vir três linhas abaixo do título e constitui-se da forma de tratamento e cargo daqueles a quem o documento é dirigido. Exemplo: Aos Senhores Diretores de Secretaria;</li>
                               <li>texto: desenvolvimento da matéria. Os parágrafos devem ser numerados com algarismos arábicos, exceto o primeiro;</li>
                               <li>fecho de cortesia simplificado;</li>
                               <li>assinatura: aposta ao nome completo e cargo do emitente;</li>
                               <li>vias:<br />
                   &nbsp;&nbsp;- original: para arquivo do emitente;<br />
                   &nbsp;&nbsp;- cópia para arquivo do órgão de Desenvolvimento Organizacional;<br />
                   &nbsp;&nbsp;- cópias em número suficiente para distribuição àqueles a quem se deseja comunicar o objeto da Circular.</li>
                           </ul>
                      </li>';
		$sHelp['formularioinformacao']['form']						= 
                      '<li>A INFORMAÇÃO é a nota manuscrita ou datilografada pela qual se fornecem, por solicitação ou determinação, elementos necessários e esclarecimentos sobre assuntos ou matérias que devam ser objeto de decisões de autoridades competentes.</li>
                       <li>São competentes para proferir INFORMAÇÕES os dirigentes e servidores do Tribunal que sejam conhecedores do assunto tratado.</li>
                       <li>A INFORMAÇÃO deve primar pela clareza, precisão e objetividade e estar isenta de parcialidade, eximindo-se de considerações subjetivas ou aleatórias.</li>
                       <li>Quando solicitado ou determinado estudo quanto ao mérito, a INFORMAÇÃO deve ser fundamentada, indicando dispositivos legais pertinentes.</li>
                       <li>A INFORMAÇÃO deve ser proferida preferencialmente no corpo (anverso e verso) do documento objeto do assunto tratado. Quando não for possível, deve ser proferida em papel separado, para essa finalidade.</li>
                       <li>Quando a INFORMAÇÃO for proferida em papel separado, deve ser observada a seguinte estrutura:
                           <ul>
                               <li>apresentação: - os impressos para informacões sem pauta devem ser datilografados ou emitidos via sistema automatizado em folha de continuação de ofício - IMP.15-02-05;<br />
                               &nbsp;&nbsp;- os impressos para informações pautados devem ser manuscritos - IMP.15-02-01;</li>
                               <li>título: por extenso, seguido do número e data, posicionado na parte superior esquerda do impresso;</li>
                               <li>referência: número do processo ou documento de origem, posicionado na linha seguinte à do título;</li>
                               <li>ementa/assunto: quando se tratar da primeira informação do processo, deve ser colocado duas linhas abaixo do título na margem esquerda do impresso;</li>
                               <li>vocativo: adequado ao destinatário;</li>
                               <li>conclusão: precisa e clara;</li>
                               <li>local e data;</li>
                               <li>assinatura: aposta ao nome, identificação do cargo e órgão do emitente.</li>
                           </ul>
                       </li>
                       <li>Quando conveniente ou necessário, deve ser adotada a divisão em capítulos numerados em algarismos romanos, com a respectiva titulação.</li>';
		$sHelp['formulariomemorando']['form']						= 
                      '<li>O MEMORANDO é o documento utilizado para formalizar a comunicação interna do Tribunal e das Seccionais, entre todas as suas unidades administrativas, obedecidos os níveis hierárquicos, quando se tratar de assuntos simples ou rotineiros.</li>
                       <li>O MEMORANDO objetiva atender, entre outros, os seguintes casos:
                           <ul>
                               <li>solicitação de execução de serviços;</li>
                               <li>compras de materiais;</li>
                               <li>marcação de reuniões;</li>
                               <li>solicitação de informações;</li>
                               <li>encaminhamento de documentos e providências rotineiras.</li>
                           </ul>
                       </li>
                       <li>O MEMORANDO pode ser emitido por quaisquer dirigentes do Tribunal ou das Seccionais. Ele é a correspondência usada para formalizar a comunicação entre a chefia imediata e os servidores, bem como para comunicação de Grupos de Trabalho e Comissões.</li>
                       <li>As comunicações dirigidas aos Magistrados não devem ser feitas por meio de MEMORANDO.</li>
                       <li>Quando o assunto objeto do MEMORANDO for do interesse de diferentes órgãos ou pessoas, deve ser emitido o MEMORANDO CIRCULAR, seguindo a mesma numeração do MEMORANDO. O número de vias do documento variará em função da quantidade de destinatários. O termo CIRCULAR deve ser acrescentado ao título, abreviado para CIRC. Exemplo: MEM. CIRC. N.123.</li>
                       <li>O MEMORANDO tramita sem envelope, exceto quando o assunto é considerado de caráter confidencial, e, neste caso, no envelope, deve constar carimbo ou etiqueta com o termo "CONFIDENCIAL".</li>
                       <li>O MEMORANDO é emitido no impresso IMP.15-02-05, com a seguinte estrutura:
                           <ul>
                               <li>ítulo: abreviado para MEM., seguido de número, e colocado na margem esquerda superior do impresso, sem necessidade de identificação da unidade emitente. Ex.: MEM. N. 001;</li>
                               <li>data: colocada na mesma linha do título, deslocada para a direita, no formato dd.mm.aa, sem necessidade de identificação do local;</li>
                               <li>cabeçalho: duas linhas abaixo do título, devem ser identificadas a unidade emitente e a destinatária, por meio das expressões DE/PARA ou DO/AO;</li>
                               <li>vocativo: adequado ao destinatário;</li>
                               <li>texto: exposição do assunto de forma objetiva e concisa;</li>
                               <li>fecho de cortesia: simplificado, ATENCIOSAMENTE ou RESPEITOSAMENTE, de acordo com a hierarquia do destinatário;</li>
                               <li>assinatura: aposta ao nome, identificação do cargo e área emitente.</li>
                           </ul>
                       </li>';
		$sHelp['formulariominuta']['form']							= '';
		$sHelp['formulariooficio']['form']							= 
                       '<li>OFÍCIO é o tipo de documento utilizado para formalizar a comunicação oficial do Tribunal e das Seções Judiciárias com outros órgãos ou autoridades públicas, com particulares, em caráter oficial, bem como com os Magistrados.</li>
                        <li>São competentes para emitir OFÍCIO:
                            <ul>
                                <li>Presidente e Vice-Presidente e Corregedor do Tribunal;</li>
                                <li>Juízes do Tribunal e Juízes Federais;</li>
                                <li>Diretor-Geral da Secretaria do Tribunal;</li>
                                <li>Diretores de Secretaria ou de órgão de nível hierárquico equivalente do Tribunal;</li>
                                <li>Diretores de Secretaria Administrativa das Seções Judiciárias.</li>
                                <li>Em casos especiais, a critério do superior hierárquico, o OFÍCIO pode ser emitido por outros dirigentes.</li>
                            </ul>
                        </li>
                        <li>Não deve ser emitido OFÍCIO CIRCULAR.</li>
                        <li>O OFÍCIO tem a seguinte estrutura:
                            <ul>
                                <li>título: a palavra OFíCIO, seguida da sigla do órgão e do número, colocados na parte superior esquerda do impresso. Exemplo: OFÍCIO/PRESI/N. 025;</li>
                                <li>local e data: nome da cidade, sigla do Estado e a data por extenso, colocados na mesma linha do título, observando-se a margem direita do impresso;</li>
                                <li>vocativo: o vocativo adequado ao remetente deve ser colocado quatro linhas abaixo do título, na margem destinada aos parágrafos;</li>
                                <li>texto: exposição do assunto. Se o texto possuir vários parágrafos, recomenda-se numerá-los a partir do segundo, com algarismos arábicos, excetuando-se o último referente ao fecho; o início do texto deve ser objetivo, devendo-se evitar as longas e tradicionais aberturas de cortesia;</li>
                                <li>fecho: utilizar fecho de cortesia simplificado, de acordo com o grau de formalidade que se deve dispensar ao destinatário;</li>
                                <li>assinatura: aposta ao nome completo do emitente, identificação do órgão e cargo do emitente;</li>
                                <li>destinatário: indicado sempre na primeira página do OFÍCIO, na parte inferior esquerda do impresso. Deve constar da forma de tratamento, o nome e o cargo do destinatário, o nome da cidade e a sigla da Unidade da Federação.</li>
                            </ul>
                        </li>
                        <li>A via original do OFÍCIO deve ser encaminhada, preferencialmente, por meio de envelope branco, timbrado, formato 110mmx229mm, obedecendo-se o disposto no título V do módulo 02 desta IN.</li>
                        <li>Quando o OFÍCIO ocupar mais de uma folha, devem ser observadas as disposições constantes do título II do módulo 02 desta IN.</li>';
		$sHelp['gerenciared']['form']								= '';
		$sHelp['lancamentofase']['form']							= '';
		$sHelp['partes']['form']									= '';
		$sHelp['pesquisadcmto']['form']							= '';
		$sHelp['protocolo']['form']								= '';
		$sHelp['tipocaixa']['form']								= '';
		$sHelp['tipoprocesso']['form']								= '';
		
		/* ==========================================================================================
		 * Término do trecho de definição dos textos de informacao
		 * 
		 * Retorno das strings para exibição na tela 
		========================================================================================== */
		// Seleção da informacao conforme controller / action informados como parâmetros
        $txtInformacao = '';
        if(isset($sHelp[$sController][$sAction])){
            $txtInformacao		= $sHelp[$sController][$sAction];
        }
		
		// Ajuste para informacao ainda não informada
		if ($txtInformacao == '') {
			$txtInformacao = 'Nenhuma informacao disponível.';
		}
		
		$txtFixoInicio	= '' . '<br /><br />';
		$txtFixoTermino	= '<br /><br />' . '';
		
		// Acréscimos de textos fixos, se for o caso
		//$txtFinal = $txtFixoInicio . $txtInformacao . $txtFixoTermino;
		$txtFinal = $txtInformacao;
		
		// Retorno da função
		return $txtFinal;
	}
	
}