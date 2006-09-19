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
 * TODO: Add script for printing info about a jpeg file.
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
// include $Toolkit_Dir . 'PictureInfo.php';
include $Toolkit_Dir . 'XMP.php';
// include $Toolkit_Dir . 'Photoshop_IRB.php';
// include $Toolkit_Dir . 'EXIF.php';


define("DEBUG", false);


$test_xmp = 
"<rdf:Description rdf:about='uuid:dceba4c3-e699-11d8-94b2-b6ec48319f2d'
  xmlns:xapRights='http://ns.adobe.com/xap/1.0/rights/'>
<xapRights:Marked>True</xapRights:Marked>
<xapRights:WebStatement>http://ozhiker.com</xapRights:WebStatement>
</rdf:Description>";

/**
 * Prints usage help options.
 */
function print_help ()
{
    echo "\nThis app embeds an XMP xml file into a jpeg.\n\n",
         sprintf("Usage: \n\tphp %s [-f JPEG -x XMP FILE -o OUT FILE]\n\n",
                 $_SERVER['argv'][0]),
         "Possible Arguments:\n\n",
         "  -h\t\t\tGet help for this commandline program.\n",
         "  -v\t\t\tTurn on verbosity level.\n",
         "  -f [FILENAME]\t\tA jpeg image file of your choosing.\n",
         "  -x [FILENAME]\t\tAn XMP XML file of your choosing.\n",
         "  -o [FILENAME]\t\tThe file to output new jpeg.\n",
         "\nPossible Usages: \n\n",
         "Example 1: This embeds new XMP file into JPEG \n",
         "and outputs to new file. \n\n",
         sprintf("\tphp %s -f test.jpg -x test.xmp -o new.jpg\n\n",
         $_SERVER['argv'][0]),
         "Example 2: This does the same but is verbose about \n",
         "what is happening and prints message to commandline.\n\n",
         sprintf("\tphp %s -v -f test.jpg -x test.xmp -o new.jpg\n",
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

    print_help();
    if ( $exit_enabled ) 
        exit(1);
}

/**
 * Embeds content from an XMP file into a JPEG file. If there are any errors
 * this script exits with errorcode and/or if verbose is globally enabled,
 * outputs error messages.
 *
 * @param string $filename_jpeg Filename of a jpeg and any path to it.
 * @param string $filename_xmp Filename of a text file with XMP XML in it.
 * @param string $filename_out Filename of modified jpeg with new XMP.
 * @param bool $merge_xmp Merges old xmp in jpeg with new xmp.
 */
function embed_xmp ($filename_jpeg, $filename_xmp, $filename_out, $merge_xmp)
{
    if ( ! file_exists($filename_jpeg) )
        print_message("The file, '" . $filename_jpeg . "', does not exist.");
    if ( ! file_exists($filename_xmp) )
        print_message("The file, '" . $filename_xmp . "', does not exist.");
    if ( file_exists($filename_out) )
        print_message("The file, '" . $filename_out . "', already exists.");

    // TODO: Add file checks here

    // Get the jpeg file's header data
    $header_data = get_jpeg_header_data( $filename_jpeg );
    // read in xmp file as string
    $xmp_text_orig = get_XMP_text( $header_data );
    $xmp_text_new = file_get_contents( $filename_xmp );

    if ( $merge_xmp )
    {
        $xmp_merged = 
            merge_xmp_arrays( read_XMP_array_from_text( $xmp_text_orig ),
                              read_XMP_array_from_text( $xmp_text_new ) );
        if ( false != $xmp_merged )
            $xmp_text_new = write_XMP_array_to_text( $xmp_merged );
    }

    $header_modified = put_XMP_text($header_data, $xmp_text_new);

    if ( ! put_jpeg_header_data($filename_jpeg,$filename_out,$header_modified) )
        print_message("Couldn't write new file out with new metadata.");
}

function merge_xmp_arrays ($xmp_array_orig, $xmp_array_new)
{
    return FALSE;
    # looks like reading in array is working, now just need to merge


    if ( false == $xmp_array_orig ) 
        echo "ERROR: xmp_array_orig";
    // print_r( $xmp_array );

    if ( false == $xmp_array_new ) 
        echo "ERROR: xmp_array_new";
    print_r( $xmp_array_new );
   
    exit;
}

// main driver code

// parse command line options
$opt = getopt('hmvf:x:o:');

// if there are no arguments passed or -h option, then print help
if ( count($opt) == 0 || isset($opt['h']) || 
     ! isset($opt['f'], $opt['x'], $opt['o']) )
    print_help();

if ( isset($opt['v']) )
    $_GLOBALS['verbose'] = true;

embed_xmp ($opt['f'], $opt['x'], $opt['o'], isset($opt['m']));


exit(0);

?>
