(function (window, $, undefined)
{
    'use strict';

    if (!window.WaHiddenTextField)
    {
        window.WaHiddenTextField = WaHiddenTextField;
    }

    function WaHiddenTextField(fieldModel)
    {
        fieldModel = fieldModel || {};

        var field,
            description,
            placeholderValue,
            descriptionHtml;

        function onFocus()
        {
            field.attr('placeholder', '');
        }

        function onBlur()
        {
            field.attr('placeholder', placeholderValue);
        }

        function onChange()
        {
            description.html((field.val() == '') ? descriptionHtml : '&nbsp;');
        }

        function init()
        {
            field = $('#' + fieldModel.fieldId);
            description = $('#' + fieldModel.descriptionId);
            placeholderValue = field.attr('placeholder');
            descriptionHtml = description.html();

            field.on('keyup paste change', onChange);
            field.on('focus', onFocus);
            field.on('blur', onBlur);
        }

        $(function(){ init(); });
    }

})(window, jQuery);