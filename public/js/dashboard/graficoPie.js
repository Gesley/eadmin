graficoPie = function (id, data)
{
    var 
        dadosChart = dadosPie(data),
        grafico;
            
    grafico = new Highcharts.Chart({
        chart: {
            renderTo: id,
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: true
        },
            
        title    : {
            text: null
        },
        subtitle : {
            text: null
        },
        credits  : {
            enabled: false
        },
            
        tooltip: {
            formatter: function() {
                //return '<b>'+ this.point.name +'</b>: '+ this.point.y;
                return this.point.name + ': ' + this.point.y;
            }
        },
           
        legend: {
            enabled:true,
            layout: 'vertical',
            backgroundColor: '#FFFFFF',
            align: 'center',
            verticalAlign: 'top',
            x: 100,
            y: 70
        },

        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        //return '<b>'+ this.point.name +'</b>: '+ this.point.y;
                        return this.point.name + ': ' + this.point.y;
                    }
                }
            }
        },

        series: [{
            type: dadosChart.type,
            name: dadosChart.name,
            data: dadosChart.data
        }]
    });
};
 