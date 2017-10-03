<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pctt
 *
 * @author TR17358PS
 */
class Arquivo_Model_DataMapper_Pctt {
    
    private $db; 
    
    public function __construct() {
        
        $this->db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
    }
    
    public function getCodClasse($id){
        $stm = $this->db->query("SELECT AQAP_CD_ASSUNTO_PRINCIPAL, AQAS_ID_ASSUNTO_SECUNDARIO 
                                FROM SAD_TB_AQAP_ASSUNTO_PRINCIPAL,
                                SAD_TB_AQAS_ASSUNTO_SECUNDARIO,
                                SAD_TB_AQCL_CLASSE
                                WHERE AQAS_ID_ASSUNTO_SECUNDARIO = AQCL_ID_AQAS  
                                AND AQAS_CD_ASSUNTO_PRINCIPAL = AQAP_CD_ASSUNTO_PRINCIPAL  
                                AND   AQCL_ID_AQAS = " .$id);
        
        return $stm->fetchAll();
    }
    public function getAssuntoPrincipal()
    {

        $stmt = $this->db->query("SELECT AQAP_CD_ASSUNTO_PRINCIPAL,
                                         AQAP_DS_ASSUNTO_PRINCIPAL
               			    FROM SAD_TB_AQAP_ASSUNTO_PRINCIPAL ORDER BY AQAP_CD_ASSUNTO_PRINCIPAL ASC ");
        return $stmt->fetchAll();
    }
    public function getAssuntoSecundario($id)
    {

        $stmt = $this->db->query("SELECT DISTINCT AQAS_CD_ASSUNTO_SECUNDARIO,
                                         AQAS_DS_ASSUNTO_SECUNDARIO
               			    FROM SAD_TB_AQAS_ASSUNTO_SECUNDARIO
                                   WHERE AQAS_CD_ASSUNTO_PRINCIPAL = $id
                                    ORDER BY AQAS_CD_ASSUNTO_SECUNDARIO ASC");
        return $stmt->fetchAll();
    }
    
      public function getSelectDescAtividade($id)
    {
        $stmt = $this->db->query("SELECT A.AQAT_DS_ATIVIDADE
                                  FROM SAD.SAD_TB_AQAT_ATIVIDADE A
                                  WHERE AQAT_ID_ATIVIDADE = $id");
        return $stmt->fetchAll();
    }
    /* Pegando o proximo registro do campo atividade de acordo com o id */
      public function getSelectQTAtividade($id)
    {
        $stmt = $this->db->query("SELECT MAX(AQVI_QT_VIA)+ 1  AS AQVI_QT_VIA 
                                           FROM SAD.SAD_TB_AQVI_VIA 
                                           WHERE AQVI_CD_VIA = '$id'");
        return $stmt->fetchAll();
    }
    /* //Pegando o proximo registro do campo atividade de acordo com o id */
    
    /*  Contando os codigo e incrementando mais um assunto secundario  */
    public function getCountSecundario($id)
    {

        $stmt = $this->db->query("SELECT MAX(AQAS_CD_ASSUNTO_SECUNDARIO) + 1 as total
               			    FROM SAD_TB_AQAS_ASSUNTO_SECUNDARIO
                                   WHERE AQAS_CD_ASSUNTO_PRINCIPAL = $id
                                    ORDER BY AQAS_CD_ASSUNTO_SECUNDARIO ASC");
        return $stmt->fetchAll();
    }
    /* ////////////////// fim do contador /////////////////////////////////// */
    
    /* ///Contando o campo cogido secundario ////////////////////////////////  */
    public function getCountCodigoSecundario($id)
    {

        $stmt = $this->db->query("SELECT COUNT(AQAS_CD_ASSUNTO_PRINCIPAL) AS RESULTADO
                                    FROM SAD_TB_AQAS_ASSUNTO_SECUNDARIO
                                    WHERE AQAS_CD_ASSUNTO_PRINCIPAL = $id
                                    ORDER BY AQAS_CD_ASSUNTO_SECUNDARIO ASC");
        return $stmt->fetchAll();
    }
    /* ///Contado o campo cogido secundario ////////////////////////////////  */
    
    /* ///Contando o campo cogido classse ////////////////////////////////  */
    public function getCountCodigoClasse($id)
    {

        $stmt = $this->db->query("SELECT COUNT(AQCL_CD_CLASSE) AS RESULTADO
                                    FROM SAD_TB_AQCL_CLASSE
                                    WHERE AQCL_ID_AQAS  = $id
                                    ORDER BY AQCL_CD_CLASSE ASC");
        return $stmt->fetchAll();
    }
    /* ///Contado o campo cogido secundario ////////////////////////////////  */
    /* ///Contando o campo cogido Subclassse ////////////////////////////////  */
    public function getCountCodigoSubClasse($id)
    {

        $stmt = $this->db->query("SELECT COUNT(AQSC_CD_SUBCLASSE) AS RESULTADO
                                    FROM SAD_TB_AQSC_SUBCLASSE
                                    WHERE AQSC_ID_AQCL  = $id
                                    ORDER BY AQSC_ID_SUBCLASSE ASC");
        return $stmt->fetchAll();
    }
    /* ///Contado o campo cogido SubClasse ////////////////////////////////  */
    public function getClasse($id){
        
        $stm = $this->db->query(
                "SELECT A.AQCL_ID_CLASSE, A.AQCL_ID_AQAS, A.AQCL_CD_CLASSE,
                 A.AQCL_DS_CLASSE, A.AQCL_DH_CRIACAO, A.AQCL_DH_FIM
                 FROM SAD.SAD_TB_AQCL_CLASSE A WHERE AQCL_ID_AQAS = $id"
                );
        return $stm->fetchAll();    
        
    }
    /* /////////////////////// Contando o campo codigo da classe //////////// */
    
       public function getCountClasse($id){
        
        $stm = $this->db->query(
                "SELECT MAX(A.AQCL_CD_CLASSE) + 1 as totalClasse
                 FROM SAD.SAD_TB_AQCL_CLASSE A 
                 WHERE AQCL_ID_AQAS = $id"
                );
        return $stm->fetchAll();    
        
    }
    /* /////////////////////// fim Contando o campo codigo da classe //////// */
    
    /*/////////////////////////Contando os registros temporalidade////////////*/
    
         public function getCountTemporalidade($id, $idAqat){
        
        $stm = $this->db->query(
                "SELECT COUNT(AQVP_CD_PCTT) AS VALOR
                    FROM   SAD_TB_AQVP_VIA_PCTT
                    WHERE  AQVP_CD_PCTT = '$id' AND AQVP_ID_AQAT = $idAqat"
                );
        return $stm->fetchAll();    
        
    }
    
    /*/////////////////////////Contando os registros temporalidade////////////*/
    public function getClasseCodigo($id){
        
        $stm = $this->db->query(
                "SELECT aqcl_cd_classe
                FROM   sad_tb_aqcl_classe 
                WHERE  aqcl_id_classe = $id"
                );
        return $stm->fetchAll();    
        
    }
    /* /////////Contando quantidade codigo sub classe o e inerindo mai um //////////////*/
    
     /*/////////////////////////Recuperando o CAMPO AQVP_CD_PCTT////////////*/
    public function getPCTTCodigo($id){
        
        $stm = $this->db->query(
                "SELECT  A.AQVP_CD_PCTT
                FROM    SAD.SAD_TB_AQVP_VIA_PCTT A
                WHERE   AQVP_ID_AQAT = $id"
                );
        return $stm->fetchAll();    
        
    }
    /* /////////Contando quantidade codigo sub classe o e inerindo mai um //////////////*/
    
      public function getCountSubclasseCodigo($id){
        
        $stm = $this->db->query(
                "SELECT MAX(AQSC_CD_SUBCLASSE) + 1 as totalSubClasse
                FROM   SAD_TB_AQSC_SUBCLASSE 
                WHERE  AQSC_ID_AQCL = $id"
                );
        return $stm->fetchAll();    
        
    }
    
    /* /////////Contando quantidade codigo sub classe o e inerindo mai um //////////////*/
      public function getSubclasseCodigo($id){
        
        $stm = $this->db->query(
                "SELECT AQSC_CD_SUBCLASSE
                FROM   SAD_TB_AQSC_SUBCLASSE 
                WHERE  AQSC_ID_SUBCLASSE = $id"
                );
        return $stm->fetchAll();    
        
    }
    
    public function getSubClass($id){
        $stm = $this->db->query(
                "SELECT A.AQSC_ID_SUBCLASSE,
                 A.AQSC_ID_AQCL, A.AQSC_CD_SUBCLASSE,
                A.AQSC_DS_SUBCLASSE, A.AQSC_DH_CRIACAO, A.AQSC_DH_FIM
                FROM SAD.SAD_TB_AQSC_SUBCLASSE A WHERE AQSC_ID_AQCL = " .$id
        );
         return $stm->fetchAll();
    }

        public function getPCTT() {
        
        $stmt = $this->db->query("SELECT B.AQVP_ID_PCTT, AQAT_DS_ATIVIDADE||' - '||B.AQVP_CD_PCTT DESCRICAO_PCTT
                                    FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                                   WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                                     AND AQVP_ID_AQVP_ATUAL IS NULL
                                     AND AQVP_DH_FIM IS NULL
                                ORDER BY DESCRICAO_PCTT");

        return $stmt->fetchAll();

    }
    
    /*//////////// contando o codigo de atividade e incrementando mais um ////*/
           public function getCountaTividadeVia($id){
        
        $stm = $this->db->query(
                "SELECT  MAX(AQAT_CD_ATIVIDADE) + 1
                 FROM SAD_TB_ATIVIDADE WHERE AQCL_ID_AQAS = $id"
                );
        return $stm->fetchAll();    
        
    }
    /*////////// fim contando o codigo de atividade e incrementando mais um ///*/
    
           public function getCountVia(){
        
        $stm = $this->db->query(
                    "SELECT MAX(AQVI_QT_VIA)+ 1 AS TOTALVIAS
                     FROM   SAD_TB_AQVI_VIA "
                );
        return $stm->fetchAll();    
        
    }
    
    /*// Contando os codigos da tabela ATAT_ATIVIDADE //*/
     public function getContaAtividade($id){
        
        $stm = $this->db->query(
                "SELECT MAX(AQAT_CD_ATIVIDADE) + 1 AS TOTALATIVIDADE
                 FROM   SAD_TB_AQAT_ATIVIDADE
                 WHERE  AQAT_ID_AQSC = " .$id
                );
        return $stm->fetchAll();    
        
    }
    /*// Contando os codigos da tabela ATAT_ATIVIDADE //*/
    
     /*// Contando os codigos da tabela ATAT_ATIVIDADE //*/
     public function getContaCodigoAtividade($id){
        
        $stm = $this->db->query(
                "SELECT COUNT(AQAT_CD_ATIVIDADE) AS RESULTADO
                 FROM   SAD_TB_AQAT_ATIVIDADE
                  WHERE AQAT_ID_AQSC" .$id
                );
        return $stm->fetchAll();    
        
    }
    /*// Contando os codigos da tabela ATAT_ATIVIDADE //*/
    
    // Selecionando os campo da tabela cadastro de vias
    public function getPCTTAjax($assunto) {
        
        $stmt = $this->db->query("SELECT B.AQVP_ID_PCTT, AQAT_DS_ATIVIDADE||' - '||B.AQVP_CD_PCTT AS LABEL
                                    FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                                   WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                                     AND AQVP_ID_AQVP_ATUAL IS NULL
                                     AND AQVP_DH_FIM IS NULL
                                     AND UPPER(AQAT_DS_ATIVIDADE||' - '||B.AQVP_CD_PCTT) LIKE UPPER('%$assunto%')");
        return $stmt->fetchAll();
    }

    public function getPCTTbyId($id) {

        $stmt = $this->db->query("SELECT B.AQVP_ID_PCTT, AQAT_DS_ATIVIDADE, B.AQVP_CD_PCTT
                                    FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                                   WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                                     AND AQVP_ID_PCTT = $id");
        return $stmt->fetch();
    }
    
    public function getPCTTbyCodigo($codigo) {

        $stmt = $this->db->query("SELECT B.AQVP_ID_PCTT
                                    FROM SAD_TB_AQAT_ATIVIDADE A,SAD_TB_AQVP_VIA_PCTT B
                                   WHERE AQAT_ID_ATIVIDADE=B.AQVP_ID_AQAT
                                     AND AQVP_CD_PCTT = '$codigo'");
        return $stmt->fetch();
    }
    
      public function getSecectAtividade($codigo) {

        $stmt = $this->db->query("SELECT count(aqvi_cd_via)as contar
                                    FROM sad.sad_tb_aqvi_via a
                                    where aqvi_cd_via = '$codigo'");
        return $stmt->fetch();
    }
    
    
    public function getViasAtividades($id){
        
        $stm = $this->db->query(
              "SELECT  D.AQDE_CD_DESTINO  AS codigo_destino
                ,D.AQDE_DS_DESTINO AS destino
                ,D2.AQDE_CD_DESTINO AS cogigo_final 
                ,D2.AQDE_DS_DESTINO as destino_final
                ,T.AQTE_CD_TEMPORALIDADE AS codigo_corrente
                ,T.AQTE_DS_TEMPORALIDADE AS corrente
                ,T.AQTE_CD_TEMPORALIDADE AS cogigo_intermediario
                ,T.AQTE_DS_TEMPORALIDADE AS intermediario
                ,V.AQVI_CD_VIA
                ,V.AQVI_QT_VIA
                ,P.AQVP_IC_MAIS_VIAS
                ,P.AQVP_DS_OBSERVACAO
                ,P.AQVP_ID_AQAT
                ,P.AQVP_DH_CRIACAO
                ,P.AQVP_DH_FIM
                ,P.AQVP_CD_PCTT

                FROM   SAD_TB_AQDE_DESTINO D
                      ,SAD_TB_AQDE_DESTINO D2
                      ,SAD_TB_AQTE_TEMPORALIDADE T
                      ,SAD_TB_AQVI_VIA V
                      ,SAD_TB_AQVP_VIA_PCTT P
                      ,SAD_TB_AQAT_ATIVIDADE A

                WHERE    P.AQVP_CD_AQDE_FIM   = D2.AQDE_CD_DESTINO 
                  AND    P.AQVP_CD_AQDE_INI   = D.AQDE_CD_DESTINO
                  AND    P.AQVP_CD_AQTE_COR   = T.AQTE_CD_TEMPORALIDADE(+)
                  AND    P.AQVP_CD_AQTE_INT   = T.AQTE_CD_TEMPORALIDADE(+)
                  AND    P.AQVP_CD_AQVI       = V.AQVI_CD_VIA(+)          
                  AND    P.AQVP_ID_AQAT       = A.AQAT_ID_ATIVIDADE       
                  AND    AQVP_ID_AQAT = " .$id  
                );
        
                return $stm->fetchAll();
    }
    
    
   public function getMaxIdAqas() {

        $stmt = $this->db->query("SELECT max(AQAS_ID_ASSUNTO_SECUNDARIO) + 1 AQAS_ID_ASSUNTO_SECUNDARIO
                                    FROM SAD_TB_AQAS_ASSUNTO_SECUNDARIO");
        return $stmt->fetch();
    }
    
    
    public function getMaxIdClasse(){
        $stmt = $this->db->query("SELECT max(AQCL_ID_CLASSE) + 1 AQCL_ID_CLASSE
                                    FROM SAD_TB_AQCL_CLASSE");
        return $stmt->fetch();
    }
    
     public function getMaxIdSubClasse(){
        $stmt = $this->db->query("SELECT max(AQSC_ID_SUBCLASSE) + 1 AQSC_ID_SUBCLASSE"
                . "   FROM SAD_TB_AQSC_SUBCLASSE");
        return $stmt->fetch();
    }
    
     public function getMaxIdAtividade(){
        $stmt = $this->db->query("SELECT max(AQAT_ID_ATIVIDADE) + 1 AQAT_ID_ATIVIDADE
                                               FROM SAD_TB_AQAT_ATIVIDADE");
        
        return $stmt->fetch();
    }
    public function getMaxIdAtividadesVias() {

        $stmt = $this->db->query("SELECT max(AQVP_ID_PCTT) + 1 AQVP_ID_PCTT
                                    FROM SAD_TB_AQVP_VIA_PCTT");
        return $stmt->fetch();
    }
    
    public function __destruct() {
        
        $this->db->closeConnection();
        
    }
    
    
    ////////////////////////////////Filtro de atividadess////////////////////////
    
          public function getBuscaAtividades($id){
        
        $stm = $this->db->query(
              "SELECT  D.AQDE_CD_DESTINO  AS codigo_destino
                ,D.AQDE_DS_DESTINO AS destino
                ,D2.AQDE_CD_DESTINO AS cogigo_final 
                ,D2.AQDE_DS_DESTINO as destino_final
                ,T.AQTE_CD_TEMPORALIDADE AS codigo_corrente
                ,T.AQTE_DS_TEMPORALIDADE AS corrente
                ,T.AQTE_CD_TEMPORALIDADE AS cogigo_intermediario
                ,T.AQTE_DS_TEMPORALIDADE AS intermediario
                ,V.AQVI_CD_VIA
                ,V.AQVI_QT_VIA
                ,P.AQVP_IC_MAIS_VIAS
                ,P.AQVP_DS_OBSERVACAO
                ,P.AQVP_ID_AQAT
                ,P.AQVP_DH_CRIACAO
                ,P.AQVP_DH_FIM
                ,P.AQVP_CD_PCTT

                FROM   SAD_TB_AQDE_DESTINO D
                      ,SAD_TB_AQDE_DESTINO D2
                      ,SAD_TB_AQTE_TEMPORALIDADE T
                      ,SAD_TB_AQVI_VIA V
                      ,SAD_TB_AQVP_VIA_PCTT P
                      ,SAD_TB_AQAT_ATIVIDADE A

                WHERE    P.AQVP_CD_AQDE_FIM   = D2.AQDE_CD_DESTINO 
                  AND    P.AQVP_CD_AQDE_INI   = D.AQDE_CD_DESTINO
                  AND    P.AQVP_CD_AQTE_COR   = T.AQTE_CD_TEMPORALIDADE(+)
                  AND    P.AQVP_CD_AQTE_INT   = T.AQTE_CD_TEMPORALIDADE(+)
                  AND    P.AQVP_CD_AQVI       = V.AQVI_CD_VIA(+)          
                  AND    P.AQVP_ID_AQAT       = A.AQAT_ID_ATIVIDADE 
                  AND    ROWNUM               <=20
                  AND   (UPPER(D.AQDE_DS_DESTINO) LIKE UPPER('%$id%')
                  OR    UPPER(D2.AQDE_DS_DESTINO) LIKE UPPER('%$id%')
                  OR    UPPER(T.AQTE_DS_TEMPORALIDADE) LIKE UPPER('%$id%')
                  OR    UPPER(P.AQVP_DS_OBSERVACAO) LIKE UPPER('%$id%')
                  OR    UPPER(A.AQAT_DS_ATIVIDADE) LIKE UPPER('%$id%'))
                  ORDER BY AQVP_CD_PCTT ASC"  
                );
        
                return $stm->fetchAll();
    }
    
}

