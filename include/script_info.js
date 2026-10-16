jQuery(function($)
{
	if(document.cookie.indexOf("cookie_accepted=") !== -1)
	{
		$("#accepted_cookies").fadeIn();
	}

	else
	{
		$("#accept_cookies").fadeIn();
	}

	$(document).on('click', "#accept_cookies .accept_button", function()
	{
		var d = new Date();
		d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));

		document.cookie = "cookie_accepted=true; expires=" + d.toUTCString() + "; path=/";

		$("#accept_cookies").fadeOut();
		$("#accepted_cookies").fadeIn();
	});

	$(document).on('click', "#accepted_cookies > span", function()
	{
		document.cookie = "cookie_accepted=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";

		$("#accepted_cookies").fadeOut();
		$("#accept_cookies").fadeIn();
	});
});