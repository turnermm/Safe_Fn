
 /**
  *  Safe_ascii is a Javascript implementation of Christopher Smith's php
  *  SafeFN class which was written for Dokuwiki 
  *  
  *  @copyright Myron Turner (C) GPL 2 or greater
  *  @author Myron Turner <turnermm@shaw.ca>
  */


	var plain = '-/_0123456789abcdefghijklmnopqrstuvwxyz'; // these characters aren't converted
	var pre_indicator = '%';
	var post_indicator = '.';

    /**
     * convert numbers from base 10 to base 36 and base 36 to base 10
     *
     * @param  string representing an integer or integer   num  number to be converted
     * @param  integer      from    base from which to convert
     * @param  integer      to      base to which to convert
     *   
     * @return  array   int    an array of unicode codepoints
     *
	 * @author   Myron Turner <turnermm02@shaw.ca>
     */

	function changeSafeBase(num, from, to)   {  
	      if(isNaN(from) || from < 2 || from > 36 || isNaN(to) || to < 2 || to > 36) {
	        throw (new RangeError("Illegal radix. Radices must be integers between 2 and 36, inclusive."));
	      }

	      num = parseInt(num, from);
	      if(from == 36) return num; 
	      return num.toString(to); 
	  }


    /**
     * convert a UTF8 string into an array of unicode code points  
     *
     * @param   UTF8 string 
     * @return  array   int    an array of unicode codepoints
     *
	 * @author   Myron Turner <turnermm02@shaw.ca>
     */

	function get_u_array(utf8str) {
	    var unicode_array = new Array();
		for (var i=0; i<utf8str.length; i++) {
	           unicode_array[i] = utf8str.charCodeAt(i);;
		}
	   return unicode_array;
	}


    /**
     * convert a 'safe_filename' string into an array of unicode codepoints
     *
     * @param   string         safe     a filename in 'safe_filename' format
     * @return  array   int    an array of unicode codepoints
     *
     * @author   Christopher Smith <chris@jalakai.co.uk>
     */
	function safe_to_unicode(safe) {
    	var unicode = new Array();
    	var regex = new RegExp('(?=[' + pre_indicator + '\\' + post_indicator + '])');        
    	var split_array = safe.split(regex);
    	var converted = false;

    	for (var i = 0; i<split_array.length; i++ ) {
    	    var sub = split_array[i];
    	    if (sub.charAt(0) != pre_indicator) { //  i.e. sub.charAt(0) != '%'
    	        var start = converted?1:0;             
    	        for (j=start; j < sub.length; j++) {
    	             unicode.push(sub.charCodeAt(j));
    	        }
    	        converted = false;
    	    } else if (sub.length==1) {
    	        unicode.push(sub.charCodeAt(0));
    	        converted = true;
    	    } else {
    	        unicode.push(32 +  changeSafeBase(sub.slice(1),36,10));
    	        converted = true;
    	    }
    	}

    	return unicode;
	}

	/**
	* convert an array of unicode codepoints into 'safe_filename' format
	*  
	* @param    array  int    $unicode    an array of unicode codepoints
	* @return   string        the unicode represented in 'safe_filename' format
	*
	* @author   Christopher Smith <chris@jalakai.co.uk>	 
	* @author   Myron Turner <turnermm02@shaw.ca>
	*/
	function unicode_to_safe(unicode) {
    	var safe = '';
    	var converted = false;
    	var plain_str = plain + post_indicator;

    	for (var i=0; i< unicode.length; i++) {
    	     codepoint = unicode[i];           
             var regex = new RegExp(String.fromCharCode(codepoint));
             var match = plain_str.match(regex);

             if (codepoint < 127 && match) {
    	        if (converted) {
    	            safe += post_indicator;
    	            converted = false;
    	        }
    	        safe += String.fromCharCode(codepoint);

    	    } else if (codepoint == pre_indicator.charCodeAt(0)) {
    	        safe += pre_indicator;
    	        converted = true;
    	    } else {                                       
    	        safe += pre_indicator + changeSafeBase((codepoint-32), 10, 36);   
    	        converted = true;
    	    }
    	}
        if(converted) safe += post_indicator;
    	return safe;
	}

  /**
     * Convert an UTF-8 string to a safe ASCII String
     *
     *
     * @param    string    filename     a utf8 string, should only include printable characters - not 0x00-0x1f
     * @return   string    an encoded representation of filename using only 'safe' ASCII characters
     *
  	 * @author   Myron Turner <turnermm02@shaw.ca>
     */
	function Safe_encode(filename) {      
         return unicode_to_safe(get_u_array(filename));
	}

    /**
     * decode a 'safe' encoded file name and return a UTF8 string
     *      
     * @param    string    filename     a 'safe' encoded ASCII string,
     * @return   string    decoded utf8 representation of $filename
     *
  	 * @author   Myron Turner <turnermm02@shaw.ca>
     */
       
	function Safe_decode(filename) {   

		var unic = safe_to_unicode(filename);
		var str = new Array();
		for (var i=0; i < unic.length; i++) {
			str[i] = code2utf(unic[i]);
		}
          return utf8Decode(str.join('')); 
       
	}



