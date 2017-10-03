/*
 * Obtém a applet do SDK de assinatura.
 */
function getApplet(){
	return document.getElementById('assinadorDigital');
}

/**
 * @return Verdadeiro caso as bibliotecas do e-Assinador tenham sido encontradas.
 */
function existeAssinador() {
	var retorno = getApplet().existeAssinador();
	return (retorno && retorno.toUpperCase() == "TRUE");
}

/**
 * @return Verdadeiro caso o e-assinador precise ser atualizado
 */
function isAtualizar() {
	return getApplet().isAtualizar();
}

/**
 * Inicia uma sessão para o uso apenas da chave privada selecionada.
 */
function iniciarSessaoChave() {
    getApplet().iniciarSessaoChave();
}

/**
 * Fecha a sessão de uso da chave privada.
 */
function fecharSessaoChave() {
    getApplet().fecharSessaoChave();
}

/**
 * Codifica os dados informados em Hexadecimal.
 *
 * @param dados Dados para a codificação
 * @return Dados codificados
 */
function encodeToHex(dados) {
	return getApplet().encodeToHex(dados);
}

/**
 * Decodifica os dados em hexadecimal informados.
 *
 * @param dados Dados para a decodificação
 * @return Dados decodificados
 */
function decodeToHex(dados) {
	return getApplet().decodeToHex(dados);
}

/*
 * Carimba um dado.
 * parâmetros:
 * 		conteudo: conteúdo que será carimbado.
 * retorno: string contendo carimbo de tempo em hexadecimal.
 */
function carimbarDados(dados) {
	return getApplet().carimbar(dados);
}

/*
 * Assina um conteúdo no formato detached.
 * parâmetros:
 * 		conteudo: conteúdo que será assinado.
 * retorno: string conteúdo o array de bytes no formato pkcs#7 em hexadecimal.
 */
function assinarConteudoDetached(conteudo) {
	return getApplet().assinarConteudoDetached(conteudo);
}

/*
 * Assina um conteúdo no formato attached.
 * parâmetros:
 * 		conteudo: conteúdo que será assinado.
 * retorno: string conteúdo o array de bytes no formato pkcs#7 em hexadecimal.
 */
function assinarConteudoAttached(conteudo) {
	return getApplet().assinarConteudoAttached(conteudo);
}

/*
 * Assina um conteúdo no formato attached.
 * parâmetros: 
 * 		conteudo: conteúdo que será assinado.
 * 		algoritmoOID: OID do algoritmo que será usado para assinar.
 * retorno: string conteúdo o array de bytes no formato pkcs#7 em hexadecimal.
 */
function assinarConteudoDetachedAlgoritmo(conteudo, algoritmoOID) {
	return getApplet().assinarConteudoDetached(conteudo, algoritmoOID);
}

/*
 * Assina um conteúdo no formato detached.
 * parâmetros: 
 * 		conteudo: conteúdo que será assinado.
 * 		algoritmoOID: OID do algoritmo que será usado para assinar.
 * retorno: string conteúdo o array de bytes no formato pkcs#7 em hexadecimal.
 */
function assinarConteudoAttachedAlgoritmo(conteudo, algoritmoOID) {
	return getApplet().assinarConteudoAttached(conteudo, algoritmoOID);
}

/*
 * Valida a assinatura do arquivo.
 * parâmetros:
 *      conteudo = conteúdo que foi assinado.
 * 		envelope = envelope de assinatura em hexadecimal.
 * retorno: retorna true caso a assinatura seja validada com sucesso.
 */
function validarAssinatura(conteudo, envelope) {
	return getApplet().validarAssinatura(conteudo, envelope);
}

/*
 * Efetua uma verificação na validade do certificado quando foi gerado o carimbo de tempo.
 * parâmetros:
 * 		carimbo = carimbo gerado no momento da assinatura em hexadecimal.
 * retorno: retorna true caso o carimbo de tempo esteja valido.
 */
function validarValidadeComDataCarimbo(carimbo) {
	return getApplet().validarValidadeComDataCarimbo(carimbo);
}

/*
 * Efetua uma comparação entre o hash carimbado e o conteudo carimbado informado.
 * parâmetros:
 * 		carimbo = carimbo gerado no momento da assinatura em hexadecimal.
 * 		conteudoCarimbado = conteúdo que foi carimbado.
 * retorno: retorna true caso o hash carimbo corresponda ao hash do conteúdo informado.
 */
