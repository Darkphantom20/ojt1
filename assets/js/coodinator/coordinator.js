// Coordinator Dashboard Page Scripts

function renderLiveFeed(events) {
  const container = document.getElementById('liveActivityTimeline');
  if (!container) return;

  let html = '';
  if (Array.isArray(events) && events.length > 0) {
    events.forEach(event => {
      const icon = event.icon ? event.icon : 'fas fa-clock bg-gray';
      const time = event.time ? event.time : '';
      const student = event.student ? event.student : 'Unknown';
      const text = event.event ? event.event : '';

      html += '<div>' +
              '<i class="' + icon + '"></i>' +
              '<div class="timeline-item">' +
              '<span class="time"><i class="far fa-clock"></i> ' + time + '</span>' +
              '<h3 class="timeline-header border-0"><strong>' +
              $('<div>').text(student).html() + '</strong> ' +
              $('<div>').text(text).html() + '</h3>' +
              '</div>' +
              '</div>';
    });
  } else {
    html += '<div>' +
            '<i class="far fa-clock bg-gray"></i>' +
            '<div class="timeline-item">' +
            '<h3 class="timeline-header border-0 text-muted">No recent activity</h3>' +
            '</div>' +
            '</div>';
  }

  html += '<div><i class="far fa-clock bg-gray"></i></div>';
  container.innerHTML = html;
}

function updateLiveFeed() {
  $.ajax({
    url: 'live_activity_feed.php',
    method: 'GET',
    dataType: 'json',
    cache: false,
    success(data) {
      if (data && Array.isArray(data.feed)) {
        renderLiveFeed(data.feed);
      }
    },
    error() {
      console.warn('Live feed refresh failed');
    }
  });
}

function renderAllActivity(events) {
  const container = document.getElementById('allActivityContent');
  if (!container) return;

  let html = '';
  if (Array.isArray(events) && events.length > 0) {
    events.forEach(event => {
      const icon = event.icon ? event.icon : 'fas fa-history bg-info';
      const time = event.time ? event.time : '';
      const student = event.student ? event.student : 'Unknown';
      const text = event.event ? event.event : '';

      html += '<div class="list-group-item list-group-item-action flex-column align-items-start">' +
              '<div class="d-flex w-100 justify-content-between align-items-center">' +
              '<div class="d-flex align-items-center">' +
              '<span class="badge badge-light mr-2"><i class="' + icon + '"></i></span>' +
              '<h5 class="mb-1 mb-0">' + $('<div>').text(student).html() + '</h5>' +
              '</div>' +
              '<small class="text-muted">' + $('<div>').text(time).html() + '</small>' +
              '</div>' +
              '<p class="mb-1 text-secondary">' + $('<div>').text(text).html() + '</p>' +
              '</div>';
    });
  } else {
    html = '<div class="list-group-item text-muted">No activity records found.</div>';
  }
  container.innerHTML = html;
}

function updateAllActivity() {
  $.ajax({
    url: 'live_activity_feed.php',
    method: 'GET',
    data: { all: 1 },
    dataType: 'json',
    cache: false,
    success(data) {
      if (data && Array.isArray(data.feed)) {
        renderAllActivity(data.feed);
      }
    },
    error() {
      console.warn('All activity refresh failed');
    }
  });
}

function renderOverviewChart(labels, hoursData, progressData) {
  const chartCanvas = document.getElementById('coordinatorOverviewChart');
  if (!chartCanvas) {
    console.warn('Coordinator overview chart canvas not found');
    return;
  }
  if (typeof Chart === 'undefined') {
    console.warn('Chart.js library is not loaded');
    return;
  }

  const ctx = chartCanvas.getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Monthly Hours Worked',
          data: hoursData,
          backgroundColor: 'rgba(54, 162, 235, 0.75)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1,
        },
        {
          label: 'Cumulative Hours',
          data: progressData,
          type: 'line',
          fill: false,
          borderColor: 'rgba(255, 99, 132, 1)',
          backgroundColor: 'rgba(255, 99, 132, 0.5)',
          pointRadius: 4,
          pointHoverRadius: 6,
          lineTension: 0.2,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        xAxes: [{
          categoryPercentage: 0.8,
          barPercentage: 0.9,
        }],
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
            },
            scaleLabel: {
              display: true,
              labelString: 'Hours'
            }
          }
        ]
      },
      legend: {
        display: true,
        position: 'top'
      },
      tooltips: {
        mode: 'index',
        intersect: false,
        callbacks: {
          label: function(tooltipItem, data) {
            const label = data.datasets[tooltipItem.datasetIndex].label || '';
            const value = tooltipItem.yLabel !== undefined ? tooltipItem.yLabel : tooltipItem.value;
            return label + ': ' + value + ' h';
          }
        }
      },
      hover: {
        mode: 'index',
        intersect: false
      }
    }
  });
}

$(document).ready(function() {
  $('.preloader').fadeOut();

  renderOverviewChart(
    window.coordinatorMonthlyLabels || [],
    window.coordinatorMonthlyData || [],
    window.coordinatorMonthlyCumulative || []
  );
  setInterval(updateLiveFeed, 15000);
  $('#allActivityModal').on('show.bs.modal', function () {
    updateAllActivity();
  });
});
