<?php

  include 'db.php';

  $invite = addslashes($_GET['invite']);
  $session = addslashes($_GET['session']);
  // 都需要带上todo和session
  $res = array (
    'state'=>'error',
    'info'=>'errorInfoOrTimeout'
  );
  $sql = "SELECT *
    FROM user_table
    where user_session='$session' AND user_invite_id='$invite'";
  $result = mysqli_query( $conn, $sql );
  $rows = mysqli_num_rows($result);

  if ($rows) {
    // 这些东西都应该在传过来的时候检测一下，但是后端也得做好准备
    // 最多16长度
    $todoName = "autoTodo";
    $doTime = $_GET['doTime'];
    $restTime = $_GET['restTime'];
    $todoTimes = 99999999;
    $todoForm = $_GET['todoForm']; // 正计时1倒计时2
    
    if ($todoForm != 1) {
      // 如果不是声明正计时，那就直接倒计时吧
      $todoForm = 2;
    }
    if ($doTime < 10) {
      // 不到十分钟不让用番茄
      $doTime = 10;
    }
    if ($doTime > 240) {
      // 四个小时你不累吗……
      $doTime = 240;
    }
    if ($restTime < 0) {
      // hhh怕不是在逗我玩
      $restTime = 0;
    }
    if ($restTime > 240) {
      // 该学习了！
      $restTime = 240;
    }
    // 处理完了
    $row = mysqli_fetch_array($result);
    $userId = $row['user_id'];
    
    // 现在添加表项
    $sql = "INSERT INTO todo_table ".
           "(todo_state,  todo_shown_name, todo_tar_num,  todo_complete_num,  todo_type,  todo_run_time,  todo_rest_time, todo_owner_id) ".
           "VALUES ".
           "(1,           '$todoName',     $todoTimes ,   0,                  $todoForm,  $doTime,        $restTime,      $userId)";
    $result = mysqli_query( $conn, $sql );
    if (!$result) {
      $res['info'] = "errorCreatingTodo";
    }
    else {
      $res['doTime'] = $doTime;
      $res['restTime'] =  $restTime;
      $res['todoTimes'] = $todoTimes;
      $res['todoForm'] = $todoForm;
      $res['todoName'] = $todoName;
      // 因为创建了tomato之后，用户就在对应的这个todo里面了，所以需要找到刚才的那个todo的id
      $sql = "select max(todo_id) from todo_table where todo_owner_id = $userId";
      $result = mysqli_query( $conn, $sql );
      if (!$result) {
        $res['info'] = "errorGettingTodoId";
      }
      else {
        $row = mysqli_fetch_array($result);
        $todoID = $row['max(todo_id)'];
        // result 就是那个需要的id
        $sql = "UPDATE user_table
                SET user_todo_id = $todoID
                WHERE user_id = $userId";
        $retval = mysqli_query( $conn, $sql );
        if ($retval) {
          // 成功修改用户的当前todo信息
          // 现在开始创建tomato
          $doTimeSec = $doTime * 60;
          $createTime = time() + 8 * 3600;// 新浪云不需要调时区，腾讯云需要
          $sql = "INSERT INTO todo_info_table ".
                 "(todo_info_owner_id,  todo_info_no, todo_info_finished,  todo_info_left_time_sec,  todo_info_total_time_sec) ".
                 "VALUES ".
                 "($todoID,             0,            0,                   $doTimeSec,               $doTimeSec)";
          $result = mysqli_query( $conn, $sql );
          if (!$result) {
            $res['info'] = "errorCreatingTomato";
          }
          else {
            // 成功创建番茄
            $res['info'] = "added";
            $res['state'] = 'ok';
            $res['tomatoInfo'] = array (
              'leftTime'=>$doTimeSec,
              'nowTime'=>date('Y-m-d H:i:s', $createTime),
              'startTime'=>date('Y-m-d H:i:s', $createTime),
              'state'=>"running",
              'timeBetween'=>0,
              'totalTime'=>$doTimeSec
            );
          }
        }
        else {
          $res['info'] = "errorUpdateUserInfo";
        }
      }
    }
  }

  mysqli_close($conn);
  echo json_encode($res);  

?>
