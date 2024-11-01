<?php
/*
Plugin Name: Userbase Access Control
Plugin URI: http://www.digilab.co.za
Description: Create access control groups for pages and posts.
Author: David Cramer
Version: 1.0
Author URI: http://www.digilab.co.za
*/
define('UAC_PATH', plugin_dir_path(__FILE__));
define('UAC_URL', plugin_dir_url(__FILE__));

function custom_login_message($message){
    if(!empty($_GET['uac_login'])){
        $message = '<p id="login_error">You do not have access to this page</p><br />';
    }
    //$message = '<p class="message">Welcome, if you haven\'t already read our <a href="#">terms of service</a> please do so before you register.</p><br />';
    return $message;
}
add_filter('login_message', 'custom_login_message');

if(is_admin ()){

    function uac_updateuser(){
        $set = explode('-', $_POST['permset']);

        if(isset($_POST['checked'])){
            $groups = get_user_meta($set[0], '_uac_access_group');
            if(!empty($groups)){
                if(in_array($set[1], $groups)){
                    echo 'true'; //Already in Group '.$_POST['group'];
                    die;
                }
            }
            add_user_meta($set[0], '_uac_access_group', $set[1]);
            echo 'true';
            die;
        }else{
            $groups = get_user_meta($set[0], '_uac_access_group');
            if(empty($groups)){
                echo 'false';
                die;
            }
            if(in_array($set[1], $groups)){
                delete_user_meta($set[0], '_uac_access_group',$set[1]);
                echo 'false';
            }
            die;
        }
        die;
    }
    add_action('wp_ajax_updateuser_perm', 'uac_updateuser');
    
}

    function uac_menu(){
        add_users_page("Userbase Group Manager", "Access Groups", 'activate_plugins', "access_control", "uac_admin");
        add_users_page("Userbase User Access Rights", "User Access", 'activate_plugins', "user_access", "uac_users");
    }
    function uac_admin(){
        include(UAC_PATH.'access_control.php');
    }
    function uac_users(){
        include(UAC_PATH.'user_access.php');
    }
    function uac_assign($post){
        global $wpdb;
        $defaults = array();
        if(!empty($post->ID)){
            $PostID = $post->ID;
            $defaults = get_post_meta($post->ID, '_accessControl');
            //print_r($defaults);
        }
        $accessGroups = get_option('uac_access_groups');
        
        if($post->post_type == 'page'){ // only make pages login and deniedable.
            $isDefault = get_option('_groupAccess_isdefault');
            $isDenied = get_option('_groupAccess_isdenied');
            echo '<div id="visibility" class="misc-pub-section">';
            echo '<strong>Page Function</strong><br>';
            echo '<select id="usergroup_action" name="usergroup_action">';
            echo '<option value=""></option>';
            $sel = '';
            if($isDefault === $PostID){
                $sel = 'selected="selected"';
            }            
            echo '<option value="isLogin" class="level-0" '.$sel.'>Login page</option>';
            $sel = '';
            if($isDenied === $PostID){
                $sel = 'selected="selected"';
            }
            echo '<option value="isDenied" class="level-0" '.$sel.'>Access denied page</option>';
            echo '</select></div>';

        }

        if(!empty($accessGroups)){
            foreach($accessGroups as $groupID=>$group){
                //dump($group,0);
                //$groupData = get_option($group['option_name']);
                $sel = '';
                if(in_array($groupID, $defaults)){
                    $sel = 'checked="checked"';
                }
                echo '<div id="visibility" class="misc-pub-section" style="padding:3px 10px 6px;">';
                echo '<input type="checkbox" class="uac-group-checks" id="'.$groupID.'" name="usergroup_data[]" value="'.$groupID.'" '.$sel.' /> <label for="'.$groupID.'">'.$group['name'];
                if(!empty($group['desc'])){
                    echo ' : <span class="description">'.$group['desc'].'</span>';
                }
                echo '</label>';
                echo '</div>';
                
            }
            echo '<input type="hidden" name="usergroup_dataCheck" value="true" />';
        }
    }
    function uac_metabox(){
        $types = get_post_types();
        foreach($types as &$type){
            add_meta_box( 'userbase-access-control', 'Access Control', 'uac_assign', $type, 'side', 'high' );
        }
    }
    function uac_checkAuth(){

        global $wp_query;
        if(empty($wp_query)){return;}

        $current_user = wp_get_current_user();

        if(!empty($current_user->data->ID)){
            if(user_can($current_user->data->ID, 'activate_plugins')){
                return;
            }
        }
        foreach($wp_query->posts as $key=>&$currentpost){
            $groups = get_post_meta($wp_query->posts[$key]->ID, '_accessControl');
            if(!empty($current_user->data->ID)){
                $rights = get_user_meta($current_user->data->ID, '_uac_access_group');
            }else{
                $rights = array();
            }
            if(!empty($groups)){
                if(empty($current_user->ID)){
                    // not logged in-
                    $login = get_option('_groupAccess_isdefault');
                    if(!empty($login)){
                        $redirect = get_permalink($login);
                    }else{
                        $redirect = wp_login_url(get_permalink($currentpost->ID)).'&uac_login=true';
                    }
                    wp_safe_redirect($redirect);
                    die;
                }
                foreach($groups as &$access){
                    if(in_array($access, $rights)){
                      return;
                    }
                }

                $denied = get_option('_groupAccess_isdenied');
                if(empty($denied)){
                    $redirect = wp_login_url(get_permalink($currentpost->ID)).'&uac_login=true';
                }else{
                    $redirect = get_permalink($denied);
                }
                wp_safe_redirect($redirect);
                die;
            }
        }
    }
    function uac_menuAuth($menuItems){
        global $current_user;
        if(!empty($current_user->data->ID)){
            if(user_can($current_user->data->ID, 'activate_plugins')){
                return $menuItems;
            }
        }
        if(!empty($current_user->data->ID)){
            $rights = get_user_meta($current_user->data->ID, '_uac_access_group');
        }else{
            $rights = array();
        }
        $toremove = array();
        
        if(!empty($menuItems)){
            foreach($menuItems as $itemID=>$itemObject){
                $groups = get_post_meta($itemObject->object_id, '_accessControl');
                $cleared = 0;
                $groups = get_post_meta($itemObject->object_id, '_accessControl');
                if(!empty($groups)){
                    foreach($groups as &$access){
                        if(in_array($access, $rights)){
                            $cleared = 1;
                        }
                    }
                    if(empty($cleared)){
                        unset($menuItems[$itemID]);
                        foreach($menuItems as $subID=>$subObject){
                            if($subObject->menu_item_parent == $itemObject->ID){
                                $toremove[$subID] = $subID;
                            }
                        }                        
                    }
                }
            }
            if(!empty($toremove)){
                foreach($toremove as $remove){
                    unset($menuItems[$remove]);
                }
            }
        }
        return $menuItems;
    }
    function uac_savemeta($post_id){

      // verify this came from the our screen and with proper authorization,
      // because save_post can be triggered at other times
       //echo plugin_basename();
       if(empty($_POST['usergroup_dataCheck'])){
        return $post_id;
       }
      //  return $post_id;
      //}
      // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
      // to do anything
      if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
        return $post_id;
      }



      // Check permissions
      if ($_POST['post_type'] == 'page') {
        if ( !current_user_can( 'edit_page', $post_id ) )
          return $post_id;
      } else {
        if ( !current_user_can( 'edit_post', $post_id ) )
          return $post_id;
      }

       $isDefault = get_option('_groupAccess_isdefault');
       if($isDefault == $post_id){
           delete_option('_groupAccess_isdefault');
       }
       $isDenied = get_option('_groupAccess_isdenied');
       if($isDefault == $post_id){
           delete_option('_groupAccess_isdenied');
       }
      $oldMeta = get_post_meta($post_id, '_accessControl');
      if(!empty($oldMeta)){
          delete_post_meta($post_id, '_accessControl');
      }       

       if(!empty($_POST['usergroup_action'])){
            if($_POST['usergroup_action'] == 'isLogin'){
                update_option('_groupAccess_isdefault', $post_id );
            }
            if($_POST['usergroup_action'] == 'isDenied'){
                update_option('_groupAccess_isdenied', $post_id );
            }
        return;
       }

      if(!empty($_POST['usergroup_data'])){
        foreach ($_POST['usergroup_data'] as $userGroup){
            add_post_meta($post_id, '_accessControl', $userGroup);
        }
      }
      return;
    }

    function uac_saveuser($user_id){
	if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/user-edit.php') === false && strpos($_SERVER['REQUEST_URI'], '/wp-admin/profile.php') === false ||
			$_POST['action'] != 'update')
		return;

	$user_id = empty($_POST['user_id']) ? $_GET['user_id'] : $_POST['user_id'];

	if(!empty($_POST['accessGroup'])){
            update_usermeta( $user_id, '_accessControl', serialize($_POST['accessGroup']));
        }else{
            delete_usermeta($user_id,'_accessControl');
        }
       // die;
    }

    // menu
    add_action('admin_menu', 'uac_menu');
    // meta-box
    add_action('admin_menu', 'uac_metabox');

    add_action('get_header', 'uac_checkAuth');
    // save data
    add_action('save_post', 'uac_savemeta');

    add_action('edit_user_profile', 'uac_saveuser');

    add_action('init','uac_saveuser');
    add_filter('wp_nav_menu_objects','uac_menuAuth');





    ///// user profile addon test
