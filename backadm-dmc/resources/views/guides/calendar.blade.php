@extends('layouts.layout')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs5/2.1.8/dataTables.bootstrap5.min.css" integrity="sha512-9d9bjYZUo25k3MPAMpx+OUyvGQcbJe8qGOUJilgowXEPc0lNCVoe+zHZX8HszzkDJEUynZeF648jP9JLX1Pi7A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* Custom styles for disabled and blackout dates */
    .fc-day.closed-date {
        background-color: rgba(255, 0, 0, 0.2) !important; /* Light red background */
        pointer-events: none;
    }
    .fc-day.disabled-date {
        background-color: rgba(255, 0, 0, 0.2) !important; /* Light red */
        pointer-events: none;
    }
</style>

@section('content')

<div class="page-content mt-1">
    <div class="page-container">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-10 col-sm-12">
                <div class="card">
                    <div class="card-body p-1">
                        <h2 class="text-center my-4">Availability Calendar</h2>

                        <!-- Color Legend -->
                        <div class="color-legend">
                            <div>
                                <div class="color-box" style="background-color: rgba(255, 0, 0, 0.2);"></div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#approveModal" class="btn btn-primary btn-sm">
                                Add Holidays or Close Dates
                            </a>
                        </div>

                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Dates Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Holidays or Close Dates</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="GuideCloseDate" method="POST" action="{{ route('guide_close_dates') }}">
                        @csrf
                        <input type="hidden" name="guide_id" value="{{$guide_id}}">
                        
                        <!-- Closed Days -->
                        <div class="mb-3">
                            <label for="guide_closed_days"><strong>Closed Days</strong></label>
                            <select id="guide_closed_days" class="form-control select2" name="guide_closed_days[]" multiple required>
                                @php
                                    $closedDays = is_string($guide->close_days) ? json_decode($guide->close_days, true) : ($guide->close_days ?? []);
                                @endphp
                                <option value="Sunday" {{ in_array('Sunday', $closedDays) ? 'selected' : '' }}>Sunday</option>
                                <option value="Monday" {{ in_array('Monday', $closedDays) ? 'selected' : '' }}>Monday</option>
                                <option value="Tuesday" {{ in_array('Tuesday', $closedDays) ? 'selected' : '' }}>Tuesday</option>
                                <option value="Wednesday" {{ in_array('Wednesday', $closedDays) ? 'selected' : '' }}>Wednesday</option>
                                <option value="Thursday" {{ in_array('Thursday', $closedDays) ? 'selected' : '' }}>Thursday</option>
                                <option value="Friday" {{ in_array('Friday', $closedDays) ? 'selected' : '' }}>Friday</option>
                                <option value="Saturday" {{ in_array('Saturday', $closedDays) ? 'selected' : '' }}>Saturday</option>
                            </select>
                        </div>

                        <!-- Closed Dates -->
                        <div class="mb-3">
                            <label for="guide_holiday_dates"><strong>Holiday Dates</strong></label>
                            <input type="text" class="form-control flatpickr-input" id="guide_holiday_dates"
                                   name="guide_holiday_dates" placeholder="Select holiday dates" required>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const closedDays = @json(is_string($guide->close_days) ? json_decode($guide->close_days, true) : ($guide->close_days ?? []));
        const closedDates = @json(explode(',', $guide->close_dates ?? ''));

        // Initialize Flatpickr for selecting close dates
        flatpickr("#guide_holiday_dates", {
            mode: "multiple",
            dateFormat: "Y-m-d",
            defaultDate: closedDates,
            showMonths: 4,
        });

        // Initialize Select2 for closed days
        $('#guide_closed_days').select2({
            placeholder: "Select Days",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#approveModal')
        });

        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'en',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth',
            },
            validRange: {
                start: '{{ date("Y") }}-01-01',
                end: '{{ date("Y", strtotime("+1 year")) }}-12-31',
            },
            dayCellDidMount: function (info) {
                let cell = info.el;
                let dateStr = info.date.toISOString().split('T')[0];

                // Check if the day is in closed days
                let dayName = info.date.toLocaleString('en-us', { weekday: 'long' });
                if (closedDays.includes(dayName)) {
                    cell.classList.add("closed-date");
                    cell.setAttribute('title', 'Closed on ' + dayName);
                }

                // Check if the date is in closed dates
                if (closedDates.includes(dateStr)) {
                    cell.classList.add("disabled-date");
                    cell.setAttribute('title', 'Closed on this date');
                }
            },
            height: 'auto'
        });

        calendar.render();
    });
</script>

@endsection
