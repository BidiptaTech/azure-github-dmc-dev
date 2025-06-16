
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTable JS -->
<!-- <script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js' }}"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.js' }}"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.js' }}"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.js' }}"></script> -->

<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        $('.datatables-basic').DataTable({
            responsive: true,
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print' // Enable copy, CSV, Excel, PDF, and Print buttons
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
        });

        // Custom export button functionality (for the dropdown)
        $('#exportCopy').on('click', function() {
            $('.datatables-basic').DataTable().button('.buttons-copy').trigger();
        });

        $('#exportCSV').on('click', function() {
            $('.datatables-basic').DataTable().button('.buttons-csv').trigger();
        });

        $('#exportExcel').on('click', function() {
            $('.datatables-basic').DataTable().button('.buttons-excel').trigger();
        });

        $('#exportPDF').on('click', function() {
            $('.datatables-basic').DataTable().button('.buttons-pdf').trigger();
        });

        $('#exportPrint').on('click', function() {
            $('.datatables-basic').DataTable().button('.buttons-print').trigger();
        });
    });
</script>