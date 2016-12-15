define(function(require, exports) {
    return {
        filetype: {
            'music': ['mp3', 'wma', 'wav', 'mid', 'aac', 'ogg', 'oga', 'midi', 'ram', 'ac3', 'aif', 'aiff', 'm3a',
                'm4a', 'm4b', 'mka', 'mp1', 'mx3', 'mp2'],
            'movie': ['avi', 'flv', 'f4v', 'wmv', '3gp', 'rmvb', 'mp4', 'rm', 'rmvb', 'flv', 'mkv', 'wmv', 'asf', 'avi',
                'aiff', 'mp4', 'divx', 'dv', 'm4v', 'mov', 'mpeg', 'vob', 'mpg', 'mpv', 'ogm', 'ogv', 'qt'],
            'image': ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'ico', 'tif', 'tiff', 'dib', 'rle'],
            'code': ['html', 'htm', 'js', 'css', 'less', 'scss', 'sass', 'py', 'php', 'rb', 'erl', 'lua', 'pl', 'c', 'cpp'
                        , 'm', 'h', 'java', 'jsp', 'cs', 'asp', 'sql', 'as', 'go', 'lsp', 'yml', 'json', 'tpl', 'xml',
                'cmd', 'reg', 'bat', 'vbs', 'sh'],
            'doc': ['doc', 'docx', 'docm', 'xls', 'xlsx', 'xlsb', 'xlsm', 'ppt', 'pptx', 'pptm'],
            'text': ['txt', 'ini', 'inc', 'inf', 'conf', 'oexe', 'md', 'htaccess', 'csv', 'log', 'asc', 'tsv'],
            'bindary': ['pdf', 'bin', 'zip', 'swf', 'gzip', 'rar', 'arj', 'tar', 'gz', 'cab', 'tbz', 'tbz2', 'lzh', 'uue', 'bz2'
                        , 'ace', 'exe', 'so', 'dll', 'chm', 'rtf', 'odp', 'odt', 'pages', 'class', 'psd', 'ttf']
        },
        ico: function(type) {
            var path = G.static_path + 'images/file_16/';
            var arr = ['folder', 'file', 'edit', 'search', 'up', 'setting', 'appStore', 'error', 'info',
                'mp3', 'flv', 'pdf', 'doc', 'xls', 'ppt', 'html', 'swf'];
            var index = $.inArray(type, arr);
            if (index == -1) {
                return path + 'file.png';
            } else {
                return path + type + '.png';
            }
        },
        contextmenu: function(event) {
            rightMenu.hidden();
            var e = event || window.event;
            if (e && ($.nodeName(e.target, 'TEXTAREA') ||
                    $.nodeName(e.target, 'INPUT'))) {
                return true;
            }
            //return false;
        },
        //获取当前文件名
        pathThis: function(path) {
            path = path.replace(/\\/g, "/");
            var arr = path.split('/');
            var name = arr[arr.length - 1];
            if (name == '')
                name = arr[arr.length - 2];
            return name;
        },
        //获取文件父目录
        pathFather: function(path) {
            path = path.replace(/\\/g, "/");
            var index = path.lastIndexOf('/');
            return path.substr(0, index + 1);
        },
        //获取路径扩展名
        pathExt: function(path) {
            path = path.replace(/\\/g, "/");
            path = path.replace(/\/+/g, "/");
            var index = path.lastIndexOf('.');
            path = path.substr(index + 1);
            return path.toLowerCase();
        },
        //绝对路径转url路径
        path2url: function(path) {
            if (path.substr(0, 4) == 'http')
                return path;

            path = path.replace(/\\/g, "/");
            path = path.replace(/\/+/g, "/");
            path = path.replace(/\/\.*\//g, "/");

            //public path
            if (path.substring(0, G.public_path.length) == G.public_path) {
                return G.app_host + 'data/public/' + path.replace(G.public_path, '');
            }

            //user group
            if (G.is_root) {
                if (path.substring(0, G.web_root.length) == G.web_root) {//服务器路径下
                    return G.web_host + path.replace(G.web_root, '');
                }
                var host = G.basic_path.replace(G.web_root, '') + '/';
                host = G.web_host + host;
                return host + 'index.php?explorer/fileProxy&path=' + urlEncode(path);
            } else {
                return G.web_host + G.web_root + path;
            }
        },
        ajaxError: function(XMLHttpRequest, textStatus, errorThrown) {
            core.tips.close(LNG.system_error, false);
            var response = XMLHttpRequest.responseText;
            var error = '<div class="ajaxError">' + response + '</div>';
            var dialog = $.dialog.list['ajaxErrorDialog'];

            //已经退出
            if (response.substr(0, 17) == '<!--user login-->') {
                FrameCall.goRefresh();
                return;
            }

            if (dialog) {
                dialog.content(error);
            } else {
                $.dialog({
                    id: 'ajaxErrorDialog',
                    padding: 0,
                    fixed: true,
                    resize: true,
                    ico: core.ico('error'),
                    title: 'ajax error',
                    width: 960,
                    height: 580,
                    content: error
                });
            }
        },
        // setting 对话框
        setting: function(setting) {
            if (setting == undefined)
                setting = '';
            if (window.top.frames["Opensetting_mode"] == undefined) {
                $.dialog.open(setting, {
                    id: 'setting_mode',
                    fixed: true,
                    ico: core.ico('setting'),
                    resize: true,
                    title: LNG.setting,
                    width: 960,
                    height: 580
                });
            } else {
                $.dialog.list['setting_mode'].display(true);
                FrameCall.top('Opensetting_mode', 'Setting.setGoto', '"' + setting + '"');
            }
        },
        openApp: function(app) {
            if(app.open == 1){
                if (app.type == 1) {
                    var icon = app.icon;
                    if (app.icon.search(G.static_path) == -1 && app.icon.substring(0, 4) != 'http') {
                        icon = G.static_path + 'images/app/' + app.icon;
                    }
                    //高宽css px或者*%
                    if (typeof (app.width) != 'number'
                            && app.width.search('%') == -1) {
                        app.width = parseInt(app.width);
                    }
                    if (typeof (app.height) != 'number'
                            && app.height.search('%') == -1) {
                        app.height = parseInt(app.height);
                    }
                    $.dialog.open(app.content, {
                        title: app.name,
                        fixed: true,
                        ico: icon,
                        resize: parseInt(app.resize),
                        simple: parseInt(app.simple),
                        width: parseInt(app.width),
                        height: parseInt(app.height)
                    });
                } else {
                    var exec = app.content;
                    eval('{' + exec + '}');
                }
            }else{
                $.dialog({
                resize: false,
                fixed: true,
                ico: core.ico('info'),
                title: "没有访问权限",
                width: 400,
                height: 60,
                padding: 0,
                content: "<center style='padding:10px;font-size:24px;color:red;'>您没有访问权限，请联系授权！！</center>"
            });
            }
        },
        //编辑器全屏 编辑器调用父窗口全屏
        editorFull: function() {
            var $frame = $('iframe[name=OpenopenEditor]');
            $frame.toggleClass('frame_fullscreen');
        },
        language: function(lang) {
            Cookie.set('think_language', lang, 24 * 365);//365 day
            //保存到数据库
            $.ajax({
                url:G.uconfig_path+"?k=language&v="+lang
            });
            window.location.reload();
        },
        // tips 
        tips: {
            loading: function(msg) {
                Tips.loading(msg, 'info', Global.topbar_height);
            },
            close: function(msg, code) {
                if (typeof (msg) == 'object') {
                    Tips.close(msg.data, msg.code, Global.topbar_height);
                } else {
                    Tips.close(msg, code, Global.topbar_height);
                }
            },
            tips: function(msg, code) {
                if (typeof (msg) == 'object') {
                    Tips.tips(msg.data, msg.code, Global.topbar_height);
                } else {
                    Tips.tips(msg, code, Global.topbar_height);
                }
            }
        },
        file_size: function(size) {
            if (size == 0)
                return "0B";
            size = parseFloat(size);
            var unit = {
                'GB': 1073741824, // pow( 1024, 3)
                'MB': 1048576, // pow( 1024, 2)
                'KB': 1024, // pow( 1024, 1)
                'B ': 0			// pow( 1024, 0)
            };
            for (var key in unit) {
                if (size >= unit[key]) {
                    return (size / unit[key]).toFixed(1) + key;
                }
            }
            return '0B';
        }
    };
});