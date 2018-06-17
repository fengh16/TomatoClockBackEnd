<?php

  include 'db.php';

  function subString_UTF8($str, $start, $lenth)
  {
    $len = strlen($str);
    $r = array();
    $n = 0;
    $m = 0;
    for($i = 0; $i < $len; $i++) {
      $x = substr($str, $i, 1);
      $a  = base_convert(ord($x), 10, 2);
      $a = substr('00000000'.$a, -8);
      if ($n < $start){
        if (substr($a, 0, 1) == 0) {
        }elseif (substr($a, 0, 3) == 110) {
            $i += 1;
        }elseif (substr($a, 0, 4) == 1110) {
            $i += 2;
        }
        $n++;
      }else{
        if (substr($a, 0, 1) == 0) {
          $r[ ] = substr($str, $i, 1);
        }elseif (substr($a, 0, 3) == 110) {
          $r[ ] = substr($str, $i, 2);
          $i += 1;
        }elseif (substr($a, 0, 4) == 1110) {
          $r[ ] = substr($str, $i, 3);
          $i += 2;
        }else{
          $r[ ] = '';
        }
        if (++$m >= $lenth){
          break;
        }
      }
    }
    return join($r);
  } // End subString_UTF8;

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

  if (rows) {
    // 这些东西都应该在传过来的时候检测一下，但是后端也得做好准备
    // 最多16长度
    $todoName = subString_UTF8(addslashes($_GET['todoName']),0,16);
    $doTime = $_GET['doTime'];
    $restTime = $_GET['restTime'];
    $todoTimes = $_GET['todoTimes'];
    $todoForm = $_GET['todoForm']; // 正计时1倒计时2
    
    if ($todoForm != 1) {
      // 默认倒计时所以如果不是1就设置成2
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
    if ($todoTimes < 1) {
      // 怕不是恶意攻击；如果次数太多就让他做去！！！！
      $todoTimes = 1;
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
      $res['info'] = "added!";
      $res['state'] = 'ok';
      $res['doTime'] = $doTime;
      $res['restTime'] =  $restTime;
      $res['todoTimes'] = $todoTimes;
      $res['todoForm'] = $todoForm;
      $res['todoName'] = $todoName;
    }
  }

  mysqli_close($conn);
  echo json_encode($res);  

?>
