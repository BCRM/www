var TicketSearch = function (form) {
    var searchTimer;
    var searchTerm;
    var resultContainer = $('<div />').insertAfter(form);
    var resultTemplate = $(form.data('result-template')).text();

    function executeSearch() {
        if (searchTerm.length < 3) return;
        $.ajax({
            url: form.attr('action'),
            data: {q: searchTerm},
            success: function (response) {
                resultContainer.empty();
                resultContainer.append(Mustache.render(resultTemplate, response));
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
