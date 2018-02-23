<?php
bcscale(20);

class matematica{
	var $fix = 0;
	var $exactitud = 20;
	var $usaFraccion = false;

    function usaFraccion($usa){
    	$this->usaFraccion = (bool) $usa;
    }

    function fix($fix = false){
    	if ($fix === false)
    		return $this->fix;

    	if (is_int($fix)){
			$this->fix = (int) $fix;
		}
		
    	return $this;
    }

    function rtrim($n){
    	if (is_array($n)){
			$n[0] = $this->rtrim($n[0]);
			$n[1] = $this->rtrim($n[1]);
			return $n;
		}

    	if (strpos($n, ".") !== false){
			$n = rtrim(rtrim(rtrim($n), "0"), ".");
		}

		return $n;
    }

    function salida($n){
    	if ($this->usaFraccion !== true)
    		$n = $this->convertirNumero($n);

    	if ($this->fix === 0)
    		return $this->rtrim($n);

   		return $this->bcround($this->convertirNumero($n), $this->fix);
    }

    function bcround($strval, $precision = 0){
    	$strval = trim($strval);
		if (false !== ($pos = strpos($strval, '.')) && (strlen($strval) - $pos - 1) > $precision) {
			$zeros = str_repeat("0", $precision);
			return bcadd($strval, "0.{$zeros}5", $precision);
		}else
			return $strval;
	}

    function esFraccion($n){
        if (!is_string($n) && (is_int($n) || is_float($n)))
            return false;

        if (substr_count($n, "/") == 1)
            return true;

        return false;
    }

    function formato($n){
        if (is_array($n))
            if (isset($n[0]) && isset($n[1])){
            	if ($n[1] < 0){
	                $n[0] = bcmul($n[0], -1, $this->exactitud);
	                $n[1] = bcmul($n[1], -1, $this->exactitud);
	            }
                return array($n[0], $n[1]);
            }

        if ($this->esFraccion($n)){
            $n = explode("/", $n);

            if ($n[1] < 0){
                $n[0] = bcmul($n[0], -1, $this->exactitud);
                $n[1] = bcmul($n[1], -1, $this->exactitud);
            }
            return array($n[0], $n[1]);
        }

        return array($n, 1);
    }
	
	function o($n1 = 0, $o = "+", $n2 = 0, $fraccion = true){
		return $this->operacion($n1, $o, $n2, $fraccion);
	}
	
