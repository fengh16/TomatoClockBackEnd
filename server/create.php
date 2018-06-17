<?php

  if(basename($_SERVER['PHP_SELF']) == 'create.php') {
    // 如果是直接通过浏览器访问
    header('HTTP/1.0 403 Forbidden');
    echo '你干啥勒，图谋不轨，谋财害命';
  }
  else {
    include 'db.php';
    // 创建数据表
    // 用于用户控制，使用：
    //      用户的table 和 用户信息的talbe 和 todolist的table 和 todolist信息的table
    //      用户的table中具有：用户ID、用户登陆ID、partener的ID、邀请ID、当前todo、完成计时次数、完成计时总时间。
    //      用户信息的table中具有：用户ID、自己的ID、完成计时次数、完成计时总时间、date。
    //      todolist的table中具有：todo状态、todoID、todo名称、todo目标次数、todo完成次数、todo形式（正计时、倒计时）、todo每次的时间、todo每次的休息时间、todo主人id。
    //      todolist信息的table中具有：todoID、自己的ID、这个todo第几个数据、开始时间、是否完成、结束时间、番茄剩余时间、总共时间。


    // 用户表
    $sql = "CREATE TABLE user_table( ".
        // 0. 用户ID：一直增长的自动计数的ID
        "user_id INT NOT NULL AUTO_INCREMENT, ".
        // 1. 用户OpenID：微信登陆时返回
        "user_login_id TEXT NOT NULL, ".
        // 2. 用户session：微信登陆时返回，之后一直带着这个东西来请求
        "user_session TEXT NOT NULL, ".
        // 3. parterID：类型与用户ID相同，因此不多说
        "user_par_id INT NOT NULL, ".
        // 4. 邀请ID
        "user_invite_id TEXT NOT NULL, ".
        // 5. 当前todoID：如果没有在todo之中，为-1
        "user_todo_id INT NOT NULL DEFAULT -1, ".
        // 6. 完成计时次数
        "user_times INT NOT NULL DEFAULT 0, ".
        // 7. 完成计时总时间
        "user_time INT NOT NULL DEFAULT 0, ".
        // 设定主键
        "PRIMARY KEY ( user_id ))ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
    $retval = mysqli_query( $conn, $sql );
    if(! $retval )
    {
        die('user数据表创建失败: ' . mysqli_error($conn));
    }
    echo "user数据表创建成功<br />";


    // 用户信息表（用于记录每一天的数据）
    $sql = "CREATE TABLE user_info_table( ".
        // 0. 用户ID
        "user_info_owner_id INT NOT NULL, ".
        // 1. 数据自己的ID
        "user_info_id INT NOT NULL AUTO_INCREMENT, ".
        // 2. 完成计时次数
        "user_info_times INT NOT NULL, ".
        // 3. 完成计时总时间
        "user_info_time INT NOT NULL, ".
        // 4. 数据日期
        "user_info_date DATE NOT NULL, ".
        // 设定主键
        "PRIMARY KEY ( user_info_id ))ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
    $retval = mysqli_query( $conn, $sql );
    if(! $retval )
    {
        die('userinfo数据表创建失败: ' . mysqli_error($conn));
    }
    echo "userinfo数据表创建成功<br />";


    // todolist表
    $sql = "CREATE TABLE todo_table( ".
        // 0. todo状态：一个整数，1表示未完成，2表示已完成
        "todo_state INT NOT NULL, ".
        // 1. todoID：一直增长的自动计数的ID
        "todo_id INT NOT NULL AUTO_INCREMENT, ".
        // 2. todo名称：用于显示，最大长度98，可以重复
        "todo_shown_name VARCHAR(100) NOT NULL, ".
        // 3. todo目标次数：一个整数
        "todo_tar_num INT NOT NULL, ".
        // 4. todo完成次数：一个整数
        "todo_complete_num INT NOT NULL, ".
        // 5. todo形式：一个整数，1表示正计时，2表示倒计时（普通番茄钟就是倒计时）
        "todo_type INT NOT NULL, ".
        // 6. todo每次的时间：分钟，正计时时无效
        "todo_run_time INT NOT NULL, ".
        // 7. todo每次的休息时间：分钟
        "todo_rest_time INT NOT NULL, ".
        // 8. todo主人id
        "todo_owner_id INT NOT NULL, ".
        // 设定主键
        "PRIMARY KEY ( todo_id ))ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
    $retval = mysqli_query( $conn, $sql );
    if(! $retval )
    {
        die('todo数据表创建失败: ' . mysqli_error($conn));
    }
    echo "todo数据表创建成功<br />";


    // todolist信息表（用于记录每一个todo完成的详细数据）
    $sql = "CREATE TABLE todo_info_table( ".
        // 0. todoID
        "todo_info_owner_id INT NOT NULL, ".
        // 1. 数据自己的ID
        "todo_info_id INT NOT NULL AUTO_INCREMENT, ".
        // 2. 这个todo第几个数据：从0开始
        "todo_info_no INT NOT NULL, ".
        // 3. 开始时间
        "todo_info_start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ".
        // 4. 是否完成：1表示已完成，2表示暂停，0表示正在进行
        "todo_info_finished INT NOT NULL DEFAULT 0, ".
        // 5. 结束时间；如果没有完成，则为最后一次同步时间或者最后一次暂停时间
        "todo_info_stop_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ".
        // 6. 还剩余的时间：在最后一次同步/暂停/结束时间之后还剩下多长时间（秒）
        "todo_info_left_time_sec INT NOT NULL, ".
        // 7. 这个番茄总共的时间：在番茄钟设置时显示
        "todo_info_total_time_sec INT NOT NULL, ".
        // 设定主键
        "PRIMARY KEY ( todo_info_id ))ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
    $retval = mysqli_query( $conn, $sql );
    if(! $retval )
    {
        die('todoinfo数据表创建失败: ' . mysqli_error($conn));
    }
    echo "todoinfo数据表创建成功<br />";

    mysqli_close($conn);
  }

?>