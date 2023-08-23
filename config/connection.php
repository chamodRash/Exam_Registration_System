<?php
    $con = mysqli_connect('localhost', 'root', '', 'ers');

    if (!$con) {
        die("Connection Failed! ".mysqli_connect_error());
    }

?>