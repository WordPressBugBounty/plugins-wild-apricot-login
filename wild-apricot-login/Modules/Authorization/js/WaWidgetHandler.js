(function (window, $, undefined)
{
    'use strict';

    if (!window.WaWidgetHandlerData)
    {
        window.WaWidgetHandlerData = {};
    }

    $(function() {
        var isWidgetUserAnonymous,
            sessionKey = 'waWidgetLoginReturnUrl',
            widgetReturnUrl = getSessionValue(sessionKey),
            widgetReturnUrlToStore = {},
            isLogoutStarted = false;

        setSessionValue(sessionKey, widgetReturnUrlToStore);

        $(window).on(
            'message',
            function(event)
            {
                if (isLogoutStarted) { return; }

                try
                {
                    var waWidgetData = JSON.parse(event.originalEvent.data);

                    $('iframe').each(
                        function()
                        {
                            if (this.contentWindow === event.originalEvent.source)
                            {
                                var src = this.src.substring(0, 255);

                                $(this).css('visibility', 'visible');

                                if (!WaWidgetHandlerData.isLoggedIn)
                                {
                                    if (waWidgetData.isUserAnonymous && waWidgetData.isLoginRequired)
                                    {
                                        if (waWidgetData.returnUrl && waWidgetData.returnUrl.length)
                                        {
                                            src = this.src.substring(0, 255);
                                            widgetReturnUrlToStore[src] = waWidgetData.returnUrl;
                                            setSessionValue(sessionKey, widgetReturnUrlToStore);
                                        }

                                        $(this).replaceWith(WaWidgetHandlerData.loginForm);
                                    }
                                }
                                else
                                {
                                    if (isWidgetUserAnonymous === false && waWidgetData.isUserAnonymous === true)
                                    {
                                        isLogoutStarted = true;
                                        window.location = WaWidgetHandlerData.logoutUrl;

                                        return false;
                                    }

                                    if (!waWidgetData.isLoginRequired && waWidgetData.isUserAnonymous === false)
                                    {
                                        src = this.src.substring(0, 255);

                                        if (widgetReturnUrl && widgetReturnUrl[src] && this.src != widgetReturnUrl[src])
                                        {
                                            this.src = widgetReturnUrl[src];
                                        }
                                    }

                                    if (WaWidgetHandlerData.sessionKey)
                                    {
                                        var param = 'sk=' + encodeURIComponent(WaWidgetHandlerData.sessionKey);
                                        var searchKey = 'sk=';

                                        if (src.indexOf(searchKey) < 0)
                                        {
                                            $(this).css('visibility', 'hidden');

                                            try {
                                                var url = new URL(src);
                                                search = url.search;
                    
                                                url.search = search + (search.indexOf('?') > -1 ? '&' : '?') + param;
                                                this.src = url.toString();
                                            } catch (error) {
                                                this.src = src + (src.indexOf('?') < 0 ? '?' : '&') + param;
                                            }
                                        }
                                    }
                                }

                                return false;
                            }
                        }
                    );

                    isWidgetUserAnonymous = waWidgetData.isUserAnonymous;
                }
                catch (e) {}
            }
        );
    });

    function setSessionValue(key, value)
    {
        try
        {
            window.sessionStorage.setItem(key, JSON.stringify(value));
        }
        catch (e) {}
    }

    function getSessionValue(key)
    {
        try
        {
            return JSON.parse(window.sessionStorage.getItem(key));
        }
        catch (e)
        {
            return null;
        }
    }
})(window, jQuery);
