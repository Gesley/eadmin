<?php

/**
 * @category	        TRF1
 * @package		Service_Sosti_RespostaPadrao
 * @copyright	        Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Daniel Rodrigues
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * TRF1, Classe negocial sobre Respostas Padrões do Sistema
 */
class Services_Sosti_RespostaPadrao {

    private $RespostaPadraoNs;
    private $trf1_negocio;
    private $userNs;
    private $tb_dual;

    public function __construct() {
        //VARIAVEL DA SESSAO PARA A RESPOSTA PADRAO
        $this->RespostaPadraoNs = new Zend_Session_Namespace('RespostaPadraoNs');
        //INSTANCIA DO MAPPER
        $this->trf1_negocio = new Trf1_Sosti_Negocio_RespostaPadrao();
        //INSTANCIA DA SESSÃO DO USUÁRIO
        $this->userNs = new Zend_Session_Namespace('userNs');
        //INSTANCIA DA TABELA DUAL PARA OBTER O SYSDATE
        $this->tb_dual = new Application_Model_DbTable_Dual();
    }

    /**
     * Retorna o valor do IdGrupo, setado na variável da sesssão
     * @author	Daniel Rodrigues
     * @return Int Retorna o IdGrupo
     */
    public function getIdGrupo() {
        return $this->RespostaPadraoNs->idGrupo;
    }

    /**
     * Adiciona na Session os Ids dos Grupos em que o usuario tiver acesso para fazer a validacao
     * @param	int $idGrupo Representando o código do Grupo 
     * @author	Daniel Rodrigues
     */
    public function setIdGrupo($idGrupo) {
        if (!in_array($idGrupo, $this->RespostaPadraoNs->idGrupo)) {
            $this->RespostaPadraoNs->idGrupo[] = $idGrupo;
        }
    }

