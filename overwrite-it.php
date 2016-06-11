<?php
/**
 * OverWrite It - WordPress Plugin.
 *
 * 'OverWrite It' overwrites to existing files when you use Media Library.
 *
 * @package OverWriteIt
 * @since 0.0.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class OverWriteIt {
    private static $_instance = null;

    /**
     * call this method to get instance
     **/
    public static function getInstance() {
        if (null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * class via the `new` operator from outside of this class.
     */
    protected function __construct() {
        // check PHP and WP versions
        add_action( 'admin_notices', array( $this, 'version_check' ), 9999 );

        // add warning into new media screen
        add_action( 'post-upload-ui', array( $this, 'display_warning' ) );

        // do the magic
        add_filter( 'wp_handle_upload_prefilter', array( $this, 'handle_upload' ), 9999 );

    }

    /**
     * Check required versions
     */
    public function version_check() {
        global $wp_version;

        $errors = array();

        // check PHP minimum required version
        if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
            $errors[] = sprintf( __( 'Required PHP version: 5.3.0+ but your version is %s', 'overwrite-it' ), PHP_VERSION );
        }

        // check WordPress miniumun required version
        if ( version_compare( $wp_version, '2.9', '<' ) ) {
            $errors[] = sprintf( __( 'Required WordPress version: 2.9+ but your version is %s', 'overwrite-it' ), $wp_version );
        }

        // there are errors, display them
        if ( !empty( $errors ) ) {
            echo '<div class="notice notice-error">';
            echo '<p>'.__( 'OverWrite It: following errors are occurred, please fix them to run the plugin properly', 'overwrite-it' ).'</p>';
            echo '<ul class="ul-disc">';

            foreach ($errors as $error) {
                echo '<li>'.$error.'</li>';
            }

            echo '</ul>';
            echo '</div>';
        }
    }


    /**
     * Display warnings to the user
     */
    public function display_warning() {
        echo '<div class="notice notice-warning notice-alt"><p>' .
            __( '<strong>Warning:</strong> Uploading file will be overwritten to first matching file.', 'overwrite-it' ) .
            '</p></div>';

        echo '<div class="notice notice-info notice-alt"><p>' .
            __( '<strong>Overwrite It</strong> plugin is active. It searches <code>wp-content/uploads</code> folder ' .
            'and will overwrite the file that you upload to the first match. There is no excluded folder or file so be careful when using.', 'overwrite-it' ) .
            '</p></div>';
    }


    /**
     * hook into upload action to do overwrite
     *
     * @param array $file
     * @return mixed
     */
    public function handle_upload($file) {

        $file_name = sanitize_file_name( $file['name'] );

        // search file
        $path = $this->_search_by_name( $file_name );

        // file exists
        if ( $path != false) {
            // check for upload errors
            if ( ( $file['error'] > 0) || // upload error
                (! ($file['size'] > 0 )) || // empty file
                (! @ is_uploaded_file( $file['tmp_name'] )) //not an uploaded file
            ) {
                // wp will handle errors
                return $file;
            }

            // check file type and mime with given filename
            $wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], false );
            $type = $wp_filetype['type'];
            $ext = $wp_filetype['ext'];

            if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) ) {
                // wp will handle errors
                return $file;
            }

            // move the file into proper path
            if (@ !move_uploaded_file( $file['tmp_name'], $path.'/'.$file_name) ) {
                // wp will handle errors
                return $file;
            }

            // tadaa
            $file['error'] = __('This file has overwritten:', 'overwrite-it' ) . ' «'.str_replace (get_home_path(), '', $path.'/'.$file_name).'»';
        }

        return $file;
    }


    /**
     * search file by name recursively in the path
     *
     * @param string $filename
     * @param null|string $path
     * @return bool|string
     */
    private function _search_by_name($filename, $path = null) {
        if ($path == null) $path = WP_CONTENT_DIR.'/uploads';

        // get files and folders
        $path_content = scandir($path);

        foreach ($path_content as $item) {
            if ($item != '..' && $item != '.') {
                // if not a folder
                if (!is_dir($path.'/'.$item)) {
                    // found the file
                    if ($item == $filename) {
                        return $path;
                    }
                }
                else {
                    // this is a folder, go into deeper
                    $find = $this->_search_by_name($filename, $path.'/'.$item);
                    if ( $find != false) {
                        return $find;
                    }
                }
            } // if
        } // foreach

        return false;
    }


    /**
     * Kill traditional methods of creating new instances
     **/
    private function __clone(){}
    private function __wakeup() {}
}