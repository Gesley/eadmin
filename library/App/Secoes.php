<?
class App_Secoes
{
 
    public static $campos = array(
        'uf' => '', 'codigo' => '',
        'sigla' => '', 'alias' => ''
    );

    public static $sjs = array(
        array(
            'uf' => 'AM',
            'codigo' => '3200',
            'sigla' => 'SJAM',
            'alias' => 'JFAM',
            'telefone' => '(92) 3612-3300',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Amazonas',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.19.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfam.trf1.gov.br)(INSTANCE_NAME = jfam)))'
        ),
        array(
            'uf' => 'TB',
            'codigo' => '3201',
            'sigla' => 'TBT',
            'alias' => 'TBT',
            'telefone' => '(92) 3612-3300',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Tabatinga',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.19.32.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = tbt.trf1.gov.br)(INSTANCE_NAME = tbt)))'
        ),
        array(
            'uf' => 'TFE',
            'codigo' => '3202',
            'sigla' => 'TFE',
            'alias' => 'JFAM',
            'telefone' => '(97) 3343-2750',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Tef&eacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.19.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfam.trf1.gov.br)(INSTANCE_NAME = jfam)))'
        ),
        array(
            'uf' => 'AC',
            'codigo' => '3000',
            'sigla' => 'SJAC',
            'alias' => 'JFAC',
            'telefone' => '(68) 3214-2000',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Acre',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.17.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfac.trf1.gov.br)(INSTANCE_NAME = jfac)))'
        ),
        array(
            'uf' => 'AP',
            'codigo' => '3100',
            'sigla' => 'SJAP',
            'alias' => 'JFAP',
            'telefone' => '(96) 3214-1518 / 3214-1512',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Amap&aacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.18.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfap.trf1.gov.br)(INSTANCE_NAME = jfap)))'
        ),
        array(
            'uf' => 'LJI',
            'codigo' => '3101',
            'sigla' => 'LJI',
            'alias' => 'LJI',
            'telefone' => '',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria de Laranjal do Jari',
            'tns' => '(DESCRIPTION = (SDU = 1460) (TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) ) (CONNECT_DATA = (SERVICE_NAME = jfap2.trf1.gov.br) (INSTANCE_NAME = jfap2) ))'
        ),
        array(
            'uf' => 'OPQ',
            'codigo' => '3102',
            'sigla' => 'OPQ',
            'alias' => 'JFAP2',
            'telefone' => '',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Oiapoque',
            'tns' => '(DESCRIPTION = (SDU = 1460) (TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) ) (CONNECT_DATA = (SERVICE_NAME = jfap2.trf1.gov.br) (INSTANCE_NAME = jfap2) ))'
        ),
        array(
            'uf' => 'BA',
            'codigo' => '3300',
            'sigla' => 'SJBA',
            'alias' => 'JFBA',
            'telefone' => '(71) 3617-2600',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria da Bahia',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.20.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfba.trf1.gov.br)(INSTANCE_NAME = jfba)))'
        ),
        array(
            'uf' => 'ILS',
            'codigo' => '3301',
            'sigla' => 'ILS',
            'alias' => 'ILS',
            'telefone' => '(73) 6347225 / 6347225',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Ilh&eacute;s',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br)(INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'CFS',
            'codigo' => '3302',
            'sigla' => 'CFS',
            'alias' => 'CFS',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Campo Formoso',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br)(INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'BES',
            'codigo' => '3303',
            'sigla' => 'BES',
            'alias' => 'BES',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Barreiras',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br)(INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'FSA',
            'codigo' => '3304',
            'sigla' => 'FSA',
            'alias' => 'FSA',
            'telefone' => '(75) 3221-6274',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Feira de Santana',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br)(INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'JZR',
            'codigo' => '3305',
            'sigla' => 'JZR',
            'alias' => 'JZR',
            'telefone' => '(74) 3613-7402, (74) 3611-3961',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Juazeiro',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br)(INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'BA',
            'codigo' => '3306',
            'sigla' => 'PAF',
            'alias' => 'PAF',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Afonso',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br)(INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'VCA',
            'codigo' => '3307',
            'sigla' => 'VCA',
            'alias' => 'VCA',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Vit&oacute;ria da Conquista',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br) (INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'JEE',
            'codigo' => '3308',
            'sigla' => 'JEE',
            'alias' => '',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Jequi&eacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br) (INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'GNB',
            'codigo' => '3309',
            'sigla' => 'GNB',
            'alias' => '',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Guanambi',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br) (INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'EUS',
            'codigo' => '3310',
            'sigla' => 'EUS',
            'alias' => 'EUS',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Eun&aacute;polis',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br) (INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'ITB',
            'codigo' => '3311',
            'sigla' => 'ITB',
            'alias' => 'ITB',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Itabuna',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br) (INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'IEE',
            'codigo' => '3312',
            'sigla' => 'IEE',
            'alias' => 'IEE',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Irec&ecirc;',
            'host' => '172.16.3.217',
            'servico' => 'ils.trf1.gov.br',
            'banco' => 'ils',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br) (INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'TAF',
            'codigo' => '3313',
            'sigla' => 'TAF',
            'alias' => 'TAF',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Teixeira de Freitas.',
            'host' => '172.16.3.217',
            'servico' => 'ils.trf1.gov.br',
            'banco' => 'ils',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br) (INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'ALH',
            'codigo' => '3314',
            'sigla' => 'ALH',
            'alias' => 'ALH',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Alagoinhas.',
            'host' => '172.16.3.217',
            'servico' => 'ils.trf1.gov.br',
            'banco' => 'ils',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ils.trf1.gov.br) (INSTANCE_NAME = ils)))'
        ),
        array(
            'uf' => 'DF',
            'codigo' => '3400',
            'sigla' => 'SJDF',
            'alias' => 'JFDF',
            'telefone' => '(61) 3221-6000',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Distrito Federal',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.21.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfdf.trf1.gov.br)(INSTANCE_NAME = jfdf)))'
        ),
        array(
            'uf' => 'GO',
            'codigo' => '3500',
            'sigla' => 'SJGO',
            'alias' => 'JFGO',
            'telefone' => '(62) 3226-1500',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Goi&aacute;s',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfgo.trf1.gov.br)(INSTANCE_NAME = jfgo)))'
        ),
        array(
            'uf' => 'LZA',
            'codigo' => '3501',
            'sigla' => 'LZA',
            'alias' => 'LZA',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Luzi&acirc;nia',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFGO)))'
        ),
        array(
            'uf' => 'ANS',
            'codigo' => '3502',
            'sigla' => 'ANS',
            'alias' => 'ANS',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de An&aacute;polis',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFGO)))'
        ),
        array(
            'uf' => 'RVD',
            'codigo' => '3503',
            'sigla' => 'RVD',
            'alias' => 'RVD',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Rio Verde',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFGO)))'
        ),
        array(
            'uf' => 'ACG',
            'codigo' => '3504',
            'sigla' => 'ACG',
            'alias' => 'ACG',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Aparecida de Goi&acirc;nia',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFGO)))'
        ),
        array(
            'uf' => 'URC',
            'codigo' => '3505',
            'sigla' => 'URC',
            'alias' => 'URC',
            'telefone' => '(62) 2261680',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Urua&ccedil;&uacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFGO)))'
        ),
        array(
            'uf' => 'FRM',
            'codigo' => '3506',
            'sigla' => 'FRM',
            'alias' => 'FRM',
            'telefone' => '(62) 2261680',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Formosa',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFGO)))'
        ),
        array(
            'uf' => 'JTI',
            'codigo' => '3507',
            'sigla' => 'JTI',
            'alias' => 'JTI',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Jata&iacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFGO)))'
        ),
        array(
            'uf' => 'IUB',
            'codigo' => '3508',
            'sigla' => 'IUB',
            'alias' => 'IUB',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Itumbiara',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.22.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFGO)))'
        ),
        array(
            'uf' => 'MA',
            'codigo' => '3700',
            'sigla' => 'SJMA',
            'alias' => 'JFMA',
            'telefone' => '(98) 3214-5701',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Maranh&atilde;o',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.23.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfma.trf1.gov.br)(INSTANCE_NAME = jfma)))'
        ),
        array(
            'uf' => 'ITZ',
            'codigo' => '3701',
            'sigla' => 'ITZ',
            'alias' => 'ITZ',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Imperatriz',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.23.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = itz.trf1.gov.br)(INSTANCE_NAME = itz)))'
        ),
        array(
            'uf' => 'CXS',
            'codigo' => '3702',
            'sigla' => 'CXS',
            'alias' => 'CXS',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Caxias',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.23.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfma.trf1.gov.br)(INSTANCE_NAME = jfma)))'
        ),
        array(
            'uf' => 'BBL',
            'codigo' => '3703',
            'sigla' => 'BBL',
            'alias' => 'BBL',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Bacabal',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.23.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfma.trf1.gov.br)(INSTANCE_NAME = jfma)))'
        ),
        array(
            'uf' => 'MT',
            'codigo' => '3600',
            'sigla' => 'SJMT',
            'alias' => 'JFMT',
            'telefone' => '(65) 3614-5700 / 3614-5800',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Mato Grosso',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfmt.trf1.gov.br)(INSTANCE_NAME = jfmt)))'
        ),
        array(
            'uf' => 'CCS',
            'codigo' => '3601',
            'sigla' => 'CCS',
            'alias' => 'CCS',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de C&aacute;ceres ',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfmt.trf1.gov.br)(INSTANCE_NAME = jfmt)))'
        ),
        array(
            'uf' => 'ROI',
            'codigo' => '3602',
            'sigla' => 'ROI',
            'alias' => 'ROI',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Rondon&oacute;polis',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFMT)))'
        ),
        array(
            'uf' => 'SNO',
            'codigo' => '3603',
            'sigla' => 'SNO',
            'alias' => 'SNO',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Sinop',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFMT)))'
        ),
        array(
            'uf' => 'DIO',
            'codigo' => '3604',
            'sigla' => 'DIO',
            'alias' => 'DIO',
            'telefone' => '(65) 6145779',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Diamantino',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFMT)))'
        ),
        array(
            'uf' => 'BAG',
            'codigo' => '3605',
            'sigla' => 'BAG',
            'alias' => 'BAG',
            'telefone' => '(66) 3402-0003',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Barra do Gar&ccedil;as',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.24.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFMT)))'
        ),
        array(
            'uf' => 'MG',
            'codigo' => '3800',
            'sigla' => 'SJMG',
            'alias' => 'JFMG',
            'telefone' => '(31) 2129-6300',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria de Minas Gerais',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.25.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfmg.trf1.gov.br)      (INSTANCE_NAME = jfmg)))'
        ),
        array(
            'uf' => 'JFO',
            'codigo' => '3801',
            'sigla' => 'JFO',
            'alias' => 'JFO',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Juiz de Fora',
            //'tns' => 'jfo'
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.25.96.103)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfo.trf1.gov.br)(INSTANCE_NAME = jfo)))'
        ),
        array(
            'uf' => 'UBE',
            'codigo' => '3802',
            'sigla' => 'UBE',
            'alias' => 'UBE',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Uberaba',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'UDI',
            'codigo' => '3803',
            'sigla' => 'UDI',
            'alias' => 'UDI',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Uberl&acirc;ndia',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.25.64.57)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = udi.trf1.gov.br)(INSTANCE_NAME = udi)))'
        ),
        array(
            'uf' => 'PSS',
            'codigo' => '3804',
            'sigla' => 'PSS',
            'alias' => 'PSS',
            'telefone' => '(35) 3522-7427',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Passos',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)))'
        ),
        array(
            'uf' => 'VCS',
            'codigo' => '3823',
            'sigla' => 'VCS',
            'alias' => 'VCS',
            'telefone' => '(35) 3522-7427',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Vi&ccedil;osa',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)))'
        ),
        array(
            'uf' => 'SSP',
            'codigo' => '3805',
            'sigla' => 'SSP',
            'alias' => 'SSP',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de S?o Sebasti&atilde;o do Para&iacute;so',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'PMS',
            'codigo' => '3806',
            'sigla' => 'PMS',
            'alias' => 'PMS',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Patos de Minas',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'MCL',
            'codigo' => '3807',
            'sigla' => 'MCL',
            'alias' => 'MCL',
            'telefone' => '(38) 21018200',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Montes Claros',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)))'
        ),
        array(
            'uf' => 'LAV',
            'codigo' => '3808',
            'sigla' => 'LAV',
            'alias' => 'LAV',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Lavras',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'VGA',
            'codigo' => '3809',
            'sigla' => 'VGA',
            'alias' => 'VGA',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Varginha',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'PSA',
            'codigo' => '3810',
            'sigla' => 'PSA',
            'alias' => 'PSA',
            'telefone' => '(35) 3421-9506',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Pouso Alegre',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'DVL',
            'codigo' => '3811',
            'sigla' => 'DVL',
            'alias' => 'DVL',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Divin&oacute;polis',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)) )'
        ),
        array(
            'uf' => 'SLA',
            'codigo' => '3812',
            'sigla' => 'SLA',
            'alias' => 'SLA',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Sete Lagoas',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)) )'
        ),
        array(
            'uf' => 'GVS',
            'codigo' => '3813',
            'sigla' => 'GVS',
            'alias' => 'GVS',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Governador Valadares',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)) )'
        ),
        array(
            'uf' => 'IIG',
            'codigo' => '3814',
            'sigla' => 'IIG',
            'alias' => 'IIG',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Ipatinga',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)) )'
        ),
        array(
            'uf' => 'SOE',
            'codigo' => '3815',
            'sigla' => 'SOE',
            'alias' => 'SOE',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de S?o Jo&atilde;o Del Rei',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)) )'
        ),
        array(
            'uf' => 'TOT',
            'codigo' => '3816',
            'sigla' => 'TOT',
            'alias' => 'TOT',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Te&oacute;filo Otoni',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)) )'
        ),
        array(
            'uf' => 'PTU',
            'codigo' => '3817',
            'sigla' => 'PTU',
            'alias' => 'PTU',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Paracatu',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'UNI',
            'codigo' => '3818',
            'sigla' => 'UNI',
            'alias' => 'UNI',
            'telefone' => '(31) 32996300',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Una&iacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)) )'
        ),
        array(
            'uf' => 'MNC',
            'codigo' => '3819',
            'sigla' => 'MNC',
            'alias' => 'MNC',
            'telefone' => '(33) 3332-1506',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Manhua&ccedil;u',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'CEM',
            'codigo' => '3820',
            'sigla' => 'CEM',
            'alias' => 'CEM',
            'telefone' => '(31) 3268-6302',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Contagem',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)))(CONNECT_DATA = (SERVICE_NAME = ube.trf1.gov.br)(INSTANCE_NAME = ube)))'
        ),
        array(
            'uf' => 'MRE',
            'codigo' => '3821',
            'sigla' => 'MRE',
            'alias' => 'MRE',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Muria&eacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)) )'
        ),
        array(
            'uf' => 'PNV',
            'codigo' => '3822',
            'sigla' => 'PNV',
            'alias' => 'PNV',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Ponte Nova;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460) (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.217)(PORT = 1526)) ) (CONNECT_DATA = (SERVICE_NAME = pss.trf1.gov.br)(INSTANCE_NAME = pss)) )'
        ),
        array(
            'uf' => 'PA',
            'codigo' => '3900',
            'sigla' => 'SJPA',
            'alias' => 'JFPA',
            'telefone' => '(91) 3299-6159 / 3299-6213',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria do Par&aacute;',
            'tns' => '(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfpa.trf1.gov.br)(INSTANCE_NAME = jfpa))(SDU = 1460))'
        ),
        array(
            'uf' => 'TUU',
            'codigo' => '3907',
            'sigla' => 'TUU',
            'alias' => 'JFPA',
            'telefone' => '(94) 3787-6004 / 3787-9088',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Tucuru&iacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1526)))(CONNECT_DATA =(SERVICE_NAME = mba.trf1.gov.br)(INSTANCE_NAME = mba)))'
        ),
        array(
            'uf' => 'MB',
            'codigo' => '3901',
            'sigla' => 'MBA',
            'alias' => 'MBA',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Marab&aacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = mba.trf1.gov.br)(INSTANCE_NAME = mba)))'
        ),
        array(
            'uf' => 'STM',
            'codigo' => '3902',
            'sigla' => 'STM',
            'alias' => 'STM',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Santar&eacute;m',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.32.3)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.32.3)(PORT = 1526)))(CONNECT_DATA =(SERVICE_NAME = stm.trf1.gov.br)(INSTANCE_NAME = stm)))'
        ),
        array(
            'uf' => 'ATM',
            'codigo' => '3903',
            'sigla' => 'ATM',
            'alias' => 'ATM',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Altamira',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1526)))(CONNECT_DATA =(SERVICE_NAME = mba.trf1.gov.br)(INSTANCE_NAME = mba)))'
        ),
        array(
            'uf' => 'CAH',
            'codigo' => '3904',
            'sigla' => 'CAH',
            'alias' => 'CAH',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Castanhal',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1526)))(CONNECT_DATA =(SERVICE_NAME = mba.trf1.gov.br)(INSTANCE_NAME = mba)))'
        ),
        array(
            'uf' => 'RDO',
            'codigo' => '3905',
            'sigla' => 'RDO',
            'alias' => 'RDO',
            'telefone' => '(94) 3424-1105',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Reden&ccedil;&atilde;o',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1521))(ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1526)))(CONNECT_DATA =(SERVICE_NAME = jfpa.trf1.gov.br)(INSTANCE_NAME = jfpa)))'
        ),
        array(
            'uf' => 'PGN',
            'codigo' => '3906',
            'sigla' => 'PGN',
            'alias' => 'PGN',
            'telefone' => '(94) 3424-1105',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Paragominas',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.26.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = mba.trf1.gov.br)(INSTANCE_NAME = mba)))'
        ),
        array(
            'uf' => 'PI',
            'codigo' => '4000',
            'sigla' => 'SJPI',
            'alias' => 'JFPI',
            'telefone' => '(86) 2107-2800',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria do Piau&iacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.27.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfpi.trf1.gov.br)(INSTANCE_NAME = jfpi)))'
        ),
        array(
            'uf' => 'PCZ',
            'codigo' => '4001',
            'sigla' => 'PCZ',
            'alias' => 'PCZ',
            'telefone' => '(89) 3422-2656',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Picos',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.27.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfpi.trf1.gov.br)(INSTANCE_NAME = jfpi)))'
        ),
        array(
            'uf' => 'PNA',
            'codigo' => '4002',
            'sigla' => 'PNA',
            'alias' => 'PNA',
            'telefone' => '(86) 3322-4091 ',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Parna&iacute;ba',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.27.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfpi.trf1.gov.br)(INSTANCE_NAME = jfpi)))'
        ),
        array(
            'uf' => 'FLO',
            'codigo' => '4003',
            'sigla' => 'FLO',
            'alias' => 'FLO',
            'telefone' => '(86) 3322-4091 ',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Floriano',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.27.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfpi.trf1.gov.br)(INSTANCE_NAME = jfpi)))'
        ),
        array(
            'uf' => 'RO',
            'codigo' => '4100',
            'sigla' => 'SJRO',
            'alias' => 'JFRO',
            'telefone' => '',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria de Rond&ocirc;nia',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.28.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfro.trf1.gov.br)(INSTANCE_NAME = jfro)))'
        ),
        array(
            'uf' => 'JIP',
            'codigo' => '4101',
            'sigla' => 'JIP',
            'alias' => 'JIP',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Jiparan&aacute;',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.28.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.28.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFRO)))'
        ),
        array(
            'uf' => 'GUM',
            'codigo' => '4102',
            'sigla' => 'GUM',
            'alias' => 'GUM',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Guajar&aacute;-Mirim',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.28.3.3)(PORT = 1521)) (ADDRESS = (PROTOCOL = TCP)(HOST = 172.28.3.3)(PORT = 1526))) (CONNECT_DATA = (SID = JFRO)))'
        ),
        array(
            'uf' => 'RR',
            'codigo' => '4200',
            'sigla' => 'SJRR',
            'alias' => 'JFRR',
            'telefone' => '(95) 2121-4200',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria de Roraima',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.29.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfrr.trf1.gov.br)(INSTANCE_NAME = jfrr)))'
        ),
        array(
            'uf' => 'TO',
            'codigo' => '4300',
            'sigla' => 'SJTO',
            'alias' => 'JFTO',
            'telefone' => '(63) 3218-3809',
            'nome' => 'Se&ccedil;&atilde;o Judici&aacute;ria de Tocantins',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.30.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfto.trf1.gov.br)(INSTANCE_NAME = jfto)))'
        ),
        array(
            'uf' => 'ARN',
            'codigo' => '4301',
            'sigla' => 'ARN',
            'alias' => 'ARN',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Aragua&iacute;na',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.30.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfto.trf1.gov.br)(INSTANCE_NAME = jfto)))'
        ),
        array(
            'uf' => 'GUR',
            'codigo' => '4302',
            'sigla' => 'GUR',
            'alias' => 'GUR',
            'telefone' => '',
            'nome' => 'Subse&ccedil;&atilde;o Judici&aacute;ria de Gurupi',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.30.3.3)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfto.trf1.gov.br)(INSTANCE_NAME = jfto)))'
        ),
        array(
            'uf' => 'TRF',
            'codigo' => '100',
            'sigla' => 'TRF1',
            'alias' => 'TRF1',
            'telefone' => '(61) 3314-5225',
            'nome' => 'Tribunal Regional Federal da 1&ordf; Regi&atilde;o',
            'tns' => '(DESCRIPTION = (ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.3)(PORT = 1521)))(CONNECT_DATA =(SERVICE_NAME = trf1.trf1.gov.br)(INSTANCE_NAME = trf1))(SDU = 1460))'
        ),
        array(
            'uf' => 'TRF1DSV',
            'codigo' => '',
            'sigla' => 'TRF1DSV',
            'alias' => 'TRF1DSV',
            'telefone' => '',
            'nome' => '',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.216)(PORT = 1521)) )(CONNECT_DATA = (SERVICE_NAME = trf1dsv.trf1.gov.br)(INSTANCE_NAME = trf1dsv)))'
        ),
        array(
            'uf' => 'JFDSV1',
            'codigo' => '',
            'sigla' => 'JFDSV1',
            'alias' => 'JFDSV1',
            'telefone' => '',
            'nome' => '',
            'tns' => 'JFDSV1'
        ),
        array(
            'uf' => 'JFDSV',
            'codigo' => '',
            'sigla' => 'JFDSV',
            'alias' => 'JFDSV',
            'telefone' => '',
            'nome' => '',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.216)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfdsv.trf1.gov.br)(INSTANCE_NAME = jfdsv)))'
        ),
        array(
            'uf' => 'JFHML',
            'codigo' => '',
            'sigla' => 'JFHML',
            'alias' => 'JFHML',
            'telefone' => '',
            'nome' => '',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.216)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = jfhml.trf1.gov.br)(INSTANCE_NAME = jfhml)))'
        ),
        array(
            'uf' => '',
            'codigo' => '',
            'sigla' => 'JFDSV3',
            'alias' => 'JFDSV3',
            'telefone' => '',
            'nome' => '',
            'tns' => 'JFDSV3'
        ),
        array(
            'uf' => 'CJF',
            'codigo' => 'CJF',
            'sigla' => 'CJF',
            'telefone' => '',
            'alias' => 'CJF',
            'nome' => 'Conselho da Justi�a Federal',
            'tns' => '(DESCRIPTION = (SDU = 1460)(TDU = 1460)(ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.3.129)(PORT = 1521)) (CONNECT_DATA =  (SERVER = DEDICATED) (SERVICE_NAME = CJFTRF1) ) )'
        )

    );

    function GetAll()
    {
        $campos = array_filter($this->campos);
        //var_dump($campos);
        foreach (self::$sjs as $sj)
        {
            //foreach ($sj as $key=>$valor) {
            if (($sj['codigo'] != '' && $this->campos['codigo'] != '' && $this->campos['codigo'] == $sj['codigo']) || ($sj['uf'] != '' && $this->campos['uf'] != '' && $this->campos['uf'] == $sj['uf']) || ($sj['sigla'] != '' && $this->campos['sigla'] != '' && $this->campos['sigla'] == $sj['sigla']) || ($sj['alias'] != '' && $this->campos['alias'] != '' && $this->campos['alias'] == $sj['alias'])) {
                //print 'valor encontrado';
                return $sj;
            }
            if (in_array($campos, $this->sjs)) {
                //print 'valor encontrado';
                return $sj;
            }
            //}
        }
        return false;
    }
 
    public static function getOne($arg)
    {
        if (is_string($arg)) {
            if (strlen($arg) == 2) {
                // get por uf
                return self::getPorUf($arg);
            } else {
                // get por alias
                return self::getPorAlias($arg);
            }
        } elseif (is_int($arg)) {
            //get por codigo
            return self::getPorCodigo($arg);
        }
        throw new SecoesException('Seção não encontrada [' . $arg . ']');
    }
 
    public static function isSecao($arg)
    {
        if ((strlen($arg) == 0) || empty($arg)) {
            throw new SecoesException('Seção não encontrada [' . $arg . ']');
        } elseif (is_string($arg)) {
            if (strlen($arg) == 2) {
                // get por uf
                return self::getPorUf($arg);
            } else {
                // get por alias
                return self::getPorSigla($arg);
            }
        } elseif (is_int($arg)) {
            //get por codigo
            return self::getPorCodigo($arg);
        }
        throw new SecoesException('Seção não encontrada[' . $arg . ']');
    }
 
    public static function getPorSigla($sigla)
    {
        foreach (self::$sjs as $sj)
        {
            if ($sigla == $sj['sigla']) {
                $o = new App_Secao();
                $o->uf = $sj['uf'];
                $o->codigo = $sj['codigo'];
                $o->sigla = $sj['sigla'];
                $o->alias = $sj['alias'];
                $o->nome = $sj['nome'];
                $o->tns = $sj['tns'];
                return $o;
            }
        }
        throw new SecoesException('Seção não encontrada [' . $sigla . ']');
        //return false;
    }
 
    public static function getPorUf($uf)
    {
        if (strlen($uf) > 2) {
            return self::getPorSigla($uf);
        } else {
            foreach (self::$sjs as $sj)
            {
                if ($uf == $sj['uf'] && $sj['uf'] != '') {
                    $o = new App_Secao();
                    $o->uf = $sj['uf'];
                    $o->codigo = $sj['codigo'];
                    $o->sigla = $sj['sigla'];
                    $o->alias = $sj['alias'];
                    $o->nome = $sj['nome'];
                    $o->tns = $sj['tns'];
                    return $o;
                }
            }
        }
 
        throw new SecoesException('Seção não encontrada [' . $uf . ']');
    }
 
    public static function getPorCodigo($codigo)
    {
        foreach (self::$sjs as $sj)
        {
            if ($codigo == $sj['codigo']) {
                $o = new App_Secao();
                $o->uf = $sj['uf'];
                $o->codigo = $sj['codigo'];
                $o->sigla = $sj['sigla'];
                $o->alias = $sj['alias'];
                $o->nome = $sj['nome'];
                $o->tns = $sj['tns'];
                return $o;
            }
        }
        throw new SecoesException('Seção não encontrada [' . $codigo . ']');
    }
 
    public static function getPorAlias($alias)
    {
        foreach (self::$sjs as $sj)
        {
            if ($alias == $sj['alias']) {
                $o = new App_Secao();
                $o->uf = $sj['uf'];
                $o->codigo = $sj['codigo'];
                $o->sigla = $sj['sigla'];
                $o->alias = $sj['alias'];
                $o->nome = $sj['nome'];
                $o->tns = $sj['tns'];
                return $o;
            }
        }
        throw new SecoesException('Seção não encontrada [' . $alias . ']');
    }
 
    function Get($var)
    {
        $campos = array_filter($this->campos);
        //var_dump(array_values($campos));
        foreach (self::$sjs as $sj)
        {
            //foreach ($sj as $key=>$valor) {
            if (($sj['codigo'] != '' && $this->campos['codigo'] != '' && $this->campos['codigo'] == $sj['codigo']) || ($sj['uf'] != '' && $this->campos['uf'] != '' && $this->campos['uf'] == $sj['uf']) || ($sj['sigla'] != '' && $this->campos['sigla'] != '' && $this->campos['sigla'] == $sj['sigla']) || ($sj['alias'] != '' && $this->campos['alias'] != '' && $this->campos['alias'] == $sj['alias'])) {
                return $sj[$var];
            }
        }
        return false;
    }
 
    function Set($var, $valor)
    {
        if (array_key_exists($var, $this->campos)) {
            $this->campos[$var] = $valor;
        }
    }
 
    public static function temJEF($secao)
    {
        $arrayJEF = array(
            "BA", "DF",
            "AC", "AP", "AM",
            "GO", "MT", "MA",
            "MG", "PA", "PI",
            "RO", "RR", "TO",
            "JFBA", "JFDF",
            "JFAC", "JFAP",
            "JFAM", "JFGO",
            "JFMT", "JFMA",
            "JFMG", "JFPA",
            "JFPI", "JFRO",
            "JFRR", "JFTO",
            "3000", "3100",
            "3200", "3300",
            "3400", "3500",
            "3600", "3700",
            "3800", "3900",
            "4000", "4100",
            "4200", "4300"
        );
        if (in_array($secao, $arrayJEF)) {
            return true;
        } else {
            return false;
        }
    }
 
}