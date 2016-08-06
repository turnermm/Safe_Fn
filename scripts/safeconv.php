<!DOCTYPE html>
<html lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction'] ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 </head>
 
<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once DOKU_INC . "inc/init.php";

if(empty($_SERVER['REMOTE_USER'])) {
  echo "<h3 style='font-family:arial'>You must be logged in as an  admin.</h3>" ; 
  $client = "";
}
else $client = $_SERVER['REMOTE_USER']; 


global $USERINFO;
 $groups = $USERINFO['grps'];
if($client && !in_array('admin',$groups)) {
   echo "<h3 style='font-family:arial'>You must be logged in as an  admin.</h3>" ;  
   $client = "";
}

if($client && isset($_REQUEST['cmd'])){ 

  $converted = recode($conf['datadir']);   
  echo  "<h3>$converted files/directories converted</h3>";
}

 function recode($dir) {
      static $safe__test = false; 
      static $x=0;
      static $type="";
      if(!$type) {          
          $type = strtolower($_REQUEST['cmd'][0]);
          $type= str_replace('-',"",$type);           
      }
      if(!$safe__test) {        
       $safe__test = new SafeFN;
      }   
        $dh = opendir($dir);
        if(!$dh) return;
        while (($file = readdir($dh)) !== false) {
            if($file == '.' || $file == '..') continue;          
            if(is_dir("$dir/$file")) recode("$dir/$file"); #recurse
            $enc =mb_detect_encoding("$dir/$file", 'auto');  
         
           if($type == 'safe' && $enc !='UTF-8') {                  
               continue;
            }                   
            else if($type == 'utf8') {    
                if(strpos("$dir/$file", '%') === false) continue;  
                if(strpos("$dir/$file", ']') === false) continue;                     
            }    
          
           $writable= is_writable ("$dir/$file") ? 'writable':   '<span style = "font-weight:bold; font-size:125%;color: red">not writable</span>';
           echo "$dir/$file ($enc) $writable<br />";
            if(!is_writable ("$dir/$file")) continue;           
           if($type == 'safe')  {          
           $new = cleanID($file);
           }
           else $new = $file;
           if(is_dir("$dir/$file")) echo "Directory: ";
           if($type == 'safe')  {         
           $new = $safe__test->encode($new);
               echo "Safe: $new<br />";                      
           }
          else {
                $new = $safe__test->decode($new);               
                echo "UTF-8: $new<br />";                      
          }
          if(rename("$dir/$file","$dir/$new")) {  $x++; }                     # rename it
          
         echo "\n";
        }
        closedir($dh);
        return $x;
    }

?>    



<p>This script will convert UTF-8 directory and filenames to Dokuwiki's safe encoding or safe encoding to UTF-8.<br />
 It must be run from <dokuwiki>/lib/exe by a user logged in with admin privileges.<br />
The URL: <?php echo htmlspecialchars('http://<your_domain>/<dokuwiki>/lib/exe/safeconv.php');?>
</p>
   <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
           Convert UTF-8 to Dokuwiki Safe Endcoing:  <input type="submit" name="cmd[]"  value="Safe" /> <br />  
          Convert Dokuwiki Safe Encoding to UTF-8:   <input type="submit" name="cmd[]"  value="UTF-8" />   
    </form>



