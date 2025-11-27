@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Yönlendirmeler')
    <li class="active">Yönlendirmeler</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'redirects')
    @slot('name', 'Yönlendirme')

    @slot('thead')
        <tr>
            <th>Seç</th>
            <th>ID</th>
            <th>Kaynak</th>
            <th>Hedef</th>
            <th>Status</th>
            <th>Aktif</th>
            <th>Oluşturulma</th>
            <th>Aksiyonlar</th>
        </tr>
    @endslot
@endcomponent

@push('scripts')
<script type="module">
    const dt = new DataTable('#redirects-table .table', {
        columns: [
            { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
            { data: 'id', width: '5%' },
            { data: 'source_path', name: 'source_path' },
            { data: 'target', name: 'target_url' },
            { data: 'status', name: 'status_code', orderable: false },
            { data: 'active', name: 'is_active', orderable: false },
            { data: 'created', name: 'created_at', searchable: false },
            { data: 'actions', orderable: false, searchable: false },
        ]
    });

    const filtersHtml = `
        <div id="redirects-filters" class="box box-default" style="margin-bottom:10px;">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <input id="f-source" class="form-control" placeholder="Kaynak ara" />
                    </div>
                    <div class="col-md-3">
                        <input id="f-target" class="form-control" placeholder="Hedef ara" />
                    </div>
                    <div class="col-md-3">
                        <select id="f-code" class="form-control">
                            <option value="">HTTP Kod</option>
                            <option value="301">301</option>
                            <option value="302">302</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="f-active" class="form-control">
                            <option value="">Aktif/Pasif</option>
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>`;

    const tableWrapper = document.querySelector('#redirects-table');
    if (tableWrapper) {
        tableWrapper.insertAdjacentHTML('afterbegin', `
            <div class="m-b-10">
                <button id="toggle-redirects-filters" class="btn btn-default">Filtreler</button>
            </div>
        `);
        tableWrapper.insertAdjacentHTML('afterbegin', filtersHtml);
    }

    const apply = () => {
        dt.column(2).search(document.getElementById('f-source').value || '');
        dt.column(3).search(document.getElementById('f-target').value || '');
        dt.column(4).search(document.getElementById('f-code').value || '');
        dt.column(5).search(document.getElementById('f-active').value || '');
        dt.draw();
    };

    ['f-source','f-target','f-code','f-active'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', apply);
        el.addEventListener('change', apply);
    });

    const toggleBtn = document.getElementById('toggle-redirects-filters');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const el = document.getElementById('redirects-filters');
            if (el) el.classList.toggle('hidden');
        });
    }

    $(document).on('change', '.js-toggle-active', function () {
        $(this).closest('.js-toggle-form').submit();
    });

    $('#redirects-table .table').on('click', '.action-delete', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const id = $(this).data('id');
        if (!id) return;

        const confirmationModal = $('#confirmation-modal');
        confirmationModal
            .modal('show')
            .find('form')
            .off('submit')
            .on('submit', (ev) => {
                ev.preventDefault();
                confirmationModal.modal('hide');

                axios
                    .delete(`${FleetCart.baseUrl}/admin/redirects/${id}`)
                    .then(() => {
                        DataTable.reload('#redirects-table .table');
                    })
                    .catch(() => {
                        DataTable.reload('#redirects-table .table');
                    });
            });
    });

    $('#redirects-table .table').on('click', '.action-edit', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const href = $(this).attr('href');
        if (href) window.location.href = href;
    });

    $('#redirects-table .table').on('click', '[data-confirm]', function (e) {
        e.stopImmediatePropagation();
    });

    $('#redirects-table .table').on('click', 'td', function (e) {
        if ($(e.target).closest('.action-delete, .action-edit, .switch, .switch label').length) {
            e.stopImmediatePropagation();
        }
    });
</script>
@endpush
