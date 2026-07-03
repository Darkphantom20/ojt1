(function () {
  var config = window.studentQrConfig || {};
  var studentId = config.studentId || '';
  var qrContainer = document.getElementById('qrcode');
  var downloadBtn = document.getElementById('downloadBtn');

  if (!qrContainer || typeof QRCode === 'undefined') {
    return;
  }

  new QRCode(qrContainer, {
    text: studentId || 'UNKNOWN',
    width: 210,
    height: 210,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
  });

  if (downloadBtn) {
    downloadBtn.addEventListener('click', function () {
      var img = qrContainer.querySelector('img');
      if (!img) {
        alert('QR code not ready yet.');
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
})();