    /**
     * Função que faz a validação dos IDs dos grupos
     * @author Daniel Rodrigues
     * @param int $idGrupo Id do Grupo, vindo por parâmetro GET da caixa
     * @return boolean Retorna True ou False, de acordo com a validação do parâmetro
     */
    public function validaGrupo($idGrupo) {

        if (!in_array($idGrupo, $this->RespostaPadraoNs->idGrupo)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Função usada no cadastro de Respostas Padrões do sistema
     * @param array $data Dados passados por parametro para o cadastro da resposta padrao
     * @author Daniel Rodrigues
     * @return Boolean Retorna True ou False para verificar se cadastrou ou não no banco de dados
     */
    public function addRespostaPadrao(array $data) {

        //MATRICULA DO USUÁRIO LOGADO
        $data['REPD_CD_MATRICULA_CADASTRO'] = $this->userNs->matricula;
        //DATA E HORA DO CADASTRO
        $data['REPD_DH_CADASTRO'] = $this->tb_dual->sysdate();
        //REMOVENDO SUBMIT
        unset($data['Salvar']);
        unset($data['REPD_ID_RESPOSTA_PADRAO']);

        //VERIFICAR SE A RESPOSTA PADRÃO É PRIVADA
        $data['REPD_IC_CONFIDENCIALIDADE'] = ($data['REPD_IC_CONFIDENCIALIDADE'] == '1' ? 'P' : 'G');

        //CHAMA A MAPPER PARA FAZER A PERSISTENCIA COM O BANCO
        $this->trf1_negocio->addRespostaPadrao($data);
    }

    /**
     * Função que busca as Respostas Padrões do sistema de acordo com o parâmetro de busca
     * @param Int $idGrupo ID do grupo
     * @return Array Araay da dados
     * @author Daniel Rodrigues
     */
    public function listRespostaPadrao($idGrupo) {

        //PEGANDO O IDGRUPO E A MATRICULA DA SESSAO
        $data = array($this->userNs->matricula, $idGrupo);

        //RETORNANDO VALORES
        return $this->trf1_negocio->listRespostaPadrao($data);
    }

    /**
     * Função que busca uma Resposta Padrão de acordo com o ID
     * @param Int $idResposta
     * @return Array Array de dados
     * @author Daniel Rodrigues
     */
    public function buscaRespostaPadrao($idResposta) {
        //utf8_decode
        $resposta = $this->trf1_negocio->buscaRespostaPadrao($idResposta);
        $resposta['REPD_DS_RESPOSTA_PADRAO'] = html_entity_decode(htmlspecialchars_decode($resposta['REPD_DS_RESPOSTA_PADRAO'], ENT_QUOTES), ENT_QUOTES, 'UTF-8');

        //VERIFICAR SE A RESPOSTA PADRÃO É PRIVADA
        $resposta['REPD_IC_CONFIDENCIALIDADE'] = ($resposta['REPD_IC_CONFIDENCIALIDADE'] == 'P' ? '1' : '0');
        return $resposta;
    }

    /**
     * Função de alteração de uma Resposta Padrão
     * @param Array $data Novos dados da Resposta Padrão
     * @author Daniel Rodrigues
     */
    public function editRespostaPadrao(array $data) {

        //DATA E HORA DA ALTERAÇÃO
        $data['REPD_DH_ALTERACAO'] = $this->tb_dual->sysdate();
        //VERIFICAR SE A RESPOSTA PADRÃO É PRIVADA
        $data['REPD_IC_CONFIDENCIALIDADE'] = ($data['REPD_IC_CONFIDENCIALIDADE'] == '1' ? 'P' : 'G');
        //CHAMA A MAPPER PARA FAZER A ALTERAÇÃO
        $this->trf1_negocio->editRespostaPadrao($data);
    }

    /**
     * Função de alteração de uma Resposta Padrão
     * @param Array $data Novos dados da Resposta Padrão
     * @author Daniel Rodrigues
     */
    public function deleteRespostaPadrao(array $data) {
        
        //DATA E HORA DA ALTERAÇÃO
        $data['REPD_DH_EXCLUSAO'] = $this->tb_dual->sysdate();
        //CHAMA A MAPPER PARA FAZER A EXCLUSÃO
        return $this->trf1_negocio->deleteRespostaPadrao($data);
    }

    /**
     * Função de pesquisa das Respostas Padrões na base de dados
     * @param Array $data Dados da pesquisa da Resposta Padrão
     * @author Daniel Rodrigues
     */
    public function pesquisaRespostaPadrao(array $data) {

        //TRATAMENTO DOS DADOS DA CONSULTA
        $data['REPD_CD_MATRICULA_CADASTRO'] = $this->userNs->matricula;
        //VERIFICA SE FORAM PASSADOS VÁRIOS GRUPOS
        $data['REPD_ID_GRUPO'] = Zend_Json::decode($data['REPD_ID_GRUPO']);
        $idGrupos = implode($data['REPD_ID_GRUPO'], ' , ');

        //CHAMA A MAPPER PARA FAZER A PESQUISA
        return $this->trf1_negocio->pesquisaRespostaPadrao($data, $idGrupos);
    }
    
    /**
     * Função de filtrar as Respostas Padrões na base de dados
     * @param Array $data Dados da pesquisa da Resposta Padrão
     * @author Daniel Rodrigues
     */
    public function filtroRespostaPadrao(array $data) {

        //TRATAMENTO DOS DADOS DA CONSULTA
        $data['REPD_CD_MATRICULA_CADASTRO'] = $this->userNs->matricula;
        if(is_array($data['REPD_ID_GRUPO'])){
            $idGrupos = implode($data['REPD_ID_GRUPO'], ' , ');
        }else{
           $idGrupos = $data['REPD_ID_GRUPO'];
        }
       
        //CHAMA A MAPPER PARA FAZER A PESQUISA
        return $this->trf1_negocio->pesquisaRespostaPadrao($data, $idGrupos);
    }

}

