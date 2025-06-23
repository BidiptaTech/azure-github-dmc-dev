@extends('layouts.layout')

<!-- FullCalendar Styles -->
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/main.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/multimonth@6.1.8/main.min.css" rel="stylesheet" />

<body>
    <!-- Calendar Container -->
    <div id="calendar"></div>

    @section('scripts')
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/multimonth@6.1.8/index.global.min.js"></script>

    <!-- Calendar Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    plugins: ['multiMonth'],
                    initialView: 'multiMonthYear',  // Use multi-month view to show a full year
                    views: {
                        multiMonthYear: {
                            type: 'multiMonth',
                            duration: { years: 1 },   // Show full year
                            months: 12,               // Show 12 months
                            aspectRatio: 1.9          // Adjust aspect ratio for yearly view
                        }
                    }
                });

                calendar.render();
            }
        });
    </script>
    @endsection
</body>
