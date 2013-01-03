function getElementPosition(ele)
{
    this.x = ele.offsetLeft;
    this.y = ele.offsetTop;
    this.ele = ele;

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
        _ele.attachEvent('on' + _type, _func);
    }
}

function removeEvent(_ele, _type, _func)
{
    if (_ele.removeEventListener) {
        _ele.removeEventListener(_type, _func, false);

    } if (_ele.detachEvent) {
        _ele.detachEvent('on' + _type, _func);
    }
}

function cancelEvent(_e)
{
    var evt = _e ? _e : window.event;
    evt.cancelBubble = true;
}

function getParentElement(parent, name)
{
    if (name.toLowerCase() == parent.nodeName.toLowerCase()) {
        return parent;

    } else if (parent.parentNode) {
        return getParentElement(parent.parentNode, name);

    } else {
        return false;
    }
}

function getClassName(ele, exeptClassName)
{
    var classes = ele.className.split(' ');
    var className = '';

    for (var i = 0; i < classes.length; i++) {
        if (exeptClassName != classes[i]) {
            className += (className.length > 0 ? ' ' : '') + classes[i];
        }
    }

    return className;
}


/*** Loading bar
*********************************************************/
var loadings = 0;

function showLoadingBar()
{
    var loadingEle = document.getElementById('loading');
    if (!loadingEle) {
        loadingEle = document.createElement('div');
        loadingEle.setAttribute('id', 'loading');

        var bodyEle = document.getElementsByTagName('body')[0];
        bodyEle.insertBefore(loadingEle, bodyEle.firstChild);
    }

    if (loadings == 0) {
        loadingEle.style.display = 'block';
        waitCursor(true);
    }

    loadings++;
}

function hideLoadingBar()
{
    var loadingEle = document.getElementById('loading');
    loadings--;

    if (loadingEle && loadings == 0) {
        loadingEle.style.display = 'none';
        waitCursor(false);
    }
}

function waitCursor(isOn)
{
    var bodyEle = document.getElementsByTagName('body')[0];
    var className = getClassName(bodyEle, 'wait');
    if (isOn) {
        if (className != '') className += ' ';
        className += 'wait';
    }

    bodyEle.className = className;
}
