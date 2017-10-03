$( document ).delegate( ".plus-vinc", "click", function(e) {
    e.preventDefault();
    var id = $(this).attr('class').replace('plus-vinc ', '');
    var tr = $(".vinculados." + id);
    tr.find('td').slideToggle(300);

    var urlPlus = base_url + '/img/a-plus-icon.png';
    var urlMinus = base_url + '/img/a-minus-icon.png';

    if ($(this).find('img').attr('src') == urlPlus) {
        $(this).find('img').attr('src', urlMinus);
        var url = base_url + '/sosti/detalhesolicitacao/vinculados/id/' + id;
        $.ajax({
            url: url,
            dataType: 'html',
            type: 'POST',
            data: $(this),
            processData: false,
            success: function (data) {
                $('#row_'+id).after(data);
            }
        });
    } else {
        $(this).find('img').attr('src', urlPlus);
        $('.v_'+id).remove();
    }
});