dadosPie = function(data){
    var 
        l = data.dados.length,
        series = [],
        aux = [],
        i = 0,
        //chart = {},
        types = {
            'pizza'   : 'pie',
            'barras'  : 'bar',
            'colunas' : 'column',
            'tabela'  : 'grid'
        };
    // console.log('dadosPie');
    for(; i < l; i++)
    {
        aux = new Array(data.dados[i].label,data.dados[i].valor);
        series.push(aux);
    }

    return {
        type: 'pie',
        //name: data.titulo,
        name: 'abc',
        data: series
    };
};