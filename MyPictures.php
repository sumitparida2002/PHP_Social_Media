<?php

try {
    $dbConnection = parse_ini_file("db_connection.ini");
    extract($dbConnection);
    $myPdo = new PDO($dsn, $user, $password);
    $myPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
include("./common/header.php");



?>

<div class="container">
    <h1>My Pictures</h1>

    <form action="MyPictures.php">
        <select class="form-control" style="margin-bottom: 8px;" id="album" name="album" onchange="trigger()">

            <?php


            $sql = "SELECT Album_Id, Title FROM album where Owner_Id=:OID";

            $stmt = $myPdo->prepare($sql);


            $stmt->execute([':OID' => $_SESSION['id']]);




            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                echo "<option value='" . $row['Album_Id'] . "'>" . $row['Title'] . "</option>";
            }
            ?>
        </select>
    </form>

    <div class="row">
        <div class="col-md-9">
            <img id="selectedPicture" src="/Applications/XAMPP/xamppfiles/htdocs/Final/originals/CamScanner 09-28-2023 08.38_1.jpg" alt="Selected Picture" class="img-fluid">
            <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit...</p>
        </div>

        <div class="col-md-3">
            <div id="descriptionArea">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Laboriosam, rem qui amet doloribus soluta laborum blanditiis itaque repellat, tenetur voluptates adipisci quo, quidem iusto ea veritatis obcaecati voluptas! Explicabo, minima.</div>
            <div id="commentsArea"></div>
            <textarea class="form-control" name="desc" id="desc" rows="5" style="resize:none" placeholder="Leave a comment"></textarea>
            <button class="btn btn-primary mt-2">Submit Comment</button>
        </div>
    </div>
</div>

<script>
    function triggerChange() {
        document.getElementById("submitBtn").click();
    }
</script>

<?php include('./common/footer.php'); ?>