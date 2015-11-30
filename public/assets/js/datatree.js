var giveDepth = function (ol, depth) {
    if (!ol.length) return;
    depth = depth || 1;
    ol.attr("data-depth", depth);
    var li = ol.children(".datatree-item");
    li.attr("data-depth", depth);
    giveDepth(li.children(".datatree-list"), depth + 1);
};