
var UserPicker = function(parameters) {
	this.parameters = parameters;

	this.pendingRequest = null;

	this.currentValue = null;
	this.currentIndex = null;
	this.currentUserData = null;
	this.userPickerItemsArray = [];
}

UserPicker.prototype.build = function() {
	this.userPickerInputWrapperElement = document.createElement("div");
	this.userPickerInputWrapperElement.className = "user-picker-input-wrapper";

	this.userPickerInputElement = document.createElement("input");
	this.userPickerInputElement.setAttribute("type", "text");
	this.userPickerInputElement.className = "user-picker-input";
	this.userPickerInputWrapperElement.appendChild(this.userPickerInputElement);

	this.moderatorSubmitElement = document.createElement("input");
	this.moderatorSubmitElement.setAttribute("type", "button");
	this.moderatorSubmitElement.value = "Add";
	this.moderatorSubmitElement.setAttribute("disabled", "disabled");
	this.userPickerInputWrapperElement.appendChild(this.moderatorSubmitElement);

	this.userPickerListElement = document.createElement("div");
	this.userPickerListElement.className = "user-picker-list";
	this.userPickerListElement.style.display = "none";
	this.userPickerInputWrapperElement.appendChild(this.userPickerListElement);

	var parentElement = document.getElementById(this.parameters.parentElementId);
	parentElement.appendChild(this.userPickerInputWrapperElement);

	this.bindActions();
};

UserPicker.prototype.bindActions = function() {
	this.userPickerInputElement.onkeyup = this.userPickerKeyUpCallback.bind(this);
	this.userPickerInputElement.onkeydown = this.userPickerKeyDownCallback.bind(this);
	this.userPickerInputElement.onblur = this.userPickerBlurCallback.bind(this);
	this.userPickerInputElement.onfocus = this.userPickerFocusCallback.bind(this);
	this.moderatorSubmitElement.onclick = this.moderatorSubmitClickCallback.bind(this);
};

UserPicker.prototype.userPickerKeyUpCallback = function() {
	if(this.pendigRequest) {
		this.pendingRequest.abort();
	}
	if(this.currentValue === this.userPickerInputElement.value) {
		return false;
	}
	if(this.userPickerInputElement.value.length < 2) {
		this.userPickerListElement.innerHTML = "";
		this.userPickerItemsArray = [];
		return false;
	}
	this.currentValue = this.userPickerInputElement.value;

	this.pendingRequest = jQuery.ajax({
		type: "POST",
		data: {
			action: "chatwee_admin_search_user",
			search_name: this.userPickerInputElement.value
		},
		dataType: "json",
		url: "admin-ajax.php",
		success: (function(data) {
			this.refreshList(data);
		}).bind(this)
	});
};

UserPicker.prototype.userPickerKeyDownCallback = function(event) {
	if(event.keyCode === 13) {
		event.preventDefault();
		event.stopPropagation();

		if(this.currentIndex !== null) {
			this.currentUserData = this.userPickerItemsArray[this.currentIndex].getUserData();
			this.userPickerInputElement.value = this.currentUserData.data.display_name;
			this.currentValue = this.userPickerInputElement.value;
			this.hideList();
			this.triggerUserChangeCallback();
			this.userPickerItemsArray = []
		}
	}
	if(event.keyCode === 40) {
		this.currentIndex = this.currentIndex === null ? 0 : this.currentIndex + 1;
		this.currentIndex = this.currentIndex > this.userPickerItemsArray.length - 1  ? 0 : this.currentIndex;
		this.refreshHighlight();
	}
	if(event.keyCode === 38) {
		this.currentIndex = this.currentIndex === null ? this.userPickerItemsArray.length - 1 : this.currentIndex - 1;
		this.currentIndex = this.currentIndex < 0 ? this.userPickerItemsArray.length - 1 : this.currentIndex;
		this.refreshHighlight();
	}
};

UserPicker.prototype.userPickerBlurCallback = function() {
	this.hideList();
};

UserPicker.prototype.userPickerFocusCallback = function() {
	if(this.userPickerItemsArray.length > 0) {
		this.showList();
	}
};

UserPicker.prototype.moderatorSubmitClickCallback = function() {
	this.parameters.userList.showLoader();

	jQuery.ajax({
		type:"POST",
		data: {
			action: 'chatwee_admin_add_moderator',
			user_id: this.currentUserData.ID
		},
		url: "admin-ajax.php",
		success: (function() {
			this.parameters.userList.reload();
			this.clear();
		}).bind(this)
	});
};

UserPicker.prototype.clear = function() {
	this.hideList();
	this.currentIndex = null;
	this.currentUserData = null;
	this.userPickerInputElement.value = "";
	this.currentValue = this.userPickerInputElement.value;
	this.moderatorSubmitElement.setAttribute("disabled", "disabled");
	this.userPickerListElement.innerHTML = "";
	this.userPickerItemsArray = [];
};

UserPicker.prototype.hideList = function() {
	this.userPickerListElement.style.display = "none";
};

UserPicker.prototype.showList = function() {
	this.userPickerListElement.style.display = "block";
};

UserPicker.prototype.triggerUserChangeCallback = function() {
	if(this.currentUserData) {
		this.moderatorSubmitElement.removeAttribute("disabled");
	} else {
		this.moderatorSubmitElement.setAttribute("disabled", "disabled");
	}
};

UserPicker.prototype.refreshList = function(users) {
	if(users.length === 0) {
		this.hideList();
		return false;
	}
	this.showList();
	this.currentIndex = null;
	this.currentUserData = null;
	this.triggerUserChangeCallback();

	this.userPickerListElement.innerHTML = "";

	this.userPickerItemsArray = [];
	users.forEach((function(user) {
		var userPickerItem = new UserPickerItem({
			"userData": user,
			"parentElement": this.userPickerListElement,
			"mouseDownCallback": this.userPickerItemMouseDownCallback.bind(this)
		});
		userPickerItem.build();
		this.userPickerItemsArray.push(userPickerItem);
	}).bind(this));
};

UserPicker.prototype.userPickerItemMouseDownCallback = function(userData) {
	this.currentUserData = userData
	this.userPickerInputElement.value = userData.data.display_name;
	this.currentValue = this.userPickerInputElement.value;
	this.hideList();
	this.triggerUserChangeCallback();
	this.userPickerItemsArray = [];
};

UserPicker.prototype.refreshHighlight = function() {
	this.userPickerItemsArray.forEach(function(userPickerItem) {
		userPickerItem.unhighlight();
	});
	this.userPickerItemsArray[this.currentIndex].highlight();
};

