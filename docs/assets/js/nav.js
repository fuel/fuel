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
			"Classes": {
				"Introduction":	"general/classes.html",
				"Extending Core":	"general/extending_core.html"
			},
			"Tasks":			"general/tasks.html",
			"Migrations":		"general/migrations.html",
			"Coding Standards":	"general/coding_standards.html"
		},
		"Classes": {
			 "Arr":			"classes/arr.html",
			"Asset":		"classes/asset.html",
			"Cache": {
				"Configuration":	"classes/cache/config.html",
				"Usage":			"classes/cache/usage.html",
				"Advanced":			"classes/cache/advanced.html"
			},
			"Cli":			"classes/cli.html",
			"Config":		"classes/config.html",
			"Cookie":		"classes/cookie.html",
			"Crypt":		"classes/crypt.html",
			// "Database":		"todo.html",
			 "Date":			"classes/date.html",
			// "DButil":		"todo.html",
			"Debug":		"classes/debug.html",
			// "Email":		"todo.html",
			"Event":		"classes/event.html",
			// "Error":		"todo.html",
			// "Exception":	"todo.html",
			// "Fieldset":		"todo.html",
			"File": {
				"Introduction":	"classes/file/intro.html",
				"Usage":		"classes/file/usage.html",
				"Advanced":		"classes/file/advanced.html"
			},
			// "Form":			"todo.html",
			"Ftp":			"classes/ftp.html",
			"Html":			"classes/html.html",
			"Inflector":	"classes/inflector.html",
			"Input":		"classes/input.html",
			"Lang":			"classes/lang.html",
			"Log":	    	"classes/log.html",
			"Migrate":		"classes/migrate.html",
			"Output":		"classes/output.html",
			"Pagination":	"classes/pagination.html",
			// "Profiler":	"todo.html",
			// "Redis":		"todo.html",
			// "Request":	"todo.html",
			// "Route":		"todo.html",
			"Security":		"classes/security.html",
			"Session": {
				"Configuration":	"classes/session/config.html",
				"Usage":			"classes/session/usage.html",
				"Advanced":			"classes/session/advanced.html"
			},
			// "Unzip":		"todo.html", // dont do this, needs to be cleaned up. forget it exists.
			"Upload": {
				"Configuration":	"classes/upload/config.html",
				"Usage":			"classes/upload/usage.html"
			},
			"Uri":			"classes/uri.html",
			"Validation":	"classes/validation.html"
			// "View":			"todo.html"
		},
		"Packages": {
			"ActiveRecord": {
				"Introduction":		"packages/activerecord/intro.html",
				"Conventions":		"packages/activerecord/conventions.html"
			},
			"Auth": {
				"Introduction":		"packages/auth/intro.html",
				"Usage":			"packages/auth/usage.html",
				"Driver types":		"packages/auth/driver_types.html",
				"SimpleAuth, Group &amp; Acl":		"packages/auth/simpleauth.html",
				"Writing drivers":	"packages/auth/drivers.html"
			},
			// "Octane":			"todo.html",
			"Oil": {
				"Introduction":		"packages/oil/intro.html",
				"Generate":			"packages/oil/generate.html",
				"Refine":			"packages/oil/refine.html",
				"Package":			"packages/oil/package.html",
//				"Test":				"packages/oil/test.html",
				"Console":			"packages/oil/console.html"
			}
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
