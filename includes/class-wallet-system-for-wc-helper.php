<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/aryanbokde
 * @since      1.0.0
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/includes
 * @author     Rakesh <aryanbokde@gmail.com>
 */


require_once WALLET_SYSTEM_FOR_WC_DIR_PATH . 'phpqrcode/qrlib.php';

class Wallet_System_For_Wc_Helper {

    
    // Wallet_System_For_Wc Generate a qr code for user
    public function wallet_system_for_wc_generate_qrcode($id){

        $user = get_user_by('ID', $id);
        $domain = home_url();  
        $id         = $user->ID;
        $username   = $user->user_login; 
        $email      = $user->user_email;
        $user_url   = $domain.'/user-profile/'.$id;

        $upload_dir = wp_upload_dir();
        $upload_basedir = $upload_dir['basedir']."/qrcode/";
        $upload_baseurl = $upload_dir['baseurl']."/qrcode/";

        // Check if the folder doesn't already exist
        if (!is_dir($upload_basedir)) {
            mkdir($upload_basedir, 0777, true);
        }
        
        $file_name = "user-qr-".$id.".png";
        $upload_dir = $upload_basedir.$file_name;
        
        // Check if the file already exists in the custom folder
        if (file_exists($upload_dir)) {
            // Delete the existing file
            unlink($upload_dir);
            // if (unlink($upload_dir)) {
            //     echo "File deleted";
            // }else{
            //     echo "File not deleted";
            // }
        }

        // $parts = explode('/', rtrim($upload_dir, '/')); 
        // $qr_code_file_name = array_pop($parts);
        // echo $qr_code_file_name;

        $img_path = $upload_baseurl.$file_name;

        // $ecc stores error correction capability('L')
        $ecc = 'L';
        $pixel_Size = 10;
        $frame_Size = 1;
        $text = $user_url;    

        // Generates QR Code and Stores it in directory given
        QRcode::png($text, $upload_dir, $ecc, $pixel_Size, $frame_Size); 

        return $img_path;
    }


    // Wallet_System_For_Wc Generate a qr code for user
    public function wallet_system_for_wc_user_registration_hook( $user_id ) {
    
        $user_meta = get_user_meta($user_id); // Get user all metadata
        $user_qr_url = get_user_meta($user_id, 'user_qr_url', true);// metadata by specifying
    
        // Check if the meta value is empty or null
        if ( empty($user_qr_url) && $user_qr_url == null ) {
    
            $user_qr_url = $this->wallet_system_for_wc_generate_qrcode($user_id);
            $parts = explode('/', rtrim($user_qr_url, '/')); 
            $qr_code_file_name = array_pop($parts);
            
            update_user_meta($user_id, 'user_qr_url', $user_qr_url);
            update_user_meta($user_id, 'user_qr_file_name', $qr_code_file_name);
            update_user_meta($user_id, 'user_qr_wallet', 0);
    
        }
    
    }
    
    
    // Hook into the delete_user action
    public function wallet_system_for_wc_user_and_meta($user_id) {
        // Delete the user metadata
        $user_qr_url = get_user_meta($user_id, 'user_qr_url', true);
        $user_qr_file_name = get_user_meta($user_id, 'user_qr_file_name', true);

        // Check if the meta value is empty or null
        if ( !empty($user_qr_url) && $user_qr_url !== null ) {

            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir']."/qrcode/".$user_qr_file_name;
            
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        delete_user_meta($user_id, 'user_qr_url');
        delete_user_meta($user_id, 'user_qr_file_name');
        delete_user_meta($user_id, 'user_qr_wallet');

    }


    // Add custom capability for editing user metadata
    public function wallet_system_for_wc_custom_capabilities() {
        $roles = array('shop_manager', 'administrator'); // Adjust role names as needed
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->add_cap('edit_user_metadata');
            }
        }
    }
    

}
