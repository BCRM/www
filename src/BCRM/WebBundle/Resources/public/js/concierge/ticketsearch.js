var TicketSearch = function (form) {
    var searchTimer;
    var searchTerm;
    var resultContainer = $('<div />').insertAfter(form);
    var resultTemplate = $(form.data('result-template')).text();

    function printTicket(ev) {
        ev.preventDefault();
        var a = $(ev.target).closest('a');
        var span = $('<span>Printing …</span>').insertAfter(a);
        $.ajax({
            url: a.attr('href'),
            method: a.data('method'),
            success: function (response) {
                span.text('OK');
            }
        });

    }

    function executeSearch() {
        if (searchTerm.length < 3) return;
        $.ajax({
            url: form.attr('action'),
            data: {q: searchTerm},
            success: function (response) {
                resultContainer.empty();
                resultContainer.append(Mustache.render(resultTemplate, response));
                resultContainer.find('a[rel=print]').click(printTicket);
            }
        });
    }

    function search(term) {
        if (searchTimer) {
            window.clearTimeout(searchTimer);
            searchTimer = null;
        }
        searchTimer = window.setTimeout(executeSearch, 250);
        searchTerm = term;
    }

    form.find('input[name=q]').keyup(function (ev) {
        var input = $(ev.target);
        search(input.val())
    });
};
