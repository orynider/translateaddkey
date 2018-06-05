<?php
mb_internal_encoding('UTF-8');

function mb_ord($string) {
	$result = unpack('N', mb_convert_encoding($string, 'UCS-4BE', 'UTF-8'));

	if (is_array($result)) {
		return $result[1];
	}

	return ord($string);
}

function hebrew_t13n($word) {
	$simpleHandler = function($x) {
		return function($chars, $i) use($x) {
			return array($x, $i + 1);
		};
	};
	$mapiqHandler = function($soft, $hard) {
		return function($chars, $i) use($soft, $hard) {
			return $chars[$i + 1] === 'ּ' ? array($hard, $i + 2) : array($soft, $i + 1);
		};
	};
	$vowelHandler = function($base, $double) use(&$vowels) {
		return function($chars, $i) use(&$vowels, $base, $double) {
			if($double && $chars[$i + 1] === 'ִ') {
				return array($base !== 'i' ? $base.'y' : 'i', $i + 1);
			}

			$tmp = $i + ($chars[$i + 2] === 'ּ' ? 3 : 2);
			if($chars[$i + 1] === 'י' &&
				!array_key_exists($chars[$tmp], $vowels) &&
				!($chars[$tmp] === 'ו' && strpos('ֹּ', $chars[$tmp + 1]) !== false)) {
				return array($base !== 'i' ? $base.'i' : 'i', $tmp);
			}
			return array($base, $i + 1);
		};
	};

	$vowels = array(
		'ֱ'=>$vowelHandler('e', false),
		'ֲ'=>$vowelHandler('a', false),
		'ֳ'=>$vowelHandler('o', false),
		'ִ'=>$vowelHandler('i', false),
		'ֵ'=>function($chars, $i) use(&$vowels) {
			if($chars[$i + 1] === 'ִ') {
				return array('ey', $i + 1);
			}

			$tmp = $i + ($chars[$i + 2] === 'ּ' ? 3 : 2);
			if($chars[$i + 1] === 'י') {
				if(array_key_exists($chars[$tmp], $vowels) ||
					($chars[$tmp] === 'ו' && in_array($chars[$tmp + 1], array('ֹ', 'ּ')))) {
					return array("e", $tmp + 1);
				} else {
					return array("ei", $tmp);
				}
			}
			return array("ei", $i + 1);
		},
		'ֶ'=>$vowelHandler('e', true),
		'ַ'=>$vowelHandler('a', true),
		'ָ'=>function($chars, $i) use(&$vowels, $base, $double) {
			if($chars[$i + 1] === 'ִ') {
				return array('ay', $i + 1);
			}

			if($chars[$i + 1] === 'י' && $chars[$i + 2] === 'ו') {
				return array('a', $i + 2);
			}

			$tmp = $i + ($chars[$i + 2] === 'ּ' ? 3 : 2);
			if($chars[$i + 1] === 'י' &&
				!array_key_exists($chars[$tmp], $vowels) &&
				!($chars[$tmp] === 'ו' && strpos('ֹּ', $chars[$tmp + 1]) !== false)) {
				return array('ai', $tmp);
			}
			return array('a', $i + 1);
		},
		'ֹ'=>function($chars, $i) use(&$vowels) {
			$tmp = $i + ($chars[$i + 2] === 'ּ' ? 3 : 2);
			if($chars[$tmp + 1] === 'י') {
				if(array_key_exists($chars[$tmp], $vowels) ||
					($chars[$tmp] === 'ו' && strpos('ֹּ', $chars[$tmp + 1]) !== false)) {
					return array("o", $i + 1);
				} else {
					return array("oi", $tmp);
				}
			}
			if($chars[$i + 1] === 'ו' &&
				!array_key_exists($chars[$tmp], $vowels)) {
				return array("o", $tmp);
			}
			return array("o", $i + 1);
		},
		'ֺ'=>function($chars, $i) {
			$tmp = $i + ($chars[$i + 2] === 'ּ' ? 3 : 2);
			if($chars[$tmp + 1] === 'י') {
				if(array_key_exists($vowels, $chars[$tmp]) ||
					($chars[$tmp] === 'ו' && strpos('ֹּ', $chars[$tmp + 1]) !== false)) {
					return array("o", $i + 1);
				} else {
					return array("oi", $tmp);
				}
			}
			if($chars[$i + 1] === 'ו' &&
				!array_key_exists($chars[$tmp], $vowels)) {
				return array("o", $tmp);
			}
			return array("o", $i + 1);
		},
		'ֻ'=>function($chars, $i) use(&$vowels) {
			$tmp = $i + ($chars[$i + 2] === 'ּ' ? 3 : 2);
			if($chars[$tmp + 1] === 'י') {
				if(array_key_exists($chars[$tmp], $vowels) ||
					($chars[$tmp] === 'ו' && strpos('ֹּ', $chars[$tmp + 1]) !== false)) {
					return array("u", $i + 1);
				} else {
					return array("ui", $tmp);
				}
			}
			if($chars[$i + 1] === 'ו' &&
				!array_key_exists($chars[$tmp], $vowels)) {
				return array("u", $tmp);
			}
			return array("u", $i + 1);
		},
		'ְ'=>$simpleHandler('')
	);

	$letters = array(
		'א'=>function($chars, $i) use(&$letters, &$vowels) {
			if($i === 0) {
				return array("", $i + 1);
			}
			
			if(!array_key_exists($chars[$i - 1], $letters) &&
				!array_key_exists($chars[$i - 1], $vowels)) {
				return array("", $i + 1);
			}

			if($i === count($chars) - 1 ||
				(!array_key_exists($chars[$i + 1], $letters) &&
				!array_key_exists($chars[$i + 1], $vowels))) {
				return array("", $i + 1);
			}

			return array("'", $i + 1);
		},
		'ב'=>function($chars, $i) {
			if($chars[$i + 1] === 'ּ') {
				if($chars[$i + 2] === 'ְ' && $chars[$i + 3] === 'ה') {
					return array("b'h", $i + 4);
				}
				return array('b', $i + 2);
			}
			return array('v', $i + 1);
		},
		'ג'=>$simpleHandler('g'),
		'ד'=>$simpleHandler('d'),
		'ה'=>$simpleHandler('h'),
		'ו'=>function($chars, $i) use(&$vowels) {
			if($chars[$i + 1] === 'ּ' && !array_key_exists($chars[$i + 2], $vowels)) {
				return array('u', $i + 2);
			}
			if($chars[$i + 1] === 'ֹ' || $chars[$i + 1] === 'ֺ') {
				return array('o', $i + 2);
			}
			return array('w', $i + 1);
		},
		'ז'=>$simpleHandler('z'),
		'ח'=>$simpleHandler('ch'),
		'ט'=>$simpleHandler('t'),
		'י'=>$simpleHandler('y'),
		'ך'=>$mapiqHandler('kh', 'k'),
		'כ'=>function($chars, $i) {
			if($chars[$i + 1] === 'ּ') {
				if($chars[$i + 2] === 'ְ' && $chars[$i + 3] === 'ה') {
					return array("k'h", $i + 4);
				}
				return array('k', $i + 2);
			}
			return array('kh', $i + 1);
		},
		'ל'=>$simpleHandler('l'),
		'ם'=>$simpleHandler('m'),
		'מ'=>$simpleHandler('m'),
		'ן'=>$simpleHandler('n'),
		'נ'=>$simpleHandler('n'),
		'ס'=>$simpleHandler('s'),
		'ע'=>$simpleHandler('′'),
		'ף'=>$mapiqHandler('f', 'p'),
		'פ'=>$mapiqHandler('f', 'p'),
		'ץ'=>$simpleHandler('tz'),
		'צ'=>$simpleHandler('tz'),
		'ק'=>$simpleHandler('q'),
		'ר'=>$simpleHandler('r'),
		'ש'=>function($chars, $i) {
			if($chars[$i + 1] === 'ׂ') {
				return array('s', $i + 2);
			}
			if($chars[$i + 2] === 'ׂ') {
				return array('s', $i + 1);
			}
			if($chars[$i + 1] === 'ׁ') {
				return array('sh', $i + 2);
			}
			if($chars[$i + 2] === 'ׁ') {
				return array('sh', $i + 1);
			}
			return array('sh', $i + 1);
		},
		'ת'=>$mapiqHandler('th', 't')
	);

	$fns = $vowels + $letters + array(
		'ּ'=>$simpleHandler(''),
		'ְ'=>$simpleHandler(''),
		'ׁ'=>$simpleHandler(''),
		'ׂ'=>$simpleHandler(''),
		' '=>$simpleHandler(' '),
		'־'=>$simpleHandler('-'),
		'׃'=>$simpleHandler('.')
	);

  $stop = mb_strlen($word);
  $chars = array();

  for ($idx = 0; $idx < $stop; $idx++) {
     $chars[] = mb_substr($word, $idx, 1);
  }

	$countChars = count($chars);
	$t13n = '';
	for($i = 0; $i < $countChars; ++$i) {
		$char = $chars[$i];
		if((mb_ord($char) < mb_ord('ְ') &&
			mb_ord($char) >= mb_ord('֑')) || $char === 'ֽ') {
			unset($chars[$i]);
		}
	}

	$chars = array_values($chars);
	$countChars = count($chars);
	$i = 0;
	do {
		$char = $chars[$i];
		if(array_key_exists($char, $fns)) {
			list($nextp, $nexti) = $fns[$char]($chars, $i);
		} else {
			$nextp = $char;
			$nexti = $i + 1;
		}
		$t13n .= $nextp;
		$i = $nexti;
	}while($i < $countChars);

	return $t13n;
}

header('Content-Type: text/html; charset=utf-8');
echo '<form method="post"><textarea name="text" cols="50">',
	$_POST['text'], '</textarea><br/><input type="submit"/><br/>',
	'<textarea name="out" cols="50">', hebrew_t13n($_POST['text']), '</textarea></form>';
?>