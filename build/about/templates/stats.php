<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
?>
<div>
    <?php echo $words->get("StatsHead") ?>
</div>
<?php
/*
 * Puts a set of divs onto the page that contain the generated images
 */
function drawCharts($label, $headlineCode, $words)
{
    ?>
    <div class="row mb-1">
        <div class="col-12 mt-3">
            <span class="h3"><?php echo $words->get($headlineCode) ?></span>
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header"><h4 class="m-0"><?php echo $words->get('StatsHeadCol1') ?></h4></div>
                <div class="card-body">
                    <canvas id="<?php echo $label ?>-alltime"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header"><h4 class="m-0"><?php echo $words->get('StatsHeadCol2') ?></h4></div>
                <div class="card-body">
                    <canvas id="<?php echo $label ?>-last2month"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php
    echo $words->flushBuffer();
}
?>
    <?php
    drawCharts('members', 'StatsMembersAlltime', $words);
    drawCharts('newMembers', 'StatsNewMembersAlltime', $words);
    drawCharts('newMembersPercent', 'StatsPercentNewMembersLast', $words);
    drawCharts('membersLoggedIn', 'StatsLoginAlltime', $words);
    drawCharts('newMembersLoggedInPercent', 'StatsPercentLoginAlltime', $words);
    drawCharts('membersWithPositiveComments', 'StatsTrustAlltime', $words);
    drawCharts('messageSent', 'StatsMessagesAlltime', $words);
    drawCharts('messageRead', 'StatsMessagesAlltime', $words);
    ?>
<div class="row mb-1">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header"><h4 class="m-0"><?php echo $words->get("StatsLastLogin") ?></h4></div>
            <div class="card-body">
                <canvas id="logins" width="100" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header"><h4 class="m-0"><?php echo $words->get("StatsMemberCountry") ?></h4></div>
            <div class="card-body">
                <canvas id="countries" width="100" height="130"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row mb-1">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header"><h4 class="m-0"><?php echo $words->get("StatsLanguages") ?></h4></div>
            <div class="card-body">
                <canvas id="languages" width="100" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header"><h4 class="m-0"><?php echo $words->get("StatsPreferredLanguages") ?></h4></div>
            <div class="card-body">
                <canvas id="preferred" width="100" height="130"></canvas>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        function createLineChart(data, duration) {
            let stats = data.statistics;
            Object.keys(stats).forEach(
                function(key) {
                    let ctx = document.getElementById(key + "-" + duration).getContext('2d');
                    let barChartData = {
                        labels: data.labels,
                        datasets: [{
                            label: 'Data',
                            backgroundColor: '#999',
                            borderColor: '#888',
                            pointRadius: .5,
                            data: stats[key]
                        }]
                    };
                    new Chart(ctx, {
                        type: 'line',
                        data: barChartData,
                        options: {
                            title: {
                                display: false,
                            },
                            legend: {
                                display: false
                            }

                        }
                    });
                }
            );
        }

        function createLanguageChart(data) {
            let labels = [];
            let counts = [];
            let i = 0;
            Object.keys(data).forEach(
                function(key) {
                    labels[i] = key;
                    counts[i] = data[key];
                    i++;
                }
            );
            let ctx = document.getElementById('languages').getContext('2d');
            let barChartData = {
                labels: labels,
                datasets: [{
                    label: 'Languages',
                    backgroundColor: ["#BCE02E", "#E0642E","#E0D62E","#2E97E0","#B02EE0", "#E02E75", "#5CE02E","#E0B02E","#FF3179","#374AF9"],
                    data: counts
                }]
            };
            new Chart(ctx, {
                type: 'bar',
                data: barChartData,
                options: {
                    responsive: true,
                    title: {
                        display: false,
                    },
                    legend: {
                        display: false,
                        position: 'bottom'
                    }
                }
            });
        }

        function createPieChart(data, canvas) {
            let labels = [];
            let counts = [];
            let i = 0;
            Object.keys(data).forEach(
                function(key) {
                    labels[i] = key;
                    counts[i] = data[key];
                    i++;
                }
            );
            let ctx = document.getElementById(canvas).getContext('2d');
            let pieChartData = {
                labels: labels,
                datasets: [{
                    label: 'Data',
                    backgroundColor: ["#BCE02E", "#E0642E","#E0D62E","#2E97E0","#B02EE0", "#E02E75", "#5CE02E","#E0B02E","#FF3179","#374AF9",
                    "#E105A7", "#58A29E", "#4ADB83", "#916184", "#0EB109"],
                    data: counts
                }]
            };
            console.log(pieChartData);
            new Chart(ctx, {
                type: 'pie',
                data: pieChartData,
                options: {
                    responsive: true,
                    title: {
                        display: false,
                    },
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            });
        }

        $.post("/stats/data/alltime",
            function (data)
            {
                createLineChart(data, 'alltime');
            }
        );
        $.post("/stats/data/last2month",
            function (data)
            {
                createLineChart(data, 'last2month');
            }
        );
        $.post("/stats/data/other",
            function (data)
            {
                console.log(data);
                createLanguageChart(data.languages);
                createPieChart(data.preferred, 'preferred');
                createPieChart(data.logins, 'logins');
                createPieChart(data.countries, 'countries');
            }
        );
    });
</script>

