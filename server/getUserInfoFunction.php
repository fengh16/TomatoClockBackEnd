<?php
// 获取用户记录（每天的数据），提前给$todoOwnerId，结果放在$gottenUserInfo里面
// 如果要指定第$userInfoFromNo和$userInfoShownNum，就设置
$todoOwnerId = (int)$todoOwnerId;
if (isset($userInfoFromNo)) {
  $userInfoShownNum = (int)$userInfoShownNum;
  $userInfoFromNo = (int)$userInfoFromNo;
  $sql = "SELECT * from user_info_table 
          WHERE user_info_owner_id = $todoOwnerId
          ORDER by user_info_id DESC 
          LIMIT $userInfoFromNo, $userInfoShownNum";
}
else {
  $month = addslashes($month);
  $sql = "SELECT * from user_info_table 
          WHERE user_info_owner_id = $todoOwnerId AND date_format(user_info_date,'%Y-%m')='$month'
          ORDER by user_info_id DESC";
}
// 下面是开始查询
$gottenUserInfo = array();
$result = mysqli_query( $conn, $sql );
while($row = mysqli_fetch_array($result))
{
  array_push($gottenUserInfo,array (
    "user_info_times"=>(int)$row["user_info_times"],
    "user_info_time"=>(int)$row['user_info_time'],
    "user_info_date"=>$row["user_info_date"]
  ));
}
?>