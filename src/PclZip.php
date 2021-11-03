<?php

declare(strict_types=1);

namespace MirazMac\PclZip;

use MirazMac\PclZip\Exceptions\PclZipException;

/**
 * A sort of refactor of pclzip
 */
class PclZip
{
    public const PCLZIP_READ_BLOCK_SIZE = 8;

    // ----- File list separator
    // In version 1.x of PclZip, the separator for file list is a space
    // (which is not a very smart choice, specifically for windows paths !).
    // A better separator should be a comma (,). This constant gives you the
    // abilty to change that.
    // However notice that changing this value, may have impact on existing
    // scripts, using space separated filenames.
    // Recommanded values for compatibility with older versions :
    //define( 'PCLZIP_SEPARATOR', ' ' );
    public const PCLZIP_SEPARATOR = ',';

    // ----- Error configuration
    // 0 : PclZip Class integrated error handling
    // 1 : PclError external library error handling. By enabling this
//     you must ensure that you have included PclError library.
    // [2,...] : reserved for futur use
    public const PCLZIP_ERROR_EXTERNAL = 0;

    // ----- Optional static temporary directory
//       By default temporary files are generated in the script current
//       path.
//       If defined :
//       - MUST BE terminated by a '/'.
//       - MUST be a valid, already created directory
//       Samples :
    // define( 'PCLZIP_TEMPORARY_DIR', '/temp/' );
    // define( 'PCLZIP_TEMPORARY_DIR', 'C:/Temp/' );
    public const PCLZIP_TEMPORARY_DIR = '';

    // ----- Optional threshold ratio for use of temporary files
//       Pclzip sense the size of the file to add/extract and decide to
//       use or not temporary file. The algorythm is looking for
//       memory_limit of PHP and apply a ratio.
//       threshold = memory_limit * ratio.
//       Recommended values are under 0.5. Default 0.47.
//       Samples :
    // define( 'PCLZIP_TEMPORARY_FILE_RATIO', 0.5 );
    public const PCLZIP_TEMPORARY_FILE_RATIO = 0.47;

    // --------------------------------------------------------------------------------
    // ***** UNDER THIS LINE NOTHING NEEDS TO BE MODIFIED *****
    // --------------------------------------------------------------------------------

    // ----- Global variables
    public $g_pclzip_version = "2.8.2";

    // ----- Error codes
    //   -1 : Unable to open file in binary write mode
    //   -2 : Unable to open file in binary read mode
    //   -3 : Invalid parameters
    //   -4 : File does not exist
    //   -5 : Filename is too long (max. 255)
    //   -6 : Not a valid zip file
    //   -7 : Invalid extracted file size
    //   -8 : Unable to create directory
    //   -9 : Invalid archive extension
    //  -10 : Invalid archive format
    //  -11 : Unable to delete file (unlink)
    //  -12 : Unable to rename file (rename)
    //  -13 : Invalid header checksum
    //  -14 : Invalid archive size
    public const PCLZIP_ERR_USER_ABORTED = 2;
    public const PCLZIP_ERR_NO_ERROR = 0;
    public const PCLZIP_ERR_WRITE_OPEN_FAIL = -1;
    public const PCLZIP_ERR_READ_OPEN_FAIL = -2;
    public const PCLZIP_ERR_INVALID_PARAMETER = -3;
    public const PCLZIP_ERR_MISSING_FILE = -4;
    public const PCLZIP_ERR_FILENAME_TOO_LONG = -5;
    public const PCLZIP_ERR_INVALID_ZIP = -6;
    public const PCLZIP_ERR_BAD_EXTRACTED_FILE = -7;
    public const PCLZIP_ERR_DIR_CREATE_FAIL = -8;
    public const PCLZIP_ERR_BAD_EXTENSION = -9;
    public const PCLZIP_ERR_BAD_FORMAT = -10;
    public const PCLZIP_ERR_DELETE_FILE_FAIL = -11;
    public const PCLZIP_ERR_RENAME_FILE_FAIL = -12;
    public const PCLZIP_ERR_BAD_CHECKSUM = -13;
    public const PCLZIP_ERR_INVALID_ARCHIVE_ZIP = -14;
    public const PCLZIP_ERR_MISSING_OPTION_VALUE = -15;
    public const PCLZIP_ERR_INVALID_OPTION_VALUE = -16;
    public const PCLZIP_ERR_ALREADY_A_DIRECTORY = -17;
    public const PCLZIP_ERR_UNSUPPORTED_COMPRESSION = -18;
    public const PCLZIP_ERR_UNSUPPORTED_ENCRYPTION = -19;
    public const PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE = -20;
    public const PCLZIP_ERR_DIRECTORY_RESTRICTION = -21;

    // ----- Options values
    public const PCLZIP_OPT_PATH = 1;
    public const PCLZIP_OPT_ADD_PATH = 2;
    public const PCLZIP_OPT_REMOVE_PATH = 3;
    public const PCLZIP_OPT_REMOVE_ALL_PATH = 4;
    public const PCLZIP_OPT_SET_CHMOD = 5;
    public const PCLZIP_OPT_EXTRACT_AS_STRING = 6;
    public const PCLZIP_OPT_NO_COMPRESSION = 7;
    public const PCLZIP_OPT_BY_NAME = 8;
    public const PCLZIP_OPT_BY_INDEX = 9;
    public const PCLZIP_OPT_BY_EREG = 0;
    public const PCLZIP_OPT_BY_PREG = 1;
    public const PCLZIP_OPT_COMMENT = 2;
    public const PCLZIP_OPT_ADD_COMMENT = 3;
    public const PCLZIP_OPT_PREPEND_COMMENT = 4;
    public const PCLZIP_OPT_EXTRACT_IN_OUTPUT = 5;
    public const PCLZIP_OPT_REPLACE_NEWER = 6;
    public const PCLZIP_OPT_STOP_ON_ERROR = 7;
    // Having big trouble with crypt. Need to multiply 2 long int
    // which is not correctly supported by PHP ...
    //define( 'PCLZIP_OPT_CRYPT', 77018 );
    public const PCLZIP_OPT_EXTRACT_DIR_RESTRICTION = 9;
    public const PCLZIP_OPT_TEMP_FILE_THRESHOLD = 0;
    public const PCLZIP_OPT_ADD_TEMP_FILE_THRESHOLD = 0; // alias
    public const PCLZIP_OPT_TEMP_FILE_ON = 1;
    public const PCLZIP_OPT_ADD_TEMP_FILE_ON = 1; // alias
    public const PCLZIP_OPT_TEMP_FILE_OFF = 2;
    public const PCLZIP_OPT_ADD_TEMP_FILE_OFF = 2; // alias

    // ----- File description attributes
    public const PCLZIP_ATT_FILE_NAME = 1;
    public const PCLZIP_ATT_FILE_NEW_SHORT_NAME = 2;
    public const PCLZIP_ATT_FILE_NEW_FULL_NAME = 3;
    public const PCLZIP_ATT_FILE_MTIME = 4;
    public const PCLZIP_ATT_FILE_CONTENT = 5;
    public const PCLZIP_ATT_FILE_COMMENT = 6;

    // ----- Call backs values
    public const PCLZIP_CB_PRE_EXTRACT = 1;
    public const PCLZIP_CB_POST_EXTRACT = 2;
    public const PCLZIP_CB_PRE_ADD = 3;
    public const PCLZIP_CB_POST_ADD = 4;

    // ----- Filename of the zip file
    public $zipname = '';

    // ----- File descriptor of the zip file
    public $zip_fd = 0;

    // ----- Internal error handling
    public $error_code = 1;
    public $error_string = '';

    // ----- Current status of the magic_quotes_runtime
    // This value store the php configuration for magic_quotes
    // The class can then disable the magic_quotes and reset it after
    public $magic_quotes_status;

    // --------------------------------------------------------------------------------
    // Function : PclZip()
    // Description :
    //   Creates a PclZip object and set the name of the associated Zip archive
    //   filename.
    //   Note that no real action is taken, if the archive does not exist it is not
    //   created. Use create() for that.
    // --------------------------------------------------------------------------------
    public function __construct($p_zipname)
    {
        // ----- Tests the zlib
        if (!function_exists('gzopen')) {
            die('Abort ' . basename(__FILE__) . ' : Missing zlib extensions');
        }

        // ----- Set the attributes
        $this->zipname             = $p_zipname;
        $this->zip_fd              = 0;
        $this->magic_quotes_status = -1;

        // ----- Return
        return;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function :
    //   create($p_filelist, $p_add_dir="", $p_remove_dir="")
    //   create($p_filelist, $p_option, $p_option_value, ...)
    // Description :
    //   This method supports two different synopsis. The first one is historical.
    //   This method creates a Zip Archive. The Zip file is created in the
    //   filesystem. The files and directories indicated in $p_filelist
    //   are added in the archive. See the parameters description for the
    //   supported format of $p_filelist.
    //   When a directory is in the list, the directory and its content is added
    //   in the archive.
    //   In this synopsis, the function takes an optional variable list of
    //   options. See bellow the supported options.
    // Parameters :
    //   $p_filelist : An array containing file or directory names, or
    //                 a string containing one filename or one directory name, or
    //                 a string containing a list of filenames and/or directory
    //                 names separated by spaces.
    //   $p_add_dir : A path to add before the real path of the archived file,
    //                in order to have it memorized in the archive.
    //   $p_remove_dir : A path to remove from the real path of the file to archive,
    //                   in order to have a shorter path memorized in the archive.
    //                   When $p_add_dir and $p_remove_dir are set, $p_remove_dir
    //                   is removed first, before $p_add_dir is added.
    // Options :
    //   static::PCLZIP_OPT_ADD_PATH :
    //   static::PCLZIP_OPT_REMOVE_PATH :
    //   static::PCLZIP_OPT_REMOVE_ALL_PATH :
    //   static::PCLZIP_OPT_COMMENT :
    //   static::PCLZIP_CB_PRE_ADD :
    //   static::PCLZIP_CB_POST_ADD :
    // Return Values :
    //   0 on failure,
    //   The list of the added files, with a status of the add action.
    //   (see PclZip::listContent() for list entry format)
    // --------------------------------------------------------------------------------
    public function create($p_filelist)
    {
        $v_result = 1;




        // ----- Set default values
        $v_options                            = [];
        $v_options[static::PCLZIP_OPT_NO_COMPRESSION] = false;

        // ----- Look for variable options arguments
        $v_size = func_num_args();

        // ----- Look for arguments
        if ($v_size > 1) {
            // ----- Get the arguments
            $v_arg_list = func_get_args();

            // ----- Remove from the options list the first argument
            array_shift($v_arg_list);
            $v_size--;

            // ----- Look for first arg
            if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
                // ----- Parse the options
                $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options, array(
                    static::PCLZIP_OPT_REMOVE_PATH => 'optional',
                    static::PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                    static::PCLZIP_OPT_ADD_PATH => 'optional',
                    static::PCLZIP_CB_PRE_ADD => 'optional',
                    static::PCLZIP_CB_POST_ADD => 'optional',
                    static::PCLZIP_OPT_NO_COMPRESSION => 'optional',
                    static::PCLZIP_OPT_COMMENT => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                    //, static::PCLZIP_OPT_CRYPT => 'optional'
                ));
                if ($v_result != 1) {
                    return 0;
                }

                // ----- Look for 2 args
            // Here we need to support the first historic synopsis of the
            // method.
            } else {
                // ----- Get the first argument
                $v_options[static::PCLZIP_OPT_ADD_PATH] = $v_arg_list[0];

                // ----- Look for the optional second argument
                if ($v_size == 2) {
                    $v_options[static::PCLZIP_OPT_REMOVE_PATH] = $v_arg_list[1];
                } elseif ($v_size > 2) {
                    throw new PclZipException("Invalid number / type of arguments", static::PCLZIP_ERR_INVALID_PARAMETER);

                    return 0;
                }
            }
        }

        // ----- Look for default option values
        $this->privOptionDefaultThreshold($v_options);

        // ----- Init
        $v_string_list    = [];
        $v_att_list       = [];
        $v_filedescr_list = [];
        $p_result_list    = [];

        // ----- Look if the $p_filelist is really an array
        if (is_array($p_filelist)) {
            // ----- Look if the first element is also an array
            //       This will mean that this is a file description entry
            if (isset($p_filelist[0]) && is_array($p_filelist[0])) {
                $v_att_list = $p_filelist;

            // ----- The list is a list of string names
            } else {
                $v_string_list = $p_filelist;
            }

            // ----- Look if the $p_filelist is a string
        } elseif (is_string($p_filelist)) {
            // ----- Create a list from the string
            $v_string_list = explode(static::PCLZIP_SEPARATOR, $p_filelist);

        // ----- Invalid variable type for $p_filelist
        } else {
            throw new PclZipException("Invalid variable type p_filelist", static::PCLZIP_ERR_INVALID_PARAMETER);

            return 0;
        }

        // ----- Reformat the string list
        if (sizeof($v_string_list) != 0) {
            foreach ($v_string_list as $v_string) {
                if ($v_string != '') {
                    $v_att_list[][static::PCLZIP_ATT_FILE_NAME] = $v_string;
                } else {
                }
            }
        }

        // ----- For each file in the list check the attributes
        $v_supported_attributes = array(
            static::PCLZIP_ATT_FILE_NAME => 'mandatory',
            static::PCLZIP_ATT_FILE_NEW_SHORT_NAME => 'optional',
            static::PCLZIP_ATT_FILE_NEW_FULL_NAME => 'optional',
            static::PCLZIP_ATT_FILE_MTIME => 'optional',
            static::PCLZIP_ATT_FILE_CONTENT => 'optional',
            static::PCLZIP_ATT_FILE_COMMENT => 'optional'
        );
        foreach ($v_att_list as $v_entry) {
            $v_result = $this->privFileDescrParseAtt($v_entry, $v_filedescr_list[], $v_options, $v_supported_attributes);
            if ($v_result != 1) {
                return 0;
            }
        }

        // ----- Expand the filelist (expand directories)
        $v_result = $this->privFileDescrExpand($v_filedescr_list, $v_options);
        if ($v_result != 1) {
            return 0;
        }

        // ----- Call the create fct
        $v_result = $this->privCreate($v_filedescr_list, $p_result_list, $v_options);
        if ($v_result != 1) {
            return 0;
        }

