
var PagePicker = function(parameters) {
	this.parameters = parameters;

	this.pendingRequest = null;

	this.currentValue = null;
	this.currentIndex = null;
	this.currentPageData = null;
	this.pagePickerItemsArray = [];
}

PagePicker.prototype.build = function() {
	this.pagePickerInputWrapperElement = document.createElement("div");
	this.pagePickerInputWrapperElement.className = "user-picker-input-wrapper";

	this.pagePickerInputElement = document.createElement("input");
	this.pagePickerInputElement.setAttribute("type", "text");
	this.pagePickerInputElement.className = "user-picker-input";
	this.pagePickerInputWrapperElement.appendChild(this.pagePickerInputElement);

	this.pageSubmitElement = document.createElement("input");
	this.pageSubmitElement.setAttribute("type", "button");
	this.pageSubmitElement.value = "Add";
	this.pageSubmitElement.setAttribute("disabled", "disabled");
	this.pagePickerInputWrapperElement.appendChild(this.pageSubmitElement);

	this.pagePickerListElement = document.createElement("div");
	this.pagePickerListElement.className = "user-picker-list";
	this.pagePickerListElement.style.display = "none";
	this.pagePickerInputWrapperElement.appendChild(this.pagePickerListElement);

	var parentElement = document.getElementById(this.parameters.parentElementId);
	parentElement.appendChild(this.pagePickerInputWrapperElement);

	this.bindActions();
};

PagePicker.prototype.bindActions = function() {
	this.pagePickerInputElement.onkeyup = this.pagePickerKeyUpCallback.bind(this);
	this.pagePickerInputElement.onkeydown = this.pagePickerKeyDownCallback.bind(this);
	this.pagePickerInputElement.onblur = this.pagePickerBlurCallback.bind(this);
	this.pagePickerInputElement.onfocus = this.pagePickerFocusCallback.bind(this);
	this.pageSubmitElement.onclick = this.pageSubmitClickCallback.bind(this);
};

PagePicker.prototype.pagePickerKeyUpCallback = function() {
	if(this.pendigRequest) {
		this.pendingRequest.abort();
	}
	if(this.currentValue === this.pagePickerInputElement.value) {
		return false;
	}
	if(this.pagePickerInputElement.value.length < 2) {
		this.pagePickerListElement.innerHTML = "";
		this.pagePickerItemsArray = [];
		return false;
	}
	this.currentValue = this.pagePickerInputElement.value;

	this.pendingRequest = jQuery.ajax({
		type: "POST",
		data: {
			action: "chatwee_admin_search_page",
			search_name: this.pagePickerInputElement.value
		},
		dataType: "json",
		url: "admin-ajax.php",
		success: (function(data) {
			this.refreshList(data);
		}).bind(this)
	});
};

PagePicker.prototype.pagePickerKeyDownCallback = function(event) {
	if(event.keyCode === 13) {
		event.preventDefault();
		event.stopPropagation();

		if(this.currentIndex !== null) {
			this.currentPageData = this.pagePickerItemsArray[this.currentIndex].getPageData();
			this.pagePickerInputElement.value = this.currentPageData.post_title;
			this.currentValue = this.pagePickerInputElement.value;
			this.hideList();
			this.triggerPageChangeCallback();
			this.pagePickerItemsArray = []
		}
	}
	if(event.keyCode === 40) {
		this.currentIndex = this.currentIndex === null ? 0 : this.currentIndex + 1;
		this.currentIndex = this.currentIndex > this.pagePickerItemsArray.length - 1  ? 0 : this.currentIndex;
		this.refreshHighlight();
	}
	if(event.keyCode === 38) {
		this.currentIndex = this.currentIndex === null ? this.pagePickerItemsArray.length - 1 : this.currentIndex - 1;
		this.currentIndex = this.currentIndex < 0 ? this.pagePickerItemsArray.length - 1 : this.currentIndex;
		this.refreshHighlight();
	}
};

PagePicker.prototype.pagePickerBlurCallback = function() {
	this.hideList();
};

PagePicker.prototype.pagePickerFocusCallback = function() {
	if(this.pagePickerItemsArray.length > 0) {
		this.showList();
	}
};

PagePicker.prototype.pageSubmitClickCallback = function() {
	this.parameters.pageList.showLoader();

	jQuery.ajax({
		type:"POST",
		data: {
			action: 'chatwee_admin_add_page',
			page_id: this.currentPageData.ID
		},
		url: "admin-ajax.php",
		success: (function() {
			this.parameters.pageList.reload();
			this.clear();
		}).bind(this)
	});

};

PagePicker.prototype.clear = function() {
	this.hideList();
	this.currentIndex = null;
	this.currentPageData = null;
	this.pagePickerInputElement.value = "";
	this.currentValue = this.pagePickerInputElement.value;
	this.pageSubmitElement.setAttribute("disabled", "disabled");
	this.pagePickerListElement.innerHTML = "";
	this.pagePickerItemsArray = [];
};

PagePicker.prototype.hideList = function() {
	this.pagePickerListElement.style.display = "none";
};

PagePicker.prototype.showList = function() {
	this.pagePickerListElement.style.display = "block";
};

PagePicker.prototype.triggerPageChangeCallback = function() {
	if(this.currentPageData) {
		this.pageSubmitElement.removeAttribute("disabled");
	} else {
		this.pageSubmitElement.setAttribute("disabled", "disabled");
	}
};

PagePicker.prototype.refreshList = function(pages) {
	if(pages.length === 0) {
		this.hideList();
		return false;
	}
	this.showList();
	this.currentIndex = null;
	this.currentPageData = null;
	this.triggerPageChangeCallback();

	this.pagePickerListElement.innerHTML = "";

	this.pagePickerItemsArray = [];
	pages.forEach((function(page) {
		var pagePickerItem = new PagePickerItem({
			"pageData": page,
			"parentElement": this.pagePickerListElement,
			"mouseDownCallback": this.pagePickerItemMouseDownCallback.bind(this)
		});
		pagePickerItem.build();
		this.pagePickerItemsArray.push(pagePickerItem);
	}).bind(this));
};

PagePicker.prototype.pagePickerItemMouseDownCallback = function(pageData) {
	this.currentPageData = pageData
	this.pagePickerInputElement.value = pageData.post_title;
	this.currentValue = this.pagePickerInputElement.value;
	this.hideList();
	this.triggerPageChangeCallback();
	this.pagePickerItemsArray = [];
};

PagePicker.prototype.refreshHighlight = function() {
	this.pagePickerItemsArray.forEach(function(pagePickerItem) {
		pagePickerItem.unhighlight();
	});
	this.pagePickerItemsArray[this.currentIndex].highlight();
};

