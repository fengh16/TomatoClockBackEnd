<?php
// 我知道这样复制来复制去很不优雅……但是时间太紧了2333只能这样玩了
  include 'db.php';

  $invite = addslashes($_GET['invite']);
  $session = addslashes($_GET['session']);
  $partInvite = addslashes($_GET['invitePart']);
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
    if ($invite != $partInvite) {
      $row = mysqli_fetch_array($result);
      $userId = $row['user_id'];
      // 如果没有part
      if ($row['user_par_id'] < 0) {
        // Just copy from login.php
        $sql = "SELECT *
                FROM user_table
                where user_invite_id='$partInvite'";
        $result = mysqli_query( $conn, $sql );
        $rows = mysqli_num_rows($result);

        if ($rows) {
          $row = mysqli_fetch_array($result);
          // 如果没有part
          if ($row['user_par_id'] < 0) {
            // 现在可以了！
            $sql = "UPDATE user_table
                    SET user_par_id = '$userId'
                    where user_invite_id='$partInvite'";
            $retval = mysqli_query( $conn, $sql );

            $userId = $row['user_id'];
            $sql = "UPDATE user_table
                    SET user_par_id = '$userId'
                    where user_invite_id='$invite'";
            $retval = mysqli_query( $conn, $sql );
            $res['state'] = "ok";
            $res['info'] = "connected";
          }
          else {
            $res['info'] = "errorPartAlreadyHavePartner";
          }
        }
        else {
          $res['info'] = "errorPartInviteNotExist";
        }  

      }
      else {
        $res['info'] = "errorYouAlreadyHavePartner";
      }
    }
    else {
      $res['info'] = "errorWithYourself";
    }
  }

  mysqli_close($conn);
  echo json_encode($res);  

?>
