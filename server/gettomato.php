<?php
// 需要在include之前有对user的查询，并且获得了row！
// 只在updateTomato和login中使用！
// 而pauseTomato只不过是在update的基础上进行了一部分判断有没有需要暂停或继续的操作
// stopTomato全部抄的updateTomato的代码，直接判断里面有没有$stopTomatoSign
// startTodo复制的stopTomato的代码
if(basename($_SERVER['PHP_SELF']) == 'gettomato.php') {
  // 如果是直接通过浏览器访问
  header('HTTP/1.0 403 Forbidden');
  echo '你干啥勒，图谋不轨，谋财害命';
}
else {
        if ($isStartTodo == 1) {
          $tomatoID = $todoNo;
        }
        else {
          $tomatoID = (int)$row['user_todo_id'];
        }
        // 现在已经有了tomato的ID了（其实应该说是todo的id）
        if ($tomatoID > 0) {
          $sql = "select * from todo_table where todo_id = " . $tomatoID;
          $result = mysqli_query( $conn, $sql );
          $rows = mysqli_num_rows($result);
          if ($rows) {
            // 找到了todo对应的项
            $row = mysqli_fetch_array($result);
            $completeNum = $row['todo_complete_num'];
            // if ($row['todo_type'] != 1) {
            if (1 == 1) {
            // 如果是倒计时
              if ($row['todo_state'] == 1) {
                // 已完成了就不管了
                $sql = "select * from todo_info_table where todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $row['todo_complete_num'];
                $result = mysqli_query( $conn, $sql );
                $rows = mysqli_num_rows($result);
                if ($rows) {
                  // 找到了todo对应的tomato！
                  // 保留一下这个tomato对应的index信息，在如果以后发现已经完成，就可以用这个信息来更新todo_table
                  $tomatoIndex = $row['todo_complete_num'] + 1;
                  // 保留一下todo的信息，如果todo只差一个了，就让这个变量为1，否则为0；
                  $todoInfo = array(
                    "todo_shown_name"=>$row['todo_shown_name'],
                    "todo_tar_num"=>(int)$row['todo_tar_num'],
                    "todo_complete_num"=>(int)$row['todo_complete_num'],
                    "todo_type"=>(int)$row['todo_type'],
                    "todo_run_time"=>(int)$row['todo_run_time'],
                    "todo_rest_time"=>(int)$row['todo_rest_time'],
                    "todo_id"=>(int)$row["todo_id"]
                  );
                  if ((int)$row['todo_tar_num'] - (int)$row['todo_complete_num'] <= 1) {
                    $nextIsLastTomato = 1;
                  }
                  else {
                    $nextIsLastTomato = 0;
                  }
                  $row = mysqli_fetch_array($result);
                  $tomatoRes = array (
                    'startTime'=>$row['todo_info_stop_time'],
                    'nowTime'=> date('Y-m-d H:i:s', (time() + 8*3600)),// 新浪云不需要调时区，但腾讯云需要
                    'leftTime'=>(int)$row['todo_info_left_time_sec'],
                    'totalTime'=>(int)$row['todo_info_total_time_sec'],
                    'state'=>'running'
                  );
                  $tomatoRes['todoInfo'] = $todoInfo;
                  $tomatoRestimeBetween = strtotime($tomatoRes['nowTime']) - strtotime($tomatoRes['startTime']);
                  if ($isStartTodo) {
                    // 如果是要开始tomato
                    // 现在需要判断一下，如果是暂停状态，就开始；否则直接重新开始计数
                    if ($row['todo_info_finished'] == 2) {
                      // 如果是暂停状态
                      // 直接复制下面暂停处理的代码
                      // 如果现在是暂停，要开始，就直接修改为0就行：
                      $sql = "UPDATE todo_info_table
                              SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . " , todo_info_finished=0
                              WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                      $sql1 = "UPDATE todo_info_table
                              SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . "1 , todo_info_finished=0
                              WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                      $retval = mysqli_query( $conn, $sql1 );
                      $retval = mysqli_query( $conn, $sql );
                    }
                    else {
                      // 否则借鉴之前的番茄过期的做法，让大家重新开始做番茄！
                      $tomatoRes['state'] = 'restart';
                      $tomatoRes['leftTime'] = $tomatoRes['totalTime'];
                      $tomatoRes['startTime'] = $tomatoRes['nowTime'];
                      $sql = "UPDATE todo_info_table
                              SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . " , todo_info_finished=0
                              WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                      $sql1 = "UPDATE todo_info_table
                              SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . "1 , todo_info_finished=0
                              WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                      $retval = mysqli_query( $conn, $sql1);
                      $retval = mysqli_query( $conn, $sql );
                    }
                  }
                  else {
                    // 如果不是从startTodo.php开始访问的
                    // 现在需要判断一下，是否是暂停状态
                    // 但是如果要停止，就不进入这里，需要去else里面
                    if ($row['todo_info_finished'] == 2 && !$isPause && $stopTomatoSign != 1) {
                      // 如果现在不改变状态，并且已经是暂停，就不需要进行任何操作了
                      $tomatoRes['state'] = 'paused';
                    }
                    // 要停止的话去else里面
                    else if ($row['todo_info_finished'] == 2 && $stopTomatoSign != 1) {
                      // 如果现在是暂停，要开始，就直接修改为0就行：
                      $sql = "UPDATE todo_info_table
                              SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . " , todo_info_finished=0
                              WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                      $sql1 = "UPDATE todo_info_table
                              SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . "1 , todo_info_finished=0
                              WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                      $retval = mysqli_query( $conn, $sql1 );
                      $retval = mysqli_query( $conn, $sql );
                    }
                    else {
                      if ($isPause == 1) {
                        $isPauseInput = 2;
                      }
                      else {
                        $isPauseInput = 0;
                      }
                      // 判断一下是否是已经过期了（开启番茄之后过了很久才回来，已经过期了，就不算了呗，现在变成暂停状态；通过判断是在结束50秒之内的话，就算是正常结束
                      // 如果是要停止，也这样做……
                      // 但是停止的话也把用户的状态改一下，用户当前不在任何todo里面
                      if (($tomatoRestimeBetween > $tomatoRes['leftTime'] + 50) || $stopTomatoSign == 1) {
                        $tomatoRes['state'] = 'passedAndPaused';
                        if ($stopTomatoSign == 1) {
                          $tomatoRes['state'] = 'stoped';
                          $sql = 'UPDATE user_table
                                  SET user_todo_id = -1
                                  WHERE user_todo_id = ' . $tomatoID;
                          $retval = mysqli_query( $conn, $sql );
                        }
                        $tomatoRes['leftTime'] = $tomatoRes['totalTime'];
                        $tomatoRes['startTime'] = $tomatoRes['nowTime'];
                        $sql = "UPDATE todo_info_table
                                SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . " , todo_info_finished=2
                                WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                        $sql1 = "UPDATE todo_info_table
                                SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . "1 , todo_info_finished=2
                                WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                        $retval = mysqli_query( $conn, $sql1);
                        $retval = mysqli_query( $conn, $sql );
                      }
                      else if (($tomatoRestimeBetween < $tomatoRes['leftTime'])) {
                        $tomatoRes['leftTime'] = $tomatoRes['leftTime'] - $tomatoRestimeBetween;
                        if ($isPause == 1) {
                          $tomatoRes['state'] = 'paused';
                        }
                        $sql = "UPDATE todo_info_table
                                SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . " , todo_info_finished=$isPauseInput
                                WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                        $sql1 = "UPDATE todo_info_table
                                SET todo_info_left_time_sec = " . $tomatoRes['leftTime'] . "1 , todo_info_finished=$isPauseInput
                                WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                        $retval = mysqli_query( $conn, $sql1 );
                        $retval = mysqli_query( $conn, $sql );
                      }
                      else {
                        // 既然都finish了……那你想暂停也别暂停了，直接终止得了
                        $finished = 1;
                        $tomatoTotalTimeRec = (int)((int)$tomatoRes['totalTime'] / 60);
                        $tomatoRes['leftTime'] = $tomatoRes['leftTime'] - $tomatoRestimeBetween;
                        $sql = "UPDATE todo_info_table
                                SET todo_info_left_time_sec = 0, todo_info_finished = 1
                                WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                        $sql1 = "UPDATE todo_info_table
                                SET todo_info_left_time_sec = 0, todo_info_finished = 11
                                WHERE todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                        $retval = mysqli_query( $conn, $sql1 );
                        $retval = mysqli_query( $conn, $sql );
                      }
                    }
                  }
                  $temptomato = $tomatoRes;
                }
                else if ($isStartTodo != 1){
                  // 现在是内部错误，第i个todo没有了
                  $sql = 'UPDATE user_table
                          SET user_todo_id = -1
                          WHERE user_todo_id = ' . $tomatoID;
                  $retval = mysqli_query( $conn, $sql );
                  $res['state'] = 'error';
                  $res['info'] = 'lostTomato';
                }
                else {
                  // 创建一个todo！现在row是todo对应的项，传进来的有$todoNo
                  // 从newTomato.php里面抄过来
                  // 现在开始创建tomato
                  $doTimeSec = (int)$row['todo_run_time'] * 60;
                  $createTime = time() + 8 * 3600;// 新浪云不需要调时区，腾讯云需要
                  $tomatoNowNo = (int)$row['todo_complete_num'];
                  $sql = "INSERT INTO todo_info_table ".
                         "(todo_info_owner_id,  todo_info_no, todo_info_finished,  todo_info_left_time_sec,  todo_info_total_time_sec) ".
                         "VALUES ".
                         "($todoNo,             $tomatoNowNo, 0,                   $doTimeSec,               $doTimeSec)";
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
                if ($isStartTodo) {
                  // 现在就修改用户的user_todo_id信息
                  $sql = "UPDATE user_table
                          SET user_todo_id = $tomatoID
                          WHERE user_id = $userId";
                  $retval = mysqli_query( $conn, $sql );
                }
              }
              else {
                // 现在这个状态对应的是todo已经完成
                // 既然已经完成，那就直接修改用户数据
                $sql = 'UPDATE user_table
                        SET user_todo_id = -1
                        WHERE user_todo_id = ' . $tomatoID;
                $retval = mysqli_query( $conn, $sql );
                $res['state'] = 'error';
                $res['info'] = 'todoAlreadyFinished';
              }
            }
            // 倒计时结束，下面分析正计时的情形
            else {
              // 正计时其实挺好玩的……
              // 正计时情况下，如果选择暂停就直接修改state
              // 
            }
          }
          else {
            // 错误处理，这时没有找到对应的todo（todoid太大了），直接update
            if($isStartTodo != 1) {
              $sql = 'UPDATE user_table
                      SET user_todo_id = -1
                      WHERE user_todo_id = ' . $tomatoID;
              $retval = mysqli_query( $conn, $sql );
            }
            $res['state'] = 'error';
            $res['info'] = 'todoIDNotExist';
          }
        }
        else if($isStartTodo != 1) {
          // 如果不是从start进入的并且todo小于等于0
          $sql = 'UPDATE user_table
                  SET user_todo_id = -1
                  WHERE user_todo_id = ' . $tomatoID;
          $retval = mysqli_query( $conn, $sql );
          $res['state'] = 'error';
          $res['info'] = 'todoIDLessOrEqualTo0';
        }
        else {
          $res['state'] = 'error';
          $res['info'] = 'todoIDLessOrEqualTo0';
        }
        // 获取番茄信息部分结束！
        if (is_null($temptomato)) {
          $res['nowInTomato'] = 0;          
        }
        else {
          $res['nowInTomato'] = 1;
          $res['tomatoInfo'] = $temptomato;
        }
}
?>