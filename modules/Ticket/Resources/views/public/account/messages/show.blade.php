@extends('storefront::public.account.layout')

@section('title', trans('ticket::ticket.ticket'))

@section('account_breadcrumb')
    <li><a href="{{ route('account.tickets.index') }}">{{ trans('ticket::ticket.tickets') }}</a></li>
    <li class="active">#{{ $ticket->id }}</li>
@endsection

@section('panel')
    <div class="panel">
        <div class="panel-header">
            <h4>#{{ $ticket->id }} — {{ $ticket->subject }}</h4>
        </div>
        <div class="panel-body" style="padding:0">
            <style>
                .chat-wrapper{width:100%;margin:0;padding:12px}
                .chat-card{background:#fff;border-radius:0;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden}
                .chat-container{display:flex;flex-direction:column;min-height:60vh;background:#fff}
                .chat-header{padding:20px 20px;border-bottom:1px solid #e5e7eb;background:#fff;min-height:64px}
                .chat-wrap{overflow:visible;padding:16px;display:flex;flex-direction:column;gap:12px}
                .msg{max-width:92%;padding:12px 14px;border-radius:16px;position:relative;line-height:1.7;font-size:15px;background:#fff;border:1px solid #e5e7eb}
                .msg-user{margin-left:auto;background:#fff;color:#222;border:1px solid #e5e7eb}
                .msg-admin{margin-right:auto;background:#fff;color:#222;border:1px solid #e5e7eb}
                .msg-author{font-size:12px;color:#666;margin-bottom:6px}
                .msg-attach{background:transparent;border:none;box-shadow:none;padding:0;margin:0}
                .attachments{display:flex;gap:10px;flex-wrap:wrap;margin:8px 0 0}
                .attachments .item{border-radius:10px;overflow:hidden;max-width:280px}
                .attachments .item img{display:block;width:100%;height:auto}
                .attachments-user{justify-content:flex-end}
                .attachments-admin{justify-content:flex-start}
                .msg-time{font-size:11px;color:#888;margin-top:6px;text-align:right}
                .chat-composer{padding:12px;border-top:1px solid #e5e7eb;background:#fff}
                .chat-composer-inner{display:grid;grid-template-columns:1fr 44px;gap:12px;border:1px solid #e5e7eb;border-radius:9999px;background:#fff;padding:8px 10px;width:100%;align-items:center}
                .chat-composer .field{flex:1;position:relative}
                .chat-composer .actions{display:flex;gap:8px;align-items:center}
                .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border:1px solid #e5e7eb;border-radius:8px;background:#fff}
                .send-btn{height:44px;width:44px;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;box-sizing:border-box;background:#6f63ff;color:#fff}
                .file-count{font-size:12px;color:#666}
                .file-input{display:none}
                .chat-composer textarea{min-height:56px;max-height:220px;resize:none;border:none;border-radius:9999px;outline:none;padding:12px 14px;overflow-y:auto;width:100%;word-break:break-word}
                .composer-top{display:flex;gap:10px;align-items:center;margin-bottom:12px}
                .upload-btn{display:inline-flex;gap:6px;align-items:center;padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;cursor:pointer}
                .status-chip{display:inline-block;font-size:14px}
                .status-open{color:#0f5132}
                .status-closed{color:#842029}
                @media (max-width: 768px){
                    .chat-wrapper{width:100%;padding:8px}
                    .chat-container{min-height:calc(100vh - 160px)}
                    .chat-wrap{padding:12px}
                    .msg{max-width:96%;font-size:15px;border-radius:12px}
                    .attachments .item{max-width:100%}
                    .attachments .item img{width:100%;height:auto}
                    .chat-composer{padding:8px}
                    .chat-composer-inner{grid-template-columns:1fr 44px}
                    .chat-composer-inner{border-radius:0}
                    .chat-composer textarea{border-radius:0}
                    .chat-composer textarea{min-height:40px;max-height:160px}
                    .send-btn{height:40px;width:40px}
                }
            </style>
            <div class="chat-wrapper"><div class="chat-card"><div class="chat-container">
                <div class="chat-header d-flex justify-content-between align-items-center">
                    <div></div>
                    <div class="status-chip">Ticket Durumu: <span class="{{ $ticket->status === 'closed' ? 'status-closed' : 'status-open' }}">{{ $ticket->status === 'closed' ? 'Kapalı' : 'Açık' }}</span></div>
                </div>
                <div class="chat-wrap">
                    @foreach($ticket->messages as $m)
                        @if($m->is_internal)
                            @continue
                        @endif
                        <div class="msg {{ $m->sender_type === 'user' ? 'msg-user' : 'msg-admin' }}">
                            <div class="msg-author">{{ $m->sender_type === 'admin' ? 'Admin' : trans('storefront::account.pages.my_profile') }}</div>
                            @if($m->attachments->isNotEmpty())
                                <div class="attachments">
                                    @foreach($m->attachments as $a)
                                        <div class="item">
                                            <a href="{{ $a->url }}" class="ticket-lightbox" data-gallery="ticket-{{ $ticket->id }}">
                                                <img src="{{ $a->url }}" alt="attachment">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div>{{ $m->body }}</div>
                            <div class="msg-time">{{ optional($m->created_at)->format('Y-m-d H:i') }}</div>
                        </div>
                    @endforeach
                </div>
                @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('account.tickets.messages.store', $ticket->id) }}" enctype="multipart/form-data" class="chat-composer">
                        {{ csrf_field() }}
                        <div class="composer-top">
                            <input id="ticket-upload" type="file" name="images[]" multiple accept="image/*" class="file-input">
                            <label for="ticket-upload" class="upload-btn"><i class="las la-image"></i> {{ __('Görsel ekle') }}</label>
                            <span class="file-count"></span>
                        </div>
                        <div class="chat-composer-inner">
                            <div class="field">
                                <textarea id="ticket-textarea" name="body" rows="1" placeholder="{{ trans('ticket::ticket.write_message') }}" required></textarea>
                            </div>
                            <button class="btn btn-primary send-btn"><i class="las la-paper-plane"></i></button>
                        </div>
                    </form>
                @else
                    <div class="chat-composer" style="opacity:.7">
                        <div class="chat-composer-inner">
                            <div class="field">
                                <textarea rows="1" placeholder="{{ __('Ticket kapalı, mesaj gönderemezsiniz.') }}" disabled></textarea>
                            </div>
                            <button class="btn btn-default send-btn" disabled><i class="las la-paper-plane"></i></button>
                        </div>
                    </div>
                @endif
            </div></div></div>
        </div>
    </div>
@endsection
@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded',function(){
            if (window.GLightbox) {
                GLightbox({ selector: '.ticket-lightbox' });
            }
            var wrap = document.querySelector('.chat-wrap');
            if (wrap) { wrap.scrollTop = wrap.scrollHeight; }
            var fi = document.getElementById('ticket-upload');
            var fc = document.querySelector('.file-count');
            if (fi && fc) {
                fi.addEventListener('change', function(){
                    fc.textContent = fi.files && fi.files.length ? (fi.files.length + ' dosya') : '';
                });
            }
            var ta = document.getElementById('ticket-textarea');
            if (ta) {
                var auto = function(){
                    ta.style.height = 'auto';
                    var h = Math.min(ta.scrollHeight, 220);
                    ta.style.height = h + 'px';
                    ta.style.overflowY = ta.scrollHeight > 220 ? 'auto' : 'hidden';
                };
                ta.addEventListener('input', auto);
                auto();

                var form = document.querySelector('.chat-composer');
                ta.addEventListener('keydown', function(e){
                    if (e.key === 'Enter' && !e.shiftKey && form) {
                        e.preventDefault();
                        form.submit();
                    }
                });
            }
        });
    </script>
@endpush
