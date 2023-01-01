<?php 
///////////////////// SETTINGS ////////////////////Ú
header('Content-Type: text/html; charset=utf-8');
$password = '1234';
$login = 'admin';
$filename = "timestamp_logger.txt";
$message = "";
$main_file = 'logger.php';



function generate_css()
{
    $css = 'body {
        height: 100%;
        }';
        
    $css .= 'textarea {
        width: 100%;
        height: 30%;
        }';
    
    return $css;
}

function generate_login_form($main_file)
{
    $page = '
    <body bgcolor="#121212">
    <form action='.$main_file.' method="post">
    <table style="border:1px; position: absolute; top: 50%; left: 50%; margin: -120px 0 0 -180px;" cellpadding="0px" cellspacing="12px" bgcolor="#dddddd">
    <tr><td><label for="login">Login:</label></td><td>
         <input type="text" name="login"></td></tr>
    <tr><td>
    <label for="password">Password:</label></td>
    <td>
    <input type="password" name="password"></td>
    </tr>
    <tr><td>
        <input type="submit" name="submit" value="Login" />
        </td></tr>
    </form></body>';
    return $page;
    
}


function encrypt($text, $password)
{
    //
    $ciphering = "AES-128-CTR";
    $iv_length = openssl_cipher_iv_length($ciphering);
    $encryption_iv = '1334527861031124';
    $options = 0;
    $encryption_key = $password;
    $encryption = openssl_encrypt($text, $ciphering, $encryption_key, $options, $encryption_iv);
    return $encryption;
}

function decrypt($text, $password)
{
    //
    $ciphering = "AES-128-CTR";
    $iv_length = openssl_cipher_iv_length($ciphering);
    $encryption_iv = '1334527861031124';
    $options = 0;
    $encryption_key = $password;
    $decryption=openssl_decrypt($text, $ciphering, $encryption_key, $options, $encryption_iv);
    return $decryption;
}

function write($filename, $content)
{
    // this function add new line to existing file or create new text file and set permission
    $fp = fopen($filename, 'a');
    fwrite($fp, $content);
    chmod($filename, 0777);
}

function read($filename)
{
    $content = '';
    if(file_exists($filename))
    {
        $content = file_get_contents($filename);
    }else
    {
        file_put_contents($filename, '');
    }
    return $content;
}


////////////////////////////////////// MAIN ///////////////////////////////////////////////
header('Content-Type: text/html; charset=utf-8');

$Now = new DateTime('now', new DateTimeZone('Europe/Prague'));
$head = '<html><head><style>'.generate_css().'</style></head><body>';
echo $head;

// authenticate
if(!( (isset($_POST["login"]))  &  (isset($_POST["password"])) )  )
{
    
    echo generate_login_form($main_file);
    
}else
{
    if(($_POST["login"] == $login) & ($_POST["password"] == $password))
    {
        if(isset($_POST['zprava']))
        {
            $message = $_POST['zprava'];
            $timestamp = '['.$Now->format('Y-m-d H:i:s').']';
    
            $content = file_get_contents($filename);  
            $content = decrypt($content, $password);
            $content .= '<b>'.$timestamp.'</b><br> '.$message.' <br>'.PHP_EOL;
    
            $content = encrypt($content, $password);    
            file_put_contents($filename,$content);
        
            unset($_POST['zprava']);
        }
    
        $content = read($filename);
        $content = decrypt($content, $password);
        
        // if send "clear" text, then reset datafile and logout
        if($message == "clear")
        {   
            $content = '';
            $content = encrypt($content, $password);
            file_put_contents($filename,$content);
            header("Location: ".$main_file);
        }
        
        // if send "logout" text, then logout session
        if($message == "logout")
        {
            header("Location: ".$main_file);
        }
        
        
    
        // translate special characters ("\n" -> <br>)
        $content = nl2br($content);
    
        echo '<div style="height:60%;width:100%;border:solid 1px gray;overflow:scroll;overflow-x:hidden;overflow-y:scroll;">
            <p style="height:200px;">'.$content.'</p>
            </div>';

        echo '
            <form action="'.$main_file.'" method="post">
            <textarea class="text" name="zprava" width="100%" id="zprava" value=""></textarea><br>
            <input type="hidden" name="password" value="'.$password.'">
            <input type="hidden" name="login" value="'.$login.'">
            <input type="submit" value="Write">';

    
        echo '</body></html>';       
    
    }else
    {
        echo generate_login_form($main_file);
    }
}















?>
