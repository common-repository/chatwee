
var UserPickerItem = function(parameters) {
	this.parameters = parameters;
};

UserPickerItem.prototype.build = function() {
	this.userPickerItemElement = document.createElement("div");
	this.userPickerItemElement.className = "user-picker-item";
	this.userPickerItemElement.innerHTML = this.parameters.userData.data.display_name;
	this.userPickerItemElement.onmousedown = this.itemMouseDownCallback.bind(this);

	this.parameters.parentElement.appendChild(this.userPickerItemElement);
};

UserPickerItem.prototype.itemMouseDownCallback = function() {
	if(typeof this.parameters.mouseDownCallback === "function") {
		this.parameters.mouseDownCallback(this.parameters.userData);
	}
};

UserPickerItem.prototype.highlight = function() {
	this.userPickerItemElement.className = "user-picker-item user-picker-item-highlighted";
};

UserPickerItem.prototype.unhighlight = function() {
	this.userPickerItemElement.className = "user-picker-item";
};

UserPickerItem.prototype.getUserData = function() {
	return this.parameters.userData;
};
