let move = require('move-js');

const scrollBarWidths = 40;
const wrapper = document.getElementsByClassName("wrapper-nav")[0];
const lastNavLink = document.getElementsByClassName("last-nav-link")[0];

const scrollerRight = document.getElementsByClassName("scroller-right")[0];
const scrollerLeft = document.getElementsByClassName("scroller-left")[0];

const list = document.querySelectorAll(".list");

let btnTriggered = false;

let widthOfList = function() {
    let itemsWidth = 0;

    const listLinks = document.querySelectorAll(".list a");

    listLinks.forEach((el) => {
        let itemWidth = getOuterWidth(el);
        itemsWidth += itemWidth;
    });

    return itemsWidth;
};

let widthOfHidden = function(w) {
    const wrapperHelper = document.getElementsByClassName("wrapper-nav")[0];

    w = (!w) ? 0 : w;

    oW = getOuterWidth(wrapperHelper) - w;

    let ww = parseFloat((0 - oW).toFixed(3));

    let hw = (oW - widthOfList() - getLeftPosition()) - scrollBarWidths;

    let rp = document.body.clientWidth - (getOuterLeft(lastNavLink) + getOuterWidth(lastNavLink)) - w;

    if (ww > hw) {
        //return ww;
        return (rp > ww ? rp : ww);
    }
    else {
        //return hw;
        return (rp > hw ? rp : hw);
    }
};

let getLeftPosition = function() {
    let ww = 0 - getOuterWidth(wrapper);
    let lp = getOuterLeft(list[0]);

    if (ww > lp) {
        return ww;
    }
    else {
        return lp;
    }
};

let reAdjust = function() {
    let rp = document.body.clientWidth - (getOuterLeft(lastNavLink) + getOuterWidth(lastNavLink));

    if (getOuterWidth(wrapper) < widthOfList() && (rp < 0)) {
        scrollerRight.style.cssText = 'display: flex';
    }
    else {
        scrollerRight.style.display = 'none';
    }

    if (getLeftPosition() < 0) {
        scrollerLeft.style.cssText = 'display: flex';
    }
    else {
        scrollerLeft.style.display = 'none';
    }

    btnTriggered = false;
}

window.addEventListener('resize', function(event) {
    reAdjust();
}, true);

scrollerRight.addEventListener("click", function() {
    if (btnTriggered) return;

    btnTriggered = true;

    fade(scrollerLeft);
    unfade(scrollerRight);

    let wR = getOuterWidth(scrollerRight);

    move(document.querySelectorAll(".list")[0]).add("left", +widthOfHidden(wR), 200).end().then(x=> {
        reAdjust();
    });
});

scrollerLeft.addEventListener("click", function() {
    if (btnTriggered) return;

    btnTriggered = true;

    fade(scrollerRight);
    unfade(scrollerLeft);

    let wL = getOuterWidth(scrollerLeft);

    move(document.querySelectorAll(".list")[0]).add("left", -getLeftPosition() + wL, 200).end().then(()=> {
        reAdjust();
    });
});

let getOuterLeft = function(elem) {
    return elem.getBoundingClientRect().left;
}

let getOuterWidth = function(elem) {
    return parseFloat(window.getComputedStyle(elem).width);
}

function fade(elem) {
    elem.style.display = "none";
    elem.style.transition="opacity 0.6s";
    elem.style.opacity=0;
}

function unfade(elem) {
    elem.style.display = "block";
    elem.style.transition="opacity 0.6s";
    elem.style.opacity=1;
}

reAdjust();
