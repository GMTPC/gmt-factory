document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl || typeof FullCalendar === 'undefined') return;

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth', // default view

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay' 
    },

    selectable: true,
    editable: false,
    height: 'auto',

    events: []
  });

  calendar.render();
});