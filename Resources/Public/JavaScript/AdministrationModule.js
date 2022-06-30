define(['jquery', 'TYPO3/CMS/Backend/Tooltip'], function ($) {

    var filterName = 'qcComments'
    if ($('.t3js-clearable').length) {
        require(['TYPO3/CMS/Backend/jquery.clearable'], function () {
            $('.t3js-clearable').clearable();
        });
    }

    $(document).ready(function () {

        if (getCookie(filterName) == '') {
            setCookie(filterName, 1, 365);
        }

        if (getCookie(filterName) == 1) {
            $('#filter-container').css('display', 'block');
        } else {
            $('#filter-container').css('display', 'none');
        }

        $('a[data-togglelink="1"]').click(function (e) {
            e.preventDefault();
            toggleTeaserManagerCategoryFilter();
        });
    });

    function toggleTeaserManagerCategoryFilter() {
        var display, cookieval
        if (getCookie(filterName) == 1) {
            display = 'none'
            cookieval = 0
        } else {
            display = 'block'
            cookieval = 1
        }
        $('#filter-container').css('display', display);
        setCookie(filterName, cookieval, 365);
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

});