<?php
$rootPath = '/ojt1/';
?>
<script src="<?= $rootPath ?>plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?= $rootPath ?>plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="<?= $rootPath ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="<?= $rootPath ?>plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="<?= $rootPath ?>plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="<?= $rootPath ?>plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="<?= $rootPath ?>plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="<?= $rootPath ?>plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="<?= $rootPath ?>plugins/moment/moment.min.js"></script>
<script src="<?= $rootPath ?>plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="<?= $rootPath ?>plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="<?= $rootPath ?>plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="<?= $rootPath ?>plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= $rootPath ?>dist/js/adminlte.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="<?= $rootPath ?>dist/js/pages/dashboard.js"></script>
<script>
(function() {
  if (!window.location.pathname.includes('/students/')) {
    return;
  }

  function sanitizeText(value) {
    if (typeof value !== 'string') {
      return value;
    }
    return value.trim().replace(/[<>"'`]/g, '');
  }

  function sanitizeFields(form) {
    var fields = form.querySelectorAll('input[type="text"], input[type="email"], input[type="search"], textarea');
    fields.forEach(function(field) {
      field.value = sanitizeText(field.value);
    });
  }

  document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function() {
      sanitizeFields(form);
    });
  });

  document.querySelectorAll('input[type="text"], input[type="email"], input[type="search"], textarea').forEach(function(field) {
    field.addEventListener('blur', function() {
      field.value = sanitizeText(field.value);
    });
  });

})();
</script>
<script src="<?= $rootPath ?>assets/js/students.js"></script>