        // ----- Return
        return $p_result_list;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function :
    //   add($p_filelist, $p_add_dir="", $p_remove_dir="")
    //   add($p_filelist, $p_option, $p_option_value, ...)
    // Description :
    //   This method supports two synopsis. The first one is historical.
    //   This methods add the list of files in an existing archive.
    //   If a file with the same name already exists, it is added at the end of the
    //   archive, the first one is still present.
    //   If the archive does not exist, it is created.
    // Parameters :
    //   $p_filelist : An array containing file or directory names, or
    //                 a string containing one filename or one directory name, or
    //                 a string containing a list of filenames and/or directory
    //                 names separated by spaces.
    //   $p_add_dir : A path to add before the real path of the archived file,
    //                in order to have it memorized in the archive.
    //   $p_remove_dir : A path to remove from the real path of the file to archive,
    //                   in order to have a shorter path memorized in the archive.
    //                   When $p_add_dir and $p_remove_dir are set, $p_remove_dir
    //                   is removed first, before $p_add_dir is added.
    // Options :
    //   static::PCLZIP_OPT_ADD_PATH :
    //   static::PCLZIP_OPT_REMOVE_PATH :
    //   static::PCLZIP_OPT_REMOVE_ALL_PATH :
    //   static::PCLZIP_OPT_COMMENT :
    //   static::PCLZIP_OPT_ADD_COMMENT :
    //   static::PCLZIP_OPT_PREPEND_COMMENT :
    //   static::PCLZIP_CB_PRE_ADD :
    //   static::PCLZIP_CB_POST_ADD :
    // Return Values :
    //   0 on failure,
    //   The list of the added files, with a status of the add action.
    //   (see PclZip::listContent() for list entry format)
    // --------------------------------------------------------------------------------
    public function add($p_filelist)
    {
        $v_result = 1;




        // ----- Set default values
        $v_options                            = [];
        $v_options[static::PCLZIP_OPT_NO_COMPRESSION] = false;

        // ----- Look for variable options arguments
        $v_size = func_num_args();

        // ----- Look for arguments
        if ($v_size > 1) {
            // ----- Get the arguments
            $v_arg_list = func_get_args();

            // ----- Remove form the options list the first argument
            array_shift($v_arg_list);
            $v_size--;

            // ----- Look for first arg
            if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
                // ----- Parse the options
                $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options, array(
                    static::PCLZIP_OPT_REMOVE_PATH => 'optional',
                    static::PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                    static::PCLZIP_OPT_ADD_PATH => 'optional',
                    static::PCLZIP_CB_PRE_ADD => 'optional',
                    static::PCLZIP_CB_POST_ADD => 'optional',
                    static::PCLZIP_OPT_NO_COMPRESSION => 'optional',
                    static::PCLZIP_OPT_COMMENT => 'optional',
                    static::PCLZIP_OPT_ADD_COMMENT => 'optional',
                    static::PCLZIP_OPT_PREPEND_COMMENT => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                    //, static::PCLZIP_OPT_CRYPT => 'optional'
                ));
                if ($v_result != 1) {
                    return 0;
                }

                // ----- Look for 2 args
            // Here we need to support the first historic synopsis of the
            // method.
            } else {
                // ----- Get the first argument
                $v_options[static::PCLZIP_OPT_ADD_PATH] = $v_add_path = $v_arg_list[0];

                // ----- Look for the optional second argument
                if ($v_size == 2) {
                    $v_options[static::PCLZIP_OPT_REMOVE_PATH] = $v_arg_list[1];
                } elseif ($v_size > 2) {
                    // ----- Error log
                    throw new PclZipException("Invalid number / type of arguments", static::PCLZIP_ERR_INVALID_PARAMETER);

                    // ----- Return
                    return 0;
                }
            }
        }

        // ----- Look for default option values
        $this->privOptionDefaultThreshold($v_options);

        // ----- Init
        $v_string_list    = [];
        $v_att_list       = [];
        $v_filedescr_list = [];
        $p_result_list    = [];

        // ----- Look if the $p_filelist is really an array
        if (is_array($p_filelist)) {
            // ----- Look if the first element is also an array
            //       This will mean that this is a file description entry
            if (isset($p_filelist[0]) && is_array($p_filelist[0])) {
                $v_att_list = $p_filelist;

            // ----- The list is a list of string names
            } else {
                $v_string_list = $p_filelist;
            }

            // ----- Look if the $p_filelist is a string
        } elseif (is_string($p_filelist)) {
            // ----- Create a list from the string
            $v_string_list = explode(static::PCLZIP_SEPARATOR, $p_filelist);

        // ----- Invalid variable type for $p_filelist
        } else {
            throw new PclZipException("Invalid variable type '" . gettype($p_filelist) . "' for p_filelist", static::PCLZIP_ERR_INVALID_PARAMETER);

            return 0;
        }

        // ----- Reformat the string list
        if (sizeof($v_string_list) != 0) {
            foreach ($v_string_list as $v_string) {
                $v_att_list[][static::PCLZIP_ATT_FILE_NAME] = $v_string;
            }
        }

        // ----- For each file in the list check the attributes
        $v_supported_attributes = array(
            static::PCLZIP_ATT_FILE_NAME => 'mandatory',
            static::PCLZIP_ATT_FILE_NEW_SHORT_NAME => 'optional',
            static::PCLZIP_ATT_FILE_NEW_FULL_NAME => 'optional',
            static::PCLZIP_ATT_FILE_MTIME => 'optional',
            static::PCLZIP_ATT_FILE_CONTENT => 'optional',
            static::PCLZIP_ATT_FILE_COMMENT => 'optional'
        );
        foreach ($v_att_list as $v_entry) {
            $v_result = $this->privFileDescrParseAtt($v_entry, $v_filedescr_list[], $v_options, $v_supported_attributes);
            if ($v_result != 1) {
                return 0;
            }
        }

        // ----- Expand the filelist (expand directories)
        $v_result = $this->privFileDescrExpand($v_filedescr_list, $v_options);
        if ($v_result != 1) {
            return 0;
        }

        // ----- Call the create fct
        $v_result = $this->privAdd($v_filedescr_list, $p_result_list, $v_options);
        if ($v_result != 1) {
            return 0;
        }

        // ----- Return
        return $p_result_list;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : listContent()
    // Description :
    //   This public method, gives the list of the files and directories, with their
    //   properties.
    //   The properties of each entries in the list are (used also in other functions) :
    //     filename : Name of the file. For a create or add action it is the filename
    //                given by the user. For an extract function it is the filename
    //                of the extracted file.
    //     stored_filename : Name of the file / directory stored in the archive.
    //     size : Size of the stored file.
    //     compressed_size : Size of the file's data compressed in the archive
    //                       (without the headers overhead)
    //     mtime : Last known modification date of the file (UNIX timestamp)
    //     comment : Comment associated with the file
    //     folder : true | false
    //     index : index of the file in the archive
    //     status : status of the action (depending of the action) :
    //              Values are :
    //                ok : OK !
    //                filtered : the file / dir is not extracted (filtered by user)
    //                already_a_directory : the file can not be extracted because a
    //                                      directory with the same name already exists
    //                write_protected : the file can not be extracted because a file
    //                                  with the same name already exists and is
    //                                  write protected
    //                newer_exist : the file was not extracted because a newer file exists
    //                path_creation_fail : the file is not extracted because the folder
    //                                     does not exist and can not be created
    //                write_error : the file was not extracted because there was a
    //                              error while writing the file
    //                read_error : the file was not extracted because there was a error
    //                             while reading the file
    //                invalid_header : the file was not extracted because of an archive
    //                                 format error (bad file header)
    //   Note that each time a method can continue operating when there
    //   is an action error on a file, the error is only logged in the file status.
    // Return Values :
    //   0 on an unrecoverable failure,
    //   The list of the files in the archive.
    // --------------------------------------------------------------------------------
    public function listContent()
    {
        $v_result = 1;




        // ----- Check archive
        if (!$this->privCheckFormat()) {
            return (0);
        }

        // ----- Call the extracting fct
        $p_list = [];
        if (($v_result = $this->privList($p_list)) != 1) {
            unset($p_list);

            return (0);
        }

        // ----- Return
        return $p_list;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function :
    //   extract($p_path="./", $p_remove_path="")
    //   extract([$p_option, $p_option_value, ...])
    // Description :
    //   This method supports two synopsis. The first one is historical.
    //   This method extract all the files / directories from the archive to the
    //   folder indicated in $p_path.
    //   If you want to ignore the 'root' part of path of the memorized files
    //   you can indicate this in the optional $p_remove_path parameter.
    //   By default, if a newer file with the same name already exists, the
    //   file is not extracted.
    //
    //   If both static::PCLZIP_OPT_PATH and static::PCLZIP_OPT_ADD_PATH aoptions
    //   are used, the path indicated in static::PCLZIP_OPT_ADD_PATH is append
    //   at the end of the path value of static::PCLZIP_OPT_PATH.
    // Parameters :
    //   $p_path : Path where the files and directories are to be extracted
    //   $p_remove_path : First part ('root' part) of the memorized path
    //                    (if any similar) to remove while extracting.
    // Options :
    //   static::PCLZIP_OPT_PATH :
    //   static::PCLZIP_OPT_ADD_PATH :
    //   static::PCLZIP_OPT_REMOVE_PATH :
    //   static::PCLZIP_OPT_REMOVE_ALL_PATH :
    //   static::PCLZIP_CB_PRE_EXTRACT :
    //   static::PCLZIP_CB_POST_EXTRACT :
    // Return Values :
    //   0 or a negative value on failure,
    //   The list of the extracted files, with a status of the action.
    //   (see PclZip::listContent() for list entry format)
    // --------------------------------------------------------------------------------
    public function extract()
    {
        $v_result = 1;




        // ----- Check archive
        if (!$this->privCheckFormat()) {
            return (0);
        }

        // ----- Set default values
        $v_options         = [];
        //    $v_path = "./";
        $v_path            = '';
        $v_remove_path     = "";
        $v_remove_all_path = false;

        // ----- Look for variable options arguments
        $v_size = func_num_args();

        // ----- Default values for option
        $v_options[static::PCLZIP_OPT_EXTRACT_AS_STRING] = false;

        // ----- Look for arguments
        if ($v_size > 0) {
            // ----- Get the arguments
            $v_arg_list = func_get_args();

            // ----- Look for first arg
            if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
                // ----- Parse the options
                $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options, array(
                    static::PCLZIP_OPT_PATH => 'optional',
                    static::PCLZIP_OPT_REMOVE_PATH => 'optional',
                    static::PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                    static::PCLZIP_OPT_ADD_PATH => 'optional',
                    static::PCLZIP_CB_PRE_EXTRACT => 'optional',
                    static::PCLZIP_CB_POST_EXTRACT => 'optional',
                    static::PCLZIP_OPT_SET_CHMOD => 'optional',
                    static::PCLZIP_OPT_BY_NAME => 'optional',
                    static::PCLZIP_OPT_BY_EREG => 'optional',
                    static::PCLZIP_OPT_BY_PREG => 'optional',
                    static::PCLZIP_OPT_BY_INDEX => 'optional',
                    static::PCLZIP_OPT_EXTRACT_AS_STRING => 'optional',
                    static::PCLZIP_OPT_EXTRACT_IN_OUTPUT => 'optional',
                    static::PCLZIP_OPT_REPLACE_NEWER => 'optional',
                    static::PCLZIP_OPT_STOP_ON_ERROR => 'optional',
                    static::PCLZIP_OPT_EXTRACT_DIR_RESTRICTION => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                ));
                if ($v_result != 1) {
                    return 0;
                }

                // ----- Set the arguments
                if (isset($v_options[static::PCLZIP_OPT_PATH])) {
                    $v_path = $v_options[static::PCLZIP_OPT_PATH];
                }
                if (isset($v_options[static::PCLZIP_OPT_REMOVE_PATH])) {
                    $v_remove_path = $v_options[static::PCLZIP_OPT_REMOVE_PATH];
                }
                if (isset($v_options[static::PCLZIP_OPT_REMOVE_ALL_PATH])) {
                    $v_remove_all_path = $v_options[static::PCLZIP_OPT_REMOVE_ALL_PATH];
                }
                if (isset($v_options[static::PCLZIP_OPT_ADD_PATH])) {
                    // ----- Check for '/' in last path char
                    if ((strlen($v_path) > 0) && (substr($v_path, -1) != '/')) {
                        $v_path .= '/';
                    }
                    $v_path .= $v_options[static::PCLZIP_OPT_ADD_PATH];
                }

                // ----- Look for 2 args
            // Here we need to support the first historic synopsis of the
            // method.
            } else {
                // ----- Get the first argument
                $v_path = $v_arg_list[0];

                // ----- Look for the optional second argument
                if ($v_size == 2) {
                    $v_remove_path = $v_arg_list[1];
                } elseif ($v_size > 2) {
                    // ----- Error log
                    throw new PclZipException("Invalid number / type of arguments", static::PCLZIP_ERR_INVALID_PARAMETER);

                    // ----- Return
                    return 0;
                }
            }
        }

        // ----- Look for default option values
        $this->privOptionDefaultThreshold($v_options);

        // ----- Trace

        // ----- Call the extracting fct
        $p_list   = [];
        $v_result = $this->privExtractByRule($p_list, $v_path, $v_remove_path, $v_remove_all_path, $v_options);
        if ($v_result < 1) {
            unset($p_list);

            return (0);
        }

        // ----- Return
        return $p_list;
    }
    // --------------------------------------------------------------------------------


    // --------------------------------------------------------------------------------
    // Function :
    //   extractByIndex($p_index, $p_path="./", $p_remove_path="")
    //   extractByIndex($p_index, [$p_option, $p_option_value, ...])
    // Description :
    //   This method supports two synopsis. The first one is historical.
    //   This method is doing a partial extract of the archive.
    //   The extracted files or folders are identified by their index in the
    //   archive (from 0 to n).
    //   Note that if the index identify a folder, only the folder entry is
    //   extracted, not all the files included in the archive.
    // Parameters :
    //   $p_index : A single index (integer) or a string of indexes of files to
    //              extract. The form of the string is "0,4-6,8-12" with only numbers
    //              and '-' for range or ',' to separate ranges. No spaces or ';'
    //              are allowed.
    //   $p_path : Path where the files and directories are to be extracted
    //   $p_remove_path : First part ('root' part) of the memorized path
    //                    (if any similar) to remove while extracting.
    // Options :
    //   static::PCLZIP_OPT_PATH :
    //   static::PCLZIP_OPT_ADD_PATH :
    //   static::PCLZIP_OPT_REMOVE_PATH :
    //   static::PCLZIP_OPT_REMOVE_ALL_PATH :
    //   static::PCLZIP_OPT_EXTRACT_AS_STRING : The files are extracted as strings and
    //     not as files.
    //     The resulting content is in a new field 'content' in the file
    //     structure.
    //     This option must be used alone (any other options are ignored).
    //   static::PCLZIP_CB_PRE_EXTRACT :
    //   static::PCLZIP_CB_POST_EXTRACT :
    // Return Values :
    //   0 on failure,
    //   The list of the extracted files, with a status of the action.
    //   (see PclZip::listContent() for list entry format)
    // --------------------------------------------------------------------------------
    //function extractByIndex($p_index, options...)
    public function extractByIndex($p_index)
    {
        $v_result = 1;




        // ----- Check archive
        if (!$this->privCheckFormat()) {
            return (0);
        }

        // ----- Set default values
        $v_options         = [];
        //    $v_path = "./";
        $v_path            = '';
        $v_remove_path     = "";
        $v_remove_all_path = false;

        // ----- Look for variable options arguments
        $v_size = func_num_args();

        // ----- Default values for option
        $v_options[static::PCLZIP_OPT_EXTRACT_AS_STRING] = false;

        // ----- Look for arguments
        if ($v_size > 1) {
            // ----- Get the arguments
            $v_arg_list = func_get_args();

            // ----- Remove form the options list the first argument
            array_shift($v_arg_list);
            $v_size--;

            // ----- Look for first arg
            if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
                // ----- Parse the options
                $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options, array(
                    static::PCLZIP_OPT_PATH => 'optional',
                    static::PCLZIP_OPT_REMOVE_PATH => 'optional',
                    static::PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                    static::PCLZIP_OPT_EXTRACT_AS_STRING => 'optional',
                    static::PCLZIP_OPT_ADD_PATH => 'optional',
                    static::PCLZIP_CB_PRE_EXTRACT => 'optional',
                    static::PCLZIP_CB_POST_EXTRACT => 'optional',
                    static::PCLZIP_OPT_SET_CHMOD => 'optional',
                    static::PCLZIP_OPT_REPLACE_NEWER => 'optional',
                    static::PCLZIP_OPT_STOP_ON_ERROR => 'optional',
                    static::PCLZIP_OPT_EXTRACT_DIR_RESTRICTION => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                    static::PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                ));
                if ($v_result != 1) {
                    return 0;
                }

                // ----- Set the arguments
                if (isset($v_options[static::PCLZIP_OPT_PATH])) {
                    $v_path = $v_options[static::PCLZIP_OPT_PATH];
                }
                if (isset($v_options[static::PCLZIP_OPT_REMOVE_PATH])) {
                    $v_remove_path = $v_options[static::PCLZIP_OPT_REMOVE_PATH];
                }
                if (isset($v_options[static::PCLZIP_OPT_REMOVE_ALL_PATH])) {
                    $v_remove_all_path = $v_options[static::PCLZIP_OPT_REMOVE_ALL_PATH];
                }
                if (isset($v_options[static::PCLZIP_OPT_ADD_PATH])) {
                    // ----- Check for '/' in last path char
                    if ((strlen($v_path) > 0) && (substr($v_path, -1) != '/')) {
                        $v_path .= '/';
                    }
                    $v_path .= $v_options[static::PCLZIP_OPT_ADD_PATH];
                }
                if (!isset($v_options[static::PCLZIP_OPT_EXTRACT_AS_STRING])) {
                    $v_options[static::PCLZIP_OPT_EXTRACT_AS_STRING] = false;
                } else {
                }

                // ----- Look for 2 args
            // Here we need to support the first historic synopsis of the
            // method.
            } else {
                // ----- Get the first argument
                $v_path = $v_arg_list[0];

                // ----- Look for the optional second argument
                if ($v_size == 2) {
                    $v_remove_path = $v_arg_list[1];
                } elseif ($v_size > 2) {
                    // ----- Error log
                    throw new PclZipException("Invalid number / type of arguments", static::PCLZIP_ERR_INVALID_PARAMETER);

                    // ----- Return
                    return 0;
                }
            }
        }

        // ----- Trace

        // ----- Trick
        // Here I want to reuse extractByRule(), so I need to parse the $p_index
        // with privParseOptions()
        $v_arg_trick     = array(
            static::PCLZIP_OPT_BY_INDEX,
            $p_index
        );
        $v_options_trick = [];
        $v_result        = $this->privParseOptions($v_arg_trick, sizeof($v_arg_trick), $v_options_trick, array(
            static::PCLZIP_OPT_BY_INDEX => 'optional'
        ));
        if ($v_result != 1) {
            return 0;
        }
        $v_options[static::PCLZIP_OPT_BY_INDEX] = $v_options_trick[static::PCLZIP_OPT_BY_INDEX];

        // ----- Look for default option values
        $this->privOptionDefaultThreshold($v_options);

        // ----- Call the extracting fct
        if (($v_result = $this->privExtractByRule($p_list, $v_path, $v_remove_path, $v_remove_all_path, $v_options)) < 1) {
            return (0);
        }

        // ----- Return
        return $p_list;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function :
    //   delete([$p_option, $p_option_value, ...])
    // Description :
    //   This method removes files from the archive.
    //   If no parameters are given, then all the archive is emptied.
    // Parameters :
    //   None or optional arguments.
    // Options :
    //   static::PCLZIP_OPT_BY_INDEX :
    //   static::PCLZIP_OPT_BY_NAME :
    //   static::PCLZIP_OPT_BY_EREG :
    //   static::PCLZIP_OPT_BY_PREG :
    // Return Values :
    //   0 on failure,
    //   The list of the files which are still present in the archive.
    //   (see PclZip::listContent() for list entry format)
    // --------------------------------------------------------------------------------
    public function delete()
    {
        $v_result = 1;




        // ----- Check archive
        if (!$this->privCheckFormat()) {
            return (0);
        }

        // ----- Set default values
        $v_options = [];

        // ----- Look for variable options arguments
        $v_size = func_num_args();

        // ----- Look for arguments
        if ($v_size > 0) {
            // ----- Get the arguments
            $v_arg_list = func_get_args();

            // ----- Parse the options
            $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options, array(
                static::PCLZIP_OPT_BY_NAME => 'optional',
                static::PCLZIP_OPT_BY_EREG => 'optional',
                static::PCLZIP_OPT_BY_PREG => 'optional',
                static::PCLZIP_OPT_BY_INDEX => 'optional'
            ));
            if ($v_result != 1) {
                return 0;
            }
        }


        // ----- Call the delete fct
        $v_list = [];
        if (($v_result = $this->privDeleteByRule($v_list, $v_options)) != 1) {
            unset($v_list);

            return (0);
        }

        // ----- Return
        return $v_list;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : deleteByIndex()
    // Description :
    //   ***** Deprecated *****
    //   delete(static::PCLZIP_OPT_BY_INDEX, $p_index) should be prefered.
    // --------------------------------------------------------------------------------
    public function deleteByIndex($p_index)
    {
        $p_list = $this->delete(static::PCLZIP_OPT_BY_INDEX, $p_index);

        // ----- Return
        return $p_list;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : properties()
    // Description :
    //   This method gives the properties of the archive.
    //   The properties are :
    //     nb : Number of files in the archive
    //     comment : Comment associated with the archive file
    //     status : not_exist, ok
    // Parameters :
    //   None
    // Return Values :
    //   0 on failure,
    //   An array with the archive properties.
    // --------------------------------------------------------------------------------
    public function properties()
    {



        // ----- Check archive
        if (!$this->privCheckFormat()) {
            return (0);
        }

        // ----- Default properties
        $v_prop            = [];
        $v_prop['comment'] = '';
        $v_prop['nb']      = 0;
        $v_prop['status']  = 'not_exist';

        // ----- Look if file exists
        if (@is_file($this->zipname)) {
            // ----- Open the zip file
            if (($this->zip_fd = @fopen($this->zipname, 'rb')) == 0) {
                throw new PclZipException('Unable to open archive \'' . $this->zipname . '\' in binary read mode', static::PCLZIP_ERR_READ_OPEN_FAIL);

                // ----- Return
                return 0;
            }

            // ----- Read the central directory informations
            $v_central_dir = [];
            if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1) {
                return 0;
            }

            // ----- Close the zip file
            $this->privCloseFd();

            // ----- Set the user attributes
            $v_prop['comment'] = $v_central_dir['comment'];
            $v_prop['nb']      = $v_central_dir['entries'];
            $v_prop['status']  = 'ok';
        }

        // ----- Return
        return $v_prop;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : duplicate()
    // Description :
    //   This method creates an archive by copying the content of an other one. If
    //   the archive already exist, it is replaced by the new one without any warning.
    // Parameters :
    //   $p_archive : The filename of a valid archive, or
    //                a valid PclZip object.
    // Return Values :
    //   1 on success.
    //   0 or a negative value on error (error code).
    // --------------------------------------------------------------------------------
    public function duplicate($p_archive)
    {
        $v_result = 1;




        // ----- Look if the $p_archive is a PclZip object
        if ((is_object($p_archive)) && (get_class($p_archive) == 'pclzip')) {
            // ----- Duplicate the archive
            $v_result = $this->privDuplicate($p_archive->zipname);

        // ----- Look if the $p_archive is a string (so a filename)
        } elseif (is_string($p_archive)) {
            // ----- Check that $p_archive is a valid zip file
            // TBC : Should also check the archive format
            if (!is_file($p_archive)) {
                throw new PclZipException("No file with filename '" . $p_archive . "'", static::PCLZIP_ERR_MISSING_FILE);
                $v_result = static::PCLZIP_ERR_MISSING_FILE;
            } else {
                // ----- Duplicate the archive
                $v_result = $this->privDuplicate($p_archive);
            }

            // ----- Invalid variable
        } else {
            throw new PclZipException("Invalid variable type p_archive_to_add", static::PCLZIP_ERR_INVALID_PARAMETER);
            $v_result = static::PCLZIP_ERR_INVALID_PARAMETER;
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : merge()
    // Description :
    //   This method merge the $p_archive_to_add archive at the end of the current
    //   one ($this).
    //   If the archive ($this) does not exist, the merge becomes a duplicate.
    //   If the $p_archive_to_add archive does not exist, the merge is a success.
    // Parameters :
    //   $p_archive_to_add : It can be directly the filename of a valid zip archive,
    //                       or a PclZip object archive.
    // Return Values :
    //   1 on success,
    //   0 or negative values on error (see below).
    // --------------------------------------------------------------------------------
    public function merge($p_archive_to_add)
    {
        $v_result = 1;




        // ----- Check archive
        if (!$this->privCheckFormat()) {
            return (0);
        }

        // ----- Look if the $p_archive_to_add is a PclZip object
        if ((is_object($p_archive_to_add)) && (get_class($p_archive_to_add) == 'pclzip')) {
            // ----- Merge the archive
            $v_result = $this->privMerge($p_archive_to_add);

        // ----- Look if the $p_archive_to_add is a string (so a filename)
        } elseif (is_string($p_archive_to_add)) {
            // ----- Create a temporary archive
            $v_object_archive = new PclZip($p_archive_to_add);

            // ----- Merge the archive
            $v_result = $this->privMerge($v_object_archive);

        // ----- Invalid variable
        } else {
            throw new PclZipException("Invalid variable type p_archive_to_add", static::PCLZIP_ERR_INVALID_PARAMETER);
            $v_result = static::PCLZIP_ERR_INVALID_PARAMETER;
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : errorName()
    // Description :
    // Parameters :
    // --------------------------------------------------------------------------------
    public function errorName($p_with_code = false)
    {
        $v_name = array(
            static::PCLZIP_ERR_NO_ERROR => 'static::PCLZIP_ERR_NO_ERROR',
            static::PCLZIP_ERR_WRITE_OPEN_FAIL => 'static::PCLZIP_ERR_WRITE_OPEN_FAIL',
            static::PCLZIP_ERR_READ_OPEN_FAIL => 'static::PCLZIP_ERR_READ_OPEN_FAIL',
            static::PCLZIP_ERR_INVALID_PARAMETER => 'static::PCLZIP_ERR_INVALID_PARAMETER',
            static::PCLZIP_ERR_MISSING_FILE => 'static::PCLZIP_ERR_MISSING_FILE',
            static::PCLZIP_ERR_FILENAME_TOO_LONG => 'static::PCLZIP_ERR_FILENAME_TOO_LONG',
            static::PCLZIP_ERR_INVALID_ZIP => 'static::PCLZIP_ERR_INVALID_ZIP',
            static::PCLZIP_ERR_BAD_EXTRACTED_FILE => 'static::PCLZIP_ERR_BAD_EXTRACTED_FILE',
            static::PCLZIP_ERR_DIR_CREATE_FAIL => 'static::PCLZIP_ERR_DIR_CREATE_FAIL',
            static::PCLZIP_ERR_BAD_EXTENSION => 'static::PCLZIP_ERR_BAD_EXTENSION',
            static::PCLZIP_ERR_BAD_FORMAT => 'static::PCLZIP_ERR_BAD_FORMAT',
            static::PCLZIP_ERR_DELETE_FILE_FAIL => 'static::PCLZIP_ERR_DELETE_FILE_FAIL',
            static::PCLZIP_ERR_RENAME_FILE_FAIL => 'static::PCLZIP_ERR_RENAME_FILE_FAIL',
            static::PCLZIP_ERR_BAD_CHECKSUM => 'static::PCLZIP_ERR_BAD_CHECKSUM',
            static::PCLZIP_ERR_INVALID_ARCHIVE_ZIP => 'static::PCLZIP_ERR_INVALID_ARCHIVE_ZIP',
            static::PCLZIP_ERR_MISSING_OPTION_VALUE => 'static::PCLZIP_ERR_MISSING_OPTION_VALUE',
            static::PCLZIP_ERR_INVALID_OPTION_VALUE => 'static::PCLZIP_ERR_INVALID_OPTION_VALUE',
            static::PCLZIP_ERR_UNSUPPORTED_COMPRESSION => 'static::PCLZIP_ERR_UNSUPPORTED_COMPRESSION',
            static::PCLZIP_ERR_UNSUPPORTED_ENCRYPTION => 'static::PCLZIP_ERR_UNSUPPORTED_ENCRYPTION',
            static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE => 'static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE',
            static::PCLZIP_ERR_DIRECTORY_RESTRICTION => 'static::PCLZIP_ERR_DIRECTORY_RESTRICTION'
        );

        if (isset($v_name[$this->error_code])) {
            $v_value = $v_name[$this->error_code];
        } else {
            $v_value = 'NoName';
        }

        if ($p_with_code) {
            return ($v_value . ' (' . $this->error_code . ')');
        } else {
            return ($v_value);
        }
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // ***** UNDER THIS LINE ARE DEFINED PRIVATE INTERNAL FUNCTIONS *****
    // *****                                                        *****
    // *****       THESES FUNCTIONS MUST NOT BE USED DIRECTLY       *****
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privCheckFormat()
    // Description :
    //   This method check that the archive exists and is a valid zip archive.
    //   Several level of check exists. (futur)
    // Parameters :
    //   $p_level : Level of check. Default 0.
    //              0 : Check the first bytes (magic codes) (default value))
    //              1 : 0 + Check the central directory (futur)
    //              2 : 1 + Check each file header (futur)
    // Return Values :
    //   true on success,
    //   false on error, the error code is set.
    // --------------------------------------------------------------------------------
    protected function privCheckFormat($p_level = 0)
    {
        $v_result = true;

        // ----- Reset the file system cache
        clearstatcache();


        // ----- Look if the file exits
        if (!is_file($this->zipname)) {
            throw new PclZipException("Missing archive file '" . $this->zipname . "'", static::PCLZIP_ERR_MISSING_FILE);
        }

        // ----- Check that the file is readeable
        if (!is_readable($this->zipname)) {
            throw new PclZipException("Unable to read archive '" . $this->zipname . "'", static::PCLZIP_ERR_READ_OPEN_FAIL);
        }

        // ----- Check the magic code
        // TBC

        // ----- Check the central header
        // TBC

        // ----- Check each file header
        // TBC

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privParseOptions()
    // Description :
    //   This internal methods reads the variable list of arguments ($p_options_list,
    //   $p_size) and generate an array with the options and values ($v_result_list).
    //   $v_requested_options contains the options that can be present and those that
    //   must be present.
    //   $v_requested_options is an array, with the option value as key, and 'optional',
    //   or 'mandatory' as value.
    // Parameters :
    //   See above.
    // Return Values :
    //   1 on success.
    //   0 on failure.
    // --------------------------------------------------------------------------------
    protected function privParseOptions(&$p_options_list, $p_size, &$v_result_list, $v_requested_options = false)
    {
        $v_result = 1;

        // ----- Read the options
        $i = 0;
        while ($i < $p_size) {
            // ----- Check if the option is supported
            if (!isset($v_requested_options[$p_options_list[$i]])) {
                throw new PclZipException("Invalid optional parameter '" . $p_options_list[$i] . "' for this method", static::PCLZIP_ERR_INVALID_PARAMETER);

                // ----- Return
            }

            // ----- Look for next option
            switch ($p_options_list[$i]) {
                // ----- Look for options that request a path value
                case static::PCLZIP_OPT_PATH:
                case static::PCLZIP_OPT_REMOVE_PATH:
                case static::PCLZIP_OPT_ADD_PATH:
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        // ----- Error log
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Get the value
                    $v_result_list[$p_options_list[$i]] = PclZipUtilTranslateWinPath($p_options_list[$i + 1], false);
                    $i++;
                    break;

                case static::PCLZIP_OPT_TEMP_FILE_THRESHOLD:
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);
                    }

                    // ----- Check for incompatible options
                    if (isset($v_result_list[static::PCLZIP_OPT_TEMP_FILE_OFF])) {
                        throw new PclZipException("Option '" . PclZipUtilOptionText($p_options_list[$i]) . "' can not be used with option 'static::PCLZIP_OPT_TEMP_FILE_OFF'", static::PCLZIP_ERR_INVALID_PARAMETER);
                    }

                    // ----- Check the value
                    $v_value = $p_options_list[$i + 1];
                    if ((!is_integer($v_value)) || ($v_value < 0)) {
                        throw new PclZipException("Integer expected for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_INVALID_OPTION_VALUE);
                    }

                    // ----- Get the value (and convert it in bytes)
                    $v_result_list[$p_options_list[$i]] = $v_value * 1048576;
                    $i++;
                    break;

                case static::PCLZIP_OPT_TEMP_FILE_ON:
                    // ----- Check for incompatible options
                    if (isset($v_result_list[static::PCLZIP_OPT_TEMP_FILE_OFF])) {
                        throw new PclZipException("Option '" . PclZipUtilOptionText($p_options_list[$i]) . "' can not be used with option 'static::PCLZIP_OPT_TEMP_FILE_OFF'", static::PCLZIP_ERR_INVALID_PARAMETER);
                    }

                    $v_result_list[$p_options_list[$i]] = true;
                    break;

                case static::PCLZIP_OPT_TEMP_FILE_OFF:
                    // ----- Check for incompatible options
                    if (isset($v_result_list[static::PCLZIP_OPT_TEMP_FILE_ON])) {
                        throw new PclZipException("Option '" . PclZipUtilOptionText($p_options_list[$i]) . "' can not be used with option 'static::PCLZIP_OPT_TEMP_FILE_ON'", static::PCLZIP_ERR_INVALID_PARAMETER);
                    }
                    // ----- Check for incompatible options
                    if (isset($v_result_list[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD])) {
                        throw new PclZipException("Option '" . PclZipUtilOptionText($p_options_list[$i]) . "' can not be used with option 'static::PCLZIP_OPT_TEMP_FILE_THRESHOLD'", static::PCLZIP_ERR_INVALID_PARAMETER);
                    }

                    $v_result_list[$p_options_list[$i]] = true;
                    break;

                case static::PCLZIP_OPT_EXTRACT_DIR_RESTRICTION:
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        // ----- Error log
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Get the value
                    if (is_string($p_options_list[$i + 1]) && ($p_options_list[$i + 1] != '')) {
                        $v_result_list[$p_options_list[$i]] = PclZipUtilTranslateWinPath($p_options_list[$i + 1], false);
                        $i++;
                    } else {
                    }
                    break;

                // ----- Look for options that request an array of string for value
                case static::PCLZIP_OPT_BY_NAME:
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        // ----- Error log
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Get the value
                    if (is_string($p_options_list[$i + 1])) {
                        $v_result_list[$p_options_list[$i]][0] = $p_options_list[$i + 1];
                    } elseif (is_array($p_options_list[$i + 1])) {
                        $v_result_list[$p_options_list[$i]] = $p_options_list[$i + 1];
                    } else {
                        // ----- Error log
                        throw new PclZipException("Wrong parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_INVALID_OPTION_VALUE);

                        // ----- Return
                    }
                    $i++;
                    break;

                // ----- Look for options that request an EREG or PREG expression
                case static::PCLZIP_OPT_BY_EREG:
                    $p_options_list[$i] = static::PCLZIP_OPT_BY_PREG;
                    // ereg() is deprecated starting with PHP 5.3. Move static::PCLZIP_OPT_BY_EREG
                    // to static::PCLZIP_OPT_BY_PREG
                    // no break
                case static::PCLZIP_OPT_BY_PREG:
                    //case static::PCLZIP_OPT_CRYPT :
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        // ----- Error log
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Get the value
                    if (is_string($p_options_list[$i + 1])) {
                        $v_result_list[$p_options_list[$i]] = $p_options_list[$i + 1];
                    } else {
                        // ----- Error log
                        throw new PclZipException("Wrong parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_INVALID_OPTION_VALUE);

                        // ----- Return
                    }
                    $i++;
                    break;

                // ----- Look for options that takes a string
                case static::PCLZIP_OPT_COMMENT:
                case static::PCLZIP_OPT_ADD_COMMENT:
                case static::PCLZIP_OPT_PREPEND_COMMENT:
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        // ----- Error log
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Get the value
                    if (is_string($p_options_list[$i + 1])) {
                        $v_result_list[$p_options_list[$i]] = $p_options_list[$i + 1];
                    } else {
                        // ----- Error log
                        throw new PclZipException("Wrong parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_INVALID_OPTION_VALUE);

                        // ----- Return
                    }
                    $i++;
                    break;

                // ----- Look for options that request an array of index
                case static::PCLZIP_OPT_BY_INDEX:
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        // ----- Error log
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Get the value
                    $v_work_list = [];
                    if (is_string($p_options_list[$i + 1])) {
                        // ----- Remove spaces
                        $p_options_list[$i + 1] = strtr($p_options_list[$i + 1], ' ', '');

                        // ----- Parse items
                        $v_work_list = explode(",", $p_options_list[$i + 1]);
                    } elseif (is_integer($p_options_list[$i + 1])) {
                        $v_work_list[0] = $p_options_list[$i + 1] . '-' . $p_options_list[$i + 1];
                    } elseif (is_array($p_options_list[$i + 1])) {
                        $v_work_list = $p_options_list[$i + 1];
                    } else {
                        // ----- Error log
                        throw new PclZipException("Value must be integer, string or array for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_INVALID_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Reduce the index list
                    // each index item in the list must be a couple with a start and
                    // an end value : [0,3], [5-5], [8-10], ...
                    // ----- Check the format of each item
                    $v_sort_flag  = false;
                    $v_sort_value = 0;
                    for ($j = 0; $j < sizeof($v_work_list); $j++) {
                        // ----- Explode the item
                        $v_item_list      = explode("-", $v_work_list[$j]);
                        $v_size_item_list = sizeof($v_item_list);

                        // ----- TBC : Here we might check that each item is a
                        // real integer ...

                        // ----- Look for single value
                        if ($v_size_item_list == 1) {
                            // ----- Set the option value
                            $v_result_list[$p_options_list[$i]][$j]['start'] = $v_item_list[0];
                            $v_result_list[$p_options_list[$i]][$j]['end']   = $v_item_list[0];
                        } elseif ($v_size_item_list == 2) {
                            // ----- Set the option value
                            $v_result_list[$p_options_list[$i]][$j]['start'] = $v_item_list[0];
                            $v_result_list[$p_options_list[$i]][$j]['end']   = $v_item_list[1];
                        } else {
                            // ----- Error log
                            throw new PclZipException("Too many values in index range for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_INVALID_OPTION_VALUE);

                            // ----- Return
                        }

                        // ----- Look for list sort
                        if ($v_result_list[$p_options_list[$i]][$j]['start'] < $v_sort_value) {
                            $v_sort_flag = true;

                            // ----- TBC : An automatic sort should be writen ...
                            // ----- Error log
                            throw new PclZipException("Invalid order of index range for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_INVALID_OPTION_VALUE);

                            // ----- Return
                        }
                        $v_sort_value = $v_result_list[$p_options_list[$i]][$j]['start'];
                    }

                    // ----- Sort the items
                    if ($v_sort_flag) {
                        // TBC : To Be Completed
                    }

                    // ----- Next option
                    $i++;
                    break;

                // ----- Look for options that request no value
                case static::PCLZIP_OPT_REMOVE_ALL_PATH:
                case static::PCLZIP_OPT_EXTRACT_AS_STRING:
                case static::PCLZIP_OPT_NO_COMPRESSION:
                case static::PCLZIP_OPT_EXTRACT_IN_OUTPUT:
                case static::PCLZIP_OPT_REPLACE_NEWER:
                case static::PCLZIP_OPT_STOP_ON_ERROR:
                    $v_result_list[$p_options_list[$i]] = true;
                    break;

                // ----- Look for options that request an octal value
                case static::PCLZIP_OPT_SET_CHMOD:
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        // ----- Error log
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Get the value
                    $v_result_list[$p_options_list[$i]] = $p_options_list[$i + 1];
                    $i++;
                    break;

                // ----- Look for options that request a call-back
                case static::PCLZIP_CB_PRE_EXTRACT:
                case static::PCLZIP_CB_POST_EXTRACT:
                case static::PCLZIP_CB_PRE_ADD:
                case static::PCLZIP_CB_POST_ADD:
                    /* for futur use
                    case static::PCLZIP_CB_PRE_DELETE :
                    case static::PCLZIP_CB_POST_DELETE :
                    case static::PCLZIP_CB_PRE_LIST :
                    case static::PCLZIP_CB_POST_LIST :
                    */
                    // ----- Check the number of parameters
                    if (($i + 1) >= $p_size) {
                        // ----- Error log
                        throw new PclZipException("Missing parameter value for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_MISSING_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Get the value
                    $v_function_name = $p_options_list[$i + 1];

                    // ----- Check that the value is a valid existing function
                    if (!function_exists($v_function_name)) {
                        // ----- Error log
                        throw new PclZipException("Function '" . $v_function_name . "()' is not an existing function for option '" . PclZipUtilOptionText($p_options_list[$i]) . "'", static::PCLZIP_ERR_INVALID_OPTION_VALUE);

                        // ----- Return
                    }

                    // ----- Set the attribute
                    $v_result_list[$p_options_list[$i]] = $v_function_name;
                    $i++;
                    break;

                default:
                    // ----- Error log
                    throw new PclZipException("Unknown parameter '" . $p_options_list[$i] . "'", static::PCLZIP_ERR_INVALID_PARAMETER);

                    // ----- Return
            }

            // ----- Next options
            $i++;
        }

        // ----- Look for mandatory options
        if ($v_requested_options !== false) {
            for ($key = reset($v_requested_options); $key = key($v_requested_options); $key = next($v_requested_options)) {
                // ----- Look for mandatory option
                if ($v_requested_options[$key] == 'mandatory') {
                    // ----- Look if present
                    if (!isset($v_result_list[$key])) {
                        // ----- Error log
                        throw new PclZipException("Missing mandatory parameter " . PclZipUtilOptionText($key) . "(" . $key . ")", static::PCLZIP_ERR_INVALID_PARAMETER);

                        // ----- Return
                    }
                }
            }
        }

        // ----- Look for default values
        if (!isset($v_result_list[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD])) {
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privOptionDefaultThreshold()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privOptionDefaultThreshold(&$p_options)
    {
        $v_result = 1;

        if (isset($p_options[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD]) || isset($p_options[static::PCLZIP_OPT_TEMP_FILE_OFF])) {
            return $v_result;
        }

        // ----- Get 'memory_limit' configuration value
        $v_memory_limit = ini_get('memory_limit');
        $v_memory_limit = trim($v_memory_limit);
        $last           = strtolower(substr($v_memory_limit, -1));
        $v_memory_limit = preg_replace('/\s*[KkMmGg]$/', '', $v_memory_limit);

        if ($last == 'g') {
            //$v_memory_limit = $v_memory_limit*1024*1024*1024;
            $v_memory_limit = $v_memory_limit * 1073741824;
        }
        if ($last == 'm') {
            //$v_memory_limit = $v_memory_limit*1024*1024;
            $v_memory_limit = $v_memory_limit * 1048576;
        }
        if ($last == 'k') {
            $v_memory_limit = $v_memory_limit * 1024;
        }

        $p_options[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD] = floor($v_memory_limit * static::PCLZIP_TEMPORARY_FILE_RATIO);

        // ----- Sanity check : No threshold if value lower than 1M
        if ($p_options[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD] < 1048576) {
            unset($p_options[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD]);
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privFileDescrParseAtt()
    // Description :
    // Parameters :
    // Return Values :
    //   1 on success.
    //   0 on failure.
    // --------------------------------------------------------------------------------
    protected function privFileDescrParseAtt(&$p_file_list, &$p_filedescr, $v_options, $v_requested_options = false)
    {
        $v_result = 1;

        // ----- For each file in the list check the attributes
        foreach ($p_file_list as $v_key => $v_value) {
            // ----- Check if the option is supported
            if (!isset($v_requested_options[$v_key])) {
                throw new PclZipException("Invalid file attribute '" . $v_key . "' for this file", static::PCLZIP_ERR_INVALID_PARAMETER);

                // ----- Return
            }

            // ----- Look for attribute
            switch ($v_key) {
                case static::PCLZIP_ATT_FILE_NAME:
                    if (!is_string($v_value)) {
                        throw new PclZipException("Invalid type " . gettype($v_value) . ". String expected for attribute '" . PclZipUtilOptionText($v_key) . "'", static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE);
                    }

                    $p_filedescr['filename'] = PclZipUtilPathReduction($v_value);

                    if ($p_filedescr['filename'] == '') {
                        throw new PclZipException("Invalid empty filename for attribute '" . PclZipUtilOptionText($v_key) . "'", static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE);
                    }

                    break;

                case static::PCLZIP_ATT_FILE_NEW_SHORT_NAME:
                    if (!is_string($v_value)) {
                        throw new PclZipException("Invalid type " . gettype($v_value) . ". String expected for attribute '" . PclZipUtilOptionText($v_key) . "'", static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE);
                    }

                    $p_filedescr['new_short_name'] = PclZipUtilPathReduction($v_value);

                    if ($p_filedescr['new_short_name'] == '') {
                        throw new PclZipException("Invalid empty short filename for attribute '" . PclZipUtilOptionText($v_key) . "'", static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE);
                    }
                    break;

                case static::PCLZIP_ATT_FILE_NEW_FULL_NAME:
                    if (!is_string($v_value)) {
                        throw new PclZipException("Invalid type " . gettype($v_value) . ". String expected for attribute '" . PclZipUtilOptionText($v_key) . "'", static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE);
                    }

                    $p_filedescr['new_full_name'] = PclZipUtilPathReduction($v_value);

                    if ($p_filedescr['new_full_name'] == '') {
                        throw new PclZipException("Invalid empty full filename for attribute '" . PclZipUtilOptionText($v_key) . "'", static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE);
                    }
                    break;

                // ----- Look for options that takes a string
                case static::PCLZIP_ATT_FILE_COMMENT:
                    if (!is_string($v_value)) {
                        throw new PclZipException("Invalid type " . gettype($v_value) . ". String expected for attribute '" . PclZipUtilOptionText($v_key) . "'", static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE);
                    }

                    $p_filedescr['comment'] = $v_value;
                    break;

                case static::PCLZIP_ATT_FILE_MTIME:
                    if (!is_integer($v_value)) {
                        throw new PclZipException("Invalid type " . gettype($v_value) . ". Integer expected for attribute '" . PclZipUtilOptionText($v_key) . "'", static::PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE);
                    }

                    $p_filedescr['mtime'] = $v_value;
                    break;

                case static::PCLZIP_ATT_FILE_CONTENT:
                    $p_filedescr['content'] = $v_value;
                    break;

                default:
                    // ----- Error log
                    throw new PclZipException("Unknown parameter '" . $v_key . "'", static::PCLZIP_ERR_INVALID_PARAMETER);

                    // ----- Return
            }

            // ----- Look for mandatory options
            if ($v_requested_options !== false) {
                for ($key = reset($v_requested_options); $key = key($v_requested_options); $key = next($v_requested_options)) {
                    // ----- Look for mandatory option
                    if ($v_requested_options[$key] == 'mandatory') {
                        // ----- Look if present
                        if (!isset($p_file_list[$key])) {
                            throw new PclZipException("Missing mandatory parameter " . PclZipUtilOptionText($key) . "(" . $key . ")", static::PCLZIP_ERR_INVALID_PARAMETER);
                        }
                    }
                }
            }

            // end foreach
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privFileDescrExpand()
    // Description :
    //   This method look for each item of the list to see if its a file, a folder
    //   or a string to be added as file. For any other type of files (link, other)
    //   just ignore the item.
    //   Then prepare the information that will be stored for that file.
    //   When its a folder, expand the folder with all the files that are in that
    //   folder (recursively).
    // Parameters :
    // Return Values :
    //   1 on success.
    //   0 on failure.
    // --------------------------------------------------------------------------------
    protected function privFileDescrExpand(&$p_filedescr_list, &$p_options)
    {
        $v_result = 1;

        // ----- Create a result list
        $v_result_list = [];

        // ----- Look each entry
        for ($i = 0; $i < sizeof($p_filedescr_list); $i++) {
            // ----- Get filedescr
            $v_descr = $p_filedescr_list[$i];

            // ----- Reduce the filename
            $v_descr['filename'] = PclZipUtilTranslateWinPath($v_descr['filename'], false);
            $v_descr['filename'] = PclZipUtilPathReduction($v_descr['filename']);

            // ----- Look for real file or folder
            if (file_exists($v_descr['filename'])) {
                if (@is_file($v_descr['filename'])) {
                    $v_descr['type'] = 'file';
                } elseif (@is_dir($v_descr['filename'])) {
                    $v_descr['type'] = 'folder';
                } elseif (@is_link($v_descr['filename'])) {
                    // skip
                    continue;
                } else {
                    // skip
                    continue;
                }

                // ----- Look for string added as file
            } elseif (isset($v_descr['content'])) {
                $v_descr['type'] = 'virtual_file';

            // ----- Missing file
            } else {
                throw new PclZipException("File '" . $v_descr['filename'] . "' does not exist", static::PCLZIP_ERR_MISSING_FILE);

                // ----- Return
            }

            // ----- Calculate the stored filename
            $this->privCalculateStoredFilename($v_descr, $p_options);

            // ----- Add the descriptor in result list
            $v_result_list[sizeof($v_result_list)] = $v_descr;

            // ----- Look for folder
            if ($v_descr['type'] == 'folder') {
                // ----- List of items in folder
                $v_dirlist_descr = [];
                $v_dirlist_nb    = 0;
                if ($v_folder_handler = @opendir($v_descr['filename'])) {
                    while (($v_item_handler = @readdir($v_folder_handler)) !== false) {
                        // ----- Skip '.' and '..'
                        if (($v_item_handler == '.') || ($v_item_handler == '..')) {
                            continue;
                        }

                        // ----- Compose the full filename
                        $v_dirlist_descr[$v_dirlist_nb]['filename'] = $v_descr['filename'] . '/' . $v_item_handler;

                        // ----- Look for different stored filename
                        // Because the name of the folder was changed, the name of the
                        // files/sub-folders also change
                        if (($v_descr['stored_filename'] != $v_descr['filename']) && (!isset($p_options[static::PCLZIP_OPT_REMOVE_ALL_PATH]))) {
                            if ($v_descr['stored_filename'] != '') {
                                $v_dirlist_descr[$v_dirlist_nb]['new_full_name'] = $v_descr['stored_filename'] . '/' . $v_item_handler;
                            } else {
                                $v_dirlist_descr[$v_dirlist_nb]['new_full_name'] = $v_item_handler;
                            }
                        }

                        $v_dirlist_nb++;
                    }

                    @closedir($v_folder_handler);
                } else {
                    // TBC : unable to open folder in read mode
                }

                // ----- Expand each element of the list
                if ($v_dirlist_nb != 0) {
                    // ----- Expand
                    if (($v_result = $this->privFileDescrExpand($v_dirlist_descr, $p_options)) != 1) {
                        return $v_result;
                    }

                    // ----- Concat the resulting list
                    $v_result_list = array_merge($v_result_list, $v_dirlist_descr);
                } else {
                }

                // ----- Free local array
                unset($v_dirlist_descr);
            }
        }

        // ----- Get the result list
        $p_filedescr_list = $v_result_list;

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privCreate()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privCreate($p_filedescr_list, &$p_result_list, &$p_options)
    {
        $v_result      = 1;
        $v_list_detail = [];


        // ----- Open the file in write mode
        if (($v_result = $this->privOpenFd('wb')) != 1) {
            // ----- Return
            return $v_result;
        }

        // ----- Add the list of files
        $v_result = $this->privAddList($p_filedescr_list, $p_result_list, $p_options);

        // ----- Close
        $this->privCloseFd();

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privAdd()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privAdd($p_filedescr_list, &$p_result_list, &$p_options)
    {
        $v_result      = 1;
        $v_list_detail = [];

        // ----- Look if the archive exists or is empty
        if ((!is_file($this->zipname)) || (filesize($this->zipname) == 0)) {
            // ----- Do a create
            $v_result = $this->privCreate($p_filedescr_list, $p_result_list, $p_options);

            // ----- Return
            return $v_result;
        }

        // ----- Open the zip file
        if (($v_result = $this->privOpenFd('rb')) != 1) {
            // ----- Return
            return $v_result;
        }

        // ----- Read the central directory informations
        $v_central_dir = [];
        if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1) {
            $this->privCloseFd();

            return $v_result;
        }

        // ----- Go to beginning of File
        @rewind($this->zip_fd);

        // ----- Creates a temporay file
        $v_zip_temp_name = static::PCLZIP_TEMPORARY_DIR . uniqid('pclzip-') . '.tmp';

        // ----- Open the temporary file in write mode
        if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0) {
            $this->privCloseFd();

            throw new PclZipException('Unable to open temporary file \'' . $v_zip_temp_name . '\' in binary write mode', static::PCLZIP_ERR_READ_OPEN_FAIL);

            // ----- Return
        }

        // ----- Copy the files from the archive to the temporary file
        // TBC : Here I should better append the file and go back to erase the central dir
        $v_size = $v_central_dir['offset'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = fread($this->zip_fd, $v_read_size);
            @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Swap the file descriptor
        // Here is a trick : I swap the temporary fd with the zip fd, in order to use
        // the following methods on the temporary fil and not the real archive
        $v_swap        = $this->zip_fd;
        $this->zip_fd  = $v_zip_temp_fd;
        $v_zip_temp_fd = $v_swap;

        // ----- Add the files
        $v_header_list = [];
        if (($v_result = $this->privAddFileList($p_filedescr_list, $v_header_list, $p_options)) != 1) {
            fclose($v_zip_temp_fd);
            $this->privCloseFd();
            @unlink($v_zip_temp_name);

            // ----- Return
            return $v_result;
        }

        // ----- Store the offset of the central dir
        $v_offset = @ftell($this->zip_fd);

        // ----- Copy the block of file headers from the old archive
        $v_size = $v_central_dir['size'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = @fread($v_zip_temp_fd, $v_read_size);
            @fwrite($this->zip_fd, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Create the Central Dir files header
        for ($i = 0, $v_count = 0; $i < sizeof($v_header_list); $i++) {
            // ----- Create the file header
            if ($v_header_list[$i]['status'] == 'ok') {
                if (($v_result = $this->privWriteCentralFileHeader($v_header_list[$i])) != 1) {
                    fclose($v_zip_temp_fd);
                    $this->privCloseFd();
                    @unlink($v_zip_temp_name);

                    // ----- Return
                    return $v_result;
                }
                $v_count++;
            }

            // ----- Transform the header to a 'usable' info
            $this->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
        }

        // ----- Zip file comment
        $v_comment = $v_central_dir['comment'];
        if (isset($p_options[static::PCLZIP_OPT_COMMENT])) {
            $v_comment = $p_options[static::PCLZIP_OPT_COMMENT];
        }
        if (isset($p_options[static::PCLZIP_OPT_ADD_COMMENT])) {
            $v_comment = $v_comment . $p_options[static::PCLZIP_OPT_ADD_COMMENT];
        }
        if (isset($p_options[static::PCLZIP_OPT_PREPEND_COMMENT])) {
            $v_comment = $p_options[static::PCLZIP_OPT_PREPEND_COMMENT] . $v_comment;
        }

        // ----- Calculate the size of the central header
        $v_size = @ftell($this->zip_fd) - $v_offset;

        // ----- Create the central dir footer
        if (($v_result = $this->privWriteCentralHeader($v_count + $v_central_dir['entries'], $v_size, $v_offset, $v_comment)) != 1) {
            // ----- Reset the file list
            unset($v_header_list);

            // ----- Return
            return $v_result;
        }

        // ----- Swap back the file descriptor
        $v_swap        = $this->zip_fd;
        $this->zip_fd  = $v_zip_temp_fd;
        $v_zip_temp_fd = $v_swap;

        // ----- Close
        $this->privCloseFd();

        // ----- Close the temporary file
        @fclose($v_zip_temp_fd);


        // ----- Delete the zip file
        // TBC : I should test the result ...
        @unlink($this->zipname);

        // ----- Rename the temporary file
        // TBC : I should test the result ...
        //@rename($v_zip_temp_name, $this->zipname);
        PclZipUtilRename($v_zip_temp_name, $this->zipname);

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privOpenFd()
    // Description :
    // Parameters :
    // --------------------------------------------------------------------------------
    protected function privOpenFd($p_mode)
    {
        $v_result = 1;

        // ----- Look if already open
        if ($this->zip_fd != 0) {
            throw new PclZipException('Zip file \'' . $this->zipname . '\' already open', static::PCLZIP_ERR_READ_OPEN_FAIL);

            // ----- Return
        }

        // ----- Open the zip file
        if (($this->zip_fd = @fopen($this->zipname, $p_mode)) == 0) {
            throw new PclZipException('Unable to open archive \'' . $this->zipname . '\' in ' . $p_mode . ' mode', static::PCLZIP_ERR_READ_OPEN_FAIL);

            // ----- Return
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privCloseFd()
    // Description :
    // Parameters :
    // --------------------------------------------------------------------------------
    protected function privCloseFd()
    {
        $v_result = 1;

        if ($this->zip_fd != 0) {
            @fclose($this->zip_fd);
        }
        $this->zip_fd = 0;

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privAddList()
    // Description :
    //   $p_add_dir and $p_remove_dir will give the ability to memorize a path which is
    //   different from the real path of the file. This is usefull if you want to have PclTar
    //   running in any directory, and memorize relative path from an other directory.
    // Parameters :
    //   $p_list : An array containing the file or directory names to add in the tar
    //   $p_result_list : list of added files with their properties (specially the status field)
    //   $p_add_dir : Path to add in the filename path archived
    //   $p_remove_dir : Path to remove in the filename path archived
    // Return Values :
    // --------------------------------------------------------------------------------
    //  function privAddList($p_list, &$p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_options)
    protected function privAddList($p_filedescr_list, &$p_result_list, &$p_options)
    {
        $v_result = 1;

        // ----- Add the files
        $v_header_list = [];
        if (($v_result = $this->privAddFileList($p_filedescr_list, $v_header_list, $p_options)) != 1) {
            // ----- Return
            return $v_result;
        }

        // ----- Store the offset of the central dir
        $v_offset = @ftell($this->zip_fd);

        // ----- Create the Central Dir files header
        for ($i = 0, $v_count = 0; $i < sizeof($v_header_list); $i++) {
            // ----- Create the file header
            if ($v_header_list[$i]['status'] == 'ok') {
                if (($v_result = $this->privWriteCentralFileHeader($v_header_list[$i])) != 1) {
                    // ----- Return
                    return $v_result;
                }
                $v_count++;
            }

            // ----- Transform the header to a 'usable' info
            $this->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
        }

        // ----- Zip file comment
        $v_comment = '';
        if (isset($p_options[static::PCLZIP_OPT_COMMENT])) {
            $v_comment = $p_options[static::PCLZIP_OPT_COMMENT];
        }

        // ----- Calculate the size of the central header
        $v_size = @ftell($this->zip_fd) - $v_offset;

        // ----- Create the central dir footer
        if (($v_result = $this->privWriteCentralHeader($v_count, $v_size, $v_offset, $v_comment)) != 1) {
            // ----- Reset the file list
            unset($v_header_list);

            // ----- Return
            return $v_result;
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privAddFileList()
    // Description :
    // Parameters :
    //   $p_filedescr_list : An array containing the file description
    //                      or directory names to add in the zip
    //   $p_result_list : list of added files with their properties (specially the status field)
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privAddFileList($p_filedescr_list, &$p_result_list, &$p_options)
    {
        $v_result = 1;
        $v_header = [];

        // ----- Recuperate the current number of elt in list
        $v_nb = sizeof($p_result_list);

        // ----- Loop on the files
        for ($j = 0; ($j < sizeof($p_filedescr_list)) && ($v_result == 1); $j++) {
            // ----- Format the filename
            $p_filedescr_list[$j]['filename'] = PclZipUtilTranslateWinPath($p_filedescr_list[$j]['filename'], false);

            // ----- Skip empty file names
            // TBC : Can this be possible ? not checked in DescrParseAtt ?
            if ($p_filedescr_list[$j]['filename'] == "") {
                continue;
            }

            // ----- Check the filename
            if (($p_filedescr_list[$j]['type'] != 'virtual_file') && (!file_exists($p_filedescr_list[$j]['filename']))) {
                throw new PclZipException("File '" . $p_filedescr_list[$j]['filename'] . "' does not exist", static::PCLZIP_ERR_MISSING_FILE);
            }

            // ----- Look if it is a file or a dir with no all path remove option
            // or a dir with all its path removed
            //      if (   (is_file($p_filedescr_list[$j]['filename']))
            //          || (   is_dir($p_filedescr_list[$j]['filename'])
            if (($p_filedescr_list[$j]['type'] == 'file') || ($p_filedescr_list[$j]['type'] == 'virtual_file') || (($p_filedescr_list[$j]['type'] == 'folder') && (!isset($p_options[static::PCLZIP_OPT_REMOVE_ALL_PATH]) || !$p_options[static::PCLZIP_OPT_REMOVE_ALL_PATH]))) {
                // ----- Add the file
                $v_result = $this->privAddFile($p_filedescr_list[$j], $v_header, $p_options);
                if ($v_result != 1) {
                    return $v_result;
                }

                // ----- Store the file infos
                $p_result_list[$v_nb++] = $v_header;
            }
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privAddFile()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privAddFile($p_filedescr, &$p_header, &$p_options)
    {
        $v_result = 1;

        // ----- Working variable
        $p_filename = $p_filedescr['filename'];

        // TBC : Already done in the fileAtt check ... ?
        if ($p_filename == "") {
            throw new PclZipException("Invalid file list parameter (invalid or empty list)", static::PCLZIP_ERR_INVALID_PARAMETER);

            // ----- Return
        }

        // ----- Look for a stored different filename
        /* TBC : Removed
        if (isset($p_filedescr['stored_filename'])) {
        $v_stored_filename = $p_filedescr['stored_filename'];
        } else {
        $v_stored_filename = $p_filedescr['stored_filename'];
        }
        */

        // ----- Set the file properties
        clearstatcache();
        $p_header['version']           = 20;
        $p_header['version_extracted'] = 10;
        $p_header['flag']              = 0;
        $p_header['compression']       = 0;
        $p_header['crc']               = 0;
        $p_header['compressed_size']   = 0;
        $p_header['filename_len']      = strlen($p_filename);
        $p_header['extra_len']         = 0;
        $p_header['disk']              = 0;
        $p_header['internal']          = 0;
        $p_header['offset']            = 0;
        $p_header['filename']          = $p_filename;
        // TBC : Removed    $p_header['stored_filename'] = $v_stored_filename;
        $p_header['stored_filename']   = $p_filedescr['stored_filename'];
        $p_header['extra']             = '';
        $p_header['status']            = 'ok';
        $p_header['index']             = -1;

        // ----- Look for regular file
        if ($p_filedescr['type'] == 'file') {
            $p_header['external'] = 0x00000000;
            $p_header['size']     = filesize($p_filename);

        // ----- Look for regular folder
        } elseif ($p_filedescr['type'] == 'folder') {
            $p_header['external'] = 0x00000010;
            $p_header['mtime']    = filemtime($p_filename);
            $p_header['size']     = filesize($p_filename);

        // ----- Look for virtual file
        } elseif ($p_filedescr['type'] == 'virtual_file') {
            $p_header['external'] = 0x00000000;
            $p_header['size']     = strlen($p_filedescr['content']);
        }

        // ----- Look for filetime
        if (isset($p_filedescr['mtime'])) {
            $p_header['mtime'] = $p_filedescr['mtime'];
        } elseif ($p_filedescr['type'] == 'virtual_file') {
            $p_header['mtime'] = time();
        } else {
            $p_header['mtime'] = filemtime($p_filename);
        }

        // ------ Look for file comment
        if (isset($p_filedescr['comment'])) {
            $p_header['comment_len'] = strlen($p_filedescr['comment']);
            $p_header['comment']     = $p_filedescr['comment'];
        } else {
            $p_header['comment_len'] = 0;
            $p_header['comment']     = '';
        }

        // ----- Look for pre-add callback
        if (isset($p_options[static::PCLZIP_CB_PRE_ADD])) {
            // ----- Generate a local information
            $v_local_header = [];
            $this->privConvertHeader2FileInfo($p_header, $v_local_header);

            // ----- Call the callback
            // Here I do not use call_user_func() because I need to send a reference to the
            // header.
            //      eval('$v_result = '.$p_options[static::PCLZIP_CB_PRE_ADD].'(static::PCLZIP_CB_PRE_ADD, $v_local_header);');
            $v_result = $p_options[static::PCLZIP_CB_PRE_ADD](static::PCLZIP_CB_PRE_ADD, $v_local_header);
            if ($v_result == 0) {
                // ----- Change the file status
                $p_header['status'] = "skipped";
                $v_result           = 1;
            }

            // ----- Update the informations
            // Only some fields can be modified
            if ($p_header['stored_filename'] != $v_local_header['stored_filename']) {
                $p_header['stored_filename'] = PclZipUtilPathReduction($v_local_header['stored_filename']);
            }
        }

        // ----- Look for empty stored filename
        if ($p_header['stored_filename'] == "") {
            $p_header['status'] = "filtered";
        }

        // ----- Check the path length
        if (strlen($p_header['stored_filename']) > 0xFF) {
            $p_header['status'] = 'filename_too_long';
        }

        // ----- Look if no error, or file not skipped
        if ($p_header['status'] == 'ok') {
            // ----- Look for a file
            if ($p_filedescr['type'] == 'file') {
                // ----- Look for using temporary file to zip
                if ((!isset($p_options[static::PCLZIP_OPT_TEMP_FILE_OFF])) && (isset($p_options[static::PCLZIP_OPT_TEMP_FILE_ON]) || (isset($p_options[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD]) && ($p_options[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD] <= $p_header['size'])))) {
                    $v_result = $this->privAddFileUsingTempFile($p_filedescr, $p_header, $p_options);
                    if ($v_result < static::PCLZIP_ERR_NO_ERROR) {
                        return $v_result;
                    }

                    // ----- Use "in memory" zip algo
                } else {
                    // ----- Open the source file
                    if (($v_file = @fopen($p_filename, "rb")) == 0) {
                        throw new PclZipException("Unable to open file '$p_filename' in binary read mode", static::PCLZIP_ERR_READ_OPEN_FAIL);
                    }

                    // ----- Read the file content
                    $v_content = @fread($v_file, $p_header['size']);

                    // ----- Close the file
                    @fclose($v_file);

                    // ----- Calculate the CRC
                    $p_header['crc'] = @crc32($v_content);

                    // ----- Look for no compression
                    if ($p_options[static::PCLZIP_OPT_NO_COMPRESSION]) {
                        // ----- Set header parameters
                        $p_header['compressed_size'] = $p_header['size'];
                        $p_header['compression']     = 0;

                    // ----- Look for normal compression
                    } else {
                        // ----- Compress the content
                        $v_content = @gzdeflate($v_content);

                        // ----- Set header parameters
                        $p_header['compressed_size'] = strlen($v_content);
                        $p_header['compression']     = 8;
                    }

                    // ----- Call the header generation
                    if (($v_result = $this->privWriteFileHeader($p_header)) != 1) {
                        @fclose($v_file);

                        return $v_result;
                    }

                    // ----- Write the compressed (or not) content
                    @fwrite($this->zip_fd, $v_content, $p_header['compressed_size']);
                }

                // ----- Look for a virtual file (a file from string)
            } elseif ($p_filedescr['type'] == 'virtual_file') {
                $v_content = $p_filedescr['content'];

                // ----- Calculate the CRC
                $p_header['crc'] = @crc32($v_content);

                // ----- Look for no compression
                if ($p_options[static::PCLZIP_OPT_NO_COMPRESSION]) {
                    // ----- Set header parameters
                    $p_header['compressed_size'] = $p_header['size'];
                    $p_header['compression']     = 0;

                // ----- Look for normal compression
                } else {
                    // ----- Compress the content
                    $v_content = @gzdeflate($v_content);

                    // ----- Set header parameters
                    $p_header['compressed_size'] = strlen($v_content);
                    $p_header['compression']     = 8;
                }

                // ----- Call the header generation
                if (($v_result = $this->privWriteFileHeader($p_header)) != 1) {
                    @fclose($v_file);

                    return $v_result;
                }

                // ----- Write the compressed (or not) content
                @fwrite($this->zip_fd, $v_content, $p_header['compressed_size']);

            // ----- Look for a directory
            } elseif ($p_filedescr['type'] == 'folder') {
                // ----- Look for directory last '/'
                if (@substr($p_header['stored_filename'], -1) != '/') {
                    $p_header['stored_filename'] .= '/';
                }

                // ----- Set the file properties
                $p_header['size']     = 0;
                //$p_header['external'] = 0x41FF0010;   // Value for a folder : to be checked
                $p_header['external'] = 0x00000010; // Value for a folder : to be checked

                // ----- Call the header generation
                if (($v_result = $this->privWriteFileHeader($p_header)) != 1) {
                    return $v_result;
                }
            }
        }

        // ----- Look for post-add callback
        if (isset($p_options[static::PCLZIP_CB_POST_ADD])) {
            // ----- Generate a local information
            $v_local_header = [];
            $this->privConvertHeader2FileInfo($p_header, $v_local_header);

            // ----- Call the callback
            // Here I do not use call_user_func() because I need to send a reference to the
            // header.
            //      eval('$v_result = '.$p_options[static::PCLZIP_CB_POST_ADD].'(static::PCLZIP_CB_POST_ADD, $v_local_header);');
            $v_result = $p_options[static::PCLZIP_CB_POST_ADD](static::PCLZIP_CB_POST_ADD, $v_local_header);
            if ($v_result == 0) {
                // ----- Ignored
                $v_result = 1;
            }

            // ----- Update the informations
            // Nothing can be modified
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privAddFileUsingTempFile()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privAddFileUsingTempFile($p_filedescr, &$p_header, &$p_options)
    {
        $v_result = static::PCLZIP_ERR_NO_ERROR;

        // ----- Working variable
        $p_filename = $p_filedescr['filename'];

        // ----- Open the source file
        if (($v_file = @fopen($p_filename, "rb")) == 0) {
            throw new PclZipException("Unable to open file '$p_filename' in binary read mode", static::PCLZIP_ERR_READ_OPEN_FAIL);
        }

        // ----- Creates a compressed temporary file
        $v_gzip_temp_name = static::PCLZIP_TEMPORARY_DIR . uniqid('pclzip-') . '.gz';
        if (($v_file_compressed = @gzopen($v_gzip_temp_name, "wb")) == 0) {
            fclose($v_file);
            throw new PclZipException('Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary write mode', static::PCLZIP_ERR_WRITE_OPEN_FAIL);
        }

        // ----- Read the file by static::PCLZIP_READ_BLOCK_SIZE octets blocks
        $v_size = filesize($p_filename);
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = @fread($v_file, $v_read_size);
            //$v_binary_data = pack('a'.$v_read_size, $v_buffer);
            @gzputs($v_file_compressed, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Close the file
        @fclose($v_file);
        @gzclose($v_file_compressed);

        // ----- Check the minimum file size
        if (filesize($v_gzip_temp_name) < 18) {
            throw new PclZipException('gzip temporary file \'' . $v_gzip_temp_name . '\' has invalid filesize - should be minimum 18 bytes', static::PCLZIP_ERR_BAD_FORMAT);
        }

        // ----- Extract the compressed attributes
        if (($v_file_compressed = @fopen($v_gzip_temp_name, "rb")) == 0) {
            throw new PclZipException('Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary read mode', static::PCLZIP_ERR_READ_OPEN_FAIL);
        }

        // ----- Read the gzip file header
        $v_binary_data = @fread($v_file_compressed, 10);
        $v_data_header = unpack('a1id1/a1id2/a1cm/a1flag/Vmtime/a1xfl/a1os', $v_binary_data);

        // ----- Check some parameters
        $v_data_header['os'] = bin2hex($v_data_header['os']);

        // ----- Read the gzip file footer
        @fseek($v_file_compressed, filesize($v_gzip_temp_name) - 8);
        $v_binary_data = @fread($v_file_compressed, 8);
        $v_data_footer = unpack('Vcrc/Vcompressed_size', $v_binary_data);

        // ----- Set the attributes
        $p_header['compression']     = ord($v_data_header['cm']);
        //$p_header['mtime'] = $v_data_header['mtime'];
        $p_header['crc']             = $v_data_footer['crc'];
        $p_header['compressed_size'] = filesize($v_gzip_temp_name) - 18;

        // ----- Close the file
        @fclose($v_file_compressed);

        // ----- Call the header generation
        if (($v_result = $this->privWriteFileHeader($p_header)) != 1) {
            return $v_result;
        }

        // ----- Add the compressed data
        if (($v_file_compressed = @fopen($v_gzip_temp_name, "rb")) == 0) {
            throw new PclZipException('Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary read mode', static::PCLZIP_ERR_READ_OPEN_FAIL);
        }

        // ----- Read the file by static::PCLZIP_READ_BLOCK_SIZE octets blocks
        fseek($v_file_compressed, 10);
        $v_size = $p_header['compressed_size'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = @fread($v_file_compressed, $v_read_size);
            //$v_binary_data = pack('a'.$v_read_size, $v_buffer);
            @fwrite($this->zip_fd, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Close the file
        @fclose($v_file_compressed);

        // ----- Unlink the temporary file
        @unlink($v_gzip_temp_name);

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privCalculateStoredFilename()
    // Description :
    //   Based on file descriptor properties and global options, this method
    //   calculate the filename that will be stored in the archive.
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privCalculateStoredFilename(&$p_filedescr, &$p_options)
    {
        $v_result = 1;

        // ----- Working variables
        $p_filename = $p_filedescr['filename'];
        if (isset($p_options[static::PCLZIP_OPT_ADD_PATH])) {
            $p_add_dir = $p_options[static::PCLZIP_OPT_ADD_PATH];
        } else {
            $p_add_dir = '';
        }
        if (isset($p_options[static::PCLZIP_OPT_REMOVE_PATH])) {
            $p_remove_dir = $p_options[static::PCLZIP_OPT_REMOVE_PATH];
        } else {
            $p_remove_dir = '';
        }
        if (isset($p_options[static::PCLZIP_OPT_REMOVE_ALL_PATH])) {
            $p_remove_all_dir = $p_options[static::PCLZIP_OPT_REMOVE_ALL_PATH];
        } else {
            $p_remove_all_dir = 0;
        }

        // ----- Look for full name change
        if (isset($p_filedescr['new_full_name'])) {
            // ----- Remove drive letter if any
            $v_stored_filename = PclZipUtilTranslateWinPath($p_filedescr['new_full_name']);

        // ----- Look for path and/or short name change
        } else {
            // ----- Look for short name change
            // Its when we cahnge just the filename but not the path
            if (isset($p_filedescr['new_short_name'])) {
                $v_path_info = pathinfo($p_filename);
                $v_dir       = '';
                if ($v_path_info['dirname'] != '') {
                    $v_dir = $v_path_info['dirname'] . '/';
                }
                $v_stored_filename = $v_dir . $p_filedescr['new_short_name'];
            } else {
                // ----- Calculate the stored filename
                $v_stored_filename = $p_filename;
            }

            // ----- Look for all path to remove
            if ($p_remove_all_dir) {
                $v_stored_filename = basename($p_filename);

            // ----- Look for partial path remove
            } elseif ($p_remove_dir != "") {
                if (substr($p_remove_dir, -1) != '/') {
                    $p_remove_dir .= "/";
                }

                if ((substr($p_filename, 0, 2) == "./") || (substr($p_remove_dir, 0, 2) == "./")) {
                    if ((substr($p_filename, 0, 2) == "./") && (substr($p_remove_dir, 0, 2) != "./")) {
                        $p_remove_dir = "./" . $p_remove_dir;
                    }
                    if ((substr($p_filename, 0, 2) != "./") && (substr($p_remove_dir, 0, 2) == "./")) {
                        $p_remove_dir = substr($p_remove_dir, 2);
                    }
                }

                $v_compare = PclZipUtilPathInclusion($p_remove_dir, $v_stored_filename);
                if ($v_compare > 0) {
                    if ($v_compare == 2) {
                        $v_stored_filename = "";
                    } else {
                        $v_stored_filename = substr($v_stored_filename, strlen($p_remove_dir));
                    }
                }
            }

            // ----- Remove drive letter if any
            $v_stored_filename = PclZipUtilTranslateWinPath($v_stored_filename);

            // ----- Look for path to add
            if ($p_add_dir != "") {
                if (substr($p_add_dir, -1) == "/") {
                    $v_stored_filename = $p_add_dir . $v_stored_filename;
                } else {
                    $v_stored_filename = $p_add_dir . "/" . $v_stored_filename;
                }
            }
        }

        // ----- Filename (reduce the path of stored name)
        $v_stored_filename              = PclZipUtilPathReduction($v_stored_filename);
        $p_filedescr['stored_filename'] = $v_stored_filename;

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privWriteFileHeader()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privWriteFileHeader(&$p_header)
    {
        $v_result = 1;

        // ----- Store the offset position of the file
        $p_header['offset'] = ftell($this->zip_fd);

        // ----- Transform UNIX mtime to DOS format mdate/mtime
        $v_date  = getdate($p_header['mtime']);
        $v_mtime = ($v_date['hours'] << 11) + ($v_date['minutes'] << 5) + $v_date['seconds'] / 2;
        $v_mdate = (($v_date['year'] - 1980) << 9) + ($v_date['mon'] << 5) + $v_date['mday'];

        // ----- Packed data
        $v_binary_data = pack("VvvvvvVVVvv", 0x04034b50, $p_header['version_extracted'], $p_header['flag'], $p_header['compression'], $v_mtime, $v_mdate, $p_header['crc'], $p_header['compressed_size'], $p_header['size'], strlen($p_header['stored_filename']), $p_header['extra_len']);

        // ----- Write the first 148 bytes of the header in the archive
        fputs($this->zip_fd, $v_binary_data, 30);

        // ----- Write the variable fields
        if (strlen($p_header['stored_filename']) != 0) {
            fputs($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
        }
        if ($p_header['extra_len'] != 0) {
            fputs($this->zip_fd, $p_header['extra'], $p_header['extra_len']);
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privWriteCentralFileHeader()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privWriteCentralFileHeader(&$p_header)
    {
        $v_result = 1;

        // TBC
        //for (reset($p_header); $key = key($p_header); next($p_header)) {
        //}

        // ----- Transform UNIX mtime to DOS format mdate/mtime
        $v_date  = getdate($p_header['mtime']);
        $v_mtime = ($v_date['hours'] << 11) + ($v_date['minutes'] << 5) + $v_date['seconds'] / 2;
        $v_mdate = (($v_date['year'] - 1980) << 9) + ($v_date['mon'] << 5) + $v_date['mday'];

        // ----- Packed data
        $v_binary_data = pack("VvvvvvvVVVvvvvvVV", 0x02014b50, $p_header['version'], $p_header['version_extracted'], $p_header['flag'], $p_header['compression'], $v_mtime, $v_mdate, $p_header['crc'], $p_header['compressed_size'], $p_header['size'], strlen($p_header['stored_filename']), $p_header['extra_len'], $p_header['comment_len'], $p_header['disk'], $p_header['internal'], $p_header['external'], $p_header['offset']);

        // ----- Write the 42 bytes of the header in the zip file
        fputs($this->zip_fd, $v_binary_data, 46);

        // ----- Write the variable fields
        if (strlen($p_header['stored_filename']) != 0) {
            fputs($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
        }
        if ($p_header['extra_len'] != 0) {
            fputs($this->zip_fd, $p_header['extra'], $p_header['extra_len']);
        }
        if ($p_header['comment_len'] != 0) {
            fputs($this->zip_fd, $p_header['comment'], $p_header['comment_len']);
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privWriteCentralHeader()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privWriteCentralHeader($p_nb_entries, $p_size, $p_offset, $p_comment)
    {
        $v_result = 1;

        // ----- Packed data
        $v_binary_data = pack("VvvvvVVv", 0x06054b50, 0, 0, $p_nb_entries, $p_nb_entries, $p_size, $p_offset, strlen($p_comment));

        // ----- Write the 22 bytes of the header in the zip file
        fputs($this->zip_fd, $v_binary_data, 22);

        // ----- Write the variable fields
        if (strlen($p_comment) != 0) {
            fputs($this->zip_fd, $p_comment, strlen($p_comment));
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privList()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privList(&$p_list)
    {
        $v_result = 1;

        // ----- Open the zip file
        if (($this->zip_fd = @fopen($this->zipname, 'rb')) == 0) {
            throw new PclZipException('Unable to open archive \'' . $this->zipname . '\' in binary read mode', static::PCLZIP_ERR_READ_OPEN_FAIL);

            // ----- Return
        }

        // ----- Read the central directory informations
        $v_central_dir = [];
        if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1) {
            return $v_result;
        }

        // ----- Go to beginning of Central Dir
        @rewind($this->zip_fd);
        if (@fseek($this->zip_fd, $v_central_dir['offset'])) {
            throw new PclZipException('Invalid archive size', static::PCLZIP_ERR_INVALID_ARCHIVE_ZIP);

            // ----- Return
        }

        // ----- Read each entry
        for ($i = 0; $i < $v_central_dir['entries']; $i++) {
            // ----- Read the file header
            if (($v_result = $this->privReadCentralFileHeader($v_header)) != 1) {
                return $v_result;
            }
            $v_header['index'] = $i;

            // ----- Get the only interesting attributes
            $this->privConvertHeader2FileInfo($v_header, $p_list[$i]);
            unset($v_header);
        }

        // ----- Close the zip file
        $this->privCloseFd();




        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privConvertHeader2FileInfo()
    // Description :
    //   This function takes the file informations from the central directory
    //   entries and extract the interesting parameters that will be given back.
    //   The resulting file infos are set in the array $p_info
    //     $p_info['filename'] : Filename with full path. Given by user (add),
    //                           extracted in the filesystem (extract).
    //     $p_info['stored_filename'] : Stored filename in the archive.
    //     $p_info['size'] = Size of the file.
    //     $p_info['compressed_size'] = Compressed size of the file.
    //     $p_info['mtime'] = Last modification date of the file.
    //     $p_info['comment'] = Comment associated with the file.
    //     $p_info['folder'] = true/false : indicates if the entry is a folder or not.
    //     $p_info['status'] = status of the action on the file.
    //     $p_info['crc'] = CRC of the file content.
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privConvertHeader2FileInfo($p_header, &$p_info)
    {
        $v_result = 1;

        // ----- Get the interesting attributes
        $v_temp_path               = PclZipUtilPathReduction($p_header['filename']);
        $p_info['filename']        = $v_temp_path;
        $v_temp_path               = PclZipUtilPathReduction($p_header['stored_filename']);
        $p_info['stored_filename'] = $v_temp_path;
        $p_info['size']            = $p_header['size'];
        $p_info['compressed_size'] = $p_header['compressed_size'];
        $p_info['mtime']           = $p_header['mtime'];
        $p_info['comment']         = $p_header['comment'];
        $p_info['folder']          = (($p_header['external'] & 0x00000010) == 0x00000010);
        $p_info['index']           = $p_header['index'];
        $p_info['status']          = $p_header['status'];
        $p_info['crc']             = $p_header['crc'];

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privExtractByRule()
    // Description :
    //   Extract a file or directory depending of rules (by index, by name, ...)
    // Parameters :
    //   $p_file_list : An array where will be placed the properties of each
    //                  extracted file
    //   $p_path : Path to add while writing the extracted files
    //   $p_remove_path : Path to remove (from the file memorized path) while writing the
    //                    extracted files. If the path does not match the file path,
    //                    the file is extracted with its memorized path.
    //                    $p_remove_path does not apply to 'list' mode.
    //                    $p_path and $p_remove_path are commulative.
    // Return Values :
    //   1 on success,0 or less on error (see error code list)
    // --------------------------------------------------------------------------------
    protected function privExtractByRule(&$p_file_list, $p_path, $p_remove_path, $p_remove_all_path, &$p_options)
    {
        $v_result = 1;




        // ----- Check the path
        if (($p_path == "") || ((substr($p_path, 0, 1) != "/") && (substr($p_path, 0, 3) != "../") && (substr($p_path, 1, 2) != ":/"))) {
            $p_path = "./" . $p_path;
        }

        // ----- Reduce the path last (and duplicated) '/'
        if (($p_path != "./") && ($p_path != "/")) {
            // ----- Look for the path end '/'
            while (substr($p_path, -1) == "/") {
                $p_path = substr($p_path, 0, strlen($p_path) - 1);
            }
        }

        // ----- Look for path to remove format (should end by /)
        if (($p_remove_path != "") && (substr($p_remove_path, -1) != '/')) {
            $p_remove_path .= '/';
        }
        $p_remove_path_size = strlen($p_remove_path);

        // ----- Open the zip file
        if (($v_result = $this->privOpenFd('rb')) != 1) {
            return $v_result;
        }

        // ----- Read the central directory informations
        $v_central_dir = [];
        if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1) {
            // ----- Close the zip file
            $this->privCloseFd();


            return $v_result;
        }

        // ----- Start at beginning of Central Dir
        $v_pos_entry = $v_central_dir['offset'];

        // ----- Read each entry
        $j_start = 0;
        for ($i = 0, $v_nb_extracted = 0; $i < $v_central_dir['entries']; $i++) {
            // ----- Read next Central dir entry
            @rewind($this->zip_fd);
            if (@fseek($this->zip_fd, $v_pos_entry)) {
                // ----- Close the zip file
                $this->privCloseFd();


                throw new PclZipException('Invalid archive size', static::PCLZIP_ERR_INVALID_ARCHIVE_ZIP);

                // ----- Return
            }

            // ----- Read the file header
            $v_header = [];
            if (($v_result = $this->privReadCentralFileHeader($v_header)) != 1) {
                // ----- Close the zip file
                $this->privCloseFd();


                return $v_result;
            }

            // ----- Store the index
            $v_header['index'] = $i;

            // ----- Store the file position
            $v_pos_entry = ftell($this->zip_fd);

            // ----- Look for the specific extract rules
            $v_extract = false;

            // ----- Look for extract by name rule
            if ((isset($p_options[static::PCLZIP_OPT_BY_NAME])) && ($p_options[static::PCLZIP_OPT_BY_NAME] != 0)) {
                // ----- Look if the filename is in the list
                for ($j = 0; ($j < sizeof($p_options[static::PCLZIP_OPT_BY_NAME])) && (!$v_extract); $j++) {
                    // ----- Look for a directory
                    if (substr($p_options[static::PCLZIP_OPT_BY_NAME][$j], -1) == "/") {
                        // ----- Look if the directory is in the filename path
                        if ((strlen($v_header['stored_filename']) > strlen($p_options[static::PCLZIP_OPT_BY_NAME][$j])) && (substr($v_header['stored_filename'], 0, strlen($p_options[static::PCLZIP_OPT_BY_NAME][$j])) == $p_options[static::PCLZIP_OPT_BY_NAME][$j])) {
                            $v_extract = true;
                        }

                        // ----- Look for a filename
                    } elseif ($v_header['stored_filename'] == $p_options[static::PCLZIP_OPT_BY_NAME][$j]) {
                        $v_extract = true;
                    }
                }
                // ----- Look for extract by ereg rule
            // ereg() is deprecated with PHP 5.3
            /*
            elseif (   (isset($p_options[static::PCLZIP_OPT_BY_EREG]))
            && ($p_options[static::PCLZIP_OPT_BY_EREG] != "")) {
            if (ereg($p_options[static::PCLZIP_OPT_BY_EREG], $v_header['stored_filename'])) {
            $v_extract = true;
            }
            }
            */

            // ----- Look for extract by preg rule
            } elseif ((isset($p_options[static::PCLZIP_OPT_BY_PREG])) && ($p_options[static::PCLZIP_OPT_BY_PREG] != "")) {
                if (preg_match($p_options[static::PCLZIP_OPT_BY_PREG], $v_header['stored_filename'])) {
                    $v_extract = true;
                }

                // ----- Look for extract by index rule
            } elseif ((isset($p_options[static::PCLZIP_OPT_BY_INDEX])) && ($p_options[static::PCLZIP_OPT_BY_INDEX] != 0)) {
                // ----- Look if the index is in the list
                for ($j = $j_start; ($j < sizeof($p_options[static::PCLZIP_OPT_BY_INDEX])) && (!$v_extract); $j++) {
                    if (($i >= $p_options[static::PCLZIP_OPT_BY_INDEX][$j]['start']) && ($i <= $p_options[static::PCLZIP_OPT_BY_INDEX][$j]['end'])) {
                        $v_extract = true;
                    }
                    if ($i >= $p_options[static::PCLZIP_OPT_BY_INDEX][$j]['end']) {
                        $j_start = $j + 1;
                    }

                    if ($p_options[static::PCLZIP_OPT_BY_INDEX][$j]['start'] > $i) {
                        break;
                    }
                }

                // ----- Look for no rule, which means extract all the archive
            } else {
                $v_extract = true;
            }

            // ----- Check compression method
            if (($v_extract) && (($v_header['compression'] != 8) && ($v_header['compression'] != 0))) {
                $v_header['status'] = 'unsupported_compression';

                // ----- Look for static::PCLZIP_OPT_STOP_ON_ERROR
                if ((isset($p_options[static::PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[static::PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                    throw new PclZipException("Filename '" . $v_header['stored_filename'] . "' is " . "compressed by an unsupported compression " . "method (" . $v_header['compression'] . ") ", static::PCLZIP_ERR_UNSUPPORTED_COMPRESSION);
                }
            }

            // ----- Check encrypted files
            if (($v_extract) && (($v_header['flag'] & 1) == 1)) {
                $v_header['status'] = 'unsupported_encryption';

                // ----- Look for static::PCLZIP_OPT_STOP_ON_ERROR
                if ((isset($p_options[static::PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[static::PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                    throw new PclZipException("Unsupported encryption for " . " filename '" . $v_header['stored_filename'] . "'", static::PCLZIP_ERR_UNSUPPORTED_ENCRYPTION);
                }
            }

            // ----- Look for real extraction
            if (($v_extract) && ($v_header['status'] != 'ok')) {
                $v_result = $this->privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++]);
                if ($v_result != 1) {
                    $this->privCloseFd();


                    return $v_result;
                }

                $v_extract = false;
            }

            // ----- Look for real extraction
            if ($v_extract) {
                // ----- Go to the file position
                @rewind($this->zip_fd);
                if (@fseek($this->zip_fd, $v_header['offset'])) {
                    // ----- Close the zip file
                    $this->privCloseFd();



                    // ----- Error log
                    throw new PclZipException('Invalid archive size', static::PCLZIP_ERR_INVALID_ARCHIVE_ZIP);

                    // ----- Return
                }

                // ----- Look for extraction as string
                if ($p_options[static::PCLZIP_OPT_EXTRACT_AS_STRING]) {
                    $v_string = '';

                    // ----- Extracting the file
                    $v_result1 = $this->privExtractFileAsString($v_header, $v_string, $p_options);
                    if ($v_result1 < 1) {
                        $this->privCloseFd();


                        return $v_result1;
                    }

                    // ----- Get the only interesting attributes
                    if (($v_result = $this->privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted])) != 1) {
                        // ----- Close the zip file
                        $this->privCloseFd();


                        return $v_result;
                    }

                    // ----- Set the file content
                    $p_file_list[$v_nb_extracted]['content'] = $v_string;

                    // ----- Next extracted file
                    $v_nb_extracted++;

                    // ----- Look for user callback abort
                    if ($v_result1 == 2) {
                        break;
                    }

                    // ----- Look for extraction in standard output
                } elseif ((isset($p_options[static::PCLZIP_OPT_EXTRACT_IN_OUTPUT])) && ($p_options[static::PCLZIP_OPT_EXTRACT_IN_OUTPUT])) {
                    // ----- Extracting the file in standard output
                    $v_result1 = $this->privExtractFileInOutput($v_header, $p_options);
                    if ($v_result1 < 1) {
                        $this->privCloseFd();


                        return $v_result1;
                    }

                    // ----- Get the only interesting attributes
                    if (($v_result = $this->privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++])) != 1) {
                        $this->privCloseFd();


                        return $v_result;
                    }

                    // ----- Look for user callback abort
                    if ($v_result1 == 2) {
                        break;
                    }

                    // ----- Look for normal extraction
                } else {
                    // ----- Extracting the file
                    $v_result1 = $this->privExtractFile($v_header, $p_path, $p_remove_path, $p_remove_all_path, $p_options);
                    if ($v_result1 < 1) {
                        $this->privCloseFd();


                        return $v_result1;
                    }

                    // ----- Get the only interesting attributes
                    if (($v_result = $this->privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++])) != 1) {
                        // ----- Close the zip file
                        $this->privCloseFd();


                        return $v_result;
                    }

                    // ----- Look for user callback abort
                    if ($v_result1 == 2) {
                        break;
                    }
                }
            }
        }

        // ----- Close the zip file
        $this->privCloseFd();


        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privExtractFile()
    // Description :
    // Parameters :
    // Return Values :
    //
    // 1 : ... ?
    // static::PCLZIP_ERR_USER_ABORTED(2) : User ask for extraction stop in callback
    // --------------------------------------------------------------------------------
    protected function privExtractFile(&$p_entry, $p_path, $p_remove_path, $p_remove_all_path, &$p_options)
    {
        $v_result = 1;

        // ----- Read the file header
        if (($v_result = $this->privReadFileHeader($v_header)) != 1) {
            // ----- Return
            return $v_result;
        }

        // ----- Check that the file header is coherent with $p_entry info
        if ($this->privCheckFileHeaders($v_header, $p_entry) != 1) {
            // TBC
        }

        // ----- Look for all path to remove
        if ($p_remove_all_path == true) {
            // ----- Look for folder entry that not need to be extracted
            if (($p_entry['external'] & 0x00000010) == 0x00000010) {
                $p_entry['status'] = "filtered";

                return $v_result;
            }

            // ----- Get the basename of the path
            $p_entry['filename'] = basename($p_entry['filename']);

        // ----- Look for path to remove
        } elseif ($p_remove_path != "") {
            if (PclZipUtilPathInclusion($p_remove_path, $p_entry['filename']) == 2) {
                // ----- Change the file status
                $p_entry['status'] = "filtered";

                // ----- Return
                return $v_result;
            }

            $p_remove_path_size = strlen($p_remove_path);
            if (substr($p_entry['filename'], 0, $p_remove_path_size) == $p_remove_path) {
                // ----- Remove the path
                $p_entry['filename'] = substr($p_entry['filename'], $p_remove_path_size);
            }
        }

        // ----- Add the path
        if ($p_path != '') {
            $p_entry['filename'] = $p_path . "/" . $p_entry['filename'];
        }

        // ----- Check a base_dir_restriction
        if (isset($p_options[static::PCLZIP_OPT_EXTRACT_DIR_RESTRICTION])) {
            $v_inclusion = PclZipUtilPathInclusion($p_options[static::PCLZIP_OPT_EXTRACT_DIR_RESTRICTION], $p_entry['filename']);
            if ($v_inclusion == 0) {
                throw new PclZipException("Filename '" . $p_entry['filename'] . "' is " . "outside static::PCLZIP_OPT_EXTRACT_DIR_RESTRICTION", static::PCLZIP_ERR_DIRECTORY_RESTRICTION);
            }
        }

        // ----- Look for pre-extract callback
        if (isset($p_options[static::PCLZIP_CB_PRE_EXTRACT])) {
            // ----- Generate a local information
            $v_local_header = [];
            $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

            // ----- Call the callback
            // Here I do not use call_user_func() because I need to send a reference to the
            // header.
            //      eval('$v_result = '.$p_options[static::PCLZIP_CB_PRE_EXTRACT].'(static::PCLZIP_CB_PRE_EXTRACT, $v_local_header);');
            $v_result = $p_options[static::PCLZIP_CB_PRE_EXTRACT](static::PCLZIP_CB_PRE_EXTRACT, $v_local_header);
            if ($v_result == 0) {
                // ----- Change the file status
                $p_entry['status'] = "skipped";
                $v_result          = 1;
            }

            // ----- Look for abort result
            if ($v_result == 2) {
                // ----- This status is internal and will be changed in 'skipped'
                $p_entry['status'] = "aborted";
                $v_result          = static::PCLZIP_ERR_USER_ABORTED;
            }

            // ----- Update the informations
            // Only some fields can be modified
            $p_entry['filename'] = $v_local_header['filename'];
        }

        // ----- Look if extraction should be done
        if ($p_entry['status'] == 'ok') {
            // ----- Look for specific actions while the file exist
            if (file_exists($p_entry['filename'])) {
                // ----- Look if file is a directory
                if (is_dir($p_entry['filename'])) {
                    // ----- Change the file status
                    $p_entry['status'] = "already_a_directory";

                    // ----- Look for static::PCLZIP_OPT_STOP_ON_ERROR
                    // For historical reason first PclZip implementation does not stop
                    // when this kind of error occurs.
                    if ((isset($p_options[static::PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[static::PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                        throw new PclZipException("Filename '" . $p_entry['filename'] . "' is " . "already used by an existing directory", static::PCLZIP_ERR_ALREADY_A_DIRECTORY);
                    }

                    // ----- Look if file is write protected
                } elseif (!is_writeable($p_entry['filename'])) {
                    // ----- Change the file status
                    $p_entry['status'] = "write_protected";

                    // ----- Look for static::PCLZIP_OPT_STOP_ON_ERROR
                    // For historical reason first PclZip implementation does not stop
                    // when this kind of error occurs.
                    if ((isset($p_options[static::PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[static::PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                        throw new PclZipException("Filename '" . $p_entry['filename'] . "' exists " . "and is write protected", static::PCLZIP_ERR_WRITE_OPEN_FAIL);
                    }

                    // ----- Look if the extracted file is older
                } elseif (filemtime($p_entry['filename']) > $p_entry['mtime']) {
                    // ----- Change the file status
                    if ((isset($p_options[static::PCLZIP_OPT_REPLACE_NEWER])) && ($p_options[static::PCLZIP_OPT_REPLACE_NEWER] === true)) {
                    } else {
                        $p_entry['status'] = "newer_exist";

                        // ----- Look for static::PCLZIP_OPT_STOP_ON_ERROR
                        // For historical reason first PclZip implementation does not stop
                        // when this kind of error occurs.
                        if ((isset($p_options[static::PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[static::PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                            throw new PclZipException("Newer version of '" . $p_entry['filename'] . "' exists " . "and option static::PCLZIP_OPT_REPLACE_NEWER is not selected", static::PCLZIP_ERR_WRITE_OPEN_FAIL);
                        }
                    }
                } else {
                }

                // ----- Check the directory availability and create it if necessary
            } else {
                if ((($p_entry['external'] & 0x00000010) == 0x00000010) || (substr($p_entry['filename'], -1) == '/')) {
                    $v_dir_to_check = $p_entry['filename'];
                } elseif (!strstr($p_entry['filename'], "/")) {
                    $v_dir_to_check = "";
                } else {
                    $v_dir_to_check = dirname($p_entry['filename']);
                }

                if (($v_result = $this->privDirCheck($v_dir_to_check, (($p_entry['external'] & 0x00000010) == 0x00000010))) != 1) {
                    // ----- Change the file status
                    $p_entry['status'] = "path_creation_fail";

                    // ----- Return
                    //return $v_result;
                    $v_result = 1;
                }
            }
        }

        // ----- Look if extraction should be done
        if ($p_entry['status'] == 'ok') {
            // ----- Do the extraction (if not a folder)
            if (!(($p_entry['external'] & 0x00000010) == 0x00000010)) {
                // ----- Look for not compressed file
                if ($p_entry['compression'] == 0) {
                    // ----- Opening destination file
                    if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0) {
                        // ----- Change the file status
                        $p_entry['status'] = "write_error";

                        // ----- Return
                        return $v_result;
                    }

                    // ----- Read the file by static::PCLZIP_READ_BLOCK_SIZE octets blocks
                    $v_size = $p_entry['compressed_size'];
                    while ($v_size != 0) {
                        $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
                        $v_buffer    = @fread($this->zip_fd, $v_read_size);
                        /* Try to speed up the code
                        $v_binary_data = pack('a'.$v_read_size, $v_buffer);
                        @fwrite($v_dest_file, $v_binary_data, $v_read_size);
                        */
                        @fwrite($v_dest_file, $v_buffer, $v_read_size);
                        $v_size -= $v_read_size;
                    }

                    // ----- Closing the destination file
                    fclose($v_dest_file);

                    // ----- Change the file mtime
                    touch($p_entry['filename'], $p_entry['mtime']);
                } else {
                    // ----- TBC
                    // Need to be finished
                    if (($p_entry['flag'] & 1) == 1) {
                        throw new PclZipException('File \'' . $p_entry['filename'] . '\' is encrypted. Encrypted files are not supported.', static::PCLZIP_ERR_UNSUPPORTED_ENCRYPTION);
                    }

                    // ----- Look for using temporary file to unzip
                    if ((!isset($p_options[static::PCLZIP_OPT_TEMP_FILE_OFF])) && (isset($p_options[static::PCLZIP_OPT_TEMP_FILE_ON]) || (isset($p_options[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD]) && ($p_options[static::PCLZIP_OPT_TEMP_FILE_THRESHOLD] <= $p_entry['size'])))) {
                        $v_result = $this->privExtractFileUsingTempFile($p_entry, $p_options);
                        if ($v_result < static::PCLZIP_ERR_NO_ERROR) {
                            return $v_result;
                        }

                        // ----- Look for extract in memory
                    } else {
                        // ----- Read the compressed file in a buffer (one shot)
                        $v_buffer = @fread($this->zip_fd, $p_entry['compressed_size']);

                        // ----- Decompress the file
                        $v_file_content = @gzinflate($v_buffer);
                        unset($v_buffer);
                        if ($v_file_content === false) {
                            // ----- Change the file status
                            // TBC
                            $p_entry['status'] = "error";

                            return $v_result;
                        }

                        // ----- Opening destination file
                        if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0) {
                            // ----- Change the file status
                            $p_entry['status'] = "write_error";

                            return $v_result;
                        }

                        // ----- Write the uncompressed data
                        @fwrite($v_dest_file, $v_file_content, $p_entry['size']);
                        unset($v_file_content);

                        // ----- Closing the destination file
                        @fclose($v_dest_file);
                    }

                    // ----- Change the file mtime
                    @touch($p_entry['filename'], $p_entry['mtime']);
                }

                // ----- Look for chmod option
                if (isset($p_options[static::PCLZIP_OPT_SET_CHMOD])) {
                    // ----- Change the mode of the file
                    @chmod($p_entry['filename'], $p_options[static::PCLZIP_OPT_SET_CHMOD]);
                }
            }
        }

        // ----- Change abort status
        if ($p_entry['status'] == "aborted") {
            $p_entry['status'] = "skipped";

        // ----- Look for post-extract callback
        } elseif (isset($p_options[static::PCLZIP_CB_POST_EXTRACT])) {
            // ----- Generate a local information
            $v_local_header = [];
            $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

            // ----- Call the callback
            // Here I do not use call_user_func() because I need to send a reference to the
            // header.
            //      eval('$v_result = '.$p_options[static::PCLZIP_CB_POST_EXTRACT].'(static::PCLZIP_CB_POST_EXTRACT, $v_local_header);');
            $v_result = $p_options[static::PCLZIP_CB_POST_EXTRACT](static::PCLZIP_CB_POST_EXTRACT, $v_local_header);

            // ----- Look for abort result
            if ($v_result == 2) {
                $v_result = static::PCLZIP_ERR_USER_ABORTED;
            }
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privExtractFileUsingTempFile()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privExtractFileUsingTempFile(&$p_entry, &$p_options)
    {
        $v_result = 1;

        // ----- Creates a temporary file
        $v_gzip_temp_name = static::PCLZIP_TEMPORARY_DIR . uniqid('pclzip-') . '.gz';
        if (($v_dest_file = @fopen($v_gzip_temp_name, "wb")) == 0) {
            fclose($v_file);
            throw new PclZipException('Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary write mode', static::PCLZIP_ERR_WRITE_OPEN_FAIL);
        }

        // ----- Write gz file format header
        $v_binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($p_entry['compression']), Chr(0x00), time(), Chr(0x00), Chr(3));
        @fwrite($v_dest_file, $v_binary_data, 10);

        // ----- Read the file by static::PCLZIP_READ_BLOCK_SIZE octets blocks
        $v_size = $p_entry['compressed_size'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = @fread($this->zip_fd, $v_read_size);
            //$v_binary_data = pack('a'.$v_read_size, $v_buffer);
            @fwrite($v_dest_file, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Write gz file format footer
        $v_binary_data = pack('VV', $p_entry['crc'], $p_entry['size']);
        @fwrite($v_dest_file, $v_binary_data, 8);

        // ----- Close the temporary file
        @fclose($v_dest_file);

        // ----- Opening destination file
        if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0) {
            $p_entry['status'] = "write_error";

            return $v_result;
        }

        // ----- Open the temporary gz file
        if (($v_src_file = @gzopen($v_gzip_temp_name, 'rb')) == 0) {
            @fclose($v_dest_file);
            $p_entry['status'] = "read_error";
            throw new PclZipException('Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary read mode', static::PCLZIP_ERR_READ_OPEN_FAIL);
        }

        // ----- Read the file by static::PCLZIP_READ_BLOCK_SIZE octets blocks
        $v_size = $p_entry['size'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = @gzread($v_src_file, $v_read_size);
            //$v_binary_data = pack('a'.$v_read_size, $v_buffer);
            @fwrite($v_dest_file, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }
        @fclose($v_dest_file);
        @gzclose($v_src_file);

        // ----- Delete the temporary file
        @unlink($v_gzip_temp_name);

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privExtractFileInOutput()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privExtractFileInOutput(&$p_entry, &$p_options)
    {
        $v_result = 1;

        // ----- Read the file header
        if (($v_result = $this->privReadFileHeader($v_header)) != 1) {
            return $v_result;
        }

        // ----- Check that the file header is coherent with $p_entry info
        if ($this->privCheckFileHeaders($v_header, $p_entry) != 1) {
            // TBC
        }

        // ----- Look for pre-extract callback
        if (isset($p_options[static::PCLZIP_CB_PRE_EXTRACT])) {
            // ----- Generate a local information
            $v_local_header = [];
            $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

            // ----- Call the callback
            // Here I do not use call_user_func() because I need to send a reference to the
            // header.
            //      eval('$v_result = '.$p_options[static::PCLZIP_CB_PRE_EXTRACT].'(static::PCLZIP_CB_PRE_EXTRACT, $v_local_header);');
            $v_result = $p_options[static::PCLZIP_CB_PRE_EXTRACT](static::PCLZIP_CB_PRE_EXTRACT, $v_local_header);
            if ($v_result == 0) {
                // ----- Change the file status
                $p_entry['status'] = "skipped";
                $v_result          = 1;
            }

            // ----- Look for abort result
            if ($v_result == 2) {
                // ----- This status is internal and will be changed in 'skipped'
                $p_entry['status'] = "aborted";
                $v_result          = static::PCLZIP_ERR_USER_ABORTED;
            }

            // ----- Update the informations
            // Only some fields can be modified
            $p_entry['filename'] = $v_local_header['filename'];
        }

        // ----- Trace

        // ----- Look if extraction should be done
        if ($p_entry['status'] == 'ok') {
            // ----- Do the extraction (if not a folder)
            if (!(($p_entry['external'] & 0x00000010) == 0x00000010)) {
                // ----- Look for not compressed file
                if ($p_entry['compressed_size'] == $p_entry['size']) {
                    // ----- Read the file in a buffer (one shot)
                    $v_buffer = @fread($this->zip_fd, $p_entry['compressed_size']);

                    // ----- Send the file to the output
                    echo $v_buffer;
                    unset($v_buffer);
                } else {
                    // ----- Read the compressed file in a buffer (one shot)
                    $v_buffer = @fread($this->zip_fd, $p_entry['compressed_size']);

                    // ----- Decompress the file
                    $v_file_content = gzinflate($v_buffer);
                    unset($v_buffer);

                    // ----- Send the file to the output
                    echo $v_file_content;
                    unset($v_file_content);
                }
            }
        }

        // ----- Change abort status
        if ($p_entry['status'] == "aborted") {
            $p_entry['status'] = "skipped";

        // ----- Look for post-extract callback
        } elseif (isset($p_options[static::PCLZIP_CB_POST_EXTRACT])) {
            // ----- Generate a local information
            $v_local_header = [];
            $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

            // ----- Call the callback
            // Here I do not use call_user_func() because I need to send a reference to the
            // header.
            //      eval('$v_result = '.$p_options[static::PCLZIP_CB_POST_EXTRACT].'(static::PCLZIP_CB_POST_EXTRACT, $v_local_header);');
            $v_result = $p_options[static::PCLZIP_CB_POST_EXTRACT](static::PCLZIP_CB_POST_EXTRACT, $v_local_header);

            // ----- Look for abort result
            if ($v_result == 2) {
                $v_result = static::PCLZIP_ERR_USER_ABORTED;
            }
        }

        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privExtractFileAsString()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privExtractFileAsString(&$p_entry, &$p_string, &$p_options)
    {
        $v_result = 1;

        // ----- Read the file header
        $v_header = [];
        if (($v_result = $this->privReadFileHeader($v_header)) != 1) {
            // ----- Return
            return $v_result;
        }

        // ----- Check that the file header is coherent with $p_entry info
        if ($this->privCheckFileHeaders($v_header, $p_entry) != 1) {
            // TBC
        }

        // ----- Look for pre-extract callback
        if (isset($p_options[static::PCLZIP_CB_PRE_EXTRACT])) {
            // ----- Generate a local information
            $v_local_header = [];
            $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

            // ----- Call the callback
            // Here I do not use call_user_func() because I need to send a reference to the
            // header.
            //      eval('$v_result = '.$p_options[static::PCLZIP_CB_PRE_EXTRACT].'(static::PCLZIP_CB_PRE_EXTRACT, $v_local_header);');
            $v_result = $p_options[static::PCLZIP_CB_PRE_EXTRACT](static::PCLZIP_CB_PRE_EXTRACT, $v_local_header);
            if ($v_result == 0) {
                // ----- Change the file status
                $p_entry['status'] = "skipped";
                $v_result          = 1;
            }

            // ----- Look for abort result
            if ($v_result == 2) {
                // ----- This status is internal and will be changed in 'skipped'
                $p_entry['status'] = "aborted";
                $v_result          = static::PCLZIP_ERR_USER_ABORTED;
            }

            // ----- Update the informations
            // Only some fields can be modified
            $p_entry['filename'] = $v_local_header['filename'];
        }

        // ----- Look if extraction should be done
        if ($p_entry['status'] == 'ok') {
            // ----- Do the extraction (if not a folder)
            if (!(($p_entry['external'] & 0x00000010) == 0x00000010)) {
                // ----- Look for not compressed file
                //      if ($p_entry['compressed_size'] == $p_entry['size'])
                if ($p_entry['compression'] == 0) {
                    // ----- Reading the file
                    $p_string = @fread($this->zip_fd, $p_entry['compressed_size']);
                } else {
                    // ----- Reading the file
                    $v_data = @fread($this->zip_fd, $p_entry['compressed_size']);

                    // ----- Decompress the file
                    if (($p_string = @gzinflate($v_data)) === false) {
                        // TBC
                    }
                }

                // ----- Trace
            } else {
                // TBC : error : can not extract a folder in a string
            }
        }

        // ----- Change abort status
        if ($p_entry['status'] == "aborted") {
            $p_entry['status'] = "skipped";

        // ----- Look for post-extract callback
        } elseif (isset($p_options[static::PCLZIP_CB_POST_EXTRACT])) {
            // ----- Generate a local information
            $v_local_header = [];
            $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

            // ----- Swap the content to header
            $v_local_header['content'] = $p_string;
            $p_string                  = '';

            // ----- Call the callback
            // Here I do not use call_user_func() because I need to send a reference to the
            // header.
            //      eval('$v_result = '.$p_options[static::PCLZIP_CB_POST_EXTRACT].'(static::PCLZIP_CB_POST_EXTRACT, $v_local_header);');
            $v_result = $p_options[static::PCLZIP_CB_POST_EXTRACT](static::PCLZIP_CB_POST_EXTRACT, $v_local_header);

            // ----- Swap back the content to header
            $p_string = $v_local_header['content'];
            unset($v_local_header['content']);

            // ----- Look for abort result
            if ($v_result == 2) {
                $v_result = static::PCLZIP_ERR_USER_ABORTED;
            }
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privReadFileHeader()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privReadFileHeader(&$p_header)
    {
        $v_result = 1;

        // ----- Read the 4 bytes signature
        $v_binary_data = @fread($this->zip_fd, 4);
        $v_data        = unpack('Vid', $v_binary_data);

        // ----- Check signature
        if ($v_data['id'] != 0x04034b50) {
            throw new PclZipException('Invalid archive structure', static::PCLZIP_ERR_BAD_FORMAT);

            // ----- Return
        }

        // ----- Read the first 42 bytes of the header
        $v_binary_data = fread($this->zip_fd, 26);

        // ----- Look for invalid block size
        if (strlen($v_binary_data) != 26) {
            $p_header['filename'] = "";
            $p_header['status']   = "invalid_header";

            throw new PclZipException("Invalid block size : " . strlen($v_binary_data), static::PCLZIP_ERR_BAD_FORMAT);

            // ----- Return
        }

        // ----- Extract the values
        $v_data = unpack('vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $v_binary_data);

        // ----- Get filename
        $p_header['filename'] = fread($this->zip_fd, $v_data['filename_len']);

        // ----- Get extra_fields
        if ($v_data['extra_len'] != 0) {
            $p_header['extra'] = fread($this->zip_fd, $v_data['extra_len']);
        } else {
            $p_header['extra'] = '';
        }

        // ----- Extract properties
        $p_header['version_extracted'] = $v_data['version'];
        $p_header['compression']       = $v_data['compression'];
        $p_header['size']              = $v_data['size'];
        $p_header['compressed_size']   = $v_data['compressed_size'];
        $p_header['crc']               = $v_data['crc'];
        $p_header['flag']              = $v_data['flag'];
        $p_header['filename_len']      = $v_data['filename_len'];

        // ----- Recuperate date in UNIX format
        $p_header['mdate'] = $v_data['mdate'];
        $p_header['mtime'] = $v_data['mtime'];
        if ($p_header['mdate'] && $p_header['mtime']) {
            // ----- Extract time
            $v_hour    = ($p_header['mtime'] & 0xF800) >> 11;
            $v_minute  = ($p_header['mtime'] & 0x07E0) >> 5;
            $v_seconde = ($p_header['mtime'] & 0x001F) * 2;

            // ----- Extract date
            $v_year  = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
            $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
            $v_day   = $p_header['mdate'] & 0x001F;

            // ----- Get UNIX date format
            $p_header['mtime'] = @mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);
        } else {
            $p_header['mtime'] = time();
        }

        // TBC
        //for (reset($v_data); $key = key($v_data); next($v_data)) {
        //}

        // ----- Set the stored filename
        $p_header['stored_filename'] = $p_header['filename'];

        // ----- Set the status field
        $p_header['status'] = "ok";

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privReadCentralFileHeader()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privReadCentralFileHeader(&$p_header)
    {
        $v_result = 1;

        // ----- Read the 4 bytes signature
        $v_binary_data = @fread($this->zip_fd, 4);
        $v_data        = unpack('Vid', $v_binary_data);

        // ----- Check signature
        if ($v_data['id'] != 0x02014b50) {
            throw new PclZipException('Invalid archive structure', static::PCLZIP_ERR_BAD_FORMAT);

            // ----- Return
        }

        // ----- Read the first 42 bytes of the header
        $v_binary_data = fread($this->zip_fd, 42);

        // ----- Look for invalid block size
        if (strlen($v_binary_data) != 42) {
            $p_header['filename'] = "";
            $p_header['status']   = "invalid_header";

            throw new PclZipException("Invalid block size : " . strlen($v_binary_data), static::PCLZIP_ERR_BAD_FORMAT);

            // ----- Return
        }

        // ----- Extract the values
        $p_header = unpack('vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $v_binary_data);

        // ----- Get filename
        if ($p_header['filename_len'] != 0) {
            $p_header['filename'] = fread($this->zip_fd, $p_header['filename_len']);
        } else {
            $p_header['filename'] = '';
        }

        // ----- Get extra
        if ($p_header['extra_len'] != 0) {
            $p_header['extra'] = fread($this->zip_fd, $p_header['extra_len']);
        } else {
            $p_header['extra'] = '';
        }

        // ----- Get comment
        if ($p_header['comment_len'] != 0) {
            $p_header['comment'] = fread($this->zip_fd, $p_header['comment_len']);
        } else {
            $p_header['comment'] = '';
        }

        // ----- Extract properties

        // ----- Recuperate date in UNIX format
        //if ($p_header['mdate'] && $p_header['mtime'])
        // TBC : bug : this was ignoring time with 0/0/0
        if (1) {
            // ----- Extract time
            $v_hour    = ($p_header['mtime'] & 0xF800) >> 11;
            $v_minute  = ($p_header['mtime'] & 0x07E0) >> 5;
            $v_seconde = ($p_header['mtime'] & 0x001F) * 2;

            // ----- Extract date
            $v_year  = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
            $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
            $v_day   = $p_header['mdate'] & 0x001F;

            // ----- Get UNIX date format
            $p_header['mtime'] = @mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);
        } else {
            $p_header['mtime'] = time();
        }

        // ----- Set the stored filename
        $p_header['stored_filename'] = $p_header['filename'];

        // ----- Set default status to ok
        $p_header['status'] = 'ok';

        // ----- Look if it is a directory
        if (substr($p_header['filename'], -1) == '/') {
            //$p_header['external'] = 0x41FF0010;
            $p_header['external'] = 0x00000010;
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privCheckFileHeaders()
    // Description :
    // Parameters :
    // Return Values :
    //   1 on success,
    //   0 on error;
    // --------------------------------------------------------------------------------
    protected function privCheckFileHeaders(&$p_local_header, &$p_central_header)
    {
        $v_result = 1;

        // ----- Check the static values
        // TBC
        if ($p_local_header['filename'] != $p_central_header['filename']) {
        }
        if ($p_local_header['version_extracted'] != $p_central_header['version_extracted']) {
        }
        if ($p_local_header['flag'] != $p_central_header['flag']) {
        }
        if ($p_local_header['compression'] != $p_central_header['compression']) {
        }
        if ($p_local_header['mtime'] != $p_central_header['mtime']) {
        }
        if ($p_local_header['filename_len'] != $p_central_header['filename_len']) {
        }

        // ----- Look for flag bit 3
        if (($p_local_header['flag'] & 8) == 8) {
            $p_local_header['size']            = $p_central_header['size'];
            $p_local_header['compressed_size'] = $p_central_header['compressed_size'];
            $p_local_header['crc']             = $p_central_header['crc'];
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privReadEndCentralDir()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privReadEndCentralDir(&$p_central_dir)
    {
        $v_result = 1;

        // ----- Go to the end of the zip file
        $v_size = filesize($this->zipname);
        @fseek($this->zip_fd, $v_size);
        if (@ftell($this->zip_fd) != $v_size) {
            throw new PclZipException('Unable to go to the end of the archive \'' . $this->zipname . '\'', static::PCLZIP_ERR_BAD_FORMAT);

            // ----- Return
        }

        // ----- First try : look if this is an archive with no commentaries (most of the time)
        // in this case the end of central dir is at 22 bytes of the file end
        $v_found = 0;
        if ($v_size > 26) {
            @fseek($this->zip_fd, $v_size - 22);
            if (($v_pos = @ftell($this->zip_fd)) != ($v_size - 22)) {
                throw new PclZipException('Unable to seek back to the middle of the archive \'' . $this->zipname . '\'', static::PCLZIP_ERR_BAD_FORMAT);

                // ----- Return
            }

            // ----- Read for bytes
            $v_binary_data = @fread($this->zip_fd, 4);
            $v_data        = @unpack('Vid', $v_binary_data);

            // ----- Check signature
            if ($v_data['id'] == 0x06054b50) {
                $v_found = 1;
            }

            $v_pos = ftell($this->zip_fd);
        }

        // ----- Go back to the maximum possible size of the Central Dir End Record
        if (!$v_found) {
            $v_maximum_size = 65557; // 0xFFFF + 22;
            if ($v_maximum_size > $v_size) {
                $v_maximum_size = $v_size;
            }
            @fseek($this->zip_fd, $v_size - $v_maximum_size);
            if (@ftell($this->zip_fd) != ($v_size - $v_maximum_size)) {
                throw new PclZipException('Unable to seek back to the middle of the archive \'' . $this->zipname . '\'', static::PCLZIP_ERR_BAD_FORMAT);

                // ----- Return
            }

            // ----- Read byte per byte in order to find the signature
            $v_pos   = ftell($this->zip_fd);
            $v_bytes = 0x00000000;
            while ($v_pos < $v_size) {
                // ----- Read a byte
                $v_byte = @fread($this->zip_fd, 1);

                // -----  Add the byte
                //$v_bytes = ($v_bytes << 8) | Ord($v_byte);
                // Note we mask the old value down such that once shifted we can never end up with more than a 32bit number
                // Otherwise on systems where we have 64bit integers the check below for the magic number will fail.
                $v_bytes = (($v_bytes & 0xFFFFFF) << 8) | Ord($v_byte);

                // ----- Compare the bytes
                if ($v_bytes == 0x504b0506) {
                    $v_pos++;
                    break;
                }

                $v_pos++;
            }

            // ----- Look if not found end of central dir
            if ($v_pos == $v_size) {
                throw new PclZipException("Unable to find End of Central Dir Record signature", static::PCLZIP_ERR_BAD_FORMAT);

                // ----- Return
            }
        }

        // ----- Read the first 18 bytes of the header
        $v_binary_data = fread($this->zip_fd, 18);

        // ----- Look for invalid block size
        if (strlen($v_binary_data) != 18) {
            throw new PclZipException("Invalid End of Central Dir Record size : " . strlen($v_binary_data), static::PCLZIP_ERR_BAD_FORMAT);

            // ----- Return
        }

        // ----- Extract the values
        $v_data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', $v_binary_data);

        // ----- Check the global size
        if (($v_pos + $v_data['comment_size'] + 18) != $v_size) {
            // ----- Removed in release 2.2 see readme file
            // The check of the file size is a little too strict.
            // Some bugs where found when a zip is encrypted/decrypted with 'crypt'.
            // While decrypted, zip has training 0 bytes
            if (0) {
                throw new PclZipException('The central dir is not at the end of the archive.' . ' Some trailing bytes exists after the archive.', static::PCLZIP_ERR_BAD_FORMAT);

                // ----- Return
            }
        }

        // ----- Get comment
        if ($v_data['comment_size'] != 0) {
            $p_central_dir['comment'] = fread($this->zip_fd, $v_data['comment_size']);
        } else {
            $p_central_dir['comment'] = '';
        }

        $p_central_dir['entries']      = $v_data['entries'];
        $p_central_dir['disk_entries'] = $v_data['disk_entries'];
        $p_central_dir['offset']       = $v_data['offset'];
        $p_central_dir['size']         = $v_data['size'];
        $p_central_dir['disk']         = $v_data['disk'];
        $p_central_dir['disk_start']   = $v_data['disk_start'];

        // TBC
        //for (reset($p_central_dir); $key = key($p_central_dir); next($p_central_dir)) {
        //}

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privDeleteByRule()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privDeleteByRule(&$p_result_list, &$p_options)
    {
        $v_result      = 1;
        $v_list_detail = [];

        // ----- Open the zip file
        if (($v_result = $this->privOpenFd('rb')) != 1) {
            // ----- Return
            return $v_result;
        }

        // ----- Read the central directory informations
        $v_central_dir = [];
        if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1) {
            $this->privCloseFd();

            return $v_result;
        }

        // ----- Go to beginning of File
        @rewind($this->zip_fd);

        // ----- Scan all the files
        // ----- Start at beginning of Central Dir
        $v_pos_entry = $v_central_dir['offset'];
        @rewind($this->zip_fd);
        if (@fseek($this->zip_fd, $v_pos_entry)) {
            // ----- Close the zip file
            $this->privCloseFd();

            throw new PclZipException('Invalid archive size', static::PCLZIP_ERR_INVALID_ARCHIVE_ZIP);

            // ----- Return
        }

        // ----- Read each entry
        $v_header_list = [];
        $j_start       = 0;
        for ($i = 0, $v_nb_extracted = 0; $i < $v_central_dir['entries']; $i++) {
            // ----- Read the file header
            $v_header_list[$v_nb_extracted] = [];
            if (($v_result = $this->privReadCentralFileHeader($v_header_list[$v_nb_extracted])) != 1) {
                // ----- Close the zip file
                $this->privCloseFd();

                return $v_result;
            }

            // ----- Store the index
            $v_header_list[$v_nb_extracted]['index'] = $i;

            // ----- Look for the specific extract rules
            $v_found = false;

            // ----- Look for extract by name rule
            if ((isset($p_options[static::PCLZIP_OPT_BY_NAME])) && ($p_options[static::PCLZIP_OPT_BY_NAME] != 0)) {
                // ----- Look if the filename is in the list
                for ($j = 0; ($j < sizeof($p_options[static::PCLZIP_OPT_BY_NAME])) && (!$v_found); $j++) {
                    // ----- Look for a directory
                    if (substr($p_options[static::PCLZIP_OPT_BY_NAME][$j], -1) == "/") {
                        // ----- Look if the directory is in the filename path
                        if ((strlen($v_header_list[$v_nb_extracted]['stored_filename']) > strlen($p_options[static::PCLZIP_OPT_BY_NAME][$j])) && (substr($v_header_list[$v_nb_extracted]['stored_filename'], 0, strlen($p_options[static::PCLZIP_OPT_BY_NAME][$j])) == $p_options[static::PCLZIP_OPT_BY_NAME][$j])) {
                            $v_found = true;
                        } elseif ((($v_header_list[$v_nb_extracted]['external'] & 0x00000010) == 0x00000010) /* Indicates a folder */ && ($v_header_list[$v_nb_extracted]['stored_filename'] . '/' == $p_options[static::PCLZIP_OPT_BY_NAME][$j])) {
                            $v_found = true;
                        }

                        // ----- Look for a filename
                    } elseif ($v_header_list[$v_nb_extracted]['stored_filename'] == $p_options[static::PCLZIP_OPT_BY_NAME][$j]) {
                        $v_found = true;
                    }
                }

                // ----- Look for extract by ereg rule
            // ereg() is deprecated with PHP 5.3
            /*
            elseif (   (isset($p_options[static::PCLZIP_OPT_BY_EREG]))
            && ($p_options[static::PCLZIP_OPT_BY_EREG] != "")) {
            if (ereg($p_options[static::PCLZIP_OPT_BY_EREG], $v_header_list[$v_nb_extracted]['stored_filename'])) {
            $v_found = true;
            }
            }
            */

            // ----- Look for extract by preg rule
            } elseif ((isset($p_options[static::PCLZIP_OPT_BY_PREG])) && ($p_options[static::PCLZIP_OPT_BY_PREG] != "")) {
                if (preg_match($p_options[static::PCLZIP_OPT_BY_PREG], $v_header_list[$v_nb_extracted]['stored_filename'])) {
                    $v_found = true;
                }

                // ----- Look for extract by index rule
            } elseif ((isset($p_options[static::PCLZIP_OPT_BY_INDEX])) && ($p_options[static::PCLZIP_OPT_BY_INDEX] != 0)) {
                // ----- Look if the index is in the list
                for ($j = $j_start; ($j < sizeof($p_options[static::PCLZIP_OPT_BY_INDEX])) && (!$v_found); $j++) {
                    if (($i >= $p_options[static::PCLZIP_OPT_BY_INDEX][$j]['start']) && ($i <= $p_options[static::PCLZIP_OPT_BY_INDEX][$j]['end'])) {
                        $v_found = true;
                    }
                    if ($i >= $p_options[static::PCLZIP_OPT_BY_INDEX][$j]['end']) {
                        $j_start = $j + 1;
                    }

                    if ($p_options[static::PCLZIP_OPT_BY_INDEX][$j]['start'] > $i) {
                        break;
                    }
                }
            } else {
                $v_found = true;
            }

            // ----- Look for deletion
            if ($v_found) {
                unset($v_header_list[$v_nb_extracted]);
            } else {
                $v_nb_extracted++;
            }
        }

        // ----- Look if something need to be deleted
        if ($v_nb_extracted > 0) {
            // ----- Creates a temporay file
            $v_zip_temp_name = static::PCLZIP_TEMPORARY_DIR . uniqid('pclzip-') . '.tmp';

            // ----- Creates a temporary zip archive
            $v_temp_zip = new PclZip($v_zip_temp_name);

            // ----- Open the temporary zip file in write mode
            if (($v_result = $v_temp_zip->privOpenFd('wb')) != 1) {
                $this->privCloseFd();

                // ----- Return
                return $v_result;
            }

            // ----- Look which file need to be kept
            for ($i = 0; $i < sizeof($v_header_list); $i++) {
                // ----- Calculate the position of the header
                @rewind($this->zip_fd);
                if (@fseek($this->zip_fd, $v_header_list[$i]['offset'])) {
                    // ----- Close the zip file
                    $this->privCloseFd();
                    $v_temp_zip->privCloseFd();
                    @unlink($v_zip_temp_name);

                    // ----- Error log
                    throw new PclZipException('Invalid archive size', static::PCLZIP_ERR_INVALID_ARCHIVE_ZIP);

                    // ----- Return
                }

                // ----- Read the file header
                $v_local_header = [];
                if (($v_result = $this->privReadFileHeader($v_local_header)) != 1) {
                    // ----- Close the zip file
                    $this->privCloseFd();
                    $v_temp_zip->privCloseFd();
                    @unlink($v_zip_temp_name);

                    // ----- Return
                    return $v_result;
                }

                // ----- Check that local file header is same as central file header
                if ($this->privCheckFileHeaders($v_local_header, $v_header_list[$i]) != 1) {
                    // TBC
                }
                unset($v_local_header);

                // ----- Write the file header
                if (($v_result = $v_temp_zip->privWriteFileHeader($v_header_list[$i])) != 1) {
                    // ----- Close the zip file
                    $this->privCloseFd();
                    $v_temp_zip->privCloseFd();
                    @unlink($v_zip_temp_name);

                    // ----- Return
                    return $v_result;
                }

                // ----- Read/write the data block
                if (($v_result = PclZipUtilCopyBlock($this->zip_fd, $v_temp_zip->zip_fd, $v_header_list[$i]['compressed_size'])) != 1) {
                    // ----- Close the zip file
                    $this->privCloseFd();
                    $v_temp_zip->privCloseFd();
                    @unlink($v_zip_temp_name);

                    // ----- Return
                    return $v_result;
                }
            }

            // ----- Store the offset of the central dir
            $v_offset = @ftell($v_temp_zip->zip_fd);

            // ----- Re-Create the Central Dir files header
            for ($i = 0; $i < sizeof($v_header_list); $i++) {
                // ----- Create the file header
                if (($v_result = $v_temp_zip->privWriteCentralFileHeader($v_header_list[$i])) != 1) {
                    $v_temp_zip->privCloseFd();
                    $this->privCloseFd();
                    @unlink($v_zip_temp_name);

                    // ----- Return
                    return $v_result;
                }

                // ----- Transform the header to a 'usable' info
                $v_temp_zip->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
            }

            // ----- Zip file comment
            $v_comment = '';
            if (isset($p_options[static::PCLZIP_OPT_COMMENT])) {
                $v_comment = $p_options[static::PCLZIP_OPT_COMMENT];
            }

            // ----- Calculate the size of the central header
            $v_size = @ftell($v_temp_zip->zip_fd) - $v_offset;

            // ----- Create the central dir footer
            if (($v_result = $v_temp_zip->privWriteCentralHeader(sizeof($v_header_list), $v_size, $v_offset, $v_comment)) != 1) {
                // ----- Reset the file list
                unset($v_header_list);
                $v_temp_zip->privCloseFd();
                $this->privCloseFd();
                @unlink($v_zip_temp_name);

                // ----- Return
                return $v_result;
            }

            // ----- Close
            $v_temp_zip->privCloseFd();
            $this->privCloseFd();

            // ----- Delete the zip file
            // TBC : I should test the result ...
            @unlink($this->zipname);

            // ----- Rename the temporary file
            // TBC : I should test the result ...
            //@rename($v_zip_temp_name, $this->zipname);
            PclZipUtilRename($v_zip_temp_name, $this->zipname);

            // ----- Destroy the temporary archive
            unset($v_temp_zip);

        // ----- Remove every files : reset the file
        } elseif ($v_central_dir['entries'] != 0) {
            $this->privCloseFd();

            if (($v_result = $this->privOpenFd('wb')) != 1) {
                return $v_result;
            }

            if (($v_result = $this->privWriteCentralHeader(0, 0, 0, '')) != 1) {
                return $v_result;
            }

            $this->privCloseFd();
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privDirCheck()
    // Description :
    //   Check if a directory exists, if not it creates it and all the parents directory
    //   which may be useful.
    // Parameters :
    //   $p_dir : Directory path to check.
    // Return Values :
    //    1 : OK
    //   -1 : Unable to create directory
    // --------------------------------------------------------------------------------
    protected function privDirCheck($p_dir, $p_is_dir = false)
    {
        $v_result = 1;

        // ----- Remove the final '/'
        if (($p_is_dir) && (substr($p_dir, -1) == '/')) {
            $p_dir = substr($p_dir, 0, strlen($p_dir) - 1);
        }

        // ----- Check the directory availability
        if ((is_dir($p_dir)) || ($p_dir == "")) {
            return 1;
        }

        // ----- Extract parent directory
        $p_parent_dir = dirname($p_dir);

        // ----- Just a check
        if ($p_parent_dir != $p_dir) {
            // ----- Look for parent directory
            if ($p_parent_dir != "") {
                if (($v_result = $this->privDirCheck($p_parent_dir)) != 1) {
                    return $v_result;
                }
            }
        }

        // ----- Create the directory
        if (!@mkdir($p_dir, 0777)) {
            throw new PclZipException("Unable to create directory '$p_dir'", static::PCLZIP_ERR_DIR_CREATE_FAIL);

            // ----- Return
        }

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privMerge()
    // Description :
    //   If $p_archive_to_add does not exist, the function exit with a success result.
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privMerge(&$p_archive_to_add)
    {
        $v_result = 1;

        // ----- Look if the archive_to_add exists
        if (!is_file($p_archive_to_add->zipname)) {
            // ----- Nothing to merge, so merge is a success
            $v_result = 1;

            // ----- Return
            return $v_result;
        }

        // ----- Look if the archive exists
        if (!is_file($this->zipname)) {
            // ----- Do a duplicate
            $v_result = $this->privDuplicate($p_archive_to_add->zipname);

            // ----- Return
            return $v_result;
        }

        // ----- Open the zip file
        if (($v_result = $this->privOpenFd('rb')) != 1) {
            // ----- Return
            return $v_result;
        }

        // ----- Read the central directory informations
        $v_central_dir = [];
        if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1) {
            $this->privCloseFd();

            return $v_result;
        }

        // ----- Go to beginning of File
        @rewind($this->zip_fd);

        // ----- Open the archive_to_add file
        if (($v_result = $p_archive_to_add->privOpenFd('rb')) != 1) {
            $this->privCloseFd();

            // ----- Return
            return $v_result;
        }

        // ----- Read the central directory informations
        $v_central_dir_to_add = [];
        if (($v_result = $p_archive_to_add->privReadEndCentralDir($v_central_dir_to_add)) != 1) {
            $this->privCloseFd();
            $p_archive_to_add->privCloseFd();

            return $v_result;
        }

        // ----- Go to beginning of File
        @rewind($p_archive_to_add->zip_fd);

        // ----- Creates a temporay file
        $v_zip_temp_name = static::PCLZIP_TEMPORARY_DIR . uniqid('pclzip-') . '.tmp';

        // ----- Open the temporary file in write mode
        if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0) {
            $this->privCloseFd();
            $p_archive_to_add->privCloseFd();

            throw new PclZipException('Unable to open temporary file \'' . $v_zip_temp_name . '\' in binary write mode', static::PCLZIP_ERR_READ_OPEN_FAIL);

            // ----- Return
        }

        // ----- Copy the files from the archive to the temporary file
        // TBC : Here I should better append the file and go back to erase the central dir
        $v_size = $v_central_dir['offset'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = fread($this->zip_fd, $v_read_size);
            @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Copy the files from the archive_to_add into the temporary file
        $v_size = $v_central_dir_to_add['offset'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = fread($p_archive_to_add->zip_fd, $v_read_size);
            @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Store the offset of the central dir
        $v_offset = @ftell($v_zip_temp_fd);

        // ----- Copy the block of file headers from the old archive
        $v_size = $v_central_dir['size'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = @fread($this->zip_fd, $v_read_size);
            @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Copy the block of file headers from the archive_to_add
        $v_size = $v_central_dir_to_add['size'];
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = @fread($p_archive_to_add->zip_fd, $v_read_size);
            @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Merge the file comments
        $v_comment = $v_central_dir['comment'] . ' ' . $v_central_dir_to_add['comment'];

        // ----- Calculate the size of the (new) central header
        $v_size = @ftell($v_zip_temp_fd) - $v_offset;

        // ----- Swap the file descriptor
        // Here is a trick : I swap the temporary fd with the zip fd, in order to use
        // the following methods on the temporary fil and not the real archive fd
        $v_swap        = $this->zip_fd;
        $this->zip_fd  = $v_zip_temp_fd;
        $v_zip_temp_fd = $v_swap;

        // ----- Create the central dir footer
        if (($v_result = $this->privWriteCentralHeader($v_central_dir['entries'] + $v_central_dir_to_add['entries'], $v_size, $v_offset, $v_comment)) != 1) {
            $this->privCloseFd();
            $p_archive_to_add->privCloseFd();
            @fclose($v_zip_temp_fd);
            $this->zip_fd = null;

            // ----- Reset the file list
            unset($v_header_list);

            // ----- Return
            return $v_result;
        }

        // ----- Swap back the file descriptor
        $v_swap        = $this->zip_fd;
        $this->zip_fd  = $v_zip_temp_fd;
        $v_zip_temp_fd = $v_swap;

        // ----- Close
        $this->privCloseFd();
        $p_archive_to_add->privCloseFd();

        // ----- Close the temporary file
        @fclose($v_zip_temp_fd);

        // ----- Delete the zip file
        // TBC : I should test the result ...
        @unlink($this->zipname);

        // ----- Rename the temporary file
        // TBC : I should test the result ...
        //@rename($v_zip_temp_name, $this->zipname);
        PclZipUtilRename($v_zip_temp_name, $this->zipname);

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
    // Function : privDuplicate()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    protected function privDuplicate($p_archive_filename)
    {
        $v_result = 1;

        // ----- Look if the $p_archive_filename exists
        if (!is_file($p_archive_filename)) {
            // ----- Nothing to duplicate, so duplicate is a success.
            $v_result = 1;

            // ----- Return
            return $v_result;
        }

        // ----- Open the zip file
        if (($v_result = $this->privOpenFd('wb')) != 1) {
            // ----- Return
            return $v_result;
        }

        // ----- Open the temporary file in write mode
        if (($v_zip_temp_fd = @fopen($p_archive_filename, 'rb')) == 0) {
            $this->privCloseFd();

            throw new PclZipException('Unable to open archive file \'' . $p_archive_filename . '\' in binary write mode', static::PCLZIP_ERR_READ_OPEN_FAIL);

            // ----- Return
        }

        // ----- Copy the files from the archive to the temporary file
        // TBC : Here I should better append the file and go back to erase the central dir
        $v_size = filesize($p_archive_filename);
        while ($v_size != 0) {
            $v_read_size = ($v_size < static::PCLZIP_READ_BLOCK_SIZE ? $v_size : static::PCLZIP_READ_BLOCK_SIZE);
            $v_buffer    = fread($v_zip_temp_fd, $v_read_size);
            @fwrite($this->zip_fd, $v_buffer, $v_read_size);
            $v_size -= $v_read_size;
        }

        // ----- Close
        $this->privCloseFd();

        // ----- Close the temporary file
        @fclose($v_zip_temp_fd);

        // ----- Return
        return $v_result;
    }
    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------
}

// End of class
// --------------------------------------------------------------------------------


// --------------------------------------------------------------------------------
// Function : PclZipUtilPathReduction()
// Description :
// Parameters :
// Return Values :
// --------------------------------------------------------------------------------
function PclZipUtilPathReduction($p_dir)
{
    $v_result = "";

    // ----- Look for not empty path
    if ($p_dir != "") {
        // ----- Explode path by directory names
        $v_list = explode("/", $p_dir);

        // ----- Study directories from last to first
        $v_skip = 0;
        for ($i = sizeof($v_list) - 1; $i >= 0; $i--) {
            // ----- Look for current path
            if ($v_list[$i] == ".") {
                // ----- Ignore this directory
                // Should be the first $i=0, but no check is done
            } elseif ($v_list[$i] == "..") {
                $v_skip++;
            } elseif ($v_list[$i] == "") {
                // ----- First '/' i.e. root slash
                if ($i == 0) {
                    $v_result = "/" . $v_result;
                    if ($v_skip > 0) {
                        // ----- It is an invalid path, so the path is not modified
                        // TBC
                        $v_result = $p_dir;
                        $v_skip   = 0;
                    }

                    // ----- Last '/' i.e. indicates a directory
                } elseif ($i == (sizeof($v_list) - 1)) {
                    $v_result = $v_list[$i];

                // ----- Double '/' inside the path
                } else {
                    // ----- Ignore only the double '//' in path,
                    // but not the first and last '/'
                }
            } else {
                // ----- Look for item to skip
                if ($v_skip > 0) {
                    $v_skip--;
                } else {
                    $v_result = $v_list[$i] . ($i != (sizeof($v_list) - 1) ? "/" . $v_result : "");
                }
            }
        }

        // ----- Look for skip
        if ($v_skip > 0) {
            while ($v_skip > 0) {
                $v_result = '../' . $v_result;
                $v_skip--;
            }
        }
    }

    // ----- Return
    return $v_result;
}
// --------------------------------------------------------------------------------

// --------------------------------------------------------------------------------
// Function : PclZipUtilPathInclusion()
// Description :
//   This function indicates if the path $p_path is under the $p_dir tree. Or,
//   said in an other way, if the file or sub-dir $p_path is inside the dir
//   $p_dir.
//   The function indicates also if the path is exactly the same as the dir.
//   This function supports path with duplicated '/' like '//', but does not
//   support '.' or '..' statements.
// Parameters :
// Return Values :
//   0 if $p_path is not inside directory $p_dir
//   1 if $p_path is inside directory $p_dir
//   2 if $p_path is exactly the same as $p_dir
// --------------------------------------------------------------------------------
function PclZipUtilPathInclusion($p_dir, $p_path)
{
    $v_result = 1;

    // ----- Look for path beginning by ./
    if (($p_dir == '.') || ((strlen($p_dir) >= 2) && (substr($p_dir, 0, 2) == './'))) {
        $p_dir = PclZipUtilTranslateWinPath(getcwd(), false) . '/' . substr($p_dir, 1);
    }
    if (($p_path == '.') || ((strlen($p_path) >= 2) && (substr($p_path, 0, 2) == './'))) {
        $p_path = PclZipUtilTranslateWinPath(getcwd(), false) . '/' . substr($p_path, 1);
    }

    // ----- Explode dir and path by directory separator
    $v_list_dir       = explode("/", $p_dir);
    $v_list_dir_size  = sizeof($v_list_dir);
    $v_list_path      = explode("/", $p_path);
    $v_list_path_size = sizeof($v_list_path);

    // ----- Study directories paths
    $i = 0;
    $j = 0;
    while (($i < $v_list_dir_size) && ($j < $v_list_path_size) && ($v_result)) {
        // ----- Look for empty dir (path reduction)
        if ($v_list_dir[$i] == '') {
            $i++;
            continue;
        }
        if ($v_list_path[$j] == '') {
            $j++;
            continue;
        }

        // ----- Compare the items
        if (($v_list_dir[$i] != $v_list_path[$j]) && ($v_list_dir[$i] != '') && ($v_list_path[$j] != '')) {
            $v_result = 0;
        }

        // ----- Next items
        $i++;
        $j++;
    }

    // ----- Look if everything seems to be the same
    if ($v_result) {
        // ----- Skip all the empty items
        while (($j < $v_list_path_size) && ($v_list_path[$j] == '')) {
            $j++;
        }
        while (($i < $v_list_dir_size) && ($v_list_dir[$i] == '')) {
            $i++;
        }

        if (($i >= $v_list_dir_size) && ($j >= $v_list_path_size)) {
            // ----- There are exactly the same
            $v_result = 2;
        } elseif ($i < $v_list_dir_size) {
            // ----- The path is shorter than the dir
            $v_result = 0;
        }
    }

    // ----- Return
    return $v_result;
}
// --------------------------------------------------------------------------------

// --------------------------------------------------------------------------------
// Function : PclZipUtilCopyBlock()
// Description :
// Parameters :
//   $p_mode : read/write compression mode
//             0 : src & dest normal
//             1 : src gzip, dest normal
//             2 : src normal, dest gzip
//             3 : src & dest gzip
// Return Values :
// --------------------------------------------------------------------------------
function PclZipUtilCopyBlock($p_src, $p_dest, $p_size, $p_mode = 0)
{
    $v_result = 1;

    if ($p_mode == 0) {
        while ($p_size != 0) {
            $v_read_size = ($p_size < READ_BLOCK_SIZE ? $p_size : READ_BLOCK_SIZE);
            $v_buffer    = @fread($p_src, $v_read_size);
            @fwrite($p_dest, $v_buffer, $v_read_size);
            $p_size -= $v_read_size;
        }
    } elseif ($p_mode == 1) {
        while ($p_size != 0) {
            $v_read_size = ($p_size < READ_BLOCK_SIZE ? $p_size : READ_BLOCK_SIZE);
            $v_buffer    = @gzread($p_src, $v_read_size);
            @fwrite($p_dest, $v_buffer, $v_read_size);
            $p_size -= $v_read_size;
        }
    } elseif ($p_mode == 2) {
        while ($p_size != 0) {
            $v_read_size = ($p_size < READ_BLOCK_SIZE ? $p_size : READ_BLOCK_SIZE);
            $v_buffer    = @fread($p_src, $v_read_size);
            @gzwrite($p_dest, $v_buffer, $v_read_size);
            $p_size -= $v_read_size;
        }
    } elseif ($p_mode == 3) {
        while ($p_size != 0) {
            $v_read_size = ($p_size < READ_BLOCK_SIZE ? $p_size : READ_BLOCK_SIZE);
            $v_buffer    = @gzread($p_src, $v_read_size);
            @gzwrite($p_dest, $v_buffer, $v_read_size);
            $p_size -= $v_read_size;
        }
    }

    // ----- Return
    return $v_result;
}
// --------------------------------------------------------------------------------

// --------------------------------------------------------------------------------
// Function : PclZipUtilRename()
// Description :
//   This function tries to do a simple rename() function. If it fails, it
//   tries to copy the $p_src file in a new $p_dest file and then unlink the
//   first one.
// Parameters :
//   $p_src : Old filename
//   $p_dest : New filename
// Return Values :
//   1 on success, 0 on failure.
// --------------------------------------------------------------------------------
function PclZipUtilRename($p_src, $p_dest)
{
    $v_result = 1;

    // ----- Try to rename the files
    if (!@rename($p_src, $p_dest)) {
        // ----- Try to copy & unlink the src
        if (!@copy($p_src, $p_dest)) {
            $v_result = 0;
        } elseif (!@unlink($p_src)) {
            $v_result = 0;
        }
    }

    // ----- Return
    return $v_result;
}
// --------------------------------------------------------------------------------

// --------------------------------------------------------------------------------
// Function : PclZipUtilOptionText()
// Description :
//   Translate option value in text. Mainly for debug purpose.
// Parameters :
//   $p_option : the option value.
// Return Values :
//   The option text value.
// --------------------------------------------------------------------------------
function PclZipUtilOptionText($p_option)
{
    $v_list = get_defined_constants();
    for (reset($v_list); $v_key = key($v_list); next($v_list)) {
        $v_prefix = substr($v_key, 0, 10);
        if ((($v_prefix == 'OPT') || ($v_prefix == 'CB_') || ($v_prefix == 'ATT')) && ($v_list[$v_key] == $p_option)) {
            return $v_key;
        }
    }

    $v_result = 'Unknown';

    return $v_result;
}
// --------------------------------------------------------------------------------

// --------------------------------------------------------------------------------
// Function : PclZipUtilTranslateWinPath()
// Description :
//   Translate windows path by replacing '\' by '/' and optionally removing
//   drive letter.
// Parameters :
//   $p_path : path to translate.
//   $p_remove_disk_letter : true | false
// Return Values :
//   The path translated.
// --------------------------------------------------------------------------------
function PclZipUtilTranslateWinPath($p_path, $p_remove_disk_letter = true)
{
    if (stristr(php_uname(), 'windows')) {
        // ----- Look for potential disk letter
        if (($p_remove_disk_letter) && (($v_position = strpos($p_path, ':')) != false)) {
            $p_path = substr($p_path, $v_position + 1);
        }
        // ----- Change potential windows directory separator
        if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0, 1) == '\\')) {
            $p_path = strtr($p_path, '\\', '/');
        }
    }

    return $p_path;
}
// --------------------------------------------------------------------------------
