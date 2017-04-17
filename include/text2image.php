<?php
// creation d'une image à partir d'un texte
class Cartouche {
    var $nom_image;
	var $texte;
	var $path;
	var $font_name;
	var $font_size;
	var $text_color;

	function __construct($nom_image, $texte, $path, $font_name, $font_size, $text_color){
            $this->nom_image = '_'.$nom_image;
			$this->texte = utf8_decode($texte);
			$this->path = $path ;
   			$this->font_name = $font_name ;
		    $this->font_size = $font_size ;
		    $this->text_color = $this->hexa2rgb($text_color) ;   // ffdd00;
	}

	function setTextImage(){

		// Charge la police GD
        $font = 5;
        if (file_exists('./gdfonts/'.$this->font_name.$this->font_size.'.gdf')){
			$font = imageloadfont('./gdfonts/'.$this->font_name.$this->font_size.'.gdf');
		}
        else if (file_exists('../gdfonts/'.$this->font_name.$this->font_size.'.gdf')){
			$font = imageloadfont('../gdfonts/'.$this->font_name.$this->font_size.'.gdf');
		}
        $fonth = imagefontheight($font);
        $fontw = imagefontwidth($font);

        $sizex = strlen($this->texte) * $fontw+16;
        $sizey = $fonth+16;

        $posx = 8;
        $posy = 8;

		// DEBUG
		// echo "<br />FONTH: $fonth FONTW: $fontw <br /> TEXTE ".$this->texte." Wide : ".$sizex." Height : ".$sizey."\n";

  		if ($im = @imagecreatetruecolor($sizex, $sizey)){ // taille de l'image
            	imagesavealpha($im, true);
				//$black = imagecolorallocate($im, 0, 0, 0);
    			$red = imagecolorallocate($im, 255, 0, 0);
				//$dgreen = imagecolorallocate($im, 132, 135, 28);
				//$green = imagecolorallocate($im, 0, 255, 0);
            	//$blue = imagecolorallocate($im, 0,0, 255);
				//$yellow = imagecolorallocate($im, 255, 255, 0);
                //$pink = imagecolorallocate($im, 255, 105, 180);
				//$magenta = imagecolorallocate($im, 255, 0, 255);
            	//$cyan = imagecolorallocate($im, 0, 255, 255);
    			//$white = imagecolorallocate($im, 255, 255, 255);
    			$transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
				if (!empty($this->text_color)){
					$couleur=imagecolorallocate($im, $this->text_color->r, $this->text_color->g, $this->text_color->b);
				}
				else{
                    $couleur= $red;
				}

				imagefill($im, 0, 0, $transparent);
                imagerectangle($im, 0, 0, $sizex-1, $sizey-1, $red);

				imagestring($im, $font, $posx, $posy, $this->texte, $couleur);
                imagepng($im, $this->path.'/'.$this->nom_image.'.png');
    			imagedestroy($im);
				if (file_exists($this->path.'/'.$this->nom_image.'.png')){
					return $this->path.'/'.$this->nom_image.'.png';
				}
		}
		return '';
	}

	// --------------------
	function hexa2rgb($strcouleur){
		// conversion d'une couleur RVB "ffaa33" en composnate rgb 0..255
		$color = new stdClass();
		if (!empty($strcouleur) && strlen($strcouleur)>=6){
			$hexr = substr($strcouleur,0,2);
	        $hexg = substr($strcouleur,2,2);
			$hexb = substr($strcouleur,4,2);
			$color->r = hexdec($hexr);
            $color->g = hexdec($hexg);
            $color->b = hexdec($hexb);
		}
		return $color;
	}

}
?>