<?php

/**
 * 
 * Poi全文检索引擎
 * 
 * @package Better.Poi
 * @author  Guo Yimin <guoym@peptalk.cn>
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Fulltext
{
	private static $_instance = null;
	private $_server = '';
	
	private function __construct()
	{
		$this->_server = Better_Config::getAppConfig()->poi->fulltext->server;
	}
	
	public static function getInstance()
	{
		if (self::$_instance==null) {
			self::$_instance = new self();
		}	
		
		return self::$_instance;
	}
	
	public function updateItem($id, $type)
	{
		Better_Cache::remote()->set('kai_poi_'.$id, null);
		return Better_DAO_Poi_Fulltext::getInstance()->updateItem($id, $type);
	}
	
	/**
	 * 
	 * 从全文检索引擎查询数据
	 * 
	 * @param unknown_type $query
	 * @param unknown_type $type
	 * @param unknown_type $sort
	 * @param unknown_type $page
	 * @param unknown_type $size
	 * @return array
	 */
	public function search($query, $type, $sort, $page, $size)
	{
		$result = array(
			'rows' => array(),
			'total' => 0
			);
		$size || $size = BETTER_PAGE_SIZE;
		$page>0 ? $page = $page - 1: $page = 0;
		$start = $page*$size;
		
		$url = $this->_server.
			'?q='.urlencode($query.' category:'.$type).
			'&start='.$start.
			'&rows='.$size.
			'&sort='.urlencode($sort).
			'&wt=json';
		$client = new Zend_Http_Client($url);
		$client->request(Zend_Http_Client::GET);
		$html = $client->getLastResponse()->getBody();
		
		if ($html) {
			$jsonResult = json_decode($html);
			$resultDocs = $jsonResult->{'response'};
			$totalFound = (int)$resultDocs->{'numFound'};
			$docs = $resultDocs->{'docs'};
			
			if ($totalFound>0 && is_array($docs)) {
				foreach ($docs as $doc) {
					$result['rows'][] = array(
						'itemid' => $doc->{'itemid'}[0],
						'author' => $doc->{'author'}[0],
						'uid' => $doc->{'uid'}[0],
						'subject' => $doc->{'subject'}[0],
						'content' => $doc->{'content'}[0],
						'postdate' => $doc->{'postdate'}[0],
						'lastreplies' => $doc->{'lastreplies'}[0],
						'hits' => (int)$doc->{'hits'}[0],
						'replies' => (int)$doc->{'replies'}[0],
						'footprint' => (int)$doc->{'footprint'}[0],
						'pfrom' => $doc->{'pfrom'}[0],
						'uploadtype' => $doc->{'uploadtype'}[0],
						'ifconvert' => 1
						);
				}
			}
		}
				
		return $result;
	}
}