Modernizr.load([
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
                        var visual = $('div.visual');
                        visual.html(data);
                        visual.addClass('slider');
                        visual.find('img').each(function(n, img) { var i = $(img); i.attr('title', i.attr('alt')); });
                        $('.slider ul').bxSlider(
                            {
                                captions: true,
                                pager: false,
                                auto: true
                            }
                        );
                    }
                }
            );
        }
    }
]);
