<?php
function generateSnippet($id, $query) {
	
	$doc = new DomDocument();
	libxml_use_internal_errors(true);
	$doc->loadHTMLFile($id);
	libxml_use_internal_errors(false);

	$querylist=explode(" ",$query);
	$snippet =null;
	$position=0;

	$meta=$doc->getElementsByTagName('meta');
	$p=$doc->getElementsByTagName('p');
	$data=$doc->getElementsByTagName('*');

	foreach($meta as $element) {
		if (preg_match('/.*[dD]escription.*/' ,strtolower($element->getAttribute('name'))) || preg_match('/.*[dD]escription.*/' ,strtolower($element->getAttribute('property'))) ) {
			$sentence = $element->getAttribute('content');
			if (stripos($sentence,$query)) {
				if(strlen($sentence)>strlen($snippet)){
					$snippet = $sentence;
					$position=stripos($sentence,$query);			}
			}
		}
	}


	
	foreach($doc->getElementsByTagName('p') as $element) {
		$sentence = $element->textContent;
			if (stripos($sentence,$query)) {
					if(strlen($sentence)>strlen($snippet)){
					$snippet = $sentence;	
					$position=stripos($sentence,$query);		}
			}
			
		}



	if(!$snippet){
		foreach($meta as $element){
			if (preg_match('/.*[dD]escription.*/' ,strtolower($element->getAttribute('name'))) || preg_match('/.*[dD]escription.*/' ,strtolower($element->getAttribute('property'))) ) {
				$sentence = $element->getAttribute('content');
				$contain=true;
				foreach ($querylist as $ql) {
					if(!stripos($sentence, $ql) || preg_match('/[A-Za-z0-9]/' ,$sentence[stripos($sentence, $ql)-1] )|| preg_match('/[A-Za-z0-9]/' ,$sentence[stripos($sentence, $ql)+strlen($ql)] ) ){
						$contain=false;
						break;
					}else{
						$position=stripos($sentence,$ql);
					}
				}
				if($contain){
					$snippet=$sentence;
				}

			}
		}

		foreach($doc->getElementsByTagName('p') as $element) {
			$sentence = $element->textContent;
			$contain=true;
			foreach ($querylist as $ql) {
				if(!stripos($sentence, $ql) || preg_match('/[A-Za-z0-9]/' ,$sentence[stripos($sentence, $ql)-1] )|| preg_match('/[A-Za-z0-9]/' ,$sentence[stripos($sentence, $ql)+strlen($ql)] ) ){
					$contain=false;
					break;
					}else{
						$position=stripos($sentence,$ql);
					}
				}
			if ($contain) {
					if(strlen($sentence)>strlen($snippet)){
					$snippet = $sentence;		
						}
			}
			
		}


	}

	if(!$snippet){
		foreach($meta as $element){
			if (preg_match('/.*[dD]escription.*/' ,strtolower($element->getAttribute('name'))) || preg_match('/.*[dD]escription.*/' ,strtolower($element->getAttribute('property'))) ) {
				$sentence = $element->getAttribute('content');
				
				foreach ($querylist as $ql) {
					if(stripos($sentence, $ql) && preg_match('/[^A-Za-z0-9]/' ,$sentence[stripos($sentence, $ql)-1] )&& preg_match('/[^A-Za-z0-9]/' ,$sentence[stripos($sentence, $ql)+strlen($ql)] ) ){
						$position=stripos($sentence, $ql);
						$snippet=$sentence;
						break; 

					}
				}

			}		
		}

		foreach($doc->getElementsByTagName('p') as $element) {
			$sentence = $element->textContent;
			
			foreach ($querylist as $ql) {
				if(stripos($sentence, $ql) && preg_match('/[^A-Za-z0-9]/' ,$sentence[stripos($sentence, $ql)-1] )&& preg_match('/[^A-Za-z0-9]/' ,$sentence[stripos($sentence, $ql)+strlen($ql)] ) ){
						$position=stripos($sentence, $ql);
						$snippet=$sentence;
						break;
					
					}
				}
		
			
		}



	}

	if(!$snippet){
		foreach($doc->getElementsByTagName('a') as $element) {
			
			$sentence = strtolower($element->textContent);
			foreach($querylist as $q1){			
			if (stripos($sentence,$q1)) {
				$snippet = $sentence;
								
				
				}
			}
	}
}	
	
	$length=strlen($snippet);
	if($length>160){
		if($position<160-strlen($query)){
			$snippet=substr($snippet,0,159);
			$end=strripos($snippet, " ");
			if($end){
				$snippet=substr($snippet,0,$end);
			}
			
			return $snippet."...";
		}
		else{

			$tsnippet=substr($snippet, 0, $position);
			$start=strripos($tsnippet, ".");
			if(!$start) $start=strripos($tsnippet, ",");
			if(!$start) $start=strripos($tsnippet, " ");
			if($start){
				if($start+161>$length-1){
				$snippet=substr($snippet, $start+2, $length-1);
				return "...".$snippet;}
				else{
				$snippet=substr($snippet, $start+2, $start+161);
				return "...".$snippet."...";
				}
			}
			
			
		}
		
	}
	return $snippet;

}
?>