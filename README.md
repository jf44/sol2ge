# SOLTools

SailOnLine races in Google Earth

This program collect boats data on www.sailonline.org and display them on Google Earth.

It is delivered "as this" in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the [GNU General Public License](http://www.gnu.org/licenses/) for more details.

## Scripts

* solboats2ge.php : a G.E. view of the boats in race

## SolBoats2GE Documentation

What's that ?
A web server which gets Sailonline boats positions and export them as a KML / KMZ file for Google Earth

### How it works

Boats positions (longitude, latitude) and COG (direction) are sent by the Sol server.
So I compute the speed (SOG) and  TWA from Grib file and Polars, with a few liberties
since I have not implemented exactly the interpolation algorithm that Sol uses.

If anybody can gives me hints about how Sol compute interpolation, I'll implemented it...

The sails (jib, genois, gennaker or spinnaker) are deduced from TWA (if greater than 140° spinnaker is displayed).

The scale determines the size of the boats on the map.
Set scale to 0.1 (or less) for narrows, 0.5 for coastal races, 2 to 15 for transocean races.

### Improvements

Improvements of that version :

* localisation (fr, en).
* getting Grib, Polar, Marks, Tracks
* setting TWD, TWS, TWA, SOG from grib and polars
* computation of boat roll and tilt from TWS / TWA and the right sail (jib, genois, gennaker, spi)
* tour of the fleet

 monohull or multihull (catatmaran, trimaran)

Next improvements

* choose which boats to display
* new boats like greatboats, iceboats, etc.

If you like to get others improvements send me a mail.

JF44 : jean.fruitet@free.fr

### Needs

A) You need Google Earth to display the KML/KMZ files

B) You need a web server which allows the PHP fonction file_get_contents($url) to collect the data
directly from the SailOnLine race server and generate the KML / KMZ files.

If you lack of a personneal webserver on the Internet you can set up a local web server to test and produce your own G.E maps

Look at [Apachefriends' Xampp](https://www.apachefriends.org/) for exemple.

If you own a personnal Web server on the Internet *whith the PHP fonction* **file_get_contents($SolServerUrl)**
allowed, the visitors of your site may generate their own maps online.

### Installation

Unzip the sol2gearth.zip archive in a folder of your local webserver, for exemple
	./hdocs/solgearth/

If you use a remote server please edit the $url_serveur_local variable and give
the url from where the script will be loaded.

### Usage

Connect with a Web browser to the *solgerth URL*, select the **SolBoats2GE** link then

1. Choose a race
2. Select a scale
3. Select a boat model
4. Click the yellow "Validate" button...
5. Wait a while... Two KMZ and one KML files are produced.
6. Open one of them with G.E.

You may also use
http://solboat2ge.php?lang=en&racenumber=xxxx&scale=yy&boattype=catamaran

xxxx : race id to load.
scale : yy [0.1 - 15]
boattype : monocoque, catamaran, trimaran, greatboat, motorboat

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


That's all folks.

