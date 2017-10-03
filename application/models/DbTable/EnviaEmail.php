<?php
class Application_Model_DbTable_EnviaEmail extends Zend_Db_Table_Abstract
{
    /**
     * @abstract Para as chamandas que não tratam a excessão da package 
     * envia email setar a variável como false, caso contrário setar como true.
     * @var bollean 
     */
    public $_setEnviarEmailThrowExceptionFlag = false;
    
    public function setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $mensagem)    
    {
        $a = explode(" - ", $sistema);
        $x = $a[0];
        
        if ($x == 'e-Sosti')
        {
            $esosti='<span style="font-weight:bold; text-decoration: underline;">
                        Respostas à solicitações de informação deverão ser realizadas através do e-Sosti.
                     </span>';
        }
                    
        $mensagemAviso = (APPLICATION_ENV == 'development') ? '
                            <tr>
								<td colspan="2"><h3 id="aviso">Teste de funcionalidade do Ambiente de TREINAMENTO (DESCONSIDERAR)</h3></td>
							</tr>' : '';
        /**
         * Cabeçalho do Email
         */
        $V_CABECALHO = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
				"http://www.w3.org/TR/html4/loose.dtd">
			<html>
				<head>
					<title>Untitled Document</title>
					<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
							<style type="text/css">
								* {
									margin:0;
									padding:0;
									border:0;
								}
								body {
									font:12px Tahoma, Helvetica,Verdana;
									line-height:1.3em;
								}
								h3{
									font-size: 14px;
									color:#fff;
								}
								table{
									margin:0 auto;
									width: 720px;
								}
								table td {
									padding: 0 10px;
								}
								td h3 {
									padding:10px 0;
									font-size:14px;
									font-weight:bold;
									color:#001a33;
								}
								th {
									background:#001A33;
									overflow:hidden;
								}
								th.cabecalho {
									width:325px;
									border-collapse:collapse;
								}
								th h3 {
									font-size:12px;
									color:#fff;
								}
								th img {
									float:left;
								}
								tfoot tr td {
									padding:10px;
								}
								tfoot span {
									color:#001a33;
									font-weight:bold;
								}
					</style>
				</head>
				<body>
					<table>
						<thead>
							<tr>
								<th class="cabecalho"><img src="http://portal.trf1.jus.br/trf/imgs/layout/logo.png"></th>
								<th><h3>e-Admin - Sistema de Gestão Administrativa Integrada<br/></h3></th>
							</tr>
						</thead>
						
						<tbody>
                                                       '.$mensagemAviso.'
							<tr>
								<td colspan="2"><h3>'.$sistema.'</h3></td>
							</tr>
							<tr>
								<td colspan="2"> 
									'.$mensagem.'
								</td>
							</tr>
						</tbody>

						<tfoot>
							<tr>
                                                            <td colspan="2">Grato pela atenção!</td>
							</tr>
							<tr>
                                                            <td colspan="2">
                                                                <span style="font-weight:bold;">
                                                                    Este é um e-mail automático. Por favor não responder.
                                                                </span>
                                                                <br />
                                                                '.$esosti.'   
                                                            </td>
							</tr>
						</tfoot>
					</table>
				</body>
			</html>
 ';
       
       $email = $V_CABECALHO;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try {
            $db->query("BEGIN envia_email.ENVIA_VARCHAR2('$remetente', '$destinatario','$assunto','$email', TRUE);END; ");
        } catch (Exception $exc) {
            if ($this->_setEnviarEmailThrowExceptionFlag) {
                throw $exc;
            } else {
                return;
            }
        }
        return;
    }

//}

public function setEnviarEmailExterno($sistema, $remetente, $destinatario, $assunto, $mensagem)    
    {
        $a = explode(" - ", $sistema);
        $x = $a[0];
        
        if ($x == 'e-Sosti')
        {
            $esosti='<span style="font-weight:bold; text-decoration: underline;">
                        Respostas à solicitações de informação deverão ser realizadas através do e-Sosti.
                     </span>';
        }
                    
        $mensagemAviso = (APPLICATION_ENV == 'development') ? '
                            <tr>
								<td colspan="2"><h3 id="aviso">Teste de funcionalidade do Ambiente de TREINAMENTO (DESCONSIDERAR)</h3></td>
							</tr>' : '';
        /**
         * Cabeçalho do Email
         */
        $V_CABECALHO = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
				"http://www.w3.org/TR/html4/loose.dtd">
			<html>
				<head>
					<title>Untitled Document</title>
					<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
							<style type="text/css">
								* {
									margin:0;
									padding:0;
									border:0;
								}
								body {
									font:12px Tahoma, Helvetica,Verdana;
									line-height:1.3em;
								}
								h3{
									font-size: 14px;
									color:#fff;
								}
								table{
									margin:0 auto;
									width: 720px;
								}
								table td {
									padding: 0 10px;
								}
								td h3 {
									padding:10px 0;
									font-size:14px;
									font-weight:bold;
									color:#001a33;
								}
								th {
									background:#001A33;
									overflow:hidden;
								}
								th.cabecalho {
									width:325px;
									border-collapse:collapse;
								}
								th h3 {
									font-size:12px;
									color:#fff;
								}
								th img {
									float:left;
								}
								tfoot tr td {
									padding:10px;
								}
								tfoot span {
									color:#001a33;
									font-weight:bold;
								}
					</style>
				</head>
				<body>
					<table>
						<thead>
							<tr>
								<th class="cabecalho"><img src="http://portal.trf1.jus.br/trf/imgs/layout/logo.png"></th>
								<th><h3>e-Admin - Sistema de Gestão Administrativa Integrada<br/></h3></th>
							</tr>
						</thead>
						
						<tbody>
                                                       '.$mensagemAviso.'
							<tr>
								<td colspan="2"><h3>'.$sistema.'</h3></td>
							</tr>
							<tr>
								<td colspan="2"> 
									'.$mensagem.'
								</td>
							</tr>
						</tbody>

						<tfoot>
							<tr>
                                                            <td colspan="2">Grato pela atenção!</td>
							</tr>
						
						</tfoot>
					</table>
				</body>
			</html>
 ';
       
       $email = $V_CABECALHO;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try {
            $db->query("BEGIN envia_email.ENVIA_VARCHAR2('$remetente', '$destinatario','$assunto','$email', TRUE);END; ");
        } catch (Exception $exc) {
            if ($this->_setEnviarEmailThrowExceptionFlag) {
                throw $exc;
            } else {
                return;
            }
        }
        return;
    }

}