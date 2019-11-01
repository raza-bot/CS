<?php
    require_once 'db_connection.php';
    require_once 'utilities.php';
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die($conn->connect_error);

    //Signs user up if everything is entered
    if(isset($_POST['SignUp'])){
        if(isset($_POST['mailuid']) && isset($_POST['username']) && isset($_POST['pwd']) && isset($_POST['first'])&& isset($_POST['last'])){
            $username = mysql_entities_fix_string($conn, $_POST['username']);
            $pass = mysql_entities_fix_string($conn, $_POST['pwd']);
            $email = mysql_entities_fix_string($conn, $_POST['mailuid']);
            $first = mysql_entities_fix_string($conn, $_POST['first']);
            $last = mysql_entities_fix_string($conn, $_POST['last']);
    
            $token = hash('ripemd128', $pass);
    
            $query = "INSERT INTO User(userName, firstName, lastName, email, passHash) VALUES ('$username', '$first', '$last', '$email', '$token')";
            $result = $conn->query($query);
            if(!$result){
                echo "<script type='text/javascript'>alert(\"ERROR\");</script><noscript>ERROR</noscript>";
            }
            else{
                $queryEmail = "SELECT * FROM User WHERE email='$email'";
                $result = $conn->query($queryEmail);
                if($result->num_rows){
                    $row = $result->fetch_array(MYSQLI_NUM);
                    $result->close();
                    $query = "INSERT INTO Customer(user_id, reserveNum) VALUES ('$row[0]', 0)";
                    $result = $conn->query($query);
                }
            }
        }    
    }

    echo <<<_END
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name=viewport content="width=device-width, intial-scale=1">
            <title>HMS Website</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <body>
    
        <header>
            <nav class="nav-header-main">
                <!-- Logo Image -->
                <a class="header-logo" href="index.php">
                    <img src="img/hotel_logo.PNG" alt="logo"> 
                </a>   
                <!-- Access buttons on top of page --> 
                <ul>
                <form action="index.php" method="post">
                    <li><button type="submit" name="room">Home</a></li>
                    <li><button type="submit" name="hotel">Hotels</button></li>
                    <li><a href="contact.php">Contact</a></li> 
                </form>
                </ul> 
            </nav>   
                <div class="header-login">
    _END;

    //Display Logout if signed in
    try{
        if(isset($_POST['login-submit'])){
            //Sanitizing
            $userOrEmail = mysql_entities_fix_string($conn, $_POST['mailuid']);
            $pass = mysql_entities_fix_string($conn, $_POST['pwd']);

            $query = "SELECT * FROM User WHERE email='$userOrEmail' OR userName='$userOrEmail'";
            $result = $conn->query($query);

            if (!$result) { 
                echo "<script type='text/javascript'>alert(\"Email or Password is Wrong!\");</script></script><noscript>Email or Password is Wrong!</noscript>";
                throw new Exception("Not Found");
            }
            else if($result->num_rows){
                login($result, $pass, $conn);
            }
            else{
                throw new Exception("Not Found");
            }
        }  
        else{
            throw new Exception("Not Login");
        }
    }
    catch(Exception $e){
    echo <<<_END
        <!-- Login  --> 
        <form action="index.php" method="post">
            <input type="text" name="mailuid" placeholder="Username/Email">
            <input type="password" name="pwd" placeholder="Password">
            <button type="submit" name="login-submit">Login</button>
            <button type="submit" name="signup-submit">Sign Up</button> 
            <a href="admin.php">Admin</a>
        </form>   
        </div>
    _END;
    }
    
    echo "</header></body>";

    //Display Sign up if user wants to signup
    if(isset($_POST['signup-submit'])){
        echo <<<_END
        <!-- Logout -->
        <body>
        <form action="index.php" method="post">
            <input type="text" name="first" placeholder="First Name">
            <input type="text" name="last" placeholder="Last Name">
            <input type="text" name="username" placeholder="Username">
            <input type="text" name="mailuid" placeholder="Email">
            <input type="password" name="pwd" placeholder="Password">
            <button type="submit" name="SignUp">Sign Up</button>                
        </form>
        </body>
    _END;
    }
    else if(isset($_POST['hotel'])){
        require "hotel_list.php";
    }
    else{
        require  "room_list.php";
    }

    require "footer.php";

    //Logs user in using MySQL
    function login($result, $pass, $conn){
        if($result->num_rows){
            $row = $result->fetch_array(MYSQLI_NUM);
            $result->close();
            $query = "SELECT * FROM Customer WHERE user_id='$row[0]'";
            $result = $conn->query($query);
            if($result->num_rows){
                $token = hash('ripemd128', $pass);
                if($token == $row[5]){
                    echo <<<_END
                        <!-- Logout -->
                        <p>Logged in as $row[2] $row[3]!</p>
                        <form action="index.php" method="post">
                            <button type="submit" name="logout-submit">Logout</button>                
                        </form>
                        </div>
                    _END;
                }
                else{
                    throw new Exception("Wrong Pass");
                }
            }
            else{
                throw new Exception("Not Customer");
            }
        }
    }
?>