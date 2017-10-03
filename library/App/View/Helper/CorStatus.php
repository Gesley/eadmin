<?php
/**
 * Esta classe serve para adicionar as cores dos status nas caixas
 * e as cores dos status na legenda
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta[at]trf1.jus.br
 * @license Free to use - no strings.
 */
class App_View_Helper_CorStatus extends Zend_View_Helper_Abstract
{
    
    private static $tabela = '';
    private static $classCaixa = '';

    public static function corCaixa($controller, $fase, $espera, $tempoTotal, $atendente)
    {
	    switch ($controller) {
			case 'atendimentosecoes':
             	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= (($tempoTotal >= 480) && (strlen($atendente) < 5))?('class="tempoUltrapassado" title="Solicitação com o tempo de atendimento ultrapassado"'):('');  
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');
                break;
            case 'atendimentotecnico':
				self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= (($tempoTotal >= 57600)  && (strlen($atendente) < 5))?('class="tempoUltrapassado" title="Solicitação com o tempo de atendimento ultrapassado"'):('');  
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');
                break;
            case 'bancodadosrede':
          		self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= (($tempoTotal >= 600) && (strlen($atendente) < 5))?('class="tempoUltrapassado" title="Solicitação com o tempo de atendimento ultrapassado"'):('');  
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');
                break;
            
            case 'caixapessoal':
              	self::$classCaixa =  ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');
                self::$classCaixa .= ($fase == 1085)?('class="emhomologacao" title="Em Homologação"'):('');  
                self::$classCaixa .= ($fase == 1086)?('class="homologada" title="Homologada"'):('');  
                self::$classCaixa .= ($fase == 1087)?('class="naohomologada" title="Não Homologada"'):('');  
                break;
            
            case 'desenvolvimentosustentacao':
                self::$classCaixa = ($fase == 1056)?('class="pedidoCancelamento" title="Pedido de cancelamento de solicitação"'):('');
                self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
              	self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
                self::$classCaixa .= ($fase == 1085)?('class="emhomologacao" title="Em Homologação"'):('');  
                self::$classCaixa .= ($fase == 1086)?('class="homologada" title="Homologada"'):('');  
                self::$classCaixa .= ($fase == 1087)?('class="naohomologada" title="Não Homologada"'):('');  
                break;
            
            case 'gestaodemandasinfraestrutura':
            	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
             break;
            case 'gestaodedemandasdoatendimentoaosusuarios':
            	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
                break;
            case 'gestaodedemandasdoatendimentoaosusuariossecoes':
            	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
                break;
            case 'gestaodedemandasdonoc':
				self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
                break;
            case 'gestaodedemandasti':
            	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
                break;
            case 'helpdesk':
            	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= (($tempoTotal >= 480) && (strlen($atendente) < 5))?('class="tempoUltrapassado" title="Solicitação com o tempo de atendimento ultrapassado"'):('');  
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');
                break;
            case 'noc':
            	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= (($tempoTotal >= 600) && (strlen($atendente) < 5))?('class="tempoUltrapassado" title="Solicitação com o tempo de atendimento ultrapassado"'):('');  
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');
                break;
            case 'pesquisarsolicitacoes':
            	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
                self::$classCaixa .= ($fase == 1085)?('class="emhomologacao" title="Em Homologação"'):('');  
                self::$classCaixa .= ($fase == 1086)?('class="homologada" title="Homologada"'):('');  
                self::$classCaixa .= ($fase == 1087)?('class="naohomologada" title="Não Homologada"'):(''); 
                
                break;
            case 'servicoexterno':
				self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= (($tempoTotal >= 57600) && (strlen($atendente) < 5))?('class="tempoUltrapassado" title="Solicitação com o tempo de atendimento ultrapassado"'):('');  
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
                break;
            case 'suporteespecializado':
            	self::$classCaixa = ($fase == 1025)?('class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($fase == 1024)?('class="pedidoInformacao" title="Pedido de informação para solicitação de TI"'):('');
                self::$classCaixa .= ($espera)?('class="espera" title="Solicitação colocada em espera"'):('');
                self::$classCaixa .= (($tempoTotal >= 57600) && (strlen($atendente) < 5))?('class="tempoUltrapassado" title="Solicitação com o tempo de atendimento ultrapassado"'):('');  
                self::$classCaixa .= ($fase == 1019)?('class="recusada" title="Solicitação recusada"'):('');  
                break;
			case 'minhassolicitacoes':
                self::$classCaixa = ($fase == 1056)?('class="pedidoCancelamento" title="Pedido de cancelamento de solicitação"'):('');
                break;
            default:
                break;
        }
        return self::$classCaixa;
    }

  public static function corLegenda()
    {
        self::$tabela = 
        '<table class="legenda ui-widget-content" style="margin-top:1.4em">
             <thead>
                 <tr>
                     <th colspan="2">Legenda de Cores dos Status da Caixa de Atendimento</th>
                 </tr>
             </thead>
             <tbody>
                 <tr>
                     <td class="pedidoInformacao" title="Pedido de informação para solicitação de TI"></td>
                     <td>Pedido de informação para solicitação de TI</td>
                 </tr>
				  <tr>
                     <td class="pedidoCancelamento" title="Pedido de cancelamento de solicitação"></td>
                     <td>Pedido de cancelamento de solicitação</td>
                 </tr>
                 <tr>
                     <td class="respostaPedidoInformacao" title="Resposta ao pedido de informação para solicitação de TI"></td>
                     <td>Resposta ao pedido de informação para solicitação de TI</td>
                 </tr>
                 <tr>
                     <td class="espera" title="Solicitação colocada em espera"></td>
                     <td>Solicitação colocada em espera</td>
                 </tr>
                 <tr>
                     <td class="tempoUltrapassado" title="Solicitação com o tempo de atendimento ultrapassado"></td>
                     <td>Solicitação com o tempo de atendimento ultrapassado</td>
                 </tr>
                 <tr>
                     <td class="recusada" title="Solicitação recusada"></td>
                     <td>Solicitação recusada</td>
                 </tr>
				  <tr>
                     <td class="videorealizada" title="Videoconferência realizada"></td>
                     <td>Videoconferência realizada</td>
                 </tr>
                 <tr>
                     <td class="videoconferenciaamanha" title="Videoconferência agendada para amanhã"></td>
                     <td>Verificar videoconferência agendada para amanhã</td>
                 </tr>
                 <tr>
                     <td class="extensaoprazoamanha" title="Solicitação com extensão de prazo vencendo amanhã"></td>
                     <td>Solicitação com extensão de prazo vencendo amanhã</td>
                 </tr>
                 <tr>
                     <td class="emhomologacao" title="Solicitação em Homologação"></td>
                     <td>Solicitação em homologação</td>
                 </tr>
                 <tr>
                     <td class="homologada" title="Solicitação Homologada"></td>
                     <td>Solicitação homologada</td>
                 </tr>
                 <tr>
                     <td class="naohomologada" title="Solicitação não Homologada"></td>
                     <td>Solicitação não homologada</td>
                 </tr>
             </tbody>
         </table>';
        return self::$tabela;
    }
}