<?php
declare(strict_types=1);

/**
 * Created by Gbenga Ogunbule.
 * User: Gbenga Ogunbule
 * Date: 07/07/2019
 * Time: 21:09
 */

namespace App\Word;

class TextProcessing{
	private $text;
	private $number;
	
	public function wrapText($width, $indent){
		$wrapped="";
		$paragraphs=explode("\n", $this->$this->text);

		foreach($paragraphs as $paragraph){
			if($indent > 0 ) $wrapped .= str_repeat("&nbsp;" , $indent);

				$words = explode("", $paragraph);
				$len = $indent;

				foreach($words as $word){
					$wlen = strlen($word);

					if(($len + $wlen) < $width){
						$wrapped.= "$word";
						$len += $wlen + 1;
					}else{
						$wrapped = rtrim($wrapped);
						$wrapped .= "<br/>\n $word";
						$len = $wlen;
					}
				}

				$wrapped = rtrim($wrapped);
				$wrapped .= "<br/>\n";
			}

			return $wrapped;
		}
	}

	public function capsControl($text, $type){
		switch($type){
			case "u" : return strtoupper($text);

			case "l" : return strtolower($text);

			case "w" :
				$newtext = "";
				$words = explode(" ", $text);
				
				foreach($words as $word)
					$newtext .= ucfirst(strtolower($word)) . "";
					return rtrim($text);

			case "s" :
				$newtext = "";
				$sentences = explode(".", $text);
				
				foreach($sentences as $sentence)
					$newtext .= ucfirst(ltrim(strtolower($sentence))) . ".";
					return rtrim($newtext);
		}
		return $text; 
	}

	public function friendlyText($text, $emphasis){
		$misc = ["let us", "let's", "i\.e\.", "for example", "e\.g\.", "for example","can not","can't","can not", "can't", "shall not", "shan't", "will not", "won't"];
	
		$nots = ["are", "could", "did", "do", "does", "is", "had", "has", "have", "might", "must", "should", "was", "were","would"];

		$haves = ["could", "might", "must", "should", "would"];

		$who = ["he", "here", "how", "I", "it", "she", "that", "there", "they", "we", "who", "what", "when", "where", "why","you"];

		$what = ["am", "are", "had", "has", "have", "shall", "will", "would"];

		$contractions = ["m", "re", "d", "s", "ve", "ll", "ll", "d"];

		for($j = 0; $j < sizeof($misc); $j += 2){
			$from = $misc[$j];
			$to = $misc[$j + 1];
			
			$text = FT_FN1($from, $to, $text, $emphasis);
    	}

		for($j=0; $j < sizeof($nots); ++$j){
			$from = $nots[$j] . "not";
			$to = $nots[$j] . "n't";
			$text = FT_FN1($from, $to, $text, $emphasis);
		}

		for($j=0;$j<sizeof($haves);++$j){
			$from = $haves[$j] . "have";
			$to = $haves[$j] . "'ve";
			
			$text = FT_FN1($from, $to, $text, $emphasis);
		}

		for($j = 0; $j < sizeof($who); ++$j){
			for($k=0; $k < sizeof($what); ++$k){
				$from = "$who[$j] $what[$k]";
				$to = "$who[$j]'$contractions[$k]";
				$text = FT_FN1($from, $to, $text, $emphasis);
			}
		}

		$to = "'s";
		$u1= $u2 = "";

		if($emphasis){
			$u1="<u>";
			$u2="</u>";
		}

		return preg_replace("/([\w]*)is([^\w]+)/", "$u1$1$to$u2$2", $text);
	}

	function FT_FN1($f, $t, $s, $e){
		$uf=ucfirst($f);
		$ut=ucfirst($t);

		if($e){
			$t = "<u>$t</u>";
			$ut = "<u>$ut</u>";
		}

		$s = preg_replace("/([^\w]+)$f([^\w]+)/", "$1$t$2", $s);
		return preg_replace("/([^\w]+)$uf([^\w]+)/", "$1$ut$2", $s);
	}

	public function wordSelector($text, $matches, $replace) {
		foreach($matches as $match){
			switch($replace){
				case"u":
				case"b":
				case"i":
					$text = preg_replace("/([^\w]+)($match)([^\w]+)/", "$1<$replace>$2</$replace>$3", $text);
					break;

				default:
					$text = preg_replace("/([^\w]+)$match([^\w]+)/", "$1$replace$2", $text);
					break;
			}
		}

		return $text;
	}

	public function countTail($number) {
		$nstring = (string)$number;
		$pointer = strlen($nstring) - 1;
		$digit = $nstring[$pointer];
		$suffix = "th";

		if($pointer == 0 || ($pointer > 0 && $nstring [$pointer-1] != 1)){
			switch($nstring[$pointer]){
				case 1: 
					$suffix = "st";
					break;
				case 2: 
					$suffix = "nd";
					break;
				case 3: 
					$suffix = "rd";
					break;
			}
		}

		return $number . $suffix;
	}

	public function textTruncate($text, $max, $symbol) {
		$temp = substr($text, 0, $max);
		$last = strrpos($temp, " ");
		$temp = substr($temp, 0, $last);
		$temp = preg_replace("/([^\w])$/", "", $temp);
		return "$temp$symbol";
	}

	public function spellCheck($text, $action) {
		$dictionary = spellCheckLoadDictionary("words.txt");
		$text .= ' ';
		$newtext = " ";
		$offset = 0;

		while($offset < strlen($text)) {
			$result = preg_match('/[^\w]*([\w]+)[^\w]+/', $text, $matches, PREG_OFFSET_CAPTURE, $offset);
			$word = $matches[1][0];
			$offset = $matches[0][1] + strlen($matches[0][0]);

			if(!spellCheckWord($word, $dictionary)){
				$newtext .= "<$action>$word</$action>";
			} else {
				$newtext .= "$word";
			}
		}
		return rtrim($newtext);
	}

	private function spellCheckLoadDictionary($filename) {
		return explode("\r\n", file_get_contents($filename));
	}

	private function spellCheckWord($word, $dictionary){
		$top = sizeof($dictionary) - 1;
		$bot = 0;
		$word = strtolower($word);

		while($top >= $bot) {
			$p = floor(($top + $bot)/2);

			if($dictionary[$p] < $word)
				$bot = $p + 1;
			elseif($dictionary[$p] > $word)
				$top = $p - 1;
			else
				return TRUE;
		}

		return FALSE;
	}

	public function removeAccents($text) {
		$from = ["ç","æ","oe","á","é","í","ó","ú","à","è","ì","ò","ù","ä","ë","ï","ö","ü","ÿ","â","ê","î","ô","û","å","e","i","ø","u","Ç","Æ","OE","Á","É","Í","Ó","Ú","À","È","Ì","Ò","Ù","Ä","Ë","Ï","Ö","Ü","Ÿ","Â","Ê","Î","Ô","Û","Å","Ø"];

		$to = ["c","ae","oe","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","y","a","e","i","o","u","a","e","i","o","u","C","AE","OE","A","E","I","O","U","A","E","I","O","U","A","E","I","O","U","Y","A","E","I","O","U","A","O"];

		returnstr_replace($from, $to, $text);
	}

	public function shortenText($text, $size, $mark){
		$len=strlen($text);
		if($size >= $len)
			return$text;

		$a = substr($text, 0, $size / 2 - 1);
		$b = substr($text, $len - $size / 2 + 1, $size / 2 - 1);
		
		return $a . $mark . $b;
	}
}