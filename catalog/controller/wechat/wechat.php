<?php
class ControllerWechatWechat extends Controller
{
    private $textTpl = '';
    private $imgTextTplStart = '';
    private $imgTextTplItem = '';
    private $imgTextTplTail = '';
    private $orderstatusPic = array();
    private $appId = 'wx6262d90f286743c3';
    private $appSecret = 'd641490c7db8fe02238ed38e0579d314 ';
    private $storeId = 0;
    private $token;
 /*
  * index方法是获取acces_stoken 来创建自定义菜单
  * 	获取token值 以便添加自定义菜单
  * 	$this->getToken(); 根据appid和appsecret去获取access_token(注意 这个access_token有存在时间)
  * 	$this->createMenu(); 创建一个json菜单 以便发给微信服务器 创建自己需要的按钮(注意 发送完毕以后需要24小时以内才能显示 并且请求时必须是https)
  * 	$this->httpRequest(); 模拟一个提交请求 
  */     
    public function index() {

        if( isset($_GET["echostr"]) ){
            if($this->checkSignature()){
                die($_GET["echostr"]);
            }
        }

        $this->load->model('wechat/wechat');

        $this->getToken();

        var_dump($HTTP_RAW_POST_DATA);exit();



//			$access_token = $this->getToken();
//    		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
//    		$query = $this->createMenu();
//
//    		//$file = DIR_LOGS .'weixin.txt';
//    		//file_put_contents($file,'');
//    		$res = $this->httpRequest($url, $query, 'post');
//    		//echo json_encode($res);
    }
    
    private function getToken() {

        $currentTimeStamp = time();

        $result = $this->model_wechat_wechat->getToken();

        if(empty($result)) {

            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . APP_ID . '&secret=' . APP_SECRET;

            $response = $this->httpRequest($url);

            if(is_object($response) && property_exists($response,'access_token') && !empty($response->access_token)) {

                $insertResult = $this->model_wechat_wechat->insertToken($currentTimeStamp,$response->access_token);

                if(!$insertResult) {

                    die('Error: There is something wrong when inserting token.');

                }

            } else {

                die('Error: There is something wrong when getting token.');

            }

        } elseif(!empty($result['timestamp']) && !empty($result['token']) && is_numeric($result['timestamp']) && ($currentTimeStamp - (int)$result['timestamp'] < 7000)) {

            $this->token = $result['token'];

        } else {

            if(!empty($result['id'])) {

                $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . APP_ID . '&secret=' . APP_SECRET;

                $response = $this->httpRequest($url);

                if(is_object($response) && property_exists($response,'access_token') && !empty($response->access_token)) {

                    $updateResult = $this->model_wechat_wechat->updateToken($result['id'], $currentTimeStamp, $response->access_token);

                    if(!$updateResult) {

                        die('Error: There is something wrong when updating token.');

                    }

                } else {

                    die('Error: There is something wrong when getting token.');

                }

            } else {

                die('Error: There is something wrong when updating token.');

            }

        }

    }
    
    private function httpRequest($url, $query = '', $type = 'get')
    {
    	//global $log;
    	$log = new Log('weixin.txt');
    	$arr_query = array();
    	$options = array(    			
    			CURLOPT_URL => $url . (strpos($url, '?') === false ? '?' : '') . $query,
    			CURLOPT_HEADER => 0,
    			CURLOPT_RETURNTRANSFER => 1,
    	);

    	if ($type == 'post') {
    		$options[CURLOPT_URL] = $url;
    		$options[CURLOPT_POST] = 1;
    		$options[CURLOPT_POSTFIELDS] = $query;
    	}   	
    	$log->write('REQ:' . var_export($options, true));
    	$ch = curl_init();
    	curl_setopt_array($ch, $options);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	if ($type == 'get'){ 
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    	}else{
    		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,true); ;
    		curl_setopt($ch,CURLOPT_CAINFO,DIR_SYSTEM .'library/mailchimp-mandrill/cacert.pem');
    	}
    	$result = curl_exec($ch);
    	curl_close($ch);
    	$log->write('REP:' . var_export($result, true));
    	$result = json_decode($result);
    	return $result;
    }
    
    public function createMenu()
    {
    	//        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=T2sG5IqdNDiT-cDoKBzOEePrwxr0ZiWVRtdTVRsct9rVGGgg8IWf_JESKSB8Wsxi4I_blrRSWhn_ipwCk6wibOv9QtgeE_GHNtoEu4tNkKslQvFwdQOqtfopfgh8_hPdffwsHF5FyHzNhHst0qLCJg';
    	//     	$token = $this->request->get['access_token'];
    	//     	$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $token;
    
    	$sql = '{
     "button":[
     {
          "type":"click",
          "name":"click测试1",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "type":"click",
           "name":"click测试2",
           "key":"V1001_TODAY_SINGER"
      },
      {
           "name":"菜单",
           "sub_button":[
           {
               "type":"view",
               "name":"view测试1",
               "url":"http://www.soso.com/"
            },
            {
               "type":"view",
               "name":"view测试2",
               "url":"http://v.qq.com/"
            },
            {
               "type":"click",
               "name":"view测试3",
               "key":"V1001_GOOD"
            }]
       }]
 }';
    	return $sql;
    }

    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = 'reshday';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
