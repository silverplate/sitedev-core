var calendars = new Array();

function getCalendar(name)
{
	for (var i = 0; i < calendars.length; i++) {
		if (calendars[i].name == name) {
			return calendars[i];
		}
	}
	return false;
}

function calendarInit(name, lang)
{
	calendars[calendars.length] = new Calendar(name, lang);
}

function calendarParseInput(name)
{
	getCalendar(name).parseInput();
}

function calendarRemove()
{
	for (var i = 0; i < calendars.length; i++) {
		calendars[i].remove();
	}
}

function calendarSwitcher(name, e)
{
	getCalendar(name).calendar(e);
}

function calendarDraw(name, y, m, d)
{
	getCalendar(name).draw(y, m, d);
}

function calendarSet(name, y, m, d)
{
	getCalendar(name).set(y, m, d);
}

function Calendar(name, lang)
{
	this.name = name;
	this.lang = lang != 'en' && lang != 'ru' ? 'ru' : lang;

	this.weekdays = this.lang == 'en'
		? new Array('M', 'T', 'W', 'T', 'F', 'S', 'S')
		: new Array('П', 'В', 'С', 'Ч', 'П', 'С', 'В');

	this.months = this.lang == 'en'
		? new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')
		: new Array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');

	this.getEleName = function()
	{
		return this.name + '-calendar-ele';
	};

	this.getInputName = function()
	{
		return this.name + '-input';
	};

	this.getEle = function()
	{
		return document.getElementById(this.getEleName());
	};

	this.getDateEle = function()
	{
		return document.getElementById(this.name);
	};

	this.getInputEle = function()
	{
		return document.getElementById(this.getInputName());
	};

	this.stringToDate = function(string)
	{
		var regexp = /^\s*(\d+)[\s|\/|.|\-](.+)[\s|\/|.|\-](\d+)\s*$/i;
		var parse = regexp.exec(string);
		var now = new Date();
		var date = new Date(now.getFullYear(), now.getMonth(), now.getDate());

		if (parse) {
			if (parse[1].toString().length == 4) {
				date = new Date(parse[1], Number(parse[2]) - 1, Number(parse[3]));

			} else {
				if (isNaN(parse[2])) {
					date = new Date(parse[3], 0, parse[1]);

					for (var i = 0; i < this.months.length; i++) {
						if (this.months[i].substr(0, 3).toLowerCase() == parse[2].substr(0, 3).toLowerCase()) {
							date.setMonth(i);
							break;
						}
					}
				} else {
					date = this.lang == 'en'
						? new Date(parse[3], Number(parse[1]) - 1, parse[2])
						: new Date(parse[3], Number(parse[2]) - 1, parse[1]);
				}
			}
		}

		return date;
	};

	this.getSqlDate = function(date)
	{
		var m = (date.getMonth() + 1).toString();
		if (m.length == 1) m = '0' + m;

		var d = date.getDate().toString();
		if (d.length == 1) d = '0' + d;

		return date.getFullYear() + '-' + m + '-' + d;
	};

	this.getDate = function()
	{
		return this.stringToDate(this.getInputEle().value);
	};

	this.setDate = function(y, m, d)
	{
		var date = new Date(y, m, d);
		this.getInputEle().value = date.getDate() + ' ' + this.months[date.getMonth()].substr(0, 3).toLowerCase() + ' ' + date.getFullYear();
		this.getDateEle().value = this.getSqlDate(date);
	};

    this.isDateSet = function()
    {
        return this.getDateEle().value != '';
    };

	this.clearDate = function()
	{
		this.getInputEle().value = '';
		this.getDateEle().value = '';
	};

	this.set = function(y, m, d)
	{
		this.setDate(y, m, d);
		this.remove();
	};

	this.calendar = function(e)
	{
		cancelEvent(e);

		if (this.getEle()) {
			this.remove();

		} else {
			calendarRemove();
			this.draw();
		}
	};

	this.remove = function()
	{
		if (this.getEle()) {
			this.getEle().parentNode.removeChild(this.getEle());
			removeEvent(document, 'click', calendarRemove);
		}
	};

	this.parseInput = function()
	{
		if (this.getInputEle().value) {
			var date = this.getDate();
			this.setDate(date.getFullYear(), date.getMonth(), date.getDate());
		} else {
			this.clearDate();
		}
	};

	this.init = function()
	{
		if (this.getDateEle().value && this.getDateEle().value != '0000-00-00') {
			var date = this.stringToDate(this.getDateEle().value);
			this.setDate(date.getFullYear(), date.getMonth(), date.getDate());
		}
	};

	this.draw = function(y, m, d)
	{
		this.remove();

		var bodyEle = document.getElementsByTagName('body')[0];
		var ele = document.createElement('div');
		var pos = new getElementPosition(this.getInputEle());

		ele.className = 'calendar';
		ele.id = this.getEleName();
		ele.style.left = pos.x + 'px';
		ele.style.top = pos.y + this.getInputEle().offsetHeight + 5 + 'px';

		bodyEle.appendChild(ele);
		addEvent(ele, 'click', cancelEvent);
		addEvent(document, 'click', calendarRemove);

		var selected = this.isDateSet() ? this.getDate() : false;
		var now = new Date();
		var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

		var calendar = y ? new Date(y, m, d) : this.getDate();
		var y = calendar.getFullYear();
		var m = calendar.getMonth();
		var d = calendar.getDate();
		var prevM = m == 0 ? new Date(y - 1, 11, 1) : new Date(y, m - 1, 1);
		var nextM = m == 11 ? new Date(y + 1, 0, 1) : new Date(y, m + 1, 1);

		var content = '<table><thead>';
		content += '<tr class="year"><td><a class="calc-arr-l" onclick="calendarDraw(\'' + this.name + '\',' + (y - 1) + ',' + m + ',' + d + '); return false;"><img src="/cms/f/calendar/left.gif" width="4" height="7" alt="" /></a></td>';
		content += '<td><a class="calc-arr-l" onclick="calendarDraw(\'' + this.name + '\',' + (y - 10) + ',' + m + ',' + d + '); return false;"><img src="/cms/f/calendar/left.gif" width="4" height="7" alt="" /><img src="/cms/f/calendar/left.gif" width="4" height="7" alt="" /></a></td>';
		content += '<td colspan="3">' + y + '</td>';
		content += '<td><a class="calc-arr-r" onclick="calendarDraw(\'' + this.name + '\',' + (y + 10) + ',' + m + ',' + d + '); return false;"><img src="/cms/f/calendar/right.gif" width="4" height="7" alt="" /><img src="/cms/f/calendar/right.gif" width="4" height="7" alt="" /></a></td>';
		content += '<td><a class="calc-arr-r" onclick="calendarDraw(\'' + this.name + '\',' + (y + 1) + ',' + m + ',' + d + '); return false;"><img src="/cms/f/calendar/right.gif" width="4" height="7" alt="" /></a></td></tr>';
		content += '<tr class="month"><td><a class="calc-arr-l" onclick="calendarDraw(\'' + this.name + '\',' + prevM.getFullYear() + ',' + prevM.getMonth() + ',' + prevM.getDate() + '); return false;"><img src="/cms/f/calendar/left.gif" width="4" height="7" alt="" /></a></td>';
		content += '<td colspan="5">' + this.months[m] + '</td>';
		content += '<td><a class="calc-arr-r" onclick="calendarDraw(\'' + this.name + '\',' + nextM.getFullYear() + ',' + nextM.getMonth() + ',' + nextM.getDate() + '); return false;"><img src="/cms/f/calendar/right.gif" width="4" height="7" alt="" /></a></td></tr>';

		content += '<tr class="weekdays">';
		for (var i = 0; i < this.weekdays.length; i++) {
			content += '<td' + (i > 4 ? ' class="weekend"' : '') + '>' + this.weekdays[i] + '</td>';
		}
		content += '</tr></thead><tbody><tr>';

		while (!(calendar.getDay() == 1 && (calendar.getDate() == 1 || calendar.getMonth() != m))) {
			calendar.setDate(calendar.getDate() - 1);
		}

		var className = '';
		while (!(calendar.getDay() == 1 && calendar.getMonth() == nextM.getMonth())) {
			if (calendar.getMonth() != m) className += 'notnow';
			if (calendar.getDay() == 0 || calendar.getDay() == 6) className += (className ? ' ' : '') + 'weekend';

            if (calendar.valueOf() == today.valueOf()) {
                className += (className ? ' ' : '') + 'today';
            }

			if (selected && calendar.valueOf() == selected.valueOf()) {
			    className += (className ? ' ' : '') + 'selected';
			}

			content += '<td' + (className ? ' class="' + className + '"' : '') + '>';
			content += '<a onclick="calendarSet(\'' + this.name + '\',' + calendar.getFullYear() + ',' + calendar.getMonth() + ',' + calendar.getDate() + '); return false;">' + calendar.getDate() + '</a></td>';

			if (calendar.getDay() == 0) content += '</tr><tr>';
			calendar.setDate(calendar.getDate() + 1);
			className = '';
		}

		content += '</tr></tbody></table>';
		ele.innerHTML = content;
	};

	this.init();
}
