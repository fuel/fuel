#FuelPHP

* Version: 1.2
* [Website](http://fuelphp.com/)
* [Release Documentation](http://docs.fuelphp.com)
* [Development Documentation](http://fueldevdocs.exite.eu) and in its own [git repo](https://github.com/fuel/docs)
* [Forums](http://fuelphp.com/forums) for comments, discussion and community support

## Description

FuelPHP is a fast, lightweight PHP 5.3 framework. In an age where frameworks are a dime a dozen, We believe that FuelPHP will stand out in the crowd.  It will do this by combining all the things you love about the great frameworks out there, while getting rid of the bad.

##Development Team

* Harro Verton - Project Manager, Developer ([http://wanwizard.eu/](http://wanwizard.eu/))
* Jelmer Schreuder - Project Architect, Senior Developer ([http://jelmerschreuder.nl/](http://jelmerschreuder.nl/))
* Frank de Jonge - Developer ([http://frenky.net/](http://frenky.net/))
* Phil Sturgeon - Developer ([http://philsturgeon.co.uk](http://philsturgeon.co.uk))
* Dan Horrigan - Founder, Developer ([http://dhorrigan.com](http://dhorrigan.com))

##Downloading FuelPHP

Since FuelPHP uses Submodules and since GitHub Downloads don't support submodules, do not download FuelPHP using the Downloads link here. Instead, use the [download links location in the docs](http://docs.fuelphp.com/installation/download.html).

##Cloning FuelPHP

FuelPHP uses submodules for things like the **core** folder.  After you clone the repository you will need to init and update the submodules.

Here is the basic usage:

    git clone --recursive git://github.com/fuel/fuel.git

The above command is the same as running:

    git clone git://github.com/fuel/fuel.git
    cd fuel/
    git submodule init
    git submodule update

You can also shorten the last two commands to one:

    git submodule update --init
