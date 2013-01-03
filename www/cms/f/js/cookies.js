function setCookie(_name, _value, _expire, _path)
{
	var e = isNaN(_expire) ? 0 : _expire;
	var curDate = new Date();
	var cookieExpire = new Date(Date.parse(curDate.toUTCString()) + e * 24 * 60 * 60 * 1000);
	var cookiePath = _path ? "; path=" + _path : "";
	document.cookie = _name + "=" + escape(_value) + (e ? ("; expires=" + cookieExpire.toUTCString()) : "") + cookiePath;
}

function getCookie(_name)
{
	var search = _name + "=";

	if (document.cookie.length > 0) {
		offset = document.cookie.indexOf(search);

		if (offset != -1) {
			offset += search.length;
			end = document.cookie.indexOf(";", offset);
			if (end == -1) end = document.cookie.length;
			return unescape(document.cookie.substring(offset, end));
		}
	}

	return "";
}

function removeFromCookieList(_name, _value, _expire)
{
	cookieList(_name, _value, _expire);
}

function saveIntoCookieList(_name, _value, _expire)
{
	cookieList(_name, _value, _expire, _value);
}

function cookieList(_name, _value, _expire, init)
{
	var cookies = getCookie(_name).split("|");
	var newCookies = new Array(init);

	for (var i = 0; i < cookies.length; i++) {
		if (cookies[i] != "" && cookies[i] != _value) {
			newCookies.push(cookies[i]);
		}
	}

	if (newCookies.length > 0) {
		setCookie(_name, newCookies.join("|"), _expire);
	} else {
		setCookie(_name, "", -1);
	}
}

function isCookie(_name, _value)
{
	var cookies = getCookie(_name).split("|");

	for (var i = 0; i < cookies.length; i++) {
		if (cookies[i] != "" && cookies[i] == _value) {
			return true;
		}
	}

	return false;
}
