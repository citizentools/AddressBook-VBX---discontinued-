var index_page = {
    browse_contacts_table: null,
    recent_contacts_table: null,
    groups_table: null,
    tags_table: null,

    initialize: function() {
        var that = index_page;

        that.browse_contacts_table = $('#browse_contacts table.datatable').tw_table(
            // {{{
            base_url + 'p/addressbook?op=contacts/get',
            { onServerData: onServerCall, sort_by:[[1, 'asc']] },
            {
                aoColumns: [
                    { sName:'profile_img', sWidth:'50px', fnRender:function(oObj) { return '<div class="profile_img"></div>'; } },
                    { sName:'first_name', fnRender:function(oObj) { 
                        return '<input name="name" class="edit_inactive" type="text" value="' + oObj.aData[1] + ' ' + oObj.aData[4] + '" readonly="readonly" />' +
                        '<input name="title" class="edit_inactive" type="text" value="' + oObj.aData[6] + '" readonly="readonly" />' +
                        '<input name="company" class="edit_inactive" type="text" value="' + oObj.aData[7] + '" readonly="readonly" />';
                    }},
                    { sName:'phone', fnRender:function(oObj) {
                        var phone = oObj.aData[2];
                        var parsed = phone.match(/([0-9]{3})([0-9]{3})([0-9]{4})/);
                        if(parsed) var text_phone  = '(' + parsed[1] + ') ' + parsed[2] + '-' + parsed[3];
                        else var text_phone = phone;

                        var html = '<input name="phone" class="edit_inactive" type="text" value="' + text_phone + '" readonly="readonly" />'; 

                        if(phone.trim() != '') 
                            html = html + '<br />' +
                                '<input class="call_' + phone + '_btn" type="button" value="Call" /> ' +
                                '<input class="sms_' + phone + '_btn" type="button" value="SMS" />'; 

                        return html;
                    }},
                    { sName:'email', fnRender:function(oObj) {
                        return '<input name="email" class="edit_inactive" type="text" value="' + oObj.aData[3] + '" readonly="readonly" />' +
                        '<div class="data">' +
                            '<span class="id">' + oObj.aData[5] + '</span>' +
                        '</div>';
                    }},
                    { sName:'last_name', bVisible:false },
                    { sName:'id', bVisible:false },
                    { sName:'title', bVisible:false },
                    { sName:'company', bVisible:false },
                    { sName:'street', bVisible:false },
                    { sName:'city', bVisible:false },
                    { sName:'state', bVisible:false },
                    { sName:'zip', bVisible:false },
                    { sName:'country', bVisible:false },
                    { sName:'website', bVisible:false },
                    { sName:'bday', bVisible:false },
                    { sName:'notes', bVisible:false },
                    { sName:'private', bVisible:false },
                    { sName:'data', bVisible:false },
                    { sName:'created', bVisible:false },
                    { sName:'updated', bVisible:false },
                    { sName:'user_id', bVisible:false }
                ]
            }
        ); // }}}

        that.recent_contacts_table = $('#recent_contacts table.datatable').tw_table(
            // {{{
            base_url + 'p/addressbook?op=contacts/get',
            { onServerData:onServerCall, limit:5, sort_by:[[0, 'desc']] },
            {
                aoColumns: [
                    { sName:'profile_img', sWidth:'50px', fnRender:function(oObj) { return '<div class="profile_img"></div>'; }},
                    { sName:'first_name', fnRender:function(oObj) { 
                        return '<span class="name">' + oObj.aData[1] + ' ' + oObj.aData[4] + '</span>' +
                        '<span class="company">' + oObj.aData[7] + '</span>' +
                        '<div class="data">' +
                            '<span class="id">' + oObj.aData[5] + '</span>' +
                        '</div>';
                    }},
                    { sName:'phone', bVisible:false },
                    { sName:'email', bVisible:false },
                    { sName:'last_name', bVisible:false },
                    { sName:'id', bVisible:false },
                    { sName:'title', bVisible:false },
                    { sName:'company', bVisible:false },
                    { sName:'street', bVisible:false },
                    { sName:'city', bVisible:false },
                    { sName:'state', bVisible:false },
                    { sName:'zip', bVisible:false },
                    { sName:'country', bVisible:false },
                    { sName:'website', bVisible:false },
                    { sName:'bday', bVisible:false },
                    { sName:'notes', bVisible:false },
                    { sName:'private', bVisible:false },
                    { sName:'data', bVisible:false },
                    { sName:'created', bVisible:false },
                    { sName:'updated', bVisible:false },
                    { sName:'user_id', bVisible:false }
                ],
                bFilter: false,
                bLengthChange: false,
                bInfo: false,
                bPaginate: false
            }
        ); // }}}

        that.groups_table = $('#list_of_groups table.datatable').tw_table(
            // {{{
            base_url + 'p/addressbook?op=groups/get',
            { onServerData:onServerCall },
            {
                aoColumns: [
                    { sName:'name', fnRender:function(oObj) {
                        return '<input name="name" class="edit_inactive" value="' + oObj.aData[0] + '" readonly="readonly" />' +
                        '<div class="data">' +
                            '<span class="id">' + oObj.aData[1] + '</span>' +
                        '</div>';
                    }},
                    { sName:'id', bVisible:false },
                    { sName:'count', bVisible:false },
                    { sName:'color', bVisible:false },
                    { sName:'created', bVisible:false },
                    { sName:'user_id', bVisible:false }
                ],
                bFilter: false,
                bLengthChange: false,
                bInfo: false
            }
        ); // }}}

        that.tags_table = $('#list_of_tags table.datatable').tw_table(
            // {{{
            base_url + 'p/addressbook?op=tags/get',
            { onServerData:onServerCall  },
            {
                aoColumns: [
                    { sName:'name', fnRender:function(oObj) {
                        return '<input name="name" class="edit_inactive" value="' + oObj.aData[0] + '" readonly="readonly" />' +
                        '<div class="data">' +
                            '<span class="id">' + oObj.aData[1] + '</span>' +
                        '</div>';
                    }},
                    { sName:'id', bVisible:false },
                    { sName:'count', bVisible:false },
                    { sName:'created', bVisible:false },
                    { sName:'user_id', bVisible:false }
                ],
                bFilter: false,
                bLengthChange: false,
                bInfo: false
            }
        ); // }}}

        that.render();
    },

    submit_new_contact: function() 
    { // {{{
        var that = index_page;
        var new_contact_el = $('#browse_contacts tr.new_contact_form');
        var name = new_contact_el.find('input[name="name"]').val();
        var title = new_contact_el.find('input[name="title"]').val();
        var company = new_contact_el.find('input[name="company"]').val();
        var phone = new_contact_el.find('input[name="phone"]').val();
        var email = new_contact_el.find('input[name="email"]').val();
        var errors = [];

        new_contact_el.find('span.err').remove();

        if(name.trim() == '') {
            errors.push({
                name:'name',
                msg:'Name is required.'
            });
        }

        if(errors.length == 0) {
            $.post(
                base_url + 'p/addressbook?op=contacts/new',
                { name:name, title:title, company:company, phone:phone, email:email },
                function(resp) {
                    try {
                        resp = resp.match(/JSON_DATA\>(.*)\<\/JSON_DATA/)[1];
                        json = eval("(" + resp + ")");
                        if(json.key == 'SUCCESS') {
                            new_contact_el.remove();
                            that.browse_contacts_table.engine_obj.fnDraw();
                        }
                    } catch(e) {}
                },
                'text'
            );
        } else {
            $.each(errors, function(k, v) {
                var err_el = $('<span></span>').addClass('err').html(v.msg);
                new_contact_el.find('input[name="' + v.name + '"]').after(err_el);
            });
        }
    }, // }}}

    render: function(name) {
        var that = index_page;

        switch(name) {
            case 'browse_contacts':
                $('#browse_contacts tbody tr').live('click', function(e) {
                    var target = $(e.target);
                    var from = $('div.call-dialog select[name="callerid"] option')[0].value;

                    if(target.attr('class').match(/call_[0-9+]+_btn/)) {
                        var phone = target.attr('class').match(/call_([0-9+]+)_btn/)[1];

                        if(user_numbers && user_numbers[0]) {
                            var callerid = user_numbers[0].value;
                        } else {
                            var callerid = from;
                        }

                        $.post(
                            base_url + '/messages/call', 
                            {
                                callerid:callerid,
                                from:from,
                                to:phone
                            },
                            function(resp) {
                                console.log(resp);
                            },
                            'text'
                        );
                    } else if(target.attr('class').match(/sms_[0-9]+_btn/)) {
                        var phone = target.attr('class').match(/sms_([0-9+]+)_btn/)[1];
                        alert('SMS ' + phone);
                    } else {
                        $(this).css('background-color', 'yellow');
                    }
                });

                $('#browse_contacts input.new_contact_btn').click(function() {
                    var new_contact_el = $('#new_contact_form_template tr').clone();
                    var table_el = $('#browse_contacts table.datatable tbody');
                    table_el.find('tr.new_contact_form').remove();
                    new_contact_el.addClass('new_contact_form').prependTo(table_el);

                    new_contact_el.find('input.cancel_btn').click(function() {
                        new_contact_el.remove();
                    });

                    new_contact_el.find('input.save_btn').click(function() {
                        that.submit_new_contact();
                    });
                });
                break;

            case 'list_of_groups':
                break;

            case 'list_of_tags':
                break;

            case undefined:
                that.render('browse_contacts');
                break;
        }
    }
}
