function passwordReminder(_isOn)
{
	var link = document.getElementById("auth-forgot");
	var reminder = document.getElementById("auth-reminder");

	link.style.display = _isOn ? "none" : "block";
	reminder.style.display = _isOn ? "block" : "none";
}
