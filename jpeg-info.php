<?php
/**
 * Creative Commons has made the contents of this file
 * available under a CC-GNU-GPL license:
 *
 * http://creativecommons.org/licenses/GPL/2.0/
 *
 * A copy of the full license can be found as part of this
 * distribution in the file COPYING.
 *
 * You may use the ccHost software in accordance with the
 * terms of that license. You agree that you are solely 
 * responsible for your use of the ccHost software and you
 * represent and warrant to Creative Commons that your use
 * of the ccHost software will comply with the CC-GNU-GPL.
 *
 * $Id: cc-host-data-dump.php 4123 2006-08-31 00:49:56Z kidproto $
 *
 * Copyright 2005-2006, Creative Commons, www.creativecommons.org.
 * Copyright 2006, Jon Phillips, jon@rejon.org.
 *
 * This script generates a large dump of all the audio submitted to this
 * project using different feed formats (rss, atom, etc). It can output
 * individual files with specific feed formats or all files. Check the usage
 * options for more information.
 *
 */
 
// Turn off Error Reporting
error_reporting ( 0 );

// Change: Allow this example file to be easily relocatable - as of version 1.11
$Toolkit_Dir = "./pjmt/";     // Ensure dir name includes trailing slash

// Hide any unknown EXIF tags
$GLOBALS['HIDE_UNKNOWN_TAGS'] = TRUE;

include $Toolkit_Dir . 'Toolkit_Version.php';
include $Toolkit_Dir . 'JPEG.php'; 
include $Toolkit_Dir . 'JFIF.php';
include $Toolkit_Dir . 'PictureInfo.php';
include $Toolkit_Dir . 'XMP.php';
include $Toolkit_Dir . 'Photoshop_IRB.php';
include $Toolkit_Dir . 'EXIF.php';


define("DEBUG", false);


/**
 * Prints usage help options.
 */
function print_help ()
{
    echo "\nThis app prints XMP file from a jpeg.\n\n",
         sprintf("Usage: \n\tphp %s [OPTIONS]... [-f] [JPEG]\n\n",
                 $_SERVER['argv'][0]),
         "Possible Arguments:\n\n",
         "  -h\t\t\tGet help for this commandline program.\n",
         "  -x\t\t\tPrint only info for xmp metadata.\n",
         /* "  -v\t\t\tTurn on verbosity level.\n", */
         "  -f [FILENAME]\t\tA jpeg image file of your choosing.\n",
         "\nPossible Usages: \n\n",
         "Example 1: This prints all file info from a JPEG.\n\n",
         sprintf("\tphp %s -f test.jpg\n\n",
         $_SERVER['argv'][0]), "\n",
         "Example 2: This prints XMP file info from a JPEG only.\n\n",
         sprintf("\tphp %s -xf test.jpg\n\n",
         $_SERVER['argv'][0]),
         "\n";
    exit(1);
}

/**
 * Prints a commandline message and exits this.
 *
 * @param string $msg A message for printing to stdout.
 * @param bool $verbose_enalbed true if printing messages to stdout.
 * @param bool $exit_enalbed true if exiting the app.
 */
function print_message ($msg, $exit_enabled = true)
{
    global $_GLOBALS;

    if (! $_GLOBALS['verbose'])
        if ( $exit_enabled ) 
            exit(1);

    if ( !empty($msg) )
        echo "$msg\n";
    else
        echo "ERROR in the script\n";

    if ( $exit_enabled ) 
        exit(1);
}



// main driver code

// parse command line options
$opt = getopt('hvxf:');

// if there are no arguments passed or -h option, then print help
if ( count($opt) == 0 || isset($opt['h']) && ! isset($opt['f']) )
    print_help();

// if ( isset($opt['v']) )
    $_GLOBALS['verbose'] = true;

if ( isset($opt['x']) )
    $_GLOBALS['only_xmp'] = true;
else 
    $_GLOBALS['only_xmp'] = false;

if ( ! file_exists($opt['f']) )
    print_message("The file, '" . $opt['f'] . "', does not exist.");

// TODO: Add file checks here

// operate on the file
$header_data = get_jpeg_header_data( $opt['f'] );
// read in xmp file as string


$jpeg_get_functions = 
    array('get_jpeg_intrinsic_values'   => 
                  array('title'     => 'Intrinsic Values',
                        'enabled'   => ! $_GLOBALS['only_xmp']),
          'get_jpeg_comment' => 
                  array('title'     => "Comments",
                        'enabled'   => ! $_GLOBALS['only_xmp']),
          'get_XMP_text' =>
                  array('title'     => "XMP Text",
                        'enabled'   => true),
          'get_Photoshop_IRB' =>
                  array('title'     => "Photoshop IRB",
                        'enabled'   => ! $_GLOBALS['only_xmp']),
          /* "Photoshop IPTC"      => 'get_Photoshop_IPTC', */
          'get_jpeg_App12_Pic_Info' =>
                  array('title'     => "App12 Pic Info",
                        'enabled'   => ! $_GLOBALS['only_xmp']),
          'get_JFIF' =>
                  array('title'     => "JFIF App0 Pic Info",
                        'enabled'   => ! $_GLOBALS['only_xmp'])
         );

foreach ($jpeg_get_functions as $jpeg_func_cb => $jpeg_func_array)
{
    // if not printing, go to next function
    if ( ! $jpeg_func_array['enabled'] )
        continue;

    $jpeg_func_title = &$jpeg_func_array['title'];
    echo "\n$jpeg_func_title\n\n";
    if ( $jpeg_func_ret = $jpeg_func_cb($header_data) )
        print_r( $jpeg_func_ret );
    else
        print_message("\tReturned FALSE or EMPTY on get with $jpeg_func_title",
                      false);
        
}

// Doing the same for functions that need $filename and not header_data
$jpeg_get_functions_fn =
    array('get_EXIF_JPEG' =>
                  array('title'     => "EXIF JPEG",
                        'enabled'   => ! $_GLOBALS['only_xmp']),
          'get_Meta_JPEG' =>
                  array('title'     => "Meta JPEG",
                        'enabled'   => ! $_GLOBALS['only_xmp']),
          'get_EXIF_TIFF' =>
                  array('title'     => "EXIF TIFF",
                        'enabled'   => ! $_GLOBALS['only_xmp'])
         );

foreach ($jpeg_get_functions_fn as $jpeg_func_cb => $jpeg_func_array)
{
    // if not printing, go to next function
    if ( ! $jpeg_func_array['enabled'] )
        continue;

    $jpeg_func_title = &$jpeg_func_array['title'];
    echo "\n$jpeg_func_title\n\n";
    if ( $jpeg_func_ret = $jpeg_func_cb($opt['f']) )
        print_r( $jpeg_func_ret );
    else
        print_message("\tReturned FALSE or EMPTY on get with $jpeg_func_title",
                      false);
}


exit(0);

?>
