var Stats = function (parent) {

    var checkinStats = $('<div id="checkin-stats" style="height: 350px; width: 100%;"></div>').appendTo(parent);
    var checkinUniqueStats = $('<div id="checkin-unique-stats" style="height: 350px; width: 100%;"></div>').appendTo(parent);

    // Fetch checkin stats
    function updateCheckinStats() {
        $.ajax({
            url: '/stats.json',
            method: 'GET',
            success: function (response) {
                // Checkin
                var data = google.visualization.arrayToDataTable([
                    ['Tag', 'Checkins'],
                    ['Samstag', response.stats.checkins.sa],
                    ['Sonntag', response.stats.checkins.su]
                ]);

                var options = {
                    title: 'Checkins pro Tag',
                    vAxis: {
                        minValue: 0
                    }
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('checkin-stats'));
                chart.draw(data, options);

                // Unique Checkins
                var data2 = google.visualization.arrayToDataTable([
                    ['Tag', 'Checkins'],
                    ['Nur Samstag', response.stats.checkins.only_sa],
                    ['Nur Sonntag', response.stats.checkins.only_su],
                    ['Beide Tage', response.stats.checkins.both]
                ]);

                var options2 = {
                    title: 'Checkins pro Tag (Eindeutig)',
                    vAxis: {
                        minValue: 0
                    }
                };

                var chart2 = new google.visualization.ColumnChart(document.getElementById('checkin-unique-stats'));
                chart2.draw(data2, options2);
            }
        });
    }

    function updateStats() {
        updateCheckinStats();
    }

    updateStats();
};
