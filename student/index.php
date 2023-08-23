<?php require_once($_SERVER['DOCUMENT_ROOT'].'/Exam_Registration_System/auth/userDataController.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php echo "Hello ".$_SESSION['username']; ?>
</body>
</html>