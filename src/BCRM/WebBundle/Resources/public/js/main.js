Modernizr.load([
    {
        test: Modernizr.fontface,
        yep: 'http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js',
        callback: function (url, result, key) {
            if (!result) return;
            WebFont.load({
                google: {
                    families: ['Ubuntu:400,300,300italic,400italic,700,700italic']
                }
            });
        }
    },
    // Slider
    {
        test: Modernizr.mq('(min-width: 950px)'),
        yep: [ '/bundles/bcrmweb/vendor/bxslider/jquery.bxslider.css', '/bundles/bcrmweb/css/slider.css', '/bundles/bcrmweb/vendor/bxslider/jquery.bxslider.js' ],
        callback: function (url, result, key) {
            if (!result || key < 2) return;
            $.ajax(
                '/c/Slider/Index',
                {
                    accepts: 'text/html',
                    dataType: 'html',
                    success: function (data) {
                        var slider = $('<div class="visual slider" />').html(data);
                        $('div.main').prepend(slider);
                        $('.slider ul').bxSlider(
                            {
                                captions: true,
                                pager: false
                            }
                        );
                    }
                }
            );
        }
    }
]);
