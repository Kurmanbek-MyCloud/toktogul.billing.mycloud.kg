<template>
  <Layout>
    <div class="py-2" >
      <div class="px-2">
        <div class="flex justify-between mb-1">
          <div class="w-1/2">
            <button
              v-if="selectedView != 'timeGridDay'"
              class="text-xs text-white bg-indigo-700 p-1 mr-1 rounded calendar-button"
              :class="{ selected: calendarOptions.weekends }"
              @click="calendarOptions.weekends = !calendarOptions.weekends"
            >
              Выходные
            </button>
          </div>
          <div class="flex items-center justify-end w-1/2">
            <button
              class="text-xs text-white bg-indigo-700 p-1 ml-1 rounded calendar-button"
              v-for="view in calendarViewOptions"
              :class="{ selected: selectedView === view.name }"
              @click="selectView(view.name)"
            >
              {{ view.label }}
            </button>
          </div>
        </div>
        <full-calendar :options="calendarOptions" ref="fullCalendar" />
      </div>
      <router-link
        :to="`view/MARKETING/Events/create`"
        class="fixed bottom-0 right-0 bg-indigo-400 z-10 rounded-full m-6 flex p-3 shadow-lg"
      >
        <i class="dripicons-plus text-2xl inline-flex text-white"></i>
      </router-link>
    </div>
  </Layout>
</template>
<script>
import VCalendar from 'v-calendar/lib/components/calendar.umd';
import VDatePicker from 'v-calendar/lib/components/date-picker.umd';
import FullCalendar from '@fullcalendar/vue';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridWeekPlugin from '@fullcalendar/timegrid';
import ruLocale from '@fullcalendar/core/locales/ru';
import { DateTime } from 'luxon';

