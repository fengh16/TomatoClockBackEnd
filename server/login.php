<?php

  include 'db.php';
  $code = $_GET['code'];
  $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=appid&secret=secret&js_code=' . $code . '&grant_type=authorization_code';
  $info = file_get_contents($url);
  $json = json_decode($info);
  $arr = get_object_vars($json);
  $res = array (
    'state'=>'error',
    'info'=>'errorCodeOrNetworkError'
  );
  if ($arr['openid']) {
    $openidmd5 = md5($arr['openid']);
    $openid = addslashes($arr['openid']);
    $res['session'] = md5(addslashes($arr['session_key']));
    $res['invite'] = $openidmd5;
    // 现在开始进行登录操作
    // 查找有没有该用户
    $sql = "SELECT *
            FROM user_table
            where user_login_id='$openid'";
    $result = mysqli_query( $conn, $sql );
    $rows = mysqli_num_rows($result);

    if (!$rows) {
      // 如果没有数据，就添加
      $sql = "INSERT INTO user_table ".
             "(user_login_id, user_par_id,  user_invite_id,   user_session) ".
             "VALUES ".
             "('$openid',     -1,           '$openidmd5', '". $res['session']."')";
      $result = mysqli_query( $conn, $sql );
      if (!$result) {
        $res['info'] = "errorCreatingUser";
      }
      else {
        $res['state'] = "ok";
        $res['info'] = "signedUp";
        $res['totalTime'] = 0;
        $res['totalClockNums'] = 0;
        $res['nowInTomato'] = 0; // 返回0表示现在不在一个番茄钟里面
        $res['havePart'] = 0;
      }
    }
    else {
      $res['state'] = "ok";
      $res['info'] = "loggedIn";
      $row = mysqli_fetch_array($result);
      $res['totalTime'] = (int)$row['user_time'];
      $res['totalClockNums'] = (int)$row['user_times'];
      $todoOwnerId = (int)$row['user_id'];
      $userParId = $row['user_par_id'];
      // 下面的查询语句，只是更新session，所以在别的文件中再获取信息的时候就不用了
      $sql = "UPDATE user_table
              SET user_session = '". $res['session']."'
              WHERE user_login_id = '$openid'";
      $retval = mysqli_query( $conn, $sql );
      // 上面的内容在复制到别的内容的时候，不用复制
      $finished = 0;
      $todoIdNow = $row['user_todo_id'];
      if ($row['user_todo_id'] < 0) {
        $res['nowInTomato'] = 0;
      }
      else {
        $isStartTodo = 0;
        $isPause = 0;
        $stopTomatoSign = 0;
        // 现在获取番茄信息！
        include 'gettomato.php';
      }
      if ($finished == 1) {
        // 现在发现刚好番茄钟结束了！（或者迟了不到50秒的时间……
        // 现在需要处理：tomato信息、todo信息、用户信息、用户info信息
        
        // tomato信息的处理，之前已经处理过了
        // 现在进行todo信息的处理（tomatoindex之前已经加了1
        $tomatoState = (1 + (int)$nextIsLastTomato);
        $sql = "UPDATE todo_table
                SET todo_complete_num = $tomatoIndex , todo_state = $tomatoState
                WHERE todo_id = $todoIdNow";
        $retval = mysqli_query( $conn, $sql );
        // 总时间多了(int)($tomatoRes['totalTime'] / 60)，总tomato数目多了1
        // 所以进行用户信息的更新：
        $newTotalTime = (int)$res['totalTime'] + (int)($tomatoTotalTimeRec);
        $res['totalTime'] = $newTotalTime;
        $newTotalClocks = (int)$res['totalClockNums'] + 1;
        $res['totalClockNums'] = $newTotalClocks;
        $sql = "UPDATE user_table
                SET user_time = $newTotalTime , user_times = $newTotalClocks, user_todo_id = -1
                WHERE user_login_id = '$openid'";
        $retval = mysqli_query( $conn, $sql );

        $userIdForUpdate = $todoOwnerId;
        include 'updateUserInfo.php';
  
        // 修改返回info，说明现在已经完成
        $res['info'] = "finished";
      }

      if ($userParId < 0) {
        $res['havePart'] = 0;
      }
      else {
        $res['havePart'] = 1;
        $res['partTomatoInfo'] = getUserTomatoInfo($userParId, $todoOwnerId);
      }
    }
  }

  mysqli_close($conn);
  echo json_encode($res);  

?>