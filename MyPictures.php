<?php
session_start();




try {
    $dbConnection = parse_ini_file("db_connection.ini");
    extract($dbConnection);
    $myPdo = new PDO($dsn, $user, $password);
    $myPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
include("./common/header.php");

$selectedImageId = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitComment'])) {
    $album = $_POST['album'];
    $comment = $_POST['comment'];
    $selectedImageId = isset($_POST['selectedImageId']) ? $_POST['selectedImageId'] : null;


    if (!empty($selectedImageId)) {
        var_dump($selectedImageId, $comment, $_SESSION['id']);
        $sql = "INSERT INTO comment (Author_Id, Picture_Id, Comment_Text) VALUES (:AID, :PID, :CT) ";
        $stmt = $myPdo->prepare($sql);
        $stmt->execute([':AID' => $_SESSION['id'], ':CT' => $comment, ":PID" => $selectedImageId]);
    }
}

$album = $_POST['album'];

$selectedImageId = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitId'])) {
    $selectedImageId = $_POST['selectedImageId'];
    $album = $_POST['album'];
}

?>

<div class="container">
    <h1>My Pictures</h1>



    <form method="post" action="MyPictures.php">
        <select class="form-control" style="margin-bottom: 8px;" id="album" name="album" onchange="triggerChange()">

            <?php


            $sql = "SELECT Album_Id, Title FROM album where Owner_Id=:OID";

            $stmt = $myPdo->prepare($sql);


            $stmt->execute([':OID' => $_SESSION['id']]);




            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = ($_POST["album"] == $row['Album_Id']) ? 'selected' : '';
                echo "<option value='" . $row['Album_Id'] . "' $selected>" . $row['Title'] . "</option>";
            }
            ?>
        </select>


        <input type="submit" hidden id="submitBtn">
    </form>

    <form method="post" action="MyPictures.php">
        <div class="row">
            <div class="col-md-9">

                <?php
                if (!empty($album)) {
                    // Fetch album details
                    $sql = "SELECT Album_Id, Title, Description FROM album where Album_Id=:AID";
                    $stmt = $myPdo->prepare($sql);
                    $stmt->execute([':AID' => $album]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo "<div id='descriptionArea'>" . $row['Description'] . "</div>";


                    $sql = "SELECT Picture_Id, File_Name FROM picture WHERE Album_Id=:AID";
                    $stmt = $myPdo->prepare($sql);
                    $stmt->execute([':AID' => $album]);
                    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);



                    if (!empty($images)) {
                        $mainImage = empty($_POST["mainImg"]) ? $images[0]['File_Name'] : $_POST["mainImg"];



                        echo "<div id='mainImageContainer'>";
                        echo "<img src='" . $mainImage . "'  style='max-height: 400px' alt='Main Image' id='mainImage'>";
                        echo "</div>";





                        echo "<div id='thumbnailContainer' class='d-flex flex-row'>";
                        foreach ($images as $image) {
                            echo "<img src='" . $image['File_Name'] . "' alt='Thumbnail' style='max-height: 200px; max-width: 80px; overflow-y: auto; ' onclick='changeMainImage(\"" . $image['File_Name'] . "\", " . $image['Picture_Id'] . ")'>";
                        }
                        echo "</div>";
                    }
                }
                ?>


                <input type="hidden" name="selectedImageId" id="selectedImageId" value="" />

                <input type="hidden" name="album" id="album" value=<?php echo $album ?> />

                <input type="hidden" name="mainImg" id="mainImg" value="" />

                <button type="submit" name="submitId" id="submitId" style="display: none;"></button>





            </div>
    </form>

    <div class="col-md-3">
        <?php
        if (!empty($_POST["album"])) {
            $sql = "SELECT Album_Id, Title, Description FROM album where Album_Id=:AID";

            $stmt = $myPdo->prepare($sql);


            $stmt->execute([':AID' => $_POST['album']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);


            echo "<div id='descriptionArea'>" . $row['Description'] . "</div>";
        }
        ?>


        <?php
        $sql = "SELECT Author_Id, Comment_Text From comment where Picture_Id=:PID ";
        $stmt = $myPdo->prepare($sql);
        $stmt->execute([":PID" => $selectedImageId]);
        if ($stmt->rowCount() > 0) {
            echo "<div id='commentsArea'>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $authorId = $row['Author_Id'];
                $commentText = $row['Comment_Text'];


                echo "<p><strong>Author:</strong> $authorId</p>";
                echo "<p><strong>Comment:</strong> $commentText</p>";
                echo "<hr>";
            }
            echo "</div>";
        } else {
            echo "<p>No comments yet.</p>";
        }
        ?>

        <form action="MyPictures.php" method="post">
            <textarea class="form-control" name="comment" id="comment" rows="5" style="resize:none" placeholder="Leave a comment"></textarea>

            <input type="hidden" name="selectedImageId" id="selectedImageId" value="<?php echo $_POST['selectedImageId']  ?>" />

            <input type="hidden" name="album" id="album" value="<?php echo $album ?>" />

            <input type="hidden" name="mainImg" id="mainImg" value="<?php echo $_POST['mainImg']  ?>" />



            <button name="submitComment" class="btn btn-primary mt-2">Submit Comment</button>
        </form>

    </div>
</div>
<div id="imageInfoArea">

</div>
</div>

<script>
    function triggerChange() {
        document.getElementById("submitBtn").click();
    }

    function changeMainImage(imagePath, imageId) {
        document.getElementById("mainImage").src = imagePath;
        document.getElementById("selectedImageId").value = imageId;
        document.getElementById("mainImg").value = imagePath;


        document.getElementById("submitId").click();


    }
</script>

<style>

</style>


<?php include('./common/footer.php'); ?>