export default {
  components: {
    VCalendar,
    VDatePicker,
    FullCalendar,
  },
  data() {
    return {
      screen: '',
      selectedView: '',
      calendarViewOptions: [
        {
          label: 'Месяц',
          name: 'dayGridMonth',
        },
        {
          label: 'Неделя',
          name: 'timeGridWeek',
        },
        {
          label: 'День',
          name: 'timeGridDay',
        },
      ],
      calendarOptions: {
        plugins: [dayGridPlugin, interactionPlugin, timeGridWeekPlugin],
        initialView: 'timeGridWeek',
        locale: ruLocale,
        weekends: false,
        slotDuration: '00:15:00',
        slotLabelInterval: '00:15:00',
        slotMinTime: '08:00:00',
        height: 'auto',
        allDaySlot: false,
        eventClick: this.handleEventClick,
        selectable: true,
        select: this.handleTimeSelect,
        editable: true,
        eventDrop: this.handleEventTimeChange,
        eventResize: this.handleEventTimeChange,
        headerToolbar: {
          left: 'title',
          right: 'today prev,next',
        },
        slotLabelFormat: {
          hour: 'numeric',
          minute: '2-digit',
        },
        events: [
          {
            title: 'BCH237',
            start: '2020-11-25T10:30:00',
            end: '2020-11-25T11:30:00',
            extendedProps: {
              department: 'BioChemistry',
            },
          },
        ],
      },
    };
  },
  created() {
    axios
      .post('api.php', {
        _operation: 'query',
        query: 'select * from Events;',
      })
      .then(({ data }) => {
        if (!data.success) return;

        data.result.records.forEach(item => {
          let color = '';
          let textColor = '';
          let borderColor = '';
          let status = '';
          if (
            item.eventstatus === 'Выполнено' ||
            item.eventstatus === 'Not Held'
          ) {
            color = '#c2c2c2';
            borderColor = '#878787';
            textColor = '#000000';
          } else {
            switch (item.activitytype) {
              case 'Meeting':
                color = '#f5f511';
                borderColor = '#bebf03';
                textColor = '#000000';
                break;
              case 'Выполнить':
                color = '#3aad00';
                borderColor = '#368b00';
                break;
              case 'Call':
                color = '#375bed';
                borderColor = '#1f44cb';
                break;
              case 'Mobile Call':
                color = '#89bcfa';
                borderColor = '#5984b2';
                break;
              default:
                return '#c2c2c2';
            }
          }
          switch (item.eventstatus) {
            case 'Not Held':
              status = 'Не проведено';
              break;
            case 'Held':
              status = 'В работе';
              break;
            case 'Planned':
              status = 'Запланировано';
              break;
            case 'Выполнено':
              status = 'Выполнено';
              break;
            default:
              return '';
          }
          this.calendarOptions.events.push({
            title: `${item.subject} (${status})`,
            start: `${item.date_start}T${item.time_start}`,
            end: `${item.due_date}T${item.time_end}`,
            textColor: textColor,
            backgroundColor: color,
            borderColor: borderColor,
            id: item.id,
            status: item.eventstatus,
            activityType: item.activitytype,
          });
        });
      });
  },
  mounted() {
    window.addEventListener('orientationchange', this.handleOrientationChange);
    this.screen = screen.orientation.type;
  },
  methods: {
    handleEventClick(arg) {
      let id = arg.event.id.split(['x']);
      this.$router.push(`/view/MARKETING/Events/detail/${id[1]}`);
    },
    handleOrientationChange() {
      const orientation = window.screen.orientation.type;
      if (orientation === 'portrait-primary') {
        this.screen = 'portrait-primary';
      } else {
        this.screen = 'landscape-primary';
      }
    },
    handleTimeSelect(selectedTimeData) {
      let start = DateTime.fromJSDate(selectedTimeData.start);
      let end = DateTime.fromJSDate(selectedTimeData.end);
      
      let startDate = start.toISODate();
      let startTime = start.toFormat('HH:mm:ss');
      let endDate = end.toISODate();
      let endTime = end.toFormat('HH:mm:ss');

      let presetFields = {
        date_start: startDate,
        time_start: startTime,
        due_date: endDate,
        time_end: endTime,
      };

      let presetFieldsJson = JSON.stringify(presetFields);

      this.$router.push(`/view/MARKETING/Events/create/${presetFieldsJson}`);
    },
    handleEventTimeChange(eventChangeInfo) {
      let start = DateTime.fromJSDate(eventChangeInfo.event.start);
      let end = DateTime.fromJSDate(eventChangeInfo.event.end);
      
      let startDate = start.toISODate();
      let startTime = start.toFormat('HH:mm:ss');
      let endDate = end.toISODate();
      let endTime = end.toFormat('HH:mm:ss');

      let id = eventChangeInfo.event.id;

      let values = {
        date_start: startDate,
        time_start: startTime,
        due_date: endDate,
        time_end: endTime,
      }

      axios.post('api.php', {
        'module': 'Calendar',
        '_operation': 'saveRecord',
        'values': JSON.stringify(values),
        'record': id,
      }).then(({ data }) => {
        if (!data.success) eventChangeInfo.revert();
      });
    },
    selectView(view) {
      this.$refs.fullCalendar.getApi().changeView(view);
      this.selectedView = view;
    },
  },
  watch: {
    screen() {
      if (this.screen === 'portrait-primary') {
        this.selectedView = 'timeGridDay';
        this.$refs.fullCalendar.getApi().changeView('timeGridDay');
      }
      if (this.screen === 'landscape-primary') {
        this.selectedView = 'timeGridWeek';
        this.$refs.fullCalendar.getApi().changeView('timeGridWeek');
      }
    },
  },
};
</script>

<style lang="css">
.calendar-button.selected {
  background: #76818d;
}
.fc .fc-header-toolbar.fc-header-toolbar {
  margin-bottom: 0.75em;
}
.fc .fc-header-toolbar .fc-toolbar-chunk .fc-toolbar-title {
  font-size: 0.75rem;
}
.fc
  .fc-header-toolbar
  .fc-toolbar-chunk
  .fc-button-group
  .fc-button-primary
  .fc-icon {
  font-size: 1rem;
}
.fc .fc-header-toolbar .fc-toolbar-chunk .fc-button-group .fc-button-primary {
  padding: 0.175rem;
}
.fc .fc-header-toolbar .fc-toolbar-chunk .fc-button-primary {
  font-size: 0.7rem;
  padding: 0.175rem;
}
.fc table {
  font-size: 0.7rem;
}
.fc-highlight {
  background: rgba(92, 111, 233, 0.5) !important;
}
</style>
