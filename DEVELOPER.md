# SOLTools Developer info

SailOnLine races in Google Earth

This program collect boats data on www.sailonline.org and display them on Google Earth.

It is delivered "as this" in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the [GNU General Public License](http://www.gnu.org/licenses/) for more details.

## Folders


### solboats2ge.php : a G.E. view of the boats in race

Theses folders are mandatory

* css: guess!
* gdfonts: data for generating screen overlays for G.E.
* include: all the stuff to keep then display boats in G.E.  
* js: javascript scripts
* kml: where are kept the KML files produced by the soft
* kmz: KML archives in zip (kmz) format
* lang: localisation stuff; add your own traduction here
* sol_include: specific to SOL configuration and connection
* SOLGribXml: where grib files are kept.
NB: These gribs have only 2 or 3 layers of data. For a complete grib file use that ones AG DCChecker produces.
* sources: old stuff for mapping small pictures on G.E. Not used in this version
* sources_3d: boats models (.dae format) and textures 
* tmp: overlay images
*
These folders are optionnal

* images: Where to keep manual screenshots and G.E. screen overlays

### Localisation

To add a new language "xx" copy the *./lang/sol2kml_en_utf8.php* to *./lang/sol2kml_xx_utf8.php*

Edit the new file and translate to "xx" language *each second part* of the sentences (after the '='):

> $t_string['key'] = 'This is the key message without any parameter';

> $t_string['key2'] = 'This is the key2 message with the parameter {$a}';

Warning : do not translate the key part !:>))
The {$a} is a token that has to be kept as this and will be set up dynamically in the code.

For exemple
> $t_string['welcome'] = '"Welcome {$a}"';

when coded as

> echo $al->get_string('welcome','John Do');

will output

> "Welcome John Do".

So don't bother with the {$a} parametrers, but keep them in the translation.

### Boats models

I use DAE (collada) files to put boats in G.E., one serie by type (monohull, multihull, motorboat) 

Models are in the ./source_3d/models/monocoque, the ./source_3d/models/multicoque folder and the ./source_3d/models/motorboat folders.

For each of the 4 boats 'allure' you have to set a 3D views, so two sets of 4 collada files each.

One set for port wind view

* model_foc_babord (hull + mainsail + jib portwind)
* model_genois_babord (hull + mainsail + genois portwind)
* model_gennaker_babord (hull + mainsail + gennaker portwind)
* model_spi_babord (hull + mainsail + spinnaker portwind)

One set for starboard wind

* model_foc_tribord (hull + mainsail + jib starportwind)
* model_genois_tribord (hull + mainsail + genois starboardwind)
* model_gennaker_tribord (hull + mainsail + gennaker starboardwind)
* model_spi_tribord (hull + mainsail + spinnaker starboardwind)

The original 3D models are designed with Sketchup. They are very large but simplified boats object.

The hull and sails colors are images textures computed in the fly, but since the SailOnLine boats colors are not choosen by the users, they seams randomly selected.

It would be an improvement to give the color choice to users...

All textures are kept in the destination folder : ./kml/sol3d/models/textures

If you are interested by the original SketchUp models, or like to add some new models like iceboats, send me an e-mail.



