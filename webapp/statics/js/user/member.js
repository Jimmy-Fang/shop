define(function(require, exports) {
    var api = G.user_list,
        select = '',//用户组下拉列表
        groupData ='[{"name":"管理员","role":1},{"name":"审计员","role":2}]',
        userData;
        groupData = JSON.parse(groupData);
    var init = function(){
        $.ajax({
            url:api,
            dataType:'json',
            async:false,
            success:function(data){
                if (!data.code) {
                    core.tips.tips(LNG.data_error,'info');
                    return;
                }
                userData= data.data;
                _init_view();
            },
            error:function(){
                return false;
            }
        });
    };

    var _init_view = function(){
        var html="<tr class='title'>"+
                "<td width=''>"+LNG.login_username+"</td>"+
                "<td width='20%'>"+LNG.nickname+"</td>"+
                "<td width='15%'>"+LNG.group_name+"</td>"+
                "<td width='30%'>"+LNG.action+"</td>"+
                "</tr>";
        for (var i in userData){
            html += get_html(userData[i]['username'],userData[i]['roleid'],userData[i]['nickname']);
        }
        select = '';
        for (var i in groupData) {
            select += "<option value='"+groupData[i]['role']+"'>"+groupData[i]['name']+"</option>";
        };
        $('.member table#list').html(html);
    }

    var get_html = function(name,role,nickname){
        if (name == undefined) name='';
        if (role == undefined) role='';
        var group_name;
        for (var i in groupData) {
            if(groupData[i]['role'] ==role){
                group_name = groupData[i]['name'];
            }
        }
        if(group_name == ''){
            group_name = LNG.group_not_exists;
            role = '';
        }
        var action = "<a href='javascript:void(0)' class='button edit'>"+LNG.button_edit+"</a>    "+
                     "<a href='javascript:void(0)' class='button del'>"+LNG.button_del+"</a>";
        if (name == 'admin') action = LNG.default_group_can_not_do;
        var html="<tr name='"+name+"' role='"+role+"' nickname='"+nickname+"'>"+
        "   <td>"+name+"</td>"+
        "   <td>"+nickname+"</td>"+
        "   <td><a href='javascript:void(0)' class='edit_role'>"+group_name+"</a></td>"+
        "   <td>"+ action +"</td>"+
        "</tr>";
        return html;
    }

    var get_edit = function(type,name,role,nickname){
        if (name == undefined) name='';
        if (role == undefined) role='';
        if (nickname == undefined) nickname='';
        var action = '',hname='',tpwd="";
        if (type == 'add'){
            action = {b1:"add_save' action='"+ G. user_add+"'>"+LNG.member_add,b2:"add_exit'>"+LNG.button_cancle};
            hname = "<input type='text' id='name' value='"+name+"' required/>";
        }else{
            action = {b1:"edit_save'  action='"+ G. user_upd+"'>"+LNG.button_save_edit,b2:"edit_exit'>"+LNG.button_cancle};
            hname = name;
            tpwd = "placeholder='为空不修改' title='为空不修改密码'";
        }
        var html=   
        "<tr name='"+name+"' role='"+role+"' nickname='"+nickname+"'>"+
        "   <td class='member'>"+LNG.login_username+":"+hname+
        "       <span>"+LNG.login_password+":</span><input type='text' id='password'  "+tpwd+"/></td>"+
        "   <td><span>"+LNG.nickname+":</span><input type='text' id='nickname' value='"+nickname+"' required/></td>"+
        "   <td><select id='role' value='"+role+"'>"+select+"</select></td>"+
        "   <td>"+
        "       <a href='javascript:void(0)' class='button " + action.b1 + "</a>"+
        "       <a href='javascript:void(0)' class='button " + action.b2 + "</a>"+
        "   </td>"+
        "</tr>";
        return html;
    }

    //添加用户，dom操作。
    var add = function(){
        var html = get_edit('add');
        $(html).insertAfter(".member table#list tr:last");
    };
    var add_exit = function(){
        var obj=$(this).parent().parent();//定位到tr
        $(obj).detach();
    };
    //添加用户，保存动作
    var add_save = function(){
        var obj=$(this).parent().parent();//定位到tr
        var name=$(obj).find('#name').val();
        var password=$(obj).find('#password').val();
        var role=$(obj).find('#role').val();
        var nickname=$(obj).find('#nickname').val();
        
        if (name=='' || password =='' || role==''|| nickname==''){
            tips(LNG.not_null,'warning');
            return false;
        }
        if (escape(name).indexOf("%u")>=0){
            tips(LNG.login_username +''+ LNG.not_chinease,'warning');
            return false;
        }
        if (escape(nickname).indexOf("%u")<0){
            tips(LNG.nickname +''+ LNG.be_chinease,'warning');
            return false;
        }
        var form = $("#J_ajaxForm");
        form.ajaxSubmit({
            url: $(this).attr("action"), 
            dataType: 'json',
            data: {
                username: name,
                password: password,
                roleid: role,
                nickname: nickname
            },
            success: function (data, statusText, xhr, $form) {
                if (data.state === 'success') {
                    core.tips.tips(data.info,'success');
                     if (data.referer) {
                            window.location.href = data.referer;
                        } else {
                             reloadPage(window);
                        }
                } else if (data.state === 'fail') {
                    core.tips.tips(data.info,'error');
                    }
                }
            });
    };

    //编辑用户
    var edit = function(){
        var obj=$(this).parent().parent();//定位到tr
        var html = get_edit('edit',$(obj).attr('name'),$(obj).attr('role'),$(obj).attr('nickname'));
        
        $('.info').html(LNG.password_null_not_update).fadeIn(100);
        $(html).insertAfter(obj);

        var set_select = $(obj).attr('role');
        $(obj).next().find('option[value='+set_select+']').attr('selected','true');
        $(obj).detach();
    };
    //取消编辑
    var edit_exit = function(){
        var obj=$(this).parent().parent();//定位到tr
        var html = get_html($(obj).attr('name'),$(obj).attr('role'),$(obj).attr('nickname'));

        $(html).insertAfter(obj);
        $(obj).detach();
        $('.info').fadeOut(100);
    };
    //编辑保存用户
    var edit_save = function(){
        var obj=$(this).parent().parent();//定位到tr
        var name=$(obj).attr('name');
        var role_to=$(obj).find('#role').val();
        var nickname=$(obj).find('#nickname').val();
        var password_to=$(obj).find('#password').val();
        if (role_to ==''){
            tips(LNG.not_null,'error');
            return false;
        }
        if (escape(nickname).indexOf("%u")<0){
            tips(LNG.nickname +''+ LNG.be_chinease,'warning');
            return false;
        }
        $.ajax({
            url:$(this).attr("action"),
            dataType:'json',
            type:'post',
            data: {
                username: name,
                password: password_to,
                roleid: role_to,
                nickname: nickname
            },
            success:function(data){
               if (data.state === 'success') {
                    core.tips.tips(data.info,'success');
                     if (data.referer) {
                            window.location.href = data.referer;
                        } else {
                             reloadPage(window);
                        }
                } else if (data.state === 'fail') {
                    core.tips.tips(data.info,'error');
                    }
            }
        }); 
    };
    //删除用户
    var del = function(){
        var obj=$(this).parent().parent();//定位到tr
        var name=$(obj).attr('name');
        art.dialog({
            fixed: true,//不跟随页面滚动
            icon:'question',
            drag: true,//拖曳
            title:LNG.warning,
            content: LNG.if_remove+":"+name+'<br/>'+LNG.member_remove_tips,
            ok:function() {
                $.ajax({
                    url:G.user_del,
                    dataType:'json',
                    type:'post',
                    data: {
                        username: name,
                    },
                    async:false,
                    success:function(data){
                        if (data.state === 'success') {
                            core.tips.tips(data.info,'success');
                             $(obj).detach();
                        } else if (data.state === 'fail') {
                            core.tips.tips(data.info,'error');
                            }
                    }
                }); 
            },
            okVal:'OK',
            cancle:true
        });
    };

    var bindEvent = function(){
        $('.member a.add').live('click',add);
        $('.member a.add_exit').live('click',add_exit);
        $('.member a.add_save').live('click',add_save);
        $('.member a.edit').live('click',edit);
        $('.member a.edit_save').live('click',edit_save);
        $('.member a.edit_exit').live('click',edit_exit);
        $('.member a.del').live('click',del);   
        $('.member a.edit_role').live('click',function(){
            var role = $(this).parent().parent().attr('role');
            if (role == ''){
                tips(LNG.group_already_remove,false);return;
            }
        });         
    };

    return{
        init:init,
        bindEvent:bindEvent
    }
});