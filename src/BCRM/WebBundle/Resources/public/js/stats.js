var Stats = function (parent) {

    var checkinStats = $('<div id="stats-checkin" style="height: 350px; width: 100%;"></div>').appendTo(parent);
    var nowShowsStats = $('<div id="stats-noshows" style="height: 350px; width: 100%;"></div>').appendTo(parent);
    var checkinUniqueStats = $('<div id="stats-checkin-unique" style="height: 350px; width: 100%;"></div>').appendTo(parent);

    var chartColors = ['#2b6dc5', '#78cf2f', '#ffd300'];

    // Fetch checkin stats
    function updateCheckinStats() {
        $.ajax({
            url: '/stats.json',
            method: 'GET',
            success: function (response) {
                // Checkin
                var data = google.visualization.arrayToDataTable([
                    ['Tag', 'Samstag', 'Sonntag'],
                    ['', response.stats.checkins.sa, response.stats.checkins.su]
                ]);

                var options = {
                    title: 'Checkins pro Tag',
                    vAxis: {
                        minValue: 0
                    },
                    colors: chartColors
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('stats-checkin'));
                chart.draw(data, options);

                // Unique Checkins
                var dataUnique = google.visualization.arrayToDataTable([
                    ['Tag', 'Nur Samstag', 'Nur Sonntag', 'Beide Tage'],
                    [
                        '',
                        response.stats.checkins.unique.sa,
                        response.stats.checkins.unique.su,
                        response.stats.checkins.unique.both
                    ]
                ]);

                var optionsUnique = {
                    title: 'WiederholungsCamper?',
                    vAxis: {
                        minValue: 0
                    },
                    colors: chartColors
                };

                var chartUnique = new google.visualization.ColumnChart(document.getElementById('stats-checkin-unique'));
                chartUnique.draw(dataUnique, optionsUnique);

                // No-Shows
                var dataNoShows = google.visualization.arrayToDataTable([
                    ['Tag', 'Samstag', 'Sonntag'],
                    [
                        '',
                        response.stats.checkins.noshows.sa / (response.stats.checkins.sa + response.stats.checkins.noshows.sa),
                        response.stats.checkins.noshows.su / (response.stats.checkins.su + response.stats.checkins.noshows.su)
                    ]
                ]);

                var formatter = new google.visualization.NumberFormat({pattern: '#%'});
                formatter.format(dataNoShows, 1);
                formatter.format(dataNoShows, 2);

                var optionsNoShows = {
                    title: 'No-Shows',
                    vAxis: {
                        minValue: 0,
                        maxValue: 1,
                        format: '#%'
                    },
                    colors: chartColors
                };

                var chartNoShows = new google.visualization.ColumnChart(document.getElementById('stats-noshows'));
                chartNoShows.draw(dataNoShows, optionsNoShows);
            }
        });
    }

    updateCheckinStats();
};
