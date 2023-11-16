<?php
session_start();

function ValidateUID($UID)
{
    if (empty($UID)) {
        return "User ID is required.";
    }
    return "";
}

function ValidatePassword($password)
{
    if (empty($password)) {
        return "Password is required.";
    }
    return "";
}


$UIDError = $passwordError = $incorrectInfoError = "";

if (isset($_POST["submit"])) {

    $UID = $_POST['UID'];


    $Password = $_POST['password'];


    $UIDError = ValidateUID($UID);

    $passwordError = ValidatePassword($Password);


    if (empty($UIDError) && empty($passwordError)) {

        try {
            $dbConnection = parse_ini_file("db_connection.ini");
            extract($dbConnection);
            $myPdo = new PDO($dsn, $user, $password);

            $myPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            print "Not Connecting";
            die();
        }

        $hashedPassword = hash("sha256", $Password);



        $sqlSelect = "SELECT * FROM user where UserId = ? AND Password = ?";
        $stmt = $myPdo->prepare($sqlSelect);
        $stmt->execute([$UID, $hashedPassword]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);


        if (empty($row)) {
            $incorrectInfoError = "Inccorrect User ID and/or Password ";
        } else {
            $_SESSION["id"] = $row["UserId"];
            $_SESSION["name"] = $row["Name"];

            header('Location: MyFriends.php');
            exit();
        }
    }
}


include("./common/header.php");
?>
<div class="container">
    <h1>Log in </h1>
    <p>you need to <a href="http://localhost/CST8257Lab5/NewUser.php">sign up</a> if you are new user</p>

    <form method="post" action="Login.php" id="form">





        <p class='text-danger'><?php echo $incorrectInfoError; ?></p>


        <label class="form-label" for="UID">User ID:</label>
        <input class="form-control" type="text" id="UID" name="UID" value="<?php echo $UIDValue; ?>">
        <p class='text-danger'><?php echo $UIDError; ?></p>








        <label class="form-label" for="password">Password:</label>
        <input class="form-control" type="password" id="password" name="password" value=""><br>
        <p class='text-danger'><?php echo $passwordError; ?></p>





        <button type="submit" class="btn btn-primary" name="submit">Submit</button>


        <button class="btn btn-primary" id="clear" name="clear">Clear</button>


    </form>


    <script>
        const clearBtn = document.getElementById("clear");
        clearBtn.addEventListener("click", (e) => {

            document.getElementById("form").reset();
        });
    </script>

</div>
<?php include('./common/footer.php'); ?>