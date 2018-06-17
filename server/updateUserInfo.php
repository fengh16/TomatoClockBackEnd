<?php
// 在用户 $userIdForUpdate 对应的当天数据中，时间加上 $tomatoTotalTimeRec ，次数加上1
// 当天定义为：这一天凌晨三点 到 第二天凌晨三点
$userIdForUpdate = (int)$userIdForUpdate;
$tomatoTotalTimeRec = (int)$tomatoTotalTimeRec;
$updateUserInfoTime = time() + 8 * 3600 - 3 * 3600;// 新浪云不需要调时区，腾讯云需要
$updateUserInfoDate = date('Y-m-d', $updateUserInfoTime);
$sql = "SELECT *
        FROM user_info_table
        where user_info_date='$updateUserInfoDate' AND user_info_owner_id=$userIdForUpdate";
$result = mysqli_query( $conn, $sql );
$rows = mysqli_num_rows($result);

if ($rows) {
  $row = mysqli_fetch_array($result);
  $userNewTime = $tomatoTotalTimeRec + (int)$row['user_info_time'];
  $userNewTimes = 1 + (int)$row['user_info_times'];
  $sql = "UPDATE user_info_table
          SET user_info_times = $userNewTimes, user_info_time = $userNewTime
          where user_info_date='$updateUserInfoDate' AND user_info_owner_id=$userIdForUpdate";
  $retval = mysqli_query( $conn, $sql );
}
else {
  $sql = "INSERT INTO user_info_table ".
          "(user_info_owner_id, user_info_times,  user_info_time,       user_info_date) ".
          "VALUES ".
          "($userIdForUpdate,   1,                $tomatoTotalTimeRec,  '$updateUserInfoDate')";
  $result = mysqli_query( $conn, $sql );
}
?>