/* UTF8 encoding/decoding functions
 * Copyright (c) 2006 by Ali Farhadi.
 * released under the terms of the Gnu Public License.
 * see the GPL for details.
 *
 * Email: ali[at]farhadi[dot]ir
 * Website: http://farhadi.ir/
 */

//an alias of String.fromCharCode
function chr(code)
{
	return String.fromCharCode(code);
}

//returns utf8 encoded character of a unicode value.
//code must be a number indicating the Unicode value.
//returned value is a string between 1 and 4 charachters.
function code2utf(code)
{
	if (code < 128) return chr(code);
	if (code < 2048) return chr(192+(code>>6)) + chr(128+(code&63));
	if (code < 65536) return chr(224+(code>>12)) + chr(128+((code>>6)&63)) + chr(128+(code&63));
	if (code < 2097152) return chr(240+(code>>18)) + chr(128+((code>>12)&63)) + chr(128+((code>>6)&63)) + chr(128+(code&63));
}

//it is a private function for internal use in utf8Decode function 
function _utf8Decode(utf8str)
{

	var str = new Array();
	var code,code2,code3,code4,j = 0;
	for (var i=0; i<utf8str.length; ) {
		code = utf8str.charCodeAt(i++);


		if (code > 127) code2 = utf8str.charCodeAt(i++);
		if (code > 223) code3 = utf8str.charCodeAt(i++);
		if (code > 239) code4 = utf8str.charCodeAt(i++);
		
		if (code < 128) str[j++]= chr(code);
		else if (code < 224) str[j++] = chr(((code-192)<<6) + (code2-128));
		else if (code < 240) str[j++] = chr(((code-224)<<12) + ((code2-128)<<6) + (code3-128));
		else str[j++] = chr(((code-240)<<18) + ((code2-128)<<12) + ((code3-128)<<6) + (code4-128));

	}
	return str.join('');
}

//Decodes a UTF8 formated string
function utf8Decode(utf8str)
{
	var str = new Array();
	var pos = 0;
	var tmpStr = '';
	var j=0;
	while ((pos = utf8str.search(/[^\x00-\x7F]/)) != -1) {
		tmpStr = utf8str.match(/([^\x00-\x7F]+[\x00-\x7F]{0,10})+/)[0];
		str[j++]= utf8str.substr(0, pos) + _utf8Decode(tmpStr);
		utf8str = utf8str.substr(pos + tmpStr.length);
	}
	
	str[j++] = utf8str;
	return str.join('');
}

/* Not Required for fckgLite file browser */
//it is a private function for internal use in utf8Encode function 
function _utf8Encode(str)
{	
	var utf8str = new Array();
	for (var i=0; i<str.length; i++) {
		utf8str[i] = code2utf(str.charCodeAt(i));
	}
	return utf8str.join('');
}

// Not Required for fckgLite file browser 
//Encodes a unicode string to UTF8 format.
function utf8Encode(str){

	var utf8str = new Array();
	var pos,j = 0;
	var tmpStr = '';
	
	while ((pos = str.search(/[^\x00-\x7F]/)) != -1) {
		tmpStr = str.match(/([^\x00-\x7F]+[\x00-\x7F]{0,10})+/)[0];
		utf8str[j++] = str.substr(0, pos);
		utf8str[j++] = _utf8Encode(tmpStr);
		str = str.substr(pos + tmpStr.length);
	}
	
	utf8str[j++] = str;
	return utf8str.join('');
}







