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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitBtn'])) {





    $fid = $_POST["FID"];
    $userId = $_SESSION["id"];

    $sqlFriend = "SELECT UserId, Name from user WHERE UserId=:FID";
    $stmtFriend = $myPdo->prepare($sqlFriend);
    $stmtFriend->execute([':FID' => $fid]);
    $rowFriend = $stmtFriend->fetch(PDO::FETCH_ASSOC);

    $userId = $_SESSION["id"];

    // checking Friendship Status if already
    $sqlAlreadyFriend = "SELECT * FROM friendship WHERE (Friend_RequesterId = :FID AND Friend_RequesteeId = :OID AND Status = 'accepted') OR (Friend_RequesterId = :OID AND Friend_RequesteeId = :FID AND Status = 'accepted')";
    $stmtAlreadyFriend = $myPdo->prepare($sqlAlreadyFriend);
    $stmtAlreadyFriend->execute([':FID' => $fid, 'OID' => $userId]);
    $rowAlreadyFriend = $stmtAlreadyFriend->fetch(PDO::FETCH_ASSOC);



    //checking pending requests
    $sqlRequestPending = "SELECT * FROM friendship WHERE (Friend_RequesterId = :FID AND Friend_RequesteeId = :OID AND Status = 'request')";
    $stmtRequestPending = $myPdo->prepare($sqlRequestPending);
    $stmtRequestPending->execute([':FID' => $fid, 'OID' => $userId]);
    $rowRequestPending = $stmtRequestPending->fetch(PDO::FETCH_ASSOC);





    if ($fid == $userId) {
        $message = "You can't send request to yourself";
    } else if (empty($rowFriend)) {
        $message = "This username does not exist";
    } else if (!empty($rowAlreadyFriend)) {
        $message = "You are already friends";
    } elseif (!empty($rowRequestPending)) {
        $sqlAcceptFriend = " UPDATE friendship
     SET Status = 'accepted'
        WHERE (Friend_RequesterId = :OID AND Friend_RequesteeId = :FID)
         OR (Friend_RequesterId = :FID AND Friend_RequesteeId = :OID)";
        $stmtAccept = $myPdo->prepare($sqlAcceptFriend);
        $stmtAccept->execute([':FID' => $fid, 'OID' => $userId]);
        $rowAccept = $stmtAccept->fetch(PDO::FETCH_ASSOC);
        $message = "You have become friends";
    } else {
        $sqlRequestSent = "SELECT * from friendship where (Friend_RequesterId=:OID and Friend_RequesteeId=:FID and Status='request')";
        $stmtSent = $myPdo->prepare($sqlRequestSent);
        $stmtSent->execute([":FID" => $fid, ":OID" => $userId]);
        $rowSent = $stmtSent->fetch(PDO::FETCH_ASSOC);
        if (empty($rowSent)) {
            $sqlSendRequest = "INSERT INTO friendship (Friend_RequesterId, Friend_RequesteeId, Status)
            VALUES (:OID, :FID, 'request')";
            $stmtSend = $myPdo->prepare($sqlSendRequest);
            $stmtSend->execute([":FID" => $fid, ":OID" => $userId]);
            $message = "Friend request sent";
        } else {
            $message = "Friend Already Request sent";
        }
    }
}

session_start();

?>
<div class="container">
    <h1>Create New Album Selection</h1>
    <p>Welcome <?php echo $_SESSION["name"] ?>! (not you? change user <a href="Logout.php">here</a>)</p>
    <p>Enter the ID of the user you want to be friend with</p>

    <form action="AddFriend.php" method="post">



        <label class="form-label" for="FID">ID:</label>
        <input class="form-control" type="text" id="FID" name="FID" value="<?php echo $UIDValue; ?>">

        <p class="text-danger"><?php echo $message ?></p>




        <input type="submit" class="btn btn-primary" name="submitBtn" id="submitBtn" value="Send Friend Request">




    </form>
</div>
<?php include('./common/footer.php'); ?>