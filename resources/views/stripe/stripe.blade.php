<form action="{{ route('stripe.checkout') }}" method="POST">
    @csrf

    <button type="submit">
        Pay Now
    </button>
</form>