/*
 * 微信公众平台填的url方法 response
 * 
 */
    public function response()
    {
        if( isset($_GET["echostr"]) ){
            $echoStr = $_GET["echostr"];
            if($this->checkSignature()){
                echo $echoStr;
                exit;
            }
        }   
        
        $this->initTpl();//发送数据的格式初始化
        $this->initPic();
        $log = new Log('weixin.txt');
        $log->write('weixin test : this is the function of responseMsg');
        
        //$access_token = $this->getToken();
        $log->write('***access_token :'. $access_token .'***');
        //get post data, May be due to the different environments
        $postStr = '';
        if( isset($GLOBALS["HTTP_RAW_POST_DATA"]) ){
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        }
        //extract post data
        if (!empty($postStr)) {
            $this->load->model('webChat/order');
            $this->load->library('webChat/webChat');
            $this->load->model('weixin/emails');

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $this->storeId = $this->model_webChat_order->getStoreByWeChatId($toUsername);
            $time = date("Y-m-d H:i:s", time() + TIMEDIFFERENCE );
            $log->write('fromUsername : ' . $fromUsername);
            $log->write('toUsername : ' . $toUsername);
            $log->write('Event : ' . $postObj->Event);

            if (isset($postObj->EventKey)) {
                $log->write('eventKey : ' . $postObj->EventKey);
            } else {
                $log->write('eventKey is nothing : ');
            }
/*            if( isset($postObj->Event) && $postObj->Event == 'LOCATION' ){
                $msgType = "text";
                $log->write('LOCATION  Latitude is  : ' . $postObj->Latitude);
                $log->write('LOCATION  Longitude is  : ' . $postObj->Longitude);
                $log->write('LOCATION  Precision is  : ' . $postObj->Precision);
                $contentStr = "Latitude is  :  $postObj->Latitude" . " Longitude is  : $postObj->Longitude" . " Precision is  : $postObj->Precision";
                $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
                echo $resultStr;
                die();
            }*/
            $keyword = trim($postObj->Content);
            
            if($postObj->MsgType == 'voice'){
                $msgType = "text";
                $contentStr = '';

                $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );
                $this->load->model('webChat/message');
                $data = array(
                    'webChat_id'=> $fromUsername,
                    'public_id'=> $toUsername,
                    'store_id'=> $this->storeId,
                    'content'=> $postObj->Recognition,
                    'voice_media_id'=> $postObj->MediaId,
                    'mobile_number'=> $mobile,
                    'type'=>'voice'
                );

                $this->model_webChat_message->insertMsg( $data );

                /*$nickName = '';
                $webchat = new WebChat();
                $storeId = $this->model_webChat_order->getStoreByWeChatId($toUsername);
                $appId = $this->model_webChat_order->getStoreWebChatAppId($storeId);
                $appSecret = $this->model_webChat_order->getStoreWebChatAppSecret($storeId);
                $wechatUserInfo = $webchat->getWechatInfo($fromUsername, $appId, $appSecret );
                if( is_object($wechatUserInfo) && isset($wechatUserInfo->nickname)){
                    $nickName = $wechatUserInfo->nickname;
                }*/

                /*if(isset( $postObj->Recognition )){
                    $contentStr = $postObj->Recognition ;
                    $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
                }else{
                    $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
                }
                echo $resultStr;*/
                $log->write('voice $postObj : ' . var_export( $postObj, true) );
                //todo : add the voice function
                $this->voiceFunction( $postObj->Recognition, $fromUsername, $toUsername, $time, $msgType );

            }else if( isset($postObj->Event) && $postObj->Event == 'subscribe' ){
                $msgType = "text";
                $href = WEIXINHTTP . 'index.php?route=weixin/checkmobile&webChatId=' . $fromUsername . '&operationType=bbx4&publicId='.$toUsername;
                $bind = "<a href='" . $href . "' >绑定手机</a>";
//                 $contentStr = "亲，欢迎您关注 程途-公寓门锁管家，从今以后我们就是一家人了。在这里，" . $bind . " 之后，入住体验重新想象：
// ① 通过“授权自己/他人开门”，就可以用微信打开您的房间门锁啦；
// ② 点击下面“呼叫前台”就可以和我们免费通话；
// ③ 还可以直接通过下面的菜单进行预定哦；
// ④ 想看偶的房间？点击“百宝箱-我的主页”试试看；
// ⑤ ……
// 抱歉，想说的太多了！不如，您亲自玩玩？" . $postObj->EventKey;

                $nickName = '';
                /*
                $webchat = new WebChat();
                $storeId = $this->model_webChat_order->getStoreByWeChatId($toUsername);
                $appId = $this->model_webChat_order->getStoreWebChatAppId($storeId);
                $appSecret = $this->model_webChat_order->getStoreWebChatAppSecret($storeId);
                $wechatUserInfo = $webchat->getWechatInfo($fromUsername, $appId, $appSecret );
                if( is_object($wechatUserInfo) && isset($wechatUserInfo->nickname)){
                    $nickName = $wechatUserInfo->nickname;
                }*/

                //                 $contentStr = "您太有眼光了！
// 现在绑定手机就可以享受我们智慧与科技融合的服务体验啦！
// <a href='" . $href . "'>绑定手机</a>";
                $contentStr = "感谢您的关注！
                现在就可以享受我们智慧与科技融合的服务体验啦！\n 
                由于目前是测试版 您可以回复以下数字 查询相关的内容 !\n
                1.查看订阅爱日租的人数~		
                		";
    
                $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
                echo $resultStr;
                die();
            }else if( isset($postObj->Event) && $postObj->Event == 'SCAN' ){
                $msgType = "text";
                $contentStr = "扫描了二维码成功， 您已关注. EventKey:" . $postObj->EventKey;
                $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
                echo $resultStr;
                die();

            } else if ($postObj->MsgType == 'text') {
        		if($postObj->Content == '1'){
        			$msgType = "text";
        			$total = $this->model_weixin_emails->getEmails();
        			$contentStr = '目前已有:'.$total.'位客人关注了(爱日租) 谢谢您的关注!';
        			$log->write('*contentStr:'.$contentStr.'*');
        			$resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
        		}else{
        			$msgType = "text";
        			$contentStr = '抱歉 目前没有相应的指令 谢谢您的关注!';
        			$resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
        		}      	
        		echo $resultStr;
        		die();
            }
            else if (!empty($keyword)) {
                $msgType = "text";
                // 隐藏功能：获得该微信号 open id
                if( $keyword == 'myopenid'){


                    $contentStr = "微信公众账号open id 为：" . $fromUsername;
                    $log->write( $contentStr );
                    $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
                    echo $resultStr;
                    die();
                }

                $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );
                $this->load->model('webChat/message');
                $data = array(
                    'webChat_id'=> $fromUsername,
                    'public_id'=> $toUsername,
                    'store_id'=> $this->storeId,
                    'content'=> $keyword,
                    'mobile_number'=> $mobile,
                    'type'=>'word'
                );

                $this->model_webChat_message->insertMsg( $data );
                $this->wordFunction( $keyword, $fromUsername, $toUsername, $time, $msgType );
                // 判断最后一步 是否授权操作

            }
            else if (!empty($postObj->EventKey)) {
                $res = $this->recordWebChat( $fromUsername, $time, $postObj->EventKey );

                if( !$res ){
                    $msgType = 'text';
                    $contentStr = '测试-记录保存失败.';
                    $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    die();
                }

                $msgType = "text";

                $contentStr = '';

                if($postObj->EventKey == 'open'){

                    $this->openAction( $fromUsername, $toUsername, $time, $msgType );
                }else if( $postObj->EventKey == 'calling' ){

                    $this->callingAction( $fromUsername, $toUsername, $time, $msgType );
                }

                else if( $postObj->EventKey == 'recording' ){

                    //todo 查看开锁记录
                    $this->searchOpenRecord( $fromUsername, $toUsername, $time );
                    die();
                }else if($postObj->EventKey == 'bbx4'){
                    //todo 判定 该微信是否绑定
                    $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );
                    if( !empty($mobile) ){
                        $link = WEIXINHTTP . 'index.php?route=weixin/checkmobile&webChatId=' . $fromUsername . '&operationType=bbx4'. '&publicId=' . $toUsername;
                        $unBindLink = WEIXINHTTP . 'index.php?route=weixin/checkmobile/unbind&webChatId=' . $fromUsername . '&operationType=bbx4'. '&publicId=' . $toUsername;

                        $contentStr = "您已绑定手机" . $mobile . "，您可以：
<a href='" . $link . "' >重新绑定</a> 或 <a href='" . $unBindLink . "' >解除绑定</a>";
                    }else{
                        $link = WEIXINHTTP . 'index.php?route=weixin/checkmobile&webChatId=' . $fromUsername . '&operationType=bbx4'. '&publicId=' . $toUsername;

                        $contentStr = "如需使用，请先绑定您的手机号：
<a href='" . $link . "'>立刻绑定</a>";

                    }
                }

                else if( $postObj->EventKey == 'booking' ){

                    $this->bookingAction( $fromUsername, $toUsername, $time, $msgType );
                }
                else if( $postObj->EventKey == 'joke' ){
//                    $joke = $this->getRandJoke();
//                    $contentStr .= $joke;
                    $this->jokeAction( $fromUsername, $toUsername, $time, $msgType );
                }else if( $postObj->EventKey == 'newPublic' ){

                    $this->announceAction( $fromUsername, $toUsername, $time, $msgType );
                }else if( $postObj->EventKey == 'weiHome' ){
                    $this->homeAction( $fromUsername, $toUsername, $time, $msgType );
                }
                else if( $postObj->EventKey == 'authManager'){
                    //订单管理
                    $res = $this->checkInit( $fromUsername );
                    
                    if( $res['status'] == 'noMobile' ){

                        $link = WEIXINHTTP . 'index.php?route=weixin/checkmobile&operationType=open&webChatId=' . $fromUsername . '&publicId=' . $toUsername;
                        $linkCon = "<a href='" . $link . "'>立刻绑定</a>";
                        $contentStr = "如需使用，请先绑定您的手机号：
 ".$linkCon;

                    }else if( $res['status'] == 'noOrder' ){
                        $bookingLink = WEIXINHTTP . "index.php?route=weixin/room_type_list". '&publicId=' . $toUsername.'&webChatId=' . $fromUsername;
                        $contentStr = "对不起，您当前没有可打开的房间，因此无法进行权限管理。
现在就去 <a href='" . $bookingLink . "'>预订房间</a>";
                    }else{
                        $authMangerLink = WEIXINHTTP . 'index.php?route=weixin/auth/authList&webChatId=' . $fromUsername. '&publicId=' . $toUsername;
                        $contentStr = "请谨慎管理您的开门权限，不要随意允许他人打开您的房间。
<a href='" . $authMangerLink . "'>授权管理</a>";
                    }
                }
                else if( $postObj->EventKey == 'myOrder'){
                    //查看我的订单

                    $this->orderAction( $fromUsername, $toUsername, $time, $msgType );

                }
                else if( $postObj->EventKey == 'clean'){
                    $this->cleanAction( $fromUsername, $toUsername, $time, $msgType );
                }
                else if( $postObj->EventKey == 'checkOut'){
                    $this->checkOutAction( $fromUsername, $toUsername, $time, $msgType );
                }
                else if( $postObj->EventKey == 'lottery'){
                    //: 找到一个合理的抽奖活动
                    $this->lotteryAction( $fromUsername, $toUsername, $time, $msgType );
                }
                $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                die();
            }       				
        }else{
            echo "";
            exit;
        }
    }  
 
    private function returnImg( $fromUsername, $toUsername, $time, $msgType ){

        $tpl = "<xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>12345678</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
            <MediaId><![CDATA[media_id]]></MediaId>
            </Image>
            </xml>";
        $img = "<img src='http://weixin.imaginato.site4test.com/image/phone.jpg' style='width:200px; height:100px' />";
        $resultStr = sprintf( $tpl, $fromUsername, $toUsername, $img, $msgType );
        return $resultStr;
    }


    private function openLock( $webChatId, $toUsername, $time, $bindMobile ){


//        $orders = $this->model_webChat_order->getAuthedOrder( $webChatId );
        $orders = $this->model_webChat_order->getAuthedOrderDetails( null, $webChatId, null, null, $this->storeId );


        if(empty($orders)){
            $msgType = 'text';
//            $link = WEIXINHTTP . 'index.php?route=weixin/room_type_list'. '&publicId=' . $toUsername.'&webChatId=' . $webChatId;
//            $linkContent = "<a href='" . $link . "'>预定房间</a>";
//            $contentStr = "嗯...您当前没有订单哦
//现在就去 " .  $linkContent;
            $contentStr = "对不起，您还没有登记入住，可以联系前台为您办理后才可以用微信开门哦。";
            $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;

        }else if(count($orders) == 1){

            //在有订单的情况下，判断是否在开门时间(入住时间)，否则提示不能开锁的信息
            $newOrders = array();
            if( !empty($orders) ){
                foreach( $orders as $key => $order ){
                    $contactAta = date('Y-m-d',strtotime($order['contact_ata']));
                    $contactAtd = date('Y-m-d',strtotime($order['contact_atd']));
                    $today = date("Y-m-d");
                    if( $today >= $contactAta && $today <= $contactAtd ){
                        $newOrders[] = $order;
                    }
                }
            }
            if( !empty($newOrders) ){
                //todo 判断是否入住
                $checkInOrders = array();
                foreach( $newOrders as $newOrder ){
                    if($newOrder['status'] == '1'){
                        $checkInOrders[] = $newOrder;
                    }
                }
                if(!empty( $checkInOrders )){
                    if( count( $checkInOrders ) == 1 ){
                        //todo : 判断 开锁次数，如果开锁类型为次数， 并且 非入住人的情况下 ：
                        if( $checkInOrders[0]['lodgerOpenId'] != $webChatId ){
                            $this->load->model('webChat/auth');
                            $now = date('Y-m-d H:i:s', time() + TIMEDIFFERENCE );
                            $authInfo =$this->model_webChat_auth->getAuthByOrderDetailAndMobile( $checkInOrders[0]['order_detail_id'], $bindMobile, $now);
                            if( !empty($authInfo)){
                                if( $authInfo['auth_type'] == 'number' ){
                                    $historyOpenTimes = $this->model_webChat_order->getOpenNumber(  $checkInOrders[0]['order_detail_id'], $bindMobile, $authInfo['auth_ata'], $authInfo['auth_atd'] );
                                    if( $historyOpenTimes >= $authInfo['open_number']){
                                        $msgType = 'text';
                                        $contentStr = '您当前时间段的开门次数已经用尽..';
                                        $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
                                        echo $resultStr;
                                        die();
                                    }
                                }
                            }else{
                                $msgType = 'text';
                                $contentStr = "对不起，当前时间不在您被授权开门的时间之内。。";
                                $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
                                echo $resultStr;
                                die();
                            }

                        }

                        // 直接开锁
                        $order = $checkInOrders[0];
                        $room = $this->model_webChat_order->getRoomLockAddrByRoomId( $order['room_id'] );
                        $webChat = new webChat();
                        $res = $webChat->openLock( $room['lock_addr'] );

                        if($res){
                            // 记录 开锁操作
                            $phone = $this->model_webChat_order->checkWebChatBinded( $webChatId );
                            $authedPerson = $this->model_webChat_order->getOrderDetailAuthedPersonByPhone( $order['order_detail_id'], $phone );

                            $name = '';
                            if( !empty($authedPerson) ){
                                if( $authedPerson['mobile_number'] == $phone ){
                                    $name = $authedPerson['name'];
                                }else{
                                    $name = $authedPerson['lodger'];
                                }
                            }

                            $this->model_webChat_order->recordOpenLockOperation( $webChatId, $order['order_detail_id'], $time, $phone, $name );

                            $msgType = 'text';
                            $contentStr = "已成功开锁。请留意门锁状态，门锁维持可开启状态的时间仅有5秒钟。
程程趁现在讲个笑话吧——";

                            $joke = $this->getRandJoke();
                            $contentStr .= $joke;
                            $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                        }else{
                            // 返回错误信息
                            $msgType = 'text';
                            $contentStr = '网络异常,锁没有被打开..';
                            $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                        }
                    }else{

                        $msgType = 'text';
                        $link = WEIXINHTTP . 'index.php?route=weixin/order/locks&webChatId=' . $webChatId. '&publicId=' . $toUsername;

                        $linkContent = "您当前可打开多个房间。
请 <a href='" . $link . "'>选择房间</a>";
                        $contentStr = $linkContent;

                        $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                    }
                }else{
                    $msgType = 'text';
                    $callingUrl = WEIXINHTTP . 'index.php?route=weixin/order/callLandlord&webChatId='.$webChatId . '&storeId=' . $this->storeId. '&publicId=' . $toUsername;
                    $callingLink = "<a href='" . $callingUrl . "'>免费呼叫前台</a>";

                    $checkingUrl = WEIXINHTTP . 'index.php?route=weixin/order/locks&webChatId='.$webChatId. '&publicId=' . $toUsername;
                    $checkingLink = "<a href='" . $checkingUrl . "'>查看订单</a>";

                    $contentStr = "对不起，您暂未办理入住手续，目前还无法使用微信开房门哦。
您可以：
$callingLink 或 $checkingLink;";

                    $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }

            }else{
                $msgType = 'text';
//                $this->storeId = $this->model_webChat_order->getStoreByWeChatId( $toUsername );
                $callingUrl = WEIXINHTTP . 'index.php?route=weixin/order/callLandlord&webChatId='.$webChatId . '&storeId=' . $this->storeId. '&publicId=' . $toUsername;
                $callingLink = "<a href='" . $callingUrl . "'>免费呼叫前台</a>";

                $checkingUrl = WEIXINHTTP . 'index.php?route=weixin/order/locks&webChatId='.$webChatId. '&publicId=' . $toUsername;
                $checkingLink = "<a href='" . $checkingUrl . "'>查看订单</a>";
                $contentStr = "对不起，当前还未到入住时间，您无法打开房门。
您可以：
$callingLink 或 $checkingLink";
//                $contentStr = "对不起，您还没有登记入住，可以联系前台为您办理后才可以用微信开门哦。
//您可以：
//$callingLink 或 $checkingLink;";

                $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }
        }else{
            $msgType = 'text';
            $link = WEIXINHTTP . 'index.php?route=weixin/order/locks&webChatId=' . $webChatId. '&publicId=' . $toUsername;

            $linkContent = "您当前可打开多个房间。
请 <a href='" . $link . "'>选择房间</a>";
            $contentStr = $linkContent;

            $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }

    }

    private function callLandlord($caller, $called){
        require_once('system/library/webChat/webChat.php');
        $webChat = new WebChat();
        $res = $webChat->callLandlord($caller, $called, $this->storeId);
        return $res;
    }

    private function getUuid( $prefix = '' ){
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars,0,8) . '-';
        $uuid .= substr($chars,8,4) . '-';
        $uuid .= substr($chars,12,4) . '-';
        $uuid .= substr($chars,16,4) . '-';
        $uuid .= substr($chars,20,12);
        return $prefix . $uuid;
    }

    private function recordWebChat( $fromUsername, $time, $eventKey ){
        $this->load->model('webChat/order');
        $res = $this->model_webChat_order->operationRecord( $fromUsername, $time, $eventKey );
        return $res;
    }


    private function checkMobile($phone){
        $webChat = new WebChat();
        $verifiacation = $webChat->checkMobile( $phone );
        return $verifiacation;
    }

    private function sendMsg( $mobileNumber, $content ){
        $webChat = new webChat();
        $sendType = $this->config->get('config_msg_type');
        $res = '';
        if( $sendType == 'sms' ){
            $res = $webChat->sendMsgBySms( $mobileNumber, $content );
        }else if( $sendType == 'webservice' ){
            $res = $webChat->sendMsgByWebservice( $mobileNumber, $content );
        }
        return $res;
    }



    private function checkVerification(){
        $webChat = new WebChat();
        $res = $webChat->checkVerification();
        return $res;
    }

    private function searchOpenRecord( $webChatId, $toUsername, $time ){
        $res = $this->checkInit( $webChatId );

        if( $res['status'] == 'noMobile' ){
            $msgType = 'text';
            $bindLink = HTTP_SERVER . "index.php?route=weixin/checkmobile" . '&webChatId=' . $webChatId . '&operationType=recording'. '&publicId=' . $toUsername;
            $contentStr = "如需使用，请先绑定您的手机号：
<a href='" . $bindLink . "'>立刻绑定</a>";
            $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }else{

            $orders = $res['orders'];

            if(empty($orders)){
                $msgType = 'text';

                $bookingLink = WEIXINHTTP . 'index.php?route=weixin/room_type_list'. '&publicId=' . $toUsername.'&webChatId=' . $webChatId;

                $bookingLinkCon = "<a href='" . $bookingLink . "'>预定房间</a>";
                $contentStr = "对不起，您当前没有可打开的房间，因此无法查询开门记录。
现在就去 " . $bookingLinkCon;
                $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{

                if(count($orders) > 1){
                    $msgType = 'text';
                    $link = WEIXINHTTP . 'index.php?route=weixin/order/locks&openRecord=1&webChatId='.$webChatId. '&publicId=' . $toUsername;

                    $linkCon = "您当前可打开多个房间，请选择您要查看的房间。
<a href='" . $link . "'>选择房间</a>";
                    $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $linkCon);
                    echo $resultStr;
                }else{

                    $msgType = 'text';
                    $link = WEIXINHTTP . 'index.php?route=weixin/order/locks&openRecord=1&webChatId='.$webChatId . '&orderDetailId=' . $orders[0]['order_detail_id']. '&publicId=' . $toUsername;
                    $linkCon = "您在这里可以查看当前入住房间的所有开门记录，包括开门时间、开门者姓名、开门者号码等。
<a href='" . $link . "'>查看开门记录</a>";
                    $resultStr = sprintf($this->textTpl, $webChatId, $toUsername, $time, $msgType, $linkCon);
                    echo $resultStr;
                }
            }
        }
    }


    private function createOrderItemsTpl($param){
        $items = "<item>
                <Title>" . $param['title'] . "</Title>
                <Description>" . $param['description'] . "</Description>
                <PicUrl>" . $param['picUrl'] . "</PicUrl>
                <Url>" . $param['url'] . "</Url>
                </item>";
        return $items;
    }

    private function initTpl(){
        $this->textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";

        $this->imgTextTplStart = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>12345678</CreateTime>
<MsgType>news</MsgType>
<ArticleCount><![CDATA[%s]]></ArticleCount>
<Articles>";
        $this->imgTextTplItem = "<item>
<Title>test1</Title>
<Description>adaadaadd</Description>
<PicUrl></PicUrl>
<Url>http://www.baidu.com</Url>
</item>";
        $this->imgTextTplTail = "</Articles>
</xml> ";

    }

    private function initPic(){
        $this->orderstatusPic = array(
            '0'=> WEIXINHTTP . 'image/webchat/auth0.png',
            '1'=> WEIXINHTTP . 'image/webchat/auth1.png',
            '2'=> WEIXINHTTP . 'image/webchat/auth2.png',
            '3'=> WEIXINHTTP . 'image/webchat/auth2.png'
        );
    }


    private function moreHelp( $keyword, $fromUsername, $toUsername, $time ){
        if($keyword == '1'){
            $contentStr = "每个房门都有钥匙和门卡开锁的备用方案，如果您不小心忘记带手机或手机没电的情况下，可以找房东用门卡或钥匙帮助您打开房门。";
        }else if($keyword == '2'){
            $contentStr = "如果您所处位置信号不好，导致微信下发的指令无法通过互联网传达到门锁上，则可能导致门锁无法通过微信打开。
另外，如果您的订单入住时间已过，则也无法打开房门了哦。";
        }else if($keyword == '3'){
            $contentStr = "如果网络情况不稳定，则可能会导致信息记录被遗漏的情况发生。
稍等一段时间后重新刷新记录，则会出现正常的开门记录啦。
目前只能记录通过微信打开房门的情况哦。";
        }else if($keyword == '4'){
            $contentStr = "可以有多个微信号在同一时段内都能够打开同一个门锁，但是所有的微信号都需要入住人通过手机接收验证码后才可以得到授权。
没有入住人授权的情况下，任何微信都是不可以打开门锁的。";
        }else if($keyword == '5'){
            $contentStr = "所有采用了我们智能门锁解决方案的服务式公寓和酒店都可以通过授权订单来进行微信开锁。也欢迎您帮助我们进行宣传哦！";
        }
        else if( $keyword == 'auth'){
            $appId = $this->appId;
            $redirect_uri = WEIXINHTTP . 'index.php?route=weixin/api_request/webAuthCode';

//            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=snsapi_base&state=base#wechat_redirect";

            $contentStr = "<a href='" . $url . "'>测试授权auth2.0</a>" . $url;
        }


        else if($keyword == 'guagua'){
            $url = WEIXINHTTP . "index.php?route=weixin/ui_test";
            $contentStr = "<a href='" . $url . "'>刮刮卡</a>";
        }else if($keyword == 'guagualx'){
            $url = WEIXINHTTP . "index.php?route=weixin/ui_test/guagualx";
            $contentStr = "<a href='" . $url . "'>刮刮卡乐享</a>";
        }else if( $keyword == 'dzp'){
            $url = WEIXINHTTP . "index.php?route=weixin/ui_test/choujiang";
            $contentStr = "<a href='" . $url . "'>抽奖</a>";
        }
        else if( $keyword == 'popup'){
            $url = WEIXINHTTP . "index.php?route=weixin/ui_test/popup";
            $contentStr = "<a href='" . $url . "'>弹出框</a>";
        }
        else{
            $contentStr = '对不起，我…我没有识别您的指令哦。请耐心等待主人的答复吧—_—!';
//            $contentStr = '';
        }
        return $contentStr;
    }

    private function getRandJoke(){

        $total = $this->model_webChat_order->getJokeTotal();
        if(!empty($total)){
            $total --;
            $random = rand(0,$total);
            $jokes = $this->model_webChat_order->getRandJoke( $random );
            $jokeContents = htmlspecialchars_decode( $jokes['contents'] );
            return $jokeContents;
        }
        return '';
    }


    /*
     *用于检测该微信账户
     * 1.是否绑定手机
     * 2.时候有相关订单
     * 3.是否有入住人订单
     *
     * return string， 逐次递进：
     *
     * 1.noMobile : 没有绑定手机
     * 2.noOrder ： 没有相关订单
     * 3.noLodger ： 没有入住人订单
     * 4.fine ： 什么都有了
     *
     */
    private function checkInit( $fromUsername ){
        $res = array(
            'status' => '',
            'orders' => array()
        );
        $ret = '';
        $orders = array();
        $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );

        if( !empty( $mobile )){
            $orders = $this->model_webChat_order->getAuthedOrderDetails( null, $fromUsername, null, null, $this->storeId  );
            if( !empty($orders) ){
                //todo: 有无入住人的订单
                $isLodger = false;
                while( current($orders) !== false  ){
                    $order = current($orders);
                    if( !empty($order['lodgerOpenId']) && $order['lodgerOpenId'] == $fromUsername ){
                        $isLodger = true;
                        break;
                    }
                    next( $orders );
                }
                if( !$isLodger ){

                    $ret = 'noLodger';

                }else{

                    $ret = 'fine';

                }

            }else{

                $ret = 'noOrder';

            }

        }else{

            $ret = 'noMobile';

        }
        $res['status'] = $ret;
        $res['orders'] = $orders;
        return $res;
    }


    /**
     * 通过 文字 调用微信各个菜单功能
     * “开门”、“开锁”就调用开门过程，
    “订单”调用我的订单过程，
    “呼叫”调用呼叫前台过程，
    “预定”、“主页”调用小店主页，
    “抽奖”调用抽奖过程，
    “优惠”、“公告”调用优惠/公告过程，
    “保洁”调用保洁过程，
    “退房”调用退房过程。
     */

    private function wordFunction( $word ,$fromUsername, $toUsername, $time, $msgType ){



        /* warning: 误删， 如果 客户需要 打开 文字恢复， 取消下面的注释， 并删掉 该函数 上面的代码 */

        /*$res = $this->recordWebChat( $fromUsername, $time, $word );
        if( $word == 'getopenid' ){
            $this->openIdAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '开门') !== false || strpos($word, '开锁') !== false ){
            $this->openAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '订单') !== false  ){
            $this->orderAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '呼叫') !== false ){
            $this->callingAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '预定') !== false ){
            $this->bookingAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '主页') !== false ){
            $this->homeAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '抽奖') !== false  ){
            $this->lotteryAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '优惠') !== false || strpos($word, '公告') !== false ){
            $this->announceAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '保洁') !== false  ){
            $this->cleanAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, 'joke') !== false  ){
            $this->jokeAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($word, '退房') !== false  ){
            $this->checkOutAction( $fromUsername, $toUsername, $time, $msgType );

        }else{
            $msgType = "text";
            $contentStr = '对不起，我…我没有识别您的指令哦。请耐心等待主人的答复吧—_—!';
            $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
            echo $resultStr;
        }*/
    }
    /**
     * 通过语言调用微信各个菜单功能
     * “开门”、“开锁”就调用开门过程，
    “订单”调用我的订单过程，
    “呼叫”调用呼叫前台过程，
    “预定”、“主页”调用小店主页，
    “抽奖”调用抽奖过程，
    “优惠”、“公告”调用优惠/公告过程，
    “保洁”调用保洁过程，
    “退房”调用退房过程。
     */
    private function voiceFunction( $voice ,$fromUsername, $toUsername, $time, $msgType ){
        $res = $this->recordWebChat( $fromUsername, $time, $voice );
        if( strpos($voice, '开门') !== false || strpos($voice, '开锁') !== false ){
            $this->openAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($voice, '订单') !== false  ){
            $this->orderAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($voice, '呼叫') !== false ){
            $this->callingAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($voice, '预定') !== false || strpos($voice, '预订') !== false  ){
            $this->bookingAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($voice, '主页') !== false ){
            $this->homeAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($voice, '抽奖') !== false  ){
            $this->lotteryAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($voice, '优惠') !== false || strpos($voice, '公告') !== false ){
            $this->announceAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($voice, '保洁') !== false  ){
            $this->cleanAction( $fromUsername, $toUsername, $time, $msgType );

        }else if( strpos($voice, '退房') !== false  ){
            $this->checkOutAction( $fromUsername, $toUsername, $time, $msgType );

        }else{
            $msgType = "text";
            $contentStr = '对不起，我…我没有识别您的指令哦。请耐心等待主人的答复吧—_—!';
            $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr  );
            echo $resultStr;
        }

    }


    private function openIdAction( $fromUsername, $toUsername, $time, $msgType ){

        $contentStr = "openId : " . $toUsername;
        $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
        die();
    }
    /**
     * 微信开门动作
     * @param $fromUsername
     * @param $toUsername
     * @param $time
     * @param $msgType
     */
    private function openAction( $fromUsername, $toUsername, $time, $msgType ){
        $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );
        if( empty($mobile) ){
            $link = WEIXINHTTP . 'index.php?route=weixin/checkmobile&operationType=open&webChatId=' . $fromUsername . '&publicId=' . $toUsername;
            $linkCon = "<a href='" . $link . "'>立刻绑定</a>";
            $contentStr = "如需使用，请先绑定您的手机号：
 ".$linkCon;
            $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
            die();
        }

        $this->openLock( $fromUsername, $toUsername, $time, $mobile );
        die();
    }

    /**
     * 微信订单动作
     * @param $fromUsername
     * @param $toUsername
     * @param $time
     * @param $msgType
     */
    private function orderAction( $fromUsername, $toUsername, $time, $msgType ){
        $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );
        if( !empty( $mobile )){
            $orders = $this->model_webChat_order->getAuthedOrderDetails( null, $fromUsername, null, null, $this->storeId  );
            if( !empty($orders) ){
                //: 有无入住人的订单
                $isLodger = false;
                while( current($orders) !== false  ){
                    $order = current($orders);
                    if( !empty($order['lodgerOpenId']) && $order['lodgerOpenId'] == $fromUsername ){
                        $isLodger = true;
                        break;
                    }
                    next( $orders );
                }
                if( !$isLodger ){

                    $checkLink = WEIXINHTTP . 'index.php?route=weixin/auth/authList&webChatId=' . $fromUsername. '&publicId=' . $toUsername;
                    $contentStr = "此处仅显示酒店登记为第一入住人的订单。
您可以 <a href='" . $checkLink . "'>查看我的开门权限</a>";

                    $orderLink = WEIXINHTTP . 'index.php?route=weixin/order/locks&webChatId='.$fromUsername . '&storeId=' . $this->storeId. '&publicId=' . $toUsername .'&type=order';
                    $continueBook = "<a href='" . $orderLink . "'>" . "查看我的订单" . "</a>";
                    $contentStr .= " 或继续 " . $continueBook;

                    $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    die();
                }else{

                    $checkLink = WEIXINHTTP . 'index.php?route=weixin/order/locks&webChatId='.$fromUsername . '&storeId=' . $this->storeId. '&publicId=' . $toUsername .'&type=order';
                    $contentStr = "如果要查看您当前的订单，请点击
<a href='" . $checkLink . "'>查看我的订单</a>";
                    $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    die();
                }

            }else{
                $bookingLink = WEIXINHTTP . 'index.php?route=weixin/room_type_list'. '&publicId=' . $toUsername.'&webChatId=' . $fromUsername;
                $contentStr = "对不起，您当前没有关联的订单，无法进行此操作。
现在就去 <a href='" . $bookingLink . "'>预定房间</a>";
                $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                die();
            }

        }else{

            $link = WEIXINHTTP . 'index.php?route=weixin/checkmobile&operationType=open&webChatId=' . $fromUsername . '&publicId=' . $toUsername;
            $linkCon = "<a href='" . $link . "'>立刻绑定</a>";
            $contentStr = "如需使用，请先绑定您的手机号：
 ".$linkCon;
            $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
            die();
        }
    }
    /**
     * 微信呼叫动作
     * @param $fromUsername
     * @param $toUsername
     * @param $time
     * @param $msgType
     */
    private function callingAction( $fromUsername, $toUsername, $time, $msgType ){
        // : 检测 手机号是否绑定
        $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );

        if( empty($mobile) ){
            $link = WEIXINHTTP . 'index.php?route=weixin/checkmobile&operationType=calling&webChatId=' . $fromUsername. '&publicId=' . $toUsername;
            $linkCon = "<a href='" . $link . "'>绑定手机</a>.";
            $contentStr = "绑定手机后，当呼叫前台时，我们的400电话会双向拨通您和前台，仅需接听，通话免费。
现在就去 ".$linkCon;
            $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
            die();
        }
        // 检测是否有 绑定订单

        // 直接通话
        $caller = $mobile;

        if( !empty( $this->storeId ) ){
            $called = $this->model_webChat_order->getStorePhone( $this->storeId );
            if( !empty( $called ) ){

                $res = false;

                $res = $this->callLandlord($caller, $called, $this->storeId);

                ob_clean();
                if( $res ){
                    $contentStr = "已成功呼叫，稍后请接听来电“4008-801-802”，接听后请等待呼通前台。
                                   如前台未接电话，则可能处于占线或忙碌状态，可稍后再试。";
                }else{
                    $contentStr = '由于网络原因,未接通,请稍后再拨.';
                }
                $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                die();
            }else{
                $contentStr = '很抱歉，酒店尚未登记前台电话，当前无法帮助您联系到前台.';
                $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                die();
            }
        }else{
            $contentStr = '很抱歉，酒店尚未登记前台电话，当前无法帮助您联系到前台。';
            $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
            die();
        }
    }

    private function homeAction( $fromUsername, $toUsername, $time, $msgType ){
        $link = WEIXINHTTP . 'index.php?route=weixin/room_type_list'. '&publicId=' . $toUsername .'&webChatId=' . $fromUsername;
        $contentStr = "欢迎光临！
在小店主页您将可以预定房间、查看最新优惠等等。
点击下面链接，精彩世界将为您缤纷呈现！
<a href='" . $link . "'>小店主页</a>";

        $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
        die();
    }
    private function bookingAction( $fromUsername, $toUsername, $time, $msgType ){
        $link = WEIXINHTTP . 'index.php?route=weixin/room_type_list' . '&publicId=' . $toUsername.'&webChatId=' . $fromUsername;

        $contentStr = "欢迎您预定我们的房间：
请点击 <a href='" . $link . "'>预订房间</a>";
        $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
        die();
    }


    private function lotteryAction( $fromUsername, $toUsername, $time, $msgType ){
        $today = date( 'Y-m-d H:i:s', time() + TIMEDIFFERENCE );
        $this->load->model('webChat/lottery');
        $newLottery = $this->model_webChat_lottery->getAvailableLottery( $this->storeId, $today, 'nothing');
        $lotteryLink = '';
        if( !empty($newLottery) ){

            if($newLottery['lottery_flag_key'] == 'turning'){
                $lotteryLink = WEIXINHTTP . 'index.php?route=weixin/lottery/turning' . '&webChatId=' . $fromUsername . '&publicId=' . $toUsername;
            }else{
                $lotteryLink = WEIXINHTTP . 'index.php?route=weixin/lottery/guagua' . '&webChatId=' . $fromUsername . '&publicId=' . $toUsername;
            }
            $contentStr = "活动来啦！还不快试试手气，万一就中了呢？！~ -o- ~点击参加：<a href='" . $lotteryLink . "'>抽奖</a>";
        }else{
            $contentStr = "哎呀！奖品已经被人拿走了~~~看来只能等下次机会了..T-T..";
        }
        $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
        die();
    }

    private function announceAction( $fromUsername, $toUsername, $time, $msgType ){
        $this->load->model('webChat/announcement');
        $announceType = "activity";
        $notice = $this->model_webChat_announcement->getList($this->storeId, $announceType );
        if( !empty($notice) ){
            $newestPublicUrl = WEIXINHTTP . "index.php?route=weixin/Announcement&publicId=" . $toUsername;
            $contentStr = "快来看看小店又有什么新动态吧！也许会有惊喜哦！~点击查看：<a href='" . $newestPublicUrl . "'>最新优惠/公告</a>";
        }else{
            $contentStr = "小店现在还没有什么新动态，还是去看点别的吧~";
        }
        $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
        die();
    }

    private function cleanAction( $fromUsername, $toUsername, $time, $msgType ){
        $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );

        if( !empty( $mobile )){

            //有无可开门房间
            $orders = $this->model_webChat_order->getAuthedOrderDetails( null, $fromUsername, null, null, $this->storeId  );
            if( !empty($orders) ){
                //: 有无入住人的订单
                $isLodger = false;
                while( current($orders) !== false  ){
                    $order = current($orders);
                    if( $order['status'] != '0' ){

                        if( !empty($order['lodgerOpenId']) && $order['lodgerOpenId'] == $fromUsername ){
                            $isLodger = true;
                            break;
                        }
                    }

                    next( $orders );
                }
                if( !$isLodger ){

                    $bookingLink = WEIXINHTTP . 'index.php?route=weixin/order/callLandlord&webChatId='.$fromUsername . '&storeId=' . $this->storeId. '&publicId=' . $toUsername;
                    $contentStr = "您好，只有酒店登记的第一入住人才可以提交保洁需求。
如需退房，您可 <a href='" . $bookingLink . "'>呼叫前台</a> ";
                }else{

                    $bookingLink = WEIXINHTTP . 'index.php?route=weixin/clean&webChatId=' . $fromUsername. '&publicId=' . $toUsername;
                    $contentStr = "如果您需要预约保洁，请点击
<a href='" . $bookingLink . "'>提交保洁需求</a>";
                }

            }else{
                $bookingLink = WEIXINHTTP . 'index.php?route=weixin/room_type_list'. '&publicId=' . $toUsername.'&webChatId=' . $fromUsername;
                $contentStr = "对不起，您当前没有入住中的订单，无法进行此操作。
现在就去 <a href='" . $bookingLink . "'>预定房间</a>";
            }

        }else{

            $link = WEIXINHTTP . 'index.php?route=weixin/checkmobile&operationType=open&webChatId=' . $fromUsername . '&publicId=' . $toUsername;
            $linkCon = "<a href='" . $link . "'>立刻绑定</a>";
            $contentStr = "如需使用，请先绑定您的手机号：
 ".$linkCon;
        }
        $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
        die();
    }

    private function checkOutAction( $fromUsername, $toUsername, $time, $msgType ){
        $mobile = $this->model_webChat_order->checkWebChatBinded( $fromUsername );

        if( !empty( $mobile )){
            $link = WEIXINHTTP . 'index.php?route=weixin/checkOut'. '&publicId=' . $toUsername;
            $contentStr = "<a href='" . $link . "'>退房</a>";
            // : 查找是否有房间入住

            $orders = $this->model_webChat_order->getAuthedOrderDetails( null, $fromUsername, null, null, $this->storeId  );

            if( !empty($orders) ){

                $isLodger = false;
                while( current($orders) !== false  ){
                    $order = current($orders);
                    if( !empty($order['lodgerOpenId']) && $order['lodgerOpenId'] == $fromUsername ){
                        $isLodger = true;
                        break;
                    }
                    next( $orders );
                }
                if( !$isLodger ){
                    $bookingLink = WEIXINHTTP . 'index.php?route=weixin/order/callLandlord&webChatId='.$fromUsername . '&storeId=' . $this->storeId. '&publicId=' . $toUsername;
                    $contentStr = "您好，只有酒店登记的第一入住人才可以提交退房通知。
如需退房，您可 <a href='" . $bookingLink . "'>呼叫前台</a>";
                }else{

                    $bookingLink = WEIXINHTTP . 'index.php?route=weixin/checkOut&webChatId=' . $fromUsername. '&publicId=' . $toUsername;
                    $contentStr = "如果您需要预约退房，请点击
<a href='" . $bookingLink . "'>提交退房通知</a>";
                }

            }else{
                $bookingLink = WEIXINHTTP . 'index.php?route=weixin/room_type_list'. '&publicId=' . $toUsername.'&webChatId=' . $fromUsername;
                $contentStr = "对不起，您当前没有入住中的订单，无法进行此操作。
现在就去 <a href='" . $bookingLink . "'>预定房间</a>";
            }
        }else{

            $link = WEIXINHTTP . 'index.php?route=weixin/checkmobile&operationType=open&webChatId=' . $fromUsername . '&publicId=' . $toUsername;
            $linkCon = "<a href='" . $link . "'>立刻绑定</a>";
            $contentStr = "如需使用，请先绑定您的手机号：
 ".$linkCon;
        }

        $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
        die();
    }

    /**
     *
     */
    public function jokeAction($fromUsername, $toUsername, $time, $msgType){
        //todo : get the returned joke number , if it is bigger than 10, return enough tips
        $returnedNumber = $this->model_webChat_order->getReturnedJoke($fromUsername, $time);
        $contentStr = '';
        $joke_show_number = $this->model_webChat_order->getStoreJokeShowNumber( $this->storeId );
        $joke_show_number = !empty($joke_show_number) ? $joke_show_number : 10;
        if( $returnedNumber > $joke_show_number){
            $contentStr = '很抱歉，您今天浏览笑话的次数已经用尽';
        }else{
            $joke = $this->getRandJoke();
            $contentStr = $joke;
        }
        $resultStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
        die();

    }
}