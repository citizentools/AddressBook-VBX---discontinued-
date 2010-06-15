<?php

$CI =& get_instance();
$op = @$_REQUEST['op'];
$user_id = $CI->session->userdata('user_id');

function get_data($table, $R=NULL) 
{ // {{{
    $CI =& get_instance();

    if(empty($R)) $R = $_REQUEST;

    $fields = $CI->db->list_fields($table);
    $CI->db->start_cache();
    if(isset($R['iSortCol_0'])) {
        for($i=0; $i<mysql_real_escape_string($R['iSortingCols']); $i++) {
            $CI->db->order_by($fields[$R['iSortCol_'.$i]], $R['sSortDir_'.$i]);
        }
    }
    $CI->db->stop_cache();

    $total = $CI->db->count_all_results($table);
    $res = $CI->db->get($table);

    $rows = array();
    foreach($res->result_array() as $row) {
        $parsed_row = array();
        foreach($row as $k=>$v) {
            if(empty($v)) $parsed_row[] = '';
            else $parsed_row[] = $v;
        }
        $rows[] = $parsed_row;
    }

    $results = array(
        'sEcho'=>intval(@$R['sEcho']),
        'iTotalRecords'=>$total,
        'iTotalDisplayRecords'=>$total,
        'sColumns'=>implode(',', $fields),
        'aaData'=>$rows
    );

    return $results;
} // }}}

if($op == 'contacts/del' || $op == 'contact/del')
{
}

else if($op == 'contacts/get' || $op == 'contact/get')
{ // {{{
    $results = get_data('addressbook_contacts');
} // }}}

else if($op == 'contacts/new' || $op =='contact/new') 
{ // {{{
    try {
        $name = @$_REQUEST['name'];
        $title = @$_REQUEST['title'];
        $company = @$_REQUEST['company'];
        $phone = @$_REQUEST['phone'];
        $email = @$_REQUEST['email'];

        $first_name = trim(substr($name, 0, strrpos($name, ' '))); 
        $last_name = trim(substr($name, strrpos($name, ' ') + 1)); 

        $new_contact = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'title' => $title,
            'company' => $company,
            'phone' => preg_replace('/[^0-9+]+/', '', $phone),
            'email' => $email,
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
            'user_id' => $user_id
        );

        if($CI->db->insert('addressbook_contacts', $new_contact)) {
            throw new Exception('SUCCESS');
        } else {
            throw new Exception('DB_ERROR');
        }

        throw new Exception('EXCEPTION');
    } catch(Exception $e) {
        switch($e->getMessage()) {
            case 'SUCCESS':
                $results = array(
                    'msg' => 'New contact created.',
                    'key' => 'SUCCESS',
                    'type' => 'success'
                );
                break;

            default:
            case 'EXCEPTION':
                $results = array(
                    'msg' => 'Cannot create new contact due to an exception error.',
                    'key' => 'EXCEPTION',
                    'type' => 'error'
                );
                break;
        }
    }
} // }}}

else if($op == 'contacts/update' || $op == 'contact/update')
{
}

else if($op == 'groups/del' || $op == 'group/del')
{
}

else if($op == 'groups/get' || $op == 'group/get')
{ // {{{
    $results = get_data('addressbook_groups');
} // }}}

else if($op == 'groups/new' || $op == 'group/new') 
{
}

else if($op == 'groups/update' || $op == 'group/update') 
{
}

else if($op == 'tags/del' || $op == 'tag/del')
{
}

else if($op == 'tags/get' || $op == 'tag/get')
{ // {{{
    $results = get_data('addressbook_tags');
} // }}}

else if($op == 'tags/new' || $op == 'tag/new')
{
}

else if($op == 'tags/update' || $op == 'tag/update')
{
}
?>

<?php if(!empty($op)): ?>
    <JSON_DATA><?php echo json_encode($results) ?></JSON_DATA>
<?php else: ?>
<?php
$sqls = file_get_contents('plugins/AddressBook-VBX/applets/addressbook/db.sql');
$sqls = explode(';', $sqls);
foreach($sqls as $sql) {
    if(trim($sql) != '') $CI->db->query($sql);
}
?>
<style>
table { width:100%; }
div.dataTables_processing { position:absolute; top:10px; right:40px; }
div.dataTables_length { float:left; visibility:hidden; }
div.dataTables_filter { text-align:right; }

input.edit_active { border:0px; margin:0px; margin-bottom:2px; padding:1px; }
input.edit_inactive { background-color:inherit; border:0px; margin:0px; margin-bottom:2px; padding:1px; }

div.data { display:none; }

div.section { border:1px solid gray; margin:10px; padding:10px; position:relative; }
div.section > h3 { margin-top:0px; }
div.section > input.new_btn { float:right; }
div.side.right { float:right; width:250px; }
div.side div.section:first-child { margin-top:0px; }

