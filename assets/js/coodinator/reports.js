function renderLiveFeed(events) {
  const container = document.getElementById('liveActivityTimeline');
  if (!container) return;

  let html = '';
  if (Array.isArray(events) && events.length > 0) {
    events.forEach(event => {
      const icon = event.icon ? event.icon : 'fas fa-file-excel bg-success';
      const time = event.time ? event.time : '';
      const label = event.student ? event.student : 'Export';
      const text = event.event ? event.event : '';
      const details = event.details ? event.details : '';

      html += '<div>' +
              '<i class="' + icon + '"></i>' +
              '<div class="timeline-item">' +
              '<span class="time"><i class="far fa-clock"></i> ' + $('<div>').text(time).html() + '</span>' +
              '<h3 class="timeline-header border-0"><strong>' +
              $('<div>').text(label).html() + '</strong> ' +
              $('<div>').text(text).html() + '</h3>' +
              (details ? '<div class="timeline-body text-sm text-secondary">' + $('<div>').text(details).html() + '</div>' : '') +
              '</div>' +
              '</div>';
    });
  } else {
    html += '<div>' +
            '<i class="fas fa-file-excel bg-gray"></i>' +
            '<div class="timeline-item">' +
            '<h3 class="timeline-header border-0 text-muted">No export history</h3>' +
            '<div class="timeline-body text-sm text-secondary">Export a report to populate this timeline.</div>' +
            '</div>' +
            '</div>';
  }

  html += '<div>' +
          '<i class="fas fa-clock bg-gray"></i>' +
          '<div class="timeline-item">' +
          '<span class="time"><i class="far fa-clock"></i> ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + '</span>' +
          '<h3 class="timeline-header border-0">History last refreshed</h3>' +
          '</div>' +
          '</div>';

  container.innerHTML = html;
}

function updateLiveFeed() {
  $.ajax({
    url: '/ojt1/coodinator/live_activity_feed.php',
    method: 'GET',
    dataType: 'json',
    cache: false,
    success(data) {
      const feed = data && Array.isArray(data.feed) ? data.feed : [];
      renderLiveFeed(feed.slice(0, 1));
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
      const icon = event.icon ? event.icon : 'fas fa-file-excel bg-success';
      const time = event.time ? event.time : '';
      const label = event.student ? event.student : 'Export';
      const text = event.event ? event.event : '';
      const details = event.details ? event.details : '';

      html += '<div class="list-group-item list-group-item-action flex-column align-items-start">' +
              '<div class="d-flex w-100 justify-content-between align-items-center">' +
              '<div class="d-flex align-items-center">' +
              '<span class="badge badge-pill badge-light mr-2"><i class="' + icon + '"></i></span>' +
              '<div>' +
              '<h5 class="mb-1 mb-0">' + $('<div>').text(label).html() + '</h5>' +
              '<small class="text-muted">' + $('<div>').text(text).html() + '</small>' +
              '</div>' +
              '</div>' +
              '<small class="text-muted">' + $('<div>').text(time).html() + '</small>' +
              '</div>' +
              (details ? '<p class="mb-1 text-secondary">' + $('<div>').text(details).html() + '</p>' : '') +
              '</div>';
    });
  } else {
    html = '<div class="list-group-item text-muted">No export history available.</div>';
  }
  container.innerHTML = html;
}

function updateAllActivity() {
  $.ajax({
    url: '/ojt1/coodinator/live_activity_feed.php',
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

// ===== Secure, accurate, debounced search for the report table =====
function debounce(func, delay) {
  let timer;
  return function() {
    const args = arguments;
    clearTimeout(timer);
    timer = setTimeout(() => func.apply(this, args), delay);
  };
}

function filterReportTable() {
  const searchInput = document.getElementById('reportSearch');
  if (!searchInput) return;
  const query = searchInput.value.trim().toLowerCase();
  // Escape special regex characters to prevent injection
  const safeQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const rows = document.querySelectorAll('#reportTable tbody tr');
  rows.forEach(row => {
    const name = row.getAttribute('data-student-name') || '';
    const id = row.getAttribute('data-student-id') || '';
    const match = safeQuery === '' || name.indexOf(safeQuery) !== -1 || id.indexOf(safeQuery) !== -1;
    row.style.display = match ? '' : 'none';
  });
}

$(document).ready(function() {
  setTimeout(function() {
    $('.preloader').fadeOut();
  }, 1000);

  updateLiveFeed();
  setInterval(updateLiveFeed, 15000);

  $('#allActivityModal').on('show.bs.modal', function () {
    updateAllActivity();
  });

  // --- Report search improvements ---
  const searchInput = document.getElementById('reportSearch');
  const clearBtn = document.getElementById('clearSearchBtn');
  if (searchInput) {
    // Apply debounced filter on input
    searchInput.addEventListener('input', debounce(filterReportTable, 300));

    // Run initial filter if search has a value
    if (searchInput.value.trim() !== '') {
      filterReportTable();
    }

    // Clear button
    if (clearBtn) {
      clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterReportTable();
      });
    }
  }
});
