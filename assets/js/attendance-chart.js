// attendance-chart.js
$(function () {
  const dataObj = window.attendanceChartData || {};
  const labels = dataObj.labels || [];
  const completed = dataObj.completed || [];
  const required = dataObj.required || 0;
  const hasProgress = completed.some(v => v > 0);
  const ctxEl = document.getElementById('hourLineChart');
  if (!ctxEl) return;
  const ctx = ctxEl.getContext('2d');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Completed Hours',
          data: completed,
          borderColor: '#28a745',
          backgroundColor: hasProgress ? 'rgba(40, 167, 69, 0.25)' : 'rgba(0, 0, 0, 0.08)',
          fill: hasProgress,
          tension: 0.2,
          showLine: true,
          pointRadius: 4,
          spanGaps: true,
          borderWidth: 2
        },
        {
          label: 'Required ' + required + 'h',
          data: new Array(labels.length).fill(required),
          borderColor: '#ffc107',
          borderDash: [5, 5],
          fill: false,
          pointRadius: 0
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: required,
          title: { display: true, text: 'Hours' }
        },
        x: {
          title: { display: true, text: 'Week' }
        }
      }
    }
  });
});

$(document).ready(function() {
  $('.preloader').fadeOut();
});
