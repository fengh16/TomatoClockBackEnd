<?php
        
if(basename($_SERVER['PHP_SELF']) == 'config.php') {
  // 如果是直接通过浏览器访问
  header('HTTP/1.0 403 Forbidden');
  echo '你干啥勒，图谋不轨，谋财害命';
}
else {
  // 新浪云
  // $mysqlconfig = array(
  //   'host' => SAE_MYSQL_HOST_M,
  //   'port' => SAE_MYSQL_PORT,
  //   'user' => SAE_MYSQL_USER,
  //   'db'   => SAE_MYSQL_DB,
  //   'pass' => SAE_MYSQL_PASS,
  // ); 
  // 腾讯云
  $mysqlconfig = array(
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'root',
    'db'   => 'cAuth',
    'pass' => 'appid',
    'char' => 'char'
  ); 
}

?>