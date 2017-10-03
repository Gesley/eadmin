<?php
require_once("trf1_Biblioteca.php");
@header("cache-control: no-cache");
@header("pragma: no-cache");
@header("expires: no-cache");
if(  ((!isset($numpasta))&&(!isset($_GET['numpasta']))&&(!isset($_REQUEST['numpasta']))&&(!isset($_POST['numpasta'])))&&((!isset($numprocesso))&&(!isset($_GET['numprocesso']))&&(!isset($_REQUEST['numprocesso']))&&(!isset($_POST['numprocesso']))) ){
	MensagemErro("Não foi possível encontrar os arquivos anexos por falta de parâmetros!");
}
else{
	if((isset($numpasta))||(isset($_POST['numpasta']))||(isset($_REQUEST['numpasta']))||(isset($_GET['numpasta']))){
		if(isset($numpasta)){
			$NUM_PASTA = $numpasta;
		}
		elseif(isset($_POST['numpasta'])){
			$NUM_PASTA = $_POST['numpasta'];
		}
		elseif(isset($_REQUEST['numpasta'])){
			$NUM_PASTA = $_REQUEST['numpasta'];
		}
		else{
			$NUM_PASTA = $_GET['numpasta'];
		}		
	}
	else{
		if(!(isset($numprocesso))){
			if(isset($_POST['numprocesso'])){
				$numprocesso = $_POST['numprocesso'];
			}elseif(isset($_REQUEST['numprocesso'])){
				$numprocesso = $_REQUEST['numprocesso'];
			}else{
				$numprocesso = $_GET['numprocesso'];
			}
			if($numprocesso == ""){
				MensagemErro("O identificador do processo não pôde ser encontrado!");
				exit;
			}
		}
		if(@trim($SECAO)==""){
			if(@trim($_GET['SECAO'])!=""){
				$SECAO = $_GET['SECAO'];
			}elseif(@trim($_REQUEST['SECAO'])!=""){
				$SECAO = $_REQUEST['SECAO'];
			}else{
				if(@trim($_POST['SECAO'])!=""){
					$SECAO = $_POST['SECAO'];
				}
			}
		}
		$numprocesso = LimpaNumericoTxt($numprocesso);//00020594420104010000
		$QueryBuscaId = "SELECT PG_NR_UNICA.GETNUMPIDPROC(:PROCESSO) NUMPID FROM DUAL"; 
		if(@trim($SECAO)==""){
			$SECAO = "TRF1";
		}		
		$cursorBuscaId = ExecutaQueryTweb($QueryBuscaId,$SECAO,":PROCESSO",$numprocesso);
		$nrowsId = OCIFetchStatement($cursorBuscaId,$resultsBuscaId);
		if($nrowsId==0){
			MensagemErro("Não foi possível encontrar o processo $numprocesso !");
			exit;
		}
		$NUM_PASTA = $resultsBuscaId['NUMPID'][0];
		//echo "<strong>Processo Pesquisado:</strong> $numprocesso<br>\n\n"
		//."<strong>ID DA PASTA:</strong> $NUM_PASTA<br><br></strong>\n\n";
	}	
	//echo $resultsBuscaId['NUMP_ID'][0];exit;
	# PROCESSO = 200734007007288    /   8562441820094013400
	//$NUM_PASTA 		= 1954088;
	//$NUM_PASTA 			= "195408";
	if(@trim($NUM_PASTA)==""){
		MensagemErro("O identificador do processo não pôde ser encontrado!");
	}
	else{
		require_once('WebServices/RED/classListaDocRed.php');
		$services 				= new classListaDocRed();
		$DadosDocRed 			= $services->red()->consultarDadosNumIdProc ( $NUM_PASTA );
		$arrayResult 		= (array)$DadosDocRed['return'];
		if ( sizeof($arrayResult) > 0 ) {
			$contaInicio = 0;
			$Contar = 0;
			$arrayResultTrata = "";
			$Decode = false;
			foreach ($arrayResult as $key=>$value){
				if(is_array($value)) break;
				$Decode = true;
				$Chave = (string)$key;
				$Valor = utf8_decode((string)$value);
				if($Contar == true){
					$contaInicio++;
					$Contar = false;
				}
				$arrayResultTrata[$contaInicio][$Chave] = $Valor;
				if($Chave=="versaoAnotacao"){
					$contaInicio++;
				}
			}		
			if($arrayResultTrata!="")
				$arrayResult = $arrayResultTrata;
			$DataCons 	= date("Ymd_G_i_s");
			echo "<table align=center width=770px BORDER=\"0\" CELLSPACING=\"1\" CELLPADDING=\"1\">
			\n<tr><TD width=\"770px\" bgcolor=\"$corborda\">
			\n<TABLE border=0 cellspacing=0 width=770px>
			\n<TR bgcolor=$corlinha1><TD width=\"770px\">
			\n<font face=verdana color=$corfontecabec size=2><center><B>Documentos Anexos</center></FONT>
			\n</td></tr></table></td></tr>
			\n<tr><TD width=\"770px\" bgcolor=\"$corborda\">
			\n<table border=1 cellspacing=0 width=770px>\n";
			$ContaArqPublicos = 0;
			$SaidaLista = "";
			for ( $i = 0; $i < sizeof($arrayResult); $i++ ){
				$TamDown = false;
				$corlinha = ($corlinha == $corlinha2)?$corlinha3:$corlinha2;
				if($Decode)
					$c0 	= $arrayResult[$i]["descTipoDocumento"];
				else
					$c0 	= utf8_decode($arrayResult[$i]["descTipoDocumento"]);
				$c2		= $arrayResult[$i]["codigoDocumento"];
				$c1 	= $arrayResult[$i]["dataInclusao"];
				$c3		= $arrayResult[$i]['idSigiloDocumento'];
				if(($c3=="0")||($c3=="1")){
					if(strlen($c1)>6){
						$c1		= explode("-",$c1);
						$c1		= substr($c1[2],0,2) . "/" . $c1[1] . "/" . $c1[0];
					}
					if($SaidaLista == ""){
						$SaidaLista = $SaidaLista
						."
						\n<TR bgcolor=$corlinha1>
						\n<TD align=center width=\"95%\"><font face=verdana color=$corfontecabec size=2><B>Descrição do Documento</TD>
						\n<TD align=center width=\"1%\"><font face=verdana color=$corfontecabec size=2>&nbsp;&nbsp;<B>&nbsp;Data&nbsp;de&nbsp;Inclusão</b>&nbsp;&nbsp;&nbsp;</TD>
						\n<TD align=center width=\"1%\"><font face=verdana color=$corfontecabec size=2><B>Visualizar<sup>*</sup></TD>
						\n</TR>";
					}
					//descrição
					$SaidaLista = $SaidaLista
					."\n<TR bgcolor=$corlinha valign=\"top\"><TD>&nbsp;".$c0."&nbsp;</TD>"
					. "\n<TD align=center >&nbsp;".str_replace(" ","&nbsp;",$c1)."&nbsp;</TD>"
					. "\n<TD align=center>&nbsp;&nbsp;"
					. "\n<a target=\"_blank\" title=\"Visualizar o Arquivo\" href=\"/Processos/ProcessosTRF/ctrf1proc/ConsProcTrf1RedArquivo.php?nDocRedp=$c2&PROC=$numprocesso&SECAO=$SECAO\">"
					. "<img src=/Layout/trf1_icone_ver.gif border=0></a>&nbsp;&nbsp;&nbsp;"
					. "\n</TD></TR>\n";
				}
			}		
			if($SaidaLista == ""){
				SemDocs();
			}
			else{
				echo $SaidaLista . "</td></tr><tr><td bgcolor=\"#ffffff\" colspan=\"3\">"
				.""
				. "<ul><font face=verdana size=2 color=#000000>"
				. "<li><strong>Observações sobre a abertura e visualização dos arquivos:</strong>"
				. "<ul>"
				. "<li>O tamanho citado do arquivo é apenas estimativo, sendo este apenas um valor referencial.</li>"
				. "<li>Os Arquivos estão no formato \"PDF\" e para sua abertura é necessário o softwate <a href=\"http://www.adobe.com.br/products/acrobat/readstep2.html\" target=\"_new\" class=linka><i><strong>Acrobat Reader</strong></i></a> instalado em seu computador.</li>"
				. "<li>Os software citado é de responsabilidade de seu fabricante e a instalação desse componente deve ser realizada pelo usuário.</li>"
				. "</ul></li></ul>"
				. "</table></td></tr>\n"
				. "<tr><td>"
				. $MsgArq ."</td></tr>\n"
				. "</table>\n"
				. "</center></center></center></center></center></b></b></b></b></b>";
				}
		}
		else{
			SemDocs();
		}
	}
}
function MensagemErro($Msg){
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
	//exit;
}
function SemDocs(){
	global $corborda,$corfontecabec,$corlinha2,$corlinha1 ;
	echo "<table  align=\"center\" width=\"770px\" border=\"0\" cellspacing=\"0\" cellpadding=\"1\">\n
	<tr><td width=\"770px\" bgcolor=\"$corborda\"><table align=\"center\" border=\"0\" cellspacing=\"1\" width=\"770px\">\n
	<tr bgcolor=\"$corlinha1\"><td width=\"770px\">\n
	<font face=\"verdana\" color=\"$corfontecabec\" size=\"2\"><center><b>Documentos Digitais Anexos</b></center></font>\n
	</td></tr></table></td></tr>\n
	<tr><td width=\"770px\" bgcolor=\"$corborda\"><table align=\"center\" border=\"0\" cellspacing=\"1\" width=\"770px\">\n
	<TR bgcolor=\"$corlinha2\">\n
	<TD align=\"center\"><font class=\"titulo2\"><br>Não há documentos digitais para este processo.<br><br><font></TD>\n
	</tr>\n
	</table>\n
	</td>\n
	</tr>\n
	</table>\n
	</td>\n
	</tr>\n
	</table>\n";			
	//exit;
}
?>