@extends('storefront::public.account.layout')

@section('title', trans('ticket::ticket.new_ticket'))

@section('account_breadcrumb')
    <li><a href="{{ route('account.tickets.index') }}">{{ trans('ticket::ticket.tickets') }}</a></li>
    <li class="active">{{ trans('ticket::ticket.new_ticket') }}</li>
@endsection

@section('panel')
    <div class="panel">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h4>{{ trans('ticket::ticket.new_ticket') }}</h4>
        </div>
        <div class="panel-body" style="padding:0">
            <style>
                .ticket-create-wrapper{width:100%;margin:0;padding:12px}
                .ticket-card{background:#fff;border-radius:0;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden}
                .ticket-create-container{display:flex;flex-direction:column;min-height:60vh;background:#fff}
                .ticket-create-header{padding:12px 16px;border-bottom:1px solid #e5e7eb;background:#fff}
                .ticket-create-content{padding:20px;display:flex;flex-direction:column;gap:26px}
                .tc-field{display:flex;flex-direction:column;gap:12px}
                .tc-subject{margin-bottom:16px}
                .tc-input{font-size:16px;padding:16px 18px;border:1px solid #e5e7eb;border-radius:12px;outline:none;width:100%;min-height:56px}
                .tc-textarea{min-height:180px;max-height:420px;resize:none;border:1px solid #e5e7eb;border-radius:12px;outline:none;padding:16px 18px;overflow-y:auto;width:100%;font-size:16px;line-height:1.7}
                .composer-top{display:flex;gap:10px;align-items:center;margin-bottom:16px}
                .file-input{display:none}
                .upload-btn{display:inline-flex;gap:6px;align-items:center;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;cursor:pointer}
                .file-count{font-size:12px;color:#666}
                .tc-actions{display:flex;gap:10px;align-items:center;justify-content:flex-end;margin-top:6px}
                .send-btn{height:44px;padding:0 18px;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;box-sizing:border-box;background:#6f63ff;color:#fff;border:none}
                @media (max-width: 768px){
                    .ticket-create-wrapper{width:100%;padding:8px}
                    .ticket-create-content{padding:12px}
                    .tc-input{font-size:15px;padding:14px 16px;border-radius:10px;min-height:52px}
                    .tc-textarea{min-height:150px;border-radius:10px}
                    .send-btn{height:40px;padding:0 16px}
                }
            </style>
            <div class="ticket-create-wrapper"><div class="ticket-card"><div class="ticket-create-container">
                <div class="ticket-create-header d-flex justify-content-end align-items-center"></div>
                <div class="ticket-create-content">
                    <form method="POST" action="{{ route('account.tickets.store') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="tc-field tc-subject">
                            <input type="text" name="subject" class="tc-input" placeholder="{{ trans('ticket::ticket.subject') }}" required>
                        </div>
                        <div class="composer-top">
                            <input id="new-ticket-upload" type="file" name="images[]" multiple accept="image/*" class="file-input">
                            <label for="new-ticket-upload" class="upload-btn"><i class="las la-image"></i> {{ __('Görsel ekle') }}</label>
                            <span class="file-count"></span>
                        </div>
                        <div class="tc-field">
                            <textarea id="new-ticket-textarea" name="body" class="tc-textarea" rows="6" placeholder="{{ trans('ticket::ticket.write_message') }}" required></textarea>
                        </div>
                        <div class="tc-actions">
                            <button class="send-btn">{{ trans('ticket::ticket.send') }}</button>
                            <a href="{{ route('account.tickets.index') }}" class="btn btn-default">{{ __('Vazgeç') }}</a>
                        </div>
                    </form>
                </div>
            </div></div></div>
            <script>
                document.addEventListener('DOMContentLoaded',function(){
                    var fi = document.getElementById('new-ticket-upload');
                    var fc = document.querySelector('.file-count');
                    if (fi && fc) {
                        fi.addEventListener('change', function(){
                            fc.textContent = fi.files && fi.files.length ? (fi.files.length + ' dosya') : '';
                        });
                    }
                    var ta = document.getElementById('new-ticket-textarea');
                    if (ta) {
                        var auto = function(){
                            ta.style.height = 'auto';
                            var h = Math.min(ta.scrollHeight, 420);
                            ta.style.height = h + 'px';
                            ta.style.overflowY = ta.scrollHeight > 420 ? 'auto' : 'hidden';
                        };
                        ta.addEventListener('input', auto);
                        auto();
                    }
                });
            </script>
        </div>
    </div>
@endsection
