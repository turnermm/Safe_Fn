<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once DOKU_INC . "inc/init.php";

if(empty($_SERVER['REMOTE_USER'])) {
  echo "<h3 style='font-family:arial'>You must be logged in as an  admin.</h3>" ; 
  $client = "";
}
else $client = $_SERVER['REMOTE_USER']; 

echo '<pre>';
global $USERINFO;
 $groups = $USERINFO['grps'];
if($client && !in_array('admin',$groups)) {
   echo "<h3 style='font-family:arial'>You must be logged in as an  admin.</h3>" ;  
   $client = "";
}

if($client && isset($_REQUEST['cmd'])){ 

  recode($conf['datadir']);   
}

 function recode($dir) {
      static $safe__test = false; 
      static $x=0;
      if(!$safe__test) {        
       $safe__test = new SafeFN;
      }   
        $dh = opendir($dir);
        if(!$dh) return;
        while (($file = readdir($dh)) !== false) {
            if($file == '.' || $file == '..') continue;          
            if(is_dir("$dir/$file")) recode("$dir/$file"); #recurse
            $enc =mb_detect_encoding("$dir/$file", 'auto');  
         
           if($enc !='UTF-8') { continue;}        
           
          
           $writable= is_writable ("$dir/$file") ? 'writable':   '<span style = "font-weight:bold; font-size:125%;color: red">not writable</span>';
           echo "$dir/$file ($enc) $writable\n";
            if(!is_writable ("$dir/$file")) continue;           
           $new = cleanID($file);
           if(is_dir("$dir/$file")) echo "Directory: ";
           $new = $safe__test->encode($new);
           echo "Safe: $new\n";                      
          
           if(rename("$dir/$file","$dir/$new"))echo "done\n";                     # rename it
          
         echo "\n";
        }
        closedir($dh);
    }

?>    


</pre>
<p>This script will convert UTF-8 directory and filenames to Dokuwiki's safe encoding.  It must be run from <dokuwiki>/lib/exe by a user with admin privileges.
The URL: <?php echo htmlspecialchars('http://<your_domain>/<dokuwiki>/lib/exe/safeconv.php');?>
</p>
   <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
          <input type="submit" name="cmd"  value="Convert UTF-8 -> Safe" />   
    </form>



