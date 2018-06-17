<?php

  if(basename($_SERVER['PHP_SELF']) == 'db.php') {
    // 如果是直接通过浏览器访问
    header('HTTP/1.0 403 Forbidden');
    echo '你干啥勒，图谋不轨，谋财害命';
  }
  else {
    include 'config.php';

    $dbhost = $mysqlconfig['host'] . ":" . $mysqlconfig['port'];
    $dbuser = $mysqlconfig['user'];
    $dbpass = $mysqlconfig['pass'];
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
    if(! $conn )
    {
        die('Could not connect: ' . mysqli_error());
    }
    
    mysqli_select_db($conn, $mysqlconfig['db']);
    // 设置编码，防止中文乱码
    mysqli_query($conn , "set names utf8");

    function getUserTomatoInfo( $parId, $myId ) {
      global $conn;
      $sql = "SELECT *
        FROM user_table
        where user_id=$parId AND user_par_id=$myId";
      $result = mysqli_query( $conn, $sql );
      $rows = mysqli_num_rows($result);
      $returnFunc = array();
      if ($rows) {
        $row = mysqli_fetch_array($result);
        // 获取当前的用户信息（nowInTomato, tomatoInfo, totalClockNums, totalTime, userInfo）
        if ((int)$row['user_todo_id'] <= 0) {
          $returnFunc['nowInTomato'] = 0;
        }
        else {
          $returnFunc['nowInTomato'] = 1;
          // 现在获取tomatoInfo
          $tomatoID = (int)$row['user_todo_id'];
          $sql = "select * from todo_table where todo_id = " . $tomatoID;
          $result = mysqli_query( $conn, $sql );
          $rows = mysqli_num_rows($result);
          $temptomato = array();
          if ($rows) {
            // 找到了todo对应的项
            $row = mysqli_fetch_array($result);
            $completeNum = (int)$row['todo_complete_num'];
            $todoInfo = array(
              "todo_shown_name"=>$row['todo_shown_name'],
            );
            //if ($row['todo_type'] != 1) {
            if (1 == 1) {
              // 如果是倒计时
              if ($row['todo_state'] == 1) {
                // 已完成了就不管了
                $sql = "select * from todo_info_table where todo_info_owner_id = " . $tomatoID . " AND todo_info_no = " . $completeNum;
                $result = mysqli_query( $conn, $sql );
                $rows = mysqli_num_rows($result);
                if ($rows) {
                  // 找到了todo对应的tomato！
                  $row = mysqli_fetch_array($result);
                  $temptomato['startTime'] = $row['todo_info_stop_time'];
                  $temptomato['nowTime'] = date('Y-m-d H:i:s', (time() + 8*3600));// 新浪云这里不需要调时区，但是腾讯云需要
                  $temptomato['leftTime'] = (int)$row['todo_info_left_time_sec'];
                  $temptomato['totalTime'] = (int)$row['todo_info_total_time_sec'];
                  $temptomatotimeBetween = strtotime($temptomato['nowTime']) - strtotime($temptomato['startTime']);
                  if ($row['todo_info_finished'] == 0) {
                    $temptomato['state'] = 'running';
                    if ($temptomatotimeBetween > $temptomato['leftTime'] + 50) {
                      // 已经过期
                      $temptomato['state'] = 'timeout';
                    }
                    else if ($temptomatotimeBetween >= $temptomato['leftTime']) {
                      $temptomato['state'] = 'finished';
                    }
                  }
                  else if ($row['todo_info_finished'] == 2) {
                    $temptomato['state'] = 'paused';
                  }
                  else {
                    $temptomato['state'] = 'finished';
                  }
                }
                else {
                  // 现在发现todo对应的tomato不存在！
                  $temptomato['state'] = "errorTomatoInfo";
                }
              }
              else {
                // 现在发现todo已经完成了
                $temptomato['state'] = "errorTodoCompleted";
              }
            }
            else {
              // 正计时还没有考虑……
              $temptomato['state'] = "error1NotEqual1";
            }
          }
          else {
            $temptomato['state'] = "errorTodoInfo";
          }
          $returnFunc['tomatoInfo'] = $temptomato;
        }
        $returnFunc['totalClockNums'] = (int)$row['user_times'];
        $returnFunc['totalTime'] = (int)$row['user_time'];
        $returnFunc['todoInfo'] = $todoInfo;
      }
      return $returnFunc;
    }
  
  }

?>