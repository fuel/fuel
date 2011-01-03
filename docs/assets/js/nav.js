/*
 * This is inspired by the CodeIgniter user guide's create_menu function.
 * http://codeigniter.com/user_guide/nav/nav.js
 *
 * It extracts the navigation to a single file for easier updating.
 */

//define document navigation
var nav = {
		"Basic": {
			"Home":			"index.html",
			"Requirements":	"requirements.html",
			"License":		"license.html",
			"Credits":		"credits.html"
		},
		"Installation": {
			"Instructions":	"installation/instructions.html",
			"Download":		"installation/download.html"
		},
		"General": {
			"Controllers": {
				"Base" :		"general/controllers/base.html",
				"Template":		"general/controllers/template.html",
				"Rest":			"general/controllers/rest.html"
			},
			"Routing":			"general/routing.html",
			"Views":			"general/views.html",
			"Tasks":			"general/tasks.html",
			"Migrations":		"general/migrations.html",
			"Coding Standards":	"general/coding_standards.html"
		},
		"Classes": {
			"Cli":		"classes/cli.html",
			"Config":	"classes/config.html",
			"Cookie":	"classes/cookie.html",
			"Crypt":	{
				"Configuration":	"classes/crypt/config.html",
				"Usage":			"classes/crypt/usage.html"
			},
			"Event":	"classes/event.html",
			"Ftp":	{
				"Configuration":	"classes/ftp/config.html",
				"Usage":			"classes/ftp/usage.html"
			},
			"Input":	"classes/input.html",
			"Log":	    "classes/log.html",
			"Session":	{
				"Configuration":	"classes/session/config.html",
				"Usage":			"classes/session/usage.html",
				"Advanced":			"classes/session/advanced.html"
			},
			"Upload":	{
				"Configuration":	"classes/upload/config.html",
				"Usage":			"classes/upload/usage.html"
			},
			"Migrate":	"classes/migrate.html",
			"Html":		"classes/html.html"
		}
};

//insert the navigation
function show_nav(page, path)
{
	active_path = window.location.pathname;
	path = path == null ? '' : path;
	$.each(nav, function(section,links) {
		var h3 = $('<h3></h3>');
		h3.addClass('collapsible').html(section);
		h3.attr('id', 'nav_'+section.toLowerCase().replace(' ', ''));
		h3.bind('click', function() {
			$(this).next('div').slideToggle();
		});

		$('#main-nav').append(h3);
		var div = $('<div></div>');
		if ('nav_'+page != h3.attr('id')) {
			div.hide();
		}

		var ul = div.append('<ul></ul>');
		ul.find('ul').append(generate_nav(path, links));

		$('#main-nav').append(div);
		$('#main-nav').find('#nav_'+page).next('div').slideDown();
	});
}

//generate the navigation
function generate_nav(path, links)
{
	var html = '';
	$.each(links, function(title, href) {
		if (typeof(href) == "object")
		{
			for(var link in href) break;
			html = html + '<li><a href="'+path+href[link]+'">' + title + '</a>';
			html = html + '<ul>' + generate_nav(path, href) + '</ul></li>';
		}
		else
		{
			active = '';
			if (active_path.indexOf(href, active_path.length - href.length) != -1)
			{
				active = ' class="active"';
			}
			html = html + '<li><a href="'+path+href+'"'+active+'>'+title+'</a></li>';
		}
	});
	return html;
}
