define(function(require, exports, module) {
     Global = {
        frameLeftWidth:200,         // 左边树目录宽度
        treeSpaceWide:10,           // 树目录层级相差宽度
        topbar_height:40,           // 头部高度
        
        fileListSelect:'',          // 选s择的文件
        fileListSelectNum:'',       // 选中的文件数。
        isIE:!-[1,],                // 是否ie
        isDragSelect:false,         // 是否框选
        historyStatus:{back:1,next:0}   // 是否可以前进后退操作状态
    };
    require('jquery-lib');
    require('util');
    require('ajaxForm');
    core = require('common/core');     //公共方法及工具封装
    Member  = require('./member');
    Member.bindEvent();
    Member.init();
});