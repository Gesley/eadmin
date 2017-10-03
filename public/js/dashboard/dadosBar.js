dadosBar = function(data){
    var 
        l = data.dados.length,
        series = {},
       // aux = [],
        i = 0,
        //chart = {},
        types = {
            'pizza'   : 'pie',
            'barras'  : 'bar',
            'colunas' : 'column',
            'tabela'  : 'grid'
        };
    // console.log('dadosPie');
    
    /*
     {
            name: 'Year 1800',
            data: [107, 31, 635, 203, 2]
        }, {
            name: 'Year 1900',
            data: [133, 156, 947, 408, 6]
        }, {
            name: 'Year 2008',
            data: [973, 914, 4054, 732, 34]
        }
    
    
    
    
    {
        "tipo":"barras",
        "titulo":" Barras",
        "dados":[
            { "label":"No prazo", "valor":100},
            { "label":"Falta 25%","valor":30}
        ],
        "legenda":"Total de chamados at\u00e9 o momento da constru\u00e7\u00e3o, mostrando os atendimentos conclu\u00eddos(baixados), os n\u00e3o atendidos(n\u00e3o baixados e n\u00e3o encaminhados) e os encaminhados",
        "link":"url",
        "cor":"cinza"
    }
    
     */
    /*
    for(; i < l; i++)
    {
        //aux = new Array(data.dados[i].label,data.dados[i].valor);
        //series.push(aux);
        
        series.name = data.dados[i].label;
        series.data = data.dados[i].valor;
    }
    */
    return {
        type: 'bar',
        //name: data.titulo,
        name: 'abcde',
        data: data.dados
    };
};