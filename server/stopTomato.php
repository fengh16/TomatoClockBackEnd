<?php
// 我知道这样复制来复制去很不优雅……但是时间太紧了2333只能这样玩了
// 其实停止，就只是在todo还在的情况下，把剩余时间恢复并且把状态设置为暂停hhh
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
    $row = mysqli_fetch_array($result);
		$userId = $row['user_id'];
    // Just copy from login.php
    $res['state'] = "ok";
    $res['info'] = "stopped";
    $res['totalTime'] = (int)$row['user_time'];
    $res['totalClockNums'] = (int)$row['user_times'];
    $userParId = $row['user_par_id'];
		$finished = 0;
		$todoIdNow = $row['user_todo_id'];
    if ($row['user_todo_id'] < 0) {
      $res['nowInTomato'] = 0;
    }
    else {
      // 现在获取番茄信息！
      $isStartTodo = 0;
      $isPause = 0;
      $stopTomatoSign = 1;
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
							WHERE user_id = $userId";
			$retval = mysqli_query( $conn, $sql );
      
      $userIdForUpdate = $userId;
      include 'updateUserInfo.php';
      
			// 修改返回info，说明现在已经完成
			$res['info'] = "finished";
		}  

    if ($userParId < 0) {
      $res['havePart'] = 0;
    }
    else {
      $res['havePart'] = 1;
      $res['partTomatoInfo'] = getUserTomatoInfo($userParId, $userId);
    }

  }

  mysqli_close($conn);
  echo json_encode($res);  

?>
