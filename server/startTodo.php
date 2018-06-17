<?php
// 我知道这样复制来复制去很不优雅……但是时间太紧了2333只能这样玩了
// 其实就是找出来那个todo，然后如果找到了对应的tomato就执行（从暂停则开始执行，否则重新开始），否则就直接新建对应的tomato
  include 'db.php';

  $invite = addslashes($_GET['invite']);
  $session = addslashes($_GET['session']);
  $todoNo = (int)$_GET['todoNum'];
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
    $res['info'] = "started";
    $res['totalTime'] = (int)$row['user_time'];
    $res['totalClockNums'] = (int)$row['user_times'];
    $userParId = $row['user_par_id'];
    $finished = 0;
    $todoIdNow = $row['user_todo_id'];
    if ($row['user_todo_id'] < 0) {
      $res['nowInTomato'] = 0;
    }
    // 现在获取番茄信息！
    $isStartTodo = 1;
    $isPause = 0;
    $stopTomatoSign = 1;
    include 'gettomato.php';
    // 这里不需要finish的判断！
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
