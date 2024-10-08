(function()
{
    jQuery(function() {
        if (!window.WaLoginPluginShortcodesData) { return; }

        for (var i in WaLoginPluginShortcodesData)
        {
            if (WaLoginPluginShortcodesData.hasOwnProperty(i))
            {
                addQuickTagButton(WaLoginPluginShortcodesData[i]);
            }
        }
    });

    function addQuickTagButton(shortcodeData)
    {
        var shortcodeTag = shortcodeData.shortcodeTag,
            attrs = shortcodeData.attr,
            defaults = shortcodeData.defaults,
            attrString = attrs.reduce(function(attrString, attrName) {
                return attrString + ' ' + attrName + '="' + (defaults[attrName] || '') + '"';
            }, '');

        QTags.addButton(
            shortcodeTag,
            shortcodeTag,
            '[' + shortcodeTag + attrString + ']',
            shortcodeData.isContentWrapper ? '[/' + shortcodeTag + ']' : '',
            '',
            shortcodeData.formTitle
        );
    }
})();