function validarHashCarimbado(carimbo, conteudoCarimbado) {
	return getApplet().validarHashCarimbado(carimbo, conteudoCarimbado);
}

/*
 * Verifica a integridade de um protocolo. O objetivo é verificar a integridade dos processos,
 * identificando alteraçõeses ou substituições de documentos assinados digitalmente.
 * parâmetros:
 * 		tsAnterior = carimbo de tempo do documento imediatamente posterior no processo de protocolo em hexadecimal.
 * 		tsAtual = carimbo de tempo de um documento em hexadecimal.
 * 		protocolo = protocolo digital do último carimbo de tempo em hexadecimal.
 * retorno: true caso o protocolo esteja íntegro.
 */
function validarProtocolo(tsAnterior, tsAtual, protocolo) {
	return getApplet().validarProtocolo(tsAnterior, tsAtual, protocolo);
}

/*
 * Cria protocolo.
 * parâmetros:
 * 		tsProtocolo = timeStamp do ultimo protocolo, podendo ser nulo caso seja o primeiro em hexadecimal.
 * 		tsAtual = timeStamp do documento a ser anexado ao protocolo em hexadecimal.
 * retorno: Retorna um array de strings com tamanho 2, onde a primeira string é o carimbo de tempo 
 * e a segunda e o protocolo.
 */
function criarProtocolo(tsProtocolo, tsAtual) {
	var resultado = getApplet().criarProtocolo(tsProtocolo, tsAtual);
	return resultado.split("|");
}

/*
 * Criptografa um conteúdo.
 * parâmetros:
 * 		conteudo = conteúdo a ser criptografado.
 * retorno: resultado da criptografia em hexadecimal.
 */
function criptografar(conteudo) {
	return getApplet().criptografar(conteudo);
}

/*
 * Criptografa um conteúdo.
 * parâmetros:
 * 		conteudo = conteúdo a ser decriptografado em hexadecimal.
 * retorno: resultado da decriptografia.
 */
function decriptografar(conteudo) {
	return getApplet().decriptografar(conteudo);
}

/*
 * Gera o hash de um algoritmo usando o SHA-1.
 * parâmetros:
 * 		conteudo = conteúdo a ser gerado o hash.
 * retorno: hash do conteúdo em hexadecimal.
 */
function gerarHash(conteudo) {
	return getApplet().gerarHash(conteudo);
}

/**
 * Assina um hash gerado usando SHA1 no formato detached usando o algoritmo RSA. 
 * parâmetros:
 * 		hash = string com o hash.
 * retorno: resultado da decriptografia em hexadecimal.
 */
function assinarHashDetached(hash) {
	return getApplet().assinarHashDetached(hash);
}

/**
 * Carimba um hash em forma de string.
 * parâmetros:
 * 		hash = hash que será carimbado.
 * 		oidHash = algoritmo que foi usado para gerar o hash.
 * retorno: string representado o dado carimbado em hexadecimal.
 */
function carimbarHash(hash, oidHash) {
	return getApplet().carimbarHash(hash, oidHash);
}

/**
 * Obtem o valor do dado alternativo do Certificado
 *
 *Dados:
 	- TituloEleitor
    	- Zona
    	- Secao
    	- MunicipioEleitor
    	- UfEleitor
    	- DataNascimento
    	- Cpf
    	- Pis
    	- Rg
    	- OrgaoExpeditor
    	- Uf
    	- NomeResponsavel
        - Cnpj
        - InssPessoaJuridica
     	- InssPessoaFisica
   		- Pj
   		- ICPBrasil
 */
function obterDadosCertificado(dado) {
	return getApplet().obterDadosCertificado(dado);
}
 
 
 /**
  * Obtem o detalhamento do Certificado
  *
  *Dados:
  	- Email
     	- EmitidoPor
     	- InicioDataValidadeFormatada (DD/|MM|/AAAA)
     	- FimDataValidadeFormatada (DD/|MM|/AAAA)
     	- Nome
     	- NumeroSerie
     	- Pais
     	- Uf
  */
 function obterDetalhesCertificado(dado) {
 	return getApplet().obterDetalhesCertificado(dado);
 } 