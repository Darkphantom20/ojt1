// Students Page Scripts

function refreshStudentProgress() {
  $('#student-progress-table').load(window.location.href + ' #student-progress-table');
}

$(function () {
  $('#refresh-student-progress').on('click', refreshStudentProgress);
  setInterval(refreshStudentProgress, 10000);
});
