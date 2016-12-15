define(function(require, exports) {
    //双击或者选中后enter 打开 执行事件
    //或者打开指定文件
    var _open = function(path, ext) {
        if (path == undefined)
            return;
        if (ext == undefined)
            ext = core.pathExt(path);//没有扩展名则自动解析
        ext = ext.toLowerCase();
        if (ext == 1) {
            core.openApp(path);
            return;
        }
        if (ext == 'html' || ext == 'htm') {
            var url = core.path2url(path);
            _openWindow(url, core.ico('html'), core.pathThis(path));
            return;
        }
        _openEditor(path);//代码文件，编辑
    }
    //新的页面作为地址打开。鼠标右键，IE下打开
    var _openIE = function(path) {
        if (path == undefined)
            return;
        var url = core.path2url(path);
        window.open(url);
    };
    var _openWindow = function(url, ico, title, name) {
        if (!url)
            return;
        if (name == undefined)
            name = 'openWindow' + UUID();

        var html = "<iframe frameborder='0' name='Open" + name + "' src='" + url +
                "' style='width:100%;height:100%;border:0;'></iframe>";
        // if(url.substr(url.length-4).toLowerCase() == '.swf'){
        // 	html = core.createFlash(url,'',name);
        // }
        art.dialog.through({
            id: name,
            title: title,
            ico: ico,
            width: '70%',
            height: '65%',
            padding: 0,
            content: html,
            resize: true
        });
    };
    //对外接口
    return{
        open: _open,
        openIE: _openIE
    }
});
