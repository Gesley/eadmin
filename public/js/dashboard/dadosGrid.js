dadosGrid = function(data){
    //console.log(data);
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

    return {
        type: 'grid',
        name: 'abc',
        data: data
    };
};