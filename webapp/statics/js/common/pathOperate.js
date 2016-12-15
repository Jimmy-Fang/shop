define(function(require, exports) {
    var tpl = require('../tpl/fileinfo');
    var path_not_allow = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];//win文件名命不允许的字符
    //检测文件名是否合法，根据操作系统，规则不一样
    //win 不允许  / \ : * ? " < > |，lin* 不允许 ‘、’
    var _pathAllow = function(path) {
        //字符串中检验是否出现某些字符，check=['-','=']
        var _strHasChar = function(str, check) {
            var len = check.length;
            var reg = "";
            for (var i = 0; i < len; i++) {
                if (str.indexOf(check[i]) > 0)
                    return true;
            }
            return false;
        };
        if (_strHasChar(path, path_not_allow)) {
            core.tips.tips(LNG.path_not_allow + ':/ \ : * ? " < > |', false);
            return false;
        }
        return true;
    };
    //组装数据
    var _json = function(json) {
        var send = 'list=[';
        for (var i in json) {
            send += '{"type":"' + json[i].type + '","path":"' + urlEncode2(json[i].path) + '"}';
            if (i != json.length - 1)
                send += ',';
        }
        ;
        return send + ']';
    }
    //获取数据
    var _app_param = function(dom) {
        var param = {};
        param.type = dom.find("input[type=radio]:checked").val();
        param.content = dom.find("textarea").val();
        param.group = dom.find("[name=group]").val();
        dom.find('input[type=text]').each(function() {
            var name = $(this).attr('name');
            param[name] = $(this).val();
        });
        dom.find('input[type=checkbox]').each(function() {
            var name = $(this).attr('name');
            param[name] = $(this).attr('checked') == 'checked' ? 1 : 0;
        });
        return param;
    }
    var _bindAppEvent = function(dom) {
        dom.find('.type input').change(function() {
            var val = $(this).attr('apptype');
            dom.find('[data-type]').addClass('hidden');
            dom.find('[data-type=' + val + ']').removeClass('hidden');
        });
    }
    var appList = function() {
        core.appStore();
    };

    return{
        appList: appList
    }
});