<?php

/**
 * Rss 处理类
 * @author yanglei
 *
 */
class Better_Rss {
	
	/**
	 * 根据客户提供的URL解析，返回一个数组
	 * @param String $rss_url
	 * @return array
	 */
	public function parseRss($rss_url){
	// 取得最新的 Slashdot 头条新闻
	try {
   	 	$slashdotRss = Zend_Feed::import($rss_url);
	} catch (Zend_Feed_Exception $e) {
   	 	// feed 导入失败
   		echo "Exception caught importing feed: {$e->getMessage()}\n";
    	exit;
	}

	// 初始化保存 channel 数据的数组
	$channel = array(
    	'title'       => $slashdotRss->title(),
   	 	'link'        => $slashdotRss->link(),
   	 	'description' => $slashdotRss->description(),
   		'items'       => array()
    );

	// 循环获得channel的item并存储到相关数组中
	foreach ($slashdotRss as $item) {
   		$channel['items'][] = array(
        	'title'       => $item->title(),
        	'link'        => $item->link(),
        	'description' => $item->description()
       	 	);
		}
		
	return $channel;
		
	}
	
	
	/**
	 * 根据数组参数创建一个xml格式的feed,数组格式见资料
	 * @param array $feedArray
	 */
	public function createFeed($feedArray){
		
	//导入数组
    $feed = Zend_Feed::importArray($feedArray, 'rss');
    
    //产生一个xml的字符串
    $rssFeed = $feed->saveXML();
    
    echo $rssFeed;
    
    return $rssFeed;
  }
  
  
  /**
   * 解析数组转化为符合rss格式的数组
   * @param  array $blogArray (count, rows[], data[])
   */
  public function generateRss($blogArray, $userInfo=array())
  {

  	$feedArray=array();
  	$feedArray['title']=$userInfo['nickname'].'在开开的消息';
  	$feedArray['link']=BETTER_BASE_URL.'/'.$userInfo['username'];
  	$feedArray['charset']="UTF-8";
  	$feedArray['entries']=array();
  	$feedArray['generator'] = 'k.ai';
  	
  	//取得内容
  	$blogs=$blogArray['rows'];
  	foreach($blogs as $item){
  		$itemArray=array();
  		$itemArray['title']=$item['message'];;
  		$itemArray['link']=BETTER_BASE_URL.'/'.$item['username'];
  		$itemArray['description']=$item['message'];
  		
  		$feedArray['entries'][]=$itemArray;
  	}
  	
  	return $this->createFeed($feedArray);
  	
  }
	
}

?>