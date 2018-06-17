<?php

  include 'db.php';

  $invite = addslashes($_GET['invite']);
  $session = addslashes($_GET['session']);
  if (isset($_GET['largerThanThisId'])) {
    $largerThanThisId = (int)$_GET['largerThanThisId'];
  }
  else {
    if (isset($_GET['startIdNotIncluded'])) {
      $startIdNotIncluded = (int)$_GET['startIdNotIncluded'];
    }
    else {
      $todoFromNo = (int)$_GET['start'];
    }
    $todoShownNum = (int)$_GET['num'];
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
    include 'getTodoInfo.php';
    $res['state'] = 'ok';
    $res['info'] = 'gotTodoInfo';
    $res['todoInfo'] = $gottenTodoInfo;
  }

  mysqli_close($conn);
  echo json_encode($res);  

?>
