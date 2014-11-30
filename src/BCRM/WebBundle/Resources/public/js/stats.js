"use strict";

var Stats = function (parent) {

    $('<div id="stats-checkin" style="height: 350px; width: 100%;"></div>').appendTo(parent);
    $('<div id="stats-checkin-hour" style="height: 350px; width: 100%;"></div>').appendTo(parent);
    $('<div id="stats-noshows" style="height: 350px; width: 100%;"></div>').appendTo(parent);
    $('<div id="stats-unregistrations" style="height: 350px; width: 100%;"></div>').appendTo(parent);
    $('<div id="stats-checkin-unique" style="height: 350px; width: 100%;"></div>').appendTo(parent);

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

                // Checkin per Hour
                var dataCheckinsHour = new google.visualization.DataTable();
                dataCheckinsHour.addColumn('string', 'Uhrzeit');
                dataCheckinsHour.addColumn('number', 'Samstag');
                dataCheckinsHour.addColumn('number', 'Sonntag');
                dataCheckinsHour.addRows(49);
                var labelindex = [];
                var n = 0;

                for (var h = 0; h < 8; h++) {
                    for (var m = 0; m < 6; m++) {
                        var slot = (h + 8) + ":" + (m == 0 ? "00" : (m * 10));
                        labelindex[slot.replace(":", "")] = n;
                        dataCheckinsHour.setValue(n, 0, slot);
                        n++;
                    }
                }
                dataCheckinsHour.setValue(n, 0, "16:00");
                labelindex["1600"] = n;
                var max = 0;
                var saSum = 0;
                for (var slot in response.stats.checkins.sa_hour) {
                    saSum += response.stats.checkins.sa_hour[slot];
                    if (max < saSum) {
                        max = saSum;
                    }
                    dataCheckinsHour.setValue(labelindex[slot], 1, saSum);
                }
                var soSum = 0;
                for (var slot in response.stats.checkins.su_hour) {
                    soSum += response.stats.checkins.su_hour[slot];
                    if (max < soSum) {
                        max = soSum;
                    }
                    dataCheckinsHour.setValue(labelindex[slot], 2, soSum);
                }

                var optionsCheckinHour = {
                    title: 'Checkins pro Stunde',
                    interpolateNulls: true,
                    vAxis: {
                        minValue: 0,
                        maxValue: max
                    },
                    colors: chartColors
                };

                var chartCheckinHour = new google.visualization.LineChart(document.getElementById('stats-checkin-hour'));
                chartCheckinHour.draw(dataCheckinsHour, optionsCheckinHour);

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

                // Unregistrations per Day
                var dataUnregistrationsDay = new google.visualization.DataTable();
                dataUnregistrationsDay.addColumn('string', 'Tag');
                dataUnregistrationsDay.addColumn('number', 'Samstag');
                dataUnregistrationsDay.addColumn('number', 'Sonntag');

                for (var slot in response.stats.unregistrations) {
                    dataUnregistrationsDay.addRow(
                        [slot, response.stats.unregistrations[slot]['sa'], response.stats.unregistrations[slot]['su']]
                    );
                }

                var optionsunregistrationDay = {
                    title: 'Stornierungen pro Tag',
                    vAxis: {
                        minValue: 0
                    },
                    colors: chartColors
                };

                var chartunregistrationDay = new google.visualization.ColumnChart(document.getElementById('stats-unregistrations'));
                chartunregistrationDay.draw(dataUnregistrationsDay, optionsunregistrationDay);
            }
        });
    }

    updateCheckinStats();
};
