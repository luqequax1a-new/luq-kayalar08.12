@php($enabled = setting('geliver_enabled'))
@if($enabled)
    @if(isset($order) && empty($order->geliver_shipment_id))
        <form action="{{ route('admin.geliver.orders.send', $order->id) }}" method="POST" style="display:inline-block; margin-bottom:10px;">
            @csrf
            <button type="submit" class="btn btn-primary">
                Geliver’e Gönder
            </button>
        </form>
    @endif
@endif
