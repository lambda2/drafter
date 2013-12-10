

$(document).ready(function()
{
	var dr = $("#draft-aera");
	var mk = $("#marked-aera");

	dr.change(function()
	{
		var ctnt = $(this).val();
		var mkd = marked(ctnt);
		mk.html(mkd);
	});
	
	$('[data-action="preview"]').click(function()
	{
		dr.hide();
		mk.show();
		$('[data-action="edit"]').show();
		$('[data-action="preview"]').hide();
	});

	$('[data-action="edit"]').click(function()
	{
		dr.show();
		mk.hide();
		$('[data-action="edit"]').hide();
		$('[data-action="preview"]').show();
	});
});
