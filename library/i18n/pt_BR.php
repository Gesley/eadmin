<?php

// Traduï¿½ï¿½o para o Portuguï¿½s por Nivaldo Arruda - nivaldo@gmail.com
$portugues = array();
$portugues['isEmpty']= 'Este campo não pode ser vazio';
$portugues['stringEmpty'] = "'%value%' é uma string vazia";

// Email
$portugues['emailAddressInvalid'] = 'Não um email válido no formato nome@servidor';
$portugues['emailAddressInvalidFormat'] = 'Não um email válido no formato nome@servidor';

//hostname
$portugues['hostnameIpAddressNotAllowed']  = "'%value%' Parece ser um endereï¿½o de IP, mas endereï¿½os de IP nï¿½o sï¿½o permitidos";
$portugues['hostnameUnknownTld'] = "'%value%' parece ser um DNS, mas nï¿½o foi possivel validar o TLD";
$portugues['hostnameDashCharacter'] = "'%value%' parece ser um DNS, mas contï¿½m um 'dash' (-) em uma posiï¿½ï¿½o invï¿½lida";
$portugues['hostnameInvalidHostnameSchema'] = "'%value%' parece ser um DNS, mas nï¿½o foi possï¿½vel comparar com o schema para o TLD '%tld%'";
$portugues['hostnameUndecipherableTld'] = "'%value%' parece ser um DNS mas nï¿½o foi possï¿½vel extrair o TLD";
$portugues['hostnameInvalidHostname'] = "'%value% nï¿½o ï¿½ compatï¿½vel com a estrutura DNS";
$portugues['hostnameInvalidLocalName'] = "'%value%' nï¿½o parece ser uma rede local vï¿½lida";
$portugues['hostnameLocalNameNotAllowed'] = "'%value%' parece ser o nome de uma rede local, mas nome de rede local nï¿½o sï¿½o permitido";

//identical

$portugues['notSame'] = "Comparaï¿½ï¿½o nï¿½o bate";
$portugues['missingToken'] = "Nï¿½o foi fornecido parï¿½metros para teste";

//greater then
$portugues['notGreaterThan'] = "'%value%' nï¿½o ï¿½ maior que '%min%'";

//float
$portugues['notFloat'] = "'%value%' nï¿½o ï¿½ do tipo float";

//date
$portugues['dateNotYYYY-MM-DD'] = "'%value%' deve estar no formato aaaa-mm-dd";
$portugues['dateInvalid'] = "'%value%' nï¿½o parece ser um data vï¿½lida";
$portugues['dateFalseFormat'] = "'%value%' nï¿½o combina com o formato informado";

//digits
$portugues['notDigits'] = "'%value%' nï¿½o contï¿½m apenas dï¿½gitos";

//between
$portugues['notBetween'] = "'%value%' nï¿½o estï¿½ entre '%min%' e '%max%', inclusive";
$portugues['notBetweenStrict'] = "'%value%' nï¿½o estï¿½ estritamente entre '%min%' e '%max%'";

//alnum
$portugues['notAlnum'] = "'%value%' nï¿½o possuï¿½ apenas letras e dï¿½gitos";

//alpha
$portugues['notAlpha'] = "'%value%' nï¿½o possuï¿½ apenas letras";

//in array
$portugues['notInArray'] = "'%value%' nï¿½o foi encontrado na lista";

//int
$portugues['notInt'] = "'%value%' nï¿½o parece ser um inteiro";

//ip
$portugues['notIpAddress'] = "'%value%' nï¿½o parece ser um endereï¿½o ip vï¿½lido";

//lessthan
$portugues['notLessThan'] = "'%value%' nï¿½o ï¿½ menor que '%max%'";

//notempty
$portugues['isEmpty'] = "Campo vazio, mas um valor diferente de vazio ï¿½ esperado";

//regex
$portugues['regexNotMatch'] = "'%value%' nï¿½o foi validado na expressï¿½o '%pattern%'";

//stringlength
$portugues['stringLengthTooShort'] = "'%value%' ï¿½ menor que %min% (tamanho mï¿½nimo desse campo)";
$portugues['stringLengthTooLong'] = "'%value%' ï¿½ maior que  %max% (tamanho maximo desse campo)";

//$portugues[''] = "";
return $portugues;
?>