<?php
define('FPDF_FONTPATH','fpdf/font/');

require('fpdf/fpdf.php');


class PDF_Rotate extends FPDF
{
    var $angle = 0;

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}

foreach($_GET as $key => $value){
    $cards[$key] = $value;
}

$h = 82; //88
$w = 57; //63

//$h = 297;
//$w = 210;

$pdf = new PDF_Rotate();
$pdf->SetFont('Arial','',72);



if(!isset($space))
    $space = 8;

if ($handle = opendir('cards')) {
	$i = 0;
    while (false !== ($file = readdir($handle))) { 
        if ("jpg" == substr(strrchr($file, '.'), 1)) { 
			$file = "cards/".$file;
			if ($i % 9 == 0) {
				$pdf->AddPage();
				for ($l = 0; $l < 9; $l++) {
					//$pdf->Image('../img/blackside.jpg', 11-3 + $l % 3 * ($w + $space), 16-3 + floor(($l % 9) / 3) * ($h + $space), $w+6, $h+6);
					$pdf->Image('back.jpg', 11 + $l % 3 * ($w + $space), 16 + floor(($l % 9) / 3) * ($h + $space), $w, $h);
				}
				$pdf->AddPage();
			}
			list($width, $height) = getimagesize($file);
			if($width/$height > 1)
				$rotate = true;
			else
				$rotate = false;

			if($rotate) {
				$pdf->Rotate(-90, 10 + $i % 3 * ($w + $space), 16 + floor(($i % 9) / 3) * ($h + $space));
				$pdf->Image($file, 10 + $i % 3 * ($w + $space), 16 + floor(($i % 9) / 3) * ($h + $space) - $w, $h, $w);
				$pdf->Rotate(0);
			}
			else
				$pdf->Image($file, 10 + $i % 3 * ($w + $space), 16 + floor(($i % 9) / 3) * ($h + $space), $w, $h);
			$i++;
        } 
    }
    closedir($handle); 
}
$pdf->Output('shula.pdf','I');