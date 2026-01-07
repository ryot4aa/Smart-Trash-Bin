@foreach($notifikasis as $notif)
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2">
        <p><strong>Notifikasi:</strong> {{ $notif->keterangan }}</p>
        <p>User: {{ $notif->user ? $notif->user->name : '-' }}</p>
        <p>Tong: {{ $notif->device ? $notif->device->nama_device : '-' }}</p>
        <p>Status: {{ $notif->status }}</p>
    </div>
@endforeach
