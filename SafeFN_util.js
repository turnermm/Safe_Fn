/*
    These two functions are utility functions for  SafeFN_class.js.
    They accept an encoding parameter, which is the fnencode value
    set in the Dokuwiki configuration manager.  The will encode/decode
    based on that parameter.
   
*/

function dwikiUTF8_encodeFN(file, encoding){

    if(encoding == 'utf-8') return file;

    if(file.match(/^[a-zA-Z0-9\/_\-\.%\]]+$/)){
        return file;
    }

    if(encoding == 'safe'){
        return SafeFN_encode(file);
    }

    file =  encodeURIComponent(file);
    file =  file.replace(/%2F/g,'/');
    return file;
}


function dwikiUTF8_decodeFN(file, encoding){

    if(encoding == 'utf-8') return file;

    if(encoding == 'safe'){
        return SafeFN_decode(file);
    }

    return decodeURIComponent(file);
}

