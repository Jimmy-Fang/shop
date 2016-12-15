define(function(require, exports) {
	var setting; //url后缀参数
	var urls;
	//主动修改	
	var setTheme = function(thistheme) {
		core.setSkin(thistheme, 'app_setting.css');
		FrameCall.father('ui.setTheme', '"' + thistheme + '"');
	};
	//被动修改
	var setThemeSelf = function(thistheme) {
		core.setSkin(thistheme, 'app_setting.css');
	};
	var gotoPage = function(urls, page) {
		if (page == '' || page == undefined) page = 'user';
		setting = page;

		$('.selected').removeClass('selected');
		$('ul.setting li#' + page).addClass('selected');
		window.location.href = '#' + page;

		$.ajax({
			url: urls,
			beforeSend: function(data) {
				$('.main').html("<img src='" + G.static_path + "images/loading.gif'/>");
			},
			success: function(data) {
				$('.main').css('display', 'none');
				$('.main').html(data);
				$('.main').fadeIn('fast');
				if (page=='member') Member.init();//用户管理
				setting = page;
			}
		});
	};

	var bindEvent = function() {
		setting = location.hash.split("#", 2)[1];
		var info = $('ul.setting li').attr("data-url");
		gotoPage(info, setting);
		$('ul.setting li').hover(function() {
			$(this).addClass('hover');
		},
		function() {
			$(this).toggleClass('hover');
		}).click(function() {
			setting = $(this).attr('id');
			urls = $(this).attr("data-url");
			gotoPage(urls, setting);
			//gotoPage(setting);
		});;

		//选择事件绑定
		$('.box .list').live('hover',
		function() {
			$(this).addClass('listhover');
		},
		function() {
			$(this).toggleClass('listhover');
		}).live('click',
		function() {
			var _self = $(this),
			_parent = _self.parent();
			type = _parent.attr('data-type'); //设置参数
			value = _self.attr('data-value');
			_parent.find('.this').removeClass('this');
			_self.addClass('this');

			//对应相应动作
			switch (type) {
			case 'wall':
				var image = G.static_path + 'images/wall_page/' + value + '.jpg';
				FrameCall.father('ui.setWall', '"' + image + '"');
				break;
			case 'theme':
				setTheme(value);
				break;
			default:
				break;
			}
			//保存到服务器
			var geturl = 'index.php?setting/set&k=' + type + '&v=' + value;
			$.ajax({
				url: geturl,
				type: 'json',
				success: function(data) {
					tips(data);
				}
			});
		});
	};

	// 设置子内容动作处理
	var tools = function(action) {
		var page = $('.selected').attr('id');
		switch (page) {
		case 'user':
			//修改密码
			var password_now = $('#password_now').val();
			var password_new = $('#password_new').val();
			if (password_new == '' || password_now == '') {
				tips(LNG.verify_password_must, 'error');
				break;
			}
			$.ajax({
				url: $("#upduser").attr("data-url"),
				data: {
					pold: password_now,
					pnew: password_new
				},
				dataType: 'json',
				success: function(data) {
                                                                                    if(data.status==0){
                                                                                        tips(data.info,'error');
                                                                                    }else{
                                                                                        tips(data.info);
                                                                                         window.top.location.href = G.login_out;
                                                                                    }
				}
			});
			break;
		case 'wall':
			var image = $('#wall_url').val();
			if (image == "") {
				tips(LNG.picture_can_not_null, 'error');
				break;
			}
			FrameCall.father('ui.setWall', '"' + image + '"');
			$('.box').find('.this').removeClass('this');
			var geturl = 'index.php?setting/set&k=wall&v=' + urlEncode(image);
			$.ajax({
				url: geturl,
				type: 'json',
				success: function(data) {
					tips(data);
				}
			});
		default:
			break;
		}
	};
	// 对外提供的函数
	return {
		init:
		bindEvent,
		setGoto: gotoPage,
		tools: tools,
		setThemeSelf: setThemeSelf,
		setTheme: setTheme
	};
});