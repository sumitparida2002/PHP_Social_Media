<?php

define("ORIGINAL_IMAGE_DESTINATION", "originals");

define("IMAGE_DESTINATION", "/images");
define("IMAGE_MAX_WIDTH", 800);
define("IMAGE_MAX_HEIGHT", 600);

define("THUMB_DESTINATION", "/thumbnails");
define("THUMB_MAX_WIDTH", 100);
define("THUMB_MAX_HEIGHT", 100);
$supportedImageTypes = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);

// include("./common/Functions.php");

try {
    $dbConnection = parse_ini_file("db_connection.ini");
    extract($dbConnection);
    $myPdo = new PDO($dsn, $user, $password);
    $myPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
include("./common/header.php");

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitBtn'])) {
    $title = $_POST["title"];
    $desc = $_POST['desc'];
    $userId = $_SESSION["id"];
    $album = $_POST["album"];


    $destination = array();
    foreach ($_FILES['file']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['file']['error'][$key] == 0) {

            //upload Image
            $destinationPath = __DIR__ . DIRECTORY_SEPARATOR . ORIGINAL_IMAGE_DESTINATION;


            if (!file_exists($destinationPath)) {
                if (mkdir($destinationPath, 0777, true)) {
                    echo "Directory created successfully!";
                } else {
                    echo "Failed to create directory. Check permissions and paths.";
                }
            }


            $tempFilePath = $_FILES['file']['tmp_name'][$key];
            $filePath = $destinationPath . "/" . $_FILES['file']['name'][$key];

            $pathInfo = pathinfo($filePath);
            $dir = $pathInfo['dirname'];
            $fileName = $pathInfo['filename'];
            $ext = $pathInfo['extension'];

            $i = "";
            while (file_exists($filePath)) {
                $i++;
                $filePath = $dir . "/" . $fileName . "_" . $i . "." . $ext;
            }

            move_uploaded_file($tempFilePath, $filePath);

            $destination[$key] = $filePath;



            //Refactor Image to implement



        } elseif ($_FILES['file']['error'][$key] == 1) {
            $error = "Upload file is too large";
        } elseif ($_FILES['file']['error'][$key] == 4) {
            $error = "No upload file specified";
        } else {
            $error  = "Error happened while uploading the file. Try again late";
        }
    }

    foreach ($destination as $key => $filePath) {
        $insertStatement = $myPdo->prepare("INSERT INTO picture (Title, Description, Album_Id, File_Name) VALUES (:Title, :Desc, :AID, :FName)");

        $insertStatement->execute([':Title' => $title, ':Desc' => $desc, ':AID' => $album, ':FName' => $filePath]);
    }
}






?>

<div class="container">
    <h1>Upload Pictures</h1>
    <p>You can upload multiple pictures at a time by pressing the shift key while selecting pictures</p>
    <p>When uploading multiple pictures, the title and description field will be applied to all pictures.</p>
    <span class="text-danger"><?php echo $error; ?></span>

    <form action="UploadPictures.php" method="post" enctype="multipart/form-data">
        <label class="form-label" for="album">Upload to Album:</label>
        <select class="form-control" style="margin-bottom: 8px;" id="album" name="album">

            <?php


            $sql = "SELECT Album_Id, Title FROM album where Owner_Id=:OID";

            $stmt = $myPdo->prepare($sql);


            $stmt->execute([':OID' => $_SESSION['id']]);




            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                echo "<option value='" . $row['Album_Id'] . "'>" . $row['Title'] . "</option>";
            }
            ?>
        </select>
        <label class="form-label" for="file">Upload to Album:</label>
        <input class="form-control" type="file" accept=".jpeg, .gif, .png" name="file[]" multiple />

        <label class="form-label" for="title">Title:</label>
        <input class="form-control" type="text" id="title" name="title">

        <label class="form-label" for="desc">Description:</label>
        <textarea class="form-control" name="desc" id="desc" rows="5" style="resize:none"></textarea>







        <input type="submit" class="btn btn-primary" name="submitBtn" id="submitBtn" value="Submit">




    </form>
</div>

<?php include('./common/footer.php'); ?>