<?php
$CI =& get_instance();
$plugin = OpenVBX::$currentPlugin;
$plugin = $plugin->getInfo();
$plugin_url = base_url().'plugins/'.$plugin['dir_name'];
$op = @$_REQUEST['op'];
$user_id = $CI->session->userdata('user_id');

if(!function_exists('json_encode')) {
    include($plugin['plugin_path'].'/vendors/json.php');
}

function get_data($table, $R=NULL) 
{ // {{{
    $CI =& get_instance();
    $plugin = OpenVBX::$currentPlugin;
    $plugin = $plugin->getInfo();
    $plugin_url = base_url().'plugins/'.$plugin['dir_name'];

    if(empty($R)) $R = $_REQUEST;

    $fields = $CI->db->list_fields($table);
    $CI->db->start_cache();

    if(@$R['sSearch']) {
        $s = $R['sSearch'];

        if(preg_match_all('/([a-z 0-9_]+)(\>|\<|\!:|:)+([^,:<>!]+)*,*/', $s, $matches, PREG_SET_ORDER)) {
            foreach($matches as $match) {
                $key = trim($match[1]);
                $v = !$match[3] ? '' : trim($match[3]);

                if(in_array($key, $fields)) {
                    if($match[2] == '<') {
                        $CI->db->where("$key < $v");
                    } else if($match[2] == '>') {
                        $CI->db->where("$key > $v");
                    } else if($match[2] == '!:') {
                        if(empty($v)) $CI->db->where("$key != ''");
                        else $CI->db->not_like($key, $v);
                    } else if($match[2] == ':') {
                        $CI->db->like($key, $v);
                    }
                }
            }
        } else if($table == 'addressbook_contacts') {
            if(is_numeric($s) && strlen($s) >= 7) {
                $CI->db->like('phone', $s);
            } else if(strlen($s) == 1) {
                $CI->db->where("first_name >= '$s'");
                if($s != 'z') $CI->db->where("first_name < '".chr(ord($s) + 1)."'");
            } else if(strlen($s) < 6) { 
                $CI->db->like('first_name', $s);
                $CI->db->or_like('last_name', $s);
                $CI->db->or_like('email', $s);
                $CI->db->or_like('title', $s);
                $CI->db->or_like('phone', $s);
            } else {
                $CI->db->where("MATCH (first_name, last_name, email, phone, company, title) AGAINST ('$s')", NULL, FALSE);
            }
        }
    }

    $CI->db->stop_cache();
    $total = $CI->db->count_all_results($table);

    // Order
    if($table == 'addressbook_contacts') {
        $CI->db->select("*, IF(first_name IS NULL or first_name='', 1, 0) AS nulltest", FALSE);
        $CI->db->order_by('nulltest asc');
    }

    if(isset($R['iSortCol_0'])) {
        for($i=0; $i<mysql_real_escape_string($R['iSortingCols']); $i++) {
            $CI->db->order_by($fields[$R['iSortCol_'.$i]], $R['sSortDir_'.$i]);
        }
    }

    // Limit
    if(@$R['iDisplayLength'] != -1) {
        $CI->db->limit($R['iDisplayLength'], @$R['iDisplayStart'] ? $R['iDisplayStart'] : 0);
    }

    $res = $CI->db->get($table);
    $rows = array();
    foreach($res->result_array() as $row) {
        $parsed_row = array();
        foreach($row as $k=>$v) {
            if(empty($v)) $parsed_row[] = '';
            else $parsed_row[] = $v;
        }

        if($table == 'addressbook_contacts') {
            // Get profile_img
            $filename = $plugin['plugin_path'].'/upload/'.$row['id'];

            if(is_file($filename)) {
                $parsed_row[16] = $plugin_url.'/upload/'.$row['id'];
            } else {
                $parsed_row[16] = '';
            }
        }

        $rows[] = $parsed_row;
    }

    error_log(json_encode($fields));
    error_log(json_encode($rows));

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
{ // {{{
    try {
        $contact_id = $_REQUEST['id'];

        if($CI->db->delete('addressbook_contacts', array('id' => $contact_id))) {
            throw new Exception('SUCCESS');
        }

        throw new Exception('EXCEPTION');
    } catch(Exception $e) {
        switch($e->getMessage()) {
            case 'SUCCESS':
                $results = array(
                    'msg' => 'Contact deleted.',
                    'key' => 'SUCCESS',
                    'type' => 'success'
                );
                break;

            default:
            case 'EXCEPTION':
                $results = array(
                    'msg' => 'Cannot delete contact due to an exceptions error.',
                    'key' => 'EXCEPTION',
                    'type' => 'error'
                );
                break;
        }
    }
} // }}}

else if($op == 'contacts/get' || $op == 'contact/get')
{ // {{{
    $results = get_data('addressbook_contacts');
} // }}}

else if($op == 'contacts/import' || $op == 'contact/import') 
{ // {{{
    try {
        $source = @$_REQUEST['source'];
        $email = @$_REQUEST['email'];
        $password = @$_REQUEST['password'];
        $errors = array();

        if(empty($source)) {
            $errors[] = array(
                'name' => 'source',
                'msg' => 'Source is required.'
            );
        }

        if(empty($email)) {
            $errors[] = array(
                'name' => 'email',
                'msg' => 'Email is required.'
            );
        }

        if(empty($password)) {
            $errors[] = array(
                'name' => 'password',
                'msg' => 'Password is reuired.'
            );
        }

        if(!empty($errors)) throw new Exception('FORM_ERRORS');

        if($source == 'gmail') {
            // Get authorization key
            $ch = curl_init();

            $params = array(
                'accountType' => 'HOSTED_OR_GOOGLE',
                'Email' => $email,
                'Passwd' => $password,
                'service' => 'cp',
                'source' => 'twilio-addressBookVBX-1'
            );

            curl_setopt_array($ch, array(
                CURLOPT_HEADER => FALSE,
                CURLOPT_URL => 'https://www.google.com/accounts/ClientLogin',
                CURLOPT_FOLLOWLOCATION => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POSTFIELDS => http_build_query($params)
            ));

            $results = curl_exec($ch);
            $ch_info = curl_getinfo($ch);
            $ch_error = curl_error($ch);

            if($ch_info['http_code'] == 200) {
                preg_match('/Auth=(.*)/', $results, $matches);
                $auth_key = $matches[1];
            } else {
                $errors[] = array(
                    'name' => 'password',
                    'msg' => 'Invalid Google credentials.'
                );
                throw new Exception('FORM_ERRORS');
            }
            curl_close($ch);

            // Get a list of contacts
            $ch = curl_init();

            curl_setopt_array($ch, array(
                CURLOPT_URL => 'http://www.google.com/m8/feeds/contacts/'.$email.'/full?alt=json&max-results=1000',
                CURLOPT_HEADER => FALSE,
                CURLOPT_FOLLOWLOCATION => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array('Authorization: GoogleLogin auth='.$auth_key)
            ));

            $results = curl_exec($ch);
            $ch_info = curl_getinfo($ch);
            $ch_error = curl_error($ch);

            if($ch_info['http_code'] == 200) {
                $results = json_decode($results);
                foreach($results->feed->entry as $contact) {
                    $new_contact = array();

                    $name = $contact->title->{'$t'};
                    if(!empty($name)) {
                        $space = strrpos($name, ' ');
                        if($space === FALSE) {
                            $new_contact['first_name'] = trim($name);
                        } else {
                            $new_contact['first_name'] = trim(substr($name, 0, $space));
                            $new_contact['last_name'] = trim(substr($name, $space + 1));
                        }
                    }

                    $phone = $contact->{'gd$phoneNumber'}[0]->{'$t'};
                    if(!empty($phone)) $new_contact['phone'] = preg_replace('/[^0-9+]+/', '', $phone);

                    $email = $contact->{'gd$email'}[0]->address;
                    if(!empty($email)) $new_contact['email'] = $email;

                    $new_contact = (array) $new_contact;
                    $chk_contact = $CI->db->get_where('addressbook_contacts', array('email' => $new_contact['email']))->row();
                    if(!empty($chk_contact)) {
                        $new_contact['updated'] = gmdate('Y-m-d H:i:s');
                        $CI->db->update('addressbook_contacts', $new_contact, array('id' => $chk_contact->id));
                    } else {
                        $new_contact['created'] = gmdate('Y-m-d H:i:s');
                        $CI->db->insert('addressbook_contacts', $new_contact);
                    }
                }

                throw new Exception('SUCCESS');
            } 
        } else if($source == 'yahoo') {
        } else if($source == 'msn') {
        }

        throw new Exception('EXCEPTION');
    } catch(Exception $e) {
        switch($e->getMessage()) {
            case 'FORM_ERRORS':
                $results = array(
                    'msg' => 'Canoot import contacts due to form validation errors.',
                    'key' => 'FORM_ERRORS',
                    'type' => 'error',
                    'data' => array(
                        'errors' => $errors
                    )
                );
                break;

            case 'SUCCESS':
                $results = array(
                    'msg' => 'Contact imported',
                    'key' => 'SUCCESS',
                    'type' => 'success'
                );
                break;

            default:
            case 'EXCEPTION':
                $results = array(
                    'msg' => 'Cannot import contacts due to an exceptions error.',
                    'key' => 'EXCEPTION',
                    'type' => 'error'
                );
                break;
        }
    }
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
{ // {{{
    try {
        $contact_id = $_REQUEST['id'];
        $name = @$_REQUEST['name'];
        $title = @$_REQUEST['title'];
        $company = @$_REQUEST['company'];
        $phone = @$_REQUEST['phone'];
        $email = @$_REQUEST['email'];

        $first_name = trim(substr($name, 0, strrpos($name, ' '))); 
        $last_name = trim(substr($name, strrpos($name, ' ') + 1)); 

        $update_contact = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'title' => $title,
            'company' => $company,
            'phone' => preg_replace('/[^0-9+]+/', '', $phone),
            'email' => $email,
            'updated' => date('Y-m-d H:i:s')
        );

        if($CI->db->update('addressbook_contacts', $update_contact, array('id' => $contact_id))) {
            throw new Exception('SUCCESS');
        } else {
            throw new Exception('DB_ERROR');
        }

        throw new Exception('EXCEPTION');
    } catch(Exception $e) {
        switch($e->getMessage()) {
            case 'DB_ERROR':
                break;

            case 'SUCCESS':
                $results = array(
                    'msg' => 'Contact updated.',
                    'key' => 'SUCCESS',
                    'type' => 'success'
                );
                break;

            default:
            case 'EXCEPTION':
                $results = array(
                    'msg' => 'Cannout update contact due to an exception error.',
                    'key' => 'EXCEPTION',
                    'type' => 'error'
                );
                break;
        }
    }
} // }}}

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
    <JSON_DATA><?php echo @$results ? json_encode($results) : '' ?></JSON_DATA>
<?php else: ?>
<?php
$sqls = file_get_contents($plugin['plugin_path'].'/applets/addressbook/db.sql');
$sqls = explode(';', $sqls);
foreach($sqls as $sql) {
    if(trim($sql) != '') $CI->db->query($sql);
}
?>
<style>
table { width:100%; }
table.datatable { margin-bottom:2px; }
div.dataTables_paginate { text-align:right; }
div.dataTables_info { float:left; line-height:20px; }
div.dataTables_paginate.paging_two_button div[title="Previous"] { line-height:20px; height:20px; width:20px; margin-left:2px; background:url(<?php echo $plugin_url ?>/assets/img/arrows_prev.png); } 
div.dataTables_paginate.paging_two_button div[title="Next"] { line-height:20px; height:20px; width:20px; margin-left:2px; background:url(<?php echo $plugin_url ?>/assets/img/arrows_next.png); } 
div.dataTables_paginate.paging_two_button div[title] { display:inline-block; }
div.dataTables_paginate.paging_two_button div[title]:hover { cursor:pointer; }
div.dataTables_paginate.paging_full_numbers span.paginate_button, 
    div.dataTables_paginate.paging_full_numbers span.paginate_active { display:inline-block; line-height:20px; margin-left:2px; text-align:center; background-color:#ccc; padding-left:5px; padding-right:5px; }
div.dataTables_paginate.paging_full_numbers span.paginate_button:hover, 
    div.dataTables_paginate.paging_full_numbers span.paginate_active:hover { cursor:pointer; background-color:#333; color:white; }
div.dataTables_paginate.paging_full_numbers span.paginate_active { color:white; background-color:#333; }
div.dataTables_processing { position:absolute; top:10px; right:40px; }
div.dataTables_length { float:left; }
div.dataTables_filter { text-align:right; }

input[type="button"].edit_inactive { visibility:hidden; }
input[type="button"].edit_active { visibility:visible; }}
input[type="text"].edit_active { border:0px; margin:0px; margin-bottom:2px; padding:1px; }
input[type="text"].edit_inactive { background-color:inherit; border:0px; margin:0px; margin-bottom:2px; padding:1px; }

div.data { display:none; }

div.section { border:1px solid gray; margin:10px; padding:10px; position:relative; }
div.section > h3 { margin-top:0px; }
div.section > input.new_btn { float:right; }
div.side.right { float:right; width:250px; }
div.side div.section:first-child { margin-top:0px; }

iframe.upload_iframe { background-color:white; border:1px solid gray; width:470px; height:23px; overflow:hidden; }

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
#browse_contacts div.profile_img { border:1px solid gray; width:50px; height:50px; line-height:50px; text-align:center; overflow:hidden; }
#browse_contacts div.profile_img > * { vertical-align:middle; }
#browse_contacts div.profile_img > img { width:50px; }
#browse_contacts div.profile_img input[value="Chng"] { position:absolute; left:45px; }
#browse_contacts tr input[name="name"] { width:95%; }
#browse_contacts tr input[name="company"] { display:block; font-size:10px; width:95%; }
#browse_contacts tr input[name="title"] { display:block; font-size:10px; width:95%; }
#browse_contacts tr.edit_active { background-color:#C2DBEF; }
#browse_contacts ul.letter_filter li { padding:2px; display:block; text-align:center; }
#browse_contacts ul.letter_filter li.selected { background-color:#C2DBEF; }
#browse_contacts ul.letter_filter li:hover { background-color:#ccc; cursor:pointer; }
#browse_contacts input.import_btn { float:right; margin-right:5px; }
#browse_contacts input.new_contact_btn { float:right; }

#import_contacts div.import_source a { border:3px solid transparent; display:inline-block;margin-right:20px; height:50px; padding:3px; vertical-align:middle; }
#import_contacts div.import_source a:hover { border-color:#C2DBEF; cursor:pointer; }
#import_contacts div.import_source a.selected { border-color:#C2DBEF; cursor:pointer; }
#import_contacts div.import_source a img { width:150px; vertical-align:middle; }
#import_contacts div.import_form { margin-top:20px; }

#list_of_groups th { padding:0px; visibility:hidden; }
#list_of_groups input.new_group_btn { float:right; }

#list_of_tags th { padding:0px; visibility:hidden; }
#list_of_tags input.new_tag_btn { float:right; }

#recent_contacts { margin-top:0px; }
#recent_contacts th { padding:0px; visibility:hidden; }
#recent_contacts div.profile_img { border:1px solid gray; width:50px; height:50px; text-align:center; overflow:hidden; line-height:50px; }
#recent_contact div.profile_img > * { vertical-align:middle; }
#recent_contact div.profile_img > img { width:50px; }
#recent_contacts tr span.company { display:block; font-size:10px;  }
#recent_contacts tr span.title { display:block; font-size:10px; }
#recent_contacts tr span.created { font-size:10px; }

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
                <div id="list_of_groups" class="section" style="display:none;">
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

                <div id="list_of_tags" class="section" style="display:none;">
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

                    <div id="import_contacts" class="dialog" title="Import Contacts" style="display:none;">
                        <div class="import_source">
                            <p>Currently only the Address Book can only import contacts from Google.</p>

                            <a class="gmail selected"><img src="http://www.google.com/intl/en_ALL/images/srpr/logo1w.png" /></a>
                            <a class="yahoo disabled" style="opacity:.2"><img src="http://l.yimg.com/a/i/ww/met/yahoo_logo_us_061509.png" /></a>
                            <a class="msn disabled" style="opacity:.2"><img src="http://col.stb.s-msn.com/i/BA/F7AFD6FD9371ACDFE1873AA174F5E.png" /></a>
                        </div>
                        <input name="source" type="hidden" value="gmail" />

                        <div class="import_form">
                            <fieldset class="vbx-input-container">
                                <label class="field-label">
                                    Email
                                    <input name="email" class="medium" type="text" />
                                </label>
                            </fieldset>

                            <fieldset class="vbx-input-container" style="margin-top:10px;">
                                <label class="field-label">
                                    Password
                                    <input name="password" class="medium" type="password" />
                                </label>
                            </fieldset>
                        </div>
                    </div>

                    <div id="new_contact_form_template" style="display:none;">
                        <table>
                            <tr>
                                <td>
                                    <div class="profile_img" style="line-height:50px; text-align:center;">
                                        <input type="button" value="Chng" style="vertical-align:middle;" />
                                    </div>
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
                                    <input name="email" type="text" placeholder="Email" />

                                    <div style="text-align:right; margin-top:10px;">
                                        <input class="cancel_btn" type="button" value="Cancel" />
                                        <input class="save_btn" type="button" value="Save" />
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="display:table; width:100%;">
                        <div style="display:table-cell; padding-right:10px;">
                            <ul class="letter_filter" style="width:20px; display:block;">
                                <li class="selected">All</li>
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
var plugin_url = '<?php echo $plugin_url ?>';
var user_numbers = <?php echo json_encode($user_numbers) ?>;
</script>

<?php OpenVBX::addJS('assets/js/twilio/datatables/jquery.dataTables.min.js'); ?>
<?php OpenVBX::addJS('assets/js/twilio/twilio.js'); ?>
<?php OpenVBX::addJS('assets/js/twilio/jquery.tw_table.js'); ?>

<?php OpenVBX::addJS('applets/addressbook/js/index_page.js'); ?>
<?php OpenVBX::addJS('applets/addressbook/js/index_init.js'); ?>

<?php endif; ?>
