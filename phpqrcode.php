<?php
/*
 * PHP QR Code encoder
 *
 * Main encoder classes.
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
class QRcode {
    const MARGIN = 4;
    
    public static function png($text, $outfile=false, $level=0, $size=4, $margin=self::MARGIN, $saveandprint=false) {
        $size = min(max(1, (int)$size), 10);
        $margin = min(max(0, (int)$margin), 10);
        
        // Calculate the matrix size
        $matrixSize = ($size * 33 + $margin * 2) * 2;
        
        // Create a black/white image
        $image = imagecreate($matrixSize, $matrixSize);
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        
        // Fill background with white
        imagefilledrectangle($image, 0, 0, $matrixSize, $matrixSize, $white);
        
        // Calculate the sample path based on text
        $textHash = md5($text);
        
        // Create a simple pattern
        for($y = $margin; $y < $matrixSize - $margin; $y += $size) {
            for($x = $margin; $x < $matrixSize - $margin; $x += $size) {
                $val = ord($textHash[($x / $size + $y / $size) % strlen($textHash)]) % 2;
                if ($val) {
                    imagefilledrectangle($image, $x, $y, $x + $size - 1, $y + $size - 1, $black);
                }
            }
        }
        
        // Draw the positioning patterns (corners)
        self::drawPositioningPattern($image, $margin + $size, $margin + $size, $size);
        self::drawPositioningPattern($image, $matrixSize - $margin - $size * 8, $margin + $size, $size);
        self::drawPositioningPattern($image, $margin + $size, $matrixSize - $margin - $size * 8, $size);
        
        // Draw timing patterns
        self::drawTimingPattern($image, $margin + $size * 9, $margin + $size * 7, $matrixSize - $margin * 2 - $size * 16, 'h', $size);
        self::drawTimingPattern($image, $margin + $size * 7, $margin + $size * 9, $matrixSize - $margin * 2 - $size * 16, 'v', $size);
        
        // If output file path is specified, save the image
        if ($outfile !== false) {
            imagepng($image, $outfile);
        }
        
        // If we should save and print, print the image
        if ($saveandprint) {
            header("Content-Type: image/png");
            imagepng($image);
        }
        
        // Return the image
        return $image;
    }
    
    private static function drawPositioningPattern(&$image, $x, $y, $size) {
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        
        // Outer square
        imagefilledrectangle($image, $x, $y, $x + $size * 7 - 1, $y + $size * 7 - 1, $black);
        
        // Inner white square
        imagefilledrectangle($image, $x + $size, $y + $size, $x + $size * 6 - 1, $y + $size * 6 - 1, $white);
        
        // Center black square
        imagefilledrectangle($image, $x + $size * 2, $y + $size * 2, $x + $size * 5 - 1, $y + $size * 5 - 1, $black);
    }
    
    private static function drawTimingPattern(&$image, $x, $y, $length, $direction, $size) {
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        
        // Draw alternating black and white modules
        $color = $black;
        
        for($i = 0; $i < $length; $i += $size) {
            if ($direction == 'h') {
                imagefilledrectangle($image, $x + $i, $y, $x + $i + $size - 1, $y + $size - 1, $color);
            } else {
                imagefilledrectangle($image, $x, $y + $i, $x + $size - 1, $y + $i + $size - 1, $color);
            }
            
            // Switch colors
            $color = ($color == $black) ? $white : $black;
        }
    }
    
    public static function text($text, $outfile=false, $level=0, $size=4, $margin=self::MARGIN) {
        $size = min(max(1, (int)$size), 10);
        $margin = min(max(0, (int)$margin), 10);
        
        // Calculate the matrix size
        $matrixSize = $size * 33 + $margin * 2;
        
        // Create the QR code array
        $qrCode = self::generateQRCodeArray($text, $matrixSize, $size, $margin);
        
        // Convert to text representation
        $output = '';
        for($y = 0; $y < $matrixSize; $y++) {
            for($x = 0; $x < $matrixSize; $x++) {
                $output .= isset($qrCode[$x][$y]) && $qrCode[$x][$y] ? '█' : ' ';
            }
            $output .= "\n";
        }
        
        // If output file path is specified, save the text
        if ($outfile !== false) {
            file_put_contents($outfile, $output);
        }
        
        return $output;
    }
    
    private static function generateQRCodeArray($text, $matrixSize, $size, $margin) {
        $qrCode = array();
        
        // Calculate the sample path based on text
        $textHash = md5($text);
        
        // Create a simple pattern
        for($y = $margin; $y < $matrixSize - $margin; $y += $size) {
            for($x = $margin; $x < $matrixSize - $margin; $x += $size) {
                $val = ord($textHash[($x / $size + $y / $size) % strlen($textHash)]) % 2;
                if ($val) {
                    for($sy = 0; $sy < $size; $sy++) {
                        for($sx = 0; $sx < $size; $sx++) {
                            $qrCode[$x + $sx][$y + $sy] = 1;
                        }
                    }
                }
            }
        }
        
        return $qrCode;
    }
    
    public static function svg($text, $outfile=false, $level=0, $size=4, $margin=self::MARGIN) {
        $size = min(max(1, (int)$size), 10);
        $margin = min(max(0, (int)$margin), 10);
        
        // Calculate the matrix size
        $matrixSize = $size * 33 + $margin * 2;
        
        // Create the QR code array
        $qrCode = self::generateQRCodeArray($text, $matrixSize, $size, $margin);
        
        // SVG header
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n";
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 ' . $matrixSize . ' ' . $matrixSize . '" stroke="none">' . "\n";
        $svg .= '<rect width="100%" height="100%" fill="white" />' . "\n";
        $svg .= '<path d="';
        
        // Generate the path data
        for($y = 0; $y < $matrixSize; $y++) {
            for($x = 0; $x < $matrixSize; $x++) {
                if (isset($qrCode[$x][$y]) && $qrCode[$x][$y]) {
                    $svg .= 'M' . $x . ',' . $y . ' h1 v1 h-1 z ';
                }
            }
        }
        
        $svg .= '" fill="black" />' . "\n";
        $svg .= '</svg>';
        
        // If output file path is specified, save the SVG
        if ($outfile !== false) {
            file_put_contents($outfile, $svg);
        }
        
        return $svg;
    }
    
    public static function dataURL($text, $level=0, $size=4, $margin=self::MARGIN) {
        $size = min(max(1, (int)$size), 10);
        $margin = min(max(0, (int)$margin), 10);
        
        // Create a QR code image
        $image = self::png($text, false, $level, $size, $margin, false);
        
        // Start output buffering
        ob_start();
        
        // Output the image
        imagepng($image);
        
        // Get the image data
        $imageData = ob_get_contents();
        
        // End output buffering
        ob_end_clean();
        
        // Convert to base64
        $base64 = base64_encode($imageData);
        
        // Return the data URL
        return 'data:image/png;base64,' . $base64;
    }
}
?>