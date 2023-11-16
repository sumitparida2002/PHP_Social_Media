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
if (!isset($_SESSION["id"])) {
    header('Location: Welcome.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitBtn'])) {





    $title = $_POST["title"];
    $desc = $_POST['desc'];
    $userId = $_SESSION["id"];
    $access = $_POST["accessibility"];

    var_dump($title, $desc, $userId, $access);

    $insertStatement = $myPdo->prepare("INSERT INTO album (Title, Description,Owner_Id, Accessibility_Code) VALUES (:Title, :Desc, :OID, :ACode)");

    $insertStatement->execute([':Title' => $title, ':Desc' => $desc, ':OID' => $userId, ':ACode' => $access]);
}

session_start();

?>
<div class="container">
    <h1>Create New Album Selection</h1>
    <p>Welcome <?php echo $_SESSION["name"] ?>! (not you? change user <a href="Logout.php">here</a>), the following are your current registrations</p>

    <form action="AddAlbum.php" method="post">
        <label class="form-label" for="title">Title:</label>
        <input class="form-control" type="text" id="title" name="title" value="<?php echo $titleValue; ?>">
        <p class='text-danger'><?php echo $titleError; ?></p>
        <p class='text-danger'><?php echo $alreadyExistError; ?></p>

        <label class="form-label" for="accessibility">Accessibility:</label>
        <select class="form-control" style="margin-bottom: 8px;" id="accessbility" name="accessibility">

            <?php
            $sql = "SELECT Accessibility_Code, Description FROM accessibility";
            $stmt = $myPdo->query($sql);

            $selectedSemester = isset($_POST['accessibility']) ? $_POST['accessibility'] : '';

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = ($row['Accessibility_Code'] == $selectedSemester) ? 'selected' : '';
                echo "<option  value='" . $row['Accessibility_Code'] . "' $selected>" . $row['Description'] . "</option>";
            }
            ?>
        </select>
        <br />

        <label class="form-label" for="desc">Description:</label>
        <br />
        <textarea class="form-control" name="desc" id="desc" rows="5" style="resize:none"></textarea>


        <p class='text-danger'><?php echo $descError; ?></p>



        <input type="submit" class="btn btn-primary" name="submitBtn" id="submitBtn" value="Submit">




    </form>
</div>
<?php include('./common/footer.php'); ?>