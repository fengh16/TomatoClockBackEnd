<?php
// 我知道这样复制来复制去很不优雅……但是时间太紧了2333只能这样玩了
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
    // 如果没有part
    if ($row['user_par_id'] > 0) {
      // Just copy from login.php
      $userParId = $row['user_par_id'];
      $sql = "SELECT *
              FROM user_table
              where user_id= $userParId";
      $result = mysqli_query( $conn, $sql );
      $rows = mysqli_num_rows($result);

      if ($rows) {
        $row = mysqli_fetch_array($result);
        // 如果没有part
        if ($row['user_par_id'] == $userId) {
          // 现在可以了！
          $sql = "UPDATE user_table
                  SET user_par_id = -1
                  where user_id= $userParId";
          $retval = mysqli_query( $conn, $sql );
          $sql = "UPDATE user_table
                  SET user_par_id = -1
                  where user_id= $userId";
          $retval = mysqli_query( $conn, $sql );
          $res['state'] = "ok";
          $res['info'] = "disconnected";
        }
        else {
          // 服务器内部错误，对方居然不是和自己配对！
          $sql = "UPDATE user_table
                  SET user_par_id = -1
                  where user_id= $userId";
          $retval = mysqli_query( $conn, $sql );
          $res['info'] = "errorPartPartIsntYou";
        }
      }
      else {
        // 对方不存在，自己单相思
        $sql = "UPDATE user_table
                SET user_par_id = -1
                where user_id= $userId";
        $retval = mysqli_query( $conn, $sql );
        $res['info'] = "errorPartNotExist";
      }  

    }
    else {
      // 单身狗就不要妄想解除配对了~
      $res['info'] = "errorYouDontHavePart";
    }

  }

  mysqli_close($conn);
  echo json_encode($res);  

?>
