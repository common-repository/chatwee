
var UserListItem = function(parameters) {
	this.parameters = parameters;
};

UserListItem.prototype.build = function() {
	this.userListItemElement = document.createElement("tr");

	var nameCell = document.createElement("td");
	nameCell.innerHTML = this.parameters.userData.data.display_name;
	nameCell.className = "name-cell";

	var emailCell = document.createElement("td");
	emailCell.innerHTML = this.parameters.userData.data.user_email;
	emailCell.className = "email-cell";

	this.removeButtonElement = document.createElement("span");
	this.removeButtonElement.innerHTML = "&#x2716;";
	this.removeButtonElement.onclick = this.removeButtonClickCallback.bind(this);

	var actionCell = document.createElement("td");
	actionCell.appendChild(this.removeButtonElement);
	actionCell.className = "action-cell";

	this.parameters.parentElement.appendChild(this.userListItemElement);
	this.userListItemElement.appendChild(nameCell);
	this.userListItemElement.appendChild(emailCell);
	this.userListItemElement.appendChild(actionCell);
};

UserListItem.prototype.removeButtonClickCallback = function() {
	if(typeof this.parameters.removeCallback === "function") {
		this.parameters.removeCallback(this.parameters.userData);
	}
};