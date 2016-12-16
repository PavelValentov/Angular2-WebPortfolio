<?php
$tns = "  
(DESCRIPTION =
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = 13.81.116.201)(PORT = 1521))
    )
    (CONNECT_DATA =
      (SERVICE_NAME = OracleSrv)
    )
  )
       ";
$db_username = "system";
$db_password = "k93jvF2929jdF";
try{
    $conn = new PDO("oci:dbname=".$tns,$db_username,$db_password);
    echo "Connection by TNS: OK";

    $sql = "SELECT * FROM SiteUsers ORDER BY NAME";

    foreach ($conn->query($sql) as $row) {
        echo $row['ID'] . "\t";
        print $row['NAME'] . "\t";
        print $row['AGE'] . "\t";
        print $row['CITY'] . "\n";
        print $row['EMAIL'] . "\n";
    }


}catch(PDOException $e){
    echo ($e->getMessage());
}

echo '<p>';

$db_username = "system";
$db_password = "k93jvF2929jdF";

try{
    $conn = new PDO("oci:dbname=//13.81.116.201/OracleSrv",$db_username,$db_password);
    echo "Connection by service name: OK";
}catch(PDOException $e){
    echo ($e->getMessage());
}

?>