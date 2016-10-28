<?php
/**
 * Plugin SafeFNUtils
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca>
 */

 
/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_safefnutil extends DokuWiki_Admin_Plugin {

    var $type = '';
    var $go=false;
  
    /**
     * handle user request
     */
    function handle() {
        if(!extension_loaded ( mbstring)) {
              msg($this->getLang('mbstring') , 1);            
              return;
        }
    
      if (!isset($_REQUEST['cmd'])) return;   // first time - nothing to do

      $this->type = 'invalid';
      
      if (!checkSecurityToken()) return;
      if (!is_array($_REQUEST['cmd'])) return;
      $this->go=true;
      $this->type = key($_REQUEST['cmd']);
   
    }
 
    /**
     * output appropriate html
     */
    function html() {
      global $conf;  
   
      ptln("<p>" . $this->getLang('about')  . "<br />");
   

      ptln('<form action="'.wl($ID).'" method="post">');
      
      // output hidden values to ensure dokuwiki will return back to this plugin
      ptln('  <input type="hidden" name="do"   value="admin" />');
      ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
      formSecurityToken();
      ptln('<p style = "line-height:175%">');
      ptln('Convert UTF-8 to Dokuwiki Safe Endcoing:  <input type="submit" name="cmd[safe]"  value="'.$this->getLang('btn_safe').'" /><br />');  
      ptln('Convert Dokuwiki Safe Encoding to UTF-8:   <input type="submit" name="cmd[utf8]"   value="'.$this->getLang('btn_utf8').'" /> ');  
      ptln('</p></form>');
      ptln('Current encoding: ' .  $conf['fnencode'] ) ;
      if($this->go) ptln('<p> Converting to: '.htmlspecialchars($this->type).'<br /><span id="safeconvutil"></span></p>');
  
       ptln('<DIV style = "height:250px;overflow:scroll">');
       if($this->go)  $this->print_results();
       ptln('</DIV>');
    }
    function print_results() {
        
        
        global $conf;
        echo "<b>" . $conf['datadir'] . "</b><br>";
        $files = $this->recode($conf['datadir']);  
        echo "<br /><b>" . $conf['mediadir'] . "</b><br />";
        $media =  $this->recode($conf['mediadir'],true);         
        
         echo "<script type='text/javascript'>\n//<![CDATA[ \n"; 
            echo "var inner=document.getElementById('safeconvutil');"; 
            echo "inner.innerHTML='Converted $files  files , $media media'";
          echo "\n //]]> </script>\n";
    }
    
    function recode($dir, $nmedia = false) {
        static $safe__test = false; 
        static $x=0;
        if($nmedia) $x=0;
        static $type;
       if(!$type) {    
         $type = $this->type;     
    
      }
      
      if(!$safe__test) {        
          $safe__test = new SafeFN;
      }   
  
        $dh = opendir($dir);
        if(!$dh) return;
        while (($file = readdir($dh)) !== false) {
            if($file == '.' || $file == '..') continue;          
            if(is_dir("$dir/$file")) $this->recode("$dir/$file"); #recurse
            $enc =mb_detect_encoding("$dir/$file", 'auto');  
         
           if($type == 'safe' && $enc !='UTF-8') {                  
               continue;
            }                   
            else if($type == 'utf8') {    
           
                if(strpos("$dir/$file", '%') === false) continue;  
                if(strpos("$dir/$file", ']') === false) continue;  
                if(!$safe__test->validate_safe("$dir/$file")) continue;
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
/*  ------------  */

    function media_recode($dir) {
        static $safe__test = false; 
        static $x=0;
        static $type;
       if(!$type) {    
         $type = $this->type;     
    
      }
      
      if(!$safe__test) {        
          $safe__test = new SafeFN;
      }   
  
        $dh = opendir($dir);
        if(!$dh) return;
        while (($file = readdir($dh)) !== false) {
            if($file == '.' || $file == '..') continue;          
            if(is_dir("$dir/$file")) $this->recode("$dir/$file"); #recurse
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
    //      if(rename("$dir/$file","$dir/$new")) {  $x++; }                     # rename it
          
         echo "\n";
        }
        closedir($dh);
        return $x;
    }


    
}