<?php

/*
********************************************************************************************************* 
* PHP Class 
* Copyright (c) 2011 Giulio Calzolari <giuliocalzo@gmail.com> All Rights Reserved.  
* 
* 
*   This program is free software; you can redistribute it and/or modify 
*   it under the terms of the GNU General Public License as published by 
*   the Free Software Foundation; either version 2 of the License, or 
*   (at your option) any later version. 
* 
*   This program is distributed in the hope that it will be useful, 
*   but WITHOUT ANY WARRANTY; without even the implied warranty of 
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
*   GNU General Public License for more details. 
* 
*   You should have received a copy of the GNU General Public License 
*   along with this program; if not, write to the Free Software 
*   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
* 
********************************************************************************************************* 
* Original author name cannot be removed.   
* Authors:    Giulio Calzolari <giuliocalzo@gmail.com> 
********************************************************************************************************* 
* 
*/

class Mac {
    private $mac = '';

    function __construct($mac = '') {
        $m = trim($mac);
		if ($mac === ''){
			//$this->mac = $this->GetMacAddr();
			return;
		}

        if(strpos($m, '-')) {
            $this->mac = str_replace('-', '', $m);
        } elseif(strpos($m, '.')) {
            $this->mac = str_replace('.', '', $m);
        } elseif(strpos($m, ':')) {
            $this->mac = str_replace(':', '', $m);
        } else {
            //$this->error('No valid MAC ADDRESS : ' . $m);
        }
    }


    function output($sep = ':', $digit = 2, $output = 'normal') {
        $out = "";
        for($i = 1; $i < strlen($this->mac) + 1; $i++) {
            $out .= $this->mac[$i - 1];
            if(@($i % $digit) == 0)
                $out .= $sep;
        }

        switch($output) {
            case 'normal':
                return rtrim($out, $sep);
                break;
            case 'upper':
                return strtoupper(rtrim($out, $sep));
                break;
            case 'lower':
                return strtolower(rtrim($out, $sep));
                break;
            default:
                $this->error('No valid output option : ' . $out);
        }
    }


    public function GetMacAddr($ifname = 'eth0') {
        switch(PHP_OS) {
            case 'FreeBSD':
                $command_name = "/sbin/ifconfig $ifname ";
                $condition = "/ether [0-9A-F:]*/i";
                break;
            case 'Windows' || 'WINNT':
                $command_name = "ipconfig /all ";
                $condition = "/Direcci.n F.sica.+[0-9A-F:]*|Physical Addess [0-9A-F:]*|Indirizzo Fisico [0-9A-F:]*/i";
                break;

            default:
                $command_name = "/sbin/ifconfig $ifname | grep HWadd";
                $condition = "/HWaddr (\S+)/i";
                break;
        }
        
        $ifip = "";
        exec($command_name, $command_result);
        $ifmac = implode($command_result, "\n");
		
		$macs = array();
		
		preg_match_all($condition, $ifmac, $match);
		foreach($match[0] as $matchele){
			preg_match('/([0-9A-F\-]+)$/', $matchele, $m);
			
			if (strlen($m[0]) == 17 && substr($m[0], 0, 2) != '00')
				$macs[] = $m[0];
		}
		
		return array_unique($macs);
    }


    function error($msg) {
        trigger_error("[Class MacAddress]: " . $msg, E_USER_ERROR);
        echo "[Class MacAddress]: " . $msg . "\n";
    }
}

//$mac = new MacAddress();
//var_dump($mac->GetMacAddr());