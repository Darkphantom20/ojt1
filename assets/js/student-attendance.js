(function () {
  var config = window.studentAttendanceConfig || {};
  window.attendanceChartData = {
    labels: Array.isArray(config.chartLabels) ? config.chartLabels : [],
    completed: Array.isArray(config.chartData) ? config.chartData : [],
    required: config.required != null ? config.required : 0
  };
})();
