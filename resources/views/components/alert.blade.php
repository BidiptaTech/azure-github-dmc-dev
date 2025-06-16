
{{-- Notification for success message --}}
@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <p>{{ $message }}</p>
    </div>
@endif


{{-- Notification for error message --}}
@if ($message = Session::get('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <p>{{ $message }}</p>
    </div>
@endif

@if ($message = Session::get('denied'))
    <div class="alert alert-danger alert-dismissible fade show">
        <p>{{ $message }}</p>
    </div>
@endif


<script>
    // Set a timeout to automatically hide the alert after 5 seconds
    setTimeout(function() {
        let alert = document.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show'); // Fades out the alert
            setTimeout(() => alert.remove(), 300); // Removes the element after fading
        }
    }, 5000);
</script>