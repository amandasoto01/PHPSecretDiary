<?php
  session_start();
  $error = "";

  if(array_key_exists("Logout",$_GET)){
      unset($_SESSION);
      setcookie("id","",time()-60*60);
      $_COOKIE["id"] = "";
  }else if( (array_key_exists("id",$_SESSION) && $_SESSION['id']) || ( array_key_exists("id",$_COOKIE) && $_COOKIE['id'])){
    header("loggedinpage.php");
  }

  if(array_key_exists("submit",$_POST)){
    $link = mysqli_connect("localhost","root","","secretdi");

    if(mysqli_connect_error()){
      die("Database Connection error");
    }

    if(!$_POST['email']){
      $error.="An email address is required<br>";
    }

    if(!$_POST['password']){
      $error.="A password is required<br>";
    }

    if($error != ""){
      $error = "<p>There were error(s) in your form:</p>".$error;
    }else{

      if($_POST['signUp'] == '1'){
        $query = "SELECT id FROM `users` WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."' LIMIT 1";
        $result = mysqli_query( $link , $query );

        if(mysqli_num_rows($result) > 0){
          $error = "That email address is taken.";
        }else{
          $query = "INSERT INTO `users`(`email`,`password`) VALUES ('".mysqli_real_escape_string($link, $_POST['email'])."',
          '".mysqli_real_escape_string($link, $_POST['password'])."')";

          if(! mysqli_query($link, $query)){
              $error = "<p>Could not sign you up - please try again later.<p>";
          }else{
            $var = mysqli_insert_id($link);

            $pass="";
            $pass = md5(md5($var).$_POST['password']);

            $query = "UPDATE `users` SET password = '" . $pass . "' WHERE id =" .mysqli_insert_id($link)." LIMIT 1";

            mysqli_query($link, $query);

            $_SESSION['id'] = mysqli_insert_id($link);

            if($_POST['stayLoggedIn'] == '1' ){
              setcookie("id",mysqli_insert_id($link),time()+60*60*24*365);
            }

            header("Location: loggedinpage.php");

          }

        }
      }else{
          $query = "SELECT * FROM `users` WHERE email = '".mysqli_real_escape_string($link,$_POST['email'])."'";
          $result = mysqli_query($link,$query);
          $row = mysqli_fetch_array($result);

          if(isset($row)){
            $hashedPassword = md5(md5($row['id']).$_POST['password']);

            if($hashedPassword == $row['password']){
              $_SESSION['id'] = $roe['id'];
              if($_POST['stayLoggedIn'] == '1'){
                setcookie("id",$row('id'), time()+60*60*24*365);
              }
              header("Location: loggedinpage.php");
            }else{
                $error = "That email/password cambiantion could not be found.";
            }
          }else{
            $error = "That email/password cambiantion could not be found.";
          }
      }
    }
  }

?>

<div id="error"><?php echo $error; ?></div>

<form method="post">
  <input type="email" name="email" placeholder="Your Email">
  <input type="password" name="password" placeholder="Password">
  <input type="checkbox" name="stayLoggedIn" value=1>
  <input type="hidden" name="signUp" value="1">
  <input type="submit" name="submit" value="Sign up!">
</form>

<form method="post">
  <input type="email" name="email" placeholder="Your Email">
  <input type="password" name="password" placeholder="Password">
  <input type="checkbox" name="stayLoggedIn" value=1>
  <input type="hidden" name="signUp" value="0">
  <input type="submit" name="submit" value="Log in!">
</form>
