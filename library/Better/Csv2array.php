<?php

/**
 * Csv到数组的转换
 * 
 * @package Better
 * @author yangl <yangl@peptalk.cn>
 *
 */
class Better_Csv2array
{

  /**
   * 把csv文件解析为一个数组返回
   *
   * @param string $file 要解析的csv文件路径
   * @param char $delimiter csv文件里的内容分隔符 默认为,
   * @return array
   */
  public static function csvtoarray($file, $delimiter = ',')
  {
   $result = array();
   $size = filesize($file) + 1;
   $file = fopen($file, 'r');
 
   while (!feof($file))
   {
   	$line = fgets($file);
  	$row = explode($delimiter, $line);
    $result[] = $row;
   }
  
   fclose($file);

   return $result;
  }
  
  
}