$(function () {
	$("form#resource").submit(function () {
		$.ajax({
			type:"POST",			
			url:$(this).attr("action"),
			data: $(this).serialize(),
			dataType:"json",
			success:function (data) {
			
			}
		});
		return false;
	});
});