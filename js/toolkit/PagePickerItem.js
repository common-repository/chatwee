
var PagePickerItem = function(parameters) {
	this.parameters = parameters;
};

PagePickerItem.prototype.build = function() {
	this.pagePickerItemElement = document.createElement("div");
	this.pagePickerItemElement.className = "page-picker-item";
	this.pagePickerItemElement.innerHTML = this.parameters.pageData.post_title;
	this.pagePickerItemElement.onmousedown = this.itemMouseDownCallback.bind(this);

	this.parameters.parentElement.appendChild(this.pagePickerItemElement);
};

PagePickerItem.prototype.itemMouseDownCallback = function() {
	if(typeof this.parameters.mouseDownCallback === "function") {
		this.parameters.mouseDownCallback(this.parameters.pageData);
	}
};

PagePickerItem.prototype.highlight = function() {
	this.pagePickerItemElement.className = "page-picker-item page-picker-item-highlighted";
};

PagePickerItem.prototype.unhighlight = function() {
	this.pagePickerItemElement.className = "page-picker-item";
};

PagePickerItem.prototype.getPageData = function() {
	return this.parameters.pageData;
};
