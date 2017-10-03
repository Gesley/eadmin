<?php
require_once('classes/NovoRed.Class.php');
if((isset($nDocRedp))||(isset($_POST['nDocRedp']))||(isset($_GET['nDocRedp']))||(isset($_REQUEST['nDocRedp']))){
	$UrlEnvio = "";
	$numDocRedNovo = "";
	if(isset($_REQUEST['nDocRedp'])){
		$numDocRedNovo = $_REQUEST['nDocRedp'];
	}
	elseif(isset($_POST['nDocRedp'])){
		$numDocRedNovo = $_POST['nDocRedp'];
	}
	elseif(isset($_POST['nDocRedp'])){
		$numDocRedNovo = $_GET['nDocRedp'];
	}
	else{
		$numDocRedNovo = "";
		$UrlEnvio = "";
		MensagemInErroT("Não foi possível encontrar o arquivo anexo!");	
	}
	if($numDocRedNovo!=""){
		$NovoRed = new DocNovoRed($numDocRedNovo); 
		if(@isset($NovoRed)){
			if($NovoRed->erro==true){
				$MsgTServer = $NovoRed->txtErro;
			}
			else if(sizeof($NovoRed->mensagem)>0){
				$MsgTServer = "";
				foreach($NovoRed->mensagem as $pl){
					$MsgTServer = $MsgTServer . $pl['descricao'] . "<br>";
				}
			}
			else{
				$UrlEnvio = $NovoRed->urlSaida[0]['url'];
				if(trim($UrlEnvio)!=""){
					$filename = $numDocRedNovo . date('d-m-Y') . ".pdf";
					$arquivo = open_https_url($UrlEnvio,"",false); 
					$len = strlen($arquivo); 
					$Pos = strpos($arquivo,"%PDF");
					$arquivo = substr($arquivo,$Pos);
					# Comandos setando que o conteudo que será aberto é do tipo PDF 
					$len= strlen($arquivo) ;  
					header("Content-type: application/pdf");
					header("Pragma: public");
					header("Cache-control: public");
					header("Content-Length: $len");
					header("Content-Disposition: attachment; filename=$filename");	
					echo $arquivo;
					/*
					echo "
					<center> \n<br><br>
					<font style=\"font-family:Verdana, Arial, Helvetica, sans-serif;font-size:12px;\">
					<strong>Arquivo Encontrado com Sucesso.</strong> \n 
					 \n
					<br><br>  \n
					<strong>Esta página será redirecionada para a abertura do arquivo.</strong>  \n
					</font>
					</center> \n
					 \n
					<META HTTP-EQUIV=\"Window-target\" CONTENT=\"_top\">
					<META HTTP-EQUIV=\"refresh\" content=\"2;url=$UrlEnvio\">  \n ";	
					*/		
				}
				else{
					MensagemInErroT("Não foi possível encontrar o arquivo anexo!");			
				}
			}
		}
		else{
			MensagemInErroT("Não foi possível encontrar o arquivo anexo!");	
		}
	}
}
else{
	MensagemInErroT("Erro de abertura!!");
}
function MensagemInErroT($Msg){
	echo "
	<html>
	\n<head>
	\n<title>Autenticação</title>
	\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
	\n<TITLE>TRF 1ª Região - Documentos Anexos</TITLE>
	\n<link href=\"/objetos/trf1_style.css\" rel=\"stylesheet\" type=\"text/css\">
	\n<script language=\"JavaScript1.2\" src=\"/objetos/trf1_layout.js\"></script>
	\n<script language=\"JavaScript1.2\" src=\"/objetos/trf1_barramenu.js\"></script>
	\n</head>
	\n<body>		
	\n<TABLE align=center BORDER=0 width=770px CELLSPACING=0 CELLPADDING=0 >
	\n<tr><td valign=top bgcolor=#ffffff align=center >
	\n<br><font size=5><strong>"
	.str_replace("\\n","<br>",$Msg)."<br></strong>
	\n</td></tr></table><br>
	\n</body>
	\n</html>";
	exit;
}
?>