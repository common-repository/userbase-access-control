<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if($_GET['page']=='user_access'){
    echo '<div class="wrap">';
    echo '<div class="icon32" id="icon-users"><br></div>';
    echo '<h2>User Access Control</h2>';
?>
<br />

<table cellspacing="0" class="widefat fixed">
    <thead>
	<tr>
            <th scope="col" id="group-name" class="manage-column column-group-name" style="width: 250px;">User</th>

<?php

    $accessGroups = get_option('uac_access_groups');//$wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE `option_name` LIKE 'group_%' ", ARRAY_A);
    //dump($accessGroups);
    foreach($accessGroups as $ID=>&$Group){        
        //$Groups[$Group['option_name']] = $GroupData;
        echo '<th scope="col" id="group-desc" class="manage-column column-group-desc" style="text-overflow: ellipsis;white-space: nowrap;" title="'.$Group['name'].'">'.$Group['name'].'</th>';
    }

?>            
        </tr>
    </thead>
    <tbody class="list:group" id="group-list">
<?php
    $Users = get_users();
    foreach($Users as $user){
            $rights = get_user_meta($user->ID, '_uac_access_group');
            echo '<tr>';
                echo '<td class="username column-username" style="padding:5px 8px;">';
                echo '<span id="user_'.$user->ID.'" class="description" style="display:none;"></span>';
                echo '<img width="16" height="16" id="avatar_'.$user->ID.'" class="avatar avatar-16 photo" src="http://1.gravatar.com/avatar/'.md5($user->user_email).'?s=16&amp;r=G" alt="">';
                echo $user->user_login.' ';
                //echo '<div class="row-actions">';
                echo '<span class="description">('.$user->user_email.')</span>';
                //echo '</div>';
                echo '</td>';
                foreach($accessGroups as $ID=>&$Group){
                    $sel = '';
                    if(!empty ($rights)){
                        if(in_array($ID, $rights)){
                            $sel = 'checked="checked"';
                        }
                    }
                    echo '<td scope="col" id="group-desc" class="manage-column column-group-desc" title="'.$Group['name'].'" ><input class="permcheck" id="'.$user->ID.'-'.$ID.'" name="'.$Group['name'].'" type="checkbox" value="'.$user->ID.'" '.$sel.' /></td>';
                }
            echo '</tr>';


    }
?>
    </tbody>
</table>
<?php
    echo '</div>';
}
?>
<script>

jQuery(document).ready(function(){

        jQuery('.permcheck').click(function(){
        var check = jQuery(this);
        var user = check.val();
        var data = {
                action: 'updateuser_perm',
                permset: check.attr('id'),
                group: check.attr('name'),
                checked: check.attr('checked')
        };        
        jQuery('#user_'+user).html('<img src="<?php echo UAC_URL.'indicator.gif'; ?>" align="absmiddle" width="16" height="16" />');
        jQuery('#avatar_'+user).hide();
        jQuery('#user_'+user).show();
        check.removeAttr('checked').attr('disabled','disabled');
        jQuery.post(ajaxurl, data, function(response) {
            if(response === 'true'){
                check.attr('checked','checked');
            }
            check.removeAttr('disabled');
            jQuery('#avatar_'+user).show();
            jQuery('#user_'+user).empty();
        });

    })


})

</script>