Modernizr.load([
    {
        test: Modernizr.fontface,
        yep: '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js',
        callback: function (url, result, key) {
            if (!result) return;
            WebFont.load({
                google: {
                    families: ['Ubuntu:400,300,300italic,400italic,700,700italic']
                }
            });
        }
    }
]);

$(document).ready(function () {
    $('button[type=submit]').append('<span class="arrow"></span>');
    $('.button').append('<span class="arrow"></span>');
});
