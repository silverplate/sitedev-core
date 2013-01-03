function getElementPosition(_ele)
{
    this.x = _ele.offsetLeft;
    this.y = _ele.offsetTop;
    this.ele = _ele;

    while (this.ele.offsetParent != null) {
        this.ele = this.ele.offsetParent;
        this.x += this.ele.offsetLeft;
        this.y += this.ele.offsetTop;
    }

    return this;
}

function addEvent(_ele, _type, _func)
{
    if (_ele.addEventListener) {
        _ele.addEventListener(_type, _func, false);

    } if (_ele.attachEvent) {
        _ele.attachEvent("on" + _type, _func);
    }
}

function removeEvent(_ele, _type, _func)
{
    if (_ele.removeEventListener) {
        _ele.removeEventListener(_type, _func, false);

    } if (_ele.detachEvent) {
        _ele.detachEvent("on" + _type, _func);
    }
}

function cancelEvent(_e)
{
    var evt = _e ? _e : window.event;
    evt.cancelBubble = true;
}

function getParentElement(_parent, _name)
{
    if (_name.toLowerCase() == _parent.nodeName.toLowerCase()) {
        return _parent;

    } else if (_parent.parentNode) {
        return getParentElement(_parent.parentNode, _name);

    } else {
        return false;
    }
}

function getClassName(_ele, _exeptClassName)
{
    var classes = _ele.className.split(" ");
    var className = "";

    for (var i = 0; i < classes.length; i++) {
        if (_exeptClassName != classes[i]) {
            className += (className.length > 0 ? " " : "") + classes[i];
        }
    }

    return className;
}

function removeElement(_ele)
{
    if (_ele.parentNode) {
        _ele.parentNode.removeChild(_ele);
    }
}


/**
 * Loading bar
 */

var loadings = 0;

function showLoadingBar()
{
    var loadingEle = document.getElementById("loading");

    if (!loadingEle) {
        loadingEle = document.createElement("div");
        loadingEle.setAttribute("id", "loading");

        var bodyEle = document.getElementsByTagName("body")[0];
        bodyEle.insertBefore(loadingEle, bodyEle.firstChild);
    }

    if (loadings == 0) {
        loadingEle.style.display = "block";
        waitCursor(true);
    }

    loadings++;
}

function hideLoadingBar()
{
    var loadingEle = document.getElementById("loading");
    loadings--;

    if (loadingEle && loadings == 0) {
        loadingEle.style.display = "none";
        waitCursor(false);
    }
}

function waitCursor(_isOn)
{
    var bodyEle = document.getElementsByTagName("body")[0];
    var className = getClassName(bodyEle, "wait");

    if (_isOn) {
        if (className != "") className += " ";
        className += "wait";
    }

    bodyEle.className = className;
}
