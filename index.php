<?php
//VIA: http://bitsignals.com/2007/08/24/como-chequear-nuestro-pagerank-en-php/
class GooglePageRank {

	var $_GOOGLE_MAGIC = 0xE6359A60;
	var $_url = '';
	var $_checksum = '';

	function GooglePageRank($url)
	{
		$this->_url = $url;
	}

	function _strToNum($Str, $Check, $Magic)
	{
		$Int32Unit = 4294967296;

		$length = strlen($Str);
		for ($i = 0; $i < $length; $i++) {
			$Check *= $Magic;    

			if ($Check >= $Int32Unit) {
				$Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
				$Check = ($Check < -2147483647) ? ($Check + $Int32Unit) : $Check;
			}
			$Check += ord($Str{$i});
		}
		return $Check;
	}

	function _hashURL($String)
	{
		$Check1 = $this->_strToNum($String, 0x1505, 0x21);
		$Check2 = $this->_strToNum($String, 0, 0x1003F);

		$Check1 >>= 2;
		$Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
		$Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
		$Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);   

		$T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
		$T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );

		return ($T1 | $T2);
	}

	function checksum()
	{
		if($this->_checksum != '') return $this->_checksum;

		$Hashnum = $this->_hashURL($this->_url);

		$CheckByte = 0;
		$Flag = 0;

		$HashStr = sprintf('%u', $Hashnum) ;
		$length = strlen($HashStr);

		for ($i = $length - 1;  $i >= 0;  $i --) {
			$Re = $HashStr{$i};
			if (1 == ($Flag % 2)) {
				$Re += $Re;
				$Re = (int)($Re / 10) + ($Re % 10);
			}
			$CheckByte += $Re;
			$Flag ++;
		}

		$CheckByte %= 10;
		if (0 !== $CheckByte) {
			$CheckByte = 10 - $CheckByte;
			if (1 === ($Flag%2) ) {
				if (1 === ($CheckByte % 2)) {
					$CheckByte += 9;
				}
				$CheckByte >>= 1;
			}
		}

		$this->_checksum = '7'.$CheckByte.$HashStr;
		return $this->_checksum;
	}

	function pageRankUrl($dcchosen)
	{
		return $dcchosen . '/search?client=navclient-auto&features=Rank:&q=info:'.$this->_url.'&ch='.$this->checksum();
	}

	function getPageRank($dcchosen)
	{
		$fh = @fopen($this->pageRankUrl($dcchosen), "r");
		if($fh)
		{
			$contenido = '';
			while (!feof($fh)) {
			  $contenido .= fread($fh, 8192);
			}
			fclose($fh);
			ltrim($contenido);
			rtrim($contenido);
			$contenido=str_replace("Rank_1:1:","",$contenido);
			return $contenido;
		}
		return -1;
	}

}
$url=get_url();
$dc = "http://toolbarqueries.google.com";
$gpr =& new GooglePageRank($url);
$pagerank = $gpr->getPageRank($dc);
echo 'El pagerank de '.$url.'  - PR: '.$pagerank;

function get_url(){  
//Pagina web que visitamos
  if (isset($_GET['url'])){
	$url=$_GET['url'];
	if (substr($url,0,7)!='http://'){
		$url='http://'.$url;
	}
  }else{
	$url='http://smultron.es';//URL por defecto en el caso de que no nos lo pasen por parametro get
  }
return urldecode($url);
}//end get_url
?>
