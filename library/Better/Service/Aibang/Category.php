<?php

/**
 * 爱帮的poi分类
 * 用来处理爱帮分类与我们分类的对应关系
 * 
 * @package Better.Service.Aibang
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Service_Aibang_Category
{
	
	public static function trans($cat)
	{
		$our = 0;
		
		$tmp = explode(':', $cat);
		$cat = $tmp[0];
		
		switch ($cat) {
			case '饭店':
				$our = 1;
				break;
			case '酒店':			
			case '宾馆':
			case '住宿':
			case '宾馆酒店':
			case 'hotel':
			case '住的地方':
				$our = 11;
				break;
				
			case '医院':
			case '看病':
				$our = 4;
				break;
				
			case '学校':
				$our = 6;
				break;
			
			case '电影院':
			case '电影':
			case '影城':
			case '影院':
			case '影剧院':
				$our = 3;
				break;
				
			case '餐馆':
			case '餐厅':
			case '酒楼':
			case '酒馆':
			case '饭馆':
			case '菜馆':
			case '酒家':
			case '吃饭':
				$our = 1;
				break;
				
			case '蛋糕坊':
			case '蛋糕房':
			case '蛋糕店':
			case '蛋糕':
				$our = 1;
				break;
				
			case '冷饮店':
			case '冷饮':
				$our = 1;
				break;
				
			case '茶馆':
			case '茶楼':
			case '喝茶':
				$our = 1;
				break;
				
			case '商场':
			case '商城':
			case '购物':
			case '商厦':
				$our = 2;
				break;
				
			case '超市':
				break;
				
			case 'ktv':
			case '唱歌':
			case 'k歌':
			case '卡拉ok':
				$our = 3;
				break;
				
			case '公园':
				$our = 9;
				break;
				
			case '酒吧':
			case 'bar':
			case '酒馆':
			case '酒吧酒馆':
			case '酒吧/酒馆':		
				$our = 8;
				break;
				
			case '咖啡馆':
			case '咖啡':
			case '咖啡厅':
			case 'cafe':
			case '咖啡吧':
			case '咖啡店':
			case '咖啡屋':
				$our = 1;
				break;
				
			case '体育':
			case '体育健身':
			case '健身':
				$our = 5;
				break;
				
			case 'atm':
			case '提款机':
			case '银行/提款机':
				$our = 4;
				break;
			
			case '银行':
				$our = 4;
				break;
				
			case '家政':
			case '家政服务':
			case '小时工':
				$our = 4;
				break;
				
			case '小区':
				$our = 4;
				break;
				
			case '加油站':
			case '加油':
				$our = 11;
				break;
				
			case '停车场':
			case '停车':
			case '汽车':
				$our = 11;
				break;

			case '游乐园':
			case '游乐场':
				$our = 9;
				break;
				
			case '景点':
				$our = 9;
				break;
				
			case '洗浴按摩':
			case '按摩':
			case '洗浴':
			case '按摩房':
			case '按摩院':
				$our = 5;
				break;
				
			case '美容':
			case '美发':
			case '美容美发':
			case '美容院':
			case '美发厅':
			case '美容店':
				$our = 5;
				break;
				
			case '娱乐城':
			case '娱乐场':
				$our = 8;
				break;
				
			case '药店':
			case '药房':
				$our = 4;
				break;
				
			case '夜总会':
			case '迪厅':
			case '舞厅':
				$our = 8;
				break;
				
			case '家电商场':
			case '家用电器':
			case '家电':
				$our = 2;
				break;
				
			case '家居建材':
			case '家居':
			case '建材':
				$our = 2;
				break;
				
			case '书店':
				$our = 6;
				break;
				
			case '网吧':
				$our = 3;
				break;
				
			case '展览馆':
				$our = 3;
				break;
				
			case '博物馆':
				$our = 3;
				break;
				
			case '电子游戏厅':
			case '电子游艺厅':
			case '游戏厅':
			case '游艺厅':
			case '游戏':
				$our = 3;
				break;
				
			case '剧场':
			case '剧院':
				$our = 3;
				break;
				
			case '驾校':
			case '驾驶学校':
			case '学车':
				$our = 4;
				break;
				
			case '摄影':
			case '照相':
			case '照相馆':
				$our = 7;
				break;
				
			case '渡假村':
			case '渡假':
			case '度假村':
			case '度假':
				$our = 11;
				break;
				
			case '房屋中介':
				$our = 4;
				break;
			default:
				$our = 7;
				break;
				
		}

		return $our;		
	}
}