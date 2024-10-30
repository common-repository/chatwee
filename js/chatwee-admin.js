
jQuery(document).ready(function() {
	var userList = new UserList({
		"parentElementId": "user_list_wrapper"
	});
	userList.build();
	userList.reload();

	var userPicker = new UserPicker({
		"parentElementId": "user_picker_wrapper",
		"userList": userList
	});
	userPicker.build();

	
	var pageList = new PageList({
		"parentElementId": "page_list_wrapper"
	});
	pageList.build();
	pageList.reload();

	var pagePicker = new PagePicker({
		"parentElementId": "page_picker_wrapper",
		"pageList": pageList
	});
	pagePicker.build();
});

var adjustTabs = function(tabKey) {
	jQuery(".tab-switch").removeClass("nav-tab-active");
	jQuery(".tab-switch[data-tab-key='" + tabKey + "'").addClass("nav-tab-active");

	jQuery(".chatwee-options-section").hide();
	jQuery(".chatwee-options-section[data-tab-key='" + tabKey + "'").show();
	window.location.hash = tabKey;
}

jQuery(document).ready(function() {
	jQuery(".tab-switch").click(function() {
		var tabKey = jQuery(this).attr("data-tab-key");
		adjustTabs(tabKey);
	});

	adjustTabs(window.location.hash.substr(1) || "general");
});

