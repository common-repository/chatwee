
var UserList = function(parameters) {
	this.parameters = parameters;
};

UserList.prototype.build = function() {
	this.userListElement = document.createElement("div");
	this.userListElement.className = "user-list";

	this.emptyDataElement = document.createElement("div");
	this.emptyDataElement.className = "empty-data";
	this.emptyDataElement.style.display = "none";
	this.emptyDataElement.innerHTML = "You don't have any WordPress moderators appointed yet.";

	this.userListTableElement = document.createElement("table");
	this.userListTableElement.className = "user-list-table";
	this.userListTableElement.style.display = "none";
	this.userListTableElement.innerHTML = "<thead><tr><th class='name-cell'>Name</th><th class='email-cell'>Email</th><th class='action-cell'>Remove</th></tr></thead>";

	this.userListDataElement = document.createElement("tbody");
	this.userListDataElement.className = "user-list-data";

	var parentElement = document.getElementById(this.parameters.parentElementId);
	parentElement.appendChild(this.userListElement);

	this.loaderElement = document.createElement("div");
	this.loaderElement.className = "user-list-loader-element";
	this.loaderElement.style.display = "none";

	this.userListElement.appendChild(this.emptyDataElement);
	this.userListElement.appendChild(this.userListTableElement);
	this.userListTableElement.appendChild(this.userListDataElement);
	this.userListElement.appendChild(this.loaderElement);
};

UserList.prototype.reload = function() {
	this.showLoader();

	jQuery.ajax({
		type:"GET",
		data: {
			action: 'chatwee_admin_get_moderators'
		},
		dataType: "json",
		url: "admin-ajax.php",
		success: (function(data) {
			this.refresh(data);
		}).bind(this)
	});
};

UserList.prototype.setNoDataMode = function() {
	this.emptyDataElement.style.display = "block";
	this.userListTableElement.style.display = "none";
};

UserList.prototype.setDataMode = function() {
	this.emptyDataElement.style.display = "none";
	this.userListTableElement.style.display = "table";
};

UserList.prototype.showLoader = function() {
	this.loaderElement.style.display = "block";
};

UserList.prototype.hideLoader = function() {
	this.loaderElement.style.display = "none";
};

UserList.prototype.refresh = function(data) {
	this.hideLoader();

	if (data.length === 0) {
		this.setNoDataMode();
		return false;
	}
	this.setDataMode();

	this.userListDataElement.innerHTML = "";
	data.forEach((function(dataRow) {
		var userListItem = new UserListItem({
			"userData": dataRow,
			"parentElement": this.userListDataElement,
			"removeCallback": this.userListItemRemoveCallback.bind(this)
		});
		userListItem.build();
	}).bind(this));
};

UserList.prototype.userListItemRemoveCallback = function(userData) {
	if(confirm("Are you sure you want to remove this moderator?") !== true) {
		return false;
	}

	this.showLoader();
	jQuery.ajax({
		type:"POST",
		data: {
			action: 'chatwee_admin_remove_moderator',
			"user_id": userData.ID
		},
		url: "admin-ajax.php",
		success: (function() {
			this.reload();
		}).bind(this)
	});
};