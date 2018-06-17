<?php
// 获取todo列表，之前已经设定了变量$todoOwnerId（用户的id），默认返回未完成todo的后10项（最近）和没有完成todo的后10项（最近）
// 如果要指定第$todoFromNo和$todoShownNum，就设置
if (isset($largerThanThisId)) {
  $largerThanThisId = (int)$largerThanThisId;
  $sqlNotFinished =  "SELECT * from todo_table 
                      WHERE todo_state = 1 AND todo_owner_id = $todoOwnerId AND todo_id > $largerThanThisId
                      ORDER by todo_id DESC";
  $sqlFinished = "SELECT * from todo_table 
                  WHERE todo_state = 2 AND todo_owner_id = $todoOwnerId AND todo_id > $largerThanThisId
                  ORDER by todo_id DESC";
}
else {
  $todoShownNum = (int)$todoShownNum;
  if (isset($startIdNotIncluded)) {
    $startIdNotIncluded = (int)$startIdNotIncluded;
    $sqlNotFinished =  "SELECT * from todo_table 
                        WHERE todo_state = 1 AND todo_owner_id = $todoOwnerId AND todo_id < $largerThanThisId
                        ORDER by todo_id DESC 
                        LIMIT $todoShownNum";
    $sqlFinished = "SELECT * from todo_table 
                    WHERE todo_state = 2 AND todo_owner_id = $todoOwnerId AND todo_id > $largerThanThisId
                    ORDER by todo_id DESC 
                    LIMIT $todoShownNum";
  }
  else {
    $todoFromNo = (int)$todoFromNo;
    $sqlNotFinished =  "SELECT * from todo_table 
                        WHERE todo_state = 1 AND todo_owner_id = $todoOwnerId
                        ORDER by todo_id DESC 
                        LIMIT $todoFromNo, $todoShownNum";
    $sqlFinished = "SELECT * from todo_table 
                    WHERE todo_state = 2 AND todo_owner_id = $todoOwnerId
                    ORDER by todo_id DESC 
                    LIMIT $todoFromNo, $todoShownNum";
  }
}
// 下面是开始查询
$gottenTodoInfo = array (
  'notFinished' => array(),
  'finished' => array()
);
$result = mysqli_query( $conn, $sqlNotFinished );
while($row = mysqli_fetch_array($result))
{
  array_push($gottenTodoInfo['notFinished'],array (
    "todo_state"=>(int)$row["todo_state"],
    "todo_id"=>(int)$row["todo_id"],
    "todo_shown_name"=>$row["todo_shown_name"],
    "todo_tar_num"=>(int)$row["todo_tar_num"],
    "todo_complete_num"=>(int)$row["todo_complete_num"],
    "todo_type"=>(int)$row["todo_type"],
    "todo_run_time"=>(int)$row["todo_run_time"],
    "todo_rest_time"=>(int)$row["todo_rest_time"]
  ));
}
$result = mysqli_query( $conn, $sqlFinished );
while($row = mysqli_fetch_array($result))
{
  array_push($gottenTodoInfo['finished'],array (
    "todo_state"=>(int)$row["todo_state"],
    "todo_id"=>(int)$row["todo_id"],
    "todo_shown_name"=>$row["todo_shown_name"],
    "todo_tar_num"=>(int)$row["todo_tar_num"],
    "todo_complete_num"=>(int)$row["todo_complete_num"],
    "todo_type"=>(int)$row["todo_type"],
    "todo_run_time"=>(int)$row["todo_run_time"],
    "todo_rest_time"=>(int)$row["todo_rest_time"]
  ));
}
?>