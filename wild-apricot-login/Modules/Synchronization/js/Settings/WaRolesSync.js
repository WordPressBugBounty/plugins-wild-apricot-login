(function (window, $, undefined)
{
    'use strict';

    if (!window.WaRolesSyncSettingData)
    {
        window.WaRolesSyncSettingData = {};
    }

    $(function(){

        var button = $('#' + WaRolesSyncSettingData.buttonId),
            spinner = $('#' + WaRolesSyncSettingData.spinnerId),
            description = $('#' + WaRolesSyncSettingData.descriptionId);

        button.click
        (
            function()
            {
                if (this.disabled) { return; }

                this.disabled = true;
                spinner.addClass('visible');
                description.html(WaRolesSyncSettingData.waitMessage);

                $.ajax
                (
                    {
                        url: WaRolesSyncSettingData.url + '?' + new Date().getTime(),
                        type: 'POST',
                        data: { action: WaRolesSyncSettingData.action },
                        dataType: 'json',
                        success: function(data, status, xhr)
                        {
                            spinner.removeClass('visible');
                            description.html
                            (
                                (typeof data == 'object' && data.message) ? data.message : WaRolesSyncSettingData.errorMessage
                            );
                            button.prop('disabled', false);
                        },
                        error: function(xhr, status, error)
                        {
                            spinner.removeClass('visible');
                            description.html(WaRolesSyncSettingData.errorMessage);
                            button.prop('disabled', false);
                        }
                    }
                );
            }
        );
    });

})(window, jQuery);