//重新刷新页面，使用location.reload()有可能导致重新提交
function reloadPage(win) {
    var location = win.location;
    location.href = location.pathname + location.search;
}

//页面跳转
function redirect(url) {
    location.href = url;
}

//判断ip地址的合法性
function checkIP(value){
   var val = /([0-9]{1,3}\.{1}){3}[0-9]{1,3}/;
    var vald = val.exec(value);
    if (vald == null) {
        return false;
    }
    if (vald != '') {
        if (vald[0] != value) {
            return false;
        }
    }
}

/**
 * 判断名称合法性
 */
function checkName(name){
    var rg = /^[a-zA-Z][a-zA-Z0-9-_]*$/
    if(rg.test(name)){
        return true;
    }else{
        return false;
    }
}

/**
 * 校验时间HH:mm
 * @param {type} time
 * @returns {undefined}
 */
function checkTime(time){
    var rg = /^([0-2][0-9]):([0-5][0-9])$/;
    if(rg.test(time)){
        return true;
    }else{
        return false;
    }
}

/**
 * 校验用逗号分割的日期
 * @returns {undefined}
 */
function checkDDate($value){
    var rg = /^(\d+,?)+$/;
    if(rg.test($value)){
        return true;
    }else{
        return false;
    }
}

/**
 * 判断电话号码合法性
 */
function checkTel(name){
    var reg0=/^13\d{5,9}$/; //130--139。至少7位
    var reg1=/^15\d{5,9}$/; //15至少7位
    var reg2=/^18\d{5,9}$/; //18
    var my=false;
   if (reg0.test(name))
       my=true;
   if (reg1.test(name))
       my=true;
   if (reg2.test(name))
       my=true;
   
   return my;
}

/**
 * 判断名称合法性
 */
function checkMail(name){
    var rg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
    if(rg.test(name)){
        return true;
    }else{
        return false;
    }
}

/**
 * 判断是否是数字
 */
function checkNumber(name){
    var rg = /^[1-9]+[0-9]*]*$/
    if(rg.test(name)){
        return true;
    }else{
        return false;
    }
}

/**
 * 判断是否是合法路径 /开头或者c:
 */
function checkPath(name){
	var rg = /^(\/|[A-Za-z]:)(\w+|\\|\/)+$/
    if(rg.test(name)){
    	if(name.indexOf("//") === -1){
    		return true;
    	}else{
    		return false;
    	}
    }else{
        return false;
    }
}

function checkWinPath(name){
	var rg = /^([A-Za-z]:)(\w+|\\|\/)+$/
    if(rg.test(name)){
    	if(name.indexOf("//") === -1){
    		return true;
    	}else{
    		return false;
    	}
    }else{
        return false;
    }
}