/*
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) {
    global $current_user;
    get_currentuserinfo();
    $defaults = array();
    if($current_user->roles[0] == 'administrator'){

        $defaults = get_usermeta($user->id, '_accessControl');
        //print_r($defaults);

    ?>
<h3>Membership Information</h3>
<table class="form-table">
    <tr>
        <th><label for="accessGroup">Access Groups</label></th>
        <?php
            global $wpdb;

            $accessGroups = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE `option_name` LIKE 'group_%' ", ARRAY_A);
            echo '<td>';
            if(!empty($accessGroups)){
                foreach($accessGroups as $group){
                    $sel = '';
                    if(is_array($defaults)){
                        if(in_array($group['option_name'], $defaults)){
                            $sel = 'checked="checked"';
                        }
                    }
                    $groupData = get_option($group['option_name']);
                    $groupData = unserialize($groupData);

                        echo '<div style="padding:3px;"><input type="checkbox" name="accessGroup[]" id="groupname_'.$group['option_name'].'" value="'.$group['option_name'].'" class="checkbox" '.$sel.' /> <strong><label for="groupname_'.$group['option_name'].'">'.$groupData['name'].'</label></strong> :';
                        echo '<span class="description">'.$groupData['desc'].'</span></div>';

                }
            }
            echo '</td>';
        ?>
    </tr>
</table>
<?php
    }
}
*/
?>