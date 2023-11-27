<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="text" name="a">
        <input type="submit" name="submit" value="Gửi">
    </form>

    <?php
        session_start();

        if (!isset($_SESSION['arr'])) {
            $_SESSION['arr'] = [];
        }

        if(isset($_POST['submit'])) {
            $a = $_POST['a'];

            array_push($_SESSION['arr'], $a);
            
            $solonnhat = max($_SESSION['arr']);
        }
        // unset($_SESSION['arr']);
        echo "Các giá trị đã push vào mảng:";
        print_r($_SESSION['arr']);
        if(isset($solonnhat)) {
            echo "Số lớn nhất là: " . $solonnhat;
        }
    ?>
</body>
</html>