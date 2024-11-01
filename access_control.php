<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$accessGroups = get_option('uac_access_groups');

if(!empty($_GET['edit'])){
    echo '<div class="wrap">';
    echo '<div class="icon32" id="icon-users"><br></div>';
    echo '<h2>Edit Group</h2>';
    ?>
<form name="editInterfaceForm" method="post" action="<?php echo str_replace('&edit='.$_GET['edit'], '', $_SERVER['REQUEST_URI']); ?>">
    <?php
        
        $data = $accessGroups[$_GET['edit']];        
        echo '<div class="tablenav">New Access Group: <input type="text" name="editGroup[name]" value="'.$data['name'].'" id="new-access-group" /> Description: <input type="text" name="editGroup[desc]" value="'.$data['desc'].'" id="edit-access-group" /><input type="hidden" name="editGroup[id]" value="'.$_GET['edit'].'" /> <input type="submit" value="Save" class="button" /> |  <input type="submit" name="editGroup[delete]" value="Delete" class="button" onclick="return confirm(\'Are you sure you want to delete this group?\');" /></div>';

    ?>
    </form>

    </div>
    <?php
    return;
}
?>


<?php
if($_GET['page']=='access_control'){
    echo '<div class="wrap">';
    echo '<div class="icon32" id="icon-users"><br></div>';
    echo '<h2>Group Manager</h2>';
?>
<form name="newInterfaceForm" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php
    echo '<div class="tablenav">New Access Group: <input type="text" name="group[name]" value="" id="new-access-group" /> Description: <input type="text" name="group[desc]" value="" id="new-access-group" /> <input type="submit" value="Create" class="button" /></div>';
    if(!empty($_POST['group'])){
        //add_option(uniqid('group_'), $_POST['group']);
        $groupID = uniqid();
        $accessGroups[$groupID] = $_POST['group'];
        update_option('uac_access_groups', $accessGroups);
    }
   
    if(!empty($_POST['editGroup'])){
        //dump($_POST);
        if(empty($_POST['editGroup']['delete'])){
            $accessGroups[$_POST['editGroup']['id']] = array(
                'name' => $_POST['editGroup']['name'],
                'desc' => $_POST['editGroup']['desc']
            );
        }else{            
            unset($accessGroups[$_POST['editGroup']['id']]);
        }
         
        update_option('uac_access_groups', $accessGroups);

    }
?>
</form>

<table cellspacing="0" class="widefat fixed">
    <thead>
	   <tr>
            <th scope="col" id="group-name" class="manage-column column-group-name" style="" width="30%">Group Name</th>
            <th scope="col" id="group-desc" class="manage-column column-group-desc" style="">Description</th>
            <th scope="col" id="group-name" class="manage-column column-group-name" style="" width="150">ID</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th scope="col" id="group-name" class="manage-column column-group-name" style="">Group Name</th>
            <th scope="col" id="group-desc" class="manage-column column-group-desc" style="">Description</th>
            <th scope="col" id="group-name" class="manage-column column-group-name" style="" width="150">ID</th>
        </tr>
    </tfoot>
    <tbody class="list:group" id="group-list">
        <tr class="iedit alternate" id="cat-1">            
            <td class="name column-name"><strong>Public</strong></td>
            <td class="name column-desc">Default Public Group. No login or membership required.</td>
            <td class="name column-id" style="" width="150"></th>
        </tr>
<?php    
    
    if(!empty($accessGroups)){
        //dump($accessGroups, 0);
        foreach($accessGroups as $groupID=>$group){
            echo '<tr>';                
                echo '<td class="name column-group-name"><strong>'.$group['name'].'</strong><div class="row-actions"><span class="edit"><a href="'.$_SERVER['REQUEST_URI'].'&edit='.$groupID.'">Edit</a></div></td>';
                echo '<td class="name column-group-desc">'.$group['desc'].'</td>';
                echo '<td class="name column-group-id">'.$groupID.'</td>';
            echo '</tr>';

        }
    }
?>
    </tbody>
</table>
<?php
    echo '</div>';
}
?>