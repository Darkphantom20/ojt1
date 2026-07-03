(function () {
  var config = window.studentSettingsConfig || {};
  var studentId = config.studentId || '';

  function renderStudentQr() {
    var holder = document.getElementById('qrCodeHolder');
    if (!holder || typeof QRCode === 'undefined') {
      return null;
    }

    holder.innerHTML = '';
    var code = new QRCode(holder, {
      text: studentId || 'UNKNOWN',
      width: 220,
      height: 220,
      colorDark: '#000000',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.H,
    });

    var qrStudentId = document.getElementById('qrStudentId');
    if (qrStudentId) {
      qrStudentId.textContent = studentId;
    }

    return code;
  }

  $(document).ready(function () {
    $('.preloader').fadeOut();

    var showQrBtn = document.getElementById('showQrBtn');
    var downloadQrBtn = document.getElementById('downloadQrBtn');

    if (showQrBtn) {
      showQrBtn.addEventListener('click', function () {
        renderStudentQr();
        $('#qrModal').modal('show');
      });
    }

    if (downloadQrBtn) {
      downloadQrBtn.addEventListener('click', function () {
        var holder = document.getElementById('qrCodeHolder');
        var img = holder ? holder.querySelector('img') : null;
        if (!img || !img.src) {
          alert('QR code is not ready yet.');
          return;
        }

        var link = document.createElement('a');
        link.href = img.src;
        link.download = 'student-qr-' + (studentId ? studentId.replace(/[^A-Za-z0-9]+/g, '-') : 'unknown') + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });
    }
  });
})();
