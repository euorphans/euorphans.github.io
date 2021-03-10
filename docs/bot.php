

    <?php

if (!isset($_REQUEST)) {
return;
}

//Строка для подтверждения адреса сервера из настроек Callback API
$confirmation_token = 'хххххххх';

//Ключ доступа сообщества
$token = 'xxx';

//Получаем и декодируем уведомление
$data = json_decode(file_get_contents('php://input'));

//Проверяем, что находится в поле "type"
switch ($data->type) {
//Если это уведомление для подтверждения адреса...
case 'confirmation':
//...отправляем строку для подтверждения
echo $confirmation_token;
break;
     
    define('confirmation_token', '50dc77d0');                                                                
    define('token', '9c21fca28aeb51965f4eed762926d4c2cdd99748db9061804d979989438621964d100b85aeb877f183402');
     
    $data = json_decode(file_get_contents('php://input') , true);
     
    switch ($data['type']){
        case 'confirmation':
            echo confirmation_token;
        break;
        case 'message_new':
            $peer_id = $data['object']['peer_id'] ? : $data['object']['user_id'];
            $text = explode(" ", $data['object']['text']);
            $text[0] = mb_strtolower($text[0]);
                    
            switch ($text[0]){
                case '/help': case '/хелп': case '/команды':
                    send_message($peer_id, "Доступные команды:\n\n/stats <ник> - просмотр статистики игроков");
                break;   
                case '/stats': case '/статс': case '/статистика':
                	$ch = curl_init("https://api.vimeworld.ru/user/name/{$text[1]}");
    		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Access-Token: DuraSymQufsZkVntQ6by3CgeZHufPEN'));
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    		$response = json_decode(curl_exec($ch), true);
    		curl_close($ch);
     
    	            if($response[0]['id']){
    	            	if($response[0]['guild']['name']){
    			    $guild = $response[0]['guild']['name'];
    			}
    			else{
    			    $guild = "Отсутствует";
    			}
     
    	            	$days = number_format($response[0]['playedSeconds']/60/60/24,0,'.','');
    			$hour = number_format($response[0]['playedSeconds']/60/60%24,0,'.','');
    			$min = number_format($response[0]['playedSeconds']/60%60,0,'.','');
    			$sec = number_format($response[0]['playedSeconds']%60,0,'.','');
     
    			$player = "Профиль игрока: {$response[0]['username']}
    			Ранг: {$response[0]['rank']}
    			Гильдия: {$guild}
    			Проведено в игре: {$days} дн. {$hour} ч. {$min} мин. {$sec} сек.";
    			}
    			else{
    			$player = "Данного игрока не существует!";
    			}
     
                    send_message($peer_id, $player);
                break;   
            case '/moders': case '/модеры':
            	$ch = curl_init("https://api.vimeworld.ru/online/staff");
    		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    		$response = json_decode(curl_exec($ch), true);
    		curl_close($ch);
     
    		$count = count($response);
    		$m = -1;
     
    		while($m != $count-1){
    			$m +=1;
    			if($response[$m]['guild']['tag']){$guildtag = "<{$response[$m]['guild']['tag']}> ";}else{$guildtag = "";}
    			if($response[$m]['rank'] == 'MODER'){$rank = '[Модер]';}
    			if($response[$m]['rank'] == 'WARDEN'){$rank = '[Пр.Модер]';}
    			if($response[$m]['rank'] == 'CHIEF'){$rank = '[Гл.Модер]';}
     
    			$c +=1;
    			$template .= "\n{$guildtag}{$rank} {$response[$m]['username']}. {$response[$m]['online']['message']}";
    		} 
    			
    	send_message($peer_id, "Модераторы онлайн: \n{$template}\n\nВсего в сети: {$count}");
    	break;	                 
            case '/streams': case '/стримы':
            	$ch = curl_init("https://api.vimeworld.ru/online/streams");
    		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    		$response = json_decode(curl_exec($ch), true);
    		curl_close($ch);
     
    		$count = count($response);
    		$s = -1;
     
    		while($s != $count-1){
    			$s +=1;
     
    			$time1 = $response[$s]['duration']/60/60/24;
    			$time2 = $response[$s]['duration']/60/60%24;
    			$time3 = $response[$s]['duration']/60%60;
    			$time4 = $response[$s]['duration']%60;
     
    			$time1 = number_format($time1,0,'.','');
    			$time2 = number_format($time2,0,'.','');
    			$time3 = number_format($time3,0,'.','');
    			$time4 = number_format($time4,0,'.','');
     
    			$c +=1;
    			$template .= "{$response[$s]['title']}\nСтример: {$response[$s]['owner']}\nЗрителей: {$response[$s]['viewers']}\nСсылка: {$response[$s]['url']}\nСтрим идёт: {$time1} дн. {$time2} ч. {$time3} мин. {$time4} сек.\n";
    		}
     
    	send_message($peer_id, "Стримы в данный момент:\n\n{$template}Всего стримов: {$count}");
    	break;	                 
            }
     
            echo ('ok');
            header("HTTP/1.1 200 OK");
        break;
        default:
            echo ('Unsupported event');
        break;
    }
     
    function send_message($peer_id = null, $message = null, $forward_messages = null, $attach = null, $random_id = null)
    {
        api('messages.send', array(
            'peer_id' => $peer_id,
            'message' => $message,
            'forward_messages' => $forward_messages,
            'keyboard' => $keyboard,
            'attachment' => $attach,
            'random_id' => '0'
        ));
    }
     
    function api($method, $params)
    {
        $params['access_token'] = token;
        $params['v'] = '5.102';
     
        $curl = curl_init();
     
        curl_setopt_array($curl, [
        CURLOPT_URL             => 'https://api.vk.com/method/' . $method,
        CURLOPT_RETURNTRANSFER  => TRUE,
        CURLOPT_POSTFIELDS      => $params,
        CURLOPT_CUSTOMREQUEST => "POST"
        ]);
     
        $json = curl_exec($curl);
        
        curl_close($curl);
     
        $response = json_decode($json, true);
        return $response['response'];
     
    }
     
    ?>  

