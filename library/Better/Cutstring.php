<?php

/**
 * 长字符串切割处理
 *
 */

class Better_Cutstring
{
	
	public static function mystrcut($string,$length,$etc='...'){   
         $result= '';
         $string = html_entity_decode(trim(strip_tags($string)),ENT_QUOTES,'UTF-8');     
         $strlen = strlen($string);   

         for($i=0; (($i<$strlen)&& ($length> 0));$i++){   
             $number=strpos(str_pad(decbin(ord(substr($string,$i,1))), 8, '0', STR_PAD_LEFT), '0');
             if($number){   
                if($length<1.0) {   
                    break;   
                }   
                  $result .= substr($string, $i, $number);   
                  $length -= 1.0;   
                  $i+= $number-1;   
            }else{
                $result.= substr($string, $i, 1);   
                $length-= 0.5;
            }   
         }   

         $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');   

         if($i<$strlen){   
            $result.= $etc;   
         }   
        return   $result;   
    }
}

?>