<?php

/**
 * Copyright 2012 Nathan Dinsmore
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

function makeThumbnailFromProj($filename) {
	global $f, $objects;
	$f = fopen($filename, 'r');

	if (read(8) !== "\x53\x63\x72\x61\x74\x63\x68\x56" ||
		intval(read(2)) < 1) die('Bad header');
	read(4);

	if (read(10) !== "\x4f\x62\x6a\x53\x01\x53\x74\x63\x68\x01") die('Bad header');
	$count = ru32();
	$objects = array();

	$i = $count;
	while ($i--) {
		$objects[] = robj();
	}

	$i = $count;
	while ($i--) {
		fixobj($objects[$i]);
	}

	$info = $objects[0][1];
	$i = array_search('thumbnail', $info['keys']);
	if ($i === false) die('No thumbnail');
	$form = $info['values'][$i];

	$bits = $form[4];
	if (isset($bits['isBitmap'])) {
		$bitmap = $bits['bitmap'];
	} else {
		$i = 0;
		$length = $bits[$i++];
		if ($length > 223) {
			$length = $length < 255 ?
				(($length - 224) << 8) + $bits[$i++] :
				$bits[$i++] << 24 | $bits[$i++] << 16 | $bits[$i++] << 8 | $bits[$i++];
		}
		$bitmap = array();
		while ($i < count($bits)) {
			$v = $bits[$i++];
			if ($v > 223) {
				$v = $v < 255 ?
					(($v - 224) << 8) + $bits[$i++] :
					$bits[$i++] << 24 | $bits[$i++] << 16 | $bits[$i++] << 8 | $bits[$i++];
			}
			$n = $v >> 2;
			switch ($v & 3) {
			case 1:
				$d = $bits[$i++];
				$n *= 4;
				while ($n--) {
					$bitmap[] = $d;
				}
				break;
			case 2:
				$d = $bits[$i++];
				$e = $bits[$i++];
				$f = $bits[$i++];
				$g = $bits[$i++];
				while ($n--) {
					$bitmap[] = $d;
					$bitmap[] = $e;
					$bitmap[] = $f;
					$bitmap[] = $g;
				}
				break;
			case 3:
				$n *= 4;
				while ($n--) {
					$bitmap[] = $bits[$i++];
				}
				break;
			}
		}
	}
	$width = $form[0];
	$height = $form[1];

	$im = imagecreatetruecolor($width, $height);

	$colors = $form[5];
	$i = count($colors);
	while ($i--) {
		$colors[$i] = imagecolorallocatealpha($im, $colors[$i][0], $colors[$i][1], $colors[$i][2], 127 - $colors[$i][3] / 2);
	}

	imagesavealpha($im, true);
	imagealphablending($im, false);
	imagefilledrectangle($im, 0, 0, $width, $height, imagecolorallocatealpha($im, 0, 0, 0, 127));

	for ($x = 0; $x < $width; ++$x) {
		for ($y = 0; $y < $height; ++$y) {
			$bit = $y * $width + $x;
			if (!isset($bitmap[$bit])) continue;
			imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $colors[$bitmap[$bit]]);
		}
	}
	
	$rand = rand();
	imagepng($im, SRV_ROOT . '/data/' . $rand);
	$out = file_get_contents(SRV_ROOT . '/data/' . $rand);
	imagedestroy($im);
	unlink(SRV_ROOT . '/data/' . $rand);
	return $out;
}

function read($n=1) {
	global $f;
	return $n == 0 ? '' : fread($f, $n);
}

function ru8() {
	return ord(read());
}
function ru16() {
	return ord(read()) << 8 | ord(read());
}
function ru24() {
	return ord(read()) << 16 | ord(read()) << 8 | ord(read());
}
function ru32() {
	return ord(read()) << 24 | ord(read()) << 16 | ord(read()) << 8 | ord(read());
}
function rs8() {
	$a = ord(read());
	return $a >= 0x80 ? 0x100 - $a : $a;
}
function rs16() {
	$a = ord(read());
	$b = $a << 8 | ord(read());
	return $a >= 0x80 ? 0x10000 - $b : $b;
}
function rs24() {
	$a = ord(read());
	$b = $a << 16 | ord(read()) << 8 | ord(read());
	return $a >= 0x80 ? 0x1000000 - $b : $b;
}
function rs32() {
	$a = ord(read());
	$b = $a << 24 | ord(read()) << 16 | ord(read()) << 8 | ord(read());
	return $a >= 0x80 ? 0x100000000 - $b : $b;
}
function rbytes($n) {
	$s = read($n);
	$bytes = array();
	for ($i = 0; $i < $n; ++$i) {
		$bytes[] = ord($s[$i]);
	}
	return $bytes;
}
function robj() {
	if (($class = ru8()) > 99) {
		read();
		$fieldCount = ru8();
		$fields = array();
		while ($fieldCount--) {
			$fields[] = rfield();
		}
		return array($class, array(), $fields);
	}
	return array($class, rff($class));
}
function rff($class) {
	switch ($class) {
	case 1: return null;
	case 2: return true;
	case 3: return false;
	case 4: return rs32();
	case 5: return rs16();
	case 6:
	case 7: read(ru16()); return 0;
	case 8: read(8); return 0;
	case 9:
	case 10:
	case 14: return read(ru32());
	case 11: return rbytes(ru32());
	case 12: read(ru32() * 2); return 0;
	case 13:
		$bitmap = array();
		$n = ru32();
		while ($n--) {
			$bitmap[] = ru32();
		}
		return array('isBitmap' => true, 'bitmap' => $bitmap);
	case 20:
	case 21:
	case 22:
	case 23:
		$set = array();
		$n = ru32();
		while ($n--) {
			$set[] = rfield();
		}
		return $set;
	case 24:
	case 25:
		$keys = array();
		$values = array();
		$n = ru32();
		while ($n--) {
			$keys[] = rfield();
			$values[] = rfield();
		}
		return array('keys' => $keys, 'values' => $values);
	case 30:
		$n = ru32();
		return array(($n >> 20 & 0x3ff) >> 2, ($n >> 10 & 0x3ff) >> 2, ($n & 0x3ff) >> 2, 255);
	case 31:
		$n = ru32();
		return array(($n >> 20 & 0x3ff) >> 2, ($n >> 10 & 0x3ff) >> 2, ($n & 0x3ff) >> 2, ru8());
	case 32:
		return array(rfield(), rfield());
	case 33:
		return array(rfield(), rfield(), rfield(), rfield());
	case 34:
		return array(rfield(), rfield(), rfield(), rfield(), rfield());
	case 35:
		return array(rfield(), rfield(), rfield(), rfield(), rfield(), rfield());
	}
	die('Bad fixed-format');
}
function rfield() {
	if (($class = ru8()) == 99) {
		return array('ref' => ru24());
	}
	return rff($class);
}
function fixobj(&$o) {
	$class = $o[0];
	if ($class < 99) {
		fixff($class, $o[1]);
	} else {
		fixa($o[2]);
	}
}
function fixff($class, &$o) {
	switch ($class) {
	case 20:
	case 21:
	case 22:
	case 23: fixa($o); break;
	case 24:
	case 25: fixa($o['keys']); fixa($o['values']); break;
	case 32:
	case 33: fixa($o); break;
	case 34: fixt($o[3]); fixt($o[4]); break;
	case 35: fixt($o[3]); fixt($o[4]); fixt($o[5]); break;
	}
}
function fixa(&$a) {
	global $objects;
	$n = count($a);
	while ($n--) {
		if (is_array($x = $a[$n]) && isset($x['ref'])) {
			if ($x['ref'] > count($objects)) die('Bad objref');
			$a[$n] = $objects[$x['ref'] - 1][1];
		}
	}
	return $a;
}
function fixt(&$o) {
	global $objects;
	if (is_array($o) && isset($o['ref'])) {
		if ($o['ref'] > count($objects)) die('Bad objref');
		$o = $objects[$o['ref'] - 1][1];
	}
}