<?php
/**
 * Created by PhpStorm.
 * User: evari
 * Date: 1/31/2018
 * Time: 10:42 PM
 */

function filter_matches($tweets,$keywords){
    $res = [];
    foreach ($tweets as $tweet){
        $i = 0;
        $k = true;
        while ($i<count($keywords)&& $k){
            if(strpos($tweet['text'],$keywords[$i])!==false){
                array_push($res,$tweet);
                $k = false;
            }
            $i++;
        }
    }

    return $res;
}

function similar_comp ($text,$k,$t1,$t2,$tweet){
    $texts = explode(" ",$text);
    $j = 0;
    while ($j<count($texts) && $k){
        similar_text(strtolower($t1),$t2,$ct);

        if($ct>75){
            array_push($res,$tweet);
            $k = false;
        }
        $j++;
    }
}

function get_tweets($res,$users,$db,$cb){
    foreach ($users as $user){
        $statement = $db->prepare("select * from tweet_trackings where user_id = :id");
        $statement->execute(array(':id' => $user['id_str']));
        $row = $statement->fetchAll(PDO::FETCH_ASSOC);
        $tts = [];
        $params =array(
            'user_id'=> $user['id_str']
        );
        if(count($row)>0){
            $params['since_id']= $row[0]['last_tweet_id'];
        }
        $tts = $cb->statuses_userTimeline($params);

        $most_recent = true;
        foreach ($tts as $tt){
            if(is_array($tt)&& array_key_exists('id_str',$tt) && array_key_exists('text',$tt)){
                if($most_recent){
                    $statement = $db->prepare("insert into tweet_trackings (user_id, last_tweet_id) 
                      values (:user_id, :tweet_id) on duplicate key update last_tweet_id=:tweet_id");
                    $statement->execute(array(':user_id' => $user['id_str'],':tweet_id'=>$tt['id_str']));
                    $most_recent= false;
                }
                array_push($res,['id_str'=>$tt['id_str'],'text'=>$tt['text']]);

            }
        }
    }
    return $res;
}