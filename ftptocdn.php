<?php
/*
Plugin Name: FTP Image Upload
Description: Uploads images to an FTP server.
Version: 1.0
Author: Your Name
*/

class FTP_Image_Upload {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_filter('wp_handle_upload', array($this, 'custom_handle_upload'));
    }

    public function add_plugin_page() {
        add_menu_page(
            'FTP Image Upload',
            'FTP Image Upload',
            'manage_options',
            'ftp_image_upload',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h2>FTP Image Upload Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('ftp_image_upload_group');
                do_settings_sections('ftp_image_upload');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting('ftp_image_upload_group', 'ftp_host');
        register_setting('ftp_image_upload_group', 'ftp_user');
        register_setting('ftp_image_upload_group', 'ftp_pass');
        register_setting('ftp_image_upload_group', 'ftp_directory'); // Yeni eklendi

        add_settings_section(
            'ftp_image_upload_section',
            'FTP Settings',
            array($this, 'print_section_info'),
            'ftp_image_upload'
        );

        add_settings_field(
            'ftp_host',
            'FTP Host',
            array($this, 'ftp_host_callback'),
            'ftp_image_upload',
            'ftp_image_upload_section'
        );

        add_settings_field(
            'ftp_user',
            'FTP User',
            array($this, 'ftp_user_callback'),
            'ftp_image_upload',
            'ftp_image_upload_section'
        );

        add_settings_field(
            'ftp_pass',
            'FTP Password',
            array($this, 'ftp_pass_callback'),
            'ftp_image_upload',
            'ftp_image_upload_section'
        );

        add_settings_field(
            'ftp_directory',
            'FTP Directory',
            array($this, 'ftp_directory_callback'),
            'ftp_image_upload',
            'ftp_image_upload_section'
        );
    }

    public function print_section_info() {
        print 'Enter your FTP credentials and directory below:';
    }

    public function ftp_host_callback() {
        printf(
            '<input type="text" id="ftp_host" name="ftp_host" value="%s" />',
            esc_attr(get_option('ftp_host'))
        );
    }

    public function ftp_user_callback() {
        printf(
            '<input type="text" id="ftp_user" name="ftp_user" value="%s" />',
            esc_attr(get_option('ftp_user'))
        );
    }

    public function ftp_pass_callback() {
        printf(
            '<input type="password" id="ftp_pass" name="ftp_pass" value="%s" />',
            esc_attr(get_option('ftp_pass'))
        );
    }

    public function ftp_directory_callback() {
        printf(
            '<input type="text" id="ftp_directory" name="ftp_directory" value="%s" />',
            esc_attr(get_option('ftp_directory'))
        );
    }

    public function custom_handle_upload($file) {
        $local_path = $file['file'];
        $remote_path = get_option('ftp_directory') . '/' . basename($local_path);
    
        $ftp_host = get_option('ftp_host');
        $ftp_user = get_option('ftp_user');
        $ftp_pass = get_option('ftp_pass');
    
        $connection = ftp_connect($ftp_host);
        $login = ftp_login($connection, $ftp_user, $ftp_pass);
    
        if ($connection && $login) {
            ftp_pasv($connection, true);
            $upload = ftp_put($connection, $remote_path, $local_path, FTP_BINARY);
            ftp_close($connection);
    
            if ($upload) {
                unlink($local_path);
            } else {
                // Hata mesajını göster
                error_log("FTP Upload Error: Upload failed for file $local_path to $remote_path");
            }
        } else {
            // Hata mesajını göster
            error_log("FTP Connection Error: Connection to $ftp_host failed with user $ftp_user");
        }
    
        return $file;
    }
    
}

$ftp_image_upload = new FTP_Image_Upload();
