
var PageListItem = function(parameters) {
	this.parameters = parameters;
};

PageListItem.prototype.build = function() {
	this.pageListItemElement = document.createElement("tr");

	var nameCell = document.createElement("td");
	nameCell.innerHTML = this.parameters.pageData.post_title;
	nameCell.className = "name-cell";

	var dateCell = document.createElement("td");
	dateCell.innerHTML = this.parameters.pageData.post_date;
	dateCell.className = "email-cell";

	this.removeButtonElement = document.createElement("span");
	this.removeButtonElement.innerHTML = "&#x2716;";
	this.removeButtonElement.onclick = this.removeButtonClickCallback.bind(this);

	var actionCell = document.createElement("td");
	actionCell.appendChild(this.removeButtonElement);
	actionCell.className = "action-cell";

	this.parameters.parentElement.appendChild(this.pageListItemElement);
	this.pageListItemElement.appendChild(nameCell);
	this.pageListItemElement.appendChild(dateCell);
	this.pageListItemElement.appendChild(actionCell);
};

PageListItem.prototype.removeButtonClickCallback = function() {
	if(typeof this.parameters.removeCallback === "function") {
		this.parameters.removeCallback(this.parameters.pageData);
	}
};