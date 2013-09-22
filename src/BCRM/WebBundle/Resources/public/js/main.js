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
    }
]);
