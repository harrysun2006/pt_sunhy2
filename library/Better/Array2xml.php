<?php
/*
 * source: http://snipplr.com/view/3491/convert-php-array-to-xml-or-simple-xml-object-if-you-wish/
 * edited by panzy@peptalk.cn to support numeric array.
 */
class Better_Array2xml
{
  /**
   * The main function for converting to an XML document.
   * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
   *
   * @param array $data
   * @param string $rootNodeName - what you want the root node to be - defaultsto data.
   * @param SimpleXMLElement $xml - parent node, should only be used recursively
   * @return string XML
   */
  public static function toXml($data, $rootNodeName = 'data', $xml=null)
  {
    // turn off compatibility mode as simple xml throws a wobbly if you don't.
    if (ini_get('zend.ze1_compatibility_mode') == 1)
    {
      ini_set ('zend.ze1_compatibility_mode', 0);
    }

    if ($xml == null)
    {
      $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
    }

    // loop through the data passed in.
    foreach($data as $key => $value)
    {
      if (is_numeric($key))
      {
        $key = 'item';//$rootNodeName;
      }

      // replace anything not alpha numeric
      $key = preg_replace('/[^a-z]/i', '', $key);

      // if there is another array found recrusively call this function
      if (is_array($value))
      {
        if(0 && is_numeric(array_pop(array_keys($value))))
          $parent = $xml;
        else
          $parent = $xml->addChild($key);
        // recrusive call.
        Better_Array2xml::toXml($value, $key, $parent);
      }
      else 
      {
        // add single node.
        $value = htmlspecialchars($value);
        $xml->addChild($key,$value);
      }
    }
    // pass back as string. or simple xml object if you want!
    return $xml->asXML();
  }
}


