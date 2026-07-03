(function () {
  var config = window.studentLogbookConfig || {};
  var calendarMonth = parseInt(config.calendarMonth, 10) || 1;
  var calendarYear = parseInt(config.calendarYear, 10) || new Date().getFullYear();
  var selectedDate = config.selectedDate || '';
  var entriesByDate = config.entriesByDate || {};
  var studentDepartment = config.studentDepartment || '';

  function getDepartmentTheme(dept) {
    var department = (dept || '').toString();
    if (department.indexOf('Information Systems') !== -1 || department.indexOf('Computer Science') !== -1) {
      return { bg: 'purple', text: 'white' };
    }
    if (department.indexOf('Business Administration') !== -1 || department.indexOf('Human Resource') !== -1 || department.indexOf('Agriculture Business') !== -1) {
      return { bg: 'yellow', text: 'black' };
    }
    if (department.indexOf('Criminal Justice') !== -1) {
      return { bg: 'red', text: 'white' };
    }
    if (department.indexOf('Agricultural Biosystem Engineering') !== -1) {
      return { bg: 'orange', text: 'white' };
    }
    if (department.indexOf('BSED') !== -1 || department.indexOf('BEED') !== -1) {
      return { bg: 'blue', text: 'white' };
    }
    if (department.indexOf('Forestry') !== -1 || department.indexOf('Agriculture') !== -1) {
      return { bg: 'green', text: 'white' };
    }
    return { bg: '#2f4b6f', text: '#2f4b6f' };
  }

  function formatDateYMD(d) {
    var m = d.getMonth() + 1;
    var day = d.getDate();
    return d.getFullYear() + '-' + (m < 10 ? '0' + m : m) + '-' + (day < 10 ? '0' + day : day);
  }

  function renderNow() {
    var now = new Date();
    var nowString = now.toLocaleString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
    var todayText = 'Today: ' + now.toISOString().slice(0, 10);
    var nowEl = document.getElementById('lb-now');
    var todayInfoEl = document.getElementById('lb-today-info');
    if (nowEl) {
      nowEl.textContent = nowString;
    }
    if (todayInfoEl) {
      todayInfoEl.textContent = todayText + (selectedDate === now.toISOString().slice(0, 10) ? ' (Selected)' : '');
    }
  }

  function renderCalendar() {
    var first = new Date(calendarYear, calendarMonth - 1, 1);
    var start = new Date(first);
    var dayOfWeek = start.getDay();
    start.setDate(start.getDate() - dayOfWeek);

    var titleEl = document.getElementById('lb-month-year');
    if (titleEl) {
      titleEl.textContent = first.toLocaleString('en-US', { month: 'long', year: 'numeric' });
    }

    var tableHtml = '<table class="table table-sm table-bordered mb-0 calendar-table"><thead><tr>' +
      ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(function (w) { return '<th class="text-center">' + w + '</th>'; }).join('') +
      '</tr></thead><tbody>';

    var current = new Date(start);
    var todayStr = formatDateYMD(new Date());
    for (var week = 0; week < 6; week++) {
      tableHtml += '<tr>';
      for (var dow = 0; dow < 7; dow++) {
        var inCurr = current.getMonth() === (calendarMonth - 1);
        var dateKey = formatDateYMD(current);
        var dayData = entriesByDate[dateKey];
        var entryInfo = '';
        if (dayData && dayData.entries && dayData.entries.length > 0) {
          entryInfo = '<div class="day-info">' + dayData.entries.length + ' log' + (dayData.entries.length > 1 ? 's' : '') + ' • ' + dayData.hours.toFixed(1) + 'h</div>';
        }
        var active = dateKey === selectedDate ? ' border border-primary' : '';
        var weekend = (dow === 0 || dow === 6) ? ' bg-light' : '';
        var todayHighlight = dateKey === todayStr ? ' bg-info' : '';
        var classNames = 'text-center align-top' + (entryInfo ? ' has-entries' : '');
        var cellStyle = 'padding: 0; vertical-align: top; position: relative; border-radius: 12px; box-shadow: 0 2px 8px var(--calendar-shadow); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background-color: ' + (inCurr ? 'var(--calendar-card-bg)' : 'var(--calendar-other-month-bg)') + '; border: 2px solid transparent;';
        if (weekend) {
          cellStyle += ' background: var(--calendar-weekend-bg);';
        }
        if (todayHighlight) {
          cellStyle += ' background: var(--calendar-today-bg); border-color: #007bff; transform: scale(1.05);';
        }
        if (active) {
          cellStyle += ' border-color: #28a745; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);';
        }

        tableHtml += '<td class="' + classNames + '" data-date="' + dateKey + '" style="cursor:pointer; ' + cellStyle + '" onmouseover="this.style.transform=\'scale(1.02)\'; this.style.boxShadow=\'0 6px 20px var(--calendar-hover-shadow)\'; this.style.zIndex=\'10\';" onmouseout="this.style.transform=\'' + (todayHighlight ? 'scale(1.05)' : 'scale(1)') + '\'; this.style.boxShadow=\'0 2px 8px var(--calendar-shadow)\'; this.style.zIndex=\'1\';">' +
          '<div class="calendar-day-cell">' +
            '<div class="day-number" style="color:' + (inCurr ? 'var(--calendar-text)' : 'var(--calendar-text-muted)') + ';">' + current.getDate() + '</div>' +
            entryInfo +
          '</div>' +
          '</td>';
        current.setDate(current.getDate() + 1);
      }
      tableHtml += '</tr>';
    }
    tableHtml += '</tbody></table>';
    var calendarGrid = document.getElementById('logbook-calendar-grid');
    if (calendarGrid) {
      calendarGrid.innerHTML = tableHtml;
      var dayCells = calendarGrid.querySelectorAll('td[data-date]');
      dayCells.forEach(function (td) {
        td.addEventListener('click', function () {
          var d = this.getAttribute('data-date');
          if (d) {
            showLogbookModal(d);
          }
        });
      });
    }
  }

  function showLogbookModal(date) {
    var modalDateEl = document.getElementById('modal-date');
    var modalBodyEl = document.getElementById('modal-body');
    var dayData = entriesByDate[date];

    if (modalDateEl) {
      modalDateEl.textContent = date;
    }

    if (dayData && dayData.entries && dayData.entries.length > 0) {
      var totalSeconds = dayData.seconds;
      var totalHours = dayData.hours;
      var totalH = Math.floor(totalSeconds / 3600);
      var totalM = Math.floor((totalSeconds % 3600) / 60);
      var totalS = totalSeconds % 60;

      var html = '<div class="logbook-summary"><strong>Total logged:</strong> ' + totalH.toString().padStart(2, '0') + ':' + totalM.toString().padStart(2, '0') + ':' + totalS.toString().padStart(2, '0') + ' (' + totalHours.toFixed(2) + 'h)</div>';
      html += '<div class="logbook-documentary"><h6>Daily Logged Time</h6>';

      dayData.entries.forEach(function (entry, idx) {
        var rawSeconds = entry.raw_seconds || Math.round(entry.hours * 3600);
        var entryH = Math.floor(rawSeconds / 3600);
        var entryM = Math.floor((rawSeconds % 3600) / 60);
        var entryS = rawSeconds % 60;
        var inTime = new Date(entry.in * 1000).toLocaleString();
        var outTime = new Date(entry.out * 1000).toLocaleString();
        var durationText = entryH.toString().padStart(2, '0') + ':' + entryM.toString().padStart(2, '0') + ':' + entryS.toString().padStart(2, '0');

        html += '<div class="entry-item">';
        html += '<div><strong>Log ' + (idx + 1) + '</strong> <small>(' + entry.hours.toFixed(2) + 'h / ' + Math.floor(rawSeconds / 60) + 'm ' + entryS + 's)</small></div>';
        html += '<div><small>In: </small><strong>' + inTime + '</strong></div>';
        html += '<div><small>Out: </small><strong>' + outTime + '</strong></div>';
        html += '<div><small>Duration: </small>' + durationText + '</div>';
        html += '</div>';
      });

      html += '</div>';
      if (modalBodyEl) {
        modalBodyEl.innerHTML = html;
      }
    } else if (dayData && dayData.hours > 0) {
      var totalHours = dayData.hours;
      var totalSeconds = dayData.seconds || Math.round(totalHours * 3600);
      var totalH = Math.floor(totalSeconds / 3600);
      var totalM = Math.floor((totalSeconds % 3600) / 60);
      var totalS = totalSeconds % 60;

      var totalTimeText = totalH.toString().padStart(2, '0') + ':' + totalM.toString().padStart(2, '0') + ':' + totalS.toString().padStart(2, '0');
      var html = '<div class="logbook-summary"><strong>Total logged:</strong> ' + totalTimeText + ' (' + totalHours.toFixed(2) + 'h)</div>';
      html += '<div class="logbook-documentary"><h6>Daily Logged Time</h6>';

      if (dayData.entries && dayData.entries.length > 0) {
        dayData.entries.forEach(function (entry, idx) {
          var rawSeconds = entry.raw_seconds || Math.round(entry.hours * 3600);
          var eH = Math.floor(rawSeconds / 3600);
          var eM = Math.floor((rawSeconds % 3600) / 60);
          var eS = rawSeconds % 60;
          var inTime = entry.in ? new Date(entry.in * 1000).toLocaleString() : 'n/a';
          var outTime = entry.out ? new Date(entry.out * 1000).toLocaleString() : 'n/a';
          html += '<div class="entry-item">';
          html += '<strong>Log ' + (idx + 1) + ':</strong> ' + eH.toString().padStart(2, '0') + ':' + eM.toString().padStart(2, '0') + ':' + eS.toString().padStart(2, '0') + ' (' + (entry.hours ? entry.hours.toFixed(2) : '0.00') + 'h)<br>';
          html += '<small>In: ' + inTime + '</small><br>';
          html += '<small>Out: ' + outTime + '</small>';
          html += '</div>';
        });
      } else {
        html += '<div class="entry-item"><strong>In:</strong> n/a<br><strong>Out:</strong> n/a<br><small>Recorded: ' + totalTimeText + ' (' + totalHours.toFixed(2) + 'h)</small></div>';
      }

      html += '</div>';
      if (modalBodyEl) {
        modalBodyEl.innerHTML = html;
      }
    } else if (modalBodyEl) {
      modalBodyEl.innerHTML = '<div class="alert alert-warning">No entries for ' + date + ' yet.</div>';
    }

    if (typeof $ !== 'undefined') {
      $('#logbookModal').modal('show');
    }

    var theme = getDepartmentTheme(studentDepartment);
    var headerEl = document.querySelector('#logbookModal .modal-header');
    if (headerEl) {
      headerEl.style.background = 'linear-gradient(120deg, ' + theme.bg + ', ' + theme.bg + ')';
      headerEl.style.color = theme.text;
    }
  }

  function loadEntries() {
    var url = '?api=logbook_calendar&month=' + calendarMonth + '&year=' + calendarYear;
    fetch(url, { cache: 'no-cache' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.entriesByDate) {
          entriesByDate = data.entriesByDate;
          selectedDate = data.selectedDate || selectedDate;
          renderCalendar();
        }
      });
  }

  function navigateTo(month, year, day) {
    var url = window.location.pathname + '?month=' + month + '&year=' + year + '&day=' + encodeURIComponent(day);
    window.location.href = url;
  }

  var today = new Date();
  function setSelectedDayToFirstOfMonth(month, year) {
    var d = new Date(year, month - 1, 1);
    selectedDate = formatDateYMD(d);
  }

  var prevYearBtn = document.getElementById('lb-prev-year');
  if (prevYearBtn) {
    prevYearBtn.addEventListener('click', function () {
      calendarYear--;
      setSelectedDayToFirstOfMonth(calendarMonth, calendarYear);
      navigateTo(calendarMonth, calendarYear, selectedDate);
    });
  }

  var prevMonthBtn = document.getElementById('lb-prev-month');
  if (prevMonthBtn) {
    prevMonthBtn.addEventListener('click', function () {
      calendarMonth--;
      if (calendarMonth < 1) {
        calendarMonth = 12;
        calendarYear--;
      }
      setSelectedDayToFirstOfMonth(calendarMonth, calendarYear);
      navigateTo(calendarMonth, calendarYear, selectedDate);
    });
  }

  var nextMonthBtn = document.getElementById('lb-next-month');
  if (nextMonthBtn) {
    nextMonthBtn.addEventListener('click', function () {
      calendarMonth++;
      if (calendarMonth > 12) {
        calendarMonth = 1;
        calendarYear++;
      }
      setSelectedDayToFirstOfMonth(calendarMonth, calendarYear);
      navigateTo(calendarMonth, calendarYear, selectedDate);
    });
  }

  var nextYearBtn = document.getElementById('lb-next-year');
  if (nextYearBtn) {
    nextYearBtn.addEventListener('click', function () {
      calendarYear++;
      setSelectedDayToFirstOfMonth(calendarMonth, calendarYear);
      navigateTo(calendarMonth, calendarYear, selectedDate);
    });
  }

  var todayBtn = document.getElementById('lb-today');
  if (todayBtn) {
    todayBtn.addEventListener('click', function () {
      var todayDate = today.toISOString().slice(0, 10);
      calendarMonth = today.getMonth() + 1;
      calendarYear = today.getFullYear();
      selectedDate = todayDate;
      navigateTo(calendarMonth, calendarYear, selectedDate);
    });
  }

  var refreshBtn = document.getElementById('lb-refresh');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', function () {
      loadEntries();
    });
  }

  renderNow();
  renderCalendar();
  setInterval(function () { renderNow(); }, 1000);
})();
