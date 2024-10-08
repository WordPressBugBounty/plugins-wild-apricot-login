(function()
{
    if (!window.WaLoginPluginShortcodesData) { return; }

    for (var i in WaLoginPluginShortcodesData)
    {
        if (WaLoginPluginShortcodesData.hasOwnProperty(i))
        {
            addEditorButton(WaLoginPluginShortcodesData[i]);
        }
    }

    function addEditorButton(shortcodeData)
    {
        if (typeof shortcodeData !== 'object') { return; }

        var shortcodeTag = shortcodeData.shortcodeTag,
            commandId = shortcodeTag + '_command',
            attrs = shortcodeData.attr,
            defaults = shortcodeData.defaults;

        tinymce.create('tinymce.plugins.' + shortcodeTag,
            {
                init : function(editor)
                {
                    editor.addButton(shortcodeTag, {
                        title : shortcodeData.formTitle,
                        image : shortcodeData.buttonImgUrl,
                        cmd: commandId
                    });

                    editor.addCommand(commandId, function(ui, v) {
                        var values = getAttr(attrs, v, defaults);

                        editor.windowManager.open({
                            title: shortcodeData.formTitle,
                            body: fillFormFields(shortcodeData.formFields, values),

                            onsubmit: function(e)
                            {
                                var attr = getAttr(attrs, e.data, defaults),
                                    shortCodeString = '[' + shortcodeTag +
                                        attrs.reduce(function(attrString, attrName) {
                                            return attrString + (attr[attrName] ? ' ' + attrName + '="' + attr[attrName] + '"' : '');
                                        }, '') + ']' + (shortcodeData.isContentWrapper ?  (editor.selection.getContent() || '') + '[/' + shortcodeTag + ']' : '');

                                editor.insertContent(shortCodeString);
                            }
                        });
                    });
                },

                createControl : function(n, cm) { return null; }
            }
        );

        tinymce.PluginManager.add(shortcodeTag, tinymce.plugins[shortcodeTag]);
    }

    function getAttr(attrs, attrObj, defaults)
    {
        return attrs.reduce(function(result, attr) {
            result[attr] = (attrObj && attrObj[attr]) || defaults[attr];

            return result;
        }, {});
    }

    function fillFormFields(formFields, values)
    {
        if (formFields.length)
        {
            formFields.forEach(function(field) {
                field.value = values[field.name] || '';
            });
        }

        return formFields;
    }
})();