    function operacion($n1 = 0, $o = "+", $n2 = 0, $fraccion = null){
    	if (is_null($fraccion)){
    		$fraccion = $this->usaFraccion;
    	}
    	
		$o = trim($o);
    	
    	if ($fraccion){
    		$n1 = str_replace(',', '.', $n1);
    		$n2 = str_replace(',', '.', $n2);
    		switch ($o){
	            case "+":
	            	$r = bcadd($n1, $n2, $this->exactitud);
	                break;
	
	            case "-":
	            	$r = bcsub($n1, $n2, $this->exactitud);
	                break;
	
	            case "*":
	            	$r = bcmul($n1, $n2, $this->exactitud);
	                break;
	
	            case "/":
	            	$r = bcdiv($n1, $n2, $this->exactitud);
	                break;
	                
	            case "<":
					$r = bccomp($n1, $n2, $this->exactitud) === -1;
	                break;
	                
	            case "<=":
					$r = bccomp($n1, $n2, $this->exactitud) <= 0;
	                break;
	                
	            case ">":
	                $r = bccomp($n1, $n2, $this->exactitud) === 1;
	                break;
	                
	            case ">=":
	                $r = bccomp($n1, $n2, $this->exactitud) >= 0;
	            	$r = $r;
	                break;
	                
	            case "=":
	                $r = bccomp($n1, $n2, $this->exactitud) === 0;
	                break;
	
	            case "%":
	            	$r = bcmod($n1, $n2, $this->exactitud);
	                break;
	
	            case "^":
	            	$r = bcpow($n1, $n2, $this->exactitud);
	            	break;
	
	            case "raiz2":
	            	$r = bcsqrt($n1, $this->exactitud);
	            	break;
	
			  	case "raiz":
		    		$r = bcpow($n1, bcdiv(1, $n1, $this->exactitud), $this->exactitud);
	            	break;
	
	            default:
	            	return false;
	        }
	        
	        return $r;
    	}else{
	    	$n1 = $this->formato(trim($n1));
	        $n2 = $this->formato(trim($n2));
	        
	        switch ($o){
	            case "+":
	                $r = array((bcadd(bcmul($n1[0], $n2[1], $this->exactitud), bcmul($n1[1], $n2[0], $this->exactitud), $this->exactitud)), bcmul($n1[1], $n2[1], $this->exactitud));
	                break;
	
	            case "-":
					$r = array(bcsub(bcmul($n1[0], $n2[1], $this->exactitud), bcmul($n1[1], $n2[0], $this->exactitud), $this->exactitud), bcmul($n1[1], $n2[1], $this->exactitud));
	                break;
	
	            case "*":
					$r = array(bcmul($n1[0], $n2[0], $this->exactitud), bcmul($n1[1], $n2[1], $this->exactitud));
	                break;
	
	            case "/":
					$r = array(bcmul($n1[0], $n2[1], $this->exactitud), bcmul($n1[1], $n2[0], $this->exactitud));
	                break;
	                
	            case "<":
					$r = bccomp($this->convertirNumero($n1), $this->convertirNumero($n2), $this->exactitud);
	            	$r = $r === -1;
	                break;
	                
	            case "<=":
					$r = bccomp($this->convertirNumero($n1), $this->convertirNumero($n2), $this->exactitud);
	            	$r = $r <= 0;
	                break;
	                
	            case ">":
	                $r = bccomp($this->convertirNumero($n1), $this->convertirNumero($n2), $this->exactitud);
	            	$r = $r === 1;
	                break;
	                
	            case ">=":
	                $r = bccomp($this->convertirNumero($n1), $this->convertirNumero($n2), $this->exactitud);
	            	$r = $r >= 0;
	                break;
	                
	            case "=":
	                $r = bccomp($this->convertirNumero($n1), $this->convertirNumero($n2), $this->exactitud);
	            	$r = $r === 0;
	                break;
	
	            case "%":
	            	$r = array(bcmod($n1[0], $n2[0], $this->exactitud), 1);
	                break;
	
	            case "^":
		            $r = array(bcpow($n1[0], $n2[0], $this->exactitud), bcpow($n1[1], $n2[0], $this->exactitud));
	            	break;
	
	            case "raiz2":
		    		$r = array(bcsqrt($n1[0], $this->exactitud), 1);
	            	break;
	
			  	case "raiz":
		    		$r = array(bcpow($n1[0], bcdiv(1, $n1[0], $this->exactitud), $this->exactitud), 1);
	            	break;
	
	            default:
	            	return false;
	        }
	        
	        if (is_bool($r))
				return $r;
				
			$salida = $this->salida($this->convertirCadena($this->resolverFraccion($this->formato($r))));
			
			if ($fraccion === true){
				return $salida;
			}
			
			return $this->convertirNumero($salida);
        }
    }

    function convertirNumero($n){
    	if (!is_array($n))
			$n = $this->formato($n);
			
   		return $n[1] == 1 ? $n[0] : bcdiv($n[0], $n[1]);
    }

    function convertirCadena($n){
        if (is_array($n))
            if (isset($n[0]) && isset($n[1]))
            	if ($n[1] == 1)
                	return $n[0];
            	else
            		return $n[0] . "/" . $n[1];

        if (is_string($n) || is_int($n) || is_float($n))
            return $n;

		return false;
    }

    function resolverFraccion($n){
        if (!is_array($n))
        	return false;

		if (!isset($n[0]) || !isset($n[1]))
       		return false;
		
		if ($n[1] == 0)
			return false;
		
		if (is_int($n[0] / $n[1]))
       		return bcdiv($n[0], $n[1], $this->exactitud);

 		$divisores = array(2,3,5);
		$p = true;
        while($p){
        	$pp = false;
        	foreach($divisores as $d){
        		if (bcmod($n[0], $d) == "0" && bcmod($n[1], $d) == "0"){

					$n[0] = bcdiv($n[0], $d, $this->exactitud);
					$n[1] = bcdiv($n[1], $d, $this->exactitud);
					$pp = true;
				}
        	}
        	if ($pp == false)
        		$p = false;
        }

        return $this->salida($n);
    }

    function exponencial($n, $e = 0){
    	return $this->operacion($n, '^', $e);
    }

    function factorial($n){
		$f = 1;

		$fix = $this->fix;
   		$this->fix = 0;

		for($i = $n; $i >= 1; $i--)
			$f = $this->operacion($f, '*', $i);

   		$this->fix = $fix;

		return $this->salida($f);
	}

	function combinatoria($n, $r){
		$fix = $this->fix;
   		$this->fix = 0;

		$salida = $this->salida($this->factorial($n) / ($this->operacion($this->factorial($r), "*", $this->factorial($this->operacion($n, "-", $r)))));
		$this->fix = $fix;

		return $salida;
	}
}