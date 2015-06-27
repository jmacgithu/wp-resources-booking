// Calendar configuration
var _calendarOpts = {
    header: {
        left: 'title',
        center: 'agendaDay,agendaWeek,month',
        right: 'today prev,next'
    },
    axisFormat: 'HH:mm',
    timeFormat: {
        agenda: 'HH:mm' //h:mm{ - h:mm}'
    },
    timezone: 'local', //'Europe,Rome',
    firstDay: 1,
//    weekends: false,
    defaultView: 'agendaWeek',
    allDaySlot: false,
    eventColor: "#123456",
    selectable: true,
    selectOverlap: false,
    eventOverlap: false,
    editable: true,
    lazyFetching: false,
    height: 'auto',
    slotDuration: '00:30:00',
    businessHours: {
        start: '08:00',
        end: '18:30',
    },
    minTime: '07:30:00',
    maxTime: '19:30:00',
    views: {
        agenda: {//For week and day
            titleFormat: 'D MMMM YYYY',
            columnFormat: 'ddd D/M',
        },
        month: {
            titleFormat: 'MMMM YYYY',
            timeFormat: 'HH:mm',
        }
    },
};
