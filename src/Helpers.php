<?php

namespace MirazMac\PclZip;

/**
 * Moved the inline functions to a separate file to meet the PSR standards
 */
class Helpers
{
// --------------------------------------------------------------------------------
    // Function : PclZipUtilPathReduction()
    // Description :
    // Parameters :
    // Return Values :
    // --------------------------------------------------------------------------------
    public static function pclZipUtilPathReduction($p_dir)
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
    public static function pclZipUtilPathInclusion($p_dir, $p_path)
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
    public static function pclZipUtilCopyBlock($p_src, $p_dest, $p_size, $p_mode = 0)
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
    public static function pclZipUtilRename($p_src, $p_dest)
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
    public static function pclZipUtilOptionText($p_option)
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
    public static function pclZipUtilTranslateWinPath($p_path, $p_remove_disk_letter = true)
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
}
