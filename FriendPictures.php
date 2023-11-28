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

$friendId = isset($_GET['friendId']) ? $_GET['friendId'] : (isset($_POST['friendId']) ? $_POST['friendId'] : null);




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

// $album = isset($_GET['albumId']) ? $_GET['albumId'] : null;


$selectedImageId = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitId'])) {
    $selectedImageId = $_POST['selectedImageId'];
    $album = $_POST['album'];
}

?>

<div class="container">
    <h1>Friend Pictures</h1>



    <form method="post" action="FriendPictures.php">
        <select class="form-control" style="margin-bottom: 8px;" id="album" name="album" onchange="triggerChange()">

            <?php


            if (empty($friendId)) {
                $sql = "SELECT a.Album_Id, a.Title FROM album a 
    JOIN friendship f ON (a.Owner_Id = f.Friend_RequesterId OR a.Owner_Id = f.Friend_RequesteeId)
    WHERE (f.Friend_RequesterId = :UID OR f.Friend_RequesteeId = :UID)
    AND f.Status = 'Accepted' AND a.Owner_Id != :UID";
                $stmt = $myPdo->prepare($sql);


                $stmt->execute([':UID' => $_SESSION['id']]);
            } else {
                $sql = "SELECT a.Album_Id, a.Title FROM album a 
                JOIN friendship f ON (a.Owner_Id = f.Friend_RequesterId OR a.Owner_Id = f.Friend_RequesteeId)
                WHERE ((f.Friend_RequesterId = :UID AND f.Friend_RequesteeId = :friendId) OR
                       (f.Friend_RequesterId = :friendId AND f.Friend_RequesteeId = :UID))
                AND f.Status = 'Accepted' AND a.Owner_Id = :friendId";

                $stmt = $myPdo->prepare($sql);



                $stmt->execute([':UID' => $_SESSION['id'], ':friendId' => $friendId]);
            }








            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = ($_POST["album"] == $row['Album_Id']) ? 'selected' : '';
                echo "<option value='" . $row['Album_Id'] . "' $selected>" . $row['Title'] . "</option>";
            }
            ?>
        </select>

        <input type="hidden" name="friendId" value="<?php echo $friendId; ?>">
        <input type="submit" hidden id="submitBtn">
    </form>

    <form method="post" action="FriendPictures.php">
        <div class="row" style='margin-top: 10px;'>
            <div class="col-md-9 my-2">
                <div class="col-md-8 ">
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
                            echo "<img src='" . $mainImage . "' alt='Main Image' id='mainImage'>";
                            echo "</div>";





                            echo "<div id='thumbnailContainer' class='d-flex flex-row' style='overflow-x: auto; white-space: nowrap;'>";
                            foreach ($images as $image) {
                                $isActive = ($mainImage == $image['File_Name']) ? 'active' : '';

                                echo "<img src='" . $image['File_Name'] . "' alt='Thumbnail'  class='$isActive'  onclick='changeMainImage(\"" . $image['File_Name'] . "\", " . $image['Picture_Id'] . ")'>";
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
        $sql = "SELECT c.Author_Id, u.Name, c.Comment_Text FROM comment c
           JOIN user u ON c.Author_Id = u.UserId
           WHERE c.Picture_Id = :PID";
        $stmt = $myPdo->prepare($sql);
        $stmt->execute([":PID" => $selectedImageId]);
        if ($stmt->rowCount() > 0) {
            echo "<div id='commentsArea'>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $authorName = $row['Name'];
                $commentText = $row['Comment_Text'];


                echo "<p>$authorName:</strong> $commentText</p>";

                echo "<hr>";
            }
            echo "</div>";
        } else {
            echo "<p>No comments yet.</p>";
        }
        ?>

        <form action="FriendPictures.php" method="post">
            <textarea class="form-control" name="comment" id="comment" rows="5" style="resize:none" placeholder="Leave a comment"></textarea>

            <input type="hidden" name="selectedImageId" id="selectedImageId" value="<?php echo $_POST['selectedImageId']  ?>" />

            <input type="hidden" name="album" id="album" value="<?php echo $album ?>" />

            <input type="hidden" name="mainImg" id="mainImg" value="<?php echo $_POST['mainImg']  ?>" />



            <button name="submitComment" style='margin-top: 10px;' class="btn btn-primary ">Submit Comment</button>
        </form>

    </div>
</div>
<div id="imageInfoArea">

</div>
</div>

<style>
    #mainImage {
        width: 100%;
        max-height: 300px;
    }

    #thumbnailContainer {
        overflow-x: auto;
        white-space: nowrap;
        display: flex;


        max-width: 100%;
        overflow-y: hidden;
        margin-top: 10px;
    }

    #thumbnailContainer img {

        max-height: 80px;
        max-width: 80px;
        min-width: 80px;
        margin-right: 10px;
        border: 3px solid transparent;


    }

    #thumbnailContainer img.active {
        border-color: #007bff;

    }
</style>

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