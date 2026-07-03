<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Student Dashboard';
include 'includes/header.php';
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Student OJT Dashboard</h1>
          </div><div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Student Dashboard</li>
            </ol>
          </div></div></div></div>
    <section class="content">
      <div class="container-fluid">
        
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box bg-dept-default">
              <div class="inner">
                <h3>400</h3>
                <p>Required OJT Hours</p>
              </div>
              <div class="icon">
                <i class="fas fa-briefcase"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box bg-dept-default">
              <div class="inner">
                <h3>125.5</h3>
                <p>Hours Completed</p>
              </div>
              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box bg-dept-default">
              <div class="inner">
                <h3>274.5</h3>
                <p>Hours Remaining</p>
              </div>
              <div class="icon">
                <i class="fas fa-hourglass-half"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box bg-dept-default">
              <div class="inner">
                <h3>Timed Out</h3>
                <p>Current Status</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-clock"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          
          <div class="col-md-4">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock"></i> Daily Time Record</h3>
              </div>
              <div class="card-body text-center">
                <p class="lead" id="currentTime">00:00:00</p>
                <hr>
                
                <h5 class="mb-3">Morning Shift</h5>
                <div class="row mb-4">
                  <div class="col-6">
                    <button class="btn btn-outline-primary btn-block"><i class="fas fa-sign-in-alt"></i> AM In</button>
                    <small class="text-muted">Logged: 08:02 AM</small>
                  </div>
                  <div class="col-6">
                    <button class="btn btn-outline-warning btn-block"><i class="fas fa-sign-out-alt"></i> AM Out</button>
                    <small class="text-muted">Logged: 12:05 PM</small>
                  </div>
                </div>

                <h5 class="mb-3">Afternoon Shift</h5>
                <div class="row">
                  <div class="col-6">
                    <button class="btn btn-success btn-block"><i class="fas fa-sign-in-alt"></i> PM In</button>
                    <small class="text-muted">--:--</small>
                  </div>
                  <div class="col-6">
                    <button class="btn btn-danger btn-block" disabled><i class="fas fa-sign-out-alt"></i> PM Out</button>
                    <small class="text-muted">--:--</small>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="col-md-8">
            <div class="card card-success progress-chart-card">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Hours Progression</h3>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="progressChart" style="width: 100%; min-height: 250px; height: 100%; max-width: 100%; display: block;"></canvas>
                </div>
              </div>
            </div>
          </div>

        </div>
        </div></section>
    </div>
  <?php include 'includes/footer.php'; ?>

  <aside class="control-sidebar control-sidebar-dark">
  </aside>
  </div>
<?php include 'includes/script.php'; ?>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="plugins/sparklines/sparkline.js"></script>
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="dist/js/adminlte.js"></script>

<script>
  $(function () {
    
    function updateClock() {
      var now = new Date();
      var time = now.toLocaleTimeString();
      $('#currentTime').text(time);
    }
    setInterval(updateClock, 1000);
    updateClock();

    
    var ctx = document.getElementById('progressChart').getContext('2d');
    
    
    var progressData = {
      labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
      datasets: [
        {
          label: 'Cumulative Hours Completed',
          backgroundColor: 'rgba(60,141,188,0.2)',
          borderColor: 'rgba(60,141,188,1)',
          pointRadius: 4,
          pointColor: '#3b8bba',
          pointStrokeColor: 'rgba(60,141,188,1)',
          pointHighlightFill: '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data: [20, 42, 65, 88, 110, 125.5] 
        }
      ]
    };

    var progressOptions = {
      maintainAspectRatio: false,
      responsive: true,
      legend: {
        display: true,
        position: 'top',
        labels: {
          boxWidth: 12,
          fontSize: 12,
          fontColor: '#000',
          padding: 12
        }
      },
      tooltips: {
        mode: 'index',
        intersect: false,
        callbacks: {
          label: function(tooltipItem, data) {
            var dataset = data.datasets[tooltipItem.datasetIndex];
            return dataset.label + ': ' + tooltipItem.yLabel + 'h';
          }
        }
      },
      elements: {
        line: {
          tension: 0.25,
          borderWidth: 3,
        },
        point: {
          radius: 4,
          hoverRadius: 6,
          hitRadius: 10,
        }
      },
      layout: {
        padding: {
          top: 10,
          right: 10,
          left: 8,
          bottom: 6
        }
      },
      scales: {
        xAxes: [{
          gridLines: { display: false },
          ticks: {
            autoSkip: true,
            maxRotation: 0,
            minRotation: 0,
            fontSize: 12,
          }
        }],
        yAxes: [{
          gridLines: { display: true, color: 'rgba(0,0,0,0.08)' },
          ticks: {
            beginAtZero: true,
            suggestedMax: 400,
            stepSize: 50,
            fontSize: 12,
            callback: function(value) { return value + 'h'; }
          }
        }]
      }
    };

    
    new Chart(ctx, {
      type: 'line',
      data: progressData,
      options: progressOptions
    });
  });
</script>
</body>
</html>