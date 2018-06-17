<?php

  include 'db.php';

  $invite = addslashes($_GET['invite']);
  $session = addslashes($_GET['session']);
  if(isset($_GET["month"]))//是否存在
  {
    $month=addslashes($_GET["month"]);//存在
  }
  else {
    $userInfoFromNo = (int)$_GET['start'];
    $userInfoShownNum = (int)$_GET['num'];
  }
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

  if (rows) {
    // 这些东西都应该在传过来的时候检测一下，但是后端也得做好准备
    // 最多16长度
    $row = mysqli_fetch_array($result);
    $todoOwnerId = $row['user_id'];
    // 获取用户记录（每天的数据），提前给$todoOwnerId，结果放在$gottenUserInfo里面
    include 'getUserInfoFunction.php';
    $res['state'] = 'ok';
    $res['info'] = 'gotUserInfo';
    $res['userInfo'] = $gottenUserInfo;
  }

  mysqli_close($conn);
  echo json_encode($res);  

?>
