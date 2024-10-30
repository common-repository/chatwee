
var PageList = function(parameters) {
	this.parameters = parameters;
};

PageList.prototype.build = function() {
	this.pageListElement = document.createElement("div");
	this.pageListElement.className = "user-list";

	this.emptyDataElement = document.createElement("div");
	this.emptyDataElement.className = "empty-data";
	this.emptyDataElement.style.display = "none";
	this.emptyDataElement.innerHTML = "You don't have any particular pages chosen yet.";

	this.pageListTableElement = document.createElement("table");
	this.pageListTableElement.className = "user-list-table";
	this.pageListTableElement.style.display = "none";
	this.pageListTableElement.innerHTML = "<thead><tr><th class='name-cell'>Name</th><th class='email-cell'>Published on</th><th class='action-cell'>Remove</th></tr></thead>";

	this.pageListDataElement = document.createElement("tbody");
	this.pageListDataElement.className = "user-list-data";

	var parentElement = document.getElementById(this.parameters.parentElementId);
	parentElement.appendChild(this.pageListElement);

	this.loaderElement = document.createElement("div");
	this.loaderElement.className = "user-list-loader-element";
	this.loaderElement.style.display = "none";

	this.pageListElement.appendChild(this.emptyDataElement);
	this.pageListElement.appendChild(this.pageListTableElement);
	this.pageListTableElement.appendChild(this.pageListDataElement);
	this.pageListElement.appendChild(this.loaderElement);
};

PageList.prototype.reload = function() {
	this.showLoader();

	jQuery.ajax({
		type:"GET",
		data: {
			action: 'chatwee_admin_get_pages_to_display'
		},
		dataType: "json",
		url: "admin-ajax.php",
		success: (function(data) {
			this.refresh(data);
		}).bind(this)
	});
};

PageList.prototype.setNoDataMode = function() {
	this.emptyDataElement.style.display = "block";
	this.pageListTableElement.style.display = "none";
};

PageList.prototype.setDataMode = function() {
	this.emptyDataElement.style.display = "none";
	this.pageListTableElement.style.display = "table";
};

PageList.prototype.showLoader = function() {
	this.loaderElement.style.display = "block";
};

PageList.prototype.hideLoader = function() {
	this.loaderElement.style.display = "none";
};

PageList.prototype.refresh = function(data) {
	this.hideLoader();

	if (data.length === 0) {
		this.setNoDataMode();
		return false;
	}
	this.setDataMode();

	this.pageListDataElement.innerHTML = "";
	data.forEach((function(dataRow) {
		var pageListItem = new PageListItem({
			"pageData": dataRow,
			"parentElement": this.pageListDataElement,
			"removeCallback": this.pageListItemRemoveCallback.bind(this)
		});
		pageListItem.build();
	}).bind(this));
};

PageList.prototype.pageListItemRemoveCallback = function(pageData) {
	if(confirm("Are you sure you want to remove this page?") !== true) {
		return false;
	}

	this.showLoader();
	jQuery.ajax({
		type:"POST",
		data: {
			action: 'chatwee_admin_remove_page',
			"page_id": pageData.ID
		},
		url: "admin-ajax.php",
		success: (function() {
			this.reload();
		}).bind(this)
	});
};