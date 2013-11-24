var Stats = function (parent) {

    var checkinStats = $('<div id="checkin-stats" style="height: 350px; width: 100%;"></div>').appendTo(parent);

    // Fetch checkin stats
    function updateCheckinStats() {
        $.ajax({
            url: '/stats.json',
            method: 'GET',
            success: function (response) {
                var data = google.visualization.arrayToDataTable([
                    ['Tag', 'Checkins'],
                    ['Samstag', response.stats.checkins.sa],
                    ['Sonntag', response.stats.checkins.su]
                ]);

                var options = {
                    title: 'Checkins pro Tag'
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('checkin-stats'));
                chart.draw(data, options);
            }
        });
    }

    function updateStats() {
        updateCheckinStats();
    }

    updateStats();
};