table.datatable th { background-color:#333; color:white; font-size:10px; text-transform:uppercase; vertical-align:bottom !important; padding:5px; }
table.datatable td { padding:5px; }
table.datatable tr {}
table.datatable tr.odd { background-color:#CCC; }
table.datatable tr:hover { background-color:lightgray; }
table.datatable tr.selected { background-color:#368FC9; }
table.datatable tr.selected:hover { background-color:#368FC9; }

span.err { color:red; font-size:10px; display:block; margin-bottom:3px; }

#browse_contacts th { padding:0px; visibility:hidden; }
#browse_contacts td { vertical-align:top; }
#browse_contacts div.profile_img { border:1px solid gray; width:50px; height:50px; }
#browse_contacts tr input[name="name"] { width:95%; }
#browse_contacts tr input[name="company"] { display:block; font-size:10px; width:95%; }
#browse_contacts tr input[name="title"] { display:block; font-size:10px; width:95%; }
#browse_contacts ul.letter_filter li { padding:2px; display:block; }
#browse_contacts ul.letter_filter li:hover { background-color:#ccc; cursor:pointer; }
#browse_contacts input.import_btn { float:right; margin-right:5px; }
#browse_contacts input.new_contact_btn { float:right; }

#list_of_groups { display:none; }
#list_of_groups th { padding:0px; visibility:hidden; }
#list_of_groups input.new_group_btn { float:right; }

#list_of_tags { display:none; }
#list_of_tags th { padding:0px; visibility:hidden; }
#list_of_tags input.new_tag_btn { float:right; }

#recent_contacts { margin-top:0px; }
#recent_contacts th { padding:0px; visibility:hidden; }
#recent_contacts div.profile_img { border:1px solid gray; width:50px; height:50px; }
#recent_contacts tr span.company { display:block; font-size:10px;  }
#recent_contacts tr span.title { display:block; font-size:10px; }

ul.errors { color:red; }
ul.errors.li { margin:2px; }
</style>

<div class="vbx-content-main">
    <div class="vbx-content-tabs">
        <h2 class="vbx-content-heading">Address Book</h2>
    </div><!-- .vbx-content-tabs -->

    <div class="vbx-table-section">
        <div id="index_page">
            <div class="side right">
                <div id="list_of_groups" class="section">
                    <!-- {{{ -->
                    <input class="new_group_btn" type="button" value=" + " /> 
                    <h3>Groups</h3>

                    <table class="datatable">
                        <?php $fields = $CI->db->list_fields('addressbook_groups') ?>
                        <thead>
                            <tr>
                                <?php foreach($fields as $v): ?>
                                <th></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php foreach($fields as $v): ?>
                                <td></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                    <!-- }}} -->
                </div>

                <div id="list_of_tags" class="section">
                    <!-- {{{ -->
                    <input class="new_tag_btn" type="button" value=" + " />
                    <h3>Tags</h3>

                    <table class="datatable">
                        <?php $fields = $CI->db->list_fields('addressbook_tags') ?>
                        <thead>
                            <tr>
                                <?php foreach($fields as $v): ?>
                                <th></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php foreach($fields as $v): ?>
                                <td></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                    <!-- }}} -->
                </div>

                <div id="recent_contacts" class="section">
                    <! -- {{{ -->
                    <h3>Recently Added Contacts</h3>

                    <table class="datatable">
                        <?php $fields = $CI->db->list_fields('addressbook_contacts') ?>
                        <thead>
                            <tr>
                                <?php foreach($fields as $v): ?>
                                <th></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php foreach($fields as $v): ?>
                                <td></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                    <!-- }}} -->
                </div>
            </div><!-- .side.right -->

            <div style="margin-right:250px;">
                <div id="browse_contacts" class="section">
                    <!-- {{{ -->
                    <input class="new_contact_btn" type="button" value=" + " />
                    <input class="import_btn" type="button" value="Import" />
                    <h3>Browse Contacts</h3>

                    <div id="new_contact_form_template" style="display:none;">
                        <table>
                            <tr>
                                <td>
                                    <form>
                                    <div class="profile_img"></div>
                                </td>
                                <td>
                                    <input name="name" type="text" placeholder="Name" />
                                    <input name="title" type="text" placeholder="Title" />
                                    <input name="company" type="text" placeholder="Company" />
                                </td>
                                <td>
                                    <input name="phone" type="text" placeholder="Phone" />
                                </td>
                                <td>
                                    <div style="float:right; text-align:right;">
                                        <input class="cancel_btn" type="button" value="Cancel" /><br />
                                        <input class="save_btn" type="button" value="Save" />
                                    </div>

                                    <input name="email" type="text" placeholder="Email" />
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="display:table; width:100%;">
                        <div style="display:table-cell; padding-right:10px;">
                            <ul class="letter_filter" style="width:20px; display:block;">
                                <?php foreach(range('A', 'Z') as $letter): ?>
                                <li><?php echo $letter ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div style="display:table-cell;">
                            <table class="datatable">
                                <?php $fields = $CI->db->list_fields('addressbook_contacts') ?>
                                <thead>
                                    <tr>
                                        <?php foreach($fields as $v): ?>
                                        <th></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php foreach($fields as $v): ?>
                                        <td></td>
                                        <?php endforeach; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- }}} -->
                </div><!-- #browse_contacts -->
            </div>
        </div>
    </div>
</div>

<script>
var base_url = '<?php echo base_url() ?>';
var user_numbers = <?php echo json_encode($user_numbers) ?>;
</script>

<?php $CI->template->add_js('plugins/AddressBook-VBX/assets/js/twilio/datatables/jquery.dataTables.min.js'); ?>
<?php $CI->template->add_js('plugins/AddressBook-VBX/assets/js/twilio/twilio.js'); ?>
<?php $CI->template->add_js('plugins/AddressBook-VBX/assets/js/twilio/jquery.tw_table.js'); ?>

<?php $CI->template->add_js('plugins/AddressBook-VBX/applets/addressbook/js/index_page.js'); ?>
<?php $CI->template->add_js('plugins/AddressBook-VBX/applets/addressbook/js/index_init.js'); ?>

<?php endif; ?>
